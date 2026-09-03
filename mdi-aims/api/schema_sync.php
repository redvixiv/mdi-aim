<?php
header("Content-Type: application/json");
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    echo json_encode(['status' => 'error', 'message' => 'Admin access required.']);
    exit;
}

if (empty($_GET['force']) || $_GET['force'] !== '1') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Schema sync is disabled by default. Enable it only in a controlled migration session.'
    ]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Helper function to safely add columns without crashing if they already exist
function addCol($db, $table, $colDef) {
    try { 
        $db->exec("ALTER TABLE $table ADD COLUMN $colDef"); 
    } catch (PDOException $e) {
        // Ignore "Duplicate column name" errors
    }
}

try {
    addCol($db, 'system_dropdowns', 'Route_In_Charge VARCHAR(150) NULL');
    addCol($db, 'system_dropdowns', 'Center_Code VARCHAR(50) NULL');
    addCol($db, 'system_dropdowns', 'Center_In_Charge VARCHAR(150) NULL');
    addCol($db, 'system_dropdowns', 'Parent_Link VARCHAR(100) NULL');
    addCol($db, 'system_dropdowns', 'Linked_Warehouse_ID INT(11) NULL');
    
    addCol($db, 'products', 'Barcode VARCHAR(100) NULL');
    
    addCol($db, 'ds_sales_orders', "DS_Type VARCHAR(50) DEFAULT 'DS'");
    addCol($db, 'ds_invoices', "DS_Type VARCHAR(50) DEFAULT 'DS'");
    addCol($db, 'ds_collection_receipts', "DS_Type VARCHAR(50) DEFAULT 'DS'");
    
    $db->exec("UPDATE ds_sales_orders SET DS_Type = 'DS' WHERE DS_Type IS NULL OR DS_Type = ''");
    $db->exec("UPDATE ds_invoices SET DS_Type = 'DS' WHERE DS_Type IS NULL OR DS_Type = ''");
    $db->exec("UPDATE ds_collection_receipts SET DS_Type = 'DS' WHERE DS_Type IS NULL OR DS_Type = ''");
    
    addCol($db, 'ds_invoices', 'Discount_Percent DECIMAL(5,2) DEFAULT 0.00');
    addCol($db, 'ds_invoices', 'Discount_Amount DECIMAL(15,2) DEFAULT 0.00');
    
    addCol($db, 'yl_delivery_receipts', 'Items_JSON TEXT NULL');
    addCol($db, 'yl_delivery_receipts', 'Total_Quantity INT(11) DEFAULT 0');
    addCol($db, 'yl_delivery_receipts', 'Total_Amount DECIMAL(15,2) DEFAULT 0.00');
    
    addCol($db, 'yl_invoices', 'DR_IDs_JSON TEXT NULL');
    addCol($db, 'yl_invoices', 'DR_Nos TEXT NULL');
    addCol($db, 'yl_invoices', 'Dealer_Name VARCHAR(255) NULL');
    addCol($db, 'yl_invoices', 'Items_JSON TEXT NULL');
    addCol($db, 'yl_invoices', 'Dealer_Discount_Type VARCHAR(50) NULL');
    addCol($db, 'yl_invoices', 'Dealer_Discount_Amount DECIMAL(15,2) DEFAULT 0.00');
    
    addCol($db, 'company_profile', 'YL_Disc_Orig DECIMAL(5,3) DEFAULT 0.450');
    addCol($db, 'company_profile', 'YL_Disc_Light DECIMAL(5,3) DEFAULT 0.550');
    addCol($db, 'company_profile', 'YL_Trade_Orig DECIMAL(5,3) DEFAULT 0.500');
    addCol($db, 'company_profile', 'YL_Trade_Light DECIMAL(5,3) DEFAULT 0.700');
    
    $db->exec("CREATE TABLE IF NOT EXISTS yl_rebate_matrix (Rebate_ID INT AUTO_INCREMENT PRIMARY KEY, Product_Type VARCHAR(50) DEFAULT 'Original', Min_Qty INT, Max_Qty INT, Rebate_Amount DECIMAL(10,3))");
    $checkOrig = $db->query("SELECT COUNT(*) FROM yl_rebate_matrix WHERE Product_Type = 'Original'")->fetchColumn();
    if ($checkOrig == 0) {
        $db->exec("INSERT INTO yl_rebate_matrix (Product_Type, Min_Qty, Max_Qty, Rebate_Amount) VALUES 
            ('Original', 0, 99, 0.000), ('Original', 100, 149, 0.525), ('Original', 150, 199, 0.540), ('Original', 200, 249, 0.625), 
            ('Original', 250, 299, 0.655), ('Original', 300, 349, 0.660), ('Original', 350, 399, 0.665), ('Original', 400, 449, 0.680), 
            ('Original', 450, 499, 0.685), ('Original', 500, 599, 0.700), ('Original', 600, 999999, 0.710)");
    }
    
    $checkLight = $db->query("SELECT COUNT(*) FROM yl_rebate_matrix WHERE Product_Type = 'Light'")->fetchColumn();
    if ($checkLight == 0) {
        $db->exec("INSERT INTO yl_rebate_matrix (Product_Type, Min_Qty, Max_Qty, Rebate_Amount) VALUES 
            ('Light', 0, 9, 0.000), ('Light', 10, 19, 0.660), ('Light', 20, 29, 0.680), ('Light', 30, 39, 0.690), 
            ('Light', 40, 49, 0.700), ('Light', 50, 999999, 0.710)");
    }
    
    $db->exec("CREATE TABLE IF NOT EXISTS yl_calculated_rebates (
        Rebate_Calc_ID INT AUTO_INCREMENT PRIMARY KEY,
        Center VARCHAR(100), Dealer_ID INT, Dealer_Name VARCHAR(255),
        Area_No VARCHAR(100), Period_Month VARCHAR(20), Period_Day INT,
        Invoice_ID INT, Invoice_No VARCHAR(50), Items_JSON TEXT,
        Total_Dealer_Discount DECIMAL(15,2), Total_Trade_Discount DECIMAL(15,2), Total_Sales_Rebate DECIMAL(15,2),
        CreatedBy VARCHAR(50), CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UpdateBy VARCHAR(50), UpdateDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    addCol($db, 'yl_calculated_rebates', 'Rebate_Date DATE NULL');
    
    $db->exec("CREATE TABLE IF NOT EXISTS fixed_assets (Asset_ID INT AUTO_INCREMENT PRIMARY KEY, Asset_Name VARCHAR(255), Purchase_Date DATE, Purchase_Cost DECIMAL(15,2), Useful_Life_Months INT, Monthly_Depreciation DECIMAL(15,2), Accumulated_Depreciation DECIMAL(15,2) DEFAULT 0, Status VARCHAR(50) DEFAULT 'Active')");
    $db->exec("INSERT IGNORE INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1010', 'Cash on Hand', 'Asset'), ('3000', 'Retained Earnings', 'Equity'), ('1500', 'Accumulated Depreciation', 'Asset'), ('5100', 'Depreciation Expense', 'Expense')");
    
    $db->exec("CREATE TABLE IF NOT EXISTS audit_logs (Log_ID INT AUTO_INCREMENT PRIMARY KEY, Username VARCHAR(50), Action VARCHAR(100), Details TEXT, Log_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    addCol($db, 'users', 'Agent_Type VARCHAR(50) NULL');
    addCol($db, 'users', 'Linked_Entity VARCHAR(100) NULL');
    
    $db->exec("CREATE TABLE IF NOT EXISTS employees (Emp_ID INT AUTO_INCREMENT PRIMARY KEY, Emp_No VARCHAR(50), First_Name VARCHAR(50), Last_Name VARCHAR(50), Position VARCHAR(100), Department VARCHAR(100), Basic_Rate DECIMAL(15,2), Rate_Type VARCHAR(20), SSS_No VARCHAR(50), PhilHealth_No VARCHAR(50), PagIBIG_No VARCHAR(50), TIN VARCHAR(50), Status VARCHAR(20) DEFAULT 'Active', Hire_Date DATE)");
    $db->exec("CREATE TABLE IF NOT EXISTS dtr (DTR_ID INT AUTO_INCREMENT PRIMARY KEY, Emp_ID INT, Cutoff_Start DATE, Cutoff_End DATE, Days_Worked DECIMAL(5,2), OT_Hours DECIMAL(5,2), Late_Undertime_Hours DECIMAL(5,2))");
    $db->exec("CREATE TABLE IF NOT EXISTS payroll_records (Payroll_ID INT AUTO_INCREMENT PRIMARY KEY, Emp_ID INT, Cutoff_Start DATE, Cutoff_End DATE, Gross_Pay DECIMAL(15,2), SSS_Deduct DECIMAL(15,2), PHIC_Deduct DECIMAL(15,2), HDMF_Deduct DECIMAL(15,2), Tax_Deduct DECIMAL(15,2), Net_Pay DECIMAL(15,2), Date_Generated TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    
    $db->exec("CREATE TABLE IF NOT EXISTS fleet_vehicles (Vehicle_ID INT AUTO_INCREMENT PRIMARY KEY, Plate_No VARCHAR(20), Make_Model VARCHAR(100), Vehicle_Type VARCHAR(50), Current_Mileage DECIMAL(10,2) DEFAULT 0, Status VARCHAR(20) DEFAULT 'Active')");
    $db->exec("CREATE TABLE IF NOT EXISTS fleet_trips (Trip_ID INT AUTO_INCREMENT PRIMARY KEY, Vehicle_ID INT, Trip_Date DATE, Route VARCHAR(100), Driver_Name VARCHAR(100), Agent_Name VARCHAR(100), Start_Mileage DECIMAL(10,2), End_Mileage DECIMAL(10,2), Status VARCHAR(20) DEFAULT 'Dispatched')");
    $db->exec("CREATE TABLE IF NOT EXISTS fleet_maintenance (Maintenance_ID INT AUTO_INCREMENT PRIMARY KEY, Vehicle_ID INT, Service_Date DATE, Service_Type VARCHAR(100), Cost DECIMAL(15,2), Remarks TEXT, Account_ID INT(11) NULL)");
    $db->exec("INSERT IGNORE INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('5400', 'Fuel and Oil Expense', 'Expense'), ('5401', 'Repairs and Maintenance', 'Expense'), ('5402', 'Taxes and Licenses', 'Expense')");
    
    echo json_encode(['status' => 'success', 'message' => 'Schema successfully validated and synced.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Schema sync failed: ' . $e->getMessage()]);
}
?>