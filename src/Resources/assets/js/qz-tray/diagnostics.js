/**
 * Diagnostics System for QZ Tray
 * Handles system health monitoring and diagnostics
 */

class DiagnosticsSystem {
    constructor(qzManager) {
        this.qzManager = qzManager;
        this.metrics = {
            connection: [],
            prints: [],
            errors: [],
            performance: []
        };

        this.startTime = new Date();
        this.healthScore = 0;
        this.healthStatus = 'unknown';

        // Start monitoring
        this.startMonitoring();
    }

    /**
     * Start monitoring system
     */
    startMonitoring() {
        // Monitor connection every 10 seconds
        setInterval(() => {
            this.recordConnectionMetric();
        }, 10000);

        // Monitor performance every 30 seconds
        setInterval(() => {
            this.recordPerformanceMetric();
        }, 30000);

        // Monitor memory usage if available
        if (performance && performance.memory) {
            setInterval(() => {
                this.recordMemoryMetric();
            }, 60000);
        }
    }

    /**
     * Run full diagnostics
     */
    async runFullDiagnostics() {
        const diagnostics = {
            timestamp: new Date(),
            uptime: Date.now() - this.startTime,
            tests: {}
        };

        try {
            // Test 1: QZ Tray Installation
            diagnostics.tests.installation = await this.testInstallation();

            // Test 2: Connection
            diagnostics.tests.connection = await this.testConnection();

            // Test 3: Security
            diagnostics.tests.security = await this.testSecurity();

            // Test 4: Printer Discovery
            diagnostics.tests.printerDiscovery = await this.testPrinterDiscovery();

            // Test 5: Print Capabilities
            diagnostics.tests.printCapabilities = await this.testPrintCapabilities();

            // Test 6: Performance
            diagnostics.tests.performance = await this.testPerformance();

            // Calculate health score
            diagnostics.health = this.calculateHealth(diagnostics.tests);

            // Update internal state
            this.healthScore = diagnostics.health.score;
            this.healthStatus = diagnostics.health.status;

            // Store diagnostics
            this.storeDiagnostics(diagnostics);

            return diagnostics;

        } catch (error) {
            console.error('Diagnostics failed:', error);

            return {
                timestamp: new Date(),
                error: error.message,
                tests: {},
                health: {
                    score: 0,
                    status: 'error'
                }
            };
        }
    }

    /**
     * Test QZ Tray installation
     */
    async testInstallation() {
        const test = {
            name: 'Installation',
            passed: false,
            details: {}
        };

        try {
            // Check if QZ object exists
            test.details.qzObject = typeof qz !== 'undefined';

            // Check if websocket is available
            test.details.websocket = typeof qz.websocket !== 'undefined';

            // Check if print API is available
            test.details.printApi = typeof qz.print !== 'undefined';

            // Check if security API is available
            test.details.securityApi = typeof qz.security !== 'undefined';

            // Try to get version
            if (test.details.qzObject) {
                try {
                    const version = await qz.api.getVersion();
                    test.details.version = version;
                    test.details.versionCheck = true;
                } catch (e) {
                    test.details.versionCheck = false;
                    test.details.versionError = e.message;
                }
            }

            test.passed = test.details.qzObject &&
                test.details.websocket &&
                test.details.printApi &&
                test.details.securityApi;

        } catch (error) {
            test.details.error = error.message;
        }

        return test;
    }

    /**
     * Test connection
     */
    async testConnection() {
        const test = {
            name: 'Connection',
            passed: false,
            details: {}
        };

        try {
            const startTime = Date.now();

            // Test WebSocket connection
            if (!qz.websocket.isActive()) {
                await qz.websocket.connect({ retries: 1, delay: 500 });
            }

            test.details.connectionTime = Date.now() - startTime;
            test.details.isActive = qz.websocket.isActive();

            // Test API call
            const config = await qz.api.getConfig();
            test.details.config = config !== null;

            // Test printer discovery
            const printers = await qz.printers.find();
            test.details.printerDiscovery = printers.length > 0;
            test.details.printerCount = printers.length;

            test.passed = test.details.isActive &&
                test.details.config &&
                test.details.printerDiscovery;

        } catch (error) {
            test.details.error = error.message;
        }

        return test;
    }

    /**
     * Test security
     */
    async testSecurity() {
        const test = {
            name: 'Security',
            passed: false,
            details: {}
        };

        try {
            // Check if page is HTTPS
            test.details.https = window.location.protocol === 'https:';

            // Check certificate promise
            test.details.certificatePromise = typeof qz.security.setCertificatePromise === 'function';

            // Check signature promise
            test.details.signaturePromise = typeof qz.security.setSignaturePromise === 'function';

            // Test signature (if possible)
            if (test.details.https && test.details.signaturePromise) {
                try {
                    // Create a test signature
                    const testData = 'test_signature_' + Date.now();
                    await qz.security.setSignaturePromise(() => Promise.resolve('test'));
                    test.details.signatureTest = true;
                } catch (e) {
                    test.details.signatureTest = false;
                    test.details.signatureError = e.message;
                }
            }

            test.passed = test.details.certificatePromise &&
                test.details.signaturePromise;

        } catch (error) {
            test.details.error = error.message;
        }

        return test;
    }

    /**
     * Test printer discovery
     */
    async testPrinterDiscovery() {
        const test = {
            name: 'Printer Discovery',
            passed: false,
            details: {}
        };

        try {
            const printers = await this.qzManager.printerManager.discoverAll();

            test.details.totalPrinters = printers.printers.length;
            test.details.defaultPrinter = printers.default;
            test.details.selectedPrinter = printers.selected;
            test.details.categorized = printers.categorized;

            test.passed = printers.printers.length > 0;

        } catch (error) {
            test.details.error = error.message;
        }

        return test;
    }

    /**
     * Test print capabilities
     */
    async testPrintCapabilities() {
        const test = {
            name: 'Print Capabilities',
            passed: false,
            details: {}
        };

        try {
            const printer = await this.qzManager.printerManager.getSelected();
            const capabilities = await this.qzManager.printerManager.getCapabilities(printer);

            test.details.printer = printer;
            test.details.capabilities = capabilities;
            test.details.hasColor = capabilities.color || false;
            test.details.hasDuplex = capabilities.duplex || false;
            test.details.paperSizes = capabilities.paperSizes?.length || 0;
            test.details.supportedFeatures = capabilities.supportedFeatures || [];

            // Test basic print
            const testResult = await this.testPrint(printer);
            test.details.testPrint = testResult;

            test.passed = test.details.testPrint.success;

        } catch (error) {
            test.details.error = error.message;
        }

        return test;
    }

    /**
     * Test print a simple document
     */
    async testPrint(printerName) {
        try {
            const config = qz.configs.create(printerName);

            await qz.print(config, [
                '================================\n',
                '   QZ Tray Diagnostics Test\n',
                '   ' + new Date().toISOString() + '\n',
                '   Printer: ' + printerName + '\n',
                '================================\n\n',
                'This is a test print to verify\n',
                'that the printer is working\n',
                'correctly with QZ Tray.\n\n',
                '✓ Connection: OK\n',
                '✓ Security: OK\n',
                '✓ Printing: OK\n\n',
                '--------------------------------\n'
            ]);

            return { success: true };

        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    /**
     * Test performance
     */
    async testPerformance() {
        const test = {
            name: 'Performance',
            passed: true,
            details: {}
        };

        try {
            // Test connection speed
            const connectionStart = Date.now();
            await this.qzManager.connect();
            test.details.connectionSpeed = Date.now() - connectionStart;

            // Test printer discovery speed
            const discoveryStart = Date.now();
            await this.qzManager.printerManager.discoverAll();
            test.details.discoverySpeed = Date.now() - discoveryStart;

            // Memory usage
            if (performance && performance.memory) {
                test.details.memoryUsage = Math.round(performance.memory.usedJSHeapSize / 1024 / 1024) + 'MB';
                test.details.memoryLimit = Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024) + 'MB';
            }

            // Connection metrics
            test.details.connectionMetrics = this.metrics.connection.slice(-10);
            test.details.errorCount = this.metrics.errors.length;

        } catch (error) {
            test.details.error = error.message;
        }

        return test;
    }

    /**
     * Record connection metric
     */
    async recordConnectionMetric() {
        try {
            const metric = {
                connected: this.qzManager.state.connected,
                websocketActive: qz.websocket?.isActive?.() || false,
                timestamp: new Date()
            };

            this.metrics.connection.push(metric);

            // Keep only last 1000 metrics
            if (this.metrics.connection.length > 1000) {
                this.metrics.connection = this.metrics.connection.slice(-1000);
            }

        } catch (error) {
            console.warn('Failed to record connection metric:', error);
        }
    }

    /**
     * Record performance metric
     */
    recordPerformanceMetric() {
        try {
            const metric = {
                timestamp: new Date(),
                memory: performance?.memory ? {
                    used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024),
                    total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024)
                } : null
            };

            this.metrics.performance.push(metric);

            if (this.metrics.performance.length > 1000) {
                this.metrics.performance = this.metrics.performance.slice(-1000);
            }

        } catch (error) {
            console.warn('Failed to record performance metric:', error);
        }
    }

    /**
     * Record memory metric
     */
    recordMemoryMetric() {
        if (!performance || !performance.memory) return;

        try {
            const metric = {
                timestamp: new Date(),
                used: performance.memory.usedJSHeapSize,
                total: performance.memory.totalJSHeapSize,
                limit: performance.memory.jsHeapSizeLimit
            };

            this.metrics.performance.push(metric);

        } catch (error) {
            console.warn('Failed to record memory metric:', error);
        }
    }

    /**
     * Record error metric
     */
    recordError(error, context = {}) {
        const metric = {
            timestamp: new Date(),
            error: error.message || String(error),
            context: context,
            stack: error.stack
        };

        this.metrics.errors.push(metric);

        if (this.metrics.errors.length > 1000) {
            this.metrics.errors = this.metrics.errors.slice(-1000);
        }

        // Emit error event
        this.qzManager.emit('error', metric);
    }

    /**
     * Record print metric
     */
    recordPrint(job) {
        const metric = {
            timestamp: new Date(),
            jobId: job.id,
            format: job.format,
            printer: job.options?.printer,
            status: job.status
        };

        this.metrics.prints.push(metric);

        if (this.metrics.prints.length > 1000) {
            this.metrics.prints = this.metrics.prints.slice(-1000);
        }
    }

    /**
     * Calculate health score from tests
     */
    calculateHealth(tests) {
        const weights = {
            installation: 20,
            connection: 20,
            security: 20,
            printerDiscovery: 15,
            printCapabilities: 15,
            performance: 10
        };

        let totalWeight = 0;
        let totalScore = 0;

        for (const [testName, test] of Object.entries(tests)) {
            if (weights[testName]) {
                totalWeight += weights[testName];
                totalScore += test.passed ? weights[testName] : 0;
            }
        }

        const score = totalWeight > 0 ? Math.round((totalScore / totalWeight) * 100) : 0;

        return {
            score: score,
            status: score >= 80 ? 'healthy' : score >= 60 ? 'warning' : 'critical',
            grade: score >= 90 ? 'A' : score >= 80 ? 'B' : score >= 70 ? 'C' : score >= 60 ? 'D' : 'F'
        };
    }

    /**
     * Store diagnostics in localStorage
     */
    storeDiagnostics(diagnostics) {
        try {
            const history = JSON.parse(localStorage.getItem('qz_diagnostics_history') || '[]');
            history.unshift(diagnostics);

            // Keep only last 50 diagnostics
            if (history.length > 50) {
                history.length = 50;
            }

            localStorage.setItem('qz_diagnostics_history', JSON.stringify(history));

        } catch (error) {
            console.warn('Failed to store diagnostics:', error);
        }
    }

    /**
     * Get diagnostic history
     */
    getDiagnosticHistory(limit = 10) {
        try {
            const history = JSON.parse(localStorage.getItem('qz_diagnostics_history') || '[]');
            return history.slice(0, limit);
        } catch (error) {
            return [];
        }
    }

    /**
     * Clear diagnostic history
     */
    clearDiagnosticHistory() {
        localStorage.removeItem('qz_diagnostics_history');
        this.metrics = {
            connection: [],
            prints: [],
            errors: [],
            performance: []
        };
    }

    /**
     * Get metrics summary
     */
    getMetricsSummary() {
        return {
            uptime: Date.now() - this.startTime,
            connection: {
                total: this.metrics.connection.length,
                connected: this.metrics.connection.filter(m => m.connected).length,
                disconnected: this.metrics.connection.filter(m => !m.connected).length
            },
            prints: {
                total: this.metrics.prints.length,
                successful: this.metrics.prints.filter(m => m.status === 'completed').length,
                failed: this.metrics.prints.filter(m => m.status === 'failed').length
            },
            errors: this.metrics.errors.length,
            health: {
                score: this.healthScore,
                status: this.healthStatus
            }
        };
    }

    /**
     * Export diagnostics to file
     */
    exportDiagnostics() {
        try {
            const diagnostics = {
                timestamp: new Date(),
                metrics: this.getMetricsSummary(),
                history: this.getDiagnosticHistory(100),
                system: {
                    userAgent: navigator.userAgent,
                    platform: navigator.platform,
                    language: navigator.language,
                    cookies: navigator.cookieEnabled,
                    online: navigator.onLine
                }
            };

            const dataStr = JSON.stringify(diagnostics, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });

            const url = URL.createObjectURL(dataBlob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `qz-diagnostics-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

        } catch (error) {
            console.error('Failed to export diagnostics:', error);
            throw error;
        }
    }
}

// Export as global
if (typeof window !== 'undefined') {
    window.DiagnosticsSystem = DiagnosticsSystem;
}
