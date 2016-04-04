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
        if (is_array($form)) {
            foreach($form as $f) {
                $dpth = count($f);
                if ($dpth > 1) {
                    foreach($f as $fs) {
                       $this->Import($fs);
                      //  foreach($fs as $key => $data) {
                            // $this->Process($key,$data);
                           // $line .= '['.$key.']['.$data.']';
                            //echo($line);
                        //}
                       // $this->Import($line);
                        //unset($line);
                    }
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
    function Process($key, $data) {

        $values = array(
        array('Field' => addslashes($key), 'Data' => addslashes($data)));

        /*** insert the array of values ***/
        $crud->dbInsert('formdata', $values);
        return true;
    }

    /*
     *  ImportData
     *  Method to communicate with Redcap Server using Token
     *
     */
    function ImportData($file, $token) {

        $file = curl_file_create($file, 'text/plain', 'importdata');

        $fields = array('token' => $token, 'content' => 'file', 'action' => 'import', 'record' => 'f21a3ffd37fc0b3c', 'field' => 'file_upload', 'event' => 'event_1_arm_1', 'file' => $file);

        $fields['returnFormat'] = 'json';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->_apiurl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields, '', '&'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Set to TRUE for production use
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);

        $output = curl_exec($ch);
        print $output;
        curl_close($ch);

    }

    function Import($line) {

        $data = json_encode($line);

        $fields = array('token' => $GLOBALS['api_token'], 'content' => 'record', 'format' => 'json', 'type' => 'flat', 'data' => $data, );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $GLOBALS['api_url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));

        $output = curl_exec($ch);
        print $output;
        curl_close($ch);

    }

}
?>
