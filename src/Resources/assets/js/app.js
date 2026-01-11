/**
 * Main QZ Tray Application
 * Initializes and coordinates all components
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {

    // Check if we're on a QZ Tray page
    if (document.querySelector('[data-qz-tray-enabled]') || window.qzTrayConfig) {
        initializeQzTray();
    }
});

/**
 * Initialize QZ Tray Application
 */
async function initializeQzTray() {
    console.log('Initializing QZ Tray Application...');

    try {
        // Check if QZ Tray is available
        if (typeof qz === 'undefined') {
            console.warn('QZ Tray not loaded. Please install QZ Tray from https://qz.io');
            showQzTrayWarning();
            return;
        }

        // Create QZ Manager instance
        window.qzManager = new QzTrayManager(window.qzTrayConfig || {});

        // Create sub-managers
        window.qzManager.printerManager = new PrinterManager(window.qzManager);
        window.qzManager.printEngine = new PrintEngine(window.qzManager);
        window.qzManager.diagnostics = new DiagnosticsSystem(window.qzManager);

        // Initialize manager
        await window.qzManager.initialize();

        console.log('QZ Tray Application initialized successfully');

        // Setup global event handlers
        setupGlobalEventHandlers();

        // Auto-discover printers
        if (window.qzManager.config.autoConnect) {
            await window.qzManager.printerManager.discoverAll();
        }

    } catch (error) {
        console.error('Failed to initialize QZ Tray:', error);
        showQzTrayError(error);
    }
}

/**
 * Setup global event handlers
 */
function setupGlobalEventHandlers() {
    // Global print function
    window.qzPrint = async function(content, options = {}) {
        try {
            return await window.qzManager.printEngine.print(content, options);
        } catch (error) {
            console.error('Print failed:', error);
            throw error;
        }
    };

    // Global test print function
    window.qzTestPrint = async function(printerName) {
        try {
            const printer = printerName || await window.qzManager.printerManager.getSelected();
            return await window.qzManager.diagnostics.testPrint(printer);
        } catch (error) {
            console.error('Test print failed:', error);
            throw error;
        }
    };

    // Global discover printers function
    window.qzDiscoverPrinters = async function() {
        try {
            return await window.qzManager.printerManager.discoverAll();
        } catch (error) {
            console.error('Printer discovery failed:', error);
            throw error;
        }
    };

    // Global get selected printer function
    window.qzGetSelectedPrinter = async function(module = null) {
        try {
            return await window.qzManager.printerManager.getSelected(module);
        } catch (error) {
            console.error('Failed to get selected printer:', error);
            throw error;
        }
    };

    // Global select printer function
    window.qzSelectPrinter = function(printerName, module = null) {
        try {
            return window.qzManager.printerManager.select(printerName, module);
        } catch (error) {
            console.error('Failed to select printer:', error);
            throw error;
        }
    };

    // Global run diagnostics function
    window.qzRunDiagnostics = async function() {
        try {
            return await window.qzManager.diagnostics.runFullDiagnostics();
        } catch (error) {
            console.error('Diagnostics failed:', error);
            throw error;
        }
    };

    // Global print barcode function
    window.qzPrintBarcode = async function(data, type = 'CODE128', options = {}) {
        try {
            return await window.qzManager.printEngine.print(data, {
                ...options,
                format: 'barcode',
                type: type
            });
        } catch (error) {
            console.error('Barcode print failed:', error);
            throw error;
        }
    };

    // Global print receipt function
    window.qzPrintReceipt = async function(content, options = {}) {
        try {
            return await window.qzManager.printEngine.print(content, {
                ...options,
                format: 'escpos',
                paperSize: 'Receipt'
            });
        } catch (error) {
            console.error('Receipt print failed:', error);
            throw error;
        }
    };

    // Global print label function
    window.qzPrintLabel = async function(content, options = {}) {
        try {
            return await window.qzManager.printEngine.print(content, {
                ...options,
                format: 'zpl',
                paperSize: 'Label'
            });
        } catch (error) {
            console.error('Label print failed:', error);
            throw error;
        }
    };
}

/**
 * Show QZ Tray warning
 */
function showQzTrayWarning() {
    // Check if we should show the warning
    if (!document.getElementById('qz-tray-warning')) {
        const warning = document.createElement('div');
        warning.id = 'qz-tray-warning';
        warning.className = 'alert alert-warning alert-dismissible fade show';
        warning.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        `;

        warning.innerHTML = `
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>QZ Tray Required</h6>
            <p class="mb-2">QZ Tray is not installed or not loaded.</p>
            <p class="mb-3 small">Please install QZ Tray to enable printing features.</p>
            <div class="d-flex gap-2">
                <a href="https://qz.io/download/" target="_blank" class="btn btn-sm btn-primary">
                    <i class="fas fa-download me-1"></i> Download QZ Tray
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('qz-tray-warning').remove()">
                    Dismiss
                </button>
            </div>
        `;

        document.body.appendChild(warning);

        // Auto-remove after 30 seconds
        setTimeout(() => {
            if (warning.parentNode) {
                warning.remove();
            }
        }, 30000);
    }
}

/**
 * Show QZ Tray error
 */
function showQzTrayError(error) {
    // Check if we should show the error
    if (!document.getElementById('qz-tray-error')) {
        const errorDiv = document.createElement('div');
        errorDiv.id = 'qz-tray-error';
        errorDiv.className = 'alert alert-danger alert-dismissible fade show';
        errorDiv.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        `;

        const errorMessage = error.message || 'Unknown error';

        errorDiv.innerHTML = `
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <h6 class="alert-heading"><i class="fas fa-exclamation-circle me-2"></i>QZ Tray Error</h6>
            <p class="mb-2">${errorMessage}</p>
            <p class="mb-3 small">Check browser console for details.</p>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="location.reload()">
                    <i class="fas fa-redo me-1"></i> Reload
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('qz-tray-error').remove()">
                    Dismiss
                </button>
            </div>
        `;

        document.body.appendChild(errorDiv);
    }
}

/**
 * Create print button
 */
function createPrintButton(options = {}) {
    const button = document.createElement('button');
    button.className = options.className || 'btn btn-primary qz-print-button';
    button.innerHTML = options.html || '<i class="fas fa-print"></i> Print';
    button.onclick = options.onClick || async function() {
        try {
            const content = options.content || document.getElementById(options.contentId).innerHTML;
            await window.qzPrint(content, options);
        } catch (error) {
            console.error('Print failed:', error);
            alert('Print failed: ' + error.message);
        }
    };

    return button;
}

/**
 * Create printer selector
 */
function createPrinterSelector(options = {}) {
    const container = document.createElement('div');
    container.className = options.className || 'qz-printer-selector';

    const select = document.createElement('select');
    select.className = 'form-select';
    select.innerHTML = '<option>Loading printers...</option>';

    const refreshButton = document.createElement('button');
    refreshButton.className = 'btn btn-sm btn-outline-secondary ms-2';
    refreshButton.innerHTML = '<i class="fas fa-sync"></i>';
    refreshButton.onclick = async function() {
        select.innerHTML = '<option>Refreshing...</option>';
        await window.qzDiscoverPrinters();
        updatePrinterSelector(select);
    };

    container.appendChild(select);
    container.appendChild(refreshButton);

    // Update printer list
    setTimeout(async () => {
        await updatePrinterSelector(select);
    }, 1000);

    return container;
}

/**
 * Update printer selector
 */
async function updatePrinterSelector(select) {
    try {
        const result = await window.qzDiscoverPrinters();

        select.innerHTML = '';

        result.printers.forEach(printer => {
            const option = document.createElement('option');
            option.value = printer;
            option.textContent = printer;

            if (printer === result.selected) {
                option.selected = true;
            }

            if (printer === result.default) {
                option.textContent += ' (Default)';
            }

            select.appendChild(option);
        });

        select.onchange = function() {
            window.qzSelectPrinter(this.value);
        };

    } catch (error) {
        select.innerHTML = '<option>Failed to load printers</option>';
    }
}

// Export global functions
window.qzPrint = window.qzPrint || function() {
    console.warn('QZ Tray not initialized. Call initializeQzTray() first.');
};

window.qzTestPrint = window.qzTestPrint || function() {
    console.warn('QZ Tray not initialized. Call initializeQzTray() first.');
};

window.qzDiscoverPrinters = window.qzDiscoverPrinters || function() {
    console.warn('QZ Tray not initialized. Call initializeQzTray() first.');
};

// Auto-initialize if config is present
if (window.qzTrayConfig && window.qzTrayConfig.autoInitialize !== false) {
    initializeQzTray();
}
