// ==========================================
// MDI AIMS - DS INVOICES & COLLECTIONS
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    // EXPLICIT MODAL RESETS TO PREVENT GHOSTING
    document.getElementById('dsInvoiceModal')?.addEventListener('hidden.bs.modal', function () { 
        document.getElementById('dsInvoiceForm')?.reset(); 
    });
    document.getElementById('dsCollectionModal')?.addEventListener('hidden.bs.modal', function () { 
        document.getElementById('dsCollectionForm')?.reset(); 
    });

    // MODAL: ISSUE INVOICE
    document.getElementById('dsInvoiceModal')?.addEventListener('show.bs.modal', function () {
        document.getElementById('dsInvoiceForm').reset();
        document.getElementById('inv_date').valueAsDate = new Date();
        
        // Ensure submit button is unlocked
        const btn = document.querySelector('#dsInvoiceForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Post Invoice'; }

        const discChk = document.getElementById('inv_apply_discount');
        if(discChk) discChk.checked = false;
        const discSel = document.getElementById('inv_discount_percent');
        if(discSel) { discSel.value = "1"; discSel.style.display = 'none'; }
        const discDisp = document.getElementById('inv_discount_disp');
        if(discDisp) discDisp.innerText = '₱ 0.00';
        
        const titleSpan = document.querySelector('#dsInvoiceModal .modal-control-panel span');
        if (titleSpan) titleSpan.innerHTML = `<i class="bi bi-receipt me-1"></i>New Sales Invoice`;
        
        const soSel = document.getElementById('inv_so_id');
        soSel.innerHTML = '<option value="">Loading Orders...</option>';
        
        // Fetch DS Orders to populate the dropdown
        fetch('api/endpoints.php?table=ds_sales_orders&ds_type=DS').then(r => r.json()).then(data => {
            soSel.innerHTML = '<option value="">Select Stock Order...</option>';
            if (data.error || data.length === 0) return;
            
            data.forEach(so => {
                // Only show orders that haven't been invoiced yet
                const hasInv = Array.from(document.querySelectorAll('#dsInvoicesTable tbody tr')).some(tr => tr.innerText.includes(so.SO_No));
                if(!hasInv) {
                    soSel.innerHTML += `<option value="${so.SO_ID}" data-terms="${so.Terms||''}" data-outlet="${so.Outlet_Name}" data-tin="${so.TIN||''}" data-style="${so.Business_Style||''}" data-amt="${so.Total_Amount}" data-return="${so.Returned_Amount||0}">${so.SO_No} (${so.Outlet_Name})</option>`;
                }
            });
        });
    });

    // MODAL: COLLECTION RECEIPT
    document.getElementById('dsCollectionModal')?.addEventListener('show.bs.modal', function () {
        document.getElementById('dsCollectionForm').reset();
        document.getElementById('cr_date').valueAsDate = new Date();
        document.getElementById('crInvoicesTbody').innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3 font-monospace">Select a customer to view unpaid invoices.</td></tr>';
        
        // Ensure submit button is unlocked
        const btn = document.querySelector('#dsCollectionForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Post Receipt'; }

        const outletSel = document.getElementById('cr_outlet_id');
        outletSel.innerHTML = '<option value="">Loading customers...</option>';
        
        fetch('api/endpoints.php?table=ds_outlets_with_unpaid_invoices&ds_type=DS')
        .then(r => r.json())
        .then(data => {
            if(data.error || data.length === 0) {
                outletSel.innerHTML = '<option value="">No customers have unpaid invoices.</option>';
                return;
            }
            outletSel.innerHTML = '<option value="">Select Customer...</option>';
            data.forEach(o => { 
                const branchLabel = o.Branch ? ` (${o.Branch})` : '';
                outletSel.innerHTML += `<option value="${o.Outlet_ID}" data-address="${o.Address||''}" data-tin="${o.Outlet_TIN||''}" data-style="${o.Business_Style||''}">${o.Outlet_Name}${branchLabel}</option>`; 
            });
        });
    });

    // DYNAMIC EVENT LISTENERS
    document.addEventListener('change', (e) => {
        if(e.target.id === 'inv_so_id') {
            const opt = e.target.options[e.target.selectedIndex];
            if(opt && opt.value) {
                document.getElementById('inv_outlet_name').value = opt.getAttribute('data-outlet');
                document.getElementById('inv_outlet_tin').value = opt.getAttribute('data-tin');
                document.getElementById('inv_business_style').value = opt.getAttribute('data-style');
                
                const terms = opt.getAttribute('data-terms') || '';
                let title = 'New Sales Invoice';
                const tUp = terms.toUpperCase();
                if (tUp.includes('COD') || tUp.includes('CASH')) title = 'New Cash Sales Invoice';
                else if (tUp.includes('7') || tUp.includes('15') || tUp.includes('30') || tUp.includes('CHARGE')) title = 'New Charge Sales Invoice';
                
                const baseAmt = parseFloat(opt.getAttribute('data-amt')) || 0;
                const retAmt = parseFloat(opt.getAttribute('data-return')) || 0;
                const finalAmt = baseAmt - retAmt;
                let badge = retAmt > 0 ? `<span class="badge bg-danger ms-2 fs-6">Less Return: ₱ ${window.formatCurrency(retAmt)}</span>` : '';
                
                const titleSpan = document.querySelector('#dsInvoiceModal .modal-control-panel span');
                if (titleSpan) titleSpan.innerHTML = `<i class="bi bi-receipt me-1"></i>${title} ${badge}`;

                window.calculateDsInvTotals(finalAmt);
            } else {
                const titleSpan = document.querySelector('#dsInvoiceModal .modal-control-panel span');
                if (titleSpan) titleSpan.innerHTML = `<i class="bi bi-receipt me-1"></i>New Sales Invoice`;
            }
        }
        
        if(e.target.id === 'inv_apply_ewt') {
            const soSel = document.getElementById('inv_so_id');
            const opt = soSel.options[soSel.selectedIndex];
            if(opt && opt.value) {
                const baseAmt = parseFloat(opt.getAttribute('data-amt')) || 0;
                const retAmt = parseFloat(opt.getAttribute('data-return')) || 0;
                window.calculateDsInvTotals(baseAmt - retAmt);
            }
        }

        if(e.target.id === 'inv_apply_discount' || e.target.id === 'inv_discount_percent') {
            const discSel = document.getElementById('inv_discount_percent');
            if(e.target.id === 'inv_apply_discount') {
                discSel.style.display = e.target.checked ? 'inline-block' : 'none';
            }
            
            const soSel = document.getElementById('inv_so_id');
            const opt = soSel.options[soSel.selectedIndex];
            if(opt && opt.value) {
                const baseAmt = parseFloat(opt.getAttribute('data-amt')) || 0;
                const retAmt = parseFloat(opt.getAttribute('data-return')) || 0;
                window.calculateDsInvTotals(baseAmt - retAmt);
            }
        }
        
        if(e.target.id === 'cr_outlet_id') {
            const opt = e.target.options[e.target.selectedIndex];
            const tbody = document.getElementById('crInvoicesTbody');
            if(!opt || !opt.value) { 
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3 font-monospace">Select a customer to view unpaid invoices.</td></tr>'; 
                return; 
            }
            
            document.getElementById('cr_address').value = opt.getAttribute('data-address');
            document.getElementById('cr_outlet_tin').value = opt.getAttribute('data-tin');
            document.getElementById('cr_business_style').value = opt.getAttribute('data-style');

            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">Loading invoices...</td></tr>';
            fetch(`api/endpoints.php?table=ds_unpaid_invoices&outlet_id=${opt.value}`).then(r => r.json()).then(data => {
                tbody.innerHTML = '';
                if(data.length === 0) { 
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">No unpaid invoices found for this customer.</td></tr>'; 
                    window.calculateDsCrTotals(); 
                    return; 
                }
                data.forEach(inv => {
                    tbody.innerHTML += `<tr><td class="text-center"><div class="form-check mb-0"><input class="form-check-input ds-cr-invoice-check" type="checkbox" value="${inv.Invoice_ID}" data-amt="${inv.Amount_Due}"></div></td><td class="fw-bold">${inv.Invoice_No}</td><td class="text-primary fw-bold text-end">₱ ${window.formatCurrency(inv.Amount_Due)}</td></tr>`;
                });
                window.calculateDsCrTotals();
            });
        }

        if(e.target.classList.contains('ds-cr-invoice-check')) window.calculateDsCrTotals();
    });

    // ==========================================
    // STRICT ANTI-DOUBLE-SUBMISSION LOCKS
    // ==========================================
    document.getElementById('dsInvoiceForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;
        
        const ewtCheckbox = document.getElementById('inv_apply_ewt');
        const discountCheckbox = document.getElementById('inv_apply_discount');
        
        const data = { 
            ds_type: 'DS',
            so_id: document.getElementById('inv_so_id').value, 
            invoice_no: document.getElementById('inv_no').value.toUpperCase(), 
            invoice_date: document.getElementById('inv_date').value, 
            net_amount: parseFloat(document.getElementById('inv_net_disp').innerText.replace(/[₱ ,]/g,'')), 
            vat: parseFloat(document.getElementById('inv_vat_disp').innerText.replace(/[₱ ,]/g,'')), 
            applied_ewt: (ewtCheckbox && ewtCheckbox.checked) ? 1 : 0, 
            ewt_amount: parseFloat(document.getElementById('inv_ewt_disp').innerText.replace(/[₱ ,]/g,'')) || 0,
            discount_percent: (discountCheckbox && discountCheckbox.checked) ? parseFloat(document.getElementById('inv_discount_percent').value) : 0,
            discount_amount: parseFloat(document.getElementById('inv_discount_disp').innerText.replace(/[₱ ,]/g,'')) || 0,
            amount_due: parseFloat(document.getElementById('inv_due_disp').innerText.replace(/[₱ ,]/g,'')) 
        };

        window.postData('ds_invoices', data, this, 'dsInvoiceModal', () => { 
            if (btn) btn.disabled = false;
            window.loadDsInvoices(); 
            if(typeof window.loadAccountingGL === 'function') { window.loadAccountingGL(); window.loadAccountingAR(); } 
            if(typeof window.loadTaxReports === 'function') { window.loadTaxReports(); window.loadFinancialReports(); }
            const currentAccId = document.getElementById('ledger_account_id')?.value;
            if (currentAccId && typeof window.loadAccountLedger === 'function') window.loadAccountLedger(currentAccId);
        });
    });

    document.getElementById('dsCollectionForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const selInvs = []; 
        document.querySelectorAll('.ds-cr-invoice-check:checked').forEach(chk => selInvs.push(chk.value));
        if(selInvs.length === 0) { 
            alert("Please select at least one invoice to collect."); 
            if (btn) btn.disabled = false;
            return; 
        }
        
        const sel = document.getElementById('cr_outlet_id'); 
        const oname = sel.options[sel.selectedIndex].text;
        
        const data = { 
            ds_type: 'DS', cr_no: document.getElementById('cr_no').value.toUpperCase(), cr_date: document.getElementById('cr_date').value, 
            outlet_name: oname, address: document.getElementById('cr_address').value, outlet_tin: document.getElementById('cr_outlet_tin').value,
            business_style: document.getElementById('cr_business_style').value, invoice_ids: selInvs, 
            total_amount_due: parseFloat(document.getElementById('cr_total_amount').innerText.replace(/[₱ ,]/g,'')), 
            total_words: document.getElementById('cr_amount_words').value 
        };

        window.postData('ds_collection_receipts', data, this, 'dsCollectionModal', () => { 
            if (btn) btn.disabled = false;
            window.loadDsCollections(); window.loadDsOrders(); 
            if(typeof window.loadAccountingGL === 'function') { window.loadAccountingGL(); window.loadAccountingAR(); } 
            if(typeof window.loadTaxReports === 'function') { window.loadTaxReports(); window.loadFinancialReports(); }
            const currentAccId = document.getElementById('ledger_account_id')?.value;
            if (currentAccId && typeof window.loadAccountLedger === 'function') window.loadAccountLedger(currentAccId);
        });
    });
});

window.calculateDsInvTotals = function(grossAmt) {
    let discAmt = 0;
    const discChk = document.getElementById('inv_apply_discount');
    if(discChk && discChk.checked) {
        const pct = parseFloat(document.getElementById('inv_discount_percent').value) || 0;
        discAmt = grossAmt * (pct / 100);
    }
    
    const newGross = grossAmt - discAmt;
    const net = newGross / 1.12; 
    const vat = newGross - net;
    
    let ewt = 0;
    if(document.getElementById('inv_apply_ewt') && document.getElementById('inv_apply_ewt').checked) ewt = net * 0.01; 
    
    const due = newGross - ewt;
    
    document.getElementById('inv_gross_disp').innerText = `₱ ${window.formatCurrency(grossAmt)}`; 
    const discDisp = document.getElementById('inv_discount_disp');
    if(discDisp) discDisp.innerText = `₱ ${window.formatCurrency(discAmt)}`;
    document.getElementById('inv_vat_disp').innerText = `₱ ${window.formatCurrency(vat)}`; 
    document.getElementById('inv_net_disp').innerText = `₱ ${window.formatCurrency(net)}`; 
    document.getElementById('inv_ewt_disp').innerText = `₱ ${window.formatCurrency(ewt)}`;
    document.getElementById('inv_due_disp').innerText = `₱ ${window.formatCurrency(due)}`; 
};

window.calculateDsCrTotals = function() {
    let total = 0; 
    document.querySelectorAll('.ds-cr-invoice-check:checked').forEach(chk => { total += parseFloat(chk.getAttribute('data-amt')) || 0; });
    document.getElementById('cr_total_amount').innerText = `₱ ${window.formatCurrency(total)}`;
    document.getElementById('cr_amount_words').value = total > 0 ? (typeof window.numberToWords === 'function' ? window.numberToWords(total) + " PESOS" : '') : ''; 
};

window.loadDsInvoices = function() { 
    fetch('api/endpoints.php?table=ds_invoices&ds_type=DS').then(r=>r.json()).then(data=>{ 
        const t=document.querySelector('#dsInvoicesTable tbody'); 
        if(t){ 
            t.innerHTML=''; 
            if (data.length === 0 || data.error) return;
            data.forEach(row=>{ 
                t.innerHTML+=`<tr><td class="fw-bold">${row.Invoice_No}</td><td>${window.formatDate(row.Invoice_Date)}</td><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.SO_No}</span></td><td>${row.Outlet_Name}</td><td class="text-end">₱ ${window.formatCurrency(row.Net_Amount)}</td><td class="text-end text-primary fw-bold">₱ ${window.formatCurrency(row.Amount_Due)}</td><td class="text-center">${window.actionIconsHTML('ds_invoices', row.Invoice_ID)}</td></tr>`; 
            }); 
        }
    }); 
};

window.loadDsCollections = function() { 
    fetch('api/endpoints.php?table=ds_collection_receipts&ds_type=DS').then(r=>r.json()).then(data=>{ 
        const t=document.querySelector('#dsCollectionsTable tbody'); 
        if(t){ 
            t.innerHTML=''; 
            if (data.length === 0 || data.error) return;
            data.forEach(row=>{ 
                const invs = JSON.parse(row.Invoice_IDs_JSON||'[]'); 
                t.innerHTML+=`<tr><td class="fw-bold text-success">${row.CR_No}</td><td>${window.formatDate(row.CR_Date)}</td><td>${row.Outlet_Name}</td><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${invs.length} Invoice(s)</span></td><td class="text-end fw-bold text-success">₱ ${window.formatCurrency(row.Total_Amount_Due)}</td><td class="text-center">${window.actionIconsHTML('ds_collection_receipts', row.CR_ID)}</td></tr>`; 
            }); 
        }
    }); 
};