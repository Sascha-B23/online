<?php
/**
 * Baseclass for all Status Forms
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class StatusFormDataEntry
{
	
	/**
	 * List of all form elements
	 * @var FormElement[]
	 */
	protected $elements = 	array();
	
	/**
	 * List with all values of form elements
	 * @var Array
	 */
	protected $formElementValues = 	array();
	
	/**
	 * The form data object
	 * @var WorkflowStatus 
	 */
	protected $obj = null;
	
	/**
	 * The DBManager
	 * @var DBManager
	 */
	protected $db = null;
	
		
	/**
	 * ExtendedLanguageManager
	 * @var ExtendedLanguageManager
	 */
	protected $languageManager = null;
	
	/**
	 * List with errors of form elements
	 * @var Array
	 */
	protected $error = array();
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param WorkflowStatus $object
	 * @param array $formElementValues Form element values (POST-Vars)
	 */
	public function StatusFormDataEntry(DBManager $db, WorkflowStatus $object, &$formElementValues)
	{
		global $lm;
		$this->formElementValues = &$formElementValues;
		$this->obj = $object;
		$this->db = $db;
		$this->languageManager = $lm;
	}
	
	/**
	 * Set the current form element values (POST-Vars)
	 * @param array $formElementValues
	 */
	public function SetFormElementValues(&$formElementValues)
	{
		$this->formElementValues = &$formElementValues;
	}
	
	/**
	 * Return all UI elements
	 * @return FormElement[] 
	 */
	public function GetUiElements()
	{
		return $this->elements;
	}
	
	/**
	 * Return the UI errors 
	 * @return array 
	 */
	public function GetUiErrors()
	{
		return $this->error;
	}
	
	/**
	 * This function is called once-only when switching to this status
	 */
	abstract public function Prepare();
	
	/**
	 * this function is called once-only when sitching to the previous status 
	 */
	public function OnReturnToPreviousStatus()
	{	
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	abstract public function InitElements($loadFromObject);
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	abstract public function Store($gotoNextStatus);
	
	/**
	 * Inject HTML/JavaScript before the UI is send to the browser 
	 */
	abstract public function PrePrint();
	
	/**
	 * Inject HTML/JavaScript after the UI is send to the browser 
	 */
	abstract public function PostPrint();
	
	/**
	 * Return the following status 
	 */
	abstract public function GetNextStatus();
	
	/**
	 * Is called after the changes have been saved successfully
	 */
	public function PostStore(){}
	
}
?>