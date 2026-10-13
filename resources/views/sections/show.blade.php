@extends('layouts.app')

@section('title', 'Section Roster - ' . $section->course->code)

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('sections.index') }}">Sections</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $section->course->code }} ({{ $section->section_number }})</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">{{ $section->course->code }}: {{ $section->course->title }}</h2>
        <p class="text-muted mb-0">
            Section: <strong>{{ $section->section_number }}</strong> | 
            Term: <strong>{{ $section->term->name ?? 'N/A' }}</strong> | 
            Schedule: <strong>{{ $section->schedule_summary }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sections.grades', $section) }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-pencil-square me-1"></i> Open Batch Grade Sheet
        </a>
        @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('enrollments.create') }}" class="btn btn-outline-primary fw-semibold">
                <i class="bi bi-person-plus me-1"></i> Register Student
            </a>
            <a href="{{ route('sections.edit', $section) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Edit Section
            </a>
        @endif
    </div>
</div>

<!-- Section Overview Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <span class="text-muted small fw-semibold text-uppercase">Instructor</span>
            <h5 class="fw-bold my-1 text-dark">{{ $section->instructor->full_name ?? 'Unassigned' }}</h5>
            <small class="text-muted">{{ $section->instructor->designation ?? 'Faculty' }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <span class="text-muted small fw-semibold text-uppercase">Classroom Facility</span>
            <h5 class="fw-bold my-1 text-primary">{{ $section->classroom->room_number ?? 'TBA' }}</h5>
            <small class="text-muted">{{ $section->classroom->building ?? 'Campus' }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <span class="text-muted small fw-semibold text-uppercase">Enrolled Roster</span>
            <h4 class="fw-bold my-1 text-success">{{ $section->enrolled_count }} / {{ $section->capacity }}</h4>
            <small class="text-muted">{{ $section->capacity - $section->enrolled_count }} seats remaining</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <span class="text-muted small fw-semibold text-uppercase">Section Status</span>
            <h4 class="fw-bold my-1 text-uppercase {{ $section->status === 'open' ? 'text-success' : 'text-secondary' }}">{{ $section->status }}</h4>
            <small class="text-muted">{{ $section->term->academic_year ?? 'Academic Year' }}</small>
        </div>
    </div>
</div>

<!-- Enrolled Students Roster -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-people-fill text-primary"></i> Enrolled Student Roster
        </h5>
        <span class="badge bg-light text-dark border">{{ $section->enrollments->count() }} Students Enrolled</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Program</th>
                    <th>Email</th>
                    <th>Grade Status</th>
                    <th>Score</th>
                    <th>Letter Grade</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($section->enrollments as $enr)
                    <tr>
                        <td>
                            <a href="{{ route('students.show', $enr->student) }}" class="fw-bold text-decoration-none">
                                {{ $enr->student->student_id ?? 'N/A' }}
                            </a>
                        </td>
                        <td>{{ $enr->student->full_name ?? 'N/A' }}</td>
                        <td><small class="badge bg-light text-dark border">{{ $enr->student->program->code ?? 'N/A' }}</small></td>
                        <td><small class="text-muted">{{ $enr->student->email ?? '' }}</small></td>
                        <td>
                            <span class="badge {{ $enr->grade_status === 'published' ? 'bg-success-subtle text-success' : ($enr->grade_status === 'submitted' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning') }} border">
                                {{ ucfirst($enr->grade_status) }}
                            </span>
                        </td>
                        <td>{{ $enr->total_score !== null ? number_format($enr->total_score, 1) . '%' : '-' }}</td>
                        <td>
                            @if($enr->letter_grade)
                                <span class="badge bg-primary">{{ $enr->letter_grade }} ({{ number_format($enr->grade_point, 2) }})</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('enrollments.destroy', $enr) }}" class="d-inline" onsubmit="return confirm('Drop this student from section?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Drop Enrollment">
                                    <i class="bi bi-person-dash"></i> Drop
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-people fs-1 text-muted d-block mb-2"></i>
                            No students registered in this class section yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
