<?php
/**
 * Logging class for Login/Logout
 * @author Stephan Walleczek <s.walleczek@stollvongati.com>
 * @version 1.0
 */
class LoggingLogin extends Logging
{
	/**
	 * Table name
	 */
	const TABLE_NAME = "LoggingLogin";
	
	/**
	 * Enums of loggin status
	 */
	const LOGIN_STATUS_UNKNOWN = 0;
	const LOGIN_STATUS_SUCCESS = 1;
	const LOGIN_STATUS_FAILED = 2;
	const LOGIN_STATUS_FAILED_LOCKED = 3;
	const LOGIN_STATUS_LOGOUT = 4;
	
	/**
	 * Login EMail-Address
	 * @var string
	 */
	protected $loginEMailAddress = "";
	
	/**
	 * Name of the user
	 * @var string 
	 */
	protected $userName = "";
	
	/**
	 * Status of the Login
	 * @var int 
	 */
	protected $loginStatus = self::LOGIN_STATUS_UNKNOWN;
	
	/**
	 * Constructor
	 */
	public function LoggingLogin($loginEMailAddress, $userName, $loginStatus)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("loginEMailAddress", "userName", "loginStatus");
		$dbConfig->rowParam = Array("VARCHAR(255)", "VARCHAR(255)", "INT");
		$dbConfig->rowIndex = Array();
		parent::__construct($dbConfig);
		
		$this->loginEMailAddress = $loginEMailAddress;
		$this->userName = $userName;
		$this->loginStatus = $loginStatus;
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
		$rowName[] = "loginEMailAddress";
		$rowData[] = $this->loginEMailAddress;
		$rowName[] = "userName";
		$rowData[] = $this->userName;
		$rowName[] = "loginStatus";
		$rowData[] = $this->loginStatus;
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
		$this->loginEMailAddress = $data["loginEMailAddress"];
		$this->userName = $data["userName"];
		$this->loginStatus = (int)$data["loginStatus"];
		return true;
	}
	
	/**
	 * Return the email address
	 * @return string
	 */
	public function GetLoginEMailAddress() 
	{
		return $this->loginEMailAddress;
	}

	/**
	 * Return user name
	 * @return string
	 */
	public function GetUserName() 
	{
		return $this->userName;
	}

	/**
	 * Return the loggin status
	 * @return int
	 */
	public function GetLoginStatus() 
	{
		return $this->loginStatus;
	}
	
}
?>