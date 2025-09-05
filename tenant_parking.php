<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['TenantID'])) {
    header("Location: tenant_login.php");
    exit();
}

$tenantID = $_SESSION['TenantID'];
$parkingSpot = null;
$message = "";

// Get parking info for this tenant
$stmt = $pdo->prepare("SELECT * FROM ParkingSpaces WHERE AssignedTo = ?");
$stmt->execute([$tenantID]);
$parkingSpot = $stmt->fetch(PDO::FETCH_ASSOC);

// Request spot (if not assigned yet)
if (isset($_POST['request_spot']) && !$parkingSpot) {
    $spotNumber = $_POST['spot_number'];

    // Check if spot is already taken
    $check = $pdo->prepare("SELECT * FROM ParkingSpaces WHERE SpaceNumber = ?");
    $check->execute([$spotNumber]);
    if ($check->fetch()) {
        $message = "Spot already taken. Try another one.";
    } else {
        $insert = $pdo->prepare("INSERT INTO ParkingSpaces (ApartmentID, SpaceNumber, Status, AssignedTo) VALUES (NULL, ?, 'Occupied', ?)");
        $insert->execute([$spotNumber, $tenantID]);
        $message = "Parking spot assigned!";
        header("Refresh: 1");
    }
}
?>

<?php include('header.php'); ?>

<div class="main-container">
  <div class="glass-card">
    <h2 class="title">🚗 Parking Spot Management</h2>

    <?php if ($message): ?>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script>
        Swal.fire({
          icon: '<?= strpos($message, "assigned") !== false ? "success" : "warning" ?>',
          title: 'Notice',
          text: '<?= $message ?>'
        });
      </script>
    <?php endif; ?>

    <?php if ($parkingSpot): ?>
      <div class="ticket">
        <h3>Your Parking Pass</h3>
        <p><strong>Spot Number:</strong> <?= htmlspecialchars($parkingSpot['SpaceNumber']) ?></p>
        <span class="status">✅ Assigned</span>
      </div>
    <?php else: ?>
      <form method="POST">
        <label class="form-label">Request Parking Spot</label>
        <input type="text" name="spot_number" class="form-control" required placeholder="e.g. P-12">
        <button type="submit" name="request_spot" class="btn">Request Spot</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<style>
  /* Background Gradient */
  body {
    margin: 0;
    min-height: 100vh;
    background: linear-gradient(-45deg, #1d2671, #c33764, #159957, #155799);
    background-size: 400% 400%;
    animation: gradient 15s ease infinite;
    display: flex;
    flex-direction: column;
    font-family: 'Inter', sans-serif;
  }

  @keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  /* Main Glass Card */
  .main-container {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
  }

  .glass-card {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    padding: 2.5rem;
    width: 100%;
    max-width: 500px;
    color: white;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
  }

  /* Title */
  .title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    background: linear-gradient(90deg, #ff6a00, #ee0979);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  /* Ticket Style */
  .ticket {
    background: white;
    color: #2c3e50;
    padding: 1.5rem;
    border-radius: 12px;
    text-align: left;
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    margin-top: 1.5rem;
  }

  .ticket h3 {
    margin: 0 0 10px;
    font-weight: 700;
  }

  .ticket .status {
    display: inline-block;
    margin-top: 10px;
    padding: 5px 12px;
    background: #2ecc71;
    color: white;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
  }

  /* Form */
  .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    text-align: left;
  }

  .form-control {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: none;
    outline: none;
    margin-bottom: 15px;
    font-size: 1rem;
    background: rgba(255,255,255,0.9);
    color: #2c3e50;
  }

  /* Button with ripple */
  .btn {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
  }

  .btn:active::after {
    content: "";
    position: absolute;
    left: 50%;
    top: 50%;
    width: 200%;
    height: 200%;
    background: rgba(255,255,255,0.3);
    transform: translate(-50%, -50%) scale(0);
    border-radius: 50%;
    animation: ripple 0.6s linear;
  }

  @keyframes ripple {
    to {
      transform: translate(-50%, -50%) scale(1);
      opacity: 0;
    }
  }
</style>

<?php include('footer.php'); ?>
