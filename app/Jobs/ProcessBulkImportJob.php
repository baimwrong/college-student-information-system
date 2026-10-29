<?php

namespace App\Jobs;

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
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProcessBulkImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        public int $importLogId,
        public string $filePath,
        public string $type,
        public string $onDuplicate
    ) {}

    public function handle(): void
    {
        $log = ImportLog::find($this->importLogId);
        if (! $log) {
            return;
        }

        $log->update(['status' => 'processing']);

        if (! file_exists($this->filePath)) {
            $log->update([
                'status' => 'failed',
                'errors' => [['row' => 0, 'error' => 'Import source file not found on disk.']],
            ]);

            return;
        }

        $handle = fopen($this->filePath, 'r');
        if (! $handle) {
            $log->update([
                'status' => 'failed',
                'errors' => [['row' => 0, 'error' => 'Unable to open import file.']],
            ]);

            return;
        }

        // Check BOM
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
            $log->update(['status' => 'failed', 'errors' => [['row' => 0, 'error' => 'CSV file appears to be empty.']]]);

            return;
        }

        $headers = array_map(fn ($h) => strtolower(trim($h)), $headerRow);

        $totalRows = 0;
        $importedRows = 0;
        $updatedRows = 0;
        $skippedRows = 0;
        $failedRows = 0;
        $errors = [];
        $rowNumber = 1;
        $affectedStudentIds = [];

        // Reference lookups cached in memory
        $deptCache = Department::all()->keyBy('code');
        $progCache = AcademicProgram::all()->keyBy('code');

        while (($data = fgetcsv($handle, 0, $detectedDelimiter)) !== false) {
            $rowNumber++;

            if (empty(array_filter($data, fn ($v) => trim($v) !== ''))) {
                continue;
            }

            $totalRows++;

            if (count($data) < count($headers)) {
                $failedRows++;
                if (count($errors) < 50) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'error' => 'Column count mismatch. Expected '.count($headers).' columns, found '.count($data),
                    ];
                }

                continue;
            }

            $row = array_combine($headers, array_slice($data, 0, count($headers)));
            $row = array_map(fn ($val) => trim($val), $row);

            try {
                $res = match ($this->type) {
                    'students' => $this->importStudentRow($row, $deptCache, $progCache),
                    'instructors' => $this->importInstructorRow($row, $deptCache),
                    'courses' => $this->importCourseRow($row, $deptCache),
                    'enrollments' => $this->importEnrollmentRow($row, $affectedStudentIds),
                    default => throw new \Exception('Unsupported import entity type.'),
                };

                if ($res === 'imported') {
                    $importedRows++;
                } elseif ($res === 'updated') {
                    $updatedRows++;
                } elseif ($res === 'skipped') {
                    $skippedRows++;
                }
            } catch (\Throwable $e) {
                $failedRows++;
                if (count($errors) < 50) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }

        fclose($handle);

        // Recalculate CGPA for affected students
        if ($this->type === 'enrollments' && ! empty($affectedStudentIds)) {
            foreach (array_keys($affectedStudentIds) as $stId) {
                $st = Student::find($stId);
                if ($st) {
                    $st->recalculateGpa();
                }
            }
        }

        $finalStatus = 'completed';
        if ($failedRows > 0) {
            $finalStatus = ($importedRows > 0 || $updatedRows > 0) ? 'partially_failed' : 'failed';
        }

        $log->update([
            'total_rows' => $totalRows,
            'imported_rows' => $importedRows,
            'updated_rows' => $updatedRows,
            'skipped_rows' => $skippedRows,
            'failed_rows' => $failedRows,
            'status' => $finalStatus,
            'errors' => ! empty($errors) ? $errors : null,
        ]);

        // Cleanup temporary import file if in storage
        if (file_exists($this->filePath) && str_contains($this->filePath, 'imports')) {
            @unlink($this->filePath);
        }
    }

    private function importStudentRow(array $row, $deptCache, $progCache): string
    {
        if (empty($row['student_id']) || empty($row['email']) || empty($row['first_name']) || empty($row['last_name'])) {
            throw new \Exception('Missing required fields: student_id, first_name, last_name, or email.');
        }

        $department = $deptCache[$row['department_code'] ?? ''] ?? Department::where('code', $row['department_code'] ?? '')->first();
        if (! $department) {
            throw new \Exception("Department code '{$row['department_code']}' does not exist.");
        }

        $program = $progCache[$row['program_code'] ?? ''] ?? AcademicProgram::where('code', $row['program_code'] ?? '')->first();
        if (! $program) {
            throw new \Exception("Academic Program code '{$row['program_code']}' does not exist.");
        }

        $existing = Student::where('student_id', $row['student_id'])
            ->orWhere('email', $row['email'])
            ->first();

        if ($existing) {
            if ($this->onDuplicate === 'skip') {
                return 'skipped';
            }

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

    private function importInstructorRow(array $row, $deptCache): string
    {
        if (empty($row['employee_id']) || empty($row['email']) || empty($row['first_name']) || empty($row['last_name'])) {
            throw new \Exception('Missing required fields: employee_id, first_name, last_name, or email.');
        }

        $department = $deptCache[$row['department_code'] ?? ''] ?? Department::where('code', $row['department_code'] ?? '')->first();
        if (! $department) {
            throw new \Exception("Department code '{$row['department_code']}' does not exist.");
        }

        $existing = Instructor::where('employee_id', $row['employee_id'])
            ->orWhere('email', $row['email'])
            ->first();

        if ($existing) {
            if ($this->onDuplicate === 'skip') {
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

    private function importCourseRow(array $row, $deptCache): string
    {
        if (empty($row['code']) || empty($row['title']) || empty($row['department_code'])) {
            throw new \Exception('Missing required fields: code, title, or department_code.');
        }

        $department = $deptCache[$row['department_code']] ?? Department::where('code', $row['department_code'])->first();
        if (! $department) {
            throw new \Exception("Department code '{$row['department_code']}' does not exist.");
        }

        $existing = Course::where('code', $row['code'])->first();

        if ($existing) {
            if ($this->onDuplicate === 'skip') {
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

    private function importEnrollmentRow(array $row, array &$affectedStudentIds): string
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
            if ($this->onDuplicate === 'skip') {
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
}
