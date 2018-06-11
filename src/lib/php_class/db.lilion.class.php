<?php

	//wersja 20180526 - poczatek przejscia na PDO (zostalo 21 metod uzywajacych _odpytaj, ktorych nie migrowano na pdo)
	//wersja 20170124 - tostring

class db
{
/*
SPIS TRESCI
	public	function __construct($host, $login, $haslo, $baza=NULL)
	public function __toString()
	private	function _start()
	private function _blad_obsluz()
	public	function ustaw_baza($baza)
	public	function prefix_ustaw($prefix)
	public	function ustaw_warunki($warunki)
	public	function ustaw_format_czas($format_czas)
	private	function _przygotuj($sql)
	private	function _wykonaj($sql)
	private	function _pobierz($sql)

	public	function rekord_dodaj(array $dane, $tabela)
	public	function dodaj_rekord(array $dane, $tabela)
	public	function aktualizuj (array $dane, $warunki, $tabela)
	public	function bezposrednie ($sql)
	public	function komorka ($kolumna, $warunki, $tabela)
	public	function komorka_bezposrednie ($sql)
	public	function komorka_czas ($kolumna, $warunki, $tabela)
	public	function tabela ($l_kolumny, $warunki, $tabela)
	public	function tabela_bezposrednie($sql)
	public	function kolumna ($kolumna, $warunki, $tabela)
	public	function kolumna_klucz ($klucze='id', $wartosci='id', $warunki, $tabela)
	public	function kolumna_klucz_bezposrednie ($sql)
	public	function kolumna_operacja ($operacja, $kolumna, $warunki, $tabela)
	public	function kolumna_bezposrednie($sql)
	public	function kolumna_klucz_bezposrednie($sql)
	public	function wiersz ($l_kolumny, $warunki, $tabela)
	public	function wiersz_bezposrednie($sql)
	public	function lista_bezposrednie ($sql)
	public	function lista_klucz_bezposrednie ($sql)
	public	function usun_rekord ($warunki, $tabela)

	public	function tabele ()
	public	function kolumny ($tabela)
	public function pobierz_multi_index($kolumna, $tabela, $baza)
	public function ustaw_multi_index(array $multi,$tabela)
	public	function dodaj_tabela(array $kolumny, $tabela)
	public function dodaj_widok($widok, $zapytanie)
	public	function wyczysc_tabela ($tabela)
	public	function dodaj_kolumna(array $kolumna, $tabela)
	public	function zmien_kolumna(array $kolumna, $tabela)


*/
	public	$o_raport;		//obiekt raportujacy
	private	$o_db;
	private	$host;
	private	$login;
	private	$haslo;

	public	$baza;
	public	$prefix;
	public	$format_czas='%Y%m%d%H%i%s';
	public	$ilosc_zapytan=0;
	public	$tabela_xy=false;

	public	$sql;		//ostatnio wykonane zapytanie
	public	$wynik;		//wynik ostatnio wykonanego zapytania
	public	$rekord_id;	//id ostatnio dodanego rekordu
	public	$error;		//ostatni komunikat bledu

	//dane do import/export
	private $struktura_tabeli = false;
	private $rekordy = false;

	/**
	 * Konstruktor
	 * @param mixed $host adres hosta, albo tabliza zawierająca wszystkie wymagane parametry
	 * @param string $login
	 * @param string $haslo
	 * @param string $baza
	 */
	public	function __construct($host, $login=NULL, $haslo=NULL, $baza=NULL)
	{
		$this->o_raport=new raport;

		if(is_array($host))
		{
			$this->host=$host['host'];
			$this->login=$host['login'];
			$this->haslo=$host['haslo'];
			$this->baza=$host['baza'];
		}
		else
		{
			$this->host=$host;
			$this->login=$login;
			$this->haslo=$haslo;
			$this->baza=$baza;
		}

		$this->_start();
	}

	public function __toString()
    {
			//zwraca wersje tekstowa klasy - mozna dowolnie modyfikowac

        return __CLASS__;	//niektore klasy korzystajace np. log wymagaja wypisania argumentow metod, wtedy potrzebna jest wersja tekstowa
    }

	/**
	 * Metoda inicjuje polaczenie z mysql i zwraca klucz polaczenia
	 * W przypadku niepowodzenia zapisuje błąd w raporcie
	 * @return $this zwraca własny obiekt - wygodne przy łańcuchowaniu
	 */
	private	function _start()
		{
		$this->o_raport->dodaj('raport');

		if(!empty($this->host) and !empty($this->login) and !empty($this->haslo))
		{
			$this->o_db=new PDO("mysql:host={$this->host};dbname={$this->baza}", $this->login, $this->haslo);

			$polaczenie = (!empty($this->o_db))? true : false;
		}
		else $polaczenie = false;

		if($polaczenie !== false) $this->o_db->query("SET NAMES 'utf8';");
		else
			{
			$this->error="Nie udało się połączyć z bazą!";
			$this->o_raport->dodaj('error');
			}

		return $this;
		}

	private function _blad_obsluz()
	{
		$this->o_db->errorCode();
		$this->o_db->errorInfo();
		//!!!
		$wynik=$this->error="trzeba zmontowac z tego czytelny tekst (w o_db jest chyba nawet tresc zapytania)";

		return $wynik;
	}

	/**
	 * Metoda ustawia domyslna baze danych
	 * @param string $baza
	 * @return $this zwraca własny obiekt - wygodne przy łańcuchowaniu
	 */
	public	function baza_ustaw($baza)
	{
		$this->o_raport->dodaj('raport');
		if(!empty($this->o_db))
		{
			$this->baza=$baza;
			$this->o_db->query("USE {$this->baza};");
		}

		return $this;
	}
	public	function ustaw_baza($baza)
	{
		return $this->baza_ustaw($baza);
	}

	public	function baza_sprawdz()
	{
		//funkcja zwraca nazwe aktualnie uzywanej bazy

		return $this->baza;
	}

	public	function prefix_ustaw($prefix)
	{
		$this->o_raport->dodaj('raport');
		$this->prefix=$prefix;
		return $this;
	}

	/**
	 * metoda ustawia domyślny format czasu
	 * @param string $format_czas
	 * @return $this zwraca własny obiekt - wygodne przy łańcuchowaniu
	 */
	public	function ustaw_format_czas($format_czas)
	{
		$this->o_raport->dodaj('raport');
		$this->format_czas=$format_czas;
		return $this;
	}

	/**
	 * metoda wykonuje zapytanie do MySQL
	 * dla zapytań typu SELECT, SHOW, EXPLAIN i DESCRIBE zwraca identyfikator wyniku (lub FALSE w przypadku niepowodzenia)
	 * dla innych zapytań SQL zwraca TRUE lub FALSE informując czy zapytanie zakończyło się sukcesem czy też nie.
	 *
	 * @param string $sql
	 * @return mixed
	 */
	private	function _przygotuj($sql)
	{
		$this->o_raport->dodaj('raport');
		$this->sql=$sql;
		$this->baza_ustaw($this->baza);		//bo mu sie czasem pierdola polaczenia
		$this->ilosc_zapytan++;

		return $sql;
	}

	private function _wykonaj($sql)
	{
		//funkcja sluzy do odpytywania kiedy nie zwraca danych tabelarycznych
		$this->o_raport->dodaj('raport');

		$wynik=$this->o_db->exec($this->_przygotuj($sql));

		return $wynik;
	}

	private function _pobierz($sql)
	{
		//funkcja sluzy do odpytywania kiedy nie zwraca danych tabelarycznych
		$this->o_raport->dodaj('raport');

		$efekt=$this->o_db->query($this->_przygotuj($sql));
		$wynik=array();		//dzieki temu kolejne foreach nie musza byc sprawdzane czy sa array
		if($efekt==FALSE)
		{
			$this->error=$this->o_db->errorInfo();
			return FALSE;			//np. brak kolumny
		}
		if($efekt->rowCount()>0)
		{
			foreach($efekt as $klucz=>$wartosc) $wynik[$klucz]=$wartosc;
			$efekt->closeCursor();
		}

		return $wynik;
	}

//funkcje operujace na rekordach

	/**
	 * Metoda dodaje rekord do bazy
	 *
	 * @param array $dane tablica asocjacyjna gdzie kluczami sa nazwy kolumn tabeli
	 * @param string $tabela
	 * @return bolean informacja o powodzeniu lub niepowodzeniu operacji
	 */
	public	function rekord_dodaj(array $dane, $tabela)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
		{
			//tworzenie zapytania
			$wartosci=$kolumny=$insert=array();
			foreach($dane as $klucz=>$wartosc)
			{
				$kolumny[] = "`$klucz`";
				$wartosci[] = ":$klucz";
				$insert[":$klucz"]=$wartosc;
			}

			$kolumny = implode(', ', $kolumny);
			$wartosci = implode(', ', $wartosci);
			$db=$this->o_db->prepare("INSERT INTO `{$this->baza}`.`{$this->prefix}$tabela` ($kolumny) VALUES ($wartosci);");

			$this->sql=$db->queryString;

			//wykonanie zapytania
			if(($wynik=$db->execute($insert))===false) $this->error=$db->errorInfo();
			else $this->rekord_id=$this->o_db->lastInsertId();

			return $wynik;
		}

	}

	public	function dodaj_rekord(array $dane, $tabela)
	{
		//nakladka na rekord_dodaj
		return $this->rekord_dodaj($dane, $tabela);
	}

	public function zeruj_rekord($id, $tabela)
	{
		$this->o_raport->dodaj('raport');
		$arr_kolumny = $this->kolumny($tabela);

//		echo "$tabela: <pre>";
//		var_dump($arr_kolumny);
//		echo '</pre>';

		$arr_dane = array();
		$primary = array_search('PRI', $arr_kolumny['Key']);
		$primary = $arr_kolumny['Field'][$primary];
//		echo "primary: $primary";
		foreach($arr_kolumny['Field'] as $n0 => $nazwa)
		{
			if($arr_kolumny['Key'][$n0] !== 'PRI')
			{
				if($arr_kolumny['Null'][$n0] == 'YES')
					$arr_dane[$nazwa] = NULL;
				elseif($arr_kolumny['Key'][$n0] == 'UNI')
					$arr_dane[$nazwa] = $id;
				else
					$arr_dane[$nazwa] = (strpos('int',$arr_dane['Type'][$n0]) !== false)? 0 : '';
			}
		}
//		echo "kolumny: <pre>";
//		var_dump($arr_dane);
//		echo '</pre>';
		return $this->aktualizuj($arr_dane, "WHERE $primary = '$id' ", $tabela);
	}

	/**
	 * Metoda aktualizuje rekordy tablicy $dane gdzie nazwami kolumn sa klucze tablicy
	 *
	 * @param array $dane tablica asocjacyjna gdzie kluczami sa nazwy kolumn tabeli
	 * @param string $warunki
	 * @param string $tabela
	 * @return bolean informacja o powodzeniu lub niepowodzeniu operacji
	 */
	public	function aktualizuj(array $dane, $warunki, $tabela)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
		{
			//tworzenie zapytania
			$pary=$update=array();
			foreach($dane as $klucz=>$wartosc)
			{
				$pary[]="`$klucz`=:$klucz";
				$update[":$klucz"]=$wartosc;
			}

			$wartosci=implode(', ', $pary);
			$db=$this->o_db->prepare("UPDATE `{$this->baza}`.`{$this->prefix}$tabela` SET $wartosci $warunki;");

			//wykonanie zapytania
			$wynik=$db->execute($update);
			$this->sql=$db->queryString;
			$this->rekord_id=$this->o_db->lastInsertId();

			return $wynik;
		}
	}

	/**
	 * Metoda wykonuje przekazane w zapytanie
	 * @param string $sql - zapytanie
	 * @return bolean informacja o powodzeniu lub niepowodzeniu operacji
	 */
	public	function bezposrednie($sql)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)	return $this->_wykonaj($sql);
	}

	/**
	 * Metoda wczytuje jedna komorke z podanej tabeli i zwraca ją w postaci pojedynczej zmiennej
	 * @param string $kolumna
	 * @param string $warunki
	 * @param string $tabela
	 * @return mixed pobrana z tabeli wartość lub informacja o niepowodzeniu operacji
	 */
	public	function komorka ($kolumna, $warunki, $tabela)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
		{
			if(!strpos($warunki, 'LIMIT')) $warunki.=' LIMIT 1';

			//tworzenie zapytania
			$sql="SELECT `$kolumna` FROM `{$this->prefix}$tabela` $warunki;";

			$wynik=NULL;
			$efekt=$this->_pobierz($sql);

			if($efekt==FALSE) return FALSE;
			else foreach($efekt as $klucz=>$wartosc) if($wynik==NULL) $wynik=$wartosc[$kolumna];		//w praktyce przekazuje tylko popjedyncza $kolumna z pierwszego wiersza

			return $wynik;
		}
	}

	/**
	 * Metoda zwraca pojedynczą wartość na podstawie zapytania
	 *
	 * @param string $sql
	 * @return mixed pobrana z tabeli wartość lub informacja o niepowodzeniu operacji
	 */
	public	function komorka_bezposrednie ($sql)
		{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
			{
			//wykonanie zapytania
			if($this->wynik=$this->_odpytaj($sql))
				{
				$wiersz=mysql_fetch_array($this->wynik, MYSQL_NUM);
				$wynik=$wiersz[0];
				}
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}
			return $wynik;
			}
		}

	/**
	 * Metoda wczytuje jedna komorke z podanej tabeli i zwraca ją w postaci pojedynczej zmiennej w podanym formacie
	 * @param string $kolumna
	 * @param string $warunki
	 * @param string $tabela
	 * @return mixed pobrana z tabeli wartość lub informacja o niepowodzeniu operacji
	 */
	public	function komorka_czas ($kolumna, $warunki, $tabela)
		{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
			{
			$format_czas=$this->format_czas;

			//tworzenie zapytania
			$sql="SELECT DATE_FORMAT(`$kolumna`, '$format_czas') as `$kolumna` FROM `{$this->prefix}$tabela` $warunki;";

			//wykonanie zapytania
			if($this->wynik=$this->_odpytaj($sql))
				{
				$wiersz=mysql_fetch_array($this->wynik, MYSQL_ASSOC);
				$wynik=$wiersz[$kolumna];
				}
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}
			return $wynik;
			}

		}

	/**
	 * Metoda zwraca wynik mysql jako tablice[x-kolumna][y-wiersz]
	 *
	 * @param mixed $l_kolumny string zawierający nazwy komumn oddzieline przecinkami albo tablica zawierająca nazwy kolumn
	 * @param string $warunki
	 * @param string $tabela
	 * @return mixed - wynik w postaci tablica[x-kolumna][y-wiersz] lub informacja o niepowodzeniu operacji
	 */

	public	function tabela ($l_kolumny, $warunki, $tabela)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
		{
			//tworzenie zapytania
			if(is_array($l_kolumny))
			{
				foreach ($l_kolumny as $klucz => $wartosc) $l_kolumny[$klucz] = "`$wartosc`";
				$kolumny = implode(', ', $l_kolumny);
			}
			else $kolumny=$l_kolumny;	//np. * (gwiazdka)

			$sql="SELECT $kolumny FROM `{$this->prefix}$tabela` $warunki;";

			return $this->_pobierz($sql);
		}
	}

	/**
	 * Metoda zwraca wynik zapytania w postaci dwuwymiarowej tabicy o formie zależnej od ustawienia
	 * właściwiści $this->tabela_xy
	 *
	 * @param string $sql
	 * @return mixed - wynik w postaci tablica[x-kolumna][y-wiersz] lub tablica[x-wiersz][y-kolumna]
	 * lub informacja o niepowodzeniu operacji
	 */
	public	function tabela_bezposrednie($sql)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
		{
			//wykonanie zapytania

			return $this->_pobierz($sql);
		}
	}

	/**
	 * Metoda zwraca zawartość wybranej kolumny w postaci tablicy[x-wiersz]
	 *
	 * @param string $kolumna
	 * @param string $warunki
	 * @param string $tabela
	 * @return mixed - wynik w postaci tablica[x-wiersz] lub informacja o niepowodzeniu operacji
	 */
	public	function kolumna($kolumna, $warunki, $tabela)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
		{
			//tworzenie zapytania
			$sql="SELECT $kolumna FROM `{$this->prefix}$tabela` $warunki;";

			//wykonanie zapytania
			foreach($this->_pobierz($sql) as $klucz=>$wartosc) $wynik[$klucz]=$wartosc[0];

			return $wynik;
		}
	}

	/**
	 * metoda pobiera ze wskazanej tabeli dwie wybrane kolumny i zwraca tablice,
	 * gdzie kluczami sa wartosci z kolumny klucze, a wartosciami wartosci z kolumny wartosci
	 *
	 * @param string $klucze - nazwa pierwszej kolumny
	 * @param string $wartosci - nazwa drugiej kolumny
	 * @param string $warunki
	 * @param string $tabela
	 * @return mixed - wynik w postaci tablica[$klucze]=$wartosci lub informacja o niepowodzeniu operacji
	 */
	public	function kolumna_klucz($klucze='id', $wartosci='id', $warunki, $tabela)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
		{
			//tworzenie zapytania
			$sql="SELECT `$klucze`, `$wartosci` FROM `{$this->prefix}$tabela` $warunki;";

			foreach($efekt=$this->_pobierz($sql) as $klucz=>$wartosc) $wynik[$wartosc[0]]=$wartosc[1];

			return $wynik;
		}
	}

	/**
	 * metoda zwraca wynik operacji na kolumnie
	 *
	 * @param string $operacja
	 * @param string $kolumna
	 * @param string $warunki
	 * @param string $tabela
	 * @return mixed - wynik lub informacja o niepowodzeniu operacji
	 */
	public	function kolumna_operacja($operacja, $kolumna, $warunki, $tabela)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
		{
			//tworzenie zapytania
			$sql="SELECT $operacja(`$kolumna`) as `$kolumna` FROM `{$this->prefix}$tabela` $warunki;";

			$wynik=NULL;
			$efekt=$this->_pobierz($sql);

			if($efekt==false) return false;
			else foreach($efekt as $klucz=>$wartosc) if($wynik==NULL) $wynik=$wartosc[$kolumna];		//w praktyce przekazuje tylko popjedyncza $kolumna z pierwszego wiersza

			return $wynik;
		}

	}

	/**
	 * Alias dla lista_bezposrednie
	 * @param string $sql - zapytanie
	 * @return mixed - wynik lub informacja o niepowodzeniu operacji
	 */
	public	function kolumna_bezposrednie($sql)
	{
		$this->o_raport->dodaj('raport');
		return $this->lista_bezposrednie($sql);
	}

	/**
	 * Alias dla lista_klucz_bezposrednie
	 * @param string $sql - zapytanie
	 * @return mixed - wynik lub informacja o niepowodzeniu operacji
	 */
	public	function kolumna_klucz_bezposrednie($sql)
	{
		$this->o_raport->dodaj('raport');
		return $this->lista_klucz_bezposrednie($sql);
	}

	/**
	 * Metoda zwraca zawartość jednego rekordu tabeli
	 *
	 * @param mixed $l_kolumny string zawierający nazwy komumn oddzieline przecinkami albo tablica zawierająca nazwy kolumn
	 * @param string $warunki
	 * @param string $tabela
	 * @return mixed - wynik w postaci tablica[kolumna] lub informacja o niepowodzeniu operacji
	 */
	public	function wiersz($l_kolumny, $warunki, $tabela)
	{
		$this->o_raport->dodaj('raport');
		if($this->o_db)
		{
			//tworzenie zapytania
			if(is_array($l_kolumny))
			{
				foreach ($l_kolumny as $klucz => $wartosc) $l_kolumny[$klucz] = "`$wartosc`";
				$kolumny = implode(',', $l_kolumny);
			}
			else $kolumny=$l_kolumny;	//np. * (gwiazdka)

			if(!strpos($warunki, 'LIMIT')) $warunki.=' LIMIT 1';

			$sql="SELECT $kolumny FROM `{$this->prefix}$tabela` $warunki;";

			//wykonanie zapytania
			$efekt=$this->_pobierz($sql);
			$wynik=NULL;
			foreach($efekt as $klucz=>$wartosc) $wynik=$wartosc;	//w praktyce przekazuje tylko ostatni zwrocony wiersz

			return $wynik;
		}

	}

	/**
	 * Metoda zwraca zawartość jednego rekordu tabeli
	 * @param string $sql
	 * @return mixed - wynik w postaci tablica[kolumna] lub informacja o niepowodzeniu operacji
	 */
	public	function wiersz_bezposrednie($sql)
		{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
			{
			//wykonanie zapytania
			if($this->wynik=$this->_odpytaj($sql))
				{
				while ($wiersz=mysql_fetch_array($this->wynik, MYSQL_ASSOC))
					{
					foreach ($wiersz as $klucz=>$wartosc)
					$wynik[$klucz]=$wartosc;
					}
				}
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}

			return $wynik;
			}
		}

		/**
		 * Metoda usuwa rekord
		 * @param string $warunki - parametr konieczny ze względów bezpieczeństwa
		 * @param string $tabela
		 * @return bolean
		 */
		public	function usun_rekord($warunki, $tabela)
		{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
			{

			//tworzenie zapytania
			$sql="DELETE FROM `{$this->prefix}$tabela` $warunki;";

			//wykonanie zapytania
			if($this->wynik=$this->_odpytaj($sql)) $wynik=TRUE;
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}

			return $wynik;
			}
		}

	public	function rekord_usun($warunki, $tabela)
	{
		return $this->usun_rekord($warunki, $tabela);
	}

	public	function index_usun($kolumna,  $tabela)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
			{

			$sql = "SHOW INDEX FROM `{$this->prefix}$tabela`";

			if($this->wynik=$this->_odpytaj($sql))
				{
				$n0=0;	//zlicza zwrocone wiersze
				while ($wiersz=mysql_fetch_array($this->wynik, MYSQL_ASSOC))
					{
					foreach ($wiersz as $klucz=>$wartosc) $indexy[$klucz][$n0]=$wartosc;
					$n0++;
					}
				}
				else $wynik=FALSE;
//				uzytki::var_dump($indexy,'indeksy');
				if(!empty($indexy))
				{
					$n0 = array_search($kolumna,$indexy['Column_name']);
					if($n0 !== false)
					{
						$sql = "ALTER TABLE `{$this->prefix}$tabela` DROP INDEX `{$indexy['Key_name'][$n0]}`";
						if($this->wynik=$this->_odpytaj($sql)) 	$wynik=true;
						else $wynik=FALSE;
					}
					else $wynik=FALSE;
				}
			return $wynik;
			}

	}

	/**
	 * Metoda zwraca jedną kolumnę na podstawie zapytania
	 * @param string $sql
	 * @return mixed - wynik w postaci tablica[x-wiersz] lub informacja o niepowodzeniu operacji
	 */
	public	function lista_bezposrednie($sql)
		{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
			{
			//wykonanie zapytania
			if($this->wynik=$this->_odpytaj($sql))
				{
				$n0=0;	//zlicza zwrocone wiersze
				while ($wiersz=mysql_fetch_array($this->wynik, MYSQL_ASSOC))
					{
					foreach($wiersz as $wartosc) $wynik[$n0]=$wartosc;
					$n0++;
					}
				}
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}

			return $wynik;
			}

		}

	/**
	 * Metoda zwraca dwukolumnowy wynik na podstawie zapytania
	 * @param string $sql
	 * @return mixed - wynik w postaci tablica[kolumna_1]=kolumna_2 lub informacja o niepowodzeniu operacji
	 */
	public	function lista_klucz_bezposrednie($sql)
		{
		$this->o_raport->dodaj('raport');
		if($this->o_db)
			{
			//wykonanie zapytania
			if($this->wynik=$this->_odpytaj($sql))
				{
				while ($wiersz=mysql_fetch_array($this->wynik, MYSQL_NUM))
					{
					$wynik[$wiersz[0]]=$wiersz[1];
					}
				}
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}
			return $wynik;
			}

		}

	/**
	 * Metoda zwraca listę tabel wskazanej bazy
	 *
	 * @return mixed FALSE lub array
	 */
	public	function tabele()
		{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
			{
			//tworzenie zapytania
			$sql="SHOW TABLES;";

			//wykonanie zapytania
			if($this->wynik=$this->_odpytaj($sql))
				{
				$n0=0;	//zlicza zwrocone wiersze
				while ($wiersz=mysql_fetch_array($this->wynik, MYSQL_ASSOC))
					{
					foreach($wiersz as $wartosc) $wynik[$n0]=$wartosc;
					$n0++;
					}
				}
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}
			return $wynik;
			}

		}

	/**
	 * Metoda zwraca listę kolumn wskazanej tabeli.w formacie:
	 * array(
	 *	'Field' => array(),
	 *	'Type' => array(),
	 *	'Null' => array(),
	 *	'Key' => array(),
	 *	'Default' => array(),
	 *	'Extra' => array()
	 * )
	 *
	 * @param string $tabela - nazwa tabeli
	 * @return mixed FALSE lub array
	 */
	public	function kolumny($tabela)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
		{
			//tworzenie zapytania
			$sql="SHOW COLUMNS FROM `{$this->prefix}$tabela`;";

			//wykonanie zapytania
			if($this->wynik=$this->_odpytaj($sql))
			{
				$n0=0;	//zlicza zwrocone wiersze
				while($wiersz=mysql_fetch_array($this->wynik, MYSQL_ASSOC))
				{
					foreach($wiersz as $klucz=>$wartosc) $wynik[$klucz][$n0]=$wartosc;
					$n0++;
				}
			}
			else
			{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
			}

			return $wynik;
		}
	}

	/**
	 * Metoda pobiera informacje na temat kluczy wieloktornych związanych ze wskazaną kolumną wybranej tabeli
	 *
	 * @param string $kolumna
	 * @param string $tabela
	 * @return mixed tablica której kluczami sa nazwy indeksów a polami tablice nazw kolumn przynależych do danego klucza, lub false jeśli niepowodzenie
	 */
	public function pobierz_multi_index($kolumna, $tabela)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
		{
			$this->baza_ustaw('information_schema');

			//sprawdza nazwę klucza
			$sql = "SELECT `CONSTRAINT_NAME`
					FROM `information_schema`.`KEY_COLUMN_USAGE`
					WHERE `TABLE_NAME` = '{$this->prefix}$tabela' AND `COLUMN_NAME` = '$kolumna' AND `TABLE_SCHEMA` = '{$this->baza}'";
			if($nazwy=$this->lista_bezposrednie($sql))
			{
				$multi = array();
				foreach ($nazwy as $nazwa_klucza) {
					$sql = "SELECT `COLUMN_NAME`
							FROM `information_schema`.`KEY_COLUMN_USAGE`
							WHERE `TABLE_NAME` = '{$this->prefix}$tabela' AND `CONSTRAINT_NAME` = '$nazwa_klucza' AND `TABLE_SCHEMA` = '{$this->$baza}'";
					$pola = $this->lista_bezposrednie($sql);
					if(!empty($pola))
						$multi[$nazwa_klucza] = $pola;
				}
				$wynik = (!empty($multi))? $multi : null;
			}
			else
			{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
			}
		}
		$this->baza_ustaw($this->baza);
		return $wynik;
	}

	/**
	 * Metoda ustawia klucze wielokolumnowe dla wskazanej tabeli
	 *
	 * @param array $multi tablica pobrana metodą db::pobierz_multi_index
	 * @param string $tabela
	 * @return bolean
	 */
	public function ustaw_multi_index(array $multi, $tabela)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
		{

			foreach ($multi as $nazwa => $pola) {
				$pola = implode('`,`', $pola);
				$sql = "ALTER TABLE `{$this->prefix}$tabela` ADD CONSTRAINT `$nazwa` UNIQUE (`$pola`)";
				$wynik = $this->_odpytaj($sql);
			}

			return $wynik;
		}
	}

	/**
	 * Generuje i wykonuje zapytanie tworzące tabelę we wskazanej bazie
	 *
	 * @param array $kolumny - dane musza miec format zwrotu z zapytania SHOW COLUMNS
	 * @param string $tabela
	 * @return bolean
	 */
	public	function dodaj_tabela(array $kolumny, $tabela)
		{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
			{

			//tworzenie zapytania
			$sql="CREATE TABLE `{$this->baza}`.`{$this->prefix}$tabela` (";
			for ($i0=0; $i0<count($kolumny['Field']); $i0++)
				{
				$nazwa=$kolumny['Field'][$i0];
				$typ=$kolumny['Type'][$i0];
				$null=$kolumny['Null'][$i0]=='YES'?'NULL':'NOT NULL';
				$klucz=$kolumny['Key'][$i0];
				if($klucz=='PRI') $klucz='PRIMARY KEY';
				if($klucz=='UNI') $klucz='UNIQUE';
				if($klucz=='MUL') $klucz='';
				$domyslne=$kolumny['Default'][$i0];
				if($null=='NULL')
					{
					if($domyslne==NULL) $domyslne='DEFAULT NULL';
					elseif($domyslne == 'CURRENT_TIMESTAMP') $domyslne="DEFAULT $domyslne";
					else $domyslne="DEFAULT '$domyslne'";
					}
				elseif($domyslne == 'CURRENT_TIMESTAMP') $domyslne="DEFAULT $domyslne";
				elseif($domyslne!==NULL) $domyslne="DEFAULT '$domyslne'";
				$extra=$kolumny['Extra'][$i0];
				$sql.="`$nazwa` $typ $null $klucz $domyslne $extra,";
				}
			$sql=substr($sql, 0, -1);
			$sql.=") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

			//wykonanie zapytania
			if($this->wynik=$this->_odpytaj($sql))
				{
				$wynik=TRUE;
				}
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}
			return $wynik;
			}

		}

	/**
	 * Metoda pozwala stworzyć widok o podanej nazwie i definicji
	 *
	 * @param string $widok - nazwa widoku
	 * @param string $zapytanie - definicja widoku
	 * @return boolean
	 */
	public function dodaj_widok($widok, $zapytanie)
		{
		$this->o_raport->dodaj('raport');
		if($this->o_db)
			{
			$sql="CREATE OR REPLACE VIEW $widok AS $zapytanie";
			if($this->wynik=$this->_odpytaj($sql))
				{
				$wynik=TRUE;
				}
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}
			}
			return $wynik;
		}

	/**
	 * Metoda czyści wskazaną tabelę
	 * @param string $tabela
	 * @return bolean
	 */
	public	function wyczysc_tabela($tabela)
		{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
			{

			//tworzenie zapytania
			$sql="TRUNCATE TABLE `{$this->prefix}$tabela`;";

			//wykonanie zapytania
			if($this->wynik=$this->_odpytaj($sql)) $wynik=TRUE;
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}
			return $wynik;
			}

		}

	/**
	 * Metoda dodaje kolumne do wskazanej tabeli
	 * @param array $kolumna - parametr musi miec format zwrotu z zapytania SHOW COLUMNS jako tablica jednowymiarowa + ewentualnie dodatkowy klucz "After"
	 * @param string $tabela
	 * @return bolean
	 */
	public	function dodaj_kolumna(array $kolumna, $tabela)
		{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
			{

			//tworzenie zapytania
			$nazwa=$kolumna['Field'];
			$typ=$kolumna['Type'];
			$null=$kolumna['Null']=='YES'?'NULL':'NOT NULL';
			$klucz=$kolumna['Key'];
			if($klucz=='PRI') $klucz='PRIMARY KEY';
			if($klucz=='UNI') $klucz='UNIQUE';
			$domyslne=$kolumna['Default'];
			if($null=='NULL')
				{
				if($domyslne === NULL or $domyslne == 'NULL') $domyslne='DEFAULT NULL';
				elseif($domyslne == 'CURRENT_TIMESTAMP') $domyslne="DEFAULT $domyslne";
				else $domyslne="DEFAULT '$domyslne'";
				}
			elseif($domyslne == 'CURRENT_TIMESTAMP') $domyslne="DEFAULT $domyslne";
			elseif($domyslne !== false and $domyslne !== NULL) $domyslne="DEFAULT '$domyslne'";
			$extra=$kolumna['Extra'];
			$after=$kolumna['After']?"AFTER `$kolumna[After]`":'';
			$sql="ALTER TABLE `{$this->baza}`.`{$this->prefix}$tabela` ADD `$nazwa` $typ $null $klucz $domyslne $extra $after;";

			//wykonanie zapytania
			if($this->wynik=$this->_odpytaj($sql))
				{
				$wynik=TRUE;
				}
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}
			return $wynik;
			}

		}

	public function usun_kolumna($kolumna, $tabela)
	{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
		{
			$sql = "ALTER TABLE `{$this->prefix}$tabela` DROP COLUMN `$kolumna`";

			if($this->wynik=$this->_odpytaj($sql))
				{
				$wynik=TRUE;
				}
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}
		}

		return $wynik;
	}


	/**
	 * Metoda modyfikuje wskazaną kolumnę
	 * @param array $kolumna - parametr musi miec format zwrotu z zapytania SHOW COLUMNS jako tablica jednowymiarowa
	 * @param string $tabela
	 * @return bolean
	 */
	public	function zmien_kolumna(array $kolumna, $tabela)
		{
		$this->o_raport->dodaj('raport');

		if($this->o_db)
			{

			//tworzenie zapytania
			$nazwa=$kolumna['Field'];
			$typ=$kolumna['Type'];
			$null=$kolumna['Null']=='YES'?'NULL':'NOT NULL';
			$struktura_tabeli = $this->kolumny($tabela);
			$index = array_search($nazwa,$struktura_tabeli['Field']);
			$stary_klucz = $struktura_tabeli['Key'][$index];
			$klucz = $kolumna['Key'];
			switch ($klucz) {
				case 'PRI':
					$klucz = ($stary_klucz != 'PRI' and $stary_klucz != 'UNI' and !in_array('PRI',$struktura_tabeli['Key']))? 'PRIMARY KEY' : '';
					break;
				case 'UNI':
					$klucz = ($stary_klucz != 'PRI' and $stary_klucz != 'UNI')? 'UNIQUE' : '';
					break;
				default :
					$klucz = '';
					break;
//				case 'MUL':
//					$klucz='UNIQUE';
//					$null='NULL';
//					break;
			}
			$domyslne=$kolumna['Default'];
			if($null=='NULL')
				{
				if($domyslne==NULL) $domyslne='DEFAULT NULL';
				else $domyslne="DEFAULT '$domyslne'";
				}
			elseif($domyslne or $domyslne=='0') $domyslne="DEFAULT '$domyslne'";
			$extra=$kolumna['Extra'];
			$sql="ALTER TABLE `{$this->baza}`.`{$this->prefix}$tabela` CHANGE `$nazwa` `$nazwa` $typ $null $klucz $domyslne $extra;";

			//wykonanie zapytania
			if($this->wynik=$this->_odpytaj($sql)) $wynik=TRUE;
			else
				{
				$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				$wynik=FALSE;
				}
			return $wynik;
			}

		}


	/**
	 * Metoda zwraca status wybranej tabeli, lub wszystkich tabel, jeśli nie wskazano jednej
	 * @param string $tabela
	 * @return mixed false lub tablica
	 */
	public function tabela_status($tabela){
		if($this->o_db)
		{
			if($tabela)
			{
					//dla pojedynczej tabeli
//				$sql = "OPTIMIZE TABLE `{$this->prefix}$tabela`";	//bo przy wielkich tabelach trwa to za dlugo
				$this->_odpytaj($sql);
				$wynik = $this->wiersz_bezposrednie("SHOW TABLE STATUS FROM `{$this->baza}` LIKE '{$this->prefix}$tabela'");
			}
			else
			{
					//dla kompletu tabel
				$tabele = $this->tabele();
//				if(!empty($tabele) and is_array($tabele)) foreach($tabele as $nazwa_tabeli) $this->_odpytaj("OPTIMIZE TABLE `{$this->prefix}$nazwa_tabeli`");	//bo przy wielkich tabelach trwa to za dlugo
				$wynik = $this->tabela_bezposrednie("SHOW TABLE STATUS FROM `{$this->baza}`");
			}
			return $wynik;
		}
	}

	public function struktura_eksportuj($tabela)
	{
		$this->o_raport->dodaj('raport');
		if($this->o_db)
		{
			if(!$wynik=$this->kolumny($tabela))
			{
				$this->error = "Klasa Db: Błąd zapytania.";
				$this->o_raport->dodaj('error', null, $this->error);
				$wynik = false;
			}

			return $wynik;
		}
	}

	public function struktura_importuj($dane, $tabela)
	{
		$this->o_raport->dodaj('raport');
		if($this->o_db and $this->struktura_tabeli=$dane)
		{
			$wynik = true;
			//sprawdzenie czy tabela istnieje
			$sql = "SHOW TABLES FROM `{$this->baza}` LIKE '{$this->prefix}$tabela'";
			if($this->komorka_bezposrednie($sql)){
				//pobranie struktury tabeli docelowej
				if($struktura_celu=$this->kolumny($tabela))
				{
//					cms::var_dump($struktura_celu, 'cel');
					//kopiowanie brakujących kolumn i aktualizacja struktury istniejących
					foreach($this->struktura_tabeli['Field'] as $n0 => $nazwa)
					{
						$kolumna = array();
						foreach($this->struktura_tabeli as $pole => $dane)
						{
							$kolumna[$pole] = $dane[$n0];
						}

						$pozycja = array_search($nazwa,$struktura_celu['Field']);
//						Echo "$nazwa: '$pozycja' <br/>";
						if( $pozycja === false)
						{
//							echo 'dodaj <br/>';
							$this->dodaj_kolumna($kolumna, $tabela);
						}
						else//dodaj kolumnę
						{
//							echo 'aktualizuj <br/>';
							$this->zmien_kolumna($kolumna, $tabela);
						}
					}
					//usuwanie nadmiarowych kolumn
					foreach($struktura_celu['Field'] as $n0 => $nazwa)
					{
						if(!in_array($nazwa,$this->struktura_tabeli['Field']))
							$this->usun_kolumna($kolumna, $tabela);
					}
				}
				else{
					$this->error = "Klasa Db: Błąd wczytania struktury tabeli docelowej.";
					$this->o_raport->dodaj('error', null, $this->error);
					$wynik = false;
				}
			}
			else //tworzy tabelę
			{
				$this->dodaj_tabela($this->struktura_tabeli, $tabela);
			}

		}
		elseif(!$this->struktura_tabeli)
		{
			$this->error = "Klasa Db: Brak struktury tabeli do zaimportowania.";
			$this->o_raport->dodaj('error', null, $this->error);
			$wynik = false;
		}

		return $wynik;
	}

	public function dane_eksportuj($tabela, $ilosc=null)
	{
		$this->o_raport->dodaj('raport');
		if($this->o_db)
		{
			$warunki = '';//(!empty($ilosc) and is_int($ilosc))? "LIMIT $ilosc " : '';
			$this->tabela_xy = false;
			if(!$wynik=$this->tabela_bezposrednie("SELECT * FROM `{$this->prefix}$tabela` $warunki"))
			{
				$this->error=$this->o_raport->dodaj('error', null, $this->error);
				$wynik = false;
			}

			return $wynik;
		}
	}

	public function dane_importuj($dane, $tabela, $wyczysc = false)
	{
		$this->o_raport->dodaj('raport');
		if($this->o_db and $this->rekordy=$dane)
		{

			if($wyczysc)
			{
				$sql = "TRUNCATE TABLE `{$this->prefix}$tabela`";
				if(!$this->wynik=$this->_odpytaj($sql))
				{
					$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
					return FALSE;
				}
			}

			foreach($this->rekordy as $dane)
			{
				$this->error = null;
				if(!$wyczysc) $id = array_shift($dane);
				if(!$this->dodaj_rekord($dane, $tabela))
				{
					$this->o_raport->dodaj('error', null, $this->_blad_obsluz().' Zapytanie: '.$sql);
				}
				if($this->error === null)
					$wynik = true;
				else
					$wynik = false;
			}
//			$sql = "OPTIMIZE TABLE `{$this->prefix}$tabela`";
//			if(!$this->wynik=$this->_odpytaj($sql))
//			{
//				$this->error="Klasa Db: ".mysql_error ($this->o_db);
//				$this->o_raport->dodaj('error', null, $this->error.' Zapytanie: '.$sql);
//				return FALSE;
//			}
		}
		elseif(!$this->rekordy)
		{
			$this->error = "Klasa Db: Brak rekordów do eksportu.";
			$this->o_raport->dodaj('error', null, $this->error);
			$wynik = false;
		}

		return $wynik;
	}

}

?>
