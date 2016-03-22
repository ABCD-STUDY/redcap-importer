<?php
/** 
* Reader - Reads and writes from JSON file to Redcap DB
*
*  Author: James Hudnall
*  Version 1.0 
*  
*/
class Reader {
/* Parse data from file */
     function Parser($source) {
        if (file_exists ($source)) {
        $rh = fopen($source, 'r');
        if ($rh===false) {
        // error reading or opening file
        echo "Fail: " . $rh;
           return true;
        }
        while (!feof($rh)) {
            if ($contents = fread($rh, filesize($source)) != FALSE) {
                   echo 'CODE: ' . json_decode($contents);
                   $this->Process($contents);
                   // 'Download error: Cannot write to file ('.$file_target.')';
                   return true;
               }
        }
        fclose($rh);
        // No error
        } else {
            echo 'File Not Found: ' . $source . '<br/>';
        }
        return false;
     }
    /* process data pulled */
    function Process($row) {
        var_dump(json_decode($row));

        $values = array(
         array(
    'Field'=>addslashes($Field),    
    'Data'=>addslashes($Data)
    ));

    /*** insert the array of values ***/
    $crud->dbInsert('formdata', $values);
	 header("location:". ABCD_HOST);
        return true;
    }
 }
?>
