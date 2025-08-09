<?php
session_start();
// Include configuration and functions
include '../includes/config.php';

// Check if user is logged in
if (!isLogIn()) {
    header("Location: ../auth/login.php?error=Please log in first");
    exit;
}

// Get supplier ID from URL
$supplier_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$supplier_id) {
    header("Location: index.php?error=Invalid supplier ID");
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Check if supplier is referenced in items table
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE supplier_id = ?");
        $stmt->execute([$supplier_id]);
        $item_count = $stmt->fetchColumn();

        if ($item_count > 0) {
            header("Location: index.php?error=Cannot delete supplier. It is referenced by $item_count item(s).");
            exit;
        }

        // Delete the supplier
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->execute([$supplier_id]);

        header("Location: index.php?success=Supplier deleted successfully");
        exit;
    } catch (Exception $e) {
        $error = "Error deleting supplier: " . $e->getMessage();
    }
}

// Fetch supplier data for display
try {
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch();
    
    if (!$supplier) {
        header("Location: index.php?error=Supplier not found");
        exit;
    }
} catch (Exception $e) {
    header("Location: index.php?error=Error fetching supplier: " . urlencode($e->getMessage()));
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
                    <i class="bi bi-trash me-2"></i>Delete Supplier
                </h1>
                <p class="text-muted mb-0">Permanently remove supplier from the system</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Suppliers
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
                    <strong>Warning:</strong> This action cannot be undone. The supplier will be permanently removed from the system.
                </div>

                <!-- Supplier Details -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Supplier Name</label>
                        <p class="fw-bold"><?= htmlspecialchars($supplier['name']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Contact Person</label>
                        <p class="fw-bold"><?= htmlspecialchars($supplier['contact_person']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Contact Email</label>
                        <p><?= htmlspecialchars($supplier['contact_email']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Contact Phone</label>
                        <p><?= htmlspecialchars($supplier['contact_phone']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Created</label>
                        <p><?= date('M d, Y g:i A', strtotime($supplier['created_at'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Last Updated</label>
                        <p><?= date('M d, Y g:i A', strtotime($supplier['updated_at'])) ?></p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            <a href="edit.php?id=<?= $supplier['id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil me-2"></i>Edit Instead
                            </a>
                            <form method="POST" class="d-inline">
                                <button type="submit" name="confirm_delete" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash me-2"></i>Yes, Delete Supplier
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
