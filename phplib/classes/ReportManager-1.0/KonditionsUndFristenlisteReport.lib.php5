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
class KonditionsUndFristenlisteReport extends ReportManager
{
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param User $user
	 */
	public function KonditionsUndFristenlisteReport(DBManager $db, User $user, CustomerManager $customerManager, AddressManager $addressManager, UserManager $userManager, RSKostenartManager $kostenartManager, ErrorManager $errorManager, ExtendedLanguageManager $languageManager)
	{
		parent::__construct($db, $user, $customerManager, $addressManager, $userManager, $kostenartManager, $errorManager, $languageManager, "konditionsUndFristenlisteReport", "Berichte > Konditions- und Fristenliste");
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
		$filterData = $this->PrepareFilters((int)$this->GetValue("group"), (int)$this->GetValue("company"));
		// create FormElement-objects
		$elements = Array();
		$error = Array();
		// groups
		$elements[] = new DropdownElement("group", "Gruppe", $filterData['groups']['selected'], true, $error["group"], $filterData['groups']['options'], false);
		$elements[count($elements)-1]->SetWidth(200);
		// companies
		$elements[] = new DropdownElement("company", "Firma",  $filterData['companies']['selected'], true, $error["company"], $filterData['companies']['options'], false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=1&param01='+$('group').options[$('group').selectedIndex].value+'", "$('group').onchange=function(){%REQUESTCALL%;};", "company") );
		$elements[count($elements)-1]->SetWidth(200);

		return $elements;
	}

	/**
	 * Output the report as HTML
	 * @return bool
	 */
	public function PrintHtml()
	{
		global $DOMAIN_HTTP_ROOT;
		$currentCompany = null;
		if (isset($_POST["company"]) && is_numeric($_POST["company"]))
		{
			$currentCompany = new CCompany($this->db);
			if ($currentCompany->Load((int)$_POST["company"], $this->db)!==true) $currentCompany = null;
		}
		// Firma geladen?
		if ($currentCompany!=null)
		{
			// Ist der aktive Benutzer  berechtigt, diese Firma einzusehen?
			if ($currentCompany->HasUserAccess($this->user, $this->db))
			{

				echo '<table border="0" cellpadding="0" cellspacing="0" style="width:900px;"><tbody><tr><td width="20">&nbsp;</td><td width="287">';

				if ($konditionsUndFristenliste = $currentCompany->GetKonditionsUndFristenliste()){
					echo '<a href="'.$DOMAIN_HTTP_ROOT.'templates/download_file.php5?'.SID.'&code='.$_SESSION['fileDownloadManager']->AddDownloadFile($konditionsUndFristenliste).'&timestamp='.time().'">Download der Konditions- und Fristenliste</a>';
				} else{
					// Show Message
					echo 'Aktuell ist keine Konditions- und Fristenliste hinterlegt.';
				}
				echo '</td><td width="auto">&nbsp;</td></tr></tbody></table>';
				//$tabs = new TabManager(new LtLocationTabData($this->db, $this->languageManager, $currentLocation));
				//$tabs->PrintData();
			}
			else
			{
				// Benutzer darf diese Seite nicht einsehen
				$this->errorManager->ShowError("KonditionsUndFristenlisteReport.lib.php5", "Zugriff auf Bericht für Benutzer '".$this->user->GetUserName()."' verweigert<br/>\n");
				return false;
			}
		}
		return true;
	}

}
?>