/**
 * Print Engine for QZ Tray
 * Handles all printing operations
 */

class PrintEngine {
    constructor(qzManager) {
        this.qzManager = qzManager;
        this.jobQueue = [];
        this.processing = false;
        this.jobHistory = [];
        this.maxHistory = 100;

        // Print formats and their handlers
        this.formats = {
            raw: this.printRaw.bind(this),
            html: this.printHtml.bind(this),
            pdf: this.printPdf.bind(this),
            image: this.printImage.bind(this),
            zpl: this.printZpl.bind(this),
            escpos: this.printEscpos.bind(this),
            barcode: this.printBarcode.bind(this)
        };
    }

    /**
     * Print content
     */
    async print(content, options = {}) {
        const format = options.format || this.detectFormat(content);

        if (!this.formats[format]) {
            throw new Error(`Unsupported print format: ${format}`);
        }

        const job = {
            id: this.generateJobId(),
            format: format,
            content: content,
            options: options,
            status: 'queued',
            timestamp: new Date(),
            attempts: 0
        };

        // Add to queue
        this.jobQueue.push(job);
        this.qzManager.emit('jobQueued', job);

        // Process queue
        await this.processQueue();

        return job.id;
    }

    /**
     * Process print queue
     */
    async processQueue() {
        if (this.processing || this.jobQueue.length === 0) {
            return;
        }

        this.processing = true;

        while (this.jobQueue.length > 0) {
            const job = this.jobQueue[0];

            try {
                job.status = 'processing';
                job.startedAt = new Date();
                job.attempts++;

                this.qzManager.emit('jobProcessing', job);

                // Execute print
                const result = await this.executeJob(job);

                job.status = 'completed';
                job.result = result;
                job.completedAt = new Date();

                // Move to history
                this.jobQueue.shift();
                this.jobHistory.unshift(job);

                // Trim history
                if (this.jobHistory.length > this.maxHistory) {
                    this.jobHistory = this.jobHistory.slice(0, this.maxHistory);
                }

                this.qzManager.emit('jobCompleted', job);

            } catch (error) {
                console.error(`Print job ${job.id} failed:`, error);

                job.status = 'failed';
                job.error = error.message;
                job.completedAt = new Date();

                if (job.attempts < 3) {
                    // Move to end of queue for retry
                    this.jobQueue.push(this.jobQueue.shift());

                    // Wait before retry
                    await this.delay(2000);
                } else {
                    // Move to history as failed
                    this.jobQueue.shift();
                    this.jobHistory.unshift(job);
                    this.qzManager.emit('jobFailed', job);
                }
            }
        }

        this.processing = false;
    }

    /**
     * Execute a print job
     */
    async executeJob(job) {
        await this.qzManager.ensureConnected();

        const printer = job.options.printer || await this.qzManager.printerManager.getSelected();
        const config = this.createConfig(printer, job.options);

        // Get format handler
        const handler = this.formats[job.format];
        if (!handler) {
            throw new Error(`No handler for format: ${job.format}`);
        }

        // Execute print
        return await handler(job.content, config, job.options);
    }

    /**
     * Create printer configuration
     */
    createConfig(printerName, options = {}) {
        const config = qz.configs.create(printerName, {
            colorType: options.color || 'color',
            copies: options.copies || 1,
            duplex: options.duplex || false,
            interpolation: 'bilinear',
            jobName: options.jobName || `Print Job ${Date.now()}`,
            orientation: options.orientation || 'portrait',
            paperThickness: 'default',
            printerTray: options.tray || 'auto',
            rasterize: options.rasterize !== false,
            scaleContent: options.scaleContent !== false,
            size: this.getPaperSize(options.paperSize),
            ...options
        });

        return config;
    }

    /**
     * Get paper size configuration
     */
    getPaperSize(paperSize = 'A4') {
        const sizes = {
            'A4': { width: 210, height: 297, unit: 'mm' },
            'A5': { width: 148, height: 210, unit: 'mm' },
            'Letter': { width: 216, height: 279, unit: 'mm' },
            'Legal': { width: 216, height: 356, unit: 'mm' },
            'Receipt': { width: 80, height: 'continuous', unit: 'mm' },
            'Label': { width: 100, height: 150, unit: 'mm' }
        };

        return sizes[paperSize] || sizes['A4'];
    }

    /**
     * Detect content format
     */
    detectFormat(content) {
        if (typeof content !== 'string') {
            return 'raw';
        }

        // Check for ZPL
        if (content.trim().startsWith('^') || content.includes('^XA')) {
            return 'zpl';
        }

        // Check for ESC/POS
        if (content.includes('\x1B') || content.includes('\x1D')) {
            return 'escpos';
        }

        // Check for HTML
        if (content.includes('<html') || content.includes('<div') || content.includes('<p')) {
            return 'html';
        }

        // Check for PDF (basic detection)
        if (content.startsWith('%PDF')) {
            return 'pdf';
        }

        // Check for image URL
        if (content.match(/\.(jpg|jpeg|png|gif|bmp|svg)$/i)) {
            return 'image';
        }

        // Default to raw
        return 'raw';
    }

    /**
     * Print raw text
     */
    async printRaw(content, config, options = {}) {
        return await qz.print(config, [content]);
    }

    /**
     * Print HTML
     */
    async printHtml(content, config, options = {}) {
        const printData = [{
            type: 'html',
            format: 'plain',
            data: content,
            options: {
                pageSize: options.paperSize || 'A4',
                margins: options.margins || { top: 0.4, bottom: 0.4, left: 0.4, right: 0.4 },
                scale: options.scale || 1.0
            }
        }];

        return await qz.print(config, printData);
    }

    /**
     * Print PDF
     */
    async printPdf(content, config, options = {}) {
        const printData = [{
            type: 'pdf',
            format: options.pdfFormat || 'file',
            data: content,
            options: {
                pageRange: options.pageRange || 'all',
                orientation: options.orientation || 'portrait'
            }
        }];

        return await qz.print(config, printData);
    }

    /**
     * Print image
     */
    async printImage(content, config, options = {}) {
        const printData = [{
            type: 'image',
            format: options.imageFormat || 'url',
            data: content,
            options: {
                density: options.dpi || 300,
                width: options.width,
                height: options.height
            }
        }];

        return await qz.print(config, printData);
    }

    /**
     * Print ZPL
     */
    async printZpl(content, config, options = {}) {
        const printData = [{
            type: 'raw',
            format: 'plain',
            data: content,
            flavor: 'zpl'
        }];

        return await qz.print(config, printData);
    }

    /**
     * Print ESC/POS
     */
    async printEscpos(content, config, options = {}) {
        const printData = [{
            type: 'raw',
            format: 'plain',
            data: content,
            flavor: 'escpos'
        }];

        return await qz.print(config, printData);
    }

    /**
     * Print barcode
     */
    async printBarcode(data, config, options = {}) {
        const type = options.type || 'CODE128';
        const width = options.width || 2;
        const height = options.height || 100;
        const humanReadable = options.humanReadable !== false;

        let barcodeContent;

        if (options.flavor === 'zpl') {
            // ZPL barcode
            barcodeContent = `
^XA
^FO50,50
^BY${width},3,${height}
^B${type},N,N,N
^FD${data}^FS
${humanReadable ? `^FO50,150^A0N,50,50^FD${data}^FS` : ''}
^XZ
`;
            return await this.printZpl(barcodeContent, config, options);

        } else if (options.flavor === 'escpos') {
            // ESC/POS barcode
            barcodeContent = `
\x1D\x68\x64\x1D\x48\x02\x1D\x77\x03\x1D\x6B\x04${data}\x00
`;
            return await this.printEscpos(barcodeContent, config, options);

        } else {
            // Generic barcode using image
            const barcodeUrl = this.generateBarcodeImage(data, type, options);
            return await this.printImage(barcodeUrl, config, options);
        }
    }

    /**
     * Generate barcode image URL
     */
    generateBarcodeImage(data, type, options = {}) {
        const params = new URLSearchParams({
            data: data,
            code: type,
            dpi: options.dpi || 96,
            format: options.imageFormat || 'PNG',
            width: options.imageWidth || 300,
            height: options.imageHeight || 150
        });

        return `https://barcode.tec-it.com/barcode.ashx?${params.toString()}`;
    }

    /**
     * Generate job ID
     */
    generateJobId() {
        return `job_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Delay helper
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Get job history
     */
    getHistory(limit = 50) {
        return this.jobHistory.slice(0, limit);
    }

    /**
     * Get queue status
     */
    getQueueStatus() {
        return {
            queued: this.jobQueue.length,
            processing: this.processing,
            recentJobs: this.jobHistory.slice(0, 10),
            lastJob: this.jobHistory[0] || null
        };
    }

    /**
     * Clear job history
     */
    clearHistory() {
        this.jobHistory = [];
    }

    /**
     * Clear queue
     */
    clearQueue() {
        this.jobQueue = [];
    }
}

// Export as global
if (typeof window !== 'undefined') {
    window.PrintEngine = PrintEngine;
}
