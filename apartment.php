<?php
session_start();
require_once('database/db.php');

// ✅ Ensure tenant is logged in
if (!isset($_SESSION['TenantID'])) {
    $_SESSION['message'] = "⚠️ Please log in to apply for an apartment.";
    header("Location: tenant_login.php");
    exit();
}

$tenantID = $_SESSION['TenantID'];

// ✅ Fetch apartments not yet applied for by this tenant
$stmt = $pdo->prepare("
    SELECT A.*
    FROM apartments A
    WHERE A.Available = 1
      AND A.ApartmentID NOT IN (
          SELECT ApartmentID 
          FROM apartmentapplications 
          WHERE TenantID = ?
      )
");
$stmt->execute([$tenantID]);
$apartments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('header.php'); ?>

<div class="container mt-5">
    <h2>🏢 Available Apartments</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-info mt-3"><?= htmlspecialchars($_SESSION['message']) ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if ($apartments): ?>
        <div class="table-responsive mt-3">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Building</th>
                        <th>Unit</th>
                        <th>Rent</th>
                        <th>Bedrooms</th>
                        <th>Bathrooms</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($apartments as $apt): ?>
                        <tr>
                            <td><?= htmlspecialchars($apt['BuildingName']) ?></td>
                            <td><?= htmlspecialchars($apt['UnitNumber']) ?></td>
                            <td>₱<?= number_format($apt['RentAmount'], 2) ?></td>
                            <td><?= htmlspecialchars($apt['Bedrooms']) ?></td>
                            <td><?= htmlspecialchars($apt['Bathrooms']) ?></td>
                            <td>
                                <?= htmlspecialchars($apt['Apt_City']) ?>, 
                                <?= htmlspecialchars($apt['Apt_Brgy']) ?>, 
                                <?= htmlspecialchars($apt['Apt_Street']) ?>
                            </td>
                            <td>
                                <form method="POST" action="apply_apartment.php">
                                    <input type="hidden" name="apartment_id" value="<?= $apt['ApartmentID'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Apply</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted mt-3">
            You've applied for all currently available apartments or none are available.
        </p>
    <?php endif; ?>
</div>

<?php include('footer.php'); ?>

<!-- ✅ SweetAlert2 Popup -->
<?php if (isset($_SESSION['message_flash'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            title: 'Notice',
            text: "<?= addslashes($_SESSION['message_flash']) ?>",
            icon: 'info',
            confirmButtonText: 'OK'
        });
    </script>
    <?php unset($_SESSION['message_flash']); ?>
<?php endif; ?>


<style>
  /* Modern Background with image (no transparency overlay) */
  body {
    background-color: #f8f9fa;
    background-image: url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  }

  /* Container Styling */
  .container {
    max-width: 1200px;
    padding: 2rem 1rem;
    background-color: rgba(255,255,255,0.9);
    border-radius: 12px;
    backdrop-filter: blur(2px);
    margin: 3rem auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  }

  /* Header Styling */
  h2 {
    color: #2c3e50;
    font-weight: 700;
    font-size: 2.2rem;
    margin-bottom: 2.5rem !important;
    position: relative;
    letter-spacing: -0.5px;
  }

  h2:after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(to right, #3498db, #2c3e50);
    border-radius: 3px;
  }

  /* Card Styling */
  .card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    background-color: #ffffff;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
  }

  .card-body {
    padding: 1.75rem;
  }

  /* Card Title */
  .card-title {
    color: #2c3e50;
    font-weight: 700;
    font-size: 1.25rem;
    margin-bottom: 1.25rem;
    line-height: 1.4;
  }

  /* Card Text */
  .card-text {
    color: #5a6a7e;
    font-size: 0.95rem;
    line-height: 1.7;
    margin-bottom: 1.75rem;
  }

  .card-text strong {
    color: #2c3e50;
    font-weight: 600;
  }

  /* Button Styling */
  .btn-primary {
    background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
    border: none;
    border-radius: 8px;
    padding: 12px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    transform: translateY(-2px);
    box-shadow: 0 7px 18px rgba(52, 152, 219, 0.3);
  }

  /* Alert Styling */
  .alert-info {
    background-color: #f8f9fa;
    border: 1px solid rgba(0,0,0,0.05);
    color: #5a6a7e;
    border-radius: 10px;
    padding: 1.25rem;
    font-weight: 500;
  }

  /* Responsive Adjustments */
  @media (max-width: 768px) {
    .col-md-4 {
      margin-bottom: 1.5rem;
    }
    
    h2 {
      font-size: 1.8rem;
    }
    
    .card-body {
      padding: 1.5rem;
    }
    
    .container {
      margin: 1.5rem auto;
      padding: 1.5rem;
    }
  }
</style>

<?php include('footer.php'); ?>

<!-- ✅ SweetAlert2 Popup -->
<?php if (isset($_SESSION['message'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            title: 'Notice',
            text: "<?= addslashes($_SESSION['message']) ?>",
            icon: 'info',
            confirmButtonText: 'OK'
        });
    </script>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>
