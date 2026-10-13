<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::withCount('courseSections')->latest()->paginate(10);
        return view('classrooms.index', compact('classrooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:50', 'unique:classrooms,room_number'],
            'building' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'type' => ['required', 'in:lecture_hall,lab,seminar,auditorium'],
            'status' => ['required', 'in:available,maintenance'],
        ]);

        Classroom::create($validated);

        return redirect()->route('classrooms.index')->with('success', 'Classroom registered successfully.');
    }

    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:50', 'unique:classrooms,room_number,' . $classroom->id],
            'building' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'type' => ['required', 'in:lecture_hall,lab,seminar,auditorium'],
            'status' => ['required', 'in:available,maintenance'],
        ]);

        $classroom->update($validated);

        return redirect()->route('classrooms.index')->with('success', 'Classroom updated successfully.');
    }

    public function destroy(Classroom $classroom)
    {
        if ($classroom->courseSections()->exists()) {
            return back()->with('error', 'Cannot delete classroom associated with scheduled sections.');
        }

        $classroom->delete();
        return redirect()->route('classrooms.index')->with('success', 'Classroom deleted successfully.');
    }
}
