<?php
/**
 * Column definition
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class SyncColumn 
{    
	/**
	 * ID of the column
	 * @var string 
	 */
	protected $columnId = -1;
	
	/**
	 * Name of the column
	 * @var string 
	 */
	protected $columnName = "";
	
	/**
	 * Empty values allowed
	 * @var boolean 
	 */
	protected $allowEmptyValues = true;
	
	/**
	 * regular expression to validate values
	 * @var string 
	 */
	protected $valueFormat = "";
	
	/**
	 * SyncDataCell instance
	 * @var SyncDataCell 
	 */
	protected $dataCell = null;

    /**
     * @var SyncColumn[]
     */
    protected $preConditionColumn = Array();

	/**
	 * Constructor
	 */
	public function SyncColumn($columnId, $columnName, $allowEmptyValues=true, $valueFormat="", SyncDataCell $dataCell=null)
	{
		$this->columnId = $columnId;
		$this->columnName = $columnName;
		$this->allowEmptyValues = $allowEmptyValues;
		$this->valueFormat = $valueFormat;
		$this->dataCell = $dataCell;
		if ($this->dataCell==null)
		{
			$this->dataCell = new SyncDataCell();
		}
		$this->dataCell->SetColumn($this);
	}
    
	/**
	 * returns the column id
	 * @return int
	 */
	public function GetId()
	{
		return $this->columnId;
	}
	
	/**
	 * returns the column name
	 * @return string
	 */
	public function GetName()
	{
		return $this->columnName;
	}
	
	/**
	 * return if empty value is allowed
	 * @return boolean
	 */
	public function IsEmptyValueAllowed()
	{
		return $this->allowEmptyValues;
	}
	
	/**
	 * return a new instance of class SyncDataCell
	 * @return SyncDataCell
	 */
	public function CreateNewSyncDataCell()
	{
		return (clone $this->dataCell);
	}

    /**
     * @param SyncColumn $column
     */
    public function AddPreConditionColumn(SyncColumn $column)
    {
        $this->preConditionColumn[] = $column;
    }

    /**
     * @return SyncColumn[]
     */
    public function GetPreConditionColumns()
    {
        return $this->preConditionColumn;
    }

}
?>