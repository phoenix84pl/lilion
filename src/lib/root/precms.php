<?php

//wersja 20170710 - dodana obsługa composera
//wersja 20170124 - dodane klasy log

//plik zawiera elementarne prefunkcje potrzebne jeszcze przed zaladowaniem klasy cms

define('STREFA_CZASOWA', 'UTC');

srand();
ini_set('session.gc_maxlifetime', 3600);	//dlugosc trwania sesji
session_start();
libxml_use_internal_errors(true);
date_default_timezone_set(STREFA_CZASOWA);
ini_set('session.bug_compat_warn', 0);
ini_set('session.bug_compat_42', 0);

if(file_exists(__DIR__.'/../vendor/autoload.php')) require_once(__DIR__.'/../vendor/autoload.php');

	//jesli konsola
if(empty($_SERVER['REMOTE_ADDR'])) $_SERVER['REMOTE_ADDR']=NULL;
if(empty($_SERVER['REMOTE_ADDR'])) $_SERVER['REQUEST_URI']=NULL;
if(empty($_SERVER['REMOTE_ADDR'])) $_SERVER['HTTP_ACCEPT_LANGUAGE']=NULL;

if(!empty($argv)) parse_str(implode('&', array_slice($argv, 1)), $_REQUEST);
	//koniec konsoli

if(isset($_REQUEST['cms_tryb']))
{
	if($_REQUEST['cms_tryb']=='null') unset($_SESSION['cms']['tryb']);
	else $_SESSION['cms']['tryb']=$_REQUEST['cms_tryb'];
}

function cms_przekieruj($link = null){
	header('Location: '.(isset($link) ? $link : '.'));
	exit;
}

function cms_czasy_rejestruj($opis=NULL)
{
	if(isset($_SESSION['cms']['tryb']) && strpos($_SESSION['cms']['tryb'], 'czasy')===FALSE) return null;

	global $cms_czasy;

	if(isset($_SESSION['cms']['tryb']) && strpos($_SESSION['cms']['tryb'], 'admin')!==FALSE) $wywolanie=debug_backtrace();

	$czas=microtime(TRUE);

	if(!isset($wywolanie[0]['file'])) $wywolanie[0]['file']='';
	if(!isset($wywolanie[0]['line'])) $wywolanie[0]['line']='';

	$cms_czasy[]=array('czas'=>$czas, 'opis'=>$opis, 'plik'=>$wywolanie[0]['file'], 'linia'=>$wywolanie[0]['line']);
}

function cms_czasy_generuj()
{
	if(!isset($_SESSION['cms']['tryb']) || strpos($_SESSION['cms']['tryb'], 'czasy')===FALSE) return null;

	global $cms_czasy;

	$wynik="<table width=\"100%\" border=\"1\"><tr><th>Czas</th><th>Różnica</th><th>Opis</th><th>Plik</th><th>Linia</th></tr>";
	$czas_poprzedni=$ladowanie=0;
	foreach($cms_czasy as $id=>$dane)
	{
		$roznica=$dane['czas']-$czas_poprzedni;
		if($roznica==$dane['czas']) $roznica=0;
		$roznica=round($roznica, 4);
		$ladowanie+=$roznica;

		$kolor='green';
		if($roznica>0.001) $kolor='lime';
		if($roznica>0.01) $kolor='yellow';
		if($roznica>0.1) $kolor='red';
		if($roznica>1) $kolor='brown';
		$wynik.="<tr><td>$dane[czas]</td><td bgcolor=\"$kolor\">+$roznica</td><td>$dane[opis]</td><td>$dane[plik]</td><td>$dane[linia]</td></tr>";
		$czas_poprzedni=$dane['czas'];
	}
	$wynik.="</table><br /><center>Czas generowania witryny: <strong>$ladowanie</strong> sekund</center><br />";

	return $wynik;
}

function cms_raport_generuj()
{
	if(!isset($_SESSION['cms']['tryb']) || strpos($_SESSION['cms']['tryb'], 'raport')===FALSE) return null;

	$wynik="<table width=\"100%\" border=\"1\"><tr><th>Klucz</th><th>Wartość</th></tr>
			<tr><td>TimeStamp:</td><td>".time()."</td></tr>
			<tr><td>Czas:</td><td>".date("Y/m/d H:i:s", time())."</td></tr>
			<tr><td>CMS_Tryb:</td><td>{$_SESSION['cms']['tryb']}</td></tr>
			<tr><td>U_ID:</td><td>{$_SESSION['u_id']}</td></tr>
			</table><br />";

	return $wynik;
}

function cms_require_katalog($sciezka)
{
	if(file_exists($sciezka))
	{
		foreach(new DirectoryIterator($sciezka) as $file)
		{
			if(!$file->isDot() )
			{
				$nazwa = $sciezka.'/'.$file->getFilename();
				if($file->isDir()) cms_require_katalog($nazwa);
				else require_once($nazwa);
			}
		}
	}
}

function cms_require_projekt($sciezka)
{
	if(file_exists("$sciezka/main.class.php")) require_once("$sciezka/main.class.php");			//nadklasa
	if(file_exists("$sciezka/common.class.php")) require_once("$sciezka/common.class.php");		//szablon
	cms_require_katalog($sciezka);

	cms_czasy_rejestruj("Załadowano Projekt: $sciezka");
}

function cms_require_modul($modul)
{
	//funkcja laduje komplet plikow do jakiegos modulu

	switch($modul)
	{
		case 'db':
		{
			require_once(__DIR__.'/../../config/db.php');
			require_once(__DIR__.'/../../lib/php_class/raport.lilion.class.php');
			require_once(__DIR__.'/../../lib/php_class/log.lilion.class.php');
			require_once(__DIR__.'/../../lib/php_class/db.lilion.class.php');

			$GLOBALS['o_db']=new db(DB_CMS_HOST, DB_CMS_LOGIN, DB_CMS_HASLO, DB_CMS_BAZA);

			break;
		}

		case 'curl':
		{
			require_once(__DIR__.'/../../lib/php_class/raport.lilion.class.php');
			require_once(__DIR__.'/../../lib/php_class/log.lilion.class.php');
			require_once(__DIR__.'/../../lib/php_class/curl.lilion.class.php');

			break;
		}

		case 'email':
		{
			require_once(__DIR__.'/../../config/email.php');
			require_once(__DIR__.'/../../lib/php_class/log.lilion.class.php');
			require_once(__DIR__.'/../../lib/php_class/email.lilion.class.php');

			$GLOBALS['o_email']=new email(EMAIL_HOST, EMAIL_PORT, EMAIL_LOGIN, EMAIL_HASLO, EMAIL_ADRES, EMAIL_NAZWA);

			break;
		}

	}
}

//require
cms_czasy_rejestruj('START');

?>
