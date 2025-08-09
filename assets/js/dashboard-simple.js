// Simple, working Chart.js implementation
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
        const stockCanvas = document.getElementById('stockTrendsChart');
        if (!stockCanvas) {
            console.log('Stock canvas not found');
            return;
        }

        console.log('Fetching stock trends data...');

        fetch('../api/chart-data.php?type=stock-trends')
            .then(response => {
                console.log('Stock trends response:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Stock trends data received:', data);
                
                if (data.error) {
                    throw new Error(data.error);
                }

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
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Quantity'
                                }
                            }
                        }
                    }
                });
                console.log('Stock trends chart created successfully');
            })
            .catch(error => {
                console.error('Error loading stock trends:', error);
                
                // Use fallback data
                const fallbackData = {
                    labels: ['Aug 2', 'Aug 3', 'Aug 4', 'Aug 5', 'Aug 6', 'Aug 7', 'Aug 8'],
                    datasets: [
                        {
                            label: 'Stock In (No Data)',
                            data: [0, 0, 0, 0, 0, 0, 0],
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            borderWidth: 3,
                            tension: 0.4
                        },
                        {
                            label: 'Stock Out (No Data)',
                            data: [0, 0, 0, 0, 0, 0, 0],
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 3,
                            tension: 0.4
                        }
                    ]
                };
                
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
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Quantity'
                                }
                            }
                        }
                    }
                });
                console.log('Stock trends chart created with fallback data');
            });
    }

    // Initialize Category Distribution Chart
    function initCategoryChart() {
        const categoryCanvas = document.getElementById('categoryChart');
        if (!categoryCanvas) {
            console.log('Category canvas not found');
            return;
        }

        console.log('Fetching category data...');

        fetch('../api/chart-data.php?type=category-distribution')
            .then(response => {
                console.log('Category response:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Category data received:', data);
                
                if (data.error) {
                    throw new Error(data.error);
                }

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
                            }
                        }
                    }
                });

                // Update category stats
                updateCategoryStats(data.labels, data.datasets[0].data);
                console.log('Category chart created successfully');
            })
            .catch(error => {
                console.error('Error loading category data:', error);
                
                // Use fallback data
                const fallbackData = {
                    labels: ['No Categories Found'],
                    datasets: [{
                        data: [1],
                        backgroundColor: ['#6c757d'],
                        borderWidth: 2
                    }]
                };
                
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
                            }
                        }
                    }
                });

                // Update category stats with fallback data
                updateCategoryStats(fallbackData.labels, fallbackData.datasets[0].data);
                console.log('Category chart created with fallback data');
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
        console.log('Setting up export/print handlers...');
        // Export/print functionality will be added here
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

    // Refresh charts functionality
    window.refreshCharts = function() {
        console.log('Refreshing charts...');
        if (stockTrendsChart) {
            stockTrendsChart.destroy();
        }
        if (categoryChart) {
            categoryChart.destroy();
        }
        initCharts();
    };

    // Start initialization when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing charts in 500ms...');
            setTimeout(initCharts, 500);
        });
    } else {
        console.log('DOM already loaded, initializing charts in 500ms...');
        setTimeout(initCharts, 500);
    }
})();
