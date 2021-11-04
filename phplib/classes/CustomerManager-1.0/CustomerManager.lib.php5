<?php
include('CGroup.lib.php5');
include('CCompany.lib.php5');
include('CLocation.lib.php5');
include('CShop.lib.php5');
include('CCountry.lib.php5');
include('CCurrency.lib.php5');
include('CLanguage.lib.php5');

/**
 * Verwaltungsklasse für Kunden-Daten
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class CustomerManager extends SearchDBEntry 
{
		
	/**
	 * Datenbankobjekt
	 * @var MySQLManager
	 */
	protected $db=null;

	/**
	 * Dummyobjekte 
	 */
	protected $groupDummy=null;
	protected $companyDummy=null;
	protected $locationDummy=null;
	protected $shopDummy=null;
	
	/**
	 * Array map with all group instances loaded with fn. GetGroupByPkey
	 * @var Array
	 */
	protected static $groupCache = Array();
	
	/**
	 * Array map with all company instances loaded with fn. GetCompanyByPkey
	 * @var Array
	 */
	protected static $companyCache = Array();
	
	/**
	 * Array map with all location instances loaded with fn. GetLocationByPkey
	 * @var Array
	 */
	protected static $locationCache = Array();
	
	/**
	 * Array map with all shop instances loaded with fn. GetShopByPkey
	 * @var Array
	 */
	protected static $shopCache = Array();
	
	/**
	 * Konstruktor
	 * @param MySQLManager $db
	 */
	public function CustomerManager($db)
	{
		$this->db=$db;
		$this->groupDummy=new CGroup($this->db);
		$this->companyDummy=new CCompany($this->db);
		$this->locationDummy=new CLocation($this->db);
		$this->shopDummy=new CShop($this->db);
	}
	
	/**
	 * Gibt zurück, ob die übergebene Benutzergruppe in einer CustomerGruppe referenziert ist
	 * @param UserGroup $userGroup
	 * @return bool
	 */
	static public function IsUserGroupInUse($db, $userGroup)
	{
		if( $userGroup==null || get_class($userGroup)!="UserGroup" )return false;
		$data=$db->SelectAssoc( "SELECT count(pkey) AS numGroups FROM ".CGroup::TABLE_NAME." WHERE userGroup=".$userGroup->GetPKey() );
		return (int)$data[0]["numGroups"]>0 ? true : false;
	}
	
	/**
	 * Gibt die Anzahl der Kunden-Gruppen auf die der übergebene Benutzer zugriff hat zurück
	 * @param User $user
	 * @return boolean|int
	 */
	/*public function GetCGroupCount($user)
	{
		if( $user==null || get_class($user)!="User" )return false;
		$query="SELECT count(pkey) AS numGroups FROM ".CGroup::TABLE_NAME;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupQuery="";
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			if( trim($groupQuery)!="" )$query.=" WHERE ".$groupQuery;
		}
		$data=$this->db->SelectAssoc($query);
		return (int)$data[0]["numGroups"];
	}*/
	
	/**
	 * Gibt alle Kunden-Gruppen auf die der übergebene Benutzer zugriff hat zurück
	 * @param User $user
	 * @return boolean/CGroup[]
	 */
	/*public function GetCGroups($user)
	{
		if( $user==null || get_class($user)!="User" )return false;
		$query="SELECT pkey FROM ".CGroup::TABLE_NAME;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupQuery="";
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			if( trim($groupQuery)!="" )$query.=" WHERE ".$groupQuery;
		}
		$dbEntrys=$this->db->SelectAssoc($query);
		// Objekte erzeugen
		$groups=Array();
		for( $a=0; $a<count($dbEntrys); $a++){
			$group=new CGroup($this->db);
			if( $group->Load($dbEntrys[$a]["pkey"], $this->db) )$groups[]=$group;
		}
		return $groups;
	}*/
	
	/**
	 * Gibt die Anzahl der Gruppen zurück
	 * @param User $user
	 * @return int
	 */
	public function GetGroupCount($user, $searchString="")
	{
		if( $user==null || get_class($user)!="User" )return 0;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$groupQuery="";
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupQuery="";
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		$object=new CGroup($this->db);
		return $this->GetDBEntryCount($searchString, CGroup::TABLE_NAME, $object->GetTableConfig()->rowName, $groupQuery);
	}
	
	/**
	 * Gibt die Gruppen zurück
	 * @param User $user
	 * @return CGroup[]
	 */
	public function GetGroups(User $user, $searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20, $ignoreGroupRights=false)
	{
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$groupQuery="";
		if (!$ignoreGroupRights && $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER)
		{
			$groupQuery = "";
			$groupIDs = $user->GetGroupIDs($this->db);
			for ($a=0; $a<count($groupIDs); $a++)
			{
				if ($groupQuery!="") $groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		$object = new CGroup($this->db);
		$data = $this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, CGroup::TABLE_NAME, $object->GetTableConfig()->rowName, $groupQuery);
		// Objekte erzeugen
		$objects=Array();
		for( $a=0; $a<count($data); $a++)
		{
			$object = new CGroup($this->db);
			if ($object->LoadFromArray($data[$a], $this->db)===true) $objects[] = $object;
		}
		return $objects;
	}
	
	/**
	 * Gibt die Gruppen mit der angeforderten ID zurück
	 * @param User $user
	 * @param int $groupID
	 * @param boolean $ignoreGroupRights
	 * @return CGroup 
	 */
	public function GetGroupByID($user, $groupID, $ignoreGroupRights=false)
	{
		if( $user==null || get_class($user)!="User" || !is_numeric($groupID) || ((int)$groupID)!=$groupID )return null;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$object=new CGroup($this->db);
		if( !$object->Load((int)$groupID, $this->db) )return null;
		// Zugriffsberechtigung prüfen
		if( !$ignoreGroupRights && $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupIDs[$a]==$object->GetUserGroup()->GetPKey() )return $object;
			}
			return null;
		}
		return $object;
	}
		
	/**
	 * Gibt die Gruppen mit dem angeforderten Namen zurück
	 * @param User $currentUser
	 * @param int $groupName
	 * @return CGroup
	 */
	public function GetGroupByName($currentUser, $groupName)
	{
		if( $currentUser==null || get_class($currentUser)!="User" || trim($groupName)=="" )return null;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$groupQuery="";
		if( $currentUser->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupQuery="";
			$groupIDs=$currentUser->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.") AND ";
		}
		$groupQuery.=" name='".trim($groupName)."'";
		$object=new CGroup($this->db);
		$dbEntrys = $this->GetDBEntry("", "name", 0, 0, 20, CGroup::TABLE_NAME, $object->GetTableConfig()->rowName, $groupQuery);
		if (count($dbEntrys)!=1) return null;
		if ($object->Load($dbEntrys[0], $this->db)!==true) return null;
		return $object;
	}
	
	/**
	 * Gibt die Anzahl der Firmen zurück
	 * @param User $user
	 * @param string $searchString
	 * @return int
	 */
	public function GetCompanyCount($user, $searchString="")
	{
		if( $user==null || get_class($user)!="User" )return 0;
		// Berechtigung 
		$groupQuery="";
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		
		$joinClause="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		
		$object=new CCompany($this->db);
		return $this->GetDBEntryCount($searchString, CCompany::TABLE_NAME, $allRowNames, $groupQuery, $joinClause);
	}
	
	/**
	 * Gibt Firmen zurück
	 * @param User $user
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @return CCompany[]
	 */
	public function GetCompanys($user, $searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20)
	{
		if( $user==null || get_class($user)!="User" )return Array();
		// Berechtigung 
		$groupQuery="";
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		
		$joinClause="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		
		$object=new CCompany($this->db);
		$dbEntrys=$this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, CCompany::TABLE_NAME, $allRowNames, $groupQuery, $joinClause);
		// Objekte erzeugen
		$objects=Array();
		for ($a=0; $a<count($dbEntrys); $a++)
		{
			$object=new CCompany($this->db);
			if ($object->LoadFromArray($dbEntrys[$a], $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Gibt die Gruppen mit der angeforderten ID zurück
	 * @param User $user
	 * @param int $companyID
	 * @param boolean $ignoreGroupRights
	 * @return
	 */
	public function GetCompanyByID($user, $companyID, $ignoreGroupRights=false)
	{
		if( $user==null || get_class($user)!="User" || !is_numeric($companyID) || ((int)$companyID)!=$companyID )return null;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$object=new CCompany($this->db);
		if( !$object->Load((int)$companyID, $this->db) )return null;
		// Zugriffsberechtigung prüfen
		if( !$ignoreGroupRights && $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupIDs=$user->GetGroupIDs($this->db);
			$group=$object->GetGroup();
			if( $group!=null ){
				for( $a=0; $a<count($groupIDs); $a++){
					if( $groupIDs[$a]==$group->GetUserGroup()->GetPKey() )return $object;
				}
			}
			return null;
		}
		return $object;
	}
	
	/**
	 * Gibt die Firma mit dem angeforderten Namen zurück
	 * @param User $currentUser
	 * @param string $companyName
	 * @return CCompany[]
	 */
	public function GetCompanysByName($currentUser, $companyName)
	{
		if ($currentUser==null || get_class($currentUser)!="User" || trim($companyName)=="") return Array();
		$object=new CCompany($this->db);
		$dbEntrys=$this->GetDBEntry("", "name", 0, 0, 20, CCompany::TABLE_NAME, $object->GetTableConfig()->rowName, " name='".trim($companyName)."' ");
		// Objekte erzeugen
		$objects=Array();
		for( $a=0; $a<count($dbEntrys); $a++)
		{
			$object=new CCompany($this->db);
			if ($object->Load($dbEntrys[$a], $this->db)===true)
			{
				// TODO: Evtl. prüfen, ob Benutzer zu dieser Gruppe gehört
				$objects[]=$object;
			}
		}
		return $objects;
	}
	
	/**
	 * Gibt die Anzahl der Standort zurück
	 * @param User $currentUser
	 * @param string $searchString
	 * @return int
	 */
	public function GetLocationCount($user, $searchString="")
	{
		if( $user==null || get_class($user)!="User" )return 0;
		// Berechtigung 
		$groupQuery="";
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		
		$joinClause="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
							Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		
		$object=new CLocation($this->db);
		return $this->GetDBEntryCount($searchString, CLocation::TABLE_NAME, $allRowNames, $groupQuery, $joinClause);
	}
	
	/**
	 * Gibt die Standorte zurück
	 * @param User $user
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @return CLocation[]
	 */
	public function GetLocations($user, $searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20)
	{
		if( $user==null || get_class($user)!="User" )return Array();
		// Berechtigung 
		$groupQuery="";
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		
		$joinClause="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
							Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
	
		$object=new CLocation($this->db);
		$dbEntrys=$this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, CLocation::TABLE_NAME, $allRowNames, $groupQuery, $joinClause);
		// Objekte erzeugen
		$objects=Array();
		for( $a=0; $a<count($dbEntrys); $a++)
		{
			$object=new CLocation($this->db);
			if ($object->LoadFromArray($dbEntrys[$a], $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Gibt die Gruppen mit der angeforderten ID zurück
	 * @param User $user
	 * @param int $locationID
	 * @param boolean $ignoreGroupRights
	 * @return CLocation
	 */
	public function GetLocationByID($user, $locationID, $ignoreGroupRights=false)
	{
		if( $user==null || get_class($user)!="User" || !is_numeric($locationID) || ((int)$locationID)!=$locationID )return null;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$object=new CLocation($this->db);
		if( !$object->Load((int)$locationID, $this->db) )return null;
		// Zugriffsberechtigung prüfen
		if( !$ignoreGroupRights && $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupIDs=$user->GetGroupIDs($this->db);
			$company=$object->GetCompany();
			if($company!=null)$group=$company->GetGroup();
			if( $group!=null ){
				for( $a=0; $a<count($groupIDs); $a++){
					if( $groupIDs[$a]==$group->GetUserGroup()->GetPKey() )return $object;
				}
			}
			return null;
		}
		return $object;
	}
	
	/**
	 * Gibt die Anzahl der Läden zurück
	 * @param User $user
	 * @param string $searchString
	 * @param string $additionWhereClause
	 * @return int
	 */
	public function GetShopCount($user, $searchString="", $additionWhereClause = "")
	{
		if( $user==null || get_class($user)!="User" )return 0;
		// Berechtigung 
		$groupQuery="";
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		if ($additionWhereClause!="")
		{
			if ($groupQuery!="") $groupQuery.=" AND ";
			$groupQuery.="(".$additionWhereClause.")";
		}
		
		$joinClause="LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
							Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		
		$object=new CShop($this->db);
		return $this->GetDBEntryCount($searchString, CShop::TABLE_NAME, $allRowNames, $groupQuery, $joinClause);
	}
	
	/**
	 * Gibt die Läden zurück
	 * @param User $user
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @param string $additionWhereClause
	 * @return CShop[]
	 */
	public function GetShops($user, $searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20, $additionWhereClause = "")
	{
		if( $user==null || get_class($user)!="User" )return Array();
		// Berechtigung 
		$groupQuery="";
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		if ($additionWhereClause!="")
		{
			if ($groupQuery!="") $groupQuery.=" AND ";
			$groupQuery.="(".$additionWhereClause.")";
		}
		$joinClause="LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
							Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		
		$object=new CShop($this->db);
		$dbEntrys=$this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, CShop::TABLE_NAME, $allRowNames, $groupQuery, $joinClause);
		// Objekte erzeugen
		$objects=Array();
		for ($a=0; $a<count($dbEntrys); $a++)
		{
			$object=new CShop($this->db);
			if ($object->LoadFromArray($dbEntrys[$a], $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	public function GetAllShops(User $user)
	{
		// Berechtigung 
		$groupQuery="";
		if ($user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER)
		{
			$groupIDs = $user->GetGroupIDs($this->db);
			for ($a=0; $a<count($groupIDs); $a++)
			{
				if ($groupQuery!="") $groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery=" WHERE (".$groupQuery.") ";
		}
	
		$joinClause="LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$data = $this->db->SelectAssoc("SELECT ".CShop::TABLE_NAME.".pkey, ".CShop::TABLE_NAME.".name FROM ".CShop::TABLE_NAME." ".$joinClause." ".$groupQuery." ORDER BY ".CShop::TABLE_NAME.".name");
		
		return $data;
	}
	
	/**
	 * Gibt die Gruppen mit der angeforderten ID zurück
	 * @param User $user
	 * @param int $shopID
	 * @param boolean $ignoreGroupRights
	 * @return CShop
	 */
	public function GetShopByID($user, $shopID, $ignoreGroupRights=false)
	{
		if( $user==null || get_class($user)!="User" || !is_numeric($shopID) || ((int)$shopID)!=$shopID )return null;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$object=new CShop($this->db);
		if( !$object->Load((int)$shopID, $this->db) )return null;
		// Zugriffsberechtigung prüfen
		if( !$ignoreGroupRights && $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupIDs=$user->GetGroupIDs($this->db);
			$location=$object->GetLocation();
			if($location!=null)$company=$location->GetCompany();
			if($company!=null)$group=$company->GetGroup();
			if( $group!=null ){
				for( $a=0; $a<count($groupIDs); $a++){
					if( $groupIDs[$a]==$group->GetUserGroup()->GetPKey() )return $object;
				}
			}
			return null;
		}
		return $object;
	}
		
	/**
	 * Gibt den Laden mit der angeforderten RSID zurück
	 * @param User $user
	 * @param int $RSID
	 * @return CShop
	 */
	public function GetShopByRSID($user, $RSID)
	{
		if( $user==null || get_class($user)!="User" /*|| !is_numeric($RSID) || ((int)$RSID)!=$RSID*/)return null; 
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$object=new CShop($this->db);
		$data=$this->db->SelectAssoc("SELECT pkey FROM ".CShop::TABLE_NAME." WHERE RSID='".$RSID."'" );
		if( count($data)!=1 )return null;
		if( !$object->Load((int)$data[0]["pkey"], $this->db) )return null;
		// Zugriffsberechtigung prüfen
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER ){
			$groupIDs=$user->GetGroupIDs($this->db);
			$location=$object->GetLocation();
			if($location!=null)$company=$location->GetCompany();
			if($company!=null)$group=$company->GetGroup();
			if( $group!=null ){
				for( $a=0; $a<count($groupIDs); $a++){
					if( $groupIDs[$a]==$group->GetUserGroup()->GetPKey() )return $object;
				}
			}
			return null;
		}
		return $object;
	}
	
	/**
	 * Gibt die Anzahl der Länder zurück
	 * @param string $searchString
	 * @return int
	 */
	public function GetCountryCount($searchString="")
	{
		$object=new CCountry($this->db);
		return $this->GetDBEntryCount($searchString, CCountry::TABLE_NAME, $object->GetTableConfig()->rowName);
	}
	
	/**
	 * Gibt die Länder zurück
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @return CCountry[]
	 */
	public function GetCountries($searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=0)
	{
		$object=new CCountry($this->db);
		$dbEntrys=$this->GetDBEntry($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, CCountry::TABLE_NAME, $object->GetTableConfig()->rowName);
		// Objekte erzeugen
		$objects=Array();
		for( $a=0; $a<count($dbEntrys); $a++)
		{
			$object=new CCountry($this->db);
			if ($object->Load($dbEntrys[$a], $this->db)===true) $objects[] = $object;
		}
		return $objects;
	}
	
	/**
	 * Gibt die Anzahl der Währungen zurück
	 * @param string $searchString
	 * @return int
	 */
	public function GetCurrencyCount($searchString="")
	{
		$object=new CCurrency($this->db);
		return $this->GetDBEntryCount($searchString, CCurrency::TABLE_NAME, $object->GetTableConfig()->rowName);
	}
	
	/**
	 * Gibt die Währungen zurück
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @return CCurrency[]
	 */
	public function GetCurrencies($searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=0)
	{
		$object=new CCurrency($this->db);
		$dbEntrys=$this->GetDBEntry($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, CCurrency::TABLE_NAME, $object->GetTableConfig()->rowName);
		// Objekte erzeugen
		$objects=Array();
		for( $a=0; $a<count($dbEntrys); $a++)
		{
			$object=new CCurrency($this->db);
			if ($object->Load($dbEntrys[$a], $this->db)===true) $objects[] = $object;
		}
		return $objects;
	}
	
	/**
	 * Gibt die Anzahl der Sprachen zurück
	 * @param string $searchString
	 * @return int
	 */
	public function GetLanguageCount($searchString="")
	{
		$object=new CLanguage($this->db);
		return $this->GetDBEntryCount($searchString, CLanguage::TABLE_NAME, $object->GetTableConfig()->rowName);
	}
	
	/**
	 * Gibt die Sprachen zurück
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @return CLanguage[]
	 */
	public function GetLanguages($searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=0)
	{
		$object=new CLanguage($this->db);
		$dbEntrys=$this->GetDBEntry($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, CLanguage::TABLE_NAME, $object->GetTableConfig()->rowName);
		// Objekte erzeugen
		$objects=Array();
		for( $a=0; $a<count($dbEntrys); $a++)
		{
			$object=new CLanguage($this->db);
			if ($object->Load($dbEntrys[$a], $this->db)===true) $objects[] = $object;
		}
		return $objects;
	}
	
	/**
	 * Return all supported languages 
	 * @return string[]
	 */
	static public function GetLanguagesISO639List(DBManager $db)
	{
		$object = new CLanguage($db);
		$data = $db->SelectAssoc('SELECT iso639 FROM '.CLanguage::TABLE_NAME);
		$returnValue = Array();
		foreach ($data as $value) 
		{
			$returnValue[] = $value['iso639'];
		}
		return $returnValue;
	}
	
	/**
	 * Return CGroup instance by pkey
	 * @param DBManager $db
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return CGroup
	 */
	static public function GetGroupByPkey(DBManager $db, $pkey, $useCache=true)
	{
		// look up cache
		if ($useCache && isset(self::$groupCache[(int)$pkey])) return self::$groupCache[(int)$pkey];
		// load from DB
		$group = new CGroup($db);
		if ($group->Load((int)$pkey, $db)!==true) $group = null;
		if ($useCache) self::$groupCache[(int)$pkey] = $group;
		return $group;
	}
	
	/**
	 * Return CCompany instance by pkey
	 * @param DBManager $db
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return CCompany
	 */
	static public function GetCompanyByPkey(DBManager $db, $pkey, $useCache=true)
	{
		// look up cache
		if ($useCache && isset(self::$companyCache[(int)$pkey])) return self::$companyCache[(int)$pkey];
		// load from DB
		$company = new CCompany($db);
		if ($company->Load((int)$pkey, $db)!==true) $company = null;
		if ($useCache) self::$companyCache[(int)$pkey] = $company;
		return $company;
	}
	
	/**
	 * Return CLocation instance by pkey
	 * @param DBManager $db
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return CLocation
	 */
	static public function GetLocationByPkey(DBManager $db, $pkey, $useCache=true)
	{
		// look up cache
		if ($useCache && isset(self::$locationCache[(int)$pkey])) return self::$locationCache[(int)$pkey];
		// load from DB
		$location = new CLocation($db);
		if ($location->Load((int)$pkey, $db)!==true) $location = null;
		if ($useCache) self::$locationCache[(int)$pkey] = $location;
		return $location;
	}

	/**
	 * Return CShop instance by pkey
	 * @param DBManager $db
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return CShop
	 */
	static public function GetShopByPkey(DBManager $db, $pkey, $useCache=true)
	{
		// look up cache
		if ($useCache && isset(self::$shopCache[(int)$pkey])) return self::$shopCache[(int)$pkey];
		// load from DB
		$shop = new CShop($db);
		if ($shop->Load((int)$pkey, $db)!==true) $shop = null;
		if ($useCache) self::$shopCache[(int)$pkey] = $shop;
		return $shop;
	}
	
	/**
	 * 
	 * @param DBManager $db
	 * @param type $locationName
	 * @return array
	 */
	static public function GetCustomerGroupsByLocationName(DBManager $db, User $user, $locationName)
	{
		$query ="SELECT ".CGroup::TABLE_NAME.".pkey FROM ".CLocation::TABLE_NAME." ";
		$query.=" LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$query.=" LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$query.=" WHERE ".CLocation::TABLE_NAME.".name='".$db->ConvertStringToDBString($locationName)."'";
		$query.=" GROUP BY ".CGroup::TABLE_NAME.".name ORDER BY ".CGroup::TABLE_NAME.".name";
		//echo $query;
		$data = $db->SelectAssoc($query);
		/*echo "<pre>";
		print_r($data);
		echo "</pre>";*/
		$cutomerManager = new CustomerManager($db);
		$returnValue = Array();
		foreach ($data as $value)
		{
			$cGroup = $cutomerManager->GetGroupByID($user, (int)$value['pkey']);
			if ($cGroup!=null)
			{
				$returnValue[] = $cGroup->GetName();
			}
		}
		return $returnValue;
	}
	
}
?>