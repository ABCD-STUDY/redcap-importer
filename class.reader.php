<?php
/** 
* Reader - Processes file json data
*
*  Author: James Hudnall
*  Version 1.0 
*  
*/
class Reader {
/* 
* Parser
* This method is for Parsing data from file 
*
* @param string $source - file path to source file
*/
   function Parser($source) {
        $form = json_decode(file_get_contents($source), true);
			if (is_array($form)){
                $a = 0;
 
                //print_r($form);echo '<br/><hl>';
                foreach ( $form as $f ) {
				foreach ($f as $key=>$data){
                    $this->Process($key,$data);
				}
         
                }
			} else {
                echo 'NO DATA PRESENT';
            }
     }
/* 
* Process
* Writes data passed to it to the database 
*
* @param string $key - Field Name being Passed
* @param string $data - Data value passed
*/
    function Process($key,$data) {
       require_once 'class.crud.php';
       $crud = new crud();
        $values = array(
         array(
    'Field'=>addslashes($key),    
    'Data'=>addslashes($data)
    ));

    /*** insert the array of values ***/
    $crud->dbInsert('formdata', $values);
    return true;
    }
 }
?>
