<?php
include 'db.php';
session_start();

// Detect current page for sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// Current month and year
$current_month = date('m');
$current_year = date('Y');

// Total broodcocks
$sql_broodcocks = "SELECT COUNT(*) AS total_broodcocks FROM broodcocks";
$result_broodcocks = $conn->query($sql_broodcocks);
$total_broodcocks = ($result_broodcocks->num_rows > 0) ? $result_broodcocks->fetch_assoc()['total_broodcocks'] : 0;

// Mortality rate
$sql_mortality = "SELECT COUNT(*) AS total_mortality FROM mortality";
$result_mortality = $conn->query($sql_mortality);
$total_mortality = ($result_mortality->num_rows > 0) ? $result_mortality->fetch_assoc()['total_mortality'] : 0;
$mortality_rate = ($total_broodcocks > 0) ? ($total_mortality / $total_broodcocks) * 100 : 0;

// Sold for the current month (wingbands count)
$sql_sold = "
    SELECT COUNT(sw.id) AS sold_this_month
    FROM sale_wingbands sw
    INNER JOIN sale s ON sw.sale_id = s.sale_id
    WHERE MONTH(s.sale_date) = $current_month
      AND YEAR(s.sale_date) = $current_year
";
$result_sold = $conn->query($sql_sold);
$sold_this_month = ($result_sold->num_rows > 0) ? $result_sold->fetch_assoc()['sold_this_month'] : 0;

// Employees count
$sql_employees = "SELECT COUNT(*) AS total_employees FROM employees";
$result_employees = $conn->query($sql_employees);
$total_employees = ($result_employees->num_rows > 0) ? $result_employees->fetch_assoc()['total_employees'] : 0;

// Bloodline distribution
$sql_bloodline = "
    SELECT b.bloodline_name AS bloodline, COUNT(c.cock_id) AS count
    FROM bloodline b
    LEFT JOIN broodcocks c ON b.bloodline_id = c.bloodline_id
    GROUP BY b.bloodline_name
";
$result_bloodline = $conn->query($sql_bloodline);
$bloodlines = [];
$bloodline_counts = [];
while ($row = $result_bloodline->fetch_assoc()) {
    $bloodlines[] = $row['bloodline'];
    $bloodline_counts[] = $row['count'];
}

// Weekly sales totals (₱)
$sql_weekly_sales = "
    SELECT 
        (WEEK(sale_date, 1) - WEEK(DATE_SUB(sale_date, INTERVAL DAY(sale_date)-1 DAY), 1) + 1) AS week,
        SUM(amount) AS total_sales
    FROM sale
    WHERE MONTH(sale_date) = $current_month
      AND YEAR(sale_date) = $current_year
    GROUP BY week
    ORDER BY week
";
$result_weekly_sales = $conn->query($sql_weekly_sales);
$weeks = [];
$sales = [];
while ($row = $result_weekly_sales->fetch_assoc()) {
    $weeks[] = "Week " . $row['week'];
    $sales[] = $row['total_sales'];
}

$currentMonth = date('F', mktime(0, 0, 0, $current_month, 10)); // Converts numeric month to name

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            font-family: Arial, sans-serif;
            background: var(--green1);
            color: #333;
            height: 100vh;
            overflow: hidden;
        }
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
        .layout { display: flex; height: calc(100vh - 64px); }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--green4);
            color: var(--green1);
            display: flex;
            flex-direction: column;
            padding: 16px 12px;
            height: 100%;
            position: sticky;
            top: 0;
            flex-shrink: 0;
            overflow-y: auto;
        }
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .nav a span, .sidebar.collapsed .nav-section-title { display: none; }
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
            background: linear-gradient(rgba(177, 221, 158, 0.40), rgba(177, 221, 158, 0.40)),
                        url("image/cover.jpg") no-repeat center center;
            background-size: cover;
            height: 100%;
            overflow-y: auto;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        .page-title { text-align: center; margin: 0 0 20px 0; font-size: 28px; font-weight: 700; color: #18392b; }
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
        .chart-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .charts-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 20px; /* extra space from cards */
}


.chart-container {
    flex: 1 1 45%; /* responsive width */
    background: #fff;
    padding: 20px; /* keep padding same as cards */
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    min-width: 300px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start; /* chart starts from top */
    height: 500px; /* konti lang mas maikli kaysa sa card */
}
.chart-container h3 {
    margin: 0 0 10px 0;
    text-align: center;
}

.chart-container canvas {
    max-height: 90%;
}


    </style>
</head>
<body>
<header class="header">
    <div class="farm-logo">
        <img src="image/logo.png" alt="Farm Logo" />
        <span class="farm-name">Bicol Heritage Gamefarm</span>
    </div>
</header>

<div class="layout">
    <!-- Sidebar copied from sales.php -->
    <aside class="sidebar" id="sidebar">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <nav class="nav">
            <a href="dashboard.php" class="<?php echo ($current_page=='dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
            <div class="nav-section-title">BREEDING</div>
            <a href="lineage.php" class="<?php echo ($current_page=='lineage.php') ? 'active' : ''; ?>"><i class="fas fa-dna"></i> <span>Breeding</span></a>
            <div class="nav-section-title">FEEDS</div>
            <a href="purchase.php" class="<?php echo ($current_page=='purchase.php') ? 'active' : ''; ?>"><i class="fas fa-cart-plus"></i> <span>Purchase</span></a>
            <div class="nav-section-title">HEALTH</div>
            <a href="mortality.php" class="<?php echo ($current_page=='mortality.php') ? 'active' : ''; ?>"><i class="fas fa-skull-crossbones"></i> <span>Mortality</span></a>
            <a href="disease.php" class="<?php echo ($current_page=='disease.php') ? 'active' : ''; ?>"><i class="fas fa-virus"></i> <span>Disease & Cured</span></a>
            <div class="nav-section-title">REPORT</div>
            <a href="sales.php" class="<?php echo ($current_page=='sales.php') ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> <span>Sales</span></a>
            <a href="payroll.php" class="<?php echo ($current_page=='payroll.php') ? 'active' : ''; ?>"><i class="fas fa-money-check"></i> <span>Payroll</span></a>
            <a href="employee.php" class="<?php echo ($current_page=='employee.php') ? 'active' : ''; ?>"><i class="fas fa-users"></i> <span>Employee</span></a>
        </nav>
    </aside>
    <main class="main">
        <<section class="cards">
    <!-- No. of Gamefowl -->
    <a href="lineage.php" class="card-button">
        <svg class="chicken-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="white">
            <path d="M49 24c0-2-2-4-4-4s-4 2-4 4-2 6-6 6-6-4-6-6c0-1-1-2-2-2s-2 1-2 2c0 2-2 6-6 6s-6-4-6-6 0-4 0-4-2-2-4-2-4 2-4 4c0 2-1 4 1 6s6 4 6 8-2 10-2 10 2 0 4 2 2 6 2 6 2-2 4-2 4 2 4 2 0-4 2-6 4-2 4-2-2-6-2-10 4-6 6-8 1-4 1-6z"/>
        </svg>
        <div class="text">
            <div class="label">NO. OF GAMEFOWL</div>
            <div class="value"><?php echo $total_broodcocks; ?></div>
        </div>
    </a>

    <!-- Mortality Rate -->
    <a href="mortality.php" class="card-button">
        <i class="fas fa-skull-crossbones"></i>
        <div class="text">
            <div class="label">MORTALITY RATE</div>
            <div class="value"><?php echo number_format($mortality_rate, 2); ?>%</div>
        </div>
    </a>

    <!-- Sold for the Month -->
    <a href="sales.php" class="card-button">
        <i class="fas fa-dollar-sign"></i>
        <div class="text">
            <div class="label">SOLD FOR THE MONTH</div>
            <div class="value"><?php echo $sold_this_month; ?></div>
        </div>
    </a>

    <!-- No. of Employees -->
    <a href="employees.php" class="card-button">
        <i class="fas fa-users"></i>
        <div class="text">
            <div class="label">NO. OF EMPLOYEES</div>
            <div class="value"><?php echo $total_employees; ?></div>
        </div>
    </a>
</section>


<div class="charts-row">
<div class="chart-container">
    <h3>Sales for <?php echo $currentMonth; ?></h3>
    <p style="font-size:18px; text-align:center; margin-bottom:15px; margin-top: 5px;">
       <h3>Total Sales: ₱ <?php echo number_format(array_sum($sales), 2); ?> </h3>
    </p>
    <canvas id="salesChart"></canvas>
</div>



    <div class="chart-container">
        <h3>Bloodline Distribution</h3>
        <canvas id="bloodlineChart"></canvas>
    </div>
</div>

    </main>
</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

// Generate colors dynamically
function generateColors(count) {
    const colors = [];
    for(let i=0; i<count; i++){
        colors.push(`hsl(${i * (360 / count)}, 70%, 50%)`);
    }
    return colors;
}

// Bloodline Chart
const ctxBloodline = document.getElementById('bloodlineChart').getContext('2d');
new Chart(ctxBloodline, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($bloodlines); ?>,
        datasets: [{
            label: 'Bloodline Count',
            data: <?php echo json_encode($bloodline_counts); ?>,
            backgroundColor: generateColors(<?php echo count($bloodlines); ?>)
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

const ctxSales = document.getElementById('salesChart').getContext('2d');
new Chart(ctxSales, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($weeks); ?>, // weeks
        datasets: [{
            data: <?php echo json_encode($sales); ?>, // weekly sales
            fill: false,
            borderColor: 'rgba(75, 192, 192, 1)',
            tension: 0.2
        }]
    },
    options: {
        responsive: true,
        plugins: { 
            legend: { display: false },   // hides legend
            title: { display: false }     // hides chart title
        },
        scales: { y: { beginAtZero: true } }
    }
});


</script>
</body>
</html>
