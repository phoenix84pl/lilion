<div id="starter_text">
	<h2>Welcome to Agado.</h2>
	<p>Earn money with managed property flipping.</p>
	<p>Buy parts of property for better diversification.</p>
	<p>Fixed plans for individual investors.</p>
	<p>Earn over 25% per annum.</p>
	<p>Get your profits daily.</p>
	<p>Reffer us to other investors and get 5% of their profit.</p>
</div>
<div id="starter_buttons">
	<a class="link" onClick="$('#starter').fadeOut(500);">Invest now</a>
	<a class="link" onClick="$.ajax({url: 'www/hiw.php', success: function(e){$('#hiw').html(e); $('#hiw').fadeIn(500); $('#starter').fadeOut(500);}});">How it works?</a>
	<a class="link" onClick="$.ajax({url: 'www/conditions.php', success: function(e){$('#conditions').html(e); $('#conditions').fadeIn(500); $('#starter').fadeOut(500);}});">Conditions</a>
</div>