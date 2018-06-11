<?php
require_once(__DIR__.'/../lib/root/precms.php');
cms_require_modul('db');

$faktura=$o_db->wiersz(array('uid', 'id_kp', 'batch', 'status'), "WHERE `id`='".htmlspecialchars($_REQUEST['invoice_id'])."'", 'faktury');
$status=$o_db->komorka('nazwa', "WHERE `status`={$faktura['status']}", 'faktury_statusy');

	//sprawdzanie statusu i ewentualne zmiany w bazie
require_once(__DIR__."/../lib/php_class/InPay/inpay.class.php");

$ip=new \InPay\inpay();
$ip->init(array("apiKey" => "AT16HNQRFB7WVUGX2KC3"));
$wynik=$ip->invoiceStatus($faktura['batch']);

if(($faktura['status']!=7) && ($wynik['status']=='confirmed'))
{
	//kredytuj konto klienta i debetuj konto procesora

	require_once(__DIR__."/../lib/php_foo/agado.php");

	if(transakcja('-'.$faktura['id_kp'], $faktura['uid'], $wynik['in_amount']/100, $faktura['batch'])) $o_db->aktualizuj(array('status'=>7), "WHERE `id`='".htmlspecialchars($_REQUEST['invoice_id'])."'", 'faktury');

		//aktualizacja zmiennych do nowego statusu
	$faktura=$o_db->wiersz(array('uid', 'id_kp', 'batch', 'status'), "WHERE `id`='".htmlspecialchars($_REQUEST['invoice_id'])."'", 'faktury');
	$status=$o_db->komorka('nazwa', "WHERE `status`={$faktura['status']}", 'faktury_statusy');
}

?>