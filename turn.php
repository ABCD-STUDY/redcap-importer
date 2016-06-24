<?php
/*
*  Turn Cron
*  Looks for recent files and processes them
*  Author: James Hudnall
*  (c) 2016 - ABCD UCSD
*/
error_reporting(E_ALL);
ini_set("display_errors", 1);
//
// parameter for this script as used by cron
//   turn.php <site> <project>
//   */10 * * * *      php turn.php /var/www/html/applications/little-man-task/ data/UCSD lmt
//
require_once 'class.reader.php';

if ($argc !== 4) {
   echo ("Usage: provide path <sub-path to data> <project shortcut>\n\n");
   return;
}
$RootPath  = $_SERVER['argv'][1];
$site_path = $_SERVER['argv'][2];
$project   = $_SERVER['argv'][3];
$url   = "https://abcd-rc.ucsd.edu/redcap/api/";
$logfile = $path.DIRECTORY_SEPARATOR.$project.".log";
file_put_contents($logfile, "called with : ".$RootPath." ".$site_path." ".$project."\n", FILE_APPEND);

// path to assessment files
$Path = $RootPath.DIRECTORY_SEPARATOR.$site_path;
// path to directory for failed imports
$FailedPath = $RootPath.DIRECTORY_SEPARATOR.'data_import_failed';
if (!is_dir($FailedPath)) {
   mkdir($FailedPath, 0700, TRUE);
}
// path to drectory for imported scans
$ArchivePath = $RootPath.DIRECTORY_SEPARATOR.'data_import_archive';
if (!is_dir($ArchivePath)) {
   mkdir($ArchivePath, 0700, TRUE);
}
$latestTime = 0;
$filestodo = array();
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Path), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $fileinfo) {
    if (in_array($fileinfo->getFilename(), array(".", "..")))
        continue;
    if (is_dir($fileinfo))
        continue;
    $path_parts = pathinfo($fileinfo);
    
    if ($fileinfo->isFile() && $path_parts['extension'] == "json") {
        $filestodo[] = $path_parts['dirname'] . DIRECTORY_SEPARATOR . $path_parts['filename'] . '.json';
    }
}
file_put_contents($logfile, "found ".count($filestodo)." files to process\n", FILE_APPEND);
// now start reading the files
foreach($filestodo as $source) {
    // split the subject and event from the name IF there is any file
    
    if (file_exists($source)) {
        file_put_contents($logfile, "  reading ".$source."\n", FILE_APPEND);
        $read = new Reader($source,$url,$project);	
        $pp = pathinfo($source);
      
        if ($read->isActive($source)) {
            file_put_contents($logfile, "  file is active: ".$source."\n", FILE_APPEND);
            $site    = $read->GetSite;
	    // use the site information to read the token for this upload
	    $tokens = json_decode(file_get_contents('/var/www/html/code/php/tokens.json'),TRUE);
	    if (!isset($tokens[$site])) {
               file_put_contents($logfile, "  ERROR detecting token for site : ".$site." from file ".$source."\n", FILE_APPEND);	    
	       continue;
	    }
	    $api_token = $tokens[$site];
	    $read->setToken($api_token);
            file_put_contents($logfile, "  use this token to read ".$api_token."\n", FILE_APPEND);
            $log = $read->Parser($source);
            if (!isset($log)) {
                file_put_contents($logfile, "  Successful import ".$source."\n", FILE_APPEND);
                // create a location to store error messages and files that could not be loaded
                $dir = $ArchivePath.DIRECTORY_SEPARATOR.$site;                
                if ( !is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
		
                $finfo = pathinfo($source);
                $pathinfo = pathinfo($source);
		file_put_contents($logfile, "  move file to ".$dir . DIRECTORY_SEPARATOR . $pathinfo['filename'].".".$pathinfo['extension']."\n", FILE_APPEND);
                rename($source, $dir . DIRECTORY_SEPARATOR . $pathinfo['filename'].".".$pathinfo['extension']);
                // 		move file if successfully processed
            } else {
                $dir = $FailedPath . DIRECTORY_SEPARATOR . $site;
                file_put_contents($logfile, "  import ERROR ".$source." with ->\"". $log."\"\n", FILE_APPEND);
                
                if( !is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                $finfo = pathinfo($source);
                
                // create log
		file_put_contents($dir.DIRECTORY_SEPARATOR.$finfo['filename']."_error.txt", $log);
                // move file to error directory
                $pathinfo = pathinfo($source);
		file_put_contents($logfile, "  move file to ".$dir . DIRECTORY_SEPARATOR . $pathinfo['filename'].".".$pathinfo['extension'], FILE_APPEND);
		
                // move into error directory
                rename($source, $dir . DIRECTORY_SEPARATOR . $pathinfo['filename'].".".$pathinfo['extension']);
            }
        }
    } else {
	file_put_contents($logfile, "ERROR: file not found ".$source, FILE_APPEND);
    }
}