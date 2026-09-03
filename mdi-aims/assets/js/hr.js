// ==========================================
// MDI AIMS - HUMAN RESOURCES & PAYROLL
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('module-HR')) {
        window.loadEmployees();
        window.loadDTR();
        window.loadPayroll();
    }

    // EXPLICIT MODAL RESETS TO PREVENT GHOSTING
    document.getElementById('employeeModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('employeeForm')?.reset();
        document.getElementById('emp_id').value = '';
    });

    document.getElementById('dtrModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('dtrForm')?.reset();
        document.getElementById('dtr_id').value = '';
        document.getElementById('zkteco_status_msg').style.display = 'none';
        document.getElementById('dtr_emp_id').removeAttribute('data-edit-val');
    });

    document.getElementById('generatePayrollModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('generatePayrollForm')?.reset();
    });

    // MODAL OPEN LISTENERS TO UNLOCK BUTTONS
    document.getElementById('employeeModal')?.addEventListener('show.bs.modal', function () {
        const btn = document.querySelector('#employeeForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save Employee'; }
    });

    document.getElementById('generatePayrollModal')?.addEventListener('show.bs.modal', function () {
        const btn = document.querySelector('#generatePayrollForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Process Payroll'; }
    });

    document.getElementById('dtrModal')?.addEventListener('show.bs.modal', function () {
        if(!document.getElementById('dtr_id').value) {
            document.getElementById('dtrForm').reset();
            document.getElementById('dtr_end').valueAsDate = new Date();
            document.getElementById('zkteco_status_msg').style.display = 'none';
        }
        
        const btn = document.querySelector('#dtrForm button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = 'Save DTR'; }
        
        const empSel = document.getElementById('dtr_emp_id');
        empSel.innerHTML = '<option value="">Loading Active Employees...</option>';
        
        fetch('api/endpoints.php?table=employees')
        .then(r => r.json())
        .then(data => {
            empSel.innerHTML = '<option value="">Select Employee...</option>';
            data.filter(e => e.Status === 'Active').forEach(e => {
                empSel.innerHTML += `<option value="${e.Emp_ID}" data-empno="${e.Emp_No}">[${e.Emp_No}] ${e.First_Name} ${e.Last_Name} - ${e.Position}</option>`;
            });
            if(empSel.getAttribute('data-edit-val')) {
                empSel.value = empSel.getAttribute('data-edit-val');
                empSel.removeAttribute('data-edit-val');
            }
        });
    });

    // ==========================================
    // STRICT ANTI-DOUBLE-SUBMISSION LOCKS
    // ==========================================
    document.getElementById('employeeForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = {
            emp_id: document.getElementById('emp_id').value,
            fname: document.getElementById('emp_fname').value.toUpperCase(),
            lname: document.getElementById('emp_lname').value.toUpperCase(),
            dept: document.getElementById('emp_dept').value.toUpperCase(),
            pos: document.getElementById('emp_pos').value.toUpperCase(),
            hire_date: document.getElementById('emp_hire_date').value,
            status: document.getElementById('emp_status').value,
            rate_type: document.getElementById('emp_rate_type').value,
            base_rate: parseFloat(document.getElementById('emp_base_rate').value) || 0,
            sss: document.getElementById('emp_sss').value,
            phic: document.getElementById('emp_phic').value,
            hdmf: document.getElementById('emp_hdmf').value,
            tin: document.getElementById('emp_tin').value
        };

        window.postData('employees', data, this, 'employeeModal', () => {
            if(btn) btn.disabled = false;
            window.loadEmployees();
        });
    });

    document.getElementById('dtrForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = {
            dtr_id: document.getElementById('dtr_id').value,
            emp_id: document.getElementById('dtr_emp_id').value,
            cutoff_start: document.getElementById('dtr_start').value,
            cutoff_end: document.getElementById('dtr_end').value,
            days_worked: parseFloat(document.getElementById('dtr_days').value) || 0,
            ot_hours: parseFloat(document.getElementById('dtr_ot').value) || 0,
            late_ut_hours: parseFloat(document.getElementById('dtr_late').value) || 0
        };

        window.postData('dtr', data, this, 'dtrModal', () => {
            if(btn) btn.disabled = false;
            window.loadDTR();
        });
    });

    // ZKTECO BIOMETRIC ATTLOG.DAT PARSER
    document.getElementById('zktecoFileInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const empSel = document.getElementById('dtr_emp_id');
        if(!empSel.value) { alert("Please select an Employee first before importing their biometric log."); return; }
        
        const selectedEmpNo = empSel.options[empSel.selectedIndex].getAttribute('data-empno');
        const cutoffStart = new Date(document.getElementById('dtr_start').value);
        const cutoffEnd = new Date(document.getElementById('dtr_end').value);
        cutoffEnd.setHours(23, 59, 59, 999); 

        if (isNaN(cutoffStart) || isNaN(cutoffEnd)) { alert("Please set the Cutoff Start and End dates first."); return; }

        const reader = new FileReader();
        reader.onload = function(event) {
            const rawText = event.target.result;
            const lines = rawText.split(/\r?\n/);
            
            const uniqueDays = new Set();
            let rawEmpCode = selectedEmpNo.replace('EMP-', ''); 

            lines.forEach(line => {
                const cols = line.split(/\t|,/); 
                if (cols.length >= 2) {
                    const zkTecoId = cols[0].trim();
                    const timestampStr = cols[1].trim();
                    
                    if (zkTecoId === selectedEmpNo || zkTecoId == parseInt(rawEmpCode)) {
                        const punchDate = new Date(timestampStr);
                        if (punchDate >= cutoffStart && punchDate <= cutoffEnd) {
                            const dateString = punchDate.toISOString().split('T')[0];
                            uniqueDays.add(dateString); 
                        }
                    }
                }
            });

            document.getElementById('dtr_days').value = uniqueDays.size;
            
            const statusMsg = document.getElementById('zkteco_status_msg');
            statusMsg.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Successfully extracted <b>${uniqueDays.size} days worked</b> from ZKTeco log for ${selectedEmpNo}.`;
            statusMsg.style.display = 'block';
            
            document.getElementById('zktecoFileInput').value = ''; 
        };
        
        reader.readAsText(file);
    });

    document.getElementById('generatePayrollForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const btn = this.querySelector('button[type="submit"]');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;

        const data = {
            cutoff_start: document.getElementById('payroll_start').value,
            cutoff_end: document.getElementById('payroll_end').value
        };
        
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        fetch('api/endpoints.php?table=payroll_records&action=generate', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        }).then(r => r.json()).then(res => {
            if(res.status === 'success') {
                alert(res.message);
                bootstrap.Modal.getInstance(document.getElementById('generatePayrollModal'))?.hide();
                window.loadPayroll();
                if(typeof window.loadAccountingGL === 'function') window.loadAccountingGL();
            } else {
                alert("Error: " + res.message);
            }
        }).finally(() => {
            btn.innerHTML = 'Process Payroll';
            btn.disabled = false;
        });
    });
});

window.loadEmployees = function() {
    fetch('api/endpoints.php?table=employees')
    .then(r => r.json())
    .then(data => {
        const tbody = document.querySelector('#employeesTable tbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        
        if (!data || data.length === 0 || data.error) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted font-monospace">No employees configured yet.</td></tr>';
            return;
        }

        data.forEach(row => {
            const badgeClass = row.Status === 'Active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle';
            const rateBadge = row.Rate_Type === 'Monthly' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-info-subtle text-info-emphasis border border-info-subtle';
            
            const editBtn = `<i class="bi bi-pencil-square text-primary" style="cursor:pointer;" title="Edit" onclick='editEmployee(${JSON.stringify(row).replace(/'/g, "\\'")})'></i>`;
            const delBtn = `<i class="bi bi-trash text-danger" style="cursor:pointer;" title="Delete" onclick="deleteRecord('employees', ${row.Emp_ID})"></i>`;
            
            let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${editBtn} ${delBtn}</div>`;
            if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) {
                actionsHTML = `<span class="badge bg-light text-muted border border-secondary-subtle">LOCKED</span>`;
            }

            tbody.innerHTML += `<tr>
                <td class="fw-bold text-muted">${row.Emp_No}</td>
                <td class="fw-bold text-dark">${row.First_Name} ${row.Last_Name}</td>
                <td>${row.Department}</td>
                <td>${row.Position}</td>
                <td class="text-end fw-bold text-primary">₱ ${window.formatCurrency(row.Basic_Rate)}</td>
                <td class="text-center"><span class="badge ${rateBadge}">${row.Rate_Type}</span></td>
                <td class="text-center"><span class="badge ${badgeClass}">${row.Status}</span></td>
                <td class="text-center">${actionsHTML}</td>
            </tr>`;
        });
    });
};

window.editEmployee = function(emp) {
    document.getElementById('employeeForm').reset();
    document.getElementById('emp_id').value = emp.Emp_ID;
    document.getElementById('emp_fname').value = emp.First_Name;
    document.getElementById('emp_lname').value = emp.Last_Name;
    document.getElementById('emp_dept').value = emp.Department;
    document.getElementById('emp_pos').value = emp.Position;
    document.getElementById('emp_hire_date').value = emp.Hire_Date && emp.Hire_Date !== '0000-00-00' ? emp.Hire_Date : '';
    document.getElementById('emp_status').value = emp.Status;
    document.getElementById('emp_rate_type').value = emp.Rate_Type;
    document.getElementById('emp_base_rate').value = emp.Basic_Rate;
    document.getElementById('emp_sss').value = emp.SSS_No || '';
    document.getElementById('emp_phic').value = emp.PhilHealth_No || '';
    document.getElementById('emp_hdmf').value = emp.PagIBIG_No || '';
    document.getElementById('emp_tin').value = emp.TIN || '';
    
    new bootstrap.Modal(document.getElementById('employeeModal')).show();
};

window.loadDTR = function() {
    fetch('api/endpoints.php?table=dtr')
    .then(r => r.json())
    .then(data => {
        const tbody = document.querySelector('#dtrTable tbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        
        if (!data || data.length === 0 || data.error) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted font-monospace">No DTR records found.</td></tr>';
            return;
        }

        data.forEach(row => {
            const editBtn = `<i class="bi bi-pencil-square text-info" style="cursor:pointer;" title="Edit" onclick='editDTR(${JSON.stringify(row).replace(/'/g, "\\'")})'></i>`;
            const delBtn = `<i class="bi bi-trash text-danger" style="cursor:pointer;" title="Delete" onclick="deleteDtrRecord(${row.DTR_ID})"></i>`;
            
            let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${editBtn} ${delBtn}</div>`;
            if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) {
                actionsHTML = `<span class="badge bg-light text-muted border border-secondary-subtle">LOCKED</span>`;
            }

            tbody.innerHTML += `<tr>
                <td class="fw-bold text-dark"><span class="text-muted fw-normal me-2 font-monospace">[${row.Emp_No}]</span>${row.First_Name} ${row.Last_Name}</td>
                <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${window.formatDate(row.Cutoff_Start)} to ${window.formatDate(row.Cutoff_End)}</span></td>
                <td class="text-center fw-bold text-success">${row.Days_Worked}</td>
                <td class="text-center fw-bold text-primary">${row.OT_Hours}</td>
                <td class="text-center fw-bold text-danger">${row.Late_Undertime_Hours}</td>
                <td class="text-center">${actionsHTML}</td>
            </tr>`;
        });
    });
};

window.editDTR = function(dtr) {
    document.getElementById('dtrForm').reset();
    document.getElementById('dtr_id').value = dtr.DTR_ID;
    document.getElementById('dtr_start').value = dtr.Cutoff_Start;
    document.getElementById('dtr_end').value = dtr.Cutoff_End;
    document.getElementById('dtr_days').value = dtr.Days_Worked;
    document.getElementById('dtr_ot').value = dtr.OT_Hours;
    document.getElementById('dtr_late').value = dtr.Late_Undertime_Hours;
    
    document.getElementById('zkteco_status_msg').style.display = 'none';
    document.getElementById('dtr_emp_id').setAttribute('data-edit-val', dtr.Emp_ID);
    
    new bootstrap.Modal(document.getElementById('dtrModal')).show();
};

window.deleteDtrRecord = function(id) {
    if (confirm("Are you sure you want to delete this DTR record?")) {
        fetch(`api/endpoints.php?table=dtr&id=${id}`, { method: 'DELETE' })
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') window.loadDTR();
            else alert("Error deleting DTR: " + res.message);
        });
    }
};

window.loadPayroll = function() {
    fetch('api/endpoints.php?table=payroll_records')
    .then(r => r.json())
    .then(data => {
        const tbody = document.querySelector('#payrollTable tbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        
        if (!data || data.length === 0 || data.error) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted font-monospace">No payroll records found.</td></tr>';
            return;
        }

        data.forEach(row => {
            const printBtn = `<i class="bi bi-printer text-primary" style="cursor:pointer;" title="Print Payslip" onclick='printPayslip(${JSON.stringify(row).replace(/'/g, "\\'")})'></i>`;
            const delBtn = `<i class="bi bi-trash text-danger" style="cursor:pointer;" title="Delete" onclick="deletePayrollRecord(${row.Payroll_ID})"></i>`;
            
            let actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${printBtn} ${delBtn}</div>`;
            if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) {
                actionsHTML = `<div class="action-icons d-flex justify-content-center gap-3">${printBtn}</div>`;
            }
            
            const totDeduct = parseFloat(row.SSS_Deduct) + parseFloat(row.PHIC_Deduct) + parseFloat(row.HDMF_Deduct) + parseFloat(row.Tax_Deduct);

            tbody.innerHTML += `<tr>
                <td class="text-muted">${window.formatDate(row.Date_Generated)}</td>
                <td class="fw-bold text-dark">${row.First_Name} ${row.Last_Name}</td>
                <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${window.formatDate(row.Cutoff_Start)} to ${window.formatDate(row.Cutoff_End)}</span></td>
                <td class="text-end fw-bold">₱ ${window.formatCurrency(row.Gross_Pay)}</td>
                <td class="text-end text-danger fw-bold">₱ ${window.formatCurrency(totDeduct)}</td>
                <td class="text-end fw-bold text-success fs-6">₱ ${window.formatCurrency(row.Net_Pay)}</td>
                <td class="text-center">${actionsHTML}</td>
            </tr>`;
        });
    });
};

window.deletePayrollRecord = function(id) {
    if (confirm("Are you sure you want to delete this payroll record? Note: This will not automatically reverse the General Ledger entry. You must do that manually in Accounting.")) {
        fetch(`api/endpoints.php?table=payroll_records&id=${id}`, { method: 'DELETE' })
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') window.loadPayroll();
            else alert("Error deleting Payroll: " + res.message);
        });
    }
};

window.printPayslip = function(pr) {
    fetch('api/endpoints.php?table=company_profile').then(r=>r.json()).then(comp => {
        let headerHtml = `<div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px;">
            <h2 style="margin: 0; text-transform: uppercase;">${comp ? comp.Company_Name : 'MDI AIMS'}</h2>
            <p style="margin: 0; color: #555;">Payslip for Period: ${window.formatDate(pr.Cutoff_Start)} to ${window.formatDate(pr.Cutoff_End)}</p>
        </div>`;

        let printContent = `
        <div style="padding: 20px; font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;">
            ${headerHtml}
            <table style="width: 100%; margin-bottom: 20px; font-size: 14px;">
                <tr><td style="width: 25%; font-weight: bold;">Employee:</td><td style="width: 75%; border-bottom: 1px solid #ddd; font-weight:bold; font-size: 16px;">${pr.First_Name} ${pr.Last_Name}</td></tr>
                <tr><td style="font-weight: bold; padding-top: 10px;">Department:</td><td style="border-bottom: 1px solid #ddd; padding-top: 10px;">${pr.Department}</td></tr>
                <tr><td style="font-weight: bold; padding-top: 10px;">Position:</td><td style="border-bottom: 1px solid #ddd; padding-top: 10px;">${pr.Position}</td></tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr><th style="border: 1px solid #333; padding: 8px; background: #f5f5f5; text-align: left;">Earnings</th><th style="border: 1px solid #333; padding: 8px; background: #f5f5f5; text-align: right;">Amount</th></tr>
                </thead>
                <tbody>
                    <tr><td style="border: 1px solid #333; padding: 8px; font-weight:bold;">Gross Basic Pay</td><td style="border: 1px solid #333; padding: 8px; text-align: right; font-weight:bold;">₱ ${window.formatCurrency(pr.Gross_Pay)}</td></tr>
                </tbody>
                <thead>
                    <tr><th style="border: 1px solid #333; padding: 8px; background: #f5f5f5; text-align: left;">Deductions</th><th style="border: 1px solid #333; padding: 8px; background: #f5f5f5; text-align: right;">Amount</th></tr>
                </thead>
                <tbody>
                    <tr><td style="border: 1px solid #333; padding: 8px;">SSS Contribution</td><td style="border: 1px solid #333; padding: 8px; text-align: right; color:#d32f2f;">₱ ${window.formatCurrency(pr.SSS_Deduct)}</td></tr>
                    <tr><td style="border: 1px solid #333; padding: 8px;">PhilHealth</td><td style="border: 1px solid #333; padding: 8px; text-align: right; color:#d32f2f;">₱ ${window.formatCurrency(pr.PHIC_Deduct)}</td></tr>
                    <tr><td style="border: 1px solid #333; padding: 8px;">Pag-IBIG</td><td style="border: 1px solid #333; padding: 8px; text-align: right; color:#d32f2f;">₱ ${window.formatCurrency(pr.HDMF_Deduct)}</td></tr>
                    <tr><td style="border: 1px solid #333; padding: 8px;">Withholding Tax</td><td style="border: 1px solid #333; padding: 8px; text-align: right; color:#d32f2f;">₱ ${window.formatCurrency(pr.Tax_Deduct)}</td></tr>
                </tbody>
                <tfoot>
                    <tr><td style="border: 1px solid #333; padding: 12px; font-weight: bold; font-size: 16px; background: #e8f5e9;">NET TAKE HOME PAY</td><td style="border: 1px solid #333; padding: 12px; text-align: right; font-weight: bold; font-size: 18px; background: #e8f5e9; color:#2e7d32;">₱ ${window.formatCurrency(pr.Net_Pay)}</td></tr>
                </tfoot>
            </table>
            
            <div style="margin-top: 50px; text-align: center;">
                <div style="border-bottom: 1px solid #333; width: 60%; margin: 0 auto; height: 30px;"></div>
                <p style="margin-top: 5px; font-weight: bold;">Employee Signature</p>
            </div>
        </div>`;

        const printWindow = window.open('', '_blank'); 
        printWindow.document.write('<html><head><title>Payslip</title></head><body>' + printContent + '</body></html>'); 
        printWindow.document.close(); 
        printWindow.focus(); 
        setTimeout(() => { printWindow.print(); printWindow.close(); }, 250);
    });
};