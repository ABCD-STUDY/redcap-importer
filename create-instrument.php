<?php 
// writes instrument to screen for saving as csv
$out = '"Variable / Field Name","Form Name","Section Header","Field Type","Field Label","Choices, Calculations, OR Slider Labels","Field Note","Text Validation Type OR Show Slider Number","Text Validation Min","Text Validation Max",Identifier?,"Branching Logic (Show field only if...)","Required Field?","Custom Alignment","Question Number (surveys only)","Matrix Group Name","Matrix Ranking?","Field Annotation"<br>' . PHP_EOL .
'record_id,little_man_task,,text,"Record ID",,,,,,,,,,,,,<br>'. PHP_EOL.
'lmt_site,little_man_task,,text,"Site",,,,,,,,,,,,,<br>'. PHP_EOL .
'lmt_assessment_date,little_man_task,,text,"Assessment Date",,,,,,,,,,,,,<br>'. PHP_EOL .
'lmt_event_name,little_man_task,,text,"Event name",,,,,,,,,,,,,<br>'. PHP_EOL .
'little_man_task_complete,little_man_task,,text,"Task Complete",,,,,,,,,,,,,<br>'. PHP_EOL;

for ($x = 0; $x < 45; $x++) {
    $x = sprintf('%02d', $x);

$out .= 'lmt_rt_'.$x.',little_man_task,,text,"Reaction time '.$x.'",,,,,,,,,,,,,<br>'. PHP_EOL .
'lmt_stimulus_'.$x.',little_man_task,,text,"Stimulus '.$x.'",,,,,,,,,,,,,<br>'. PHP_EOL .
'lmt_stimulus_type_'.$x.',little_man_task,,text,"Stimulus Type'.$x.'",,,,,,,,,,,,,<br>'. PHP_EOL .
'lmt_key_press_'.$x.',little_man_task,,text,"Key Press '.$x.'",,,,,,,,,,,,,<br>'. PHP_EOL .
'lmt_trial_type_'.$x.',little_man_task,,text,"Trial Type '.$x.'",,,,,,,,,,,,,<br>'. PHP_EOL .
'lmt_trial_index_'.$x.',little_man_task,,text,"Trial Index '.$x.'",,,,,,,,,,,,,<br>'. PHP_EOL .
'lmt_time_elapsed_'.$x.',little_man_task,,text,"Time Elapsed '.$x.'",,,,,,,,,,,,,<br>' .
'lmt_internal_node_id_'.$x.',little_man_task,,text,"Internal Node ID '.$x.'",,,,,,,,,,,,,<br>' .
'lmt_skipped_'.$x.',little_man_task,,text,"Skipped '.$x.'",,,,,,,,,,,,,<br>' .
'lmt_correct_'.$x.',little_man_task,,text,"Correct '.$x.'",,,,,,,,,,,,,<br>' .
'lmt_is_data_element_'.$x.',little_man_task,,text,"Is Data Element '.$x.'",,,,,,,,,,,,,<br>';


}
echo $out.'<br/>';
/*
        $file = fopen('lmt_file.csv', "w");
        fwrite($file, $out);
        fclose($file);
 */
?>
