<?php

/**
 * ListData-Implementierung für Teilabrechnungspositionen (nur lesend)
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TeilabrechnungspositionReadOnlyListData extends ListData 
{
	
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 * @access protected
	 */
	protected $db = null;
	
	/**
	 * UserManager-Objekt
	 * @var RSKostenartManager
	 */
	protected $manager = null;

	/**
	 * UserManager-Objekt
	 * @var Teilabrechnung
	 */
	protected $teilabrechnung = null;
	
	/**
	* Konstruktor
	* @param DBManager $db
	* @param Teilabrechnung $teilabrechnung
	**/
	public function TeilabrechnungspositionReadOnlyListData(DBManager $db, Teilabrechnung $teilabrechnung){
		$this->db = $db;
		$this->teilabrechnung=$teilabrechnung;
		
		global $rsKostenartManager;
		$this->manager = $rsKostenartManager;
		// Options Array setzen	
		$this->options["icon"]="teilabrechnung.png";
		$this->options["icontext"]="Teilabrechnungspositionen";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => "Bez.", "sortby" => "bezeichnungTeilflaeche" ),
											1 => array( "caption" => "Kostenart lt. Abr.", "sortby" => "bezeichnungKostenart" ),
											2 => array( "caption" => "Kostenart SFM", "sortby" => "kostenartRS" ),
											3 => array( "caption" => "Gesamteinh.", "sortby" => "gesamteinheiten" ),
											4 => array( "caption" => "Einh. Kunde", "sortby" => "einheitKunde" ),
                                            5 => array( "caption" => "Pauschale", "sortby" => "pauschale" ),
											6 => array( "caption" => "Gesamt-betrag", "sortby" => "gesamtbetrag" ),
											7 => array( "caption" => "Betrag Kunde", "sortby" => "betragKunde" ),
											8 => array( "caption" => "Umlagef.", "sortby" => "umlagefaehig" ),
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
	 **/
	public function Search($searchString, $orderBy, $orderDirection="ASC", $numEntrysPerPage=20, $currentPage=0)
	{
		global $UM_GROUP_BASETYPE;
		global $SHARED_HTTP_ROOT;
		global $NKM_TEILABRECHNUNGSPOSITION_EINHEIT;
		$return=null;
		// Ist der Suchstring eine Zahl?
		$tempSearch=HelperLib::ConvertStringToFloat($searchString);
		if( $tempSearch!==false )$searchString="".$tempSearch;
		$currency = $this->teilabrechnung->GetCurrency();
		$objects=$this->manager->GetTeilabrechnungsposition($_SESSION["currentUser"], $searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage, $this->teilabrechnung);
		for( $a=0; $a<count($objects); $a++){
			$return[$a][0]=$objects[$a]->GetBezeichnungTeilflaech();
			$return[$a][1]=$objects[$a]->GetBezeichnungKostenart();
			$return[$a][2]=$objects[$a]->GetKostenartRS($this->db)==null ? "-" : $objects[$a]->GetKostenartRS($this->db)->GetName();
			$return[$a][3]=HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetGesamteinheiten())." ".$NKM_TEILABRECHNUNGSPOSITION_EINHEIT[$objects[$a]->GetGesamteinheitenEinheit()]["short"];
			$return[$a][4]=HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetEinheitKunde())." ".$NKM_TEILABRECHNUNGSPOSITION_EINHEIT[$objects[$a]->GetEinheitKundeEinheit()]["short"];
            $return[$a][5]=($objects[$a]->IsPauschale() ? "Ja" : "Nein");
            $return[$a][6]=$objects[$a]->IsPauschale() ? "-" : $currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetGesamtbetrag());
			$return[$a][7]=$currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetBetragKunde());
			$return[$a][8]=$objects[$a]->GetUmlagefaehig()==1 ? "Ja" : ($objects[$a]->GetUmlagefaehig()==2 ? "Nein" : "-");
		}
		$return[] = Array( 	0 => "<strong>Gesamt</strong>",
							1 => "",
							2 => "",
							3 => "",
							4 => "",
                            5 => "",
							6 => "<strong>".$currency." ".HelperLib::ConvertFloatToLocalizedString($this->teilabrechnung->GetSummeGesamtbetrag($this->db))."</strong>",
							7 => "<strong>".$currency." ".HelperLib::ConvertFloatToLocalizedString($this->teilabrechnung->GetSummeBetragKunde($this->db))."</strong>",
							8 => "",
						);
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 **/
	public function GetNumTotalEntrys($searchString){
		// Ist der Suchstring eine Zahl?
		$tempSearch=HelperLib::ConvertStringToFloat($searchString);
		if( $tempSearch!==false )$searchString="".$tempSearch;
		return $this->manager->GetTeilabrechnungspositionCount($_SESSION["currentUser"], $searchString, $this->teilabrechnung);
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 **/
	public function DeleteEntries($deleteArray){
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 **/
	public function CloneEntries($cloneArray){
	}
	
}
?>