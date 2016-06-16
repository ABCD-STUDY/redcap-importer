<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);
// writes instrument to screen for saving as csv
$out = '"Variable / Field Name","Form Name","Section Header","Field Type","Field Label","Choices, Calculations, OR Slider Labels","Field Note","Text Validation Type OR Show Slider Number","Text Validation Min","Text Validation Max",Identifier?,"Branching Logic (Show field only if...)","Required Field?","Custom Alignment","Question Number (surveys only)","Matrix Group Name","Matrix Ranking?","Field Annotation"'. PHP_EOL .
'record_id,delay_discounting,,text,"Record ID",,,,,,,,,,,,,' . PHP_EOL .
'ded_id,delay_discounting,,text,"Record ID",,,,,,,,,,,,,' . PHP_EOL .
'ded_subject_id,delay_discounting,,text,"Subject ID",,,,,,,,,,,,,' . PHP_EOL .
'ded_server_date,delay_discounting,,text,"Server Date",,,,,,,,,,,,,' . PHP_EOL .
'ded_server_time,delay_discounting,,text,"Sserver Time",,,,,,,,,,,,,' . PHP_EOL .
'ded_site,delay_discounting,,text,"Site",,,,,,,,,,,,,' . PHP_EOL .
'ded_run,delay_discounting,,text,"Run",,,,,,,,,,,,,' . PHP_EOL .
'ded_assessment_date,delay_discounting,,text,"Assessment Date",,,,,,,,,,,,,' . PHP_EOL .
'ded_ip_1_day,delay_discounting,,text,"IP 1 day",,,,,,,,,,,,,' . PHP_EOL .
'ded_ip_1_week,delay_discounting,,text,"IP 1 week",,,,,,,,,,,,,' . PHP_EOL .
'ded_ip_1_month,delay_discounting,,text,"IP 1 month",,,,,,,,,,,,,' . PHP_EOL .
'ded_ip_3_months,delay_discounting,,text,"IP 3 months",,,,,,,,,,,,,' . PHP_EOL .
'ded_ip_1_year,delay_discounting,,text,"IP 1 year",,,,,,,,,,,,,' . PHP_EOL .
'ded_ip_5_years,delay_discounting,,text,"IP 5 years",,,,,,,,,,,,,' . PHP_EOL .
'ded_k,delay_discounting,,text,"K",,,,,,,,,,,,,' . PHP_EOL .
'ded_logk,delay_discounting,,text,"Log K",,,,,,,,,,,,,' . PHP_EOL .
'ded_consistency,delay_discounting,,text,"Consistency",,,,,,,,,,,,,' . PHP_EOL .
'ded_cons_resp_check01,delay_discounting,,text,"Consistent resp check 1",,,,,,,,,,,,,' . PHP_EOL .
'ded_cons_resp_check02,delay_discounting,,text,"Consistent resp check 2",,,,,,,,,,,,,' . PHP_EOL .
'ded_event_name,delay_discounting,,text,"Event name",,,,,,,,,,,,,' . PHP_EOL;


for ($x = 0; $x < 74; $x++) {
    $x = sprintf('%02d', $x);

$out .= 'ded_rt_'.$x.',delay_discounting,,text,"Reaction time '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'ded_stimulus_'.$x.',delay_discounting,,text,"Stimulus '.$x.'",,,,,,,,,,,,,' . PHP_EOL.
'ded_stimulus_type_'.$x.',delay_discounting,,text,"Stimulus Type'.$x.'",,,,,,,,,,,,,' . PHP_EOL.
'ded_button_pressed_'.$x.',delay_discounting,,text,"Button Pressed '.$x.'",,,,,,,,,,,,,' . PHP_EOL.
'ded_key_press_'.$x.',delay_discounting,,text,"Button Pressed '.$x.'",,,,,,,,,,,,,' . PHP_EOL. 
'ded_trial_type_'.$x.',delay_discounting,,text,"Trial Type '.$x.'",,,,,,,,,,,,,' . PHP_EOL. 
'ded_trial_index_'.$x.',delay_discounting,,text,"Trial Index '.$x.'",,,,,,,,,,,,,' . PHP_EOL. 
'ded_time_elapsed_'.$x.',delay_discounting,,text,"Time Elapsed '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'ded_internal_node_id_'.$x.',delay_discounting,,text,"Internal Node ID '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'ded_choice1_'.$x.',delay_discounting,,text,"Choice 1 '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'ded_choice2_'.$x.',delay_discounting,,text,"Choice 2 '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'ded_trialnum_'.$x.',delay_discounting,,text,"Trial Number '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'ded_amount_'.$x.',delay_discounting,,text,"Amount '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'ded_delays_'.$x.',delay_discounting,,text,"Delays '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'ded_response_'.$x.',delay_discounting,,text,"Response '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'ded_t_'.$x.',delay_discounting,,text,"T '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'ded_stim_onset_'.$x.',delay_discounting,,text,"Stim Onset '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'ded_now_'.$x.',delay_discounting,,text,"Now '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'ded_is_data_element_'.$x.',delay_discounting,,text,"Is Data Element '.$x.'",,,,,,,,,,,,,' . PHP_EOL;


}
echo $out. PHP_EOL;

        $file = fopen('instrument.csv', "w");
        fwrite($file, $out);
        fclose($file);

?>
