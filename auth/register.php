<?php
require "../includes/config.php";

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username2 = $_POST['username'];
  $password = $_POST['password'];
  $role = $_POST['role'];
  if (isset($username2, $password, $role) && !empty($username2) && !empty($password) && !empty($role)) {

    // Check if username already exists
    try {
      $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
      $stmt->execute([$username2]);
      if ($stmt->fetch()) {
        $error = "Username already exists. Please choose a different username.";
      } else {
        // Username is available, proceed with registration
        if (registerUser($username2, $password, $role)) {
          header("Location: ./login.php?success=Registration successful! Please login.");
          exit;
        } else {
          $error = "Registration failed. Please try again.";
        }
      }
    } catch (Exception $e) {
      $error = "Database error. Please try again later.";
    }
  } else {
    $error = "All fields are required.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Register - Inventory System</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-light">
  <div class="container">
    <div class="row justify-content-center mt-5">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h4 class="card-title text-center mb-4 text-primary fw-bold">Create Account</h4>

            <?php if (isset($_GET['error'])): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <form method="POST">
              <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
              <?php endif; ?>

              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" 
                       name="username" 
                       id="username" 
                       class="form-control <?= isset($error) && strpos($error, 'Username') !== false ? 'is-invalid' : '' ?>" 
                       value="<?= isset($username2) ? htmlspecialchars($username2) : '' ?>"
                       minlength="4" 
                       maxlength="50" 
                       required>
                <div class="invalid-feedback">
                  <?= isset($error) && strpos($error, 'Username') !== false ? htmlspecialchars($error) : 'Username must be 4-50 characters.' ?>
                </div>
                <small class="form-text text-muted">Username must be 4-50 characters long</small>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" 
                         name="password" 
                         id="password" 
                         class="form-control" 
                         minlength="6" 
                         required>
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                    <i class="bi bi-eye" id="toggleIcon"></i>
                  </button>
                </div>
                <div class="invalid-feedback">Password must be at least 6 characters.</div>
                <small class="form-text text-muted">Minimum 6 characters required</small>
              </div>

              <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-select" required>
                  <option value="" disabled <?= !isset($role) ? 'selected' : '' ?>>Select role</option>
                  <option value="admin" <?= isset($role) && $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                  <option value="staff" <?= isset($role) && $role === 'staff' ? 'selected' : '' ?>>Staff</option>
                </select>
                <div class="invalid-feedback">Please select a role.</div>
                <small class="form-text text-muted">Admin has full access, Staff has limited access</small>
              </div>

              <button type="submit" class="btn btn-primary w-100 py-2 mb-3 fw-bold">Register</button>

              <div class="text-center">
                <a href="login.php" class="text-decoration-none">Already have an account? Login</a>
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
      const icon = document.getElementById("toggleIcon");
      
      if (pwd.type === "password") {
        pwd.type = "text";
        icon.className = "bi bi-eye-slash";
      } else {
        pwd.type = "password";
        icon.className = "bi bi-eye";
      }
    }

    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form');
      const username = document.getElementById('username');
      const password = document.getElementById('password');
      
      // Real-time username validation
      username.addEventListener('input', function() {
        if (this.value.length < 4) {
          this.setCustomValidity('Username must be at least 4 characters long');
        } else if (this.value.length > 50) {
          this.setCustomValidity('Username must be no more than 50 characters long');
        } else {
          this.setCustomValidity('');
        }
      });
      
      // Real-time password validation
      password.addEventListener('input', function() {
        if (this.value.length < 6) {
          this.setCustomValidity('Password must be at least 6 characters long');
        } else {
          this.setCustomValidity('');
        }
      });
      
      // Form submission validation
      form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        form.classList.add('was-validated');
      });
    });
  </script>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>