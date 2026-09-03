// ==========================================
// MDI AIMS - YL ORDERS
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    if(document.getElementById('module-YL')) {
        window.loadYLOrders();
    }

    // EXPLICIT MODAL RESETS TO PREVENT GHOSTING
    document.getElementById('ylOrderModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('ylOrderForm')?.reset();
        document.getElementById('ylSoItemsTbody').innerHTML = '';
        document.getElementById('yl_so_total_qty').innerText = '0';
        document.getElementById('yl_so_total_amount').innerText = '₱ 0.00';
        document.getElementById('yl_area').value = '';
    });

    // MODAL OPEN LISTENERS TO UNLOCK BUTTONS
    document.getElementById('ylOrderModal')?.addEventListener('show.bs.modal', function () {
        document.getElementById('ylOrderForm').reset();
        document.getElementById('yl_so_date').valueAsDate = new Date();
        document.getElementById('ylSoItemsTbody').innerHTML = '';
        
        const btn = document.querySelector('#ylOrderForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save Order'; }
        
        if (!window.globalProductsList || window.globalProductsList.length === 0) {
            fetch('api/endpoints.php?table=product_pricing').then(r=>r.json()).then(data => {
                const uniqueProducts = []; const seenIds = new Set();
                data.forEach(item => { if (!seenIds.has(item.Product_ID)) { seenIds.add(item.Product_ID); uniqueProducts.push(item); } });
                window.globalProductsList = uniqueProducts;
                window.addYlSoItemRow();
            }).catch(() => window.addYlSoItemRow());
        } else {
            window.addYlSoItemRow();
        }

        const dealerSel = document.getElementById('yl_dealer_id');
        fetch(`api/endpoints.php?table=dealers`).then(r => r.json()).then(data => {
            window.globalDealersList = data; 
            dealerSel.innerHTML = `<option value="">Select Dealer...</option>`;
            data.forEach(d => { 
                dealerSel.innerHTML += `<option value="${d.Dealer_ID}" data-area="${d.Area || ''}">${d.First_Name} ${d.Last_Name}</option>`; 
            });
        });
    });

    // EVENT LISTENERS
    document.addEventListener('change', (e) => {
        if(e.target.id === 'yl_dealer_id') {
            const opt = e.target.options[e.target.selectedIndex];
            if(opt && opt.value) {
                document.getElementById('yl_area').value = opt.getAttribute('data-area') || '';
            } else {
                document.getElementById('yl_area').value = '';
            }
        }
    });

    document.body.addEventListener('click', (e) => {
        const btnAddSo = e.target.closest('#btnAddYlSoItem');
        if (btnAddSo) window.addYlSoItemRow();
    });

    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('yl-so-qty') || e.target.classList.contains('yl-so-product-select')) {
            window.calculateYlSOTotals();
        }
    });

    // STRICT ANTI-DOUBLE-SUBMISSION LOCK
    document.getElementById('ylOrderForm')?.addEventListener('submit', function(e) {
        e.preventDefault(); 
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const items = []; let tq = 0; let ta = 0;
        document.querySelectorAll('.yl-so-item-row').forEach(row => {
            const sel = row.querySelector('.yl-so-product-select');
            if(sel && sel.value) {
                const q = parseInt(row.querySelector('.yl-so-qty').value)||0; 
                const p = parseFloat(sel.options[sel.selectedIndex].getAttribute('data-price'))||0;
                if(q > 0) { 
                    items.push({product_id: sel.value, product_name: sel.options[sel.selectedIndex].text, quantity: q, unit_price: p}); 
                    tq += q; ta += (q * p); 
                }
            }
        });
        
        if(items.length === 0) { 
            alert("Add at least one product."); 
            if (btn) btn.disabled = false;
            return; 
        }
        
        const data = { 
            dealer_id: document.getElementById('yl_dealer_id').value, 
            so_date: document.getElementById('yl_so_date').value, 
            items: items, 
            total_qty: tq, 
            total_amount: ta 
        };
        
        window.postData('yl_stock_orders', data, this, 'ylOrderModal', () => {
            if (btn) btn.disabled = false;
            window.loadYLOrders();
        });
    });
}); // <-- END OF DOMContentLoaded

// FUNCTIONS
window.addYlSoItemRow = function() {
    const tbody = document.getElementById('ylSoItemsTbody'); 
    let optionsHTML = '<option value="">Select Product...</option>';
    if (window.globalProductsList && window.globalProductsList.length > 0) { 
        optionsHTML += window.globalProductsList.map(p => `<option value="${p.Product_ID}" data-price="${p.Retail}">[${p.Product_No}] ${p.Product_Name} (₱ ${window.formatCurrency(p.Retail)})</option>`).join(''); 
    }
    const tr = document.createElement('tr'); tr.className = 'yl-so-item-row';
    tr.innerHTML = `<td><select class="form-select odoo-input yl-so-product-select border-0 bg-transparent" required>${optionsHTML}</select></td><td><input type="number" class="form-control odoo-input yl-so-qty border-0 bg-transparent" value="1" min="1" required></td><td><input type="text" class="form-control odoo-input yl-so-price border-0 bg-transparent" readonly></td><td><input type="text" class="form-control odoo-input yl-so-subtotal fw-bold text-primary border-0 bg-transparent text-end" readonly></td><td class="text-center align-middle"><i class="bi bi-trash text-danger" style="cursor:pointer;" onclick="this.closest('tr').remove(); window.calculateYlSOTotals();"></i></td>`;
    tbody.appendChild(tr);
};

window.calculateYlSOTotals = function() {
    let tq = 0; let ta = 0;
    document.querySelectorAll('.yl-so-item-row').forEach(row => {
        const sel = row.querySelector('.yl-so-product-select');
        if(sel && sel.value) {
            const p = parseFloat(sel.options[sel.selectedIndex].getAttribute('data-price'))||0; 
            const q = parseInt(row.querySelector('.yl-so-qty').value)||0; 
            const sub = p * q;
            row.querySelector('.yl-so-price').value = `₱ ${window.formatCurrency(p)}`; 
            row.querySelector('.yl-so-subtotal').value = `₱ ${window.formatCurrency(sub)}`; 
            tq += q; ta += sub;
        }
    });
    document.getElementById('yl_so_total_qty').innerText = window.formatQuantity(tq); 
    document.getElementById('yl_so_total_amount').innerText = `₱ ${window.formatCurrency(ta)}`;
};

window.loadYLOrders = function() { 
    fetch('api/endpoints.php?table=yl_stock_orders').then(r=>r.json()).then(data=>{ 
        const t = document.querySelector('#ylOrdersTable tbody'); 
        if(t){ 
            t.innerHTML = ''; 
            if (data.length === 0 || data.error) {
                t.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted font-monospace">No stock orders found.</td></tr>';
                return;
            }
            data.forEach(row=>{ 
                const badgeClass = row.Payment_Status === 'Paid' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'; 
                t.innerHTML += `<tr><td class="fw-bold">${row.SO_No}</td><td>${window.formatDate(row.SO_Date)}</td><td class="fw-bold text-dark">${row.First_Name} ${row.Last_Name}</td><td class="text-center">${window.formatQuantity(row.Total_Quantity)}</td><td class="text-end fw-bold text-primary">₱ ${window.formatCurrency(row.Total_Amount)}</td><td class="text-center"><span class="badge ${badgeClass}">${row.Payment_Status || 'Pending'}</span></td><td class="text-center">${window.actionIconsHTML('yl_stock_orders', row.SO_ID)}</td></tr>`; 
            }); 
        }
    }); 
};