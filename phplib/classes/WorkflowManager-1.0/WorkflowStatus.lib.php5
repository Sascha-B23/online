<?php
/**
 * Basis-Klasse des Workflow-Status
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class WorkflowStatus extends Schedule implements AttributeNameMaper
{	

	/**
	 * Aktueller Status
	 * @var int
	 */
	protected $currentStatus = 0;
	
	/**
	 * Last time the status of this process has changed
	 * @var int 
	 */
	protected $lastStatusChange = 0;
	
	/**
	 * Letzter Status
	 * @var int
	 */
	protected $lastStatus = -1;
	
	/**
	 * Status in den zurückgesprungen wird
	 * @var int
	 */
	protected $jumpBackStatus = 0;
		
	/**
	 * Status von dem automatisch zurückgesprungen wurde
	 * @var int
	 */
	protected $autoJumpFromStatus = -1;
	
	/**
	 * Deadline
	 * @var timestamp
	 */
	protected $deadline = 0;
	
	/**
	 * Zusätzliche Informationen
	 * @var string
	 */
	protected $additionalInfo = "";
	
	/**
	 * Temporär zugewiesener User
	 * @var User
	 */
	protected $zuweisungUser = null;

	/**
	 * Zeit der Fertigstellung des Prozess
	 * @var timestamp
	 */
	protected $finished = 0;
		
	/**
	 * Wurde der Kunde per EMail benachrichtigt (für Cronjob wichtig)
	 * @var int
	 */
	protected $customerMailSend = 0;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 * @param DBConfig $dbConfig
	 */
	public function WorkflowStatus(DBManager $db, DBConfig $dbConfig)
	{
		$configTemp = new DBConfig();
		$configTemp->rowName = Array("currentStatus", "lastStatusChange", "lastStatus", "jumpBackStatus", "autoJumpFromStatus", "deadline", "additionalInfo", "zuweisungUser", "finished", "customerMailSend");
		$configTemp->rowParam = Array("INT", "BIGINT", "INT", "INT", "INT", "BIGINT", "LONGTEXT", "BIGINT", "BIGINT", "BIGINT");
		$configTemp->rowIndex = Array("currentStatus", "lastStatusChange", "deadline", "zuweisungUser");
		$dbConfig->InsertRowsAt($configTemp->rowName, $configTemp->rowParam, $configTemp->rowIndex);
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array $rowName Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array $rowData Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool|int
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "currentStatus";
		$rowData[]= $this->currentStatus;
		$rowName[]= "lastStatusChange";
		$rowData[]= $this->lastStatusChange;
		$rowName[]= "lastStatus";
		$rowData[]= $this->lastStatus;
		$rowName[]= "jumpBackStatus";
		$rowData[]= $this->jumpBackStatus;
		$rowName[]= "autoJumpFromStatus";
		$rowData[]= $this->autoJumpFromStatus;
		$rowName[]= "deadline";
		$rowData[]= $this->deadline;
		$rowName[]= "additionalInfo";
		$rowData[]= $this->additionalInfo;
		$rowName[]= "zuweisungUser";
		$rowData[]= $this->zuweisungUser==null ? -1 : $this->zuweisungUser->GetPKey();
		$rowName[]= "finished";
		$rowData[]= $this->finished;
		$rowName[]= "customerMailSend";
		$rowData[]= $this->customerMailSend;
		return Schedule::BuildDBArray($db, $rowName, $rowData);
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array $data Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->currentStatus = (int)$data['currentStatus'];
		$this->lastStatusChange = (int)$data['lastStatusChange'];
		$this->lastStatus = (int)$data['lastStatus'];
		$this->jumpBackStatus = (int)$data['jumpBackStatus'];
		$this->autoJumpFromStatus = (int)$data['autoJumpFromStatus'];
		if( $this->autoJumpFromStatus==0 )$this->autoJumpFromStatus=-1;
		$this->deadline = (int)$data['deadline'];
		$this->additionalInfo = $data['additionalInfo'];
		if( $data['zuweisungUser']!=-1 )
		{
			$this->zuweisungUser = new User($db);
			if( !$this->zuweisungUser->Load($data['zuweisungUser'], $db) )$this->zuweisungUser=null;
		}
		else
		{
			$this->zuweisungUser=null;
		}
		$this->finished = $data['finished'];
		$this->customerMailSend = $data['customerMailSend'];
		return Schedule::BuildFromDBArray($db, $data);
	}
	
	/**
	 * Setzt den aktueller Status
	 * @param int $currentStatus
	 * @return bool
	 */
	public function SetCurrentStatus($currentStatus)
	{
		if (!is_int($currentStatus)) return false;
		$this->lastStatus=$this->GetCurrentStatus();
		$this->currentStatus=$currentStatus;
		$this->autoJumpFromStatus=-1;
		$this->customerMailSend=0;
		$this->lastStatusChange = time();
		$this->SetPlanned(false);
		return true;
	}
	
	/**
	 * Gibt den aktueller Status zurück
	 * @return int
	 */
	public function GetCurrentStatus()
	{
		return $this->currentStatus;
	}
	
	/**
	 * Return the last time the status of this process has changed
	 * @return int
	 */
	public function GetLastStatusChange()
	{
		return $this->lastStatusChange;
	}
	
	/**
	 * Gibt den vorherigen Status zurück
	 * @return int
	 */
	public function GetLastStatus()
	{
		return $this->lastStatus;
	}
	
	/**
	 * clear last status
	 * @return int
	 */
	public function ClearLastStatus()
	{
		$this->lastStatus = -1;
	}
	
	/**
	 * Setzt den JumpBackStatus
	 * @param int $jumpBackStatus
	 * @return bool
	 */
	public function SetJumpBackStatus($jumpBackStatus)
	{
		if( !is_int($jumpBackStatus) )return false;
		$this->jumpBackStatus=$jumpBackStatus;
		return true;
	}
	
	/**
	 * Gibt den JumpBackStatus zurück
	 * @return int
	 */
	public function GetJumpBackStatus()
	{
		return $this->jumpBackStatus;
	}

	/**
	 * Setzt den Status von dem automatisch zurückgesprungen wurde
	 * @param int $autoJumpFromStatus
	 * @return bool
	 */
	public function SetAutoJumpFromStatus($autoJumpFromStatus)
	{
		if (!is_int($autoJumpFromStatus)) return false;
		$this->autoJumpFromStatus = $autoJumpFromStatus;
		return true;
	}
	
	/**
	 * Gibt den Status, von dem automatisch zurückgesprungen wurde, zurück
	 * @return int
	 */
	public function GetAutoJumpFromStatus()
	{
		return $this->autoJumpFromStatus;
	}
	
	/**
	 * Setzt die Deadline
	 * @param int $deadline
	 * @return bool
	 */
	public function SetDeadline($deadline)
	{
		if( !is_numeric($deadline) || ((int)$deadline)!=$deadline )return false;
		$this->deadline=(int)$deadline;
		return true;
	}
	
	/**
	 * Gibt die Deadline zurück
	 * @return int
	 */
	public function GetDeadline()
	{
		return $this->deadline;
	}
	
	/**
	 * Setzt die Zeit, bei der der Kunde per EMail benachrichtigt wurde
	 * @param int $customerMailSend
	 * @return bool
	 */
	public function SetCustomerMailSendTime($customerMailSend)
	{
		if( !is_numeric($customerMailSend) || ((int)$customerMailSend)!=$customerMailSend )return false;
		$this->customerMailSend=(int)$customerMailSend;
		return true;
	}
	
	/**
	 * Gibt die Zeit zurück, bei der der Kunde per EMail benachrichtigt wurde
	 * @return int
	 */
	public function GetCustomerMailSendTime()
	{
		return $this->customerMailSend;
	}
	
	/**
	 * Setzt AdditionalInfo
	 * @param string $additionalInfo
	 * @return bool
	 */
	public function SetAdditionalInfo($additionalInfo)
	{
		$this->additionalInfo=$additionalInfo;
		return true;
	}
	
	/**
	 * Gibt AdditionalInfo zurück
	 * @return string
	 */
	public function GetAdditionalInfo()
	{
		return $this->additionalInfo;
	}
		
	/**
	 * Gibt ZuweisungUser zurück
	 * @return User
	 */
	public function GetZuweisungUser()
	{
		return $this->zuweisungUser;
	}
	
	/**
	 * Setzt ZuweisungUser
	 * @param User $zuweisungUser
	 * @return bool
	 */
	public function SetZuweisungUser(User $zuweisungUser=null)
	{
		if ($zuweisungUser==null)
		{
			$this->zuweisungUser=null; 
			return true;
		}
		if ($zuweisungUser->GetPKey()==-1) return false;
		$this->zuweisungUser=$zuweisungUser;
		return true;
	}

	/**
	 * Setzt den Abschlußzeitpunkt
	 * @param int $finished
	 * @return bool
	 */
	public function SetFinished($finished)
	{
		if( !is_int($finished) )return false;
		$this->finished=$finished;
		return true;
	}
	
	/**
	 * Gibt den Abschlußzeitpunkt zurück
	 * @return int
	 */
	public function GetFinished()
	{
		return $this->finished;
	}
	
	/**
	 * Gibt zurück, ob der Prozess abgeschlossen ist
	 * @return bool
	 */
	public function IsFinished()
	{
		// Status 7 und 26 sind Endstadien
		if( $this->GetFinished()>0 && ($this->GetCurrentStatus()==7 || $this->GetCurrentStatus()==26) )return true;
		return false;
	}
	
	/**
	 * Gibt die Dauer der Prüfung zurück
	 * @return int
	 */
	public function GetDuration(DBManager $db)
	{
		if( $this->IsFinished() )return ($this->GetFinished()-$this->GetCreationTime());
		return (time()-$this->GetCreationTime());
	}
		
	/**
	 * Gibt den Typ des Status zurück
	 * @return int
	 */
	public abstract function GetStatusTyp();

	
	/**
	 * Überführt in den nächsten Status 
	 * @param int $branch
	 * @return bool
	 */
	public abstract function GotoNextStatus($branch=-1);

	/**
	 * Überführt in den vorherigen Status 
	 * @return bool
	 */
	public abstract function GotoPreviousStatus();
	
	/**
	 * Gibt den Typ des Status zurück
	 * @param User $currentUser
	 * @param DBManager $db
	 * @return string
	 */
	public abstract function GetCurrentStatusName(User $currentUser, DBManager $db);
	
	
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
			case "currentStatus":
				return $languageManager->GetString('PROCESS', 'CURRENT_STATUS');
			case "deadline":
				return $languageManager->GetString('PROCESS', 'DEADLINE');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}
	
}
?>