<?php
session_start();
// Include configuration and functions
include '../includes/config.php';
// Check if user is logged in
if (!isLogIn()) {
    header("Location: ../auth/login.php?error=Please log in first");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO items (sku, name, description, category_id, supplier_id, quantity, unit_price, reorder_level, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

        $stmt->execute([
            $_POST['sku'],
            $_POST['name'],
            $_POST['description'],
            $_POST['category_id'],
            $_POST['supplier_id'],
            $_POST['quantity'],
            $_POST['unit_price'],
            $_POST['reorder_level']
        ]);

        header("Location: index.php?success=Item created successfully");
        exit;
    } catch (Exception $e) {
        $error = "Error creating item: " . $e->getMessage();
        // Keep form data only when there's an error
        $formData = $_POST;
    }
}

// Get categories for dropdown
$stmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Get suppliers for dropdown
$stmt = $pdo->prepare("SELECT id, name FROM suppliers ORDER BY name");
$stmt->execute();
$suppliers = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="col">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-primary fw-bold">
                    <i class="bi bi-box-seam me-2"></i>Create New Item
                </h1>
                <p class="text-muted mb-0">Add a new item to your inventory</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Inventory
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
                    <i class="bi bi-plus-circle text-primary me-2"></i>Item Details
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <!-- SKU -->
                        <div class="col-md-6">
                            <label for="sku" class="form-label fw-medium">
                                <i class="bi bi-upc-scan me-1"></i>SKU <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="sku"
                                id="sku"
                                class="form-control"
                                placeholder="e.g., ELEC-001"
                                value="<?= isset($formData['sku']) ? htmlspecialchars($formData['sku']) : '' ?>"
                                required>
                            <div class="invalid-feedback">Please provide a valid SKU.</div>
                        </div>

                        <!-- Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-medium">
                                <i class="bi bi-tag me-1"></i>Item Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="name"
                                id="name"
                                class="form-control"
                                placeholder="e.g., iPhone 14 Pro"
                                value="<?= isset($formData['name']) ? htmlspecialchars($formData['name']) : '' ?>"
                                required>
                            <div class="invalid-feedback">Please provide an item name.</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="description" class="form-label fw-medium">
                                <i class="bi bi-file-text me-1"></i>Description
                            </label>
                            <textarea required name="description"
                                id="description"
                                class="form-control"
                                rows="3"
                                placeholder="Detailed description of the item..."><?= isset($formData['description']) ? htmlspecialchars($formData['description']) : '' ?></textarea>
                            <div class="invalid-feedback">Please provide a description.</div>

                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-medium">
                                <i class="bi bi-tags me-1"></i>Category <span class="text-danger">*</span>
                            </label>
                            <select name="category_id" id="category_id" class="form-select" required>
                                <option value="">Select a category...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"
                                        <?= isset($formData['category_id']) && $formData['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>

                        <!-- Supplier -->
                        <div class="col-md-6">
                            <label for="supplier_id" class="form-label fw-medium">
                                <i class="bi bi-building me-1"></i>Supplier <span class="text-danger">*</span>
                            </label>
                            <select name="supplier_id" id="supplier_id" class="form-select" required>
                                <option value="">Select a supplier...</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= $supplier['id'] ?>"
                                        <?= isset($formData['supplier_id']) && $formData['supplier_id'] == $supplier['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($supplier['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a supplier.</div>
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-4">
                            <label for="quantity" class="form-label fw-medium">
                                <i class="bi bi-stack me-1"></i>Initial Quantity <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                name="quantity"
                                id="quantity"
                                class="form-control"
                                min="0"
                                placeholder="0"
                                value="<?= isset($formData['quantity']) ? htmlspecialchars($formData['quantity']) : '' ?>"
                                required>
                            <div class="invalid-feedback">Please provide a valid quantity.</div>
                        </div>

                        <!-- Unit Price -->
                        <div class="col-md-4">
                            <label for="unit_price" class="form-label fw-medium">
                                <i class="bi bi-currency-dollar me-1"></i>Unit Price <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number"
                                    name="unit_price"
                                    id="unit_price"
                                    class="form-control"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    value="<?= isset($formData['unit_price']) ? htmlspecialchars($formData['unit_price']) : '' ?>"
                                    required>
                                <div class="invalid-feedback">Please provide a valid price.</div>
                            </div>
                        </div>

                        <!-- Reorder Level -->
                        <div class="col-md-4">
                            <label for="reorder_level" class="form-label fw-medium">
                                <i class="bi bi-exclamation-triangle me-1"></i>Reorder Level <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                name="reorder_level"
                                id="reorder_level"
                                class="form-control"
                                min="0"
                                placeholder="10"
                                value="<?= isset($formData['reorder_level']) ? htmlspecialchars($formData['reorder_level']) : '' ?>"
                                required>
                            <div class="invalid-feedback">Please provide a reorder level.</div>
                            <small class="form-text text-muted">Alert when stock reaches this level</small>
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
                                    <i class="bi bi-check-circle me-2"></i>Create Item
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

    // Auto-generate SKU suggestion
    document.getElementById('category_id').addEventListener('change', function() {
        const categorySelect = this;
        const skuInput = document.getElementById('sku');

        if (categorySelect.value && !skuInput.value) {
            const categoryText = categorySelect.options[categorySelect.selectedIndex].text;
            const categoryCode = categoryText.substring(0, 4).toUpperCase();
            const timestamp = Date.now().toString().slice(-4);
            skuInput.value = categoryCode + '-' + timestamp;
        }
    });
</script>

<?php
include '../includes/footer.php';
?>