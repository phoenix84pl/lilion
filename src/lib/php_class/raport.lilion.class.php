<?php

class raport
{
	/* SPIS TRESCI

	public	function object2nazwa($data)
	public	function dodaj($typ, $dane_in, $tresc=NULL)
	private function _parse_raport($typ)
	public	function ostatni($typ)
	public function pokaz_ostatni($typ)
	private function _prezentuj_raport($typ,$raport)
	public function pokaz_wszystkie($typ)
	*/

	public $raport;		//tablica zawierajaca raporty
	public $error;		//tablica zawierajaca bledy
	public $curl;		//tablica zawierajaca odpytywania www
	private $_dane;
	private $_poziom = 5;

	/**
	 * metoda zwraca nazwe obiektu
	 * @param mixed $data obiekt albo tablica obiektów
	 * @return string
	 */
	public	function object2nazwa($data)
	{
		//funkcja

		if (is_object($data))
		{
			$data=get_class($data);
			if($data=='') $data=NULL;
		}
		return is_array($data)?array_map(array('raport','object2nazwa'), $data) : $data;
	}

	/**
	 * metoda rejestruje kolejny raport
	 *
	 * @param string $typ - typ raportu (raport, error lub curl)
	 * @param mixed $dane int (w nowej wersji) oznaczający ilość kroków debug_bucktrase do zapisania, lub array (w starej wersji) naogół wynik wywołania funkcji debug_bucktrase
	 * @param mixed $tresc string lub array - dodatkowe infomacje
	 */
	public	function dodaj($typ, $dane_in=null, $tresc=NULL)
	{
			//dziala tylko jesli debug

		if(!empty($_REQUEST['debug']))
		{
			if(is_integer($dane_in) or empty($dane_in)){
				if($dane_in != 0) $this->_poziom = $dane_in;
				$this->_dane = debug_backtrace();//DEBUG_BACKTRACE_PROVIDE_OBJECT,$this->_poziom);
			}else{
				$this->_dane = $dane_in;
			}


			if(is_array($tresc))
			{
				$dane = array_merge(array('czas'=>time(), 'godzina'=>date("YmdHis")), $tresc);

			}
			else
			{
				//echo "decode $typ <br/>";
				$tresc = htmlentities($tresc);
				$dane = array('czas'=>time(), 'godzina'=>date("YmdHis"), 'tresc'=>$tresc);
			}

			$dane['zrzut'] = $this->_parse_raport($typ);

			switch($typ)
			{
				case 'raport':	$this->raport[]	= $dane; break;
				case 'error':	$this->error[]	= $dane; break;
				case 'curl':	$dane['tresc'] = '<pre style="background-color: white; color:black; font-weight:normal;">'.$dane['tresc'].'</pre>'; $this->curl[] = $dane; break;//
			}
		}
	}

	/**
	 * metoda analizuje dane raportu, które pochodzą z funkcji debug_backtrase
	 *
	 * @param string $typ - typ raportu
	 * @return array
	 */
	private function _parse_raport($typ)
	{
		if(empty($this->_dane)) return false;

		$raport = array();
		//$start = ($typ == 'error')? 0 : 1;
		for($i=0; $i<$this->_poziom;$i++){
			if(empty($this->_dane[$i])) break;
			$raport[] = array(
				'file' => (isset($this->_dane[$i]['file']) ? $this->_dane[$i]['file'] : NULL),
				'line' => (isset($this->_dane[$i]['line']) ? $this->_dane[$i]['line'] : NULL),
				'function' => (isset($this->_dane[$i]['function']) ? $this->_dane[$i]['function'] : NULL),
				'class' => (isset($this->_dane[$i]['class']) ? $this->_dane[$i]['class'] : NULL),
				'args' => (isset($this->_dane[$i]['args']) ? $this->_dane[$i]['args'] : NULL),
			);
		}
		return $raport;
	}

	/**
	 * metoda zwraca zawartość ostatniego raportu wybranego typu.
	 *
	 * @param string $typ - typ raportu
	 * @return array
	 */
	public	function ostatni($typ)
	{
		switch($typ)
		{
			case 'raport':	$ilosc=count($this->raport);	return $this->raport[$ilosc-1];	break;
			case 'error':	$ilosc=count($this->error);		return $this->error[$ilosc-1];	break;
			case 'curl':	$ilosc=count($this->curl);		return $this->curl[$ilosc-1]['tresc'];	break;
		}
	}

	/**
	 * metoda generuje kod HTML prezentujący ostatni raport wybranego typu
	 *
	 * @param string $typ - typ raportu
	 * @return string
	 */
	public function pokaz_ostatni($typ)
	{
		switch($typ)
		{
			case 'raport':	$ilosc=count($this->raport);	$raport = $this->raport[$ilosc-1];	break;
			case 'error':	$ilosc=count($this->error);		$raport = $this->error[$ilosc-1];	break;
			case 'curl':	$ilosc=count($this->curl);		$raport = $this->curl[$ilosc-1]['tresc'];	break;
		}

		return '<table style="font-family: Arial; font-size: 12px;"><tr><td colspan="3" style="color:white; background-color:red; font-size:140%;padding:5px 10px;">'.$typ.'</td></tr>'.$this->_prezentuj_raport($typ,$raport).'</table>';
	}

	/**
	 * metoda przetwarza tablicę zawierającą raport na kod HTML do prezentacji
	 *
	 * @param string $typ - typ raportu
	 * @param array $raport - analizowany raport
	 * @return string
	 */
	private function _prezentuj_raport($typ,$raport)
	{
		if(empty($raport)) return '<tr><td colspan="2"> brak raportów </td></tr>';
		$czas = (is_integer($raport['czas']))? date("Y/m/d H:i:s",$raport['czas']) : $raport['czas'];
		$tresc_raportu = '<tr style="background-color: #ccc; font-size:120%; color: #d00; font-weight:bold;">
				<td colspan="2" style="padding:5px 10px;">'." $typ: $czas</td>
				<td>{$raport['tresc']}</td>
			</tr>";

		if(!empty($raport['zrzut'])){
			if(is_array($raport['zrzut'])) foreach ($raport['zrzut'] as $nr => $dane) {
				if($nr) $nr = "-$nr";
				$tresc_raportu .= '<tr>
					<td rowspan="3" style="text-align:right padding:10px; border-bottom:solid 1px red;">step: '.$nr.'</td>
					<td style="text-align:right;padding:5px 10px;"> plik: </td><td style="font-weight:bold;">'.$dane['file'].'</td>
				</tr>
				<tr>
					<td style="text-align:right;padding:5px 10px;">linia: </td><td style="font-weight:bold;">'.$dane['line'].'</td>
				</tr>
				<tr>
					<td style="text-align:right;padding:5px 10px; border-bottom:solid 1px red;">funkcja: </td>
					<td style="font-weight:bold; border-bottom:solid 1px red;">'.( (!empty($dane['class']))? $dane['class'].'::' : '' ).$dane['function'].'()</td>
				</tr>';
			}
			else{
				$tresc_raportu .= '<tr>
					<td>zrzut</td>
					<td style="text-align:right;padding:5px 10px;"> plik: </td><td style="font-weight:bold;">'.$raport['zrzut'].'</td>
				</tr>';
			}
		}

		return $tresc_raportu;
	}

	/**
	 * metoda generuje kod HTML prezentujący wszystkie zebrane raporty wybranego typu
	 *
	 * @param string $typ - typ raportu
	 * @return string
	 */
	public function pokaz_wszystkie($typ)
	{
		switch($typ)
		{
			case 'raport':	$raport = $this->raport;	break;
			case 'error':	$raport = $this->error;	break;
			case 'curl':	$raport = $this->curl;	break;
		}
		if(!empty ($raport)){
			$tresc_raportow = '';
			foreach ($raport as $nr => $dane_raportu) {
				$tresc_raportow .= $this->_prezentuj_raport($typ,$dane_raportu);
			}
		} else {
			$tresc_raportow = '<td colspan="3" style="color:red; padding:5px 10px;"> brak danych</td>';
		}

		return '<table style="font-family: Arial; font-size: 12px;"><tr><td colspan="3" style="color:white; background-color:red; font-size:140%;padding:5px 10px;">'.$typ.'</td></tr>'.$tresc_raportow.'</table>';
	}

}

?>
