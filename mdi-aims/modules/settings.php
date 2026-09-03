<div id="module-Settings" class="module-container p-2">
    
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
        <h4 class="erp-header mb-0"><i class="bi bi-gear-fill text-muted me-2"></i>Settings & Configuration</h4>
    </div>
    
    <ul class="nav erp-nav" id="settingsTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#set-company" type="button">Company Profile</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#set-finance" type="button">Financial Settings</button></li>
        <li class="nav-item"><button class="nav-link text-success" id="warehouses-tab" data-bs-toggle="tab" data-bs-target="#set-warehouses" type="button">Warehouses</button></li>
        <li class="nav-item"><button class="nav-link text-info" id="centers-tab" data-bs-toggle="tab" data-bs-target="#set-centers" type="button">Centers & Areas</button></li>
        <li class="nav-item"><button class="nav-link" style="color:#fd7e14;" id="dsconfig-tab" data-bs-toggle="tab" data-bs-target="#set-dsconfig" type="button">DS Config</button></li>
        <li class="nav-item"><button class="nav-link" id="dropdowns-tab" data-bs-toggle="tab" data-bs-target="#dropdowns" type="button">Generic Dropdowns</button></li>
        <li class="nav-item"><button class="nav-link text-primary" id="users-tab" data-bs-toggle="tab" data-bs-target="#set-users" type="button">User Roles</button></li>
        <li class="nav-item ms-auto"><button class="nav-link text-danger border border-danger bg-danger-subtle rounded" id="system-tab" data-bs-toggle="tab" data-bs-target="#set-system" type="button"><i class="bi bi-shield-shaded me-1"></i>Security & Backups</button></li>
    </ul>
    
    <div class="tab-content" id="settingsTabsContent">
        
        <div class="tab-pane fade show active" id="set-company">
            <div class="kpi-card p-4">
                <div class="d-flex justify-content-between mb-4 pb-3 border-bottom">
                    <h5 class="fw-bold mb-0 align-self-end text-dark" style="letter-spacing:-0.5px;"><i class="bi bi-building text-primary me-2"></i>Business Information</h5>
                    <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#companyProfileModal"><i class="bi bi-pencil-square me-2"></i>Edit Profile</button>
                </div>
                <div class="d-flex align-items-start gap-4">
                    <div id="cp_display_logo_container" style="width: 150px; height: 150px; background-color: #f8f9fa; border: 2px dashed #cbd5e1; border-radius: 12px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <i class="bi bi-building text-muted" style="font-size: 3rem;" id="cp_display_icon"></i>
                        <img id="cp_display_img" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                    </div>
                    <div class="flex-grow-1 mt-2">
                        <h2 class="fw-bolder mb-1 text-uppercase text-primary" id="cp_display_name" style="letter-spacing:-1px;">NO COMPANY CONFIGURED</h2>
                        <p class="text-muted mb-4 font-monospace" id="cp_display_tin">TIN: -</p>
                        <div class="row">
                            <div class="col-md-6 mb-2"><span class="kpi-title d-block mb-1">Registered Address</span><span class="fw-bold text-dark" id="cp_display_address">-</span><br><span id="cp_display_location" class="text-muted">-</span></div>
                            <div class="col-md-6 mb-2"><span class="kpi-title d-block mb-1">Contact Details</span><span class="fw-bold text-dark" id="cp_display_contact">-</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="set-finance">
            <div class="kpi-card p-4">
                <div class="d-flex justify-content-between mb-4 pb-3 border-bottom">
                    <h5 class="fw-bold mb-0 align-self-end text-dark" style="letter-spacing:-0.5px;"><i class="bi bi-shield-lock text-danger me-2"></i>Accounting Controls</h5>
                    <button class="btn btn-erp btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#financialSettingsModal"><i class="bi bi-lock-fill me-2"></i>Set Closing Date</button>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <span class="kpi-title text-danger d-block mb-2"><i class="bi bi-shield-lock-fill me-1"></i>Books Locked Until</span>
                        <span id="cp_display_lock_date" class="fw-bolder text-danger fs-2" style="letter-spacing:-0.5px;">Not Locked</span>
                        <p class="text-muted mt-3 mb-0 w-75">When a closing date is set, the system will block any user from creating, editing, or backdating transactions (Journal Entries, Invoices, Receipts) on or before this date to preserve data integrity.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="set-warehouses">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-dark" style="letter-spacing:-0.5px;"><i class="bi bi-building text-success me-2"></i>Physical Warehouses</h5>
                <button class="btn btn-erp btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#warehouseModal" onclick="document.getElementById('warehouseForm').reset(); document.getElementById('w_id').value='';"><i class="bi bi-plus-lg me-2"></i>New Warehouse</button>
            </div>
            <div class="erp-table-container">
                <table class="erp-table" id="warehousesTable">
                    <thead><tr><th>Warehouse ID</th><th>Name</th><th>Location</th><th class="text-center">Actions</th></tr></thead>
                    <tbody><tr><td colspan="4" class="text-center py-4 text-muted font-monospace">Loading warehouses...</td></tr></tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="set-centers">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-dark" style="letter-spacing:-0.5px;"><i class="bi bi-geo-alt-fill text-info me-2"></i>Distribution Centers & Areas</h5>
                <button class="btn btn-erp btn-info text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#centerModal" onclick="document.getElementById('centerForm').reset(); document.getElementById('center_id').value='';"><i class="bi bi-plus-lg me-2"></i>New Record</button>
            </div>
            <div class="erp-table-container">
                <table class="erp-table" id="centersTable">
                    <thead><tr><th>Type</th><th>Code</th><th>Name</th><th>Parent Center</th><th>Linked Warehouse</th><th>In Charge</th><th class="text-center">Actions</th></tr></thead>
                    <tbody><tr><td colspan="7" class="text-center py-4 text-muted font-monospace">Loading centers...</td></tr></tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="set-dsconfig">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-dark" style="letter-spacing:-0.5px;"><i class="bi bi-map-fill text-warning me-2"></i>DS Configuration</h5>
                <button class="btn btn-erp btn-warning text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#dsConfigModal" onclick="document.getElementById('dsConfigForm').reset(); document.getElementById('ds_id').value='';"><i class="bi bi-plus-lg me-2"></i>New DS Area</button>
            </div>
            <div class="erp-table-container">
                <table class="erp-table" id="dsConfigTable">
                    <thead><tr><th style="width: 50px;">#</th><th>Parent (Area Type)</th><th>Child (Area Name)</th><th>Linked Warehouse</th><th>Route In Charge</th><th class="text-center">Actions</th></tr></thead>
                    <tbody><tr><td colspan="6" class="text-center py-4 text-muted font-monospace">Loading DS configuration...</td></tr></tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="dropdowns">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-dark" style="letter-spacing:-0.5px;"><i class="bi bi-list-task text-secondary me-2"></i>Generic Dropdowns</h5>
                <button class="btn btn-erp btn-outline-secondary bg-white shadow-sm" data-bs-toggle="modal" data-bs-target="#newDropdownModal" onclick="document.getElementById('dropdownSettingsForm').reset(); document.getElementById('dropdown_id').value='';"><i class="bi bi-plus-lg me-2"></i>New Option</button>
            </div>
            <div class="erp-table-container">
                <table class="erp-table" id="dropdownsTable">
                    <thead><tr><th>Dropdown Type</th><th>Option Value</th><th>Linked To (Rule)</th><th class="text-center">Actions</th></tr></thead>
                    <tbody><tr><td colspan="4" class="text-center py-4 text-muted font-monospace">Loading options...</td></tr></tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="set-users">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-dark" style="letter-spacing:-0.5px;"><i class="bi bi-people-fill text-primary me-2"></i>User Access Rights</h5>
                <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#userModal" onclick="document.getElementById('userForm').reset(); document.getElementById('u_id').value='';"><i class="bi bi-person-plus-fill me-2"></i>New User</button>
            </div>
            <div class="erp-table-container">
                <table class="erp-table" id="usersTable">
                    <thead><tr><th>Username</th><th class="text-center">Role</th><th>Linked Entity</th><th>Permissions Configured</th><th class="text-center">Actions</th></tr></thead>
                    <tbody><tr><td colspan="5" class="text-center py-4 text-muted font-monospace">Loading users...</td></tr></tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="set-system">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark" style="letter-spacing:-0.5px;"><i class="bi bi-shield-shaded text-danger me-2"></i>Security & Activity Logs</h5>
                    <small class="text-muted fw-bold text-uppercase">Track user activity and safeguard system data.</small>
                </div>
                <button class="btn btn-erp btn-danger shadow-sm" onclick="window.backupDatabase()"><i class="bi bi-database-down me-2"></i>Download Database Backup (.SQL)</button>
            </div>
            <div class="erp-table-container" style="max-height: 55vh; overflow-y: auto;">
                <table class="erp-table" id="auditLogsTable">
                    <thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Timestamp</th><th>User</th><th>Action Performed</th><th>Details</th></tr></thead>
                    <tbody><tr><td colspan="4" class="text-center py-4 text-muted font-monospace">Loading audit logs...</td></tr></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- ================= MODALS ================= -->

<div class="modal fade" id="companyProfileModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="companyProfileForm" enctype="multipart/form-data">
                <input type="hidden" id="cp_existing_logo" name="existing_logo">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Profile</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-building me-1"></i>Edit Company Profile</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm border-top border-4 border-primary">
                        <label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">Company Name</label>
                        <input type="text" id="cp_name" name="company_name" class="form-control odoo-title-input mb-0 fs-4" required>
                        <div class="row mt-5">
                            <div class="col-md-6 pe-lg-4">
                                <div class="odoo-field-group mb-3"><label class="odoo-label">TIN</label><input type="text" id="cp_tin" name="tin" class="form-control odoo-input fw-bold"></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Contact No.</label><input type="text" id="cp_contact_no" name="contact_no" class="form-control odoo-input fw-bold"></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Upload Logo</label><input type="file" id="cp_logo" name="logo" class="form-control odoo-input" style="text-transform: none;" accept="image/*"></div>
                            </div>
                            <div class="col-md-6 ps-lg-4 border-start">
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">State / Province</label><select id="cp_province" name="province" class="form-select odoo-input fw-bold"><option value="">Loading...</option></select></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">City</label><select id="cp_city" name="city" class="form-select odoo-input fw-bold" disabled><option value="">Select Province First</option></select></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Barangay</label><select id="cp_brgy" name="barangay" class="form-select odoo-input fw-bold" disabled><option value="">Select City First</option></select></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Address</label><input type="text" id="cp_address" name="address" class="form-control odoo-input"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="financialSettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="financialSettingsForm">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-danger btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Lock Date</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-shield-lock me-1"></i>Financial Settings</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm border-top border-4 border-danger">
                        <h6 class="fw-bolder text-uppercase text-danger mb-4"><i class="bi bi-shield-lock-fill me-2"></i>Set Closing Date</h6>
                        <div class="odoo-field-group mb-0">
                            <input type="date" id="fs_lock_date" class="form-control odoo-input fw-bold text-danger w-100 fs-5" style="text-transform: none;">
                        </div>
                        <small class="text-muted d-block mt-3">Leave this field blank to unlock the books completely.</small>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="warehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="warehouseForm">
                <input type="hidden" id="w_id">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Warehouse</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-building-add me-1"></i>Warehouse Details</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-4 rounded-3 shadow-sm border-top border-4 border-success">
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold" style="font-size: 0.8rem;">WAREHOUSE NAME</label>
                            <input type="text" class="form-control odoo-input fw-bold text-uppercase" id="w_name" placeholder="e.g. Main Warehouse" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-secondary fw-bold" style="font-size: 0.8rem;">LOCATION / ADDRESS</label>
                            <input type="text" class="form-control odoo-input text-uppercase" id="w_location" placeholder="e.g. Quezon City">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="centerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="centerForm">
                <input type="hidden" id="center_id">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-info text-white btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Record</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-geo-alt me-1"></i>New Center / Area</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-4 rounded-3 shadow-sm border-top border-4 border-info">
                        
                        <div class="mb-3">
                            <label class="form-label text-info fw-bold text-uppercase" style="font-size: 0.8rem;">Entry Type</label>
                            <select class="form-select odoo-input fw-bold" id="center_type" required>
                                <option value="Center">Create Parent (Center)</option>
                                <option value="Area">Create Child (Area)</option>
                            </select>
                        </div>

                        <div class="mb-3" id="center_parent_group" style="display: none;">
                            <label class="form-label text-primary fw-bold text-uppercase" style="font-size: 0.8rem;">Parent (Select Center)</label>
                            <select class="form-select odoo-input fw-bold" id="center_parent">
                                <option value="">Select Parent Center...</option>
                            </select>
                        </div>

                        <div class="mb-3 border-top pt-3">
                            <label class="form-label text-success fw-bold text-uppercase" style="font-size: 0.8rem;">Linked Physical Warehouse</label>
                            <select class="form-select odoo-input fw-bold" id="center_warehouse" required>
                                <option value="">Select Warehouse...</option>
                            </select>
                            <small class="text-muted" style="font-size: 0.7rem;">Stock will be deducted from this warehouse.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-info fw-bold text-uppercase" style="font-size: 0.8rem;">Code (Optional)</label>
                            <input type="text" class="form-control odoo-input fw-bold text-uppercase" id="center_code" placeholder="e.g. CEB-A">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold text-uppercase" style="font-size: 0.8rem;" id="lbl_center_name">Parent (Center Name)</label>
                            <input type="text" class="form-control odoo-input fw-bold text-uppercase" id="center_name" placeholder="e.g. CEBU A" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-secondary fw-bold text-uppercase" style="font-size: 0.8rem;" id="lbl_center_in_charge">Center In Charge / Supervisor</label>
                            <input type="text" class="form-control odoo-input fw-bold text-uppercase" id="center_in_charge" placeholder="e.g. Juan Dela Cruz">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="dsConfigModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="dsConfigForm">
                <input type="hidden" id="ds_id">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-warning text-dark btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Area</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-map me-1"></i>New DS Area</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-4 rounded-3 shadow-sm border-top border-4 border-warning">
                        
                        <div class="mb-3">
                            <label class="form-label text-warning fw-bold text-uppercase" style="font-size: 0.8rem;">Parent (Area Type)</label>
                            <select class="form-select odoo-input fw-bold" id="ds_area_type" required>
                                <option value="">Select Type...</option>
                                <option value="Booking">Booking</option>
                                <option value="Route">Route</option>
                                <option value="Mini Route">Mini Route</option>
                            </select>
                        </div>

                        <div class="mb-3 border-top pt-3">
                            <label class="form-label text-success fw-bold text-uppercase" style="font-size: 0.8rem;">Linked Physical Warehouse</label>
                            <select class="form-select odoo-input fw-bold" id="ds_warehouse" required>
                                <option value="">Select Warehouse...</option>
                            </select>
                            <small class="text-muted" style="font-size: 0.7rem;">Stock will be deducted from this warehouse.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold text-uppercase" style="font-size: 0.8rem;">Child (Area Name / Identifier)</label>
                            <input type="text" class="form-control odoo-input fw-bold text-uppercase" id="ds_area_name" placeholder="e.g. BOOKING 1" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-secondary fw-bold text-uppercase" style="font-size: 0.8rem;">Route In Charge (Assigned Personnel)</label>
                            <input type="text" class="form-control odoo-input fw-bold text-uppercase" id="ds_route_in_charge" placeholder="e.g. Juan Dela Cruz">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="newDropdownModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="dropdownSettingsForm">
                <input type="hidden" id="dropdown_id">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Option</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-list-task me-1"></i>New Dropdown</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-4 rounded-3 shadow-sm border-top border-4 border-primary">
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold" style="font-size: 0.8rem;">DROPDOWN TYPE</label>
                            <select class="form-select odoo-input fw-bold" id="dropdown_type" required>
                                <option value="">Select Target...</option>
                                <option value="Category">Accounts -> Category</option>
                                <option value="Credit Terms">Accounts -> Credit Terms</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold" style="font-size: 0.8rem;">OPTION VALUE</label>
                            <input type="text" class="form-control odoo-input text-uppercase" id="option_value" placeholder="e.g. 45 DAYS" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-secondary fw-bold" style="font-size: 0.8rem;">LINK TO PARENT (RULE - Optional)</label>
                            <input type="text" class="form-control odoo-input text-uppercase" id="parent_link" placeholder="e.g. ROUTE">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="userForm">
                <input type="hidden" id="u_id">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Save User</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-person-fill-gear me-1"></i>User Management</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm border-top border-4 border-primary">
                        <div class="row">
                            <div class="col-md-4 pe-lg-4 border-end">
                                <h6 class="fw-bolder text-uppercase text-secondary mb-4"><i class="bi bi-person-badge me-2"></i>Credentials</h6>
                                <div class="odoo-field-group flex-column align-items-start mb-4">
                                    <label class="odoo-label w-100 mb-1 text-primary">Username</label>
                                    <input type="text" id="u_username" class="form-control odoo-input w-100 text-lowercase fw-bold fs-5" style="text-transform:none;" required>
                                </div>
                                <div class="odoo-field-group flex-column align-items-start mb-4">
                                    <label class="odoo-label w-100 mb-1 text-primary">Password</label>
                                    <input type="password" id="u_password" class="form-control odoo-input w-100" style="text-transform:none;" placeholder="Leave blank to keep existing">
                                </div>
                                <div class="odoo-field-group flex-column align-items-start mb-4">
                                    <label class="odoo-label w-100 mb-1 text-primary">System Role</label>
                                    <select id="u_role" class="form-select odoo-input w-100 fw-bold" required>
                                        <option value="Admin">Full Admin</option>
                                        <option value="Cashier">Cashier / Encoder</option>
                                        <option value="Viewer">Read-Only Viewer</option>
                                        <option value="Field Agent">Field Agent (Mobile)</option>
                                    </select>
                                </div>
                                
                                <div id="field_agent_settings" style="display: none;" class="w-100 mt-3 border-top pt-3">
                                    <h6 class="fw-bold text-warning text-uppercase mb-3" style="font-size: 0.75rem;">Field Agent Configuration</h6>
                                    <div class="odoo-field-group flex-column align-items-start mb-3">
                                        <label class="odoo-label w-100 mb-1 text-primary">Agent Type</label>
                                        <select id="u_agent_type" class="form-select odoo-input w-100 fw-bold">
                                            <option value="">Select Type...</option>
                                            <option value="DS">Direct Sales (DS Driver)</option>
                                            <option value="YL">Yakult Lady (YL)</option>
                                        </select>
                                    </div>
                                    <div class="odoo-field-group flex-column align-items-start mb-3">
                                        <label class="odoo-label w-100 mb-1 text-primary">Linked Entity</label>
                                        <select id="u_linked_entity" class="form-select odoo-input w-100 fw-bold">
                                            <option value="">Select Agent Type First...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8 ps-lg-4">
                                <h6 class="fw-bolder text-uppercase text-primary mb-4"><i class="bi bi-ui-checks-grid me-2"></i>Module Access Rights</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card p-4 border-0 shadow-sm bg-light rounded-3">
                                            <div class="form-check form-switch mb-3 border-bottom pb-3">
                                                <input class="form-check-input perm-module" type="checkbox" id="perm_accounts" value="Accounts">
                                                <label class="form-check-label fw-bold">Accounts Module</label>
                                            </div>
                                            <div class="form-check form-switch mb-3 border-bottom pb-3">
                                                <input class="form-check-input perm-module" type="checkbox" id="perm_ds" value="DS">
                                                <label class="form-check-label fw-bold">DS Management</label>
                                            </div>
                                            <div class="form-check form-switch mb-3 border-bottom pb-3">
                                                <input class="form-check-input perm-module" type="checkbox" id="perm_yl" value="YL">
                                                <label class="form-check-label fw-bold">YL Management</label>
                                            </div>
                                            <div class="form-check form-switch mb-1">
                                                <input class="form-check-input perm-module" type="checkbox" id="perm_inventory" value="Inventory">
                                                <label class="form-check-label fw-bold">Products & Suppliers</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card p-4 border-0 shadow-sm bg-white border-start border-4 border-primary rounded-3">
                                            <div class="form-check form-switch mb-3 border-bottom pb-3">
                                                <input class="form-check-input perm-module" type="checkbox" id="perm_accounting" value="Accounting">
                                                <label class="form-check-label fw-bolder text-primary">Accounting Module</label>
                                            </div>
                                            <div class="ps-2">
                                                <div class="form-check mb-2"><input class="form-check-input perm-sub" type="checkbox" id="perm_acc_reports" value="acc-reports"><label class="small fw-bold text-secondary">Dashboard & Tax</label></div>
                                                <div class="form-check mb-2"><input class="form-check-input perm-sub" type="checkbox" id="perm_acc_ar" value="acc-receivables"><label class="small fw-bold text-secondary">Receivables</label></div>
                                                <div class="form-check mb-2"><input class="form-check-input perm-sub" type="checkbox" id="perm_acc_ap" value="acc-payables"><label class="small fw-bold text-secondary">Payables & Vouchers</label></div>
                                                <div class="form-check mb-2"><input class="form-check-input perm-sub" type="checkbox" id="perm_acc_gl" value="acc-core"><label class="small fw-bold text-secondary">General Ledger</label></div>
                                                <div class="form-check mb-2"><input class="form-check-input perm-sub" type="checkbox" id="perm_acc_audit" value="acc-audit"><label class="small fw-bold text-secondary">Audit & Valuations</label></div>
                                            </div>
                                            <div class="form-check form-switch mt-4 pt-3 border-top">
                                                <input class="form-check-input" type="checkbox" id="perm_readonly">
                                                <label class="form-check-label fw-bolder text-danger">Read-Only Mode</label>
                                                <small class="d-block text-muted mt-1" style="font-size:0.75rem; line-height: 1.2;">Hides all Save/Delete buttons.</small>
                                            </div>
                                        </div>
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