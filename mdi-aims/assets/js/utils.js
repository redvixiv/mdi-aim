// ==========================================
// MDI AIMS - GLOBAL UTILITY FUNCTIONS
// ==========================================

window.formatDate = function(dateString) { 
    if(!dateString || dateString === '0000-00-00') return ''; 
    const [y,m,d] = dateString.split(' ')[0].split('-'); 
    return `${y}-${m}-${d}`; 
};

window.formatCurrency = function(amount) {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

window.formatQuantity = function(qty) {
    const num = parseInt(qty) || 0;
    return num.toLocaleString('en-US');
};

window.numberToWords = function(amount) { 
    const a = ['','ONE','TWO','THREE','FOUR','FIVE','SIX','SEVEN','EIGHT','NINE','TEN','ELEVEN','TWELVE','THIRTEEN','FOURTEEN','FIFTEEN','SIXTEEN','SEVENTEEN','EIGHTEEN','NINETEEN']; 
    const b = ['','','TWENTY','THIRTY','FORTY','FIFTY','SIXTY','SEVENTY','EIGHTY','NINETY']; 
    const toWords = (n) => { 
        if(n<20) return a[n]; if(n<100) return b[Math.floor(n/10)] + (n%10 ? " " + a[n%10] : ""); 
        if(n<1000) return a[Math.floor(n/100)] + " HUNDRED" + (n%100 ? " AND " + toWords(n%100) : ""); 
        if(n<1000000) return toWords(Math.floor(n/1000)) + " THOUSAND" + (n%1000 ? " " + toWords(n%1000) : ""); 
        return ""; 
    }; 
    const whole = Math.floor(amount); const cents = Math.round((amount - whole) * 100); 
    let str = toWords(whole); if(cents > 0) str += ` AND ${cents}/100`; return str || "ZERO"; 
};

window.postData = function(table, data, formElement, modalId, reloadFunction) { 
    if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) {
        alert("You do not have permission to perform this action.");
        return;
    }

    const submitBtn = formElement.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.innerHTML : 'Save';
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    }

    fetch(`api/endpoints.php?table=${table}`, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(data) })
    .then(res=>res.text()).then(text=>{ 
        try { 
            const res=JSON.parse(text); 
            if(res.status==='success'){ 
                formElement.reset(); 
                formElement.querySelectorAll('input[type="hidden"]').forEach(inp => inp.value = '');
                formElement.querySelectorAll('input, select, textarea').forEach(inp => { 
                    inp.value = ''; 
                    inp.removeAttribute('data-edit-val'); 
                });
                
                if (modalId) {
                    const modalEl = document.getElementById(modalId);
                    if (modalEl) {
                        const bsModal = bootstrap.Modal.getInstance(modalEl);
                        if (bsModal) bsModal.hide();
                    }
                }
                
                if(typeof reloadFunction === 'function') reloadFunction(); 
            } else { alert('Error: '+res.message); } 
        } catch(e) { alert("Database Error! Check console."); console.error(text); } 
    }).finally(() => {
        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalText; }
    }); 
};

window.bindPhilLocations = function(provId, cityId, brgyId) { 
    const p=document.getElementById(provId); const c=document.getElementById(cityId); const b=document.getElementById(brgyId); 
    if(!p||!c||!b) return; 
    fetch('https://psgc.gitlab.io/api/provinces/').then(r=>r.json()).then(data=>{ 
        p.innerHTML='<option value="">Select Province...</option><option value="NCR">METRO MANILA (NCR)</option>'; 
        data.sort((a,b)=>a.name.localeCompare(b.name)).forEach(prov=>{ p.innerHTML+=`<option value="${prov.code}">${prov.name}</option>`; }); 
    }); 
    p.addEventListener('change', function(){ 
        c.innerHTML='<option value="">Loading...</option>'; c.disabled=true; b.disabled=true; 
        let ep=this.value==='NCR'?`https://psgc.gitlab.io/api/regions/130000000/cities-municipalities/`:`https://psgc.gitlab.io/api/provinces/${this.value}/cities-municipalities/`; 
        if(this.value){ fetch(ep).then(r=>r.json()).then(data=>{ c.innerHTML='<option value="">Select City/Municipality...</option>'; data.sort((a,b)=>a.name.localeCompare(b.name)).forEach(city=>{ c.innerHTML+=`<option value="${city.code}">${city.name}</option>`; }); c.disabled=false; }); } 
    }); 
    c.addEventListener('change', function(){ 
        b.innerHTML='<option value="">Loading...</option>'; b.disabled=true; 
        if(this.value){ fetch(`https://psgc.gitlab.io/api/cities-municipalities/${this.value}/barangays/`).then(r=>r.json()).then(data=>{ b.innerHTML='<option value="">Select Barangay...</option>'; data.sort((a,b)=>a.name.localeCompare(b.name)).forEach(brgy=>{ b.innerHTML+=`<option value="${brgy.code}">${brgy.name}</option>`; }); b.disabled=false; }); } 
    }); 
};

// ==========================================
// DECOUPLED ACTION ICONS
// ==========================================
window.actionIconsHTML = function(table, id, extra_id = null) { 
    const printTables = ['accounting_pv', 'accounting_gl', 'ds_sales_orders', 'ds_invoices', 'ds_collection_receipts', 'yl_stock_orders', 'yl_delivery_receipts', 'yl_invoices', 'yl_collection_receipts', 'stock_returns', 'purchase_orders', 'goods_receipts'];
    const canPrint = printTables.includes(table);

    if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) {
        if (canPrint) return `<div class="action-icons d-flex justify-content-center gap-3"><i class="bi bi-printer text-primary" title="Print Document" style="cursor:pointer;" onclick="window.mdiPrintDocument('${table}', ${id})"></i></div>`;
        return `<div class="action-icons d-flex justify-content-center gap-3"><span class="badge bg-light text-muted border">LOCKED</span></div>`; 
    }

    let icons = `<div class="action-icons d-flex justify-content-center gap-3">`;
    if (table === 'customers') icons += `<i class="bi bi-plus-circle text-info" title="Manage Outlets" onclick="addOutlet(${id})"></i>`;
    if (canPrint) icons += `<i class="bi bi-printer text-primary" title="Print Document" style="cursor:pointer;" onclick="window.mdiPrintDocument('${table}', ${id})"></i>`;
    
    if (table === 'outlets' || table === 'outlets_global') {
        icons += `<i class="bi bi-trash text-danger" title="Delete Outlet" onclick="deleteRecord('${table}', ${id}, ${extra_id})"></i>`;
    } else {
        icons += `<i class="bi bi-trash text-danger" title="Delete" onclick="deleteRecord('${table}', ${id})"></i>`;
    }
    
    icons += `</div>`;
    return icons;
};

// ==========================================
// MASTER UNIFIED PRINT LAYOUT SYSTEM
// ==========================================
window.mdiPrintDocument = function(table, id) {
    const printWindow = window.open('', '_blank'); 
    if(!printWindow) { alert("Please allow pop-ups for this site to print."); return; }
    printWindow.document.write('<html><head><title>Loading...</title></head><body style="text-align:center; margin-top:50px; font-family:Arial,sans-serif;"><h3>Generating Document...</h3></body></html>');

    fetch('api/endpoints.php?table=company_profile').then(r=>r.json()).then(comp => {
        const cp = comp && comp.length > 0 ? comp[0] : null;
        let logoHtml = cp && cp.Logo_Path ? `<img src="${cp.Logo_Path}" style="max-height: 80px; margin-bottom: 5px;">` : '';
        let compName = cp ? cp.Company_Name : 'MERCHANDISE, DISTRIBUTORS, INC.';
        let compAddress = cp && cp.Address ? `${cp.Address}, ${cp.City || ''}`.replace(/, ,/g, ',').replace(/,\s*$/, '') : '2108 MF Echivarre Street';
        let compContact = `TIN: ${cp ? cp.TIN : '000-310-014-00000'} | Contact: ${cp ? cp.Contact_No : '(032) 346 8641'}`;
        
        let headerHtml = `
            <div style="text-align: left; font-size: 10px; color: #555; position: absolute; top: 20px; left: 20px; text-transform: uppercase;">
                ${table.includes('yl_') ? 'YL' : 'MDI AIMS System'}
            </div>
            <div style="text-align: right; font-size: 10px; color: #555; position: absolute; top: 20px; right: 20px;">
                MDI AIMS System
            </div>
            <div style="text-align: center; margin-top: 40px; margin-bottom: 20px;">
                ${logoHtml}
                <h3 style="margin: 0; font-size: 18px; color: #1e3a8a; font-family: Arial, sans-serif;">${compName}</h3>
                <p style="margin: 3px 0 0; font-size: 12px; color: #555; font-family: Arial, sans-serif;">
                    ${compAddress}<br>${compContact}
                </p>
            </div>`;

        const directFetchTables = ['yl_stock_orders', 'ds_sales_orders', 'purchase_orders', 'goods_receipts', 'yl_delivery_receipts', 'stock_returns', 'yl_stock_returns', 'yl_invoices', 'ds_invoices', 'yl_collection_receipts', 'ds_collection_receipts'];
        
        let url = `api/endpoints.php?table=print_data&type=${table}&id=${id}`;
        if (directFetchTables.includes(table)) {
            let actualTable = table === 'stock_returns' ? 'yl_stock_returns' : table;
            url = `api/endpoints.php?table=${actualTable}`;
        }

        fetch(url).then(r=>r.json()).then(data => {
            if (Array.isArray(data)) {
                let pk = 'id';
                if (table.includes('stock_orders') || table.includes('sales_orders')) pk = 'SO_ID';
                else if (table === 'purchase_orders') pk = 'PO_ID';
                else if (table === 'goods_receipts') pk = 'Receipt_ID';
                else if (table === 'yl_delivery_receipts') pk = 'DR_ID';
                else if (table.includes('stock_returns')) pk = 'Return_ID';
                else if (table.includes('invoices')) pk = 'Invoice_ID';
                else if (table.includes('collection_receipts')) pk = 'CR_ID';
                
                const target = data.find(o => o[pk] == id);
                if (!target) { printWindow.close(); alert('Record not found.'); return; }
                data = target; 
            } else if (!data || data.status === 'error') {
                printWindow.close(); alert('Record not found.'); return; 
            }
            
            let itemsArray = data.items || [];
            if (itemsArray.length === 0 && data.Items_JSON && data.Items_JSON !== 'null') {
                try { itemsArray = JSON.parse(data.Items_JSON); } catch(e) {}
            }
            if (typeof itemsArray === 'string') {
                try { itemsArray = JSON.parse(itemsArray); } catch(e) { itemsArray = []; }
            }

            let docTitle = 'DOCUMENT';
            let metaHtml = '';
            let tableHtml = '';
            let sigLeft = 'Prepared By';
            let sigRight = 'Authorized Signature';

            if (table === 'yl_stock_orders' || table === 'ds_sales_orders') {
                let isDS = table === 'ds_sales_orders';
                docTitle = isDS ? 'SALES ORDER' : 'STOCK ORDER SLIP (YL)';
                let clientLabel = isDS ? 'Outlet Name:' : 'Dealer Name:';
                let clientName = isDS ? data.Outlet_Name : `${data.First_Name || ''} ${data.Last_Name || ''}`;
                let dateField = data.SO_Date;
                
                let dealerArea = "UNASSIGNED";
                if (!isDS && window.globalDealersList) {
                    const dealer = window.globalDealersList.find(d => d.Dealer_ID == data.Dealer_ID);
                    if (dealer && dealer.Area) dealerArea = dealer.Area;
                }
                let areaHtml = isDS ? '' : `<br><strong style="color: #000;">Dealer Area:</strong> <span style="color: #000;">${dealerArea}</span>`;

                metaHtml = `<div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 12px; font-family: Arial, sans-serif;">
                                <div style="line-height: 1.6;">
                                    <strong style="color: #000;">${clientLabel}</strong> <span style="color: #000;">${clientName}</span>
                                    ${areaHtml}
                                </div>
                                <div style="text-align: right; line-height: 1.6;">
                                    <strong style="color: #000;">SO No:</strong> <span style="color: #000;">${data.SO_No}</span><br>
                                    <strong style="color: #000;">Date:</strong> <span style="color: #000;">${window.formatDate(dateField)}</span>
                                </div>
                            </div>`;
                
                let lines = '';
                if(itemsArray && itemsArray.length > 0) {
                    itemsArray.forEach(l => {
                        let cName = l.product_name ? l.product_name.replace(/^\[.*?\]\s*/, '').replace(/\s*\([ \$]?\s*\d[\d,]*(\.\d+)?\)$/, '') : (l.Description || 'Unknown Product');
                        let price = parseFloat(l.unit_price) || parseFloat(l.unit_cost) || 0; 
                        let qty = parseInt(l.quantity) || parseInt(l.Qty) || 0;
                        lines += `<tr>
                            <td style="border: 1px solid #000; padding: 6px; color: #000;">${cName}</td>
                            <td style="border: 1px solid #000; padding: 6px; text-align: center; color: #000;">${window.formatQuantity(qty)}</td>
                            <td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(price)}</td>
                            <td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(qty * price)}</td>
                        </tr>`;
                    });
                }
                
                tableHtml = `
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;">
                        <thead>
                            <tr style="background-color: #fff;">
                                <th style="border: 1px solid #000; padding: 6px; text-align: left; color: #000;">PRODUCT</th>
                                <th style="border: 1px solid #000; padding: 6px; text-align: center; width: 60px; color: #000;">QTY</th>
                                <th style="border: 1px solid #000; padding: 6px; text-align: right; width: 100px; color: #000;">UNIT PRICE</th>
                                <th style="border: 1px solid #000; padding: 6px; text-align: right; width: 120px; color: #000;">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>${lines}</tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="border: 1px solid #000; padding: 6px;"></td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">TOTAL QTY:</td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">${window.formatQuantity(data.Total_Quantity)}</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="border: 1px solid #000; padding: 6px; text-align: left; font-style: italic; color: #000;">
                                    AMOUNT IN WORDS: <b style="text-transform: uppercase;">${window.numberToWords(data.Total_Amount)} PESOS</b>
                                </td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">TOTAL<br>AMOUNT:</td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000; vertical-align: bottom;"> ${window.formatCurrency(data.Total_Amount)}</td>
                            </tr>
                        </tfoot>
                    </table>
                `;
                sigLeft = 'Prepared By'; sigRight = isDS ? 'Client Signature' : 'Dealer Signature';
            }
            
            else if (table === 'purchase_orders') {
                docTitle = 'PURCHASE ORDER';
                metaHtml = `<div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 12px; font-family: Arial, sans-serif;"><div style="line-height: 1.6;"><strong style="color: #000;">Supplier Name:</strong> <span style="color: #000;">${data.Supplier_Name || 'N/A'}</span><br><strong style="color: #000;">Warehouse:</strong> <span style="color: #000;">${data.Warehouse_Name || 'MDI'}</span></div><div style="text-align: right; line-height: 1.6;"><strong style="color: #000;">PO No:</strong> <span style="color: #000;">${data.PO_No}</span><br><strong style="color: #000;">Date:</strong> <span style="color: #000;">${window.formatDate(data.PO_Date)}</span></div></div>`;
                
                let lines = '';
                if(itemsArray && itemsArray.length > 0) {
                    itemsArray.forEach(l => {
                        let cost = parseFloat(l.unit_cost) || 0; let qty = parseInt(l.quantity) || 0;
                        let cName = l.product_name ? l.product_name.replace(/^\[.*?\]\s*/, '').replace(/\s*\([ \$]?\s*\d[\d,]*(\.\d+)?\)$/, '') : 'Unknown Product';
                        lines += `<tr><td style="border: 1px solid #000; padding: 6px; color: #000;">${cName}</td><td style="border: 1px solid #000; padding: 6px; text-align: center; color: #000;">${window.formatQuantity(qty)}</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(cost)}</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(qty * cost)}</td></tr>`;
                    });
                }
                
                tableHtml = `<table style="width: 100%; border-collapse: collapse; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><thead><tr style="background-color: #fff;"><th style="border: 1px solid #000; padding: 6px; text-align: left; color: #000;">PRODUCT</th><th style="border: 1px solid #000; padding: 6px; text-align: center; width: 60px; color: #000;">QTY</th><th style="border: 1px solid #000; padding: 6px; text-align: right; width: 100px; color: #000;">UNIT COST</th><th style="border: 1px solid #000; padding: 6px; text-align: right; width: 120px; color: #000;">AMOUNT</th></tr></thead><tbody>${lines}</tbody><tfoot><tr><td colspan="2" style="border: 1px solid #000; padding: 6px; text-align: left; font-style: italic; color: #000;">AMOUNT IN WORDS: <b style="text-transform: uppercase;">${window.numberToWords(data.Total_Amount)} PESOS</b></td><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">TOTAL<br>AMOUNT:</td><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000; vertical-align: bottom;"> ${window.formatCurrency(data.Total_Amount)}</td></tr></tfoot></table>`;
                sigLeft = 'Purchasing Officer'; sigRight = 'Manager Approval';
            }
            
            else if (table === 'goods_receipts') {
                docTitle = 'GOODS RECEIPT (DR)';
                metaHtml = `<div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 12px; font-family: Arial, sans-serif;"><div style="line-height: 1.6;"><strong style="color: #000;">Forwarder:</strong> <span style="color: #000;">${data.Forwarder || 'N/A'} (Seal: ${data.Seal_No || 'N/A'})</span><br><strong style="color: #000;">PO Reference:</strong> <span style="color: #000;">${data.PO_No || 'N/A'}</span></div><div style="text-align: right; line-height: 1.6;"><strong style="color: #000;">DR No:</strong> <span style="color: #000;">${data.DR_No}</span><br><strong style="color: #000;">Arrival Date:</strong> <span style="color: #000;">${window.formatDate(data.Arrival_Date)}</span></div></div>`;
                
                let lines = '';
                if(itemsArray && itemsArray.length > 0) {
                    itemsArray.forEach(l => {
                        let cost = parseFloat(l.unit_cost) || 0; let qty = parseInt(l.quantity) || 0;
                        let cName = l.product_name ? l.product_name.replace(/^\[.*?\]\s*/, '').replace(/\s*\([ \$]?\s*\d[\d,]*(\.\d+)?\)$/, '') : 'Unknown Product';
                        lines += `<tr><td style="border: 1px solid #000; padding: 6px; color: #000;">${cName}</td><td style="border: 1px solid #000; padding: 6px; text-align: center; color: #000;">${window.formatQuantity(qty)}</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(cost)}</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(qty * cost)}</td></tr>`;
                    });
                }
                tableHtml = `<table style="width: 100%; border-collapse: collapse; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><thead><tr style="background-color: #fff;"><th style="border: 1px solid #000; padding: 6px; text-align: left; color: #000;">PRODUCT RECEIVED</th><th style="border: 1px solid #000; padding: 6px; text-align: center; width: 80px; color: #000;">ACTUAL QTY</th><th style="border: 1px solid #000; padding: 6px; text-align: right; width: 100px; color: #000;">UNIT COST</th><th style="border: 1px solid #000; padding: 6px; text-align: right; width: 120px; color: #000;">SUBTOTAL</th></tr></thead><tbody>${lines}</tbody><tfoot><tr><td colspan="2" style="border: 1px solid #000; padding: 6px;"></td><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">TOTAL RECEIVED:</td><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">${window.formatQuantity(data.Total_Received)} ITEMS</td></tr></tfoot></table>`;
                sigLeft = 'Checked By (Checker)'; sigRight = 'Verified By (Warehouseman)';
            }
            
            else if (table === 'yl_delivery_receipts') {
                docTitle = 'DELIVERY RECEIPT (YL)';
                metaHtml = `<div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 12px; font-family: Arial, sans-serif;"><div style="line-height: 1.6;"><strong style="color: #000;">DR No:</strong> <span style="color: #000;">${data.DR_No}</span><br><strong style="color: #000;">Date:</strong> <span style="color: #000;">${window.formatDate(data.DR_Date)}</span></div><div style="text-align: right; line-height: 1.6;"><strong style="color: #000;">Dealer:</strong> <span style="color: #000;">${data.First_Name || ''} ${data.Last_Name || ''}</span><br><strong style="color: #000;">SO Ref:</strong> <span style="color: #000;">${data.SO_No}</span></div></div>`;
                
                let lines = '';
                if(itemsArray && itemsArray.length > 0) {
                    itemsArray.forEach(l => {
                        let cName = l.product_name ? l.product_name.replace(/^\[.*?\]\s*/, '').replace(/\s*\([ \$]?\s*\d[\d,]*(\.\d+)?\)$/, '') : 'Unknown Product';
                        let price = parseFloat(l.unit_price) || 0; let qty = parseInt(l.quantity) || 0;
                        lines += `<tr><td style="border: 1px solid #000; padding: 6px; color: #000;">${cName}</td><td style="border: 1px solid #000; padding: 6px; text-align: center; color: #000;">${window.formatQuantity(qty)}</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(price)}</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(qty * price)}</td></tr>`;
                    });
                } else {
                    lines = `<tr><td style="border: 1px solid #000; padding: 6px; height: 100px; vertical-align: top; color: #000;" colspan="4">Delivery of stock corresponding to Stock Order ${data.SO_No}. Please check items upon receipt.</td></tr>`;
                }
                
                tableHtml = `<table style="width: 100%; border-collapse: collapse; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><thead><tr style="background-color: #fff;"><th style="border: 1px solid #000; padding: 6px; text-align: left; color: #000;">PRODUCT</th><th style="border: 1px solid #000; padding: 6px; text-align: center; width: 60px; color: #000;">QTY</th><th style="border: 1px solid #000; padding: 6px; text-align: right; width: 100px; color: #000;">UNIT PRICE</th><th style="border: 1px solid #000; padding: 6px; text-align: right; width: 120px; color: #000;">AMOUNT</th></tr></thead><tbody>${lines}</tbody><tfoot><tr><td colspan="2" style="border: 1px solid #000; padding: 6px;"></td><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">TOTAL QTY:</td><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">${window.formatQuantity(data.Total_Quantity)} ITEMS</td></tr></tfoot></table>`;
                sigLeft = 'Prepared / Released By'; sigRight = 'Received By (Dealer Signature)';
            }
            
            else if (table === 'ds_invoices' || table === 'yl_invoices') {
                let isDS = table === 'ds_invoices';
                let clientLabel = isDS ? 'Outlet:' : 'Dealer:';
                let clientName = isDS ? data.Outlet_Name : (data.Dealer_Name_Display || `${data.First_Name || ''} ${data.Last_Name || ''}`);
                let refText = isDS ? `SO Ref: ${data.SO_No}` : `DR Ref: ${data.DR_Nos_Display || data.DR_No}`;
                
                docTitle = 'SALES INVOICE';
                if (isDS && data.Terms) {
                    let t = data.Terms.toString().toUpperCase();
                    if (t.includes('COD') || t.includes('CASH')) docTitle = 'CASH SALES INVOICE';
                    else if (t.includes('7') || t.includes('15') || t.includes('30') || t.includes('CHARGE')) docTitle = 'CHARGE SALES INVOICE';
                }

                metaHtml = `<div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 12px; font-family: Arial, sans-serif;"><div style="line-height: 1.6;"><strong style="color: #000;">Inv No:</strong> <span style="color: #000;">${data.Invoice_No}</span><br><strong style="color: #000;">Date:</strong> <span style="color: #000;">${window.formatDate(data.Invoice_Date)}</span></div><div style="text-align: right; line-height: 1.6;"><strong style="color: #000;">${clientLabel}</strong> <span style="color: #000;">${clientName}</span><br><strong style="color: #000;">${refText}</strong></div></div>`;
                
                let lines = ''; let grossAmt = 0;
                if(itemsArray && itemsArray.length > 0) { 
                    itemsArray.forEach(l => { 
                        let cName = l.product_name ? l.product_name.replace(/^\[.*?\]\s*/, '').replace(/\s*\([ \$]?\s*\d[\d,]*(\.\d+)?\)$/, '') : '';
                        let sub = (l.quantity * l.unit_price) || 0;
                        grossAmt += sub;
                        lines += `<tr><td style="border-left: 1px solid #000; border-right: 1px solid #000; padding: 5px 8px;">${cName} <span style="color:#555; font-size:10px;">(${window.formatQuantity(l.quantity)} @  ${window.formatCurrency(l.unit_price)})</span></td><td style="border-left: 1px solid #000; border-right: 1px solid #000; padding: 5px 8px; text-align: right;"> ${window.formatCurrency(sub)}</td></tr>`; 
                    }); 
                } else {
                    lines = `<tr><td style="border: 1px solid #000; padding: 6px; height: 80px; vertical-align: top; color: #000;">Invoice based on ${refText}.</td><td style="border: 1px solid #000; padding: 6px; height: 80px; text-align: right;"></td></tr>`;
                }

                let baseTotalSales = grossAmt > 0 ? grossAmt : parseFloat(data.Net_Amount) + parseFloat(data.VAT) + parseFloat(data.Discount_Amount || 0) + parseFloat(data.Discount_Orig_Amount || 0) + parseFloat(data.Discount_Light_Amount || 0) + parseFloat(data.Trade_Orig_Amount || 0) + parseFloat(data.Trade_Light_Amount || 0);

                let discRow = '';
                if (isDS && parseFloat(data.Discount_Amount) > 0) {
                    discRow = `<tr><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">LESS DISCOUNT</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(data.Discount_Amount)}</td></tr>`;
                } else if (!isDS) {
                    if (parseFloat(data.Discount_Orig_Amount) > 0) discRow += `<tr><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">LESS DEALER'S DISCOUNT (Orig)</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(data.Discount_Orig_Amount)}</td></tr>`;
                    if (parseFloat(data.Discount_Light_Amount) > 0) discRow += `<tr><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">LESS DEALER'S DISCOUNT (Light)</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(data.Discount_Light_Amount)}</td></tr>`;
                    if (parseFloat(data.Trade_Orig_Amount) > 0) discRow += `<tr><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">LESS TRADE DISCOUNT (Orig)</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(data.Trade_Orig_Amount)}</td></tr>`;
                    if (parseFloat(data.Trade_Light_Amount) > 0) discRow += `<tr><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">LESS TRADE DISCOUNT (Light)</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(data.Trade_Light_Amount)}</td></tr>`;
                }

                let ewtRow = (parseFloat(data.EWT_Amount) > 0) ? `<tr><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">LESS EWT</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(data.EWT_Amount)}</td></tr>` : '';

                tableHtml = `<table style="width: 100%; border-collapse: collapse; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><thead><tr style="background-color: #fff;"><th style="border: 1px solid #000; padding: 6px; text-align: left; color: #000;">PARTICULARS</th><th style="border: 1px solid #000; padding: 6px; text-align: right; width: 120px; color: #000;">AMOUNT</th></tr></thead><tbody>${lines}<tr><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">TOTAL SALES</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(baseTotalSales)}</td></tr>${discRow}<tr><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">AMOUNT NET OF VAT</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(data.Net_Amount)}</td></tr><tr><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">ADD VAT (12%)</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(data.VAT)}</td></tr>${ewtRow}</tbody><tfoot><tr><td style="border: 1px solid #000; padding: 8px 6px; text-align: left; font-style: italic; color: #000;">Amount in Words: <strong>${window.numberToWords(data.Amount_Due)} PESOS</strong></td><td style="border: 1px solid #000; padding: 8px 6px; text-align: right; font-weight: bold; color: #000;">TOTAL DUE:<br> ${window.formatCurrency(data.Amount_Due)}</td></tr></tfoot></table>`;
                sigLeft = 'Prepared By'; sigRight = 'Client Signature / Date';
            }
            
            else if (table === 'ds_collection_receipts' || table === 'yl_collection_receipts') {
                let isDS = table === 'ds_collection_receipts';
                docTitle = 'COLLECTION RECEIPT';
                let clientLabel = isDS ? 'Received From:' : 'Received From Dealer:';
                let clientName = isDS ? data.Outlet_Name : data.Dealer_Name;

                metaHtml = `<div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 12px; font-family: Arial, sans-serif;"><div style="line-height: 1.6;"><strong style="color: #000;">CR No:</strong> <span style="color: #000;">${data.CR_No}</span><br><strong style="color: #000;">Date:</strong> <span style="color: #000;">${window.formatDate(data.CR_Date)}</span></div></div><table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><tr><td style="border: 1px solid #000; padding: 6px; font-weight: bold; width: 25%; color: #000;">THE SUM OF:</td><td style="border: 1px solid #000; padding: 6px; font-style: italic; color: #000;">${data.Total_Amount_Words || window.numberToWords(data.Total_Amount_Due) + ' PESOS'}</td></tr><tr><td style="border: 1px solid #000; padding: 6px; font-weight: bold; color: #000;">${clientLabel}</td><td style="border: 1px solid #000; padding: 6px; color: #000;">${clientName}</td></tr></table>`;
                tableHtml = `<table style="width: 100%; border-collapse: collapse; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><thead><tr style="background-color: #fff;"><th style="border: 1px solid #000; padding: 6px; text-align: left; color: #000;">PAYMENT APPLIED TO INVOICES</th><th style="border: 1px solid #000; padding: 6px; text-align: right; width: 120px; color: #000;">AMOUNT COLLECTED</th></tr></thead><tbody><tr><td style="border: 1px solid #000; padding: 6px; height: 80px; vertical-align: top; color: #000;">Settlement for Invoice(s) covered by this receipt.</td><td style="border: 1px solid #000; padding: 6px; height: 80px; vertical-align: top; text-align: right; font-weight: bold; color: #000;"> ${window.formatCurrency(data.Total_Amount_Due)}</td></tr></tbody></table>`;
                sigLeft = 'Cashier / Authorized Signatory'; sigRight = '';
            }
            
            else if (table === 'stock_returns' || table === 'yl_stock_returns') {
                docTitle = 'STOCK RETURN SLIP';
                metaHtml = `<div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 12px; font-family: Arial, sans-serif;"><div style="line-height: 1.6;"><strong style="color: #000;">Return No:</strong> <span style="color: #000;">${data.Return_No}</span><br><strong style="color: #000;">Date:</strong> <span style="color: #000;">${window.formatDate(data.Return_Date)}</span></div><div style="text-align: right; line-height: 1.6;"><strong style="color: #000;">Type:</strong> <span style="color: #000;">${data.Return_Type}</span><br><strong style="color: #000;">Reference No:</strong> <span style="color: #000;">${data.Reference_No || 'N/A'}</span></div></div><table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><tr><td style="border: 1px solid #000; padding: 6px; font-weight: bold; width: 15%; color: #000;">WAREHOUSE:</td><td style="border: 1px solid #000; padding: 6px; color: #000;">${data.Warehouse_Name || 'Main Warehouse'}</td></tr></table>`;
                
                let lines = ''; let totalVal = 0;
                itemsArray.forEach(l => {
                    let p = parseFloat(l.unit_price) || 0; let s = parseFloat(l.subtotal) || 0; totalVal += s;
                    let cName = l.product_name ? l.product_name.replace(/^\[.*?\]\s*/, '').replace(/\s*\([ \$]?\s*\d[\d,]*(\.\d+)?\)$/, '') : '';
                    lines += `<tr><td style="border: 1px solid #000; padding: 6px; color: #000;">${cName}</td><td style="border: 1px solid #000; padding: 6px; text-align: center; color: #000;">${window.formatQuantity(l.quantity)}</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(p)}</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;"> ${window.formatCurrency(s)}</td><td style="border: 1px solid #000; padding: 6px; text-align: center; color: #000;">${l.condition || '-'}</td></tr>`; 
                });
                
                tableHtml = `<table style="width: 100%; border-collapse: collapse; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><thead><tr style="background-color: #fff;"><th style="border: 1px solid #000; padding: 6px; text-align: left; color: #000;">PRODUCT</th><th style="border: 1px solid #000; padding: 6px; text-align: center; width: 60px; color: #000;">QTY</th><th style="border: 1px solid #000; padding: 6px; text-align: right; width: 80px; color: #000;">UNIT PRICE</th><th style="border: 1px solid #000; padding: 8px; text-align: right; width: 90px; color: #000;">SUBTOTAL</th><th style="border: 1px solid #000; padding: 6px; text-align: center; width: 100px; color: #000;">CONDITION</th></tr></thead><tbody>${lines}</tbody><tfoot><tr><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;" colspan="3">TOTAL REFUND</td><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;"> ${window.formatCurrency(totalVal)}</td><td style="border: 1px solid #000; padding: 6px;"></td></tr><tr><td colspan="5" style="border: 1px solid #000; padding: 6px; text-align: left; font-style: italic; color: #000;">Remarks: <strong>${data.Remarks || 'None'}</strong></td></tr></tfoot></table>`;
                sigLeft = 'Received By (Warehouse)'; sigRight = 'Returned By (Client / Driver)';
            }
            
            else if (table === 'accounting_pv') {
                docTitle = 'PAYMENT VOUCHER';
                metaHtml = `<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><tr><td style="border: 1px solid #000; padding: 6px; width: 15%; font-weight: bold; color: #000;">PV NO:</td><td style="border: 1px solid #000; padding: 6px; width: 35%; color: #000;">${data.PV_No}</td><td style="border: 1px solid #000; padding: 6px; width: 15%; font-weight: bold; text-align: right; color: #000;">DATE:</td><td style="border: 1px solid #000; padding: 6px; width: 35%; color: #000;">${formatDate(data.PV_Date)}</td></tr><tr><td style="border: 1px solid #000; padding: 6px; font-weight: bold; color: #000;">PAYEE:</td><td style="border: 1px solid #000; padding: 6px; color: #000;">${data.Supplier_Name}</td><td style="border: 1px solid #000; padding: 6px; font-weight: bold; text-align: right; color: #000;">METHOD:</td><td style="border: 1px solid #000; padding: 6px; color: #000;">${data.Payment_Method} ${data.Check_No ? '('+data.Check_No+')' : ''}</td></tr></table>`;
                tableHtml = `<table style="width: 100%; border-collapse: collapse; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><thead><tr style="background-color: #fff;"><th style="border: 1px solid #000; padding: 6px; text-align: left; color: #000;">PARTICULARS</th><th style="border: 1px solid #000; padding: 6px; text-align: right; width: 120px; color: #000;">AMOUNT</th></tr></thead><tbody><tr><td style="border: 1px solid #000; padding: 6px; height: 100px; vertical-align: top; color: #000;">Payment for AP Reference: <strong>${data.AP_Ref}</strong><br><br>Remarks: ${data.Remarks || 'N/A'}</td><td style="border: 1px solid #000; padding: 6px; height: 100px; vertical-align: top; text-align: right; font-weight: bold; color: #000;"> ${window.formatCurrency(data.Amount)}</td></tr></tbody></table>`;
                sigLeft = 'Prepared By'; sigRight = 'Approved By';
            }
            
            else if (table === 'accounting_gl') {
                docTitle = 'JOURNAL VOUCHER';
                metaHtml = `<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><tr><td style="border: 1px solid #000; padding: 6px; width: 15%; font-weight: bold; color: #000;">JOURNAL ID:</td><td style="border: 1px solid #000; padding: 6px; width: 35%; color: #000;">JE-${data.Journal_ID}</td><td style="border: 1px solid #000; padding: 6px; width: 15%; font-weight: bold; text-align: right; color: #000;">DATE:</td><td style="border: 1px solid #000; padding: 6px; width: 35%; color: #000;">${formatDate(data.Journal_Date)}</td></tr><tr><td style="border: 1px solid #000; padding: 6px; font-weight: bold; color: #000;">REFERENCE:</td><td style="border: 1px solid #000; padding: 6px; color: #000;">${data.Reference_No || 'N/A'}</td><td style="border: 1px solid #000; padding: 6px; font-weight: bold; text-align: right; color: #000;">MEMO:</td><td style="border: 1px solid #000; padding: 6px; color: #000;">${data.Description}</td></tr></table>`;
                
                let linesHtml = ''; let tDeb = 0; let tCred = 0;
                if(data.lines && data.lines.length > 0) { 
                    data.lines.forEach(l => { tDeb += parseFloat(l.Debit); tCred += parseFloat(l.Credit); linesHtml += `<tr><td style="border: 1px solid #000; padding: 6px; color: #000;">${l.Account_Code} - ${l.Account_Name}</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;">${parseFloat(l.Debit) > 0 ? ' ' + window.formatCurrency(l.Debit) : '-'}</td><td style="border: 1px solid #000; padding: 6px; text-align: right; color: #000;">${parseFloat(l.Credit) > 0 ? ' ' + window.formatCurrency(l.Credit) : '-'}</td></tr>`; }); 
                }
                tableHtml = `<table style="width: 100%; border-collapse: collapse; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><thead><tr style="background-color: #fff;"><th style="border: 1px solid #000; padding: 6px; text-align: left; color: #000;">ACCOUNT</th><th style="border: 1px solid #000; padding: 6px; text-align: right; width: 120px; color: #000;">DEBIT</th><th style="border: 1px solid #000; padding: 6px; text-align: right; width: 120px; color: #000;">CREDIT</th></tr></thead><tbody>${linesHtml}</tbody><tfoot><tr><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;">TOTAL</td><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;"> ${window.formatCurrency(tDeb)}</td><td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; color: #000;"> ${window.formatCurrency(tCred)}</td></tr></tfoot></table>`;
                sigLeft = 'Prepared By'; sigRight = 'Approved By';
            }
            
            else {
                let metaKeys = '';
                for (let key in data) {
                    if (typeof data[key] !== 'object' && data[key] !== null && key !== 'Items_JSON') {
                        metaKeys += `<tr><td style="border: 1px solid #000; padding: 6px; font-weight: bold; width: 30%; background-color: #f8f9fa; color: #000;">${key.replace(/_/g, ' ').toUpperCase()}</td><td style="border: 1px solid #000; padding: 6px; color: #000;">${data[key]}</td></tr>`;
                    }
                }
                tableHtml = `<table style="width: 100%; border-collapse: collapse; font-size: 12px; font-family: Arial, sans-serif; border: 1px solid #000;"><tbody>${metaKeys}</tbody></table>`;
            }

            let finalHtml = `
                <div style="font-family: Arial, sans-serif; color: #000; max-width: 800px; margin: 0 auto; background: #fff; padding: 20px;">
                    <div style="text-align: left; font-size: 10px; color: #555; position: absolute; top: 20px; left: 20px; text-transform: uppercase;">
                        MDI AIMS System
                    </div>
                    <div style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
                        ${logoHtml}
                        <h3 style="margin: 0; font-size: 16px; font-weight: bold; color: #1e3a8a;">${compName}</h3>
                        <p style="margin: 3px 0 0; font-size: 11px; color: #555;">
                            ${compAddress}<br>${compContact}
                        </p>
                    </div>
                    <div style="border-top: 2px solid #000; border-bottom: 2px solid #000; margin: 15px 0; padding: 10px 0; text-align: center;">
                        <h4 style="margin: 0; font-weight: bold; font-family: Arial, sans-serif; font-size: 18px; text-transform: uppercase; color: #000;">
                            ${docTitle}
                        </h4>
                    </div>
                    ${metaHtml}
                    ${tableHtml}
                    <div style="margin-top: 50px; display: flex; justify-content: space-between; font-size: 11px; font-family: Arial, sans-serif; color: #000;">
                        <div style="width: 35%; text-align: center;">
                            <div style="border-bottom: 1px solid #000; margin-bottom: 5px;"></div>
                            <span>${sigLeft}</span>
                        </div>
                        <div style="width: 35%; text-align: center;">
                            <div style="border-bottom: 1px solid #000; margin-bottom: 5px;"></div>
                            <span>${sigRight}</span>
                        </div>
                    </div>
                </div>
            `;

            printWindow.document.open();
            printWindow.document.write('<html><head><title>Print Document</title><style>@page { size: auto; margin: 0mm; } body { margin: 1cm; }</style></head><body style="margin:0; padding:0; background:#fff;">' + finalHtml + '</body></html>');
            printWindow.document.close();
            
            let img = printWindow.document.querySelector('img');
            if(img) {
                img.onload = () => { setTimeout(() => { printWindow.print(); printWindow.close(); }, 250); };
                img.onerror = () => { setTimeout(() => { printWindow.print(); printWindow.close(); }, 250); };
            } else {
                setTimeout(() => { printWindow.print(); printWindow.close(); }, 250);
            }

        }).catch(err => {
            console.error(err);
            printWindow.close();
            alert("Error fetching document data.");
        });

    }).catch(err => {
        console.error(err);
        printWindow.close();
        alert("Error fetching company profile.");
    });
};

window.deleteRecord = function(table, id, extra_id = null) { 
    const dbTable = (table === 'outlets_global') ? 'outlets' : table; 
    if (confirm("Are you sure you want to delete this record?")) { 
        fetch(`api/endpoints.php?table=${dbTable}&id=${id}`, { method: 'DELETE' }).then(r => r.json()).then(res => { 
            if (res.status === 'success') { location.reload(); } 
        }); 
    } 
};

window.loadGlobalProducts = function() { 
    fetch('api/endpoints.php?table=products')
    .then(r => r.json())
    .then(data => {
        window.globalProductsList = data || [];
    });
}; 

window.exportTableToCSV = function(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    let csv = [];
    const rows = table.querySelectorAll('tr');
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        if(rows[i].style.display === 'none') continue;
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
            data = data.replace(/"/g, '""');
            if(data.includes('ACTIONS') || data.includes('Actions') || cols[j].innerHTML.includes('bi-pencil') || cols[j].innerHTML.includes('bi-trash')) continue;
            row.push('"' + data + '"');
        }
        if(row.length > 0) csv.push(row.join(','));
    }
    const csvFile = new Blob([csv.join('\n')], {type: 'text/csv'});
    const downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
};