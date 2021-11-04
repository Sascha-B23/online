<?php

/**
 *
 * @author ngerwien
 */
interface IAppointmentObservable {
	/**
	 * Register observer
	 * 
	 * @param IAppointmentObserver $observer
	 */
	function AddObserver(IAppointmentObserver $observer);
	
	/**
	 * Remove registered observer
	 * 
	 * @param IAppointmentObserver $observer
	 */
	function RemoveObserver(IAppointmentObserver $observer);
	
	/**
	 * Notify all registerd observers
	 * 
	 * @param Appointment $appointment
	 */
	function NotifyAll(Appointment $appointment);
}
?>