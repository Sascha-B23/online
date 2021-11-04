<?php
/**
 * Diese Klasse verwaltet Beziehungen zwischen Objekten der File-Klasse und den Objekten einer anderen Klasse die von DBEntry erbt
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class FileRelationManager
{
	/**
	 * Tablename
	 * @var string 
	 */
	const TABLE_NAME_POSTFIX = "_file";
	
	/**
	 * Column name of the target object
	 * @var string 
	 */
	protected $targetObjectColumnName = "";
	
	/**
	 * Courrent database configuration
	 * @var DBConfig 
	 */
	protected $dbConfig = null;
	
	/**
	 * Object to which the relation is bound
	 * @var DBEntry
	 */
	protected $targetObject = null;
	
	/**
	 * Constructor
	 * @param DBManager $db 
	 */
	public function FileRelationManager(DBManager $db, DBEntry $targetObject)
	{
		$this->targetObject = $targetObject;
		$this->targetObjectColumnName = trim(strtolower(get_class($this->targetObject)));
		if (trim($this->targetObjectColumnName)=="") die("Table Prefix not defined!");
		
		$this->dbConfig = new DBConfig();
		$this->dbConfig->tableName = $this->targetObjectColumnName.self::TABLE_NAME_POSTFIX;
		$this->dbConfig->rowName = Array($this->targetObjectColumnName, "file");
		$this->dbConfig->rowParam = Array("BIGINT", "BIGINT");
		$this->dbConfig->rowIndex = Array($this->targetObjectColumnName, "file");
		$db->CreateOrUpdateTable($this->dbConfig);
	}
	
	/**
	 * Gibt die Anzahl der zu diesem Prozess hinterlegten Dateien mit der übergebenen Semantik zurück
	 * @param DBManager $db
	 * @param int $fileSemantic
	 * @param string $additionalWhereClause
	 * @return int
	 */
	public function GetFileCount(DBManager $db, $fileSemantic=FM_FILE_SEMANTIC_UNKNOWN, $additionalWhereClause="")
	{
		if ($this->targetObject->GetPKey()==-1 || !is_int($fileSemantic)) return 0;
		if ($additionalWhereClause!="") $additionalWhereClause=" AND (".$additionalWhereClause.") ";
		if ($fileSemantic!=FM_FILE_SEMANTIC_UNKNOWN)
		{
			// Nur bestimmte Semantik zählen
			$data = $db->SelectAssoc("SELECT count(".$this->dbConfig->tableName.".pkey) as count FROM ".$this->dbConfig->tableName." LEFT JOIN ".File::TABLE_NAME." ON ".$this->dbConfig->tableName.".file=".File::TABLE_NAME.".pkey WHERE ".$this->dbConfig->tableName.".".$this->targetObjectColumnName."=".$this->targetObject->GetPKey()." AND ".File::TABLE_NAME.".fileSemantic=".$fileSemantic." ".$additionalWhereClause );
		}
		else
		{
			// Alle zählen
			$data = $db->SelectAssoc("SELECT count(".$this->dbConfig->tableName.".pkey) as count FROM ".$this->dbConfig->tableName." LEFT JOIN ".File::TABLE_NAME." ON ".$this->dbConfig->tableName.".file=".File::TABLE_NAME.".pkey WHERE ".$this->dbConfig->tableName.".".$this->targetObjectColumnName."=".$this->targetObject->GetPKey()." ".$additionalWhereClause );
		}
		return (int)$data[0]["count"];
	}
	
	/**
	 * Gibt alle zu diesem Prozess hinterlegten Dateien mit der übergebenen Semantik zurück
	 * @param DBManager $db
	 * @param int $fileSemantic
	 * @param string $additionalWhereClause
	 * @return File[]
	 */
	public function GetFiles(DBManager $db, $fileSemantic=FM_FILE_SEMANTIC_UNKNOWN, $additionalWhereClause="")
	{
		if ($this->targetObject->GetPKey()==-1 || !is_int($fileSemantic)) return Array();
		if ($additionalWhereClause!="") $additionalWhereClause=" AND (".$additionalWhereClause.") ";
		if ($fileSemantic!=FM_FILE_SEMANTIC_UNKNOWN)
		{
			// Nur bestimmte Semantik zurückgeben
			$data=$db->SelectAssoc("SELECT ".$this->dbConfig->tableName.".file FROM ".$this->dbConfig->tableName." LEFT JOIN ".File::TABLE_NAME." ON ".$this->dbConfig->tableName.".file=".File::TABLE_NAME.".pkey WHERE ".$this->targetObjectColumnName."=".$this->targetObject->GetPKey()." AND ".File::TABLE_NAME.".fileSemantic=".$fileSemantic." ".$additionalWhereClause." ORDER BY ".$this->dbConfig->tableName.".pkey");
		}
		else
		{
			// Alle zurückgeben
			$data=$db->SelectAssoc("SELECT ".$this->dbConfig->tableName.".file FROM ".$this->dbConfig->tableName." LEFT JOIN ".File::TABLE_NAME." ON ".$this->dbConfig->tableName.".file=".File::TABLE_NAME.".pkey WHERE ".$this->targetObjectColumnName."=".$this->targetObject->GetPKey()." ".$additionalWhereClause." ORDER BY ".$this->dbConfig->tableName.".pkey");
		}
		$objects=Array();
		for($a=0; $a<count($data); $a++)
		{
			$object=new File($db);
			if ($object->Load($data[$a]["file"], $db)===true) $objects[]=$object;
		}
		return $objects;
	}
		
	/**
	* Fügt diesem Prozess die übergebene Datei hinzu
	* @param DBManager $db
	* @param File $file
	* @return bool
	*/
	public function AddFile(DBManager $db, File $file)
	{
		if ($this->targetObject->GetPKey()==-1 || $file->GetPKey()==-1) return false;
		// Prüfen, ob Datei bereits diesem Prozess hinzugefügt ist
		$data = $db->SelectAssoc("SELECT pkey FROM ".$this->dbConfig->tableName." WHERE ".$this->targetObjectColumnName."=".$this->targetObject->GetPKey()." AND file=".$file->GetPKey() );
		if (count($data)!=0) return true;
		// Datei diesem Prozess hinzufügen		
		$db->Insert($this->dbConfig->tableName, $this->dbConfig->rowName, Array($this->targetObject->GetPKey(), $file->GetPKey()) );
		// Referenzzähler der Datei erhöhen
		$file->AddReference();
		$file->Store($db);
		// Write action to log
		LoggingManager::GetInstance()->Log(new LoggingFileAccess(LoggingFileAccess::TYPE_ADD, $file, $db));
		return true;
	}
	
	/**
	 * Entfernt die übergebene Datei von diesem Prozess. Falls die Datei 
	 * sonst nirgends verwendet wird, wird diese gleich gelöscht.
	 * @param DBManager $db
	 * @param File $file
	 * @return bool
	 */
	public function RemoveFile(DBManager $db, File $file)
	{
		if ($this->targetObject->GetPKey()==-1 || $file->GetPKey()==-1) return false;
		// Prüfen, ob Datei diesem Prozess zugeordnet ist
		$data = $db->SelectAssoc( "SELECT pkey FROM ".$this->dbConfig->tableName." WHERE ".$this->targetObjectColumnName."=".$this->targetObject->GetPKey()." AND file=".$file->GetPKey() );
		if (count($data)==0) return true;
		// Write action to log
		LoggingManager::GetInstance()->Log(new LoggingFileAccess(LoggingFileAccess::TYPE_REMOVE, $file, $db));
		// Datei von diesem Prozess entfernen
		$db->Query( "DELETE FROM ".$this->dbConfig->tableName." WHERE ".$this->targetObjectColumnName."=".$this->targetObject->GetPKey()." AND file=".$file->GetPKey() );
		// Referenzzähler der Datei verringern
		$file->RemoveReference();
		if ($file->GetReferenceCounter()>0)
		{
			$file->Store($db);
		}
		else
		{
			// Wenn der Referenzzähler Null erreicht hat, Datei löschen
			$file->DeleteMe($db);
		}
		return true;
	}
	
	/**
	 * Entfernt alle Datei von dieser Prozess. Falls die Dateien 
	 * sonst nirgends verwendet werden, werden diese gleich gelöscht.
	 * @param DBManager $db
	 * @return bool
	 */
	public function RemoveAllFiles(DBManager $db)
	{
		if ($this->targetObject->GetPKey()==-1) return false;
		// Alle Dateien des Contracts ermitteln
		$data=$db->SelectAssoc( "SELECT file FROM ".$this->dbConfig->tableName." WHERE ".$this->targetObjectColumnName."=".$this->targetObject->GetPKey() );
		$objects = Array();
		for ($a=0; $a<count($data); $a++)
		{
			$object=new File($db);
			if ($object->Load($data[$a]["file"], $db)===true) $objects[]=$object;
		}
		// Alle Dateien löschen
		for($a=0; $a<count($objects); $a++)
		{
			$this->RemoveFile($db, $objects[$a]);
		}
		return true;
	}
	
}

?>
