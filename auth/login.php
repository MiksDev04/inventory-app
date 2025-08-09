<?php
require "../includes/config.php";

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  if (isset($username, $password) && loginUser($username, $password)) {
    header("Location: ../dashboard/index.php");
    exit;
  } else {
    $error = "Invalid credentials";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Inventory System</title>
  <!-- Bootstrap CSS only -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
  <div class="container">
    <div class="row justify-content-center mt-5">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h4 class="card-title text-center mb-4 text-primary fw-bold">Login to Inventory</h4>
            
            <?php if (isset($_GET['error'])): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            
            <form method="POST">
              <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
              <?php endif; ?>
              
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
                <div class="invalid-feedback">Username is required (4-50 characters).</div>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" name="password" id="password" class="form-control" required>
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
                <div class="invalid-feedback">Password must be at least 6 characters.</div>
              </div>

              <button type="submit" class="btn btn-primary w-100 py-2 mb-3 fw-bold">Login</button>
              
              <div class="text-center">
                <a href="./register.php" class="text-decoration-none">Don't have an account? Register</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const pwd = document.getElementById("password");
      pwd.type = pwd.type === "password" ? "text" : "password";
    }
  </script>
  
  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>