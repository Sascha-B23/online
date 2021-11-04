<?php

/**
 * ListData-Implementierung für Widerspruchspunkte
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class KuerzungsbetraegeListData extends ListData 
{

	/**
	 * Datenbankobjekt
	 * @var MySQLManager
	 */
	protected $db = null;
	
	/**
	 * Forderungsmanager-Objekt
	 * @var RSKostenartManager
	 */
	protected $manager = null;

	/**
	 * Widerspruchspunkt
	 * @var Widerspruchspunkt
	 */
	protected $widerspruchspunkt = null;
	
	/**
	 * Listenansicht für Prozess 'Realisierte Einsparung dokumentieren'
	 * @var boolean 
	 */
	protected $ansichtRealisiert = false;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param Widerspruchspunkt $widerspruchspunkt
	 */
	public function KuerzungsbetraegeListData($db, Widerspruchspunkt $widerspruchspunkt, $ansichtRealisiert = false)
	{
		$this->db = $db;
		$this->widerspruchspunkt = $widerspruchspunkt;
		global $rsKostenartManager;
		$this->manager = $rsKostenartManager;
		$this->ansichtRealisiert = $ansichtRealisiert;
		// Options Array setzen	
		$this->options["icon"] = "teilabrechnung.png";
		$this->options["icontext"] = "Kürzungsbeträge";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => "Kürzungsbetrag", "sortby" => "kuerzungsbetrag" ),
											1 => array( "caption" => "Ampelfarbe", "sortby" => "rating" ),
											2 => array( "caption" => "Typ", "sortby" => "type" ),
										);
		if ($this->ansichtRealisiert)
		{
			$this->data["datahead"][3] = array( "caption" => "Realisiert", "sortby" => "realisiert" );
		}
		$this->data["datahead"][] = array( "caption" => "Optionen", "sortby" => "" );
		
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
		$currency = $this->widerspruchspunkt->GetCurrency();
		$objects = $this->manager->GetKuerzungsbetraege($_SESSION["currentUser"], $this->widerspruchspunkt, $searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for ($a=0; $a<count($objects); $a++)
		{
			$return[$a][0]=$currency." ".HelperLib::ConvertFloatToLocalizedString($objects[$a]->GetKuerzungsbetrag());
			$return[$a][1]=Kuerzungsbetrag::GetRatingName($objects[$a]->GetRating());
			$return[$a][2]=Kuerzungsbetrag::GetTypeName($objects[$a]->GetType());
			if ($this->ansichtRealisiert)
			{
				$return[$a][3]=$objects[$a]->GetRealisiert()==Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES ? "Ja" : "Nein";
			}
			// Optionen
			$options = Array();
			$options["editUrl"] = "javascript:EditPos(".$objects[$a]->GetPKey().");";
			if( !$this->ansichtRealisiert && $objects[$a]->IsDeletable($this->db) )
			{
				$options["deleteUrl"] = "FM_FORM";
			}
			$options["pkey"] = $objects[$a]->GetPKey();
			$return[$a][] = $options;
		}
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @param string $searchString
	 * @return int
	 */
	public function GetNumTotalEntrys($searchString)
	{
		// Ist der Suchstring eine Zahl?
		$tempSearch=HelperLib::ConvertStringToFloat($searchString);
		if ($tempSearch!==false) $searchString = "".$tempSearch;
		return $this->manager->GetKuerzungsbetraegeCount($_SESSION["currentUser"], $this->widerspruchspunkt, $searchString);
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @param array $deleteArray
	 */
	public function DeleteEntries($deleteArray)
	{
		if (!$this->ansichtRealisiert)
		{
			for($a=0; $a<count($deleteArray); $a++)
			{
				$object=new Kuerzungsbetrag($this->db);
				if( $object->Load($deleteArray[$a], $this->db)===true )
				{
					$object->DeleteMe($this->db);
				}
			}
		}
	}
	
}
?>