<?php
/**
 * ListData-Implementierung für die Textbausteine
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TextModuleListManager extends ListData 
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
	 * UserManager-Objekt
	 * @var TextModuleManager
	 */
	protected $manager = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db Datenbank-Objekt
	 */
	public function TextModuleListManager(DBManager $db, CCountry $country)
	{
		// Kostenartenmanager initialisieren
		$this->db = $db;
		$this->country = $country;
		$this->manager = new TextModuleManager($this->db, $this->country);
		// Options Array setzen	
		$this->options["icon"]="textmodule.png";
		$this->options["icontext"]="Textbausteine ".$this->country->GetName();
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => "Name", "sortby" => "title" ),
											1 => array( "caption" => "Optionen", "sortby" => "" )
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
		global $SHARED_HTTP_ROOT;
		$return=null;
		$objects=$this->manager->GetTextModule($searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for ($a=0; $a<count($objects); $a++)
		{
			$return[$a][0]=$objects[$a]->GetTitle();
			$return[$a][1]=Array();
			$return[$a][1]["editUrl"] = "textbausteine_edit.php5?editElement=".$objects[$a]->GetPKey();
			if ($objects[$a]->IsDeletable($this->db))
			{
				$return[$a][1]["deleteUrl"] = "";
			}
			$return[$a][1]["pkey"] = $objects[$a]->GetPKey();
		}
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @param string $searchString
	 * @return int Anzahl der Einträge
	 */
	public function GetNumTotalEntrys($searchString)
	{
		return $this->manager->GetTextModuleCount($searchString);
	}
		
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @param Array $deleteArray
	 */
	public function DeleteEntries($deleteArray)
	{
		for($a=0; $a<count($deleteArray); $a++)
		{
			$object=new TextModule($this->db);
			if ($object->Load($deleteArray[$a], $this->db)===true)
			{
				$object->DeleteMe($this->db);
			}
		}
	}
	
}
?>