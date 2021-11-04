<?php

/**
 * ListData-Implementierung für Verträge
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class DynamicTableConfigListData extends ListData 
{
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * Manager
	 * @var DynamicTableConfigManager 
	 */
	protected $manager = null;
	
	/**
	 * UserManager-Objekt
	 * @var DynamicTable
	 */
	protected $dynamicTable = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db Datenbank-Objekt
	 */
	public function DynamicTableConfigListData(DBManager $db, DynamicTable $dynamicTable)
	{
		$this->db = $db;
		$this->manager = new DynamicTableConfigManager($this->db);
		$this->dynamicTable = $dynamicTable;
		// Options Array setzen	
		$this->options["icon"]="filter.png";
		$this->options["icontext"]="Filterkonfigurationen";
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => "Name", "sortby" => "name" ),
											1 => array( "caption" => "Standardfilter", "sortby" => "defaultConfig" ),
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
		$objects = $this->manager->GetDynamicTableConfigs($_SESSION["currentUser"], $this->dynamicTable, $searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for ($a=0; $a<count($objects); $a++)
		{
			$return[$a][0]=$objects[$a]->GetName();
			$return[$a][1]=$objects[$a]->IsDefault() ? "Ja" : "";
			$return[$a][2]=Array();
			$return[$a][2]["editUrl"] = "configuration_edit.php5?editElement=".$objects[$a]->GetPKey();
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
		return $this->manager->GetDynamicTableConfigCount($_SESSION["currentUser"], $this->dynamicTable, $searchString);
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 */
	public function DeleteEntries($deleteArray)
	{
		for($a=0; $a<count($deleteArray); $a++)
		{
			$object=new DynamicTableConfig($this->db);
			if ($object->Load($deleteArray[$a], $this->db)===true)
			{
				if ($object->GetUser()->GetPKey()==$_SESSION["currentUser"]->GetPKey() && $object->GetTableId()==$this->dynamicTable->GetId())
				{
					$object->DeleteMe($this->db);
				}
			}
		}
	}
	
} // ContractListData

?>