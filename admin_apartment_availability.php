<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['OwnerID'])) {
    header("Location: login.php");
    exit();
}

$ownerID = $_SESSION['OwnerID'];

// ✅ Handle Form Submission
if (isset($_POST['add_availability'])) {
    $apartmentID = $_POST['apartment_id'];
    $startDate   = $_POST['start_date'];
    $endDate     = $_POST['end_date'];
    $status      = $_POST['status'];

    try {
        // use correct lowercase table name
        $stmt = $pdo->prepare("INSERT INTO apartmentavailability 
            (ApartmentID, Start_Date, End_Date, Status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$apartmentID, $startDate, $endDate, $status]);

        header("Location: admin_dashboard.php");
        exit();
    } catch (PDOException $e) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Database Error',
                    text: '" . addslashes($e->getMessage()) . "'
                });
              </script>";
    }
}

// ✅ Get Apartments Owned by Owner
$stmt = $pdo->prepare("SELECT * FROM apartments WHERE OwnerID = ?");
$stmt->execute([$ownerID]);
$apartments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Get Current Availability
$availStmt = $pdo->prepare("
    SELECT AA.*, A.BuildingName, A.UnitNumber
    FROM apartmentavailability AA
    JOIN apartments A ON AA.ApartmentID = A.ApartmentID
    WHERE A.OwnerID = ?
    ORDER BY AA.Start_Date DESC
");
$availStmt->execute([$ownerID]);
$availability = $availStmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

 
<style>
/* Base Styles */
:root {
    --primary-color: #2c3e50;
    --secondary-color: #e74c3c;
    --accent-color: #3498db;
    --light-bg: #f9f9f9;
    --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    --border-radius: 8px;
}

body {
    background-color: #f8f9fa;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                    url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
    background-size: cover;
    background-position: center;
    min-height: 100vh;
    background-color: #f5f7fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
}

/* Header Styles */
h3 {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #eee;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Form Styles */
form {
    background: var(--light-bg);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    margin-bottom: 2rem !important;
    box-shadow: var(--card-shadow);
}

.form-control {
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    padding: 0.5rem 1rem;
    height: calc(2.25rem + 8px);
    transition: all 0.3s;
}

.form-control:focus {
    border-color: var(--accent-color);
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

label {
    font-weight: 500;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    display: block;
}

/* Button Styles */
.btn {
    border-radius: var(--border-radius);
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    transition: all 0.3s;
    border: none;
}

.btn-success {
    background-color: #27ae60;
    width: 100%;
}

.btn-success:hover {
    background-color: #2ecc71;
    transform: translateY(-2px);
}

.btn-secondary {
    background-color: var(--primary-color);
}

.btn-secondary:hover {
    background-color: #34495e;
    transform: translateY(-2px);
}

/* Table Styles */
.table {
    width: 100%;
    margin-bottom: 1.5rem;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--card-shadow);
}

.table thead th {
    background-color: var(--primary-color);
    color: white;
    font-weight: 500;
    padding: 1rem;
    text-align: left;
}

.table tbody td {
    padding: 1rem;
    border-top: 1px solid #eee;
    vertical-align: middle;
}

.table tbody tr:nth-child(even) {
    background-color: var(--light-bg);
}

.table tbody tr:hover {
    background-color: rgba(52, 152, 219, 0.1);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .row {
        flex-direction: column;
        gap: 1rem;
    }
    
    .col-md-3, .col-md-2, .col-md-1 {
        width: 100%;
    }
    
    .d-flex.align-items-end {
        align-items: flex-start;
    }
}

/* Status Badges (optional) */
td:last-child {
    font-weight: 500;
}

td:last-child[data-status="Available"] {
    color: #27ae60;
}

td:last-child[data-status="Reserved"] {
    color: #e74c3c;
}
</style>
 
 
<?php include('footer.php'); ?>