// ==========================================
// MDI AIMS - YL INVOICES, COLLECTIONS & REBATES
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    
    // EXPLICIT MODAL RESETS TO PREVENT GHOSTING
    document.getElementById('ylInvoiceModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('ylInvoiceForm')?.reset();
        document.getElementById('ylInvDrsTbody').innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4 font-monospace">Select a Dealer to view pending deliveries.</td></tr>';
        window.currentYlInvItems = [];
        window.calculateYlInvTotals(0, []);
    });
    document.getElementById('ylCollectionModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('ylCollectionForm')?.reset();
        document.getElementById('ylCrInvoicesTbody').innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4 font-monospace">Select a Dealer to view unpaid invoices.</td></tr>';
        document.getElementById('yl_cr_total_amount').innerText = '₱ 0.00';
    });
    document.getElementById('ylRebateModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('ylRebateForm')?.reset();
        document.getElementById('ylRebateItemsTbody').innerHTML = '<tr><td colspan="5" class="py-4 text-muted font-monospace">Select an Invoice to trigger calculations based on the Matrix.</td></tr>';
        document.getElementById('yl_rebate_calc_id').value = '';
        document.getElementById('yl_rebate_dealer_name').value = '';
        document.getElementById('yl_rebate_tot_dealer').innerText = '₱ 0.00';
        document.getElementById('yl_rebate_tot_trade').innerText = '₱ 0.00';
        document.getElementById('yl_rebate_tot_rebate').innerText = '₱ 0.00';
        window.currentRebateItems = [];
    });

    // MODAL: Issue Invoice
    document.getElementById('ylInvoiceModal')?.addEventListener('show.bs.modal', function () {
        document.getElementById('ylInvoiceForm').reset();
        document.getElementById('yl_inv_date').valueAsDate = new Date();
        document.getElementById('yl_inv_warehouse_name').value = '';
        document.getElementById('ylInvDrsTbody').innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4 font-monospace">Select a Dealer to view pending deliveries.</td></tr>';
        
        // Ensure submit button is unlocked
        const btn = document.querySelector('#ylInvoiceForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Post Invoice'; }
        window.currentYlInvItems = [];
        window.calculateYlInvTotals(0, []);
        
        const dealerSel = document.getElementById('yl_inv_dealer_id');
        dealerSel.innerHTML = `<option value="">Loading dealers...</option>`;
        
        fetch(`api/endpoints.php?table=yl_delivery_receipts`).then(r => r.json()).then(data => {
            window.tempUninvoicedDrs = [];
            const uniqueDealers = new Map();
            
            data.forEach(dr => {
                const hasInv = Array.from(document.querySelectorAll('#ylInvoicesTable tbody tr')).some(tr => tr.innerText.includes(dr.DR_No));
                if(!hasInv) {
                    window.tempUninvoicedDrs.push(dr);
                    uniqueDealers.set(`${dr.First_Name} ${dr.Last_Name}`, dr.Dealer_ID);
                }
            });
            
            dealerSel.innerHTML = `<option value="">Select Dealer...</option>`;
            uniqueDealers.forEach((id, name) => {
                dealerSel.innerHTML += `<option value="${name}">${name}</option>`; 
            });
        });
    });

    // MODAL: Receive Payment (Collection)
    document.getElementById('ylCollectionModal')?.addEventListener('show.bs.modal', function () {
        document.getElementById('ylCollectionForm').reset();
        document.getElementById('yl_cr_date').valueAsDate = new Date();
        document.getElementById('ylCrInvoicesTbody').innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4 font-monospace">Select a Dealer to view unpaid invoices.</td></tr>';
        
        // Ensure submit button is unlocked
        const btn = document.querySelector('#ylCollectionForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Post Receipt'; }
        const dealerSel = document.getElementById('yl_cr_dealer_id');
        dealerSel.innerHTML = `<option value="">Loading dealers...</option>`;
        
        fetch(`api/endpoints.php?table=yl_dealers_with_unpaid_invoices`)
        .then(r => r.json())
        .then(data => {
            if(data.error || data.length === 0) {
                dealerSel.innerHTML = '<option value="">No dealers have unpaid invoices.</option>';
                return;
            }
            dealerSel.innerHTML = `<option value="">Select Dealer...</option>`;
            data.forEach(d => { dealerSel.innerHTML += `<option value="${d.Dealer_ID}">${d.First_Name} ${d.Last_Name}</option>`; });
        });
    });

    // MODAL: Calculate Rebate
    document.getElementById('ylRebateModal')?.addEventListener('show.bs.modal', function () {
        document.getElementById('ylRebateForm').reset();
        document.getElementById('yl_rebate_calc_id').value = '';
        document.getElementById('yl_rebate_dealer_name').value = '';
        document.getElementById('yl_rebate_date').valueAsDate = new Date();
        document.getElementById('ylRebateItemsTbody').innerHTML = '<tr><td colspan="5" class="py-4 text-muted font-monospace">Select an Invoice to trigger calculations based on the Matrix.</td></tr>';
        document.getElementById('yl_rebate_tot_dealer').innerText = '₱ 0.00';
        document.getElementById('yl_rebate_tot_trade').innerText = '₱ 0.00';
        document.getElementById('yl_rebate_tot_rebate').innerText = '₱ 0.00';
        window.currentRebateItems = [];

        // Ensure submit button is unlocked
        const btn = document.querySelector('#ylRebateForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save Calculation'; }

        const centerSel = document.getElementById('yl_rebate_center');
        fetch('api/endpoints.php?table=system_dropdowns').then(r=>r.json()).then(data => {
            centerSel.innerHTML = '<option value="">Select Center...</option>';
            data.filter(d => d.Dropdown_Type === 'Center').forEach(c => { centerSel.innerHTML += `<option value="${c.Option_Value}">${c.Option_Value}</option>`; });
        });

        const areaSel = document.getElementById('yl_rebate_area');
        fetch('api/endpoints.php?table=system_dropdowns').then(r=>r.json()).then(data => {
            areaSel.innerHTML = '<option value="">Select Area...</option>';
            data.filter(d => d.Dropdown_Type === 'Area').forEach(a => { areaSel.innerHTML += `<option value="${a.Option_Value}">${a.Option_Value}</option>`; });
        });

        const dealerSel = document.getElementById('yl_rebate_dealer_id');
        fetch('api/endpoints.php?table=dealers').then(r=>r.json()).then(data => {
            dealerSel.innerHTML = '<option value="">Select Dealer...</option>';
            data.forEach(d => { dealerSel.innerHTML += `<option value="${d.Dealer_ID}" data-name="${d.First_Name} ${d.Last_Name}">${d.First_Name} ${d.Last_Name}</option>`; });
        });
    });

    // EVENT LISTENERS
    document.addEventListener('change', (e) => {
        // Invoice Dealer Selection
        if(e.target.id === 'yl_inv_dealer_id') {
            const dealerName = e.target.value;
            const tbody = document.getElementById('ylInvDrsTbody');
            
            if(!dealerName) { 
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4 font-monospace">Select a Dealer to view pending deliveries.</td></tr>'; 
                document.getElementById('yl_inv_warehouse_name').value = '';
                window.currentYlInvItems = [];
                window.calculateYlInvTotals(0, []);
                return; 
            }
            
            tbody.innerHTML = '';
            let center = '';
            const drs = window.tempUninvoicedDrs.filter(dr => `${dr.First_Name} ${dr.Last_Name}` === dealerName);
            
            if(drs.length === 0) { 
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No pending deliveries found.</td></tr>';
            } else {
                drs.forEach(dr => {
                    center = dr.Center || center;
                    const safeJson = dr.Items_JSON ? dr.Items_JSON.replace(/"/g, '&quot;') : '[]';
                    
                    const retQty = parseInt(dr.Returned_Qty) || 0;
                    const retAmt = parseFloat(dr.Returned_Amount) || 0;
                    
                    const netQty = parseInt(dr.Total_Quantity) - retQty;
                    const netAmt = parseFloat(dr.Total_Amount) - retAmt;
                    
                    let qtyDisplay = window.formatQuantity(netQty);
                    let amtDisplay = `₱ ${window.formatCurrency(netAmt)}`;
                    
                    if (retQty > 0) {
                        qtyDisplay += `<br><small class="text-danger fw-bold">(-${window.formatQuantity(retQty)} returned)</small>`;
                        amtDisplay += `<br><small class="text-danger fw-bold">(-₱ ${window.formatCurrency(retAmt)} returned)</small>`;
                    }

                    tbody.innerHTML += `<tr>
                        <td class="text-center align-middle"><div class="form-check mb-0"><input class="form-check-input yl-inv-dr-check" type="checkbox" value="${dr.DR_ID}" data-drno="${dr.DR_No}" data-amt="${netAmt}" data-items="${safeJson}"></div></td>
                        <td class="fw-bold align-middle">${dr.DR_No}</td>
                        <td class="text-center align-middle">${qtyDisplay}</td>
                        <td class="text-primary fw-bold text-end align-middle">${amtDisplay}</td>
                    </tr>`;
                });
            }

            if (!center && window.globalDealersList && window.globalDealersList.length > 0) {
                const dInfo = window.globalDealersList.find(d => `${d.First_Name} ${d.Last_Name}` === dealerName);
                if(dInfo) center = dInfo.Center || '';
            }
            
            let whName = 'Main Warehouse (Default)';
            if(center && window.globalWarehousesList.length > 0) {
                const matchedWH = window.globalWarehousesList.find(w => w.Warehouse_Name.toUpperCase() === center.toUpperCase() || w.Warehouse_Name.toUpperCase().includes(center.toUpperCase()));
                if(matchedWH) whName = matchedWH.Warehouse_Name;
            }
            document.getElementById('yl_inv_warehouse_name').value = whName;
            window.currentYlInvItems = [];
            window.calculateYlInvTotals(0, []);
        }

        // Invoice DR Checkbox
        if(e.target.classList.contains('yl-inv-dr-check')) {
            window.currentYlInvItems = [];
            let total = 0;
            document.querySelectorAll('.yl-inv-dr-check:checked').forEach(chk => { 
                total += parseFloat(chk.getAttribute('data-amt')) || 0; 
                try {
                    const items = JSON.parse(chk.getAttribute('data-items'));
                    items.forEach(newItem => {
                        const existing = window.currentYlInvItems.find(i => i.product_id === newItem.product_id);
                        if(existing) {
                            existing.quantity += parseInt(newItem.quantity);
                        } else {
                            window.currentYlInvItems.push({...newItem});
                        }
                    });
                } catch(e){}
            });
            window.calculateYlInvTotals(total, window.currentYlInvItems);
        }

        // Collection Dealer Selection
        if(e.target.id === 'yl_cr_dealer_id') {
            const did = e.target.value;
            const tbody = document.getElementById('ylCrInvoicesTbody');
            if(!did) { tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4 font-monospace">Select a Dealer to view unpaid invoices.</td></tr>'; return; }
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Loading invoices...</td></tr>';
            
            fetch(`api/endpoints.php?table=yl_unpaid_invoices&dealer_id=${did}`).then(r => r.json()).then(data => {
                tbody.innerHTML = '';
                if(data.length === 0) { tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4 font-monospace">No unpaid invoices found for this dealer.</td></tr>'; window.calculateYlCrTotals(); return; }
                data.forEach(inv => {
                    const drNos = inv.DR_Nos_Display || inv.DR_No || '-';
                    tbody.innerHTML += `<tr><td class="text-center"><div class="form-check mb-0"><input class="form-check-input yl-cr-invoice-check" type="checkbox" value="${inv.Invoice_ID}" data-amt="${inv.Amount_Due}"></div></td><td class="fw-bold">${inv.Invoice_No}</td><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-wrap" style="max-width:150px;">${drNos}</span></td><td class="text-primary fw-bold text-end">₱ ${window.formatCurrency(inv.Amount_Due)}</td></tr>`;
                });
                window.calculateYlCrTotals();
            });
        }

        // Collection Invoice Checkbox
        if(e.target.classList.contains('yl-cr-invoice-check')) { window.calculateYlCrTotals(); }

        // Rebate Listeners
        if(e.target.id === 'yl_rebate_dealer_id') {
            const opt = e.target.options[e.target.selectedIndex];
            document.getElementById('yl_rebate_dealer_name').value = opt ? opt.getAttribute('data-name') : '';
            window.loadRebateInvoices();
        }
        if(e.target.id === 'yl_rebate_date') {
            window.loadRebateInvoices();
        }
        if(e.target.id === 'yl_rebate_invoice_id') {
            const opt = e.target.options[e.target.selectedIndex];
            const tbody = document.getElementById('ylRebateItemsTbody');
            
            if(!opt || !opt.value) {
                tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-muted font-monospace">Select an Invoice to trigger calculations based on the Matrix.</td></tr>';
                document.getElementById('yl_rebate_tot_dealer').innerText = '₱ 0.00';
                document.getElementById('yl_rebate_tot_trade').innerText = '₱ 0.00';
                document.getElementById('yl_rebate_tot_rebate').innerText = '₱ 0.00';
                window.currentRebateItems = [];
                return;
            }

            window.currentRebateItems = [];
            let tDealer = 0, tTrade = 0, tRebate = 0;
            
            try {
                const items = JSON.parse(opt.getAttribute('data-items'));
                tbody.innerHTML = '';
                
                if(items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-muted font-monospace text-danger">No items parsed. Save order properly to view items.</td></tr>';
                } else {
                    items.forEach(item => {
                        const name = (item.product_name || '').toUpperCase();
                        const qty = parseInt(item.quantity) || 0;
                        const type = name.includes('LIGHT') ? 'Light' : 'Original';
                        
                        const dRate = type === 'Light' ? window.ylDiscountRates.light : window.ylDiscountRates.orig;
                        const tRate = type === 'Light' ? window.ylTradeRates.light : window.ylTradeRates.orig;
                        const dealerDisc = qty * dRate;
                        const tradeDisc = qty * tRate;
                        
                        let rebateAmt = 0;
                        const matchedTier = window.ylRebateMatrixTiers.find(t => t.Product_Type === type && qty >= t.Min_Qty && qty <= t.Max_Qty);
                        if (matchedTier) rebateAmt = qty * parseFloat(matchedTier.Rebate_Amount);

                        tDealer += dealerDisc;
                        tTrade += tradeDisc;
                        tRebate += rebateAmt;
                        
                        window.currentRebateItems.push({
                            product_name: name,
                            product_type: type,
                            quantity: qty,
                            dealer_disc: dealerDisc,
                            trade_disc: tradeDisc,
                            sales_rebate: rebateAmt
                        });

                        tbody.innerHTML += `<tr>
                            <td class="text-start fw-bold text-dark">${name}</td>
                            <td class="fw-bold">${window.formatQuantity(qty)}</td>
                            <td class="text-primary fw-bold">₱ ${window.formatCurrency(dealerDisc)}</td>
                            <td class="text-warning fw-bold">₱ ${window.formatCurrency(tradeDisc)}</td>
                            <td class="text-danger fw-bolder">₱ ${window.formatCurrency(rebateAmt)}</td>
                        </tr>`;
                    });
                }
            } catch(e) {
                tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-danger font-monospace">Error calculating items.</td></tr>';
            }

            document.getElementById('yl_rebate_tot_dealer').innerText = `₱ ${window.formatCurrency(tDealer)}`;
            document.getElementById('yl_rebate_tot_trade').innerText = `₱ ${window.formatCurrency(tTrade)}`;
            document.getElementById('yl_rebate_tot_rebate').innerText = `₱ ${window.formatCurrency(tRebate)}`;
        }
    });

    // ==========================================
    // STRICT ANTI-DOUBLE-SUBMISSION LOCKS
    // ==========================================
    document.getElementById('ylInvoiceForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const selDrs = [];
        const selDrNos = [];
        let combinedItems = [];
        
        document.querySelectorAll('.yl-inv-dr-check:checked').forEach(chk => {
            selDrs.push(chk.value);
            selDrNos.push(chk.getAttribute('data-drno'));
            try {
                const items = JSON.parse(chk.getAttribute('data-items'));
                items.forEach(newItem => {
                    const existing = combinedItems.find(i => i.product_id === newItem.product_id);
                    if(existing) {
                        existing.quantity += parseInt(newItem.quantity);
                    } else {
                        combinedItems.push({...newItem});
                    }
                });
            } catch(e){}
        });
        
        if(selDrs.length === 0) { 
            alert("Please select at least one Delivery Receipt."); 
            if (btn) btn.disabled = false;
            return; 
        }
        
        const data = { 
            dr_ids: selDrs,
            dr_nos: selDrNos.join(', '),
            dealer_name: document.getElementById('yl_inv_dealer_id').value,
            items: combinedItems,
            warehouse_name: document.getElementById('yl_inv_warehouse_name').value,
            invoice_no: document.getElementById('yl_inv_no').value.toUpperCase(), 
            invoice_date: document.getElementById('yl_inv_date').value,
            
            discount_orig_amount: 0,
            discount_light_amount: 0,
            trade_orig_amount: 0,
            trade_light_amount: 0,
            
            net_amount: parseFloat(document.getElementById('yl_inv_net_disp').innerText.replace(/[₱ ,]/g,'')), 
            vat: parseFloat(document.getElementById('yl_inv_vat_disp').innerText.replace(/[₱ ,]/g,'')), 
            amount_due: parseFloat(document.getElementById('yl_inv_due_disp').innerText.replace(/[₱ ,]/g,'')) 
        };

        window.postData('yl_invoices', data, this, 'ylInvoiceModal', () => { 
            if (btn) btn.disabled = false;
            window.loadYlInvoices(); 
            if(typeof window.loadAccountingGL === 'function') { window.loadAccountingGL(); window.loadAccountingAR(); } 
        });
    });

    document.getElementById('ylCollectionForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const selInvs = []; document.querySelectorAll('.yl-cr-invoice-check:checked').forEach(chk => selInvs.push(chk.value));
        if(selInvs.length === 0) { 
            alert("Please select at least one invoice to collect."); 
            if (btn) btn.disabled = false;
            return; 
        }

        const sel = document.getElementById('yl_cr_dealer_id'); const dname = sel.options[sel.selectedIndex].text;
        const data = { 
            cr_no: document.getElementById('yl_cr_no').value.toUpperCase(), 
            cr_date: document.getElementById('yl_cr_date').value, 
            dealer_name: dname, 
            invoice_ids: selInvs, 
            total_amount_due: parseFloat(document.getElementById('yl_cr_total_amount').innerText.replace(/[₱ ,]/g,'')), 
            total_words: document.getElementById('yl_cr_amount_words').value 
        };

        window.postData('yl_collection_receipts', data, this, 'ylCollectionModal', () => { 
            if (btn) btn.disabled = false;
            window.loadYlCollectionReceipts(); 
            window.loadYLOrders(); 
            if(typeof window.loadAccountingGL === 'function') { window.loadAccountingGL(); window.loadAccountingAR(); } 
        });
    });

    document.getElementById('ylRebateForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        if(window.currentRebateItems.length === 0) { 
            alert("Please select a valid invoice with products."); 
            if (btn) btn.disabled = false;
            return; 
        }
        
        const selInv = document.getElementById('yl_rebate_invoice_id');
        const invNo = selInv.options[selInv.selectedIndex].text.split(' ')[0];
        
        const data = {
            rebate_calc_id: document.getElementById('yl_rebate_calc_id').value,
            center: document.getElementById('yl_rebate_center').value,
            dealer_id: document.getElementById('yl_rebate_dealer_id').value,
            dealer_name: document.getElementById('yl_rebate_dealer_name').value,
            area_no: document.getElementById('yl_rebate_area').value,
            rebate_date: document.getElementById('yl_rebate_date').value, 
            invoice_id: selInv.value,
            invoice_no: invNo,
            items: window.currentRebateItems,
            total_dealer_disc: parseFloat(document.getElementById('yl_rebate_tot_dealer').innerText.replace(/[₱ ,]/g,'')),
            total_trade_disc: parseFloat(document.getElementById('yl_rebate_tot_trade').innerText.replace(/[₱ ,]/g,'')),
            total_sales_rebate: parseFloat(document.getElementById('yl_rebate_tot_rebate').innerText.replace(/[₱ ,]/g,''))
        };

        window.postData('yl_calculated_rebates', data, this, 'ylRebateModal', () => {
            if (btn) btn.disabled = false;
            window.loadYlRebates();
        });
    });

    // FUNCTIONS
    window.calculateYlInvTotals = function(grossAmt, items = []) {
        const net = grossAmt / 1.12; 
        const vat = grossAmt - net;
        
        document.getElementById('yl_inv_gross_disp').innerText = `₱ ${window.formatCurrency(grossAmt)}`; 
        document.getElementById('yl_inv_vat_disp').innerText = `₱ ${window.formatCurrency(vat)}`; 
        document.getElementById('yl_inv_net_disp').innerText = `₱ ${window.formatCurrency(net)}`; 
        document.getElementById('yl_inv_due_disp').innerText = `₱ ${window.formatCurrency(grossAmt)}`; 
    };

    window.calculateYlCrTotals = function() {
        let total = 0; document.querySelectorAll('.yl-cr-invoice-check:checked').forEach(chk => { total += parseFloat(chk.getAttribute('data-amt')) || 0; });
        document.getElementById('yl_cr_total_amount').innerText = `₱ ${window.formatCurrency(total)}`;
        document.getElementById('yl_cr_amount_words').value = total > 0 ? (typeof window.numberToWords === 'function' ? window.numberToWords(total) + " PESOS" : '') : ''; 
    };

    window.loadRebateInvoices = function() {
        const dname = document.getElementById('yl_rebate_dealer_name').value;
        const rdate = document.getElementById('yl_rebate_date').value;
        const invSel = document.getElementById('yl_rebate_invoice_id');
        const tbody = document.getElementById('ylRebateItemsTbody');
        
        if(!dname || !rdate) {
            invSel.innerHTML = '<option value="">Select Dealer & Date First...</option>';
            tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-muted font-monospace">Select an Invoice to trigger calculations.</td></tr>';
            return;
        }

        invSel.innerHTML = '<option value="">Loading Invoices...</option>';
        fetch('api/endpoints.php?table=yl_invoices').then(r=>r.json()).then(data => {
            const myInvs = data.filter(i => {
                const nameMatch = (i.Dealer_Name_Display || `${i.First_Name} ${i.Last_Name}`) === dname;
                const dbDate = i.Invoice_Date ? i.Invoice_Date.split(' ')[0] : '';
                return nameMatch && dbDate === rdate;
            });
            
            if (myInvs.length === 0) {
                invSel.innerHTML = '<option value="">No Invoices found on this date</option>';
                return;
            }
            
            invSel.innerHTML = '<option value="">Select Invoice...</option>';
            myInvs.forEach(i => {
                let itemsToUse = i.Master_Items;
                if (!itemsToUse || itemsToUse === 'null' || itemsToUse === '[]') itemsToUse = i.Items_JSON;
                if (!itemsToUse) itemsToUse = '[]';
                
                const safeJson = itemsToUse.replace(/"/g, '&quot;');
                invSel.innerHTML += `<option value="${i.Invoice_ID}" data-items="${safeJson}">${i.Invoice_No}</option>`;
            });
        });
    };

    window.loadYlInvoices = function() { 
        fetch('api/endpoints.php?table=yl_invoices').then(r=>r.json()).then(data=>{ 
            const t=document.querySelector('#ylInvoicesTable tbody'); 
            if(t){ 
                t.innerHTML=''; 
                if (data.length === 0 || data.error) return;
                data.forEach(row=>{ 
                    const drNos = row.DR_Nos_Display || row.DR_No || '-';
                    const dealer = row.Dealer_Name_Display || `${row.First_Name} ${row.Last_Name}`;
                    t.innerHTML+=`<tr><td class="fw-bold">${row.Invoice_No}</td><td>${window.formatDate(row.Invoice_Date)}</td><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-wrap" style="max-width:200px;">${drNos}</span></td><td>${dealer}</td><td class="text-end">₱ ${window.formatCurrency(row.Net_Amount)}</td><td class="text-end text-primary fw-bold">₱ ${window.formatCurrency(row.Amount_Due)}</td><td class="text-center">${window.actionIconsHTML('yl_invoices', row.Invoice_ID)}</td></tr>`; 
                }); 
            }
        }); 
    };

    window.loadYlCollectionReceipts = function() {
        fetch('api/endpoints.php?table=yl_collection_receipts').then(r=>r.json()).then(data=>{
            const t=document.querySelector('#ylCollectionsTable tbody');
            if(t){
                t.innerHTML='';
                if (data.length === 0 || data.error) return;
                data.forEach(row=>{
                    const invs = JSON.parse(row.Invoice_IDs_JSON||'[]');
                    t.innerHTML+=`<tr><td class="fw-bold text-success">${row.CR_No}</td><td>${window.formatDate(row.CR_Date)}</td><td>${row.Dealer_Name}</td><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${invs.length} Invoice(s)</span></td><td class="text-end fw-bold text-success">₱ ${window.formatCurrency(row.Total_Amount_Due)}</td><td class="text-center">${window.actionIconsHTML('yl_collection_receipts', row.CR_ID)}</td></tr>`;
                });
            }
        });
    };

    window.loadYlRebates = function() {
        fetch('api/endpoints.php?table=yl_calculated_rebates').then(r=>r.json()).then(data=>{
            const t=document.querySelector('#ylRebatesTable tbody');
            if(t){
                t.innerHTML='';
                if (data.length === 0 || data.error) return;
                data.forEach(row=>{
                    t.innerHTML+=`<tr><td>${window.formatDate(row.CreatedDate)}</td><td class="fw-bold">${row.Dealer_Name}</td><td>${window.formatDate(row.Rebate_Date)}</td><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Invoice_No}</span></td><td class="text-end text-primary fw-bold">₱ ${window.formatCurrency(row.Total_Dealer_Discount)}</td><td class="text-end text-warning fw-bold">₱ ${window.formatCurrency(row.Total_Trade_Discount)}</td><td class="text-end text-danger fw-bolder">₱ ${window.formatCurrency(row.Total_Sales_Rebate)}</td><td class="text-center"><i class="bi bi-trash text-danger" style="cursor:pointer;" onclick="deleteRecord('yl_calculated_rebates', ${row.Rebate_Calc_ID})"></i></td></tr>`;
                });
            }
        });
    };

});