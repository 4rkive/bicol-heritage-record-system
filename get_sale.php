<?php
$conn = new mysqli("localhost","root","","bgc");
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

$sale_id = intval($_GET['sale_id']);

// Get sale header
$sale_res = $conn->query("SELECT * FROM sale WHERE sale_id=$sale_id");
if($sale_res && $sale_res->num_rows>0){
    $sale = $sale_res->fetch_assoc();

    // Get wingbands
    $wingbands_res = $conn->query("SELECT wingband FROM sale_wingbands WHERE sale_id=$sale_id");
    $wingbands = [];
    while($wb = $wingbands_res->fetch_assoc()){
        $wingbands[] = $wb['wingband'];
    }

    // Return JSON
    echo json_encode([
        'sale_id'=>$sale['sale_id'],
        'sale_date'=>$sale['sale_date'],
        'buyer'=>$sale['buyer'],
        'amount'=>$sale['amount'],
        'remarks'=>$sale['remarks'],
        'wingbands'=>$wingbands
    ]);
}
?>
