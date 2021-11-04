<?php
require_once 'ReportManager.lib.php5';
/**
 * Implementation of report 'Kürzungsbeträge'
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2016 Stoll von Gáti GmbH www.stollvongati.com
 */
class KostenartenReportCSV extends ReportManager
{
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param User $user
	 */
	public function KostenartenReportCSV(DBManager $db, User $user, CustomerManager $customerManager, AddressManager $addressManager, UserManager $userManager, RSKostenartManager $kostenartManager, ErrorManager $errorManager, ExtendedLanguageManager $languageManager)
	{
		parent::__construct($db, $user, $customerManager, $addressManager, $userManager, $kostenartManager, $errorManager, $languageManager, get_class($this), "Berichte > Kostenarten CSV");
		$this->action_button_text = "CSV-Datei herunterladen";
	}

	/**
	 * Return the filter elements
	 * @return FormElement[]
	 */
	public function GetFilterElements()
	{
		$elements = Array();
		return $elements;
	}
	
	/**
	 * Return if the specified format is supported
	 * @param int $format
	 * @return boolean
	 */
	public function IsFormatSupported($format)
	{
		if ($format==ReportManager::REPORT_FORMAT_CSV) return true;
		return false;
	}
	
	/**
	 * Prepare the report data for output
	 * @return array
	 */
	private function GetReportData()
	{
		if (!isset($_POST["show"]))
		{
			return Array();
		}
		$data = $this->kostenartManager->GetBezeichnungTeilflächeAndFmsKostenart('', 0, 0);
		return $data;
	}
	
	/**
	 * Output the report as HTML
	 * @return bool
	 */
	public function PrintHtml()
	{
		?><input type="hidden" name="print" value="<?=ReportManager::REPORT_FORMAT_CSV;?>" /><?
		return true;
	}
	
	
	/**
	 * Output the report in the specified format
	 * @param int $format
	 * @return bool
	 */
	public function StreamAsFormat($format)
	{
		if ($format==ReportManager::REPORT_FORMAT_CSV) return $this->StreamAsCsv();
		return false;
	}
	
	/**
	 * Stream report as CSV-File
	 */
	private function StreamAsCsv()
	{
		$sep = ";";
		$endline = "\n";
		$myCSVString = "";
		$data = $this->GetReportData();
		foreach($data as $dateset)
		{
			$rsKostenart = RSKostenartManager::GetKostenartByPkey($this->db, $dateset['kostenartRS']);
			$tmp = Array('Kostenart Abrechnung' => $dateset['bezeichnungKostenart'], 'Kostenart SFM' => $rsKostenart!=null ? $rsKostenart->GetName() : ' ');
			if ($myCSVString=="") $myCSVString.=implode($sep, array_keys($tmp)).$endline;
			$myCSVString.=implode($sep, array_values($tmp)).$endline;
		}
		$myCSVString = utf8_decode($myCSVString);
		// stream file to browser...
		header('HTTP/1.1 200 OK');
		header('Status: 200 OK');
		header('Accept-Ranges: bytes');
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Transfer-Encoding: Binary');
		header("Content-type: application/octetstream; charset=UTF-8");
		header("Content-Disposition: attachment; filename=\"Kuerzungsbetraege.csv\"");
		header("Content-Length: ".(string)strlen($myCSVString));
		echo $myCSVString;
		exit;
	}
	
}
?>