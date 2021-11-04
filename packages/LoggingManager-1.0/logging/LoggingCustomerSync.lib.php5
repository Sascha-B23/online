<?php
/**
 * Logging class for Customer DB import/export
 * @author Stephan Walleczek <s.walleczek@stollvongati.com>
 * @version 1.0
 */
class LoggingCustomerSync extends Logging
{
	/**
	 * Table name
	 */
	const TABLE_NAME = "LoggingCustomerSync";
	
	/**
	 * Enums of loggin status
	 */
	const TYPE_UNKNOWN = 0;
	const TYPE_IMPORT = 1;
	const TYPE_EXPORT = 2;
	
	/**
	 * 
	 * @var int 
	 */
	protected $type = self::TYPE_UNKNOWN;
	
	/**
	 * Constructor
	 */
	public function LoggingCustomerSync($type)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("type");
		$dbConfig->rowParam = Array("INT");
		$dbConfig->rowIndex = Array("type");
		parent::__construct($dbConfig);
		
		$this->type = $type;
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
		$rowData[] = $this->type;
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

}
?>