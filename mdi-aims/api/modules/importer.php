<?php
session_start();
header("Content-Type: application/json");
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? '';

if ($action === 'process_import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = $_POST['target'] ?? '';
    
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or missing file upload.']);
        exit;
    }

    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, 'r');

    if (!$handle) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to open CSV file.']);
        exit;
    }

    // Skip BOM if present
    fseek($handle, 0);
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") fseek($handle, 0);

    $headers = fgetcsv($handle, 2000, ",");
    if (!$headers) {
        echo json_encode(['status' => 'error', 'message' => 'CSV file is empty.']);
        exit;
    }

    $headers = array_map('trim', $headers);
    
    $inserted = 0; $skipped = 0; $errors = 0; $logs = [];
    $db->beginTransaction();

    try {
        while (($row = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if (empty(array_filter($row))) continue; // Skip blank lines
            $data = array_combine($headers, array_map('trim', $row));

            // --- IMPORT PRODUCTS ---
            if ($target === 'products') {
                $pname = strtoupper($data['Product_Name'] ?? '');
                if (empty($pname)) continue;

                $check = $db->prepare("SELECT Product_ID FROM products WHERE Product_Name = ?");
                $check->execute([$pname]);
                if ($check->fetchColumn()) {
                    $skipped++;
                    $logs[] = ['type' => 'SKIP', 'item' => $pname, 'message' => 'Product already exists.'];
                    continue;
                }

                $q = $db->query("SELECT MAX(CAST(SUBSTRING(Product_No, 3) AS UNSIGNED)) AS max_no FROM products");
                $maxRow = $q->fetch(PDO::FETCH_ASSOC);
                $pno = "P-" . ($maxRow['max_no'] ? $maxRow['max_no'] + 1 : 10001);

                $insP = $db->prepare("INSERT INTO products (Product_No, Product_Name, Category, Description, Barcode) VALUES (?, ?, ?, ?, ?)");
                $insP->execute([$pno, $pname, strtoupper($data['Category'] ?? ''), strtoupper($data['Description'] ?? ''), $data['Barcode'] ?? null]);
                $pid = $db->lastInsertId();

                $insPr = $db->prepare("INSERT INTO product_pricing (Product_ID, Unit_Cost, Wholesale, Retail, Effective_From) VALUES (?, ?, ?, ?, CURRENT_DATE)");
                $insPr->execute([$pid, (float)($data['Unit_Cost'] ?? 0), (float)($data['Wholesale'] ?? 0), (float)($data['Retail'] ?? 0)]);

                $inserted++;
                $logs[] = ['type' => 'SUCCESS', 'item' => "[$pno] $pname", 'message' => 'Product and pricing imported.'];
            }

            // --- IMPORT OUTLETS ---
            elseif ($target === 'outlets') {
                $oname = strtoupper($data['Outlet_Name'] ?? '');
                $branch = strtoupper($data['Branch'] ?? '');
                if (empty($oname)) continue;

                $q = $db->query("SELECT MAX(CAST(Outlet_No AS UNSIGNED)) AS max_no FROM outlets");
                $maxRow = $q->fetch(PDO::FETCH_ASSOC);
                $ono = $maxRow['max_no'] ? $maxRow['max_no'] + 1 : 100001;

                $ins = $db->prepare("INSERT INTO outlets (Outlet_No, Outlet_Name, Branch, Outlet_TIN, Province, City, Barangay, Address, Route, Contact_Person, Contact_No, Terms, Business_Style, Category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $ins->execute([$ono, $oname, $branch, $data['Outlet_TIN'] ?? '', strtoupper($data['Province'] ?? ''), strtoupper($data['City'] ?? ''), strtoupper($data['Barangay'] ?? ''), strtoupper($data['Address'] ?? ''), strtoupper($data['Route'] ?? ''), strtoupper($data['Contact_Person'] ?? ''), $data['Contact_No'] ?? '', $data['Terms'] ?? '', strtoupper($data['Business_Style'] ?? ''), strtoupper($data['Category'] ?? '')]);

                $inserted++;
                $logs[] = ['type' => 'SUCCESS', 'item' => "[$ono] $oname ($branch)", 'message' => 'Outlet created successfully.'];
            }

            // --- IMPORT DEALERS ---
            elseif ($target === 'dealers') {
                $fname = strtoupper($data['First_Name'] ?? '');
                $lname = strtoupper($data['Last_Name'] ?? '');
                if (empty($fname) || empty($lname)) continue;

                $q = $db->query("SELECT MAX(CAST(Dealer_No AS UNSIGNED)) AS max_no FROM independent_dealers");
                $maxRow = $q->fetch(PDO::FETCH_ASSOC);
                $dno = $maxRow['max_no'] ? $maxRow['max_no'] + 1 : 10001;

                $ins = $db->prepare("INSERT INTO independent_dealers (Dealer_No, First_Name, Middle_Name, Last_Name, Birth_Date, Hiring_Date, Center_Code, Center, Area, Type, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $ins->execute([$dno, $fname, strtoupper($data['Middle_Name'] ?? ''), $lname, !empty($data['Birth_Date']) ? $data['Birth_Date'] : null, !empty($data['Hiring_Date']) ? $data['Hiring_Date'] : date('Y-m-d'), strtoupper($data['Center_Code'] ?? ''), strtoupper($data['Center'] ?? ''), strtoupper($data['Area'] ?? ''), $data['Type'] ?? 'Yakult Lady', $data['Status'] ?? 'Active']);

                $inserted++;
                $logs[] = ['type' => 'SUCCESS', 'item' => "[$dno] $fname $lname", 'message' => 'Dealer account created.'];
            }

            // --- IMPORT SUPPLIERS ---
            elseif ($target === 'suppliers') {
                $sname = strtoupper($data['Supplier_Name'] ?? '');
                if (empty($sname)) continue;

                $q = $db->query("SELECT MAX(CAST(SUBSTRING(Supplier_No, 3) AS UNSIGNED)) AS max_no FROM suppliers");
                $maxRow = $q->fetch(PDO::FETCH_ASSOC);
                $sno = "S-" . ($maxRow['max_no'] ? $maxRow['max_no'] + 1 : 10001);

                $ins = $db->prepare("INSERT INTO suppliers (Supplier_No, Supplier_Name, Province, City, Barangay, Address, Contact_Name, Contact_No) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $ins->execute([$sno, $sname, strtoupper($data['Province'] ?? ''), strtoupper($data['City'] ?? ''), strtoupper($data['Barangay'] ?? ''), strtoupper($data['Address'] ?? ''), strtoupper($data['Contact_Name'] ?? ''), $data['Contact_No'] ?? '']);

                $inserted++;
                $logs[] = ['type' => 'SUCCESS', 'item' => "[$sno] $sname", 'message' => 'Supplier added.'];
            }

            // --- IMPORT EMPLOYEES ---
            elseif ($target === 'employees') {
                $fname = strtoupper($data['First_Name'] ?? '');
                $lname = strtoupper($data['Last_Name'] ?? '');
                if (empty($fname) || empty($lname)) continue;

                $q = $db->query("SELECT MAX(CAST(REPLACE(Emp_No, 'EMP-', '') AS UNSIGNED)) AS max_no FROM employees WHERE Emp_No LIKE 'EMP-%'");
                $maxRow = $q->fetch(PDO::FETCH_ASSOC);
                $eno = "EMP-" . str_pad($maxRow['max_no'] ? $maxRow['max_no'] + 1 : 1001, 3, "0", STR_PAD_LEFT);

                $ins = $db->prepare("INSERT INTO employees (Emp_No, First_Name, Last_Name, Position, Department, Basic_Rate, Rate_Type, SSS_No, PhilHealth_No, PagIBIG_No, TIN, Status, Hire_Date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', ?)");
                $ins->execute([$eno, $fname, $lname, strtoupper($data['Position'] ?? ''), strtoupper($data['Department'] ?? ''), (float)($data['Basic_Rate'] ?? 0), $data['Rate_Type'] ?? 'Daily', $data['SSS_No'] ?? '', $data['PhilHealth_No'] ?? '', $data['PagIBIG_No'] ?? '', $data['TIN'] ?? '', !empty($data['Hire_Date']) ? $data['Hire_Date'] : date('Y-m-d')]);

                $inserted++;
                $logs[] = ['type' => 'SUCCESS', 'item' => "[$eno] $fname $lname", 'message' => 'Employee 201 File created.'];
            }
        }

        $db->commit();
        fclose($handle);

        echo json_encode([
            'status' => 'success',
            'inserted' => $inserted,
            'skipped' => $skipped,
            'errors' => $errors,
            'logs' => $logs
        ]);
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        fclose($handle);
        echo json_encode(['status' => 'error', 'message' => 'Import Error: ' . $e->getMessage()]);
        exit;
    }
}