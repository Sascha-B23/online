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
class SyncDataCellIdToTextMap extends SyncDataCell 
{
	/**
	 * value that indicates an empty value
	 * @var mixed
	 */
	protected $emptyValue = "";
	
	/**
	 * array map id to name
	 * @var array
	 */
	protected $idToNameMap = Array();
	
	/**
	 * array map name to id
	 * @var array
	 */
	protected $nameToIdMap = Array();
	
	/**
	 * ids
	 * @var mixed[]
	 */
	protected $ids = Array();
	
	/**
	 * names
	 * @var string[]
	 */
	protected $names = Array();
	
	/**
	 * Constructor
	 */
	public function SyncDataCellIdToTextMap($idToNameMap, $emptyValue = -1)
	{
		$this->emptyValue = $emptyValue;
		$this->idToNameMap = $idToNameMap;
		$this->ids = array_keys($this->idToNameMap);
		$this->names = array_values($this->idToNameMap);
		$this->nameToIdMap = array_flip($this->idToNameMap);
	}
	
	/**
	 * Checks if a value exists in an array
	 * This function is used instead of the native PHP function in_array(), because the native function returns strange results if the array contains intagers and the needle is a string - in this case in_array allways returns TRUE (in_array('test', Array(0,1,2,3,4,5)=TRUE)
	 * @param mixed $needle
	 * @param array $haystack
	 */
	private function InArray($needle, $haystack)
	{
		foreach ($haystack as $value) 
		{
			if (is_int($value) && is_numeric($needle) && $value==$needle || !is_int($value) && $value==$needle)
			{
				return true;
			}
		}
		//echo "$value!=$needle<br />";
		return false;
	}


	/**
	 * set the value
	 * @param float $value
	 * @return boolean
	 */
	public function SetValue($value)
	{
		if (!$this->InArray($value, $this->ids)) return false;
		$this->value = $value;
		$this->csvValue = $this->idToNameMap[$this->value];
		return true;
	}
	
	/**
	 * Set the value from the csv string
	 * @param string $value
	 * @return boolean
	 */
	public function SetCsvValue($value)
	{
		$this->csvValue = trim($value);
		if (isset($this->nameToIdMap[$this->csvValue]))
		{
			$this->value = $this->nameToIdMap[$this->csvValue];
		}
		else
		{
			$this->value = $this->csvValue;
		}
		$this->UpdateValidationState();
	}
	
	/**
	 * return the value to be displayed on UI
	 * @return string
	 */
	public function GetDisplayValue()
	{
		if (!$this->InArray($this->value, $this->ids))
		{
			return $this->csvValue;
		}
		else
		{
			return $this->idToNameMap[$this->value];
		}
	}
	
	/**
	 * Updates validation state based on internal informations
	 */
	protected function UpdateValidationState()
	{
		if (!$this->InArray($this->value, $this->ids))
		{
			$this->SetValidationErrorMessage("Feld in Spalte '".$this->column->GetName()."' enthält einen ungültigen Wert (".$this->value."). Zulässig sind: ".implode(', ', $this->names));
			$this->SetValidationState(self::STATE_INVALID_VALUE);
		}
		else
		{
			if ($this->column!=null && !$this->column->IsEmptyValueAllowed() && $this->value==$this->emptyValue)
			{
				$this->SetValidationErrorMessage("Feld in Spalte '".$this->column->GetName()."' darf nicht leer sein");
				$this->SetValidationState(self::STATE_INVALID_VALUE);
			}
		}
	}
	
}
?>