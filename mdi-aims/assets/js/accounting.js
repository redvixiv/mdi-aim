// ==========================================
// MDI AIMS - ACCOUNTING & FINANCE MODULE
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    
    if(document.getElementById('module-Accounting')) {
        
        if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.accounting_tabs) {
            const allowedTabs = window.userPermissions.accounting_tabs;
            const allTabs = ['acc-reports', 'acc-receivables', 'acc-payables', 'acc-core', 'acc-audit'];
            let firstAllowedTabBtn = null;

            document.querySelectorAll('#accountingTabs .nav-link').forEach(btn => {
                const targetId = btn.getAttribute('data-bs-target').substring(1); 
                const pane = document.getElementById(targetId);
                
                if (allTabs.includes(targetId) && !allowedTabs.includes(targetId)) {
                    btn.closest('li').style.display = 'none';
                    if (pane) { pane.classList.remove('show', 'active'); pane.style.display = 'none'; }
                } 
                else if (targetId === 'acc-tax' && !allowedTabs.includes('acc-reports') && !allowedTabs.includes('acc-core')) {
                    btn.closest('li').style.display = 'none';
                    if (pane) { pane.classList.remove('show', 'active'); pane.style.display = 'none'; }
                } 
                else { if (!firstAllowedTabBtn) firstAllowedTabBtn = btn; }
            });

            if (firstAllowedTabBtn) firstAllowedTabBtn.click();
        }

        window.loadAccountingCOA();
        window.loadAccountingAR();
        window.loadAccountingAP();
        window.loadAccountingPV();
        window.loadAccountingExpenses();
        window.loadAccountingGL();
        window.loadFinancialReports();
        window.loadTaxReports();
        window.loadFixedAssets();
        window.loadAuditReports();
        
        setTimeout(() => {
            const headerActions = document.querySelector('#acc-reports .d-flex.justify-content-between div');
            if(headerActions && !document.getElementById('btnSyncLedger')) {
                headerActions.insertAdjacentHTML('beforeend', `<button id="btnSyncLedger" class="btn btn-erp btn-outline-warning shadow-sm ms-2" onclick="window.syncHistoricalLedger()"><i class="bi bi-arrow-repeat me-1"></i>Sync Ledger</button>`);
            }
        }, 500);
    }

    // ==========================================
    // EXPLICIT MODAL RESETS (ANTI-GHOSTING)
    // ==========================================
    const accModals = ['coaModal', 'apModal', 'pvModal', 'expenseModal', 'glModal', 'assetModal'];
    accModals.forEach(modalId => {
        document.getElementById(modalId)?.addEventListener('hidden.bs.modal', function () {
            const form = this.querySelector('form');
            if (form) form.reset();

            if (modalId === 'glModal') {
                const tbody = document.getElementById('journalItemsTbody');
                if (tbody) tbody.innerHTML = '';
                const statusEl = document.getElementById('j_balance_status');
                if (statusEl) {
                    statusEl.className = "badge bg-danger-subtle text-danger border border-danger-subtle p-2";
                    statusEl.innerText = "UNBALANCED";
                }
                const btnSave = document.getElementById('btnSaveGl');
                if (btnSave) btnSave.disabled = true;
                const td = document.getElementById('j_total_debit'); if(td) td.innerText = '₱ 0.00';
                const tc = document.getElementById('j_total_credit'); if(tc) tc.innerText = '₱ 0.00';
            }
            if (modalId === 'pvModal') {
                const apSelect = document.getElementById('pv_ap_id');
                if (apSelect) {
                    apSelect.innerHTML = '<option value="">Select Supplier First</option>';
                    apSelect.disabled = true;
                }
            }
        });
    });

    // ==========================================
    // MODAL OPEN LISTENERS (Initialization)
    // ==========================================
    const apModalEl = document.getElementById('apModal');
    if (apModalEl) {
        apModalEl.addEventListener('show.bs.modal', function (event) {
            document.getElementById('apForm').reset();
            document.getElementById('ap_date').valueAsDate = new Date();
            
            // Unlock submit button
            const btn = document.querySelector('#apForm button[type="submit"]');
            if(btn) { btn.disabled = false; btn.innerHTML = 'SAVE PAYABLE'; }

            const accSelect = document.getElementById('ap_account_id');
            accSelect.innerHTML = '<option value="">Select Account...</option>';
            if (window.globalCOAList && window.globalCOAList.length > 0) {
                window.globalCOAList.forEach(a => {
                    if(a.Account_Type === 'Expense' || a.Account_Type === 'Asset') { accSelect.innerHTML += `<option value="${a.Account_ID}">${a.Account_Code} - ${a.Account_Name}</option>`; }
                });
            }

            const select = document.getElementById('ap_supplier_id');
            select.innerHTML = '<option value="">LOADING SUPPLIERS...</option>';
            fetch(`api/endpoints.php?table=suppliers&_t=${Date.now()}`).then(r => r.json()).then(data => {
                select.innerHTML = `<option value="">Select Supplier...</option>`;
                data.forEach(s => { select.innerHTML += `<option value="${s.Supplier_ID}">${s.Supplier_No} - ${s.Supplier_Name}</option>`; });
            });
        });
    }

    const pvModalEl = document.getElementById('pvModal');
    if (pvModalEl) {
        pvModalEl.addEventListener('show.bs.modal', function (event) {
            document.getElementById('pvForm').reset();
            document.getElementById('pv_date').valueAsDate = new Date();
            document.getElementById('pv_ap_id').innerHTML = '<option value="">Select Supplier First</option>';
            document.getElementById('pv_ap_id').disabled = true;
            
            const btn = document.querySelector('#pvForm button[type="submit"]');
            if(btn) { btn.disabled = false; btn.innerHTML = 'SAVE VOUCHER'; }

            const select = document.getElementById('pv_supplier_id');
            select.innerHTML = '<option value="">LOADING SUPPLIERS...</option>';
            fetch(`api/endpoints.php?table=suppliers&_t=${Date.now()}`).then(r => r.json()).then(data => {
                select.innerHTML = `<option value="">Select Supplier...</option>`;
                data.forEach(s => { select.innerHTML += `<option value="${s.Supplier_ID}">${s.Supplier_No} - ${s.Supplier_Name}</option>`; });
            });
        });
    }

    const expModalEl = document.getElementById('expenseModal');
    if (expModalEl) {
        expModalEl.addEventListener('show.bs.modal', function (event) {
            document.getElementById('expenseForm').reset();
            document.getElementById('exp_date').valueAsDate = new Date();
            
            const btn = document.querySelector('#expenseForm button[type="submit"]');
            if(btn) { btn.disabled = false; btn.innerHTML = 'SAVE EXPENSE'; }

            const select = document.getElementById('exp_account_id');
            select.innerHTML = '<option value="">Select Expense Account...</option>';
            if (window.globalCOAList && window.globalCOAList.length > 0) {
                window.globalCOAList.forEach(a => {
                    if(a.Account_Type === 'Expense') { select.innerHTML += `<option value="${a.Account_ID}">${a.Account_Code} - ${a.Account_Name}</option>`; }
                });
            }
        });
    }

    const glModalEl = document.getElementById('glModal');
    if (glModalEl) {
        glModalEl.addEventListener('show.bs.modal', function (event) {
            document.getElementById('glForm').reset();
            document.getElementById('gl_date').valueAsDate = new Date();
            const tbody = document.getElementById('journalItemsTbody');
            tbody.innerHTML = '';
            window.addJournalLineRow();
            window.addJournalLineRow();
        });
    }
    
    const assetModalEl = document.getElementById('assetModal');
    if (assetModalEl) {
        assetModalEl.addEventListener('show.bs.modal', function (event) {
            document.getElementById('assetForm').reset();
            document.getElementById('ast_date').valueAsDate = new Date();
            
            const btn = document.querySelector('#assetForm button[type="submit"]');
            if(btn) { btn.disabled = false; btn.innerHTML = 'SAVE ASSET'; }
        });
    }

    // ==========================================
    // STRICT ANTI-DOUBLE-SUBMISSION LOCKS
    // ==========================================
    document.getElementById('coaForm')?.addEventListener('submit', function(e) { 
        e.preventDefault(); e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = { account_code: document.getElementById('coa_code').value, account_name: document.getElementById('coa_name').value, account_type: document.getElementById('coa_type').value }; 
        window.postData('accounting_coa', data, this, 'coaModal', () => { 
            if(btn) btn.disabled = false;
            window.loadAccountingCOA(); window.loadFinancialReports(); 
        }); 
    });

    document.getElementById('apForm')?.addEventListener('submit', function(e) { 
        e.preventDefault(); e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = { account_id: document.getElementById('ap_account_id').value, supplier_id: document.getElementById('ap_supplier_id').value, reference_no: document.getElementById('ap_ref').value.toUpperCase(), ap_date: document.getElementById('ap_date').value, amount: document.getElementById('ap_amt').value, remarks: document.getElementById('ap_remarks').value }; 
        window.postData('accounting_ap', data, this, 'apModal', () => { 
            if(btn) btn.disabled = false;
            window.loadAccountingAP(); window.loadAccountingGL(); window.loadFinancialReports(); window.loadTaxReports(); window.loadAuditReports(); 
        }); 
    });

    document.getElementById('pvForm')?.addEventListener('submit', function(e) { 
        e.preventDefault(); e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = { pv_no: document.getElementById('pv_no').value.toUpperCase(), pv_date: document.getElementById('pv_date').value, supplier_id: document.getElementById('pv_supplier_id').value, ap_id: document.getElementById('pv_ap_id').value, amount: document.getElementById('pv_amt').value, payment_method: document.getElementById('pv_method').value, check_no: document.getElementById('pv_check_no').value, remarks: document.getElementById('pv_remarks').value }; 
        window.postData('accounting_pv', data, this, 'pvModal', () => { 
            if(btn) btn.disabled = false;
            window.loadAccountingPV(); window.loadAccountingAP(); window.loadAccountingGL(); window.loadFinancialReports(); window.loadTaxReports(); window.loadAuditReports(); 
        }); 
    });

    document.getElementById('expenseForm')?.addEventListener('submit', function(e) { 
        e.preventDefault(); e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = { expense_date: document.getElementById('exp_date').value, account_id: document.getElementById('exp_account_id').value, amount: document.getElementById('exp_amt').value, description: document.getElementById('exp_desc').value, reference_no: document.getElementById('exp_ref').value }; 
        window.postData('accounting_expenses', data, this, 'expenseModal', () => { 
            if(btn) btn.disabled = false;
            window.loadAccountingExpenses(); window.loadAccountingGL(); window.loadFinancialReports(); window.loadTaxReports(); 
        }); 
    });

    document.getElementById('assetForm')?.addEventListener('submit', function(e) {
        e.preventDefault(); e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = {
            name: document.getElementById('ast_name').value.trim(),
            date: document.getElementById('ast_date').value,
            cost: parseFloat(document.getElementById('ast_cost').value),
            life: parseInt(document.getElementById('ast_life').value)
        };
        window.postData('fixed_assets', data, this, 'assetModal', () => { 
            if(btn) btn.disabled = false;
            window.loadFixedAssets(); window.loadFinancialReports(); window.loadAccountingGL(); 
        });
    });

    document.getElementById('glForm')?.addEventListener('submit', function(e) {
        e.preventDefault(); e.stopImmediatePropagation();
        const btn = document.getElementById('btnSaveGl');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const lines = [];
        document.querySelectorAll('.gl-item-row').forEach(row => {
            const accSelect = row.querySelector('.gl-acc-select');
            if(accSelect && accSelect.value) {
                const d = parseFloat(row.querySelector('.gl-debit').value) || 0; const c = parseFloat(row.querySelector('.gl-credit').value) || 0;
                if(d > 0 || c > 0) { lines.push({ account_id: accSelect.value, debit: d, credit: c }); }
            }
        });
        if(lines.length < 2) { 
            alert("A journal entry requires at least 2 lines."); 
            if(btn) btn.disabled = false;
            return; 
        }
        
        const data = { journal_date: document.getElementById('gl_date').value, description: document.getElementById('gl_desc').value.toUpperCase(), reference_no: document.getElementById('gl_ref').value.toUpperCase(), lines: lines };
        window.postData('accounting_journal', data, this, 'glModal', () => { 
            if(btn) btn.disabled = false;
            window.loadAccountingGL(); window.loadFinancialReports(); window.loadTaxReports(); window.loadAuditReports();
            const currentAccId = document.getElementById('ledger_account_id').value; 
            if (currentAccId) { window.loadAccountLedger(currentAccId); } 
        });
    });

    // ==========================================
    // DYNAMIC EVENT LISTENERS
    // ==========================================
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('recon-check')) {
            const status = e.target.checked ? 'Cleared' : 'Pending';
            fetch('api/endpoints.php?table=update_recon_status', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ line_id: e.target.getAttribute('data-id'), status: status }) })
            .then(r=>r.json()).then(res => { if(res.status === 'success') { window.loadBankRecon(document.getElementById('recon_account_id').value); } });
        }
        if (e.target.id === 'recon_account_id') {
            const aid = e.target.value;
            if(!aid) { document.querySelector('#accReconTable tbody').innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4 font-monospace">Select an account to view recon lines.</td></tr>'; document.getElementById('recon_cleared_balance').innerText = '₱ 0.00'; return; }
            window.loadBankRecon(aid);
        }
        if (e.target.id === 'pv_supplier_id') {
            const sid = e.target.value; const apSelect = document.getElementById('pv_ap_id');
            if(!sid) { apSelect.innerHTML = '<option value="">Select Supplier First</option>'; apSelect.disabled = true; document.getElementById('pv_amt').value=''; return; }
            apSelect.innerHTML = '<option value="">LOADING PENDING APs...</option>'; apSelect.disabled = true;
            fetch(`api/endpoints.php?table=accounting_ap_pending&supplier_id=${sid}&_t=${Date.now()}`).then(r=>r.json()).then(data => {
                apSelect.innerHTML = '<option value="">Select AP Reference...</option>';
                if(data.length === 0) apSelect.innerHTML += '<option value="" disabled>No Pending Payables</option>';
                data.forEach(ap => { apSelect.innerHTML += `<option value="${ap.AP_ID}" data-amt="${ap.Amount}">${ap.Reference_No} (₱ ${window.formatCurrency(ap.Amount)})</option>`; });
                apSelect.disabled = false;
            });
        }
        if (e.target.id === 'pv_ap_id') {
            const selectedOpt = e.target.options[e.target.selectedIndex];
            if(selectedOpt && selectedOpt.value) { document.getElementById('pv_amt').value = selectedOpt.getAttribute('data-amt'); } else { document.getElementById('pv_amt').value = ''; }
        }
        if (e.target.id === 'ledger_account_id') {
            const aid = e.target.value;
            if(!aid) { document.querySelector('#accLedgerTable tbody').innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4 font-monospace">Select an account to view its ledger entries.</td></tr>'; document.getElementById('ledger_current_balance').innerText = '₱ 0.00'; return; }
            window.loadAccountLedger(aid);
        }
    });

    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('gl-debit') || e.target.classList.contains('gl-credit')) { window.calculateGLTotals(); }
    });

    document.body.addEventListener('click', (e) => {
        const btnAddGL = e.target.closest('#btnAddJournalLine');
        if (btnAddGL) { window.addJournalLineRow(); }
    });

    window.addJournalLineRow = function() {
        const tbody = document.getElementById('journalItemsTbody');
        let optionsHTML = '<option value="">Select Account...</option>';
        if (window.globalCOAList && window.globalCOAList.length > 0) { optionsHTML += window.globalCOAList.map(a => `<option value="${a.Account_ID}">${a.Account_Code} - ${a.Account_Name}</option>`).join(''); }
        const tr = document.createElement('tr'); tr.className = 'gl-item-row';
        tr.innerHTML = `<td><select class="form-select odoo-input gl-acc-select border-0 bg-transparent p-0" required>${optionsHTML}</select></td><td><input type="number" step="0.01" class="form-control odoo-input gl-debit text-success fw-bold border-0 bg-transparent p-0" value="0.00" required></td><td><input type="number" step="0.01" class="form-control odoo-input gl-credit text-danger fw-bold border-0 bg-transparent p-0" value="0.00" required></td><td class="text-center align-middle"><i class="bi bi-trash text-muted" style="cursor:pointer; font-size:1.2rem;" onclick="this.closest('tr').remove(); window.calculateGLTotals();"></i></td>`;
        tbody.appendChild(tr);
    };

    window.calculateGLTotals = function() {
        let totDeb = 0; let totCred = 0;
        document.querySelectorAll('.gl-item-row').forEach(row => { totDeb += parseFloat(row.querySelector('.gl-debit').value) || 0; totCred += parseFloat(row.querySelector('.gl-credit').value) || 0; });
        document.getElementById('j_total_debit').innerText = `₱ ${window.formatCurrency(totDeb)}`; 
        document.getElementById('j_total_credit').innerText = `₱ ${window.formatCurrency(totCred)}`;
        const statusEl = document.getElementById('j_balance_status'); const btnSave = document.getElementById('btnSaveGl');
        if (Math.abs(totDeb - totCred) < 0.01 && totDeb > 0) { statusEl.className = "badge bg-success-subtle text-success border border-success-subtle p-2"; statusEl.innerText = "BALANCED"; if(btnSave) btnSave.disabled = false; } else { statusEl.className = "badge bg-danger-subtle text-danger border border-danger-subtle p-2"; statusEl.innerText = "UNBALANCED"; if(btnSave) btnSave.disabled = true; }
    };

    // ==========================================
    // DATA LOADING FUNCTIONS
    // ==========================================
    window.loadFinancialReports = function() {
        fetch(`api/endpoints.php?table=accounting_reports&_t=${Date.now()}`).then(r=>r.json()).then(data => {
            if (!data || !data.summary) return; const s = data.summary;
            document.getElementById('kpi_revenue').innerText = `₱ ${window.formatCurrency(s.revenue)}`; 
            document.getElementById('kpi_expense').innerText = `₱ ${window.formatCurrency(s.expense)}`; 
            document.getElementById('kpi_net_income').innerText = `₱ ${window.formatCurrency(s.net_income)}`; 
            document.getElementById('kpi_asset').innerText = `₱ ${window.formatCurrency(s.asset)}`; 
            document.getElementById('kpi_liability').innerText = `₱ ${window.formatCurrency(s.liability)}`;
            
            document.getElementById('pnl_rev').innerText = `₱ ${window.formatCurrency(s.revenue)}`; 
            document.getElementById('pnl_exp').innerText = `₱ ${window.formatCurrency(s.expense)}`; 
            document.getElementById('pnl_net').innerText = `₱ ${window.formatCurrency(s.net_income)}`;
            
            document.getElementById('bs_asset').innerText = `₱ ${window.formatCurrency(s.asset)}`; 
            document.getElementById('bs_liability').innerText = `₱ ${window.formatCurrency(s.liability)}`; 
            document.getElementById('bs_equity').innerText = `₱ ${window.formatCurrency(s.equity)}`;
            const totalLE = parseFloat(s.liability) + parseFloat(s.equity); 
            document.getElementById('bs_total_le').innerText = `₱ ${window.formatCurrency(totalLE)}`;

            const cf = data.cash_flow;
            document.getElementById('cf_operating').innerText = `₱ ${window.formatCurrency(cf.operating)}`;
            document.getElementById('cf_investing').innerText = `₱ ${window.formatCurrency(cf.investing)}`;
            document.getElementById('cf_financing').innerText = `₱ ${window.formatCurrency(cf.financing)}`;
            const netCash = parseFloat(cf.operating) + parseFloat(cf.investing) + parseFloat(cf.financing);
            document.getElementById('cf_net').innerText = `₱ ${window.formatCurrency(netCash)}`;
        });
    };

    window.loadTaxReports = function() {
        fetch(`api/endpoints.php?table=accounting_tax_report&_t=${Date.now()}`).then(r=>r.json()).then(data => {
            const s = (data && data.summary) ? data.summary : { output_vat: 0, input_vat: 0, net_vat_payable: 0, ewt_withheld: 0 };
            document.getElementById('tax_output_vat').innerText = `₱ ${window.formatCurrency(s.output_vat)}`; 
            document.getElementById('tax_input_vat').innerText = `₱ ${window.formatCurrency(s.input_vat)}`; 
            document.getElementById('tax_net_vat').innerText = `₱ ${window.formatCurrency(s.net_vat_payable)}`; 
            document.getElementById('tax_ewt_withheld').innerText = `₱ ${window.formatCurrency(s.ewt_withheld)}`;
            
            const tbody = document.querySelector('#accTaxTable tbody'); if(!tbody) return; tbody.innerHTML = '';
            if(!data || !data.details || data.details.length === 0) { tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4 font-monospace">No sales tax transactions recorded.</td></tr>'; return; }
            
            data.details.forEach(row => { 
                const net = parseFloat(row.Net_Amount) || 0; const vat = parseFloat(row.VAT) || 0; const ewt = parseFloat(row.EWT) || 0; const gross = parseFloat(row.Amount_Due) || 0; 
                tbody.innerHTML += `<tr><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Source}</span></td><td class="fw-bold">${row.Invoice_No}</td><td>${window.formatDate(row.Invoice_Date)}</td><td class="text-end">₱ ${window.formatCurrency(net)}</td><td class="text-primary fw-bold text-end">₱ ${window.formatCurrency(vat)}</td><td class="text-success fw-bold text-end">₱ ${window.formatCurrency(ewt)}</td><td class="fw-bold text-end">₱ ${window.formatCurrency(gross)}</td></tr>`; 
            });
        });
    };

    window.printTrialBalance = function() {
        fetch('api/endpoints.php?table=trial_balance').then(r=>r.json()).then(data => {
            if(!data || !data.details) { alert('No GL Data available to print.'); return; }
            let rows = '';
            data.details.forEach(r => {
                rows += `<tr>
                    <td style="padding:8px; border-bottom:1px solid #eee; color:#475569;">${r.code}</td>
                    <td style="padding:8px; border-bottom:1px solid #eee; font-weight:600; color:#334155;">${r.name}</td>
                    <td style="padding:8px; border-bottom:1px solid #eee; text-align:right;">${r.debit > 0 ? '₱ '+window.formatCurrency(r.debit) : '-'}</td>
                    <td style="padding:8px; border-bottom:1px solid #eee; text-align:right;">${r.credit > 0 ? '₱ '+window.formatCurrency(r.credit) : '-'}</td>
                </tr>`;
            });
            let content = `<div style="font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; padding:40px;">
                <h2 style="text-align:center; text-transform:uppercase; margin-bottom:5px; color:#0f172a; letter-spacing: 1px;">Trial Balance</h2>
                <p style="text-align:center; color:#64748b; margin-top:0;">As of ${new Date().toLocaleDateString()}</p>
                <table style="width:100%; border-collapse:collapse; margin-top:30px; font-size: 0.9rem;">
                    <thead><tr style="background:#f8fafc;"><th style="padding:12px; border-bottom:2px solid #cbd5e1; text-align:left; color:#64748b; text-transform:uppercase;">Account Code</th><th style="padding:12px; border-bottom:2px solid #cbd5e1; text-align:left; color:#64748b; text-transform:uppercase;">Account Name</th><th style="padding:12px; border-bottom:2px solid #cbd5e1; text-align:right; color:#64748b; text-transform:uppercase;">Debit</th><th style="padding:12px; border-bottom:2px solid #cbd5e1; text-align:right; color:#64748b; text-transform:uppercase;">Credit</th></tr></thead>
                    <tbody>${rows}</tbody>
                    <tfoot><tr><th colspan="2" style="padding:15px 12px; border-top:2px solid #94a3b8; text-align:right; font-size:1.1rem; color:#0f172a;">TOTAL BALANCES:</th><th style="padding:15px 12px; border-top:2px solid #94a3b8; text-align:right; color:#10b981; font-size:1.1rem; border-bottom: 4px double #94a3b8;">₱ ${window.formatCurrency(data.total_debit)}</th><th style="padding:15px 12px; border-top:2px solid #94a3b8; text-align:right; color:#10b981; font-size:1.1rem; border-bottom: 4px double #94a3b8;">₱ ${window.formatCurrency(data.total_credit)}</th></tr></tfoot>
                </table>
            </div>`;
            const printWindow = window.open('', '_blank'); 
            printWindow.document.write('<html><head><title>Trial Balance</title></head><body>' + content + '</body></html>'); 
            printWindow.document.close(); printWindow.focus(); setTimeout(() => { printWindow.print(); printWindow.close(); }, 250);
        });
    };

    window.exportBIRSLSP = function() {
        fetch('api/endpoints.php?table=bir_slsp_export').then(r=>r.json()).then(data => {
            if(!data || data.length === 0) { alert('No sales data available for BIR export.'); return; }
            let csv = "TIN,Registered Name,Address,Gross Sales,Exempt Sales,Zero Rated Sales,Taxable Sales,Output Tax,EWT Withheld\n";
            data.forEach(row => {
                csv += `"${row.TIN}","${row.Registered_Name}","${row.Address}","${row.Gross_Sales}","0.00","0.00","${row.Taxable_Sales}","${row.Output_VAT}","${row.EWT_Withheld}"\n`;
            });
            const blob = new Blob(["\uFEFF" + csv], {type: "text/csv;charset=utf-8;"});
            const link = document.createElement("a"); link.href = URL.createObjectURL(blob); link.download = "BIR_SLSP_Report.csv"; link.click();
        });
    };

    window.loadBankRecon = function(accountId) {
        fetch(`api/endpoints.php?table=bank_recon_lines&account_id=${accountId}`).then(r=>r.json()).then(data => {
            const tbody = document.querySelector('#accReconTable tbody'); tbody.innerHTML = '';
            if(data.length === 0) { tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4 font-monospace">No transactions to reconcile.</td></tr>'; document.getElementById('recon_cleared_balance').innerText = '₱ 0.00'; return; }
            
            let clearedBalance = 0;
            data.forEach(row => {
                const deb = parseFloat(row.Debit) || 0; const cred = parseFloat(row.Credit) || 0;
                const isCleared = row.Cleared_Status === 'Cleared';
                if (isCleared) clearedBalance += (deb - cred);
                
                const chk = `<div class="form-check form-switch mb-0 d-flex justify-content-center"><input class="form-check-input recon-check" type="checkbox" data-id="${row.Line_ID}" ${isCleared ? 'checked' : ''} style="cursor:pointer; transform: scale(1.2);"></div>`;
                tbody.innerHTML += `<tr><td>${window.formatDate(row.Journal_Date)}</td><td class="text-muted font-monospace">${row.Reference_No || '-'}</td><td class="fw-bold">${row.Description}</td><td class="text-success text-end">₱ ${window.formatCurrency(deb)}</td><td class="text-danger text-end">₱ ${window.formatCurrency(cred)}</td><td>${chk}</td></tr>`;
            });
            document.getElementById('recon_cleared_balance').innerText = `₱ ${window.formatCurrency(clearedBalance)}`;
        });
    };

    window.loadAccountLedger = function(accountId) {
        fetch(`api/endpoints.php?table=accounting_ledger&account_id=${accountId}&_t=${Date.now()}`).then(r=>r.json()).then(data => {
            const tbody = document.querySelector('#accLedgerTable tbody'); tbody.innerHTML = '';
            if(data.length === 0) { tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4 font-monospace">No transactions found for this account.</td></tr>'; document.getElementById('ledger_current_balance').innerText = '₱ 0.00'; return; }
            
            let runningBalance = 0; let accountType = data[0].Account_Type;
            let rowsHtml = []; 
            data.forEach(row => {
                const deb = parseFloat(row.Debit) || 0; const cred = parseFloat(row.Credit) || 0;
                if (accountType === 'Asset' || accountType === 'Expense') { runningBalance += (deb - cred); } else { runningBalance += (cred - deb); }
                
                rowsHtml.unshift(`<tr><td>${window.formatDate(row.Journal_Date)}</td><td class="text-muted font-monospace">${row.Reference_No || '-'}</td><td class="fw-bold text-dark">${row.Description}</td><td class="text-success text-end">₱ ${window.formatCurrency(deb)}</td><td class="text-danger text-end">₱ ${window.formatCurrency(cred)}</td><td class="text-primary fw-bold text-end">₱ ${window.formatCurrency(runningBalance)}</td></tr>`);
            });
            tbody.innerHTML = rowsHtml.join('');
            document.getElementById('ledger_current_balance').innerText = `₱ ${window.formatCurrency(runningBalance)}`;
        });
    };

    window.loadAccountingCOA = function() {
        fetch(`api/endpoints.php?table=accounting_coa&_t=${Date.now()}`).then(r=>r.json()).then(data => {
            window.globalCOAList = data; 
            const tbody = document.querySelector(`#accCoaTable tbody`); if(tbody) tbody.innerHTML = '';
            
            const ledgerSelect = document.getElementById('ledger_account_id'); if(ledgerSelect) ledgerSelect.innerHTML = '<option value="">Select Account to View Ledger...</option>';
            const reconSelect = document.getElementById('recon_account_id'); if(reconSelect) reconSelect.innerHTML = '<option value="">Select Cash/Bank Account...</option>';

            if(data.length===0 && tbody) tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4 font-monospace">No accounts found.</td></tr>';
            
            data.forEach(row => { 
                if(tbody) tbody.innerHTML += `<tr><td class="fw-bold">${row.Account_Code}</td><td>${row.Account_Name}</td><td class="text-center"><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Account_Type}</span></td><td class="text-center">${window.actionIconsHTML('accounting_coa', row.Account_ID)}</td></tr>`; 
                if(ledgerSelect) ledgerSelect.innerHTML += `<option value="${row.Account_ID}">${row.Account_Code} - ${row.Account_Name}</option>`;
                if(reconSelect && row.Account_Type === 'Asset') reconSelect.innerHTML += `<option value="${row.Account_ID}">${row.Account_Code} - ${row.Account_Name}</option>`;
            });
        });
    };

    window.loadAccountingAR = function() {
        fetch(`api/endpoints.php?table=accounting_ar&_t=${Date.now()}`).then(r=>r.json()).then(data => {
            const tbody = document.querySelector(`#accArTable tbody`); if(!tbody) return; tbody.innerHTML = '';
            let current = 0, thirty = 0, sixty = 0, ninety = 0;
            if(data.length===0) { tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4 font-monospace">No pending receivables found.</td></tr>'; }
            data.forEach(row => { 
                const age = parseInt(row.Age_Days) || 0; const amt = parseFloat(row.Amount_Due) || 0; let ageBadge = `<span class="badge bg-success-subtle text-success border border-success-subtle">${age} Days</span>`;
                if (age <= 30) current += amt; else if (age <= 60) { thirty += amt; ageBadge = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">${age} Days</span>`; } else if (age <= 90) { sixty += amt; ageBadge = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle">${age} Days</span>`; } else { ninety += amt; ageBadge = `<span class="badge bg-dark-subtle text-dark border border-dark-subtle">${age} Days</span>`; }
                
                const statBadge = row.Status === 'Pending' ? `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">${row.Status}</span>` : `<span class="badge bg-success-subtle text-success border border-success-subtle">${row.Status}</span>`;
                
                tbody.innerHTML += `<tr><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Source}</span></td><td class="fw-bold">${row.Invoice_No}</td><td>${window.formatDate(row.Invoice_Date)}</td><td>${row.Client_Name}</td><td class="text-primary fw-bold text-end">₱ ${window.formatCurrency(amt)}</td><td class="text-center">${ageBadge}</td><td class="text-center">${statBadge}</td></tr>`; 
            });
            const elC = document.getElementById('ar_0_30'); if(elC) elC.innerText = `₱ ${window.formatCurrency(current)}`;
            const el30 = document.getElementById('ar_31_60'); if(el30) el30.innerText = `₱ ${window.formatCurrency(thirty)}`;
            const el60 = document.getElementById('ar_61_90'); if(el60) el60.innerText = `₱ ${window.formatCurrency(sixty)}`;
            const el90 = document.getElementById('ar_90_plus'); if(el90) el90.innerText = `₱ ${window.formatCurrency(ninety)}`;
        });
    };

    window.loadAccountingAP = function() {
        fetch(`api/endpoints.php?table=accounting_ap&_t=${Date.now()}`).then(r=>r.json()).then(data => {
            const tbody = document.querySelector(`#accApTable tbody`); if(!tbody) return; tbody.innerHTML = '';
            let current = 0, thirty = 0, sixty = 0, ninety = 0;
            if(data.length===0) { tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4 font-monospace">No payables found.</td></tr>'; }
            data.forEach(row => { 
                const badgeClass = row.Status === 'Paid' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'; 
                const age = parseInt(row.Age_Days) || 0; const amt = parseFloat(row.Amount) || 0; let ageBadge = `<span class="badge bg-success-subtle text-success border border-success-subtle">${age} Days</span>`;
                
                if (row.Status === 'Pending') { if (age <= 30) current += amt; else if (age <= 60) { thirty += amt; ageBadge = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">${age} Days</span>`; } else if (age <= 90) { sixty += amt; ageBadge = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle">${age} Days</span>`; } else { ninety += amt; ageBadge = `<span class="badge bg-dark-subtle text-dark border border-dark-subtle">${age} Days</span>`; } } else { ageBadge = '<span class="text-muted small">-</span>'; }
                
                let actionBtn = row.Status === 'Pending' ? `<i class="bi bi-trash text-danger" style="cursor:pointer; font-size: 1.2rem;" onclick="deleteRecord('accounting_ap', ${row.AP_ID})" title="Delete Bill"></i>` : `<span class="badge bg-light text-muted border border-secondary-subtle">LOCKED</span>`;
                if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) actionBtn = `<span class="badge bg-light text-muted border border-secondary-subtle">LOCKED</span>`;
                
                tbody.innerHTML += `<tr><td>${window.formatDate(row.AP_Date)}</td><td class="fw-bold">${row.Supplier_Name}</td><td class="font-monospace text-muted">${row.Reference_No}</td><td class="text-danger fw-bold text-end">₱ ${window.formatCurrency(amt)}</td><td class="text-center">${ageBadge}</td><td class="text-center"><span class="badge ${badgeClass}">${row.Status}</span></td><td><small class="text-muted">${row.Remarks||''}</small></td><td class="text-center">${actionBtn}</td></tr>`; 
            });
            const elC = document.getElementById('ap_0_30'); if(elC) elC.innerText = `₱ ${window.formatCurrency(current)}`;
            const el30 = document.getElementById('ap_31_60'); if(el30) el30.innerText = `₱ ${window.formatCurrency(thirty)}`;
            const el60 = document.getElementById('ap_61_90'); if(el60) el60.innerText = `₱ ${window.formatCurrency(sixty)}`;
            const el90 = document.getElementById('ap_90_plus'); if(el90) el90.innerText = `₱ ${window.formatCurrency(ninety)}`;
        });
    };

    window.loadAccountingPV = function() {
        fetch(`api/endpoints.php?table=accounting_pv&_t=${Date.now()}`).then(r=>r.json()).then(data => {
            const tbody = document.querySelector(`#accPvTable tbody`); if(!tbody) return; tbody.innerHTML = '';
            if(data.length===0) tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4 font-monospace">No payment vouchers found.</td></tr>';
            data.forEach(row => { tbody.innerHTML += `<tr><td class="fw-bold">${row.PV_No}</td><td>${window.formatDate(row.PV_Date)}</td><td>${row.Supplier_Name}</td><td class="font-monospace text-muted">${row.AP_Ref}</td><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Payment_Method}</span> <small class="text-muted ms-1">${row.Check_No||''}</small></td><td class="text-primary fw-bold text-end">₱ ${window.formatCurrency(row.Amount)}</td><td class="text-center">${window.actionIconsHTML('accounting_pv', row.PV_ID)}</td></tr>`; });
        });
    };

    window.loadAccountingExpenses = function() {
        fetch(`api/endpoints.php?table=accounting_expenses&_t=${Date.now()}`).then(r=>r.json()).then(data => {
            const tbody = document.querySelector(`#accExpenseTable tbody`); if(!tbody) return; tbody.innerHTML = '';
            if(data.length===0) tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4 font-monospace">No direct expenses found.</td></tr>';
            data.forEach(row => { tbody.innerHTML += `<tr><td>${window.formatDate(row.Expense_Date)}</td><td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-2">${row.Account_Code}</span><span class="fw-bold">${row.Account_Name}</span></td><td class="text-dark">${row.Description}</td><td class="font-monospace text-muted">${row.Reference_No||'-'}</td><td class="text-danger fw-bold text-end">₱ ${window.formatCurrency(row.Amount)}</td><td class="text-center">${window.actionIconsHTML('accounting_expenses', row.Expense_ID)}</td></tr>`; });
        });
    };

    window.loadAccountingGL = function() {
        fetch(`api/endpoints.php?table=accounting_gl&_t=${Date.now()}`).then(r=>r.json()).then(data => {
            const tbody = document.querySelector(`#accGlTable tbody`); if(!tbody) return; tbody.innerHTML = '';
            if(data.length===0) tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4 font-monospace">No journal entries found.</td></tr>';
            data.forEach(row => { 
                const isReversed = row.Description.includes('[REVERSED]');
                let actions = `<i class="bi bi-printer text-primary" style="cursor:pointer; font-size:1.1rem;" title="Print" onclick="window.printDocument('accounting_gl', ${row.Journal_ID})"></i>`;
                
                if(!isReversed) {
                    actions = `<i class="bi bi-arrow-counterclockwise text-warning me-3" style="cursor:pointer; font-size:1.1rem;" title="Reverse Entry" onclick="window.reverseJournalEntry(${row.Journal_ID})"></i>` + actions;
                }
                
                const descHtml = isReversed ? `<span class="text-decoration-line-through text-muted fst-italic">${row.Description}</span>` : `<span class="fw-bold text-dark">${row.Description}</span>`;
                tbody.innerHTML += `<tr><td class="fw-bold text-muted">JE-${row.Journal_ID}</td><td>${window.formatDate(row.Journal_Date)}</td><td class="font-monospace text-muted">${row.Reference_No||'-'}</td><td>${descHtml}</td><td class="text-primary fw-bold text-end">₱ ${window.formatCurrency(row.Total_Amount)}</td><td class="text-center">${actions}</td></tr>`; 
            });
        });
    };

    window.loadFixedAssets = function() {
        fetch(`api/endpoints.php?table=fixed_assets`).then(r=>r.json()).then(data => {
            const tbody = document.querySelector(`#accAssetsTable tbody`); if(!tbody) return; tbody.innerHTML = '';
            if(data.length===0) tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4 font-monospace">No Fixed Assets recorded.</td></tr>';
            data.forEach(row => { 
                const bv = parseFloat(row.Purchase_Cost) - parseFloat(row.Accumulated_Depreciation);
                const badge = row.Status === 'Active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                tbody.innerHTML += `<tr><td class="fw-bold">${row.Asset_Name}</td><td>${window.formatDate(row.Purchase_Date)}</td><td class="text-end">₱ ${window.formatCurrency(row.Purchase_Cost)}</td><td class="text-center">${row.Useful_Life_Months} Mos.</td><td class="text-end">₱ ${window.formatCurrency(row.Monthly_Depreciation)}</td><td class="text-end text-danger fw-bold">₱ ${window.formatCurrency(row.Accumulated_Depreciation)}</td><td class="text-end text-primary fw-bold">₱ ${window.formatCurrency(bv)}</td><td class="text-center"><span class="badge ${badge}">${row.Status}</span></td></tr>`; 
            });
        });
    };

    window.runDepreciation = function() {
        if(confirm("Are you sure you want to run monthly depreciation? This will post Journal Entries for all active assets.")) {
            fetch('api/endpoints.php?table=run_depreciation', { method: 'POST', body: JSON.stringify({}) }).then(r=>r.json()).then(res => {
                if(res.status === 'success') { alert(res.message); window.loadFixedAssets(); window.loadAccountingGL(); window.loadFinancialReports(); }
            });
        }
    };

    window.closeFiscalYear = function() {
        if(confirm("WARNING: Closing the fiscal year will zero out all Revenue and Expense accounts into Retained Earnings. This cannot be undone. Proceed?")) {
            fetch('api/endpoints.php?table=close_fiscal_year', { method: 'POST', body: JSON.stringify({}) }).then(r=>r.json()).then(res => {
                if(res.status === 'success') { alert("Fiscal Year closed successfully!"); window.loadAccountingGL(); window.loadFinancialReports(); }
                else { alert(res.message); }
            });
        }
    };

    window.syncHistoricalLedger = function() {
        const btn = document.getElementById('btnSyncLedger');
        if(confirm("This will safely sync all missing historical Invoices and Collection Receipts into your General Ledger. Proceed?")) {
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Syncing...`;
            btn.disabled = true;
            fetch('api/endpoints.php?table=sync_historical_ledger', { method: 'POST', body: JSON.stringify({}) })
            .then(r => r.json()).then(res => {
                if(res.status === 'success') { 
                    alert(res.message); 
                    window.loadAccountingGL(); 
                    window.loadFinancialReports(); 
                    window.loadTaxReports(); 
                    btn.innerHTML = `<i class="bi bi-check-circle me-1"></i>Synced!`;
                    setTimeout(() => { btn.style.display = 'none'; }, 3000);
                } else {
                    alert("Error: " + res.message);
                    btn.innerHTML = `<i class="bi bi-arrow-repeat me-1"></i>Sync Failed`;
                    btn.disabled = false;
                }
            });
        }
    };

    window.loadAuditReports = function() {
        fetch(`api/endpoints.php?table=audit_reports&_t=${Date.now()}`).then(r=>r.json()).then(data => {
            let arTot = 0, apTot = 0, invTot = 0;
            
            const tAR = document.querySelector('#auditArTable tbody'); if(tAR) tAR.innerHTML = '';
            data.ar_sl.forEach(r => { arTot += parseFloat(r.Total_Due); tAR.innerHTML += `<tr><td class="fw-bold">${r.Client_Name}</td><td class="text-end text-primary fw-bold">₱ ${window.formatCurrency(r.Total_Due)}</td></tr>`; });
            if(data.ar_sl.length === 0) tAR.innerHTML = `<tr><td colspan="2" class="text-center text-muted font-monospace">No pending AR</td></tr>`;

            const tAP = document.querySelector('#auditApTable tbody'); if(tAP) tAP.innerHTML = '';
            data.ap_sl.forEach(r => { apTot += parseFloat(r.Total_Due); tAP.innerHTML += `<tr><td class="fw-bold">${r.Supplier_Name}</td><td class="text-end text-danger fw-bold">₱ ${window.formatCurrency(r.Total_Due)}</td></tr>`; });
            if(data.ap_sl.length === 0) tAP.innerHTML = `<tr><td colspan="2" class="text-center text-muted font-monospace">No pending AP</td></tr>`;

            const tInv = document.querySelector('#auditInvTable tbody'); if(tInv) tInv.innerHTML = '';
            data.inv_val.forEach(r => { 
                const val = parseFloat(r.Qty) * parseFloat(r.Cost); invTot += val;
                tInv.innerHTML += `<tr><td class="fw-bold text-dark">${r.Product_Name}</td><td class="text-center">${window.formatQuantity(r.Qty)}</td><td class="text-end text-info fw-bold">₱ ${window.formatCurrency(val)}</td></tr>`; 
            });
            if(data.inv_val.length === 0) tInv.innerHTML = `<tr><td colspan="3" class="text-center text-muted font-monospace">No inventory value</td></tr>`;

            document.getElementById('audit_ar_tot').innerText = `₱ ${window.formatCurrency(arTot)}`;
            document.getElementById('audit_ap_tot').innerText = `₱ ${window.formatCurrency(apTot)}`;
            document.getElementById('audit_inv_tot').innerText = `₱ ${window.formatCurrency(invTot)}`;
        });
    };

    window.reverseJournalEntry = function(id) {
        if(confirm("Are you sure you want to REVERSE this Journal Entry? This action will generate a counter-entry and cannot be undone.")) {
            fetch('api/endpoints.php?table=reverse_journal', { method: 'POST', body: JSON.stringify({journal_id: id}) })
            .then(r=>r.json()).then(res => {
                if(res.status === 'success') {
                    alert('Entry reversed successfully.');
                    window.loadAccountingGL();
                    window.loadFinancialReports();
                    window.loadTaxReports();
                    window.loadAuditReports();
                } else {
                    alert('Error: ' + res.message);
                }
            });
        }
    };

});