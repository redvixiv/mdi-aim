// ==========================================
// MDI AIMS - DS ORDERS
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    if(document.getElementById('module-DS')) {
        window.loadDsOrders();
    }

    // EXPLICIT MODAL RESETS TO PREVENT GHOSTING
    document.getElementById('dsOrderModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('dsOrderForm')?.reset();
        document.getElementById('soItemsTbody').innerHTML = '';
        document.getElementById('so_total_qty').innerText = '0';
        document.getElementById('so_total_amount').innerText = '₱ 0.00';
        document.getElementById('so_outlet_address').value = '';
        document.getElementById('so_outlet_branch').value = '';
        document.getElementById('so_outlet_tin').value = '';
    });

    // MODAL OPEN LISTENERS TO UNLOCK BUTTONS
    document.getElementById('dsOrderModal')?.addEventListener('show.bs.modal', function () {
        document.getElementById('dsOrderForm').reset();
        document.getElementById('so_date').valueAsDate = new Date();
        document.getElementById('soItemsTbody').innerHTML = '';
        
        const btn = document.querySelector('#dsOrderForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save Order'; }
        
        if (!window.globalProductsList || window.globalProductsList.length === 0) {
            fetch('api/endpoints.php?table=product_pricing').then(r=>r.json()).then(data => {
                const uniqueProducts = []; const seenIds = new Set();
                data.forEach(item => { if (!seenIds.has(item.Product_ID)) { seenIds.add(item.Product_ID); uniqueProducts.push(item); } });
                window.globalProductsList = uniqueProducts;
                window.addSoItemRow();
            }).catch(() => window.addSoItemRow());
        } else {
            window.addSoItemRow();
        }

        const outletSel = document.getElementById('so_outlet_id');
        fetch(`api/endpoints.php?table=outlets`).then(r => r.json()).then(data => {
            outletSel.innerHTML = `<option value="">Select Customer...</option>`;
            data.forEach(o => { 
                outletSel.innerHTML += `<option value="${o.Outlet_ID}" data-address="${o.Address}" data-branch="${o.Branch}" data-tin="${o.Outlet_TIN}">${o.Outlet_Name}</option>`; 
            });
        });
    });

    // EVENT LISTENERS
    document.addEventListener('change', (e) => {
        if(e.target.id === 'so_outlet_id') {
            const opt = e.target.options[e.target.selectedIndex];
            if(opt && opt.value) {
                document.getElementById('so_outlet_address').value = opt.getAttribute('data-address') || '';
                document.getElementById('so_outlet_branch').value = opt.getAttribute('data-branch') || '';
                document.getElementById('so_outlet_tin').value = opt.getAttribute('data-tin') || '';
            } else {
                document.getElementById('so_outlet_address').value = '';
                document.getElementById('so_outlet_branch').value = '';
                document.getElementById('so_outlet_tin').value = '';
            }
        }
    });

    document.body.addEventListener('click', (e) => {
        const btnAddSo = e.target.closest('#btnAddSoItem');
        if (btnAddSo) window.addSoItemRow();
    });

    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('so-qty') || e.target.classList.contains('so-product-select')) window.calculateSOTotals();
    });

    // STRICT ANTI-DOUBLE-SUBMISSION LOCK
    document.getElementById('dsOrderForm')?.addEventListener('submit', function(e) {
        e.preventDefault(); 
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const items = []; let tq = 0; let ta = 0;
        document.querySelectorAll('.so-item-row').forEach(row => {
            const sel = row.querySelector('.so-product-select');
            if(sel && sel.value) {
                const q = parseInt(row.querySelector('.so-qty').value)||0; 
                const p = parseFloat(sel.options[sel.selectedIndex].getAttribute('data-price'))||0;
                if(q>0) { 
                    items.push({product_id: sel.value, product_name: sel.options[sel.selectedIndex].text, quantity: q, unit_price: p}); 
                    tq+=q; ta+=(q*p); 
                }
            }
        });
        
        if(items.length===0) { 
            alert("Add at least one product."); 
            if (btn) btn.disabled = false;
            return; 
        }
        
        const data = { 
            ds_type: document.getElementById('ds_type_order').value, 
            outlet_id: document.getElementById('so_outlet_id').value, 
            so_date: document.getElementById('so_date').value, 
            items: items, 
            total_qty: tq, 
            total_amount: ta 
        };
        
        window.postData('ds_sales_orders', data, this, 'dsOrderModal', () => {
            if (btn) btn.disabled = false;
            window.loadDsOrders();
        });
    });
});

// FUNCTIONS
window.addSoItemRow = function() {
    const tbody = document.getElementById('soItemsTbody'); 
    let optionsHTML = '<option value="">Select Product...</option>';
    if (window.globalProductsList && window.globalProductsList.length > 0) { 
        optionsHTML += window.globalProductsList.map(p => `<option value="${p.Product_ID}" data-price="${p.Wholesale}">[${p.Product_No}] ${p.Product_Name} (₱ ${window.formatCurrency(p.Wholesale)})</option>`).join(''); 
    }
    const tr = document.createElement('tr'); tr.className = 'so-item-row';
    tr.innerHTML = `<td><select class="form-select odoo-input so-product-select border-0 bg-transparent" required>${optionsHTML}</select></td><td><input type="number" class="form-control odoo-input so-qty border-0 bg-transparent" value="1" min="1" required></td><td><input type="text" class="form-control odoo-input so-price border-0 bg-transparent" readonly></td><td><input type="text" class="form-control odoo-input so-subtotal fw-bold text-primary border-0 bg-transparent text-end" readonly></td><td class="text-center align-middle"><i class="bi bi-trash text-danger" style="cursor:pointer;" onclick="this.closest('tr').remove(); window.calculateSOTotals();"></i></td>`;
    tbody.appendChild(tr);
};

window.calculateSOTotals = function() {
    let tq = 0; let ta = 0;
    document.querySelectorAll('.so-item-row').forEach(row => {
        const sel = row.querySelector('.so-product-select');
        if(sel && sel.value) {
            const p = parseFloat(sel.options[sel.selectedIndex].getAttribute('data-price'))||0; const q = parseInt(row.querySelector('.so-qty').value)||0; const sub = p*q;
            row.querySelector('.so-price').value = `₱ ${window.formatCurrency(p)}`; row.querySelector('.so-subtotal').value = `₱ ${window.formatCurrency(sub)}`; tq += q; ta += sub;
        }
    });
    document.getElementById('so_total_qty').innerText = window.formatQuantity(tq); 
    document.getElementById('so_total_amount').innerText = `₱ ${window.formatCurrency(ta)}`;
};

window.loadDsOrders = function() { 
    fetch('api/endpoints.php?table=ds_sales_orders&ds_type=DS').then(r=>r.json()).then(data=>{ 
        const t=document.querySelector('#dsOrdersTable tbody'); 
        if(t){ 
            t.innerHTML=''; 
            if (data.length === 0 || data.error) {
                t.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted font-monospace">No sales orders found.</td></tr>';
                return;
            }
            data.forEach(row=>{ 
                const badgeClass = row.Payment_Status === 'Paid' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'; 
                t.innerHTML+=`<tr><td class="fw-bold">${row.SO_No}</td><td>${window.formatDate(row.SO_Date)}</td><td>${row.Outlet_Name}</td><td>${row.Branch||'-'}</td><td class="font-monospace text-muted">${row.TIN||'-'}</td><td class="text-center">${window.formatQuantity(row.Total_Quantity)}</td><td class="text-end fw-bold text-primary">₱ ${window.formatCurrency(row.Total_Amount)}</td><td class="text-center"><span class="badge ${badgeClass}">${row.Payment_Status || 'Pending'}</span></td><td class="text-center">${window.actionIconsHTML('ds_sales_orders', row.SO_ID)}</td></tr>`; 
            }); 
        }
    }); 
};