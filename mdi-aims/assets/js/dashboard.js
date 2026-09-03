// ==========================================
// MDI AIMS - EXECUTIVE DASHBOARD LOGIC
// ==========================================

let financeTrendChart = null;

window.loadDashboardCharts = function() {
    fetch('api/endpoints.php?table=dashboard_analytics')
    .then(r => r.json())
    .then(data => {
        if (data.status !== 'success') return;

        // 1. Render Top Level Real-Time GL KPIs
        document.getElementById('dash_inv_val').innerText = `₱ ${window.formatCurrency(data.inv_valuation)}`;
        document.getElementById('dash_ar_val').innerText = `₱ ${window.formatCurrency(data.ar_total)}`;
        document.getElementById('dash_ap_val').innerText = `₱ ${window.formatCurrency(data.ap_total)}`;

        // 2. Render Top Sales Leaders (DS)
        const dsDiv = document.getElementById('dash_ds_leaders');
        dsDiv.innerHTML = '';
        if (data.top_ds.length === 0) dsDiv.innerHTML = '<p class="text-muted small fst-italic">No DS sales this month.</p>';
        data.top_ds.forEach((ds, index) => {
            const medal = index === 0 ? '<i class="bi bi-1-circle-fill text-warning me-2"></i>' : `<span class="text-muted me-2 font-monospace">${index+1}.</span>`;
            dsDiv.innerHTML += `<div class="d-flex justify-content-between align-items-center mb-2"><span class="fw-bold text-dark text-truncate" style="font-size:0.85rem; max-width:60%;">${medal}${ds.name}</span><span class="fw-bolder text-primary" style="font-size:0.85rem;">₱ ${window.formatCurrency(ds.total)}</span></div>`;
        });

        // 3. Render Top Sales Leaders (YL)
        const ylDiv = document.getElementById('dash_yl_leaders');
        ylDiv.innerHTML = '';
        if (data.top_yl.length === 0) ylDiv.innerHTML = '<p class="text-muted small fst-italic">No YL sales this month.</p>';
        data.top_yl.forEach((yl, index) => {
            const medal = index === 0 ? '<i class="bi bi-1-circle-fill text-warning me-2"></i>' : `<span class="text-muted me-2 font-monospace">${index+1}.</span>`;
            ylDiv.innerHTML += `<div class="d-flex justify-content-between align-items-center mb-2"><span class="fw-bold text-dark text-truncate" style="font-size:0.85rem; max-width:60%;">${medal}${yl.name}</span><span class="fw-bolder text-success" style="font-size:0.85rem;">₱ ${window.formatCurrency(yl.total)}</span></div>`;
        });

        // 4. Render Chart.js
        const labels = data.chart_labels.reverse();
        const revData = data.chart_revenue.reverse();
        const expData = data.chart_expenses.reverse();

        const ctx = document.getElementById('financeTrendChart');
        if(!ctx) return;
        
        if (financeTrendChart) { financeTrendChart.destroy(); }

        financeTrendChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Revenue', data: revData, backgroundColor: '#2e7d32', borderRadius: 4, barPercentage: 0.6 },
                    { label: 'Expenses', data: expData, backgroundColor: '#d32f2f', borderRadius: 4, barPercentage: 0.6 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { weight: 'bold' } } },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)', titleFont: { size: 13 }, bodyFont: { size: 13 }, padding: 10,
                        callbacks: { label: function(context) { return ' ' + context.dataset.label + ': ₱ ' + window.formatCurrency(context.raw); } }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9', drawBorder: false }, ticks: { font: { family: 'monospace' }, callback: function(value) { return '₱ ' + (value / 1000) + 'k'; } } },
                    x: { grid: { display: false, drawBorder: false }, ticks: { font: { weight: 'bold' } } }
                }
            }
        });
    })
    .catch(err => console.error("Error loading dashboard analytics:", err));
};

// Auto-load charts when the Dashboard module is initialized
document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('module-Dashboard')) {
        setTimeout(() => { if (document.getElementById('module-Dashboard').classList.contains('active')) { window.loadDashboardCharts(); } }, 500);
    }
});