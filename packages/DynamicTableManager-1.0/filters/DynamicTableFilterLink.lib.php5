<?php
/**
 * @author jglaser
 * @version 1.0
 * @created 20-Jul-2012 13:16:06
 */
class DynamicTableFilterLink extends DynamicTableFilter
{
	/**
	 * Filter active
	 * @var boolean 
	 */
	private $active = false;
	
	/**
	 * Constructor
	 * @param int $type
	 * @param string $text
	 * @param string $icon
	 */
	function DynamicTableFilterLink($type, $id="", $text="", $icon="") 
	{ 
		parent::__construct($type, $id, $text, $icon); 
	}
	
	/**
	 * Reset the filter to default settings
	 */
	public function ResetFilterToDefault()
	{
		$this->active = false;
	}
	
	/**
	 * Returns if the filter is active
	 * @return boolean
	 */
	public function IsFilterActive()
	{
		return $this->active;
	}
	
	/**
	 * Return if filter is active
	 * @return boolean
	 */
	public function GetActive()
	{
		return $this->active;
	}

	/**
	 * Set if the filter is avtive
	 * @param boolean $active
	 */
	public function SetActive($active)
	{
		$this->active = $active;
	}
		
	/**
	 * Function to create the JSON Answer as Array
	 * @return Array
	 */
	public function GetJSONAnswer()
	{
		$returnValue = parent::GetJSONAnswer();
		$returnValue["active"] = $this->active;
		return $returnValue;
	}
	
	/**
	 * Set the filter paramter
	 * @param array $params
	 */
	public function SetFilterParams($params)
	{
		$this->active = $params["active"];
	}

	/**
	 * Function is called to retrive all data to be stored in the filter setting templates
	 * return Array()
	 */
	public function OnStoreSettings()
	{
		$returnValue = parent::OnStoreSettings();
		$returnValue["active"] = $this->active;
		return $returnValue;
	}
	
	/**
	 * Function is called to retrive all data to be stored in the filter setting templates
	 * return Array()
	 */
	public function OnLoadSettings($settings)
	{
		if (!parent::OnLoadSettings($settings)) return false;
		$this->active = $settings["active"];
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
		return "";
	}

}
?>