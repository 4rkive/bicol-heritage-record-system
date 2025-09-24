<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch mortality records with optional month/year/branch filter
$where = [];
if (!empty($_GET['month'])) $where[] = "MONTH(m.date) = ".intval($_GET['month']);
if (!empty($_GET['year'])) $where[] = "YEAR(m.date) = ".intval($_GET['year']);
if (!empty($_GET['branch_id'])) $where[] = "m.branch_id = ".intval($_GET['branch_id']);

$sql = "SELECT m.*,
               bl.bloodline_name,
               b.branch_name
        FROM mortality m
        LEFT JOIN bloodline bl ON m.bloodline = bl.bloodline_id
        LEFT JOIN branches b ON m.branch_id = b.branch_id";
if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY m.date DESC";
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
  <title>Gamefowl Mortality Records</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    /* --- your styles unchanged --- */
    :root { --green1:#a8d5a2;--green2:#81c784;--green3:#4caf50;--green4:#388e3c;--green5:#2e7d32;--green6:#1b5e20;}
    body { margin:0; font-family:Arial,Helvetica,sans-serif; background:var(--green1); color:#333;}
    .header { display:flex; justify-content:space-between; align-items:center; padding:6px 20px; background:var(--green2); color:#fff;}
    .farm-logo { display:flex; align-items:center; gap:12px;}
    .farm-logo img { width:50px; height:50px; object-fit:cover; border-radius:50%; border:2px solid rgba(255,255,255,0.6); background:#fff;}
    .farm-name { font-size:20px; font-weight:700;}
    .layout { display:flex;}
    .sidebar { width:260px; background:#388e3c; color:#a8d5a2; display:flex; flex-direction:column; padding:16px 12px; min-height:calc(100vh - 64px); transition:width 0.3s;}
    .sidebar.collapsed { width:80px;}
    .sidebar.collapsed .nav a span, .sidebar.collapsed .nav-section-title { display:none;}
    .toggle-btn { cursor:pointer; font-size:20px; padding:10px; color:white; background:none; border:none; margin-bottom:20px; text-align:left;}
    .nav { flex:1; padding:6px 6px 12px;}
    .nav a { display:flex; align-items:center; gap:10px; text-decoration:none; color:#fff; padding:10px 12px; margin:4px 6px; border-radius:8px; transition:background 0.2s ease;}
    .nav a.active, .nav a:hover { background:rgba(255,255,255,0.25);}
    .nav-section-title { font-size:11px; letter-spacing:1.5px; opacity:0.9; margin:14px 6px 6px;}
    .main { flex:1; padding:20px; background:linear-gradient(rgba(177,221,158,0.40), rgba(177,221,158,0.40)), url("image/cover.jpg") no-repeat center center; background-size:cover;}
    .page-title { text-align:center; margin:0; font-size:28px; font-weight:700; margin-bottom:10px; color:#18392b;}
    .header-controls { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;}
    .filter-controls form { display:flex; gap:10px;}
    .filter-controls select, .filter-controls button { padding:6px;}
    .add-btn button { background:var(--green3); color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer;}
    .add-btn button:hover { background:var(--green5);}
    .stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin:25px 0;}
    .stats .card { padding:20px; border-radius:12px; text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.1); transition:0.3s; color:white;}
    .stats .card h2 { margin:8px 0; font-size:22px;}
    .stats .card p { margin:0; font-size:14px;}
    table { width:100%; border-collapse:collapse; margin-top:15px; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.05);}
    table th, table td { padding:12px; border-bottom:1px solid #eee; text-align:center; font-size:14px;}
    table th { background:var(--green3); color:#fff; text-transform:uppercase; font-size:13px; letter-spacing:1px;}
    table tr:nth-child(even) { background:#f9f9f9;}
    table tr:hover td { background:#f1f8e9;}
    .btn-edit, .btn-delete { padding:6px 12px; border-radius:6px; text-decoration:none; font-size:13px; margin:0 3px; display:inline-block;}
    .btn-edit { background:#388e3c; color:white;}
    .btn-edit:hover { background:#2e7d32;}
    .btn-delete { background:#d9534f; color:white;}
    .btn-delete:hover { background:#b52b27;}
    .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;}
    .modal form { background:#fff; padding:25px; border-radius:12px; display:flex; flex-direction:column; gap:12px; width:320px; box-shadow:0 6px 16px rgba(0,0,0,0.2);}
    .modal h2 { margin-top:0; text-align:center; color:#1b5e20;}
    .modal input, .modal select, .modal button { padding:8px; border-radius:6px; border:1px solid #ccc; font-size:14px;}
    .modal button[type="submit"] { background:#4caf50; border:none; color:#fff; cursor:pointer;}
    .modal button[type="submit"]:hover { background:#2e7d32;}
    .modal button[type="button"] { background:#ccc; border:none; cursor:pointer;}
    .modal button[type="button"]:hover { background:#aaa; color:#fff;}
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
            <div class="nav-section-title">LINEAGE</div>
            <a href="lineage.php" class="<?php echo ($current_page=='lineage.php') ? 'active' : ''; ?>"><i class="fas fa-dna"></i> <span>Lineage</span></a>
            <div class="nav-section-title">FEEDS</div>
            <a href="purchase.php" class="<?php echo ($current_page=='purchase.php') ? 'active' : ''; ?>"><i class="fas fa-cart-plus"></i> <span>Purchase</span></a>
            <a href="consumption.php" class="<?php echo ($current_page=='consumption.php') ? 'active' : ''; ?>"><i class="fas fa-bowl-food"></i> <span>Consumption</span></a>
            <div class="nav-section-title">HEALTH</div>
            <a href="mortality.php" class="<?php echo ($current_page=='mortality.php') ? 'active' : ''; ?>"><i class="fas fa-skull-crossbones"></i> <span>Mortality</span></a>
            <a href="disease.php" class="<?php echo ($current_page=='disease.php') ? 'active' : ''; ?>"><i class="fas fa-virus"></i> <span>Disease</span></a>
            <a href="cured.php" class="<?php echo ($current_page=='cured.php') ? 'active' : ''; ?>"><i class="fas fa-notes-medical"></i> <span>Cured</span></a>
            <div class="nav-section-title">REPORT</div>
            <a href="sales.php" class="<?php echo ($current_page=='sales.php') ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> <span>Sales</span></a>
            <a href="employee.php" class="<?php echo ($current_page=='employee.php') ? 'active' : ''; ?>"><i class="fas fa-users"></i> <span>Employee</span></a>
        </nav>
    </aside>

    <!-- Main -->
    <main class="main">
        <h1 class="page-title">Mortality Records</h1>
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
                            $years = $conn->query("SELECT DISTINCT YEAR(date) as yr FROM mortality ORDER BY yr DESC");
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

        <!-- Stats -->
        <section class="stats">
            <div class="card" style="background:#2e7d32;">🐓 
                <?php 
                    $res = $conn->query("SELECT COUNT(*) as total FROM mortality");
                    $row = $res->fetch_assoc();
                    echo "<h2>".$row['total']."</h2><p>Total Deaths</p>";
                ?>
            </div>
            <div class="card" style="background:#388e3c;">💉 
                <?php 
                    $res = $conn->query("SELECT cause_of_death, COUNT(*) as cnt FROM mortality GROUP BY cause_of_death ORDER BY cnt DESC LIMIT 1");
                    if ($res->num_rows > 0) {
                        $row = $res->fetch_assoc();
                        echo "<h2>".$row['cause_of_death']."</h2><p>Most Common Cause</p>";
                    } else {
                        echo "<h2>—</h2><p>No Data</p>";
                    }
                ?>
            </div>
            <div class="card" style="background:#4caf50;">🧬 
                <?php 
                    $res = $conn->query("SELECT bl.bloodline_name, COUNT(*) as cnt 
                                         FROM mortality m 
                                         LEFT JOIN bloodline bl ON m.bloodline = bl.bloodline_id 
                                         GROUP BY m.bloodline 
                                         ORDER BY cnt DESC LIMIT 1");
                    if ($res->num_rows > 0) {
                        $row = $res->fetch_assoc();
                        echo "<h2>".$row['bloodline_name']."</h2><p>Top Bloodline</p>";
                    } else {
                        echo "<h2>—</h2><p>No Data</p>";
                    }
                ?>
            </div>
        </section>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Bloodline</th>
                    <th>Wing Band</th>
                    <th>Leg Band</th>
                    <th>Cause of Death</th>
                    <th>Branch</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>".$row['date']."</td>
                            <td>".$row['bloodline_name']."</td>
                            <td>".$row['wing_band']."</td>
                            <td>".$row['leg_band']."</td>
                            <td>".$row['cause_of_death']."</td>
                            <td>".$row['branch_name']."</td>
                            <td>
                                <a class='btn-edit' href='edit.php?id=".$row['id']."'>Edit</a>
                                <a class='btn-delete' href='delete.php?id=".$row['id']."' onclick=\"return confirm('Are you sure you want to delete this record?');\">Delete</a>
                            </td>
                        </tr>";
                    }
                ?>
            </tbody>
        </table>
    </main>
</div>

<!-- Modal -->
<div id="addForm" class="modal">
    <form action="insert_mortality.php" method="post">
        <h2>Add Record</h2>
        <label>Date:</label>
        <input type="date" name="date" required>

        <!-- Bloodline Dropdown -->
        <label>Bloodline:</label>
        <select name="bloodline" required>
            <option value="">-- Select Bloodline --</option>
            <?php 
                $bloodlines = $conn->query("SELECT * FROM bloodline ORDER BY bloodline_name ASC");
                while ($bl = $bloodlines->fetch_assoc()) {
                    echo "<option value='".$bl['bloodline_id']."'>".$bl['bloodline_name']."</option>";
                }
            ?>
        </select>

        <label>Wing Band:</label>
        <input type="text" name="wing_band">
        <label>Leg Band:</label>
        <input type="text" name="leg_band">
        <label>Cause of Death:</label>
        <input type="text" name="cause_of_death" required>

        <!-- Branch Dropdown -->
        <label>Branch:</label>
        <select name="branch_id" required>
            <option value="">-- Select Branch --</option>
            <?php 
                $branches = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");
                while ($b = $branches->fetch_assoc()) {
                    echo "<option value='".$b['branch_id']."'>".$b['branch_name']."</option>";
                }
            ?>
        </select>

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
</script>

</body>
</html>
