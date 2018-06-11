<?php
require_once(__DIR__.'/../lib/root/precms.php');

if(isset($_SESSION['cms']['u']['id']))
{
	echo "
		<form class=\"max\">
		<table class=\"max\">
			<tr><td><h2 class=\"link\" onClick=\"windowHide();\">Deposit</h2></td></tr>
			<tr><td><p class=\"maxitext\"><input type=\"number\" name=\"amount\" placeholder=\"Amount\" min=\"0.01\" step=\"0.01\" class=\"amount maxitext right\"> PLN</p></td></tr>
			<tr><td><img src=\"img/button/bitcoin.png\" class=\"link\" onClick=\"windowShow('deposit_inpay', {'amount': $('input[name=amount]').val()});\"/></td></tr>
			<tr><td><hr /></td></tr>
			<tr><td class=\"bottom minitext\">
				<p>Deposits made with Bitcoin are converted to PLN at no fee.</p>
				<p>Deposits made with Bitcoin are processed by InPay.pl</p>
				<p>InPay.pl converts BTC to PLN and transfers PLN immediately to us.</p>
				<p>Deposits by wire are converted to PLN with <a href=\"http://www.bankbps.pl/kurs-walut?type=bps\" target=\"_blank\">BPS rate</a>.</p>
				<p>Deposits by wire may be processed up to 5 business days and there is a deposit fee of 10 PLN.</p>
				<p>Deposits by wire from Poland are subject to 2% <a href=\"https://www.wikiwand.com/pl/Podatek_od_czynno%C5%9Bci_cywilnoprawnych\" target=\"_blank\">PCC</a> tax, so 2% will be deducted from your deposit.</p>
				<p>Polish Tax Council considers investments from Poland as a loan and loans are subject to PCC tax.</p>
			</td></tr>
		</table>
		</form>

	";
}
?>