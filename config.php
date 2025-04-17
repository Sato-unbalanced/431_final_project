<?php
// config.php
// Chapter 16 reference- Setup for backend file and path management
// Similar to prior config files

// Base path of application 
// located in /opt/lampp/htdocs/project for me- using linux machine
$BASE_PATH = $_SERVER['DOCUMENT_ROOT'] . '/project';

// Public HTML/PHP pages = base
$DOC_PATH = $BASE_PATH;

// Private data (ex: logs, uploads, or json) could go here 
// log login times etc 
$DATA_PATH = $BASE_PATH . '/data';
?>
