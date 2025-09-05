<?php 
include('header.php'); 
require_once('database/db.php'); 
?>

<!-- Top Navbar -->
<nav id="topNav" class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm py-3">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold text-primary fs-4" href="#">🏢 ApartmentHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto fw-semibold">
        <li class="nav-item"><a class="nav-link btn btn-sm btn-outline-primary ms-2 px-3 rounded-pill" href="apartment.php">Apartments</a></li>
        <li class="nav-item"><a class="nav-link btn btn-sm btn-outline-primary ms-2 px-3 rounded-pill" href="admin_registration.php">Register</a></li>
        <li class="nav-item"><a class="nav-link btn btn-sm btn-outline-primary ms-2 px-3 rounded-pill" href="tenant_login.php">Login</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Sidebar -->
<div id="sideNav" class="sidebar">
  <h4 class="p-3 text-white text-center fw-bold">🏢 ApartmentHub</h4>
  <a href="apartment.php"><i class="bi bi-building me-2"></i>Apartments</a>
  <a href="admin_registration.php"><i class="bi bi-person-plus me-2"></i>Register</a>
  <a href="tenant_login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a>
</div>

<!-- Main Content -->
<div id="mainContent">

  <!-- Hero Section -->
  <section class="container card section-card text-center mt-5 pt-5 mb-5">
    <h1 class="fw-bold display-4 text-primary">Welcome to <span class="text-dark">ApartmentHub</span></h1>
    <p class="lead text-muted mb-4">Your trusted platform for finding and managing apartments with ease.</p>
    <div>
      <a href="tenant_registration.php" class="btn btn-primary btn-lg shadow-sm rounded-pill px-4 me-2">Register</a>
      <a href="tenant_login.php" class="btn btn-outline-secondary btn-lg shadow-sm rounded-pill px-4">Login</a>
    </div>
  </section>

  <!-- About Section -->
  <section class="container card section-card mb-5" id="about">
    <h2 class="section-title text-center mb-4">About ApartmentHub</h2>
    <div class="row align-items-center">
      <div class="col-lg-6">
        <p class="lead fw-semibold text-dark">Your trusted partner in finding the perfect home.</p>
        <p>Founded in 2023, ApartmentHub revolutionizes how people search and manage rentals. We bridge the gap between owners and tenants to create a smooth and reliable rental experience.</p>
        <p>Our platform combines modern technology with personalized service to help you find your dream apartment or manage properties effectively.</p>
      </div>
      <div class="col-lg-6">
        <div class="card border-0 shadow-lg hover-card">
          <img src="https://images.unsplash.com/photo-1560520031-3a4dc4e9de0c?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80" class="card-img-top rounded" alt="Our Mission">
          <div class="card-body text-center">
            <h5 class="card-title fw-bold">Our Mission</h5>
            <p class="card-text text-muted">To simplify the rental process with innovative technology while keeping the human touch that makes finding a home special.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="container card section-card mb-5" id="contact">
    <h2 class="section-title text-center mb-4">Contact Us</h2>
    <div class="row">
      <div class="col-lg-6">
        <h4 class="fw-bold text-primary mb-3">📞 Get in Touch</h4>
        <p>Need help? Our team is ready to assist you with your rental needs.</p>
        <ul class="list-unstyled contact-info">
          <li class="mb-2"><i class="bi bi-geo-alt-fill text-primary me-2"></i> Marayow, Lipa City, Batangas</li>
          <li class="mb-2"><i class="bi bi-telephone-fill text-primary me-2"></i> (0993) 962-8973</li>
          <li class="mb-2"><i class="bi bi-envelope-fill text-primary me-2"></i> ApartmentHub@gmail.com</li>
          <li><i class="bi bi-clock-fill text-primary me-2"></i> Mon–Fri: 9AM - 6PM | Sat: 10AM - 4PM</li>
        </ul>
      </div>
      <div class="col-lg-6">
        <h4 class="fw-bold text-primary mb-3">🌐 Connect With Us</h4>
        <p>Follow us on social media for updates and property listings.</p>
        <div class="d-flex gap-3 mt-3">
          <a href="#" class="btn btn-outline-primary rounded-circle"><i class="bi bi-facebook"></i></a>
          <a href="#" class="btn btn-outline-primary rounded-circle"><i class="bi bi-twitter"></i></a>
          <a href="#" class="btn btn-outline-primary rounded-circle"><i class="bi bi-instagram"></i></a>
        </div>
      </div>
    </div>
  </section>

</div> <!-- END mainContent -->

  <!-- Custom Styles -->
<style>
  body {
    background-color: #f5f7fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    scroll-behavior: smooth;
    overflow-x: hidden;
  }

  /* Top Navbar (hidden on scroll) */
  #topNav {
    transition: top 0.5s ease-in-out, background 0.3s;
    backdrop-filter: blur(6px);
  }

  /* Sidebar */
  .sidebar {
    height: 100%;
    width: 270px;
    position: fixed;
    top: 0;
    left: -280px;
    background: rgba(44, 62, 80, 0.95);
    backdrop-filter: blur(10px);
    padding-top: 80px;
    transition: all 0.5s cubic-bezier(0.77, 0, 0.175, 1);
    z-index: 1100;
    box-shadow: 4px 0 20px rgba(0,0,0,0.25);
    overflow-y: auto;
    border-right: 1px solid rgba(255,255,255,0.15);
  }

  .sidebar h4 {
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    color: #ecf0f1;
    letter-spacing: 1px;
    text-transform: uppercase;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    padding-bottom: 15px;
  }

  .sidebar a {
    padding: 14px 22px;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    color: #ecf0f1;
    display: flex;
    align-items: center;
    border-radius: 10px;
    margin: 8px 14px;
    transition: all 0.35s ease;
    position: relative;
    overflow: hidden;
  }
  .sidebar a i {
    margin-right: 12px;
    font-size: 1.2rem;
  }
  .sidebar a::before {
    content: "";
    position: absolute;
    left: -100%;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(52, 152, 219, 0.25);
    transition: all 0.4s ease;
    z-index: 0;
  }
  .sidebar a:hover::before {
    left: 0;
  }
  .sidebar a:hover {
    color: #fff;
    transform: translateX(5px);
  }

  .sidebar.active { left: 0; }

  /* Scrollbar for sidebar */
  .sidebar::-webkit-scrollbar { width: 6px; }
  .sidebar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 10px;
  }

  /* Main Content */
  #mainContent {
    transition: margin-left 0.5s cubic-bezier(0.77, 0, 0.175, 1);
    margin-left: 0;
    padding: 20px;
  }
  #mainContent.shifted { margin-left: 270px; }

  /* Section Cards */
  .section-card {
    border-radius: 24px;
    border: none;
    background: #fff;
    padding: 3rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    transition: transform 0.4s ease, box-shadow 0.4s ease;
  }
  .section-card:hover {
    transform: translateY(-8px) scale(1.01);
    box-shadow: 0 15px 45px rgba(0,0,0,0.15);
  }

  /* Hover Card */
  .hover-card {
    transition: transform 0.35s ease, box-shadow 0.35s ease;
    border-radius: 18px;
  }
  .hover-card:hover {
    transform: translateY(-10px) scale(1.03);
    box-shadow: 0 20px 40px rgba(0,0,0,0.18);
  }

  /* Section Titles */
  .section-title {
    font-weight: 800;
    font-size: 2.4rem;
    color: #2c3e50;
    position: relative;
    display: inline-block;
    padding-bottom: 6px;
  }
  .section-title::after {
    content: "";
    display: block;
    width: 60%;
    height: 4px;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    margin: 12px auto 0;
    border-radius: 3px;
  }
    
  footer {
    text-align: center;
    padding: 20px 0;
    background: #2c3e50;
    color: #ecf0f1;
    font-size: 0.95rem;
    position: relative;   /* Change to fixed if you want it always visible */
    bottom: 0;
    width: 100%;
  }

  footer .footer-content {
    display: flex;
    justify-content: center;  /* Centers horizontally */
    align-items: center;      /* Centers vertically */
    gap: 20px;
    flex-wrap: wrap;
  }
</style>

<!-- Scroll Script -->
<script>
  const topNav = document.getElementById("topNav");
  const sideNav = document.getElementById("sideNav");
  const mainContent = document.getElementById("mainContent");

  window.addEventListener("scroll", () => {
    if (window.scrollY > 150) {
      topNav.style.top = "-80px";      
      sideNav.classList.add("active"); 
      mainContent.classList.add("shifted"); 
    } else {
      topNav.style.top = "0";          
      sideNav.classList.remove("active"); 
      mainContent.classList.remove("shifted");
    }
  });
</script>

<?php include('footer.php'); ?>
