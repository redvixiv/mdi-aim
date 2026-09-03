// ==========================================
// MDI AIMS - ACCOUNTS MASTER MODULE
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('module-Accounts') || document.querySelector('.accounts-container')) {
        if(typeof window.loadAllOutlets === 'function') window.loadAllOutlets();
        if(typeof window.loadDealers === 'function') window.loadDealers();
    }

    document.addEventListener('change', (e) => {
        const target = e.target;
        
        if (target.id === 'o_ds' || (target.tagName.toLowerCase() === 'select' && target.id.toLowerCase().includes('section'))) {
            const categoryDropdown = document.getElementById('o_cat');
            const routeDropdown = document.getElementById('o_route');
            
            const selectedSection = target.value.toUpperCase().trim();
            const dynamicLabel = selectedSection ? selectedSection : 'Route';
            document.getElementById('lbl_o_route').innerText = dynamicLabel;
            
            if(routeDropdown) routeDropdown.innerHTML = `<option value="">SELECT ${dynamicLabel}...</option>`;
            
            if (!selectedSection) {
                if(categoryDropdown) categoryDropdown.innerHTML = '<option value="">SELECT DS SECTION FIRST</option>';
                if(routeDropdown) routeDropdown.innerHTML = '<option value="">SELECT DS SECTION FIRST</option>';
                return;
            }
            
            if(categoryDropdown) categoryDropdown.innerHTML = '<option value="">LOADING...</option>';
            if(routeDropdown) routeDropdown.innerHTML = '<option value="">LOADING...</option>';
            
            fetch('api/endpoints.php?table=system_dropdowns').then(r => r.json()).then(data => {
                if(categoryDropdown) {
                    categoryDropdown.innerHTML = '<option value="">SELECT CATEGORY...</option>';
                    if (!data || data.length === 0 || data.error) {
                        categoryDropdown.innerHTML += '<option value="OTHERS">OTHERS</option>';
                    } else {
                        const matchingOptions = data.filter(opt => opt.Dropdown_Type === 'Category' && opt.Parent_Link && opt.Parent_Link.toUpperCase() === selectedSection);
                        matchingOptions.sort((a, b) => a.Option_Value.localeCompare(b.Option_Value, undefined, { numeric: true, sensitivity: 'base' }));
                        
                        if (matchingOptions.length > 0) {
                            matchingOptions.forEach(opt => { categoryDropdown.innerHTML += `<option value="${opt.Option_Value}">${opt.Option_Value}</option>`; });
                            if(categoryDropdown.getAttribute('data-edit-val')) {
                                categoryDropdown.value = categoryDropdown.getAttribute('data-edit-val');
                                categoryDropdown.removeAttribute('data-edit-val');
                            }
                        } else { categoryDropdown.innerHTML += '<option value="OTHERS">OTHERS (ADD IN SETTINGS)</option>'; }
                    }
                }

                if(routeDropdown) {
                    routeDropdown.innerHTML = `<option value="">SELECT ${dynamicLabel}...</option>`;
                    if (!data || data.length === 0 || data.error) {
                        routeDropdown.innerHTML += `<option value="">NO AREAS (ADD IN SETTINGS)</option>`;
                    } else {
                        const routeOptions = data.filter(opt => opt.Dropdown_Type && opt.Dropdown_Type.toUpperCase() === selectedSection);
                        routeOptions.sort((a, b) => a.Option_Value.localeCompare(b.Option_Value, undefined, { numeric: true, sensitivity: 'base' }));
                        
                        if (routeOptions.length > 0) {
                            routeOptions.forEach(opt => { routeDropdown.innerHTML += `<option value="${opt.Option_Value}">${opt.Option_Value}</option>`; });
                            if(routeDropdown.getAttribute('data-edit-val')) {
                                routeDropdown.value = routeDropdown.getAttribute('data-edit-val');
                                routeDropdown.removeAttribute('data-edit-val');
                            }
                        } else { routeDropdown.innerHTML += `<option value="">NO AREAS (ADD IN SETTINGS)</option>`; }
                    }
                }
            });
        }
        
        if (target.id === 'd_center_code') {
            const sel = target;
            const opt = sel.options[sel.selectedIndex];
            const centerNameInput = document.getElementById('d_center');
            const areaSel = document.getElementById('d_area');
            
            if (!sel.value) {
                centerNameInput.value = '';
                areaSel.innerHTML = '<option value="">Select Center Code First</option>';
                return;
            }
            
            const cName = opt.getAttribute('data-name');
            centerNameInput.value = cName;
            areaSel.innerHTML = '<option value="">Loading Areas...</option>';
            
            const areas = (window.globalSettingsDropdowns || []).filter(d => d.Dropdown_Type === 'Area' && d.Parent_Link === cName);
            areaSel.innerHTML = '<option value="">Select Area...</option>';
            
            if(areas.length === 0) {
                areaSel.innerHTML += '<option value="" disabled>No Areas Found in Settings</option>';
            } else {
                areas.forEach(a => {
                    areaSel.innerHTML += `<option value="${a.Option_Value}">${a.Option_Value}</option>`;
                });
            }
            
            const editArea = areaSel.getAttribute('data-edit-val');
            if(editArea) {
                areaSel.value = editArea;
                areaSel.removeAttribute('data-edit-val');
            }
        }
    });

    document.getElementById('dealerModal')?.addEventListener('show.bs.modal', function () {
        if(!document.getElementById('d_dealer_id').value) {
            document.getElementById('dealerForm')?.reset();
            document.getElementById('d_center_code')?.removeAttribute('data-edit-val');
            document.getElementById('d_area')?.removeAttribute('data-edit-val');
        }
        
        const btn = document.querySelector('#dealerForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save Dealer'; }
        
        const centerCodeSel = document.getElementById('d_center_code');
        if (centerCodeSel) {
            centerCodeSel.innerHTML = '<option value="">Loading Codes...</option>';
            fetch('api/endpoints.php?table=system_dropdowns').then(r => r.json()).then(data => {
                window.globalSettingsDropdowns = data; 
                centerCodeSel.innerHTML = '<option value="">Select Center Code...</option>';
                const centers = data.filter(opt => opt.Dropdown_Type === 'Center');
                if(centers.length === 0) {
                    centerCodeSel.innerHTML += '<option value="" disabled>(Add Centers in Settings)</option>';
                } else {
                    centers.forEach(opt => { 
                        const code = opt.Center_Code || 'NO-CODE';
                        centerCodeSel.innerHTML += `<option value="${code}" data-name="${opt.Option_Value}">${code} - ${opt.Option_Value}</option>`; 
                    });
                }
                
                const editVal = centerCodeSel.getAttribute('data-edit-val');
                if (editVal) { 
                    centerCodeSel.value = editVal; 
                    centerCodeSel.dispatchEvent(new Event('change'));
                }
            });
        }
    });

    // EXPLICIT RESET WHEN THE MODALS CLOSE TO PREVENT GHOSTING
    document.getElementById('dealerModal')?.addEventListener('hidden.bs.modal', function () {
        const frm = document.getElementById('dealerForm');
        if(frm) {
            frm.reset();
            const idFld = document.getElementById('d_dealer_id');
            if(idFld) idFld.value = '';
        }
    });

    document.getElementById('outletModal')?.addEventListener('hidden.bs.modal', function () {
        const frm = document.getElementById('outletForm');
        if(frm) {
            frm.reset();
            const idFld = document.getElementById('o_outlet_id');
            if(idFld) idFld.value = '';
        }
    });

    // ==========================================
    // STRICT ANTI-DOUBLE-SUBMISSION LOCKS
    // ==========================================
    document.getElementById('dealerForm')?.addEventListener('submit', function(e) {
        e.preventDefault(); e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = {
            dealer_id: document.getElementById('d_dealer_id')?.value,
            fname: document.getElementById('d_fname')?.value,
            mname: document.getElementById('d_mname')?.value,
            lname: document.getElementById('d_lname')?.value,
            bdate: document.getElementById('d_bdate')?.value,
            hdate: document.getElementById('d_hdate')?.value,
            center_code: document.getElementById('d_center_code')?.value, 
            center: document.getElementById('d_center')?.value, 
            area: document.getElementById('d_area')?.value,
            type: document.getElementById('d_type')?.value,
            status: document.getElementById('d_status')?.value,
            remarks: document.getElementById('d_remarks')?.value
        };

        window.postData('dealers', data, this, 'dealerModal', () => { 
            if(btn) btn.disabled = false;
            window.loadDealers(); 
        });
    });

    document.getElementById('outletForm')?.addEventListener('submit', function(e) {
        e.preventDefault(); e.stopImmediatePropagation();
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = {
            outlet_id: document.getElementById('o_outlet_id')?.value,
            customer_name: document.getElementById('o_customer_name')?.value,
            tin: document.getElementById('o_tin')?.value,
            bstyle: document.getElementById('o_bstyle')?.value,
            ds_section: document.getElementById('o_ds')?.value,
            category: document.getElementById('o_cat')?.value,
            branch: document.getElementById('o_branch')?.value,
            province: document.getElementById('o_province')?.options[document.getElementById('o_province').selectedIndex].text,
            city: document.getElementById('o_city')?.options[document.getElementById('o_city').selectedIndex].text,
            barangay: document.getElementById('o_brgy')?.options[document.getElementById('o_brgy').selectedIndex].text,
            address: document.getElementById('o_address')?.value,
            route: document.getElementById('o_route')?.value,
            contact_person: document.getElementById('o_contact_person')?.value,
            contact_no: document.getElementById('o_contact_no')?.value,
            terms: document.getElementById('o_terms')?.value
        };
        
        window.postData('outlets', data, this, 'outletModal', () => { 
            if(btn) btn.disabled = false;
            window.loadAllOutlets(); 
        });
    });
}); // <--- END OF DOMContentLoaded LISTENER

window.editDealer = function(d) {
    document.getElementById('d_dealer_id').value = d.Dealer_ID;
    document.getElementById('d_fname').value = d.First_Name;
    document.getElementById('d_mname').value = d.Middle_Name;
    document.getElementById('d_lname').value = d.Last_Name;
    document.getElementById('d_bdate').value = d.Birth_Date && d.Birth_Date !== '0000-00-00' ? d.Birth_Date : '';
    document.getElementById('d_hdate').value = d.Hiring_Date && d.Hiring_Date !== '0000-00-00' ? d.Hiring_Date : '';
    document.getElementById('d_status').value = d.Status;
    document.getElementById('d_remarks').value = d.Remarks;
    
    document.getElementById('d_center_code')?.setAttribute('data-edit-val', d.Center_Code || '');
    document.getElementById('d_area')?.setAttribute('data-edit-val', d.Area || '');
    new bootstrap.Modal(document.getElementById('dealerModal')).show();
};

window.openNewOutletModal = function() {
    document.getElementById('outletForm').reset();
    document.getElementById('o_outlet_id').value = '';
    
    const btn = document.querySelector('#outletForm button[type="submit"]');
    if(btn) { btn.disabled = false; btn.innerHTML = 'Save Customer'; }
    document.getElementById('lbl_o_route').innerText = 'Route Code';
    
    const catDrop = document.getElementById('o_cat');
    if(catDrop) catDrop.innerHTML = '<option value="">SELECT DS SECTION FIRST</option>';
    const routeDrop = document.getElementById('o_route');
    if(routeDrop) routeDrop.innerHTML = '<option value="">SELECT DS SECTION FIRST</option>';
    
    if(typeof window.bindPhilLocations === 'function') window.bindPhilLocations('o_province', 'o_city', 'o_brgy');
    window.loadCreditTerms();
};

window.editOutlet = function(o) {
    document.getElementById('outletForm').reset();
    
    const btn = document.querySelector('#outletForm button[type="submit"]');
    if(btn) { btn.disabled = false; btn.innerHTML = 'Save Customer'; }
    
    const dynamicLabel = o.DS_Section ? o.DS_Section : 'Route';
    document.getElementById('lbl_o_route').innerText = dynamicLabel;
    
    if(typeof window.bindPhilLocations === 'function') window.bindPhilLocations('o_province', 'o_city', 'o_brgy');
    window.loadCreditTerms(o.Terms);

    document.getElementById('o_outlet_id').value = o.Outlet_ID;
    document.getElementById('o_customer_no').value = o.Outlet_No;
    document.getElementById('o_customer_name').value = o.Outlet_Name;
    document.getElementById('o_branch').value = o.Branch;
    document.getElementById('o_tin').value = o.Outlet_TIN;
    document.getElementById('o_bstyle').value = o.Business_Style;
    document.getElementById('o_ds').value = o.DS_Section;
    
    document.getElementById('o_cat').setAttribute('data-edit-val', o.Category);
    document.getElementById('o_route').setAttribute('data-edit-val', o.Route);
    
    const evt = new Event('change');
    document.getElementById('o_ds').dispatchEvent(evt);

    document.getElementById('o_address').value = o.Address;
    document.getElementById('o_contact_person').value = o.Contact_Person;
    document.getElementById('o_contact_no').value = o.Contact_No;

    setTimeout(() => {
        const pSel = document.getElementById('o_province');
        Array.from(pSel.options).forEach(opt => { if(opt.text === o.Province) pSel.value = opt.value; });
        pSel.dispatchEvent(new Event('change'));
        
        setTimeout(() => {
            const cSel = document.getElementById('o_city');
            Array.from(cSel.options).forEach(opt => { if(opt.text === o.City) cSel.value = opt.value; });
            cSel.dispatchEvent(new Event('change'));
            
            setTimeout(() => {
                const bSel = document.getElementById('o_brgy');
                Array.from(bSel.options).forEach(opt => { if(opt.text === o.Barangay) bSel.value = opt.value; });
            }, 500);
        }, 500);
    }, 500);
    
    new bootstrap.Modal(document.getElementById('outletModal')).show();
};

window.loadAllOutlets = function() {
    fetch('api/endpoints.php?table=outlets').then(r => r.json()).then(data => {
        const t = document.querySelector('#allOutletsTable tbody');
        if(t) {
            t.innerHTML = '';
            if (data.length === 0 || data.error) { t.innerHTML = '<tr><td colspan="10" class="text-center text-muted font-monospace py-4">No customers found.</td></tr>'; return; }
            data.forEach(row => {
                const fullAddress = [row.Address, row.Barangay, row.City, row.Province].filter(Boolean).join(', ');
                
                const editBtn = `<i class="bi bi-pencil-square text-primary" style="cursor:pointer;" onclick='editOutlet(${JSON.stringify(row).replace(/'/g, "\\'")})' title="Edit"></i>`;
                const delBtn = `<i class="bi bi-trash text-danger" style="cursor:pointer;" onclick="deleteRecord('outlets', ${row.Outlet_ID})" title="Delete"></i>`;
                let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${editBtn} ${delBtn}</div>`;
                
                if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) actionsHTML = `<span class="badge bg-light text-muted border border-secondary-subtle">LOCKED</span>`;

                t.innerHTML += `<tr>
                    <td class="fw-bold">${row.Outlet_No || ''}</td>
                    <td class="fw-bold text-dark">${row.Outlet_Name || ''}</td>
                    <td><span class="font-monospace text-muted">${row.Outlet_TIN || '-'}</span></td>
                    <td>${row.Business_Style || ''}</td>
                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">${row.DS_Section || ''}</span></td>
                    <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Category || ''}</span></td>
                    <td>${row.Branch || ''}</td>
                    <td class="text-wrap" style="max-width: 250px;">${fullAddress || '-'}</td>
                    <td><span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">${row.Route || ''}</span></td>
                    <td class="text-center">${actionsHTML}</td>
                </tr>`;
            });
        }
    });
};

window.loadDealers = function() {
    fetch('api/endpoints.php?table=dealers').then(r => r.json()).then(data => {
        const t = document.querySelector('#dealersTable tbody');
        if(t) {
            t.innerHTML = '';
            if (data.length === 0 || data.error) { t.innerHTML = '<tr><td colspan="8" class="text-center text-muted font-monospace py-4">No dealers found.</td></tr>'; return; }
            data.forEach(row => {
                const fullName = `${row.First_Name || ''} ${row.Middle_Name || ''} ${row.Last_Name || ''}`.trim();
                const centerCodeBadge = row.Center_Code ? `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Center_Code}</span>` : `<span class="text-muted">-</span>`;
                const centerBadge = row.Center ? `<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">${row.Center}</span>` : `<span class="text-muted">-</span>`;
                const statusBadge = row.Status === 'Active' ? `<span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>` : `<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>`;
                
                const editBtn = `<i class="bi bi-pencil-square text-primary" style="cursor:pointer;" onclick='editDealer(${JSON.stringify(row).replace(/'/g, "\\'")})' title="Edit"></i>`;
                const delBtn = `<i class="bi bi-trash text-danger" style="cursor:pointer;" onclick="deleteRecord('dealers', ${row.Dealer_ID})" title="Delete"></i>`;
                let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${editBtn} ${delBtn}</div>`;
                
                if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) actionsHTML = `<span class="badge bg-light text-muted border border-secondary-subtle">LOCKED</span>`;

                t.innerHTML += `<tr><td class="fw-bold">${row.Dealer_No || ''}</td><td class="fw-bold text-dark">${fullName}</td><td>${centerCodeBadge}</td><td>${centerBadge}</td><td class="fw-bold text-primary">${row.Area || ''}</td><td>${row.Type || ''}</td><td class="text-center">${statusBadge}</td><td class="text-center">${actionsHTML}</td></tr>`;
            });
        }
    });
};

window.loadCreditTerms = function(selectedTerm = '') {
    const termSel = document.getElementById('o_terms');
    if(!termSel) return;
    termSel.innerHTML = '<option value="">Loading Terms...</option>';
    fetch('api/endpoints.php?table=system_dropdowns').then(r => r.json()).then(data => {
        termSel.innerHTML = '<option value="">Select Credit Term...</option>';
        if (!data || data.error) return;
        const terms = data.filter(opt => opt.Dropdown_Type === 'Credit Terms');
        if(terms.length === 0) {
            termSel.innerHTML += '<option value="">(Add terms in Settings)</option>';
        } else {
            terms.forEach(opt => { termSel.innerHTML += `<option value="${opt.Option_Value}">${opt.Option_Value}</option>`; });
            if (selectedTerm) termSel.value = selectedTerm;
        }
    });
};