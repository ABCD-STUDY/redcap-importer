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

//echo $source . '<p>&nbsp;</p>';

// split the subject and event from the name IF there is any file
if (file_exists($source)) {
	
	$c = preg_split('/[_.]/i', $newFile, -1, PREG_SPLIT_DELIM_CAPTURE);
	
	$event = $c[0];
	
	$subject = $c[1];
	
	$read = new Reader();
	
	$site = $read->GetSite($source);
	
	if ($log = $read->Parser($source,$subject, $event)) {
		
		$dir = $Path . '/archive/' . $site . '/';
		
		if( !is_dir($dir)) mkdir($dir, 0777, true);
		
		rename($source, $dir . $newFile);
		// 		move file if successfully processed
	}
	else {
		echo 'Failed';
		$dir = $Path . '/error/' . $site . '/';
		$fl = 'error_' . $newFile;
		if( !is_dir($dir)) mkdir($dir, 0777, true);
        // create log
        $out = fopen($fl, "w");
        fwrite($out, $log);
        fclose($out);
		// move file to error directory
		rename($source, $dir . $newFile);
		// 		move file on errors
	}
	
} else {
	echo 'No File Found';
}


