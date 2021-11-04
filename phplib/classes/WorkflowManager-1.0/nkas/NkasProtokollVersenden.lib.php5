<?php
/**
 * Status "Protokoll per Email versenden"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasProtokollVersenden extends NkasStatusFormDataEntry
{
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 1 Tage nach Erhalt
		$this->obj->SetDeadline( time()+60*60*24);
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		/*@var $widerspruch Widerspruch*/
		$sendProtocolAsAttachment = true;
		if ($widerspruch!=null)
		{
			$rueckweisungsBegruendung=$widerspruch->GetLastRueckweisungsBegruendung($this->db, RueckweisungsBegruendung::RWB_TYPE_WIDERSPRUCH);
			if ($rueckweisungsBegruendung!=null) $this->formElementValues["ablehnungsbegruendung"]=$rueckweisungsBegruendung->GetBegruendung();
			if ($loadFromObject)
			{
				$addressBase =$widerspruch->GetAnsprechpartner();
				if ($addressBase!=null)
				{
					if (is_a($addressBase, 'AddressData'))
					{
						/*@var $addressBase AddressData*/
						$this->formElementValues["recipient"] = $addressBase->GetEMail();
					}
					elseif (is_a($addressBase, 'AddressCompany'))
					{
						/*@var $addressBase AddressCompany*/
						$this->formElementValues["recipient"] = $addressBase->GetEMail();
					}
				}
			}
			
			$this->formElementValues["letterSubject"] = $widerspruch->ReplacePlaceholders($this->db, $widerspruch->GetLetterSubject());
			$this->formElementValues["letter"] = $widerspruch->ReplacePlaceholders($this->db, $widerspruch->GetLetter());
			$this->formElementValues["letterSendTime"] = $widerspruch->GetLetterSendTime();
			$sendProtocolAsAttachment = $widerspruch->GetSendProtocolAsAttachemnt();
		}
		// Empfänger
		$this->elements[] = new TextElement("recipient", "E-Mail Empfänger", $this->formElementValues["recipient"], true, $this->error["recipient"], false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// CC-Empfänger
		$this->elements[] = new TextElement("recipientCc", "E-Mail Empfänger CC", $this->formElementValues["recipientCc"], false, $this->error["recipientCc"], false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Absender
		$this->elements[] = new TextElement("sender", "E-Mail Absender (nur lesend)", "Seybold FM <mnk@seybold-fm.com>", false, $this->error["sender"], false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Betreff
		$this->elements[] = new TextElement("letterSubject", "Vorschau E-Mail Betreff (nur lesend)", $this->formElementValues["letterSubject"], false, $this->error["letterSubject"], false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Anschreiben
		$this->elements[] = new TextAreaElement("letter", "Vorschau E-Mail Text (nur lesend)", $this->formElementValues["letter"], false, $this->error["letter"], false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		if ($sendProtocolAsAttachment)
		{
			// Anhang
			ob_start();
			?>
				<strong>Anhang</strong><br>
				<input type="hidden" name="createDownloadFile" id="createDownloadFile" value="" />
				<input type="button" value="Vorschau Anhang" onClick="DownloadFile(<?=DOCUMENT_TYPE_ANHANG;?>);" class="formButton2" />
				<?if( trim($this->error["createDocument"])!=""){?><br /><br /><div class="errorText"><?=$this->error["createDocument"];?></div><?}?>
			<?
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		// Zusätzliche Dateien
		if ($widerspruch!=null)
		{
			$additionalFiles = $widerspruch->GetFiles($this->db, FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE);
			if (count($additionalFiles)>0)
			{
				$this->elements[] = new FileElement("wFile", "<br/>Zusätzliche Anhänge", $this->formElementValues["wFile"], false, $this->error["wFile"], FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE, $additionalFiles, false, false, false, false);
				$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
				$this->elements[] = new BlankElement();
				$this->elements[] = new BlankElement();
			}
		}
		// Email versenden
		ob_start();
		?>
			<br />
			<?if($this->formElementValues["letterSendTime"]>0){?>
				<strong>Dieses Protokoll wurde am <?=date("d.m.Y", $this->formElementValues["letterSendTime"]);?> um <?=date("H:i", $this->formElementValues["letterSendTime"]);?> Uhr per E-Mail versendet</strong><br /><br />
			<?}?>
			<input type="hidden" name="doSendEmail" id="doSendEmail" value="" />
			<input type="button" id="sendMailButton" value="E-Mail versenden" onClick="SendEmail();" class="formButton2" />
			<?if( trim($this->error["sendEmail"])!=""){?><br /><br /><div class="errorText"><?=$this->error["sendEmail"];?></div><?}?>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		global $SHARED_FILE_SYSTEM_ROOT, $EMAIL_MANAGER_OFFLINE_MODE, $EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL;
		if ($this->formElementValues["doSendEmail"]=="on")
		{
			// Check E-Mail Address...
			$validEmails = 0;
			$errorRecipients = "";
			$recipients = explode(',', $this->formElementValues["recipient"]);
			foreach ($recipients as $recipient)
			{
				if (User::CheckFormatEMail(trim($recipient))!==true)
				{
					if ($errorRecipients!='') $errorRecipients.=", ";
					$errorRecipients.="'".trim($recipient)."'";
				}
				else
				{
					$validEmails++;
				}
			}
			if (trim($this->formElementValues["recipient"])=="")
			{
				$this->error["recipient"] = "Bitte geben Sie die E-Mail Adresse des Empfängers an.";
			}
			elseif ($validEmails==0 || $errorRecipients!="")
			{
				$this->error["recipient"] = "Die eingegebene(n) E-Mail-Adresse(n) sind ungültig: ".$errorRecipients;
			}
			// Check CC E-Mail Address...
			if (trim($this->formElementValues["recipientCc"])!="")
			{
				$validEmails = 0;
				$errorRecipients = "";
				$recipients = explode(',', $this->formElementValues["recipientCc"]);
				foreach ($recipients as $recipient)
				{
					if (User::CheckFormatEMail(trim($recipient))!==true)
					{
						if ($errorRecipients!='') $errorRecipients.=", ";
						$errorRecipients.="'".trim($recipient)."'";
					}
					else
					{
						$validEmails++;
					}
				}
				
				if ($validEmails==0 || $errorRecipients!="")
				{
					$this->error["recipientCc"] = "Die eingegebene(n) E-Mail-Adresse(n) sind ungültig: ".$errorRecipients;
				}
			}
			
			
			if (count($this->error)==0)
			{
				$widerspruch=$this->obj->GetWiderspruch($this->db);
				if( $widerspruch!=null )
				{
					$this->formElementValues["letterSubject"] = $widerspruch->ReplacePlaceholders($this->db, $widerspruch->GetLetterSubject());
					$this->formElementValues["letter"] = $widerspruch->ReplacePlaceholders($this->db, $widerspruch->GetLetter());
					$sendProtocolAsAttachment = $widerspruch->GetSendProtocolAsAttachemnt();

					if ($sendProtocolAsAttachment)
					{
						$result = $widerspruch->CreateDocument($this->db, DOCUMENT_TYPE_PDF, DOCUMENT_TYPE_ANHANG);
						if (is_int($result))
						{
							$this->error["sendEmail"] = "Anhang konnte nicht erzeugt werden (".$result.")";
						}
					}

					if (count($this->error)==0)
					{
						$emailManager = new EMailManager($SHARED_FILE_SYSTEM_ROOT."templates/emailSignature.html", "", $EMAIL_MANAGER_OFFLINE_MODE, $EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL);
						$emailSignature = $emailManager->GetHtmlSignature();
						$emailSignature = str_replace("%KM_NAME%", $_SESSION["currentUser"]->GetFirstName()." ".$_SESSION["currentUser"]->GetName(), $emailSignature);
                        $emailSignature = str_replace("%KM_ROLE%", trim($_SESSION["currentUser"]->GetRole())!="" ? "(".$_SESSION["currentUser"]->GetRole().")" : "", $emailSignature);
						$emailSignature = str_replace("%KM_TEL%", $_SESSION["currentUser"]->GetPhone(), $emailSignature);
						$emailSignature = str_replace("%KM_FAX%", $_SESSION["currentUser"]->GetFax(), $emailSignature);
						$emailSignature = $widerspruch->ReplacePlaceholders($this->db, $emailSignature);
						$emailManager->SetHtmlSignature($emailSignature);
						$files = Array();
						/*@var $widerspruch Widerspruch*/
						$contract = $widerspruch->GetContract();
						$shop = $contract!=null ? $contract->GetShop() : null;
						$location = $shop!=null ? $shop->GetLocation() : null;
						$company = $location!=null ? $location->GetCompany() : null;
						$fileName = "Widerspruch ".($company!=null ? $company->GetName()." " : "").($location!=null ? $location->GetName()." " : "").date("d.m.Y").".pdf";
						// add PDF attachment
						if ($sendProtocolAsAttachment) $files[]=Array("fileName" => $fileName, "fileContent" => $result["content"] );
						// add additonal files as attachment
						$additionalFiles = $widerspruch->GetFiles($this->db, FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE);
						foreach ($additionalFiles as $additionalFile)
						{
							$files[]=Array("fileName" => $additionalFile->GetFileName(), "fileContent" => file_get_contents(FM_FILE_ROOT.$additionalFile->GetDocumentPath()));
						}
						// send email
						if (!$emailManager->SendEmailWithAttachments(trim($this->formElementValues["recipient"]), $this->formElementValues["letterSubject"], str_replace("\n", "<br />", $this->formElementValues["letter"]), "Seybold FM <mnk@seybold-fm.com>", $this->formElementValues["recipientCc"], $files, Array("Seybold FM <mnk@seybold-fm.com>") ))
						{
							$this->error["sendEmail"] = "E-Mail konnte nicht versandt werden";
						}
						else
						{
							$widerspruch->SetLetterSendTime(time());
							$widerspruch->Store($this->db);
							if ($sendProtocolAsAttachment)
							{
								// WS-Anhang als Dokument in Terminschiene hinzufügen
								$fileObject = FileManager::CreateFromStream($this->db, $result["content"], FM_FILE_SEMANTIC_PROTOCOL, "Protokoll Anhang vom ".date("d.m.Y H.i", $widerspruch->GetLetterSendTime())." Uhr.pdf", "PDF");
								if (is_object($fileObject) && is_a($fileObject, "File"))
								{
									$returnValue = $this->obj->AddFile($this->db, $fileObject);
									//$returnValue = $widerspruch->AddFile($this->db, $fileObject);
									if ($returnValue!==true) $this->error["misc"][] = "Der Anhang des Protokolls konnte nicht zur Terminschiene hinzugefügt werden (".$returnValue.")";
								}
								else
								{
									$this->error["sendEmail"] = "Der Anhang des Protokolls konnte nicht zur Terminschiene hinzugefügt werden (".$fileObject.")";
								}
							}
							// Anschreiben als PDF erzeugen und in Terminschiene hinzufügen
							ob_start();
							$ANSCHREIBEN = Array("from" => "Seybold FM <mnk@seybold-fm.com>", "to" => $this->formElementValues["recipient"], "cc" => $this->formElementValues["recipientCc"], "subject" => $this->formElementValues["letterSubject"], "text" => $this->formElementValues["letter"], "sendTime" => $widerspruch->GetLetterSendTime() );
							include($SHARED_FILE_SYSTEM_ROOT."templates/template_anschreiben.inc.php5");
							$html = ob_get_contents();
							ob_end_clean();
							include_once("html2pdf.php5");
							//echo $html;
							$pdfContent = convert_to_pdf($html, "A4", 1024, false, '', false, array('left' => 10, 'right' => 10, 'top' => 10, 'bottom' => 10) );
							$fileObject = FileManager::CreateFromStream($this->db, $pdfContent, FM_FILE_SEMANTIC_PROTOCOL, "Protokoll Anschreiben vom ".date("d.m.Y H.i", $widerspruch->GetLetterSendTime())." Uhr.pdf", "PDF");
							if (is_object($fileObject) && is_a($fileObject, "File"))
							{
								$returnValue = $this->obj->AddFile($this->db, $fileObject);
								//$returnValue = $widerspruch->AddFile($this->db, $fileObject);
								if ($returnValue!==true) $this->error["misc"][] = "Das Anschreiben des Protokolls konnte nicht zur Terminschiene hinzugefügt werden (".$returnValue.")";
							}
							else
							{
								$this->error["sendEmail"] = "Das Anschreiben des Protokolls konnte nicht zur Terminschiene hinzugefügt werden (".$fileObject.")";
							}
							return (count($this->error)==0 ? true : false);
						}
					}
				}
				else
				{
					$this->error["sendEmail"] = "Anhang konnte nicht erzeugt werden (null)";
				}
			}
		}
		if (!$gotoNextStatus) return true;
		if ($gotoNextStatus)
		{
			$widerspruch=$this->obj->GetWiderspruch($this->db);
			if ($widerspruch!=null && $widerspruch->GetLetterSendTime()>0) return true;
			$this->error["sendEmail"] = "Um die Aufgabe abschließen zu können, müssen Sie das Protokoll per E-Mail versenden.";
		}
		return false;
	}
	
	/**
	 * Inject HTML/JavaScript before the UI is send to the browser 
	 */
	public function PrePrint()
	{
		if( trim($_POST["createDownloadFile"])!="" ){
			// Mögliche Änderungen übernehmen
			$this->Store(false);
			$widerspruch=$this->obj->GetWiderspruch($this->db);
			// Includes
			if( $widerspruch!=null )
			{
				$result=$widerspruch->CreateDocument($this->db, (int)$_POST["createDownloadFile"]==DOCUMENT_TYPE_ANSCHREIBEN ? DOCUMENT_TYPE_RTF : DOCUMENT_TYPE_PDF, (int)$_POST["createDownloadFile"]==DOCUMENT_TYPE_ANSCHREIBEN ? DOCUMENT_TYPE_ANSCHREIBEN : DOCUMENT_TYPE_ANHANG );
				if (is_int($result))
				{
					$this->error["createDocument"]="Dokument konnte nicht erzeugt werden (".$result.")";
				}
				else
				{
					// Streamen...
					header('HTTP/1.1 200 OK');
					header('Status: 200 OK');
					header('Accept-Ranges: bytes');
					header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header('Content-Transfer-Encoding: Binary');
					header("Content-type: application/".$result["extension"]);
					header("Content-Disposition: attachment; filename=\"Widerspruch.".$result["extension"]."\"");
					header("Content-Length: ".(string)strlen($result["content"]));
					echo $result["content"];
					exit;
				}
			}
		}
	}
	
	/**
	 * Inject HTML/JavaScript after the UI is send to the browser 
	 */
	public function PostPrint()
	{
		global $DOMAIN_HTTP_ROOT;
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		// Includes
		$letterSendTime = 0;
		if( $widerspruch!=null )
		{
			$letterSendTime = $widerspruch->GetLetterSendTime();
		}
		?>
		<script type="text/javascript">
			<!--
				function DownloadFile(format)
				{
					document.forms.FM_FORM.createDownloadFile.value=format; 
					document.forms.FM_FORM.submit()
					document.forms.FM_FORM.createDownloadFile.value=""; 
				}
				function SendEmail()
				{
				<?	if ($letterSendTime>0){?>
					if (confirm("Das Protokoll wurde bereits am <?=date("d.m.Y", $letterSendTime);?> um <?=date("H:i", $letterSendTime);?> Uhr per E-Mail versendet\n\nMöchten Sie das Protokoll erneut per E-Mail versenden?"))
					{
				<?	}?>
						document.forms.FM_FORM.sendMailButton.disabled = true;
						document.forms.FM_FORM.doSendEmail.value="on"; 
						document.forms.FM_FORM.sendData.value="true";
						document.forms.FM_FORM.forwardToListView.value="false";
						document.forms.FM_FORM.submit();
				<?	if ($letterSendTime>0){?>
					}
				<?	}?>
				}
				$('sender').onfocus = function(){$('sender').blur();}
				$('letterSubject').onfocus = function(){$('letterSubject').blur();}
				$('letter').onfocus = function(){$('letter').blur();}
			-->
		</script>
		<?
	}
	
	/**
	 * Return the following status 
	 */
	public function GetNextStatus()
	{
		return 0;
	}
		
	/**
	 * return if this status of the current process can be edited when in a group
	 * @return boolean
	 */
	public function IsEditableInGroup()
	{
		// only group is editable
		return ($this->IsProcessGroup() ? true : false);
	}
	
}
?>