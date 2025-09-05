
    <?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['OwnerID'])) {
    header("Location: login.php");
    exit();
}

$ownerID = $_SESSION['OwnerID'];

// Fetch parking stats
$parkingStats = $pdo->query("SELECT SUM(Status = 'Available') AS available, SUM(Status = 'Occupied') AS occupied FROM ParkingSpaces P JOIN Apartments A ON P.ApartmentID = A.ApartmentID WHERE A.OwnerID = $ownerID")->fetch();

// Fetch all apartments by this owner
$stmtApt = $pdo->prepare("SELECT * FROM Apartments WHERE OwnerID = ?");
$stmtApt->execute([$ownerID]);
$apartments = $stmtApt->fetchAll(PDO::FETCH_ASSOC);

// Fetch active leases
$stmtLease = $pdo->prepare("SELECT L.*, T.FirstName AS TenantFirstName, T.LastName AS TenantLastName, A.BuildingName, A.UnitNumber FROM Leases L JOIN Tenants T ON L.TenantID = T.TenantID JOIN Apartments A ON L.ApartmentID = A.ApartmentID WHERE A.OwnerID = ?");
$stmtLease->execute([$ownerID]);
$leases = $stmtLease->fetchAll(PDO::FETCH_ASSOC);

// Fetch maintenance requests
$stmtReq = $pdo->prepare("SELECT MR.RequestID, MR.RequestDate, MR.RequestDetails, MR.RequestStatus, T.FirstName, T.LastName, A.BuildingName, A.UnitNumber FROM MaintenanceRequest MR JOIN Tenants T ON MR.TenantID = T.TenantID JOIN Apartments A ON MR.ApartmentID = A.ApartmentID WHERE A.OwnerID = ? ORDER BY MR.RequestDate DESC");
$stmtReq->execute([$ownerID]);
$requests = $stmtReq->fetchAll(PDO::FETCH_ASSOC);

// Fetch available apartments
$stmtAvailable = $pdo->prepare("SELECT AA.*, A.BuildingName, A.UnitNumber, A.Bedrooms, A.Bathrooms, A.RentAmount, A.Apt_City, A.Apt_Brgy, A.Apt_Street FROM ApartmentAvailability AA JOIN Apartments A ON AA.ApartmentID = A.ApartmentID WHERE A.OwnerID = ? AND AA.Status = 'Available' ORDER BY AA.Start_Date ASC");
$stmtAvailable->execute([$ownerID]);
$availableApts = $stmtAvailable->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending apartment applications
$stmtApps = $pdo->prepare("SELECT AA.ApplicationID, AA.ApartmentID, AA.TenantID, AA.Status, T.FirstName, T.LastName, A.BuildingName, A.UnitNumber FROM ApartmentApplications AA JOIN Tenants T ON AA.TenantID = T.TenantID JOIN Apartments A ON AA.ApartmentID = A.ApartmentID WHERE AA.Status = 'Pending' AND A.OwnerID = ?");
$stmtApps->execute([$ownerID]);
$pendingApps = $stmtApps->fetchAll(PDO::FETCH_ASSOC);

// Fetch tenants with leases
$stmtTenants = $pdo->prepare("SELECT T.TenantID, T.FirstName, T.LastName, T.Email FROM Tenants T JOIN Leases L ON L.TenantID = T.TenantID JOIN Apartments A ON L.ApartmentID = A.ApartmentID WHERE A.OwnerID = ?");
$stmtTenants->execute([$ownerID]);
$tenants = $stmtTenants->fetchAll(PDO::FETCH_ASSOC);

// Fetch utility bills
$stmtBills = $pdo->prepare("SELECT U.*, T.FirstName, T.LastName, A.BuildingName, A.UnitNumber FROM UtilityBills U JOIN Tenants T ON U.TenantID = T.TenantID JOIN Apartments A ON U.ApartmentID = A.ApartmentID WHERE A.OwnerID = ? ORDER BY U.BillDate DESC");
$stmtBills->execute([$ownerID]);
$utilityBills = $stmtBills->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('header.php'); ?>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-info mt-3"><?= $_SESSION['message']; ?></div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>📊 Dashboard</h3>
            <p>Welcome, <?= htmlspecialchars($_SESSION['OwnerName']) ?></p>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="#overview" class="nav-link active" data-section="overview">🏠 Overview</a></li>
                <li><a href="#pending-apps" class="nav-link" data-section="pending-apps">📨 Pending Applications <span class="badge"><?= count($pendingApps) ?></span></a></li>
                <li><a href="#tenants" class="nav-link" data-section="tenants">👤 Tenant Accounts</a></li>
                <li><a href="#available-apts" class="nav-link" data-section="available-apts">✅ Available Apartments</a></li>
                <li><a href="#all-apts" class="nav-link" data-section="all-apts">📋 All Apartments</a></li>
                <li><a href="#leases" class="nav-link" data-section="leases">📄 Active Leases</a></li>
                <li><a href="#maintenance" class="nav-link" data-section="maintenance">🛠 Maintenance Requests</a></li>
                <li><a href="#parking" class="nav-link" data-section="parking">🚘 Parking Overview</a></li>
                <li><a href="#utilities" class="nav-link" data-section="utilities">💡 Utility Bills</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Overview Section -->
        <div id="overview" class="content-section active">
            <h2>Dashboard Overview</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🏠</div>
                    <div class="stat-info">
                        <h3><?= count($apartments) ?></h3>
                        <p>Total Apartments</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?= count($tenants) ?></h3>
                        <p>Active Tenants</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-info">
                        <h3><?= count($leases) ?></h3>
                        <p>Active Leases</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🚘</div>
                    <div class="stat-info">
                        <h3><?= $parkingStats['available'] ?? 0 ?></h3>
                        <p>Parking Available</p>
                    </div>
                </div>
            </div>
            
            <div class="recent-activity">
                <h4>Recent Activity</h4>
                <div class="activity-list">
                    <?php if ($requests): ?>
                        <?php foreach (array_slice($requests, 0, 5) as $req): ?>
                            <div class="activity-item">
                                <span class="activity-icon">🛠</span>
                                <div class="activity-content">
                                    <p><strong><?= $req['FirstName'] . ' ' . $req['LastName'] ?></strong> submitted a maintenance request</p>
                                    <small><?= $req['RequestDate'] ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pending Applications Section -->
        <div id="pending-apps" class="content-section">
            <h2>📨 Pending Apartment Applications</h2>
            <?php if ($pendingApps): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr><th>Tenant</th><th>Apartment</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingApps as $app): ?>
                        <tr>
                            <td><?= $app['FirstName'] . ' ' . $app['LastName'] ?></td>
                            <td><?= $app['BuildingName'] ?> - Unit <?= $app['UnitNumber'] ?></td>
                            <td>
                                <form method="POST" action="approve_application.php">
                                    <input type="hidden" name="application_id" value="<?= $app['ApplicationID'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No pending applications at the moment.</p>
            <?php endif; ?>
        </div>

        <!-- Tenants Section -->
        <div id="tenants" class="content-section">
            <h2>👤 Tenant Accounts</h2>
            <?php if ($tenants): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tenants as $tenant): ?>
                        <tr>
                            <td><?= $tenant['FirstName'] . ' ' . $tenant['LastName'] ?></td>
                            <td><?= $tenant['Email'] ?></td>
                            <td>
                                <a href="delete_tenant.php?id=<?= $tenant['TenantID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this tenant?');">🗑️ Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No tenants found for your apartments.</p>
            <?php endif; ?>
        </div>

        <!-- Available Apartments Section -->
        <div id="available-apts" class="content-section">
            <h2>✅ Currently Available Apartments</h2>
            <?php if ($availableApts): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Building</th>
                            <th>Unit</th>
                            <th>Rent</th>
                            <th>Bedrooms</th>
                            <th>Bathrooms</th>
                            <th>Location</th>
                            <th>Available From</th>
                            <th>Until</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($availableApts as $apt): ?>
                        <tr>
                            <td><?= $apt['BuildingName'] ?></td>
                            <td><?= $apt['UnitNumber'] ?></td>
                            <td>₱<?= number_format($apt['RentAmount'], 2) ?></td>
                            <td><?= $apt['Bedrooms'] ?></td>
                            <td><?= $apt['Bathrooms'] ?></td>
                            <td><?= $apt['Apt_City'] ?>, <?= $apt['Apt_Brgy'] ?>, <?= $apt['Apt_Street'] ?></td>
                            <td><?= $apt['Start_Date'] ?></td>
                            <td><?= $apt['End_Date'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No apartments are currently marked as available.</p>
            <?php endif; ?>
        </div>

        <!-- All Apartments Section -->
        <div id="all-apts" class="content-section">
            <h2>📋 All Apartments</h2>
            <?php if ($apartments): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Building</th>
                            <th>Unit</th>
                            <th>Rent</th>
                            <th>Bedrooms</th>
                            <th>Bathrooms</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($apartments as $apt): ?>
                        <tr>
                            <td><?= $apt['BuildingName'] ?></td>
                            <td><?= $apt['UnitNumber'] ?></td>
                            <td>₱<?= number_format($apt['RentAmount'], 2) ?></td>
                            <td><?= $apt['Bedrooms'] ?></td>
                            <td><?= $apt['Bathrooms'] ?></td>
                            <td><?= $apt['Apt_City'] ?>, <?= $apt['Apt_Brgy'] ?>, <?= $apt['Apt_Street'] ?></td>
                            <td><?= $apt['Available'] ? 'Available' : 'Occupied' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No apartments found.</p>
            <?php endif; ?>
            <div class="action-buttons">
                <a href="admin_add_apartment.php" class="btn btn-outline-primary btn-sm">➕ Add Apartment</a>
                <a href="admin_apartment_availability.php" class="btn btn-outline-info btn-sm">📅 Manage Availability</a>
            </div>
        </div>

        <!-- Leases Section -->
        <div id="leases" class="content-section">
            <h2>📄 Active Leases</h2>
            <?php if ($leases): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Apartment</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Monthly Rent</th>
                            <th>Deposit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leases as $lease): ?>
                        <tr>
                            <td><?= $lease['TenantFirstName'] . ' ' . $lease['TenantLastName'] ?></td>
                            <td><?= $lease['BuildingName'] ?> - Unit <?= $lease['UnitNumber'] ?></td>
                            <td><?= $lease['StartDate'] ?></td>
                            <td><?= $lease['EndDate'] ?></td>
                            <td>₱<?= number_format($lease['MonthlyRent'], 2) ?></td>
                            <td>₱<?= number_format($lease['DepositAmount'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No leases found.</p>
            <?php endif; ?>
            <a href="admin_add_leases.php" class="btn btn-outline-primary btn-sm">➕ Add Lease</a>
        </div>

        <!-- Maintenance Section -->
        <div id="maintenance" class="content-section">
            <h2>🛠 Maintenance Requests</h2>
            <?php if ($requests): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Tenant</th>
                            <th>Apartment</th>
                            <th>Request Details</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><?= $req['RequestDate'] ?></td>
                            <td><?= $req['FirstName'] . ' ' . $req['LastName'] ?></td>
                            <td><?= $req['BuildingName'] ?> - Unit <?= $req['UnitNumber'] ?></td>
                            <td><?= nl2br(htmlspecialchars($req['RequestDetails'])) ?></td>
                            <td>
                                <?php if ($req['RequestStatus'] === 'Done'): ?>
                                    <span class="badge bg-success">Done</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No maintenance requests submitted.</p>
            <?php endif; ?>
        </div>

        <!-- Parking Section -->
        <div id="parking" class="content-section">
            <h2>🚘 Parking Overview</h2>
            <div class="parking-stats">
                <div class="stat-item">
                    <span class="stat-label">Available Spaces:</span>
                    <span class="stat-value"><?= $parkingStats['available'] ?? 0 ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Occupied Spaces:</span>
                    <span class="stat-value"><?= $parkingStats['occupied'] ?? 0 ?></span>
                </div>
            </div>
            <a href="parking_spaces.php" class="btn btn-outline-secondary btn-sm">Manage Parking</a>
        </div>

        <!-- Utilities Section -->
        <div id="utilities" class="content-section">
            <h2>💡 Utility Bills</h2>
            <?php if ($utilityBills): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Tenant</th>
                            <th>Apartment</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilityBills as $bill): ?>
                        <tr>
                            <td><?= $bill['BillDate'] ?></td>
                            <td><?= $bill['FirstName'] . ' ' . $bill['LastName'] ?></td>
                            <td><?= $bill['BuildingName'] ?> - Unit <?= $bill['UnitNumber'] ?></td>
                            <td><?= $bill['Type'] ?></td>
                            <td>₱<?= number_format($bill['Amount'], 2) ?></td>
                            <td>
                                <?php if ($bill['Status'] === 'Paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($bill['PaymentMethod'] ?? 'N/A') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No utility bills recorded.</p>
            <?php endif; ?>
            <a href="admin_utilities.php" class="btn btn-outline-info btn-sm">➕ Add Utility Bill</a>
        </div>
    </div>
</div>

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