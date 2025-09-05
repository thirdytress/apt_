<?php
require_once('database/db.php');
session_start();

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "❌ Tenant ID is missing.";
    header("Location: admin_dashboard.php");
    exit();
}

$tenantID = $_GET['id'];

// Check for existing leases
$leaseCheck = $pdo->prepare("SELECT COUNT(*) FROM Leases WHERE TenantID = ?");
$leaseCheck->execute([$tenantID]);
$leaseCount = $leaseCheck->fetchColumn();

if ($leaseCount > 0) {
    $_SESSION['message'] = "⚠️ Cannot delete tenant. Active lease(s) exist.";
    header("Location: admin_dashboard.php");
    exit();
}

// Proceed with deletion
$stmt = $pdo->prepare("DELETE FROM Tenants WHERE TenantID = ?");
$stmt->execute([$tenantID]);

$_SESSION['message'] = "✅ Tenant deleted successfully.";
header("Location: admin_dashboard.php");
exit();
