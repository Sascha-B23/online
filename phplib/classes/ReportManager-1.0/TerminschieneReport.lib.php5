<?php
require_once 'ReportManager.lib.php5';
/**
 * Implementation of report 'Kundenstandorte'
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class TerminschieneReport extends ReportManager
{

	/**
	 * @var ProcessStatus
	 */
	protected $processStatus = null;

	/**
	 * Constructor
	 * @param DBManager $db
	 * @param User $user
	 */
	public function TerminschieneReport(DBManager $db, User $user, CustomerManager $customerManager, AddressManager $addressManager, UserManager $userManager, RSKostenartManager $kostenartManager, ErrorManager $errorManager, ExtendedLanguageManager $languageManager)
	{
		parent::__construct($db, $user, $customerManager, $addressManager, $userManager, $kostenartManager, $errorManager, $languageManager, "terminschieneReport", "Berichte > Terminschiene");
	}
	
	/**
	 * Return the default setting
	 * @return array 
	 */
	protected function GetDefaultSetting()
	{
		$defalutSettings = Array();
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$defalutSettings["showArchievedStatus"] = -1;
		}
		else
		{
			$defalutSettings["showArchievedStatus"] = Schedule::ARCHIVE_STATUS_UPTODATE;
		}
		$defalutSettings["group"] = -1;
		$defalutSettings["company"] = -1;
		$defalutSettings["location"] = -1;	
		$defalutSettings["shop"] = -1;	
		$defalutSettings["contract"] = -1;	
		$defalutSettings["year"] = -1;	
		return $defalutSettings;
	}
	
	/**
	 * Prepare the var from POST for the report 
	 * @return boolean 
	 */
	protected function PrepareSettings()
	{
		if (isset($_POST["group"])) $this->SetValue("group", (int)$_POST["group"]);
		if (isset($_POST["company"])) $this->SetValue("company", (int)$_POST["company"]);
		if (isset($_POST["location"])) $this->SetValue("location", (int)$_POST["location"]);
		if (isset($_POST["shop"])) $this->SetValue("shop", (int)$_POST["shop"]);
		if (isset($_POST["contract"])) $this->SetValue("contract", (int)$_POST["contract"]);
		if (isset($_POST["year"])) $this->SetValue("year", (int)$_POST["year"]);
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			if (isset($_POST["showArchievedStatus"])) $this->SetValue("showArchievedStatus", (int)$_POST["showArchievedStatus"]);
		}
		else
		{
			// Customers only can see uptodate prozesses
			$this->SetValue("showArchievedStatus", Schedule::ARCHIVE_STATUS_UPTODATE);
		}
		return true;
	}

	
	/**
	 * Return the filter elements
	 * @return FormElement[]
	 */
	public function GetFilterElements()
	{
		global $SHARED_HTTP_ROOT;
		// prepare filters
		$filterData = $this->PrepareFilters((int)$this->GetValue("group"), (int)$this->GetValue("company"), (int)$this->GetValue("location"), (int)$this->GetValue("shop"), (int)$this->GetValue("contract"), (int)$this->GetValue("year"));
		// create FormElement-objects
		$elements = Array();
		$error = Array();
		// groups
		$elements[] = new DropdownElement("group", "Gruppe", $filterData['groups']['selected'], true, $error["group"], $filterData['groups']['options'], false);
		$elements[count($elements)-1]->SetWidth(200);
		// companies
		$elements[] = new DropdownElement("company", "Firma",  $filterData['companies']['selected'], true, $error["company"], $filterData['companies']['options'], false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=1&param01='+$('group').options[$('group').selectedIndex].value+'", "$('group').onchange=function(){%REQUESTCALL%;};", "company") );
		$elements[count($elements)-1]->SetWidth(200);
		// locations
		$elements[] = new DropdownElement("location", "Standort", $filterData['locations']['selected'], true, $error["location"], $filterData['locations']['options'], false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=2&param01='+$('company').options[$('company').selectedIndex].value+'", "$('company').onchange=function(){%REQUESTCALL%;};", "location"));
		$elements[count($elements)-1]->SetWidth(200);
		// shop
		$elements[] = new DropdownElement("shop", "Laden", $filterData['shop']['selected'], true, $error["shop"], $filterData['shop']['options'], false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=3&param01='+$('location').options[$('location').selectedIndex].value+'", "$('location').onchange=function(){%REQUESTCALL%;};", "shop"));
		$elements[count($elements)-1]->SetWidth(200);
		// contract
		$elements[] = new DropdownElement("contract", "Vertrag", $filterData['contract']['selected'], true, $error["contract"], $filterData['contract']['options'], false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=4&param01='+$('shop').options[$('shop').selectedIndex].value+'", "$('shop').onchange=function(){%REQUESTCALL%;};", "contract"));
		$elements[count($elements)-1]->SetWidth(200);
		// year
		$elements[] = new DropdownElement("year", "Abrechnungsjahr", $filterData['year']['selected'], true, $error["year"], $filterData['year']['options'], false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=10&param01='+$('contract').options[$('contract').selectedIndex].value+'", "$('contract').onchange=function(){%REQUESTCALL%;};", "year"));
		$elements[count($elements)-1]->SetWidth(200);
		// process status
		if( $this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE )
		{
			$options = Array();
			$options[] = Array("name" => "Alle", "value" => -1);
			$options[] = Array("name" => "Archivierte", "value" => Schedule::ARCHIVE_STATUS_ARCHIVED);
			$options[] = Array("name" => "Aktuelle Prozesse (noch auf Stand zu bringen)", "value" => Schedule::ARCHIVE_STATUS_UPDATEREQUIRED);
			$options[] = Array("name" => "Aktuelle Prozesse (bereits auf Stand)", "value" => Schedule::ARCHIVE_STATUS_UPTODATE);
			$elements[] = new DropdownElement("showArchievedStatus", "Bearbeitungsstatus", (int)$this->GetValue("showArchievedStatus"), true, $error["showArchievedStatus"], $options, false);
			$elements[count($elements)-1]->SetWidth(200);
		}
		else
		{
			$elements[] = new BlankElement();
		}
		return $elements;
	}

	/**
	 * @param ProcessStatus $prozess
	 */
	public function SetProcessStatus(ProcessStatus $processStatus)
	{
		$this->processStatus = $processStatus;
	}

	/**
	 * Output the report as HTML
	 * @return bool
	 */
	public function PrintHtml()
	{
		if ($this->processStatus!=null)
		{
			$process = $this->processStatus;
		}
		else
		{
			$yearToShow = ($this->GetValue("year")=="all" ? -1 : (int)$this->GetValue("year"));
			$_POST["showArchievedStatus"] = $this->GetValue("showArchievedStatus");
		}
		include("template_terminschiene.inc.php5");
		return true;
	}
	
}
?>