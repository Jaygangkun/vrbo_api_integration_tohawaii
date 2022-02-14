<?php
// file download
function downloadFile($type) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    //   CURLOPT_URL => 'https://apim.expedia.com/feed/downloadUrl?locale=en-US&type=vacationrental',
        CURLOPT_URL => 'https://apim.expedia.com/feed/downloadUrl?locale=en-US&type='.$type,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Key: f6444ed9-81bb-41bd-adbe-ca2fbbf1d30d',
            'Authorization: Basic ZjY0NDRlZDktODFiYi00MWJkLWFkYmUtY2EyZmJiZjFkMzBkOk55ODhFeE9pa0Y5SEJaNW8='
        ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    // echo $response;
    
    $download_url = '';
    
    if($response != '') {
        $res_data = json_decode($response, true);
        $download_url = $res_data['downloadUrl'];
    }
        
    if($download_url == ''){
        return;
    }
    
    $file_name = $type.'.zip';
          
    if (file_exists($file_name)) {
        unlink($file_name);
    }
    
    // Use file_get_contents() function to get the file
    // from url and use file_put_contents() function to
    // save the file by using base name
    if (file_put_contents($file_name, file_get_contents($download_url)))
    {
        return $file_name;
    }
    else
    {
        return false;
    }
    
}

function unZipDownloadFile($file_name, $type) {
    // $file_name = 'download.zip';

    $dir_name = 'download-'.$type.'/';

    if (file_exists($dir_name)) {
        rmdir($dir_name);
    }

    mkdir($dir_name , 0777, true);
    
    $zip = new ZipArchive;
      
    // Zip File Name
    if ($zip->open($file_name) === TRUE) {
      
        // Unzip Path
        $zip->extractTo($dir_name);
        $zip->close();
        
        return $dir_name;
    } else {
        return false;
    }
}

function processDownloadFile($file_name, $type) {

    global $dbcon;

    // read file;
    $db_index_fields = array(
        'uniturl' => '', /**from vacationrental */
        'bathrooms' => '', /**from vacationrental */
        'bedrooms' => '', /**from vacationrental */
        'sleeps' => '',
        'href' => '',
        'thumbnailUrl' => '', /**from images */
        'headline' => '', /**from summary */
        'description' => '', /**from descriptions */
        'state' => '', /**from summary */
        'city' => '', /**from summary */
        'country' => '', /**from summary */
        'lastModified' => '', /**from summary */
        'region' => '',
        'source' => '',
        'property_type' => '', /**from summary */
        'location_type' => '', /**empty */
        'suitability' => '', /**empty */
        'features' => '', /**empty */
        'address' => '', /**from summary */
        'price' => '',
        'minimum_nights' => '',
        'rating' => '', /**from guestreviews */
        'review_count' => '', /**from guestreviews */
        'latitude' => '', /**from summary */
        'longitude' => '', /**from summary */
        'en_US_VR' => '', 
        'en_US_VRBO' => '',
        'propertyName' => '', /**from summary */
        'actual_thumbnail' => '',
        'postalCode' => '', /**from summary */
        'registration_number' => '', /**from vacationrental */
        'regcheck' => '',
        'updated' => '',
        'price_update' => '',
        'thumbnail_update' => '',
        'registration_update' => '',
    );

    // if ($file = fopen("download-vacationrental/expedia-lodging-1-all.jsonl", "r")) {
    if ($file = fopen($file_name, "r")) {
        $count = 0;
        while(!feof($file)) {
            $line = fgets($file);
            $data_json = json_decode($line, true);

            if($type == 'vacationrental') {
                if(isset($data_json['propertyId'])) {
                    $db_index_fields['uniturl'] = mysqli_real_escape_string($dbcon, $data_json['propertyId']['expedia']);
                }
    
                if(isset($data_json['bathrooms'])) {
                    $db_index_fields['bathrooms'] = mysqli_real_escape_string($dbcon, $data_json['bathrooms']['numberOfBathrooms']);
                }
    
                if(isset($data_json['bedrooms'])) {
                    $db_index_fields['bedrooms'] = mysqli_real_escape_string($dbcon, $data_json['bedrooms']['numberOfBedrooms']);
                }
                
                if(isset($data_json['propertyRegistryNumber'])) {
                    $db_index_fields['registration_number'] = mysqli_real_escape_string($dbcon, $data_json['propertyRegistryNumber']);
                }
    
                $sql_check_url = "SELECT * FROM `vacationrentalindex` WHERE `uniturl`='".$db_index_fields['uniturl']."' LIMIT 1";
    
                $res_check_url = $dbcon->query($sql_check_url);
    
                if($res_check_url && $res_check_url->num_rows > 0) {
                    $sql_update = "UPDATE `vacationrentalindex` SET `bathrooms`='".$db_index_fields['bathrooms']."', `bedrooms`='".$db_index_fields['bedrooms']."', `registration_number`='".$db_index_fields['registration_number']."' WHERE `uniturl`='".$db_index_fields['uniturl']."'";
    
                    mysqli_query($dbcon, $sql_update);// or die(mysqli_error($con)) ; 
                }
                else {
                    $sql_insert = "INSERT INTO `vacationrentalindex`(`uniturl`, `bathrooms`, `bedrooms`, `registration_number`) VALUES ('".$db_index_fields["uniturl"]."', '".$db_index_fields["bathrooms"]."', '".$db_index_fields["bedrooms"]."', '".$db_index_fields["registration_number"]."');";
    
                    mysqli_query($dbcon, $sql_insert);// or die(mysqli_error($con)) ; 
                }

                $count ++;
            }

            if($type == 'summary') {
                if(isset($data_json['propertyId'])) {
                    $db_index_fields['uniturl'] = mysqli_real_escape_string($dbcon, $data_json['propertyId']['expedia']);
                }
    
                if(isset($data_json['propertyName'])) {
                    $db_index_fields['headline'] = mysqli_real_escape_string($dbcon, $data_json['propertyName']);
                }

                if(isset($data_json['propertyName'])) {
                    $db_index_fields['propertyName'] = mysqli_real_escape_string($dbcon, $data_json['propertyName']);
                }
    
                if(isset($data_json['province'])) {
                    $db_index_fields['state'] = mysqli_real_escape_string($dbcon, $data_json['province']);
                }
                
                if(isset($data_json['city'])) {
                    $db_index_fields['city'] = mysqli_real_escape_string($dbcon, $data_json['city']);
                }

                if(isset($data_json['country'])) {
                    $db_index_fields['country'] = mysqli_real_escape_string($dbcon, $data_json['country']);
                }

                if(isset($data_json['propertyType'])) {
                    $db_index_fields['property_type'] = mysqli_real_escape_string($dbcon, $data_json['propertyType']['name']);
                }

                if(isset($data_json['lastUpdated'])) {
                    $db_index_fields['lastModified'] = mysqli_real_escape_string($dbcon, $data_json['lastUpdated']);
                }

                if(isset($data_json['address1'])) {
                    $db_index_fields['address'] = mysqli_real_escape_string($dbcon, $data_json['address1']);
                }

                if(isset($data_json['geoLocation'])) {
                    $db_index_fields['latitude'] = mysqli_real_escape_string($dbcon, $data_json['geoLocation']['latitude']);
                }

                if(isset($data_json['geoLocation'])) {
                    $db_index_fields['longitude'] = mysqli_real_escape_string($dbcon, $data_json['geoLocation']['longitude']);
                }

                if(isset($data_json['postalCode'])) {
                    $db_index_fields['postalCode'] = mysqli_real_escape_string($dbcon, $data_json['postalCode']);
                }
    
                $sql_check_url = "SELECT * FROM `vacationrentalindex` WHERE `uniturl`='".$db_index_fields['uniturl']."' LIMIT 1";
    
                $res_check_url = $dbcon->query($sql_check_url);
    
                if($res_check_url && $res_check_url->num_rows > 0) {
                    $sql_update = "UPDATE `vacationrentalindex` SET `headline`='".$db_index_fields['headline']."', `propertyName`='".$db_index_fields['propertyName']."', `state`='".$db_index_fields['state']."', `city`='".$db_index_fields['city']."', `country`='".$db_index_fields['country']."', `property_type`='".$db_index_fields['property_type']."', `lastModified`='".$db_index_fields['lastModified']."', `address`='".$db_index_fields['address']."', `latitude`='".$db_index_fields['latitude']."', `longitude`='".$db_index_fields['longitude']."', `postalCode`='".$db_index_fields['postalCode']."' WHERE `uniturl`='".$db_index_fields['uniturl']."'";
    
                    mysqli_query($dbcon, $sql_update);// or die(mysqli_error($con)) ; 
                }
                else {
                    $sql_insert = "INSERT INTO `vacationrentalindex`(`uniturl`, `headline`, `propertyName`, `state`, `city`, `country`, `property_type`, `lastModified`, `address`, `latitude`, `longitude`, `postalCode`) VALUES ('".$db_index_fields["uniturl"]."', '".$db_index_fields["headline"]."', '".$db_index_fields["propertyName"]."', '".$db_index_fields["state"]."', '".$db_index_fields["city"]."', '".$db_index_fields["country"]."', '".$db_index_fields["property_type"]."', '".$db_index_fields["lastModified"]."', '".$db_index_fields["address"]."', '".$db_index_fields["latitude"]."', '".$db_index_fields["longitude"]."', '".$db_index_fields["postalCode"]."');";
    
                    mysqli_query($dbcon, $sql_insert);// or die(mysqli_error($con)) ; 
                }

                $count ++;
            }

            if($type == 'descriptions') {
                if(isset($data_json['propertyId'])) {
                    $db_index_fields['uniturl'] = mysqli_real_escape_string($dbcon, $data_json['propertyId']['expedia']);
                }

                if(isset($data_json['propertyDescription'])) {
                    $db_index_fields['description'] = mysqli_real_escape_string($dbcon, $data_json['propertyDescription']);
                }
    
                $sql_check_url = "SELECT * FROM `vacationrentalindex` WHERE `uniturl`='".$db_index_fields['uniturl']."' LIMIT 1";
    
                $res_check_url = $dbcon->query($sql_check_url);
    
                if($res_check_url && $res_check_url->num_rows > 0) {
                    $sql_update = "UPDATE `vacationrentalindex` SET `description`='".$db_index_fields['description']."' WHERE `uniturl`='".$db_index_fields['uniturl']."'";
    
                    mysqli_query($dbcon, $sql_update);// or die(mysqli_error($con)) ; 
                }
                else {
                    $sql_insert = "INSERT INTO `vacationrentalindex`(`uniturl`, `description`) VALUES ('".$db_index_fields["uniturl"]."', '".$db_index_fields["description"]."');";
    
                    mysqli_query($dbcon, $sql_insert);// or die(mysqli_error($con)) ; 
                }

                $count ++;
            }

            if($type == 'images') {
                if(isset($data_json['propertyId'])) {
                    $db_index_fields['uniturl'] = mysqli_real_escape_string($dbcon, $data_json['propertyId']['expedia']);
                }

                if(isset($data_json['images'])) {
                    $images = $data_json['images'];
                    foreach($images as $images_) {
                        foreach($images_ as $image) {
                            $db_index_fields['thumbnailUrl'] .= $image['link']."|";
                        }
                    }
                }
                
                $sql_check_url = "SELECT * FROM `vacationrentalindex` WHERE `uniturl`='".$db_index_fields['uniturl']."' LIMIT 1";
    
                $res_check_url = $dbcon->query($sql_check_url);
                
                if($res_check_url && $res_check_url->num_rows > 0) {
                    $sql_update = "UPDATE `vacationrentalindex` SET `thumbnailUrl`='".$db_index_fields['thumbnailUrl']."' WHERE `uniturl`='".$db_index_fields['uniturl']."'";
    
                    mysqli_query($dbcon, $sql_update);// or die(mysqli_error($con)) ; 
                    echo $sql_update;die();
                }
                else {
                    $sql_insert = "INSERT INTO `vacationrentalindex`(`uniturl`, `thumbnailUrl`) VALUES ('".$db_index_fields["uniturl"]."', '".$db_index_fields["thumbnailUrl"]."');";
    
                    mysqli_query($dbcon, $sql_insert);// or die(mysqli_error($con)) ; 
                    echo $sql_insert;die();
                }

                $count ++;
            }

            if($type == 'reviews') {
                if(isset($data_json['propertyId'])) {
                    $db_index_fields['uniturl'] = mysqli_real_escape_string($dbcon, $data_json['propertyId']['expedia']);
                }

                if(isset($data_json['guestRating']) && isset($data_json['guestRating']['vrbo'])) {
                    $db_index_fields['review_count'] = mysqli_real_escape_string($dbcon, $data_json['guestRating']['vrbo']['reviewCount']); 
                    $db_index_fields['rating'] = mysqli_real_escape_string($dbcon, $data_json['guestRating']['vrbo']['avgRating']);
                }
    
                $sql_check_url = "SELECT * FROM `vacationrentalindex` WHERE `uniturl`='".$db_index_fields['uniturl']."' LIMIT 1";
    
                $res_check_url = $dbcon->query($sql_check_url);
    
                if($res_check_url && $res_check_url->num_rows > 0) {
                    $sql_update = "UPDATE `vacationrentalindex` SET `rating`='".$db_index_fields['rating']."', `review_count`='".$db_index_fields['review_count']."' WHERE `uniturl`='".$db_index_fields['uniturl']."'";
    
                    mysqli_query($dbcon, $sql_update);// or die(mysqli_error($con)) ; 
                }
                else {
                    $sql_insert = "INSERT INTO `vacationrentalindex`(`uniturl`, `rating`, `review_count`) VALUES ('".$db_index_fields["uniturl"]."', '".$db_index_fields["rating"]."', '".$db_index_fields["review_count"]."');";
    
                    mysqli_query($dbcon, $sql_insert);// or die(mysqli_error($con)) ; 
                }

                $count ++;
            }

            if($count > 1000) {
                break;
            }
        }

        fclose($file);

        return $count;
    }   

    return null;
}

function getQuote($propertyIds, $checkIn, $checkOut) {

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://apim.expedia.com/lodging/quotes?propertyIds='.$propertyIds.'&checkIn='.$checkIn.'&checkOut='.$checkOut,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Key: f6444ed9-81bb-41bd-adbe-ca2fbbf1d30d',
            'Authorization: Basic ZjY0NDRlZDktODFiYi00MWJkLWFkYmUtY2EyZmJiZjFkMzBkOk55ODhFeE9pa0Y5SEJaNW8=',
            'Partner-Transaction-ID: Iva-tohawaii',
            'Accept: application/vnd.exp-lodging.v3+json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $res_data = json_decode($response, true);

    $ret_data = array(
        'price' => '',
        'href' => ''
    );

    if(isset($res_data['Properties']) && count($res_data['Properties']) > 0) {
        $property_data = $res_data['Properties'][0];
        if($property_data['Status'] == 'AVAILABLE') {
            foreach($property_data['RoomTypes'] as $roomType) {
                $price = '';
                $link = '';

                if(isset($roomType['Price'])) {
                    $price = $roomType['Price']['BaseRate']['Value'];
                }

                if(isset($roomType['Links'])) {
                    $link = $roomType['Links']['WebDetails']['Href'];
                }

                if($price != '' && $link != '') {
                    $ret_data['price'] = $price;
                    $ret_data['href'] = $href;

                    break;
                }
            }
        }
    }

    return $ret_data;
}