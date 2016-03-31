<html> 
<link href = "code/css/style.css" rel = "stylesheet" type = "text/css" />
</html>
<body>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once($_SERVER["DOCUMENT_ROOT"].'/code/php/abcd_etc.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/code/php/class.db.php');
$crud = new database(H, U, P, D);
$crud->connect();
$file = $_SERVER["DOCUMENT_ROOT"].'/applications/little-man-task/code/sites/lmt_HAUKE_HAUKE.json'; change to varaible passed to this script by turn.php
require_once 'code/php/class.reader.php';
$read = new Reader();
$data = $read->Parser($file);
