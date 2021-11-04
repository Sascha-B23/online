<?php
/**
 * Status "Teilabrechnung erfassen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasTeilabrechnungErfassen extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 3 Tage ab Auftragsdatum
		$this->obj->SetDeadline($this->obj->GetAuftragsdatumAbrechnung()+60*60*24*3);
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		// Daten aus Objekt laden?
		$currency = "";
		if ($loadFromObject)
		{
			/*@var $teilabrechnung Teilabrechnung*/
			$teilabrechnung = $this->obj->GetTeilabrechnung($this->db);
			if( $teilabrechnung!=null)
			{
				$currency = $teilabrechnung->GetCurrency();
				$this->formElementValues["bezeichnung"]=$teilabrechnung->GetBezeichnung();
				$this->formElementValues["datum"]=$teilabrechnung->GetDatum()==0 ? "" : date("d.m.Y", $teilabrechnung->GetDatum());
				$this->formElementValues["umlageflaeche_qm"]=HelperLib::ConvertFloatToLocalizedString( $teilabrechnung->GetUmlageflaecheQM() );
				$this->formElementValues["abrechnungszeitraumVon"]=$teilabrechnung->GetAbrechnungszeitraumVon()==0 ? "" : date("d.m.Y", $teilabrechnung->GetAbrechnungszeitraumVon());
				$this->formElementValues["abrechnungszeitraumBis"]=$teilabrechnung->GetAbrechnungszeitraumBis()==0 ? "" : date("d.m.Y", $teilabrechnung->GetAbrechnungszeitraumBis());
				$this->formElementValues["eigentuemer"]=$teilabrechnung->GetEigentuemer() == null ? "" : $teilabrechnung->GetEigentuemer()->GetAddressIDString();
				$this->formElementValues["verwalter"]=$teilabrechnung->GetVerwalter() == null ? "" : $teilabrechnung->GetVerwalter()->GetAddressIDString();
				$this->formElementValues["anwalt"]=$teilabrechnung->GetAnwalt() == null ? "" : $teilabrechnung->GetAnwalt()->GetAddressIDString();
				$this->formElementValues["schriftverkehrMit"]=$teilabrechnung->GetSchriftverkehrMit();
				$this->formElementValues["fristBelegeinsicht"]=$teilabrechnung->GetFristBelegeinsicht()==0 ? "" : date("d.m.Y", $teilabrechnung->GetFristBelegeinsicht());
				$this->formElementValues["fristWiderspruch"]=$teilabrechnung->GetFristWiderspruch()==0 ? "" : date("d.m.Y", $teilabrechnung->GetFristWiderspruch());
				$this->formElementValues["fristZahlung"]=$teilabrechnung->GetFristZahlung()==0 ? "" : date("d.m.Y", $teilabrechnung->GetFristZahlung());
				$this->formElementValues["prepaid"]=HelperLib::ConvertFloatToLocalizedString( $teilabrechnung->GetVorauszahlungLautKunde() );
				$this->formElementValues["vorauszahlungLautAbrechnung"]=HelperLib::ConvertFloatToLocalizedString( $teilabrechnung->GetVorauszahlungLautAbrechnung() );
				$this->formElementValues["abrechnungsergebnisLautAbrechnung"]=HelperLib::ConvertFloatToLocalizedString( $teilabrechnung->GetAbrechnungsergebnisLautAbrechnung() );
				$this->formElementValues["nachzahlungGutschrift"]=HelperLib::ConvertFloatToLocalizedString( $teilabrechnung->GetNachzahlungGutschrift() );
				$this->formElementValues["abschlagszahlungGutschrift"]=HelperLib::ConvertFloatToLocalizedString( $teilabrechnung->GetAbschlagszahlungGutschrift() );
				$this->formElementValues["heizkostenNachHVOAbgerechnet"]=$teilabrechnung->GetHeizkostenNachHVOAbgerechnet();
				//$this->formElementValues["versicherungserhoehungBeachtet"]=$teilabrechnung->GetVersicherungserhoehungBeachtet();
				//$this->formElementValues["grundsteuererhoehungBeachtet"]=$teilabrechnung->GetGrundsteuererhoehungBeachtet();
				$this->formElementValues["erstabrechnungOderAbrechnungNachUmbau"]=$teilabrechnung->GetErstabrechnungOderAbrechnungNachUmbau();
				
				$abrechnungsjahr=$teilabrechnung->GetAbrechnungsJahr();
				if ($abrechnungsjahr!=null)
				{
					$contract=$abrechnungsjahr->GetContract();
					if ($contract!=null)
					{
						$this->formElementValues["nurGrundsteuererhoehungUmlegbar"]=$contract->GetNurGrundsteuererhoehungUmlegbar() ? "on" : "";
						$this->formElementValues["nurVersicherungserhoehungUmlegbar"]=$contract->GetNurVersicherungserhoehungUmlegbar() ? "on" : "";
						// Eigentümer und Verwalter aus Vertrag übernehmen wenn leer
						if ($this->formElementValues["eigentuemer"]=="")
						{
							$this->formElementValues["eigentuemer"] = $contract->GetEigentuemer() == null ? "" : $contract->GetEigentuemer()->GetAddressIDString();
						}
						if ($this->formElementValues["verwalter"]=="")
						{
							$this->formElementValues["verwalter"] = $contract->GetVerwalter() == null ? "" : $contract->GetVerwalter()->GetAddressIDString();
						}
					}
				}
			}
		}
		$this->elements[] = new DateElement("datum", Teilabrechnung::GetAttributeName($this->languageManager, 'datum'), $this->formElementValues["datum"], true, $this->error["datum"]);
		$this->elements[] = new TextElement("bezeichnung", Teilabrechnung::GetAttributeName($this->languageManager, 'bezeichnung'), $this->formElementValues["bezeichnung"], true, $this->error["bezeichnung"]);
		$this->elements[] = new TextElement("umlageflaeche_qm", Teilabrechnung::GetAttributeName($this->languageManager, 'umlageflaeche_qm'), $this->formElementValues["umlageflaeche_qm"], true, $this->error["umlageflaeche_qm"]);
		$this->elements[] = new DateElement("abrechnungszeitraumVon", Teilabrechnung::GetAttributeName($this->languageManager, 'abrechnungszeitraumVon'), $this->formElementValues["abrechnungszeitraumVon"], true, $this->error["abrechnungszeitraumVon"]);
		$this->elements[] = new DateElement("abrechnungszeitraumBis", Teilabrechnung::GetAttributeName($this->languageManager, 'abrechnungszeitraumBis'), $this->formElementValues["abrechnungszeitraumBis"], true, $this->error["abrechnungszeitraumBis"]);
		
		$buttons = Array();
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/address_new.png", "help" => "Neuen Ansprechpartner anlegen", "href" => "javascript:CreateNewAddress(".AM_ADDRESSDATA_TYPE_OWNER.")");
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany(".AM_ADDRESSDATA_TYPE_OWNER.")");
		$this->elements[] = new TextElement("eigentuemer", "Eigentümer", $this->formElementValues["eigentuemer"], true, $this->error["eigentuemer"], false, new SearchTextDynamicContent(  $SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 6, "param01" => AM_ADDRESSDATA_TYPE_NONE), "", "eigentuemer"), $buttons);
		
		$buttons = Array();
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/address_new.png", "help" => "Neuen Ansprechpartner anlegen", "href" => "javascript:CreateNewAddress(".AM_ADDRESSDATA_TYPE_TRUSTEE.")");
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany(".AM_ADDRESSDATA_TYPE_TRUSTEE.")");
		$this->elements[] = new TextElement("verwalter", "Verwalter", $this->formElementValues["verwalter"], true, $this->error["verwalter"], false, new SearchTextDynamicContent(  $SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 6, "param01" => AM_ADDRESSDATA_TYPE_NONE), "", "verwalter"), $buttons);
		
		$buttons = Array();
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/address_new.png", "help" => "Neuen Ansprechpartner anlegen", "href" => "javascript:CreateNewAddress(".AM_ADDRESSDATA_TYPE_ADVOCATE.")");
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany(".AM_ADDRESSDATA_TYPE_ADVOCATE.")");
		$this->elements[] = new TextElement("anwalt", "Anwalt", $this->formElementValues["anwalt"], false, $this->error["anwalt"], false, new SearchTextDynamicContent(  $SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 6, "param01" => AM_ADDRESSDATA_TYPE_NONE), "", "anwalt"), $buttons);
		
		$options=Array();
		$options[]=Array("name" => "Eigentümer", "value" => NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_EIGENTUEMER);
		$options[]=Array("name" => "Verwalter", "value" => NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_VERWALTER);
		$options[]=Array("name" => "Anwalt", "value" => NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_ANWALT);
		$this->elements[] = new RadioButtonElement("schriftverkehrMit", "Schriftverkehr mit", $this->formElementValues["schriftverkehrMit"], false, $this->error["schriftverkehrMit"], $options );
		
		$this->elements[] = new DateElement("fristBelegeinsicht", Teilabrechnung::GetAttributeName($this->languageManager, 'fristBelegeinsicht'), $this->formElementValues["fristBelegeinsicht"], true, $this->error["fristBelegeinsicht"]);
		$this->elements[] = new DateElement("fristWiderspruch", Teilabrechnung::GetAttributeName($this->languageManager, 'fristWiderspruch'), $this->formElementValues["fristWiderspruch"], true, $this->error["fristWiderspruch"]);
		$this->elements[] = new DateElement("fristZahlung", Teilabrechnung::GetAttributeName($this->languageManager, 'fristZahlung'), $this->formElementValues["fristZahlung"], true, $this->error["fristZahlung"]);
		$this->elements[] = new TextElement("prepaid", Teilabrechnung::GetAttributeName($this->languageManager, 'vorauszahlungLautKunde')." (".$currency.")", $this->formElementValues["prepaid"], true, $this->error["prepaid"], false, $doAutoFill ? new TextDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=8&param01='+$('teilabrechnungAuswahl').options[$('teilabrechnungAuswahl').selectedIndex].value+'", "$('teilabrechnungAuswahl').onchange=function(){%REQUESTCALL%;};", "prepaid") : null);
		$this->elements[] = new TextElement("vorauszahlungLautAbrechnung", Teilabrechnung::GetAttributeName($this->languageManager, 'vorauszahlungLautAbrechnung')." (".$currency.")"." (netto)", $this->formElementValues["vorauszahlungLautAbrechnung"], true, $this->error["vorauszahlungLautAbrechnung"]);
		$this->elements[] = new TextElement("abrechnungsergebnisLautAbrechnung", Teilabrechnung::GetAttributeName($this->languageManager, 'abrechnungsergebnisLautAbrechnung')." (".$currency.")"." (netto)", $this->formElementValues["abrechnungsergebnisLautAbrechnung"], false, $this->error["abrechnungsergebnisLautAbrechnung"]);
		$this->elements[] = new TextElement("nachzahlungGutschrift", Teilabrechnung::GetAttributeName($this->languageManager, 'nachzahlungGutschrift')." (".$currency.")"." (netto)", $this->formElementValues["nachzahlungGutschrift"], false, $this->error["nachzahlungGutschrift"]);
		$this->elements[] = new TextElement("abschlagszahlungGutschrift", Teilabrechnung::GetAttributeName($this->languageManager, 'abschlagszahlungGutschrift')." (".$currency.")"." (netto)", $this->formElementValues["abschlagszahlungGutschrift"], false, $this->error["abschlagszahlungGutschrift"]);
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => 1);
		$options[]=Array("name" => "Nein", "value" => 2);
		$this->elements[] = new RadioButtonElement("heizkostenNachHVOAbgerechnet", "Heizkosten nach HVO abgerechnet", $this->formElementValues["heizkostenNachHVOAbgerechnet"], true, $this->error["heizkostenNachHVOAbgerechnet"], $options );
		$this->elements[] = new RadioButtonElement("erstabrechnungOderAbrechnungNachUmbau", "Erstabrechnung oder Abrechnung nach Umbau", $this->formElementValues["erstabrechnungOderAbrechnungNachUmbau"], true, $this->error["erstabrechnungOderAbrechnungNachUmbau"], $options );
		
		$locationTemp=$this->obj->GetLocation();
		if( $locationTemp!=null && $locationTemp->GetLocationType()==CM_LOCATION_TYPE_NONE ){
			global $CM_LOCATION_TYPES;
			$options=Array(Array("name" => "Bitte wählen...", "value" => CM_LOCATION_TYPE_NONE));
			for( $a=0; $a<count($CM_LOCATION_TYPES); $a++ ){
				if( $CM_LOCATION_TYPES[$a]["id"]==CM_LOCATION_TYPE_NONE )continue;
				$options[]=Array("name" => $CM_LOCATION_TYPES[$a]["name"], "value" => $CM_LOCATION_TYPES[$a]["id"]);
			}		
			$this->elements[] = new DropdownElement("type", CLocation::GetAttributeName($this->languageManager, 'locationType'), !isset($this->formElementValues["type"]) ? Array() : $this->formElementValues["type"], true, $this->error["type"], $options, false);
		}
	
		// Zuständiger FMS-Mitarbeiter für Freigabe bzw. Abbruch
		/* @var $userManager UserManager */
		global $userManager;
		$users = $userManager->GetUsers($_SESSION["currentUser"], "", "email", 0, 0, 0, UM_GROUP_BASETYPE_AUSHILFE);
		$options = Array(Array("name" => "keine", "value" => ""));
		for($a=0; $a<count($users); $a++)
		{
			if ($users[$a]->GetPKey()==$_SESSION["currentUser"]->GetPKey()) continue;
			$userName = $users[$a]->GetAddressData()==null ? $users[$a]->GetEMail() : $users[$a]->GetAddressData()->GetName()." ".$users[$a]->GetAddressData()->GetFirstName();
			$options[] = Array("name" => $userName, "value" => $users[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("aushilfe", "Aushilfe zum Teilabrechnungspositionen erfassen...", !isset($this->formElementValues["aushilfe"]) ? Array() : $this->formElementValues["aushilfe"], true, $this->error["aushilfe"], $options, false);
        $this->elements[] = new TextAreaElement("comment", "Kommentar", $this->obj->GetCustomerComment(), false, "", true);
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		global $addressManager;
		$teilabrechnung=$this->obj->GetTeilabrechnung($this->db);
		if( $teilabrechnung!=null){
			// Datum
			if( trim($this->formElementValues["datum"])!="" ){
				$tempValue=DateElement::GetTimeStamp($this->formElementValues["datum"]);
				if( $tempValue===false )$this->error["datum"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
				else $teilabrechnung->SetDatum($tempValue);
			}else{
				$teilabrechnung->SetDatum(0);
			}
			// Bezeichnung
			$teilabrechnung->SetBezeichnung($this->formElementValues["bezeichnung"]);
			// Abrechnungszeitruam Von
			if( trim($this->formElementValues["abrechnungszeitraumVon"])!="" ){
				$tempValue=DateElement::GetTimeStamp($this->formElementValues["abrechnungszeitraumVon"]);
				if( $tempValue===false )$this->error["abrechnungszeitraumVon"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
				else $teilabrechnung->SetAbrechnungszeitraumVon($tempValue);
			}else{
				$teilabrechnung->SetAbrechnungszeitraumVon(0);
			}
			// Umlagefläche
			$umlageflaeche=TextElement::GetFloat($this->formElementValues["umlageflaeche_qm"]);
			if( $umlageflaeche===false ){
				$this->error["umlageflaeche_qm"]="Bitte geben Sie die Umlagefläche in qm ein";
			}else{
				$teilabrechnung->SetUmlageflaecheQM( $umlageflaeche );
			}
			// Abrechnungszeitruam Bis
			if( trim($this->formElementValues["abrechnungszeitraumBis"])!="" ){
				$tempValue=DateElement::GetTimeStamp($this->formElementValues["abrechnungszeitraumBis"]);
				if( $tempValue===false )$this->error["abrechnungszeitraumBis"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
				else $teilabrechnung->SetAbrechnungszeitraumBis($tempValue);
			}else{
				$teilabrechnung->SetAbrechnungszeitraumBis(0);
			}
			if( $teilabrechnung->GetAbrechnungszeitraumVon()>0 && $teilabrechnung->GetAbrechnungszeitraumBis()>0 && $teilabrechnung->GetAbrechnungszeitraumVon()>=$teilabrechnung->GetAbrechnungszeitraumBis() )$this->error["abrechnungszeitraumBis"]="Dieser Tag muss nach 'Abrechnungszeitraum von' liegen";
			// Frist Belegeinsicht 
			if( trim($this->formElementValues["fristBelegeinsicht"])!="" ){
				$tempValue=DateElement::GetTimeStamp($this->formElementValues["fristBelegeinsicht"]);
				if( $tempValue===false )$this->error["fristBelegeinsicht"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
				else $teilabrechnung->SetFristBelegeinsicht($tempValue);
			}else{
				$teilabrechnung->SetFristBelegeinsicht(0);
			}
			// Frist Widerspruch
			if( trim($this->formElementValues["fristWiderspruch"])!="" ){
				$tempValue=DateElement::GetTimeStamp($this->formElementValues["fristWiderspruch"]);
				if( $tempValue===false )$this->error["fristWiderspruch"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
				else $teilabrechnung->SetFristWiderspruch($tempValue);
			}else{
				$teilabrechnung->SetFristWiderspruch(0);
			}
			// Frist Zahlung
			if( trim($this->formElementValues["fristZahlung"])!="" ){
				$tempValue=DateElement::GetTimeStamp($this->formElementValues["fristZahlung"]);
				if( $tempValue===false )$this->error["fristZahlung"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
				else $teilabrechnung->SetFristZahlung($tempValue);
			}else{
				$teilabrechnung->SetFristZahlung(0);
			}
			// Vorauszahlung laut Buchhaltung
			$tempValue=TextElement::GetFloat($this->formElementValues["prepaid"]);
			if( $tempValue===false )$this->error["prepaid"]="Bitte geben Sie eine gültige Zahl ein";
			else $teilabrechnung->SetVorauszahlungLautKunde($tempValue);
			// Vorauszahlung laut Abrechnung
			$tempValue=TextElement::GetFloat($this->formElementValues["vorauszahlungLautAbrechnung"]);
			if( $tempValue===false )$this->error["vorauszahlungLautAbrechnung"]="Bitte geben Sie eine gültige Zahl ein";
			else $teilabrechnung->SetVorauszahlungLautAbrechnung($tempValue);
			// Abrechnungsergebnis laut Abrechnung
			$tempValue=TextElement::GetFloat($this->formElementValues["abrechnungsergebnisLautAbrechnung"]);
			if( $tempValue===false )$this->error["abrechnungsergebnisLautAbrechnung"]="Bitte geben Sie eine gültige Zahl ein";
			else $teilabrechnung->SetAbrechnungsergebnisLautAbrechnung($tempValue);
			// Nachzahlung / Gutschrift
			$tempValue=TextElement::GetFloat($this->formElementValues["nachzahlungGutschrift"]);
			if( $tempValue===false )$this->error["nachzahlungGutschrift"]="Bitte geben Sie eine gültige Zahl ein";
			else $teilabrechnung->SetNachzahlungGutschrift($tempValue);
			// Abschlagszahlung / Gutschrift
			$tempValue=TextElement::GetFloat($this->formElementValues["abschlagszahlungGutschrift"]);
			if( $tempValue===false )$this->error["abschlagszahlungGutschrift"]="Bitte geben Sie eine gültige Zahl ein";
			else $teilabrechnung->SetAbschlagszahlungGutschrift($tempValue);

			if( $this->formElementValues["heizkostenNachHVOAbgerechnet"]=="" )$this->formElementValues["heizkostenNachHVOAbgerechnet"]=0;
			$teilabrechnung->SetHeizkostenNachHVOAbgerechnet((int)$this->formElementValues["heizkostenNachHVOAbgerechnet"]);
			if( $this->formElementValues["erstabrechnungOderAbrechnungNachUmbau"]=="" )$this->formElementValues["erstabrechnungOderAbrechnungNachUmbau"]=0;
			$teilabrechnung->SetErstabrechnungOderAbrechnungNachUmbau((int)$this->formElementValues["erstabrechnungOderAbrechnungNachUmbau"]);
			/*if( $this->formElementValues["grundsteuererhoehungBeachtet"]=="" )$this->formElementValues["grundsteuererhoehungBeachtet"]=0;
			$teilabrechnung->SetGrundsteuererhoehungBeachtet((int)$this->formElementValues["grundsteuererhoehungBeachtet"]);
			if( $this->formElementValues["versicherungserhoehungBeachtet"]=="" )$this->formElementValues["versicherungserhoehungBeachtet"]=0;
			$teilabrechnung->SetVersicherungserhoehungBeachtet((int)$this->formElementValues["versicherungserhoehungBeachtet"]);*/
			
			// Eigentümer
			if (trim($this->formElementValues["eigentuemer"])!="")
			{
				$tempAddressData = AddressManager::GetAddessById($this->db, $this->formElementValues["eigentuemer"]);
				if ($tempAddressData===null)
				{
					$this->error["eigentuemer"]="Der eingegebene Eigentümer konnte nicht gefunden werden";
				}
				else
				{
					$teilabrechnung->SetEigentuemer($tempAddressData);
					$this->formElementValues["eigentuemer"] = $tempAddressData->GetAddressIDString();
				}
			}
			else
			{
				$teilabrechnung->SetEigentuemer(null);
			}
			
			// Verwalter
			if (trim($this->formElementValues["verwalter"])!="")
			{
				$tempAddressData = AddressManager::GetAddessById($this->db, $this->formElementValues["verwalter"]);
				if ($tempAddressData===null)
				{
					$this->error["verwalter"]="Der eingegebene Verwalter konnte nicht gefunden werden";
				}
				else
				{
					$teilabrechnung->SetVerwalter($tempAddressData);
					$this->formElementValues["verwalter"] = $tempAddressData->GetAddressIDString();
				}
			}
			else
			{
				$teilabrechnung->SetVerwalter(null);
			}
			
			// Anwalt
			if (trim($this->formElementValues["anwalt"])!="")
			{
				$tempAddressData = AddressManager::GetAddessById($this->db, $this->formElementValues["anwalt"]);
				if ($tempAddressData===null)
				{
					$this->error["anwalt"]="Der eingegebene Anwalt konnte nicht gefunden werden";
				}
				else
				{
					$teilabrechnung->SetAnwalt($tempAddressData);
					$this->formElementValues["anwalt"] = $tempAddressData->GetAddressIDString();
				}
			}
			else
			{
				$teilabrechnung->SetAnwalt(null);
			}
			
			$teilabrechnung->SetSchriftverkehrMit( (int)$this->formElementValues["schriftverkehrMit"] );		
			$cLocation=$this->obj->GetLocation();
			if( $cLocation!=null ){
				if( $cLocation->GetLocationType()==CM_LOCATION_TYPE_NONE ){
					if( (int)$this->formElementValues["type"]!=CM_LOCATION_TYPE_NONE && $this->formElementValues["type"]!="" ){
						$cLocation->SetLocationType( $this->formElementValues["type"] );
						$returnValue=$cLocation->Store($this->db);
						if( $returnValue!==true ){
							$this->error["type"]="Systemfehler (".$returnValue.")";
						}
					}
				}
			}
			if (count($this->error)==0)
			{
				$returnValue=$teilabrechnung->Store($this->db);
				if ($returnValue===true)
				{
					// Sind alle Werte gesetzt, um in den nächsten Status zu springen?
					if ($gotoNextStatus)
					{
						if( $teilabrechnung->GetDatum()==0 )$this->error["datum"]="Um die Aufgabe abschließen zu können, müssen Sie ein gültiges Datum im Format tt.mm.jjjj eingeben.";
						if( trim($teilabrechnung->GetBezeichnung())=="" )$this->error["bezeichnung"]="Um die Aufgabe abschließen zu können, müssen Sie eine Bezeichnung eingeben.";
						if( $teilabrechnung->GetUmlageflaecheQM()<=0.0 )$this->error["umlageflaeche_qm"]="Um die Aufgabe abschließen zu können, müssen Sie die Umlagefläche eingeben";
						if( $teilabrechnung->GetAbrechnungszeitraumVon()==0 )$this->error["abrechnungszeitraumVon"]="Um die Aufgabe abschließen zu können, müssen Sie ein gültiges Datum im Format tt.mm.jjjj eingeben.";
						if( $teilabrechnung->GetAbrechnungszeitraumBis()==0 )$this->error["abrechnungszeitraumBis"]="Um die Aufgabe abschließen zu können, müssen Sie ein gültiges Datum im Format tt.mm.jjjj eingeben.";
						if( $teilabrechnung->GetFristBelegeinsicht()==0 )$this->error["fristBelegeinsicht"]="Um die Aufgabe abschließen zu können, müssen Sie ein gültiges Datum im Format tt.mm.jjjj eingeben.";
						if( $teilabrechnung->GetFristWiderspruch()==0 )$this->error["fristWiderspruch"]="Um die Aufgabe abschließen zu können, müssen Sie ein gültiges Datum im Format tt.mm.jjjj eingeben.";
						if( $teilabrechnung->GetFristZahlung()==0 )$this->error["fristZahlung"]="Um die Aufgabe abschließen zu können, müssen Sie ein gültiges Datum im Format tt.mm.jjjj eingeben.";
						if( $teilabrechnung->GetHeizkostenNachHVOAbgerechnet()==0 )$this->error["heizkostenNachHVOAbgerechnet"]="Um die Aufgabe abschließen zu können, müssen Sie ein Auswahl treffen.";
						if( $teilabrechnung->GetErstabrechnungOderAbrechnungNachUmbau()==0 )$this->error["erstabrechnungOderAbrechnungNachUmbau"]="Um die Aufgabe abschließen zu können, müssen Sie ein Auswahl treffen.";
						if( $teilabrechnung->GetEigentuemer()==null )$this->error["eigentuemer"]="Um die Aufgabe abschließen zu können, müssen Sie den Eigentümer angegeben.";
						if( $teilabrechnung->GetVerwalter()==null )$this->error["verwalter"]="Um die Aufgabe abschließen zu können, müssen Sie den Verwalter angegeben.";
						if( $teilabrechnung->GetSchriftverkehrMit()==NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_KEINEM )$this->error["schriftverkehrMit"]="Um die Aufgabe abschließen zu können, müssen Sie ein Auswahl treffen.";
						if( $teilabrechnung->GetSchriftverkehrMit()==NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_EIGENTUEMER && $teilabrechnung->GetEigentuemer()==null )$this->error["schriftverkehrMit"]="Es ist kein Eigentümer angegeben.";
						if( $teilabrechnung->GetSchriftverkehrMit()==NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_VERWALTER && $teilabrechnung->GetVerwalter()==null )$this->error["schriftverkehrMit"]="Es ist kein Verwalter angegeben.";
						if( $teilabrechnung->GetSchriftverkehrMit()==NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_ANWALT && $teilabrechnung->GetAnwalt()==null )$this->error["schriftverkehrMit"]="Es ist kein Anwalt angegeben.";
						$locationTemp=$this->obj->GetLocation();
						if( $locationTemp!=null && $locationTemp->GetLocationType()==CM_LOCATION_TYPE_NONE ){
							$this->error["type"]="Um die Aufgabe abschließen zu können, müssen Sie ein Auswahl treffen.";
						}
						if( count($this->error)>0 )return false;
						
						if ($this->formElementValues["aushilfe"]!="")
						{
							$user = new User($this->db);
							if ($user->Load((int)$this->formElementValues["aushilfe"], $this->db)===true)
							{
								$this->obj->SetZuweisungUser($user);
							}
							else
							{
								$this->error["aushilfe"]="Um die Aufgabe abschließen zu können, müssen Sie einen Datenerfasser auswählen.";
							}
						}
						else
						{
							$this->obj->SetZuweisungUser(null);
						}
                        // reset customer comment
                        $this->obj->SetCustomerComment("");
						$returnValue = $this->obj->Store($this->db);
						if ($returnValue!==true)
						{
							$this->error["misc"][]="Systemfehler (2/".$returnValue.")";
							return false;
						}
					}
					return true;
				}
				$this->error["misc"][]="Systemfehler (1/".$returnValue.")";
			}
		}
		else
		{
			$this->error["misc"][]="Systemfehler: Dem Prozess ist keine Teilabrechnung zugeordnet";
		}
		return false;
	}
	
	/**
	 * Inject HTML/JavaScript before the UI is send to the browser 
	 */
	public function PrePrint()
	{
		
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
				function CreateNewAddress(type)
				{
					var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/createAddress.php5?<?=SID;?>&type='+type, '_createAddress', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				
				function SetAddress(type, name)
				{
					if (type==<?=AM_ADDRESSDATA_TYPE_OWNER;?>) $('eigentuemer').value=name;
					if (type==<?=AM_ADDRESSDATA_TYPE_TRUSTEE;?>) $('verwalter').value=name;
					if (type==<?=AM_ADDRESSDATA_TYPE_ADVOCATE;?>) $('anwalt').value=name;
				}
				
				function CreateNewAddressCompany(type)
				{
					var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/createAddressCompany.php5?<?=SID;?>&type='+type, '_createAddressCompany', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				
				function SetAddressCompany(type, name, addressId, addressCompanyId)
				{
					if (type==<?=AM_ADDRESSDATA_TYPE_OWNER;?>) $('eigentuemer').value = addressCompanyId;
					if (type==<?=AM_ADDRESSDATA_TYPE_TRUSTEE;?>) $('verwalter').value = addressCompanyId;
					if (type==<?=AM_ADDRESSDATA_TYPE_ADVOCATE;?>) $('anwalt').value = addressCompanyId;
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
		return 0;
	}
			
	/**
	 * return if this status of the current process can be edited when in a group
	 * @return boolean
	 */
	public function IsEditableInGroup()
	{
		// only none group is editable
		return ($this->IsProcessGroup() ? false : true);
	}
	
}
?>