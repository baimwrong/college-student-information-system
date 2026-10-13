@extends('layouts.app')

@section('title', 'Enroll Student')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('enrollments.index') }}">Enrollments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">New Registration</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-1">Course Registration</h2>
                <p class="text-muted mb-0">Enroll a student into a scheduled course section for the active academic session.</p>
            </div>
            <a href="{{ route('enrollments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('enrollments.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Student *</label>
                        <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">Select Student</option>
                            @foreach($students as $st)
                                <option value="{{ $st->id }}" {{ old('student_id') == $st->id ? 'selected' : '' }}>
                                    {{ $st->student_id }} - {{ $st->full_name }} ({{ $st->program->code ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-secondary">Available Course Section *</label>
                        <select name="course_section_id" class="form-select @error('course_section_id') is-invalid @enderror" required>
                            <option value="">Select Section</option>
                            @foreach($sections as $sec)
                                <option value="{{ $sec->id }}" {{ old('course_section_id') == $sec->id ? 'selected' : '' }}>
                                    {{ $sec->course->code }} - {{ $sec->course->title }} ({{ $sec->section_number }}) | {{ $sec->instructor->full_name ?? 'Instructor TBA' }} | Seats: {{ $sec->enrolled_count }}/{{ $sec->capacity }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="text-end pt-3 border-top">
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-check2-circle me-1"></i> Confirm Course Enrollment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
