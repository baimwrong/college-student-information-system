@extends('layouts.app')

@section('title', 'Faculty Profile - ' . $instructor->full_name)

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('instructors.index') }}">Faculty Instructors</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $instructor->employee_id }}</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">{{ $instructor->designation }} {{ $instructor->full_name }}</h2>
        <p class="text-muted mb-0">Employee ID: <strong>{{ $instructor->employee_id }}</strong> | Department of {{ $instructor->department->name ?? 'N/A' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('instructors.edit', $instructor) }}" class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-pencil me-1"></i> Edit Profile
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-person-lines-fill text-primary"></i> Faculty Details
                </h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <small class="text-muted d-block">Department</small>
                        <span class="fw-semibold">{{ $instructor->department->name ?? 'N/A' }}</span>
                    </li>
                    <li class="mb-3">
                        <small class="text-muted d-block">Email Address</small>
                        <span class="fw-semibold">{{ $instructor->email }}</span>
                    </li>
                    <li class="mb-3">
                        <small class="text-muted d-block">Office Room</small>
                        <span class="fw-semibold">{{ $instructor->office_room ?? 'Not specified' }}</span>
                    </li>
                    <li class="mb-3">
                        <small class="text-muted d-block">Specialization</small>
                        <span class="fw-semibold">{{ $instructor->specialization ?? 'General' }}</span>
                    </li>
                    <li class="mb-3">
                        <small class="text-muted d-block">Phone Number</small>
                        <span class="fw-semibold">{{ $instructor->phone ?? 'N/A' }}</span>
                    </li>
                    <li class="mb-0">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge {{ $instructor->status === 'active' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} border">
                            {{ ucfirst(str_replace('_', ' ', $instructor->status)) }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-easel text-success"></i> Assigned Course Sections
                </h6>
                <span class="badge bg-light text-dark border">{{ $instructor->courseSections->count() }} Sections</span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Term</th>
                            <th>Section</th>
                            <th>Classroom</th>
                            <th>Schedule</th>
                            <th class="text-end">Grading</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($instructor->courseSections as $sec)
                            <tr>
                                <td>
                                    <strong>{{ $sec->course->code ?? 'N/A' }}</strong>
                                    <small class="d-block text-muted">{{ $sec->course->title ?? '' }}</small>
                                </td>
                                <td><small class="badge bg-light text-dark border">{{ $sec->term->name ?? 'N/A' }}</small></td>
                                <td><span class="badge bg-primary-subtle text-primary">{{ $sec->section_number }}</span></td>
                                <td><small>{{ $sec->classroom->room_number ?? 'TBA' }}</small></td>
                                <td><small>{{ $sec->schedule_summary }}</small></td>
                                <td class="text-end">
                                    <a href="{{ route('sections.grades', $sec) }}" class="btn btn-sm btn-outline-primary">
                                        Grade Sheet
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No sections assigned to this instructor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
