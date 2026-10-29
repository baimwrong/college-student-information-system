@extends('layouts.app')

@section('title', 'Course Registration')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Self-Service Course Registration</h3>
        <p class="text-muted mb-0">Browse open course sections and register for classes in the active academic term.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('student.schedule') }}" class="btn btn-outline-primary fw-semibold">
            <i class="bi bi-calendar3 me-1"></i> View My Schedule
        </a>
    </div>
</div>

<!-- Term & Credit Status Bar -->
<div class="card shadow-sm border-0 mb-4 bg-primary text-white">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <small class="text-white-50 text-uppercase fw-semibold" style="letter-spacing: 1px;">Current Term</small>
                <h4 class="fw-bold mb-0 text-white">
                    <i class="bi bi-calendar-check me-2"></i>
                    {{ $currentTerm->name ?? 'No Active Term' }} ({{ $currentTerm->academic_year ?? 'N/A' }})
                </h4>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-white-50 text-uppercase fw-semibold">Credit Load: {{ $currentCredits }} / {{ $maxCredits }} Units</small>
                    <span class="badge {{ $currentCredits >= $maxCredits ? 'bg-warning text-dark' : 'bg-light text-primary' }} fw-bold">
                        {{ $maxCredits - $currentCredits }} Credits Remaining
                    </span>
                </div>
                <div class="progress" style="height: 10px; background-color: rgba(255,255,255,0.25);">
                    <div class="progress-bar {{ $currentCredits >= $maxCredits ? 'bg-warning' : 'bg-white' }}" role="progressbar" style="width: {{ min(100, ($currentCredits / $maxCredits) * 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Registered Courses Table -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-bookmark-check-fill text-success"></i> My Registered Courses ({{ $currentEnrollments->count() }})
        </h5>
        <span class="badge bg-success-subtle text-success border border-success-subtle">
            {{ $currentCredits }} Total Credits
        </span>
    </div>
    <div class="card-body p-0">
        @if($currentEnrollments->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-journal-plus display-5 text-muted mb-2 d-block"></i>
                <p class="text-muted mb-0">You have not registered for any courses in this term yet.</p>
                <small class="text-secondary">Select an available section below to register.</small>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Instructor</th>
                            <th>Schedule & Room</th>
                            <th class="text-center">Credits</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($currentEnrollments as $enr)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $enr->courseSection->course->code ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $enr->courseSection->course->title ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $enr->courseSection->section_number ?? 'SEC-A' }}</span>
                                </td>
                                <td>
                                    <small>{{ $enr->courseSection->instructor->full_name ?? 'Staff' }}</small>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-secondary">
                                        <i class="bi bi-clock me-1 text-primary"></i> {{ $enr->courseSection->day_of_week ?? 'TBA' }} ({{ $enr->courseSection->start_time ?? '' }} - {{ $enr->courseSection->end_time ?? '' }})
                                    </div>
                                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i> Room: {{ $enr->courseSection->classroom->room_number ?? 'TBA' }}</small>
                                </td>
                                <td class="text-center fw-bold">{{ $enr->courseSection->course->credits ?? 0 }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Enrolled</span>
                                </td>
                                <td class="text-end pe-3">
                                    @if($enr->grade_status !== 'published')
                                        <form method="POST" action="{{ route('student.registration.destroy', $enr) }}" onsubmit="return confirm('Are you sure you want to drop this course section?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash3 me-1"></i> Drop
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary" title="Grades published; cannot drop">Locked</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Available Course Sections Section -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-collection-play-fill text-primary"></i> Available Course Sections
        </h5>
        <form method="GET" action="{{ route('student.registration') }}" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search course code or title..." value="{{ request('search') }}" style="width: 250px;">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="bi bi-search"></i>
            </button>
            @if(request()->has('search'))
                <a href="{{ route('student.registration') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            @endif
        </form>
    </div>
    <div class="card-body p-0">
        @if($availableSections->isEmpty())
            <div class="text-center py-5">
                <p class="text-muted mb-0">No open course sections found matching your criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Instructor</th>
                            <th>Day & Time</th>
                            <th>Room</th>
                            <th class="text-center">Credits</th>
                            <th class="text-center">Seats</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($availableSections as $sec)
                            @php
                                $isAlreadyEnrolledThisSection = in_array($sec->id, $enrolledSectionIds);
                                $isAlreadyEnrolledSameCourse = in_array($sec->course_id, $enrolledCourseIds);
                                $isFull = $sec->enrolled_count >= $sec->capacity;
                                $canRegister = !$isAlreadyEnrolledSameCourse && !$isFull && (($currentCredits + ($sec->course->credits ?? 0)) <= $maxCredits);
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $sec->course->code }}</div>
                                    <small class="text-muted">{{ $sec->course->title }}</small>
                                    @if(!empty($sec->course->prerequisites))
                                        <div class="small text-danger"><i class="bi bi-exclamation-circle me-1"></i> Prereq: {{ $sec->course->prerequisites }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $sec->section_number }}</span>
                                </td>
                                <td>
                                    <small>{{ $sec->instructor->full_name ?? 'TBA' }}</small>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-secondary">
                                        <i class="bi bi-calendar3 me-1 text-primary"></i> {{ $sec->day_of_week ?? 'TBA' }}
                                    </div>
                                    <small class="text-muted">{{ $sec->start_time ?? '' }} - {{ $sec->end_time ?? '' }}</small>
                                </td>
                                <td>
                                    <small class="text-secondary">{{ $sec->classroom->room_number ?? 'TBA' }}</small>
                                </td>
                                <td class="text-center fw-bold">{{ $sec->course->credits }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $isFull ? 'bg-danger' : 'bg-success-subtle text-success border border-success-subtle' }}">
                                        {{ $sec->enrolled_count }} / {{ $sec->capacity }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    @if($isAlreadyEnrolledThisSection)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                            <i class="bi bi-check-lg me-1"></i> Enrolled
                                        </span>
                                    @elseif($isAlreadyEnrolledSameCourse)
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1 small">
                                            Other Sec Enrolled
                                        </span>
                                    @elseif($isFull)
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 small">
                                            Section Full
                                        </span>
                                    @elseif(($currentCredits + ($sec->course->credits ?? 0)) > $maxCredits)
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1 small" title="Would exceed maximum 18 semester credits">
                                            Exceeds Max Credits
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('student.registration.store') }}">
                                            @csrf
                                            <input type="hidden" name="course_section_id" value="{{ $sec->id }}">
                                            <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3">
                                                <i class="bi bi-plus-circle me-1"></i> Register
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                {{ $availableSections->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
