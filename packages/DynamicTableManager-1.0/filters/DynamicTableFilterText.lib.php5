<?php
/**
 * @author jglaser
 * @version 1.0
 * @created 20-Jul-2012 13:16:06
 */
class DynamicTableFilterText extends DynamicTableFilter
{
	/**
	 * Search string
	 * @var string 
	 */
	private $value = "";
	
	/**
	 * Regex which is used to verify search string
	 * @var string 
	 */
	protected $regEx = "";
	
	/**
	 * Hint to show near serch field
	 * @var string  
	 */
	protected $hint = "";
	
	/**
	 * Constructor
	 * @param int $type
	 * @param string $text
	 * @param string $icon
	 */
	function DynamicTableFilterText($type, $id="", $text="", $icon="")
	{ 
		parent::__construct($type, $id, $text, $icon); 
	}

	/**
	 * Reset the filter to default settings
	 */
	public function ResetFilterToDefault()
	{
		$this->value = "";
	}
	
	/**
	 * Returns if the filter is active
	 * @return boolean
	 */
	public function IsFilterActive()
	{
		return ($this->value != "");
	}
	
	/**
	 * Get the value
	 * @return string
	 */
	public function GetValue()
	{
		return $this->value;
	}

	/**
	 * Set the value
	 * @param string $value
	 */
	public function SetValue($value)
	{
		$this->value = $value;
	}
	
	/**
	 * Get the hint
	 * @return string
	 */
	public function GetHint() 
	{
		return $this->hint;
	}

	/**
	 * Set the hint
	 * @param string $hint
	 */
	public function SetHint($hint) 
	{
		$this->hint = $hint;
	}

		
	/**
	 * Function to create the JSON Answer as Array
	 * @return Array
	 */
	public function GetJSONAnswer()
	{
		$returnValue = parent::GetJSONAnswer();
		$returnValue["value"] = $this->value;
		$returnValue["regEx"] = $this->GetRegEx();
		$returnValue["hint"] = $this->GetHintString();
		return $returnValue;
	}
	
	/**
	 * Return the regex
	 * @return string
	 */
	protected function GetRegEx()
	{
		if (trim($this->regEx)!="") return $this->regEx;
		if ($this->dynamicTableColumn==null) return "";
		switch ($this->dynamicTableColumn->GetType())
		{
			case DynamicTableColumn::TYPE_INT:
				return "[0-9]*";
			case DynamicTableColumn::TYPE_FLOAT:
				return "[0-9]*.{0,1}[0-9]*";
			case DynamicTableColumn::TYPE_DATE:
				return "^((([0]?[1-9]|[1-2][0-9]|[3][0-1])[\.\/\:\-]([0]?[13578]|[1][02]))|(([0]?[1-9]|[1-2][0-9]|[3][0])[\.\/\:\-]([0]?[469]|[1][1]))|(([0]?[1-9]|[1-2][0-9])[\.\/\:\-][0]?[2]))[\.\/\:\-](([1][9][7-9][0-9])|([2][0-9]{3}))$";
		}
		return "";
	}
	
	/**
	 * Return the hint
	 * @return string
	 */
	protected function GetHintString()
	{
		if (trim($this->hint)!="") return $this->hint;
		if ($this->dynamicTableColumn==null) return "";
		switch ($this->dynamicTableColumn->GetType())
		{
			case DynamicTableColumn::TYPE_INT:
				return "Format: Ganzahl";
			case DynamicTableColumn::TYPE_FLOAT:
				return "Format: Dezimalzahl";
			case DynamicTableColumn::TYPE_DATE:
				return "Format: tt.mm.jjjj";
		}
		return "";
	}
	
	/**
	 * Set the filter paramter
	 * @param array $params
	 */
	public function SetFilterParams($params)
	{
		$this->value = $params["value"];
	}
	
	/**
	 * Function is called to retrive all data to be stored in the filter setting templates
	 * return Array()
	 */
	public function OnStoreSettings()
	{
		$returnValue = parent::OnStoreSettings();
		$returnValue["value"] = $this->value;
		return $returnValue;
	}
	
	/**
	 * Function is called to retrive all data to be stored in the filter setting templates
	 * return Array()
	 */
	public function OnLoadSettings($settings)
	{
		if (!parent::OnLoadSettings($settings)) return false;
		$this->value = $settings["value"];
		return true;
	}
	
	/**
	 * Return the where clause for this filter
	 * @param DBManager $db
	 * @param DynamicTableColumn $column
	 * @return string
	 */
	protected function GetDefaultWhereClause(DBManager $db, DynamicTableColumn $column)
	{
		$returnValue = "";
		$rowName = $column->GetTableRowName();
		if ($rowName=="") return "";
		$rowType = $column->GetType();
		
		if (trim($this->GetValue())!='')
		{
			if ($this->GetFilterType()==DynamicTableFilter::TYPE_FILTER_FROM || $this->GetFilterType()==DynamicTableFilter::TYPE_FILTER_TO)
			{
				$returnValue = $rowName;
				if ($this->GetFilterType()==DynamicTableFilter::TYPE_FILTER_FROM) $returnValue.=">=";
				if ($this->GetFilterType()==DynamicTableFilter::TYPE_FILTER_TO) $returnValue.="<=";
				
				if ($rowType==DynamicTableColumn::TYPE_INT)
				{
					$returnValue.=(int)$this->GetValue();
				}
				elseif ($rowType==DynamicTableColumn::TYPE_FLOAT)
				{
					$returnValue.=(float)$this->GetValue();
				}
				elseif ($rowType==DynamicTableColumn::TYPE_DATE)
				{
					$parts = explode(".", $this->GetValue());
					$returnValue.=(int)mktime(0, 0, 0, $parts[1], $parts[0], $parts[2])+($this->GetFilterType()==DynamicTableFilter::TYPE_FILTER_TO ? 60*60*24 : 0);
				}
			}
			else
			{
				$returnValue = $rowName." LIKE '%".$db->ConvertStringToDBString(trim($this->GetValue()))."%'";
			}
		}
		return $returnValue;
	}
	
}
?>