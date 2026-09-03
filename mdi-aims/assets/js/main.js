// ==========================================
// MDI AIMS - GLOBAL UTILITIES & CORE LOGIC
// ==========================================

document.addEventListener("DOMContentLoaded", () => {
    window.globalProductsList = [];
    window.globalCOAList = [];
    window.globalWarehousesList = []; 
    
    // Load global products immediately on system boot
    if (typeof window.loadGlobalProducts === 'function') {
        window.loadGlobalProducts();
    }

    const sortStyle = document.createElement('style');
    sortStyle.innerHTML = `
        table thead th { cursor: pointer; user-select: none; position: relative; transition: color 0.2s, background-color 0.2s; }
        table thead th:hover { color: var(--theme-green) !important; background-color: #f8f9fa; }
    `;
    document.head.appendChild(sortStyle);

    if (window.userRole !== 'Admin' && window.userPermissions && window.userPermissions.is_readonly) {
        document.body.classList.add('readonly-mode');
        const style = document.createElement('style');
        style.innerHTML = `
            body.readonly-mode button[data-bs-toggle="modal"],
            body.readonly-mode .modal-control-panel button[type="submit"] { display: none !important; }
        `;
        document.head.appendChild(style);
    }

    document.getElementById('toggleAppMenu').addEventListener('click', (e) => { 
        e.preventDefault(); 
        document.getElementById('appMenuOverlay').style.display = 'block'; 
        if(document.getElementById('navbarTitle')) document.getElementById('navbarTitle').style.visibility = 'hidden';
    });
    
    document.getElementById('closeAppMenuIcon').addEventListener('click', () => {
        document.getElementById('appMenuOverlay').style.display = 'none';
        if(document.getElementById('navbarTitle')) document.getElementById('navbarTitle').style.visibility = 'visible';
    });

    document.querySelectorAll('.search-bar').forEach(bar => {
        bar.addEventListener('keyup', function() {
            const filter = this.value.toUpperCase();
            const target = document.querySelector(this.getAttribute('data-target'));
            if(target) {
                target.querySelectorAll('table tbody tr').forEach(row => {
                    row.style.display = row.innerText.toUpperCase().includes(filter) ? "" : "none";
                });
            }
        });
    });

    fetch('api/endpoints.php?table=warehouses').then(r=>r.json()).then(data => {
        window.globalWarehousesList = data || [];
        if (document.getElementById('module-Settings') || document.querySelector('#dropdownsTable')) {
            setTimeout(() => { if(typeof window.loadDropdowns === 'function') window.loadDropdowns(); }, 500);
        }
    });

    const savedModule = localStorage.getItem('mdi_active_module') || 'Dashboard';
    if (savedModule && document.getElementById('module-' + savedModule)) {
        window.switchModule(savedModule);
    } else {
        document.getElementById('appMenuOverlay').style.display = 'block'; 
        if(document.getElementById('navbarTitle')) document.getElementById('navbarTitle').style.visibility = 'hidden';
    }

    // GLOBAL TABLE SORTING ALGORITHM
    document.addEventListener('click', (e) => {
        const th = e.target.closest('th');
        if (th && th.closest('table')) {
            const table = th.closest('table');
            const tbody = table.querySelector('tbody');
            if (!tbody) return;

            const headerText = th.innerText.replace(/  /g, '').trim().toUpperCase();
            if (headerText === 'ACTIONS' || headerText === '') return;

            const rows = Array.from(tbody.querySelectorAll('tr'));
            if (rows.length <= 1 && (!rows[0] || rows[0].cells.length <= 1)) return;

            const index = Array.from(th.parentNode.children).indexOf(th);
            const isAscending = th.classList.contains('sort-asc');

            table.querySelectorAll('th').forEach(header => {
                header.classList.remove('sort-asc', 'sort-desc');
                header.innerText = header.innerText.replace(/  /g, '').trim();
            });

            th.classList.toggle('sort-asc', !isAscending);
            th.classList.toggle('sort-desc', isAscending);
            th.innerText = th.innerText + (!isAscending ? '  ' : '  ');

            rows.sort((rowA, rowB) => {
                let cellA = rowA.cells[index]?.innerText.trim() || '';
                let cellB = rowB.cells[index]?.innerText.trim() || '';

                const isDate = (str) => /^\d{4}-\d{2}-\d{2}$/.test(str);
                const isNumeric = (str) => /^[\ \$-]?\s*\d[\d,]*(\.\d+)?( ITEMS)?( DAYS)?$/i.test(str);

                if (isDate(cellA) && isDate(cellB)) {
                    return !isAscending ? new Date(cellA) - new Date(cellB) : new Date(cellB) - new Date(cellA);
                }

                if (isNumeric(cellA) && isNumeric(cellB)) {
                    const numA = parseFloat(cellA.replace(/[^0-9.-]+/g, ''));
                    const numB = parseFloat(cellB.replace(/[^0-9.-]+/g, ''));
                    return !isAscending ? numA - numB : numB - numA;
                }

                return !isAscending 
                    ? cellA.localeCompare(cellB, undefined, { numeric: true, sensitivity: 'base' }) 
                    : cellB.localeCompare(cellA, undefined, { numeric: true, sensitivity: 'base' });
            });

            tbody.append(...rows);
        }
    });

    window.switchModule = function(appName) { 
        document.getElementById('appMenuOverlay').style.display = 'none'; 
        
        const navTitle = document.getElementById('navbarTitle');
        if (navTitle) {
            navTitle.style.visibility = 'visible';
            navTitle.textContent = appName; 
        }
        
        document.querySelectorAll('.module-container').forEach(mod => mod.classList.remove('active')); 
        
        const targetModule = document.getElementById('module-' + appName); 
        if (targetModule) { 
            targetModule.classList.add('active'); 
            localStorage.setItem('mdi_active_module', appName); 

            // AUTO-REFRESH LIVE DATA TRIGGERS
            if (appName === 'Dashboard') {
                if(typeof window.loadDashboardCharts === 'function') window.loadDashboardCharts();
            }
            if (appName === 'Accounts') {
                if(typeof window.loadCustomers === 'function') window.loadCustomers();
                if(typeof window.loadAllOutlets === 'function') window.loadAllOutlets();
                if(typeof window.loadDealers === 'function') window.loadDealers();
            }
            if (appName === 'Products') {
                if(typeof window.loadProducts === 'function') window.loadProducts();
                if(typeof window.loadProductPricing === 'function') window.loadProductPricing();
            }
            if (appName === 'Suppliers') {
                if(typeof window.loadSuppliers === 'function') window.loadSuppliers();
            }
            if (appName === 'DS') {
                if(typeof window.loadDsOrders === 'function') window.loadDsOrders();
                if(typeof window.loadDsInvoices === 'function') window.loadDsInvoices();
                if(typeof window.loadDsCollections === 'function') window.loadDsCollections();
            }
            if (appName === 'YL') {
                if(typeof window.loadYLOrders === 'function') window.loadYLOrders();
                if(typeof window.loadYlDeliveryReceipts === 'function') window.loadYlDeliveryReceipts();
                if(typeof window.loadYlInvoices === 'function') window.loadYlInvoices();
                if(typeof window.loadYlCollectionReceipts === 'function') window.loadYlCollectionReceipts();
            }
            if (appName === 'HR') {
                if(typeof window.loadEmployees === 'function') window.loadEmployees();
                if(typeof window.loadDTR === 'function') window.loadDTR();
                if(typeof window.loadPayroll === 'function') window.loadPayroll();
            }
            if (appName === 'Fleet') {
                if(typeof window.loadFleetVehicles === 'function') window.loadFleetVehicles();
                if(typeof window.loadFleetTrips === 'function') window.loadFleetTrips();
                if(typeof window.loadFleetMaintenance === 'function') window.loadFleetMaintenance();
            }
            if (appName === 'Discounts') {
                if(typeof window.loadYlRates === 'function') window.loadYlRates();
                if(typeof window.loadRebateMatrix === 'function') window.loadRebateMatrix();
            }
            if (appName === 'Accounting') {
                if(typeof window.loadAccountingCOA === 'function') window.loadAccountingCOA();
                if(typeof window.loadAccountingAR === 'function') window.loadAccountingAR();
                if(typeof window.loadAccountingAP === 'function') window.loadAccountingAP();
                if(typeof window.loadAccountingPV === 'function') window.loadAccountingPV();
                if(typeof window.loadAccountingExpenses === 'function') window.loadAccountingExpenses();
                if(typeof window.loadAccountingGL === 'function') window.loadAccountingGL();
                if(typeof window.loadFinancialReports === 'function') window.loadFinancialReports();
                if(typeof window.loadTaxReports === 'function') window.loadTaxReports();
                
                const currentAccId = document.getElementById('ledger_account_id')?.value;
                if (currentAccId && typeof window.loadAccountLedger === 'function') {
                    window.loadAccountLedger(currentAccId);
                }
            }
            if (appName === 'Inventory') {
                if(typeof window.loadStockBalances === 'function') window.loadStockBalances();
                if(typeof window.loadInventoryLedger === 'function') window.loadInventoryLedger();
                if(typeof window.loadStockTransfers === 'function') window.loadStockTransfers();
                if(typeof window.loadStockReturns === 'function') window.loadStockReturns();
            }
            if (appName === 'Purchasing') {
                if(typeof window.loadPurchaseOrders === 'function') window.loadPurchaseOrders();
                if(typeof window.loadGoodsReceipts === 'function') window.loadGoodsReceipts();
            }
            if (appName === 'Settings') {
                if(typeof window.loadCompanyProfile === 'function') window.loadCompanyProfile();
                if(typeof window.loadWarehousesSettings === 'function') window.loadWarehousesSettings();
                if(typeof window.loadDropdowns === 'function') window.loadDropdowns();
                if(typeof window.loadUsers === 'function') window.loadUsers();
                if(typeof window.loadAuditLogs === 'function') window.loadAuditLogs();
            }
        } else { 
            alert(appName + ' module is under construction!'); 
        } 
    };
});