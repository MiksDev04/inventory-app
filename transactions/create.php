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
    $stmt = $pdo->prepare("SELECT id, name, sku, quantity, unit_price FROM items ORDER BY name ASC");
    $stmt->execute();
    $items = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Error fetching items: " . $e->getMessage();
    $items = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Insert transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (type, item_id, qty, price, date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['type'],
            $_POST['item_id'],
            $_POST['qty'],
            $_POST['price'],
            $_POST['date']
        ]);

        header("Location: index.php?success=Transaction recorded successfully");
        exit;
    } catch (Exception $e) {
        $error = "Error recording transaction: " . $e->getMessage();
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
                    <i class="bi bi-plus-circle me-2"></i>Add Transaction
                </h1>
                <p class="text-muted mb-0">Record a new purchase or sale transaction</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Transactions
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
                    <i class="bi bi-plus-circle text-primary me-2"></i>Transaction Details
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <!-- Transaction Type -->
                        <div class="col-md-6">
                            <label for="type" class="form-label fw-medium">
                                <i class="bi bi-arrow-left-right me-1"></i>Transaction Type <span class="text-danger">*</span>
                            </label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="">Select transaction type...</option>
                                <option value="purchase" <?= isset($formData['type']) && $formData['type'] == 'purchase' ? 'selected' : '' ?>>
                                    <i class="bi bi-cart-plus"></i> Purchase (Buy from supplier)
                                </option>
                                <option value="sale" <?= isset($formData['type']) && $formData['type'] == 'sale' ? 'selected' : '' ?>>
                                    <i class="bi bi-cart-dash"></i> Sale (Sell to customer)
                                </option>
                            </select>
                            <div class="invalid-feedback">Please select a transaction type.</div>
                        </div>

                        <!-- Item Selection -->
                        <div class="col-md-6">
                            <label for="item_id" class="form-label fw-medium">
                                <i class="bi bi-box-seam me-1"></i>Item <span class="text-danger">*</span>
                            </label>
                            <select name="item_id" id="item_id" class="form-select" required onchange="updateItemInfo()">
                                <option value="">Select an item...</option>
                                <?php foreach ($items as $item): ?>
                                    <option value="<?= $item['id'] ?>" 
                                            data-sku="<?= htmlspecialchars($item['sku']) ?>"
                                            data-quantity="<?= $item['quantity'] ?>"
                                            data-price="<?= $item['unit_price'] ?>"
                                            <?= isset($formData['item_id']) && $formData['item_id'] == $item['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($item['name']) ?> (SKU: <?= htmlspecialchars($item['sku']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select an item.</div>
                            <small id="itemInfo" class="form-text text-muted"></small>
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-6">
                            <label for="qty" class="form-label fw-medium">
                                <i class="bi bi-hash me-1"></i>Quantity <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                name="qty"
                                id="qty"
                                class="form-control"
                                min="1"
                                placeholder="Enter quantity"
                                value="<?= isset($formData['qty']) ? htmlspecialchars($formData['qty']) : '' ?>"
                                required
                                onchange="calculateTotal()">
                            <div class="invalid-feedback">Please provide a valid quantity.</div>
                        </div>

                        <!-- Price per Unit -->
                        <div class="col-md-6">
                            <label for="price" class="form-label fw-medium">
                                <i class="bi bi-currency-dollar me-1"></i>Price per Unit <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number"
                                    name="price"
                                    id="price"
                                    class="form-control"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    value="<?= isset($formData['price']) ? htmlspecialchars($formData['price']) : '' ?>"
                                    required
                                    onchange="calculateTotal()">
                            </div>
                            <div class="invalid-feedback">Please provide a valid price.</div>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <label for="date" class="form-label fw-medium">
                                <i class="bi bi-calendar me-1"></i>Transaction Date <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local"
                                name="date"
                                id="date"
                                class="form-control"
                                value="<?= isset($formData['date']) ? htmlspecialchars($formData['date']) : date('Y-m-d\TH:i') ?>"
                                required>
                            <div class="invalid-feedback">Please provide a valid date.</div>
                        </div>

                        <!-- Total Amount (Calculated) -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">
                                <i class="bi bi-calculator me-1"></i>Total Amount
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text"
                                    id="total"
                                    class="form-control"
                                    placeholder="0.00"
                                    readonly>
                            </div>
                            <small class="form-text text-muted">Automatically calculated: Quantity × Price</small>
                        </div>
                    </div>

                    <!-- Transaction Summary -->
                    <div id="transactionSummary" class="alert alert-info border-0 mt-4" style="display: none;">
                        <h6 class="alert-heading mb-2">
                            <i class="bi bi-info-circle me-2"></i>Transaction Summary
                        </h6>
                        <div id="summaryContent"></div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-check-circle me-2"></i>Record Transaction
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Form Validation and Calculations Script -->
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

    // Update item information display
    function updateItemInfo() {
        const itemSelect = document.getElementById('item_id');
        const itemInfoElement = document.getElementById('itemInfo');
        const priceInput = document.getElementById('price');
        
        if (itemSelect.value) {
            const selectedOption = itemSelect.options[itemSelect.selectedIndex];
            const currentQuantity = selectedOption.getAttribute('data-quantity');
            const sku = selectedOption.getAttribute('data-sku');
            const itemPrice = selectedOption.getAttribute('data-price');
            
            itemInfoElement.innerHTML = `Current stock: <strong>${currentQuantity}</strong> units • SKU: ${sku}`;
            
            // Auto-fill price if available
            if (itemPrice && itemPrice > 0) {
                priceInput.value = parseFloat(itemPrice).toFixed(2);
                calculateTotal();
            }
        } else {
            itemInfoElement.innerHTML = '';
        }
    }

    // Calculate total amount
    function calculateTotal() {
        const qty = document.getElementById('qty').value;
        const price = document.getElementById('price').value;
        const totalElement = document.getElementById('total');
        const summaryElement = document.getElementById('transactionSummary');
        const summaryContent = document.getElementById('summaryContent');
        
        if (qty && price) {
            const total = parseFloat(qty) * parseFloat(price);
            totalElement.value = total.toFixed(2);
            
            // Update summary
            const itemSelect = document.getElementById('item_id');
            const typeSelect = document.getElementById('type');
            
            if (itemSelect.value && typeSelect.value) {
                const itemName = itemSelect.options[itemSelect.selectedIndex].text.split(' (SKU:')[0];
                const type = typeSelect.options[typeSelect.selectedIndex].text;
                
                summaryContent.innerHTML = `
                    <div class="row g-2 small">
                        <div class="col-md-3"><strong>Type:</strong> ${type}</div>
                        <div class="col-md-3"><strong>Item:</strong> ${itemName}</div>
                        <div class="col-md-3"><strong>Quantity:</strong> ${qty} units</div>
                        <div class="col-md-3"><strong>Total:</strong> $${total.toFixed(2)}</div>
                    </div>
                `;
                summaryElement.style.display = 'block';
            }
        } else {
            totalElement.value = '';
            summaryElement.style.display = 'none';
        }
    }

    // Add event listeners
    document.getElementById('type').addEventListener('change', calculateTotal);
    document.getElementById('item_id').addEventListener('change', calculateTotal);
</script>

<?php
include '../includes/footer.php';
?>
