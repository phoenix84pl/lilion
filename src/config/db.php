<?php
if(isset($_SERVER['SHELL']))
{
		//cron i konsola
	define('DB_CMS_HOST', 'localhost');
	define('DB_CMS_BAZA', 'DB_NAME');
	define('DB_CMS_LOGIN', 'DB_LOGIN');
	define('DB_CMS_HASLO', 'DB_PASSWORD');
}
elseif($_SERVER['HTTP_HOST']=='domain.pl')
{
	define('DB_CMS_HOST', 'localhost');
	define('DB_CMS_BAZA', 'DB_NAME');
	define('DB_CMS_LOGIN', 'DB_LOGIN');
	define('DB_CMS_HASLO', 'DB_PASSWORD');
}
elseif($_SERVER['HTTP_HOST']=='domain.com')
{
	define('DB_CMS_HOST', 'localhost');
	define('DB_CMS_BAZA', 'DB_NAME');
	define('DB_CMS_LOGIN', 'DB_LOGIN');
	define('DB_CMS_HASLO', 'DB_PASSWORD');
}


?>
