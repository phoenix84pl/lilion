<?php
class konto
{
	//klasa odopowiada za obsluge kont

	private $_o_db;		//znacznik_bazy
	public	$o_raport;	//znacznik_raportu
	public	$error;		//ostatni znany blad

	private $_uid;		//uzytkownik, ktorego kontem sie bawimy
						//uid banku (wplaty i wyplaty)	-1	(jedyny uid, ktory moze byc na minusie)
						//uid prowizji (zysk)			-2

	private $_waluta;	//waluta konta, ktorym sie bawimy
	private $_id;		//numer konta, na ktorym pracujemy (moze byc 0, wtedy znaczy, ze konto nie istnieje i je emulujemy, ale wszystko jest tam puste, a saldo i blokady wynosza 0)
						//konto emulowane (jeszczeni nie istniejace)	0

	private $_saldo;		//ostatnie saldo konta (aktualizowane po kazdej operacji)
//	private $_blokady;		//ostatnie blokady konta (aktualizowane po kazdej operacji)
	private $_dostepne;		//ostatnie dostepne konta (aktualizowane po kazdej operacji)

	function __construct($o_db, $uid, $waluta)
	{
		//konstruktor - laduje dane uzytkownika i waluty do pamieci i ustala numer konta (nieistotny zewnetrznie)
		//waluta moze byc tekstowa lub id_waluty

		$this->o_raport=new raport();
		$this->_o_db=$o_db;

		//pozniej trzeba zrobic sprawdzanie uid i destrukcja obiektu, ale nie wiadomo jak zrobic destrukcje obiektu z wewnatrz by dzialalo
		if(ctype_digit($waluta)) $waluta=$this->cid2waluta($waluta);		//jesli ID waluty to zamien na kod

		$this->_uid=$uid;
		$this->_waluta=$waluta;

			//ta czesc jest prawie zduplikowana w metodzie _aktualizuj (na ten moment brak pomyslu na integracje, bo bazuje na innych danych w zapytaniu, mimo ze robi to samo)
		if($wynik=$this->_o_db->wiersz(array('id', 'saldo', 'dostepne'), "WHERE `uid`='".htmlspecialchars($this->_uid)."' AND `id_waluty`='".$this->waluta2cid($waluta)."'", '_transfery_konta'))
		{
				//jesli konto istnieje
			$this->_id=$wynik['id'];
			$this->_saldo=$wynik['saldo'];
//			$this->_blokady=$wynik['blokady'];
			$this->_dostepne=$wynik['dostepne'];
		}
		else
		{
			$this->_id=0;
			$this->_saldo=0;
//			$this->_blokady=0;
			$this->_dostepne=0;
		}

	}

	private function _aktualizuj()
	{
		//funkcja aktualizuje dane w obiekcie (troche duplikuje sie z konstruktorem, ale zapytanie do bazy na innych danych)

		if($wynik=$this->_o_db->wiersz(array('id', 'saldo', 'dostepne'), "WHERE `id`='".htmlspecialchars($this->_id)."'", '_transfery_konta'))
		{
				//jesli konto istnieje
			$this->_id=$wynik['id'];
			$this->_saldo=$wynik['saldo'];
//			$this->_blokady=$wynik['blokady'];
			$this->_dostepne=$wynik['dostepne'];
		}
		else
		{
			$this->_id=0;
			$this->_saldo=0;
//			$this->_blokady=0;
			$this->_dostepne=0;
		}
	}

	private function _konto_otworz($uid, $waluta)
	{
		//funkcja otwiera konto dla $uid i $waluta, jesli konto nie istnialo wczesniej - zwraca numer nowego konta lub tego, ktore istnialo wczesniej

		if(!$id=$this->_o_db->komorka('id', "WHERE `id_waluty`='".htmlspecialchars($this->waluta2cid($waluta))."' AND `uid`='".htmlspecialchars($uid)."'", '_transfery_konta'))
		{
			if($cid=$this->waluta2cid($waluta))	//kod waluty
			{
				$this->_o_db->rekord_dodaj(array('uid'=>htmlspecialchars($uid), 'id_waluty'=>htmlspecialchars($cid)), 'transfery_konta');
				return $this->_o_db->rekord_id;
			}
		}
		else
		{
			return $id;
		}
	}

	public function cid2waluta($cid)
	{
		//zamienia id waluty na jej kod

		if($waluta=$this->_o_db->komorka('kod', "WHERE `id`='".htmlspecialchars($cid)."'", 'waluty')) return $waluta;
		else
		{
			$this->error="Currency C$cid does not exist.";
			return false;
		}
	}

	public function waluta2cid($waluta)
	{
		//zamienia kod waluty na jej id

		if(($cid=$this->_o_db->komorka('id', "WHERE `kod`='".htmlspecialchars($waluta)."'", 'waluty'))>0) return $cid;
		else
		{
			$this->error="$waluta does not exist.";
			return false;
		}
	}

	public function uid2id($uid)
	{
		//zamienia uid na id konta przy aktualnej walucie

		if(($id=$this->_o_db->komorka('id', "WHERE `kod`='".htmlspecialchars($this->_waluta)."' AND `uid`='".htmlspecialchars($uid)."'", '_transfery_konta'))>0) return $id;
		else
		{
			$this->error="U{$this->_uid} $waluta account does not exist.";
			return false;
		}
	}

	public function dostepne_podaj()
	{
			//zwraca dostepne na aktualnym koncie
		return $this->_dostepne;
	}

	public function przelew_reczny_wykonaj($uid_do, $kwota, $tytul)
	{
		//funkcja wykonuje zestaw przelewow miedzy kontami - FUNKCJA TYLKO DO UZYCIA PRZED ADMINA Z POZIOMU PRZEGLADARKI!

			//sprawdzanie uprawnien
		if(!(isset($_SESSION['cms']) && (isset($_SESSION['cms']['u']['id'])) && ($_SESSION['cms']['u']['status']>=8)))
		{
			$this->error="Permission denied";
			return false;
		}
		else $oid=$_SESSION['cms']['u']['id'];

			//sprawdzanie kwoty
		if($kwota<=0)
		{
			$this->error="Transfer amount cannot be 0 or less";
			return false;
		}

			//sprawdzanie czy wystarcza srodkow
		if(($this->_uid!=-1) && ($kwota>$this->_dostepne))	//dlatego, ze uzytkownik -1 moze byc na minusie, bo jest bankiem
		{
			$this->error="$kwota>{$this->_dostepne} Insufficient funds.";
			return false;
		}

			//zakladanie konta nadawcy, jesli nie ma (nawet jesli jest, to wykonujemy procedure, bo nie zalozy drugiego)
		if(!$this->_id=$this->_konto_otworz($this->_uid, $this->_waluta))	//od razu zapisz nowy numer konta do zmiennej
		{
			$this->error="Cannot open {$this->_waluta} account for U{$this->_uid}";
			return false;
		}

			//zakladanie konta odbiorcy, jesli nie ma
		if(!$id_do=$this->_konto_otworz($uid_do, $this->_waluta))	//od razu zapisz nowy numer konta do zmiennej
		{
			$this->error="Cannot open {$this->_waluta} account for U{$this->_uid}";
			return false;
		}

			//operacja wlasciwa
		if(!$this->_o_db->bezposrednie("START TRANSACTION;"))
		{
			$this->error="Start transaction failed.";
			return false;
		}
		elseif(!($suma=$this->_o_db->kolumna_operacja('SUM', 'saldo', "WHERE `id_waluty`='".htmlspecialchars($this->waluta2cid($this->_waluta))."'", 'transfery_konta')==0))	//sprawdzanie przed transakcja czy suma sald w walucie to 0
		{
			$this->_o_db->bezposrednie("ROLLBACK;");
			$this->error="Sum of amounts {$this->_waluta} BEFORE transfers IS NOT 0 ($suma {$this->_waluta})!";
			return false;
		}
		elseif(!$this->_o_db->rekord_dodaj(array('oid'=>htmlspecialchars($oid), 'czas'=>time(), 'tytul'=>htmlspecialchars($tytul), 'status'=>0), 'transfery_transakcje'))	//tworzenie transakcji
		{
			$this->_o_db->bezposrednie("ROLLBACK;");
			$this->error="Couldn't add a transation.";
			return false;
		}
		elseif(!$id_transakcje=$this->_o_db->rekord_id)		//sprawdzanie numeru transakcji
		{
			$this->_o_db->bezposrednie("ROLLBACK;");
			$this->error="Couldn't reach a transaction number.";
			return false;
		}
		elseif(!$this->_o_db->rekord_dodaj(array('id_transakcje'=>htmlspecialchars($id_transakcje), 'id_konta'=>htmlspecialchars($this->_id), 'kwota'=>htmlspecialchars(-$kwota)), 'transfery_przelewy'))	//pobieramy kase od nadawcy
		{
			$this->_o_db->bezposrednie("ROLLBACK;");
			$this->error="Couldn't substract the amount.";
			return false;
		}
		elseif(!$this->_o_db->rekord_dodaj(array('id_transakcje'=>htmlspecialchars($id_transakcje), 'id_konta'=>htmlspecialchars($id_do), 'kwota'=>htmlspecialchars($kwota)), 'transfery_przelewy'))		//dajemy kase odbiorcy
		{
			$this->_o_db->bezposrednie("ROLLBACK;");
			$this->error="Couldn't add the amount.";
			return false;
		}
		elseif(!$this->_o_db->aktualizuj(array('saldo'=>$this->_o_db->kolumna_operacja('SUM', 'kwota', "WHERE `id_konta`='".htmlspecialchars($this->_id)."'", 'transfery_przelewy')), "WHERE `id`='".htmlspecialchars($this->_id)."'", 'transfery_konta'))			//aktualizacja salda konta nadawczego
		{
			$this->_o_db->bezposrednie("ROLLBACK;");
			$this->error="Couldn't update sending account balance.";
			return false;
		}
		elseif(!$this->_o_db->aktualizuj(array('saldo'=>$this->_o_db->kolumna_operacja('SUM', 'kwota', "WHERE `id_konta`='".htmlspecialchars($id_do)."'", 'transfery_przelewy')), "WHERE `id`='".htmlspecialchars($id_do)."'", 'transfery_konta'))			//aktualizacja salda konta odbiorczego
		{
			$this->_o_db->bezposrednie("ROLLBACK;");
			$this->error="Couldn't update receiving account balance.";
			return false;
		}
		elseif(!($suma=$this->_o_db->kolumna_operacja('SUM', 'saldo', "WHERE `id_waluty`='".htmlspecialchars($this->waluta2cid($this->_waluta))."'", 'transfery_konta')==0))	//sprawdzanie przed transakcja czy suma sald w walucie to 0
		{
			$this->_o_db->bezposrednie("ROLLBACK;");
			$this->error="Sum of amounts {$this->_waluta} AFTER transfers IS NOT 0 ($suma {$this->_waluta})!";
			return false;
		}
		elseif(!$this->_o_db->aktualizuj(array('status'=>7), "WHERE `id`='".htmlspecialchars($id_transakcje)."'", 'transfery_transakcje'))			//aktualizacja statusu
		{
			$this->_o_db->bezposrednie("ROLLBACK;");
			$this->error="Couldn't update the transaction status.";
			return false;
		}
		elseif(!$this->_o_db->bezposrednie("COMMIT;"))		//zatwierdzenie transakcji
		{
			$this->_o_db->bezposrednie("ROLLBACK;");
			$this->error="Commit transaction failed.";
			return false;
		}
		else 				//aktualizacja danych w obiekcie i zwrot wyniku
		{
			$this->_aktualizuj();

				//testowanie zapytan z gory
			return $id_transakcje;
		}
	}
}
?>