@extends('layouts.auth')

@section('title', 'Set New Password')

@section('content')
<div class="auth-header">
    <div class="auth-logo">
        <i class="bi bi-shield-lock-fill"></i>
    </div>
    <h4 class="fw-bold mb-1 text-slate-900">Set New Password</h4>
    <p class="text-muted small mb-0">Create a new secure password for your portal account.</p>
</div>

<div class="auth-body">
    @if($errors->any())
        <div class="alert alert-danger py-2 small mb-3">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold small text-secondary">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0">
                    <i class="bi bi-envelope"></i>
                </span>
                <input type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $email) }}" required autofocus placeholder="name@college.edu">
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-semibold small text-secondary">New Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Minimum 8 characters">
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold small text-secondary">Confirm New Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0">
                    <i class="bi bi-lock-fill"></i>
                </span>
                <input type="password" class="form-control border-start-0 ps-0" id="password_confirmation" name="password_confirmation" required placeholder="Re-type your password">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
            <i class="bi bi-check2-circle me-1"></i> Reset & Update Password
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none small text-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Sign In
            </a>
        </div>
    </form>
</div>
@endsection
