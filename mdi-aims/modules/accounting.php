<div id="module-Accounting" class="module-container p-2">
    
    <!-- ERP Custom Styling -->
    <style>
        .erp-header { font-weight: 800; color: #1e293b; letter-spacing: -0.5px; }
        .erp-nav { border-bottom: 2px solid #e2e8f0; margin-bottom: 25px; }
        .erp-nav .nav-link { color: #64748b; font-weight: 700; padding: 12px 20px; border: none; border-bottom: 3px solid transparent; border-radius: 0; transition: all 0.2s ease; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
        .erp-nav .nav-link:hover { color: #334155; border-bottom-color: #cbd5e1; background: transparent; }
        .erp-nav .nav-link.active { color: var(--theme-green, #10b981); border-bottom-color: var(--theme-green, #10b981); background: transparent; }
        
        .kpi-card { border-radius: 16px; transition: transform 0.2s ease, box-shadow 0.2s ease; border: 1px solid rgba(0,0,0,0.03); background: #fff; z-index: 1; }
        .kpi-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.06) !important; }
        .kpi-icon-bg { position: absolute; right: -10px; bottom: -15px; font-size: 6rem; opacity: 0.08; transform: rotate(-10deg); transition: transform 0.3s ease; z-index: 0; }
        .kpi-card:hover .kpi-icon-bg { transform: rotate(0deg) scale(1.1); }
        .kpi-title { font-size: 0.75rem; letter-spacing: 0.8px; color: #64748b; }
        .kpi-value { font-size: 1.8rem; letter-spacing: -0.5px; color: #0f172a; }

        .fs-card { border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .fs-header { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; background: #f8fafc; border-radius: 12px 12px 0 0; }
        .fs-table { width: 100%; margin: 0; }
        .fs-table td { padding: 12px 20px; border-bottom: 1px solid #f8fafc; color: #475569; font-size: 0.95rem; }
        .fs-table tr:hover td { background: #fcfcfc; }
        .fs-val { font-family: 'Courier New', Courier, monospace; font-weight: 600; text-align: right; }
        .fs-total td { border-top: 2px solid #cbd5e1 !important; border-bottom: 4px double #94a3b8 !important; font-weight: 800; font-size: 1.15rem; color: #0f172a; padding: 15px 20px; }

        .erp-table-container { border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .erp-table { border-spacing: 0; border-collapse: collapse; width: 100%; margin: 0; }
        .erp-table thead th { background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
        .erp-table tbody td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; font-size: 0.875rem; }
        .erp-table tbody tr:hover td { background: #f8fafc; }
        
        .btn-erp { border-radius: 6px; font-weight: 600; letter-spacing: 0.3px; padding: 6px 14px; transition: all 0.2s; font-size: 0.85rem; text-transform: uppercase; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="erp-header mb-0"><i class="bi bi-briefcase text-muted me-2"></i>Accounting & Finance</h4>
    </div>
    
    <ul class="nav erp-nav" id="accountingTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#acc-reports" type="button"><i class="bi bi-graph-up me-2"></i>Dashboard</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#acc-receivables" type="button"><i class="bi bi-box-arrow-in-down-left me-2"></i>Receivables</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#acc-payables" type="button"><i class="bi bi-box-arrow-up-right me-2"></i>Payables</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#acc-tax" type="button"><i class="bi bi-receipt me-2"></i>Tax Reports</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#acc-core" type="button"><i class="bi bi-journal-bookmark me-2"></i>General Ledger</button></li>
        <li class="nav-item ms-auto"><button class="nav-link text-warning" data-bs-toggle="tab" data-bs-target="#acc-audit" type="button"><i class="bi bi-shield-check me-2"></i>Audit & Valuations</button></li>
    </ul>

    <div class="tab-content" id="accountingTabsContent">
        
        <!-- DASHBOARD TAB -->
        <div class="tab-pane fade show active" id="acc-reports">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-secondary mb-0" style="letter-spacing: -0.3px;">Financial Overview</h5>
                <div>
                    <button class="btn btn-erp btn-outline-danger me-2 shadow-sm" onclick="window.closeFiscalYear()"><i class="bi bi-calendar-check me-1"></i>Close Fiscal Year</button>
                    <button class="btn btn-erp btn-primary shadow-sm" onclick="window.printTrialBalance()"><i class="bi bi-file-earmark-pdf me-1"></i>Print Trial Balance</button>
                </div>
            </div>
            
            <div class="row g-4 mb-5">
                <div class="col-md">
                    <div class="kpi-card p-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%); border-bottom: 3px solid #0ea5e9;">
                        <div class="kpi-icon-bg text-primary"><i class="bi bi-cash-stack"></i></div>
                        <span class="text-uppercase fw-bold kpi-title d-block position-relative z-1">Total Sales Revenue</span>
                        <h2 class="mb-0 mt-2 fw-bolder kpi-value position-relative z-1" id="kpi_revenue">₱0.00</h2>
                    </div>
                </div>
                <div class="col-md">
                    <div class="kpi-card p-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #fee2e2 100%); border-bottom: 3px solid #ef4444;">
                        <div class="kpi-icon-bg text-danger"><i class="bi bi-cart-dash"></i></div>
                        <span class="text-uppercase fw-bold kpi-title d-block position-relative z-1">Total Expenses</span>
                        <h2 class="mb-0 mt-2 fw-bolder kpi-value position-relative z-1 text-danger" id="kpi_expense">₱0.00</h2>
                    </div>
                </div>
                <div class="col-md">
                    <div class="kpi-card p-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #dcfce7 100%); border-bottom: 3px solid #10b981;">
                        <div class="kpi-icon-bg text-success"><i class="bi bi-graph-up-arrow"></i></div>
                        <span class="text-uppercase fw-bold kpi-title d-block position-relative z-1">Net Income</span>
                        <h2 class="mb-0 mt-2 fw-bolder kpi-value position-relative z-1 text-success" id="kpi_net_income">₱0.00</h2>
                    </div>
                </div>
                <div class="col-md">
                    <div class="kpi-card p-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 3px solid #64748b;">
                        <div class="kpi-icon-bg text-secondary"><i class="bi bi-bank"></i></div>
                        <span class="text-uppercase fw-bold kpi-title d-block position-relative z-1">Total Assets</span>
                        <h2 class="mb-0 mt-2 fw-bolder kpi-value position-relative z-1" id="kpi_asset">₱0.00</h2>
                    </div>
                </div>
                <div class="col-md">
                    <div class="kpi-card p-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #ffedd5 100%); border-bottom: 3px solid #f97316;">
                        <div class="kpi-icon-bg text-warning"><i class="bi bi-receipt-cutoff"></i></div>
                        <span class="text-uppercase fw-bold kpi-title d-block position-relative z-1">Total Liabilities</span>
                        <h2 class="mb-0 mt-2 fw-bolder kpi-value position-relative z-1" id="kpi_liability">₱0.00</h2>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="fs-card h-100">
                        <div class="fs-header d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-uppercase text-secondary m-0" style="letter-spacing: 0.5px;"><i class="bi bi-file-earmark-bar-graph me-2"></i>Income Statement</h6>
                        </div>
                        <table class="fs-table" id="tblIncomeStatement">
                            <tbody>
                                <tr><td>Operating Revenue</td><td class="fs-val text-primary" id="pnl_rev">₱0.00</td></tr>
                                <tr><td>Operating Expenses</td><td class="fs-val text-danger" id="pnl_exp">₱0.00</td></tr>
                                <tr class="fs-total"><td>Net Income</td><td class="fs-val text-success" id="pnl_net">₱0.00</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="fs-card h-100">
                        <div class="fs-header d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-uppercase text-secondary m-0" style="letter-spacing: 0.5px;"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Balance Sheet</h6>
                        </div>
                        <table class="fs-table" id="tblBalanceSheet">
                            <tbody>
                                <tr><td>Total Assets</td><td class="fs-val text-success" id="bs_asset">₱0.00</td></tr>
                                <tr><td class="ps-4">Liabilities</td><td class="fs-val text-danger" id="bs_liability">₱0.00</td></tr>
                                <tr><td class="ps-4">Owner's Equity</td><td class="fs-val text-primary" id="bs_equity">₱0.00</td></tr>
                                <tr class="fs-total"><td class="text-uppercase">Liab. + Equity</td><td class="fs-val text-dark" id="bs_total_le">₱0.00</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="fs-card h-100 border-top border-4 border-info">
                        <div class="fs-header d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-uppercase text-info m-0" style="letter-spacing: 0.5px;"><i class="bi bi-droplet-half me-2"></i>Statement of Cash Flows</h6>
                        </div>
                        <table class="fs-table" id="tblCashFlow">
                            <tbody>
                                <tr><td>Operating Activities</td><td class="fs-val" id="cf_operating">₱0.00</td></tr>
                                <tr><td>Investing Activities</td><td class="fs-val" id="cf_investing">₱0.00</td></tr>
                                <tr><td>Financing Activities</td><td class="fs-val" id="cf_financing">₱0.00</td></tr>
                                <tr class="fs-total"><td class="text-uppercase">Net Cash Change</td><td class="fs-val text-dark" id="cf_net">₱0.00</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECEIVABLES TAB -->
        <div class="tab-pane fade" id="acc-receivables">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-secondary mb-0" style="letter-spacing: -0.3px;">Accounts Receivable Ledger</h5>
                <div class="d-flex w-50 justify-content-end">
                    <button class="btn btn-erp btn-outline-success me-2 bg-white" onclick="window.exportTableToCSV('accArTable', 'AR_Aging_Report.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Export</button>
                    <div class="input-group w-50 shadow-sm"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#acc-receivables" placeholder="Search invoices..."></div>
                </div>
            </div>
            
            <div class="row g-3 mb-4" id="arAgingSummary">
                <div class="col"><div class="kpi-card p-3 border-start border-4 border-primary"><span class="kpi-title text-uppercase">Current (0-30 Days)</span><h4 class="mb-0 text-primary fw-bolder mt-1" id="ar_0_30">₱0.00</h4></div></div>
                <div class="col"><div class="kpi-card p-3 border-start border-4 border-warning"><span class="kpi-title text-uppercase">31-60 Days Overdue</span><h4 class="mb-0 text-warning fw-bolder mt-1" id="ar_31_60">₱0.00</h4></div></div>
                <div class="col"><div class="kpi-card p-3 border-start border-4 border-danger"><span class="kpi-title text-uppercase">61-90 Days Overdue</span><h4 class="mb-0 text-danger fw-bolder mt-1" id="ar_61_90">₱0.00</h4></div></div>
                <div class="col"><div class="kpi-card p-3 border-start border-4 border-dark bg-light"><span class="kpi-title text-uppercase">Over 90 Days</span><h4 class="mb-0 text-dark fw-bolder mt-1" id="ar_90_plus">₱0.00</h4></div></div>
            </div>
            
            <div class="erp-table-container">
                <table class="erp-table" id="accArTable">
                    <thead><tr><th>Source</th><th>Invoice No.</th><th>Date</th><th>Client / Dealer</th><th class="text-end">Amount Due</th><th class="text-center">Age</th><th class="text-center">Status</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- PAYABLES TAB -->
        <div class="tab-pane fade" id="acc-payables">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <ul class="nav nav-pills" role="tablist">
                    <li class="nav-item"><button class="nav-link active btn-erp px-4" data-bs-toggle="pill" data-bs-target="#ap-list" type="button">Vendor Bills</button></li>
                    <li class="nav-item ms-2"><button class="nav-link btn-erp px-4" data-bs-toggle="pill" data-bs-target="#pv-list" type="button">Payment Vouchers</button></li>
                    <li class="nav-item ms-2"><button class="nav-link btn-erp px-4" data-bs-toggle="pill" data-bs-target="#exp-list" type="button">Direct Expenses</button></li>
                </ul>
                <div class="d-flex w-50 justify-content-end">
                    <button class="btn btn-erp btn-outline-success me-2 bg-white" onclick="window.exportTableToCSV('accApTable', 'Accounts_Payable_Report.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Export</button>
                    <div class="input-group w-50 shadow-sm"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#acc-payables" placeholder="Search payables..."></div>
                </div>
            </div>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="ap-list">
                    <div class="row g-3 mb-4" id="apAgingSummary">
                        <div class="col"><div class="kpi-card p-3 border-start border-4 border-primary"><span class="kpi-title text-uppercase">Current (0-30 Days)</span><h4 class="mb-0 text-primary fw-bolder mt-1" id="ap_0_30">₱0.00</h4></div></div>
                        <div class="col"><div class="kpi-card p-3 border-start border-4 border-warning"><span class="kpi-title text-uppercase">31-60 Days Overdue</span><h4 class="mb-0 text-warning fw-bolder mt-1" id="ap_31_60">₱0.00</h4></div></div>
                        <div class="col"><div class="kpi-card p-3 border-start border-4 border-danger"><span class="kpi-title text-uppercase">61-90 Days Overdue</span><h4 class="mb-0 text-danger fw-bolder mt-1" id="ap_61_90">₱0.00</h4></div></div>
                        <div class="col"><div class="kpi-card p-3 border-start border-4 border-dark bg-light"><span class="kpi-title text-uppercase">Over 90 Days</span><h4 class="mb-0 text-dark fw-bolder mt-1" id="ap_90_plus">₱0.00</h4></div></div>
                    </div>
                    <button class="btn btn-erp btn-primary mb-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#apModal"><i class="bi bi-plus-lg me-2"></i>New Vendor Bill</button>
                    <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                        <table class="erp-table" id="accApTable">
                            <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Date</th><th>Supplier</th><th>Reference No</th><th class="text-end">Amount</th><th class="text-center">Age</th><th class="text-center">Status</th><th>Remarks</th><th class="text-center">Actions</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="pv-list">
                    <button class="btn btn-erp btn-primary mb-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#pvModal"><i class="bi bi-plus-lg me-2"></i>New Payment Voucher</button>
                    <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                        <table class="erp-table" id="accPvTable">
                            <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>PV No.</th><th>Date</th><th>Supplier</th><th>Paid For (AP)</th><th>Method / Check No</th><th class="text-end">Amount Paid</th><th class="text-center">Actions</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="exp-list">
                    <button class="btn btn-erp btn-primary mb-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#expenseModal"><i class="bi bi-plus-lg me-2"></i>New Direct Expense</button>
                    <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                        <table class="erp-table" id="accExpenseTable">
                            <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Date</th><th>Expense Account</th><th>Description</th><th>Ref No.</th><th class="text-end">Amount</th><th class="text-center">Actions</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAX REPORTS TAB -->
        <div class="tab-pane fade" id="acc-tax">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-secondary mb-0" style="letter-spacing: -0.3px;">BIR Tax Compliance</h5>
            </div>
            <div class="row g-3 mb-5">
                <div class="col-md-3"><div class="kpi-card p-4 border-start border-4 border-primary"><span class="kpi-title text-uppercase fw-bold">Output VAT (12%)</span><h3 class="mb-0 text-primary fw-bolder mt-1" id="tax_output_vat">₱0.00</h3></div></div>
                <div class="col-md-3"><div class="kpi-card p-4 border-start border-4 border-info"><span class="kpi-title text-uppercase fw-bold">Input VAT (12%)</span><h3 class="mb-0 text-info fw-bolder mt-1" id="tax_input_vat">₱0.00</h3></div></div>
                <div class="col-md-3"><div class="kpi-card p-4 border-start border-4 border-danger bg-light"><span class="kpi-title text-uppercase fw-bold">Net VAT Payable</span><h3 class="mb-0 text-danger fw-bolder mt-1" id="tax_net_vat">₱0.00</h3></div></div>
                <div class="col-md-3"><div class="kpi-card p-4 border-start border-4 border-success"><span class="kpi-title text-uppercase fw-bold">EWT Withheld (1%)</span><h3 class="mb-0 text-success fw-bolder mt-1" id="tax_ewt_withheld">₱0.00</h3></div></div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-uppercase text-secondary m-0">Sales Output VAT & EWT Audit Trail</h6>
                <div class="d-flex w-50 justify-content-end">
                    <button class="btn btn-erp btn-danger me-2 shadow-sm" onclick="window.exportBIRSLSP()"><i class="bi bi-filetype-csv me-1"></i>Generate SLSP</button>
                    <button class="btn btn-erp btn-outline-success me-2 bg-white shadow-sm" onclick="window.exportTableToCSV('accTaxTable', 'Tax_Audit_Trail.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Export List</button>
                    <div class="input-group w-50 shadow-sm"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#acc-tax" placeholder="Search trail..."></div>
                </div>
            </div>
            <div class="erp-table-container" style="max-height: 50vh; overflow-y: auto;">
                <table class="erp-table" id="accTaxTable">
                    <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Source</th><th>Invoice No</th><th>Date</th><th class="text-end">Net Sales</th><th class="text-end text-primary">Output VAT (12%)</th><th class="text-end text-success">EWT (1%)</th><th class="text-end">Gross Amount</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- GENERAL LEDGER TAB -->
        <div class="tab-pane fade" id="acc-core">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <ul class="nav nav-pills" role="tablist">
                    <li class="nav-item"><button class="nav-link active btn-erp px-4" data-bs-toggle="pill" data-bs-target="#gl-journal" type="button">Journal Entries</button></li>
                    <li class="nav-item ms-2"><button class="nav-link btn-erp px-4" data-bs-toggle="pill" data-bs-target="#gl-ledger" type="button">Account Ledger</button></li>
                    <li class="nav-item ms-2"><button class="nav-link btn-erp px-4" data-bs-toggle="pill" data-bs-target="#gl-coa" type="button">Chart of Accounts</button></li>
                    <li class="nav-item ms-2"><button class="nav-link btn-erp px-4 text-success border border-success bg-light" data-bs-toggle="pill" data-bs-target="#gl-recon" type="button"><i class="bi bi-check-circle me-1"></i>Bank Recon</button></li>
                    <li class="nav-item ms-2"><button class="nav-link btn-erp px-4 text-info border border-info bg-light" data-bs-toggle="pill" data-bs-target="#gl-assets" type="button"><i class="bi bi-building-gear me-1"></i>Fixed Assets</button></li>
                </ul>
                <div class="input-group w-25 shadow-sm"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#acc-core" placeholder="Search Ledger..."></div>
            </div>
            
            <div class="tab-content">
                <!-- Journal Entries -->
                <div class="tab-pane fade show active" id="gl-journal">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#glModal"><i class="bi bi-plus-lg me-2"></i>New Journal Entry</button>
                        <button class="btn btn-erp btn-outline-success bg-white shadow-sm" onclick="window.exportTableToCSV('accGlTable', 'Journal_Entries.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Export</button>
                    </div>
                    <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                        <table class="erp-table" id="accGlTable">
                            <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Journal ID</th><th>Date</th><th>Reference No</th><th>Description</th><th class="text-end">Total Amount</th><th class="text-center">Actions</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Account Ledger -->
                <div class="tab-pane fade" id="gl-ledger">
                    <div class="d-flex justify-content-between align-items-center mb-3 kpi-card p-4 border-start border-4 border-primary">
                        <div class="w-50">
                            <label class="kpi-title text-uppercase fw-bold mb-1">Select Account</label>
                            <select id="ledger_account_id" class="form-select form-select-lg shadow-sm fw-bold"><option value="">Select Account to View Ledger...</option></select>
                        </div>
                        <div class="text-end">
                            <span class="kpi-title text-uppercase fw-bold d-block mb-1">Current Balance</span>
                            <div class="d-flex align-items-center justify-content-end">
                                <button class="btn btn-erp btn-outline-success me-3 bg-white" onclick="window.exportTableToCSV('accLedgerTable', 'Account_Ledger.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Export</button>
                                <h2 class="mb-0 text-primary fw-bolder fs-1" id="ledger_current_balance">₱0.00</h2>
                            </div>
                        </div>
                    </div>
                    <div class="erp-table-container" style="max-height: 45vh; overflow-y: auto;">
                        <table class="erp-table" id="accLedgerTable">
                            <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Date</th><th>Reference</th><th class="w-25">Description</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end text-primary">Balance</th></tr></thead>
                            <tbody><tr><td colspan="6" class="text-center text-muted py-4 font-monospace">Select an account to view its ledger entries.</td></tr></tbody>
                        </table>
                    </div>
                </div>
                
                <!-- COA -->
                <div class="tab-pane fade" id="gl-coa">
                    <button class="btn btn-erp btn-primary mb-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#coaModal"><i class="bi bi-plus-lg me-2"></i>New Account</button>
                    <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                        <table class="erp-table" id="accCoaTable">
                            <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Account Code</th><th>Account Name</th><th class="text-center">Type</th><th class="text-center">Actions</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Bank Recon -->
                <div class="tab-pane fade" id="gl-recon">
                    <div class="d-flex justify-content-between align-items-center mb-3 kpi-card p-4 border-start border-4 border-success">
                        <div class="w-50">
                            <label class="kpi-title text-uppercase fw-bold mb-1">Select Cash/Bank Account</label>
                            <select id="recon_account_id" class="form-select form-select-lg shadow-sm fw-bold"><option value="">Select Account...</option></select>
                        </div>
                        <div class="text-end">
                            <span class="kpi-title text-uppercase fw-bold d-block mb-1">Reconciled / Cleared Balance</span>
                            <h2 class="mb-0 text-success fw-bolder fs-1" id="recon_cleared_balance">₱0.00</h2>
                        </div>
                    </div>
                    <div class="erp-table-container" style="max-height: 45vh; overflow-y: auto;">
                        <table class="erp-table" id="accReconTable">
                            <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Date</th><th>Reference</th><th class="w-25">Description</th><th class="text-end">Debit (In)</th><th class="text-end">Credit (Out)</th><th class="text-center">Cleared?</th></tr></thead>
                            <tbody><tr><td colspan="6" class="text-center text-muted py-4 font-monospace">Select an account to view recon lines.</td></tr></tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Fixed Assets -->
                <div class="tab-pane fade" id="gl-assets">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#assetModal"><i class="bi bi-plus-lg me-2"></i>New Fixed Asset</button>
                        <button class="btn btn-erp btn-info text-white shadow-sm" onclick="window.runDepreciation()"><i class="bi bi-calculator me-1"></i>Run Monthly Depreciation</button>
                    </div>
                    <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                        <table class="erp-table" id="accAssetsTable">
                            <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Asset Name</th><th>Purchase Date</th><th class="text-end">Cost</th><th class="text-center">Useful Life</th><th class="text-end">Monthly Dep.</th><th class="text-end text-danger">Accum. Dep.</th><th class="text-end text-primary">Book Value</th><th class="text-center">Status</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- AUDIT & VALUATIONS TAB -->
        <div class="tab-pane fade" id="acc-audit">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-secondary mb-0" style="letter-spacing: -0.3px;">Audit & Subsidiary Ledgers</h5>
            </div>
            
            <div class="row g-4 mb-5">
                <div class="col-md-4"><div class="kpi-card p-4 border-start border-4 border-primary"><span class="kpi-title text-uppercase fw-bold">Total AR (Subledger)</span><h3 class="mb-0 text-primary fw-bolder mt-1" id="audit_ar_tot">₱0.00</h3></div></div>
                <div class="col-md-4"><div class="kpi-card p-4 border-start border-4 border-danger"><span class="kpi-title text-uppercase fw-bold">Total AP (Subledger)</span><h3 class="mb-0 text-danger fw-bolder mt-1" id="audit_ap_tot">₱0.00</h3></div></div>
                <div class="col-md-4"><div class="kpi-card p-4 border-start border-4 border-info"><span class="kpi-title text-uppercase fw-bold">Inventory Valuation</span><h3 class="mb-0 text-info fw-bolder mt-1" id="audit_inv_tot">₱0.00</h3></div></div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <h6 class="fw-bold text-uppercase text-secondary mb-3"><i class="bi bi-person-lines-fill me-2"></i>AR Subledger</h6>
                    <div class="erp-table-container" style="max-height: 40vh; overflow-y: auto;">
                        <table class="erp-table" id="auditArTable"><thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Client / Dealer</th><th class="text-end">Total Due</th></tr></thead><tbody></tbody></table>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold text-uppercase text-secondary mb-3"><i class="bi bi-shop me-2"></i>AP Subledger</h6>
                    <div class="erp-table-container" style="max-height: 40vh; overflow-y: auto;">
                        <table class="erp-table" id="auditApTable"><thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Supplier Name</th><th class="text-end">Total Due</th></tr></thead><tbody></tbody></table>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold text-uppercase text-secondary mb-3"><i class="bi bi-box-seam me-2"></i>Inventory Valuation</h6>
                    <div class="erp-table-container" style="max-height: 40vh; overflow-y: auto;">
                        <table class="erp-table" id="auditInvTable"><thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Total Value</th></tr></thead><tbody></tbody></table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ======================= ACCOUNTING MODALS ======================= -->
<div class="modal fade" id="coaModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content border-0 odoo-modal-content"><form id="coaForm"><div class="modal-control-panel"><button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">SAVE ACCOUNT</button><button type="button" class="btn btn-light btn-sm px-3 shadow-sm border text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bold">New Account</span></div><div class="modal-body p-4"><div class="odoo-sheet"><div class="row"><div class="col-md-12"><div class="odoo-field-group"><label class="odoo-label">Account Code</label><input type="text" id="coa_code" class="form-control odoo-input fw-bold" placeholder="e.g. 1010" required></div><div class="odoo-field-group"><label class="odoo-label">Account Name</label><input type="text" id="coa_name" class="form-control odoo-input" placeholder="e.g. Cash in Bank" required></div><div class="odoo-field-group"><label class="odoo-label">Type</label><select id="coa_type" class="form-select odoo-input" required><option value="Asset">Asset</option><option value="Liability">Liability</option><option value="Equity">Equity</option><option value="Revenue">Revenue</option><option value="Expense">Expense</option></select></div></div></div></div></div></form></div></div></div>
<div class="modal fade" id="apModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content border-0 odoo-modal-content"><form id="apForm"><div class="modal-control-panel"><button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">SAVE PAYABLE</button><button type="button" class="btn btn-light btn-sm px-3 shadow-sm border text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bold">New Accounts Payable</span></div><div class="modal-body p-4"><div class="odoo-sheet"><div class="row"><div class="col-md-12"><div class="odoo-field-group"><label class="odoo-label text-primary">Expense Account</label><select id="ap_account_id" class="form-select odoo-input fw-bold" required><option value="">Loading Accounts...</option></select></div><div class="odoo-field-group"><label class="odoo-label text-primary">Supplier</label><select id="ap_supplier_id" class="form-select odoo-input fw-bold" required><option value="">Loading Suppliers...</option></select></div><div class="odoo-field-group"><label class="odoo-label">Reference No.</label><input type="text" id="ap_ref" class="form-control odoo-input" required></div><div class="odoo-field-group"><label class="odoo-label">Date</label><input type="date" id="ap_date" class="form-control odoo-input" style="text-transform: none;" required></div><div class="odoo-field-group"><label class="odoo-label">Amount</label><input type="number" step="0.01" id="ap_amt" class="form-control odoo-input text-danger fw-bold" required></div><div class="odoo-field-group"><label class="odoo-label">Remarks</label><input type="text" id="ap_remarks" class="form-control odoo-input"></div></div></div></div></div></form></div></div></div>
<div class="modal fade" id="pvModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content border-0 odoo-modal-content"><form id="pvForm"><div class="modal-control-panel"><button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">SAVE VOUCHER</button><button type="button" class="btn btn-light btn-sm px-3 shadow-sm border text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bold">New Payment Voucher</span></div><div class="modal-body p-4"><div class="odoo-sheet"><div class="d-flex align-items-center mb-4"><div class="w-50 pe-4"><label class="text-muted small fw-bold mb-0 text-uppercase text-primary">PV No.</label><input type="text" id="pv_no" class="form-control odoo-title-input mb-0" placeholder="e.g. PV-1001" required></div><div class="w-50 ps-4 border-start"><label class="text-muted small fw-bold mb-0 text-uppercase">PV Date</label><input type="date" id="pv_date" class="form-control odoo-title-input mb-0" style="text-transform: none;" required></div></div><div class="row"><div class="col-md-6 pe-lg-4"><div class="odoo-field-group"><label class="odoo-label text-primary">Supplier</label><select id="pv_supplier_id" class="form-select odoo-input fw-bold" required><option value="">Loading Suppliers...</option></select></div><div class="odoo-field-group"><label class="odoo-label">AP Reference</label><select id="pv_ap_id" class="form-select odoo-input" disabled required><option value="">Select Supplier First</option></select></div><div class="odoo-field-group mt-3 pt-2 border-top"><label class="odoo-label text-primary fs-6">Amount</label><input type="number" step="0.01" id="pv_amt" class="form-control odoo-input text-primary fs-6 fw-bold" required></div></div><div class="col-md-6 ps-lg-4"><div class="odoo-field-group"><label class="odoo-label">Payment Method</label><select id="pv_method" class="form-select odoo-input" required><option value="Cash">Cash</option><option value="Check">Check</option><option value="Bank Transfer">Bank Transfer</option></select></div><div class="odoo-field-group"><label class="odoo-label">Check / Ref No.</label><input type="text" id="pv_check_no" class="form-control odoo-input"></div><div class="odoo-field-group"><label class="odoo-label">Remarks</label><input type="text" id="pv_remarks" class="form-control odoo-input"></div></div></div></div></div></form></div></div></div>
<div class="modal fade" id="expenseModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content border-0 odoo-modal-content"><form id="expenseForm"><div class="modal-control-panel"><button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">SAVE EXPENSE</button><button type="button" class="btn btn-light btn-sm px-3 shadow-sm border text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bold">New Direct Expense</span></div><div class="modal-body p-4"><div class="odoo-sheet"><div class="row"><div class="col-md-12"><div class="odoo-field-group"><label class="odoo-label text-primary">Expense Account</label><select id="exp_account_id" class="form-select odoo-input fw-bold" required><option value="">Loading Accounts...</option></select></div><div class="odoo-field-group"><label class="odoo-label">Date</label><input type="date" id="exp_date" class="form-control odoo-input" style="text-transform: none;" required></div><div class="odoo-field-group"><label class="odoo-label">Amount</label><input type="number" step="0.01" id="exp_amt" class="form-control odoo-input text-danger fw-bold" required></div><div class="odoo-field-group"><label class="odoo-label">Description / Payee</label><input type="text" id="exp_desc" class="form-control odoo-input" placeholder="e.g. Bought office supplies..." required></div><div class="odoo-field-group"><label class="odoo-label">Receipt / Ref No.</label><input type="text" id="exp_ref" class="form-control odoo-input"></div></div></div></div></div></form></div></div></div>
<div class="modal fade" id="glModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content border-0 odoo-modal-content"><form id="glForm"><div class="modal-control-panel"><button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm" id="btnSaveGl">POST JOURNAL ENTRY</button><button type="button" class="btn btn-light btn-sm px-3 shadow-sm border text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bold">New Journal Entry</span></div><div class="modal-body p-4"><div class="odoo-sheet"><div class="d-flex align-items-center mb-4"><div class="w-50 pe-4"><label class="text-muted small fw-bold mb-0 text-uppercase">Journal Date</label><input type="date" id="gl_date" class="form-control odoo-title-input mb-0" style="text-transform: none;" required></div><div class="w-50 ps-4 border-start"><label class="text-muted small fw-bold mb-0 text-uppercase">Reference No.</label><input type="text" id="gl_ref" class="form-control odoo-title-input mb-0"></div></div><div class="odoo-field-group mb-4"><label class="odoo-label text-primary" style="width:15%">Memo</label><input type="text" id="gl_desc" class="form-control odoo-input w-100" placeholder="Description of this journal entry..." required></div><hr class="my-4"><div class="d-flex justify-content-between align-items-center mb-3"><h6 class="fw-bold text-uppercase text-secondary m-0">Journal Lines</h6><button type="button" class="btn btn-sm text-primary border-0 p-0" id="btnAddJournalLine" title="Add Line"><i class="bi bi-plus-circle-fill" style="font-size: 2rem; pointer-events: none;"></i></button></div><table class="table table-borderless" id="journalItemsTable"><thead class="border-bottom"><tr><th>Account</th><th style="width: 20%;">Debit</th><th style="width: 20%;">Credit</th><th style="width: 5%;"></th></tr></thead><tbody id="journalItemsTbody"></tbody></table><div class="d-flex justify-content-end align-items-center gap-4 mt-3 p-3 bg-light border-top"><div><span class="text-muted small text-uppercase">Total Debit:</span> <h4 class="mb-0 d-inline text-success" id="j_total_debit">₱0.00</h4></div><div><span class="text-muted small text-uppercase">Total Credit:</span> <h4 class="mb-0 d-inline text-danger" id="j_total_credit">₱0.00</h4></div><div class="border-start ps-4"><span id="j_balance_status" class="badge bg-success">BALANCED</span></div></div></div></div></form></div></div></div>
<div class="modal fade" id="assetModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content border-0 odoo-modal-content"><form id="assetForm"><div class="modal-control-panel"><button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">SAVE ASSET</button><button type="button" class="btn btn-light btn-sm px-3 shadow-sm border text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bold">New Fixed Asset</span></div><div class="modal-body p-4"><div class="odoo-sheet"><div class="row"><div class="col-md-12"><div class="odoo-field-group"><label class="odoo-label">Asset Name</label><input type="text" id="ast_name" class="form-control odoo-input fw-bold" placeholder="e.g. Delivery Truck" required></div><div class="odoo-field-group"><label class="odoo-label">Purchase Date</label><input type="date" id="ast_date" class="form-control odoo-input" style="text-transform: none;" required></div><div class="odoo-field-group"><label class="odoo-label">Purchase Cost</label><input type="number" step="0.01" id="ast_cost" class="form-control odoo-input text-primary fw-bold" required></div><div class="odoo-field-group"><label class="odoo-label">Useful Life (Months)</label><input type="number" id="ast_life" class="form-control odoo-input text-primary fw-bold" required></div></div></div></div></div></form></div></div></div>