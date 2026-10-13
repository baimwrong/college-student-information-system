@extends('layouts.app')

@section('title', 'Add Department')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-1">Create Academic Department</h2>
            </div>
            <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('departments.store') }}">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Department Code *</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required placeholder="e.g. CS, ENG, BIZ">
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-secondary">Department Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g. Computer Science & Software Engineering">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Faculty Division</label>
                            <input type="text" name="faculty_name" class="form-control" value="{{ old('faculty_name') }}" placeholder="e.g. Faculty of Science & Information Technology">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Head of Department (Chair)</label>
                            <input type="text" name="head_of_department" class="form-control" value="{{ old('head_of_department') }}" placeholder="e.g. Dr. Jane Smith">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Contact Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="cs-dept@college.edu">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1 555-0100">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-secondary">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Department overview, goals, or mission...">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="text-end pt-3 border-top">
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-check2 me-1"></i> Save Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
