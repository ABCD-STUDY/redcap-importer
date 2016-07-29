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
    var $obj = null;
    var $token = null;
    var $fname = null;
    var $url = null;
    var $pin = null;

    /*
     *  Constructor - autoloads variables needed for class
     */
    function __construct($source, $url, $project) {
        $this->obj = json_decode(file_get_contents($source), false);
        $this->fname = $source;
        $this->url = $url;
        $this->project = $project;
    }
    /*
     *  setToken - establish token for
     */
    function setToken($token) {
        $this->token = $token;
    }
    /*
     * is Active
     * Determines if the file is full or empty
     */
    function isActive() {
        $od = get_object_vars($this->obj);
        if (count($od['data']) > 0) {
            return true;
        } else {
            return false;
        }
    }
    /*
     * GetSite
     * Returns the Site name from the file
     */
    function GetSite($source = array()) {
        $site = NULL;
        $od = get_object_vars($this->obj);
        // look in the top level of $this->obj for a field that ends in _site
        if (isset($od[$this->project.'_site'])) {
            $site = $od[$this->project.'_site'];
        } else {
            echo("Error: could not find site as ".$this->project.'_site in '.$source."\n");
            return;
        }
        return $site;
    }
    /**
     *  GetFields
     *  API call to get the fields for the instrument for comparison
     *
     */
    function GetFields() {
        $data = array('token' => $this->token, 'content' => 'exportFieldNames', 'format' => 'json', 'returnFormat' => 'json');
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
    function Parser() {
        $log = null;
        $log1 = null;
        $log2 = null;
        $fields = $this->GetFields(); // get data dictionary from redcap
        $ks = array_keys(get_object_vars($this->obj));
        $k = get_object_vars($this->obj);
        $log1 = $this->iCheck($k[$this->project.'_subject_id'], $k[$this->project.'_event_name']); // check if imported already
        //   $this->readDataFile($source);
        $send = array();
        //
        // copy all global level keys from data
        //
        for ($i = 0; $i < count($ks); $i++) {
            if ($ks[$i] == "data") continue;
            // copy all that are unique
            $send[$ks[$i]] = trim($this->obj->{
                $ks[$i]
            });
        }

        $x = 0;
        // pull data array from object
        $data = $this->obj->data;
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
        echo $send;
        if (empty($send['record_id'])) $send['record_id'] = $send[$this->project.'_subject_id'];
        $log2 = $this->Import($send);

        $log = $log1.$log2;
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
        $data = array('token' => $this->token, 'content' => 'record', 'format' => 'json', 'type' => 'flat', 'overwriteBehavior' => 'normal', 'data' => $record, 'returnContent' => 'count', 'returnFormat' => 'json');
        curl_setopt($ch, CURLOPT_URL, $this->url);
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
                $rec = str_replace(",", ",\n\n", $record);
                // 				make readable
                $op = str_replace("{", "", $output);
                $op = str_replace("}", "", $op);
                $op = str_replace("\"", "", $op);
                print "INFO send ok for ".$this->fname."\n".'  <br/><span  style="color:green">Success: '.$op.'</span><br/>'."\n";
            } else {
                // 				show failed responses in red. Strip tags
                $rec = str_replace(",", ",\n\n", $record);
                // 				make readable
                $op = str_replace("[{", "", $rec);
                $op = str_replace("}]", "", $op);
                $op = str_replace("\"", "", $op);
                $log = "Error reading file ".$this->fname."\n REDCAP:".$output."\n";
                print '<br><font color="red">'.$log.'</font>';
                $log .= $record.$op;
            }
        }
        curl_close($ch);
        return $log;
    }
    /*
     *  iCheck
     *  Method to determine status of data before sending to avoid duplication
     */
    function iCheck($recordID, $event) {
        $log = null;
        $data = array('token' => $this->token, 'content' => 'record', 'format' => 'json', 'type' => 'flat', 'records' => array($recordID), 'events' => array($event), 'rawOrLabel' => 'raw', 'rawOrLabelHeaders' => 'raw', 'exportCheckboxLabel' => 'false', 'exportSurveyFields' => 'false', 'exportDataAccessGroups' => 'false', 'returnFormat' => 'json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
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
        curl_close($ch);
        if (strrpos($output, "record_id")) {
            $log = "\n Record found for: ".$recordID." Event: ".$event."\n";
            print $log."<p>";
        }
        return $log;
    }
    /*
     *  Determine which file type is being processed
     */
    function fileType($fname) {
        if (strrpos($fname, "Assessment Data")) {
            $type = 1;
        }
        elseif(strrpos($fname, "Assessment Scores")) {
            $type = 2;
        }
        elseif(strrpos($fname, "Registration Data")) {
            $type = 3;
        } else {
            $type = 0;
        }
        return $type;
    }
    /*
     *  parseCSV - Handles NIH files
     */
    function parseCSV($source) {
        $row = 1;
        $z = 0;
        $log = null;
        $jump = null;
        $iloc = "Inst";
        $send = array();
        // write to array
        if (($handle = fopen($source, "r")) !== FALSE) {
            $type = $this->fileType($source);
            switch ($type) {
                case 0:
                    break;
                case 1:
                    // Assessment Data
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if ($z == 0) {
                            $i = $this->getOrder($data, 1);
                        } else {
                            $send['pin'] = $data[$i[0]];
                            $send['device_id'] = $data[$i[1]];
                            $send['assessment_name'] = $data[$i[2]];
                            $send['instr_order'] = $data[$i[3]];
                            $send['inst_sctn'] = $data[$i[4]];
                            $send['itm_ordr'] = $data[$i[5]];
                            $send['inst'] = $data[$i[6]];
                            $send['locale'] = $data[$i[7]];
                            $send['item_id'] = $data[$i[8]];
                            $send['response'] = $data[$i[9]];
                            $send['score'] = $data[$i[10]];
                            $send['theta'] = $data[$i[11]];
                            $send['tscore'] = $data[$i[12]];
                            $send['se'] = $data[$i[13]];
                            $send['data_type'] = $data[$i[14]];
                            $send['position'] = $data[$i[15]];
                            $send['response_time'] = $data[$i[16]];
                            $send['date_created'] = $data[$i[17]];
                            $send['inst_start'] = $data[$i[18]];
                            $send['inst_ended'] = $data[$i[19]];
                        }
                        $z++;
                    }
                    break;
                case 2:
                    //  Assessment Scores
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if ($z == 0) {
                            $i = $this->getOrder($data, 2);
                        } else {
                            $send['scr_pin'] = $data[$i[0]];
                            $send['scr_device'] = $data[$i[1]];
                            $send['scr_name'] = $data[$i[2]];
                            $send['scr_inst'] = $data[$i[3]];
                            $send['scr_raw'] = $data[$i[4]];
                            $send['scr_theta'] = $data[$i[5]];
                            $send['scr_tscore'] = $data[$i[6]];
                            $send['scr_se'] = $data[$i[7]];
                            $send['scr_item_count'] = $data[$i[8]];
                            $send['scr_finished'] = $data[$i[9]];
                            $send['scr_col_1'] = $data[$i[10]];
                            $send['scr_col_2'] = $data[$i[11]];
                            $send['scr_col_3'] = $data[$i[12]];
                            $send['scr_col_4'] = $data[$i[13]];
                            $send['scr_col_5'] = $data[$i[14]];
                            $send['scr_language'] = $data[$i[15]];
                            $send['scr_comp_score'] = $data[$i[16]];
                            $send['scr_standard_score'] = $data[$i[17]];
                            $send['scr_age_score'] = $data[$i[18]];
                            $send['scr_corrected_tscore'] = $data[$i[19]];
                            $send['scr_breakoff'] = $data[$i[20]];
                            $send['scr_status_2'] = $data[$i[21]];
                            $send['scr_reason'] = $data[$i[22]];
                            $send['scr_reason_other'] = $data[$i[23]];
                        }
                        $z++;
                    }
                    break;
                case 3:
                    // Registration Data
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if ($z == 0) {
                            $i = $this->getOrder($data, 3);
                            var_dump($i);
                        } else {
                            $send['reg_pin'] = $data[$i[0]];
                            $send['reg_device'] = $data[$i[1]];
                            $send['reg_name'] = $data[$i[2]];
                            $send['reg_age'] = $data[$i[3]];
                            $send['reg_educate'] = $data[$i[4]];
                            $send['reg_mother'] = $data[$i[5]];
                            $send['reg_father'] = $data[$i[6]];
                            $send['reg_guardian'] = $data[$i[7]];
                            $send['reg_starting'] = $data[$i[8]];
                            $send['reg_gender'] = $data[$i[9]];
                            $send['reg_hand'] = $data[$i[10]];
                            $send['reg_race'] = $data[$i[11]];
                            $send['reg_etnicity'] = $data[$i[12]];
                        }
                        $z++;
                    }
                    break;
            }
            fclose($handle);
        }
        if ($type > 0) {
            $send['redcap_event_name'] = 'baseline_arm_1';
            echo '<p> AK:';
            print_r($ak);
            $rec = json_encode($send);
            $send = '['.$rec.']';
            echo $send;
            $log = $this->Import($send);
        }
        return $log;

    }
    /*
     * getOrder - Determine Order of fields
     */
    function getOrder($row, $type) {
        $x = 0;
        $ak = array();
        if (is_array($row)) {
            switch ($type) {
                case '1':
                foreach($row as $k => $v) {
                    switch ($v) {
                        case 'PIN':
                            $i[0] = $x;
                            break;
                        case 'DeviceID':
                            $i[1] = $x;
                            break;
                        case 'Assessment Name':
                            $i[2] = $x;
                            break;
                        case 'InstOrdr':
                            $i[3] = $x;
                            break;
                        case 'InstSctn':
                            $i[4] = $x;
                            break;
                        case 'Inst':
                            $i[5] = $x;
                            break;
                        case 'Locale':
                            $i[6] = $x;
                            break;
                        case 'ItemID':
                            $i[7] = $x;
                            break;
                        case 'Response':
                            $i[8] = $x;
                            break;
                        case 'Score':
                            $i[9] = $x;
                            break;
                        case 'Theta':
                            $i[10] = $x;
                            break;
                        case 'TScore':
                            $i[11] = $x;
                            break;
                        case 'SE':
                            $i[12] = $x;
                            break;
                        case 'DataType':
                            $i[7] = $x;
                            break;
                        case 'Position':
                            $i[8] = $x;
                            break;
                        case 'ResponseTime':
                            $i[9] = $x;
                            break;
                        case 'DateCreated':
                            $i[10] = $x;
                            break;
                        case 'InstStarted':
                            $i[11] = $x;
                            break;
                        case 'InstEnded':
                            $i[12] = $x;
                            break;
                    }
                    $x++;
            }
            break;
            case '2':
                foreach($row as $k => $v) {
                    switch ($v) {
                        case 'PIN':
                            $i[0] = $x;
                            break;
                        case 'DeviceID':
                            $i[1] = $x;
                            break;
                        case 'Assessment Name':
                            $i[2] = $x;
                            break;
                        case 'Inst':
                            $i[3] = $x;
                            break;
                        case 'RawScore':
                            $i[4] = $x;
                            break;
                        case 'Theta':
                            $i[5] = $x;
                            break;
                        case 'TScore':
                            $i[6] = $x;
                            break;
                        case 'SE':
                            $i[7] = $x;
                            break;
                        case 'ItmCnt':
                            $i[8] = $x;
                            break;
                        case 'DateFinished':
                            $i[9] = $x;
                            break;
                        case 'Column1':
                            $i[10] = $x;
                            break;
                        case 'Column2':
                            $i[11] = $x;
                            break;
                        case 'Column3':
                            $i[12] = $x;
                            break;
                        case 'Column4':
                            $i[13] = $x;
                            break;
                        case 'Column5':
                            $i[14] = $x;
                            break;
                        case 'Language':
                            $i[15] = $x;
                            break;
                        case 'Computed Score':
                            $i[16] = $x;
                            break;
                        case 'Uncorrected Standard Score':
                            $i[17] = $x;
                            break;
                        case 'Age-Corrected Standard Score':
                            $i[18] = $x;
                            break;
                        case 'Fully-Corrected T-score':
                            $i[19] = $x;
                            break;
                        case 'InstrumentBreakoff':
                            $i[20] = $x;
                            break;
                        case 'InstrumentStatus2':
                            $i[21] = $x;
                            break;
                        case 'InstrumentRCReason':
                            $i[22] = $x;
                            break;
                        case 'InstrumentRCReasonOther':
                            $i[23] = $x;
                            break;
                    }
                    $x++;
                }
                break;
            case '3':
                foreach($row as $k => $v) {
                    switch ($v) {
                        case 'PIN':
                            $i[0] = $x;
                            break;
                        case 'DeviceID':
                            $i[1] = $x;
                            break;
                        case 'Name':
                            $i[2] = $x;
                            break;
                        case 'Age':
                            $i[3] = $x;
                            break;
                        case 'Education':
                            $i[4] = $x;
                            break;
                        case 'MothersEducation':
                            $i[5] = $x;
                            break;
                        case 'FathersEducation':
                            $i[6] = $x;
                            break;
                        case 'GuardiansEducation':
                            $i[7] = $x;
                            break;
                        case 'StartingLevelOverride':
                            $i[8] = $x;
                            break;
                        case 'Gender':
                            $i[9] = $x;
                            break;
                        case 'Handedness':
                            $i[10] = $x;
                            break;
                        case 'Race':
                            $i[11] = $x;
                            break;
                        case 'Ethnicity':
                            $i[12] = $x;
                            break;
                    }
                    $x++;
                }
                break;
        }
    }
    return $ak;
}

}
?>