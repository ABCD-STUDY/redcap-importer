<?php
/*
*  Turn Cron
*  Looks for recent files and processes them
*  Author: James Hudnall
*  (c) 2016 - ABCD UCSD
*/
error_reporting(E_ALL);
ini_set("display_errors", 1);

//parse_str($_SERVER['argv'][1], $_GET); // capture cron variables passed
//var_dump($_GET);
// remove two lines below. Testing only
require_once 'code/php/config.php'; // REMOVE - for testing only
$token = $GLOBALS['api_token']; // REMOVE - for testing only

/*
require_once 'code/php/class.reader.php';


# todo: change based on the current user

$offline=FALSE;
if( !isset($_SERVER) || !isset($_SERVER["DOCUMENT_ROOT"]) || $_SERVER["DOCUMENT_ROOT"] == "") {
$_SERVER["DOCUMENT_ROOT"] = "/var/www/html/";
$offline = TRUE;
echo("START in offline mode");
}

if ($offline == FALSE) {

session_start();

include($_SERVER["DOCUMENT_ROOT"]."/code/php/AC.php");
$user_name = check_logged(); /// function checks if we are logged in
$admin = false;

if ($user_name == "") {
// user is not logged in
return;
} else {
$admin = true;
}

$permissions = list_permissions_for_user( $user_name );

// find the first permission that corresponds to a site
// Assumption here is that a user can only add assessment for the first site he has permissions for!
$site = "";
foreach ($permissions as $per) {
$a = explode("Site", $per); // permissions should be structured as "Site<site name>"

if (count($a) > 0) {
$site = $a[1];
break;
}
}
# todo, do this for all site permissions, not just the first one
if ($site == "") {
echo (json_encode ( array( "message" => "Error: no site assigned to this user" ) ) );
return;
}

# use the site to lookup the correct token
$tokens = json_decode(file_get_contents('tokens.json'),true);
$keys = array_keys($tokens);
$token = "";
foreach($keys as $k) {
if (strtolower($k) == strtolower($site)) {
$token = $tokens[$k];
}
}
} else {
$token = "9560341DB5CD569629990DD4BF8D5947"; // test token for offline mode
}
*/
$api_token = $token; // "9560341DB5CD569629990DD4BF8D5947";
$api_url   = "https://abcd-rc.ucsd.edu/redcap/api/";

require_once 'code/php/class.reader.php';

$RootPath = '/var/www/html/applications/importerREDCap/';

$Path = $RootPath.'testdata';

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
$read = new Reader();
//echo 'Files: ' . count($filestodo) . '<p>';
foreach($filestodo as $source) {
    // split the subject and event from the name IF there is any file
    
    if (file_exists($source)) {
        $pp = pathinfo($source);
        $project = substr($pp['filename'], 0, 3);  // set project based on first three characters
        //echo 'Project: ' . $project . '<p>';
        $obj = (array) json_decode(file_get_contents($source), true);
       
        if ($read->isActive($source)) {
            
            $read->setProject($project);
            $site    = $read->GetSite($source);
            
            $log = $read->Parser($source);
            if (!isset($log)) {
                
                $dir = $RootPath . 'archive_'.$project."_".$site . '/';
                
                if( !is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                $finfo = pathinfo($source);
                $pathinfo = pathinfo($source);
                // rename($source, $dir . DIRECTORY_SEPARATOR . $pathinfo['filename'].".".$pathinfo['extension']);
                // 		move file if successfully processed
            }
            else {
                $dir = $RootPath . 'error_'.$project.'_'. $site . '/';
                
                if( !is_dir($dir)) {
                    echo("create directory: ". $dir. "\n");
                    mkdir($dir, 0777, true);
                }
                $finfo = pathinfo($source);
                
                // create log
                $out = fopen($dir.DIRECTORY_SEPARATOR.$finfo['filename']."_error.txt", "w");
               // fwrite($dir . $out, $log);
                fclose($out);
                // move file to error directory
                $pathinfo = pathinfo($source);
                // move into error directory
                // rename($source, $dir . DIRECTORY_SEPARATOR . $pathinfo['filename'].".".$pathinfo['extension']);
                //rename($source, $dir . $newFile);
            }
        } else {
            print_r($obj);
        }
    } else {
        echo 'File Not Found: ' . $source;
    }
}