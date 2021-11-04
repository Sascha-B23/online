<?php
/**
 * Diese Klasse verwaltet die Textmbausteine für den Widerspruchsgenerator
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TextModuleManager extends SearchDBEntry 
{
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * Country
	 * @var CCountry
	 */
	protected $country = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function TextModuleManager(DBManager $db, CCountry $country)
	{
		$this->db = $db;
		$this->country = $country;
	}
	
	/**
	 * Gibt die Anzahl der Textmbausteine zurück
	 * @param string $searchString
	 * @return int
	 */
	public function GetTextModuleCount($searchString="")
	{
		$object=new TextModule($this->db);
		return $this->GetDBEntryCount($searchString, TextModule::TABLE_NAME, $object->GetTableConfig()->rowName, "country='".$this->country->GetIso3166()."'");
	}
	
	/**
	 * Gibt die Users entsprechend den übergebenen Parametern zurück
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @return TextModule[]
	 */
	public function GetTextModule($searchString="", $orderBy="title", $orderDirection=0, $currentPage=0, $numEntrysPerPage=0)
	{
		$object=new TextModule($this->db);
		$dbEntrys=$this->GetDBEntry($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, TextModule::TABLE_NAME, $object->GetTableConfig()->rowName, "country='".$this->country->GetIso3166()."'");
		// Objekte erzeugen
		$objects=Array();
		for ($a=0; $a<count($dbEntrys); $a++)
		{
			$object = new TextModule($this->db);
			if ($object->Load($dbEntrys[$a], $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
}
?>