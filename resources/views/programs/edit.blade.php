@extends('layouts.app')

@section('title', 'Edit Academic Program')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('programs.index') }}">Programs</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit {{ $program->code }}</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-1">Edit Academic Program</h2>
            </div>
            <a href="{{ route('programs.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('programs.update', $program) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Program Code *</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $program->code) }}" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-secondary">Program Title *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $program->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Department *</label>
                            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $program->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }} ({{ $dept->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Degree Level *</label>
                            <select name="degree_level" class="form-select" required>
                                <option value="undergraduate" {{ old('degree_level', $program->degree_level) === 'undergraduate' ? 'selected' : '' }}>Undergraduate (Bachelor)</option>
                                <option value="postgraduate" {{ old('degree_level', $program->degree_level) === 'postgraduate' ? 'selected' : '' }}>Postgraduate (Master)</option>
                                <option value="doctorate" {{ old('degree_level', $program->degree_level) === 'doctorate' ? 'selected' : '' }}>Doctorate (Ph.D.)</option>
                                <option value="diploma" {{ old('degree_level', $program->degree_level) === 'diploma' ? 'selected' : '' }}>Diploma / Certificate</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Duration (Years) *</label>
                            <input type="number" name="duration_years" class="form-control" value="{{ old('duration_years', $program->duration_years) }}" min="1" max="10" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Total Credits Required *</label>
                            <input type="number" name="total_credits_required" class="form-control" value="{{ old('total_credits_required', $program->total_credits_required) }}" min="30" max="300" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', $program->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $program->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-end pt-3 border-top">
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-check2 me-1"></i> Update Program
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
