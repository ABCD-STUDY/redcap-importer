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
     *  Constructor
     */
    function __construct($source) {
        $this->obj = json_decode(file_get_contents($source), false);
        $this->fname = $source;
        $this->url = $GLOBALS['api_url'];
    }
    /*
     * setProject
     * estabishes the project prefix
     */
    function setProject($project) {
        $this->project = $project;
    }

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
    function Parser($source) {
        $fields = $this->GetFields(); // get data dictionary from redcap
        //   $this->readDataFile($source);
        $log = null;
        $send = array();
        //
        // copy all global level keys from data
        //
        $ks = array_keys(get_object_vars($this->obj));

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
        if (!$send['record_id']) $send['record_id'] = $send[$this->project.'_subject_id'];
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
        $data = array('token' => $this->token, 'content' => 'record', 'format' => 'json', 'type' => 'flat', 'overwriteBehavior' => 'normal', 'data' => $record, 'returnContent' => 'count', 'returnFormat' => 'json');
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
                print "INFO send ok for ".$this->fname."\n".'  <br/><span  style="color:green">Success: '.$op.'</span><br/>'."\n";
            } else {
                // 				show failed responses in red. Strip tags
                $rec = str_replace(",", ",<br>", $record);
                // echo $record;
                // 				make readable
                $op = str_replace("[{", "", $rec);
                $op = str_replace("}]", "", $op);
                $op = str_replace("\"", "", $op);
                print "Error reading file ".$this->fname."\n".'   <span  style="color:red"><b>'.$output.'</b></span></center><br/>'."\n";
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
    function Export($recordID, $event) { <? php
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
        print $output;
        curl_close($ch);


    }
    /*
     *  parseCSV - Handles NIH files
     */
    function parseCSV($source) {
        $row = 1;
        $a = 0;
        $iloc = "Inst";
        $file = array();
        if (($handle = fopen($source, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($data[6] != $iloc) {
                    $iloc = $data[6];
                    echo '<p>';
                }
                $num = count($data);
                /*
        echo "<p>$data[6] - $num fields in line $row: <br /></p>\n";
        $row++;
        for ($c = 0; $c < $num; $c++) {
            echo $data[$c]." | \n";
        }
        */
                $file[$a] = array('pin' => $data[0], 'device' => $data[1], 'name' => $data[2], 'order' => $data[3], 'sctn' => $data[4], 'iorder' => $data[5], 'inst' => $data[6], 'locale' => $data[7], 'item' => $data[8], 'response' => $data[9], 'score' => $data[10], 'theta' => $data[11], 'TScore' => $data[12], 'SE' => $data[13], 'datatype' => $data[14], 'position' => $data[15], 'response' => $data[16], 'created' => $data[17], 'start' => $data[18], 'end' => $data[19]);
                $a++;
            }
            fclose($handle);
        }
        echo '<p>&nbsp;</p>';
        foreach($file as $k => $v) {
            echo "<p>";
            print($k);
            echo "\n";
            foreach($v as $key => $val) {
                print($key.': '.$val." \n");
            }
        }
    }
}
?>