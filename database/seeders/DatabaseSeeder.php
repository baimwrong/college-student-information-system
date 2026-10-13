<?php

namespace Database\Seeders;

use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Users (Admin & Staff)
        $admin = User::create([
            'name' => 'Dr. Evelyn Vance',
            'email' => 'admin@college.edu',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+1 555-0100',
            'status' => 'active',
        ]);

        $staff = User::create([
            'name' => 'Sarah Connor',
            'email' => 'registrar@college.edu',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'phone' => '+1 555-0101',
            'status' => 'active',
        ]);

        // 2. Departments
        $csDept = Department::create([
            'code' => 'CS',
            'name' => 'Computer Science & Software Engineering',
            'faculty_name' => 'Faculty of Engineering & Computing',
            'head_of_department' => 'Dr. Alan Turing',
            'email' => 'cs@college.edu',
            'phone' => '+1 555-0110',
            'description' => 'Department pioneering algorithms, systems engineering, cybersecurity, and data intelligence.',
            'status' => 'active',
        ]);

        $mathDept = Department::create([
            'code' => 'MATH',
            'name' => 'Mathematics & Statistics',
            'faculty_name' => 'Faculty of Pure Sciences',
            'head_of_department' => 'Dr. Ada Lovelace',
            'email' => 'math@college.edu',
            'phone' => '+1 555-0111',
            'description' => 'Mathematical sciences, numerical methods, and applied probabilistic modeling.',
            'status' => 'active',
        ]);

        $bizDept = Department::create([
            'code' => 'BUS',
            'name' => 'Business Administration & Management',
            'faculty_name' => 'School of Business & Economics',
            'head_of_department' => 'Dr. Peter Drucker',
            'email' => 'business@college.edu',
            'phone' => '+1 555-0112',
            'description' => 'Developing strategic leadership, enterprise finance, and innovative corporate management.',
            'status' => 'active',
        ]);

        $eceDept = Department::create([
            'code' => 'ECE',
            'name' => 'Electrical & Computer Engineering',
            'faculty_name' => 'Faculty of Engineering & Computing',
            'head_of_department' => 'Dr. Nikola Tesla',
            'email' => 'ece@college.edu',
            'phone' => '+1 555-0113',
            'description' => 'Semiconductor architecture, embedded robotics, and power systems.',
            'status' => 'active',
        ]);

        // 3. Academic Programs
        $bscs = AcademicProgram::create([
            'department_id' => $csDept->id,
            'code' => 'BSCS',
            'name' => 'Bachelor of Science in Computer Science',
            'degree_level' => 'undergraduate',
            'duration_years' => 4,
            'total_credits_required' => 120,
            'status' => 'active',
        ]);

        $bsse = AcademicProgram::create([
            'department_id' => $csDept->id,
            'code' => 'BSSE',
            'name' => 'Bachelor of Science in Software Engineering',
            'degree_level' => 'undergraduate',
            'duration_years' => 4,
            'total_credits_required' => 124,
            'status' => 'active',
        ]);

        $bba = AcademicProgram::create([
            'department_id' => $bizDept->id,
            'code' => 'BBA',
            'name' => 'Bachelor of Business Administration',
            'degree_level' => 'undergraduate',
            'duration_years' => 4,
            'total_credits_required' => 120,
            'status' => 'active',
        ]);

        $msai = AcademicProgram::create([
            'department_id' => $csDept->id,
            'code' => 'MSAI',
            'name' => 'Master of Science in Artificial Intelligence',
            'degree_level' => 'postgraduate',
            'duration_years' => 2,
            'total_credits_required' => 36,
            'status' => 'active',
        ]);

        // 4. Academic Terms
        $spring2026 = AcademicTerm::create([
            'code' => '2026-SPRING',
            'name' => 'Spring 2026',
            'academic_year' => '2025-2026',
            'start_date' => '2026-01-15',
            'end_date' => '2026-05-30',
            'is_current' => false,
            'status' => 'completed',
        ]);

        $fall2026 = AcademicTerm::create([
            'code' => '2026-FALL',
            'name' => 'Fall 2026',
            'academic_year' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
            'status' => 'active',
        ]);

        $spring2027 = AcademicTerm::create([
            'code' => '2027-SPRING',
            'name' => 'Spring 2027',
            'academic_year' => '2026-2027',
            'start_date' => '2027-01-15',
            'end_date' => '2027-05-30',
            'is_current' => false,
            'status' => 'upcoming',
        ]);

        // 5. Classrooms
        $room101 = Classroom::create([
            'room_number' => 'LH-101',
            'building' => 'Main Academic Hall',
            'capacity' => 120,
            'type' => 'lecture_hall',
            'status' => 'available',
        ]);

        $room205 = Classroom::create([
            'room_number' => 'LH-205',
            'building' => 'Science Complex',
            'capacity' => 80,
            'type' => 'lecture_hall',
            'status' => 'available',
        ]);

        $lab301 = Classroom::create([
            'room_number' => 'LAB-301',
            'building' => 'Computing Pavilion',
            'capacity' => 40,
            'type' => 'lab',
            'status' => 'available',
        ]);

        $sem104 = Classroom::create([
            'room_number' => 'SEM-104',
            'building' => 'Management Center',
            'capacity' => 30,
            'type' => 'seminar',
            'status' => 'available',
        ]);

        // 6. Faculty Instructors & User Accounts
        $facultyData = [
            [
                'name' => 'Dr. Alan Turing',
                'email' => 'dr.alan@college.edu',
                'employee_id' => 'FAC-1001',
                'department_id' => $csDept->id,
                'designation' => 'Professor',
                'specialization' => 'Theory of Computation & Algorithms',
                'office_room' => 'CP-401',
            ],
            [
                'name' => 'Dr. Grace Hopper',
                'email' => 'dr.grace@college.edu',
                'employee_id' => 'FAC-1002',
                'department_id' => $csDept->id,
                'designation' => 'Associate Professor',
                'specialization' => 'Compilers & Programming Languages',
                'office_room' => 'CP-305',
            ],
            [
                'name' => 'Dr. Ada Lovelace',
                'email' => 'dr.ada@college.edu',
                'employee_id' => 'FAC-1003',
                'department_id' => $mathDept->id,
                'designation' => 'Professor',
                'specialization' => 'Applied Mathematics & Combinatorics',
                'office_room' => 'SCI-212',
            ],
            [
                'name' => 'Dr. Peter Drucker',
                'email' => 'dr.peter@college.edu',
                'employee_id' => 'FAC-1004',
                'department_id' => $bizDept->id,
                'designation' => 'Professor',
                'specialization' => 'Strategic Management & Leadership',
                'office_room' => 'MC-101',
            ],
        ];

        $instructors = [];
        foreach ($facultyData as $f) {
            $u = User::create([
                'name' => $f['name'],
                'email' => $f['email'],
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'status' => 'active',
            ]);

            $names = explode(' ', $f['name']);
            $inst = Instructor::create([
                'user_id' => $u->id,
                'department_id' => $f['department_id'],
                'employee_id' => $f['employee_id'],
                'first_name' => $names[0] . ' ' . ($names[1] ?? ''),
                'last_name' => $names[2] ?? $names[1],
                'email' => $f['email'],
                'phone' => '+1 555-02' . rand(10, 99),
                'designation' => $f['designation'],
                'specialization' => $f['specialization'],
                'office_room' => $f['office_room'],
                'hire_date' => '2020-08-15',
                'status' => 'active',
            ]);

            $instructors[$f['employee_id']] = $inst;
        }

        // 7. Students & User Accounts
        $studentData = [
            [
                'name' => 'Alex Mercer',
                'email' => 'student@college.edu',
                'student_id' => 'STU-2026-001',
                'first_name' => 'Alex',
                'last_name' => 'Mercer',
                'gender' => 'male',
                'program_id' => $bscs->id,
                'department_id' => $csDept->id,
                'semester' => 3,
            ],
            [
                'name' => 'John Doe',
                'email' => 'john.doe@college.edu',
                'student_id' => 'STU-2026-002',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'gender' => 'male',
                'program_id' => $bscs->id,
                'department_id' => $csDept->id,
                'semester' => 2,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@college.edu',
                'student_id' => 'STU-2026-003',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'gender' => 'female',
                'program_id' => $bsse->id,
                'department_id' => $csDept->id,
                'semester' => 3,
            ],
            [
                'name' => 'Sophia Rodriguez',
                'email' => 'sophia.rodriguez@college.edu',
                'student_id' => 'STU-2026-004',
                'first_name' => 'Sophia',
                'last_name' => 'Rodriguez',
                'gender' => 'female',
                'program_id' => $bba->id,
                'department_id' => $bizDept->id,
                'semester' => 2,
            ],
            [
                'name' => 'Michael Chang',
                'email' => 'michael.chang@college.edu',
                'student_id' => 'STU-2026-005',
                'first_name' => 'Michael',
                'last_name' => 'Chang',
                'gender' => 'male',
                'program_id' => $bscs->id,
                'department_id' => $csDept->id,
                'semester' => 1,
            ],
        ];

        $students = [];
        foreach ($studentData as $s) {
            $u = User::create([
                'name' => $s['name'],
                'email' => $s['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'status' => 'active',
            ]);

            $st = Student::create([
                'user_id' => $u->id,
                'department_id' => $s['department_id'],
                'program_id' => $s['program_id'],
                'student_id' => $s['student_id'],
                'first_name' => $s['first_name'],
                'last_name' => $s['last_name'],
                'email' => $s['email'],
                'phone' => '+1 555-03' . rand(10, 99),
                'date_of_birth' => '2004-05-14',
                'gender' => $s['gender'],
                'address' => '742 Evergreen Terrace, Springfield',
                'emergency_contact_name' => 'Mary Mercer',
                'emergency_contact_phone' => '+1 555-9999',
                'admission_date' => '2025-09-01',
                'current_semester' => $s['semester'],
                'academic_status' => 'active',
                'cgpa' => 0.00,
                'total_credits_earned' => 0,
            ]);

            $students[$s['student_id']] = $st;
        }

        // 8. Courses
        $cs101 = Course::create([
            'department_id' => $csDept->id,
            'code' => 'CS101',
            'title' => 'Introduction to Computer Science & Programming',
            'description' => 'Fundamental algorithmic concepts, structured programming paradigms, recursion, and object-oriented foundations.',
            'credits' => 3,
            'level' => 100,
            'lecture_hours' => 3,
            'lab_hours' => 0,
            'status' => 'active',
        ]);

        $cs201 = Course::create([
            'department_id' => $csDept->id,
            'code' => 'CS201',
            'title' => 'Data Structures & Algorithms',
            'description' => 'Linear data structures, trees, graphs, sorting, searching algorithms, and Big-O computational complexity analysis.',
            'credits' => 4,
            'level' => 200,
            'lecture_hours' => 3,
            'lab_hours' => 2,
            'prerequisites' => 'CS101',
            'status' => 'active',
        ]);

        $cs301 = Course::create([
            'department_id' => $csDept->id,
            'code' => 'CS301',
            'title' => 'Database Systems & PostgreSQL Architecture',
            'description' => 'Relational database theory, SQL queries, PostgreSQL 18 internals, transaction isolation, and index optimization.',
            'credits' => 3,
            'level' => 300,
            'lecture_hours' => 3,
            'lab_hours' => 1,
            'prerequisites' => 'CS201',
            'status' => 'active',
        ]);

        $math101 = Course::create([
            'department_id' => $mathDept->id,
            'code' => 'MATH101',
            'title' => 'Calculus I: Differential & Integral Analysis',
            'description' => 'Limits, continuity, differentiation rules, integration techniques, and applications to engineering models.',
            'credits' => 4,
            'level' => 100,
            'lecture_hours' => 4,
            'lab_hours' => 0,
            'status' => 'active',
        ]);

        $bus101 = Course::create([
            'department_id' => $bizDept->id,
            'code' => 'BUS101',
            'title' => 'Principles of Modern Business Management',
            'description' => 'Foundations of corporate management, organizational strategy, and marketing frameworks.',
            'credits' => 3,
            'level' => 100,
            'lecture_hours' => 3,
            'lab_hours' => 0,
            'status' => 'active',
        ]);

        // 9. Course Sections
        // Spring 2026 (Completed historical terms)
        $secSpringCS101 = CourseSection::create([
            'course_id' => $cs101->id,
            'term_id' => $spring2026->id,
            'instructor_id' => $instructors['FAC-1001']->id,
            'classroom_id' => $room101->id,
            'section_number' => 'SEC-A',
            'day_of_week' => 'Mon, Wed',
            'start_time' => '09:00',
            'end_time' => '10:30',
            'capacity' => 60,
            'enrolled_count' => 0,
            'status' => 'closed',
        ]);

        $secSpringMath101 = CourseSection::create([
            'course_id' => $math101->id,
            'term_id' => $spring2026->id,
            'instructor_id' => $instructors['FAC-1003']->id,
            'classroom_id' => $room205->id,
            'section_number' => 'SEC-A',
            'day_of_week' => 'Tue, Thu',
            'start_time' => '11:00',
            'end_time' => '13:00',
            'capacity' => 50,
            'enrolled_count' => 0,
            'status' => 'closed',
        ]);

        // Fall 2026 (Active current term)
        $secFallCS201 = CourseSection::create([
            'course_id' => $cs201->id,
            'term_id' => $fall2026->id,
            'instructor_id' => $instructors['FAC-1001']->id,
            'classroom_id' => $lab301->id,
            'section_number' => 'SEC-A',
            'day_of_week' => 'Mon, Wed',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'capacity' => 40,
            'enrolled_count' => 0,
            'status' => 'open',
        ]);

        $secFallCS301 = CourseSection::create([
            'course_id' => $cs301->id,
            'term_id' => $fall2026->id,
            'instructor_id' => $instructors['FAC-1002']->id,
            'classroom_id' => $lab301->id,
            'section_number' => 'SEC-A',
            'day_of_week' => 'Tue, Thu',
            'start_time' => '14:00',
            'end_time' => '15:30',
            'capacity' => 35,
            'enrolled_count' => 0,
            'status' => 'open',
        ]);

        $secFallBus101 = CourseSection::create([
            'course_id' => $bus101->id,
            'term_id' => $fall2026->id,
            'instructor_id' => $instructors['FAC-1004']->id,
            'classroom_id' => $sem104->id,
            'section_number' => 'SEC-A',
            'day_of_week' => 'Mon, Wed',
            'start_time' => '13:00',
            'end_time' => '14:30',
            'capacity' => 30,
            'enrolled_count' => 0,
            'status' => 'open',
        ]);

        // 10. Enrollments & Realistic Grades
        // Historical Spring 2026 enrollments for Alex Mercer (STU-2026-001)
        $enr1 = Enrollment::create([
            'student_id' => $students['STU-2026-001']->id,
            'course_section_id' => $secSpringCS101->id,
            'term_id' => $spring2026->id,
            'assignments_score' => 96.0,
            'midterm_score' => 94.0,
            'final_score' => 95.0,
            'grade_status' => 'published',
            'status' => 'completed',
        ]);
        $enr1->computeGrade(true);
        $secSpringCS101->increment('enrolled_count');

        $enr2 = Enrollment::create([
            'student_id' => $students['STU-2026-001']->id,
            'course_section_id' => $secSpringMath101->id,
            'term_id' => $spring2026->id,
            'assignments_score' => 90.0,
            'midterm_score' => 88.0,
            'final_score' => 91.0,
            'grade_status' => 'published',
            'status' => 'completed',
        ]);
        $enr2->computeGrade(true);
        $secSpringMath101->increment('enrolled_count');

        // Historical Spring 2026 for Jane Smith
        $enr3 = Enrollment::create([
            'student_id' => $students['STU-2026-003']->id,
            'course_section_id' => $secSpringCS101->id,
            'term_id' => $spring2026->id,
            'assignments_score' => 88.0,
            'midterm_score' => 85.0,
            'final_score' => 86.0,
            'grade_status' => 'published',
            'status' => 'completed',
        ]);
        $enr3->computeGrade(true);
        $secSpringCS101->increment('enrolled_count');

        // Current Fall 2026 Enrollments
        $enr4 = Enrollment::create([
            'student_id' => $students['STU-2026-001']->id,
            'course_section_id' => $secFallCS201->id,
            'term_id' => $fall2026->id,
            'assignments_score' => 92.0,
            'midterm_score' => 90.0,
            'grade_status' => 'draft',
            'status' => 'enrolled',
        ]);
        $enr4->computeGrade(true);
        $secFallCS201->increment('enrolled_count');

        $enr5 = Enrollment::create([
            'student_id' => $students['STU-2026-002']->id,
            'course_section_id' => $secFallCS201->id,
            'term_id' => $fall2026->id,
            'assignments_score' => 85.0,
            'midterm_score' => 82.0,
            'grade_status' => 'draft',
            'status' => 'enrolled',
        ]);
        $enr5->computeGrade(true);
        $secFallCS201->increment('enrolled_count');

        $enr6 = Enrollment::create([
            'student_id' => $students['STU-2026-003']->id,
            'course_section_id' => $secFallCS301->id,
            'term_id' => $fall2026->id,
            'assignments_score' => 94.0,
            'midterm_score' => 92.0,
            'grade_status' => 'draft',
            'status' => 'enrolled',
        ]);
        $enr6->computeGrade(true);
        $secFallCS301->increment('enrolled_count');

        $enr7 = Enrollment::create([
            'student_id' => $students['STU-2026-004']->id,
            'course_section_id' => $secFallBus101->id,
            'term_id' => $fall2026->id,
            'assignments_score' => 89.0,
            'midterm_score' => 87.0,
            'grade_status' => 'draft',
            'status' => 'enrolled',
        ]);
        $enr7->computeGrade(true);
        $secFallBus101->increment('enrolled_count');

        // Recalculate CGPA for all students
        foreach ($students as $student) {
            $student->recalculateGpa();
        }
    }
}
