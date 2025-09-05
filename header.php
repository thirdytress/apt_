<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ApartmentHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="d-flex flex-column min-vh-100">
  <style>
  /* Modern Real Estate Inspired Styling */
  body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background-color:rgb(47, 67, 87);
    background-image: url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
  }

  /* Premium Navbar Styling */
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

  .nav-link {
    color: rgba(89, 76, 145, 0.85) !important;
    font-weight: 500;
    letter-spacing: 0.3px;
    padding: 0.10rem 1rem !important;
    margin: 0 0.2rem;
    border-radius: 6px;
    transition: all 0.3s ease;
    position: relative;
  }

  .nav-link:hover {
    color: darkcyan !important;
    background: rgba(131, 90, 90, 0.1);
    transform: translateY(-2px);
  }

  .nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 2px;
    background: blue;
    transition: width 0.3s ease;
  }

  .nav-link:hover::after {
    width: 70%;
  }

  .navbar-toggler {
    border: none;
    padding: 0.5rem;
  }

  .navbar-toggler:focus {
    box-shadow: none;
  }

  .navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.85%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
  }

  /* Main content styling */
  .main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: darkcyan;
    text-align: center;
    padding: 2rem;
    background-color: rgba(0, 0, 0, 0.5);
    width: 100%;
  }
  .main-content h1 {
    color: #ecf0f1;
    font-weight: 700;
    font-size: 2.5rem;
    margin-bottom: 1rem;
  }
  .main-content p {
    color: #bdc3c7;
    font-size: 1.2rem;
    max-width: 600px;
    line-height: 1.6;
  }
  .main-content a {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
  }
  .main-content a:hover {
    color: #2980b9;
    text-decoration: underline;
  }
  .text-end {
    position: absolute;
    top: 1rem;
    right: 1rem;
  }
  .text-end a {
    color: #e74c3c;
    font-weight: 500;
    transition: color 0.3s ease;
  }
  .text-end a:hover {
    color: #c0392b;
  }


  /* Footer styling */
  footer {
    background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
    color: rgba(255, 255, 255, 0.8);
    padding: 1rem;
    text-align: center;
    font-size: 0.9rem;
    position: relative;
    bottom: 0;
    width: 100%;
  }

  /* Button styling */
  .btn-custom {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border: none;
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 30px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  .btn-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    color: white;
  }

  /* Responsive Adjustments */
  @media (max-width: 992px) {
    .navbar-collapse {
      background: rgba(44, 62, 80, 0.95);
      padding: 1rem;
      border-radius: 8px;
      margin-top: 0.5rem;
    }
    
    .nav-link {
      margin: 0.3rem 0;
      padding: 0.5rem !important;
    }
    
    .nav-link::after {
      display: none;
    }
  }
</style>

<!-- Navigation at the top -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">ApartmentHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="apartment.php">Apartments</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_registration.php">Register</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_login.php">Login</a></li>
      </ul>
    </div>
  </div>
</nav>








<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>