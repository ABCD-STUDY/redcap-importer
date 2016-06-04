<?php 
// writes instrument to screen for saving as csv
$out = '"Variable / Field Name","Form Name","Section Header","Field Type","Field Label","Choices, Calculations, OR Slider Labels","Field Note","Text Validation Type OR Show Slider Number","Text Validation Min","Text Validation Max",Identifier?,"Branching Logic (Show field only if...)","Required Field?","Custom Alignment","Question Number (surveys only)","Matrix Group Name","Matrix Ranking?","Field Annotation"\n\n' .
'subject_id,little_man_task,,text,"Subject ID",,,,,,,,,,,,,\n\n'
'server_date,little_man_task,,text,"Server Date",,,,,,,,,,,,,\n\n'
'server_time,little_man_task,,text,"Sserver Time",,,,,,,,,,,,,\n\n'
'site,little_man_task,,text,"Site",,,,,,,,,,,,,\n\n'
'run,little_man_task,,text,"Run",,,,,,,,,,,,,\n\n'
'assessment_date,little_man_task,,text,"Assessment Date",,,,,,,,,,,,,\n\n'
'event_name,little_man_task,,text,"Event name",,,,,,,,,,,,,\n\n'


for ($x = 0; $x < 44; $x++) {
    $x = sprintf('%02d', $x);

$out .= 'rt_'.$x.',little_man_task,,text,"Reaction time '.$x.'",,,,,,,,,,,,,\n\n\n\n'
'stimulus_'.$x.',little_man_task,,text,"Stimulus '.$x.'",,,,,,,,,,,,,\n\n'. PHP_EOL .
'stimulus_type_'.$x.',little_man_task,,text,"Stimulus Type'.$x.'",,,,,,,,,,,,,\n\n'. PHP_EOL .
'button_pressed_'.$x.',little_man_task,,text,"Button Pressed '.$x.'",,,,,,,,,,,,,\n\n'. PHP_EOL .
'key_press_'.$x.',little_man_task,,text,"Button Pressed '.$x.'",,,,,,,,,,,,,\n\n'. PHP_EOL .
'trial_type_'.$x.',little_man_task,,text,"Trial Type '.$x.'",,,,,,,,,,,,,\n\n'. PHP_EOL .
'trial_index_'.$x.',little_man_task,,text,"Trial Index '.$x.'",,,,,,,,,,,,,\n\n'. PHP_EOL .
'time_elapsed_'.$x.',little_man_task,,text,"Time Elapsed '.$x.'",,,,,,,,,,,,,\n\n' .
'internal_node_id_'.$x.',little_man_task,,text,"Internal Node ID '.$x.'",,,,,,,,,,,,,\n\n' .
'choice1_'.$x.',little_man_task,,text,"Choice 1 '.$x.'",,,,,,,,,,,,,\n\n' .
'choice2_'.$x.',little_man_task,,text,"Choice 2 '.$x.'",,,,,,,,,,,,,\n\n' .
'trialnum_'.$x.',little_man_task,,text,"Trial Number '.$x.'",,,,,,,,,,,,,\n\n' .
'amount_'.$x.',little_man_task,,text,"Amount '.$x.'",,,,,,,,,,,,,\n\n' .
'delays_'.$x.',little_man_task,,text,"Delays '.$x.'",,,,,,,,,,,,,\n\n' .
'response_'.$x.',little_man_task,,text,"Response '.$x.'",,,,,,,,,,,,,\n\n' .
't_'.$x.',little_man_task,,text,"T '.$x.'",,,,,,,,,,,,,\n\n' .
'stim_onset_'.$x.',little_man_task,,text,"Stim Onset '.$x.'",,,,,,,,,,,,,\n\n' .
'now_'.$x.',little_man_task,,text,"T '.$x.'",,,,,,,,,,,,,\n\n' .
'trialnum_'.$x.',little_man_task,,text,"Correct '.$x.'",,,,,,,,,,,,,\n\n' .
'is_data_element_'.$x.',little_man_task,,text,"Is Data Element '.$x.'",,,,,,,,,,,,,\n\n';


}
echo $out.'\n\n';
/*
        $file = fopen('file.csv', "w");
        fwrite($file, $out);
        fclose($file);
 */
?>
