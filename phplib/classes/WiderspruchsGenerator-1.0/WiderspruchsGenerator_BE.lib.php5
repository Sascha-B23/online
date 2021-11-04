<?php
/**
 * Widerspruchsgenerator (Belgien)
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class WiderspruchsGenerator_BE extends WiderspruchsGenerator
{
	/**
	 * Textbausteine (pkeys)
	 */
	const WG_WIDERSPRUCHSPUNKT_HEIZKOSTEN = 96;									// Heizkosten										OK
	const WG_WIDERSPRUCHSPUNKT_ENTSORGUNG = 94;									// Entsorgung										OK
	const WG_WIDERSPRUCHSPUNKT_REINIGUNG_UND_PFLEGE = 108;						// Reinigung und Pflege								OK
	const WG_WIDERSPRUCHSPUNKT_ALLGEMEINSTROM = 86;								// Allgemeinstrom									OK
	const WG_WIDERSPRUCHSPUNKT_WARTUNG_INSTANDHALTUNG_INSTANDSETZUNG = 122;		// Wartung, Instandhaltung und Instandsetzung		OK
	const WG_WIDERSPRUCHSPUNKT_AUSZAHLUNG_GUTSCHRIFT = 88;						// Auszahlung Gutschrift							OK
	const WG_WIDERSPRUCHSPUNKT_REDUZIERUNG_VORAUSZAHLUNG = 102;					// Reduzierung Vorauszahlung						OK
	const WG_WIDERSPRUCHSPUNKT_OEFFENTLICHE_ABGABEN = 106;						// Öffentliche Abgaben								OK
	const WG_WIDERSPRUCHSPUNKT_VERSICHERUNGEN = 114;							// Versicherung										OK
	const WG_WIDERSPRUCHSPUNKT_VERWALTUNG_UND_MANAGEMENT = 116;					// Verwaltung und Management						OK
	const WG_WIDERSPRUCHSPUNKT_UMLAGEFLAECHE = 112;								// Umlagefläche										OK
	const WG_WIDERSPRUCHSPUNKT_BERECHNUNGSFEHLER = 90;							// Berechnungsfehler								OK
	const WG_WIDERSPRUCHSPUNKT_VORAUSZAHLUNG = 118;								// Vorauszahlung									OK
	const WG_WIDERSPRUCHSPUNKT_NICHT_VEREINBARTE_ABRECHNUNGSPOSITIONEN = 104;	// Nicht vereinbarte Abrechnungspositionen			OK
	const WG_WIDERSPRUCHSPUNKT_LUEFTUNG_UND_KLIMATISIERUNG = 100;				// Lüftung und Klimatisierung						OK
	const WG_WIDERSPRUCHSPUNKT_SONSTIGES = 110;									// Sonstige											OK
	const WG_WIDERSPRUCHSPUNKT_KOSTENSTEIGERUNG = 98;							// Kostensteigerung									OK
	const WG_WIDERSPRUCHSPUNKT_ZUSAMMENSETZUNG_GU = 124;						// Zusammensetzung Gesamteinheiten Umlageschlüssel	OK
	const WG_WIDERSPRUCHSPUNKT_ZUSAMMENSETZUNG_GESAMTKOSTEN = 126;				// Zusammensetzung Gesamtkosten						OK
	const WG_WIDERSPRUCHSPUNKT_ABGRENZUNG = 84;									// Abgrenzungen										OK
	const WG_WIDERSPRUCHSPUNKT_BERECHNUNGSFEHLER_ABRECHNUNGSERGEBNIS = 92;		// Berechnungsfehler Abrechnungsergebnis			OK
	const WG_WIDERSPRUCHSPUNKT_GESAMTKOSTENDECKELUNG = 120;						// Gesamtkostendeckelung							OK
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function WiderspruchsGenerator_BE(DBManager $db)
	{
		parent::__construct($db);
		// init mapping table
		$this->FMS_KOSTENART_WS_WIDERSPRUCHSPUNKT = Array(	RSKostenartManager::RS_KOSTENART_WARTUNG_INSTANDHALTUNG_INSTANDSETZUNG => self::WG_WIDERSPRUCHSPUNKT_WARTUNG_INSTANDHALTUNG_INSTANDSETZUNG,
															RSKostenartManager::RS_KOSTENART_VERWALTUNG_UND_MANAGEMENT => self::WG_WIDERSPRUCHSPUNKT_VERWALTUNG_UND_MANAGEMENT,
															RSKostenartManager::RS_KOSTENART_HEIZKOSTEN => self::WG_WIDERSPRUCHSPUNKT_HEIZKOSTEN,
															RSKostenartManager::RS_KOSTENART_LUEFTUNG_UND_KLIMATISIERUNG => self::WG_WIDERSPRUCHSPUNKT_LUEFTUNG_UND_KLIMATISIERUNG,
															RSKostenartManager::RS_KOSTENART_ALLGEMEINSTROM => self::WG_WIDERSPRUCHSPUNKT_ALLGEMEINSTROM,
															RSKostenartManager::RS_KOSTENART_VERSICHERUNGEN => self::WG_WIDERSPRUCHSPUNKT_VERSICHERUNGEN,
															RSKostenartManager::RS_KOSTENART_OEFFENTLICHE_ABGABEN => self::WG_WIDERSPRUCHSPUNKT_OEFFENTLICHE_ABGABEN,
															RSKostenartManager::RS_KOSTENART_ENTSORGUNG => self::WG_WIDERSPRUCHSPUNKT_ENTSORGUNG,
															RSKostenartManager::RS_KOSTENART_REINIGUNG_UND_PFLEGE => self::WG_WIDERSPRUCHSPUNKT_REINIGUNG_UND_PFLEGE,
															RSKostenartManager::RS_KOSTENART_SONSTIGES => self::WG_WIDERSPRUCHSPUNKT_SONSTIGES
														);
		$this->useSollwerteOfCountry = "BE";
	}
	
	/**
	 * Generate Widerspruch
	 * @param Teilabrechnung[] $tas
	 * @param Array $tapList
	 * @return boolean
	 */
	public function Create($tas, $tapList)
	{
		$tapListComplete = $tapList;
		
		// Variable für die Summe der Kürzungen initialisieren 
		$summeKuerzungen=0.0;
		// STUFE I
		$summeKuerzungen+=$this->PruefeNichtVereinbarteAbrechnungspositionen($tapList);
		// STUFE II (ohne Berücksichtigung der TAPs die bereits in Stufe I betroffen sind)
		$tapList=$this->CleanTAPList($tapList); // Alle in Stufe I betroffenen TAPs entfernen
		// 1. Wartung, Instandhaltung und Instandsetzung
		$summeKuerzungen+=$this->PruefeRSKostenart($tapList, $tas, RSKostenartManager::RS_KOSTENART_WARTUNG_INSTANDHALTUNG_INSTANDSETZUNG);
		// 2. Verwaltung und Management
		$summeKuerzungen+=$this->PruefeRSKostenart($tapList, $tas, RSKostenartManager::RS_KOSTENART_VERWALTUNG_UND_MANAGEMENT);
		// 3. Heizkosten
		$summeKuerzungen+=$this->PruefeRSKostenart($tapList, $tas, RSKostenartManager::RS_KOSTENART_HEIZKOSTEN);
		// 4. Lüftung und Klimatisierung
		$summeKuerzungen+=$this->PruefeRSKostenart($tapList, $tas, RSKostenartManager::RS_KOSTENART_LUEFTUNG_UND_KLIMATISIERUNG);
		// 5. Allgemeinstrom
		$summeKuerzungen+=$this->PruefeRSKostenart($tapList, $tas, RSKostenartManager::RS_KOSTENART_ALLGEMEINSTROM);
		// 6. Versicherung
		$summeKuerzungen+=$this->PruefeRSKostenart($tapList, $tas, RSKostenartManager::RS_KOSTENART_VERSICHERUNGEN);
		// 7. Öffentliche Abgaben
		$summeKuerzungen+=$this->PruefeRSKostenart($tapList, $tas, RSKostenartManager::RS_KOSTENART_OEFFENTLICHE_ABGABEN);
		// 8. Entsorgung
		$summeKuerzungen+=$this->PruefeRSKostenart($tapList, $tas, RSKostenartManager::RS_KOSTENART_ENTSORGUNG);
		// 9. Reinigung und Pflege
		$summeKuerzungen+=$this->PruefeRSKostenart($tapList, $tas, RSKostenartManager::RS_KOSTENART_REINIGUNG_UND_PFLEGE);
		// 10. Sonstiges
		$summeKuerzungen+=$this->PruefeRSKostenart($tapList, $tas, RSKostenartManager::RS_KOSTENART_SONSTIGES);
		// STUFE III (ohne Berücksichtigung der TAPs die bereits in Stufe I + II betroffen sind)
		$tapList=$this->CleanTAPList($tapList); // Alle in Stufe II betroffenen TAPs entfernen
		$summeKuerzungen+=$this->PruefeKostensteigerung($tapList);
		// STUFE IV (Stufenübergreifend)
		// Zusammensetzung Gesamteinheiten Umlageschlüssel
		$summeKuerzungen+=$this->PruefeZusammensetzungGesamteinheitenUmlageschluessel($tapListComplete);
		// Umlagefläche
		$summeKuerzungen+=$this->PruefeUmlageflaeche($tas, $tapListComplete);
		// Zusammensetzung Gesamtkosten
		$summeKuerzungen+=$this->CreateWP(self::WG_WIDERSPRUCHSPUNKT_ZUSAMMENSETZUNG_GESAMTKOSTEN, Array(Array("name" => "[COMMENT]", "value" => $this->contract->GetBemerkungZusammensetzungGesamtkostenAllgemein())));
		// Abgrenzungen
		$summeKuerzungen+=$this->CreateWP(self::WG_WIDERSPRUCHSPUNKT_ABGRENZUNG, Array());
		// Vorauszahlung
		$summeKuerzungen+=$this->PruefeVorauszahlung($tas);
		// Berechnungsfehler
		$summeKuerzungen+=$this->PruefeBerechnungsfehler($tapListComplete);
		// Berechnungsfehler Abrechnungsergebnis
		for ($a=0; $a<count($tas); $a++)
		{
			$summeKostenanteilKundeLautTaNetto = $tas[$a]->GetAbrechnungsergebnisLautAbrechnung();
			$abrechnungsergebnisNettoLautTaNetto = $tas[$a]->GetVorauszahlungLautAbrechnung();
			$summeKostenateilKundeNetto = $tas[$a]->GetSummeBetragKunde($this->db);
			$abrechnungsergebnisNetto = $summeKostenateilKundeNetto-$tas[$a]->GetNachzahlungGutschrift();
			
			if ($summeKostenanteilKundeLautTaNetto-$summeKostenateilKundeNetto>1.0 || $abrechnungsergebnisNettoLautTaNetto-$abrechnungsergebnisNetto>1.0)
			{
				$replace = Array( 
									0 => Array("name" => "[SUMME_BETRAG_TAPS_TA]", "value" => HelperLib::ConvertFloatToLocalizedString($summeKostenanteilKundeLautTaNetto) ),
									1 => Array("name" => "[SUMME_BETRAG_TAPS_TA_BRUTTO]", "value" => HelperLib::ConvertFloatToLocalizedString($summeKostenanteilKundeLautTaNetto*HelperLib::GetMwst((int)$this->abrechnungsJahr->GetJahr())) ),
									2 => Array("name" => "[ABRECHNUNGSERGEBNIS_TA]", "value" => HelperLib::ConvertFloatToLocalizedString($abrechnungsergebnisNettoLautTaNetto) ),
									3 => Array("name" => "[ABRECHNUNGSERGEBNIS_TA_BRUTTO]", "value" => HelperLib::ConvertFloatToLocalizedString($abrechnungsergebnisNettoLautTaNetto*HelperLib::GetMwst((int)$this->abrechnungsJahr->GetJahr())) ),
									4 => Array("name" => "[SUMME_BETRAG_TAPS]", "value" => HelperLib::ConvertFloatToLocalizedString($summeKostenateilKundeNetto) ),
									5 => Array("name" => "[SUMME_BETRAG_TAPS_BRUTTO]", "value" => HelperLib::ConvertFloatToLocalizedString($summeKostenateilKundeNetto*HelperLib::GetMwst((int)$this->abrechnungsJahr->GetJahr())) ),
									6 => Array("name" => "[ABRECHNUNGSERGEBNIS]", "value" => HelperLib::ConvertFloatToLocalizedString($abrechnungsergebnisNetto) ),
									7 => Array("name" => "[ABRECHNUNGSERGEBNIS_BRUTTO]", "value" => HelperLib::ConvertFloatToLocalizedString($abrechnungsergebnisNetto*HelperLib::GetMwst((int)$this->abrechnungsJahr->GetJahr())) ),
								);
				$this->CreateWP(self::WG_WIDERSPRUCHSPUNKT_BERECHNUNGSFEHLER_ABRECHNUNGSERGEBNIS, $replace);
			}
		}
		
		// Gesamtkostendeckelung
		$summeKuerzungen+=$this->PruefeGesamtkostendeckelung($tas);
		// Auszahlung Gutschrift / Anpassung Vorauszahlung
		$nachzahlungGutschrift = 0.0;
		for( $a=0; $a<count($tas); $a++ )
		{
			$nachzahlungGutschrift += $tas[$a]->GetNachzahlungGutschrift();
		}
		if ($nachzahlungGutschrift<0.0)
		{
			// Auszahlung Gutschrift
			$replace = Array( 0 => Array("name" => "[GUTSCHRIFT]", "value" => HelperLib::ConvertFloatToLocalizedString(-$nachzahlungGutschrift) )	);
			$this->CreateWP(self::WG_WIDERSPRUCHSPUNKT_AUSZAHLUNG_GUTSCHRIFT, $replace);
		}
		else
		{
			// Anpassung Vorauszahlung
			$this->CreateWP(self::WG_WIDERSPRUCHSPUNKT_REDUZIERUNG_VORAUSZAHLUNG, Array());
		}
		return true;
	}
	
	/**
	 * Sucht nach nicht vereinbarten Abrechnungspositionen
	 * @param array	$taps		Zu überprüfende Teilabrechnungspositionen
	 * @return float				Summe des Kürzungsbetrag dieser Prüfung
	 */
	protected function PruefeNichtVereinbarteAbrechnungspositionen(&$taps)
	{
		$kuerzungsbetragSumme=0.0;
		$list=Array();
		// Alle TAPs durchlaufen
		for ($a=0; $a<count($taps); $a++)
		{
			// Ist TAP nicht umlagefähig?
			if ($taps[$a]["tap"]->GetUmlagefaehig()==2)
			{
				// Benötigte Daten aus TAP holen
				$kuerzungsbetragSumme+=$taps[$a]["betragKunde"];
				$list[] = $taps[$a];
				// TAP in zukünftigen Berechnungen nicht mehr miteinbeziehen
				$taps[$a]["use"]=false;
			}
		}
		// Wurden nicht umlagefähige TAPs gefunden?
		if (count($list)>0)
		{
			// Widerspruchspunkt erzeugen
			$replace = Array(	0 => Array("name" => "[TAP_LIST]", "value" => $list ),
								1 => Array("name" => "[COMMENT]", "value" => str_replace("\n", "<br />", $this->contract->GetBemerkungNichtVereinbarteAbrPos()))
							);
			$this->CreateWP(self::WG_WIDERSPRUCHSPUNKT_NICHT_VEREINBARTE_ABRECHNUNGSPOSITIONEN, $replace, $summe);
		}
		return $kuerzungsbetragSumme;
	}
	
	/**
	 * Zusammensetzung Gesamteinheiten Umlageschlüssel
	 * @global type $NKM_TEILABRECHNUNGSPOSITION_EINHEIT
	 * @param type $tapListComplete		Liste aller Teilabrechnungspositionen
	 * @return float	Summe des Kürzungsbetrag dieser Prüfung
	 */
	protected function PruefeZusammensetzungGesamteinheitenUmlageschluessel($tapListComplete)
	{
		// 1. Schritt: zu jedem Umlageschlüssel ein Beispiel holen
		$unitMap = Array();
		$unitList = Array();
		$checkPreviousYear = false;
		for ($a=0; $a<count($tapListComplete); $a++)
		{
            // ignore TAP if pauschale
            if ($tapListComplete[$a]["tap"]->IsPauschale()) continue;

			$unit = $tapListComplete[$a]["tap"]->GetGesamteinheitenEinheit()."_".$tapListComplete[$a]["tap"]->GetGesamteinheiten();
			if (isset($unitMap[$unit])) continue;
			$unitMap[$unit] = true;
			$name = $tapListComplete[$a]["tap"]->GetBezeichnungKostenart();
			if (!isset($unitList[$name])) $unitList[$name] = Array();
			$tempData = Array();
			$tempData['value'] = $tapListComplete[$a]["tap"]->GetGesamteinheiten();
			$tempData['unit'] = $tapListComplete[$a]["tap"]->GetGesamteinheitenEinheit();
			$unitList[$name][] = $tempData;
		}
		// 2. Schritt: Vorjahresprüfung durchführen und alls Umlageschlüssel holen die sich um mehr als 10% verändert haben
		$checkPreviousYear = false;
		for ($a=0; $a<count($tapListComplete); $a++)
		{
			if (!isset($tapListComplete[$a]["vorjahr"])) continue;
            // ignore TAP if pauschale
            if ($tapListComplete[$a]["tap"]->IsPauschale()) continue;

			$tempData = Array();
			$tempData['value'] = $tapListComplete[$a]["tap"]->GetGesamteinheiten();
			$tempData['unit'] = $tapListComplete[$a]["tap"]->GetGesamteinheitenEinheit();
			$tempData['previousYear'] = Array();
			foreach ($tapListComplete[$a]["vorjahr"] as $previousYear)
			{
                // ignore TAP if pauschale
                if ($previousYear->IsPauschale()) continue;
				if ($tempData['unit']==$previousYear->GetGesamteinheitenEinheit() && (abs((($previousYear->GetGesamteinheiten()*100.0)/$tempData['value'])-100.0)<10.0)) continue;
				// Unterschiedliche Einheit und/oder Änderung von >=10% ...
				$tempData2 = Array();
				$tempData2['value'] = $previousYear->GetGesamteinheiten();
				$tempData2['unit'] = $previousYear->GetGesamteinheitenEinheit();
				// check if this value+unit combination is allready in array...
				$found = false;
				foreach ($tempData['previousYear'] as $valueTemp)
				{
					if ($valueTemp['value']==$tempData2['value'] && $valueTemp['unit']==$tempData2['unit'])
					{
						///...combination allready in array
						$found = true;
						break;
					}
				}
				// only add to array if not allready in array
				if (!$found) $tempData['previousYear'][] = $tempData2;
			}
			if (count($tempData['previousYear'])==0) continue;
			
			$name = $tapListComplete[$a]["tap"]->GetBezeichnungKostenart();
			if (!isset($unitList[$name])) $unitList[$name] = Array();
			$checkPreviousYear = true;
			// check if this value+unit+previous year combination is allready in array...
			$found = false;
			foreach ($unitList[$name] as $valueTemp)
			{
				if ($valueTemp['value']==$tempData['value'] && $valueTemp['unit']==$tempData['unit'])
				{
					if (count($valueTemp['previousYear'])==count($tempData['previousYear']))
					{
						/// previous year the same?
						$found = true;
						for($b=0; $b<count($valueTemp['previousYear']); $b++)
						{
							if ($valueTemp['previousYear'][$b]['value']!=$tempData['previousYear'][$b]['value'] || $valueTemp['previousYear'][$b]['unit']!=$tempData['previousYear'][$b]['unit'])
							{
								// previews year is differnet
								$found = false;
								break;
							}
						}
						
					}
				}
			}
			// add to list
			if (!$found) $unitList[$name][] = $tempData;
		}
		//echo $this->BuildUnitTapList($unitList, $checkPreviousYear);
		//echo "<pre>".print_r($unitList, true)."</pre>";
		//exit;
		
		// Gruppen nach Verrechnungseinheiten bilden und größten Wert ziehen...
		$verrechnungseinheiten = Array();
		for ($a=0; $a<count($tapListComplete); $a++)
		{
            // ignore TAP if pauschale
            if ($tapListComplete[$a]["tap"]->IsPauschale()) continue;

			if (!isset($verrechnungseinheiten[$tapListComplete[$a]["tap"]->GetGesamteinheitenEinheit()])) $verrechnungseinheiten[$tapListComplete[$a]["tap"]->GetGesamteinheitenEinheit()] = 0.0;
			if ($verrechnungseinheiten[$tapListComplete[$a]["tap"]->GetGesamteinheitenEinheit()]<$tapListComplete[$a]["tap"]->GetGesamteinheiten())
			{
				$verrechnungseinheiten[$tapListComplete[$a]["tap"]->GetGesamteinheitenEinheit()] = $tapListComplete[$a]["tap"]->GetGesamteinheiten();
			}
		}
		// Für jede Gruppe mit einem Wert >0.0 einen Text erzeugen...
		$umlageschluesselText = "";
		global $NKM_TEILABRECHNUNGSPOSITION_EINHEIT;
		$keys=array_keys($NKM_TEILABRECHNUNGSPOSITION_EINHEIT);
		for($a=0; $a<count($keys); $a++)
		{
			if ($keys[$a]==NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE || $keys[$a]==NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KUBIKMETER || $keys[$a]==NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KWH) continue;
			if (!isset($verrechnungseinheiten[$keys[$a]]) || $verrechnungseinheiten[$keys[$a]]<=0.0 ) continue;
			if ($umlageschluesselText!="") $umlageschluesselText.=" bzw. ";
			$umlageschluesselText.=HelperLib::ConvertFloatToLocalizedString($verrechnungseinheiten[$keys[$a]])." ".$NKM_TEILABRECHNUNGSPOSITION_EINHEIT[$keys[$a]]["short"];
		}
		if ($umlageschluesselText=="") $umlageschluesselText="-";
		$replace = Array(	0 => Array("name" => "[COMMENT]", "value" => $this->contract->GetBemerkungUmlageschluesselAllgemein() ),
							2 => Array("name" => "[TAP_UNIT_LIST]", "value" => $this->BuildUnitTapList($unitList, $checkPreviousYear) ),
							1 => Array("name" => "[UMLAGESCHLUESSEL]", "value" => $umlageschluesselText) 
						);
		$bedingungen = Array(0);
		if ($this->CheckUnitDifferAll($unitList)) $bedingungen[] = 1;
		$this->CreateWP(self::WG_WIDERSPRUCHSPUNKT_ZUSAMMENSETZUNG_GU, $replace, 0.0, $bedingungen);
		return 0.0;
	}
	
	/**
	 * Umlagefläche
	 * @param array $tas
	 * @param array $tapListComplete 
	 * @return float	Summe des Kürzungsbetrag dieser Prüfung
	 */
	protected function PruefeUmlageflaeche($tas, $tapListComplete)
	{
		$summeKuerzungen = 0.0;
		for( $a=0; $a<count($tas); $a++ ){
			if( $tas[$a]->GetUmlageflaecheQM() - $this->contract->GetUmlageflaecheQM() > 5.0 )	// TODO: Prüfen ob korrekt
			{
				$kuerzungsBetragTemp = 0.0;
				$summeBetragTAPsKunde = 0.0;
				for ($b=0; $b<count($tapListComplete); $b++)
				{
					if ($tapListComplete[$b]["ta"]->GetPKey()!=$tas[$a]->GetPKey()) continue;
                    // ignore TAP if pauschale
                    if ($tapListComplete[$a]["tap"]->IsPauschale()) continue;
					// Nur Flächenbasierte TAPs in die Rechnung miteinbeziehen...
					switch ($tapListComplete[$b]["tap"]->GetEinheitKundeEinheit())
					{
						case NKM_TEILABRECHNUNGSPOSITION_EINHEIT_QUATRATMETER:
						case NKM_TEILABRECHNUNGSPOSITION_EINHEIT_GEWICHTETEQUATRATMETER:
						case NKM_TEILABRECHNUNGSPOSITION_EINHEIT_QUATRATMETERTAGE:
							$summeBetragTAPsKunde += $tapListComplete[$b]["tap"]->GetBetragKunde();
							break;
					}
				}
				$kuerzungsBetragTemp = $tas[$a]->GetUmlageflaecheQM()>0.0 ? ($summeBetragTAPsKunde-$summeBetragTAPsKunde/$tas[$a]->GetUmlageflaecheQM()*$this->contract->GetUmlageflaecheQM()) : $summeBetragTAPsKunde;
				$replace = Array(	0 => Array("name" => "[UMLAGEFLAECHE_MV]", "value" => HelperLib::ConvertFloatToLocalizedString( $this->contract->GetUmlageflaecheQM()) ),
									1 => Array("name" => "[UMLAGEFLAECHE_TA]", "value" => HelperLib::ConvertFloatToLocalizedString( $tas[$a]->GetUmlageflaecheQM()) )
								);
				$this->CreateWP(self::WG_WIDERSPRUCHSPUNKT_UMLAGEFLAECHE, $replace, $kuerzungsBetragTemp);
				$summeKuerzungen+=$kuerzungsBetragTemp;
			}
		}
		return $summeKuerzungen;
	}
	
	/**
	 * Prüft die Vorauszahlung der TAs
	 * @param array	$tas	Zu überprüfende Teilabrechnungen
	 * @return float	Summe des Kürzungsbetrag dieser Prüfung
	 */
	protected function PruefeVorauszahlung(&$tas){
		$summe=0.0;
		$summeVZlautAbrechnung=0.0;
		$summeVZlautKunde=0.0;
		// Alle TAs durchlaufen und Werte Addieren...
		for( $a=0; $a<count($tas); $a++){
			$summeVZlautAbrechnung += $tas[$a]->GetVorauszahlungLautAbrechnung();
			$summeVZlautKunde += $tas[$a]->GetVorauszahlungLautKunde();
		}
		// Hat der Kunde mehr Vorausgezahlt als nötig?
		if( $summeVZlautAbrechnung - $summeVZlautKunde < -10.0 ){
			$summe=$summeVZlautKunde-$summeVZlautAbrechnung;
			$replace=Array( 0 => Array("name" => "[VORAUSZAHLUNG_TA]", "value" => HelperLib::ConvertFloatToLocalizedString($summeVZlautAbrechnung) ),
							1 => Array("name" => "[VORAUSZAHLUNG_KUNDE]", "value" => HelperLib::ConvertFloatToLocalizedString($summeVZlautKunde) )
						);
			$this->CreateWP(self::WG_WIDERSPRUCHSPUNKT_VORAUSZAHLUNG, $replace, $summe);
		}
		return $summe;
	}
	
	
	/**
	 * Prüft die TAPs auf Kostensteigerung
	 * @param array $taps	TAPs
	 * @return float	Ermittelter Kürzungsbetrag 
	 */
	protected function PruefeKostensteigerung(&$taps)
	{
		$kuerzungsbetragSumme = 0.0;
		$summeBetragKundeProQMJahr = 0.0;
		$sollwert = 0.0;
		$list=Array();
		// Alle TAPs durchlaufen
		for( $a=0; $a<count($taps); $a++){
			// Ist TAP nicht umlagefähig?
			if (count($taps[$a]["vorjahr"])>0 && $taps[$a]["tap"]->GetBetragKunde()>0.0)
			{
				$betragKundeVorjahr = 0.0;
				for ($b=0; $b<count($taps[$a]["vorjahr"]); $b++)
				{
					//$gesamtbetragVorjahr += $taps[$a]["vorjahr"][$b]->GetGesamtbetrag();
					$betragKundeVorjahr += $taps[$a]["vorjahr"][$b]->GetBetragKunde();
				}
				$aenderungZumVorJahr = round($betragKundeVorjahr * 100.0 / $taps[$a]["tap"]->GetBetragKunde(), 2);
				if ($aenderungZumVorJahr>25.0 && ($taps[$a]["tap"]->GetBetragKunde()-$betragKundeVorjahr)>500.0 )
				{
					$list[] = $taps[$a];
					// TAP in zukünftigen Berechnungen nicht mehr miteinbeziehen
					$taps[$a]["use"]=false;
				}
			}
		}
		// Wurden TAPs von der übergebene FMS-Kostenart gefunden?
		if( count($list)>0 ){
			// Widerspruchspunkt erzeugen
			$replace = Array( 0 => Array("name" => "[TAP_LIST]", "value" => $list ) );
			$bedingungen = Array();
			if ($this->IsCostIncreaseExisting($list)) $bedingungen[] = 0;
			$this->CreateWP(self::WG_WIDERSPRUCHSPUNKT_KOSTENSTEIGERUNG, $replace, $kuerzungsbetragSumme, $bedingungen);
		}
		return $kuerzungsbetragSumme;
	}
	
	/**
	 * Sucht nach TAPs, deren rechnerischer Wert des "Betrags Kunde" von dem eingegebenen abweicht
	 * @param array	$taps		Zu überprüfende Teilabrechnungspositionen
	 * @return float				Summe des Kürzungsbetrag dieser Prüfung
	 * @access public
	 */
	protected function PruefeBerechnungsfehler($taps){
		$summe=0.0;
		$list=Array();
		// Alle TAPs durchlaufen
		for( $a=0; $a<count($taps); $a++){
            // ignore TAP if pauschale
            if ($taps[$a]["tap"]->IsPauschale()) continue;
			// Abweichung zwischen rechnerischem und eingegebenem Wert?
			if( $taps[$a]["betragKunde"]-$taps[$a]["tap"]->GetBetragKundeSoll($this->db)>1.0 ){
				// Benötigte Daten aus TAP holen
				$summe+=($taps[$a]["betragKunde"]-$taps[$a]["tap"]->GetBetragKundeSoll($this->db));
				$list[] = $taps[$a];
			}
		}
		// Wurden nicht umlagefähige TAPs gefunden?
		if( count($list)>0 ){
			// Widerspruchspunkt erzeugen
			$replace=Array( 0 => Array("name" => "[TAP_LIST_WRONG]", "value" => $this->BuildTapList($list, WiderspruchsGenerator::BUILDTAPLIST_OUTPUTMODE_TAPLIST_WITH_ERRORS) ),
							1 => Array("name" => "[TAP_LIST_CORRECT]", "value" => $this->BuildTapList($list, WiderspruchsGenerator::BUILDTAPLIST_OUTPUTMODE_TAPLIST_WITHOUT_ERRORS) ) 
						);
			$this->CreateWP(self::WG_WIDERSPRUCHSPUNKT_BERECHNUNGSFEHLER, $replace, $summe);
		}
		return $summe;
	}
	
	/**
	 * Gesamtkostendeckelung prüfen
	 * @param array $tas
	 * @param array $tapListComplete 
	 */
	protected function PruefeGesamtkostendeckelung($tas)
	{
		$kuerzungsbetragSumme = 0.0;
		for( $a=0; $a<count($tas); $a++ )
		{
			$bedingungen = Array(1);
			if (trim($this->contract->GetBemerkungGesamtkostendeckelungAllgemein())!="" )
			{
				$replace = Array( 
									0 => Array("name" => "[COMMENT]", "value" => $this->contract->GetBemerkungGesamtkostendeckelungAllgemein() ) ,
									1 => Array("name" => "[BETRAGKUNDE_TA]", "value" => HelperLib::ConvertFloatToLocalizedString($tas[$a]->GetAbrechnungsergebnisLautAbrechnung()) )
								);
				$bedingungen[] = 0;
			}
			$this->CreateWP(self::WG_WIDERSPRUCHSPUNKT_GESAMTKOSTENDECKELUNG, $replace, $kuerzungsbetragSumme, $bedingungen);
		}
		return $kuerzungsbetragSumme;
	}
	
}
?>