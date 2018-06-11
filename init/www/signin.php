<?php
require_once(__DIR__.'/../lib/root/precms.php');

// imie uzytkownika w {$_SESSION['cms']['u']['imie']}
if(isset($_SESSION['cms']['u']['id'])) echo "<a href=\"javascript:FbLogout();\">Sign out</a>";
else echo "<a href=\"javascript:FbLogin();\">Sign in with Facebook</a>";

?>