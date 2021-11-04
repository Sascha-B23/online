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
class LtContractTabData extends TabData 
{
	/**
	 * current shop
	 * @var CShop
	 */
	protected $shop = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param CLocation $location 
	 */
	public function LtContractTabData(DBManager $db, ExtendedLanguageManager $languageManager, CShop $shop)
	{
		$this->shop = $shop;
		if ($this->shop!=null)
		{
			$contracts = $this->shop->GetContracts($db);
			for ($a=0; $a<count($contracts); $a++)
			{
				$this->tabDataEntries[] = new LtContractTabDataEntry($db, $languageManager, $contracts[$a]);
			}
		}
	}
	
}
?>