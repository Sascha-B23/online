<?php
/**
 * Diese Klasse repräsentiert eine Addressgruppe
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class AddressGroup extends DBEntry implements AttributeNameMaper, AddressBase
{
	/**
	 * Datenbanknamen
	 * @var string
	 */
	const TABLE_NAME = "addresgroup";
	
	/**
	 * Prefix string for ID
	 */
	const ID_PREFIX = 'AG';
	
	/**
	 * Nachname
	 * @var string
	 */
	protected $name="";
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function AddressGroup(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("name");
		$dbConfig->rowParam = Array("VARCHAR(255)");
		$dbConfig->rowIndex = Array();
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * return the class type 
	 * @return int
	 */
	public function GetClassType()
	{
		return AddressBase::AM_CLASS_ADDRESSGROUP;
	}
	
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @param DBManager $db
	 * @return bool
	 */
	public function IsDeletable(&$db)
	{
		if ($this->GetAddressCompanyCount($db)>0) return false;
		return parent::IsDeletable($db);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1	Name nicht gesetzt
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// Name gesetzt?
		if ($this->name=="") return -1;
		// Array mit zu speichernden Daten anlegen
		$rowName[] = "name";
		$rowData[] = $this->name;
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbank
	 * @param array  	$data		Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool					Erfolg
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->name = $data['name'];
		return true;
	}
	
	/**
 	 * Setzt den Namen
	 * @param string $name
	 * @return bool
	 */
	public function SetName($name)
	{
		$this->name=$name;
		return true;
	}
	
	/**
	 * Gibt den Namen zurück
	 * @return string
	 */
	public function GetName()
	{
		return $this->name;
	}
	
	/**
	 * return the name of the group
	 * @return string
	 */
	public function GetAddressGroupName()
	{
		return $this->GetName();
	}
	
	/**
	 * Gibt die Anzahl der Firmen dieser Gruppe zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetAddressCompanyCount(DBManager $db)
	{
		if ($this->GetPKey()==-1) return false;
		$data = $db->SelectAssoc("SELECT count(pkey) as numAddresscompanies FROM ".AddressCompany::TABLE_NAME." WHERE addressGroup=".$this->GetPKey());
		return (int)$data[0]["numAddresscompanies"];
	}
	
	/**
	 * Gibt einen eindeutigen String für diesen Datensatz zurück
	 * @return string
	 */
	public function GetOverviewString()
	{
		if ($this->GetPKey()==-1) return "";
		$str ="";
		$str.=$this->GetName();
		return $str;
	}
	
	/**
	 * Gibt einen eindeutigen String für diesen Datensatz zurück
	 * @param bool $noID
	 * @return string Company ([ACID: PKEY])
	 */
	public function GetAddressIDString($noID=false)
	{
		if ($this->GetPKey()==-1) return "";
		$str = $this->GetName();													// Company Name
		if (!$noID) $str .= " [".self::ID_PREFIX.$this->GetPKey()."]";				// AG[PKey]
		return $str;
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
			case "id":
				return $languageManager->GetString('ADDRESSMANAGER', 'GROUP_ID');
			case "name":
				return $languageManager->GetString('ADDRESSMANAGER', 'GROUP_NAME');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}
	
	/**
	 * return default placeholders 
	 * @return Array
	 */
	public function GetPlaceholders(DBManager $db, $language = 'DE', $prefix = 'AP')
	{
		return Array();
	}
	
}
?>