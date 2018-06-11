<?php

class uzytki{

	function czas_relatywny_generuj($czas, $czas2 = null, $poligloto = false, $str_przerwa = '', $literki = null, $odmiana = false)
	{
	//~ funkcja zwraca czas w postaci stringa z opisem (minut, sekund, itp)
	//~ $czas = time() //jako liczba, czas będzie mierzony do teraz, chyba że ustalony będzie $czas2 (wtedy różnica będzie od tych dwóch czasów)
	//~ $str_przerwa to znak np ' ' między liczbą a opisem(minut, itp)
	//~ $literki (jeśli ustalone zamiast opisu poligloto będzie wyświetlała się tylko jedna literka ze zmiennej $standardowe_literki),
	//~ jeśli $literki to tablica o indeksach takich jak tablica $standardowe_literki to będzie brana z $literki (możemy zamienić np 'g'=>'h' (godzin na hours))
	//~ jeśli poligloto = true to klucze poligloto muszą być w bazie(w przypadku kiedy nie używamy pojedyńczych literek). jeśli nie będzie klucza w poligloto to (pusty tekst)
	//~ klucze poligloto są niżej w tablicy $przedziały
	//~ jeśli odmiana jest false to pobiera z poligloto tylko 3cią formę

		$standardowe_literki = array('sek'=>'s', 'min'=>'m', 'hour'=>'h', 'day'=>'d', 'mon'=>'m', 'year'=>'y');
		if(!is_array($literki)) $literki = $standardowe_literki;
		if(!isset($czas2)) $czas2 = time();
		if(is_string($czas) and !ctype_digit($czas)) $czas = strtotime($czas);
		if(is_string($czas2) and !ctype_digit($czas)) $czas2 = strtotime($czas2);

		$sek = intval(abs($czas2 - $czas)); //różnica czasu

		if (!function_exists('odmiana'))
		{
			function odmiana($index, $val, $odmien){
				$przedzialy = array(
					'sek'  => array('[[pg|czas_sekunda]]', '[[pg|czas_sekundy]]', '[[pg|czas_sekund]]'),
					'min'  => array('[[pg|czas_minuta]]', '[[pg|czas_minuty]]', '[[pg|czas_minut]]'),
					'hour' => array('[[pg|czas_godzina]]', '[[pg|czas_godziny]]', '[[pg|czas_godzin]]'),
					'day'  => array('[[pg|czas_dzien]]', '[[pg|czas_dni]]', '[[pg|czas_dni]]'),
					'mon'  => array('[[pg|czas_miesiac]]', '[[pg|czas_miesiace]]', '[[pg|czas_miesiecy]]'),
					'year' => array('[[pg|czas_rok]]', '[[pg|czas_lata]]', '[[pg|czas_lat]]'),
				);

				if(!$odmien) //zwraca tylko 3 forme
					return $przedzialy[$index][2];

				if($val == 1)
					return $przedzialy[$index][0]; else
					if($val < 5 || ($val >= 22 && $val%10 < 5 && $val%10 != 0))
						return $przedzialy[$index][1]; else
						return $przedzialy[$index][2];
			}
		}

		$dzielniki = array('sek'=>60, 'min'=>60, 'hour'=>24, 'day'=>30, 'mon'=>12, 'year'=>1000);

		foreach($dzielniki as $index => $d)
			if($sek < $d)
			{
				$opis = $poligloto ? odmiana($index, $sek, $odmiana) : (is_array($literki) && isset($literki[$index]) ? $literki[$index] : $standardowe_literki[$index]);
				return $sek.$str_przerwa.$opis;

			} else $sek = (int)($sek / $d);

		return false;
	}

	public	static function var_dump($zmienna, $opis='')
	{
		echo "$opis <br /><pre>".var_export($zmienna, TRUE)."</pre>";
	}

	public	static function array_wzor($tablica_dane, $szablon)
	{
		//funkcja przetwarza i zwraca tablice wg podanego wzoru... do podmiany [$klucz]

		$wynik = array();
		if(!empty($tablica_dane) and is_array($tablica_dane)) foreach($tablica_dane as $rekord)
			{
			$tablica_z = array();
			$tablica_do = array();

			if(!empty($rekord) and is_array($rekord))foreach($rekord as $klucz=>$wartosc)
				{
				$tablica_z[] = "[$klucz]";
				$tablica_do[] = $wartosc;
				}
				if(!empty($tablica_z))
					$wynik[] = str_replace($tablica_z,$tablica_do,$szablon);
			}

		return $wynik;

	}

	public	static function tablica_odwroc($dane)
	{
		if(empty($dane) or !is_array($dane))
			return NULL;

		$wynik = array();

		foreach($dane as $x=>$linia)
			if(is_array($linia))
				foreach($linia as $y=>$wartosc)
					$wynik[$y][$x]=$wartosc;

		return $wynik;
	}

	public static function tablica_sortuj(array $tablica, $klucz_sortowania)
	{
		//funkcja sortuje tablicę wielowymiarową po wartościach w podanym kluczu podtablicy
		foreach($tablica as $klucz=>$wiersz)
		{
			$wartosci[]=$wiersz[$klucz_sortowania];		//w liscie $wartosci trzymamy wartosci wg ktorych sortujemy
		}

		array_multisort($wartosci, $tablica);

		return $tablica;
	}

	public static function float_rand($Min, $Max, $round=0)
	{
		//funkcja liczy rand miedzy floatami

	    //validate input
	    if ($Min>$Max) { $min=$Max; $max=$Min; }
	        else { $min=$Min; $max=$Max; }

	    $randomfloat = $min + mt_rand() / mt_getrandmax() * ($max - $min);
	    if($round>0)
	        $randomfloat = round($randomfloat,$round);

	    return $randomfloat;
	}

	public static function numer_formatuj($numer, $znaczace, $round=NULL)
	{
		//NIE UZYWAC NIE DZIALA!
		//funkcja formatuje numer tak, by mial odpowiednia dlugosc wzgledem swojej wartosci
		//liczby z duza iloscia cyfr przed i po przecinku zostana zredukowane w dlugosci
		//liczby z duza iloscia cyfr tylko przed przecinkiem nie zostana zmienione
		//liczby z duza iloscia cyfr po przecinku zostana zredukowane do cyfr znaczacych

		$numer=number_format($numer, 14); //mozna sobie zmieniac (ta liczba to max cyfr po przecinku jakie beda brane pod uwage w tej funkcji)

		$t=explode('.', $numer);
		$przed=$t[0];	//przed przecinkiem
		$po=$t[1];		//po przecinku

		if((!$po) || (strlen($przed)>=$znaczace)) $wynik=$przed;
		else
		{
			$znaczace-=strlen($przed);	//ustalanie ilosci cyfr znaczacych po przecinku z uwzglednieniem tych z przed przecinka
			if($przed==0) $znaczace++;

			$dlugosc=strlen($po);

			for($i0=0; $i0<$dlugosc; $i0++)
			{
				if($po[$i0]!=0)
				{
					$pozycja=$i0;
					break;
				}
			}

			$wynik="$przed.".substr($po, 0, $pozycja+$znaczace);
		}

/*		if($round)
		{
			if($round>$pozycja+$znaczace) $wynik=number_format($wynik, $pozycja+$znaczace, '.', '');
			else $wynik=number_format($wynik, $round, '.', '');
		}
		else $wynik=number_format($wynik, $pozycja+$znaczace, '.', '');
*/
		$wynik=number_format($wynik, $pozycja+$znaczace, '.', '');

//		var_dump($wynik, $po, $pozycja); exit();
		return $wynik;
	}

	public static function shuffle($array)
	{
		//funkcja shuffluje tablice, ale pamieta jej klucze

		$orig = array_flip($array);
	    shuffle($array);
	    foreach($array AS $key=>$n)
	    {
	        $data[$n] = $orig[$n];
	    }
	    return array_flip($data);
	}

	public static function pomiedzy($wartosc, $min, $max)
	{
		//funkcja zwraca true/false w zaleznosci od tego czy $wartosc jest pomiedzy min i max

		if((($wartosc==$max) && ($wartosc==$min)) || (($max>$wartosc) && ($wartosc>$min)) || (($max<$wartosc) &&($wartosc<$min))) return true;
		else return false;
	}

	public static function db2option($o_db, $tabela, $kolumna_klucz, $kolumna_wartosc, $warunek, $selected=NULL, $atrybuty=NULL)
	{
		//funkcja zwraca <option> wzgledem danych z bazy, w selected podajemy klucz opcji, ktora ma byc domyslna

		$db=$o_db->kolumna_klucz($kolumna_klucz, $kolumna_wartosc, $warunek, $tabela);
		$wynik='';
		if(isset($db)) foreach($db as $klucz=>$wartosc)	if($klucz!='')
		{
			if($klucz==$selected) $wynik.="<option $atrybuty value=\"$klucz\" selected=\"selected\">$wartosc</option>";
			else $wynik.="<option $atrybuty value=\"$klucz\">$wartosc</option>";
		}

		return $wynik;
	}

	public static function db2select($o_db, $tabela, $kolumna_klucz, $kolumna_wartosc, $warunek, $selected=NULL, $atrybuty=NULL, $atrybuty_option=NULL)
	{
		//funkcja zwraca <option> wzgledem danych z bazy

		$wynik="<select $atrybuty>";
		$wynik.=self::db2option($o_db, $tabela, $kolumna_klucz, $kolumna_wartosc, $warunek, $selected, $atrybuty_option);
		$wynik.='</select>';

		return $wynik;
	}
}
?>
