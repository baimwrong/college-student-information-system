@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="auth-header">
    <div class="auth-logo">
        <i class="bi bi-key-fill"></i>
    </div>
    <h4 class="fw-bold mb-1 text-slate-900">Reset Portal Password</h4>
    <p class="text-muted small mb-0">Enter your university email address to receive password reset instructions.</p>
</div>

<div class="auth-body">
    @if(session('status'))
        <div class="alert alert-success py-2 small mb-3">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
        </div>
    @endif

    @if(session('demo_reset_link'))
        <div class="alert alert-info py-2 small mb-3">
            <strong>Local Environment Reset Link:</strong><br>
            <a href="{{ session('demo_reset_link') }}" class="alert-link text-break" style="font-size: 0.8rem;">
                Click here to reset your password directly
            </a>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger py-2 small mb-3">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label fw-semibold small text-secondary">University Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0">
                    <i class="bi bi-envelope"></i>
                </span>
                <input type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@college.edu">
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
            <i class="bi bi-send-fill me-1"></i> Send Password Reset Link
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none small text-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Sign In
            </a>
        </div>
    </form>
</div>
@endsection
