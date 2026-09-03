<?php
// Safe schema compatibility checks only. These are additive; destructive migration behavior has been removed.
try { $db->exec("ALTER TABLE outlets ADD COLUMN Business_Style VARCHAR(100) NULL"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE outlets ADD COLUMN DS_Section VARCHAR(50) NULL"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE outlets ADD COLUMN Category VARCHAR(50) NULL"); } catch (PDOException $e) {}

try { $db->exec("ALTER TABLE independent_dealers ADD COLUMN Center VARCHAR(100) NULL"); } catch (PDOException $e) {}
try { $db->exec("ALTER TABLE independent_dealers ADD COLUMN Center_Code VARCHAR(50) NULL"); } catch (PDOException $e) {}

if ($method === 'GET') {
    if ($table === 'dealers') {
        $stmt = $db->prepare("SELECT * FROM independent_dealers ORDER BY Dealer_ID DESC"); $stmt->execute(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'dealer_details') {
        $stmt = $db->prepare("SELECT * FROM independent_dealers WHERE Dealer_ID = :id"); $stmt->execute([':id' => $id]); echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'outlets') {
        $stmt = $db->prepare("SELECT * FROM outlets ORDER BY Outlet_ID DESC"); $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'outlet_details') {
        $stmt = $db->prepare("SELECT * FROM outlets WHERE Outlet_ID = :id"); $stmt->execute([':id' => $id]); echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    }
} 
elseif ($method === 'POST') {
    if ($table === 'dealers') {
        if (!empty($data->dealer_id)) {
            $stmt = $db->prepare("UPDATE independent_dealers SET First_Name = :fname, Middle_Name = :mname, Last_Name = :lname, Birth_Date = :bdate, Hiring_Date = :hdate, Center_Code = :ccode, Center = :center, Area = :area, Type = :type, Status = :status, Remarks = :remarks WHERE Dealer_ID = :id");
            $stmt->execute([
                ':fname' => $data->fname, ':mname' => $data->mname, ':lname' => $data->lname, 
                ':bdate' => !empty($data->bdate) ? $data->bdate : null, 
                ':hdate' => !empty($data->hdate) ? $data->hdate : null, 
                ':ccode' => $data->center_code ?? null,
                ':center' => $data->center ?? null, 
                ':area' => $data->area, ':type' => $data->type, ':status' => $data->status, 
                ':remarks' => $data->remarks, ':id' => $data->dealer_id
            ]);
        } else {
            $stmt = $db->query("SELECT MAX(CAST(Dealer_No AS UNSIGNED)) AS max_no FROM independent_dealers"); 
            $row = $stmt->fetch(PDO::FETCH_ASSOC); 
            $dealer_no = $row['max_no'] ? $row['max_no'] + 1 : 10001;
            
            $stmt = $db->prepare("INSERT INTO independent_dealers (Dealer_No, First_Name, Middle_Name, Last_Name, Birth_Date, Hiring_Date, Center_Code, Center, Area, Type, Status, Remarks) VALUES (:dno, :fname, :mname, :lname, :bdate, :hdate, :ccode, :center, :area, :type, :status, :remarks)");
            $stmt->execute([
                ':dno' => $dealer_no, ':fname' => $data->fname, ':mname' => $data->mname, 
                ':lname' => $data->lname, ':bdate' => !empty($data->bdate) ? $data->bdate : null, 
                ':hdate' => !empty($data->hdate) ? $data->hdate : null, 
                ':ccode' => $data->center_code ?? null,
                ':center' => $data->center ?? null, 
                ':area' => $data->area, ':type' => $data->type, ':status' => $data->status, 
                ':remarks' => $data->remarks
            ]);
        }
        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'outlets') {
        if (!empty($data->outlet_id)) {
            $stmt = $db->prepare("UPDATE outlets SET Outlet_Name = :oname, Outlet_TIN = :tin, Business_Style = :bstyle, DS_Section = :ds, Category = :cat, Branch = :branch, Province = :prov, City = :city, Barangay = :brgy, Address = :addr, Route = :rte, Contact_Person = :cperson, Contact_No = :cno, Terms = :terms WHERE Outlet_ID = :id");
            $stmt->execute([':oname'=>$data->customer_name, ':tin'=>$data->tin, ':bstyle'=>$data->bstyle, ':ds'=>$data->ds_section, ':cat'=>$data->category, ':branch'=>$data->branch, ':prov'=>$data->province, ':city'=>$data->city, ':brgy'=>$data->barangay, ':addr'=>$data->address, ':rte'=>$data->route, ':cperson'=>$data->contact_person, ':cno'=>$data->contact_no, ':terms'=>$data->terms, ':id' => $data->outlet_id]);
        } else {
            $o_stmt = $db->query("SELECT MAX(CAST(Outlet_No AS UNSIGNED)) AS max_no FROM outlets");
            $row = $o_stmt->fetch(PDO::FETCH_ASSOC);
            $next_seq = ($row['max_no'] && $row['max_no'] >= 100001) ? $row['max_no'] + 1 : 100001;
            
            $stmt = $db->prepare("INSERT INTO outlets (Outlet_No, Outlet_Name, Outlet_TIN, Business_Style, DS_Section, Category, Branch, Province, City, Barangay, Address, Route, Contact_Person, Contact_No, Terms) VALUES (:ono, :oname, :tin, :bstyle, :ds, :cat, :branch, :prov, :city, :brgy, :addr, :rte, :cperson, :cno, :terms)");
            $stmt->execute([':ono'=>$next_seq, ':oname'=>$data->customer_name, ':tin'=>$data->tin, ':bstyle'=>$data->bstyle, ':ds'=>$data->ds_section, ':cat'=>$data->category, ':branch'=>$data->branch, ':prov'=>$data->province, ':city'=>$data->city, ':brgy'=>$data->barangay, ':addr'=>$data->address, ':rte'=>$data->route, ':cperson'=>$data->contact_person, ':cno'=>$data->contact_no, ':terms'=>$data->terms]);
        }
        echo json_encode(['status' => 'success']);
    }
}
elseif ($method === 'DELETE') {
    if ($table === 'dealers' && $id) { 
        // FIXED: Security check to prevent deleting a dealer with active transactions
        $check = $db->prepare("SELECT COUNT(*) FROM yl_stock_orders WHERE Dealer_ID = :id");
        $check->execute([':id' => $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete Dealer. They already have active Stock Orders in the system.']);
            exit;
        }
        
        $stmt = $db->prepare("DELETE FROM independent_dealers WHERE Dealer_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
    }
    elseif ($table === 'outlets' && $id) { 
        // FIXED: Security check to prevent deleting an outlet with active transactions
        $check = $db->prepare("SELECT COUNT(*) FROM ds_sales_orders WHERE Outlet_ID = :id");
        $check->execute([':id' => $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete Outlet. They already have active Sales Orders in the system.']);
            exit;
        }
        
        $stmt = $db->prepare("DELETE FROM outlets WHERE Outlet_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
    }
}
?>