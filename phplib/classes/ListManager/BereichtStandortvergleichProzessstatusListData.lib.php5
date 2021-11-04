<?php
/**
 * ListData-Implementierung für den Bereicht Standortvergleich Prozessstatus 
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class BereichtStandortvergleichProzessstatusListData extends ListData {
	
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * UserManager-Objekt
	 * @var RSKostenartManager
	 */
	protected $manager = null;
	
	/**
	 * 
	 * @var Array
	 */
	protected $rowsToShow = Array();
		
	/**
	 * 
	 * @var Array
	 */
	protected $dataList = Array();
	
	/**
	 * column to order by
	 * @var int 
	 */
	static public $orderBy = 0;
	
	/**
	 *
	 * @var int 
	 */
	static public $sortType = "str";
	
	/**
	 *
	 * @var string 
	 */
	static public $orderDirection = "ASC";
	
	/**
	* Konstruktor
	* @param DBManager $db
	*/
	public function BereichtStandortvergleichProzessstatusListData(DBManager $db, $rowConfig, $dataList)
	{
		$this->db = $db;
		global $rsKostenartManager, $lm;
		$this->languageManager = $lm;
		$this->manager = $rsKostenartManager;
		$this->rowConfig = $rowConfig;
		$this->dataList = $dataList;
		// Options Array setzen	
		$this->options["show_header"]=false;
		$this->options["icon"]="teilabrechnung.png";
		$this->options["icontext"]="Aufgaben";		
		// Header definieren
		$this->data["datahead"] = array();
		for( $a=0; $a<count($this->rowConfig); $a++)
		{
			if( $this->rowConfig[$a]["visible"] )$this->data["datahead"][]=$this->rowConfig[$a];
		}
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
		self::$orderBy=$this->data["datahead"][$orderBy]["sortby"];
		self::$sortType=$this->data["datahead"][$orderBy]["sorttype"];
		self::$orderDirection=$orderDirection;
		usort($this->dataList, "BereichtStandortvergleichProzessstatusListData::ListSort");
		// Ist der Suchstring eine Zahl?
		for( $a=0; $a<count($this->dataList); $a++)
		{
			for( $b=0; $b<count($this->rowConfig); $b++)
			{
				if( $this->rowConfig[$b]["visible"] )
				{
					$value = $this->dataList[$a][$this->rowConfig[$b]["sortby"]];
					if ($this->rowConfig[$b]["convert"]=="number")
					{
						$value = HelperLib::ConvertFloatToLocalizedString((float)$value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="currency")
					{
						$value = /*$this->dataList[$a]["currency"]." ".*/HelperLib::ConvertFloatToLocalizedString((float)$value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="currency_qm")
					{
						$value = HelperLib::ConvertFloatToLocalizedString((float)$value)/*." ".$this->dataList[$a]["currency"]."/m²"*/;
					}
					elseif ($this->rowConfig[$b]["convert"]=="pkey_string")
					{
						$value = ($value==(-1) ? "-" : $value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="date")
					{
						$value = ($value==0 ? "-" : date("d.m.Y", $value));
					}
					elseif ($this->rowConfig[$b]["convert"]=="status")
					{
						$value = str_replace("/", " / ", WorkflowManager::GetStatusName($this->db, $_SESSION["currentUser"], $value, $this->dataList[$a]["numWs"]>0 ? true : false));
					}
					elseif ($this->rowConfig[$b]["convert"]=="widerspruch_id")
					{
						$value = ($value==0 ? "-" : "WS".$value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="widerspruchspunkt_id")
					{
						$value = ($value==0 ? "-" : "WSP".$value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="kuerzungsbetrag_id")
					{
						$value = ($value==0 ? "-" : "KB".$value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="kuerzungsbetrag_rating")
					{
						$value = Kuerzungsbetrag::GetRatingName($value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="einstufung_rating")
					{
						$value = Kuerzungsbetrag::GetCategorizationName($value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="contract_id")
					{
						$value = ($value==0 ? "-" : "V".$value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="process_id")
					{
						$value = ($value==0 ? "-" : "P".$value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="processgroup_id")
					{
						$value = ($value==0 ? "-" : "GID".$value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="processgroup_id2")
					{
						$value = ($value==0 ? "" : "GID".$value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="teilabrechnung_id")
					{
						$value = ($value==0 ? "-" : Teilabrechnung::ID_PREFIX.$value);
					}
					elseif ($this->rowConfig[$b]["convert"]=="archievedStatus")
					{
						switch($value)
						{
							case Schedule::ARCHIVE_STATUS_ARCHIVED:
								$value = "Archiviert";
								break;
							case Schedule::ARCHIVE_STATUS_UPDATEREQUIRED:
								$value = "Aktuell (noch auf Stand zu bringen)";
								break;
							case Schedule::ARCHIVE_STATUS_UPTODATE:
								$value = "Aktuell (bereits auf Stand)";
								break;
						}
					}
					elseif ($this->rowConfig[$b]["convert"]=="bool")
					{
						switch($value)
						{
							case 0:
								$value = "-";
								break;
							case 1:
								$value = "Ja";
								break;
							case 2:
								$value = "Nein";
								break;
						}
					}
					elseif ($this->rowConfig[$b]["convert"]=="bool2")
					{
						switch($value)
						{
							case 0:
								$value = "Nein";
								break;
							case 1:
								$value = "Ja";
								break;
						}
					}
					elseif ($this->rowConfig[$b]["convert"]=="bool3")
					{
						switch($value)
						{
							case 0:
								$value = "";
								break;
							case 1:
								$value = "Ja";
								break;
							case 2:
								$value = "Nein";
								break;
						}
					}
					elseif ($this->rowConfig[$b]["convert"]=="mietvertrVollstaendig")
					{
						switch($value)
						{
							case Contract::MVC_UNKNOWN:
								$value = "-";
								break;
							case Contract::MVC_NO_ADDITIONAL_FILES:
								$value = "keine neuen Unterlagen hinzugefügt";
								break;
							case Contract::MVC_NEW_FILES_ADDED:
								$value = "neue Unterlagen hinuzgefügt";
								break;
							case Contract::MVC_NEW_FILES_AVAILABLE_NOT_ADDED:
								$value = "neue Unterlagen vorhanden aber nicht hinzugefügt";
								break;
						}
					}
					elseif ($this->rowConfig[$b]["convert"]=="prio")
					{
						$value = ($value==Schedule::PRIO_HIGH ? "Ja" : "Nein");
					}
					elseif($this->rowConfig[$b]["convert"]=="schriftverkehr_mit")
					{
						switch($value)
						{
							case NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_EIGENTUEMER:
								$value = "E";
								break;
							case NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_VERWALTER:
								$value = "V";
								break;
							case NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_ANWALT:
								$value = "A";
								break;
							case NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_KEINEM:
							default:
								$value = "-";
								break;
						}
					}
					elseif($this->rowConfig[$b]["convert"]=="abrechnungstage_gesamt")
					{
						$bis = $this->dataList[$a]["abrechnungszeitraumBis"];
						$von = $value;
						
						if($von == 0 || $bis == 0)
						{
							$value = "-";
						}
						else
						{
							//max_abrechnungszeitraum - min_abrechnungszeitraum = abrechnungstage gesamt
							$value = floor((($bis - $von) / (60*60*24)) + 1);
						}
					}
					elseif($this->rowConfig[$b]["convert"]=="plus_14_days")
					{
						$days14 = 60*60*24*14; //14 days in milliseconds
						$value = ($value==0 ? "-" : date("d.m.Y", $value + $days14));
					}
					elseif($this->rowConfig[$b]["convert"]=="form")
					{
						$value = $this->languageManager->GetString("PROCESS", "FORM_".(int)$value);
					}
					elseif($this->rowConfig[$b]["convert"]=="stagesPointsToBeClarified")
					{
						$value = $this->languageManager->GetString("PROCESS", "STAGE_POINTS_TO_BE_CLARIFIED_".(int)$value);
					}
					elseif($this->rowConfig[$b]["convert"]=="procedure")
					{
						$process = new ProcessStatus($this->db);
						if ($process->Load((int)$value, $this->db)===true)
						{
							$value = $process->GetProcedure($this->db);
						}
					}
					elseif($this->rowConfig[$b]["convert"]=="procedureType")
					{
						$process = new ProcessStatus($this->db);
						if ($process->Load((int)$value, $this->db)===true)
						{
							$value = $process->GetProcedureType($this->db);
						}
					}
					$return[$a][]=$value;
				}
			}
		}
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int
	 */
	public function GetNumTotalEntrys($searchString)
	{
		return count($this->dataList);
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 */
	public function DeleteEntries($deleteArray)
	{
	}
	
	/**
	 * Sortierfunktion für die Liste
	 */
	static public function ListSort($a, $b) 
	{
		$compareResult=0;
		if( BereichtStandortvergleichProzessstatusListData::$sortType=="num" )
		{
			if( (float)$a[BereichtStandortvergleichProzessstatusListData::$orderBy]==(float)$b[BereichtStandortvergleichProzessstatusListData::$orderBy] )$compareResult=0;
			else $compareResult=((float)$a[BereichtStandortvergleichProzessstatusListData::$orderBy]<(float)$b[BereichtStandortvergleichProzessstatusListData::$orderBy] ? -1 : 1);
		}
		else
		{
			$compareResult=strcmp($a[BereichtStandortvergleichProzessstatusListData::$orderBy], $b[BereichtStandortvergleichProzessstatusListData::$orderBy]);
		}
		return (BereichtStandortvergleichProzessstatusListData::$orderDirection=="ASC" ? 1 : -1) * $compareResult;
	}

}
?>