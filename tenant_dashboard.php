<?php
// Keep your backend logic here (session, db, queries, etc.)
include('header.php');
?>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
  body {
    background-color: #f4f6f9;
    font-family: 'Inter', sans-serif;
  }

  /* Sidebar */
  .sidebar {
    min-height: 100vh;
    background: #1f2937;
    color: #fff;
    padding: 20px 15px;
  }
  .sidebar h5 {
    color: #93c5fd;
    margin-bottom: 20px;
    font-weight: 700;
  }
  .sidebar .nav-link {
    color: #d1d5db;
    border-radius: 8px;
    margin-bottom: 6px;
    transition: all 0.3s ease-in-out;
  }
  .sidebar .nav-link.active,
  .sidebar .nav-link:hover {
    background: #2563eb;
    color: #fff;
    transform: translateX(5px);
  }

  /* Content */
  .content {
    padding: 25px;
  }

  /* Card Styling */
  .card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease-in-out;
  }
  .card:hover {
    transform: translateY(-6px) scale(1.02);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  }
  .card-header {
    border: none;
    font-weight: 600;
    color: #fff;
    border-radius: 12px 12px 0 0;
  }

  /* Footer */
  footer {
    background: #111827;
    color: #fff;
    padding: 12px;
    text-align: center;
    margin-top: 30px;
  }
</style>

<div class="container-fluid">
  <div class="row">
    <!-- SIDEBAR -->
    <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
      <h5>🏢 Tenant Menu</h5>
      <ul class="nav flex-column">
        <li><a class="nav-link <?= $page==='overview'?'active':'' ?>" href="?page=overview">🏠 Overview</a></li>
        <li><a class="nav-link <?= $page==='notifications'?'active':'' ?>" href="?page=notifications">🔔 Notifications</a></li>
        <li><a class="nav-link <?= $page==='lease'?'active':'' ?>" href="?page=lease">📄 Lease Info</a></li>
        <li><a class="nav-link <?= $page==='payments'?'active':'' ?>" href="?page=payments">💳 Payments</a></li>
        <li><a class="nav-link <?= $page==='maintenance'?'active':'' ?>" href="?page=maintenance">🛠️ Maintenance</a></li>
        <li><a class="nav-link <?= $page==='parking'?'active':'' ?>" href="?page=parking">🚗 Parking</a></li>
        <li><a class="nav-link <?= $page==='bills'?'active':'' ?>" href="?page=bills">💡 Utility Bills</a></li>
        <li><a class="nav-link" href="update_tenant_profile.php">✏️ Update My Info</a></li>
      </ul>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="col-md-9 ms-sm-auto col-lg-10 content">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
        <h2>Welcome, <?= htmlspecialchars($tenant['FirstName'] ?? 'Tenant') ?> 👋</h2>
      </div>

      <!-- Dashboard Cards Example -->
      <div class="row g-4">
        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-header bg-primary">🔔 Notifications</div>
            <div class="card-body">
              <h4>5</h4>
              <p>Unread messages</p>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-header bg-danger">💡 Unpaid Bills</div>
            <div class="card-body">
              <h4>2</h4>
              <p>Pending bills</p>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-header bg-success">💳 Payments</div>
            <div class="card-body">
              <h4>₱12,000</h4>
              <p>Last payment</p>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-header bg-warning">🚗 Parking</div>
            <div class="card-body">
              <h4>Slot A3</h4>
              <p>Active Reservation</p>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<footer>
  &copy; <?= date("Y") ?> ApartmentHub. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
