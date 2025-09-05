<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['TenantID'])) {
    header("Location: tenant_login.php");
    exit();
}

$tenantID = $_SESSION['TenantID'];

// Get recent payments
$stmt = $pdo->prepare("SELECT * FROM Payments WHERE TenantID = ? ORDER BY Pay_Date DESC");
$stmt->execute([$tenantID]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('header.php'); ?>

<main class="content fade-in">
    <div class="container mt-5">
        <h2><i class="fas fa-receipt"></i> Your Payment History</h2>

        <?php if ($payments): ?>
            <div class="table-responsive">
                <table class="table table-hover mt-3 shadow-sm">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar-alt"></i> Payment Date</th>
                            <th><i class="fas fa-money-bill-wave"></i> Amount</th>
                            <th><i class="fas fa-credit-card"></i> Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $pay): ?>
                            <tr>
                                <td><?= htmlspecialchars($pay['Pay_Date']) ?></td>
                                <td><span class="badge bg-success">₱<?= number_format($pay['Amount'], 2) ?></span></td>
                                <td><?= htmlspecialchars($pay['Pay_Method']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center mt-3 shadow-sm">
                <i class="fas fa-info-circle"></i> No payments recorded yet.
            </div>
        <?php endif; ?>

        <!-- 🔙 Back Button -->
        <div class="mt-4 text-center">
            <a href="tenant_dashboard.php" class="btn btn-gradient">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
        </div>
    </div>
</main>

<style>
  /* Smooth fade-in effect */
  .fade-in {
    animation: fadeIn 1.2s ease-in-out;
  }

  @keyframes fadeIn {
    from {opacity: 0; transform: translateY(20px);}
    to {opacity: 1; transform: translateY(0);}
  }

  /* Page Layout Fix */
  html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
  }

  main.content {
    flex: 1; /* Push footer to bottom */
  }

  body {
    background: linear-gradient(135deg, #3498db, #2c3e50);
    font-family: 'Poppins', sans-serif;
  }

  /* Container Styling */
  .container {
    background: #fff;
    border-radius: 16px;
    padding: 2rem;
    margin: 2rem auto;
    max-width: 900px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
  }

  /* Heading Styling */
  h2 {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
    text-align: center;
  }

  h2 i {
    color: #3498db;
    margin-right: 10px;
  }

  /* Table Styling */
  .table {
    border-radius: 12px;
    overflow: hidden;
  }

  .table thead {
    background: linear-gradient(135deg, #2c3e50, #3498db);
    color: #fff;
  }

  .table thead th {
    border: none;
    padding: 1rem;
    text-align: center;
  }

  .table tbody td {
    padding: 1rem;
    text-align: center;
    vertical-align: middle;
  }

  .table tbody tr:hover {
    background-color: #f2f6fa;
    transition: 0.3s ease;
    transform: scale(1.01);
  }

  /* Badge for Amount */
  .badge {
    font-size: 0.95rem;
    padding: 8px 12px;
    border-radius: 8px;
  }

  /* Gradient Button */
  .btn-gradient {
    background: linear-gradient(135deg, #3498db, #2c3e50);
    border: none;
    color: white;
    padding: 10px 25px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
  }

  .btn-gradient:hover {
    background: linear-gradient(135deg, #2c3e50, #3498db);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
  }
</style>

<!-- Load FontAwesome Icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<?php include('footer.php'); ?>
