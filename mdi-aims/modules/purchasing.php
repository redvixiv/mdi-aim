<div id="module-Purchasing" class="module-container p-2">          
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
        <h4 class="erp-header mb-0"><i class="bi bi-cart-check text-muted me-2"></i>Purchasing Management</h4>     
    </div>          
    <ul class="nav erp-nav" id="purchasingOnlyTabs" role="tablist">         
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#purch-pos" type="button">Purchase Orders</button></li>         
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#purch-receipts" type="button">Goods Receipts</button></li>         
        <li class="nav-item ms-auto"><button class="nav-link text-primary" data-bs-toggle="tab" data-bs-target="#purch-reports" type="button"><i class="bi bi-bar-chart-fill me-2"></i>Purchasing Report</button></li>     
    </ul>     
    <div class="tab-content" id="purchasingOnlyTabsContent">                  
        <div class="tab-pane fade show active" id="purch-pos">             
            <div class="d-flex justify-content-between align-items-center mb-4">                 
                <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#poModal"><i class="bi bi-plus-lg me-2"></i>Create Stock Order</button>                 
                <div class="input-group w-25 shadow-sm">                     
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>                     
                    <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#poTable" placeholder="Search orders...">                 
                </div>             
            </div>             
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">                 
                <table class="erp-table" id="poTable">                     
                    <thead style="position: sticky; top: 0; z-index: 1;">                         
                        <tr><th>Order No.</th><th>Date</th><th>Supplier</th><th>Warehouse</th><th class="text-center">Total Qty</th><th class="text-end">Total Amount</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr>                     
                    </thead>                     
                    <tbody></tbody>                 
                </table>             
            </div>         
        </div>         
        <div class="tab-pane fade" id="purch-receipts">             
            <div class="d-flex justify-content-between align-items-center mb-4">                 
                <button class="btn btn-erp btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#grModal"><i class="bi bi-truck me-2"></i>Receive Delivery</button>                 
                <div class="input-group w-25 shadow-sm">                     
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>                     
                    <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#grTable" placeholder="Search deliveries...">                 
                </div>             
            </div>             
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">                 
                <table class="erp-table" id="grTable">                     
                    <thead style="position: sticky; top: 0; z-index: 1;">                         
                        <tr><th>DR No.</th><th>Arrival Date</th><th>PO Ref</th><th>Forwarder</th><th>Seal No.</th><th class="text-center">Total Qty Received</th><th class="text-end">Total Amount</th><th class="text-center">Actions</th></tr>                     
                    </thead>                     
                    <tbody></tbody>                 
                </table>             
            </div>         
        </div>         
        <!-- PURCHASING REPORT TAB -->         
        <div class="tab-pane fade" id="purch-reports">             
            <div class="d-flex justify-content-between align-items-center mb-4">                 
                <div class="d-flex align-items-center gap-3">                     
                    <label class="small text-muted text-uppercase fw-bold m-0">From:</label>                     
                    <input type="date" id="purch_rep_from" class="form-control form-control-sm fw-bold shadow-sm" style="text-transform:none;">                     
                    <label class="small text-muted text-uppercase fw-bold m-0 ms-2">To:</label>                     
                    <input type="date" id="purch_rep_to" class="form-control form-control-sm fw-bold shadow-sm" style="text-transform:none;">                     
                    <button class="btn btn-erp btn-primary shadow-sm" onclick="window.loadPurchasingReport()"><i class="bi bi-arrow-clockwise me-1"></i>Generate</button>                 
                </div>                 
                <div class="d-flex align-items-center gap-2">                     
                    <div class="input-group shadow-sm me-2 flex-shrink-0" style="width: 250px;">                         
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>                         
                        <input type="text" class="form-control border-start-0 ps-0 border-0" id="purchReportSearch" placeholder="Search Report...">                     
                    </div>                     
                    <button class="btn btn-erp btn-outline-success bg-white shadow-sm" onclick="window.exportTableToCSV('purchReportTable', 'Purchasing_Report.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Excel</button>                     
                    <button class="btn btn-erp btn-outline-danger bg-white shadow-sm" onclick="window.printPurchasingReport()"><i class="bi bi-file-pdf me-1"></i>Print</button>                 
                </div>             
            </div>                          
            <div class="row g-4 mb-4" id="purchReportKPIs">                 
                <div class="col-md-4"><div class="kpi-card p-4 border-start border-4 border-primary"><span class="kpi-title text-uppercase fw-bold">Total Ordered (PO)</span><h3 class="mb-0 text-primary fw-bolder mt-1" id="purch_rep_ordered">₱0.00</h3></div></div>                 
                <div class="col-md-4"><div class="kpi-card p-4 border-start border-4 border-success"><span class="kpi-title text-uppercase fw-bold">Total Received (GR)</span><h3 class="mb-0 text-success fw-bolder mt-1" id="purch_rep_received">₱0.00</h3></div></div>                 
                <div class="col-md-4"><div class="kpi-card p-4 border-start border-4 border-danger"><span class="kpi-title text-uppercase fw-bold">Total Pending</span><h3 class="mb-0 text-danger fw-bolder mt-1" id="purch_rep_pending">₱0.00</h3></div></div>             
            </div>             
            <div class="erp-table-container" style="max-height: 45vh; overflow-y: auto;" id="purchReportWrapper">                 
                <table class="erp-table" id="purchReportTable">                     
                    <thead style="position: sticky; top: 0; z-index: 1;">                         
                        <tr><th>Date</th><th>PO No.</th><th>DR No.</th><th>Supplier</th><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit Cost</th><th class="text-end text-primary">Total Amount</th><th class="text-center">Status</th></tr>                     
                    </thead>                     
                    <tbody><tr><td colspan="9" class="text-center py-4 text-muted font-monospace">Select dates and click Generate to run report.</td></tr></tbody>                 
                </table>             
            </div>         
        </div>     
    </div> 
</div> 
<!-- PO Modal --> 
<div class="modal fade" id="poModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content border-0 odoo-modal-content"><form id="poForm"><div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center"><button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Order</button><button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-cart me-1"></i>New Purchase Order</span></div><div class="modal-body p-4 bg-light"><div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm"><div class="d-flex align-items-center mb-5"><div class="w-50 pe-4"><label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">PO Number</label><input type="text" id="po_no" class="form-control odoo-title-input mb-0 fs-4" placeholder="e.g. PO-2026-001" required></div><div class="w-50 ps-4 border-start"><label class="text-muted small fw-bolder mb-1 text-uppercase">Order Date</label><input type="date" id="po_date" class="form-control odoo-title-input mb-0 fs-5" style="text-transform: none;" required></div></div><div class="row mb-5"><div class="col-md-6 pe-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Supplier</label><select id="po_supplier_id" class="form-select odoo-input fw-bold" required></select></div></div><div class="col-md-6 ps-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Ship To (Warehouse)</label><select id="po_warehouse_id" class="form-select odoo-input fw-bold" required></select></div></div></div><div class="d-flex justify-content-between align-items-center mb-3"><h6 class="fw-bolder text-uppercase text-secondary m-0">Order Lines</h6><button type="button" class="btn btn-sm btn-outline-primary fw-bold text-uppercase rounded-pill px-3" id="btnAddPoItem"><i class="bi bi-plus-lg me-1"></i>Add Product</button></div>     <table class="table table-borderless table-sm mb-0" id="poItemsTable"><thead class="border-bottom text-uppercase text-muted" style="font-size: 0.75rem;"><tr><th class="pb-2">Product Description</th><th class="pb-2" style="width: 15%;">Order Qty</th><th class="pb-2" style="width: 20%;">Unit Price</th><th class="pb-2 text-end" style="width: 20%;">Subtotal</th><th style="width: 5%;"></th></tr></thead><tbody id="poItemsTbody"></tbody></table>     <div class="d-flex justify-content-end gap-5 mt-4 pt-3 border-top">         <div><span class="text-muted small text-uppercase fw-bold">Total Quantity</span> <h3 class="mb-0 text-primary fw-bolder text-end" id="po_total_qty">0</h3></div>         <div><span class="text-muted small text-uppercase fw-bold">Total Amount</span> <h3 class="mb-0 text-primary fw-bolder text-end" id="po_total_amount">₱0.00</h3></div>     </div></div></div></form></div></div></div> 
<!-- GR Modal --> 
<div class="modal fade" id="grModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content border-0 odoo-modal-content"><form id="grForm"><div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center"><button type="submit" class="btn btn-success btn-sm px-4 fw-bold shadow-sm text-uppercase">Confirm Receipt</button><button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-truck me-1"></i>Receive Goods</span></div><div class="modal-body p-4 bg-light"><div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm"><div class="d-flex align-items-center mb-5"><div class="w-50 pe-4"><label class="text-muted small fw-bolder mb-1 text-uppercase text-success">Delivery Receipt (DR) No.</label><input type="text" id="gr_dr_no" class="form-control odoo-title-input mb-0 fs-4" required></div><div class="w-50 ps-4 border-start"><label class="text-muted small fw-bolder mb-1 text-uppercase">Arrival Date</label><input type="date" id="gr_arrival_date" class="form-control odoo-title-input mb-0 fs-5" style="text-transform: none;" required></div></div><div class="row mb-5"><div class="col-md-6 pe-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label text-primary">PO Reference</label><select id="gr_po_id" class="form-select odoo-input fw-bold" required></select></div><div class="odoo-field-group mb-3"><label class="odoo-label">Warehouse</label><input type="text" id="gr_warehouse_name" class="form-control odoo-input bg-light" disabled><input type="hidden" id="gr_warehouse_id"></div></div><div class="col-md-6 ps-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Forwarder</label><input type="text" id="gr_forwarder" class="form-control odoo-input fw-bold" required></div><div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Seal No.</label><input type="text" id="gr_seal" class="form-control odoo-input fw-bold" required></div><div class="odoo-field-group mb-3"><label class="odoo-label">Remarks</label><input type="text" id="gr_remarks" class="form-control odoo-input" placeholder="e.g. Late Arrival..."></div></div></div><h6 class="fw-bolder text-uppercase text-secondary mb-3 border-bottom pb-2">Verify Received Items</h6>     <table class="table table-borderless table-sm mb-0" id="grItemsTable"><thead class="border-bottom text-uppercase text-muted" style="font-size: 0.75rem;"><tr><th class="pb-2">Product Description</th><th class="pb-2" style="width: 20%;">Expected Qty</th><th class="pb-2" style="width: 20%;">Actual Qty</th><th class="pb-2 text-end" style="width: 15%;">Unit Price</th><th class="pb-2 text-end" style="width: 20%;">Subtotal</th></tr></thead><tbody id="grItemsTbody"><tr><td colspan="5" class="text-center text-muted py-4 font-monospace">Select a Pending PO to populate expected items.</td></tr></tbody></table>     <div class="d-flex justify-content-end gap-5 mt-4 pt-3 border-top">         <div><span class="text-muted small text-uppercase fw-bold">Total Received</span> <h3 class="mb-0 text-success fw-bolder text-end" id="gr_total_qty">0</h3></div>         <div><span class="text-muted small text-uppercase fw-bold">Total Amount</span> <h3 class="mb-0 text-success fw-bolder text-end" id="gr_total_amount">₱0.00</h3></div>     </div></div></div></form></div></div></div>