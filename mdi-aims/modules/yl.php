<div id="module-YL" class="module-container p-2">

    <!-- ERP Custom Styling -->
    <style>
        .erp-header { font-weight: 800; color: #1e293b; letter-spacing: -0.5px; }
        .erp-nav { border-bottom: 2px solid #e2e8f0; margin-bottom: 25px; }
        .erp-nav .nav-link { color: #64748b; font-weight: 700; padding: 12px 20px; border: none; border-bottom: 3px solid transparent; border-radius: 0; transition: all 0.2s ease; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
        .erp-nav .nav-link:hover { color: #334155; border-bottom-color: #cbd5e1; background: transparent; }
        .erp-nav .nav-link.active { color: var(--theme-green, #10b981); border-bottom-color: var(--theme-green, #10b981); background: transparent; }
        
        .kpi-card { border-radius: 16px; transition: transform 0.2s ease, box-shadow 0.2s ease; border: 1px solid rgba(0,0,0,0.03); background: #fff; z-index: 1; }
        .kpi-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.06) !important; }
        .kpi-title { font-size: 0.75rem; letter-spacing: 0.8px; color: #64748b; }
        
        .erp-table-container { border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .erp-table { border-spacing: 0; border-collapse: collapse; width: 100%; margin: 0; }
        .erp-table thead th { background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
        .erp-table tbody td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; font-size: 0.875rem; }
        .erp-table tbody tr:hover td { background: #f8fafc; }
        
        .btn-erp { border-radius: 6px; font-weight: 600; letter-spacing: 0.3px; padding: 6px 14px; transition: all 0.2s; font-size: 0.85rem; text-transform: uppercase; }
    </style>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="erp-header mb-0"><i class="bi bi-person-badge text-muted me-2"></i>YL Management</h4>
    </div>

    <ul class="nav erp-nav" id="ylTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#yl-orders" type="button">Stock Orders</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#yl-drs" type="button">Delivery Receipts</button></li>
        <li class="nav-item"><button class="nav-link text-danger" data-bs-toggle="tab" data-bs-target="#yl-returns" type="button">Stock Returns</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#yl-invoices" type="button">Invoices</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#yl-collections" type="button">Collection Receipts</button></li>
        <li class="nav-item"><button class="nav-link text-warning" data-bs-toggle="tab" data-bs-target="#yl-rebates" type="button"><i class="bi bi-gift-fill me-1"></i>Rebates</button></li>
        <li class="nav-item ms-auto"><button class="nav-link text-danger border border-danger bg-danger-subtle rounded" data-bs-toggle="tab" data-bs-target="#yl-remittance" type="button"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Daily Remittance</button></li>
        <li class="nav-item ms-2"><button class="nav-link text-success" data-bs-toggle="tab" data-bs-target="#yl-reports" type="button"><i class="bi bi-bar-chart-fill me-2"></i>Sales Report</button></li>
    </ul>
    <div class="tab-content" id="ylTabsContent">

        <div class="tab-pane fade show active" id="yl-orders">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#ylOrderModal"><i class="bi bi-plus-lg me-2"></i>New Stock Order</button>
                <div class="input-group w-25 shadow-sm"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#ylOrdersTable" placeholder="Search orders..."></div>
            </div>
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                <table class="erp-table" id="ylOrdersTable">
                    <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>SO No.</th><th>Date</th><th>Dealer Name</th><th class="text-center">Total Items</th><th class="text-end">Total Amount</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="yl-drs">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#ylDrModal"><i class="bi bi-truck me-2"></i>Issue Delivery Receipt</button>
                <div class="input-group w-25 shadow-sm"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#ylDrTable" placeholder="Search deliveries..."></div>
            </div>
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                <table class="erp-table" id="ylDrTable">
                    <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>DR No.</th><th>Date</th><th>SO Ref</th><th>Dealer Name</th><th class="text-center">Total Items</th><th class="text-end">Total Amount</th><th class="text-center">Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="yl-returns">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#returnModal"><i class="bi bi-arrow-return-left me-2"></i>Log Stock Return</button>
                <div class="input-group w-25 shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#returnsTable" placeholder="Search returns...">
                </div>
            </div>
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                <table class="erp-table" id="returnsTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr><th>Return No.</th><th>Date</th><th>Warehouse</th><th>Return Type</th><th>Reference No.</th><th class="text-center">Total Qty</th><th class="text-danger text-end">Total Amount</th><th>Remarks</th><th class="text-center">Actions</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="yl-invoices">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#ylInvoiceModal"><i class="bi bi-receipt me-2"></i>Issue Invoice</button>
                <div class="input-group w-25 shadow-sm"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#ylInvoicesTable" placeholder="Search invoices..."></div>
            </div>
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                <table class="erp-table" id="ylInvoicesTable">
                    <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Invoice No.</th><th>Date</th><th>DR Ref(s)</th><th>Dealer Name</th><th class="text-end">Net Amount</th><th class="text-end text-primary">Amount Due</th><th class="text-center">Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="yl-collections">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#ylCollectionModal"><i class="bi bi-cash-coin me-2"></i>Receive Payment</button>
                <div class="input-group w-25 shadow-sm"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#ylCollectionsTable" placeholder="Search receipts..."></div>
            </div>
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                <table class="erp-table" id="ylCollectionsTable">
                    <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>CR No.</th><th>Date</th><th>Dealer Name</th><th class="w-25">Invoices Covered</th><th class="text-end text-success">Amount Collected</th><th class="text-center">Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="yl-rebates">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#ylRebateModal"><i class="bi bi-calculator me-2"></i>Calculate Monthly Rebate</button>
                <div class="input-group w-25 shadow-sm"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#ylRebatesTable" placeholder="Search rebates..."></div>
            </div>
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                <table class="erp-table" id="ylRebatesTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr>
                            <th>Calculated On</th>
                            <th>Dealer Name</th>
                            <th>Rebate Date</th>
                            <th>Invoice Ref</th>
                            <th class="text-end text-primary">Dealer Disc.</th>
                            <th class="text-end text-warning">Trade Disc.</th>
                            <th class="text-end text-danger">Sales Rebate</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <!-- DAILY REMITTANCE TAB -->
        <div class="tab-pane fade" id="yl-remittance">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted text-uppercase fw-bold m-0">Target Date:</label>
                    <input type="date" id="yl_remit_date" class="form-control form-control-sm fw-bold shadow-sm" style="text-transform:none; width: 135px;">
                    <label class="small text-muted text-uppercase fw-bold m-0 ms-2">Center:</label>
                    <select id="yl_remit_center" class="form-select form-select-sm fw-bold shadow-sm" style="width: 200px;"><option value="">All Centers</option></select>
                    <button class="btn btn-erp btn-primary shadow-sm btn-sm ms-2" onclick="window.loadYlRemittanceTable()"><i class="bi bi-arrow-clockwise me-1"></i>Load Data</button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-erp btn-outline-success bg-white shadow-sm btn-sm" onclick="window.exportTableToCSV('ylRemittanceTable', 'YL_Daily_Remittance.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Export</button>
                    <button class="btn btn-erp btn-danger shadow-sm btn-sm" onclick="window.printYlRemittance()"><i class="bi bi-printer me-1"></i>Print Paper Form</button>
                </div>
            </div>
            
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                <table class="erp-table" id="ylRemittanceTable" style="font-size: 0.8rem;">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr>
                            <th>YL Name</th>
                            <th>DR Nos.</th>
                            <th class="text-end">Added Stock</th>
                            <th class="text-end text-success">Today Sales</th>
                            <th>Inv Nos.</th>
                            <th class="text-end text-primary">Today Col.</th>
                            <th class="text-end">Short/(Excess)</th>
                            <th class="text-end">Beg. AR</th>
                            <th class="text-end text-danger">End. AR</th>
                            <th class="text-end">Beg. Stock</th>
                            <th class="text-end text-info">End. Stock</th>
                        </tr>
                    </thead>
                    <tbody><tr><td colspan="11" class="text-center py-4 text-muted font-monospace">Select a date and click Load Data.</td></tr></tbody>
                    <tfoot style="position: sticky; bottom: 0; background: #f8fafc; font-weight: bold; border-top: 2px solid #e2e8f0; z-index: 1;">
                        <tr>
                            <td colspan="2" class="text-end">GRAND TOTAL:</td>
                            <td class="text-end" id="remit_tot_add">0</td>
                            <td class="text-end text-success fs-6" id="remit_tot_sales">₱ 0.00</td>
                            <td></td>
                            <td class="text-end text-primary fs-6" id="remit_tot_col">₱ 0.00</td>
                            <td class="text-end fs-6" id="remit_tot_short">₱ 0.00</td>
                            <td class="text-end" id="remit_tot_beg_ar">₱ 0.00</td>
                            <td class="text-end text-danger fs-6" id="remit_tot_end_ar">₱ 0.00</td>
                            <td class="text-end" id="remit_tot_beg_stock">0</td>
                            <td class="text-end text-info fs-6" id="remit_tot_end_stock">0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="yl-reports">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted text-uppercase fw-bold m-0">From:</label>
                    <input type="date" id="yl_rep_from" class="form-control form-control-sm fw-bold shadow-sm" style="text-transform:none; width: 125px;">
                    <label class="small text-muted text-uppercase fw-bold m-0">To:</label>
                    <input type="date" id="yl_rep_to" class="form-control form-control-sm fw-bold shadow-sm" style="text-transform:none; width: 125px;">
                    <button class="btn btn-erp btn-success shadow-sm btn-sm" onclick="window.loadYlReport()"><i class="bi bi-arrow-clockwise me-1"></i>Generate</button>
                </div>
                <div class="d-flex align-items-center gap-2 border-start ps-3 ms-1">
                    <button class="btn btn-erp btn-outline-success bg-white shadow-sm btn-sm" onclick="window.exportTableToCSV('ylReportTable', 'YL_Sales_Report.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Export</button>
                    <button class="btn btn-erp btn-outline-danger bg-white shadow-sm btn-sm" onclick="window.printYlReport()"><i class="bi bi-printer me-1"></i>Print Report</button>
                </div>
            </div>
            
            <div class="row g-4 mb-4" id="ylReportKPIs">
                <div class="col-md-3"><div class="kpi-card p-4 border-start border-4 border-primary"><span class="kpi-title text-uppercase fw-bold">Total Gross Sales</span><h3 class="mb-0 text-primary fw-bolder mt-1" id="yl_rep_gross">₱ 0.00</h3></div></div>
                <div class="col-md-3"><div class="kpi-card p-4 border-start border-4 border-primary"><span class="kpi-title text-uppercase fw-bold">Total Output VAT</span><h3 class="mb-0 text-primary fw-bolder mt-1" id="yl_rep_vat">₱ 0.00</h3></div></div>
                <div class="col-md-3"><div class="kpi-card p-4 border-start border-4 border-success"><span class="kpi-title text-uppercase fw-bold">Total Collected</span><h3 class="mb-0 text-success fw-bolder mt-1" id="yl_rep_paid">₱ 0.00</h3></div></div>
                <div class="col-md-3"><div class="kpi-card p-4 border-start border-4 border-danger"><span class="kpi-title text-uppercase fw-bold">Total Unpaid (AR)</span><h3 class="mb-0 text-danger fw-bolder mt-1" id="yl_rep_unpaid">₱ 0.00</h3></div></div>
            </div>
            <div class="erp-table-container" style="max-height: 45vh; overflow-y: auto;" id="ylReportWrapper">
                <table class="erp-table" id="ylReportTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr><th>Date</th><th>Invoice No.</th><th>SO No.</th><th>DR No.</th><th>CR No.</th><th>Dealer Name</th><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end text-primary">Total Amount</th><th class="text-center">Status</th></tr>
                    </thead>
                    <tbody><tr><td colspan="11" class="text-center py-4 text-muted font-monospace">Select dates and click Generate to run report.</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ======================= YL MODALS ======================= -->
<!-- Order Modal -->
<div class="modal fade" id="ylOrderModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content border-0 odoo-modal-content"><form id="ylOrderForm"><div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center"><button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Order</button><button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-file-earmark-plus me-1"></i>New Stock Order</span></div><div class="modal-body p-4 bg-light"><div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm"><div class="d-flex align-items-center mb-4"><div class="w-50 pe-4"><label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">SO Number</label><input type="text" id="yl_so_no" class="form-control odoo-title-input mb-0 fs-4" readonly placeholder="Auto-generated"></div><div class="w-50 ps-4 border-start"><label class="text-muted small fw-bolder mb-1 text-uppercase">Order Date</label><input type="date" id="yl_so_date" class="form-control odoo-title-input mb-0 fs-5" style="text-transform: none;" required></div></div><div class="row mb-5"><div class="col-md-6 pe-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Select Dealer</label><select id="yl_dealer_id" class="form-select odoo-input fw-bold" required></select></div></div><div class="col-md-6 ps-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label">Dealer Area</label><input type="text" id="yl_area" class="form-control odoo-input bg-light" disabled></div></div></div><div class="d-flex justify-content-between align-items-center mb-3"><h6 class="fw-bolder text-uppercase text-secondary m-0">Order Lines</h6><button type="button" class="btn btn-sm btn-outline-primary fw-bold text-uppercase rounded-pill px-3" id="btnAddYlSoItem"><i class="bi bi-plus-lg me-1"></i>Add Product</button></div><table class="table table-borderless table-sm mb-0" id="ylSoItemsTable"><thead class="border-bottom text-uppercase text-muted" style="font-size: 0.75rem;"><tr><th class="pb-2">Product Description</th><th class="pb-2" style="width: 15%;">Quantity</th><th class="pb-2" style="width: 15%;">Unit Price</th><th class="pb-2 text-end" style="width: 20%;">Subtotal</th><th style="width: 5%;"></th></tr></thead><tbody id="ylSoItemsTbody"></tbody></table><div class="d-flex justify-content-end gap-5 mt-4 pt-3 border-top"><div><span class="text-muted small text-uppercase fw-bold">Total Items</span> <h3 class="mb-0 text-dark fw-bolder text-end" id="yl_so_total_qty">0</h3></div><div><span class="text-muted small text-uppercase fw-bold">Total Amount</span> <h3 class="mb-0 text-primary fw-bolder text-end" id="yl_so_total_amount">₱ 0.00</h3></div></div></div></div></form></div></div></div>

<!-- Delivery Receipt Modal -->
<div class="modal fade" id="ylDrModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="ylDrForm">
                <input type="hidden" id="yl_dr_items_data">
                <input type="hidden" id="yl_dr_total_amt">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-bold shadow-sm text-uppercase">Issue DR</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-truck me-1"></i>New Delivery Receipt</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm">
                        <div class="d-flex align-items-center mb-5">
                            <div class="w-50 pe-4">
                                <label class="text-muted small fw-bolder mb-1 text-uppercase text-success">DR Number</label>
                                <input type="text" id="yl_dr_no" class="form-control odoo-title-input mb-0 fs-4" readonly placeholder="Auto-generated">
                            </div>
                            <div class="w-50 ps-4 border-start">
                                <label class="text-muted small fw-bolder mb-1 text-uppercase">Delivery Date</label>
                                <input type="date" id="yl_dr_date" class="form-control odoo-title-input mb-0 fs-5" style="text-transform: none;" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 pe-lg-4">
                                <div class="odoo-field-group mb-4">
                                    <label class="odoo-label text-success">Select Pending SO</label>
                                    <select id="yl_dr_so_id" class="form-select odoo-input fw-bold" required></select>
                                </div>
                            </div>
                            <div class="col-md-6 ps-lg-4">
                                <div class="odoo-field-group mb-3">
                                    <label class="odoo-label">Dealer Name</label>
                                    <input type="text" id="yl_dr_dealer" class="form-control odoo-input bg-light" disabled>
                                </div>
                                <div class="odoo-field-group mb-3">
                                    <label class="odoo-label text-info">Source Warehouse</label>
                                    <input type="text" id="yl_dr_warehouse_name" class="form-control odoo-input fw-bold text-info bg-light" disabled placeholder="Pending...">
                                </div>
                            </div>
                        </div>
                        <h6 class="fw-bolder text-uppercase text-secondary mt-4 mb-3 border-bottom pb-2">Delivery Items</h6>
                        <table class="table table-borderless table-sm mb-0" id="ylDrItemsTable">
                            <thead class="text-uppercase text-muted" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="pb-2">Product Description</th>
                                    <th class="pb-2 text-center" style="width: 15%;">Quantity</th>
                                    <th class="pb-2 text-end" style="width: 20%;">Unit Price</th>
                                    <th class="pb-2 text-end" style="width: 20%;">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="ylDrItemsTbody">
                                <tr><td colspan="4" class="text-center py-4 text-muted font-monospace">Select a Pending SO to view items.</td></tr>
                            </tbody>
                        </table>
                        
                        <div class="d-flex justify-content-end gap-5 mt-4 pt-3 border-top">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold">Total Items</span> 
                                <h3 class="mb-0 text-success fw-bolder text-end" id="yl_dr_total_qty">0</h3>
                                <input type="hidden" id="yl_dr_qty">
                            </div>
                            <div>
                                <span class="text-muted small text-uppercase fw-bold">Total Amount</span> 
                                <h3 class="mb-0 text-success fw-bolder text-end" id="yl_dr_total_amt_disp">₱ 0.00</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Returns Modal -->
<div class="modal fade" id="returnModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content border-0 odoo-modal-content"><form id="returnForm"><div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center"><button type="submit" class="btn btn-danger btn-sm px-4 fw-bold shadow-sm text-uppercase">Post Return</button><button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-arrow-return-left me-1"></i>New Stock Return</span></div><div class="modal-body p-4 bg-light"><div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm border-top border-4 border-danger"><div class="d-flex align-items-center mb-5"><div class="w-50 pe-4"><label class="text-muted small fw-bolder mb-1 text-uppercase text-danger">Return Number</label><input type="text" id="rt_no" class="form-control odoo-title-input mb-0 fs-4" placeholder="e.g. RT-1001" required></div><div class="w-50 ps-4 border-start"><label class="text-muted small fw-bolder mb-1 text-uppercase">Return Date</label><input type="date" id="rt_date" class="form-control odoo-title-input mb-0 fs-5" style="text-transform: none;" required></div></div><div class="row mb-5"><div class="col-md-6 pe-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Warehouse</label><select id="rt_warehouse_id" class="form-select odoo-input fw-bold" required></select></div><div class="odoo-field-group mb-3"><label class="odoo-label text-danger">Return Type</label><select id="rt_type" class="form-select odoo-input fw-bold text-danger" required><option value="Customer Return">Customer Return (Adds to Stock)</option><option value="Return to Supplier">Return to Supplier (Deducts from Stock)</option></select></div></div><div class="col-md-6 ps-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label">Reference No.</label><select id="rt_ref" class="form-select odoo-input fw-bold" required><option value="">Loading References...</option></select></div><div class="odoo-field-group mb-3"><label class="odoo-label">Remarks / Reason</label><input type="text" id="rt_remarks" class="form-control odoo-input" placeholder="e.g. Damaged Goods, Expired..."></div></div></div><div class="d-flex justify-content-between align-items-center mb-3"><h6 class="fw-bolder text-uppercase text-secondary m-0">Returned Items</h6><button type="button" class="btn btn-sm btn-outline-danger fw-bold text-uppercase rounded-pill px-3" id="btnAddRtItem"><i class="bi bi-plus-lg me-1"></i>Add Product</button></div><table class="table table-borderless table-sm mb-0" id="rtItemsTable"><thead class="border-bottom text-uppercase text-muted" style="font-size: 0.75rem;">
    <tr><th class="pb-2">Product Description</th><th class="pb-2" style="width: 15%;">Return Qty</th><th class="pb-2" style="width: 15%;">Unit Price</th><th class="pb-2" style="width: 15%;">Subtotal</th><th class="pb-2" style="width: 20%;">Condition</th><th style="width: 5%;"></th></tr>
</thead><tbody id="rtItemsTbody"></tbody></table><div class="d-flex justify-content-end gap-5 mt-4 pt-3 border-top">
    <div><span class="text-muted small text-uppercase fw-bold">Total Returned</span> <h3 class="mb-0 text-danger fw-bolder text-end" id="rt_total_qty">0</h3></div>
    <div><span class="text-muted small text-uppercase fw-bold">Total Amount</span> <h3 class="mb-0 text-danger fw-bolder text-end" id="rt_total_amount">₱ 0.00</h3></div>
</div></div></div></form></div></div></div>

<!-- Invoice Modal (STRIPPED DOWN - NO DISCOUNT TOGGLES) -->
<div class="modal fade" id="ylInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="ylInvoiceForm">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Post Invoice</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-receipt me-1"></i>New Sales Invoice</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm">
                        <div class="d-flex align-items-center mb-5">
                            <div class="w-50 pe-4">
                                <label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">Invoice Number</label>
                                <input type="text" id="yl_inv_no" class="form-control odoo-title-input mb-0 fs-4" required>
                            </div>
                            <div class="w-50 ps-4 border-start">
                                <label class="text-muted small fw-bolder mb-1 text-uppercase">Invoice Date</label>
                                <input type="date" id="yl_inv_date" class="form-control odoo-title-input mb-0 fs-5" style="text-transform: none;" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 pe-lg-4">
                                <div class="odoo-field-group mb-5">
                                    <label class="odoo-label text-primary">Select Dealer</label>
                                    <select id="yl_inv_dealer_id" class="form-select odoo-input fw-bold" required></select>
                                </div>
                            </div>
                            <div class="col-md-6 ps-lg-4">
                                <div class="odoo-field-group mb-3">
                                    <label class="odoo-label text-primary">Select DR(s)</label>
                                    <div id="yl_inv_drs_container" class="border p-2 rounded bg-light" style="max-height: 150px; overflow-y: auto;">
                                        <small class="text-muted p-2 d-block text-center font-monospace">Select a dealer first to view available Delivery Receipts.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="fw-bolder text-uppercase text-secondary mt-4 mb-3 border-bottom pb-2">Consolidated Items</h6>
                        <table class="table table-borderless table-sm mb-0" id="ylInvItemsTable">
                            <thead class="text-uppercase text-muted" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="pb-2">Product Description</th>
                                    <th class="pb-2 text-center" style="width: 15%;">Quantity</th>
                                    <th class="pb-2 text-end" style="width: 20%;">Unit Price</th>
                                    <th class="pb-2 text-end" style="width: 20%;">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="ylInvItemsTbody">
                                <tr><td colspan="4" class="text-center py-4 text-muted font-monospace">Select DRs to view items.</td></tr>
                            </tbody>
                        </table>
                        
                        <div class="row border-top pt-4 mt-4">
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless fw-bold mb-0">
                                    <tbody class="text-end">
                                        <tr><td class="text-muted w-50">Total Gross:</td><td id="yl_inv_gross_disp" class="fs-6">₱ 0.00</td></tr>
                                        <tr><td class="text-muted">Dealer Discount:</td><td id="yl_inv_dealer_disc_disp" class="text-danger fs-6">₱ 0.00</td></tr>
                                        <tr><td class="text-muted border-bottom pb-2">Net Amount:</td><td id="yl_inv_net_disp" class="border-bottom pb-2 fs-6">₱ 0.00</td></tr>
                                        <tr><td class="fs-4 text-primary pt-4 text-uppercase">Amount Due:</td><td class="fs-3 text-primary pt-3 fw-bolder" id="yl_inv_due_disp">₱ 0.00</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Collection Modal -->
<div class="modal fade" id="ylCollectionModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="ylCollectionForm">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-bold shadow-sm text-uppercase">Post Receipt</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-cash-stack me-1"></i>New Collection Receipt</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm">
                        <div class="d-flex align-items-center mb-5">
                            <div class="w-50 pe-4">
                                <label class="text-muted small fw-bolder mb-1 text-uppercase text-success">CR Number</label>
                                <input type="text" id="yl_cr_no" class="form-control odoo-title-input mb-0 fs-4" required>
                            </div>
                            <div class="w-50 ps-4 border-start">
                                <label class="text-muted small fw-bolder mb-1 text-uppercase">Collection Date</label>
                                <input type="date" id="yl_cr_date" class="form-control odoo-title-input mb-0 fs-5" style="text-transform: none;" required>
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-6 pe-lg-4">
                                <div class="odoo-field-group mb-3">
                                    <label class="odoo-label text-success">Select Dealer</label>
                                    <select id="yl_cr_dealer_id" class="form-select odoo-input fw-bold" required></select>
                                </div>
                            </div>
                        </div>
                        <h6 class="fw-bolder text-uppercase text-secondary mb-3">Unpaid Invoices to Collect</h6>
                        <table class="table table-borderless table-sm mb-4" id="ylCrInvoicesTable">
                            <thead class="border-bottom text-uppercase text-muted" style="font-size: 0.75rem;">
                                <tr>
                                    <th style="width: 5%;" class="pb-2"></th>
                                    <th class="pb-2">Invoice No</th>
                                    <th class="text-end pb-2">Amount Due</th>
                                </tr>
                            </thead>
                            <tbody id="ylCrInvoicesTbody">
                                <tr><td colspan="3" class="text-center text-muted py-4 font-monospace">Select a dealer to view unpaid invoices.</td></tr>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between align-items-end p-4 bg-light rounded-3 border">
                            <div class="text-end ms-auto">
                                <span class="text-muted small text-uppercase fw-bold">Total Collected</span> 
                                <h2 class="mb-0 text-success fw-bolder mt-1" id="yl_cr_total_amount">₱ 0.00</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rebate Modal -->
<div class="modal fade" id="ylRebateModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="ylRebateForm">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-warning btn-sm px-4 fw-bold shadow-sm text-uppercase">Calculate Rebate</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-gift me-1"></i>Calculate Monthly Rebate</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm border-top border-4 border-warning">
                        <div class="row mb-4">
                            <div class="col-md-6 pe-lg-4">
                                <div class="odoo-field-group mb-3">
                                    <label class="odoo-label text-warning">Select Dealer</label>
                                    <select id="yl_rebate_dealer_id" class="form-select odoo-input fw-bold" required></select>
                                </div>
                            </div>
                            <div class="col-md-6 ps-lg-4">
                                <div class="odoo-field-group mb-3">
                                    <label class="odoo-label text-warning">Rebate Month</label>
                                    <input type="month" id="yl_rebate_month" class="form-control odoo-input fw-bold" required>
                                </div>
                            </div>
                        </div>
                        
                        <div id="yl_rebate_preview" class="mt-4" style="display: none;">
                            <h6 class="fw-bolder text-uppercase text-secondary border-bottom pb-2">Rebate Calculation Preview</h6>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded text-center">
                                        <small class="text-muted fw-bold text-uppercase">Total Original Qty</small>
                                        <h4 class="text-primary fw-bolder mt-1" id="yl_rebate_orig_qty">0</h4>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded text-center">
                                        <small class="text-muted fw-bold text-uppercase">Total Light Qty</small>
                                        <h4 class="text-info fw-bolder mt-1" id="yl_rebate_light_qty">0</h4>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-warning-subtle border border-warning-subtle rounded text-center">
                                        <small class="text-warning-emphasis fw-bold text-uppercase">Total Calculated Rebate</small>
                                        <h3 class="text-warning-emphasis fw-bolder mt-1" id="yl_rebate_total_amt">₱ 0.00</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>