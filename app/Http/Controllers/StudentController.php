<?php

namespace App\Http\Controllers;

use App\Models\AcademicProgram;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['department', 'program', 'user']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('academic_status')) {
            $query->where('academic_status', $request->academic_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(10)->withQueryString();
        $departments = Department::where('status', 'active')->get();
        $programs = AcademicProgram::where('status', 'active')->get();

        return view('students.index', compact('students', 'departments', 'programs'));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->get();
        $programs = AcademicProgram::where('status', 'active')->get();

        return view('students.create', compact('departments', 'programs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string', 'max:30', 'unique:students,student_id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:students,email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'department_id' => ['required', 'exists:departments,id'],
            'program_id' => ['required', 'exists:academic_programs,id'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['required', 'in:male,female,other'],
            'address' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'admission_date' => ['required', 'date'],
            'current_semester' => ['required', 'integer', 'min:1', 'max:12'],
            'academic_status' => ['required', 'in:active,probation,graduated,suspended,withdrawn'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $password = $request->filled('password') ? $validated['password'] : 'password123';

            $user = User::create([
                'name' => "{$validated['first_name']} {$validated['last_name']}",
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => 'student',
                'phone' => $validated['phone'] ?? null,
                'status' => in_array($validated['academic_status'], ['active', 'probation', 'graduated']) ? 'active' : 'inactive',
            ]);

            $validated['user_id'] = $user->id;
            Student::create($validated);
        });

        return redirect()->route('students.index')->with('success', 'Student admitted and student portal account created.');
    }

    public function show(Student $student)
    {
        $student->load([
            'department',
            'program',
            'enrollments.courseSection.course',
            'enrollments.courseSection.instructor',
            'enrollments.term',
        ]);

        // Group enrollments by term
        $enrollmentsByTerm = $student->enrollments->groupBy(fn ($item) => $item->term->name ?? 'Unknown Term');

        return view('students.show', compact('student', 'enrollmentsByTerm'));
    }

    public function edit(Student $student)
    {
        $departments = Department::where('status', 'active')->get();
        $programs = AcademicProgram::where('status', 'active')->get();

        return view('students.edit', compact('student', 'departments', 'programs'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string', 'max:30', 'unique:students,student_id,'.$student->id],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:students,email,'.$student->id, 'unique:users,email,'.($student->user_id ?? 0)],
            'phone' => ['nullable', 'string', 'max:30'],
            'department_id' => ['required', 'exists:departments,id'],
            'program_id' => ['required', 'exists:academic_programs,id'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['required', 'in:male,female,other'],
            'address' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'admission_date' => ['required', 'date'],
            'current_semester' => ['required', 'integer', 'min:1', 'max:12'],
            'academic_status' => ['required', 'in:active,probation,graduated,suspended,withdrawn'],
        ]);

        DB::transaction(function () use ($validated, $student) {
            $student->update($validated);

            if ($student->user) {
                $student->user->update([
                    'name' => "{$validated['first_name']} {$validated['last_name']}",
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'status' => in_array($validated['academic_status'], ['active', 'probation', 'graduated']) ? 'active' : 'inactive',
                ]);
            }
        });

        return redirect()->route('students.show', $student)->with('success', 'Student record updated successfully.');
    }

    public function transcript(Student $student)
    {
        $user = Auth::user();

        // Enforce authorization: Admin, Staff, or the student themselves
        if ($user->isStudent()) {
            $studentProfile = $user->student ?? Student::where('email', $user->email)->first();
            if (! $studentProfile || $studentProfile->id !== $student->id) {
                abort(403, 'Unauthorized. You may only view your own academic transcript.');
            }
        } elseif (! $user->isAdmin() && ! $user->isStaff()) {
            abort(403, 'Unauthorized to view this student transcript.');
        }

        $student->load([
            'department',
            'program',
            'enrollments.courseSection.course',
            'enrollments.term',
        ]);

        // Group enrollments by term
        $termsData = $student->enrollments
            ->groupBy(fn ($item) => $item->term->id ?? 0)
            ->map(function ($enrollments) {
                $term = $enrollments->first()->term ?? null;
                $termCredits = 0;
                $termPoints = 0;

                foreach ($enrollments as $e) {
                    if ($e->grade_status === 'published' && $e->grade_point !== null) {
                        $c = $e->courseSection->course->credits ?? 0;
                        $termCredits += $c;
                        $termPoints += ($e->grade_point * $c);
                    }
                }

                $sgpa = $termCredits > 0 ? round($termPoints / $termCredits, 2) : 0.00;

                return [
                    'term' => $term,
                    'enrollments' => $enrollments,
                    'credits' => $termCredits,
                    'sgpa' => $sgpa,
                ];
            });

        return view('students.transcript', compact('student', 'termsData'));
    }

    public function destroy(Student $student)
    {
        if ($student->enrollments()->exists()) {
            return back()->with('error', 'Cannot delete student with historical course enrollments.');
        }

        DB::transaction(function () use ($student) {
            $user = $student->user;
            $student->delete();
            if ($user) {
                $user->delete();
            }
        });

        return redirect()->route('students.index')->with('success', 'Student record deleted successfully.');
    }
}
