<?php

class curl
{
        //klasa odpowiada za tworzenie polaczen curl

        public $o_raport;               //zawiera raport
        public $error;                  //zawiera ostatni error
        public $ciastko;                //adres ciastka

        public $info;					//INFO o wykonanym curlu (curl_getinfo)

                //wartosci jednorazowe (przywracane po kazdym polaczeniu)
        public $post=NULL;                      //zawiera tablice z postami do wyslania
        public $headers=array(  //array z dodatkowymi naglowkami do wyslania w zapytaniu
                'Accept-Language: pl,en;q=0.7,en-us;q=0.3',
                'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:31.0) Gecko/20100101 Firefox/31.0',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Charset: ISO-8859-2,utf-8;q=0.7,*;q=0.7',
                'Expect:'
                );

        public function __construct()
        {
                $this->o_raport=new raport();
                $this->o_raport->dodaj('raport');
        }

        public function headers_dodaj($headers)
        {
                //funkcja dodaje wiecej naglowkow do curla

                $this->o_raport->dodaj('raport');

                $this->headers=array_merge($this->headers, $headers);
        }

        public function jednorazowe_przywroc()
        {
                //funkcja przywraca wartosci domyslne dla jednorazowych

                $this->o_raport->dodaj('raport');

                $this->post=NULL;

                $this->headers=array(
					'Accept-Language: pl,en;q=0.7,en-us;q=0.3',
					'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:31.0) Gecko/20100101 Firefox/31.0',
					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',

					'Accept-Charset: ISO-8859-2,utf-8;q=0.7,*;q=0.7',
					'Expect:'
                );
        }

        public function wykonaj($url, $curl_opts = array())
        {
                $this->o_raport->dodaj('raport');

                $this->url = $url;
 //               $this->cainfo=__DIR__.'/'.$this->cainfo;

                if(isset($_SESSION['cms_tryb']) && strpos($_SESSION['cms_tryb'], 'nocurl')!==FALSE)
                {
                        $this->o_raport->dodaj('error', 'NOCURL');
                        return null;
                }

                $curl=curl_init();
//              var_dump($curl);
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

                if(isset($this->verifypeer)) curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->verifypeer);
                if(isset($this->verifyhost)) curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->verifyhost);
                if((isset($this->sslversion)?$this->sslversion:NULL) == 3) curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, 'SSLv3');
                if(isset($this->sslversion)) curl_setopt($curl, CURLOPT_SSLVERSION, $this->sslversion);
                if(isset($this->timeout)) curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
                if(isset($this->followlocation)) curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $this->followlocation);
                if(isset($this->useragent)) curl_setopt($curl, CURLOPT_USERAGENT, $this->useragent);
                if(isset($this->verbose)) curl_setopt($curl, CURLOPT_VERBOSE, $this->verbose);
                if(isset($this->header)) curl_setopt($curl, CURLOPT_HEADER, $this->header);
                if(isset($this->headers)) curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
				//curl_setopt($curl, CURLOPT_CAINFO, $this->cainfo);
				curl_setopt_array($curl, $curl_opts);

                if($this->ciastko)
                {
                        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->ciastko);
                        curl_setopt($curl, CURLOPT_COOKIEFILE, $this->ciastko);
                }

                if($this->post)
                {
                        if( is_array($this->post) && count($this->post) > 0 ) {
                                $postdata = http_build_query($this->post,'','&');
                        }
                        elseif( is_string($this->post) ) {
                            $postdata = $this->post;
                        }
                        else $postdata = '';

                        curl_setopt($curl, CURLOPT_POST, 1);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
                }
//cms_czasy_rejestruj('curl_przed');
                $wynik=curl_exec($curl);
                $this->info=curl_getinfo($curl);
//cms_czasy_rejestruj('curl_po');

                if($wynik===FALSE) $this->error=curl_errno($curl).': '.curl_error($curl);

                $this->jednorazowe_przywroc();

                //kkuz: added for reports
                $this->o_raport->dodaj('curl', null, "url: |$url|\r\n\r\n $wynik" );
                //$this->o_raport->dodaj('curl', $url, $wynik);

                return $wynik;
        }

}

?>
