<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>QZ Tray Dashboard - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --qz-primary: #2c3e50;
            --qz-secondary: #3498db;
            --qz-success: #27ae60;
            --qz-danger: #e74c3c;
            --qz-warning: #f39c12;
            --qz-info: #17a2b8;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .qz-dashboard {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            min-height: 90vh;
            margin: 20px;
            overflow: hidden;
        }

        .qz-sidebar {
            background: var(--qz-primary);
            color: white;
            padding: 0;
        }

        .qz-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }

        .qz-sidebar .nav-link:hover,
        .qz-sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: var(--qz-secondary);
        }

        .qz-sidebar .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 10px;
        }

        .qz-content {
            padding: 30px;
            background: #f8f9fa;
        }

        .qz-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .qz-card:hover {
            transform: translateY(-5px);
        }

        .qz-card-header {
            background: transparent;
            border-bottom: 2px solid #f8f9fa;
            padding: 20px;
            font-weight: 600;
        }

        .qz-card-body {
            padding: 20px;
        }

        .qz-status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .qz-status-connected {
            background: rgba(39, 174, 96, 0.1);
            color: var(--qz-success);
        }

        .qz-status-disconnected {
            background: rgba(231, 76, 60, 0.1);
            color: var(--qz-danger);
        }

        .qz-status-connecting {
            background: rgba(243, 156, 18, 0.1);
            color: var(--qz-warning);
            animation: pulse 1.5s infinite;
        }

        .qz-printer-item {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .qz-printer-item:hover {
            border-color: var(--qz-secondary);
            background: rgba(52, 152, 219, 0.05);
        }

        .qz-printer-item.selected {
            border-color: var(--qz-success);
            background: rgba(39, 174, 96, 0.05);
        }

        .qz-printer-item .badge {
            font-size: 10px;
            padding: 3px 8px;
        }

        .qz-stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 15px;
            color: white;
            margin-bottom: 20px;
        }

        .qz-stat-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .qz-stat-success {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        }

        .qz-stat-warning {
            background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%);
        }

        .qz-stat-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }

        .qz-stat-card .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin: 10px 0;
        }

        .qz-stat-card .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .qz-health-meter {
            height: 10px;
            background: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
            margin: 10px 0;
        }

        .qz-health-fill {
            height: 100%;
            transition: width 0.5s ease;
        }

        .qz-health-excellent {
            background: linear-gradient(90deg, #27ae60, #2ecc71);
        }

        .qz-health-good {
            background: linear-gradient(90deg, #2ecc71, #f1c40f);
        }

        .qz-health-fair {
            background: linear-gradient(90deg, #f1c40f, #f39c12);
        }

        .qz-health-poor {
            background: linear-gradient(90deg, #f39c12, #e74c3c);
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .qz-btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }

        .qz-btn-primary {
            background: var(--qz-secondary);
            color: white;
        }

        .qz-btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .qz-btn-success {
            background: var(--qz-success);
            color: white;
        }

        .qz-btn-danger {
            background: var(--qz-danger);
            color: white;
        }

        .qz-btn-outline {
            background: transparent;
            border: 2px solid var(--qz-secondary);
            color: var(--qz-secondary);
        }

        .qz-btn-outline:hover {
            background: var(--qz-secondary);
            color: white;
        }

        .qz-modal {
            border-radius: 15px;
            border: none;
        }

        .qz-modal-header {
            background: var(--qz-primary);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }

        .qz-modal-body {
            padding: 30px;
        }

        .qz-toast {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .qz-dashboard {
                margin: 10px;
                border-radius: 10px;
            }

            .qz-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row qz-dashboard">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 qz-sidebar">
            <div class="d-flex flex-column align-items-center py-4">
                <div class="mb-4">
                    <i class="fas fa-print fa-3x text-white"></i>
                </div>
                <h4 class="text-white mb-0">QZ Tray</h4>
                <small class="text-muted">Enterprise v2.2.5</small>
            </div>

            <nav class="nav flex-column mt-4">
                <a class="nav-link active" href="#dashboard" data-tab="dashboard">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="#printers" data-tab="printers">
                    <i class="fas fa-print"></i> Printers
                </a>
                <a class="nav-link" href="#print" data-tab="print">
                    <i class="fas fa-paper-plane"></i> Print
                </a>
                <a class="nav-link" href="#jobs" data-tab="jobs">
                    <i class="fas fa-tasks"></i> Jobs
                </a>
                <a class="nav-link" href="#diagnostics" data-tab="diagnostics">
                    <i class="fas fa-stethoscope"></i> Diagnostics
                </a>
                <a class="nav-link" href="#settings" data-tab="settings">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </nav>

            <div class="mt-auto p-4">
                <div id="qz-status" class="qz-status-badge qz-status-connecting">
                    <i class="fas fa-circle fa-xs"></i> Connecting...
                </div>
                <small class="text-muted d-block mt-2" id="qz-version">Version 2.2.5</small>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 qz-content">
            <!-- Status Bar -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="qz-card">
                        <div class="qz-card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">QZ Tray Management</h4>
                                    <small class="text-muted" id="qz-connection-info">Initializing connection...</small>
                                </div>
                                <div>
                                    <button class="qz-btn qz-btn-primary me-2" onclick="qzManager.connect()">
                                        <i class="fas fa-plug"></i> Connect
                                    </button>
                                    <button class="qz-btn qz-btn-outline" onclick="runDiagnostics()">
                                        <i class="fas fa-stethoscope"></i> Diagnostics
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Tab -->
            <div id="dashboard-tab" class="tab-content">
                <div class="row">
                    <!-- Stats Cards -->
                    <div class="col-md-3">
                        <div class="qz-stat-card qz-stat-primary">
                            <i class="fas fa-print fa-2x"></i>
                            <div class="stat-number" id="stat-printers">0</div>
                            <div class="stat-label">Printers</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="qz-stat-card qz-stat-success">
                            <i class="fas fa-tasks fa-2x"></i>
                            <div class="stat-number" id="stat-jobs">0</div>
                            <div class="stat-label">Jobs Today</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="qz-stat-card qz-stat-warning">
                            <i class="fas fa-clock fa-2x"></i>
                            <div class="stat-number" id="stat-queue">0</div>
                            <div class="stat-label">In Queue</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="qz-stat-card qz-stat-danger">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                            <div class="stat-number" id="stat-errors">0</div>
                            <div class="stat-label">Errors</div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <!-- Printer List -->
                    <div class="col-md-8">
                        <div class="qz-card">
                            <div class="qz-card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-print me-2"></i>Available Printers</span>
                                <button class="qz-btn qz-btn-outline btn-sm" onclick="discoverPrinters()">
                                    <i class="fas fa-sync"></i> Refresh
                                </button>
                            </div>
                            <div class="qz-card-body">
                                <div id="printer-list">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">Discovering printers...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Health -->
                    <div class="col-md-4">
                        <div class="qz-card">
                            <div class="qz-card-header">
                                <i class="fas fa-heartbeat me-2"></i> System Health
                            </div>
                            <div class="qz-card-body">
                                <div class="text-center mb-4">
                                    <div class="qz-health-meter">
                                        <div id="health-fill" class="qz-health-fill qz-health-excellent" style="width: 0%"></div>
                                    </div>
                                    <h2 id="health-score" class="mt-3">0%</h2>
                                    <p id="health-status" class="text-muted">Checking...</p>
                                </div>

                                <div class="mt-4">
                                    <h6>Quick Actions</h6>
                                    <button class="qz-btn qz-btn-success w-100 mb-2" onclick="testPrint()">
                                        <i class="fas fa-print"></i> Test Print
                                    </button>
                                    <button class="qz-btn qz-btn-outline w-100 mb-2" onclick="showPrintModal()">
                                        <i class="fas fa-paper-plane"></i> Quick Print
                                    </button>
                                    <button class="qz-btn qz-btn-outline w-100" onclick="window.location.href='#diagnostics'">
                                        <i class="fas fa-chart-line"></i> View Diagnostics
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Jobs -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="qz-card">
                            <div class="qz-card-header">
                                <i class="fas fa-history me-2"></i> Recent Jobs
                            </div>
                            <div class="qz-card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                        <tr>
                                            <th>Job ID</th>
                                            <th>Type</th>
                                            <th>Printer</th>
                                            <th>Status</th>
                                            <th>Time</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody id="recent-jobs-table">
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Printers Tab -->
            <div id="printers-tab" class="tab-content" style="display: none;">
                <!-- Printer management content -->
            </div>

            <!-- Print Tab -->
            <div id="print-tab" class="tab-content" style="display: none;">
                <!-- Print content -->
            </div>

            <!-- Jobs Tab -->
            <div id="jobs-tab" class="tab-content" style="display: none;">
                <!-- Jobs content -->
            </div>

            <!-- Diagnostics Tab -->
            <div id="diagnostics-tab" class="tab-content" style="display: none;">
                <!-- Diagnostics content -->
            </div>

            <!-- Settings Tab -->
            <div id="settings-tab" class="tab-content" style="display: none;">
                <!-- Settings content -->
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade qz-modal" id="printModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="qz-modal-header">
                <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i> Print Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="qz-modal-body">
                <!-- Print form -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade qz-modal" id="printerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="qz-modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i> Printer Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="qz-modal-body">
                <!-- Printer details -->
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- QZ Tray Scripts -->
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.5/dist/js/qz-tray.js"></script>
<script src="{{ asset('vendor/qz-tray/js/qz-tray/qz-manager.js') }}"></script>
<script src="{{ asset('vendor/qz-tray/js/qz-tray/printer-manager.js') }}"></script>
<script src="{{ asset('vendor/qz-tray/js/qz-tray/print-engine.js') }}"></script>
<script src="{{ asset('vendor/qz-tray/js/qz-tray/diagnostics.js') }}"></script>
<script src="{{ asset('vendor/qz-tray/js/app.js') }}"></script>

<script>
    // Global QZ Manager instance
    let qzManager;

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Configure Toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };

        // Initialize QZ Manager
        qzManager = new QzTrayManager({
            autoConnect: true,
            discoveryInterval: 30000,
            fallbackStrategy: 'browser-print'
        });

        // Setup event listeners
        setupEventListeners();

        // Setup tab navigation
        setupTabs();

        // Load initial data
        loadInitialData();
    });

    function setupEventListeners() {
        // QZ Manager events
        qzManager.on('connected', function(data) {
            updateStatus('connected', 'Connected to QZ Tray v' + data.version);
            toastr.success('Connected to QZ Tray', 'Connection Successful');
            loadPrinters();
            loadRecentJobs();
        });

        qzManager.on('disconnected', function() {
            updateStatus('disconnected', 'Disconnected from QZ Tray');
            toastr.warning('Disconnected from QZ Tray', 'Connection Lost');
        });

        qzManager.on('error', function(data) {
            console.error('QZ Tray error:', data);
            toastr.error(data.error?.message || 'Unknown error', 'Error');
        });

        qzManager.on('printersDiscovered', function(data) {
            updatePrinterList(data.printers);
            updateStats('printers', data.printers.length);
        });

        qzManager.on('printerSelected', function(data) {
            toastr.info(`Selected printer: ${data.current}`, 'Printer Changed');
            updateSelectedPrinter(data.current);
        });

        qzManager.on('jobQueued', function(job) {
            toastr.info(`Job ${job.id} added to queue`, 'Print Job Queued');
            updateStats('queue', qzManager.state.jobQueue.length);
        });

        qzManager.on('jobCompleted', function(job) {
            toastr.success(`Job ${job.id} completed`, 'Print Successful');
            updateStats('queue', qzManager.state.jobQueue.length);
            loadRecentJobs();
        });

        qzManager.on('jobFailed', function(job) {
            toastr.error(`Job ${job.id} failed: ${job.error?.message}`, 'Print Failed');
            updateStats('errors', qzManager.state.errors.length + 1);
            updateStats('queue', qzManager.state.jobQueue.length);
        });
    }

    function setupTabs() {
        // Tab navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Update active tab
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                // Show corresponding tab content
                const tab = this.getAttribute('data-tab');
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.style.display = 'none';
                });
                document.getElementById(`${tab}-tab`).style.display = 'block';

                // Load tab specific data
                loadTabData(tab);
            });
        });
    }

    function loadInitialData() {
        // Initial status
        updateStatus('connecting', 'Initializing QZ Tray...');

        // Load version
        document.getElementById('qz-version').textContent = `Version ${qzManager.config.version}`;

        // Initialize manager
        qzManager.initialize().catch(error => {
            console.error('Initialization error:', error);
            toastr.error('Failed to initialize QZ Tray', 'Initialization Error');
        });
    }

    function loadTabData(tab) {
        switch(tab) {
            case 'dashboard':
                loadPrinters();
                loadRecentJobs();
                break;
            case 'printers':
                loadPrinterManagement();
                break;
            case 'print':
                loadPrintInterface();
                break;
            case 'jobs':
                loadJobHistory();
                break;
            case 'diagnostics':
                loadDiagnostics();
                break;
            case 'settings':
                loadSettings();
                break;
        }
    }

    // Status update functions
    function updateStatus(status, message) {
        const statusBadge = document.getElementById('qz-status');
        const connectionInfo = document.getElementById('qz-connection-info');

        statusBadge.className = 'qz-status-badge';

        switch(status) {
            case 'connected':
                statusBadge.classList.add('qz-status-connected');
                statusBadge.innerHTML = '<i class="fas fa-circle fa-xs"></i> Connected';
                break;
            case 'disconnected':
                statusBadge.classList.add('qz-status-disconnected');
                statusBadge.innerHTML = '<i class="fas fa-circle fa-xs"></i> Disconnected';
                break;
            case 'connecting':
                statusBadge.classList.add('qz-status-connecting');
                statusBadge.innerHTML = '<i class="fas fa-circle fa-xs"></i> Connecting...';
                break;
        }

        connectionInfo.textContent = message;
    }

    async function discoverPrinters() {
        try {
            toastr.info('Discovering printers...', 'Please wait');
            const result = await qzManager.discoverPrinters();
            toastr.success(`Found ${result.printers.length} printers`, 'Discovery Complete');
        } catch (error) {
            toastr.error(error.message, 'Discovery Failed');
        }
    }

    function updatePrinterList(printers) {
        const container = document.getElementById('printer-list');

        if (printers.length === 0) {
            container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-print fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No printers found</p>
                        <button class="qz-btn qz-btn-outline" onclick="discoverPrinters()">
                            <i class="fas fa-search"></i> Discover Printers
                        </button>
                    </div>
                `;
            return;
        }

        let html = '';

        printers.forEach(printer => {
            const isDefault = printer === qzManager.state.defaultPrinter;
            const isSelected = printer === qzManager.state.selectedPrinter;
            const type = qzManager.detectPrinterType(printer);

            html += `
                    <div class="qz-printer-item ${isSelected ? 'selected' : ''}"
                         onclick="selectPrinter('${printer}')"
                         data-bs-toggle="tooltip"
                         title="Click to select">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${printer}</h6>
                                <small class="text-muted">
                                    <span class="badge bg-${getTypeColor(type)}">${type}</span>
                                    ${isDefault ? '<span class="badge bg-info ms-1">Default</span>' : ''}
                                </small>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-info"
                                        onclick="showPrinterInfo('${printer}'); event.stopPropagation();">
                                    <i class="fas fa-info"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success ms-1"
                                        onclick="testPrinter('${printer}'); event.stopPropagation();">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
        });

        container.innerHTML = html;

        // Initialize tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    }

    function getTypeColor(type) {
        const colors = {
            'label': 'success',
            'receipt': 'warning',
            'virtual': 'secondary',
            'network': 'primary',
            'usb': 'info',
            'standard': 'dark'
        };
        return colors[type] || 'dark';
    }

    async function selectPrinter(printerName) {
        try {
            await qzManager.selectPrinter(printerName);
        } catch (error) {
            toastr.error(error.message, 'Selection Failed');
        }
    }

    function updateSelectedPrinter(printerName) {
        // Update UI to show selected printer
        document.querySelectorAll('.qz-printer-item').forEach(item => {
            item.classList.remove('selected');
            if (item.querySelector('h6').textContent === printerName) {
                item.classList.add('selected');
            }
        });
    }

    async function testPrinter(printerName) {
        try {
            toastr.info('Printing test page...', 'Please wait');
            await qzManager.testPrinter(printerName);
            toastr.success('Test print sent to ' + printerName, 'Print Successful');
        } catch (error) {
            toastr.error(error.message, 'Print Failed');
        }
    }

    async function testPrint() {
        try {
            const printer = await qzManager.getSelectedPrinter();
            await testPrinter(printer);
        } catch (error) {
            toastr.error(error.message, 'Print Failed');
        }
    }

    function updateStats(type, value) {
        const element = document.getElementById(`stat-${type}`);
        if (element) {
            element.textContent = value;
        }
    }

    async function loadRecentJobs() {
        try {
            const response = await fetch('/api/qz-tray/jobs?limit=10');
            const data = await response.json();

            if (data.success) {
                updateRecentJobsTable(data.data);
                updateStats('jobs', data.data.length);
            }
        } catch (error) {
            console.error('Failed to load recent jobs:', error);
        }
    }

    function updateRecentJobsTable(jobs) {
        const tbody = document.getElementById('recent-jobs-table');

        if (jobs.length === 0) {
            tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No recent jobs</p>
                        </td>
                    </tr>
                `;
            return;
        }

        let html = '';

        jobs.forEach(job => {
            const time = new Date(job.created_at).toLocaleTimeString();
            const date = new Date(job.created_at).toLocaleDateString();
            const statusBadge = getStatusBadge(job.status);

            html += `
                    <tr>
                        <td><small class="text-muted">${job.id.substring(0, 8)}...</small></td>
                        <td><span class="badge bg-secondary">${job.type}</span></td>
                        <td><small>${job.printer}</small></td>
                        <td>${statusBadge}</td>
                        <td><small>${date} ${time}</small></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick="viewJob('${job.id}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
        });

        tbody.innerHTML = html;
    }

    function getStatusBadge(status) {
        const badges = {
            'queued': '<span class="badge bg-warning">Queued</span>',
            'processing': '<span class="badge bg-info">Processing</span>',
            'completed': '<span class="badge bg-success">Completed</span>',
            'failed': '<span class="badge bg-danger">Failed</span>',
            'cancelled': '<span class="badge bg-secondary">Cancelled</span>'
        };
        return badges[status] || '<span class="badge bg-dark">Unknown</span>';
    }

    async function runDiagnostics() {
        try {
            toastr.info('Running diagnostics...', 'Please wait');
            const response = await fetch('/api/qz-tray/diagnostics/run', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                const health = data.data.health;

                // Update health display
                document.getElementById('health-score').textContent = `${health.score}%`;
                document.getElementById('health-status').textContent = `Status: ${health.status}`;

                const healthFill = document.getElementById('health-fill');
                healthFill.style.width = `${health.score}%`;

                // Update health color
                healthFill.className = 'qz-health-fill';
                if (health.score >= 80) {
                    healthFill.classList.add('qz-health-excellent');
                } else if (health.score >= 60) {
                    healthFill.classList.add('qz-health-good');
                } else if (health.score >= 40) {
                    healthFill.classList.add('qz-health-fair');
                } else {
                    healthFill.classList.add('qz-health-poor');
                }

                toastr.success(`Health score: ${health.score}%`, 'Diagnostics Complete');
            }
        } catch (error) {
            toastr.error('Failed to run diagnostics', 'Error');
        }
    }

    // Tab specific load functions
    async function loadPrinterManagement() {
        const container = document.getElementById('printers-tab');
        container.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <div class="qz-card">
                            <div class="qz-card-header">
                                <i class="fas fa-print me-2"></i> Printer Management
                            </div>
                            <div class="qz-card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">Available Printers</label>
                                            <div id="management-printer-list" class="mb-3">
                                                <div class="text-center py-3">
                                                    <div class="spinner-border" role="status"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="qz-card">
                                            <div class="qz-card-body">
                                                <h6>Printer Actions</h6>
                                                <div class="d-grid gap-2">
                                                    <button class="qz-btn qz-btn-primary" onclick="discoverPrinters()">
                                                        <i class="fas fa-search"></i> Discover
                                                    </button>
                                                    <button class="qz-btn qz-btn-success" onclick="testPrint()">
                                                        <i class="fas fa-print"></i> Test Print
                                                    </button>
                                                    <button class="qz-btn qz-btn-warning" onclick="clearPrinterQueue()">
                                                        <i class="fas fa-broom"></i> Clear Queue
                                                    </button>
                                                    <button class="qz-btn qz-btn-info" onclick="refreshPrinterCache()">
                                                        <i class="fas fa-sync"></i> Refresh Cache
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

        // Load printer list for management
        await loadManagementPrinters();
    }

    async function loadManagementPrinters() {
        try {
            const response = await fetch('/api/qz-tray/printers');
            const data = await response.json();

            const container = document.getElementById('management-printer-list');

            if (data.success && data.data.length > 0) {
                let html = '<div class="list-group">';

                data.data.forEach(printer => {
                    html += `
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${printer.name}</h6>
                                    <small class="text-muted">${printer.type}</small>
                                </div>
                                <p class="mb-1">Status: ${printer.status}</p>
                                <small>${printer.default ? 'Default printer' : ''}</small>
                            </div>
                        `;
                });

                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-print fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No printers found</p>
                        </div>
                    `;
            }
        } catch (error) {
            console.error('Failed to load printers:', error);
        }
    }

    async function clearPrinterQueue() {
        try {
            const printer = await qzManager.getSelectedPrinter();

            const response = await fetch(`/api/qz-tray/printers/${encodeURIComponent(printer)}/queue`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                toastr.success('Printer queue cleared', 'Success');
            } else {
                toastr.error(data.error, 'Error');
            }
        } catch (error) {
            toastr.error(error.message, 'Error');
        }
    }

    async function refreshPrinterCache() {
        try {
            const response = await fetch('/api/qz-tray/printers/refresh', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                toastr.success('Printer cache refreshed', 'Success');
                await loadManagementPrinters();
            }
        } catch (error) {
            toastr.error(error.message, 'Error');
        }
    }

    function showPrintModal() {
        const modal = new bootstrap.Modal(document.getElementById('printModal'));
        modal.show();
    }

    function showPrinterInfo(printerName) {
        const modal = new bootstrap.Modal(document.getElementById('printerModal'));

        // Load printer info
        fetch(`/api/qz-tray/printers/${encodeURIComponent(printerName)}/capabilities`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modalBody = document.querySelector('#printerModal .qz-modal-body');
                    modalBody.innerHTML = `
                            <h5>${printerName}</h5>
                            <hr>
                            <pre>${JSON.stringify(data.data.capabilities, null, 2)}</pre>
                        `;
                    modal.show();
                }
            })
            .catch(error => {
                toastr.error('Failed to load printer info', 'Error');
            });
    }

    async function viewJob(jobId) {
        try {
            const response = await fetch(`/api/qz-tray/jobs/${jobId}`);
            const data = await response.json();

            if (data.success) {
                // Show job details in modal
                const modal = new bootstrap.Modal(document.getElementById('printerModal'));
                const modalBody = document.querySelector('#printerModal .qz-modal-body');

                modalBody.innerHTML = `
                        <h5>Job Details</h5>
                        <hr>
                        <pre>${JSON.stringify(data.data, null, 2)}</pre>
                    `;

                modal.show();
            }
        } catch (error) {
            toastr.error('Failed to load job details', 'Error');
        }
    }
</script>
</body>
</html>
