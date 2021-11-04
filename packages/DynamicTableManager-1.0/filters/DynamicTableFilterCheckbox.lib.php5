<?php
/**
 * @author jglaser
 * @version 1.0
 * @created 20-Jul-2012 13:16:06
 */
class DynamicTableFilterCheckbox extends DynamicTableFilter
{
	/**
	 * Filter data
	 * @var array 
	 */
	private $data = Array();
	
	/**
	 * Constructor
	 * @param string $text
	 * @param string $icon
	 */
	function DynamicTableFilterCheckbox($id="", $text="", $icon="")
	{
		parent::__construct($id, DynamicTableFilter::TYPE_FILTER_CHECKBOX, $text, $icon); 
	}
	
	/**
	 * Reset the filter to default settings
	 */
	public function ResetFilterToDefault()
	{
		for ($a=0; $a<count($this->data); $a++)
		{
			$this->data[$a]["checked"] = $this->data[$a]["default"];
		}
	}
	
	/**
	 * Returns if the filter is active
	 * @return boolean
	 */
	public function IsFilterActive()
	{
		for ($a=0; $a<count($this->data); $a++)
		{
			if ($this->data[$a]["checked"] != $this->data[$a]["default"]) return true;
		}
		return false;
	}
	
	/**
	 * Add a Checkbox to the filter
	 * @param string $id
	 * @param string $name
	 * @param bool $checked
	 * @param bool $default
	 */
	public function AddCheckbox($id, $name, $checked, $default=-1)
	{
		$this->data[] = Array(
			"id" => $id,
			"text" => $name,
			"checked" => $checked,
			"default" => (is_bool($default) ? $default : $checked)
		);
	}
	
	/**
	 * Return if the checkbox is checked
	 * @param string $id
	 * @return boolean|array
	 */
	public function IsCheckboxChecked($id)
	{
		foreach ($this->data as $checkbox) 
		{
			if ($checkbox['id']==$id) return $checkbox["checked"];
		}
		return false;
	}
	
	/**
	 * Function to create the JSON Answer as Array
	 * @return Array
	 */
	public function GetJSONAnswer()
	{
		$returnValue = parent::GetJSONAnswer();
		$returnValue["data"] = $this->data;
		return $returnValue;
	}
	
	/**
	 * Set the filter paramter
	 * @param array $params
	 */
	public function SetFilterParams($params)
	{
		if (!is_array($params) || !isset($params['data']) || !is_array($params['data']) ) return;
		foreach ($params['data'] as $value)
		{
			for($a=0; $a<count($this->data); $a++)
			{
				if ($this->data[$a]["id"]==$value["id"])
				{
					$this->data[$a]["checked"] = ($value["active"]==1  || $params['visible']=="true" ? true : false);
				}
			}
		}
	}
	
	/**
	 * Function is called to retrive all data to be stored in the filter setting templates
	 * return Array()
	 */
	public function OnStoreSettings()
	{
		$returnValue = parent::OnStoreSettings();
		$returnValue["data"] = Array();
		foreach ($this->data as $checkbox)
		{
			$returnValue["data"][] = Array("id" => $checkbox["id"], "checked" => $checkbox["checked"]);
		}
		return $returnValue;
	}
	
	/**
	 * Function is called to retrive all data to be stored in the filter setting templates
	 * return Array()
	 */
	public function OnLoadSettings($settings)
	{
		if (!parent::OnLoadSettings($settings)) return false;
		if (is_array($settings["data"]))
		{
			foreach ($settings["data"] as $data)
			{
				for ($a=0; $a<count($this->data); $a++)
				{
					if ($this->data[$a]["id"]==$data["id"])
					{
						$this->data[$a]["checked"] = $data["checked"];
						break;
					}
				}
			}
		}
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
		
		$filtersEnabled = Array();
		$filtersDisabled = Array();
//		print_r($this->data);
//		exit;
		foreach ($this->data as $checkbox) 
		{
			if ($checkbox['checked']==true)
			{
				$filtersEnabled[] = $checkbox;
			}
			else
			{
				$filtersDisabled[] = $checkbox;
			}
		}
		// if all checkboxes are checked return empty string because we want all data
		if (count($filtersDisabled)==0) return "";
		// build where clause
		foreach ($filtersDisabled as $checkbox)
		{
			if ($returnValue!="") $returnValue.=" AND ";
			if ($rowType==DynamicTableColumn::TYPE_INT)$returnValue.=$rowName."!=".(int)$db->ConvertStringToDBString($checkbox['id'])." ";
			elseif ($rowType==DynamicTableColumn::TYPE_FLOAT)$returnValue.=$rowName."!=".(float)$db->ConvertStringToDBString($checkbox['id'])." ";
			else $returnValue.=$rowName."!='".$db->ConvertStringToDBString($checkbox['id'])."'";
		}
		return $returnValue;
	}

}
?>