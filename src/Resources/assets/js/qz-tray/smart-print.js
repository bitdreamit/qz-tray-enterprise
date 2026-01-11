/**
 * Smart Print Manager for QZ Tray
 * Auto-detects best printing method with fallback
 */

class SmartPrintManager {
    constructor(config = {}) {
        this.config = {
            enabled: true,
            fallbackOrder: ['qz', 'browser', 'download'],
            autoAttach: true,
            showIndicator: true,
            showErrors: true,
            defaultType: 'auto',
            timeout: 30000,
            ...config
        };

        this.state = {
            qzAvailable: false,
            qzConnected: false,
            printStats: {
                qzSuccess: 0,
                browserSuccess: 0,
                downloadFallback: 0,
                totalAttempts: 0
            }
        };

        this.events = new EventTarget();

        // Initialize
        this.detectPrintMethods();
    }

    /**
     * Detect available print methods
     */
    detectPrintMethods() {
        // Check QZ Tray
        this.state.qzAvailable = typeof qz !== 'undefined';

        if (this.state.qzAvailable) {
            // Try to connect to QZ
            qz.websocket.connect({ retries: 0 })
                .then(() => {
                    this.state.qzConnected = true;
                    console.log('✅ Smart Print: QZ Tray detected and connected');
                })
                .catch(() => {
                    console.log('⚠️ Smart Print: QZ Tray detected but not connected');
                });
        }
    }

    /**
     * Smart print function
     */
    async smartPrint(options) {
        this.state.printStats.totalAttempts++;

        const printOptions = {
            url: '',
            type: this.config.defaultType,
            filename: 'document.pdf',
            printer: null,
            copies: 1,
            paperSize: 'A4',
            orientation: 'portrait',
            silent: false,
            ...options
        };

        if (!printOptions.url) {
            throw new Error('Print URL is required');
        }

        // Auto-detect file type
        if (printOptions.type === 'auto') {
            printOptions.type = this.detectFileType(printOptions.url);
        }

        console.log('Starting smart print:', printOptions);

        // Try methods in configured order
        for (const method of this.config.fallbackOrder) {
            try {
                let result;

                switch (method) {
                    case 'qz':
                        result = await this.printWithQz(printOptions);
                        break;

                    case 'browser':
                        result = await this.printWithBrowser(printOptions);
                        break;

                    case 'download':
                        result = await this.printWithDownload(printOptions);
                        break;

                    default:
                        continue;
                }

                // Update statistics
                if (method === 'qz') this.state.printStats.qzSuccess++;
                if (method === 'browser') this.state.printStats.browserSuccess++;
                if (method === 'download') this.state.printStats.downloadFallback++;

                // Show indicator
                this.showIndicator(method);

                // Dispatch event
                this.emit('success', { method, options: printOptions, result });

                return {
                    success: true,
                    method,
                    message: `Printed using ${method.toUpperCase()}`,
                    data: result
                };

            } catch (error) {
                console.log(`Method ${method} failed:`, error.message);

                this.emit('methodFailed', { method, error: error.message, options: printOptions });

                // Continue to next method
                continue;
            }
        }

        // All methods failed
        this.emit('failed', { options: printOptions });
        throw new Error('All print methods failed');
    }

    /**
     * Print using QZ Tray
     */
    async printWithQz(options) {
        if (!this.state.qzAvailable) {
            throw new Error('QZ Tray not available');
        }

        try {
            // Ensure connected
            await qz.websocket.connect({
                retries: 2,
                delay: 1000
            });

            this.state.qzConnected = true;

            // Get printer
            let printer = options.printer;
            if (!printer) {
                printer = await qz.printers.getDefault();
                if (!printer) {
                    throw new Error('No default printer available');
                }
            }

            // Create config
            const config = qz.configs.create(printer, {
                copies: options.copies,
                size: options.paperSize,
                orientation: options.orientation
            });

            let printData;

            switch (options.type) {
                case 'pdf':
                    const pdfResponse = await fetch(options.url);
                    const pdfBuffer = await pdfResponse.arrayBuffer();
                    const pdfBase64 = this.arrayBufferToBase64(pdfBuffer);

                    printData = [{
                        type: 'pdf',
                        format: 'base64',
                        data: pdfBase64
                    }];
                    break;

                case 'html':
                    const htmlResponse = await fetch(options.url);
                    const htmlContent = await htmlResponse.text();

                    printData = [{
                        type: 'html',
                        data: htmlContent,
                        options: {
                            size: options.paperSize,
                            orientation: options.orientation
                        }
                    }];
                    break;

                case 'image':
                    printData = [{
                        type: 'image',
                        format: 'url',
                        data: options.url
                    }];
                    break;

                case 'raw':
                    const rawResponse = await fetch(options.url);
                    const rawContent = await rawResponse.text();

                    printData = [rawContent];
                    break;

                default:
                    throw new Error(`Unsupported print type for QZ: ${options.type}`);
            }

            // Execute print
            const result = await qz.print(config, printData);

            return {
                printer,
                result
            };

        } catch (error) {
            throw new Error(`QZ Tray print failed: ${error.message}`);
        }
    }

    /**
     * Print using browser
     */
    async printWithBrowser(options) {
        return new Promise((resolve, reject) => {
            const timeout = setTimeout(() => {
                reject(new Error('Browser print timeout'));
            }, this.config.timeout);

            try {
                if (options.type === 'pdf' || options.type === 'html') {
                    // Open in iframe and print
                    const iframe = document.createElement('iframe');
                    iframe.style.position = 'fixed';
                    iframe.style.right = '-9999px';
                    iframe.style.bottom = '-9999px';
                    iframe.style.width = '1px';
                    iframe.style.height = '1px';
                    iframe.style.border = '0';

                    iframe.onload = () => {
                        try {
                            if (options.silent) {
                                iframe.contentWindow.print();
                            } else {
                                iframe.contentWindow.print();
                            }

                            setTimeout(() => {
                                document.body.removeChild(iframe);
                                clearTimeout(timeout);
                                resolve({ method: 'browser' });
                            }, 100);
                        } catch (error) {
                            document.body.removeChild(iframe);
                            clearTimeout(timeout);
                            reject(error);
                        }
                    };

                    iframe.onerror = () => {
                        document.body.removeChild(iframe);
                        clearTimeout(timeout);
                        reject(new Error('Failed to load content'));
                    };

                    iframe.src = options.url;
                    document.body.appendChild(iframe);

                } else if (options.type === 'image') {
                    // Open image in new window and print
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(`
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Print Image</title>
                            <style>
                                body { margin: 0; padding: 20px; }
                                img { max-width: 100%; max-height: 100%; display: block; margin: 0 auto; }
                            </style>
                        </head>
                        <body>
                            <img src="${options.url}" onload="window.print(); window.close();">
                        </body>
                        </html>
                    `);
                    printWindow.document.close();

                    clearTimeout(timeout);
                    resolve({ method: 'browser' });

                } else {
                    // Direct window print
                    const printWindow = window.open(options.url, '_blank');
                    if (printWindow) {
                        printWindow.onload = () => {
                            if (options.silent) {
                                printWindow.print();
                            }
                            setTimeout(() => {
                                printWindow.close();
                                clearTimeout(timeout);
                                resolve({ method: 'browser' });
                            }, 100);
                        };
                    } else {
                        clearTimeout(timeout);
                        reject(new Error('Popup blocked. Please allow popups for printing.'));
                    }
                }
            } catch (error) {
                clearTimeout(timeout);
                reject(error);
            }
        });
    }

    /**
     * Fallback to download
     */
    async printWithDownload(options) {
        return new Promise((resolve, reject) => {
            try {
                const link = document.createElement('a');
                link.href = options.url;
                link.download = options.filename;
                link.style.display = 'none';

                link.onclick = () => {
                    setTimeout(() => {
                        document.body.removeChild(link);
                        resolve({ method: 'download' });
                    }, 100);
                };

                document.body.appendChild(link);
                link.click();

                // Fallback in case onclick doesn't fire
                setTimeout(() => {
                    if (link.parentNode) {
                        document.body.removeChild(link);
                        resolve({ method: 'download' });
                    }
                }, 1000);

            } catch (error) {
                reject(new Error('Download failed: ' + error.message));
            }
        });
    }

    /**
     * Detect file type from URL
     */
    detectFileType(url) {
        if (!url) return 'html';

        const extension = url.split('.').pop().toLowerCase().split('?')[0];

        const typeMap = {
            'pdf': 'pdf',
            'html': 'html',
            'htm': 'html',
            'jpg': 'image',
            'jpeg': 'image',
            'png': 'image',
            'gif': 'image',
            'bmp': 'image',
            'svg': 'image',
            'txt': 'raw',
            'text': 'raw',
            'log': 'raw',
            'zpl': 'raw',
            'epl': 'raw',
            'esc': 'raw',
        };

        return typeMap[extension] || 'html';
    }

    /**
     * Show print indicator
     */
    showIndicator(method, message = null) {
        if (!this.config.showIndicator) return;

        const indicator = document.getElementById('smart-print-indicator');
        if (!indicator) return;

        indicator.className = `print-method-indicator print-method-${method}`;

        let text = '';
        switch(method) {
            case 'qz':
                text = message || '🖨️ Printing via QZ Tray';
                break;
            case 'browser':
                text = message || '🌐 Printing via Browser';
                break;
            case 'download':
                text = message || '📥 Downloading file';
                break;
            default:
                text = message || '🖨️ Printing';
        }

        indicator.textContent = text;
        indicator.style.display = 'block';

        // Auto-hide after 3 seconds
        setTimeout(() => {
            indicator.style.opacity = '0';
            setTimeout(() => {
                indicator.style.display = 'none';
                indicator.style.opacity = '1';
            }, 300);
        }, 3000);
    }

    /**
     * Helper: ArrayBuffer to Base64
     */
    arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return btoa(binary);
    }

    /**
     * Get statistics
     */
    getStats() {
        return {
            ...this.state.printStats,
            successRate: this.state.printStats.totalAttempts > 0
                ? ((this.state.printStats.qzSuccess + this.state.printStats.browserSuccess) / this.state.printStats.totalAttempts * 100).toFixed(2) + '%'
                : '0%',
            qzAvailable: this.state.qzAvailable,
            qzConnected: this.state.qzConnected
        };
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
}

// Global smart print function
window.smartPrint = async function(url, options = {}) {
    if (!window.smartPrintManager) {
        console.error('Smart Print Manager not initialized');
        return { success: false, error: 'Smart Print Manager not initialized' };
    }

    return await window.smartPrintManager.smartPrint({
        url,
        ...options
    });
};

// Auto-initialize
document.addEventListener('DOMContentLoaded', function() {
    if (window.qzTrayConfig?.smartPrint?.enabled) {
        window.smartPrintManager = new SmartPrintManager(window.qzTrayConfig.smartPrint);

        // Auto-attach to smart-print elements
        if (window.qzTrayConfig.smartPrint.autoAttach) {
            document.addEventListener('click', async function(e) {
                const printBtn = e.target.closest('.smart-print');
                if (!printBtn) return;

                e.preventDefault();

                const url = printBtn.dataset.url;
                const type = printBtn.dataset.type || 'auto';
                const filename = printBtn.dataset.filename || null;
                const printer = printBtn.dataset.printer || null;
                const silent = printBtn.dataset.silent === 'true';
                const copies = parseInt(printBtn.dataset.copies) || 1;

                if (!url) {
                    console.error('No URL specified for printing');
                    return;
                }

                // Add loading state
                const originalHtml = printBtn.innerHTML;
                const originalClass = printBtn.className;
                printBtn.classList.add('smart-print-loading');

                try {
                    const result = await window.smartPrint(url, {
                        type,
                        filename,
                        printer,
                        silent,
                        copies,
                        paperSize: printBtn.dataset.paperSize || 'A4',
                        orientation: printBtn.dataset.orientation || 'portrait'
                    });

                    // Success feedback
                    printBtn.classList.remove('smart-print-loading');
                    printBtn.classList.add('smart-print-success');

                    // Restore original style after 2 seconds
                    setTimeout(() => {
                        printBtn.className = originalClass;
                        printBtn.innerHTML = originalHtml;
                    }, 2000);

                } catch (error) {
                    // Error feedback
                    printBtn.classList.remove('smart-print-loading');
                    printBtn.classList.add('smart-print-error');

                    console.error('Print failed:', error);

                    if (window.qzTrayConfig.smartPrint.showErrors) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Print failed: ' + error.message);
                        } else {
                            alert('Print failed: ' + error.message);
                        }
                    }

                    // Restore original style after 2 seconds
                    setTimeout(() => {
                        printBtn.className = originalClass;
                        printBtn.innerHTML = originalHtml;
                    }, 2000);
                }
            });
        }
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SmartPrintManager;
}
