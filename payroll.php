<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "bgc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filters (from GET)
$branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : '';
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Determine cutoff label for Cash Advance 1 (1–15 of selected month)
$ca1_start = 1;
$ca1_end = 15;
$cutoff_label = date('F', mktime(0,0,0,$selected_month,1)) . " $ca1_start-$ca1_end, $selected_year";


// If you use $_SESSION['toast'] in insert/update scripts, copy it here to JS then remove from session
$session_toast = null;
if (isset($_SESSION['toast'])) {
    $session_toast = $_SESSION['toast']; // array ['msg'=>'...','type'=>'success'|'error']
    unset($_SESSION['toast']);
}

// Detect current page for sidebar
$current_page = basename($_SERVER['PHP_SELF']);
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
    body { margin:0; font-family: Arial, Helvetica, sans-serif; background: var(--green1); color:#333; }
    header { background: var(--green3); padding:10px 20px; display:flex; align-items:center; color:white; }
    .farm-logo { display:flex; align-items:center; gap:12px; }
    .farm-logo img { width:50px; height:50px; border-radius:50%; border:2px solid rgba(255,255,255,0.6); background:#fff; }
    .farm-name { font-size:20px; font-weight:700; }

    .layout { display:flex; }
    .sidebar { width:240px; background: var(--green4); color:#fff; display:flex; flex-direction:column; padding:16px 12px; min-height: calc(100vh - 64px); }
    .nav a { display:flex; align-items:center; gap:10px; text-decoration:none; color:#fff; padding:10px 12px; margin:4px 6px; border-radius:8px; }
    .nav a.active, .nav a:hover { background: rgba(255,255,255,0.25); }
    .nav-section-title { font-size:11px; letter-spacing:1.5px; margin:14px 6px 6px; opacity:0.9; }

    .main-content { flex:1; padding:20px; background: linear-gradient(rgba(177,221,158,0.40), rgba(177,221,158,0.40)), url("image/cover.jpg") no-repeat center center; background-size: cover; min-height:100vh; }
    .page-title { text-align:center; font-size:28px; font-weight:700; margin-bottom:20px; color:#18392b; }

    .header-controls { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; gap:10px; }
    .filter-controls select { padding:6px; border-radius:6px; border:1px solid #ccc; }
    .add-btn button, .download-btn {
        background: var(--green3); color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; margin-left:5px; text-decoration:none;
    }
    .add-btn button:hover, .download-btn:hover { background: var(--green5); }

    table { width:100%; border-collapse:collapse; margin-top:15px; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.05); }
    table th, table td { padding:12px; border-bottom:1px solid #eee; text-align:center; font-size:14px; }
    table th { background: var(--green3); color:#fff; text-transform:uppercase; font-size:13px; letter-spacing:1px; }
    table tr:nth-child(even) { background:#f9f9f9; }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 999;
        top: 0; left:0; width:100%; height:100%;
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        overflow: auto;
    }
    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 95%;
        max-width: 320px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-content h3 { text-align:center; margin-top:0; color:#1b5e20; }
    .modal-content label { font-weight:bold; margin-top:8px; display:block; }
    .modal-content input, .modal-content select { width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; font-size:14px; box-sizing:border-box; margin-top:6px; }

    .modal-actions { margin-top:15px; text-align:right; }
    .modal-actions button { padding:8px 12px; border:none; border-radius:6px; cursor:pointer; }
    .save-btn { background: var(--green3); color:#fff; }
    .save-btn:hover { background: var(--green5); }
    .cancel-btn { background:#ccc; }
    .cancel-btn:hover { background:#aaa; color:#fff; }

    .btn-edit { padding:4px 8px; border:none; border-radius:4px; background:#4caf50; color:white; cursor:pointer; }
    .btn-edit:hover { background:#388e3c; }

    .toast {
        visibility: hidden;
        min-width: 250px;
        margin-left: -125px;
        background-color: #28a745;
        color: #fff;
        text-align: center;
        border-radius: 8px;
        padding: 16px;
        position: fixed;
        z-index: 1000;
        left: 50%;
        top: 30px;
        font-size: 17px;
        opacity: 0;
        transition: opacity 0.5s, bottom 0.5s;
    }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast.success { background: #28a745; }
    .toast.error { background: #dc3545; }

    /* small responsive tweaks */
    @media (max-width:900px){
        .sidebar{display:none;}
        .header-controls{flex-direction:column; align-items:flex-start; gap:8px;}
    }
</style>
<script>
    function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}
function showToast(message, success = true) {
    var toast = document.getElementById("toast");
    toast.innerText = message;
    toast.className = "toast " + (success ? "success" : "error") + " show";
    setTimeout(function(){ toast.className = toast.className.replace(" show", ""); }, 3000);
}

window.addEventListener('DOMContentLoaded', function(){
    // show toast from URL param status
    const params = new URLSearchParams(window.location.search);
    if (params.has('status')) {
        switch (params.get('status')) {
            case 'success': showToast("Record updated successfully!", true); break;
            case 'error': showToast("Error updating record.", false); break;
            case 'added': showToast("Record added successfully!", true); break;
            case 'add_error': showToast("Error adding record.", false); break;
        }
    }
});

// Open Add modal
function openAddModal(){
    var modal = document.getElementById('addPayrollModal');
    modal.style.display = 'flex';
    // reset fields
    modal.querySelector('[name="employee_id"]').selectedIndex = 0;
    modal.querySelector('[name="branch_id"]').selectedIndex = 0;
    modal.querySelector('[name="payroll_date"]').value = '';
    modal.querySelector('[name="no_of_days"]').value = '';
    modal.querySelector('[name="total_monthly_salary"]').value = '';
    modal.querySelector('[name="cash_advance1"]').value = '';
    modal.querySelector('[name="sss"]').value = '';
    modal.querySelector('[name="philhealth"]').value = '';
    modal.querySelector('[name="pagibig"]').value = '';
    modal.querySelector('[name="cash_advance2"]').value = '';
    calculateTotalReceived(modal);
}

// Open Edit Modal with data object
function openEditForm(data){
    var modal=document.getElementById('editPayrollModal');
    modal.style.display='flex';
    modal.querySelector('[name="payroll_id"]').value = data.payroll_id || '';
    modal.querySelector('[name="employee_id"]').value = data.employee_id || '';
    modal.querySelector('[name="branch_id"]').value = data.branch_id || '';
    modal.querySelector('[name="payroll_date"]').value = data.payroll_date || '';
    modal.querySelector('[name="no_of_days"]').value = data.no_of_days || '';
    modal.querySelector('[name="total_monthly_salary"]').value = data.total_monthly_salary || 0;
    modal.querySelector('[name="cash_advance1"]').value = data.cash_advance1 || 0;
    modal.querySelector('[name="sss"]').value = data.sss || 0;
    modal.querySelector('[name="philhealth"]').value = data.philhealth || 0;
    modal.querySelector('[name="pagibig"]').value = data.pagibig || 0;
    modal.querySelector('[name="cash_advance2"]').value = data.cash_advance2 || 0;
    calculateTotalReceived(modal);
}

// calculate Total Received inside the modal (context is .modal element or document)
function calculateTotalReceived(context=document){
    try {
        const salary = parseFloat(context.querySelector('[name="total_monthly_salary"]').value) || 0;
        const ca1    = parseFloat(context.querySelector('[name="cash_advance1"]').value) || 0;
        const sss    = parseFloat(context.querySelector('[name="sss"]').value) || 0;
        const ph     = parseFloat(context.querySelector('[name="philhealth"]').value) || 0;
        const pagibig= parseFloat(context.querySelector('[name="pagibig"]').value) || 0;
        const ca2    = parseFloat(context.querySelector('[name="cash_advance2"]').value) || 0;

        const total = salary - (ca1 + sss + ph + pagibig + ca2);
        context.querySelector('[name="total_amount_received"]').value = (total >= 0 ? total : 0);
    } catch(e) {
        // ignore if fields not present
    }
}

// Recalculate automatically if any inputs change inside modal
document.addEventListener("input", function(e) {
    const names = ["total_monthly_salary","cash_advance1","sss","philhealth","pagibig","cash_advance2"];
    if (names.includes(e.target.name)) {
        let modal = e.target.closest('.modal');
        if (modal) calculateTotalReceived(modal);
    }
});

// close modal helper
function closeModal(id){ document.getElementById(id).style.display='none'; }

// close modal by clicking outside the content
document.addEventListener('click', function(e){
    document.querySelectorAll('.modal').forEach(function(modal){
        if (modal.style.display === 'flex' && e.target === modal) modal.style.display = 'none';
    });
});

// ✅ Open Preview modal with filters (month, year, branch)
function openPreview() {
    const params = new URLSearchParams(window.location.search);
    const month = params.get('month') || '';
    const year = params.get('year') || '';
    const branch = params.get('branch_id') || '';

    document.getElementById("previewFrame").src = 
        "preview_payroll.php?month=" + month + "&year=" + year + "&branch_id=" + branch;
    document.getElementById("previewModal").style.display = "flex";
}

// ✅ Close Preview modal
function closePreview() {
    document.getElementById("previewModal").style.display = "none";
    document.getElementById("previewFrame").src = "";
}
</script>
</head>
<body>

<header>
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

    <div class="main-content">
        <h2 class="page-title">Payroll Records</h2>

        <div class="header-controls">
            <div class="filter-controls">
                <form method="GET" onchange="this.submit()">
                    <select name="branch_id">
                        <option value="">All Branches</option>
                        <?php 
                        $branches = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");
                        while ($b = $branches->fetch_assoc()) {
                            $selected = ($branch_id==$b['branch_id'])?'selected':''; 
                            echo "<option value='".$b['branch_id']."' $selected>".$b['branch_name']."</option>";
                        }
                        ?>
                    </select>
                    <select name="month">
                        <?php
                        for ($m=1;$m<=12;$m++){
                            $monthName=date('F',mktime(0,0,0,$m,1));
                            $selected=($selected_month==$m)?'selected':''; 
                            echo "<option value='$m' $selected>$monthName</option>";
                        }
                        ?>
                    </select>
                    <select name="year">
                        <?php
                        $current_year=date('Y');
                        for ($y=$current_year;$y>=$current_year-5;$y--){
                            $selected=($selected_year==$y)?'selected':''; 
                            echo "<option value='$y' $selected>$y</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>

            <div class="add-btn">
                <button type="button" onclick="openAddModal()">Add Payroll</button>
                <button  onclick="openPreview()">Download</button>
            </div>
        </div>

        <!-- Payroll Table -->
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Employee</th>
                    <th rowspan="2">No. of Days</th>
                    <th rowspan="2">Total Monthly Salary</th>
                    <th colspan="4">Deduction</th>
                    <th rowspan="2">Cash Advance</th>
                    <th rowspan="2">Total Amount Received</th>
                    <th rowspan="2">Action</th>
                </tr>
                <tr>
                    <th>Cash Advance<br><small><?php echo $cutoff_label; ?></small></th>
                    <th>SSS</th>
                    <th>PhilHealth</th>
                    <th>Pag-IBIG</th>
                </tr>
            </thead>
<?php
$sql="SELECT p.*, e.employee_name, b.branch_name FROM payroll p 
      JOIN employees e ON p.employee_id=e.employee_id 
      JOIN branches b ON p.branch_id=b.branch_id 
      WHERE MONTH(p.payroll_date)=$selected_month AND YEAR(p.payroll_date)=$selected_year";
if(!empty($branch_id)) $sql.=" AND p.branch_id=$branch_id";
$sql.=" ORDER BY p.payroll_id DESC";

$result=$conn->query($sql);

$total_salary=$total_ca1=$total_sss=$total_ph=$total_pagibig=$total_ca2=$total_received=0;

if($result && $result->num_rows>0){
    while($row=$result->fetch_assoc()){
        $received = $row['total_monthly_salary'] - ($row['cash_advance1']+$row['sss']+$row['philhealth']+$row['pagibig']+$row['cash_advance2']);
        $received_display=number_format($received,0);

        $total_salary+=$row['total_monthly_salary'];
        $total_ca1+=$row['cash_advance1'];
        $total_sss+=$row['sss'];
        $total_ph+=$row['philhealth'];
        $total_pagibig+=$row['pagibig'];
        $total_ca2+=$row['cash_advance2'];
        $total_received+=$received;

        // json encode row for JS openEditForm
        $json_row = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');

        echo "<tr>
            <td>".htmlspecialchars($row['employee_name'])."</td>
            <td>".number_format($row['no_of_days'],0)."</td>
            <td>".number_format($row['total_monthly_salary'],0)."</td>
            <td>".number_format($row['cash_advance1'],0)."</td>
            <td>".number_format($row['sss'],0)."</td>
            <td>".number_format($row['philhealth'],0)."</td>
            <td>".number_format($row['pagibig'],0)."</td>
            <td>".number_format($row['cash_advance2'],0)."</td>
            <td>".$received_display."</td>
            <td><button type='button' class='btn-edit' onclick='openEditForm($json_row)'>✏️ Edit</button></td>
        </tr>";
    }

    echo "<tr style='font-weight:bold;background:#d9ead3;'>
        <td></td>
        <td>Grand Total</td>
        <td>".number_format($total_salary,0)."</td>
        <td>".number_format($total_ca1,0)."</td>
        <td>".number_format($total_sss,0)."</td>
        <td>".number_format($total_ph,0)."</td>
        <td>".number_format($total_pagibig,0)."</td>
        <td>".number_format($total_ca2,0)."</td>
        <td>".number_format($total_received,0)."</td>
        <td></td>
    </tr>";
}else{ 
    echo "<tr><td colspan='11'>No payroll records found.</td></tr>"; 
}
?>
        </table>
    </div>
</div>

<!-- Add Payroll Modal -->
<div id="addPayrollModal" class="modal" role="dialog" aria-modal="true">
  <div class="modal-content">
    <h3>Add Payroll</h3>
    <form method="POST" action="insert_payroll.php">
      <label>Employee:</label>
      <select name="employee_id" required>
        <?php
        $employees = $conn->query("SELECT * FROM employees ORDER BY employee_name ASC");
        while ($emp = $employees->fetch_assoc()) {
            echo "<option value='".htmlspecialchars($emp['employee_id'])."'>".htmlspecialchars($emp['employee_name'])."</option>";
        }
        ?>
      </select>

      <label>Branch:</label>
      <select name="branch_id" required>
        <?php
        $branches = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");
        while ($b = $branches->fetch_assoc()) {
            echo "<option value='".htmlspecialchars($b['branch_id'])."'>".htmlspecialchars($b['branch_name'])."</option>";
        }
        ?>
      </select>

      <label>Payroll Date:</label>
      <input type="date" name="payroll_date" required>

      <label>No. of Days:</label>
      <input type="number" name="no_of_days">

      <label>Total Monthly Salary:</label>
      <input type="number" step="1" name="total_monthly_salary" required>

      <label>Cash Advance:</label>
      <input type="number" step="1" name="cash_advance1">

      <label>SSS:</label>
      <input type="number" step="1" name="sss">

      <label>PhilHealth:</label>
      <input type="number" step="1" name="philhealth">

      <label>Pag-IBIG:</label>
      <input type="number" step="1" name="pagibig">

      <label>Cash Advance:</label>
      <input type="number" step="1" name="cash_advance2">

      <label>Total Amount Received:</label>
      <input type="number" step="1" name="total_amount_received" readonly>

      <div class="modal-actions">
        <button type="submit" class="save-btn">Save</button>
        <button type="button" class="cancel-btn" onclick="closeModal('addPayrollModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Payroll Modal -->
<div id="editPayrollModal" class="modal" role="dialog" aria-modal="true">
  <div class="modal-content">
    <h3>Edit Payroll</h3>
    <form method="POST" action="update_payroll.php">
      <input type="hidden" name="payroll_id">

      <label>Employee:</label>
      <select name="employee_id" required>
        <?php
        $employees=$conn->query("SELECT * FROM employees ORDER BY employee_name ASC");
        while($emp=$employees->fetch_assoc()){ echo "<option value='".htmlspecialchars($emp['employee_id'])."'>".htmlspecialchars($emp['employee_name'])."</option>"; }
        ?>
      </select>

      <label>Branch:</label>
      <select name="branch_id" required>
        <?php
        $branches=$conn->query("SELECT * FROM branches ORDER BY branch_name ASC");
        while($b=$branches->fetch_assoc()){ echo "<option value='".htmlspecialchars($b['branch_id'])."'>".htmlspecialchars($b['branch_name'])."</option>"; }
        ?>
      </select>

      <label>Payroll Date:</label>
      <input type="date" name="payroll_date" required>

      <label>No. of Days:</label>
      <input type="number" name="no_of_days">

      <label>Total Monthly Salary:</label>
      <input type="number" step="1" name="total_monthly_salary" required>

      <label>Cash Advance:</label>
      <input type="number" step="1" name="cash_advance1">

      <label>SSS:</label>
      <input type="number" step="1" name="sss">

      <label>PhilHealth:</label>
      <input type="number" step="1" name="philhealth">

      <label>Pag-IBIG:</label>
      <input type="number" step="1" name="pagibig">

      <label>Cash Advance:</label>
      <input type="number" step="1" name="cash_advance2">

      <label>Total Amount Received:</label>
      <input type="number" step="1" name="total_amount_received" readonly>

      <div class="modal-actions">
        <button type="submit" class="save-btn">Update</button>
        <button type="button" class="cancel-btn" onclick="closeModal('editPayrollModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="toast"></div>
    <div id="previewModal" class="modal">
    <div style="background:#fff; width:65%; height:75%; border-radius:10px; position:relative; overflow:hidden;">
        <iframe id="previewFrame" src="" style="width:100%; height:100%; border:none;"></iframe>
    </div>
</div>

<?php
// If server passed a toast via session, show it now
if ($session_toast) {
    $msg = addslashes($session_toast['msg']);
    $isSuccess = ($session_toast['type'] === 'success') ? 'true' : 'false';
    echo "<script>document.addEventListener('DOMContentLoaded', function(){ showToast(\"{$msg}\", {$isSuccess}); });</script>";
}
?>

</body>
</html>
