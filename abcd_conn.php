<?php
 require_once 'ABCD_etc.php';
 require_once 'class.crud.php';
    /*** a new crud object ***/
    $crud = new crud();

    /*** The DSN ***/
    $crud->dsn = "mysql:dbname=abcd;host=localhost";

    /*** MySQL username and password ***/
    $crud->username = ABCD_USER;
    $crud->password = ABCD_PASS;
    $db = new PDO('mysql:host=localhost;dbname='ABCD_DB, ABCD_USER, ABCD_PASS);
?>

