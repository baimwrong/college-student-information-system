@extends('layouts.app')

@section('title', 'Academic Departments')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Academic Departments</h2>
        <p class="text-muted mb-0">Manage college faculties, academic divisions, leadership, and contact records.</p>
    </div>
    <div>
        <a href="{{ route('departments.create') }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-plus-circle-fill me-1"></i> Add Department
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Department Name</th>
                    <th>Faculty Division</th>
                    <th>Head of Dept.</th>
                    <th>Faculty Count</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                    <tr>
                        <td>
                            <span class="badge bg-primary px-2 py-1 fw-bold">{{ $dept->code }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $dept->name }}</div>
                            <small class="text-muted">{{ $dept->email ?? '' }}</small>
                        </td>
                        <td>{{ $dept->faculty_name ?? 'General Faculty' }}</td>
                        <td>{{ $dept->head_of_department ?? 'Unassigned' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $dept->instructors_count }} Faculty</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $dept->students_count }} Students</span>
                        </td>
                        <td>
                            @if($dept->status === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('departments.edit', $dept) }}" class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('departments.destroy', $dept) }}" class="d-inline" onsubmit="return confirm('Delete this department?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No departments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($departments->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $departments->links() }}
        </div>
    @endif
</div>
@endsection
