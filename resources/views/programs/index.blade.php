@extends('layouts.app')

@section('title', 'Academic Programs')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Academic Programs & Majors</h2>
        <p class="text-muted mb-0">Degree offerings, credit curriculum requirements, and student matriculation tracking.</p>
    </div>
    <div>
        <a href="{{ route('programs.create') }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-plus-circle-fill me-1"></i> Add Academic Program
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Program Title</th>
                    <th>Department</th>
                    <th>Level</th>
                    <th>Duration</th>
                    <th>Credits Required</th>
                    <th>Enrolled Students</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $prog)
                    <tr>
                        <td>
                            <span class="badge bg-primary px-2 py-1 fw-bold">{{ $prog->code }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $prog->name }}</div>
                        </td>
                        <td>{{ $prog->department->name ?? 'N/A' }}</td>
                        <td><span class="badge bg-light text-dark border text-capitalize">{{ $prog->degree_level }}</span></td>
                        <td>{{ $prog->duration_years }} Years</td>
                        <td><strong>{{ $prog->total_credits_required }}</strong> Credits</td>
                        <td>
                            <span class="badge bg-info-subtle text-info border">{{ $prog->students_count }} Students</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('programs.edit', $prog) }}" class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('programs.destroy', $prog) }}" class="d-inline" onsubmit="return confirm('Delete this program?');">
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
                        <td colspan="8" class="text-center text-muted py-4">No academic programs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($programs->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $programs->links() }}
        </div>
    @endif
</div>
@endsection
