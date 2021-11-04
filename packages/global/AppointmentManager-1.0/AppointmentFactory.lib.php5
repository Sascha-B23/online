<?php

/**
 * Factory functions to create appointments
 *
 * @author ngerwien
 */

class AppointmentFactory {
	
	/**
	 * Send an inviation for a new appointment
	 * 
	 * @param string $start A timestamp is expected
	 * @param string $end A timestamp is expected
	 * @param string $location
	 * @param string $subject
	 * @param string $summary
	 * @param string $from
	 * @param string $to
	 * @return \Appointment
	 */
	public static function CreateInvitation($start, $end, $location, $subject, $summary, $from, $to, $uid, $sequence = 0)
	{		
		$appointment = new Appointment();
		$appointment->SetMethod(Appointment::METHOD_REQUEST);
		$appointment->SetStartDate($start);
		$appointment->SetEndDate($end);
		$appointment->SetLocation($location);
		$appointment->SetSubject($subject);
		$appointment->SetSummary($summary);
		$appointment->SetUID($uid);
		$appointment->SetSequence($sequence);
		$appointment->SetFrom($from);
		$appointment->SetTo($to);
		
		return $appointment;
	}
	
	/**
	 * Cancel an Appointment. (You have to be the organiser)
	 * 
	 * @param type $start
	 * @param type $end
	 * @param type $subject
	 * @param type $from
	 * @param type $to
	 * @param type $uid
	 * @param type $sequence
	 * @return \Appointment
	 */
	public static function CreateCancelMessage($start, $end, $subject, $from, $to, $uid, $sequence)
	{
		$appointment = new Appointment();
		$appointment->SetMethod(Appointment::METHOD_CANCEL);
		$appointment->SetStartDate($start);
		$appointment->SetEndDate($end);
		$appointment->SetSubject($subject);
		$appointment->SetUID($uid);
		$appointment->SetSequence($sequence);
		$appointment->SetFrom($from);
		$appointment->SetTo($to);
		
		return $appointment;
	}
	
	/**
	 * Suggest another date for an appointment when you are not the organiser
	 * 
	 * @param Appointment $relatedAppointment
	 * @param string $start
	 * @param string $end
	 * @return \Appointment
	 */
	/*public static function CreateDateSuggestion($relatedAppointment, $newStart, $newEnd)
	{
		$appointment = new Appointment();
		$appointment->SetMethod(Appointment::METHOD_COUNTER);
		$appointment->SetStartDate($newStart);
		$appointment->SetEndDate($newEnd);
		$appointment->SetOriginalStartDate($relatedAppointment->GetStartDate());
		$appointment->SetOriginalEndDate($relatedAppointment->GetEndDate());
		$appointment->SetLocation($relatedAppointment->location);
		$appointment->SetSubject($relatedAppointment->subject);
		$appointment->SetSummary($relatedAppointment->summary);
		$appointment->SetUID($relatedAppointment->uid);
		
		//switch from and to
		$appointment->from = $relatedAppointment->to;
		$appointment->to = $relatedAppointment->from;
		
		return $appointment;
	}*/
	
	/**
	 * Reply and accept a suggestion for changing the date of an appointment
	 * You have to be the organiser
	 * 
	 * @param Appointment $suggestedAppointment
	 * @param int $sequence
	 * @return \Appointment
	 */
	public static function CreateReplyToDateSuggestion($suggestedAppointment, $sequence)
	{
		$appointment = new Appointment();
		$appointment->SetMethod(Appointment::METHOD_REQUEST);
		$appointment->SetStartDate($suggestedAppointment->GetStartDate());
		$appointment->SetEndDate($suggestedAppointment->GetEndDate());
		$appointment->SetLocation($suggestedAppointment->GetLocation());
		$appointment->SetSubject($suggestedAppointment->GetSubject());
		$appointment->SetSummary($suggestedAppointment->GetSummary());
		$appointment->SetUID($suggestedAppointment->GetUID());
		$appointment->SetSequence($sequence);
		
		//switch from and to
		$appointment->SetFrom($suggestedAppointment->GetTo());
		$appointment->SetTo($suggestedAppointment->GetFrom());
		
		return $appointment;
	}
}
?>