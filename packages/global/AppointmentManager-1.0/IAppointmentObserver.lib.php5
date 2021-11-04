<?php

/**
 *
 * @author ngerwien
 */
interface IAppointmentObserver {
	/**
	 * Notifies an observer
	 * 
	 * @param AppointmentManager $appointmentManager
	 * @param Appointment $appointment
	 * @return EmailReaderAction
	 */
	function Notify(AppointmentManager $appointmentManager, Appointment $appointment);
}
?>