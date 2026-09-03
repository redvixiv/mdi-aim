<?php
try { $db->exec("ALTER TABLE purchase_orders ADD COLUMN Total_Amount DECIMAL(15,2) DEFAULT 0.00"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE goods_receipts ADD COLUMN Total_Amount DECIMAL(15,2) DEFAULT 0.00"); } catch (PDOException $e) {}

if ($method === 'GET') {
    if ($table === 'warehouses') {
        $stmt = $db->prepare("SELECT * FROM warehouses ORDER BY Warehouse_Name ASC"); $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'purchase_orders') {
        $stmt = $db->prepare("SELECT po.*, s.Supplier_Name, w.Warehouse_Name FROM purchase_orders po JOIN suppliers s ON po.Supplier_ID = s.Supplier_ID JOIN warehouses w ON po.Warehouse_ID = w.Warehouse_ID ORDER BY po.PO_ID DESC");
        $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'goods_receipts') {
        $stmt = $db->prepare("SELECT gr.*, po.PO_No, w.Warehouse_Name FROM goods_receipts gr JOIN purchase_orders po ON gr.PO_ID = po.PO_ID JOIN warehouses w ON gr.Warehouse_ID = w.Warehouse_ID ORDER BY gr.Receipt_ID DESC");
        $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'stock_transfers') {
        $stmt = $db->prepare("SELECT st.*, wf.Warehouse_Name as From_Warehouse, wt.Warehouse_Name as To_Warehouse FROM stock_transfers st JOIN warehouses wf ON st.From_Warehouse_ID = wf.Warehouse_ID JOIN warehouses wt ON st.To_Warehouse_ID = wt.Warehouse_ID ORDER BY st.Transfer_ID DESC");
        $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'stock_returns') {
        $stmt = $db->prepare("SELECT sr.*, w.Warehouse_Name FROM stock_returns sr JOIN warehouses w ON sr.Warehouse_ID = w.Warehouse_ID ORDER BY sr.Return_ID DESC");
        $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'return_references') {
        $type = $_GET['type'] ?? '';
        $refs = [];
        if ($type === 'Customer Return') {
            $ds = $db->query("SELECT SO_No as Ref_No, 'DS Order' as Source FROM ds_sales_orders WHERE Payment_Status = 'Pending' ORDER BY SO_ID DESC")->fetchAll(PDO::FETCH_ASSOC);
            $yl = $db->query("SELECT SO_No as Ref_No, 'YL Order' as Source FROM yl_stock_orders WHERE Payment_Status = 'Pending' ORDER BY SO_ID DESC")->fetchAll(PDO::FETCH_ASSOC);
            $refs = array_merge($ds, $yl);
        } else {
            $pos = $db->query("SELECT PO_No as Ref_No, 'Purchase Order' as Source FROM purchase_orders WHERE Status IN ('Partially Received', 'Fully Received') ORDER BY PO_ID DESC")->fetchAll(PDO::FETCH_ASSOC);
            $refs = $pos;
        }
        echo json_encode($refs);
    }
    elseif ($table === 'stock_balances') {
        $wid = $_GET['warehouse_id'] ?? 1;
        $stmt = $db->prepare("
            SELECT p.Product_No, p.Product_Name, p.Category,
                   COALESCE(SUM(l.Qty_In), 0) - COALESCE(SUM(l.Qty_Out), 0) as Current_Stock
            FROM products p
            LEFT JOIN inventory_ledger l ON p.Product_ID = l.Product_ID AND l.Warehouse_ID = :wid
            GROUP BY p.Product_ID, p.Product_No, p.Product_Name, p.Category
            ORDER BY p.Product_Name ASC
        ");
        $stmt->execute([':wid' => $wid]); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'inventory_ledger') {
        $stmt = $db->prepare("SELECT l.*, p.Product_Name, w.Warehouse_Name FROM inventory_ledger l JOIN products p ON l.Product_ID = p.Product_ID JOIN warehouses w ON l.Warehouse_ID = w.Warehouse_ID ORDER BY l.Transaction_Date DESC, l.Ledger_ID DESC");
        $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} 
elseif ($method === 'POST') {
    if ($table === 'warehouses') {
        if (!empty($data->Warehouse_ID)) {
            $stmt = $db->prepare("UPDATE warehouses SET Warehouse_Name = :wname, Location = :loc WHERE Warehouse_ID = :id");
            $stmt->execute([':wname' => $data->Warehouse_Name, ':loc' => $data->Location, ':id' => $data->Warehouse_ID]);
        } else {
            $stmt = $db->prepare("INSERT INTO warehouses (Warehouse_Name, Location) VALUES (:wname, :loc)");
            $stmt->execute([':wname' => $data->Warehouse_Name, ':loc' => $data->Location]);
        }
        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'purchase_orders') {
        $stmt = $db->prepare("INSERT INTO purchase_orders (PO_No, PO_Date, Warehouse_ID, Supplier_ID, Items_JSON, Total_Quantity, Total_Amount, Status) VALUES (:pono, :podate, :wid, :sid, :items, :tq, :tamt, 'Pending')");
        $stmt->execute([':pono' => $data->po_no, ':podate' => $data->po_date, ':wid' => $data->warehouse_id, ':sid' => $data->supplier_id, ':items' => json_encode($data->items), ':tq' => $data->total_qty, ':tamt' => $data->total_amount]);
        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'goods_receipts') {
        $stmt = $db->prepare("INSERT INTO goods_receipts (DR_No, Arrival_Date, PO_ID, Warehouse_ID, Forwarder, Seal_No, Items_JSON, Total_Received, Total_Amount, Remarks) VALUES (:dr, :arr, :poid, :wid, :fwd, :seal, :items, :tq, :tamt, :rem)");
        $stmt->execute([':dr' => $data->dr_no, ':arr' => $data->arrival_date, ':poid' => $data->po_id, ':wid' => $data->warehouse_id, ':fwd' => $data->forwarder, ':seal' => $data->seal_no, ':items' => json_encode($data->items), ':tq' => $data->total_qty, ':tamt' => $data->total_amount, ':rem' => $data->remarks]);
        
        $upd = $db->prepare("UPDATE purchase_orders SET Status = 'Fully Received' WHERE PO_ID = :poid");
        $upd->execute([':poid' => $data->po_id]);

        $led_stmt = $db->prepare("INSERT INTO inventory_ledger (Transaction_Date, Warehouse_ID, Product_ID, Transaction_Type, Reference_No, Qty_In, Qty_Out) VALUES (:d, :w, :p, 'Stock In', :ref, :qi, 0)");
        foreach($data->items as $item) {
            if((int)$item->quantity > 0) {
                $led_stmt->execute([':d' => $data->arrival_date, ':w' => $data->warehouse_id, ':p' => $item->product_id, ':ref' => 'DR-'.$data->dr_no, ':qi' => $item->quantity]);
            }
        }

        $po_stmt = $db->prepare("SELECT Supplier_ID FROM purchase_orders WHERE PO_ID = :poid");
        $po_stmt->execute([':poid' => $data->po_id]);
        $po_data = $po_stmt->fetch(PDO::FETCH_ASSOC);
        $supplier_id = $po_data ? $po_data['Supplier_ID'] : null;

        if ($supplier_id && $data->total_amount > 0) {
            $ap_stmt = $db->prepare("INSERT INTO accounting_payables (Supplier_ID, Reference_No, AP_Date, Amount, Status, Remarks) VALUES (:sid, :ref, :apdate, :amt, 'Pending', :rem)");
            $ap_stmt->execute([':sid' => $supplier_id, ':ref' => 'DR-' . $data->dr_no, ':apdate' => $data->arrival_date, ':amt' => $data->total_amount, ':rem' => 'Auto-generated from Goods Receipt DR-' . $data->dr_no]);
            
            $inv_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1300'")->fetchColumn();
            if (!$inv_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1300', 'Inventory Asset', 'Asset')"); $inv_id = $db->lastInsertId(); }
            
            $ap_acc_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '2000'")->fetchColumn();
            if (!$ap_acc_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('2000', 'Accounts Payable', 'Liability')"); $ap_acc_id = $db->lastInsertId(); }
            
            if ($inv_id && $ap_acc_id) {
                $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)"); 
                $j_stmt->execute([':d' => $data->arrival_date, ':ref' => 'DR-'.$data->dr_no, ':desc' => "Inventory received from DR-" . $data->dr_no]); 
                $jid = $db->lastInsertId();
                
                $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
                $l_stmt->execute([':jid' => $jid, ':aid' => $inv_id, ':deb' => $data->total_amount, ':cred' => 0]); 
                $l_stmt->execute([':jid' => $jid, ':aid' => $ap_acc_id, ':deb' => 0, ':cred' => $data->total_amount]);
            }
        }
        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'stock_transfers') {
        if ($data->from_warehouse_id == $data->to_warehouse_id) { echo json_encode(['status' => 'error', 'message' => 'Source and Destination warehouses cannot be the same!']); exit; }

        $stmt = $db->prepare("INSERT INTO stock_transfers (Transfer_No, Transfer_Date, From_Warehouse_ID, To_Warehouse_ID, Items_JSON, Total_Quantity, Remarks) VALUES (:tno, :tdate, :fromw, :tow, :items, :tq, :rem)");
        $stmt->execute([':tno' => $data->transfer_no, ':tdate' => $data->transfer_date, ':fromw' => $data->from_warehouse_id, ':tow' => $data->to_warehouse_id, ':items' => json_encode($data->items), ':tq' => $data->total_qty, ':rem' => $data->remarks]);

        $led_stmt = $db->prepare("INSERT INTO inventory_ledger (Transaction_Date, Warehouse_ID, Product_ID, Transaction_Type, Reference_No, Qty_In, Qty_Out) VALUES (:d, :w, :p, :type, :ref, :qi, :qo)");
        foreach($data->items as $item) {
            if((int)$item->quantity > 0) {
                $led_stmt->execute([':d' => $data->transfer_date, ':w' => $data->from_warehouse_id, ':p' => $item->product_id, ':type' => 'Transfer Out', ':ref' => 'TR-'.$data->transfer_no, ':qi' => 0, ':qo' => $item->quantity]);
                $led_stmt->execute([':d' => $data->transfer_date, ':w' => $data->to_warehouse_id, ':p' => $item->product_id, ':type' => 'Transfer In', ':ref' => 'TR-'.$data->transfer_no, ':qi' => $item->quantity, ':qo' => 0]);
            }
        }
        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'stock_returns') {
        $stmt = $db->prepare("INSERT INTO stock_returns (Return_No, Return_Date, Warehouse_ID, Return_Type, Reference_No, Items_JSON, Total_Quantity, Remarks) VALUES (:rno, :rdate, :wid, :rtype, :ref, :items, :tq, :rem)");
        $stmt->execute([':rno' => $data->return_no, ':rdate' => $data->return_date, ':wid' => $data->warehouse_id, ':rtype' => $data->return_type, ':ref' => $data->reference_no, ':items' => json_encode($data->items), ':tq' => $data->total_qty, ':rem' => $data->remarks]);

        $led_stmt = $db->prepare("INSERT INTO inventory_ledger (Transaction_Date, Warehouse_ID, Product_ID, Transaction_Type, Reference_No, Qty_In, Qty_Out) VALUES (:d, :w, :p, :type, :ref, :qi, :qo)");
        
        $total_refund = 0;
        $total_cogs = 0;

        foreach($data->items as $item) {
            if((int)$item->quantity > 0) {
                if ($data->return_type === 'Customer Return') {
                    $qi = $item->quantity; $qo = 0; $ttype = 'Return In';
                } else {
                    $qi = 0; $qo = $item->quantity; $ttype = 'Return Out';
                }
                
                $ref_suffix = (isset($item->condition) && $item->condition === 'Damaged') ? ' [Damaged]' : '';
                $led_stmt->execute([':d' => $data->return_date, ':w' => $data->warehouse_id, ':p' => $item->product_id, ':type' => $ttype, ':ref' => 'RT-'.$data->return_no . $ref_suffix, ':qi' => $qi, ':qo' => $qo]);
                
                $price_stmt = $db->prepare("SELECT Wholesale, Unit_Cost FROM product_pricing WHERE Product_ID = :pid ORDER BY Pricing_ID DESC LIMIT 1");
                $price_stmt->execute([':pid' => $item->product_id]);
                $pricing = $price_stmt->fetch(PDO::FETCH_ASSOC);
                
                $price = $pricing ? (float)$pricing['Wholesale'] : 0;
                $cost = $pricing ? (float)$pricing['Unit_Cost'] : 0;
                
                $total_refund += ($price * (int)$item->quantity);
                $total_cogs += ($cost * (int)$item->quantity);
            }
        }
        
        $inv_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1300'")->fetchColumn();
        if (!$inv_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1300', 'Inventory Asset', 'Asset')"); $inv_id = $db->lastInsertId(); }

        if ($data->return_type === 'Customer Return' && $total_refund > 0) {
            $ar_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1200'")->fetchColumn();
            if (!$ar_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('1200', 'Accounts Receivable', 'Asset')"); $ar_id = $db->lastInsertId(); }
            
            $sales_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '4000'")->fetchColumn();
            if (!$sales_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('4000', 'Sales Revenue', 'Revenue')"); $sales_id = $db->lastInsertId(); }
            
            $cogs_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '5000'")->fetchColumn();
            if (!$cogs_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('5000', 'Cost of Goods Sold', 'Expense')"); $cogs_id = $db->lastInsertId(); }
            
            if ($ar_id && $sales_id && $inv_id && $cogs_id) {
                $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)");
                $j_stmt->execute([':d' => $data->return_date, ':ref' => 'RT-'.$data->return_no, ':desc' => "Credit Memo (Customer Return) for " . $data->reference_no]);
                $jid = $db->lastInsertId();
                
                $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
                
                $l_stmt->execute([':jid' => $jid, ':aid' => $sales_id, ':deb' => $total_refund, ':cred' => 0]); 
                $l_stmt->execute([':jid' => $jid, ':aid' => $ar_id, ':deb' => 0, ':cred' => $total_refund]);
                
                if ($total_cogs > 0) {
                    $l_stmt->execute([':jid' => $jid, ':aid' => $inv_id, ':deb' => $total_cogs, ':cred' => 0]); 
                    $l_stmt->execute([':jid' => $jid, ':aid' => $cogs_id, ':deb' => 0, ':cred' => $total_cogs]);
                }
            }

            if (!empty($data->reference_no)) {
                $refno = $data->reference_no;
                try {
                    $db->prepare("UPDATE ds_invoices inv JOIN ds_sales_orders so ON inv.SO_ID = so.SO_ID SET inv.Net_Amount = inv.Net_Amount - (:refund / 1.12), inv.VAT = inv.VAT - (:refund - (:refund / 1.12)), inv.Amount_Due = inv.Amount_Due - :refund WHERE so.SO_No = :refno")->execute([':refund' => $total_refund, ':refno' => $refno]);
                } catch(Exception $e) {}

                try {
                    $db->prepare("UPDATE yl_invoices inv JOIN yl_delivery_receipts dr ON inv.DR_ID = dr.DR_ID JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID SET inv.Net_Amount = inv.Net_Amount - (:refund / 1.12), inv.VAT = inv.VAT - (:refund - (:refund / 1.12)), inv.Amount_Due = inv.Amount_Due - :refund WHERE so.SO_No = :refno")->execute([':refund' => $total_refund, ':refno' => $refno]);
                } catch(Exception $e) {}
            }
        } 
        elseif ($data->return_type === 'Return to Supplier' && $total_cogs > 0) {
            $ap_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '2000'")->fetchColumn();
            if (!$ap_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('2000', 'Accounts Payable', 'Liability')"); $ap_id = $db->lastInsertId(); }
            
            if ($ap_id && $inv_id) {
                $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)");
                $j_stmt->execute([':d' => $data->return_date, ':ref' => 'RT-'.$data->return_no, ':desc' => "Debit Memo (Supplier Return) for " . $data->reference_no]);
                $jid = $db->lastInsertId();
                
                $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
                
                $l_stmt->execute([':jid' => $jid, ':aid' => $ap_id, ':deb' => $total_cogs, ':cred' => 0]); 
                $l_stmt->execute([':jid' => $jid, ':aid' => $inv_id, ':deb' => 0, ':cred' => $total_cogs]); 
            }
        }
        
        echo json_encode(['status' => 'success']);
    }
}
elseif ($method === 'DELETE') {
    if ($table === 'warehouses' && $id) { 
        // FIXED: Security check to prevent deleting an active warehouse
        $check = $db->prepare("SELECT COUNT(*) FROM inventory_ledger WHERE Warehouse_ID = :id");
        $check->execute([':id' => $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete warehouse. It contains active inventory history.']);
            exit;
        }
        
        $stmt = $db->prepare("DELETE FROM warehouses WHERE Warehouse_ID = :id"); $stmt->execute([':id' => $id]); echo json_encode(['status' => 'success']); 
    }
    if ($table === 'purchase_orders' && $id) { 
        $stmt = $db->prepare("DELETE FROM purchase_orders WHERE PO_ID = :id AND Status = 'Pending'"); $stmt->execute([':id' => $id]); 
        if ($stmt->rowCount() > 0) { echo json_encode(['status' => 'success']); } else { echo json_encode(['status' => 'error', 'message' => 'Cannot delete this Purchase Order because it has already been partially or fully received.']); }
    }
    elseif ($table === 'goods_receipts' && $id) { 
        $sel = $db->prepare("SELECT DR_No, PO_ID FROM goods_receipts WHERE Receipt_ID = :id");
        $sel->execute([':id' => $id]);
        $gr = $sel->fetch(PDO::FETCH_ASSOC);
        
        if ($gr && !empty($gr['DR_No'])) {
            $ref = 'DR-' . $gr['DR_No'];
            $db->prepare("UPDATE purchase_orders SET Status = 'Pending' WHERE PO_ID = :poid")->execute([':poid' => $gr['PO_ID']]);
            $db->prepare("DELETE FROM inventory_ledger WHERE Reference_No = :ref")->execute([':ref' => $ref]);
            $db->prepare("DELETE FROM accounting_payables WHERE Reference_No = :ref")->execute([':ref' => $ref]);
            
            $find_j = $db->prepare("SELECT Journal_ID FROM accounting_journal WHERE Reference_No = :ref");
            $find_j->execute([':ref' => $ref]);
            $journals = $find_j->fetchAll(PDO::FETCH_ASSOC);
            foreach($journals as $j) {
                $db->prepare("DELETE FROM accounting_journal_lines WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
                $db->prepare("DELETE FROM accounting_journal WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
            }
        }
        $db->prepare("DELETE FROM goods_receipts WHERE Receipt_ID = :id")->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
    }
    elseif ($table === 'stock_transfers' && $id) { 
        $sel = $db->prepare("SELECT Transfer_No FROM stock_transfers WHERE Transfer_ID = :id");
        $sel->execute([':id' => $id]);
        $tr = $sel->fetch(PDO::FETCH_ASSOC);
        
        if ($tr && !empty($tr['Transfer_No'])) {
            $ref = 'TR-' . $tr['Transfer_No'];
            $db->prepare("DELETE FROM inventory_ledger WHERE Reference_No = :ref")->execute([':ref' => $ref]);
        }
        $db->prepare("DELETE FROM stock_transfers WHERE Transfer_ID = :id")->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
    }
    elseif ($table === 'stock_returns' && $id) { 
        $sel = $db->prepare("SELECT Return_No, Reference_No, Return_Type, Items_JSON FROM stock_returns WHERE Return_ID = :id");
        $sel->execute([':id' => $id]);
        $rt = $sel->fetch(PDO::FETCH_ASSOC);
        
        if ($rt && !empty($rt['Return_No'])) {
            $ref = 'RT-' . $rt['Return_No'] . '%'; 
            $db->prepare("DELETE FROM inventory_ledger WHERE Reference_No LIKE :ref")->execute([':ref' => $ref]);
            
            $exact_ref = 'RT-' . $rt['Return_No'];
            $find_j = $db->prepare("SELECT Journal_ID FROM accounting_journal WHERE Reference_No = :ref");
            $find_j->execute([':ref' => $exact_ref]);
            $journals = $find_j->fetchAll(PDO::FETCH_ASSOC);
            foreach($journals as $j) {
                $db->prepare("DELETE FROM accounting_journal_lines WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
                $db->prepare("DELETE FROM accounting_journal WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
            }
            
            $total_refund = 0;
            if (!empty($rt['Items_JSON'])) {
                $items = json_decode($rt['Items_JSON'], true);
                foreach($items as $item) {
                    $price_stmt = $db->prepare("SELECT Wholesale FROM product_pricing WHERE Product_ID = :pid ORDER BY Pricing_ID DESC LIMIT 1");
                    $price_stmt->execute([':pid' => $item['product_id']]);
                    $price = $price_stmt->fetchColumn() ?: 0;
                    $total_refund += ($price * (int)$item['quantity']);
                }
            }
            if ($rt['Return_Type'] === 'Customer Return' && $total_refund > 0 && !empty($rt['Reference_No'])) {
                $refno = $rt['Reference_No'];
                try {
                    $db->prepare("UPDATE ds_invoices inv JOIN ds_sales_orders so ON inv.SO_ID = so.SO_ID SET inv.Net_Amount = inv.Net_Amount + (:refund / 1.12), inv.VAT = inv.VAT + (:refund - (:refund / 1.12)), inv.Amount_Due = inv.Amount_Due + :refund WHERE so.SO_No = :refno")->execute([':refund' => $total_refund, ':refno' => $refno]);
                } catch (Exception $e) {}

                try {
                    $db->prepare("UPDATE yl_invoices inv JOIN yl_delivery_receipts dr ON inv.DR_ID = dr.DR_ID JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID SET inv.Net_Amount = inv.Net_Amount + (:refund / 1.12), inv.VAT = inv.VAT + (:refund - (:refund / 1.12)), inv.Amount_Due = inv.Amount_Due + :refund WHERE so.SO_No = :refno")->execute([':refund' => $total_refund, ':refno' => $refno]);
                } catch (Exception $e) {}
            }
        }
        $db->prepare("DELETE FROM stock_returns WHERE Return_ID = :id")->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
    }
}
?>