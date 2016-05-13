<?php 
// writes instrument to screen for saving as csv
$out = '"Variable / Field Name","Form Name","Section Header","Field Type","Field Label","Choices, Calculations, OR Slider Labels","Field Note","Text Validation Type OR Show Slider Number","Text Validation Min","Text Validation Max",Identifier?,"Branching Logic (Show field only if...)","Required Field?","Custom Alignment","Question Number (surveys only)","Matrix Group Name","Matrix Ranking?","Field Annotation"
record_id,little_man_task,,text,"Record ID",,,,,,,,,,,,,'. PHP_EOL.
'lmt_site,little_man_task,,text,"Site",,,,,,,,,,,,,'. PHP_EOL .
'lmt_assessment_date,little_man_task,,text,"Assessment Date",,,,,,,,,,,,,'. PHP_EOL .
'lmt_event_name,little_man_task,,text,"Event name",,,,,,,,,,,,,'. PHP_EOL .
'lmt_complete,little_man_task,,text,"Task Complete",,,,,,,,,,,,,'. PHP_EOL;

for ($x = 0; $x < 43; $x++) {
    $x = sprintf('%02d', $x);

$out .= 'lmt_rt_'.$x.',little_man_task,,text,"Reaction time '.$x.'",,,,,,,,,,,,,'. PHP_EOL .
'lmt_stimulus_'.$x.',little_man_task,,text,"Stimulus '.$x.'",,,,,,,,,,,,,'. PHP_EOL .
'lmt_key_press_'.$x.',little_man_task,,text,"Key Press '.$x.'",,,,,,,,,,,,,'. PHP_EOL .
'lmt_trial_type_'.$x.',little_man_task,,text,"Trial Type '.$x.'",,,,,,,,,,,,,'. PHP_EOL .
'lmt_trial_index_'.$x.',little_man_task,,text,"Trial Index '.$x.'",,,,,,,,,,,,,'. PHP_EOL .
'lmt_time_elapsed_'.$x.',little_man_task,,text,"Time Elapsed '.$x.'",,,,,,,,,,,,,'. PHP_EOL .
'lmt_internal_node_id_'.$x.',little_man_task,,text,"Internal Node ID '.$x.'",,,,,,,,,,,,,'. PHP_EOL .
'lmt_is_data_element_'.$x.',little_man_task,,text,"Is Data Element '.$x.'",,,,,,,,,,,,,'."\r\n";


}
echo $out.'<br/>';
/*
        $file = fopen('lmt_file.csv', "w");
        fwrite($file, $out);
        fclose($file);
 */
?>

