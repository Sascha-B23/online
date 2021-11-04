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
class ContractListData extends ListData 
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
	public function ContractListData(DBManager $db, ExtendedLanguageManager $languageManager)
	{
		parent::__construct($db, $languageManager);
		$this->manager = new RSKostenartManager($db);		
		// Options Array setzen	
		$this->options["icon"]="contract.png";
		$this->options["icontext"]="Verträge";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => CCompany::GetAttributeName($this->languageManager, 'name'), "sortby" => CCompany::TABLE_NAME.".name" ),
											1 => array( "caption" => CLocation::GetAttributeName($this->languageManager, 'name'), "sortby" => CLocation::TABLE_NAME.".name" ),
											2 => array( "caption" => CShop::GetAttributeName($this->languageManager, 'name'), "sortby" => CShop::TABLE_NAME.".name" ),
											3 => array( "caption" => CShop::GetAttributeName($this->languageManager, 'RSID'), "sortby" => CShop::TABLE_NAME.".RSID" ),
											4 => array( "caption" => "Mietfläche qm", "sortby" => Contract::TABLE_NAME.".mietflaeche_qm" ),
											5 => array( "caption" => "Umlagefläche qm", "sortby" => Contract::TABLE_NAME.".umlageflaeche_qm" ),
											6 => array( "caption" => "Währung", "sortby" => Contract::TABLE_NAME.".currency" ),
											7 => array( "caption" => "Optionen", "sortby" => "" ),
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
		$objects=$this->manager->GetContracts($_SESSION["currentUser"], $searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for ($a=0; $a<count($objects); $a++)
		{
			$cShop=$objects[$a]->GetShop();
			$cLocation=null;
			if( $cShop!=null )$cLocation=$cShop->GetLocation();
			$cCompany=null;
			if( $cLocation!=null)$cCompany=$cLocation->GetCompany();
			$return[$a][0]=$cCompany==null ? "" : $cCompany->GetName();
			$return[$a][1]=$cLocation==null ? "" : $cLocation->GetName();
			$return[$a][2]=$cShop==null ? "" : $cShop->GetName();
			$return[$a][3]=$cShop==null ? "" : $cShop->GetRSID();
			$return[$a][4]=HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetMietflaecheQM());
			$return[$a][5]=HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetUmlageflaecheQM());
			$return[$a][6]=$objects[$a]->GetCurrency();
			$return[$a][7]=Array();
			$return[$a][7]["editUrl"] = "contract_edit.php5?editElement=".$objects[$a]->GetPKey();
			if ($objects[$a]->IsDeletable($this->db))
			{
				$return[$a][7]["deleteUrl"] = "";
			}
			$return[$a][7]["pkey"] = $objects[$a]->GetPKey();
		}
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 */
	public function GetNumTotalEntrys($searchString)
	{
		return $this->manager->GetContractCount($_SESSION["currentUser"], $searchString);
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 */
	public function DeleteEntries($deleteArray)
	{
		for ($a=0; $a<count($deleteArray); $a++)
		{
			$object = new Contract($this->db);
			if ($object->Load($deleteArray[$a], $this->db)===true)
			{
				$object->DeleteMe($this->db);
			}
		}
	}
	
}
?>