<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['OwnerID'])) {
    header("Location: login.php");
    exit();
}

$ownerID = $_SESSION['OwnerID'];
$success = "";

// ✅ Assign Parking to Tenant
if (isset($_POST['assign_parking'], $_POST['parking_id'], $_POST['tenant_id'])) {
    $spaceID = $_POST['parking_id'];
    $tenantID = $_POST['tenant_id'];

    $stmt = $pdo->prepare("UPDATE parkingspaces SET AssignedTo = ?, Status = 'Occupied' WHERE ParkingID = ?");
    $stmt->execute([$tenantID, $spaceID]);

    header("Location: parking_spaces.php");
    exit();
}

// ✅ Unassign Parking Space
if (isset($_POST['unassign_parking'], $_POST['parking_id'])) {
    $spaceID = $_POST['parking_id'];

    $stmt = $pdo->prepare("UPDATE parkingspaces SET AssignedTo = NULL, Status = 'Available' WHERE ParkingID = ?");
    $stmt->execute([$spaceID]);

    header("Location: parking_spaces.php");
    exit();
}

// ✅ Add Parking Space
if (isset($_POST['add_parking'])) {
    $apartmentID = $_POST['apartment_id'];
    $spaceNumber = $_POST['space_number'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("INSERT INTO parkingspaces (ApartmentID, SpaceNumber, Status) VALUES (?, ?, ?)");
    $stmt->execute([$apartmentID, $spaceNumber, $status]);
    $success = "✅ New parking space added!";
}

// ✅ Fetch Apartments owned by logged-in owner
$apartments = $pdo->prepare("SELECT * FROM apartments WHERE OwnerID = ?");
$apartments->execute([$ownerID]);
$apartments = $apartments->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch Tenants linked to this owner's apartments
$tenants = $pdo->prepare("
    SELECT T.TenantID, T.FirstName, T.LastName
    FROM tenants T
    JOIN leases L ON T.TenantID = L.TenantID
    JOIN apartments A ON L.ApartmentID = A.ApartmentID
    WHERE A.OwnerID = ?
");
$tenants->execute([$ownerID]);
$tenants = $tenants->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch Parking Spaces with tenant info
$parkingSpaces = $pdo->prepare("
    SELECT P.*, A.BuildingName, A.UnitNumber, T.FirstName, T.LastName
    FROM parkingspaces P
    JOIN apartments A ON P.ApartmentID = A.ApartmentID
    LEFT JOIN tenants T ON P.AssignedTo = T.TenantID
    WHERE A.OwnerID = ?
    ORDER BY P.ParkingID DESC
");
$parkingSpaces->execute([$ownerID]);
$parkingSpaces = $parkingSpaces->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('header.php'); ?>
<div class="container mt-5">
    <h3>🚗 Parking Space Management</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- ✅ Add Parking Form -->
    <form method="POST" class="row g-3 my-3">
        <div class="col-md-3">
            <label>Apartment</label>
            <select name="apartment_id" class="form-control" required>
                <?php foreach ($apartments as $apt): ?>
                    <option value="<?= $apt['ApartmentID'] ?>">
                        <?= htmlspecialchars($apt['BuildingName']) ?> - Unit <?= htmlspecialchars($apt['UnitNumber']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Space Number</label>
            <input type="text" name="space_number" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="Available">Available</option>
                <option value="Occupied">Occupied</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" name="add_parking" class="btn btn-success w-100">Add Parking</button>
        </div>
    </form>

    <!-- ✅ Parking Table -->
    <h5>🧾 Parking Overview</h5>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Apartment</th>
                <th>Space Number</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($parkingSpaces as $space): ?>
                <tr>
                    <td><?= htmlspecialchars($space['BuildingName']) ?> - Unit <?= htmlspecialchars($space['UnitNumber']) ?></td>
                    <td><?= htmlspecialchars($space['SpaceNumber']) ?></td>
                    <td>
                        <?php if ($space['Status'] === 'Available'): ?>
                            <span class="badge bg-success">Available</span>
                        <?php else: ?>
                            <div>
                                <span class="badge bg-secondary">
                                    Assigned to <?= htmlspecialchars($space['FirstName'] . ' ' . $space['LastName']) ?>
                                </span>
                                <form method="POST" class="mt-2">
                                    <input type="hidden" name="parking_id" value="<?= $space['ParkingID'] ?>">
                                    <button type="submit" name="unassign_parking" class="btn btn-sm btn-danger">
                                        Unassign
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($space['Status'] === 'Available'): ?>
                            <form method="POST" class="d-flex">
                                <input type="hidden" name="parking_id" value="<?= $space['ParkingID'] ?>">
                                <select name="tenant_id" class="form-select me-2" required>
                                    <?php foreach ($tenants as $tenant): ?>
                                        <option value="<?= $tenant['TenantID'] ?>">
                                            <?= htmlspecialchars($tenant['FirstName'] . ' ' . $tenant['LastName']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="assign_parking" class="btn btn-warning btn-sm">
                                    Assign
                                </button>
                            </form>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>


<style>
  /* Modern Background */
  body {
    background-image: url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    min-height: 100vh;
    background-color: #56718b;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  }

  .navbar {
    background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%) !important;
    box-shadow: 0 4px 20px rgba(54, 38, 38, 0.1);
    padding: 0.8rem 0;
    width:100%;
  }

  .navbar-brand {
    font-weight: 700;
    font-size: 1.5rem;
    letter-spacing: 0.5px;
    color: darkcyan !important;
    transition: all 0.3s ease;
  }

  .navbar-brand:hover {
    transform: translateY(-2px);
    text-shadow: 0 2px 10px rgba(56, 45, 45, 0.3);
  }

  /* Container Styling */
  .container {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    padding: 2.5rem;
    margin-top: 2rem !important;
  }

  /* Header Styling */
  h3 {
    color: #2c3e50;
    font-weight: 700;
    font-size: 1.8rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  h3::before {
    content: "🚗";
    font-size: 1.5rem;
  }

  h5 {
    color: #2c3e50;
    font-weight: 600;
    margin: 2rem 0 1.5rem;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  h5::before {
    content: "🧾";
  }

  /* Form Styling */
  .row.g-3 {
    background: #f9fafc;
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    border: 1px solid #eaeef2;
  }

  .form-control, .form-select {
    border: 1px solid #e0e4e8;
    border-radius: 8px;
    padding: 10px 15px;
    transition: all 0.3s ease;
  }

  .form-control:focus, .form-select:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
  }

  label {
    color: #5a6a7e;
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
    font-size: 0.9rem;
  }

  /* Button Styling */
  .btn-success {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    border: none;
    border-radius: 8px;
    padding: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .btn-success:hover {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
  }

  .btn-warning {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    border: none;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .btn-warning:hover {
    background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
    transform: translateY(-2px);
  }

  .btn-danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border: none;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .btn-danger:hover {
    background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
    transform: translateY(-2px);
  }

  /* Table Styling */
  .table {
    border-radius: 10px;
    overflow: hidden;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 1.5rem;
  }

  .table thead th {
    background-color: #2c3e50;
    color: white;
    font-weight: 600;
    padding: 12px 15px;
    border: none;
  }

  .table tbody td {
    padding: 12px 15px;
    vertical-align: middle;
    border-bottom: 1px solid #eaeef2;
  }

  .table tbody tr:last-child td {
    border-bottom: none;
  }

  /* Badge Styling */
  .badge {
    padding: 6px 10px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.8rem;
  }

  .bg-success {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
  }

  .bg-secondary {
    background: linear-gradient(135deg, #7f8c8d 0%, #95a5a6 100%);
  }

  /* Alert Styling */
  .alert-success {
    background: linear-gradient(135deg, rgba(46, 204, 113, 0.1) 0%, rgba(39, 174, 96, 0.1) 100%);
    border: 1px solid rgba(46, 204, 113, 0.2);
    color: #27ae60;
    border-radius: 8px;
    padding: 12px 20px;
  }

  /* Responsive Adjustments */
  @media (max-width: 768px) {
    .container {
      padding: 1.5rem;
    }
    
    .row.g-3 {
      padding: 1rem;
    }
    
    .col-md-3 {
      margin-bottom: 1rem;
    }
    
    .table {
      font-size: 0.9rem;
    }
  }
</style>

<?php include('footer.php'); ?>
