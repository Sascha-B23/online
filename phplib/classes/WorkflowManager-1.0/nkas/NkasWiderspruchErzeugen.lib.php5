<?php
/**
 * Status "Widerspruch/Folgewiderspruch/Protokoll erzeugen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasWiderspruchErzeugen extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 14 Tage ab Auftragsdatum
		$this->obj->SetDeadline($this->obj->GetAuftragsdatumAbrechnung()+60*60*24*14);
		// Rückweisung?
		if ($this->obj->GetLastStatus()==8 || $this->obj->GetLastStatus()==20 || $this->obj->GetLastStatus()==30)
		{
			// Bearbeitungsfrist nach Rückweisung: 1 Tag
			$this->obj->SetDeadline(time() + 60*60*24*1);
		}
		$this->obj->Store($this->db);
		
		// Vorbereitung treffen, wenn in diesen Status gesprungen wird
		if ($this->IsProcessGroup())
		{
			// use selected ProcessStatus on groups (to determine deadline with TA AuftragsdatumAbrechnung)
			$selectedProcess = $this->obj->GetSelectedProcessStatus();
			// create a new (Folge)Widerspruch for the selected process
			if ($selectedProcess!=null && WorkflowManager::GetProcessStatusId($selectedProcess)!=WorkflowManager::GetProcessStatusId($this->obj))
			{
				$widerspruch = $selectedProcess->GetWiderspruch($this->db);
				$this->CreateWiderspruch($selectedProcess, $widerspruch);
			}
			// set selected ProcessStatus to NULL
			$this->obj->SetSelectedProcessStatus(null);
			$this->obj->Store($this->db);
		}
	
		// Widerspruch anlegen (wenn noch keiner existiert) oder Folgewiderspruch anlegen (wenn bereits einer existiert und dieser versendet wurde [erkennt man anhand des hochgeladenen PDFs])
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		if ($widerspruch==null || $widerspruch->GetFileCount($this->db, FM_FILE_SEMANTIC_WIDERSPRUCH)>0)
		{
			$this->CreateWiderspruch($this->obj, $widerspruch);
			// Bei Folgewiderspruch ist Bearbeitungsfrist 14 Tage ab jetzt!
			if ($widerspruch!=null)
			{
				$this->obj->SetDeadline(time()+60*60*24*14);
				$this->obj->Store($this->db);
			}
		}
		
	}
	
	/**
	 * Create a new Widerspruch for the passed process
	 * @param ProcessStatus $processStatus
	 * @param Widerspruch $widerspruch
	 */
	protected function CreateWiderspruch(ProcessStatus $processStatus, Widerspruch $widerspruch=null)
	{
		$autoGenerate=false;
		if ($widerspruch==null)
		{
			// Widerspruch erzeugen
			$widerspruch = new Widerspruch($this->db);
			$autoGenerate = true;
		}
		else
		{
			// Folgewiderspruch erzeugen
			$widerspruch = $widerspruch->CreateFolgewiderspruch($this->db);
		}
		$processStatus->AddWiderspruch($this->db, $widerspruch);
		$widerspruch->Store($this->db);
		// Widerspruchsgenerator ausführen (nur bei neuem Widerspruch und nicht bei Folgewidersprüchen)?
		// Wurde am 16.04.2013 deaktiviert -> Punkt "Vereinfachung: WS wird erst durch Button erzeugt"
		/*if ($autoGenerate)
		{
			$location = $processStatus->GetLocation();
			if ($location!=null)
			{
				$wg = WiderspruchsGenerator::GetWiderspruchsGeneratorForCountry($this->db, $location->GetCountry());
				$wg->CreateWiderspruchspunkte($widerspruch);
			}
		}*/
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		global $SHARED_HTTP_ROOT, $customerManager;
		// Widerspruch-Objekt holen
		/** @var Widerspruch $widerspruch */
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		// Nebenkostenanalyst holen
		$leaderFms = $this->obj->GetCPersonFmsLeader();
		
		if( $loadFromObject )
		{
			$tempData = unserialize($this->obj->GetAdditionalInfo());
			if (is_array($tempData))
			{
				$this->formElementValues["useBranchAfterStatus21"] = $tempData["useBranchAfterStatus21"];
			}

			if( $widerspruch!=null )
			{
				$this->formElementValues["upToDate"] = $widerspruch->IsUpToDate() ? "on" : "";
				//$this->formElementValues["footer"]=$widerspruch->GetFooter();
				//$this->formElementValues["ansprechpartner"] = $widerspruch->GetAnsprechpartner()!=null ? $widerspruch->GetAnsprechpartner()->GetAddressIDString() : "";
				if( trim($this->formElementValues["ansprechpartner"])=="" )
				{
					$this->formElementValues["ansprechpartner"] = $this->obj->GetAnsprechpartner()!=null ? $this->obj->GetAnsprechpartner()->GetAddressIDString() : "";
				}
				// Unterschrift 1 = Ansprechpartner Kunde
				$this->formElementValues["unterschrift1"] = $widerspruch->GetUnterschrift1();
				$this->formElementValues["funktionUnterschrift1"] = $widerspruch->GetFunktionUnterschrift1();
				if( trim($this->formElementValues["unterschrift1"])=="" )
				{
					$cPerson = $this->obj->GetResponsibleCustomer();
					if ($cPerson!=null)
					{
						/* @var $cPerson User */
						$this->formElementValues["unterschrift1"] = $cPerson->GetUserName();
						$addressData = $cPerson->GetAddressData();
						if ($addressData!=null && $this->formElementValues["funktionUnterschrift1"]=='')
						{
							$this->formElementValues["funktionUnterschrift1"] = $addressData->GetRole();
						}
					}
				}
				// Unterschrift 2 = Vorgesetzter des Ansprechpartner Kunde
				$this->formElementValues["unterschrift2"] = $widerspruch->GetUnterschrift2();
				$this->formElementValues["funktionUnterschrift2"] = $widerspruch->GetFunktionUnterschrift2();
				$this->formElementValues["customerComment"] = $widerspruch->GetBemerkungFuerKunde();
				$this->formElementValues["wsType"] = $widerspruch->GetDokumentenTyp();
				$this->formElementValues["letterLanguage"] = $widerspruch->GetLetterLanguage();
				$this->formElementValues["letterSubject"] = $widerspruch->GetLetterSubject();
				$this->formElementValues["letter"] = $widerspruch->GetLetter();
				$this->formElementValues["sendProtocolAsAttachment"] = ($widerspruch->GetSendProtocolAsAttachemnt() ? "on" : "");
				$this->formElementValues["attachementForCustomerVisible"] = ($widerspruch->GetHideAttachemntFromCustomer() ? "" : "on");
			}
			// Nebenkostenanalyst als Freigabeperson vorauswählen
			$this->formElementValues["rsuser"] = $leaderFms!=null ? $leaderFms->GetPKey() : "";
		}
		if (trim($this->formElementValues["letterLanguage"])=="")
		{
			$this->formElementValues["letterLanguage"] = "DE";
		}
		// Standardtexte laden wenn Felder leer
		if (trim($this->formElementValues["letterSubject"])=="")
		{
			$company = $this->obj->GetCompany();
			$standardText = ($company!=null ? $company->GetAnschreibenVorlageBetreff() : null);
			if ($standardText==null) $standardText = StandardTextManager::GetStandardTextById($this->db, StandardTextManager::STM_WSANSCHREIBEN_SUBJECT);
			if ($standardText!=null) 
			{
				$this->formElementValues["letterSubject"] = $standardText->GetStandardText($this->formElementValues["letterLanguage"]);
			}
		}
		if (trim($this->formElementValues["letter"])=="")
		{
			$company = $this->obj->GetCompany();
			$standardText = ($company!=null ? $company->GetAnschreibenVorlageText() : null);
			if ($standardText==null) $standardText = StandardTextManager::GetStandardTextById($this->db, StandardTextManager::STM_WSANSCHREIBEN);
			if ($standardText!=null) 
			{
				$this->formElementValues["letter"] = $standardText->GetStandardText($this->formElementValues["letterLanguage"]);
			}
		}
		
		// Widerspruchsgenerator starten (nur auf Prozessebene)
		if ($this->formElementValues["wgTest"]=="true" && !$this->IsProcessGroup())
		{
			$location = $this->obj->GetLocation();
			if ($location!=null)
			{
				$wg=WiderspruchsGenerator::GetWiderspruchsGeneratorForCountry($this->db, $location->GetCountry());
				$wg->CreateWiderspruchspunkte($widerspruch);
			}
		}
		
		// Mieteinbehalt möglich?
		$contract = ($widerspruch!=null ? $widerspruch->GetContract() : null);
		if ($contract!=null && $contract->GetZurueckBehaltungAusgeschlossen()==Contract::CONTRACT_NO)
		{
			$info=Array('type' => 0, 'text' => 'Mieteinbehalt ist lt. Vertrag möglich');
			ob_start();
			include("info.inc.php5");
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		
		// Rückweisungsbegründung für Abbruch anzeigen wenn vorhanden und wir von Status 6 kommen
		if( $this->obj->GetLastStatus()==6 && $widerspruch!=null &&  $widerspruch->GetLastAbbruchProtokoll($this->db)!=null ){
			$abbruchProtokoll=$widerspruch->GetLastAbbruchProtokoll($this->db);
			$protokoll=Array();
			$protokoll[0]["date"]=$abbruchProtokoll->GetDatumAbbruch();
			$protokoll[0]["username"]=$abbruchProtokoll->GetUser()==null ? "-" : $abbruchProtokoll->GetUser()->GetUserName();
			$protokoll[0]["title"]="Prozess abgebrochen";
			$protokoll[0]["text"]=str_replace("\n", "<br/>", $abbruchProtokoll->GetBegruendung());
			$protokoll[1]["date"]=$abbruchProtokoll->GetDatumAblehnung();
			$protokoll[1]["username"]=$abbruchProtokoll->GetUserRelease()==null ? "-" : $abbruchProtokoll->GetUserRelease()->GetUserName();
			$protokoll[1]["title"]="Abbruch abgelehnt";
			$protokoll[1]["text"]=str_replace("\n", "<br/>", $abbruchProtokoll->GetAblehnungsbegruendung());
			ob_start();
			include("abbruchProtokoll.inc.php5");
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		// Rückweisungsbegründung für Freigabe anzeigen wenn vorhanden und wenn wir von Status 8 oder 20 kommen (Freigabe durch FMS bzw. Kunde abgelehnt)
		if( ($this->obj->GetLastStatus()==8 || $this->obj->GetLastStatus()==30 || $this->obj->GetLastStatus()==20) && $widerspruch!=null  && count($widerspruch->GetRueckweisungsBegruendungen($this->db, RueckweisungsBegruendung::RWB_TYPE_WIDERSPRUCH))>0 ){
			$rueckweisungsBegruendung=$widerspruch->GetRueckweisungsBegruendungen($this->db, RueckweisungsBegruendung::RWB_TYPE_WIDERSPRUCH);
			$protokoll=Array();
			for( $a=0; $a<count($rueckweisungsBegruendung); $a++){
				$protokoll[$a]["date"]=$rueckweisungsBegruendung[$a]->GetDatum();
				$protokoll[$a]["username"]=$rueckweisungsBegruendung[$a]->GetUserRelease()==null ? "*GELÖSCHT*" : $rueckweisungsBegruendung[$a]->GetUserRelease()->GetUserName();
				$protokoll[$a]["title"]="Freigabe abgelehnt";
				$protokoll[$a]["text"]=str_replace("\n", "<br/>", $rueckweisungsBegruendung[$a]->GetBegruendung());
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
		
		// Widerspruchspunkte (nur auf Prozessebene)
		if (!$this->IsProcessGroup())
		{
			$this->elements[] = new ListElement("widerspruchspunkte", "Widerspruchspunkte", $this->formElementValues["widerspruchspunkte"], false, $this->error["widerspruchspunkte"], false, new WiderspruchspunktListData($this->db, $widerspruch));
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			ob_start();
			?>
			<input type="hidden" name="wgTest" id="wgTest" value="">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td valign="top"><a href="javascript:$('wgTest').value='true'; document.forms.FM_FORM.submit();" style="text-decoration:none;" onfocus="if (this.blur) this.blur()"><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/teilabrechnung.png" border="0" alt="" /> <span style="position:relative; top:-6px;"></a></td>
					<td><a href="javascript:$('wgTest').value='true'; document.forms.FM_FORM.submit();" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">Widerspruchspunkte automatisch erzeugen</a></td>
				</tr>
			</table>
			<?
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);

			ob_start();
			?>
			<a href="javascript:EditKb();" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
			<span style="position:relative; left:30px;">
				<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/teilabrechnung.png" border="0" alt="" /> <span style="position:relative; top:-6px;">Kürzungsbeträge bearbeiten</span>
			</span>
			</a><br><br>
			<?
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);

			ob_start();
			?>
			<a href="javascript:CreateNewPos();" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
			<span style="position:relative; left:30px;">
				<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/teilabrechnung.png" border="0" alt="" /> <span style="position:relative; top:-6px;">Widerspruchspunkt hinzufügen</span>
			</span>
			</a><br><br>
			<?
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
		}
		
		// WS-Typ
		$options=Array(Array("name" => "Bitte wählen...", "value" => Widerspruch::DOKUMENTEN_TYP_UNDEFINED));
		$options[]=Array("name" => "Widerspruch/Folgewiderspruch", "value" => Widerspruch::DOKUMENTEN_TYP_WIDERSPRUCH);
		$options[]=Array("name" => "Protokoll", "value" => Widerspruch::DOKUMENTEN_TYP_PROTOKOLL);
		$options[]=Array("name" => "Protokollentwurf", "value" => Widerspruch::DOKUMENTEN_TYP_PROTOKOLLENTWURF);
		$this->elements[] = new DropdownElement("wsType", "Dokumententyp", $this->formElementValues["wsType"], true, $this->error["wsType"], $options, false);
		$this->elements[] = new BlankElement();
		$this->elements[] = new CheckboxElement("upToDate", "Ampel bereits auf Stand", $this->formElementValues["upToDate"], false, $this->error["upToDate"]);

		// Ansprechpartner
		$buttons = Array();
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/address_new.png", "help" => "Neuen Ansprechpartner anlegen", "href" => "javascript:CreateNewAddress(".AM_ADDRESSDATA_TYPE_NONE.")");
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany(".AM_ADDRESSDATA_TYPE_NONE.")");
		$this->elements[] = new TextElement("ansprechpartner", "<br/>Ansprechpartner für Widerspruch", $this->formElementValues["ansprechpartner"], true, $this->error["ansprechpartner"], false, new SearchTextDynamicContent(  $SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 6, "param01" => AM_ADDRESSDATA_TYPE_NONE), "", "ansprechpartner"), $buttons);
		$this->elements[] = new BlankElement();
		// Vorschau Widerspruch
		ob_start();
		if ($this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_WIDERSPRUCH || !$this->IsProcessGroup())
		{?>
			<strong>Vorschau Widerspruch anzeigen</strong><br>
			<input type="hidden" name="createDownloadFile" id="createDownloadFile" value="" />
			<?if ($this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_WIDERSPRUCH){?><input type="button" value="Anschreiben" onClick="DownloadFile(<?=DOCUMENT_TYPE_ANSCHREIBEN;?>);" class="formButton2"/>&#160;&#160;&#160;<?}?><?if (!$this->IsProcessGroup()){?><input type="button" value="Anlage" onClick="DownloadFile(<?=DOCUMENT_TYPE_ANHANG;?>);" class="formButton2"/><?}?>
			<?if ($this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_PROTOKOLL){?><input type="checkbox" name="sendProtocolAsAttachment" id="sendProtocolAsAttachment" <?if($this->formElementValues["sendProtocolAsAttachment"]=="on")echo "checked"?> />An E-Mail anhängen<?}?>
			<?if ($this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_WIDERSPRUCH && !$this->IsProcessGroup()){?><br /><input type="checkbox" name="attachementForCustomerVisible" id="attachementForCustomerVisible" <?if($this->formElementValues["attachementForCustomerVisible"]=="on")echo "checked"?> />Anlage für Kunde sichtbar<?}?>
			<?if( trim($this->error["createDocument"])!=""){?><br /><br /><div class="errorText"><?=$this->error["createDocument"];?></div><?}
		}
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		
		
		// UI abhängig vom Dokumententyp
		if ($this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_WIDERSPRUCH)
		{
			/** Widerspruch **/
			// Sprache der E-Mail
			/* @var $customerManager CustomerManager */
			$availableLanguages = $customerManager->GetLanguages();
			$options=Array();
			foreach ($availableLanguages as $langauge) 
			{
				$options[]=Array("name" => $langauge->GetName(), "value" => $langauge->GetIso639());
			}
			$this->elements[] = new DropdownElement("letterLanguage", "Sprache der E-Mail", $this->formElementValues["letterLanguage"], true, $this->error["letterLanguage"], $options, false);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// Betreff
			$this->elements[] = new TextElement("letterSubject", "Anschreiben Betreff", $this->formElementValues["letterSubject"], true, $this->error["letterSubject"]);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[count($this->elements)-1]->SetWidth(800);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// Anschreiben
			$this->elements[] = new TextAreaElement("letter", "Anschreiben Text", $this->formElementValues["letter"], true, $this->error["letter"]);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[count($this->elements)-1]->SetWidth(800);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			$this->elements[] = new TextElement("unterschrift1", "<br/>Linksunterzeichner", $this->formElementValues["unterschrift1"], true, $this->error["unterschrift1"]);
			$this->elements[] = new TextElement("unterschrift2", "<br/>Rechtsunterzeichner", $this->formElementValues["unterschrift2"], false, $this->error["unterschrift2"]);
			$this->elements[] = new BlankElement();	
			$this->elements[] = new TextElement("funktionUnterschrift1", "<br/>Funktion Linksunterzeichner", $this->formElementValues["funktionUnterschrift1"], false, $this->error["funktionUnterschrift1"]);
			$this->elements[] = new TextElement("funktionUnterschrift2", "<br/>Funktion Rechtsunterzeichner", $this->formElementValues["funktionUnterschrift2"], false, $this->error["funktionUnterschrift2"]);
			$this->elements[] = new BlankElement();
			// Bemerkungsfeld für den Kunden
			$this->elements[] = new TextAreaElement("customerComment", "<br/>Bemerkungsfeld für Kunde", $this->formElementValues["customerComment"], false, $this->error["customerComment"]);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[count($this->elements)-1]->SetWidth(800);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// Festlegen, wohin nach Status 21 "Widerspruch drucken/versenden/hochladen" gesprungen werden soll
			$options=Array();
			$info21 = WorkflowManager::GetProzessStatusForStatusID(21);
			for ($a=0; $a<count($info21["nextStatusIDs"]); $a++)
			{
				$infoTemp = WorkflowManager::GetProzessStatusForStatusID($info21["nextStatusIDs"][$a]);
				$options[] = Array("name" => $infoTemp["name"], "value" => $a);
			}
			$this->elements[] = new DropdownElement("useBranchAfterStatus21", "<br />Nächste Maßnahme nachdem Kunde WS hochgeladen hat", $this->formElementValues["useBranchAfterStatus21"], true, $this->error["useBranchAfterStatus21"], $options);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// Ausgeblendete Felder des WS als hidden-Felder ausgeben, damit die Eingabe beim Umschalten des Dokumenttyps nicht verloren gehen!
			ob_start();
			?>
				<input type="hidden" name="sendProtocolAsAttachment" id="sendProtocolAsAttachment" value="<?=$this->formElementValues["sendProtocolAsAttachment"];?>" />
			<?
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			
			// Beliebige PDF-Dokumente die dem Widerspruch zugeordnet werden sollen
			if( $widerspruch!=null && isset($_POST["deleteFile_wFile"]) && $_POST["deleteFile_wFile"]!="" ){
				$fileToDelete=new File($this->db);
				if( $fileToDelete->Load((int)$_POST["deleteFile_wFile"], $this->db) ){
					$widerspruch->RemoveFile($this->db, $fileToDelete);
				}
			}
			$this->elements[] = new FileElement("wFile", "<br/>Zusätzliche Anlagen Widerspruch", $this->formElementValues["wFile"], false, $this->error["wFile"], FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE, $widerspruch!=null ? $widerspruch->GetFiles($this->db, FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE) : Array() );
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		elseif ($this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_PROTOKOLL || $this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_PROTOKOLLENTWURF)
		{
			/** Protokoll **/
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
			// Betreff
			$this->elements[] = new TextElement("letterSubject", "Anschreiben Betreff", $this->formElementValues["letterSubject"], true, $this->error["letterSubject"]);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[count($this->elements)-1]->SetWidth(800);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// Anschreiben
			$this->elements[] = new TextAreaElement("letter", "Anschreiben Text", $this->formElementValues["letter"], true, $this->error["letter"]);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[count($this->elements)-1]->SetWidth(800);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			// Ausgeblendete Felder des WS als hidden-Felder ausgeben, damit die Eingabe beim Umschalten des Dokumenttyps nicht verloren gehen!
			ob_start();
			?>
			<?	if ($this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_PROTOKOLLENTWURF){ ?>
					<input type="hidden" name="sendProtocolAsAttachment" id="sendProtocolAsAttachment" value="<?=$this->formElementValues["sendProtocolAsAttachment"];?>" />
			<?	}?>
				<input type="hidden" name="attachementForCustomerVisible" id="attachementForCustomerVisible" value="<?=$this->formElementValues["attachementForCustomerVisible"];?>" />
				<input type="hidden" name="unterschrift1" id="unterschrift1" value="<?=$this->formElementValues["unterschrift1"];?>" />
				<input type="hidden" name="unterschrift2" id="unterschrift2" value="<?=$this->formElementValues["unterschrift2"];?>" />
				<input type="hidden" name="funktionUnterschrift1" id="funktionUnterschrift1" value="<?=$this->formElementValues["funktionUnterschrift1"];?>" />
				<input type="hidden" name="funktionUnterschrift2" id="funktionUnterschrift2" value="<?=$this->formElementValues["funktionUnterschrift2"];?>" />
				<textarea name="customerComment"  id="customerComment" style="width: 1px; height: 1px; visibility: hidden;"><?=$this->formElementValues["customerComment"];?></textarea>
				<input type="hidden" name="useBranchAfterStatus21" id="useBranchAfterStatus21" value="<?=$this->formElementValues["useBranchAfterStatus21"];?>" />
			<?
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			
			// Beliebige PDF-Dokumente die dem Protokoll zugeordnet werden sollen
			if( $widerspruch!=null && isset($_POST["deleteFile_wFile"]) && $_POST["deleteFile_wFile"]!="" ){
				$fileToDelete=new File($this->db);
				if( $fileToDelete->Load((int)$_POST["deleteFile_wFile"], $this->db) ){
					$widerspruch->RemoveFile($this->db, $fileToDelete);
				}
			}
			$this->elements[] = new FileElement("wFile", "<br/>Zusätzliche Anlagen Protokoll", $this->formElementValues["wFile"], false, $this->error["wFile"], FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE, $widerspruch!=null ? $widerspruch->GetFiles($this->db, FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE) : Array() );
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		else
		{
			/** Protokollentwurf **/
			// Ausgeblendete Felder des WS als hidden-Felder ausgeben, damit die Eingabe beim Umschalten des Dokumenttyps nicht verloren gehen!
			ob_start();
			?>
				<input type="hidden" name="sendProtocolAsAttachment" id="sendProtocolAsAttachment" value="<?=$this->formElementValues["sendProtocolAsAttachment"];?>" />
				<input type="hidden" name="attachementForCustomerVisible" id="attachementForCustomerVisible" value="<?=$this->formElementValues["attachementForCustomerVisible"];?>" />
				<input type="hidden" name="letterLanguage" id="letterLanguage" value="<?=$this->formElementValues["letterLanguage"];?>" />
				<input type="hidden" name="letterSubject" id="letterSubject" value="<?=$this->formElementValues["letterSubject"];?>" />
				<textarea name="letter" id="letter" style="width: 1px; height: 1px; visibility: hidden;"><?=$this->formElementValues["letter"];?></textarea>
				<input type="hidden" name="unterschrift1" id="unterschrift1" value="<?=$this->formElementValues["unterschrift1"];?>" />
				<input type="hidden" name="unterschrift2" id="unterschrift2" value="<?=$this->formElementValues["unterschrift2"];?>" />
				<input type="hidden" name="funktionUnterschrift1" id="funktionUnterschrift1" value="<?=$this->formElementValues["funktionUnterschrift1"];?>" />
				<input type="hidden" name="funktionUnterschrift2" id="funktionUnterschrift2" value="<?=$this->formElementValues["funktionUnterschrift2"];?>" />
				<textarea name="customerComment" id="customerComment" style="width: 1px; height: 1px; visibility: hidden;"><?=$this->formElementValues["customerComment"];?></textarea>
				<input type="hidden" name="useBranchAfterStatus21" id="useBranchAfterStatus21" value="<?=$this->formElementValues["useBranchAfterStatus21"];?>" />
			<?
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			
			// Beliebige PDF-Dokumente die dem Protokoll zugeordnet werden sollen
			if( $widerspruch!=null && isset($_POST["deleteFile_wFile"]) && $_POST["deleteFile_wFile"]!="" ){
				$fileToDelete=new File($this->db);
				if( $fileToDelete->Load((int)$_POST["deleteFile_wFile"], $this->db) ){
					$widerspruch->RemoveFile($this->db, $fileToDelete);
				}
			}
			$this->elements[] = new FileElement("wFile", "<br/>Zusätzliche Anlagen Protokoll", $this->formElementValues["wFile"], false, $this->error["wFile"], FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE, $widerspruch!=null ? $widerspruch->GetFiles($this->db, FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE) : Array() );
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		// Zuständiger FMS-Mitarbeiter für Freigabe bzw. Abbruch
		global $userManager;
		$users=$userManager->GetUsers($_SESSION["currentUser"], "", AddressData::TABLE_NAME.".name", 0, 0, 0);
		$options=Array(Array("name" => "Bitte wählen...", "value" => ""));
		for($a=0; $a<count($users); $a++){
			if( !$users[$a]->BelongToGroupBasetype($this->db, UM_GROUP_BASETYPE_RSMITARBEITER) || $users[$a]->GetPKey()==$_SESSION["currentUser"]->GetPKey() )continue;
			$userName=$users[$a]->GetAddressData()==null ? $users[$a]->GetEMail() : $users[$a]->GetAddressData()->GetName()." ".$users[$a]->GetAddressData()->GetFirstName();
			if ($leaderFms!=null && $leaderFms->GetPKey()==$users[$a]->GetPKey()) $userName.=" (BL)";
			$options[]=Array("name" => $userName, "value" => $users[$a]->GetPKey());
		}
		$options[]=Array("name" => "[SFM-Freigabe überspringen]", "value" => -1);
		$this->elements[] = new DropdownElement("rsuser", "<br/>SFM-Freigabe durch...", !isset($this->formElementValues["rsuser"]) ? Array() : $this->formElementValues["rsuser"], true, $this->error["rsuser"], $options, false);
		// Abbruch
		$this->elements[] = new CheckboxElement("cancelProcess", "<br/>Prüfung wegen Geringfügigkeit abbrechen", "", false, $this->error["cancelProcess"]);
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		global $addressManager;
		// Beliebige PDF-Dokumente die dem Widerspruch zugeordnet werden sollen
		/** @var Widerspruch $widerspruch */
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		$widerspruch->SetUpToDate($this->formElementValues["upToDate"]=="on" ? true : false);
		//$widerspruch->SetFooter($this->formElementValues["footer"]);
		$widerspruch->SetUnterschrift1($this->formElementValues["unterschrift1"]);
		$widerspruch->SetUnterschrift2($this->formElementValues["unterschrift2"]);
		$widerspruch->SetFunktionUnterschrift1($this->formElementValues["funktionUnterschrift1"]);
		$widerspruch->SetFunktionUnterschrift2($this->formElementValues["funktionUnterschrift2"]);
		$widerspruch->SetBemerkungFuerKunde($this->formElementValues["customerComment"]);
		$widerspruch->SetDokumentenTyp((int)$this->formElementValues["wsType"]);
		$widerspruch->SetLetterLanguage($this->formElementValues["letterLanguage"]);
		$widerspruch->SetLetterSubject($this->formElementValues["letterSubject"]);
		$widerspruch->SetLetter($this->formElementValues["letter"]);
		$widerspruch->SetSendProtocolAsAttachemnt($this->formElementValues["sendProtocolAsAttachment"]=="on" ? true : false);
		$widerspruch->SetHideAttachemntFromCustomer($this->formElementValues["attachementForCustomerVisible"]=="on" ? false : true);
		$widerspruch->SetLetterSendTime(0);
		
		// Ansprechpartner für WS
		if (trim($this->formElementValues["ansprechpartner"])!="")
		{
			$tempAddressData = AddressManager::GetAddessById($this->db, $this->formElementValues["ansprechpartner"]);
			if ($tempAddressData===null)
			{
				$this->error["ansprechpartner"] = "Der eingegebene Ansprechpartner konnte nicht gefunden werden";
			}
			else
			{
				$widerspruch->SetAnsprechpartner($tempAddressData);
				$this->formElementValues["ansprechpartner"] = $tempAddressData->GetAddressIDString();
			}
		}
		else
		{
			$widerspruch->SetAnsprechpartner(null);
		}
		
		$fileSemantic = FM_FILE_SEMANTIC_UNKNOWN;
		if ($this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_WIDERSPRUCH) $fileSemantic = FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE;
		elseif ($this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_PROTOKOLL || $this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_PROTOKOLLENTWURF) $fileSemantic = FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE;
		if ($fileSemantic!=FM_FILE_SEMANTIC_UNKNOWN)
		{
			$fileObject=FileElement::GetFileElement($this->db, $_FILES["wFile"], $fileSemantic);
			if (!is_object($fileObject) || get_class($fileObject)!="File")
			{
				if ($fileObject!=-1)
				{
					$this->error["wFile"]=FileElement::GetErrorText($fileObject);
				}
			}
			else
			{
				// Datei wieder löschen, wenn ein Fehler
				if( count($this->error)>0 )$fileObject->DeleteMe($this->db);
				else $widerspruch->AddFile($this->db, $fileObject);
			}
		}
		
		// Fehler aufgetreten?
		if (count($this->error)==0)
		{
			$returnValue=$widerspruch->Store($this->db);
			if ($returnValue===true)
			{
				if ($gotoNextStatus)
				{
					$user=null;
					if ($this->formElementValues["rsuser"]!="" && ((int)$this->formElementValues["rsuser"])!=-1)
					{
						$user = new User($this->db);
						if ($user->Load((int)$this->formElementValues["rsuser"], $this->db)!==true || !$user->BelongToGroupBasetype($this->db, UM_GROUP_BASETYPE_RSMITARBEITER))
						{
							$user = null;
						}
					}
					$this->obj->SetZuweisungUser($user);
				}
				// Festlegen, wohin nach Status 21 "Widerspruch drucken/versenden/hochladen" gesprungen werden soll
				$dataToStore = Array();
				$dataToStore["useBranchAfterStatus21"] = (int)$this->formElementValues["useBranchAfterStatus21"];
				$this->obj->SetAdditionalInfo(serialize($dataToStore));
				// Speichern
				$returnValue=$this->obj->Store($this->db);
				if ($returnValue===true)
				{
					if (!$gotoNextStatus) return true;
					// Prüfen, ob in nächsten Status gesprungen werden kann
					if ($this->formElementValues["cancelProcess"]!="on")
					{
						// Es soll in den nächsten Status gesprungen werden
						if ($widerspruch->GetDokumentenTyp()==Widerspruch::DOKUMENTEN_TYP_UNDEFINED) $this->error["wsType"] = "Um die Aufgabe abschließen zu können, müssen Sie den Typ des Dokumentes bestimmen.";
						if ($widerspruch->GetDokumentenTyp()!=Widerspruch::DOKUMENTEN_TYP_PROTOKOLLENTWURF && $this->obj->GetZuweisungUser()==null && ((int)$this->formElementValues["rsuser"])!=-1) $this->error["rsuser"]="Um die Aufgabe abschließen zu können, müssen Sie einen SFM-Mitarbeiter auswählen, der den Widerspruch/das Protokoll freigeben soll.";
						if (trim($widerspruch->GetUnterschrift1())=="") $this->error["unterschrift1"]="Um die Aufgabe abschließen zu können, müssen Sie den Namen der Person eintragen, welche den Widerspruch unterschreibt.";
						if ($widerspruch->GetAnsprechpartner()==null) $this->error["ansprechpartner"]="Um die Aufgabe abschließen zu können, müssen Sie den Ansprechpartner festlegen (Verwalter, Anwalt oder Eigentümer).";
					}
					if (count($this->error)==0)
					{
						// JumpBack-Status auf aktuellen Status setzen -> für Freigabe UND Abbruch
						if (!$this->masterObject->SetJumpBackStatus($this->masterObject->GetCurrentStatus()))
						{
							$this->error["misc"][]="Status für den Rücksprung konnte nicht gesetzt werden";
						}
						else
						{
							$returnValue = $this->obj->Store($this->db);
							if ($returnValue!==true) $this->error["misc"][]="Systemfehler in Objekt WorkflowStatus (2/".$returnValue.")";
						}
					}
					if (count($this->error)==0) return true;
				}
				else
				{
					$this->error["misc"][]="Systemfehler in Objekt WorkflowStatus (1/".$returnValue.")";
				}
			}
			else
			{
				$this->error["misc"][]="Systemfehler in Objekt Widerspruch (".$returnValue.")";
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
			$this->Store(false);
			$widerspruch = $this->obj->GetWiderspruch($this->db);
			// Includes
			if ($widerspruch!=null)
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
		?>
		<script type="text/javascript">
			<!--
			<?	if (!$this->IsProcessGroup()){?>
					function CreateNewPos()
					{
						var newWin=window.open('editWiderspruchspunkt.php5?<?=SID;?>&widerspruchID=<?=$this->obj->GetWiderspruch($this->db)->GetPKey();?>','_createEditWiderspruchspunkt','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=900,width=1050');
						//newWin.moveTo(width,height);
						newWin.focus();
					}

					function EditKb()
					{
						var newWin=window.open('editKbs.php5?<?=SID;?>&editElement=<?=$this->obj->GetWiderspruch($this->db)->GetPKey();?>','_editKbs','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=900,width=1050');
						//newWin.moveTo(width,height);
						newWin.focus();
					}

					function EditPos(posID)
					{
						var newWin=window.open('editWiderspruchspunkt.php5?<?=SID;?>&editElement='+posID,'_createEditWiderspruchspunkt','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=900,width=1050');
						//newWin.moveTo(width,height);
						newWin.focus();
					}
					
			<?	}?>
				
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
				
				function DownloadFile(format)
				{
					document.forms.FM_FORM.createDownloadFile.value=format; 
					document.forms.FM_FORM.submit();
					document.forms.FM_FORM.createDownloadFile.value=""; 
				}
				
				$('wsType').onchange=function()
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
				
			-->
		</script>
		<?
	}
	
	/**
	 * Return the following status 
	 */
	public function GetNextStatus()
	{
		// Abbruch
		if ($this->formElementValues["cancelProcess"]=="on")
		{
			$this->masterObject->SetJumpBackStatus(4);
			return 1; 
		}
		
		
		
		// Protokoll
		if ($this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_PROTOKOLL)
		{
			if ((int)$this->formElementValues["rsuser"]==-1) return 4; // ohne FMS-Freigabe
			return 2; 
		}
		// Protokollentwurf
		if ($this->formElementValues["wsType"]==Widerspruch::DOKUMENTEN_TYP_PROTOKOLLENTWURF)
		{
			return 3; 
		}
		// Widerspruch
		if ((int)$this->formElementValues["rsuser"]==-1) return 5; // ohne FMS-Freigabe
		return 0; 
	}
				
	/**
	 * return if this status of the current process can be edited when in a group
	 * @return boolean
	 */
	public function IsEditableInGroup()
	{
		return true;
	}
	
	/**
	 * return if the button "Aufgabe abschließen" should been shown
	 * @return boolean
	 */
	public function CanBeCompletetdInGroup()
	{
		// only group can be completed
		return ($this->IsProcessGroup() ? true : false);
	}
	
	/**
	 * Return if the Status can be aboarded by user
	 * @return boolean
	 */
	public function CanBeAboarded()
	{
		if ($this->obj->IsAttachedToGroup()) return false;
		if ($this->obj->GetWiderspruchCount($this->db)>1)
		{
			return true;
		}
		return false;
	}
	
}
?>