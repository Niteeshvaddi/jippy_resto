@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-magic text-primary"></i>
                        Passwordless Login
                    </h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="auth-icon">
                            <i class="fas fa-envelope fa-3x text-primary"></i>
                        </div>
                        <h5 class="mt-3">Sign in with Magic Link</h5>
                        <p class="text-muted">
                            Enter your email address and we'll send you a secure link to sign in instantly.
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

                    <form method="POST" action="{{ route('passwordless.send') }}">
                        @csrf
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="Enter your email address"
                                   autocomplete="email"
                                   autofocus
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-actions text-center">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-paper-plane"></i> Send Magic Link
                            </button>
                        </div>
                    </form>

                    <div class="mt-4">
                        <div class="divider">
                            <span>Or</span>
                        </div>
                        
                        <div class="alternative-login">
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-key"></i> Use Password Instead
                            </a>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="security-features">
                            <h6><i class="fas fa-shield-alt"></i> Why Magic Links?</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> No passwords to remember</li>
                                <li><i class="fas fa-check text-success"></i> More secure than passwords</li>
                                <li><i class="fas fa-check text-success"></i> One-click sign in</li>
                                <li><i class="fas fa-check text-success"></i> Links expire automatically</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Magic links are sent to your email and expire in 15 minutes.
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
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.security-features {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.security-features h6 {
    color: #495057;
    margin-bottom: 1rem;
}

.security-features li {
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
// Focus on input when page loads
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('email').focus();
});

// Auto-submit form when Enter is pressed
document.getElementById('email').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});
</script>
@endsection
