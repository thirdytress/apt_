<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['TenantID'])) {
    header("Location: tenant_login.php");
    exit();
}

$tenantID = $_SESSION['TenantID'];
$success = "";

// Handle Payment
if (isset($_POST['pay_bill'])) {
    $billID = $_POST['bill_id'];
    $method = $_POST['payment_method'] ?? 'Unknown';
    $receiptFile = null;

    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES["receipt"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["receipt"]["tmp_name"], $targetFilePath)) $receiptFile = $fileName;
    }

    $stmt = $pdo->prepare("UPDATE UtilityBills SET Status = 'Paid', PaymentMethod = ? WHERE BillID = ? AND TenantID = ?");
    $stmt->execute([$method, $billID, $tenantID]);

    $stmt = $pdo->prepare("
        INSERT INTO Payments (TenantID, Amount, Pay_Method, Pay_Date, BillID, Receipt)
        SELECT TenantID, Amount, ?, NOW(), BillID, ? 
        FROM UtilityBills WHERE BillID = ? AND TenantID = ?
    ");
    $stmt->execute([$method, $receiptFile, $billID, $tenantID]);

    header("Location: receipt.php?bill_id=" . $billID);
    exit();
}

$bills = $pdo->prepare("
    SELECT UB.*, A.BuildingName, A.UnitNumber 
    FROM UtilityBills UB
    JOIN Apartments A ON UB.ApartmentID = A.ApartmentID
    WHERE UB.TenantID = ?
    ORDER BY UB.BillDate DESC
");
$bills->execute([$tenantID]);
$billList = $bills->fetchAll();
?>

<?php include('header.php'); ?>

<div class="d-flex flex-column min-vh-100">
    <div class="container flex-grow-1 py-5">
        <h3 class="text-center text-primary mb-5">🏢 My Apartment Utility Bills</h3>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach ($billList as $bill): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card apartment-card h-100 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0"><?= $bill['BuildingName'] ?> - Unit <?= $bill['UnitNumber'] ?></h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><i class="bi bi-lightning-charge-fill text-warning"></i> <strong>Type:</strong> <?= $bill['Type'] ?></p>
                        <p class="card-text"><i class="bi bi-currency-dollar text-success"></i> <strong>Amount:</strong> ₱<?= number_format($bill['Amount'], 2) ?></p>
                        <p class="card-text"><i class="bi bi-calendar-event-fill text-info"></i> <strong>Bill Date:</strong> <?= $bill['BillDate'] ?></p>
                        <p class="card-text">
                            <strong>Status:</strong>
                            <?php if (strtolower(trim($bill['Status'])) === 'paid'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Unpaid</span>
                            <?php endif; ?>
                        </p>
                        <p class="card-text"><strong>Method:</strong> <?= htmlspecialchars($bill['PaymentMethod'] ?? '—') ?></p>
                    </div>
                    <div class="card-footer bg-white border-top-0 d-flex justify-content-center">
                        <?php if (strtolower(trim($bill['Status'])) === 'unpaid'): ?>
                            <button type="button" class="btn btn-gradient w-100" data-bs-toggle="modal" data-bs-target="#payModal<?= $bill['BillID'] ?>">💳 Pay Now</button>
                        <?php else: ?>
                            <a href="receipt.php?bill_id=<?= $bill['BillID'] ?>" class="btn btn-outline-primary w-100">📄 View Receipt</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Payment Modal -->
            <div class="modal fade" id="payModal<?= $bill['BillID'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" enctype="multipart/form-data" class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Select Payment Method</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <input type="hidden" name="bill_id" value="<?= $bill['BillID'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select payment-method" name="payment_method" required>
                                    <option value="" disabled selected>-- Select Method --</option>
                                    <option value="GCash">GCash</option>
                                    <option value="PayMaya">PayMaya</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Cash">Cash</option>
                                </select>
                            </div>

                            <div class="gcash-info d-none border rounded p-3 bg-white">
                                <p>📱 <strong>GCash Number:</strong> 09XXXXXXXXX</p>
                                <p>Scan QR Code below:</p>
                                <img src="assets/gcash_qr.png" width="200" class="border rounded mb-2">
                                <div class="mb-2">
                                    <label class="form-label">Upload Receipt (Optional)</label>
                                    <input type="file" name="receipt" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="pay_bill" class="btn btn-gradient w-100">Confirm Payment</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php endforeach; ?>
        </div>

        <div class="mt-5 text-center">
            <a href="tenant_dashboard.php" class="btn btn-secondary btn-lg">← Back to Dashboard</a>
        </div>
    </div>

    <?php include('footer.php'); ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.payment-method').forEach(select => {
    select.addEventListener('change', function() {
        const modalBody = this.closest('.modal-body');
        const gcashInfo = modalBody.querySelector('.gcash-info');
        if (this.value === "GCash" || this.value === "Bank Transfer") {
            gcashInfo.classList.remove('d-none');
        } else {
            gcashInfo.classList.add('d-none');
        }
    });
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');

body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(160deg, #e0f7fa, #f1f8e9);
}

/* Card design like apartment site */
.apartment-card {
    border-radius: 15px;
    transition: transform 0.3s, box-shadow 0.3s;
}
.apartment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}
.card-title {
    font-weight: 600;
    color: #0d6efd;
}

/* Gradient buttons */
.btn-gradient {
    background: linear-gradient(90deg, #4e54c8, #8f94fb);
    color: #fff;
    font-weight: 600;
    border: none;
    transition: all 0.3s;
}
.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

/* Modal styling */
.modal-header {
    border-bottom: none;
    border-radius: 12px 12px 0 0;
}
.modal-body {
    border-radius: 0 0 12px 12px;
}

/* Footer styling */
footer {
    background-color: #343a40;
    color: #fff;
    padding: 1rem;
    text-align: center;
    margin-top: auto;
}
</style>
