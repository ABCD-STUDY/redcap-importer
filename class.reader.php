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

    /*
     * Header
     * Returns header info to use in parsing array
     */
    function Header($source = array()) {

        $head = array('site' => null, 'adate' => null, 'id' => null, 'event' => null);

        foreach($source as $key => $f) {

//            if ($key == 'record_id') $head['record_id'] = $f;
            if ($key == 'redcap_event_name') $head['event_name'] = $f;
            if ($key == $this->project.'_subjectid') $head['subject'] = $f;
            if ($key == $this->project.'_site') $head['site'] = $f;
            if ($key == $this->project.'_assessmentDate') $head['adate'] = $f;
            if ($key == $this->project.'_session') $head['event'] = $f;
        }
        return $head;
    }

    /*
     * GetSite
     * Returns the Site name from the file
     */

    function GetSite($source = array()) {
        $file = json_decode(file_get_contents($source), true);
	$info = array( "", "", "");
        foreach($file as $key => $f) {
            if ($key == $this->project.'_site')
	       $info[0] = $f;
            if ($key == $this->project.'_event')
	       $info[1] = $f;
            if ($key == $this->project.'_subject')
	       $info[2] = $f;
        }
        return $info;
    }

    /*
     * setProject
     * Set the project name for variables in the data
     */
    function setProject( $name ) {
       $this->project = $name;
    }

    /**
     *  GetFields
     *  API call to get the fields for the instrument for comparison
     *
     */
    function GetFields() {

        $data = array('token' => $GLOBALS['api_token'], 'content' => 'exportFieldNames', 'format' => 'json', 'returnFormat' => 'json');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $GLOBALS['api_url']); // 'https://abcd-rc.ucsd.edu/redcap/api/');
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
        $pz = ($px / 3);
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
            //print $op . ', ';
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

        $fields = $this->GetFields();
        $log = null;
	//echo("ERROR?: " .$source);
        $form = json_decode(file_get_contents($source), true);
	
	echo("\n\nRead source ". $source . " found : ". is_array($form)." of length: ". count($form)."\n\n");

        //$k = array_keys($form);
	//print_r($k);
	//print_r($form['data'][0]);

        if (is_array($form)) {
            $send = array();
            $head = $this->Header($form);  // get header info
	    $counter = 1;
            foreach($form['data'] as $f) { // get each group in turn
	        $k = array_keys($f);
		// print_r($k);

		for ($i = 0; $i < count($k); $i++) {
                   $x = sprintf('%02d', $counter);
		   $keyForRedcap = $k[$i].'_'.$x;
		   if (!in_array($keyForRedcap, $fields)) {
		      print '<br><font style="color: red; font-size: 15pt; font-style: italic;">Field not Defined: '.$keyForRedcap.'</font><br>'."\n";
		   } else {
		      $v = $f[$k[$i]];
		      if ($k[$i] == $this->project.'stimulus') {
		        $v = htmlspecialchars($f[$k[$i]]);
		      }
  		      $send[$keyForRedcap] = $v;
		   }
		}
		$counter = $counter + 1;

		/*		
                $dpth = count($f);
                $x = 0;

                if ($dpth > 1) {
                    foreach($f as $fs) {
                        // 						put each line in the table as a row for excel
                        foreach($fs as $key => $item) {

                            $x = sprintf('%02d', $x);
                            $item = ($key === $this->project .'stimulus') ? htmlspecialchars($item) : $item; // make sure html characters are encoded for stimulus
                            $send[$key.'_'.$x] = $item;
                            $k = $key.'_'.$x;
                            if (!in_array($k, $fields)) print '<br><font style="color: red; font-size: 15pt; font-style: italic;">Field not Defined: '.$k.'</font><br>'; // field not in instrument
                            // 							add new key and value 
                            unset($fs[$key]);
                            // 							drops the old key and value
                        }
                        $x++;
                        ///////////////////////////////////// array was previously built
                    }
                } */
            }
            // output assembled array to API for processing
            $send['record_id'] = $head['subject'];
            $send[$this->project.'_subject_id'] = $head['subject'];
            $send[$this->project.'_event_name'] = $head['event'];
            $send['redcap_event_name'] = $head['event'];
            $send[$this->project.'_assessment_date'] = $head['adate'];
            $send[$this->project.'_site'] = $head['site'];
            //$send['little_man_task_complete'] = '0';
            $lg = $this->Import($send);
            $log .= $log . $lg;
        }
	
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
	//$rec = "[{ \"record_id\": \"NDARAB123CDE\" }]";
        $record = '['.$rec.']';
        //echo "SEND TO REDCAP:" ."\n\n".$record."\n\n";

        //echo $record.'<br/><font style="color:blue">Response:</font>  ';

        $ch = curl_init();

        $data = array(
	      'token' => $GLOBALS['api_token'],
	      'content' => 'record',
	      'format' => 'json',
	      'type' => 'flat', 
              'overwriteBehavior' => 'normal',
	      'data' => $record,
	      'returnFormat' => 'json');
	//print_r($data);
        //echo("Use url:\"".$GLOBALS['api_url']."\"\n".http_build_query($data, '', '&')."\n");

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

            //echo(" OUTPUT OF SEND OPERATION\n\n");
	    //print_r($output);
            //echo("\n\n OUTPUT OF SEND OPERATION\n\n");
	    

            $pos = strrpos($output, "error");

            if ($pos === false) {

                // 				show successful responses in green. Strip tags
                $rec = str_replace(",", ",<br>", $record);
                // 				make readable
                $op = str_replace("{", "", $output);
                $op = str_replace("}", "", $op);
                $op = str_replace("\"", "", $op);
                print '<hr>'.$rec.'<br/><span  style="color:green">Success: '.$op.'</span><br/>';
            } else {

                // 				show failed responses in red. Strip tags
                $rec = str_replace(",", ",<br>", $record);

                // 				make readable
                $op = str_replace("{", "", $output);
                $op = str_replace("}", "", $op);
                $op = str_replace("\"", "", $op);
                //print '<hr>'.$rec.'<br/><span  style="color:red"><b>'.$op.'</b></span></center>';
                print $op . "\r\n";
                      
		$log = $record.'<br/><span  style="color:red"><b>'.$op.'</b></span><br/>';
            }
        } else {

            echo '<p>IMPORT FAILED</p>';

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
