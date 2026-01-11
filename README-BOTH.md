# QZ Tray Enterprise for Laravel

[![Latest Version](https://img.shields.io/packagist/v/bitdreamit/qz-tray-enterprise.svg)](https://packagist.org/packages/bitdreamit/qz-tray-enterprise)
[![Total Downloads](https://img.shields.io/packagist/dt/bitdreamit/qz-tray-enterprise.svg)](https://packagist.org/packages/bitdreamit/qz-tray-enterprise)
[![License](https://img.shields.io/packagist/l/bitdreamit/qz-tray-enterprise.svg)](https://packagist.org/packages/bitdreamit/qz-tray-enterprise)
[![PHP Version](https://img.shields.io/packagist/php-v/bitdreamit/qz-tray-enterprise.svg)](https://packagist.org/packages/bitdreamit/qz-tray-enterprise)
[![Laravel Version](https://img.shields.io/badge/Laravel-9.x|10.x|11.x-brightgreen.svg)](https://laravel.com)

Complete QZ Tray integration package for Laravel - The definitive enterprise printing solution with **Smart Print** technology. Now with intelligent fallback system that automatically chooses the best printing method.

## 🎯 What's New: Smart Print Technology

### 🚀 **Smart Print Features**
- ✅ **Intelligent Auto-Detection** - Automatically detects if QZ Tray is available
- ✅ **Seamless Fallback System** - QZ Tray → Browser Print → Download
- ✅ **One-Click Printing** - Just add `class="smart-print"` to any element
- ✅ **File Type Detection** - Auto-detects PDF, HTML, images, ZPL, ESC/POS
- ✅ **Visual Feedback** - Loading states, success/error indicators
- ✅ **Statistics & Logging** - Track print method usage and success rates
- ✅ **Module-Specific Preferences** - Different printers for different tasks
- ✅ **Silent Printing** - Print without showing dialog when needed

## 📦 Package Structure

```
qz-tray-enterprise/
├── src/
│   ├── Services/
│   │   ├── QzService.php
│   │   ├── PrinterService.php
│   │   ├── PrintService.php
│   │   └── SmartPrintService.php          ← NEW: Smart Print logic
│   ├── Resources/
│   │   ├── views/partials/
│   │   │   └── qz-assets.blade.php        ← UPDATED: With Smart Print
│   │   └── assets/js/qz-tray/
│   │       └── smart-print.js             ← NEW: Smart Print JavaScript
│   ├── Models/
│   │   └── SmartPrintLog.php              ← NEW: Smart Print logging
│   ├── Events/
│   │   ├── SmartPrintSuccess.php          ← NEW: Smart Print events
│   │   └── SmartPrintFailed.php           ← NEW
│   ├── Listeners/
│   │   └── LogSmartPrint.php              ← NEW: Smart Print listener
│   └── Helpers/
│       └── smartPrint.php                 ← NEW: Helper functions
├── database/migrations/
│   └── create_smart_print_logs_table.php  ← NEW: Smart Print logs
├── config/
│   └── qz-tray.php                        ← UPDATED: Smart Print config
└── README.md                               ← YOU ARE HERE
```

## 🚀 Installation & Setup

### 1. Install Package
```bash
composer require bitdreamit/qz-tray-enterprise
```

### 2. Run Installation Command
```bash
php artisan qz-tray:install
```

### 3. Run Migrations (Includes Smart Print Logs)
```bash
php artisan migrate
```

### 4. Generate Security Certificate
```bash
php artisan qz-tray:generate-certificate
```

### 5. Include Assets in Your Layout
```blade
<!DOCTYPE html>
<html>
<head>
    <!-- Include Smart Print assets -->
    @include('qz-tray::partials.qz-assets')
</head>
<body>
    @yield('content')
</body>
</html>
```

## ⚡ Smart Print Quick Start

### Basic Usage - Just Add a Class!
```blade
<!-- Simple Smart Print Button -->
<a href="#" class="smart-print" 
   data-url="/print/invoice/123" 
   title="Print Invoice">
    <i class="fas fa-print"></i> Print Invoice
</a>

<!-- Smart Print with Specific Options -->
<button class="smart-print btn btn-primary"
        data-url="/print/report/monthly"
        data-type="pdf"
        data-filename="monthly-report.pdf"
        data-copies="2">
    <i class="fas fa-file-pdf"></i> Print Report
</button>
```

### Blade Helper Functions
```blade
<!-- Generate Smart Print Button -->
@smartPrintButton(route('orders.print', $order->id), [
    'class' => 'btn btn-success',
    'title' => 'Print Receipt',
    'icon' => 'fas fa-receipt',
    'copies' => 2,
    'type' => 'pdf'
])

<!-- Generate Smart Print Link -->
@smartPrintLink(route('labels.print', $product->id), 'Print Label', [
    'class' => 'text-blue-500 hover:underline',
    'type' => 'raw',
    'printer' => 'Zebra LP 2844'
])
```

### JavaScript API
```javascript
// Direct JavaScript printing
await window.smartPrint('/print/url', {
    type: 'pdf',
    filename: 'document.pdf',
    copies: 2,
    silent: true
});

// Get print statistics
const stats = window.smartPrintManager.getStats();
console.log('Print success rate:', stats.successRate);

// Listen to print events
document.addEventListener('smartPrintSuccess', (event) => {
    console.log('Printed via:', event.detail.method);
});

document.addEventListener('smartPrintFailed', (event) => {
    console.error('Print failed:', event.detail);
});
```

## ⚙️ Smart Print Configuration

### Environment Variables (.env)
```env
# Smart Print Configuration
QZ_SMART_PRINT_ENABLED=true
QZ_FALLBACK_ORDER=qz,browser,download
QZ_AUTO_ATTACH=true
QZ_SHOW_PRINT_INDICATOR=true
QZ_SHOW_PRINT_ERRORS=true
QZ_DEFAULT_PRINT_TYPE=auto
QZ_PRINT_TIMEOUT=30000
```

### Configuration File (config/qz-tray.php)
```php
'smart_print' => [
    'enabled' => env('QZ_SMART_PRINT_ENABLED', true),
    'fallback_order' => explode(',', env('QZ_FALLBACK_ORDER', 'qz,browser,download')),
    'auto_attach' => env('QZ_AUTO_ATTACH', true),
    'show_indicator' => env('QZ_SHOW_PRINT_INDICATOR', true),
    'show_errors' => env('QZ_SHOW_PRINT_ERRORS', true),
    'default_type' => env('QZ_DEFAULT_PRINT_TYPE', 'auto'),
    'timeout' => env('QZ_PRINT_TIMEOUT', 30000),
],
```

## 🎯 Smart Print Features

### 1. **Auto File Type Detection**
The system automatically detects file types:
- `invoice.pdf` → `type: 'pdf'`
- `label.html` → `type: 'html'`
- `photo.jpg` → `type: 'image'`
- `barcode.zpl` → `type: 'raw'`

### 2. **Intelligent Fallback Logic**
```javascript
// Smart Print Fallback Flow:
1. Check if QZ Tray is available and connected
2. If YES → Print via QZ Tray (fastest, most reliable)
3. If NO → Try browser print (opens in new window/iframe)
4. If browser blocked → Download file automatically
5. Show visual feedback to user
```

### 3. **Module-Specific Printers**
```php
// Save printer preferences per module
QzTray::saveUserPreference('labels', 'Zebra LP 2844');
QzTray::saveUserPreference('receipts', 'EPSON TM-T88V');
QzTray::saveUserPreference('documents', 'HP LaserJet');

// Smart Print will use the right printer automatically
<a class="smart-print" data-url="/print/label" data-type="zpl">
    <!-- Will use 'Zebra LP 2844' automatically -->
</a>
```

### 4. **Visual Feedback System**
- **Loading State**: Button shows "Printing..." with spinner
- **Success State**: Green highlight with success message
- **Error State**: Red highlight with error message
- **Method Indicator**: Shows which method was used (QZ/Browser/Download)

## 📊 Smart Print Statistics & Logging

### View Print Statistics
```php
// Get smart print statistics
$stats = QzTray::smartPrintStats();

// Returns:
[
    'total_attempts' => 150,
    'qz_success' => 120,      // 80% success rate with QZ
    'browser_success' => 25,  // 16.7% browser print
    'download_fallback' => 5, // 3.3% download fallback
    'success_rate' => '96.7%',
    'recent_logs' => [...]
]
```

### Database Logs Table
```sql
smart_print_logs
├── id
├── user_id
├── url
├── type (pdf/html/image/raw/auto)
├── printer
├── copies
├── status (processing/success/failed)
├── method_used (qz/browser/download)
├── error_message
├── ip_address
├── user_agent
├── metadata (JSON)
├── response (JSON)
├── created_at
└── updated_at
```

## 🔧 Advanced Smart Print Usage

### Silent Printing (No Dialog)
```blade
<button class="smart-print"
        data-url="/print/silent/zpl"
        data-type="raw"
        data-silent="true"
        data-printer="Zebra LP 2844">
    <i class="fas fa-barcode"></i> Silent Label Print
</button>
```

### Multiple Copies
```blade
<a class="smart-print btn btn-warning"
   data-url="/print/bulk/labels"
   data-type="pdf"
   data-copies="10"
   data-filename="bulk-labels.pdf">
    <i class="fas fa-copy"></i> Print 10 Copies
</a>
```

### Custom Paper Size & Orientation
```blade
<button class="smart-print"
        data-url="/print/landscape/report"
        data-type="pdf"
        data-paper-size="A4"
        data-orientation="landscape">
    <i class="fas fa-file-alt"></i> Print Landscape
</button>
```

### Event Listeners for Custom Behavior
```javascript
// Custom event listeners
document.addEventListener('smartPrintSuccess', function(event) {
    const method = event.detail.method;
    const options = event.detail.options;
    
    // Update UI
    updatePrintStats(method);
    
    // Show custom notification
    toast.success(`Printed via ${method.toUpperCase()}`);
    
    // Log to analytics
    analytics.track('print_success', {
        method: method,
        type: options.type,
        copies: options.copies
    });
});

document.addEventListener('smartPrintFailed', function(event) {
    console.error('Smart print failed:', event.detail);
    
    // Show fallback options
    showPrintFallbackOptions(event.detail.options.url);
});
```

## 🖨️ Supported Print Formats

### Smart Print automatically handles:
| Format | Extension | Description |
|--------|-----------|-------------|
| **PDF** | `.pdf` | Adobe PDF documents |
| **HTML** | `.html`, `.htm` | Web pages and reports |
| **Images** | `.jpg`, `.jpeg`, `.png`, `.gif`, `.bmp`, `.svg` | Photos and graphics |
| **Raw Text** | `.txt`, `.text`, `.log` | Plain text files |
| **ZPL** | `.zpl` | Zebra Programming Language |
| **ESC/POS** | `.esc`, `.pos` | Thermal receipt commands |
| **EPL** | `.epl` | Epson Programming Language |

### Auto-Detection Examples:
```blade
<!-- These all work automatically -->
<a class="smart-print" data-url="/files/invoice.pdf">
    <!-- Auto-detected as PDF -->
</a>

<a class="smart-print" data-url="/reports/monthly.html">
    <!-- Auto-detected as HTML -->
</a>

<a class="smart-print" data-url="/labels/product.zpl">
    <!-- Auto-detected as ZPL -->
</a>
```

## 🔄 Integration Examples

### E-commerce Order Printing
```blade
<!-- Order Details Page -->
<div class="card">
    <div class="card-header">
        <h5>Order #{{ $order->number }}</h5>
    </div>
    <div class="card-body">
        <!-- Print Invoice -->
        @smartPrintButton(route('orders.invoice.pdf', $order->id), [
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-file-invoice-dollar',
            'text' => 'Print Invoice',
            'type' => 'pdf',
            'filename' => "invoice-{$order->number}.pdf"
        ])
        
        <!-- Print Receipt -->
        @smartPrintButton(route('orders.receipt.html', $order->id), [
            'class' => 'btn btn-success',
            'icon' => 'fas fa-receipt',
            'text' => 'Print Receipt',
            'type' => 'html',
            'printer' => 'EPSON TM-T88V'
        ])
        
        <!-- Print Shipping Label -->
        @smartPrintButton(route('orders.label.zpl', $order->id), [
            'class' => 'btn btn-warning',
            'icon' => 'fas fa-shipping-fast',
            'text' => 'Print Shipping Label',
            'type' => 'raw',
            'printer' => 'Zebra LP 2844',
            'copies' => 2
        ])
    </div>
</div>
```

### Laboratory Sample Labels
```blade
<!-- Laboratory Management System -->
<div class="sample-card" data-sample-id="{{ $sample->id }}">
    <h6>{{ $sample->name }}</h6>
    <p>ID: {{ $sample->code }}</p>
    
    <button class="smart-print btn btn-sm btn-info"
            data-url="{{ route('samples.label.zpl', $sample->id) }}"
            data-type="raw"
            data-printer="Zebra ZT410"
            data-silent="true"
            data-copies="3">
        <i class="fas fa-tag"></i> Print Label (3x)
    </button>
</div>
```

### POS System Receipts
```blade
<!-- Point of Sale System -->
<div class="pos-actions">
    <button class="btn btn-lg btn-success smart-print"
            data-url="{{ route('pos.receipt.escpos', $transaction->id) }}"
            data-type="raw"
            data-printer="EPSON TM-T88V"
            data-silent="false">
        <i class="fas fa-print"></i> Print Receipt
    </button>
    
    <button class="btn btn-lg btn-primary smart-print"
            data-url="{{ route('pos.receipt.pdf', $transaction->id) }}"
            data-type="pdf"
            data-filename="receipt-{{ $transaction->id }}.pdf">
        <i class="fas fa-download"></i> Email Receipt
    </button>
</div>
```

## 🚨 Troubleshooting Smart Print

### Common Issues & Solutions

#### **1. Smart Print not working**
```javascript
// Check if Smart Print is initialized
console.log('Smart Print Manager:', window.smartPrintManager);
console.log('Smart Print function:', window.smartPrint);

// Check configuration
console.log('QZ Tray Config:', window.qzTrayConfig?.smartPrint);
```

#### **2. File type not detected correctly**
```blade
<!-- Force specific type -->
<a class="smart-print" 
   data-url="/files/document"
   data-type="pdf">  <!-- Explicitly set type -->
    Print Document
</a>
```

#### **3. Fallback not working**
```javascript
// Check fallback order in config
console.log('Fallback order:', window.qzTrayConfig?.smartPrint?.fallbackOrder);

// Test individual methods
await window.smartPrintManager.printWithQz(options);
await window.smartPrintManager.printWithBrowser(options);
await window.smartPrintManager.printWithDownload(options);
```

#### **4. No visual feedback**
```css
/* Ensure CSS is loaded */
.smart-print-loading {
    opacity: 0.7;
    cursor: wait;
}

.smart-print-success {
    background-color: #28a745 !important;
}

.smart-print-error {
    background-color: #dc3545 !important;
}
```

## 📈 Performance Monitoring

### Smart Print Dashboard
```php
// Access Smart Print statistics dashboard
Route::get('/smart-print/dashboard', function() {
    $stats = QzTray::smartPrintStats();
    $recentLogs = SmartPrintLog::latest()->take(50)->get();
    
    return view('smart-print.dashboard', compact('stats', 'recentLogs'));
});
```

### Export Logs
```php
// Export Smart Print logs to CSV
Route::get('/smart-print/logs/export', function() {
    return QzTray::exportSmartPrintLogs();
});
```

## 🔄 Migration from Existing Print Solutions

### From Standard QZ Tray Usage
```javascript
// BEFORE: Manual QZ Tray implementation
async function printDocument(url) {
    try {
        await qz.websocket.connect();
        const printer = await qz.printers.getDefault();
        const config = qz.configs.create(printer);
        // ... complex print logic ...
    } catch (error) {
        // Manual fallback logic
        window.open(url, '_blank');
    }
}

// AFTER: Smart Print (one line!)
async function printDocument(url) {
    return await window.smartPrint(url);
}
```

### From Browser Print Dialogs
```javascript
// BEFORE: Browser print dialog
function printPage() {
    window.print(); // Limited control, no fallback
}

// AFTER: Smart Print with full control
function printPage() {
    return window.smartPrint(window.location.href, {
        type: 'html',
        silent: false
    });
}
```

## 🎯 Best Practices

### 1. **Always Use Smart Print Classes**
```blade
<!-- GOOD: Smart Print auto-attaches -->
<button class="smart-print" data-url="/print">Print</button>

<!-- BETTER: Use helper function -->
@smartPrintButton('/print', ['class' => 'btn btn-primary'])
```

### 2. **Set Appropriate File Types**
```blade
<!-- Always specify type when known -->
<a class="smart-print" 
   data-url="/invoice.pdf"
   data-type="pdf">  <!-- Explicit type -->
    Print Invoice
</a>
```

### 3. **Use Module-Specific Printers**
```php
// Set user preferences once
QzTray::saveUserPreference('receipts', 'EPSON TM-T88V');

// Smart Print will use it automatically
<a class="smart-print" data-url="/print/receipt">
    <!-- Uses EPSON TM-T88V automatically -->
</a>
```

### 4. **Monitor Print Statistics**
```php
// Regularly check print performance
$stats = QzTray::smartPrintStats();
if ($stats['success_rate'] < 90) {
    // Investigate printing issues
    Log::warning('Low print success rate', $stats);
}
```

## 🔮 Future Roadmap

### Planned Smart Print Features:
- **🔄 Cloud Print Relay** - Print to remote locations
- **📱 Mobile Print Support** - Print from mobile devices
- **🤖 AI-Print Optimization** - AI suggests best print methods
- **📊 Advanced Analytics** - Predictive failure analysis
- **🔗 API Webhooks** - Notify external systems
- **🎨 Template Builder** - Drag-and-drop print templates

## 🤝 Contributing

We welcome contributions to improve Smart Print technology! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Report Smart Print Issues
```bash
# Create issue on GitHub
https://github.com/bitdreamit/qz-tray-enterprise/issues

# Include Smart Print debug info
console.log('Smart Print Debug:', {
    manager: window.smartPrintManager,
    config: window.qzTrayConfig?.smartPrint,
    qzAvailable: typeof qz !== 'undefined'
});
```

## 📞 Support

### Smart Print Documentation
- **[Smart Print Guide](https://docs.bitdreamit.com/qz-tray-enterprise/smart-print)** - Complete Smart Print documentation
- **[Video Tutorials](https://youtube.com/bitdreamit/smart-print)** - Smart Print setup and usage
- **[Live Demo](https://demo.bitdreamit.com/qz-tray/smart-print)** - Try Smart Print live

### Community Support
- **[GitHub Discussions](https://github.com/bitdreamit/qz-tray-enterprise/discussions)** - Ask questions and share ideas
- **[Discord Community](https://discord.gg/bitdreamit)** - Real-time chat with developers
- **[Stack Overflow](https://stackoverflow.com/questions/tagged/qz-tray-smart-print)** - Tag: `qz-tray-smart-print`

### Professional Support
- **Email Support**: smartprint@bitdreamit.com
- **Priority Support**: Available with Premium package
- **Custom Development**: Need custom Smart Print features? Contact us!

---

## 🎯 Quick Reference Card

### Smart Print Cheat Sheet
```html
<!-- Basic Smart Print -->
<a class="smart-print" data-url="URL">Print</a>

<!-- With Options -->
<a class="smart-print"
   data-url="URL"
   data-type="pdf|html|image|raw|auto"
   data-filename="file.pdf"
   data-printer="Printer Name"
   data-copies="2"
   data-silent="true"
   data-paper-size="A4|Letter|Receipt"
   data-orientation="portrait|landscape">
    Print
</a>

<!-- Blade Helpers -->
@smartPrintButton(URL, options)
@smartPrintLink(URL, text, options)

<!-- JavaScript API -->
window.smartPrint(url, options)
window.smartPrintManager.getStats()
```

### Installation Quick Start
```bash
# 1. Install
composer require bitdreamit/qz-tray-enterprise

# 2. Install package
php artisan qz-tray:install

# 3. Run migrations (includes Smart Print logs)
php artisan migrate

# 4. Add to layout
@include('qz-tray::partials.qz-assets')

# 5. Start printing!
<a class="smart-print" data-url="/print/test">Test Print</a>
```

---

**Happy Smart Printing!** 🖨️✨

With **QZ Tray Enterprise + Smart Print**, you get the most intelligent, reliable, and easy-to-use printing solution for Laravel. Print anything, anywhere, with zero configuration hassles!
