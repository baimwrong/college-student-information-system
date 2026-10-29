@extends('layouts.app')

@section('title', 'My Class Schedule')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 no-print">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.registration') }}">Course Registration</a></li>
                <li class="breadcrumb-item active" aria-current="page">Class Schedule</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">My Semester Timetable & Schedule</h3>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('student.registration') }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil-square me-1"></i> Add / Drop Courses
        </a>
        <button type="button" class="btn btn-primary fw-semibold" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Print Schedule
        </button>
    </div>
</div>

<!-- Term Header Card -->
<div class="card shadow-sm border-0 mb-4 bg-white p-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                <i class="bi bi-calendar-week fs-3"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0">{{ $currentTerm->name ?? 'Active Session' }} ({{ $currentTerm->academic_year ?? '' }})</h4>
                <p class="text-muted small mb-0">Student: <strong>{{ $student->full_name }}</strong> (ID: {{ $student->student_id }}) | Program: {{ $student->program->name ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="text-end">
            <span class="badge bg-primary px-3 py-2 fs-6">
                {{ $enrolledSections->sum(fn($s) => $s->course->credits ?? 0) }} Total Enrolled Credits
            </span>
        </div>
    </div>
</div>

<!-- Schedule Table -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-primary"></i> Registered Class Timetable
        </h5>
    </div>
    <div class="card-body p-0">
        @if($enrolledSections->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-calendar-x display-5 text-muted mb-2 d-block"></i>
                <p class="text-muted mb-0">You have no scheduled classes for this academic term.</p>
                <a href="{{ route('student.registration') }}" class="btn btn-sm btn-primary mt-3">Register for Courses</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Course Code</th>
                            <th>Course Title</th>
                            <th class="text-center" style="width: 10%;">Section</th>
                            <th style="width: 20%;">Days</th>
                            <th style="width: 15%;">Time</th>
                            <th style="width: 15%;">Classroom / Lab</th>
                            <th style="width: 20%;">Faculty Instructor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrolledSections as $sec)
                            <tr>
                                <td class="fw-bold text-primary">{{ $sec->course->code }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $sec->course->title }}</div>
                                    <small class="text-muted">{{ $sec->course->credits }} Credit Units</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $sec->section_number }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">
                                        <i class="bi bi-calendar-day me-1 text-primary"></i> {{ $sec->day_of_week ?? 'TBA' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ $sec->start_time ?? 'TBA' }} - {{ $sec->end_time ?? 'TBA' }}
                                    </span>
                                </td>
                                <td>
                                    <div><i class="bi bi-geo-alt-fill text-danger me-1"></i> Room {{ $sec->classroom->room_number ?? 'TBA' }}</div>
                                    <small class="text-muted">{{ $sec->classroom->building ?? '' }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $sec->instructor->full_name ?? 'Assigned Faculty' }}</div>
                                    <small class="text-muted">{{ $sec->instructor->designation ?? '' }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
