<?php
/**
 * Base class for all logging classes
 * @author Stephan Walleczek <s.walleczek@stollvongati.com>
 * @version 1.0
 */
abstract class Logging extends DBEntry
{
	/**
	 * The session ID
	 * @var string 
	 */
	protected $sessionId = "";
	
	/**
	 * Constructor
	 */
	public function Logging(DBConfig $dbConfig)
	{
		$this->dbConfig = $dbConfig;
		$this->dbConfig->InsertRowsAt(Array("sessionId"), Array("VARCHAR(255)"), Array("sessionId"));
		$this->dbConfig->InsertRowsAt($this->additionalTableRowNames, $this->additionalTableRowParams, $this->additionalTableIndex);
		$this->sessionId = session_id();
	}
	
	/**
	 * Update table in the db
	 */
	public function UpdateDB(DBManager $db)
	{
		$db->CreateOrUpdateTable($this->dbConfig);
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
		$rowName[] = "sessionId";
		$rowData[] = $this->sessionId;
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
		$this->sessionId = $data["sessionId"];
		return true;
	}
	
	/**
	 * Return the session id
	 * @return string
	 */
	public function GetSessionId() 
	{
		return $this->sessionId;
	}
	
	
	
}
?>