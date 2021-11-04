<?php
/**
 * ListData-Implementierung für Widerspruchspunkte
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class WiderspruchspunktReadOnlyListData extends ListData 
{
	
	/**
	 * Datenbankobjekt
	 * @var MySQLManager
	 */
	protected $db = null;
	
	/**
	 * Forderungsmanager-Objekt
	 * @var Object
	 */
	protected $manager = null;

	/**
	 * Widerspruch-Objekt
	 * @var Widerspruch
	 */
	protected $widerspruch = null;
	
	/**
	 * Konstruktor
	 * @param MySQLManager $db	
	 * @param Widerspruch $widerspruch	
	 */
	public function WiderspruchspunktReadOnlyListData($db, $widerspruch)
	{
		$this->db = $db;
		$this->widerspruch=$widerspruch;
		global $rsKostenartManager;
		$this->manager = $rsKostenartManager;
		// Options Array setzen	
		$this->options["icon"]="teilabrechnung.png";
		$this->options["icontext"]="Widerspruchspunkte";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => "Rang", "sortby" => "rank" ),
											1 => array( "caption" => "Überschrift", "sortby" => "title" ),
											2 => array( "caption" => "Kürzungsbetrag Grün", "sortby" => "kuerzungGruen" ),
											3 => array( "caption" => "Kürzungsbetrag Gelb", "sortby" => "kuerzungGelb" ),
											4 => array( "caption" => "Kürzungsbetrag Rot", "sortby" => "kuerzungRot" ),
											5 => array( "caption" => "Kürzungsbetrag Grau", "sortby" => "kuerzungGrau" ),
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
		if ($tempSearch!==false) $searchString="".$tempSearch;
		$currency = $this->widerspruch->GetCurrency($this->db);
		$objects = $this->manager->GetWiderspruchspunkte($_SESSION["currentUser"], $searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage, $this->widerspruch);
		for( $a=0; $a<count($objects); $a++)
		{
			$return[$a][0]=$objects[$a]->GetRank();
			$return[$a][1]=$objects[$a]->GetTitle();
			$return[$a][2]="<font color='#008800'>".$currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetKuerzungbetrag($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN))."</font>";
			$return[$a][3]="<font color='#dd8800'>".$currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetKuerzungbetrag($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB))."</font>";
			$return[$a][4]="<font color='#dd0000'>".$currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetKuerzungbetrag($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT))."</font>";
			$return[$a][5]="<font color='#888888'>".$currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetKuerzungbetrag($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU))."</font>";
		}
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 */
	public function GetNumTotalEntrys($searchString)
	{
		// Ist der Suchstring eine Zahl?
		$tempSearch=HelperLib::ConvertStringToFloat($searchString);
		if ($tempSearch!==false) $searchString="".$tempSearch;
		return $this->manager->GetWiderspruchspunkteCount($_SESSION["currentUser"], $searchString, $this->widerspruch);
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 */
	public function DeleteEntries($deleteArray)
	{
	}
	
}
?>