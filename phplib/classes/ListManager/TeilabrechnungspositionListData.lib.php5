<?php
/**
 * ListData-Implementierung für Teilabrechnungspositionen
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TeilabrechnungspositionListData extends ListData 
{	
	/**
	 * Datenbankobjekt
	 * @var MySQLManager
	 * @access protected
	 */
	protected $db = null;
	
	/**
	 * UserManager-Objekt
	 * @var Object
	 * @access protected
	 */
	protected $manager = null;

	/**
	 * UserManager-Objekt
	 * @var Object
	 * @access protected
	 */
	protected $teilabrechnung = null;
	
	/**
	 * Konstruktor
	 * @param object	$db	Datenbank-Objekt
	 * @access public
	 */
	public function TeilabrechnungspositionListData($db, $teilabrechnung)
	{
		$this->db = $db;
		$this->teilabrechnung=$teilabrechnung;
		global $rsKostenartManager;
		$this->manager = $rsKostenartManager;
		// Options Array setzen	
		$this->options["icon"]="teilabrechnung.png";
		$this->options["icontext"]="Positionen";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => "Vollständig", "sortby" => "" ),
											1 => array( "caption" => "Teilfläche", "sortby" => "bezeichnungTeilflaeche" ),
											2 => array( "caption" => "Bezeichnung lt. Abrechnung", "sortby" => "bezeichnungKostenart" ),
											3 => array( "caption" => "Bezeichnung lt. SFM", "sortby" => "kostenartRS" ),
                                            4 => array( "caption" => "Pauschale", "sortby" => "pauschale" ),
											5 => array( "caption" => "Gesamtbetrag", "sortby" => "gesamtbetrag" ),
											6 => array( "caption" => "Betrag Kunde", "sortby" => "betragKunde" ),
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
	 * @access public
	 */
	public function Search($searchString, $orderBy, $orderDirection="ASC", $numEntrysPerPage=20, $currentPage=0)
	{
		global $UM_GROUP_BASETYPE;
		global $SHARED_HTTP_ROOT;
		$return=null;
		// Ist der Suchstring eine Zahl?
		$tempSearch=HelperLib::ConvertStringToFloat($searchString);
		if ($tempSearch!==false) $searchString="".$tempSearch;
		$currency = $this->teilabrechnung->GetCurrency();
		$objects=$this->manager->GetTeilabrechnungsposition($_SESSION["currentUser"], $searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage, $this->teilabrechnung);
		for ($a=0; $a<count($objects); $a++)
		{
			$return[$a][0]=$objects[$a]->IsComplete($this->db) ? "Ja" : "Nein";
			$return[$a][1]=$objects[$a]->GetBezeichnungTeilflaech();
			$return[$a][2]=$objects[$a]->GetBezeichnungKostenart();
			$return[$a][3]=$objects[$a]->GetKostenartRS($this->db)==null ? "-" : $objects[$a]->GetKostenartRS($this->db)->GetName();
            $return[$a][4]=($objects[$a]->IsPauschale() ? "Ja" : "Nein");
			$return[$a][5]=$objects[$a]->IsPauschale() ? "-" : $currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetGesamtbetrag());
			$return[$a][6]=$currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetBetragKunde());
			$return[$a][7]=Array();
			$return[$a][7]["editUrl"] = "javascript:EditPos(".$objects[$a]->GetPKey().");";
			$return[$a][7]["cloneUrl"] = "FM_FORM";
			if ($objects[$a]->IsDeletable($this->db))
			{
				$return[$a][7]["deleteUrl"] = "FM_FORM";
			}
			$return[$a][7]["pkey"] = $objects[$a]->GetPKey();
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
		// Ist der Suchstring eine Zahl?
		$tempSearch=HelperLib::ConvertStringToFloat($searchString);
		if ($tempSearch!==false) $searchString="".$tempSearch;
		return $this->manager->GetTeilabrechnungspositionCount($_SESSION["currentUser"], $searchString, $this->teilabrechnung);
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 */
	public function DeleteEntries($deleteArray)
	{
		for ($a=0; $a<count($deleteArray); $a++)
		{
			$object=new Teilabrechnungsposition($this->db);
			if ($object->Load($deleteArray[$a], $this->db)===true)
			{
				$object->DeleteMe($this->db);
			}
		}
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 */
	public function CloneEntries($cloneArray)
	{
		for ($a=0; $a<count($cloneArray); $a++)
		{
			$this->teilabrechnung->CloneTeilabrechnungsposition($this->db, (int)$cloneArray[$a]);
		}
	}
	
}
?>