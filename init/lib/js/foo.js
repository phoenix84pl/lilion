function loaderShow()
{
	//functions shows loader
	$("#loader").show();
//	console.log('show loader');
}

function loaderHide()
{
	//function hides loader
	$("#loader").hide();
//	console.log('hide loader');
}

function windowShow(page, args)
{
		//function loads special window div and shows it

	$("#window").html('<p><img src="img/anime/dots.gif" style="max-width: 50%; max-height: 50%;"/></p>');

	switch(page)
	{
		case 'robots':
		{
			$.ajax({url: "www/robots.php", success: function(e){$("#window").html(e);}});
			break;
		}

		case 'robot_edit':
		{
			$.ajax({url: "www/robot_edit.php?id="+args.id, success: function(e){$("#window").html(e);}});
			break;
		}

		case 'robot_graph_composition':
		{
			$.ajax({url: "www/robot_graph_composition.php?id="+args.id, success: function(e){$("#window").html(e);}});
			break;
		}

		case 'robot_graph_history':
		{
			if(args.timeFrom==undefined) args.timeFrom='';
			if(args.timeTo==undefined) args.timeTo='';
			if(args.mode==undefined) args.mode='';
			if(args.currency==undefined) args.currency='';
			if(args.destinationDivId==undefined) args.destinationDivId='';

			var url="www/robot_graph_history.php?id="+args.id+'&time_from='+args.timeFrom+'&time_to='+args.timeTo+'&mode='+args.mode+'&currency='+args.currency+'&destination_div_id='+args.destinationDivId;
//			console.log(url);
			$.ajax({url: url, success: function(e){$("#window").html(e);}});
			break;
		}

		case 'robot_trades':
		{
			$.ajax({url: "www/robot_trades.php?id="+args.id, success: function(e){$("#window").html(e);}});
			break;
		}

		case 'brokers':
		{
			$.ajax({url: "www/brokers.php", success: function(e){$("#window").html(e);}});
			break;
		}

		case 'bi_graph_history':
		{
			if(args.timeFrom==undefined) args.timeFrom='';
			if(args.timeTo==undefined) args.timeTo='';
			if(args.mode==undefined) args.mode='';
			if(args.currency==undefined) args.currency='';
			if(args.destinationDivId==undefined) args.destinationDivId='';

			var url="www/bi_graph_history.php?id="+args.id+'&time_from='+args.timeFrom+'&time_to='+args.timeTo+'&mode='+args.mode+'&currency='+args.currency+'&destination_div_id='+args.destinationDivId;

			$.ajax({url: url, success: function(e){$("#window").html(e);}});
			break;
		}
	}

	$("#shadow").fadeIn(500);
	$("#window").fadeIn(500);
}

function windowHide()
{
		//function hides window
	$("#window").fadeOut(500);
	$("#shadow").fadeOut(500);
}

function investmentStart(id, amount)
{
		//starts an investment program

	$.ajax({url: "process/investment_start.php?id="+id+"&amount="+amount, success: function(e){windowShow('investments');}});
}