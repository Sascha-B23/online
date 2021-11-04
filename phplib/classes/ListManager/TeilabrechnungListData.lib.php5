<?php

/**
 * ListData-Implementierung für Teilabrechnungen
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TeilabrechnungListData extends ListData 
{
	/**
	 * RSKostenartManager
	 * @var RSKostenartManager
	 */
	protected $manager = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 * @param WorkflowManager $workflowManager 
	 */
	public function TeilabrechnungListData(DBManager $db, ExtendedLanguageManager $languageManager)
	{
		parent::__construct($db, $languageManager);
		$this->manager = new RSKostenartManager($db);
		// Options Array setzen	
		$this->options["icon"]="teilabrechnung.png";
		$this->options["icontext"]="Teilabrechnungen";		
		// Header definieren
		$this->data["datahead"] = array(	
											0 => array( "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'bezeichnung'), "sortby" => Teilabrechnung::TABLE_NAME.".bezeichnung" ),
											1 => array( "caption" => CCompany::GetAttributeName($this->languageManager, 'name'), "sortby" => CCompany::TABLE_NAME.".name" ),
											2 => array( "caption" => CLocation::GetAttributeName($this->languageManager, 'name'), "sortby" => CLocation::TABLE_NAME.".name" ),
											3 => array( "caption" => CShop::GetAttributeName($this->languageManager, 'name'), "sortby" => CShop::TABLE_NAME.".name" ),
											4 => array( "caption" => CShop::GetAttributeName($this->languageManager, 'RSID'), "sortby" => CShop::TABLE_NAME.".RSID" ),
											5 => array( "caption" => AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr'), "sortby" => AbrechnungsJahr::TABLE_NAME.".jahr" ),
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
		global $UM_GROUP_BASETYPE;
		global $SHARED_HTTP_ROOT;
		$return = null;
		$objects = $this->manager->GetTeilabrechnungen($_SESSION["currentUser"], $searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for ($a=0; $a<count($objects); $a++)
		{
			$jahr=$objects[$a]->GetAbrechnungsJahr();
			$contract=null;
			if ($jahr!=null) $contract=$jahr->GetContract();
			$cShop=null;
			if ($contract!=null) $cShop=$contract->GetShop();
			$cLocation=null;
			if ($cShop!=null) $cLocation=$cShop->GetLocation();
			$cCompany=null;
			if ($cLocation!=null) $cCompany=$cLocation->GetCompany();
			$return[$a][0]=$objects[$a]->GetBezeichnung();
			$return[$a][1]=$cCompany==null ? "" : $cCompany->GetName();
			$return[$a][2]=$cLocation==null ? "" : $cLocation->GetName();
			$return[$a][3]=$cShop==null ? "" : $cShop->GetName();
			$return[$a][4]=$cShop==null ? "" : $cShop->GetRSID();
			$return[$a][5]=$jahr==null ? "" : $jahr->GetJahr();
			$return[$a][6]=Array();
			$return[$a][6]["editUrl"] = "teilabrechnung_edit.php5?editElement=".$objects[$a]->GetPKey();
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
		return $this->manager->GetTeilabrechnungenCount($_SESSION["currentUser"], $searchString);
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 */
	public function DeleteEntries($deleteArray)
	{
		for ($a=0; $a<count($deleteArray); $a++)
		{
			$object = new Teilabrechnung($this->db);
			if ($object->Load($deleteArray[$a], $this->db)===true)
			{
				$object->DeleteMe($this->db);
			}
		}
	}
	
}
?>