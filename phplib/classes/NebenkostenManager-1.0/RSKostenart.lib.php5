<?php
/**
 * Diese Klasse repräsentiert eine FMS-Kostenart
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class RSKostenart extends DBEntry 
{
	
	/**
	 * Datenbankname und Spalten
	 * @var string
	 */
	const TABLE_NAME = "rsKostenart";
	
	/**
	 * Name
	 * @var string
	 */
	protected $name="";

	/**
	 * Beschreibung
	 * @var string
	 */
	protected $beschreibung="";
	
	/**
	 * Sollwerte
	 * @var array
	 */
	protected $sollwerte=Array();
		
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function RSKostenart(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("name", "beschreibung", "sollwerte");
		$dbConfig->rowParam = Array("TEXT", "LONGTEXT", "LONGTEXT");
		$dbConfig->rowIndex = Array();
		parent::__construct($db, $dbConfig);
	}
		
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @return bool			Kann der Datensatz gelöscht werden (true/false)
	 */
	public function IsDeletable(&$db)
	{
		if ($this->pkey!=-1 && $db->TableExists(Teilabrechnungsposition::TABLE_NAME))
		{
			$data=$db->SelectAssoc("SELECT count(pkey) as count FROM ".Teilabrechnungsposition::TABLE_NAME." WHERE kostenartRS=".$this->pkey );
			if ((int)$data[0]["count"]>0) return false;
		}
		return parent::IsDeletable($db);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1	Kein Name gesetzt
	 *								-2	FMS-Kostenart mit disesem Namen existiert bereits
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if( trim($this->name)=="" )return -1;
		// Prüfen, ob neue FMS-Kostenart
		if($this->pkey == -1){
			// Prüfen ob FMS-Kostenart mit gleichem Namen bereits existiert
			if( count($db->SelectAssoc("SELECT pkey FROM ".self::TABLE_NAME." WHERE name='".$this->name."'", false)) != 0){
				// FMS-Kostenart mit disesem Namen existiert bereits
				return -2;
			}
		}else{
			// Prüfen ob FMS-Kostenart mit gleichem Namen bereits existiert
			if( count($db->SelectAssoc("SELECT pkey FROM ".self::TABLE_NAME." WHERE name='".$this->name."' AND pkey!=".$this->pkey, false)) != 0){
				// FMS-Kostenart mit disesem Namen existiert bereits
				return -2;
			}			
		}
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "name";
		$rowData[]= $this->name;
		$rowName[]= "beschreibung";
		$rowData[]= $this->beschreibung;
		$rowName[]= "sollwerte";
		$rowData[]= serialize($this->sollwerte);
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
		$this->beschreibung = $data['beschreibung'];
		$this->sollwerte = unserialize($data['sollwerte']);
		if (!isset($this->sollwerte['DE'])) $this->sollwerte = Array('DE' => unserialize($data['sollwerte']));
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
	 * Setzt die Beschreibung
	 * @param string $beschreibung
	 * @return bool
	 */
	public function SetBeschreibung($beschreibung)
	{
		$this->beschreibung=$beschreibung;
		return true;
	}
	
	/**
	 * Gibt die Beschreibung zurück
	 * @return string
	 */
	public function GetBeschreibung()
	{
		return $this->beschreibung;
	}
	
	/**
	 * Setzt Sollwert
	 * @param string $country Country (DE, CH ...)
	 * @param int $locationType Standorttyp (Center, Outlet ...)
	 * @param float $sollwert Sollwert
	 * @return bool
	 */
	public function SetSollwert($country, $locationType, $sollwert)
	{
		if (strlen(trim($country))<2) return false;
		if (!is_int($locationType) || !is_numeric($sollwert)) return false;
		if (!isset($this->sollwerte[trim($country)])) $this->sollwerte[trim($country)] = Array();
		$this->sollwerte[trim($country)][$locationType]=(float)$sollwert;
		return true;
	}
	
	/**
	 * Gibt Sollwert zurück
	 * @param string $country Country (DE, CH ...)
	 * @param int $locationType Standorttyp (Center, Outlet ...)
	 * @return float
	 */
	public function GetSollwert($country, $locationType)
	{
		if (strlen(trim($country))<2) return false;
		if (!isset($this->sollwerte[trim($country)])) return false;
		if (!is_int($locationType)) return false;
		return isset($this->sollwerte[trim($country)][$locationType]) ? $this->sollwerte[trim($country)][$locationType] : 0.0;
	}
	
}
?>