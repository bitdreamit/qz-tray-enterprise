{{-- QZ Tray Assets Partial --}}

@if(config('qz-tray.enabled', true))
    <!-- QZ Tray Core -->
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.5/dist/js/qz-tray.js"></script>

    <!-- CryptoJS for signatures -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>

    <!-- QZ Tray Configuration -->
    <script>
        // Global QZ Tray configuration
        window.qzTrayConfig = {
            version: '2.2.5',
            autoInitialize: {{ config('qz-tray.connection.auto_connect', true) ? 'true' : 'false' }},
            retryAttempts: {{ config('qz-tray.connection.retry_attempts', 3) }},
            retryDelay: {{ config('qz-tray.connection.delay', 1000) }},
            discoveryInterval: {{ config('qz-tray.printers.discovery_interval', 30000) }},
            fallbackStrategy: '{{ config('qz-tray.printers.fallback_strategy', "browser-print") }}',
            apiBaseUrl: '{{ route("qz-tray.api.status") }}',
            certificateUrl: '{{ route("qz-tray.certificate") }}',
            signUrl: '{{ route("qz-tray.sign") }}',
            csrfToken: '{{ csrf_token() }}',
            userId: {{ auth()->id() ?? 'null' }},
            userName: '{{ auth()->user()->name ?? "Guest" }}'
        };

        // Setup security promises
        if (typeof qz !== 'undefined') {
            qz.security.setCertificatePromise(function(resolve, reject) {
                fetch(qzTrayConfig.certificateUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': qzTrayConfig.csrfToken
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Certificate fetch failed: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(certificate => resolve(certificate))
                    .catch(error => {
                        console.warn('Certificate fetch failed, using unsigned mode:', error);
                        resolve(null); // Allow unsigned mode for development
                    });
            });

            qz.security.setSignaturePromise(function(toSign) {
                return fetch(qzTrayConfig.signUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/octet-stream',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': qzTrayConfig.csrfToken
                    },
                    body: toSign
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Signature failed: ${response.status}`);
                        }
                        return response.text();
                    })
                    .catch(error => {
                        console.error('Signature failed:', error);
                        throw error;
                    });
            });
        }
    </script>

    <!-- Toastr for notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Custom QZ Tray CSS -->
    <style>
        .qz-status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .qz-status-connected {
            background-color: #28a745;
            box-shadow: 0 0 10px #28a745;
        }

        .qz-status-disconnected {
            background-color: #dc3545;
            box-shadow: 0 0 10px #dc3545;
        }

        .qz-status-connecting {
            background-color: #ffc107;
            box-shadow: 0 0 10px #ffc107;
            animation: qz-pulse 1.5s infinite;
        }

        .qz-print-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .qz-print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .qz-print-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @keyframes qz-pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .qz-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 9999;
        }

        .qz-modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            margin: 100px auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
    </style>

    <!-- Print Fallback System -->
    <div id="qz-fallback-overlay" class="qz-modal-overlay">
        <div class="qz-modal-content">
            <h4>Print Fallback</h4>
            <p>QZ Tray printing failed. Please choose an alternative:</p>
            <div class="d-grid gap-2">
                <button class="btn btn-primary" onclick="qzFallback.browserPrint()">
                    <i class="fas fa-print"></i> Browser Print
                </button>
                <button class="btn btn-secondary" onclick="qzFallback.downloadPdf()">
                    <i class="fas fa-download"></i> Download PDF
                </button>
                <button class="btn btn-light" onclick="qzFallback.hide()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <script>
        // Fallback system
        window.qzFallback = {
            currentJob: null,

            show: function(job) {
                this.currentJob = job;
                document.getElementById('qz-fallback-overlay').style.display = 'block';
            },

            hide: function() {
                document.getElementById('qz-fallback-overlay').style.display = 'none';
                this.currentJob = null;
            },

            browserPrint: function() {
                if (!this.currentJob) return;

                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Print Document</title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 20px; }
                            @media print {
                                .no-print { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        ${this.currentJob.content || 'Printable content'}
                        <div class="no-print" style="position: fixed; top: 10px; right: 10px;">
                            <button onclick="window.print()">Print</button>
                            <button onclick="window.close()">Close</button>
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                this.hide();
            },

            downloadPdf: function() {
                if (!this.currentJob) return;

                // Create PDF blob (simplified - in real implementation use a PDF library)
                const blob = new Blob([this.currentJob.content || ''], { type: 'text/html' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'document_' + Date.now() + '.html';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                this.hide();
            }
        };
    </script>
@endif
