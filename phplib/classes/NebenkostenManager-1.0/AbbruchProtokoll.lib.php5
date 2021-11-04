<?php
/**
 * Diese Klasse repräsentiert ein AbbruchProtokoll
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class AbbruchProtokoll extends DBEntry 
{
	/**
	 * Table name
	 * @var string
	 */
	const TABLE_NAME = "abbruchProtokolle";
	
	/**
	 * Nummer des Abbruchs
	 * @var int
	 */
	protected $nummer = -1;
	
	/**
	 * Zeitpunkt des Abbruchs
	 * @var int
	 */
	protected $datumAbbruch = 0;

	/**
	 * Begründung für den Abbruch
	 * @var string
	 */
	protected $begruendung = "";

	/**
	 * Zeitpunkt der Zurückweisung des Abbruchs
	 * @var int
	 */
	protected $datumAblehnung = 0;

	/**
	 * Begründung für die Zurückweisung des Abbruchs
	 * @var string
	 */
	protected $ablehnungsbegruendung = "";
		
	/**
	 * Zugehöriger Widerspruch
	 * @var Widerspruch
	 */
	protected $widerspruch = null;
			
	/**
	 * Benutzer der den Abbruch angefordert hat
	 * @var User
	 */
	protected $user = null;
			
	/**
	 * Benutzer der den Abbruch abgelehnt/stattgegeben hat
	 * @var User
	 */
	protected $userRelease = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function AbbruchProtokoll(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("nummer", "datumAbbruch", "begruendung", "datumAblehnung", "ablehnungsbegruendung", "widerspruch", "user", "userRelease");
		$dbConfig->rowParam = Array("BIGINT", "BIGINT", "LONGTEXT", "BIGINT", "LONGTEXT", "BIGINT", "BIGINT", "BIGINT");
		$dbConfig->rowIndex = Array("nummer");
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1 Kein Widerspruch zugeordnet
	 *								-2 Kein User zugeordnet
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// Array mit zu speichernden Daten anlegen
		if ($this->widerspruch==null) return -1;
		if ($this->user==null) return -2;
		if ($this->nummer==-1)
		{
			// Wenn die Nummer noch nicht gesetzt wurde, dies jetzt nachholen
			$data = $db->SelectAssoc("SELECT count(pkey) as count FROM ".self::TABLE_NAME." WHERE widerspruch=".$this->widerspruch->GetPKey());
			$this->nummer = ((int)$data[0]["count"])+1;
		}
		$rowName[]= "nummer";
		$rowData[]= $this->nummer;
		$rowName[]= "datumAbbruch";
		$rowData[]= $this->datumAbbruch;
		$rowName[]= "begruendung";
		$rowData[]= $this->begruendung;
		$rowName[]= "datumAblehnung";
		$rowData[]= $this->datumAblehnung;
		$rowName[]= "ablehnungsbegruendung";
		$rowData[]= $this->ablehnungsbegruendung;
		$rowName[]= "widerspruch";
		$rowData[]= $this->widerspruch->GetPKey();
		$rowName[]= "user";
		$rowData[]= $this->user->GetPKey();
		$rowName[]= "userRelease";
		$rowData[]= $this->userRelease==null ? -1 : $this->userRelease->GetPKey();
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array  	$data		Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->nummer = $data['nummer'];
		$this->datumAbbruch = $data['datumAbbruch'];
		$this->begruendung = $data['begruendung'];
		$this->datumAblehnung = $data['datumAblehnung'];
		$this->ablehnungsbegruendung = $data['ablehnungsbegruendung'];
		if ($data['widerspruch']!=-1)
		{
			$this->widerspruch = new Widerspruch($db);
			if ($this->widerspruch->Load($data['widerspruch'], $db)!==true) $this->widerspruch=null;
		}
		else
		{
			$this->widerspruch=null;
		}
		if ($data['user']!=-1)
		{
			$this->user = new User($db);
			if ($this->user->Load($data['user'], $db)!==true) $this->user=null;
		}
		else
		{
			$this->user=null;
		}
		if ($data['userRelease']!=-1)
		{
			$this->userRelease = new User($db);
			if ($this->userRelease->Load($data['userRelease'], $db)!==true) $this->userRelease=null;
		}
		else
		{
			$this->userRelease=null;
		}
		return true;
	}
	
	/**
	 * Gibt die Nummer des AbbruchProtokolls zurück
	 * @return int
	 */
	public function GetNummer()
	{
		return $this->nummer;
	}
	
	/**
	 * Setzt das Datum Abbruch
	 * @param int $datumAbbruch
	 * @return bool
	 */
	public function SetDatumAbbruch($datumAbbruch)
	{
		if (!is_int($datumAbbruch)) return false;
		$this->datumAbbruch = $datumAbbruch;
		return true;
	}
	
	/**
	 * Gibt das Datum Abbruch zurück
	 * @return int
	 */
	public function GetDatumAbbruch()
	{
		return $this->datumAbbruch;
	}
	
	/**
	 * Setzt die Begründung
	 * @param string $begruendung Begründung
	 * @return bool
	 */
	public function SetBegruendung($begruendung)
	{
		$this->begruendung=$begruendung;
		return true;
	}
	
	/**
	 * Gibt die Begründung zurück
	 * @return string
	 */
	public function GetBegruendung()
	{
		return $this->begruendung;
	}
	
	/**
	 * Setzt das Datum Ablehnung
	 * @param int $datumAblehnung Datum Ablehnung
	 * @return bool
	 */
	public function SetDatumAblehnung($datumAblehnung)
	{
		if (!is_int($datumAblehnung)) return false;
		$this->datumAblehnung = $datumAblehnung;
		return true;
	}
	
	/**
	 * Gibt das Datum Ablehnung zurück
	 * @return int Datum Ablehnung
	 */
	public function GetDatumAblehnung()
	{
		return $this->datumAblehnung;
	}
	
	/**
	 * Setzt die Ablehnungsbegründung
	 * @param string $ablehnungsbegruendung Ablehnungsbegründung
	 * @return bool
	 */
	public function SetAblehnungsbegruendung($ablehnungsbegruendung)
	{
		$this->ablehnungsbegruendung=$ablehnungsbegruendung;
		return true;
	}
	
	/**
	 * Gibt die Ablehnungsbegründung zurück
	 * @return string
	 */
	public function GetAblehnungsbegruendung()
	{
		return $this->ablehnungsbegruendung;
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
	 * @param Widerspruch $widerspruch Widerspruch
	 * @return bool
	 */
	public function SetWiderspruch(Widerspruch $widerspruch)
	{
		if ($widerspruch->GetPKey()==-1) return false;
		$this->widerspruch = $widerspruch;
		return true;
	}
		
	/**
	 * Gibt den zugehörigen Benutzer zurück
	 * @return User
	 */
	public function GetUser()
	{
		return $this->user;
	}
	
	/**
	 * Setzt den zugehörigen Benutzer
	 * @param User $user Benutzer
	 * @return bool
	 */
	public function SetUser(User $user)
	{
		if ($user->GetPKey()==-1) return false;
		$this->user = $user;
		return true;
	}
			
	/**
	 * Gibt den Benutzer zurück, der den Abbruch abgelehnt/stattgegeben hat
	 * @return User
	 */
	public function GetUserRelease()
	{
		return $this->userRelease;
	}
	
	/**
	 * Setzt den Benutzer der den Abbruch abgelehnt/stattgegeben hat
	 * @param User $userRelease Benutzer
	 * @return bool
	 */
	public function SetUserRelease(User $userRelease)
	{
		if ($userRelease->GetPKey()==-1) return false;
		$this->userRelease = $userRelease;
		return true;
	}
	
}
?>