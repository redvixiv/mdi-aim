// ==========================================
// MDI AIMS - FLEET & LOGISTICS
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    window.fleetVehicles = [];
    window.fleetCpkChartInstance = null;
    
    if (document.getElementById('module-Fleet')) {
        window.loadFleetAnalytics();
    }

    // EXPLICIT MODAL RESETS TO PREVENT GHOSTING
    document.getElementById('vehicleModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('vehicleForm')?.reset();
        document.getElementById('vehicle_id').value = '';
    });

    document.getElementById('tripModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('tripForm')?.reset();
        document.getElementById('trip_id').value = '';
        window.toggleEndMileage();
    });

    document.getElementById('maintenanceModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('maintenanceForm')?.reset();
        document.getElementById('maintenance_id').value = '';
    });

    // MODAL OPEN LISTENERS TO UNLOCK BUTTONS
    document.getElementById('vehicleModal')?.addEventListener('show.bs.modal', function () {
        const btn = document.querySelector('#vehicleForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save'; }
    });

    document.getElementById('tripModal')?.addEventListener('show.bs.modal', function () {
        const btn = document.querySelector('#tripForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save Trip'; }
    });

    document.getElementById('maintenanceModal')?.addEventListener('show.bs.modal', function () {
        const btn = document.querySelector('#maintenanceForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save Expense'; }
    });

    // ==========================================
    // STRICT ANTI-DOUBLE-SUBMISSION LOCKS
    // ==========================================
    document.getElementById('vehicleForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = {
            vehicle_id: document.getElementById('vehicle_id').value,
            plate_no: document.getElementById('veh_plate').value.toUpperCase(),
            make_model: document.getElementById('veh_model').value.toUpperCase(),
            type: document.getElementById('veh_type').value,
            mileage: parseFloat(document.getElementById('veh_mileage').value) || 0,
            status: document.getElementById('veh_status').value
        };

        window.postData('fleet_vehicles', data, this, 'vehicleModal', () => {
            if (btn) btn.disabled = false;
            window.loadFleetVehicles();
        });
    });

    document.getElementById('tripForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const status = document.getElementById('trip_status').value;
        const startM = parseFloat(document.getElementById('trip_start_mileage').value) || 0;
        const endM = parseFloat(document.getElementById('trip_end_mileage').value) || 0;

        if (status === 'Completed' && endM <= startM) {
            alert("End Mileage must be greater than Start Mileage.");
            if (btn) btn.disabled = false;
            return;
        }

        const data = {
            trip_id: document.getElementById('trip_id').value,
            vehicle_id: document.getElementById('trip_veh_id').value,
            date: document.getElementById('trip_date').value,
            route: document.getElementById('trip_route').value.toUpperCase(),
            driver: document.getElementById('trip_driver').value.toUpperCase(),
            agent: document.getElementById('trip_agent').value.toUpperCase(),
            start_mileage: startM,
            end_mileage: endM,
            status: status
        };

        window.postData('fleet_trips', data, this, 'tripModal', () => {
            if (btn) btn.disabled = false;
            window.loadFleetTrips();
            window.loadFleetVehicles(); 
            window.loadFleetAnalytics(); 
        });
    });

    document.getElementById('maintenanceForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = {
            maintenance_id: document.getElementById('maintenance_id').value,
            vehicle_id: document.getElementById('maint_veh_id').value,
            account_id: document.getElementById('maint_account_id').value, 
            date: document.getElementById('maint_date').value,
            type: document.getElementById('maint_type').value,
            cost: parseFloat(document.getElementById('maint_cost').value) || 0,
            remarks: document.getElementById('maint_remarks').value.toUpperCase()
        };

        window.postData('fleet_maintenance', data, this, 'maintenanceModal', () => {
            if (btn) btn.disabled = false;
            window.loadFleetMaintenance();
            window.loadFleetAnalytics(); 
            if(typeof window.loadAccountingGL === 'function') window.loadAccountingGL(); 
        });
    });
    
    document.querySelector('button[data-bs-target="#fleet-dashboard"]')?.addEventListener('shown.bs.tab', function () {
        if(window.fleetCpkChartInstance) { window.fleetCpkChartInstance.resize(); }
    });
});

window.toggleEndMileage = function() {
    const stat = document.getElementById('trip_status')?.value;
    const endRow = document.getElementById('trip_end_mileage')?.parentElement;
    if(!stat || !endRow) return;

    if(stat === 'Completed') {
        endRow.style.display = 'flex';
        document.getElementById('trip_end_mileage').required = true;
    } else {
        endRow.style.display = 'none';
        document.getElementById('trip_end_mileage').required = false;
        document.getElementById('trip_end_mileage').value = '';
    }
};

window.fetchVehicleMileage = function() {
    const vid = document.getElementById('trip_veh_id').value;
    const v = window.fleetVehicles.find(x => x.Vehicle_ID == vid);
    if(v) {
        document.getElementById('trip_start_mileage').value = v.Current_Mileage;
    }
};

window.populateFleetDropdowns = function() {
    const tripsDrop = document.getElementById('trip_veh_id');
    const maintDrop = document.getElementById('maint_veh_id');
    if(!tripsDrop || !maintDrop) return;
    
    let html = '<option value="">Select Vehicle...</option>';
    window.fleetVehicles.forEach(v => {
        html += `<option value="${v.Vehicle_ID}">[${v.Plate_No}] ${v.Make_Model}</option>`;
    });
    
    tripsDrop.innerHTML = html;
    maintDrop.innerHTML = html;
};

window.loadFleetAccounts = function() {
    fetch('api/endpoints.php?table=accounting_coa')
    .then(r => r.json())
    .then(data => {
        const sel = document.getElementById('maint_account_id');
        sel.innerHTML = '<option value="">Select Payment Source...</option>';
        data.filter(a => a.Account_Type === 'Asset').forEach(acc => {
            sel.innerHTML += `<option value="${acc.Account_ID}">${acc.Account_Code} - ${acc.Account_Name}</option>`;
        });
    });
};

window.loadFleetAnalytics = function() {
    fetch('api/endpoints.php?table=fleet_analytics')
    .then(r => r.json())
    .then(data => {
        let totalDistance = 0;
        let totalFuel = 0;
        let labels = [];
        let cpkData = [];

        data.forEach(v => {
            const dist = parseFloat(v.Total_Distance) || 0;
            const fuel = parseFloat(v.Total_Fuel) || 0;
            totalDistance += dist;
            totalFuel += fuel;
            
            const cpk = dist > 0 ? (fuel / dist) : 0;
            
            if(dist > 0 || fuel > 0) {
                labels.push(`[${v.Plate_No}]`);
                cpkData.push(cpk.toFixed(2));
            }
        });

        document.getElementById('kpi_distance').innerText = window.formatQuantity(totalDistance);
        document.getElementById('kpi_fuel').innerText = window.formatCurrency(totalFuel);
        const avgCpk = totalDistance > 0 ? (totalFuel / totalDistance) : 0;
        document.getElementById('kpi_avg_cpk').innerText = avgCpk.toFixed(2);

        const ctx = document.getElementById('fleetCpkChart');
        if(!ctx) return;
        
        if (window.fleetCpkChartInstance) {
            window.fleetCpkChartInstance.destroy(); 
        }

        window.fleetCpkChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Fuel Cost per Kilometer (₱)',
                    data: cpkData,
                    backgroundColor: '#10b981', 
                    borderRadius: 4,
                    barPercentage: 0.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Pesos (₱) per KM', font: {weight: 'bold'} } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                }
            }
        });
    });
};

window.loadFleetVehicles = function() {
    fetch('api/endpoints.php?table=fleet_vehicles')
    .then(r => r.json())
    .then(data => {
        window.fleetVehicles = data || [];
        window.populateFleetDropdowns();

        const tbody = document.querySelector('#vehiclesTable tbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted font-monospace">No vehicles registered yet.</td></tr>';
            return;
        }

        data.forEach(row => {
            const badgeClass = row.Status === 'Active' ? 'bg-success-subtle text-success border border-success-subtle' : 
                               (row.Status === 'Under Repair' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle');
            
            const editBtn = `<i class="bi bi-pencil-square text-primary" style="cursor:pointer;" title="Edit" onclick='editVehicle(${JSON.stringify(row).replace(/'/g, "\\'")})'></i>`;
            const delBtn = `<i class="bi bi-trash text-danger" style="cursor:pointer;" title="Delete" onclick="deleteFleetRecord('fleet_vehicles', ${row.Vehicle_ID})"></i>`;
            
            let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${editBtn} ${delBtn}</div>`;
            if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) {
                actionsHTML = `<span class="badge bg-light text-muted border">LOCKED</span>`;
            }

            tbody.innerHTML += `<tr>
                <td class="fw-bold text-dark font-monospace">${row.Plate_No}</td>
                <td>${row.Make_Model}</td>
                <td>${row.Vehicle_Type}</td>
                <td class="text-end fw-bold font-monospace">${window.formatQuantity(row.Current_Mileage)}</td>
                <td class="text-center"><span class="badge ${badgeClass}">${row.Status}</span></td>
                <td class="text-center">${actionsHTML}</td>
            </tr>`;
        });
    });
};

window.loadFleetTrips = function() {
    fetch('api/endpoints.php?table=fleet_trips')
    .then(r => r.json())
    .then(data => {
        const tbody = document.querySelector('#tripsTable tbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted font-monospace">No trips logged yet.</td></tr>';
            return;
        }

        data.forEach(row => {
            const badgeClass = row.Status === 'Completed' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-info-subtle text-info-emphasis border border-info-subtle';
            const distance = row.Status === 'Completed' ? (parseFloat(row.End_Mileage) - parseFloat(row.Start_Mileage)).toFixed(2) : '-';
            
            const editBtn = `<i class="bi bi-pencil-square text-info" style="cursor:pointer;" title="Edit" onclick='editTrip(${JSON.stringify(row).replace(/'/g, "\\'")})'></i>`;
            const delBtn = `<i class="bi bi-trash text-danger" style="cursor:pointer;" title="Delete" onclick="deleteFleetRecord('fleet_trips', ${row.Trip_ID})"></i>`;
            
            let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${editBtn} ${delBtn}</div>`;
            if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) {
                actionsHTML = `<span class="badge bg-light text-muted border">LOCKED</span>`;
            }

            tbody.innerHTML += `<tr>
                <td>${window.formatDate(row.Trip_Date)}</td>
                <td class="fw-bold text-dark"><span class="text-muted font-monospace me-2">[${row.Plate_No}]</span></td>
                <td>${row.Route}</td>
                <td>${row.Driver_Name} ${row.Agent_Name ? ' / ' + row.Agent_Name : ''}</td>
                <td class="text-center fw-bold">${distance}</td>
                <td class="text-center"><span class="badge ${badgeClass}">${row.Status}</span></td>
                <td class="text-center">${actionsHTML}</td>
            </tr>`;
        });
    });
};

window.loadFleetMaintenance = function() {
    fetch('api/endpoints.php?table=fleet_maintenance')
    .then(r => r.json())
    .then(data => {
        const tbody = document.querySelector('#maintenanceTable tbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted font-monospace">No maintenance records found.</td></tr>';
            return;
        }

        data.forEach(row => {
            const editBtn = `<i class="bi bi-pencil-square text-warning" style="cursor:pointer;" title="Edit" onclick='editMaintenance(${JSON.stringify(row).replace(/'/g, "\\'")})'></i>`;
            const delBtn = `<i class="bi bi-trash text-danger" style="cursor:pointer;" title="Delete" onclick="deleteFleetRecord('fleet_maintenance', ${row.Maintenance_ID})"></i>`;
            
            let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${editBtn} ${delBtn}</div>`;
            if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) {
                actionsHTML = `<span class="badge bg-light text-muted border">LOCKED</span>`;
            }

            tbody.innerHTML += `<tr>
                <td>${window.formatDate(row.Service_Date)}</td>
                <td class="fw-bold text-dark"><span class="text-muted font-monospace me-2">[${row.Plate_No}]</span></td>
                <td><span class="badge bg-light text-dark border">${row.Service_Type}</span></td>
                <td class="small text-muted">${row.Remarks || '-'}</td>
                <td class="text-end text-danger fw-bold">₱ ${window.formatCurrency(row.Cost)}</td>
                <td class="text-center">${actionsHTML}</td>
            </tr>`;
        });
    });
};

window.editVehicle = function(v) {
    document.getElementById('vehicleForm').reset();
    document.getElementById('vehicle_id').value = v.Vehicle_ID;
    document.getElementById('veh_plate').value = v.Plate_No;
    document.getElementById('veh_model').value = v.Make_Model;
    document.getElementById('veh_type').value = v.Vehicle_Type;
    document.getElementById('veh_mileage').value = v.Current_Mileage;
    document.getElementById('veh_status').value = v.Status;
    new bootstrap.Modal(document.getElementById('vehicleModal')).show();
};

window.editTrip = function(t) {
    document.getElementById('tripForm').reset();
    document.getElementById('trip_id').value = t.Trip_ID;
    document.getElementById('trip_veh_id').value = t.Vehicle_ID;
    document.getElementById('trip_date').value = t.Trip_Date;
    document.getElementById('trip_route').value = t.Route;
    document.getElementById('trip_driver').value = t.Driver_Name;
    document.getElementById('trip_agent').value = t.Agent_Name;
    document.getElementById('trip_start_mileage').value = t.Start_Mileage;
    document.getElementById('trip_end_mileage').value = t.End_Mileage;
    document.getElementById('trip_status').value = t.Status;
    window.toggleEndMileage();
    new bootstrap.Modal(document.getElementById('tripModal')).show();
};

window.editMaintenance = function(m) {
    document.getElementById('maintenanceForm').reset();
    document.getElementById('maintenance_id').value = m.Maintenance_ID;
    document.getElementById('maint_veh_id').value = m.Vehicle_ID;
    document.getElementById('maint_date').value = m.Service_Date;
    document.getElementById('maint_type').value = m.Service_Type;
    document.getElementById('maint_cost').value = m.Cost;
    document.getElementById('maint_remarks').value = m.Remarks;
    
    window.loadFleetAccounts();
    setTimeout(() => { document.getElementById('maint_account_id').value = m.Account_ID || ''; }, 200);
    new bootstrap.Modal(document.getElementById('maintenanceModal')).show();
};

window.deleteFleetRecord = function(table, id) {
    let msg = "Are you sure you want to delete this record?";
    if(table === 'fleet_maintenance') msg += "\n\nNote: If this generated a Journal Entry, you must delete the Journal Entry manually in the Accounting module to balance your books.";
    
    if (confirm(msg)) {
        fetch(`api/endpoints.php?table=${table}&id=${id}`, { method: 'DELETE' })
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') {
                if(table === 'fleet_vehicles') window.loadFleetVehicles();
                if(table === 'fleet_trips') window.loadFleetTrips();
                if(table === 'fleet_maintenance') window.loadFleetMaintenance();
                window.loadFleetAnalytics(); 
            } else alert("Error: " + res.message);
        });
    }
};