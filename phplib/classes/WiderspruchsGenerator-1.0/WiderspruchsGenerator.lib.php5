<?php
/**
 * Widerspruchsgenerator
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class WiderspruchsGenerator 
{
	/**
	 * Output mode for funciton BuildTapList
	 * @var enum
	 */
	const BUILDTAPLIST_OUTPUTMODE_TAPLIST = 0;					// TAP-Auflistung
	const BUILDTAPLIST_OUTPUTMODE_TAPLIST_WITH_ERRORS = 1;		// TAP-Auflistung mit Berechnungsfehler (function PruefeBerechnungsfehler)
	const BUILDTAPLIST_OUTPUTMODE_TAPLIST_WITHOUT_ERRORS = 2;	// TAP-Auflistung ohne Berechnungsfehler (funciton PruefeBerechnungsfehler)
	
	/**
	 * Gibt an, aus welchem Land die Sollwerte für die Berechnung herangezogen werden sollen
	 * @var string 
	 */
	protected $useSollwerteOfCountry = "DE";
	
	/**
	 * Allowed percental diff to previews year in (function CheckUnitDiffer)
	 * @var float 
	 */
	protected $checkUnitDifferValue = 10.0;						// 10%
	
	/**
	 * Prozentueller Schwellenwert für Kostensteigerung zum Vorjahr (function IsCostIncreaseExisting)
	 * @var float 
	 */
	protected $costIncreaseThresholdPercent = 25.0;				// 25%
	
	/**
	 * Wertabhängiger Schwellenwert für Kostensteigerung zum Vorjahr (function IsCostIncreaseExisting)
	 * @var float 
	 */
	protected $costIncreaseThresholdCurrency = 500.0;			// 500.00 (EUR/CHF etc.)
	
	/**
	 * Mapping-Tabelle von FMS-Kostenarten zu Textbausteinen
	 * @var array 
	 */
	protected $FMS_KOSTENART_WS_WIDERSPRUCHSPUNKT = Array();
	
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
		
	/**
	 * Widerspruch der verarbeitet wird
	 * @var Widerspruch
	 */
	protected $widerspruch = null;
	
	/**
	 * Abrechnungsjahr des Widerspruchs der verarbeitet wird
	 * @var AbrechnungsJahr
	 */
	protected $abrechnungsJahr = null;
		
	/**
	 * Vertrag des Widerspruchs der verarbeitet wird
	 * @var Contract
	 */
	protected $contract = null;
	
	/**
	 * Vertrag des Widerspruchs der verarbeitet wird
	 * @var CShop
	 */
	protected $shop = null;

	/**
	 * Vertrag des Widerspruchs der verarbeitet wird
	 * @var CLocation
	 */
	protected $location = null;

	/**
	 * Vertrag des Widerspruchs der verarbeitet wird
	 * @var CCompany
	 */
	protected $company = null;

	/**
	 * Vertrag des Widerspruchs der verarbeitet wird
	 * @var CGroup
	 */
	protected $group = null;
	
	/**
	 * Return the correct WiderspruchsGenerator for the passed country
	 * @param DBManager $db Datenbank-Objekt
	 * @param string $country
	 * @return WiderspruchsGenerator
	 */
	static public function GetWiderspruchsGeneratorForCountry(DBManager $db, $country)
	{
		switch($country)
		{
			case 'DE':
				return new WiderspruchsGenerator_DE($db);
			case 'CH':
				return new WiderspruchsGenerator_CH($db);
			case 'AT':
				return new WiderspruchsGenerator_AT($db);
			case 'BE':
				return new WiderspruchsGenerator_BE($db);
			case 'NL':
				return new WiderspruchsGenerator_NL($db);
			// add new cases here...
				
		}
		return new WiderspruchsGenerator_DE($db);
	}
	
	/**
	 * Konstruktor
	 * @param DBManager $db Datenbank-Objekt
	 */
	public function WiderspruchsGenerator(DBManager $db)
	{
		$this->db = $db;
	}
	
	/**
	 * Erzeugt die Widerspruchspunkte für den übergebenene Widerspruch
	 * @param Widerspruch $widerspruch
	 * @return boolean
	 */
	public function CreateWiderspruchspunkte(Widerspruch $widerspruch)
	{
		$this->widerspruch=$widerspruch;
		// Abrechnungsjahr
		$this->abrechnungsJahr=$this->widerspruch->GetAbrechnungsJahr();
		// Vertrag
		$this->contract=$this->abrechnungsJahr->GetContract();
		// Shop
		$this->shop=$this->contract->GetShop();
		// Standort
		$this->location=$this->shop->GetLocation();
		// Firma
		$this->company=$this->location->GetCompany();
		// Gruppe
		$this->group=$this->company->GetGroup();
		// Alle Teilabrechnungen holen 
		$tas = $this->abrechnungsJahr->GetTeilabrechnungen($this->db);
		// Alle TAPs in eine Liste packen...
		$tapList = Array();
		for ($a=0; $a<count($tas); $a++)
		{
			$taps=$tas[$a]->GetTeilabrechnungspositionen($this->db);
			for( $b=0; $b<count($taps); $b++ )
			{
				//echo $taps[$b]->GetPkey()."<br />";
				$tapList[]=Array("tap" => $taps[$b], "ta" => $tas[$a], "use" => true, "betragKunde" => $taps[$b]->GetBetragKunde(), "vorjahr" => Array() );
			}
		}
		// Vorjahr prüfen...
		$abrechnungsJahrVorjahr=$this->abrechnungsJahr->GetVorjahr($this->db);
		if ($abrechnungsJahrVorjahr!=null)
		{
			$tasTemp = $abrechnungsJahrVorjahr->GetTeilabrechnungen($this->db);
			foreach ($tasTemp as $taTemp)
			{
				$tapsTemp = $taTemp->GetTeilabrechnungspositionen($this->db);
				foreach ($tapsTemp as $tapTemp)
				{
					for ($c=0; $c<count($tapList); $c++)
					{
						if (trim($tapList[$c]["tap"]->GetBezeichnungKostenart())==trim($tapTemp->GetBezeichnungKostenart()))
						{
							//echo "Match: ".$tapList[$c]["tap"]->GetBezeichnungKostenart()."==".$tapTemp->GetBezeichnungKostenart()." (".$tapList[$c]["tap"]->GetBetragKunde()."-".$tapTemp->GetBetragKunde()."=".($tapList[$c]["tap"]->GetBetragKunde()-$tapTemp->GetBetragKunde()).")<br />";
							$tapList[$c]["vorjahr"][] = $tapTemp;
						}
					}
				}
			}
		}
		// do country specific stuff...
		return $this->Create($tas, $tapList);
	}
	
	/**
	 * Generate Widerspruch
	 * @param Teilabrechnung[] $tas
	 * @param Array $tapList
	 * @return boolean
	 */
	abstract protected function Create($tas, $tapList);
	
	
	/**
	 * Übernimmt aus der übergebenen Liste nur die TAPs bei denen "use"=true ist
	 * und gibt diese wiederum als Array zurück.
	 * @param array	$tapList		Liste mit TAPs die bereinigt werden soll
	 * @return array					Bereinigte Liste mit TAPs
	 * @access public
	 */
	protected function CleanTAPList(&$tapList)
	{
		$newTapList=Array();
		for ($a=0; $a<count($tapList); $a++)
		{
			if ($tapList[$a]["use"]) $newTapList[]=&$tapList[$a];
		}
		return $newTapList;
	}
	
	/**
	 * Prüft die übergebene FMS-Kostenart
	 * @param array $taps	TAPs
	 * @param array $tas	Teilabrechnungen
	 * @param type $rsKostenartID	Zu prüfende FMS-Kostenart
	 * @return float	Ermittelter Kürzungsbetrag 
	 */
	protected function PruefeRSKostenart(&$taps, $tas, $rsKostenartID)
	{
		$kuerzungsbetragSumme = 0.0;
		$summeBetragKundeProJahr = 0.0;
		$summeBetragKundeProQMJahr = 0.0;
		$list=Array();
		// Abrechnungszeitraum über alle TAs hinweg ermitteln 
		$abrechnungszeitraumVon = $tas[0]->GetAbrechnungszeitraumVon();
		$abrechnungszeitraumBis = $tas[0]->GetAbrechnungszeitraumBis();
		for ($a=1; $a<count($tas); $a++)
		{
			if ($abrechnungszeitraumVon>$tas[$a]->GetAbrechnungszeitraumVon()) $abrechnungszeitraumVon=$tas[$a]->GetAbrechnungszeitraumVon();
			if ($abrechnungszeitraumBis<$tas[$a]->GetAbrechnungszeitraumBis()) $abrechnungszeitraumBis=$tas[$a]->GetAbrechnungszeitraumBis();
		}
		// Anzhal Tage dieses Zeitraums ermitteln
		$abrechnungszeitraumTage = round((($abrechnungszeitraumBis+60*60*24)-$abrechnungszeitraumVon)/60/60/24);
		// Alle TAPs durchlaufen
		for ($a=0; $a<count($taps); $a++)
		{
			// Ist TAP nicht umlagefähig?
			if ($taps[$a]["tap"]->GetKostenartRSPKey()==$rsKostenartID)
			{
				// Benötigte Daten aus TAP holen
				$summeBetragKundeProJahr+=$taps[$a]["betragKunde"] / $taps[$a]["ta"]->GetWeight();
				$summeBetragKundeProQMJahr+=($taps[$a]["betragKunde"] / $this->contract->GetMietflaecheQM()) / $taps[$a]["ta"]->GetWeight();
				$list[] = $taps[$a];
				// TAP in zukünftigen Berechnungen nicht mehr miteinbeziehen
				$taps[$a]["use"]=false;
			}
		}
		// Wurden TAPs von der übergebenen FMS-Kostenart gefunden?
		if (count($list)>0)
		{
			$kostenartRS = new RSKostenart($this->db);
			if( $kostenartRS->Load($rsKostenartID, $this->db)!==true )$kostenartRS = null;
			$sollwertProQMJahr = ($kostenartRS==null ? 0.0 : $kostenartRS->GetSollwert($this->useSollwerteOfCountry, $this->location->GetLocationType()));
			$sollwertProJahr = $sollwertProQMJahr * $this->contract->GetMietflaecheQM();
			$sollwertAbrechnungszeitraum = $sollwertProQMJahr * $this->contract->GetMietflaecheQM() * $abrechnungszeitraumTage / 365.0;
			// Widerspruchspunkt erzeugen
			$replace = Array(	0 => Array("name" => "[TAP_LIST]", "value" => $list ),
								1 => Array("name" => "[SOLLWERT]", "value" => $kostenartRS==null ? "-" : HelperLib::ConvertFloatToLocalizedString($sollwertAbrechnungszeitraum) ),
								2 => Array("name" => "[SUMME_KUNDE]", "value" => HelperLib::ConvertFloatToLocalizedString($summeBetragKundeProJahr) ),
								3 => Array("name" => "[SUMME_KUNDE_QM_JAHR]", "value" => HelperLib::ConvertFloatToLocalizedString($summeBetragKundeProQMJahr) ),
								4 => Array("name" => "[SOLLWERT_DIFF]", "value" => HelperLib::ConvertFloatToLocalizedString($summeBetragKundeProJahr-$sollwertAbrechnungszeitraum) ),
								5 => Array("name" => "[COMMENT]", "value" => $this->contract->GetRSKostenartAllgemein($rsKostenartID) )
						);
			
			// FMS-Kostenarten-Speziefische Infos:
			$bedingungen = $this->ErzeugeKostenartenBedingungen($rsKostenartID, $list, $summeBetragKundeProJahr, $sollwertAbrechnungszeitraum);
			// add wsp only if at last one contition matched
			if (count($bedingungen)>0)
			{
				$this->CreateWP($this->FMS_KOSTENART_WS_WIDERSPRUCHSPUNKT[$rsKostenartID], $replace, $kuerzungsbetragSumme, $bedingungen);
			}
		}
		return $kuerzungsbetragSumme;
	}
	
	/**
	 * Erzeugt die Bedingungen für die übergebene Kostenart
	 * @param int $rsKostenartID
	 * @param array $list
	 * @param float $summeBetragKundeProJahr
	 * @param float $sollwertAbrechnungszeitraum
	 * @return Array
	 */
	protected function ErzeugeKostenartenBedingungen($rsKostenartID, &$list, $summeBetragKundeProJahr, $sollwertAbrechnungszeitraum)
	{
		$bedingungen = Array();
		switch($rsKostenartID)
		{
			case RSKostenartManager::RS_KOSTENART_WARTUNG_INSTANDHALTUNG_INSTANDSETZUNG:
				$bedingungen = Array();
				if ($this->contract->GetDeckelungInstandhaltung()) $bedingungen[] = 0;
				if ($this->contract->GetDeckelungInstandhaltung() && ($summeBetragKundeProJahr-$sollwertAbrechnungszeitraum > 1000.0)) $bedingungen[] = 1; // TODO: Prüfen ob korrekt
				if (!$this->contract->GetDeckelungInstandhaltung() && ($summeBetragKundeProJahr-$sollwertAbrechnungszeitraum > 1000.0)) $bedingungen[] = 2; // TODO: Prüfen ob korrekt
				if ($this->IsCostIncreaseExisting($list)) $bedingungen[] = 3;
				//$replace[] = Array("name" => "[SPECIAL_COMMENT]", "value" => $this->contract->GetDeckelungInstandhaltungBeschreibung() );
				break;
			case RSKostenartManager::RS_KOSTENART_VERWALTUNG_UND_MANAGEMENT:
				$bedingungen = Array();
				if ($this->contract->GetDeckelungVerwaltungManagement() ) $bedingungen[] = 0;
				if ($this->contract->GetDeckelungVerwaltungManagement() && ($summeBetragKundeProJahr-$sollwertAbrechnungszeitraum > 1000.0)) $bedingungen[] = 1; // TODO: Prüfen ob korrekt
				if (!$this->contract->GetDeckelungVerwaltungManagement() && ($summeBetragKundeProJahr-$sollwertAbrechnungszeitraum > 1000.0)) $bedingungen[] = 2; // TODO: Prüfen ob korrekt
				//if ($this->contract->GetGesetzlicheDefinitionVerwaltungManagement() ) $bedingungen[] = 2;
				if ($this->IsCostIncreaseExisting($list)) $bedingungen[] = 3;
				//$replace[] = Array("name" => "[SPECIAL_COMMENT]", "value" => $this->contract->GetDeckelungVerwaltungManagementBeschreibung() );
				break;
			case RSKostenartManager::RS_KOSTENART_HEIZKOSTEN:
				$bedingungen = Array(0);
				for($a=0; $a<count($list); $a++)
				{
					if ($list[$a]["ta"]->GetHeizkostenNachHVOAbgerechnet()==2)
					{
						$bedingungen[] = 1;
						break;
					}
				}
				if ($summeBetragKundeProJahr-$sollwertAbrechnungszeitraum > 1000.0) $bedingungen[] = 2;
				if ($this->IsCostIncreaseExisting($list)) $bedingungen[] = 3;
				break;
			case RSKostenartManager::RS_KOSTENART_LUEFTUNG_UND_KLIMATISIERUNG:
				$bedingungen = Array(0);
				if ($summeBetragKundeProJahr-$sollwertAbrechnungszeitraum > 1000.0) $bedingungen[] = 1;
				if ($this->IsCostIncreaseExisting($list)) $bedingungen[] = 2;
				break;
			case RSKostenartManager::RS_KOSTENART_ALLGEMEINSTROM:
				$bedingungen = Array(0);
				if ($summeBetragKundeProJahr-$sollwertAbrechnungszeitraum > 1000.0) $bedingungen[] = 1;
				if ($this->IsCostIncreaseExisting($list)) $bedingungen[] = 2;
				break;
			case RSKostenartManager::RS_KOSTENART_VERSICHERUNGEN:
				$bedingungen = Array(0);
				if ($summeBetragKundeProJahr-$sollwertAbrechnungszeitraum > 1000.0) $bedingungen[] = 1;
				if ($this->IsCostIncreaseExisting($list)) $bedingungen[] = 2;
				break;
			case RSKostenartManager::RS_KOSTENART_OEFFENTLICHE_ABGABEN:
				$bedingungen = Array(0);
				if ($summeBetragKundeProJahr-$sollwertAbrechnungszeitraum > 1000.0) $bedingungen[] = 1;
				if ($this->IsCostIncreaseExisting($list)) $bedingungen[] = 2;
				break;
			case RSKostenartManager::RS_KOSTENART_ENTSORGUNG:
				$bedingungen = Array();
				if ($summeBetragKundeProJahr-$sollwertAbrechnungszeitraum > 1000.0) $bedingungen[] = 0;
				if ($this->IsCostIncreaseExisting($list)) $bedingungen[] = 1;
				break;
			case RSKostenartManager::RS_KOSTENART_REINIGUNG_UND_PFLEGE:
				$bedingungen = Array();
				if ($summeBetragKundeProJahr-$sollwertAbrechnungszeitraum > 1000.0) $bedingungen[] = 0;
				if ($this->IsCostIncreaseExisting($list)) $bedingungen[] = 1;
				break;
			case RSKostenartManager::RS_KOSTENART_SONSTIGES:
				$bedingungen = Array();
				if (trim($this->contract->GetRSKostenartAllgemein($rsKostenartID))!="")$bedingungen[] = 0;
				break;
		}
		return $bedingungen;
	}
	
	/**
	 * Erzeugt den Widerspruchspunkt mit der übergebene ID und fügt diesen dem Widerspruch hinzu
	 * @param int $wpID ID des zu erzeugenden Widerspruchpunkts
	 * @param Array $replace
	 * @param float $kuerzungsbetrag
	 * @param Array $bedingungen
	 * @return Widerspruchspunkt|boolean Widerspruchspunkt-Objekt oder false
	 */
	protected function CreateWP($wpID, $replace, $kuerzungsbetrag=0.0, $bedingungen=Array(0))
	{
		// Allgemeine Platzhalter hinzufügen
		$replace[] = Array("name" => "[ABRECHNUNGSJAHR]", "value" => $this->abrechnungsJahr->GetJahr());
		$replace[] = Array("name" => "[NAME_KUNDE]", "value" => $this->group->GetName());
		$replace[] = Array("name" => "[KUERZUNGSBETRAG]", "value" => HelperLib::ConvertFloatToLocalizedString($kuerzungsbetrag));
		$replace[] = Array("name" => "[EROEFFNUNGSDATUM]", "value" => $this->shop->GetOpening()==0 ? "-" : date("d.m.Y", $this->shop->GetOpening()));
		$replace[] = Array("name" => "[BANKVERBINDUNG_KUNDE]", "value" => str_replace("\n", "<br />", $this->company->GetBankverbindung()) );
		sort($bedingungen, SORT_NUMERIC);
		$tm=new TextModule($this->db);
		if( $tm->Load($wpID, $this->db)===true)
		{
			$textLeft = '<strong>[WS_NUMMER] '.$tm->GetTitle().'</strong><br />';
			$textLeft.= $tm->GetTextLeft();
			
			$textLeft = $this->ReplaceText($textLeft, $replace);
			$textRight = "<br />";
			for ($a=0; $a<count($bedingungen); $a++)
			{
				$textRight.=$tm->GetTextRight($bedingungen[$a]);
			}
			$textRight = $this->ReplaceText($textRight, $replace);
			
			$wp=new Widerspruchspunkt($this->db);
			$wp->SetAutoGenerated(true);
			$wp->SetTitle( $tm->GetTitle() );
			$wp->SetRank($this->widerspruch->GetHighestRank($this->db)+10);
			$wp->SetTextLeft( $textLeft );
			$wp->SetTextRight( $textRight );
			$wp->SetWiderspruch($this->widerspruch);
			if ($wp->Store($this->db)===true)
			{
				// Kürzungsbetrag festlegen
				$kuerzungsbetragObj = new Kuerzungsbetrag($this->db);
				$kuerzungsbetragObj->SetKuerzungsbetrag($kuerzungsbetrag);
				$kuerzungsbetragObj->SetRating(Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU);
				$kuerzungsbetragObj->SetType(Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG);
				$kuerzungsbetragObj->SetWiderspruchspunkt($wp);
				$kuerzungsbetragObj->Store($this->db);
			}
			return $wp;
		}
		return false;
	}
	
	/**
	 * Replaces text in the given string
	 * @param string $originalText	Original text
	 * @param array $replace	Array with the text to replace
	 * @return string	The replaced Text
	 */
	protected function ReplaceText($originalText, $replace)
	{
		for ($a=0; $a<count($replace); $a++)
		{
			if ($replace[$a]["name"]=="[TAP_LIST]")
			{
				$replace[$a]["value"] = $this->BuildTapList($replace[$a]["value"]);
			}
			elseif( is_array($replace[$a]["value"]) )
			{
				$strTemp="";
				for($b=0; $b<count($replace[$a]["value"]); $b++)
				{
					for($c=0; $c<count($replace[$a]["value"][$b]); $c++)
					{
						$strTemp.=$replace[$a]["value"][$b][$c]."    ";
					}
					$strTemp.="\n";
				}
				$replace[$a]["value"]=$strTemp;
			}
			$originalText = str_replace($replace[$a]["name"], $replace[$a]["value"], $originalText);
		}
		return $originalText;
	}
	
	/**
	 * Check if the passed entry of the unit tap data list differs more then '$this->checkUnitDifferValue'% (percent) from the previous year
	 * @param array $untitTapData
	 * @return boolean
	 */
	protected function CheckUnitDiffer($untitTapData)
	{
		$showWarning = false;
		foreach ($untitTapData as $tap)
		{
			if (isset($tap["previousYear"]) && count($tap["previousYear"])>0)
			{
				if (count($tap["previousYear"])==1)
				{
					foreach ($tap["previousYear"] as $tapPreviousYear)
					{
						// different units
						if ($tapPreviousYear['unit']!=$tap['unit']) $showWarning = true;
						elseif ($tapPreviousYear['value']!=$tap['value'])
						{
							// values differ > '$this->checkUnitDifferValue'% (percent)
							if (abs((($tapPreviousYear['value']*100.0)/$tap['value'])-100.0)>$this->checkUnitDifferValue) $showWarning = true;
						}
					}
				}
				else
				{
					// different values in previous year
					$showWarning = true;
				}
			}
		}
		return $showWarning;
	}
	
	/**
	 * Check if any entry of the unit tap data list differs more then '$this->checkUnitDifferValue'% (percent) from the previous year
	 * @param array $untitTapData
	 * @return boolean
	 */
	protected function CheckUnitDifferAll($untitTapData)
	{
		foreach ($untitTapData as $taps)
		{
			if ($this->CheckUnitDiffer($taps)) return true;
		}
		return false;
	}
	
	/**
	 * Build the table "Umlageschlüssel" 
	 * @global array $NKM_TEILABRECHNUNGSPOSITION_EINHEIT
	 * @param array $untitTapData
	 * @param bool $checkPreviousYear
	 * @return string
	 */
	protected function BuildUnitTapList($untitTapData, $checkPreviousYear)
	{
		global $NKM_TEILABRECHNUNGSPOSITION_EINHEIT;
		ob_start();
		$showPreviousYearColumn = $checkPreviousYear;
		$showPreviousYearColumn = false; // Immer aus! Änderungswunsch vom 05.10.2012 weil WS sonst zu unübersichtlich wird
		$showColumnAenderungGross = false;
		if ($checkPreviousYear)
		{
			foreach ($untitTapData as $key => $taps)
			{
				if ($this->CheckUnitDiffer($taps))
				{
					$showColumnAenderungGross = true;
					break;
				}
			}
		}
		
		?>
		<table id="wstable">
			<tr>
			<?	if ($showColumnAenderungGross){?>
					<th style="width: 10px;">&#160;</th>
			<?	}?>
				<th>Bezeichnung lt. Abrechnung</th>
				<th>Gesamteinheiten</th>
			<?	if ($showPreviousYearColumn && $checkPreviousYear){?>
					<th>Gesamteinheiten Vorjahr</th>
			<?	}?>
			</tr>
			<?
			foreach ($untitTapData as $key => $taps)
			{
				?>
				<tr>
				<?	if ($showColumnAenderungGross){?>
						<td>
						<?	$showWarning = false;
							if ($checkPreviousYear)
							{
								$showWarning = $this->CheckUnitDiffer($taps);
							}?>
							<?=($showWarning ? "<strong>!</strong>" : "&#160;");?>
						</td>
				<?	}?>
					<td><?=$key;?></td>
					<td style="text-align: right;">
					<?	foreach ($taps as $tap)
						{
							echo HelperLib::ConvertFloatToLocalizedString($tap['value'])." ".$NKM_TEILABRECHNUNGSPOSITION_EINHEIT[$tap['unit']]['short'];
							if (isset($tap["previousYear"]))
							{
								foreach ($tap["previousYear"] as $tapPreviousYear)
								{
									echo "<br />";
								}
							}
						}
					?>
					</td>
				<?	if ($showPreviousYearColumn && $checkPreviousYear){?>
						<td style="text-align: right;">
						<?	foreach ($taps as $tap)
							{
								if (isset($tap["previousYear"]) && count($tap["previousYear"])>0)
								{
									foreach ($tap["previousYear"] as $tapPreviousYear)
									{
										echo HelperLib::ConvertFloatToLocalizedString($tapPreviousYear['value'])." ".$NKM_TEILABRECHNUNGSPOSITION_EINHEIT[$tapPreviousYear['unit']]['short']."<br />";
									}
								}
								else
								{
									echo "-<br />";
								}
							}
						?>
						</td>
				<?	}?>
				</tr>
				<?
			}
			?>
		</table>
		<?
		$list = ob_get_contents();
		ob_end_clean();
		return $list;
	}
	
	/**
	 * Erzeugt eine HTML-Tabelle mit den überegbenen TAPs
	 * @param array $taps TAPs
	 * @param int $outputMode 0=TAP-Auflistung / 1=TAP-Auflistung mit Berechnungsfehler / TAP-Auflistung ohne Berechnungsfehler  (1 und 2 für Fn. PruefeBerechnungsfehler() )
	 * @return string HTML-Tabelle 
	 */
	protected function BuildTapList($taps, $outputMode = self::BUILDTAPLIST_OUTPUTMODE_TAPLIST)
	{
		$data = $this->BuildTapListArray($taps);
		ob_start();
		$currency = $this->contract->GetCurrency();
		// check if the column "change to previous year" have to be hidden
		$showColumnChangeToPreviousYear = false;
		$showColumnAenderungGross = false;
		for($a=0; $a<count($data["taps"]); $a++)
		{
			if (isset($data["taps"][$a]["aenderungZumVorjahr"]) && is_numeric($data["taps"][$a]["aenderungZumVorjahr"]) && $data["taps"][$a]["aenderungZumVorjahr"]!=0.0)
			{
				$showColumnChangeToPreviousYear = true;
			}
			if ($data["taps"][$a]["aenderungGross"])
			{
				$showColumnAenderungGross = true;
			}
		}
		$showColumnChangeToPreviousYear = false; // Immer aus! Änderungswunsch vom 05.10.2012 weil WS sonst zu unübersichtlich wird
		?>
		<table id="wstable">
			<tr>
			<?	if ($showColumnAenderungGross){?>
					<th style="width: 10px;">&#160;</th>
			<?	}?>
				<th>Bezeichnung lt. Abrechnung</th>
				<th>Gesamtkosten</th>
			<?	if ($outputMode>=self::BUILDTAPLIST_OUTPUTMODE_TAPLIST_WITH_ERRORS){?>
					<th>Gesamt-einheiten</th>
					<th>Einheiten <?=$this->group->GetName();?></th>
			<?	}?>
				<th>Kostenanteil <?=$this->group->GetName();?></th>
			<?	if ($outputMode==self::BUILDTAPLIST_OUTPUTMODE_TAPLIST && $showColumnChangeToPreviousYear){?>
					<th>Veränderung in % zum Vorjahr</th>
			<?	}?>
			</tr>
			<?
			for($a=0; $a<count($data["taps"]); $a++)
			{
				?>
				<tr>
				<?	if ($showColumnAenderungGross){?>
						<td><?=($data["taps"][$a]["aenderungGross"] ? "<strong>!</strong>" : "&#160;");?></td>
				<?	}?>
					<td><?=$data["taps"][$a]["bezeichnungKostenart"];?></td>
					<td style="text-align: right;"><?=HelperLib::ConvertFloatToLocalizedString($data["taps"][$a]["gesamtbetrag"]);?> <?=$currency;?></td>
				<?	if ($outputMode>=self::BUILDTAPLIST_OUTPUTMODE_TAPLIST_WITH_ERRORS){?>
						<td style="text-align: right;"><?=HelperLib::ConvertFloatToLocalizedString($data["taps"][$a]["gesamteinheiten"]);?></td>
						<td style="text-align: right;"><?=HelperLib::ConvertFloatToLocalizedString($data["taps"][$a]["einheitenKunde"]);?></td>
				<?	}?>
					<td style="text-align: right;"><?=HelperLib::ConvertFloatToLocalizedString($outputMode!=self::BUILDTAPLIST_OUTPUTMODE_TAPLIST_WITHOUT_ERRORS ? $data["taps"][$a]["betragKunde"] : $data["taps"][$a]["betragKundeSoll"]);?> <?=$currency;?></td>
				<?	if ($outputMode==self::BUILDTAPLIST_OUTPUTMODE_TAPLIST && $showColumnChangeToPreviousYear){?>
						<td style="text-align: right;"><?=(count($taps[$a]["vorjahr"])>0 && $taps[$a]["tap"]->GetBetragKunde()>0.0 ? HelperLib::ConvertFloatToRoundedLocalizedString($data["taps"][$a]["aenderungZumVorjahr"]) : "-");?></td>
				<?	}?>
				</tr>
				<?
			}
			?>
			<tr>
			<?	if ($showColumnAenderungGross){?>
					<td>&#160;</td>
			<?	}?>
				<td><strong>Summe</strong></td>
				<td style="text-align: right;"><strong><?=HelperLib::ConvertFloatToLocalizedString($data["summeGesamtbetrag"]);?> <?=$currency;?></strong></td>
			<?	if ($outputMode>=self::BUILDTAPLIST_OUTPUTMODE_TAPLIST_WITH_ERRORS){?>
					<td style="text-align: right;">&#160;</td>
					<td style="text-align: right;">&#160;</td>
			<?	}?>
				<td style="text-align: right;"><strong><?=HelperLib::ConvertFloatToLocalizedString($outputMode!=self::BUILDTAPLIST_OUTPUTMODE_TAPLIST_WITHOUT_ERRORS ? $data["summeBetragKunde"] : $data["summeBetragKundeSoll"]);?> <?=$currency;?></strong></td>
			<?	if ($outputMode==self::BUILDTAPLIST_OUTPUTMODE_TAPLIST && $showColumnChangeToPreviousYear){?>
					<td style="text-align: right;"><strong><?=(count($taps[$a]["vorjahr"])>0 && $taps[$a]["tap"]->GetBetragKunde()>0.0 ? HelperLib::ConvertFloatToRoundedLocalizedString($data["summeAenderungZumVorjahr"]) : "-");?></strong></td>
			<?	}?>
			</tr>
		</table>
		<?
		$list = ob_get_contents();
		ob_end_clean();
		return $list;
	}
	
	/**
	 * Bereitet ein Array mit den Daten der übergebenen TAPs auf (Für Fn. BuildTapList()) 
	 * @param Array $taps
	 * @return Array 
	 */
	protected function BuildTapListArray($taps)
	{
		$returnValue = Array();
		$returnValue["summeGesamtbetrag"] = 0.0;
		$returnValue["summeBetragKunde"] = 0.0;
		$returnValue["summeBetragKundeSoll"] = 0.0;
		$returnValue["summeGesamtbetragVorjahr"] = 0.0;
		$returnValue["summeBetragKundeVorjahr"] = 0.0;
		$returnValue["summeAenderungZumVorjahr"] = 0.0;
		$returnValue["taps"] = Array();
		for($a=0; $a<count($taps); $a++)
		{
			$betragKundeSoll = $taps[$a]["tap"]->GetBetragKundeSoll($this->db);
			$returnValue["summeGesamtbetrag"] += $taps[$a]["tap"]->GetGesamtbetrag();
			$returnValue["summeBetragKunde"] += $taps[$a]["tap"]->GetBetragKunde();
			$returnValue["summeBetragKundeSoll"] += $betragKundeSoll;
			$tapData = Array();
			$tapData["bezeichnungKostenart"] = $taps[$a]["tap"]->GetBezeichnungKostenart();
			$tapData["gesamtbetrag"] = $taps[$a]["tap"]->GetGesamtbetrag();
			$tapData["gesamteinheiten"] = $taps[$a]["tap"]->GetGesamteinheiten();
			$tapData["betragKunde"] = $taps[$a]["tap"]->GetBetragKunde();
			$tapData["betragKundeSoll"] = $betragKundeSoll;
			$tapData["einheitenKunde"] = $taps[$a]["tap"]->GetEinheitKunde();
			$tapData["gesamtbetragVorjahr"] = 0.0;
			$tapData["betragKundeVorjahr"] = 0.0;
			$tapData["aenderungZumVorjahr"] = 0.0;
			$tapData["aenderungGross"] = false;
			
			if (count($taps[$a]["vorjahr"])>0 && $tapData["betragKunde"]>0.0)
			{
				for ($b=0; $b<count($taps[$a]["vorjahr"]); $b++)
				{
					$tapData["gesamtbetragVorjahr"] += $taps[$a]["vorjahr"][$b]->GetGesamtbetrag();
					$tapData["betragKundeVorjahr"] += $taps[$a]["vorjahr"][$b]->GetBetragKunde();
				}
				$returnValue["summeGesamtbetragVorjahr"] += $tapData["gesamtbetragVorjahr"];
				$returnValue["summeBetragKundeVorjahr"] += $tapData["betragKundeVorjahr"];
				$tapData["aenderungZumVorjahr"] = round(($tapData["betragKunde"] * 100.0 / $tapData["betragKundeVorjahr"])-100.0, 2);
				if ($tapData["aenderungZumVorjahr"]>25.0 && ($tapData["betragKunde"]-$tapData["betragKundeVorjahr"])>500.0 )
				{
					$tapData["aenderungGross"] = true;
				}
			}
			$returnValue["taps"][] = $tapData;
		}
		if ($tapData["summeBetragKunde"]>0.0)
		{
			$returnValue["summeAenderungZumVorjahr"] = round($tapData["summeBetragKundeVorjahr"] * 100.0 / $tapData["summeBetragKunde"], 2);
		}
		return $returnValue;
	}
	
	/**
	 * Prüft, ob bei den übergebenen TAPs eine Kostensteigerung zum Vorjahr von > '$this->costIncreaseThresholdPercent'% (percent) und > '$this->costIncreaseThresholdCurrency' (EUR/CHF etc.) existiert
	 * @param Array $taps
	 * @return boolean Kostensteigerung true=Ja/false=Nein
	 */
	protected function IsCostIncreaseExisting($taps)
	{
		for($a=0; $a<count($taps); $a++)
		{
			if (count($taps[$a]["vorjahr"])>0 && $taps[$a]["tap"]->GetBetragKunde()>0.0)
			{
				$betragKundeVorjahr = 0.0;
				for ($b=0; $b<count($taps[$a]["vorjahr"]); $b++)
				{
					//$gesamtbetragVorjahr += $taps[$a]["vorjahr"][$b]->GetGesamtbetrag();
					$betragKundeVorjahr += $taps[$a]["vorjahr"][$b]->GetBetragKunde();
				}
				$aenderungZumVorJahr = round(($taps[$a]["tap"]->GetBetragKunde() * 100.0 / $betragKundeVorjahr)-100.0, 2);
				if ($aenderungZumVorJahr>$this->costIncreaseThresholdPercent && ($taps[$a]["tap"]->GetBetragKunde() - $betragKundeVorjahr)>$this->costIncreaseThresholdCurrency )
				{
					return true;
				}
			}
		}
		return false;
	}		
}


require_once 'WiderspruchsGenerator_DE.lib.php5';
require_once 'WiderspruchsGenerator_CH.lib.php5';
require_once 'WiderspruchsGenerator_AT.lib.php5';
require_once 'WiderspruchsGenerator_BE.lib.php5';
require_once 'WiderspruchsGenerator_NL.lib.php5';

?>