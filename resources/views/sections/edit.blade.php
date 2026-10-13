@extends('layouts.app')

@section('title', 'Edit Section')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('sections.index') }}">Sections</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit {{ $section->section_number }}</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-1">Edit Class Section</h2>
                <p class="text-muted mb-0">Course: <strong>{{ $section->course->code ?? 'N/A' }}</strong> ({{ $section->section_number }})</p>
            </div>
            <a href="{{ route('sections.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('sections.update', $section) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-secondary">Course *</label>
                            <select name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}" {{ old('course_id', $section->course_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->code }} - {{ $c->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Section Code *</label>
                            <input type="text" name="section_number" class="form-control @error('section_number') is-invalid @enderror" value="{{ old('section_number', $section->section_number) }}" required>
                            @error('section_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Academic Term *</label>
                            <select name="term_id" class="form-select @error('term_id') is-invalid @enderror" required>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ old('term_id', $section->term_id) == $term->id ? 'selected' : '' }}>
                                        {{ $term->name }} ({{ $term->academic_year }})
                                    </option>
                                @endforeach
                            </select>
                            @error('term_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Faculty Instructor</label>
                            <select name="instructor_id" class="form-select @error('instructor_id') is-invalid @enderror">
                                <option value="">No Instructor Assigned</option>
                                @foreach($instructors as $inst)
                                    <option value="{{ $inst->id }}" {{ old('instructor_id', $section->instructor_id) == $inst->id ? 'selected' : '' }}>
                                        {{ $inst->designation }} {{ $inst->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('instructor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Classroom / Facility</label>
                            <select name="classroom_id" class="form-select @error('classroom_id') is-invalid @enderror">
                                <option value="">No Classroom Assigned</option>
                                @foreach($classrooms as $room)
                                    <option value="{{ $room->id }}" {{ old('classroom_id', $section->classroom_id) == $room->id ? 'selected' : '' }}>
                                        {{ $room->room_number }} ({{ $room->building }})
                                    </option>
                                @endforeach
                            </select>
                            @error('classroom_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Max Student Capacity *</label>
                            <input type="number" name="capacity" class="form-control" value="{{ old('capacity', $section->capacity) }}" min="1" max="500" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="open" {{ old('status', $section->status) === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="closed" {{ old('status', $section->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                                <option value="cancelled" {{ old('status', $section->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Weekly Meeting Days</label>
                            <input type="text" name="day_of_week" class="form-control" value="{{ old('day_of_week', $section->day_of_week) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $section->start_time) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $section->end_time) }}">
                        </div>
                    </div>

                    <div class="text-end pt-3 border-top">
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-check2 me-1"></i> Update Section Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
