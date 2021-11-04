<?php
/**
 * Contract form
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class ContractFormData extends FormData 
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
	public function ContractFormData($formElementValues, $object, $db, $restrictedMode=false)
	{
		$this->restrictedMode = $restrictedMode;
		parent::FormData($formElementValues, $object, $db);
	}

	/**
	 * This function have to initialize all form elements
	 * @param boolean $edit				Edit (true) or Create (true) Mode
	 * @param boolean $loadFromObject	Data should be read from data object (true) or not (false)
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		global $CM_LOCATION_TYPES;
		global $userManager;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "contract.png";
		$this->options["icontext"] = "Vertrag ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject )
		{
			$cShop = $this->obj->GetShop();
			if ($cShop!=null)
			{
				$this->formElementValues["cShop"] = $cShop->GetPkey();
				$cLocation = $cShop->GetLocation();
				if ($cLocation!=null)
				{
					$this->formElementValues["location"] = $cLocation->GetPKey();
				}
			}
			$this->formElementValues["mietflaeche_qm"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetMietflaecheQM() );
			$this->formElementValues["umlageflaeche_qm"]=HelperLib::ConvertFloatToLocalizedString(  $this->obj->GetUmlageflaecheQM() );
			// Mietvertragsmanagement
			$this->formElementValues["mvBeginn"] = $this->obj->GetMvBeginn()==0 ? "" : date("d.m.Y", $this->obj->GetMvBeginn());
			$this->formElementValues["mvEndeErstmalsMoeglich"] = $this->obj->GetMvEndeErstmalsMoeglich()==0 ? "" : date("d.m.Y", $this->obj->GetMvEndeErstmalsMoeglich());
			$this->formElementValues["mvEnde"] = $this->obj->GetMvEnde()==0 ? "" : date("d.m.Y", $this->obj->GetMvEnde());
			$this->formElementValues["nurVertragsauszuegeVorhanden"] = $this->obj->GetNurAuszuegeVorhanden();
			$this->formElementValues["mietvertragsdokumenteVollstaendig"] = $this->obj->GetMietvertragsdokumenteVollstaendig();
			$this->formElementValues["eigentuemer"]=$this->obj->GetEigentuemer() == null ? "" : $this->obj->GetEigentuemer()->GetAddressIDString();
			$this->formElementValues["verwalter"]=$this->obj->GetVerwalter() == null ? "" : $this->obj->GetVerwalter()->GetAddressIDString();
			// Nicht vereinbarte Abrechnungspositionen
			$this->formElementValues["nichtVereinbarteAbrPos"]=$this->obj->GetBemerkungNichtVereinbarteAbrPos();
			// Wartung, Instandhaltung und Instandsetzung
			$this->formElementValues["instandhaltungAllgemein"]=$this->obj->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_WARTUNG_INSTANDHALTUNG_INSTANDSETZUNG);
			$this->formElementValues["deckelungInstandhaltung"]=$this->obj->GetDeckelungInstandhaltung() ? "on" : "";
			//$this->formElementValues["deckelungInstandhaltungBeschreibung"]=$this->obj->GetDeckelungInstandhaltungBeschreibung();
			// Verwaltung und Management
			$this->formElementValues["verwaltungManagementAllgemein"]=$this->obj->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_VERWALTUNG_UND_MANAGEMENT);
			$this->formElementValues["deckelungVerwaltungManagement"]=$this->obj->GetDeckelungVerwaltungManagement() ? "on" : "";
			//$this->formElementValues["deckelungVerwaltungManagementBeschreibung"]=$this->obj->GetDeckelungVerwaltungManagementBeschreibung();
			//$this->formElementValues["gesetzlicheDefVerwaltungManagement"]=$this->obj->GetGesetzlicheDefinitionVerwaltungManagement() ? "on" : "";
			// Heizkosten
			$this->formElementValues["heizkostenAllgemein"]=$this->obj->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_HEIZKOSTEN);
			// Lüftung und Klimatisierung
			$this->formElementValues["lueftungKlimatisierungAllgemein"]=$this->obj->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_LUEFTUNG_UND_KLIMATISIERUNG);
			// Allgemeinstrom
			$this->formElementValues["allgemeinstromAllgemein"] = $this->obj->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_ALLGEMEINSTROM);
			// Versicherung
			$this->formElementValues["versicherungAllgemein"] = $this->obj->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_VERSICHERUNGEN);
			// Öffentliche Abgaben
			$this->formElementValues["oeffentlicheAbgabenAllgemein"] = $this->obj->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_OEFFENTLICHE_ABGABEN);
			// Entsorgung
			$this->formElementValues["entsorgungAllgemein"] = $this->obj->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_ENTSORGUNG);
			// Reinigung
			$this->formElementValues["reinigungPflegeAllgemein"] = $this->obj->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_REINIGUNG_UND_PFLEGE);
			// Sonstiges
			$this->formElementValues["sonstigesAllgemein"] = $this->obj->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_SONSTIGES);
			// Allgemeine Bemerkung Umlageschlüssel
			$this->formElementValues["bemerkungUmlageschluesselAllgemein"] = $this->obj->GetBemerkungUmlageschluesselAllgemein();
			// Allgemeine Bemerkung Gesamtkostendeckelung
			$this->formElementValues["bemerkungGesamtkostendeckelungAllgemein"] = $this->obj->GetBemerkungGesamtkostendeckelungAllgemein();
			// Allgemeine Bemerkung Zusammensetzung Gesamtkosten
			$this->formElementValues["bemerkungZusammensetzungGesamtkostenAllgemein"] = $this->obj->GetBemerkungZusammensetzungGesamtkostenAllgemein();
			
			$this->formElementValues["zurueckBehaltungAusgeschlossen"]=$this->obj->GetZurueckBehaltungAusgeschlossen();
			$this->formElementValues["zurueckBehaltungAusgeschlossenBeschreibung"]=$this->obj->GetZurueckBehaltungAusgeschlossenBeschreibung();
			/*$this->formElementValues["nurGrundsteuererhoehungUmlegbar"]=$this->obj->GetNurGrundsteuererhoehungUmlegbar() ? "on" : "";
			$this->formElementValues["nurGrundsteuererhoehungUmlegbarBeschreibung"]=$this->obj->GetNurGrundsteuererhoehungUmlegbarBeschreibung();
			$this->formElementValues["nurVersicherungserhoehungUmlegbar"]=$this->obj->GetNurVersicherungserhoehungUmlegbar() ? "on" : "";
			$this->formElementValues["nurVersicherungserhoehungUmlegbarBeschreibung"]=$this->obj->GetNurVersicherungserhoehungUmlegbarBeschreibung();
			$this->formElementValues["sonstigeBesonderheiten"]=$this->obj->GetSonstigeBesonderheiten() ? "on" : "";
			$this->formElementValues["sonstigeBesonderheitenBeschreibung"]=$this->obj->GetSonstigeBesonderheitenBeschreibung();*/
			$this->formElementValues["vertragErfasst"]=$this->obj->GetVertragErfasst();
			$this->formElementValues["werbegemeinschaft"]=$this->obj->GetWerbegemeinschaft() ? "on" : "";
			$this->formElementValues["werbegemeinschaftBeschreibung"]=$this->obj->GetWerbegemeinschaftBeschreibung();
		}
		
		// check if shop is in one ore more groups
		$numEffectedGroups = WorkflowManager::GetProcessStatusGroupCountOfContract($this->db, $this->obj);
		$currencyAllowedToChange = $this->obj->IsAttributeAllowedToChange($this->db);
		$inGroup = $numEffectedGroups>0 ? true : false;
		$otherContracts = Array();
		if ($numEffectedGroups==1) $otherContracts = WorkflowManager::GetContractsInSameProcessStatusGroup($this->db, $this->obj);
		$showSub2 = (!$currencyAllowedToChange || count($otherContracts)>0) ? true : false;
		
		if ($loadFromObject || $inGroup)
		{
			if ($loadFromObject || !$currencyAllowedToChange)
			{
				$this->formElementValues["currency"] = $this->obj->GetCurrency();
			}
			$cShop = $this->obj->GetShop();
			if ($cShop!=null)
			{
				if($this->restrictedMode) $this->formElementValues["cShop"] = $cShop->GetPkey();
				$cLocation = $cShop->GetLocation();
				if ($cLocation!=null)
				{
					if($this->restrictedMode) $this->formElementValues["location"] = $cLocation->GetPKey();
					$cCompany = $cLocation->GetCompany();
					if ($cCompany!=null)
					{
						$this->formElementValues["company"] = $cCompany->GetPKey();
						$cGroup = $cCompany->GetGroup();
						if ($cGroup!=null)
						{
							$this->formElementValues["group"] = $cGroup->GetPKey();
						}
					}
				}
			}
		}
		// Erzeugen des leeren Forms bzw. aus den postparametern
		global $customerManager;
		$emptyOptions=Array();
		// Hier wird der value Selected definiert
		// Soll nicht gemacht werden! Da der URL-Parameter als Vordefiniert gelten soll
		$emptyOptions[]=Array("name" => "Bitte wählen...", "value" => "");
		// Gruppe
		$options=$emptyOptions;
		$objects=$customerManager->GetGroups($_SESSION["currentUser"], '', 'name', 0, 0, 0);
		// Implementierung 27.10.2021 S.B. 
		//$predefname = "Mexx";
		//$options[]=Array("name" => $predefname , "value" => $this->formElementValues["mietflaeche_qm"] );
		for( $a=0; $a<count($objects); $a++){
			$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("group", "Gruppe* ".($inGroup ? "<sup>1)</sup>" : ""), $this->formElementValues["group"], false, $this->error["group"], $options, false, null, Array(), ($inGroup || $this->restrictedMode));
		// Firma
		$options=$emptyOptions;
		$currentGroup=$customerManager->GetGroupByID($_SESSION["currentUser"], $this->formElementValues["group"]);
		if($currentGroup!=null){
			$objects=$currentGroup->GetCompanys($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("company", "Firma* ".($inGroup ? "<sup>1)</sup>" : ""), $this->formElementValues["company"], false, $this->error["company"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=1&param01='+$('group').options[$('group').selectedIndex].value+'", "$('group').onchange=function(){%REQUESTCALL%;};", "company"), Array(), ($inGroup || $this->restrictedMode) );
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

		// Währung
		global $customerManager;
		$currencies = $customerManager->GetCurrencies();
		$options = $emptyOptions;
		foreach($currencies as $currency)
		{
			$options[] = Array("name" => $currency->GetName()." (".$currency->GetIso4217().")", "value" => $currency->GetIso4217());
		}
		$this->elements[] = new DropdownElement("currency", "Währung* ".($showSub2 ? "<sup>2)</sup>" : ""), $this->formElementValues["currency"], false, $this->error["currency"], $options, false, null, Array(), !$currencyAllowedToChange);
		$this->elements[] = new TextElement("mietflaeche_qm", "Mietfläche (qm)", $this->formElementValues["mietflaeche_qm"], true, $this->error["mietflaeche_qm"]);
		$this->elements[] = new TextElement("umlageflaeche_qm", "Umlagefläche (qm)", $this->formElementValues["umlageflaeche_qm"], true, $this->error["umlageflaeche_qm"]);
		$this->elements[] = new DateElement("mvBeginn", "Mietvertragsbeginn", $this->formElementValues["mvBeginn"], false, $this->error["mvBeginn"]);
		$this->elements[] = new DateElement("mvEndeErstmalsMoeglich", "Erstmals mögliches Mietvertragsende", $this->formElementValues["mvEndeErstmalsMoeglich"], false, $this->error["mvEndeErstmalsMoeglich"]);
		$this->elements[] = new DateElement("mvEnde", "Aktuelles Mietvertragsende", $this->formElementValues["mvEnde"], false, $this->error["mvEnde"]);
		
		$buttons = Array();
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/address_new.png", "help" => "Neuen Ansprechpartner anlegen", "href" => "javascript:CreateNewAddress(".AM_ADDRESSDATA_TYPE_OWNER.")");
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany(".AM_ADDRESSDATA_TYPE_OWNER.")");
		$this->elements[] = new TextElement("eigentuemer", "Eigentümer", $this->formElementValues["eigentuemer"], false, $this->error["eigentuemer"], false, new SearchTextDynamicContent(  $SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 6, "param01" => AM_ADDRESSDATA_TYPE_NONE), "", "eigentuemer"), $buttons);
		$buttons = Array();
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/address_new.png", "help" => "Neuen Ansprechpartner anlegen", "href" => "javascript:CreateNewAddress(".AM_ADDRESSDATA_TYPE_TRUSTEE.")");
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany(".AM_ADDRESSDATA_TYPE_TRUSTEE.")");
		$this->elements[] = new TextElement("verwalter", "Verwalter", $this->formElementValues["verwalter"], false, $this->error["verwalter"], false, new SearchTextDynamicContent(  $SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 6, "param01" => AM_ADDRESSDATA_TYPE_NONE), "", "verwalter"), $buttons);

		$options=Array();
		$options[]=Array("name" => "Ja", "value" => Contract::CONTRACT_YES);
		$options[]=Array("name" => "Nein", "value" => Contract::CONTRACT_NO);
		$this->elements[] = new RadioButtonElement("nurVertragsauszuegeVorhanden", "Nur Vertragsauszüge vorhanden", $this->formElementValues["nurVertragsauszuegeVorhanden"], false, $this->error["nurVertragsauszuegeVorhanden"], $options);
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => Contract::MVC_UNKNOWN);
		$options[]=Array("name" => "Keine neuen Unterlagen hinzugefügt", "value" => Contract::MVC_NO_ADDITIONAL_FILES);
		$options[]=Array("name" => "Neue Unterlagen hinuzgefügt", "value" => Contract::MVC_NEW_FILES_ADDED);
		$options[]=Array("name" => "Neue Unterlagen vorhanden aber nicht hinzugefügt", "value" => Contract::MVC_NEW_FILES_AVAILABLE_NOT_ADDED);
		$this->elements[] = new DropdownElement("mietvertragsdokumenteVollstaendig", "Mietvertragsdokumente vollständig", $this->formElementValues["mietvertragsdokumenteVollstaendig"], false, $this->error["mietvertragsdokumenteVollstaendig"], $options);
		// Nicht vereinbarte Abrechnungspositionen
		$this->elements[] = new TextAreaElement("nichtVereinbarteAbrPos", "Bemerkung Nicht vereinbarte Abrechnungspositionen", $this->formElementValues["nichtVereinbarteAbrPos"], false, $this->error["nichtVereinbarteAbrPos"]);
		// Wartung, Instandhaltung und Instandsetzung
		$this->elements[] = new TextAreaElement("instandhaltungAllgemein", "Bemerkung Wartung, Instandhaltung und Instandsetzung", $this->formElementValues["instandhaltungAllgemein"], false, $this->error["instandhaltungAllgemein"]);
		$this->elements[] = new CheckboxElement("deckelungInstandhaltung", "Deckelung/Pauschale vereinbart", $this->formElementValues["deckelungInstandhaltung"], false, $this->error["deckelungInstandhaltung"]);
		//$this->elements[] = new TextAreaElement("deckelungInstandhaltungBeschreibung", "Beschreibung Deckelung/Pauschale", $this->formElementValues["deckelungInstandhaltungBeschreibung"], false, $this->error["deckelungInstandhaltungBeschreibung"]);
		// Verwaltung und Management
		$this->elements[] = new TextAreaElement("verwaltungManagementAllgemein", "Bemerkung Verwaltung und Management", $this->formElementValues["verwaltungManagementAllgemein"], false, $this->error["verwaltungManagementAllgemein"]);
		$this->elements[] = new CheckboxElement("deckelungVerwaltungManagement", "Deckelung/Pauschale vereinbart", $this->formElementValues["deckelungVerwaltungManagement"], false, $this->error["deckelungVerwaltungManagement"]);
		//$this->elements[] = new TextAreaElement("deckelungVerwaltungManagementBeschreibung", "Beschreibung Deckelung/Pauschale", $this->formElementValues["deckelungVerwaltungManagementBeschreibung"], false, $this->error["deckelungVerwaltungManagementBeschreibung"]);
		//$this->elements[] = new CheckboxElement("gesetzlicheDefVerwaltungManagement", "Verwaltung und Management auf gesetzliche Definition beschränkt", $this->formElementValues["gesetzlicheDefVerwaltungManagement"], false, $this->error["gesetzlicheDefVerwaltungManagement"]);
		// Heizkosten
		$this->elements[] = new TextAreaElement("heizkostenAllgemein", "Bemerkung Heizkosten", $this->formElementValues["heizkostenAllgemein"], false, $this->error["heizkostenAllgemein"]);
		// Lüftung und Klimatisierung
		$this->elements[] = new TextAreaElement("lueftungKlimatisierungAllgemein", "Bemerkung Lüftung und Klimatisierung", $this->formElementValues["lueftungKlimatisierungAllgemein"], false, $this->error["lueftungKlimatisierungAllgemein"]);
		// Allgemeinstrom
		$this->elements[] = new TextAreaElement("allgemeinstromAllgemein", "Bemerkung Allgemeinstrom", $this->formElementValues["allgemeinstromAllgemein"], false, $this->error["allgemeinstromAllgemein"]);
		// Versicherung
		$this->elements[] = new TextAreaElement("versicherungAllgemein", "Bemerkung Versicherung", $this->formElementValues["versicherungAllgemein"], false, $this->error["versicherungAllgemein"]);
		// Öffentliche Abgaben
		$this->elements[] = new TextAreaElement("oeffentlicheAbgabenAllgemein", "Bemerkung Öffentliche Abgaben", $this->formElementValues["oeffentlicheAbgabenAllgemein"], false, $this->error["oeffentlicheAbgabenAllgemein"]);
		// Entsorgung
		$this->elements[] = new TextAreaElement("entsorgungAllgemein", "Bemerkung Entsorgung", $this->formElementValues["entsorgungAllgemein"], false, $this->error["entsorgungAllgemein"]);
		// Reinigung
		$this->elements[] = new TextAreaElement("reinigungPflegeAllgemein", "Bemerkung Reinigung", $this->formElementValues["reinigungPflegeAllgemein"], false, $this->error["reinigungPflegeAllgemein"]);
		// Sonstiges
		$this->elements[] = new TextAreaElement("sonstigesAllgemein", "Bemerkung Sonstiges", $this->formElementValues["sonstigesAllgemein"], false, $this->error["sonstigesAllgemein"]);
		// Allgemeine Bemerkung Umlageschlüssel
		$this->elements[] = new TextAreaElement("bemerkungUmlageschluesselAllgemein", "Bemerkung Umlageschlüssel", $this->formElementValues["bemerkungUmlageschluesselAllgemein"], false, $this->error["bemerkungUmlageschluesselAllgemein"]);
		// Allgemeine Bemerkung Gesamtkostendeckelung
		$this->elements[] = new TextAreaElement("bemerkungGesamtkostendeckelungAllgemein", "Bemerkung Gesamtkostendeckelung", $this->formElementValues["bemerkungGesamtkostendeckelungAllgemein"], false, $this->error["bemerkungGesamtkostendeckelungAllgemein"]);
		// Allgemeine Bemerkung Zusammensetzung Gesamtkosten
		$this->elements[] = new TextAreaElement("bemerkungZusammensetzungGesamtkostenAllgemein", "Bemerkung Zusammensetzung Gesamtkosten", $this->formElementValues["bemerkungZusammensetzungGesamtkostenAllgemein"], false, $this->error["bemerkungZusammensetzungGesamtkostenAllgemein"]);
		
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => Contract::CONTRACT_YES);
		$options[]=Array("name" => "Nein", "value" => Contract::CONTRACT_NO);
		$this->elements[] = new RadioButtonElement("zurueckBehaltungAusgeschlossen", "Zurückbehaltung ausgeschlossen", $this->formElementValues["zurueckBehaltungAusgeschlossen"], false, $this->error["zurueckBehaltungAusgeschlossen"], $options);
		
		$this->elements[] = new TextAreaElement("zurueckBehaltungAusgeschlossenBeschreibung", "Beschreibung", $this->formElementValues["zurueckBehaltungAusgeschlossenBeschreibung"], false, $this->error["zurueckBehaltungAusgeschlossenBeschreibung"]);
		/*$this->elements[] = new CheckboxElement("nurGrundsteuererhoehungUmlegbar", "Nur Grundsteuererhöhung umlegbar", $this->formElementValues["nurGrundsteuererhoehungUmlegbar"], false, $this->error["nurGrundsteuererhoehungUmlegbar"]);
		$this->elements[] = new TextAreaElement("nurGrundsteuererhoehungUmlegbarBeschreibung", "Beschreibung", $this->formElementValues["nurGrundsteuererhoehungUmlegbarBeschreibung"], false, $this->error["nurGrundsteuererhoehungUmlegbarBeschreibung"]);
		$this->elements[] = new CheckboxElement("nurVersicherungserhoehungUmlegbar", "Nur Versicherungserhöhung umlegbar", $this->formElementValues["nurVersicherungserhoehungUmlegbar"], false, $this->error["nurVersicherungserhoehungUmlegbar"]);
		$this->elements[] = new TextAreaElement("nurVersicherungserhoehungUmlegbarBeschreibung", "Beschreibung", $this->formElementValues["nurVersicherungserhoehungUmlegbarBeschreibung"], false, $this->error["nurVersicherungserhoehungUmlegbarBeschreibung"]);
		$this->elements[] = new CheckboxElement("sonstigeBesonderheiten", "Sonstige Besonderheiten", $this->formElementValues["sonstigeBesonderheiten"], false, $this->error["sonstigeBesonderheiten"]);
		$this->elements[] = new TextAreaElement("sonstigeBesonderheitenBeschreibung", "Beschreibung", $this->formElementValues["sonstigeBesonderheitenBeschreibung"], false, $this->error["sonstigeBesonderheitenBeschreibung"]);*/
		$this->elements[] = new CheckboxElement("werbegemeinschaft", "Werbegemeinschaft", $this->formElementValues["werbegemeinschaft"], false, $this->error["werbegemeinschaft"]);
		$this->elements[] = new TextAreaElement("werbegemeinschaftBeschreibung", "Beschreibung", $this->formElementValues["werbegemeinschaftBeschreibung"], false, $this->error["werbegemeinschaftBeschreibung"]);
		// Dateien
		if( isset($_POST["deleteFile_mietvertrag"]) && $_POST["deleteFile_mietvertrag"]!="" ){
			$fileToDelete=new File($this->db);
			if( $fileToDelete->Load((int)$_POST["deleteFile_mietvertrag"], $this->db)===true ){
				$this->obj->RemoveFile($this->db, $fileToDelete);
			}
		}
		if( isset($_POST["deleteFile_mietvertraganlage"]) && $_POST["deleteFile_mietvertraganlage"]!="" ){
			$fileToDelete=new File($this->db);
			if( $fileToDelete->Load((int)$_POST["deleteFile_mietvertraganlage"], $this->db)===true ){
				$this->obj->RemoveFile($this->db, $fileToDelete);
			}
		}
		if( isset($_POST["deleteFile_mietvertragnachtrag"]) && $_POST["deleteFile_mietvertragnachtrag"]!="" ){
			$fileToDelete=new File($this->db);
			if( $fileToDelete->Load((int)$_POST["deleteFile_mietvertragnachtrag"], $this->db)===true ){
				$this->obj->RemoveFile($this->db, $fileToDelete);
			}
		}
		$this->elements[] = new FileElement("mietvertrag", "Mietvertrag", $this->formElementValues["mietvertrag"], false, $this->error["mietvertrag"], FM_FILE_SEMANTIC_MIETVERTRAG, $this->obj->GetFiles($this->db, FM_FILE_SEMANTIC_MIETVERTRAG) );
		$this->elements[] = new FileElement("mietvertraganlage", "Mietvertrag-Anlage", $this->formElementValues["mietvertraganlage"], false, $this->error["mietvertraganlage"], FM_FILE_SEMANTIC_MIETVERTRAGANLAGE, $this->obj->GetFiles($this->db, FM_FILE_SEMANTIC_MIETVERTRAGANLAGE) );
		$this->elements[] = new FileElement("mietvertragnachtrag", "Mietvertrag-Nachtrag", $this->formElementValues["mietvertragnachtrag"], false, $this->error["mietvertragnachtrag"], FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG, $this->obj->GetFiles($this->db, FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG) );
		$this->elements[] = new CheckboxElement("vertragErfasst", "Vertrag vollständig erfasst", $this->formElementValues["vertragErfasst"], false, $this->error["vertragErfasst"]);


		if( isset($_POST["deleteFile_stammdatenblatt"]) && $_POST["deleteFile_stammdatenblatt"]!="" ){

			$fileToDelete=new File($this->db);
			if( $fileToDelete->Load((int)$_POST["deleteFile_stammdatenblatt"], $this->db)===true ){
				$fileToDelete->DeleteMe($this->db);
				$this->obj->setStammdatenblatt(null);
			}
		}
		$stammdatenblatt = $this->obj->getStammdatenblatt();
		$this->elements[] = new FileElement("stammdatenblatt", "Stammdatenblatt", $this->formElementValues["stammdatenblatt"], false, $this->error["stammdatenblatt"], FM_FILE_SEMANTIC_STAMMDATENBLATT, $stammdatenblatt==null ? Array() : Array($stammdatenblatt), true );


		if ($numEffectedGroups>0)
		{
			ob_start();
			?>
			<div style="border-top: solid 1px #cccccc; padding: 5px;">
				<sup>1)</sup> Die Gruppe und Firma kann nicht geändert werden, da sich dieser Vertrag in einem Paket befindet.<br/><br/>
				<?
				if(!$currencyAllowedToChange)
				{ ?>
					<sup>2)</sup> Die Währung kann nicht geändert werden, da dieser Vertrag (oder ein anderer Vertrag aus diesem Paket) in
					<strong>mehreren</Strong> Pakten verwendet wird.<br/><br/>
					<?
				}
				else
				{
					if(count($otherContracts) > 0)
					{
						?>
						<sup>2)</sup> Dieser Vertrag befindet sich in einem Paket mit mehreren Verträgen.<br/>
						Um die Konsistenz des Paketes zu gewährleisten, werden Änderungen<br/>
						an der Währung auch in die anderen Verträge übernommen.<br/>
						<br/>
						Folgende Verträge sind davon betroffen: <br/>
						<table border="0" cellpadding="2" cellspacing="0" width="100%">
							<tr>
								<td class="TAPMatrixHeader2" align="left" valign="top">Gruppe</td>
								<td class="TAPMatrixHeader2" align="left" valign="top">Firma</td>
								<td class="TAPMatrixHeader2" align="left" valign="top">Standort</td>
								<td class="TAPMatrixHeader2" align="left" valign="top">Laden</td>
								<td class="TAPMatrixHeader2" align="left" valign="top">SFM-ID</td>
								<td class="TAPMatrixHeader2" align="left" valign="top">Vertrag</td>
							</tr>
							<?

							foreach($otherContracts as $otherContract)
							{
								$shop = $otherContract->GetShop();
								$locationTemp = $shop->GetLocation();
								$companyTemp = $locationTemp == null ? null : $locationTemp->GetCompany();
								$groupTemp = $companyTemp == null ? null : $companyTemp->GetGroup();
								?>
								<tr>
									<td class="TAPMatrixRow" align="left" valign="top"><?= ($groupTemp == null ? "-" : $groupTemp->GetName()); ?></td>
									<td class="TAPMatrixRow" align="left" valign="top"><?= ($companyTemp == null ? "-" : $companyTemp->GetName()); ?></td>
									<td class="TAPMatrixRow" align="left" valign="top"><?= ($locationTemp == null ? "-" : $locationTemp->GetName()); ?></td>
									<td class="TAPMatrixRow" align="left" valign="top"><?= $shop->GetName(); ?></td>
									<td class="TAPMatrixRow" align="left" valign="top"><?= $shop->GetRSID(); ?></td>
									<td class="TAPMatrixRow" align="left" valign="top">V<?= $otherContract->GetPKey(); ?></td>
								</tr>
								<?
							}
							?>
						</table>
						<?
					}
				}
				?>
			</div>
			<?
			$html = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($html, "&#160;");
		}
		
	}
	
	/**
	 * The data should be stored to data object
	 * @return boolean
	 */
	public function Store()
	{
		$currencyAllowedToChange = $this->obj->IsAttributeAllowedToChange($this->db);
		
		global $addressManager;
		$this->error=array();
		$valueTemp=TextElement::GetFloat( $this->formElementValues["mietflaeche_qm"] );
		if( $valueTemp===false )$this->error["mietflaeche_qm"]="Bitte geben Sie eine gültige Zahl ein";
		else $this->obj->SetMietflaecheQM( $valueTemp );
		$valueTemp=TextElement::GetFloat( $this->formElementValues["umlageflaeche_qm"] );
		if( $valueTemp===false )$this->error["umlageflaeche_qm"]="Bitte geben Sie eine gültige Zahl ein";
		else $this->obj->SetUmlageflaecheQM( $valueTemp );
		// Mietvertragsmanagement
		if (trim($this->formElementValues["mvBeginn"])!="")
		{
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["mvBeginn"]);
			if ($tempValue===false) $this->error["mvBeginn"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $this->obj->SetMvBeginn($tempValue);
		}
		else
		{
			$this->obj->SetMvBeginn(0);
		}
		if (trim($this->formElementValues["mvEndeErstmalsMoeglich"])!="")
		{
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["mvEndeErstmalsMoeglich"]);
			if ($tempValue===false) $this->error["mvEndeErstmalsMoeglich"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $this->obj->SetMvEndeErstmalsMoeglich($tempValue);
		}
		else
		{
			$this->obj->SetMvEndeErstmalsMoeglich(0);
		}
		if (trim($this->formElementValues["mvEnde"])!="")
		{
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["mvEnde"]);
			if ($tempValue===false) $this->error["mvEnde"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $this->obj->SetMvEnde($tempValue);
		}
		else
		{
			$this->obj->SetMvEnde(0);
		}
		
		if (trim($this->formElementValues["eigentuemer"])!="")
		{
			$tempAddressData = AddressManager::GetAddessById($this->db, $this->formElementValues["eigentuemer"]);
			if ($tempAddressData===null)
			{
				$this->error["eigentuemer"] = "Der eingegebene Eigentümer konnte nicht gefunden werden";
			}
			else
			{
				$this->obj->SetEigentuemer($tempAddressData);
				$this->formElementValues["eigentuemer"] = $tempAddressData->GetAddressIDString();
			}
		}
		else
		{
			$this->obj->SetEigentuemer(null);
		}
		//if( $this->obj->GetEigentuemer()==null )$this->error["eigentuemer"]="Bitte geben Sie den Eigentümer ein";
		
		if (trim($this->formElementValues["verwalter"])!="")
		{
			$tempAddressData = AddressManager::GetAddessById($this->db, $this->formElementValues["verwalter"]);
			if ($tempAddressData===null)
			{
				$this->error["verwalter"]="Der eingegebene Verwalter konnte nicht gefunden werden";
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
		//if( $this->obj->GetVerwalter()==null )$this->error["verwalter"]="Bitte geben Sie den Verwalter ein";
		
		if ((int)$this->formElementValues["nurVertragsauszuegeVorhanden"]>0)
		{
			$this->obj->SetNurAuszuegeVorhanden((int)$this->formElementValues["nurVertragsauszuegeVorhanden"]);
		}
		$this->obj->SetMietvertragsdokumenteVollstaendig((int)$this->formElementValues["mietvertragsdokumenteVollstaendig"]);
		// Währung
		if ($currencyAllowedToChange)
		{
			if (!$this->obj->SetCurrency($this->db, $this->formElementValues["currency"])) $this->error["currency"]="Bitte wählen Sie eine Währung aus";
		}
		// Nicht vereinbarte Abrechnungspositionen
		$this->obj->SetBemerkungNichtVereinbarteAbrPos($this->formElementValues["nichtVereinbarteAbrPos"] );
		// Wartung, Instandhaltung und Instandsetzung
		$this->obj->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_WARTUNG_INSTANDHALTUNG_INSTANDSETZUNG, $this->formElementValues["instandhaltungAllgemein"] );
		$this->obj->SetDeckelungInstandhaltung( $this->formElementValues["deckelungInstandhaltung"]=="on" ? true : false );
		//$this->obj->SetDeckelungInstandhaltungBeschreibung( $this->formElementValues["deckelungInstandhaltungBeschreibung"] );
		// Verwaltung und Management
		$this->obj->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_VERWALTUNG_UND_MANAGEMENT, $this->formElementValues["verwaltungManagementAllgemein"] );
		$this->obj->SetDeckelungVerwaltungManagement( $this->formElementValues["deckelungVerwaltungManagement"]=="on" ? true : false );
		//$this->obj->SetDeckelungVerwaltungManagementBeschreibung( $this->formElementValues["deckelungVerwaltungManagementBeschreibung"] );
		//$this->obj->SetGesetzlicheDefinitionVerwaltungManagement( $this->formElementValues["gesetzlicheDefVerwaltungManagement"]=="on" ? true : false );
		// Heizkosten
		$this->obj->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_HEIZKOSTEN, $this->formElementValues["heizkostenAllgemein"]);
		// Lüftung und Klimatisierung
		$this->obj->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_LUEFTUNG_UND_KLIMATISIERUNG, $this->formElementValues["lueftungKlimatisierungAllgemein"]);
		// Allgemeinstrom
		$this->obj->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_ALLGEMEINSTROM, $this->formElementValues["allgemeinstromAllgemein"]);
		// Versicherung
		$this->obj->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_VERSICHERUNGEN, $this->formElementValues["versicherungAllgemein"]);
		// Öffentliche Abgaben
		$this->obj->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_OEFFENTLICHE_ABGABEN, $this->formElementValues["oeffentlicheAbgabenAllgemein"]);
		// Entsorgung
		$this->obj->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_ENTSORGUNG, $this->formElementValues["entsorgungAllgemein"]);
		// Reinigung
		$this->obj->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_REINIGUNG_UND_PFLEGE, $this->formElementValues["reinigungPflegeAllgemein"]);
		// Sonstiges
		$this->obj->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_SONSTIGES, $this->formElementValues["sonstigesAllgemein"]);
		// Allgemeine Bemerkung Umlageschlüssel
		$this->obj->SetBemerkungUmlageschluesselAllgemein($this->formElementValues["bemerkungUmlageschluesselAllgemein"]);
		// Allgemeine Bemerkung Gesamtkostendeckelung
		$this->obj->SetBemerkungGesamtkostendeckelungAllgemein($this->formElementValues["bemerkungGesamtkostendeckelungAllgemein"]);
		// Allgemeine Bemerkung Zusammensetzung Gesamtkosten
		$this->obj->SetBemerkungZusammensetzungGesamtkostenAllgemein($this->formElementValues["bemerkungZusammensetzungGesamtkostenAllgemein"]);
		
		if ((int)$this->formElementValues["zurueckBehaltungAusgeschlossen"]>0)
		{
			$this->obj->SetZurueckBehaltungAusgeschlossen((int)$this->formElementValues["zurueckBehaltungAusgeschlossen"]);
		}
		$this->obj->SetZurueckBehaltungAusgeschlossenBeschreibung( $this->formElementValues["zurueckBehaltungAusgeschlossenBeschreibung"] );
		/*$this->obj->SetNurGrundsteuererhoehungUmlegbar( $this->formElementValues["nurGrundsteuererhoehungUmlegbar"]=="on" ? true : false );
		$this->obj->SetNurGrundsteuererhoehungUmlegbarBeschreibung( $this->formElementValues["nurGrundsteuererhoehungUmlegbarBeschreibung"] );
		$this->obj->SetNurVersicherungserhoehungUmlegbar( $this->formElementValues["nurVersicherungserhoehungUmlegbar"]=="on" ? true : false );
		$this->obj->SetNurVersicherungserhoehungUmlegbarBeschreibung( $this->formElementValues["nurVersicherungserhoehungUmlegbarBeschreibung"] );
		$this->obj->SetSonstigeBesonderheiten( $this->formElementValues["sonstigeBesonderheiten"]=="on" ? true : false );
		$this->obj->SetSonstigeBesonderheitenBeschreibung( $this->formElementValues["sonstigeBesonderheitenBeschreibung"] );*/
		$this->obj->SetWerbegemeinschaft( $this->formElementValues["werbegemeinschaft"]=="on" ? true : false );
		$this->obj->SetWerbegemeinschaftBeschreibung( $this->formElementValues["werbegemeinschaftBeschreibung"] );
		$this->obj->SetVertragErfasst( $this->formElementValues["vertragErfasst"]=="on" ? true : false );

		// Laden seten
		if (!$this->restrictedMode)
		{
			$cShop = new CShop($this->db);
			if(!isset($this->formElementValues["cShop"]) || $this->formElementValues["cShop"] == "" || $cShop->Load((int)$this->formElementValues["cShop"],
					$this->db) !== true)
			{
				unset($cShop);
				$this->error["cShop"] = "Bitte wählen Sie den zugehörigen Laden aus";
			}
			$this->obj->SetShop($this->db, $cShop);
		}
		// Stammdatenblatt
		$fileObject=FileElement::GetFileElement($this->db, $_FILES["stammdatenblatt"], FM_FILE_SEMANTIC_STAMMDATENBLATT);
		if( is_object($fileObject) && get_class($fileObject)=="File" ){

			$oldFile = $this->obj->getStammdatenblatt();
			$this->obj->setStammdatenblatt($fileObject);
			// Alte Datei löschen wenn vorhanden
			if ($oldFile!=null) $oldFile->DeleteMe($this->db);
		}

		if( count($this->error)==0 ){
			$returnValue=$this->obj->Store($this->db);
			if( $returnValue===true ){
				// HIER braucht man den Check, ob man aus der Vorklassifizierung kommt...
				//if ($this->formElementValues["klassifikation"] === 1){
				//	$fileObject = 
				//}
				// Mietvertrag
				$fileObject=FileElement::GetFileElement($this->db, $_FILES["mietvertrag"], FM_FILE_SEMANTIC_MIETVERTRAG);
				if( !is_object($fileObject) || get_class($fileObject)!="File" ){
					if( $fileObject!==-1 ){
						$this->error["mietvertrag"]=FileElement::GetErrorText($fileObject);
						$returnValue=false;
					}
				}else{
					$this->obj->AddFile($this->db, $fileObject);
				}
				// Mietvertraganlage
				$fileObject=FileElement::GetFileElement($this->db, $_FILES["mietvertraganlage"], FM_FILE_SEMANTIC_MIETVERTRAGANLAGE);
				if( !is_object($fileObject) || get_class($fileObject)!="File" ){
					if( $fileObject!==-1 ){
						$this->error["mietvertraganlage"]=FileElement::GetErrorText($fileObject);
						$returnValue=false;
					}
				}else{
					$this->obj->AddFile($this->db, $fileObject);
				}
				// Mietvertragnachtrag
				$fileObject=FileElement::GetFileElement($this->db, $_FILES["mietvertragnachtrag"], FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG);
				if( !is_object($fileObject) || get_class($fileObject)!="File" ){
					if( $fileObject!==-1 ){
						$this->error["mietvertragnachtrag"]=FileElement::GetErrorText($fileObject);
						$returnValue=false;
					}
				}else{
					$this->obj->AddFile($this->db, $fileObject);
				}
				return $returnValue;
			}else{
				$this->error["cShop"]="Systemfehler (".$returnValue.")";
			}
		}
		return false;
	}
	
	/**
	 * This function can be used to output HTML data after the form
	 */
	public function PostPrint()
	{
		global $DOMAIN_HTTP_ROOT;
		?>
		<script type="text/javascript">
			<!--
				function UpdateUploadFormState(){
					var mietflaeche=$('mietflaeche_qm').value.replace(/\./g, "").replace(/,/g, ".");
					var umlageflaeche=$('umlageflaeche_qm').value.replace(/\./g, "").replace(/,/g, ".");
					if( mietflaeche!="" && !isNaN(mietflaeche) && umlageflaeche!="" && !isNaN(umlageflaeche) && $('cShop').options[$('cShop').selectedIndex].value!="") {
						$('mietvertrag').disabled=false;
						$('btn_mietvertrag').disabled=false;
						$('mietvertraganlage').disabled=false;
						$('btn_mietvertraganlage').disabled=false;
						$('mietvertragnachtrag').disabled=false;
						$('btn_mietvertragnachtrag').disabled=false;
					}else{
						$('mietvertrag').disabled=true;
						$('btn_mietvertrag').disabled=true;
						$('mietvertraganlage').disabled=true;
						$('btn_mietvertraganlage').disabled=true;
						$('mietvertragnachtrag').disabled=true;
						$('btn_mietvertragnachtrag').disabled=true;
					}
				}
				$('cShop').onchange=function(){
					UpdateUploadFormState();
				};
				$('mietflaeche_qm').onchange=function(){
					UpdateUploadFormState();
				};
				$('umlageflaeche_qm').onchange=function(){
					UpdateUploadFormState();
				};
				UpdateUploadFormState();

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
				}

			-->
		</script>
		<?
	}
	
}
?>