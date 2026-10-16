<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount(['programs', 'instructors', 'students', 'courses'])->latest()->paginate(10);

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:departments,code'],
            'name' => ['required', 'string', 'max:255'],
            'faculty_name' => ['nullable', 'string', 'max:255'],
            'head_of_department' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        Department::create($validated);

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:departments,code,'.$department->id],
            'name' => ['required', 'string', 'max:255'],
            'faculty_name' => ['nullable', 'string', 'max:255'],
            'head_of_department' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $department->update($validated);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->students()->exists() || $department->courses()->exists()) {
            return back()->with('error', 'Cannot delete department with associated students or courses.');
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}
