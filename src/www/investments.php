<?php
require_once(__DIR__.'/../lib/root/precms.php');

if(isset($_SESSION['cms']['u']['id']))
{
	cms_require_modul('db');

	$saldo=(float) $o_db->komorka('saldo', "WHERE `uid`='".htmlspecialchars($_SESSION['cms']['u']['id'])."' ORDER BY `id` DESC", 'przelewy');

	$inwestycje=$o_db->tabela(array('id', 'id_programy', 'nazwa', 'dlugosc', 'stawka', 'czas', 'czas_odsetki', 'kwota', 'odsetki_suma', 'status'), "WHERE `uid`='".htmlspecialchars($_SESSION['cms']['u']['id'])."' ORDER BY `status`", '_inwestycje');

	$html='';
	$n0=0;
	if($inwestycje) foreach($inwestycje as $nr=>$dane)
	{
			//wygeneruj tabele
		$html.="<tr class=\"window_tr\">";

		$tlo=$nr%2==1?'bg_blue':'';

		$dziennie=$dane['stawka']*100;
		$miesiecznie=$dziennie*31;
		$rocznie=$dziennie*365;
		$program=$dziennie*$dane['dlugosc'];

		$s_dziennie=floor($dane['kwota']*$dane['stawka']*100)/100;
		$s_miesiecznie=$s_dziennie*31;
		$s_rocznie=$s_dziennie*365;
		$s_program=$s_dziennie*$dane['dlugosc'];

		$czas_start=date("Y/m/d H:i:s", $dane['czas']);
		$czas_koniec=date("Y/m/d H:i:s", $dane['czas']+$dane['dlugosc']*86400);

		switch($dane['status'])
		{
			case 1: $zostalo=ceil(($dane['czas']+$dane['dlugosc']*86400-time())/86400)." days left"; break;
			case 7: $zostalo="Finished"; break;
			case 9: $zostalo="Cancelled"; break;
		}

		$html.="
			<td class=\"window_td $tlo\">
				<p>{$dane['nazwa']}</p>
				<p>{$dane['dlugosc']} days</p>
			</td>
			<td class=\"window_td $tlo\">
				<p class=\"minitext\">$czas_start</p>
				<p class=\"minitext\">$czas_koniec</p>
				<p class=\"minitext\">$zostalo</p>
			</td>
			<td class=\"window_td $tlo\">
				<p class=\"minitext\">$s_dziennie PLN ($dziennie%) per day</p>
				<p class=\"minitext\">$s_miesiecznie PLN ($miesiecznie%) per month</p>
				<p class=\"minitext\">$s_rocznie PLN ($rocznie%) per year</p>
				<p class=\"minitext\">$s_program PLN ($program%) per program</p>
			</td>
			<td class=\"window_td $tlo\"><p>{$dane['odsetki_suma']} PLN</p></td>
		";

		$n0++;
	}

	echo "
		<h2 class=\"link\" onClick=\"windowHide();\">Investments</h2>
		<h3>Fixed programs</h3>
		<p><input class=\"link maxitext\" type=\"button\" value=\"Start a New Investment\" onClick=\"windowShow('programs');\"></p>
		<table class=\"maxw\">
			<tr class=\"bg_blue\"><th>Program</th><th><p>Start</p><p>End</p></th><th><p>Earnings</p><p>Interests</p></th><th>Profit</th></tr>
			$html
		</table>
		<p><input class=\"link maxitext\" type=\"button\" value=\"Start a New Investment\" onClick=\"windowShow('programs');\"></p>
		<p class=\"minitext\">Interests is paid for each 24 hours period at full hours.</p>
	";
}

?>