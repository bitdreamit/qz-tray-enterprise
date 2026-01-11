/**
 * QZ Tray Manager v2.2.5
 * Complete QZ Tray integration for Laravel
 */

class QzTrayManager {
    constructor(config = {}) {
        this.config = {
            version: '2.2.5',
            autoConnect: true,
            retryAttempts: 3,
            retryDelay: 1000,
            discoveryInterval: 30000,
            fallbackStrategy: 'browser-print',
            ...config
        };

        this.state = {
            connected: false,
            connecting: false,
            printers: [],
            defaultPrinter: null,
            selectedPrinter: null,
            capabilities: new Map(),
            jobQueue: [],
            history: [],
            errors: []
        };

        this.events = new EventTarget();
        this.initialized = false;

        // Initialize if QZ is available
        if (typeof qz !== 'undefined') {
            this.setupSecurity();
            this.loadPreferences();
            this.initialize();
        } else {
            console.warn('QZ Tray not loaded. Please install QZ Tray from https://qz.io');
        }
    }

    /**
     * Initialize QZ Tray
     */
    async initialize() {
        if (this.initialized) return;

        try {
            await this.setupSecurity();
            await this.connect();

            // Discover printers
            await this.discoverPrinters();

            // Load user preferences
            this.loadPreferences();

            // Setup auto-discovery if enabled
            if (this.config.discoveryInterval > 0) {
                this.startAutoDiscovery();
            }

            this.initialized = true;
            this.emit('initialized', this.state);

        } catch (error) {
            console.error('QZ Tray initialization failed:', error);
            this.emit('error', { type: 'initialization', error });
        }
    }

    /**
     * Setup security promises
     */
    async setupSecurity() {
        if (typeof qz === 'undefined') return;

        // Set certificate promise
        qz.security.setCertificatePromise(async (resolve, reject) => {
            try {
                const response = await fetch('/api/qz-tray/certificate', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });

                if (!response.ok) {
                    throw new Error(`Certificate fetch failed: ${response.status}`);
                }

                const certificate = await response.text();
                resolve(certificate);
            } catch (error) {
                console.warn('Certificate fetch failed, using unsigned mode:', error);
                resolve(null); // Allow unsigned mode for development
            }
        });

        // Set signature promise
        qz.security.setSignaturePromise(async (toSign) => {
            try {
                const response = await fetch('/api/qz-tray/sign', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/octet-stream',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: toSign
                });

                if (!response.ok) {
                    throw new Error(`Signature failed: ${response.status}`);
                }

                return await response.text();
            } catch (error) {
                console.error('Signature failed:', error);
                throw error;
            }
        });
    }

    /**
     * Connect to QZ Tray
     */
    async connect(options = {}) {
        if (this.state.connecting) return;

        this.state.connecting = true;
        this.emit('connecting');

        try {
            const connectOptions = {
                retries: options.retries || this.config.retryAttempts,
                delay: options.delay || this.config.retryDelay,
                ...options
            };

            if (!qz.websocket.isActive()) {
                await qz.websocket.connect(connectOptions);
            }

            this.state.connected = true;
            this.state.connecting = false;

            // Get version info
            const version = await qz.api.getVersion();
            console.log(`Connected to QZ Tray v${version}`);

            this.emit('connected', { version });

            return true;

        } catch (error) {
            this.state.connected = false;
            this.state.connecting = false;

            console.error('QZ Tray connection failed:', error);
            this.emit('error', { type: 'connection', error });

            throw error;
        }
    }

    /**
     * Disconnect from QZ Tray
     */
    async disconnect() {
        if (!this.state.connected) return;

        try {
            if (qz.websocket.isActive()) {
                await qz.websocket.disconnect();
            }

            this.state.connected = false;
            this.emit('disconnected');

        } catch (error) {
            console.error('Disconnection failed:', error);
            this.emit('error', { type: 'disconnection', error });
        }
    }

    /**
     * Discover all printers
     */
    async discoverPrinters(options = {}) {
        try {
            this.emit('discoveringPrinters');

            await this.ensureConnected();

            // Get all printers
            const allPrinters = await qz.printers.find();

            // Get default printer
            const defaultPrinter = await qz.printers.getDefault();

            // Update state
            this.state.printers = allPrinters;
            this.state.defaultPrinter = defaultPrinter;

            // If no printer is selected, select default
            if (!this.state.selectedPrinter && defaultPrinter) {
                this.state.selectedPrinter = defaultPrinter;
            }

            this.emit('printersDiscovered', {
                printers: allPrinters,
                default: defaultPrinter,
                selected: this.state.selectedPrinter
            });

            return {
                printers: allPrinters,
                default: defaultPrinter,
                selected: this.state.selectedPrinter
            };

        } catch (error) {
            console.error('Printer discovery failed:', error);
            this.emit('error', { type: 'discovery', error });
            throw error;
        }
    }

    /**
     * Get printer capabilities
     */
    async getPrinterCapabilities(printerName) {
        if (this.state.capabilities.has(printerName)) {
            return this.state.capabilities.get(printerName);
        }

        try {
            await this.ensureConnected();

            const capabilities = await qz.printers.getPrinterInfo(printerName);

            // Enhance with additional info
            const enhanced = {
                ...capabilities,
                name: printerName,
                type: this.detectPrinterType(printerName),
                supports: this.detectSupportedFeatures(capabilities),
                isDefault: printerName === this.state.defaultPrinter,
                isSelected: printerName === this.state.selectedPrinter
            };

            // Cache capabilities
            this.state.capabilities.set(printerName, enhanced);

            return enhanced;

        } catch (error) {
            console.warn(`Failed to get capabilities for ${printerName}:`, error);
            return null;
        }
    }

    /**
     * Select printer
     */
    selectPrinter(printerName, module = null) {
        if (!this.state.printers.includes(printerName)) {
            throw new Error(`Printer ${printerName} not found`);
        }

        const previous = this.state.selectedPrinter;
        this.state.selectedPrinter = printerName;

        // Save to preferences
        this.savePreference('selectedPrinter', printerName);

        if (module) {
            this.savePreference(`selectedPrinter_${module}`, printerName);
        }

        this.emit('printerSelected', {
            previous,
            current: printerName,
            module
        });

        return printerName;
    }

    /**
     * Get selected printer with fallback logic
     */
    async getSelectedPrinter(module = null) {
        // 1. Check module-specific preference
        if (module) {
            const modulePrinter = this.getPreference(`selectedPrinter_${module}`);
            if (modulePrinter && this.state.printers.includes(modulePrinter)) {
                return modulePrinter;
            }
        }

        // 2. Check general preference
        const preferredPrinter = this.getPreference('selectedPrinter');
        if (preferredPrinter && this.state.printers.includes(preferredPrinter)) {
            return preferredPrinter;
        }

        // 3. Use default printer
        if (this.state.defaultPrinter) {
            return this.state.defaultPrinter;
        }

        // 4. Use first available printer
        if (this.state.printers.length > 0) {
            return this.state.printers[0];
        }

        // 5. Discover printers
        const discovery = await this.discoverPrinters();
        if (discovery.default) {
            return discovery.default;
        }

        throw new Error('No printers available');
    }

    /**
     * Print content
     */
    async print(content, options = {}) {
        const jobId = `job_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

        const job = {
            id: jobId,
            content,
            options,
            status: 'queued',
            timestamp: new Date(),
            attempts: 0
        };

        this.state.jobQueue.push(job);
        this.emit('jobQueued', job);

        // Process queue
        await this.processQueue();

        return jobId;
    }

    /**
     * Process print queue
     */
    async processQueue() {
        if (this.processingQueue || this.state.jobQueue.length === 0) return;

        this.processingQueue = true;

        while (this.state.jobQueue.length > 0) {
            const job = this.state.jobQueue[0];

            try {
                job.status = 'processing';
                job.attempts++;
                this.emit('jobProcessing', job);

                // Execute print
                const result = await this.executePrint(job);

                job.status = 'completed';
                job.result = result;
                job.completedAt = new Date();

                // Move to history
                this.state.jobQueue.shift();
                this.state.history.unshift(job);

                // Keep history limited
                if (this.state.history.length > 100) {
                    this.state.history = this.state.history.slice(0, 100);
                }

                this.emit('jobCompleted', job);

            } catch (error) {
                job.status = 'failed';
                job.error = error;

                if (job.attempts < 3) {
                    // Retry after delay
                    setTimeout(() => {
                        this.state.jobQueue.push(this.state.jobQueue.shift());
                        this.processQueue();
                    }, 2000);
                } else {
                    // Move to history as failed
                    this.state.jobQueue.shift();
                    this.state.history.unshift(job);
                    this.emit('jobFailed', job);
                }
            }
        }

        this.processingQueue = false;
    }

    /**
     * Execute print job
     */
    async executePrint(job) {
        await this.ensureConnected();

        const printer = job.options.printer || await this.getSelectedPrinter();
        const config = qz.configs.create(printer, job.options);

        let printData;

        if (typeof job.content === 'string') {
            // Determine content type
            if (job.content.trim().startsWith('^') || job.content.includes('^XA')) {
                // ZPL
                printData = [{
                    type: 'raw',
                    data: job.content,
                    flavor: 'zpl'
                }];
            } else if (job.content.includes('<html') || job.content.includes('<div')) {
                // HTML
                printData = [{
                    type: 'html',
                    data: job.content
                }];
            } else if (job.content.startsWith('%PDF')) {
                // PDF
                printData = [{
                    type: 'pdf',
                    data: job.content
                }];
            } else {
                // Raw text
                printData = [job.content];
            }
        } else if (job.content.type) {
            // Already formatted
            printData = [job.content];
        } else {
            throw new Error('Invalid print content');
        }

        return await qz.print(config, printData);
    }

    /**
     * Test printer
     */
    async testPrinter(printerName = null) {
        const printer = printerName || await this.getSelectedPrinter();

        const testContent = `================================
   QZ Tray Test Print
   Date: ${new Date().toLocaleString()}
   Printer: ${printer}
   User: ${window.userName || 'Anonymous'}
================================

This is a test print to verify
that the printer is working
correctly with QZ Tray.

✓ Connection: OK
✓ Security: OK
✓ Printing: OK

--------------------------------
`;

        return await this.print(testContent, { printer });
    }

    /**
     * Clear printer queue
     */
    async clearPrinterQueue(printerName = null) {
        const printer = printerName || await this.getSelectedPrinter();

        try {
            await this.ensureConnected();
            await qz.printers.clearQueue(printer);

            this.emit('queueCleared', { printer });
            return true;

        } catch (error) {
            console.error(`Failed to clear queue for ${printer}:`, error);
            throw error;
        }
    }

    /**
     * Start auto-discovery
     */
    startAutoDiscovery(interval = null) {
        if (this.discoveryInterval) {
            clearInterval(this.discoveryInterval);
        }

        const discoveryInterval = interval || this.config.discoveryInterval;

        this.discoveryInterval = setInterval(() => {
            this.discoverPrinters().catch(console.error);
        }, discoveryInterval);

        console.log(`Auto-discovery started (${discoveryInterval}ms interval)`);
    }

    /**
     * Stop auto-discovery
     */
    stopAutoDiscovery() {
        if (this.discoveryInterval) {
            clearInterval(this.discoveryInterval);
            this.discoveryInterval = null;
            console.log('Auto-discovery stopped');
        }
    }

    /**
     * Detect printer type from name
     */
    detectPrinterType(printerName) {
        const lowerName = printerName.toLowerCase();

        if (lowerName.includes('zebra') || lowerName.includes('zpl')) {
            return 'label';
        } else if (lowerName.includes('epson') || lowerName.includes('tm-')) {
            return 'receipt';
        } else if (lowerName.includes('pdf') || lowerName.includes('virtual')) {
            return 'virtual';
        } else if (lowerName.includes('network') || lowerName.includes('tcp')) {
            return 'network';
        } else if (lowerName.includes('usb')) {
            return 'usb';
        } else {
            return 'standard';
        }
    }

    /**
     * Detect supported features from capabilities
     */
    detectSupportedFeatures(capabilities) {
        const supports = [];

        if (capabilities.color) supports.push('color');
        if (capabilities.duplex) supports.push('duplex');
        if (capabilities.copies) supports.push('copies');

        // Check paper sizes
        if (capabilities.paperSizes) {
            if (capabilities.paperSizes.includes('A4') || capabilities.paperSizes.includes('Letter')) {
                supports.push('document');
            }
            if (capabilities.paperSizes.includes('receipt')) {
                supports.push('receipt');
            }
        }

        return supports;
    }

    /**
     * Ensure connected to QZ Tray
     */
    async ensureConnected() {
        if (!this.state.connected) {
            await this.connect();
        }
        return true;
    }

    /**
     * Save preference
     */
    savePreference(key, value) {
        const preferences = JSON.parse(localStorage.getItem('qz_preferences') || '{}');
        preferences[key] = value;
        localStorage.setItem('qz_preferences', JSON.stringify(preferences));
    }

    /**
     * Get preference
     */
    getPreference(key) {
        const preferences = JSON.parse(localStorage.getItem('qz_preferences') || '{}');
        return preferences[key];
    }

    /**
     * Load preferences
     */
    loadPreferences() {
        const preferences = JSON.parse(localStorage.getItem('qz_preferences') || '{}');

        // Load selected printer
        if (preferences.selectedPrinter) {
            this.state.selectedPrinter = preferences.selectedPrinter;
        }

        return preferences;
    }

    /**
     * Emit event
     */
    emit(eventName, data = {}) {
        const event = new CustomEvent(eventName, { detail: data });
        this.events.dispatchEvent(event);
    }

    /**
     * Add event listener
     */
    on(eventName, callback) {
        this.events.addEventListener(eventName, (event) => callback(event.detail));
    }

    /**
     * Remove event listener
     */
    off(eventName, callback) {
        this.events.removeEventListener(eventName, callback);
    }

    /**
     * Get system info
     */
    async getSystemInfo() {
        try {
            const [version, config, properties] = await Promise.all([
                qz.api.getVersion(),
                qz.api.getConfig(),
                qz.api.getProperties()
            ]);

            return {
                version,
                config,
                properties,
                manager: {
                    connected: this.state.connected,
                    printers: this.state.printers.length,
                    queue: this.state.jobQueue.length,
                    history: this.state.history.length
                }
            };
        } catch (error) {
            console.error('Failed to get system info:', error);
            return null;
        }
    }
}

// Export as global
window.QzTrayManager = QzTrayManager;

// Auto-initialize if configured
document.addEventListener('DOMContentLoaded', () => {
    if (window.qzTrayConfig && window.qzTrayConfig.autoInitialize !== false) {
        window.qzManager = new QzTrayManager(window.qzTrayConfig);
        window.qzManager.initialize().catch(console.error);
    }
});
