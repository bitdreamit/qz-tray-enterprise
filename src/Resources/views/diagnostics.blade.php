@extends('qz-tray::dashboard')

@section('content')
    <div class="qz-diagnostics">
        <div class="row mb-4">
            <div class="col-12">
                <div class="qz-card">
                    <div class="qz-card-header">
                        <i class="fas fa-stethoscope me-2"></i> System Diagnostics
                    </div>
                    <div class="qz-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Quick Diagnostics</h5>
                                <p class="text-muted">Run a quick check of all QZ Tray components.</p>
                                <button class="qz-btn qz-btn-primary" onclick="runDiagnostics()">
                                    <i class="fas fa-play me-2"></i> Run Diagnostics
                                </button>
                                <button class="qz-btn qz-btn-outline ms-2" onclick="exportDiagnostics()">
                                    <i class="fas fa-download me-2"></i> Export Report
                                </button>
                            </div>
                            <div class="col-md-6">
                                <h5>System Health</h5>
                                <div class="qz-health-meter mb-3">
                                    <div id="overall-health" class="qz-health-fill qz-health-excellent" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div id="health-score-display" class="h3 mb-0">0%</div>
                                    <div id="health-status-display" class="text-muted">Unknown</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="qz-card">
                    <div class="qz-card-header">
                        <i class="fas fa-tasks me-2"></i> Test Results
                    </div>
                    <div class="qz-card-body">
                        <div id="diagnostics-results">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Run diagnostics to see results</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="qz-card">
                    <div class="qz-card-header">
                        <i class="fas fa-chart-bar me-2"></i> Metrics
                    </div>
                    <div class="qz-card-body">
                        <div id="metrics-display">
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="qz-card mt-4">
                    <div class="qz-card-header">
                        <i class="fas fa-history me-2"></i> Diagnostic History
                    </div>
                    <div class="qz-card-body">
                        <div id="diagnostics-history">
                            <div class="text-center py-3">
                                <small class="text-muted">No history available</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function runDiagnostics() {
            try {
                showLoading('Running diagnostics...');

                const response = await fetch('/api/qz-tray/diagnostics/run', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    updateDiagnosticsResults(data.data);
                    updateHealthDisplay(data.data.health);
                    toastr.success('Diagnostics completed successfully', 'Success');
                } else {
                    toastr.error(data.error, 'Diagnostics Failed');
                }

            } catch (error) {
                console.error('Diagnostics error:', error);
                toastr.error('Failed to run diagnostics', 'Error');
            } finally {
                hideLoading();
            }
        }

        function updateDiagnosticsResults(data) {
            const container = document.getElementById('diagnostics-results');

            let html = '';

            for (const [testName, test] of Object.entries(data.tests)) {
                const passed = test.passed;
                const icon = passed ? '✅' : '❌';
                const color = passed ? 'text-success' : 'text-danger';

                html += `
            <div class="mb-3 p-3 border rounded">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 ${color}">
                        ${icon} ${test.name}
                    </h6>
                    <span class="badge ${passed ? 'bg-success' : 'bg-danger'}">
                        ${passed ? 'PASS' : 'FAIL'}
                    </span>
                </div>
                <div class="small text-muted">
                    ${Object.entries(test.details).map(([key, value]) =>
                    `<div><strong>${key}:</strong> ${value}</div>`
                ).join('')}
                </div>
            </div>
        `;
            }

            container.innerHTML = html;

            // Load metrics
            loadMetrics();
        }

        function updateHealthDisplay(health) {
            const healthFill = document.getElementById('overall-health');
            const healthScore = document.getElementById('health-score-display');
            const healthStatus = document.getElementById('health-status-display');

            healthScore.textContent = `${health.score}%`;
            healthStatus.textContent = `Status: ${health.status}`;

            // Update health bar
            healthFill.style.width = `${health.score}%`;
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
        }

        async function loadMetrics() {
            try {
                const response = await fetch('/api/qz-tray/diagnostics/metrics');
                const data = await response.json();

                if (data.success) {
                    updateMetricsDisplay(data.data);
                }
            } catch (error) {
                console.error('Failed to load metrics:', error);
            }
        }

        function updateMetricsDisplay(metrics) {
            const container = document.getElementById('metrics-display');

            let html = `
        <div class="mb-3">
            <strong>System</strong>
            <div class="small text-muted">
                <div>PHP: ${metrics.system.php_version}</div>
                <div>Laravel: ${metrics.system.laravel_version}</div>
                <div>Memory: ${metrics.system.memory_limit}</div>
            </div>
        </div>

        <div class="mb-3">
            <strong>QZ Tray</strong>
            <div class="small text-muted">
                <div>Enabled: ${metrics.qz_tray.enabled ? 'Yes' : 'No'}</div>
                <div>Version: ${metrics.qz_tray.version}</div>
                <div>Jobs Today: ${metrics.qz_tray.print_jobs_today}</div>
                <div>Total Jobs: ${metrics.qz_tray.print_jobs_total}</div>
            </div>
        </div>
    `;

            if (metrics.performance) {
                html += `
            <div class="mb-3">
                <strong>Performance</strong>
                <div class="small text-muted">
                    <div>Memory: ${Math.round(metrics.performance.memory_usage / 1024 / 1024)} MB</div>
                    <div>Peak: ${Math.round(metrics.performance.memory_peak / 1024 / 1024)} MB</div>
                </div>
            </div>
        `;
            }

            container.innerHTML = html;
        }

        async function loadDiagnosticHistory() {
            try {
                const response = await fetch('/api/qz-tray/diagnostics/history?limit=5');
                const data = await response.json();

                if (data.success && data.data.length > 0) {
                    updateDiagnosticHistory(data.data);
                }
            } catch (error) {
                console.error('Failed to load diagnostic history:', error);
            }
        }

        function updateDiagnosticHistory(history) {
            const container = document.getElementById('diagnostics-history');

            let html = '';

            history.forEach(item => {
                const time = new Date(item.timestamp).toLocaleString();
                const score = item.health?.score || 0;
                const status = item.health?.status || 'unknown';

                html += `
            <div class="border-bottom pb-2 mb-2">
                <div class="small">${time}</div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-${getHealthBadgeColor(score)}">
                        ${score}%
                    </span>
                    <small class="text-muted">${status}</small>
                </div>
            </div>
        `;
            });

            container.innerHTML = html;
        }

        function getHealthBadgeColor(score) {
            if (score >= 80) return 'success';
            if (score >= 60) return 'warning';
            return 'danger';
        }

        function exportDiagnostics() {
            // This would trigger a download of diagnostics report
            window.open('/api/qz-tray/diagnostics/export', '_blank');
        }

        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadMetrics();
            loadDiagnosticHistory();
        });

        // Utility functions
        function showLoading(message) {
            // Implement loading indicator
            console.log(`[LOADING] ${message}`);
        }

        function hideLoading() {
            // Hide loading indicator
        }
    </script>
@endsection
