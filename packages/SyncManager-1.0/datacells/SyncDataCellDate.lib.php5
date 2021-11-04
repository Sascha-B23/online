<?php
/**
 * data cell definition for date values
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class SyncDataCellDate extends SyncDataCell 
{
	/**
	 * hanlde zero timestamps as empty values
	 * @var boolean 
	 */
	protected $handleZeroAsEmpty = false;
	
	/**
	 * Constructor
	 * @param boolean $handleZeroAsEmpty
	 */
	public function SyncDataCellDate($handleZeroAsEmpty=false)
	{
		$this->handleZeroAsEmpty = $handleZeroAsEmpty;
	}
	
	/**
	 * set the value
	 * @param float $value
	 * @return boolean
	 */
	public function SetValue($value)
	{
		if (!is_int($value)) return false;
		$this->value = $value;
		if ($this->handleZeroAsEmpty && $this->value==0)
		{
			$this->csvValue = "";
		}
		else
		{
			$this->csvValue = date('d.m.Y', $this->value);
		}
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
		if ($this->handleZeroAsEmpty && (trim($value) == "-" || trim($this->csvValue) == ""))
		{
			$this->value = 0;
		}
		else
		{
			$this->value = HelperLib::ConvertStringToTimeStamp($value);
			if ($this->value===false)
			{
				// on converting error we use the string
				$this->value = (string)$value;
			}
		}
		$this->UpdateValidationState();
	}
	
	/**
	 * return the value to be displayed on UI
	 * @return string
	 */
	public function GetDisplayValue()
	{
		if (!is_int($this->value))
		{
			return $this->csvValue;
		}
		else
		{
			if ($this->handleZeroAsEmpty && $this->value==0)
			{
				return "";
			}
			return date('d.m.Y', $this->value);
		}
	}
	
	/**
	 * Updates validation state based on internal informations
	 */
	protected function UpdateValidationState()
	{
		// float value
		if (!is_int($this->value))
		{
			$this->SetValidationErrorMessage("Feld in Spalte '".$this->column->GetName()."' enthält ein ungültiges Datum (tt.mm.jjjj)");
			$this->SetValidationState(self::STATE_INVALID_VALUE);
		}
		else
		{
			if ($this->column!=null && !$this->column->IsEmptyValueAllowed() && $this->handleZeroAsEmpty && $this->value==0)
			{
				$this->SetValidationErrorMessage("Feld in Spalte '".$this->column->GetName()."' darf nicht leer sein");
				$this->SetValidationState(self::STATE_INVALID_VALUE);
			}
		}
	}
	
}
?>