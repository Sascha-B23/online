<?php
/**
 * Diese Klasse repräsentiert eine Datei
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class File extends DBEntry
{
	
	/**
	 * Zugriffsberechtigung
	 * @var enum
	 */
	const FM_FILE_INTERN_NO = 0;	// Für Alle 
	const FM_FILE_INTERN_YES = 1;	// Nur für FMS-Mitarbeiter
	
	/**
	 * Filetypes
	 * @var enum
	 */
	const FM_FILE_TYPE_UNKNOWN = 0;
	const FM_FILE_TYPE_PDF = 1;
	const FM_FILE_TYPE_RTF = 2;
	const FM_FILE_TYPE_EXCEL = 4;

	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "file";

	/**
	 * Dateiname
	 * @var string
	 */
	protected $fileName="";
	
	/**
	 * Dateityp
	 * @var int
	 */
	protected $fileType=self::FM_FILE_TYPE_UNKNOWN;
	
	/**
	 * Original-Dateiname
	 * @var string
	 */
	protected $originalFileName="";
	
	/**
	 * Dateibeschreibung
	 * @var string
	 */
	protected $description="";
	
	/**
	 * Dateipfad auf Server (relativ zum Dateiverzeichnis)
	 * @var string
	 */
	protected $documentPath="";
	
	/**
	 * Dateityp
	 * @var int
	 */
	protected $fileSemantic=FM_FILE_SEMANTIC_UNKNOWN;
	
	/**
	 * Reference-Counter
	 * @var int
	 */
	protected $referenceCounter=0;
	
	/**
	 * Ist die Datei nur für FMS-Mitarbeiter?
	 * @var int
	 */
	protected $intern = self::FM_FILE_INTERN_NO;
	
	/**
	 * Filesemantik spezifische Zusatzinformationenen
	 * @var string
	 */
	protected $fileSemanticSpecific = "";
	
	/**
	 * Constructor
	 * @param DBManager $db
	 */
	function File(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("fileName", "fileType", "originalFileName", "description", "documentPath", "fileSemantic", "referenceCounter", "intern", "fileSemanticSpecific");
		$dbConfig->rowParam = Array("TEXT", "INT", "TEXT", "LONGTEXT", "TEXT", "INT", "INT", "INT", "LONGTEXT");
		$dbConfig->rowIndex = Array();
		parent::__construct($db, $dbConfig);
	}	
	
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @param DBManager $db
	 * @return bool
	 * @access public
	 */
	public function IsDeletable(&$db)
	{
		if ($this->referenceCounter>0) return false;
		return parent::IsDeletable($db);
	}
	
	/**
	 * Is called when the file is going to be deleted
	 * @param DBManager $db
	 * @return bool
	 */
	protected function OnDeleteMe(&$db)
	{
		// Write action to log
		LoggingManager::GetInstance()->Log(new LoggingFileAccess(LoggingFileAccess::TYPE_DELETE, $this, $db));
		// Datei löschen
		@unlink( FM_FILE_ROOT.$this->GetDocumentPath() );
		return true;
	}
	
	/**
	 * Funciton is called when DBEntry is added new to db
	 * @param DBManager $db
	 */
	protected function OnCreated($db)
	{
		// Write action to log
		LoggingManager::GetInstance()->Log(new LoggingFileAccess(LoggingFileAccess::TYPE_CREATE, $this, $db));
	}
	
	/**
	 * Funciton is called when DBEntry is updated in db
	 * @param DBManager $db
	 */
	protected function OnChanged($db)
	{
		LoggingManager::GetInstance()->Log(new LoggingFileAccess(LoggingFileAccess::TYPE_CHANGED, $this, $db));
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param DBManager $db
	 * @param &array $rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array $rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int			Im Erfolgsfall true oder 
	 *								-1 Kein Verweis auf Datei enthalten
	 *								-2 Kein Dateiname gesetzt
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// EMail-Adresse gesetzt?
		if ($this->documentPath=="") return -1;
		if (trim($this->fileName)=="") return -2;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "fileName";
		$rowData[]= $this->fileName;
		$rowName[]= "fileType";
		$rowData[]= $this->fileType;
		$rowName[]= "originalFileName";
		$rowData[]= $this->originalFileName;
		$rowName[]= "description";
		$rowData[]= $this->description;
		$rowName[]= "documentPath";
		$rowData[]= $this->documentPath;
		$rowName[]= "fileSemantic";
		$rowData[]= $this->fileSemantic;
		$rowName[]= "referenceCounter";
		$rowData[]= $this->referenceCounter;
		$rowName[]= "intern";
		$rowData[]= $this->intern;
		$rowName[]= "fileSemanticSpecific";
		$rowData[]= $this->fileSemanticSpecific;
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param DBManager $db
	 * @param array $data
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->fileName = $data['fileName'];
		$this->fileType = (int)$data['fileType'];
		$this->originalFileName = $data['originalFileName'];
		$this->description = $data['description'];
		$this->documentPath = $data['documentPath'];
		$this->fileSemantic = (int)$data['fileSemantic'];
		$this->referenceCounter = (int)$data['referenceCounter'];
		$this->intern = (int)$data['intern'];
		$this->fileSemanticSpecific = $data['fileSemanticSpecific'];
		return true;
	}
	
	/**
	 * Setzt den Dateinamen
	 * @param string $fileName
	 * @return bool
	 */
	public function SetFileName($fileName)
	{
		$this->fileName = $fileName;
		return true;
	}
	
	/**
	 * Gibt den Dateinamen zurück
	 * @return string
	 */
	public function GetFileName()
	{
		return $this->fileName;
	}
	
	/**
	 * Setzt den Dateityp
	 * @param int $fileType
	 * @return bool
	 */
	public function SetFileType($fileType)
	{
		if (!is_int($fileType)) return false;
		$this->fileType = $fileType;
		return true;
	}
	
	/**
	 * Gibt den Dateityp zurück
	 * @return int
	 */
	public function GetFileType()
	{
		return $this->fileType;
	}
	
	/**
	 * return the file extension
	 * @return string 
	 */
	public function GetFileTypeExtension()
	{
		$info = pathinfo($this->fileName);
		return strtolower($info['extension']);
	}
	
	/**
	 * Setzt den Original-Dateinamen
	 * @param string $originalFileName
	 * @return bool
	 */
	public function SetOriginalFileName($originalFileName)
	{
		$this->originalFileName = $originalFileName;
		return true;
	}
	
	/**
	 * Gibt den Original-Dateinamen zurück
	 * @return string
	 */
	public function GetOriginalFileName()
	{
		return $this->originalFileName;
	}
		
	/**
	 * Setzt die Dateibeschreibung
	 * @param string $description
	 * @return bool
	 */
	public function SetDescription($description)
	{
		$this->description = $description;
		return true;
	}
	
	/**
	 * Gibt die Dateibeschreibung zurück
	 * @return string
	 */
	public function GetDescription()
	{
		return $this->description;
	}
		
	/**
	 * Setzt den Dateipfad auf Server (relativ zum Dateiverzeichnis)
	 * @param string $documentPath
	 * @return bool
	 */
	public function SetDocumentPath($documentPath)
	{
		if (!file_exists(FM_FILE_ROOT.$documentPath) || !is_file(FM_FILE_ROOT.$documentPath)) return false;
		$this->documentPath=$documentPath;
		return true;
	}
	
	/**
	 * Gibt den Dateipfad auf Server (relativ zum Dateiverzeichnis) zurück
	 * @return string
	 */
	public function GetDocumentPath()
	{
		return $this->documentPath;
	}
	
	/**
	 * Setzt die FileSemantic
	 * @param int $fileSemantic
	 * @return bool
	 */
	public function SetFileSemantic($fileSemantic)
	{
		if (!is_int($fileSemantic)) return false;
		$this->fileSemantic = $fileSemantic;
		return true;
	}
	
	/**
	 * Gibt die FileSemantic zurück
	 * @return int
	 */
	public function GetFileSemantic()
	{
		return $this->fileSemantic;
	}
	
	/**
	 * Erhöht den Referenzzähler um eins
	 */
	public function AddReference()
	{
		$this->referenceCounter++;
	}
	
	/**
	 * Veringert den Referenzzähler um eins
	 */
	public function RemoveReference()
	{
		$this->referenceCounter--;
	}
	
	/**
	 * Gibt den Wert des Referenzzähler zurück
	 * @return int
	 */
	public function GetReferenceCounter()
	{
		return $this->referenceCounter;
	}
	
	/**
	 * Setzt, ob die Datei nur für FMS-Mitarbeiter ist oder für alle
	 * @param bool $intern
	 * @return bool
	 */
	public function SetIntern($intern)
	{
		if (!is_bool($intern)) return false;
		$this->intern=($intern ? self::FM_FILE_INTERN_YES : self::FM_FILE_INTERN_NO);
		return true;
	}
	
	/**
	 * Gibt zurück, ob die Datei nur für FMS-Mitarbeiter ist oder für alle
	 * @return bool
	 */
	public function GetIntern()
	{
		return ($this->intern==self::FM_FILE_INTERN_YES ? true : false);
	}
	
	/**
	 * Setzt Filesemantik spezifische Zusatzinformationenen
	 * @param string $fileSemanticSpecific
	 * @return bool
	 */
	public function SetFileSemanticSpecificString($fileSemanticSpecific)
	{
		$this->fileSemanticSpecific = $fileSemanticSpecific;
		return true;
	}
	
	/**
	 * Gibt Filesemantik spezifische Zusatzinformationenen zurück
	 * @return string
	 */
	public function GetFileSemanticSpecificString()
	{
		return $this->fileSemanticSpecific;
	}
	
	public function GetDependencyStrings(DBManager $db)
	{
		$returnStrings = Array();
		$objects = $this->FindDependencyObjects($db);
		foreach ($objects as $value)
		{
			$returnStrings[] = $value["desc"];
		}
		return $returnStrings;
	}
	
	/**
	 * Find dependecies of this file 
	 * @param DBManager $db
	 * @return array
	 */
	public function FindDependencyObjects(DBManager $db)
	{
		if ($this->GetPKey()==-1) return null;
		$objects = Array();
		$errors = Array();
		// Antwortschrieben
		$data = $db->SelectAssoc("SELECT ".Antwortschreiben::TABLE_NAME.".* FROM ".Antwortschreiben::TABLE_NAME." LEFT JOIN ".(Antwortschreiben::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX)." ON ".Antwortschreiben::TABLE_NAME.".pkey=".(Antwortschreiben::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".antwortschreiben WHERE ".(Antwortschreiben::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".file=".(int)$this->GetPKey());
		$objects = array_merge($objects, $this->LoadDependenciesFromArray($db, $data, new Antwortschreiben($db), $errors));
			
		// Contracts
		$data = $db->SelectAssoc("SELECT ".Contract::TABLE_NAME.".* FROM ".Contract::TABLE_NAME." LEFT JOIN ".(Contract::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX)." ON ".Contract::TABLE_NAME.".pkey=".(Contract::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".contract WHERE ".(Contract::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".file=".(int)$this->GetPKey());
		$objects = array_merge($objects, $this->LoadDependenciesFromArray($db, $data, new Contract($db), $errors));
		
		// Anschreiben-Vorlage
		//$data = $db->SelectAssoc("SELECT * FROM ".CCompany::TABLE_NAME." WHERE anschreiben=".(int)$this->GetPKey());
		//$objects = array_merge($objects, $this->LoadDependenciesFromArray($db, $data, new CCompany($db), $errors));
		
		// Prozessstatus
		$data = $db->SelectAssoc("SELECT ".ProcessStatus::TABLE_NAME.".* FROM ".ProcessStatus::TABLE_NAME." LEFT JOIN ".strtolower(ProcessStatus::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX)." ON ".ProcessStatus::TABLE_NAME.".pkey=".strtolower(ProcessStatus::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".processstatus WHERE ".strtolower(ProcessStatus::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".file=".(int)$this->GetPKey());
		$objects = array_merge($objects, $this->LoadDependenciesFromArray($db, $data, new ProcessStatus($db), $errors));

		// Prozessstatusgruppe
		$data = $db->SelectAssoc("SELECT ".ProcessStatusGroup::TABLE_NAME.".* FROM ".ProcessStatusGroup::TABLE_NAME." LEFT JOIN ".strtolower(ProcessStatusGroup::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX)." ON ".ProcessStatusGroup::TABLE_NAME.".pkey=".strtolower(ProcessStatusGroup::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".processstatusgroup WHERE ".strtolower(ProcessStatusGroup::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".file=".(int)$this->GetPKey());
		$objects = array_merge($objects, $this->LoadDependenciesFromArray($db, $data, new ProcessStatusGroup($db), $errors));
		
		// Prozessstatus comment
		$data = $db->SelectAssoc("SELECT ".Comment::TABLE_NAME.".* FROM  ".Comment::TABLE_NAME." LEFT JOIN ".strtolower(Comment::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX)." ON ".Comment::TABLE_NAME.".pkey=".strtolower(Comment::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".comment WHERE ".strtolower(Comment::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".file=".(int)$this->GetPKey());
		$objects = array_merge($objects, $this->LoadDependenciesFromArray($db, $data, new Comment($db), $errors));
		
		// Teilabrechnung
		$data = $db->SelectAssoc("SELECT ".Teilabrechnung::TABLE_NAME.".* FROM ".Teilabrechnung::TABLE_NAME." LEFT JOIN ".(Teilabrechnung::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX)." ON ".Teilabrechnung::TABLE_NAME.".pkey=".(Teilabrechnung::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".teilabrechnung WHERE ".(Teilabrechnung::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".file=".(int)$this->GetPKey());
		$objects = array_merge($objects, $this->LoadDependenciesFromArray($db, $data, new Teilabrechnung($db), $errors));
		
		// Widerspruch
		$data = $db->SelectAssoc("SELECT ".Widerspruch::TABLE_NAME.".* FROM ".Widerspruch::TABLE_NAME." LEFT JOIN ".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX)." ON ".Widerspruch::TABLE_NAME.".pkey=".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".widerspruch WHERE ".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".file=".(int)$this->GetPKey());
		$objects = array_merge($objects, $this->LoadDependenciesFromArray($db, $data, new Widerspruch($db), $errors));
		
		return $objects;
	}
	
	/**
	 * Load all passed data objects
	 * @param array $data
	 * @param DBEntry $emptyObject
	 * @param string[] $errors
	 * @return DBEntry[]
	 */
	private function LoadDependenciesFromArray(DBManager $db, $data, DBEntry $emptyObject, &$errors)
	{
		global $FM_DESCRIPTIONS_FOR_SEMANTIC;
		$objects = Array();
		foreach ($data as $row)
		{
			$object = clone $emptyObject;
			if ($object->LoadFromArray($row, $db)===true)
			{
				$desc = $this->GetFileSemanticDescription();
				$objects[] = Array("desc" => $object->GetDependencyFileDescription().DependencyFileDescription::SEPERATOR." ".$this->GetFileName()." [".$desc['short']."|".$desc['long']."]", "obj" => $object);
			}
			else
			{
				$errors[] = "Can't load data with pkey '".$row['pkey']."' into class '".get_class($emptyObject)."'";
			}
		}
		return $objects;
	}
	
	/**
	 * Return the description of the file semantic
	 * @global type $FM_DESCRIPTIONS_FOR_SEMANTIC
	 * @return array
	 */
	public function GetFileSemanticDescription()
	{
		global $FM_DESCRIPTIONS_FOR_SEMANTIC;
		$short = $FM_DESCRIPTIONS_FOR_SEMANTIC[$this->GetFileSemantic()]['short'];
		$long = str_replace('%SUB%', FileManager::GetRSFileType($this->GetFileSemanticSpecificString()),$FM_DESCRIPTIONS_FOR_SEMANTIC[$this->GetFileSemantic()]['long']);
		return Array("short" => $short, "long" => $long);
	}
	
}
?>