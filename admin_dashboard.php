<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['OwnerID'])) {
    header("Location: login.php");
    exit();
}

$ownerID = $_SESSION['OwnerID'];

/** =======================
 * DASHBOARD DATA FETCHING
 * ======================= */

// ✅ Parking stats (safe parameterized query)
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN P.Status = 'Available' THEN 1 ELSE 0 END) AS available,
        SUM(CASE WHEN P.Status = 'Occupied' THEN 1 ELSE 0 END) AS occupied
    FROM parkingspaces P
    JOIN apartments A ON P.ApartmentID = A.ApartmentID
    WHERE A.OwnerID = ?
");
$stmt->execute([$ownerID]);
$parkingStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['available' => 0, 'occupied' => 0];

// ✅ Fetch all apartments by this owner
$stmtApt = $pdo->prepare("SELECT * FROM apartments WHERE OwnerID = ?");
$stmtApt->execute([$ownerID]);
$apartments = $stmtApt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Active leases
$stmtLease = $pdo->prepare("
    SELECT L.*, T.FirstName AS TenantFirstName, T.LastName AS TenantLastName, 
           A.BuildingName, A.UnitNumber
    FROM leases L
    JOIN tenants T ON L.TenantID = T.TenantID
    JOIN apartments A ON L.ApartmentID = A.ApartmentID
    WHERE A.OwnerID = ?
");
$stmtLease->execute([$ownerID]);
$leases = $stmtLease->fetchAll(PDO::FETCH_ASSOC);

// ✅ Maintenance requests
$stmtReq = $pdo->prepare("
    SELECT MR.RequestID, MR.RequestDate, MR.RequestDetails, MR.RequestStatus, 
           T.FirstName, T.LastName, A.BuildingName, A.UnitNumber
    FROM maintenancerequest MR
    JOIN tenants T ON MR.TenantID = T.TenantID
    JOIN apartments A ON MR.ApartmentID = A.ApartmentID
    WHERE A.OwnerID = ?
    ORDER BY MR.RequestDate DESC
");
$stmtReq->execute([$ownerID]);
$requests = $stmtReq->fetchAll(PDO::FETCH_ASSOC);

// ✅ Available apartments
$stmtAvailable = $pdo->prepare("
    SELECT AA.*, A.BuildingName, A.UnitNumber, A.Bedrooms, A.Bathrooms, 
           A.RentAmount, A.Apt_City, A.Apt_Brgy, A.Apt_Street
    FROM apartmentavailability AA
    JOIN apartments A ON AA.ApartmentID = A.ApartmentID
    WHERE A.OwnerID = ? AND AA.Status = 'Available'
    ORDER BY AA.Start_Date ASC
");
$stmtAvailable->execute([$ownerID]);
$availableApts = $stmtAvailable->fetchAll(PDO::FETCH_ASSOC);

// ✅ Pending apartment applications
$stmtApps = $pdo->prepare("
    SELECT AA.ApplicationID, AA.ApartmentID, AA.TenantID, AA.Status, 
           T.FirstName, T.LastName, A.BuildingName, A.UnitNumber
    FROM apartmentapplications AA
    JOIN tenants T ON AA.TenantID = T.TenantID
    JOIN apartments A ON AA.ApartmentID = A.ApartmentID
    WHERE AA.Status = 'Pending' AND A.OwnerID = ?
");
$stmtApps->execute([$ownerID]);
$pendingApps = $stmtApps->fetchAll(PDO::FETCH_ASSOC);

// ✅ Tenants with leases
$stmtTenants = $pdo->prepare("
    SELECT DISTINCT T.TenantID, T.FirstName, T.LastName, T.Email
    FROM tenants T
    JOIN leases L ON L.TenantID = T.TenantID
    JOIN apartments A ON L.ApartmentID = A.ApartmentID
    WHERE A.OwnerID = ?
");
$stmtTenants->execute([$ownerID]);
$tenants = $stmtTenants->fetchAll(PDO::FETCH_ASSOC);

// ✅ Utility bills
$stmtBills = $pdo->prepare("
    SELECT U.*, T.FirstName, T.LastName, A.BuildingName, A.UnitNumber
    FROM utilitybills U
    JOIN tenants T ON U.TenantID = T.TenantID
    JOIN apartments A ON U.ApartmentID = A.ApartmentID
    WHERE A.OwnerID = ?
    ORDER BY U.BillDate DESC
");
$stmtBills->execute([$ownerID]);
$utilityBills = $stmtBills->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>


<style>
/* Base Styles */
:root {
    --primary-color: #1a365d;
    --secondary-color: #e53e3e;
    --accent-color: #3182ce;
    --success-color: #38a169;
    --warning-color: #d69e2e;
    --light-bg: #f7fafc;
    --sidebar-bg: #2d3748;
    --sidebar-text: #f7fafc;
    --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    --border-radius: 12px;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2d3748;
    line-height: 1.6;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    background-attachment: fixed;
    margin: 0;
    padding: 0;
    min-height: 100vh;
}

/* Dashboard Layout */
.dashboard-container {
    display: flex;
    min-height: 100vh;
}

/* Sidebar Styles */
.sidebar {
    width: 300px;
    background: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
    color: var(--sidebar-text);
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header {
    padding: 2.5rem 2rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
}

.sidebar-header h3 {
    margin: 0 0 0.5rem 0;
    color: var(--sidebar-text);
    font-size: 1.75rem;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.sidebar-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1rem;
    font-weight: 500;
    color: #cbd5e0;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.sidebar-nav li {
    margin: 0;
}

.nav-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.2rem 2rem;
    color: var(--sidebar-text);
    text-decoration: none;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-left: 4px solid transparent;
    font-weight: 500;
    font-size: 1rem;
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transition: left 0.6s;
}

.nav-link:hover::before {
    left: 100%;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    transform: translateX(8px);
    border-left-color: var(--accent-color);
}

.nav-link.active {
    background: linear-gradient(90deg, rgba(49, 130, 206, 0.3), rgba(49, 130, 206, 0.1));
    border-left-color: var(--accent-color);
    color: white;
    box-shadow: inset 0 0 20px rgba(49, 130, 206, 0.2);
}

.nav-link .badge {
    background: linear-gradient(135deg, var(--secondary-color), #c53030);
    color: white;
    padding: 0.3rem 0.7rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(229, 62, 62, 0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Main Content */
.main-content {
    margin-left: 300px;
    padding: 2.5rem;
    width: calc(100% - 300px);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 0 0 0 30px;
    min-height: 100vh;
    box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
}

.content-section {
    display: none;
    animation: fadeInUp 0.6s ease-out;
}

.content-section.active {
    display: block;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    transition: all 0.4s ease;
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--accent-color), var(--success-color));
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    font-size: 3rem;
    padding: 1rem;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-color), var(--success-color));
    color: white;
    box-shadow: 0 8px 20px rgba(49, 130, 206, 0.3);
}

.stat-info h3 {
    margin: 0;
    font-size: 2rem;
    color: var(--primary-color);
}

.stat-info p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

/* Recent Activity */
.recent-activity {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    margin-bottom: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    padding: 1.2rem;
    margin-bottom: 1rem;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 12px;
    border-left: 4px solid var(--accent-color);
    transition: all 0.3s ease;
}

.activity-item:hover {
    transform: translateX(8px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.activity-item:last-child {
    margin-bottom: 0;
}

.activity-icon {
    font-size: 1.5rem;
    padding: 0.8rem;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-color), var(--success-color));
    color: white;
    box-shadow: 0 4px 12px rgba(49, 130, 206, 0.3);
}

.activity-content p {
    margin: 0 0 0.25rem 0;
    font-size: 0.9rem;
}

.activity-content small {
    color: #666;
    font-size: 0.8rem;
}

/* Table Styles */
.table {
    width: 100%;
    margin-bottom: 2rem;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--card-shadow);
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.table thead th {
    background: linear-gradient(135deg, var(--primary-color) 0%, #2c5282 100%);
    color: white;
    font-weight: 600;
    padding: 1.5rem;
    text-align: left;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
}

.table tbody td {
    padding: 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    vertical-align: middle;
    font-weight: 500;
}

.table tbody tr:nth-child(even) {
    background: rgba(247, 250, 252, 0.5);
}

.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: linear-gradient(90deg, rgba(49, 130, 206, 0.1), rgba(56, 161, 105, 0.1));
    transform: scale(1.01);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Button Styles */
.btn {
    border-radius: var(--border-radius);
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.9rem;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.6s;
}

.btn:hover::before {
    left: 100%;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
}

.btn-success {
    background: linear-gradient(135deg, var(--success-color), #48bb78);
    color: white;
}

.btn-success:hover {
    background: linear-gradient(135deg, #48bb78, var(--success-color));
}

.btn-outline-primary {
    border: 2px solid var(--accent-color);
    color: var(--accent-color);
    background: transparent;
}

.btn-outline-primary:hover {
    background: linear-gradient(135deg, var(--accent-color), #4299e1);
    color: white;
    border-color: transparent;
}

.btn-outline-secondary {
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    background: transparent;
}

.btn-outline-secondary:hover {
    background: linear-gradient(135deg, var(--primary-color), #2c5282);
    color: white;
    border-color: transparent;
}

.btn-outline-info {
    border: 2px solid #3182ce;
    color: #3182ce;
    background: transparent;
}

.btn-outline-info:hover {
    background: linear-gradient(135deg, #3182ce, #4299e1);
    color: white;
    border-color: transparent;
}

.btn-danger {
    background: linear-gradient(135deg, var(--secondary-color), #f56565);
    color: white;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #f56565, var(--secondary-color));
}

/* Badge Styles */
.badge {
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.bg-success {
    background: linear-gradient(135deg, var(--success-color), #48bb78) !important;
    color: white;
}

.bg-warning {
    background: linear-gradient(135deg, var(--warning-color), #ed8936) !important;
    color: white;
}

.bg-danger {
    background: linear-gradient(135deg, var(--secondary-color), #f56565) !important;
    color: white;
}

/* Parking Stats */
.parking-stats {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    margin-bottom: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    margin-bottom: 1rem;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 12px;
    border-left: 4px solid var(--accent-color);
    transition: all 0.3s ease;
}

.stat-item:hover {
    transform: translateX(8px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-item:last-child {
    margin-bottom: 0;
    border-left-color: var(--success-color);
}

.stat-label {
    font-weight: 600;
    color: var(--primary-color);
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--accent-color);
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Action Buttons */
.action-buttons {
    margin-top: 1rem;
}

.action-buttons .btn {
    margin-right: 0.5rem;
}

/* Text Styles */
.text-muted {
    color: #95a5a6 !important;
    font-style: italic;
}

h2 {
    color: var(--primary-color);
    font-weight: 700;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid transparent;
    background: linear-gradient(90deg, var(--accent-color), var(--success-color)) padding-box,
                linear-gradient(90deg, var(--accent-color), var(--success-color)) border-box;
    background-size: 100% 3px, 100% 3px;
    background-position: 0 100%, 0 100%;
    background-repeat: no-repeat;
    border-image: linear-gradient(90deg, var(--accent-color), var(--success-color)) 1;
    font-size: 2rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

h4 {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
}

/* Form Styles */
form {
    display: inline-block;
    margin-right: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.open {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
        width: 100%;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
}

/* Mobile Menu Toggle */
.mobile-toggle {
    display: none;
    position: fixed;
    top: 1rem;
    left: 1rem;
    z-index: 1001;
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 0.5rem;
    border-radius: 4px;
    font-size: 1.2rem;
}

@media (max-width: 768px) {
    .mobile-toggle {
        display: block;
    }
}

/* Loading Animation */
.content-section {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
}

.content-section.active {
    opacity: 1;
    transform: translateY(0);
}

/* Alert Styles */
.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: var(--border-radius);
    border: 1px solid transparent;
}

.alert-info {
    color: #0c5460;
    background-color: #d1ecf1;
    border-color: #bee5eb;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Navigation functionality
    const navLinks = document.querySelectorAll('.nav-link');
    const contentSections = document.querySelectorAll('.content-section');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Hide all content sections
            contentSections.forEach(section => section.classList.remove('active'));
            
            // Show target section
            const targetSection = this.getAttribute('data-section');
            const targetElement = document.getElementById(targetSection);
            if (targetElement) {
                targetElement.classList.add('active');
            }
        });
    });
    
    // Mobile menu toggle
    const mobileToggle = document.createElement('button');
    mobileToggle.className = 'mobile-toggle';
    mobileToggle.innerHTML = '☰';
    mobileToggle.addEventListener('click', function() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('open');
    });
    document.body.appendChild(mobileToggle);
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        const mobileToggle = document.querySelector('.mobile-toggle');
        
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !mobileToggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
});
</script>

<?php include('footer.php'); ?>