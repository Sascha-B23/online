<?php
/**
 * Status "Vertrag erfassen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasVertragErfassen extends NkasStatusFormDataEntry
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
		$numEffectedGroups = 0;
		$currencyAllowedToChange = true;
		$otherContracts = Array();
		$showSub1 = false;
		// Daten aus Objekt laden?
		if( $loadFromObject ){
			$abrechnungsjahr=$this->obj->GetAbrechnungsJahr();
			if( $abrechnungsjahr!=null ){
				$contract=$abrechnungsjahr->GetContract();
				if( $contract!=null ){
					$this->formElementValues["currency"] = $contract->GetCurrency();
					$this->formElementValues["mietflaeche_qm"]=HelperLib::ConvertFloatToLocalizedString( $contract->GetMietflaecheQM() );
					$this->formElementValues["umlageflaeche_qm"]=HelperLib::ConvertFloatToLocalizedString( $contract->GetUmlageflaecheQM() );
					// Mietvertragsmanagement
					$this->formElementValues["mvBeginn"] = $contract->GetMvBeginn()==0 ? "" : date("d.m.Y", $contract->GetMvBeginn());
					$this->formElementValues["mvEndeErstmalsMoeglich"] = $contract->GetMvEndeErstmalsMoeglich()==0 ? "" : date("d.m.Y", $contract->GetMvEndeErstmalsMoeglich());
					$this->formElementValues["mvEnde"] = $contract->GetMvEnde()==0 ? "" : date("d.m.Y", $contract->GetMvEnde());
					$this->formElementValues["nurVertragsauszuegeVorhanden"] = $contract->GetNurAuszuegeVorhanden();
					$this->formElementValues["mietvertragsdokumenteVollstaendig"] = $contract->GetMietvertragsdokumenteVollstaendig();
					$this->formElementValues["eigentuemer"]=$contract->GetEigentuemer() == null ? "" : $contract->GetEigentuemer()->GetAddressIDString();
					$this->formElementValues["verwalter"]=$contract->GetVerwalter() == null ? "" : $contract->GetVerwalter()->GetAddressIDString();
					// Nicht vereinbarte Abrechnungspositionen
					$this->formElementValues["nichtVereinbarteAbrPos"]=$contract->GetBemerkungNichtVereinbarteAbrPos();
					// Wartung, Instandhaltung und Instandsetzung
					$this->formElementValues["instandhaltungAllgemein"]=$contract->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_WARTUNG_INSTANDHALTUNG_INSTANDSETZUNG);
					$this->formElementValues["deckelungInstandhaltung"]=$contract->GetDeckelungInstandhaltung() ? "on" : "";
					//$this->formElementValues["deckelungInstandhaltungBeschreibung"]=$contract->GetDeckelungInstandhaltungBeschreibung();
					// Verwaltung und Management
					$this->formElementValues["verwaltungManagementAllgemein"]=$contract->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_VERWALTUNG_UND_MANAGEMENT);
					$this->formElementValues["deckelungVerwaltungManagement"]=$contract->GetDeckelungVerwaltungManagement() ? "on" : "";
					//$this->formElementValues["deckelungVerwaltungManagementBeschreibung"]=$contract->GetDeckelungVerwaltungManagementBeschreibung();
					//$this->formElementValues["gesetzlicheDefVerwaltungManagement"]=$contract->GetGesetzlicheDefinitionVerwaltungManagement() ? "on" : "";
					// Heizkosten
					$this->formElementValues["heizkostenAllgemein"]=$contract->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_HEIZKOSTEN);
					// Lüftung und Klimatisierung
					$this->formElementValues["lueftungKlimatisierungAllgemein"]=$contract->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_LUEFTUNG_UND_KLIMATISIERUNG);
					// Allgemeinstrom
					$this->formElementValues["allgemeinstromAllgemein"] = $contract->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_ALLGEMEINSTROM);
					// Versicherung
					$this->formElementValues["versicherungAllgemein"] = $contract->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_VERSICHERUNGEN);
					// Öffentliche Abgaben
					$this->formElementValues["oeffentlicheAbgabenAllgemein"] = $contract->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_OEFFENTLICHE_ABGABEN);
					// Entsorgung
					$this->formElementValues["entsorgungAllgemein"] = $contract->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_ENTSORGUNG);
					// Reinigung
					$this->formElementValues["reinigungPflegeAllgemein"] = $contract->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_REINIGUNG_UND_PFLEGE);
					// Sonstiges
					$this->formElementValues["sonstigesAllgemein"] = $contract->GetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_SONSTIGES);
					// Allgemeine Bemerkung Umlageschlüssel
					$this->formElementValues["bemerkungUmlageschluesselAllgemein"] = $contract->GetBemerkungUmlageschluesselAllgemein();
					// Allgemeine Bemerkung Gesamtkostendeckelung
					$this->formElementValues["bemerkungGesamtkostendeckelungAllgemein"] = $contract->GetBemerkungGesamtkostendeckelungAllgemein();
					// Allgemeine Bemerkung Zusammensetzung Gesamtkosten
					$this->formElementValues["bemerkungZusammensetzungGesamtkostenAllgemein"] = $contract->GetBemerkungZusammensetzungGesamtkostenAllgemein();
					
					$this->formElementValues["zurueckBehaltungAusgeschlossen"]=$contract->GetZurueckBehaltungAusgeschlossen();
					$this->formElementValues["zurueckBehaltungAusgeschlossenBeschreibung"]=$contract->GetZurueckBehaltungAusgeschlossenBeschreibung();
					/*$this->formElementValues["nurGrundsteuererhoehungUmlegbar"]=$contract->GetNurGrundsteuererhoehungUmlegbar() ? "on" : "";
					$this->formElementValues["nurGrundsteuererhoehungUmlegbarBeschreibung"]=$contract->GetNurGrundsteuererhoehungUmlegbarBeschreibung();
					$this->formElementValues["nurVersicherungserhoehungUmlegbar"]=$contract->GetNurVersicherungserhoehungUmlegbar() ? "on" : "";
					$this->formElementValues["nurVersicherungserhoehungUmlegbarBeschreibung"]=$contract->GetNurVersicherungserhoehungUmlegbarBeschreibung();
					$this->formElementValues["sonstigeBesonderheiten"]=$contract->GetSonstigeBesonderheiten() ? "on" : "";
					$this->formElementValues["sonstigeBesonderheitenBeschreibung"]=$contract->GetSonstigeBesonderheitenBeschreibung();*/
					
					$this->formElementValues["werbegemeinschaft"]=$contract->GetWerbegemeinschaft() ? "on" : "";
					$this->formElementValues["werbegemeinschaftBeschreibung"]=$contract->GetWerbegemeinschaftBeschreibung();
					
					
					$numEffectedGroups = WorkflowManager::GetProcessStatusGroupCountOfContract($this->db, $contract);
					$currencyAllowedToChange = $contract->IsAttributeAllowedToChange($this->db);
					$otherContracts = Array();
					if ($numEffectedGroups==1) $otherContracts = WorkflowManager::GetContractsInSameProcessStatusGroup($this->db, $contract);
					$showSub1 = (!$currencyAllowedToChange || count($otherContracts)>0) ? true : false;
				}
			}
		}
		// Währung
		global $customerManager, $SHARED_HTTP_ROOT;
		$currencies = $customerManager->GetCurrencies();
		$options = $emptyOptions;
		foreach($currencies as $currency)
		{
			$options[] = Array("name" => $currency->GetName()." (".$currency->GetIso4217().")", "value" => $currency->GetIso4217());
		}
		$this->elements[] = new DropdownElement("currency", "Währung* ".($showSub1 ? "<sup>1)</sup>" : ""), $this->formElementValues["currency"], false, $this->error["currency"], $options, false, null, Array(), !$currencyAllowedToChange);
		$this->elements[] = new TextElement("mietflaeche_qm", "Mietfläche (qm)", $this->formElementValues["mietflaeche_qm"], true, $this->error["mietflaeche_qm"]);
		$this->elements[] = new TextElement("umlageflaeche_qm", "Umlagefläche (qm)", $this->formElementValues["umlageflaeche_qm"], true, $this->error["umlageflaeche_qm"]);
		
		$this->elements[] = new DateElement("mvBeginn", "Mietvertragsbeginn", $this->formElementValues["mvBeginn"], true, $this->error["mvBeginn"]);
		$this->elements[] = new DateElement("mvEndeErstmalsMoeglich", "Erstmals mögliches Mietvertragsende", $this->formElementValues["mvEndeErstmalsMoeglich"], true, $this->error["mvEndeErstmalsMoeglich"]);
		$this->elements[] = new DateElement("mvEnde", "Aktuelles Mietvertragsende", $this->formElementValues["mvEnde"], true, $this->error["mvEnde"]);
		
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => Contract::CONTRACT_YES);
		$options[]=Array("name" => "Nein", "value" => Contract::CONTRACT_NO);
		$this->elements[] = new RadioButtonElement("nurVertragsauszuegeVorhanden", "Nur Vertragsauszüge vorhanden", $this->formElementValues["nurVertragsauszuegeVorhanden"], true, $this->error["nurVertragsauszuegeVorhanden"], $options);
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => Contract::MVC_UNKNOWN);
		$options[]=Array("name" => "Keine neuen Unterlagen hinzugefügt", "value" => Contract::MVC_NO_ADDITIONAL_FILES);
		$options[]=Array("name" => "Neue Unterlagen hinuzgefügt", "value" => Contract::MVC_NEW_FILES_ADDED);
		$options[]=Array("name" => "Neue Unterlagen vorhanden aber nicht hinzugefügt", "value" => Contract::MVC_NEW_FILES_AVAILABLE_NOT_ADDED);
		$this->elements[] = new DropdownElement("mietvertragsdokumenteVollstaendig", "Mietvertragsdokumente vollständig", $this->formElementValues["mietvertragsdokumenteVollstaendig"], false, $this->error["mietvertragsdokumenteVollstaendig"], $options);
        $this->elements[] = new TextAreaElement("comment", "Kommentar", $this->obj->GetCustomerComment(), false, "", true);

        // Eigentümer + Verwalter
		$buttons = Array();
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/address_new.png", "help" => "Neuen Ansprechpartner anlegen", "href" => "javascript:CreateNewAddress(".AM_ADDRESSDATA_TYPE_OWNER.")");
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany(".AM_ADDRESSDATA_TYPE_OWNER.")");
		$this->elements[] = new TextElement("eigentuemer", "Eigentümer", $this->formElementValues["eigentuemer"], false, $this->error["eigentuemer"], false, new SearchTextDynamicContent(  $SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 6, "param01" => AM_ADDRESSDATA_TYPE_NONE), "", "eigentuemer"), $buttons);
		$buttons = Array();
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/address_new.png", "help" => "Neuen Ansprechpartner anlegen", "href" => "javascript:CreateNewAddress(".AM_ADDRESSDATA_TYPE_TRUSTEE.")");
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany(".AM_ADDRESSDATA_TYPE_TRUSTEE.")");
		$this->elements[] = new TextElement("verwalter", "Verwalter", $this->formElementValues["verwalter"], false, $this->error["verwalter"], false, new SearchTextDynamicContent(  $SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 6, "param01" => AM_ADDRESSDATA_TYPE_NONE), "", "verwalter"), $buttons);
		$this->elements[] = new BlankElement();

		// Nicht vereinbarte Abrechnungspositionen
		$this->elements[] = new TextAreaElement("nichtVereinbarteAbrPos", "Bemerkung Nicht vereinbarte Abrechnungspositionen", $this->formElementValues["nichtVereinbarteAbrPos"], false, $this->error["nichtVereinbarteAbrPos"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Wartung, Instandhaltung und Instandsetzung
		$this->elements[] = new TextAreaElement("instandhaltungAllgemein", "Bemerkung Wartung, Instandhaltung und Instandsetzung", $this->formElementValues["instandhaltungAllgemein"], false, $this->error["instandhaltungAllgemein"]);
		$this->elements[] = new CheckboxElement("deckelungInstandhaltung", "Deckelung/Pauschale vereinbart", $this->formElementValues["deckelungInstandhaltung"], false, $this->error["deckelungInstandhaltung"]);
		//$this->elements[] = new TextAreaElement("deckelungInstandhaltungBeschreibung", "Beschreibung Deckelung/Pauschale", $this->formElementValues["deckelungInstandhaltungBeschreibung"], false, $this->error["deckelungInstandhaltungBeschreibung"], $this->formElementValues["deckelungInstandhaltung"]=="on" ? false : true);
		$this->elements[] = new BlankElement();
		// Verwaltung und Management
		$this->elements[] = new TextAreaElement("verwaltungManagementAllgemein", "Bemerkung Verwaltung und Management", $this->formElementValues["verwaltungManagementAllgemein"], false, $this->error["verwaltungManagementAllgemein"]);
		$this->elements[] = new CheckboxElement("deckelungVerwaltungManagement", "Deckelung/Pauschale vereinbart", $this->formElementValues["deckelungVerwaltungManagement"], false, $this->error["deckelungVerwaltungManagement"]);
		//$this->elements[] = new TextAreaElement("deckelungVerwaltungManagementBeschreibung", "Beschreibung Deckelung/Pauschale", $this->formElementValues["deckelungVerwaltungManagementBeschreibung"], false, $this->error["deckelungVerwaltungManagementBeschreibung"], $this->formElementValues["deckelungVerwaltungManagement"]=="on" ? false : true);
		//$this->elements[] = new CheckboxElement("gesetzlicheDefVerwaltungManagement", "Verwaltung und Management auf gesetzliche Definition beschränkt", $this->formElementValues["gesetzlicheDefVerwaltungManagement"], false, $this->error["gesetzlicheDefVerwaltungManagement"]);
		$this->elements[] = new BlankElement();
		//$this->elements[] = new BlankElement();
		// Heizkosten
		$this->elements[] = new TextAreaElement("heizkostenAllgemein", "Bemerkung Heizkosten", $this->formElementValues["heizkostenAllgemein"], false, $this->error["heizkostenAllgemein"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Lüftung und Klimatisierung
		$this->elements[] = new TextAreaElement("lueftungKlimatisierungAllgemein", "Bemerkung Lüftung und Klimatisierung", $this->formElementValues["lueftungKlimatisierungAllgemein"], false, $this->error["lueftungKlimatisierungAllgemein"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Allgemeinstrom
		$this->elements[] = new TextAreaElement("allgemeinstromAllgemein", "Bemerkung Allgemeinstrom", $this->formElementValues["allgemeinstromAllgemein"], false, $this->error["allgemeinstromAllgemein"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Versicherung
		$this->elements[] = new TextAreaElement("versicherungAllgemein", "Bemerkung Versicherung", $this->formElementValues["versicherungAllgemein"], false, $this->error["versicherungAllgemein"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Öffentliche Abgaben
		$this->elements[] = new TextAreaElement("oeffentlicheAbgabenAllgemein", "Bemerkung Öffentliche Abgaben", $this->formElementValues["oeffentlicheAbgabenAllgemein"], false, $this->error["oeffentlicheAbgabenAllgemein"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Entsorgung
		$this->elements[] = new TextAreaElement("entsorgungAllgemein", "Bemerkung Entsorgung", $this->formElementValues["entsorgungAllgemein"], false, $this->error["entsorgungAllgemein"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Reinigung
		$this->elements[] = new TextAreaElement("reinigungPflegeAllgemein", "Bemerkung Reinigung", $this->formElementValues["reinigungPflegeAllgemein"], false, $this->error["reinigungPflegeAllgemein"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Sonstiges
		$this->elements[] = new TextAreaElement("sonstigesAllgemein", "Bemerkung Sonstiges", $this->formElementValues["sonstigesAllgemein"], false, $this->error["sonstigesAllgemein"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Allgemeine Bemerkung Umlageschlüssel
		$this->elements[] = new TextAreaElement("bemerkungUmlageschluesselAllgemein", "Bemerkung Umlageschlüssel", $this->formElementValues["bemerkungUmlageschluesselAllgemein"], false, $this->error["bemerkungUmlageschluesselAllgemein"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Allgemeine Bemerkung Gesamtkostendeckelung
		$this->elements[] = new TextAreaElement("bemerkungGesamtkostendeckelungAllgemein", "Bemerkung Gesamtkostendeckelung", $this->formElementValues["bemerkungGesamtkostendeckelungAllgemein"], false, $this->error["bemerkungGesamtkostendeckelungAllgemein"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Allgemeine Bemerkung Zusammensetzung Gesamtkosten
		$this->elements[] = new TextAreaElement("bemerkungZusammensetzungGesamtkostenAllgemein", "Bemerkung Zusammensetzung Gesamtkosten", $this->formElementValues["bemerkungZusammensetzungGesamtkostenAllgemein"], false, $this->error["bemerkungZusammensetzungGesamtkostenAllgemein"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();		
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => Contract::CONTRACT_YES);
		$options[]=Array("name" => "Nein", "value" => Contract::CONTRACT_NO);
		$this->elements[] = new RadioButtonElement("zurueckBehaltungAusgeschlossen", "Zurückbehaltung ausgeschlossen", $this->formElementValues["zurueckBehaltungAusgeschlossen"], true, $this->error["zurueckBehaltungAusgeschlossen"], $options);
		$this->elements[] = new TextAreaElement("zurueckBehaltungAusgeschlossenBeschreibung", "Beschreibung", $this->formElementValues["zurueckBehaltungAusgeschlossenBeschreibung"], false, $this->error["zurueckBehaltungAusgeschlossenBeschreibung"], $this->formElementValues["zurueckBehaltungAusgeschlossen"]==Contract::CONTRACT_YES ? false : true);
		$this->elements[] = new BlankElement();
		/*$this->elements[] = new CheckboxElement("nurGrundsteuererhoehungUmlegbar", "Nur Grundsteuererhöhung umlegbar", $this->formElementValues["nurGrundsteuererhoehungUmlegbar"], false, $this->error["nurGrundsteuererhoehungUmlegbar"]);
		$this->elements[] = new TextAreaElement("nurGrundsteuererhoehungUmlegbarBeschreibung", "Beschreibung", $this->formElementValues["nurGrundsteuererhoehungUmlegbarBeschreibung"], false, $this->error["nurGrundsteuererhoehungUmlegbarBeschreibung"], $this->formElementValues["nurGrundsteuererhoehungUmlegbar"]=="on" ? false : true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new CheckboxElement("nurVersicherungserhoehungUmlegbar", "Nur Versicherungserhöhung umlegbar", $this->formElementValues["nurVersicherungserhoehungUmlegbar"], false, $this->error["nurVersicherungserhoehungUmlegbar"]);
		$this->elements[] = new TextAreaElement("nurVersicherungserhoehungUmlegbarBeschreibung", "Beschreibung", $this->formElementValues["nurVersicherungserhoehungUmlegbarBeschreibung"], false, $this->error["nurVersicherungserhoehungUmlegbarBeschreibung"], $this->formElementValues["nurVersicherungserhoehungUmlegbar"]=="on" ? false : true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new CheckboxElement("sonstigeBesonderheiten", "Sonstige Besonderheiten", $this->formElementValues["sonstigeBesonderheiten"], false, $this->error["sonstigeBesonderheiten"]);
		$this->elements[] = new TextAreaElement("sonstigeBesonderheitenBeschreibung", "Beschreibung", $this->formElementValues["sonstigeBesonderheitenBeschreibung"], false, $this->error["sonstigeBesonderheitenBeschreibung"], $this->formElementValues["sonstigeBesonderheiten"]=="on" ? false : true);
		$this->elements[] = new BlankElement();*/
		$this->elements[] = new CheckboxElement("werbegemeinschaft", "Werbegemeinschaft", $this->formElementValues["werbegemeinschaft"], false, $this->error["werbegemeinschaft"]);
		$this->elements[] = new TextAreaElement("werbegemeinschaftBeschreibung", "Beschreibung", $this->formElementValues["werbegemeinschaftBeschreibung"], false, $this->error["werbegemeinschaftBeschreibung"], $this->formElementValues["werbegemeinschaft"]=="on" ? false : true);
		$this->elements[] = new BlankElement();
		
		if ($numEffectedGroups>0)
		{
			ob_start();
			?>
			<div style="border-top: solid 1px #cccccc; padding: 5px;">
			<?
			if (!$currencyAllowedToChange)
			{	?>
				<sup>1)</sup> Die Währung kann nicht geändert werden, da dieser Vertrag (oder ein anderer Vertrag aus diesem Paket) in <strong>mehreren</Strong> Pakten verwendet wird.<br /><br />
				<?
			}
			else 
			{	
				if (count($otherContracts)>0)
				{
					?>
					<sup>1)</sup> Dieser Vertrag befindet sich in einem Paket mit mehreren Verträgen. Um die Konsistenz des Paketes zu gewährleisten, werden Änderungen an der Währung auch in die anderen Verträge übernommen.<br />
					<br />
					Folgende Verträge sind davon betroffen: <br />
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

						foreach ($otherContracts as $otherContract) 
						{
							$shop = $otherContract->GetShop();
							$locationTemp = $shop->GetLocation();
							$companyTemp = $locationTemp==null ? null : $locationTemp->GetCompany();
							$groupTemp = $companyTemp==null ? null : $companyTemp->GetGroup();
							?>
							<tr>
								<td class="TAPMatrixRow" align="left" valign="top"><?=($groupTemp==null ? "-" : $groupTemp->GetName());?></td>
								<td class="TAPMatrixRow" align="left" valign="top"><?=($companyTemp==null ? "-" : $companyTemp->GetName());?></td>
								<td class="TAPMatrixRow" align="left" valign="top"><?=($locationTemp==null ? "-" : $locationTemp->GetName());?></td>
								<td class="TAPMatrixRow" align="left" valign="top"><?=$shop->GetName();?></td>
								<td class="TAPMatrixRow" align="left" valign="top"><?=$shop->GetRSID();?></td>
								<td class="TAPMatrixRow" align="left" valign="top">V<?=$otherContract->GetPKey();?></td>
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
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		}
		
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		$abrechnungsjahr=$this->obj->GetAbrechnungsJahr();
		if( $abrechnungsjahr!=null ){
			/**@var $contract Contract*/
			$contract=$abrechnungsjahr->GetContract();
			if( $contract!=null ){
				$currencyAllowedToChange = $contract->IsAttributeAllowedToChange($this->db);
				if ($currencyAllowedToChange)
				{
					if (!$contract->SetCurrency($this->db, $this->formElementValues["currency"])) $this->error["currency"]="Bitte wählen Sie eine Währung aus";
				}
				$mietflaeche=TextElement::GetFloat($this->formElementValues["mietflaeche_qm"]);
				if( $mietflaeche===false ){
					$this->error["mietflaeche_qm"]="Bitte geben Sie die Mietfläche in qm ein";
				}else{
					$contract->SetMietflaecheQM( $mietflaeche );
				}
				$umlageflaeche=TextElement::GetFloat($this->formElementValues["umlageflaeche_qm"]);
				if( $umlageflaeche===false ){
					$this->error["umlageflaeche_qm"]="Bitte geben Sie die Umlagefläche in qm ein";
				}else{
					$contract->SetUmlageflaecheQM( $umlageflaeche );
				}
				// Mietvertragsmanagement
				$dateFrom = $dateUntil = 0;
				if (trim($this->formElementValues["mvBeginn"])!="")
				{
					$tempValue=DateElement::GetTimeStamp($this->formElementValues["mvBeginn"]);
					if ($tempValue===false)
					{
						$this->error["mvBeginn"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
					}
					else 
					{
						$dateFrom = $tempValue;
						$contract->SetMvBeginn($tempValue);
					}
				}
				else
				{
					$contract->SetMvBeginn(0);
				}
				if (trim($this->formElementValues["mvEndeErstmalsMoeglich"])!="")
				{
					$tempValue=DateElement::GetTimeStamp($this->formElementValues["mvEndeErstmalsMoeglich"]);
					if ($tempValue===false) $this->error["mvEndeErstmalsMoeglich"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
					else $contract->SetMvEndeErstmalsMoeglich($tempValue);
				}
				else
				{
					$contract->SetMvEndeErstmalsMoeglich(0);
				}
				if (trim($this->formElementValues["mvEnde"])!="")
				{
					$tempValue=DateElement::GetTimeStamp($this->formElementValues["mvEnde"]);
					if ($tempValue===false)
					{
						$this->error["mvEnde"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
					}
					else 
					{
						$dateUntil = $tempValue;
						$contract->SetMvEnde($tempValue);
					}
				}
				else
				{
					$contract->SetMvEnde(0);
				}
				// Check life of lease collision
				if ($dateFrom!=0 && $dateUntil!=0)
				{
					$collsionContract = null;
					if ($contract->CheckLifeOfLeaseCollison($this->db, $dateFrom, $dateUntil, $collsionContract)==true)
					{
						$this->error["mvBeginn"]="Für den angegebenen Zeitraum existiert bereits ein anderer Vertrag (Vertrag ".$collsionContract->GetLifeOfLeaseString().") für diesen Laden.";
					}
				}	
				
				if ((int)$this->formElementValues["nurVertragsauszuegeVorhanden"]>0)
				{
					$contract->SetNurAuszuegeVorhanden((int)$this->formElementValues["nurVertragsauszuegeVorhanden"]);
				}
				$contract->SetMietvertragsdokumenteVollstaendig((int)$this->formElementValues["mietvertragsdokumenteVollstaendig"]);

				if (trim($this->formElementValues["eigentuemer"])!="")
				{
					$tempAddressData = AddressManager::GetAddessById($this->db, $this->formElementValues["eigentuemer"]);
					if ($tempAddressData===null)
					{
						$this->error["eigentuemer"] = "Der eingegebene Eigentümer konnte nicht gefunden werden";
					}
					else
					{
						$contract->SetEigentuemer($tempAddressData);
						$this->formElementValues["eigentuemer"] = $tempAddressData->GetAddressIDString();
					}
				}
				else
				{
					$contract->SetEigentuemer(null);
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
						$contract->SetVerwalter($tempAddressData);
						$this->formElementValues["verwalter"] = $tempAddressData->GetAddressIDString();
					}
				}
				else
				{
					$contract->SetVerwalter(null);
				}

				// Nicht vereinbarte Abrechnungspositionen
				$contract->SetBemerkungNichtVereinbarteAbrPos($this->formElementValues["nichtVereinbarteAbrPos"] );
				// Wartung, Instandhaltung und Instandsetzung
				$contract->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_WARTUNG_INSTANDHALTUNG_INSTANDSETZUNG, $this->formElementValues["instandhaltungAllgemein"] );
				$contract->SetDeckelungInstandhaltung( $this->formElementValues["deckelungInstandhaltung"]=="on" ? true : false );
				//$contract->SetDeckelungInstandhaltungBeschreibung( $this->formElementValues["deckelungInstandhaltungBeschreibung"] );
				// Verwaltung und Management
				$contract->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_VERWALTUNG_UND_MANAGEMENT, $this->formElementValues["verwaltungManagementAllgemein"] );
				$contract->SetDeckelungVerwaltungManagement( $this->formElementValues["deckelungVerwaltungManagement"]=="on" ? true : false );
				//$contract->SetDeckelungVerwaltungManagementBeschreibung( $this->formElementValues["deckelungVerwaltungManagementBeschreibung"] );
				//$contract->SetGesetzlicheDefinitionVerwaltungManagement( $this->formElementValues["gesetzlicheDefVerwaltungManagement"]=="on" ? true : false );
				// Heizkosten
				$contract->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_HEIZKOSTEN, $this->formElementValues["heizkostenAllgemein"]);
				// Lüftung und Klimatisierung
				$contract->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_LUEFTUNG_UND_KLIMATISIERUNG, $this->formElementValues["lueftungKlimatisierungAllgemein"]);
				// Allgemeinstrom
				$contract->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_ALLGEMEINSTROM, $this->formElementValues["allgemeinstromAllgemein"]);
				// Versicherung
				$contract->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_VERSICHERUNGEN, $this->formElementValues["versicherungAllgemein"]);
				// Öffentliche Abgaben
				$contract->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_OEFFENTLICHE_ABGABEN, $this->formElementValues["oeffentlicheAbgabenAllgemein"]);
				// Entsorgung
				$contract->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_ENTSORGUNG, $this->formElementValues["entsorgungAllgemein"]);
				// Reinigung
				$contract->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_REINIGUNG_UND_PFLEGE, $this->formElementValues["reinigungPflegeAllgemein"]);
				// Sonstiges
				$contract->SetRSKostenartAllgemein(RSKostenartManager::RS_KOSTENART_SONSTIGES, $this->formElementValues["sonstigesAllgemein"]);
				// Allgemeine Bemerkung Umlageschlüssel
				$contract->SetBemerkungUmlageschluesselAllgemein($this->formElementValues["bemerkungUmlageschluesselAllgemein"]);
				// Allgemeine Bemerkung Gesamtkostendeckelung
				$contract->SetBemerkungGesamtkostendeckelungAllgemein($this->formElementValues["bemerkungGesamtkostendeckelungAllgemein"]);
				// Allgemeine Bemerkung Zusammensetzung Gesamtkosten
				$contract->SetBemerkungZusammensetzungGesamtkostenAllgemein($this->formElementValues["bemerkungZusammensetzungGesamtkostenAllgemein"]);
		
				if ((int)$this->formElementValues["zurueckBehaltungAusgeschlossen"]>0)
				{
					$contract->SetZurueckBehaltungAusgeschlossen((int)$this->formElementValues["zurueckBehaltungAusgeschlossen"]);
				}
				$contract->SetZurueckBehaltungAusgeschlossenBeschreibung( $this->formElementValues["zurueckBehaltungAusgeschlossenBeschreibung"] );
				/*$contract->SetNurGrundsteuererhoehungUmlegbar( $this->formElementValues["nurGrundsteuererhoehungUmlegbar"]=="on" ? true : false );
				$contract->SetNurGrundsteuererhoehungUmlegbarBeschreibung( $this->formElementValues["nurGrundsteuererhoehungUmlegbarBeschreibung"] );
				$contract->SetNurVersicherungserhoehungUmlegbar( $this->formElementValues["nurVersicherungserhoehungUmlegbar"]=="on" ? true : false );
				$contract->SetNurVersicherungserhoehungUmlegbarBeschreibung( $this->formElementValues["nurVersicherungserhoehungUmlegbarBeschreibung"] );
				$contract->SetSonstigeBesonderheiten( $this->formElementValues["sonstigeBesonderheiten"]=="on" ? true : false );
				$contract->SetSonstigeBesonderheitenBeschreibung( $this->formElementValues["sonstigeBesonderheitenBeschreibung"] );*/
				$contract->SetWerbegemeinschaft( $this->formElementValues["werbegemeinschaft"]=="on" ? true : false );
				$contract->SetWerbegemeinschaftBeschreibung( $this->formElementValues["werbegemeinschaftBeschreibung"] );
				if( count($this->error)==0 ){
					$returnValue=$contract->Store($this->db);
					if( $returnValue!==true ){
						$this->error["misc"][]="Systemfehler (".$returnValue.")";
						return false;
					}
					// Sind alle Werte gesetzt, um in den nächsten Status zu springen?
					if( $gotoNextStatus ){
						if ($contract->GetZurueckBehaltungAusgeschlossen()==Contract::CONTRACT_UNKNOWN) $this->error["zurueckBehaltungAusgeschlossen"]="Um die Aufgabe abschließen zu können, müssen Sie eine Auswahl treffen";
						if ($contract->GetMietflaecheQM()<=0.0) $this->error["mietflaeche_qm"]="Um die Aufgabe abschließen zu können, müssen Sie die Mietfläche eingeben";
						if ($contract->GetUmlageflaecheQM()<=0.0) $this->error["umlageflaeche_qm"]="Um die Aufgabe abschließen zu können, müssen Sie die Umlagefläche eingeben";
						if ($contract->GetNurAuszuegeVorhanden()<=0) $this->error["nurVertragsauszuegeVorhanden"]="Bitte geben Sie an, ob nur Auszüge vom Vertrag vorhanden sind (Ja) oder ob der gesamte Vertrag vorhanden ist (Nein)";
						if ($contract->GetMvBeginn()==0) $this->error["mvBeginn"]="Um die Aufgabe abschließen zu können, müssen Sie ein Datum eingeben";
						if ($contract->GetMvEndeErstmalsMoeglich()==0) $this->error["mvEndeErstmalsMoeglich"]="Um die Aufgabe abschließen zu können, müssen Sie ein Datum eingeben";
						if ($contract->GetMvEnde()==0) $this->error["mvEnde"]="Um die Aufgabe abschließen zu können, müssen Sie ein Datum eingeben";
						if (count($this->error)>0) return false;
						// Vertrag als erfasst markieren
						$contract->SetVertragErfasst(true);
						$contract->Store($this->db);
					}
					return true;
				}
			}
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
		?>
		<script type="text/javascript">
			<!--
				//$('deckelungInstandhaltung').onclick= function(){ $('deckelungInstandhaltungBeschreibung').disabled = !$('deckelungInstandhaltung').checked; };
				//$('deckelungVerwaltungManagement').onclick= function(){ $('deckelungVerwaltungManagementBeschreibung').disabled = !$('deckelungVerwaltungManagement').checked; };
				var zurueckBehaltungAusgeschlossenRadioButtons = $$('input[name=zurueckBehaltungAusgeschlossen]');
				for (var a=0; a<zurueckBehaltungAusgeschlossenRadioButtons.length; a++)
				{
					zurueckBehaltungAusgeschlossenRadioButtons[a].onclick = function(){$('zurueckBehaltungAusgeschlossenBeschreibung').disabled = !($$('input[name=zurueckBehaltungAusgeschlossen]:checked').get('value')==<?=Contract::CONTRACT_YES;?>); };
				}
				//$('nurGrundsteuererhoehungUmlegbar').onclick= function(){ $('nurGrundsteuererhoehungUmlegbarBeschreibung').disabled = !$('nurGrundsteuererhoehungUmlegbar').checked; };
				//$('nurVersicherungserhoehungUmlegbar').onclick= function(){ $('nurVersicherungserhoehungUmlegbarBeschreibung').disabled = !$('nurVersicherungserhoehungUmlegbar').checked; };
				//$('sonstigeBesonderheiten').onclick= function(){ $('sonstigeBesonderheitenBeschreibung').disabled = !$('sonstigeBesonderheiten').checked; };
				$('werbegemeinschaft').onclick= function(){ $('werbegemeinschaftBeschreibung').disabled = !$('werbegemeinschaft').checked; };
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