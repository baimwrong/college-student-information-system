@extends('layouts.app')

@section('title', 'Batch Grade Sheet - ' . $section->course->code)

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('sections.index') }}">Sections</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sections.show', $section) }}">{{ $section->course->code }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Grade Sheet</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Batch Course Grade Sheet</h2>
        <p class="text-muted mb-0">
            <strong>{{ $section->course->code }}: {{ $section->course->title }}</strong> ({{ $section->section_number }}) | 
            Term: <strong>{{ $section->term->name ?? 'N/A' }}</strong> | 
            Instructor: <strong>{{ $section->instructor->full_name ?? 'Instructor' }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sections.show', $section) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Section
        </a>
    </div>
</div>

<!-- Grading Policy Card -->
<div class="card shadow-sm mb-4 border-start border-4 border-primary">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-calculator-fill text-primary"></i>
            <span class="fw-bold small">Automated Grading Calculation Formula:</span>
        </div>
        <p class="small text-muted mb-0">
            Composite Score = (Assignments &times; 20%) + (Midterm Exam &times; 35%) + (Final Exam &times; 45%). Setting status to <strong>Published</strong> automatically computes final letter grades (A through F), 4.0 grade points, and updates each student's Cumulative GPA (CGPA) and earned credits in real-time.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('sections.grades.save', $section) }}">
    @csrf

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
            <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square text-primary"></i> Student Grade Ledger ({{ $section->enrollments->count() }} Students)
            </h5>
            <button type="submit" class="btn btn-primary fw-semibold px-4 shadow-sm">
                <i class="bi bi-save2 me-1"></i> Save Grade Sheet & Recalculate CGPA
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%;">Student ID</th>
                        <th style="width: 20%;">Student Name</th>
                        <th style="width: 12%;">Assignments (20%)</th>
                        <th style="width: 12%;">Midterm (35%)</th>
                        <th style="width: 12%;">Final Exam (45%)</th>
                        <th style="width: 12%;">Grade Status</th>
                        <th style="width: 17%;">Remarks / Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($section->enrollments as $index => $enr)
                        <tr>
                            <td>
                                <input type="hidden" name="grades[{{ $index }}][enrollment_id]" value="{{ $enr->id }}">
                                <span class="fw-bold text-dark">{{ $enr->student->student_id ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $enr->student->full_name ?? 'N/A' }}</div>
                                @if($enr->letter_grade)
                                    <small class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        Current: {{ $enr->letter_grade }} ({{ number_format($enr->grade_point, 2) }})
                                    </small>
                                @endif
                            </td>
                            <td>
                                <input type="number" step="0.01" name="grades[{{ $index }}][assignments_score]" class="form-control form-control-sm text-center" value="{{ old("grades.$index.assignments_score", $enr->assignments_score) }}" placeholder="0 - 100" min="0" max="100">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="grades[{{ $index }}][midterm_score]" class="form-control form-control-sm text-center" value="{{ old("grades.$index.midterm_score", $enr->midterm_score) }}" placeholder="0 - 100" min="0" max="100">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="grades[{{ $index }}][final_score]" class="form-control form-control-sm text-center" value="{{ old("grades.$index.final_score", $enr->final_score) }}" placeholder="0 - 100" min="0" max="100">
                            </td>
                            <td>
                                <select name="grades[{{ $index }}][grade_status]" class="form-select form-select-sm">
                                    <option value="draft" {{ old("grades.$index.grade_status", $enr->grade_status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="submitted" {{ old("grades.$index.grade_status", $enr->grade_status) === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="published" {{ old("grades.$index.grade_status", $enr->grade_status) === 'published' ? 'selected' : '' }}>Published (Final)</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="grades[{{ $index }}][remarks]" class="form-control form-control-sm" value="{{ old("grades.$index.remarks", $enr->remarks) }}" placeholder="Optional remarks">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-people fs-1 text-muted d-block mb-2"></i>
                                No enrolled students in this section to grade.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($section->enrollments->isNotEmpty())
            <div class="card-footer bg-white py-3 text-end">
                <button type="submit" class="btn btn-primary fw-semibold px-4 shadow-sm">
                    <i class="bi bi-save2 me-1"></i> Save Grade Sheet & Recalculate CGPA
                </button>
            </div>
        @endif
    </div>
</form>
@endsection
