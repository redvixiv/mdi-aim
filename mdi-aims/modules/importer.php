<div id="module-Importer" class="module-container p-2">
    <style>
        .erp-header { font-weight: 800; color: #1e293b; letter-spacing: -0.5px; }
        .importer-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .drop-zone { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 40px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.2s; }
        .drop-zone:hover { border-color: var(--theme-green, #10b981); background: #f0fdf4; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="erp-header mb-0"><i class="bi bi-file-earmark-arrow-up text-muted me-2"></i>Bulk CSV Data Importer</h4>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="importer-card h-100 border-top border-4 border-primary">
                <h6 class="fw-bold text-uppercase text-primary mb-3"><i class="bi bi-gear-fill me-2"></i>1. Import Configuration</h6>
                
                <form id="bulkImportForm" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Target Data Table</label>
                        <select id="import_target" class="form-select form-select-lg fw-bold border-secondary-subtle" required>
                            <option value="">Select Import Target...</option>
                            <option value="products">Products & Pricing</option>
                            <option value="outlets">Outlets / Customers (DS)</option>
                            <option value="dealers">Independent Dealers (YL)</option>
                            <option value="suppliers">Suppliers</option>
                            <option value="employees">Employees (201 File)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase d-flex justify-content-between">
                            <span>CSV Data File</span>
                            <a href="#" class="text-primary text-decoration-none fw-bold" onclick="window.downloadCSVTemplate()"><i class="bi bi-download me-1"></i>Download Template</a>
                        </label>
                        <div class="drop-zone" onclick="document.getElementById('csv_file').click()">
                            <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 2.5rem;"></i>
                            <p class="mb-0 mt-2 fw-bold text-dark" id="csv_file_label">Click to select .CSV file</p>
                            <small class="text-muted">Must be a valid UTF-8 formatted CSV file</small>
                            <input type="file" id="csv_file" name="csv_file" accept=".csv" style="display: none;" required onchange="document.getElementById('csv_file_label').innerText = this.files[0] ? this.files[0].name : 'Click to select .CSV file'">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold text-uppercase shadow-sm" id="btnRunImport">
                        <i class="bi bi-box-arrow-in-down me-2"></i>Run Bulk Import
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="importer-card h-100">
                <h6 class="fw-bold text-uppercase text-secondary mb-3"><i class="bi bi-journal-check me-2"></i>2. Import Execution Summary</h6>
                
                <div id="importResultSummary" style="display: none;">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><div class="p-3 bg-success-subtle border border-success-subtle rounded-3 text-center"><span class="small text-uppercase fw-bold text-success d-block">Inserted</span><h2 class="mb-0 fw-bolder text-success" id="res_inserted">0</h2></div></div>
                        <div class="col-md-4"><div class="p-3 bg-warning-subtle border border-warning-subtle rounded-3 text-center"><span class="small text-uppercase fw-bold text-warning-emphasis d-block">Skipped / Duplicates</span><h2 class="mb-0 fw-bolder text-warning-emphasis" id="res_skipped">0</h2></div></div>
                        <div class="col-md-4"><div class="p-3 bg-danger-subtle border border-danger-subtle rounded-3 text-center"><span class="small text-uppercase fw-bold text-danger d-block">Errors</span><h2 class="mb-0 fw-bolder text-danger" id="res_errors">0</h2></div></div>
                    </div>
                    <div class="erp-table-container" style="max-height: 35vh; overflow-y: auto;">
                        <table class="erp-table" id="importLogTable">
                            <thead><tr><th>Status</th><th>Record Details</th><th>Notes</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div id="importPlaceholder" class="text-center py-5 text-muted font-monospace">
                    <i class="bi bi-file-earmark-spreadsheet" style="font-size: 3rem;"></i>
                    <p class="mt-2">Select a target table and upload a CSV file to view the migration log.</p>
                </div>
            </div>
        </div>
    </div>
</div>