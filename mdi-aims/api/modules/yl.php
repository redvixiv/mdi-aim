<?php
// Auto-migrate database schema to support new columns
try { $db->exec("ALTER TABLE yl_delivery_receipts ADD COLUMN Items_JSON TEXT NULL"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE yl_delivery_receipts ADD COLUMN Total_Quantity INT(11) DEFAULT 0"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE yl_delivery_receipts ADD COLUMN Total_Amount DECIMAL(15,2) DEFAULT 0.00"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE yl_invoices ADD COLUMN DR_IDs_JSON TEXT NULL"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE yl_invoices ADD COLUMN DR_Nos TEXT NULL"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE yl_invoices ADD COLUMN Dealer_Name VARCHAR(255) NULL"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE yl_invoices ADD COLUMN Items_JSON TEXT NULL"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE yl_invoices ADD COLUMN Discount_Orig_Amount DECIMAL(15,2) DEFAULT 0.00"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE yl_invoices ADD COLUMN Discount_Light_Amount DECIMAL(15,2) DEFAULT 0.00"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE yl_invoices ADD COLUMN Trade_Orig_Amount DECIMAL(15,2) DEFAULT 0.00"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE yl_invoices ADD COLUMN Trade_Light_Amount DECIMAL(15,2) DEFAULT 0.00"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE yl_collection_receipts ADD COLUMN Actual_Cash_Received DECIMAL(15,2) DEFAULT NULL"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE yl_delivery_receipts ADD COLUMN Is_Advance_Delivery TINYINT(1) DEFAULT 0"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE stock_returns ADD COLUMN Total_Amount DECIMAL(15,2) DEFAULT 0.00"); } catch (PDOException $e) {}

if ($method === 'GET') {
    if ($table === 'yl_stock_orders') {
        $stmt = $db->prepare("SELECT so.*, d.First_Name, d.Last_Name FROM yl_stock_orders so JOIN independent_dealers d ON so.Dealer_ID = d.Dealer_ID ORDER BY so.SO_ID DESC");
        $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    elseif ($table === 'yl_delivery_receipts') {
        $stmt = $db->prepare("SELECT dr.*, so.SO_No, d.First_Name, d.Last_Name FROM yl_delivery_receipts dr JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID JOIN independent_dealers d ON so.Dealer_ID = d.Dealer_ID ORDER BY dr.DR_ID DESC");
        $stmt->execute(); 
        $drs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Dynamically calculate and deduct Stock Returns linked to the SO Reference
        foreach($drs as &$dr) {
            $ret_stmt = $db->prepare("SELECT Items_JSON FROM stock_returns WHERE Reference_No = :ref");
            $ret_stmt->execute([':ref' => $dr['SO_No']]);
            $returns = $ret_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $ret_qty = 0;
            $ret_amt = 0;
            foreach($returns as $rt) {
                $items = json_decode($rt['Items_JSON'], true);
                if($items){
                    foreach($items as $i) { 
                        $ret_qty += (isset($i['quantity']) ? (int)$i['quantity'] : 0);
                        $ret_amt += (isset($i['subtotal']) ? (float)$i['subtotal'] : 0); 
                    }
                }
            }
            $dr['Returned_Qty'] = $ret_qty;
            $dr['Returned_Amount'] = $ret_amt;
        }
        
        echo json_encode($drs);
        exit;
    }
    // FIXED: YL specific return fetcher
    elseif ($table === 'yl_stock_returns') {
        $stmt = $db->prepare("SELECT r.*, w.Warehouse_Name FROM stock_returns r LEFT JOIN warehouses w ON r.Warehouse_ID = w.Warehouse_ID ORDER BY r.Return_ID DESC");
        $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    elseif ($table === 'yl_invoices') {
        $stmt = $db->prepare("SELECT inv.*, COALESCE(inv.DR_Nos, dr.DR_No) as DR_Nos_Display, COALESCE(inv.Dealer_Name, CONCAT(d.First_Name, ' ', d.Last_Name)) as Dealer_Name_Display FROM yl_invoices inv LEFT JOIN yl_delivery_receipts dr ON inv.DR_ID = dr.DR_ID LEFT JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID LEFT JOIN independent_dealers d ON so.Dealer_ID = d.Dealer_ID ORDER BY inv.Invoice_ID DESC");
        $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    elseif ($table === 'yl_collection_receipts') {
        $stmt = $db->prepare("SELECT * FROM yl_collection_receipts ORDER BY CR_ID DESC");
        $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    elseif ($table === 'yl_unpaid_invoices') {
        $did = $_GET['dealer_id'] ?? 0;
        $stmt = $db->prepare("SELECT inv.*, COALESCE(inv.DR_Nos, dr.DR_No) as DR_Nos_Display FROM yl_invoices inv LEFT JOIN yl_delivery_receipts dr ON inv.DR_ID = dr.DR_ID LEFT JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID WHERE (so.Dealer_ID = :did OR inv.Dealer_Name = (SELECT CONCAT(First_Name, ' ', Last_Name) FROM independent_dealers WHERE Dealer_ID = :did2 LIMIT 1)) AND (so.Payment_Status != 'Paid' OR so.Payment_Status IS NULL) ORDER BY inv.Invoice_ID DESC");
        $stmt->execute([':did' => $did, ':did2' => $did]); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    elseif ($table === 'yl_dealers_with_unpaid_invoices') {
        $stmt = $db->prepare("SELECT DISTINCT d.Dealer_ID, d.First_Name, d.Last_Name FROM independent_dealers d JOIN yl_stock_orders so ON d.Dealer_ID = so.Dealer_ID JOIN yl_delivery_receipts dr ON so.SO_ID = dr.SO_ID JOIN yl_invoices inv ON dr.DR_ID = inv.DR_ID WHERE so.Payment_Status != 'Paid' ORDER BY d.First_Name ASC");
        $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    elseif ($table === 'yl_sales_report') {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-t');
        $stmt = $db->prepare("
            SELECT inv.Invoice_ID, inv.Invoice_Date, inv.Invoice_No, 
                   COALESCE(inv.DR_Nos, dr.DR_No) as DR_No, so.SO_No, 
                   COALESCE(inv.Dealer_Name, CONCAT(d.First_Name, ' ', d.Last_Name)) as Dealer_Name, 
                   COALESCE(inv.Amount_Due, so.Total_Amount) as Gross_Sales, inv.VAT, inv.Amount_Due, 
                   so.Payment_Status, COALESCE(inv.Items_JSON, so.Items_JSON) as Items_JSON, 
                   GROUP_CONCAT(cr.CR_No SEPARATOR ', ') as CR_No 
            FROM yl_invoices inv 
            LEFT JOIN yl_delivery_receipts dr ON inv.DR_ID = dr.DR_ID 
            LEFT JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID 
            LEFT JOIN independent_dealers d ON so.Dealer_ID = d.Dealer_ID 
            LEFT JOIN yl_collection_receipts cr ON cr.Invoice_IDs_JSON LIKE CONCAT('%\"', inv.Invoice_ID, '\"%')
            WHERE inv.Invoice_Date BETWEEN :from AND :to 
            GROUP BY inv.Invoice_ID
            ORDER BY inv.Invoice_Date ASC, inv.Invoice_No ASC
        ");
        $stmt->execute([':from' => $from, ':to' => $to]); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    elseif ($table === 'yl_remittance_report') {
        $date = $_GET['date'] ?? date('Y-m-d');
        $center = $_GET['center'] ?? '';
        
        $dealers_query = "SELECT Dealer_ID, First_Name, Last_Name FROM independent_dealers WHERE Status = 'Active'";
        if (!empty($center)) { $dealers_query .= " AND Center = " . $db->quote($center); }
        $dealers = $db->query($dealers_query)->fetchAll(PDO::FETCH_ASSOC);

        $report_data = [];
        foreach ($dealers as $d) {
            $did = $d['Dealer_ID'];
            
            // 1. AR Calculations
            $past_inv = (float)$db->query("SELECT COALESCE(SUM(Amount_Due),0) FROM yl_invoices i JOIN yl_delivery_receipts dr ON i.DR_ID = dr.DR_ID JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID WHERE so.Dealer_ID = $did AND i.Invoice_Date < '$date'")->fetchColumn();
            $past_cr = (float)$db->query("SELECT COALESCE(SUM(Total_Amount_Due),0) FROM yl_collection_receipts WHERE Dealer_Name = '{$d['First_Name']} {$d['Last_Name']}' AND CR_Date < '$date'")->fetchColumn();
            $beg_ar = $past_inv - $past_cr;

            $today_inv = (float)$db->query("SELECT COALESCE(SUM(Amount_Due),0) FROM yl_invoices i JOIN yl_delivery_receipts dr ON i.DR_ID = dr.DR_ID JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID WHERE so.Dealer_ID = $did AND i.Invoice_Date = '$date'")->fetchColumn();
            $today_cr = (float)$db->query("SELECT COALESCE(SUM(Total_Amount_Due),0) FROM yl_collection_receipts WHERE Dealer_Name = '{$d['First_Name']} {$d['Last_Name']}' AND CR_Date = '$date'")->fetchColumn();
            
            // Short/Excess
            $actual_cash = (float)$db->query("SELECT COALESCE(SUM(Actual_Cash_Received), SUM(Total_Amount_Due)) FROM yl_collection_receipts WHERE Dealer_Name = '{$d['First_Name']} {$d['Last_Name']}' AND CR_Date = '$date'")->fetchColumn();
            $short_excess = $actual_cash - $today_cr;

            $end_ar = $beg_ar + $today_inv - $today_cr;

            // 2. Fetch Transaction Numbers
            $dr_nos = $db->query("SELECT GROUP_CONCAT(dr.DR_No SEPARATOR ', ') FROM yl_delivery_receipts dr JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID WHERE so.Dealer_ID = $did AND dr.DR_Date = '$date'")->fetchColumn();
            $inv_nos = $db->query("SELECT GROUP_CONCAT(i.Invoice_No SEPARATOR ', ') FROM yl_invoices i JOIN yl_delivery_receipts dr ON i.DR_ID = dr.DR_ID JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID WHERE so.Dealer_ID = $did AND i.Invoice_Date = '$date'")->fetchColumn();
            $adv_del = $db->query("SELECT GROUP_CONCAT(dr.DR_No SEPARATOR ', ') FROM yl_delivery_receipts dr JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID WHERE so.Dealer_ID = $did AND dr.DR_Date = '$date' AND dr.Is_Advance_Delivery = 1")->fetchColumn();

            // 3. Stock Calculations
            $past_dr_qty = (float)$db->query("SELECT COALESCE(SUM(dr.Total_Quantity),0) FROM yl_delivery_receipts dr JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID WHERE so.Dealer_ID = $did AND dr.DR_Date < '$date'")->fetchColumn();
            $past_inv_qty = (float)$db->query("SELECT COALESCE(SUM(dr.Total_Quantity),0) FROM yl_invoices i JOIN yl_delivery_receipts dr ON i.DR_ID = dr.DR_ID JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID WHERE so.Dealer_ID = $did AND i.Invoice_Date < '$date'")->fetchColumn();
            $beg_stock = $past_dr_qty - $past_inv_qty;

            $today_dr_qty = (float)$db->query("SELECT COALESCE(SUM(dr.Total_Quantity),0) FROM yl_delivery_receipts dr JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID WHERE so.Dealer_ID = $did AND dr.DR_Date = '$date'")->fetchColumn();
            $today_inv_qty = (float)$db->query("SELECT COALESCE(SUM(dr.Total_Quantity),0) FROM yl_invoices i JOIN yl_delivery_receipts dr ON i.DR_ID = dr.DR_ID JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID WHERE so.Dealer_ID = $did AND i.Invoice_Date = '$date'")->fetchColumn();
            $running_stock = $beg_stock + $today_dr_qty - $today_inv_qty;

            if ($today_dr_qty > 0 || $today_inv > 0 || $today_cr > 0 || $beg_ar > 0 || $beg_stock > 0) {
                $report_data[] = [
                    'yl_name' => "{$d['Last_Name']}, {$d['First_Name']}",
                    'dr_nos' => $dr_nos ?: '-',
                    'inv_nos' => $inv_nos ?: '-',
                    'adv_del' => $adv_del ?: '-',
                    'beg_ar' => $beg_ar,
                    'today_sales' => $today_inv,
                    'today_col' => $today_cr,
                    'short_excess' => $short_excess,
                    'end_ar' => $end_ar,
                    'beg_stock' => $beg_stock,
                    'add_stock' => $today_dr_qty,
                    'ret_stock' => 0, 
                    'running_stock' => $running_stock
                ];
            }
        }
        echo json_encode(['status' => 'success', 'date' => $date, 'data' => $report_data]);
        exit;
    }
} 
elseif ($method === 'POST') {
    if ($table === 'yl_stock_orders') {
        $q = $db->query("SELECT MAX(CAST(REPLACE(SO_No, 'SO-YL-', '') AS UNSIGNED)) AS max_no FROM yl_stock_orders WHERE SO_No LIKE 'SO-YL-%' AND SO_No NOT LIKE 'SO-YL-%-%'");
        $row = $q->fetch(PDO::FETCH_ASSOC);
        $next_no = $row['max_no'] ? $row['max_no'] + 1 : 100001;
        $so_no = "SO-YL-" . $next_no;

        $stmt = $db->prepare("INSERT INTO yl_stock_orders (SO_No, SO_Date, Dealer_ID, Items_JSON, Total_Quantity, Total_Amount, Payment_Status) VALUES (:sono, :sodate, :did, :items, :tq, :tamt, 'Pending')");
        $stmt->execute([':sono' => $so_no, ':sodate' => $data->so_date, ':did' => $data->dealer_id, ':items' => json_encode($data->items), ':tq' => $data->total_qty, ':tamt' => $data->total_amount]);
        echo json_encode(['status' => 'success']);
        exit;
    }
    elseif ($table === 'yl_delivery_receipts') {
        $q = $db->query("SELECT MAX(CAST(REPLACE(DR_No, 'DR-YL-', '') AS UNSIGNED)) AS max_no FROM yl_delivery_receipts WHERE DR_No LIKE 'DR-YL-%' AND DR_No NOT LIKE 'DR-YL-%-%'");
        $row = $q->fetch(PDO::FETCH_ASSOC);
        $next_no = $row['max_no'] ? $row['max_no'] + 1 : 100001;
        $dr_no = "DR-YL-" . $next_no;

        $stmt = $db->prepare("INSERT INTO yl_delivery_receipts (DR_No, DR_Date, SO_ID, Items_JSON, Total_Quantity, Total_Amount) VALUES (:drno, :drdate, :soid, :items, :tq, :tamt)");
        $stmt->execute([':drno' => $dr_no, ':drdate' => $data->dr_date, ':soid' => $data->so_id, ':items' => json_encode($data->items), ':tq' => $data->total_qty, ':tamt' => $data->total_amt]);
        echo json_encode(['status' => 'success']);
        exit;
    }
    // FIXED: CPA Compliant Stock Return Post Logic (Now safely mapped to yl_stock_returns)
    elseif ($table === 'yl_stock_returns') {
        $stmt = $db->prepare("INSERT INTO stock_returns (Return_No, Return_Date, Warehouse_ID, Return_Type, Reference_No, Items_JSON, Total_Quantity, Remarks, Total_Amount) VALUES (:rno, :rdate, :wid, :rtype, :ref, :items, :tq, :rem, :tamt)");
        
        $total_amount = 0;
        $total_cogs = 0;
        
        if (!empty($data->items)) {
            foreach ($data->items as $item) {
                $qty = (int)$item->quantity;
                $price = (float)$item->unit_price;
                $total_amount += ($qty * $price);
                
                // Get Unit Cost for COGS
                $cost_stmt = $db->prepare("SELECT Unit_Cost FROM product_pricing WHERE Product_ID = :pid ORDER BY Effective_From DESC LIMIT 1");
                $cost_stmt->execute([':pid' => $item->product_id]);
                $cost = (float)$cost_stmt->fetchColumn();
                $total_cogs += ($cost * $qty);
            }
        }
        
        $stmt->execute([
            ':rno' => $data->return_no,
            ':rdate' => $data->return_date,
            ':wid' => $data->warehouse_id,
            ':rtype' => $data->return_type,
            ':ref' => $data->reference_no,
            ':items' => json_encode($data->items),
            ':tq' => $data->total_qty,
            ':rem' => $data->remarks,
            ':tamt' => $total_amount
        ]);
        
        // 1. INVENTORY LEDGER ENTRIES
        if (!empty($data->items)) {
            $led_stmt = $db->prepare("INSERT INTO inventory_ledger (Transaction_Date, Warehouse_ID, Product_ID, Transaction_Type, Reference_No, Qty_In, Qty_Out) VALUES (:d, :wid, :p, :ttype, :ref, :qi, :qo)");
            
            foreach ($data->items as $item) {
                if ($data->return_type === 'Customer Return') {
                    $led_stmt->execute([':d' => $data->return_date, ':wid' => $data->warehouse_id, ':p' => $item->product_id, ':ttype' => 'Return In', ':ref' => $data->return_no, ':qi' => $item->quantity, ':qo' => 0]);
                } else {
                    $led_stmt->execute([':d' => $data->return_date, ':wid' => $data->warehouse_id, ':p' => $item->product_id, ':ttype' => 'Return Out', ':ref' => $data->return_no, ':qi' => 0, ':qo' => $item->quantity]);
                }
            }
        }

        // 2. ACCOUNTING / GENERAL LEDGER ENTRIES
        $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)");
        $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
        
        $sra_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '4100'")->fetchColumn();
        if (!$sra_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('4100', 'Sales Returns and Allowances', 'Revenue')"); $sra_id = $db->lastInsertId(); }
        
        $ar_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1200'")->fetchColumn();
        $vat_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '2100'")->fetchColumn();
        $inv_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1300'")->fetchColumn();
        $cogs_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '5000'")->fetchColumn();
        $ap_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '2000'")->fetchColumn();

        if ($data->return_type === 'Customer Return' && $total_amount > 0) {
            $j_stmt->execute([':d' => $data->return_date, ':ref' => $data->return_no, ':desc' => "Customer Return - " . $data->return_no]);
            $jid = $db->lastInsertId();
            
            $net = $total_amount / 1.12;
            $vat = $total_amount - $net;
            
            if ($sra_id) $l_stmt->execute([':jid' => $jid, ':aid' => $sra_id, ':deb' => $net, ':cred' => 0]);
            if ($vat > 0 && $vat_id) $l_stmt->execute([':jid' => $jid, ':aid' => $vat_id, ':deb' => $vat, ':cred' => 0]);
            if ($ar_id) $l_stmt->execute([':jid' => $jid, ':aid' => $ar_id, ':deb' => 0, ':cred' => $total_amount]);
            
            if ($total_cogs > 0 && $inv_id && $cogs_id) {
                $j_stmt->execute([':d' => $data->return_date, ':ref' => $data->return_no, ':desc' => "COGS Reversal for Return - " . $data->return_no]);
                $jid2 = $db->lastInsertId();
                $l_stmt->execute([':jid' => $jid2, ':aid' => $inv_id, ':deb' => $total_cogs, ':cred' => 0]);
                $l_stmt->execute([':jid' => $jid2, ':aid' => $cogs_id, ':deb' => 0, ':cred' => $total_cogs]);
            }
        } 
        elseif ($data->return_type === 'Return to Supplier' && $total_cogs > 0) {
            $j_stmt->execute([':d' => $data->return_date, ':ref' => $data->return_no, ':desc' => "Return to Supplier - " . $data->return_no]);
            $jid = $db->lastInsertId();
            
            if ($ap_id) $l_stmt->execute([':jid' => $jid, ':aid' => $ap_id, ':deb' => $total_cogs, ':cred' => 0]);
            if ($inv_id) $l_stmt->execute([':jid' => $jid, ':aid' => $inv_id, ':deb' => 0, ':cred' => $total_cogs]);
        }
        
        echo json_encode(['status' => 'success']);
        exit;
    }
    elseif ($table === 'yl_invoices') {
        $stmt = $db->prepare("INSERT INTO yl_invoices (Invoice_No, Invoice_Date, DR_ID, DR_IDs_JSON, DR_Nos, Dealer_Name, Items_JSON, Net_Amount, VAT, Amount_Due, Discount_Orig_Amount, Discount_Light_Amount, Trade_Orig_Amount, Trade_Light_Amount) VALUES (:invno, :invdate, :drid, :dr_ids, :dr_nos, :dname, :items, :net, :vat, :amtdue, :disc_orig, :disc_light, :trade_orig, :trade_light)");
        $stmt->execute([
            ':invno' => $data->invoice_no, 
            ':invdate' => $data->invoice_date, 
            ':drid' => !empty($data->dr_ids[0]) ? $data->dr_ids[0] : null,
            ':dr_ids' => json_encode($data->dr_ids),
            ':dr_nos' => $data->dr_nos,
            ':dname' => $data->dealer_name,
            ':items' => json_encode($data->items),
            ':net' => $data->net_amount, 
            ':vat' => $data->vat, 
            ':amtdue' => $data->amount_due,
            ':disc_orig' => $data->discount_orig_amount ?? 0,
            ':disc_light' => $data->discount_light_amount ?? 0,
            ':trade_orig' => $data->trade_orig_amount ?? 0,
            ':trade_light' => $data->trade_light_amount ?? 0
        ]);
        
        $ar_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1200'")->fetchColumn();
        if (!$ar_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1200', 'Accounts Receivable', 'Asset')"); $ar_id = $db->lastInsertId(); }
        $sales_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '4000'")->fetchColumn();
        if (!$sales_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('4000', 'Sales Revenue', 'Revenue')"); $sales_id = $db->lastInsertId(); }
        
        $vat_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '2100'")->fetchColumn();
        if (!$vat_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('2100', 'Output VAT Payable', 'Liability')"); $vat_id = $db->lastInsertId(); }

        if ($ar_id && $sales_id && $vat_id) {
            $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)"); $j_stmt->execute([':d' => $data->invoice_date, ':ref' => $data->invoice_no, ':desc' => "Auto-generated from YL Invoice " . $data->invoice_no]); $jid = $db->lastInsertId();
            $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
            
            $net = (float)($data->net_amount ?? 0);
            $vat = (float)($data->vat ?? 0);
            $due = (float)($data->amount_due ?? 0);

            $l_stmt->execute([':jid' => $jid, ':aid' => $ar_id, ':deb' => $due, ':cred' => 0]); 
            $l_stmt->execute([':jid' => $jid, ':aid' => $sales_id, ':deb' => 0, ':cred' => $net]);
            if($vat > 0) {
                $l_stmt->execute([':jid' => $jid, ':aid' => $vat_id, ':deb' => 0, ':cred' => $vat]);
            }
        }
        
        $total_cogs = 0;
        if (!empty($data->items)) {
            foreach ($data->items as $item) {
                $cost_stmt = $db->prepare("SELECT Unit_Cost FROM product_pricing WHERE Product_ID = :pid AND Effective_From <= :idate AND (Effective_To IS NULL OR Effective_To = '0000-00-00' OR Effective_To = '' OR Effective_To >= :idate) ORDER BY Effective_From DESC LIMIT 1");
                $cost_stmt->execute([':pid' => $item->product_id, ':idate' => $data->invoice_date]); $cost_data = $cost_stmt->fetch(PDO::FETCH_ASSOC);
                $total_cogs += (($cost_data ? (float)$cost_data['Unit_Cost'] : 0) * (float)$item->quantity);
            }
        }
        if ($total_cogs > 0) {
            $inv_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1300'")->fetchColumn();
            if (!$inv_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1300', 'Inventory Asset', 'Asset')"); $inv_id = $db->lastInsertId(); }
            $cogs_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '5000'")->fetchColumn();
            if (!$cogs_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('5000', 'Cost of Goods Sold', 'Expense')"); $cogs_id = $db->lastInsertId(); }
            
            if ($inv_id && $cogs_id) {
                $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)"); $j_stmt->execute([':d' => $data->invoice_date, ':ref' => $data->invoice_no, ':desc' => "COGS for YL Invoice " . $data->invoice_no]); $jid = $db->lastInsertId();
                $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
                $l_stmt->execute([':jid' => $jid, ':aid' => $cogs_id, ':deb' => $total_cogs, ':cred' => 0]); $l_stmt->execute([':jid' => $jid, ':aid' => $inv_id, ':deb' => 0, ':cred' => $total_cogs]); 
            }
        }

        if (!empty($data->items)) {
            $w_stmt = $db->query("SELECT Warehouse_ID FROM warehouses ORDER BY Warehouse_ID ASC LIMIT 1");
            $target_wid = $w_stmt->fetchColumn() ?: 1;

            if (!empty($data->warehouse_name)) {
                $match_stmt = $db->prepare("SELECT Warehouse_ID FROM warehouses WHERE Warehouse_Name = :cname OR Warehouse_Name LIKE :cnamelike LIMIT 1");
                $match_stmt->execute([':cname' => $data->warehouse_name, ':cnamelike' => '%' . $data->warehouse_name . '%']);
                $found_wid = $match_stmt->fetchColumn();
                if ($found_wid) $target_wid = $found_wid;
            }

            $led_stmt = $db->prepare("INSERT INTO inventory_ledger (Transaction_Date, Warehouse_ID, Product_ID, Transaction_Type, Reference_No, Qty_In, Qty_Out) VALUES (:d, :wid, :p, 'Sales', :ref, 0, :qo)");
            foreach ($data->items as $item) {
                if((int)$item->quantity > 0) {
                    $led_stmt->execute([':d' => $data->invoice_date, ':wid' => $target_wid, ':p' => $item->product_id, ':ref' => 'INV-'.$data->invoice_no, ':qo' => $item->quantity]);
                }
            }
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
    elseif ($table === 'yl_collection_receipts') {
        $stmt = $db->prepare("INSERT INTO yl_collection_receipts (CR_No, CR_Date, Invoice_IDs_JSON, Dealer_Name, Total_Amount_Due, Total_Amount_Words) VALUES (:crno, :crdate, :inv_ids, :dname, :tdue, :words)");
        $stmt->execute([':crno' => $data->cr_no, ':crdate' => $data->cr_date, ':inv_ids' => json_encode($data->invoice_ids), ':dname' => $data->dealer_name, ':tdue' => $data->total_amount_due, ':words' => $data->total_words]);

        if (!empty($data->invoice_ids)) {
            $in = implode(',', array_fill(0, count($data->invoice_ids), '?'));
            $inv_stmt = $db->prepare("SELECT DR_ID, DR_IDs_JSON FROM yl_invoices WHERE Invoice_ID IN ($in)");
            $inv_stmt->execute($data->invoice_ids);
            $invoices = $inv_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $all_dr_ids = [];
            foreach ($invoices as $inv) {
                if (!empty($inv['DR_IDs_JSON'])) {
                    $arr = json_decode($inv['DR_IDs_JSON'], true);
                    if(is_array($arr)) $all_dr_ids = array_merge($all_dr_ids, $arr);
                } elseif (!empty($inv['DR_ID'])) {
                    $all_dr_ids[] = $inv['DR_ID'];
                }
            }
            
            if (!empty($all_dr_ids)) {
                $dr_in = implode(',', array_fill(0, count($all_dr_ids), '?'));
                $update_stmt = $db->prepare("UPDATE yl_stock_orders so JOIN yl_delivery_receipts dr ON so.SO_ID = dr.SO_ID SET so.Payment_Status = 'Paid' WHERE dr.DR_ID IN ($dr_in)"); 
                $update_stmt->execute($all_dr_ids);
            }
        }
        
        $cash_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1010'")->fetchColumn();
        if (!$cash_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1010', 'Cash on Hand', 'Asset')"); $cash_id = $db->lastInsertId(); }
        $ar_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1200'")->fetchColumn();
        if (!$ar_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1200', 'Accounts Receivable', 'Asset')"); $ar_id = $db->lastInsertId(); }
        
        if ($cash_id && $ar_id) {
            $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)"); $j_stmt->execute([':d' => $data->cr_date, ':ref' => $data->cr_no, ':desc' => "Auto-generated from YL CR " . $data->cr_no]); $jid = $db->lastInsertId();
            $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
            $l_stmt->execute([':jid' => $jid, ':aid' => $cash_id, ':deb' => $data->total_amount_due, ':cred' => 0]); $l_stmt->execute([':jid' => $jid, ':aid' => $ar_id, ':deb' => 0, ':cred' => $data->total_amount_due]);
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
}
elseif ($method === 'DELETE') {
    if ($table === 'yl_stock_orders' && $id) { 
        $check = $db->prepare("SELECT COUNT(*) FROM yl_delivery_receipts WHERE SO_ID = :id");
        $check->execute([':id' => $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete Stock Order. A Delivery Receipt has already been issued for this order.']);
            exit;
        }

        $stmt = $db->prepare("DELETE FROM yl_stock_orders WHERE SO_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
        exit;
    }
    // FIXED: Route to cleanly delete the YL Return and its CPA Journal Entries
    elseif ($table === 'yl_stock_returns' && $id) {
        $sel = $db->prepare("SELECT Return_No FROM stock_returns WHERE Return_ID = :id");
        $sel->execute([':id' => $id]);
        $ret = $sel->fetch(PDO::FETCH_ASSOC);
        
        if ($ret && !empty($ret['Return_No'])) {
            $ret_ref = $ret['Return_No'];
            $db->prepare("DELETE FROM inventory_ledger WHERE Reference_No = :ref")->execute([':ref' => $ret_ref]);
            
            $find_j = $db->prepare("SELECT Journal_ID FROM accounting_journal WHERE Reference_No = :ref OR Description LIKE :desc");
            $find_j->execute([':ref' => $ret_ref, ':desc' => "%$ret_ref%"]);
            $journals = $find_j->fetchAll(PDO::FETCH_ASSOC);
            foreach($journals as $j) {
                $db->prepare("DELETE FROM accounting_journal_lines WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
                $db->prepare("DELETE FROM accounting_journal WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
            }
        }
        
        $stmt = $db->prepare("DELETE FROM stock_returns WHERE Return_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
        exit;
    }
    elseif ($table === 'yl_delivery_receipts' && $id) { 
        $check = $db->prepare("SELECT COUNT(*) FROM yl_invoices WHERE DR_ID = :id OR DR_IDs_JSON LIKE :json_id");
        $check->execute([':id' => $id, ':json_id' => '%"'.$id.'"%']);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete Delivery Receipt. An Invoice has already been issued against it.']);
            exit;
        }

        $stmt = $db->prepare("DELETE FROM yl_delivery_receipts WHERE DR_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
        exit;
    }
    elseif ($table === 'yl_invoices' && $id) { 
        $check = $db->prepare("SELECT COUNT(*) FROM yl_collection_receipts WHERE Invoice_IDs_JSON LIKE :id");
        $check->execute([':id' => '%"'.$id.'"%']);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete Invoice. A Collection Receipt has already been issued against it.']);
            exit;
        }

        $sel = $db->prepare("SELECT Invoice_No FROM yl_invoices WHERE Invoice_ID = :id");
        $sel->execute([':id' => $id]);
        $inv = $sel->fetch(PDO::FETCH_ASSOC);
        
        if ($inv && !empty($inv['Invoice_No'])) {
            $inv_ref = $inv['Invoice_No'];
            $led_ref = 'INV-' . $inv_ref;
            $db->prepare("DELETE FROM inventory_ledger WHERE Reference_No = :ref")->execute([':ref' => $led_ref]);
            
            $find_j = $db->prepare("SELECT Journal_ID FROM accounting_journal WHERE Reference_No = :ref");
            $find_j->execute([':ref' => $inv_ref]);
            $journals = $find_j->fetchAll(PDO::FETCH_ASSOC);
            foreach($journals as $j) {
                $db->prepare("DELETE FROM accounting_journal_lines WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
                $db->prepare("DELETE FROM accounting_journal WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
            }
        }
        
        $stmt = $db->prepare("DELETE FROM yl_invoices WHERE Invoice_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
        exit;
    }
    elseif ($table === 'yl_collection_receipts' && $id) { 
        $fetch_cr = $db->prepare("SELECT CR_No, Invoice_IDs_JSON FROM yl_collection_receipts WHERE CR_ID = :id"); 
        $fetch_cr->execute([':id' => $id]); 
        $cr = $fetch_cr->fetch(PDO::FETCH_ASSOC);
        
        if ($cr) {
            if (!empty($cr['Invoice_IDs_JSON'])) {
                $inv_ids = json_decode($cr['Invoice_IDs_JSON'], true);
                if (!empty($inv_ids)) { 
                    $in = implode(',', array_fill(0, count($inv_ids), '?')); 
                    
                    $inv_stmt = $db->prepare("SELECT DR_ID, DR_IDs_JSON FROM yl_invoices WHERE Invoice_ID IN ($in)");
                    $inv_stmt->execute($inv_ids);
                    $invoices = $inv_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $all_dr_ids = [];
                    foreach ($invoices as $inv) {
                        if (!empty($inv['DR_IDs_JSON'])) {
                            $arr = json_decode($inv['DR_IDs_JSON'], true);
                            if(is_array($arr)) $all_dr_ids = array_merge($all_dr_ids, $arr);
                        } elseif (!empty($inv['DR_ID'])) {
                            $all_dr_ids[] = $inv['DR_ID'];
                        }
                    }
                    
                    if (!empty($all_dr_ids)) {
                        $dr_in = implode(',', array_fill(0, count($all_dr_ids), '?'));
                        $revert_stmt = $db->prepare("UPDATE yl_stock_orders so JOIN yl_delivery_receipts dr ON so.SO_ID = dr.SO_ID SET so.Payment_Status = 'Pending' WHERE dr.DR_ID IN ($dr_in)"); 
                        $revert_stmt->execute($all_dr_ids);
                    }
                }
            }
            
            if (!empty($cr['CR_No'])) {
                $find_j = $db->prepare("SELECT Journal_ID FROM accounting_journal WHERE Reference_No = :ref");
                $find_j->execute([':ref' => $cr['CR_No']]);
                $journals = $find_j->fetchAll(PDO::FETCH_ASSOC);
                foreach($journals as $j) {
                    $db->prepare("DELETE FROM accounting_journal_lines WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
                    $db->prepare("DELETE FROM accounting_journal WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
                }
            }
        }
        
        $stmt = $db->prepare("DELETE FROM yl_collection_receipts WHERE CR_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
        exit;
    }
}
?>