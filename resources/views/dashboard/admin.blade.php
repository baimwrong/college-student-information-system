@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Campus Administration Dashboard</h2>
        <p class="text-muted mb-0">
            Current Academic Session: 
            <span class="badge bg-primary px-3 py-1">{{ $currentTerm->name ?? 'No Active Term' }} ({{ $currentTerm->academic_year ?? 'N/A' }})</span>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.imports.index') }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Bulk Data Import Hub
        </a>
        <a href="{{ route('students.create') }}" class="btn btn-outline-primary fw-semibold">
            <i class="bi bi-person-plus-fill me-1"></i> Register Student
        </a>
        <a href="{{ route('sections.create') }}" class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-calendar-plus me-1"></i> Schedule Class
        </a>
    </div>
</div>

<!-- Metrics Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3 border-0 shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Total Students</span>
                    <h3 class="fw-bold my-1 text-slate-800">{{ number_format($totalStudents) }}</h3>
                    <small class="text-success fw-semibold"><i class="bi bi-arrow-up-right"></i> Active Matriculation</small>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3 border-0 shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Faculty Members</span>
                    <h3 class="fw-bold my-1 text-slate-800">{{ number_format($totalInstructors) }}</h3>
                    <small class="text-primary fw-semibold"><i class="bi bi-mortarboard"></i> Academic Staff</small>
                </div>
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3 border-0 shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Courses Catalog</span>
                    <h3 class="fw-bold my-1 text-slate-800">{{ number_format($totalCourses) }}</h3>
                    <small class="text-muted">{{ $totalDepartments }} Departments</small>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-journal-text"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3 border-0 shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Term Enrollments</span>
                    <h3 class="fw-bold my-1 text-slate-800">{{ number_format($activeEnrollments) }}</h3>
                    <small class="text-success fw-semibold"><i class="bi bi-check2-circle"></i> Registered Seats</small>
                </div>
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-card-checklist"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Department Distribution & Quick Breakdown -->
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-diagram-3-fill text-primary"></i> Students by Department
                </h6>
                <a href="{{ route('departments.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
            </div>
            <div class="card-body p-4">
                @if($departmentStats->isEmpty())
                    <p class="text-muted text-center py-4">No departments found.</p>
                @else
                    @foreach($departmentStats as $dept)
                        @php
                            $percentage = $totalStudents > 0 ? round(($dept->students_count / $totalStudents) * 100) : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark">{{ $dept->name }} ({{ $dept->code }})</span>
                                <span class="badge bg-light text-dark border">{{ $dept->students_count }} Students ({{ $percentage }}%)</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart-fill text-success"></i> Academic Grade Distribution
                </h6>
                <span class="badge bg-secondary-subtle text-secondary">All Semesters</span>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-around text-center py-3">
                    <div>
                        <h4 class="fw-bold text-success mb-0">{{ $gradeDistribution['A'] }}</h4>
                        <span class="badge bg-success-subtle text-success mt-1">Grade A</span>
                    </div>
                    <div>
                        <h4 class="fw-bold text-primary mb-0">{{ $gradeDistribution['B'] }}</h4>
                        <span class="badge bg-primary-subtle text-primary mt-1">Grade B</span>
                    </div>
                    <div>
                        <h4 class="fw-bold text-warning mb-0">{{ $gradeDistribution['C'] }}</h4>
                        <span class="badge bg-warning-subtle text-warning mt-1">Grade C</span>
                    </div>
                    <div>
                        <h4 class="fw-bold text-secondary mb-0">{{ $gradeDistribution['D'] }}</h4>
                        <span class="badge bg-secondary-subtle text-secondary mt-1">Grade D</span>
                    </div>
                    <div>
                        <h4 class="fw-bold text-danger mb-0">{{ $gradeDistribution['F'] }}</h4>
                        <span class="badge bg-danger-subtle text-danger mt-1">Grade F</span>
                    </div>
                </div>

                <div class="bg-light p-3 rounded-3 mt-3 border">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-lightning-charge-fill text-warning"></i>
                        <span class="fw-semibold small">Pro-Tip for Administrators:</span>
                    </div>
                    <p class="small text-muted mb-0">
                        Use the <strong>Bulk Import Hub</strong> to ingest hundreds of students, faculty rosters, curriculum catalogs, and grade records in seconds using standard CSV formats.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Students & Recent Enrollments -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-person-check-fill text-primary"></i> Recently Admitted Students
                </h6>
                <a href="{{ route('students.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentStudents as $student)
                            <tr>
                                <td>
                                    <a href="{{ route('students.show', $student) }}" class="fw-semibold text-decoration-none">
                                        {{ $student->student_id }}
                                    </a>
                                </td>
                                <td>{{ $student->full_name }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $student->program->code ?? 'N/A' }}</span></td>
                                <td>
                                    <span class="badge bg-success-subtle text-success">{{ ucfirst($student->academic_status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-journal-check text-success"></i> Recent Course Registrations
                </h6>
                <a href="{{ route('enrollments.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Term</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentEnrollments as $enr)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $enr->student->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $enr->student->student_id ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $enr->courseSection->course->code ?? 'N/A' }}</span>
                                    <small class="d-block text-muted">{{ $enr->courseSection->section_number ?? '' }}</small>
                                </td>
                                <td><small class="badge bg-light text-secondary border">{{ $enr->term->name ?? 'N/A' }}</small></td>
                                <td>
                                    @if($enr->letter_grade)
                                        <span class="badge bg-primary">{{ $enr->letter_grade }}</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">In Progress</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No enrollments recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
