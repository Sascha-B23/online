<?php
/**
 * Diese Klasse repräsentiert einen Textbaustein für den Widerspruchsgenerator
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TextModule extends DBEntry 
{
	/**
	 * Maximale Anzahl an Textblöcken
	 */
	const MAX_NUMBER_OF_TEXT_BLOCKS = 5;
	
	/**
	 * Datenbankname und Spalten
	 * @var string
	 */
	const TABLE_NAME = "textmodule";
	
	/**
	 * country code (ISO 3166)
	 * @var string
	 */
	protected $country="";
	
	/**
	 * Überschrift
	 * @var string
	 */
	protected $title="";
		
	/**
	 * Vorlagentext 'Standpunkt Name Eigentümer/Verwalter/Anwalt'
	 * @var string
	 */
	protected $textLeft="";
	
	/**
	 * Anzahl der Bedingungen
	 * @var int
	 */
	protected $numberOfTextBlocks = 1;
	
	/**
	 * Text
	 * @var array
	 */
	protected $textRight = Array();
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function TextModule(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("country", "title", "textLeft", "numberOfTextBlocks");
		$dbConfig->rowParam = Array("VARCHAR(2)", "LONGTEXT", "LONGTEXT", "INT");
		$dbConfig->rowIndex = Array("country");
		for($a=0; $a<TextModule::MAX_NUMBER_OF_TEXT_BLOCKS; $a++)
		{
			$dbConfig->rowName[] = "textRight_".$a;
			$dbConfig->rowParam[] = "LONGTEXT";
		}
		parent::__construct($db, $dbConfig);
		
		// temp query to update new column 'country'
		// TODO: Delete update query after executed online 
		$db->Update(self::TABLE_NAME, Array("country"), Array("DE"), "country=''");
		
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int			Im Erfolgsfall true oder 
	 *					-1 Name leer
	 *					-2 Text leer
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if( trim($this->title)=="" )return -1;
		if( trim($this->country)=="" )return -1;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "country";
		$rowData[]= $this->country;
		$rowName[]= "title";
		$rowData[]= $this->title;
		$rowName[]= "textLeft";
		$rowData[]= $this->textLeft;
		$rowName[]= "numberOfTextBlocks";
		$rowData[]= $this->numberOfTextBlocks;
		
		for($a=0; $a<TextModule::MAX_NUMBER_OF_TEXT_BLOCKS; $a++)
		{
			$rowName[]= "textRight_".$a;
			$rowData[]= $this->textRight[$a];
		}
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array $data		Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->country = $data['country'];
		if (trim($this->country)=="") $this->country = "DE";
		$this->title = $data['title'];
		$this->textLeft = $data['textLeft'];
		$this->numberOfTextBlocks = $data['numberOfTextBlocks'];
		for($a=0; $a<TextModule::MAX_NUMBER_OF_TEXT_BLOCKS; $a++)
		{
			$this->textRight[$a] = $data["textRight_".$a];
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
		$this->undeletable=($deletable ? 0 : 1);
		return true;
	}
	
	/**
	 * Set the country
	 * @param string $country country code (ISO 3166)
	 * @return bool
	 */
	public function SetCountry($country)
	{
		if (strlen(trim($country))<2) return false;
		$this->country = strtoupper(trim($country));
		return true;
	}
	
	/**
	 * Get the country
	 * @return string country code (ISO 3166)
	 */
	public function GetCountry()
	{
		return $this->country;
	}
	
	/**
	 * Setzt die Überschrift
	 * @param string $title
	 * @return bool
	 */
	public function SetTitle($title)
	{
		$this->title=$title;
		return true;
	}
	
	/**
	 * Gibt die Überschrift zurück
	 * @return string
	 */
	public function GetTitle()
	{
		return $this->title;
	}

	/**
	 * Setzt den Text 'Standpunkt Name Eigentümer/Verwalter/ Anwalt'
	 * @param string $text
	 * @return bool
	 */
	public function SetTextLeft($text)
	{
		$this->textLeft=$text;
		return true;
	}
	
	/**
	 * Gibt den Text 'Standpunkt Name Eigentümer/Verwalter/ Anwalt' zurück
	 * @return string
	 */
	public function GetTextLeft()
	{
		return $this->textLeft;
	}
	
	/**
	 * Setzt die Anzahl an verschiedenene Textblöcken für 'Standpunkt Name Eigentümer/Verwalter/ Anwalt'
	 * @param int $numberOfTextBlocks
	 * @return bool
	 */
	public function SetNumberOfTextBlocks($numberOfTextBlocks)
	{
		if( !is_int($numberOfTextBlocks) || $numberOfTextBlocks<1 || $numberOfTextBlocks>self::MAX_NUMBER_OF_TEXT_BLOCKS )return false;
		$this->numberOfTextBlocks=$numberOfTextBlocks;
		return true;
	}
	
	/**
	 * Gibt die Anzahl an verschiedenene Textblöcken für 'Standpunkt Name Eigentümer/Verwalter/ Anwalt' zurück
	 * @return int
	 */
	public function GetNumberOfTextBlocks()
	{
		return $this->numberOfTextBlocks;
	}
	
	/**
	 * Setzt den Text 'Standpunkt Name Eigentümer/Verwalter/ Anwalt'
	 * @param int $index
	 * @param string $text
	 * @return bool
	 */
	public function SetTextRight($index, $text)
	{
		if( !is_int($index) || $index<0 || $index>=self::MAX_NUMBER_OF_TEXT_BLOCKS )return false;
		$this->textRight[$index]=$text;
		return true;
	}
	
	/**
	 * Gibt den Text 'Standpunkt Name Eigentümer/Verwalter/ Anwalt' zurück
	 * @param int $index
	 * @return string
	 */
	public function GetTextRight($index)
	{
		if( !is_int($index) || $index<0 || $index>=self::MAX_NUMBER_OF_TEXT_BLOCKS )return false;
		return $this->textRight[$index];
	}
	
}
?>