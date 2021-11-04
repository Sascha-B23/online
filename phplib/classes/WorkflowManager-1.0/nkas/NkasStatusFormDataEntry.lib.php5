<?php
/**
 * Baseclass for all Status Forms
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class NkasStatusFormDataEntry extends StatusFormDataEntry
{
	
	/**
	 * the master process status object
	 * @var ProcessStatus
	 */
	protected $masterObject = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param WorkflowStatus $object
	 * @param array $formElementValues Form element values (POST-Vars)
	 */
	public function NkasStatusFormDataEntry(DBManager $db, ProcessStatus $object, ProcessStatus $masterObject, &$formElementValues)
	{
		$this->masterObject = $masterObject;
		parent::__construct($db, $object, $formElementValues);
	}
	
	/**
	 * return if the process status is a group
	 * @return boolean
	 */
	protected function IsProcessGroup()
	{
		if (is_a($this->obj, 'ProcessStatusGroup')) return true;
		return false;
	}
	
	/**
	 * Set the object to be edited
	 * @param ProcessStatus $object
	 */
	public function SetObject(ProcessStatus $object)
	{
		$this->obj = $object;
	}
	
	/**
	 * return if this status of the current process can be edited when in a group
	 * @return boolean
	 */
	public function IsEditableInGroup()
	{
		return true;
	}
	
	/**
	 * return if the button "Aufgabe abschließen" should been shown
	 * @return boolean
	 */
	public function CanBeCompletetdInGroup()
	{
		return true;
	}
	
	/**
	 * Return if the Status can be aboarded by user
	 * @return boolean
	 */
	public function CanBeAboarded()
	{
		return false;
	}
	
}
?>