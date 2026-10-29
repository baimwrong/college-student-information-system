<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Enrollment::with(['student.program', 'courseSection.course', 'courseSection.instructor', 'term']);

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        if ($request->filled('section_id')) {
            $query->where('course_section_id', $request->section_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->latest()->paginate(15)->withQueryString();
        $terms = AcademicTerm::latest('start_date')->get();

        return view('enrollments.index', compact('enrollments', 'terms'));
    }

    public function create()
    {
        $currentTerm = AcademicTerm::current()->first() ?? AcademicTerm::latest()->first();
        $sections = CourseSection::with(['course', 'term', 'instructor'])
            ->when($currentTerm, fn ($q) => $q->where('term_id', $currentTerm->id))
            ->where('status', 'open')
            ->get();
        $students = Student::where('academic_status', 'active')->orderBy('first_name')->get();

        return view('enrollments.create', compact('sections', 'students', 'currentTerm'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'course_section_id' => ['required', 'exists:course_sections,id'],
        ]);

        $section = CourseSection::with('course')->findOrFail($validated['course_section_id']);
        $student = Student::findOrFail($validated['student_id']);

        // Check course prerequisites
        if (! empty($section->course->prerequisites)) {
            $prereqCodes = array_filter(array_map('trim', explode(',', $section->course->prerequisites)));
            foreach ($prereqCodes as $pCode) {
                $prereqCourse = Course::where('code', $pCode)->first();
                if ($prereqCourse) {
                    $hasPassed = Enrollment::where('student_id', $student->id)
                        ->where('grade_status', 'published')
                        ->where('grade_point', '>', 0)
                        ->whereHas('courseSection', fn ($q) => $q->where('course_id', $prereqCourse->id))
                        ->exists();

                    if (! $hasPassed) {
                        return back()->withInput()->withErrors([
                            'course_section_id' => "Student {$student->student_id} has not completed the prerequisite course {$pCode} ({$prereqCourse->title}).",
                        ]);
                    }
                }
            }
        }

        try {
            DB::transaction(function () use ($student, $validated) {
                // Pessimistic lock on the section row to eliminate race conditions
                $section = CourseSection::where('id', $validated['course_section_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // Check if student already enrolled in this section or another section of the same course in this term
                $alreadyEnrolledSameCourse = Enrollment::where('student_id', $student->id)
                    ->where('term_id', $section->term_id)
                    ->whereHas('courseSection', fn ($q) => $q->where('course_id', $section->course_id))
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyEnrolledSameCourse) {
                    throw new \DomainException('This student is already enrolled in a section of this course for this term.');
                }

                if ($section->enrolled_count >= $section->capacity) {
                    throw new \DomainException('This course section is currently at maximum capacity.');
                }

                Enrollment::create([
                    'student_id' => $student->id,
                    'course_section_id' => $section->id,
                    'term_id' => $section->term_id,
                    'status' => 'enrolled',
                    'grade_status' => 'draft',
                ]);

                $section->increment('enrolled_count');
            });
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors([
                'course_section_id' => $e->getMessage(),
            ]);
        }

        return redirect()->route('enrollments.index')->with('success', 'Student enrolled successfully.');
    }

    public function destroy(Enrollment $enrollment)
    {
        DB::transaction(function () use ($enrollment) {
            $section = $enrollment->courseSection;
            $student = $enrollment->student;

            $enrollment->delete();

            if ($section && $section->enrolled_count > 0) {
                $section->decrement('enrolled_count');
            }

            if ($student) {
                $student->recalculateGpa();
            }
        });

        return back()->with('success', 'Enrollment removed.');
    }

    /**
     * Batch Grade Sheet entry for an entire class section
     */
    public function gradeSheet(CourseSection $section)
    {
        $user = Auth::user();

        // Check authorization: Admin, Staff, or the assigned instructor
        if ($user->isInstructor()) {
            $instructor = $user->instructor ?? Instructor::where('email', $user->email)->first();
            if (! $instructor || $section->instructor_id !== $instructor->id) {
                abort(403, 'Unauthorized to grade this course section.');
            }
        } elseif (! $user->isAdmin() && ! $user->isStaff()) {
            abort(403, 'Unauthorized to grade this course section.');
        }

        $section->load([
            'course',
            'term',
            'instructor',
            'classroom',
            'enrollments.student.program',
        ]);

        return view('enrollments.grades', compact('section'));
    }

    /**
     * Save the Batch Grade Sheet
     */
    public function saveGradeSheet(Request $request, CourseSection $section)
    {
        $user = Auth::user();

        if ($user->isInstructor()) {
            $instructor = $user->instructor ?? Instructor::where('email', $user->email)->first();
            if (! $instructor || $section->instructor_id !== $instructor->id) {
                abort(403, 'Unauthorized to grade this course section.');
            }
        } elseif (! $user->isAdmin() && ! $user->isStaff()) {
            abort(403, 'Unauthorized to grade this course section.');
        }

        $validated = $request->validate([
            'grades' => ['required', 'array'],
            'grades.*.enrollment_id' => ['required', 'exists:enrollments,id'],
            'grades.*.assignments_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.midterm_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.final_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.grade_status' => ['required', 'in:draft,submitted,published'],
            'grades.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            $studentsToRecalculate = [];

            foreach ($validated['grades'] as $gradeRow) {
                $enrollment = Enrollment::find($gradeRow['enrollment_id']);
                if ($enrollment) {
                    $wasPublished = $enrollment->grade_status === 'published';
                    $newStatus = $gradeRow['grade_status'];
                    $isNowPublished = $newStatus === 'published';

                    $enrollment->assignments_score = $gradeRow['assignments_score'] ?? null;
                    $enrollment->midterm_score = $gradeRow['midterm_score'] ?? null;
                    $enrollment->final_score = $gradeRow['final_score'] ?? null;
                    $enrollment->grade_status = $newStatus;
                    $enrollment->remarks = $gradeRow['remarks'] ?? null;

                    // Compute grade numbers without triggering auto recalculation per row
                    $enrollment->computeGrade(false);
                    $enrollment->save();

                    // If grade status was published or is now published, recalculate CGPA
                    if ($enrollment->student_id && ($wasPublished || $isNowPublished)) {
                        $studentsToRecalculate[$enrollment->student_id] = true;
                    }
                }
            }

            foreach (array_keys($studentsToRecalculate) as $studentId) {
                $student = Student::find($studentId);
                if ($student) {
                    $student->recalculateGpa();
                }
            }
        });

        return back()->with('success', 'Grade sheet saved and student records updated successfully.');
    }
}
