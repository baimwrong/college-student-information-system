<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentRegistrationController extends Controller
{
    /**
     * Maximum credit hours a student can enroll in a single academic term without special dean approval.
     */
    public const MAX_CREDITS_PER_SEMESTER = 18;

    /**
     * Display course registration hub for students.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $student = $user->student ?? Student::where('email', $user->email)->first();

        if (! $student) {
            abort(403, 'No student record associated with this account.');
        }

        $currentTerm = AcademicTerm::current()->first() ?? AcademicTerm::latest()->first();

        // Retrieve student's current term enrollments
        $currentEnrollments = collect();
        $currentCredits = 0;
        $enrolledCourseIds = [];
        $enrolledSectionIds = [];

        if ($currentTerm) {
            $currentEnrollments = Enrollment::with(['courseSection.course', 'courseSection.instructor', 'courseSection.classroom', 'term'])
                ->where('student_id', $student->id)
                ->where('term_id', $currentTerm->id)
                ->get();

            foreach ($currentEnrollments as $enr) {
                $c = $enr->courseSection->course->credits ?? 0;
                $currentCredits += $c;
                if ($enr->courseSection) {
                    $enrolledCourseIds[] = $enr->courseSection->course_id;
                    $enrolledSectionIds[] = $enr->course_section_id;
                }
            }
        }

        // Available sections query
        $sectionsQuery = CourseSection::with(['course.department', 'term', 'instructor', 'classroom'])
            ->where('status', 'open')
            ->when($currentTerm, fn ($q) => $q->where('term_id', $currentTerm->id));

        if ($request->filled('search')) {
            $search = $request->search;
            $sectionsQuery->whereHas('course', function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $sectionsQuery->whereHas('course', fn ($q) => $q->where('department_id', $request->department_id));
        }

        $availableSections = $sectionsQuery->orderBy('section_number')->paginate(12)->withQueryString();

        return view('student.registration', [
            'student' => $student,
            'currentTerm' => $currentTerm,
            'currentEnrollments' => $currentEnrollments,
            'currentCredits' => $currentCredits,
            'maxCredits' => self::MAX_CREDITS_PER_SEMESTER,
            'enrolledCourseIds' => $enrolledCourseIds,
            'enrolledSectionIds' => $enrolledSectionIds,
            'availableSections' => $availableSections,
        ]);
    }

    /**
     * Handle self-service course registration.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $student = $user->student ?? Student::where('email', $user->email)->first();

        if (! $student) {
            abort(403, 'No student record associated with this account.');
        }

        if ($student->academic_status === 'suspended') {
            return back()->with('error', 'Registration blocked: Your academic status is currently suspended. Please visit the Registrar.');
        }

        $validated = $request->validate([
            'course_section_id' => ['required', 'exists:course_sections,id'],
        ]);

        $sectionId = (int) $validated['course_section_id'];

        try {
            DB::transaction(function () use ($student, $sectionId) {
                // Lock the course section row
                $section = CourseSection::with('course')
                    ->where('id', $sectionId)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 1. Check if term is active
                $term = AcademicTerm::find($section->term_id);
                if (! $term || $term->status === 'completed') {
                    throw new \DomainException('Course registration is closed for this academic term.');
                }

                // 2. Check if student already enrolled in another section of this course in this term
                $alreadyEnrolledSameCourse = Enrollment::where('student_id', $student->id)
                    ->where('term_id', $section->term_id)
                    ->whereHas('courseSection', fn ($q) => $q->where('course_id', $section->course_id))
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyEnrolledSameCourse) {
                    throw new \DomainException("You are already enrolled in a section of {$section->course->code} for this term.");
                }

                // 3. Check section capacity
                if ($section->enrolled_count >= $section->capacity) {
                    throw new \DomainException("Section {$section->section_number} of {$section->course->code} is currently full.");
                }

                // 4. Check course prerequisites
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
                                throw new \DomainException("Prerequisite unmet: You must complete {$pCode} ({$prereqCourse->title}) before enrolling in this course.");
                            }
                        }
                    }
                }

                // 5. Check maximum semester credit limit
                $currentTermCredits = Enrollment::where('student_id', $student->id)
                    ->where('term_id', $section->term_id)
                    ->with('courseSection.course')
                    ->get()
                    ->sum(fn ($e) => $e->courseSection->course->credits ?? 0);

                $newCredits = $section->course->credits ?? 0;
                if (($currentTermCredits + $newCredits) > self::MAX_CREDITS_PER_SEMESTER) {
                    $limit = self::MAX_CREDITS_PER_SEMESTER;
                    throw new \DomainException("Credit limit exceeded: Registering for this course would bring your load to ".($currentTermCredits + $newCredits)." credits (maximum allowed is {$limit} credits).");
                }

                // 6. Check timetable collision with student's other enrolled classes
                if (! empty($section->day_of_week) && ! empty($section->start_time) && ! empty($section->end_time)) {
                    $newDays = array_filter(array_map('trim', explode(',', str_replace(['/', '&'], ',', $section->day_of_week))));

                    $studentCurrentSections = CourseSection::whereHas('enrollments', function ($q) use ($student) {
                        $q->where('student_id', $student->id);
                    })
                        ->where('term_id', $section->term_id)
                        ->whereNotNull('day_of_week')
                        ->whereNotNull('start_time')
                        ->whereNotNull('end_time')
                        ->with('course')
                        ->get();

                    foreach ($studentCurrentSections as $existingSec) {
                        $existingDays = array_filter(array_map('trim', explode(',', str_replace(['/', '&'], ',', $existingSec->day_of_week))));
                        $commonDays = array_intersect(array_map('strtolower', $newDays), array_map('strtolower', $existingDays));

                        if (! empty($commonDays)) {
                            // Check time overlap: (start1 < end2) and (end1 > start2)
                            if ($section->start_time < $existingSec->end_time && $section->end_time > $existingSec->start_time) {
                                throw new \DomainException("Schedule Conflict: This section ({$section->day_of_week} {$section->start_time}-{$section->end_time}) overlaps with your registered course {$existingSec->course->code} ({$existingSec->day_of_week} {$existingSec->start_time}-{$existingSec->end_time}).");
                            }
                        }
                    }
                }

                // 7. Create Enrollment & increment count
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
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Course section registered successfully!');
    }

    /**
     * Handle student self-service course drop.
     */
    public function destroy(Enrollment $enrollment)
    {
        $user = Auth::user();
        $student = $user->student ?? Student::where('email', $user->email)->first();

        if (! $student || $enrollment->student_id !== $student->id) {
            abort(403, 'Unauthorized.');
        }

        // Only allow dropping if grades haven't been published yet
        if ($enrollment->grade_status === 'published') {
            return back()->with('error', 'Cannot drop a course section after final grades have been published.');
        }

        DB::transaction(function () use ($enrollment) {
            $section = $enrollment->courseSection;
            $enrollment->delete();

            if ($section && $section->enrolled_count > 0) {
                $section->decrement('enrolled_count');
            }
        });

        return back()->with('success', 'Course section successfully dropped.');
    }

    /**
     * Display weekly schedule timetable grid for student.
     */
    public function schedule()
    {
        $user = Auth::user();
        $student = $user->student ?? Student::where('email', $user->email)->first();

        if (! $student) {
            abort(403, 'No student record associated with this account.');
        }

        $currentTerm = AcademicTerm::current()->first() ?? AcademicTerm::latest()->first();

        $enrolledSections = collect();
        if ($currentTerm) {
            $enrolledSections = CourseSection::whereHas('enrollments', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })
                ->where('term_id', $currentTerm->id)
                ->with(['course', 'instructor', 'classroom', 'term'])
                ->get();
        }

        return view('student.schedule', compact('student', 'currentTerm', 'enrolledSections'));
    }
}
