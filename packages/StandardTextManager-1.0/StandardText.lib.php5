<?php

/**
 * Diese Klasse repräsentiert einen StandardText-Datensatz
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class StandardText extends DBEntry
{	
	/**
	 * StandardText Types
	 */
	const ST_TYPE_TEMPLATE = 0;
	const ST_TYPE_TRANSLATION = 1;
    const ST_TYPE_SCHEDULECOMMENT = 2;
	
	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "standardText";

	/**
	 * Name
	 * @var string
	 */
	protected $type = self::ST_TYPE_TEMPLATE;
	
	/**
	 * Multiline text needed?
	 * @var boolean
	 */
	protected $multiLineText = true;
	
	/**
	 * Name
	 * @var string
	 */
	protected $name="";

	/**
	 * Text
	 * @var string
	 */
	protected $standardText = Array();
	
	/**
	 * Languages
	 * @var string[] 
	 */
	protected $languages = Array();
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function StandardText(DBManager $db)
	{
		$this->languages = CustomerManager::GetLanguagesISO639List($db);
		if (!is_array($this->languages) || count($this->languages)==0)
		{
			die('Error: no languages available in class StandardText');
			exit;
		}
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("type", "name", "multiLineText");
		$dbConfig->rowParam = Array("INT", "TEXT", "INT");
		foreach ($this->languages as $value) 
		{
			$dbConfig->rowName[] = "standardText_".$value;
			$dbConfig->rowParam[] = "LONGTEXT";
		}
		$dbConfig->rowIndex = Array("type");
		parent::__construct($db, $dbConfig);
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
		if (trim($this->name)=="") return -1;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "type";
		$rowData[]= $this->type;
		$rowName[]= "name";
		$rowData[]= $this->name;
		$rowName[]= "multiLineText";
		$rowData[]= ($this->multiLineText ? 0 : 1);
		foreach ($this->languages as $value) 
		{
			$rowName[]= "standardText_".$value;
			$rowData[]= $this->standardText[$value];
		}
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbank
	 * @param array $data Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->type = (int)$data['type'];
		$this->name = $data['name'];
		$this->multiLineText = ($data['multiLineText']==0 ? true : false);
		foreach ($this->languages as $value) 
		{
			$this->standardText[$value] = $data["standardText_".$value];
		}
		return true;
	}
	
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @param DBManager 	$db			Datenbankobjekt
	 * @return bool					Kann der Datensatz gelöscht werden (true/false)
	 * @access public
	 */
	public function SetDeletable($deletable)
	{
		if (!is_bool($deletable)) return false;
		$this->undeletable = ($deletable ? 0 : 1);
		return true;
	}
	
	/**
	 * Setzt den Typ
	 * @param int $type
	 * @return bool
	 */
	public function SetType($type)
	{
		if (!is_int($type)) return false;
		$this->type = $type;
		if ($this->type==self::ST_TYPE_TRANSLATION) $this->SetMultiLineText(false);
        if ($this->type==self::ST_TYPE_SCHEDULECOMMENT) $this->SetMultiLineText(false);
		return true;
	}
	
	/**
	 * Gibt den Typ zurück
	 * @return int
	 */
	public function GetType()
	{
		return $this->type;
	}
	
	/**
	 * Set if multiline text is needed
	 * @param boolean $multiLineText
	 * @return boolean
	 */
	public function SetMultiLineText($multiLineText)
	{
		if (!is_bool($multiLineText)) return false;
		$this->multiLineText = $multiLineText;
		return true;
	}
	
	/**
	 * Return if multiline text is needed
	 * @return boolean
	 */
	public function IsMultiLineText()
	{
		return $this->multiLineText;
	}
	
	/**
	 * Setzt den Namen
	 * @param string $name
	 * @return bool
	 */
	public function SetName($name)
	{
		if (trim($name)=='') return false;
		$this->name = $name;
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
	 * Setzt den Vorlagentext
	 * @param string $standardText
	 * @return bool
	 */
	public function SetStandardText($language, $standardText)
	{
		if (!in_array($language, $this->languages)) return false;
		$this->standardText[$language] = $standardText;
		return true;
	}
	
	/**
	 * Gibt den Vorlagentext zurück
	 * @return string 
	 */
	public function GetStandardText($language)
	{
		if (!in_array($language, $this->languages)) return false;
		return $this->standardText[$language];
	}	
		
}
?>