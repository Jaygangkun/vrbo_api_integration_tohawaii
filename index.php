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
    
    // echo $download_url;
    
    if($download_url == ''){
        return;
    }
    
    $file_name = $type.'.zip';
          
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

$type = 'vacationrental';
// $download_file = downloadFile($type);

$download_file = 'vacationrental.zip';

if($download_file) {
    $unzip_dir = unZipDownloadFile($download_file, $type);
    if($unzip_dir){
        $download_files = array_diff(scandir($unzip_dir), array('..', '.'));

        // echo json_encode($scanned_directory);
        
        foreach($download_files as $key => $download_file_name) {
            echo $download_file_name."<br>";
        }
        die();
    }
}
die();
// $string = file_get_contents("download/expedia-lodging-1-all.jsonl");
// $json_a = json_decode($string, true);
// echo count($json_a);

$download_files = array_diff(scandir('download/'), array('..', '.'));

// echo json_encode($scanned_directory);

foreach($download_files as $key => $download_file_name) {
    echo $download_file_name."<br>";
}
die();
// read file;
if ($file = fopen("download/expedia-lodging-1-all.jsonl", "r")) {
    $index = 0;
    while(!feof($file)) {
        $line = fgets($file);
        $index ++;
    }
    fclose($file);

    echo $index;
}   