<?
/**
 * EMailManager
 * 
 * @access public
 * @author Stephan Walleczek <s.walleczek@stollvongati.com>
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2011 Stoll von Gáti GmbH www.stollvongati.com
 */
class EMailManager {
	
	/**
	 * EMail Typ
	 * @var enum
	 * @acces public
	 */
	const EMAIL_TYPE_TEXT = 0;
	const EMAIL_TYPE_HTML = 1;
	const EMAIL_TYPE_CALENDAR = 2;
	
	/**
	 * E-Mail signature
	 * @var string
	 */
	private $htmlSignature = "";
	
	/**
	 * Path to various email template files
	 * @var string 
	 */
	private $emailTemplatePath = "";
	
	/**
	 * Offline Mode?
	 * @var boolean 
	 */
	private $offlineMode = false;
	
	/**
	 * E-Mail address, the mails are send to when in offline mode
	 * @var string
	 */
	private $offlineModeOverwriteMail = "";
	
	/**
	 * Konsturktor
	 * @param string $htmlSignature			Signatur der Emails (Pfad zur HTML-Datei)
	 * @access public
	 */
	public function EMailManager($htmlSignature = "", $emailTemplatePath = "", $offlineMode=false, $offlineModeOverwriteMail = "")
	{
		$this->offlineMode = $offlineMode;
		$this->offlineModeOverwriteMail = $offlineModeOverwriteMail;
		$this->emailTemplatePath = $emailTemplatePath;
		if( $htmlSignature!="" && is_file($htmlSignature) ){
			$signatureContent = @file_get_contents($htmlSignature);
			if( $signatureContent!==false && strpos($signatureContent, "%EMAIL_TEXT%")!==false )$this->htmlSignature = $signatureContent;
		}
		// Wenn keine Signatur geladen werden konnte die Standard-Signatur laden
		if( $this->htmlSignature=="" ){
			$signatureContent = @file_get_contents("defaultEMailSignature.html", true);
			if( $signatureContent!==false && strpos($signatureContent, "%EMAIL_TEXT%")!==false )$this->htmlSignature = $signatureContent;
		}
	}
	
	/**
	 * Sendet eine Email an die angegebene Email-Adresse
	 * @param enum $email		Email des Empfängers
	 * @param string $subject	Betreff
	 * @param string $message	Nachricht
	 * @param string $from		Absender
	 * @param bool $ccMail		CC-Email(s)
	 * @param enum $emailtype	EMail Typ
	 * @return bool				Erfolg
	 * @access public
	 */
	public function SendEmail($to, $subject, $message, $from, $ccMails="", $emailtype=EMailManager::EMAIL_TYPE_HTML, $bccMails=Array() )
	{
		// are we in offline mode with overwrite mail address?
		if ($this->offlineMode && $this->offlineModeOverwriteMail!="")
		{
			// then overwrite all cc and bcc
			$to = $this->offlineModeOverwriteMail;
			$ccMails = "";
			$bccMails = Array();
		}
		
		if ($to!="")
		{
			$header ='MIME-Version: 1.0' . "\n";
			$header.='From: '.$from."\n";
			if( trim($ccMails)!="" )$header.='Cc: '.trim($ccMails)."\n";
			if (count($bccMails)>0)
			{
				$bccString = "";
				for ($a=0; $a<count($bccMails); $a++)
				{
					if (trim($bccMails[$a])=='')continue;
					if ($bccString!='') $bccString.=', ';
					$bccString.=trim($bccMails[$a]);
				}
				if ($bccString!='') $header.='Bcc: '.$bccString."\n";
			}
			$msg = "";
			if( $emailtype==EMailManager::EMAIL_TYPE_HTML ){
				$header.='Content-type: text/html; charset=utf-8' . "\n";
				if( $this->htmlSignature!="" ){
					$msg = str_replace("%EMAIL_TEXT%", $message, $this->htmlSignature);
				}else{
					$msg = $message;
				}			
			}elseif ($emailtype==EMailManager::EMAIL_TYPE_CALENDAR) {
				$header .= "Reply-To: ".$from."\n";
				$header .= 'Content-type: text/calendar; method=REQUEST; charset=utf-8'."\n";
				$header .= 'Content-Transfer-Encoding: 7bit'."\n";
				$msg = $message;
			}
			else {
				$header.='Content-type: text; charset=utf-8' . "\n";
				$msg = $message;
			}
			if ($this->offlineMode)
			{
				if ($this->offlineModeOverwriteMail!="")
				{
					return @mail($this->offlineModeOverwriteMail, $this->EncodeSubjectToUTF8($subject.' (offline mode)'), $msg, $header);
				}
				// 
				echo "<br /><br /><strong>EMailManager Offline-Mode (no email was sent)</strong><br />";
				echo "An: ".$to."<br/>";
				echo "CC: ".$ccMails."<br/>";
				echo "Betreff: ".$subject."<br/>";
				echo "Von: ".$from."<br/>";
				echo "Nachricht:<br/>".$msg."<br/><br/>";
				exit;
			}
			else
			{
				return @mail($to, $this->EncodeSubjectToUTF8($subject), $msg, $header);
			}
		}
		return false;
	}
	
	/**
	 * Sendet eine Email an die angegebene Email-Adresse
	 * @param enum $email		Email des Empfängers
	 * @param string $subject	Betreff
	 * @param string $message	Nachricht
	 * @param string $from		Absender
	 * @param bool $ccMail		CC-Email(s)
	 * @param enum $emailtype	EMail Typ
	 * @param array $attachments Array(Array('fileName' => 'xxx', 'fileContent' => '--CONTENT--'));
	 * @return bool				Erfolg
	 * @access public
	 */
	public function SendEmailWithAttachments($to, $subject, $message, $from, $ccMails="", $files = Array(), $bccMails=Array() )
	{
		if (count($files)==0)return $this->SendEmail ($to, $subject, $message, $from, $ccMails, EMailManager::EMAIL_TYPE_HTML, $bccMails);
		
		// are we in offline mode with overwrite mail address?
		if ($this->offlineMode && $this->offlineModeOverwriteMail!="")
		{
			// then overwrite all cc and bcc
			$to = $this->offlineModeOverwriteMail;
			$ccMails = "";
			$bccMails = Array();
		}
		
		if( $to!="" ){
			
			$boundary1 = rand(0,9)."-".rand(10000000000,9999999999)."-".rand(10000000000,9999999999)."=:".rand(10000,99999);
			$boundary2 = rand(0,9)."-".rand(10000000000,9999999999)."-".rand(10000000000,9999999999)."=:".rand(10000,99999);
 
			$header ='MIME-Version: 1.0' . "\n";
			$header.='From: '.$from."\n";
			if( trim($ccMails)!="" )$header.='Cc: '.trim($ccMails)."\n";
			if (count($bccMails)>0)
			{
				$bccString = "";
				for ($a=0; $a<count($bccMails); $a++)
				{
					if (trim($bccMails[$a])=='')continue;
					if ($bccString!='') $bccString.=', ';
					$bccString.=trim($bccMails[$a]);
				}
				if ($bccString!='') $header.='Bcc: '.$bccString."\n";
			}		
			$header.="Content-Type: multipart/mixed; boundary=\"".$boundary1."\"\n";
			$attachments='';
			for($a=0;$a<count($files); $a++){
				$attachments.="--".$boundary1."\n";
				$attachments.="Content-Type: application/octetstream; name=\"".$this->EncodeSubjectToUTF8($files[$a]['fileName'])."\"\n";
				$attachments.="Content-Transfer-Encoding: base64\n";
				$attachments.="Content-Disposition: attachment; filename=\"".$this->EncodeSubjectToUTF8($files[$a]['fileName'])."\"\n\n";
				$attachments.=chunk_split(base64_encode($files[$a]['fileContent']))."\n\n";
			}
			
			$body = "This is a multi-part message in MIME format.\n\n";
			$body.= "--".$boundary1."\n";
			$body.= "Content-Type: multipart/alternative; boundary=\"".$boundary2."\"\n\n";
			$body.= "--".$boundary2."\n";
			$body.= "Content-type: text/html; charset=\"utf-8\"\n\n";
			$msg = "";
			if( $this->htmlSignature!="" ){
				$msg = str_replace("%EMAIL_TEXT%", $message, $this->htmlSignature);
			}else{
				$msg = $message;
			}
			$body.= $msg."\n";
			$body.= "--".$boundary2."--\n\n";
			$body.= $attachments."\n";
			$body.= "--".$boundary1."--\n";
			
			if ($this->offlineMode)
			{
				if ($this->offlineModeOverwriteMail!="")
				{
					return @mail($this->offlineModeOverwriteMail, $this->EncodeSubjectToUTF8($subject.' (offline mode)'), $body, $header);
				}
				echo "<br /><br /><strong>EMailManager Offline-Mode (no email was sent)</strong><br />";
				echo "An: ".$to."<br/>";
				echo "CC: ".$ccMails."<br/>";
				echo "Betreff: ".$subject."<br/>";
				echo "Von: ".$from."<br/>";
				echo "Nachricht:<br/>".$msg."<br/><br/>";
				exit;
			}
			else
			{
				return @mail($to, $this->EncodeSubjectToUTF8($subject), $body, $header);
			}
		}
		return false;
	}
	
	/**
	 * Encodes UTF8 String to RFC-2047
	 * @param string $subject Subject to encode
	 * @return string Encoded subject
	 */
	protected function EncodeSubjectToUTF8($subject)
	{
		return "=?utf-8?B?".base64_encode($subject)."?=";
	}
	
	/**
	 * Return the path to various email template files
	 * @return string
	 */
	public function GetEmailTemplatePath()
	{
		return $this->emailTemplatePath;
	}
	
	/**
	 * Return the content of the email template file as sting
	 * @param type $templateFileName
	 * @return string
	 */
	public function GetEmailTemplateContent($templateFileName)
	{
		return file_get_contents($this->emailTemplatePath.$templateFileName);
	}
	
	/**
	 * Return the HTML E-Mail signature
	 * @return string
	 */
	public function GetHtmlSignature()
	{
		return $this->htmlSignature;
	}
	
	/**
	 * Set the HTML E-Mail signature (the string have to contain at least the placeholder %EMAIL_TEXT% for email content)
	 * @param string $htmlSignature
	 * @return boolean
	 */
	public function SetHtmlSignature($htmlSignature)
	{
		// the string have to contain at least the placeholder %EMAIL_TEXT% for email content
		if (strpos($htmlSignature, "%EMAIL_TEXT%")===false) return false;
		$this->htmlSignature = $htmlSignature;
		return true;
	}
	
} // class CronjobErrorLogger

?>