@extends('layouts.auth')

@section('title', 'Page Not Found (404)')

@section('content')
<div class="auth-header">
    <div class="auth-logo bg-warning-subtle text-warning">
        <i class="bi bi-search"></i>
    </div>
    <h3 class="fw-bold mb-1 text-slate-900">Resource Not Found (404)</h3>
    <p class="text-muted small mb-0">Record or Page Unavailable</p>
</div>

<div class="auth-body text-center">
    <div class="py-3">
        <p class="text-secondary mb-4">
            The requested course, student record, or portal page could not be located in the university registry.
        </p>
        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-primary fw-semibold px-4 shadow-sm">
            <i class="bi bi-house-door me-1"></i> Return to {{ auth()->check() ? 'Dashboard' : 'Portal Login' }}
        </a>
    </div>
</div>
@endsection
