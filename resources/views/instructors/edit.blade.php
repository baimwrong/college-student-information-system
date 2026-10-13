@extends('layouts.app')

@section('title', 'Edit Instructor')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('instructors.index') }}">Faculty Instructors</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit {{ $instructor->employee_id }}</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-1">Edit Faculty Instructor</h2>
                <p class="text-muted mb-0">Modify information for <strong>{{ $instructor->full_name }}</strong></p>
            </div>
            <a href="{{ route('instructors.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('instructors.update', $instructor) }}">
                    @csrf
                    @method('PUT')

                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-badge me-2"></i> Faculty Academic Credentials</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Employee / Faculty ID *</label>
                            <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" value="{{ old('employee_id', $instructor->employee_id) }}" required>
                            @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Department *</label>
                            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $instructor->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }} ({{ $dept->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Designation Rank *</label>
                            <select name="designation" class="form-select" required>
                                <option value="Professor" {{ old('designation', $instructor->designation) === 'Professor' ? 'selected' : '' }}>Full Professor</option>
                                <option value="Associate Professor" {{ old('designation', $instructor->designation) === 'Associate Professor' ? 'selected' : '' }}>Associate Professor</option>
                                <option value="Assistant Professor" {{ old('designation', $instructor->designation) === 'Assistant Professor' ? 'selected' : '' }}>Assistant Professor</option>
                                <option value="Senior Lecturer" {{ old('designation', $instructor->designation) === 'Senior Lecturer' ? 'selected' : '' }}>Senior Lecturer</option>
                                <option value="Lecturer" {{ old('designation', $instructor->designation) === 'Lecturer' ? 'selected' : '' }}>Lecturer</option>
                                <option value="Adjunct Professor" {{ old('designation', $instructor->designation) === 'Adjunct Professor' ? 'selected' : '' }}>Adjunct Professor</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Research Specialization</label>
                            <input type="text" name="specialization" class="form-control" value="{{ old('specialization', $instructor->specialization) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Office Room</label>
                            <input type="text" name="office_room" class="form-control" value="{{ old('office_room', $instructor->office_room) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Hire Date</label>
                            <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date', $instructor->hire_date ? $instructor->hire_date->format('Y-m-d') : '') }}">
                        </div>
                    </div>

                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-lines-fill me-2"></i> Personal Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">First Name *</label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $instructor->first_name) }}" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Last Name *</label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $instructor->last_name) }}" required>
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Email Address *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $instructor->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $instructor->phone) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', $instructor->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="on_leave" {{ old('status', $instructor->status) === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                <option value="retired" {{ old('status', $instructor->status) === 'retired' ? 'selected' : '' }}>Retired</option>
                                <option value="terminated" {{ old('status', $instructor->status) === 'terminated' ? 'selected' : '' }}>Terminated</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-end pt-3 border-top">
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-check2 me-1"></i> Update Faculty Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
