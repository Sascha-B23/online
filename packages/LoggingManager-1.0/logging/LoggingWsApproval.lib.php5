<?php
/**
 * Logging class for WS approval
 * @author Stephan Walleczek <s.walleczek@stollvongati.com>
 * @version 1.0
 */
class LoggingWsApproval extends Logging
{
	/**
	 * Table name
	 */
	const TABLE_NAME = "WsApproval";
	
	/**
	 * process path
	 * @var string 
	 */
	protected $processPath = "";
	
	/**
	 * Constructor
	 */
	public function LoggingWsApproval($processPath)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("processPath");
		$dbConfig->rowParam = Array("VARCHAR(255)");
		$dbConfig->rowIndex = Array();
		parent::__construct($dbConfig);
		$this->processPath = $processPath;
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
		$rowName[] = "processPath";
		$rowData[] = $this->processPath;
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
		$this->processPath = $data["processPath"];
		return true;
	}
	
}
?>