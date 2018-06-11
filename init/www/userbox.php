<?php
require_once(__DIR__.'/../lib/root/precms.php');

	echo "<a class=\"link\" onClick=\"windowShow('obwieszczenia');\">Obwieszczenia</a>";

if(isset($_SESSION['cms']['u']['id']))
{
	echo "<a class=\"link\" onClick=\"windowShow('obwieszczenia');\">Obwieszczenia</a>";
}
else echo "Use anonymous Facebook account to sign in and use free demo → → → ";

?>