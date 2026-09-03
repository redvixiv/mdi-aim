<?php
if (!isset($db)) { exit; }

if ($method === 'GET') {
    if ($table === 'products') {
        $stmt = $db->prepare("SELECT * FROM products ORDER BY Product_ID DESC"); 
        $stmt->execute(); 
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'product_pricing') {
        $stmt = $db->prepare("SELECT pp.*, p.Product_Name, p.Product_No FROM product_pricing pp JOIN products p ON pp.Product_ID = p.Product_ID ORDER BY pp.Pricing_ID DESC"); 
        $stmt->execute(); 
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($table === 'active_price') {
        $pid = $_GET['product_id']; 
        $date = !empty($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        $stmt = $db->prepare("SELECT * FROM product_pricing WHERE Product_ID = :pid AND Effective_From <= :date AND (Effective_To IS NULL OR Effective_To = '0000-00-00' OR Effective_To = '' OR Effective_To >= :date) ORDER BY Effective_From DESC LIMIT 1");
        $stmt->execute([':pid' => $pid, ':date' => $date]); 
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: null); 
    }
} 
elseif ($method === 'POST') {
    if ($table === 'products') {
        // FIXED: Added UPDATE logic alongside INSERT
        if (!empty($data->product_id)) {
            $stmt = $db->prepare("UPDATE products SET Product_Name=:name, Category=:cat, Description=:desc WHERE Product_ID=:id");
            $stmt->execute([':name'=>$data->name, ':cat'=>$data->category, ':desc'=>$data->description, ':id'=>$data->product_id]);
            logAudit($db, 'UPDATE RECORD', "Updated Product ID: {$data->product_id}");
        } else {
            $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(Product_No, 3) AS UNSIGNED)) AS max_no FROM products"); 
            $row = $stmt->fetch(PDO::FETCH_ASSOC); 
            $product_no = "P-" . ($row['max_no'] ? $row['max_no'] + 1 : 10001);
            
            $stmt = $db->prepare("INSERT INTO products (Product_No, Product_Name, Category, Description) VALUES (:pno, :name, :cat, :desc)");
            $stmt->execute([':pno'=>$product_no, ':name'=>$data->name, ':cat'=>$data->category, ':desc'=>$data->description]); 
            logAudit($db, 'CREATE RECORD', "Created Product: {$data->name}");
        }
        echo json_encode(['status' => 'success']);
    }
    elseif ($table === 'product_pricing') {
        $effective_to = !empty($data->effective_to) ? $data->effective_to : null;

        if (!empty($data->pricing_id)) {
            $stmt = $db->prepare("UPDATE product_pricing SET Product_ID = :pid, Unit_Cost = :cost, Wholesale = :ws, Retail = :rt, ODL = :odl, Effective_From = :efrom, Effective_To = :eto WHERE Pricing_ID = :id");
            $stmt->execute([':pid'=>$data->product_id, ':cost'=>$data->unit_cost, ':ws'=>$data->wholesale, ':rt'=>$data->retail, ':odl'=>$data->odl, ':efrom'=>$data->effective_from, ':eto'=>$effective_to, ':id'=>$data->pricing_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO product_pricing (Product_ID, Unit_Cost, Wholesale, Retail, ODL, Effective_From, Effective_To) VALUES (:pid, :cost, :ws, :rt, :odl, :efrom, :eto)");
            $stmt->execute([':pid'=>$data->product_id, ':cost'=>$data->unit_cost, ':ws'=>$data->wholesale, ':rt'=>$data->retail, ':odl'=>$data->odl, ':efrom'=>$data->effective_from, ':eto'=>$effective_to]);
        }
        echo json_encode(['status' => 'success']);
    }
}
elseif ($method === 'DELETE') {
    if ($table === 'products' && $id) { 
        $check = $db->prepare("SELECT COUNT(*) FROM inventory_ledger WHERE Product_ID = :id");
        $check->execute([':id' => $id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete product because it has active inventory transactions.']);
            exit;
        }
        $db->prepare("DELETE FROM product_pricing WHERE Product_ID = :id")->execute([':id' => $id]);
        $stmt = $db->prepare("DELETE FROM products WHERE Product_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
    }
    elseif ($table === 'product_pricing' && $id) { 
        $stmt = $db->prepare("DELETE FROM product_pricing WHERE Pricing_ID = :id"); 
        $stmt->execute([':id' => $id]); 
        echo json_encode(['status' => 'success']); 
    }
}
?>