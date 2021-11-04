<?php
define("NKM_TERMIN_TYPE_UNBEKANNT", 0);
define("NKM_TERMIN_TYPE_PHONE", 1);
define("NKM_TERMIN_TYPE_MEETING", 2);
define("NKM_TERMIN_TYPE_RESPONSE", 3);

/**
 * Diese Klasse repräsentiert einen Termin (obsolete since December 2012)
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class Termin extends DBEntry 
{
	/**
	 * Datenbankname und Spalten
	 * @var string
	 */
	const TABLE_NAME = "termin";

	/**
	 * Datum und Uhrzeit
	 * @var timestamp
	 */
	protected $datumUndUhrzeit=0;
	
	/**
	 * Bemerkung
	 * @var string
	 */
	protected $bemerkung="";
	
	/**
	 * Typ
	 * @var int
	 */
	protected $type=NKM_TERMIN_TYPE_UNBEKANNT;
	
	/**
	 * Zugehöriger Widerspruch
	 * @var Widerspruch
	 */
	protected $widerspruch = null;
	
	/**
	 * Zugehöriger Benutzer
	 * @var User
	 */
	protected $author = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function Termin(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("datumUndUhrzeit", "bemerkung", "type", "widerspruch", "author");
		$dbConfig->rowParam = Array("BIGINT", "LONGTEXT", "INT", "BIGINT", "BIGINT");
		$dbConfig->rowIndex = Array("widerspruch");
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1	Kein Widerspruch zugeordnet
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if( $this->widerspruch==null )return -1;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "datumUndUhrzeit";
		$rowData[]= $this->datumUndUhrzeit;
		$rowName[]= "bemerkung";
		$rowData[]= $this->bemerkung;
		$rowName[]= "type";
		$rowData[]= $this->type;
		$rowName[]= "widerspruch";
		$rowData[]= $this->widerspruch==null ? -1 : $this->widerspruch->GetPKey();
		$rowName[]= "author";
		$rowData[]= $this->author==null ? -1 : $this->author->GetPKey();
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
		$this->datumUndUhrzeit = $data['datumUndUhrzeit'];
		$this->bemerkung = $data['bemerkung'];
		$this->type = $data['type'];
		if ($data['widerspruch']!=-1)
		{
			$this->widerspruch = new Widerspruch($db);
			if ($this->widerspruch->Load($data['widerspruch'], $db)!==true) $this->widerspruch = null;
		}
		else
		{
			$this->widerspruch=null;
		}
		if ($data['author']!=-1)
		{
			$this->author = new User($db);
			if ($this->author->Load($data['author'], $db)!==true) $this->author = null;
		}
		else
		{
			$this->author=null;
		}
		return true;
	}
	
	/**
	 * Setzt das Datum mit Uhrzeit
	 * @param int $datumUndUhrzeit
	 * @return bool
	 */
	public function SetDatumUndUhrzeit($datumUndUhrzeit)
	{
		if( !is_int($datumUndUhrzeit) )return false;
		$this->datumUndUhrzeit=$datumUndUhrzeit;
		return true;
	}
	
	/**
	 * Gibt das Datum mit Uhrzeit zurück
	 * @return int
	 */
	public function GetDatumUndUhrzeit()
	{
		return $this->datumUndUhrzeit;
	}
		
	/**
	 * Setzt Bemerkung
	 * @param string $bemerkung
	 * @return bool
	 */
	public function SetBemerkung($bemerkung)
	{
		$this->bemerkung=$bemerkung;
		return true;
	}
	
	/**
	 * Gibt Bemerkung zurück
	 * @return string
	 */
	public function GetBemerkung()
	{
		return $this->bemerkung;
	}
		
	/**
	 * Setzt den Type
	 * @param int $type
	 * @return bool
 	 */
	public function SetType($type)
	{
		if( !is_int($type) )return false;
		$this->type=$type;
		return true;
	}
	
	/**
	 * Gibt den Type zurück
	 * @return int
	 */
	public function GetType()
	{
		return $this->type;
	}
	
	/**
	 * Gibt den zugehörigen Benutzer zurück
	 * @return User
	 */
	public function GetAuthor()
	{
		return $this->author;
	}
	
	/**
	 * Setzt den zugehörigen Benutzer
	 * @param User $author
	 * @return bool
 	 */
	public function SetAuthor(User $author)
	{
		if ($author->GetPKey()==-1) return false;
		$this->author = $author;
		return true;
	}
	
	/**
	 * Gibt den zugehörigen Widerspruch zurück
	 * @return Widerspruch
	 */
	public function GetWiderspruch()
	{
		return $this->widerspruch;
	}
	
	/**
	 * Setzt den zugehörigen Widerspruch
	 * @param Widerspruch $widerspruch
	 * @return bool
	 */
	public function SetWiderspruch(Widerspruch $widerspruch)
	{
		if ($widerspruch->GetPKey()==-1) return false;
		$this->widerspruch = $widerspruch;
		return true;
	}
}
?>