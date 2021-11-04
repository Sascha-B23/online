<?php
/**
 * Diese Klasse repräsentiert eine Kunden-Gruppe (z.B. Esprit)
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class CGroup extends DBEntry implements DependencyFileDescription, AttributeNameMaper
{
	/**
	 * Datenbankname und Spalten
	 * @var string
	 */
	const TABLE_NAME = "customerGroup";
	
	/**
	 * Name
	 * @var string
	 */
	protected $name="";
	
	/**
	 * Zugehörige Benutzergruppe
	 * @var UserGroup
	 */
	protected $userGroup = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function CGroup(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("name", "userGroup");
		$dbConfig->rowParam = Array("VARCHAR(255)", "BIGINT");
		$dbConfig->rowIndex = Array("userGroup");
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @param DBManager $db
	 * @return bool
	 */
	public function IsDeletable(&$db)
	{
		if( $this->GetCompanyCount($db)>0 )return false;
		return parent::IsDeletable($db);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1	Gruppenname nicht gesetzt
	 *								-2	Zugehörige Benutzer-Gruppe nicht gesetzt
	 *								-3	Gruppe mit diesem Namen existiert bereits
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// EMail-Adresse gesetzt?
		if( $this->name=="" )return -1;
		if( $this->userGroup==null )return -2;
		// Prüfen, ob neue Gruppe
		if($this->pkey == -1){
			// Prüfen ob Gruppe mit gleichem Namen bereits existiert
			if( count($db->SelectAssoc("SELECT pkey FROM ".self::TABLE_NAME." WHERE name='".$db->ConvertStringToDBString($this->name)."'", false)) != 0){
				// Gruppe mit disesem Namen existiert bereits
				return -3;
			}
		}
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "name";
		$rowData[]= $this->name;
		$rowName[]= "userGroup";
		$rowData[]= $this->userGroup==null ? -1 : $this->userGroup->GetPKey();
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
		$this->name = $data['name'];
		if( $data['userGroup']!=-1 )
		{
			$this->userGroup = new UserGroup($db);
			if ($this->userGroup->Load($data['userGroup'], $db)!==true) $this->userGroup = null;
		}
		else
		{
			$this->userGroup=null;
		}
		return true;
	}
	
	/**
	 * Return Description
	 */
	public function GetDependencyFileDescription()
	{
		return $this->GetName();
	}

	/**
	 * Setzt den Gruppennamen
	 * @param string $name
	 * @return bool
	 */
	public function SetName($name)
	{
		$this->name=$name;
		return true;
	}
	
	/**
	 * Gibt den Gruppennamen zurück
	 * @return string
	 */
	public function GetName()
	{
		return $this->name;
	}
	
	/**
	 * Gibt die zugehörige Benutzer-Gruppe zurück
	 * @return UserGroup
	 */
	public function GetUserGroup()
	{
		return $this->userGroup;
	}
	
	/**
	 * Setzt die zugehörige Benutzer-Gruppe
	 * @param UserGroup $userGroup
	 * @return bool
	 */
	public function SetUserGroup(UserGroup $userGroup)
	{
		if ($userGroup->GetPKey()==-1) return false;
		$this->userGroup = $userGroup;
		return true;
	}
	
	/**
	 * Gibt die Anzahl der untergeordneten Firmen zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetCompanyCount(DBManager $db)
	{
		if ($this->pkey==-1) return 0;
		$data = $db->SelectAssoc("SELECT count(pkey) as count FROM ".CCompany::TABLE_NAME." WHERE cGroup=".$this->pkey );
		return (int)$data[0]["count"];
	}
	
	/**
	 * Gibt alle untergeordneten Firmen zurück
	 * @param DBManager $db
	 * @return CCompany[]
	 */
	public function GetCompanys(DBManager $db)
	{
		if ($this->pkey==-1) return Array();
		$data = $db->SelectAssoc("SELECT * FROM ".CCompany::TABLE_NAME." WHERE cGroup=".$this->pkey." ORDER BY name");
		$objects=Array();
		for($a=0; $a<count($data); $a++)
		{
			$object=new CCompany($db);
			$data[$a]['cGroup'] = $this;
			if ($object->LoadFromArray($data[$a], $db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Ordnet die übergebene Firma dieser Gruppe unter
	 * @param DBManager $db
	 * @param CCompany $company
	 * @return bool
	 */
	public function AddCompany(DBManager $db, CCompany $company)
	{
		if ($this->pkey==-1) return false;
		// Der Firma diese Gruppe zuweisen...
		return $company->SetGroup($this);
	}
	
	/**
	 * Prüft, ob der übergebene Benutzer berechtigt ist, dieses Objekt zu verarbeiten
	 * @param User $user
	 * @param DBManager $db
	 * @return bool
	 */
	public function HasUserAccess(User $user, DBManager $db)
	{
		if ($user->GetGroupBasetype($db) >= UM_GROUP_BASETYPE_ADMINISTRATOR) return true;
		$userGroup = $this->GetUserGroup();
		if ($userGroup==null) return false;
		$userGroupIDs = $user->GetGroupIDs($db);
		return in_array($userGroup->GetPKey(), $userGroupIDs);
	}
	
	/**
	 * Return a human readable name for the requested attribute
	 * @param LanguageManager $languageManager
	 * @param string $attributeName
	 * @return string
	 */
	static public function GetAttributeName(ExtendedLanguageManager $languageManager, $attributeName)
	{
		switch($attributeName)
		{
			case "name":
				return $languageManager->GetString('CUSTOMERMANAGER', 'GROUP_NAME');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}
	
}
?>