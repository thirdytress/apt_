    <?php
session_start();
require_once('database/db.php');
include('header.php');
require 'vendor/autoload.php'; // for PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['register'])) {
    $first = htmlspecialchars(trim($_POST['firstname']));
    $last = htmlspecialchars(trim($_POST['lastname']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    // ✅ Password validation
    if (
        strlen($password) < 8 || 
        !preg_match('/[A-Z]/', $password) ||      
        !preg_match('/[#@_]/', $password) ||      
        !preg_match('/[0-9]/', $password)         
    ) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Invalid Password',
                text: 'Password must be at least 8 characters, include one uppercase letter, one number, and contain #, @, or _.'
            });
        </script>";
        exit;
    }

    // ✅ Check email duplicate
    $check = $pdo->prepare("SELECT * FROM Owner WHERE Email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Email Already Used',
                text: 'This email is already registered. Please use another.'
            });
        </script>";
    } else {
        // ✅ Generate verification code
        $verification_code = rand(100000, 999999);

        // Save data in session temporarily
        $_SESSION['pending_user'] = [
            'firstname' => $first,
            'lastname' => $last,
            'email' => $email,
            'phone' => $phone,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'code' => $verification_code
        ];

        // ✅ Send email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'jgarvia9@gmail.com';  // your Gmail
$mail->Password   = 'jtrdkbkwthstfkpa';   // <-- your 16-char App Password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;


            $mail->setFrom('no-reply@apartmenthub.com', 'ApartmentHub Verification');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Your Verification Code";
            $mail->Body    = "<h3>Your verification code is: <b>$verification_code</b></h3>";

            $mail->send();

            echo "<script>
                Swal.fire({
                    icon: 'info',
                    title: 'Verify Your Email',
                    text: 'We sent a verification code to your email. Please enter it below.'
                }).then(() => {
                    window.location.href = 'verify_code.php';
                });
            </script>";

        } catch (Exception $e) {
            echo "<pre>";
            echo "Mailer Error: " . $mail->ErrorInfo . "\n";
            echo "Exception: " . $e->getMessage();
            echo "</pre>";
        }
    }
}
?>



    <div class="container mt-5">
        <div class="card shadow border-0 rounded-lg overlay-card">
            <div class="card-header bg-light py-3">
                <h2 class="text-center mb-0 text-primary">Admin Registration</h2>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstname" class="form-label">First Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="firstname" class="form-control" placeholder="Enter first name" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastname" class="form-label">Last Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="lastname" class="form-control" placeholder="Enter last name" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="Enter email address" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" name="phone" class="form-control" placeholder="Enter phone number" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-at"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Create a password" required>
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Must be at least 8 characters long</div>
                    </div>
                    
                    <button type="submit" name="register" class="btn btn-primary btn-block mt-4 py-2">
                        <i class="fas fa-user-plus me-2"></i>Register Account
                    </button>
                    
                    <div class="text-center mt-3">
                        <p class="mb-0">Already have an account? <a href="admin_login.php" class="text-decoration-none">Login here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
    body {
        background-image: url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding: 20px;
        min-height: 100vh;
        display: flex;
        align-items: center;
        position: relative;
    }

    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(52, 152, 219, 0.8) 0%, rgba(44, 62, 80, 0.8) 100%);
        z-index: -1;
    }

    .container {
        max-width: 700px;
    }

    .card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        backdrop-filter: blur(10px);
        background-color: rgba(255, 255, 255, 0.92);
    }

    .overlay-card {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.1);
        background-color: rgba(248, 249, 250, 0.95) !important;
    }

    .rounded-lg {
        border-radius: 12px !important;
    }

    h2 {
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #2c3e50;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
    }

    .form-control {
        padding: 12px 15px;
        border-left: none;
        border-radius: 0 6px 6px 0 !important;
        transition: all 0.3s;
        background-color: rgba(255, 255, 255, 0.8);
    }

    .form-control:focus {
        border-color: #ced4da;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.15);
        background-color: white;
    }

    .input-group:focus-within .input-group-text {
        border-color: #3498db;
        background-color: #e3f2fd;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
        border: none;
        padding: 12px;
        font-weight: 600;
        border-radius: 6px;
        transition: all 0.3s;
        letter-spacing: 0.5px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    #togglePassword {
        border-left: none;
        border-radius: 0 6px 6px 0;
    }

    .form-text {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    a {
        color: #3498db;
        font-weight: 500;
    }

    a:hover {
        color: #2c3e50;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem;
        }
        
        .row {
            margin-bottom: -0.75rem;
        }
        
        .col-md-6 {
            margin-bottom: 0.75rem;
        }
        
        body {
            padding: 15px;
        }
    }
    </style>

    <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Form validation for password
    document.querySelector('form').addEventListener('submit', function(e) {
        const pass = document.getElementById('password').value;

        const hasUppercase = /[A-Z]/.test(pass);
        const hasSpecial   = /[#@_]/.test(pass);   // now accepts #, @, or _
        const hasNumber    = /[0-9]/.test(pass);
        const longEnough   = pass.length >= 8;

        if (!longEnough || !hasUppercase || !hasSpecial || !hasNumber) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Password',
                text: 'Password must be at least 8 characters, include one uppercase letter, one number, and contain #, @, or _.'
            });
        }
    });


    </script>

    <?php include('footer.php'); ?>