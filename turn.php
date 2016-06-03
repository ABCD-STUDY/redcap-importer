<?php

/*
*  Turn Cron
*  Looks for recent files and processes them
*  Author: James Hudnall
*  (c) 2016 - ABCD UCSD
*/

error_reporting(E_ALL);

ini_set("display_errors", 1);

# todo: change based on the current user

$api_token = "9560341DB5CD569629990DD4BF8D5947";
$api_url   = "https://abcd-rc.ucsd.edu/redcap/api/";

require_once 'class.reader.php';
$project = 'lmt';

#$RootPath = $_SERVER["DOCUMENT_ROOT"] .'/applications/importerREDCap/';
$RootPath = '/var/www/html/applications/importerREDCap/';
#$Path = $RootPath.'data_'.$project;
$Path = $RootPath.DIRECTORY_SEPARATOR.'testdata';

$latestTime = 0;


$filestodo = array();
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Path), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $fileinfo) {
        if (in_array($fileinfo->getFilename(), array(".", "..")))
	   continue;
	if (is_dir($fileinfo))
	   continue;
        $path_parts = pathinfo($fileinfo);
	//echo("Check file: ". $fileinfo->getFilename(). "\n");
	//echo("file extension is ".$path_parts['extension']);
	//echo("isfile?".$fileinfo->isFile() );
	
	if ($fileinfo->isFile() && $path_parts['extension'] == "json") {
		// if ($fileinfo->getMTime() > $latestTime) {
		//	$latestTime = $fileinfo->getMTime();
		//	$newFile = $fileinfo->getFilename();
		//}
		$filestodo[] = $path_parts['dirname'] . DIRECTORY_SEPARATOR . $path_parts['filename'] . '.json';
	}
}
//echo("we have the following files to check");
//print_r($filestodo);
//echo("\n");
// $source = $Path . $newFile;

foreach($filestodo as $source) {
  // split the subject and event from the name IF there is any file
  if (file_exists($source)) {
	#$c = preg_split('/[_.]/i', $newFile, -1, PREG_SPLIT_DELIM_CAPTURE);
	
	#$event = $c[0];
	
	#$subject = $c[1];
	
	$read = new Reader();
	$read->setProject($project);
	
	$info    = $read->GetSite($source);
	$site    = $info[0];
	$event   = $info[1];
	$subject = $info[2];

	$log = $read->Parser($source);
	if  (!isset($log)) {
		
		$dir = $RootPath . 'archive_'.$project."_".$site . '/';
		
	        echo("\n\ncreate directory: \"".$dir ."\"\n");
		if( !is_dir($dir)) {
		  mkdir($dir, 0777, true);
		}
		$finfo = pathinfo($source);
		//echo ("RENAME: ". $source. " to ". $dir . $finfo['filename']. ".".$finfo['extension']);
	//	rename($source, $dir . $finfo['filename']. ".".$finfo['extension'] );
		// 		move file if successfully processed
	} else {		
		$dir = $RootPath . 'error_'.$project.'_'. $site . '/';

		
		if( !is_dir($dir)) {
		    echo("create directory: ". $dir. "\n");
		    mkdir($dir, 0777, true);
		}
		$finfo = pathinfo($source);
		
        	// create log
                $out = fopen($dir.DIRECTORY_SEPARATOR.$finfo['filename']."_error.txt", "w");
                fwrite($dir . $out, $log);
                fclose($out);
		// move file to error directory
		//	rename($source, $dir.DIRECTORY_SEPARATOR.$finfo['filename'].".".$finfo['extension']);
		// echo("Wrote error info file into: ". $dir.$finfo['filename'].".".$finfo['extension']);
		// 		move file on errors
	}
  }
}

