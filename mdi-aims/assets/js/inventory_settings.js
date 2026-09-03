// ==========================================
// MDI AIMS - SETTINGS & CONFIGURATION
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    
    window.systemDropdownsData = [];
    if(document.getElementById('module-Settings')) { window.loadCompanyProfile(); }

    // ==========================================
    // EXPLICIT MODAL RESETS (ANTI-GHOSTING)
    // ==========================================
    const settingModals = ['warehouseModal', 'centerModal', 'dsConfigModal', 'newDropdownModal', 'userModal', 'companyProfileModal', 'financialSettingsModal'];
    
    settingModals.forEach(modalId => {
        document.getElementById(modalId)?.addEventListener('hidden.bs.modal', function () {
            const form = this.querySelector('form');
            if(form) {
                form.reset();
                const hiddenIds = ['w_id', 'center_id', 'ds_id', 'dropdown_id', 'u_id'];
                hiddenIds.forEach(id => { if(document.getElementById(id)) document.getElementById(id).value = ''; });
                form.querySelectorAll('select').forEach(sel => sel.removeAttribute('data-edit-val'));
                const faPanel = document.getElementById('field_agent_settings');
                if(faPanel) faPanel.style.display = 'none';
                const parentGrp = document.getElementById('center_parent_group');
                if(parentGrp) parentGrp.style.display = 'none';
                if (modalId === 'userModal') {
                    document.querySelectorAll('.perm-module, .perm-sub').forEach(cb => cb.checked = false);
                    const ro = document.getElementById('perm_readonly'); if(ro) ro.checked = false;
                }
            }
        });
        // Ensure submit buttons are enabled on open
        document.getElementById(modalId)?.addEventListener('show.bs.modal', function () {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) btn.disabled = false;
        });
    });

    document.getElementById('companyProfileModal')?.addEventListener('show.bs.modal', function() {
        if(typeof window.bindPhilLocations === 'function') window.bindPhilLocations('cp_province', 'cp_city', 'cp_brgy');
        window.loadCompanyProfile(); 
    });

    // ==========================================
    // STRICT ANTI-DOUBLE-SUBMISSION LOCKS
    // ==========================================
    document.getElementById('companyProfileForm')?.addEventListener('submit', function(e) {
        e.preventDefault(); 
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        const formData = new FormData(this); 
        formData.append('table', 'company_profile'); 
        
        const p=document.getElementById('cp_province'); const c=document.getElementById('cp_city'); const b=document.getElementById('cp_brgy'); 
        if(p && p.value) formData.set('province', p.options[p.selectedIndex].text.toUpperCase()); 
        if(c && c.value) formData.set('city', c.options[c.selectedIndex].text.toUpperCase()); 
        if(b && b.value) formData.set('barangay', b.options[b.selectedIndex].text.toUpperCase()); 

        fetch(`api/endpoints.php`, { method: 'POST', body: formData }).then(r=>r.json()).then(res=>{ 
            btn.innerHTML = originalText; btn.disabled = false;
            if(res.status==='success'){ 
                bootstrap.Modal.getInstance(document.getElementById('companyProfileModal'))?.hide(); 
                window.loadCompanyProfile(); 
            } else { alert("Error saving profile: " + res.message); }
        }).catch(() => { btn.innerHTML = originalText; btn.disabled = false; }); 
    });

    document.getElementById('financialSettingsForm')?.addEventListener('submit', function(e) {
        e.preventDefault(); 
        e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = { lock_date: document.getElementById('fs_lock_date').value };
        window.postData('update_lock_date', data, this, 'financialSettingsModal', () => {
            if(btn) btn.disabled = false;
            window.loadCompanyProfile();
        }); 
    });

    document.getElementById('warehouseForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = { Warehouse_ID: document.getElementById('w_id').value, Warehouse_Name: document.getElementById('w_name').value.trim(), Location: document.getElementById('w_location').value.trim() };
        window.postData('warehouses', data, this, 'warehouseModal', () => {
            if(btn) btn.disabled = false;
            if(typeof window.loadWarehousesSettings === 'function') window.loadWarehousesSettings();
            if(typeof window.loadWarehouses === 'function') window.loadWarehouses();
        });
    });

    document.getElementById('dropdownSettingsForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = { id: document.getElementById('dropdown_id').value || null, Dropdown_Type: document.getElementById('dropdown_type').value, Option_Value: document.getElementById('option_value').value.toUpperCase().trim(), Parent_Link: document.getElementById('parent_link').value.toUpperCase().trim() };
        window.postData('system_dropdowns', data, this, 'newDropdownModal', () => {
            if(btn) btn.disabled = false;
            window.loadDropdowns();
        });
    });

    document.getElementById('centerForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const type = document.getElementById('center_type').value;
        const parent = type === 'Area' ? document.getElementById('center_parent').value : '';

        const data = {
            id: document.getElementById('center_id').value || null, Dropdown_Type: type,
            Center_Code: document.getElementById('center_code').value.toUpperCase().trim(), Option_Value: document.getElementById('center_name').value.toUpperCase().trim(),
            Center_In_Charge: document.getElementById('center_in_charge').value.toUpperCase().trim(), Parent_Link: parent, Linked_Warehouse_ID: document.getElementById('center_warehouse').value || null 
        };

        window.postData('system_dropdowns', data, this, 'centerModal', () => {
            if(btn) btn.disabled = false;
            window.loadDropdowns();
        });
    });

    document.getElementById('dsConfigForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = {
            id: document.getElementById('ds_id').value || null, Dropdown_Type: document.getElementById('ds_area_type').value,
            Option_Value: document.getElementById('ds_area_name').value.toUpperCase().trim(), Route_In_Charge: document.getElementById('ds_route_in_charge').value.toUpperCase().trim(),
            Parent_Link: '', Linked_Warehouse_ID: document.getElementById('ds_warehouse').value || null 
        };

        window.postData('system_dropdowns', data, this, 'dsConfigModal', () => {
            if(btn) btn.disabled = false;
            window.loadDropdowns();
        });
    });

    document.getElementById('userForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const permissions = { modules: [], accounting_tabs: [], is_readonly: document.getElementById('perm_readonly').checked };
        document.querySelectorAll('.perm-module:checked').forEach(cb => permissions.modules.push(cb.value));
        document.querySelectorAll('.perm-sub:checked').forEach(cb => permissions.accounting_tabs.push(cb.value));

        const data = {
            user_id: document.getElementById('u_id').value,
            username: document.getElementById('u_username').value,
            password: document.getElementById('u_password').value, 
            role: document.getElementById('u_role').value,
            agent_type: document.getElementById('u_agent_type') ? document.getElementById('u_agent_type').value : null,
            linked_entity: document.getElementById('u_linked_entity') ? document.getElementById('u_linked_entity').value : null,
            permissions: permissions
        };

        window.postData('users', data, this, 'userModal', () => {
            if(btn) btn.disabled = false;
            window.loadUsers();
        });
    });

    // ==========================================
    // USER MANAGEMENT LOGIC
    // ==========================================
    document.getElementById('userModal')?.addEventListener('show.bs.modal', function () {
        if(!document.getElementById('u_id').value) {
            const passFld = document.getElementById('u_password');
            if(passFld) {
                passFld.required = true;
                passFld.placeholder = "Enter secure password";
            }
        }
    });

    document.getElementById('u_role')?.addEventListener('change', function() {
        const faPanel = document.getElementById('field_agent_settings');
        if (this.value === 'Field Agent') {
            faPanel.style.display = 'block';
            document.getElementById('u_agent_type').required = true;
            document.getElementById('u_linked_entity').required = true;
        } else {
            faPanel.style.display = 'none';
            document.getElementById('u_agent_type').required = false;
            document.getElementById('u_agent_type').value = '';
            document.getElementById('u_linked_entity').required = false;
            document.getElementById('u_linked_entity').innerHTML = '<option value="">Select Agent Type First...</option>';
        }
    });

    document.getElementById('u_agent_type')?.addEventListener('change', function() {
        const entSel = document.getElementById('u_linked_entity');
        entSel.innerHTML = '<option value="">Loading...</option>';
        
        if (this.value === 'DS') {
            fetch('api/endpoints.php?table=system_dropdowns').then(r=>r.json()).then(data => {
                entSel.innerHTML = '<option value="">Select DS Route/Area...</option>';
                const dsAreas = Array.isArray(data) ? data.filter(d => ['Booking', 'Route', 'Mini Route'].includes(d.Dropdown_Type)) : [];
                dsAreas.forEach(a => entSel.innerHTML += `<option value="${a.Option_Value}">${a.Option_Value} (${a.Dropdown_Type})</option>`);
                if(entSel.getAttribute('data-edit-val')) { entSel.value = entSel.getAttribute('data-edit-val'); entSel.removeAttribute('data-edit-val'); }
            });
        } else if (this.value === 'YL') {
            fetch('api/endpoints.php?table=dealers').then(r=>r.json()).then(data => {
                entSel.innerHTML = '<option value="">Select Yakult Lady Dealer...</option>';
                if(Array.isArray(data)) {
                    data.forEach(d => entSel.innerHTML += `<option value="${d.Dealer_ID}">${d.First_Name} ${d.Last_Name} (${d.Dealer_No})</option>`);
                }
                if(entSel.getAttribute('data-edit-val')) { entSel.value = entSel.getAttribute('data-edit-val'); entSel.removeAttribute('data-edit-val'); }
            });
        } else {
            entSel.innerHTML = '<option value="">Select Agent Type First...</option>';
        }
    });

    // ==========================================
    // TAB RELOAD LISTENERS
    // ==========================================
    document.getElementById('users-tab')?.addEventListener('click', () => { if(typeof window.loadUsers === 'function') window.loadUsers(); });
    document.getElementById('system-tab')?.addEventListener('click', () => { if(typeof window.loadAuditLogs === 'function') window.loadAuditLogs(); });
    document.getElementById('warehouses-tab')?.addEventListener('click', () => { if(typeof window.loadWarehousesSettings === 'function') window.loadWarehousesSettings(); });
    document.getElementById('dropdowns-tab')?.addEventListener('click', () => { if(typeof window.loadDropdowns === 'function') window.loadDropdowns(); });
    document.getElementById('centers-tab')?.addEventListener('click', () => { if(typeof window.loadDropdowns === 'function') window.loadDropdowns(); });
    document.getElementById('dsconfig-tab')?.addEventListener('click', () => { if(typeof window.loadDropdowns === 'function') window.loadDropdowns(); });

    document.getElementById('center_type')?.addEventListener('change', function() {
        const parentGrp = document.getElementById('center_parent_group');
        const parentSel = document.getElementById('center_parent');
        const nameLbl = document.getElementById('lbl_center_name');
        const chargeLbl = document.getElementById('lbl_center_in_charge');
        
        if (this.value === 'Area') {
            parentGrp.style.display = 'block'; parentSel.required = true;
            nameLbl.innerText = 'Child (Area Name)'; chargeLbl.innerText = 'Area In Charge / Supervisor';
        } else {
            parentGrp.style.display = 'none'; parentSel.required = false; parentSel.value = '';
            nameLbl.innerText = 'Parent (Center Name)'; chargeLbl.innerText = 'Center In Charge / Supervisor';
        }
    });

    document.getElementById('centerModal')?.addEventListener('show.bs.modal', function () {
        const parentSel = document.getElementById('center_parent');
        if (parentSel) {
            parentSel.innerHTML = '<option value="">Loading Centers...</option>';
            fetch('api/endpoints.php?table=system_dropdowns').then(r => r.json()).then(data => {
                parentSel.innerHTML = '<option value="">Select Parent Center...</option>';
                if (Array.isArray(data)) {
                    const centers = data.filter(opt => opt.Dropdown_Type === 'Center');
                    centers.forEach(opt => { parentSel.innerHTML += `<option value="${opt.Option_Value}">${opt.Option_Value}</option>`; });
                }
                if (parentSel.getAttribute('data-edit-val')) { parentSel.value = parentSel.getAttribute('data-edit-val'); parentSel.removeAttribute('data-edit-val'); }
            });
        }
        const whSel = document.getElementById('center_warehouse');
        if (whSel) {
            whSel.innerHTML = '<option value="">Select Physical Warehouse...</option>';
            if (Array.isArray(window.globalWarehousesList)) {
                window.globalWarehousesList.forEach(w => { whSel.innerHTML += `<option value="${w.Warehouse_ID}">${w.Warehouse_Name}</option>`; });
            }
            if (whSel.getAttribute('data-edit-val')) { whSel.value = whSel.getAttribute('data-edit-val'); whSel.removeAttribute('data-edit-val'); }
        }
    });

    document.getElementById('dsConfigModal')?.addEventListener('show.bs.modal', function () {
        const whSel = document.getElementById('ds_warehouse');
        if (whSel) {
            whSel.innerHTML = '<option value="">Select Physical Warehouse...</option>';
            if (Array.isArray(window.globalWarehousesList)) {
                window.globalWarehousesList.forEach(w => { whSel.innerHTML += `<option value="${w.Warehouse_ID}">${w.Warehouse_Name}</option>`; });
            }
            if (whSel.getAttribute('data-edit-val')) { whSel.value = whSel.getAttribute('data-edit-val'); whSel.removeAttribute('data-edit-val'); }
        }
    });

    if (document.getElementById('module-Settings')) {
        setTimeout(() => { 
            if(typeof window.loadUsers === 'function') window.loadUsers(); 
            if(typeof window.loadWarehousesSettings === 'function') window.loadWarehousesSettings(); 
            if(typeof window.loadDropdowns === 'function') window.loadDropdowns(); 
        }, 500);
    }
});

// SAFE ID-BASED EDIT TO PREVENT JSON CRASHES
window.editDropdown = function(type, id) {
    if (!Array.isArray(window.systemDropdownsData)) return;
    const row = window.systemDropdownsData.find(d => d.ID == id);
    if (!row) return;
    
    if (type === 'generic') {
        document.getElementById('dropdown_id').value = row.ID;
        document.getElementById('dropdown_type').value = row.Dropdown_Type;
        document.getElementById('option_value').value = row.Option_Value;
        document.getElementById('parent_link').value = row.Parent_Link || '';
        new bootstrap.Modal(document.getElementById('newDropdownModal')).show();
    } 
    else if (type === 'center') {
        document.getElementById('center_id').value = row.ID;
        document.getElementById('center_type').value = row.Dropdown_Type;
        document.getElementById('center_code').value = row.Center_Code || '';
        document.getElementById('center_name').value = row.Option_Value;
        document.getElementById('center_in_charge').value = row.Center_In_Charge || '';
        
        const whSel = document.getElementById('center_warehouse');
        if (whSel) whSel.setAttribute('data-edit-val', row.Linked_Warehouse_ID || '');
        const parentSel = document.getElementById('center_parent');
        if (row.Dropdown_Type === 'Area') {
            document.getElementById('center_parent_group').style.display = 'block';
            parentSel.required = true;
            if(parentSel) parentSel.setAttribute('data-edit-val', row.Parent_Link || '');
        } else {
            document.getElementById('center_parent_group').style.display = 'none';
            parentSel.required = false;
        }
        
        new bootstrap.Modal(document.getElementById('centerModal')).show();
    }
    else if (type === 'ds') {
        document.getElementById('ds_id').value = row.ID;
        document.getElementById('ds_area_type').value = row.Dropdown_Type;
        document.getElementById('ds_area_name').value = row.Option_Value;
        document.getElementById('ds_route_in_charge').value = row.Route_In_Charge || '';
        const whSel = document.getElementById('ds_warehouse');
        if (whSel) whSel.setAttribute('data-edit-val', row.Linked_Warehouse_ID || '');
        new bootstrap.Modal(document.getElementById('dsConfigModal')).show();
    }
};

window.loadDropdowns = function() {
    const tGen = document.querySelector('#dropdownsTable tbody');
    const tCen = document.querySelector('#centersTable tbody');
    const tDs = document.querySelector('#dsConfigTable tbody');
    
    fetch('api/endpoints.php?table=system_dropdowns&_t=' + Date.now())
    .then(r => r.json())
    .then(data => {
        if(tGen) tGen.innerHTML = '';
        if(tCen) tCen.innerHTML = '';
        if(tDs) tDs.innerHTML = '';

        if (!Array.isArray(data)) {
            const msg = data && data.message ? data.message : "Error loading data.";
            if(tGen) tGen.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger font-monospace">${msg}</td></tr>`;
            if(tCen) tCen.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger font-monospace">${msg}</td></tr>`;
            if(tDs) tDs.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger font-monospace">${msg}</td></tr>`;
            return;
        }

        if (data.length === 0) {
            if(tGen) tGen.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted font-monospace">No options configured yet.</td></tr>';
            if(tCen) tCen.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted font-monospace">No centers configured yet.</td></tr>';
            if(tDs) tDs.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted font-monospace">No DS areas configured yet.</td></tr>';
            return;
        }

        window.systemDropdownsData = data; 
        data.sort((a, b) => {
            const typeA = a.Dropdown_Type || '';
            const typeB = b.Dropdown_Type || '';
            const valA = a.Option_Value || '';
            const valB = b.Option_Value || '';
            if (typeA !== typeB) return typeA.localeCompare(typeB);
            return valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' });
        });

        let hasGen = false, hasCen = false, hasDs = false;
        let dsCounter = 1;

        data.forEach(row => {
            const getActionBtn = (type) => `<td class="text-center">
                <div class="action-icons d-flex justify-content-center gap-3">
                    <i class="bi bi-pencil-square text-primary" style="cursor:pointer;" title="Edit" onclick="editDropdown('${type}', ${row.ID})"></i>
                    <i class="bi bi-trash text-danger" style="cursor:pointer;" title="Delete" onclick="deleteRecord('system_dropdowns', ${row.ID})"></i>
                </div>
            </td>`;
            
            let whName = '-';
            if (row.Linked_Warehouse_ID && Array.isArray(window.globalWarehousesList)) {
                const wh = window.globalWarehousesList.find(w => w.Warehouse_ID == row.Linked_Warehouse_ID);
                if (wh) whName = `<span class="badge bg-light text-dark border border-success"><i class="bi bi-building text-success me-1"></i>${wh.Warehouse_Name}</span>`;
            }

            if (row.Dropdown_Type === 'Center' || row.Dropdown_Type === 'Area') {
                hasCen = true;
                const cType = `<span class="badge ${row.Dropdown_Type === 'Center' ? 'bg-info-subtle text-info-emphasis border border-info-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle'}">${row.Dropdown_Type}</span>`;
                const cCode = row.Center_Code ? `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Center_Code}</span>` : '<span class="text-muted small">-</span>';
                const parent = row.Parent_Link ? `<span class="badge bg-info text-white">${row.Parent_Link}</span>` : '<span class="text-muted small">-</span>';
                const cInCharge = row.Center_In_Charge ? `<span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i>${row.Center_In_Charge}</span>` : '<span class="text-muted small">-</span>';
                
                if(tCen) tCen.innerHTML += `<tr><td>${cType}</td><td>${cCode}</td><td class="fw-bold text-dark">${row.Option_Value || ''}</td><td>${parent}</td><td>${whName}</td><td>${cInCharge}</td>${getActionBtn('center')}</tr>`;
            } else if (['Booking', 'Route', 'Mini Route'].includes(row.Dropdown_Type)) {
                hasDs = true;
                const inCharge = row.Route_In_Charge ? `<span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i>${row.Route_In_Charge}</span>` : '<span class="text-muted small">-</span>';
                if(tDs) tDs.innerHTML += `<tr><td class="text-muted fw-bold font-monospace">${dsCounter++}</td><td><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">${row.Dropdown_Type}</span></td><td class="fw-bold text-dark">${row.Option_Value || ''}</td><td>${whName}</td><td>${inCharge}</td>${getActionBtn('ds')}</tr>`;
            } else {
                hasGen = true;
                if(tGen) tGen.innerHTML += `<tr>
                    <td class="fw-bold text-dark">${row.Dropdown_Type || ''}</td>
                    <td class="text-success fw-bold">${row.Option_Value || ''}</td>
                    <td><span class="badge bg-light text-dark border">${row.Parent_Link || 'N/A'}</span></td>
                    ${getActionBtn('generic')}
                </tr>`;
            }
        });

        if(!hasGen && tGen) tGen.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted font-monospace">No options configured yet.</td></tr>';
        if(!hasCen && tCen) tCen.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted font-monospace">No centers configured yet.</td></tr>';
        if(!hasDs && tDs) tDs.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted font-monospace">No DS areas configured yet.</td></tr>';
    })
    .catch(err => {
        console.error("Error loading dropdowns:", err);
        if(tCen) tCen.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger font-monospace">System Error. Check Console.</td></tr>`;
    });
};

window.loadAuditLogs = function() {
    fetch('api/endpoints.php?table=audit_logs').then(r=>r.json()).then(data => {
        const t = document.querySelector('#auditLogsTable tbody');
        if(t) {
            t.innerHTML = '';
            if(!data || data.length === 0) { t.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">No logs found.</td></tr>'; return; }
            data.forEach(row => {
                t.innerHTML += `<tr>
                    <td class="font-monospace text-muted">${row.Log_Date}</td>
                    <td class="fw-bold text-primary"><i class="bi bi-person-circle me-1"></i>${row.Username}</td>
                    <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Action}</span></td>
                    <td class="text-dark">${row.Details}</td>
                </tr>`;
            });
        }
    });
};

window.backupDatabase = function() {
    if(confirm("Generate a complete SQL backup of your database? This may take a moment.")) {
        window.location.href = 'api/endpoints.php?table=backup_database';
    }
};

window.loadWarehousesSettings = function() {
    fetch('api/endpoints.php?table=warehouses')
    .then(r => r.json())
    .then(data => {
        window.globalWarehousesList = Array.isArray(data) ? data : []; 
        const t = document.querySelector('#warehousesTable tbody');
        if(t) {
            t.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) {
                t.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted font-monospace">No warehouses configured yet.</td></tr>';
                return;
            }
            data.forEach(row => {
                const editBtn = `<i class="bi bi-pencil-square text-primary" style="cursor:pointer;" onclick='editWarehouse(${JSON.stringify(row).replace(/'/g, "\\'")})' title="Edit"></i>`;
                const delBtn = `<i class="bi bi-trash text-danger" style="cursor:pointer;" title="Delete" onclick="deleteRecord('warehouses', ${row.Warehouse_ID})"></i>`;
                let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${editBtn} ${delBtn}</div>`;
                if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) actionsHTML = `<span class="badge bg-light text-muted border border-secondary-subtle">LOCKED</span>`;
                
                t.innerHTML += `<tr>
                    <td class="fw-bold text-muted">WH-${row.Warehouse_ID}</td>
                    <td class="fw-bold text-dark">${row.Warehouse_Name}</td>
                    <td>${row.Location || '-'}</td>
                    <td class="text-center">${actionsHTML}</td>
                </tr>`;
            });
        }
    });
};

window.editWarehouse = function(w) {
    document.getElementById('w_id').value = w.Warehouse_ID;
    document.getElementById('w_name').value = w.Warehouse_Name;
    document.getElementById('w_location').value = w.Location || '';
    new bootstrap.Modal(document.getElementById('warehouseModal')).show();
};

window.loadCompanyProfile = function() {
    fetch('api/endpoints.php?table=company_profile').then(r => r.json()).then(data => {
        if (data && !data.error) {
            const n = document.getElementById('cp_display_name'); if(!n) return;
            
            n.textContent = data.Company_Name || "NO COMPANY CONFIGURED";
            document.getElementById('cp_display_tin').textContent = "TIN: " + (data.TIN || "-");
            document.getElementById('cp_display_address').textContent = data.Address || "-";
            document.getElementById('cp_display_location').textContent = [data.Barangay, data.City, data.Province].filter(Boolean).join(", ") || "-";
            document.getElementById('cp_display_contact').textContent = data.Contact_No || "-";
            
            const lockDisp = document.getElementById('cp_display_lock_date');
            if (data.Lock_Date && data.Lock_Date !== '0000-00-00') {
                lockDisp.textContent = window.formatDate(data.Lock_Date);
                document.getElementById('fs_lock_date').value = data.Lock_Date;
            } else {
                lockDisp.textContent = "Not Locked";
                document.getElementById('fs_lock_date').value = '';
            }

            if (data.Logo_Path) { 
                document.getElementById('cp_display_icon').style.display = 'none'; 
                const img = document.getElementById('cp_display_img'); 
                img.src = data.Logo_Path + "?t=" + new Date().getTime(); 
                img.style.display = 'block'; 
                document.getElementById('cp_existing_logo').value = data.Logo_Path; 
            }
            
            document.getElementById('cp_name').value = data.Company_Name || ""; 
            document.getElementById('cp_tin').value = data.TIN || ""; 
            document.getElementById('cp_contact_no').value = data.Contact_No || ""; 
            document.getElementById('cp_address').value = data.Address || ""; 
        }
    });
};

window.loadUsers = function() {
    fetch('api/endpoints.php?table=users')
    .then(r => r.json())
    .then(data => {
        const t = document.querySelector('#usersTable tbody');
        if(t) {
            t.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) { 
                t.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted font-monospace">No users configured yet.</td></tr>'; 
                return; 
            }
            data.forEach(row => {
                let badgeColor = row.Role === 'Admin' ? 'bg-danger-subtle text-danger border border-danger-subtle' : 
                                 (row.Role === 'Field Agent' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 
                                 (row.Role === 'Cashier' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle'));
                
                let perms = [];
                try {
                    const p = JSON.parse(row.Permissions_JSON || '{}');
                    if (p.is_readonly) perms.push('<span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1"><i class="bi bi-eye me-1"></i>Read-Only</span>');
                    if (p.modules && p.modules.length > 0) perms.push(`<span class="text-dark fw-bold">${p.modules.join(', ')}</span>`);
                } catch(e) {}
                
                let entityBadge = '<span class="text-muted small">-</span>';
                if (row.Role === 'Field Agent') {
                    const aType = row.Agent_Type || 'Unassigned';
                    const aLink = row.Linked_Entity || 'Not Linked';
                    entityBadge = `<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">${aType}: ${aLink}</span>`;
                }
                
                const editBtn = `<i class="bi bi-pencil-square text-primary" style="cursor:pointer;" onclick='editUser(${JSON.stringify(row).replace(/'/g, "\\'")})'></i>`;
                const delBtn = row.Username === 'admin' ? '' : `<i class="bi bi-trash text-danger" style="cursor:pointer;" onclick="deleteRecord('users', ${row.User_ID})"></i>`;
                
                t.innerHTML += `<tr>
                    <td class="fw-bold">${row.Username}</td>
                    <td class="text-center"><span class="badge ${badgeColor}">${row.Role}</span></td>
                    <td class="text-center">${entityBadge}</td>
                    <td style="font-size: 0.85rem;">${perms.join(' ') || '<span class="text-muted fst-italic">No specific access</span>'}</td>
                    <td class="text-center d-flex gap-3 justify-content-center">${editBtn} ${delBtn}</td>
                </tr>`;
            });
        }
    })
    .catch(err => {
        const t = document.querySelector('#usersTable tbody');
        if(t) t.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger font-monospace">Error loading users. Check console.</td></tr>`;
    });
};

window.editUser = function(user) {
    document.getElementById('userForm').reset();
    document.getElementById('u_id').value = user.User_ID;
    document.getElementById('u_username').value = user.Username;
    document.getElementById('u_role').value = user.Role;
    
    const passFld = document.getElementById('u_password');
    if(passFld) {
        passFld.placeholder = "Leave blank to keep existing password";
        passFld.required = false;
    }
    
    const roleEvt = new Event('change');
    document.getElementById('u_role').dispatchEvent(roleEvt);
    
    if (user.Role === 'Field Agent') {
        document.getElementById('u_agent_type').value = user.Agent_Type || '';
        document.getElementById('u_linked_entity').setAttribute('data-edit-val', user.Linked_Entity || '');
        document.getElementById('u_agent_type').dispatchEvent(new Event('change'));
    }

    try {
        const p = JSON.parse(user.Permissions_JSON || '{}');
        if (p.modules) p.modules.forEach(mod => { const cb = document.getElementById('perm_' + mod.toLowerCase()); if(cb) cb.checked = true; });
        if (p.accounting_tabs) p.accounting_tabs.forEach(tab => { const cb = document.getElementById('perm_' + tab.replace('-', '_')); if(cb) cb.checked = true; });
        if (p.is_readonly) document.getElementById('perm_readonly').checked = true;
    } catch(e) {}

    new bootstrap.Modal(document.getElementById('userModal')).show();
};