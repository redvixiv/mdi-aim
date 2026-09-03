<?php
if (!isset($db)) { exit; }

if ($method === 'GET') {
    if ($table === 'company_profile') {
        $stmt = $db->prepare("SELECT * FROM company_profile WHERE Profile_ID = 1"); 
        $stmt->execute(); 
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: null);
    }
} 
elseif ($method === 'POST') {
    if ($table === 'company_profile') {
        $company_name = $_POST['company_name'] ?? ''; 
        $tin = $_POST['tin'] ?? ''; 
        $province = $_POST['province'] ?? ''; 
        $city = $_POST['city'] ?? ''; 
        $barangay = $_POST['barangay'] ?? ''; 
        $address = $_POST['address'] ?? ''; 
        $contact_no = $_POST['contact_no'] ?? ''; 
        $logo_path = $_POST['existing_logo'] ?? '';
        
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/'; 
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
            $fileName = time() . '_' . basename($_FILES['logo']['name']);
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $fileName)) { 
                $logo_path = 'uploads/' . $fileName; 
            }
        }
        $query = "INSERT INTO company_profile (Profile_ID, Company_Name, TIN, Province, City, Barangay, Address, Contact_No, Logo_Path) VALUES (1, :name, :tin, :prov, :city, :brgy, :addr, :cno, :logo) ON DUPLICATE KEY UPDATE Company_Name=:name, TIN=:tin, Province=:prov, City=:city, Barangay=:brgy, Address=:addr, Contact_No=:cno, Logo_Path=:logo";
        $stmt = $db->prepare($query); 
        $stmt->execute([':name'=>$company_name, ':tin'=>$tin, ':prov'=>$province, ':city'=>$city, ':brgy'=>$barangay, ':addr'=>$address, ':cno'=>$contact_no, ':logo'=>$logo_path]);
        logAudit($db, 'SETTINGS', "Updated Company Profile details.");
        echo json_encode(['status' => 'success']); 
        exit;
    }
    elseif ($table === 'update_lock_date') {
        $lock_date = !empty($data->lock_date) ? $data->lock_date : null;
        $stmt = $db->prepare("UPDATE company_profile SET Lock_Date = :ldate WHERE Profile_ID = 1");
        $stmt->execute([':ldate' => $lock_date]);
        logAudit($db, 'SECURITY', "Updated Financial Lock Date to: " . ($lock_date ?: 'Unlocked'));
        echo json_encode(['status' => 'success']);
    }
}
?>