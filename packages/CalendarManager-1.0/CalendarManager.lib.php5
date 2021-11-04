<?php
require_once __DIR__.'/CalendarEntry.lib.php5';
/**
 * Calendar Manager
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2014 Stoll von Gáti GmbH www.stollvongati.com
 */
class CalendarManager implements IAppointmentObserver
{
	/**
	 * Database
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * LanguageManager
	 * @var ExtendedLanguageManager 
	 */
	protected $languageManager = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 */
	public function CalendarManager(DBManager $db, ExtendedLanguageManager $languageManager) 
	{
		$this->db = $db;
		$this->languageManager = $languageManager;
	}
	
	/**
	 * Return a CalendarEntry by its ID
	 * @param DBManager $db
	 * @param int $id
	 * @return CalendarEntry
	 */
	static public function GetCalendarEntryById(DBManager $db, $id)
	{
		if ($id==-1) return null;
		$object = new CalendarEntry($db);
		if ($object->Load((int)$id, $db)===true) return $object;
		return null;
	}

	/**
	 * Return a clendar UID from DB pkey
	 * @param int $id
	 * @return string
	 */
	static public function GetUidFromId($id)
	{
		return 'CE'.$id.'|fm-seybold.com/kim_online/';
	}
	
	/**
	 * Return DB pkey from clendar UID
	 * @param string $uid
	 * @return boolean
	 */
	static public function GetIdFromUid($uid)
	{
		preg_match("/^([a-zA-Z]+)([0-9]+)|/i", trim($uid), $matches);
		if ($matches[1]=='CE') return (int)$matches[2];
		return false;
	}
	
	/**
	 * Eine E-Mail ist eingetroffen
	 * @pararm AppointmentManager $appointmentManager
	 * @param Appointment $appointment
	 */
	public function Notify(AppointmentManager $appointmentManager, Appointment $appointment) 
	{
		switch($appointment->GetType())
		{
			case Appointment::MESSAGE_TYPE_DATECHANGE:
				$id = self::GetIdFromUid($appointment->GetUID());
				if ($id!==false)
				{
					$calendarEntry = self::GetCalendarEntryById($this->db, $id);
					if ($calendarEntry!=null)
					{
						$process = WorkflowManager::GetProcessStatusById($this->db, $calendarEntry->GetRefernceId());
						if ($process!=null)
						{
							$start = (int)$appointment->GetStartDate();
							$end = (int)$appointment->GetEndDate();
							//echo $calendarEntry->GetSequence();
							if ($start!=0 && $end!=0 && $appointment->GetSequence()>=$calendarEntry->GetSequence())
							{
								$newSequence = (int)$appointment->GetSequence()+1; //increase sequence for reply
								$calendarEntry->SetSequence($newSequence);
								$calendarEntry->SetStart($start, false);
								$calendarEntry->SetEnd($end, false);
								if ($process->UpdateTelefonterminByCalendarEntry($this->db, $calendarEntry))
								{
									$calendarEntry->Store($this->db);
									$appointmentDateChangeReply = AppointmentFactory::CreateReplyToDateSuggestion($appointment, $newSequence);
									$appointmentDateChangeReply->SetSubject($calendarEntry->GetSubject());
									$appointmentManager->SendAppointment($appointmentDateChangeReply);
								}
							}
						}
					}
				}
				break;
			case Appointment::MESSAGE_TYPE_REPLY_ACCEPTED:
			case Appointment::MESSAGE_TYPE_REPLY_DECLINED:
				$id = self::GetIdFromUid($appointment->GetUID());
				if ($id!==false)
				{
					$calendarEntry = self::GetCalendarEntryById($this->db, $id);
					if ($calendarEntry!=null)
					{
						//update sequence if necessary
						if ($appointment->GetSequence() > $calendarEntry->GetSequence())
						{
							$calendarEntry->SetSequence($appointment->GetSequence());
							$calendarEntry->Store($this->db);
						}
					}
				}
				break;
		}
		return null;
	}

}
?>