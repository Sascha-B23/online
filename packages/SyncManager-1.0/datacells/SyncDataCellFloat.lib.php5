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
class SyncDataCellFloat extends SyncDataCell 
{
	/**
	 * is 0 allowed as value
	 * @var booelan 
	 */
	protected $allowNullValues = true;
	
	/**
	 * are values <0 allowed as values
	 * @var boolean
	 */
	protected $allowNegativeValues = false;
	
	/**
	 * decimals to round for CSV string
	 * @var int 
	 */
	protected $decimals = 2;
	
	/**
	 * Constructor
	 */
	public function SyncDataCellFloat($allowNullValues=true, $allowNegativeValues=false, $decimals = 2)
	{
		$this->allowNullValues = $allowNullValues;
		$this->allowNegativeValues = $allowNegativeValues;
		$this->decimals = $decimals;
		$this->SetValue(0.0);
	}
	
	/**
	 * set the value
	 * @param float $value
	 * @return boolean
	 */
	public function SetValue($value)
	{
		if (!is_float($value)) return false;
		$this->value = $value;
		$this->csvValue = number_format($value, $this->decimals, ',', '');
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
		$this->value = HelperLib::ConvertStringToFloat($value);
		if ($this->value===false)
		{
			// on converting error we use the string
			$this->value = (string)$value;
		}
		$this->UpdateValidationState();
	}
	
	/**
	 * return the value to be displayed on UI
	 * @return string
	 */
	public function GetDisplayValue()
	{
		if (!is_float($this->value))
		{
			return $this->csvValue;
		}
		else
		{
			return HelperLib::ConvertFloatToLocalizedString($this->value);
		}
	}
	
	/**
	 * Updates validation state based on internal informations
	 */
	protected function UpdateValidationState()
	{
		parent::UpdateValidationState();
		// float value
		if (!is_float($this->value))
		{
			$this->SetValidationErrorMessage("Feld in Spalte '".$this->column->GetName()."' enthält einen ungültigen Wert");
			$this->SetValidationState(self::STATE_INVALID_VALUE);
		}
		else
		{
			if (!$this->allowNullValues && $this->value==0.0)
			{
				$this->SetValidationErrorMessage("Feld in Spalte '".$this->column->GetName()."' darf nicht 0 sein");
				$this->SetValidationState(self::STATE_INVALID_VALUE);
			}
			if (!$this->allowNegativeValues && $this->value<0.0)
			{
				$this->SetValidationErrorMessage("Feld in Spalte '".$this->column->GetName()."' darf nicht <0 sein");
				$this->SetValidationState(self::STATE_INVALID_VALUE);
			}
		}
	}
	
}
?>