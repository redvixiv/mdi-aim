// ==========================================
// MDI AIMS - INVENTORY MOVEMENTS SUB-MODULE
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    
    // Global variable to hold live stock for the selected warehouse
    window.currentWarehouseStock = null;

    // EXPLICIT RESETS TO PREVENT GHOSTING
    document.getElementById('transferModal')?.addEventListener('hidden.bs.modal', function () { 
        document.getElementById('transferForm')?.reset(); 
        document.getElementById('trItemsTbody').innerHTML = '';
    });

    // MODAL OPEN LISTENERS
    document.getElementById('transferModal')?.addEventListener('show.bs.modal', function () {
        document.getElementById('transferForm').reset();
        document.getElementById('tr_date').valueAsDate = new Date();
        document.getElementById('tr_total_qty').innerText = '0';
        window.currentWarehouseStock = null;
        
        document.getElementById('trItemsTbody').innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted font-monospace">Select a Source Warehouse to view available stock.</td></tr>';
        
        const btn = document.querySelector('#transferForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Post Transfer'; }
        
        if (typeof window.loadGlobalProducts === 'function') window.loadGlobalProducts();
    });

    // SMART WAREHOUSE SELECTION LOGIC
    document.getElementById('tr_from_warehouse')?.addEventListener('change', function() {
        const wid = this.value;
        const tbody = document.getElementById('trItemsTbody');
        document.getElementById('tr_total_qty').innerText = '0';
        window.currentWarehouseStock = null;

        if(!wid) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted font-monospace">Select a Source Warehouse to view available stock.</td></tr>';
            return;
        }

        tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted font-monospace">Loading available stock...</td></tr>';
        
        fetch(`api/endpoints.php?table=stock_balances&warehouse_id=${wid}`)
        .then(r=>r.json())
        .then(data => {
            if(!data.error) {
                window.currentWarehouseStock = data.filter(item => parseFloat(item.Current_Stock) > 0);
            } else {
                window.currentWarehouseStock = [];
            }
            
            tbody.innerHTML = '';
            if(window.currentWarehouseStock.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-danger font-monospace fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>No stock available in this warehouse.</td></tr>';
            } else {
                window.addTrItemRow();
            }
        });
    });

    // DYNAMIC EVENT LISTENERS
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('tr-product-select')) {
            const row = e.target.closest('tr');
            const qtyInput = row.querySelector('.tr-qty');
            if (qtyInput && e.target.value) {
                const max = parseInt(e.target.options[e.target.selectedIndex].getAttribute('data-max')) || 0;
                if (parseInt(qtyInput.value) > max && max > 0) {
                    qtyInput.value = max;
                }
                window.calculateTRTotals();
            }
        }
    });

    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('tr-qty')) {
            const row = e.target.closest('tr');
            const sel = row.querySelector('.tr-product-select');
            if (sel && sel.value) {
                const max = parseInt(sel.options[sel.selectedIndex].getAttribute('data-max')) || 0;
                const val = parseInt(e.target.value) || 0;
                if (max > 0 && val > max) {
                    alert(`Quantity cannot exceed available stock (${max}).`);
                    e.target.value = max;
                }
            }
            window.calculateTRTotals();
        }
    });

    document.body.addEventListener('click', (e) => {
        if (e.target.closest('#btnAddTrItem')) window.addTrItemRow();
    });

    // ==========================================
    // STRICT ANTI-DOUBLE-SUBMISSION LOCKS
    // ==========================================
    document.getElementById('transferForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const items = []; let totalQty = 0;
        document.querySelectorAll('.tr-item-row').forEach(row => {
            const select = row.querySelector('.tr-product-select');
            if(select && select.value) {
                const qty = parseInt(row.querySelector('.tr-qty').value) || 0;
                if(qty > 0) { items.push({ product_id: select.value, product_name: select.options[select.selectedIndex].text.split(' (Avail:')[0], quantity: qty }); totalQty += qty; }
            }
        });
        
        if(items.length === 0) { 
            alert("Please add at least one valid product."); 
            if(btn) btn.disabled = false;
            return; 
        }
        
        const data = { transfer_no: document.getElementById('tr_no').value.toUpperCase(), transfer_date: document.getElementById('tr_date').value, from_warehouse_id: document.getElementById('tr_from_warehouse').value, to_warehouse_id: document.getElementById('tr_to_warehouse').value, remarks: document.getElementById('tr_remarks').value, items: items, total_qty: totalQty };
        
        window.postData('stock_transfers', data, this, 'transferModal', () => { 
            if(btn) btn.disabled = false;
            window.loadStockTransfers(); 
            if(typeof window.loadInventoryLedger === 'function') window.loadInventoryLedger(); 
            if(typeof window.loadStockBalances === 'function') window.loadStockBalances(); 
        });
    });
});

window.addTrItemRow = function() {
    const wid = document.getElementById('tr_from_warehouse')?.value;
    if(!wid) { alert("Please select a Source Warehouse first."); return; }

    if(window.currentWarehouseStock && window.currentWarehouseStock.length === 0) { alert("This warehouse has no available stock to transfer."); return; }

    const tbody = document.getElementById('trItemsTbody');
    // Clear placeholder text if it's there
    if(tbody.querySelector('td[colspan="3"]')) tbody.innerHTML = '';

    let optionsHTML = '<option value="" data-max="0">Select Product...</option>';
    
    if (window.globalProductsList && window.globalProductsList.length > 0 && window.currentWarehouseStock) { 
        window.globalProductsList.forEach(p => {
            // Find stock match
            const stockItem = window.currentWarehouseStock.find(s => s.Product_No === p.Product_No);
            if (stockItem && parseFloat(stockItem.Current_Stock) > 0) {
                optionsHTML += `<option value="${p.Product_ID}" data-max="${stockItem.Current_Stock}">[${p.Product_No}] ${p.Product_Name} (Avail: ${window.formatQuantity(stockItem.Current_Stock)})</option>`;
            }
        });
    }

    const tr = document.createElement('tr'); tr.className = 'tr-item-row';
    tr.innerHTML = `<td><select class="form-select odoo-input tr-product-select border-0 bg-transparent p-0" required>${optionsHTML}</select></td><td><input type="number" class="form-control odoo-input tr-qty border-0 bg-transparent p-0 fs-6 fw-bold text-primary" value="1" min="1" required></td><td class="text-center align-middle"><i class="bi bi-trash text-danger" style="cursor:pointer; font-size:1.2rem;" onclick="this.closest('tr').remove(); window.calculateTRTotals();"></i></td>`; 
    tbody.appendChild(tr);
};

window.calculateTRTotals = function() { 
    let tot = 0; 
    document.querySelectorAll('.tr-qty').forEach(input => { tot += parseInt(input.value) || 0; }); 
    document.getElementById('tr_total_qty').innerText = window.formatQuantity(tot); 
};

window.loadStockTransfers = function() {
    fetch('api/endpoints.php?table=stock_transfers')
    .then(r=>r.json())
    .then(data => {
        const tbody = document.querySelector('#transfersTable tbody'); 
        if(!tbody) return; 
        tbody.innerHTML = '';
        if(data.length===0 || data.error) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4 font-monospace">No Transfers found.</td></tr>';
            return;
        }
        data.forEach(row => {
            let productsStr = '-'; 
            if (row.Items_JSON) { 
                try { 
                    const items = JSON.parse(row.Items_JSON); 
                    productsStr = items.map(i => i.product_name).join(', '); 
                } catch(e) {} 
            }
            
            let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3"><i class="bi bi-trash text-danger" style="cursor:pointer;" onclick="deleteRecord('stock_transfers', ${row.Transfer_ID})" title="Undo/Delete Transfer"></i></div>`;
            if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) {
                actionsHTML = `<span class="badge bg-light text-muted border border-secondary-subtle">LOCKED</span>`;
            }
            
            tbody.innerHTML += `<tr>
                <td class="fw-bold text-primary">${row.Transfer_No}</td>
                <td>${window.formatDate(row.Transfer_Date)}</td>
                <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle">${row.From_Warehouse}</span></td>
                <td><span class="badge bg-success-subtle text-success border border-success-subtle">${row.To_Warehouse}</span></td>
                <td class="fw-bold text-dark">${productsStr}</td>
                <td class="text-center">${window.formatQuantity(row.Total_Quantity)}</td>
                <td><small class="text-muted">${row.Remarks || '-'}</small></td>
                <td class="text-center">${actionsHTML}</td>
            </tr>`;
        });
    });
};