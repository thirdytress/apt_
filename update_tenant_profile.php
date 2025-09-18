<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['TenantID'])) {
    header("Location: tenant_login.php");
    exit();
}

$tenantID = $_SESSION['TenantID'];

// Fetch tenant details
$stmt = $pdo->prepare("SELECT * FROM tenants WHERE tenant_ID = ?");
$stmt->execute([$tenantID]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstname']);
    $lastName  = trim($_POST['lastname']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phonenumber']);

    $errors = [];
    if (empty($firstName)) $errors[] = "First name is required.";
    if (empty($lastName))  $errors[] = "Last name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if (empty($phone))     $errors[] = "Phone number is required.";

    // Check if email is already used by another tenant
    $emailCheck = $pdo->prepare("SELECT tenant_ID FROM tenants WHERE email = ? AND tenant_ID != ?");
    $emailCheck->execute([$email, $tenantID]);
    if ($emailCheck->fetch()) {
        $errors[] = "This email is already registered by another user.";
    }

    if (empty($errors)) {
        try {
            $update = $pdo->prepare("UPDATE tenants 
                                     SET firstname = ?, lastname = ?, email = ?, phonenumber = ? 
                                     WHERE tenant_ID = ?");
            $update->execute([$firstName, $lastName, $email, $phone, $tenantID]);

            $_SESSION['message'] = "✅ Your information has been updated successfully.";
            $_SESSION['message_type'] = "success";
            header("Location: tenant_dashboard.php");
            exit();
        } catch (Exception $e) {
            $errors[] = "An error occurred while updating your information. Please try again.";
        }
    }
}
?>


<?php include('header.php'); ?>

<!-- Apartment Background -->
<div class="apartment-bg"></div>

<div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-icon">
                <i class="fas fa-user-edit"></i>
            </div>
            <div class="profile-title">
                <h2>Update My Profile</h2>
                <p>Keep your information up to date</p>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <div class="alert-icon">⚠️</div>
                <div class="alert-content">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="profile-form" id="profileForm">
            <div class="form-group">
                <input type="text" name="FirstName" id="firstName" class="form-input" required value="<?= htmlspecialchars($tenant['FirstName']) ?>" placeholder=" ">
                <label for="firstName">First Name</label>
            </div>

            <div class="form-group">
                <input type="text" name="LastName" id="lastName" class="form-input" required value="<?= htmlspecialchars($tenant['LastName']) ?>" placeholder=" ">
                <label for="lastName">Last Name</label>
            </div>

            <div class="form-group">
                <input type="email" name="Email" id="email" class="form-input" required value="<?= htmlspecialchars($tenant['Email']) ?>" placeholder=" ">
                <label for="email">Email Address</label>
            </div>

            <div class="form-group">
                <input type="tel" name="PhoneNumber" id="phone" class="form-input" required value="<?= htmlspecialchars($tenant['PhoneNumber']) ?>" placeholder=" ">
                <label for="phone">Phone Number</label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="tenant_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>

<?php include('footer.php'); ?>

<style>
/* ===== Apartment Background ===== */
.apartment-bg {
    position: fixed;
    top:0; left:0; right:0; bottom:0;
    background: url('https://images.unsplash.com/photo-1600585154340-be6161c67f7a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
    background-size: cover;
    z-index: -2;
}

.apartment-bg::after {
    content:'';
    position:absolute;
    top:0; left:0; right:0; bottom:0;
    background: rgba(0,0,0,0.5);
    z-index: -1;
}

/* ===== Profile Container ===== */
.profile-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 60px 20px;
    min-height: calc(100vh - 80px);
    z-index:1;
    position: relative;
}

/* ===== Glassmorphism Card ===== */
.profile-card {
    background: rgba(255,255,255,0.25);
    backdrop-filter: blur(15px);
    border-radius: 20px;
    padding: 30px;
    width: 100%;
    max-width: 700px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.3);
    animation: slideUp 0.8s ease forwards;
}

/* ===== Card Header ===== */
.profile-header {
    display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;
}
.profile-icon {
    width:70px; height:70px; border-radius:50%; background: rgba(255,255,255,0.2);
    display:flex; align-items:center; justify-content:center; font-size:2rem;
    transition: all 0.3s ease;
}
.profile-header:hover .profile-icon { transform: scale(1.1); }
.profile-title h2 { margin:0; font-weight:600; color:white; }
.profile-title p { margin:0; color:white; opacity:0.85; }

/* ===== Form ===== */
.profile-form {
    display: flex; flex-direction: column; gap:1.2rem;
}
.form-group {
    position: relative;
}
.form-input {
    width:100%; padding:1rem 1rem; border:none; border-radius:10px;
    background: rgba(255,255,255,0.25);
    color:white;
    font-size:1rem;
    backdrop-filter: blur(5px);
    transition: all 0.3s ease;
}

.form-input::placeholder {
    color: rgba(255,255,255,0.7);
}

.form-input:focus { 
    outline:none; 
    transform: scale(1.02); 
    box-shadow: 0 0 10px rgba(255,255,255,0.4); 
}

.form-input:focus + label,
.form-input:not(:placeholder-shown) + label {
    top: -10px; 
    left:10px; 
    font-size:0.85rem; 
    color:#ffd700; /* bright gold color */
    text-shadow: 0 0 4px rgba(0,0,0,0.5);
}

label {
    position: absolute; 
    top:14px; 
    left:14px; 
    color:white; 
    pointer-events:none; 
    transition: all 0.3s ease;
    text-shadow: 0 0 3px rgba(0,0,0,0.5);
    font-weight:500;
}

/* ===== Buttons ===== */
.form-actions { display:flex; gap:1rem; margin-top:1rem; flex-wrap:wrap; }
.btn {
    display:flex; align-items:center; gap:0.5rem; padding:0.8rem 1.5rem; border-radius:12px;
    font-weight:600; cursor:pointer; transition: all 0.3s ease;
}
.btn-primary {
    background: linear-gradient(135deg,#4f46e5,#8b5cf6); color:white; box-shadow:0 5px 15px rgba(79,70,229,0.3);
}
.btn-primary:hover { transform: translateY(-3px); }
.btn-secondary { background:#6b7280; color:white; }
.btn-secondary:hover { transform: translateY(-2px); }

/* ===== Card Animation ===== */
@keyframes slideUp {
    from { opacity:0; transform: translateY(30px); }
    to { opacity:1; transform: translateY(0); }
}

/* ===== Footer ===== */
footer {
    background:#1f2937; color:white; text-align:center; padding:1rem 0;
    position:fixed; bottom:0; left:0; right:0; z-index:10;
    box-shadow: 0 -5px 15px rgba(0,0,0,0.2);
}

/* ===== Responsive ===== */
@media(max-width:768px){
    .profile-card { padding:20px; }
    .profile-header { flex-direction:column; text-align:center; gap:1rem; }
    .profile-icon { width:60px; height:60px; font-size:1.5rem; }
}
</style>
