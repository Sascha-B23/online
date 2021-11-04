<?php
/**
 * ListData-Implementierung für CustomerGroup
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class CustomerGroupListData extends ListData 
{	
	/**
	 * UserManager-Objekt
	 * @var CustomerManager
	 */
	protected $manager = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 * @param CustomerManager $customerManager
	 */
	public function CustomerGroupListData(DBManager $db, ExtendedLanguageManager $languageManager, CustomerManager $customerManager)
	{
		parent::__construct($db, $languageManager);
		$this->manager = $customerManager;
		// Options Array setzen	
		$this->options["icon"]="cGroup.png";
		$this->options["icontext"]="Kundengruppen";
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => CGroup::GetAttributeName($this->languageManager, 'name'), "sortby" => "name" ),
											1 => array( "caption" => "Benutzergruppe", "sortby" => "userGroup" ),
											2 => array( "caption" => "Anzahl untergeordneter Firmen", "sortby" => "" ),
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
		$return=null;
		$objects=$this->manager->GetGroups($_SESSION["currentUser"], $searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for ($a=0; $a<count($objects); $a++)
		{
			$return[$a][0]=$objects[$a]->GetName();
			$return[$a][1]=$objects[$a]->GetUserGroup()!=null ? $objects[$a]->GetUserGroup()->GetName() : "-";
			$return[$a][2]=$objects[$a]->GetCompanyCount($this->db);
			$return[$a][3]=Array();
			$return[$a][3]["editUrl"] = "cGroup_edit.php5?editElement=".$objects[$a]->GetPKey();
			if ($objects[$a]->IsDeletable($this->db))
			{
				$return[$a][3]["deleteUrl"] = "";
			}
			$return[$a][3]["pkey"] = $objects[$a]->GetPKey();
		}
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 */
	public function GetNumTotalEntrys($searchString)
	{
		return $this->manager->GetGroupCount($_SESSION["currentUser"], $searchString);
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 */
	public function DeleteEntries($deleteArray)
	{
		for ($a=0; $a<count($deleteArray); $a++)
		{
			$object = new CGroup($this->db);
			if ($object->Load($deleteArray[$a], $this->db)===true)
			{
				$object->DeleteMe($this->db);
			}
		}
	}
	
}
?>