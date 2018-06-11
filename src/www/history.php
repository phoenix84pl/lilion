<?php
require_once(__DIR__.'/../lib/root/precms.php');
cms_require_modul('db');
cms_require_modul('email');

var_dump($o_email->wyslij('kamil.dudkowski@gmail.com', 'Dupa', "Biskupa"), $o_email->getBledy());

$transakcje=$o_db->tabela(array('id', 'czas', 'tytul', 'kwota', 'saldo'), "WHERE `uid`='".htmlspecialchars($_SESSION['cms']['u']['id'])."' ORDER BY `id` DESC", '_transakcje');

$html='';
$n0=0;
if($transakcje) foreach($transakcje as $nr=>$dane)
{
		//wygeneruj tabele
	$html.="<tr class=\"window_tr\">";

	$tlo=$nr%2==1?'bg_blue':'';

	$czas=date("Y/m/d H:i:s", $dane['czas']);

	$html.="
		<td class=\"window_td $tlo\"><p>#{$dane['id']}</p></td>
		<td class=\"window_td $tlo\"><p>{$dane['tytul']}</p><p>$czas</p></td>
		<td class=\"window_td $tlo\"><p>{$dane['kwota']}</p><p>{$dane['saldo']}</p></td>
	";

	$n0++;
}

echo "
	<h2 class=\"link\" onClick=\"windowHide();\">History</h2>
	<table class=\"maxw\">
		<tr class=\"bg_blue\"><th><p>#</p></th><th><p>Info</p></th><th><p>Amount</p></th></tr>
		$html
	</table>
";

?>