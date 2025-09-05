<?php
require_once('database/db.php');
include('header.php');

if (isset($_POST['submit_application'])) {
    // Basic tenant login info
    $first = $_POST['FirstName'];
    $last = $_POST['LastName'];
    $email = $_POST['Email'];
    $phone = $_POST['Phone'];
    $password = password_hash($_POST['Password'], PASSWORD_DEFAULT);

    // Insert into Tenants
    $tenantStmt = $pdo->prepare("INSERT INTO Tenants (FirstName, LastName, Email, PhoneNumber, password)
                                 VALUES (?, ?, ?, ?, ?)");
    $tenantStmt->execute([$first, $last, $email, $phone, $password]);
    $tenantID = $pdo->lastInsertId();

    // Insert into RentalApplications
    $applicationStmt = $pdo->prepare("
    INSERT INTO RentalApplications (
        TenantID, RentalPropertyAddress, FullName, DateOfBirth, SSN, Email, Phone,
        CurrentAddress, CurrentCity, CurrentState, CurrentZip, OwnOrRent, MonthlyPayment, CurrentHowLong,
        PreviousAddress, PreviousCity, PreviousState, PreviousZip, PreviousOwnOrRent, PreviousMonthlyPayment, PreviousHowLong,
        Employer, EmployerAddress, EmployerHowLong, EmployerPhone, EmployerEmail, EmployerFax, EmployerCity, EmployerState, EmployerZip,
        Position, PayType, AnnualIncome,
        EmergencyName, EmergencyAddress, EmergencyCity, EmergencyState, EmergencyZip, EmergencyPhone, EmergencyRelationship,
        ReferenceName, ReferenceAddress, ReferencePhone
    )
    VALUES (
        ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?
    )
");

$applicationStmt->execute([
    $tenantID, $_POST['RentalPropertyAddress'], $_POST['FullName'], $_POST['DOB'], $_POST['SSN'], $email, $phone,
    $_POST['CurrentAddress'], $_POST['CurrentCity'], $_POST['CurrentState'], $_POST['CurrentZip'], $_POST['OwnOrRent'], $_POST['MonthlyPayment'], $_POST['CurrentHowLong'],
    $_POST['PreviousAddress'], $_POST['PreviousCity'], $_POST['PreviousState'], $_POST['PreviousZip'], $_POST['PreviousOwnOrRent'], $_POST['PreviousMonthlyPayment'], $_POST['PreviousHowLong'],
    $_POST['Employer'], $_POST['EmployerAddress'], $_POST['EmployerHowLong'], $_POST['EmployerPhone'], $_POST['EmployerEmail'], $_POST['EmployerFax'], $_POST['EmployerCity'], $_POST['EmployerState'], $_POST['EmployerZip'],
    $_POST['Position'], $_POST['PayType'], $_POST['AnnualIncome'],
    $_POST['EmergencyName'], $_POST['EmergencyAddress'], $_POST['EmergencyCity'], $_POST['EmergencyState'], $_POST['EmergencyZip'], $_POST['EmergencyPhone'], $_POST['EmergencyRelationship'],
    $_POST['ReferenceName'], $_POST['ReferenceAddress'], $_POST['ReferencePhone']
]);



    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Application Submitted',
            text: 'Your rental application has been submitted successfully.'
        }).then(() => {
            window.location.href = 'tenant_login.php';
        });
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Rental Application</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --primary-dark: #0069d9;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-radius: 8px;
            --box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #495057;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 15px;
        }

        .application-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .application-header h2 {
            font-size: 2.2rem;
            color: var(--dark-color);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .application-header h2:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            counter-reset: step;
            position: relative;
        }

        .progress-bar:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: -1;
        }

        .progress-step {
            width: 40px;
            height: 40px;
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #999;
            position: relative;
        }

        .progress-step.active {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: #fff;
        }

        .progress-step.completed {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .form-section {
            background: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }

        .form-section:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .form-section h4 {
            color: var(--dark-color);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f1f1;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
        }

        .form-section h4 i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }

        .required-field:after {
            content: " *";
            color: #dc3545;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .form-col {
            padding: 0 10px;
            flex: 1;
            min-width: 200px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: var(--transition);
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 123, 255, 0.4);
        }

        .btn-submit {
            background-color: var(--success-color);
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
        }

        .btn-submit:hover {
            background-color: #218838;
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
        }

        .btn-container {
            text-align: center;
            margin-top: 40px;
        }

        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-upload input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
        }

        .file-upload-label {
            display: block;
            padding: 12px;
            background-color: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: var(--border-radius);
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .file-upload-label:hover {
            background-color: #e9ecef;
            border-color: var(--primary-color);
        }

        .file-upload-icon {
            font-size: 24px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .form-note {
            font-size: 0.85rem;
            color: var(--secondary-color);
            margin-top: 5px;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .form-col {
                flex: 100%;
                margin-bottom: 15px;
            }
            
            .progress-step {
                width: 30px;
                height: 30px;
                font-size: 0.8rem;
            }
            
            .application-header h2 {
                font-size: 1.8rem;
            }
        }

        /* Social Icons - Enhanced */
        .social-links {
            display: flex;
            justify-content: center;
            margin: 30px 0;
        }

        .social-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            color: white;
            font-size: 20px;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .social-icon:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }

        .facebook {
            background-color: #1877F2;
        }

        .twitter {
            background-color: #1DA1F2;
        }

        .instagram {
            background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
        }

        .linkedin {
            background-color: #0077B5;
        }

        /* Enhanced form controls */
        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
            padding-right: 35px;
        }

        /* Floating label effect */
        .form-control {
            position: relative;
            margin-bottom: 25px;
        }

        .form-control input:not(:placeholder-shown) + label,
        .form-control input:focus + label {
            transform: translateY(-25px) scale(0.9);
            background: white;
            padding: 0 5px;
            left: 10px;
            color: var(--primary-color);
        }

        /* Documents upload section */
        .documents-section {
            background: rgba(0, 123, 255, 0.05);
            border-left: 4px solid var(--primary-color);
        }

        .document-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: var(--transition);
        }

        .document-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .document-icon {
            width: 40px;
            height: 40px;
            background: rgba(0, 123, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="application-header">
            <h2>Premium Rental Application</h2>
            <p>Please fill out this form completely to apply for your dream property</p>
        </div>

        

        <form method="POST" class="needs-validation" novalidate>
            <!-- Account Information -->
            <div class="form-section">
                <h4><i class="fas fa-user-circle"></i> Account Information</h4>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="FirstName" class="required-field">First Name</label>
                        <input type="text" id="FirstName" name="FirstName" placeholder="John" required>
                    </div>
                    <div class="form-col form-group">
                        <label for="LastName" class="required-field">Last Name</label>
                        <input type="text" id="LastName" name="LastName" placeholder="Doe" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="Email" class="required-field">Email</label>
                        <input type="email" id="Email" name="Email" placeholder="your.email@example.com" required>
                    </div>
                    <div class="form-col form-group">
                        <label for="Phone" class="required-field">Phone Number</label>
                        <input type="tel" id="Phone" name="Phone" placeholder="(123) 456-7890" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="Password" class="required-field">Password</label>
                        <input type="password" id="Password" name="Password" placeholder="••••••••" required>
                    </div>
                </div>
            </div>

            <!-- Property Information -->
            <div class="form-section">
                <h4><i class="fas fa-home"></i> Property Information</h4>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="RentalPropertyAddress" class="required-field">Rental Property Address</label>
                        <input type="text" id="RentalPropertyAddress" name="RentalPropertyAddress" placeholder="123 Main St, Apt 4B" required>
                    </div>
                    <div class="form-col form-group">
                        <label for="MoveInDate">Desired Move-In Date</label>
                        <input type="date" id="MoveInDate" name="MoveInDate">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label>How did you hear about us?</label>
                        <select name="ReferralSource">
                            <option value="">Select an option</option>
                            <option value="Website">Our Website</option>
                            <option value="Friend">Friend/Family</option>
                            <option value="Social">Social Media</option>
                            <option value="Advertisement">Advertisement</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Current Address -->
            <div class="form-section">
                <h4><i class="fas fa-map-marker-alt"></i> Current Address</h4>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="CurrentAddress" class="required-field">Street Address</label>
                        <input type="text" id="CurrentAddress" name="CurrentAddress" placeholder="456 Current Ave" required>
                    </div>
                    <div class="form-col form-group">
                        <label for="CurrentCity" class="required-field">City</label>
                        <input type="text" id="CurrentCity" name="CurrentCity" placeholder="Current City" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="CurrentState" class="required-field">State</label>
                        <input type="text" id="CurrentState" name="CurrentState" placeholder="State" required>
                    </div>
                    <div class="form-col form-group">
                        <label for="CurrentZip" class="required-field">ZIP Code</label>
                        <input type="text" id="CurrentZip" name="CurrentZip" placeholder="12345" required>
                    </div>
                    <div class="form-col form-group">
                        <label for="OwnOrRent" class="required-field">Own or Rent?</label>
                        <select id="OwnOrRent" name="OwnOrRent" required>
                            <option value="">Select</option>
                            <option value="Own">Own</option>
                            <option value="Rent">Rent</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="MonthlyPayment">Monthly Payment ($)</label>
                        <input type="number" step="0.01" id="MonthlyPayment" name="MonthlyPayment" placeholder="1500.00">
                    </div>
                    <div class="form-col form-group">
                        <label for="CurrentHowLong">How Long? (Years)</label>
                        <input type="text" id="CurrentHowLong" name="CurrentHowLong" placeholder="2">
                    </div>
                </div>
            </div>

            <!-- Previous Address -->
            <div class="form-section">
                <h4><i class="fas fa-history"></i> Previous Address</h4>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="PreviousAddress">Street Address</label>
                        <input type="text" id="PreviousAddress" name="PreviousAddress" placeholder="789 Previous St">
                    </div>
                    <div class="form-col form-group">
                        <label for="PreviousCity">City</label>
                        <input type="text" id="PreviousCity" name="PreviousCity" placeholder="Previous City">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="PreviousState">State</label>
                        <input type="text" id="PreviousState" name="PreviousState" placeholder="State">
                    </div>
                    <div class="form-col form-group">
                        <label for="PreviousZip">ZIP Code</label>
                        <input type="text" id="PreviousZip" name="PreviousZip" placeholder="12345">
                    </div>
                    <div class="form-col form-group">
                        <label for="PreviousOwnOrRent">Own or Rent?</label>
                        <select id="PreviousOwnOrRent" name="PreviousOwnOrRent">
                            <option value="">Select</option>
                            <option value="Own">Own</option>
                            <option value="Rent">Rent</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="PreviousMonthlyPayment">Monthly Payment ($)</label>
                        <input type="number" step="0.01" id="PreviousMonthlyPayment" name="PreviousMonthlyPayment" placeholder="1200.00">
                    </div>
                    <div class="form-col form-group">
                        <label for="PreviousHowLong">How Long? (Years)</label>
                        <input type="text" id="PreviousHowLong" name="PreviousHowLong" placeholder="1">
                    </div>
                </div>
            </div>

            <!-- Employment Information -->
            <div class="form-section">
                <h4><i class="fas fa-briefcase"></i> Employment Information</h4>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="Employer">Employer</label>
                        <input type="text" id="Employer" name="Employer" placeholder="ABC Corporation">
                    </div>
                    <div class="form-col form-group">
                        <label for="EmployerAddress">Employer Address</label>
                        <input type="text" id="EmployerAddress" name="EmployerAddress" placeholder="123 Business Ave">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="EmployerHowLong">How Long? (Years)</label>
                        <input type="text" id="EmployerHowLong" name="EmployerHowLong" placeholder="3">
                    </div>
                    <div class="form-col form-group">
                        <label for="EmployerPhone">Phone</label>
                        <input type="tel" id="EmployerPhone" name="EmployerPhone" placeholder="(123) 456-7890">
                    </div>
                    <div class="form-col form-group">
                        <label for="EmployerEmail">Email</label>
                        <input type="email" id="EmployerEmail" name="EmployerEmail" placeholder="hr@example.com">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col form-group">
                        <label for="Position">Position</label>
                        <input type="text" id="Position" name="Position" placeholder="Software Engineer">
                    </div>
                    <div class="form-col form-group">
                        <label for="PayType">Pay Type</label>
                        <select id="PayType" name="PayType">
                            <option value="">Select</option>
                            <option value="Hourly">Hourly</option>
                            <option value="Salary">Salary</option>
                        </select>
                    </div>
                    <div class="form-col form-group">
                        <label for="AnnualIncome">Annual Income ($)</label>
                        <input type="number" step="0.01" id="AnnualIncome" name="AnnualIncome" placeholder="75000.00">
                    </div>
                </div>
            </div>

            <!-- Documents Upload -->
            <div class="form-section documents-section">
                <h4><i class="fas fa-file-upload"></i> Required Documents</h4>
                <p>Please upload the following documents to complete your application</p>
                
                <div class="document-item">
                    <div class="document-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Government Issued ID</label>
                        <div class="file-upload">
                            <label for="GovernmentID" class="file-upload-label">
                                <div class="file-upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <span>Click to Upload ID</span>
                                <input type="file" id="GovernmentID" name="GovernmentID" accept="image/*,.pdf">
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="document-item">
                    <div class="document-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Proof of Income (Last 2 Pay Stubs)</label>
                        <div class="file-upload">
                            <label for="ProofOfIncome" class="file-upload-label">
                                <div class="file-upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <span>Click to Upload Proof</span>
                                <input type="file" id="ProofOfIncome" name="ProofOfIncome" accept="image/*,.pdf" multiple>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="btn-container">
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Application
                </button>
            </div>
        </form>

        <!-- Social Links Section -->
        <div class="social-links">
            <a href="#" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-icon twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-icon linkedin"><i class="fab fa-linkedin-in"></i></a>
        </div>
    </div>

    <script>
        // Form validation example
        document.querySelector('form').addEventListener('submit', function(e) {
            // Basic validation - check required fields
            const requiredInputs = document.querySelectorAll('input[required], select[required]');
            let isValid = true;
            
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    input.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill out all required fields marked with *');
            }
        });
        
        // File upload feedback
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const label = this.parentElement;
                if (this.files.length > 0) {
                    label.innerHTML = `<i class="fas fa-check-circle" style="color: #28a745;"></i> ${this.files.length} file(s) selected`;
                }
            });
        });
        
        // Progress bar animation
        document.querySelectorAll('.form-section').forEach((section, index) => {
            section.addEventListener('mouseenter', () => {
                document.querySelectorAll('.progress-step').forEach((step, i) => {
                    if (i <= index) {
                        step.classList.add('completed');
                        step.classList.remove('active');
                    } else if (i === index + 1) {
                        step.classList.add('active');
                    } else {
                        step.classList.remove('active', 'completed');
                    }
                });
            });
        });
    </script>
</body>
</html>



<?php include('footer.php'); ?>