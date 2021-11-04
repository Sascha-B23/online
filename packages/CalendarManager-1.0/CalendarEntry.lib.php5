<?php
/**
 * Calendar Entry
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2014 Stoll von Gáti GmbH www.stollvongati.com
 */
class CalendarEntry extends DBEntry
{
	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "calendarEntry";

	/**
	 * Start time
	 * @var integer
	 */
	protected $start = 0;
	
	/**
	 * End time
	 * @var integer
	 */
	protected $end = 0;
	
	/**
	 * Subject
	 * @var string 
	 */
	protected $subject = 0;
	
	/**
	 * Sequence number 
	 * @var int
	 */
	protected $sequence = -1;
	
	/**
	 * ID of the referencing object
	 * @var string
	 */
	protected $refernceId = '';
	
	/**
	 * User
	 * @var User
	 */
	protected $user = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 */
	public function CalendarEntry(DBManager $db) 
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("start", "end", "subject", "sequence", "refernceId", "user_rel");
		$dbConfig->rowParam = Array("BIGINT", "BIGINT", "TEXT", "INT", "TEXT", "BIGINT");
		$dbConfig->rowIndex = Array("user_rel");
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1 no User
	 *								-2 no start time
	 *								-3 no end time
	 *								-4 no subject
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if ($this->user==null) return -1;
		if ($this->start==0) return -2;
		if ($this->end==0) return -3;
		if (trim($this->subject)=='') return -4;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "start";
		$rowData[]= $this->start;
		$rowName[]= "end";
		$rowData[]= $this->end;
		$rowName[]= "subject";
		$rowData[]= $this->subject;
		$rowName[]= "sequence";
		$rowData[]= $this->sequence;
		$rowName[]= "refernceId";
		$rowData[]= $this->refernceId;
		$rowName[]= "user_rel";
		$rowData[]= ($this->user!=null ? $this->user->GetPKey() : -1);
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbank
	 * @param array $data Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->start = (int)$data['start'];
		$this->end = (int)$data['end'];
		$this->subject = $data['subject'];
		$this->sequence = (int)$data['sequence'];
		$this->refernceId = $data['refernceId'];
		$this->user = UserManager::GetUserByPkey($db, (int)$data['user_rel']);
		return true;
	}
	
	/**
	 * Get start time
	 * @return int
	 */
	public function GetStart()
	{
		return $this->start;
	}
	
	/**
	 * Set start time
	 * @param int $timestamp
	 * @return boolean
	 */
	public function SetStart($timestamp, $validate=true)
	{
		if (!is_int($timestamp)) return false;
		if ($validate && $this->end!=0 && $this->end<=$timestamp) return false;
		$this->start = $timestamp;
		return true;
	}
	
	/**
	 * Get end time
	 * @return int
	 */
	public function GetEnd()
	{
		return $this->end;
	}
	
	/**
	 * Set end time
	 * @param int $timestamp
	 * @return boolean
	 */
	public function SetEnd($timestamp, $validate=true)
	{
		if (!is_int($timestamp)) return false;
		if ($validate && $this->start!=0 && $this->start>=$timestamp) return false;
		$this->end = $timestamp;
		return true;
	}
	
	/**
	 * Get subject
	 * @return string
	 */
	public function GetSubject()
	{
		return $this->subject;
	}
	
	/**
	 * Set subject
	 * @param string $subject
	 * @return boolean
	 */
	public function SetSubject($subject)
	{
		$this->subject = $subject;
		return true;
	}
	
	/**
	 * Get sequence number
	 * @return int
	 */
	public function GetSequence()
	{
		return $this->sequence;
	}

	/**
	 * Set the sequence number
	 * @param int $number
	 * @return boolean
	 */
	public function SetSequence($number)
	{
		if (!is_int($number) || $number<$this->sequence) return false;
		$this->sequence = $number;
		return true;
	}
	
	/**
	 * Get ID of referencing object
	 * @return string
	 */
	public function GetRefernceId()
	{
		return $this->refernceId;
	}
	
	/**
	 * Set ID of referencing object
	 * @param string $id
	 * @return boolean
	 */
	public function SetRefernceId($id)
	{
		$this->refernceId = $id;
		return true;
	}
	
	/**
	 * Get user
	 * @return User
	 */
	public function GetUser()
	{
		return $this->user;
	}
	
	/**
	 * Set user
	 * @param User $user
	 * @return boolean
	 */
	public function SetUser(User $user)
	{
		if ($user->GetPKey()==-1) return false;
		$this->user = $user;
		return true;
	}
	
	/**
	 * Send a Appointment for this calendar entry
	 * @param DBManager $db
	 * @param AppointmentManager $appointmentManager
	 * @return boolean
	 */
	public function SendAppointement(DBManager $db, AppointmentManager $appointmentManager)
	{
		global $CALENDAR_MANAGER_EMAIL;
		if ($this->GetPKey()==-1) return false;
		$this->sequence++;
		$appointment = AppointmentFactory::CreateInvitation($this->start, $this->end, '', $this->subject, $this->subject, $CALENDAR_MANAGER_EMAIL, $this->user->GetEMail(), CalendarManager::GetUidFromId($this->GetPKey()), $this->sequence);
		if ($appointment==null) return false;
		$returnValue = $appointmentManager->SendAppointment($appointment);
		if ($returnValue===true)
		{
			return $db->UpdateByPkey(self::TABLE_NAME, Array('sequence'), Array($this->sequence), $this->GetPKey());
		}
		return $returnValue;
	}
	
	/**
	 * Send a Cancel Appointment for this calendar entry
	 * @param DBManager $db
	 * @param AppointmentManager $appointmentManager
	 * @return boolean
	 */
	public function CancelAppointement(DBManager $db, AppointmentManager $appointmentManager)
	{
		global $CALENDAR_MANAGER_EMAIL;
		if ($this->GetPKey()==-1) return false;
		$this->sequence++;
		$appointment = AppointmentFactory::CreateCancelMessage($this->start, $this->end, $this->subject, $CALENDAR_MANAGER_EMAIL, $this->user->GetEMail(), CalendarManager::GetUidFromId($this->GetPKey()), $this->sequence);
		if ($appointment==null) return false;
		$returnValue = $appointmentManager->SendAppointment($appointment);
		if ($returnValue===true)
		{
			return $db->UpdateByPkey(self::TABLE_NAME, Array('sequence'), Array($this->sequence), $this->GetPKey());
		}
		return $returnValue;
	}
	
}
?>