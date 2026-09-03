<div id="module-Dashboard" class="module-container p-2">     
    <div class="d-flex justify-content-between align-items-center mb-4">         
        <h4 class="erp-header mb-0" style="font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">             
            <i class="bi bi-speedometer2 text-muted me-2"></i>Executive Analytics         
        </h4>         
        <button class="btn btn-sm btn-primary fw-bold text-uppercase shadow-sm" style="border-radius: 6px;" onclick="window.loadDashboardCharts()">             
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh Data         
        </button>     
    </div>     
    <!-- Top KPI Row (Real-Time Accounting Data) -->     
    <div class="row g-3 mb-4">         
        <div class="col-md-4">             
            <div class="card border-0 shadow-sm rounded-3 p-4 h-100 border-bottom border-4 border-info" style="background: #fff;">                 
                <span class="text-muted small fw-bolder text-uppercase mb-1"><i class="bi bi-box-seam me-2 text-info"></i>Current Inventory Valuation</span>                 
                <h2 class="fw-bolder mb-0 text-dark" id="dash_inv_val" style="letter-spacing: -1px;">₱0.00</h2>             
            </div>         
        </div>         
        <div class="col-md-4">             
            <div class="card border-0 shadow-sm rounded-3 p-4 h-100 border-bottom border-4 border-primary" style="background: #fff;">                 
                <span class="text-muted small fw-bolder text-uppercase mb-1"><i class="bi bi-box-arrow-in-down-left me-2 text-primary"></i>Total Accounts Receivable (AR)</span>                 
                <h2 class="fw-bolder mb-0 text-primary" id="dash_ar_val" style="letter-spacing: -1px;">₱0.00</h2>             
            </div>         
        </div>         
        <div class="col-md-4">             
            <div class="card border-0 shadow-sm rounded-3 p-4 h-100 border-bottom border-4 border-danger" style="background: #fff;">                 
                <span class="text-muted small fw-bolder text-uppercase mb-1"><i class="bi bi-box-arrow-up-right me-2 text-danger"></i>Total Accounts Payable (AP)</span>                 
                <h2 class="fw-bolder mb-0 text-danger" id="dash_ap_val" style="letter-spacing: -1px;">₱0.00</h2>             
            </div>         
        </div>     
    </div>     
    <div class="row g-4">         
        <!-- 6-Month Financial Trend Chart -->         
        <div class="col-lg-8">             
            <div class="card border-0 shadow-sm rounded-3 p-4 h-100" style="background: #fff;">                 
                <h6 class="fw-bold text-uppercase text-secondary mb-4" style="letter-spacing: 0.5px;">6-Month Revenue vs Expense Trend</h6>                 
                <div style="position: relative; height: 350px; width: 100%;">                     
                    <canvas id="financeTrendChart"></canvas>                 
                </div>             
            </div>         
        </div>                  
        <!-- Top Sales Leaders -->         
        <div class="col-lg-4">             
            <div class="card border-0 shadow-sm rounded-3 p-4 h-100" style="background: #fff;">                 
                <h6 class="fw-bold text-uppercase text-secondary mb-3 border-bottom pb-3" style="letter-spacing: 0.5px;"><i class="bi bi-trophy-fill text-warning me-2"></i>Top Sales This Month</h6>                                  
                <span class="d-block text-muted small fw-bold text-uppercase mb-2 text-primary">Direct Sales (DS) Leaders</span>                 
                <div id="dash_ds_leaders" class="mb-4">                     
                    <p class="text-muted small font-monospace">Loading data...</p>                 
                </div>                 
                <span class="d-block text-muted small fw-bold text-uppercase mb-2 text-success">Yakult Lady (YL) Leaders</span>                 
                <div id="dash_yl_leaders">                     
                    <p class="text-muted small font-monospace">Loading data...</p>                 
                </div>             
            </div>         
        </div>     
    </div> 
</div>