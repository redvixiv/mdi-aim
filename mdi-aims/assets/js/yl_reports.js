// ==========================================
// MDI AIMS - YL REPORTS & PRINT RENDERING
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    if(document.getElementById('module-YL')) {
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        
        const fromInp = document.getElementById('yl_rep_from');
        const toInp = document.getElementById('yl_rep_to');
        if(fromInp) fromInp.valueAsDate = firstDay;
        if(toInp) toInp.valueAsDate = lastDay;

        const remitDateInp = document.getElementById('yl_remit_date');
        if(remitDateInp) remitDateInp.valueAsDate = new Date();
        
        fetch('api/endpoints.php?table=system_dropdowns').then(r=>r.json()).then(data => {
            const remitCenterSel = document.getElementById('yl_remit_center');
            if(remitCenterSel) {
                remitCenterSel.innerHTML = '<option value="">All Centers</option>';
                data.filter(d => d.Dropdown_Type === 'Center').forEach(c => { 
                    remitCenterSel.innerHTML += `<option value="${c.Option_Value}">${c.Option_Value}</option>`; 
                });
            }
        });
    }
}); // <-- END OF DOMContentLoaded

window.loadYlReport = function() {
    const from = document.getElementById('yl_rep_from').value;
    const to = document.getElementById('yl_rep_to').value;
    if (!from || !to) { alert("Please select a date range."); return; }

    const tbody = document.querySelector('#ylReportTable tbody');
    tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4">Loading report...</td></tr>';

    fetch(`api/endpoints.php?table=yl_sales_report&from=${from}&to=${to}`)
    .then(r => r.json())
    .then(data => {
        tbody.innerHTML = '';
        if (data.length === 0 || data.error) {
            tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-muted font-monospace">No sales found in this date range.</td></tr>';
            document.getElementById('yl_rep_gross').innerText = '₱ 0.00';
            document.getElementById('yl_rep_vat').innerText = '₱ 0.00';
            document.getElementById('yl_rep_paid').innerText = '₱ 0.00';
            document.getElementById('yl_rep_unpaid').innerText = '₱ 0.00';
            return;
        }

        data.forEach(row => {
            const due = parseFloat(row.Amount_Due) || 0;
            const badgeClass = row.Payment_Status === 'Paid' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle';
            const crNo = row.CR_No ? `<span class="badge bg-success-subtle text-success border border-success-subtle">${row.CR_No}</span>` : '<span class="text-muted">-</span>';
            const soNo = `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.SO_No}</span>`;
            const drNos = row.DR_No ? row.DR_No.split(',').map(dr => `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-1">${dr.trim()}</span>`).join('') : '<span class="text-muted">-</span>';
            
            let items = [];
            try { items = JSON.parse(row.Items_JSON || '[]'); } catch(e) {}
            
            if (items.length === 0) {
                tbody.innerHTML += `<tr class="visible-row" data-invid="${row.Invoice_ID}" data-gross="${row.Gross_Sales}" data-vat="${row.VAT}" data-due="${due}" data-status="${row.Payment_Status}">
                    <td>${window.formatDate(row.Invoice_Date)}</td>
                    <td class="fw-bold">${row.Invoice_No}</td>
                    <td>${soNo}</td>
                    <td>${drNos}</td>
                    <td>${crNo}</td>
                    <td class="fw-bold text-dark">${row.Dealer_Name}</td>
                    <td class="text-muted fst-italic">No items</td>
                    <td class="text-center">-</td>
                    <td class="text-end">-</td>
                    <td class="text-end text-primary fw-bold">₱ ${window.formatCurrency(due)}</td>
                    <td class="text-center"><span class="badge ${badgeClass}">${row.Payment_Status || 'Pending'}</span></td>
                </tr>`;
            } else {
                items.forEach((item, index) => {
                    const displayDue = index === 0 ? `₱ ${window.formatCurrency(due)}` : '';
                    const displayStatus = index === 0 ? `<span class="badge ${badgeClass}">${row.Payment_Status || 'Pending'}</span>` : '';
                    tbody.innerHTML += `<tr class="visible-row" data-invid="${row.Invoice_ID}" data-gross="${index === 0 ? row.Gross_Sales : 0}" data-vat="${index === 0 ? row.VAT : 0}" data-due="${index === 0 ? due : 0}" data-status="${index === 0 ? row.Payment_Status : 'Child'}">
                        <td>${window.formatDate(row.Invoice_Date)}</td>
                        <td class="fw-bold text-danger">${row.Invoice_No}</td>
                        <td>${soNo}</td>
                        <td>${drNos}</td>
                        <td>${crNo}</td>
                        <td class="fw-bold text-dark">${row.Dealer_Name}</td>
                        <td class="fw-bold text-dark">${item.product_name}</td>
                        <td class="text-center">${window.formatQuantity(item.quantity)}</td>
                        <td class="text-end">₱ ${window.formatCurrency(item.unit_price)}</td>
                        <td class="text-end text-primary fw-bolder">${displayDue}</td>
                        <td class="text-center">${displayStatus}</td>
                    </tr>`;
                });
            }
        });
        window.recalculateYlReportKPIs();
    });
};

window.recalculateYlReportKPIs = function() {
    let totGross = 0, totVat = 0, totPaid = 0, totUnpaid = 0;
    
    document.querySelectorAll('#ylReportTable tbody tr.visible-row').forEach(row => {
        const gross = parseFloat(row.getAttribute('data-gross')) || 0;
        const vat = parseFloat(row.getAttribute('data-vat')) || 0;
        const due = parseFloat(row.getAttribute('data-due')) || 0;
        const status = row.getAttribute('data-status');
        
        totGross += gross;
        totVat += vat;
        if (status === 'Paid') { totPaid += due; } 
        else if (status !== 'Child') { totUnpaid += due; }
    });

    document.getElementById('yl_rep_gross').innerText = `₱ ${window.formatCurrency(totGross)}`;
    document.getElementById('yl_rep_vat').innerText = `₱ ${window.formatCurrency(totVat)}`;
    document.getElementById('yl_rep_paid').innerText = `₱ ${window.formatCurrency(totPaid)}`;
    document.getElementById('yl_rep_unpaid').innerText = `₱ ${window.formatCurrency(totUnpaid)}`;
};

window.printYlReport = function() {
    const from = document.getElementById('yl_rep_from').value;
    const to = document.getElementById('yl_rep_to').value;
    const totGross = document.getElementById('yl_rep_gross').innerText;
    const totVat = document.getElementById('yl_rep_vat').innerText;
    const totPaid = document.getElementById('yl_rep_paid').innerText;
    const totUnpaid = document.getElementById('yl_rep_unpaid').innerText;

    let table = document.getElementById('ylReportTable');
    let tableClone = table.cloneNode(true);
    
    Array.from(tableClone.querySelectorAll('tbody tr')).forEach(tr => { if (tr.style.display === 'none') tr.remove(); });
    let tableHTML = tableClone.outerHTML;

    let styledTable = tableHTML
        .replace(/<table[^>]*>/g, '<table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px;">')
        .replace(/<th[^>]*>/g, '<th style="border: 1px solid #ddd; padding: 8px; background: #eee; text-align: left;">')
        .replace(/<td[^>]*>/g, '<td style="border: 1px solid #ddd; padding: 8px;">');

    fetch('api/endpoints.php?table=company_profile').then(r=>r.json()).then(comp => {
        let logoHtml = comp && comp.Logo_Path ? `<img src="${comp.Logo_Path}" style="max-height: 80px; margin-bottom: 10px;">` : '';
        let headerHtml = `<div style="text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #333;">${logoHtml}<h2 style="margin: 0; text-transform: uppercase;">${comp ? comp.Company_Name : 'MDI AIMS'}</h2><p style="margin: 5px 0 0 0; color: #555;">${comp ? comp.Address : ''}</p><p style="margin: 0; color: #555;">TIN: ${comp ? comp.TIN : ''} | Contact: ${comp ? comp.Contact_No : ''}</p></div>`;

        let summaryTable = `
            <table style="width: 350px; border-collapse: collapse; margin-top: 30px; font-size: 12px; margin-left: auto;">
                <tr><td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f8f9fa;">Total Gross Sales</td><td style="padding: 8px; border: 1px solid #ddd; text-align: right; font-weight: bold; color: #0d6efd;">${totGross}</td></tr>
                <tr><td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f8f9fa;">Total Output VAT</td><td style="padding: 8px; border: 1px solid #ddd; text-align: right; font-weight: bold; color: #0d6efd;">${totVat}</td></tr>
                <tr><td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f8f9fa;">Total Collected</td><td style="padding: 8px; border: 1px solid #ddd; text-align: right; font-weight: bold; color: #198754;">${totPaid}</td></tr>
                <tr><td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f8f9fa;">Total Unpaid (AR)</td><td style="padding: 8px; border: 1px solid #ddd; text-align: right; font-weight: bold; color: #dc3545;">${totUnpaid}</td></tr>
            </table>
        `;

        let printContent = `<div style="padding: 20px; font-family: Arial, sans-serif; color: #333;">${headerHtml}<h3 style="text-align: center; text-transform: uppercase; margin-bottom: 10px;">Detailed YL Sales Report</h3><p style="text-align: center; color: #666; margin-bottom: 30px;">Period: ${from} to ${to}</p>${styledTable}${summaryTable}</div>`;
        const printWindow = window.open('', '_blank'); 
        printWindow.document.write('<html><head><title>YL Sales Report</title></head><body>' + printContent + '</body></html>'); 
        printWindow.document.close(); printWindow.focus(); 
        setTimeout(() => { printWindow.print(); printWindow.close(); }, 250);
    });
};

window.loadYlRemittanceTable = function() {
    const date = document.getElementById('yl_remit_date').value;
    const center = document.getElementById('yl_remit_center').value;
    if(!date) { alert("Please select a target date."); return; }

    const tbody = document.querySelector('#ylRemittanceTable tbody');
    tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4">Loading data...</td></tr>';

    fetch(`api/endpoints.php?table=yl_remittance_report&date=${date}&center=${center}`)
    .then(r => r.json())
    .then(res => {
        if(res.status !== 'success') {
            tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-danger font-monospace">Error loading remittance.</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        if(res.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-muted font-monospace">No transactions for this date.</td></tr>';
            document.getElementById('remit_tot_add').innerText = '0';
            document.getElementById('remit_tot_sales').innerText = '₱ 0.00';
            document.getElementById('remit_tot_col').innerText = '₱ 0.00';
            document.getElementById('remit_tot_short').innerText = '₱ 0.00';
            document.getElementById('remit_tot_beg_ar').innerText = '₱ 0.00';
            document.getElementById('remit_tot_end_ar').innerText = '₱ 0.00';
            document.getElementById('remit_tot_beg_stock').innerText = '0';
            document.getElementById('remit_tot_end_stock').innerText = '0';
            return;
        }

        let tAdd = 0, tSales = 0, tCol = 0, tShort = 0, tBegAr = 0, tEndAr = 0, tBegStock = 0, tEndStock = 0;

        res.data.forEach(d => {
            tAdd += parseFloat(d.add_stock);
            tSales += parseFloat(d.today_sales);
            tCol += parseFloat(d.today_col);
            tShort += parseFloat(d.short_excess);
            tBegAr += parseFloat(d.beg_ar);
            tEndAr += parseFloat(d.end_ar);
            tBegStock += parseFloat(d.beg_stock);
            tEndStock += parseFloat(d.running_stock);

            const shortColor = parseFloat(d.short_excess) < 0 ? 'text-danger' : (parseFloat(d.short_excess) > 0 ? 'text-primary' : 'text-muted');

            tbody.innerHTML += `<tr>
                <td class="fw-bold text-dark">${d.yl_name}</td>
                <td class="font-monospace text-muted" style="font-size:0.7rem; max-width: 100px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d.dr_nos}">${d.dr_nos}</td>
                <td class="text-end fw-bold">${window.formatQuantity(d.add_stock)}</td>
                <td class="text-end fw-bold text-success">₱ ${window.formatCurrency(d.today_sales)}</td>
                <td class="font-monospace text-muted" style="font-size:0.7rem; max-width: 100px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d.inv_nos}">${d.inv_nos}</td>
                <td class="text-end fw-bold text-primary">₱ ${window.formatCurrency(d.today_col)}</td>
                <td class="text-end fw-bold ${shortColor}">${window.formatCurrency(d.short_excess)}</td>
                <td class="text-end">₱ ${window.formatCurrency(d.beg_ar)}</td>
                <td class="text-end fw-bold text-danger">₱ ${window.formatCurrency(d.end_ar)}</td>
                <td class="text-end">${window.formatQuantity(d.beg_stock)}</td>
                <td class="text-end fw-bold text-info">${window.formatQuantity(d.running_stock)}</td>
            </tr>`;
        });

        document.getElementById('remit_tot_add').innerText = window.formatQuantity(tAdd);
        document.getElementById('remit_tot_sales').innerText = `₱ ${window.formatCurrency(tSales)}`;
        document.getElementById('remit_tot_col').innerText = `₱ ${window.formatCurrency(tCol)}`;
        document.getElementById('remit_tot_short').innerText = `₱ ${window.formatCurrency(tShort)}`;
        document.getElementById('remit_tot_beg_ar').innerText = `₱ ${window.formatCurrency(tBegAr)}`;
        document.getElementById('remit_tot_end_ar').innerText = `₱ ${window.formatCurrency(tEndAr)}`;
        document.getElementById('remit_tot_beg_stock').innerText = window.formatQuantity(tBegStock);
        document.getElementById('remit_tot_end_stock').innerText = window.formatQuantity(tEndStock);
    });
};

window.printYlRemittance = function() {
    const date = document.getElementById('yl_remit_date').value;
    const center = document.getElementById('yl_remit_center').options[document.getElementById('yl_remit_center').selectedIndex].text || 'ALL CENTERS';
    
    let table = document.getElementById('ylRemittanceTable');
    let tableClone = table.cloneNode(true);
    let styledTable = tableClone.outerHTML
        .replace(/<table[^>]*>/g, '<table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px;">')
        .replace(/<th[^>]*>/g, '<th style="border: 1px solid #ddd; padding: 6px; background: #eee; text-align: center;">')
        .replace(/<td[^>]*>/g, '<td style="border: 1px solid #ddd; padding: 6px;">');

    fetch('api/endpoints.php?table=company_profile').then(r=>r.json()).then(comp => {
        let headerHtml = `<div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px;">
            <h3 style="margin: 0; text-transform: uppercase;">${comp ? comp.Company_Name : 'MDI AIMS'}</h3>
            <h4 style="margin: 5px 0;">DAILY YAKULT LADY REMITTANCE REPORT</h4>
            <p style="margin: 0; color: #555;">Date: ${window.formatDate(date)} | Center: ${center}</p>
        </div>`;

        let printContent = `<div style="padding: 10px; font-family: Arial, sans-serif; color: #333;">${headerHtml}${styledTable}</div>`;
        const printWindow = window.open('', '_blank'); 
        printWindow.document.write('<html><head><title>YL Remittance Report</title></head><body>' + printContent + '</body></html>'); 
        printWindow.document.close(); printWindow.focus(); 
        setTimeout(() => { printWindow.print(); printWindow.close(); }, 250);
    });
};