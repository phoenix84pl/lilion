//load the JavaScript SDK
(function(d, s, id)
{
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) {return;}
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/en_US/sdk.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));


	//ladowanie asynchroniczne
window.fbAsyncInit = function()
{
	//SDK loaded, initialize it
	FB.init({
		appId	: '1788983774648061',
		status : true,
		cookie  : true,
		xfbml	: true,
		version : 'v2.2'
	});

	//autologowanie
	FB.getLoginStatus(function(response) {
		if (response.status === 'connected') {
			FbLogin();
		} else {
			console.log("Logged out. (Chrome does not provide cookies @localhost)");
		}
	});
};