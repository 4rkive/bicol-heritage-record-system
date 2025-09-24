<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Detect current page
$current_page = basename($_SERVER['PHP_SELF']);

// Month/year filter
$where = [];
if (!empty($_GET['month'])) $where[] = "MONTH(sale_date) = " . intval($_GET['month']);
if (!empty($_GET['year'])) $where[] = "YEAR(sale_date) = " . intval($_GET['year']);
$sql = "SELECT * FROM sale";
if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY sale_date DESC";
$result = $conn->query($sql);

// Total sales computation
$total_sales = 0;
$res_total = $conn->query("SELECT SUM(amount) as total FROM sale " . (!empty($where) ? "WHERE " . implode(" AND ", $where) : ""));
if ($res_total && $row_total = $res_total->fetch_assoc()) {
    $total_sales = $row_total['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales Records</title>
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
    body { margin: 0; font-family: Arial, Helvetica, sans-serif; background: var(--green1); color: #333; }
    .header { display: flex; justify-content: space-between; align-items: center; padding: 6px 20px; background: var(--green2); color: #fff; }
    .farm-logo { display: flex; align-items: center; gap: 12px; }
    .farm-logo img { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; border: 2px solid rgba(255,255,255,0.6); background: #fff; }
    .farm-name { font-size: 20px; font-weight: 700; }
    .layout { display: flex; }
    .sidebar { width: 260px; background: #388e3c; color: #a8d5a2; display: flex; flex-direction: column; padding: 16px 12px; min-height: calc(100vh - 64px); transition: width 0.3s; }
    .sidebar.collapsed { width: 80px; }
    .sidebar.collapsed .nav a span, .sidebar.collapsed .nav-section-title { display: none; }
    .toggle-btn { cursor: pointer; font-size: 20px; padding: 10px; color: white; background: none; border: none; margin-bottom: 20px; text-align: left; }
    .nav { flex: 1; padding: 6px 6px 12px; }
    .nav a { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; padding: 10px 12px; margin: 4px 6px; border-radius: 8px; transition: background 0.2s ease; }
    .nav a.active, .nav a:hover { background: rgba(255, 255, 255, 0.25); }
    .nav-section-title { font-size: 11px; letter-spacing: 1.5px; opacity: 0.9; margin: 14px 6px 6px; }
    .main { flex: 1; padding: 20px; background: linear-gradient(rgba(177, 221, 158, 0.40), rgba(177, 221, 158, 0.40)), url("image/cover.jpg") no-repeat center center; background-size: cover; }
    .page-title { text-align: center; margin: 0; font-size: 28px; font-weight: 700; margin-bottom: 10px; color: #18392b; }
    .header-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .filter-controls form { display: flex; gap: 10px; }
    .filter-controls select, .filter-controls button { padding: 6px; }
    .add-btn button { background: var(--green3); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
    .add-btn button:hover { background: var(--green5); }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    table th, table td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; font-size: 14px; }
    table th { background: var(--green3); color: #fff; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; }
    table tr:nth-child(even) { background: #f9f9f9; }
    table tr:hover td { background: #f1f8e9; }
    .btn-edit, .btn-delete { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; margin: 0 3px; display: inline-block; }
    .btn-edit { background: #388e3c; color: white; }
    .btn-edit:hover { background: #2e7d32; }
    .btn-delete { background: #d9534f; color: white; }
    .btn-delete:hover { background: #b52b27; }
    .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
    .modal form { background: #fff; padding: 25px; border-radius: 12px; display: flex; flex-direction: column; gap: 12px; width: 320px; box-shadow: 0 6px 16px rgba(0,0,0,0.2); }
    .modal h2 { margin-top: 0; text-align: center; color: #1b5e20; }
    .modal input, .modal button, .modal textarea { padding: 8px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; }
    .modal button[type="submit"] { background: #4caf50; border: none; color: #fff; cursor: pointer; }
    .modal button[type="submit"]:hover { background: #2e7d32; }
    .modal button[type="button"] { background: #ccc; border: none; cursor: pointer; }
    .modal button[type="button"]:hover { background: #aaa; color: #fff; }
    .wingband-row { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; }
    .wingband-row input { flex: 1; }
    .wingband-row button { background: #d9534f; color: white; border: none; padding: 3px 8px; cursor: pointer; border-radius: 4px; }
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

    <main class="main">
        <h1 class="page-title">Sales Records</h1>
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
                            $minYearRes = $conn->query("SELECT MIN(YEAR(sale_date)) as min_year FROM sale");
                            $row = $minYearRes->fetch_assoc();
                            $minYear = $row['min_year'] ?? date("Y");
                            $currentYear = date("Y");
                            for ($y = $currentYear; $y >= $minYear; $y--) {
                                $selected = (isset($_GET['year']) && $_GET['year'] == $y) ? "selected" : "";
                                echo "<option value='".$y."' $selected>".$y."</option>";
                            }
                        ?>
                    </select>
                </form>
            </div>
            <div class="add-btn">
                <button onclick="document.getElementById('addForm').style.display='flex'">➕ Add Sale</button>
                <!-- ✅ Fixed Download button -->
                <button type="button" onclick="openPreview()">Download</button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Buyer</th>
                    <th>No. of Birds (Wing Bands)</th>
                    <th>Amount</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    <?php 
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $wingbands_res = $conn->query("SELECT wingband FROM sale_wingbands WHERE sale_id = ".$row['sale_id']);
                echo "<tr>
                    <td>".$row['sale_date']."</td>
                    <td>".$row['buyer']."</td>
                    <td>";
                if($wingbands_res->num_rows > 0){
                    while($wb = $wingbands_res->fetch_assoc()) {
                        echo htmlspecialchars($wb['wingband'])."<br>";
                    }
                } else {
                    echo "-";
                }
                echo "</td>
                    <td>₱ ".number_format($row['amount'],2)."</td>
                    <td>".$row['remarks']."</td>
                    <td>
                        <button class='btn-edit' onclick='openEditModal(".$row['sale_id'].")'>Edit</button>
                        <button class='btn-delete' onclick='deleteSale(".$row['sale_id'].")'>Delete</button>
                    </td>
                </tr>";
            }

            echo "<tr class='total-row'>
                <td colspan='3' style='text-align:right; font-weight:bold;'>Total Sales</td>
                <td style='font-weight:bold;'>₱ ".number_format($total_sales, 2)."</td>
                <td colspan='2'></td>
            </tr>";

        } else {
            $msg = "No records found";
            if (!empty($_GET['year']) && empty($_GET['month'])) {
                $msg = "No record for this year";
            } elseif (!empty($_GET['month']) && !empty($_GET['year'])) {
                $msg = "No record for this month";
            } elseif (!empty($_GET['month']) && empty($_GET['year'])) {
                $msg = "No record for this month";
            }

            echo "<tr><td colspan='6' style='text-align:center; font-weight:bold; color:red;'>$msg</td></tr>";
        }
    ?>
    </tbody>
        </table>
    </main>
</div>

<!-- Add Sale Modal -->
<div id="addForm" class="modal">
    <form action="insert_sale.php" method="post">
        <h2>Add Sale</h2>
        <label>Date:</label>
        <input type="date" name="sale_date" required>
        <label>Buyer:</label>
        <input type="text" name="buyer" required>
        <label>Wing Bands:</label>
        <div id="wingbands-container">
            <div class="wingband-row">
                <input type="text" name="wingband[]" required>
                <button type="button" onclick="this.parentElement.remove()">✖</button>
            </div>
        </div>
        <button type="button" onclick="addWingband()">➕ Add Wingband</button>
        <label>Amount:</label>
        <input type="number" name="amount" required>
        <label>Remarks:</label>
        <input type="text" name="remarks">
        <button type="submit">Save</button>
        <button type="button" onclick="document.getElementById('addForm').style.display='none'">Cancel</button>
    </form>
</div>

<!-- Edit Sale Modal -->
<div id="editForm" class="modal">
    <form id="editFormElement" method="post" action="update_sale.php">
        <h2>Edit Sale</h2>
        <input type="hidden" name="sale_id" id="edit_sale_id">
        <label>Date:</label>
        <input type="date" name="sale_date" id="edit_sale_date" required>
        <label>Buyer:</label>
        <input type="text" name="buyer" id="edit_buyer" required>
        <label>Wing Bands:</label>
        <div id="edit-wingbands-container"></div>
        <button type="button" onclick="addEditWingband()">➕ Add Wingband</button>
        <label>Amount:</label>
        <input type="number" step="0.01" name="amount" id="edit_amount" required>
        <label>Remarks:</label>
        <input type="text" name="remarks" id="edit_remarks">
        <button type="submit">Update</button>
        <button type="button" onclick="document.getElementById('editForm').style.display='none'">Cancel</button>
    </form>
</div>

<!-- ✅ Fixed Preview Modal -->
<div id="previewModal" class="modal">
    <div style="background:#fff; width:90%; height:90%; border-radius:10px; position:relative; overflow:hidden;">
        <button onclick="closePreview()" style="position:absolute; top:10px; right:10px; 
            background:red; color:#fff; border:none; padding:6px 12px; cursor:pointer; border-radius:6px; z-index: 2;">X</button>
        <iframe id="previewFrame" src="" style="width:100%; height:100%; border:none;"></iframe>
    </div>
</div>

<script>
// Sidebar toggle
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

// Add wingband input in Add form
function addWingband() {
    var container = document.getElementById('wingbands-container');
    var row = document.createElement('div');
    row.className = 'wingband-row';
    row.innerHTML = '<input type="text" name="wingband[]" required> <button type="button" onclick="this.parentElement.remove()">✖</button>';
    container.appendChild(row);
}

// Add wingband input in Edit form
function addEditWingband(value='') {
    var container = document.getElementById('edit-wingbands-container');
    var row = document.createElement('div');
    row.className = 'wingband-row';
    row.innerHTML = '<input type="text" name="wingband[]" value="'+value+'" required> <button type="button" onclick="this.parentElement.remove()">✖</button>';
    container.appendChild(row);
}

// Open edit modal
function openEditModal(sale_id) {
    fetch('get_sale.php?sale_id='+sale_id)
    .then(response => response.json())
    .then(data => {
        document.getElementById('edit_sale_id').value = data.sale_id;
        document.getElementById('edit_sale_date').value = data.sale_date;
        document.getElementById('edit_buyer').value = data.buyer;
        document.getElementById('edit_amount').value = data.amount;
        document.getElementById('edit_remarks').value = data.remarks;

        var container = document.getElementById('edit-wingbands-container');
        container.innerHTML = '';
        data.wingbands.forEach(function(wb) {
            addEditWingband(wb);
        });

        document.getElementById('editForm').style.display = 'flex';
    });
}

// Delete sale
function deleteSale(sale_id) {
    if(confirm("Are you sure you want to delete this sale?")) {
        window.location.href = "delete_sale.php?sale_id=" + sale_id;
    }
}

// ✅ Open Preview modal
function openPreview() {
    document.getElementById("previewFrame").src = "preview_sales.php?month=<?php echo $_GET['month'] ?? ''; ?>&year=<?php echo $_GET['year'] ?? ''; ?>";
    document.getElementById("previewModal").style.display = "flex";
}

// ✅ Close Preview modal
function closePreview() {
    document.getElementById("previewModal").style.display = "none";
    document.getElementById("previewFrame").src = "";
}
</script>
</body>
</html>
