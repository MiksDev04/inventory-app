<?php
session_start();
// Include configuration and functions
include '../includes/config.php';
// Check if user is logged in
if (!isLogIn()) {
    header("Location: ../auth/login.php?error=Please log in first");
    exit;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM suppliers 
                            WHERE name LIKE ? OR contact_person LIKE ? OR contact_email LIKE ? OR contact_phone LIKE ?
                            ORDER BY created_at DESC");
    $stmt->execute(['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
} else {
    $stmt = $pdo->prepare("SELECT * FROM suppliers ORDER BY created_at DESC");
    $stmt->execute();
}
$suppliers = $stmt->fetchAll();
include '../includes/header.php';
?>

<div class="col">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-primary fw-bold">
                    <i class="bi bi-building me-2"></i>Suppliers
                </h1>
                <p class="text-muted mb-0">Manage your suppliers and contact information</p>
            </div>
            <div>
                <a href="create.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add New Supplier
                </a>
            </div>
        </div>

        <!-- Search and Actions -->
        <div class="mb-4">
            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="search" 
                           name="search" 
                           placeholder="Search supplier names and contacts..." 
                           class="form-control" 
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
        <!-- Search Results Info -->
        <?php if (!empty($search)): ?>
            <div class="alert alert-info border-0 shadow-sm mb-4">
                <i class="bi bi-info-circle me-2"></i>
                Showing <?= count($suppliers) ?> result(s) for "<strong><?= htmlspecialchars($search) ?></strong>"
                <a href="index.php" class="alert-link ms-2">Clear search</a>
            </div>
        <?php endif; ?>

        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>
        
        <!-- Suppliers Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i>Suppliers List
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($suppliers)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No suppliers found</h5>
                        <p class="text-muted">Start by adding your first supplier to the system.</p>
                        <a href="create.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add New Supplier
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact Person</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <tr>
                                        <td class="fw-medium"><?= htmlspecialchars($supplier['name']) ?></td>
                                        <td><?= htmlspecialchars($supplier['contact_person']) ?></td>
                                        <td>
                                            <?php if ($supplier['contact_email']): ?>
                                                <a href="mailto:<?= htmlspecialchars($supplier['contact_email']) ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($supplier['contact_email']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($supplier['contact_phone']): ?>
                                                <a href="tel:<?= htmlspecialchars($supplier['contact_phone']) ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($supplier['contact_phone']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M d, Y', strtotime($supplier['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="edit.php?id=<?= $supplier['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <a href="delete.php?id=<?= $supplier['id'] ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php
include '../includes/footer.php';
?>