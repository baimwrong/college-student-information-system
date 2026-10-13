@extends('layouts.app')

@section('title', 'Bulk Data Import Hub')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Bulk Data Ingestion & Import Hub</h2>
        <p class="text-muted mb-0">High-throughput CSV batch import engine with integrity verification, duplicate strategies, and automated user generation.</p>
    </div>
    <div class="dropdown">
        <button class="btn btn-outline-success dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-download me-1"></i> Export Database to CSV
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
            <li><a class="dropdown-item" href="{{ route('admin.export', 'students') }}"><i class="bi bi-people me-2"></i> Export Students</a></li>
            <li><a class="dropdown-item" href="{{ route('admin.export', 'instructors') }}"><i class="bi bi-person-badge me-2"></i> Export Instructors</a></li>
            <li><a class="dropdown-item" href="{{ route('admin.export', 'courses') }}"><i class="bi bi-journal-bookmark me-2"></i> Export Courses</a></li>
        </ul>
    </div>
</div>

<!-- Summary metrics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Total Import Jobs</span>
                    <h3 class="fw-bold my-1 text-slate-800">{{ $totalImports }}</h3>
                    <small class="text-muted">Historical executions</small>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-cloud-arrow-up-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Rows Created</span>
                    <h3 class="fw-bold my-1 text-success">{{ number_format($totalImportedRows) }}</h3>
                    <small class="text-success"><i class="bi bi-check-lg"></i> Ingested clean</small>
                </div>
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Rows Updated</span>
                    <h3 class="fw-bold my-1 text-info">{{ number_format($totalUpdatedRows) }}</h3>
                    <small class="text-info"><i class="bi bi-arrow-repeat"></i> Synchronized</small>
                </div>
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-arrow-clockwise"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Rows Rejected</span>
                    <h3 class="fw-bold my-1 text-danger">{{ number_format($totalFailedRows) }}</h3>
                    <small class="text-danger"><i class="bi bi-exclamation-octagon"></i> Validation errors</small>
                </div>
                <div class="stat-icon bg-danger-subtle text-danger">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Ingestion Cards Grid -->
<div class="row g-4 mb-5">
    <!-- 1. Students Import -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100 border-top border-4 border-primary">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="stat-icon bg-primary-subtle text-primary" style="width: 36px; height: 36px; font-size: 1.1rem;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-0">Students</h5>
                </div>
                <p class="text-muted small flex-grow-1">
                    Ingest matriculated student profiles, link to departments and programs, and auto-provision student login accounts.
                </p>

                <div class="mb-3">
                    <a href="{{ route('admin.imports.template', 'students') }}" class="btn btn-sm btn-outline-secondary w-100 mb-3">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Template CSV
                    </a>

                    <form method="POST" action="{{ route('admin.imports.process', 'students') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-secondary">Upload CSV</label>
                            <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv,.txt" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Conflict Strategy</label>
                            <select name="on_duplicate" class="form-select form-select-sm">
                                <option value="skip">Skip existing</option>
                                <option value="update">Update existing</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                            <i class="bi bi-upload me-1"></i> Start Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Faculty / Instructors Import -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100 border-top border-4 border-info">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="stat-icon bg-info-subtle text-info" style="width: 36px; height: 36px; font-size: 1.1rem;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-0">Instructors</h5>
                </div>
                <p class="text-muted small flex-grow-1">
                    Upload academic faculty rosters, designation, department assignments, office rooms, and provision instructor accounts.
                </p>

                <div class="mb-3">
                    <a href="{{ route('admin.imports.template', 'instructors') }}" class="btn btn-sm btn-outline-secondary w-100 mb-3">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Template CSV
                    </a>

                    <form method="POST" action="{{ route('admin.imports.process', 'instructors') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-secondary">Upload CSV</label>
                            <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv,.txt" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Conflict Strategy</label>
                            <select name="on_duplicate" class="form-select form-select-sm">
                                <option value="skip">Skip existing</option>
                                <option value="update">Update existing</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-info text-white w-100 fw-semibold">
                            <i class="bi bi-upload me-1"></i> Start Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Courses Catalog Import -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100 border-top border-4 border-warning">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="stat-icon bg-warning-subtle text-warning" style="width: 36px; height: 36px; font-size: 1.1rem;">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-0">Courses</h5>
                </div>
                <p class="text-muted small flex-grow-1">
                    Import entire curriculum syllabus: course codes, titles, credit units, prerequisites, lecture & lab hours.
                </p>

                <div class="mb-3">
                    <a href="{{ route('admin.imports.template', 'courses') }}" class="btn btn-sm btn-outline-secondary w-100 mb-3">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Template CSV
                    </a>

                    <form method="POST" action="{{ route('admin.imports.process', 'courses') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-secondary">Upload CSV</label>
                            <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv,.txt" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Conflict Strategy</label>
                            <select name="on_duplicate" class="form-select form-select-sm">
                                <option value="skip">Skip existing</option>
                                <option value="update">Update existing</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-warning text-dark w-100 fw-semibold">
                            <i class="bi bi-upload me-1"></i> Start Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Enrollments & Historical Grades -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100 border-top border-4 border-success">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="stat-icon bg-success-subtle text-success" style="width: 36px; height: 36px; font-size: 1.1rem;">
                        <i class="bi bi-card-checklist"></i>
                    </div>
                    <h5 class="fw-bold mb-0">Enrollments & Grades</h5>
                </div>
                <p class="text-muted small flex-grow-1">
                    Batch upload student registrations, exam scores, and grade status with automated 4.0 GPA recalculation.
                </p>

                <div class="mb-3">
                    <a href="{{ route('admin.imports.template', 'enrollments') }}" class="btn btn-sm btn-outline-secondary w-100 mb-3">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Template CSV
                    </a>

                    <form method="POST" action="{{ route('admin.imports.process', 'enrollments') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-secondary">Upload CSV</label>
                            <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv,.txt" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Conflict Strategy</label>
                            <select name="on_duplicate" class="form-select form-select-sm">
                                <option value="skip">Skip existing</option>
                                <option value="update">Update existing</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-success w-100 fw-semibold">
                            <i class="bi bi-upload me-1"></i> Start Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historical Import Logs & Audit Trail -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-primary"></i> Ingestion Audit Logs
        </h5>
        <span class="badge bg-secondary-subtle text-secondary">{{ $logs->total() }} Total Records</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Date / Time</th>
                    <th>Initiator</th>
                    <th>Type</th>
                    <th>File Name</th>
                    <th>Total Rows</th>
                    <th>Imported</th>
                    <th>Updated</th>
                    <th>Failed</th>
                    <th>Status</th>
                    <th class="text-end">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td><small>{{ $log->created_at->format('Y-m-d H:i') }}</small></td>
                        <td>{{ $log->user->name ?? 'System' }}</td>
                        <td><span class="badge bg-light text-dark border text-uppercase">{{ $log->type }}</span></td>
                        <td><code class="text-muted">{{ $log->file_name }}</code></td>
                        <td><strong>{{ $log->total_rows }}</strong></td>
                        <td class="text-success fw-semibold">{{ $log->imported_rows }}</td>
                        <td class="text-info fw-semibold">{{ $log->updated_rows }}</td>
                        <td class="{{ $log->failed_rows > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $log->failed_rows }}</td>
                        <td>
                            @if($log->status === 'completed')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Success</span>
                            @elseif($log->status === 'partially_failed')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Partial Fail</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Failed</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.imports.show', $log) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye me-1"></i> Audit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No import jobs recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
