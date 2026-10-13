@extends('layouts.app')

@section('title', 'Edit Course')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">Courses</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit {{ $course->code }}</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-1">Edit Course Syllabus</h2>
            </div>
            <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('courses.update', $course) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Course Code *</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $course->code) }}" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-secondary">Course Title *</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $course->title) }}" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Department *</label>
                            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $course->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }} ({{ $dept->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Credit Units *</label>
                            <input type="number" name="credits" class="form-control" value="{{ old('credits', $course->credits) }}" min="1" max="12" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Academic Level *</label>
                            <select name="level" class="form-select" required>
                                <option value="100" {{ old('level', $course->level) == 100 ? 'selected' : '' }}>100 (Freshman)</option>
                                <option value="200" {{ old('level', $course->level) == 200 ? 'selected' : '' }}>200 (Sophomore)</option>
                                <option value="300" {{ old('level', $course->level) == 300 ? 'selected' : '' }}>300 (Junior)</option>
                                <option value="400" {{ old('level', $course->level) == 400 ? 'selected' : '' }}>400 (Senior)</option>
                                <option value="500" {{ old('level', $course->level) == 500 ? 'selected' : '' }}>500 (Graduate)</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Lecture Hours</label>
                            <input type="number" name="lecture_hours" class="form-control" value="{{ old('lecture_hours', $course->lecture_hours) }}" min="0" max="20" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Lab Hours</label>
                            <input type="number" name="lab_hours" class="form-control" value="{{ old('lab_hours', $course->lab_hours) }}" min="0" max="20" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Prerequisites</label>
                            <input type="text" name="prerequisites" class="form-control" value="{{ old('prerequisites', $course->prerequisites) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', $course->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="archived" {{ old('status', $course->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-secondary">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $course->description) }}</textarea>
                        </div>
                    </div>

                    <div class="text-end pt-3 border-top">
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-check2 me-1"></i> Update Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
