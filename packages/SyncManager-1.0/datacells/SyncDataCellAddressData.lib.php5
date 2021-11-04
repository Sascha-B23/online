<?php
/**
 * data cell definition for AddressBase-object values
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class SyncDataCellAddressData extends SyncDataCell 
{
	/**
	 * DBManager
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * Constructor
	 * @param boolean $handleZeroAsEmpty
	 */
	public function SyncDataCellAddressData(DBManager $db)
	{
		$this->db = $db;
	}
	
	/**
	 * set the value
	 * @param AddressBase $value
	 * @return boolean
	 */
	public function SetValue($value)
	{
		if ($value!=null && !is_a($value, 'AddressBase')) return false;
		$this->value = $value;
		
		if ($value==null)
		{
			$this->csvValue = "";
		}
		else
		{
			$this->csvValue = $value->GetAddressIDString();
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
		if (trim($value)!="")
		{
			$tempAddressData = AddressManager::GetAddessById($this->db, $value);
			if ($tempAddressData!==null)
			{
				$this->value = $tempAddressData;
				
			}
			else
			{
				$this->value = (string)$value;
			}
		}
		else
		{
			$this->value = null;
		}
		$this->UpdateValidationState();
	}
	
	/**
	 * return the value to be displayed on UI
	 * @return string
	 */
	public function GetDisplayValue()
	{
		if ($this->value===null)
		{
			return "";
		}
		elseif (!is_a($this->value, "AddressBase"))
		{
			return $this->csvValue;
		}
		else
		{
			return $this->value->GetAddressIDString();
		}
	}
	
	/**
	 * Updates validation state based on internal informations
	 */
	protected function UpdateValidationState()
	{
		// float value
		if ($this->value!==null && !is_a($this->value, "AddressBase"))
		{
			$this->SetValidationErrorMessage("Die Adresse in Spalte '".$this->column->GetName()."' wurde im System nicht gefunden");
			$this->SetValidationState(self::STATE_INVALID_VALUE);
		}
		else
		{
			if ($this->column!=null && !$this->column->IsEmptyValueAllowed() && $this->value==null)
			{
				$this->SetValidationErrorMessage("Feld in Spalte '".$this->column->GetName()."' darf nicht leer sein");
				$this->SetValidationState(self::STATE_INVALID_VALUE);
			}
		}
	}
	
}
?>