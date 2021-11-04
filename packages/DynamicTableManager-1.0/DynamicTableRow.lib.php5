<?php
/**
 * @author jglaser
 * @version 1.0
 * @created 20-Jul-2012 13:16:01
 */
class DynamicTableRow
{
	/**
	 * Group ID
	 * @var int 
	 */
	private $groupId = -1;
	
	/**
	 * Group ID the row belong to
	 * @var int 
	 */
	private $belongToGroupId = -1;
	
	/**
	 * Data of columns
	 * @var Array 
	 */
	private $columnData = Array();
	
	/**
	 * Set the group id the row belong to
	 * @param int $belongToGroupId
	 */
	public function SetGroupId($groupId)
	{
		$this->groupId = $groupId;
	}
	
	/**
	 * Set the group id the row belong to
	 * @param int $belongToGroupId
	 */
	public function SetBelongToGroupId($belongToGroupId)
	{
		$this->belongToGroupId = $belongToGroupId;
	}
	
	/**
	 * Add column data to table row
	 * @param string $columnId
	 * @param string $columnData
	 * @return boolean
	 */
	public function AddColumnData($columnId, $columnData)
	{
		for ($a=0; $a<count($this->columnData); $a++)
		{
			// overwrite data if column id allready exists
			if ($this->columnData[$a]["column_id"]==$columnId)
			{
				$this->columnData[$a]["content"]=$columnData;
				return true;
			}
		}
		// column id does not exist -> add now
		$this->columnData[] = Array("column_id" => $columnId, "content" => $columnData);
		return true;
	}
	
	/**
	 * Function to create the JSON Answer as Array
	 * @access  public
	 * @param  void
	 * @return  Array  The object information as array
	 */
	public function GetJSONAnswer()
	{	
		$returnValue = Array();
		if ($this->groupId!=-1) $returnValue["group_id"] = $this->groupId;
		if ($this->belongToGroupId!=-1) $returnValue["belong_to_group_id"] = $this->belongToGroupId;
		$returnValue["column_data"] = $this->columnData;
		return $returnValue;
	}
}
?>