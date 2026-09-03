<div id="module-Discounts" class="module-container p-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="erp-header mb-0"><i class="bi bi-tags-fill text-muted me-2"></i>Discounts & Rebates Management</h4>
    </div>
    
    <div class="row">
        <!-- YL Discount Multipliers -->
        <div class="col-md-12 mb-4">
            <div class="kpi-card p-4 border-top border-4 border-warning">
                <div class="d-flex justify-content-between mb-4 pb-3 border-bottom">
                    <h5 class="fw-bold mb-0 align-self-end text-dark" style="letter-spacing:-0.5px;"><i class="bi bi-percent text-warning me-2"></i>YL Discount Multipliers</h5>
                    <button class="btn btn-erp btn-warning text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#ylDiscountModal"><i class="bi bi-pencil-square me-2"></i>Edit Rates</button>
                </div>
                <div class="row">
                    <div class="col-md-3 border-end">
                        <span class="kpi-title d-block mb-2 text-primary">Dealer Orig CP</span>
                        <span id="disp_yl_orig" class="fw-bolder text-primary fs-3">0.450</span>
                    </div>
                    <div class="col-md-3 border-end">
                        <span class="kpi-title d-block mb-2 text-primary">Dealer Light CP</span>
                        <span id="disp_yl_light" class="fw-bolder text-primary fs-3">0.550</span>
                    </div>
                    <div class="col-md-3 border-end ps-4">
                        <span class="kpi-title d-block mb-2 text-warning">Trade Orig CP</span>
                        <span id="disp_yl_trade_orig" class="fw-bolder text-warning fs-3">0.500</span>
                    </div>
                    <div class="col-md-3 ps-4">
                        <span class="kpi-title d-block mb-2 text-warning">Trade Light CP</span>
                        <span id="disp_yl_trade_light" class="fw-bolder text-warning fs-3">0.700</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sales Rebate (Original) -->
        <div class="col-md-6 mb-4">
            <div class="kpi-card p-4 border-top border-4 border-danger h-100">
                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                    <h6 class="fw-bold mb-0 align-self-end text-dark"><i class="bi bi-graph-up-arrow text-danger me-2"></i>Sales Rebate (Original)</h6>
                    <button class="btn btn-erp btn-danger text-white shadow-sm btn-sm" onclick="window.openRebateModal('Original')"><i class="bi bi-pencil-square me-1"></i>Edit</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-center mb-0" id="rebateMatrixTable_Original">
                        <thead class="table-light"><tr><th class="text-secondary fw-bold">MIN QTY</th><th class="text-secondary fw-bold">MAX QTY</th><th class="text-danger fw-bold">REBATE AMOUNT / BTL</th></tr></thead>
                        <tbody><tr><td colspan="3" class="text-muted font-monospace py-3">Loading matrix...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Sales Rebate (Light) -->
        <div class="col-md-6 mb-4">
            <div class="kpi-card p-4 border-top border-4 border-primary h-100">
                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                    <h6 class="fw-bold mb-0 align-self-end text-dark"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Sales Rebate (Light)</h6>
                    <button class="btn btn-erp btn-primary text-white shadow-sm btn-sm" onclick="window.openRebateModal('Light')"><i class="bi bi-pencil-square me-1"></i>Edit</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-center mb-0" id="rebateMatrixTable_Light">
                        <thead class="table-light"><tr><th class="text-secondary fw-bold">MIN QTY</th><th class="text-secondary fw-bold">MAX QTY</th><th class="text-primary fw-bold">REBATE AMOUNT / BTL</th></tr></thead>
                        <tbody><tr><td colspan="3" class="text-muted font-monospace py-3">Loading matrix...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- DS Promotional Discounts -->
        <div class="col-md-12">
            <div class="kpi-card p-4 border-top border-4" style="border-color: #fd7e14 !important;">
                <div class="d-flex justify-content-between mb-2 pb-2">
                    <h5 class="fw-bold mb-0 align-self-end text-dark" style="letter-spacing:-0.5px;"><i class="bi bi-tags-fill me-2" style="color: #fd7e14;"></i>DS Promotional Discounts</h5>
                </div>
                <p class="text-muted mb-0 font-monospace py-3"><i class="bi bi-info-circle me-2"></i>DS Outlet specific discount overrides and volume rebates can be configured directly inside the Direct Sales -> Sales Invoices module during transaction creation.</p>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<div class="modal fade" id="rebateMatrixModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="rebateMatrixForm">
                <input type="hidden" id="rebate_matrix_type" value="Original">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-dark btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Matrix</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <button type="button" class="btn btn-outline-dark btn-sm px-3 fw-bold ms-2" onclick="window.addRebateRow()"><i class="bi bi-plus-lg me-1"></i>Add Row</button>
                    <span id="rebateModalTitle" class="ms-auto small text-uppercase fw-bolder text-dark"></span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-4 rounded-3 shadow-sm border-top border-4 border-dark">
                        <table class="table table-bordered text-center align-middle" id="editRebateTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-secondary" style="width: 30%;">MIN QTY</th>
                                    <th class="text-secondary" style="width: 30%;">MAX QTY</th>
                                    <th class="text-dark" style="width: 30%;">REBATE AMT</th>
                                    <th style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody id="rebateModalBody">
                                <!-- JS Injected Rows -->
                            </tbody>
                        </table>
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Use 999999 to indicate "and above" for the maximum quantity.</small>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ylDiscountModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="ylDiscountForm">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-warning btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Rates</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-percent me-1"></i>YL Discounts</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-4 rounded-3 shadow-sm border-top border-4 border-warning">
                        
                        <h6 class="fw-bold text-uppercase text-secondary mb-3 pb-2 border-bottom">Dealer's Discount</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-bold" style="font-size: 0.8rem;">ORIGINAL CP MULTIPLIER</label>
                                <input type="number" step="0.001" class="form-control odoo-input fw-bold text-primary fs-5" id="inp_yl_orig" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-bold" style="font-size: 0.8rem;">LIGHT CP MULTIPLIER</label>
                                <input type="number" step="0.001" class="form-control odoo-input fw-bold text-primary fs-5" id="inp_yl_light" required>
                            </div>
                        </div>

                        <h6 class="fw-bold text-uppercase text-warning mb-3 pb-2 border-bottom">Trade Discount</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-bold" style="font-size: 0.8rem;">ORIGINAL CP MULTIPLIER</label>
                                <input type="number" step="0.001" class="form-control odoo-input fw-bold text-warning fs-5" id="inp_yl_trade_orig" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-bold" style="font-size: 0.8rem;">LIGHT CP MULTIPLIER</label>
                                <input type="number" step="0.001" class="form-control odoo-input fw-bold text-warning fs-5" id="inp_yl_trade_light" required>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>