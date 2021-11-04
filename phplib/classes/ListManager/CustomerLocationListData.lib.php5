<?php
/**
 * ListData-Implementierung für CustomerLocation
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class CustomerLocationListData extends ListData 
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
	public function CustomerLocationListData(DBManager $db, ExtendedLanguageManager $languageManager, CustomerManager $customerManager)
	{
		parent::__construct($db, $languageManager);
		$this->manager = $customerManager;		
		// Options Array setzen	
		$this->options["icon"]="cLocation.png";
		$this->options["icontext"]="Standorte";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => CLocation::GetAttributeName($this->languageManager, 'name'), "sortby" => CLocation::TABLE_NAME.".name" ),
											1 => array( "caption" => CCompany::GetAttributeName($this->languageManager, 'name'), "sortby" => CCompany::TABLE_NAME.".name" ),
											2 => array( "caption" => CLocation::GetAttributeName($this->languageManager, 'locationType'), "sortby" => CLocation::TABLE_NAME.".locationType" ),
											3 => array( "caption" => CLocation::GetAttributeName($this->languageManager, 'city'), "sortby" => CLocation::TABLE_NAME.".city" ),
											4 => array( "caption" => CLocation::GetAttributeName($this->languageManager, 'country'), "sortby" => CLocation::TABLE_NAME.".country" ),
											5 => array( "caption" => "Anzahl untergeordneter Läden", "sortby" => "" ),
											6 => array( "caption" => "Optionen", "sortby" => "" ),
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
		$objects=$this->manager->GetLocations($_SESSION["currentUser"], $searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for( $a=0; $a<count($objects); $a++){
			$return[$a][0]=$objects[$a]->GetName();
			$return[$a][1]=$objects[$a]->GetCompany()!=null ? $objects[$a]->GetCompany()->GetName() : "-";
			$return[$a][2]=GetLocationName($objects[$a]->GetLocationType());
			$return[$a][3]=$objects[$a]->GetCity();
			$return[$a][4]=$objects[$a]->GetCountry();
			$return[$a][5]=$objects[$a]->GetShopCount($this->db);
			$return[$a][6]=Array();
			$return[$a][6]["editUrl"] = "cLocation_edit.php5?editElement=".$objects[$a]->GetPKey();
			if( $objects[$a]->IsDeletable($this->db) ){
				$return[$a][6]["deleteUrl"] = "";
			}
			$return[$a][6]["pkey"] = $objects[$a]->GetPKey();
		}
		return $return;
	}
	
	/***************************************************************************
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 * @access public
	 ***************************************************************************/
	public function GetNumTotalEntrys($searchString){
		return $this->manager->GetLocationCount($_SESSION["currentUser"], $searchString);
	}
	
	/***************************************************************************
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 ***************************************************************************/
	public function DeleteEntries($deleteArray){
		for($a=0; $a<count($deleteArray); $a++){
			$object=new CLocation($this->db);
			if( $object->Load($deleteArray[$a], $this->db)===true ){
				$object->DeleteMe($this->db);
			}
		}
	}
	
} // CustomerLocationListData


?>