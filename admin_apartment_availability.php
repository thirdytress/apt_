<?php
session_start();
require_once('database/db.php');
 
if (!isset($_SESSION['OwnerID'])) {
    header("Location: login.php");
    exit();
}
 
$ownerID = $_SESSION['OwnerID'];
 
// ✅ Handle Form Submission and redirect
if (isset($_POST['add_availability'])) {
    $apartmentID = $_POST['apartment_id'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $status = $_POST['status'];
 
    $stmt = $pdo->prepare("INSERT INTO ApartmentAvailability (ApartmentID, Start_Date, End_Date, Status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$apartmentID, $startDate, $endDate, $status]);
 
    // ✅ Redirect to admin dashboard
    header("Location: admin_dashboard.php");
    exit();
}
 
// ✅ Get Apartments Owned by Admin
$stmt = $pdo->prepare("SELECT * FROM Apartments WHERE OwnerID = ?");
$stmt->execute([$ownerID]);
$apartments = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
// ✅ Get Current Availability
$availStmt = $pdo->prepare("
    SELECT AA.*, A.BuildingName, A.UnitNumber
    FROM ApartmentAvailability AA
    JOIN Apartments A ON AA.ApartmentID = A.ApartmentID
    WHERE A.OwnerID = ?
    ORDER BY Start_Date DESC
");
$availStmt->execute([$ownerID]);
$availability = $availStmt->fetchAll(PDO::FETCH_ASSOC);
?>
 
<?php include('header.php'); ?>
 
<div class="container mt-5">
    <h3>🗓 Apartment Availability</h3>
 
    <!-- 🔘 Add Availability Form -->
    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label>Select Apartment</label>
                <select name="apartment_id" class="form-control" required>
                    <?php foreach ($apartments as $apt): ?>
                        <option value="<?= $apt['ApartmentID'] ?>">
                            <?= $apt['BuildingName'] ?> - Unit <?= $apt['UnitNumber'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="Available">Available</option>
                    <option value="Reserved">Reserved</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" name="add_availability" class="btn btn-success">Add</button>
            </div>
        </div>
    </form>
 
    <!-- 📋 Availability Table -->
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Apartment</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($availability as $row): ?>
            <tr>
                <td><?= $row['BuildingName'] ?> - Unit <?= $row['UnitNumber'] ?></td>
                <td><?= $row['Start_Date'] ?></td>
                <td><?= $row['End_Date'] ?></td>
                <td><?= $row['Status'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
 
    <!-- 🔙 Back to Dashboard -->
    <div class="mt-4">
        <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
 
</div>
 
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