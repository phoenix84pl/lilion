function init()
{
	//load all ajax init elements
	if(getParameterByName('cms_window')==null) $.ajax({url: "www/starter.php", success: function(e) {$("#starter").html(e);}});
	else
	{
		//if window has been forced
		$('#starter').hide();
		windowHide();
		windowShow(getParameterByName('cms_window'), queryStringToJSON());
	}
}

function relogin()
{
	$.ajax({url: "www/signin.php", success: function(e) {$("#signin").html(e);}});

	$.ajax({url: "www/userbox.php", success: function(e) {$("#userbox").html(e);}});

	$.ajax({url: "www/playground.php", success: function(e) {$("#playground").html(e);}});

	loaderHide();
}