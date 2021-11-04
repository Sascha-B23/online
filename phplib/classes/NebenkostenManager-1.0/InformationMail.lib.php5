<?php
/**
 * Diese Klasse repräsentiert einen Informations-E-Mail
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2014 Stoll von Gáti GmbH www.stollvongati.com
 */
class InformationMail extends EMail 
{
	/**
	 * Datenbankname und Spalten
	 * @var string
	 */
	const TABLE_NAME = "informationmail";
		
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function InformationMail(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		parent::__construct($db, $dbConfig);
	}

	/**
	 * Return a list with all placeholders with preview data
	 * @param DBManager $db
	 * @return Array
	 */
	static public function GetPlaceholderPreview(DBManager $db)
	{
		$mail = new InformationMail($db);
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