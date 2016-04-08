<?php
/*
*  Turn Cron
*  Looks for recent files and processes them
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
        }
    }
}
$source = $Path . $newFile;
$read = new Reader();
if ($read->Parser($source)) {
    rename($source, $Path . '/read/$newFile');   // move file if successfully processed
}

