<?php
session_start();
require_once('database/db.php');

// ✅ Ensure tenant is logged in
if (!isset($_SESSION['TenantID'])) {
    // Save the page they wanted to visit
    $_SESSION['redirect_after_login'] = 'apply_apartment.php';
    header("Location: tenant_login.php");
    exit();
}

$tenantID    = $_SESSION['TenantID'];
$apartmentID = $_POST['apartment_id'] ?? null;

if ($apartmentID) {
    // ✅ Check for existing pending application
    $stmt = $pdo->prepare("
        SELECT * 
        FROM apartmentapplications 
        WHERE ApartmentID = ? 
          AND TenantID = ? 
          AND Status = 'Pending'
    ");
    $stmt->execute([$apartmentID, $tenantID]);

    if ($stmt->rowCount() === 0) {
        // ✅ Insert new application
        $insert = $pdo->prepare("
            INSERT INTO apartmentapplications (ApartmentID, TenantID, Status) 
            VALUES (?, ?, 'Pending')
        ");
        $insert->execute([$apartmentID, $tenantID]);

        $_SESSION['message_flash'] = "✅ Application submitted successfully!";
    } else {
        $_SESSION['message_flash'] = "⚠️ You have already applied for this apartment.";
    }
} else {
    $_SESSION['message_flash'] = "⚠️ Invalid request. Please select an apartment.";
}

// ✅ Redirect back to available apartments page
header("Location: tenant_available_apartments.php");
exit();
