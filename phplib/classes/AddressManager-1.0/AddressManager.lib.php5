<?php
require_once("AddressBase.lib.php5");
require_once("AddressGroup.lib.php5");
require_once("AddressCompany.lib.php5");
require_once("AddressData.lib.php5");

// Konstanten des UserManagers
define("AM_ADDRESSDATA_TYPE_NONE", 0);			// Undefiniert
define("AM_ADDRESSDATA_TYPE_ADVOCATE", 1);		// Anwalt
define("AM_ADDRESSDATA_TYPE_TRUSTEE", 2);		// Verwalter
define("AM_ADDRESSDATA_TYPE_OWNER", 4);			// Eigentümer
//define("AM_ADDRESSDATA_TYPE_SYSTEMUSER", 8);	// Systembenutzer

$AM_ADDRESSDATA_TYPE = array(	AM_ADDRESSDATA_TYPE_NONE => "Undefiniert",
								AM_ADDRESSDATA_TYPE_ADVOCATE => "Anwalt",
								AM_ADDRESSDATA_TYPE_TRUSTEE => "Verwalter",
								AM_ADDRESSDATA_TYPE_OWNER => "Eigentümer",
							);

/**
 * Verwaltungsklasse für Address-Daten
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class AddressManager extends SearchDBEntry 
{
	
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * Dummyobjekte 
	 */
	protected $groupDummy=null;
	protected $companyDummy=null;
	protected $dataDummy=null;
	
	/**
	 * Array map with all AddressGroup instances loaded with fn. GetAddressGroupByPkey
	 * @var Array
	 */
	protected static $addressGroupCache = Array();
	
	/**
	 * Array map with all AddressCompany instances loaded with fn. GetAddressCompanyByPkey
	 * @var Array
	 */
	protected static $addressCompanyCache = Array();
	
	/**
	 * Array map with all AddressData instances loaded with fn. GetAddressDataByPkey
	 * @var Array
	 */
	protected static $addressDataCache = Array();
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function AddressManager(DBManager $db)
	{
		$this->db = $db;
		$this->groupDummy = new AddressGroup($this->db);
		$this->companyDummy = new AddressCompany($this->db);
		$this->dataDummy = new AddressData($this->db);
	}
	
	/**
	 * Gibt die Anzahl der Addressen zurück
	 * @var string $searchString 
	 * @var int $type
	 * @return int
	 */
	public function GetAddressDataCount($searchString="", $type=AM_ADDRESSDATA_TYPE_NONE)
	{
		$whereClause="";
		if ($type!=AM_ADDRESSDATA_TYPE_NONE)
		{
			$whereClause="type=".$type;
		}
		
		$joinClause = "";
		$joinClause ="LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressData::TABLE_NAME.".addressCompany=".AddressCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".AddressGroup::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".addressGroup=".AddressGroup::TABLE_NAME.".pkey ";
		$rowsToUse = Array(	Array("tableName" => AddressData::TABLE_NAME, "tableRowNames" => $this->dataDummy->GetTableConfig()->rowName),
							Array("tableName" => AddressCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => AddressGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		return $this->GetDBEntryCount($searchString, AddressData::TABLE_NAME, $allRowNames, $whereClause, $joinClause);
	}
		
	/**
	 * Gibt die Addressen entsprechend den übergebenen Parametern zurück
	 * @param string	$searchString		Suchstring
	 * @param string	$orderBy			Spaltenname nach dem sortiert werden soll
	 * @param string	$orderDirection		Sortierrichtung (ASC oder DESC)
	 * @param string	$currentPage		Aktuelle Seite
	 * @param string	$numEntrysPerPage	Anzhal der Einträge je Seite
	 * @return AddressData[]
	 */
	public function GetAddressData($searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=0, $type=AM_ADDRESSDATA_TYPE_NONE, $additionalWhereClause="")
	{
		$whereClause="";
		if ($type!=AM_ADDRESSDATA_TYPE_NONE)
		{
			$whereClause="type=".$type;
		}
		if ($additionalWhereClause!="")
		{
			if ($whereClause!="") $whereClause.=" AND ";
			$whereClause.=" (".$additionalWhereClause.") ";
		}
		$joinClause = "";
		$joinClause ="LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressData::TABLE_NAME.".addressCompany=".AddressCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".AddressGroup::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".addressGroup=".AddressGroup::TABLE_NAME.".pkey ";
		$rowsToUse = Array(	Array("tableName" => AddressData::TABLE_NAME, "tableRowNames" => $this->dataDummy->GetTableConfig()->rowName),
							Array("tableName" => AddressCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => AddressGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		$dbEntrys=$this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, AddressData::TABLE_NAME, $allRowNames, $whereClause, $joinClause);
		// Objekte erzeugen
		$addresses=Array();
		for( $a=0; $a<count($dbEntrys); $a++){
			$address=new AddressData($this->db);
			if ($address->LoadFromArray($dbEntrys[$a], $this->db)===true) $addresses[]=$address;
		}
		return $addresses;
	}
	
	/**
	 * Return the address object with of requested id
	 * @param DBManager $db
	 * @param string $id
	 * @return AddressData|AddressCompany
	 */
	static public function GetAddessById(DBManager $db, $id)
	{
		$returnValue = null;
		$matches = Array();
		if (preg_match("/\[(?P<class>".AddressData::ID_PREFIX."|".AddressCompany::ID_PREFIX.")(?P<id>\d+)\]/", $id, $matches)==0) return $returnValue;
		switch($matches["class"])
		{
			case AddressData::ID_PREFIX:
				$returnValue = self::GetAddressDataByPkey($db, (int)$matches["id"]);
				break;
			case AddressCompany::ID_PREFIX:
				$returnValue = self::GetAddressCompanyByPkey($db, (int)$matches["id"]);
				break;
		}
		return $returnValue;
	}
	
	/**
	 * Gibt den Benutzer mit dem übergebenen Vor- und Nachnamen zurück
	 * @param string $loginName
	 * @return object
	 */
	public function GetAdressByName($firstname, $name)
	{
		$addressDataID = $this->db->SelectAssoc("SELECT email FROM ".AddressData::TABLE_NAME." WHERE name='".trim($name)."' AND firstname='".trim($firstname)."'");
		if (count($addressDataID)!=1) return null;
		return $this->GetUserByLogin( $addressDataID[0]["email"] );
	}
	
	/**
	 * Gibt die Anzahl der Addressen zurück
	 * @return int
	 */
	public function GetAddressGroupDataCount($searchString="")
	{
		$object=new AddressGroup($this->db);
		return $this->GetDBEntryCount($searchString, AddressGroup::TABLE_NAME, $object->GetTableConfig()->rowName);
	}
		
	/**
	 * Gibt die Addressen entsprechend den übergebenen Parametern zurück
	 * @param string $searchString		Suchstring
	 * @param string $orderBy			Spaltenname nach dem sortiert werden soll
	 * @param int $orderDirection		Sortierrichtung (ASC=0 oder DESC=1)
	 * @param int $currentPage			Aktuelle Seite
	 * @param int $numEntrysPerPage		Anzhal der Einträge je Seite
	 * @return AddressGroup[]
	 */
	public function GetAddressGroupData($searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=0)
	{
		$object = new AddressGroup($this->db);
		$dbEntrys = $this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, AddressGroup::TABLE_NAME, $object->GetTableConfig()->rowName);
		// Objekte erzeugen
		$addresses = Array();
		for ($a=0; $a<count($dbEntrys); $a++)
		{
			$address = new AddressGroup($this->db);
			if ($address->LoadFromArray($dbEntrys[$a], $this->db)===true) $addresses[] = $address;
		}
		return $addresses;
	}
	
		/**
	 * Return AddressData instance by pkey
	 * @param DBManager $db
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return AddressGroup
	 */
	static public function GetAddressGroupByPkey(DBManager $db, $pkey, $useCache=true)
	{
		// look up cache
		if ($useCache && isset(self::$addressGroupCache[(int)$pkey])) return self::$addressGroupCache[(int)$pkey];
		// load from DB
		$addressData = new AddressGroup($db);
		if ($addressData->Load((int)$pkey, $db)!==true) $addressData = null;
		if ($useCache) self::$addressGroupCache[(int)$pkey] = $addressData;
		return $addressData;
	}
	
	/**
	 * Return AddressData instance by pkey
	 * @param DBManager $db
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return AddressData
	 */
	static public function GetAddressDataByPkey(DBManager $db, $pkey, $useCache=true)
	{
		// look up cache
		if ($useCache && isset(self::$addressDataCache[(int)$pkey])) return self::$addressDataCache[(int)$pkey];
		// load from DB
		$addressData = new AddressData($db);
		if ($addressData->Load((int)$pkey, $db)!==true) $addressData = null;
		if ($useCache) self::$addressDataCache[(int)$pkey] = $addressData;
		return $addressData;
	}
	
	/**
	 * Gibt die Anzahl der Addressen zurück
	 * @return int
	 */
	public function GetAddressCompanyCount($searchString="")
	{
		$joinClause = "LEFT JOIN ".AddressGroup::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".addressGroup=".AddressGroup::TABLE_NAME.".pkey ";
		$rowsToUse = Array(	Array("tableName" => AddressCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => AddressGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable($rowsToUse);
		return $this->GetDBEntryCount($searchString, AddressCompany::TABLE_NAME, $allRowNames, "", $joinClause);
	}
		
	/**
	 * Gibt die Addressen entsprechend den übergebenen Parametern zurück
	 * @param string $searchString		Suchstring
	 * @param string $orderBy			Spaltenname nach dem sortiert werden soll
	 * @param int $orderDirection		Sortierrichtung (ASC=0 oder DESC=1)
	 * @param int $currentPage			Aktuelle Seite
	 * @param int $numEntrysPerPage		Anzhal der Einträge je Seite
	 * @return AddressCompany[]
	 */
	public function GetAddressCompany($searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=0, $additionalWhereClause="")
	{
		$whereClause="";
		if ($additionalWhereClause!="")
		{
			if ($whereClause!="") $whereClause.=" AND ";
			$whereClause.=" (".$additionalWhereClause.") ";
		}
		
		$joinClause = "LEFT JOIN ".AddressGroup::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".addressGroup=".AddressGroup::TABLE_NAME.".pkey ";
		$rowsToUse = Array(	Array("tableName" => AddressCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => AddressGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable($rowsToUse);
		$dbEntrys = $this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, AddressCompany::TABLE_NAME, $allRowNames, $whereClause, $joinClause);
		// Objekte erzeugen
		$addresses = Array();
		for ($a=0; $a<count($dbEntrys); $a++)
		{
			$address = new AddressCompany($this->db);
			if ($address->LoadFromArray($dbEntrys[$a], $this->db)===true) $addresses[] = $address;
		}
		return $addresses;
	}
	
	/**
	 * Return AddressData instance by pkey
	 * @param DBManager $db
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return AddressCompany
	 */
	static public function GetAddressCompanyByPkey(DBManager $db, $pkey, $useCache=true)
	{
		// look up cache
		if ($useCache && isset(self::$addressCompanyCache[(int)$pkey])) return self::$addressCompanyCache[(int)$pkey];
		// load from DB
		$addressData = new AddressCompany($db);
		if ($addressData->Load((int)$pkey, $db)!==true) $addressData = null;
		if ($useCache) self::$addressCompanyCache[(int)$pkey] = $addressData;
		return $addressData;
	}
	
	/**
	 * Return AddressData instance by pkey
	 * @param DBManager $db
	 * @param int $type
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return AddressData|AddressCompany|AddressGroup
	 */
	static public function GetAddressElementByPkeyAndType(DBManager $db, $type, $pkey, $useCache=true)
	{
		switch($type)
		{
			case AddressBase::AM_CLASS_ADDRESSDATA:
				return self::GetAddressDataByPkey($db, $pkey, $useCache);
			case AddressBase::AM_CLASS_ADDRESSCOMPANY:
				return self::GetAddressCompanyByPkey($db, $pkey, $useCache);
			case AddressBase::AM_CLASS_ADDRESSGROUP:
				return self::GetAddressGroupByPkey($db, $pkey, $useCache);
		}
		return null;
	}
	
}
?>