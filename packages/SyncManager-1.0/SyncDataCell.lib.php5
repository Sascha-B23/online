<?php
/**
 * data cell definition
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class SyncDataCell 
{
	/**
	 * validation states
	 */
    const STATE_IGNORE = -1;
	const STATE_NOCHANGE = 0;
	const STATE_CHANGED = 1;
    const STATE_NEW = 2;
	//all states below describe an error
    const STATE_ISREQUIRED = 3;
    const STATE_HAS_TO_EXIST_IN_DB = 4;
    const STATE_CANNOT_BE_CHANGED = 5;
	const STATE_INVALID_VALUE = 6;
	
	/**
	 * Column
	 * @var SyncColumn 
	 */
	protected $column = null;
	
	/**
	 * Value of the cell
	 * @var string 
	 */
	protected $value = "";
	
	/**
	 * CSV value of the cell
	 * @var string 
	 */
	protected $csvValue = "";
	
	/**
	 * Current validation state
	 * @var int 
	 */
	protected $validationState = self::STATE_NOCHANGE;
	
	/**
	 * Validation error
	 * @var string 
	 */
	protected $validationErrorMessage = "";

	/**
	 * import error
	 * @var string
	 */
	protected $importErrorMessage = "";

	/**
	 * Constructor
	 */
	public function SyncDataCell()
	{
	}

	/**
	 * Set the column
	 * @param SyncColumn $column
	 */
	public function SetColumn(SyncColumn $column)
	{
		$this->column = $column;
	}
	
	/**
	 * returns the column
	 * @return SyncColumn
	 */
	public function GetColumn()
	{
		return $this->column;
	}
	
	/**
	 * set the value
	 * @param string $value
	 * @return boolean
	 */
	public function SetValue($value)
	{
		$this->value = $value;
		$this->csvValue = $value;
		return true;
	}
	
	/**
	 * get the value
	 * @return string
	 */
	public function GetValue()
	{
		return $this->value;
	}
	
	/**
	 * Set the value from the csv string
	 * @param string $value
	 * @return boolean
	 */
	public function SetCsvValue($value)
	{
		$this->csvValue = $value;
		$this->value = $value;
		$this->UpdateValidationState();
		return true;
	}
	
	/**
	 * Return the value for CSV string
	 * @return string
	 */
	public function GetCsvValue()
	{
		return $this->csvValue;
	}
	
	/**
	 * return the value to be displayed on UI
	 * @return string
	 */
	public function GetDisplayValue()
	{
		return $this->value;
	}
	
	/**
	 * Set the validation state of this cell
	 * @param int $state
	 * @return boolean
	 */
	public function SetValidationState($state)
	{
		if (!is_int($state)) return false;
		$this->validationState = $state;
		return true;
	}
	
	/**
	 * Get the validation state of this cell
	 * @return int
	 */
	public function GetValidationState()
	{
		return $this->validationState;
	}

    /**
     * Check if all
     * @param SyncDataRow $dataRow
     * @return bool
     */
    public function SkipByPreConditions(SyncDataRow $dataRow)
    {
        $preConditionColumn = $this->column->GetPreConditionColumns();
        foreach ($preConditionColumn as $column)
        {
            $dataCell = $dataRow->GetDataCellForColumn($column->GetId());
            if ($dataCell->SkipCondition())
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Return if the validation of a depending cell can be skipped
     * @return bool
     */
    protected function SkipCondition()
    {
        return false;
    }

	/**
	 * return if the data cells of this row are valid
     * @param SyncDataRow $dataRow
	 * @return boolean
	 */
	public function IsValid(SyncDataRow $dataRow)
	{
        if ($this->SkipByPreConditions($dataRow))
        {
            $this->validationState = self::STATE_IGNORE;
            return true;
        }
		if ($this->validationState<=self::STATE_NEW) return true;
		return false;
	}
	
	/**
	 * set the validation error of this cell
	 * @param string $errorMessage
	 */
	public function SetValidationErrorMessage($errorMessage)
	{
		$this->validationErrorMessage = $errorMessage;
	}
	
	/**
	 * Return the validation error of this cell
	 * @return string
	 */
	public function GetValidationErrorMessage()
	{
		return $this->validationErrorMessage;
	}
	
	/**
	 * return if at least one data cell of this row has changed
	 * @return boolean
	 */
	public function HasChanged()
	{
		if ($this->validationState==self::STATE_NEW || $this->validationState==self::STATE_CHANGED) return true;
		return false;
	}
	
	/**
	 * Updates validation state based on intern informations
	 */
	protected function UpdateValidationState()
	{
		if ($this->column!=null && !$this->column->IsEmptyValueAllowed() && trim($this->value)=="")
		{
			$this->SetValidationErrorMessage("Feld in Spalte '".$this->column->GetName()."' darf nicht leer sein");
			$this->SetValidationState(self::STATE_INVALID_VALUE);
		}
	}
	
	/**
	 * set the validation error of this cell
	 * @param string $errorMessage
	 */
	public function SetImportErrorMessage($errorMessage)
	{
		$this->importErrorMessage = $errorMessage;
	}
	
	/**
	 * Return the validation error of this cell
	 * @return string
	 */
	public function GetImportErrorMessage()
	{
		return $this->importErrorMessage;
	}
	
}
?>