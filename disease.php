<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch disease records with optional month/year/branch filter
$where = [];
if (!empty($_GET['month'])) $where[] = "MONTH(d.date_detected) = ".intval($_GET['month']);
if (!empty($_GET['year'])) $where[] = "YEAR(d.date_detected) = ".intval($_GET['year']);
if (!empty($_GET['branch_id'])) $where[] = "d.branch_id = ".intval($_GET['branch_id']);

$sql = "SELECT d.*
        FROM disease_records d";
if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY d.date_detected DESC";
$result = $conn->query($sql);

// Detect current page
$current_page = basename($_SERVER['PHP_SELF']);

// Save current branch filter so the modal form can use it
$selected_branch = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Disease Records</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
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
      font-family: Arial, Helvetica, sans-serif;
      background: var(--green1);
      color: #333;
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
    .sidebar.collapsed { width: 80px; }
    .sidebar.collapsed .nav a span,
    .sidebar.collapsed .nav-section-title { display: none; }
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
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #18392b;
    }
    .header-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .filter-controls form { display: flex; gap: 10px; }
    .filter-controls select, .filter-controls button { padding: 6px; }
    .add-btn button {
        background: var(--green3);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
    }
    .add-btn button:hover { background: var(--green5); }

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    table th, table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        text-align: center;
        font-size: 14px;
    }
    table th {
        background: var(--green3);
        color: #fff;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 1px;
    }
    table tr:nth-child(even) { background: #f9f9f9; }
    table tr:hover td { background: #f1f8e9; }

    .btn-mark {
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        margin: 0 3px;
        display: inline-block;
        background: #388e3c;
        color: white;
    }
    .btn-mark:hover { background: #2e7d32; }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
    }
    .modal form {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 320px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    }
    .modal h2 {
        margin-top: 0;
        text-align: center;
        color: #1b5e20;
    }
    .modal input, .modal button {
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 14px;
    }
    .modal button[type="submit"] {
        background: #4caf50;
        border: none;
        color: #fff;
        cursor: pointer;
    }
    .modal button[type="submit"]:hover { background: #2e7d32; }
    .modal button[type="button"] {
        background: #ccc;
        border: none;
        cursor: pointer;
    }
    .modal button[type="button"]:hover {
        background: #aaa;
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

    <!-- Main -->
    <main class="main">
        <h1 class="page-title">Disease Records</h1>
        <div class="header-controls">
            <div class="filter-controls">
    <form method="GET">
        <select name="month" onchange="this.form.submit()">
            <option value="">All Months</option>
            <?php 
                for ($m=1; $m<=12; $m++) {
                    $selected = (isset($_GET['month']) && $_GET['month']==$m) ? "selected" : "";
                    echo "<option value='$m' $selected>".date("F", mktime(0,0,0,$m,1))."</option>";
                }
            ?>
        </select>
        <select name="year" onchange="this.form.submit()">
            <option value="">All Years</option>
            <?php 
                $years = $conn->query("SELECT DISTINCT YEAR(date_detected) as yr FROM disease_records ORDER BY yr DESC");
                while ($y = $years->fetch_assoc()) {
                    $selected = (isset($_GET['year']) && $_GET['year']==$y['yr']) ? "selected" : "";
                    echo "<option value='".$y['yr']."' $selected>".$y['yr']."</option>";
                }
            ?>
        </select>
        <select name="branch_id" onchange="this.form.submit()">
            <option value="">All Branches</option>
            <?php 
                $branches = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");
                while ($b = $branches->fetch_assoc()) {
                    $selected = (isset($_GET['branch_id']) && $_GET['branch_id']==$b['branch_id']) ? "selected" : "";
                    echo "<option value='".$b['branch_id']."' $selected>".$b['branch_name']."</option>";
                }
            ?>
        </select>
    </form>
</div>
            <div class="add-btn">
                <button onclick="openModal()">➕ Add Record</button>
            </div>
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th>Date Detected</th>
                    <th>Bloodline</th>
                    <th>Wing Band</th>
                    <th>Leg Band</th>
                    <th>Disease Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>".$row['date_detected']."</td>
                            <td>".$row['bloodline']."</td>
                            <td>".$row['wingband']."</td>
                            <td>".$row['legband']."</td>
                            <td>".$row['disease_name']."</td>
                            <td>".$row['status']."</td>
                        </tr>";
                    }
                ?>
            </tbody>
        </table>
    </main>
</div>

<!-- Modal -->
<div id="addForm" class="modal">
    <form action="insert_disease.php" method="post" onsubmit="return validateBranch()">
        <h2>Add Disease Record</h2>
        <label>Date Detected:</label>
        <input type="date" name="date_detected" required>
        <label>Bloodline:</label>
        <input type="text" name="bloodline" required>
        <label>Wing Band:</label>
        <input type="text" name="wingband">
        <label>Leg Band:</label>
        <input type="text" name="legband">
        <label>Disease Name:</label>
        <input type="text" name="disease_name" required>

        <!-- Hidden branch_id based on filter -->
        <input type="hidden" name="branch_id" value="<?php echo $selected_branch; ?>">

        <button type="submit">Save</button>
        <button type="button" onclick="closeModal()">Cancel</button>
    </form>
</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}
function openModal() {
    document.getElementById("addForm").style.display = "flex";
}
function closeModal() {
    document.getElementById("addForm").style.display = "none";
}
function validateBranch() {
    const branchId = document.querySelector("input[name='branch_id']").value;
    if (!branchId) {
        alert("⚠️ Please filter a branch first before adding a record.");
        return false;
    }
    return true;
}
</script>

</body>
</html>