<?php
/**
 * ListData-Implementierung für Prozessgruppen
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class ProzessGroupListData extends ListData 
{
	/**
	 * WorkflowManager
	 * @var WorkflowManager
	 */
	protected $manager = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 * @param WorkflowManager $workflowManager 
	 */
	public function ProzessGroupListData(DBManager $db, ExtendedLanguageManager $languageManager, WorkflowManager $workflowManager)
	{
		parent::__construct($db, $languageManager);
		$this->manager = $workflowManager;		
		// Options Array setzen	
		$this->options["icon"]="passiveTask.png";
		$this->options["icontext"]="Pakete";			
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => ProcessStatusGroup::GetAttributeName($this->languageManager, 'name'), "sortby" => ProcessStatusGroup::TABLE_NAME.".name" ),
											1 => array( "caption" => "Prozesse", "sortby" => "" ),
											2 => array( "caption" => "Optionen", "sortby" => "" )
										);
		
	}
	
	/**
	 * Funktion zum Abrufen der Daten, die in der Liste angezeigt werden sollen
	 * @param string	$searchString 		Suchstring (leer bedeutet KEINE Suche)
	 * @param string	$orderBy 			Sortiert nach Spalte
	 * @param string	$orderDirection 	Sortier Richtung (ASC oder DESC)
	 * @param int	$numEntrysPerPage 	Anzahl der Einträge pro Seite
	 * @param int	$currentPage 		Angezeigte Seite
	 * @return array	Das Rückgabearray muss folgendes Format haben:
	 *			Array[index][rowIndex] = content 	mit 	index = laufvaribale von 0 bis  $numEntrysPerPage 
	 *										rowIndex = Zugehörige Spalte siehe auch $this->data["datahead"]
	 *										content = Ausgabetext oder URL-Array in der Format Array[urlType] = url mit urlType = [editUrl oder deleteUrl]
	 * @access public
	 */
	public function Search($searchString, $orderBy, $orderDirection="ASC", $numEntrysPerPage=20, $currentPage=0)
	{
		global $UM_GROUP_BASETYPE, $SHARED_HTTP_ROOT;
		$return=null;
		$objects=$this->manager->GetProcessStatusGroups($searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for ($a=0; $a<count($objects); $a++)
		{
			$return[$a][0]=$objects[$a]->GetName();
			$return[$a][1]=$objects[$a]->GetProcessCount();
			$return[$a][2]=Array();
			$return[$a][2]["editUrl"] = "processgroup_edit.php5?editElement=".$objects[$a]->GetPKey();
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db) >= UM_GROUP_BASETYPE_ADMINISTRATOR)
			{
				$return[$a][2]["deleteUrl"] = "";
				$return[$a][2]["pkey"] = $objects[$a]->GetPKey();
			}
		}
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 * @access public
	 */
	public function GetNumTotalEntrys($searchString)
	{
		return $this->manager->GetProcessStatusCount($searchString);
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 */
	public function DeleteEntries($deleteArray)
	{
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db) < UM_GROUP_BASETYPE_ADMINISTRATOR) return;
		for ($a=0; $a<count($deleteArray); $a++)
		{
			$object=new ProcessStatusGroup($this->db);
			if ($object->Load($deleteArray[$a], $this->db)===true)
			{
				$object->DeleteRecursive($this->db, $_SESSION["currentUser"]);
			}
		}
	}

}

?>