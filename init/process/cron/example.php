<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__.'/../../lib/root/precms.php');
cms_require_modul('db');
require_once(__DIR__."/../../lib/php_foo/agado.php");

//pobierz aktywne inwestycje, ktore nie maja odsetek od ponad 24h
//sprobuj zamknac te ktore z niewiadomych przyczyn nadal sa otwarte
//wyplac odsetki
//zamknij inwestycje jesli to byly ostatnie odsetki

function inwestycja_zakoncz($id, $uid, $kwota)
{
	global $o_db;

	if(transakcja(0, $uid, $kwota, "Investment #$id"))
	{
		$o_db->aktualizuj(array('status'=>7), "WHERE `id`='".htmlspecialchars($id)."'", 'inwestycje');
		echo "Zamknięto inwestycję #$id użytkownika $uid. Zwrócono kwotę $kwota PLN.\n";
	}
}

$o_db->bezposrednie("UPDATE `inwestycje` SET `czas_odsetki`=`czas` WHERE `czas_odsetki`=0;");	//ustaw czas odsetki zgodny z czasem otwarcia zakladu, zeby nie bylo zer i zeby nowym nie dawalo odsetek na dzien dobry

$inwestycje=$o_db->tabela(array('id', 'uid', 'id_programy', 'nazwa', 'dlugosc', 'stawka', 'czas', 'czas_odsetki', 'kwota', 'odsetki_suma', 'status'), "WHERE `status`='1' AND `czas_odsetki`<'".(time()-86400)."'", '_inwestycje');

if($inwestycje) foreach($inwestycje as $id=>$dane)
{
	if($dane['czas_odsetki']>=($dane['czas']+$dane['dlugosc']*86400)) inwestycja_zakoncz($dane['id'], $dane['uid'], $dane['kwota']);	//jesli dostal komplet odsetek to zamknij
	else
	{
		//liczymy odsetki
		$dane['czas_odsetki']=$dane['czas_odsetki']<$dane['czas']?$dane['czas']:$dane['czas_odsetki'];

		$odsetki=floor($dane['kwota']*$dane['stawka']*100)/100;

		if($odsetki>0) if(transakcja(0, $dane['uid'], $odsetki, "Interest #{$dane['id']}"))
		{
			$o_db->aktualizuj(array('czas_odsetki'=>$dane['czas_odsetki']+86400, 'odsetki_suma'=>$dane['odsetki_suma']+$odsetki), "WHERE `id`='{$dane['id']}'", 'inwestycje');
			echo "Odsetki $odsetki PLN → {$dane['uid']}.\n";
		}

		if($dane['czas_odsetki']+86400>=$dane['czas']+$dane['dlugosc']*86400) inwestycja_zakoncz($dane['id'], $dane['uid'], $dane['kwota']);	//jesli dostal komplet odsetek to zamknij
	}
}

echo "OK\n";
?>