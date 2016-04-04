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
                    }
                }
            }
            ECHO 'Finished';
        } else {
            echo 'NO DATA PRESENT';
        }
    }

    /*
     *  Import
     *  Method to communicate with Redcap Server using Token
     *
     */
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
