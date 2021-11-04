<?php
/**
 * FormData-Implementierung für die Teilabrechnungen
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TeilabrechnungFormData extends FormData 
{

	/**
	 * @var bool use restricted mode?
	 */
	protected $restrictedMode = false;

	/**
	 * ContractFormData constructor.
	 * @param array $formElementValues
	 * @param Contract $object
	 * @param DBManager $db
	 * @param bool $restrictedMode
	 */
	public function TeilabrechnungFormData($formElementValues, $object, $db, $restrictedMode=false)
	{
		$this->restrictedMode = $restrictedMode;
		parent::FormData($formElementValues, $object, $db);
	}

	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		global $CM_LOCATION_TYPES;
		global $userManager;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "teilabrechnung.png";
		$this->options["icontext"] = "Teilabrechnung ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject ||$this->restrictedMode ){
			$abrechnungsjahr=$this->obj->GetAbrechnungsJahr();
			if( $abrechnungsjahr!=null ){
				$this->formElementValues["year"]=$abrechnungsjahr->GetPKey();
				$contract=$abrechnungsjahr->GetContract();
				if( $contract!=null ){
					$this->formElementValues["contract"]=$contract->GetPKey();
					$cShop=$contract->GetShop();
					if( $cShop!=null ){
						$this->formElementValues["cShop"]=$cShop->GetPKey();
						$cLocation=$cShop->GetLocation();
						if( $cLocation!=null ){
							$this->formElementValues["location"]=$cLocation->GetPKey();
							$cCompany=$cLocation->GetCompany();
							if( $cCompany!=null ){
								$this->formElementValues["company"]=$cCompany->GetPKey();
								$cGroup=$cCompany->GetGroup();
								if( $cGroup!=null ){
									$this->formElementValues["group"]=$cGroup->GetPKey();
								}
							}
						}
					}
				}
			}
		}
		if( $loadFromObject ){
			$this->formElementValues["firmenName"]=$this->obj->GetFirmenname();
			$this->formElementValues["vorauszahlungLautKunde"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetVorauszahlungLautKunde() );
			$this->formElementValues["vorauszahlungLautAbrechnung"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetVorauszahlungLautAbrechnung() );
			$this->formElementValues["abrechnungsergebnisLautAbrechnung"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetAbrechnungsergebnisLautAbrechnung() );
			$this->formElementValues["nachzahlungGutschrift"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetNachzahlungGutschrift() );
			$this->formElementValues["abschlagszahlungGutschrift"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetAbschlagszahlungGutschrift() );
			$this->formElementValues["korrigiertesAbrechnungsergebnis"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetKorrigiertesAbrechnungsergebnis() );
			$this->formElementValues["bezeichnung"]=$this->obj->GetBezeichnung();
			$this->formElementValues["datum"]=$this->obj->GetDatum()==0 ? "" : date("d.m.Y", $this->obj->GetDatum());
			$this->formElementValues["abrechnungszeitraumVon"]=$this->obj->GetAbrechnungszeitraumVon()==0 ? "" : date("d.m.Y", $this->obj->GetAbrechnungszeitraumVon());
			$this->formElementValues["abrechnungszeitraumBis"]=$this->obj->GetAbrechnungszeitraumBis()==0 ? "" : date("d.m.Y", $this->obj->GetAbrechnungszeitraumBis());
			$this->formElementValues["eigentuemer"]=$this->obj->GetEigentuemer() == null ? "" : $this->obj->GetEigentuemer()->GetAddressIDString();
			$this->formElementValues["verwalter"]=$this->obj->GetVerwalter() == null ? "" : $this->obj->GetVerwalter()->GetAddressIDString();
			$this->formElementValues["anwalt"]=$this->obj->GetAnwalt() == null ? "" : $this->obj->GetAnwalt()->GetAddressIDString();
			$this->formElementValues["schriftverkehrMit"]=$this->obj->GetSchriftverkehrMit();
			$this->formElementValues["fristBelegeinsicht"]=$this->obj->GetFristBelegeinsicht()==0 ? "" : date("d.m.Y", $this->obj->GetFristBelegeinsicht());
			$this->formElementValues["fristWiderspruch"]=$this->obj->GetFristWiderspruch()==0 ? "" : date("d.m.Y", $this->obj->GetFristWiderspruch());
			$this->formElementValues["fristZahlung"]=$this->obj->GetFristZahlung()==0 ? "" : date("d.m.Y", $this->obj->GetFristZahlung());
			$this->formElementValues["umlageflaeche_qm"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetUmlageflaecheQM() );
			$this->formElementValues["heizkostenNachHVOAbgerechnet"]=$this->obj->GetHeizkostenNachHVOAbgerechnet();
			//$this->formElementValues["versicherungserhoehungBeachtet"]=$this->obj->GetVersicherungserhoehungBeachtet();
			//$this->formElementValues["grundsteuererhoehungBeachtet"]=$this->obj->GetGrundsteuererhoehungBeachtet();
			$this->formElementValues["erstabrechnungOderAbrechnungNachUmbau"]=$this->obj->GetErstabrechnungOderAbrechnungNachUmbau();
			$this->formElementValues["erfasst"]=($this->obj->GetErfasst() ? "on" : "");
			$this->formElementValues["date"]=$this->obj->GetAuftragsdatumAbrechnung()==0 ? "" : date("d.m.Y", $this->obj->GetAuftragsdatumAbrechnung());
			$this->formElementValues["settlementDifferenceHidden"]=($this->obj->IsSettlementDifferenceHidden() ? "on" : "");
		}else{
			if( !isset($this->formElementValues["vorauszahlungLautKunde"]) )$this->formElementValues["vorauszahlungLautKunde"]=0;
			if( !isset($this->formElementValues["vorauszahlungLautAbrechnung"]) )$this->formElementValues["vorauszahlungLautAbrechnung"]=0;
			if( !isset($this->formElementValues["abrechnungsergebnisLautAbrechnung"]) )$this->formElementValues["abrechnungsergebnisLautAbrechnung"]=0;
			if( !isset($this->formElementValues["nachzahlungGutschrift"]) )$this->formElementValues["nachzahlungGutschrift"]=0;
			if( !isset($this->formElementValues["abschlagszahlungGutschrift"]) )$this->formElementValues["abschlagszahlungGutschrift"]=0;
			if( !isset($this->formElementValues["korrigiertesAbrechnungsergebnis"]) )$this->formElementValues["korrigiertesAbrechnungsergebnis"]=0;
			if( !isset($this->formElementValues["umlageflaeche_qm"]) )$this->formElementValues["umlageflaeche_qm"]=0;
			if( !isset($this->formElementValues["bezeichnung"]) )$this->formElementValues["bezeichnung"]="Nebenkosten";
		}
		global $customerManager;
		$emptyOptions=Array();
		$emptyOptions[]=Array("name" => "Bitte wählen...", "value" => "");
		// Gruppe
		$options=$emptyOptions;
		$objects=$customerManager->GetGroups($_SESSION["currentUser"], '', 'name', 0, 0, 0);
		for( $a=0; $a<count($objects); $a++){
			$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("group", "Gruppe", $this->formElementValues["group"], true, $this->error["group"], $options, false, null, Array(), $this->restrictedMode);
		// Firma
		$options=$emptyOptions;
		$currentGroup=$customerManager->GetGroupByID($_SESSION["currentUser"], $this->formElementValues["group"]);
		if($currentGroup!=null){
			$objects=$currentGroup->GetCompanys($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("company", "Firma", $this->formElementValues["company"], true, $this->error["company"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=1&param01='+$('group').options[$('group').selectedIndex].value+'", "$('group').onchange=function(){%REQUESTCALL%;};", "company"), Array(), $this->restrictedMode );
		// Standort
		$options=$emptyOptions;
		$currentCompany=$customerManager->GetCompanyByID($_SESSION["currentUser"], $this->formElementValues["company"]);
		if($currentCompany!=null){
			$objects=$currentCompany->GetLocations($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("location", "Standort", $this->formElementValues["location"], true, $this->error["location"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=2&param01='+$('company').options[$('company').selectedIndex].value+'", "$('company').onchange=function(){%REQUESTCALL%;};", "location"), Array(), $this->restrictedMode);
		// Läden
		$options=$emptyOptions;
		$currentLocation=$customerManager->GetLocationByID($_SESSION["currentUser"], $this->formElementValues["location"]);
		if($currentLocation!=null){
			$objects=$currentLocation->GetShops($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("cShop", "Laden", $this->formElementValues["cShop"], true, $this->error["cShop"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=3&param01='+$('location').options[$('location').selectedIndex].value+'", "$('location').onchange=function(){%REQUESTCALL%;};", "cShop"), Array(), $this->restrictedMode);
		// Verträge
		$options=$emptyOptions;
		$currentShop=$customerManager->GetShopByID($_SESSION["currentUser"], $this->formElementValues["cShop"]);
		if($currentShop!=null){
			$objects=$currentShop->GetContracts($this->db);
			for( $a=0; $a<count($objects); $a++)
			{
				$options[]=Array("name" => "Vertrag ".($objects[$a]->GetLifeOfLeaseString()=='' ? ($a+1) : $objects[$a]->GetLifeOfLeaseString() ), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("contract", "Vertrag", $this->formElementValues["contract"], true, $this->error["contract"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=4&param01='+$('cShop').options[$('cShop').selectedIndex].value+'", "$('cShop').onchange=function(){%REQUESTCALL%;};", "contract"), Array(), $this->restrictedMode);

		global $rsKostenartManager;
		// Abrechnungsjahre
		$options=$emptyOptions;
		$currentContract=$rsKostenartManager->GetContractByID($_SESSION["currentUser"], $this->formElementValues["contract"]);
		if($currentContract!=null)
		{
			$objects = $currentContract->GetAbrechnungsJahre($this->db);
			for( $a=0; $a<count($objects); $a++)
			{
				$options[] = Array("name" => $objects[$a]->GetJahr(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("year", AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr'), $this->formElementValues["year"], true, $this->error["year"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=10&param01='+$('contract').options[$('contract').selectedIndex].value+'", "$('contract').onchange=function(){%REQUESTCALL%;};", "year"), Array(), $this->restrictedMode);

		// Abrechnungsjahr
		//$this->elements[] = new TextElement("year", AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr'), $this->formElementValues["year"], true, $this->error["year"]);
		$this->elements[] = new DateElement("date", Teilabrechnung::GetAttributeName($this->languageManager, 'auftragsdatumAbrechnung'), $this->formElementValues["date"], false, $this->error["date"]);
		
		// Teilabrechnungsdaten
		$this->elements[] = new TextElement("firmenName", "Firmenname", $this->formElementValues["firmenName"], false, $this->error["firmenName"]);
		$this->elements[] = new TextElement("vorauszahlungLautKunde", Teilabrechnung::GetAttributeName($this->languageManager, 'vorauszahlungLautKunde'), $this->formElementValues["vorauszahlungLautKunde"], false, $this->error["vorauszahlungLautKunde"]);
		$this->elements[] = new TextElement("vorauszahlungLautAbrechnung", Teilabrechnung::GetAttributeName($this->languageManager, 'vorauszahlungLautAbrechnung')." (netto)", $this->formElementValues["vorauszahlungLautAbrechnung"], false, $this->error["vorauszahlungLautAbrechnung"]);
		$this->elements[] = new TextElement("abrechnungsergebnisLautAbrechnung", Teilabrechnung::GetAttributeName($this->languageManager, 'abrechnungsergebnisLautAbrechnung')." (netto)", $this->formElementValues["abrechnungsergebnisLautAbrechnung"], false, $this->error["abrechnungsergebnisLautAbrechnung"]);
		
		
		$this->elements[] = new TextElement("nachzahlungGutschrift", Teilabrechnung::GetAttributeName($this->languageManager, 'nachzahlungGutschrift')." (netto)", $this->formElementValues["nachzahlungGutschrift"], false, $this->error["nachzahlungGutschrift"]);
		$this->elements[] = new TextElement("abschlagszahlungGutschrift", Teilabrechnung::GetAttributeName($this->languageManager, 'abschlagszahlungGutschrift')." (netto)", $this->formElementValues["abschlagszahlungGutschrift"], false, $this->error["abschlagszahlungGutschrift"]);
		$this->elements[] = new TextElement("korrigiertesAbrechnungsergebnis", Teilabrechnung::GetAttributeName($this->languageManager, 'korrigiertesAbrechnungsergebnis'), $this->formElementValues["korrigiertesAbrechnungsergebnis"], false, $this->error["korrigiertesAbrechnungsergebnis"]);
		$this->elements[] = new TextElement("bezeichnung", Teilabrechnung::GetAttributeName($this->languageManager, 'bezeichnung'), $this->formElementValues["bezeichnung"], false, $this->error["bezeichnung"]);
		$this->elements[] = new DateElement("datum", Teilabrechnung::GetAttributeName($this->languageManager, 'datum'), $this->formElementValues["datum"], false, $this->error["datum"]);
		$this->elements[] = new DateElement("abrechnungszeitraumVon", Teilabrechnung::GetAttributeName($this->languageManager, 'abrechnungszeitraumVon'), $this->formElementValues["abrechnungszeitraumVon"], false, $this->error["abrechnungszeitraumVon"]);
		$this->elements[] = new DateElement("abrechnungszeitraumBis", Teilabrechnung::GetAttributeName($this->languageManager, 'abrechnungszeitraumBis'), $this->formElementValues["abrechnungszeitraumBis"], false, $this->error["abrechnungszeitraumBis"]);
		
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
		
		$this->elements[] = new DateElement("fristBelegeinsicht", Teilabrechnung::GetAttributeName($this->languageManager, 'fristBelegeinsicht'), $this->formElementValues["fristBelegeinsicht"], false, $this->error["fristBelegeinsicht"]);
		$this->elements[] = new DateElement("fristWiderspruch", Teilabrechnung::GetAttributeName($this->languageManager, 'fristWiderspruch'), $this->formElementValues["fristWiderspruch"], false, $this->error["fristWiderspruch"]);
		$this->elements[] = new DateElement("fristZahlung", Teilabrechnung::GetAttributeName($this->languageManager, 'fristZahlung'), $this->formElementValues["fristZahlung"], false, $this->error["fristZahlung"]);
		$this->elements[] = new TextElement("umlageflaeche_qm", Teilabrechnung::GetAttributeName($this->languageManager, 'umlageflaeche_qm'), $this->formElementValues["umlageflaeche_qm"], false, $this->error["umlageflaeche_qm"]);
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => 1);
		$options[]=Array("name" => "Nein", "value" => 2);
		$this->elements[] = new RadioButtonElement("heizkostenNachHVOAbgerechnet", "Heizkosten nach HVO abgerechnet", $this->formElementValues["heizkostenNachHVOAbgerechnet"], false, $this->error["heizkostenNachHVOAbgerechnet"], $options );
		//$this->elements[] = new RadioButtonElement("versicherungserhoehungBeachtet", "Versicherungserhöhung beachtet", $this->formElementValues["versicherungserhoehungBeachtet"], false, $this->error["versicherungserhoehungBeachtet"], $options );
		//$this->elements[] = new RadioButtonElement("grundsteuererhoehungBeachtet", "Grundsteuererhöhung beachtet", $this->formElementValues["grundsteuererhoehungBeachtet"], false, $this->error["grundsteuererhoehungBeachtet"], $options );
		$this->elements[] = new RadioButtonElement("erstabrechnungOderAbrechnungNachUmbau", "Erstabrechnung oder Abrechnung nach Umbau", $this->formElementValues["erstabrechnungOderAbrechnungNachUmbau"], false, $this->error["erstabrechnungOderAbrechnungNachUmbau"], $options );
		
		// Dateien
		if( isset($_POST["deleteFile_teilabrechnung"]) && $_POST["deleteFile_teilabrechnung"]!="" ){
			$fileToDelete=new File($this->db);
			if( $fileToDelete->Load((int)$_POST["deleteFile_teilabrechnung"], $this->db) ){
				$this->obj->RemoveFile($this->db, $fileToDelete);
			}
		}
		$this->elements[] = new FileElement("teilabrechnung", "Teilabrechnung", $this->formElementValues["teilabrechnung"], false, $this->error["teilabrechnung"], FM_FILE_SEMANTIC_TEILABRECHNUNG, $this->obj->GetFiles($this->db) );
		$this->elements[] = new CheckboxElement("settlementDifferenceHidden", Teilabrechnung::GetAttributeName($this->languageManager, 'hideSettlementDifference'), $this->formElementValues["settlementDifferenceHidden"], false, "");
		$this->elements[] = new CheckboxElement("erfasst", "Teilabrechnungspositionen erfasst", $this->formElementValues["erfasst"], false, "");
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		global $addressManager;
		$this->error=array();
		$this->obj->SetFirmenname($this->formElementValues["firmenName"]);
		$tempValue=TextElement::GetFloat($this->formElementValues["vorauszahlungLautKunde"]);
		if( $tempValue===false )$this->error["vorauszahlungLautKunde"]="Bitte geben Sie eine gültige Zahl ein";
		else $this->obj->SetVorauszahlungLautKunde($tempValue);
		
		$tempValue=TextElement::GetFloat($this->formElementValues["vorauszahlungLautAbrechnung"]);
		if( $tempValue===false )$this->error["vorauszahlungLautAbrechnung"]="Bitte geben Sie eine gültige Zahl ein";
		else $this->obj->SetVorauszahlungLautAbrechnung($tempValue);
		
		$tempValue=TextElement::GetFloat($this->formElementValues["abrechnungsergebnisLautAbrechnung"]);
		if( $tempValue===false )$this->error["abrechnungsergebnisLautAbrechnung"]="Bitte geben Sie eine gültige Zahl ein";
		else $this->obj->SetAbrechnungsergebnisLautAbrechnung($tempValue);
		
		$tempValue=TextElement::GetFloat($this->formElementValues["nachzahlungGutschrift"]);
		if( $tempValue===false )$this->error["nachzahlungGutschrift"]="Bitte geben Sie eine gültige Zahl ein";
		else $this->obj->SetNachzahlungGutschrift($tempValue);
		
		$tempValue=TextElement::GetFloat($this->formElementValues["abschlagszahlungGutschrift"]);
		if( $tempValue===false )$this->error["abschlagszahlungGutschrift"]="Bitte geben Sie eine gültige Zahl ein";
		else $this->obj->SetAbschlagszahlungGutschrift($tempValue);
		
		$tempValue=TextElement::GetFloat($this->formElementValues["korrigiertesAbrechnungsergebnis"]);
		if( $tempValue===false )$this->error["korrigiertesAbrechnungsergebnis"]="Bitte geben Sie eine gültige Zahl ein";
		else $this->obj->SetKorrigiertesAbrechnungsergebnis($tempValue);
		
		$this->obj->SetBezeichnung($this->formElementValues["bezeichnung"]);
		if( trim($this->formElementValues["datum"])!="" ){
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["datum"]);
			if( $tempValue===false )$this->error["datum"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $this->obj->SetDatum($tempValue);
		}else{
			$this->obj->SetDatum(0);
		}
		if( trim($this->formElementValues["abrechnungszeitraumVon"])!="" ){
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["abrechnungszeitraumVon"]);
			if( $tempValue===false )$this->error["abrechnungszeitraumVon"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $this->obj->SetAbrechnungszeitraumVon($tempValue);
		}else{
			$this->obj->SetAbrechnungszeitraumVon(0);
		}
		if( trim($this->formElementValues["abrechnungszeitraumBis"])!="" ){
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["abrechnungszeitraumBis"]);
			if( $tempValue===false )$this->error["abrechnungszeitraumBis"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $this->obj->SetAbrechnungszeitraumBis($tempValue);
		}else{
			$this->obj->SetAbrechnungszeitraumBis(0);
		}
		if( $this->obj->GetAbrechnungszeitraumVon()>0 && $this->obj->GetAbrechnungszeitraumBis()>0 && $this->obj->GetAbrechnungszeitraumVon()>=$this->obj->GetAbrechnungszeitraumBis() )$this->error["abrechnungszeitraumBis"]="Dieser Tag muss nach 'Abrechnungszeitraum von' liegen";
		
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
				$this->obj->SetEigentuemer($tempAddressData);
				$this->formElementValues["eigentuemer"]=$tempAddressData->GetAddressIDString();
			}
		}
		else
		{
			$this->obj->SetEigentuemer(null);
		}
		if ($this->obj->GetEigentuemer()==null) $this->error["eigentuemer"]="Bitte geben Sie den Eigentümer ein";
		
		// Verwalter
		if (trim($this->formElementValues["verwalter"])!="")
		{
			$tempAddressData = AddressManager::GetAddessById($this->db, $this->formElementValues["verwalter"]);
			if ($tempAddressData===null)
			{
				$this->error["verwalter"] = "Der eingegebene Verwalter konnte nicht gefunden werden";
			}
			else
			{
				$this->obj->SetVerwalter($tempAddressData);
				$this->formElementValues["verwalter"] = $tempAddressData->GetAddressIDString();
			}
		}
		else
		{
			$this->obj->SetVerwalter(null);
		}
		if ($this->obj->GetVerwalter()==null) $this->error["verwalter"]="Bitte geben Sie den Verwalter ein";
		
		// Anwalt
		if (trim($this->formElementValues["anwalt"])!="")
		{
			$tempAddressData = AddressManager::GetAddessById($this->db, $this->formElementValues["anwalt"]);
			if ($tempAddressData===null)
			{
				$this->error["anwalt"] = "Der eingegebene Anwalt konnte nicht gefunden werden";
			}
			else
			{
				$this->obj->SetAnwalt($tempAddressData);
				$this->formElementValues["anwalt"] = $tempAddressData->GetAddressIDString();
			}
		}
		else
		{
			$this->obj->SetAnwalt(null);
		}
		
		$this->obj->SetSchriftverkehrMit( (int)$this->formElementValues["schriftverkehrMit"] );
		if( trim($this->formElementValues["fristBelegeinsicht"])!="" ){
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["fristBelegeinsicht"]);
			if( $tempValue===false )$this->error["fristBelegeinsicht"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $this->obj->SetFristBelegeinsicht($tempValue);
		}else{
			$this->obj->SetFristBelegeinsicht(0);
		}
		if( trim($this->formElementValues["fristWiderspruch"])!="" ){
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["fristWiderspruch"]);
			if( $tempValue===false )$this->error["fristWiderspruch"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $this->obj->SetFristWiderspruch($tempValue);
		}else{
			$this->obj->SetFristWiderspruch(0);
		}
		if( trim($this->formElementValues["fristZahlung"])!="" ){
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["fristZahlung"]);
			if( $tempValue===false )$this->error["fristZahlung"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $this->obj->SetFristZahlung($tempValue);
		}else{
			$this->obj->SetFristZahlung(0);
		}
		$tempValue=TextElement::GetFloat($this->formElementValues["umlageflaeche_qm"]);
		if( $tempValue===false )$this->error["umlageflaeche_qm"]="Bitte geben Sie eine gültige Zahl ein";
		else $this->obj->SetUmlageflaecheQM($tempValue);
		if( $this->formElementValues["heizkostenNachHVOAbgerechnet"]=="" )$this->formElementValues["heizkostenNachHVOAbgerechnet"]=0;
		$this->obj->SetHeizkostenNachHVOAbgerechnet((int)$this->formElementValues["heizkostenNachHVOAbgerechnet"]);
		/*if( $this->formElementValues["versicherungserhoehungBeachtet"]=="" )$this->formElementValues["versicherungserhoehungBeachtet"]=0;
		$this->obj->SetVersicherungserhoehungBeachtet((int)$this->formElementValues["versicherungserhoehungBeachtet"]);
		if( $this->formElementValues["grundsteuererhoehungBeachtet"]=="" )$this->formElementValues["grundsteuererhoehungBeachtet"]=0;
		$this->obj->SetGrundsteuererhoehungBeachtet((int)$this->formElementValues["grundsteuererhoehungBeachtet"]);*/
		if( $this->formElementValues["erstabrechnungOderAbrechnungNachUmbau"]=="" )$this->formElementValues["erstabrechnungOderAbrechnungNachUmbau"]=0;
		$this->obj->SetErstabrechnungOderAbrechnungNachUmbau((int)$this->formElementValues["erstabrechnungOderAbrechnungNachUmbau"]);
		$this->obj->SetErfasst( $this->formElementValues["erfasst"]=="on" ? true : false );
		$this->obj->SetSettlementDifferenceHidden( $this->formElementValues["settlementDifferenceHidden"]=="on" ? true : false );
		$timeStamp=DateElement::GetTimeStamp($this->formElementValues["date"]);
		if( $timeStamp!==false || trim($this->formElementValues["date"])=="" ){
			if( $timeStamp!==false )$this->obj->SetAuftragsdatumAbrechnung($timeStamp);
			elseif( $this->obj->GetPKey()!=-1 ) $this->obj->SetAuftragsdatumAbrechnung(0);
		}
		else
		{
			$this->error["date"]="Bitte geben Sie das Beauftragungsdatum im Format tt.mm.jjjj ein";
		}
		// Jahr setzen
		$rsKostenartManager=new RSKostenartManager($this->db);
		if (!$this->restrictedMode)
		{
			$currentYear = $rsKostenartManager->GetAbrechnungsJahrByID($_SESSION["currentUser"], $this->formElementValues["year"]);
			if ($currentYear==null)
			{
				$this->error["contract"]="Bitte wählen Sie das zugehörige Jahr aus";
			}
		}
		if (count($this->error)==0)
		{
			// Datei verarbeiten
			$fileObject=FileElement::GetFileElement($this->db, $_FILES["teilabrechnung"], FM_FILE_SEMANTIC_TEILABRECHNUNG);
			if( !is_object($fileObject) || get_class($fileObject)!="File" ){
				// Nur wenn nicht bereits min. eine Datei vorhanden ist einen Fehler ausgeben
				if( $fileObject!=-1 || $this->obj->GetFileCount($this->db)==0 ){
					$this->error["teilabrechnung"]=FileElement::GetErrorText($fileObject);
					return false;
				}
			}
			/* @var $oldYear AbrechnungsJahr */
			if (!$this->restrictedMode)
			{
				$oldYear = $this->obj->GetAbrechnungsJahr();
				$processOldToStore = null;
				$processNewToStore = null;
				if($oldYear != null && $oldYear->GetPKey() != $currentYear->GetPKey())
				{
					// check if the TA is linked to another process
					$processOld = WorkflowManager::GetProcessStatusByAbrechnungsJahr($this->db, $oldYear);
					if($processOld != null)
					{
						$processNew = WorkflowManager::GetProcessStatusByAbrechnungsJahr($this->db, $currentYear);
						if($processNew == null || $processOld->GetPKey() != $processNew->GetPKey())
						{
							// check if this TA is the 'current TA' of the old process
							if($processOld->GetCurrentTeilabrechnung() != null && $processOld->GetCurrentTeilabrechnung()
									->GetPKey() == $this->obj->GetPKey())
							{
								// remove the "current TA" from the old process
								$processOld->RemoveCurrentTeilabrechnung($this->db);
								$processOldToStore = $processOld;
							}
						}
						// Set current TA if it's not already set
						if($processNew != null && $processNew->GetCurrentTeilabrechnung() == null)
						{
							$processNew->SetCurrentTeilabrechnung($this->db, $this->obj, true);
							$processNewToStore = $processNew;
						}
					}
				}
				$this->obj->SetAbrechnungsJahr($currentYear);
			}
			//$this->obj->SetVorauszahlungLautKunde($prepaid);
			$returnValue=$this->obj->Store($this->db);
			if( $returnValue===true )
			{
				// stroe processes
				if ($processOldToStore!=null) $processOldToStore->Store($this->db);
				if ($processNewToStore!=null) $processNewToStore->Store($this->db);
				// Teilabrechung hinzufügen
				if( !is_object($fileObject) || get_class($fileObject)!="File" || $this->obj->AddFile($this->db, $fileObject) )
				{
					return true;
				}
				// Fehler
				if( is_object($fileObject) && get_class($fileObject)=="File" )$fileObject->DeleteMe($this->db);
				// Abrechnungsjahr wieder löschen, falls es gerade erzeugt wurde...
				$this->error["teilabrechnung"]="Teilabrechnung konnte nicht hinzugefügt werden";
			}else{
				// Abrechnungsjahr wieder löschen, falls es gerade erzeugt wurde...

				$fileObject->DeleteMe($this->db);
				$this->error["teilabrechnung"]="Systemfehler (".$returnValue.")";
			}
		}
		return false;
	}
	
	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben nachdem das Formular ausgegeben wurde
	 */
	public function PostPrint()
	{
		global $DOMAIN_HTTP_ROOT;
			?>
			<script type="text/javascript">
				<!--
					function UpdateUploadFormState(){
						var abrechnungsjahr=$('year').value;
						if( abrechnungsjahr!="" && !isNaN(abrechnungsjahr) && abrechnungsjahr>=1900 && $('contract').options[$('contract').selectedIndex].value!="") {
							$('teilabrechnung').disabled=false;
							$('btn_teilabrechnung').disabled=false;
						}else{
							$('teilabrechnung').disabled=true;
							$('btn_teilabrechnung').disabled=true;
						}
					}
					UpdateUploadFormState();

					function CreateNewAddress(type)
					{
						var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/createAddress.php5?<?=SID;?>&type='+type, '_createAddress', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
						//newWin.moveTo(width,height);
						newWin.focus();
					}
					
					function SetAddress(type, name)
					{
						if( type==<?=AM_ADDRESSDATA_TYPE_OWNER;?> )$('eigentuemer').value=name;
						if( type==<?=AM_ADDRESSDATA_TYPE_TRUSTEE;?> )$('verwalter').value=name;
						if( type==<?=AM_ADDRESSDATA_TYPE_ADVOCATE;?> )$('anwalt').value=name;
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
	
}
?>