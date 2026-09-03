<?php
session_start();
header("Content-Type: application/json");
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access. Please log in.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

try { $db->exec("INSERT IGNORE INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('5400', 'Fuel and Oil Expense', 'Expense')"); } catch (PDOException $e) {}
try { $db->exec("INSERT IGNORE INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('5401', 'Repairs and Maintenance', 'Expense')"); } catch (PDOException $e) {}
try { $db->exec("INSERT IGNORE INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('5402', 'Taxes and Licenses', 'Expense')"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE fleet_maintenance ADD COLUMN Account_ID INT(11) NULL"); } catch (PDOException $e) {}

function logAudit($db, $action, $details) {
    $user = $_SESSION['username'] ?? 'System';
    try {
        $stmt = $db->prepare("INSERT INTO audit_logs (Username, Action, Details) VALUES (?, ?, ?)");
        $stmt->execute([$user, $action, $details]);
    } catch(PDOException $e) {}
}

$table = $_GET['table'] ?? ($_POST['table'] ?? '');
$id = $_GET['id'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input")); 

if ($method === 'GET') {
    if ($table === 'fleet_vehicles') {
        $stmt = $db->query("SELECT * FROM fleet_vehicles ORDER BY Status ASC, Plate_No ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    elseif ($table === 'fleet_trips') {
        $stmt = $db->query("SELECT t.*, v.Plate_No FROM fleet_trips t JOIN fleet_vehicles v ON t.Vehicle_ID = v.Vehicle_ID ORDER BY t.Trip_Date DESC, t.Trip_ID DESC LIMIT 200");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    elseif ($table === 'fleet_maintenance') {
        $stmt = $db->query("SELECT m.*, v.Plate_No FROM fleet_maintenance m JOIN fleet_vehicles v ON m.Vehicle_ID = v.Vehicle_ID ORDER BY m.Service_Date DESC LIMIT 200");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    elseif ($table === 'fleet_analytics') {
        // --- NEW: COST PER KILOMETER ANALYTICS ENGINE ---
        $stmt = $db->query("
            SELECT 
                v.Plate_No, 
                v.Make_Model,
                (SELECT COALESCE(SUM(Cost), 0) FROM fleet_maintenance m WHERE m.Vehicle_ID = v.Vehicle_ID AND m.Service_Type = 'Fuel Refill') as Total_Fuel,
                (SELECT COALESCE(SUM(End_Mileage - Start_Mileage), 0) FROM fleet_trips t WHERE t.Vehicle_ID = v.Vehicle_ID AND t.Status = 'Completed') as Total_Distance
            FROM fleet_vehicles v
            WHERE v.Status = 'Active'
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
} 
elseif ($method === 'POST') {
    if ($table === 'fleet_vehicles') {
        if (empty($data->vehicle_id)) {
            $stmt = $db->prepare("INSERT INTO fleet_vehicles (Plate_No, Make_Model, Vehicle_Type, Current_Mileage, Status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$data->plate_no, $data->make_model, $data->type, $data->mileage, $data->status]);
            logAudit($db, 'FLEET', "Registered new vehicle: {$data->plate_no}");
        } else {
            $stmt = $db->prepare("UPDATE fleet_vehicles SET Plate_No=?, Make_Model=?, Vehicle_Type=?, Current_Mileage=?, Status=? WHERE Vehicle_ID=?");
            $stmt->execute([$data->plate_no, $data->make_model, $data->type, $data->mileage, $data->status, $data->vehicle_id]);
            logAudit($db, 'FLEET', "Updated vehicle ID: {$data->vehicle_id}");
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
    elseif ($table === 'fleet_trips') {
        $db->beginTransaction();
        try {
            if (empty($data->trip_id)) {
                $stmt = $db->prepare("INSERT INTO fleet_trips (Vehicle_ID, Trip_Date, Route, Driver_Name, Agent_Name, Start_Mileage, End_Mileage, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$data->vehicle_id, $data->date, $data->route, $data->driver, $data->agent, $data->start_mileage, $data->end_mileage, $data->status]);
            } else {
                $stmt = $db->prepare("UPDATE fleet_trips SET Vehicle_ID=?, Trip_Date=?, Route=?, Driver_Name=?, Agent_Name=?, Start_Mileage=?, End_Mileage=?, Status=? WHERE Trip_ID=?");
                $stmt->execute([$data->vehicle_id, $data->date, $data->route, $data->driver, $data->agent, $data->start_mileage, $data->end_mileage, $data->status, $data->trip_id]);
            }
            
            if ($data->status === 'Completed' && $data->end_mileage > 0) {
                $upd = $db->prepare("UPDATE fleet_vehicles SET Current_Mileage = ? WHERE Vehicle_ID = ? AND Current_Mileage < ?");
                $upd->execute([$data->end_mileage, $data->vehicle_id, $data->end_mileage]);
            }
            
            $db->commit();
            logAudit($db, 'FLEET', "Saved Trip Dispatch for Route: {$data->route}");
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
    elseif ($table === 'fleet_maintenance') {
        $db->beginTransaction();
        try {
            if (empty($data->maintenance_id)) {
                $stmt = $db->prepare("INSERT INTO fleet_maintenance (Vehicle_ID, Account_ID, Service_Date, Service_Type, Cost, Remarks) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$data->vehicle_id, $data->account_id, $data->date, $data->type, $data->cost, $data->remarks]);
                $maint_id = $db->lastInsertId();

                $expense_code = '5401'; 
                if ($data->type === 'Fuel Refill') $expense_code = '5400';
                if ($data->type === 'Registration / Insurance') $expense_code = '5402';
                
                $exp_acc_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '$expense_code'")->fetchColumn();

                $v_plate = $db->query("SELECT Plate_No FROM fleet_vehicles WHERE Vehicle_ID = " . (int)$data->vehicle_id)->fetchColumn();
                $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (?, ?, ?)");
                $j_stmt->execute([$data->date, "FM-".$maint_id, "Fleet Expense ($data->type) - $v_plate"]);
                $jid = $db->lastInsertId();

                $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (?, ?, ?, ?)");
                $l_stmt->execute([$jid, $exp_acc_id, $data->cost, 0]); 
                $l_stmt->execute([$jid, $data->account_id, 0, $data->cost]); 

                logAudit($db, 'FLEET', "Logged Maintenance ({$data->type}) for {$v_plate} & Auto-Posted to GL.");
            } else {
                $stmt = $db->prepare("UPDATE fleet_maintenance SET Vehicle_ID=?, Account_ID=?, Service_Date=?, Service_Type=?, Cost=?, Remarks=? WHERE Maintenance_ID=?");
                $stmt->execute([$data->vehicle_id, $data->account_id, $data->date, $data->type, $data->cost, $data->remarks, $data->maintenance_id]);
                logAudit($db, 'FLEET', "Updated Maintenance Record ID: {$data->maintenance_id}");
            }
            $db->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}
elseif ($method === 'DELETE') {
    if ($table && $id) {
        $stmt = $db->prepare("DELETE FROM $table WHERE " . ($table === 'fleet_vehicles' ? 'Vehicle_ID' : ($table === 'fleet_trips' ? 'Trip_ID' : 'Maintenance_ID')) . " = ?");
        $stmt->execute([$id]);
        logAudit($db, 'FLEET', "Deleted record ID $id from $table.");
        echo json_encode(['status' => 'success']);
        exit;
    }
}
?>