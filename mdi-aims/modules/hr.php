<div id="module-HR" class="module-container p-2">
    
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
        <h4 class="erp-header mb-0"><i class="bi bi-people-fill text-muted me-2"></i>Human Resources</h4>
    </div>
    
    <ul class="nav erp-nav" id="hrTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#hr-employees" type="button">Employee Directory</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#hr-dtr" type="button">Timekeeping (DTR)</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#hr-payroll" type="button">Payroll Generation</button></li>
    </ul>

    <div class="tab-content" id="hrTabsContent">
        
        <!-- EMPLOYEES TAB -->
        <div class="tab-pane fade show active" id="hr-employees">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#employeeModal" onclick="document.getElementById('employeeForm').reset(); document.getElementById('emp_id').value='';"><i class="bi bi-person-plus-fill me-2"></i>Add Employee</button>
                <div class="input-group w-25 shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#employeesTable" placeholder="Search employees...">
                </div>
            </div>
            <div class="erp-table-container" style="max-height: 60vh; overflow-y: auto;">
                <table class="erp-table" id="employeesTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr><th>Emp No.</th><th>Full Name</th><th>Department</th><th>Position</th><th class="text-end">Base Rate</th><th class="text-center">Rate Type</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- DTR TAB -->
        <div class="tab-pane fade" id="hr-dtr">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-info text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#dtrModal" onclick="document.getElementById('dtrForm').reset(); document.getElementById('dtr_id').value='';"><i class="bi bi-clock-history me-2"></i>Encode DTR</button>
                <div class="input-group w-25 shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#dtrTable" placeholder="Search DTR records...">
                </div>
            </div>
            <div class="erp-table-container" style="max-height: 60vh; overflow-y: auto;">
                <table class="erp-table" id="dtrTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr><th>Employee</th><th>Cutoff Period</th><th class="text-center">Days Worked</th><th class="text-center">OT (Hrs)</th><th class="text-center">Late/UT (Hrs)</th><th class="text-center">Actions</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- PAYROLL TAB -->
        <div class="tab-pane fade" id="hr-payroll">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-erp btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#generatePayrollModal"><i class="bi bi-gear-fill me-2"></i>Generate Payroll</button>
                <div class="input-group w-25 shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#payrollTable" placeholder="Search payroll...">
                </div>
            </div>
            <div class="erp-table-container" style="max-height: 60vh; overflow-y: auto;">
                <table class="erp-table" id="payrollTable">
                    <thead style="position: sticky; top: 0; z-index: 1;">
                        <tr><th>Date Generated</th><th>Employee</th><th>Cutoff Period</th><th class="text-end">Gross Pay</th><th class="text-end text-danger">Deductions</th><th class="text-end text-success">Net Pay</th><th class="text-center">Actions</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- ================= MODALS ================= -->

<div class="modal fade" id="employeeModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="employeeForm">
                <input type="hidden" id="emp_id">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Employee</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-person-vcard me-1"></i>Employee 201 File</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm border-top border-4 border-primary">
                        
                        <div class="row border-bottom pb-4 mb-4">
                            <div class="col-md-6 pe-4">
                                <label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">First Name</label>
                                <input type="text" id="emp_fname" class="form-control odoo-title-input fs-4" required>
                            </div>
                            <div class="col-md-6 ps-4">
                                <label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">Last Name</label>
                                <input type="text" id="emp_lname" class="form-control odoo-title-input fs-4" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 pe-lg-4">
                                <h6 class="fw-bolder text-uppercase text-secondary mb-3"><i class="bi bi-briefcase me-2"></i>Employment Details</h6>
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Department</label><input type="text" id="emp_dept" class="form-control odoo-input fw-bold" placeholder="e.g. Warehouse" required></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Position</label><input type="text" id="emp_pos" class="form-control odoo-input fw-bold" placeholder="e.g. Checker" required></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Hire Date</label><input type="date" id="emp_hire_date" class="form-control odoo-input" style="text-transform:none;" required></div>
                                <div class="odoo-field-group mb-3">
                                    <label class="odoo-label">Status</label>
                                    <select id="emp_status" class="form-select odoo-input fw-bold" required>
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive / Terminated</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6 ps-lg-4 border-start">
                                <h6 class="fw-bolder text-uppercase text-primary mb-3"><i class="bi bi-cash me-2"></i>Compensation & Benefits</h6>
                                
                                <div class="d-flex gap-2 mb-4 p-3 bg-light rounded-3 border">
                                    <div class="w-50">
                                        <label class="text-muted small fw-bold text-uppercase mb-1">Rate Type</label>
                                        <select id="emp_rate_type" class="form-select border-0 fw-bold shadow-sm" required>
                                            <option value="Daily">Daily Rate</option>
                                            <option value="Monthly">Monthly Rate</option>
                                        </select>
                                    </div>
                                    <div class="w-50">
                                        <label class="text-primary small fw-bold text-uppercase mb-1">Base Amount (₱)</label>
                                        <input type="number" step="0.01" id="emp_base_rate" class="form-control border-0 fw-bold shadow-sm text-primary" required>
                                    </div>
                                </div>

                                <div class="odoo-field-group mb-2"><label class="odoo-label text-muted">SSS No.</label><input type="text" id="emp_sss" class="form-control odoo-input font-monospace"></div>
                                <div class="odoo-field-group mb-2"><label class="odoo-label text-muted">PhilHealth No.</label><input type="text" id="emp_phic" class="form-control odoo-input font-monospace"></div>
                                <div class="odoo-field-group mb-2"><label class="odoo-label text-muted">Pag-IBIG No.</label><input type="text" id="emp_hdmf" class="form-control odoo-input font-monospace"></div>
                                <div class="odoo-field-group mb-2"><label class="odoo-label text-muted">TIN</label><input type="text" id="emp_tin" class="form-control odoo-input font-monospace"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DTR Modal (ZKTeco Integrated) -->
<div class="modal fade" id="dtrModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="dtrForm">
                <input type="hidden" id="dtr_id">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-info text-white btn-sm px-4 fw-bold shadow-sm text-uppercase">Save DTR</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-clock me-1"></i>Timekeeping Entry</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm border-top border-4 border-info">
                        
                        <div class="d-flex justify-content-between align-items-end border-bottom pb-4 mb-4">
                            <div class="odoo-field-group mb-0 w-75">
                                <label class="odoo-label text-info">Select Employee</label>
                                <select id="dtr_emp_id" class="form-select odoo-input fw-bold fs-5 text-dark" required>
                                    <option value="">Loading Employees...</option>
                                </select>
                            </div>
                            
                            <!-- ZKTeco Import Button -->
                            <div class="w-25 text-end">
                                <input type="file" id="zktecoFileInput" accept=".dat, .txt, .csv" style="display: none;">
                                <button type="button" class="btn btn-sm btn-outline-dark fw-bold" onclick="document.getElementById('zktecoFileInput').click();">
                                    <i class="bi bi-fingerprint me-1"></i>Import ZKTeco Log
                                </button>
                            </div>
                        </div>
                        
                        <div class="row mb-5">
                            <div class="col-md-6 pe-3">
                                <div class="odoo-field-group mb-0">
                                    <label class="odoo-label">Cutoff Start</label>
                                    <input type="date" id="dtr_start" class="form-control odoo-input fw-bold text-muted" style="text-transform:none;" required>
                                </div>
                            </div>
                            <div class="col-md-6 ps-3 border-start">
                                <div class="odoo-field-group mb-0">
                                    <label class="odoo-label">Cutoff End</label>
                                    <input type="date" id="dtr_end" class="form-control odoo-input fw-bold text-muted" style="text-transform:none;" required>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="fw-bolder text-uppercase text-secondary mb-3"><i class="bi bi-calculator me-2"></i>Attendance Hours Captured</h6>
                        <div class="row p-3 bg-light border rounded-3">
                            <div class="col-md-4 border-end">
                                <label class="text-muted small fw-bold text-uppercase mb-1">Days Worked</label>
                                <input type="number" step="0.01" id="dtr_days" class="form-control border-0 fw-bold shadow-sm text-center fs-5 text-success" placeholder="e.g. 15" required>
                            </div>
                            <div class="col-md-4 border-end">
                                <label class="text-muted small fw-bold text-uppercase mb-1">Overtime (Hrs)</label>
                                <input type="number" step="0.01" id="dtr_ot" class="form-control border-0 fw-bold shadow-sm text-center fs-5 text-primary" value="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small fw-bold text-uppercase mb-1">Late / UT (Hrs)</label>
                                <input type="number" step="0.01" id="dtr_late" class="form-control border-0 fw-bold shadow-sm text-center fs-5 text-danger" value="0" required>
                            </div>
                        </div>
                        
                        <div id="zkteco_status_msg" class="mt-3 text-center fw-bold text-success" style="display: none;"></div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="generatePayrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="generatePayrollForm">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-bold shadow-sm text-uppercase">Process Payroll</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Cancel</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-gear-fill me-1"></i>Run Payroll Batch</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm border-top border-4 border-success">
                        <h6 class="fw-bolder text-uppercase text-secondary mb-4 text-center">Select Cutoff Period to Process</h6>
                        <div class="odoo-field-group mb-3">
                            <label class="odoo-label text-primary">Cutoff Start</label>
                            <input type="date" id="payroll_start" class="form-control odoo-input fw-bold fs-5" style="text-transform:none;" required>
                        </div>
                        <div class="odoo-field-group mb-0">
                            <label class="odoo-label text-primary">Cutoff End</label>
                            <input type="date" id="payroll_end" class="form-control odoo-input fw-bold fs-5" style="text-transform:none;" required>
                        </div>
                        <small class="d-block text-muted mt-4 text-center">
                            The system will automatically find all DTRs within this period, calculate statutory deductions, and post the Salaries Expense to the General Ledger.
                        </small>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>