<?php
include 'Comment.lib.php5';

/**
 * Diese Klasse verwaltet Beziehungen zwischen Objekten der Comment-Klasse und den Objekten einer anderen Klasse die von DBEntry erbt
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class CommentRelationManager
{
	/**
	 * Tablename
	 * @var string 
	 */
	const TABLE_NAME_POSTFIX = "_comment";
	
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
	 * @param DBEntry $targetObject
	 */
	public function CommentRelationManager(DBManager $db, DBEntry $targetObject)
	{
		$this->targetObject = $targetObject;
		$this->targetObjectColumnName = trim(strtolower(get_class($this->targetObject)));
		if (trim($this->targetObjectColumnName)=="") die("Table Prefix not defined!");
		
		$this->dbConfig = new DBConfig();
		$this->dbConfig->tableName = $this->targetObjectColumnName.self::TABLE_NAME_POSTFIX;
		$this->dbConfig->rowName = Array($this->targetObjectColumnName, "comment");
		$this->dbConfig->rowParam = Array("BIGINT", "BIGINT");
		$this->dbConfig->rowIndex = Array($this->targetObjectColumnName, "comment");
		
		new Comment($db);
		$db->CreateOrUpdateTable($this->dbConfig);
	}
	
	/**
	 * Gibt die Anzahl der zum TargetObject hinterlegten Kommentare zurück
	 * @param DBManager $db
	 * @param User $user
	 * @param string $additionalWhereClause
	 * @return int
	 */
	public function GetCommentCount(DBManager $db, User $user, $additionalWhereClause="")
	{
		if ($this->targetObject->GetPKey()==-1 ) return 0;
		if ($additionalWhereClause!="") $additionalWhereClause=" AND (".$additionalWhereClause.") ";
		// Bei nicht FMS-Mitarbeiter die interen Kommentare herausfiltern
		$groupBasetype = $user->GetGroupBasetype($db);
		$addQuery = "";
		if ($groupBasetype<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP) $addQuery=" AND intern=0 ";
		$data = $db->SelectAssoc("SELECT count(".$this->dbConfig->tableName.".pkey) as count FROM ".$this->dbConfig->tableName." LEFT JOIN ".Comment::TABLE_NAME." ON ".$this->dbConfig->tableName.".comment=".Comment::TABLE_NAME.".pkey WHERE ".$this->dbConfig->tableName.".".$this->targetObjectColumnName."=".$this->targetObject->GetPKey()." ".$addQuery." ".$additionalWhereClause );
		return (int)$data[0]["count"];
	}
	
	/**
	 * Gibt alle zum TargetObject hinterlegten Kommentare zurück
	 * @param DBManager $db
	 * @param User $user
	 * @param string $additionalWhereClause
	 * @return Comment[]
	 */
	public function GetComments(DBManager $db, User $user, $additionalWhereClause="")
	{
		if ($this->targetObject->GetPKey()==-1 ) return Array();
		if ($additionalWhereClause!="") $additionalWhereClause=" AND (".$additionalWhereClause.") ";
		// Bei nicht FMS-Mitarbeiter die interen Kommentare herausfiltern
		$groupBasetype = $user->GetGroupBasetype($db);
		$addQuery = "";
		if ($groupBasetype<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP) $addQuery=" AND intern=0 ";
		$data=$db->SelectAssoc("SELECT ".$this->dbConfig->tableName.".comment FROM ".$this->dbConfig->tableName." LEFT JOIN ".Comment::TABLE_NAME." ON ".$this->dbConfig->tableName.".comment=".Comment::TABLE_NAME.".pkey WHERE ".$this->targetObjectColumnName."=".$this->targetObject->GetPKey()." ".$addQuery." ".$additionalWhereClause." ORDER BY ".$this->dbConfig->tableName.".pkey");
		
		$objects=Array();
		for($a=0; $a<count($data); $a++)
		{
			$object=new Comment($db);
			if ($object->Load($data[$a]["comment"], $db)===true) $objects[]=$object;
		}
		return $objects;
	}
		
	/**
	* Fügt dem TargetObject das übergebene Kommentar hinzu
	* @param DBManager $db
	* @param Comment $comment
	* @return bool
	*/
	public function AddComment(DBManager $db, Comment $comment)
	{
		if ($this->targetObject->GetPKey()==-1 || $comment->GetPKey()==-1) return false;
		// Prüfen, ob das Kommentar bereits diesem TargetObject hinzugefügt ist
		$data = $db->SelectAssoc("SELECT pkey FROM ".$this->dbConfig->tableName." WHERE ".$this->targetObjectColumnName."=".$this->targetObject->GetPKey()." AND comment=".$comment->GetPKey() );
		if (count($data)!=0) return true;
		// Kommentar diesem TargetObject hinzufügen		
		$db->Insert($this->dbConfig->tableName, $this->dbConfig->rowName, Array($this->targetObject->GetPKey(), $comment->GetPKey()) );
		// Referenzzähler des Kommentars erhöhen
		$comment->AddReference();
		$comment->Store($db);
		return true;
	}
	
	/**
	 * Entfernt das übergebene Kommentar von dem TargetObject. Falls das Kommentar
	 * sonst nirgends verwendet wird, wird es gleich gelöscht.
	 * @param DBManager $db
	 * @param Comment $comment
	 * @return bool
	 */
	public function RemoveComment(DBManager $db, Comment $comment)
	{
		if ($this->targetObject->GetPKey()==-1 || $comment->GetPKey()==-1) return false;
		// Prüfen, ob das Kommentar diesem TargetObject zugeordnet ist
		$data = $db->SelectAssoc("SELECT pkey FROM ".$this->dbConfig->tableName." WHERE ".$this->targetObjectColumnName."=".$this->targetObject->GetPKey()." AND comment=".$comment->GetPKey() );
		if (count($data)==0) return true;
		// Kommentar von diesem TargetObject entfernen
		$db->Query("DELETE FROM ".$this->dbConfig->tableName." WHERE ".$this->targetObjectColumnName."=".$this->targetObject->GetPKey()." AND comment=".$comment->GetPKey());
		// Referenzzähler des Kommentars verringern
		$comment->RemoveReference();
		if ($comment->GetReferenceCounter()>0)
		{
			$comment->Store($db);
		}
		else
		{
			// Wenn der Referenzzähler Null erreicht hat, Kommentar löschen
			$comment->DeleteMe($db);
		}
		return true;
	}
	
	/**
	 * Entfernt alle Kommentare vom TargetObject. Falls die Kommentare 
	 * sonst nirgends verwendet werden, werden diese gleich gelöscht.
	 * @param DBManager $db
	 * @return bool
	 */
	public function RemoveAllComments(DBManager $db)
	{
		if ($this->targetObject->GetPKey()==-1) return false;
		// Alle Kommentare des TargetObjects ermitteln
		$data=$db->SelectAssoc( "SELECT comment FROM ".$this->dbConfig->tableName." WHERE ".$this->targetObjectColumnName."=".$this->targetObject->GetPKey() );
		$objects = Array();
		for ($a=0; $a<count($data); $a++)
		{
			$object=new Comment($db);
			if ($object->Load($data[$a]["comment"], $db)===true) $objects[]=$object;
		}
		// Alle Kommentare löschen
		for($a=0; $a<count($objects); $a++)
		{
			$this->RemoveComment($db, $objects[$a]);
		}
		return true;
	}
	
}

?>
