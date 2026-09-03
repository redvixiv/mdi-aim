<?php
if (!isset($db)) { exit; }

if ($method === 'GET') {
    if ($table === 'suppliers') {
        $stmt = $db->prepare("SELECT * FROM suppliers ORDER BY Supplier_ID DESC"); 
        $stmt->execute(); 
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} 
elseif ($method === 'POST') {
    if ($table === 'suppliers') {
        // FIXED: Added UPDATE logic alongside INSERT
        if (!empty($data->supplier_id)) {
            $stmt = $db->prepare("UPDATE suppliers SET Supplier_Name=:sname, Province=:prov, City=:city, Barangay=:brgy, Address=:addr, Contact_Name=:cname, Contact_No=:cno WHERE Supplier_ID=:id");
            $stmt->execute([':sname'=>$data->name, ':prov'=>$data->province, ':city'=>$data->city, ':brgy'=>$data->barangay, ':addr'=>$data->address, ':cname'=>$data->contact_name, ':cno'=>$data->contact_no, ':id'=>$data->supplier_id]); 
            logAudit($db, 'UPDATE RECORD', "Updated Supplier ID: {$data->supplier_id}");
        } else {
            $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(Supplier_No, 3) AS UNSIGNED)) AS max_no FROM suppliers"); 
            $row = $stmt->fetch(PDO::FETCH_ASSOC); 
            $supplier_no = "S-" . ($row['max_no'] ? $row['max_no'] + 1 : 10001);
            
            $stmt = $db->prepare("INSERT INTO suppliers (Supplier_No, Supplier_Name, Province, City, Barangay, Address, Contact_Name, Contact_No) VALUES (:sno, :sname, :prov, :city, :brgy, :addr, :cname, :cno)");
            $stmt->execute([':sno'=>$supplier_no, ':sname'=>$data->name, ':prov'=>$data->province, ':city'=>$data->city, ':brgy'=>$data->barangay, ':addr'=>$data->address, ':cname'=>$data->contact_name, ':cno'=>$data->contact_no]); 
            logAudit($db, 'CREATE RECORD', "Created Supplier: {$data->name}");
        }
        echo json_encode(['status' => 'success']);
    }
}
elseif ($method === 'DELETE') {
    if ($table === 'suppliers' && $id) { 
        $check = $db->prepare("SELECT COUNT(*) FROM purchase_orders WHERE Supplier_ID = :id");
        $check->execute([':id' => $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete supplier because they have active Purchase Orders.']);
            exit;
        }
        $stmt = $db->prepare("DELETE FROM suppliers WHERE Supplier_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
    }
}
?>