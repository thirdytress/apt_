<?php
session_start();
require_once('database/db.php'); 

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM Owner WHERE Owner_username = ?");
    $stmt->execute([$username]);
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($owner && password_verify($password, $owner['OwnerPass'])) {
        $_SESSION['OwnerID'] = $owner['OwnerID'];
        $_SESSION['OwnerName'] = $owner['FirstName'];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: 'Invalid username or password'
                });
              </script>";
    }
}
?>

<?php include('header.php'); ?>


<div class="container mt-5 col-md-4">
  <h3 class="text-center">Admin Login - ApartmentHub</h3>
  <form method="POST">
    <div class="mb-3">
      <label>Username</label>
      <input type="text" name="username" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" name="login" class="btn btn-dark w-100">Login</button>
  </form>
</div>

<style>
  /* Background styling */
  body {
    background-color: #26415c;
    background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                      url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    min-height: 100vh;
    display: flex;
    align-items: center;
  }
  
  /* Container styling inspired by casacol.co */
  .container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    padding: 2.5rem;
    margin: 2rem auto;
    border: 1px solid #eaeaea;
  }
  
  /* Header styling */
  h3 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 2rem;
    font-size: 1.5rem;
    letter-spacing: -0.5px;
  }
  
  /* Form input styling */
  .form-control {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px 15px;
    transition: all 0.3s ease;
    background-color: #fafafa;
  }
  
  .form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    background-color: white;
  }
  
  /* Label styling */
  label {
    color: #555;
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
  }
  
  /* Button styling */
  .btn-dark {
    background-color: #2c3e50;
    border: none;
    border-radius: 8px;
    padding: 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    text-transform: uppercase;
    font-size: 0.9rem;
  }
  
  .btn-dark:hover {
    background-color: #1a252f;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
  /* Form group spacing */
  .mb-3 {
    margin-bottom: 1.5rem !important;
  }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .container {
      padding: 2rem 1.5rem;
      width: 90%;
    }
  }
</style>


<?php include('footer.php'); ?>
