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
        $query = CourseSection::with(['course.department', 'term', 'instructor', 'classroom']);

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

        if ($request->filled('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
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

        CourseSection::create($validated);

        return redirect()->route('sections.index')->with('success', 'Class section scheduled successfully.');
    }

    public function show(CourseSection $section)
    {
        $section->load([
            'course.department',
            'term',
            'instructor',
            'classroom',
            'enrollments.student.program'
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
}
