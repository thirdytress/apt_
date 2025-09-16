<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['OwnerID'])) {
    header("Location: login.php");
    exit();
}

include('header.php');
?>
<link rel="stylesheet" href="style.css"> <!-- removed ! -->
<?php
// Handle form submission
if (isset($_POST['add_apartment'])) {
    $ownerID   = $_SESSION['OwnerID'];
    $building  = $_POST['building_name'];
    $unit      = $_POST['unit_number'];
    $rent      = $_POST['rent_amount'];
    $bedrooms  = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $province  = $_POST['province'];
    $city      = $_POST['city'];
    $barangay  = $_POST['barangay'];
    $street    = $_POST['street'];

    try {
        // Use db.php function
        addApartment($pdo, $ownerID, $building, $unit, $rent, $bedrooms, $bathrooms, $province, $city, $barangay, $street);

        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Apartment Added',
                    text: 'The apartment has been successfully added.'
                }).then(() => {
                    window.location.href = 'admin_dashboard.php';
                });
              </script>";
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
?>

<div class="container mt-5">
    <h2>Add New Apartment</h2>
    <form method="POST" class="mt-4">
        <input type="text" name="building_name" class="form-control mb-2" placeholder="Building Name" required>
        <input type="text" name="unit_number" class="form-control mb-2" placeholder="Unit Number" required>
        <input type="number" name="rent_amount" class="form-control mb-2" placeholder="Monthly Rent (PHP)" required>
        <input type="number" name="bedrooms" class="form-control mb-2" placeholder="Bedrooms" required>
        <input type="number" name="bathrooms" class="form-control mb-2" placeholder="Bathrooms" required>
        <input type="text" name="province" class="form-control mb-2" placeholder="Province" required>
        <input type="text" name="city" class="form-control mb-2" placeholder="City" required>
        <input type="text" name="barangay" class="form-control mb-2" placeholder="Barangay" required>
        <input type="text" name="street" class="form-control mb-3" placeholder="Street" required>
        <button type="submit" name="add_apartment" class="btn btn-primary">Add Apartment</button>
        <a href="admin_dashboard.php" class="btn btn-secondary">← Back</a>
    </form>
</div>

<?php include('footer.php'); ?>


<style>
  /* Modern Background */
  body {
    background-color: #f5f7fa;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background-image: linear-gradient(rgba(245, 247, 250, 0.96), rgba(245, 247, 250, 0.96)),
      url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
    background-size: cover;
    background-position: center;
    min-height: 100vh;
  }

  .navbar {
    background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%) !important;
    box-shadow: 0 4px 20px rgba(54, 38, 38, 0.1);
    padding: 0.8rem 0;
    width: 100%;
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
    margin: 2rem auto;
    border: 1px solid rgba(0, 0, 0, 0.03);
  }

  /* Header Styling */
  h2 {
    color: #2c3e50;
    font-weight: 700;
    font-size: 2.1rem;
    margin-bottom: 1.8rem;
    position: relative;
    padding-bottom: 12px;
  }

  h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 4px;
    background: linear-gradient(to right, #3498db, #2c3e50);
    border-radius: 4px;
  }

  /* Form Styling */
  .form-control {
    border: 1px solid #e0e4e8;
    border-radius: 10px;
    padding: 14px 18px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    font-size: 1rem;
    margin-bottom: 1rem;
    background-color: #f9fafc;
  }

  .form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
    background-color: white;
  }

  .form-control::placeholder {
    color: #95a5a6;
    font-size: 0.95rem;
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

    .btn-primary,
    .btn-secondary {
      width: 100%;
      margin-bottom: 12px;
    }

    .btn-primary {
      margin-right: 0;
    }
  }

  /* SweetAlert Customization */
  .swal2-popup {
    border-radius: 12px !important;
    font-family: 'Inter', sans-serif !important;
  }
</style>

<?php include('footer.php'); ?>