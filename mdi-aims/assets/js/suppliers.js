// ==========================================
// MDI AIMS - SUPPLIERS SUB-MODULE
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    
    // STRICT ANTI-DOUBLE-SUBMISSION LOCKS
    document.getElementById('supplierForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const provSelect = document.getElementById('s_province');
        const citySelect = document.getElementById('s_city');
        const brgySelect = document.getElementById('s_brgy');
        
        window.postData('suppliers', {
            supplier_id: document.getElementById('s_supplier_id').value, 
            name: document.getElementById('s_name').value.toUpperCase(), 
            province: provSelect.selectedIndex >= 0 ? provSelect.options[provSelect.selectedIndex].text.toUpperCase() : "", 
            city: citySelect.selectedIndex >= 0 ? citySelect.options[citySelect.selectedIndex].text.toUpperCase() : "", 
            barangay: brgySelect.selectedIndex >= 0 ? brgySelect.options[brgySelect.selectedIndex].text.toUpperCase() : "", 
            address: document.getElementById('s_address').value.toUpperCase(), 
            contact_name: document.getElementById('s_contact_name').value.toUpperCase(), 
            contact_no: document.getElementById('s_contact_no').value.toUpperCase()
        }, this, 'supplierModal', () => {
            if (btn) btn.disabled = false;
            window.loadSuppliers();
        });
    });

    document.getElementById('supplierModal')?.addEventListener('show.bs.modal', function() {
        if(!document.getElementById('s_supplier_id').value) {
            document.getElementById('supplierForm').reset();
        }
        const btn = document.querySelector('#supplierForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save Supplier'; }
        
        if(typeof window.bindPhilLocations === 'function') window.bindPhilLocations('s_province', 's_city', 's_brgy');
    });

    // EXPLICIT RESET WHEN MODAL CLOSES
    document.getElementById('supplierModal')?.addEventListener('hidden.bs.modal', function() {
        document.getElementById('supplierForm')?.reset();
        document.getElementById('s_supplier_id').value = '';
    });
});

window.loadSuppliers = function() { 
    fetch('api/endpoints.php?table=suppliers')
    .then(r=>r.json())
    .then(data => { 
        const t=document.querySelector('#suppliersTable tbody'); 
        if(t){ 
            t.innerHTML=''; 
            if(data.error || data.length === 0) {
                t.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted font-monospace">No suppliers found.</td></tr>';
                return;
            }
            data.forEach(row=>{ 
                const editBtn = `<i class="bi bi-pencil-square text-primary" style="cursor:pointer;" onclick='editSupplier(${JSON.stringify(row).replace(/'/g, "\\'")})' title="Edit"></i>`;
                const delBtn = `<i class="bi bi-trash text-danger" style="cursor:pointer;" onclick="deleteRecord('suppliers', ${row.Supplier_ID})" title="Delete"></i>`;
                let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${editBtn} ${delBtn}</div>`;
                if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) actionsHTML = `<span class="badge bg-light text-muted border border-secondary-subtle">LOCKED</span>`;
                
                t.innerHTML+=`<tr>
                    <td class="fw-bold font-monospace text-muted">${row.Supplier_No}</td>
                    <td class="fw-bold text-dark">${row.Supplier_Name}</td>
                    <td><small>${row.City}, ${row.Barangay}</small></td>
                    <td><small class="text-muted">${row.Address}</small></td>
                    <td class="fw-bold text-dark">${row.Contact_Name}</td>
                    <td>${window.formatDate(row.CreatedDate)}</td>
                    <td class="text-center">${actionsHTML}</td>
                </tr>`; 
            }); 
        }
    }); 
};

window.editSupplier = function(s) {
    document.getElementById('supplierForm').reset();
    document.getElementById('s_supplier_id').value = s.Supplier_ID;
    document.getElementById('s_name').value = s.Supplier_Name;
    document.getElementById('s_address').value = s.Address;
    document.getElementById('s_contact_name').value = s.Contact_Name;
    document.getElementById('s_contact_no').value = s.Contact_No;

    if(typeof window.bindPhilLocations === 'function') window.bindPhilLocations('s_province', 's_city', 's_brgy');
    
    setTimeout(() => {
        const pSel = document.getElementById('s_province');
        Array.from(pSel.options).forEach(opt => { if(opt.text === s.Province) pSel.value = opt.value; });
        pSel.dispatchEvent(new Event('change'));
        
        setTimeout(() => {
            const cSel = document.getElementById('s_city');
            Array.from(cSel.options).forEach(opt => { if(opt.text === s.City) cSel.value = opt.value; });
            cSel.dispatchEvent(new Event('change'));
            
            setTimeout(() => {
                const bSel = document.getElementById('s_brgy');
                Array.from(bSel.options).forEach(opt => { if(opt.text === s.Barangay) bSel.value = opt.value; });
            }, 500);
        }, 500);
    }, 500);

    new bootstrap.Modal(document.getElementById('supplierModal')).show();
};