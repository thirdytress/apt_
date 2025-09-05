<?php
session_start();
if (!isset($_SESSION['TenantID'])) {
    header("Location: tenant_login.php");
    exit();
}
$tenantID = $_SESSION['TenantID'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Lease - ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('apartment-bg.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    .content {
      flex: 1;
    }
    .navbar {
      background-color: rgba(0, 33, 71, 0.95);
      padding: 12px 0;
    }
    .navbar-brand {
      font-weight: bold;
      font-size: 1.4rem;
      color: #00bfa5 !important;
    }
    .navbar-nav .nav-link {
      color: #fff !important;
      font-weight: 500;
      margin-left: 15px;
    }
    .btn-logout {
      background-color: #dc3545;
      color: #fff;
      border: none;
      padding: 6px 14px;
      border-radius: 6px;
      transition: background 0.3s;
    }
    .btn-logout:hover {
      background-color: #b02a37;
    }
    .card-custom {
      background: rgba(255, 255, 255, 0.92);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.25);
    }
    footer {
      background: rgba(0, 33, 71, 0.95);
      color: #fff;
      text-align: center;
      padding: 12px 0;
      font-size: 14px;
      margin-top: auto;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="tenant_dashboard.php">ApartmentHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="apartments.php">Apartments</a></li>
        <li class="nav-item"><a class="nav-link" href="tenant_register.php">Register</a></li>
        <li class="nav-item"><a class="nav-link" href="tenant_login.php">Login</a></li>
        <li class="nav-item">
          <form method="post" action="tenant_logout.php" class="d-inline">
            <button type="submit" class="btn btn-logout">Logout</button>
          </form>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Content -->
<div class="container content my-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card-custom text-center">
        <h3 class="mb-3">My Lease</h3>
        <?php
        // Example placeholder - replace with your backend lease query
        echo "<p>No lease record found. Please contact your apartment manager.</p>";
        ?>
        <a href="tenant_dashboard.php" class="btn btn-secondary mt-3">← Back to Dashboard</a>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<footer>
  © 2025 ApartmentHub. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
