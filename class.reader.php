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
        $a = 0;
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
                        if ($a == 0) {
                            $ak = $this->getOrder($data, 1);
                        } else {
                            $send['pin'] = $data[0];
                            $send['device_id'] = $data[1];
                            $send['assessment_name'] = $data[2];
                            $send['instr_order'] = $data[3];
                            $send['inst_sctn'] = $data[4];
                            $send['itm_ordr'] = $data[5];
                            $send['inst'] = $data[6];
                            $send['locale'] = $data[7];
                            $send['item_id'] = $data[8];
                            $send['response'] = $data[9];
                            $send['score'] = $data[10];
                            $send['theta'] = $data[11];
                            $send['tscore'] = $data[12];
                            $send['se'] = $data[13];
                            $send['data_type'] = $data[14];
                            $send['position'] = $data[15];
                            $send['response_time'] = $data[16];
                            $send['date_created'] = $data[17];
                            $send['inst_start'] = $data[18];
                            $send['inst_ended'] = $data[19];
                        }
                        $a++;
                    }
                    break;
                case 2:
                    //  Assessment Scores
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if ($a == 0) {
                            $ak = $this->getOrder($data, 2);
                        } else {
                            $send['scr_pin'] = $data[0];
                            $send['scr_device'] = $data[1];
                            $send['scr_name'] = $data[2];
                            $send['scr_inst'] = $data[3];
                            $send['scr_raw'] = $data[4];
                            $send['scr_theta'] = $data[5];
                            $send['scr_tscore'] = $data[6];
                            $send['scr_se'] = $data[7];
                            $send['scr_item_count'] = $data[8];
                            $send['scr_finished'] = $data[9];
                            $send['scr_col_1'] = $data[10];
                            $send['scr_col_2'] = $data[11];
                            $send['scr_col_3'] = $data[12];
                            $send['scr_col_4'] = $data[13];
                            $send['scr_col_5'] = $data[14];
                            $send['scr_language'] = $data[15];
                            $send['scr_comp_score'] = $data[16];
                            $send['scr_standard_score'] = $data[17];
                            $send['scr_age_score'] = $data[18];
                            $send['scr_corrected_tscore'] = $data[19];
                            $send['scr_breakoff'] = $data[20];
                            $send['scr_status_2'] = $data[21];
                            $send['scr_reason'] = $data[22];
                            $send['scr_reason_other'] = $data[23];
                        }
                        $a++;
                    }
                    break;
                case 3:
                    // Registration Data
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if ($a == 0) {
                            $ak = $this->getOrder($data, 3);
                        } else {
                            var_dump($data);
                            $send['reg_pin'] = $data[0];
                            $send['reg_device'] = $data[1];
                            $send['reg_name'] = $data[2];
                            $send['reg_age'] = $data[3];
                            $send['reg_educate'] = $data[4];
                            $send['reg_mother'] = $data[5];
                            $send['reg_father'] = $data[6];
                            $send['reg_guardian'] = $data[7];
                            $send['reg_starting'] = $data[8];
                            $send['reg_gender'] = $data[9];
                            $send['reg_hand'] = $data[10];
                            $send['reg_race'] = $data[11];
                            $send['reg_etnicity'] = $data[12];
                        }
                        $a++;
                    }
                    break;
            }
            fclose($handle);
        }
        if ($type > 0) {
            $send['redcap_event_name'] = 'baseline_arm_1';
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
        $ak = array();
        if (is_array($row)) {
            switch ($type) {
                case '1':
                    foreach($row as $k => $v) {
                        $ak[$k] = $v;
                    }
                    break;
                case '2':
                    foreach($row as $k => $v) {
                        $ak[$k] = $v;
                    }
                    break;
                case '3':
                    foreach($row as $k => $v) {
                        $ak[$k] = $v;
                    }
                    break;
            }
        }
        return $ak;
    }

}
?>