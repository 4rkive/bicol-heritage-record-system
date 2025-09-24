<?php
$conn = new mysqli("localhost","root","","bgc");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

$sale_id=intval($_POST['sale_id']);
$sale_date=$_POST['sale_date'];
$buyer=$_POST['buyer'];
$amount=$_POST['amount'];
$remarks=$_POST['remarks'];
$wingbands=$_POST['wingband'] ?? [];

$conn->query("UPDATE sale SET sale_date='$sale_date', buyer='$buyer', amount='$amount', remarks='$remarks' WHERE sale_id=$sale_id");
$conn->query("DELETE FROM sale_wingbands WHERE sale_id=$sale_id");
foreach($wingbands as $wb){
    $wb = $conn->real_escape_string($wb);
    $conn->query("INSERT INTO sale_wingbands(sale_id,wingband) VALUES($sale_id,'$wb')");
}
header("Location:sales.php");
