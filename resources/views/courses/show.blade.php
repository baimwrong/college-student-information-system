@extends('layouts.app')

@section('title', 'Course - ' . $course->code)

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">Courses</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $course->code }}</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">{{ $course->code }}: {{ $course->title }}</h2>
        <p class="text-muted mb-0">Department of {{ $course->department->name ?? 'N/A' }} | <strong>{{ $course->credits }} Credits</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('courses.edit', $course) }}" class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-pencil me-1"></i> Edit Course
        </a>
        <a href="{{ route('sections.create') }}?course_id={{ $course->id }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-calendar-plus me-1"></i> Schedule Section
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-primary"></i> Course Specifications
                </h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <small class="text-muted d-block">Credits</small>
                        <span class="fw-bold fs-5 text-primary">{{ $course->credits }} Units</span>
                    </li>
                    <li class="mb-3">
                        <small class="text-muted d-block">Academic Level</small>
                        <span class="badge bg-light text-dark border">{{ $course->level }} Level</span>
                    </li>
                    <li class="mb-3">
                        <small class="text-muted d-block">Contact Hours</small>
                        <span class="fw-semibold">{{ $course->lecture_hours }} hrs Lecture, {{ $course->lab_hours }} hrs Lab</span>
                    </li>
                    <li class="mb-3">
                        <small class="text-muted d-block">Prerequisites</small>
                        <span class="fw-semibold">{{ $course->prerequisites ?? 'None' }}</span>
                    </li>
                    <li class="mb-0">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge {{ $course->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} border">
                            {{ ucfirst($course->status) }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-card-text text-primary"></i> Course Description & Syllabus
                </h6>
            </div>
            <div class="card-body p-4">
                <p class="mb-0 text-secondary" style="line-height: 1.7;">
                    {{ $course->description ?? 'No detailed syllabus description entered for this course.' }}
                </p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-success"></i> Scheduled Class Sections
                </h6>
                <span class="badge bg-light text-dark border">{{ $course->sections->count() }} Sections</span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Term</th>
                            <th>Instructor</th>
                            <th>Classroom</th>
                            <th>Schedule</th>
                            <th>Enrolled</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($course->sections as $sec)
                            <tr>
                                <td>
                                    <a href="{{ route('sections.show', $sec) }}" class="fw-bold text-decoration-none">
                                        {{ $sec->section_number }}
                                    </a>
                                </td>
                                <td><small class="badge bg-light text-secondary border">{{ $sec->term->name ?? 'N/A' }}</small></td>
                                <td><small>{{ $sec->instructor->full_name ?? 'TBA' }}</small></td>
                                <td><small>{{ $sec->classroom->room_number ?? 'TBA' }}</small></td>
                                <td><small>{{ $sec->schedule_summary }}</small></td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border">{{ $sec->enrolled_count }} / {{ $sec->capacity }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No sections scheduled for this course.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
