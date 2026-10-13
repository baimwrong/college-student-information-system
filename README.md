# College Student Information System (College SIS)

> **A Production-Ready, Offline-First College Student Information System built with Laravel, PostgreSQL 18, and Local Offline Bootstrap.**

---

## 🎓 Executive Summary

**College Student Information System (College SIS)** is an enterprise-grade academic platform engineered to streamline university administration, academic program management, student matriculation, faculty course scheduling, automated grade ledger computation, and official academic transcript generation.

The application is tailored for **internal intranet networks and air-gapped campus environments**, shipping with **100% self-contained local UI assets** (Bootstrap 5.3 & Bootstrap Icons webfonts) requiring **zero internet connection or external CDN access**.

---

## 🌟 Core System Features

### 1. 🛡️ Role-Based Access Control (RBAC) & Portals
- **Administrator / Registrar Staff**: Full governance over academic faculties, degree curriculums, student matriculation rosters, faculty assignments, schedule timetabling, and the administrative bulk import hub.
- **Faculty Instructors**: Dedicated teaching portal featuring assigned class section schedules, real-time student rosters, and batch grade ledger submission.
- **Students**: Self-service student portal displaying enrolled courses, classroom schedules, semester progress, credit tallies, and an **official printable academic transcript**.

### 2. 🏛️ Academic Structure & Scheduling
- **Faculties & Departments**: Department codes, leadership chairs, contact records, and divisional affiliations.
- **Degree Programs**: Undergraduate, Postgraduate, Doctorate, and Diploma curricula with duration and credit graduation quotas.
- **Academic Terms**: Semester scheduling (Active, Upcoming, Completed) with seamless one-click active session switching.
- **Campus Classrooms & Laboratories**: Room numbers, building complexes, seat capacities, and facility types (lecture halls, computer labs, seminar suites).
- **Curriculum Catalog**: Course catalog with credit units, difficulty levels (100–500), lecture/lab breakdown, prerequisites, and syllabi.
- **Class Sections & Timetable**: Multi-section scheduling with instructor assignments, room capacity tracking, and collision prevention.

### 3. 📊 Automated Grading Engine & Cumulative GPA (CGPA) Synchronization
- **Configurable Score Composition**:
  $$\text{Composite Score} = (\text{Assignments} \times 20\%) + (\text{Midterm Exam} \times 35\%) + (\text{Final Exam} \times 45\%)$$
- **Standard 4.0 Academic Grading Scale**:
  - `A` (93–100%) = **4.00**
  - `A-` (90–92.9%) = **3.70**
  - `B+` (87–89.9%) = **3.30**
  - `B` (83–86.9%) = **3.00**
  - `B-` (80–82.9%) = **2.70**
  - `C+` (77–79.9%) = **2.30**
  - `C` (73–76.9%) = **2.00**
  - `C-` (70–72.9%) = **1.70**
  - `D` (60–69.9%) = **1.00**
  - `F` (<60%) = **0.00**
- **Batch Grade Sheet**: Instructors and administrators can enter scores for an entire section simultaneously.
- **Real-Time GPA Sync**: Publishing grades automatically recalculates each student's semester GPA (SGPA), cumulative CGPA, and earned credit hours.
- **Official Printable Academic Transcript**: Fully formatted transcript document with university letterhead, term-by-term performance, and registrar seal certification.

### 4. ⚡ High-Throughput Bulk Import & Export Hub
Located at `/admin/imports`, the Bulk Ingestion Hub enables administrators to ingest thousands of records in seconds:
- **Supported Entities**:
  1. `Students` (Student ID, Name, Email, Department, Program, Admission Date, Semester, Status)
  2. `Faculty Instructors` (Employee ID, Name, Email, Department, Designation, Specialization, Office)
  3. `Courses` (Code, Title, Department, Credits, Level, Lecture/Lab Hours, Prerequisites)
  4. `Enrollments & Grades` (Student ID, Course Code, Term Code, Section, Scores, Status)
- **Automatic User Provisioning**: Importing students or instructors automatically creates their corresponding authentication user accounts with default credentials.
- **Intelligent CSV Ingestion**:
  - Delimiter auto-detection (comma `,`, semicolon `;`, tab `\t`).
  - UTF-8 Byte Order Mark (BOM) sanitization.
  - Conflict resolution strategies: **Skip Existing** or **Update Existing**.
- **Downloadable Sample CSV Templates**: Dynamic one-click downloads with headers and pre-filled valid example rows.
- **Persistent Audit Logging**: Detailed audit records (`import_logs` table) logging processed, created, updated, and rejected counts, complete with row-level error diagnostic reports.
- **CSV Data Export**: One-click database export for Students, Faculty, and Courses.

### 5. 🌐 100% Offline / Intranet Network Ready
- **No Internet Required**: All CSS stylesheets, JS bundles, and icon font files reside directly in `public/vendor/`.
  - Bootstrap 5.3: `public/vendor/bootstrap/css/bootstrap.min.css` & `public/vendor/bootstrap/js/bootstrap.bundle.min.js`
  - Bootstrap Icons: `public/vendor/bootstrap-icons/bootstrap-icons.min.css` & `public/vendor/bootstrap-icons/fonts/*` (WOFF & WOFF2)
- Zero external calls to Google Fonts, CDNs, or external APIs.

---

## 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| **Framework** | Laravel 12.x / 13.x (PHP 8.2+) |
| **Primary Database** | **PostgreSQL 18** (with SQLite fallback for local preview) |
| **Front-End Styling** | Bootstrap 5.3 (Self-Hosted Local Bundle) |
| **Iconography** | Bootstrap Icons v1.13+ (Local Webfonts & CSS) |
| **Local Environment** | Laragon / XAMPP / Apache / Nginx |
| **Test Suite** | PHPUnit / Pest Feature & Unit Tests |

---

## 💾 PostgreSQL 18 Configuration Guide

### 1. Database Configuration Settings
The application is pre-configured for PostgreSQL 18 in `.env.example` and `config/database.php`.

To connect to your PostgreSQL 18 instance (e.g. running via Laragon or standalone PostgreSQL service), set the following variables in your `.env` file:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=college_sis
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
DB_SSLMODE=prefer
```

### 2. Creating the Database in PostgreSQL 18
You can create the database using `psql`, pgAdmin, or Laragon's terminal:

```sql
CREATE DATABASE college_sis WITH OWNER = postgres ENCODING = 'UTF8';
```

### 3. Running Migrations & Seeding in PostgreSQL 18
Once PostgreSQL 18 is active:

```bash
php artisan migrate --seed
```

> **Note on Local SQLite Fallback**:
> If PostgreSQL 18 is not yet running on your workstation, you can run and test the application immediately using the pre-seeded SQLite database:
> ```env
> DB_CONNECTION=sqlite
> ```

---

## 🚀 Installation & Setup Instructions

### 1. Clone or Open the Project
Open a terminal in the project directory:
```bash
cd college-student-information-system
```

### 2. Configure Environment
Copy the example environment file if not already present:
```bash
cp .env.example .env
```
Ensure your application key is generated:
```bash
php artisan key:generate
```

### 3. Initialize Database & Seed Demo Data
Run the migrations and database seeder:
```bash
php artisan migrate:fresh --seed
```

### 4. Serve the Application
You can serve the application through your preferred web server:

**Option A: Built-in Artisan Development Server**
```bash
php artisan serve
```
Then navigate to: `http://localhost:8000`

**Option B: Laragon / Apache Virtual Host**
- If using Laragon, create a host pointing to `c:/xampp/htdocs/college-student-information-system/public` or access via `http://localhost/college-student-information-system/public`.

---

## 🔑 Default Login Credentials

The seeder populates realistic accounts for all user roles:

| Role | Email Address | Password | Name & Description |
|---|---|---|---|
| **System Admin** | `admin@college.edu` | `password` | Dr. Evelyn Vance (Chief Academic Officer) |
| **Academic Registrar** | `registrar@college.edu` | `password` | Sarah Connor (University Registrar) |
| **Faculty Instructor** | `dr.alan@college.edu` | `password` | Dr. Alan Turing (CS Professor) |
| **Faculty Instructor** | `dr.grace@college.edu` | `password` | Dr. Grace Hopper (CS Associate Professor) |
| **Student** | `student@college.edu` | `password` | Alex Mercer (Matriculation ID: `STU-2026-001`) |
| **Student** | `john.doe@college.edu` | `password` | John Doe (Matriculation ID: `STU-2026-002`) |

> 💡 *Tip: The login screen contains one-click credential buttons for quick role switching.*

---

## 📥 Bulk CSV Import Specifications

When importing data in the **Bulk Import Hub** (`/admin/imports`), you can download pre-formatted CSV templates with headers and valid data.

### 1. Students CSV (`template_students.csv`)
| Column Header | Type | Description | Sample Value |
|---|---|---|---|
| `student_id` | String (Unique) | Matriculation number | `STU-2026-101` |
| `first_name` | String | First name | `John` |
| `last_name` | String | Last name | `Doe` |
| `email` | Email (Unique) | Portal login email | `john.doe@example.edu` |
| `phone` | String | Contact phone | `+1 555-0192` |
| `department_code` | String (FK) | Valid department code | `CS` |
| `program_code` | String (FK) | Valid academic program code | `BSCS` |
| `admission_date` | Date (YYYY-MM-DD) | Date enrolled | `2026-09-01` |
| `gender` | Enum | `male`, `female`, or `other` | `male` |
| `current_semester` | Integer | Semester level (1–12) | `1` |
| `academic_status` | Enum | `active`, `probation`, `graduated`, `suspended` | `active` |

### 2. Faculty Instructors CSV (`template_instructors.csv`)
| Column Header | Type | Description | Sample Value |
|---|---|---|---|
| `employee_id` | String (Unique) | Faculty employee code | `FAC-201` |
| `first_name` | String | First name | `Robert` |
| `last_name` | String | Last name | `Miller` |
| `email` | Email (Unique) | Portal login email | `robert.miller@example.edu` |
| `phone` | String | Office contact phone | `+1 555-0201` |
| `department_code` | String (FK) | Department code | `CS` |
| `designation` | String | Rank (e.g. Professor, Lecturer) | `Associate Professor` |
| `specialization` | String | Research field | `Distributed Systems` |
| `office_room` | String | Campus office room | `ENG-304` |
| `hire_date` | Date (YYYY-MM-DD) | Hire date | `2022-01-15` |

### 3. Courses CSV (`template_courses.csv`)
| Column Header | Type | Description | Sample Value |
|---|---|---|---|
| `code` | String (Unique) | Course code | `CS101` |
| `title` | String | Course title | `Introduction to Computer Science` |
| `department_code` | String (FK) | Department code | `CS` |
| `credits` | Integer | Credit units (1–12) | `3` |
| `level` | Integer | 100, 200, 300, 400, 500 | `100` |
| `lecture_hours` | Integer | Weekly lecture hours | `3` |
| `lab_hours` | Integer | Weekly lab hours | `0` |
| `prerequisites` | String | Prerequisite course codes | `None` |
| `description` | Text | Syllabus description | `Fundamental computing principles.` |

### 4. Enrollments & Grades CSV (`template_enrollments.csv`)
| Column Header | Type | Description | Sample Value |
|---|---|---|---|
| `student_id` | String (FK) | Existing student ID | `STU-2026-101` |
| `course_code` | String (FK) | Existing course code | `CS101` |
| `term_code` | String (FK) | Existing academic term | `2026-FALL` |
| `section_number` | String | Section code (defaults to SEC-A) | `SEC-A` |
| `assignments_score`| Numeric (0–100) | Assignments weighted score | `95.0` |
| `midterm_score` | Numeric (0–100) | Midterm exam score | `88.5` |
| `final_score` | Numeric (0–100) | Final exam score | `92.0` |
| `grade_status` | Enum | `draft`, `submitted`, `published` | `published` |

---

## 📁 Project Architecture & Key Directories

```
college-student-information-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php              # Authentication & Profile management
│   │   │   ├── BulkImportController.php        # CSV Bulk Ingestion Engine & Audit
│   │   │   ├── DashboardController.php         # Role-aware dashboard routing
│   │   │   ├── DepartmentController.php        # Academic faculties
│   │   │   ├── AcademicProgramController.php   # Degree majors & credit requirements
│   │   │   ├── AcademicTermController.php      # Semester sessions & active term switch
│   │   │   ├── ClassroomController.php         # Physical lecture halls & lab facilities
│   │   │   ├── InstructorController.php        # Faculty profiles & user sync
│   │   │   ├── StudentController.php           # Student matriculation & transcripts
│   │   │   ├── CourseController.php            # Course syllabus catalog
│   │   │   ├── CourseSectionController.php     # Class timetabling & capacity
│   │   │   └── EnrollmentController.php        # Registration & Batch Grade Sheets
│   │   └── Middleware/
│   │       └── EnsureUserHasRole.php           # Role-Based Access Control Middleware
│   └── Models/
│       ├── User.php                            # RBAC User entity
│       ├── Department.php                      # Academic Department model
│       ├── AcademicProgram.php                 # Degree Program model
│       ├── AcademicTerm.php                    # Semester Session model
│       ├── Classroom.php                       # Facility & Room model
│       ├── Instructor.php                      # Faculty member model
│       ├── Student.php                         # Student matriculation & CGPA model
│       ├── Course.php                          # Course curriculum model
│       ├── CourseSection.php                   # Scheduled class section model
│       ├── Enrollment.php                      # Registration, Grade Ledger & Scale
│       └── ImportLog.php                       # Bulk Import Audit trail
├── database/
│   ├── migrations/                             # 10 PostgreSQL 18 relational migrations
│   └── seeders/DatabaseSeeder.php              # Comprehensive realistic academic dataset
├── public/
│   └── vendor/                                 # 100% Offline UI Assets (No CDN)
│       ├── bootstrap/                          # Local Bootstrap 5.3 CSS & JS
│       └── bootstrap-icons/                    # Local Bootstrap Icons CSS & Webfonts
├── resources/
│   └── views/                                  # Responsive Blade UI views
│       ├── layouts/                            # Master & Auth layouts
│       ├── dashboard/                          # Admin, Faculty, & Student Dashboards
│       ├── admin/imports/                      # Bulk Ingestion Hub & Audit Reports
│       ├── students/                           # Registry, Profile & Official Transcript
│       ├── enrollments/                        # Course registration & Batch Grade Sheets
│       └── ...
└── tests/Feature/                              # Automated Feature Test Suite
```

---

## 🧪 Testing & Verification

Run the automated test suite to verify routing, authentication, GPA calculation, bulk CSV import, and transcript rendering:

```bash
php artisan test
```

All 7 feature and unit tests run with zero failures:
```text
PASS  Tests\Unit\ExampleTest
✓ that true is true

PASS  Tests\Feature\ExampleTest
✓ the application returns a successful response

PASS  Tests\Feature\StudentInformationSystemTest
✓ user can view login page
✓ admin can login and view dashboard
✓ admin can bulk import students csv
✓ admin can download csv template
✓ student transcript accessible

Tests: 7 passed (20 assertions)
```

---

## 🔒 Security Best Practices Implemented
- **CSRF Protection**: All form submissions enforce CSRF tokens.
- **SQL Injection Prevention**: Eloquent ORM and parameterized queries utilized across all models and bulk import operations.
- **Transaction Safety**: All multi-entity bulk operations and student admissions execute inside atomic database transactions (`DB::transaction`).
- **Encrypted Password Hashing**: Passwords hashed using Bcrypt with configurable cost rounds.
- **Role-Based Gateways**: Route endpoints strictly protected via `role:admin,staff`, `role:instructor`, and `role:student` middleware.

---

## 📜 License
This software is developed as an open academic system for colleges and universities. Free to adapt and deploy.
