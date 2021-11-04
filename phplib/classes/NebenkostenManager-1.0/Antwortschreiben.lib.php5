<?php
/**
 * Diese Klasse repräsentiert ein Antwortschrieben
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class Antwortschreiben extends DBEntry implements DependencyFileDescription
{
	/**
	 * Datenbanknamen
	 * @var string
	 */
	const TABLE_NAME = "antwortschreiben";
	
	/**
	 * Nummer
	 * @var int
	 */
	protected $nummer = -1;
	
	/**
	 * Datum 
	 * @var int
	 */
	protected $datum = 0;
	
	/**
	 * File relation manager
	 * @var FileRelationManager 
	 */
	protected $fileRelationManager = null;
	
	/**
	 * Zugehöriger Widerspruch
	 * @var Widerspruch
	 */
	protected $widerspruch = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function Antwortschreiben(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("nummer", "datum", "widerspruch");
		$dbConfig->rowParam = Array("BIGINT", "BIGINT", "BIGINT");
		$dbConfig->rowIndex = Array("widerspruch", "nummer");
		parent::__construct($db, $dbConfig);
		
		$this->fileRelationManager = new FileRelationManager($db, $this);
	}
	
	/**
	 * Delete this and all contained objects
	 * @param DBManager $db
	 * @param User $user 
	 */
	public function DeleteRecursive(DBManager $db, User $user)
	{
		if ($user->GetGroupBasetype($db) < UM_GROUP_BASETYPE_ADMINISTRATOR) return false;
		// delete all files...
		$files = $this->fileRelationManager->GetFiles($db);
		for ($a=0; $a<count($files); $a++)
		{
			$this->RemoveFile($db, $files[$a]);
		}
		$this->DeleteMe($db);
		return true;
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
		// Array mit zu speichernden Daten anlegen
		if ($this->widerspruch==null) return -1;
		if ($this->nummer==-1)
		{
			// Wenn die Nummer noch nicht gesetzt wurde, dies jetzt nachholen
			$data=$db->SelectAssoc("SELECT count(pkey) as count FROM ".self::TABLE_NAME." WHERE widerspruch=".$this->widerspruch->GetPKey() );
			$this->nummer=((int)$data[0]["count"])+1;
		}
		$rowName[]= "nummer";
		$rowData[]= $this->nummer;
		$rowName[]= "datum";
		$rowData[]= $this->datum;
		$rowName[]= "widerspruch";
		$rowData[]= $this->widerspruch==null ? -1 : $this->widerspruch->GetPKey();
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
		$this->datum = $data['datum'];
		if ($data['widerspruch']!=-1)
		{
			$this->widerspruch = new Widerspruch($db);
			if ($this->widerspruch->Load($data['widerspruch'], $db)!==true) $this->widerspruch=null;
		}
		else
		{
			$this->widerspruch=null;
		}
		return true;
	}

	/**
	 * Return Description
	 */
	public function GetDependencyFileDescription()
	{
		$ws = $this->GetWiderspruch();
		return ($ws==null ? "?" : $ws->GetDependencyFileDescription()).DependencyFileDescription::SEPERATOR."AW Nr. ".$this->GetNummer()." vom ".date("d.m.Y", $this->GetDatum());
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
	 * Setzt das Datum
	 * @param int $datum
	 * @return bool
	 */
	public function SetDatum($datum)
	{
		if (!is_int($datum)) return false;
		$this->datum = $datum;
		return true;
	}
	
	/**
	 * Gibt das Datum zurück
	 * @return int
	 */
	public function GetDatum()
	{
		return $this->datum;
	}
	
	/**
	 * Gibt die Anzahl der zu diesem Antwortschreiben hinterlegten Dateien zurück
	 * @param DBManager $db
	 * @param int $fileSemantic
	 * @return int
	 */
	public function GetFileCount(DBManager $db, $fileSemantic=FM_FILE_SEMANTIC_UNKNOWN)
	{
		return $this->fileRelationManager->GetFileCount($db, $fileSemantic);
	}

	/**
	 * Gibt alle zu diesem Antwortschreiben hinterlegten Dateien zurück
	 * @param DBManager $db
	 * @param int $fileSemantic	Semantik
	 * @return array Zugeordneten Dateien
	 */
	public function GetFiles(DBManager $db, $fileSemantic=FM_FILE_SEMANTIC_UNKNOWN)
	{
		return $this->fileRelationManager->GetFiles($db, $fileSemantic);
	}
		
	/**
	 * Fügt diesem Antwortschreiben die übergebene Datei hinzu
	 * @param DBManager $db
	 * @param File $file
	 * @return bool
	 */
	public function AddFile(DBManager $db, File $file)
	{
		return $this->fileRelationManager->AddFile($db, $file);
	}
	
	/**
	 * Entfernt die übergebene Datei von diesem Antwortschreiben. Falls die Datei 
	 * sonst nirgends verwendet wird, wird diese gleich gelöscht.
	 * @param DBManager $db
	 * @param File $file
	 * @return bool
	 */
	public function RemoveFile(DBManager $db, File $file)
	{
		return $this->fileRelationManager->RemoveFile($db, $file);
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