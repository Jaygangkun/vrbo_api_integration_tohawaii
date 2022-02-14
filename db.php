<?php
// db init
$dbhost = "localhost";
$dbuser = "root";
$dbpwd = "root";
$dbname = "tohawaii";

$dbcon = mysqli_connect($dbhost, $dbuser, $dbpwd);

if (mysqli_connect_errno())
{
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}
$db_select = mysqli_select_db($dbcon, $dbname);

if(!$db_select){
    die("Failed to connect to DB: ".mysqli_error());
}