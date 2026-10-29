@extends('layouts.app')

@section('title', 'Student Portal')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Student Academic Portal</h2>
        <p class="text-muted mb-0">
            Welcome, <strong>{{ $student->full_name ?? auth()->user()->name }}</strong> 
            (ID: <span class="badge bg-light text-dark border">{{ $student->student_id ?? 'N/A' }}</span>)
        </p>
    </div>
    @if($student)
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('student.registration') }}" class="btn btn-primary fw-semibold shadow-sm">
                <i class="bi bi-pencil-square me-1"></i> Register for Courses
            </a>
            <a href="{{ route('student.schedule') }}" class="btn btn-outline-secondary fw-semibold shadow-sm">
                <i class="bi bi-calendar3-week me-1"></i> My Schedule
            </a>
            <a href="{{ route('students.transcript', $student) }}" class="btn btn-outline-primary fw-semibold shadow-sm">
                <i class="bi bi-file-earmark-medical me-1"></i> View Transcript
            </a>
        </div>
    @endif
</div>

@if($student)
<!-- Academic Standing Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Cumulative GPA</span>
                    <h3 class="fw-bold my-1 text-primary">{{ number_format($student->cgpa, 2) }}</h3>
                    <small class="text-muted">Scale 4.00</small>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-star-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Credits Earned</span>
                    <h3 class="fw-bold my-1 text-success">{{ $student->total_credits_earned }}</h3>
                    <small class="text-muted">Required: {{ $student->program->total_credits_required ?? 120 }}</small>
                </div>
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-award-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Semester</span>
                    <h3 class="fw-bold my-1 text-dark">Term {{ $student->current_semester }}</h3>
                    <small class="text-muted">{{ $student->program->code ?? 'Degree' }}</small>
                </div>
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Status</span>
                    <h4 class="fw-bold my-1 text-success">{{ ucfirst($student->academic_status) }}</h4>
                    <small class="text-muted">Admitted: {{ $student->admission_date ? $student->admission_date->format('M Y') : 'N/A' }}</small>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-shield-check"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Current Enrolled Classes -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-book-half text-primary"></i> Current Term Registered Courses ({{ $currentTerm->name ?? 'Active' }})
        </h5>
        <span class="badge bg-primary-subtle text-primary">{{ $enrolledCourses->count() }} Registered Courses</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Course Code & Title</th>
                    <th>Credits</th>
                    <th>Instructor</th>
                    <th>Schedule</th>
                    <th>Classroom</th>
                    <th>Grade Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrolledCourses as $enr)
                    @php $sec = $enr->courseSection; @endphp
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $sec->course->code ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $sec->course->title ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $sec->course->credits ?? 0 }}</td>
                        <td>
                            <small>{{ $sec->instructor->full_name ?? 'Instructor TBA' }}</small>
                        </td>
                        <td>
                            <small><i class="bi bi-clock me-1 text-muted"></i> {{ $sec->schedule_summary }}</small>
                        </td>
                        <td>
                            <small><i class="bi bi-geo-alt me-1 text-muted"></i> {{ $sec->classroom->room_number ?? 'TBA' }}</small>
                        </td>
                        <td>
                            @if($enr->grade_status === 'published' && $enr->letter_grade)
                                <span class="badge bg-success">{{ $enr->letter_grade }} ({{ number_format($enr->grade_point, 2) }})</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">In Progress</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            You are not currently enrolled in any courses for this term. Please see your academic advisor.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<div class="alert alert-info">
    Your user account is not currently linked to a student matriculation record. Please contact the administrator.
</div>
@endif
@endsection
