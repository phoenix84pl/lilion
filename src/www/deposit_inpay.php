<?php
require_once(__DIR__.'/../lib/root/precms.php');

if(isset($_SESSION['cms']['u']['id']))
{
	cms_require_modul('db');
	require_once(__DIR__."/../lib/php_class/InPay/inpay.class.php");

	$o_db->rekord_dodaj(array('uid'=>htmlspecialchars($_SESSION['cms']['u']['id']), 'id_kp'=>1), 'faktury');
	$faktura_id=$o_db->rekord_id;

	if($faktura_id)
	{
		$url="http://agado.pl/?cms_window=invoice&invoice_id=$faktura_id";
		$callback="http://agado.pl/process/payment_callback.php?invoice_id=$faktura_id";

		$ip=new \InPay\inpay();
		$ip->init(array("apiKey" => "AT16HNQRFB7WVUGX2KC3"));
		$ip->setUrls($callback, $url, $url);
		$ip->setCustomerEmail($_SESSION['cms']['u']['email']);
		$wynik=$ip->invoiceCreate($_REQUEST['amount']);

		if(!isset($wynik['invoiceCode'])) echo "<script>windowHide(); windowShow('deposit');</script>";
		else
		{
			$o_db->aktualizuj(array('batch'=>$wynik['invoiceCode']), "WHERE `id`=$faktura_id", 'faktury');

			echo "
			<table class=\"max\">
				<tr><td class=\"middle\">
					<script>setTimeout(function(){ window.location.replace('{$wynik['redirectUrl']}'); }, 2000);</script>
					<p><img src=\"img/anime/redirect.gif\" /></p>
					<p>Redirecting to InPay Bitcoin Payment...</p>
					<p><a href=\"{$wynik['redirectUrl']}\">{$wynik['redirectUrl']}</a></p>
				</td></tr>
			</table>

			";
		}
	}
}

?>