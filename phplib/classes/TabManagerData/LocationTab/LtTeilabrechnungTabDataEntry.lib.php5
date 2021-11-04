<?php
/**
 * TabDataEntry implementation
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class LtTeilabrechnungTabDataEntry extends LtLocationBaseTabDataEntry 
{
	/**
	 * current contract
	 * @var Teilabrechnung
	 */
	protected $teilabrechnung = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 * @param CShop $contract
	 */
	public function LtTeilabrechnungTabDataEntry(DBManager $db, ExtendedLanguageManager $languageManager, Teilabrechnung $teilabrechnung)
	{
		$this->teilabrechnung = $teilabrechnung;
		parent::__construct($db, $languageManager, $this->teilabrechnung->GetPKey(), trim($this->teilabrechnung->GetBezeichnung())=="" ? "(TA ohne Name)" : $this->teilabrechnung->GetBezeichnung());
	}
	
	/**
	 * Output the HTML for this tabs
	 * @return bool
	 */
	public function PrintContent()
	{
		$eigentuemer=$this->teilabrechnung->GetEigentuemer();
		$verwalter=$this->teilabrechnung->GetVerwalter();
		$anwalt=$this->teilabrechnung->GetAnwalt();
		$currency = $this->teilabrechnung->GetCurrency();
		// Header ausgeben
		$headerData=Array();
		$headerData[0]=Array( 	
								Array( "name" => Teilabrechnung::GetAttributeName($this->languageManager, 'korrigiertesAbrechnungsergebnis'), "value" => $currency." ".HelperLib::ConvertFloatToLocalizedString($this->teilabrechnung->GetKorrigiertesAbrechnungsergebnis()) ),
								Array( "name" => Teilabrechnung::GetAttributeName($this->languageManager, 'umlageflaeche_qm'), "value" => HelperLib::ConvertFloatToLocalizedString($this->teilabrechnung->GetUmlageflaecheQM())),
								Array( "name" => Teilabrechnung::GetAttributeName($this->languageManager, 'fristBelegeinsicht'), "value" => $this->teilabrechnung->GetFristBelegeinsicht()==0 ? "-" : date("d.m.Y", $this->teilabrechnung->GetFristBelegeinsicht()) ),
								Array( "name" => Teilabrechnung::GetAttributeName($this->languageManager, 'fristWiderspruch'), "value" => $this->teilabrechnung->GetFristWiderspruch()==0 ? "-" : date("d.m.Y", $this->teilabrechnung->GetFristWiderspruch()) ),
								Array( "name" => Teilabrechnung::GetAttributeName($this->languageManager, 'fristZahlung'), "value" => $this->teilabrechnung->GetFristZahlung()==0 ? "-" : date("d.m.Y", $this->teilabrechnung->GetFristZahlung()) ),
							);
		$headerData[1]=Array( 	
								Array( "name" => Teilabrechnung::GetAttributeName($this->languageManager, 'datum'), "value" => $this->teilabrechnung->GetDatum()==0 ? "-" : date("d.m.Y", $this->teilabrechnung->GetDatum()) ),
								Array( "name" => Teilabrechnung::GetAttributeName($this->languageManager, 'abrechnungszeitraumVon'), "value" => $this->teilabrechnung->GetAbrechnungszeitraumVon()==0 ? "-" : date("d.m.Y", $this->teilabrechnung->GetAbrechnungszeitraumVon()) ),
								Array( "name" => Teilabrechnung::GetAttributeName($this->languageManager, 'abrechnungszeitraumBis'), "value" => $this->teilabrechnung->GetAbrechnungszeitraumBis()==0 ? "-" : date("d.m.Y", $this->teilabrechnung->GetAbrechnungszeitraumBis()) ),
								Array( "name" => "Abrechnungszeitraum Tage", "value" => round($this->teilabrechnung->GetNumDays()) ),
							);
		$headerData[2]=Array( 	Array( "name" => "Heizkosten nach HVO abgerechnet", "value" => $this->teilabrechnung->GetHeizkostenNachHVOAbgerechnet()==0 ? "-" : ($this->teilabrechnung->GetHeizkostenNachHVOAbgerechnet()==1 ? "Ja" : "Nein") ),
								Array( "name" => "Erstabrechnung/Abr. nach Umbau", "value" => $this->teilabrechnung->GetErstabrechnungOderAbrechnungNachUmbau()==0 ? "-" : ($this->teilabrechnung->GetErstabrechnungOderAbrechnungNachUmbau()==1 ? "Ja" : "Nein") ),
								Array( "name" => "Teilabrechnung erfasst", "value" => $this->teilabrechnung->GetErfasst() ? "Ja" : "Nein" ),
								Array( "name" => Teilabrechnung::GetAttributeName($this->languageManager, 'auftragsdatumAbrechnung'), "value" => $this->teilabrechnung->GetAuftragsdatumAbrechnung()==0 ? "-" : date("d.m.Y", $this->teilabrechnung->GetAuftragsdatumAbrechnung()) ),
							);
		// Ansprechpartner ausgeben
		$this->PrintTabHeaderContent($headerData, 3, 1, false);
	
		$headerData=Array();
		$headerData[0]=Array( 	Array( "name" => "Eigentümer".($this->teilabrechnung->GetSchriftverkehrMit()==NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_EIGENTUEMER ? " (Schriftverkehr)" : ""), "value" => $eigentuemer==null ? "-" : ( $this->userIsRSMember ? "<a href='javascript:".(is_a($eigentuemer, "AddressData") ? "ShowAddress" : "ShowAddressCompany")."(".$eigentuemer->GetPKey().");'>".$eigentuemer->GetAddressIDString(true)."</a>" : $eigentuemer->GetAddressIDString(true)) ) );
		$headerData[1]=Array( 	Array( "name" => "Verwalter".($this->teilabrechnung->GetSchriftverkehrMit()==NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_VERWALTER ? " (Schriftverkehr)" : ""), "value" => $verwalter==null ? "-" : ( $this->userIsRSMember ? "<a href='javascript:".(is_a($verwalter, "AddressData") ? "ShowAddress" : "ShowAddressCompany")."(".$verwalter->GetPKey().");'>".$verwalter->GetAddressIDString(true)."</a>" : $verwalter->GetAddressIDString(true)) ) );
		$headerData[2]=Array( 	Array( "name" => "Anwalt".($this->teilabrechnung->GetSchriftverkehrMit()==NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_ANWALT ? " (Schriftverkehr)" : ""), "value" => $anwalt==null ? "-" : ( $this->userIsRSMember ? "<a href='javascript:".(is_a($anwalt, "AddressData") ? "ShowAddress" : "ShowAddressCompany")."(".$anwalt->GetPKey().");'>".$anwalt->GetAddressIDString(true)."</a>" : $anwalt->GetAddressIDString(true)) ) );
		$this->PrintTabHeaderContent($headerData, 3, 2, false);
		
		// comparison table
		$year = $this->teilabrechnung->GetAbrechnungsJahr();
		if ($year!=null)
		{
			$processStatus = $year->GetProcessStatus($this->db);
			if ($processStatus!=null)
			{
				?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<td width="29">&#160;</td>
					<td><?TeilabrechnungTabDataEntry::PrintComparisonTable($this->db, $processStatus, $this->teilabrechnung, '', true);?></td>
					<td width="29">&#160;</td>
				</table>
				<?
			}
		}
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			// TAPs ausgeben
			$list1 = new NCASList( new TeilabrechnungspositionReadOnlyListData($this->db, $this->teilabrechnung), "FM_FORM" );
			$list1->PrintData();
		}
	}
	
}
?>