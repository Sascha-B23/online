<?php

/**
 * Auswertungsklasse für den ListManager
 * 
 * @access   	public
 * @author   	Johannes Glaser <j.glaser@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class AddressGroupListData extends ListData 
{
	/**
	 * AddressManager
	 * @var AddressManager
	 */
	protected $manager = null;
	
	/**
	 * Konstruktor
	 * @param AddressManager $addressManager
	 * @param DBManager $db
	 */
	public function AddressGroupListData(DBManager $db, ExtendedLanguageManager $languageManager, AddressManager $addressManager)
	{
		parent::__construct($db, $languageManager);
		$this->manager = $addressManager;		
		// Options Array setzen	
		$this->options["icon"]="adressgruppen.png";
		$this->options["icontext"]="Gruppen";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => AddressGroup::GetAttributeName($this->languageManager, 'name'), "sortby" => "name" ),
											1 => array( "caption" => "Firmen", "sortby" => "" ),
											2 => array( "caption" => "Optionen", "sortby" => "" ),
										);
		
	}
	
	/**
	 * Funktion zum Abrufen der Daten, die in der Liste angezeigt werden sollen
	 * @param string	$searchString 		Suchstring (leer bedeutet KEINE Suche)
	 * @param string	$orderBy 			Sortiert nach Spalte
	 * @param string	$orderDirection 		Sortier Richtung (ASC oder DESC)
	 * @param int	$numEntrysPerPage 	Anzahl der Einträge pro Seite
	 * @param int	$currentPage 		Angezeigte Seite
	 * @return array	Das Rückgabearray muss folgendes Format haben:
	 *			Array[index][rowIndex] = content 	mit 	index = laufvaribale von 0 bis  $numEntrysPerPage 
	 *										rowIndex = Zugehörige Spalte siehe auch $this->data["datahead"]
	 *										content = Ausgabetext oder URL-Array in der Format Array[urlType] = url mit urlType = [editUrl oder deleteUrl]
	 */
	public function Search($searchString, $orderBy, $orderDirection="ASC", $numEntrysPerPage=20, $currentPage=0)
	{
		$return=null;
		$objects=$this->manager->GetAddressGroupData($searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for ($a=0; $a<count($objects); $a++)
		{
			$return[$a][0]=$objects[$a]->GetName();
			$return[$a][1]=$objects[$a]->GetAddressCompanyCount($this->db);
			$return[$a][2]=Array();
			$return[$a][2]["editUrl"] = "adressgruppen_edit.php5?editElement=".$objects[$a]->GetPKey();
			if ($objects[$a]->IsDeletable($this->db))
			{
				$return[$a][2]["deleteUrl"] = "";
			}
			$return[$a][2]["pkey"] = $objects[$a]->GetPKey();
		}
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 */
	public function GetNumTotalEntrys($searchString)
	{
		return $this->manager->GetAddressGroupDataCount($searchString);
	}
		
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 */
	public function DeleteEntries($deleteArray)
	{
		for ($a=0; $a<count($deleteArray); $a++)
		{
			$object = new AddressGroup($this->db);
			if ($object->Load($deleteArray[$a], $this->db)===true)
			{
				$object->DeleteMe($this->db);
			}
		}
	}
	
}
?>