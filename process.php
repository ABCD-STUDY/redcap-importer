<html>
<link href="code/css/style.css" rel="stylesheet" type="text/css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
</html>
<body>
<?php
$file = $_SERVER["DOCUMENT_ROOT"] .'/applications/timeline-followback/data/sessions.json_master';
$path = $_SERVER["DOCUMENT_ROOT"] .'/applications';
$dir = new DirectoryIterator($path);

//require_once 'code/php/abcd_conn.php';
//require_once 'code/php/class.reader.php';
// $read = new Reader();
//  $read->Parser($file);
   $values = array(
         array(
    'Field'=>addslashes('test'),    
    'Data'=>addslashes('DATA<------->DATA')
    ));

    /*** insert the array of values ***/
  //  $crud->dbInsert('formdata', $values);
?>

    <h2 class="strokeme">ABCD FILE IMPORTER</h2>
    <div id='frame'>
<div id="tabbed_box_1" class="tabbed_box">
    <h4>Import Data <small>Select a Study</small></h4>
    <div class="tabbed_area">
           
        <div id="content_1" class="content"><a href="#"><span class='button' id="t1">Enroll</span></a></div>
        <div id="content_2" class="content"><a href="#"><span class='button' id="t2">Little-Man-Task</a></a></div>
        <div id="content_3" class="content"><a href="#"><span class='button' id="t3">Stroop</a></a></div>
        <div id="content_3" class="content"><a href="#"><span class='button' id="t4">Timeline-followback</a></a></div>
        <div id="content_4" class="content"><a href="#"><span class='button' id="t5">User</a></a></div>
    </div>
 
</div>
 </div>
<script>
    URL = '/var/www/html/applications/';
    $( "#t1" ).click(function() {
       Process('enroll');
       alert( "3 enrollment records added" );
     });
     $( "#t2" ).click(function() {
       Process('little-man-task');
       alert( "5 little-man-task records added" );
     });
     $( "#t3" ).click(function() {
       Process('Stroop');
       alert( "4 Stroop records added" );
     });
     $( "#t4" ).click(function() {
       Process('timeline-followback');
       alert( "5 timeline-followback records added" );
     });
     $( "#t5" ).click(function() {
       Process('User');
       alert( "2 User records added" );
     });
function Process(study) {
	return $.get(URL + study);
}
</script>
</body>
<html>
