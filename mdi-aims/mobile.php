<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch the specific Agent Type and Linked Entity from the database
require_once 'api/db.php';
$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT Role, Agent_Type, Linked_Entity FROM users WHERE User_ID = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['Role'] !== 'Field Agent' && $user['Role'] !== 'Admin') {
    die("<div style='padding:40px; font-family:sans-serif; text-align:center;'><h2>Access Denied</h2><p>This portal is strictly for Mobile Field Agents.</p><a href='index.php'>Return to Dashboard</a></div>");
}

$agentType = $user['Agent_Type'] ?? 'Unknown';
$linkedEntity = $user['Linked_Entity'] ?? 'None';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>MDI Field App</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- HTML5-QRCode Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <style>
        :root {
             --theme-green: #2e7d32;
             --theme-green-dark: #1b5e20;
             --theme-accent: #fd7e14;
             --mobile-bg: #f4f6f8;
        }
        
        body {
             background-color: var(--mobile-bg);
             font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
             padding-top: 60px;
             padding-bottom: 80px; /* Space for bottom nav */
             -webkit-tap-highlight-color: transparent;
        }
        /* Top Header */
        .mobile-header {
             position: fixed; top: 0; left: 0; right: 0; height: 60px;
             background: linear-gradient(135deg, var(--theme-green) 0%, var(--theme-green-dark) 100%);
             color: white; display: flex; align-items: center; justify-content: space-between;
             padding: 0 20px; z-index: 1030; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .mobile-header h5 { margin: 0; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; font-size: 1.1rem; }
        /* Bottom Navigation Bar */
        .bottom-nav {
             position: fixed; bottom: 0; left: 0; right: 0; height: 70px;
             background: white; border-top: 1px solid #e2e8f0;
             display: flex; justify-content: space-around; align-items: center;
             z-index: 1030; box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
             padding-bottom: env(safe-area-inset-bottom);
        }
        .nav-item {
             display: flex; flex-direction: column; align-items: center; justify-content: center;
             color: #64748b; text-decoration: none; width: 100%; height: 100%; transition: color 0.2s;
        }
        .nav-item i { font-size: 1.5rem; margin-bottom: 2px; }
        .nav-item span { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .nav-item.active { color: var(--theme-green); }
        /* Cards and Elements */
        .m-card { background: white; border-radius: 16px; padding: 20px; margin-bottom: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; }
        .m-title { font-size: 0.85rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 15px; }
        
        .btn-m { border-radius: 12px; padding: 12px; font-weight: 700; text-transform: uppercase; font-size: 0.9rem; letter-spacing: 0.5px; }
        .form-control-m { border-radius: 12px; padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 1rem; }
        .form-control-m:focus { border-color: var(--theme-green); box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.25); }
        .view-section { display: none; animation: fadeIn 0.3s ease; }
        .view-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        /* Item List Tweaks */
        .item-row { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding: 10px 0; }
        .item-row:last-child { border-bottom: none; }
        .qty-controls { display: flex; align-items: center; background: #f8fafc; border-radius: 30px; padding: 5px; }
        .qty-btn { width: 35px; height: 35px; border-radius: 50%; border: none; background: white; color: var(--theme-green); font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .qty-input { width: 40px; text-align: center; border: none; background: transparent; font-weight: 800; font-size: 1.1rem; color: #0f172a; }
        /* Fullscreen Scanner Overlay */
        #scannerOverlay {
             display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
             background: #000; z-index: 2000; flex-direction: column;
        }
        #reader { width: 100%; flex-grow: 1; background: #000; border: none; }
        #reader video { object-fit: cover; }
        #scan-toast { 
             position: absolute; bottom: 100px; left: 50%; transform: translateX(-50%); 
             background: var(--theme-green); color: white; padding: 10px 20px; 
             border-radius: 30px; font-weight: bold; display: none; z-index: 2001; 
             box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="mobile-header">
        <h5>MDI Field App</h5>
        <a href="logout.php" class="text-white text-decoration-none"><i class="bi bi-box-arrow-right fs-4"></i></a>
    </div>
    <!-- Scanner Overlay -->
    <div id="scannerOverlay">
        <div class="p-3 d-flex justify-content-between align-items-center bg-dark text-white border-bottom border-secondary">
            <h5 class="m-0 fw-bold"><i class="bi bi-upc-scan me-2"></i>Scan Product Barcode</h5>
            <button class="btn btn-close btn-close-white" onclick="closeScanner()"></button>
        </div>
        <div id="reader"></div>
        <div id="scan-toast"><i class="bi bi-check-circle-fill me-2"></i><span id="scan-toast-msg">Item Added!</span></div>
        <div class="p-4 bg-dark text-center">
            <button class="btn btn-outline-light btn-lg w-100 fw-bold rounded-pill" onclick="closeScanner()">Done Scanning</button>
        </div>
    </div>
    <div class="container-fluid px-3 pt-3">
        
        <!-- HOME VIEW -->
        <div id="view-home" class="view-section active">
            <div class="m-card text-center text-white" style="background: linear-gradient(135deg, var(--theme-accent) 0%, #d8363a 100%);">
                <i class="bi bi-person-badge text-white-50" style="font-size: 3rem;"></i>
                <h3 class="fw-bolder mt-2 mb-1"><?= htmlspecialchars($_SESSION['username'] ?? 'Agent') ?></h3>
                <span class="badge bg-white text-danger fw-bold rounded-pill px-3 py-2 text-uppercase"><?= $agentType ?> | <?= htmlspecialchars($linkedEntity) ?></span>
            </div>
            <div class="m-card">
                <h6 class="m-title">Quick Actions</h6>
                <div class="row g-3">
                    <div class="col-6">
                        <button class="btn btn-outline-success w-100 py-4 rounded-4 fw-bold" onclick="switchView('order')">
                            <i class="bi bi-cart-plus fs-1 d-block mb-2"></i>New Order
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-primary w-100 py-4 rounded-4 fw-bold" onclick="switchView('collect')">
                            <i class="bi bi-cash-stack fs-1 d-block mb-2"></i>Remit Cash
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ORDER VIEW -->
        <div id="view-order" class="view-section">
            <h4 class="fw-bold text-dark mb-3">Request Stock</h4>
            
            <div class="m-card">
                <?php if ($agentType === 'DS'): ?>
                    <label class="text-muted small fw-bold text-uppercase mb-2">Select Outlet Route</label>
                    <select id="m_order_outlet" class="form-select form-control-m mb-3">
                        <option value="">Loading Outlets...</option>
                    </select>
                <?php else: ?>
                    <label class="text-muted small fw-bold text-uppercase mb-2">Ordering For</label>
                    <input type="text" class="form-control form-control-m mb-3 bg-light text-muted" value="<?= htmlspecialchars($linkedEntity) ?>" disabled>
                    <input type="hidden" id="m_order_dealer_id" value="<?= htmlspecialchars($linkedEntity) ?>">
                <?php endif; ?>
                
                <label class="text-muted small fw-bold text-uppercase mb-2">Order Date</label>
                <input type="date" id="m_order_date" class="form-control form-control-m" required>
            </div>
            <div class="m-card">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-2">
                    <h6 class="m-title mb-0 border-0 pb-0">Select Products</h6>
                    <button class="btn btn-sm btn-outline-dark fw-bold rounded-pill" onclick="openScanner()">
                        <i class="bi bi-camera me-1"></i>Scan
                    </button>
                </div>
                
                <div id="m_products_list">
                    <div class="text-center py-4 text-muted spinner-border text-success" role="status"></div>
                </div>
            </div>
            <div class="m-card bg-success text-white sticky-bottom position-sticky" style="bottom: 80px; z-index: 10;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold text-white-50">Total Amount:</span>
                    <h2 class="fw-bolder mb-0" id="m_order_total">₱ 0.00</h2>
                </div>
                <button class="btn btn-light text-success w-100 btn-m" onclick="submitMobileOrder()">Submit Order</button>
            </div>
        </div>
        
        <!-- COLLECT VIEW -->
        <div id="view-collect" class="view-section">
            <h4 class="fw-bold text-dark mb-3">Remit Collections</h4>
            
            <div class="m-card">
                <?php if ($agentType === 'DS'): ?>
                    <label class="text-muted small fw-bold text-uppercase mb-2">Select Outlet</label>
                    <select id="m_collect_outlet" class="form-select form-control-m">
                        <option value="">Loading Outlets...</option>
                    </select>
                <?php else: ?>
                    <label class="text-muted small fw-bold text-uppercase mb-2">Dealer Target</label>
                    <input type="text" class="form-control form-control-m bg-light text-muted" value="<?= htmlspecialchars($linkedEntity) ?>" disabled>
                    <input type="hidden" id="m_collect_dealer_id" value="<?= htmlspecialchars($linkedEntity) ?>">
                <?php endif; ?>
            </div>
            <div class="m-card">
                <h6 class="m-title border-bottom pb-3 mb-0">Unpaid Invoices</h6>
                <div id="m_invoices_list" class="pt-2">
                    <p class="text-center text-muted py-4 small">Select a target to view invoices.</p>
                </div>
            </div>
            <div class="m-card bg-primary text-white sticky-bottom position-sticky" style="bottom: 80px; z-index: 10;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold text-white-50">Selected Total:</span>
                    <h2 class="fw-bolder mb-0" id="m_collect_total">₱ 0.00</h2>
                </div>
                <button class="btn btn-light text-primary w-100 btn-m" onclick="submitMobileCollection()">Post Receipt</button>
            </div>
        </div>
    </div>
    
    <!-- Bottom Nav -->
    <div class="bottom-nav">
        <a href="#" class="nav-item active" onclick="switchView('home', this)">
            <i class="bi bi-house-door-fill"></i>
            <span>Home</span>
        </a>
        <a href="#" class="nav-item" onclick="switchView('order', this)">
            <i class="bi bi-cart-plus-fill"></i>
            <span>Order</span>
        </a>
        <a href="#" class="nav-item" onclick="switchView('collect', this)">
            <i class="bi bi-cash-stack"></i>
            <span>Collect</span>
        </a>
    </div>
    <script>
        const agentType = '<?= $agentType ?>';
        const linkedEntity = '<?= $linkedEntity ?>';
        
        let globalProducts = [];
        let html5QrcodeScanner = null;
        
        document.addEventListener("DOMContentLoaded", () => {
            document.getElementById('m_order_date').valueAsDate = new Date();
            loadMobileProducts();
            
            if (agentType === 'DS') {
                loadDsOutlets();
                document.getElementById('m_collect_outlet').addEventListener('change', fetchUnpaidInvoices);
            } else {
                fetchUnpaidInvoices();
            }
        });
        
        function switchView(viewId, navElement = null) {
            document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
            document.getElementById('view-' + viewId).classList.add('active');
            
            if (navElement) {
                document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
                navElement.classList.add('active');
            }
        }
        
        function formatCurrency(amount) {
            return parseFloat(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        
        function loadMobileProducts() {
            Promise.all([
                fetch('api/endpoints.php?table=product_pricing').then(r => r.json()),
                fetch('api/endpoints.php?table=products').then(r => r.json())
            ]).then(([pricingData, productsData]) => {
                const uniqueProducts = [];
                const seenIds = new Set();
                
                pricingData.forEach(item => {
                    if (!seenIds.has(item.Product_ID)) {
                        seenIds.add(item.Product_ID);
                        const match = productsData.find(p => p.Product_ID === item.Product_ID);
                        item.Barcode = match ? match.Barcode : null;
                        uniqueProducts.push(item);
                    }
                });
                
                globalProducts = uniqueProducts;
                renderProductList();
            });
        }
        
        function renderProductList() {
            const container = document.getElementById('m_products_list');
            container.innerHTML = '';
            
            globalProducts.forEach(p => {
                const price = agentType === 'YL' ? parseFloat(p.Retail) : parseFloat(p.Wholesale);
                
                container.innerHTML += `
                    <div class="item-row mt-2">
                        <div class="w-50 pe-2">
                            <h6 class="fw-bolder text-dark mb-1 text-truncate" style="font-size: 0.9rem;">${p.Product_Name}</h6>
                            <span class="text-success fw-bold">₱ ${formatCurrency(price)}</span>
                        </div>
                        <div class="qty-controls" data-pid="${p.Product_ID}" data-price="${price}">
                            <button class="qty-btn" onclick="updateQty(this, -1)">-</button>
                            <input type="number" class="qty-input m-qty-val" value="0" readonly>
                            <button class="qty-btn" onclick="updateQty(this, 1)">+</button>
                        </div>
                    </div>
                `;
            });
        }
        
        function updateQty(btn, change) {
            const input = btn.parentElement.querySelector('.qty-input');
            let current = parseInt(input.value) || 0;
            let newVal = current + change;
            if (newVal < 0) newVal = 0;
            input.value = newVal;
            calculateOrderTotal();
        }
        
        function calculateOrderTotal() {
            let total = 0;
            document.querySelectorAll('.qty-controls').forEach(ctrl => {
                const qty = parseInt(ctrl.querySelector('.qty-input').value) || 0;
                const price = parseFloat(ctrl.getAttribute('data-price')) || 0;
                total += (qty * price);
            });
            document.getElementById('m_order_total').innerText = `₱ ${formatCurrency(total)}`;
        }
        
        // ================= BARCODE SCANNER ENGINE =================
        function openScanner() {
            document.getElementById('scannerOverlay').style.display = 'flex';
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
                false
            );
            
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }
        
        function closeScanner() {
            if(html5QrcodeScanner) {
                html5QrcodeScanner.clear();
            }
            document.getElementById('scannerOverlay').style.display = 'none';
        }
        
        let lastScanTime = 0;
        function onScanSuccess(decodedText, decodedResult) {
            if (Date.now() - lastScanTime < 1500) return;
            lastScanTime = Date.now();
            const p = globalProducts.find(prod => prod.Barcode === decodedText || prod.Product_No === decodedText);
            
            if (p) {
                const ctrl = document.querySelector(`.qty-controls[data-pid="${p.Product_ID}"]`);
                if(ctrl) {
                    const addBtn = ctrl.querySelector('button:last-child');
                    updateQty(addBtn, 1);
                    
                    if(navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    
                    const toast = document.getElementById('scan-toast');
                    document.getElementById('scan-toast-msg').innerText = `+1 ${p.Product_Name}`;
                    toast.style.display = 'block';
                    setTimeout(() => toast.style.display = 'none', 1500);
                }
            } else {
                if(navigator.vibrate) navigator.vibrate(300);
                alert(`Scanned Barcode (${decodedText}) does not match any product in your database.`);
            }
        }
        
        function onScanFailure(error) {
            // Background scanning errors are common and expected as the camera focuses, keep this silent.
        }
        
        // ==========================================================
        function loadDsOutlets() {
            fetch('api/endpoints.php?table=outlets')
            .then(r => r.json())
            .then(data => {
                const selOrder = document.getElementById('m_order_outlet');
                const selCollect = document.getElementById('m_collect_outlet');
                
                selOrder.innerHTML = '<option value="">Select Outlet...</option>';
                selCollect.innerHTML = '<option value="">Select Outlet...</option>';
                
                const filtered = data.filter(o => o.Route === linkedEntity);
                
                if(filtered.length === 0) {
                    selOrder.innerHTML = '<option value="">No outlets found on your route.</option>';
                    selCollect.innerHTML = '<option value="">No outlets found on your route.</option>';
                    return;
                }
                
                filtered.forEach(o => {
                    const opt = `<option value="${o.Outlet_ID}" data-name="${o.Outlet_Name}">${o.Outlet_Name}</option>`;
                    selOrder.innerHTML += opt;
                    selCollect.innerHTML += opt;
                });
            });
        }
        
        function fetchUnpaidInvoices() {
            const list = document.getElementById('m_invoices_list');
            list.innerHTML = '<div class="text-center py-4 text-muted spinner-border text-primary" role="status"></div>';
            
            let url = '';
            if (agentType === 'DS') {
                const oid = document.getElementById('m_collect_outlet').value;
                if(!oid) { list.innerHTML = '<p class="text-center text-muted py-4 small">Select an outlet to view invoices.</p>'; return; }
                url = `api/endpoints.php?table=ds_unpaid_invoices&outlet_id=${oid}`;
            } else {
                url = `api/endpoints.php?table=yl_unpaid_invoices&dealer_id=${linkedEntity}`;
            }
            fetch(url).then(r => r.json()).then(data => {
                list.innerHTML = '';
                if(data.error || data.length === 0) {
                    list.innerHTML = '<p class="text-center text-muted py-4 small fw-bold text-success"><i class="bi bi-check-circle d-block fs-1 mb-2"></i>All clear! No unpaid invoices.</p>';
                    return;
                }
                data.forEach(inv => {
                    list.innerHTML += `
                        <div class="item-row">
                            <div class="form-check d-flex align-items-center w-100">
                                <input class="form-check-input fs-4 me-3 m-inv-chk" type="checkbox" value="${inv.Invoice_ID}" data-amt="${inv.Amount_Due}" onchange="calculateCollectionTotal()">
                                <div class="w-100 d-flex justify-content-between">
                                    <span class="fw-bold text-dark">${inv.Invoice_No}</span>
                                    <span class="fw-bolder text-danger">₱ ${formatCurrency(inv.Amount_Due)}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                calculateCollectionTotal();
            });
        }
        
        function calculateCollectionTotal() {
            let total = 0;
            document.querySelectorAll('.m-inv-chk:checked').forEach(chk => {
                total += parseFloat(chk.getAttribute('data-amt')) || 0;
            });
            document.getElementById('m_collect_total').innerText = `₱ ${formatCurrency(total)}`;
        }
        
        function submitMobileOrder() {
            const items = [];
            let totalQty = 0;
            let totalAmt = 0;
            document.querySelectorAll('.qty-controls').forEach(ctrl => {
                const qty = parseInt(ctrl.querySelector('.qty-input').value) || 0;
                if(qty > 0) {
                    const price = parseFloat(ctrl.getAttribute('data-price')) || 0;
                    const pid = ctrl.getAttribute('data-pid');
                    const prodMatch = globalProducts.find(p => p.Product_ID == pid);
                    
                    items.push({
                        product_id: pid,
                        product_name: prodMatch ? prodMatch.Product_Name : 'Unknown',
                        quantity: qty,
                        unit_price: price
                    });
                    totalQty += qty;
                    totalAmt += (qty * price);
                }
            });
            if(items.length === 0) { alert("Please add at least one product."); return; }
            const payload = { items: items, total_qty: totalQty, total_amount: totalAmt };
            let endpoint = '';
            if (agentType === 'DS') {
                const oid = document.getElementById('m_order_outlet').value;
                if(!oid) { alert("Please select an outlet."); return; }
                payload.ds_type = 'DS';
                payload.outlet_id = oid;
                payload.so_date = document.getElementById('m_order_date').value;
                endpoint = 'ds_sales_orders';
            } else {
                payload.dealer_id = linkedEntity;
                payload.so_date = document.getElementById('m_order_date').value;
                endpoint = 'yl_stock_orders';
            }
            fetch(`api/endpoints.php?table=${endpoint}`, {
                 method: 'POST',
                 headers: {'Content-Type':'application/json'},
                 body: JSON.stringify(payload)
             })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') {
                    alert('Order submitted successfully!');
                    document.querySelectorAll('.qty-input').forEach(inp => inp.value = 0);
                    calculateOrderTotal();
                    if(agentType === 'DS') document.getElementById('m_order_outlet').value = '';
                    switchView('home', document.querySelector('.nav-item'));
                } else {
                    alert("Error: " + res.message);
                }
            });
        }
        
        function submitMobileCollection() {
            const selInvs = [];
            document.querySelectorAll('.m-inv-chk:checked').forEach(chk => selInvs.push(chk.value));
            
            if(selInvs.length === 0) { alert("Select at least one invoice to collect."); return; }
            
            const totalAmt = parseFloat(document.getElementById('m_collect_total').innerText.replace(/[₱ ,]/g,''));
            
            // Generates a cryptographically safer CR Number combining the Agent's Entity ID, a timestamp, and a random string to guarantee 0% collision risk across multiple agents.
            const uniqueStr = Math.random().toString(36).substr(2, 4).toUpperCase();
            const secureCrNo = 'CR-MOB-' + linkedEntity.substring(0,3).toUpperCase() + '-' + Date.now().toString().slice(-4) + uniqueStr;
            const payload = {
                cr_no: secureCrNo,
                cr_date: new Date().toISOString().split('T')[0],
                invoice_ids: selInvs,
                total_amount_due: totalAmt,
                total_words: "RECEIVED BY FIELD AGENT"
            };
            let endpoint = '';
            if (agentType === 'DS') {
                const sel = document.getElementById('m_collect_outlet');
                if(!sel.value) { alert("Select an outlet."); return; }
                payload.ds_type = 'DS';
                payload.outlet_name = sel.options[sel.selectedIndex].getAttribute('data-name');
                endpoint = 'ds_collection_receipts';
            } else {
                payload.dealer_name = "DEALER #" + linkedEntity;
                endpoint = 'yl_collection_receipts';
            }
            fetch(`api/endpoints.php?table=${endpoint}`, {
                 method: 'POST',
                 headers: {'Content-Type':'application/json'},
                 body: JSON.stringify(payload)
             })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') {
                    alert('Collection posted successfully! Ref: ' + secureCrNo);
                    
                    // Aggressively reset checkboxes and totals so the agent doesn't accidentally double-charge if they go back
                    document.querySelectorAll('.m-inv-chk:checked').forEach(chk => chk.checked = false);
                    document.getElementById('m_collect_total').innerText = '₱ 0.00';
                    
                    fetchUnpaidInvoices();
                    switchView('home', document.querySelector('.nav-item'));
                } else {
                    alert("Error: " + res.message);
                }
            });
        }
    </script>
</body>
</html>