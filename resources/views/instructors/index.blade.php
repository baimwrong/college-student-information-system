@extends('layouts.app')

@section('title', 'Faculty Instructors')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Faculty & Academic Instructors</h2>
        <p class="text-muted mb-0">Manage professors, lecturers, departmental assignments, and faculty access.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.imports.index') }}" class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-cloud-arrow-up me-1"></i> Bulk Import
        </a>
        <a href="{{ route('instructors.create') }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-person-plus-fill me-1"></i> Add Faculty Member
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('instructors.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by Employee ID, name, email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }} ({{ $dept->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">Filter</button>
                @if(request()->hasAny(['search', 'department_id']))
                    <a href="{{ route('instructors.index') }}" class="btn btn-sm btn-light border" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Specialization</th>
                    <th>Office</th>
                    <th>Sections</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($instructors as $inst)
                    <tr>
                        <td>
                            <a href="{{ route('instructors.show', $inst) }}" class="fw-bold text-decoration-none">
                                {{ $inst->employee_id }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $inst->full_name }}</div>
                            <small class="text-muted">{{ $inst->email }}</small>
                        </td>
                        <td>{{ $inst->department->name ?? 'N/A' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $inst->designation }}</span></td>
                        <td><small class="text-muted">{{ $inst->specialization ?? '-' }}</small></td>
                        <td><small>{{ $inst->office_room ?? '-' }}</small></td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                {{ $inst->course_sections_count }} Sections
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $inst->status === 'active' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} border">
                                {{ ucfirst(str_replace('_', ' ', $inst->status)) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('instructors.show', $inst) }}" class="btn btn-sm btn-outline-secondary me-1" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('instructors.edit', $inst) }}" class="btn btn-sm btn-outline-secondary me-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('instructors.destroy', $inst) }}" class="d-inline" onsubmit="return confirm('Delete this instructor and portal account?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-person-x fs-1 text-muted d-block mb-2"></i>
                            No faculty instructors found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($instructors->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $instructors->links() }}
        </div>
    @endif
</div>
@endsection
