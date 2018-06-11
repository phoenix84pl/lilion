<?php
require_once(__DIR__.'/../lib/root/precms.php');
cms_require_modul('db');

	//wszystkie nieruchomosci, by bylo jedno zapytanie do bazy
$d_nieruchomosci=$o_db->tabela_bezposrednie("SELECT COUNT(`id`) AS `ilosc`, `id_miasta` FROM `nieruchomosci` GROUP BY `id_miasta` ORDER BY `id_miasta`");

if($d_nieruchomosci) foreach($d_nieruchomosci as $nr=>$dane) $l_nieruchomosci[$dane['id_miasta']]=$dane['ilosc'];	//ilosci miast przyporzadkowane do id miast

function komorka_generuj($x, $y, $id)
{
	//funkcja generuje komorke miasta $id o wymiarach xy (colspan rowspan)

	global $o_db, $l_nieruchomosci;

		//w zaleznosci od tego czy miasto czy niemiasto
	$nazwa=is_integer($id)?$o_db->komorka('nazwa', "WHERE `id`='".htmlspecialchars($id)."'", 'miasta'):$id;
	$id=is_integer($id)?$id:strtolower($id);
	$tlo=is_integer($id)?"img/bg/cities/$id.jpg":"img/bg/$id.jpg";

	$h=(($x>1) && ($y>1))?1:2;

	$tytul="<h$h>$nazwa</h$h>";

	$klasa=$y==1?'minitext':'';

	switch($id)
	{
		case is_integer($id):
		{
			$tresc="<h$h>$nazwa</h$h>
			<p class=$klasa>Properties bought: {$l_nieruchomosci[$id]}
<!--			<br />Properties sold: X
			<br />Properties rented: X
			<br />Properties under preparation: X--></p>";
			break;
		}
		case 'invest now':
		{
			$tresc="<h$h>↑ ↑ ↑ ↑</h$h>
			<p class=$klasa>Step 1: Sign in with Facebook
			<br />Step 2: Deposit with BitCoin
			<br />Step 3: Choose your investment program
			<br />Step 4: Enjoy interests</p>";
			break;
		}
		case 'statistics':
		{
			$depozyty=-$o_db->kolumna_operacja('SUM', 'kwota', "WHERE `uid`<0 AND `kwota`<0", 'przelewy'); //uid ujemny zeby wybralo procesory, kwoty ujemne, bo depozyty sa ujemne z punktu widzenia naszego w procesorze (bo procesor wisi nam kase wtedy)
			$wyplaty=$o_db->kolumna_operacja('SUM', 'kwota', "WHERE `uid`<0 AND `kwota`></0>0", 'przelewy'); //uid ujemny zeby wybralo procesory, kwoty dodatnie, bo wyplaty sa dodatnie z punktu widzenia naszego w procesorze (bo do procesora idzie nasza kasa na wyplate)
			$programy=$o_db->kolumna_operacja('SUM', 'kwota', "", 'inwestycje');
			$odsetki=$o_db->kolumna_operacja('SUM', 'odsetki_suma', "", 'inwestycje');
			$inwestorzy=$o_db->kolumna_operacja('COUNT', 'id', "", 'cms_uzytkownicy');
			$nieruchomosci=$o_db->kolumna_operacja('COUNT', 'id', "", 'nieruchomosci');

			$tresc="<h$h>Statistics</h$h>
<!--			<p class=$klasa>Deposited: $depozyty PLN-->
			<br />In programs: $programy PLN
<!--			<br />Invested
			<br />Interests: $odsetki PLN
			<br />Withdrawn: $wyplaty PLN-->
			<br />Investors: $inwestorzy
			<br />Properties bought: $nieruchomosci</p>
			";
			break;
		}
		case 'history':
		{
			$tresc="<h$h>History</h$h>
<!--			<p class=$klasa>Read our story and get knowledge when, where and why!</p>-->
				<p class=$klasa>We have bought our first property in 2009.
				<br />We have registered company in 2012.
				<br />First flip transaction in 2014/2015 with 37% profit.
				<br />Online programs for minor investors started in May 2016.</p>
			";
			break;
		}
		case 'contacts':
		{
			$tresc="<h$h>Contact us</h$h>
			<p class=maxitext>✆ +48 33 44 54321</p>
			";
			break;
		}
		default: $tresc="Content";
	}

	$tresc=str_replace("\n", '', $tresc);

	return "
		<td colspan=\"$x\" rowspan=\"$y\" class=\"\" style=\"background-image: url('$tlo'); background-position: center; background-size: cover;\" onMouseOver=\"playgroundInfo(this, '$tresc');\">
			$tytul
		</td>
	";
}

?>
<script>
	var objPrev;
	var objPrevHTML;

	function playgroundInfo(obj, html)
	{
		var prevHTML=$(obj).html();

		if(obj!=objPrev)	//antyflashing protection
		{
			$(obj).css({'opacity': 0}).html(html).animate({'opacity': 1}, {'duration': 2000, 'queue': false});

			playgroundReverse(objPrev, objPrevHTML);

			objPrev=obj;
			objPrevHTML=prevHTML;
		}
	}

	function playgroundReverse(obj, html)
	{
		//previous back
		if(obj!=undefined) $(obj).css({'opacity': 0}).html(html).animate({'opacity': 1}, {'duration': 1000, 'queue': false});
	}
</script>
<?php

echo "
	<table class=\"max bg_black\">
		<tr>
			".komorka_generuj(1, 2, 2)."
			".komorka_generuj(1, 1, 4)."
			".komorka_generuj(1, 1, 5)."
			".komorka_generuj(1, 1, 'Invest now')."
		</tr>
		<tr>
			".komorka_generuj(2, 2, 1)."
			".komorka_generuj(1, 2, 'Statistics')."
		</tr>
		<tr>
			".komorka_generuj(1, 2, 3)."
		</tr>
		<tr>
			".komorka_generuj(2, 1, 'History')."
			".komorka_generuj(1, 1, 'Contacts')."
		</tr>
	</table>";
?>