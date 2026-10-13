@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<div class="auth-header">
    <div class="auth-logo">
        <i class="bi bi-mortarboard-fill"></i>
    </div>
    <h4 class="fw-bold mb-1 text-slate-900">{{ config('app.name', 'College Student Information System') }}</h4>
    <p class="text-muted small mb-0">Academic & Matriculation Portal</p>
</div>

<div class="auth-body">
    @if(session('info'))
        <div class="alert alert-info py-2 small mb-3">
            {{ session('info') }}
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

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold small text-secondary">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0">
                    <i class="bi bi-envelope"></i>
                </span>
                <input type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', 'admin@college.edu') }}" required autofocus placeholder="name@college.edu">
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-semibold small text-secondary">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" id="password" name="password" required value="password">
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                <label class="form-check-label small text-secondary" for="remember">
                    Remember me
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Portal
        </button>
    </form>

    <div class="mt-4 pt-3 border-top text-center">
        <p class="text-muted small mb-2 fw-semibold">Quick Demo Credentials:</p>
        <div class="d-flex justify-content-center gap-1 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;" onclick="fillCredentials('admin@college.edu', 'password')">
                <i class="bi bi-shield-lock me-1"></i> Admin
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;" onclick="fillCredentials('dr.alan@college.edu', 'password')">
                <i class="bi bi-person-badge me-1"></i> Instructor
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;" onclick="fillCredentials('student@college.edu', 'password')">
                <i class="bi bi-person me-1"></i> Student
            </button>
        </div>
    </div>
</div>

<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
