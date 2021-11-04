<?
/**
 * Sends, receives and replies to Appointments
 *
 * Depends on EmailReader.lib.php5 and EmailManager.lib.php5
 * 
 * @author ngerwien
 */

require_once __DIR__.'/IAppointmentObservable.lib.php5';
require_once __DIR__.'/IAppointmentObserver.lib.php5';
require_once __DIR__.'/Appointment.lib.php5';
require_once __DIR__.'/AppointmentFactory.lib.php5';
require_once __DIR__.'/AppointmentParser.lib.php5';

class AppointmentManager implements IAppointmentObservable
{
	protected $emailManager;
	protected $observers = array();
	
	//check appointment state
	private $currentEmailReader = null;
	private $currentEmail = null;
	
	
	/**
	 * Constructor.
	 * 
	 * @param EMailManager $emailManager
	 */
	public function AppointmentManager(EMailManager $emailManager)
	{
		$this->emailManager = $emailManager;
	}
	
	/**
	 * sends a new appointment
	 * 
	 * @param Appointment $appointment
	 * @return bool Success
	 */
	public function SendAppointment(Appointment $appointment)
	{
		if($this->emailManager === null)
		{
			return false;
		}

		return $this->emailManager->SendEmail(
			array(new EMailAddress($appointment->GetTo())),
			$appointment->GetSubject(), 
			$appointment->CreateMailMessage(),
			new EMailAddress($appointment->GetFrom()),
			array(),
			EMailManager::EMAIL_TYPE_CALENDAR
		);
		return true;
	}
	
	/**
	 * Check the inbox for replies to transmitted appointments and clean the inbox
	 * 
	 * @param EmailReader $emailReader
	 * @param IEmailReadAction $emailActionProcessed the action that will be executed when an email could be processed
	 * @param IEmailReadAction $emailActionNotProcessed the action that will be executed when an email could not be processed
	 * 
	 * @return bool Success
	 */
	public function CheckAppointmentEmailReplies(EmailReader $emailReader, 
			IEmailReadAction $emailActionProcessed = null, 
			IEmailReadAction $emailActionNotProcessed = null)
	{
		$this->currentEmailReader = $emailReader;
		$emails = $emailReader->ReadInbox();
		foreach ($emails as $email)
		{		
			$this->currentEmail = $email;
			$emailHasBeenProcessed = FALSE;
			$vcalendarPart = $this->ExtractVCalendarPart($email);
			if($vcalendarPart !== FALSE)
			{
				$appointmentParser = new AppointmentParser();
				$parsedAppointment = $appointmentParser->parse($vcalendarPart);
				//print_r($parsedAppointment);
				//exit;
				
				//get the from and to adress from header
				$parsedAppointment->SetFrom($email->header->from[0]->mailbox."@".$email->header->from[0]->host);
				$parsedAppointment->SetTo($email->header->to[0]->mailbox."@".$email->header->to[0]->host);
				
				$emailHasBeenProcessed = $this->ProcessAppointment($parsedAppointment);
			}
			
			//execute email actions
			if($emailActionProcessed !== null && $emailHasBeenProcessed)
			{
				$emailActionProcessed->Execute($emailReader, $email);
			}
			elseif ($emailActionNotProcessed !== null && !$emailHasBeenProcessed) {
				$emailActionNotProcessed->Execute($emailReader, $email);
			}
		}
		
		return TRUE;
	}
	
	/**
	 * Process appointments parsed from emails
	 * 
	 * @param Appointment $appointment
	 * @return boolean
	 */
	private function ProcessAppointment(Appointment $appointment)
	{
		if($appointment->GetType() != Appointment::MESSAGE_TYPE_UNDEFINED)
		{
			$this->NotifyAll($appointment);
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Extracts the VCalendar part as string from an email
	 * 
	 * @param EmailReaderEmail $email
	 * @return string the VCalendar part of the email or FALSE
	 */
	private function ExtractVCalendarPart(EmailReaderEmail $email)
	{		
		$partNumber = 0;
		foreach($email->structure->parts as $part)
		{
			if(strtoupper($part->subtype) === "CALENDAR")
			{				
				//decode message
				$message = $email->bodyParts[$partNumber];
				if($part->encoding == 3) {
					$message = imap_base64($message);
				} else if($part->encoding == 1) {
					$message = imap_8bit($message);
				} else {
					$message = imap_qprint($message);
				}
				
				//find charset
				foreach ($part->parameters as $parameter)
				{
					if((strtoupper($parameter->attribute) === "CHARSET")
							&& (strtolower($parameter->value)) === "utf-8")
					{
						$message = utf8_decode($message);
						break;
					}
				}
				
				return $message;
			}
			
			$partNumber++;
		}
		
		return FALSE;
	}
	
	/**
	 * Register observer
	 * 
	 * @param IAppointmentObserver $observer
	 */
	public function AddObserver(IAppointmentObserver $observer)
	{
		//check if observer is already registered
		foreach ($this->observers as $registeredObserver)
		{
			if($observer === $registeredObserver)
			{
				return;
			}
		}
		
		$this->observers[] = $observer;
	}
	
	/**
	 * Remove registered observer
	 * 
	 * @param IAppointmentObserver $observer
	 */
	public function RemoveObserver(IAppointmentObserver $observer)
	{
		for($i = 0; $i < count($this->observers); $i++)
		{
			if($observer === $this->observers[$i])
			{
				unset($this->observers[$i]);
			}
		}
	}
	
	/**
	 * Notify all registerd observers
	 * 
	 * @param Appointment $appointment
	 */
	public function NotifyAll(Appointment $appointment)
	{
		foreach ($this->observers as $registeredObserver)
		{
			$emailReadAction = $registeredObserver->Notify($this, $appointment);
			
			//if observer returns an EmailReadAction then execute it and stop notifying other observers
			if($emailReadAction !== null && $this->currentEmailReader !== null && $this->currentEmail !== null)
			{
				$emailReadAction->Execute($this->currentEmailReader, $this->currentEmail);
				break;
			}
		}
	}
}
?>