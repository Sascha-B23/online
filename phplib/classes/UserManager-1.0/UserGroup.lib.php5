<?php
/**
 * Diese Klasse repräsentiert eine Benutzergruppe
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class UserGroup extends DBEntry 
{
	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "usergroup";

	/**
	 * Gruppenname
	 * @var string
	 */
	protected $name="";
	
	/**
	 * Gruppentyp
	 * @var int
	 */
	protected $baseType=UM_GROUP_BASETYPE_NONE;
	
	/**
	 * Count of users in this group
	 * @var int 
	 */
	protected $userCount = -1;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function UserGroup(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("name", "baseType");
		$dbConfig->rowParam = Array("VARCHAR(255)", "BIGINT");
		$dbConfig->rowIndex = Array();
		parent::__construct($db, $dbConfig);
	}
			
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @return bool Kann der Datensatz gelöscht werden (true/false)
	 */
	public function IsDeletable(&$db)
	{
		if ($this->GetUserCount($db)>0) return false;
		if (CustomerManager::IsUserGroupInUse($db, $this)) return false;
		return parent::IsDeletable($db);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1 Gruppenname nicht gesetzt
	 *								-2 Gruppe mit diesem Namen existiert bereits
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// EMail-Adresse gesetzt?
		if ($this->name=="") return -1;
		// Prüfen, ob neue Gruppe
		if ($this->pkey==-1)
		{
			// Prüfen ob Gruppe mit gleichem Namen bereits existiert
			if (count($db->SelectAssoc("SELECT pkey FROM ".self::TABLE_NAME." WHERE name='".$this->name."'", false))!=0)
			{
				// Gruppe mit disesem Namen existiert bereits
				return -2;
			}
		}
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "name";
		$rowData[]= $this->name;
		$rowName[]= "baseType";
		$rowData[]= $this->baseType;
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array $data Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->name = $data['name'];
		$this->baseType = $data['baseType'];
		return true;
	}
	
	/**
	 * Setzt den Gruppennamen
	 * @param string $name Name der Gruppe 
	 * @return bool
	 */
	public function SetName($name)
	{
		$this->name = $name;
		return true;
	}
	
	/**
	 * Gibt den Gruppennamen zurück
	 * @return string Name der Gruppe
	 */
	public function GetName()
	{
		return $this->name;
	}

	/**
	 * Setzt den Basistyp der Gruppe
	 * @return bool
	 */
	public function SetBaseType($baseType)
	{
		$this->baseType = $baseType;
		return true;
	}	
	
	/**
	 * Gibt den Basistyp der Gruppe zurück
	 * @return int
	 */
	public function GetBaseType()
	{
		return $this->baseType;
	}
	
	/**
	 * Gibt die Anzahl der Benutzer dieser Gruppe zurück
	 * @param DBManager $db
	 * @param bool $useCache Load data from local attribute
	 * @return int
	 */
	public function GetUserCount($db, $useCache=true)
	{
		if ($this->pkey==-1) return false;
		if ($this->userCount==-1 || !$useCache)
		{
			$groupUsers = $db->SelectAssoc("SELECT count(user) AS numUsers FROM ".User::TABLE_NAME_USERGROUP_REL." WHERE usergroup=".$this->pkey);
			$this->userCount = (isset($groupUsers[0]["numUsers"]) ? $groupUsers[0]["numUsers"] : 0);
		}
		return $this->userCount;
	}
	
	/**
	 * Gibt die Benutzer dieser Gruppe zurück
	 * @return User[]
	 */
	public function GetUsers($db)
	{
		if ($this->pkey==-1) return Array();
		$groupUsers = $db->SelectAssoc("SELECT user FROM ".User::TABLE_NAME_USERGROUP_REL." WHERE usergroup=".$this->pkey." GROUP BY user");
		$users = Array();
		for ($a=0; $a<count($groupUsers); $a++)
		{
			$user = new User($db);
			if ($user->Load((int)$groupUsers[$a]["user"], $db)===true) $users[] = $user;
		}
		return $users;
	}
	
}
?>