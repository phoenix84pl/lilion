<?php
class phoenixkod
{
	//wersja 20170723 - dodanie aliasu pk (działa i pk i phoenixkod)
	//wersja 20170314 - +f e/@
	//wersja 20170313 - +f d, dt, dtl
	//wersja 20170124 - poprawki
	//wersja 20170123 - powstanie klasy

	//$o_pk->przetworz($tresc);	- sposob dzialania

	private $_o_db;		//znacznik bazy

	public function __construct($o_db)
	{
			//konstruktor - zwraca przetworzony pk
		$this->_o_db=$o_db;

	}

	private	function _zakoduj($string, $klucz=NULL)
	{
			//huj wie czemu akurat tak, ale dziala, wiec nie zmieniamy
		for($i=0; $i<strlen($string); $i++)
		{
			for($j=0; $j<strlen($klucz); $j++)
			{
				$string[$i] = $string[$i]^$klucz[$j];
			}
		}
		return $string;
	}

	private	function _odkoduj($string, $klucz=NULL)
	{
			//funkcja odwrotna do powyzszej
		for($i=0; $i<strlen($string); $i++)
		{
			for($j=0; $j<strlen($klucz); $j++)
			{
				$string[$i] = $klucz[$j]^$string[$i];
			}
		}
		return $string;
	}

	private function _ignorowane_zakoduj($tresc)
	{
		//koduje wnetrze ignorowanej czesci, zeby nie bylo tam przetworzen

		$l_ignorowanie=explode('{!{', $tresc);
		$wynik='';
		$n0=0;
		foreach($l_ignorowanie as $wartosc)
		{
			if($n0>0)
			{
				$koniec=strpos($wartosc, '}!}');
				$wynik.='{!{'.$this->_zakoduj(substr($wartosc, 0, $koniec), 'phoenixkod').'}!}';
				$wynik.=substr($wartosc, $koniec+3);
			}
			else $wynik.=$wartosc;
			$n0++;
		}

		return $wynik;

	}
	private function _ignorowane_odkoduj($tresc)
	{
		//odkodowuje wnetrze ignorowanej czesci, zeby nie bylo tam przetworzen

		$l_ignorowanie=explode('{!{', $tresc);
		$wynik='';
		$n0=0;
		foreach($l_ignorowanie as $wartosc)
		{
			if($n0>0)
			{
				$koniec=strpos($wartosc, '}!}');
				$wynik.='{!{'.$this->_odkoduj(substr($wartosc, 0, $koniec), 'phoenixkod').'}!}';
				$wynik.=substr($wartosc, $koniec+3);
			}
			else $wynik.=$wartosc;
			$n0++;
		}

		return $wynik;

	}

	private function _cytaty_przetworz($tresc)
	{
		//funkcja przetwarza cytaty. UWAGA! Cytat po przetworzeniu czesto staje sie normalnym phoenixkodem, wiec rekurencja nie zadziala. Jak chcesz cos zachowac rekurencyjnie, to uzyj znacznika ignorowania {!{ }!}

			//przetwarzanie cytatow
		while((strpos($tresc, '{{')!==FALSE) && (strpos($tresc, '{{')<strrpos($tresc, '}}')))
		{
			$pozycja_k=strpos($tresc, '}}');
			$zrodlowa=substr($tresc, 0, $pozycja_k+2);																					//tresc zrodlowa (wnetrze tresci przed przetworzeniem)
			$pozycja_p=strrpos($zrodlowa, '{{');
			$zrodlowa=substr($tresc, $pozycja_p, $pozycja_k+2-$pozycja_p);																//gotowy phoenixkod do przetworzenia

			$co=array('|', '[[', ']]', '{{', '}}', '[\[', ']\]', '{\{', '}\}');															//znaczki ktorych szukamy
			$na=array("&#124;", "&#91;&#91;", "&#93;&#93;", "", "", "&#91;\&#91;", "&#93;\&#93;", "&#123;\&#123;", "&#125;\&#125;");	//na co te znaczniki zamieniamy (z usunieciem nawiasow klamrowych)
			$przetworzona=str_replace($co, $na, $zrodlowa);																				//wnetrze cytatu zamienione
			$tresc=str_replace($zrodlowa, $przetworzona, $tresc);																		//podmieniamy w calej tresci
		}

		return $tresc;
	}

	private function _ukosniki_przetworz($tresc)
	{
		//przetwarza ukosniki zamieniajac je na encje

		$co=array("\|", "[\[", "]\]", "{\{", "}\}");
		$na=array("&#124;", "&#91;&#91;", "&#93;&#93;", "&#123;&#123;", "&#125;&#125;");
		$tresc=str_replace($co, $na, $tresc);

		return $tresc;
	}

	private function _tresc_przetworz($tresc)
	{
			//funkcja przetwarza tresc wlasciwa po uwzglednieniu ignorowanych, cytatow i ukosnikow

		while((strpos($tresc, '[[')!==FALSE) && (strpos($tresc, '[[')<strrpos($tresc, ']]')))
		{
			$pozycja_k=strpos($tresc, ']]');
			$zrodlowa=substr($tresc, 0, $pozycja_k+2);	//wyciaganie stringa na ktorym pracujemy
			$pozycja_p=strrpos($zrodlowa, '[[');
			$zrodlowa=substr($tresc, $pozycja_p, $pozycja_k+2-$pozycja_p);	//gotowy phoenixkod

			$przetworzona=$this->_phoenixkod_przetworz($zrodlowa);	//przetwarzanie komendy phoenixkod

			$tresc=str_replace($zrodlowa, $przetworzona, $tresc);
		}

		return $tresc;
	}

	public function przetworz($tresc)
	{
			//funkcja sterujaca - zwraca przetworzony pk

			//INFO: ignorowanie to to, czego w ogole nie przetwarzamy, a cytaty to taka tresc pk, ktora ma zostac wyswietlona, ale znacznik cytatu w momencie przetwarzania zostanie usuniety
			//roznica jest taka, ze znacznik cytatu zostanie usuniety przy przetwarzaniu i w kolejnej iteracji tresc juz bedzie przetworzona, a ignorowanie zostaje jak jest i zawsze bedzie olane

		$tresc=$this->_ignorowane_zakoduj($tresc);		//ignorowane - wszystkie elementy miedzy {!{ a }!} zostanie zignorowane i przywrocone do stanu sprzed przetwarzania
		$tresc=$this->_cytaty_przetworz($tresc);		//cytaty - przetwarza by wygladaly na PK, ale rekurencja spowoduje ich przetworzenie jakby juz byly PK
		$tresc=$this->_ukosniki_przetworz($tresc);		//zamiana na encje z usunieciem ukosnikow (takie nieprzetwarzane wyjatki poza cytatami)
		$tresc=$this->_tresc_przetworz($tresc);			//wlasciwe wyszukanie i przetworzenie PK
		$tresc=$this->_ignorowane_odkoduj($tresc);		//ignorowane - przywracanie

		return $tresc;
	}

	private	function _phoenixkod_przetworz($tresc)
	{
		//funkcja zwraca przetworzony phoenixkod
		//przyklad:[[komenda|argument|argument2|...]]

		if((substr($tresc, 0, 2)=='[[') && (substr($tresc, strlen($tresc)-2, 2)==']]'))
		{
			$srodek=substr($tresc, 2, strlen($tresc)-4);
			$l_argumentow=explode('|', $srodek);
			$argumenty=NULL;
			for($i0=0; $i0<count($l_argumentow); $i0++)
			{
				if($i0==0) $komenda=$l_argumentow[$i0];
				else $argumenty[$i0-1]=$l_argumentow[$i0];
			}

			if($komenda==' ') $komenda='';
			if($komenda=='&') $komenda='encja';

			$metoda='_pk_'.$komenda;
			$wynik=$this->$metoda($argumenty);							//wywolanie metody dla komendy

		}
		else $wynik=$tresc;

		return $wynik;
	}


		//metody PK


	private function _pk_($argumenty)
	{
		//funkcja generuje nielamalne spacje [[ |ilosc=null]]

		if(!isset($argumenty[0])) $argumenty[0]=1;

		$wynik='';
		for($i0=0; $i0<$argumenty[0]; $i0++) $wynik.='&nbsp;';

		return $wynik;
	}

	private function _pk_encja($argumenty)
	{
		//funkcja generuje przydatne unikody za pomoca encji [[&/encja|kod]]

		switch($argumenty[0])
		{
			case 'v': $wynik='&#10004;'; break;
			case 'x': $wynik='&#10008;'; break;
			case '->': $wynik='&#8594;'; break;
			case '<-': $wynik='&#8592;'; break;
			case '*': $wynik='&#215;'; break;
			case '.': $wynik='&#8729;'; break;
			case '-': $wynik='&#8722;'; break;
			case '/': $wynik='&#247;'; break;
			case '<<': $wynik='&#171;'; break;
			case '>>': $wynik='&#187;'; break;
			case ',,': $wynik='&#132;'; break;
			case "''": $wynik='&#148;'; break;
			default: $wynik=$argumenty[0];
		}

		return $wynik;
	}

	private function _pk_arg($argumenty)
	{
		//funkcja zwraca wartosc zmiennej [[arg|zmienna]]

		return $_REQUEST[$argumenty[0]];
	}

	private function _pk_br($argumenty)
	{
		//funkcja zwraca X br [[br|ilosc]]

		if(!isset($argumenty[0])) $argumenty[0]=1;

		$wynik='';
		for($i0=0; $i0<$argumenty[0]; $i0++) $wynik.='<br />';

		return $wynik;
	}

	private function _pk_f($argumenty)
	{
		//funkcja obsluguje formularze poprzez flagi [[f|flaga|klasy i inne znaczniki|dalsze argumenty...]]

		if(empty($argumenty[0])) $argumenty[0]="";

			//sprawdzamy czy w argumencie 1 sa klasy czy znaczniki
		if(isset($argumenty[1]))
		{
			if(strpos($argumenty[1], '"')===FALSE) $argumenty[1]="class=\"{$argumenty[1]}\"";		//jesli klasy to dodajemy znacznik klasy, jesli sa znaczniki to je zostawiamy
		}
		else $argumenty[1]=NULL;


		$wynik=NULL;

		switch($argumenty[0])
		{
			case '':
			{
					//otwieranie formularza [[f]] lub [[f||klasa|link]]
				if(isset($argumenty[2]))
				{
					if(strpos($argumenty[2], '?')===0) $argumenty[2]='.'.$argumenty[2];		//jeśli pierwszy znak jest pytajnikiem to dodaj wcześniej kropkę (bo czasem mu sie pierdzili na linkach bez tej kropki)
				}

				$wynik="<form method=\"post\"";
				if(isset($argumenty[2])) $wynik.=" action=\"{$argumenty[2]}\"";
				$wynik.=" enctype=\"multipart/form-data\"";
				if(isset($argumenty[1])) $wynik.=" {$argumenty[1]}";
				$wynik.=">";

				break;
			}

			case '/':
			{
					//zamykanie formularza	[[f|/]]
				$wynik="</form>";

				break;
			}

			case 'b':
			{
					//button			[[f|b|class="button" onClick="xxx"|wyslij|Wyślij]]
				$wynik="<input type=\"button\"";
				if(isset($argumenty[2])) $wynik.=" name=\"{$argumenty[2]}\"";
				if(isset($argumenty[3])) $wynik.=" value=\"{$argumenty[3]}\"";
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			case 'c':
			{
					//color
				break;
			}

			case 'cb':
			{
					//checkbox				[[f|c|formularz|klucz|wartosc|1]]
				$wynik="<input type=\"checkbox\"";
				if(isset($argumenty[2])) $wynik.=" name=\"{$argumenty[2]}\"";
				if(isset($argumenty[3])) $wynik.=" value=\"{$argumenty[3]}\"";
				if(isset($argumenty[4]) || isset($_SESSION['post'][$argumenty[2]])) $wynik.=" checked";
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			case 'd':
			{
					//d - date				[[f|d|formularz|klucz|wartosc]]

				$wynik="<input type=\"date\"";
				if(isset($argumenty[2])) $wynik.=" name=\"{$argumenty[2]}\"";
				if(isset($argumenty[3])) $wynik.=" value=\"{$argumenty[3]}\"";
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			case 'dt':
			{
					//dt - datetime			[[f|dt|formularz|klucz|wartosc]]

				$wynik="<input type=\"datetime\"";
				if(isset($argumenty[2])) $wynik.=" name=\"{$argumenty[2]}\"";
				if(isset($argumenty[3])) $wynik.=" value=\"{$argumenty[3]}\"";
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			case 'dtl':
			{
					//dtl - datetimelocal	[[f|dtl|formularz|klucz|wartosc]]

				$wynik="<input type=\"datetimelocal\"";
				if(isset($argumenty[2])) $wynik.=" name=\"{$argumenty[2]}\"";
				if(isset($argumenty[3])) $wynik.=" value=\"{$argumenty[3]}\"";
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			case 'e':
			case '@':
			{
					//text					[[f|e|placeholder="tekst"|klucz|wartosc]]
				$wynik="<input type=\"email\"";
				if(isset($argumenty[2])) $wynik.=" name=\"{$argumenty[2]}\"";
				if(isset($argumenty[3])) $wynik.=" value=\"{$argumenty[3]}\"";
				elseif(isset($argumenty[2]) && isset($_SESSION['cms']['post'])) $wynik.=" value=\"{$_SESSION['cms']['post'][$argumenty[2]]}\"";	//przypisz to co w name bylo
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			case 'f':
			{
					//plik					[[f|f|formularz|plik]]
				$wynik="<input type=\"file\"";
				if(isset($argumenty[2])) $wynik.=" name=\"{$argumenty[2]}\"";
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			case 'h':
			{
					//hidden				[[f|h|formularz|klucz|wartosc]]

				$wynik="<input type=\"hidden\"";
				if(isset($argumenty[2])) $wynik.=" name=\"{$argumenty[2]}\"";
				if(isset($argumenty[3])) $wynik.=" value=\"{$argumenty[3]}\"";
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			//m - month

			case 'n':
			{
					//number					[[f|n|placeholder="tekst"|klucz|wartosc]]
				$wynik="<input type=\"number\"";
				if(isset($argumenty[2])) $wynik.=" name=\"{$argumenty[2]}\"";
				if(isset($argumenty[3])) $wynik.=" value=\"{$argumenty[3]}\"";
				elseif(isset($argumenty[2]) && isset($_SESSION['cms']['post'])) $wynik.=" value=\"{$_SESSION['cms']['post'][$argumenty[2]]}\"";	//przypisz to co w name bylo
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			case 'p':
			{
					//password				[[f|p|placeholder="haslo"|klucz|wartosc]]
				$wynik="<input type=\"password\"";
				if(isset($argumenty[2])) $wynik.=" name=\"{$argumenty[2]}\"";
				if(isset($argumenty[3])) $wynik.=" value=\"{$argumenty[3]}\"";
				elseif(isset($_SESSION['cms']['post'])) $wynik.=" value=\"{$_SESSION['cms']['post'][$argumenty[2]]}\"";	//przypisz to co w name bylo
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			//r - range

			case 'rb':
			{
					//radio	button				[[f|r|formularz|klucz|wartosc|1]]
				$wynik="<input type=\"radio\"";
				if ($argumenty[2]) $wynik.=" name=\"{$argumenty[2]}\"";
				if ($argumenty[3]) $wynik.=" value=\"{$argumenty[3]}\"";
				if ($argumenty[4]) $wynik.=" checked";
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			case 's':
			{
					//select				[[f|s|formularz|nazwa|klucz=wartosc&klucz=wartosc|1]]

				$wynik="<select name=\"{$argumenty[2]}\" {$argumenty[1]}>";
				if(isset($argumenty[3]))
				{
					$d1=explode('&', $argumenty[3]);
					if($d1) foreach($d1 as $wartosc)
					{
						$d2=explode('=', $wartosc);
						if(isset($argumenty[4])) $selected=$d2[0]==$argumenty[4]?'selected':'';
						elseif(isset($_SESSION['post'])) $selected=$d2[0]==$_SESSION['post'][$argumenty[2]]?'selected':'';
						else $selected='';

						$tresc=urldecode($d2[1]);
						$wynik.="<option value=\"{$d2[0]}\" $selected> $tresc </option>";
					}
				}
				$wynik.="</select>";

				break;
			}

			case 'sm':
			{
					//submit				[[f|sm|style="xxx"|wyslij|Wyślij]]
				$wynik="<input type=\"submit\"";
				if(isset($argumenty[2])) $wynik.=" name=\"{$argumenty[2]}\"";
				if(isset($argumenty[3])) $wynik.=" value=\"{$argumenty[3]}\"";
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			//src - search

			case 't':
			{
					//text					[[f|t|placeholder="tekst"|klucz|wartosc]]
				$wynik="<input type=\"text\"";
				if(isset($argumenty[2])) $wynik.=" name=\"{$argumenty[2]}\"";
				if(isset($argumenty[3])) $wynik.=" value=\"{$argumenty[3]}\"";
				elseif(isset($argumenty[2]) && isset($_SESSION['cms']['post'])) $wynik.=" value=\"{$_SESSION['cms']['post'][$argumenty[2]]}\"";	//przypisz to co w name bylo
				$wynik.=" {$argumenty[1]}>";

				break;
			}

			//tel - tel
			//time - time
			//u - url
			//w - week
		}

		return $wynik;
	}

	private function _pk_l($argumenty)
	{
		//funkcja generuje linki [[l|cel=null|alias=null|klasy=null|dodatki=null]]

		$wynik="<a";
		if(isset($argumenty[0])) $wynik.=" href=\"{$argumenty[0]}\"";
		if(isset($argumenty[2])) $wynik.=" class=\"{$argumenty[2]}\"";
		if(isset($argumenty[3])) $wynik.=" {$argumenty[3]}";
		$wynik.=">";
		if(isset($argumenty[1])) $wynik.=$argumenty[1];
		elseif(isset($argumenty[0])) $wynik.=$argumenty[0];
		$wynik.="</a>";

		return $wynik;
	}
}

class pk extends phoenixkod
{
	//alias phoenixkod
}

/*
fta - formularz textarea   [[nazwa|wartosc|x|y|parametry="class=\"formularz\"]]
ftav - textarea wypełniane głosem [[nazwa|wartosc|x|y|parametry]]
fss - formularz superselect [[nazwa|atrybuty='class="formularz"'|selected|id1=wartosc1&idn=wartoscn]]
fo - formularz obraz [[nazwa|wartosc|sciezka|tytul|atrybuty]]
fa - okno ayah do formularza
l  - link (adres, tekst)
ljs - link javascript [[funkcja js|tekst]]
la - link artykul [[id watki|tekst]]
lp - link podstrona [[podstrona&parametry|tekst|parametry]]
ls - link skrypt [[skrypt&parametry|tekst]]
lf - link formularz [[adres|tekst|[klasa=formularz]]]
ln - link nowe okno [[adres|tekst]]
lnf - link nowy formularz [[strona, tekst, [klasa=formularz]]]
lpf - link podstrona formularz [[podstrona&parametry, tekst, [klasa=formularz], parametry]]
lsf - link skrypt formularz [[skrypt&parametry, tekst, [klasa=formularz]]]
lpn - link podstrina nowe okno [[podstrona|tekst]]
lsn - link skrypt nowe okno [[skrypt|tekst]]
nl - [[nl|ile]]
nl2br - nl2br [[tekst]]
o  - obraz [[adres|tytul|parametry]]
oi  - obraz ikona[[adres|tytul|parametry]]
of  - obraz flaga [[adres|tytul|parametry]]
og  - grafika lilion [[?grafika=|tytul|parametry]]
oa  - obraz awatar [[u_id|tytul|parametry]]
oz  - obraz zdjecie [[u_id|tytul|parametry]]
tt - tooltip [[klucz1=tekst1&klucz2=tekst2|szablon]]
u - uzytkownik [[klucz]]
var - zmienna [[zmienna|element tablicy=NULL]] (poliglotoaktywna) - generuje wartosc zmiennej

			elseif($typ=="fss")
			{
//				if ($argumenty[1]=='') $argumenty[1]='class="formularz"';
				$wynik="<select name=\"$argumenty[0]\" class=\"superselect\" id=\"superselect_$argumenty[0]_rdzen\" $argumenty[1]>";
				$d1=explode('&', $argumenty[3]);
				if($d1) foreach($d1 as $wartosc)
				{
					$d2=explode('=', $wartosc);
					if($argumenty[2]) $selected=urldecode($d2[0])==$argumenty[2]?'selected':'';
					else $selected=urldecode($d2[0])==$_SESSION['post'][$argumenty[0]]?'selected':'';
					$id=urldecode($d2[0]);
					$wynik.="<option value=\"$id\" $selected></option>";
				}
				$wynik.="</select>";

				$wynik.="<div class=\"superselect\" id=\"superselect_$argumenty[0]_lista\"><span></span><ul style=\"display: none;\">";

				$d1=explode('&', $argumenty[3]);
				if($d1) foreach($d1 as $wartosc)
				{
					$d2=explode('=', $wartosc);
					$tekst=urldecode($d2[1]);
					$wynik.="<li>$tekst</li>";
				}

				$wynik.="</ul></div>
				<script>
				$(function(){
				var select = document.getElementById('superselect_$argumenty[0]_rdzen');
				var list = document.getElementById('superselect_$argumenty[0]_lista');
				superselect( select,list );
				});
				</script>";
			}
			elseif ($typ=="fta")
			{
				$wynik="<textarea";
				if ($argumenty[0]) $wynik.=" name=\"$argumenty[0]\" id=\"$argumenty[0]\"";
				if ($argumenty[2]) $wynik.=" cols=\"$argumenty[2]\"";
				if ($argumenty[3]) $wynik.=" rows=\"$argumenty[3]\"";

				if($argumenty[1]) $wynik.=" $argumenty[4] wrap=\"off\">$argumenty[1]</textarea>";
				else $wynik.=" $argumenty[4] wrap=\"off\">{$_SESSION['post'][$argumenty[0]]}</textarea>";
			}

			elseif ($typ=="ftav") //text area wypełniane głosem
			{
				$wynik="<div style=\"position: relative; width: $argumenty[2]ex\"><textarea";
				if ($argumenty[0]) $wynik.=" name=\"$argumenty[0]\" id=\"$argumenty[0]\"";
				if ($argumenty[2]) $wynik.=" cols=\"$argumenty[2]\"";
				if ($argumenty[3]) $wynik.=" rows=\"$argumenty[3]\"";
//<input  x-webkit-speech id=\"tx_{$argumenty[0]}\" class=\"tx_speech_hidden\" $argumenty[4]/>

				if($argumenty[1]) $wynik.=" $argumenty[4] style=\"width: 100%\" wrap=\"on\">$argumenty[1]</textarea>";
				else $wynik.=" $argumenty[4] style=\"width: 100%\" wrap=\"on\">{$_SESSION['post'][$argumenty[0]]}</textarea>";

				$wynik.="
					<input onwebkitspeechchange=\"transcribe(this.value, '$argumenty[0]', 'tx_{$argumenty[0]}')\"  x-webkit-speech id=\"tx_{$argumenty[0]}\" class=\"tx_speech_hidden\" $argumenty[4]/></div>
					<script>
						function transcribe(words,ta_id, t_id) {
							var txa = document.getElementById(ta_id);
							var tx = document.getElementById('t_id');

							txa_val = txa.value;
							if (txa.selectionStart) {
								var startPos = txa.selectionStart;
								var endPos = txa.selectionEnd;
								txa.value = txa.value.substring(0, startPos) + ' ' + words + ' ' + txa.value.substring(endPos, txa.value.length);
							} else {
								txa.value = txa_val + ' ' + words;
							}
						}

					</script>
				";
			}

			elseif ($typ=="ftav2") //text area wypełniane głosem
			{
				$wynik="<div style=\"position: relative; width: $argumenty[2]ex\"><textarea";
				if ($argumenty[0]) $wynik.=" name=\"$argumenty[0]\" id=\"$argumenty[0]\"";
				if ($argumenty[2]) $wynik.=" cols=\"$argumenty[2]\"";
				if ($argumenty[3]) $wynik.=" rows=\"$argumenty[3]\"";
//		onwebkitspeechchange="webkitSpeechChange(this);"
//						function webkitSpeechChange(input) {
//							input.value = input.value.replace('<?php echo $this->__('Search entire store here...') ', '');
//						}
				$wynik.=" $argumenty[4] style=\"width: 100%\" wrap=\"off\">$argumenty[1]</textarea>
					<div id=\"mic-$argumenty[0]\" class=\"tx_speech_hidden speech-mic\">  </div>
					</div>
					<script>
					(function($) {
						$(document).ready(function() {
							var textArea = $(\"#$argumenty[0]\");

							try {
								var recognition = new webkitSpeechRecognition();
							} catch(e) {
								var recognition = Object;
							}
							recognition.continuous = true;
							recognition.interimResults = true;

							var interimResult = '';

							$('#mic-{$argumenty[0]}').click(function(){
								if($(this).is('.speech-mic')){
									startRecognition();
								}
								else{
									recognition.stop();
								}
							});

//							$('.speech-mic').click(function(){
//								startRecognition();
//							});
//
//							$('.speech-mic-works').on(\"click\",function(){
//								recognition.stop();
//							});

							var startRecognition = function() {
								$('#mic-{$argumenty[0]}').removeClass('speech-mic').addClass('speech-mic-works');
								//var textArea = $('#{$argumenty[0]}');
								textArea.focus();
								recognition.start();
							};

							recognition.onresult = function (event) {
								//var textArea = $('#{$argumenty[0]}');
								var textAreaID = '$argumenty[0]';
								var pos = textArea.getCursorPosition() - interimResult.length;
								textArea.val(textArea.val().replace(interimResult, ''));
								interimResult = '';
								textArea.setCursorPosition(pos);
								for (var i = event.resultIndex; i < event.results.length; ++i) {
									if (event.results[i].isFinal) {
										insertAtCaret(textAreaID, event.results[i][0].transcript);
									} else {
										isFinished = false;
										insertAtCaret(textAreaID, event.results[i][0].transcript + '\u200B');
										interimResult += event.results[i][0].transcript + '\u200B';
									}
								}
							};

							recognition.onend = function() {
								$('#mic-$argumenty[0]').removeClass('speech-mic-works').addClass('speech-mic');
							};
						});

						insertAtCaret = function(areaId,text) {
							var txtarea = document.getElementById(areaId);
							var scrollPos = txtarea.scrollTop;
							var strPos = 0;
							var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
								'ff' : (document.selection ? 'ie' : false ) );
							if (br == 'ie') {
								txtarea.focus();
								var range = document.selection.createRange();
								range.moveStart ('character', -txtarea.value.length);
								strPos = range.text.length;
							}
							else if (br == 'ff') strPos = txtarea.selectionStart;

							var front = (txtarea.value).substring(0,strPos);
							var back = (txtarea.value).substring(strPos,txtarea.value.length);
							txtarea.value=front+text+back;
							strPos = strPos + text.length;
							if (br == 'ie') {
								txtarea.focus();
								range = document.selection.createRange();
								range.moveStart ('character', -txtarea.value.length);
								range.moveStart ('character', strPos);
								range.moveEnd ('character', 0);
								range.select();
							}
							else if (br == 'ff') {
								txtarea.selectionStart = strPos;
								txtarea.selectionEnd = strPos;
								txtarea.focus();
							}
							txtarea.scrollTop = scrollPos;
						};

						$.fn.getCursorPosition = function() {
							var el = $(this).get(0);
							var pos = 0;
							if('selectionStart' in el) {
								pos = el.selectionStart;
							} else if('selection' in document) {
								el.focus();
								var Sel = document.selection.createRange();
								var SelLength = document.selection.createRange().text.length;
								Sel.moveStart('character', -el.value.length);
								pos = Sel.text.length - SelLength;
							}
							return pos;
						};

						$.fn.setCursorPosition = function(pos) {
							if ($(this).get(0).setSelectionRange) {
								$(this).get(0).setSelectionRange(pos, pos);
							} else if ($(this).get(0).createTextRange) {
								var range = $(this).get(0).createTextRange();
								range.collapse(true);
								range.moveEnd('character', pos);
								range.moveStart('character', pos);
								range.select();
							}
						}

					})(jQuery);
					</script>
				";
			}


			elseif ($typ=="fo")
				{
				$wynik="<input type=\"image\"";
				if ($argumenty[0]) $wynik.=" name=\"$argumenty[0]\" id=\"$argumenty[0]\"";
				if ($argumenty[1]) $wynik.=" value=\"$argumenty[1]\"";
				if ($argumenty[2]) $wynik.=" src=\"$argumenty[2]\"";
				if ($argumenty[3]) $wynik.=" title=\"$argumenty[3]\" alt=\"$argumenty[3]\"";
				if ($argumenty[4]) $wynik.=" $argumenty[4] border=\"0\"";
				$wynik.="/>";
				}
			elseif($typ == 'fa')
				{
					$wynik = "<script>
						function gra()
						{
							$('#gra').html('<div id=\"AYAH\">...ładowanie AYAH...</div>');
							var request = $.ajax({
								url: 'https://lilion.org/index.php',
								type: 'GET',
								data: { cms_www:'_ajax_ayah',tryb:'pokaz',cms_widok:'nocms' },
								dataType: 'text'
							});

							request.done(function( js ) {
								//alert( 'success - wczytanie gry');
								$('#AYAH').html('');
								var s = document.createElement('script');
									s.type = 'text/javascript';
									s.src = js;
									document.body.appendChild(s);
									$(':submit').removeAttr('disabled');
							});

							request.fail(function( jqXHR, textStatus ) {
								//alert( 'Request failed (wczytanie gry): ' + textStatus );
							});
						}

						$(document).ready(function() {
							var formularz = $('#AYAH').parents('form');

							$(':submit').attr('disabled','disabled');
							gra();

							formularz.submit(function( event ) {
								var klucz = $('input[name=\"session_secret\"]').val();
								var status = 0;
								//event.preventDefault();
								//alert('akcja: '+$(this).attr('action') + ' klucz: '+klucz );

								var request2 = $.ajax({
									url: 'https://lilion.org/index.php',
									async: false,
									type: 'GET',
									data: { cms_www:'_ajax_ayah',session_secret:klucz,tryb:'sprawdz',cms_widok:'nocms' },
									dataType: 'text'
								});

								request2.done(function(ayah_stan) {
									if(ayah_stan == 'OK'){status = 1;}
									//alert( 'success (stan gry): \"' +ayah_stan+ '\"');
								});

								request2.fail(function( jqXHR, textStatus ) {
									alert( 'Musisz wpierw wygrać grę');
								});

								if(status == 0) {
									//alert('nie przejdzie - gra niezaakceptowana');
									gra();
									return false;
								} else {
									//alert('przejdzie');
									return true;
								}


							});
						});
						</script>
						<div id=\"gra\"></div>
						";
							//var = formularz;
								//session_secret
								//
								//
				}
			elseif($typ=="ifnull")
			{
				$wynik=$argumenty[0]?$argumenty[0]:$argumenty[1];
			}
			elseif($typ=="l")
			{
				$wynik="<a";
				if(isset($argumenty[0])) $wynik.=" href=\"{$argumenty[0]}\"";
				$wynik.=">";
				if(isset($argumenty[1])) $wynik.=$argumenty[1];
				elseif(isset($argumenty[0])) $wynik.=$argumenty[0];
				$wynik.="</a>";
				exit($wynik);
			}
			elseif($typ=="ljs")
			{
				$wynik="<a";
				if ($argumenty[0]) $wynik.=" href=\"javascript:$argumenty[0]\"";
				$wynik.=">";
				if ($argumenty[1]) $wynik.="$argumenty[1]";
				elseif ($argumenty[0]) $wynik.=$argumenty[0];
				$wynik.="</a>";
			}
			elseif ($typ=="lp")
			{
				$wynik="<a";

				if($argumenty[0]) $wynik.=" href=\"?cms_www=$argumenty[0]\" ".(empty($argumenty[2])?'':$argumenty[2]);
				$wynik.=">";
				if($argumenty[1]!==NULL) $wynik.="$argumenty[1]";
				elseif ($argumenty[0]) $wynik.=$argumenty[0];
				$wynik.="</a>";
			}
			elseif ($typ=="ls")
			{
				$wynik="<a";
				if ($argumenty[0]) $wynik.=" href=\"?cms_www=_$argumenty[0]\"";
				$wynik.=">";
				if ($argumenty[1]) $wynik.="$argumenty[1]";
				elseif ($argumenty[0]) $wynik.=$argumenty[0];
				$wynik.="</a>";
			}
			elseif ($typ=="nl")
				{
				if (!($argumenty[0])) $argumenty[0]=1;

				$wynik='';
				for ($i0=0; $i0<$argumenty[0]; $i0++)
					{
					$wynik.="\r\n";
					}
				}
			elseif($typ=="nl2br") $wynik=nl2br($argumenty[0]);
			elseif($typ=="o")
			{
				if(substr($argumenty[0], 0, 4)!='http') $argumenty[0]=$this->ustawienia['meta_adres'].'/'.$argumenty[0];

				$wynik="<img";
				if(isset($argumenty[0])) $wynik.=" src=\"$argumenty[0]\"";
				if(isset($argumenty[1])) $wynik.=" title=\"$argumenty[1]\" alt=\"$argumenty[1]\"";
				if(isset($argumenty[2])) $wynik.=" $argumenty[2] border=\"0\"";
				$wynik.=" />";
			}
			elseif($typ=="tt")
			{
				if($argumenty[0]) $l_tt=$this->request2array($argumenty[0]);
				$wynik="<script type=\"text/javascript\">";

				if(!$argumenty[1]) $argumenty[1]='cream';

				foreach($l_tt as $klucz=>$wartosc)
				{
					//style: cream, dark, green, light, red, blue
					$wynik.="
$('$klucz').qtip({
position: { target: 'mouse' },
content: '$wartosc',
style: { name: '$argumenty[1]' },
show: 'mouseover',
hide: 'mouseout'
})";
				}

				$wynik.="</script>";
			}
			elseif ($typ=="u")
			{
				$wynik=$_SESSION['u'][$argumenty[0]];
			}
			elseif($typ=="var")
			{
				if($argumenty[1]) $wynik=$GLOBALS[$argumenty[0]][$argumenty[1]];
				else $wynik=$GLOBALS[$argumenty[0]];
			}
			else
			{
				$wynik=FALSE;
				$this->o_raport->dodaj('error', null, "Blad wejscia w funkcji: odkoduj phoenixkod ('$phoenixkod');<br />Nierozpoznany typ: '$typ'.<br /><br />");
				return $wynik;
			}
			*/

?>