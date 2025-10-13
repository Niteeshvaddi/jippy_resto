@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-bug text-info"></i>
                        Impersonation Debug Tool
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        This tool helps debug impersonation detection issues.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Current Status</h5>
                            <div id="current-status">
                                <p><strong>User ID:</strong> <span id="user-id">{{ auth()->id() ?? 'Not logged in' }}</span></p>
                                <p><strong>User Email:</strong> <span id="user-email">{{ auth()->user()->email ?? 'Not logged in' }}</span></p>
                                <p><strong>Impersonation Data:</strong> <span id="impersonation-data">Loading...</span></p>
                                <p><strong>Is Impersonated:</strong> <span id="is-impersonated" class="badge badge-secondary">Checking...</span></p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Actions</h5>
                            <div class="btn-group-vertical w-100">
                                <button class="btn btn-primary mb-2" onclick="checkImpersonationStatus()">
                                    <i class="fas fa-sync"></i> Refresh Status
                                </button>
                                <button class="btn btn-warning mb-2" onclick="clearImpersonationData()">
                                    <i class="fas fa-trash"></i> Clear Impersonation Data
                                </button>
                                <button class="btn btn-info mb-2" onclick="testImpersonationBanner()">
                                    <i class="fas fa-eye"></i> Test Banner Display
                                </button>
                                <button class="btn btn-success mb-2" onclick="simulateImpersonation()">
                                    <i class="fas fa-user-secret"></i> Simulate Impersonation
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>LocalStorage Data</h5>
                        <pre id="localstorage-data" class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;">Loading...</pre>
                    </div>

                    <div class="mt-4">
                        <h5>Console Logs</h5>
                        <div id="console-logs" class="bg-dark text-light p-3 rounded" style="max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                            <div>Console logs will appear here...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Override console.log to show logs in the page
const originalLog = console.log;
console.log = function(...args) {
    originalLog.apply(console, args);
    const logsDiv = document.getElementById('console-logs');
    const logEntry = document.createElement('div');
    logEntry.textContent = new Date().toLocaleTimeString() + ': ' + args.join(' ');
    logsDiv.appendChild(logEntry);
    logsDiv.scrollTop = logsDiv.scrollHeight;
};

function checkImpersonationStatus() {
    console.log('Checking impersonation status...');
    
    const impersonationData = localStorage.getItem('restaurant_impersonation');
    const userElement = document.getElementById('user-id');
    const emailElement = document.getElementById('user-email');
    const dataElement = document.getElementById('impersonation-data');
    const statusElement = document.getElementById('is-impersonated');
    
    if (impersonationData) {
        try {
            const data = JSON.parse(impersonationData);
            dataElement.textContent = JSON.stringify(data, null, 2);
            
            if (data.isImpersonated && data.admin_id && data.admin_email) {
                statusElement.textContent = 'YES';
                statusElement.className = 'badge badge-warning';
            } else {
                statusElement.textContent = 'NO (Invalid Data)';
                statusElement.className = 'badge badge-danger';
            }
        } catch (error) {
            dataElement.textContent = 'Error parsing data: ' + error.message;
            statusElement.textContent = 'NO (Corrupted)';
            statusElement.className = 'badge badge-danger';
        }
    } else {
        dataElement.textContent = 'No impersonation data found';
        statusElement.textContent = 'NO';
        statusElement.className = 'badge badge-success';
    }
    
    updateLocalStorageDisplay();
}

function clearImpersonationData() {
    console.log('Clearing impersonation data...');
    localStorage.removeItem('restaurant_impersonation');
    checkImpersonationStatus();
    console.log('Impersonation data cleared');
}

function testImpersonationBanner() {
    console.log('Testing impersonation banner display...');
    
    // Create test data
    const testData = {
        isImpersonated: true,
        admin_id: 'test_admin_123',
        admin_email: 'admin@test.com',
        impersonatedAt: new Date().toISOString()
    };
    
    localStorage.setItem('restaurant_impersonation', JSON.stringify(testData));
    console.log('Test impersonation data set');
    
    // Reload page to trigger banner
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function simulateImpersonation() {
    console.log('Simulating impersonation session...');
    
    const testData = {
        isImpersonated: true,
        admin_id: 'admin_123',
        admin_email: 'admin@restaurant-system.com',
        impersonatedAt: new Date().toISOString(),
        restaurant_uid: 'test_restaurant_456'
    };
    
    localStorage.setItem('restaurant_impersonation', JSON.stringify(testData));
    console.log('Impersonation simulation data set');
    
    checkImpersonationStatus();
}

function updateLocalStorageDisplay() {
    const display = document.getElementById('localstorage-data');
    const allData = {};
    
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        allData[key] = localStorage.getItem(key);
    }
    
    display.textContent = JSON.stringify(allData, null, 2);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Impersonation debug tool loaded');
    checkImpersonationStatus();
    updateLocalStorageDisplay();
});
</script>

<style>
.badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
}

.btn-group-vertical .btn {
    text-align: left;
}

pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}

#console-logs div {
    margin-bottom: 0.25rem;
    padding: 0.25rem;
    border-bottom: 1px solid #444;
}

#console-logs div:last-child {
    border-bottom: none;
}
</style>
@endsection
