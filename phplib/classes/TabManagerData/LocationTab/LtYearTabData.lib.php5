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
class LtYearTabData extends TabData 
{
	/**
	 * current contract
	 * @var Contract
	 */
	protected $contract = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param CLocation $location 
	 */
	public function LtYearTabData(DBManager $db, ExtendedLanguageManager $languageManager, Contract $contract)
	{
		$this->contract = $contract;
		if ($this->contract!=null)
		{
			$abrechnungsJahre = $this->contract->GetAbrechnungsJahre($db);
			for ($a=0; $a<count($abrechnungsJahre); $a++)
			{
				$this->tabDataEntries[] = new LtYearTabDataEntry($db, $languageManager, $abrechnungsJahre[$a]);
			}
		}
	}
	
}
?>