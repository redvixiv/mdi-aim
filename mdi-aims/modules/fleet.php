<div id="module-Fleet" class="module-container p-2">          
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
        .kpi-card { background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }     
    </style>     
    <div class="d-flex justify-content-between align-items-center mb-2">         
        <h4 class="erp-header mb-0"><i class="bi bi-truck-front-fill text-muted me-2"></i>Fleet & Logistics</h4>     
    </div>          
    <ul class="nav erp-nav" id="fleetTabs" role="tablist">         
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#fleet-dashboard" type="button">Dashboard</button></li>         
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#fleet-vehicles" type="button">Vehicle Roster</button></li>         
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#fleet-trips" type="button">Dispatch & Trip Logs</button></li>         
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#fleet-maintenance" type="button">Fuel & Maintenance</button></li>     
    </ul>     
    <div class="tab-content" id="fleetTabsContent">                  
        <!-- NEW: DASHBOARD & ANALYTICS TAB -->         
        <div class="tab-pane fade show active" id="fleet-dashboard">             
            <div class="row g-3 mb-4">                 
                <div class="col-md-4">                     
                    <div class="kpi-card border-bottom border-4 border-primary">                         
                        <div class="text-muted small fw-bolder text-uppercase mb-1">Total Fleet Distance</div>                         
                        <h2 class="mb-0 fw-bolder text-dark"><span id="kpi_distance">0</span> <span class="fs-6 text-muted">km</span></h2>                     
                    </div>                 
                </div>                 
                <div class="col-md-4">                     
                    <div class="kpi-card border-bottom border-4 border-danger">                         
                        <div class="text-muted small fw-bolder text-uppercase mb-1">Total Fuel Cost</div>                         
                        <h2 class="mb-0 fw-bolder text-danger">₱<span id="kpi_fuel">0.00</span></h2>                     
                    </div>                 
                </div>                 
                <div class="col-md-4">                     
                    <div class="kpi-card border-bottom border-4 border-success">                         
                        <div class="text-muted small fw-bolder text-uppercase mb-1">Fleet Avg Cost / KM</div>                         
                        <h2 class="mb-0 fw-bolder text-success">₱<span id="kpi_avg_cpk">0.00</span> <span class="fs-6 text-muted">/ km</span></h2>                     
                    </div>                 
                </div>             
            </div>                          
            <div class="erp-table-container p-4">                 
                <h6 class="fw-bolder text-uppercase text-secondary mb-4"><i class="bi bi-bar-chart-fill me-2"></i>Cost-Per-Kilometer (CPK) by Vehicle</h6>                 
                <div style="height: 350px; width: 100%;">                     
                    <canvas id="fleetCpkChart"></canvas>                 
                </div>             
            </div>         
        </div>         
        <!-- VEHICLES TAB -->         
        <div class="tab-pane fade" id="fleet-vehicles">             
            <div class="d-flex justify-content-between align-items-center mb-4">                 
                <button class="btn btn-erp btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#vehicleModal" onclick="document.getElementById('vehicleForm').reset(); document.getElementById('vehicle_id').value='';"><i class="bi bi-plus-circle me-2"></i>Register Vehicle</button>                 
                <div class="input-group w-25 shadow-sm">                     
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>                     
                    <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#vehiclesTable" placeholder="Search vehicles...">                 
                </div>             
            </div>             
            <div class="erp-table-container" style="max-height: 60vh; overflow-y: auto;">                 
                <table class="erp-table" id="vehiclesTable">                     
                    <thead style="position: sticky; top: 0; z-index: 1;">                         
                        <tr><th>Plate No.</th><th>Make / Model</th><th>Vehicle Type</th><th class="text-end">Current Mileage (km)</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr>                     
                    </thead>                     
                    <tbody></tbody>                 
                </table>             
            </div>         
        </div>         
        <!-- TRIP LOGS TAB -->         
        <div class="tab-pane fade" id="fleet-trips">             
            <div class="d-flex justify-content-between align-items-center mb-4">                 
                <button class="btn btn-erp btn-info text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#tripModal" onclick="document.getElementById('tripForm').reset(); document.getElementById('trip_id').value=''; document.getElementById('trip_end_mileage').parentElement.style.display='none'; document.getElementById('trip_date').valueAsDate = new Date();"><i class="bi bi-send-fill me-2"></i>Dispatch Trip</button>                 
                <div class="input-group w-25 shadow-sm">                     
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>                     
                    <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#tripsTable" placeholder="Search trips...">                 
                </div>             
            </div>             
            <div class="erp-table-container" style="max-height: 60vh; overflow-y: auto;">                 
                <table class="erp-table" id="tripsTable">                     
                    <thead style="position: sticky; top: 0; z-index: 1;">                         
                        <tr><th>Date</th><th>Vehicle</th><th>Route</th><th>Driver / Agent</th><th class="text-center">Distance (km)</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr>                     
                    </thead>                     
                    <tbody></tbody>                 
                </table>             
            </div>         
        </div>         
        <!-- MAINTENANCE TAB -->         
        <div class="tab-pane fade" id="fleet-maintenance">             
            <div class="d-flex justify-content-between align-items-center mb-4">                 
                <button class="btn btn-erp btn-warning text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#maintenanceModal" onclick="document.getElementById('maintenanceForm').reset(); document.getElementById('maintenance_id').value=''; document.getElementById('maint_date').valueAsDate = new Date(); window.loadFleetAccounts();"><i class="bi bi-tools me-2"></i>Log Expense</button>                 
                <div class="input-group w-25 shadow-sm">                     
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>                     
                    <input type="text" class="form-control border-start-0 ps-0 search-bar border-0" data-target="#maintenanceTable" placeholder="Search records...">                 
                </div>             
            </div>             
            <div class="erp-table-container" style="max-height: 60vh; overflow-y: auto;">                 
                <table class="erp-table" id="maintenanceTable">                     
                    <thead style="position: sticky; top: 0; z-index: 1;">                         
                        <tr><th>Date</th><th>Vehicle</th><th>Service Type</th><th>Remarks</th><th class="text-end">Cost</th><th class="text-center">Actions</th></tr>                     
                    </thead>                     
                    <tbody></tbody>                 
                </table>             
            </div>         
        </div>     
    </div> 
</div> 
<!-- ================= MODALS ================= --> 
<div class="modal fade" id="vehicleModal" tabindex="-1">     
    <div class="modal-dialog">         
        <div class="modal-content border-0 odoo-modal-content">             
            <form id="vehicleForm">                 
                <input type="hidden" id="vehicle_id">                 
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">                     
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Save</button>                     
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>                 
                </div>                 
                <div class="modal-body p-4 bg-light">                     
                    <div class="odoo-sheet bg-white p-4 rounded-3 shadow-sm border-top border-4 border-primary">                         
                        <div class="odoo-field-group mb-3"><label class="odoo-label">Plate No.</label><input type="text" id="veh_plate" class="form-control odoo-input fw-bold fs-5 text-primary" required></div>                         
                        <div class="odoo-field-group mb-3"><label class="odoo-label">Make / Model</label><input type="text" id="veh_model" class="form-control odoo-input" placeholder="e.g. Isuzu Elf / Mitsubishi L300" required></div>                         
                        <div class="odoo-field-group mb-3">                             
                            <label class="odoo-label">Vehicle Type</label>                             
                            <select id="veh_type" class="form-select odoo-input" required>                                 
                                <option value="Delivery Truck">Delivery Truck</option>                                 
                                <option value="Delivery Van">Delivery Van</option>                                 
                                <option value="Motorcycle">Motorcycle</option>                                 
                                <option value="Service Vehicle">Service Vehicle</option>                             
                            </select>                         
                        </div>                         
                        <div class="odoo-field-group mb-3"><label class="odoo-label">Initial Mileage</label><input type="number" step="0.01" id="veh_mileage" class="form-control odoo-input" value="0"></div>                         
                        <div class="odoo-field-group mb-3">                             
                            <label class="odoo-label">Status</label>                             
                            <select id="veh_status" class="form-select odoo-input" required>                                 
                                <option value="Active">Active</option>                                 
                                <option value="Under Repair">Under Repair</option>                                 
                                <option value="Inactive">Inactive / Sold</option>                             
                            </select>                         
                        </div>                     
                    </div>                 
                </div>             
            </form>         
        </div>     
    </div> 
</div> 
<div class="modal fade" id="tripModal" tabindex="-1">     
    <div class="modal-dialog modal-lg">         
        <div class="modal-content border-0 odoo-modal-content">             
            <form id="tripForm">                 
                <input type="hidden" id="trip_id">                 
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">                     
                    <button type="submit" class="btn btn-info text-white btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Trip</button>                     
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>                 
                </div>                 
                <div class="modal-body p-4 bg-light">                     
                    <div class="odoo-sheet bg-white p-4 rounded-3 shadow-sm border-top border-4 border-info">                         
                        <div class="row">                             
                            <div class="col-md-6 pe-3 border-end">                                 
                                <div class="odoo-field-group mb-3">                                     
                                    <label class="odoo-label">Vehicle</label>                                     
                                    <select id="trip_veh_id" class="form-select odoo-input fw-bold" required onchange="fetchVehicleMileage()"><option value="">Select...</option></select>                                 
                                </div>                                 
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Date</label><input type="date" id="trip_date" class="form-control odoo-input" style="text-transform:none;" required></div>                                 
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Route</label><input type="text" id="trip_route" class="form-control odoo-input" placeholder="e.g. Route 1" required></div>                                 
                                <div class="odoo-field-group mb-3">                                     
                                    <label class="odoo-label">Status</label>                                     
                                    <select id="trip_status" class="form-select odoo-input fw-bold text-info" required onchange="toggleEndMileage()">                                         
                                        <option value="Dispatched">Dispatched (Ongoing)</option>                                         
                                        <option value="Completed">Completed</option>                                     
                                    </select>                                 
                                </div>                             
                            </div>                             
                            <div class="col-md-6 ps-3">                                 
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Driver Name</label><input type="text" id="trip_driver" class="form-control odoo-input" required></div>                                 
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Agent Name</label><input type="text" id="trip_agent" class="form-control odoo-input" placeholder="Optional"></div>                                 
                                <hr>                                 
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Start Mileage</label><input type="number" step="0.01" id="trip_start_mileage" class="form-control odoo-input text-muted" required></div>                                 
                                <div class="odoo-field-group mb-3" style="display:none;"><label class="odoo-label text-success fw-bold">End Mileage</label><input type="number" step="0.01" id="trip_end_mileage" class="form-control odoo-input fs-5 fw-bold text-success"></div>                             
                            </div>                         
                        </div>                     
                    </div>                 
                </div>             
            </form>         
        </div>     
    </div> 
</div> 
<div class="modal fade" id="maintenanceModal" tabindex="-1">     
    <div class="modal-dialog">         
        <div class="modal-content border-0 odoo-modal-content">             
            <form id="maintenanceForm">                 
                <input type="hidden" id="maintenance_id">                 
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">                     
                    <button type="submit" class="btn btn-warning text-white btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Expense</button>                     
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>                 
                </div>                 
                <div class="modal-body p-4 bg-light">                     
                    <div class="odoo-sheet bg-white p-4 rounded-3 shadow-sm border-top border-4 border-warning">                         
                        <div class="odoo-field-group mb-3"><label class="odoo-label">Vehicle</label><select id="maint_veh_id" class="form-select odoo-input fw-bold" required><option value="">Select...</option></select></div>                         
                        <div class="odoo-field-group mb-3"><label class="odoo-label">Date</label><input type="date" id="maint_date" class="form-control odoo-input" style="text-transform:none;" required></div>                         
                        <div class="odoo-field-group mb-3">                             
                            <label class="odoo-label">Type</label>                             
                            <select id="maint_type" class="form-select odoo-input" required>                                 
                                <option value="Fuel Refill">Fuel Refill</option>                                 
                                <option value="Preventive Maintenance (PMS)">Preventive Maintenance (PMS)</option>                                 
                                <option value="Repair / Parts">Repair / Parts Replacement</option>                                 
                                <option value="Registration / Insurance">Registration / Insurance</option>                             
                            </select>                         
                        </div>                                                  
                        <div class="odoo-field-group mb-3">                             
                            <label class="odoo-label text-success">Payment Acc.</label>                             
                            <select id="maint_account_id" class="form-select odoo-input fw-bold" required>                                 
                                <option value="">Loading accounts...</option>                             
                            </select>                         
                        </div>                                                  
                        <div class="odoo-field-group mb-3"><label class="odoo-label">Total Cost</label><input type="number" step="0.01" id="maint_cost" class="form-control odoo-input text-danger fw-bold" required></div>                         
                        <div class="odoo-field-group mb-3"><label class="odoo-label">Remarks</label><input type="text" id="maint_remarks" class="form-control odoo-input" placeholder="e.g. Changed oil, replaced tires"></div>                                                  
                        <small class="text-muted d-block text-center mt-3"><i class="bi bi-info-circle me-1"></i>Saving this will auto-generate a Journal Entry.</small>                     
                    </div>                 
                </div>             
            </form>         
        </div>     
    </div> 
</div>