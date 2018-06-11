<?php
require_once(__DIR__.'/../lib/root/precms.php');
cms_require_modul('db');

$faktura=$o_db->wiersz(array('uid', 'id_kp', 'batch', 'status'), "WHERE `id`='".htmlspecialchars($_REQUEST['invoice_id'])."'", 'faktury');
$kanal=$o_db->komorka('nazwa', "WHERE `id`={$faktura['id_kp']}", 'kanaly_platnosci');
$status=$o_db->komorka('nazwa', "WHERE `status`={$faktura['status']}", 'faktury_statusy');

if($faktura['status']!=7) require_once(__DIR__."/../process/payment_callback.php");		//wywolanie skryptu aktualizujacego fakture

	//wyswietlanie
if($_SESSION['cms']['u']['id']!=$faktura['uid']) echo "<h2 class=\"link\" onClick=\"windowHide();\">Error</h2><div class=\"center maxitext bg_red\">This is not your invoice</div>";
else
{
	echo "
		<form class=\"max\">
		<table class=\"max\">
			<tr><td colspan=\"2\"><h2 class=\"link\" onClick=\"windowHide();\">Invoice #{$_REQUEST['invoice_id']}</h2></td></tr>
			<tr class=\"bg_blue\"><td>Payment Type:</td><td>$kanal</td></tr>
			<tr><td>$kanal Batch:</td><td><a href=\"https://invoice.inpay.pl/{$faktura['batch']}\" target=\"_blank\">{$faktura['batch']}</a></td></tr>
			<tr class=\"bg_blue\"><td>Status:</td><td>$status</td></tr>
			<tr><td class=\"bottom minitext\" colspan=\"2\">
				<p>Invoice with \"Waiting for payment\" status may still be paid.</p>
				<p>Invoice with \"Completed\" status has been credited to your account.</p>
				<p>Invoice with \"Cancelled\" status cannot be paid anymore. You have to make deposit.</p>
				<p>Batch means \"Payment processor reference\".</p>
				<p>Some payment processors issue batch before payment, some after payment and some do not issue batch at all.</p>
				<p>Before payment batch is a \"unique invoice number\".</p>
				<p>After payment batch is a \"unique transfer number\".</p>
			</td></tr>
		</table>
		</form>
	";
}
?>