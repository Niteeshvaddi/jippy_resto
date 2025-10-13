<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magic Login Link</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .button {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background: #0056b3;
        }
        .security-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .expiry {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Magic Login Link</h1>
        <p>Sign in to your account without a password</p>
    </div>
    
    <div class="content">
        <h2>Hello!</h2>
        
        <p>You requested a magic login link for your account. Click the button below to sign in instantly:</p>
        
        <div style="text-align: center;">
            <a href="{{ $magicLink }}" class="button">Sign In Now</a>
        </div>
        
        <p>Or copy and paste this link into your browser:</p>
        <p style="word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 4px; font-family: monospace;">
            {{ $magicLink }}
        </p>
        
        <div class="security-info">
            <h4>🛡️ Security Information</h4>
            <ul>
                <li><strong>Expires:</strong> <span class="expiry">{{ $expiresAt->format('M j, Y \a\t g:i A') }}</span></li>
                <li><strong>Requested from:</strong> {{ $ip }}</li>
                <li><strong>One-time use:</strong> This link can only be used once</li>
                <li><strong>Secure:</strong> This link is encrypted and time-limited</li>
            </ul>
        </div>
        
        <p><strong>Didn't request this?</strong> If you didn't request this magic login link, you can safely ignore this email. Your account remains secure.</p>
        
        <p>For security reasons, this link will expire in 15 minutes.</p>
    </div>
    
    <div class="footer">
        <p>This email was sent from {{ config('app.name') }}</p>
        <p>If you have any questions, please contact our support team.</p>
    </div>
</body>
</html>
