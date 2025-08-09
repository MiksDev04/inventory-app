<?php
session_start();
// Include configuration and functions
include '../includes/config.php';
// Check if user is logged in
if (!isLogIn()) {
    header("Location: ../auth/login.php?error=Please log in first");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?error=Invalid transaction ID");
    exit;
}

$id = (int)$_GET['id'];

// Fetch transaction details with related information
try {
    $stmt = $pdo->prepare("
        SELECT t.*, 
               i.name as item_name, 
               i.sku as item_sku, 
               i.quantity as current_quantity
        FROM transactions t 
        JOIN items i ON t.item_id = i.id 
        WHERE t.id = ?
    ");
    $stmt->execute([$id]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        header("Location: index.php?error=Transaction not found");
        exit;
    }
} catch (Exception $e) {
    header("Location: index.php?error=Error fetching transaction details");
    exit;
}

// Handle form submission (actual deletion)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Delete the transaction
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: index.php?success=Transaction deleted successfully");
        exit;
    } catch (Exception $e) {
        $error = "Error deleting transaction: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="col">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-danger fw-bold">
                    <i class="bi bi-trash me-2"></i>Delete Transaction
                </h1>
                <p class="text-muted mb-0">Confirm deletion of transaction record</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Transactions
                </a>
            </div>
        </div>

        <!-- Error Messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Warning Alert -->
        <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
            <h6 class="alert-heading mb-2">
                <i class="bi bi-exclamation-triangle me-2"></i>Warning: Permanent Deletion
            </h6>
            <p class="mb-0">You are about to permanently delete this transaction record. This action cannot be undone and will remove all associated data.</p>
        </div>

        <!-- Transaction Details Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle text-danger me-2"></i>Transaction Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-hash me-1"></i>Basic Information
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <div class="mb-2">
                                <strong>Transaction ID:</strong> #<?= $transaction['id'] ?>
                            </div>
                            <div class="mb-2">
                                <strong>Type:</strong> 
                                <span class="badge bg-<?= $transaction['type'] === 'purchase' ? 'success' : 'info' ?> bg-opacity-10 text-<?= $transaction['type'] === 'purchase' ? 'success' : 'info' ?> border border-<?= $transaction['type'] === 'purchase' ? 'success' : 'info' ?> border-opacity-25">
                                    <i class="bi bi-<?= $transaction['type'] === 'purchase' ? 'cart-plus' : 'cart-dash' ?> me-1"></i>
                                    <?= ucfirst($transaction['type']) ?>
                                </span>
                            </div>
                            <div>
                                <strong>Date:</strong> <?= date('M j, Y g:i A', strtotime($transaction['date'])) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Item Information -->
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-box-seam me-1"></i>Item Information
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <div class="mb-2">
                                <strong>Name:</strong> <?= htmlspecialchars($transaction['item_name']) ?>
                            </div>
                            <div class="mb-2">
                                <strong>SKU:</strong> <?= htmlspecialchars($transaction['item_sku']) ?>
                            </div>
                            <div>
                                <strong>Current Stock:</strong> <?= $transaction['current_quantity'] ?> units
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Details -->
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-calculator me-1"></i>Transaction Details
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <div class="mb-2">
                                <strong>Quantity:</strong> <?= number_format($transaction['qty']) ?> units
                            </div>
                            <div class="mb-2">
                                <strong>Price per Unit:</strong> $<?= number_format($transaction['price'], 2) ?>
                            </div>
                            <div>
                                <strong>Total Amount:</strong> 
                                <span class="fw-bold text-primary">$<?= number_format($transaction['qty'] * $transaction['price'], 2) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-cash-stack me-1"></i>Financial Impact
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <div class="mb-2">
                                <strong>Transaction Type:</strong> 
                                <span class="text-<?= $transaction['type'] === 'purchase' ? 'danger' : 'success' ?>">
                                    <?= $transaction['type'] === 'purchase' ? 'Expense' : 'Revenue' ?>
                                </span>
                            </div>
                            <div class="mb-2">
                                <strong>Amount:</strong> 
                                <span class="text-<?= $transaction['type'] === 'purchase' ? 'danger' : 'success' ?> fw-bold">
                                    <?= $transaction['type'] === 'purchase' ? '-' : '+' ?>$<?= number_format($transaction['qty'] * $transaction['price'], 2) ?>
                                </span>
                            </div>
                            <div>
                                <strong>Impact:</strong> 
                                <span class="text-muted">
                                    <?= $transaction['type'] === 'purchase' ? 'Cost reduction' : 'Revenue loss' ?> upon deletion
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deletion Impact Notice -->
                <div class="alert alert-info border-0 mt-4" role="alert">
                    <h6 class="alert-heading mb-2">
                        <i class="bi bi-info-circle me-2"></i>Deletion Impact
                    </h6>
                    <p class="mb-0 small">
                        <strong>Note:</strong> Deleting this transaction will only remove the record from the system. 
                        It will not automatically adjust inventory quantities. If you need to reverse the impact of this transaction, 
                        consider creating a counter-transaction instead of deleting this record.
                    </p>
                </div>
            </div>
        </div>

        <!-- Confirmation Form -->
        <div class="card border-danger mt-4">
            <div class="card-header bg-danger text-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirm Deletion
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-3">Are you sure you want to delete this transaction? This will:</p>
                <ul class="mb-3">
                    <li>Permanently remove this transaction record (#<?= $transaction['id'] ?>)</li>
                    <li>Remove the financial record of this <?= $transaction['type'] ?></li>
                    <li>Cannot be undone or recovered</li>
                    <li><strong>Will NOT</strong> automatically adjust inventory quantities</li>
                </ul>
                
                <div class="alert alert-warning border-0 mb-3" role="alert">
                    <small>
                        <i class="bi bi-lightbulb me-1"></i>
                        <strong>Tip:</strong> If you need to reverse the effects of this transaction, consider creating a counter-transaction instead of deleting this record to maintain a complete audit trail.
                    </small>
                </div>
                
                <form method="POST" class="d-flex gap-2 justify-content-end">
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                    <button type="submit" name="confirm_delete" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash me-2"></i>Yes, Delete Transaction
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
