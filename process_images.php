<?php
include('./config.php');
include('./db.php');
include('./functions.php');

$type = 'images';
$download_file = downloadFile($type);

if($download_file) {
    $unzip_dir = unZipDownloadFile($download_file, $type);
    if($unzip_dir && file_exists($unzip_dir)) {
        $download_files = array_diff(scandir($unzip_dir), array('..', '.'));        
        foreach($download_files as $key => $download_file_name) {
            echo $download_file_name.": ".processDownloadFile($unzip_dir.$download_file_name, $type)."<br>";
        }
    }
}