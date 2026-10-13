@extends('layouts.app')

@section('title', 'Faculty Portal')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Faculty Academic Portal</h2>
        <p class="text-muted mb-0">
            Welcome back, <strong>{{ $instructor->designation ?? 'Instructor' }} {{ $instructor->full_name ?? auth()->user()->name }}</strong> 
            ({{ $instructor->department->name ?? 'Department' }})
        </p>
    </div>
    <div>
        <span class="badge bg-primary px-3 py-2 fw-semibold">
            <i class="bi bi-calendar-check me-1"></i> Term: {{ $currentTerm->name ?? 'Active Session' }}
        </span>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Assigned Sections</span>
                    <h3 class="fw-bold my-1 text-slate-800">{{ $sections->count() }}</h3>
                    <small class="text-primary fw-semibold"><i class="bi bi-book"></i> Active Courses</small>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-collection-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Total Students Taught</span>
                    <h3 class="fw-bold my-1 text-slate-800">{{ $totalTaughtStudents }}</h3>
                    <small class="text-success fw-semibold"><i class="bi bi-people"></i> Enrolled Roster</small>
                </div>
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Pending Grade Sheets</span>
                    <h3 class="fw-bold my-1 text-slate-800">{{ $pendingGradesCount }}</h3>
                    <small class="text-warning fw-semibold"><i class="bi bi-pencil-square"></i> Draft Records</small>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-clipboard-check-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Assigned Course Sections Table -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-easel-fill text-primary"></i> My Course Sections & Schedules
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Course Code & Title</th>
                    <th>Section</th>
                    <th>Schedule</th>
                    <th>Classroom</th>
                    <th>Enrollment</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sections as $sec)
                    <tr>
                        <td>
                            <div class="fw-bold text-primary">{{ $sec->course->code ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $sec->course->title ?? 'N/A' }} ({{ $sec->course->credits ?? 0 }} Credits)</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $sec->section_number }}</span>
                        </td>
                        <td>
                            <small><i class="bi bi-clock me-1 text-muted"></i> {{ $sec->schedule_summary }}</small>
                        </td>
                        <td>
                            <small><i class="bi bi-geo-alt me-1 text-muted"></i> {{ $sec->classroom->room_number ?? 'TBA' }} ({{ $sec->classroom->building ?? '' }})</small>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info border">
                                {{ $sec->enrolled_count }} / {{ $sec->capacity }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('sections.grades', $sec) }}" class="btn btn-sm btn-primary fw-semibold">
                                <i class="bi bi-pencil-square me-1"></i> Grade Sheet
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            You do not have any teaching sections assigned for the current academic session.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
