<?php
/**
 * Schedule
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class Schedule extends DBEntry implements AttributeNameMaper
{
	/**
	 * Archive status
	 * @var int 
	 */
	const ARCHIVE_STATUS_ARCHIVED = 0;
	const ARCHIVE_STATUS_UPDATEREQUIRED = 1;
	const ARCHIVE_STATUS_UPTODATE = 2;
	
	/**
	 * Prio function
	 * @var int 
	 */
	const PRIO_FUNCTION_AUTO = 0;
	const PRIO_FUNCTION_MANUEL = 1;
	
	/**
	 * Prio
	 * @var int 
	 */
	const PRIO_NORMAL = 0;
	const PRIO_HIGH = 10;
	
	/**
	 * Geplanter Zeitpunkt der Abarbeitung
	 * @var int
	 */
	protected $dateAndTime = 0;
	
	/**
	 * Geplante Dauer der Abarbeitung
	 * @var int
	 */
	protected $plannedDuration = 0;
	
	
	/**
	 * Ist die Aufgabe geplant?
	 * @var bool
	 */
	protected $planned = false;
	
	/**
	 * Zeitpunkt an dem das Flag 'planned' zuletzt geändert wurde
	 * @var int
	 */
	protected $plannedTime = 0;
	
	
	/**
	 * Archive status
	 * @var int 
	 */
	protected $archiveStatus = Schedule::ARCHIVE_STATUS_UPTODATE;
	
	/**
	 * Commend of this schedule 
	 * @var string 
	 */
	protected $scheduleComment = "";
	
	/**
	 * Prio function
	 * @var int 
	 */
	protected $prioFunction = self::PRIO_FUNCTION_AUTO;
	
	/**
	 * Prio
	 * @var int 
	 */
	protected $prio = self::PRIO_NORMAL;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param DBConfig $dbConfig
	 */
	public function Schedule(DBManager $db, DBConfig $dbConfig) 
	{
		$configTemp = new DBConfig();
		$configTemp->rowName = Array("dateAndTime", "plannedDuration", "planned", "plannedTime", "archiveStatus", "scheduleComment", "prio", "prioFunction");
		$configTemp->rowParam = Array("BIGINT", "BIGINT", "INT", "BIGINT", "INT", "TEXT", "INT", "INT");
		$configTemp->rowIndex = Array("archiveStatus");
		$dbConfig->InsertRowsAt($configTemp->rowName, $configTemp->rowParam, $configTemp->rowIndex);
		parent::__construct($db, $dbConfig);
	}
		
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array $rowName Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array $rowData Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool|int Im Erfolgsfall true oder 
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "dateAndTime";
		$rowData[]= $this->dateAndTime;
		$rowName[]= "plannedDuration";
		$rowData[]= $this->plannedDuration;
		$rowName[]= "planned";
		$rowData[]= $this->planned ? 1 : 0;
		$rowName[]= "plannedTime";
		$rowData[]= $this->plannedTime;
		$rowName[]= "archiveStatus";
		$rowData[]= $this->archiveStatus;
		$rowName[]= "scheduleComment";
		$rowData[]= $this->scheduleComment;
		$rowName[]= "prio";
		$rowData[]= $this->prio;
		$rowName[]= "prioFunction";
		$rowData[]= $this->prioFunction;
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array $data Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->dateAndTime = $data['dateAndTime'];
		$this->plannedDuration = $data['plannedDuration'];
		$this->planned = $data['planned']==1 ? true : false;
		$this->plannedTime = $data['plannedTime'];
		$this->archiveStatus = (int)$data['archiveStatus'];
		$this->scheduleComment = $data['scheduleComment'];
		$this->prio = (int)$data['prio'];
		$this->prioFunction = (int)$data['prioFunction'];
		return true;
	}
		
	/**
	 * Setzt den geplanter Zeitpunkt der Abarbeitung
	 * @param int $dateAndTime
	 * @return bool
	 */
	public function SetDateAndTime($dateAndTime)
	{
		if (!is_int($dateAndTime)) return false;
		$this->dateAndTime = $dateAndTime;
		return true;
	}
	
	/**
	 * Gibt den geplanter Zeitpunkt der Abarbeitungzurück
	 * @return int
	 */
	public function GetDateAndTime()
	{
		return $this->dateAndTime;
	}
		
	/**
	 * Setzt die geplante Dauer
	 * @param int $plannedDuration
	 * @return bool
	 */
	public function SetPlannedDuration($plannedDuration)
	{
		if (!is_int($plannedDuration)) return false;
		$this->plannedDuration = $plannedDuration;
		return true;
	}
	
	/**
	 * Gibt die geplante Dauer zurück
	 * @return int
	 */
	public function GetPlannedDuration()
	{
		return $this->plannedDuration;
	}

	/**
	 * Setzt ob die Aufgabe geplant ist oder nicht
	 * @param bool $planned
	 * @return bool
	 */
	public function SetPlanned($planned)
	{
		if (!is_bool($planned)) return false;
		if ($planned==$this->planned) return true;
		$this->planned = $planned;
		$this->plannedTime = time();
		return true;
	}
	
	/**
	 * Gibt den Zeitpunkt an dem das Flag 'planned' zuletzt geändert wurde zurück
	 * @return int
	 */
	public function GetPlannedTime()
	{
		return $this->plannedTime;
	}
	
	/**
	 * Gibt zurück, ob die Aufgabe geplant ist oder nicht
	 * @return bool
	 */
	public function IsPlanned()
	{
		return $this->planned;
	}
	
	/**
	 * Set the archive status
	 * @param int $archiveStatus
	 * @return boolean
	 */
	public function SetArchiveStatus($archiveStatus)
	{
		if (!is_int($archiveStatus)) return false;
		$this->archiveStatus = $archiveStatus;
		return true;
	}
	
	/**
	 * Retrun the archive status
	 * @return int
	 */
	public function GetArchiveStatus()
	{
		return $this->archiveStatus;
	}
	
	/**
	 * Retrun the archive status
	 * @return int
	 */
	public function GetArchiveStatusName()
	{
		return $this->archiveStatus;
	}
	
	/**
	 * Set the comment of this schedule
	 * @param string $scheduleComment 
	 */
	public function SetScheduleComment($scheduleComment)
	{
		$this->scheduleComment = $scheduleComment;
	}
	
	/**
	 * Return the comment of this schedule
	 * @return string 
	 */
	public function GetScheduleComment()
	{
		return $this->scheduleComment;
	}
	
	/**
	 * Set the prio of this schedule
	 * @param int $prio 
	 * @return boolean 
	 */
	public function SetPrio($prio)
	{
		if (!is_int($prio)) return false;
		$this->prio = $prio;
		return true;
	}
	
	/**
	 * Return the prio of this schedule
	 * @param DBManager $db
	 * @return int
	 */
	public function GetPrio(DBManager $db)
	{
		if ($this->GetPrioFunction()==self::PRIO_FUNCTION_AUTO)
		{
			return $this->GetAutoPrio($db);
		}
		return $this->prio;
	}
	
	/**
	 * Auto calculate priority of process
	 * @param DBManager $db
	 * @return int
	 */
	public function GetAutoPrio(DBManager $db)
	{
		return self::PRIO_NORMAL;
	}
	
	/**
	 * Set the prio function of this schedule
	 * @param int $prioFunction 
	 * @return boolean 
	 */
	public function SetPrioFunction($prioFunction)
	{
		if (!is_int($prioFunction)) return false;
		$this->prioFunction = $prioFunction;
		return true;
	}
	
	/**
	 * Return the prio function of this schedule
	 * @return int
	 */
	public function GetPrioFunction()
	{
		return $this->prioFunction;
	}
	
	/**
	 * Return a human readable name for the requested attribute
	 * @param LanguageManager $languageManager
	 * @param string $attributeName
	 * @return string
	 */
	static public function GetAttributeName(ExtendedLanguageManager $languageManager, $attributeName)
	{
		switch($attributeName)
		{
			case "archiveStatus":
				return $languageManager->GetString('PROCESS', 'ARCHIVESTATUS');
			case "scheduleComment":
				return $languageManager->GetString('PROCESS', 'SCHEDULECOMMANT');
			case "scheduleCommentGroup":
				return $languageManager->GetString('PROCESS', 'SCHEDULECOMMANT_GROUP');
			case "prio":
				return $languageManager->GetString('PROCESS', 'PRIO');
			case "planned":
				return $languageManager->GetString('PROCESS', 'PLANNED');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}
}
?>