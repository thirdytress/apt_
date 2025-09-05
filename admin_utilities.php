<?php
session_start();
require_once('database/db.php');
if (!isset($_SESSION['OwnerID'])) {
    header("Location: login.php");
    exit();
}

$ownerID = $_SESSION['OwnerID'];
$success = "";
$error = "";

// Handle bill addition
if (isset($_POST['add_bill'])) {
    $tenantID = $_POST['tenant_id'] ?? null;
    $apartmentID = $_POST['apartment_id'] ?? null;
    $billType = $_POST['type'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $billDate = $_POST['bill_date'] ?? '';

    if ($tenantID && $apartmentID) {
        $stmt = $pdo->prepare("INSERT INTO UtilityBills (TenantID, ApartmentID, Type, Amount, BillDate, Status) VALUES (?, ?, ?, ?, ?, 'Unpaid')");
        $stmt->execute([$tenantID, $apartmentID, $billType, $amount, $billDate]);
        $success = "✅ Utility bill added successfully!";
    } else {
        $error = "⚠️ Please select both tenant and apartment.";
    }
}

// Handle bill status update
if (isset($_POST['mark_paid'])) {
    $billID = $_POST['bill_id'];
    $pdo->prepare("UPDATE UtilityBills SET Status = 'Paid' WHERE BillID = ?")->execute([$billID]);
}


// Fetch tenants with apartment info
$tenants = $pdo->query("
    SELECT T.TenantID, T.FirstName, T.LastName, A.ApartmentID, A.BuildingName, A.UnitNumber 
    FROM Tenants T 
    JOIN Leases L ON T.TenantID = L.TenantID 
    JOIN Apartments A ON L.ApartmentID = A.ApartmentID 
    WHERE A.OwnerID = $ownerID
")->fetchAll();

// Fetch bills
$bills = $pdo->query("
    SELECT 
        UB.*, 
        T.FirstName, T.LastName, 
        A.BuildingName, A.UnitNumber,
        P.Pay_Method AS PaymentMethod
    FROM UtilityBills UB
    JOIN Tenants T ON UB.TenantID = T.TenantID
    JOIN Apartments A ON UB.ApartmentID = A.ApartmentID
    LEFT JOIN Payments P ON P.TenantID = UB.TenantID AND P.Amount = UB.Amount AND DATE(P.Pay_Date) = DATE(UB.BillDate)
    WHERE A.OwnerID = $ownerID
    ORDER BY UB.BillDate DESC
")->fetchAll();
?>

<?php include('header.php'); ?>
<div class="container mt-5">
    <h3>💡 Utility Bill Management</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- ✅ Add Utility Bill Form -->
    <form method="POST" class="row g-3 my-4">
        <div class="col-md-3">
            <label>Tenant</label>
            <select name="tenant_id" class="form-control" required>
                <option value="">-- Select Tenant --</option>
                <?php foreach ($tenants as $t): ?>
                    <option value="<?= $t['TenantID'] ?>">
                        <?= $t['FirstName'] . ' ' . $t['LastName'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Apartment</label>
            <select name="apartment_id" class="form-control" required>
                <option value="">-- Select Apartment --</option>
                <?php foreach ($tenants as $t): ?>
                    <option value="<?= $t['ApartmentID'] ?>">
                        <?= $t['BuildingName'] ?> - Unit <?= $t['UnitNumber'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label>Type</label>
            <select name="type" class="form-control">
                <option>Electricity</option>
                <option>Water</option>
                <option>Internet</option>
            </select>
        </div>
        <div class="col-md-2">
            <label>Amount</label>
            <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label>Bill Date</label>
            <input type="date" name="bill_date" class="form-control" required>
        </div>
        <div class="col-12 d-flex justify-content-end">
            <button type="submit" name="add_bill" class="btn btn-success">Add Utility Bill</button>
        </div>
    </form>

    <!-- 🧾 Bills Table -->
    <table class="table table-bordered">
       <thead class="table-light">
    <tr>
        <th>Tenant</th>
        <th>Apartment</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Bill Date</th>
        <th>Status</th>
        <th>Payment Method</th> <!-- New Column -->
        <th>Action</th>
    </tr>
</thead>

        <tbody>
            <?php foreach ($bills as $b): ?>
            <tr>
                <td><?= $b['FirstName'] . ' ' . $b['LastName'] ?></td>
                <td><?= $b['BuildingName'] ?> - Unit <?= $b['UnitNumber'] ?></td>
                <td><?= $b['Type'] ?></td>
                <td>₱<?= number_format($b['Amount'], 2) ?></td>
                <td><?= $b['BillDate'] ?></td>
                <td>
                    <?php if ($b['Status'] === 'Paid'): ?>
                        <span class="badge bg-success">Paid</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Unpaid</span>
                    <?php endif; ?>
                </td>
                <td><?= $b['PaymentMethod'] ?? '—' ?></td>
                <td>
                    <?php if (!empty($b['Status']) && $b['Status'] === 'Unpaid' && !empty($b['UtilityBillID'])): ?>
                        <form method="POST">
                            <input type="hidden" name="bill_id" value="<?= htmlspecialchars($b['BillID']) ?>">

                            <button type="submit" name="mark_paid" class="btn btn-sm btn-primary">Mark Paid</button>
                        </form>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
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
  /* Modern Background */
  body {
    background-color: #f8f9fa;
    background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                      url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    padding: 2rem 0;
  }

  /* Container Styling */
  .container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    padding: 2.5rem;
    margin: 0 auto;
    border: 1px solid #eaeaea;
    max-width: 1200px;
  }

  /* Header Styling */
  h3 {
    color: #2c3e50;
    font-weight: 600;
    font-size: 1.75rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  /* Alert Styling */
  .alert {
    border-radius: 8px;
    padding: 1rem 1.25rem;
    font-weight: 500;
  }

  /* Form Styling */
  .form-control {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px 15px;
    transition: all 0.3s ease;
    background-color: #fafafa;
  }

  .form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    background-color: white;
  }

  label {
    color: #555;
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
    font-size: 0.9rem;
  }

  /* Button Styling */
  .btn {
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
  }

  .btn-success {
    background-color: #27ae60;
  }

  .btn-success:hover {
    background-color: #219653;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.2);
  }

  .btn-secondary {
    background-color: #7f8c8d;
  }

  .btn-secondary:hover {
    background-color: #6c7a7d;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(127, 140, 141, 0.2);
  }

  .btn-primary {
    background-color: #3498db;
  }

  .btn-primary:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
  }

  /* Table Styling */
  .table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    margin: 2rem 0;
    border-radius: 10px;
    overflow: hidden;
  }

  .table thead th {
    background-color: #2c3e50;
    color: white;
    font-weight: 600;
    padding: 15px;
    border: none;
  }

  .table tbody td {
    padding: 12px 15px;
    vertical-align: middle;
    border-bottom: 1px solid #e0e0e0;
  }

  .table tbody tr:last-child td {
    border-bottom: none;
  }

  .table tbody tr:hover {
    background-color: rgba(236, 240, 241, 0.5);
  }

  /* Badge Styling */
  .badge {
    padding: 6px 10px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .bg-success {
    background-color: #27ae60 !important;
  }

  .bg-danger {
    background-color: #e74c3c !important;
  }

  /* Responsive Adjustments */
  @media (max-width: 768px) {
    .container {
      padding: 1.5rem;
    }
    
    .col-md-3, .col-md-2 {
      margin-bottom: 1rem;
    }
    
    .btn {
      width: 100%;
    }
  }
</style>

<?php include('footer.php'); ?>
