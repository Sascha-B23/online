<?php
/**
 * Status "Gesprächstermin vereinbaren und dokumentieren"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasGespraechsTerminVereinbaren extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 0 Tage
		$this->obj->SetDeadline(time());
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		global $SHARED_HTTP_ROOT, $customerManager;
		$leaderFms = $this->obj->GetCPersonFmsLeader();
		if ($loadFromObject)
		{
			$this->formElementValues["dateAndTime"]=$this->obj->GetTelefontermin()==0 ? "" : date("d.m.Y", $this->obj->GetTelefontermin());
			$this->formElementValues["dateAndTime_clock"]=$this->obj->GetTelefontermin()==0 ? "" : date("H:i", $this->obj->GetTelefontermin());
			$this->formElementValues["dateAndTimeEnd"]=$this->obj->GetTelefonterminEnde()==0 ? "" : date("d.m.Y", $this->obj->GetTelefonterminEnde());
			$this->formElementValues["dateAndTimeEnd_clock"]=$this->obj->GetTelefonterminEnde()==0 ? "" : date("H:i", $this->obj->GetTelefonterminEnde());			
			$this->formElementValues["ansprechpartner"]=$this->obj->GetTelefonterminAnsprechpartner() == null ? "" : $this->obj->GetTelefonterminAnsprechpartner()->GetAddressIDString();
			// Nebenkostenanalyst als Freigabeperson vorauswählen
			$this->formElementValues["rsuser"] = $leaderFms!=null ? $leaderFms->GetPKey() : "";
			// Terminemail
			$widerspruch = $this->obj->GetWiderspruch($this->db);
			if ($widerspruch!=null)
			{
				/* @var $terminMail TerminMail */
				$terminMail = $widerspruch->GetEMail($this->db, Widerspruch::EMAIL_TYPE_TERMIN);
				if ($terminMail!=null)
				{
					$this->formElementValues["letterCreate"] = ($terminMail->GetSendMail() ? "on" : "");
					$this->formElementValues["recipientCc"] = $terminMail->GetRecipientCc();
					$this->formElementValues["letterSubject"] = $terminMail->GetSubject();
					$this->formElementValues["letterLanguage"] = $terminMail->GetLanguage();
					$this->formElementValues["letter"] = $terminMail->GetText();
					$this->formElementValues["fmsType"] = $terminMail->GetFmsType();
					$this->formElementValues["visibleFor"] = $terminMail->GetFmsVisibleFor();
					$this->formElementValues["alteAntwortfrist"] = date("d.m.Y", $terminMail->GetOldDeadline()!=0 ? $terminMail->GetOldDeadline() : $this->obj->GetAbschlussdatum());
				}
			}
		}
		// Standardtexte laden wenn Felder leer
		if (trim($this->formElementValues["letterLanguage"])=="")
		{
			$this->formElementValues["letterLanguage"] = "DE";
		}
		if (trim($this->formElementValues["letterSubject"])=="")
		{
			$standardText = StandardTextManager::GetStandardTextById($this->db, StandardTextManager::STM_TERMINEMAIL_SUBJECT);
			if ($standardText!=null) $this->formElementValues["letterSubject"] = str_replace("\r", "", str_replace("\n", " ", trim($standardText->GetStandardText($this->formElementValues["letterLanguage"]))));
		}
		if (trim($this->formElementValues["letter"])=="")
		{
			$standardText = StandardTextManager::GetStandardTextById($this->db, StandardTextManager::STM_TERMINEMAIL);
			if ($standardText!=null) $this->formElementValues["letter"] = $standardText->GetStandardText($this->formElementValues["letterLanguage"]);
		}
		// Wenn kein Ansprechpartner gesetzt ist, den Ansprechpartner der Teilabrechnung übernehmen...
		if ($this->formElementValues["ansprechpartner"]=="")
		{
			$teilabrechnung = $this->obj->GetTeilabrechnung($this->db);
			if( $teilabrechnung!=null) $this->formElementValues["ansprechpartner"] = ($teilabrechnung->GetAnsprechpartner() == null ? "" : $teilabrechnung->GetAnsprechpartner()->GetAddressIDString());
		}
		$this->elements[] = new DateAndTimeElement("dateAndTime", "Terminbeginn", Array("date" => $this->formElementValues["dateAndTime"], "time" => $this->formElementValues["dateAndTime_clock"]), true, $this->error["dateAndTime"]);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		$this->elements[] = new DateAndTimeElement("dateAndTimeEnd", "Terminende", Array("date" => $this->formElementValues["dateAndTimeEnd"], "time" => $this->formElementValues["dateAndTimeEnd_clock"]), true, $this->error["dateAndTimeEnd"]);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Ansprechpartner
		$buttons = Array();
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/address_new.png", "help" => "Neuen Ansprechpartner anlegen", "href" => "javascript:CreateNewAddress(".AM_ADDRESSDATA_TYPE_NONE.")");
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany(".AM_ADDRESSDATA_TYPE_NONE.")");
		$this->elements[] = new TextElement("ansprechpartner", "Ansprechpartner", $this->formElementValues["ansprechpartner"], true, $this->error["ansprechpartner"], false, new SearchTextDynamicContent(  $SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 6, "param01" => AM_ADDRESSDATA_TYPE_NONE), "", "ansprechpartner"), $buttons);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		
		// Rückweisungsbegründung für Freigabe anzeigen wenn vorhanden und wenn wir von Status 34 oder 20 kommen (Freigabe durch FMS bzw. Kunde abgelehnt)
		if ($this->obj->GetLastStatus()==34 && $widerspruch!=null && $widerspruch->GetRueckweisungsBegruendungenCount($this->db, RueckweisungsBegruendung::RWB_TYPE_TERMINEMAIL)>0)
		{
			$rueckweisungsBegruendung = $widerspruch->GetRueckweisungsBegruendungen($this->db, RueckweisungsBegruendung::RWB_TYPE_TERMINEMAIL);
			$protokoll=Array();
			for ($a=0; $a<count($rueckweisungsBegruendung); $a++)
			{
				$protokoll[$a]["date"] = $rueckweisungsBegruendung[$a]->GetDatum();
				$protokoll[$a]["username"] = $rueckweisungsBegruendung[$a]->GetUserRelease()==null ? "*GELÖSCHT*" : $rueckweisungsBegruendung[$a]->GetUserRelease()->GetUserName();
				$protokoll[$a]["title"] = "Freigabe abgelehnt";
				$protokoll[$a]["text"] = str_replace("\n", "<br/>", $rueckweisungsBegruendung[$a]->GetBegruendung());
			}
			ob_start();
			include("abbruchProtokoll.inc.php5");
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		
		// Termiemail erzugen
		$this->elements[] = new CheckboxElement("letterCreate", "<br/>Terminemail erzeugen", $this->formElementValues["letterCreate"], false, $this->error["letterCreate"]);		
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
			// Alte Antwoertfrist
			$this->elements[] = new DateElement("alteAntwortfrist", "Alte Antwortfrist Vermieter", $this->formElementValues["alteAntwortfrist"], false, $this->error["alteAntwortfrist"]);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[count($this->elements)-1]->SetWidth(180);
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
			// Beliebige PDF-Dokumente die dem Protokoll zugeordnet werden sollen
			/*if ($widerspruch!=null && isset($_POST["deleteFile_letterAttachement"]) && $_POST["deleteFile_letterAttachement"]!="")
			{
				$fileToDelete=new File($this->db);
				if ($fileToDelete->Load((int)$_POST["deleteFile_letterAttachement"], $this->db))
				{
					$widerspruch->RemoveFile($this->db, $fileToDelete);
				}
			}
			$this->elements[] = new FileElement("letterAttachement", "<br />Zusätzliche Anlagen E-Mail", $this->formElementValues["letterAttachement"], false, $this->error["letterAttachement"], FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE, $widerspruch!=null ? $widerspruch->GetFiles($this->db, FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE) : Array() );
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();*/
			// Zuständiger FMS-Mitarbeiter für Freigabe bzw. Abbruch
			global $userManager;
			$users = $userManager->GetUsers($_SESSION["currentUser"], "", AddressData::TABLE_NAME.".name", 0, 0, 0);
			$options = Array(Array("name" => "Bitte wählen...", "value" => ""));
			for($a=0; $a<count($users); $a++)
			{
				if (!$users[$a]->BelongToGroupBasetype($this->db, UM_GROUP_BASETYPE_RSMITARBEITER) || $users[$a]->GetPKey()==$_SESSION["currentUser"]->GetPKey()) continue;
				$userName = $users[$a]->GetAddressData()==null ? $users[$a]->GetEMail() : $users[$a]->GetAddressData()->GetName()." ".$users[$a]->GetAddressData()->GetFirstName();
				if ($leaderFms!=null && $leaderFms->GetPKey()==$users[$a]->GetPKey()) $userName.=" (BL)";
				$options[] = Array("name" => $userName, "value" => $users[$a]->GetPKey());
			}
			$options[] = Array("name" => "[SFM-Freigabe überspringen]", "value" => -1);
			$this->elements[] = new DropdownElement("rsuser", "SFM-Freigabe der E-Mail durch...", !isset($this->formElementValues["rsuser"]) ? Array() : $this->formElementValues["rsuser"], true, $this->error["rsuser"], $options, false);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		else
		{
			// Ausgeblendete Felder des WS als hidden-Felder ausgeben, damit die Eingabe beim Umschalten des Dokumenttyps nicht verloren gehen!
			ob_start();
			?>
				<input type="hidden" name="letterLanguage" id="letterLanguage" value="<?=$this->formElementValues["letterLanguage"];?>" />
				<input type="hidden" name="letterSubject" id="letterSubject" value="<?=$this->formElementValues["letterSubject"];?>" />
				<input type="hidden" name="letter" id="letter" value="<?=$this->formElementValues["letter"];?>" />
				<input type="hidden" name="rsuser" id="rsuser" value="<?=$this->formElementValues["rsuser"];?>" />
			<?
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		
		// FMS-Schreiben auflisten
		$widerspruche = $this->obj->GetWidersprueche($this->db);
		$rsSchreibenList = Array();
		for ($a=count($widerspruche)-1; $a>=0; $a--)
		{
			$rsSchreibenWS = $widerspruche[$a]->GetFiles($this->db, FM_FILE_SEMANTIC_RSSCHREIBEN, "");
			for ($b=count($rsSchreibenWS)-1; $b>=0; $b--)
			{
				$rsSchreibenList[] = $rsSchreibenWS[$b];
			}
		}
		ob_start();
		?>
		<br />
		<a href="javascript:AddRSSchreiben();" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
		<span>
			<img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/newEntry.png" border="0" alt="" /> <span style="position:relative; top:-6px;">SFM-Schreiben hinzufügen</span>
		</span>
		</a><br/>
		<?
		include("rsSchreiben.inc.php5");
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT, "<br /><br />SFM-Schreiben");
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Entscheidung Folgeprozess
		$options=Array();
		$nextStatus = WorkflowManager::GetProzessStatusForStatusID(15);
		$options[] = Array("name" => $nextStatus["name"], "value" => 1);
		$nextStatus = WorkflowManager::GetProzessStatusForStatusID(4);
		$options[] = Array("name" => $nextStatus["name"], "value" => 2);		
		$this->elements[] = new DropdownElement("nextAction", "Nächste Maßnahme", !isset($this->formElementValues["nextAction"]) ? Array() : $this->formElementValues["nextAction"], false, $this->error["nextAction"], $options, false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Gesprächs-Termin vereinbaren abbrechen
		$this->elements[] = new CheckboxElement("cancelProcess", "Gesprächs-Termin vereinbaren abbrechen", "", false, $this->error["cancelProcess"]);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		global $appointmentManager;
		$terminMail = null;
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			/* @var $terminMail TerminMail */
			$terminMail = $widerspruch->GetEMail($this->db, Widerspruch::EMAIL_TYPE_TERMIN);
		}

		
		// Abbruch
		if ($gotoNextStatus && $this->formElementValues["cancelProcess"]=="on")
		{
			// reset date
			$this->obj->SetTelefontermin(0);
			$this->obj->SetTelefonterminEnde(0);
			$this->obj->SetTelefonterminAnsprechpartner(null);
			if ($terminMail!=null) $terminMail->DeleteMe($this->db);
			$returnValue = $this->obj->Store($this->db);
			if ($returnValue===true)
			{
				$returnValue = $this->obj->UpdateTelefonterminCalendarEntry($this->db, $appointmentManager);
				if ($returnValue===true)
				{
					return true;
				}
				else
				{
					$this->error["misc"][]="Der Termin konnt nicht aktualisiert werden (".$returnValue.")";
				}
			}
			else
			{
				$this->error["misc"][]="Systemfehler (".$returnValue.")";
			}
			return false;
		}
		// Datum Start
		if (trim($this->formElementValues["dateAndTime"])!="" || trim($this->formElementValues["dateAndTime_clock"])!="")
		{
			$tempValue = DateAndTimeElement::GetTimeStamp($this->formElementValues["dateAndTime"], $this->formElementValues["dateAndTime_clock"]);
			if ($tempValue===false) $this->error["dateAndTime"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj und eine gültige Uhrzeit im Format hh:mm ein";
			else $this->obj->SetTelefontermin($tempValue);
		}
		else
		{
			$this->obj->SetTelefontermin(0);
		}
		// Datum Ende
		if (trim($this->formElementValues["dateAndTimeEnd"])!="" || trim($this->formElementValues["dateAndTimeEnd_clock"])!="")
		{
			$tempValue = DateAndTimeElement::GetTimeStamp($this->formElementValues["dateAndTimeEnd"], $this->formElementValues["dateAndTimeEnd_clock"]);
			if ($tempValue===false)
			{
				$this->error["dateAndTimeEnd"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj und eine gültige Uhrzeit im Format hh:mm ein";
			}
			else
			{
				if ($tempValue<$this->obj->GetTelefontermin())
				{
					$this->error["dateAndTimeEnd"]="Das Terminende muss nach dem Terminbeginn liegen";
				}
				else
				{
					$this->obj->SetTelefonterminEnde($tempValue);
				}
			}
		}
		else
		{
			$this->obj->SetTelefonterminEnde(0);
		}
		// Alte Antwortfrist
		$alteAntwortfrist = 0;
		if ($this->formElementValues["alteAntwortfrist"]!="")
		{
			$alteAntwortfrist = DateElement::GetTimeStamp($this->formElementValues["alteAntwortfrist"]);
			if ($alteAntwortfrist===false)
			{
				$this->error["alteAntwortfrist"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			}
		}
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
				$this->obj->SetTelefonterminAnsprechpartner($tempAddressData);
				$this->formElementValues["ansprechpartner"] = $tempAddressData->GetAddressIDString();
			}
		}
		else
		{
			$this->obj->SetTelefonterminAnsprechpartner(null);
		}
		// Terminemail
		if ($terminMail!=null)
		{
			$terminMail->SetSendMail($this->formElementValues["letterCreate"]=='on' ? true : false);
			$terminMail->SetRecipient($this->obj->GetTelefonterminAnsprechpartner());
			//$terminMail->SetSender($this->obj->GetResponsibleRSUser()->GetAddressData());
			$terminMail->SetSender($_SESSION["currentUser"]->GetAddressData());

			$errorMails = "";
			if (!$terminMail->SetRecipientCc($this->formElementValues["recipientCc"], $errorMails))
			{
				$this->error["recipientCc"] = "Die eingegebene(n) E-Mail-Adresse(n) sind ungültig: ".$errorMails;
			}	
			$terminMail->SetLanguage($this->formElementValues["letterLanguage"]);
			$terminMail->SetSubject($this->formElementValues["letterSubject"]);
			$terminMail->SetText($this->formElementValues["letter"]);
			$terminMail->SetFmsType((int)$this->formElementValues["fmsType"]);
			$terminMail->SetFmsVisibleFor((int)$this->formElementValues["visibleFor"]);
			if ($alteAntwortfrist!==false) $terminMail->SetOldDeadline($alteAntwortfrist);
			$returnValue = $terminMail->Store($this->db);
			if ($returnValue!==true)
			{
				$this->error["misc"][]="Terminemail konnte nicht gespeichert werden (".$returnValue.")";
			}
		}
		// Festlegen, wohin nach Status 34 "Terminemail durch FMS freigeben" gesprungen werden soll
		$curStatusData = WorkflowManager::GetProzessStatusForStatusID(14);
		if ($curStatusData!==false)
		{
			$dataToStore = Array();
			$tempData = unserialize($this->obj->GetAdditionalInfo());
			if (is_array($tempData)) $dataToStore = $tempData;
			$dataToStore["useBranchAfterStatus34"] = $curStatusData["nextStatusIDs"][(int)$this->formElementValues["nextAction"]];
			$this->obj->SetAdditionalInfo(serialize($dataToStore));
		}
		else
		{
			$this->error["nextStatusIDs"] = "Die nächste Maßnahme konnte nicht gespeichert werden";
		}
				
		// Bezeichnung
		if (count($this->error)==0)
		{
			$returnValue = $this->obj->Store($this->db);
			if ($returnValue===true)
			{
				// Prüfen, ob in nächsten Status gesprungen werden kann
				if (!$gotoNextStatus) return true;
				if ($this->obj->GetTelefontermin()==0) $this->error["dateAndTime"]="Um die Aufgabe abschließen zu können, müssen Sie den Terminbeginn eingeben.";
				if ($this->obj->GetTelefonterminEnde()==0) $this->error["dateAndTimeEnd"]="Um die Aufgabe abschließen zu können, müssen Sie das Terminende eingeben.";
				if ($this->obj->GetTelefonterminAnsprechpartner()==null) $this->error["ansprechpartner"]="Um die Aufgabe abschließen zu können, müssen Sie den Ansprechpartner hinterlegen.";
				if ($terminMail==null) $this->error["letterCreate"]="Es konnte keine Terminemail im System angelegt werden";
				if ($terminMail!=null && $terminMail->GetSendMail())
				{
					if ($terminMail->GetSender()==null) $this->error["misc"][]="Um die Aufgabe abschließen zu können, muss dem Prozess ein verantwortlicher SFM-Mitarbeiter zugeordnet sein. Dieser wird als Absender für die Terminmail benötigt.";
					if ($terminMail->GetFmsType()==-1) $this->error["fmsType"]="Um die Aufgabe abschließen zu können, müssen Sie die Art des SFM-Schreibens festlegen.";
					if ($terminMail->GetFmsVisibleFor()==-1) $this->error["visibleFor"]="Um die Aufgabe abschließen zu können, müssen Sie eine Wahl treffen.";
					if ($terminMail->GetRecipient()==null) $this->error["ansprechpartner"]="Um die Aufgabe abschließen zu können, müssen Sie den Ansprechpartner hinterlegen.";
					if (trim($terminMail->GetLanguage())=="") $this->error["letterLanguage"]="Um die Aufgabe abschließen zu können, müssen Sie die Sprache der E-Mail festlegen.";
					if (trim($terminMail->GetSubject())=="") $this->error["letterSubject"]="Um die Aufgabe abschließen zu können, müssen Sie einen Betreff eingeben.";
					if (trim($terminMail->GetText())=="") $this->error["letter"]="Um die Aufgabe abschließen zu können, müssen Sie einen Text eingeben.";
					if (count($this->error)==0)
					{
						if ($this->formElementValues["rsuser"]!="" && ((int)$this->formElementValues["rsuser"])!=-1)
						{
							$user = new User($this->db);
							if ($user->Load((int)$this->formElementValues["rsuser"], $this->db)===true && $user->BelongToGroupBasetype($this->db, UM_GROUP_BASETYPE_RSMITARBEITER))
							{
								$this->obj->SetZuweisungUser($user);
							}
							else
							{
								$this->error["rsuser"]="Um die Aufgabe abschließen zu können, müssen Sie einen SFM-Mitarbeiter auswählen, der die Terminmail freigeben soll.";
							}
						}
						else
						{
							$this->obj->SetZuweisungUser(null);
						}
						
						// JumpBack-Status auf aktuellen Status setzen -> für Freigabe
						if (!$this->masterObject->SetJumpBackStatus($this->masterObject->GetCurrentStatus()))
						{
							$this->error["misc"][]="Status für den Rücksprung konnte nicht gesetzt werden";
						}
						else
						{
							$returnValue = $this->obj->Store($this->db);
							if ($returnValue!==true)
							{
								$this->error["misc"][]="Systemfehler in Objekt WorkflowStatus (2/".$returnValue.")";
							}
						}
					}
					if (count($this->error)==0)
					{
						// Wenn keine Fehler aufgetreten sind und eine E-Mail ohne Freigabe versendet werden soll, dies jetzt tun...
						if ((int)$this->formElementValues["rsuser"]==-1)
						{
							$returnValue = $terminMail->SendMail($this->db);
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
				}
				if (count($this->error)==0)
				{
					// Kalendereintrag versenden
					$returnValue = $this->obj->UpdateTelefonterminCalendarEntry($this->db, $appointmentManager);
					if ($returnValue!==true)
					{
						$this->error["misc"][]="Der Termin konnt nicht aktualisiert werden (".$returnValue.")";
					}
					else
					{
						//  Bei Übergang in nächsten Status die "Antwortfrist Vermieter" mit dem "Terminbeginn" überschreiben
						$this->obj->SetAbschlussdatum($this->obj->GetTelefontermin());
						$this->obj->Store($this->db);
					}
				}
				
				if (count($this->error)==0) return true;
			}
			else
			{
				$this->error["misc"][]="Systemfehler (".$returnValue.")";
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
					/* @var $terminMail TerminMail */
					$terminMail = $widerspruch->GetEMail($this->db, Widerspruch::EMAIL_TYPE_TERMIN);
					if ($terminMail!=null)
					{
						$result = $terminMail->CreateDocument($this->db);
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
				function AddRSSchreiben()
				{
				<?	if($widerspruch!=null){?>
						var newWin=window.open('addRSSchreiben.php5?<?=SID;?>&widerspruchID=<?=$widerspruch->GetPKey();?>','_addRSSchreiben','resizable=1,status=1,location=0,menubar=0,scrollbars=0,toolbar=0,height=800,width=1024');
				<?	}else{?>
						alert("Es kann kein SFM-Schreiben angelegt werden, da der Prozess keinen Widerspruch enthält.");
				<?	}?>
					//newWin.moveTo(width,height);
					newWin.focus();					
				}
				
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
				
				// Datum übernehmen
				$('dateAndTime_clock').onchange = function()
				{
						if($('dateAndTimeEnd').value=="")
						{
							$('dateAndTimeEnd').value = $('dateAndTime').value;
						}
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
		$terminMail = null;
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			/* @var $terminMail TerminMail */
			$terminMail = $widerspruch->GetEMail($this->db, Widerspruch::EMAIL_TYPE_TERMIN);
		}
		
		if ($this->formElementValues["cancelProcess"]=="on") return 3;
		if ($terminMail!=null && $terminMail->GetSendMail() && (int)$this->formElementValues["rsuser"]!=-1)
		{
			// Mit Freigabeprozess
			return 0; 
		}
		return $this->formElementValues["nextAction"];
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