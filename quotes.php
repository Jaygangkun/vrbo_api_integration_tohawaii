<?php
include('./config.php');
include('./db.php');
include('./functions.php');

$propertyIds = '1474';
$checkIn = '2022-03-10';
$checkOut = '2022-03-18';

$checkIn = date("Y-m-d");
$checkOut = date('Y-m-d', strtotime($checkIn. ' + 280 days'));

$sql_all = "SELECT * FROM `vacationrentalindex`";

$res_all = $dbcon->query($sql_all);

if($res_all) {
    $count = 0;
    while($row = $res_all->fetch_assoc()){
        echo $row['uniturl'];
        $propertyIds = $row['uniturl'];
        $quote_data = getQuote($propertyIds, $checkIn, $checkOut);

        if($quote_data['href'] != '' || $quote_data['price'] != '') {
            echo ": updated";
            $sql_update = "UPDATE `vacationrentalindex` SET `href`='".$quote_data['href']."', `price`='".$quote_data['price']."' WHERE `uniturl`='".$row['uniturl']."'";

            mysqli_query($dbcon, $sql_update);// or die(mysqli_error($con)) ; 
        }
        echo "<br>";

        $count++;
        if($count > 100) {
            die();
        }
    }
}