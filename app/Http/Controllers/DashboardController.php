<?php

namespace App\Http\Controllers;

use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isStaff()) {
            $totalStudents = Student::count();
            $totalInstructors = Instructor::count();
            $totalCourses = Course::count();
            $totalDepartments = Department::count();
            $totalPrograms = AcademicProgram::count();

            $currentTerm = AcademicTerm::current()->first() ?? AcademicTerm::latest()->first();

            $activeEnrollments = $currentTerm
                ? Enrollment::where('term_id', $currentTerm->id)->where('status', 'enrolled')->count()
                : Enrollment::where('status', 'enrolled')->count();

            $recentStudents = Student::with(['department', 'program'])->latest()->take(5)->get();
            $recentEnrollments = Enrollment::with(['student', 'courseSection.course', 'term'])->latest()->take(5)->get();

            // Department distribution for charts
            $departmentStats = Department::withCount('students')->get();

            // Grade distribution
            $gradeDistribution = [
                'A' => Enrollment::where('letter_grade', 'like', 'A%')->count(),
                'B' => Enrollment::where('letter_grade', 'like', 'B%')->count(),
                'C' => Enrollment::where('letter_grade', 'like', 'C%')->count(),
                'D' => Enrollment::where('letter_grade', 'like', 'D%')->count(),
                'F' => Enrollment::where('letter_grade', 'F')->count(),
            ];

            return view('dashboard.admin', compact(
                'totalStudents',
                'totalInstructors',
                'totalCourses',
                'totalDepartments',
                'totalPrograms',
                'currentTerm',
                'activeEnrollments',
                'recentStudents',
                'recentEnrollments',
                'departmentStats',
                'gradeDistribution'
            ));
        }

        if ($user->isInstructor()) {
            $instructor = $user->instructor ?? Instructor::where('email', $user->email)->first();
            $currentTerm = AcademicTerm::current()->first() ?? AcademicTerm::latest()->first();

            $sections = collect();
            $totalTaughtStudents = 0;
            $pendingGradesCount = 0;

            if ($instructor) {
                $sections = CourseSection::with(['course', 'term', 'classroom'])
                    ->where('instructor_id', $instructor->id)
                    ->when($currentTerm, fn ($q) => $q->where('term_id', $currentTerm->id))
                    ->get();

                $sectionIds = CourseSection::where('instructor_id', $instructor->id)->pluck('id');
                $totalTaughtStudents = Enrollment::whereIn('course_section_id', $sectionIds)->count();
                $pendingGradesCount = Enrollment::whereIn('course_section_id', $sectionIds)
                    ->where('grade_status', 'draft')
                    ->count();
            }

            return view('dashboard.instructor', compact(
                'instructor',
                'currentTerm',
                'sections',
                'totalTaughtStudents',
                'pendingGradesCount'
            ));
        }

        // Student Dashboard
        $student = $user->student ?? Student::where('email', $user->email)->first();
        $currentTerm = AcademicTerm::current()->first() ?? AcademicTerm::latest()->first();

        $enrolledCourses = collect();
        if ($student) {
            $enrolledCourses = Enrollment::with(['courseSection.course', 'courseSection.instructor', 'courseSection.classroom'])
                ->where('student_id', $student->id)
                ->when($currentTerm, fn ($q) => $q->where('term_id', $currentTerm->id))
                ->get();
        }

        return view('dashboard.student', compact(
            'student',
            'currentTerm',
            'enrolledCourses'
        ));
    }
}
