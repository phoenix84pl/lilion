<?php
require_once(__DIR__.'/../lib/root/precms.php');
$_SESSION=NULL;
session_destroy();

if(!$_SESSION['cms']['u']['id']) echo 0;
?>