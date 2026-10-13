@extends('layouts.app')

@section('title', 'Class Sections')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Class Sections & Scheduling</h2>
        <p class="text-muted mb-0">Organize course sections, assign instructors, reserve classrooms, and manage capacity.</p>
    </div>
    @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
        <div>
            <a href="{{ route('sections.create') }}" class="btn btn-primary fw-semibold shadow-sm">
                <i class="bi bi-calendar-plus me-1"></i> Schedule Class Section
            </a>
        </div>
    @endif
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('sections.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <select name="term_id" class="form-select form-select-sm">
                    <option value="">All Academic Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ (request('term_id') == $term->id || (!request()->has('term_id') && $term->is_current)) ? 'selected' : '' }}>
                            {{ $term->name }} ({{ $term->academic_year }}) {{ $term->is_current ? '• Active' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="course_id" class="form-select form-select-sm">
                    <option value="">All Courses</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->code }} - {{ $c->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">Filter Schedule</button>
                @if(request()->hasAny(['term_id', 'course_id', 'instructor_id']))
                    <a href="{{ route('sections.index') }}" class="btn btn-sm btn-light border" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
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
                    <th>Course Code & Title</th>
                    <th>Term</th>
                    <th>Section</th>
                    <th>Instructor</th>
                    <th>Schedule</th>
                    <th>Classroom</th>
                    <th>Enrollment</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sections as $sec)
                    <tr>
                        <td>
                            <a href="{{ route('sections.show', $sec) }}" class="fw-bold text-decoration-none">
                                {{ $sec->course->code ?? 'N/A' }}
                            </a>
                            <small class="d-block text-muted">{{ $sec->course->title ?? '' }}</small>
                        </td>
                        <td><small class="badge bg-light text-secondary border">{{ $sec->term->name ?? 'N/A' }}</small></td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $sec->section_number }}</span></td>
                        <td><small class="fw-semibold">{{ $sec->instructor->full_name ?? 'Instructor TBA' }}</small></td>
                        <td><small><i class="bi bi-clock me-1 text-muted"></i> {{ $sec->schedule_summary }}</small></td>
                        <td><small><i class="bi bi-geo-alt me-1 text-muted"></i> {{ $sec->classroom->room_number ?? 'TBA' }}</small></td>
                        <td>
                            <span class="badge {{ $sec->enrolled_count >= $sec->capacity ? 'bg-danger' : 'bg-info-subtle text-info border' }}">
                                {{ $sec->enrolled_count }} / {{ $sec->capacity }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $sec->status === 'open' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} border text-uppercase">
                                {{ $sec->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('sections.grades', $sec) }}" class="btn btn-sm btn-outline-primary me-1" title="Batch Grade Sheet">
                                <i class="bi bi-pencil-square"></i> Grades
                            </a>
                            <a href="{{ route('sections.show', $sec) }}" class="btn btn-sm btn-outline-secondary me-1" title="Roster Details">
                                <i class="bi bi-people"></i>
                            </a>
                            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                <a href="{{ route('sections.edit', $sec) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('sections.destroy', $sec) }}" class="d-inline" onsubmit="return confirm('Delete this section?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
                            No class sections scheduled matching the criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sections->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $sections->links() }}
        </div>
    @endif
</div>
@endsection
