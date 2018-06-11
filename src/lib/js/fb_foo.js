function FbLogin()
{
	loaderShow();
	FB.login(function(response)
	{
		if (response.authResponse)
		{
			login();
		}
	}, {scope: 'email,public_profile', return_scopes: true});
}

function FbLogout()
{
	loaderShow();
	FB.getLoginStatus(function(response)
	{
		if(response.status === 'connected')
		{
			FB.logout(function(response)
			{
				logout();	//website logout after FB logout
			});
		}
		else
		{
			logout();	//even if loggin out from FB failed - logout from the website; (Works if you are already logged out from facebook)
		}
	})
}

function login()
{
	//inner function - DO NOT USE DIRECTLY - automatically called after FB login

	FB.api('/me', function(result)
	{
		$.ajax({url: "process/signin.php",
			success: function(result)
			{
//				console.log(result);
				relogin();	//whole site reload due to login
			}});
	});
}

function logout()
{
	//inner function - DO NOT USE DIRECTLY - automatically called after FB logout

	$.ajax({url: "process/signout.php",
		success: function(result)
		{
//				console.log(result);
				relogin();	//whole site reload due to login
		}});

}
