<?php
session_start();
require_once('database/db.php');
include('header.php');
 
if (!isset($_SESSION['OwnerID'])) {
    header("Location: login.php");
    exit();
}
 
// Get list of tenants
$tenants = $pdo->query("SELECT * FROM Tenants")->fetchAll(PDO::FETCH_ASSOC);
 
// Get available apartments
$apartments = $pdo->prepare("SELECT * FROM Apartments WHERE Available = 1 AND OwnerID = ?");
$apartments->execute([$_SESSION['OwnerID']]);
$apartmentList = $apartments->fetchAll(PDO::FETCH_ASSOC);
 
// Handle form submission
if (isset($_POST['add_lease'])) {
    $tenantID = $_POST['tenant_id'];
    $apartmentID = $_POST['apartment_id'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $rent = $_POST['monthly_rent'];
    $deposit = $_POST['deposit'];
 
    try {
        // Insert lease
        $stmt = $pdo->prepare("INSERT INTO Leases
            (ApartmentID, TenantID, StartDate, EndDate, MonthlyRent, DepositAmount, LeaseStatus)
            VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        $stmt->execute([$apartmentID, $tenantID, $start, $end, $rent, $deposit]);
 
        // Mark apartment as occupied
        $pdo->prepare("UPDATE Apartments SET Available = 0 WHERE ApartmentID = ?")
            ->execute([$apartmentID]);
 
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Lease Assigned',
                text: 'Lease has been successfully assigned.'
            }).then(() => {
                window.location.href = 'admin_dashboard.php';
            });
        </script>";
    } catch (PDOException $e) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '" . addslashes($e->getMessage()) . "'
            });
        </script>";
    }
}
?>
 
<div class="container mt-5">
    <h2>Assign a New Lease</h2>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label>Tenant:</label>
            <select name="tenant_id" class="form-control" required>
                <option disabled selected value="">-- Select Tenant --</option>
                <?php foreach ($tenants as $t): ?>
                    <option value="<?= $t['TenantID'] ?>">
                        <?= $t['FirstName'] . ' ' . $t['LastName'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
 
        <div class="mb-3">
            <label>Apartment:</label>
            <select name="apartment_id" class="form-control" required>
                <option disabled selected value="">-- Select Apartment --</option>
                <?php foreach ($apartmentList as $a): ?>
                    <option value="<?= $a['ApartmentID'] ?>">
                        <?= $a['BuildingName'] ?> - Unit <?= $a['UnitNumber'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
 
        <input type="date" name="start_date" class="form-control mb-2" required>
        <input type="date" name="end_date" class="form-control mb-2" required>
        <input type="number" step="0.01" name="monthly_rent" class="form-control mb-2" placeholder="Monthly Rent" required>
        <input type="number" step="0.01" name="deposit" class="form-control mb-3" placeholder="Deposit Amount" required>
 
        <button type="submit" name="add_lease" class="btn btn-primary">Assign Lease</button>
        <a href="admin_dashboard.php" class="btn btn-secondary">← Back</a>
    </form>
</div>
 <style>
  /* Modern Background */
  body {
    background-color: #f8f9fa;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                    url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
    background-size: cover;
    background-position: center;
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 2rem 0;
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
    border-radius: 16px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.08);
    padding: 2.5rem;
    max-width: 700px;
    margin: 0 auto;
    border: 1px solid rgba(0,0,0,0.03);
  }

  /* Header Styling */
  h2 {
    color: #2c3e50;
    font-weight: 700;
    font-size: 2.1rem;
    margin-bottom: 1.8rem;
    position: relative;
    padding-bottom: 12px;
    text-align: center;
  }

  h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(to right, #3498db, #2c3e50);
    border-radius: 4px;
  }

  /* Form Styling */
  .mb-3 {
    margin-bottom: 1.5rem !important;
  }

  label {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
    font-size: 0.95rem;
  }

  .form-control {
    border: 1px solid #e0e4e8;
    border-radius: 10px;
    padding: 14px 18px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    font-size: 1rem;
    background-color: #f9fafc;
  }

  .form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
    background-color: white;
  }

  select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%232c3e50' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 12px;
  }

  /* Button Styling */
  .btn-primary {
    background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
    border: none;
    border-radius: 10px;
    padding: 14px 28px;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 18px rgba(52, 152, 219, 0.25);
    margin-right: 12px;
    letter-spacing: 0.5px;
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.35);
  }

  .btn-secondary {
    background: white;
    border: 2px solid #2c3e50;
    color: #2c3e50;
    border-radius: 10px;
    padding: 14px 28px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-secondary:hover {
    background: #2c3e50;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
  }

  /* Responsive Adjustments */
  @media (max-width: 768px) {
    .container {
      padding: 2rem 1.5rem;
      margin: 1rem;
    }
    
    h2 {
      font-size: 1.8rem;
    }
    
    .btn-primary, .btn-secondary {
      width: 100%;
      margin-bottom: 12px;
    }
    
    .btn-primary {
      margin-right: 0;
    }
  }
</style>
<?php include('footer.php'); ?>