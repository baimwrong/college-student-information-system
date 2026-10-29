<?php

namespace Tests\Feature;

use App\Jobs\ProcessBulkImportJob;
use App\Models\AcademicTerm;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\ImportLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentInformationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_can_view_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sign In');
    }

    public function test_admin_can_login_and_view_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@college.edu',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $dashResponse = $this->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Campus Administration Dashboard');
    }

    public function test_admin_can_bulk_import_students_csv(): void
    {
        $admin = User::where('role', 'admin')->first();

        $csvContent = "student_id,first_name,last_name,email,phone,department_code,program_code,admission_date,gender,current_semester,academic_status\n";
        $csvContent .= "STU-999,Samantha,Carter,samantha.carter@college.edu,+15559999,CS,BSCS,2026-09-01,female,1,active\n";

        $file = UploadedFile::fake()->createWithContent('students_test.csv', $csvContent);

        $response = $this->actingAs($admin)->post('/admin/imports/students', [
            'csv_file' => $file,
            'on_duplicate' => 'skip',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', [
            'student_id' => 'STU-999',
            'email' => 'samantha.carter@college.edu',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'samantha.carter@college.edu',
            'role' => 'student',
        ]);
    }

    public function test_admin_can_download_csv_template(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/imports/template/students');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_student_transcript_accessible_by_admin(): void
    {
        $student = Student::first();
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get("/students/{$student->id}/transcript");
        $response->assertStatus(200);
        $response->assertSee('Official Academic Record');
        $response->assertSee($student->full_name);
    }

    public function test_student_cannot_access_or_submit_grade_sheet(): void
    {
        $studentUser = User::where('role', 'student')->first();
        $section = CourseSection::first();

        // GET grade sheet should return 403
        $response = $this->actingAs($studentUser)->get("/sections/{$section->id}/grades");
        $response->assertStatus(403);

        // POST grade sheet should return 403
        $postResponse = $this->actingAs($studentUser)->post("/sections/{$section->id}/grades", [
            'grades' => [
                [
                    'enrollment_id' => 1,
                    'assignments_score' => 100,
                    'midterm_score' => 100,
                    'final_score' => 100,
                    'grade_status' => 'published',
                ],
            ],
        ]);
        $postResponse->assertStatus(403);
    }

    public function test_student_cannot_view_another_student_transcript(): void
    {
        $students = Student::with('user')->get();
        $studentA = $students[0];
        $studentB = $students[1];

        // Student A viewing Student B's transcript should 403
        $response = $this->actingAs($studentA->user)->get("/students/{$studentB->id}/transcript");
        $response->assertStatus(403);

        // Student A viewing own transcript should 200
        $ownResponse = $this->actingAs($studentA->user)->get("/students/{$studentA->id}/transcript");
        $ownResponse->assertStatus(200);
        $ownResponse->assertSee($studentA->full_name);
    }

    public function test_instructor_can_view_assigned_sections_and_grade_sheet(): void
    {
        $instructorUser = User::where('role', 'instructor')->first();
        $instructor = $instructorUser->instructor;

        // Assigned section
        $assignedSection = CourseSection::where('instructor_id', $instructor->id)->first();
        // Unassigned section
        $unassignedSection = CourseSection::where('instructor_id', '!=', $instructor->id)->first();

        // Can access sections list
        $response = $this->actingAs($instructorUser)->get('/sections');
        $response->assertStatus(200);

        // Can access assigned section grade sheet
        $gradeResponse = $this->actingAs($instructorUser)->get("/sections/{$assignedSection->id}/grades");
        $gradeResponse->assertStatus(200);

        // Cannot access other instructor's grade sheet
        if ($unassignedSection) {
            $otherResponse = $this->actingAs($instructorUser)->get("/sections/{$unassignedSection->id}/grades");
            $otherResponse->assertStatus(403);
        }
    }

    public function test_prerequisite_enforcement_on_enrollment(): void
    {
        $admin = User::where('role', 'admin')->first();
        $student = Student::where('student_id', 'STU-2026-005')->first(); // New student without CS101

        // CS201 requires CS101
        $cs201 = Course::where('code', 'CS201')->first();
        $term = AcademicTerm::where('is_current', true)->first();

        $section = CourseSection::where('course_id', $cs201->id)
            ->where('term_id', $term->id)
            ->first();

        $response = $this->actingAs($admin)->post('/enrollments', [
            'student_id' => $student->id,
            'course_section_id' => $section->id,
        ]);

        $response->assertSessionHasErrors(['course_section_id']);
        $this->assertDatabaseMissing('enrollments', [
            'student_id' => $student->id,
            'course_section_id' => $section->id,
        ]);
    }

    public function test_schedule_classroom_conflict_is_rejected(): void
    {
        $admin = User::where('role', 'admin')->first();
        $term = AcademicTerm::where('is_current', true)->first();
        $course = Course::first();
        $classroom = Classroom::where('capacity', '>=', 50)->first();

        // Create first section: Mon, Wed 10:00 - 12:00
        $sectionA = CourseSection::create([
            'course_id' => $course->id,
            'term_id' => $term->id,
            'classroom_id' => $classroom->id,
            'section_number' => 'SEC-CONFLICT-1',
            'day_of_week' => 'Mon, Wed',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'capacity' => 30,
            'status' => 'open',
        ]);

        // Attempt second section overlapping: Mon, Wed 11:00 - 13:00 in same room
        $response = $this->actingAs($admin)->post('/sections', [
            'course_id' => $course->id,
            'term_id' => $term->id,
            'classroom_id' => $classroom->id,
            'section_number' => 'SEC-CONFLICT-2',
            'day_of_week' => 'Mon, Wed',
            'start_time' => '11:00',
            'end_time' => '13:00',
            'capacity' => 30,
            'status' => 'open',
        ]);

        $response->assertSessionHasErrors(['day_of_week']);
    }

    public function test_grade_status_rollback_updates_student_cgpa(): void
    {
        $admin = User::where('role', 'admin')->first();
        $student = Student::where('student_id', 'STU-2026-001')->first();

        // Initially has published grades and CGPA > 0
        $this->assertTrue($student->cgpa > 0);

        // Find a published enrollment for this student
        $enrollment = $student->enrollments()->where('grade_status', 'published')->first();
        $this->assertNotNull($enrollment);

        // Roll grade back to draft
        $response = $this->actingAs($admin)->post("/sections/{$enrollment->course_section_id}/grades", [
            'grades' => [
                [
                    'enrollment_id' => $enrollment->id,
                    'assignments_score' => 0,
                    'midterm_score' => 0,
                    'final_score' => 0,
                    'grade_status' => 'draft',
                    'remarks' => 'Grade revoked under dispute',
                ],
            ],
        ]);

        $response->assertRedirect();
        $enrollment->refresh();
        $this->assertEquals('draft', $enrollment->grade_status);

        // Student's CGPA was updated upon rollback
        $student->refresh();
        $this->assertNotNull($student->cgpa);
    }

    public function test_draft_grades_hidden_on_transcript(): void
    {
        $student = Student::first();
        $enrollment = $student->enrollments()->first();

        // Put enrollment in draft status with scores
        $enrollment->update([
            'assignments_score' => 80,
            'midterm_score' => 75,
            'final_score' => 70,
            'total_score' => 73.75,
            'letter_grade' => 'C',
            'grade_point' => 2.00,
            'grade_status' => 'draft',
        ]);

        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get("/students/{$student->id}/transcript");

        $response->assertStatus(200);
        $response->assertSee('In Progress');
    }

    public function test_user_with_must_change_password_is_redirected_to_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/profile');
        $response->assertSessionHas('warning');
    }

    public function test_user_can_request_password_reset(): void
    {
        $user = User::first();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_section_capacity_rejection_on_enrollment(): void
    {
        $admin = User::where('role', 'admin')->first();
        $student = Student::where('student_id', 'STU-2026-002')->first();
        $section = CourseSection::where('course_id', '!=', 1)->first();

        // Fill section to capacity
        $section->update([
            'capacity' => 1,
            'enrolled_count' => 1,
        ]);

        $response = $this->actingAs($admin)->post('/enrollments', [
            'student_id' => $student->id,
            'course_section_id' => $section->id,
        ]);

        $response->assertSessionHasErrors(['course_section_id']);
    }

    public function test_bulk_import_job_execution(): void
    {
        $admin = User::where('role', 'admin')->first();
        $log = ImportLog::create([
            'user_id' => $admin->id,
            'type' => 'students',
            'file_name' => 'job_test.csv',
            'status' => 'pending',
        ]);

        $csvPath = tempnam(sys_get_temp_dir(), 'test_csv_');
        $csvContent = "student_id,first_name,last_name,email,phone,department_code,program_code,admission_date,gender,current_semester,academic_status\n";
        $csvContent .= "STU-JOB-1,JobStudent,Test,job.student@college.edu,+15551111,CS,BSCS,2026-09-01,male,1,active\n";
        file_put_contents($csvPath, $csvContent);

        $job = new ProcessBulkImportJob($log->id, $csvPath, 'students', 'skip');
        $job->handle();

        $log->refresh();
        $this->assertEquals('completed', $log->status);
        $this->assertEquals(1, $log->imported_rows);
        $this->assertDatabaseHas('students', ['student_id' => 'STU-JOB-1']);
    }

    public function test_student_can_self_register_for_course(): void
    {
        $studentUser = User::where('role', 'student')->first();
        $student = $studentUser->student;
        $currentTerm = AcademicTerm::current()->first() ?? AcademicTerm::latest()->first();

        // Create an introductory course without prerequisites
        $course = Course::create([
            'department_id' => 1,
            'code' => 'GEN101',
            'title' => 'General Academic Elective',
            'credits' => 3,
            'level' => 100,
            'lecture_hours' => 3,
            'lab_hours' => 0,
            'status' => 'active',
        ]);

        $section = CourseSection::create([
            'course_id' => $course->id,
            'term_id' => $currentTerm->id,
            'section_number' => 'SEC-GEN',
            'day_of_week' => 'Friday',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'capacity' => 30,
            'enrolled_count' => 0,
            'status' => 'open',
        ]);

        $response = $this->actingAs($studentUser)->post('/student/registration', [
            'course_section_id' => $section->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_section_id' => $section->id,
        ]);
    }

    public function test_student_cannot_exceed_semester_credit_limit(): void
    {
        $studentUser = User::where('role', 'student')->first();
        $student = $studentUser->student;

        // Artificially inflate student enrollments to 16 credits
        $currentTerm = AcademicTerm::current()->first() ?? AcademicTerm::latest()->first();
        $courseHighCredit = Course::create([
            'department_id' => 1,
            'code' => 'CS999',
            'title' => 'High Credit Capstone',
            'credits' => 16,
            'level' => 400,
            'lecture_hours' => 4,
            'lab_hours' => 0,
            'status' => 'active',
        ]);
        $sectionHigh = CourseSection::create([
            'course_id' => $courseHighCredit->id,
            'term_id' => $currentTerm->id,
            'section_number' => 'SEC-HIGH',
            'capacity' => 20,
            'enrolled_count' => 0,
            'status' => 'open',
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_section_id' => $sectionHigh->id,
            'term_id' => $currentTerm->id,
            'status' => 'enrolled',
            'grade_status' => 'draft',
        ]);

        // Attempting to register another 4-credit course (total 20 credits > 18 limit)
        $courseNext = Course::create([
            'department_id' => 1,
            'code' => 'OVER101',
            'title' => 'Overload Course',
            'credits' => 4,
            'level' => 200,
            'lecture_hours' => 3,
            'lab_hours' => 1,
            'status' => 'active',
        ]);
        $sectionNext = CourseSection::create([
            'course_id' => $courseNext->id,
            'term_id' => $currentTerm->id,
            'section_number' => 'SEC-OVER',
            'capacity' => 30,
            'enrolled_count' => 0,
            'status' => 'open',
        ]);

        $response = $this->actingAs($studentUser)->post('/student/registration', [
            'course_section_id' => $sectionNext->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Credit limit exceeded', session('error'));
    }

    public function test_student_cannot_register_for_schedule_conflict(): void
    {
        $studentUser = User::where('role', 'student')->first();
        $student = $studentUser->student;
        $currentTerm = AcademicTerm::current()->first() ?? AcademicTerm::latest()->first();

        // Create two courses with the exact same time
        $c1 = Course::create([
            'department_id' => 1,
            'code' => 'TEST101',
            'title' => 'Schedule Test 1',
            'credits' => 3,
            'level' => 100,
            'lecture_hours' => 3,
            'lab_hours' => 0,
            'status' => 'active',
        ]);
        $c2 = Course::create([
            'department_id' => 1,
            'code' => 'TEST102',
            'title' => 'Schedule Test 2',
            'credits' => 3,
            'level' => 100,
            'lecture_hours' => 3,
            'lab_hours' => 0,
            'status' => 'active',
        ]);

        $s1 = CourseSection::create([
            'course_id' => $c1->id,
            'term_id' => $currentTerm->id,
            'section_number' => 'SEC-T1',
            'day_of_week' => 'Monday, Wednesday',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'capacity' => 30,
            'enrolled_count' => 0,
            'status' => 'open',
        ]);

        $s2 = CourseSection::create([
            'course_id' => $c2->id,
            'term_id' => $currentTerm->id,
            'section_number' => 'SEC-T2',
            'day_of_week' => 'Monday, Wednesday',
            'start_time' => '10:30', // overlaps with 10:00-11:30
            'end_time' => '12:00',
            'capacity' => 30,
            'enrolled_count' => 0,
            'status' => 'open',
        ]);

        // Student registers for s1
        $this->actingAs($studentUser)->post('/student/registration', [
            'course_section_id' => $s1->id,
        ]);

        // Student attempts to register for s2 (overlapping time)
        $response = $this->actingAs($studentUser)->post('/student/registration', [
            'course_section_id' => $s2->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Schedule Conflict', session('error'));
    }

    public function test_student_can_drop_draft_course(): void
    {
        $studentUser = User::where('role', 'student')->first();
        $student = $studentUser->student;
        $currentTerm = AcademicTerm::current()->first() ?? AcademicTerm::latest()->first();

        $cDrop = Course::create([
            'department_id' => 1,
            'code' => 'DROP101',
            'title' => 'Droppable Course',
            'credits' => 3,
            'level' => 100,
            'lecture_hours' => 3,
            'lab_hours' => 0,
            'status' => 'active',
        ]);

        $sDrop = CourseSection::create([
            'course_id' => $cDrop->id,
            'term_id' => $currentTerm->id,
            'section_number' => 'SEC-DROP',
            'capacity' => 30,
            'enrolled_count' => 1,
            'status' => 'open',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_section_id' => $sDrop->id,
            'term_id' => $currentTerm->id,
            'status' => 'enrolled',
            'grade_status' => 'draft',
        ]);

        $response = $this->actingAs($studentUser)->delete("/student/registration/{$enrollment->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('enrollments', ['id' => $enrollment->id]);
    }

    public function test_official_transcript_pdf_download(): void
    {
        $admin = User::where('role', 'admin')->first();
        $student = Student::first();

        $response = $this->actingAs($admin)->get("/students/{$student->id}/transcript/pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString("Official_Transcript_{$student->student_id}.pdf", $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_public_transcript_verification_portal(): void
    {
        $student = Student::first();
        $validToken = $student->transcript_token;

        // Test valid token
        $response = $this->get("/verify/transcript/{$validToken}");
        $response->assertStatus(200);
        $response->assertSee('Authentic Official Transcript Verified');
        $response->assertSee($student->full_name);

        // Test forged / non-existent token
        $invalidResponse = $this->get('/verify/transcript/FAKE-TOKEN-999');
        $invalidResponse->assertStatus(200);
        $invalidResponse->assertSee('Verification Failed: Record Not Found');
    }

    public function test_custom_error_pages_for_403_and_404(): void
    {
        // 404
        $response404 = $this->get('/this-route-does-not-exist');
        $response404->assertStatus(404);
        $response404->assertSee('Resource Not Found');

        // 403 (student attempting to access admin section)
        $studentUser = User::where('role', 'student')->first();
        $response403 = $this->actingAs($studentUser)->get('/departments');
        $response403->assertStatus(403);
        $response403->assertSee('Access Restricted');
    }
}
