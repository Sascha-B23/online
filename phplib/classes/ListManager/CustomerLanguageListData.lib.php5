<?php
/**
 * Auswertungsklasse für den ListManager
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class CustomerLanguageListData extends ListData 
{
	
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * UserManager-Objekt
	 * @var CustomerManager
	 */
	protected $manager = null;
	
	/**
	 * Konstruktor
	 * @param DBManager	$db	Datenbank-Objekt
	 */
	public function CustomerLanguageListData($db)
	{
		$this->db = $db;
		global $customerManager;
		$this->manager = $customerManager;		
		// Options Array setzen	
		$this->options["icon"]="language.png";
		$this->options["icontext"]="Sprachen";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => "Name", "sortby" => "name" ),
											1 => array( "caption" => "ISO-639-1", "sortby" => "iso639" ),
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
	 *  @return array	Das Rückgabearray muss folgendes Format haben:
	 *			Array[index][rowIndex] = content 	mit 	index = laufvaribale von 0 bis  $numEntrysPerPage 
	 *										rowIndex = Zugehörige Spalte siehe auch $this->data["datahead"]
	 *										content = Ausgabetext oder URL-Array in der Format Array[urlType] = url mit urlType = [editUrl oder deleteUrl]
	 */
	public function Search($searchString, $orderBy, $orderDirection="ASC", $numEntrysPerPage=20, $currentPage=0)
	{
		global $UM_GROUP_BASETYPE;
		global $SHARED_HTTP_ROOT;
		$return=null;
		$objects=$this->manager->GetLanguages($searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for ($a=0; $a<count($objects); $a++)
		{
			$return[$a][0]=$objects[$a]->GetName();
			$return[$a][1]=$objects[$a]->GetIso639();
			$return[$a][2]=Array();
			if ($objects[$a]->IsDeletable($this->db))
			{
				$return[$a][2]["editUrl"] = "language_edit.php5?editElement=".$objects[$a]->GetPKey();
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
		return $this->manager->GetLanguageCount($searchString);
	}
		
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 */
	public function DeleteEntries($deleteArray)
	{
		for($a=0; $a<count($deleteArray); $a++)
		{
			$object=new CLanguage($this->db);
			if( $object->Load($deleteArray[$a], $this->db)===true )
			{
				$object->DeleteMe($this->db);
			}
		}
	}
	
}
?>