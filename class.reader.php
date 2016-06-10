<?php 
/**
 * Reader - Processes file json data
 *
 *  Author: James Hudnall
 *  Version 1.0
 *
 */
class Reader {

    var $project = ''; // member variable to indicate what preset should be used to read values

    function setProject($project) {
        $this->project = $project;
    }

    /*
     * GetSite
     * Returns the Site name from the file
     */

    function GetSite($source = array()) {
        $site = NULL;
        $obj = (object) json_decode(file_get_contents($source), false);
        switch ($this->project) {
            case 'ded':
                $site = $obj->{
                    "ded_site"
                };
                break;
            case 'lmt':
                $site = $obj->{
                    "lmt_site"
                };
                break;
            case 'str':
                $site = $obj->{
                    "str_site"
                };
                break;
        }
        return $site;
    }
    /**
     *  GetFields
     *  API call to get the fields for the instrument for comparison
     *
     */
    function GetFields() {

        $data = array('token' => $GLOBALS['api_token'], 'content' => 'exportFieldNames', 'format' => 'json', 'returnFormat' => 'json');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://abcd-rc.ucsd.edu/redcap/api/');
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
        //print $output;
        curl_close($ch);
        $p = explode(",", $output);

        $px = count($p);
        $pz = ($px / 3); // remove excess info by splitting data
        $x = 0;
        $y = 0;
        // strip out excess into to get field name from returned
        while ($y <= $pz) {
            if ($px > $x) $op = str_replace('"original_field_name":"', "", $p[$x]);
            $op = str_replace('"', "", $op);
            $op = str_replace('[', "", $op);
            $op = str_replace('{', "", $op);
            $flds[$y] = $op;
            $y++;
            $x = ($x + 3);
        }
        return $flds;
    }
    /*
     * Parser
     * This method is for Parsing data from file
     *
     * @param string $source - file path to source file
     */

    function Parser($source) {

        $fields = $this->GetFields(); // get data dictionary from redcap
        $arr = (array) json_decode($source); // cast data to object for processing
        $obj = (object) json_decode(file_get_contents($source), true); // cast data to object for processing
        $log = null;
        $send = array();

        //
        // copy all global level keys from data
        //
        $ks = array_keys(get_object_vars($obj));
        for ($i = 0; $i < count($ks); $i++) {
            if ($ks[$i] == "data") continue;
            if ($ks[$i] == $this->project."_subject_id") continue;
            if ($ks[$i] == "event_name") continue;
            // copy all that are unique
            $send[$ks[$i]] = $obj->{
                $ks[$i]
            };
        }
        array_keys($arr);
        switch ($this->project) {
            case 'ded':
                $send['record_id'] = $obj->{
                    "ded_subject_id"
                };
                $send['ded_id'] = $obj->{
                    "ded_subject_id"
                };
                $send['redcap_event_name'] = $obj->{
                    "ded_event_name"
                };
                break;
            case 'lmt':
                $send['record_id'] = $obj->{
                    "lmt_subject_id"
                };
                $send['lmt_id'] = $obj->{
                    "lmt_subject_id"
                };
                $send['redcap_event_name'] = $obj->{
                    "lmt_event_name"
                };
                break;
            case 'str':
                $send['record_id'] = $obj->{
                    "str_subject_id"
                };
                $send['str_id'] = $obj->{
                    "str_subject_id"
                };
                $send['redcap_event_name'] = $obj->{
                    "str_event_name"
                };
                break;
        }


        $x = 0;
        // pull data array from object
        $data = $obj->data;
        // process data
        while ($x < count($data)) {
            foreach($data[$x] as $key => $item) {
                // 						put each line in the table as a row for excel
                ///echo $key . '-->' . $item . '<br/>';
                $x = sprintf('%02d', $x);
                if ($key === $this->project.'_stimulus') {
                    if (is_array($item)) $item = $item[0];
                    if (is_string($item)) $item = ($key === $this->project.'_stimulus') ? htmlspecialchars($item) : $item; // make sure html characters are encoded for stimulus
                }

                if (is_bool($item)) {
                    $item = ($item) ? "TRUE" : "FALSE";
                }
                $send[$key.'_'.$x] = $item;
                $k = $key.'_'.$x;
                if (!in_array($k, $fields)) print '<br><font style="color: red; font-size: 15pt; font-style: italic;">Field not Defined: '.$k.'</font><br>'; // field not in instrument
            }
            $x++;
        }
        // output assembled array to API for processing.
        $log = $this->Import($send);
        return $log;
    }
    /*
     *  Import
     *  Method to communicate with Redcap Server using Token
     *
     */
    function Import($line) {

        $log = null;
        $rec = json_encode($line);
        $record = '['.$rec.']';

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

            $pos = strrpos($output, "error");

            if ($pos === false) {

                // 				show successful responses in green. Strip tags
                $rec = str_replace(",", ",<br>", $record);
                // 				make readable
                $op = str_replace("{", "", $output);
                $op = str_replace("}", "", $op);
                $op = str_replace("\"", "", $op);
                print '<br/><span  style="color:green">Success: '.$op.'</span><br/>';
            } else {

                // 				show failed responses in red. Strip tags
                $rec = str_replace(",", ",<br>", $record);
                // echo $record;
                // 				make readable
                $op = str_replace("[{", "", $rec);
                $op = str_replace("}]", "", $op);
                $op = str_replace("\"", "", $op);
                print '<span  style="color:red"><b>'.$output.'</b></span></center><br/>';
                $log = $record.$op;
            }
        }

        curl_close($ch);

        return $log;

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