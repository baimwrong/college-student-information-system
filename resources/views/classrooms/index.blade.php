@extends('layouts.app')

@section('title', 'Classrooms & Facilities')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Classrooms & Campus Facilities</h2>
        <p class="text-muted mb-0">Lecture halls, computer laboratories, and facility capacity management.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#createClassroomModal">
            <i class="bi bi-building-add me-1"></i> Register Classroom
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Building / Hall</th>
                    <th>Type</th>
                    <th>Seating Capacity</th>
                    <th>Status</th>
                    <th>Scheduled Sections</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classrooms as $room)
                    <tr>
                        <td>
                            <strong class="text-primary"><i class="bi bi-door-closed me-1"></i> {{ $room->room_number }}</strong>
                        </td>
                        <td>{{ $room->building }}</td>
                        <td>
                            <span class="badge bg-light text-dark border text-capitalize">
                                {{ str_replace('_', ' ', $room->type) }}
                            </span>
                        </td>
                        <td><strong>{{ $room->capacity }}</strong> Seats</td>
                        <td>
                            @if($room->status === 'available')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Available</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Maintenance</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $room->course_sections_count }} Classes</span>
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('classrooms.destroy', $room) }}" class="d-inline" onsubmit="return confirm('Delete this classroom?');">
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
                        <td colspan="7" class="text-center text-muted py-4">No classrooms registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Register Classroom -->
<div class="modal fade" id="createClassroomModal" tabindex="-1" aria-labelledby="createClassroomModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('classrooms.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createClassroomModalLabel">Register Classroom / Lab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Room Number *</label>
                        <input type="text" name="room_number" class="form-control" required placeholder="e.g. LH-101, LAB-302">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Building Name *</label>
                        <input type="text" name="building" class="form-control" required placeholder="e.g. Science Complex, Main Academic Building">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Seating Capacity *</label>
                            <input type="number" name="capacity" class="form-control" value="40" min="1" max="500" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Room Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="lecture_hall">Lecture Hall</option>
                                <option value="lab">Computer / Science Lab</option>
                                <option value="seminar">Seminar Room</option>
                                <option value="auditorium">Auditorium</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="maintenance">Under Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Save Classroom</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
