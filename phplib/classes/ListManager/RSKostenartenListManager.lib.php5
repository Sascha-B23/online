<?php

/***************************************************************************
 * ListData-Implementierung für die FMS-Kostenarten
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class RSKostenartenListManager extends ListData {
	
	/***************************************************************************
	 * Datenbankobjekt
	 * @var MySQLManager
	 * @access protected
	 **************************************************************************/
	protected $db = null;

	/***************************************************************************
	 * UserManager-Objekt
	 * @var Object
	 * @access protected
	 **************************************************************************/
	protected $manager = null;
	
	/***************************************************************************
	* Konstruktor
	* @param object	$db	Datenbank-Objekt
	* @access public
	***************************************************************************/
	public function __construct($db){
		// Kostenartenmanager initialisieren
		$this->db = $db;
		$this->manager = new RSKostenartManager($db);
		// Options Array setzen	
		$this->options["icon"]="kostenart.png";
		$this->options["icontext"]="SFM Kostenarten";
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => "Name", "sortby" => "name" ),
											1 => array( "caption" => "Beschreibung", "sortby" => "beschreibung" ),
											2 => array( "caption" => "Optionen", "sortby" => "" ),
										);
	}
	
	/***************************************************************************
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
	* @access public
	 ***************************************************************************/
	public function Search($searchString, $orderBy, $orderDirection="ASC", $numEntrysPerPage=20, $currentPage=0){
		global $SHARED_HTTP_ROOT;
		$return=null;
		$objects=$this->manager->GetKostenarten($searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for( $a=0; $a<count($objects); $a++){
			$return[$a][0]=$objects[$a]->GetName();
			$return[$a][1]=$objects[$a]->GetBeschreibung();
			$return[$a][2]=Array();
			$return[$a][2]["editUrl"] = "kostenarten_edit.php5?editElement=".$objects[$a]->GetPKey();
			if( $objects[$a]->IsDeletable($this->db) ){
				$return[$a][2]["deleteUrl"] = "";
			}
			$return[$a][2]["pkey"] = $objects[$a]->GetPKey();
		}
		return $return;
	}
	
	/***************************************************************************
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 * @access public
	 ***************************************************************************/
	public function GetNumTotalEntrys($searchString){
		return $this->manager->GetKostenartenCount($searchString);
	}
			
	/***************************************************************************
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 ***************************************************************************/
	public function DeleteEntries($deleteArray){
		for($a=0; $a<count($deleteArray); $a++){
			$object=new RSKostenart($this->db);
			if( $object->Load($deleteArray[$a], $this->db)===true ){
				$object->DeleteMe($this->db);
			}
		}
	}
	
} // RSKostenartenListManager


?>