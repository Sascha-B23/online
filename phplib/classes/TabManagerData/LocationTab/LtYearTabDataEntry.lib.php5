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
class LtYearTabDataEntry extends LtLocationBaseTabDataEntry 
{
	/**
	 * current contract
	 * @var AbrechnungsJahr
	 */
	protected $year = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 * @param CShop $contract
	 */
	public function LtYearTabDataEntry(DBManager $db, ExtendedLanguageManager $languageManager, AbrechnungsJahr $year)
	{
		$this->year = $year;
		parent::__construct($db, $languageManager, $this->year->GetPKey(), $this->year->GetJahr());
	}
	
	/**
	 * Output the HTML for this tabs
	 * @return bool
	 */
	public function PrintContent()
	{
		global $DOMAIN_HTTP_ROOT;
		$status=$this->year->GetProcessStatus($this->db);
		$statusGroup = $status->GetProcessStatusGroup($this->db);
		if ($_POST["showArchievedStatus"]>=0 && $status->GetArchiveStatus()!=$_POST["showArchievedStatus"]) return;
		
		$widersprueche=$this->year->GetWidersprueche($this->db);
		$currency = $this->year->GetCurrency();
		// Header ausgeben
		$headerData=Array();
		$headerData[0]=Array( 	Array( "name" => "Alle TAs erfasst", "value" => $this->year->GetAlleTeilabrechnungenErfasst() ? "Ja" : "Nein" ),
							);
		$headerData[1]=Array( 	Array( "name" => "Aufgabe/Status", "value" => $status==null ? "-" : (WorkflowManager::UserAllowedToEditProzess($this->db, $status) ? "<a href='".$DOMAIN_HTTP_ROOT."de/meineaufgaben/process.php5?processId=".WorkflowManager::GetProcessStatusId($statusGroup!=null ? $statusGroup : $status)."&".SID."'>".$status->GetCurrentStatusName($_SESSION["currentUser"], $this->db)."</a>" : $status->GetCurrentStatusName($_SESSION["currentUser"], $this->db)) ),
								Array( "name" => "Frist Prozessstaus", "value" => $status==null ? "-" : ($status->GetDeadline()==0 ? "-" : date("d.m.Y", $status->GetDeadline())) ),
							);
		$headerData[2]=Array( 	Array( "name" => "Summe Betrag Kunde", "value" => $status==null ? "-" : $currency." ".HelperLib::ConvertFloatToLocalizedString($status->GetSummeBetragKunde($this->db)) ),
								Array( "name" => "Summe Betrag Kunde&#160m²", "value" => $status==null ? "-" : $currency." ".HelperLib::ConvertFloatToLocalizedString($status->GetSummeBetragKundeQM($this->db)) ),
							);

		if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_SHOW_REPORT_TERMINSCHIENE)) $headerData[0][] = Array( "name" => "Terminschiene", "value" => "<a href='terminschiene.php5?".SID."&year=".$this->year->GetPKey()."' target='_Terminschiene'>Anzeigen</a>" );

		$this->PrintTabHeaderContent($headerData, 3, 1, false);
		echo "<br />";
		// Teilabrechnungen ausgeben...
		$tabs=new TabManager(new LtTeilabrechnungTabData($this->db, $this->languageManager, $this->year));
		$tabs->PrintData();
	}
	
}
?>