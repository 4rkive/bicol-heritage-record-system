<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch employee records
$sql = "SELECT * FROM employees ORDER BY employee_id DESC";
$result = $conn->query($sql);

// Fetch total salary
$total_sql = "SELECT SUM(monthly_rate) AS total_salary FROM employees";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_salary = $total_row['total_salary'] ?? 0;

// Detect current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Records</title>
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
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.6);
        background: #fff;
        object-fit: cover;
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
    .nav { flex: 1; padding: 6px; }
    .nav a {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: #fff;
        padding: 10px 12px;
        margin: 4px 6px;
        border-radius: 8px;
    }
    .nav a.active, .nav a:hover { background: rgba(255, 255, 255, 0.25); }
    .nav-section-title {
        font-size: 11px; 
        letter-spacing: 1.5px; 
        margin: 14px 6px 6px;
    }

    /* Main */
    .main {
        flex: 1;
        padding: 20px;
        background: linear-gradient(rgba(177,221,158,0.4), rgba(177,221,158,0.4)),
                    url("image/cover.jpg") no-repeat center center;
        background-size: cover;
    }
    .page-title {
        text-align: center;
        margin: 0 0 15px;
        font-size: 28px;
        font-weight: 700;
        color: #18392b;
    }

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

    tfoot td {
        font-size: 15px;
        font-weight: bold;
        color: #2e7d32;
        border-top: 2px solid #388e3c;
        background: #f1f8e9;
    }

    .btn-edit, .btn-delete {
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        margin: 0 3px;
        display: inline-block;
    }
    .btn-edit { background: #388e3c; color: white; }
    .btn-edit:hover { background: #2e7d32; }
    .btn-delete { background: #d9534f; color: white; }
    .btn-delete:hover { background: #b52b27; }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top:0; left:0;
        width:100%; height:100%;
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
        width: 350px;
    }
    .modal h2 { text-align: center; margin: 0; color: #1b5e20; }
    .modal input, .modal button, .modal select {
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 14px;
    }
    .modal button[type="submit"] {
        background: #4caf50; border: none; color: #fff; cursor: pointer;
    }
    .modal button[type="submit"]:hover { background: #2e7d32; }
    .modal button[type="button"] {
        background: #ccc; border: none; cursor: pointer;
    }
    .modal button[type="button"]:hover { background: #aaa; color: #fff; }
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

    <!-- Main -->
    <main class="main">
        <h1 class="page-title">Employee Records</h1>
        <div class="add-btn">
            <button onclick="document.getElementById('addForm').style.display='flex'">➕ Add Employee</button>
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th>Area</th>
                    <th>Position</th>
                    <th>Address</th>
                    <th>Date of Birth</th>
                    <th>Contact</th>
                    <th>Date Hired</th>
                    <th>Separation Date</th>
                    <th>Monthly Rate</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>".$row['employee_id']."</td>
                            <td>".$row['employee_name']."</td>
                            <td>".$row['area']."</td>
                            <td>".$row['position']."</td>
                            <td>".$row['address']."</td>
                            <td>".$row['date_of_birth']."</td>
                            <td>".$row['contact_number']."</td>
                            <td>".$row['date_hired']."</td>
                            <td>".$row['separation_date']."</td>
                            <td>".number_format($row['monthly_rate'], 2)."</td>
                            <td>
                                <a class='btn-edit' href='edit_employee.php?id=".$row['employee_id']."'>Edit</a>
                                <a class='btn-delete' href='delete_employee.php?id=".$row['employee_id']."' onclick=\"return confirm('Are you sure you want to delete this record?');\">Delete</a>
                            </td>
                        </tr>";
                    }
                ?>
            </tbody>

            <!-- Total Row -->
            <tfoot>
                <tr>
                    <td colspan="9" style="text-align:right;">Total Salary:</td>
                    <td><?php echo number_format($total_salary, 2); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </main>
</div>

<!-- Modal -->
<div id="addForm" class="modal">
    <form action="insert_employee.php" method="post">
        <h2>Add Employee</h2>
        <label>Name:</label>
        <input type="text" name="employee_name" required>
        <label>Area:</label>
        <input type="text" name="area">
        <label>Position:</label>
        <input type="text" name="position">
        <label>Address:</label>
        <input type="text" name="address">
        <label>Date of Birth:</label>
        <input type="date" name="date_of_birth">
        <label>Contact:</label>
        <input type="text" name="contact_number">
        <label>Date Hired:</label>
        <input type="date" name="date_hired">
        <label>Separation Date:</label>
        <input type="date" name="separation_date">
        <label>Monthly Rate:</label>
        <input type="number" step="0.01" name="monthly_rate">
        <button type="submit">Save</button>
        <button type="button" onclick="document.getElementById('addForm').style.display='none'">Cancel</button>
    </form>
</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}
</script>
</body>
</html>
