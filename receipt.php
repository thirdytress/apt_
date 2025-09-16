<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['TenantID'])) {
    header("Location: tenant_login.php");
    exit();
}

// ✅ Ensure required POST data exists
if (!isset($_POST['bill_id'], $_POST['payment_method'])) {
    die("No payment data available.");
}

$tenantID = $_SESSION['TenantID'];
$billID   = $_POST['bill_id'];
$method   = $_POST['payment_method'] ?? 'Unknown';

// Fetch tenant info
$stmt = $pdo->prepare("SELECT * FROM tenants WHERE TenantID = ?");
$stmt->execute([$tenantID]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch bill info
$stmt = $pdo->prepare("SELECT * FROM utilitybills WHERE BillID = ? AND TenantID = ?");
$stmt->execute([$billID, $tenantID]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    die("❌ Bill not found for this tenant.");
}

// ✅ Handle uploaded receipt
$uploadedReceipt = null;
if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === 0) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $fileName = time() . "_" . basename($_FILES["receipt"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    if (move_uploaded_file($_FILES["receipt"]["tmp_name"], $targetFilePath)) {
        $uploadedReceipt = $fileName;
    }
}

// ✅ Update bill status
$pdo->prepare("UPDATE utilitybills SET Status = 'Paid' WHERE BillID = ? AND TenantID = ?")
    ->execute([$billID, $tenantID]);

// ✅ Insert into payments
$pdo->prepare("
    INSERT INTO payments (TenantID, Amount, Pay_Method, Status, Pay_Date, BillID, Receipt)
    VALUES (?, ?, ?, 'Paid', NOW(), ?, ?)
")->execute([$tenantID, $bill['Amount'], $method, $billID, $uploadedReceipt]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Receipt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .receipt-card { max-width: 700px; margin: 30px auto; }
  </style>
</head>
<body>
<div class="card receipt-card shadow">
  <div class="card-body">
    <h3 class="text-center text-primary">🏢 ApartmentHub</h3>
    <h5 class="text-center">Payment Receipt</h5>
    <hr>
    <p><b>Tenant Name:</b> <?= htmlspecialchars($tenant['FirstName'] . ' ' . $tenant['LastName']) ?></p>
    <p><b>Email:</b> <?= htmlspecialchars($tenant['Email']) ?></p>
    <p><b>Phone:</b> <?= htmlspecialchars($tenant['Phone']) ?></p>
    <hr>
    <p><b>Bill Type:</b> <?= htmlspecialchars($bill['Type']) ?></p>
    <p><b>Amount Paid:</b> ₱<?= number_format($bill['Amount'], 2) ?></p>
    <p><b>Payment Method:</b> <?= htmlspecialchars($method) ?></p>
    <p><b>Bill Date:</b> <?= htmlspecialchars($bill['BillDate']) ?></p>
    <p><b>Status:</b> Paid</p>
    <?php if ($uploadedReceipt): ?>
        <p><b>Uploaded Receipt:</b></p>
        <img src="uploads/<?= htmlspecialchars($uploadedReceipt) ?>" width="200" class="border rounded">
    <?php endif; ?>
    <hr>
    <p class="text-muted text-center">Thank you for your payment!</p>
    <div class="text-center">
      <button class="btn btn-success" onclick="window.print()">🖨 Print Receipt</button>
      <a href="tenant_utilities.php" class="btn btn-secondary">⬅ Back to Bills</a>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
