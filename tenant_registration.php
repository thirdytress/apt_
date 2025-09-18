<?php
require_once('database/db.php');
include('header.php');

$apartmentID = $_GET['apt'] ?? null;

if (isset($_POST['register'])) {
    $first = $_POST['firstname'];
    $last = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // ✅ Check if email already exists
    $check = $pdo->prepare("SELECT * FROM tenants WHERE tenant_email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Email Already Registered',
                text: 'Please use a different email address.'
            });
        </script>";
    } else {
        // ✅ Insert tenant
        $stmt = $pdo->prepare("INSERT INTO tenants (tenant_FN, tenant_LN, tenant_email, tenant_phonenumber, tenant_password)
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$first, $last, $email, $phone, $password]);

        $tenantID = $pdo->lastInsertId();

        // ✅ If registering for a specific apartment, auto-apply
        if (!empty($_POST['apt_id'])) {
            $aptID = $_POST['apt_id'];
            $applyStmt = $pdo->prepare("INSERT INTO apartmentapplications (tenant_ID, apartment_ID, status, application_date)
                                        VALUES (?, ?, 'Pending', CURDATE())");
            $applyStmt->execute([$tenantID, $aptID]);
        }

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Registered!',
                text: 'Account created successfully!'
            }).then(() => {
                window.location.href = 'tenant_login.php';
            });
        </script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tenant Registration - ApartmentHub</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      margin: 0;
      background: url('https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1350&q=80') center/cover no-repeat;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-bottom: 60px;
    }

    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background: linear-gradient(120deg, rgba(26, 188, 156, 0.6), rgba(52, 152, 219, 0.6));
      z-index: 0;
    }

    .content-wrapper {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
    }

    .registration-card {
      position: relative;
      z-index: 1;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(12px);
      border-radius: 18px;
      padding: 2.5rem;
      max-width: 460px;
      width: 90%;
      color: #fff;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
      animation: fadeIn 0.8s ease;
      margin-top: 40px;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .registration-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .registration-header h2 {
      font-weight: 700;
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }

    .registration-header p {
      color: #e0e0e0;
      font-size: 1rem;
    }

    .form-group {
      position: relative;
      margin-bottom: 1.2rem;
    }

    .form-control {
      width: 100%;
      padding: 14px 45px 14px 40px;
      border: none;
      border-radius: 12px;
      font-size: 1rem;
      background: rgba(255, 255, 255, 0.9);
      color: #2c3e50;
    }

    .form-control:focus {
      outline: none;
      background: #fff;
      box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.3);
    }

    .input-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #7f8c8d;
    }

    .password-toggle {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #7f8c8d;
      cursor: pointer;
    }

    .btn-register {
      background: linear-gradient(135deg, #1abc9c, #16a085);
      border: none;
      border-radius: 12px;
      padding: 14px;
      width: 100%;
      font-size: 1rem;
      font-weight: 600;
      color: white;
      text-transform: uppercase;
      margin-top: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-register:hover {
      background: linear-gradient(135deg, #16a085, #1abc9c);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(26, 188, 156, 0.4);
    }

    .login-link {
      text-align: center;
      margin-top: 1.5rem;
      color: #ecf0f1;
    }

    .login-link a {
      color: #f1c40f;
      text-decoration: none;
      font-weight: 600;
    }

    .login-link a:hover {
      text-decoration: underline;
    }

    footer {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      text-align: center;
      padding: 10px;
      background: rgba(44, 62, 80, 0.9);
      color: #fff;
      font-size: 14px;
      z-index: 10;
    }

    @media (max-width: 768px) {
      .registration-card {
        padding: 2rem 1.5rem;
      }
      .registration-header h2 {
        font-size: 1.6rem;
      }
    }
  </style>
</head>
<body>
  <!-- Content -->
  <div class="content-wrapper">
    <div class="registration-card">
      <div class="registration-header">
        <h2>Create Account</h2>
        <p>Register to apply for apartments and manage your rentals</p>
      </div>

      <form method="POST" id="registrationForm">
        <input type="hidden" name="apt_id" value="<?= htmlspecialchars($apartmentID) ?>">

        <div class="form-group">
          <i class="fas fa-user input-icon"></i>
          <input type="text" name="firstname" class="form-control" placeholder="First Name" required>
        </div>

        <div class="form-group">
          <i class="fas fa-user input-icon"></i>
          <input type="text" name="lastname" class="form-control" placeholder="Last Name" required>
        </div>

        <div class="form-group">
          <i class="fas fa-envelope input-icon"></i>
          <input type="email" name="email" class="form-control" placeholder="Email Address" required>
        </div>

        <div class="form-group">
          <i class="fas fa-phone input-icon"></i>
          <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
        </div>

        <div class="form-group">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
          <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
        </div>

        <button type="submit" name="register" class="btn-register">Register Now</button>

        <div class="login-link">
          Already have an account? <a href="tenant_login.php">Login here</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    © 2025 <strong>ApartmentHub</strong>. All Rights Reserved.
  </footer>

  <script>
    // Password toggle
    document.getElementById('passwordToggle').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
    });

    // Simple validation
    document.getElementById('registrationForm').addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long');
      }
    });
  </script>
</body>
</html>
