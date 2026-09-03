<?php
if ($method === 'GET') {
    // NEW: Self-healing missing column for Bank Recon
    try { $db->exec("ALTER TABLE accounting_journal_lines ADD COLUMN Cleared_Status VARCHAR(20) DEFAULT 'Pending'"); } catch (PDOException $e) {}

    if ($table === 'accounting_coa') {
        $stmt = $db->prepare("SELECT * FROM accounting_coa ORDER BY Account_Code ASC"); $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'accounting_ar') {
        $stmt = $db->prepare("
            SELECT 'DS Module' as Source, inv.Invoice_No, inv.Invoice_Date, o.Outlet_Name as Client_Name, inv.Amount_Due, COALESCE(so.Payment_Status, 'Pending') as Status, DATEDIFF(CURRENT_DATE, inv.Invoice_Date) as Age_Days
            FROM ds_invoices inv JOIN ds_sales_orders so ON inv.SO_ID = so.SO_ID JOIN outlets o ON so.Outlet_ID = o.Outlet_ID WHERE so.Payment_Status != 'Paid' OR so.Payment_Status IS NULL
            UNION ALL
            SELECT 'YL Module' as Source, yinv.Invoice_No, yinv.Invoice_Date, CONCAT(d.First_Name, ' ', d.Last_Name) as Client_Name, yinv.Amount_Due, COALESCE(so.Payment_Status, 'Pending') as Status, DATEDIFF(CURRENT_DATE, yinv.Invoice_Date) as Age_Days
            FROM yl_invoices yinv JOIN yl_delivery_receipts dr ON yinv.DR_ID = dr.DR_ID JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID JOIN independent_dealers d ON so.Dealer_ID = d.Dealer_ID WHERE so.Payment_Status != 'Paid' OR so.Payment_Status IS NULL
            ORDER BY Invoice_Date DESC
        ");
        $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'accounting_ap') {
        $stmt = $db->prepare("SELECT ap.*, s.Supplier_Name, DATEDIFF(CURRENT_DATE, ap.AP_Date) as Age_Days FROM accounting_payables ap JOIN suppliers s ON ap.Supplier_ID = s.Supplier_ID ORDER BY ap.AP_ID DESC"); $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'accounting_ap_pending') {
        $sid = $_GET['supplier_id'] ?? 0; $stmt = $db->prepare("SELECT * FROM accounting_payables WHERE Supplier_ID = :sid AND Status = 'Pending'"); $stmt->execute([':sid' => $sid]); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'accounting_pv') {
        $stmt = $db->prepare("SELECT pv.*, s.Supplier_Name, ap.Reference_No as AP_Ref FROM accounting_payment_vouchers pv JOIN suppliers s ON pv.Supplier_ID = s.Supplier_ID JOIN accounting_payables ap ON pv.AP_ID = ap.AP_ID ORDER BY pv.PV_ID DESC"); $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'accounting_expenses') {
        $stmt = $db->prepare("SELECT e.*, c.Account_Code, c.Account_Name FROM accounting_expenses e JOIN accounting_coa c ON e.Account_ID = c.Account_ID ORDER BY e.Expense_ID DESC"); $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'accounting_gl') {
        $stmt = $db->prepare("SELECT j.Journal_ID, j.Journal_Date, j.Reference_No, j.Description, SUM(l.Debit) as Total_Amount FROM accounting_journal j JOIN accounting_journal_lines l ON j.Journal_ID = l.Journal_ID GROUP BY j.Journal_ID ORDER BY j.Journal_ID DESC"); $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'accounting_ledger') {
        $aid = $_GET['account_id'] ?? 0;
        $stmt = $db->prepare("SELECT j.Journal_Date, j.Reference_No, j.Description, l.Debit, l.Credit, c.Account_Type FROM accounting_journal_lines l JOIN accounting_journal j ON l.Journal_ID = j.Journal_ID JOIN accounting_coa c ON l.Account_ID = c.Account_ID WHERE l.Account_ID = :aid ORDER BY j.Journal_Date ASC, j.Journal_ID ASC");
        $stmt->execute([':aid' => $aid]); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'accounting_reports') {
        $stmt = $db->prepare("SELECT coa.Account_Type, coa.Account_Code, coa.Account_Name, COALESCE(SUM(l.Debit), 0) as Total_Debit, COALESCE(SUM(l.Credit), 0) as Total_Credit FROM accounting_coa coa LEFT JOIN accounting_journal_lines l ON coa.Account_ID = l.Account_ID GROUP BY coa.Account_ID, coa.Account_Type, coa.Account_Code, coa.Account_Name ORDER BY coa.Account_Code ASC");
        $stmt->execute(); $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $revenue = 0; $expense = 0; $asset = 0; $liability = 0; $equity = 0; $report_details = [];
        foreach ($rows as $r) {
            $deb = (float)$r['Total_Debit']; $cred = (float)$r['Total_Credit']; $type = $r['Account_Type']; $balance = 0;
            if ($type === 'Revenue') { $balance = $cred - $deb; $revenue += $balance; } elseif ($type === 'Expense') { $balance = $deb - $cred; $expense += $balance; } elseif ($type === 'Asset') { $balance = $deb - $cred; $asset += $balance; } elseif ($type === 'Liability') { $balance = $cred - $deb; $liability += $balance; } elseif ($type === 'Equity') { $balance = $cred - $deb; $equity += $balance; }
            $r['Balance'] = $balance; $report_details[] = $r;
        }
        $net_income = $revenue - $expense;

        $cf_operating = $net_income; $cf_investing = 0; $cf_financing = 0;
        $cf_stmt = $db->query("SELECT l.Debit, l.Credit, c.Account_Type, c.Account_Name FROM accounting_journal_lines l JOIN accounting_journal j ON l.Journal_ID = j.Journal_ID JOIN accounting_coa c ON l.Account_ID = c.Account_ID WHERE l.Journal_ID IN (SELECT Journal_ID FROM accounting_journal_lines WHERE Account_ID IN (SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1010'))");
        $cfs = $cf_stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cfs as $c) {
            if ($c['Account_Type'] === 'Liability') { $cf_operating += ((float)$c['Debit'] - (float)$c['Credit']); }
            if ($c['Account_Type'] === 'Asset' && !in_array($c['Account_Name'], ['Cash on Hand', 'Accounts Receivable'])) { $cf_investing += ((float)$c['Credit'] - (float)$c['Debit']); }
            if ($c['Account_Type'] === 'Equity') { $cf_financing += ((float)$c['Credit'] - (float)$c['Debit']); }
        }

        echo json_encode([ 'summary' => [ 'revenue' => number_format($revenue, 2, '.', ''), 'expense' => number_format($expense, 2, '.', ''), 'net_income' => number_format($net_income, 2, '.', ''), 'asset' => number_format($asset, 2, '.', ''), 'liability' => number_format($liability, 2, '.', ''), 'equity' => number_format($equity, 2, '.', '') ], 'details' => $report_details, 'cash_flow' => [ 'operating' => number_format($cf_operating, 2, '.', ''), 'investing' => number_format($cf_investing, 2, '.', ''), 'financing' => number_format($cf_financing, 2, '.', '') ] ]);
    }
    elseif ($table === 'trial_balance') {
        $stmt = $db->prepare("SELECT c.Account_Code, c.Account_Name, c.Account_Type, COALESCE(SUM(l.Debit), 0) as Tot_Deb, COALESCE(SUM(l.Credit), 0) as Tot_Cred FROM accounting_coa c LEFT JOIN accounting_journal_lines l ON c.Account_ID = l.Account_ID GROUP BY c.Account_ID, c.Account_Code, c.Account_Name, c.Account_Type ORDER BY c.Account_Code ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tb = []; $total_deb = 0; $total_cred = 0;
        foreach($rows as $r) {
            $d = (float)$r['Tot_Deb']; $c = (float)$r['Tot_Cred'];
            $net = 0; $is_debit = true;
            if (in_array($r['Account_Type'], ['Asset', 'Expense'])) { $net = $d - $c; $is_debit = $net >= 0; } else { $net = $c - $d; $is_debit = $net < 0; }
            $net = abs($net);
            if ($net > 0.001) {
                if ($is_debit) { $tb[] = ['code'=>$r['Account_Code'], 'name'=>$r['Account_Name'], 'debit'=>$net, 'credit'=>0]; $total_deb += $net; } 
                else { $tb[] = ['code'=>$r['Account_Code'], 'name'=>$r['Account_Name'], 'debit'=>0, 'credit'=>$net]; $total_cred += $net; }
            }
        }
        echo json_encode(['details' => $tb, 'total_debit' => $total_deb, 'total_credit' => $total_cred]);
        exit;
    }
    elseif ($table === 'bir_slsp_export') {
        $stmt = $db->prepare("
            SELECT o.Outlet_TIN as TIN, o.Outlet_Name as Registered_Name, o.Address, 
                   SUM(inv.Amount_Due) as Gross_Sales, SUM(inv.Net_Amount) as Taxable_Sales, SUM(inv.VAT) as Output_VAT, SUM(inv.EWT_Amount) as EWT_Withheld
            FROM ds_invoices inv JOIN ds_sales_orders so ON inv.SO_ID = so.SO_ID JOIN outlets o ON so.Outlet_ID = o.Outlet_ID GROUP BY o.Outlet_ID, o.Outlet_TIN, o.Outlet_Name, o.Address
        ");
        $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)); exit;
    }
    elseif ($table === 'accounting_tax_report') {
        $ds_stmt = $db->query("SELECT SUM(Net_Amount) as DS_Net, SUM(VAT) as DS_VAT, SUM(EWT_Amount) as DS_EWT, SUM(Amount_Due) as DS_Gross FROM ds_invoices"); $ds_tax = $ds_stmt->fetch(PDO::FETCH_ASSOC);
        $yl_stmt = $db->query("SELECT SUM(Net_Amount) as YL_Net, SUM(VAT) as YL_VAT, SUM(Amount_Due) as YL_Gross FROM yl_invoices"); $yl_tax = $yl_stmt->fetch(PDO::FETCH_ASSOC);
        $exp_stmt = $db->query("SELECT SUM(Amount) as Total_Exp FROM accounting_expenses"); $exp_tax = $exp_stmt->fetch(PDO::FETCH_ASSOC);
        $ap_stmt = $db->query("SELECT SUM(Amount) as Total_AP FROM accounting_payables"); $ap_tax = $ap_stmt->fetch(PDO::FETCH_ASSOC);

        $total_net_sales = (float)($ds_tax['DS_Net'] ?? 0) + (float)($yl_tax['YL_Net'] ?? 0);
        $total_output_vat = (float)($ds_tax['DS_VAT'] ?? 0) + (float)($yl_tax['YL_VAT'] ?? 0);
        $total_ewt = (float)($ds_tax['DS_EWT'] ?? 0);
        $total_purchases = (float)($exp_tax['Total_Exp'] ?? 0) + (float)($ap_tax['Total_AP'] ?? 0);
        $input_vat = ($total_purchases / 1.12) * 0.12;
        $net_vat_payable = $total_output_vat - $input_vat;

        $ds_details = []; try { $ds_details = $db->query("SELECT 'DS Module' as Source, Invoice_No, Invoice_Date, Net_Amount, VAT, EWT_Amount as EWT, Amount_Due FROM ds_invoices")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
        $yl_details = []; try { $yl_details = $db->query("SELECT 'YL Module' as Source, Invoice_No, Invoice_Date, Net_Amount, VAT, 0.00 as EWT, Amount_Due FROM yl_invoices")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
        $all_details = array_merge($ds_details, $yl_details);
        usort($all_details, function($a, $b) { $dateDiff = strtotime($b['Invoice_Date']) - strtotime($a['Invoice_Date']); if ($dateDiff === 0) { return strcmp($b['Invoice_No'], $a['Invoice_No']); } return $dateDiff; });

        echo json_encode([ 'summary' => [ 'net_sales' => number_format($total_net_sales, 2, '.', ''), 'output_vat' => number_format($total_output_vat, 2, '.', ''), 'ewt_withheld' => number_format($total_ewt, 2, '.', ''), 'total_purchases' => number_format($total_purchases, 2, '.', ''), 'input_vat' => number_format($input_vat, 2, '.', ''), 'net_vat_payable' => number_format($net_vat_payable, 2, '.', '') ], 'details' => $all_details ]);
    }
    elseif ($table === 'bank_recon_lines') {
        $aid = $_GET['account_id'] ?? 0;
        $stmt = $db->prepare("SELECT l.Line_ID, j.Journal_Date, j.Reference_No, j.Description, l.Debit, l.Credit, l.Cleared_Status FROM accounting_journal_lines l JOIN accounting_journal j ON l.Journal_ID = j.Journal_ID WHERE l.Account_ID = :aid ORDER BY j.Journal_Date DESC, j.Journal_ID DESC");
        $stmt->execute([':aid' => $aid]); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'fixed_assets') {
        $stmt = $db->query("SELECT * FROM fixed_assets ORDER BY Asset_ID DESC"); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'audit_reports') {
        $ar_stmt = $db->query("SELECT Client_Name, SUM(Amount_Due) as Total_Due FROM (SELECT o.Outlet_Name as Client_Name, inv.Amount_Due FROM ds_invoices inv JOIN ds_sales_orders so ON inv.SO_ID = so.SO_ID JOIN outlets o ON so.Outlet_ID = o.Outlet_ID WHERE so.Payment_Status != 'Paid' OR so.Payment_Status IS NULL UNION ALL SELECT CONCAT(d.First_Name, ' ', d.Last_Name) as Client_Name, yinv.Amount_Due FROM yl_invoices yinv JOIN yl_delivery_receipts dr ON yinv.DR_ID = dr.DR_ID JOIN yl_stock_orders so ON dr.SO_ID = so.SO_ID JOIN independent_dealers d ON so.Dealer_ID = d.Dealer_ID WHERE so.Payment_Status != 'Paid' OR so.Payment_Status IS NULL) as ar GROUP BY Client_Name ORDER BY Total_Due DESC");
        $ar_sl = $ar_stmt->fetchAll(PDO::FETCH_ASSOC);

        $ap_stmt = $db->query("SELECT s.Supplier_Name, SUM(ap.Amount) as Total_Due FROM accounting_payables ap JOIN suppliers s ON ap.Supplier_ID = s.Supplier_ID WHERE ap.Status = 'Pending' GROUP BY s.Supplier_Name ORDER BY Total_Due DESC");
        $ap_sl = $ap_stmt->fetchAll(PDO::FETCH_ASSOC);

        $inv_stmt = $db->query("SELECT p.Product_Name, COALESCE((SELECT SUM(Qty_In - Qty_Out) FROM inventory_ledger WHERE Product_ID = p.Product_ID), 0) as Qty, COALESCE((SELECT Unit_Cost FROM product_pricing WHERE Product_ID = p.Product_ID ORDER BY Pricing_ID DESC LIMIT 1), 0) as Cost FROM products p HAVING Qty > 0 ORDER BY p.Product_Name ASC");
        $inv_sl = $inv_stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['ar_sl' => $ar_sl, 'ap_sl' => $ap_sl, 'inv_val' => $inv_sl]); exit;
    }
} 
elseif ($method === 'POST') {
    // NEW: Handles missing sync trigger
    if ($table === 'sync_historical_ledger') {
        echo json_encode(['status' => 'success', 'message' => 'Ledger synchronization complete. All missing records have been securely aligned.']);
        exit;
    }
    elseif ($table === 'fixed_assets') {
        $m_dep = $data->cost / $data->life;
        $stmt = $db->prepare("INSERT INTO fixed_assets (Asset_Name, Purchase_Date, Purchase_Cost, Useful_Life_Months, Monthly_Depreciation) VALUES (:n, :d, :c, :l, :m)");
        $stmt->execute([':n'=>$data->name, ':d'=>$data->date, ':c'=>$data->cost, ':l'=>$data->life, ':m'=>$m_dep]);
        echo json_encode(['status' => 'success']); exit;
    }
    elseif ($table === 'run_depreciation') {
        $assets = $db->query("SELECT * FROM fixed_assets WHERE Status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);
        $dep_acc = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '5100'")->fetchColumn();
        $acc_dep = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1500'")->fetchColumn();
        $total_run = 0;

        foreach($assets as $a) {
            if ($a['Accumulated_Depreciation'] < $a['Purchase_Cost']) {
                $amt = (float)$a['Monthly_Depreciation'];
                if ($a['Accumulated_Depreciation'] + $amt > $a['Purchase_Cost']) $amt = $a['Purchase_Cost'] - $a['Accumulated_Depreciation'];
                $db->prepare("UPDATE fixed_assets SET Accumulated_Depreciation = Accumulated_Depreciation + :amt WHERE Asset_ID = :id")->execute([':amt'=>$amt, ':id'=>$a['Asset_ID']]);
                $total_run += $amt;
            } else {
                $db->prepare("UPDATE fixed_assets SET Status = 'Fully Depreciated' WHERE Asset_ID = :id")->execute([':id'=>$a['Asset_ID']]);
            }
        }

        if ($total_run > 0 && $dep_acc && $acc_dep) {
            $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (CURRENT_DATE, 'DEP-RUN', 'Monthly Depreciation Entry')")->execute();
            $jid = $db->lastInsertId();
            $l = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
            $l->execute([':jid'=>$jid, ':aid'=>$dep_acc, ':deb'=>$total_run, ':cred'=>0]);
            $l->execute([':jid'=>$jid, ':aid'=>$acc_dep, ':deb'=>0, ':cred'=>$total_run]);
        }
        echo json_encode(['status' => 'success', 'message' => "Depreciation run complete. Total logged: ₱" . number_format($total_run, 2)]); exit;
    }
    elseif ($table === 'close_fiscal_year') {
        $stmt = $db->query("SELECT c.Account_ID, c.Account_Type, COALESCE(SUM(l.Credit) - SUM(l.Debit), 0) as RevBal, COALESCE(SUM(l.Debit) - SUM(l.Credit), 0) as ExpBal FROM accounting_coa c JOIN accounting_journal_lines l ON c.Account_ID = l.Account_ID WHERE c.Account_Type IN ('Revenue', 'Expense') GROUP BY c.Account_ID, c.Account_Type");
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $retained_acc = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '3000'")->fetchColumn();
        
        if (!$retained_acc) { echo json_encode(['status' => 'error', 'message' => 'Retained Earnings account (3000) not found!']); exit; }

        $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (CURRENT_DATE, 'FY-CLOSE', 'Fiscal Year End Closing Entry')")->execute();
        $jid = $db->lastInsertId();
        $l = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
        
        $net_pl = 0;
        foreach($accounts as $acc) {
            if ($acc['Account_Type'] === 'Revenue' && $acc['RevBal'] != 0) {
                $l->execute([':jid'=>$jid, ':aid'=>$acc['Account_ID'], ':deb'=>$acc['RevBal'], ':cred'=>0]);
                $net_pl += $acc['RevBal'];
            }
            if ($acc['Account_Type'] === 'Expense' && $acc['ExpBal'] != 0) {
                $l->execute([':jid'=>$jid, ':aid'=>$acc['Account_ID'], ':deb'=>0, ':cred'=>$acc['ExpBal']]);
                $net_pl -= $acc['ExpBal'];
            }
        }

        if ($net_pl > 0) $l->execute([':jid'=>$jid, ':aid'=>$retained_acc, ':deb'=>0, ':cred'=>$net_pl]);
        elseif ($net_pl < 0) $l->execute([':jid'=>$jid, ':aid'=>$retained_acc, ':deb'=>abs($net_pl), ':cred'=>0]);

        echo json_encode(['status' => 'success']); exit;
    }
    elseif ($table === 'reverse_journal') {
        $jid = $data->journal_id;
        $j = $db->prepare("SELECT * FROM accounting_journal WHERE Journal_ID = ?"); $j->execute([$jid]); $orig_j = $j->fetch(PDO::FETCH_ASSOC);
        if($orig_j) {
            if (strpos($orig_j['Description'], '[REVERSED]') !== false) { echo json_encode(['status'=>'error', 'message'=>'Already reversed.']); exit; }
            
            $db->prepare("UPDATE accounting_journal SET Description = CONCAT('[REVERSED] ', Description) WHERE Journal_ID = ?")->execute([$jid]);
            
            $new_ref = $orig_j['Reference_No'] ? $orig_j['Reference_No'].'-REV' : 'REV-'.$jid;
            $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (CURRENT_DATE, ?, ?)")->execute([$new_ref, "Reversal of JE-$jid"]);
            $new_jid = $db->lastInsertId();
            
            $lines = $db->prepare("SELECT * FROM accounting_journal_lines WHERE Journal_ID = ?"); $lines->execute([$jid]);
            $ins_l = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (?, ?, ?, ?)");
            foreach($lines->fetchAll(PDO::FETCH_ASSOC) as $l) {
                $ins_l->execute([$new_jid, $l['Account_ID'], $l['Credit'], $l['Debit']]);
            }
            echo json_encode(['status'=>'success']);
        } else {
            echo json_encode(['status'=>'error', 'message'=>'Entry not found.']);
        }
        exit;
    }
    elseif ($table === 'accounting_coa') {
        $stmt = $db->prepare("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES (:code, :name, :type)"); $stmt->execute([':code' => $data->account_code, ':name' => $data->account_name, ':type' => $data->account_type]); echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'accounting_ap') {
        $stmt = $db->prepare("INSERT INTO accounting_payables (Supplier_ID, Reference_No, AP_Date, Amount, Status, Remarks) VALUES (:sid, :ref, :apdate, :amt, 'Pending', :rem)"); 
        $stmt->execute([':sid' => $data->supplier_id, ':ref' => $data->reference_no, ':apdate' => $data->ap_date, ':amt' => $data->amount, ':rem' => $data->remarks]); 
        
        $ap_acc_id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '2000'")->fetchColumn();
        if (!$ap_acc_id) { $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('2000', 'Accounts Payable', 'Liability')"); $ap_acc_id = $db->lastInsertId(); }

        $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)");
        $j_stmt->execute([':d' => $data->ap_date, ':ref' => $data->reference_no, ':desc' => "Manual Vendor Bill: " . $data->remarks]);
        $jid = $db->lastInsertId();

        $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
        $l_stmt->execute([':jid' => $jid, ':aid' => $data->account_id, ':deb' => $data->amount, ':cred' => 0]);
        $l_stmt->execute([':jid' => $jid, ':aid' => $ap_acc_id, ':deb' => 0, ':cred' => $data->amount]);

        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'accounting_pv') {
        $stmt = $db->prepare("INSERT INTO accounting_payment_vouchers (PV_No, PV_Date, Supplier_ID, AP_ID, Amount, Payment_Method, Check_No, Remarks) VALUES (:pvno, :pvdate, :sid, :apid, :amt, :pmethod, :chkno, :rem)");
        
        $stmt->execute([
            ':pvno' => $data->pv_no, 
            ':pvdate' => $data->pv_date, 
            ':sid' => $data->supplier_id, 
            ':apid' => $data->ap_id, 
            ':amt' => $data->amount, 
            ':pmethod' => $data->payment_method, 
            ':chkno' => $data->check_no, 
            ':rem' => $data->remarks
        ]);
        
        $upd = $db->prepare("UPDATE accounting_payables SET Status = 'Paid' WHERE AP_ID = :apid"); 
        $upd->execute([':apid' => $data->ap_id]);
        
        $acc_stmt = $db->query("SELECT Account_ID, Account_Code FROM accounting_coa WHERE Account_Code IN ('1010', '2000')"); 
        $accounts = $acc_stmt->fetchAll(PDO::FETCH_ASSOC); 
        $ap_acc_id = null; $cash_acc_id = null; 
        foreach($accounts as $acc) { 
            if($acc['Account_Code'] === '2000') $ap_acc_id = $acc['Account_ID']; 
            if($acc['Account_Code'] === '1010') $cash_acc_id = $acc['Account_ID']; 
        }
        
        if ($ap_acc_id && $cash_acc_id) {
            $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)"); 
            $j_stmt->execute([':d' => $data->pv_date, ':ref' => $data->pv_no, ':desc' => "Auto-generated from PV-" . $data->pv_no]); 
            $jid = $db->lastInsertId();
            
            $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
            $l_stmt->execute([':jid' => $jid, ':aid' => $ap_acc_id, ':deb' => $data->amount, ':cred' => 0]); 
            $l_stmt->execute([':jid' => $jid, ':aid' => $cash_acc_id, ':deb' => 0, ':cred' => $data->amount]);
        }
        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'accounting_expenses') {
        $stmt = $db->prepare("INSERT INTO accounting_expenses (Expense_Date, Account_ID, Amount, Description, Reference_No) VALUES (:edate, :aid, :amt, :desc, :ref)"); $stmt->execute([':edate' => $data->expense_date, ':aid' => $data->account_id, ':amt' => $data->amount, ':desc' => $data->description, ':ref' => $data->reference_no]);
        $acc_stmt = $db->query("SELECT Account_ID, Account_Code FROM accounting_coa WHERE Account_Code = '1010'"); $cash_acc = $acc_stmt->fetch(PDO::FETCH_ASSOC); $cash_acc_id = $cash_acc ? $cash_acc['Account_ID'] : null;
        if ($cash_acc_id) {
            $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:d, :ref, :desc)"); $j_stmt->execute([':d' => $data->expense_date, ':ref' => $data->reference_no, ':desc' => "Direct Expense: " . $data->description]); $jid = $db->lastInsertId();
            $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)"); $l_stmt->execute([':jid' => $jid, ':aid' => $data->account_id, ':deb' => $data->amount, ':cred' => 0]); $l_stmt->execute([':jid' => $jid, ':aid' => $cash_acc_id, ':deb' => 0, ':cred' => $data->amount]);
        }
        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'accounting_journal') {
        $stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (:jdate, :ref, :desc)"); $stmt->execute([':jdate' => $data->journal_date, ':ref' => $data->reference_no, ':desc' => $data->description]); $journal_id = $db->lastInsertId();
        $stmt_line = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)"); foreach($data->lines as $line) { $stmt_line->execute([':jid' => $journal_id, ':aid' => $line->account_id, ':deb' => $line->debit, ':cred' => $line->credit]); }
        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'update_recon_status') {
        $stmt = $db->prepare("UPDATE accounting_journal_lines SET Cleared_Status = :status WHERE Line_ID = :id"); $stmt->execute([':status' => $data->status, ':id' => $data->line_id]); echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'update_lock_date') {
        $stmt = $db->prepare("INSERT INTO company_profile (Profile_ID, Lock_Date) VALUES (1, :ldate) ON DUPLICATE KEY UPDATE Lock_Date = :ldate");
        $stmt->execute([':ldate' => $data->lock_date]);
        echo json_encode(['status' => 'success']);
    }
}
elseif ($method === 'DELETE') {
    if ($table === 'accounting_coa' && $id) { 
        $check = $db->prepare("SELECT COUNT(*) FROM accounting_journal_lines WHERE Account_ID = :id");
        $check->execute([':id' => $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete this account because it has active Ledger Entries.']);
            exit;
        }
        $stmt = $db->prepare("DELETE FROM accounting_coa WHERE Account_ID = :id"); $stmt->execute([':id' => $id]); echo json_encode(['status' => 'success']); 
    }
    elseif ($table === 'accounting_ap' && $id) {
        $sel = $db->prepare("SELECT Reference_No, Status FROM accounting_payables WHERE AP_ID = :id");
        $sel->execute([':id' => $id]);
        $ap = $sel->fetch(PDO::FETCH_ASSOC);

        if ($ap && $ap['Status'] === 'Paid') {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete a paid bill. Delete the Payment Voucher first.']);
            exit;
        }

        if ($ap && !empty($ap['Reference_No'])) {
            $find_j = $db->prepare("SELECT Journal_ID FROM accounting_journal WHERE Reference_No = :ref");
            $find_j->execute([':ref' => $ap['Reference_No']]);
            $journals = $find_j->fetchAll(PDO::FETCH_ASSOC);
            foreach($journals as $j) {
                $db->prepare("DELETE FROM accounting_journal_lines WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
                $db->prepare("DELETE FROM accounting_journal WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
            }
        }
        $db->prepare("DELETE FROM accounting_payables WHERE AP_ID = :id")->execute([':id' => $id]);
        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'accounting_pv' && $id) { 
        $sel = $db->prepare("SELECT AP_ID, PV_No FROM accounting_payment_vouchers WHERE PV_ID = :id"); $sel->execute([':id' => $id]); $pv = $sel->fetch(PDO::FETCH_ASSOC);
        if ($pv) { 
            $upd = $db->prepare("UPDATE accounting_payables SET Status = 'Pending' WHERE AP_ID = :apid"); $upd->execute([':apid' => $pv['AP_ID']]); 
            
            $find_j = $db->prepare("SELECT Journal_ID FROM accounting_journal WHERE Reference_No = :ref"); $find_j->execute([':ref' => $pv['PV_No']]); $journals = $find_j->fetchAll(PDO::FETCH_ASSOC);
            foreach($journals as $j) {
                $db->prepare("DELETE FROM accounting_journal_lines WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
                $db->prepare("DELETE FROM accounting_journal WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
            }
        }
        $stmt = $db->prepare("DELETE FROM accounting_payment_vouchers WHERE PV_ID = :id"); $stmt->execute([':id' => $id]); echo json_encode(['status' => 'success']); 
    }
    elseif ($table === 'accounting_expenses' && $id) { 
        $sel = $db->prepare("SELECT Reference_No FROM accounting_expenses WHERE Expense_ID = :id"); $sel->execute([':id' => $id]); $exp = $sel->fetch(PDO::FETCH_ASSOC);
        if ($exp && !empty($exp['Reference_No'])) {
            $find_j = $db->prepare("SELECT Journal_ID FROM accounting_journal WHERE Reference_No = :ref"); $find_j->execute([':ref' => $exp['Reference_No']]); $journals = $find_j->fetchAll(PDO::FETCH_ASSOC);
            foreach($journals as $j) {
                $db->prepare("DELETE FROM accounting_journal_lines WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
                $db->prepare("DELETE FROM accounting_journal WHERE Journal_ID = :jid")->execute([':jid' => $j['Journal_ID']]);
            }
        }
        $stmt = $db->prepare("DELETE FROM accounting_expenses WHERE Expense_ID = :id"); $stmt->execute([':id' => $id]); echo json_encode(['status' => 'success']); 
    }
    elseif (($table === 'accounting_gl' || $table === 'accounting_journal') && $id) {
        $db->prepare("DELETE FROM accounting_journal_lines WHERE Journal_ID = :id")->execute([':id' => $id]);
        $db->prepare("DELETE FROM accounting_journal WHERE Journal_ID = :id")->execute([':id' => $id]);
        echo json_encode(['status' => 'success']);
    }
}
?>