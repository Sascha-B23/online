<?php
/**
 * Implementierung von TabData für den Bericht Kundenstandorte
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class LtShopTabData extends TabData 
{
	/**
	 * current location
	 * @var CLocation
	 */
	protected $location = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param CLocation $location 
	 */
	public function LtShopTabData(DBManager $db, ExtendedLanguageManager $languageManager, CLocation $location)
	{
		$this->location = $location;
		if ($this->location!=null)
		{
			$shops = $this->location->GetShops($db);
			for ($a=0; $a<count($shops); $a++)
			{
				$this->tabDataEntries[] = new LtShopTabDataEntry($db, $languageManager, $shops[$a]);
			}
		}
	}
	
}
?>