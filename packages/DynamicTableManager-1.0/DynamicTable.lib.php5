<?php
/**
 * @author jglaser, Stephan Walleczek <s.walleczek@stollvongati.com>
 * @version 1.0
 * @created 20-Jul-2012 13:15:57
 */
abstract class DynamicTable
{
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * Unique ID of the dynamic table
	 * @var string
	 */
	protected $tableId = "";
	
	/**
	 * Use the PHP session for optimized JSON-requests?
	 * @var bool 
	 */
	protected $useSession = false;
	
	/**
	 * The dynamic table information (defaults to null)
	 * @var DynamicTableInformation
	 */
	protected $dynamicTableInformation = null;
	
	/**
	 * All Columns of the table
	 * @var DynamicTableColumn[]
	 */
	protected $dynamicTableColumns = Array();
	
	/**
	 * The dynamic table rows
	 * @var DynamicTableRow[]
	 */
	protected $dynamicTableRows = Array();
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param string $tableId
	 */
	public function DynamicTable(DBManager $db, $tableId='')
	{
		$this->db = $db;
		$this->tableId = trim($tableId);
		if ($this->tableId=="") $this->tableId = get_class($this);
		$this->ResetConfiguration($db);
		DynamicTableManager::GetInstance()->AddDynamicTable($this);
		// default config laden...
		DynamicTableConfigManager::LoadDefaultDynamicTableConfig($this->db, $_SESSION["currentUser"], $this);
	}
	
	/**
	 * Return the id of the table
	 * @return string
	 */
	public function GetId() 
	{
		return $this->tableId;
	}

	/**
	 * Set the current DBManager
	 * @param DBManager $db
	 */
	public function ResetObjects(DBManager $db)
	{
		$this->db = $db;
	}
	
	/**
	 * Reset table configuration to standard
	 * @param DBManager $db
	 */
	public function ResetConfiguration(DBManager $db)
	{
		$this->dynamicTableColumns = Array();
		$this->dynamicTableRows = Array();
		if (count($this->GetDynamicTableColumns())==0)
		{
			$columns = $this->GetDefaultColumnConfig($db);
			foreach ($columns as $column)
			{
				$this->AddDynamicTableColumn($column);
			}
		}
	}
	
	/**
	 * Return the default colmn config for this list
	 * @param DBManager $db
	 * @return DynamicTableColumn[]
	 */
	abstract protected function GetDefaultColumnConfig(DBManager $db);
	
	/**
	 * Return the total number of entries 
	 * @return int
	 */
	abstract protected function GetTotalDataCount();
	
	/**
	 * Load Data from DB depending on the active filters
	 */
	abstract protected function LoadDataWithFilterOptions();
	
	/**
	 * Function to get the DynamicTableInformation
	 * @return  DynamicTableInformation  The DynamicTableInformation object
	 */
	public function GetDynamicTableInformation()
	{
		return $this->dynamicTableInformation;
	}

	/**
	 * Function to set the DynamicTableInformation
	 * @param  DynamicTableInformation  $newDynamicTableInformation  The new DynamicTableInformation
	 */
	public function SetDynamicTableInformation(DynamicTableInformation $newDynamicTableInformation)
	{
		$this->dynamicTableInformation = $newDynamicTableInformation;
	}
	
	/**
	 * Get All DynamicTableColumns
	 * @return  DynamicTableColumn[]  The DynamicTableColumns
	 */
	public function GetDynamicTableColumns()
	{
		return $this->dynamicTableColumns;
	}
	
	/**
	 * Return the Column with the specified id
	 * @param string $id
	 * @return DynamicTableColumn
	 */
	public function GetDynamicTableColumnById($id)
	{
		for ($a=0; $a<count($this->dynamicTableColumns); $a++)
		{
			if ($this->dynamicTableColumns[$a]->GetId()==$id)
			{
				return $this->dynamicTableColumns[$a];
			}
		}
		return null;
	}
	
	/**
	 * Set All DynamicTableColumns
	 * @param  DynamicTableColumn[] $newDynamicTableColumns The new columns
	 */
	public function SetDynamicTableColumns(Array $newDynamicTableColumns)
	{
		$this->dynamicTableColumns = $newDynamicTableColumns;
	}
	
	/**
	 * Adds a DynamicTableColumn
	 * @param  DynamicTableColumn $newDynamicTableColumn The new column
	 */
	public function AddDynamicTableColumn(DynamicTableColumn $newDynamicTableColumn)
	{
		$newDynamicTableColumn->SetDynamicTable($this);
		$this->dynamicTableColumns[] = $newDynamicTableColumn;
	}
	
	/**
	 * Get All DynamicTableRows
	 * @return  DynamicTableRow[]  The DynamicTableRows
	 */
	public function GetDynamicTableRows()
	{
		return $this->dynamicTableRows;
	}
	
	/**
	 * Set All DynamicTableRows
	 * @param  DynamicTableRow[]  $newDynamicTableRows  The new rows
	 */
	public function SetDynamicTableRows($newDynamicTableRows)
	{
		$this->dynamicTableRows = $newDynamicTableRows;
	}
	
	/**
	 * Add a DynamicTableRow
	 * @param  DynamicTableRow  $newDynamicTableRow  The new row
	 */
	public function AddDynamicTableRow($newDynamicTableRow)
	{
		$this->dynamicTableRows[] = $newDynamicTableRows;
	}
	
	/**
	 * Return the current "sort by"-column 
	 * @return DynamicTableColumn
	 */
	public function GetOrderByColumn()
	{
		foreach ($this->dynamicTableColumns as $column)
		{
			if (!$column->IsSortable()) continue;
			$sortDirection = $column->GetSortdirection();
			if ($sortDirection!=DynamicTableColumn::SORT_ASC && $sortDirection!=DynamicTableColumn::SORT_DESC) continue;
			return $column;
		}
	}

	/**
	 * Set all columns sortdirection to SORT_NONE
	 */
	public function ClearSortByColumn()
	{
		foreach ($this->dynamicTableColumns as $column)
		{
			if (!$column->IsSortable()) continue;
			$column->SetSortdirection(DynamicTableColumn::SORT_NONE);
		}
	}
	
	/**
	 * Function to create the JSON Answer
	 * @return  Array  The Answer as Array
	 */
	public function GetJSONAnswer()
	{
		// get the total data count
		$totalDataCount = $this->GetTotalDataCount();
		// set paging information
		$information = $this->GetDynamicTableInformation();
		$information->SetEntriesCount($totalDataCount);
		$information->SetPageCount($information->GetEntriesPerPage()==0 ? 1 : ceil($information->GetEntriesCount() / $information->GetEntriesPerPage()));
		
		if ($information->GetCurrentPage() >= $information->GetPageCount())
		{
			$information->SetCurrentPage($information->GetPageCount()-1);
		}
		// Execute query
		$this->LoadDataWithFilterOptions();
		
		
		// build array
		$returnData = Array();
		
		// Get the "request_information" part
		$returnData["request_information"] = Array(
			"session_active" => $this->useSession,
			"reload_table" => true /* TODO: Header + Information GetTableReloadStatus() */
		);
		
		// Get the "information" part
		$returnData["information"] = $this->dynamicTableInformation->GetJSONAnswer();
		$returnData["information"]["uniqueViewId"] = $this->GetId();
		$returnData["information"]["configurations"] = $this->GetConfigurationsJSONAnswer($this->db);
		
		// Get the "header" part
		$returnData["header"] = Array();
		for ($i=0; $i<count($this->dynamicTableColumns); $i++)
		{
			if ($this->dynamicTableColumns[$i] == null) continue;
			$returnData["header"][] = $this->dynamicTableColumns[$i]->GetJSONAnswer();
		}
		
		// Get the "content" part
		$returnData["content"] = Array();
		for ($i=0; $i<count($this->dynamicTableRows); $i++)
		{
			if ($this->dynamicTableRows[$i] == null) continue;
			$returnData["content"][] = $this->dynamicTableRows[$i]->GetJSONAnswer();
		}
		//print_r($returnData);
		//exit;
		return $returnData;
	}
	
	/**
	 * Return all available configurations for the current user of this table
	 * @param DBManager $db
	 */
	protected function GetConfigurationsJSONAnswer(DBManager $db)
	{
		global $SHARED_HTTP_ROOT;
		$returnData = Array();
		$manager = new DynamicTableConfigManager($db);
		$configurations = $manager->GetDynamicTableConfigs($_SESSION["currentUser"], $this, "", "name", 0, 0, 0);
		$returnData[] = Array("name" => "Standardkonfiguration", "id" => "-1");
		foreach ($configurations as $configuration)
		{
			$returnData[] = Array("name" => $configuration->GetName(), "id" => $configuration->GetPKey());
		}
		$returnData[] = Array("name" => "Filterkonfigurationen verwalten...", "ligtboxurl" => $SHARED_HTTP_ROOT."de/dynamicTable/configurate.php5?".SID."&tableId=".$this->GetId());
		return $returnData;
	}
	
	/**
	 * Function to set the table parameters
	 * @param  Array  $tableParams  The post/get parameters sent by the JSON Request
	 */
	public function SetTableParams($tableParams)
	{
		// Lade config...
		if (isset($tableParams["loadconfiguration"]) && $tableParams["loadconfiguration"]=="true")
		{
			if ((int)$tableParams["configurationID"]==-1)
			{
				$this->ResetConfiguration($this->db);
			}
			else
			{
				DynamicTableConfigManager::LoadDynamicTableConfigById($this->db, $_SESSION["currentUser"], $this, (int)$tableParams["configurationID"]);
			}
			return;
		}
		
		//file_put_contents('test.txt', print_r($tableParams, true));  // -> logfiles/test.txt 
		// table information
		if (isset($tableParams["information"]))
		{
			foreach ($tableParams["information"] as $key => $value)
			{
				$value = trim($value);
				if ($value == "")
				{
					//unset($tableParams["information"][$key]);
					continue;
				}
				$tableParams["information"][$key] = $value;
			}
			// ENTRIES PER PAGE
			if (isset($tableParams["information"]["entriesperpage"]))
			{
				$this->dynamicTableInformation->SetEntriesPerPage((int)$tableParams["information"]["entriesperpage"]);
			}
			// MINIMIZED STATUS
			if (isset($tableParams["information"]["minimized"]))
			{
				$this->dynamicTableInformation->SetMinimized(($tableParams["information"]["minimized"]=="true") ? true : false);
			}
			// PAGE NUMBER
			if (isset($tableParams["information"]["page_input"]))
			{
				$this->dynamicTableInformation->SetCurrentPage(((int)$tableParams["information"]["page_input"])-1);
			}
			
			if (isset($tableParams["information"]["search_input"]))
			{
				$this->dynamicTableInformation->SetSearch($tableParams["information"]["search_input"]);
			}
		}
		
		// restet column selection
		if (isset($tableParams["resetheaderfilters"]) && (int)$tableParams["resetheaderfilters"]==1)
		{
			for ($a=0; $a<count($this->dynamicTableColumns); $a++)
			{
				$this->dynamicTableColumns[$a]->ResetVisibility();
			}
		}
		
		
		
		// column header
		if (isset($tableParams["header"]) && is_array($tableParams["header"]))
		{
			foreach ($tableParams["header"] as $value)
			{
				/*if ($value['id']==-11)
				{
					
				}
				else
				{*/
					$column = $this->GetDynamicTableColumnById($value['id']);
					if ($column!=null)
					{
						$column->SetColumnParams($value);
					}
				//}
			}
		}
		
		// filters
		if (isset($tableParams["filters"]) && is_array($tableParams["filters"]))
		{
			foreach ($tableParams["filters"] as $key => $value)
			{
				$column = $this->GetDynamicTableColumnById($key);
				if ($column!=null)
				{
					//print_r($value);
					foreach ($value as $filterParams)
					{
						$filter = $column->GetFilterById($filterParams['id']);
						if ($filter!=null) $filter->SetFilterParams($filterParams);
					}
				}
			}
		}

	}
	
	/**
	 * Clear table
	 */
	protected function ClearData()
	{
		$this->dynamicTableInformation = null;
		$this->dynamicTableColumns = Array();
		$this->dynamicTableRows = Array();
	}
	
	/**
	 * Return the where clause for this filter
	 * @param DBManager $db
	 * @return string
	 */
	public function GetWhereClause(DBManager $db)
	{
		$whereClause = "";
		foreach ($this->dynamicTableColumns as $column)
		{
			$tempValue = trim($column->GetWhereClause($this->db));
			if ($tempValue!="")
			{
				if ($whereClause!="") $whereClause.=" AND ";
				$whereClause.="(".$tempValue.")";
			}
		}
		//echo $whereClause;
		return $whereClause;
	}
	
	/**
	 * Return the current table setting (incl. entries per page, column visibility, column ordering, column filter settings)
	 */
	public function StoreSettings()
	{
		$dataToStore = Array();
		$dataToStore['tableId'] = $this->GetId();
		$dataToStore['dynamicTableInformation'] = $this->dynamicTableInformation->OnStoreSettings();
		foreach ($this->dynamicTableColumns as $dynamicTableColumn)
		{
			$dataToStore['dynamicTableColumns'][] = $dynamicTableColumn->OnStoreSettings();
		}
		return $dataToStore;
	}
	
	/**
	 * 
	 * @param type $settings
	 * @return boolean
	 */
	public function LoadSettings($settings)
	{
		if (!is_array($settings)) return false;
		if ($settings['tableId']!=$this->GetId()) return false;
		if (!$this->dynamicTableInformation->OnLoadSettings($settings['dynamicTableInformation'])) return false;
		$this->ClearSortByColumn();
		if (is_array($settings["dynamicTableColumns"]))
		{
			foreach ($settings['dynamicTableColumns'] as $dynamicTableColumnData)
			{
				for ($a=0; $a<count($this->dynamicTableColumns); $a++)
				{
					if ($this->dynamicTableColumns[$a]->OnLoadSettings($dynamicTableColumnData)) break;
				}
			}
		}
		return true;
	}
	
}
?>