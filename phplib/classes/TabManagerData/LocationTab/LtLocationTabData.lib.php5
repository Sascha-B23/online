<?php
require_once 'LtLocationBaseTabDataEntry.lib.php5';
require_once 'LtLocationTabDataEntry.lib.php5';
require_once 'LtShopTabData.lib.php5';
require_once 'LtShopTabDataEntry.lib.php5';
require_once 'LtContractTabData.lib.php5';
require_once 'LtContractTabDataEntry.lib.php5';
require_once 'LtYearTabData.lib.php5';
require_once 'LtYearTabDataEntry.lib.php5';
require_once 'LtTeilabrechnungTabData.lib.php5';
require_once 'LtTeilabrechnungTabDataEntry.lib.php5';

/**
 * Implementierung von TabData für den Bericht Kundenstandorte
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class LtLocationTabData extends TabData 
{
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 * @param CLocation $location 
	 */
	public function LtLocationTabData(DBManager $db, ExtendedLanguageManager $languageManager, CLocation $location)
	{
		if ($location!=null)
		{
			$this->tabDataEntries[] = new LtLocationTabDataEntry($db, $languageManager, $location);
		}
	}
	
}
?>