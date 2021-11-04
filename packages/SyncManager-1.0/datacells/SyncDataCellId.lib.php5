<?php
/**
 * data cell definition for float values
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class SyncDataCellId extends SyncDataCell 
{
	/**
	 * empty value allowed
	 * @var boolean 
	 */
	protected $allowEmptyValues = true;
	
	/**
	 * Prefix of the id
	 * @var string
	 */
	protected $idPrefix = '';
	
	/**
	 * Constructor
	 */
	public function SyncDataCellId($allowEmptyValues=true, $idPrefix='')
	{
		$this->allowEmptyValues = $allowEmptyValues;
		$this->idPrefix = $idPrefix;
		$this->SetValue('');
	}
	
	/**
	 * set the value
	 * @param int $value
	 * @return boolean
	 */
	public function SetValue($value)
	{
		$value = trim($value);
		if (mb_substr($value, 0, mb_strlen($this->idPrefix))!=$this->idPrefix || !is_numeric(mb_substr($value, mb_strlen($this->idPrefix)))) return false;
		$this->value = (int)mb_substr($value, mb_strlen($this->idPrefix));
		$this->csvValue = $value;
		return true;
	}
	
	/**
	 * Set the value from the csv string
	 * @param string $value
	 * @return boolean
	 */
	public function SetCsvValue($value)
	{
		$this->csvValue = $value;
		if (mb_substr($value, 0, mb_strlen($this->idPrefix))!=$this->idPrefix || !is_numeric(mb_substr($value, mb_strlen($this->idPrefix))))
		{
			$this->value = $value;
		}
		else
		{
			$this->value = (int)mb_substr($value, mb_strlen($this->idPrefix));
		}
		$this->UpdateValidationState();
	}
	
	/**
	 * return the value to be displayed on UI
	 * @return string
	 */
	public function GetDisplayValue()
	{
		if (!is_int($this->value)) return $this->value;
		return $this->idPrefix.$this->value;
	}
	
	/**
	 * Updates validation state based on internal informations
	 */
	protected function UpdateValidationState()
	{
		parent::UpdateValidationState();
		// int value
		if (!$this->allowEmptyValues || trim($this->value)!=="" )
		{
			if (!is_int($this->value))
			{
				$this->SetValidationErrorMessage("Feld in Spalte '".$this->column->GetName()."' enthält einen ungültigen Wert");
				$this->SetValidationState(self::STATE_INVALID_VALUE);
			}
		}
	}
	
}
?>