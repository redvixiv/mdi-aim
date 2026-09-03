<?php
if ($method === 'GET') {
    if ($table === 'ds_sales_orders') {
        $stmt = $db->prepare("SELECT so.*, o.Outlet_Name, o.Branch, o.Outlet_No, o.Outlet_TIN AS TIN, o.Terms, o.Business_Style FROM ds_sales_orders so JOIN outlets o ON so.Outlet_ID = o.Outlet_ID WHERE so.DS_Type = :dstype ORDER BY so.SO_ID DESC"); 
        $stmt->execute([':dstype' => $ds_type]); 
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($orders as &$order) {
            $ret_stmt = $db->prepare("SELECT Items_JSON FROM stock_returns WHERE Reference_No = :ref AND Return_Type = 'Customer Return'");
            $ret_stmt->execute([':ref' => $order['SO_No']]);
            $returns = $ret_stmt->fetchAll(PDO::FETCH_ASSOC);
            $ret_amt = 0;
            foreach($returns as $rt) {
                $items = json_decode($rt['Items_JSON'], true);
                if($items){
                    foreach($items as $i) { $ret_amt += (isset($i['subtotal']) ? (float)$i['subtotal'] : 0); }
                }
            }
            $order['Returned_Amount'] = $ret_amt;
        }
        echo json_encode($orders);
    }
    elseif ($table === 'ds_sales_order_details') {
        $stmt = $db->prepare("SELECT so.*, o.Outlet_Name, o.Branch, o.Address, o.Outlet_TIN, o.Terms, o.Business_Style FROM ds_sales_orders so JOIN outlets o ON so.Outlet_ID = o.Outlet_ID WHERE so.SO_ID = :id"); $stmt->execute([':id' => $id]); echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'ds_invoices') {
        $stmt = $db->prepare("SELECT inv.*, so.SO_No, o.Outlet_Name, o.Terms FROM ds_invoices inv JOIN ds_sales_orders so ON inv.SO_ID = so.SO_ID JOIN outlets o ON so.Outlet_ID = o.Outlet_ID WHERE inv.DS_Type = :dstype ORDER BY inv.Invoice_ID DESC"); 
        $stmt->execute([':dstype' => $ds_type]); 
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'ds_unpaid_invoices') {
        $oid = $_GET['outlet_id'] ?? 0;
        $stmt = $db->prepare("SELECT inv.*, so.SO_No, o.Outlet_Name FROM ds_invoices inv JOIN ds_sales_orders so ON inv.SO_ID = so.SO_ID JOIN outlets o ON so.Outlet_ID = o.Outlet_ID WHERE so.Outlet_ID = :oid AND (so.Payment_Status != 'Paid' OR so.Payment_Status IS NULL) ORDER BY inv.Invoice_ID DESC"); 
        $stmt->execute([':oid' => $oid]); 
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'ds_outlets_with_unpaid_invoices') {
        $stmt = $db->prepare("SELECT DISTINCT o.Outlet_ID, o.Outlet_Name, o.Branch, o.Address, o.Outlet_TIN, o.Business_Style FROM outlets o JOIN ds_sales_orders so ON o.Outlet_ID = so.Outlet_ID JOIN ds_invoices inv ON so.SO_ID = inv.SO_ID WHERE so.DS_Type = :dstype AND (so.Payment_Status != 'Paid' OR so.Payment_Status IS NULL) ORDER BY o.Outlet_Name ASC");
        $stmt->execute([':dstype' => $ds_type]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'ds_invoices_by_ids') {
        $ids = json_decode($_GET['ids'] ?? '[]'); if (empty($ids)) { echo json_encode([]); exit; }
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $db->prepare("SELECT inv.Invoice_No, inv.Amount_Due, o.Outlet_Name, o.Address, o.Outlet_TIN, o.Business_Style FROM ds_invoices inv JOIN ds_sales_orders so ON inv.SO_ID = so.SO_ID JOIN outlets o ON so.Outlet_ID = o.Outlet_ID WHERE inv.Invoice_ID IN ($in)"); $stmt->execute($ids); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'ds_collection_receipts') {
        $stmt = $db->prepare("SELECT * FROM ds_collection_receipts WHERE DS_Type = :dstype ORDER BY CR_ID DESC"); $stmt->execute([':dstype' => $ds_type]); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'ds_sales_report') {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-t');
        
        $sql = "
            SELECT inv.Invoice_ID, inv.Invoice_Date, inv.Invoice_No, so.SO_No, 
                   o.Outlet_Name, so.Total_Amount as Gross_Sales, inv.VAT, inv.Amount_Due, 
                   so.Payment_Status, so.Items_JSON, 
                   GROUP_CONCAT(cr.CR_No SEPARATOR ', ') as CR_No
            FROM ds_invoices inv 
            JOIN ds_sales_orders so ON inv.SO_ID = so.SO_ID 
            JOIN outlets o ON so.Outlet_ID = o.Outlet_ID 
            LEFT JOIN ds_collection_receipts cr ON cr.Invoice_IDs_JSON LIKE CONCAT('%\"', inv.Invoice_ID, '\"%')
            WHERE inv.Invoice_Date BETWEEN :from AND :to 
            GROUP BY inv.Invoice_ID, inv.Invoice_Date, inv.Invoice_No, so.SO_No, o.Outlet_Name, so.Total_Amount, inv.VAT, inv.Amount_Due, so.Payment_Status, so.Items_JSON
            ORDER BY inv.Invoice_Date DESC, inv.Invoice_No DESC";
            
        $stmt = $db->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} 
elseif ($method === 'POST') {
    if ($table === 'ds_sales_orders') {
        $stmt = $db->query("SELECT MAX(CAST(SO_No AS UNSIGNED)) AS max_no FROM ds_sales_orders"); $row = $stmt->fetch(PDO::FETCH_ASSOC); $so_no = $row['max_no'] ? $row['max_no'] + 1 : 100001;
        $stmt = $db->prepare("INSERT INTO ds_sales_orders (DS_Type, SO_No, SO_Date, Outlet_ID, Items_JSON, Total_Quantity, Total_Amount, Payment_Status) VALUES (:dstype, :sono, :sodate, :oid, :items, :tq, :ta, 'Pending')");
        $stmt->execute([':dstype' => $data->ds_type, ':sono' => $so_no, ':sodate' => $data->so_date, ':oid' => $data->outlet_id, ':items' => json_encode($data->items), ':tq' => $data->total_qty, ':ta' => $data->total_amount]);
        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'ds_invoices') {
        $stmt = $db->prepare("INSERT INTO ds_invoices (DS_Type, Invoice_No, Invoice_Date, SO_ID, Net_Amount, VAT, Applied_EWT, EWT_Amount, Discount_Percent, Discount_Amount, Amount_Due) VALUES (:dstype, :invno, :invdate, :soid, :net, :vat, :ewt_app, :ewt_amt, :disc_pct, :disc_amt, :amtdue)");
        $stmt->execute([':dstype' => $data->ds_type, ':invno' => $data->invoice_no, ':invdate' => $data->invoice_date, ':soid' => $data->so_id, ':net' => $data->net_amount, ':vat' => $data->vat, ':ewt_app' => $data->applied_ewt, ':ewt_amt' => $data->ewt_amount, ':disc_pct' => $data->discount_percent ?? 0, ':disc_amt' => $data->discount_amount ?? 0, ':amtdue' => $data->amount_due]);
        
        $ar_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1200'")->fetchColumn();
        if (!$ar_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1200', 'Accounts Receivable', 'Asset')"); $ar_id = $db->lastInsertId(); }
        
        $sales_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '4000'")->fetchColumn();
        if (!$sales_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('4000', 'Sales Revenue', 'Revenue')"); $sales_id = $db->lastInsertId(); }
        
        $cwt_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1205'")->fetchColumn();
        if (!$cwt_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1205', 'Creditable Withholding Tax', 'Asset')"); $cwt_id = $db->lastInsertId(); }

        // FIXED: Ensure Output VAT account exists and is used to separate net sales from taxes
        $vat_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '2100'")->fetchColumn();
        if (!$vat_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('2100', 'Output VAT Payable', 'Liability')"); $vat_id = $db->lastInsertId(); }

        if ($ar_id && $sales_id && $cwt_id && $vat_id) {
            $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)"); 
            $j_stmt->execute([':d' => $data->invoice_date, ':ref' => $data->invoice_no, ':desc' => "Auto-generated from DS Invoice " . $data->invoice_no]); 
            $jid = $db->lastInsertId();
            
            $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
            
            $ewt = (float)($data->ewt_amount ?? 0);
            $net = (float)($data->net_amount ?? 0);
            $vat = (float)($data->vat ?? 0);
            $due = (float)($data->amount_due ?? 0);
            
            // Post Debits
            $l_stmt->execute([':jid' => $jid, ':aid' => $ar_id, ':deb' => $due, ':cred' => 0]); 
            if ($ewt > 0) {
                $l_stmt->execute([':jid' => $jid, ':aid' => $cwt_id, ':deb' => $ewt, ':cred' => 0]); 
            }
            
            // Post Credits (Correctly segregating Sales Revenue and Output VAT)
            $l_stmt->execute([':jid' => $jid, ':aid' => $sales_id, ':deb' => 0, ':cred' => $net]);
            if ($vat > 0) {
                $l_stmt->execute([':jid' => $jid, ':aid' => $vat_id, ':deb' => 0, ':cred' => $vat]);
            }
        }
        
        $so_stmt = $db->prepare("SELECT Items_JSON FROM ds_sales_orders WHERE SO_ID = :soid"); $so_stmt->execute([':soid' => $data->so_id]); $so_data = $so_stmt->fetch(PDO::FETCH_ASSOC);
        $total_cogs = 0;
        if ($so_data && !empty($so_data['Items_JSON'])) {
            $items = json_decode($so_data['Items_JSON'], true);
            foreach ($items as $item) {
                $cost_stmt = $db->prepare("SELECT Unit_Cost FROM product_pricing WHERE Product_ID = :pid AND Effective_From <= :idate AND (Effective_To IS NULL OR Effective_To >= :idate) ORDER BY Effective_From DESC LIMIT 1");
                $cost_stmt->execute([':pid' => $item['product_id'], ':idate' => $data->invoice_date]); $cost_data = $cost_stmt->fetch(PDO::FETCH_ASSOC);
                $total_cogs += (($cost_data ? (float)$cost_data['Unit_Cost'] : 0) * (float)$item['quantity']);
            }
        }
        if ($total_cogs > 0) {
            $inv_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1300'")->fetchColumn();
            if (!$inv_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1300', 'Inventory Asset', 'Asset')"); $inv_id = $db->lastInsertId(); }
            $cogs_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '5000'")->fetchColumn();
            if (!$cogs_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('5000', 'Cost of Goods Sold', 'Expense')"); $cogs_id = $db->lastInsertId(); }
            
            if ($inv_id && $cogs_id) {
                $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)"); $j_stmt->execute([':d' => $data->invoice_date, ':ref' => $data->invoice_no, ':desc' => "COGS for DS Invoice " . $data->invoice_no]); $jid = $db->lastInsertId();
                $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
                $l_stmt->execute([':jid' => $jid, ':aid' => $cogs_id, ':deb' => $total_cogs, ':cred' => 0]); $l_stmt->execute([':jid' => $jid, ':aid' => $inv_id, ':deb' => 0, ':cred' => $total_cogs]); 
            }
        }
        
        if ($so_data && !empty($so_data['Items_JSON'])) {
            $w_stmt = $db->query("SELECT Warehouse_ID FROM warehouses ORDER BY Warehouse_ID ASC LIMIT 1");
            $primary_wid = $w_stmt->fetchColumn() ?: 1;

            $led_stmt = $db->prepare("INSERT INTO inventory_ledger (Transaction_Date, Warehouse_ID, Product_ID, Transaction_Type, Reference_No, Qty_In, Qty_Out) VALUES (:d, :wid, :p, 'Sales', :ref, 0, :qo)");
            $items = json_decode($so_data['Items_JSON'], true);
            foreach ($items as $item) {
                if((int)$item['quantity'] > 0) {
                    $led_stmt->execute([':d' => $data->invoice_date, ':wid' => $primary_wid, ':p' => $item['product_id'], ':ref' => 'INV-'.$data->invoice_no, ':qo' => $item['quantity']]);
                }
            }
        }
        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'ds_collection_receipts') {
        $stmt = $db->prepare("INSERT INTO ds_collection_receipts (DS_Type, CR_No, CR_Date, Invoice_IDs_JSON, Outlet_Name, Address, Outlet_TIN, Business_Style, Total_Amount_Due, Total_Amount_Words) VALUES (:dstype, :crno, :crdate, :inv_ids, :oname, :addr, :otin, :bstyle, :tdue, :words)");
        $stmt->execute([':dstype' => $data->ds_type, ':crno' => $data->cr_no, ':crdate' => $data->cr_date, ':inv_ids' => json_encode($data->invoice_ids), ':oname' => $data->outlet_name, ':addr' => $data->address, ':otin' => $data->outlet_tin, ':bstyle' => $data->business_style, ':tdue' => $data->total_amount_due, ':words' => $data->total_words]);

        if (!empty($data->invoice_ids)) {
            $in = implode(',', array_fill(0, count($data->invoice_ids), '?'));
            $update_stmt = $db->prepare("UPDATE ds_sales_orders so JOIN ds_invoices inv ON so.SO_ID = inv.SO_ID SET so.Payment_Status = 'Paid' WHERE inv.Invoice_ID IN ($in)"); $update_stmt->execute($data->invoice_ids);
        }
        
        $cash_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1010'")->fetchColumn();
        if (!$cash_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1010', 'Cash on Hand', 'Asset')"); $cash_id = $db->lastInsertId(); }
        $ar_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1200'")->fetchColumn();
        if (!$ar_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1200', 'Accounts Receivable', 'Asset')"); $ar_id = $db->lastInsertId(); }
        
        if ($cash_id && $ar_id) {
            $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)"); $j_stmt->execute([':d' => $data->cr_date, ':ref' => $data->cr_no, ':desc' => "Auto-generated from DS CR " . $data->cr_no]); $jid = $db->lastInsertId();
            $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
            $l_stmt->execute([':jid' => $jid, ':aid' => $cash_id, ':deb' => $data->total_amount_due, ':cred' => 0]); $l_stmt->execute([':jid' => $jid, ':aid' => $ar_id, ':deb' => 0, ':cred' => $data->total_amount_due]);
        }
        echo json_encode(['status' => 'success']);
    }
}
elseif ($method === 'DELETE') {
    if ($table === 'ds_sales_orders' && $id) { 
        $check = $db->prepare("SELECT COUNT(*) FROM ds_invoices WHERE SO_ID = :id");
        $check->execute([':id' => $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete Sales Order. An Invoice has already been issued for this order.']);
            exit;
        }
        
        $stmt = $db->prepare("DELETE FROM ds_sales_orders WHERE SO_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
    }
    elseif ($table === 'ds_invoices' && $id) { 
        $check = $db->prepare("SELECT COUNT(*) FROM ds_collection_receipts WHERE Invoice_IDs_JSON LIKE :id");
        $check->execute([':id' => '%"'.$id.'"%']);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete Invoice. A Collection Receipt has already been issued against it.']);
            exit;
        }

        $sel = $db->prepare("SELECT Invoice_No FROM ds_invoices WHERE Invoice_ID = :id");
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
        
        $stmt = $db->prepare("DELETE FROM ds_invoices WHERE Invoice_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
    }
    elseif ($table === 'ds_collection_receipts' && $id) { 
        $fetch_cr = $db->prepare("SELECT CR_No, Invoice_IDs_JSON FROM ds_collection_receipts WHERE CR_ID = :id"); 
        $fetch_cr->execute([':id' => $id]); 
        $cr = $fetch_cr->fetch(PDO::FETCH_ASSOC);
        
        if ($cr) {
            if (!empty($cr['Invoice_IDs_JSON'])) {
                $inv_ids = json_decode($cr['Invoice_IDs_JSON'], true);
                if (!empty($inv_ids)) { 
                    $in = implode(',', array_fill(0, count($inv_ids), '?')); 
                    $revert_stmt = $db->prepare("UPDATE ds_sales_orders so JOIN ds_invoices inv ON so.SO_ID = inv.SO_ID SET so.Payment_Status = 'Pending' WHERE inv.Invoice_ID IN ($in)"); 
                    $revert_stmt->execute($inv_ids); 
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
        
        $stmt = $db->prepare("DELETE FROM ds_collection_receipts WHERE CR_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
    }
}
?>