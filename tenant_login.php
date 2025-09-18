<?php
session_start();
require_once('database/db.php');

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ✅ FIXED: column name should be "email"
    $stmt = $pdo->prepare("SELECT * FROM tenants WHERE email = ?");
    $stmt->execute([$email]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tenant && password_verify($password, $tenant['password'])) {
        $_SESSION['TenantID'] = $tenant['tenant_ID'];
        $_SESSION['TenantName'] = $tenant['firstname'] . ' ' . $tenant['lastname'];

        header("Location: tenant_dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tenant Login - ApartmentHub</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: url('https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1600&q=80') no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    body::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,0.55);
      backdrop-filter: blur(4px);
      z-index: -1;
    }
    .login-container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }
    .login-card {
      background: rgba(255,255,255,0.95);
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
      padding: 2.5rem;
      width: 100%;
      max-width: 420px;
      animation: fadeInUp 0.7s ease-out;
    }
    .login-header { text-align: center; margin-bottom: 2rem; }
    .login-icon {
      width: 80px; height: 80px;
      background: linear-gradient(135deg,#4f46e5,#3b82f6);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem; color: #fff;
      margin: 0 auto 1rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .login-title { font-size: 1.7rem; font-weight: 700; color: #1f2937; }
    .login-subtitle { font-size: 0.95rem; color: #6b7280; }
    .form-group { margin-bottom: 1.3rem; }
    .form-label { font-weight: 500; color: #374151; margin-bottom: 0.5rem; }
    .form-input {
      width: 100%; padding: 0.9rem 1rem;
      border: 1.8px solid #e5e7eb; border-radius: 10px;
      transition: all 0.3s ease; font-size: 1rem;
    }
    .form-input:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
      outline: none;
    }
    .btn-login {
      width: 100%; padding: 0.9rem;
      background: linear-gradient(135deg,#3b82f6,#2563eb);
      border: none; border-radius: 10px;
      color: #fff; font-weight: 600; font-size: 1rem;
      cursor: pointer; transition: all 0.3s ease;
      margin-top: 0.5rem;
    }
    .btn-login:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(59,130,246,0.35); }
    .alert { border-radius: 10px; padding: 0.9rem 1rem; font-size: 0.9rem; margin-bottom: 1rem; }
    .alert-danger { background: #fee2e2; color: #b91c1c; border-left: 4px solid #ef4444; }
    .login-footer { margin-top: 1.5rem; text-align: center; font-size: 0.9rem; color: #6b7280; }
    .login-footer a { color: #2563eb; font-weight: 600; text-decoration: none; }
    .login-footer a:hover { text-decoration: underline; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(25px); } to { opacity: 1; transform: translateY(0); } }
  </style>
</head>
<body>
  <?php include('header.php'); ?>

  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="login-icon"><i class="fas fa-building"></i></div>
        <h1 class="login-title">Tenant Login</h1>
        <p class="login-subtitle">Welcome back to your apartment portal</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" required>
        </div>
        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
        </div>
        <button type="submit" name="login" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</button>
      </form>

      <div class="login-footer">
        <p>Don't have an account? <a href="#">Contact property management</a></p>
      </div>
    </div>
  </div>

  <?php include('footer.php'); ?>
</body>
</html>
