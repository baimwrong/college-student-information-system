@extends('layouts.auth')

@section('title', 'Server Error (500)')

@section('content')
<div class="auth-header">
    <div class="auth-logo bg-danger-subtle text-danger">
        <i class="bi bi-exclamation-octagon-fill"></i>
    </div>
    <h3 class="fw-bold mb-1 text-slate-900">System Error (500)</h3>
    <p class="text-muted small mb-0">Unexpected Server Exception</p>
</div>

<div class="auth-body text-center">
    <div class="py-3">
        <p class="text-secondary mb-4">
            An unexpected error occurred while processing this operation. Our system administrators have been logged of the incident. Please try again shortly or contact the Registrar.
        </p>
        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-primary fw-semibold px-4 shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Return to Safety
        </a>
    </div>
</div>
@endsection
