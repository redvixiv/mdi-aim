<div id="module-Inventory" class="module-container p-2">
    
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
        <h4 class="erp-header mb-0"><i class="bi bi-boxes text-muted me-2"></i>Inventory Management</h4>
    </div>
    
    <ul class="nav erp-nav" id="inventoryTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#inv-dashboard" type="button">Live Stock Flow</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#inv-transfers" type="button">Stock Transfers</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#inv-ledger" type="button">Inventory Ledger</button></li>
        <li class="nav-item ms-auto"><button class="nav-link text-info border border-info bg-info-subtle rounded" data-bs-toggle="tab" data-bs-target="#inv-stock-position" type="button"><i class="bi bi-boxes me-1"></i>Stock Position</button></li>
        <li class="nav-item ms-2"><button class="nav-link text-primary" data-bs-toggle="tab" data-bs-target="#inv-reports" type="button"><i class="bi bi-bar-chart-fill me-2"></i>Inventory Report</button></li>
    </ul>

    <div class="tab-content" id="inventoryTabsContent">
        
        <div class="tab-pane fade show active" id="inv-dashboard">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center w-50">
                    <label class="kpi-title text-uppercase fw-bolder mb-0 me-3 text-nowrap"><i class="bi bi-building me-2"></i>Select Warehouse:</label>
                    <select id="dash_warehouse_id" class="form-select form-select-sm fw-bold shadow-sm" style="width: 250px;"><option value="">Loading Warehouses...</option></select>
                </div>
                <div class="d-flex w-50 justify-content-end">
                    <button class="btn btn-erp btn-outline-success me-2 bg-white" onclick="window.exportTableToCSV('stockBalancesTable', 'Stock_Balances.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Export</button>
                    <div class="input-group shadow-sm" style="max-width: 250px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#stockBalancesTable" placeholder="Search stock...">
                    </div>
                </div>
            </div>
            <div class="erp-table-container" style="max-height: 60vh; overflow-y: auto;">
                <table class="erp-table" id="stockBalancesTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr><th>Product No</th><th>Product Name</th><th>Category</th><th class="text-end pe-4">Current Stock on Hand</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="inv-transfers">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#transferModal"><i class="bi bi-arrow-left-right me-2"></i>New Transfer</button>
                <div class="input-group w-25 shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#transfersTable" placeholder="Search transfers...">
                </div>
            </div>
            <div class="erp-table-container" style="max-height: 60vh; overflow-y: auto;">
                <table class="erp-table" id="transfersTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr><th>Transfer No.</th><th>Date</th><th>From Warehouse</th><th>To Warehouse</th><th>Products Transferred</th><th class="text-center">Total Qty</th><th>Remarks</th><th class="text-center">Actions</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="inv-ledger">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bolder text-secondary m-0 ps-2" style="letter-spacing:-0.3px;"><i class="bi bi-list-columns-reverse me-2"></i>Global Inventory Ledger</h5>
                <div class="d-flex w-50 justify-content-end">
                    <button class="btn btn-erp btn-outline-success me-2 bg-white shadow-sm" onclick="window.exportTableToCSV('invLedgerTable', 'Inventory_Ledger.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Export</button>
                    <div class="input-group shadow-sm" style="max-width: 250px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#invLedgerTable" placeholder="Search ledger...">
                    </div>
                </div>
            </div>
            <div class="erp-table-container" style="max-height: 60vh; overflow-y: auto;">
                <table class="erp-table" id="invLedgerTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr><th>Date</th><th>Warehouse</th><th>Product</th><th>Transaction Type</th><th>Reference No</th><th class="text-success text-center">Qty In</th><th class="text-danger text-center">Qty Out</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        
        <!-- INVENTORY STOCK POSITION TAB -->
        <div class="tab-pane fade" id="inv-stock-position">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted text-uppercase fw-bold m-0">Target Date:</label>
                    <input type="date" id="inv_sp_date" class="form-control form-control-sm fw-bold shadow-sm" style="text-transform:none; width: 135px;">
                    <label class="small text-muted text-uppercase fw-bold m-0 ms-2">Sales Type:</label>
                    <select id="inv_sp_type" class="form-select form-select-sm fw-bold shadow-sm" style="width: 150px;">
                        <option value="YL">Yakult Lady (YL)</option>
                        <option value="DS">Direct Sales (DS)</option>
                    </select>
                    <button class="btn btn-erp btn-primary shadow-sm btn-sm ms-2" onclick="window.loadInvStockPositionTable()"><i class="bi bi-arrow-clockwise me-1"></i>Load Data</button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-erp btn-outline-success bg-white shadow-sm btn-sm" onclick="window.exportTableToCSV('invStockPositionTable', 'Stock_Position.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Export</button>
                    <button class="btn btn-erp btn-info text-white shadow-sm btn-sm" onclick="window.printInvStockPosition()"><i class="bi bi-printer me-1"></i>Print Paper Form</button>
                </div>
            </div>
            
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                <table class="erp-table" id="invStockPositionTable" style="font-size: 0.9rem;">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr>
                            <th>Category</th>
                            <th>Description / Destination</th>
                            <th class="text-end" id="sp_th_orig">Original (CPO)</th>
                            <th class="text-end text-info" id="sp_th_light">Light (CPL)</th>
                            <th class="text-end text-primary">Total Stock</th>
                        </tr>
                    </thead>
                    <tbody><tr><td colspan="5" class="text-center py-4 text-muted font-monospace">Select a date/type and click Load Data.</td></tr></tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="inv-reports">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-3">
                    <label class="small text-muted text-uppercase fw-bold m-0">From:</label>
                    <input type="date" id="inv_rep_from" class="form-control form-control-sm fw-bold shadow-sm" style="text-transform:none;">
                    <label class="small text-muted text-uppercase fw-bold m-0 ms-2">To:</label>
                    <input type="date" id="inv_rep_to" class="form-control form-control-sm fw-bold shadow-sm" style="text-transform:none;">
                    <button class="btn btn-erp btn-primary shadow-sm" onclick="window.loadInvReport()"><i class="bi bi-arrow-clockwise me-1"></i>Generate</button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group shadow-sm me-2 flex-shrink-0" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0 border-0" id="invReportSearch" placeholder="Search Movement...">
                    </div>
                    <button class="btn btn-erp btn-outline-success bg-white shadow-sm" onclick="window.exportTableToCSV('invReportTable', 'Inventory_Movement_Report.csv')"><i class="bi bi-file-earmark-excel me-1"></i>Excel</button>
                    <button class="btn btn-erp btn-outline-danger bg-white shadow-sm" onclick="window.printInvReport()"><i class="bi bi-file-pdf me-1"></i>Print</button>
                </div>
            </div>
            
            <div class="row g-4 mb-4" id="invReportKPIs">
                <div class="col-md-4"><div class="kpi-card p-4 border-start border-4 border-success"><span class="kpi-title text-uppercase fw-bold">Total Stock In</span><h3 class="mb-0 text-success fw-bolder mt-1" id="inv_rep_in">0</h3></div></div>
                <div class="col-md-4"><div class="kpi-card p-4 border-start border-4 border-danger"><span class="kpi-title text-uppercase fw-bold">Total Stock Out</span><h3 class="mb-0 text-danger fw-bolder mt-1" id="inv_rep_out">0</h3></div></div>
                <div class="col-md-4"><div class="kpi-card p-4 border-start border-4 border-primary"><span class="kpi-title text-uppercase fw-bold">Net Movement</span><h3 class="mb-0 text-primary fw-bolder mt-1" id="inv_rep_net">0</h3></div></div>
            </div>

            <div class="erp-table-container" style="max-height: 45vh; overflow-y: auto;" id="invReportWrapper">
                <table class="erp-table" id="invReportTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr><th>Date</th><th>Warehouse</th><th>Product</th><th>Transaction Type</th><th>Reference No</th><th class="text-success text-center">Qty In</th><th class="text-danger text-center">Qty Out</th></tr>
                    </thead>
                    <tbody><tr><td colspan="7" class="text-center py-4 text-muted font-monospace">Select dates and click Generate to run report.</td></tr></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content border-0 odoo-modal-content"><form id="transferForm"><div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center"><button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Post Transfer</button><button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-arrow-left-right me-1"></i>New Stock Transfer</span></div><div class="modal-body p-4 bg-light"><div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm"><div class="d-flex align-items-center mb-5"><div class="w-50 pe-4"><label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">Transfer Number</label><input type="text" id="tr_no" class="form-control odoo-title-input mb-0 fs-4" placeholder="e.g. TR-1001" required></div><div class="w-50 ps-4 border-start"><label class="text-muted small fw-bolder mb-1 text-uppercase">Transfer Date</label><input type="date" id="tr_date" class="form-control odoo-title-input mb-0 fs-5" style="text-transform: none;" required></div></div><div class="row mb-5"><div class="col-md-6 pe-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label text-danger">From Warehouse</label><select id="tr_from_warehouse" class="form-select odoo-input fw-bold" required></select></div></div><div class="col-md-6 ps-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label text-primary">To Warehouse</label><select id="tr_to_warehouse" class="form-select odoo-input fw-bold" required></select></div><div class="odoo-field-group mt-3"><label class="odoo-label">Remarks</label><input type="text" id="tr_remarks" class="form-control odoo-input" placeholder="Optional notes..."></div></div></div><div class="d-flex justify-content-between align-items-center mb-3"><h6 class="fw-bolder text-uppercase text-secondary m-0">Items to Transfer</h6><button type="button" class="btn btn-sm btn-outline-primary fw-bold text-uppercase rounded-pill px-3" id="btnAddTrItem"><i class="bi bi-plus-lg me-1"></i>Add Product</button></div><table class="table table-borderless table-sm mb-0" id="trItemsTable"><thead class="border-bottom text-uppercase text-muted" style="font-size: 0.75rem;"><tr><th class="pb-2">Product Description</th><th class="pb-2" style="width: 25%;">Transfer Qty</th><th style="width: 5%;"></th></tr></thead><tbody id="trItemsTbody"></tbody></table><div class="d-flex justify-content-end gap-5 mt-4 pt-3 border-top"><div><span class="text-muted small text-uppercase fw-bold">Total Quantity</span> <h3 class="mb-0 text-primary fw-bolder text-end" id="tr_total_qty">0</h3></div></div></div></div></form></div></div></div>