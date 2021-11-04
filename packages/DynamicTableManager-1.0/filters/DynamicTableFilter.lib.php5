<?php
/**
 * Baseclass for all filter types 
 * @author jglaser, Stephan Walleczek <s.walleczek@stollvongati.com>
 * @version 1.0
 * @created 20-Jul-2012 13:16:06
 */
abstract class DynamicTableFilter
{
	/**
	 * Filter Type
	 */
	const TYPE_NONE = 0;
	const TYPE_SORT_AZ = 1;
	const TYPE_SORT_ZA = 2;
	const TYPE_FILTER_CHECKBOX = 3;
	const TYPE_FILTER_FROM = 4;
	const TYPE_FILTER_TO = 5;
	const TYPE_FILTER_TEXTSEARCH = 6;
	const TYPE_FILTER_BUTTONLIST = 9;
	
	/**
	 * Typ of the filter
	 * @var int
	 */
	protected $filterType = self::TYPE_NONE;
	
	/**
	 * ID of the filter
	 * @var string 
	 */
	protected $id = "";
	
	/**
	 * Icon to disyplay
	 * @var string 
	 */
	protected $icon = "";
	
	/**
	 * Text to disyplay
	 * @var string 
	 */
	protected $text = "";
	
	/**
	 * Dynamic table
	 * @var DynamicTableColumn $dynamicTableColumn
	 */
	protected $dynamicTableColumn = null;
	
	/**
	 * Query object
	 * @var DynamicTableQuery 
	 */
	protected $dynamicTableQuery = null;

	/**
	 * Constructor
	 * @param int $type
	 */
	function DynamicTableFilter($type, $id, $text, $icon="")
	{ 
		$this->id = $id;
		$this->filterType = $type;
		$this->text = $text;
		if ($this->text=="") $this->text = $this->GetTextByType($this->filterType);
		$this->icon = $icon;
		if ($this->icon=="") $this->icon = $this->GetIconByType($this->filterType);
	}
	
	/**
	 * Set the column
	 * @param DynamicTableColumn $dynamicTableColumn
	 */
	public function DynamicTableColumn(DynamicTableColumn $dynamicTableColumn)
	{
		$this->dynamicTableColumn = $dynamicTableColumn;
	}
	
	/**
	 * Set the query object
	 * @param DynamicTableQuery $dynamicTableQuery
	 */
	public function SetQuery(DynamicTableQuery $dynamicTableQuery=null)
	{
		$this->dynamicTableQuery = $dynamicTableQuery;
	}
	
	/**
	 * Reset the filter to default settings
	 */
	abstract public function ResetFilterToDefault();
	
	/**
	 * Returns if the filter is active
	 * @return boolean
	 */
	abstract public function IsFilterActive();
	
	/**
	 * Get the id of the column
	 * @return string
	 */
	public function GetId()
	{
		return $this->id;
	}
		
	/**
	 * Get the icon
	 * @return string
	 */
	public function GetIcon()
	{
		return $this->icon;
	}

	/**
	 * Set the icon
	 * @param string $icon
	 */
	public function SetIcon($icon)
	{
		$this->icon = $icon;
	}

	/**
	 * Get the text
	 * @return string
	 */
	public function GetText()
	{
		return $this->text;
	}

	/**
	 * Set the text
	 * @param string $text
	 */
	public function SetText($text)
	{
		$this->text = $text;
	}
		
	/**
	 * Function to get the filter type
	 * @return int
	 */
	public function GetFilterType()
	{
		return $this->filterType;
	}
	
	/**
	 * Function to create the JSON Answer as Array
	 * @return Array
	 */
	public function GetJSONAnswer()
	{		
		return Array(
			"id" => $this->id,
			"type" => $this->filterType,
			"icon" => $this->icon,
			"text" => $this->text
		);
	}
	
	/**
	 * Set the filter paramter
	 * @param array $params
	 */
	abstract public function SetFilterParams($params);
	
	/**
	 * Helperfunction to create filter by type
	 * @param int $type
	 * @return DynamicTableFilter
	 */
	public static function LoadFilterByType($type)
	{
		switch ((int)$type)
		{
			case self::TYPE_SORT_AZ:
			case self::TYPE_SORT_ZA:
				return new DynamicTableFilterLink((int)$type);
				break;
			
			case self::TYPE_FILTER_FROM:
			case self::TYPE_FILTER_TO:
			case self::TYPE_FILTER_TEXTSEARCH:
				return new DynamicTableFilterText((int)$type);
				break;
			
			case self::TYPE_FILTER_CHECKBOX:
				return new DynamicTableFilterCheckbox((int)$type);
				break;
		}
		return null;
	}
	
	/**
	 * Return the icon name by filter type
	 * @param int $type
	 * @return string
	 */
	private function GetIconByType($type)
	{
		switch ((int)$type)
		{
			case self::TYPE_SORT_AZ:
				return "";
			case self::TYPE_SORT_ZA:
				return "";
			case self::TYPE_FILTER_TEXTSEARCH:
				return "";
			case self::TYPE_FILTER_CHECKBOX:
				return "";
		}
		return "";
	}
	
	/**
	 * Return the icon name by filter type
	 * @param int $type
	 * @return string
	 */
	private function GetTextByType($type)
	{
		switch ((int)$type)
		{
			case self::TYPE_SORT_AZ:
				return "Sortieren A bis Z";
			case self::TYPE_SORT_ZA:
				return "Sortieren Z bis A";
			case self::TYPE_FILTER_TEXTSEARCH:
				return "Suche";
			case self::TYPE_FILTER_CHECKBOX:
				return "";
			case self::TYPE_FILTER_FROM:
				return "Von";
			case self::TYPE_FILTER_TO:
				return "Bis";
		}
		return "???";
	}
	
	/**
	 * Return the where clause for this filter
	 * @param DBManager $db
	 * @param DynamicTableColumn $column
	 * @return string
	 */
	public function GetWhereClause(DBManager $db, DynamicTableColumn $column)
	{
		if ($this->dynamicTableQuery!=null)
		{
			return $this->dynamicTableQuery->GetWhereClause($db, $column);
		}
		return $this->GetDefaultWhereClause($db, $column);
	}
	
	/**
	 * Function is called to retrive all data to be stored in the filter setting templates
	 * return Array()
	 */
	public function OnStoreSettings()
	{
		$returnValue = Array("id" => $this->id);
		return $returnValue;
	}
	
	/**
	 * Function is called to retrive all data to be stored in the filter setting templates
	 * return Array()
	 */
	public function OnLoadSettings($settings)
	{
		if ($settings["id"]!=$this->GetId()) return false;
		return true;
	}
	
	/**
	 * Return the default where clause for this filter
	 * @param DBManager $db
	 * @param DynamicTableColumn $column
	 * @return string
	 */
	abstract protected function GetDefaultWhereClause(DBManager $db, DynamicTableColumn $column);
        
}
?>