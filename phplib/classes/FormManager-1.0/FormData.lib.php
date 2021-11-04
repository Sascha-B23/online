<?php
/**
 * Base-class for all forms
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class FormData 
{
	/**
	 * Options of the form
	 * @var array
	 */
	protected $options = array(	"icon" => "fehlt.png",
								"icontext" => "Formname",
								"show_options_save" => true,
								"show_options_cancel" => true,
								"show_options_previous" => false,
								"show_options_next" => false,
								"jumpBackLabel" => "Abbrechen"
						);
	
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
	 * @var DBEntry 
	 */
	protected $obj = null;
	
	/**
	 * The DBManager
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * The current status of the form
	 * @var int 
	 */
	protected $formDataStatus = 0;
	
	/**
	 * List with errors of form elements
	 * @var Array
	 */
	protected $error = array();
	
	/**
	 * ExtendedLanguageManager
	 * @var ExtendedLanguageManager
	 */
	protected $languageManager = null;
	
	/**
	 * Constructor
	 * @param array $formElementValues	form element values (POST-Vars)
	 * @param DBEntry $object			Data-object for this form
	 * @param DBManager $db
	 */
	public function FormData($formElementValues, $object, $db)
	{
		global $lm;
		$this->languageManager = $lm;
		$this->formElementValues = $formElementValues;
		$this->obj=$object;
		$this->db=$db;
		$loadFromObject = false;
		// edit or create new...
		$edit = $this->LoadFormDataObjectByID($db, $this->formElementValues["editElement"]);
		// output formular data (pre)
		$this->PrePrint();
		// store data?
		if ($this->formElementValues["sendData"]=="true")
		{
			// store data!
			if ($this->Store()) $this->formDataStatus=1;
			// set (new) object id to post var
			if ($this->obj!=null && $this->obj->GetPkey()!=-1) $_POST["editElement"]=$this->GetFormDataObjectID();
		}
		// use data from object or post vars?
		if ($edit && !isset($this->formElementValues["sendData"])) $loadFromObject=true;
		// init the form elements
		$this->InitElements($edit, $loadFromObject);
	}
	
	/**
	 * Return the icon file name to display for this form
	 * @return string
	 */
	public function GetIcon()
	{
		return $this->options["icon"];
	}
	 
	/**
	 * Return the name of the form
	 * @return string
	 **/
	public function GetIconText()
	{
		return $this->options["icontext"];
	}
	
	/**
	 * Return the error-array of the form.
	 * For each form-element one error could be set $error['formElementName'] = 'form element error text'
	 * @return array
	 */
	public function GetErrors()
	{
		return $this->error;
	}
	
	/**
	 * Return if the form is read only
	 * @return boolean
	 */
	public function IsReadOnly()
	{
		return !$this->options["show_options_save"];
	}
	
	/**
	 * Return if the form is read only
	 * @return boolean
	 */
	public function IsPreviousButtonAvailable()
	{
		return $this->options["show_options_previous"];
	}
	
	/**
	 * Return if the form is read only
	 * @return boolean
	 */
	public function IsNextButtonAvailable()
	{
		return $this->options["show_options_next"];
	}
	
	/**
	 * Return if the Cancel-Button is available
	 * @return boolean
	 */
	public function IsCancelButtonAvailable()
	{
		return $this->options["show_options_cancel"];
	}
	
	/**
	 * Return the label for the jumbback link
	 * @return string
	 */
	public function GetJumpBackLabel()
	{
		return $this->options["jumpBackLabel"];
	}
	
	/**
	 * Return all form elements of this form
	 * @return array
	 */
	public function GetElements()
	{
		return $this->elements;
	}
	
	/**
	 * Return the status of this form
	 * @return int		0 = Standard 
	 *					1 = Data was stored successfully
	 *					2 = Error while store data
	 */
	public function GetFormDataStatus()
	{
		return $this->formDataStatus;
	}
	
	/**
	 * Return the id of the entity
	 * @return int
	 */
	public function GetFormDataObjectID()
	{
		if ($this->obj!=null) return $this->obj->GetPKey();
		return -1;
	}
	
	/**
	 * Load the objects data by id
	 * @param mixed $objectId
	 * @return boolean
	 */
	public function LoadFormDataObjectByID(DBManager $db, $objectId)
	{
		if (is_numeric($objectId) && ((int)$objectId)!=0 && ((int)$objectId)!=-1)
		{
			// Daten aus DB laden
			if( $this->obj!=null && $this->obj->Load((int)$objectId, $db)===true) return true;
		}
		return false;
	}
	
	/**
	 * Return the form data object
	 * @return DBEntry
	 */
	public function GetFormDataObject()
	{
		return $this->obj;
	}

	/**
	 * Return the form data object
	 * @return Array 
	 */
	public function GetFormElementValues()
	{
		return $this->formElementValues;
	}
	
	/**
	 * This function have to initialize all form elements
	 * @param boolean $edit				Edit (true) or Create (true) Mode
	 * @param boolean $loadFromObject	Data should be read from data object (true) or not (false)
	 */
	abstract public function InitElements($edit, $loadFromObject);

	/**
	 * The data should be stored to data object
	 * @return boolean
	 */
	abstract public function Store();

	/**
	 * This function can be used to output HTML data bevor the form
	 */
	public function PrePrint()
	{
	}
	
	/**
	 * This function can be used to output HTML data after the form
	 */
	public function PostPrint()
	{
	}
	
}
?>