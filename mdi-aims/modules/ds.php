<div id="module-DS" class="module-container p-2">
    
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
        <h4 class="erp-header mb-0"><i class="bi bi-truck text-muted me-2"></i>DS Management</h4>
    </div>
    
    <ul class="nav erp-nav" id="dsTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ds-orders" type="button">Stock Orders</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#ds-invoices" type="button">Invoices</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#ds-collections" type="button">Collection Receipts</button></li>
        <li class="nav-item ms-auto"><button class="nav-link text-success" data-bs-toggle="tab" data-bs-target="#ds-reports" type="button"><i class="bi bi-bar-chart-fill me-2"></i>Sales Report</button></li>
    </ul>

    <div class="tab-content" id="dsTabsContent">
        
        <div class="tab-pane fade show active" id="ds-orders">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#dsOrderModal"><i class="bi bi-plus-lg me-2"></i>Create Order</button>
                <div class="input-group w-25 shadow-sm"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#dsOrdersTable" placeholder="Search orders..."></div>
            </div>
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                <table class="erp-table" id="dsOrdersTable">
                    <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>SO No.</th><th>Date</th><th>Customer Name</th><th>Branch</th><th>TIN</th><th class="text-center">Total Items</th><th class="text-end">Total Amount</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="ds-invoices">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#dsInvoiceModal"><i class="bi bi-receipt me-2"></i>Issue Invoice</button>
                <div class="input-group w-25 shadow-sm"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#dsInvoicesTable" placeholder="Search invoices..."></div>
            </div>
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                <table class="erp-table" id="dsInvoicesTable">
                    <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Invoice No.</th><th>Date</th><th>SO Ref</th><th>Customer Name</th><th class="text-end">Net Amount</th><th class="text-end text-primary">Amount Due</th><th class="text-center">Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="ds-collections">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#dsCollectionModal"><i class="bi bi-cash-coin me-2"></i>Receive Payment</button>
                <div class="input-group w-25 shadow-sm"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#dsCollectionsTable" placeholder="Search receipts..."></div>
            </div>
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                <table class="erp-table" id="dsCollectionsTable">
                    <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>CR No.</th><th>Date</th><th>Customer Name</th><th class="w-25">Invoices Covered</th><th class="text-end text-success">Amount Collected</th><th class="text-center">Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- SALES REPORT TAB -->
        <div class="tab-pane fade" id="ds-reports">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted text-uppercase fw-bold m-0">From:</label>
                    <input type="date" id="ds_rep_from" class="form-control form-control-sm fw-bold shadow-sm" style="text-transform:none; width: 125px;">
                    <label class="small text-muted text-uppercase fw-bold m-0">To:</label>
                    <input type="date" id="ds_rep_to" class="form-control form-control-sm fw-bold shadow-sm" style="text-transform:none; width: 125px;">
                    <button class="btn btn-erp btn-success shadow-sm btn-sm ms-2" onclick="window.loadDsReport()"><i class="bi bi-arrow-clockwise me-1"></i>Generate</button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-erp btn-outline-success bg-white shadow-sm btn-sm" onclick="window.exportTableToCSV('dsReportTable', 'DS_Sales_Report.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Export CSV</button>
                    <button class="btn btn-erp btn-outline-danger bg-white shadow-sm btn-sm" onclick="window.printDsReport()"><i class="bi bi-printer me-1"></i>Print Report</button>
                </div>
            </div>
            
            <div class="row g-4 mb-4" id="dsReportKPIs">
                <div class="col-md-3"><div class="kpi-card p-4 border-start border-4 border-primary"><span class="kpi-title text-uppercase fw-bold">Total Gross Sales</span><h3 class="mb-0 text-primary fw-bolder mt-1" id="ds_rep_gross">₱0.00</h3></div></div>
                <div class="col-md-3"><div class="kpi-card p-4 border-start border-4 border-primary"><span class="kpi-title text-uppercase fw-bold">Total Output VAT</span><h3 class="mb-0 text-primary fw-bolder mt-1" id="ds_rep_vat">₱0.00</h3></div></div>
                <div class="col-md-3"><div class="kpi-card p-4 border-start border-4 border-success"><span class="kpi-title text-uppercase fw-bold">Total Collected</span><h3 class="mb-0 text-success fw-bolder mt-1" id="ds_rep_paid">₱0.00</h3></div></div>
                <div class="col-md-3"><div class="kpi-card p-4 border-start border-4 border-danger"><span class="kpi-title text-uppercase fw-bold">Total Unpaid (AR)</span><h3 class="mb-0 text-danger fw-bolder mt-1" id="ds_rep_unpaid">₱0.00</h3></div></div>
            </div>

            <div class="erp-table-container" style="max-height: 45vh; overflow-y: auto;" id="dsReportWrapper">
                <table class="erp-table" id="dsReportTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr><th>Date</th><th>Invoice No.</th><th>SO No.</th><th>CR No.</th><th>Customer Name</th><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end text-primary">Total Amount</th><th class="text-center">Status</th></tr>
                    </thead>
                    <tbody><tr><td colspan="10" class="text-center py-4 text-muted font-monospace">Select dates and click Generate to run report.</td></tr></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- ======================= DS MODALS ======================= -->
<!-- Order Modal -->
<div class="modal fade" id="dsOrderModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content border-0 odoo-modal-content"><form id="dsOrderForm"><input type="hidden" id="ds_type_order" value="DS"><div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center"><button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Order</button><button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-file-earmark-plus me-1"></i>New Stock Order</span></div><div class="modal-body p-4 bg-light"><div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm">
    <div class="d-flex align-items-center mb-4"><div class="w-50 pe-4"><label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">SO Number</label><input type="text" id="so_no" class="form-control odoo-title-input mb-0 fs-4" readonly placeholder="Auto-generated"></div><div class="w-50 ps-4 border-start"><label class="text-muted small fw-bolder mb-1 text-uppercase">Order Date</label><input type="date" id="so_date" class="form-control odoo-title-input mb-0 fs-5" style="text-transform: none;" required></div></div>
    
    <div class="row mb-5">
        <div class="col-md-6 pe-lg-4">
            <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Select Customer</label><select id="so_outlet_id" class="form-select odoo-input fw-bold" required></select></div>
            <div class="odoo-field-group mb-3"><label class="odoo-label">Customer Address</label><input type="text" id="so_outlet_address" class="form-control odoo-input bg-light" disabled></div>
        </div>
        <div class="col-md-6 ps-lg-4">
            <div class="odoo-field-group mb-3"><label class="odoo-label">Branch</label><input type="text" id="so_outlet_branch" class="form-control odoo-input bg-light" disabled></div>
            <div class="odoo-field-group mb-3"><label class="odoo-label">TIN</label><input type="text" id="so_outlet_tin" class="form-control odoo-input bg-light" disabled></div>
        </div>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-3"><h6 class="fw-bolder text-uppercase text-secondary m-0">Order Lines</h6><button type="button" class="btn btn-sm btn-outline-primary fw-bold text-uppercase rounded-pill px-3" id="btnAddSoItem"><i class="bi bi-plus-lg me-1"></i>Add Product</button></div><table class="table table-borderless table-sm mb-0" id="soItemsTable"><thead class="border-bottom text-uppercase text-muted" style="font-size: 0.75rem;"><tr><th class="pb-2">Product Description</th><th class="pb-2" style="width: 15%;">Quantity</th><th class="pb-2" style="width: 15%;">Unit Price</th><th class="pb-2 text-end" style="width: 20%;">Subtotal</th><th style="width: 5%;"></th></tr></thead><tbody id="soItemsTbody"></tbody></table><div class="d-flex justify-content-end gap-5 mt-4 pt-3 border-top"><div><span class="text-muted small text-uppercase fw-bold">Total Items</span> <h3 class="mb-0 text-dark fw-bolder text-end" id="so_total_qty">0</h3></div><div><span class="text-muted small text-uppercase fw-bold">Total Amount</span> <h3 class="mb-0 text-primary fw-bolder text-end" id="so_total_amount">₱0.00</h3></div></div></div></div></form></div></div></div>

<!-- Invoice Modal -->
<div class="modal fade" id="dsInvoiceModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content border-0 odoo-modal-content"><form id="dsInvoiceForm"><input type="hidden" id="ds_type_inv" value="DS"><div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center"><button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Post Invoice</button><button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-receipt me-1"></i>New Sales Invoice</span></div><div class="modal-body p-4 bg-light"><div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm"><div class="d-flex align-items-center mb-5"><div class="w-50 pe-4"><label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">Invoice Number</label><input type="text" id="inv_no" class="form-control odoo-title-input mb-0 fs-4" required></div><div class="w-50 ps-4 border-start"><label class="text-muted small fw-bolder mb-1 text-uppercase">Invoice Date</label><input type="date" id="inv_date" class="form-control odoo-title-input mb-0 fs-5" style="text-transform: none;" required></div></div><div class="row mb-5"><div class="col-md-6 pe-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Stock Order Ref</label><select id="inv_so_id" class="form-select odoo-input fw-bold" required></select></div><div class="odoo-field-group mb-3"><label class="odoo-label">Customer Name</label><input type="text" id="inv_outlet_name" class="form-control odoo-input bg-light" disabled></div></div><div class="col-md-6 ps-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label">TIN</label><input type="text" id="inv_outlet_tin" class="form-control odoo-input bg-light" disabled></div><div class="odoo-field-group mb-3"><label class="odoo-label">Business Style</label><input type="text" id="inv_business_style" class="form-control odoo-input bg-light" disabled></div></div></div><div class="row border-top pt-4"><div class="col-md-6"></div><div class="col-md-6"><table class="table table-sm table-borderless fw-bold mb-0"><tbody class="text-end">
    <tr><td class="text-muted w-50">Total Sales (VAT Inc):</td><td id="inv_gross_disp" class="fs-6">₱0.00</td></tr>
    <tr><td class="text-muted">Less VAT:</td><td id="inv_vat_disp" class="text-danger fs-6">₱0.00</td></tr>
    <tr><td class="text-muted border-bottom pb-2">Net of VAT:</td><td id="inv_net_disp" class="border-bottom pb-2 fs-6">₱0.00</td></tr>
    
    <tr>
        <td class="text-muted pt-3 d-flex justify-content-end align-items-center">
            Less Discount 
            <div class="form-check form-switch ms-3 mb-0">
                <input class="form-check-input" type="checkbox" id="inv_apply_discount">
            </div>
            <select id="inv_discount_percent" class="form-select form-select-sm ms-2 fw-bold text-danger border-danger" style="width: 70px; display: none;">
                <option value="1">1%</option>
                <option value="2">2%</option>
                <option value="3">3%</option>
                <option value="4">4%</option>
                <option value="5">5%</option>
            </select>
        </td>
        <td id="inv_discount_disp" class="pt-3 text-danger fs-6">₱0.00</td>
    </tr>
    <tr><td class="text-muted pt-3 d-flex justify-content-end align-items-center">Less EWT <div class="form-check form-switch ms-3 mb-0"><input class="form-check-input" type="checkbox" id="inv_apply_ewt"></div></td><td id="inv_ewt_disp" class="pt-3 text-danger fs-6">₱0.00</td></tr>
    <tr><td class="fs-4 text-primary pt-4 text-uppercase">Amount Due:</td><td class="fs-3 text-primary pt-3 fw-bolder" id="inv_due_disp">₱0.00</td></tr>
</tbody></table></div></div></div></div></form></div></div></div>

<!-- Collection Modal -->
<div class="modal fade" id="dsCollectionModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content border-0 odoo-modal-content"><form id="dsCollectionForm"><input type="hidden" id="ds_type_cr" value="DS"><div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center"><button type="submit" class="btn btn-success btn-sm px-4 fw-bold shadow-sm text-uppercase">Post Receipt</button><button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-cash-stack me-1"></i>New Collection Receipt</span></div><div class="modal-body p-4 bg-light"><div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm"><div class="d-flex align-items-center mb-5"><div class="w-50 pe-4"><label class="text-muted small fw-bolder mb-1 text-uppercase text-success">CR Number</label><input type="text" id="cr_no" class="form-control odoo-title-input mb-0 fs-4" required></div><div class="w-50 ps-4 border-start"><label class="text-muted small fw-bolder mb-1 text-uppercase">Collection Date</label><input type="date" id="cr_date" class="form-control odoo-title-input mb-0 fs-5" style="text-transform: none;" required></div></div><div class="row mb-5"><div class="col-md-6 pe-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label text-success">Select Customer</label><select id="cr_outlet_id" class="form-select odoo-input fw-bold" required></select></div><div class="odoo-field-group mb-3"><label class="odoo-label">Address</label><input type="text" id="cr_address" class="form-control odoo-input bg-light" disabled></div></div><div class="col-md-6 ps-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label">TIN</label><input type="text" id="cr_outlet_tin" class="form-control odoo-input bg-light" disabled></div><div class="odoo-field-group mb-3"><label class="odoo-label">Business Style</label><input type="text" id="cr_business_style" class="form-control odoo-input bg-light" disabled></div></div></div><h6 class="fw-bolder text-uppercase text-secondary mb-3">Unpaid Invoices to Collect</h6><table class="table table-borderless table-sm mb-4" id="crInvoicesTable"><thead class="border-bottom text-uppercase text-muted" style="font-size: 0.75rem;"><tr><th style="width: 5%;" class="pb-2"></th><th class="pb-2">Invoice No</th><th class="text-end pb-2">Amount Due</th></tr></thead><tbody id="crInvoicesTbody"><tr><td colspan="3" class="text-center text-muted py-4 font-monospace">Select a customer to view unpaid invoices.</td></tr></tbody></table><div class="d-flex justify-content-between align-items-end p-4 bg-light rounded-3 border"><div class="w-50"><label class="text-muted small text-uppercase fw-bold mb-1">Amount in Words</label><textarea id="cr_amount_words" class="form-control border-0 bg-transparent fw-bold text-dark p-0" style="resize: none;" rows="2" readonly></textarea></div><div class="text-end"><span class="text-muted small text-uppercase fw-bold">Total Collected</span> <h2 class="mb-0 text-success fw-bolder mt-1" id="cr_total_amount">₱0.00</h2></div></div></div></div></form></div></div></div>