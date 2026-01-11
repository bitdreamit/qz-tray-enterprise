/**
 * Printer Manager for QZ Tray
 * Handles printer discovery, selection, and management
 */

class PrinterManager {
    constructor(qzManager) {
        this.qzManager = qzManager;
        this.printers = [];
        this.defaultPrinter = null;
        this.selectedPrinter = null;
        this.capabilitiesCache = new Map();
        this.userPreferences = this.loadPreferences();
    }

    /**
     * Discover all printers
     */
    async discoverAll(options = {}) {
        try {
            await this.qzManager.ensureConnected();

            // Get all printers
            const allPrinters = await qz.printers.find();

            // Get default printer
            const defaultPrinter = await qz.printers.getDefault();

            // Update state
            this.printers = allPrinters;
            this.defaultPrinter = defaultPrinter;

            // If no printer selected, use default or preference
            if (!this.selectedPrinter) {
                const preference = this.getPreference('selectedPrinter');
                this.selectedPrinter = preference || defaultPrinter || allPrinters[0];
            }

            // Categorize printers
            const categorized = this.categorizePrinters(allPrinters);

            // Emit event
            this.qzManager.emit('printersDiscovered', {
                printers: allPrinters,
                default: defaultPrinter,
                selected: this.selectedPrinter,
                categorized: categorized
            });

            return {
                printers: allPrinters,
                default: defaultPrinter,
                selected: this.selectedPrinter,
                categorized: categorized
            };

        } catch (error) {
            console.error('Printer discovery failed:', error);
            throw error;
        }
    }

    /**
     * Categorize printers by type
     */
    categorizePrinters(printers) {
        const categories = {
            network: [],
            usb: [],
            virtual: [],
            label: [],
            receipt: [],
            standard: []
        };

        printers.forEach(printer => {
            const type = this.detectPrinterType(printer);

            switch (type) {
                case 'label':
                    categories.label.push(printer);
                    break;
                case 'receipt':
                    categories.receipt.push(printer);
                    break;
                case 'virtual':
                    categories.virtual.push(printer);
                    break;
                case 'network':
                    categories.network.push(printer);
                    break;
                case 'usb':
                    categories.usb.push(printer);
                    break;
                default:
                    categories.standard.push(printer);
            }
        });

        return categories;
    }

    /**
     * Detect printer type from name
     */
    detectPrinterType(printerName) {
        const lowerName = printerName.toLowerCase();

        // Label printers (Zebra, ZPL)
        if (lowerName.includes('zebra') ||
            lowerName.includes('zpl') ||
            lowerName.includes('label') ||
            lowerName.includes('lp') ||
            lowerName.includes('sato') ||
            lowerName.includes('intermec')) {
            return 'label';
        }

        // Receipt printers (Epson, POS)
        if (lowerName.includes('epson') ||
            lowerName.includes('tm-') ||
            lowerName.includes('receipt') ||
            lowerName.includes('pos') ||
            lowerName.includes('citizen') ||
            lowerName.includes('bixolon')) {
            return 'receipt';
        }

        // Virtual printers
        if (lowerName.includes('pdf') ||
            lowerName.includes('xps') ||
            lowerName.includes('virtual') ||
            lowerName.includes('microsoft print to pdf') ||
            lowerName.includes('one note')) {
            return 'virtual';
        }

        // Network printers
        if (lowerName.includes('network') ||
            lowerName.includes('tcp') ||
            lowerName.includes('//') ||
            lowerName.includes('\\\\') ||
            lowerName.includes('ipp') ||
            lowerName.includes('lpd')) {
            return 'network';
        }

        // USB printers
        if (lowerName.includes('usb') ||
            lowerName.includes('dot4') ||
            lowerName.includes('001') || // Common USB pattern
            lowerName.includes('002')) {
            return 'usb';
        }

        return 'standard';
    }

    /**
     * Get printer capabilities
     */
    async getCapabilities(printerName) {
        if (this.capabilitiesCache.has(printerName)) {
            return this.capabilitiesCache.get(printerName);
        }

        try {
            await this.qzManager.ensureConnected();

            const capabilities = await qz.printers.getPrinterInfo(printerName);

            // Enhance capabilities
            const enhanced = {
                ...capabilities,
                name: printerName,
                type: this.detectPrinterType(printerName),
                supportedFeatures: this.getSupportedFeatures(capabilities),
                isDefault: printerName === this.defaultPrinter,
                isSelected: printerName === this.selectedPrinter,
                lastChecked: new Date()
            };

            // Cache for 5 minutes
            this.capabilitiesCache.set(printerName, enhanced);
            setTimeout(() => this.capabilitiesCache.delete(printerName), 5 * 60 * 1000);

            return enhanced;

        } catch (error) {
            console.warn(`Failed to get capabilities for ${printerName}:`, error);

            // Return basic capabilities based on printer type
            return {
                name: printerName,
                type: this.detectPrinterType(printerName),
                supportedFeatures: this.getBasicFeatures(printerName),
                isDefault: printerName === this.defaultPrinter,
                isSelected: printerName === this.selectedPrinter,
                lastChecked: new Date(),
                error: error.message
            };
        }
    }

    /**
     * Get supported features from capabilities
     */
    getSupportedFeatures(capabilities) {
        const features = [];

        if (capabilities.color) features.push('color');
        if (capabilities.duplex) features.push('duplex');
        if (capabilities.collate) features.push('collate');
        if (capabilities.staple) features.push('staple');
        if (capabilities.copies) features.push('multiple-copies');

        // Paper sizes
        if (capabilities.paperSizes) {
            if (capabilities.paperSizes.some(size =>
                ['A4', 'Letter', 'Legal'].includes(size))) {
                features.push('standard-paper');
            }
            if (capabilities.paperSizes.includes('receipt')) {
                features.push('receipt-paper');
            }
            if (capabilities.paperSizes.some(size => size.includes('label'))) {
                features.push('label-paper');
            }
        }

        return features;
    }

    /**
     * Get basic features based on printer type
     */
    getBasicFeatures(printerName) {
        const type = this.detectPrinterType(printerName);
        const features = [];

        switch (type) {
            case 'label':
                features.push('labels', 'barcodes', 'zpl');
                break;
            case 'receipt':
                features.push('receipts', 'escpos', 'cut-paper');
                break;
            case 'virtual':
                features.push('pdf', 'html', 'images', 'color', 'duplex');
                break;
            case 'network':
                features.push('network-printing', 'shared');
                break;
            default:
                features.push('standard-printing');
        }

        return features;
    }

    /**
     * Select a printer
     */
    select(printerName, module = null) {
        if (!this.printers.includes(printerName)) {
            throw new Error(`Printer ${printerName} not found`);
        }

        const previous = this.selectedPrinter;
        this.selectedPrinter = printerName;

        // Save to preferences
        this.savePreference('selectedPrinter', printerName);

        if (module) {
            this.savePreference(`selectedPrinter_${module}`, printerName);
        }

        // Clear capabilities cache for previous printer
        if (previous) {
            this.capabilitiesCache.delete(previous);
        }

        // Emit event
        this.qzManager.emit('printerSelected', {
            previous: previous,
            current: printerName,
            module: module
        });

        return printerName;
    }

    /**
     * Get selected printer with fallback logic
     */
    async getSelected(module = null) {
        // 1. Check module-specific preference
        if (module) {
            const modulePrinter = this.getPreference(`selectedPrinter_${module}`);
            if (modulePrinter && this.printers.includes(modulePrinter)) {
                return modulePrinter;
            }
        }

        // 2. Check general preference
        const preferredPrinter = this.getPreference('selectedPrinter');
        if (preferredPrinter && this.printers.includes(preferredPrinter)) {
            return preferredPrinter;
        }

        // 3. Use currently selected printer
        if (this.selectedPrinter && this.printers.includes(this.selectedPrinter)) {
            return this.selectedPrinter;
        }

        // 4. Use default printer
        if (this.defaultPrinter) {
            return this.defaultPrinter;
        }

        // 5. Use first available printer
        if (this.printers.length > 0) {
            return this.printers[0];
        }

        // 6. Discover printers and try again
        await this.discoverAll();

        if (this.defaultPrinter) {
            return this.defaultPrinter;
        }

        if (this.printers.length > 0) {
            return this.printers[0];
        }

        throw new Error('No printers available');
    }

    /**
     * Clear printer queue
     */
    async clearQueue(printerName = null) {
        const printer = printerName || await this.getSelected();

        try {
            await this.qzManager.ensureConnected();
            await qz.printers.clearQueue(printer);

            this.qzManager.emit('queueCleared', { printer: printer });
            return true;

        } catch (error) {
            console.error(`Failed to clear queue for ${printer}:`, error);
            throw error;
        }
    }

    /**
     * Save user preference
     */
    savePreference(key, value) {
        this.userPreferences[key] = value;
        localStorage.setItem('qz_preferences', JSON.stringify(this.userPreferences));
    }

    /**
     * Get user preference
     */
    getPreference(key) {
        return this.userPreferences[key];
    }

    /**
     * Load preferences from localStorage
     */
    loadPreferences() {
        try {
            return JSON.parse(localStorage.getItem('qz_preferences')) || {};
        } catch (error) {
            console.warn('Failed to load preferences:', error);
            return {};
        }
    }

    /**
     * Clear all preferences
     */
    clearPreferences() {
        this.userPreferences = {};
        localStorage.removeItem('qz_preferences');
    }

    /**
     * Get printer status
     */
    async getStatus(printerName) {
        try {
            await this.qzManager.ensureConnected();

            // Try to get detailed status
            const info = await qz.printers.getPrinterInfo(printerName);

            return {
                name: printerName,
                online: true,
                ready: info.status === 'idle',
                status: info.status || 'unknown',
                jobs: info.jobs || 0,
                lastUsed: new Date(),
                capabilities: await this.getCapabilities(printerName)
            };

        } catch (error) {
            // Return basic status if detailed info fails
            return {
                name: printerName,
                online: false,
                ready: false,
                status: 'offline',
                jobs: 0,
                lastUsed: null,
                error: error.message
            };
        }
    }

    /**
     * Test printer connectivity
     */
    async testPrinter(printerName) {
        try {
            const status = await this.getStatus(printerName);

            if (!status.online) {
                throw new Error('Printer is offline');
            }

            if (!status.ready) {
                throw new Error('Printer is not ready');
            }

            return {
                success: true,
                printer: printerName,
                status: status,
                timestamp: new Date()
            };

        } catch (error) {
            return {
                success: false,
                printer: printerName,
                error: error.message,
                timestamp: new Date()
            };
        }
    }
}

// Export as global
if (typeof window !== 'undefined') {
    window.PrinterManager = PrinterManager;
}
