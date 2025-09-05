<?php
session_start();
require_once('database/db.php');
include('header.php');

// ✅ Prevent direct access if no pending registration
if ($input_code == strval($saved_code)) {  // ✅ loose comparison is fine here
    try {
        // ✅ Insert into DB
        $stmt = $pdo->prepare("INSERT INTO Owner (FirstName, LastName, Email, PhoneNumber, OwnerPass, Owner_username)
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['pending_user']['firstname'],
            $_SESSION['pending_user']['lastname'],
            $_SESSION['pending_user']['email'],
            $_SESSION['pending_user']['phone'],
            $_SESSION['pending_user']['password'], // already hashed
            $_SESSION['pending_user']['username']
        ]);

        unset($_SESSION['pending_user']); // clear session

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Verified!',
                text: 'Your account has been created successfully.'
            }).then(() => {
                window.location.href = 'admin_login.php';
            });
        </script>";
    } catch (PDOException $e) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Database Error',
                text: 'There was a problem saving your account. Please try again.'
            });
        </script>";
    }
} else {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Invalid Code',
            text: 'The verification code is incorrect. Please try again.'
        });
    </script>";
}

?>

<div class="container mt-5">
    <div class="card shadow border-0 rounded-lg">
        <div class="card-header bg-light py-3">
            <h2 class="text-center mb-0 text-primary">Email Verification</h2>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="form-group">
                    <label for="code" class="form-label">Enter Verification Code</label>
                    <input type="text" name="code" class="form-control" maxlength="6" placeholder="Enter the 6-digit code" required>
                </div>
                <button type="submit" name="verify" class="btn btn-primary btn-block mt-4">Verify</button>
            </form>
        </div>
    </div>
</div>
