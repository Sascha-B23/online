<?php
/**
 * Diese Klasse repräsentiert ein Kommentar des Prozess-Workflow-Status
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class Comment extends DBEntry implements DependencyFileDescription
{
	/**
	 * Datenbanknamen
	 * @var string
	 */
	const TABLE_NAME = "comment";
	
	/**
	 * Reference-Counter
	 * @var int
	 */
	protected $referenceCounter=0;
	
	/**
	 * File relation manager
	 * @var FileRelationManager 
	 */
	protected $fileRelationManager = null;
	
	/**
	 * Zeit
	 * @var int
	 */
	protected $time = 0;
	
	/**
	 * Ist der Eintrag nur für FMS-Mitarbeiter einsehbar?
	 * @var int
	 */
	protected $intern = 0;

	/**
	 * Kommentar
	 * @var string
	 */
	protected $comment = "";

	/**
	 * Benutzer der dieses Kommentar verfasst hat
	 * @var User
	 */
	protected $user = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function Comment(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("referenceCounter", "time", "intern", "comment", "user");
		$dbConfig->rowParam = Array("INT", "BIGINT", "INT", "LONGTEXT", "BIGINT");
		$dbConfig->rowIndex = Array("user");
		parent::__construct($db, $dbConfig);
		
		$this->fileRelationManager = new FileRelationManager($db, $this);
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
		// remove all files of this comment
		$this->fileRelationManager->RemoveAllFiles($db);
		return true;
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1 Kein Kommentar eingetragen
	 *								-2 Kein Benutzer zugewiesen
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// EMail-Adresse gesetzt?
		if (trim($this->comment)=="") return -1;
		if ($this->user==null || $this->user->GetPKey()==-1) return -2;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "referenceCounter";
		$rowData[]= $this->referenceCounter;
		$rowName[]= "time";
		$rowData[]= $this->time;
		$rowName[]= "intern";
		$rowData[]= $this->intern;
		$rowName[]= "comment";
		$rowData[]= $this->comment;
		$rowName[]= "user";
		$rowData[]= $this->user==null ? -1 : $this->user->GetPKey();
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
		$this->referenceCounter = (int)$data['referenceCounter'];
		$this->time = $data['time'];
		$this->intern = $data['intern'];
		$this->comment = $data['comment'];
		if( $data['user']!=-1 )
		{
			$this->user = new User($db);
			if ($this->user->Load($data['user'], $db)!==true) $this->user = null;
		}
		else
		{
			$this->user=null;
		}
		return true;
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
	 * Setzt die Zeit
	 * @param int $time
	 * @return bool
	 */
	public function SetTime($time)
	{
		if (!is_int($time)) return false;
		$this->time = $time;
		return true;
	}
	
	/**
	 * Gibt die Zeit zurück
	 * @return int
	 */
	public function GetTime()
	{
		return $this->time;
	}
		
	/**
	 * Setzt, ob der Eintrag nur für FMS-Mitarbeiter einsehbar ist
	 * @param bool $intern
	 * @return bool
	 */
	public function SetIntern($intern)
	{
		if (!is_bool($intern)) return false;
		$this->intern = ($intern ? 1 : 0);
		return true;
	}
	
	/**
	 * Gibt zurück, ob der Eintrag nur für FMS-Mitarbeiter einsehbar ist
	 * @return bool		Intern
	 */
	public function GetIntern()
	{
		return ($this->intern==1 ? true : false);
	}
		
	/**
	 * Setzt Kommentar
	 * @param string $comment
	 * @return bool
	 */
	public function SetComment($comment)
	{
		$this->comment = $comment;
		return true;
	}
	
	/**
	 * Gibt Kommentar zurück
	 * @return string
	 */
	public function GetComment()
	{
		return $this->comment;
	}
	
	/**
	 * Setzt User
	 * @param User $user
	 * @return bool
	 */
	public function SetUser(User $user)
	{
		if ($user->GetPKey()==-1) return false;
		$this->user = $user;
		return true;
	}
	
	/**
	 * Gibt User zurück
	 * @return User
	 */
	public function GetUser()
	{
		return $this->user;
	}
	
	/**
	 * Gibt die Anzahl der zu diesem Comment hinterlegten Dateien zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetFileCount(DBManager $db)
	{
		return $this->fileRelationManager->GetFileCount($db);
	}

	/**
	 * Gibt alle zu diesem Comment hinterlegten Dateien zurück
	 * @param DBManager $db
	 * @return File[]
	 */
	public function GetFiles(DBManager $db)
	{
		return $this->fileRelationManager->GetFiles($db);
	}
		
	/**
	 * Fügt diesem Comment die übergebene Datei hinzu
	 * @param DBManager $db
	 * @param File $file
	 * @return bool
	 */
	public function AddFile(DBManager $db, File $file)
	{
		return $this->fileRelationManager->AddFile($db, $file);
	}
	
	/**
	 * Entfernt die übergebene Datei von diesem Comment. Falls die Datei 
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
	 * Return Description
	 */
	public function GetDependencyFileDescription()
	{
		// Prozessstatus
		/*$object = null;
		$data = $db->SelectAssoc("SELECT ".ProcessStatus::TABLE_NAME.".* FROM ".ProcessStatus::TABLE_NAME." LEFT JOIN ".strtolower(ProcessStatus::TABLE_NAME.CommentRelationManager::TABLE_NAME_POSTFIX)." ON ".ProcessStatus::TABLE_NAME.".pkey=".strtolower(ProcessStatus::TABLE_NAME.CommentRelationManager::TABLE_NAME_POSTFIX).".processstatus WHERE ".strtolower(ProcessStatus::TABLE_NAME.CommentRelationManager::TABLE_NAME_POSTFIX).".comment=".(int)$this->GetPKey());
		if (count($data)>0)
		{
			$object = new ProcessStatus($db);
			if ($object->LoadFromArray($data[0], $db)!==true) $object = null;
		}
		else
		{
			// Prozessstatusgruppe
			$data = $db->SelectAssoc("SELECT ".ProcessStatusGroup::TABLE_NAME.".* FROM ".ProcessStatusGroup::TABLE_NAME." LEFT JOIN ".strtolower(ProcessStatusGroup::TABLE_NAME.CommentRelationManager::TABLE_NAME_POSTFIX)." ON ".ProcessStatusGroup::TABLE_NAME.".pkey=".strtolower(ProcessStatusGroup::TABLE_NAME.CommentRelationManager::TABLE_NAME_POSTFIX).".processstatusgroup WHERE ".strtolower(ProcessStatusGroup::TABLE_NAME.CommentRelationManager::TABLE_NAME_POSTFIX).".comment=".(int)$this->GetPKey());
			if (count($data)>0)
			{
				$object = new ProcessStatusGroup($db);
				if ($object->LoadFromArray($data[0], $db)!==true) $object = null;
			}
		}*/
		return ($object!=null ? $object->GetDependencyFileDescription().DependencyFileDescription::SEPERATOR." " : "")."Kommentar";
	}
	
}
?>