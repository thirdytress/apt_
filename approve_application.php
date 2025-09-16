<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['OwnerID'])) {
    header("Location: login.php");
    exit();
}

$applicationID = $_POST['application_id'] ?? null;

if ($applicationID) {
    // ✅ Approve the application
    $update = $pdo->prepare("UPDATE apartmentapplications SET Status = 'Approved' WHERE ApplicationID = ?");
    $update->execute([$applicationID]);

    // ✅ Get tenant and apartment info
    $stmt = $pdo->prepare("SELECT TenantID, ApartmentID FROM apartmentapplications WHERE ApplicationID = ?");
    $stmt->execute([$applicationID]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($app) {
        $tenantID    = $app['TenantID'];
        $apartmentID = $app['ApartmentID'];

        // ✅ Get apartment info for notification + lease
        $aptStmt = $pdo->prepare("SELECT BuildingName, UnitNumber, RentAmount FROM apartments WHERE ApartmentID = ?");
        $aptStmt->execute([$apartmentID]);
        $apt = $aptStmt->fetch(PDO::FETCH_ASSOC);

        if ($apt) {
            // 🔔 Notification message
            $msg = "🎉 Your application for {$apt['BuildingName']} - Unit {$apt['UnitNumber']} has been approved!";
            $notifyStmt = $pdo->prepare("
                INSERT INTO tenantnotifications (TenantID, Message, IsRead, CreatedAt) 
                VALUES (?, ?, 0, NOW())
            ");
            $notifyStmt->execute([$tenantID, $msg]);

            // 📄 Automatically create lease (1-year contract)
            $startDate     = date('Y-m-d');
            $endDate       = date('Y-m-d', strtotime('+1 year'));
            $rentAmount    = $apt['RentAmount'];
            $depositAmount = $rentAmount; // customize if needed

            $leaseStmt = $pdo->prepare("
                INSERT INTO leases (TenantID, ApartmentID, StartDate, EndDate, MonthlyRent, DepositAmount, LeaseStatus) 
                VALUES (?, ?, ?, ?, ?, ?, 'Active')
            ");
            $leaseStmt->execute([
                $tenantID,
                $apartmentID,
                $startDate,
                $endDate,
                $rentAmount,
                $depositAmount
            ]);

            // 🔒 Mark apartment as no longer available
            $pdo->prepare("UPDATE apartments SET Available = 0 WHERE ApartmentID = ?")->execute([$apartmentID]);

            $_SESSION['message_flash'] = "✅ Application approved, tenant notified, and lease created!";
        }
    }
}

// ✅ Redirect back to dashboard
header("Location: admin_dashboard.php");
exit();