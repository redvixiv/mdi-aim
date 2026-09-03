// ==========================================
// MDI AIMS - PRODUCTS SUB-MODULE
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    
    // STRICT ANTI-DOUBLE-SUBMISSION LOCKS
    document.getElementById('productForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        window.postData('products', {
            product_id: document.getElementById('p_product_id').value, 
            name: document.getElementById('p_name').value.toUpperCase(), 
            category: document.getElementById('p_category').value.toUpperCase(), 
            description: document.getElementById('p_desc').value.toUpperCase()
        }, this, 'productModal', () => { 
            if(btn) btn.disabled = false;
            window.loadProducts(); 
            window.loadProductPricing(); 
        });
    });
    
    document.getElementById('pricingForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        window.postData('product_pricing', {
            pricing_id: document.getElementById('pr_pricing_id') ? document.getElementById('pr_pricing_id').value : '',
            product_id: document.getElementById('pr_product_id').value, 
            unit_cost: document.getElementById('pr_cost').value, 
            wholesale: document.getElementById('pr_wholesale').value, 
            retail: document.getElementById('pr_retail').value, 
            odl: document.getElementById('pr_odl').value, 
            effective_from: document.getElementById('pr_from').value, 
            effective_to: document.getElementById('pr_to').value
        }, this, 'pricingModal', () => {
            if(btn) btn.disabled = false;
            window.loadProductPricing();
        });
    });

    // MODAL LISTENERS
    document.getElementById('productModal')?.addEventListener('show.bs.modal', function() {
        if(!document.getElementById('p_product_id').value) { document.getElementById('productForm').reset(); }
        const btn = document.querySelector('#productForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save Product'; }
    });

    document.getElementById('pricingModal')?.addEventListener('show.bs.modal', function() {
        if (!document.getElementById('pr_pricing_id') || !document.getElementById('pr_pricing_id').value) { 
            document.getElementById('pricingForm').reset(); 
            if(document.getElementById('pr_pricing_id')) document.getElementById('pr_pricing_id').value = '';
        }
        
        const btn = document.querySelector('#pricingForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save Pricing'; }

        const prodSel = document.getElementById('pr_product_id');
        if (prodSel.options.length <= 1 || !document.getElementById('pr_pricing_id').value) {
            prodSel.innerHTML = '<option value="">Loading Products...</option>';
            fetch('api/endpoints.php?table=products').then(r=>r.json()).then(data => {
                prodSel.innerHTML = '<option value="">Select a Product...</option>';
                if(Array.isArray(data)) {
                    data.forEach(p => { prodSel.innerHTML += `<option value="${p.Product_ID}">[${p.Product_No}] ${p.Product_Name}</option>`; });
                }
                const currentEditProduct = prodSel.getAttribute('data-edit-value');
                if (currentEditProduct) { prodSel.value = currentEditProduct; prodSel.removeAttribute('data-edit-value'); }
            });
        }
    });

    // EXPLICIT RESET WHEN MODALS CLOSE
    document.getElementById('productModal')?.addEventListener('hidden.bs.modal', function() {
        document.getElementById('productForm')?.reset();
        document.getElementById('p_product_id').value = '';
    });
    document.getElementById('pricingModal')?.addEventListener('hidden.bs.modal', function() {
        document.getElementById('pricingForm')?.reset();
        document.getElementById('pr_pricing_id').value = '';
        document.getElementById('pr_product_id').removeAttribute('data-edit-value');
    });
});

window.loadProducts = function() { 
    fetch('api/endpoints.php?table=products')
    .then(r=>r.json())
    .then(data => { 
        const t=document.querySelector('#productsTable tbody'); 
        if(t){ 
            t.innerHTML=''; 
            if(data.error || data.length === 0) {
                t.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted font-monospace">No products found.</td></tr>';
                return;
            }
            data.forEach(row=>{ 
                const editBtn = `<i class="bi bi-pencil-square text-primary" style="cursor:pointer;" onclick='editProduct(${JSON.stringify(row).replace(/'/g, "\\'")})' title="Edit"></i>`;
                const delBtn = `<i class="bi bi-trash text-danger" style="cursor:pointer;" onclick="deleteRecord('products', ${row.Product_ID})" title="Delete"></i>`;
                let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${editBtn} ${delBtn}</div>`;
                if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) actionsHTML = `<span class="badge bg-light text-muted border border-secondary-subtle">LOCKED</span>`;
                
                t.innerHTML+=`<tr>
                    <td class="fw-bold text-muted font-monospace">${row.Product_No}</td>
                    <td class="fw-bold text-dark">${row.Product_Name}</td>
                    <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${row.Category}</span></td>
                    <td><small class="text-muted">${row.Description}</small></td>
                    <td>${window.formatDate(row.CreatedDate)}</td>
                    <td class="text-center">${actionsHTML}</td>
                </tr>`; 
            }); 
        }
    }); 
};

window.editProduct = function(p) {
    document.getElementById('productForm').reset();
    document.getElementById('p_product_id').value = p.Product_ID;
    document.getElementById('p_name').value = p.Product_Name;
    document.getElementById('p_category').value = p.Category;
    document.getElementById('p_desc').value = p.Description;
    new bootstrap.Modal(document.getElementById('productModal')).show();
};

window.loadProductPricing = function() { 
    fetch('api/endpoints.php?table=product_pricing')
    .then(r=>r.json())
    .then(data => { 
        const t=document.querySelector('#pricingTable tbody'); 
        if(t){ 
            t.innerHTML=''; 
            if(data.error || data.length === 0) {
                t.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted font-monospace">No pricing records found.</td></tr>';
                return;
            }
            data.forEach(row=>{ 
                const editBtn = `<i class="bi bi-pencil-square text-primary" style="cursor:pointer;" onclick='editPricing(${JSON.stringify(row).replace(/'/g, "\\'")})' title="Edit"></i>`;
                const deleteBtn = `<i class="bi bi-trash text-danger" style="cursor:pointer;" onclick="deleteRecord('product_pricing', ${row.Pricing_ID})" title="Delete"></i>`;
                let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${editBtn} ${deleteBtn}</div>`;
                
                if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) { 
                    actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3"><span class="badge bg-light text-muted border">LOCKED</span></div>`; 
                }
                
                t.innerHTML+=`<tr>
                    <td class="fw-bold text-dark">${row.Product_Name} <small class="text-muted d-block font-monospace">${row.Product_No}</small></td>
                    <td class="text-danger fw-bolder text-end">₱ ${window.formatCurrency(row.Unit_Cost||0)}</td>
                    <td class="text-success fw-bold text-end">₱ ${window.formatCurrency(row.Wholesale)}</td>
                    <td class="text-primary fw-bold text-end">₱ ${window.formatCurrency(row.Retail)}</td>
                    <td class="text-info fw-bold text-end">₱ ${window.formatCurrency(row.ODL)}</td>
                    <td class="font-monospace text-muted">${window.formatDate(row.Effective_From)}</td>
                    <td class="font-monospace text-muted">${window.formatDate(row.Effective_To)}</td>
                    <td class="text-center">${actionsHTML}</td>
                </tr>`; 
            }); 
        }
    }); 
};

window.editPricing = function(pricing) {
    if(!document.getElementById('pr_pricing_id')) { alert("Please add the hidden input 'pr_pricing_id' to your modal form in index.php first."); return; }
    document.getElementById('pr_pricing_id').value = pricing.Pricing_ID;
    const prodSel = document.getElementById('pr_product_id');
    prodSel.setAttribute('data-edit-value', pricing.Product_ID);
    document.getElementById('pr_cost').value = pricing.Unit_Cost;
    document.getElementById('pr_wholesale').value = pricing.Wholesale;
    document.getElementById('pr_retail').value = pricing.Retail;
    document.getElementById('pr_odl').value = pricing.ODL;
    document.getElementById('pr_from').value = pricing.Effective_From ? pricing.Effective_From.split(' ')[0] : '';
    document.getElementById('pr_to').value = pricing.Effective_To && pricing.Effective_To !== '0000-00-00' ? pricing.Effective_To.split(' ')[0] : '';
    new bootstrap.Modal(document.getElementById('pricingModal')).show();
};