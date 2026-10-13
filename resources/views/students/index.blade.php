@extends('layouts.app')

@section('title', 'Students Registry')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Students Matriculation Registry</h2>
        <p class="text-muted mb-0">Manage student records, degree tracking, matriculation status, and academic standing.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.imports.index') }}" class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-cloud-arrow-up me-1"></i> Bulk Import
        </a>
        <a href="{{ route('students.create') }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-person-plus-fill me-1"></i> Admit New Student
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('students.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by Student ID, name, email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }} ({{ $dept->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="academic_status" class="form-select form-select-sm">
                    <option value="">All Academic Statuses</option>
                    <option value="active" {{ request('academic_status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="probation" {{ request('academic_status') == 'probation' ? 'selected' : '' }}>Academic Probation</option>
                    <option value="graduated" {{ request('academic_status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                    <option value="suspended" {{ request('academic_status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="withdrawn" {{ request('academic_status') == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">Filter</button>
                @if(request()->hasAny(['search', 'department_id', 'academic_status']))
                    <a href="{{ route('students.index') }}" class="btn btn-sm btn-light border" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Students List Table -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Program & Department</th>
                    <th>Semester</th>
                    <th>CGPA</th>
                    <th>Credits</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>
                            <a href="{{ route('students.show', $student) }}" class="fw-bold text-decoration-none">
                                {{ $student->student_id }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $student->full_name }}</div>
                            <small class="text-muted">{{ $student->email }}</small>
                        </td>
                        <td>
                            <div>{{ $student->program->name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $student->department->name ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">Term {{ $student->current_semester }}</span>
                        </td>
                        <td>
                            <span class="fw-bold {{ $student->cgpa >= 3.0 ? 'text-success' : ($student->cgpa >= 2.0 ? 'text-primary' : 'text-danger') }}">
                                {{ number_format($student->cgpa, 2) }}
                            </span>
                        </td>
                        <td>{{ $student->total_credits_earned }} cr</td>
                        <td>
                            @if($student->academic_status === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @elseif($student->academic_status === 'probation')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Probation</span>
                            @elseif($student->academic_status === 'graduated')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Graduated</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ ucfirst($student->academic_status) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Manage
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('students.show', $student) }}">
                                            <i class="bi bi-person me-2 text-primary"></i> View Record
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('students.transcript', $student) }}">
                                            <i class="bi bi-file-earmark-medical me-2 text-info"></i> Transcript
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('students.edit', $student) }}">
                                            <i class="bi bi-pencil me-2 text-warning"></i> Edit Details
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('students.destroy', $student) }}" onsubmit="return confirm('Are you sure you want to remove this student?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash me-2"></i> Delete Student
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-person-x fs-1 text-muted d-block mb-2"></i>
                            No student records found matching the query.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $students->links() }}
        </div>
    @endif
</div>
@endsection
