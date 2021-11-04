<?php
/**
 * @author jglaser
 * @version 1.0
 * @created 20-Jul-2012 13:15:59
 */
class DynamicTableColumn
{
	/**
	 * Column Type
	 * @var int
	 */
	const TYPE_NONE = 0;
	const TYPE_INT = 1;
	const TYPE_FLOAT = 2;
	const TYPE_BOOL = 3;
	const TYPE_STRING = 4;
	const TYPE_DATE = 5;
	
	/**
	 * Sort direction
	 * @var int
	 */
	const SORT_NONE = 0;
	const SORT_ASC = 1;
	const SORT_DESC = 2;
	
	/**
	 * @property  String  $id  The DynamicTableColumnID (defaults to "")
	 */
	private $id = "";
	
	/**
	 * @property  String  $name  The name of the header column (defaults to "")
	 */
	private $name = "";
	
	/**
	 * Name of the table row
	 * @var string 
	 */
	protected $tableRowName = "";
	
	/**
	 * Name of the table row to sort by
	 * @var string 
	 */
	protected $tableSortRowName = "";
        
	/**
	 * The visibility state of the column
	 * @var bool
	 */
	private $visible = true;
	
	/**
	 * The default visibility state of the column
	 * @var bool
	 */
	protected $defaultVisible = true;
	
	/**
	 * @property  TableRowType  $type  The Table column data type (defaults to TableRowType::TYPE_NONE)
	 */
	private $type = self::TYPE_NONE;
	
	/**
	 * Style to set
	 * @var Array 
	 */
	protected $styles = Array();
	
	/**
	 * Sortable
	 * @var bool 
	 */
	protected $sortable = true;
	
	/**
	 * Current sort direction
	 * @var int 
	 */
	protected $sortdirection = self::SORT_NONE;
	
	/**
	 * Filters
	 * @var DynamicTableFilter[]
	 */
	private $filters = Array();
	
	/**
	 * The dynamic table the column belongs to
	 * @var DynamicTable 
	 */
	protected $dynamicTable = null;
	
	/**
	 * Constructor
	 * @param type $id
	 * @param type $name
	 * @param type $tableRowName
	 * @param type $tableDataType
	 * @param type $visible
	 * @param type $sortable
	 */
	public function DynamicTableColumn($id="", $name="", $tableRowName="", $tableDataType=self::TYPE_NONE, $visible=true, $sortable=true, $tableSortRowName="")
	{ 
		$this->id = $id;
		$this->name = $name;
		$this->tableRowName = $tableRowName;
		$this->tableSortRowName = ($tableSortRowName=='' ? $tableRowName : $tableSortRowName);
		$this->type = (int)$tableDataType;
		$this->visible = $visible;
		$this->defaultVisible = $this->visible;
		$this->sortable = $sortable;
	}

	/**
	 * Set the dynamic table
	 * @param DynamicTable $dynamicTable
	 */
	public function SetDynamicTable(DynamicTable $dynamicTable)
	{
		$this->dynamicTable = $dynamicTable;
	}
	
	/**
	 * Reset the visibilty to default
	 */
	public function ResetVisibility()
	{
		$this->visible = $this->defaultVisible;
	}
	
	/**
	 * Function to get the property id
	 * @access  public
	 * @param  void
	 * @return  String  The property id
	 */
	public function GetId()
	{
		return $this->id;
	}

	/**
	 * Function to set the property id
	 * @access  public
	 * @param  String  $newId  The new id
	 * @return  void
	 */
	public function SetId($newId)
	{
		$this->id = $newId;
	}

	/**
	 * Function to get the property name
	 * @access  public
	 * @param  void
	 * @return  String  The property name
	 */
	public function GetName()
	{
		return $this->name;
	}

	/**
	 * Function to set the property name
	 * @access  public
	 * @param  String  $newName  The new name
	 * @return  void
	 */
	public function SetName($newName)
	{
		$this->name = $newName;
	}

	/**
	 * Set a CSS Style
	 * @param string $name
	 * @param string $value
	 */
	public function SetCssStyle($name, $value)
	{
		$this->styles[$name] = $value;
	}
	
	/**
	 * Is column sortable
	 * @return boolean
	 */
	public function IsSortable()
	{
		return $this->sortable;
	}

	/**
	 * Get the sortdirection
	 * @return int
	 */
	public function GetSortdirection()
	{
		return $this->sortdirection;
	}

	/**
	 * Set the sortdirection
	 * @param int $sortdirection
	 */
	public function SetSortdirection($sortdirection)
	{
		$this->sortdirection = $sortdirection;
	}
	
	/**
	 * Get the table name to sort by
	 * @return string
	 */
	public function GetTableSortRowName()
	{
		return $this->tableSortRowName;
	}

	/**
	 * Set the table name to sort by
	 * @param string $tableSortRowName
	 */
	public function SetTableSortRowName($tableSortRowName)
	{
		$this->tableSortRowName = $tableSortRowName;
	}

		
	/**
	 * Get the table row name
	 * @return string
	 */
	public function GetTableRowName() 
	{
		return $this->tableRowName;
	}

	/**
	 * Set the table row name
	 * @param string $tableRowName
	 */
	public function SetTableRowName($tableRowName)
	{
		$this->tableRowName = $tableRowName;
	}

	/**
	 * Function to get the visibility property
	 * @access  public
	 * @param  void
	 * @return  Boolean  The visibility state (true or false)
	 */
	public function GetVisible()
	{
		return $this->visible;
	}

	/**
	 * Function to set the visibility property
	 * @access  public
	 * @param  Boolean  $newVisible  The new visibility state
	 * @return  void
	 */
	public function SetVisible($newVisible)
	{
		$this->visible = $newVisible;
	}

	/**
	 * Function to get the property type
	 * @access  public
	 * @param  void
	 * @return  int  The Column data type
	 */
	public function GetType()
	{
		return $this->type;
	}

	/**
	 * Function to set the type property
	 * @access  public
	 * @param  int  $newType  The new data type
	 * @return  void
	 */
	public function SetType($newType)
	{
		$this->type = $newType;
	}

	/**
	 * Add a filter to the column
	 * @param DynamicTableFilter $filter
	 */
	public function AddFilter(DynamicTableFilter $filter)
	{
		$filter->DynamicTableColumn($this);
		$this->filters[] = $filter;
	}

	/**
	 * Return the filter by type
	 * @param int $filterType
	 * @return DynamicTableFilter
	 */
	public function GetFilterByType($filterType)
	{
		foreach ($this->filters as $filter)
		{
			if ($filter->GetFilterType()==$filterType) return $filter;
		}
		return null;
	}
	
	/**
	 * Return the Filter with the specified id
	 * @param string $id
	 * @return DynamicTableFilter
	 */
	public function GetFilterById($id)
	{
		foreach ($this->filters as $filter)
		{
			if ($filter->GetId()==$id) return $filter;
		}
		return null;
	}
	
	/**
	 * Reset the filter to default settings
	 */
	public function ResetFilterToDefault()
	{
		foreach ($this->filters as $filter)
		{
			if ($filter->ResetFilterToDefault()) return true;
		}
		return false;
	}
	
	/**
	 * Returns if the filter is active
	 * @return boolean
	 */
	public function IsFilterActive()
	{
		foreach ($this->filters as $filter)
		{
			if ($filter->IsFilterActive()) return true;
		}
		return false;
	}
	
	/**
	 * Set the column paramter
	 * @param array $params
	 */
	public function SetColumnParams($params)
	{
		// set sorting direction 
		if (isset($params['sortdirection']) && is_numeric($params['sortdirection']))
		{
			if ((int)$params['sortdirection']==DynamicTableColumn::SORT_NONE || (int)$params['sortdirection']==DynamicTableColumn::SORT_ASC || (int)$params['sortdirection']==DynamicTableColumn::SORT_DESC)
			{
				if ($this->dynamicTable!=null) $this->dynamicTable->ClearSortByColumn();
				$this->sortdirection = (int)$params['sortdirection'];
			}
		}
		// hide/show column
		if (isset($params['visible']))
		{
			$this->visible = ($params['visible']==1 || $params['visible']=="true" ? true : false);
		}
		// reset filter to default values
		if (isset($params['reset']) && $params['reset']==1)
		{
			$this->ResetFilterToDefault();
		}
		
	}
	
	/**
	 * Function to create the JSON Answer as Array
	 * @access  public
	 * @param  void
	 * @return  Array  The object information as array
	 */
	public function GetJSONAnswer()
	{
		$returnData = Array(
			"id" => $this->id,
			"name" => $this->name,
			"visible" => $this->visible,
			"type" => $this->type,
			"sortable" => $this->sortable,
			"sortdirection" => $this->sortdirection,
			"filteractive" => $this->IsFilterActive(),
			"styles" => $this->styles
		);

		$returnData["filters"] = Array();
		foreach ($this->filters as $filter)
		{
			if (!isset($returnData["filters"][0])) $returnData["filters"][0] = Array();
			$returnData["filters"][0][] = $filter->GetJSONAnswer();
		}
		return $returnData;
	}
        
	/**
	 * Return the where clause for this filter
	 * @param DBManager $db
	 * @param string $rowName
	 * @return string
	 */
	public function GetWhereClause(DBManager $db)
	{
		if ($this->tableRowName=="") return "";
		$returnValue = "";
		foreach ($this->filters as $filter) 
		{
			$tempValue = trim($filter->GetWhereClause($db, $this));
			if ($tempValue!="")
			{
				if ($returnValue!='') $returnValue.= " AND ";
				$returnValue.="(".$tempValue.")";
			}
		}
		return $returnValue;
	}
	
	
	/**
	 * Function is called to retrive all data to be stored in the filter setting templates
	 * return Array()
	 */
	public function OnStoreSettings()
	{
		$returnData = Array("id" => $this->id,
							"visible" => $this->visible,
							"sortdirection" => $this->sortdirection
							);
		$returnData["filters"] = Array();
		foreach ($this->filters as $filter)
		{
			$returnData["filters"][] = $filter->OnStoreSettings();
		}
		return $returnData;
	}
	
	/**
	 * Function is called to retrive all data to be stored in the filter setting templates
	 * return Array()
	 */
	public function OnLoadSettings($settings)
	{
		if (!is_array($settings) || $settings["id"]!=$this->GetId()) return false;
		$this->ResetFilterToDefault();
		$this->visible = $settings["visible"];
		$this->sortdirection = $settings["sortdirection"];
		if (is_array($settings["filters"]))
		{
			foreach ($settings["filters"] as $filterData)
			{
				for ($a=0;count($this->filters);$a++)
				{
					if ($this->filters[$a]->OnLoadSettings($filterData)) break;
				}
			}
		}
		return true;
	}
	
}
?>