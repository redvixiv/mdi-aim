<?php
// Ensure direct access is blocked without $method set by endpoints.php
if (!isset($method)) exit;

if ($method === 'GET') {
    if ($table === 'employees') {
        $stmt = $db->prepare("SELECT * FROM employees ORDER BY Emp_ID DESC");
        $stmt->execute(); 
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    elseif ($table === 'dtr') {
        $stmt = $db->prepare("
            SELECT d.*, e.First_Name, e.Last_Name, e.Emp_No 
            FROM dtr d 
            JOIN employees e ON d.Emp_ID = e.Emp_ID 
            ORDER BY d.Cutoff_Start DESC, e.First_Name ASC
        ");
        $stmt->execute(); 
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    elseif ($table === 'payroll_records') {
        $stmt = $db->prepare("
            SELECT p.*, e.First_Name, e.Last_Name, e.Department, e.Position 
            FROM payroll_records p 
            JOIN employees e ON p.Emp_ID = e.Emp_ID 
            ORDER BY p.Payroll_ID DESC
        ");
        $stmt->execute(); 
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
} 
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input")); 
    
    if ($table === 'employees') {
        if (empty($data->emp_id)) {
            $q = $db->query("SELECT MAX(CAST(REPLACE(Emp_No, 'EMP-', '') AS UNSIGNED)) AS max_no FROM employees WHERE Emp_No LIKE 'EMP-%'");
            $row = $q->fetch(PDO::FETCH_ASSOC);
            $next_no = $row['max_no'] ? $row['max_no'] + 1 : 1001;
            $emp_no = "EMP-" . str_pad($next_no, 3, "0", STR_PAD_LEFT);

            $stmt = $db->prepare("INSERT INTO employees (Emp_No, First_Name, Last_Name, Position, Department, Basic_Rate, Rate_Type, SSS_No, PhilHealth_No, PagIBIG_No, TIN, Status, Hire_Date) VALUES (:eno, :fname, :lname, :pos, :dept, :brate, :rtype, :sss, :phic, :hdmf, :tin, :stat, :hdate)");
            $stmt->execute([
                ':eno' => $emp_no,
                ':fname' => $data->fname,
                ':lname' => $data->lname,
                ':pos' => $data->pos,
                ':dept' => $data->dept,
                ':brate' => $data->base_rate,
                ':rtype' => $data->rate_type,
                ':sss' => $data->sss,
                ':phic' => $data->phic,
                ':hdmf' => $data->hdmf,
                ':tin' => $data->tin,
                ':stat' => $data->status,
                ':hdate' => $data->hire_date
            ]);
            logAudit($db, 'HR ACTION', "Added new employee: {$data->fname} {$data->lname}");
        } else {
            $stmt = $db->prepare("UPDATE employees SET First_Name=:fname, Last_Name=:lname, Position=:pos, Department=:dept, Basic_Rate=:brate, Rate_Type=:rtype, SSS_No=:sss, PhilHealth_No=:phic, PagIBIG_No=:hdmf, TIN=:tin, Status=:stat, Hire_Date=:hdate WHERE Emp_ID=:id");
            $stmt->execute([
                ':fname' => $data->fname,
                ':lname' => $data->lname,
                ':pos' => $data->pos,
                ':dept' => $data->dept,
                ':brate' => $data->base_rate,
                ':rtype' => $data->rate_type,
                ':sss' => $data->sss,
                ':phic' => $data->phic,
                ':hdmf' => $data->hdmf,
                ':tin' => $data->tin,
                ':stat' => $data->status,
                ':hdate' => $data->hire_date,
                ':id' => $data->emp_id
            ]);
            logAudit($db, 'HR ACTION', "Updated employee details for ID: {$data->emp_id}");
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
    elseif ($table === 'dtr') {
        if (empty($data->dtr_id)) {
            $stmt = $db->prepare("INSERT INTO dtr (Emp_ID, Cutoff_Start, Cutoff_End, Days_Worked, OT_Hours, Late_Undertime_Hours) VALUES (:eid, :cstart, :cend, :days, :ot, :late)");
            $stmt->execute([
                ':eid' => $data->emp_id,
                ':cstart' => $data->cutoff_start,
                ':cend' => $data->cutoff_end,
                ':days' => $data->days_worked,
                ':ot' => $data->ot_hours,
                ':late' => $data->late_ut_hours
            ]);
            logAudit($db, 'HR ACTION', "Encoded DTR for Employee ID: {$data->emp_id}");
        } else {
            $stmt = $db->prepare("UPDATE dtr SET Emp_ID=:eid, Cutoff_Start=:cstart, Cutoff_End=:cend, Days_Worked=:days, OT_Hours=:ot, Late_Undertime_Hours=:late WHERE DTR_ID=:id");
            $stmt->execute([
                ':eid' => $data->emp_id,
                ':cstart' => $data->cutoff_start,
                ':cend' => $data->cutoff_end,
                ':days' => $data->days_worked,
                ':ot' => $data->ot_hours,
                ':late' => $data->late_ut_hours,
                ':id' => $data->dtr_id
            ]);
            logAudit($db, 'HR ACTION', "Updated DTR ID: {$data->dtr_id}");
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
    // FIXED: Properly listening for the action parameter passed through endpoints.php
    elseif ($table === 'payroll_records' && isset($_GET['action']) && $_GET['action'] === 'generate') {
        $start = $data->cutoff_start;
        $end = $data->cutoff_end;
        
        $stmt = $db->prepare("SELECT d.*, e.Basic_Rate, e.Rate_Type FROM dtr d JOIN employees e ON d.Emp_ID = e.Emp_ID WHERE d.Cutoff_Start = :start AND d.Cutoff_End = :end");
        $stmt->execute([':start' => $start, ':end' => $end]);
        $dtrs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($dtrs) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No DTRs found for this cutoff period.']);
            exit;
        }

        $total_gross = 0; $total_sss = 0; $total_phic = 0; $total_hdmf = 0; $total_tax = 0; $total_net = 0;
        
        $ins_pr = $db->prepare("INSERT INTO payroll_records (Emp_ID, Cutoff_Start, Cutoff_End, Gross_Pay, SSS_Deduct, PHIC_Deduct, HDMF_Deduct, Tax_Deduct, Net_Pay) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($dtrs as $dtr) {
            $brate = (float)$dtr['Basic_Rate'];
            
            $daily_rate = $dtr['Rate_Type'] === 'Monthly' ? ($brate / 22) : $brate;
            $hourly_rate = $daily_rate / 8;

            $gross = ($daily_rate * (float)$dtr['Days_Worked']) 
                   + ((float)$dtr['OT_Hours'] * ($hourly_rate * 1.25)) 
                   - ((float)$dtr['Late_Undertime_Hours'] * $hourly_rate);
            
            $sss = $gross * 0.045; 
            $phic = $gross * 0.02;
            $hdmf = 100.00;
            $tax = $gross > 10000 ? ($gross * 0.10) : 0;

            $net = $gross - ($sss + $phic + $hdmf + $tax);

            $ins_pr->execute([
                $dtr['Emp_ID'], $start, $end, $gross, $sss, $phic, $hdmf, $tax, $net
            ]);

            $total_gross += $gross; $total_sss += $sss; $total_phic += $phic; $total_hdmf += $hdmf; $total_tax += $tax; $total_net += $net;
        }

        if (!function_exists('ensureAccount')) {
            function ensureAccount($db, $code, $name, $type) {
                $id = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '$code'")->fetchColumn();
                if (!$id) {
                    $db->query("INSERT INTO accounting_coa (Account_Code, Account_Name, Account_Type) VALUES ('$code', '$name', '$type')");
                    return $db->lastInsertId();
                }
                return $id;
            }
        }

        $sal_exp_id = ensureAccount($db, '5300', 'Salaries and Wages', 'Expense');
        $cash_id = ensureAccount($db, '1010', 'Cash in Bank / On Hand', 'Asset');
        $sss_pay_id = ensureAccount($db, '2201', 'SSS Payable', 'Liability');
        $phic_pay_id = ensureAccount($db, '2202', 'PhilHealth Payable', 'Liability');
        $hdmf_pay_id = ensureAccount($db, '2203', 'Pag-IBIG Payable', 'Liability');
        $tax_pay_id = ensureAccount($db, '2204', 'Withholding Tax Payable', 'Liability');

        if ($sal_exp_id && $cash_id) {
            $j_stmt = $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (?, ?, ?)");
            $j_stmt->execute([date('Y-m-d'), "PR-$start", "Payroll Batch $start to $end"]);
            $jid = $db->lastInsertId();

            $l_stmt = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (?, ?, ?, ?)");
            
            $l_stmt->execute([$jid, $sal_exp_id, $total_gross, 0]);
            
            if($total_sss > 0) $l_stmt->execute([$jid, $sss_pay_id, 0, $total_sss]);
            if($total_phic > 0) $l_stmt->execute([$jid, $phic_pay_id, 0, $total_phic]);
            if($total_hdmf > 0) $l_stmt->execute([$jid, $hdmf_pay_id, 0, $total_hdmf]);
            if($total_tax > 0) $l_stmt->execute([$jid, $tax_pay_id, 0, $total_tax]);
            
            $l_stmt->execute([$jid, $cash_id, 0, $total_net]);
        }

        logAudit($db, 'PAYROLL', "Generated Payroll batch for $start to $end. Total Net: ₱" . number_format($total_net, 2));
        
        echo json_encode(['status' => 'success', 'message' => 'Payroll generated and posted to General Ledger successfully!']);
        exit;
    }
}
elseif ($method === 'DELETE') {
    if ($table === 'employees' && $id) {
        $stmt = $db->prepare("DELETE FROM employees WHERE Emp_ID = :id");
        $stmt->execute([':id' => $id]);
        logAudit($db, 'HR ACTION', "Deleted employee ID: $id");
        echo json_encode(['status' => 'success']);
        exit;
    }
    elseif ($table === 'dtr' && $id) {
        $stmt = $db->prepare("DELETE FROM dtr WHERE DTR_ID = :id");
        $stmt->execute([':id' => $id]);
        logAudit($db, 'HR ACTION', "Deleted DTR record ID: $id");
        echo json_encode(['status' => 'success']);
        exit;
    }
    elseif ($table === 'payroll_records' && $id) {
        $stmt = $db->prepare("DELETE FROM payroll_records WHERE Payroll_ID = :id");
        $stmt->execute([':id' => $id]);
        logAudit($db, 'PAYROLL', "Deleted specific Payroll record ID: $id");
        echo json_encode(['status' => 'success']);
        exit;
    }
}
?>