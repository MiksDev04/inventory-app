<?php
session_start();
// Include configuration and functions
include '../includes/config.php';
// Check if user is logged in
if (!isLogIn()) {
    header("Location: ../auth/login.php?error=Please log in first");
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

        // Get current user ID (assuming it's stored in session)
        $user_id = $_SESSION['user_id'] ?? 1; // Default to 1 if not set

        // Insert stock log
        $stmt = $pdo->prepare("INSERT INTO stock_logs (item_id, user_id, change_type, quantity_changed, reason, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $_POST['item_id'],
            $user_id,
            $_POST['change_type'],
            $_POST['quantity_changed'],
            $_POST['reason']
        ]);

        // Update item quantity based on change type
        if ($_POST['change_type'] === 'in') {
            $stmt = $pdo->prepare("UPDATE items SET quantity = quantity + ? WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ?");
        }
        $stmt->execute([$_POST['quantity_changed'], $_POST['item_id']]);

        // Commit transaction
        $pdo->commit();

        header("Location: index.php?success=Stock movement recorded successfully");
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $error = "Error recording stock movement: " . $e->getMessage();
        // Keep form data only when there's an error
        $formData = $_POST;
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
                    <i class="bi bi-plus-circle me-2"></i>Add Stock Movement
                </h1>
                <p class="text-muted mb-0">Record stock in or stock out transactions</p>
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

        <!-- Form Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-plus-circle text-primary me-2"></i>Stock Movement Details
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
                                            <?= isset($formData['item_id']) && $formData['item_id'] == $item['id'] ? 'selected' : '' ?>>
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
                                <option value="in" <?= isset($formData['change_type']) && $formData['change_type'] == 'in' ? 'selected' : '' ?>>
                                    Stock In (Increase)
                                </option>
                                <option value="out" <?= isset($formData['change_type']) && $formData['change_type'] == 'out' ? 'selected' : '' ?>>
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
                                value="<?= isset($formData['quantity_changed']) ? htmlspecialchars($formData['quantity_changed']) : '' ?>"
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
                                required><?= isset($formData['reason']) ? htmlspecialchars($formData['reason']) : '' ?></textarea>
                            <div class="invalid-feedback">Please provide a reason for the stock movement.</div>
                            <small class="form-text text-muted">Examples: Purchase order received, Sale to customer, Damaged goods, etc.</small>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-check-circle me-2"></i>Record Movement
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
