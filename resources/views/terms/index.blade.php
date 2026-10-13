@extends('layouts.app')

@section('title', 'Academic Terms')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Academic Terms & Semesters</h2>
        <p class="text-muted mb-0">Manage academic sessions, configure active terms, and regulate registration windows.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#createTermModal">
            <i class="bi bi-calendar-plus-fill me-1"></i> New Academic Term
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Term Code</th>
                    <th>Term Name</th>
                    <th>Academic Year</th>
                    <th>Date Window</th>
                    <th>Class Sections</th>
                    <th>Current Active</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($terms as $term)
                    <tr class="{{ $term->is_current ? 'table-primary-subtle' : '' }}">
                        <td><span class="badge bg-dark px-2 py-1">{{ $term->code }}</span></td>
                        <td>
                            <strong class="text-dark">{{ $term->name }}</strong>
                        </td>
                        <td>{{ $term->academic_year }}</td>
                        <td>
                            <small class="text-muted">
                                {{ $term->start_date->format('M d, Y') }} — {{ $term->end_date->format('M d, Y') }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $term->course_sections_count }} Sections</span>
                        </td>
                        <td>
                            @if($term->is_current)
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> CURRENT</span>
                            @else
                                <form method="POST" action="{{ route('terms.setCurrent', $term) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                        Set Current
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $term->status === 'active' ? 'bg-success-subtle text-success' : ($term->status === 'upcoming' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary') }} border">
                                {{ ucfirst($term->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('terms.destroy', $term) }}" class="d-inline" onsubmit="return confirm('Delete this academic term?');">
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
                        <td colspan="8" class="text-center text-muted py-4">No academic terms found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Create Term -->
<div class="modal fade" id="createTermModal" tabindex="-1" aria-labelledby="createTermModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('terms.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createTermModalLabel">Create Academic Term</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Term Code *</label>
                        <input type="text" name="code" class="form-control" required placeholder="e.g. 2026-FALL">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Term Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Fall 2026">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Academic Year *</label>
                        <input type="text" name="academic_year" class="form-control" required placeholder="e.g. 2026-2027">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">End Date *</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="upcoming">Upcoming</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_current" value="1" id="is_current">
                        <label class="form-check-label small" for="is_current">
                            Set as current active academic session immediately
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Save Academic Term</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
