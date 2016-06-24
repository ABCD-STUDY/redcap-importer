<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);
// writes instrument to screen for saving as csv
$out = '"Variable / Field Name","Form Name","Section Header","Field Type","Field Label","Choices, Calculations, OR Slider Labels","Field Note","Text Validation Type OR Show Slider Number","Text Validation Min","Text Validation Max",Identifier?,"Branching Logic (Show field only if...)","Required Field?","Custom Alignment","Question Number (surveys only)","Matrix Group Name","Matrix Ranking?","Field Annotation"'. PHP_EOL .
'str_id,delay_discounting,,text,"Record ID",,,,,,,,,,,,,' . PHP_EOL .
'str_subject_id,delay_discounting,,text,"Subject ID",,,,,,,,,,,,,' . PHP_EOL .
'str_server_date,delay_discounting,,text,"Server Date",,,,,,,,,,,,,' . PHP_EOL .
'str_server_time,delay_discounting,,text,"Sserver Time",,,,,,,,,,,,,' . PHP_EOL .
'str_site,delay_discounting,,text,"Site",,,,,,,,,,,,,' . PHP_EOL .
'str_run,delay_discounting,,text,"Run",,,,,,,,,,,,,' . PHP_EOL .
'str_user,delay_discounting,,text,"User",,,,,,,,,,,,,' . PHP_EOL .
'str_assessment_date,delay_discounting,,text,"Assessment Date",,,,,,,,,,,,,' . PHP_EOL .
'str_event_name,delay_discounting,,text,"Event name",,,,,,,,,,,,,' . PHP_EOL;

for ($x = 0; $x < 141; $x++) {
    $x = sprintf('%02d', $x);

$out .= 'str_rt_'.$x.',delay_discounting,,text,"Reaction time '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'str_stimulus_'.$x.',delay_discounting,,text,"Stimulus '.$x.'",,,,,,,,,,,,,' . PHP_EOL.
'str_stimulus_type_'.$x.',delay_discounting,,text,"Stimulus Type'.$x.'",,,,,,,,,,,,,' . PHP_EOL.
'str_button_pressed_'.$x.',delay_discounting,,text,"Button Pressed '.$x.'",,,,,,,,,,,,,' . PHP_EOL.
'str_key_press_'.$x.',delay_discounting,,text,"Button Pressed '.$x.'",,,,,,,,,,,,,' . PHP_EOL. 
'str_trial_type_'.$x.',delay_discounting,,text,"Trial Type '.$x.'",,,,,,,,,,,,,' . PHP_EOL. 
'str_trial_index_'.$x.',delay_discounting,,text,"Trial Index '.$x.'",,,,,,,,,,,,,' . PHP_EOL. 
'str_time_elapsed_'.$x.',delay_discounting,,text,"Time Elapsed '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'str_internal_node_id_'.$x.',delay_discounting,,text,"Internal Node ID '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'str_correct_color_'.$x.',delay_discounting,,text,"Correct Color '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'str_is_real_element_'.$x.',delay_discounting,,text,"Correct Color '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'str_correct_'.$x.',delay_discounting,,text,"Correct Color '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'str_list_'.$x.',delay_discounting,,text,"List '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'str_trialnum_'.$x.',delay_discounting,,text,"Trial Number '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'str_amount_'.$x.',delay_discounting,,text,"Amount '.$x.'",,,,,,,,,,,,,' . PHP_EOL .
'str_is_data_element_'.$x.',delay_discounting,,text,"Is Data Element '.$x.'",,,,,,,,,,,,,' . PHP_EOL;

}
echo $out. PHP_EOL;

        $file = fopen('instrument.csv', "w");
        fwrite($file, $out);
        fclose($file);

?>
