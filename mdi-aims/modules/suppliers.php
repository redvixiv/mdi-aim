<div id="module-Suppliers" class="module-container">
    <div class="d-flex justify-content-between align-items-end mb-4 pb-2 border-bottom">
        <div>
            <h3 class="text-secondary fw-bolder m-0 text-uppercase" style="letter-spacing: 1px;"><i class="bi bi-truck-flatbed me-2 text-primary"></i>Supplier Management</h3>
            <small class="text-muted text-uppercase fw-bold">Manage Supplier Masterlist and Contact Details</small>
        </div>
    </div>
    
    <ul class="nav nav-tabs mb-4 border-bottom-0" id="supplierTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active text-uppercase fw-bold px-4" data-bs-toggle="tab" data-bs-target="#sup-masterlist" type="button">Supplier Masterlist</button></li>
    </ul>
    
    <div class="tab-content" id="supplierTabsContent">
        <div class="tab-pane fade show active" id="sup-masterlist">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <button class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase" data-bs-toggle="modal" data-bs-target="#supplierModal"><i class="bi bi-plus-lg me-2"></i>New Supplier</button>
                    <div class="input-group input-group-sm w-25 shadow-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light search-bar" data-target="#suppliersTable" placeholder="Search suppliers...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap m-0" id="suppliersTable" style="font-size: 0.85rem;">
                        <thead class="table-light text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <tr><th>Supplier No</th><th>Supplier Name</th><th>Location</th><th>Address</th><th>Contact Person</th><th>Date Created</th><th class="text-center">Actions</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="supplierForm">
                <input type="hidden" id="s_supplier_id"> <!-- FIXED: Added Hidden ID -->
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Supplier</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-building me-1"></i>Supplier Details</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm border-top border-4 border-primary">
                        <label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">Supplier Name</label>
                        <input type="text" id="s_name" class="form-control odoo-title-input mb-0 fs-4" required>
                        <div class="row mt-5">
                            <div class="col-md-6 pe-lg-4">
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">State / Province</label><select id="s_province" class="form-select odoo-input fw-bold" required><option value="">Loading...</option></select></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">City</label><select id="s_city" class="form-select odoo-input fw-bold" required disabled><option value="">Select Province First</option></select></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Barangay</label><select id="s_brgy" class="form-select odoo-input fw-bold" required disabled><option value="">Select City First</option></select></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Address</label><input type="text" id="s_address" class="form-control odoo-input"></div>
                            </div>
                            <div class="col-md-6 ps-lg-4">
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Contact Name</label><input type="text" id="s_contact_name" class="form-control odoo-input fw-bold" required></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Contact Number</label><input type="text" id="s_contact_no" class="form-control odoo-input fw-bold" required></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>