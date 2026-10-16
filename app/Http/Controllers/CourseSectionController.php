<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Instructor;
use Illuminate\Http\Request;

class CourseSectionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = CourseSection::with(['course.department', 'term', 'instructor', 'classroom']);

        // If instructor, only show assigned sections
        if ($user && $user->isInstructor()) {
            $instructor = $user->instructor ?? Instructor::where('email', $user->email)->first();
            if ($instructor) {
                $query->where('instructor_id', $instructor->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($request->filled('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
        }

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        } else {
            $currentTerm = AcademicTerm::current()->first();
            if ($currentTerm) {
                $query->where('term_id', $currentTerm->id);
            }
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $sections = $query->latest()->paginate(10)->withQueryString();
        $terms = AcademicTerm::latest('start_date')->get();
        $courses = Course::where('status', 'active')->orderBy('code')->get();
        $instructors = Instructor::where('status', 'active')->orderBy('first_name')->get();

        return view('sections.index', compact('sections', 'terms', 'courses', 'instructors'));
    }

    public function create()
    {
        $courses = Course::where('status', 'active')->orderBy('code')->get();
        $terms = AcademicTerm::where('status', '!=', 'completed')->latest('start_date')->get();
        $instructors = Instructor::where('status', 'active')->orderBy('first_name')->get();
        $classrooms = Classroom::where('status', 'available')->orderBy('building')->get();

        return view('sections.create', compact('courses', 'terms', 'instructors', 'classrooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'term_id' => ['required', 'exists:academic_terms,id'],
            'instructor_id' => ['nullable', 'exists:instructors,id'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'section_number' => ['required', 'string', 'max:20'],
            'day_of_week' => ['nullable', 'string', 'max:50'],
            'start_time' => ['nullable', 'string', 'max:10'],
            'end_time' => ['nullable', 'string', 'max:10'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', 'in:open,closed,cancelled'],
        ]);

        $exists = CourseSection::where('course_id', $validated['course_id'])
            ->where('term_id', $validated['term_id'])
            ->where('section_number', $validated['section_number'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'section_number' => 'A section with this number already exists for this course in the selected term.',
            ]);
        }

        $conflictError = $this->validateScheduleConflicts($validated);
        if ($conflictError) {
            return back()->withInput()->withErrors([
                'day_of_week' => $conflictError,
            ]);
        }

        CourseSection::create($validated);

        return redirect()->route('sections.index')->with('success', 'Class section scheduled successfully.');
    }

    public function show(CourseSection $section, Request $request)
    {
        $user = $request->user();

        // If instructor, only allow viewing assigned section
        if ($user && $user->isInstructor()) {
            $instructor = $user->instructor ?? Instructor::where('email', $user->email)->first();
            if (! $instructor || $section->instructor_id !== $instructor->id) {
                abort(403, 'Unauthorized to view this course section.');
            }
        }

        $section->load([
            'course.department',
            'term',
            'instructor',
            'classroom',
            'enrollments.student.program',
        ]);

        return view('sections.show', compact('section'));
    }

    public function edit(CourseSection $section)
    {
        $courses = Course::where('status', 'active')->orderBy('code')->get();
        $terms = AcademicTerm::latest('start_date')->get();
        $instructors = Instructor::where('status', 'active')->orderBy('first_name')->get();
        $classrooms = Classroom::orderBy('building')->get();

        return view('sections.edit', compact('section', 'courses', 'terms', 'instructors', 'classrooms'));
    }

    public function update(Request $request, CourseSection $section)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'term_id' => ['required', 'exists:academic_terms,id'],
            'instructor_id' => ['nullable', 'exists:instructors,id'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'section_number' => ['required', 'string', 'max:20'],
            'day_of_week' => ['nullable', 'string', 'max:50'],
            'start_time' => ['nullable', 'string', 'max:10'],
            'end_time' => ['nullable', 'string', 'max:10'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', 'in:open,closed,cancelled'],
        ]);

        $exists = CourseSection::where('course_id', $validated['course_id'])
            ->where('term_id', $validated['term_id'])
            ->where('section_number', $validated['section_number'])
            ->where('id', '!=', $section->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'section_number' => 'Another section with this number already exists for this course and term.',
            ]);
        }

        $conflictError = $this->validateScheduleConflicts($validated, $section->id);
        if ($conflictError) {
            return back()->withInput()->withErrors([
                'day_of_week' => $conflictError,
            ]);
        }

        $section->update($validated);

        return redirect()->route('sections.index')->with('success', 'Class section updated successfully.');
    }

    public function destroy(CourseSection $section)
    {
        if ($section->enrollments()->exists()) {
            return back()->with('error', 'Cannot delete section with registered students.');
        }

        $section->delete();

        return redirect()->route('sections.index')->with('success', 'Class section deleted successfully.');
    }

    /**
     * Validate room and instructor schedule conflicts and classroom capacity.
     */
    private function validateScheduleConflicts(array $validated, ?int $ignoreSectionId = null): ?string
    {
        // 1. Time range check
        if (! empty($validated['start_time']) && ! empty($validated['end_time'])) {
            if ($validated['start_time'] >= $validated['end_time']) {
                return 'Class section end time must be after the start time.';
            }
        }

        // 2. Classroom capacity check
        if (! empty($validated['classroom_id'])) {
            $classroom = Classroom::find($validated['classroom_id']);
            if ($classroom && $validated['capacity'] > $classroom->capacity) {
                return "Section capacity ({$validated['capacity']}) exceeds the maximum capacity of room {$classroom->room_number} ({$classroom->capacity} seats).";
            }
        }

        // 3. Collision checks (Classroom & Instructor)
        if (! empty($validated['day_of_week']) && ! empty($validated['start_time']) && ! empty($validated['end_time'])) {
            $inputDays = array_filter(array_map('trim', explode(',', str_replace(['/', '&'], ',', $validated['day_of_week']))));

            // Check classroom collision
            if (! empty($validated['classroom_id'])) {
                $cQuery = CourseSection::where('term_id', $validated['term_id'])
                    ->where('classroom_id', $validated['classroom_id'])
                    ->where('status', '!=', 'cancelled')
                    ->whereNotNull('day_of_week')
                    ->whereNotNull('start_time')
                    ->whereNotNull('end_time');

                if ($ignoreSectionId) {
                    $cQuery->where('id', '!=', $ignoreSectionId);
                }

                $existingSections = $cQuery->with(['course', 'classroom'])->get();
                foreach ($existingSections as $existing) {
                    $existingDays = array_filter(array_map('trim', explode(',', str_replace(['/', '&'], ',', $existing->day_of_week))));
                    $hasCommonDay = ! empty(array_intersect(array_map('strtolower', $inputDays), array_map('strtolower', $existingDays)));

                    if ($hasCommonDay) {
                        if ($validated['start_time'] < $existing->end_time && $validated['end_time'] > $existing->start_time) {
                            $roomName = $existing->classroom->room_number ?? 'Room';

                            return "Classroom scheduling conflict: {$roomName} is already reserved by {$existing->course->code} ({$existing->section_number}) on {$existing->day_of_week} ({$existing->start_time} - {$existing->end_time}).";
                        }
                    }
                }
            }

            // Check instructor collision
            if (! empty($validated['instructor_id'])) {
                $iQuery = CourseSection::where('term_id', $validated['term_id'])
                    ->where('instructor_id', $validated['instructor_id'])
                    ->where('status', '!=', 'cancelled')
                    ->whereNotNull('day_of_week')
                    ->whereNotNull('start_time')
                    ->whereNotNull('end_time');

                if ($ignoreSectionId) {
                    $iQuery->where('id', '!=', $ignoreSectionId);
                }

                $existingSections = $iQuery->with(['course', 'instructor'])->get();
                foreach ($existingSections as $existing) {
                    $existingDays = array_filter(array_map('trim', explode(',', str_replace(['/', '&'], ',', $existing->day_of_week))));
                    $hasCommonDay = ! empty(array_intersect(array_map('strtolower', $inputDays), array_map('strtolower', $existingDays)));

                    if ($hasCommonDay) {
                        if ($validated['start_time'] < $existing->end_time && $validated['end_time'] > $existing->start_time) {
                            $instructorName = $existing->instructor->full_name ?? 'Instructor';

                            return "Faculty scheduling conflict: {$instructorName} is already assigned to teach {$existing->course->code} ({$existing->section_number}) on {$existing->day_of_week} ({$existing->start_time} - {$existing->end_time}).";
                        }
                    }
                }
            }
        }

        return null;
    }
}
