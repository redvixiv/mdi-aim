<?php
session_start();
require_once 'api/db.php';

// SMART ROUTING: If already logged in, redirect to the correct portal based on role
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Field Agent') {
        header("Location: mobile.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$database = new Database();
$db = $database->getConnection();
$error = '';
$logo_path = '';

// --- AUTO-SETUP: Create table and default user if it doesn't exist ---
try {
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        User_ID INT(11) NOT NULL AUTO_INCREMENT,
        Username VARCHAR(50) NOT NULL UNIQUE,
        Password VARCHAR(255) NOT NULL,
        Role VARCHAR(50) DEFAULT 'Admin',
        Permissions_JSON TEXT NULL,
        Agent_Type VARCHAR(50) NULL,
        Linked_Entity VARCHAR(100) NULL,
        PRIMARY KEY (User_ID)
    )");
    
    // Check if any users exist; if not, create default admin
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $default_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO users (Username, Password, Role) VALUES ('admin', '$default_hash', 'Admin')");
    }
    
    // Fetch the company logo from the database for branding
    $comp_stmt = $db->query("SELECT Logo_Path FROM company_profile WHERE Profile_ID = 1");
    if ($comp_stmt) {
        $comp = $comp_stmt->fetch(PDO::FETCH_ASSOC);
        if ($comp && !empty($comp['Logo_Path'])) {
            $logo_path = $comp['Logo_Path'];
        }
    }
} catch (PDOException $e) {
    $error = "Setup Error: " . $e->getMessage();
}

// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $db->prepare("SELECT * FROM users WHERE Username = :user");
        $stmt->execute([':user' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password hash
        if ($user && password_verify($password, $user['Password'])) {
            
            // SECURED: Regenerate session ID to prevent Session Fixation attacks
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['Role'];
            
            // Decode granular permissions and save to session
            $_SESSION['permissions'] = json_decode($user['Permissions_JSON'] ?? '{}', true);
            
            // SMART ROUTING: Direct to mobile app if Field Agent
            if ($user['Role'] === 'Field Agent') {
                header("Location: mobile.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please enter both username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MDI AIMS - Secure Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Clear the saved module state whenever the login screen is loaded -->
    <script>
        localStorage.removeItem('mdi_active_module');
    </script>
    <style>
        :root {
             --theme-green: #2e7d32;
             --theme-green-dark: #1b5e20;
             --theme-red: #d32f2f;
             --theme-dark: #212529;
             --odoo-bg: #f4f6f8;
        }
        
        body {
             background-color: var(--odoo-bg);
             display: flex; align-items: center; justify-content: center;
             height: 100vh; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
             color: var(--theme-dark);
        }
        
        .login-card {
             background: white; padding: 50px 40px; border-radius: 16px;
             box-shadow: 0 10px 30px rgba(0,0,0,0.08); width: 100%; max-width: 420px;
             border-top: 5px solid var(--theme-green);
        }
        
        .login-icon-wrapper {
             width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 22px;
             display: flex; align-items: center; justify-content: center;
             font-size: 2.5rem; color: white;
             background: linear-gradient(135deg, var(--theme-green) 0%, var(--theme-green-dark) 100%);
             box-shadow: 0 8px 15px rgba(46, 125, 50, 0.25);
        }
        .company-logo {
            max-height: 90px;
            max-width: 100%;
            margin: 0 auto 20px;
            display: block;
            object-fit: contain;
        }
        
        .theme-primary { color: var(--theme-green-dark); }
        
        .btn-primary {
             background-color: var(--theme-green); border-color: var(--theme-green);
             font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 12px;
             box-shadow: 0 4px 6px rgba(46, 125, 50, 0.2); transition: all 0.2s;
        }
        .btn-primary:hover {
             background-color: var(--theme-green-dark); border-color: var(--theme-green-dark);
             transform: translateY(-2px); box-shadow: 0 6px 12px rgba(46, 125, 50, 0.3);
        }
        
        .form-control { border-radius: 8px; padding: 10px 15px; border: 1px solid #ced4da; }
        .input-group-text { border-radius: 8px 0 0 8px; border: 1px solid #ced4da; background-color: #f8f9fa; }
        .form-control:focus { border-color: var(--theme-green); box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.15); }
        
        .alert-danger {
            background-color: #ffebee; border-left: 4px solid var(--theme-red);
             color: var(--theme-red-dark); border-radius: 4px; border-top: 0; border-right: 0; border-bottom: 0;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <?php if ($logo_path): ?>
            <img src="<?= htmlspecialchars($logo_path) ?>" alt="Company Logo" class="company-logo">
        <?php else: ?>
            <div class="login-icon-wrapper">
                <i class="bi bi-building"></i>
            </div>
        <?php endif; ?>
        
        <h3 class="fw-bolder mt-2 theme-primary text-uppercase" style="letter-spacing: 1.5px;">MDI</h3>
        <p class="text-muted small text-uppercase fw-bold mt-1">ACCOUNTING AND INVENTORY MANAGEMENT SYSTEM (AIMS)</p>
    </div>
    <?php if ($error): ?>
        <div class="alert alert-danger small fw-bold text-center py-2 mb-4"><i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div class="mb-3">
            <label class="form-label text-muted small fw-bolder text-uppercase mb-1">Username</label>
            <div class="input-group shadow-sm">
                <span class="input-group-text"><i class="bi bi-person-fill text-muted"></i></span>
                <input type="text" name="username" class="form-control" placeholder="Enter your username" required autofocus>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label text-muted small fw-bolder text-uppercase mb-1">Password</label>
            <div class="input-group shadow-sm">
                <span class="input-group-text"><i class="bi bi-lock-fill text-muted"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 rounded-3 mt-2">Secure Login <i class="bi bi-box-arrow-in-right ms-2"></i></button>
    </form>
    
    <div class="text-center mt-5 pt-3 border-top">
        <small class="text-muted"><i class="bi bi-shield-lock-fill me-1 text-success"></i> Secure Enterprise Access</small>
    </div>
</div>
</body>
</html>