// ==========================================
// MDI AIMS - INVENTORY LEDGER SUB-MODULE
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    
    if(document.getElementById('module-Purchasing') || document.getElementById('module-Inventory')) {
        window.loadWarehouses();
    }
    
    if(document.getElementById('module-Inventory')) {
        if(typeof window.loadStockTransfers === 'function') window.loadStockTransfers();
        if(typeof window.loadStockReturns === 'function') window.loadStockReturns();
        
        window.loadInventoryLedger();
        
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        const fromInpInv = document.getElementById('inv_rep_from');
        const toInpInv = document.getElementById('inv_rep_to');
        if(fromInpInv) fromInpInv.valueAsDate = firstDay;
        if(toInpInv) toInpInv.valueAsDate = lastDay;

        const spDateInp = document.getElementById('inv_sp_date');
        if(spDateInp) spDateInp.valueAsDate = new Date();

        const searchInputInv = document.getElementById('invReportSearch');
        if (searchInputInv) {
            searchInputInv.addEventListener('keyup', function() {
                const filter = this.value.toUpperCase();
                const tbody = document.querySelector('#invReportTable tbody');
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
                
                window.recalculateInvReportKPIs();
            });
        }
    }
    
    document.querySelector('button[data-bs-target="#inv-ledger"]')?.addEventListener('shown.bs.tab', () => {
        window.loadInventoryLedger();
    });
    
    document.querySelector('button[data-bs-target="#inv-dashboard"]')?.addEventListener('shown.bs.tab', () => {
        window.loadStockBalances();
    });
    
    document.querySelector('button[data-bs-target="#inv-transfers"]')?.addEventListener('shown.bs.tab', () => {
        if(typeof window.loadStockTransfers === 'function') window.loadStockTransfers();
    });
    
    document.querySelector('button[data-bs-target="#inv-returns"]')?.addEventListener('shown.bs.tab', () => {
        if(typeof window.loadStockReturns === 'function') window.loadStockReturns();
    });

    document.addEventListener('change', (e) => {
        if(e.target.id === 'dash_warehouse_id') window.loadStockBalances();
        if(e.target.id === 'inv_sp_type') {
            const val = e.target.value;
            const thOrig = document.getElementById('sp_th_orig');
            const thLight = document.getElementById('sp_th_light');
            if (val === 'YL') {
                thOrig.innerText = 'Original (CPO)';
                thLight.innerText = 'Light (CPL)';
            } else {
                thOrig.innerText = 'Original (BCO)';
                thLight.innerText = 'Light (BCL)';
            }
        }
    });
});

window.loadWarehouses = function() {
    fetch('api/endpoints.php?table=warehouses').then(r=>r.json()).then(data => {
        const poSel = document.getElementById('po_warehouse_id'); if(poSel) poSel.innerHTML = '<option value="">Select Warehouse...</option>';
        const dashSel = document.getElementById('dash_warehouse_id'); if(dashSel) dashSel.innerHTML = '<option value="">All Warehouses</option>';
        const trFrom = document.getElementById('tr_from_warehouse'); if(trFrom) trFrom.innerHTML = '<option value="">Select Source...</option>';
        const trTo = document.getElementById('tr_to_warehouse'); if(trTo) trTo.innerHTML = '<option value="">Select Destination...</option>';
        const rtSel = document.getElementById('rt_warehouse_id'); if(rtSel) rtSel.innerHTML = '<option value="">Select Warehouse...</option>';
        
        if (data && !data.error) {
            data.forEach(w => {
                const opt = `<option value="${w.Warehouse_ID}">${w.Warehouse_Name}</option>`;
                if(poSel) poSel.innerHTML += opt;
                if(dashSel) dashSel.innerHTML += `<option value="${w.Warehouse_ID}">${w.Warehouse_Name} (${w.Location})</option>`;
                if(trFrom) trFrom.innerHTML += opt;
                if(trTo) trTo.innerHTML += opt;
                if(rtSel) rtSel.innerHTML += opt; 
            });
        }
        if(document.getElementById('module-Inventory')) {
            window.loadStockBalances();
        }
    });
};

window.loadInventoryLedger = function() {
    fetch('api/endpoints.php?table=inventory_ledger').then(r=>r.json()).then(data => {
        const tbody = document.querySelector('#invLedgerTable tbody'); if(!tbody) return; tbody.innerHTML = '';
        if(data.length===0 || data.error) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4 font-monospace">No inventory movements recorded.</td></tr>';
            return;
        }
        data.forEach(row => {
            const isStockIn = row.Transaction_Type === 'Stock In' || row.Transaction_Type === 'Transfer In' || row.Transaction_Type === 'Return In';
            const badgeClass = isStockIn ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle';
            
            let refHtml = `<span class="font-monospace text-muted">${row.Reference_No}</span>`;
            if (row.Reference_No && row.Reference_No.includes('[Damaged]')) {
                refHtml = `<span class="font-monospace text-danger fw-bold">${row.Reference_No}</span>`;
            }

            tbody.innerHTML += `<tr><td>${window.formatDate(row.Transaction_Date)}</td><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Warehouse_Name}</span></td><td class="fw-bold text-dark">${row.Product_Name}</td><td><span class="badge ${badgeClass}">${row.Transaction_Type}</span></td><td>${refHtml}</td><td class="text-success text-center fw-bold">${row.Qty_In > 0 ? window.formatQuantity(row.Qty_In) : '-'}</td><td class="text-danger text-center fw-bold">${row.Qty_Out > 0 ? window.formatQuantity(row.Qty_Out) : '-'}</td></tr>`;
        });
    });
};

window.loadStockBalances = function() {
    const wid = document.getElementById('dash_warehouse_id')?.value;
    if(!wid) return;
    fetch(`api/endpoints.php?table=stock_balances&warehouse_id=${wid}`).then(r=>r.json()).then(data => {
        const tbody = document.querySelector('#stockBalancesTable tbody'); if(!tbody) return; tbody.innerHTML = '';
        if(data.length===0 || data.error) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4 font-monospace">No products configured.</td></tr>';
            return;
        }
        data.forEach(row => {
            const stock = parseInt(row.Current_Stock);
            const stockColor = stock > 0 ? 'text-success' : (stock < 0 ? 'text-danger' : 'text-muted');
            tbody.innerHTML += `<tr><td class="fw-bold font-monospace text-muted">${row.Product_No}</td><td class="fw-bold text-dark">${row.Product_Name}</td><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Category}</span></td><td class="text-end pe-4 fs-5 fw-bolder ${stockColor}">${window.formatQuantity(stock)}</td></tr>`;
        });
    });
};

window.loadInvReport = function() {
    const from = document.getElementById('inv_rep_from').value;
    const to = document.getElementById('inv_rep_to').value;
    if (!from || !to) { alert("Please select a date range."); return; }

    const tbody = document.querySelector('#invReportTable tbody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Loading report...</td></tr>';

    fetch(`api/endpoints.php?table=inventory_report&from=${from}&to=${to}`)
    .then(r => r.json())
    .then(data => {
        tbody.innerHTML = '';
        if (data.length === 0 || data.error) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted font-monospace">No inventory movements found in this date range.</td></tr>';
            document.getElementById('inv_rep_in').innerText = '0';
            document.getElementById('inv_rep_out').innerText = '0';
            document.getElementById('inv_rep_net').innerText = '0';
            return;
        }

        data.forEach(row => {
            const isStockIn = row.Transaction_Type === 'Stock In' || row.Transaction_Type === 'Transfer In' || row.Transaction_Type === 'Return In';
            const badgeClass = isStockIn ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle';
            const qtyIn = parseInt(row.Qty_In) || 0;
            const qtyOut = parseInt(row.Qty_Out) || 0;
            
            let refHtml = `<span class="font-monospace text-muted">${row.Reference_No}</span>`;
            if (row.Reference_No && row.Reference_No.includes('[Damaged]')) {
                refHtml = `<span class="font-monospace text-danger fw-bold">${row.Reference_No}</span>`;
            }

            tbody.innerHTML += `<tr class="visible-row" data-in="${qtyIn}" data-out="${qtyOut}">
                <td>${window.formatDate(row.Transaction_Date)}</td>
                <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Warehouse_Name}</span></td>
                <td class="fw-bold text-dark">${row.Product_Name}</td>
                <td><span class="badge ${badgeClass}">${row.Transaction_Type}</span></td>
                <td>${refHtml}</td>
                <td class="text-success text-center fw-bold">${qtyIn > 0 ? window.formatQuantity(qtyIn) : '-'}</td>
                <td class="text-danger text-center fw-bold">${qtyOut > 0 ? window.formatQuantity(qtyOut) : '-'}</td>
            </tr>`;
        });
        window.recalculateInvReportKPIs();
    });
};

window.recalculateInvReportKPIs = function() {
    let totIn = 0, totOut = 0;
    
    document.querySelectorAll('#invReportTable tbody tr.visible-row').forEach(row => {
        totIn += parseInt(row.getAttribute('data-in')) || 0;
        totOut += parseInt(row.getAttribute('data-out')) || 0;
    });

    const net = totIn - totOut;
    const netColor = net > 0 ? 'text-success' : (net < 0 ? 'text-danger' : 'text-primary');
    const netSign = net > 0 ? '+' : '';

    document.getElementById('inv_rep_in').innerText = window.formatQuantity(totIn);
    document.getElementById('inv_rep_out').innerText = window.formatQuantity(totOut);
    
    const netEl = document.getElementById('inv_rep_net');
    netEl.innerText = netSign + window.formatQuantity(net);
    netEl.className = `mb-0 fw-bolder mt-1 ${netColor}`;
};

window.printInvReport = function() {
    const from = document.getElementById('inv_rep_from').value;
    const to = document.getElementById('inv_rep_to').value;
    const totIn = document.getElementById('inv_rep_in').innerText;
    const totOut = document.getElementById('inv_rep_out').innerText;
    const totNet = document.getElementById('inv_rep_net').innerText;

    let table = document.getElementById('invReportTable');
    let tableClone = table.cloneNode(true);
    
    Array.from(tableClone.querySelectorAll('tbody tr')).forEach(tr => {
        if (tr.style.display === 'none') {
            tr.remove();
        }
    });
    let tableHTML = tableClone.outerHTML;

    let styledTable = tableHTML
        .replace(/<table[^>]*>/g, '<table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px;">')
        .replace(/<th[^>]*>/g, '<th style="border: 1px solid #ddd; padding: 8px; background: #eee; text-align: left;">')
        .replace(/<td[^>]*>/g, '<td style="border: 1px solid #ddd; padding: 8px;">');

    fetch('api/endpoints.php?table=company_profile').then(r=>r.json()).then(comp => {
        let logoHtml = comp && comp.Logo_Path ? `<img src="${comp.Logo_Path}" style="max-height: 80px; margin-bottom: 10px;">` : '';
        let headerHtml = `<div style="text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #333;">${logoHtml}<h2 style="margin: 0; text-transform: uppercase;">${comp ? comp.Company_Name : 'MDI AIMS'}</h2><p style="margin: 5px 0 0 0; color: #555;">${comp ? comp.Address : ''}</p><p style="margin: 0; color: #555;">TIN: ${comp ? comp.TIN : ''} | Contact: ${comp ? comp.Contact_No : ''}</p></div>`;

        let summaryTable = `
            <table style="width: 300px; border-collapse: collapse; margin-top: 30px; font-size: 12px; margin-left: auto;">
                <tr><td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f8f9fa;">Total Stock In</td><td style="padding: 8px; border: 1px solid #ddd; text-align: right; font-weight: bold; color: #198754;">${totIn}</td></tr>
                <tr><td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f8f9fa;">Total Stock Out</td><td style="padding: 8px; border: 1px solid #ddd; text-align: right; font-weight: bold; color: #dc3545;">${totOut}</td></tr>
                <tr><td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f8f9fa;">Net Movement</td><td style="padding: 8px; border: 1px solid #ddd; text-align: right; font-weight: bold; color: #0d6efd;">${totNet}</td></tr>
            </table>
        `;

        let printContent = `
            <div style="padding: 20px; font-family: Arial, sans-serif; color: #333;">
                ${headerHtml}
                <h3 style="text-align: center; text-transform: uppercase; margin-bottom: 10px;">Inventory Movement Report</h3>
                <p style="text-align: center; color: #666; margin-bottom: 30px;">Period: ${from} to ${to}</p>
                ${styledTable}
                ${summaryTable}
            </div>
        `;
        const printWindow = window.open('', '_blank'); 
        printWindow.document.write('<html><head><title>Inventory Movement Report</title></head><body>' + printContent + '</body></html>'); 
        printWindow.document.close(); 
        printWindow.focus(); 
        setTimeout(() => { printWindow.print(); printWindow.close(); }, 250);
    });
};

window.loadInvStockPositionTable = function() {
    const spDate = document.getElementById('inv_sp_date').value;
    const spType = document.getElementById('inv_sp_type').value;
    if(!spDate) { alert("Please select a Target Date."); return; }

    const tbody = document.querySelector('#invStockPositionTable tbody');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Loading data...</td></tr>';

    fetch(`api/endpoints.php?table=stock_position_report&type=${spType}&date=${spDate}`)
    .then(r=>r.json()).then(data => {
        if(data.status !== 'success') {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger font-monospace">Error: ${data.message}</td></tr>`;
            return;
        }

        let html = '';
        const bO = parseInt(data.beginning.orig)||0, bL = parseInt(data.beginning.light)||0;
        
        const fO = parseInt(data.additions_factory.orig)||0, fL = parseInt(data.additions_factory.light)||0;
        const rtvO = parseInt(data.additions_rtv.orig)||0, rtvL = parseInt(data.additions_rtv.light)||0;
        const aO = fO + rtvO, aL = fL + rtvL;
        
        const avO = bO+aO, avL = bL+aL;

        html += `
            <tr class="bg-light">
                <td class="fw-bold">BEGINNING</td>
                <td class="text-muted">Beginning Stock Position</td>
                <td class="text-end fw-bold">${window.formatQuantity(bO)}</td>
                <td class="text-end text-info fw-bold">${window.formatQuantity(bL)}</td>
                <td class="text-end text-primary fw-bolder">${window.formatQuantity(bO+bL)}</td>
            </tr>
            <tr>
                <td class="fw-bold">ADDITIONS</td>
                <td class="text-muted">Received From Factory (Goods Receipts)</td>
                <td class="text-end">${window.formatQuantity(fO)}</td>
                <td class="text-end text-info">${window.formatQuantity(fL)}</td>
                <td class="text-end text-primary fw-bold">${window.formatQuantity(fO+fL)}</td>
            </tr>
            <tr>
                <td class="fw-bold border-bottom"></td>
                <td class="text-muted border-bottom">Sales Returns - RTV</td>
                <td class="text-end border-bottom">${window.formatQuantity(rtvO)}</td>
                <td class="text-end text-info border-bottom">${window.formatQuantity(rtvL)}</td>
                <td class="text-end text-primary fw-bold border-bottom">${window.formatQuantity(rtvO+rtvL)}</td>
            </tr>
            <tr class="bg-success-subtle border-top border-2 border-success">
                <td class="fw-bolder text-success">AVAILABLE</td>
                <td class="text-success">Total Stock Available</td>
                <td class="text-end fw-bolder text-success">${window.formatQuantity(avO)}</td>
                <td class="text-end fw-bolder text-success">${window.formatQuantity(avL)}</td>
                <td class="text-end fw-bolder text-success fs-6">${window.formatQuantity(avO+avL)}</td>
            </tr>
        `;

        let sO = 0, sL = 0;
        let salesHtml = '';
        for (const [route, qtys] of Object.entries(data.sales)) {
            const o = parseInt(qtys.orig)||0, l = parseInt(qtys.light)||0;
            sO += o; sL += l;
            salesHtml += `<tr><td><span class="badge bg-secondary-subtle text-secondary border">SALE</span></td><td class="text-dark">${route}</td><td class="text-end">${window.formatQuantity(o)}</td><td class="text-end text-info">${window.formatQuantity(l)}</td><td class="text-end text-primary fw-bold">${window.formatQuantity(o+l)}</td></tr>`;
        }
        if(salesHtml === '') salesHtml = '<tr><td><span class="badge bg-secondary-subtle text-secondary border">SALE</span></td><td class="text-muted fst-italic">No sales logged</td><td class="text-end">0</td><td class="text-end text-info">0</td><td class="text-end text-primary fw-bold">0</td></tr>';
        html += salesHtml;

        let tO = 0, tL = 0;
        let transHtml = '';
        for (const [wh, qtys] of Object.entries(data.transfers)) {
            const o = parseInt(qtys.orig)||0, l = parseInt(qtys.light)||0;
            tO += o; tL += l;
            transHtml += `<tr><td><span class="badge bg-danger-subtle text-danger border">TRANSFER</span></td><td class="text-dark">${wh}</td><td class="text-end">${window.formatQuantity(o)}</td><td class="text-end text-info">${window.formatQuantity(l)}</td><td class="text-end text-primary fw-bold">${window.formatQuantity(o+l)}</td></tr>`;
        }
        if(transHtml === '') transHtml = '<tr><td><span class="badge bg-danger-subtle text-danger border">TRANSFER</span></td><td class="text-muted fst-italic">No transfers logged</td><td class="text-end">0</td><td class="text-end text-info">0</td><td class="text-end text-primary fw-bold">0</td></tr>';
        html += transHtml;

        const endO = avO - sO - tO;
        const endL = avL - sL - tL;

        html += `
            <tr class="bg-primary-subtle border-top border-4 border-primary">
                <td class="fw-bolder text-primary">ENDING</td>
                <td class="text-primary fw-bold">Ending Stock Position</td>
                <td class="text-end fw-bolder text-primary fs-6">${window.formatQuantity(endO)}</td>
                <td class="text-end fw-bolder text-info fs-6">${window.formatQuantity(endL)}</td>
                <td class="text-end fw-bolder text-dark fs-5">${window.formatQuantity(endO+endL)}</td>
            </tr>
        `;
        tbody.innerHTML = html;
    });
};

window.printInvStockPosition = function() {
    const spDate = document.getElementById('inv_sp_date') ? document.getElementById('inv_sp_date').value : new Date().toISOString().split('T')[0];
    const spType = document.getElementById('inv_sp_type') ? document.getElementById('inv_sp_type').value : 'YL';
    
    if(!spDate) { alert('Please select a Target Date first.'); return; }

    const printHeaderColor = spType === 'YL' ? '#d32f2f' : '#198754';
    const printTitle = spType === 'YL' ? 'YAKULT LADY' : 'DIRECT SALES';
    const printOrigLbl = spType === 'YL' ? 'Original- CPO' : 'Original- BCO';
    const printLightLbl = spType === 'YL' ? 'Light- CPL' : 'Light- BCL';
    const printConvLbl = spType === 'YL' ? 'CONVERTED- To DS- BC' : 'CONVERTED- To YL- CP';

    fetch(`api/endpoints.php?table=stock_position_report&type=${spType}&date=${spDate}`).then(r=>r.json()).then(data => {
        if(data.status !== 'success') { alert('Error generating report: ' + data.message); return; }
        
        let salesRows = '';
        let totSalesOrig = 0; let totSalesLight = 0;
        for (const [route, qtys] of Object.entries(data.sales)) {
            const o = parseInt(qtys.orig) || 0; const l = parseInt(qtys.light) || 0; const tot = o + l;
            totSalesOrig += o; totSalesLight += l;
            salesRows += `<tr><td style="padding: 4px 10px; border: none; font-size: 12px; padding-left: 30px;">${route}</td><td style="padding: 4px 10px; border: none; text-align: right; font-size: 12px;">${o === 0 ? '-' : window.formatQuantity(o)}</td><td style="padding: 4px 10px; border: none; text-align: right; font-size: 12px;">${l === 0 ? '-' : window.formatQuantity(l)}</td><td style="padding: 4px 10px; border: none; text-align: right; font-size: 12px;">${window.formatQuantity(tot)}</td></tr>`;
        }
        if(salesRows === '') salesRows = `<tr><td style="padding: 4px 10px; border: none; font-size: 12px; padding-left: 30px;">No Route/Center Sales Logged</td><td></td><td></td><td></td></tr>`;
        
        let transRows = '';
        let totTransOrig = 0; let totTransLight = 0;
        for (const [wh, qtys] of Object.entries(data.transfers)) {
            const o = parseInt(qtys.orig) || 0; const l = parseInt(qtys.light) || 0; const tot = o + l;
            totTransOrig += o; totTransLight += l;
            transRows += `<tr><td style="padding: 4px 10px; border: none; font-size: 12px; padding-left: 30px;">${wh}</td><td style="padding: 4px 10px; border: none; text-align: right; font-size: 12px;">${o === 0 ? '-' : window.formatQuantity(o)}</td><td style="padding: 4px 10px; border: none; text-align: right; font-size: 12px;">${l === 0 ? '-' : window.formatQuantity(l)}</td><td style="padding: 4px 10px; border: none; text-align: right; font-size: 12px;">${window.formatQuantity(tot)}</td></tr>`;
        }
        if(transRows === '') transRows = `<tr><td style="padding: 4px 10px; border: none; font-size: 12px; padding-left: 30px;">No Transfers Logged</td><td></td><td></td><td></td></tr>`;

        const begOrig = parseInt(data.beginning.orig) || 0; const begLight = parseInt(data.beginning.light) || 0;
        
        const fO = parseInt(data.additions_factory.orig)||0, fL = parseInt(data.additions_factory.light)||0;
        const rtvO = parseInt(data.additions_rtv.orig)||0, rtvL = parseInt(data.additions_rtv.light)||0;
        const addOrig = fO + rtvO;
        const addLight = fL + rtvL;
        
        const availOrig = begOrig + addOrig; const availLight = begLight + addLight;
        const endOrig = availOrig - totSalesOrig - totTransOrig; const endLight = availLight - totSalesLight - totTransLight;

        let printHTML = `
        <div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;">
            <h4 style="text-align: center; margin-bottom: 5px;">MERCHANDISE DISTRIBUTORS, INC.</h4>
            <h5 style="text-align: center; margin-top: 0; font-weight: normal;">STOCK POSITION REPORT</h5>
            
            <div style="display: flex; justify-content: space-between; margin-top: 30px; font-size: 13px; font-weight: bold;">
                <div style="border-bottom: 1px solid #000; width: 40%;">STORAGE: MANDAUE- MAIN</div>
                <div style="border-bottom: 1px solid #000; width: 30%; text-align: right;">DATE: &nbsp;&nbsp; ${window.formatDate(spDate)}</div>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 12px;">
                <thead><tr><th colspan="4" style="text-align: center; border: 1px solid #000; padding: 8px; color: ${printHeaderColor};">${printTitle}</th></tr>
                <tr style="border-bottom: 1px solid #000;"><th style="padding: 8px;">Product</th><th style="text-align: right;">${printOrigLbl}</th><th style="text-align: right;">${printLightLbl}</th><th style="text-align: right;">TOTAL</th></tr></thead>
                <tbody>
                    <tr><td style="padding: 8px 10px;">BEGINNING STOCK POSITION</td><td style="text-align: right;">${window.formatQuantity(begOrig)}</td><td style="text-align: right;">${window.formatQuantity(begLight)}</td><td style="text-align: right;">${window.formatQuantity(begOrig + begLight)}</td></tr>
                    
                    <tr><td style="padding: 4px 10px;">ADD: &nbsp;&nbsp;&nbsp; Received From Factory</td><td style="text-align: right;">${fO === 0 ? '-' : window.formatQuantity(fO)}</td><td style="text-align: right;">${fL === 0 ? '-' : window.formatQuantity(fL)}</td><td style="text-align: right;">${(fO + fL) === 0 ? '-' : window.formatQuantity(fO + fL)}</td></tr>
                    <tr><td style="padding: 4px 10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Sales Returns- RTV</td><td style="text-align: right;">${rtvO === 0 ? '-' : window.formatQuantity(rtvO)}</td><td style="text-align: right;">${rtvL === 0 ? '-' : window.formatQuantity(rtvL)}</td><td style="text-align: right;">${(rtvO + rtvL) === 0 ? '-' : window.formatQuantity(rtvO + rtvL)}</td></tr>
                    
                    <tr style="border-bottom: 1px solid #000;"><td style="padding: 4px 10px; padding-bottom: 8px;">STOCK AVAILABLE</td><td style="text-align: right; font-weight: bold;">${window.formatQuantity(availOrig)}</td><td style="text-align: right; font-weight: bold;">${window.formatQuantity(availLight)}</td><td style="text-align: right; font-weight: bold;">${window.formatQuantity(availOrig + availLight)}</td></tr>
                    
                    <tr><td style="padding: 8px 10px;" colspan="4">LESS: &nbsp;&nbsp; SALES / CENTER TRANSFERS</td></tr>
                    ${salesRows}
                    <tr style="border-bottom: 1px solid #000;"><td style="padding: 8px 10px;"></td><td style="text-align: right; font-weight: bold;">${totSalesOrig === 0 ? '-' : window.formatQuantity(totSalesOrig)}</td><td style="text-align: right; font-weight: bold;">${totSalesLight === 0 ? '-' : window.formatQuantity(totSalesLight)}</td><td style="text-align: right; font-weight: bold;">${(totSalesOrig + totSalesLight) === 0 ? '-' : window.formatQuantity(totSalesOrig + totSalesLight)}</td></tr>

                    <tr><td style="padding: 8px 10px;" colspan="4">LESS: &nbsp;&nbsp; WAREHOUSE STOCK TRANSFERS</td></tr>
                    ${transRows}
                    <tr style="border-bottom: 1px solid #000;"><td style="padding: 8px 10px;"></td><td style="text-align: right; font-weight: bold;">${totTransOrig === 0 ? '-' : window.formatQuantity(totTransOrig)}</td><td style="text-align: right; font-weight: bold;">${totTransLight === 0 ? '-' : window.formatQuantity(totTransLight)}</td><td style="text-align: right; font-weight: bold;">${(totTransOrig + totTransLight) === 0 ? '-' : window.formatQuantity(totTransOrig + totTransLight)}</td></tr>

                    <tr><td style="padding: 8px 10px;">LESS: &nbsp;&nbsp; ${printConvLbl}</td><td style="text-align: right;">-</td><td style="text-align: right;">-</td><td style="text-align: right;">-</td></tr>

                    <tr style="border: 2px solid #000;"><td style="padding: 8px 10px; font-weight: bold;">ENDING STOCK POSITION- ${spType}</td><td style="text-align: right; font-weight: bold;">${window.formatQuantity(endOrig)}</td><td style="text-align: right; font-weight: bold;">${window.formatQuantity(endLight)}</td><td style="text-align: right; font-weight: bold;">${window.formatQuantity(endOrig + endLight)}</td></tr>
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between; margin-top: 80px; font-size: 13px;">
                <div style="width: 30%; text-align: center;"><div style="border-bottom: 1px solid #000; height: 30px; margin-bottom: 5px;"></div>Prepared By:</div>
                <div style="width: 30%; text-align: center;"><div style="border-bottom: 1px solid #000; height: 30px; margin-bottom: 5px;"></div>Checked By:</div>
                <div style="width: 30%; text-align: center;"><div style="border-bottom: 1px solid #000; height: 30px; margin-bottom: 5px;"></div>Storage-in-Charge</div>
            </div>
            <div style="text-align: center; margin-top: 50px; font-size: 13px;">
                <p>Noted By:</p>
                <div style="width: 40%; margin: 0 auto; border-bottom: 1px solid #000; height: 30px; margin-bottom: 5px;"></div>Sales Manager
            </div>
        </div>
        `;
        const printWin = window.open('', '_blank');
        printWin.document.write(printHTML); printWin.document.close(); printWin.focus();
        setTimeout(() => { printWin.print(); printWin.close(); }, 500);
    });
};