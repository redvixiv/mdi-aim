// ==========================================
// MDI AIMS - BULK CSV IMPORTER
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    
    document.getElementById('bulkImportForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const target = document.getElementById('import_target').value;
        const fileInput = document.getElementById('csv_file');
        
        if (!target) { alert("Please select an import target table."); return; }
        if (!fileInput.files || fileInput.files.length === 0) { alert("Please select a CSV file."); return; }

        const formData = new FormData();
        formData.append('target', target);
        formData.append('csv_file', fileInput.files[0]);

        const btn = document.getElementById('btnRunImport');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Importing Data...';
        btn.disabled = true;

        fetch('api/modules/importer.php?action=process_import', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                document.getElementById('importPlaceholder').style.display = 'none';
                document.getElementById('importResultSummary').style.display = 'block';

                document.getElementById('res_inserted').innerText = res.inserted || 0;
                document.getElementById('res_skipped').innerText = res.skipped || 0;
                document.getElementById('res_errors').innerText = res.errors || 0;

                const tbody = document.querySelector('#importLogTable tbody');
                tbody.innerHTML = '';

                if (res.logs && res.logs.length > 0) {
                    res.logs.forEach(log => {
                        const badge = log.type === 'SUCCESS' ? 'bg-success-subtle text-success border border-success-subtle' : 
                                     (log.type === 'SKIP' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle');
                        
                        tbody.innerHTML += `<tr>
                            <td><span class="badge ${badge}">${log.type}</span></td>
                            <td class="fw-bold text-dark">${log.item}</td>
                            <td class="small text-muted">${log.message}</td>
                        </tr>`;
                    });
                }
            } else {
                alert("Import Failed: " + res.message);
            }
        })
        .catch(err => {
            alert("Database/Network error during import. Check console.");
            console.error(err);
        })
        .finally(() => {
            btn.innerHTML = '<i class="bi bi-box-arrow-in-down me-2"></i>Run Bulk Import';
            btn.disabled = false;
        });
    });
});

window.downloadCSVTemplate = function() {
    const target = document.getElementById('import_target')?.value;
    if (!target) { alert("Please select a Target Table first to download its matching CSV template."); return; }

    const templates = {
        products: "Product_Name,Category,Description,Unit_Cost,Wholesale,Retail,Barcode\nYAKULT ORIGINAL (BC),CULTURED MILK,5-PACK BOTTLE,6.00,9.00,0.00,4801234567890",
        outlets: "Outlet_Name,Branch,Outlet_TIN,Province,City,Barangay,Address,Route,Contact_Person,Contact_No,Terms,Business_Style,Category\nROSE PHARMACY,A.S. FORTUNA,123-456-789,CEBU,MANDAUE,MANTUYONG,A.S. FORTUNA ST,ROUTE 1,JUAN DELA CRUZ,09171234567,15,PHARMACY,DRUG STORE",
        dealers: "First_Name,Middle_Name,Last_Name,Birth_Date,Hiring_Date,Center_Code,Center,Area,Type,Status\nMARIA,CLARA,SANTOS,1990-05-15,2026-01-10,CB1,CEBU A,CEBU A 01,Yakult Lady,Active",
        suppliers: "Supplier_Name,Province,City,Barangay,Address,Contact_Name,Contact_No\nYAKULT PHILIPPINES INC,METRO MANILA,MANILA,ERMITA,UNITED NATIONS AVE,PEDRO PENDUKO,09181234567",
        employees: "First_Name,Last_Name,Department,Position,Basic_Rate,Rate_Type,SSS_No,PhilHealth_No,PagIBIG_No,TIN,Hire_Date\nJUAN,DELA CRUZ,WAREHOUSE,CHECKER,570.00,Daily,34-1234567-8,12-345678901-2,1234-5678-9012,123-456-789,2026-01-01"
    };

    const csvContent = templates[target];
    if (!csvContent) return;

    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `Sample_Template_${target.toUpperCase()}.csv`;
    link.click();
};