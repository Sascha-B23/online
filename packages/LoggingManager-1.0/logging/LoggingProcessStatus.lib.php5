<?php
/**
 * Logging class for process status
 * @author Stephan Walleczek <s.walleczek@stollvongati.com>
 * @version 1.0
 */
class LoggingProcessStatus extends Logging
{
	/**
	 * Table name
	 */
	const TABLE_NAME = "LoggingProcessStatus";
	
	/**
	 * Enums of loggin status
	 */
	const TYPE_UNKNOWN = 0;
	const TYPE_DELETE = 1;
	const TYPE_MANUAL_STATUS_CHANGE = 2;
	
	/**
	 * Type of access
	 * @var int 
	 */
	protected $type = self::TYPE_UNKNOWN;
	
	/**
	 * Display type
	 * @var string 
	 */
	protected $newStatus = "";
	
	/**
	 * Additional, report type specific infos
	 * @var string 
	 */
	protected $oldStatus = "";
	
	/**
	 * Additional, report type specific infos
	 * @var string 
	 */
	protected $additionalInfo = "";
	
	/**
	 * Constructor
	 */
	public function LoggingProcessStatus($type, $newStatus, $oldStatus, $additionalInfo)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("type", "newStatus", "oldStatus", "additionalInfo");
		$dbConfig->rowParam = Array("INT", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(2550)");
		$dbConfig->rowIndex = Array("type");
		parent::__construct($dbConfig);
		
		$this->type = $type;
		$this->newStatus = $newStatus;
		$this->oldStatus = $oldStatus;
		$this->additionalInfo = $additionalInfo;
	}
	
	/**
	 * Write data to array
	 * @param DBManager $db
	 * @param array $rowName
	 * @param array $rowData
	 * @return boolean
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData) 
	{
		parent::BuildDBArray($db, $rowName, $rowData);
		$rowName[] = "type";
		$rowData[] = (int)$this->type;
		$rowName[] = "newStatus";
		$rowData[] = $this->newStatus;
		$rowName[] = "oldStatus";
		$rowData[] = $this->oldStatus;
		$rowName[] = "additionalInfo";
		$rowData[] = $this->additionalInfo;
		return true;
	}

	/**
	 * Read data from array
	 * @param DBManager $db
	 * @param array $data
	 * @return boolean
	 */
	protected function BuildFromDBArray(&$db, $data) 
	{
		parent::BuildFromDBArray($db, $data);
		$this->type = (int)$data["type"];
		$this->newStatus = $data["newStatus"];
		$this->additionalInfo = $data["additionalInfo"];
		$this->additionalInfo = $data["additionalInfo"];
		return true;
	}
	
	/**
	 * Get the type
	 * @return int
	 */
	public function GetType()
	{
		return $this->type;
	}

	/**
	 * Get the new status
	 * @return string
	 */
	public function GetNewStatus()
	{
		return $this->newStatus;
	}

	/**
	 * Get the old status
	 * @return string
	 */
	public function GetOldStatus()
	{
		return $this->oldStatus;
	}
	
	/**
	 * Return additional infos
	 * @return string
	 */
	public function GetAdditionalInfo()
	{
		return $this->additionalInfo;
	}

}
?>