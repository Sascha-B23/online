<?php
/**
 * Status "Kunde informieren"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasKundeInformieren extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 7 Tage nach Erhalt
		$this->obj->SetDeadline(time()+60*60*24*7);
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		global $SHARED_HTTP_ROOT, $customerManager;
		if ($loadFromObject)
		{
			// Email
			$widerspruch = $this->obj->GetWiderspruch($this->db);
			if ($widerspruch!=null)
			{
				/* @var $mail InformationMail */
				$mail = $widerspruch->GetEMail($this->db, Widerspruch::EMAIL_TYPE_INFORMATION);
				if ($mail!=null)
				{
					$this->formElementValues["letterCreate"] = ($mail->GetSendMail() ? "on" : "");
					$this->formElementValues["ansprechpartner"] = $mail->GetRecipient()==null ? "" : $mail->GetRecipient()->GetAddressIDString();
					$this->formElementValues["recipientCc"] = $mail->GetRecipientCc();
					$this->formElementValues["letterSubject"] = $mail->GetSubject();
					$this->formElementValues["letterLanguage"] = $mail->GetLanguage();
					$this->formElementValues["letter"] = $mail->GetText();
					$this->formElementValues["fmsType"] = $mail->GetFmsType();
					$this->formElementValues["visibleFor"] = $mail->GetFmsVisibleFor();
				}
			}
		}
		
		// Wenn kein Ansprechpartner gesetzt ist, den Ansprechpartner der Teilabrechnung übernehmen...
		if (trim($this->formElementValues["letterLanguage"])=="")
		{
			$this->formElementValues["letterLanguage"] = "DE";
		}
		if ($this->formElementValues["ansprechpartner"]=="")
		{
			$teilabrechnung = $this->obj->GetTeilabrechnung($this->db);
			if( $teilabrechnung!=null) $this->formElementValues["ansprechpartner"] = ($teilabrechnung->GetAnsprechpartner() == null ? "" : $teilabrechnung->GetAnsprechpartner()->GetAddressIDString());
		}
		// Standardtexte laden wenn Felder leer
		if (trim($this->formElementValues["letterSubject"])=="")
		{
			$standardText = StandardTextManager::GetStandardTextById($this->db, StandardTextManager::STM_INFORMCUSTOMER_SUBJECT);
			if ($standardText!=null) $this->formElementValues["letterSubject"] = str_replace("\r", "", str_replace("\n", " ", trim($standardText->GetStandardText($this->formElementValues["letterLanguage"]))));
		}
		if (trim($this->formElementValues["letter"])=="")
		{
			$standardText = StandardTextManager::GetStandardTextById($this->db, StandardTextManager::STM_INFORMCUSTOMER);
			if ($standardText!=null) $this->formElementValues["letter"] = $standardText->GetStandardText($this->formElementValues["letterLanguage"]);
		}
		
		// Termiemail erzugen
		$this->elements[] = new CheckboxElement("letterCreate", "<br/>Kunde informieren", $this->formElementValues["letterCreate"], false, $this->error["letterCreate"]);		
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		if ($this->formElementValues["letterCreate"]=="on")
		{
			// Sprache der E-Mail
			/* @var $customerManager CustomerManager */
			$availableLanguages = $customerManager->GetLanguages();
			$options=Array();
			foreach ($availableLanguages as $langauge) 
			{
				$options[]=Array("name" => $langauge->GetName(), "value" => $langauge->GetIso639());
			}
			$this->elements[] = new DropdownElement("letterLanguage", "<br />Sprache der E-Mail", $this->formElementValues["letterLanguage"], true, $this->error["letterLanguage"], $options, false);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// Ansprechpartner
			$buttons = Array();
			$buttons[]= Array("width" => 25, "pic" => "pics/gui/address_new.png", "help" => "Neuen Ansprechpartner anlegen", "href" => "javascript:CreateNewAddress(".AM_ADDRESSDATA_TYPE_NONE.")");
			$buttons[]= Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany(".AM_ADDRESSDATA_TYPE_NONE.")");
			$this->elements[] = new TextElement("ansprechpartner", "Ansprechpartner", $this->formElementValues["ansprechpartner"], true, $this->error["ansprechpartner"], false, new SearchTextDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 6, "param01" => AM_ADDRESSDATA_TYPE_NONE), "", "ansprechpartner"), $buttons);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// CC-Empfänger
			$this->elements[] = new TextElement("recipientCc", "E-Mail Empfänger CC", $this->formElementValues["recipientCc"], false, $this->error["recipientCc"], false);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[count($this->elements)-1]->SetWidth(800);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// Betreff
			$this->elements[] = new TextElement("letterSubject", "E-Mail Betreff", $this->formElementValues["letterSubject"], true, $this->error["letterSubject"]);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[count($this->elements)-1]->SetWidth(800);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// Anschreiben
			$this->elements[] = new TextAreaElement("letter", "E-Mail Text", $this->formElementValues["letter"], true, $this->error["letter"]);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[count($this->elements)-1]->SetWidth(800);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// Vorschau Widerspruch
			ob_start();
			?>
				<input type="hidden" name="createDownloadFile" id="createDownloadFile" value="" />
				<input type="button" value="Vorschau anzeigen" onClick="ShowPreview();" class="formButton2"/>
				<?if( trim($this->error["createDocument"])!=""){?><br /><br /><div class="errorText"><?=$this->error["createDocument"];?></div><?}

			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// Speichern als FMS-Art
			$options=Array();
			$options[]=Array("name" => "Bitte wählen...", "value" => -1);
			$options[]=Array("name" => "Protokoll", "value" => FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_PROTOKOLL);
			$options[]=Array("name" => "Aktennotiz", "value" => FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_AKTENNOTIZ);
			$options[]=Array("name" => "Sonstiges", "value" => FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_SONSTIGES);
			$this->elements[] = new DropdownElement("fmsType", "<br />E-Mail nach Versand als SFM-Schreibens folgender Art ablegen", $this->formElementValues["fmsType"], true, $this->error["fmsType"], $options, false);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// Sichtbar für...
			$options=Array();
			$options[]=Array("name" => "Bitte wählen...", "value" => -1);
			$options[]=Array("name" => "SFM Mitarbeiter", "value" => File::FM_FILE_INTERN_YES);
			$options[]=Array("name" => "Alle Benutzer", "value" => File::FM_FILE_INTERN_NO);
			$this->elements[] = new DropdownElement("visibleFor", "Abgelegtes SFM-Schreiben der E-Mail sichtbar für", $this->formElementValues["visibleFor"], true, $this->error["visibleFor"], $options, false);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		
		
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		$mail = null;
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			/* @var $mail TerminMail */
			$mail = $widerspruch->GetEMail($this->db, Widerspruch::EMAIL_TYPE_INFORMATION);
		}
		
		// Mail
		if ($mail!=null)
		{
			$mail->SetSendMail($this->formElementValues["letterCreate"]=='on' ? true : false);
			$mail->SetSender($this->obj->GetResponsibleRSUser()->GetAddressData());
			// Ansprechpartner
			if (trim($this->formElementValues["ansprechpartner"])!="")
			{
				$tempAddressData = AddressManager::GetAddessById($this->db, $this->formElementValues["ansprechpartner"]);
				if ($tempAddressData===null)
				{
					$this->error["ansprechpartner"] = "Der eingegebene Ansprechpartner konnte in der Adressdatenbank nicht gefunden werden";
				}
				else
				{
					$mail->SetRecipient($tempAddressData);
					$this->formElementValues["ansprechpartner"] = $tempAddressData->GetAddressIDString();
				}
			}
			else
			{
				$mail->SetRecipient(null);
			}

			$errorMails = "";
			if (!$mail->SetRecipientCc($this->formElementValues["recipientCc"], $errorMails))
			{
				$this->error["recipientCc"] = "Die eingegebene(n) E-Mail-Adresse(n) sind ungültig: ".$errorMails;
			}	
			$mail->SetLanguage($this->formElementValues["letterLanguage"]);
			$mail->SetSubject($this->formElementValues["letterSubject"]);
			$mail->SetText($this->formElementValues["letter"]);
			$mail->SetFmsType((int)$this->formElementValues["fmsType"]);
			$mail->SetFmsVisibleFor((int)$this->formElementValues["visibleFor"]);
			$returnValue = $mail->Store($this->db);
			if ($returnValue!==true)
			{
				$this->error["misc"][]="Terminemail konnte nicht gespeichert werden (".$returnValue.")";
			}
		}
		
		// Bezeichnung
		if (count($this->error)==0)
		{
			// Prüfen, ob in nächsten Status gesprungen werden kann
			if (!$gotoNextStatus) return true;
			if ($mail==null) $this->error["letterCreate"]="Es konnte keine Terminemail im System angelegt werden";
			if ($mail!=null && $mail->GetSendMail())
			{
				$mail->SetSender($_SESSION["currentUser"]->GetAddressData());
				if ($mail->GetSender()==null) $this->error["misc"][]="Über ihren Zugang können derzeit keine E-Mails versendet werden. Bitte hinterlegen Sie unter 'Meine Daten' ihre Kontaktdaten und versuchen Sie es dann erneut.";
				if ($mail->GetFmsType()==-1) $this->error["fmsType"]="Um die Aufgabe abschließen zu können, müssen Sie eine Wahl treffen.";
				if ($mail->GetFmsVisibleFor()==-1) $this->error["visibleFor"]="Um die Aufgabe abschließen zu können, müssen Sie eine Wahl treffen.";
				if ($mail->GetRecipient()==null) $this->error["ansprechpartner"]="Um die Aufgabe abschließen zu können, müssen Sie den Ansprechpartner hinterlegen.";
				if (trim($mail->GetLanguage())=="") $this->error["letterLanguage"]="Um die Aufgabe abschließen zu können, müssen Sie die Sprache der E-Mail festlegen.";
				if (trim($mail->GetSubject())=="") $this->error["letterSubject"]="Um die Aufgabe abschließen zu können, müssen Sie einen Betreff eingeben.";
				if (trim($mail->GetText())=="") $this->error["letter"]="Um die Aufgabe abschließen zu können, müssen Sie einen Text eingeben.";
				if (count($this->error)==0)
				{
					// Wenn keine Fehler aufgetreten sind, die E-Mail jetzt versenden...
					$returnValue = $mail->SendMail($this->db);
					if ($returnValue!==true)
					{
						switch($returnValue)
						{
							case -4:
								$this->error["ansprechpartner"]="Für diesen Ansprechpartner ist keine E-Mail-Adresse hinterlegt.";
								break;
						}
						$this->error["misc"][]="E-Mail konnte nicht versandt werden (".$returnValue.")";
					}
				}
			}
			if (count($this->error)==0)
			{
				$dataToStore = Array();
				$tempData = unserialize($this->obj->GetAdditionalInfo());
				if (is_array($tempData))$dataToStore = $tempData;
				$this->formElementValues["useBranchAfterStatus35"] = (int)$dataToStore["useBranchAfterStatus35"];
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Inject HTML/JavaScript before the UI is send to the browser 
	 */
	public function PrePrint()
	{
		if( trim($_POST["createDownloadFile"])!="" )
		{
			// Mögliche Änderungen übernehmen
			if ($this->Store(false)===true)
			{
				$widerspruch = $this->obj->GetWiderspruch($this->db);
				// Includes
				if ($widerspruch!=null)
				{
					/* @var $mail TerminMail */
					$mail = $widerspruch->GetEMail($this->db, Widerspruch::EMAIL_TYPE_INFORMATION);
					if ($mail!=null)
					{
						$mail->SetSender($_SESSION["currentUser"]->GetAddressData());
						$result = $mail->CreateDocument($this->db, $this->obj);
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
							header("Content-Disposition: attachment; filename=\"Vorschau.".$result["extension"]."\"");
							header("Content-Length: ".(string)strlen($result["content"]));
							echo $result["content"];
							exit;
						}
					}
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
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		?>
		<script type="text/javascript">
			<!--	
				function CreateNewAddress(type)
				{
					var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/createAddress.php5?<?=SID;?>&type='+type, '_createAddress', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				
				function SetAddress(type, name)
				{
					$('ansprechpartner').value=name;
				}
				
				function CreateNewAddressCompany(type)
				{
					var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/createAddressCompany.php5?<?=SID;?>&type='+type, '_createAddressCompany', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				
				function SetAddressCompany(type, name, addressId, addressCompanyId)
				{
					$('ansprechpartner').value = addressCompanyId;
				}
				
				$('letterCreate').onchange=function()
				{
					document.forms.FM_FORM.submit();
				}
				
				$('letterLanguage').onchange=function()
				{
					if (confirm('Möchten Sie die entsprechenden Vorlagen für die ausgewählte Sprache laden?\n\nDabei gehen der aktuelle E-Mail-Betreff und E-Mail-Text verloren.'))
					{
						document.forms.FM_FORM.letterSubject.value = '';
						document.forms.FM_FORM.letter.value = '';
						document.forms.FM_FORM.submit();
					}
				}
				
				function ShowPreview(format)
				{
					document.forms.FM_FORM.createDownloadFile.value="preview"; 
					document.forms.FM_FORM.submit();
					document.forms.FM_FORM.createDownloadFile.value=""; 
				}
			-->
		</script>
		<?
	}
	
	/**
	 * Return the following status 
	 */
	public function GetNextStatus()
	{
		return $this->formElementValues["useBranchAfterStatus35"];
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