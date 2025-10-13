@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-shield-alt text-primary"></i>
                        Two-Factor Authentication
                    </h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="auth-icon">
                            <i class="fas fa-mobile-alt fa-3x text-primary"></i>
                        </div>
                        <h5 class="mt-3">Enter Verification Code</h5>
                        <p class="text-muted">
                            Please enter the 6-digit code from your authenticator app to continue.
                        </p>
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('2fa.verify.post') }}">
                        @csrf
                        <div class="form-group">
                            <label for="code" class="form-label">Verification Code</label>
                            <input type="text" 
                                   class="form-control form-control-lg text-center @error('code') is-invalid @enderror" 
                                   id="code" 
                                   name="code" 
                                   placeholder="000000" 
                                   maxlength="6" 
                                   pattern="[0-9]{6}"
                                   autocomplete="off"
                                   autofocus
                                   required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-actions text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check"></i> Verify & Continue
                            </button>
                        </div>
                    </form>

                    <div class="mt-4">
                        <div class="text-center">
                            <button type="button" class="btn btn-link" data-bs-toggle="collapse" data-bs-target="#backupCodes">
                                <i class="fas fa-key"></i> Use Backup Code Instead
                            </button>
                        </div>
                        
                        <div class="collapse mt-3" id="backupCodes">
                            <div class="card card-body">
                                <h6>Backup Code</h6>
                                <p class="text-muted small">
                                    If you don't have access to your authenticator app, you can use one of your backup codes.
                                </p>
                                <div class="form-group">
                                    <input type="text" 
                                           class="form-control @error('backup_code') is-invalid @enderror" 
                                           name="backup_code" 
                                           placeholder="Enter backup code"
                                           maxlength="8">
                                    @error('backup_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    Use Backup Code
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Having trouble? Contact support for assistance.
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
    font-size: 1.5rem;
    letter-spacing: 0.5rem;
    font-weight: 600;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-lg {
    padding: 0.75rem 2rem;
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

.form-actions {
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .form-control-lg {
        font-size: 1.25rem;
        letter-spacing: 0.25rem;
    }
}
</style>

<script>
// Auto-format verification code input
document.getElementById('code').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    
    // Auto-submit when 6 digits are entered
    if (this.value.length === 6) {
        setTimeout(() => {
            this.form.submit();
        }, 500);
    }
});

// Focus on input when page loads
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('code').focus();
});

// Handle backup code form submission
document.querySelector('#backupCodes button[type="submit"]').addEventListener('click', function(e) {
    e.preventDefault();
    const backupCode = document.querySelector('input[name="backup_code"]').value;
    if (backupCode) {
        document.querySelector('input[name="code"]').value = backupCode;
        document.querySelector('form').submit();
    }
});
</script>
@endsection
