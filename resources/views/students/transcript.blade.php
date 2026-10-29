@extends('layouts.app')

@section('title', 'Official Academic Transcript - ' . $student->student_id)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 no-print">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                @if(auth()->user()->isStudent())
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Official Transcript</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('students.show', $student) }}">{{ $student->student_id }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Official Transcript</li>
                @endif
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Official Student Academic Transcript</h3>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->isStudent())
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        @else
            <a href="{{ route('students.show', $student) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        @endif
        <a href="{{ route('students.transcript.pdf', $student) }}" class="btn btn-primary fw-semibold">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download Official PDF
        </a>
        <button type="button" class="btn btn-outline-secondary fw-semibold" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print
        </button>
    </div>
</div>

<!-- Transcript Document Card -->
<div class="card shadow-sm border-0 p-4 p-md-5 bg-white" id="transcript-doc">
    <!-- University Letterhead -->
    <div class="text-center pb-4 mb-4 border-bottom border-2 border-dark">
        <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
            <i class="bi bi-mortarboard-fill text-primary display-6"></i>
            <div>
                <h2 class="fw-bold mb-0 text-uppercase tracking-wide">University College Academic Registry</h2>
                <p class="text-muted small mb-0">Office of the Registrar & Records • Student Information System</p>
            </div>
        </div>
        <div class="badge bg-dark text-white text-uppercase px-3 py-2 mt-2" style="letter-spacing: 1.5px;">
            Official Academic Record & Transcript of Grades
        </div>
    </div>

    <!-- Student Bio Details Box -->
    <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
        <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Student Name</small>
            <span class="fw-bold text-dark fs-6">{{ $student->full_name }}</span>
        </div>
        <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Matriculation ID</small>
            <span class="fw-bold text-primary fs-6">{{ $student->student_id }}</span>
        </div>
        <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Degree / Major</small>
            <span class="fw-bold text-dark fs-6">{{ $student->program->name ?? 'N/A' }}</span>
        </div>
        <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Department</small>
            <span class="fw-bold text-dark fs-6">{{ $student->department->name ?? 'N/A' }}</span>
        </div>

        <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Date of Admission</small>
            <span class="fw-semibold text-secondary">{{ $student->admission_date ? $student->admission_date->format('F d, Y') : 'N/A' }}</span>
        </div>
        <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Academic Standing</small>
            <span class="badge bg-success-subtle text-success border border-success-subtle">{{ ucfirst($student->academic_status) }}</span>
        </div>
        <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Credits Required</small>
            <span class="fw-semibold text-secondary">{{ $student->program->total_credits_required ?? 120 }} Credits</span>
        </div>
        <div class="col-sm-6 col-md-3">
            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Issue Date</small>
            <span class="fw-semibold text-secondary">{{ date('F d, Y') }}</span>
        </div>
    </div>

    <!-- Course Grades Broken Down by Academic Term -->
    @if($termsData->isEmpty())
        <div class="text-center py-5">
            <p class="text-muted mb-0">No completed academic terms or graded courses are on record for this student.</p>
        </div>
    @else
        @foreach($termsData as $tData)
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between p-2 bg-secondary text-white rounded-top">
                    <span class="fw-bold ps-2">
                        <i class="bi bi-calendar3 me-1"></i> Academic Term: {{ $tData['term']->name ?? 'Session' }} ({{ $tData['term']->academic_year ?? '' }})
                    </span>
                    <span class="pe-2 small">
                        Term Credits: <strong>{{ $tData['credits'] }}</strong> | Term SGPA: <strong>{{ number_format($tData['sgpa'], 2) }}</strong>
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%;">Course Code</th>
                                <th>Course Title</th>
                                <th class="text-center" style="width: 10%;">Credits</th>
                                <th class="text-center" style="width: 10%;">Score</th>
                                <th class="text-center" style="width: 12%;">Grade</th>
                                <th class="text-center" style="width: 12%;">Grade Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tData['enrollments'] as $enr)
                                <tr>
                                    <td class="fw-bold">{{ $enr->courseSection->course->code ?? 'N/A' }}</td>
                                    <td>{{ $enr->courseSection->course->title ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $enr->courseSection->course->credits ?? 0 }}</td>
                                    <td class="text-center">
                                        @if($enr->grade_status === 'published' && $enr->total_score !== null)
                                            {{ number_format($enr->total_score, 1) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">
                                        @if($enr->grade_status === 'published' && $enr->letter_grade)
                                            <span class="badge {{ in_array($enr->letter_grade, ['A', 'A-', 'B+', 'B']) ? 'bg-success-subtle text-success border border-success-subtle' : (in_array($enr->letter_grade, ['C+', 'C', 'C-', 'D']) ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle') }}">
                                                {{ $enr->letter_grade }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">In Progress</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($enr->grade_status === 'published' && $enr->grade_point !== null)
                                            {{ number_format($enr->grade_point, 2) }}
                                        @else
                                            <span class="text-muted">-</span>
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

    <!-- Cumulative Summary Box -->
    <div class="row g-3 my-4 p-4 border border-2 border-primary rounded-3 bg-primary-subtle text-primary">
        <div class="col-md-4 text-center">
            <small class="text-uppercase fw-semibold d-block">Total Earned Credits</small>
            <h2 class="fw-bold mb-0 text-dark">{{ $student->total_credits_earned }}</h2>
        </div>
        <div class="col-md-4 text-center border-start border-end border-primary-subtle">
            <small class="text-uppercase fw-semibold d-block">Cumulative GPA (CGPA)</small>
            <h2 class="fw-bold mb-0 text-primary">{{ number_format($student->cgpa, 2) }} / 4.00</h2>
        </div>
        <div class="col-md-4 text-center">
            <small class="text-uppercase fw-semibold d-block">Graduation Standing</small>
            @if($student->academic_status === 'graduated' || ($student->total_credits_earned >= ($student->program->total_credits_required ?? 120) && !in_array($student->academic_status, ['probation', 'suspended'])))
                <h3 class="fw-bold mb-0 text-success">Graduated</h3>
            @elseif($student->academic_status === 'probation')
                <h3 class="fw-bold mb-0 text-warning">Academic Probation</h3>
            @elseif($student->academic_status === 'suspended')
                <h3 class="fw-bold mb-0 text-danger">Suspended</h3>
            @else
                <h3 class="fw-bold mb-0 text-primary">In Good Standing</h3>
            @endif
        </div>
    </div>

    <!-- Official Certification Signatures -->
    <div class="row mt-5 pt-4 text-center">
        <div class="col-4">
            <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 8px;"></div>
            <small class="fw-bold d-block">Dean of Academic Affairs</small>
        </div>
        <div class="col-4">
            <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 8px;"></div>
            <small class="fw-bold d-block">Registrar General</small>
        </div>
        <div class="col-4">
            <div class="p-2 border border-secondary border-dashed rounded d-inline-block">
                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.65rem;">Official Seal</small>
                <i class="bi bi-patch-check-fill text-primary fs-3"></i>
            </div>
        </div>
    </div>

    <!-- Tamper-Evident Digital Verification Banner -->
    <div class="mt-4 p-3 bg-light rounded-3 border border-secondary border-dashed d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-lock-fill text-primary fs-4"></i>
            <div>
                <strong class="d-block small text-dark">Tamper-Evident Document Verification</strong>
                <span class="small text-muted">Security Token: <code class="text-primary fw-bold">{{ $student->transcript_token }}</code></span>
            </div>
        </div>
        <div>
            <a href="{{ $verificationUrl }}" target="_blank" class="btn btn-sm btn-outline-primary fw-semibold">
                <i class="bi bi-box-arrow-up-right me-1"></i> Public Verification Portal
            </a>
        </div>
    </div>
</div>
@endsection
