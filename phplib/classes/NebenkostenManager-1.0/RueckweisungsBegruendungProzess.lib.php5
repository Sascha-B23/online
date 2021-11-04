<?php
/**
 * Diese Klasse repräsentiert ein rueckweisungsBegruendungenProzess
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2019 Stoll von Gáti GmbH www.stollvongati.com
 */
class RueckweisungsBegruendungProzess extends DBEntry
{
	/**
	 * Typen
	 */
	const RWB_TYPE_ERFASSUNG = 0;

	/**
	 * Was wurde zurückgewiesen
	 * @var int
	 */
	protected $rwType = self::RWB_TYPE_ERFASSUNG;
	
	/**
	 * Nummer
	 * @var int
	 */
	protected $nummer = -1;
	
	/**
	 * Zeitpunkt
	 * @var int
	 */
	protected $datum = 0;

	/**
	 * Begründung
	 * @var string
	 */
	protected $begruendung = "";
		
	/**
	 * Zugehöriger Prozess
	 * @var ProcessStatus
	 */
	protected $process = null;
				
	/**
	 * Benutzer der den Abbruch angefordert hat
	 * @var User
	 */
	protected $user = null;
			
	/**
	 * Benutzer der den Abbruch abgelehnt/stattgegeben hat
	 * @var User
	 */
	protected $userRelease = null;
	
	/**
	 * Datenbankname und Spalten
	 * @var string
	 */
	const TABLE_NAME = "rueckweisungsBegruendungProzess";
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function RueckweisungsBegruendungProzess(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("rwType", "nummer", "datum", "begruendung", "processClass", "process", "user", "userRelease");
		$dbConfig->rowParam = Array("INT", "BIGINT", "BIGINT", "LONGTEXT", "VARCHAR(512)", "BIGINT", "BIGINT", "BIGINT");
		$dbConfig->rowIndex = Array("rwType", "nummer");
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1 Kein Widerspruch zugeordnet
	 *								-2 Kein User zugeordnet
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// Array mit zu speichernden Daten anlegen
		if ($this->process==null) return -1;
		if ($this->nummer==-1)
		{
			// Wenn die Nummer noch nicht gesetzt wurde, dies jetzt nachholen
			$data=$db->SelectAssoc("SELECT count(pkey) as count FROM ".self::TABLE_NAME." WHERE rwType=".$this->rwType." AND process=".$this->process->GetPKey()." AND processClass='".get_class($this->process)."'");
			$this->nummer=((int)$data[0]["count"])+1;
		}
		$rowName[]= "rwType";
		$rowData[]= $this->rwType;
		$rowName[]= "nummer";
		$rowData[]= $this->nummer;
		$rowName[]= "datum";
		$rowData[]= $this->datum;
		$rowName[]= "begruendung";
		$rowData[]= $this->begruendung;
		$rowName[]= "process";
		$rowData[]= $this->process->GetPKey();
		$rowName[]= "processClass";
		$rowData[]= get_class($this->process);
		$rowName[]= "user";
		$rowData[]= $this->user==null ? -1 : $this->user->GetPKey();
		$rowName[]= "userRelease";
		$rowData[]= $this->userRelease==null ? -1 : $this->userRelease->GetPKey();
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
		$this->rwType = (int)$data['rwType'];
		$this->nummer = $data['nummer'];
		$this->datum = $data['datum'];
		$this->begruendung = $data['begruendung'];
		if ($data['process']!=-1 && trim($data['processClass'])!='')
		{
			$this->process = new $data['processClass']($db);
			if ($this->process->Load($data['process'], $db)!==true) $this->process=null;
		}
		else
		{
			$this->process=null;
		}
		if ($data['user']!=-1)
		{
			$this->user = new User($db);
			if ($this->user->Load($data['user'], $db)!==true) $this->user=null;
		}
		else
		{
			$this->user=null;
		}
		if ($data['userRelease']!=-1)
		{
			$this->userRelease = new User($db);
			if ($this->userRelease->Load($data['userRelease'], $db)!==true) $this->userRelease=null;
		}
		else
		{
			$this->userRelease = null;
		}
		return true;
	}
	
	/**
	 * Gibt die Nummer des AbbruchProtokolls zurück
	 * @return int
	 */
	public function GetNummer()
	{
		return $this->nummer;
	}
	
	/**
	 * Setzt den Typ der Zurückweisung
	 * @param int $type
	 * @return boolean
	 */
	public function SetType($type)
	{
		if (!is_int($type)) return false;
		$this->rwType = $type;
		return true;
	}
	
	/**
	 * Gibt den Typ der Zurückweisung zurück
	 * @return int
	 */
	public function GetType()
	{
		return $this->rwType;
	}
	
	/**
	 * Setzt das Datum
	 * @param int $datum
	 * @return bool
	 */
	public function SetDatum($datum)
	{
		if (!is_int($datum)) return false;
		$this->datum = $datum;
		return true;
	}
	
	/**
	 * Gibt das Datum zurück
	 * @return int
	 */
	public function GetDatum()
	{
		return $this->datum;
	}
	
	/**
	 * Setzt die Begründung
	 * @param string $begruendung
	 * @return bool
	 */
	public function SetBegruendung($begruendung)
	{
		$this->begruendung = $begruendung;
		return true;
	}
	
	/**
	 * Gibt die Begründung zurück
	 * @return string
	 */
	public function GetBegruendung()
	{
		return $this->begruendung;
	}
	
	/**
	 * Gibt den zugehörigen Prozess zurück
	 * @return ProcessStatus
	 */
	public function GetProcess()
	{
		return $this->process;
	}
	
	/**
	 * Setzt den zugehörigen Prozess
	 * @param ProcessStatus $process
	 * @return bool
	 */
	public function SetProcess(ProcessStatus $process)
	{
		if ($process->GetPKey()==-1) return false;
		$this->process = $process;
		return true;
	}
			
	/**
	 * Gibt den zugehörigen Benutzer zurück
	 * @return User
	 */
	public function GetUser()
	{
		return $this->user;
	}
	
	/**
	 * Setzt den zugehörigen Benutzer
	 * @param User $user
	 * @return bool
	 */
	public function SetUser(User $user)
	{
		if ($user->GetPKey()==-1) return false;
		$this->user = $user;
		return true;
	}
			
	/**
	 * Gibt den Benutzer zurück, der den Abbruch abgelehnt/stattgegeben hat
	 * @return User
	 */
	public function GetUserRelease()
	{
		return $this->userRelease;
	}
	
	/**
	 * Setzt den Benutzer der den Abbruch abgelehnt/stattgegeben hat
	 * @param User $userRelease
	 * @return bool
	 */
	public function SetUserRelease(User $userRelease)
	{
		if ($userRelease->GetPKey()==-1) return false;
		$this->userRelease = $userRelease;
		return true;
	}
	
}
?>