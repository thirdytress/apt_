    <?php
    session_start();
    require_once('database/db.php');

    if (!isset($_SESSION['TenantID'])) {
        // Save the page they wanted to visit
        $_SESSION['redirect_after_login'] = 'apply_apartment.php';
        header("Location: tenant_login.php");
        exit();
    }

    $tenantID = $_SESSION['TenantID'];
    $apartmentID = $_POST['apartment_id'] ?? null;

    if ($apartmentID) {
        // Check for existing pending application
        $stmt = $pdo->prepare("SELECT * FROM ApartmentApplications 
                            WHERE ApartmentID = ? AND TenantID = ? AND Status = 'Pending'");
        $stmt->execute([$apartmentID, $tenantID]);

        if ($stmt->rowCount() === 0) {
            $insert = $pdo->prepare("INSERT INTO ApartmentApplications (ApartmentID, TenantID, Status) VALUES (?, ?, 'Pending')");
            $insert->execute([$apartmentID, $tenantID]);

            $_SESSION['message'] = "✅ Application submitted successfully!";
        } else {
            $_SESSION['message'] = "⚠️ You have already applied for this apartment.";
        }
    }

    header("Location: apartment.php");
    exit();
