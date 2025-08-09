// Dynamic Chart.js implementation with database integration
(function() {
    let stockTrendsChart = null;
    let categoryChart = null;

    // Wait for everything to load
    function initCharts() {
        // Check if Chart.js is available
        if (typeof Chart === 'undefined') {
            console.log('Chart.js not available, retrying...');
            setTimeout(initCharts, 100);
            return;
        }

        console.log('Chart.js loaded, initializing charts...');

        try {
            // Initialize Stock Trends Chart
            initStockTrendsChart();
            
            // Initialize Category Distribution Chart
            initCategoryChart();
            
            // Setup export/print handlers
            setupExportPrintHandlers();

        } catch (error) {
            console.error('Chart initialization error:', error);
        }
    }

    // Initialize Stock Trends Line Chart with database data
    function initStockTrendsChart() {
        let stockCanvas = document.getElementById('stockTrendsChart');
        if (!stockCanvas) return;

        // Show loading state
        showChartLoading(stockCanvas, 'Loading stock trends...');

        // Add timeout to fetch request
        const fetchTimeout = new Promise((_, reject) =>
            setTimeout(() => reject(new Error('Request timeout')), 10000)
        );

        Promise.race([
            fetch('../api/chart-data.php?type=stock-trends'),
            fetchTimeout
        ])
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.text(); // Get as text first to see what we're getting
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Stock trends data:', data);
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    // Restore canvas and get the new element
                    stockCanvas = hideChartLoading(stockCanvas);

                    stockTrendsChart = new Chart(stockCanvas, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: data.datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#ffffff',
                                    bodyColor: '#ffffff',
                                    cornerRadius: 6,
                                    padding: 10,
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y + ' units';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    },
                                    title: {
                                        display: true,
                                        text: 'Quantity'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                }
                            }
                        }
                    });
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    throw new Error('Invalid JSON response: ' + text);
                }
            })
            .catch(error => {
                console.error('Error loading stock trends:', error);
                console.log('Falling back to sample data...');
                
                // Fallback to sample data
                const fallbackData = {
                    labels: ['Aug 2', 'Aug 3', 'Aug 4', 'Aug 5', 'Aug 6', 'Aug 7', 'Aug 8'],
                    datasets: [
                        {
                            label: 'Stock In',
                            data: [45, 68, 82, 95, 115, 135, 155],
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            pointBackgroundColor: '#22c55e',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        },
                        {
                            label: 'Stock Out',
                            data: [38, 52, 68, 75, 88, 105, 125],
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            pointBackgroundColor: '#ef4444',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }
                    ]
                };
                
                try {
                    // Restore canvas and create chart with fallback data
                    stockCanvas = hideChartLoading(stockCanvas);
                    
                    stockTrendsChart = new Chart(stockCanvas, {
                        type: 'line',
                        data: fallbackData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#ffffff',
                                    bodyColor: '#ffffff',
                                    cornerRadius: 6,
                                    padding: 10,
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y + ' units (sample data)';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    },
                                    title: {
                                        display: true,
                                        text: 'Quantity'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                }
                            }
                        }
                    });
                } catch (chartError) {
                    console.error('Error creating fallback chart:', chartError);
                    showChartError(stockCanvas, 'Failed to load chart');
                }
            });
    }

    // Initialize Category Distribution Chart with database data
    function initCategoryChart() {
        let categoryCanvas = document.getElementById('categoryChart');
        if (!categoryCanvas) return;

        // Show loading state
        showChartLoading(categoryCanvas, 'Loading categories...');

        // Add timeout to fetch request
        const fetchTimeout = new Promise((_, reject) =>
            setTimeout(() => reject(new Error('Request timeout')), 10000)
        );

        Promise.race([
            fetch('../api/chart-data.php?type=category-distribution'),
            fetchTimeout
        ])
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Category data:', data);
                
                if (data.error) {
                    throw new Error(data.error);
                }

                // Restore canvas and get the new element
                categoryCanvas = hideChartLoading(categoryCanvas);

                categoryChart = new Chart(categoryCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                cornerRadius: 6,
                                padding: 10,
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + context.parsed + ' items (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });

                // Update category stats in the UI
                updateCategoryStats(data.labels, data.datasets[0].data);
            })
            .catch(error => {
                console.error('Error loading category data:', error);
                console.log('Falling back to sample category data...');
                
                // Fallback to sample data
                const fallbackData = {
                    labels: ['Electronics', 'Clothing', 'Food', 'Books'],
                    datasets: [{
                        data: [45, 32, 28, 15],
                        backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b', '#06b6d4'],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                };
                
                try {
                    // Restore canvas and create chart with fallback data
                    categoryCanvas = hideChartLoading(categoryCanvas);
                    
                    categoryChart = new Chart(categoryCanvas, {
                        type: 'doughnut',
                        data: fallbackData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#ffffff',
                                    bodyColor: '#ffffff',
                                    cornerRadius: 6,
                                    padding: 10,
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                            return context.label + ': ' + context.parsed + ' items (' + percentage + '%) (sample)';
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Update category stats with fallback data
                    updateCategoryStats(fallbackData.labels, fallbackData.datasets[0].data);
                } catch (chartError) {
                    console.error('Error creating fallback category chart:', chartError);
                    showChartError(categoryCanvas, 'Failed to load chart');
                }
            });
    }

    // Update category statistics display
    function updateCategoryStats(labels, data) {
        const categoryStatsContainer = document.getElementById('categoryStatsContainer');
        if (!categoryStatsContainer) return;

        // Clear loading state and create new stats
        categoryStatsContainer.innerHTML = '';

        const colors = ['primary', 'success', 'warning', 'info', 'danger', 'secondary'];
        
        labels.forEach((label, index) => {
            if (index < 4) { // Only show top 4 categories
                const colorClass = colors[index] || 'secondary';
                const statHtml = `
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="fw-bold text-${colorClass}">${data[index] || 0}</div>
                            <small class="text-muted">${label}</small>
                        </div>
                    </div>
                `;
                categoryStatsContainer.insertAdjacentHTML('beforeend', statHtml);
            }
        });

        // Fill remaining slots if less than 4 categories
        while (categoryStatsContainer.children.length < 4) {
            const emptyStatHtml = `
                <div class="col-6">
                    <div class="bg-light rounded p-2">
                        <div class="fw-bold text-muted">0</div>
                        <small class="text-muted">No Category</small>
                    </div>
                </div>
            `;
            categoryStatsContainer.insertAdjacentHTML('beforeend', emptyStatHtml);
        }
    }

    // Setup export and print handlers
    function setupExportPrintHandlers() {
        // Stock trends export/print
        setupDropdownHandlers('stockTrendsChart', 'stock-trends');
        
        // Category chart export/print (if there's a dropdown for it)
        setupDropdownHandlers('categoryChart', 'category-distribution');
        
        // Dashboard summary export/print
        setupDashboardExportPrint();
    }

    // Setup dropdown handlers for charts
    function setupDropdownHandlers(chartId, dataType) {
        const chartContainer = document.getElementById(chartId)?.closest('.card');
        if (!chartContainer) return;

        const dropdown = chartContainer.querySelector('.dropdown-menu');
        if (!dropdown) return;

        // Clear existing handlers and add new ones
        dropdown.innerHTML = `
            <li><a class="dropdown-item export-btn" href="#" data-type="${dataType}">
                <i class="bi bi-download me-2"></i>Export CSV
            </a></li>
            <li><a class="dropdown-item print-btn" href="#" data-type="${dataType}">
                <i class="bi bi-printer me-2"></i>Print Report
            </a></li>
        `;

        // Add event listeners
        dropdown.querySelector('.export-btn').addEventListener('click', function(e) {
            e.preventDefault();
            exportChart(this.dataset.type);
        });

        dropdown.querySelector('.print-btn').addEventListener('click', function(e) {
            e.preventDefault();
            printChart(this.dataset.type);
        });
    }

    // Setup dashboard-wide export/print functionality
    function setupDashboardExportPrint() {
        // Add export/print buttons to header if they don't exist
        const header = document.querySelector('header');
        if (!header) return;

        const exportPrintDiv = header.querySelector('.export-print-controls');
        if (!exportPrintDiv) {
            const buttonsHtml = `
                <div class="export-print-controls">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportDashboard()">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="printDashboard()">
                            <i class="bi bi-printer me-1"></i>Print
                        </button>
                    </div>
                </div>
            `;
            
            header.querySelector('.text-end').insertAdjacentHTML('beforeend', buttonsHtml);
        }
    }

    // Export chart data
    function exportChart(type) {
        const url = `../api/export-print.php?action=export&type=${type}`;
        window.open(url, '_blank');
    }

    // Print chart report
    function printChart(type) {
        const url = `../api/export-print.php?action=print&type=${type}`;
        window.open(url, '_blank');
    }

    // Global functions for dashboard export/print
    window.exportDashboard = function() {
        const url = '../api/export-print.php?action=export&type=dashboard-summary';
        window.open(url, '_blank');
    };

    window.printDashboard = function() {
        const url = '../api/export-print.php?action=print&type=dashboard-summary';
        window.open(url, '_blank');
    };

    // Utility functions for loading states
    function showChartLoading(canvas, message = 'Loading...') {
        const container = canvas.closest('.card-body');
        if (container) {
            container.innerHTML = `
                <div class="chart-loading">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    ${message}
                </div>
            `;
        }
    }

    function hideChartLoading(canvas) {
        const container = canvas.closest('.card-body');
        const canvasId = canvas.id;
        const canvasStyle = canvas.getAttribute('style') || 'height: 300px;';
        
        if (container) {
            container.innerHTML = `<canvas id="${canvasId}" style="${canvasStyle}"></canvas>`;
            return container.querySelector('canvas'); // Return the new canvas element
        }
        return canvas;
    }

    function showChartError(canvas, message) {
        const container = canvas.closest('.card-body');
        if (container) {
            container.innerHTML = `
                <div class="chart-error">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>${message}</div>
                    <button class="btn btn-outline-primary btn-sm mt-2" onclick="refreshCharts()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Retry
                    </button>
                </div>
            `;
        }
    }

    // Refresh charts functionality
    window.refreshCharts = function() {
        if (stockTrendsChart) {
            stockTrendsChart.destroy();
            initStockTrendsChart();
        }
        
        if (categoryChart) {
            categoryChart.destroy();
            initCategoryChart();
        }
    };

    // Start initialization when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initCharts, 500);
        });
    } else {
        setTimeout(initCharts, 500);
    }
})();