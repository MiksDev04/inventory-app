<?php
session_start();
// Include configuration and functions
include '../includes/config.php';

// Check if user is logged in
if (!isLogIn()) {
    header("Location: ../auth/login.php?error=Please log in first");
    exit;
}

// Get item ID from URL
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$item_id) {
    header("Location: index.php?error=Invalid item ID");
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Delete the item
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$item_id]);

        header("Location: index.php?success=Item deleted successfully");
        exit;
    } catch (Exception $e) {
        $error = "Error deleting item: " . $e->getMessage();
    }
}

// Fetch item data for display
try {
    $stmt = $pdo->prepare("SELECT i.*, c.name as category_name, s.name as supplier_name 
                          FROM items i 
                          LEFT JOIN categories c ON i.category_id = c.id 
                          LEFT JOIN suppliers s ON i.supplier_id = s.id 
                          WHERE i.id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        header("Location: index.php?error=Item not found");
        exit;
    }
} catch (Exception $e) {
    header("Location: index.php?error=Error fetching item: " . urlencode($e->getMessage()));
    exit;
}

include '../includes/header.php';
?>

<div class="col">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-danger fw-bold">
                    <i class="bi bi-trash me-2"></i>Delete Item
                </h1>
                <p class="text-muted mb-0">Permanently remove item from inventory</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Inventory
                </a>
            </div>
        </div>

        <!-- Error Messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Confirmation Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>Confirm Deletion
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. The item will be permanently removed from the inventory.
                </div>

                <!-- Item Details -->
                <div class="row g-1">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">SKU</label>
                        <p class="fw-bold"><?= htmlspecialchars($item['sku']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Item Name</label>
                        <p class="fw-bold"><?= htmlspecialchars($item['name']) ?></p>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Description</label>
                        <p><?= htmlspecialchars($item['description']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Category</label>
                        <p><?= $item['category_name'] ? htmlspecialchars($item['category_name']) : '<span class="text-muted">Not assigned</span>' ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Supplier</label>
                        <p><?= $item['supplier_name'] ? htmlspecialchars($item['supplier_name']) : '<span class="text-muted">Not assigned</span>' ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Quantity</label>
                        <p><?= number_format($item['quantity']) ?> units</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Unit Price</label>
                        <p>$<?= number_format($item['unit_price'], 2) ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Reorder Level</label>
                        <p><?= number_format($item['reorder_level']) ?> units</p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil me-2"></i>Edit Instead
                            </a>
                            <form method="POST" class="d-inline">
                                <button type="submit" name="confirm_delete" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash me-2"></i>Yes, Delete Item
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
