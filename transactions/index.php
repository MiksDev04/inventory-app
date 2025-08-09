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
    $stmt = $pdo->prepare("SELECT t.*, i.name as item_name, i.sku 
                            FROM transactions t 
                            LEFT JOIN items i ON t.item_id = i.id 
                            WHERE i.name LIKE ? OR i.sku LIKE ? OR t.type LIKE ?
                            ORDER BY t.date DESC, t.id DESC");
    $stmt->execute(['%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
} else {
    $stmt = $pdo->prepare("SELECT t.*, i.name as item_name, i.sku 
                            FROM transactions t 
                            LEFT JOIN items i ON t.item_id = i.id 
                            ORDER BY t.date DESC, t.id DESC");
    $stmt->execute();
}
$transactions = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="col">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-primary fw-bold">
                    <i class="bi bi-arrow-left-right me-2"></i>Transactions
                </h1>
                <p class="text-muted mb-0">Manage purchase and sale transactions</p>
            </div>
            <div>
                <a href="create.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Transaction
                </a>
            </div>
        </div>

        <!-- Search and Actions -->
        <div class="mb-4">
            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="search" 
                           name="search" 
                           placeholder="Search by item name, SKU, or type..." 
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
                Showing <?= count($transactions) ?> result(s) for "<strong><?= htmlspecialchars($search) ?></strong>"
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

        <!-- Transactions Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i>All Transactions
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($transactions)): ?>
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                        <h5 class="text-muted mb-2">No transactions found</h5>
                        <?php if (!empty($search)): ?>
                            <p class="text-muted mb-3">No transactions match your search criteria.</p>
                            <a href="index.php" class="btn btn-sm btn-outline-primary me-2">
                                <i class="bi bi-arrow-left me-1"></i>View All
                            </a>
                        <?php else: ?>
                            <p class="text-muted mb-3">Start by adding your first transaction.</p>
                        <?php endif; ?>
                        <a href="create.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Add Transaction
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Transactions Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-bold">ID</th>
                                    <th class="border-0 fw-bold">Type</th>
                                    <th class="border-0 fw-bold">Item</th>
                                    <th class="border-0 fw-bold">Quantity</th>
                                    <th class="border-0 fw-bold">Price</th>
                                    <th class="border-0 fw-bold">Total</th>
                                    <th class="border-0 fw-bold">Date</th>
                                    <th class="border-0 fw-bold text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td class="text-muted">#<?= $transaction['id'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= $transaction['type'] === 'purchase' ? 'success' : 'info' ?> bg-opacity-10 text-<?= $transaction['type'] === 'purchase' ? 'success' : 'info' ?> border border-<?= $transaction['type'] === 'purchase' ? 'success' : 'info' ?> border-opacity-25">
                                                <i class="bi bi-<?= $transaction['type'] === 'purchase' ? 'cart-plus' : 'cart-dash' ?> me-1"></i>
                                                <?= ucfirst($transaction['type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-medium"><?= htmlspecialchars($transaction['item_name'] ?? 'Unknown Item') ?></div>
                                                <small class="text-muted">SKU: <?= htmlspecialchars($transaction['sku'] ?? 'N/A') ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium"><?= number_format($transaction['qty']) ?></span>
                                            <small class="text-muted">units</small>
                                        </td>
                                        <td>
                                            <span class="fw-medium">$<?= number_format($transaction['price'], 2) ?></span>
                                            <small class="text-muted">per unit</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">$<?= number_format($transaction['qty'] * $transaction['price'], 2) ?></span>
                                        </td>
                                        <td>
                                            <div class="text-nowrap">
                                                <div><?= date('M j, Y', strtotime($transaction['date'])) ?></div>
                                                <small class="text-muted"><?= date('g:i A', strtotime($transaction['date'])) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="edit.php?id=<?= $transaction['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Edit Transaction">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="delete.php?id=<?= $transaction['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   title="Delete Transaction">
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
