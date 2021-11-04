<?php
require_once("UserManager.inc.php5");
require_once("UserGroup.lib.php5");
require_once("User.lib.php5");

/**
 * Verwaltungsklasse für Benutzer und Gruppen
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class UserManager extends SearchDBEntry 
{

	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db=null;
	
	/**
	 * Dummyobjekte 
	 */
	protected $groupDummy=null;
	protected $userDummy=null;
	protected $addressDummy=null;
	
	/**
	 * Array map with all User instances loaded with fn. GetUserByPkey
	 * @var Array
	 */
	protected static $userCache = Array();
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function UserManager(DBManager $db, $checkSuperUserLogin=false)
	{
		$this->db=$db;
		$this->groupDummy=new UserGroup($this->db);
		$this->userDummy=new User($this->db);
		$this->addressDummy=new AddressData($this->db);
		// Superuser-Gruppe und Support-User vorhanden?
		if ($checkSuperUserLogin)
		{
			// Superuser-Gruppe vorhanden?
			$group=$this->GetGroupByName("Superuser");
			if ($group==null)
			{
				// Superuser-Gruppe anlegen
				$this->db->Insert(UserGroup::TABLE_NAME, Array("creationTime", "lastChangeTime", "undeletable", "name", "baseType"), Array(time(), time(), 1, "Superuser", UM_GROUP_BASETYPE_SUPERUSER) );		
				$group=$this->GetGroupByName("Superuser");
			}
			// Super-User vorhanden?
			$user=$this->GetUserByLogin("support@stollvongati.com");
			if ($user==null)
			{
				// Super-User anlegen
				$this->db->Insert(User::TABLE_NAME, Array("creationTime", "lastChangeTime", "undeletable", "email", "passwort", "wrongPasswordCounter", "lastLoginTime", "addressData"), Array(time(), time(), 1, "support@stollvongati.com", "6253e1406b64bbe6ba7b00ac0bf81257", 0, 0, -1) );
				$user=$this->GetUserByLogin("support@stollvongati.com");
				if ($user!=null && $group!=null)
				{
					// User zur Superuser-Gruppe hinzufügen
					$user->AddToGroups($this->db, Array($group));
				}
			}
		}
	}
	
	/**
	 * Startet den Loginvorgang
	 * @param string 	$email			Emailadresse des Users
	 * @param string 	$pw				Passwort des Users
	 * @return object/int				User-Object oder
	 *									-1	Login fehlgeschlagen
	 *									-2	Maximale Anzahl der Loginversuche wurde überschritten
	 */
	final public function Login($email, $pw)
	{
		$user = new User($this->db);
		$retValue = $user->Login($email, $pw, $this->db);
		LoggingManager::GetInstance()->Log(new LoggingLogin($email, $retValue!==true ? "" : $user->GetUserName(), $retValue===true ? LoggingLogin::LOGIN_STATUS_SUCCESS : ($retValue==-2 ? LoggingLogin::LOGIN_STATUS_FAILED_LOCKED : LoggingLogin::LOGIN_STATUS_FAILED)));
		if ($retValue===true) return $user; 	//Login erfolgreich
		return $retValue;						//Login fehlgeschlagen
	}

	/**
	 * Gibt ein Array mit allen Gruppen des Systems zurück
	 * @return UserGroup[]
	 */
	public function GetAllGroups()
	{
		$groupIDs = $this->db->SelectAssoc("SELECT pkey FROM ".UserGroup::TABLE_NAME);
		$groups=Array();
		for ($a=0; $a<count($groupIDs); $a++)
		{
			$group = new UserGroup($this->db);
			if ($group->Load((int)$groupIDs[$a]["pkey"], $this->db)===true) $groups[] = $group;
		}
		return $groups;
	}
	
	/**
	 * Gibt ein Array mit allen Gruppen des Systems zurück
	 * @param string $groupName Name der Gruppe die gesucht wird
	 * @return UserGroup
	 */
	public function GetGroupByName($groupName)
	{
		$group=new UserGroup($this->db);
		$groupIDs = $this->db->SelectAssoc("SELECT pkey FROM ".UserGroup::TABLE_NAME." WHERE name='".$groupName."'");
		if (count($groupIDs)!=1) return null;
		if ($group->Load((int)$groupIDs[0]["pkey"], $this->db)!==true) return null;
		return $group;
	}
	
	
	/**
	 * Gibt ein Array mit allen Benutzern des Systems zurück
	 * @return User[]
	 */
	public function GetAllUsers()
	{
		$userIDs = $this->db->SelectAssoc("SELECT pkey FROM ".User::TABLE_NAME);
		$users = Array();
		for ($a=0; $a<count($userIDs); $a++)
		{
			$user = new User($this->db);
			if ($user->Load((int)$userIDs[$a]["pkey"], $this->db)===true) $users[] = $user;
		}
		return $users;
	}
	
	/**
	 * Gibt ein Array mit allen Gruppen des Systems zurück
	 * @param string $loginName Login-Name des gesuchten Users 
	 * @return User
	 */
	public function GetUserByLogin($loginName)
	{
		$user = new User($this->db);
		if (USER::CheckFormatEMail(trim($loginName))!==true) return null;
		$userIDs = $this->db->SelectAssoc("SELECT pkey FROM ".User::TABLE_NAME." WHERE email='".trim($loginName)."'");
		if (count($userIDs)!=1) return null;
		if ($user->Load((int)$userIDs[0]["pkey"], $this->db)!==true) return null;
		return $user;
	}
	
	/**
	 * Gibt den Benutzer mit dem übergebenen Vor- und Nachnamen zurück
	 * @param string $loginName Login-Name des gesuchten Users
	 * @return User
	 */
	public function GetUserByName($firstname, $name)
	{
		$addressDataID = $this->db->SelectAssoc("SELECT email FROM ".AddressData::TABLE_NAME." WHERE name='".trim($name)."' AND firstname='".trim($firstname)."'");
		if (count($addressDataID)!=1) return null;
		return $this->GetUserByLogin($addressDataID[0]["email"]);
	}
	
	/**
	 * Gibt die Anzahl der Gruppen zurück
	 * @return int
	 */
	public function GetGroupCount($searchString="", $maxGroupBasetype=UM_GROUP_BASETYPE_NONE)
	{
		$group=new UserGroup($this->db);
		return $this->GetDBEntryCount($searchString, UserGroup::TABLE_NAME, $group->GetTableConfig()->rowName, "baseType<=".(int)$maxGroupBasetype);
	}
	
	/**
	 * Gibt die Gruppen entsprechend den übergebenen Parametern zurück
	 * @param string $searchString Suchstring
	 * @param string $orderBy Spaltenname nach dem sortiert werden soll
	 * @param string $orderDirection Sortierrichtung (ASC oder DESC)
	 * @param string $currentPage Aktuelle Seite
	 * @param string $numEntrysPerPage Anzhal der Einträge je Seite
	 * @return UserGroup[]
	 */
	public function GetGroups($searchString="", $maxGroupBasetype=UM_GROUP_BASETYPE_NONE, $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=0)
	{
		$object = new UserGroup($this->db);
		$entryData = $this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, UserGroup::TABLE_NAME, $object->GetTableConfig()->rowName, "baseType<=".(int)$maxGroupBasetype);
		// Objekte erzeugen
		$objects = Array();
		foreach ($entryData as $data)
		{
			$object = new UserGroup($this->db);
			if ($object->LoadFromArray($data, $this->db)===true) $objects[] = $object;
		}
		return $objects;
	}
	
	/**
	 * Gibt die Anzahl der Benutzer zurück
	 * @param User $user
	 * @param string $searchString
	 * @return int
	 */
	public function GetUserCount(User $user, $searchString="")
	{
		// Berechtigung 
		$groupQuery="";
		if ($user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			$groupIDs=$user->GetGroupIDs($this->db);
			for ($a=0; $a<count($groupIDs); $a++)
			{
				if ($groupQuery!="") $groupQuery.=" OR ";
				$groupQuery.=" ".UserGroup::TABLE_NAME.".pkey=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		
		$joinClause="LEFT JOIN ".User::TABLE_NAME_USERGROUP_REL." ON ".User::TABLE_NAME_USERGROUP_REL.".user=".User::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".UserGroup::TABLE_NAME." ON ".User::TABLE_NAME_USERGROUP_REL.".usergroup=".UserGroup::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".AddressData::TABLE_NAME." ON ".User::TABLE_NAME.".addressData =".AddressData::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => User::TABLE_NAME, "tableRowNames" => $this->userDummy->GetTableConfig()->rowName),
							Array("tableName" => UserGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
							Array("tableName" => AddressData::TABLE_NAME, "tableRowNames" => $this->addressDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		
		$user = new User($this->db);
		return $this->GetDBEntryCount($searchString, User::TABLE_NAME, $allRowNames, $groupQuery, $joinClause, User::TABLE_NAME.".pkey");
	}
	
	/**
	 * Gibt die Users entsprechend den übergebenen Parametern zurück
	 * @param User $user
	 * @param string $searchString Suchstring
	 * @param string $orderBy Spaltenname nach dem sortiert werden soll
	 * @param string $orderDirection Sortierrichtung (ASC oder DESC)
	 * @param string $currentPage Aktuelle Seite
	 * @param string $numEntrysPerPage Anzhal der Einträge je Seite
	 * @param int $type
	 * @return User[]
	 */
	public function GetUsers(User $user, $searchString="", $orderBy="email", $orderDirection=0, $currentPage=0, $numEntrysPerPage=200, $type=UM_GROUP_BASETYPE_NONE)
	{
		// Berechtigung 
		$groupQuery="";
		if ($user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			$groupIDs=$user->GetGroupIDs($this->db);
			for ($a=0; $a<count($groupIDs); $a++)
			{
				if ($groupQuery!="") $groupQuery.=" OR ";
				$groupQuery.=" ".UserGroup::TABLE_NAME.".pkey=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
	
		$joinClause="LEFT JOIN ".User::TABLE_NAME_USERGROUP_REL." ON ".User::TABLE_NAME_USERGROUP_REL.".user=".User::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".UserGroup::TABLE_NAME." ON ".User::TABLE_NAME_USERGROUP_REL.".usergroup=".UserGroup::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".AddressData::TABLE_NAME." ON ".User::TABLE_NAME.".addressData =".AddressData::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => User::TABLE_NAME, "tableRowNames" => $this->userDummy->GetTableConfig()->rowName),
							Array("tableName" => UserGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
							Array("tableName" => AddressData::TABLE_NAME, "tableRowNames" => $this->addressDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		$whereClause="";
		if ($type!=UM_GROUP_BASETYPE_NONE)
		{
			$whereClause=UserGroup::TABLE_NAME.".baseType=".$type;
		}
		if (trim($whereClause)!="")
		{
			if( trim($groupQuery)!="" )$groupQuery=$groupQuery." AND (".$whereClause.") ";
			else $groupQuery=$whereClause;
		}
		$entryData = $this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, User::TABLE_NAME, $allRowNames, $groupQuery, $joinClause, User::TABLE_NAME.".email ");
		// Objekte erzeugen
		$objects = Array();
		foreach ($entryData as $data)
		{
			$object = new User($this->db);
			if ($object->LoadFromArray($data, $this->db)===true) $objects[] = $object;
		}
		return $objects;
	}
	
	/**
	 * Return User instance by pkey
	 * @param DBManager $db
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return User
	 */
	static public function GetUserByPkey(DBManager $db, $pkey, $useCache=true)
	{
		// look up cache
		if ($useCache && isset(self::$userCache[(int)$pkey])) return self::$userCache[(int)$pkey];
		// load from DB
		$user = new User($db);
		if ($user->Load((int)$pkey, $db)!==true) $user = null;
		if ($useCache) self::$userCache[(int)$pkey] = $user;
		return $user;
	}
	
	/**
	 * Return if the passed user ist allowed to execute the specified action
	 * @param DBManager $db
	 * @param User $user
	 * @param int $actionId
	 * @return boolean
	 */
	static public function IsUserAllowedTo(DBManager $db, User $user, $actionId)
	{
		global $UM_ACTIONS;
		$groupBasetype = $user->GetGroupBasetype($db);
		if ($groupBasetype==UM_GROUP_BASETYPE_NONE) return false;
		foreach ($UM_ACTIONS as $action) 
		{
			if ($action['ID']!=$actionId) continue;
			if ($groupBasetype>=$action['minGroupBasetype'])
			{
				if (!isset($action['excludeGroupBasetypes']) || !in_array($groupBasetype, $action['excludeGroupBasetypes']))
				{
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Return if the current loged in user ist allowed to execute the specified action
	 * @param DBManager $db
	 * @param int $actionId
	 * @return boolean
	 */
	static public function IsCurrentUserAllowedTo(DBManager $db, $actionId)
	{
		return self::IsUserAllowedTo($db, $_SESSION["currentUser"], $actionId);
	}
	
}
?>