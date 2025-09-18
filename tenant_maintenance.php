<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['TenantID'])) {
    header("Location: tenant_login.php");
    exit();
}

$tenantID = $_SESSION['TenantID'];
$successMessage = "";

// ✅ Get apartment assigned to tenant
$stmt = $pdo->prepare("SELECT ApartmentID FROM leases WHERE TenantID = ?");
$stmt->execute([$tenantID]);
$lease = $stmt->fetch(PDO::FETCH_ASSOC);
$apartmentID = $lease ? $lease['ApartmentID'] : null;

// ✅ Handle request submission
if (isset($_POST['submit_request']) && $apartmentID) {
    $requestDate    = date('Y-m-d');
    $requestDetails = $_POST['request_details'] ?? '';

    $stmt = $pdo->prepare("
        INSERT INTO maintenancerequests (TenantID, ApartmentID, RequestDate, RequestDetails, Status) 
        VALUES (?, ?, ?, ?, 'Pending')
    ");
    $stmt->execute([$tenantID, $apartmentID, $requestDate, $requestDetails]);

    $successMessage = "✅ Maintenance request submitted successfully!";
}
?>

<?php include('header.php'); ?>

<div class="page-banner">
    <div class="banner-overlay">
        <h1 class="banner-title">Maintenance Request</h1>
        <p class="banner-subtitle">Easily report issues in your apartment and get them fixed quickly.</p>
    </div>
</div>

<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h2 class="mb-4 text-center">Submit Maintenance Request</h2>

        <?php if ($successMessage): ?>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Submitted!',
                    text: '<?= $successMessage ?>'
                });
            </script>
        <?php endif; ?>

        <?php if ($apartmentID): ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="request" class="form-label">Maintenance Request</label>
                    <textarea name="request_details" class="form-control" rows="5" placeholder="Example: Aircon is leaking or door lock is broken..." required></textarea>
                    <div class="form-text text-muted">Be as specific as possible when describing the issue.</div>
                </div>
                <div class="d-flex justify-content-center gap-3">
                    <button type="submit" name="submit_request" class="btn btn-warning">Submit Request</button>
                    <button type="button" onclick="history.back()" class="btn btn-secondary">← Go Back</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-danger text-center">
                ⚠️ No active lease found. Please contact the admin.
            </div>
            <div class="text-center">
                <button type="button" onclick="history.back()" class="btn btn-secondary mt-3">← Go Back</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer>
    <p>© <?= date("Y") ?> ApartmentHub. All Rights Reserved.</p>
</footer>


<style>
  body {
    background-color: #f4f6f9;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    min-height: 100vh;
    margin: 0;
    display: flex;
    flex-direction: column;
  }

  /* Banner Styling */
  .page-banner {
    background: linear-gradient(135deg, #34495e, #2c3e50);
    padding: 3rem 1rem;
    text-align: center;
    color: white;
  }

  .banner-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: .5rem;
  }

  .banner-subtitle {
    font-size: 1rem;
    font-weight: 400;
    opacity: 0.9;
  }

  /* Card Container */
  .card {
    border-radius: 16px;
    border: none;
    background: #fff;
  }

  h2 {
    font-weight: 700;
    color: #2c3e50;
  }

  .form-label {
    font-weight: 600;
    color: #34495e;
  }

  .form-control {
    border-radius: 10px;
    border: 1px solid #ddd;
    padding: 12px;
  }

  .form-control:focus {
    border-color: #f39c12;
    box-shadow: 0 0 6px rgba(243, 156, 18, 0.2);
  }

  /* Buttons */
  .btn-warning {
    background: linear-gradient(135deg, #f39c12, #e67e22);
    border: none;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 10px;
    color: white;
    transition: 0.3s;
  }

  .btn-warning:hover {
    background: linear-gradient(135deg, #e67e22, #f39c12);
    transform: translateY(-2px);
  }

  .btn-secondary {
    border-radius: 10px;
    border: 2px solid #2c3e50;
    font-weight: 600;
    padding: 12px 24px;
    transition: 0.3s;
  }

  .btn-secondary:hover {
    background: #2c3e50;
    color: white;
  }

  /* Alert */
  .alert {
    border-radius: 10px;
    font-weight: 500;
  }

  /* Footer */
  footer {
    background: #2c3e50;
    color: white;
    padding: 8px 0;
    font-size: 0.85rem;
    text-align: center;
    margin-top: auto;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .banner-title {
      font-size: 2rem;
    }
    .card {
      padding: 1.5rem;
    }
  }
</style>
