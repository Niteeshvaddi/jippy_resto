@php
    $providers = [
        'google' => [
            'name' => 'Google',
            'icon' => 'fab fa-google',
            'color' => '#4285F4',
            'enabled' => config('services.google.client_id') && config('services.google.client_secret')
        ],
        'facebook' => [
            'name' => 'Facebook',
            'icon' => 'fab fa-facebook-f',
            'color' => '#1877F2',
            'enabled' => config('services.facebook.client_id') && config('services.facebook.client_secret')
        ],
        'github' => [
            'name' => 'GitHub',
            'icon' => 'fab fa-github',
            'color' => '#333',
            'enabled' => config('services.github.client_id') && config('services.github.client_secret')
        ]
    ];
    
    $enabledProviders = array_filter($providers, function($provider) {
        return $provider['enabled'];
    });
@endphp

@if(count($enabledProviders) > 0)
    <div class="social-login-section">
        <div class="divider">
            <span>Or continue with</span>
        </div>
        
        <div class="social-login-buttons">
            @foreach($enabledProviders as $provider => $config)
                <a href="{{ route('social.login', $provider) }}" 
                   class="social-login-btn social-login-btn-{{ $provider }}"
                   style="background-color: {{ $config['color'] }};"
                   title="Login with {{ $config['name'] }}">
                    <i class="{{ $config['icon'] }}"></i>
                    <span>{{ $config['name'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <style>
        .social-login-section {
            margin: 20px 0;
        }
        
        .divider {
            text-align: center;
            margin: 20px 0;
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
        
        .social-login-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .social-login-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .social-login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            color: white;
            text-decoration: none;
        }
        
        .social-login-btn i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .social-login-btn-google:hover {
            background-color: #3367D6 !important;
        }
        
        .social-login-btn-facebook:hover {
            background-color: #166FE5 !important;
        }
        
        .social-login-btn-github:hover {
            background-color: #24292e !important;
        }
        
        @media (min-width: 768px) {
            .social-login-buttons {
                flex-direction: row;
            }
            
            .social-login-btn {
                flex: 1;
            }
        }
    </style>
@endif
