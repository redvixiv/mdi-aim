<div id="module-Products" class="module-container">
    <div class="d-flex justify-content-between align-items-end mb-4 pb-2 border-bottom">
        <div>
            <h3 class="text-secondary fw-bolder m-0 text-uppercase" style="letter-spacing: 1px;"><i class="bi bi-box me-2 text-primary"></i>Products</h3>
            <small class="text-muted text-uppercase fw-bold">Manage Product Masterlist and Pricing Definitions</small>
        </div>
    </div>
    
    <ul class="nav nav-tabs mb-4 border-bottom-0" id="productTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active text-uppercase fw-bold px-4" data-bs-toggle="tab" data-bs-target="#prod-masterlist" type="button">Product Masterlist</button></li>
        <li class="nav-item"><button class="nav-link text-uppercase fw-bold px-4" data-bs-toggle="tab" data-bs-target="#prod-pricing" type="button">Product Pricing</button></li>
    </ul>
    
    <div class="tab-content" id="productTabsContent">
        <div class="tab-pane fade show active" id="prod-masterlist">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <button class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase" data-bs-toggle="modal" data-bs-target="#productModal"><i class="bi bi-plus-lg me-2"></i>New Product</button>
                    <div class="input-group input-group-sm w-25 shadow-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light search-bar" data-target="#productsTable" placeholder="Search products...">
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-hover align-middle text-nowrap m-0" id="productsTable" style="font-size: 0.85rem;">
                        <thead class="table-light text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px; position: sticky; top: 0; z-index: 1;">
                            <tr><th>Product No</th><th>Product Name</th><th>Category</th><th>Description</th><th>Date Created</th><th class="text-center">Actions</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="prod-pricing">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <button class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase" data-bs-toggle="modal" data-bs-target="#pricingModal"><i class="bi bi-tag-fill me-2"></i>Add Pricing</button>
                    <div class="input-group input-group-sm w-25 shadow-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light search-bar" data-target="#pricingTable" placeholder="Search pricing...">
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-hover align-middle text-nowrap m-0" id="pricingTable" style="font-size: 0.85rem;">
                        <thead class="table-light text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px; position: sticky; top: 0; z-index: 1;">
                            <tr><th>Product Name</th><th class="text-end">Unit Cost</th><th class="text-end">Wholesale</th><th class="text-end">Retail</th><th class="text-end">ODL</th><th>Effective From</th><th>Effective To</th><th class="text-center">Actions</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="productForm">
                <input type="hidden" id="p_product_id"> <!-- FIXED: Added Hidden ID -->
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Product</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-box me-1"></i>Product Details</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm border-top border-4 border-primary">
                        <label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">Product Name</label>
                        <input type="text" id="p_name" class="form-control odoo-title-input mb-0 fs-4" required>
                        <div class="row mt-5">
                            <div class="col-md-12">
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Category</label><input type="text" id="p_category" class="form-control odoo-input fw-bold"></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Description</label><input type="text" id="p_desc" class="form-control odoo-input"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="pricingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 odoo-modal-content">
            <form id="pricingForm">
                <input type="hidden" id="pr_pricing_id">
                <div class="modal-control-panel bg-white border-bottom p-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm text-uppercase">Save Pricing</button>
                    <button type="button" class="btn btn-light btn-sm px-4 fw-bold shadow-sm border ms-2 text-uppercase" data-bs-dismiss="modal">Discard</button>
                    <span class="ms-auto text-muted small text-uppercase fw-bolder"><i class="bi bi-tags me-1"></i>Pricing Setup</span>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="odoo-sheet bg-white p-5 rounded-3 shadow-sm border-top border-4 border-primary">
                        <label class="text-muted small fw-bolder mb-1 text-uppercase text-primary">Select Product</label>
                        <select id="pr_product_id" class="form-select odoo-title-input mb-0 fs-5" required><option value="">Select a Product...</option></select>
                        <div class="row mt-5">
                            <div class="col-md-6 pe-lg-4">
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-danger">Unit Cost</label><input type="number" step="0.01" id="pr_cost" class="form-control odoo-input text-danger fw-bolder" required></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Wholesale Price</label><input type="number" step="0.01" id="pr_wholesale" class="form-control odoo-input fw-bolder" required></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-success">Retail Price</label><input type="number" step="0.01" id="pr_retail" class="form-control odoo-input fw-bolder" required></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-info">ODL Price</label><input type="number" step="0.01" id="pr_odl" class="form-control odoo-input fw-bolder" required></div>
                            </div>
                            <div class="col-md-6 ps-lg-4">
                                <div class="odoo-field-group mb-3"><label class="odoo-label text-primary">Effective From</label><input type="date" id="pr_from" class="form-control odoo-input fw-bold" style="text-transform: none;" required></div>
                                <div class="odoo-field-group mb-3"><label class="odoo-label">Effective To</label><input type="date" id="pr_to" class="form-control odoo-input" style="text-transform: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>