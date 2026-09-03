<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

function canAccess($moduleName) {
    if ($_SESSION['role'] === 'Admin') return true;
    $perms = $_SESSION['permissions']['modules'] ?? [];
    return in_array($moduleName, $perms);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MDI AIMS System</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Chart.js for Executive Dashboard Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* ==========================================
           BRAND COLOR PALETTE (80% Green / 20% Red)
           ========================================== */
        :root {
             --theme-green: #2e7d32;
             --theme-green-dark: #1b5e20;
             --theme-green-light: #e8f5e9;
             --theme-red: #d32f2f;
             --theme-red-dark: #b71c1c;
             --theme-red-light: #ffebee;
             --theme-dark: #212529;
             --odoo-bg: #f4f6f8;
        }
        
        body { background-color: var(--odoo-bg); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: var(--theme-dark); }
        
        /* Force Primary & Success to Green */
        .text-primary, .text-success, .text-info { color: var(--theme-green) !important; }
        .bg-primary, .bg-success, .bg-info { background-color: var(--theme-green) !important; color: white !important; }
        .btn-primary, .btn-success, .btn-info { background-color: var(--theme-green) !important; border-color: var(--theme-green) !important; color: white !important; text-transform: uppercase; font-weight: 600; }
        .btn-primary:hover, .btn-success:hover, .btn-info:hover { background-color: var(--theme-green-dark) !important; border-color: var(--theme-green-dark) !important; }
        .btn-outline-primary, .btn-outline-success { color: var(--theme-green) !important; border-color: var(--theme-green) !important; }
        .btn-outline-primary:hover, .btn-outline-success:hover { background-color: var(--theme-green) !important; color: white !important; }
        .border-primary, .border-success { border-color: var(--theme-green) !important; }

        /* Force Danger & Warning to Red */
        .text-danger, .text-warning { color: var(--theme-red) !important; }
        .bg-danger, .bg-warning { background-color: var(--theme-red) !important; color: white !important; }
        .btn-danger, .btn-warning { background-color: var(--theme-red) !important; border-color: var(--theme-red) !important; color: white !important; text-transform: uppercase; font-weight: 600; }
        .btn-danger:hover, .btn-warning:hover { background-color: var(--theme-red-dark) !important; border-color: var(--theme-red-dark) !important; }
        .btn-outline-danger { color: var(--theme-red) !important; border-color: var(--theme-red) !important; }
        .btn-outline-danger:hover { background-color: var(--theme-red) !important; color: white !important; }
        .border-danger { border-color: var(--theme-red) !important; }
        
        /* Navbar */
        .navbar-odoo { background-color: var(--theme-green-dark); padding: 0; height: 46px; box-shadow: 0 2px 4px rgba(0,0,0,0.15); }
        .navbar-odoo .app-launcher { color: white; padding: 0 16px; height: 100%; display: flex; align-items: center; text-decoration: none; transition: background-color 0.2s; }
        .navbar-odoo .app-launcher:hover { background-color: var(--theme-green); }
        .navbar-odoo .navbar-brand { font-size: 1.1rem; color: white; margin-left: 10px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        
        /* General Components */
        .btn:focus, .nav-link:focus, .form-control:focus, .form-select:focus { outline: none !important; box-shadow: none !important; }
        .nav-tabs .nav-link { color: #6c757d; border: none; border-bottom: 2px solid transparent; padding: 10px 15px; text-transform: uppercase; font-weight: 600; }
        .nav-tabs .nav-link.active { color: var(--theme-green) !important; border-bottom: 3px solid var(--theme-green); background: none; }
        
        /* Premium App Launcher Overlay */
        #appMenuOverlay {
             position: fixed; top: 46px; left: 0; width: 100%; height: calc(100vh - 46px);
             background-color: rgba(240, 242, 245, 0.95);
             -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px);
             z-index: 1050; display: none; overflow-y: auto;
        }
        .app-menu-close {
             position: absolute; top: 25px; right: 35px; font-size: 2rem; color: #adb5bd;
             cursor: pointer; transition: all 0.2s ease-in-out;
        }
        .app-menu-close:hover { color: var(--theme-red); transform: scale(1.1); }
        
        /* Menu Layout */
        .app-menu-wrapper { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .app-category { margin-bottom: 45px; }
        .category-title {
             font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px;
             color: var(--theme-green-dark); border-bottom: 2px solid #dee2e6;
             padding-bottom: 10px; margin-bottom: 25px; font-size: 0.85rem;
        }
        .app-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 20px; }
        
        /* UNIFIED PREMIUM ICONS & HOVER EFFECTS */
        .app-item {
             text-align: center; cursor: pointer; text-decoration: none; color: #495057;
             background: transparent; border-radius: 16px; padding: 15px 10px;
             transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .app-item:hover {
             background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.08);
             transform: translateY(-5px); color: var(--theme-green-dark);
        }
        .app-icon-wrapper {
             width: 78px; height: 78px; margin: 0 auto 12px; border-radius: 22px;
             display: flex; align-items: center; justify-content: center;
             font-size: 2.3rem; color: white;
             background: linear-gradient(135deg, var(--theme-green) 0%, var(--theme-green-dark) 100%);
             box-shadow: 0 6px 15px rgba(46, 125, 50, 0.2);
             border: 1px solid rgba(255,255,255,0.15);
             transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .app-item:hover .app-icon-wrapper {
             transform: translateY(-3px) scale(1.05);
             box-shadow: 0 12px 25px rgba(46, 125, 50, 0.35);
        }
        .app-item .app-name { text-transform: uppercase; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.4px; }
        
        /* Main Content & Form Styles */
        .main-content { padding: 20px; background: white; min-height: calc(100vh - 46px); }
        table { text-transform: uppercase; font-size: 0.85rem; }
        
        /* Global Table Scroll */
        .table-responsive {
            max-height: 55vh;
             overflow-y: auto;
             overflow-x: auto;
             border-bottom: 1px solid #dee2e6;
        }
        .table-responsive table { min-width: max-content; }
        .table-responsive table thead th {
             position: sticky; top: 0; z-index: 10;
             background-color: #f8f9fa !important;
             color: var(--theme-green-dark) !important;
             font-weight: 800;
             box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.2);
        }
        
        .action-icons i { font-size: 1.1rem; cursor: pointer; color: #6c757d; transition: color 0.2s; }
        .action-icons i.bi-trash:hover { color: var(--theme-red) !important; }
        .action-icons i.bi-printer:hover { color: var(--theme-green) !important; }
        
        /* Modals & Odoo Sheets */
        .odoo-modal-content { background-color: var(--odoo-bg); }
        .modal-control-panel { background-color: white; padding: 10px 20px; border-bottom: 1px solid #dee2e6; display: flex; gap: 10px; align-items: center; }
        .odoo-sheet { background: white; border: 1px solid #ddd; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 30px 40px; margin: 0 auto; max-width: 100%; border-top: 4px solid var(--theme-green) !important; }
        .odoo-title-input { font-size: 1.8rem; font-weight: 500; color: var(--theme-green-dark) !important; border: none; border-bottom: 1px solid #adb5bd; border-radius: 0; padding: 0 0 5px 0; margin-bottom: 20px; box-shadow: none !important; text-transform: uppercase; }
        .odoo-title-input:disabled { background-color: transparent; border-bottom: 1px dashed #ccc; }
        .odoo-title-input:hover:not(:disabled), .odoo-title-input:focus:not(:disabled) { border-bottom: 2px solid var(--theme-green); }
        .odoo-field-group { display: flex; align-items: center; margin-bottom: 12px; }
        .odoo-label { font-weight: bold; color: var(--theme-green-dark) !important; font-size: 0.85rem; width: 35%; margin: 0; text-transform: uppercase; }
        .odoo-input { width: 65%; border: none; border-bottom: 1px solid #adb5bd; background-color: #fdfdfd; border-radius: 0; padding: 6px 8px; font-size: 0.9rem; box-shadow: none !important; text-transform: uppercase; }
        .odoo-input:disabled { background-color: #f0f0f0; border-bottom: 1px dashed #ccc; color: #666; }
        .odoo-input:hover:not(:disabled), .odoo-input:focus:not(:disabled) { border-bottom: 2px solid var(--theme-green); background-color: var(--theme-green-light); }
        
        .module-container { display: none; }
        .module-container.active { display: block; }
        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-odoo fixed-top">
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center h-100">
            <a href="#" class="app-launcher" id="toggleAppMenu"><i class="bi bi-grid-3x3-gap-fill fs-5"></i></a>
            <span class="navbar-brand" id="navbarTitle">DASHBOARD</span>
        </div>
        <div class="d-flex align-items-center pe-3">
            <span class="text-white small fw-bold me-3 text-uppercase"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
            <a href="logout.php" class="btn btn-sm btn-outline-light text-uppercase fw-bold" style="font-size: 0.75rem;">Logout</a>
        </div>
    </div>
</nav>
<div style="height: 46px;"></div>
<!-- UNIFIED APP LAUNCHER -->
<div id="appMenuOverlay">
    <i class="bi bi-x-circle-fill app-menu-close" id="closeAppMenuIcon" title="Close Menu"></i>
    
    <div class="app-menu-wrapper">
        
        <!-- Executive Analytics Category -->
        <div class="app-category">
            <h6 class="category-title">Executive & Analytics</h6>
            <div class="app-grid">
                <a href="#" class="app-item" onclick="switchModule('Dashboard')">
                    <div class="app-icon-wrapper"><i class="bi bi-speedometer2"></i></div>
                    <div class="app-name">Dashboard</div>
                </a>
            </div>
        </div>
        <!-- Human Resources Category -->
        <?php if(canAccess('HR') || $_SESSION['role'] === 'Admin'): ?>
        <div class="app-category">
            <h6 class="category-title">Human Resources</h6>
            <div class="app-grid">
                <a href="#" class="app-item" onclick="switchModule('HR')">
                    <div class="app-icon-wrapper"><i class="bi bi-people-fill"></i></div>
                    <div class="app-name">HR & Payroll</div>
                </a>
            </div>
        </div>
        <?php endif; ?>
        <!-- Category 1: Master Data -->
        <?php if(canAccess('Accounts') || canAccess('Inventory')): ?>
        <div class="app-category">
            <h6 class="category-title">Master Data & Entities</h6>
            <div class="app-grid">
                <?php if(canAccess('Accounts')): ?>
                    <a href="#" class="app-item" onclick="switchModule('Accounts')">
                        <div class="app-icon-wrapper"><i class="bi bi-person-vcard"></i></div>
                        <div class="app-name">Accounts</div>
                    </a>
                <?php endif; ?>
                
                <?php if(canAccess('Inventory')): ?>
                    <a href="#" class="app-item" onclick="switchModule('Products')">
                        <div class="app-icon-wrapper"><i class="bi bi-box-seam"></i></div>
                        <div class="app-name">Products</div>
                    </a>
                    <a href="#" class="app-item" onclick="switchModule('Suppliers')">
                        <div class="app-icon-wrapper"><i class="bi bi-building"></i></div>
                        <div class="app-name">Suppliers</div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <!-- Category 2: Operations & Sales -->
        <?php if(canAccess('Inventory') || canAccess('DS') || canAccess('YL') || canAccess('Fleet')): ?>
        <div class="app-category">
            <h6 class="category-title">Operations & Sales</h6>
            <div class="app-grid">
                <?php if(canAccess('Inventory')): ?>
                    <a href="#" class="app-item" onclick="switchModule('Inventory')">
                        <div class="app-icon-wrapper"><i class="bi bi-boxes"></i></div>
                        <div class="app-name">Inventory</div>
                    </a>
                    <a href="#" class="app-item" onclick="switchModule('Purchasing')">
                        <div class="app-icon-wrapper"><i class="bi bi-cart-check"></i></div>
                        <div class="app-name">Purchasing</div>
                    </a>
                <?php endif; ?>
                <?php if(canAccess('DS')): ?>
                    <a href="#" class="app-item" onclick="switchModule('DS')">
                        <div class="app-icon-wrapper"><i class="bi bi-truck"></i></div>
                        <div class="app-name">DS Module</div>
                    </a>
                <?php endif; ?>
                
                <?php if(canAccess('YL')): ?>
                    <a href="#" class="app-item" onclick="switchModule('YL')">
                        <div class="app-icon-wrapper"><i class="bi bi-person-badge"></i></div>
                        <div class="app-name">YL Module</div>
                    </a>
                <?php endif; ?>
                <?php if(canAccess('Fleet') || $_SESSION['role'] === 'Admin'): ?>
                    <a href="#" class="app-item" onclick="switchModule('Fleet')">
                        <div class="app-icon-wrapper"><i class="bi bi-truck-front-fill"></i></div>
                        <div class="app-name">Fleet Management</div>
                    </a>
                <?php endif; ?>
                
                <?php if(canAccess('DS') || canAccess('YL')): ?>
                    <a href="mobile.php" class="app-item">
                        <div class="app-icon-wrapper"><i class="bi bi-phone-vibrate"></i></div>
                        <div class="app-name">Mobile App</div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <!-- Category 3: Finance & Administration -->
        <?php if(canAccess('Accounting') || $_SESSION['role'] === 'Admin'): ?>
        <div class="app-category">
            <h6 class="category-title">Finance & System</h6>
            <div class="app-grid">
                <?php if(canAccess('Accounting')): ?>
                    <a href="#" class="app-item" onclick="switchModule('Accounting')">
                        <div class="app-icon-wrapper"><i class="bi bi-calculator"></i></div>
                        <div class="app-name">Accounting</div>
                    </a>
                <?php endif; ?>
                
                <?php if($_SESSION['role'] === 'Admin'): ?>
                    <a href="#" class="app-item" onclick="switchModule('Discounts')">
                        <div class="app-icon-wrapper" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);"><i class="bi bi-tags-fill"></i></div>
                        <div class="app-name">Discounts & Rebates</div>
                    </a>
                    
                    <a href="#" class="app-item" onclick="switchModule('Settings')">
                        <div class="app-icon-wrapper"><i class="bi bi-gear-fill"></i></div>
                        <div class="app-name">Settings</div>
                    </a>
                    <a href="#" class="app-item" onclick="switchModule('Importer')">
                        <div class="app-icon-wrapper"><i class="bi bi-file-earmark-arrow-up"></i></div>
                        <div class="app-name">Bulk CSV Importer</div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<div class="main-content">
    <?php 
        // Load modules safely
        if(file_exists('modules/dashboard.php')) include 'modules/dashboard.php';
        if(file_exists('modules/hr.php')) include 'modules/hr.php';
        if(file_exists('modules/fleet.php')) include 'modules/fleet.php';

        if(file_exists('modules/accounts.php')) include 'modules/accounts.php';
        if(file_exists('modules/ds.php')) include 'modules/ds.php';
        if(file_exists('modules/yl.php')) include 'modules/yl.php';
        if(file_exists('modules/products.php')) include 'modules/products.php';
        if(file_exists('modules/suppliers.php')) include 'modules/suppliers.php';
        if(file_exists('modules/purchasing.php')) include 'modules/purchasing.php';
        if(file_exists('modules/inventory.php')) include 'modules/inventory.php';

        if(file_exists('modules/discounts.php')) include 'modules/discounts.php';
        if(file_exists('modules/settings.php')) include 'modules/settings.php';
        if(file_exists('modules/accounting.php')) include 'modules/accounting.php';
        if(file_exists('modules/importer.php')) include 'modules/importer.php';
    ?>
</div>
<div id="printArea"></div>
<script>
    window.userRole = '<?= htmlspecialchars($_SESSION['role']) ?>';
    window.userPermissions = <?= json_encode($_SESSION['permissions'] ?? []) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/utils.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
<!-- REFACTORED INVENTORY MICRO-SCRIPTS -->
<script src="assets/js/products.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/suppliers.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/inventory_movements.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/inventory_ledger.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/inventory_settings.js?v=<?php echo time(); ?>"></script>
<!-- REMAINDER CORE MODULES -->
<script src="assets/js/purchasing.js?v=<?php echo time(); ?>"></script> 
<script src="assets/js/hr.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/fleet.js?v=<?php echo time(); ?>"></script> 
<script src="assets/js/accounts.js?v=<?php echo time(); ?>"></script>
<!-- REFACTORED DS MICRO-SCRIPTS -->
<script src="assets/js/ds_orders.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/ds_invoices.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/ds_reports.js?v=<?php echo time(); ?>"></script>
<!-- REFACTORED YL MICRO-SCRIPTS -->
<script src="assets/js/yl_orders.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/yl_invoices.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/yl_reports.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/discounts.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/accounting.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/importer.js?v=<?php echo time(); ?>"></script>
</body>
</html>