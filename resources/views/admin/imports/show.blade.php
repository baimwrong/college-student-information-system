@extends('layouts.app')

@section('title', 'Import Audit Details')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.imports.index') }}">Bulk Import Hub</a></li>
                <li class="breadcrumb-item active" aria-current="page">Audit #{{ $log->id }}</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Import Job Audit Report</h2>
        <p class="text-muted mb-0">Execution log for <strong>{{ $log->file_name }}</strong> (Entity: <span class="badge bg-light text-dark border text-uppercase">{{ $log->type }}</span>)</p>
    </div>
    <div>
        <a href="{{ route('admin.imports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Import Hub
        </a>
    </div>
</div>

<!-- Metrics summary -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <span class="text-muted small fw-semibold text-uppercase">Total Rows Processed</span>
            <h3 class="fw-bold my-1 text-dark">{{ $log->total_rows }}</h3>
            <small class="text-muted">Lines scanned</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <span class="text-muted small fw-semibold text-uppercase">Imported Clean</span>
            <h3 class="fw-bold my-1 text-success">{{ $log->imported_rows }}</h3>
            <small class="text-success"><i class="bi bi-check-circle"></i> New records created</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <span class="text-muted small fw-semibold text-uppercase">Updated Records</span>
            <h3 class="fw-bold my-1 text-info">{{ $log->updated_rows }}</h3>
            <small class="text-info"><i class="bi bi-arrow-repeat"></i> Existing records updated</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <span class="text-muted small fw-semibold text-uppercase">Rejected / Errors</span>
            <h3 class="fw-bold my-1 text-danger">{{ $log->failed_rows }}</h3>
            <small class="text-danger"><i class="bi bi-x-circle"></i> Integrity rejections</small>
        </div>
    </div>
</div>

<!-- Errors & Diagnostics Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-medical-fill text-danger"></i> Row-Level Validation & Rejection Breakdown
        </h5>
    </div>
    <div class="card-body p-0">
        @if(empty($log->errors))
            <div class="text-center py-5">
                <i class="bi bi-check-circle-fill text-success fs-1"></i>
                <h5 class="fw-bold mt-2">Zero Rejections</h5>
                <p class="text-muted mb-0">Every single row from this CSV file was processed and committed successfully.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px;">CSV Row #</th>
                            <th>Validation Diagnostic & Reason for Rejection</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($log->errors as $err)
                            <tr>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        Row {{ $err['row'] ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-danger fw-semibold">{{ $err['error'] ?? 'Unknown error' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
