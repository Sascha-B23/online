<?php
/**
 * Defines an Appointment
 *
 * @author ngerwien
 */
class Appointment 
{
	const MESSAGE_TYPE_UNDEFINED = 0;
	const MESSAGE_TYPE_NEW_INVITE = 1;
	const MESSAGE_TYPE_DATECHANGE = 2;
	const MESSAGE_TYPE_REPLY_ACCEPTED = 3;
	const MESSAGE_TYPE_REPLY_DECLINED = 4;
	
	const METHOD_REQUEST = "REQUEST"; //e.g. used to send a new appointment
	const METHOD_COUNTER = "COUNTER"; //e.g. used to suggest a new time for the appointment
	const METHOD_REPLY = "REPLY";
	const METHOD_CANCEL = "CANCEL";
	
	private $startDateTimestamp = 0;			//timestamp
	private $endDateTimestamp = 0;				//timestamp
	private $startDateCalendar = "";			//calendar format
	private $endDateCalendar = "";				//calendar format
	private $originalStartDateTimestamp = 0;	//timestamp
	private $originalEndDatetimestamp = 0;		//timestamp
	private $originalStartDateCalendar = "";	//calendar format
	private $originalEndDateCalendar = "";		//calendar format
	private $method = Appointment::METHOD_REQUEST;
	private $location = "";
	private $summary = "";
	private $uid = "";
	private $from = "";
	private $to = ""; //multiple attendees can be seperated with ","
	private $subject = "";
	private $sequence = 0;
	private $type = Appointment::MESSAGE_TYPE_UNDEFINED;
	private $dontConvertTimezone = FALSE;
	
	/**
	 * Set the method of the appointment
	 * 
	 * @parame string $method
	 */
	public function SetMethod($method)
	{
		$this->method = $method;
	}
	
	/**
	 * Get the method of the appointment
	 * 
	 * @return string
	 */
	public function GetMethod()
	{
		return $this->method;
	}
	
	/**
	 * Set the location of the appointment
	 * 
	 * @parame string $location
	 */
	public function SetLocation($location)
	{
		$this->location = $location;
	}
	
	/**
	 * Get the location of the appointment
	 * 
	 * @return string
	 */
	public function GetLocation()
	{
		return $this->location;
	}
	
	/**
	 * Set the summary of the appointment
	 * 
	 * @parame string $summary
	 */
	public function SetSummary($summary)
	{
		$this->summary = $summary;
	}
	
	/**
	 * Get the summary of the appointment
	 * 
	 * @return string
	 */
	public function GetSummary()
	{
		return $this->summary;
	}
	
	/**
	 * Set uid
	 * 
	 * @param string $uid
	 */
	public function SetUID($uid)
	{
		$this->uid = $uid;
	}
	
	/**
	 * Get uid
	 * 
	 * @return string
	 */
	public function GetUID()
	{
		return $this->uid;
	}
	
	/**
	 * Set the sender of the appointment
	 * 
	 * @param string $from
	 */
	public function SetFrom($from)
	{
		$this->from = $from;
	}
	
	/**
	 * Get the sender of the appointment
	 * 
	 * @return string
	 */
	public function GetFrom()
	{
		return $this->from;
	}
	
	/**
	 * Set the receiver(s) of the appointment
	 * 
	 * @param string $to multiple adresses can be separated by ','
	 */
	public function SetTo($to)
	{
		$this->to = $to;
	}
	
	/**
	 * Get the receiver(s) of the appointment
	 * 
	 * @return string multiple adresses can be separated by ','
	 */
	public function GetTo()
	{
		return $this->to;
	}
	
	/**
	 * Set the subject of the appointment
	 * 
	 * @parame string $subject
	 */
	public function SetSubject($subject)
	{
		$this->subject = $subject;
	}
	
	/**
	 * Get the subject of the appointment
	 * 
	 * @return string
	 */
	public function GetSubject()
	{
		return $this->subject;
	}
	
	/**
	 * Set the sequence number of the appointment
	 * 
	 * @param int $sequence
	 */
	public function SetSequence($sequence)
	{
		$this->sequence = $sequence;
	}
	
	/**
	 * Get the sequence number of the appointment
	 * 
	 * @return int
	 */
	public function GetSequence()
	{
		return $this->sequence;
	}
	
	/**
	 * Set the message type
	 * 
	 * @param int $type
	 */
	public function SetType($type)
	{
		$this->type = $type;
	}
	
	/**
	 * Returns the message type
	 * 
	 * @return int
	 */
	public function GetType()
	{
		return $this->type;
	}
	
	/**
	 * Tells if time zones should be converted to UTC+0 or not
	 * 
	 * @param bool $dontConvertTimeZone TRUE = no conversion
	 */
	public function DontConvertTimeZone($dontConvertTimeZone = TRUE)
	{
		$this->dontConvertTimezone = $dontConvertTimeZone;
	}
	
	/**
	 * Get the start date
	 * 
	 * @param bool $asTimeStamp
	 * @return type timestamp or calendar date string
	 */
	public function GetStartDate($asTimeStamp = TRUE)
	{
		if($asTimeStamp)
		{
			return $this->startDateTimestamp;
		}
		
		return $this->startDateCalendar;
	}
	
	/**
	 * Set Start Date
	 * 
	 * @param type $date if $startDate is an integer then a timestamp is assumed, otherwise a date string in calendar format
	 */
	public function SetStartDate($date)
	{
		if(is_int($date))
		{
			$this->startDateTimestamp = $date;
			$this->startDateCalendar = $this->ConvertTimeStampToCalendarDate($date);
		}
		else
		{
			$this->startDateTimestamp = $this->ConvertCalendarDateToTimeStamp($date);
			$this->startDateCalendar = $date;
		}
	}
	
	/**
	 * Get the end date
	 * 
	 * @param bool $asTimeStamp
	 * @return type timestamp or calendar date string
	 */
	public function GetEndDate($asTimeStamp = TRUE)
	{
		if($asTimeStamp)
		{
			return $this->endDateTimestamp;
		}
		
		return $this->endDateCalendar;
	}
	
	/**
	 * Set end Date
	 * 
	 * @param type $date if $startDate is an integer then a timestamp is assumed, otherwise a date string in calendar format
	 */
	public function SetEndDate($date)
	{
		if(is_int($date))
		{
			$this->endDateTimestamp = $date;
			$this->endDateCalendar = $this->ConvertTimeStampToCalendarDate($date);
		}
		else
		{
			$this->endDateTimestamp = $this->ConvertCalendarDateToTimeStamp($date);
			$this->endDateCalendar = $date;
		}
	}
	
	/**
	 * Get the original start date
	 * 
	 * @param bool $asTimeStamp
	 * @return type timestamp or calendar date string
	 */
	public function GetOriginalStartDate($asTimeStamp = TRUE)
	{
		if($asTimeStamp)
		{
			return $this->originalStartDatetimestamp;
		}
		
		return $this->originalStartDateCalendar;
	}
	
	/**
	 * Set original start date
	 * 
	 * @param type $date if $startDate is an integer then a timestamp is assumed, otherwise a date string in calendar format
	 */
	public function SetOriginalStartDate($date)
	{
		if(is_int($date))
		{
			$this->originalStartDatetimestamp = $date;
			$this->originalStartDateCalendar = $this->ConvertTimeStampToCalendarDate($date);
		}
		else
		{
			$this->originalStartDatetimestamp = $this->ConvertCalendarDateToTimeStamp($date);
			$this->originalStartDateCalendar = $date;
		}
	}
	
	/**
	 * Get the original end date
	 * 
	 * @param bool $asTimeStamp
	 * @return type timestamp or calendar date string
	 */
	public function GetOriginalEndDate($asTimeStamp = TRUE)
	{
		if($asTimeStamp)
		{
			return $this->originalEndDatetimestamp;
		}
		
		return $this->originalEndDateCalendar;
	}
	
	/**
	 * Set original end Date
	 * 
	 * @param type $date if $startDate is an integer then a timestamp is assumed, otherwise a date string in calendar format
	 */
	public function SetOriginalEndDate($date)
	{
		if(is_int($date))
		{
			$this->originalEndDatetimestamp = $date;
			$this->originalEndDateCalendar = $this->ConvertTimeStampToCalendarDate($date);
		}
		else
		{
			$this->originalEndDatetimestamp = $this->ConvertCalendarDateToTimeStamp($date);
			$this->originalEndDateCalendar = $date;
		}
	}
	
	/**
	 * Generates a string in VCalendar format which can be sent as mail
	 * 
	 * @return string
	 */
	public function CreateMailMessage()
	{
		$vcal = "BEGIN:VCALENDAR\n";
		
		$vcal .= $this->CreateVCalendarHeader();
		$vcal .= $this->CreateVCalendarTimeZone();
		$vcal .= $this->CreateVEvent();
		
		$vcal .= "END:VCALENDAR\n";
		
		return $vcal;
	}
	
	/**
	 * Creates the VCalendar header and sets the send method
	 * 
	 * @return string
	 */
	private function CreateVCalendarHeader()
	{
		$vcal .= "PRODID:-//Kim Online//Telefontermine//DE\n";
		$vcal .= "METHOD:$this->method\n";
		$vcal .= "VERSION:2.0\n";
		
		return $vcal;
	}
	
	/**
	 * Creates the timezone section of a vCalendar file
	 * 
	 * @return string
	 */
	private function CreateVCalendarTimeZone()
	{
		$vcal .= "BEGIN:VTIMEZONE\n";
		$vcal .= "TZID:W. Europe Standard Time\n";
		$vcal .= "BEGIN:STANDARD\n";
		$vcal .= "DTSTART:16010101T030000\n";
		$vcal .= "TZOFFSETFROM:+0200\n";
		$vcal .= "TZOFFSETTO:+0100\n";
		$vcal .= "RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10\n";
		$vcal .= "END:STANDARD\n";
		$vcal .= "BEGIN:DAYLIGHT\n";
		$vcal .= "DTSTART:16010101T020000\n";
		$vcal .= "TZOFFSETFROM:+0100\n";
		$vcal .= "TZOFFSETTO:+0200\n";
		$vcal .= "RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3\n";
		$vcal .= "END:DAYLIGHT\n";
		$vcal .= "END:VTIMEZONE\n";
		
		return $vcal;
	}
	
	/**
	 * Creates the event section of a vCalendar file
	 * 
	 * @return string
	 */
	private function CreateVEvent()
	{
		//create uid if not set
		if(empty($this->uid))
		{
			$this->uid = gmdate('Ymd').'T'.gmdate('His')."-".rand()."-AppointmentManager";
		}
		
		$vcal .= "BEGIN:VEVENT\n";
		$vcal .= $this->CreateAttendees();
		if ($this->summary != "") $vcal .= "SUMMARY;LANGUAGE=de:".$this->wrapText($this->summary)."\n";
		$vcal .= "DTSTART;TZID=W. Europe Standard Time:".$this->startDateCalendar."\n";
		$vcal .= "DTEND;TZID=W. Europe Standard Time:".$this->endDateCalendar."\n";
		if(!empty($this->originalStartDateCalendar) && !empty($this->originalEndDateCalendar))
		{
			$vcal .= "X-MS-OLK-ORIGINALSTART;TZID=W. Europe Standard Time:".$this->ConvertTimeStampToCalendarDate($this->originalStartDateCalendar)."\n";
			$vcal .= "X-MS-OLK-ORIGINALEND;TZID=W. Europe Standard Time:".$this->ConvertTimeStampToCalendarDate($this->originalEndDateCalendar)."\n";
		}
		$vcal .= "UID:$this->uid\n";
		
		if($this->method == Appointment::METHOD_REQUEST)
		{
			$vcal .= "CLASS:PUBLIC\n";
		$vcal .= "PRIORITY:5\n";
		}
		$vcal .= "DTSTAMP:".gmdate('Ymd').'T'.gmdate('His')."Z\n";
		if($this->method == Appointment::METHOD_REQUEST)
		{
			$vcal .= "TRANSP:OPAQUE\n";
			$vcal .= "STATUS:CONFIRMED\n";
		}
		$vcal .= "SEQUENCE:$this->sequence\n";
		if ($this->location != "") $vcal .= "LOCATION:$this->location\n";
		if($this->method == Appointment::METHOD_REQUEST)
		{
			$vcal .= $this->CreateVAlarm();
		}
		$vcal .= "END:VEVENT\n";
		
		return $vcal;
	}
	
	/**
	 * Creates a list of attendees and the organizer in vCalendar format depending on the method
	 * 
	 * @return string
	 */
	private function CreateAttendees()
	{		
		$vcal = "";
		if($this->method == Appointment::METHOD_REQUEST)
		{
			//create array of attendees
			$attendees = explode(",", $this->to);
			foreach($attendees as $attendee)
			{
				$attendee = trim($attendee);
			}
			
			//extract organizer name from $this->from if exists
			if(strpos($this->from, "<") !== FALSE)
			{
				$organizerSplit = explode("<", $this->from);
				$organizerName = trim($organizerSplit[0]);
				$organizer = trim(str_replace(">", "", $organizerSplit[1]));
				$vcal .= $this->wrapText("ORGANIZER;CN=$organizerName:MAILTO:$organizer\n");
			}
			else
			{
				$vcal .= "ORGANIZER:MAILTO:$this->from\n";
			}
			foreach ($attendees as $attendee)
			{
				$vcal .= $this->wrapText("ATTENDEE;ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:$attendee\n");
			}			
		}
		else if($this->method == Appointment::METHOD_COUNTER)
		{
			$vcal .= $this->wrapText("ATTENDEE;PARTSTAT=TENTATIVE:MAILTO:$this->from\n");
		}
		
		
		
		return $vcal;
	}
	
	/**
	 * Creates the alarm section of a vCalendar file
	 * 
	 * @return string
	 */
	private function CreateVAlarm()
	{
		$vcal .= "BEGIN:VALARM\n";
		$vcal .= "ACTION:DISPLAY\n";
		$vcal .= "DESCRIPTION:Reminder\n";
		$vcal .= "TRIGGER;RELATED=START:-PT15M\n";
		$vcal .= "END:VALARM\n";
		
		return $vcal;
	}
	
	/**
	 * Convert a timestamp to a calendar compliant date format
	 * 
	 * @param int $timestamp
	 * @return string calendar date
	 */
	public function ConvertTimeStampToCalendarDate($timestamp)
	{
		$dateTime = new DateTime();
		$dateTime->setTimestamp($timestamp);
		if($this->dontConvertTimezone === FALSE)
		{
			$dateTime->setTimezone(new DateTimeZone("UTC"));
		}
		
		return $dateTime->format('Ymd')."T".$dateTime->format('His')."Z";
	}
	
	/**
	 * Convert a calendar compliant date format to a timestamp
	 * 
	 * @param string $calendarDate
	 * @return int timestamp;
	 */
	public function ConvertCalendarDateToTimeStamp($calendarDate)
	{
		//remove letter 'T' and 'Z' from $calendarDate
		$formattedDate = str_replace("T", "", $calendarDate);
		$formattedDate = str_replace("Z", "", $formattedDate);
		
		if($this->dontConvertTimezone)
		{
			$dateTime = DateTime::createFromFormat("YmdHis", $formattedDate);
		}
		else
		{
			$dateTime = DateTime::createFromFormat("YmdHis", $formattedDate, new DateTimeZone("UTC"));
		}
		
		return $dateTime->getTimestamp();		
	}
	
	/**
	 * wraps lines in iCalendar convention
	 * 
	 * @param string $text
	 */
	private function wrapText($text)
	{
		return wordwrap($text, 75, "\n".chr(0x09), true);
	}
}
?>