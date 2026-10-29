<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstructorController extends Controller
{
    public function index(Request $request)
    {
        $query = Instructor::with(['department', 'user'])->withCount('courseSections');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $instructors = $query->latest()->paginate(10)->withQueryString();
        $departments = Department::where('status', 'active')->get();

        return view('instructors.index', compact('instructors', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->get();

        return view('instructors.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string', 'max:30', 'unique:instructors,employee_id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:instructors,email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation' => ['required', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'office_room' => ['nullable', 'string', 'max:50'],
            'hire_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,on_leave,retired,terminated'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $hasCustomPassword = $request->filled('password');
            $password = $hasCustomPassword ? $validated['password'] : 'password123';

            $user = User::create([
                'name' => "{$validated['first_name']} {$validated['last_name']}",
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => 'instructor',
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'] === 'active' ? 'active' : 'inactive',
                'must_change_password' => ! $hasCustomPassword,
            ]);

            $validated['user_id'] = $user->id;
            Instructor::create($validated);
        });

        return redirect()->route('instructors.index')->with('success', 'Faculty instructor created with login account.');
    }

    public function show(Instructor $instructor)
    {
        $instructor->load(['department', 'courseSections.course', 'courseSections.term', 'courseSections.classroom']);

        return view('instructors.show', compact('instructor'));
    }

    public function edit(Instructor $instructor)
    {
        $departments = Department::where('status', 'active')->get();

        return view('instructors.edit', compact('instructor', 'departments'));
    }

    public function update(Request $request, Instructor $instructor)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string', 'max:30', 'unique:instructors,employee_id,'.$instructor->id],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:instructors,email,'.$instructor->id, 'unique:users,email,'.($instructor->user_id ?? 0)],
            'phone' => ['nullable', 'string', 'max:30'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation' => ['required', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'office_room' => ['nullable', 'string', 'max:50'],
            'hire_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,on_leave,retired,terminated'],
        ]);

        DB::transaction(function () use ($validated, $instructor) {
            $instructor->update($validated);

            if ($instructor->user) {
                $instructor->user->update([
                    'name' => "{$validated['first_name']} {$validated['last_name']}",
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'status' => $validated['status'] === 'active' ? 'active' : 'inactive',
                ]);
            }
        });

        return redirect()->route('instructors.index')->with('success', 'Instructor updated successfully.');
    }

    public function destroy(Instructor $instructor)
    {
        if ($instructor->courseSections()->exists()) {
            return back()->with('error', 'Cannot delete instructor assigned to active or past course sections.');
        }

        DB::transaction(function () use ($instructor) {
            $user = $instructor->user;
            $instructor->delete();
            if ($user) {
                $user->delete();
            }
        });

        return redirect()->route('instructors.index')->with('success', 'Instructor and linked user removed successfully.');
    }
}
