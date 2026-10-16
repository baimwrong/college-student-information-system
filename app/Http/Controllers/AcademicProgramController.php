<?php

namespace App\Http\Controllers;

use App\Models\AcademicProgram;
use App\Models\Department;
use Illuminate\Http\Request;

class AcademicProgramController extends Controller
{
    public function index()
    {
        $programs = AcademicProgram::with('department')->withCount('students')->latest()->paginate(10);
        $departments = Department::where('status', 'active')->get();

        return view('programs.index', compact('programs', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->get();

        return view('programs.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'code' => ['required', 'string', 'max:20', 'unique:academic_programs,code'],
            'name' => ['required', 'string', 'max:255'],
            'degree_level' => ['required', 'in:undergraduate,postgraduate,diploma,doctorate'],
            'duration_years' => ['required', 'integer', 'min:1', 'max:10'],
            'total_credits_required' => ['required', 'integer', 'min:30', 'max:300'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        AcademicProgram::create($validated);

        return redirect()->route('programs.index')->with('success', 'Academic program created successfully.');
    }

    public function edit(AcademicProgram $program)
    {
        $departments = Department::where('status', 'active')->get();

        return view('programs.edit', compact('program', 'departments'));
    }

    public function update(Request $request, AcademicProgram $program)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'code' => ['required', 'string', 'max:20', 'unique:academic_programs,code,'.$program->id],
            'name' => ['required', 'string', 'max:255'],
            'degree_level' => ['required', 'in:undergraduate,postgraduate,diploma,doctorate'],
            'duration_years' => ['required', 'integer', 'min:1', 'max:10'],
            'total_credits_required' => ['required', 'integer', 'min:30', 'max:300'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $program->update($validated);

        return redirect()->route('programs.index')->with('success', 'Academic program updated successfully.');
    }

    public function destroy(AcademicProgram $program)
    {
        if ($program->students()->exists()) {
            return back()->with('error', 'Cannot delete program with enrolled students.');
        }

        $program->delete();

        return redirect()->route('programs.index')->with('success', 'Academic program deleted successfully.');
    }
}
