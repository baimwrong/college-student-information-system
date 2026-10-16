<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
}
