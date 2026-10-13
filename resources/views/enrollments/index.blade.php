@extends('layouts.app')

@section('title', 'Student Enrollments')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Student Course Enrollments</h2>
        <p class="text-muted mb-0">Track course registrations, academic terms, and semester grade records.</p>
    </div>
    <div>
        <a href="{{ route('enrollments.create') }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-card-plus me-1"></i> Register Student in Course
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('enrollments.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by student name, ID..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="term_id" class="form-select form-select-sm">
                    <option value="">All Academic Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>
                            {{ $term->name }} ({{ $term->academic_year }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">Filter</button>
                @if(request()->hasAny(['search', 'term_id', 'status']))
                    <a href="{{ route('enrollments.index') }}" class="btn btn-sm btn-light border" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Student ID & Name</th>
                    <th>Course Code & Title</th>
                    <th>Term</th>
                    <th>Section</th>
                    <th>Score</th>
                    <th>Letter Grade</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enr)
                    <tr>
                        <td>
                            <a href="{{ route('students.show', $enr->student) }}" class="fw-bold text-decoration-none">
                                {{ $enr->student->student_id ?? 'N/A' }}
                            </a>
                            <div class="fw-semibold text-dark">{{ $enr->student->full_name ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <strong>{{ $enr->courseSection->course->code ?? 'N/A' }}</strong>
                            <small class="d-block text-muted">{{ $enr->courseSection->course->title ?? '' }}</small>
                        </td>
                        <td><small class="badge bg-light text-secondary border">{{ $enr->term->name ?? 'N/A' }}</small></td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $enr->courseSection->section_number ?? 'SEC-A' }}</span></td>
                        <td>
                            @if($enr->total_score !== null)
                                <strong>{{ number_format($enr->total_score, 1) }}%</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($enr->letter_grade)
                                <span class="badge bg-primary">{{ $enr->letter_grade }} ({{ number_format($enr->grade_point, 2) }})</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">In Progress</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $enr->grade_status === 'published' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} border">
                                {{ ucfirst($enr->grade_status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('enrollments.destroy', $enr) }}" class="d-inline" onsubmit="return confirm('Drop this student from course?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Drop Enrollment">
                                    <i class="bi bi-trash"></i> Drop
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-card-checklist fs-1 text-muted d-block mb-2"></i>
                            No enrollment records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($enrollments->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $enrollments->links() }}
        </div>
    @endif
</div>
@endsection
