<?php

/**
 * Parse values from a string in vcalendar format
 *
 * @author ngerwien
 */
class AppointmentParser {
	const SECTION_VCALENDAR = "VCALENDAR";
	const SECTION_EVENT = "VEVENT";
	
	protected $rowIndex;
	protected $rows;
	
	protected $file = "";
	protected $parsedAppointment;

	protected $attendeePartstat = "";
	
	/**
	 * Parse and store a vCalendar file
	 * 
	 * @param string $file
	 * @return Appointment A parsed appointment or FALSE if failed
	 */
	public function Parse($file)
	{
		$this->file = $file;
		$this->parsedAppointment = new Appointment();
		$this->parsedAppointment->DontConvertTimeZone();
		
		//check if it is a vcalendar file
		if(strpos($file, "BEGIN:VCALENDAR") === FALSE
			|| strpos($file, "END:VCALENDAR") === FALSE)
		{
			return FALSE;
		}
		
		//iterate over each row
		$this->rowIndex = 0;
		$this->sectionStack = array();
		$this->rows = array();
		$rows = explode("\n", $file);
		//print_r($rows);
		foreach ($rows as $row) 
		{
			//echo $row." [".substr($row, 0, 1)." - ".dechex(ord(substr($row, 0, 1)))."]<br />";
			if (ord(substr($row, 0, 1))==0x20 || ord(substr($row, 0, 1))==0x09)
			{
				//echo "XXXX";
				$this->rows[count($this->rows)-1].=rtrim(substr($row, 1));
			}
			else
			{
				$this->rows[] = rtrim($row);
			}
		}
		//print_r($this->rows);
		//$this->rows = $rows;
		while(($this->rowIndex < count($this->rows)) && $this->parsedAppointment !== FALSE)
		{
			$trimmedRow = trim($this->rows[$this->rowIndex]);
			$trimmedRowUpper = strtoupper($trimmedRow);
			
			if(strpos($trimmedRowUpper, "END:VCALENDAR") !== FALSE)
			{
				break;
			}
			else if(strpos($trimmedRowUpper, "METHOD:") !== FALSE)
			{
				$value = explode(":", $trimmedRow)[1];
				$this->parsedAppointment->SetMethod($value);
			}
			else if(strpos($trimmedRowUpper, "BEGIN:VTIMEZONE") !== FALSE)
			{
				$this->ParseTimeZone();
			}
			else if(strpos($trimmedRowUpper, "BEGIN:VEVENT") !== FALSE)
			{
				$this->ParseEvent();
			}
			
			$this->rowIndex++;
		}		
		
		$this->determineType();
		
		return $this->parsedAppointment;
	}
	
	/**
	 * Determines the type of the parsed appointment
	 */
	protected function determineType()
	{
		if($this->parsedAppointment->GetMethod() == Appointment::METHOD_COUNTER)
		{
			$this->parsedAppointment->SetType(Appointment::MESSAGE_TYPE_DATECHANGE);
		}
		elseif ($this->parsedAppointment->GetMethod() == Appointment::METHOD_REPLY && $this->attendeePartstat === "DECLINED")
		{
			$this->parsedAppointment->SetType(Appointment::MESSAGE_TYPE_REPLY_DECLINED);
		}
		elseif ($this->parsedAppointment->GetMethod() == Appointment::METHOD_REPLY && $this->attendeePartstat === "ACCEPTED")
		{
			$this->parsedAppointment->SetType(Appointment::MESSAGE_TYPE_REPLY_ACCEPTED);
		}
		else
		{
			$this->parsedAppointment->SetType(Appointment::MESSAGE_TYPE_UNDEFINED);
		}
	}
	
	/**
	 * Helper Function to parse the timezone of a vcalendar
	 */
	protected function  ParseTimeZone()
	{
		while(($this->rowIndex < count($this->rows)) && $this->parsedAppointment !== FALSE)
		{
			$trimmedRow = trim($this->rows[$this->rowIndex]);
			$trimmedRowUpper = strtoupper($trimmedRow);
			
			if(strpos($trimmedRowUpper, "END:VTIMEZONE") !== FALSE)
			{
				return;
			}
			
			$this->rowIndex++;
		}
	}
	
	/**
	 * Helper Function to parse the event part of a vcalendar
	 */
	protected function  ParseEvent()
	{
		while(($this->rowIndex < count($this->rows)) && $this->parsedAppointment !== FALSE)
		{			
			$trimmedRow = trim($this->rows[$this->rowIndex]);
			$trimmedRowUpper = strtoupper($trimmedRow);
			
			if(strpos($trimmedRowUpper, "END:VEVENT") !== FALSE)
			{
				return;
			}
			else if(strpos($trimmedRowUpper, "BEGIN:VALARM") !== FALSE)
			{
				$this->ParseAlarm();
			}
			else if(strpos($trimmedRowUpper, "ORGANIZER") !== FALSE)
			{
				$value = explode("MAILTO:", $trimmedRow)[1];
				$this->parsedAppointment->SetFrom($value);
			}
			else if(strpos($trimmedRowUpper, "ATTENDEE") !== FALSE)
			{
				//attendee is sometimes cut to the next line by outlook (there will be a whitespace at the beginning of the next line
				//if thats the case merge the next row
				$nextRow = $this->rows[$this->rowIndex+1];
				if($nextRow != ltrim($nextRow))
				{
					$trimmedRow .= trim($this->rows[$this->rowIndex+1]);
				}
				
				$value = explode("MAILTO:", $trimmedRow)[1];
				$parsedAppointmentTo = $this->parsedAppointment->GetTo();
				if(empty($parsedAppointmentTo) !== TRUE)
				{
					$this->parsedAppointment->SetTo($this->parsedAppointment->GetTo().",".$value);
				}
				else
				{
					$this->parsedAppointment->SetTo($value);
				}
				
				//get partstat if set
				if(strpos($trimmedRowUpper, "PARTSTAT=") !== FALSE)
				{
					$partStat = explode("PARTSTAT=", $trimmedRowUpper)[1];
					
					//partstat can be closed with ";" or ":"
					$semicolonPos = strpos($partStat, ";");
					$colonPos = strpos($partStat, ":");
					if($semicolonPos !== FALSE && $semicolonPos < $colonPos)
					{
						$partStat = explode(";", $partStat)[0];
					}
					else
					{
						$partStat = explode(":", $partStat)[0];
					}
					$this->attendeePartstat = $partStat;
				}
			}
			else if(strpos($trimmedRowUpper, "SUMMARY") !== FALSE)
			{
				$split = explode(":", $trimmedRow);
				$value = $split[count($split) - 1];
				$this->parsedAppointment->SetSummary($value);
			}
			else if(strpos($trimmedRowUpper, "DTSTART") !== FALSE)
			{
				$split = explode(":", $trimmedRow);
				$value = $split[count($split) - 1];
				$this->parsedAppointment->SetStartDate($value);
			}
			else if(strpos($trimmedRowUpper, "DTEND") !== FALSE)
			{
				$split = explode(":", $trimmedRow);
				$value = $split[count($split) - 1];
				$this->parsedAppointment->SetEndDate($value);
			}
			else if(strpos($trimmedRowUpper, "UID:") !== FALSE)
			{
				$split = explode(":", $trimmedRow);
				$value = $split[count($split) - 1];
				$this->parsedAppointment->SetUID($value);
			}
			else if(strpos($trimmedRowUpper, "SEQUENCE:") !== FALSE)
			{
				$value = explode(":", $trimmedRow)[1];
				$this->parsedAppointment->SetSequence((int)$value);
			}
			else if(strpos($trimmedRowUpper, "LOCATION") !== FALSE)
			{
				$split = explode(":", $trimmedRow);
				$value = $split[count($split) - 1];
				$this->parsedAppointment->SetLocation($value);
			}
			else if(strpos($trimmedRowUpper, "X-MS-OLK-ORIGINALSTART") !== FALSE)
			{
				$split = explode(":", $trimmedRow);
				$value = $split[count($split) - 1];
				$this->parsedAppointment->SetOriginalStartDate($value);
			}
			else if(strpos($trimmedRowUpper, "X-MS-OLK-ORIGINALEND") !== FALSE)
			{
				$split = explode(":", $trimmedRow);
				$value = $split[count($split) - 1];
				$this->parsedAppointment->SetOriginalEndDate($value);
			}
			
			$this->rowIndex++;
		}
	}
	
	/**
	 * Helper Function to parse the alarm part of an event
	 */
	protected function  ParseAlarm()
	{
		while(($this->rowIndex < count($this->rows)) && $this->parsedAppointment !== FALSE)
		{
			$trimmedRow = trim($this->rows[$this->rowIndex]);
			$trimmedRowUpper = strtoupper($trimmedRow);
			
			if(strpos($trimmedRowUpper, "END:VALARM") !== FALSE)
			{
				return;
			}
			
			$this->rowIndex++;
		}
	}
}
?>