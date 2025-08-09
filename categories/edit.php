<?php
session_start();
// Include configuration and functions
include '../includes/config.php';
// Check if user is logged in
if (!isLogIn()) {
    header("Location: ../auth/login.php?error=Please log in first");
    exit;
}

// Get category ID from URL
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$category_id) {
    header("Location: index.php?error=Invalid category ID");
    exit;
}

// Fetch category data
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        header("Location: index.php?error=Category not found");
        exit;
    }
} catch (Exception $e) {
    header("Location: index.php?error=Error fetching category: " . urlencode($e->getMessage()));
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, updated_at = NOW() WHERE id = ?");

        $stmt->execute([
            $_POST['name'],
            $category_id
        ]);

        header("Location: index.php?success=Category updated successfully");
        exit;
    } catch (Exception $e) {
        $error = "Error updating category: " . $e->getMessage();
        // Keep form data only when there's an error
        $formData = $_POST;
    }
}

// Use category data as default form data if no error occurred
if (!isset($formData)) {
    $formData = $category;
}

include '../includes/header.php';
?>

<div class="col">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-primary fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>Edit Category
                </h1>
                <p class="text-muted mb-0">Update category information</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Categories
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
                    <i class="bi bi-pencil-square text-primary me-2"></i>Category Details
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <!-- Category Name -->
                        <div class="col-12">
                            <label for="name" class="form-label fw-medium">
                                <i class="bi bi-tag me-1"></i>Category Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="name"
                                id="name"
                                class="form-control"
                                placeholder="e.g., Electronics, Books, Clothing..."
                                value="<?= isset($formData['name']) ? htmlspecialchars($formData['name']) : '' ?>"
                                required>
                            <div class="invalid-feedback">Please provide a category name.</div>
                            <small class="form-text text-muted">Choose a descriptive name for easy item organization</small>
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
                                    <i class="bi bi-check-circle me-2"></i>Update Category
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
