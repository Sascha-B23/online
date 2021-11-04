<?php
/**
 * ListData-Implementierung für realisierte Einsparung
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class RealisierteEinsparungWsListData extends ListData {
	
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * RSKostenartManager
	 * @var RSKostenartManager
	 */
	protected $manager = null;

	/**
	 * Widerspruch
	 * @var Widerspruch
	 */
	protected $widerspruch = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 * @param Widerspruch $widerspruch
	 */
	public function RealisierteEinsparungWsListData($db, Widerspruch $widerspruch)
	{
		$this->db = $db;
		$this->widerspruch=$widerspruch;
		global $rsKostenartManager;
		$this->manager = $rsKostenartManager;
		// Options Array setzen	
		$this->options["icon"]="teilabrechnung.png";
		$this->options["icontext"]="Widerspruchspunkte";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => "Überschrift", "sortby" => "title" ),
											1 => array( "caption" => "Kürzungsbetrag Grün", "sortby" => "kuerzungGruen" ),
											2 => array( "caption" => "Kürzungsbetrag Gelb", "sortby" => "kuerzungGelb" ),
											3 => array( "caption" => "Kürzungsbetrag Rot", "sortby" => "kuerzungRot" ),
											4 => array( "caption" => "Kürzungsbetrag Grau", "sortby" => "kuerzungGrau" ),
											5 => array( "caption" => "Optionen", "sortby" => "" ),
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
		// Ist der Suchstring eine Zahl?
		$tempSearch=HelperLib::ConvertStringToFloat($searchString);
		if( $tempSearch!==false )$searchString="".$tempSearch;
		$currency = $this->widerspruch->GetCurrency($this->db);
		$objects=$this->manager->GetWiderspruchspunkte($_SESSION["currentUser"], $searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage, $this->widerspruch);
		for( $a=0; $a<count($objects); $a++){
			$return[$a][0]=$objects[$a]->GetTitle();
			$return[$a][1]="<font color='#008800'>".$currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetKuerzungbetrag($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN))."</font>";
			$return[$a][2]="<font color='#dd8800'>".$currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetKuerzungbetrag($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB))."</font>";
			$return[$a][3]="<font color='#dd0000'>".$currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetKuerzungbetrag($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT))."</font>";
			$return[$a][4]="<font color='#555555'>".$currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetKuerzungbetrag($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU))."</font>";
			$return[$a][5]=Array();
			$return[$a][5]["editUrl"] = "javascript:EditPos(".$objects[$a]->GetPKey().");";
			$return[$a][5]["pkey"] = $objects[$a]->GetPKey();
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
		if( $tempSearch!==false )$searchString="".$tempSearch;
		return $this->manager->GetWiderspruchspunkteCount($_SESSION["currentUser"], $searchString, $this->widerspruch);
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 */
	public function DeleteEntries($deleteArray)
	{
	}
	
}
?>