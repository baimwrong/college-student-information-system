@extends('layouts.auth')

@section('title', 'Official Transcript Verification')

@section('content')
<div class="auth-header">
    <div class="auth-logo bg-primary text-white">
        <i class="bi bi-patch-check-fill"></i>
    </div>
    <h4 class="fw-bold mb-1 text-slate-900">Academic Registry Verification</h4>
    <p class="text-muted small mb-0">Official University Academic Transcript Verification Portal</p>
</div>

<div class="auth-body">
    @if($isValid && $student)
        <!-- Valid Verified Record -->
        <div class="alert alert-success d-flex align-items-center gap-3 p-3 mb-4 rounded-3 border-success shadow-sm">
            <i class="bi bi-shield-fill-check fs-1 text-success"></i>
            <div>
                <h6 class="fw-bold mb-1 text-success">Authentic Official Transcript Verified</h6>
                <p class="small mb-0 text-secondary">
                    This document is certified genuine and matches official registry records maintained by the Office of the University Registrar.
                </p>
            </div>
        </div>

        <div class="card border mb-4 bg-light">
            <div class="card-body p-3">
                <div class="row g-2 small">
                    <div class="col-6 text-muted">Verification Code:</div>
                    <div class="col-6 text-end fw-bold font-monospace text-primary">{{ $token }}</div>

                    <div class="col-6 text-muted">Student Name:</div>
                    <div class="col-6 text-end fw-bold text-dark">{{ $student->full_name }}</div>

                    <div class="col-6 text-muted">Matriculation ID:</div>
                    <div class="col-6 text-end fw-bold">{{ $student->student_id }}</div>

                    <div class="col-6 text-muted">Academic Program:</div>
                    <div class="col-6 text-end fw-semibold">{{ $student->program->name ?? 'N/A' }}</div>

                    <div class="col-6 text-muted">Department:</div>
                    <div class="col-6 text-end">{{ $student->department->name ?? 'N/A' }}</div>

                    <div class="col-6 text-muted">Credits Earned:</div>
                    <div class="col-6 text-end fw-bold text-dark">{{ $student->total_credits_earned }} Credits</div>

                    <div class="col-6 text-muted">Cumulative GPA:</div>
                    <div class="col-6 text-end fw-bold text-primary">{{ number_format($student->cgpa, 2) }} / 4.00</div>

                    <div class="col-6 text-muted">Academic Status:</div>
                    <div class="col-6 text-end">
                        <span class="badge {{ $student->academic_status === 'active' ? 'bg-success' : ($student->academic_status === 'probation' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                            {{ ucfirst($student->academic_status) }}
                        </span>
                    </div>

                    <div class="col-6 text-muted">Verification Timestamp:</div>
                    <div class="col-6 text-end text-muted">{{ now()->format('Y-m-d H:i:s T') }}</div>
                </div>
            </div>
        </div>

        <div class="text-center text-muted small mb-3">
            <i class="bi bi-lock-fill me-1"></i> Digitally validated by Registrar Security Engine
        </div>
    @else
        <!-- Invalid / Forged Record -->
        <div class="alert alert-danger d-flex align-items-center gap-3 p-3 mb-4 rounded-3 border-danger shadow-sm">
            <i class="bi bi-shield-fill-x fs-1 text-danger"></i>
            <div>
                <h6 class="fw-bold mb-1 text-danger">Verification Failed: Record Not Found</h6>
                <p class="small mb-0 text-secondary">
                    The verification code <code class="text-danger fw-bold">{{ $token }}</code> does not match any official academic record issued by this institution. The document may be forged, superseded, or invalid.
                </p>
            </div>
        </div>

        <div class="p-3 border rounded bg-white text-center mb-3">
            <p class="text-muted small mb-0">
                Please verify that the code was transcribed accurately from the official academic transcript document or contact the University Registrar at <strong class="text-dark">registrar@college.edu</strong>.
            </p>
        </div>
    @endif

    <div class="text-center pt-2">
        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Return to Campus Portal
        </a>
    </div>
</div>
@endsection
