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
class CCurrency extends DBEntry 
{
	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "customerCurrency";
	
	/**
	 * Ländername
	 * @var string
	 */
	protected $name="";
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function CCurrency(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("name", "short", "symbol", "iso4217");
		$dbConfig->rowParam = Array("VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(3)");
		$dbConfig->rowIndex = Array("name", "iso4217");
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
	 *								-3 Es ist bereits ein andere Währung mit diesem ISO-Code angelegt
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if ($this->name=="") return -1;
		if ($this->iso4217=="" || strlen(trim($this->iso4217))!=3) return -2;
		$temp = $db->SelectAssoc("SELECT pkey FROM ".self::TABLE_NAME." WHERE iso4217='".strtoupper(trim($this->iso4217))."' AND pkey!=".$this->GetPKey());
		if (count($temp)>0) return -3;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "name";
		$rowData[]= $this->name;
		$rowName[]= "short";
		$rowData[]= $this->short;
		$rowName[]= "symbol";
		$rowData[]= $this->symbol;
		$rowName[]= "iso4217";
		$rowData[]= strtoupper(trim($this->iso4217));
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
		$this->short = $data['short'];
		$this->symbol = $data['symbol'];
		$this->iso4217 = $data['iso4217'];
		return true;
	}
	
	/**
	 * Setzt den Namen der Währung
	 * @param string $name
	 * @return bool
	 */
	public function SetName($name)
	{
		$this->name = $name;
		return true;
	}
	
	/**
	 * Gibt den Namen der Währung zurück
	 * @return string
	 */
	public function GetName()
	{
		return $this->name;
	}
	
	/**
	 * Setzt den Währungs-Kürzel
	 * @param string $short
	 * @return bool
	 */
	public function SetShort($short)
	{
		$this->short = $short;
		return true;
	}
	
	/**
	 * Gibt das Währungs-Kürzel zurück
	 * @return string
	 */
	public function GetShort()
	{
		return $this->short;
	}
	
	/**
	 * Setzt das Währungs-Symbol
	 * @param string $symbol
	 * @return bool
	 */
	public function SetSymbol($symbol)
	{
		$this->symbol = $symbol;
		return true;
	}
	
	/**
	 * Gibt das Währungs-Symbol zurück
	 * @return string
	 */
	public function GetSymbol()
	{
		return $this->symbol;
	}
	
	/**
	 * Setzt den ISO-4217-Code
	 * @param string $iso4217
	 * @return bool
	 */
	public function SetIso4217($iso4217)
	{
		if ($iso4217=="" || strlen(trim($iso4217))!=3) return false;
		$this->iso4217 = strtoupper(trim($iso4217));
		return true;
	}
	
	/**
	 * Gibt den ISO-4217-Code zurück
	 * @return string
	 */
	public function GetIso4217()
	{
		return $this->iso4217;
	}
	
}
?>