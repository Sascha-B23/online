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
class GroupListManager extends ListData {
	
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * UserManager-Objekt
	 * @var Object
	 */
	protected $manager = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function GroupListManager(DBManager $db)
	{
		$this->db = $db;
		global $userManager;
		$this->manager = $userManager;		
		// Options Array setzen	
		$this->options["icon"]="userGroup.png";
		$this->options["icontext"]="Gruppen";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => "Name", "sortby" => "name" ),
											1 => array( "caption" => "Gruppentyp", "sortby" => "name" ),
											2 => array( "caption" => "Anzahl Benutzer", "sortby" => "" ),
											3 => array( "caption" => "Optionen", "sortby" => "" ),
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
		global $UM_GROUP_BASETYPE;
		global $SHARED_HTTP_ROOT;
		
		if( $this->data["datahead"][(int)$orderBy]["sortby"] != "")
		{
			$orderBy=$this->data["datahead"][(int)$orderBy]["sortby"];
		}
		else
		{	
			$orderBy = "name";
		}
		
		$return = null;
		$objects = $this->manager->GetGroups($searchString, $_SESSION["currentUser"]->GetGroupBasetype($this->db), $orderBy, $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for( $a=0; $a<count($objects); $a++)
		{
			$return[$a][0]=$objects[$a]->GetName();
			$return[$a][1]=$UM_GROUP_BASETYPE[$objects[$a]->GetBaseType()];
			$return[$a][2]=$objects[$a]->GetUserCount($this->db);
			$return[$a][3]=Array();
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db) >= UM_GROUP_BASETYPE_ADMINISTRATOR)
			{
				$return[$a][3]["editUrl"] = "group_edit.php5?editElement=" . $objects[$a]->GetPKey();
			}
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db) >= UM_GROUP_BASETYPE_ADMINISTRATOR && $objects[$a]->IsDeletable($this->db))
			{
				$return[$a][3]["deleteUrl"] = "";
			}
			$return[$a][3]["pkey"] = $objects[$a]->GetPKey();
			
		}
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int
	 */
	public function GetNumTotalEntrys($searchString)
	{
		return $this->manager->GetGroupCount($searchString, $_SESSION["currentUser"]->GetGroupBasetype($this->db));
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 */
	public function DeleteEntries($deleteArray)
	{
		for($a=0; $a<count($deleteArray); $a++)
		{
			$object=new UserGroup($this->db);
			if( $object->Load($deleteArray[$a], $this->db)===true )
			{
				$object->DeleteMe($this->db);
			}
		}
	}
	
}

?>