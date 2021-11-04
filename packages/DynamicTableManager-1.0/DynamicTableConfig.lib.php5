<?php
/**
 * Class to store user defined table configurations to database
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class DynamicTableConfig extends DBEntry
{
	
	/**
	 * Name of the db table
	 * @var string
	 */
	const TABLE_NAME = "dynamicTableConfig";
	
	/**
	 * User
	 * @var User
	 */
	protected $user = null;
	
	/**
	 * Id of the table
	 * @var string
	 */
	protected $tableId = "";
	
	/**
	 * Name of the config
	 * @var string
	 */
	protected $name = "";
	
	/**
	 * Default config of the user
	 * @var boolean
	 */
	protected $default = false;
	
	/**
	 * Config
	 * @var string
	 */
	protected $config = "";
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function DynamicTableConfig(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("user_rel", "tableId", "name", "defaultConfig", "config");
		$dbConfig->rowParam = Array("BIGINT", "VARCHAR(255)", "VARCHAR(255)", "INT", "LONGTEXT");
		$dbConfig->rowIndex = Array("user_rel");
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1 Name nicht gesetzt
	 *								-2 Kein User festgelegt
	 *								-3 Keine Table ID gesetzt
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if (trim($this->name)=="") return -1;
		if ($this->user==null) return -2;
		if (trim($this->tableId)=="") return -3;
		// if this entry is the default entry...
		if ($this->default)
		{
			// set all other entries of this user and table to 0
			$db->Update(self::TABLE_NAME, Array("defaultConfig"), Array(0), "user_rel=".$this->user->GetPKey()." AND tableId='".$this->tableId."'");
		}
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "user_rel";
		$rowData[]= $this->user->GetPKey();
		$rowName[]= "tableId";
		$rowData[]= $this->tableId;
		$rowName[]= "name";
		$rowData[]= $this->name;
		$rowName[]= "defaultConfig";
		$rowData[]= $this->default ? 1 : 0;
		$rowName[]= "config";
		$rowData[]= serialize($this->config);
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array $data
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$user = new User($db);
		if ($user->Load((int)$data['user_rel'], $db)===true)
		{
			$this->user = $user;
		}
		$this->name = $data['name'];
		$this->default = ($data['defaultConfig']==0 ? false : true);
		$this->tableId = $data['tableId'];
		$this->config = unserialize($data['config']);
		return true;
	}
	
	/**
	 * Return the User
	 * @return User
	 */
	public function GetUser()
	{
		return $this->user;
	}

	/**
	 * Set the User
	 * @param User $user
	 */
	public function SetUser(User $user)
	{
		$this->user = $user;
	}

	/**
	 * Get the id of the table
	 * @return string
	 */
	public function GetTableId()
	{
		return $this->tableId;
	}

	/**
	 * Set the id of the table
	 * @param string $tableId
	 */
	public function SetTableId($tableId)
	{
		$this->tableId = $tableId;
	}
	
	/**
	 * Get the name of the config
	 * @return string
	 */
	public function GetName()
	{
		return $this->name;
	}

	/**
	 * Set the name of the config
	 * @param string $name
	 */
	public function SetName($name)
	{
		$this->name = trim($name);
	}

	/**
	 * Return if the entry is the default entry
	 * @return boolean
	 */
	public function IsDefault()
	{
		return $this->default;
	}

	/**
	 * Set if the entry is the default entry
	 * @param DBManager $db
	 * @param boolean $default
	 */
	public function SetDefault($default)
	{
		if (!is_bool($default)) return false;
		$this->default = $default;
		return true;
	}
	
	/**
	 * Set the config
	 * @return Array
	 */
	public function GetConfig()
	{
		return $this->config;
	}

	/**
	 * Return the config
	 * @param Array $config
	 */
	public function SetConfig($config)
	{
		$this->config = $config;
	}

}
?>