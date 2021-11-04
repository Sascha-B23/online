<?php


/***************************************************************************
 * ListData-Implementierung für CustomerShopSelect
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class CustomerShopSelectListData extends ListData {
	
	/***************************************************************************
	 * Datenbankobjekt
	 * @var MySQLManager
	 * @access protected
	 **************************************************************************/
	protected $db = null;
	
	/**
	 * CustomManager-Objekt
	 * @var CustomManager
	 */
	protected $manager = null;
	
	/**
	 * Selected Shop ID
	 * @var int 
	 */
	protected $selectedID = -1;
	
	/**
	 * Additional where-clause for filters
	 * @var string 
	 */
	protected $filterWhereClause = "";
	
	/***************************************************************************
	* Konstruktor
	* @param object	$db	Datenbank-Objekt
	* @access public
	***************************************************************************/
	public function __construct($db, $selectedID, $groupFilter, $companyFilter, $countryFilter){
		$this->db = $db;
		$this->selectedID = $selectedID;
		// Building filter-string
		if ((int)$groupFilter>0)
		{
			if ($this->filterWhereClause!="")$this->filterWhereClause.=" AND ";
			$this->filterWhereClause.= CGroup::TABLE_NAME.".pkey=".(int)$groupFilter;
		}
		if ((int)$companyFilter>0)
		{
			if ($this->filterWhereClause!="")$this->filterWhereClause.=" AND ";
			$this->filterWhereClause.= CCompany::TABLE_NAME.".pkey=".(int)$companyFilter;
		}
		if ($countryFilter!='')
		{
			if ($this->filterWhereClause!="")$this->filterWhereClause.=" AND ";
			$this->filterWhereClause.= CLocation::TABLE_NAME.".country='".$countryFilter."'";
		}
		
		global $customerManager;
		$this->manager = $customerManager;		
		// Options Array setzen	
		$this->options["icon"]="cShops.png";
		$this->options["icontext"]="Läden";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => "Name", "sortby" => CShop::TABLE_NAME.".name" ),
											1 => array( "caption" => "Standort", "sortby" => CLocation::TABLE_NAME.".name" ),
											2 => array( "caption" => "Firma", "sortby" => CCompany::TABLE_NAME.".name" ),
											3 => array( "caption" => "Land", "sortby" => CLocation::TABLE_NAME.".country" ),
											//4 => array( "caption" => "FMS-ID", "sortby" => CShop::TABLE_NAME.".RSID" ),
											4 => array( "caption" => "Kunden-ID", "sortby" => CShop::TABLE_NAME.".internalShopNo" ),
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
		global $UM_GROUP_BASETYPE;
		global $SHARED_HTTP_ROOT;
		$return=null;
		$objects=$this->manager->GetShops($_SESSION["currentUser"], $searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage, $this->filterWhereClause);
		for( $a=0; $a<count($objects); $a++){
			$location = $objects[$a]->GetLocation();
			$company = $location==null ? null : $location->GetCompany();
			$group = $company==null ? null : $company->GetGroup();

			$return[$a][-1]=Array();
			$return[$a][-1]["selectAction"] = "SelectShopFromList(".$group->GetPKey().", ".$company->GetPKey().",".$location->GetPKey().",".$objects[$a]->GetPKey().");";
			$return[$a][-1]["selected"] = ($this->selectedID==$objects[$a]->GetPKey() ? true : false);
			$return[$a][-1]["pkey"] = $objects[$a]->GetPKey();

			$return[$a][0]=$objects[$a]->GetName();
			$return[$a][1]=$company!=null ? $location->GetName() : "-";
			$return[$a][2]=$company!=null ? $company->GetName() : "-";
			$return[$a][3]=$location!=null ? $location->GetCountry() : "-";
			//$return[$a][4]=$objects[$a]->GetRSID();
			$return[$a][4]=$objects[$a]->GetInternalShopNo();
		}
		return $return;
	}
	
	/***************************************************************************
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 * @access public
	 ***************************************************************************/
	public function GetNumTotalEntrys($searchString){
		return $this->manager->GetShopCount($_SESSION["currentUser"], $searchString, $this->filterWhereClause);
	}
	
	/***************************************************************************
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 ***************************************************************************/
	public function DeleteEntries($deleteArray){
		// No delete actions here...
	}
	
} // CustomerShopSelectListData

?>