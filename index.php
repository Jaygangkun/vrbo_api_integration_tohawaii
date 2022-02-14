<?php
include('./config.php');
include('./db.php');
include('./functions.php');

writeLog('process1');die();
// $type = 'vacationrental';
$type = 'summary';
// $type = 'descriptions';
// $type = 'images';
// $type = 'reviews';
// $download_file = downloadFile($type);

$download_file = 'summary.zip';

if($download_file) {
    // $unzip_dir = unZipDownloadFile($download_file, $type);
    $unzip_dir = 'download-summary/';
    if($unzip_dir && file_exists($unzip_dir)) {
        $download_files = array_diff(scandir($unzip_dir), array('..', '.'));

        // echo json_encode($scanned_directory);
        
        foreach($download_files as $key => $download_file_name) {
            echo $download_file_name.": ".processDownloadFile($unzip_dir.$download_file_name, $type)."<br>";
        }
    }
}
