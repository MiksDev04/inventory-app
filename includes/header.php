<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>InventoryPro Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="row m-0 p-0 overflow-x-hidden">
  <?php
  include '../includes/sidebar.php';
  ?>

  <!-- Mobile header bar -->
  <div class="d-lg-none position-fixed top-0 start-0 end-0 bg-white shadow-sm border-bottom" style="z-index: 1040; height: 60px;">
    <div class="d-flex align-items-center h-100 px-3">
      <button class="btn btn-outline-primary btn-sm me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
        <i class="bi bi-list fs-5"></i>
      </button>
      <div class="d-flex align-items-center">
        <i class="bi bi-box-seam fs-5 me-2 text-primary"></i>
        <span class="fs-5 fw-bold text-primary">InventoryPro</span>
      </div>
    </div>
  </div>

  <!-- Main content area - responsive margin -->
  <div class="col" style="margin-left: 0;" id="main-content">
    <!-- Mobile top padding to avoid overlap with header bar -->
    <div class="d-lg-none" style="height: 60px;"></div>