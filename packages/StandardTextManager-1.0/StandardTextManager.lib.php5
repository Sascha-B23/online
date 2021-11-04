<?php
include_once __DIR__.'/StandardText.lib.php5';

/**
 * Verwaltungsklasse für StandardText-Daten
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class StandardTextManager extends SearchDBEntry 
{
	
	/**
	 * enums Textvorlagen
	 */
	const STM_TERMINEMAIL = 1;
	const STM_TERMINEMAIL_SUBJECT = 2;
	const STM_INFORMCUSTOMER = 3;
	const STM_INFORMCUSTOMER_SUBJECT = 4;
	
	const STM_ADDRESSDATA_TITLE_MR = 5;
	const STM_ADDRESSDATA_TITLE_MS = 6;
	
	const STM_ADRESSDATA_PHRASE1_MALE = 7;
	const STM_ADRESSDATA_PHRASE1_FEMALE = 8;
	const STM_ADRESSDATA_PHRASE1_COMPANY = 11;
	
	const STM_ADRESSDATA_PHRASE2_MALE = 9;
	const STM_ADRESSDATA_PHRASE2_FEMALE = 10;
	const STM_ADRESSDATA_PHRASE2_COMPANY = 12;
		
	const STM_DATE = 13;
	const STM_TIME = 14;
	
	const STM_WSANSCHREIBEN = 15;
	const STM_WSANSCHREIBEN_SUBJECT = 16;

	
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * Dummyobjekt
	 * @var StandardText 
	 */
	protected $standardTextDummy=null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function StandardTextManager(DBManager $db)
	{
		$this->db = $db;
		$this->standardTextDummy = new StandardText($this->db);
	}
	
	/**
	 * Gibt die Anzahl der Textvorlagen zurück
	 * @var string $searchString 
	 * @return int
	 */
	public function GetStandardTextCount($searchString="", $type=StandardText::ST_TYPE_TEMPLATE)
	{
		$whereClause="type=".$type;
		$joinClause = "";
		$rowsToUse = Array(Array("tableName" => StandardText::TABLE_NAME, "tableRowNames" => $this->standardTextDummy->GetTableConfig()->rowName));
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		return $this->GetDBEntryCount($searchString, StandardText::TABLE_NAME, $allRowNames, $whereClause, $joinClause);
	}
		
	/**
	 * Gibt die Textvorlagen entsprechend den übergebenen Parametern zurück
	 * @param string	$searchString		Suchstring
	 * @param string	$orderBy			Spaltenname nach dem sortiert werden soll
	 * @param string	$orderDirection		Sortierrichtung (ASC oder DESC)
	 * @param string	$currentPage		Aktuelle Seite
	 * @param string	$numEntrysPerPage	Anzhal der Einträge je Seite
	 * @return StandardText[]
	 */
	public function GetStandardText($searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=0, $additionalWhereClause="", $type=StandardText::ST_TYPE_TEMPLATE)
	{
		$whereClause="type=".$type;
		if ($additionalWhereClause!="")
		{
			if ($whereClause!="") $whereClause.=" AND ";
			$whereClause.=" (".$additionalWhereClause.") ";
		}
		$joinClause = "";
		$rowsToUse = Array(Array("tableName" => StandardText::TABLE_NAME, "tableRowNames" => $this->standardTextDummy->GetTableConfig()->rowName));
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		$dbEntrys=$this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, StandardText::TABLE_NAME, $allRowNames, $whereClause, $joinClause);
		// Objekte erzeugen
		$objects = Array();
		for ($a=0; $a<count($dbEntrys); $a++)
		{
			$object = new StandardText($this->db);
			if ($object->LoadFromArray($dbEntrys[$a], $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Return the StandardText by ID
	 * @param DBManager $db
	 * @param int $id
	 * @return StandardText|null
	 */
	static public function GetStandardTextById(DBManager $db, $id)
	{
		if ($id==-1) return null;
		$object = new StandardText($db);
		if ($object->Load((int)$id, $db)===true) return $object;
		return null;
	}
	
	/**
	 * 
	 * @param DBManager $db
	 * @param int $type
	 * @return array
	 */
	static public function GetPlaceholders(DBManager $db, $type)
	{
		switch($type)
		{
			case self::STM_TERMINEMAIL:
			case self::STM_TERMINEMAIL_SUBJECT:
				return TerminMail::GetPlaceholderPreview($db);
			case self::STM_INFORMCUSTOMER:
			case self::STM_INFORMCUSTOMER_SUBJECT:
				return InformationMail::GetPlaceholderPreview($db);
			case self::STM_WSANSCHREIBEN:
			case self::STM_WSANSCHREIBEN_SUBJECT:
				return Widerspruch::GetPlaceholderPreview($db);
		}
		return Array();
	}
	
}
?>