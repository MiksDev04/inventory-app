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
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, contact_email, contact_phone, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");

        $stmt->execute([
            $_POST['name'],
            $_POST['contact_person'],
            $_POST['contact_email'],
            $_POST['contact_phone']
        ]);

        header("Location: index.php?success=Supplier created successfully");
        exit;
    } catch (Exception $e) {
        $error = "Error creating supplier: " . $e->getMessage();
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
                    <i class="bi bi-building me-2"></i>Create New Supplier
                </h1>
                <p class="text-muted mb-0">Add a new supplier to your database</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Suppliers
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
                    <i class="bi bi-plus-circle text-primary me-2"></i>Supplier Details
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <!-- Supplier Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-medium">
                                <i class="bi bi-building me-1"></i>Supplier Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="name"
                                id="name"
                                class="form-control"
                                placeholder="e.g., ABC Electronics Ltd."
                                value="<?= isset($formData['name']) ? htmlspecialchars($formData['name']) : '' ?>"
                                required>
                            <div class="invalid-feedback">Please provide a supplier name.</div>
                        </div>

                        <!-- Contact Person -->
                        <div class="col-md-6">
                            <label for="contact_person" class="form-label fw-medium">
                                <i class="bi bi-person me-1"></i>Contact Person <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="contact_person"
                                id="contact_person"
                                class="form-control"
                                placeholder="e.g., John Smith"
                                value="<?= isset($formData['contact_person']) ? htmlspecialchars($formData['contact_person']) : '' ?>"
                                required>
                            <div class="invalid-feedback">Please provide a contact person name.</div>
                        </div>

                        <!-- Contact Email -->
                        <div class="col-md-6">
                            <label for="contact_email" class="form-label fw-medium">
                                <i class="bi bi-envelope me-1"></i>Contact Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                name="contact_email"
                                id="contact_email"
                                class="form-control"
                                placeholder="e.g., john@abcelectronics.com"
                                value="<?= isset($formData['contact_email']) ? htmlspecialchars($formData['contact_email']) : '' ?>"
                                required>
                            <div class="invalid-feedback">Please provide a valid email address.</div>
                        </div>

                        <!-- Contact Phone -->
                        <div class="col-md-6">
                            <label for="contact_phone" class="form-label fw-medium">
                                <i class="bi bi-telephone me-1"></i>Contact Phone <span class="text-danger">*</span>
                            </label>
                            <input type="tel"
                                name="contact_phone"
                                id="contact_phone"
                                class="form-control"
                                placeholder="e.g., +1-555-123-4567"
                                value="<?= isset($formData['contact_phone']) ? htmlspecialchars($formData['contact_phone']) : '' ?>"
                                required>
                            <div class="invalid-feedback">Please provide a valid phone number.</div>
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
                                    <i class="bi bi-check-circle me-2"></i>Create Supplier
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
</script>

<?php
include '../includes/footer.php';
?>