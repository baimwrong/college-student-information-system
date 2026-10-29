<?php

namespace App\Http\Controllers;

use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\ImportLog;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImportController extends Controller
{
    public function index()
    {
        $logs = ImportLog::with('user')->latest()->paginate(10);
        $totalImports = ImportLog::count();
        $totalImportedRows = ImportLog::sum('imported_rows');
        $totalUpdatedRows = ImportLog::sum('updated_rows');
        $totalFailedRows = ImportLog::sum('failed_rows');

        return view('admin.imports.index', compact(
            'logs',
            'totalImports',
            'totalImportedRows',
            'totalUpdatedRows',
            'totalFailedRows'
        ));
    }

    public function showLog(ImportLog $log)
    {
        return view('admin.imports.show', compact('log'));
    }

    /**
     * Download CSV Template for a given entity type
     */
    public function template(string $type): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"template_{$type}.csv\"",
        ];

        $callback = function () use ($type) {
            $handle = fopen('php://output', 'w');

            switch ($type) {
                case 'students':
                    fputcsv($handle, ['student_id', 'first_name', 'last_name', 'email', 'phone', 'department_code', 'program_code', 'admission_date', 'gender', 'current_semester', 'academic_status']);
                    fputcsv($handle, ['STU-2026-101', 'John', 'Doe', 'john.doe@example.edu', '+15550192', 'CS', 'BSCS', '2026-09-01', 'male', '1', 'active']);
                    fputcsv($handle, ['STU-2026-102', 'Jane', 'Smith', 'jane.smith@example.edu', '+15550193', 'CS', 'BSSE', '2026-09-01', 'female', '1', 'active']);
                    break;

                case 'instructors':
                    fputcsv($handle, ['employee_id', 'first_name', 'last_name', 'email', 'phone', 'department_code', 'designation', 'specialization', 'office_room', 'hire_date']);
                    fputcsv($handle, ['FAC-201', 'Robert', 'Miller', 'robert.miller@example.edu', '+15550201', 'CS', 'Associate Professor', 'Distributed Systems', 'ENG-304', '2022-01-15']);
                    fputcsv($handle, ['FAC-202', 'Elena', 'Rostova', 'elena.rostova@example.edu', '+15550202', 'MATH', 'Professor', 'Applied Statistics', 'SCI-210', '2020-08-20']);
                    break;

                case 'courses':
                    fputcsv($handle, ['code', 'title', 'department_code', 'credits', 'level', 'lecture_hours', 'lab_hours', 'prerequisites', 'description']);
                    fputcsv($handle, ['CS101', 'Introduction to Computer Science', 'CS', '3', '100', '3', '0', '', 'Fundamental computing principles and algorithmic thinking.']);
                    fputcsv($handle, ['CS201', 'Data Structures & Algorithms', 'CS', '4', '200', '3', '2', 'CS101', 'Linear and non-linear data structures with algorithmic complexity analysis.']);
                    break;

                case 'enrollments':
                    fputcsv($handle, ['student_id', 'course_code', 'term_code', 'section_number', 'midterm_score', 'final_score', 'assignments_score', 'grade_status']);
                    fputcsv($handle, ['STU-2026-101', 'CS101', '2026-FALL', 'SEC-A', '88.5', '92.0', '95.0', 'published']);
                    fputcsv($handle, ['STU-2026-102', 'CS101', '2026-FALL', 'SEC-A', '75.0', '80.0', '85.0', 'published']);
                    break;

                default:
                    fputcsv($handle, ['invalid_type']);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Process Bulk CSV Import
     */
    public function import(Request $request, string $type)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
            'on_duplicate' => ['required', 'in:skip,update'],
        ]);

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();
        $fileName = $file->getClientOriginalName();
        $onDuplicate = $request->input('on_duplicate', 'skip');

        @set_time_limit(600);

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return back()->with('error', 'Unable to open the uploaded file.');
        }

        // Detect and strip UTF-8 BOM via stream offset (zero memory footprint)
        $bom = fread($handle, 3);
        $offset = ($bom === "\xEF\xBB\xBF") ? 3 : 0;
        fseek($handle, $offset);

        $firstLine = fgets($handle);
        fseek($handle, $offset);

        $delimiters = [',', ';', "\t"];
        $detectedDelimiter = ',';
        $maxCount = 0;
        foreach ($delimiters as $delim) {
            $count = substr_count($firstLine, $delim);
            if ($count > $maxCount) {
                $maxCount = $count;
                $detectedDelimiter = $delim;
            }
        }

        $headerRow = fgetcsv($handle, 0, $detectedDelimiter);
        if (! $headerRow) {
            fclose($handle);

            return back()->with('error', 'CSV file appears to be empty.');
        }

        // Clean headers
        $headers = array_map(fn ($h) => strtolower(trim($h)), $headerRow);

        $totalRows = 0;
        $importedRows = 0;
        $updatedRows = 0;
        $skippedRows = 0;
        $failedRows = 0;
        $errors = [];

        $rowNumber = 1;
        $affectedStudentIds = [];

        // Preload reference models to avoid thousands of repetitive queries (O(1) lookups)
        $deptCache = Department::all()->keyBy('code');
        $progCache = AcademicProgram::all()->keyBy('code');

        while (($data = fgetcsv($handle, 0, $detectedDelimiter)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (empty(array_filter($data, fn ($v) => trim($v) !== ''))) {
                continue;
            }

            $totalRows++;

            if (count($data) < count($headers)) {
                $failedRows++;
                $errors[] = [
                    'row' => $rowNumber,
                    'error' => 'Column count mismatch. Expected '.count($headers).' columns, found '.count($data),
                ];

                continue;
            }

            $row = array_combine($headers, array_slice($data, 0, count($headers)));
            $row = array_map(fn ($val) => trim($val), $row);

            try {
                switch ($type) {
                    case 'students':
                        $res = $this->importStudentRow($row, $onDuplicate, $deptCache, $progCache);
                        break;
                    case 'instructors':
                        $res = $this->importInstructorRow($row, $onDuplicate, $deptCache);
                        break;
                    case 'courses':
                        $res = $this->importCourseRow($row, $onDuplicate, $deptCache);
                        break;
                    case 'enrollments':
                        $res = $this->importEnrollmentRow($row, $onDuplicate, $affectedStudentIds);
                        break;
                    default:
                        throw new \Exception('Unsupported import entity type.');
                }

                if ($res === 'imported') {
                    $importedRows++;
                } elseif ($res === 'updated') {
                    $updatedRows++;
                } elseif ($res === 'skipped') {
                    $skippedRows++;
                }
            } catch (\Exception $e) {
                $failedRows++;
                $errors[] = [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                ];
            }
        }

        fclose($handle);

        // Batch recalculate CGPA once for all affected students in enrollment imports
        if ($type === 'enrollments' && ! empty($affectedStudentIds)) {
            foreach (array_keys($affectedStudentIds) as $stId) {
                $st = Student::find($stId);
                if ($st) {
                    $st->recalculateGpa();
                }
            }
        }

        $status = 'completed';
        if ($failedRows > 0) {
            $status = ($importedRows > 0 || $updatedRows > 0) ? 'partially_failed' : 'failed';
        }

        $log = ImportLog::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'file_name' => $fileName,
            'total_rows' => $totalRows,
            'imported_rows' => $importedRows,
            'updated_rows' => $updatedRows,
            'skipped_rows' => $skippedRows,
            'failed_rows' => $failedRows,
            'status' => $status,
            'errors' => ! empty($errors) ? array_slice($errors, 0, 50) : null, // keep up to 50 detailed errors
        ]);

        $summary = "Bulk import processed: {$importedRows} imported, {$updatedRows} updated, {$skippedRows} skipped, {$failedRows} failed.";

        if ($failedRows > 0) {
            return redirect()->route('admin.imports.show', $log)->with('warning', $summary);
        }

        return redirect()->route('admin.imports.index')->with('success', $summary);
    }

    private function importStudentRow(array $row, string $onDuplicate, $deptCache = null, $progCache = null): string
    {
        if (empty($row['student_id']) || empty($row['email']) || empty($row['first_name']) || empty($row['last_name'])) {
            throw new \Exception('Missing required fields: student_id, first_name, last_name, or email.');
        }

        $deptCode = $row['department_code'] ?? '';
        $department = $deptCache[$deptCode] ?? Department::where('code', $deptCode)->first();
        if (! $department) {
            throw new \Exception("Department code '{$deptCode}' does not exist.");
        }

        $progCode = $row['program_code'] ?? '';
        $program = $progCache[$progCode] ?? AcademicProgram::where('code', $progCode)->first();
        if (! $program) {
            throw new \Exception("Academic Program code '{$progCode}' does not exist.");
        }

        $existing = Student::where('student_id', $row['student_id'])
            ->orWhere('email', $row['email'])
            ->first();

        if ($existing) {
            if ($onDuplicate === 'skip') {
                return 'skipped';
            }

            // Update existing
            $existing->update([
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'phone' => $row['phone'] ?? $existing->phone,
                'department_id' => $department->id,
                'program_id' => $program->id,
                'admission_date' => $row['admission_date'] ?? $existing->admission_date,
                'gender' => in_array($row['gender'] ?? '', ['male', 'female', 'other']) ? $row['gender'] : $existing->gender,
                'current_semester' => (int) ($row['current_semester'] ?? $existing->current_semester),
                'academic_status' => in_array($row['academic_status'] ?? '', ['active', 'probation', 'graduated', 'suspended', 'withdrawn']) ? $row['academic_status'] : $existing->academic_status,
            ]);

            if ($existing->user) {
                $existing->user->update([
                    'name' => "{$row['first_name']} {$row['last_name']}",
                    'phone' => $row['phone'] ?? null,
                ]);
            }

            return 'updated';
        }

        // Create new student and user account
        DB::transaction(function () use ($row, $department, $program) {
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => "{$row['first_name']} {$row['last_name']}",
                    'password' => Hash::make('password123'),
                    'role' => 'student',
                    'phone' => $row['phone'] ?? null,
                    'status' => 'active',
                    'must_change_password' => true,
                ]
            );

            Student::create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'program_id' => $program->id,
                'student_id' => $row['student_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'phone' => $row['phone'] ?? null,
                'admission_date' => $row['admission_date'] ?? now()->toDateString(),
                'gender' => in_array($row['gender'] ?? '', ['male', 'female', 'other']) ? $row['gender'] : 'male',
                'current_semester' => (int) ($row['current_semester'] ?? 1),
                'academic_status' => in_array($row['academic_status'] ?? '', ['active', 'probation', 'graduated', 'suspended', 'withdrawn']) ? $row['academic_status'] : 'active',
                'cgpa' => 0.00,
                'total_credits_earned' => 0,
            ]);
        });

        return 'imported';
    }

    private function importInstructorRow(array $row, string $onDuplicate, $deptCache = null): string
    {
        if (empty($row['employee_id']) || empty($row['email']) || empty($row['first_name']) || empty($row['last_name'])) {
            throw new \Exception('Missing required fields: employee_id, first_name, last_name, or email.');
        }

        $deptCode = $row['department_code'] ?? '';
        $department = $deptCache[$deptCode] ?? Department::where('code', $deptCode)->first();
        if (! $department) {
            throw new \Exception("Department code '{$deptCode}' does not exist.");
        }

        $existing = Instructor::where('employee_id', $row['employee_id'])
            ->orWhere('email', $row['email'])
            ->first();

        if ($existing) {
            if ($onDuplicate === 'skip') {
                return 'skipped';
            }

            $existing->update([
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'phone' => $row['phone'] ?? $existing->phone,
                'department_id' => $department->id,
                'designation' => $row['designation'] ?? $existing->designation,
                'specialization' => $row['specialization'] ?? $existing->specialization,
                'office_room' => $row['office_room'] ?? $existing->office_room,
                'hire_date' => $row['hire_date'] ?? $existing->hire_date,
            ]);

            if ($existing->user) {
                $existing->user->update([
                    'name' => "{$row['first_name']} {$row['last_name']}",
                    'phone' => $row['phone'] ?? null,
                ]);
            }

            return 'updated';
        }

        DB::transaction(function () use ($row, $department) {
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => "{$row['first_name']} {$row['last_name']}",
                    'password' => Hash::make('password123'),
                    'role' => 'instructor',
                    'phone' => $row['phone'] ?? null,
                    'status' => 'active',
                    'must_change_password' => true,
                ]
            );

            Instructor::create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'employee_id' => $row['employee_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'phone' => $row['phone'] ?? null,
                'designation' => $row['designation'] ?? 'Lecturer',
                'specialization' => $row['specialization'] ?? null,
                'office_room' => $row['office_room'] ?? null,
                'hire_date' => $row['hire_date'] ?? now()->toDateString(),
                'status' => 'active',
            ]);
        });

        return 'imported';
    }

    private function importCourseRow(array $row, string $onDuplicate, $deptCache = null): string
    {
        if (empty($row['code']) || empty($row['title']) || empty($row['department_code'])) {
            throw new \Exception('Missing required fields: code, title, or department_code.');
        }

        $deptCode = $row['department_code'] ?? '';
        $department = $deptCache[$deptCode] ?? Department::where('code', $deptCode)->first();
        if (! $department) {
            throw new \Exception("Department code '{$deptCode}' does not exist.");
        }

        $existing = Course::where('code', $row['code'])->first();

        if ($existing) {
            if ($onDuplicate === 'skip') {
                return 'skipped';
            }

            $existing->update([
                'title' => $row['title'],
                'department_id' => $department->id,
                'credits' => (int) ($row['credits'] ?? $existing->credits),
                'level' => (int) ($row['level'] ?? $existing->level),
                'lecture_hours' => (int) ($row['lecture_hours'] ?? $existing->lecture_hours),
                'lab_hours' => (int) ($row['lab_hours'] ?? $existing->lab_hours),
                'prerequisites' => $row['prerequisites'] ?? $existing->prerequisites,
                'description' => $row['description'] ?? $existing->description,
            ]);

            return 'updated';
        }

        Course::create([
            'department_id' => $department->id,
            'code' => $row['code'],
            'title' => $row['title'],
            'description' => $row['description'] ?? null,
            'credits' => (int) ($row['credits'] ?? 3),
            'level' => (int) ($row['level'] ?? 100),
            'lecture_hours' => (int) ($row['lecture_hours'] ?? 3),
            'lab_hours' => (int) ($row['lab_hours'] ?? 0),
            'prerequisites' => $row['prerequisites'] ?? null,
            'status' => 'active',
        ]);

        return 'imported';
    }

    private function importEnrollmentRow(array $row, string $onDuplicate, array &$affectedStudentIds = []): string
    {
        if (empty($row['student_id']) || empty($row['course_code']) || empty($row['term_code'])) {
            throw new \Exception('Missing required fields: student_id, course_code, or term_code.');
        }

        $student = Student::where('student_id', $row['student_id'])->first();
        if (! $student) {
            throw new \Exception("Student ID '{$row['student_id']}' does not exist.");
        }

        $course = Course::where('code', $row['course_code'])->first();
        if (! $course) {
            throw new \Exception("Course code '{$row['course_code']}' does not exist.");
        }

        $term = AcademicTerm::where('code', $row['term_code'])->first();
        if (! $term) {
            throw new \Exception("Term code '{$row['term_code']}' does not exist.");
        }

        $sectionNumber = ! empty($row['section_number']) ? $row['section_number'] : 'SEC-A';

        // Find or create course section
        $section = CourseSection::firstOrCreate(
            [
                'course_id' => $course->id,
                'term_id' => $term->id,
                'section_number' => $sectionNumber,
            ],
            [
                'capacity' => 40,
                'enrolled_count' => 0,
                'status' => 'open',
            ]
        );

        $existing = Enrollment::where('student_id', $student->id)
            ->where('course_section_id', $section->id)
            ->first();

        $midterm = isset($row['midterm_score']) && is_numeric($row['midterm_score']) ? (float) $row['midterm_score'] : null;
        $final = isset($row['final_score']) && is_numeric($row['final_score']) ? (float) $row['final_score'] : null;
        $assignments = isset($row['assignments_score']) && is_numeric($row['assignments_score']) ? (float) $row['assignments_score'] : null;
        $gradeStatus = in_array($row['grade_status'] ?? '', ['draft', 'submitted', 'published']) ? $row['grade_status'] : 'published';

        if ($existing) {
            if ($onDuplicate === 'skip') {
                return 'skipped';
            }

            $existing->midterm_score = $midterm ?? $existing->midterm_score;
            $existing->final_score = $final ?? $existing->final_score;
            $existing->assignments_score = $assignments ?? $existing->assignments_score;
            $existing->grade_status = $gradeStatus;
            $existing->computeGrade(false);
            $existing->save();
            $affectedStudentIds[$student->id] = true;

            return 'updated';
        }

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_section_id' => $section->id,
            'term_id' => $term->id,
            'status' => 'enrolled',
            'midterm_score' => $midterm,
            'final_score' => $final,
            'assignments_score' => $assignments,
            'grade_status' => $gradeStatus,
        ]);

        $section->increment('enrolled_count');
        $enrollment->computeGrade(false);
        $enrollment->save();
        $affectedStudentIds[$student->id] = true;

        return 'imported';
    }

    /**
     * Export data to CSV with formula injection sanitization (CWE-1236)
     */
    public function export(string $type): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"export_{$type}_".date('Ymd_His').'.csv"',
        ];

        $callback = function () use ($type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'students') {
                fputcsv($handle, ['student_id', 'first_name', 'last_name', 'email', 'phone', 'department_code', 'program_code', 'admission_date', 'gender', 'current_semester', 'academic_status', 'cgpa', 'total_credits_earned']);
                Student::with(['department', 'program'])->chunk(100, function ($students) use ($handle) {
                    foreach ($students as $s) {
                        fputcsv($handle, $this->sanitizeCsvRow([
                            $s->student_id,
                            $s->first_name,
                            $s->last_name,
                            $s->email,
                            $s->phone,
                            $s->department->code ?? '',
                            $s->program->code ?? '',
                            $s->admission_date ? $s->admission_date->format('Y-m-d') : '',
                            $s->gender,
                            $s->current_semester,
                            $s->academic_status,
                            $s->cgpa,
                            $s->total_credits_earned,
                        ]));
                    }
                });
            } elseif ($type === 'courses') {
                fputcsv($handle, ['code', 'title', 'department_code', 'credits', 'level', 'lecture_hours', 'lab_hours', 'prerequisites', 'status']);
                Course::with('department')->chunk(100, function ($courses) use ($handle) {
                    foreach ($courses as $c) {
                        fputcsv($handle, $this->sanitizeCsvRow([
                            $c->code,
                            $c->title,
                            $c->department->code ?? '',
                            $c->credits,
                            $c->level,
                            $c->lecture_hours,
                            $c->lab_hours,
                            $c->prerequisites,
                            $c->status,
                        ]));
                    }
                });
            } elseif ($type === 'instructors') {
                fputcsv($handle, ['employee_id', 'first_name', 'last_name', 'email', 'phone', 'department_code', 'designation', 'specialization', 'office_room', 'status']);
                Instructor::with('department')->chunk(100, function ($instructors) use ($handle) {
                    foreach ($instructors as $i) {
                        fputcsv($handle, $this->sanitizeCsvRow([
                            $i->employee_id,
                            $i->first_name,
                            $i->last_name,
                            $i->email,
                            $i->phone,
                            $i->department->code ?? '',
                            $i->designation,
                            $i->specialization,
                            $i->office_room,
                            $i->status,
                        ]));
                    }
                });
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Sanitize CSV cells to prevent Formula Injection (CWE-1236)
     */
    private function sanitizeCsvRow(array $row): array
    {
        return array_map(function ($val) {
            $str = (string) ($val ?? '');
            if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"])) {
                return "'".$str;
            }

            return $str;
        }, $row);
    }
}
