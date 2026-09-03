<?php
if (!isset($db)) { exit; }

if (in_array($table, ['fleet_vehicles', 'fleet_trips', 'fleet_maintenance', 'fleet_analytics'])) {
    if ($method === 'GET') {
        if ($table === 'fleet_vehicles') {
            $stmt = $db->query("SELECT * FROM fleet_vehicles ORDER BY Status ASC, Plate_No ASC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } elseif ($table === 'fleet_trips') {
            $stmt = $db->query("SELECT t.*, v.Plate_No FROM fleet_trips t JOIN fleet_vehicles v ON t.Vehicle_ID = v.Vehicle_ID ORDER BY t.Trip_Date DESC, t.Trip_ID DESC LIMIT 200");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } elseif ($table === 'fleet_maintenance') {
            $stmt = $db->query("SELECT m.*, v.Plate_No FROM fleet_maintenance m JOIN fleet_vehicles v ON m.Vehicle_ID = v.Vehicle_ID ORDER BY m.Service_Date DESC LIMIT 200");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } elseif ($table === 'fleet_analytics') {
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
        }
    } elseif ($method === 'POST') {
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
        } elseif ($table === 'fleet_trips') {
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
                echo json_encode(['status' => 'success']);
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } elseif ($table === 'fleet_maintenance') {
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
                }
                $db->commit();
                echo json_encode(['status' => 'success']);
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    } elseif ($method === 'DELETE') {
        $primaryKey = $table === 'fleet_vehicles' ? 'Vehicle_ID' : ($table === 'fleet_trips' ? 'Trip_ID' : 'Maintenance_ID');
        $stmt = $db->prepare("DELETE FROM $table WHERE $primaryKey = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success']);
    }
    exit;
}

if ($table === 'yl_calculated_rebates') {
    if ($method === 'GET') {
        $stmt = $db->query("SELECT * FROM yl_calculated_rebates ORDER BY CreatedDate DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } elseif ($method === 'POST') {
        $user = $_SESSION['username'] ?? 'System';
        if (empty($data->rebate_calc_id)) {
            $stmt = $db->prepare("INSERT INTO yl_calculated_rebates (Center, Dealer_ID, Dealer_Name, Area_No, Rebate_Date, Invoice_ID, Invoice_No, Items_JSON, Total_Dealer_Discount, Total_Trade_Discount, Total_Sales_Rebate, CreatedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data->center, $data->dealer_id, $data->dealer_name, $data->area_no, $data->rebate_date, $data->invoice_id, $data->invoice_no, json_encode($data->items), $data->total_dealer_disc, $data->total_trade_disc, $data->total_sales_rebate, $user]);
        } else {
            $stmt = $db->prepare("UPDATE yl_calculated_rebates SET Center=?, Dealer_ID=?, Dealer_Name=?, Area_No=?, Rebate_Date=?, Invoice_ID=?, Invoice_No=?, Items_JSON=?, Total_Dealer_Discount=?, Total_Trade_Discount=?, Total_Sales_Rebate=?, UpdateBy=? WHERE Rebate_Calc_ID=?");
            $stmt->execute([$data->center, $data->dealer_id, $data->dealer_name, $data->area_no, $data->rebate_date, $data->invoice_id, $data->invoice_no, json_encode($data->items), $data->total_dealer_disc, $data->total_trade_disc, $data->total_sales_rebate, $user, $data->rebate_calc_id]);
        }
        echo json_encode(['status' => 'success']);
    } elseif ($method === 'DELETE') {
        $stmt = $db->prepare("DELETE FROM yl_calculated_rebates WHERE Rebate_Calc_ID=?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success']);
    }
    exit;
}

if ($table === 'audit_logs' && $method === 'GET') {
    $stmt = $db->query("SELECT * FROM audit_logs ORDER BY Log_Date DESC LIMIT 200");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($table === 'backup_database') {
    logAudit($db, 'SYSTEM BACKUP', "Downloaded a full database backup.");
    $tables = [];
    $q = $db->query("SHOW TABLES");
    while($row = $q->fetch(PDO::FETCH_NUM)) { $tables[] = $row[0]; }
    
    $sql = "-- MDI AIMS Database Backup\n-- Generated By: " . ($_SESSION['username'] ?? 'Admin') . "\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
    foreach($tables as $t) {
        $row2 = $db->query("SHOW CREATE TABLE $t")->fetch(PDO::FETCH_NUM);
        $sql .= "\n\n" . $row2[1] . ";\n\n";
        $rows = $db->query("SELECT * FROM $t")->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $r) {
            $vals = array_map(function($val) use ($db) { return $val === null ? 'NULL' : $db->quote($val); }, array_values($r));
            $sql .= "INSERT INTO $t VALUES(" . implode(", ", $vals) . ");\n";
        }
    }
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="backup_mdi_aims_' . date('Y-m-d_His') . '.sql"');
    echo $sql;
    exit;
}

if ($table === 'dashboard_analytics') {
    $months = [];
    $revData = [];
    $expData = [];
    for ($i = 5; $i >= 0; $i--) {
        $m = date('Y-m', strtotime("-$i months"));
        $months[] = date('M Y', strtotime("-$i months"));
        
        $stmtR = $db->prepare("SELECT COALESCE(SUM(Credit) - SUM(Debit), 0) FROM accounting_journal_lines l JOIN accounting_journal j ON l.Journal_ID = j.Journal_ID JOIN accounting_coa c ON l.Account_ID = c.Account_ID WHERE c.Account_Type = 'Revenue' AND DATE_FORMAT(j.Journal_Date, '%Y-%m') = ?");
        $stmtR->execute([$m]);
        $revData[] = (float)$stmtR->fetchColumn();
        
        $stmtE = $db->prepare("SELECT COALESCE(SUM(Debit) - SUM(Credit), 0) FROM accounting_journal_lines l JOIN accounting_journal j ON l.Journal_ID = j.Journal_ID JOIN accounting_coa c ON l.Account_ID = c.Account_ID WHERE c.Account_Type = 'Expense' AND DATE_FORMAT(j.Journal_Date, '%Y-%m') = ?");
        $stmtE->execute([$m]);
        $expData[] = (float)$stmtE->fetchColumn();
    }
    
    $ar_val = (float)$db->query("SELECT COALESCE(SUM(l.Debit - l.Credit), 0) FROM accounting_journal_lines l JOIN accounting_coa c ON l.Account_ID = c.Account_ID WHERE c.Account_Code = '1200'")->fetchColumn();
    $ap_val = (float)$db->query("SELECT COALESCE(SUM(l.Credit - l.Debit), 0) FROM accounting_journal_lines l JOIN accounting_coa c ON l.Account_ID = c.Account_ID WHERE c.Account_Code = '2000'")->fetchColumn();
    $inv_val = (float)$db->query("SELECT COALESCE(SUM(l.Debit - l.Credit), 0) FROM accounting_journal_lines l JOIN accounting_coa c ON l.Account_ID = c.Account_ID WHERE c.Account_Code = '1300'")->fetchColumn();
    
    $top_ds = $db->query("SELECT o.Outlet_Name as name, SUM(i.Amount_Due) as total FROM ds_invoices i JOIN ds_sales_orders so ON i.SO_ID = so.SO_ID JOIN outlets o ON so.Outlet_ID = o.Outlet_ID WHERE DATE_FORMAT(i.Invoice_Date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m') GROUP BY o.Outlet_ID ORDER BY total DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $top_yl = $db->query("SELECT COALESCE(i.Dealer_Name, CONCAT(d.First_Name, ' ', d.Last_Name)) as name, SUM(i.Amount_Due) as total FROM yl_invoices i LEFT JOIN yl_delivery_receipts dr ON i.DR_ID = dr.DR_ID LEFT JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID LEFT JOIN independent_dealers d ON so.Dealer_ID = d.Dealer_ID WHERE DATE_FORMAT(i.Invoice_Date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m') GROUP BY name ORDER BY total DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'chart_labels' => $months,
        'chart_revenue' => $revData,
        'chart_expenses' => $expData,
        'ar_total' => $ar_val,
        'ap_total' => $ap_val,
        'inv_valuation' => $inv_val,
        'top_ds' => $top_ds,
        'top_yl' => $top_yl
    ]);
    exit;
}

if ($method === 'GET' && $table === 'users') {
    $stmt = $db->prepare("SELECT User_ID, Username, Role, Permissions_JSON, IFNULL(Agent_Type, '') as Agent_Type, IFNULL(Linked_Entity, '') as Linked_Entity FROM users ORDER BY User_ID ASC");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
} elseif ($method === 'POST' && $table === 'users') {
    $agent_type = !empty($data->agent_type) ? $data->agent_type : null;
    $linked_entity = !empty($data->linked_entity) ? $data->linked_entity : null;
    if (empty($data->user_id)) {
        $hash = password_hash($data->password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (Username, Password, Role, Permissions_JSON, Agent_Type, Linked_Entity) VALUES (:user, :pass, :role, :perms, :at, :le)");
        $stmt->execute([':user' => $data->username, ':pass' => $hash, ':role' => $data->role, ':perms' => json_encode($data->permissions), ':at' => $agent_type, ':le' => $linked_entity]);
    } else {
        if (!empty($data->password)) {
            $hash = password_hash($data->password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET Username = :user, Password = :pass, Role = :role, Permissions_JSON = :perms, Agent_Type = :at, Linked_Entity = :le WHERE User_ID = :id");
            $stmt->execute([':user' => $data->username, ':pass' => $hash, ':role' => $data->role, ':perms' => json_encode($data->permissions), ':at' => $agent_type, ':le' => $linked_entity, ':id' => $data->user_id]);
        } else {
            $stmt = $db->prepare("UPDATE users SET Username = :user, Role = :role, Permissions_JSON = :perms, Agent_Type = :at, Linked_Entity = :le WHERE User_ID = :id");
            $stmt->execute([':user' => $data->username, ':role' => $data->role, ':perms' => json_encode($data->permissions), ':at' => $agent_type, ':le' => $linked_entity, ':id' => $data->user_id]);
        }
    }
    echo json_encode(['status' => 'success']);
    exit;
}

if ($table === 'yl_discount_rates') {
    if ($method === 'GET') {
        $stmt = $db->query("SELECT YL_Disc_Orig, YL_Disc_Light, YL_Trade_Orig, YL_Trade_Light FROM company_profile WHERE Profile_ID = 1");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$res) { $res = ['YL_Disc_Orig' => 0.450, 'YL_Disc_Light' => 0.550, 'YL_Trade_Orig' => 0.500, 'YL_Trade_Light' => 0.700]; }
        echo json_encode($res);
    } elseif ($method === 'POST') {
        $stmt = $db->prepare("UPDATE company_profile SET YL_Disc_Orig = :orig, YL_Disc_Light = :light, YL_Trade_Orig = :torig, YL_Trade_Light = :tlight WHERE Profile_ID = 1");
        $stmt->execute([':orig' => $data->orig, ':light' => $data->light, ':torig' => $data->trade_orig, ':tlight' => $data->trade_light]);
        if ($stmt->rowCount() == 0) {
            $db->query("INSERT IGNORE INTO company_profile (Profile_ID, YL_Disc_Orig, YL_Disc_Light, YL_Trade_Orig, YL_Trade_Light) VALUES (1, {$data->orig}, {$data->light}, {$data->trade_orig}, {$data->trade_light})");
        }
        echo json_encode(['status' => 'success']);
    }
    exit;
}

if ($table === 'yl_rebate_matrix') {
    if ($method === 'GET') {
        $stmt = $db->query("SELECT * FROM yl_rebate_matrix ORDER BY Product_Type ASC, Min_Qty ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } elseif ($method === 'POST') {
        $db->beginTransaction();
        try {
            $type = $data->type ?? 'Original';
            $stmtDel = $db->prepare("DELETE FROM yl_rebate_matrix WHERE Product_Type = ?");
            $stmtDel->execute([$type]);
            $stmt = $db->prepare("INSERT INTO yl_rebate_matrix (Product_Type, Min_Qty, Max_Qty, Rebate_Amount) VALUES (?, ?, ?, ?)");
            if (!empty($data->tiers)) {
                foreach ($data->tiers as $tier) {
                    $stmt->execute([$type, $tier->min, $tier->max, $tier->amount]);
                }
            }
            $db->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    exit;
}

if ($table === 'system_dropdowns') {
    try {
        if ($method === 'GET') {
            $stmt = $db->prepare("SELECT * FROM system_dropdowns ORDER BY Dropdown_Type ASC, Option_Value ASC");
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } elseif ($method === 'POST') {
            if (!empty($data->id)) {
                $stmt = $db->prepare("UPDATE system_dropdowns SET Dropdown_Type = :dt, Option_Value = :ov, Parent_Link = :pl, Route_In_Charge = :ric, Center_Code = :ccode, Center_In_Charge = :cincharge, Linked_Warehouse_ID = :lwid WHERE ID = :id");
                $stmt->execute([':dt' => $data->Dropdown_Type ?? '', ':ov' => $data->Option_Value ?? '', ':pl' => $data->Parent_Link ?? '', ':ric' => $data->Route_In_Charge ?? null, ':ccode' => $data->Center_Code ?? null, ':cincharge' => $data->Center_In_Charge ?? null, ':lwid' => !empty($data->Linked_Warehouse_ID) ? $data->Linked_Warehouse_ID : null, ':id' => $data->id]);
            } else {
                $stmt = $db->prepare("INSERT INTO system_dropdowns (Dropdown_Type, Option_Value, Parent_Link, Route_In_Charge, Center_Code, Center_In_Charge, Linked_Warehouse_ID) VALUES (:dt, :ov, :pl, :ric, :ccode, :cincharge, :lwid)");
                $stmt->execute([':dt' => $data->Dropdown_Type ?? '', ':ov' => $data->Option_Value ?? '', ':pl' => $data->Parent_Link ?? '', ':ric' => $data->Route_In_Charge ?? null, ':ccode' => $data->Center_Code ?? null, ':cincharge' => $data->Center_In_Charge ?? null, ':lwid' => !empty($data->Linked_Warehouse_ID) ? $data->Linked_Warehouse_ID : null]);
            }
            echo json_encode(['status' => 'success']);
        } elseif ($method === 'DELETE') {
            $stmt = $db->prepare("DELETE FROM system_dropdowns WHERE ID = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['status' => 'success']);
        }
    } catch (PDOException $e) { echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); }
    exit;
}

if ($table === 'print_data' && $method === 'GET' && isset($_GET['type']) && $id) {
    $type = $_GET['type'];
    try {
        if ($type === 'accounting_pv') {
            $stmt = $db->prepare("SELECT pv.*, s.Supplier_Name, ap.Reference_No as AP_Ref FROM accounting_payment_vouchers pv JOIN suppliers s ON pv.Supplier_ID = s.Supplier_ID JOIN accounting_payables ap ON pv.AP_ID = ap.AP_ID WHERE pv.PV_ID = :id");
            $stmt->execute([':id' => $id]); echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: ['status' => 'error']);
        } elseif ($type === 'yl_delivery_receipts') {
            $stmt = $db->prepare("SELECT dr.*, so.SO_No, so.Total_Quantity, d.First_Name, d.Last_Name FROM yl_delivery_receipts dr JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID JOIN independent_dealers d ON so.Dealer_ID = d.Dealer_ID WHERE dr.DR_ID = :id");
            $stmt->execute([':id' => $id]); echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: ['status' => 'error']);
        } elseif ($type === 'accounting_gl') {
            $stmt = $db->prepare("SELECT * FROM accounting_journal WHERE Journal_ID = :id");
            $stmt->execute([':id' => $id]); $gl = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($gl) {
                $lines_stmt = $db->prepare("SELECT jl.*, c.Account_Code, c.Account_Name FROM accounting_journal_lines jl JOIN accounting_coa c ON jl.Account_ID = c.Account_ID WHERE jl.Journal_ID = :id");
                $lines_stmt->execute([':id' => $id]); $gl['lines'] = $lines_stmt->fetchAll(PDO::FETCH_ASSOC); echo json_encode($gl);
            } else { echo json_encode(['status' => 'error']); }
        } elseif ($type === 'ds_sales_orders') {
            $stmt = $db->prepare("SELECT so.*, o.Outlet_Name FROM ds_sales_orders so JOIN outlets o ON so.Outlet_ID = o.Outlet_ID WHERE so.SO_ID = :id");
            $stmt->execute([':id' => $id]); $so = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($so) { $so['items'] = json_decode($so['Items_JSON'], true); echo json_encode($so); } else { echo json_encode(['status' => 'error']); }
        } elseif ($type === 'yl_stock_orders') {
            $stmt = $db->prepare("SELECT so.*, d.First_Name, d.Last_Name FROM yl_stock_orders so JOIN independent_dealers d ON so.Dealer_ID = d.Dealer_ID WHERE so.SO_ID = :id");
            $stmt->execute([':id' => $id]); $so = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($so) { $so['items'] = json_decode($so['Items_JSON'], true); echo json_encode($so); } else { echo json_encode(['status' => 'error']); }
        } elseif ($type === 'ds_invoices') {
            $stmt = $db->prepare("SELECT i.*, so.SO_No, so.Items_JSON, o.Outlet_Name, o.Terms FROM ds_invoices i JOIN ds_sales_orders so ON i.SO_ID = so.SO_ID JOIN outlets o ON so.Outlet_ID = o.Outlet_ID WHERE i.Invoice_ID = :id");
            $stmt->execute([':id' => $id]); $inv = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($inv) { $inv['items'] = json_decode($inv['Items_JSON'], true); echo json_encode($inv); } else { echo json_encode(['status' => 'error']); }
        } elseif ($type === 'yl_invoices') {
            $stmt = $db->prepare("SELECT inv.*, COALESCE(inv.DR_Nos, dr.DR_No) as DR_Nos_Display, COALESCE(inv.Dealer_Name, CONCAT(d.First_Name, ' ', d.Last_Name)) as Dealer_Name_Display, COALESCE(inv.Items_JSON, dr.Items_JSON, so.Items_JSON) as Master_Items FROM yl_invoices inv LEFT JOIN yl_delivery_receipts dr ON inv.DR_ID = dr.DR_ID LEFT JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID LEFT JOIN independent_dealers d ON so.Dealer_ID = d.Dealer_ID WHERE inv.Invoice_ID = :id");
            $stmt->execute([':id' => $id]); $inv = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($inv) { $inv['items'] = json_decode($inv['Master_Items'], true); echo json_encode($inv); } else { echo json_encode(['status' => 'error']); }
        } elseif ($type === 'ds_collection_receipts') {
            $stmt = $db->prepare("SELECT * FROM ds_collection_receipts WHERE CR_ID = :id");
            $stmt->execute([':id' => $id]); echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: ['status' => 'error']);
        } elseif ($type === 'yl_collection_receipts') {
            $stmt = $db->prepare("SELECT * FROM yl_collection_receipts WHERE CR_ID = :id");
            $stmt->execute([':id' => $id]); echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: ['status' => 'error']);
        } elseif ($type === 'stock_returns') {
            $stmt = $db->prepare("SELECT sr.*, w.Warehouse_Name FROM stock_returns sr JOIN warehouses w ON sr.Warehouse_ID = w.Warehouse_ID WHERE sr.Return_ID = :id");
            $stmt->execute([':id' => $id]); $rt = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($rt) { $rt['items'] = json_decode($rt['Items_JSON'], true); echo json_encode($rt); } else { echo json_encode(['status' => 'error']); }
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Print data could not be retrieved.']); 
    }
    exit;
}

if ($table === 'stock_position_report' && isset($_GET['date']) && isset($_GET['type'])) {
    $date = $_GET['date'];
    $type = $_GET['type'];
    $orig_id = $type === 'YL' ? 3 : 1; 
    $light_id = $type === 'YL' ? 4 : 2;
    
    try {
        $beg_orig = $db->query("SELECT COALESCE(SUM(Qty_In - Qty_Out), 0) FROM inventory_ledger WHERE Product_ID = $orig_id AND Transaction_Date < '$date' AND Warehouse_ID = 1")->fetchColumn();
        $beg_light = $db->query("SELECT COALESCE(SUM(Qty_In - Qty_Out), 0) FROM inventory_ledger WHERE Product_ID = $light_id AND Transaction_Date < '$date' AND Warehouse_ID = 1")->fetchColumn();
        
        $factory_orig = $db->query("SELECT COALESCE(SUM(Qty_In), 0) FROM inventory_ledger WHERE Product_ID = $orig_id AND Transaction_Date = '$date' AND Transaction_Type = 'Stock In' AND Warehouse_ID = 1")->fetchColumn();
        $factory_light = $db->query("SELECT COALESCE(SUM(Qty_In), 0) FROM inventory_ledger WHERE Product_ID = $light_id AND Transaction_Date = '$date' AND Transaction_Type = 'Stock In' AND Warehouse_ID = 1")->fetchColumn();
        
        $rtv_orig = $db->query("SELECT COALESCE(SUM(Qty_In), 0) FROM inventory_ledger WHERE Product_ID = $orig_id AND Transaction_Date = '$date' AND Transaction_Type = 'Return In' AND Warehouse_ID = 1")->fetchColumn();
        $rtv_light = $db->query("SELECT COALESCE(SUM(Qty_In), 0) FROM inventory_ledger WHERE Product_ID = $light_id AND Transaction_Date = '$date' AND Transaction_Type = 'Return In' AND Warehouse_ID = 1")->fetchColumn();
        
        $sales = [];
        if ($type === 'YL') {
            $stmt = $db->query("SELECT d.Center, so.Items_JSON FROM yl_stock_orders so JOIN independent_dealers d ON so.Dealer_ID = d.Dealer_ID WHERE so.SO_Date = '$date'");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $center = $row['Center'] ?: 'Unassigned';
                if (!isset($sales[$center])) $sales[$center] = ['orig' => 0, 'light' => 0];
                
                $items = json_decode($row['Items_JSON'], true);
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $qty = (int)($item['quantity'] ?? 0);
                        $name = strtoupper($item['product_name'] ?? '');
                        if (strpos($name, 'ORIGINAL') !== false) { $sales[$center]['orig'] += $qty; } 
                        elseif (strpos($name, 'LIGHT') !== false) { $sales[$center]['light'] += $qty; }
                    }
                }
            }
        } else {
            $stmt = $db->query("SELECT COALESCE(o.Branch, 'Walk-in') as Center, so.Items_JSON FROM ds_sales_orders so JOIN outlets o ON so.Outlet_ID = o.Outlet_ID WHERE so.SO_Date = '$date'");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $center = $row['Center'];
                if (!isset($sales[$center])) $sales[$center] = ['orig' => 0, 'light' => 0];
                
                $items = json_decode($row['Items_JSON'], true);
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $qty = (int)($item['quantity'] ?? 0);
                        $name = strtoupper($item['product_name'] ?? '');
                        if (strpos($name, 'ORIGINAL') !== false) { $sales[$center]['orig'] += $qty; } 
                        elseif (strpos($name, 'LIGHT') !== false) { $sales[$center]['light'] += $qty; }
                    }
                }
            }
        }
        
        $transfers = [];
        $stmt2 = $db->query("SELECT w.Warehouse_Name, st.Items_JSON FROM stock_transfers st JOIN warehouses w ON st.To_Warehouse_ID = w.Warehouse_ID WHERE st.Transfer_Date = '$date' AND st.From_Warehouse_ID = 1");
        while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $wh = $row['Warehouse_Name'];
            if (!isset($transfers[$wh])) $transfers[$wh] = ['orig' => 0, 'light' => 0];
            
            $items = json_decode($row['Items_JSON'], true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    $qty = (int)($item['quantity'] ?? 0);
                    $name = strtoupper($item['product_name'] ?? '');
                    if (strpos($name, 'ORIGINAL') !== false) { $transfers[$wh]['orig'] += $qty; } 
                    elseif (strpos($name, 'LIGHT') !== false) { $transfers[$wh]['light'] += $qty; }
                }
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'date' => $date,
            'type' => $type,
            'beginning' => ['orig' => $beg_orig, 'light' => $beg_light],
            'additions_factory' => ['orig' => $factory_orig, 'light' => $factory_light],
            'additions_rtv' => ['orig' => $rtv_orig, 'light' => $rtv_light],
            'sales' => $sales,
            'transfers' => $transfers
        ]);
    } catch (Exception $e) { 
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); 
    }
    exit;
}
?>