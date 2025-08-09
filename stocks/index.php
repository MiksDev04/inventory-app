<?php
session_start();
// Include configuration and functions
include '../includes/config.php';
// Check if user is logged in
if (!isLogIn()) {
    header("Location: ../auth/login.php?error=Please log in first");
    exit;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT sl.*, i.name as item_name, i.sku, u.username 
                            FROM stock_logs sl 
                            LEFT JOIN items i ON sl.item_id = i.id 
                            LEFT JOIN users u ON sl.user_id = u.id
                            WHERE i.name LIKE ? OR i.sku LIKE ? OR sl.reason LIKE ? OR u.username LIKE ?
                            ORDER BY sl.created_at DESC");
    $stmt->execute(['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
} else {
    $stmt = $pdo->prepare("SELECT sl.*, i.name as item_name, i.sku, u.username 
                            FROM stock_logs sl 
                            LEFT JOIN items i ON sl.item_id = i.id 
                            LEFT JOIN users u ON sl.user_id = u.id
                            ORDER BY sl.created_at DESC");
    $stmt->execute();
}
$stock_logs = $stmt->fetchAll();
include '../includes/header.php';
?>

<div class="col">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-primary fw-bold">
                    <i class="bi bi-clipboard-data me-2"></i>Stock Logs
                </h1>
                <p class="text-muted mb-0">Track all stock movements and changes</p>
            </div>
            <div>
                <a href="create.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Stock Movement
                </a>
            </div>
        </div>

        <!-- Search and Actions -->
        <div class="mb-4">
            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="search" 
                           name="search" 
                           placeholder="Search items, SKU, reason, or user..." 
                           class="form-control" 
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Search Results Info -->
        <?php if (!empty($search)): ?>
            <div class="alert alert-info border-0 shadow-sm mb-4">
                <i class="bi bi-info-circle me-2"></i>
                Showing <?= count($stock_logs) ?> result(s) for "<strong><?= htmlspecialchars($search) ?></strong>"
                <a href="index.php" class="alert-link ms-2">Clear search</a>
            </div>
        <?php endif; ?>

        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>
        
        <!-- Stock Logs Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i>Stock Movements
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($stock_logs)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-clipboard-data text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No stock movements found</h5>
                        <p class="text-muted">Start by logging your first stock movement.</p>
                        <a href="create.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add Stock Movement
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>SKU</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Reason</th>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stock_logs as $log): ?>
                                    <tr>
                                        <td class="fw-medium"><?= htmlspecialchars($log['item_name'] ?? 'Unknown Item') ?></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($log['sku'] ?? '-') ?></small></td>
                                        <td>
                                            <?php if ($log['change_type'] === 'in'): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-arrow-down me-1"></i>Stock In
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-arrow-up me-1"></i>Stock Out
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="fw-bold <?= $log['change_type'] === 'in' ? 'text-success' : 'text-danger' ?>">
                                                <?= $log['change_type'] === 'in' ? '+' : '-' ?><?= number_format($log['quantity_changed']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($log['reason']) ?></td>
                                        <td><?= htmlspecialchars($log['username'] ?? 'Unknown User') ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M d, Y g:i A', strtotime($log['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="edit.php?id=<?= $log['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <a href="delete.php?id=<?= $log['id'] ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
