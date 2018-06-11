<?php

class log
{
	//wersja 20170124 - powstanie klasy

	private $_nazwa;		//nazwa logowanego projektu, bedzie wykorzystana w sciezce

	public function __construct($nazwa)
	{
			//konstruktor
		$this->_nazwa=$nazwa;
	}

	public function loguj($tresc)
	{
		//wrzuca log do pliku

		$data=date("Ymd");
		$czas=date("His");

		$katalog=__DIR__."/../../tmp/log";

		if(!file_exists($katalog)) mkdir(__DIR__."/../../tmp/log", 0777);

		$plik="{$data}_{$this->_nazwa}.log";
		$sciezka=$katalog.'/'.$plik;

		if(file_exists($katalog)) chmod($katalog, 0777);
		if(file_exists($sciezka)) chmod($sciezka, 0777);

		return file_put_contents($sciezka, time().' '.$czas.' '.$tresc.PHP_EOL, FILE_APPEND);
	}

	public function generuj($metoda, $argumenty)
	{
		//funkcja generuje log z metody i argumentow

		if(isset($_SESSION['cms']) && (isset($_SESSION['cms']['u']['id']))) $uid=$_SESSION['cms']['u']['id'];
		else $uid=0;

		$argumenty=implode(', ', $argumenty);

		return $this->loguj("$uid $metoda $argumenty");
	}
}

?>