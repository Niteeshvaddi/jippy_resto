@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-key text-warning"></i>
                        Backup Codes Generated
                    </h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important:</strong> Save these backup codes in a safe place. You can use them to access your account if you lose your authenticator device.
                    </div>

                    <div class="backup-codes-container">
                        <h5 class="text-center mb-4">Your Backup Codes</h5>
                        
                        <div class="row">
                            @foreach($backupCodes as $index => $code)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="backup-code-item">
                                        <span class="code-number">{{ $index + 1 }}.</span>
                                        <span class="code-value font-monospace">{{ $code }}</span>
                                        <button class="btn btn-sm btn-outline-secondary copy-code" 
                                                data-code="{{ $code }}" 
                                                title="Copy code">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="security-tips mt-4">
                        <h6><i class="fas fa-shield-alt"></i> Security Tips:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Store these codes in a secure password manager</li>
                            <li><i class="fas fa-check text-success"></i> Print them and keep them in a safe physical location</li>
                            <li><i class="fas fa-check text-success"></i> Never share these codes with anyone</li>
                            <li><i class="fas fa-check text-success"></i> Each code can only be used once</li>
                            <li><i class="fas fa-check text-success"></i> You can regenerate new codes anytime</li>
                        </ul>
                    </div>

                    <div class="actions mt-4">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <button class="btn btn-primary w-100" onclick="printCodes()">
                                    <i class="fas fa-print"></i> Print Codes
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button class="btn btn-success w-100" onclick="downloadCodes()">
                                    <i class="fas fa-download"></i> Download as Text
                                </button>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="{{ route('home') }}" class="btn btn-outline-primary">
                                <i class="fas fa-home"></i> Continue to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.backup-codes-container {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem;
    margin: 1.5rem 0;
}

.backup-code-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.backup-code-item:hover {
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0,123,255,0.1);
}

.code-number {
    font-weight: 600;
    color: #6c757d;
    margin-right: 0.5rem;
    min-width: 2rem;
}

.code-value {
    flex: 1;
    font-size: 1.1rem;
    font-weight: 600;
    color: #495057;
    letter-spacing: 0.1rem;
}

.copy-code {
    margin-left: 0.5rem;
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

.copy-code:hover {
    opacity: 1;
}

.security-tips {
    background: #e7f3ff;
    border: 1px solid #b3d9ff;
    border-radius: 6px;
    padding: 1.5rem;
}

.security-tips h6 {
    color: #0066cc;
    margin-bottom: 1rem;
}

.security-tips li {
    margin-bottom: 0.5rem;
    padding-left: 0.5rem;
}

.actions .btn {
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .backup-codes-container {
        padding: 1rem;
    }
    
    .backup-code-item {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .code-number {
        min-width: auto;
    }
}
</style>

<script>
// Copy individual backup code
document.querySelectorAll('.copy-code').forEach(button => {
    button.addEventListener('click', function() {
        const code = this.getAttribute('data-code');
        navigator.clipboard.writeText(code).then(() => {
            // Show feedback
            const icon = this.querySelector('i');
            const originalClass = icon.className;
            icon.className = 'fas fa-check text-success';
            
            setTimeout(() => {
                icon.className = originalClass;
            }, 2000);
        });
    });
});

// Print backup codes
function printCodes() {
    const printWindow = window.open('', '_blank');
    const codes = @json($backupCodes);
    
    let printContent = `
        <html>
        <head>
            <title>Backup Codes - {{ config('app.name') }}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #333; text-align: center; }
                .codes { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 20px 0; }
                .code { padding: 10px; border: 1px solid #ccc; text-align: center; font-family: monospace; font-size: 16px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
            </style>
        </head>
        <body>
            <h1>Two-Factor Authentication Backup Codes</h1>
            <div class="warning">
                <strong>Important:</strong> Store these codes in a safe place. Each code can only be used once.
            </div>
            <div class="codes">
    `;
    
    codes.forEach((code, index) => {
        printContent += `<div class="code">${index + 1}. ${code}</div>`;
    });
    
    printContent += `
            </div>
            <p><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
        </body>
        </html>
    `;
    
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.print();
}

// Download backup codes as text file
function downloadCodes() {
    const codes = @json($backupCodes);
    let content = `Two-Factor Authentication Backup Codes\n`;
    content += `Generated: ${new Date().toLocaleString()}\n\n`;
    content += `Important: Store these codes in a safe place. Each code can only be used once.\n\n`;
    
    codes.forEach((code, index) => {
        content += `${index + 1}. ${code}\n`;
    });
    
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'backup-codes.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
@endsection
