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
    header("Location: index.php?error=Invalid stock log ID");
    exit;
}

$id = (int)$_GET['id'];

// Fetch stock log details with related information
try {
    $stmt = $pdo->prepare("
        SELECT sl.*, 
               i.name as item_name, 
               i.sku as item_sku, 
               i.quantity as current_quantity,
               u.username as user_name
        FROM stock_logs sl 
        JOIN items i ON sl.item_id = i.id 
        LEFT JOIN users u ON sl.user_id = u.id
        WHERE sl.id = ?
    ");
    $stmt->execute([$id]);
    $stockLog = $stmt->fetch();
    
    if (!$stockLog) {
        header("Location: index.php?error=Stock log not found");
        exit;
    }
} catch (Exception $e) {
    header("Location: index.php?error=Error fetching stock log details");
    exit;
}

// Handle form submission (actual deletion)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Reverse the stock movement by updating item quantity
        if ($stockLog['change_type'] === 'in') {
            // If it was stock in, subtract the quantity
            $stmt = $pdo->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ?");
        } else {
            // If it was stock out, add the quantity back
            $stmt = $pdo->prepare("UPDATE items SET quantity = quantity + ? WHERE id = ?");
        }
        $stmt->execute([$stockLog['quantity_changed'], $stockLog['item_id']]);

        // Delete the stock log
        $stmt = $pdo->prepare("DELETE FROM stock_logs WHERE id = ?");
        $stmt->execute([$id]);

        // Commit transaction
        $pdo->commit();

        header("Location: index.php?success=Stock movement deleted and quantity adjusted successfully");
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $error = "Error deleting stock movement: " . $e->getMessage();
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
                    <i class="bi bi-trash me-2"></i>Delete Stock Movement
                </h1>
                <p class="text-muted mb-0">Confirm deletion of stock movement record</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Stock Logs
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
                <i class="bi bi-exclamation-triangle me-2"></i>Warning: Inventory Adjustment
            </h6>
            <p class="mb-0">Deleting this stock movement will automatically reverse the quantity changes made to the item inventory. This action cannot be undone.</p>
        </div>

        <!-- Stock Log Details Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle text-danger me-2"></i>Stock Movement Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- Item Information -->
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-box-seam me-1"></i>Item Information
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <div class="mb-2">
                                <strong>Name:</strong> <?= htmlspecialchars($stockLog['item_name']) ?>
                            </div>
                            <div class="mb-2">
                                <strong>SKU:</strong> <?= htmlspecialchars($stockLog['item_sku']) ?>
                            </div>
                            <div>
                                <strong>Current Stock:</strong> <?= $stockLog['current_quantity'] ?> units
                            </div>
                        </div>
                    </div>

                    <!-- Movement Information -->
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-arrow-left-right me-1"></i>Movement Information
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <div class="mb-2">
                                <strong>Type:</strong> 
                                <span class="badge bg-<?= $stockLog['change_type'] === 'in' ? 'success' : 'danger' ?> ms-1">
                                    <?= $stockLog['change_type'] === 'in' ? 'Stock In' : 'Stock Out' ?>
                                </span>
                            </div>
                            <div class="mb-2">
                                <strong>Quantity:</strong> 
                                <span class="text-<?= $stockLog['change_type'] === 'in' ? 'success' : 'danger' ?> fw-bold">
                                    <?= $stockLog['change_type'] === 'in' ? '+' : '-' ?><?= $stockLog['quantity_changed'] ?>
                                </span>
                            </div>
                            <div>
                                <strong>Date:</strong> <?= date('M j, Y g:i A', strtotime($stockLog['created_at'])) ?>
                            </div>
                        </div>
                    </div>

                    <!-- User Information -->
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-person me-1"></i>User Information
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <div>
                                <strong>Recorded by:</strong> <?= htmlspecialchars($stockLog['user_name'] ?? 'Unknown User') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-chat-text me-1"></i>Reason
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <div>
                                <?= nl2br(htmlspecialchars($stockLog['reason'])) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Impact Notice -->
                <div class="alert alert-info border-0 mt-4" role="alert">
                    <h6 class="alert-heading mb-2">
                        <i class="bi bi-calculator me-2"></i>Deletion Impact
                    </h6>
                    <div class="row g-2 small">
                        <div class="col-md-6">
                            <strong>Current Item Stock:</strong> <?= $stockLog['current_quantity'] ?> units
                        </div>
                        <div class="col-md-6">
                            <strong>After Deletion:</strong> 
                            <?php 
                                $afterDeletion = $stockLog['change_type'] === 'in' 
                                    ? $stockLog['current_quantity'] - $stockLog['quantity_changed']
                                    : $stockLog['current_quantity'] + $stockLog['quantity_changed'];
                            ?>
                            <span class="fw-bold"><?= $afterDeletion ?> units</span>
                            <span class="text-muted">
                                (<?= $stockLog['change_type'] === 'in' ? '-' : '+' ?><?= $stockLog['quantity_changed'] ?>)
                            </span>
                        </div>
                    </div>
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
                <p class="mb-3">Are you sure you want to delete this stock movement? This will:</p>
                <ul class="mb-3">
                    <li>Permanently remove this stock log record</li>
                    <li>Reverse the quantity change (<?= $stockLog['change_type'] === 'in' ? 'decrease' : 'increase' ?> item stock by <?= $stockLog['quantity_changed'] ?> units)</li>
                    <li>Cannot be undone</li>
                </ul>
                
                <form method="POST" class="d-flex gap-2 justify-content-end">
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                    <button type="submit" name="confirm_delete" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash me-2"></i>Yes, Delete Movement
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
