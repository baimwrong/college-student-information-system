@extends('layouts.app')

@section('title', 'Student Profile - ' . $student->student_id)

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $student->student_id }}</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">{{ $student->full_name }}</h2>
        <p class="text-muted mb-0">
            Matriculation ID: <strong>{{ $student->student_id }}</strong> | 
            Program: <strong>{{ $student->program->name ?? 'N/A' }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('students.transcript', $student) }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-file-earmark-medical me-1"></i> Official Academic Transcript
        </a>
        <a href="{{ route('students.edit', $student) }}" class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-pencil me-1"></i> Edit Profile
        </a>
    </div>
</div>

<!-- Academic Standing Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <span class="text-muted small fw-semibold text-uppercase">Cumulative GPA (CGPA)</span>
            <h3 class="fw-bold my-1 {{ $student->cgpa >= 3.0 ? 'text-success' : ($student->cgpa >= 2.0 ? 'text-primary' : 'text-danger') }}">
                {{ number_format($student->cgpa, 2) }}
            </h3>
            <small class="text-muted">Scale 4.00</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <span class="text-muted small fw-semibold text-uppercase">Earned Credits</span>
            <h3 class="fw-bold my-1 text-primary">{{ $student->total_credits_earned }}</h3>
            <small class="text-muted">Of {{ $student->program->total_credits_required ?? 120 }} required</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <span class="text-muted small fw-semibold text-uppercase">Current Semester</span>
            <h3 class="fw-bold my-1 text-dark">Term {{ $student->current_semester }}</h3>
            <small class="text-muted">{{ $student->program->degree_level ?? 'Undergraduate' }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <span class="text-muted small fw-semibold text-uppercase">Academic Standing</span>
            <h4 class="fw-bold my-1 text-success">{{ ucfirst($student->academic_status) }}</h4>
            <small class="text-muted">Admitted: {{ $student->admission_date ? $student->admission_date->format('M Y') : 'N/A' }}</small>
        </div>
    </div>
</div>

<!-- Personal & Academic Details Section -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-person-vcard text-primary"></i> Demographic Details
                </h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <small class="text-muted d-block">Email Address</small>
                        <span class="fw-semibold">{{ $student->email }}</span>
                    </li>
                    <li class="mb-3">
                        <small class="text-muted d-block">Phone Number</small>
                        <span class="fw-semibold">{{ $student->phone ?? 'Not provided' }}</span>
                    </li>
                    <li class="mb-3">
                        <small class="text-muted d-block">Gender</small>
                        <span class="fw-semibold text-capitalize">{{ $student->gender }}</span>
                    </li>
                    <li class="mb-3">
                        <small class="text-muted d-block">Date of Birth</small>
                        <span class="fw-semibold">{{ $student->date_of_birth ? $student->date_of_birth->format('F d, Y') : 'Not provided' }}</span>
                    </li>
                    <li class="mb-3">
                        <small class="text-muted d-block">Residential Address</small>
                        <span class="fw-semibold">{{ $student->address ?? 'Not provided' }}</span>
                    </li>
                    <li class="mb-0">
                        <small class="text-muted d-block">Emergency Contact</small>
                        <span class="fw-semibold">{{ $student->emergency_contact_name ?? 'None' }} ({{ $student->emergency_contact_phone ?? 'N/A' }})</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-success"></i> Academic Course History
                </h6>
            </div>
            <div class="card-body p-4">
                @if($enrollmentsByTerm->isEmpty())
                    <p class="text-muted text-center py-4">No course enrollments recorded for this student.</p>
                @else
                    @foreach($enrollmentsByTerm as $termName => $enrollments)
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold text-primary mb-0"><i class="bi bi-calendar3 me-1"></i> {{ $termName }}</h6>
                                <span class="badge bg-light text-dark border">{{ $enrollments->count() }} Courses</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Title</th>
                                            <th>Credits</th>
                                            <th>Instructor</th>
                                            <th>Score</th>
                                            <th>Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($enrollments as $enr)
                                            <tr>
                                                <td class="fw-bold">{{ $enr->courseSection->course->code ?? 'N/A' }}</td>
                                                <td>{{ $enr->courseSection->course->title ?? 'N/A' }}</td>
                                                <td>{{ $enr->courseSection->course->credits ?? 0 }}</td>
                                                <td><small>{{ $enr->courseSection->instructor->full_name ?? 'TBA' }}</small></td>
                                                <td>
                                                    {{ $enr->total_score !== null ? number_format($enr->total_score, 1) . '%' : '-' }}
                                                </td>
                                                <td>
                                                    @if($enr->letter_grade)
                                                        <span class="badge {{ in_array($enr->letter_grade, ['A', 'A-']) ? 'bg-success' : 'bg-primary' }}">
                                                            {{ $enr->letter_grade }} ({{ number_format($enr->grade_point, 2) }})
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary">In Progress</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
