<?php
/**
 * Diese Klasse repräsentiert eine Sprache
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2014 Stoll von Gáti GmbH www.stollvongati.com
 */
class CLanguage extends DBEntry 
{
	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "customerLanguage";
	
	/**
	 * Ländername
	 * @var string
	 */
	protected $name="";
	
	/**
	 * ISO-Sprachcodes
	 * @var string
	 */
	protected $iso639="";
	
	/**
	 * Return the CCountry object for the passed ISO 639 code
	 * @param DBManager $db
	 * @param string $iso639
	 * @return CCountry
	 */
	static public function GetLanguageByIso639(DBManager $db, $iso639)
	{
		if ($iso639=="" || strlen(trim($iso639))!=2) return null;
		$iso639 = strtoupper(trim($iso639));
		$language = new CLanguage($db);
		$data = $db->SelectAssoc("SELECT * FROM ".self::TABLE_NAME." WHERE iso639='".$db->ConvertStringToDBString($iso639)."'");
		if (count($data)==0) return null;
		if ($language->LoadFromArray($data[0], $db)===true) return $language;
		return null;
	}
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function CLanguage(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("name", "iso639");
		$dbConfig->rowParam = Array("VARCHAR(255)", "VARCHAR(2)");
		$dbConfig->rowIndex = Array("name", "iso639");
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @param DBManager $db
	 * @return bool
	 */
	public function IsDeletable(&$db)
	{
		//if( $this->GetCompanyCount($db)>0 )return false;
		return parent::IsDeletable($db);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1 Sprachname nicht gesetzt
	 *								-2 ISO-Code nicht gesetz oder ungültig
	 *								-3 Es ist bereits ein andere Sprache mit diesem ISO-Code angelegt
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if ($this->name=="") return -1;
		if ($this->iso639=="" || strlen(trim($this->iso639))!=2) return -2;
		$temp = $db->SelectAssoc("SELECT pkey FROM ".self::TABLE_NAME." WHERE iso639='".strtoupper(trim($this->iso639))."' AND pkey!=".$this->GetPKey());
		if (count($temp)>0) return -3;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "name";
		$rowData[]= $this->name;
		$rowName[]= "iso639";
		$rowData[]= strtoupper(trim($this->iso639));
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
		$this->iso639 = $data['iso639'];
		return true;
	}
	
	/**
	 * Setzt den Namen der Sprache
	 * @param string $name
	 * @return bool
	 */
	public function SetName($name)
	{
		$this->name = $name;
		return true;
	}
	
	/**
	 * Gibt den Namen der Sprache zurück
	 * @return string
	 */
	public function GetName()
	{
		return $this->name;
	}
	
	/**
	 * Setzt den ISO-639-Sprachcode
	 * @param string $iso639
	 * @return bool
	 */
	public function SetIso639($iso639)
	{
		if (!self::ValidateIso639($iso639)) return false;
		$this->iso639 = strtoupper(trim($iso639));
		return true;
	}
	
	/**
	 * Gibt den ISO-639-Sprachcode zurück
	 * @return string
	 */
	public function GetIso639()
	{
		return $this->iso639;
	}
	
	/**
	 * Check if the passed string is a valid ISO-639 code
	 * @param string $iso639
	 * @return boolean
	 */
	static public function ValidateIso639($iso639)
	{
		if ($iso639=="" || strlen(trim($iso639))!=2) return false;
		return true;
	}
	
}
?>