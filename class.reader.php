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
     * Returns header info to use in parsing array
     */
    function Header($source = array()) {
        $head = array('site' => null, 'adate' => null, 'id' => null, 'event' => null);
        foreach($source as $key => $f) {
            if ($key == 'lmt_subjectid') $head['subject'] = $f;
            if ($key == 'lmt_site') $head['site'] = $f;
            if ($key == 'lmt_assessmentDate') $head['adate'] = $f;
            if ($key == 'lmt_session') $head['event'] = $f;
        }
        return $head;
    }
    /*
     * GetSite
     * Returns the Site name from the file
     */
    function GetSite($source = array()) {
        $file = json_decode(file_get_contents($source), true);
        foreach($file as $key => $f) {
            if ($key == 'lmt_site') $site = $f;
        }
        return $site;
    }
    /* 
     * Parser
     * This method is for Parsing data from file
     *
     * @param string $source - file path to source file
     */
    function Parser($source) {
        $form = json_decode(file_get_contents($source), true);
        if (is_array($form)) {
            $head = $this->Header($form); // get header info
            foreach($form as $f) {
                $dpth = count($f);
                $x = 0; $log = null;
                if ($dpth > 1) {
                    foreach($f as $fs) {
                        // put each line in the table as a row for excel
                        foreach($fs as $key => $item) {
                            $x = sprintf('%02d', $x);
                            $fs[$key.'_'.$x] = $item; // add new key and value 
                            unset($fs[$key]); // drops the old key and value
                        }
                        $x++;
                        $fs['lmt_subject_id'] = $head['subject'];
                        $fs['lmt_event_name'] = $head['event'];
                        $fs['lmt_assessment_date'] = $head['adate'];
                        $fs['lmt_site'] = $head['site'];
                        $fs['lmt_complete'] = '0';
                        $lg = $this->Import($fs);
                        $log .= $lg;
                    }
                }
            }
        } else {
            echo 'NO DATA PRESENT';
        }
        return $log;
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
        return $output;
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
