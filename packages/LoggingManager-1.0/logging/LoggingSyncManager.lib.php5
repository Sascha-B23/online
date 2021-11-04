<?php
/**
 * Logging class for SyncManager based import/export
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class LoggingSyncManager extends Logging
{
	/**
	 * Table name
	 */
	const TABLE_NAME = "LoggingSyncManager";
	
	/**
	 * Enums of loggin status
	 */
	const TYPE_UNKNOWN = 0;
	const TYPE_IMPORT = 1;
	const TYPE_EXPORT = 2;
	
	/**
	 * Name of the syncmanager
	 * @var string 
	 */
	protected $syncManagerName = "";
	
	/**
	 * Typ of the syncmanager action
	 * @var int 
	 */
	protected $type = self::TYPE_UNKNOWN;
	
	/**
	 * Constructor
	 * @param string $syncManagerName
	 * @param int $type
	 */
	public function LoggingSyncManager($syncManagerName, $type)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("syncManagerName", "type");
		$dbConfig->rowParam = Array("VARCHAR(255)", "INT");
		$dbConfig->rowIndex = Array("syncManagerName","type");
		parent::__construct($dbConfig);
		$this->syncManagerName = $syncManagerName;
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
		$rowName[] = "syncManagerName";
		$rowData[] = $this->syncManagerName;
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
		$this->syncManagerName = $data["syncManagerName"];
		$this->type = (int)$data["type"];
		return true;
	}
	
	/**
	 * Get the syncmanager name
	 * @return string
	 */
	public function GetSyncManagerName()
	{
		return $this->syncManagerName;
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