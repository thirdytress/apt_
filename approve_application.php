<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['OwnerID'])) {
    header("Location: login.php");
    exit();
}

$applicationID = $_POST['application_id'] ?? null;

if ($applicationID) {
    // Approve the application
    $update = $pdo->prepare("UPDATE ApartmentApplications SET Status = 'Approved' WHERE ApplicationID = ?");
    $update->execute([$applicationID]);

    // Get tenant and apartment info
    $stmt = $pdo->prepare("SELECT TenantID, ApartmentID FROM ApartmentApplications WHERE ApplicationID = ?");
    $stmt->execute([$applicationID]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($app) {
        $tenantID = $app['TenantID'];
        $apartmentID = $app['ApartmentID'];

        // Get apartment name for message
        $aptStmt = $pdo->prepare("SELECT BuildingName, UnitNumber, RentAmount FROM Apartments WHERE ApartmentID = ?");
        $aptStmt->execute([$apartmentID]);
        $apt = $aptStmt->fetch(PDO::FETCH_ASSOC);

        // 🔔 Notification message
        $msg = "🎉 Your application for {$apt['BuildingName']} - Unit {$apt['UnitNumber']} has been approved!";
        $notifyStmt = $pdo->prepare("INSERT INTO TenantNotifications (TenantID, Message, IsRead, CreatedAt) VALUES (?, ?, 0, NOW())");
        $notifyStmt->execute([$tenantID, $msg]);

        // 📄 Automatically create lease (1-year contract starting today)
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+1 year'));
        $rentAmount = $apt['RentAmount'];
        $depositAmount = $rentAmount; // you can customize deposit logic here

        $leaseStmt = $pdo->prepare("INSERT INTO Leases (TenantID, ApartmentID, StartDate, EndDate, MonthlyRent, DepositAmount)
                                    VALUES (?, ?, ?, ?, ?, ?)");
        $leaseStmt->execute([
            $tenantID,
            $apartmentID,
            $startDate,
            $endDate,
            $rentAmount,
            $depositAmount
        ]);

        // 🔒 Optionally mark apartment as no longer available
        $pdo->prepare("UPDATE Apartments SET Available = 0 WHERE ApartmentID = ?")->execute([$apartmentID]);

        $_SESSION['message'] = "✅ Application approved, tenant notified, and lease created!";
    }
}

header("Location: admin_dashboard.php");
exit();
