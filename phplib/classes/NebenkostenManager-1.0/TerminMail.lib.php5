<?php
/**
 * Diese Klasse repräsentiert einen Terminemail
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2014 Stoll von Gáti GmbH www.stollvongati.com
 */
class TerminMail extends EMail 
{
	/**
	 * Datenbankname und Spalten
	 * @var string
	 */
	const TABLE_NAME = "terminmail";
	
	/**
	 * Old deadline
	 * @var int
	 */
	protected $oldDeadline = 0;
		
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function TerminMail(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("oldDeadline");
		$dbConfig->rowParam = Array("BIGINT");
		$dbConfig->rowIndex = Array();
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
		// Array mit zu speichernden Daten anlegen
		$returnValue = parent::BuildDBArray($db, $rowName, $rowData);
		if ($returnValue!==true) return $returnValue;
		$rowName[]= "oldDeadline";
		$rowData[]= $this->oldDeadline;
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
		$returnValue = parent::BuildFromDBArray($db, $data);
		if ($returnValue!==true) return $returnValue;
		$this->oldDeadline = $data['oldDeadline'];
		return true;
	}
	
	/**
	 * Set the old deadline
	 * @param int $oldDeadline
	 * @return bool
	 */
	public function SetOldDeadline($oldDeadline)
	{
		if (!is_int($oldDeadline)) {return false;}
		$this->oldDeadline = $oldDeadline;
		return true;
	}
	
	/**
	 * Returns the old deadline
	 * @return int
	 */
	public function GetOldDeadline()
	{
		return $this->oldDeadline;
	}
	
	/**
	 * Return a array with all placehodlers (array keys) and the corresponding values (array values)
	 * @return array
	 */
	protected function GetPlaceholders(DBManager $db)
	{
		$placeHolders = parent::GetPlaceholders($db);
		
		// Datumsformat
		$dateFormat = "";
		$date = StandardTextManager::GetStandardTextById($db, StandardTextManager::STM_DATE);
		if ($date!=null) $dateFormat = $date->GetStandardText($this->GetLanguage());
		if (trim($dateFormat)=="") $dateFormat = "d.m.Y";
		
		// Zeitformat
		$timeFormat = "";
		$time = StandardTextManager::GetStandardTextById($db, StandardTextManager::STM_TIME);
		if ($time!=null) $timeFormat = $time->GetStandardText($this->GetLanguage());
		if (trim($timeFormat)=="") $timeFormat = "d.m.Y";
		
		
		$placeHolders["%ANTWORTFRIST_ALT%"] = '-';
		$timestamp = $this->GetOldDeadline();
		if ($timestamp!=0) $placeHolders["%ANTWORTFRIST_ALT%"] = date($dateFormat, $timestamp);

		$status = ($this->GetWiderspruch()==null ? null : $this->GetWiderspruch()->GetProcessStatus($db));
		
		$placeHolders["%ANTWORTFRIST_NEU%"] = '-';
		$timestamp = ($status==null ? 0 : $status->GetTelefontermin());
		if ($timestamp!=0) $placeHolders["%ANTWORTFRIST_NEU%"] = date($dateFormat, $timestamp);
		
		$placeHolders["%TERMIN_BEGINN%"] = '-';
		$timestamp = ($status==null ? 0 : $status->GetTelefontermin());
		if ($timestamp!=0) $placeHolders["%TERMIN_BEGINN%"] = date($timeFormat, $timestamp);
		
		return $placeHolders;
	}
	
	/**
	 * Return a list with all placeholders with preview data
	 * @param DBManager $db
	 * @return Array
	 */
	static public function GetPlaceholderPreview(DBManager $db)
	{
		$mail = new TerminMail($db);
		$mail->SetOldDeadline(time()-60*60*24*7);
		$mail->SetSender($_SESSION["currentUser"]->GetAddressData());
		$widerspruch = new Widerspruch($db);
		if ($widerspruch->Load(324, $db)===true)
		{
			$mail->SetWiderspruch($widerspruch);
			$mail->SetRecipient($widerspruch->GetAnsprechpartner());
		}
		$placeholders = $mail->GetPlaceholders($db);
		return $placeholders;
	}
	
}
?>