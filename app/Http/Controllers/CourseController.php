<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with('department')->withCount('sections');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $courses = $query->latest()->paginate(10)->withQueryString();
        $departments = Department::where('status', 'active')->get();

        return view('courses.index', compact('courses', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->get();
        return view('courses.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'code' => ['required', 'string', 'max:30', 'unique:courses,code'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'credits' => ['required', 'integer', 'min:1', 'max:12'],
            'level' => ['required', 'integer', 'in:100,200,300,400,500'],
            'lecture_hours' => ['required', 'integer', 'min:0', 'max:20'],
            'lab_hours' => ['required', 'integer', 'min:0', 'max:20'],
            'prerequisites' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,archived'],
        ]);

        Course::create($validated);

        return redirect()->route('courses.index')->with('success', 'Course added to curriculum catalog.');
    }

    public function show(Course $course)
    {
        $course->load(['department', 'sections.term', 'sections.instructor', 'sections.classroom']);
        return view('courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        $departments = Department::where('status', 'active')->get();
        return view('courses.edit', compact('course', 'departments'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'code' => ['required', 'string', 'max:30', 'unique:courses,code,' . $course->id],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'credits' => ['required', 'integer', 'min:1', 'max:12'],
            'level' => ['required', 'integer', 'in:100,200,300,400,500'],
            'lecture_hours' => ['required', 'integer', 'min:0', 'max:20'],
            'lab_hours' => ['required', 'integer', 'min:0', 'max:20'],
            'prerequisites' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,archived'],
        ]);

        $course->update($validated);

        return redirect()->route('courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        if ($course->sections()->exists()) {
            return back()->with('error', 'Cannot delete course with active/historical sections.');
        }

        $course->delete();
        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }
}
