<?php
/**
 * Diese Klasse repräsentiert eine EMail
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2014 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class EMail extends DBEntry 
{
	/**
	 * Email versenden?
	 * @var boolean 
	 */
	protected $sendMail = true;
	
	/**
	 * Sender of the mail
	 * @var AddressData
	 */
	protected $sender = null;
	
	/**
	 * Recipient of the mail
	 * @var AddressBase 
	 */
	protected $recipient = null;
	
	/**
	 * additional CC-emails 
	 * @var atring
	 */
	protected $recipientCc = "";
	
	/**
	 * Betreff der Terminemail
	 * @var string 
	 */
	protected $subject = "";
	
	/**
	 * Text der Terminemail
	 * @var string 
	 */
	protected $mailText = "";
	
	/**
	 * Sprache, in welcher die E-Mail verfasst ist
	 * @var string 
	 */
	protected $language = "DE";
	
	/**
	 * Which typ of FMS-Schrieben the email shold be sored as
	 * @var int
	 */
	protected $storeAsFmsType = FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_PROTOKOLL;
	
	/**
	 * How shold be allowed to see the FMS-Schrieben
	 * @var int
	 */
	protected $fmsFileVisibleFor = -1;
	
	/**
	 * E-Mail send time
	 * @var int
	 */
	protected $sendTime = 0;
	
	/**
	 * Zugehöriger Widerspruch
	 * @var Widerspruch
	 */
	protected $widerspruch = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 * @param DBConfig $dbConfig
	 */
	public function EMail(DBManager $db, DBConfig $dbConfig)
	{
		$dbConfigTemp = new DBConfig();
		$dbConfigTemp->rowName = Array("sendMail", "sender_rel", "sender_typ", "recipient_rel", "recipient_typ", "subject", "mailText", "language", "storeAsFmsType", "fmsFileVisibleFor", "recipientCc", "sendTime", "widerspruch_rel");
		$dbConfigTemp->rowParam = Array("INT", "BIGINT", "INT", "BIGINT", "INT", "TEXT", "LONGTEXT", "VARCHAR(2)", "INT", "INT", "TEXT", "BIGINT", "BIGINT");
		$dbConfigTemp->rowIndex = Array("widerspruch_rel");
		$dbConfig->Append($dbConfigTemp);
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
		if ($this->widerspruch==null) return -1;
		$rowName[]= "sendMail";
		$rowData[]= ($this->sendMail ? 1 : 0);
		$rowName[]= "sender_rel";
		$rowData[]= $this->sender==null ? -1 : $this->sender->GetPKey();
		$rowName[]= "sender_typ";
		$rowData[]= $this->sender==null ? AddressBase::AM_CLASS_UNKNOWN : $this->sender->GetClassType();
		$rowName[]= "recipient_rel";
		$rowData[]= $this->recipient==null ? -1 : $this->recipient->GetPKey();
		$rowName[]= "recipient_typ";
		$rowData[]= $this->recipient==null ? AddressBase::AM_CLASS_UNKNOWN : $this->recipient->GetClassType();
		$rowName[]= "subject";
		$rowData[]= $this->subject;
		$rowName[]= "mailText";
		$rowData[]= $this->mailText;
		$rowName[]= "language";
		$rowData[]= $this->language;
		$rowName[]= "storeAsFmsType";
		$rowData[]= $this->storeAsFmsType;
		$rowName[]= "fmsFileVisibleFor";
		$rowData[]= $this->fmsFileVisibleFor;
		$rowName[]= "recipientCc";
		$rowData[]= $this->recipientCc;
		$rowName[]= "sendTime";
		$rowData[]= $this->sendTime;
		$rowName[]= "widerspruch_rel";
		$rowData[]= $this->widerspruch==null ? -1 : $this->widerspruch->GetPKey();
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
		$this->sendMail = ($data['sendMail'] == 1 ? true : false);
		if ($data['sender_rel']!=-1)
		{
			$this->sender = AddressManager::GetAddressElementByPkeyAndType($db, (int)$data['sender_typ'], $data['sender_rel']);
		}
		else
		{
			$this->sender = null;
		}
		if ($data['recipient_rel']!=-1)
		{
			$this->recipient = AddressManager::GetAddressElementByPkeyAndType($db, (int)$data['recipient_typ'], $data['recipient_rel']);
		}
		else
		{
			$this->recipient = null;
		}
		$this->subject = $data['subject'];
		$this->mailText = $data['mailText'];
		$this->language = (trim($data['language'])!='' ? trim($data['language']) : "DE");
		$this->storeAsFmsType = $data['storeAsFmsType'];
		$this->fmsFileVisibleFor = $data['fmsFileVisibleFor'];
		$this->recipientCc = $data['recipientCc'];
		$this->sendTime = $data['sendTime'];
		if (is_object($data['widerspruch_rel']) && is_a($data['widerspruch_rel'], 'Widerspruch'))
		{
			$this->widerspruch = $data['widerspruch_rel'];
		}
		elseif ($data['widerspruch_rel']!=-1)
		{
			$this->widerspruch = new Widerspruch($db);
			if ($this->widerspruch->Load($data['widerspruch_rel'], $db)!==true) $this->widerspruch = null;
		}
		else
		{
			$this->widerspruch=null;
		}
		return true;
	}
	
	/**
	 * Setzt, ob die E-Mail versendet werden soll
	 * @param boolean $sendMail
	 * @return boolean
	 */
	public function SetSendMail($sendMail)
	{
		if (!is_bool($sendMail)) return false;
		$this->sendMail = $sendMail;
		return true;
	}
	
	/**
	 * Gibt zurück, ob die E-Mail versendet werden soll
	 * @return boolean
	 */
	public function GetSendMail()
	{
		return $this->sendMail;
	}
	
	/**
	 * Set the sender of the mail
	 * @param AddressData $sender
	 * @return boolean
	 */
	public function SetSender(AddressData $sender=null)
	{
		$this->sender = $sender;
		return true;
	}
	
	/**
	 * Return the sender of the mail
	 * @return AddressData
	 */
	public function GetSender()
	{
		return $this->sender;
	}
	
	/**
	 * Set the recipient of the mail
	 * @param AddressBase $recipient
	 * @return boolean
	 */
	public function SetRecipient(AddressBase $recipient=null)
	{
		if ($recipient!=null && !is_a($recipient, 'AddressData') && !is_a($recipient, 'AddressCompany')) return false;
		$this->recipient = $recipient;
		return true;
	}
	
	/**
	 * Return the recipient of the mail
	 * @return AddressBase
	 */
	public function GetRecipient()
	{
		return $this->recipient;
	}
	
	/**
	 * Setzt den Email-Betreff
	 * @param string $emailSubject
	 * @return bool
	 */
	public function SetSubject($emailSubject)
	{
		$this->subject = $emailSubject;
		return true;
	}
	
	/**
	 * Gibt den Email-Betreff zurück
	 * @return string
	 */
	public function GetSubject()
	{
		return $this->subject;
	}
	
	/**
	 * Setzt den Email-Text
	 * @param string $emailText
	 * @return bool
	 */
	public function SetText($emailText)
	{
		$this->mailText = $emailText;
		return true;
	}
	
	/**
	 * Gibt den Email-Text zurück
	 * @return string
	 */
	public function GetText()
	{
		return $this->mailText;
	}
	
	/**
	 * Setzt die Sprache
	 * @param string $language
	 * @return bool
	 */
	public function SetLanguage($language)
	{
		if (!CLanguage::ValidateIso639($language)) return false;
		$this->language = $language;
		return true;
	}
	
	/**
	 * Gibt die Sprache zurück
	 * @return string
	 */
	public function GetLanguage()
	{
		return $this->language;
	}
	
	/**
	 * Set the typ of FMS-Schrieben the email shold be sored as
	 * @param int $fmsType
	 * @return bool
	 */
	public function SetFmsType($fmsType)
	{
		if (!is_int($fmsType)) {return false;}
		$this->storeAsFmsType = $fmsType;
		return true;
	}
	
	/**
	 * Return the typ of FMS-Schrieben the email shold be sored as
	 * @return int
	 */
	public function GetFmsType()
	{
		return $this->storeAsFmsType;
	}
	
	/**
	 * Set who shold be allowed to see the FMS-Schrieben
	 * @param int $fmsType
	 * @return bool
	 */
	public function SetFmsVisibleFor($visibleFor)
	{
		if (!is_int($visibleFor)) {return false;}
		$this->fmsFileVisibleFor = $visibleFor;
		return true;
	}
	
	/**
	 * Returns who shold be allowed to see the FMS-Schrieben
	 * @return int
	 */
	public function GetFmsVisibleFor()
	{
		return $this->fmsFileVisibleFor;
	}
	
	/**
	 * set additional CC-emails 
	 * @param string $recipientCc
	 * @return bool
	 */
	public function SetRecipientCc($recipientCc, &$errorRecipients)
	{
		if (trim($recipientCc)=="")
		{
			$this->recipientCc = "";
			return true;
		}
		
		$validEmails = 0;
		$errorRecipients = "";
		$correctRecipients = "";
		$recipients = explode(',', $recipientCc);
		foreach ($recipients as $recipient)
		{
			if (User::CheckFormatEMail(trim($recipient))!==true)
			{
				if ($errorRecipients!='') $errorRecipients.=", ";
				$errorRecipients.="'".trim($recipient)."'";
			}
			else
			{
				if ($correctRecipients!='') $correctRecipients.=", ";
				$correctRecipients.=trim($recipient);
				$validEmails++;
			}
		}

		if ($validEmails==0 || $errorRecipients!="")
		{
			return false;
		}
		$this->recipientCc = $correctRecipients;
		return true;
	}
	
	/**
	 * get additional CC-emails 
	 * @return string
	 */
	public function GetRecipientCc()
	{
		return $this->recipientCc;
	}
	
	/**
	 * Set the time when the email was sent
	 * @param int $sendTime
	 * @return bool
	 */
	public function SetSendTime($sendTime)
	{
		if (!is_int($sendTime)) {return false;}
		$this->sendTime = $sendTime;
		return true;
	}
	
	/**
	 * Returns the time when the email was sent
	 * @return int
	 */
	public function GetSendTime()
	{
		return $this->sendTime;
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
	
	/**
	 * Replace all placeholders in subject with the corresponding values
	 * @param DBManager $db
	 * @param string $subject
	 * @param boolean $utf8decode
	 * @param array $additionalPlaceholders
	 * @return string
	 */
	public function ReplacePlaceholders(DBManager $db, $subject, $utf8decode=false, $additionalPlaceholders=Array())
	{
		$placeholders = $this->GetPlaceholders($db);
		// add additional placeholders
		foreach ($additionalPlaceholders as $key => $value)
		{
			$placeholders[$key] = $value;
		}
		foreach ($placeholders as $key => $value)
		{
			if ($utf8decode)
			{
				$subject = str_replace($key, utf8_decode($value), $subject);
			}
			else
			{
				$subject = str_replace($key, $value, $subject);
			}
		}
		return $subject;
	}
	
	/**
	 * Return a array with all placehodlers (array keys) and the corresponding values (array values)
	 * @return array
	 */
	protected function GetPlaceholders(DBManager $db)
	{
		$placeHolders = Array();
		$process = ($this->widerspruch!=null ? $this->widerspruch->GetProcessStatus($db) : null);
		// Shop
		$shop = ($process!=null ? $process->GetShop() : null);
		if ($shop!=null)
		{
			$placeHoldersTemp = $shop->GetPlaceholders($db, $this->language);
			foreach ($placeHoldersTemp as $key => $value) 
			{
				$placeHolders[$key] = $value;
			}
		}
		// Location
		$location = ($process!=null ? $process->GetLocation() : null);
		if ($location!=null)
		{
			$placeHoldersTemp = $location->GetPlaceholders($db, $this->language);
			foreach ($placeHoldersTemp as $key => $value) 
			{
				$placeHolders[$key] = $value;
			}
		}
		// Abrehnungsjahr
		$abrechnungsjahr = ($this->widerspruch!=null ? $this->widerspruch->GetAbrechnungsJahr($db) : null);
		if ($abrechnungsjahr!=null)
		{
			$placeHoldersTemp = $abrechnungsjahr->GetPlaceholders($db, $this->language);
			foreach ($placeHoldersTemp as $key => $value) 
			{
				$placeHolders[$key] = $value;
			}
		}
		// Empfänger
		if ($this->recipient!=null)
		{
			$placeHoldersAsp = $this->recipient->GetPlaceholders($db, $this->language, 'AP');
			foreach ($placeHoldersAsp as $key => $value) 
			{
				$placeHolders[$key] = trim($value);
			}
		}
		// Sender
		if ($this->sender!=null)
		{
			$placeHoldersAsp = $this->sender->GetPlaceholders($db, $this->language, 'KM');
			foreach ($placeHoldersAsp as $key => $value) 
			{
				$placeHolders[$key] = trim($value);
			}
		}		
		return $placeHolders;
	}
	
	/**
	 * Return the final mail data
	 * @param DBManager $db
	 * @return Array
	 */
	protected function GetMailData(DBManager $db)
	{
		// Absender
		$sender = "";
		if ($this->sender!=null)
		{
			$sender = $this->sender->GetEMail();
		}
		// Empfänger
		$recipient = "";
		if ($this->recipient!=null)
		{
			$recipient = $this->recipient->GetEMail();
		}
		
		$returnValue = Array();
		$returnValue["from"] = $sender;
		$returnValue["to"] = $recipient;
		$returnValue["cc"] = $this->GetRecipientCc();
		$returnValue["subject"] = $this->ReplacePlaceholders($db, $this->GetSubject());
		$returnValue["text"] = $this->ReplacePlaceholders($db, $this->GetText());
		$returnValue["sendTime"] = time();
		
		return $returnValue;
	}
	
	/**
	 * Return the E-Mail as PDF
	 * @param DBManager $db
	 * @return string
	 */
	public function CreateDocument(DBManager $db)
	{
		$ANSCHREIBEN = $this->GetMailData($db);
		ob_start();
		include("template_anschreiben.inc.php5");
		$html = ob_get_contents();
		ob_end_clean();
		include_once("html2pdf.php5");
		//echo $html;
		$pdfContent = convert_to_pdf($html, "A4", 1024, false, '', false, array('left' => 10, 'right' => 10, 'top' => 10, 'bottom' => 10) );
		$returnValue = Array();
		$returnValue['content'] = $pdfContent;
		$returnValue['extension'] = 'pdf';
		return $returnValue;
	}
	
	/**
	 * Send this Termin-E-Mail
	 * @param DBManager $db
	 * @return int	-1: no sender 
	 *				-2: E-Mail konnte nicht versendet werden
	 *				-3: FMS-Schreiben konnt nicht erzeugt werden
	 *				-4: Keine E-Mail bei Empfänger hinterlegt
	 *				-5: Keine E-Mail bei Absender hinterlegt
	 */
	public function SendMail(DBManager $db)
	{
		global $SHARED_FILE_SYSTEM_ROOT, $EMAIL_MANAGER_OFFLINE_MODE, $EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL;
		
		$sender = $this->GetSender();
		if ($sender==null) return -1;
		
		$mailData = $this->GetMailData($db);
		$mailData["from"] = "Seybold FM <mnk@seybold-fm.com>"; // always overwrite sender email
		if (trim($mailData["to"])=='') return -4;
		if (trim($mailData["from"])=='') return -5;
		
		$emailManager = new EMailManager($SHARED_FILE_SYSTEM_ROOT."templates/emailSignature.html", "", $EMAIL_MANAGER_OFFLINE_MODE, $EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL);
		$emailSignature = $emailManager->GetHtmlSignature();
		$emailSignature = str_replace("%KM_NAME%", $sender->GetFirstName()." ".$sender->GetName(), $emailSignature);
        $emailSignature = str_replace("%KM_ROLE%", trim($sender->GetRole())!="" ? "(".$sender->GetRole().")" : "", $emailSignature);
		$emailSignature = str_replace("%KM_TEL%", $sender->GetPhone(), $emailSignature);
		$emailSignature = str_replace("%KM_FAX%", $sender->GetFax(), $emailSignature);
		$emailSignature = $this->ReplacePlaceholders($db, $emailSignature);
		$emailManager->SetHtmlSignature($emailSignature);
		$files = Array();
		if ($emailManager->SendEmailWithAttachments($mailData["to"], $mailData["subject"], str_replace("\n", "<br />", $mailData["text"]), $mailData["from"], $mailData["cc"], $files, Array($mailData["from"])))
		{
			$this->SetSendTime(time());
			$this->Store($db);
			// Add Mail as FMS-Anschreiben
			return ($this->AddMailToTerminschiene($db)===true ? true : -3);
		}
		return -2;
	}
	
	/**
	 * Add Mail as FMS-Anschreiben
	 * @param DBManager $db
	 * @return boolean
	 */
	protected function AddMailToTerminschiene(DBManager $db)
	{
		$pdf = $this->CreateDocument($db);
		if (is_int($pdf)) return false;
		$fileObject = FileManager::CreateFromStream($db, $pdf['content'], FM_FILE_SEMANTIC_RSSCHREIBEN, "Terminemail vom ".date("d.m.Y H.i", $this->GetSendTime())." Uhr.pdf", "PDF");
		if (is_object($fileObject) && is_a($fileObject, "File"))
		{
			$status = ($this->widerspruch==null ? null : $this->widerspruch->GetProcessStatus($db));
			$fileObject->SetIntern($this->GetFmsVisibleFor()==File::FM_FILE_INTERN_YES ? true : false);
			$fileObject->SetFileSemanticSpecificString($this->GetFmsType()."_".($status!=null ? $status->GetCurrentStatus() : "-1"));
			if ($fileObject->Store($db)!==true) {return false;}
			$returnValue = $this->GetWiderspruch()->AddFile($db, $fileObject);
			return ($returnValue===true ? true : false);
		}
		return false;
	}

}
?>