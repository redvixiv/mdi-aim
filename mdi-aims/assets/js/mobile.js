document.addEventListener("DOMContentLoaded", () => {
    let cart = [];
    let productsList = [];
    let html5QrcodeScanner = null;

    // Check Network Status Live
    const updateNetworkStatus = () => {
        const badge = document.getElementById('connection-status');
        if (navigator.onLine) {
            badge.innerText = 'Online (Office)';
            badge.className = 'badge bg-success text-white';
            checkOfflineOrders();
        } else {
            badge.innerText = 'Offline (Field)';
            badge.className = 'badge bg-danger text-white';
        }
    };
    window.addEventListener('online', updateNetworkStatus);
    window.addEventListener('offline', updateNetworkStatus);
    updateNetworkStatus();

    // Load Master Data (Works online, falls back to memory if offline)
    loadMasterData();

    function loadMasterData() {
        if (navigator.onLine) {
            // Fetch Products
            fetch('api/endpoints.php?table=product_pricing')
            .then(r => r.json())
            .then(data => {
                if (data.status === 'error' || data.error) {
                    return alert("Security Block: Please log into the main system first to download app data!");
                }
                if (Array.isArray(data)) {
                    const unique = Array.from(new Map(data.map(item => [item['Product_ID'], item])).values());
                    localStorage.setItem('mobile_products', JSON.stringify(unique));
                    productsList = unique;
                    populateProductDropdown();
                }
            }).catch(e => console.error("Product Fetch Error:", e));

            // Fetch DS Customers
            fetch('api/endpoints.php?table=outlets')
            .then(r => r.json())
            .then(data => {
                if (Array.isArray(data) && !data.error) {
                    localStorage.setItem('mobile_ds_customers', JSON.stringify(data));
                    if (document.getElementById('mobile_order_type').value === 'DS') populateClientDropdown('DS');
                }
            });

            // Fetch YL Dealers
            fetch('api/endpoints.php?table=dealers')
            .then(r => r.json())
            .then(data => {
                if (Array.isArray(data) && !data.error) {
                    localStorage.setItem('mobile_yl_dealers', JSON.stringify(data));
                    if (document.getElementById('mobile_order_type').value === 'YL') populateClientDropdown('YL');
                }
            });
        } else {
            // Load from Phone Memory when Offline
            productsList = JSON.parse(localStorage.getItem('mobile_products') || '[]');
            populateProductDropdown();
            populateClientDropdown(document.getElementById('mobile_order_type').value);
        }
    }

    document.getElementById('mobile_order_type').addEventListener('change', function() {
        populateClientDropdown(this.value);
        renderCart(); // Recalculate prices (Wholesale for DS, Retail for YL)
    });

    function populateClientDropdown(type) {
        const sel = document.getElementById('mobile_customer_id');
        sel.innerHTML = '<option value="">Choose Client...</option>';
        
        if (type === 'DS') {
            const custs = JSON.parse(localStorage.getItem('mobile_ds_customers') || '[]');
            if (Array.isArray(custs)) {
                custs.forEach(c => { sel.innerHTML += `<option value="${c.Outlet_ID}">${c.Outlet_Name}</option>`; });
            }
        } else {
            const dealers = JSON.parse(localStorage.getItem('mobile_yl_dealers') || '[]');
            if (Array.isArray(dealers)) {
                dealers.forEach(d => { sel.innerHTML += `<option value="${d.Dealer_ID}">${d.First_Name} ${d.Last_Name}</option>`; });
            }
        }
    }

    function populateProductDropdown() {
        const sel = document.getElementById('manual_product_select');
        sel.innerHTML = '<option value="">Select a Product...</option>';
        if (Array.isArray(productsList)) {
            productsList.forEach(p => { 
                sel.innerHTML += `<option value="${p.Product_ID}" data-barcode="${p.Barcode || ''}">[${p.Product_No}] ${p.Product_Name}</option>`; 
            });
        }
    }

    // --- MANUAL ADD TO CART ---
    document.getElementById('btnConfirmManualAdd').addEventListener('click', () => {
        const pId = document.getElementById('manual_product_select').value;
        const qty = parseInt(document.getElementById('manual_qty').value);
        if(!pId || qty < 1) return alert("Select product and valid quantity.");

        const prod = productsList.find(p => p.Product_ID == pId);
        addToCart(prod, qty);
        
        document.getElementById('manual_qty').value = 1;
        document.getElementById('manual_product_select').value = '';
        bootstrap.Modal.getInstance(document.getElementById('manualAddModal')).hide();
    });

    // --- BARCODE SCANNER LOGIC WITH ERROR HANDLING ---
    document.getElementById('btnStartScan').addEventListener('click', () => {
        const readerDiv = document.getElementById('reader');
        const btn = document.getElementById('btnStartScan');

        // IF CAMERA IS OPEN, STOP IT
        if (readerDiv.style.display === 'block') {
            if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
                html5QrcodeScanner.stop().then(() => {
                    readerDiv.style.display = 'none';
                    btn.innerHTML = '<i class="bi bi-upc-scan me-2"></i>Scan Barcode';
                }).catch(err => console.error("Failed to stop scanner", err));
            } else {
                readerDiv.style.display = 'none';
                btn.innerHTML = '<i class="bi bi-upc-scan me-2"></i>Scan Barcode';
            }
            return;
        }

        // OPEN CAMERA
        readerDiv.style.display = 'block';
        btn.innerHTML = '<i class="bi bi-stop-circle me-2"></i>Starting...';

        html5QrcodeScanner = new Html5Qrcode("reader");
        html5QrcodeScanner.start(
            { facingMode: "environment" }, 
            { fps: 10, qrbox: { width: 250, height: 150 } },
            (decodedText, decodedResult) => {
                // Successful Scan
                html5QrcodeScanner.pause(true); 
                
                const prod = productsList.find(p => p.Barcode === decodedText || p.Product_No === decodedText);
                if (prod) {
                    addToCart(prod, 1);
                    alert("Added: " + prod.Product_Name);
                } else {
                    alert("Barcode not found in database: " + decodedText);
                }
                
                setTimeout(() => html5QrcodeScanner.resume(), 1500);
            },
            (errorMessage) => { /* Ignore background scanning errors */ }
        ).then(() => {
            btn.innerHTML = '<i class="bi bi-stop-circle me-2"></i>Stop Camera';
        }).catch((err) => {
            console.error("Camera Error:", err);
            alert("Camera Blocked! If testing on Local Wi-Fi, you must enable HTTP camera access in Chrome Flags.");
            readerDiv.style.display = 'none';
            btn.innerHTML = '<i class="bi bi-upc-scan me-2"></i>Scan Barcode';
        });
    });

    // --- CART MANAGEMENT ---
    function addToCart(prod, qty) {
        const existing = cart.find(i => i.Product_ID == prod.Product_ID);
        if (existing) { existing.quantity += qty; } 
        else { cart.push({ ...prod, quantity: qty }); }
        renderCart();
    }

    window.removeFromCart = function(index) {
        cart.splice(index, 1);
        renderCart();
    }

    window.updateQty = function(index, newQty) {
        if(newQty < 1) return;
        cart[index].quantity = parseInt(newQty);
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('mobileCartContainer');
        const orderType = document.getElementById('mobile_order_type').value;
        let totalAmt = 0;
        
        container.innerHTML = '';
        if (cart.length === 0) {
            container.innerHTML = '<div class="text-center py-4 text-muted small">Cart is empty. Scan or add items.</div>';
            document.getElementById('mobile_total_amount').innerText = '₱0.00';
            return;
        }

        cart.forEach((item, index) => {
            const price = parseFloat(orderType === 'DS' ? item.Wholesale : item.Retail) || 0;
            const sub = price * item.quantity;
            totalAmt += sub;

            container.innerHTML += `
                <div class="cart-item d-flex justify-content-between align-items-center">
                    <div style="width: 50%;">
                        <h6 class="m-0 text-dark fw-bold text-truncate">${item.Product_Name}</h6>
                        <small class="text-muted">₱${price.toFixed(2)}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="number" class="form-control text-center fw-bold text-primary p-1" style="width:60px;" value="${item.quantity}" onchange="updateQty(${index}, this.value)">
                        <button class="btn btn-sm btn-outline-danger border-0" onclick="removeFromCart(${index})"><i class="bi bi-trash-fill fs-5"></i></button>
                    </div>
                </div>
            `;
        });
        document.getElementById('mobile_total_amount').innerText = `₱${totalAmt.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    }

    // --- SAVE OFFLINE ORDER ---
    document.getElementById('btnSaveOffline').addEventListener('click', () => {
        const clientId = document.getElementById('mobile_customer_id').value;
        const orderType = document.getElementById('mobile_order_type').value;
        if (!clientId) return alert("Please select a Customer / Dealer.");
        if (cart.length === 0) return alert("Cart is empty!");

        let totalQty = 0; let totalAmt = 0;
        const items = cart.map(i => {
            const p = parseFloat(orderType === 'DS' ? i.Wholesale : i.Retail) || 0;
            totalQty += i.quantity;
            totalAmt += (i.quantity * p);
            return { product_id: i.Product_ID, product_name: i.Product_Name, quantity: i.quantity, unit_price: p };
        });

        const order = {
            id: Date.now(), // Offline unique ID
            type: orderType,
            client_id: clientId,
            date: new Date().toISOString().split('T')[0],
            items: items,
            total_qty: totalQty,
            total_amount: totalAmt
        };

        const offlineOrders = JSON.parse(localStorage.getItem('mdi_offline_orders') || '[]');
        offlineOrders.push(order);
        localStorage.setItem('mdi_offline_orders', JSON.stringify(offlineOrders));

        alert("Order Saved Locally! Ready for next transaction.");
        
        cart = [];
        document.getElementById('mobile_customer_id').value = '';
        renderCart();
        checkOfflineOrders();
    });

    // --- SYNC TO SERVER ---
    function checkOfflineOrders() {
        const offlineOrders = JSON.parse(localStorage.getItem('mdi_offline_orders') || '[]');
        const syncBtn = document.getElementById('btnSync');
        const countSpan = document.getElementById('offlineCount');
        
        if (offlineOrders.length > 0 && navigator.onLine) {
            syncBtn.style.display = 'block';
            countSpan.innerText = offlineOrders.length;
        } else {
            syncBtn.style.display = 'none';
        }
    }

    document.getElementById('btnSync').addEventListener('click', async () => {
        if (!navigator.onLine) return alert("Cannot sync. You are offline!");
        
        const offlineOrders = JSON.parse(localStorage.getItem('mdi_offline_orders') || '[]');
        if (offlineOrders.length === 0) return;

        document.getElementById('btnSync').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Syncing...';
        document.getElementById('btnSync').disabled = true;

        let successfulSyncs = 0;

        for (let order of offlineOrders) {
            try {
                let table = order.type === 'DS' ? 'ds_sales_orders' : 'yl_stock_orders';
                let payload = order.type === 'DS' ? 
                    { ds_type: 'DS', outlet_id: order.client_id, so_date: order.date, items: order.items, total_qty: order.total_qty, total_amount: order.total_amount } :
                    { dealer_id: order.client_id, so_date: order.date, items: order.items, total_qty: order.total_qty, total_amount: order.total_amount };

                const response = await fetch(`api/endpoints.php?table=${table}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                if (result.status === 'success') {
                    successfulSyncs++;
                }
            } catch (err) {
                console.error("Sync error for order:", order, err);
            }
        }

        if (successfulSyncs === offlineOrders.length) {
            localStorage.setItem('mdi_offline_orders', '[]');
            alert("All offline orders successfully synced to the main server!");
        } else {
            alert(`Synced ${successfulSyncs} out of ${offlineOrders.length} orders. Please try again.`);
            const remaining = offlineOrders.slice(successfulSyncs);
            localStorage.setItem('mdi_offline_orders', JSON.stringify(remaining));
        }

        document.getElementById('btnSync').innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-1"></i>Sync (<span id="offlineCount">0</span>)';
        document.getElementById('btnSync').disabled = false;
        checkOfflineOrders();
    });
});