<?php
session_start();
require_once('../database/db.php');

if (!isset($_SESSION['TenantID'])) {
    header("Location: tenant_login.php");
    exit();
}

$tenantID = $_SESSION['TenantID'];
$amount   = $_POST['amount'] ?? 0;
$method   = $_POST['payment_method'] ?? '';
$receiptFile = null;

// ✅ Handle optional receipt upload
if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === 0) {
    $targetDir = "../uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["receipt"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["receipt"]["tmp_name"], $targetFilePath)) {
        $receiptFile = $fileName;
    }
}

// ✅ Save payment (using lowercase table name)
$stmt = $pdo->prepare("
    INSERT INTO payments (TenantID, Amount, Pay_Method, Status, Pay_Date, Receipt) 
    VALUES (?, ?, ?, 'Pending', NOW(), ?)
");
$stmt->execute([$tenantID, $amount, $method, $receiptFile]);

// ✅ Get last inserted payment ID
$paymentID = $pdo->lastInsertId();

// ✅ Redirect to receipt page
header("Location: receipt.php?id=" . urlencode($paymentID));
exit();
