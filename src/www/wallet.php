<?php
require_once(__DIR__.'/../lib/root/precms.php');

if(isset($_SESSION['cms']['u']['id']))
{
	cms_require_modul('db');

	$saldo=(float) $o_db->komorka('saldo', "WHERE `uid`='".htmlspecialchars($_SESSION['cms']['u']['id'])."' ORDER BY `id` DESC", 'przelewy');

	echo "
		<table class=\"max\">
			<tr><td><h2 class=\"link\" onClick=\"windowHide();\">Wallet: $saldo PLN</h2></td></tr>
			<tr><td class=\"middle\">
				<p><input type=\"button\" class=\"link maxitext\" value=\"Deposit\" onClick=\"windowShow('deposit');\"></p>
				<p class=\"minitext\">You may deposit with 0 Bitcoin Confirmations!</p>
			</td></tr>
			<tr><td class=\"middle\">
				<p><input type=\"button\" class=\"link maxitext\" value=\"Withdrawal\" onClick=\"windowShow('withdrawal');\"></p>
				<p class=\"minitext\">Due to security reasons we do not keep any \"hot wallets\". Withdrawals are processed every day.</p>
			</td></tr>
			<tr><td class=\"middle\">
				<p><input type=\"button\" class=\"link maxitext\" value=\"History\" onClick=\"windowShow('history');\"></p>
				<p class=\"minitext\">Check your deposits, investments, interests and withdrawals.</p>
			</td></tr>
			<tr><td class=\"bottom\">
				<p class=\"minitext\">We invest in PLN, so your balance is always in PLN</p>
				<p class=\"minitext\">All incomming transfers will be converted to PLN</p>
				<p class=\"minitext\">All interests will be paid in PLN</p>
				<p class=\"minitext\">All withdrawals are in PLN (converted to other currencies if needed)</p>
			</td></tr>
		</table>
	";
}
?>