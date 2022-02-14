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
    $propertyIdsGroup = array();
    while($row = $res_all->fetch_assoc()){
        $propertyIds .= $row['uniturl'].",";
        $count++;
        if($count > 500) {
            $propertyIdsGroup[] = $propertyIds;

            $propertyIds = '';
            $count = 0;
        }
    }

    if($count != 0) {
        $propertyIdsGroup[] = $propertyIds;
    }

    foreach($propertyIdsGroup as $propertyIds) {

        $quotes_data = getQuotes($propertyIds, $checkIn, $checkOut);
        
        if($quotes_data) {
            foreach($quotes_data as $quote_data) {
                $property_data = $quote_data;
                if($property_data['Status'] == 'AVAILABLE') {
                    $price = '';
                    $link = '';
                    foreach($property_data['RoomTypes'] as $roomType) {
        
                        $price = '';
                        $link = '';
        
                        if(isset($roomType['Price']) && isset($roomType['Price']['BaseRate'])) {
                            $price = $roomType['Price']['BaseRate']['Value'];
                        }
        
                        if(isset($roomType['Links']) && isset($roomType['Links']['WebDetails'])) {
                            $link = $roomType['Links']['WebDetails']['Href'];
                        }
        
                        if($price != '' && $link != '') {        
                            break;
                        }
                    }
        
                    if($link != '' || $price != '') {
                        $sql_update = "UPDATE `vacationrentalindex` SET `href`='".$link."', `price`='".$price."' WHERE `uniturl`='".$property_data['Id']."'";
                
                        mysqli_query($dbcon, $sql_update);// or die(mysqli_error($con)) ; 
                    }
                }
            }
        }
    }
    
}