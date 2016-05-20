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

			if ($key == 'record_id') $head['record_id'] = $f;
			if ($key == 'redcap_event_name') $head['event_name'] = $f;
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
		
        $fields = $this->GetFields();
		$log = null;
		$form = json_decode(file_get_contents($source), true);

		if (is_array($form)) {
			$head = $this->Header($form);
			// 			get header info
			foreach($form as $f) {
				$dpth = count($f);
				$x = 0;

				if ($dpth > 1) {
					foreach($f as $fs) {
						// 						put each line in the table as a row for excel
						foreach($fs as $key => $item) {

							$x = sprintf('%02d', $x);
                            $item = ($key==='lmt_stimulus') ? htmlspecialchars($item) : $item; // make sure html characters are encoded
							$fs[$key.'_'.$x] = $item;
                            $k = $key.'_'.$x;
                            if (!in_array($k, $fields)) print '<br><font style="color:red">FAIL: ' . $k . '</font><br>'; // field not in instrument
							// 							add new key and value 
							unset($fs[$key]);
							// 							drops the old key and value
						}
						$x++;
						$fs['record_id'] = $head['record_id'];			
						$fs['lmt_subject_id'] = $head['subject'];					
						$fs['lmt_event_name'] = $head['event'];
                        $fs['redcap_event_name'] = $head['event'];						
						$fs['lmt_assessment_date'] = $head['adate'];												
						$fs['lmt_site'] = $head['site'];					
						$fs['little_man_task_complete'] = '0';	
						$lg = $this->Import($fs);	
						$log .= $lg;
			
					}
				}
			}	
		}

		else {

			echo 'NO DATA PRESENT';	
		}

		return $log;	
	}
	/**
    *  GetFields
    *  API call to get the fields for the instrument for comparison
    *
    */
	function GetFields() {
		
		$data = array(
		'token' => $GLOBALS['api_token'],
		'content' => 'exportFieldNames',
		'format' => 'json',
		'returnFormat' => 'json'
		);
		
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
        $pz = ($px/3);
        $x = 0;$y = 0;
        while ($y<=$pz) {
        if ($px > $x) $op  = str_replace('"original_field_name":"', "", $p[$x]);
        $op  = str_replace('"', "", $op);
        $op  = str_replace('[', "", $op);
        $op  = str_replace('{', "", $op);
        $flds[$y] = $op;
        $y++;$x = ($x + 3);
        //print $op . ', ';
        }
		return $flds;
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

		//echo $record.'<br/><font style="color:blue">Response:</font>  ';

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
				//$rec = str_replace(",", ",<br>", $record);
				
				
				// 				make readable
				$op  = str_replace("{", "", $output);				
				$op  = str_replace("}", "", $op);
				$op  = str_replace("\"", "", $op);
				print '<hr>' . $rec . '<br/><span  style="color:green">Success: '.$op.'</span><br/>';
			}
			else {
					
				// 				show failed responses in red. Strip tags
				$rec = str_replace(",", ",<br>", $record);

				// 				make readable
				$op  = str_replace("{", "", $output);
				$op  = str_replace("}", "", $op);
				$op  = str_replace("\"", "", $op);
				print '<hr>' . $rec . '<br/><span  style="color:red"><b>'.$op.'</b></span></center>';
				$log = $record.'<br/><span  style="color:red"><b>'.$op.'</b></span><br/>';
			}	
		}
		
		
		else {

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
