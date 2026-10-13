@extends('layouts.app')

@section('title', 'Schedule Class Section')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('sections.index') }}">Sections</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Schedule Section</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-1">Schedule Class Section</h2>
                <p class="text-muted mb-0">Assign course, academic term, professor, room, and weekly timeslot.</p>
            </div>
            <a href="{{ route('sections.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('sections.store') }}">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-secondary">Course *</label>
                            <select name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                <option value="">Select Course</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}" {{ old('course_id', request('course_id')) == $c->id ? 'selected' : '' }}>
                                        {{ $c->code }} - {{ $c->title }} ({{ $c->credits }} Credits)
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Section Code *</label>
                            <input type="text" name="section_number" class="form-control @error('section_number') is-invalid @enderror" value="{{ old('section_number', 'SEC-A') }}" required placeholder="e.g. SEC-A, SEC-B">
                            @error('section_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Academic Term *</label>
                            <select name="term_id" class="form-select @error('term_id') is-invalid @enderror" required>
                                <option value="">Select Term</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ (old('term_id') == $term->id || $term->is_current) ? 'selected' : '' }}>
                                        {{ $term->name }} ({{ $term->academic_year }}) {{ $term->is_current ? '• Active' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('term_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Faculty Instructor</label>
                            <select name="instructor_id" class="form-select @error('instructor_id') is-invalid @enderror">
                                <option value="">Assign Instructor Later</option>
                                @foreach($instructors as $inst)
                                    <option value="{{ $inst->id }}" {{ old('instructor_id') == $inst->id ? 'selected' : '' }}>
                                        {{ $inst->designation }} {{ $inst->full_name }} ({{ $inst->department->code ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('instructor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Classroom / Facility</label>
                            <select name="classroom_id" class="form-select @error('classroom_id') is-invalid @enderror">
                                <option value="">Assign Room Later</option>
                                @foreach($classrooms as $room)
                                    <option value="{{ $room->id }}" {{ old('classroom_id') == $room->id ? 'selected' : '' }}>
                                        {{ $room->room_number }} ({{ $room->building }}, Cap: {{ $room->capacity }})
                                    </option>
                                @endforeach
                            </select>
                            @error('classroom_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Max Student Capacity *</label>
                            <input type="number" name="capacity" class="form-control" value="{{ old('capacity', 40) }}" min="1" max="500" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="open">Open (Enrolling)</option>
                                <option value="closed">Closed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Weekly Meeting Days</label>
                            <input type="text" name="day_of_week" class="form-control" value="{{ old('day_of_week') }}" placeholder="e.g. Mon, Wed or Tue, Thu">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', '09:00') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="{{ old('end_time', '10:30') }}">
                        </div>
                    </div>

                    <div class="text-end pt-3 border-top">
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-calendar-check me-1"></i> Schedule Class Section
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
