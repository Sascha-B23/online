<?php
/**
 * Logging class for WS customer view
 * @author Stephan Walleczek <s.walleczek@stollvongati.com>
 * @version 1.0
 */
class LoggingWsCustomerUpload extends Logging
{
	/**
	 * Table name
	 */
	const TABLE_NAME = "WsCustomerUpload";
	
	/**
	 * process path
	 * @var string 
	 */
	protected $processPath = "";
	
	/**
	 * filename
	 * @var string 
	 */
	protected $fileName = "";
	
	/**
	 * Constructor
	 */
	public function LoggingWsCustomerUpload($processPath, $fileName)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array( "processPath", "fileName");
		$dbConfig->rowParam = Array("VARCHAR(255)", "VARCHAR(255)");
		$dbConfig->rowIndex = Array();
		parent::__construct($dbConfig);
		$this->processPath = $processPath;
		$this->fileName = $fileName;
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
		$rowName[] = "fileName";
		$rowData[] = $this->fileName;
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
		$this->fileName = $data["fileName"];
		return true;
	}
	
}
?>