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
    * Header
    * Grabs array header info to use in parsing array
    *
    */
    function Header($source) {
            foreach($source as $f) {
                //var_dump($source);
                $dpth = count($f);
                if ($dpth == 1) {
                   // foreach($f as $fs){
                    // load header variables
                    print_r($f);
                    //if ($fs['site']) array($head = array('site'=>$fs['site'],'adate'=>$fs['assessmentDate'],'id'=>$fs['subjectid'],'event'=>$fs['event']));
                   // }
                }
        }
       // var_dump($head);
        return $head;
    }
    /* 
     * Parser
     * This method is for Parsing data from file
     *
     * @param string $source - file path to source file
     */
    function Parser($source, $head) {
        $form = json_decode(file_get_contents($source), true);
        if (is_array($form)) {
            $head = $this->Header($form); // get header info
            foreach($form as $f) {
                $dpth = count($f);
                $x=0;
                if ($dpth > 1) {
                    foreach($f as $fs) {
                        // put each line in the table as a row for excel
                        foreach($fs as $key => $item) {
                             $fs[$key.'_'.$x] = $item; // add new key and value 
                             unset($fs[$key]); // drops the old key and value
                        }
                        $x++;
                        $fs['record_id'] = $head['id'];
                        $fs['redcap_event_name'] = $head['event'];
                        $fs['assessmentDate'] = $head['adate'];
                        $fs['site'] = $head['site'];
                        $fs['little_man_task_complete'] = '0';
                        // $this->Import($fs);
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
        $record = '['.$rec.']';
        echo $record.'<br/><font style="color:blue">Redcap:</font><span  style="color:red">  ';

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
    /**
     *  Export
     *  Method to determine status of data before sending to avoid duplication
     *
     **/
    function Export($recordID) { 
        $data = array('token' => $GLOBALS['api_token'], 'content' => 'record', 'format' => 'json', 'type' => 'flat', 'rawOrLabel' => 'raw', 'rawOrLabelHeaders' => 'raw', 'exportCheckboxLabel' => 'false', 'exportSurveyFields' => 'false', 'exportDataAccessGroups' => 'false', 'returnFormat' => 'json');
        $ch = curl_init();
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
        $output = curl_exec($ch);
        print $output;
        curl_close($ch);


    }
}
?>
