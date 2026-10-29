@extends('layouts.auth')

@section('title', 'Access Forbidden (403)')

@section('content')
<div class="auth-header">
    <div class="auth-logo bg-danger-subtle text-danger">
        <i class="bi bi-shield-x"></i>
    </div>
    <h3 class="fw-bold mb-1 text-slate-900">Access Restricted (403)</h3>
    <p class="text-muted small mb-0">Unauthorized Portal Action</p>
</div>

<div class="auth-body text-center">
    <div class="py-3">
        <p class="text-secondary mb-4">
            {{ $exception->getMessage() ?: 'Your account role does not have authorization to access this academic resource.' }}
        </p>
        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-primary fw-semibold px-4 shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Return to {{ auth()->check() ? 'Dashboard' : 'Login' }}
        </a>
    </div>
</div>
@endsection
