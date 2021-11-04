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
class LtContractTabDataEntry extends LtLocationBaseTabDataEntry 
{
	/**
	 * current contract
	 * @var Contract
	 */
	protected $contract = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 * @param CShop $contract
	 */
	public function LtContractTabDataEntry(DBManager $db, ExtendedLanguageManager $languageManager, Contract $contract)
	{
		$this->contract = $contract;
		parent::__construct($db, $languageManager, $this->contract->GetPKey(), "Vertrag ".($this->contract->GetLifeOfLeaseString()=='' ? ($a+1) : $this->contract->GetLifeOfLeaseString()));
	}
	
	/**
	 * Output the HTML for this tabs
	 * @return bool
	 */
	public function PrintContent()
	{
		global $DOMAIN_HTTP_ROOT;
		// Header ausgeben
		$headerData=Array();
		$headerData[0]=Array( 	Array( "name" => "Mietfläche", "value" => HelperLib::ConvertFloatToLocalizedString($this->contract->GetMietflaecheQM())." m²" ),
								Array( "name" => "Umlagefläche", "value" => HelperLib::ConvertFloatToLocalizedString($this->contract->GetUmlageflaecheQM())." m²" ),
								
							);
		$headerData[1]=Array( 	Array( "name" => "Deckelung Instandhaltung", "value" => $this->contract->GetDeckelungInstandhaltung() ? "Ja" : "Nein" ),
								Array( "name" => "Zurückbehaltung ausgeschlossen", "value" => $this->contract->GetZurueckBehaltungAusgeschlossen()==Contract::CONTRACT_YES ? "Ja" : ($this->contract->GetZurueckBehaltungAusgeschlossen()==Contract::CONTRACT_NO ? "Nein" : "-") ),
							);
		$headerData[2]=Array( 	Array( "name" => "Vertrag erfasst", "value" => $this->contract->GetVertragErfasst() ? "Ja" : "Nein" ),
								Array( "name" => "Stammdatenblatt", "value" => $this->contract->getStammdatenblatt() ?
									'<a href="'.$DOMAIN_HTTP_ROOT.'templates/download_file.php5?'.SID.'&code='.$_SESSION['fileDownloadManager']->AddDownloadFile($this->contract->getStammdatenblatt()).'&timestamp='.time().'">[download]</a>'
									: "-" ),
							);
		$this->PrintTabHeaderContent($headerData, 3, 1, false);
		// Abrechnungsjahre ausgeben...
		echo "<br />";
		$tabs=new TabManager( new LtYearTabData($this->db, $this->languageManager, $this->contract) );
		$tabs->PrintData();
	}
	
}
?>