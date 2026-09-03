// ==========================================
// MDI AIMS - DISCOUNTS & REBATES MODULE
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    // YL Discount Rates Form
    document.getElementById('ylDiscountForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const data = {
            orig: document.getElementById('inp_yl_orig').value,
            light: document.getElementById('inp_yl_light').value,
            trade_orig: document.getElementById('inp_yl_trade_orig').value,
            trade_light: document.getElementById('inp_yl_trade_light').value
        };
        window.postData('yl_discount_rates', data, this, 'ylDiscountModal', window.loadYlRates);
    });

    // Rebate Matrix Form
    document.getElementById('rebateMatrixForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        let tiers = [];
        const targetType = document.getElementById('rebate_matrix_type').value;
        const rows = document.querySelectorAll('#rebateModalBody tr');
        
        rows.forEach(tr => {
            const min = parseInt(tr.querySelector('.min-qty').value);
            const max = parseInt(tr.querySelector('.max-qty').value) || 999999;
            const amt = parseFloat(tr.querySelector('.r-amt').value);
            if (!isNaN(min) && !isNaN(amt)) {
                tiers.push({ min: min, max: max, amount: amt });
            }
        });
        
        window.postData('yl_rebate_matrix', { type: targetType, tiers: tiers }, this, 'rebateMatrixModal', window.loadRebateMatrix);
    });
});

window.loadYlRates = function() {
    fetch('api/endpoints.php?table=yl_discount_rates').then(r=>r.json()).then(data => {
        if(document.getElementById('disp_yl_orig')) {
            document.getElementById('disp_yl_orig').innerText = data.YL_Disc_Orig || '0.450';
            document.getElementById('disp_yl_light').innerText = data.YL_Disc_Light || '0.550';
            document.getElementById('inp_yl_orig').value = data.YL_Disc_Orig || '0.450';
            document.getElementById('inp_yl_light').value = data.YL_Disc_Light || '0.550';
            
            document.getElementById('disp_yl_trade_orig').innerText = data.YL_Trade_Orig || '0.500';
            document.getElementById('disp_yl_trade_light').innerText = data.YL_Trade_Light || '0.700';
            document.getElementById('inp_yl_trade_orig').value = data.YL_Trade_Orig || '0.500';
            document.getElementById('inp_yl_trade_light').value = data.YL_Trade_Light || '0.700';
        }
    });
};

window.addRebateRow = function(min = '', max = '', amt = '') {
    const tbody = document.getElementById('rebateModalBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="number" class="form-control text-center fw-bold min-qty" value="${min}" required placeholder="e.g. 0"></td>
        <td><input type="number" class="form-control text-center fw-bold max-qty" value="${max}" required placeholder="e.g. 99 (or 999999)"></td>
        <td><input type="number" step="0.001" class="form-control text-center text-dark fw-bold r-amt" value="${amt}" required placeholder="e.g. 0.525"></td>
        <td><button type="button" class="btn btn-sm btn-light border" onclick="this.closest('tr').remove()"><i class="bi bi-trash text-danger"></i></button></td>
    `;
    tbody.appendChild(tr);
};

window.openRebateModal = function(productType) {
    document.getElementById('rebate_matrix_type').value = productType;
    
    const titleColor = productType === 'Original' ? 'text-danger' : 'text-primary';
    const modalHeader = document.getElementById('rebateModalTitle');
    modalHeader.innerHTML = `<i class="bi bi-graph-up-arrow me-1"></i> Edit Matrix (${productType})`;
    modalHeader.className = `ms-auto small text-uppercase fw-bolder ${titleColor}`;
    
    const tbody = document.getElementById('rebateModalBody');
    tbody.innerHTML = '';
    
    fetch('api/endpoints.php?table=yl_rebate_matrix').then(r=>r.json()).then(data => {
        const filteredData = data.filter(d => d.Product_Type === productType);
        if(filteredData.length === 0) { 
            window.addRebateRow(); 
        } else {
            filteredData.forEach(d => window.addRebateRow(d.Min_Qty, d.Max_Qty, d.Rebate_Amount));
        }
        new bootstrap.Modal(document.getElementById('rebateMatrixModal')).show();
    });
};

window.loadRebateMatrix = function() {
    fetch('api/endpoints.php?table=yl_rebate_matrix').then(r=>r.json()).then(data => {
        ['Original', 'Light'].forEach(type => {
            const tbody = document.querySelector('#rebateMatrixTable_' + type + ' tbody');
            if(!tbody) return;
            tbody.innerHTML = '';
            
            const filteredData = data.filter(d => d.Product_Type === type);
            
            if(filteredData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-muted font-monospace py-3">No rebate tiers configured.</td></tr>';
                return;
            }
            filteredData.forEach(d => {
                const maxStr = d.Max_Qty >= 999999 ? 'and above' : d.Max_Qty;
                const amtStr = d.Rebate_Amount == 0 ? '-' : '₱ ' + parseFloat(d.Rebate_Amount).toFixed(3);
                const colorClass = type === 'Original' ? 'text-danger' : 'text-primary';
                
                tbody.innerHTML += `<tr>
                    <td class="fw-bold font-monospace text-secondary">${d.Min_Qty}</td>
                    <td class="fw-bold font-monospace text-secondary">${maxStr}</td>
                    <td class="${colorClass} fw-bold">${amtStr}</td>
                </tr>`;
            });
        });
    });
};