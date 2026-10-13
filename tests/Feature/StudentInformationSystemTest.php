<?php

namespace Tests\Feature;

use App\Models\AcademicProgram;
use App\Models\Department;
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

    public function test_student_transcript_accessible(): void
    {
        $student = Student::first();
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get("/students/{$student->id}/transcript");
        $response->assertStatus(200);
        $response->assertSee('Official Academic Record');
        $response->assertSee($student->full_name);
    }
}
