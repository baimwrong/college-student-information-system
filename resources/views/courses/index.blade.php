@extends('layouts.app')

@section('title', 'Curriculum Courses')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Curriculum Courses Catalog</h2>
        <p class="text-muted mb-0">Browse university course offerings, credit units, prerequisites, and syllabi.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.imports.index') }}" class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-cloud-arrow-up me-1"></i> Bulk Import
        </a>
        <a href="{{ route('courses.create') }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-journal-plus me-1"></i> Add Course
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('courses.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by course code, title..." value="{{ request('search') }}">
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
                    <a href="{{ route('courses.index') }}" class="btn btn-sm btn-light border" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
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
                    <th>Code</th>
                    <th>Course Title</th>
                    <th>Department</th>
                    <th>Credits</th>
                    <th>Level</th>
                    <th>Prerequisites</th>
                    <th>Active Sections</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                    <tr>
                        <td>
                            <a href="{{ route('courses.show', $course) }}" class="fw-bold text-decoration-none">
                                {{ $course->code }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $course->title }}</div>
                        </td>
                        <td>{{ $course->department->code ?? 'N/A' }}</td>
                        <td><strong>{{ $course->credits }}</strong> Credits</td>
                        <td><span class="badge bg-light text-dark border">{{ $course->level }} Level</span></td>
                        <td>
                            <small class="text-muted">{{ $course->prerequisites ?? 'None' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                {{ $course->sections_count }} Sections
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('courses.destroy', $course) }}" class="d-inline" onsubmit="return confirm('Delete this course?');">
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
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-journal-x fs-1 text-muted d-block mb-2"></i>
                            No courses found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($courses->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $courses->links() }}
        </div>
    @endif
</div>
@endsection
