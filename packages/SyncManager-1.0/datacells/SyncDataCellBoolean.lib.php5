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
class SyncDataCellBoolean extends SyncDataCell 
{
	const VALUE_UNDEFINED = 0;
	const VALUE_TRUE = 1;
	const VALUE_FALSE = 2;
	
	/**
	 * Value to use for boolean true in CSV
	 * @var string 
	 */
	protected $csvValueTrue = "Ja";
	
	/**
	 * Value to use for boolean false in CSV
	 * @var string 
	 */
	protected $csvValueFalse = 'Nein';

    /**
     * Value to skip contition
     * @var int
     */
    protected $skipCondition = self::VALUE_UNDEFINED;

    /**
     * Constructor
     * @param string $csvValueTrue
     * @param string $csvValueFalse
     * @param int $skipCondition
     */
	public function SyncDataCellBoolean($csvValueTrue='Ja', $csvValueFalse='Nein', $skipCondition=self::VALUE_UNDEFINED)
	{
		$this->csvValueTrue = $csvValueTrue;
		$this->csvValueFalse = $csvValueFalse;
        $this->skipCondition = $skipCondition;
	}
	
	/**
	 * set the value
	 * @param float $value
	 * @return boolean
	 */
	public function SetValue($value)
	{
		if (!is_int($value) || $value<0 || $value>2) return false;
		$this->value = $value;
		$this->csvValue = ($this->value==self::VALUE_TRUE ? $this->csvValueTrue : ($this->value==self::VALUE_FALSE ? $this->csvValueFalse : ''));
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
		if ($value===$this->csvValueTrue) $this->value = self::VALUE_TRUE;
		elseif ($value===$this->csvValueFalse) $this->value = self::VALUE_FALSE;
		elseif (trim($value)=='') $this->value = self::VALUE_UNDEFINED;
		else $this->value = (string)$value;
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
			return ($this->value==self::VALUE_TRUE ? $this->csvValueTrue : ($this->value==self::VALUE_FALSE ? $this->csvValueFalse : ''));
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
			$this->SetValidationErrorMessage("Feld in Spalte '".$this->column->GetName()."' enthält einen ungültigen Wert (".$this->value."). Zulässig ist: ".$this->csvValueTrue."/".$this->csvValueFalse);
			$this->SetValidationState(self::STATE_INVALID_VALUE);
		}
		else
		{
			if ($this->column!=null && !$this->column->IsEmptyValueAllowed() && $this->value==self::VALUE_UNDEFINED)
			{
				$this->SetValidationErrorMessage("Feld in Spalte '".$this->column->GetName()."' darf nicht leer sein");
				$this->SetValidationState(self::STATE_INVALID_VALUE);
			}
		}
	}

    /**
     * Return if the validation of a depending cell can be skipped
     * @return bool
     */
    protected function SkipCondition()
    {
        if ($this->skipCondition==self::VALUE_UNDEFINED) return false;
        return ($this->value==$this->skipCondition ? true : false);
    }
	
}
?>