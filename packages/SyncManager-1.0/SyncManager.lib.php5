<?php
include_once 'SyncDataCell.lib.php5';
include_once 'datacells/SyncDataCellInteger.lib.php5';
include_once 'datacells/SyncDataCellFloat.lib.php5';
include_once 'datacells/SyncDataCellDate.lib.php5';
include_once 'datacells/SyncDataCellBoolean.lib.php5';
include_once 'datacells/SyncDataCellAddressData.lib.php5';
include_once 'datacells/SyncDataCellIdToTextMap.lib.php5';
include_once 'datacells/SyncDataCellId.lib.php5';
include_once 'SyncColumn.lib.php5';
include_once 'SyncDataRow.lib.php5';

/**
 * Base class to sync csv files with database
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class SyncManager 
{
	/**
	 * database access
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * LanguageManager
	 * @var ExtendedLanguageManager 
	 */
	protected $languageManager = null;
	
	/**
	 * Column definition
	 * @var SyncColumn[]
	 */
	protected $columns = Array();
	
	/**
	 * Current data from db in SyncDataRow objects
	 * @var SyncDataRow[]
	 */
	protected $dbDataRows = Array();
	
	/**
	 * Importet data from CSV file
	 * @var SyncDataRow[]
	 */
	protected $csvDataRows = Array();
	        
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 */
	public function SyncManager(DBManager $db, ExtendedLanguageManager $languageManager) 
	{
		$this->db = $db;
		$this->languageManager = $languageManager;
		$this->InitColumns();
	}
	
	/**
	 * Number of columns
	 * @return int
	 */
	public function GetColumnCount()
	{
		return count($this->columns);
	}
	
	/**
	 * 
	 * @param int $index
	 * @return SyncColumn
	 */
	public function GetColumnByIndex($index)
	{
		if (!is_int($index) || !isset($this->columns[$index])) return null;
		return $this->columns[$index];
	}
	
	/**
	 * Return data as CSV string
	 * @return string
	 */
	public function GetExportContent()
	{
		// Log export
		LoggingManager::GetInstance()->Log(new LoggingSyncManager(get_class($this), LoggingSyncManager::TYPE_EXPORT));
		// Load data from 
		$this->LoadDataFromDatabase();
		// Build CSV-String from SyncDataRow-Objects
		$header = $this->GetCsvExportHeader();
		$body = "";
		for ($a=0; $a<count($this->dbDataRows); $a++)
		{
			$body.=$this->dbDataRows[$a]->ToCsvString()."\n";	    
		}
		return utf8_decode($header.$body);
	}
	
	/**
	 * Generates header for csv export
	 * @return csv string
	 */
	private function GetCsvExportHeader() 
	{
		$header = "";
		foreach ($this->columns as $column)
		{
			$header.=$column->GetName().";";
		}		
		$header.="\n";
		return $header;
	}
	
	/**
	 * upload and parse data from csv file
	 * @param array $uploadFile $_FILE array of the uploaded file
	 * @return bool|int		boolean true on success or int	>0 HTTP-Upload Errors
	 *														-1 invalid paramter
	 *														-2 wrong file ending
	 *														-3 upload error
	 *														-4 file not readable
	 *														<-10 see function ReadCsvFile
	 */
	public function UploadCsvFile($uploadFile) 
	{
		// check paramter
		if (!is_array($uploadFile) || count($uploadFile)==0)
		{
			return -1;
		}
		// check if upload error occured
		if ($uploadFile["error"]!=0)
		{
			return $uploadFile["error"];
		}
		// check file format
		if (stristr($uploadFile["userfile"]["name"], ".csv") == "") 
		{
			return -2;
		}
		// check if file exist
		if(!file_exists($_FILES['userfile']['tmp_name'])) 
		{
			return -3;
		}
		// check if file ist readable
		if(!is_readable($_FILES['userfile']['tmp_name'])) 
		{
			return -4;
		}
		// Parse the file
		$returnValue = $this->ReadCsvFile($_FILES['userfile']['tmp_name']);
		if ($returnValue===true) return true;
		return (-10+$returnValue);
	}
	
	/**
	 * reades csv file
	 * @return bool|int return boolean true on success or int on error
	 *								-1 header not found
	 *								-2 no data rows found
	 */
	protected function ReadCsvFile($fileName) 
	{	
		$fileContent = utf8_encode(file_get_contents($fileName));
		$lines = explode("\n", $fileContent);
		// loop through all csv lines
		$mappingTable = null;
		$dataRows = Array();
		$currentLineNumber = 0;
		foreach ($lines as $line) 
		{
			$currentLineNumber++;
			if (trim(str_replace(';', '', $line))=='') continue;
			$csvRow = explode(";", $line);

			if ($mappingTable===null)
			{
				// first of all search the column header row and extract it
				$mappingTableTemp = $this->BuildCsvMappingTable($csvRow);
				if ($mappingTableTemp!==false)
				{
					$mappingTable = $mappingTableTemp;
				}
			}
			else
			{
				// create data row and fill it with the csv data 
				$dataRow = $this->CreateNewSyncDataRow($currentLineNumber);
				foreach ($mappingTable as $columnIndex => $column)
				{
					$dataCell = $dataRow->GetDataCellForColumn($column->GetId());
					if ($dataCell!=null)
					{
						$dataCell->SetCsvValue(trim($csvRow[$columnIndex]));
					}
				}
				// validate the row data
				$this->ValidateDataRow($dataRow);
				$dataRows[] = $dataRow;
			}
		}
		
		if ($mappingTable===null) return -1;
		if (count($dataRows)==0) return -2;
		$this->csvDataRows = $dataRows;
		return true;
	}
	
	/**
	 * Output HTML for import overview
	 * @return boolean
	 */
	public function ShowImportOverview()
	{
		if (count($this->csvDataRows)<=0) return false;
		$_SESSION["importDataRows"] = Array();
		$_SESSION["importDataRows"][get_class($this)] = Array();
		include('template/overview.inc.php5');
		return true;
	}
	
	/**
	 * Output HTML for import overview
	 * @return boolean
	 */
	public function ShowImportResult()
	{
		if (count($_SESSION["importDataRows"])<=0) return false;
		$dataRows = Array();
		// get all checked rows from session
		for ($i=0; $i<count($_SESSION["importDataRows"][get_class($this)]); $i++) 
		{
			//get only checked rows
			if ($_POST[$_SESSION["importDataRows"][get_class($this)][$i]["checkboxName"]] == "on") 
			{
				$dataRows[] = $_SESSION["importDataRows"][get_class($this)][$i]["data"];
			}
		}
		
		include('template/result.inc.php5');
		return true;
	}
	
	/**
	 * Import the selected data rows (fn ShowImportOverview())
	 * @return boolean
	 */
	public function ImportSelectedData()
	{
		LoggingManager::GetInstance()->Log(new LoggingSyncManager(get_class($this), LoggingSyncManager::TYPE_IMPORT));
		/* @var $dataRows SyncDataRow[]*/
		$dataRows = Array();
		// get all checked rows from session
		for ($i=0; $i<count($_SESSION["importDataRows"][get_class($this)]); $i++) 
		{
			//get only checked (and not allready imported) rows
			if ($_POST[$_SESSION["importDataRows"][get_class($this)][$i]["checkboxName"]] == "on" && $_SESSION["importDataRows"][get_class($this)][$i]["allreadyimported"]!==true) 
			{
				$dataRows[] = $_SESSION["importDataRows"][get_class($this)][$i]["data"];
				$dataRows[count($dataRows)-1]->Reset();
			}
		}
		// import data
		$result = true;
		$successfullyImported = Array();
		for ($i=0; $i<count($dataRows); $i++) 
		{
			// validate the row again
			$this->ValidateDataRow($dataRows[$i]);
			// check if row is valid
			if ($dataRows[$i]->IsValid())
			{
				// import the row
				if ($this->ImportDataRow($dataRows[$i])!==true)
				{
					// error while importing row
					$result = false;
				}
				else
				{
					$successfullyImported[] = $dataRows[$i];
				}
			}
			else
			{
				// row is invalid
				$dataRows[$i]->SetImportErrorMessage("Datensatz wurde nicht importiert, da nicht alle enthaltenen Daten gültig sind");
				$result = false;
			}
		}
		// mark all succesfully imported entrys in session to prevent duplicate import on page reload
		for ($a=0; $a<count($successfullyImported); $a++)
		{
			// find importet in session array
			for ($i=0; $i<count($_SESSION["importDataRows"][get_class($this)]); $i++) 
			{
				//get only checked rows
				if ($_SESSION["importDataRows"][get_class($this)][$i]["data"]->GetCsvLineNumber()==$successfullyImported[$a]->GetCsvLineNumber())
				{
					$_SESSION["importDataRows"][get_class($this)][$i]["allreadyimported"] = true;
				}
			}
		}
		
		return $result;
	}

	/**
	 * Check if the row is the head row
	 * @param array $csvRow
	 * @return boolean
	 */
	private function BuildCsvMappingTable($csvRow) 
	{
		$mappingTable = Array();
		// check if all column names are in csv row
		for ($a=0; $a<count($this->columns); $a++)
		{
			$found = false;
			for ($columnIndex = 0; $columnIndex < count($csvRow); $columnIndex++)
			{
				if (trim($this->columns[$a]->GetName())==trim($csvRow[$columnIndex]))
				{
					$mappingTable[$columnIndex] = $this->columns[$a];
					$found = true;
					break;
				}
			}
			// if column is not found return false
			if (!$found) return false;
		}
		// return mapping table
		return $mappingTable;
	}
	
	/**
	 * Return a new instance of class SyncDataRow
	 * @param int $currentLineNumber
	 * @return SyncDataRow
	 */
	protected function CreateNewSyncDataRow($currentLineNumber)
	{
		return new SyncDataRow($this->columns, $currentLineNumber);
	}
	
	/**
	 * Load the data from object and set it to SyncDataRow
	 * @param mixed[] $objects
	 * @return SyncDataRow
	 */
	protected function LoadDataFromObjects($objects)
	{
		$this->dbDataRows = Array();
		foreach ($objects as $object)
		{
			// Create new SyncDataRow instance
			$dataRow = $this->CreateNewSyncDataRow(0);

			// loop through each column an set the value
			foreach ($this->columns as $column)
			{
				$dataCell = $dataRow->GetDataCellForColumn($column->GetId());
				if ($dataCell!=null)
				{
					$value = $this->GetValueForColumnFromObject($column->GetId(), $object);
					$dataCell->SetValue($value);
				}
			}

			if ($dataRow!=null) $this->dbDataRows[] = $dataRow;
		}

	}
		
	/**
	 * Validate the data in the data row
	 * @param SyncDataRow $dataRow
	 */
	protected function ValidateDataRow(SyncDataRow $dataRow)
	{
		// Get the object for the current data row if exist (by ID)
		$object = $this->GetDataRowObject($dataRow);
		
		$rowsToSkip = $this->CheckRow($dataRow, $object);
				
		// loop through each column an set the value
		foreach ($this->columns as $column)
		{
			if (in_array($column->GetId(), $rowsToSkip)) continue;
			$dataCell = $dataRow->GetDataCellForColumn($column->GetId());
			if ($dataCell->IsValid($dataRow))
			{
				if ($object==null)
				{
					// new value
					$dataCell->SetValidationState(SyncDataCell::STATE_NEW);
				}
				else
				{
					if ($this->HasValueChanged($column->GetId(), $dataCell, $object, $dataRow))
					{
						// changed value
						$dataCell->SetValidationState(SyncDataCell::STATE_CHANGED);
					}
				}
			}
		}
	}
	
	/**
	 * check the row (is called before fn HasValueChanged() of each column and return an array with all columns to skip for fn HasValueChanged()
	 * @param SyncDataRow $dataRow
	 * @param DBEntry $object
	 * @return Array
	 */
	protected function CheckRow(SyncDataRow $dataRow, DBEntry $object=null)
	{
		return Array();
	}
	
	/**
	 * Import the data in the data row
	 * @param SyncDataRow $dataRow
	 * @return boolean
	 */
	protected function ImportDataRow(SyncDataRow $dataRow)
	{
		// Get the object for the current data row if exist (by ID)
		$object = $this->GetDataRowObject($dataRow);
		if ($object==null)
		{
			// new object
			$object = $this->CreateNewDataObject($this->db, $dataRow);
			if ($object==null) return false;
		}
		$errorOccured = false;
		
		// loop through each column an set the value
		foreach ($this->columns as $column)
		{
			// rentable area
			$dataCell = $dataRow->GetDataCellForColumn($column->GetId());
			if (!$this->SetValueOfColumnToObject($column->GetId(), $dataCell, $object, $dataRow))
			{
				$dataCell->SetImportErrorMessage("Fehler beim Setzen von '".$column->GetName()."'");
				$errorOccured = true;
			}
		}
		
		// store data to db
		if (!$errorOccured)
		{
			$returnValue = $object->Store($this->db);
			if ($returnValue===true) return true;
			$dataRow->SetImportErrorMessage("Fehler beim Speichern des Datensatzes (".$returnValue.")");
		}
		return false;
	}
	
	/**
	 * Return an new instance of the data object
	 * @param DBManager $db
	 * @param SyncDataRow $dataRow
	 * @return DBEntry
	 */
	abstract protected function CreateNewDataObject(DBManager $db, SyncDataRow $dataRow);
	
	/**
	 * Return the value of the object for the specified column
	 * @param int $columnId
	 * @param DBEntry $object
	 * @return mixed
	 */
	abstract protected function GetValueForColumnFromObject($columnId, DBEntry $object);
	
	/**
	 * get the object for the data row
	 * @param SyncDataRow $dataRow
	 * @return DBEntry
	 */
	abstract protected function GetDataRowObject(SyncDataRow $dataRow);
	
	/**
	 * Return the value of the object for the specified column
	 * @param int $columnId
	 * @param SyncDataCell $dataCell
	 * @param DBEntry $object
     * @param SyncDataRow $dataRow
	 * @return boolean
	 */
	abstract protected function HasValueChanged($columnId, SyncDataCell $dataCell, DBEntry $object, SyncDataRow $dataRow);
	
	/**
	 * Set the value of the object for the specified column
	 * @param int $columnId
	 * @param SyncDataCell $dataCell
	 * @param DBEntry $object
     * @param SyncDataRow $dataRow
	 * @return boolean
	 */
	abstract protected function SetValueOfColumnToObject($columnId, SyncDataCell $dataCell, DBEntry $object, SyncDataRow $dataRow);
	
	/**
	 * Initialize 
	 */
	abstract protected function LoadDataFromDatabase();
	
	/**
	 * Initialize the column objects for this SyncManager
	 */
	abstract protected function InitColumns();
	
	
	
}
?>