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
class LtTeilabrechnungTabData extends TabData 
{
	/**
	 * current contract
	 * @var AbrechnungsJahr
	 */
	protected $year = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param CLocation $location 
	 */
	public function LtTeilabrechnungTabData(DBManager $db, ExtendedLanguageManager $languageManager, AbrechnungsJahr $year)
	{
		$this->year = $year;
		if ($this->year!=null)
		{
			$teilabrechnungen = $this->year->GetTeilabrechnungen($db);
			for ($a=0; $a<count($teilabrechnungen); $a++)
			{
				$this->tabDataEntries[] = new LtTeilabrechnungTabDataEntry($db, $languageManager, $teilabrechnungen[$a]);
			}
		}
	}
	
}
?>