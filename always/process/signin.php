<?php
require_once(__DIR__.'/../lib/root/precms.php');

require_once(__DIR__.'/../config/fb.php');
require_once(__DIR__.'/../lib/php_class/Facebook/autoload.php');

cms_require_modul('db');




function fb_token_generuj($appid, $secret)
{
	//funkcja generuje token fb na podstawie ciastka stworzonego przez FB JS SDK

	$GLOBALS['o_fb']=new Facebook\Facebook(['app_id' => $appid, 'app_secret' => $secret]);
	$wspomagacz=$GLOBALS['o_fb']->getJavaScriptHelper();
	$token=$wspomagacz->getAccessToken($GLOBALS['o_fb']->getClient());

	return $token=(string) $token;	//zamiana na stringa, bo debilny format
}

function fb_udane_pobierz($token)
{
	//funkcja pobiera zadane usera na podstawie tokena (nie moze zadac wiecej info niz user wydal zezwolenie logujac sie przez JS)

	$GLOBALS['o_fb']->setDefaultAccessToken($token);
    $fb_api_wynik=$GLOBALS['o_fb']->get('/me?fields=id,first_name,name,email');

	return $fb_api_wynik->getGraphUser();
}

function uid_generuj($l_uzytkownik)
{
	//funkcja generuje i/lub aktualizuje dane uzytkownika w bazie lokalnej

		//najpierw sprawdzamy czy uzytkownik jest juz w bazie (weryfikujemy po mailu), jesli nie ma to dodajemy, a jak jest to robimy aktualizacje danych
	if($uid=$GLOBALS['o_db']->komorka('id', "WHERE `email`='".htmlspecialchars($l_uzytkownik['email'])."'", 'cms_uzytkownicy'))
	{
echo '1';
		$GLOBALS['o_db']->aktualizuj(array('logowanie'=>time(), 'email'=>htmlspecialchars($l_uzytkownik['email']), 'imie'=>htmlspecialchars($l_uzytkownik['first_name'])), "WHERE `id`='$uid'", 'cms_uzytkownicy');
		return $uid;
	}
	else
	{
echo '2';
		$GLOBALS['o_db']->rekord_dodaj(array('rejestracja'=>time(), 'logowanie'=>time(), 'email'=>htmlspecialchars($l_uzytkownik['email']), 'status'=>1, 'imie'=>htmlspecialchars($l_uzytkownik['first_name'])), 'cms_uzytkownicy');
var_dump($GLOBALS['o_db']->sql, $GLOBALS['o_db']->error);
		return $GLOBALS['o_db']->rekord_id;
	}
}




if(empty($_SESSION['cms']['u']['id']))
{
	//jesli niezalogowany to sprawdz czy jest wazne ciastko z FB JS SDK, jak jest to zaloguj na serwerze

	if($token=fb_token_generuj(FB_APPID, FB_SECRET))
	{
		if($l_uzytkownik=fb_udane_pobierz($token))
		{
			//jesli serwer potwierdzil zalogowanie i ma dane uzytkownika
			$_SESSION['cms']['u']['id']=uid_generuj($l_uzytkownik);
			$_SESSION['cms']['u']['status']=$o_db->komorka('status', "WHERE `email`='".htmlspecialchars($l_uzytkownik['email'])."'", 'cms_uzytkownicy');
			$_SESSION['cms']['u']['imie']=$l_uzytkownik['first_name'];
			$_SESSION['cms']['u']['email']=$l_uzytkownik['email'];

//			$_SESSION['u']['podmiot']=1;						//!!!chwilowo na sztywno
		}
	}
}

$wynik['cms']=$_SESSION['cms'];

echo json_encode($wynik);

?>