<?php

$page = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = explode('/', $page);


?>

<!-- Sidebar for large screens -->
<div class="d-none d-lg-flex flex-column p-3 bg-white shadow-sm position-fixed h-100" style="width: 230px; z-index: 1000;">
  <a href="" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none border-bottom pb-3">
    <i class="bi bi-box-seam fs-4 me-2 text-primary"></i>
    <span class="fs-4 fw-bold">InventoryPro</span>
  </a>
  <ul class="nav nav-pills flex-column mb-auto mt-3 h-100">
    <li class="nav-item">
      <a href="../dashboard/index.php" class="nav-link hover-nav <?= activeNav('dashboard') ?>" aria-current="page">
        <i class="bi bi-speedometer2 me-2"></i>
        Dashboard
      </a>
    </li>
    <li>
      <a href="../inventory/index.php" class="nav-link hover-nav <?= activeNav('inventory') ?>">
        <i class="bi bi-box-seam me-2"></i>
        Inventory
      </a>
    </li>
    <li>
      <a href="../suppliers/index.php" class="nav-link hover-nav <?= activeNav('suppliers') ?>">
        <i class="bi bi-people me-2"></i>
        Suppliers
      </a>
    </li>
    <li>
      <a href="../categories/index.php" class="nav-link hover-nav <?= activeNav('categories') ?>">
        <i class="bi bi-tags me-2"></i>
        Categories
      </a>
    </li>
    <li>
      <a href="../stocks/index.php" class="nav-link hover-nav <?= activeNav('stocks') ?>">
        <i class="bi bi-box-arrow-in-right me-2"></i>
        Stock Logs
      </a>
    </li>
    <li>
      <a href="../transactions/index.php" class="nav-link hover-nav <?= activeNav('transactions') ?>">
        <i class="bi bi-arrow-left-right me-2"></i>
        Transactions
      </a>
    </li>
   
    <li class="pt-3 border-top mt-auto">
      <div class="nav-link link-dark d-flex align-items-center justify-content-between">
        <i class="bi bi-person me-2" style="font-size: 1rem; font-weight: bold;"></i>
        <span class=" d-flex flex-column">
          <span class=" fw-medium">John Doe</span>
          <span class=" text-secondary">Admin</span>
        </span>
        <button type="button" class="btn btn-link text-danger p-0 ms-auto logout-btn" data-bs-toggle="modal" data-bs-target="#logoutModal" title="Logout">
          <i class="bi bi-power"></i>
        </button>
      </div>
    </li>
  </ul>
</div>

<!-- Offcanvas sidebar for small/medium screens -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title d-flex align-items-center" id="sidebarOffcanvasLabel">
      <i class="bi bi-box-seam fs-4 me-2 text-primary"></i>
      <span class="fs-4 fw-bold">InventoryPro</span>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-0">
    <ul class="nav nav-pills flex-column p-3">
      <li class="nav-item">
        <a href="../dashboard/index.php" class="nav-link hover-nav <?= activeNav('dashboard') ?>" aria-current="page">
          <i class="bi bi-speedometer2 me-2"></i>
          Dashboard
        </a>
      </li>
      <li>
        <a href="../inventory/index.php" class="nav-link hover-nav <?= activeNav('inventory') ?>">
          <i class="bi bi-box-seam me-2"></i>
          Inventory
        </a>
      </li>
      <li>
        <a href="../suppliers/index.php" class="nav-link hover-nav <?= activeNav('suppliers') ?>">
          <i class="bi bi-people me-2"></i>
          Suppliers
        </a>
      </li>
      <li>
        <a href="../categories/index.php" class="nav-link hover-nav <?= activeNav('categories') ?>">
          <i class="bi bi-tags me-2"></i>
          Categories
        </a>
      </li>
      <li>
        <a href="../stocks/index.php" class="nav-link hover-nav <?= activeNav('stocks') ?>">
          <i class="bi bi-box-arrow-in-right me-2"></i>
          Stock Logs
        </a>
      </li>
      <li>
        <a href="../transactions/index.php" class="nav-link hover-nav <?= activeNav('transactions') ?>">
          <i class="bi bi-arrow-left-right me-2"></i>
          Transactions
        </a>
      </li>
    </ul>
    
    <!-- User section for offcanvas -->
    <div class="mt-auto p-3 border-top">
      <div class="nav-link link-dark d-flex align-items-center justify-content-between">
        <i class="bi bi-person me-2" style="font-size: 1rem; font-weight: bold;"></i>
        <span class="d-flex flex-column">
          <span class="fw-medium">John Doe</span>
          <span class="text-secondary">Admin</span>
        </span>
        <button type="button" class="btn btn-link text-danger p-0 ms-auto logout-btn" data-bs-toggle="modal" data-bs-target="#logoutModal" title="Logout">
          <i class="bi bi-power"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="logoutModalLabel">
          <i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirm Logout
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-0">
        <div class="d-flex align-items-center mb-3">
          <div class="flex-shrink-0">
            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
              <i class="bi bi-person-x text-warning fs-4"></i>
            </div>
          </div>
          <div class="flex-grow-1 ms-3">
            <h6 class="mb-1">Are you sure you want to logout?</h6>
            <p class="text-muted mb-0 small">You will be redirected to the login page and your session will end.</p>
          </div>
        </div>
        
        <div class="alert alert-light border d-flex align-items-center" role="alert">
          <i class="bi bi-info-circle text-info me-2"></i>
          <small class="mb-0">Make sure you have saved all your work before logging out.</small>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i>Cancel
        </button>
        <a href="../auth/logout.php" class="btn btn-danger btn-sm">
          <i class="bi bi-power me-1"></i>Yes, Logout
        </a>
      </div>
    </div>
  </div>
</div>