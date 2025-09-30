<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Detect current page for sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// Get filters
$branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Records</title>
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
    body { margin: 0; font-family: Arial, Helvetica, sans-serif; background: var(--green1); color: #333; }
    header { background: var(--green3); padding: 10px 20px; display: flex; align-items: center; color: white; }
    .farm-logo { display: flex; align-items: center; gap: 12px; }
    .farm-logo img { width: 50px; height: 50px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.6); background: #fff; }
    .farm-name { font-size: 20px; font-weight: 700; }

    .layout { display: flex; }
    .sidebar { width: 240px; background: var(--green4); color: #fff; display: flex; flex-direction: column; padding: 16px 12px; min-height: calc(100vh - 64px); }
    .nav a { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; padding: 10px 12px; margin: 4px 6px; border-radius: 8px; }
    .nav a.active, .nav a:hover { background: rgba(255, 255, 255, 0.25); }
    .nav-section-title { font-size: 11px; letter-spacing: 1.5px; margin: 14px 6px 6px; opacity: 0.9; }

    .main-content { flex: 1; padding: 20px; background: linear-gradient(rgba(177,221,158,0.40), rgba(177,221,158,0.40)), url("image/cover.jpg") no-repeat center center; background-size: cover; min-height: 100vh; }
    .page-title { text-align: center; font-size: 28px; font-weight: 700; margin-bottom: 20px; color: #18392b; }

    .header-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .filter-controls select { padding: 6px; border-radius: 6px; border: 1px solid #ccc; }
    .add-btn button { background: var(--green3); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; margin-left: 5px; }
    .add-btn button:hover { background: var(--green5); }

    table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    table th, table td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; font-size: 14px; }
    table th { background: var(--green3); color: #fff; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; }
    table tr:nth-child(even) { background: #f9f9f9; }

    /* Modal */
    .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
    .modal-content { background: #fff; padding: 25px; border-radius: 12px; width: 400px; }
    .modal-content h3 { text-align: center; margin-top: 0; color: #1b5e20; }
    .modal-content label { font-weight: bold; margin-top: 8px; display: block; }
    .modal-content input, .modal-content select { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; }
    .modal-actions { margin-top: 15px; text-align: right; }
    .modal-actions button { padding: 8px 12px; border: none; border-radius: 6px; cursor: pointer; }
    .save-btn { background: var(--green3); color: #fff; }
    .save-btn:hover { background: var(--green5); }
    .cancel-btn { background: #ccc; }
    .cancel-btn:hover { background: #aaa; color: #fff; }
    </style>
</head>
<body>

<header>
    <div class="farm-logo">
        <img src="image/logo.png" alt="Farm Logo" />
        <span class="farm-name">Bicol Heritage Gamefarm</span>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <nav class="nav">
            <a href="dashboard.php" class="<?php echo ($current_page=='dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
            <a href="gallery.php" class="<?php echo ($current_page=='gallery.php') ? 'active' : ''; ?>"><i class="fas fa-images"></i> <span>Gallery</span></a>
            <div class="nav-section-title">BREEDING</div>
            <a href="lineage.php" class="<?php echo ($current_page=='lineage.php') ? 'active' : ''; ?>"><i class="fas fa-dna"></i> <span>Breeding</span></a>
            <div class="nav-section-title">FEEDS</div>
            <a href="purchase.php" class="<?php echo ($current_page=='purchase.php') ? 'active' : ''; ?>"><i class="fas fa-cart-plus"></i> <span>Purchase</span></a>
            <div class="nav-section-title">HEALTH</div>
            <a href="mortality.php" class="<?php echo ($current_page=='mortality.php') ? 'active' : ''; ?>"><i class="fas fa-skull-crossbones"></i> <span>Mortality</span></a>
            <a href="disease.php" class="<?php echo ($current_page=='disease.php') ? 'active' : ''; ?>"><i class="fas fa-virus"></i> <span>Disease & Cured</span></a>
            <div class="nav-section-title">REPORT</div>
            <a href="sales.php" class="<?php echo ($current_page=='sales.php') ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> <span>Sales</span></a>
            <a href="payroll.php" class="<?php echo ($current_page=='payroll.php') ? 'active' : ''; ?>"><i class="fas fa-money-check-alt"></i> <span>Payroll</span></a>
            <a href="employee.php" class="<?php echo ($current_page=='employee.php') ? 'active' : ''; ?>"><i class="fas fa-users"></i> <span>Employee</span></a>
        </nav>
    </aside>

    <div class="main-content">
        <h2 class="page-title">Payroll Records</h2>

        <div class="header-controls">
            <!-- Filter -->
            <div class="filter-controls">
                <form method="GET">
                    <select name="branch_id" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        <?php 
                        $branches = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");
                        while ($b = $branches->fetch_assoc()) {
                            $selected = ($branch_id == $b['branch_id']) ? "selected" : "";
                            echo "<option value='".$b['branch_id']."' $selected>".$b['branch_name']."</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>

            <!-- Add Payroll Button -->
            <div class="add-btn">
                <button onclick="document.getElementById('addForm').style.display='flex'">Add Payroll</button>
            </div>
        </div>

        <!-- Payroll Table -->
        <table>
            <tr>
                <th rowspan="2">Employee</th>
                <th rowspan="2">Branch</th>
                <th rowspan="2">No. of Days</th>
                <th rowspan="2">Total Monthly Salary</th>
                <th colspan="6">Deduction</th>
            </tr>
            <tr>
                <th>Cash Advance 1</th>
                <th>SSS</th>
                <th>PhilHealth</th>
                <th>Pag-IBIG</th>
                <th>Cash Advance 2</th>
                <th>Total Amount Received</th>
            </tr>
            <?php
            $sql = "SELECT p.*, e.employee_name AS employee_name, b.branch_name
                    FROM payroll p
                    JOIN employees e ON p.employee_id = e.employee_id
                    JOIN branches b ON p.branch_id = b.branch_id
                    WHERE 1=1";

            if (!empty($branch_id)) {
                $sql .= " AND p.branch_id = $branch_id";
            }

            $sql .= " ORDER BY p.payroll_id DESC";

            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>".$row['employee_name']."</td>
                            <td>".$row['branch_name']."</td>
                            <td>".$row['no_of_days']."</td>
                            <td>".$row['total_monthly_salary']."</td>
                            <td>".$row['cash_advance1']."</td>
                            <td>".$row['sss']."</td>
                            <td>".$row['philhealth']."</td>
                            <td>".$row['pagibig']."</td>
                            <td>".$row['cash_advance2']."</td>
                            <td>".$row['total_amount_received']."</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No payroll records found.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

<!-- Add Payroll Modal -->
<div id="addForm" class="modal">
    <div class="modal-content">
        <h3>Add Payroll</h3>
        <form method="POST" action="insert_payroll.php">
            <label>Employee:</label>
            <select name="employee_id" required>
                <?php
                $employees = $conn->query("SELECT * FROM employees ORDER BY employee_name ASC");
                while ($emp = $employees->fetch_assoc()) {
                    echo "<option value='".$emp['employee_id']."'>".$emp['employee_name']."</option>";
                }
                ?>
            </select>

            <label>Branch:</label>
            <select name="branch_id" required>
                <?php
                $branches = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");
                while ($b = $branches->fetch_assoc()) {
                    echo "<option value='".$b['branch_id']."'>".$b['branch_name']."</option>";
                }
                ?>
            </select>

            <label>No. of Days:</label>
            <input type="number" name="no_of_days">

            <label>Total Monthly Salary:</label>
            <input type="number" step="0.01" name="total_monthly_salary">

            <label>Cash Advance 1:</label>
            <input type="number" step="0.01" name="cash_advance1">

            <label>SSS:</label>
            <input type="number" step="0.01" name="sss">

            <label>PhilHealth:</label>
            <input type="number" step="0.01" name="philhealth">

            <label>Pag-IBIG:</label>
            <input type="number" step="0.01" name="pagibig">

            <label>Cash Advance 2:</label>
            <input type="number" step="0.01" name="cash_advance2">

            <label>Total Amount Received:</label>
            <input type="number" step="0.01" name="total_amount_received">

            <div class="modal-actions">
                <button type="submit" class="save-btn">Save</button>
                <button type="button" class="cancel-btn" onclick="document.getElementById('addForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
