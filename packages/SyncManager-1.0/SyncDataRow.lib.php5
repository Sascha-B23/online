<?php
/**
 * Base class to define a row
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class SyncDataRow
{

	/**
	 * Column definition
	 * @var SyncColumn[]
	 */
	protected $columns = Array();
	
	/**
	 * Column definition
	 * @var SyncDataCell[]
	 */
	protected $dataCells = Array();
	
	/**
	 * import error of this data row
	 * @var string
	 */
	protected $importErrorMessage = "";
	
	/**
	 * Constructor
	 * @param SyncColumn[] $columns 
	 * @param int $currentLineNumber
	 */
	public function SyncDataRow($columns, $currentLineNumber) 
	{
		$this->coulmns = $columns;
		$this->currentLineNumber = $currentLineNumber;
		$this->InitDataCells();
	}
	
	/**
	 * Initialize SyncDataCell-objects
	 */
	protected function InitDataCells()
	{
		$this->dataCells = Array();
		foreach ($this->coulmns as $column)
		{
			$this->dataCells[] = $column->CreateNewSyncDataCell();
		}
	}
	
	/**
	 * get the CSV line number of this entry
	 * @return int
	 */
	public function GetCsvLineNumber()
	{
		return $this->currentLineNumber;
	}
	
	/**
	 * Get the data cell of a specific column 
	 * @param int $columnId
	 * @return SyncDataCell
	 */
	public function GetDataCellForColumn($columnId)
	{
		for ($a=0; $a<count($this->dataCells); $a++)
		{
			if ($this->dataCells[$a]->GetColumn()->GetId()==$columnId) return $this->dataCells[$a];
		}
		return null;
	}
	
	/**
	 * Return the row as CSV-string
	 */
	public function ToCsvString()
	{
		$row = "";
		for ($a=0; $a<count($this->dataCells); $a++)
		{
			if ($row!="") $row.=";";
			$row.=str_replace(";", " ", str_replace("\n", "", $this->dataCells[$a]->GetCsvValue()));
		}
		return $row;
	}
	
	/**
	 * get the number of columns
	 * @return int
	 */
	public function GetColumnCount()
	{
		return count($this->columns);
	}
	
	/**
	 * return if all data cells of this row are valid
	 * @return boolean
	 */
	public function IsValid()
	{
		for ($a=0; $a<count($this->dataCells); $a++)
		{
			if (!$this->dataCells[$a]->IsValid($this)) return false;
		}
		return true;
	}
	
	/**
	 * Return all validation errors of the cells in one string
	 * @return string
	 */
	public function GetValidationErrorMessage()
	{
		$validationError = "";
		for ($a=0; $a<count($this->dataCells); $a++)
		{
			$error = $this->dataCells[$a]->GetValidationErrorMessage();
			if (trim($error)!="")
			{
				if ($validationError!="") $validationError.="\n";
				$validationError.=$error;
			}
		}
		return $validationError;
	}
	
	/**
	 * set the data row error message for import
	 * @param string $errorMessage
	 */
	public function SetImportErrorMessage($errorMessage)
	{
		$this->importErrorMessage = $errorMessage;
	}
	
	/**
	 * Return the import error of this row and all cells
	 * @return string
	 */
	public function GetImportErrorMessage()
	{
		$importError = "";
		// add data cell import errors
		for ($a=0; $a<count($this->dataCells); $a++)
		{
			$error = $this->dataCells[$a]->GetImportErrorMessage();
			if (trim($error)!="")
			{
				if ($importError!="") $importError.="\n";
				$importError.=$error;
			}
		}
		// add data row import error
		if (trim($this->importErrorMessage)!="")
		{
			if ($importError!="") $importError.="\n";
			$importError.=$this->importErrorMessage;
		}
		return $importError;
	}
	
	/**
	 * Return if the import was successfull
	 * @return boolean
	 */
	public function IsImportSuccessfully()
	{
		return (trim($this->GetImportErrorMessage())=="" ? true : false);
	}
	
	/**
	 * Return if at least one data cell of this row has changed
	 * @return boolean
	 */
	public function HasChanged()
	{
		for ($a=0; $a<count($this->dataCells); $a++)
		{
			if ($this->dataCells[$a]->HasChanged()) return true;
		}
		return false;
	}
	
	/**
	 * Reset validation states and errors in all cells
	 */
	public function Reset()
	{
		$this->SetImportErrorMessage('');
		for ($a=0; $a<count($this->dataCells); $a++)
		{
			$this->dataCells[$a]->SetValidationState(SyncDataCell::STATE_NOCHANGE);
			$this->dataCells[$a]->SetValidationErrorMessage('');
			$this->dataCells[$a]->SetImportErrorMessage('');
		}
	}
	
}

?>
