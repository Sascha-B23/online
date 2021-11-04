<?php
/**
 * Verwaltungsklasse für Kunden-Daten
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class DynamicTableConfigManager extends SearchDBEntry 
{
		
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db=null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function DynamicTableConfigManager(DBManager $db)
	{
		$this->db = $db;
	}
	
	/**
	 * Load a configuration by id
	 * @param DBManager $db
	 * @param User $user
	 * @param DynamicTable $table
	 * @param int $id
	 * @return DynamicTableConfig
	 */
	static public function LoadDynamicTableConfigById(DBManager $db, User $user, DynamicTable $table, $id)
	{
		$object = new DynamicTableConfig($db);
		if ($object->Load((int)$id, $db)===true)
		{
			// check user...
			if ($object->GetUser()!=null && $object->GetUser()->GetPKey()==$user->GetPKey())
			{
				// check tabele
				if ($object->GetTableId()==$table->GetId())
				{
					return $table->LoadSettings($object->GetConfig());
				}
			}
		}
		return false;
	}
	
	/**
	 * Load a configuration by id
	 * @param DBManager $db
	 * @param User $user
	 * @param DynamicTable $table
	 * @param int $id
	 * @return DynamicTableConfig
	 */
	static public function LoadDefaultDynamicTableConfig(DBManager $db, User $user, DynamicTable $table)
	{
		$object = new DynamicTableConfig($db);
		$data = $db->SelectAssoc("SELECT * FROM ".DynamicTableConfig::TABLE_NAME." WHERE user_rel=".$user->GetPKey()." AND tableId='".$table->GetId()."' AND defaultConfig=1");
		if (count($data)>0)
		{
			if ($object->LoadFromArray($data[0], $db)===true)
			{
				return $table->LoadSettings($object->GetConfig());
			}
		}
		return false;
	}
	
	/**
	 * Store the current table configuration of the passed table to db
	 * @param DBManager $db
	 * @param User $user
	 * @param DynamicTable $table
	 * @param string $configName
	 * @param boolean $defaultConfig
	 * @param int $configId
	 * @return DynamicTableConfig
	 */
	static public function StoreDynamicTableConfig(DBManager $db, User $user, DynamicTable $table, $configName="", $defaultConfig=false, $configId=-1)
	{
		$object = null;
		// config to load?
		if ($configId!=-1)
		{
			$object = new DynamicTableConfig($db);
			if ($object->Load((int)$configId, $db)===true)
			{
				if (!($object->GetUser()!=null && $object->GetUser()->GetPKey()==$user->GetPKey() && $object->GetTableId()==$table->GetId()))
				{
					// user and/or table in the loaded config doesn't match with the passed User / DynamicTable objects
					$object = null;
				}
			}
			else
			{
				// can't load config
				$object = null;
			}
		}
		// if no config was loaded, create new config object
		if ($object==null) $object = new DynamicTableConfig($db);
		// set configuration
		$object->SetUser($user);
		$object->SetTableId($table->GetId());
		$object->SetConfig($table->StoreSettings());
		$object->SetDefault($defaultConfig);
		if ($configName!="") $object->SetName($configName);
		// store config to db
		$returnValue = $object->Store($db);
		if ($returnValue===true) return $object;
		return null;
	}
	
	/**
	 * Get the total number of configurations
	 * @param User $user
	 * @param DynamicTable $table
	 * @param string $searchString
	 * @return int
	 */
	public function GetDynamicTableConfigCount(User $user, DynamicTable $table, $searchString="")
	{
		$object = new DynamicTableConfig($this->db);
		return $this->GetDBEntryCount($searchString, DynamicTableConfig::TABLE_NAME, $object->GetTableConfig()->rowName, "user_rel=".$user->GetPKey()." AND tableId='".$table->GetId()."'");
	}
	
	/**
	 * Get the configurations
	 * @param User $user
	 * @param DynamicTable $table
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @return DynamicTableConfig[]
	 */
	public function GetDynamicTableConfigs(User $user, DynamicTable $table, $searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20)
	{
		$object = new DynamicTableConfig($this->db);
		$dbEntrys=$this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, DynamicTableConfig::TABLE_NAME, $object->GetTableConfig()->rowName, "user_rel=".$user->GetPKey()." AND tableId='".$table->GetId()."'");
		// Objekte erzeugen
		$objects=Array();
		foreach ($dbEntrys as $data)
		{
			$object = new DynamicTableConfig($this->db);
			if ($object->LoadFromArray($data, $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
}
?>