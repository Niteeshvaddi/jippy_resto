@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Enable Two-Factor Authentication</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Two-factor authentication adds an extra layer of security to your account by requiring a verification code from your mobile device.
                    </div>

                    <div class="setup-steps">
                        <div class="step">
                            <h5><i class="fas fa-mobile-alt text-primary"></i> Step 1: Install an Authenticator App</h5>
                            <p>Download and install one of these authenticator apps on your mobile device:</p>
                            <ul class="list-unstyled">
                                <li><i class="fab fa-google-play"></i> <strong>Google Authenticator</strong> (iOS/Android)</li>
                                <li><i class="fas fa-shield-alt"></i> <strong>Authy</strong> (iOS/Android)</li>
                                <li><i class="fas fa-lock"></i> <strong>Microsoft Authenticator</strong> (iOS/Android)</li>
                            </ul>
                        </div>

                        <div class="step">
                            <h5><i class="fas fa-qrcode text-primary"></i> Step 2: Scan QR Code</h5>
                            <p>Open your authenticator app and scan this QR code:</p>
                            
                            <div class="text-center mb-3">
                                <div class="qr-code-container">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" 
                                         alt="QR Code" class="qr-code">
                                </div>
                            </div>

                            <div class="manual-key">
                                <p><strong>Can't scan the QR code?</strong> Enter this key manually:</p>
                                <div class="input-group">
                                    <input type="text" class="form-control font-monospace" 
                                           value="{{ $secretKey }}" readonly id="secretKey">
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="step">
                            <h5><i class="fas fa-check-circle text-primary"></i> Step 3: Verify Setup</h5>
                            <p>Enter the 6-digit code from your authenticator app to complete the setup:</p>
                            
                            <form method="POST" action="{{ route('2fa.enable') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="code">Verification Code</label>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           placeholder="000000" 
                                           maxlength="6" 
                                           pattern="[0-9]{6}"
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-shield-alt"></i> Enable 2FA
                                    </button>
                                    <a href="{{ route('home') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.setup-steps .step {
    margin-bottom: 2rem;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
}

.setup-steps .step h5 {
    margin-bottom: 1rem;
    color: #495057;
}

.qr-code-container {
    display: inline-block;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.qr-code {
    max-width: 200px;
    height: auto;
}

.manual-key {
    margin-top: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.form-actions {
    margin-top: 1.5rem;
    display: flex;
    gap: 1rem;
}

.font-monospace {
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<script>
function copyToClipboard() {
    const secretKey = document.getElementById('secretKey');
    secretKey.select();
    secretKey.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Copied!';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}

// Auto-format verification code input
document.getElementById('code').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>
@endsection
