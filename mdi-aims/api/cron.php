<?php
require_once 'db.php';

$secret = 'mdi_secure_cron_2026'; 
$provided_secret = $_GET['key'] ?? ($argv[1] ?? '');

if ($provided_secret !== $secret) {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized CRON execution.']));
}

$database = new Database();
$db = $database->getConnection();
$response_log = [];

try {
    $backupDir = '../backups/';
    if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
    
    $tables = [];
    $q = $db->query("SHOW TABLES");
    while($row = $q->fetch(PDO::FETCH_NUM)) { $tables[] = $row[0]; }
    
    $sql = "-- MDI AIMS Automated Database Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
    foreach($tables as $t) {
        $row2 = $db->query("SHOW CREATE TABLE $t")->fetch(PDO::FETCH_NUM);
        $sql .= "\n\n" . $row2[1] . ";\n\n";
        $rows = $db->query("SELECT * FROM $t")->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $r) {
            $vals = array_map(function($val) use ($db) { return $val === null ? 'NULL' : $db->quote($val); }, array_values($r));
            $sql .= "INSERT INTO $t VALUES(" . implode(", ", $vals) . ");\n";
        }
    }
    
    $fileName = 'mdi_aims_backup_' . date('Y-m-d') . '.sql';
    file_put_contents($backupDir . $fileName, $sql);
    
    $files = glob($backupDir . '*.sql');
    if (count($files) > 7) {
        usort($files, function($a, $b) { return filemtime($a) - filemtime($b); });
        unlink($files[0]); 
    }
    
    $response_log[] = "Daily backup created successfully.";
} catch (Exception $e) {
    $response_log[] = "Backup Failed: " . $e->getMessage();
}

if (date('d') === '01') {
    try {
        $assets = $db->query("SELECT * FROM fixed_assets WHERE Status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);
        $dep_acc = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '5100'")->fetchColumn();
        $acc_dep = $db->query("SELECT Account_ID FROM accounting_coa WHERE Account_Code = '1500'")->fetchColumn();
        $total_run = 0;
        
        foreach($assets as $a) {
            if ($a['Accumulated_Depreciation'] < $a['Purchase_Cost']) {
                $amt = (float)$a['Monthly_Depreciation'];
                if ($a['Accumulated_Depreciation'] + $amt > $a['Purchase_Cost']) {
                    $amt = $a['Purchase_Cost'] - $a['Accumulated_Depreciation'];
                }
                $db->prepare("UPDATE fixed_assets SET Accumulated_Depreciation = Accumulated_Depreciation + :amt WHERE Asset_ID = :id")->execute([':amt'=>$amt, ':id'=>$a['Asset_ID']]);
                $total_run += $amt;
            } else {
                $db->prepare("UPDATE fixed_assets SET Status = 'Fully Depreciated' WHERE Asset_ID = :id")->execute([':id'=>$a['Asset_ID']]);
            }
        }
        if ($total_run > 0 && $dep_acc && $acc_dep) {
            $db->prepare("INSERT INTO accounting_journal (Journal_Date, Reference_No, Description) VALUES (CURRENT_DATE, 'DEP-AUTO', 'Automated Monthly Depreciation')")->execute();
            $jid = $db->lastInsertId();
            $l = $db->prepare("INSERT INTO accounting_journal_lines (Journal_ID, Account_ID, Debit, Credit) VALUES (:jid, :aid, :deb, :cred)");
            $l->execute([':jid'=>$jid, ':aid'=>$dep_acc, ':deb'=>$total_run, ':cred'=>0]);
            $l->execute([':jid'=>$jid, ':aid'=>$acc_dep, ':deb'=>0, ':cred'=>$total_run]);
        }
        $response_log[] = "Monthly depreciation run complete. Total logged: " . number_format($total_run, 2);
    } catch (Exception $e) {
        $response_log[] = "Depreciation Failed: " . $e->getMessage();
    }
} else {
    $response_log[] = "Depreciation skipped (not the 1st of the month).";
}

try {
    $stmt = $db->prepare("INSERT INTO audit_logs (Username, Action, Details) VALUES ('SYSTEM', 'CRON AUTOMATION', ?)");
    $stmt->execute([implode(" | ", $response_log)]);
} catch (Exception $e) {}

echo json_encode(['status' => 'success', 'log' => $response_log]);
?>