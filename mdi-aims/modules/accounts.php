<div id="module-Accounts" class="module-container p-2 active">
    
    <!-- ERP Custom Styling -->
    <style>
        .erp-header { font-weight: 800; color: #1e293b; letter-spacing: -0.5px; }
        .erp-nav { border-bottom: 2px solid #e2e8f0; margin-bottom: 25px; }
        .erp-nav .nav-link { color: #64748b; font-weight: 700; padding: 12px 20px; border: none; border-bottom: 3px solid transparent; border-radius: 0; transition: all 0.2s ease; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
        .erp-nav .nav-link:hover { color: #334155; border-bottom-color: #cbd5e1; background: transparent; }
        .erp-nav .nav-link.active { color: var(--theme-green, #10b981); border-bottom-color: var(--theme-green, #10b981); background: transparent; }
        
        .erp-table-container { border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .erp-table { border-spacing: 0; border-collapse: collapse; width: 100%; margin: 0; }
        .erp-table thead th { background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
        .erp-table tbody td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; font-size: 0.875rem; }
        .erp-table tbody tr:hover td { background: #f8fafc; }
        
        .btn-erp { border-radius: 6px; font-weight: 600; letter-spacing: 0.3px; padding: 6px 14px; transition: all 0.2s; font-size: 0.85rem; text-transform: uppercase; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="erp-header mb-0"><i class="bi bi-people-fill text-muted me-2"></i>Accounts Master</h4>
    </div>
    
    <ul class="nav erp-nav" id="accountTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all-outlets" type="button">Customers</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#dealers" type="button">Independent Dealers</button></li>
    </ul>
    
    <div class="tab-content" id="accountTabsContent">
        
        <div class="tab-pane fade show active" id="all-outlets">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#outletModal" onclick="openNewOutletModal()"><i class="bi bi-plus-lg me-2"></i>New Customer</button>
                <div class="input-group w-25 shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#allOutletsTable" placeholder="Search customers...">
                </div>
            </div>
            <div class="erp-table-container" style="max-height: 60vh; overflow-y: auto;">
                <table class="erp-table" id="allOutletsTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr><th>Customer No.</th><th>Customer Name</th><th>TIN</th><th>Business Style</th><th>DS Section</th><th>Category</th><th>Branch</th><th>Location</th><th>Route</th><th class="text-center">Actions</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="dealers">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#dealerModal"><i class="bi bi-person-badge-fill me-2"></i>New Dealer</button>
                <div class="input-group w-25 shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#dealersTable" placeholder="Search dealers...">
                </div>
            </div>
            <div class="erp-table-container" style="max-height: 60vh; overflow-y: auto;">
                <table class="erp-table" id="dealersTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr><th>Dealer No.</th><th>Full Name</th><th>Center Code</th><th>Center</th><th>Area</th><th>Type</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- ======================= MODALS ======================= -->

<div class="modal fade" id="dealerModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content border-0 odoo-modal-content">
<form id="dealerForm">
    <input type="hidden" id="d_dealer_id">
    <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center"><button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Dealer</button><button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button><span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-person-badge me-1"></i>Dealer Details</span></div><div class="modal-body p-4 bg-light"><div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm"><div class="row border-bottom pb-4 mb-4"><div class="col-md-4"><label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">First Name</label><input type="text" id="d_fname" class="form-control odoo-title-input fs-5" required></div><div class="col-md-4"><label class="text-muted small fw-bolder mb-1 text-uppercase">Middle Name</label><input type="text" id="d_mname" class="form-control odoo-title-input fs-5"></div><div class="col-md-4"><label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">Last Name</label><input type="text" id="d_lname" class="form-control odoo-title-input fs-5" required></div></div><div class="row"><div class="col-md-6 pe-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label">Birth Date</label><input type="date" id="d_bdate" class="form-control odoo-input" style="text-transform: none;"></div><div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Hiring Date</label><input type="date" id="d_hdate" class="form-control odoo-input fw-bold text-dark" style="text-transform: none;"></div>
    
    <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Center Code</label><select id="d_center_code" class="form-select odoo-input fw-bold" required><option value="">Loading Codes...</option></select></div>
    <div class="odoo-field-group mb-3"><label class="odoo-label text-muted">Center Name</label><input type="text" id="d_center" class="form-control odoo-input fw-bold bg-light" readonly placeholder="Auto-filled"></div>
    <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Area</label><select id="d_area" class="form-select odoo-input fw-bold" required><option value="">Select Center Code First</option></select></div>
    
    </div><div class="col-md-6 ps-lg-4"><div class="odoo-field-group mb-3"><label class="odoo-label">Type</label><select id="d_type" class="form-select odoo-input bg-light" disabled><option value="Yakult Lady">Yakult Lady</option></select></div><div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Status</label><select id="d_status" class="form-select odoo-input fw-bold" required><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div><div class="odoo-field-group mb-3"><label class="odoo-label">Remarks</label><input type="text" id="d_remarks" class="form-control odoo-input"></div></div></div></div></div>
</form></div></div></div>

<div class="modal fade" id="outletModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="outletForm">
                <input type="hidden" id="o_outlet_id">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Customer</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-person me-1"></i>Customer Information</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm border-top border-4 border-success">
                        <div class="d-flex align-items-center border-bottom pb-4 mb-4">
                            <div class="w-50 pe-4">
                                <label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">Customer Name</label>
                                <input type="text" id="o_customer_name" class="form-control odoo-title-input mb-0 fs-4" required>
                            </div>
                            <div class="w-50 ps-4 border-start">
                                <label class="text-muted small fw-bolder mb-1 text-uppercase">Customer No. (Auto-Generated)</label>
                                <input type="text" id="o_customer_no" class="form-control odoo-title-input mb-0 fs-5 text-muted" disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 pe-lg-4">
                                <div class="odoo-field-group mb-3"><label class="odoo-label">TIN</label><input type="text" id="o_tin" class="form-control odoo-input fw-bold" placeholder="000-000-000"></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Business Style</label><input type="text" id="o_bstyle" class="form-control odoo-input fw-bold"></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-success">Branch</label><input type="text" id="o_branch" class="form-control odoo-input fw-bold text-uppercase" required></div>
                                
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Province</label><select id="o_province" class="form-select odoo-input fw-bold" required><option value="">Loading...</option></select></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">City</label><select id="o_city" class="form-select odoo-input fw-bold" required disabled><option value="">Select Province First</option></select></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Barangay</label><select id="o_brgy" class="form-select odoo-input fw-bold" required disabled><option value="">Select City First</option></select></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Street Address</label><input type="text" id="o_address" class="form-control odoo-input text-uppercase"></div>
                            </div>
                            <div class="col-md-6 ps-lg-4">
                                <div class="odoo-field-group mb-3">
                                    <label class="odoo-label text-primary">DS Section</label>
                                    <select id="o_ds" class="form-select odoo-input fw-bold" required>
                                        <option value="">Select Section...</option>
                                        <option value="Booking">Booking</option>
                                        <option value="Route">Route</option>
                                        <option value="Mini Route">Mini Route</option>
                                    </select>
                                </div>
                                <div class="odoo-field-group mb-3">
                                    <label class="odoo-label text-primary">Category</label>
                                    <select id="o_cat" class="form-select odoo-input fw-bold" required>
                                        <option value="">Select DS Section First</option>
                                    </select>
                                </div>
                                
                                <div class="odoo-field-group mb-3 mt-4">
                                    <label class="odoo-label text-primary" id="lbl_o_route">Route Code</label>
                                    <select id="o_route" class="form-select odoo-input fw-bold text-uppercase" required>
                                        <option value="">Select DS Section First</option>
                                    </select>
                                </div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Contact Person</label><input type="text" id="o_contact_person" class="form-control odoo-input fw-bold text-uppercase" required></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Contact No.</label><input type="text" id="o_contact_no" class="form-control odoo-input"></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Credit Terms</label><select id="o_terms" class="form-select odoo-input fw-bold" required><option value="">Loading Terms...</option></select></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>