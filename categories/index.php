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
    $stmt = $pdo->prepare("SELECT * FROM categories 
                            WHERE name LIKE ?
                            ORDER BY created_at DESC");
    $stmt->execute(['%' . $search . '%']);
} else {
    $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY created_at DESC");
    $stmt->execute();
}
$categories = $stmt->fetchAll();
include '../includes/header.php';
?>

<div class="col">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-primary fw-bold">
                    <i class="bi bi-tags me-2"></i>Categories
                </h1>
                <p class="text-muted mb-0">Manage your item categories and organization</p>
            </div>
            <div>
                <a href="create.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add New Category
                </a>
            </div>
        </div>

        <!-- Search and Actions -->
        <div class="mb-4">
            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="search" 
                           name="search" 
                           placeholder="Search category names..." 
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
                Showing <?= count($categories) ?> result(s) for "<strong><?= htmlspecialchars($search) ?></strong>"
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
        
        <!-- Categories Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i>Categories List
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-tags text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No categories found</h5>
                        <p class="text-muted">Start by adding your first category to organize items.</p>
                        <a href="create.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add New Category
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Category Name</th>
                                    <th>Created</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td class="fw-medium"><?= htmlspecialchars($category['name']) ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M d, Y', strtotime($category['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M d, Y', strtotime($category['updated_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="edit.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <a href="delete.php?id=<?= $category['id'] ?>" 
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
