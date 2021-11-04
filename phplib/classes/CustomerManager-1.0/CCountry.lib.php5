<?php
/**
 * Diese Klasse repräsentiert ein Land
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class CCountry extends DBEntry 
{
	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "customerCountry";
	
	/**
	 * ISO-Ländercode
	 * @var string
	 */
	protected $iso3166 = "";
	
	/**
	 * Ländername
	 * @var string
	 */
	protected $name="";
	
	/**
	 * Währung als ISO-4217-Code
	 * @var string
	 */
	protected $currency="";
	
	/**
	 * Return the CCountry object for the passed ISO 3166 code
	 * @param DBManager $db
	 * @param string $iso3166
	 * @return CCountry
	 */
	static public function GetCountryByIso3166(DBManager $db, $iso3166)
	{
		if ($iso3166=="" || strlen(trim($iso3166))!=2) return null;
		$iso3166 = strtoupper(trim($iso3166));
		$country = new CCountry($db);
		$data = $db->SelectAssoc("SELECT * FROM ".self::TABLE_NAME." WHERE iso3166='".$db->ConvertStringToDBString($iso3166)."'");
		if (count($data)==0) return null;
		if ($country->LoadFromArray($data[0], $db)===true) return $country;
		return null;
	}
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function CCountry(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("iso3166", "name", "currency");
		$dbConfig->rowParam = Array("VARCHAR(2)", "VARCHAR(255)", "VARCHAR(3)");
		$dbConfig->rowIndex = Array("iso3166", "name");
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
	 *								-1 Ländername nicht gesetzt
	 *								-2 ISO-Code nicht gesetz oder ungültig
	 *								-3 Es ist bereits ein anderes Land mit diesem ISO-Code angelegt
	 *								-4 Währung nicht gesetzt
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// EMail-Adresse gesetzt?
		if ($this->name=="") return -1;
		if ($this->iso3166=="" || strlen(trim($this->iso3166))!=2) return -2;
		$temp = $db->SelectAssoc("SELECT pkey FROM ".self::TABLE_NAME." WHERE iso3166='".strtoupper(trim($this->iso3166))."' AND pkey!=".$this->GetPKey());
		if (count($temp)>0) return -3;
		if ($this->currency=="" || strlen(trim($this->currency))!=3) return -4;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "name";
		$rowData[]= $this->name;
		$rowName[]= "iso3166";
		$rowData[]= strtoupper(trim($this->iso3166));
		$rowName[]= "currency";
		$rowData[]= strtoupper(trim($this->currency));
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
		$this->iso3166 = $data['iso3166'];
		$this->currency = $data['currency'];
		return true;
	}
	
	/**
	 * Setzt den Ländernamen
	 * @param string $name
	 * @return bool
	 */
	public function SetName($name)
	{
		$this->name = $name;
		return true;
	}
	
	/**
	 * Gibt den Ländernamen zurück
	 * @return string
	 */
	public function GetName()
	{
		return $this->name;
	}
	
	/**
	 * Setzt die zugehörige Benutzer-Gruppe
	 * @param string $iso3166
	 * @return bool
	 */
	public function SetIso3166($iso3166)
	{
		if ($iso3166=="" || strlen(trim($iso3166))!=2) return false;
		$this->iso3166 = strtoupper(trim($iso3166));
		return true;
	}
	
	/**
	 * Gibt die zugehörige Benutzer-Gruppe zurück
	 * @return string
	 */
	public function GetIso3166()
	{
		return $this->iso3166;
	}
	
	/**
	 * Setzt die Währung als ISO-4217-Code
	 * @param string $iso4217
	 * @return bool
	 */
	public function SetCurrency($iso4217)
	{
		if ($iso4217=="" || strlen(trim($iso4217))!=3) return false;
		$this->currency = strtoupper(trim($iso4217));
		return true;
	}
	
	/**
	 * Gibt die Währung als ISO-4217-Code zurück
	 * @return string
	 */
	public function GetCurrency()
	{
		return $this->currency;
	}
	
	
	
}
?>