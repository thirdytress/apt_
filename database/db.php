<?php
$host = 'mysql.hostinger.com';
$db = 'u164511188_apartmenthub';
$user = 'u164511188_apthub';
$pass = 'Apartmenthub@01';
// db password: Apartmenthub@01

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ===================== APARTMENT FUNCTIONS =====================
function addApartment($pdo, $ownerID, $building, $unit, $rent, $bedrooms, $bathrooms, $province, $city, $barangay, $street) {
    $available = 1;
    $stmt = $pdo->prepare("INSERT INTO Apartments (
        OwnerID, BuildingName, RentAmount, Bedrooms, Bathrooms, Available,
        UnitNumber, Apt_Prov, Apt_City, Apt_Brgy, Apt_Street
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $ownerID, $building, $rent, $bedrooms, $bathrooms, $available,
        $unit, $province, $city, $barangay, $street
    ]);
    return $pdo->lastInsertId();
}

function getApartmentsByOwner($pdo, $ownerID) {
    $stmt = $pdo->prepare("SELECT * FROM Apartments WHERE OwnerID = ?");
    $stmt->execute([$ownerID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAvailableApartments($pdo, $ownerID = null) {
    if ($ownerID) {
        $stmt = $pdo->prepare("SELECT * FROM Apartments WHERE Available = 1 AND OwnerID = ?");
        $stmt->execute([$ownerID]);
    } else {
        $stmt = $pdo->query("SELECT * FROM Apartments WHERE Available = 1");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function setApartmentAvailability($pdo, $apartmentID, $available) {
    $stmt = $pdo->prepare("UPDATE Apartments SET Available = ? WHERE ApartmentID = ?");
    $stmt->execute([$available, $apartmentID]);
}

// ===================== LEASE FUNCTIONS =====================
function addLease($pdo, $apartmentID, $tenantID, $start, $end, $rent, $deposit) {
    $stmt = $pdo->prepare("INSERT INTO Leases (ApartmentID, TenantID, Start_Date, End_Date, Monthly_Rent, Deposit, Status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
    $stmt->execute([$apartmentID, $tenantID, $start, $end, $rent, $deposit]);
    setApartmentAvailability($pdo, $apartmentID, 0);
    return $pdo->lastInsertId();
}

function getLeasesByOwner($pdo, $ownerID) {
    $stmt = $pdo->prepare("SELECT L.*, T.FirstName AS TenantFirstName, T.LastName AS TenantLastName, A.BuildingName, A.UnitNumber 
        FROM Leases L 
        JOIN Tenants T ON L.TenantID = T.TenantID 
        JOIN Apartments A ON L.ApartmentID = A.ApartmentID 
        WHERE A.OwnerID = ?");
    $stmt->execute([$ownerID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ===================== TENANT FUNCTIONS =====================
function addTenant($pdo, $first, $last, $email, $phone, $password) {
    $stmt = $pdo->prepare("INSERT INTO Tenants (FirstName, LastName, Email, PhoneNumber, Password) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$first, $last, $email, $phone, $password]);
    return $pdo->lastInsertId();
}

function deleteTenant($pdo, $tenantID) {
    $leaseCheck = $pdo->prepare("SELECT COUNT(*) FROM Leases WHERE TenantID = ?");
    $leaseCheck->execute([$tenantID]);
    $leaseCount = $leaseCheck->fetchColumn();
    if ($leaseCount > 0) {
        return false;
    }
    $stmt = $pdo->prepare("DELETE FROM Tenants WHERE TenantID = ?");
    $stmt->execute([$tenantID]);
    return true;
}

// ===================== APPLICATION FUNCTIONS =====================
function applyForApartment($pdo, $apartmentID, $tenantID) {
    $stmt = $pdo->prepare("SELECT * FROM ApartmentApplications WHERE ApartmentID = ? AND TenantID = ? AND Status = 'Pending'");
    $stmt->execute([$apartmentID, $tenantID]);
    if ($stmt->rowCount() === 0) {
        $insert = $pdo->prepare("INSERT INTO ApartmentApplications (ApartmentID, TenantID, Status) VALUES (?, ?, 'Pending')");
        $insert->execute([$apartmentID, $tenantID]);
        return true;
    }
    return false;
}

function approveApartmentApplication($pdo, $applicationID) {
    $update = $pdo->prepare("UPDATE ApartmentApplications SET Status = 'Approved' WHERE ApplicationID = ?");
    $update->execute([$applicationID]);
    $stmt = $pdo->prepare("SELECT TenantID, ApartmentID FROM ApartmentApplications WHERE ApplicationID = ?");
    $stmt->execute([$applicationID]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($app) {
        return [$app['TenantID'], $app['ApartmentID']];
    }
    return null;
}

// ===================== UTILITY BILL FUNCTIONS =====================
function addUtilityBill($pdo, $tenantID, $apartmentID, $type, $amount, $billDate) {
    $stmt = $pdo->prepare("INSERT INTO UtilityBills (TenantID, ApartmentID, Type, Amount, BillDate, Status) VALUES (?, ?, ?, ?, ?, 'Unpaid')");
    $stmt->execute([$tenantID, $apartmentID, $type, $amount, $billDate]);
    return $pdo->lastInsertId();
}

function markUtilityBillPaid($pdo, $billID) {
    $stmt = $pdo->prepare("UPDATE UtilityBills SET Status = 'Paid' WHERE BillID = ?");
    $stmt->execute([$billID]);
}

// ===================== PAYMENT FUNCTIONS =====================
function addPayment($pdo, $tenantID, $amount, $method, $receiptFile = null, $billID = null) {
    $stmt = $pdo->prepare("INSERT INTO Payments (TenantID, Amount, PaymentMethod, Status, PaymentDate, Receipt, BillID) VALUES (?, ?, ?, 'Pending', NOW(), ?, ?)");
    $stmt->execute([$tenantID, $amount, $method, $receiptFile, $billID]);
    return $pdo->lastInsertId();
}

// ===================== MAINTENANCE REQUEST FUNCTIONS =====================
function addMaintenanceRequest($pdo, $tenantID, $apartmentID, $requestDetails) {
    $requestDate = date('Y-m-d');
    $stmt = $pdo->prepare("INSERT INTO MaintenanceRequest (TenantID, ApartmentID, RequestDate, RequestDetails) VALUES (?, ?, ?, ?)");
    $stmt->execute([$tenantID, $apartmentID, $requestDate, $requestDetails]);
    return $pdo->lastInsertId();
}
?>
