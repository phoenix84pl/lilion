<?php
require_once(__DIR__.'/../lib/root/precms.php');

cms_require_modul('db');

$saldo=isset($_SESSION['cms']['u']['id'])?(float) $o_db->komorka('saldo', "WHERE `uid`='".htmlspecialchars($_SESSION['cms']['u']['id'])."' ORDER BY `id` DESC", 'przelewy'):0;	//jesli jest user to saldo pobierz z bazy, a jak nie ma to to zero
$kalkulacja=$saldo<100?10000:$saldo;	//liczenie dla salda, a jak 0 to dla 10 000 PLN

$programy=$o_db->tabela(array('id', 'nazwa', 'dlugosc', 'stawka'), "", 'programy');

$html='';
$n0=0;
if($programy) foreach($programy as $nr=>$dane)
{
		//wygeneruj tabele
	$html.="<tr class=\"window_tr\">";

	$tlo=$nr%2==1?'bg_blue':'';

	$dziennie=$dane['stawka']*100;
	$miesiecznie=$dziennie*31;
	$rocznie=$dziennie*365;
	$program=$dziennie*$dane['dlugosc'];

	$s_dziennie=$kalkulacja*$dane['stawka'];
	$s_miesiecznie=$s_dziennie*31;
	$s_rocznie=$s_dziennie*365;
	$s_program=$s_dziennie*$dane['dlugosc'];

	$html.="
		<td class=\"window_td $tlo\"><p>{$dane['nazwa']}</p></td>
		<td class=\"window_td $tlo\"><p>{$dane['dlugosc']} days</p></td>
		<td class=\"window_td $tlo\">
			<p class=\"minitext\">$dziennie% per day</p>
			<p class=\"minitext\">$miesiecznie% per month</p>
			<p class=\"minitext\">$rocznie% per year</p>
			<p class=\"minitext\">$program% per program</p>
		</td>
		<td class=\"window_td $tlo\">
			<p class=\"minitext\">$s_dziennie PLN per day</p>
			<p class=\"minitext\">$s_miesiecznie PLN per month</p>
			<p class=\"minitext\">$s_rocznie PLN per year</p>
			<p class=\"minitext\">$s_program PLN per program</p>
		</td>
	";

	if($saldo>0) $html.="</tr>
	<tr class=\"window_tr\">
		<td colspan=\"4\" class=\"window_td $tlo\">
			<p><input type=\"number\" name=\"program_{$dane['id']}\" placeholder=\"Amount\" value=\"$saldo\" min=\"0.01\" step=\"0.01\" class=\"amount maxitext right\"> PLN</p>
			<p><input type=\"button\" class=\"link maxitext\" value=\"Invest in {$dane['nazwa']} Program\" onClick=\"if(confirm('Do you confirm investing '+$('input[name=program_{$dane['id']}]').val()+' PLN in {$dane['nazwa']} program for {$dane['dlugosc']} days? You will get $dziennie% ('+Math.floor($('input[name=program_{$dane['id']}]').val()*{$dane['stawka']}*100)/100+' PLN) interest every day. You have been informed that you CANNOT stop the program before it is finished. Interest will be added everyday.')) investmentStart({$dane['id']}, $('input[name=program_{$dane['id']}]').val());\"></p>
		</td>
	</tr>";

	$n0++;
}

echo "
	<h2 class=\"link\" onClick=\"windowHide();\">Your programs</h2>
	<h3>Fixed programs</h3>
	<p>Fixed programs are perfect for <100 000 PLN investors. They are hasslefree and fully automated.</p>
	<p>All you need to do is to enjoy your profits!</p>
	<table class=\"maxw\">
		<tr class=\"bg_blue\"><th>Program</th><th>Duration</th><th>Interests</th><th><p>Earnings</p><p class=\"minitext\">(per $kalkulacja PLN invested)</p></th></tr>
		$html
	</table>
	<p class=\"minitext\">Money from fixed programs are invested into flip transactions only.</p>
	<p class=\"minitext\">All interests are paid daily and are immediately ready for withdrawal.</p>
	<p class=\"minitext\">Earnings are for your balance. If balance is less than 100PLN it is counted for 10 000 PLN.</p>
	<p class=\"minitext\">We may finish any investment at any time at our sole discrection. In that case invested money will credit your balance.</p>
	<p class=\"minitext\">Due to security reasons you cannot stop program before it is finished.
	<br />In case of hijacking your account, hacker could withdraw all your money at once.
	<br />Now hacker has to wait until program is finished so you have time for protection.</p>
	<hr />
	<h3>Individual programs</h3>
	<p>Individual programs are perfect for >200 000 PLN investors (minimum is 100 000 PLN investment).</p>
	<p>We find, buy, clear and sell properties exactly for you.</p>
	<p>You experience no risk at all, cause you are the owner of the property at all times.</p>
	<p>You pay us fixed commission regardless of your profit.</p>
	<table class=\"maxw\">
		<tr class=\"bg_blue\"><th class=\"window_td\">Actions</th><th class=\"window_td\">Fee</th></tr>
		<tr class=\"window_tr\"><td class=\"window_td\">Signing a contract</td><td class=\"window_td\">1 000 PLN</td></tr>
		<tr class=\"window_tr bg_blue\"><td class=\"window_td\">Property bought (under market prices)</td><td class=\"window_td\">2 000 PLN</td></tr>
		<tr class=\"window_tr\"><td class=\"window_td\">Property cleared (no inhabitants, no legal problems)</td><td class=\"window_td\">3 000 PLN</td></tr>
		<tr class=\"window_tr bg_blue\"><td class=\"window_td\">Property sold (at least at market price)</td><td class=\"window_td\">4 000 PLN</td></tr>
		<tr class=\"window_tr\"><td class=\"window_td\"></td><td></td></tr>
		<tr class=\"window_tr bg_blue\"><td class=\"window_td\">Total</td><td class=\"window_td\">10 000 PLN</td></tr>
	</table>
	<p class=\"minitext\">In this program your profit is around 30-50% per transaction.</p>
	<p class=\"minitext\">It usually takes 6 to 12 months between investment and profit.</p>
	<p class=\"minitext\">No risk investment. You are the owner of the property between buy and sell actions.</p>
	<p class=\"minitext\">You give us directives what kind of property you are looking for.</p>
	<p class=\"minitext\">We give you propositions until you accept one.</p>
	<p class=\"minitext\">We charge 100 PLN for each proposition you have rejected (first 10 rejects is included in a contract fee).</p>
	<p class=\"minitext\">If we find a buyer and you do not sell your property, you still have to pay 4 000 PLN selling fee.</p>
	<p class=\"minitext\">You may be a subject to 2% PCC tax (2% of property value) and 19% PIT (profit/income tax), but we will help you to avoid it.</p>
	<p>Still interested?</p>
	<p class=\"maxitext\">Contact us now!</p>
";

?>