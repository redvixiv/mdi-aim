// ==========================================
// MDI AIMS - PURCHASING SUB-MODULE
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    
    if(document.getElementById('module-Purchasing')) {
        if(typeof window.loadWarehouses === 'function') window.loadWarehouses();
        window.loadPurchaseOrders();
        window.loadGoodsReceipts();
        
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        const purchFrom = document.getElementById('purch_rep_from');
        const purchTo = document.getElementById('purch_rep_to');
        if(purchFrom) purchFrom.valueAsDate = firstDay;
        if(purchTo) purchTo.valueAsDate = lastDay;

        const searchInputPurch = document.getElementById('purchReportSearch');
        if (searchInputPurch) {
            searchInputPurch.addEventListener('keyup', function() {
                const filter = this.value.toUpperCase();
                const tbody = document.querySelector('#purchReportTable tbody');
                if(!tbody) return;
                
                const rows = tbody.querySelectorAll('tr');
                rows.forEach(row => {
                    if (row.innerText.toUpperCase().includes(filter)) {
                        row.style.display = "";
                        row.classList.add('visible-row');
                    } else {
                        row.style.display = "none";
                        row.classList.remove('visible-row');
                    }
                });
                window.recalculatePurchasingReportKPIs();
            });
        }
    }

    // EXPLICIT MODAL RESETS TO PREVENT GHOSTING
    document.getElementById('poModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('poForm')?.reset();
        document.getElementById('poItemsTbody').innerHTML = '';
        document.getElementById('po_total_qty').innerText = '0';
        document.getElementById('po_total_amount').innerText = '₱ 0.00';
    });

    document.getElementById('grModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('grForm')?.reset();
        document.getElementById('grItemsTbody').innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4 font-monospace">Select a Pending PO to populate expected items.</td></tr>';
        document.getElementById('gr_total_qty').innerText = '0';
        document.getElementById('gr_total_amount').innerText = '₱ 0.00';
        document.getElementById('gr_warehouse_id').value = '';
    });

    // MODAL OPEN LISTENERS TO UNLOCK BUTTONS
    document.getElementById('poModal')?.addEventListener('show.bs.modal', function () {
        document.getElementById('poForm').reset();
        document.getElementById('po_date').valueAsDate = new Date();
        document.getElementById('poItemsTbody').innerHTML = '';
        document.getElementById('po_total_qty').innerText = '0';
        document.getElementById('po_total_amount').innerText = '₱ 0.00';
        
        const btn = document.querySelector('#poForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save Order'; }

        if (window.globalProductsList && window.globalProductsList.length > 0) {
            window.addPoItemRow();
        } else {
            if (typeof window.loadGlobalProducts === 'function') window.loadGlobalProducts();
            setTimeout(() => { window.addPoItemRow(); }, 400);
        }
        
        const supSel = document.getElementById('po_supplier_id');
        fetch(`api/endpoints.php?table=suppliers`).then(r => r.json()).then(data => {
            supSel.innerHTML = `<option value="">Select Supplier...</option>`;
            data.forEach(s => { supSel.innerHTML += `<option value="${s.Supplier_ID}">${s.Supplier_Name}</option>`; });
        });
    });

    document.getElementById('grModal')?.addEventListener('show.bs.modal', function () {
        document.getElementById('grForm').reset();
        document.getElementById('gr_arrival_date').valueAsDate = new Date();
        document.getElementById('grItemsTbody').innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4 font-monospace">Select a Pending PO to populate expected items.</td></tr>';
        document.getElementById('gr_total_qty').innerText = '0';
        document.getElementById('gr_total_amount').innerText = '₱ 0.00';
        
        const btn = document.querySelector('#grForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Confirm Receipt'; }

        const poSel = document.getElementById('gr_po_id');
        fetch(`api/endpoints.php?table=purchase_orders`).then(r => r.json()).then(data => {
            poSel.innerHTML = `<option value="">Select PO Reference...</option>`;
            const pending = data.filter(po => po.Status === 'Pending' || po.Status === 'Partially Received');
            if(pending.length === 0) poSel.innerHTML += `<option value="" disabled>No Pending Orders</option>`;
            pending.forEach(po => { poSel.innerHTML += `<option value="${po.PO_ID}" data-wid="${po.Warehouse_ID}" data-wname="${po.Warehouse_Name}" data-items='${po.Items_JSON}'>${po.PO_No} (${po.Supplier_Name})</option>`; });
        });
    });

    // ==========================================
    // STRICT ANTI-DOUBLE-SUBMISSION LOCKS
    // ==========================================
    document.getElementById('poForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const items = []; let totalQty = 0; let totalAmt = 0;
        document.querySelectorAll('.po-item-row').forEach(row => {
            const select = row.querySelector('.po-product-select');
            if(select && select.value) {
                const qty = parseInt(row.querySelector('.po-qty').value) || 0;
                const cost = parseFloat(select.options[select.selectedIndex].getAttribute('data-cost')) || 0;
                if(qty > 0) { 
                    items.push({ product_id: select.value, product_name: select.options[select.selectedIndex].text, quantity: qty, unit_cost: cost, subtotal: qty * cost }); 
                    totalQty += qty; 
                    totalAmt += (qty * cost);
                }
            }
        });
        
        if(items.length === 0) { 
            alert("Please add at least one valid product."); 
            if(btn) btn.disabled = false;
            return; 
        }
        
        const data = { po_no: document.getElementById('po_no').value.toUpperCase(), po_date: document.getElementById('po_date').value, warehouse_id: document.getElementById('po_warehouse_id').value, supplier_id: document.getElementById('po_supplier_id').value, items: items, total_qty: totalQty, total_amount: totalAmt };
        
        window.postData('purchase_orders', data, this, 'poModal', () => {
            if(btn) btn.disabled = false;
            window.loadPurchaseOrders();
        });
    });

    document.getElementById('grForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const items = []; let totalQty = 0; let totalAmt = 0;
        document.querySelectorAll('.gr-item-row').forEach(row => {
            const pid = row.getAttribute('data-pid'); 
            const pname = row.getAttribute('data-pname');
            const cost = parseFloat(row.getAttribute('data-cost')) || 0;
            const qty = parseInt(row.querySelector('.gr-qty').value) || 0;
            if(qty > 0) { 
                items.push({ product_id: pid, product_name: pname, quantity: qty, unit_cost: cost, subtotal: qty * cost }); 
                totalQty += qty; 
                totalAmt += (qty * cost);
            }
        });
        
        if(items.length === 0) { 
            alert("Cannot receive 0 items."); 
            if(btn) btn.disabled = false;
            return; 
        }
        
        const data = { dr_no: document.getElementById('gr_dr_no').value.toUpperCase(), arrival_date: document.getElementById('gr_arrival_date').value, po_id: document.getElementById('gr_po_id').value, warehouse_id: document.getElementById('gr_warehouse_id').value, forwarder: document.getElementById('gr_forwarder').value.toUpperCase(), seal_no: document.getElementById('gr_seal').value.toUpperCase(), remarks: document.getElementById('gr_remarks').value, items: items, total_qty: totalQty, total_amount: totalAmt };
        
        window.postData('goods_receipts', data, this, 'grModal', () => { 
            if(btn) btn.disabled = false;
            window.loadGoodsReceipts(); window.loadPurchaseOrders(); 
            if(typeof window.loadInventoryLedger === 'function') window.loadInventoryLedger(); 
            if(typeof window.loadStockBalances === 'function') window.loadStockBalances(); 
        });
    });

    document.body.addEventListener('click', (e) => {
        if (e.target.closest('#btnAddPoItem')) window.addPoItemRow();
    });

    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('po-qty') || e.target.classList.contains('po-product-select')) window.calculatePOTotals();
        if (e.target.classList.contains('gr-qty')) window.calculateGRTotals();
    });

    document.addEventListener('change', (e) => {
        if(e.target.id === 'gr_po_id') {
            const opt = e.target.options[e.target.selectedIndex];
            if(!opt.value) return;
            document.getElementById('gr_warehouse_id').value = opt.getAttribute('data-wid');
            document.getElementById('gr_warehouse_name').value = opt.getAttribute('data-wname');
            const tbody = document.getElementById('grItemsTbody'); tbody.innerHTML = '';
            try {
                const items = JSON.parse(opt.getAttribute('data-items'));
                items.forEach(item => { 
                    const cost = item.unit_cost || 0;
                    const expectedQty = item.quantity || 0;
                    tbody.innerHTML += `<tr class="gr-item-row" data-pid="${item.product_id}" data-pname="${item.product_name}" data-cost="${cost}">
                        <td><input class="form-control odoo-input fw-bold text-secondary border-0 bg-transparent p-0" value="${item.product_name}" disabled></td>
                        <td class="text-center"><input class="form-control odoo-input fw-bold text-muted border-0 bg-transparent p-0 text-center" value="${window.formatQuantity(expectedQty)}" disabled></td>
                        <td><input type="number" class="form-control odoo-input gr-qty text-success fw-bold border-0 bg-transparent p-0 fs-5 text-center" value="${expectedQty}" min="0"></td>
                        <td class="text-end"><input class="form-control odoo-input fw-bold text-muted border-0 bg-transparent p-0 text-end" value="₱ ${window.formatCurrency(cost)}" disabled></td>
                        <td class="text-end"><input type="text" class="form-control odoo-input gr-subtotal fw-bold text-success border-0 bg-transparent p-0 text-end" readonly></td>
                    </tr>`; 
                });
                window.calculateGRTotals();
            } catch(err) { tbody.innerHTML = '<tr><td colspan="5" class="text-danger text-center py-4">Error parsing items.</td></tr>'; }
        }
    });
});

window.addPoItemRow = function() {
    const tbody = document.getElementById('poItemsTbody'); let optionsHTML = '<option value="">Select Product...</option>';
    if (window.globalProductsList && window.globalProductsList.length > 0) { 
        optionsHTML += window.globalProductsList.map(p => `<option value="${p.Product_ID}" data-cost="${p.Unit_Cost}">[${p.Product_No}] ${p.Product_Name}</option>`).join(''); 
    }
    const tr = document.createElement('tr'); tr.className = 'po-item-row';
    tr.innerHTML = `
        <td><select class="form-select odoo-input po-product-select border-0 bg-transparent p-0" required>${optionsHTML}</select></td>
        <td><input type="number" class="form-control odoo-input po-qty border-0 bg-transparent p-0 fs-6 fw-bold text-primary text-center" value="1" min="1" required></td>
        <td><input type="text" class="form-control odoo-input po-price border-0 bg-transparent p-0 text-muted" readonly></td>
        <td><input type="text" class="form-control odoo-input po-subtotal border-0 bg-transparent p-0 fw-bold text-primary text-end" readonly></td>
        <td class="text-center align-middle"><i class="bi bi-trash text-danger" style="cursor:pointer; font-size:1.2rem;" onclick="this.closest('tr').remove(); window.calculatePOTotals();"></i></td>
    `; 
    tbody.appendChild(tr);
};

window.calculatePOTotals = function() { 
    let tq = 0; let ta = 0;
    document.querySelectorAll('.po-item-row').forEach(row => { 
        const sel = row.querySelector('.po-product-select');
        if(sel && sel.value) {
            const p = parseFloat(sel.options[sel.selectedIndex].getAttribute('data-cost')) || 0;
            const q = parseInt(row.querySelector('.po-qty').value) || 0;
            const sub = p * q;
            
            row.querySelector('.po-price').value = `₱ ${window.formatCurrency(p)}`;
            row.querySelector('.po-subtotal').value = `₱ ${window.formatCurrency(sub)}`;
            
            tq += q; ta += sub;
        }
    }); 
    document.getElementById('po_total_qty').innerText = window.formatQuantity(tq); 
    document.getElementById('po_total_amount').innerText = `₱ ${window.formatCurrency(ta)}`; 
};

window.calculateGRTotals = function() { 
    let tq = 0; let ta = 0; 
    document.querySelectorAll('.gr-item-row').forEach(row => { 
        const p = parseFloat(row.getAttribute('data-cost')) || 0;
        const q = parseInt(row.querySelector('.gr-qty').value) || 0; 
        const sub = p * q;
        
        row.querySelector('.gr-subtotal').value = `₱ ${window.formatCurrency(sub)}`;
        
        tq += q; ta += sub;
    }); 
    document.getElementById('gr_total_qty').innerText = window.formatQuantity(tq); 
    document.getElementById('gr_total_amount').innerText = `₱ ${window.formatCurrency(ta)}`; 
};

window.loadPurchaseOrders = function() {
    fetch('api/endpoints.php?table=purchase_orders').then(r=>r.json()).then(data => {
        const tbody = document.querySelector('#poTable tbody'); if(!tbody) return; tbody.innerHTML = '';
        if(data.length===0) { tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4 font-monospace">No Purchase Orders found.</td></tr>'; return; }
        data.forEach(row => {
            const badgeClass = row.Status === 'Pending' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : (row.Status === 'Fully Received' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-info-subtle text-info-emphasis border border-info-subtle');
            tbody.innerHTML += `<tr><td class="fw-bold">${row.PO_No}</td><td>${window.formatDate(row.PO_Date)}</td><td class="text-dark fw-bold">${row.Supplier_Name}</td><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Warehouse_Name}</span></td><td class="text-center">${window.formatQuantity(row.Total_Quantity)}</td><td class="fw-bold text-primary text-end">₱ ${window.formatCurrency(row.Total_Amount)}</td><td class="text-center"><span class="badge ${badgeClass}">${row.Status}</span></td><td class="text-center">${window.actionIconsHTML('purchase_orders', row.PO_ID)}</td></tr>`;
        });
    });
};

window.loadGoodsReceipts = function() {
    fetch('api/endpoints.php?table=goods_receipts').then(r=>r.json()).then(data => {
        const tbody = document.querySelector('#grTable tbody'); if(!tbody) return; tbody.innerHTML = '';
        if(data.length===0) { tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4 font-monospace">No Deliveries Received yet.</td></tr>'; return; }
        data.forEach(row => {
            let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3"><i class="bi bi-trash text-danger" style="cursor:pointer;" onclick="deleteRecord('goods_receipts', ${row.Receipt_ID})" title="Undo/Delete Receipt"></i></div>`;
            if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) actionsHTML = `<span class="badge bg-light text-muted border border-secondary-subtle">LOCKED</span>`;
            tbody.innerHTML += `<tr><td class="fw-bold text-success">${row.DR_No}</td><td>${window.formatDate(row.Arrival_Date)}</td><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.PO_No}</span></td><td>${row.Forwarder}</td><td>${row.Seal_No}</td><td class="fw-bold text-success text-center">${window.formatQuantity(row.Total_Received)}</td><td class="fw-bold text-success text-end">₱ ${window.formatCurrency(row.Total_Amount)}</td><td class="text-center">${actionsHTML}</td></tr>`;
        });
    });
};

window.loadPurchasingReport = function() {
    const from = document.getElementById('purch_rep_from').value;
    const to = document.getElementById('purch_rep_to').value;
    if (!from || !to) { alert("Please select a date range."); return; }

    const tbody = document.querySelector('#purchReportTable tbody');
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4">Loading report...</td></tr>';

    fetch(`api/endpoints.php?table=purchasing_report&from=${from}&to=${to}`)
    .then(r => r.json())
    .then(data => {
        tbody.innerHTML = '';
        if (data.length === 0 || data.error) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted font-monospace">No purchases found in this date range.</td></tr>';
            document.getElementById('purch_rep_ordered').innerText = '₱ 0.00';
            document.getElementById('purch_rep_received').innerText = '₱ 0.00';
            document.getElementById('purch_rep_pending').innerText = '₱ 0.00';
            return;
        }

        let processedPOs = new Set();
        data.forEach(row => {
            const poAmt = parseFloat(row.PO_Amount) || 0;
            const grAmt = parseFloat(row.GR_Amount) || 0;
            const badgeClass = row.Status === 'Pending' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : (row.Status === 'Fully Received' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-info-subtle text-info-emphasis border border-info-subtle');
            const drNos = row.DR_No ? row.DR_No.split(',').map(dr => `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-1">${dr.trim()}</span>`).join('') : '<span class="text-muted">-</span>';
            
            let isFirstInstance = false;
            if (!processedPOs.has(row.PO_ID)) {
                processedPOs.add(row.PO_ID);
                isFirstInstance = true;
            }

            let items = [];
            try { items = JSON.parse(row.Items_JSON || '[]'); } catch(e) {}
            
            if (items.length === 0) {
                tbody.innerHTML += `<tr class="visible-row" data-poid="${row.PO_ID}" data-first="${isFirstInstance}" data-poamt="${poAmt}" data-gramt="${grAmt}">
                    <td>${window.formatDate(row.PO_Date)}</td>
                    <td class="fw-bold">${row.PO_No}</td>
                    <td>${drNos}</td>
                    <td>${row.Supplier_Name}</td>
                    <td class="text-muted fst-italic">No items</td>
                    <td class="text-center">-</td>
                    <td class="text-end">-</td>
                    <td class="text-end text-primary fw-bold">₱ ${window.formatCurrency(poAmt)}</td>
                    <td class="text-center"><span class="badge ${badgeClass}">${row.Status}</span></td>
                </tr>`;
            } else {
                items.forEach((item, index) => {
                    const displayAmt = index === 0 ? `₱ ${window.formatCurrency(poAmt)}` : '';
                    const displayStatus = index === 0 ? `<span class="badge ${badgeClass}">${row.Status}</span>` : '';
                    const calcData = (index === 0 && isFirstInstance) ? `data-poid="${row.PO_ID}" data-first="true" data-poamt="${poAmt}" data-gramt="${grAmt}"` : 'data-first="false"';

                    tbody.innerHTML += `<tr class="visible-row" ${calcData}>
                        <td>${window.formatDate(row.PO_Date)}</td>
                        <td class="fw-bold text-danger">${row.PO_No}</td>
                        <td>${drNos}</td>
                        <td class="fw-bold text-dark">${row.Supplier_Name}</td>
                        <td class="fw-bold text-dark">${item.product_name}</td>
                        <td class="text-center">${window.formatQuantity(item.quantity)}</td>
                        <td class="text-end">₱ ${window.formatCurrency(item.unit_cost)}</td>
                        <td class="text-end text-primary fw-bolder">${displayAmt}</td>
                        <td class="text-center">${displayStatus}</td>
                    </tr>`;
                });
            }
        });
        window.recalculatePurchasingReportKPIs();
    });
};

window.recalculatePurchasingReportKPIs = function() {
    let totOrdered = 0, totReceived = 0, totPending = 0;
    document.querySelectorAll('#purchReportTable tbody tr.visible-row').forEach(row => {
        if (row.getAttribute('data-first') === 'true') {
            totOrdered += parseFloat(row.getAttribute('data-poamt')) || 0;
            totReceived += parseFloat(row.getAttribute('data-gramt')) || 0;
        }
    });

    totPending = totOrdered - totReceived;
    if (totPending < 0) totPending = 0;

    document.getElementById('purch_rep_ordered').innerText = `₱ ${window.formatCurrency(totOrdered)}`;
    document.getElementById('purch_rep_received').innerText = `₱ ${window.formatCurrency(totReceived)}`;
    document.getElementById('purch_rep_pending').innerText = `₱ ${window.formatCurrency(totPending)}`;
};

window.printPurchasingReport = function() {
    const from = document.getElementById('purch_rep_from').value;
    const to = document.getElementById('purch_rep_to').value;
    const totOrdered = document.getElementById('purch_rep_ordered').innerText;
    const totReceived = document.getElementById('purch_rep_received').innerText;
    const totPending = document.getElementById('purch_rep_pending').innerText;

    let table = document.getElementById('purchReportTable');
    let tableClone = table.cloneNode(true);
    
    Array.from(tableClone.querySelectorAll('tbody tr')).forEach(tr => { if (tr.style.display === 'none') tr.remove(); });
    let styledTable = tableClone.outerHTML
        .replace(/<table[^>]*>/g, '<table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px;">')
        .replace(/<th[^>]*>/g, '<th style="border: 1px solid #ddd; padding: 8px; background: #eee; text-align: left;">')
        .replace(/<td[^>]*>/g, '<td style="border: 1px solid #ddd; padding: 8px;">');

    fetch('api/endpoints.php?table=company_profile').then(r=>r.json()).then(comp => {
        let logoHtml = comp && comp.Logo_Path ? `<img src="${comp.Logo_Path}" style="max-height: 80px; margin-bottom: 10px;">` : '';
        let headerHtml = `<div style="text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #333;">${logoHtml}<h2 style="margin: 0; text-transform: uppercase;">${comp ? comp.Company_Name : 'MDI AIMS'}</h2><p style="margin: 5px 0 0 0; color: #555;">${comp ? comp.Address : ''}</p><p style="margin: 0; color: #555;">TIN: ${comp ? comp.TIN : ''} | Contact: ${comp ? comp.Contact_No : ''}</p></div>`;

        let summaryTable = `
            <table style="width: 350px; border-collapse: collapse; margin-top: 30px; font-size: 12px; margin-left: auto;">
                <tr><td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f8f9fa;">Total Ordered (PO)</td><td style="padding: 8px; border: 1px solid #ddd; text-align: right; font-weight: bold; color: #0d6efd;">${totOrdered}</td></tr>
                <tr><td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f8f9fa;">Total Received (GR)</td><td style="padding: 8px; border: 1px solid #ddd; text-align: right; font-weight: bold; color: #198754;">${totReceived}</td></tr>
                <tr><td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f8f9fa;">Total Pending</td><td style="padding: 8px; border: 1px solid #ddd; text-align: right; font-weight: bold; color: #dc3545;">${totPending}</td></tr>
            </table>
        `;

        let printContent = `<div style="padding: 20px; font-family: Arial, sans-serif; color: #333;">${headerHtml}<h3 style="text-align: center; text-transform: uppercase; margin-bottom: 10px;">Detailed Purchasing Report</h3><p style="text-align: center; color: #666; margin-bottom: 30px;">Period: ${from} to ${to}</p>${styledTable}${summaryTable}</div>`;
        const printWindow = window.open('', '_blank'); 
        printWindow.document.write('<html><head><title>Purchasing Report</title></head><body>' + printContent + '</body></html>'); 
        printWindow.document.close(); printWindow.focus(); 
        setTimeout(() => { printWindow.print(); printWindow.close(); }, 250);
    });
};