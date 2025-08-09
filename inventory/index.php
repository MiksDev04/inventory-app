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
    $stmt = $pdo->prepare("SELECT 
                            i.*, 
                            s.name AS supplier_name, 
                            c.name AS category_name 
                            FROM items i 
                            JOIN suppliers s ON i.supplier_id = s.id 
                            JOIN categories c ON i.category_id = c.id
                            WHERE i.name LIKE ? OR i.sku LIKE ? OR i.description LIKE ? OR s.name LIKE ? OR c.name LIKE ?
                            ORDER BY i.created_at DESC");
    $stmt->execute(['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
} else {
    $stmt = $pdo->prepare("SELECT 
                            i.*, 
                            s.name AS supplier_name, 
                            c.name AS category_name 
                            FROM items i 
                            JOIN suppliers s ON i.supplier_id = s.id 
                            JOIN categories c ON i.category_id = c.id
                            ORDER BY i.created_at DESC");
    $stmt->execute();
}
$items = $stmt->fetchAll();
include '../includes/header.php';
?>

<div class="col">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-primary fw-bold">
                    <i class="bi bi-box-seam me-2"></i>Inventory
                </h1>
                <p class="text-muted mb-0">Manage your inventory items and stock levels</p>
            </div>
            <div>
                <a href="create.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add New Item
                </a>
            </div>
        </div>

        <!-- Search and Actions -->
        <div class="mb-4">
            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="search" 
                           name="search" 
                           placeholder="Search items, SKU, category, supplier..." 
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
                Showing <?= count($items) ?> result(s) for "<strong><?= htmlspecialchars($search) ?></strong>"
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

        <!-- Items Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i>Items List
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($items)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No items found</h5>
                        <p class="text-muted">Start by adding your first item to the inventory.</p>
                        <a href="create.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add New Item
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Supplier</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Reorder Level</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="fw-medium"><?= htmlspecialchars($item['sku']) ?></td>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><?= htmlspecialchars($item['description']) ?></td>
                                        <td><?= htmlspecialchars($item['category_name']) ?></td>
                                        <td><?= htmlspecialchars($item['supplier_name']) ?></td>
                                        <td><?= number_format($item['quantity']) ?></td>
                                        <td>$<?= number_format($item['unit_price'], 2) ?></td>
                                        <td><?= number_format($item['reorder_level']) ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <a href="delete.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" title="Delete">
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