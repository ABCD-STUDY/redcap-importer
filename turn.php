<?php
/*
*  Turn Cron
*  Cron Looks for recent files and processes them
*  Author: James Hudnall
*  (c) 2016 - ABCD UCSD
*/
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once 'code/php/config.php';
require_once 'code/php/class.reader.php';
$Path = $_SERVER["DOCUMENT_ROOT"] .'/applications/little-man-task/code/sites/';
$latestTime = 0;

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Path), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $fileinfo) {
    if ($fileinfo->isFile()) {
        if ($fileinfo->getMTime() > $latestTime) {
            $latestTime = $fileinfo->getMTime();
            $newFile = $fileinfo->getFilename();
            //print $newFile . " - " . date( "Y/m/d", $latestTime); 
        }
    }
}
$source = $Path . $newFile;
$read = new Reader();
$data = $read->Parser($source);
