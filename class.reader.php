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
    function Parser($source, $subject, $event) {
        $form = json_decode(file_get_contents($source), true);
        if (is_array($form)) {
            foreach($form as $f) {
                $dpth = count($f);
                if ($dpth > 1) {
                    foreach($f as $fs) {
                        $fs['record_id'] = $subject;
                        $fs['redcap_event_name'] = $event;
                        $fs['little_man_task_complete'] = '0'; // may need to make this flexible for other tasks
                        $this->Import($fs);
                    }
                }
            }
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

        $rec = json_encode($line);
        $record = '['. $rec. ']';
        //echo $record . '<br/><font style="color:blue">Redcap:</font><span  style="color:red">  ';

        $ch = curl_init();

        
        $data = array('token' => $GLOBALS['api_token'], 'content' => 'record', 'format' => 'json', 'type' => 'flat', 'overwriteBehavior' => 'normal', 'data' => $record, 'returnContent' => 'count', 'returnFormat' => 'json');

        curl_setopt($ch, CURLOPT_URL, $GLOBALS['api_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));


        if ($output = curl_exec($ch)) {
            print $output.'</span><p>';
        } else {
            echo '<p>FAILED</p>';
        }
        curl_close($ch);
    }
}
?>
