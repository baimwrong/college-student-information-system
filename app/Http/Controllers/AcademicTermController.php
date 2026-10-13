<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use Illuminate\Http\Request;

class AcademicTermController extends Controller
{
    public function index()
    {
        $terms = AcademicTerm::withCount(['courseSections', 'enrollments'])->latest('start_date')->paginate(10);
        return view('terms.index', compact('terms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:academic_terms,code'],
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:20'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'status' => ['required', 'in:upcoming,active,completed'],
        ]);

        $isCurrent = $request->boolean('is_current');

        if ($isCurrent) {
            AcademicTerm::where('is_current', true)->update(['is_current' => false]);
        }

        $validated['is_current'] = $isCurrent;
        AcademicTerm::create($validated);

        return redirect()->route('terms.index')->with('success', 'Academic term created successfully.');
    }

    public function update(Request $request, AcademicTerm $term)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:academic_terms,code,' . $term->id],
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:20'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', 'in:upcoming,active,completed'],
        ]);

        $term->update($validated);

        return redirect()->route('terms.index')->with('success', 'Academic term updated successfully.');
    }

    public function setCurrent(AcademicTerm $term)
    {
        AcademicTerm::where('id', '!=', $term->id)->update(['is_current' => false]);
        $term->update(['is_current' => true, 'status' => 'active']);

        return back()->with('success', "Active academic term switched to {$term->name}.");
    }

    public function destroy(AcademicTerm $term)
    {
        if ($term->courseSections()->exists() || $term->enrollments()->exists()) {
            return back()->with('error', 'Cannot delete academic term with sections or enrollments.');
        }

        $term->delete();
        return redirect()->route('terms.index')->with('success', 'Academic term deleted successfully.');
    }
}
