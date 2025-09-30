<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Detect current page for sidebar highlight
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cured Disease Records</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
:root {
    --green1: #a8d5a2; /* lightest */
    --green2: #81c784;
    --green3: #4caf50;
    --green4: #388e3c;
    --green5: #2e7d32;
    --green6: #1b5e20;
}
body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: var(--green1);
    color: #1a1a1a;
}

/* Header */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 20px;
    background: var(--green2);
    color: #fff;
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
.sidebar.collapsed .nav a span,
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

/* Main */
.main {
    flex: 1;
    padding: 20px;
    background: linear-gradient(rgba(177, 221, 158, 0.40), rgba(177, 221, 158, 0.40)),
                url("image/cover.jpg") no-repeat center center;
    background-size: cover;
}
.page-title {
    text-align: center;
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 10px;
    color: #18392b;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0,0,0,0.07);
}
table th, table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}
table th {
    background: var(--green3);
    color: #fff;
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

    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <button class="toggle-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="nav">
                <a href="dashboard.php" class="<?php echo ($current_page=='dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>

                <div class="nav-section-title">LINEAGE</div>
                <a href="lineage.php" class="<?php echo ($current_page=='lineage.php') ? 'active' : ''; ?>">
                    <i class="fas fa-dna"></i> <span>Lineage</span>
                </a>

                <div class="nav-section-title">FEEDS</div>
                <a href="purchase.php" class="<?php echo ($current_page=='purchase.php') ? 'active' : ''; ?>">
                    <i class="fas fa-cart-plus"></i> <span>Purchase</span>
                </a>
                <a href="consumption.php" class="<?php echo ($current_page=='consumption.php') ? 'active' : ''; ?>">
                    <i class="fas fa-bowl-food"></i> <span>Consumption</span>
                </a>

                <div class="nav-section-title">HEALTH</div>
                <a href="mortality.php" class="<?php echo ($current_page=='mortality.php') ? 'active' : ''; ?>">
                    <i class="fas fa-skull-crossbones"></i> <span>Mortality</span>
                </a>
                <a href="disease.php" class="<?php echo ($current_page=='disease.php') ? 'active' : ''; ?>">
                    <i class="fas fa-virus"></i> <span>Disease</span>
                </a>
                <a href="cured.php" class="<?php echo ($current_page=='cured.php') ? 'active' : ''; ?>">
                    <i class="fas fa-notes-medical"></i> <span>Cured</span>
                </a>

                <div class="nav-section-title">REPORT</div>
                <a href="sales.php" class="<?php echo ($current_page=='sales.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> <span>Sales</span>
                </a>
                <a href="employee.php" class="<?php echo ($current_page=='employee.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> <span>Employee</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <h1 class="page-title">Cured Records</h1>
            <table>
                <thead>
                    <tr>
                        <th>Date Detected</th>
                        <th>Bloodline</th>
                        <th>Wing Band</th>
                        <th>Leg Band</th>
                        <th>Disease</th>
                        <th>Date Cured</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $sql = "SELECT * FROM disease_records WHERE status='Cured' ORDER BY date_cured DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>".$row['date_detected']."</td>
                                    <td>".$row['bloodline']."</td>
                                    <td>".$row['wingband']."</td>
                                    <td>".$row['legband']."</td>
                                    <td>".$row['disease_name']."</td>
                                    <td>".$row['date_cured']."</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center;'>No cured records found</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </main>
    </div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}
</script>
</body>
</html>
