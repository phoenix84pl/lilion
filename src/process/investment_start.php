<?php
require_once(__DIR__.'/../lib/root/precms.php');

if(isset($_SESSION['cms']['u']['id']) && isset($_REQUEST['id']) && isset($_REQUEST['amount']))
{
	cms_require_modul('db');

	$saldo=(float) $o_db->komorka('saldo', "WHERE `uid`='".htmlspecialchars($_SESSION['cms']['u']['id'])."' ORDER BY `id` DESC", 'przelewy');

	if($saldo>=$_REQUEST['amount'])
	{
		require_once(__DIR__."/../lib/php_foo/agado.php");

		if($o_db->bezposrednie('START TRANSACTION'))
		{
			if($o_db->rekord_dodaj(array('uid'=>htmlspecialchars($_SESSION['cms']['u']['id']), 'id_programy'=>htmlspecialchars($_REQUEST['id']), 'czas'=>time(), 'kwota'=>htmlspecialchars($_REQUEST['amount'])), 'inwestycje'))
			{
				$id_inwestycje=$o_db->rekord_id;
				if(transakcja($_SESSION['cms']['u']['id'], 0, $_REQUEST['amount'], "Investment #$id_inwestycje"))
				{
					if($o_db->aktualizuj(array('status'=>1), "WHERE `id`='$id_inwestycje'", 'inwestycje'))
					{
						$o_db->bezposrednie('COMMIT;');
						return true;
					}
					else
					{
						$o_db->bezposrednie('ROLLBACK;');
						return false;
					}
				}
				else
				{
					$o_db->bezposrednie('ROLLBACK;');
					return false;
				}
			}
			else
			{
				$o_db->bezposrednie('ROLLBACK;');
				return false;
			}
		}
		else return false;
	}
}

?>