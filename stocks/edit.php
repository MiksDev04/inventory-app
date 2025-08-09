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

// Fetch stock log details
try {
    $stmt = $pdo->prepare("
        SELECT sl.*, i.name as item_name, i.sku as item_sku, i.quantity as current_quantity 
        FROM stock_logs sl 
        JOIN items i ON sl.item_id = i.id 
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

// Fetch items for dropdown
try {
    $stmt = $pdo->prepare("SELECT id, name, sku, quantity FROM items ORDER BY name ASC");
    $stmt->execute();
    $items = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Error fetching items: " . $e->getMessage();
    $items = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Get current user ID
        $user_id = $_SESSION['user_id'] ?? 1;
        
        // Calculate quantity difference for adjustment
        $oldQuantity = $stockLog['quantity_changed'];
        $oldType = $stockLog['change_type'];
        $oldItemId = $stockLog['item_id'];
        
        $newQuantity = $_POST['quantity_changed'];
        $newType = $_POST['change_type'];
        $newItemId = $_POST['item_id'];

        // Reverse the old stock movement first
        if ($oldType === 'in') {
            $stmt = $pdo->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE items SET quantity = quantity + ? WHERE id = ?");
        }
        $stmt->execute([$oldQuantity, $oldItemId]);

        // Update stock log
        $stmt = $pdo->prepare("UPDATE stock_logs SET item_id = ?, change_type = ?, quantity_changed = ?, reason = ? WHERE id = ?");
        $stmt->execute([
            $newItemId,
            $newType,
            $newQuantity,
            $_POST['reason'],
            $id
        ]);

        // Apply the new stock movement
        if ($newType === 'in') {
            $stmt = $pdo->prepare("UPDATE items SET quantity = quantity + ? WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ?");
        }
        $stmt->execute([$newQuantity, $newItemId]);

        // Commit transaction
        $pdo->commit();

        header("Location: index.php?success=Stock movement updated successfully");
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $error = "Error updating stock movement: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="col">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-primary fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>Edit Stock Movement
                </h1>
                <p class="text-muted mb-0">Modify stock movement details</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Stock Logs
                </a>
            </div>
        </div>

        <!-- Error/Success Messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Current Movement Info -->
        <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
            <h6 class="alert-heading mb-2">
                <i class="bi bi-info-circle me-2"></i>Current Movement Details
            </h6>
            <div class="row g-2 small">
                <div class="col-md-3">
                    <strong>Item:</strong> <?= htmlspecialchars($stockLog['item_name']) ?>
                </div>
                <div class="col-md-3">
                    <strong>Type:</strong> 
                    <span class="badge bg-<?= $stockLog['change_type'] === 'in' ? 'success' : 'danger' ?>">
                        <?= ucfirst($stockLog['change_type']) ?>
                    </span>
                </div>
                <div class="col-md-3">
                    <strong>Quantity:</strong> <?= $stockLog['quantity_changed'] ?>
                </div>
                <div class="col-md-3">
                    <strong>Date:</strong> <?= date('M j, Y g:i A', strtotime($stockLog['created_at'])) ?>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Update Movement Details
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <!-- Item Selection -->
                        <div class="col-md-6">
                            <label for="item_id" class="form-label fw-medium">
                                <i class="bi bi-box-seam me-1"></i>Item <span class="text-danger">*</span>
                            </label>
                            <select name="item_id" id="item_id" class="form-select" required onchange="updateCurrentStock()">
                                <option value="">Select an item...</option>
                                <?php foreach ($items as $item): ?>
                                    <option value="<?= $item['id'] ?>" 
                                            data-sku="<?= htmlspecialchars($item['sku']) ?>"
                                            data-quantity="<?= $item['quantity'] ?>"
                                            <?= $stockLog['item_id'] == $item['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($item['name']) ?> (SKU: <?= htmlspecialchars($item['sku']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select an item.</div>
                            <small id="currentStock" class="form-text text-muted"></small>
                        </div>

                        <!-- Change Type -->
                        <div class="col-md-6">
                            <label for="change_type" class="form-label fw-medium">
                                <i class="bi bi-arrow-left-right me-1"></i>Movement Type <span class="text-danger">*</span>
                            </label>
                            <select name="change_type" id="change_type" class="form-select" required>
                                <option value="">Select movement type...</option>
                                <option value="in" <?= $stockLog['change_type'] == 'in' ? 'selected' : '' ?>>
                                    Stock In (Increase)
                                </option>
                                <option value="out" <?= $stockLog['change_type'] == 'out' ? 'selected' : '' ?>>
                                    Stock Out (Decrease)
                                </option>
                            </select>
                            <div class="invalid-feedback">Please select a movement type.</div>
                        </div>

                        <!-- Quantity Changed -->
                        <div class="col-md-6">
                            <label for="quantity_changed" class="form-label fw-medium">
                                <i class="bi bi-hash me-1"></i>Quantity <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                name="quantity_changed"
                                id="quantity_changed"
                                class="form-control"
                                min="1"
                                placeholder="Enter quantity"
                                value="<?= htmlspecialchars($stockLog['quantity_changed']) ?>"
                                required>
                            <div class="invalid-feedback">Please provide a valid quantity.</div>
                        </div>

                        <!-- Reason -->
                        <div class="col-12">
                            <label for="reason" class="form-label fw-medium">
                                <i class="bi bi-chat-text me-1"></i>Reason <span class="text-danger">*</span>
                            </label>
                            <textarea name="reason"
                                id="reason"
                                class="form-control"
                                rows="3"
                                placeholder="Describe the reason for this stock movement..."
                                required><?= htmlspecialchars($stockLog['reason']) ?></textarea>
                            <div class="invalid-feedback">Please provide a reason for the stock movement.</div>
                            <small class="form-text text-muted">Examples: Purchase order received, Sale to customer, Damaged goods, etc.</small>
                        </div>
                    </div>

                    <!-- Warning Notice -->
                    <div class="alert alert-warning border-0 shadow-sm mt-4" role="alert">
                        <h6 class="alert-heading mb-2">
                            <i class="bi bi-exclamation-triangle me-2"></i>Important Notice
                        </h6>
                        <p class="mb-0 small">Editing this stock movement will automatically adjust the item quantities. The system will reverse the original movement and apply the new changes.</p>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-check-circle me-2"></i>Update Movement
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Form Validation Script -->
<script>
    // Bootstrap form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
            
            // Initialize current stock display
            updateCurrentStock();
        }, false);
    })();

    // Update current stock display
    function updateCurrentStock() {
        const itemSelect = document.getElementById('item_id');
        const currentStockElement = document.getElementById('currentStock');
        
        if (itemSelect.value) {
            const selectedOption = itemSelect.options[itemSelect.selectedIndex];
            const currentQuantity = selectedOption.getAttribute('data-quantity');
            const sku = selectedOption.getAttribute('data-sku');
            
            currentStockElement.innerHTML = `Current stock: <strong>${currentQuantity}</strong> units (SKU: ${sku})`;
        } else {
            currentStockElement.innerHTML = '';
        }
    }
</script>

<?php
include '../includes/footer.php';
?>
