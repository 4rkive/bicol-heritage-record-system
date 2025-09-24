<?php
// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bgc";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Fetch total broodcocks ---
$total_chickens = 0;
$sql_broodcock = "SELECT COUNT(*) as total FROM broodcocks";
$result_broodcock = $conn->query($sql_broodcock);
if ($result_broodcock && $result_broodcock->num_rows > 0) {
    $row = $result_broodcock->fetch_assoc();
    $total_chickens = $row['total'];
}

// --- Fetch mortality count ---
$total_mortality = 0;
$sql_mortality = "SELECT COUNT(*) as total FROM mortality";
$result_mortality = $conn->query($sql_mortality);
if ($result_mortality && $result_mortality->num_rows > 0) {
    $row = $result_mortality->fetch_assoc();
    $total_mortality = $row['total'];
}

// --- Calculate mortality percentage ---
$mortality_percentage = 0;
if ($total_chickens > 0) {
    $mortality_percentage = ($total_mortality / $total_chickens) * 100;
}

// --- Fetch bloodline data ---
$bloodline_labels = [];
$bloodline_counts = [];

$sql_bloodline = "
    SELECT b.bloodline_name AS bloodline, COUNT(c.cock_id) AS total
    FROM bloodline b
    LEFT JOIN broodcocks c ON b.bloodline_id = c.bloodline_id
    GROUP BY b.bloodline_name
";

$result_bloodline = $conn->query($sql_bloodline);
if ($result_bloodline && $result_bloodline->num_rows > 0) {
    while ($row = $result_bloodline->fetch_assoc()) {
        $bloodline_labels[] = $row['bloodline'];
        $bloodline_counts[] = (int)$row['total'];
    }
}

// --- Fetch employees count ---
$total_employees = 0;
$sql_employees = "SELECT COUNT(*) as total FROM employees";
$result_employees = $conn->query($sql_employees);
if ($result_employees && $result_employees->num_rows > 0) {
    $row = $result_employees->fetch_assoc();
    $total_employees = $row['total'];
}

$conn->close();

// --- Variables for dashboard cards ---
$total_gamefowls = $total_chickens;
$mortality_rate = number_format($mortality_percentage, 2);
$sales_month = rand(20, 100); // placeholder
$employees_count = $total_employees; // now fetched from DB

// --- Current page highlight ---
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gamefowl Farm Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
:root {
    --green1: #a8d5a2;
    --green2: #81c784;
    --green3: #4caf50;
    --green4: #388e3c;
    --green5: #2e7d32;
    --green6: #1b5e20;
}
body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: var(--bg);
    color: #1a1a1a;
}

/* Header */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 20px;
    background: var(--green2);
    color: #ffffffff;
}
.farm-logo {
    display: flex;
    align-items: center;
    gap: 12px;
}
.farm-logo img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.6);
    background: #fff;
}
.farm-name { font-size: 20px; font-weight: 700; }
.admin-info { text-align: right; }
.admin-name { display: block; font-weight: 600; }
.admin-role { display: block; font-size: 13px; opacity: 0.9; }

/* Layout */
.layout { display: flex; }

/* Sidebar */
.sidebar {
    width: 260px;
    background: #388e3c;
    color: #a8d5a2;
    display: flex;
    flex-direction: column;
    padding: 16px 12px;
    min-height: calc(100vh - 64px);
    transition: width 0.3s;
}
.sidebar.collapsed {
    width: 80px;
}
.sidebar.collapsed .nav a span {
    display: none;
}
.sidebar.collapsed .nav-section-title {
    display: none;
}
.toggle-btn {
    cursor: pointer;
    font-size: 20px;
    padding: 10px;
    color: white;
    background: none;
    border: none;
    margin-bottom: 20px;
    text-align: left;
}
.nav { flex: 1; padding: 6px 6px 12px; }
.nav a {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: #fff;
    padding: 10px 12px;
    margin: 4px 6px;
    border-radius: 8px;
    transition: background 0.2s ease;
}
.nav a.active, .nav a:hover { background: rgba(255, 255, 255, 0.25); }
.nav-section-title {
    font-size: 11px; 
    letter-spacing: 1.5px; 
    opacity: 0.9; 
    margin: 14px 6px 6px;
}

/* Main content */
.main {
    flex: 1;
    padding: 20px;
    background: linear-gradient(rgba(177, 221, 158, 0.30), rgba(177, 221, 158, 0.30)),
                url("image/cover.jpg") no-repeat center center;
    background-size: cover;
}

.cards { 
    display: grid; 
    grid-template-columns: repeat(4, 1fr); 
    gap: 16px; 
}
.card-button {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--green3);
    color: #fff;
    border-radius: 12px;
    padding: 18px;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
    justify-content: center; 
}
.card-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.card-button i, .card-button .chicken-icon {
    font-size: 36px;
    width: 36px;
    height: 36px;
    flex-shrink: 0;
}
.card-button .text {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.card-button .label { font-size: 13px; opacity: 0.95; margin-bottom: 6px; }
.card-button .value { font-size: 28px; font-weight: 800; }

.content-row { display: grid; grid-template-columns: 2fr 1.3fr; gap: 18px; margin-top: 18px; }
.panel {
    background: #fff; 
    border-radius: 12px; 
    padding: 18px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.07);
}
.panel h3 { margin: 0 0 10px; font-size: 16px; color: #003366; }

@media (max-width: 980px) {
    .layout { flex-direction: column; }
    .sidebar { width: 100%; min-height: auto; }
    .main { padding: 10px; }
    .cards { grid-template-columns: 1fr; }
    .content-row { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<!-- Header -->
<header class="header">
    <div class="farm-logo">
        <img src="image/logo.png" alt="Farm Logo" />
        <span class="farm-name">Bicol Heritage Gamefarm</span>
    </div>
</header>

<!-- Layout -->
<div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <button class="toggle-btn" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <nav class="nav">
            <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>

            <div class="nav-section-title">PROFILE</div>
            <a href="lineage.php" class="<?php echo ($current_page == 'lineage.php') ? 'active' : ''; ?>">
                <i class="fas fa-dna"></i> <span>Lineage</span>
            </a>
            
            <div class="nav-section-title">FEEDS</div>
            <a href="purchase.php" class="<?php echo ($current_page == 'purchase.php') ? 'active' : ''; ?>">
                <i class="fas fa-cart-plus"></i> <span>Purchase</span>
            </a>
            <a href="consumption.php" class="<?php echo ($current_page == 'consumption.php') ? 'active' : ''; ?>">
                <i class="fas fa-bowl-food"></i> <span>Consumption</span>
            </a>

            <div class="nav-section-title">HEALTH</div>
            <a href="mortality.php" class="<?php echo ($current_page == 'mortality.php') ? 'active' : ''; ?>">
                <i class="fas fa-skull-crossbones"></i> <span>Mortality</span>
            </a>
            <a href="disease.php" class="<?php echo ($current_page == 'disease.php') ? 'active' : ''; ?>">
                <i class="fas fa-virus"></i> <span>Disease</span>
            </a>
            <a href="cured.php" class="<?php echo ($current_page == 'cured.php') ? 'active' : ''; ?>">
                <i class="fas fa-notes-medical"></i> <span>Cured</span>
            </a>

            <div class="nav-section-title">REPORT</div>
            <a href="sales.php" class="<?php echo ($current_page == 'sales.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> <span>Sales</span>
            </a>
            <a href="payroll.php" class="<?php echo ($current_page =='payroll.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-money-check-dollar"></i> <span>Payroll</span>
            </a>
        </nav>
    </aside>
 
    <!-- Main Dashboard -->
    <main class="main">
        <section class="cards">
            <a href="lineage.php" class="card-button">
                <svg class="chicken-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="white">
                    <path d="M49 24c0-2-2-4-4-4s-4 2-4 4-2 6-6 6-6-4-6-6c0-1-1-2-2-2s-2 1-2 2c0 2-2 6-6 6s-6-4-6-6 0-4 0-4-2-2-4-2-4 2-4 4c0 2-1 4 1 6s6 4 6 8-2 10-2 10 2 0 4 2 2 6 2 6 2-2 4-2 4 2 4 2 0-4 2-6 4-2 4-2-2-6-2-10 4-6 6-8 1-4 1-6z"/>
                </svg>
                <div class="text">
                    <div class="label">NO. OF GAMEFOWL</div>
                    <div class="value"><?php echo $total_gamefowls; ?></div>
                </div>
            </a>
            <a href="mortality.php" class="card-button">
                <i class="fas fa-skull-crossbones"></i>
                <div class="text">
                    <div class="label">MORTALITY RATE</div>
                    <div class="value"><?php echo $mortality_rate; ?>%</div>
                </div>
            </a>
            <a href="sales.php" class="card-button">
                <i class="fas fa-dollar-sign"></i>
                <div class="text">
                    <div class="label">SOLD FOR THE MONTH</div>
                    <div class="value"><?php echo $sales_month; ?></div>
                </div>
            </a>
            <a href="employees.php" class="card-button">
                <i class="fas fa-users"></i>
                <div class="text">
                    <div class="label">NO. OF EMPLOYEES</div>
                    <div class="value"><?php echo $employees_count; ?></div>
                </div>
            </a>
        </section>

        <section class="content-row">
            <div class="panel">
                <h3>Sales for the Month</h3>
                <canvas id="salesLine"></canvas>
            </div>
            <div class="panel">
                <h3>Bloodline</h3>
                <canvas id="bloodPie"></canvas>
            </div>
        </section>
    </main>
</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

// Line Chart
new Chart(document.getElementById('salesLine'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun'],
        datasets:[{label:'Sales', data:[30,44,40,46,60,68], borderColor:'orange', fill:false, tension:0.3, pointRadius:4}]
    },
    options:{ plugins:{ legend:{ display:false } } }
});

// Bloodline Chart
const bloodlineLabels = <?php echo json_encode($bloodline_labels); ?>;
const bloodlineCounts = <?php echo json_encode($bloodline_counts); ?>;
new Chart(document.getElementById('bloodPie'), {
    type: 'doughnut',
    data: { 
        labels: bloodlineLabels, 
        datasets:[{ 
            data: bloodlineCounts, 
            backgroundColor:['#ff4d4d','#ffa64d','#8b0000','#4d79ff','#4db8ff','#33cc33'] 
        }] 
    },
    options: { plugins:{ legend:{ position:'bottom' } }, cutout:'55%' }
});
</script>
</body>
</html>
