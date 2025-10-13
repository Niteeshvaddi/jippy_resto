@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus text-success"></i>
                        Passwordless Registration
                    </h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="auth-icon">
                            <i class="fas fa-envelope fa-3x text-success"></i>
                        </div>
                        <h5 class="mt-3">Create Account with Magic Link</h5>
                        <p class="text-muted">
                            Enter your details and we'll send you a secure link to complete your registration.
                        </p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('passwordless.register.send') }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   placeholder="Enter your full name"
                                   autocomplete="name"
                                   autofocus
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="Enter your email address"
                                   autocomplete="email"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-actions text-center">
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-paper-plane"></i> Send Registration Link
                            </button>
                        </div>
                    </form>

                    <div class="mt-4">
                        <div class="divider">
                            <span>Or</span>
                        </div>
                        
                        <div class="alternative-registration">
                            <a href="{{ route('register') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-key"></i> Use Password Instead
                            </a>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="benefits">
                            <h6><i class="fas fa-star"></i> Benefits of Passwordless Registration</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> No passwords to create or remember</li>
                                <li><i class="fas fa-check text-success"></i> More secure than traditional passwords</li>
                                <li><i class="fas fa-check text-success"></i> Instant account activation</li>
                                <li><i class="fas fa-check text-success"></i> Email automatically verified</li>
                                <li><i class="fas fa-check text-success"></i> One-click account creation</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Registration links are sent to your email and expire in 30 minutes.
                        </small>
                    </div>

                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            Already have an account? 
                            <a href="{{ route('passwordless.login') }}">Sign in with magic link</a> or 
                            <a href="{{ route('login') }}">use password</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.auth-icon {
    margin-bottom: 1rem;
}

.form-control-lg {
    font-size: 1.1rem;
    padding: 0.75rem 1rem;
}

.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
}

.card-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
}

.card-header h4 {
    font-weight: 600;
}

.alert {
    border: none;
    border-radius: 8px;
}

.divider {
    text-align: center;
    margin: 1.5rem 0;
    position: relative;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e0e0e0;
}

.divider span {
    background: white;
    padding: 0 15px;
    color: #666;
    font-size: 14px;
}

.benefits {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.benefits h6 {
    color: #495057;
    margin-bottom: 1rem;
}

.benefits li {
    margin-bottom: 0.5rem;
    padding-left: 0.5rem;
}

@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
}
</style>

<script>
// Focus on first input when page loads
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('name').focus();
});

// Auto-advance to next field when Enter is pressed
document.getElementById('name').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('email').focus();
    }
});

// Auto-submit form when Enter is pressed on email field
document.getElementById('email').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});
</script>
@endsection
