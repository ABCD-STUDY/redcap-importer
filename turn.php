<?php
/*
*  Turn Cron
*  Looks for recent files and processes them
*  Author: James Hudnall
*  (c) 2016 - ABCD UCSD
*/
error_reporting(E_ALL); // remove later
ini_set("display_errors", 1); // remove later
$dir =  $_SERVER["DOCUMENT_ROOT"] .'/applications/little-man-task/code/sites/';         
$pattern = '\.(json)$'; // check only for json files       
$newstamp = 0;            
$newname = "";

if ($handle = opendir($dir)) {               
       while (false !== ($fname = readdir($handle)))  {            
         // Eliminate current directory, parent directory            
         if (preg_match('^\.{1,2}$^',$fname)) continue;            
         // Eliminate other pages not in pattern            
         if (! preg_match($pattern,$fname)) continue;            
         $timedat = filemtime("$dir/$fname");            
         if ($timedat > $newstamp) {
            $newstamp = $timedat;
            $newname = $fname;
          }
         }
        }
closedir ($handle);


// Show found file (for testing only - remove)        
print $newname . " - " . date( "Y/m/d", $newstamp); 
