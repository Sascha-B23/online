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
class AddressDataListData extends ListData 
{
	/**
	 * AddressManager
	 * @var AddressManager
	 */
	protected $manager = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 * @param AddressManager $addressManager
	 */
	public function AddressDataListData(DBManager $db, ExtendedLanguageManager $languageManager, AddressManager $addressManager)
	{
		parent::__construct($db, $languageManager);
		$this->manager = $addressManager;		
		// Options Array setzen	
		$this->options["icon"]="address.png";
		$this->options["icontext"]="Ansprechpartner";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => AddressData::GetAttributeName($this->languageManager, 'name'), "sortby" => AddressData::TABLE_NAME.".name" ),
											1 => array( "caption" => AddressData::GetAttributeName($this->languageManager, 'firstname'), "sortby" => AddressData::TABLE_NAME.".firstname" ),
											2 => array( "caption" => AddressCompany::GetAttributeName($this->languageManager, 'name'), "sortby" => AddressCompany::TABLE_NAME.".name" ),
											3 => array( "caption" => AddressData::GetAttributeName($this->languageManager, 'email'), "sortby" => AddressData::TABLE_NAME.".email" ),
											4 => array( "caption" => AddressData::GetAttributeName($this->languageManager, 'phone'), "sortby" => AddressData::TABLE_NAME.".phone" ),
											5 => array( "caption" => AddressData::GetAttributeName($this->languageManager, 'mobile'), "sortby" => AddressData::TABLE_NAME.".mobile" ),
											6 => array( "caption" => "Optionen", "sortby" => "" ),
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
		$objects=$this->manager->GetAddressData($searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for ($a=0; $a<count($objects); $a++)
		{
			$return[$a][0]=$objects[$a]->GetName();
			$return[$a][1]=$objects[$a]->GetFirstName();
			$return[$a][2]=$objects[$a]->GetCompany();
			$return[$a][3]=$objects[$a]->GetEMail();
			$return[$a][4]=$objects[$a]->GetPhone();
			$return[$a][5]=$objects[$a]->GetMobile();
			$return[$a][6]["editUrl"] = "adressen_edit.php5?editElement=".$objects[$a]->GetPKey();
			if ($objects[$a]->IsDeletable($this->db))
			{
				$return[$a][6]["deleteUrl"] = "";
			}
			$return[$a][6]["pkey"] = $objects[$a]->GetPKey();
		}
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 */
	public function GetNumTotalEntrys($searchString)
	{
		return $this->manager->GetAddressDataCount($searchString);
	}
			
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 */
	public function DeleteEntries($deleteArray)
	{
		for($a=0; $a<count($deleteArray); $a++)
		{
			$object=new AddressData($this->db);
			if ($object->Load($deleteArray[$a], $this->db)===true)
			{
				$object->DeleteMe($this->db);
			}
		}
	}
	
}
?>