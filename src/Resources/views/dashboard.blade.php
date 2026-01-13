<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QZ Tray Enterprise Dashboard</title>

    <!-- Self-contained styles - no external dependencies -->
    <style>
        /* CSS Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Primary Colors */
            --primary-50: #eef2ff;
            --primary-100: #e0e7ff;
            --primary-200: #c7d2fe;
            --primary-300: #a5b4fc;
            --primary-400: #818cf8;
            --primary-500: #6366f1;
            --primary-600: #4f46e5;
            --primary-700: #4338ca;
            --primary-800: #3730a3;
            --primary-900: #312e81;

            /* Secondary Colors */
            --secondary-50: #f8fafc;
            --secondary-100: #f1f5f9;
            --secondary-200: #e2e8f0;
            --secondary-300: #cbd5e1;
            --secondary-400: #94a3b8;
            --secondary-500: #64748b;
            --secondary-600: #475569;
            --secondary-700: #334155;
            --secondary-800: #1e293b;
            --secondary-900: #0f172a;

            /* Status Colors */
            --success-50: #f0fdf4;
            --success-100: #dcfce7;
            --success-200: #bbf7d0;
            --success-300: #86efac;
            --success-400: #4ade80;
            --success-500: #22c55e;
            --success-600: #16a34a;
            --success-700: #15803d;
            --success-800: #166534;
            --success-900: #14532d;

            --warning-50: #fefce8;
            --warning-100: #fef9c3;
            --warning-200: #fef08a;
            --warning-300: #fde047;
            --warning-400: #facc15;
            --warning-500: #eab308;
            --warning-600: #ca8a04;
            --warning-700: #a16207;
            --warning-800: #854d0e;
            --warning-900: #713f12;

            --danger-50: #fef2f2;
            --danger-100: #fee2e2;
            --danger-200: #fecaca;
            --danger-300: #fca5a5;
            --danger-400: #f87171;
            --danger-500: #ef4444;
            --danger-600: #dc2626;
            --danger-700: #b91c1c;
            --danger-800: #991b1b;
            --danger-900: #7f1d1d;

            --info-50: #f0f9ff;
            --info-100: #e0f2fe;
            --info-200: #bae6fd;
            --info-300: #7dd3fc;
            --info-400: #38bdf8;
            --info-500: #0ea5e9;
            --info-600: #0284c7;
            --info-700: #0369a1;
            --info-800: #075985;
            --info-900: #0c4a6e;

            /* Typography */
            --font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', sans-serif;
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;
            --font-size-2xl: 1.5rem;
            --font-size-3xl: 1.875rem;
            --font-size-4xl: 2.25rem;

            /* Spacing */
            --spacing-1: 0.25rem;
            --spacing-2: 0.5rem;
            --spacing-3: 0.75rem;
            --spacing-4: 1rem;
            --spacing-5: 1.25rem;
            --spacing-6: 1.5rem;
            --spacing-8: 2rem;
            --spacing-10: 2.5rem;
            --spacing-12: 3rem;
            --spacing-16: 4rem;

            /* Border Radius */
            --radius-sm: 0.125rem;
            --radius-md: 0.375rem;
            --radius-lg: 0.5rem;
            --radius-xl: 0.75rem;
            --radius-2xl: 1rem;
            --radius-3xl: 1.5rem;

            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);

            /* Transitions */
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-base: 300ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 500ms cubic-bezier(0.4, 0, 0.2, 1);

            /* Z-index */
            --z-dropdown: 1000;
            --z-sticky: 1020;
            --z-fixed: 1030;
            --z-modal-backdrop: 1040;
            --z-modal: 1050;
            --z-popover: 1060;
            --z-tooltip: 1070;
        }

        /* Base Styles */
        body {
            font-family: var(--font-family);
            font-size: var(--font-size-base);
            line-height: 1.5;
            color: var(--secondary-800);
            background: linear-gradient(135deg, var(--primary-50) 0%, var(--secondary-50) 100%);
            min-height: 100vh;
        }

        /* Layout */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary-800) 0%, var(--primary-900) 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform var(--transition-base);
            z-index: var(--z-fixed);
            box-shadow: var(--shadow-lg);
        }

        .sidebar-header {
            padding: var(--spacing-8) var(--spacing-6) var(--spacing-6);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-text {
            flex: 1;
        }

        .brand-name {
            font-size: var(--font-size-xl);
            font-weight: 600;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: var(--font-size-xs);
            opacity: 0.8;
        }

        .version-badge {
            background: rgba(255, 255, 255, 0.1);
            padding: var(--spacing-1) var(--spacing-3);
            border-radius: var(--radius-md);
            font-size: var(--font-size-xs);
        }

        /* Navigation */
        .nav {
            padding: var(--spacing-4) 0;
        }

        .nav-group {
            margin-bottom: var(--spacing-4);
        }

        .nav-group-label {
            padding: 0 var(--spacing-6) var(--spacing-2);
            font-size: var(--font-size-xs);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.6;
        }

        .nav-item {
            margin: 0 var(--spacing-1);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
            padding: var(--spacing-3) var(--spacing-6);
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
            margin: var(--spacing-1) var(--spacing-4);
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            font-weight: 500;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            opacity: 0.8;
        }

        .nav-text {
            flex: 1;
        }

        .nav-badge {
            background: var(--primary-500);
            color: white;
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-xs);
            font-weight: 600;
        }

        .sidebar-footer {
            padding: var(--spacing-6);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
        }

        /* Top Bar */
        .top-bar {
            height: 70px;
            background: white;
            border-bottom: 1px solid var(--secondary-200);
            padding: 0 var(--spacing-6);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: var(--z-sticky);
            box-shadow: var(--shadow-sm);
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            cursor: pointer;
            align-items: center;
            justify-content: center;
            transition: background-color var(--transition-fast);
        }

        .menu-toggle:hover {
            background: var(--secondary-100);
        }

        .menu-toggle svg {
            width: 20px;
            height: 20px;
            stroke: var(--secondary-700);
        }

        .page-title h1 {
            font-size: var(--font-size-xl);
            font-weight: 600;
            color: var(--secondary-900);
        }

        .page-title p {
            font-size: var(--font-size-sm);
            color: var(--secondary-600);
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
        }

        /* Connection Status */
        .connection-status {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            padding: var(--spacing-2) var(--spacing-3);
            background: var(--secondary-100);
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .status-connected {
            background: var(--success-500);
            animation: pulse 2s infinite;
        }

        .status-disconnected {
            background: var(--danger-500);
        }

        .status-connecting {
            background: var(--warning-500);
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Action Buttons */
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-2);
            padding: var(--spacing-2) var(--spacing-4);
            border: none;
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            text-decoration: none;
        }

        .action-btn-primary {
            background: var(--primary-600);
            color: white;
        }

        .action-btn-primary:hover {
            background: var(--primary-700);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .action-btn-secondary {
            background: var(--secondary-100);
            color: var(--secondary-700);
            border: 1px solid var(--secondary-300);
        }

        .action-btn-secondary:hover {
            background: var(--secondary-200);
        }

        .action-btn-success {
            background: var(--success-600);
            color: white;
        }

        .action-btn-success:hover {
            background: var(--success-700);
        }

        .action-btn-danger {
            background: var(--danger-600);
            color: white;
        }

        .action-btn-danger:hover {
            background: var(--danger-700);
        }

        /* Content Area */
        .content-area {
            padding: var(--spacing-6);
            min-height: calc(100vh - 70px);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-6);
            margin-bottom: var(--spacing-8);
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: var(--spacing-6);
            border: 1px solid var(--secondary-200);
            transition: all var(--transition-base);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-200);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500), var(--primary-300));
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-4);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-50);
        }

        .stat-icon svg {
            width: 24px;
            height: 24px;
            stroke: var(--primary-600);
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: var(--spacing-1);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }

        .trend-up {
            color: var(--success-600);
        }

        .trend-down {
            color: var(--danger-600);
        }

        .stat-value {
            font-size: var(--font-size-3xl);
            font-weight: 700;
            color: var(--secondary-900);
            line-height: 1;
            margin-bottom: var(--spacing-2);
        }

        .stat-label {
            font-size: var(--font-size-sm);
            color: var(--secondary-600);
            margin-bottom: var(--spacing-3);
        }

        .stat-footer {
            font-size: var(--font-size-xs);
            color: var(--secondary-500);
            padding-top: var(--spacing-3);
            border-top: 1px solid var(--secondary-200);
        }

        /* Section Header */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-6);
        }

        .section-title {
            font-size: var(--font-size-xl);
            font-weight: 600;
            color: var(--secondary-900);
        }

        .section-actions {
            display: flex;
            gap: var(--spacing-3);
        }

        /* Printer Grid */
        .printer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: var(--spacing-6);
            margin-bottom: var(--spacing-8);
        }

        .printer-card {
            background: white;
            border-radius: var(--radius-xl);
            border: 1px solid var(--secondary-200);
            overflow: hidden;
            transition: all var(--transition-base);
        }

        .printer-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-300);
        }

        .printer-card-header {
            padding: var(--spacing-4);
            background: linear-gradient(90deg, var(--primary-600), var(--primary-400));
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .printer-name {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            font-weight: 600;
        }

        .printer-status {
            padding: var(--spacing-1) var(--spacing-3);
            border-radius: var(--radius-md);
            font-size: var(--font-size-xs);
            font-weight: 600;
            text-transform: uppercase;
        }

        .printer-status-online {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .printer-status-offline {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }

        .printer-card-body {
            padding: var(--spacing-4);
        }

        .printer-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--spacing-3);
            margin-bottom: var(--spacing-4);
        }

        .printer-info-item {
            display: flex;
            flex-direction: column;
        }

        .printer-info-label {
            font-size: var(--font-size-xs);
            color: var(--secondary-600);
            margin-bottom: var(--spacing-1);
        }

        .printer-info-value {
            font-size: var(--font-size-sm);
            font-weight: 500;
            color: var(--secondary-800);
        }

        .printer-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--spacing-2);
        }

        .printer-btn {
            padding: var(--spacing-2);
            border: none;
            border-radius: var(--radius-md);
            font-size: var(--font-size-xs);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-1);
        }

        .printer-btn-select {
            background: var(--primary-100);
            color: var(--primary-700);
        }

        .printer-btn-select:hover {
            background: var(--primary-200);
        }

        .printer-btn-test {
            background: var(--success-100);
            color: var(--success-700);
        }

        .printer-btn-test:hover {
            background: var(--success-200);
        }

        .printer-btn-info {
            background: var(--secondary-100);
            color: var(--secondary-700);
        }

        .printer-btn-info:hover {
            background: var(--secondary-200);
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: var(--radius-xl);
            border: 1px solid var(--secondary-200);
            overflow: hidden;
            margin-bottom: var(--spacing-8);
        }

        .table-header {
            padding: var(--spacing-4);
            border-bottom: 1px solid var(--secondary-200);
            background: var(--secondary-50);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead th {
            padding: var(--spacing-3) var(--spacing-4);
            text-align: left;
            font-size: var(--font-size-xs);
            font-weight: 600;
            color: var(--secondary-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--secondary-50);
            border-bottom: 1px solid var(--secondary-200);
        }

        .table tbody tr {
            transition: background-color var(--transition-fast);
        }

        .table tbody tr:hover {
            background: var(--secondary-50);
        }

        .table tbody td {
            padding: var(--spacing-3) var(--spacing-4);
            border-bottom: 1px solid var(--secondary-200);
            font-size: var(--font-size-sm);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: var(--spacing-1) var(--spacing-3);
            border-radius: var(--radius-md);
            font-size: var(--font-size-xs);
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-completed {
            background: var(--success-100);
            color: var(--success-700);
        }

        .status-processing {
            background: var(--info-100);
            color: var(--info-700);
        }

        .status-failed {
            background: var(--danger-100);
            color: var(--danger-700);
        }

        .status-queued {
            background: var(--warning-100);
            color: var(--warning-700);
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: var(--z-modal-backdrop);
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            display: flex;
            animation: fadeIn var(--transition-base);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal {
            background: white;
            border-radius: var(--radius-xl);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            animation: slideUp var(--transition-base);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: var(--spacing-4) var(--spacing-6);
            background: var(--primary-600);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: var(--font-size-lg);
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: var(--radius-md);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color var(--transition-fast);
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .modal-body {
            padding: var(--spacing-6);
            max-height: calc(90vh - 73px);
            overflow-y: auto;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: var(--spacing-6);
            right: var(--spacing-6);
            z-index: var(--z-tooltip);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-3);
            max-width: 400px;
        }

        .toast {
            padding: var(--spacing-4);
            border-radius: var(--radius-lg);
            background: white;
            box-shadow: var(--shadow-lg);
            border-left: 4px solid var(--primary-500);
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-3);
            animation: slideInRight var(--transition-base);
            transform-origin: top right;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .toast-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            margin-bottom: var(--spacing-1);
            color: var(--secondary-900);
        }

        .toast-message {
            font-size: var(--font-size-sm);
            color: var(--secondary-700);
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--secondary-500);
            cursor: pointer;
            width: 24px;
            height: 24px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color var(--transition-fast);
        }

        .toast-close:hover {
            background: var(--secondary-100);
        }

        .toast-success {
            border-left-color: var(--success-500);
        }

        .toast-error {
            border-left-color: var(--danger-500);
        }

        .toast-warning {
            border-left-color: var(--warning-500);
        }

        .toast-info {
            border-left-color: var(--info-500);
        }

        /* Loading States */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-8);
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--secondary-200);
            border-top-color: var(--primary-500);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: flex;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .printer-grid {
                grid-template-columns: 1fr;
            }

            .content-area {
                padding: var(--spacing-4);
            }

            .top-bar {
                padding: 0 var(--spacing-4);
            }

            .printer-actions {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-3);
            }

            .section-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }

        /* Utility Classes */
        .hidden {
            display: none !important;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: var(--secondary-600);
        }

        .mt-1 { margin-top: var(--spacing-1); }
        .mt-2 { margin-top: var(--spacing-2); }
        .mt-3 { margin-top: var(--spacing-3); }
        .mt-4 { margin-top: var(--spacing-4); }
        .mt-6 { margin-top: var(--spacing-6); }
        .mt-8 { margin-top: var(--spacing-8); }

        .mb-1 { margin-bottom: var(--spacing-1); }
        .mb-2 { margin-bottom: var(--spacing-2); }
        .mb-3 { margin-bottom: var(--spacing-3); }
        .mb-4 { margin-bottom: var(--spacing-4); }
        .mb-6 { margin-bottom: var(--spacing-6); }
        .mb-8 { margin-bottom: var(--spacing-8); }

        .ml-auto {
            margin-left: auto;
        }

        .w-full {
            width: 100%;
        }

        .gap-2 { gap: var(--spacing-2); }
        .gap-3 { gap: var(--spacing-3); }
        .gap-4 { gap: var(--spacing-4); }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--secondary-100);
            border-radius: var(--radius-md);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-400);
            border-radius: var(--radius-md);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-500);
        }
    </style>
</head>
<body>
<div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-logo">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                    </svg>
                </div>
                <div class="brand-text">
                    <div class="brand-name">QZ Tray</div>
                    <div class="brand-subtitle">Enterprise Edition</div>
                </div>
                <div class="version-badge">v2.2.5</div>
            </div>
        </div>

        <nav class="nav">
            <div class="nav-group">
                <div class="nav-group-label">Main</div>
                <div class="nav-item">
                    <a href="#" class="nav-link active" data-tab="dashboard">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-tab="printers">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                        </svg>
                        <span class="nav-text">Printers</span>
                        <span class="nav-badge" id="printer-count">0</span>
                    </a>
                </div>
            </div>

            <div class="nav-group">
                <div class="nav-group-label">Print Jobs</div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-tab="print">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                        </svg>
                        <span class="nav-text">Print Jobs</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-tab="queue">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        <span class="nav-text">Print Queue</span>
                        <span class="nav-badge" id="queue-count">0</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-tab="history">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="nav-text">Job History</span>
                    </a>
                </div>
            </div>

            <div class="nav-group">
                <div class="nav-group-label">System</div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-tab="diagnostics">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="nav-text">Diagnostics</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-tab="settings">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="nav-text">Settings</span>
                    </a>
                </div>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div id="qz-connection-status" class="connection-status">
                <span class="status-indicator status-connecting"></span>
                <span>Connecting...</span>
            </div>
            <button class="action-btn action-btn-primary w-full mt-4" onclick="connectQzTray()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Connect QZ Tray
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="page-title">
                    <h1>QZ Tray Dashboard</h1>
                    <p>Manage printers and print jobs</p>
                </div>
            </div>

            <div class="top-bar-right">
                <div class="connection-status">
                    <span class="status-indicator status-connecting"></span>
                    <span>Initializing...</span>
                </div>
                <button class="action-btn action-btn-secondary" onclick="runDiagnostics()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Diagnostics
                </button>
                <button class="action-btn action-btn-success" onclick="testPrint()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                    </svg>
                    Test Print
                </button>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Dashboard Tab -->
            <div id="dashboard-tab" class="tab-content active">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                                </svg>
                            </div>
                            <div class="stat-trend trend-up">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                </svg>
                                Updated
                            </div>
                        </div>
                        <div class="stat-value" id="stat-printers">0</div>
                        <div class="stat-label">Printers Available</div>
                        <div class="stat-footer">Auto-discovery enabled</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="stat-trend trend-up">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                </svg>
                                12%
                            </div>
                        </div>
                        <div class="stat-value" id="stat-jobs">0</div>
                        <div class="stat-label">Jobs Today</div>
                        <div class="stat-footer">Compared to yesterday</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="stat-trend trend-down">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                </svg>
                                3
                            </div>
                        </div>
                        <div class="stat-value" id="stat-queue">0</div>
                        <div class="stat-label">In Queue</div>
                        <div class="stat-footer">From last hour</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.698-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div class="stat-trend trend-down">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                </svg>
                                50%
                            </div>
                        </div>
                        <div class="stat-value" id="stat-errors">0</div>
                        <div class="stat-label">Errors Today</div>
                        <div class="stat-footer">From yesterday</div>
                    </div>
                </div>

                <!-- Printer List -->
                <div class="section-header">
                    <h2 class="section-title">Available Printers</h2>
                    <div class="section-actions">
                        <button class="action-btn action-btn-secondary" onclick="refreshPrinters()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                        <button class="action-btn action-btn-primary" onclick="showAddPrinterModal()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Printer
                        </button>
                    </div>
                </div>

                <div class="printer-grid" id="printer-list">
                    <div class="text-center">
                        <div class="spinner"></div>
                        <p class="text-muted mt-4">Discovering printers...</p>
                    </div>
                </div>

                <!-- Recent Jobs -->
                <div class="section-header">
                    <h2 class="section-title">Recent Print Jobs</h2>
                    <button class="action-btn action-btn-secondary" onclick="refreshJobs()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Refresh
                    </button>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3>Last 10 Jobs</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Job ID</th>
                                <th>Printer</th>
                                <th>Document</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="recent-jobs-table">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner"></div>
                                    <p class="text-muted mt-4">Loading recent jobs...</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Other Tabs (Initially hidden) -->
            <div id="printers-tab" class="tab-content hidden">
                <div class="section-header">
                    <h2 class="section-title">Printer Management</h2>
                    <div class="section-actions">
                        <button class="action-btn action-btn-primary" onclick="discoverPrinters()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Discover Printers
                        </button>
                    </div>
                </div>
                <div id="printers-management-content">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>

            <div id="print-tab" class="tab-content hidden">
                <div class="section-header">
                    <h2 class="section-title">Print Jobs</h2>
                    <div class="section-actions">
                        <button class="action-btn action-btn-primary" onclick="showPrintModal()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                            </svg>
                            New Print Job
                        </button>
                    </div>
                </div>
                <div id="print-jobs-content">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>

            <div id="queue-tab" class="tab-content hidden">
                <div class="section-header">
                    <h2 class="section-title">Print Queue</h2>
                    <div class="section-actions">
                        <button class="action-btn action-btn-danger" onclick="clearQueue()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Clear Queue
                        </button>
                    </div>
                </div>
                <div id="queue-content">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>

            <div id="history-tab" class="tab-content hidden">
                <div class="section-header">
                    <h2 class="section-title">Job History</h2>
                    <div class="section-actions">
                        <button class="action-btn action-btn-secondary" onclick="exportHistory()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export
                        </button>
                    </div>
                </div>
                <div id="history-content">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>

            <div id="diagnostics-tab" class="tab-content hidden">
                <div class="section-header">
                    <h2 class="section-title">System Diagnostics</h2>
                    <div class="section-actions">
                        <button class="action-btn action-btn-primary" onclick="runFullDiagnostics()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Run Diagnostics
                        </button>
                    </div>
                </div>
                <div id="diagnostics-content">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>

            <div id="settings-tab" class="tab-content hidden">
                <div class="section-header">
                    <h2 class="section-title">Settings</h2>
                    <div class="section-actions">
                        <button class="action-btn action-btn-primary" onclick="saveSettings()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </div>
                <div id="settings-content">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Modals -->
<div class="modal-overlay" id="printerModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Printer Details</div>
            <button class="modal-close" onclick="closeModal('printerModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="printerModalBody">
            <!-- Dynamic content -->
        </div>
    </div>
</div>

<div class="modal-overlay" id="printModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Print Document</div>
            <button class="modal-close" onclick="closeModal('printModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="printModalBody">
            <!-- Dynamic content -->
        </div>
    </div>
</div>

<div class="modal-overlay" id="jobModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Job Details</div>
            <button class="modal-close" onclick="closeModal('jobModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="jobModalBody">
            <!-- Dynamic content -->
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    // Global state
    const QZ_DASHBOARD = {
        state: {
            connected: false,
            printers: [],
            jobs: [],
            queue: [],
            currentTab: 'dashboard',
            selectedPrinter: null
        },
        config: {
            autoConnect: true,
            discoveryInterval: 30000,
            maxPrinters: 50,
            maxJobs: 100
        }
    };

    // Toast System
    class Toast {
        static show(message, type = 'info', title = '') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                    <svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        ${this.getIcon(type)}
                    </svg>
                    <div class="toast-content">
                        ${title ? `<div class="toast-title">${title}</div>` : ''}
                        <div class="toast-message">${message}</div>
                    </div>
                    <button class="toast-close" onclick="this.parentElement.remove()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                `;

            container.appendChild(toast);
            setTimeout(() => toast.remove(), 5000);
        }

        static getIcon(type) {
            const icons = {
                success: '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                error: '<path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                warning: '<path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.698-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>',
                info: '<path d="M13 16h-1v-4h1m0-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            };
            return icons[type] || icons.info;
        }
    }

    // Modal System
    function showModal(modalId, content = '') {
        const modal = document.getElementById(modalId);
        const body = document.getElementById(modalId + 'Body');
        if (body && content) {
            body.innerHTML = content;
        }
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Tab Navigation
    function setupTabs() {
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Update active nav item
                document.querySelectorAll('.nav-link').forEach(item => {
                    item.classList.remove('active');
                });
                this.classList.add('active');

                // Show corresponding tab
                const tabId = this.dataset.tab;
                QZ_DASHBOARD.state.currentTab = tabId;

                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.add('hidden');
                    tab.classList.remove('active');
                });

                const tabElement = document.getElementById(tabId + '-tab');
                if (tabElement) {
                    tabElement.classList.remove('hidden');
                    tabElement.classList.add('active');
                }

                // Update page title
                const tabTitles = {
                    dashboard: 'Dashboard',
                    printers: 'Printers',
                    print: 'Print Jobs',
                    queue: 'Print Queue',
                    history: 'Job History',
                    diagnostics: 'Diagnostics',
                    settings: 'Settings'
                };

                document.querySelector('.page-title h1').textContent =
                    `QZ Tray - ${tabTitles[tabId] || tabId}`;

                // Load tab-specific data
                loadTabData(tabId);
            });
        });
    }

    // Status Management
    function updateConnectionStatus(status, message = '') {
        const indicators = document.querySelectorAll('.status-indicator');
        const statusTexts = document.querySelectorAll('.connection-status span:last-child');

        indicators.forEach(indicator => {
            indicator.className = 'status-indicator';
            indicator.classList.add(`status-${status}`);
        });

        const statusMessages = {
            connected: 'Connected to QZ Tray',
            disconnected: 'Disconnected',
            connecting: 'Connecting...'
        };

        statusTexts.forEach(text => {
            text.textContent = message || statusMessages[status] || status;
        });

        QZ_DASHBOARD.state.connected = status === 'connected';

        if (status === 'connected') {
            Toast.show('Connected to QZ Tray', 'success');
            loadPrinters();
            loadRecentJobs();
        } else if (status === 'disconnected') {
            Toast.show('Disconnected from QZ Tray', 'warning');
        }
    }

    // Printer Management
    async function loadPrinters() {
        const printerList = document.getElementById('printer-list');
        const printerCount = document.getElementById('printer-count');

        try {
            // Simulate API call
            const printers = await simulateApiCall([
                {
                    id: 1,
                    name: 'EPSON TM-T88V',
                    status: 'online',
                    type: 'Receipt',
                    default: true,
                    connection: 'USB',
                    location: 'Front Desk',
                    lastUsed: '2 minutes ago'
                },
                {
                    id: 2,
                    name: 'Zebra ZT230',
                    status: 'online',
                    type: 'Label',
                    default: false,
                    connection: 'Network',
                    location: 'Warehouse',
                    lastUsed: '5 minutes ago'
                },
                {
                    id: 3,
                    name: 'HP LaserJet Pro',
                    status: 'offline',
                    type: 'Document',
                    default: false,
                    connection: 'Network',
                    location: 'Office',
                    lastUsed: '1 hour ago'
                },
                {
                    id: 4,
                    name: 'Brother QL-800',
                    status: 'online',
                    type: 'Label',
                    default: false,
                    connection: 'USB',
                    location: 'Shipping',
                    lastUsed: 'Just now'
                }
            ]);

            QZ_DASHBOARD.state.printers = printers;
            renderPrinters(printers);
            printerCount.textContent = printers.length;
            document.getElementById('stat-printers').textContent = printers.length;

        } catch (error) {
            console.error('Failed to load printers:', error);
            printerList.innerHTML = `
                    <div class="text-center">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--danger-500);">
                            <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-muted mt-4">Failed to load printers</p>
                        <button class="action-btn action-btn-secondary mt-4" onclick="loadPrinters()">
                            Retry
                        </button>
                    </div>
                `;
            Toast.show('Failed to load printers', 'error');
        }
    }

    function renderPrinters(printers) {
        const container = document.getElementById('printer-list');

        if (!printers || printers.length === 0) {
            container.innerHTML = `
                    <div class="text-center">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--secondary-400);">
                            <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                        </svg>
                        <p class="text-muted mt-4">No printers found</p>
                        <button class="action-btn action-btn-primary mt-4" onclick="discoverPrinters()">
                            Discover Printers
                        </button>
                    </div>
                `;
            return;
        }

        let html = '';

        printers.forEach(printer => {
            const statusClass = printer.status === 'online' ? 'printer-status-online' : 'printer-status-offline';
            const statusText = printer.status === 'online' ? 'Online' : 'Offline';

            html += `
                    <div class="printer-card">
                        <div class="printer-card-header">
                            <div class="printer-name">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                                </svg>
                                ${printer.name}
                            </div>
                            <span class="printer-status ${statusClass}">${statusText}</span>
                        </div>
                        <div class="printer-card-body">
                            <div class="printer-info">
                                <div class="printer-info-item">
                                    <span class="printer-info-label">Type</span>
                                    <span class="printer-info-value">${printer.type}</span>
                                </div>
                                <div class="printer-info-item">
                                    <span class="printer-info-label">Connection</span>
                                    <span class="printer-info-value">${printer.connection}</span>
                                </div>
                                <div class="printer-info-item">
                                    <span class="printer-info-label">Location</span>
                                    <span class="printer-info-value">${printer.location}</span>
                                </div>
                                <div class="printer-info-item">
                                    <span class="printer-info-label">Last Used</span>
                                    <span class="printer-info-value">${printer.lastUsed}</span>
                                </div>
                            </div>
                            <div class="printer-actions">
                                <button class="printer-btn printer-btn-select" onclick="selectPrinter(${printer.id})">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Select
                                </button>
                                <button class="printer-btn printer-btn-test" onclick="testPrinter(${printer.id})">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                                    </svg>
                                    Test
                                </button>
                                <button class="printer-btn printer-btn-info" onclick="showPrinterDetails(${printer.id})">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M13 16h-1v-4h1m0-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Details
                                </button>
                            </div>
                        </div>
                    </div>
                `;
        });

        container.innerHTML = html;
    }

    // Jobs Management
    async function loadRecentJobs() {
        const jobsTable = document.getElementById('recent-jobs-table');

        try {
            // Simulate API call
            const jobs = await simulateApiCall([
                {
                    id: 'JOB-001',
                    printer: 'EPSON TM-T88V',
                    document: 'Receipt #12345',
                    status: 'completed',
                    created: '2 minutes ago',
                    pages: 1,
                    copies: 1
                },
                {
                    id: 'JOB-002',
                    printer: 'Zebra ZT230',
                    document: 'Shipping Label',
                    status: 'processing',
                    created: '5 minutes ago',
                    pages: 1,
                    copies: 1
                },
                {
                    id: 'JOB-003',
                    printer: 'HP LaserJet Pro',
                    document: 'Invoice #67890',
                    status: 'failed',
                    created: '10 minutes ago',
                    pages: 3,
                    copies: 1
                },
                {
                    id: 'JOB-004',
                    printer: 'EPSON TM-T88V',
                    document: 'Receipt #12346',
                    status: 'queued',
                    created: '15 minutes ago',
                    pages: 1,
                    copies: 2
                }
            ]);

            QZ_DASHBOARD.state.jobs = jobs;
            renderRecentJobs(jobs);
            document.getElementById('stat-jobs').textContent = jobs.length;
            document.getElementById('queue-count').textContent = jobs.filter(j => j.status === 'queued').length;

        } catch (error) {
            console.error('Failed to load jobs:', error);
            jobsTable.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--danger-500);">
                                <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-muted mt-4">Failed to load jobs</p>
                        </td>
                    </tr>
                `;
            Toast.show('Failed to load recent jobs', 'error');
        }
    }

    function renderRecentJobs(jobs) {
        const tbody = document.getElementById('recent-jobs-table');

        if (!jobs || jobs.length === 0) {
            tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--secondary-400);">
                                <path d="M9 12H5a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V12a2 2 0 01-2 2h-4m-6 0v4a2 2 0 002 2h4m-6-4h6"/>
                            </svg>
                            <p class="text-muted mt-4">No recent jobs</p>
                        </td>
                    </tr>
                `;
            return;
        }

        let html = '';

        jobs.forEach(job => {
            const statusClass = `status-${job.status}`;
            const statusText = job.status.charAt(0).toUpperCase() + job.status.slice(1);

            html += `
                    <tr>
                        <td><code>${job.id}</code></td>
                        <td>${job.printer}</td>
                        <td>${job.document}</td>
                        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                        <td>${job.created}</td>
                        <td>
                            <button class="action-btn action-btn-secondary" onclick="viewJobDetails('${job.id}')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View
                            </button>
                        </td>
                    </tr>
                `;
        });

        tbody.innerHTML = html;
    }

    // Tab Data Loading
    function loadTabData(tabId) {
        switch(tabId) {
            case 'dashboard':
                loadPrinters();
                loadRecentJobs();
                break;
            case 'printers':
                loadPrinterManagement();
                break;
            case 'print':
                loadPrintJobs();
                break;
            case 'queue':
                loadQueue();
                break;
            case 'history':
                loadHistory();
                break;
            case 'diagnostics':
                loadDiagnostics();
                break;
            case 'settings':
                loadSettings();
                break;
        }
    }

    // UI Functions
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    }

    function refreshPrinters() {
        Toast.show('Refreshing printers...', 'info');
        loadPrinters();
    }

    function refreshJobs() {
        Toast.show('Refreshing jobs...', 'info');
        loadRecentJobs();
    }

    function connectQzTray() {
        Toast.show('Connecting to QZ Tray...', 'info');
        updateConnectionStatus('connecting');

        // Simulate connection
        setTimeout(() => {
            updateConnectionStatus('connected', 'Connected to QZ Tray v2.2.5');
        }, 2000);
    }

    function discoverPrinters() {
        Toast.show('Discovering printers...', 'info');

        // Simulate discovery
        setTimeout(() => {
            loadPrinters();
            Toast.show('Found 4 printers', 'success');
        }, 1500);
    }

    function selectPrinter(printerId) {
        const printer = QZ_DASHBOARD.state.printers.find(p => p.id === printerId);
        if (printer) {
            QZ_DASHBOARD.state.selectedPrinter = printer;
            Toast.show(`Selected printer: ${printer.name}`, 'success');
        }
    }

    function testPrinter(printerId) {
        const printer = QZ_DASHBOARD.state.printers.find(p => p.id === printerId);
        if (printer) {
            Toast.show(`Sending test print to ${printer.name}...`, 'info');

            // Simulate test print
            setTimeout(() => {
                Toast.show(`Test print sent to ${printer.name}`, 'success');
            }, 1500);
        }
    }

    function showPrinterDetails(printerId) {
        const printer = QZ_DASHBOARD.state.printers.find(p => p.id === printerId);
        if (printer) {
            const content = `
                    <h3>${printer.name}</h3>
                    <div class="printer-info mt-4">
                        <div class="printer-info-item">
                            <span class="printer-info-label">Status</span>
                            <span class="printer-info-value">
                                <span class="status-badge status-${printer.status}">
                                    ${printer.status === 'online' ? 'Online' : 'Offline'}
                                </span>
                            </span>
                        </div>
                        <div class="printer-info-item">
                            <span class="printer-info-label">Type</span>
                            <span class="printer-info-value">${printer.type}</span>
                        </div>
                        <div class="printer-info-item">
                            <span class="printer-info-label">Connection</span>
                            <span class="printer-info-value">${printer.connection}</span>
                        </div>
                        <div class="printer-info-item">
                            <span class="printer-info-label">Location</span>
                            <span class="printer-info-value">${printer.location}</span>
                        </div>
                        <div class="printer-info-item">
                            <span class="printer-info-label">Default</span>
                            <span class="printer-info-value">${printer.default ? 'Yes' : 'No'}</span>
                        </div>
                        <div class="printer-info-item">
                            <span class="printer-info-label">Last Used</span>
                            <span class="printer-info-value">${printer.lastUsed}</span>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button class="action-btn action-btn-success w-full" onclick="testPrinter(${printer.id}); closeModal('printerModal')">
                            Test Print
                        </button>
                    </div>
                `;
            showModal('printerModal', content);
        }
    }

    function viewJobDetails(jobId) {
        const job = QZ_DASHBOARD.state.jobs.find(j => j.id === jobId);
        if (job) {
            const content = `
                    <h3>Job ${job.id}</h3>
                    <div class="printer-info mt-4">
                        <div class="printer-info-item">
                            <span class="printer-info-label">Printer</span>
                            <span class="printer-info-value">${job.printer}</span>
                        </div>
                        <div class="printer-info-item">
                            <span class="printer-info-label">Document</span>
                            <span class="printer-info-value">${job.document}</span>
                        </div>
                        <div class="printer-info-item">
                            <span class="printer-info-label">Status</span>
                            <span class="printer-info-value">
                                <span class="status-badge status-${job.status}">
                                    ${job.status.charAt(0).toUpperCase() + job.status.slice(1)}
                                </span>
                            </span>
                        </div>
                        <div class="printer-info-item">
                            <span class="printer-info-label">Created</span>
                            <span class="printer-info-value">${job.created}</span>
                        </div>
                        <div class="printer-info-item">
                            <span class="printer-info-label">Pages</span>
                            <span class="printer-info-value">${job.pages}</span>
                        </div>
                        <div class="printer-info-item">
                            <span class="printer-info-label">Copies</span>
                            <span class="printer-info-value">${job.copies}</span>
                        </div>
                    </div>
                `;
            showModal('jobModal', content);
        }
    }

    function showAddPrinterModal() {
        const content = `
                <h3>Add New Printer</h3>
                <p class="text-muted mt-2">Configure a new printer connection</p>

                <div class="mt-6">
                    <div class="mb-4">
                        <label class="printer-info-label">Printer Name</label>
                        <input type="text" class="w-full" style="padding: var(--spacing-2); border: 1px solid var(--secondary-300); border-radius: var(--radius-md);"
                               placeholder="Enter printer name" id="newPrinterName">
                    </div>

                    <div class="mb-4">
                        <label class="printer-info-label">Connection Type</label>
                        <select class="w-full" style="padding: var(--spacing-2); border: 1px solid var(--secondary-300); border-radius: var(--radius-md);"
                                id="newPrinterConnection">
                            <option value="network">Network</option>
                            <option value="usb">USB</option>
                            <option value="bluetooth">Bluetooth</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="printer-info-label">IP Address / Hostname</label>
                        <input type="text" class="w-full" style="padding: var(--spacing-2); border: 1px solid var(--secondary-300); border-radius: var(--radius-md);"
                               placeholder="192.168.1.100" id="newPrinterAddress">
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button class="action-btn action-btn-secondary flex-1" onclick="closeModal('printerModal')">
                            Cancel
                        </button>
                        <button class="action-btn action-btn-primary flex-1" onclick="addPrinter()">
                            Add Printer
                        </button>
                    </div>
                </div>
            `;
        showModal('printerModal', content);
    }

    function addPrinter() {
        const name = document.getElementById('newPrinterName')?.value;
        const connection = document.getElementById('newPrinterConnection')?.value;
        const address = document.getElementById('newPrinterAddress')?.value;

        if (!name || !address) {
            Toast.show('Please fill all required fields', 'error');
            return;
        }

        // Simulate adding printer
        Toast.show(`Adding printer ${name}...`, 'info');

        setTimeout(() => {
            closeModal('printerModal');
            Toast.show(`Printer ${name} added successfully`, 'success');
            loadPrinters();
        }, 1000);
    }

    function showPrintModal() {
        const content = `
                <h3>Print Document</h3>
                <p class="text-muted mt-2">Send a document to the selected printer</p>

                <div class="mt-6">
                    <div class="mb-4">
                        <label class="printer-info-label">Select Printer</label>
                        <select class="w-full" style="padding: var(--spacing-2); border: 1px solid var(--secondary-300); border-radius: var(--radius-md);"
                                id="printPrinter">
                            ${QZ_DASHBOARD.state.printers.map(p => `
                                <option value="${p.id}" ${p.default ? 'selected' : ''}>${p.name}</option>
                            `).join('')}
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="printer-info-label">Document Type</label>
                        <select class="w-full" style="padding: var(--spacing-2); border: 1px solid var(--secondary-300); border-radius: var(--radius-md);"
                                id="printType">
                            <option value="receipt">Receipt</option>
                            <option value="label">Label</option>
                            <option value="document">Document</option>
                            <option value="invoice">Invoice</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="printer-info-label">Copies</label>
                        <input type="number" min="1" max="99" value="1" class="w-full"
                               style="padding: var(--spacing-2); border: 1px solid var(--secondary-300); border-radius: var(--radius-md);"
                               id="printCopies">
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button class="action-btn action-btn-secondary flex-1" onclick="closeModal('printModal')">
                            Cancel
                        </button>
                        <button class="action-btn action-btn-primary flex-1" onclick="submitPrintJob()">
                            Print
                        </button>
                    </div>
                </div>
            `;
        showModal('printModal', content);
    }

    function submitPrintJob() {
        const printerId = document.getElementById('printPrinter')?.value;
        const type = document.getElementById('printType')?.value;
        const copies = document.getElementById('printCopies')?.value || 1;

        const printer = QZ_DASHBOARD.state.printers.find(p => p.id == printerId);

        Toast.show(`Sending print job to ${printer?.name || 'printer'}...`, 'info');

        setTimeout(() => {
            closeModal('printModal');
            Toast.show('Print job submitted successfully', 'success');
            loadRecentJobs();
        }, 1500);
    }

    function testPrint() {
        if (!QZ_DASHBOARD.state.selectedPrinter) {
            Toast.show('Please select a printer first', 'warning');
            return;
        }

        Toast.show(`Sending test print to ${QZ_DASHBOARD.state.selectedPrinter.name}...`, 'info');

        setTimeout(() => {
            Toast.show('Test print completed successfully', 'success');
        }, 2000);
    }

    function runDiagnostics() {
        Toast.show('Running system diagnostics...', 'info');

        // Simulate diagnostics
        setTimeout(() => {
            const errors = Math.floor(Math.random() * 3);
            const warnings = Math.floor(Math.random() * 5);

            let message = 'System diagnostics completed. ';
            if (errors === 0 && warnings === 0) {
                message += 'All systems are functioning normally.';
                Toast.show(message, 'success');
            } else {
                message += `Found ${errors} error(s) and ${warnings} warning(s).`;
                Toast.show(message, errors > 0 ? 'error' : 'warning');
            }

            document.getElementById('stat-errors').textContent = errors;
        }, 3000);
    }

    // Tab-specific load functions (simplified)
    async function loadPrinterManagement() {
        const content = await simulateApiCall('<p>Printer management content will be loaded here</p>');
        document.getElementById('printers-management-content').innerHTML = content;
    }

    async function loadPrintJobs() {
        const content = await simulateApiCall('<p>Print jobs content will be loaded here</p>');
        document.getElementById('print-jobs-content').innerHTML = content;
    }

    async function loadQueue() {
        const content = await simulateApiCall('<p>Queue content will be loaded here</p>');
        document.getElementById('queue-content').innerHTML = content;
    }

    async function loadHistory() {
        const content = await simulateApiCall('<p>History content will be loaded here</p>');
        document.getElementById('history-content').innerHTML = content;
    }

    async function loadDiagnostics() {
        const content = await simulateApiCall('<p>Diagnostics content will be loaded here</p>');
        document.getElementById('diagnostics-content').innerHTML = content;
    }

    async function loadSettings() {
        const content = await simulateApiCall('<p>Settings content will be loaded here</p>');
        document.getElementById('settings-content').innerHTML = content;
    }

    // Utility functions
    function clearQueue() {
        Toast.show('Clearing print queue...', 'info');
        setTimeout(() => {
            Toast.show('Print queue cleared', 'success');
            document.getElementById('stat-queue').textContent = '0';
            document.getElementById('queue-count').textContent = '0';
        }, 1000);
    }

    function exportHistory() {
        Toast.show('Exporting job history...', 'info');
        setTimeout(() => {
            Toast.show('Job history exported successfully', 'success');
        }, 2000);
    }

    function runFullDiagnostics() {
        Toast.show('Running full system diagnostics...', 'info');
        setTimeout(() => {
            Toast.show('Full diagnostics completed. Report generated.', 'success');
        }, 4000);
    }

    function saveSettings() {
        Toast.show('Saving settings...', 'info');
        setTimeout(() => {
            Toast.show('Settings saved successfully', 'success');
        }, 1500);
    }

    // Simulate API calls
    function simulateApiCall(data, delay = 1000) {
        return new Promise((resolve) => {
            setTimeout(() => resolve(data), delay);
        });
    }

    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
        setupTabs();
        updateConnectionStatus('connecting');

        // Simulate initial loading
        setTimeout(() => {
            updateConnectionStatus('connected', 'Connected to QZ Tray v2.2.5');
            loadPrinters();
            loadRecentJobs();
        }, 2000);

        // Close modals on overlay click
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');

        if (window.innerWidth <= 1024 &&
            sidebar.classList.contains('active') &&
            !sidebar.contains(e.target) &&
            !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });
</script>
</body>
</html>
