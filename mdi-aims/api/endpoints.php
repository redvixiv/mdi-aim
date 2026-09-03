<?php
header("Content-Type: application/json");
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access. Please log in.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

function logAudit($db, $action, $details) {
    $user = $_SESSION['username'] ?? 'System';
    try {
        $stmt = $db->prepare("INSERT INTO audit_logs (Username, Action, Details) VALUES (?, ?, ?)");
        $stmt->execute([$user, $action, $details]);
    } catch(PDOException $e) {}
}

$table = $_GET['table'] ?? ($_POST['table'] ?? '');
$id = $_GET['id'] ?? null;
$customer_id = $_GET['customer_id'] ?? null;
$ds_section = $_GET['ds_section'] ?? null;
$ds_type = $_GET['ds_type'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

$core_tables = [
    'fleet_vehicles', 'fleet_trips', 'fleet_maintenance', 'fleet_analytics',
    'yl_calculated_rebates', 'audit_logs', 'backup_database', 'dashboard_analytics',
    'users', 'yl_discount_rates', 'yl_rebate_matrix', 'system_dropdowns', 'print_data', 'stock_position_report'
];
$accounts_tables = ['customers', 'dealers', 'dealer_details', 'outlets', 'outlet_details'];
$products_tables = ['products', 'product_pricing', 'active_price'];
$suppliers_tables = ['suppliers'];
$settings_tables = ['company_profile', 'update_lock_date'];
$ds_tables = ['ds_sales_orders', 'ds_sales_order_details', 'ds_invoices', 'ds_unpaid_invoices', 'ds_outlets_with_unpaid_invoices', 'ds_invoices_by_ids', 'ds_collection_receipts', 'ds_sales_report'];
$yl_tables = ['yl_stock_orders', 'yl_delivery_receipts', 'yl_invoices', 'yl_collection_receipts', 'yl_unpaid_invoices', 'yl_dealers_with_unpaid_invoices', 'yl_discount_rates', 'yl_rebate_matrix', 'yl_calculated_rebates', 'yl_sales_report', 'yl_remittance_report', 'yl_stock_returns'];
$purchasing_tables = ['warehouses', 'purchase_orders', 'goods_receipts', 'inventory_ledger', 'stock_balances', 'stock_transfers', 'return_references', 'inventory_report', 'purchasing_report'];
$hr_tables = ['employees', 'dtr', 'payroll_records'];
$accounting_tables = [
    'accounting_coa', 'accounting_ap', 'accounting_ar', 'accounting_pv', 'accounting_expenses', 
    'accounting_gl', 'accounting_ledger', 'accounting_reports', 'trial_balance', 'accounting_tax_report', 
    'bank_recon_lines', 'fixed_assets', 'audit_reports', 'close_fiscal_year', 'run_depreciation', 
    'sync_historical_ledger', 'update_recon_status', 'reverse_journal', 'accounting_ap_pending', 'bir_slsp_export'
];

$allowedTables = array_merge(
    $core_tables, $accounts_tables, $products_tables, $suppliers_tables, 
    $settings_tables, $ds_tables, $yl_tables, $purchasing_tables, $hr_tables, $accounting_tables
);

if (!is_string($table) || $table === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $table) || !in_array($table, $allowedTables, true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid or unsupported table request.']);
    exit;
}

if ($method !== 'GET') {
    $adminWriteOnlyTables = ['users', 'company_profile', 'backup_database', 'update_lock_date', 'system_dropdowns'];
    if (in_array($table, $adminWriteOnlyTables, true) && ($_SESSION['role'] ?? '') !== 'Admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Admin privileges required to modify settings.']);
        exit;
    }
} else {
    $adminGetOnlyTables = ['users', 'backup_database'];
    if (in_array($table, $adminGetOnlyTables, true) && ($_SESSION['role'] ?? '') !== 'Admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Admin privileges required for this action.']);
        exit;
    }
}

function userHasModuleAccess($moduleName) {
    if (empty($_SESSION['permissions']) || !is_array($_SESSION['permissions'])) {
        return false;
    }
    $modules = $_SESSION['permissions']['modules'] ?? [];
    return is_array($modules) && in_array($moduleName, $modules, true);
}

function resolveModuleForTable($table) {
    global $accounts_tables, $products_tables, $suppliers_tables, $settings_tables, $ds_tables, $yl_tables, $purchasing_tables, $hr_tables, $core_tables;
    $fleetTables = ['fleet_vehicles', 'fleet_trips', 'fleet_maintenance', 'fleet_analytics'];
    
    if (in_array($table, $accounts_tables, true)) return 'Accounts';
    if (in_array($table, $products_tables, true) || in_array($table, $suppliers_tables, true) || in_array($table, $purchasing_tables, true)) return 'Inventory';
    if (in_array($table, $ds_tables, true)) return 'DS';
    if (in_array($table, $yl_tables, true)) return 'YL';
    if (in_array($table, $hr_tables, true)) return 'HR';
    if (in_array($table, $fleetTables, true)) return 'Fleet';
    if (in_array($table, $settings_tables, true) || $table === 'users') return 'Settings';
    
    return 'Accounting';
}

if (($_SESSION['role'] ?? '') !== 'Admin') {
    $module = resolveModuleForTable($table);
    if ($module !== null && !userHasModuleAccess($module) && !in_array($_SESSION['role'] ?? '', ['Field Agent'], true)) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'You do not have access to the requested module.']);
        exit;
    }
}

if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) && ($_SESSION['role'] ?? '') === 'Viewer') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Read-only users cannot modify records.']);
    exit;
}

if ($method === 'POST' && $data) {
    $prof_stmt = $db->query("SELECT Lock_Date FROM company_profile WHERE Profile_ID = 1");
    $profile = $prof_stmt->fetch(PDO::FETCH_ASSOC);
    $lock_date = $profile ? $profile['Lock_Date'] : null;
    
    if ($lock_date && $lock_date !== '0000-00-00' && $table !== 'close_fiscal_year') {
        $lock_timestamp = strtotime($lock_date);
        $date_fields = ['so_date', 'invoice_date', 'cr_date', 'dr_date', 'ap_date', 'pv_date', 'expense_date', 'journal_date', 'transfer_date', 'return_date'];
        
        foreach ($date_fields as $df) {
            if (isset($data->$df)) {
                $transaction_timestamp = strtotime($data->$df);
                if ($transaction_timestamp <= $lock_timestamp) {
                    echo json_encode(['status' => 'error', 'message' => 'TRANSACTION BLOCKED: The books are locked for this period.']);
                    exit; 
                }
            }
        }
    }
}

if (in_array($table, $core_tables, true)) { require_once 'core_api.php'; }
elseif (in_array($table, $accounts_tables, true)) { require_once 'modules/accounts.php'; }
elseif (in_array($table, $products_tables, true)) { require_once 'modules/products.php'; }
elseif (in_array($table, $suppliers_tables, true)) { require_once 'modules/suppliers.php'; }
elseif (in_array($table, $settings_tables, true)) { require_once 'modules/settings.php'; }
elseif (in_array($table, $ds_tables, true)) { require_once 'modules/ds.php'; }
elseif (in_array($table, $yl_tables, true)) { require_once 'modules/yl.php'; }
elseif (in_array($table, $purchasing_tables, true)) { require_once 'modules/purchasing.php'; }
elseif (in_array($table, $hr_tables, true)) { require_once 'modules/hr.php'; }
else {
    if ($method === 'DELETE' && $id) {
        logAudit($db, 'DELETE RECORD', "Deleted record ID {$id} from table '{$table}'.");
    }
    require_once 'modules/accounting.php';
}
?>