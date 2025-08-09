<?php
session_start();
// Include configuration and functions
include '../includes/config.php';
// Check if user is logged in
if (!isLogIn()) {
    header("Location: ../auth/login.php?error=Please log in first");
    exit;
}

include '../includes/header.php';


// Get dashboard statistics (sample data)
$totalItems = getTotal('SELECT * FROM items');
$lowStockItems = getTotal('SELECT * FROM items WHERE quantity < 5');
$totalSuppliers = getTotal('SELECT * FROM suppliers');
$totalCategories = getTotal('SELECT * FROM categories');
$inventoryValue = getTotal('SELECT SUM(unit_price * quantity) FROM items');

// Sample recent transactions
$recentTransactions = get('SELECT t.*, i.name AS item_name FROM transactions t LEFT JOIN items i ON t.item_id = i.id ORDER BY t.date DESC LIMIT 5');

// Sample recent stock logs
$recentStockLogs = get('SELECT sl.*, i.name AS item_name FROM stock_logs AS sl LEFT JOIN items AS i ON sl.item_id = i.id ORDER BY sl.created_at DESC LIMIT 5');


?>

    <div class="container-fluid py-4">
        <!-- Header -->
        <header class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3 w-100">
            <div>
                <h1 class="h3 mb-0 text-primary fw-bold">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </h1>
                <p class="text-muted mb-0">Welcome back! Here's what's happening with your inventory.</p>
            </div>
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-lg-center gap-2 gap-sm-3">
                <div class="export-print-controls">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="exportDashboard()">
                            <i class="bi bi-download me-1 d-none d-sm-inline"></i>
                            <span class="d-none d-sm-inline">Export</span>
                            <i class="bi bi-download d-sm-none"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="printDashboard()">
                            <i class="bi bi-printer me-1 d-none d-sm-inline"></i>
                            <span class="d-none d-sm-inline">Print</span>
                            <i class="bi bi-printer d-sm-none"></i>
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="refreshCharts()">
                            <i class="bi bi-arrow-clockwise me-1 d-none d-sm-inline"></i>
                            <span class="d-none d-sm-inline">Refresh</span>
                            <i class="bi bi-arrow-clockwise d-sm-none"></i>
                        </button>
                      
                    </div>
                </div>
                <div class="text-end">
                    <small class="text-muted">Last updated: <?= date('M d, Y H:i') ?></small>
                </div>
            </div>
        </header>

        <!-- Statistics Cards Row -->
        <div class="row g-3 g-md-4 mb-4">
            <!-- Total Items Card -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-gradient rounded-3 p-3">
                                    <i class="bi bi-box-seam text-white fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Total Items</div>
                                <div class="h4 fw-bold mb-0"><?= number_format($totalItems) ?></div>
                                <div class="small text-success">
                                    <i class="bi bi-arrow-up"></i> Active inventory
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock Card -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-gradient rounded-3 p-3">
                                    <i class="bi bi-exclamation-triangle text-white fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Low Stock Items</div>
                                <div class="h4 fw-bold mb-0"><?= $lowStockItems ?></div>
                                <div class="small text-warning">
                                    <i class="bi bi-arrow-down"></i> Need reorder
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Suppliers Card -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-gradient rounded-3 p-3">
                                    <i class="bi bi-building text-white fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Suppliers</div>
                                <div class="h4 fw-bold mb-0"><?= $totalSuppliers ?></div>
                                <div class="small text-info">
                                    <i class="bi bi-people"></i> Active partners
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Value Card -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-gradient rounded-3 p-3">
                                    <i class="bi bi-currency-dollar text-white fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Inventory Value</div>
                                <div class="h4 fw-bold mb-0">$<?= number_format($inventoryValue, 2) ?></div>
                                <div class="small text-success">
                                    <i class="bi bi-graph-up"></i> Total worth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Charts and Analytics Row -->
<div class="row g-3 g-md-4 mb-4">
    <!-- Stock Trends Line Chart -->
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up text-primary me-2"></i>Stock Levels Trend
                        <small class="text-muted d-block d-sm-inline">(Last 7 Days)</small>
                    </h5>
                </div>
            </div>
            <div class="card-body">
                <!-- Chart.js Canvas -->
                <div class="chart-container responsive-chart">
                    <canvas id="stockTrendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Distribution Chart & Quick Actions -->
    <div class="col-12 col-xl-4">
        <div class="h-100 d-flex flex-column gap-3 gap-md-4">
            <!-- Category Distribution -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-pie-chart text-primary me-2"></i>Items by Category
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Chart.js Doughnut Chart -->
                    <div class="chart-container mb-3" style="position: relative; height: 180px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                    <!-- Category Stats -->
                    <div class="row g-2 text-center" id="categoryStatsContainer">
                        <!-- Categories will be dynamically loaded here -->
                        <div class="col-6">
                            <div class="bg-light rounded p-2">
                                <div class="fw-bold text-primary">-</div>
                                <small class="text-muted">Loading...</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-2">
                                <div class="fw-bold text-success">-</div>
                                <small class="text-muted">Loading...</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-2">
                                <div class="fw-bold text-warning">-</div>
                                <small class="text-muted">Loading...</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-2">
                                <div class="fw-bold text-info">-</div>
                                <small class="text-muted">Loading...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning text-primary me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2 d-xl-none">
                        <!-- Horizontal layout for tablet/mobile -->
                        <div class="col-6 col-sm-3">
                            <a href="../inventory/create.php" class="btn btn-primary btn-sm w-100 d-flex flex-column align-items-center justify-content-center" style="height: 60px;">
                                <i class="bi bi-plus-circle mb-1"></i>
                                <small>Add Item</small>
                            </a>
                        </div>
                        <div class="col-6 col-sm-3">
                            <a href="../stocks/create.php" class="btn btn-success btn-sm w-100 d-flex flex-column align-items-center justify-content-center" style="height: 60px;">
                                <i class="bi bi-arrow-up-circle mb-1"></i>
                                <small>Stock In</small>
                            </a>
                        </div>
                        <div class="col-6 col-sm-3">
                            <a href="../transactions/index.php" class="btn btn-info btn-sm w-100 d-flex flex-column align-items-center justify-content-center" style="height: 60px;">
                                <i class="bi bi-cart-check mb-1"></i>
                                <small>Record Sale</small>
                            </a>
                        </div>
                        <div class="col-6 col-sm-3">
                            <a href="../suppliers/create.php" class="btn btn-outline-primary btn-sm w-100 d-flex flex-column align-items-center justify-content-center" style="height: 60px;">
                                <i class="bi bi-building-add mb-1"></i>
                                <small>Add Supplier</small>
                            </a>
                        </div>
                    </div>
                    <div class="d-none d-xl-grid gap-2">
                        <!-- Vertical layout for desktop -->
                        <a href="../inventory/create.php" class="btn btn-primary btn-sm d-flex align-items-center justify-content-start">
                            <i class="bi bi-plus-circle me-2"></i>
                            <span>Add New Item</span>
                        </a>
                        <a href="../stocks/create.php" class="btn btn-success btn-sm d-flex align-items-center justify-content-start">
                            <i class="bi bi-arrow-up-circle me-2"></i>
                            <span>Stock In</span>
                        </a>
                        <a href="../transactions/index.php" class="btn btn-info btn-sm d-flex align-items-center justify-content-start">
                            <i class="bi bi-cart-check me-2"></i>
                            <span>Record Sale</span>
                        </a>
                        <a href="../suppliers/create.php" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-start">
                            <i class="bi bi-building-add me-2"></i>
                            <span>Add Supplier</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- Recent Activity Row -->
        <div class="row g-3 g-md-4">
            <!-- Recent Transactions -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history text-primary me-2"></i>Recent Transactions
                            </h5>
                            <a href="../transactions/" class="btn btn-outline-primary btn-sm">
                                <span class="d-none d-sm-inline">View All</span>
                                <i class="bi bi-arrow-right d-sm-none"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentTransactions)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentTransactions as $transaction): ?>
                                    <div class="list-group-item border-0 px-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <?php if ($transaction['type'] === 'sale'): ?>
                                                    <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                                        <i class="bi bi-arrow-up-right text-success"></i>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                                        <i class="bi bi-arrow-down-left text-primary"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div class="fw-medium"><?= htmlspecialchars($transaction['item_name']) ?></div>
                                                <div class="small text-muted">
                                                    <?= ucfirst($transaction['type']) ?> • Qty: <?= $transaction['qty'] ?> • 
                                                    $<?= number_format($transaction['price'], 2) ?>
                                                </div>
                                            </div>
                                            <div class="text-end flex-shrink-0">
                                                <div class="small text-muted"><?= date('M d, H:i', strtotime($transaction['date'])) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted fs-1"></i>
                                <p class="text-muted mt-2">No transactions yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Stock Movements -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-arrow-left-right text-primary me-2"></i>Recent Stock Movements
                            </h5>
                            <a href="../stocks/" class="btn btn-outline-primary btn-sm">
                                <span class="d-none d-sm-inline">View All</span>
                                <i class="bi bi-arrow-right d-sm-none"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentStockLogs)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentStockLogs as $log): ?>
                                    <div class="list-group-item border-0 px-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <?php if ($log['change_type'] === 'in'): ?>
                                                    <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                                        <i class="bi bi-plus-circle text-success"></i>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="bg-danger bg-opacity-10 rounded-circle p-2">
                                                        <i class="bi bi-dash-circle text-danger"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div class="fw-medium"><?= htmlspecialchars($log['item_name']) ?></div>
                                                <div class="small text-muted">
                                                    <?= $log['change_type'] === 'in' ? 'Added' : 'Removed' ?> <?= $log['quantity_changed'] ?> units
                                                    <?php if ($log['reason']): ?>
                                                        • <?= htmlspecialchars($log['reason']) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="text-end flex-shrink-0">
                                                <div class="small text-muted"><?= date('M d, H:i', strtotime($log['created_at'])) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted fs-1"></i>
                                <p class="text-muted mt-2">No stock movements yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <?php if ($lowStockItems > 0): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-warning border-0 shadow-sm" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-1">Low Stock Alert</h6>
                                <p class="mb-0">You have <?= $lowStockItems ?> items running low on stock. <a href="../stocks/index.php" class="alert-link">View details</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/dashboard-simple.js"></script>

<?php
include '../includes/footer.php';
?>