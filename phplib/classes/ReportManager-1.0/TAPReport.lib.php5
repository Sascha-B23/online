<?php
require_once 'ReportManager.lib.php5';
/**
 * Implementation of report 'Teilabrechnungspositionen (TAP)'
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class TAPReport extends ReportManager
{
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param User $user
	 */
	public function TAPReport(DBManager $db, User $user, CustomerManager $customerManager, AddressManager $addressManager, UserManager $userManager, RSKostenartManager $kostenartManager, ErrorManager $errorManager, ExtendedLanguageManager $languageManager)
	{
		parent::__construct($db, $user, $customerManager, $addressManager, $userManager, $kostenartManager, $errorManager, $languageManager, get_class($this), "Berichte > Teilabrechnungspositionen");
		$this->action_button_text = "CSV-Datei herunterladen";
	}

	/**
	 * Return the default setting
	 * @return array 
	 */
	protected function GetDefaultSetting()
	{
		$defalutSettings = Array();
		$defalutSettings["group"] = "all";
		$defalutSettings["company"] = "all";
		$defalutSettings["location"] = "all";
		$defalutSettings["shop"] = "all";
		$defalutSettings["abrechnugsjahre"] = "all";
		// everything is selected
//		$defalutSettings["kostenart"] = "all";
		$defalutSettings["umlagefaehig"] = "all";
		// everything is selected
		$defalutSettings["naechsteMassnahme"] = "all";
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$defalutSettings["showArchievedStatus"] = -1;
		}
		else
		{
			$defalutSettings["showArchievedStatus"] = Schedule::ARCHIVE_STATUS_UPTODATE;
		}
		return $defalutSettings;
	}
	
	/**
	 * Prepare the var from POST for the report 
	 * @return boolean 
	 */
	protected function PrepareSettings()
	{
		if (isset($_POST["group"])) $this->SetValue("group", $_POST["group"]);
		if (isset($_POST["company"])) $this->SetValue("company", $_POST["company"]);
		if (isset($_POST["location"])) $this->SetValue("location", $_POST["location"]);
		if (isset($_POST["shop"])) $this->SetValue("shop", $_POST["shop"]);
		if (isset($_POST["abrechnugsjahre"])) $this->SetValue("abrechnugsjahre", $_POST["abrechnugsjahre"]);
		if (isset($_POST["kostenart"])) $this->SetValue("kostenart", $_POST["kostenart"]);
		if (isset($_POST["umlagefaehig"])) $this->SetValue("umlagefaehig", $_POST["umlagefaehig"]);
		if (isset($_POST["naechsteMassnahme"])) $this->SetValue("naechsteMassnahme", $_POST["naechsteMassnahme"]);
		if ($this->user->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_RSMITARBEITER)
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
		$elements = Array();
		$elements[] = $this->CreateFormGroups($this->GetValue("group"));
		$elements[] = $this->CreateFormCompanies($this->GetValue("company"));
		$elements[] = $this->CreateFormLocation($this->GetValue("location"));
		$elements[] = $this->CreateFormShop($this->GetValue("shop"));
		$elements[] = $this->CreateFormYears($this->GetValue("abrechnugsjahre"));
		$elements[] = $this->CreateFormKostenartUmlagefaehig($this->GetValue("umlagefaehig"));
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$elements[] = $this->CreateFormArchievedStatus($this->GetValue("showArchievedStatus"));
			$elements[] = new BlankElement();
		}
		$elements[] = $this->CreateFormKostenarten();
		$elements[] = $this->CreateFormNaechsteMassnahme(Array());

		return $elements;
	}
	
	/**
	 * Return the column filters
	 * @return array 
	 */
	public function GetColumnFilters()
	{
		$columnFilter = Array();
		return $columnFilter;
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
		$data = Array();
		if (isset($_POST["show"]))
		{
			$query = $this->BuildSqlQuery();
			$data = $this->db->SelectAssoc($query);
		}
		//print_r($data);
		return $data;
	}
	
	/**
	 * Build the SQL-Query string
	 * @return string
	 */
	private function BuildSqlQuery()
	{
		$query = "SELECT ";
		$query.= Teilabrechnungsposition::TABLE_NAME.".*, ";
		$query.= Teilabrechnung::TABLE_NAME.".pkey AS ta_pkey, ".Teilabrechnung::TABLE_NAME.".bezeichnung AS ta_bezeichnung, erfasst, ";
		$query.= AbrechnungsJahr::TABLE_NAME.".jahr, ";
		$query.= Contract::TABLE_NAME.".pkey AS contract_pkey, ";
		$query.= CShop::TABLE_NAME.".name AS shop_name, RSID,  ";
		$query.= CLocation::TABLE_NAME.".name AS location_name, ";
		$query.= CCompany::TABLE_NAME.".name AS company_name, ";
		$query.= ProcessStatus::TABLE_NAME.".processStatusGroup_rel AS processGroupId, ".ProcessStatusGroup::TABLE_NAME.".name AS processGroupName, ";
		$query.= ProcessStatus::TABLE_NAME.".scheduleComment, ".ProcessStatus::TABLE_NAME.".archiveStatus, ".ProcessStatus::TABLE_NAME.".currentStatus, ";
		$query.= CGroup::TABLE_NAME.".name AS group_name ";
		$query.= "FROM ".Teilabrechnungsposition::TABLE_NAME." ";
		$query.= "LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON ".Teilabrechnungsposition::TABLE_NAME.".teilabrechnung=".Teilabrechnung::TABLE_NAME.".pkey ";
		$query.= "LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".Teilabrechnung::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ";
		$query.= "LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey ";
		$query.= "LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey ";
		$query.= "LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
		$query.= "LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$query.= "LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$query.=" LEFT JOIN ".ProcessStatus::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".ProcessStatusGroup::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".processStatusGroup_rel=".ProcessStatusGroup::TABLE_NAME.".pkey";

		// build WHERE clause
		$whereClause = $this->BuildWhereClause();
		if ($whereClause!="") $query.=" WHERE ".$whereClause;
		// sort by year
		$query.=" ORDER BY RSID, contract_pkey, group_name, company_name, location_name, shop_name, jahr, ta_bezeichnung, bezeichnungTeilflaeche, bezeichnungKostenart";
		return $query;
	}
	
	/**
	 * Helper to add terms to the WHERE-clause
	 * @param string $whereClause
	 * @param string $postVarName
	 * @param string $columnName
	 * @param string $allValue
	 */
	private function AppendToWhereClause(&$whereClause, $postVarName, $columnName, $allValue="all")
	{
		if (isset($_POST[$postVarName]))
		{
			// if multi selection
			if(is_array($_POST[$postVarName]))
			{
				$whereClause.= ($whereClause!="" ? " AND " : "").$columnName." IN (".implode(',',$_POST[$postVarName]).")";
			}
			else
			{
				if ($_POST[$postVarName]!=$allValue && $_POST[$postVarName]==(int)$_POST[$postVarName])
				{
					$whereClause.=($whereClause!="" ? " AND " : "").$columnName."=".(int)$_POST[$postVarName];
				}
			}
		}
	}
	
	/**
	 * Build the db WHERE-clause
	 * @return string
	 */
	private function BuildWhereClause()
	{
		$whereClause = "";
		$this->AppendToWhereClause($whereClause, "group", CGroup::TABLE_NAME.".pkey");
		$this->AppendToWhereClause($whereClause, "company", CCompany::TABLE_NAME.".pkey");
		$this->AppendToWhereClause($whereClause, "location", CLocation::TABLE_NAME.".pkey");
		$this->AppendToWhereClause($whereClause, "shop", CShop::TABLE_NAME.".pkey");
		$this->AppendToWhereClause($whereClause, "abrechnugsjahre", AbrechnungsJahr::TABLE_NAME.".jahr");
		$this->AppendToWhereClause($whereClause, "kostenart", Teilabrechnungsposition::TABLE_NAME.".kostenartRS");
		$this->AppendToWhereClause($whereClause, "umlagefaehig", Teilabrechnungsposition::TABLE_NAME.".umlagefaehig");
		$this->AppendToWhereClause($whereClause, "naechsteMassnahme", ProcessStatus::TABLE_NAME.".currentStatus");
		$this->AppendToWhereClause($whereClause, "showArchievedStatus", ProcessStatus::TABLE_NAME.".archiveStatus", "-1");
		return $whereClause;
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
		global $NKM_TEILABRECHNUNGSPOSITION_EINHEIT;
		$kostenarten = $this->kostenartManager->GetKostenarten("", "name", 0, 0, 0);
		$kostenartenPkeyToNameMap = Array();
		foreach ($kostenarten as $kostenart)
		{
			$kostenartenPkeyToNameMap[$kostenart->GetPKey()] = $kostenart->GetName();
		}
		
		$sep = ";";
		$endline = "\n";
		$myCSVString = "";
		
		// Export
		$columnDefinition = Array(	0 => Array("column_name" => "RSID", "caption" => CShop::GetAttributeName($this->languageManager, 'RSID')),
									1 => Array("column_name" => "contract_pkey", "caption" => "Vertrags-ID", "postText" => "V"),
									2 => Array("column_name" => "ta_pkey", "caption" => "TA-ID", "postText" => "TA"),
									3 => Array("column_name" => "pkey", "caption" => "TAP-ID", "postText" => "TAP"),
			
			
									4 => Array("column_name" => "group_name", "caption" => CGroup::GetAttributeName($this->languageManager, 'name')),
									5 => Array("column_name" => "company_name", "caption" => CCompany::GetAttributeName($this->languageManager, 'name')),
									6 => Array("column_name" => "location_name", "caption" => CLocation::GetAttributeName($this->languageManager, 'name')),
									7 => Array("column_name" => "shop_name", "caption" => CShop::GetAttributeName($this->languageManager, 'name')),
									8 => Array("column_name" => "jahr", "caption" => AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr')),
									9 => Array("column_name" => "currentStatus", "caption" => "Nächste Maßnahme"),
									10 => Array("column_name" => "archiveStatus", "caption" => "Status Datenbereinigung"),
									11 => Array("column_name" => "ta_bezeichnung", "caption" => "Bezeichnung Abrechnung"),
									// TAP
									12 => Array("column_name" => "bezeichnungTeilflaeche", "caption" => "Flächenbezeichnung"),
									13 => Array("column_name" => "bezeichnungKostenart", "caption" => "Kostenart Abrechnung"),

									14 => Array("column_name" => "kostenartRS", "caption" => "Kostenart SFM"),
                                    15 => Array("column_name" => "pauschale", "caption" => "Pauschale"),
									16 => Array("column_name" => "gesamteinheiten", "caption" => "Gesamteinheiten Umlageschlüssel", "type" => "float"),
									17 => Array("column_name" => "gesamteinheitenEinheit", "caption" => "Gesamteinheiten Umlageschlüssel Einheiten", "type" => "tapunit"),
									18 => Array("column_name" => "einheitKunde", "caption" => "Einheiten Umlageschlüssel Kunde", "type" => "float"),
									19 => Array("column_name" => "einheitKundeEinheit", "caption" => "Einheiten Umlageschlüssel Kunde Einheiten", "type" => "tapunit"),
									20 => Array("column_name" => "gesamtbetrag", "caption" => "Gesamtkosten Kostenart", "type" => "float"),

									21 => Array("column_name" => "betragKunde", "caption" => "Kostenanteil Kunde", "type" => "float"),
									22 => Array("column_name" => "umlagefaehig", "caption" => "Kostenart Umlagefähig"),
									23 => Array("column_name" => "erfasst", "caption" => "Vollständig erfasst"),

								);
		
		// print column header
		foreach ($columnDefinition as $column) 
		{
			$myCSVString .= str_replace(";", ",", $column["caption"]).$sep;
		}
		$myCSVString.=$endline;
		// print column data
		$data = $this->GetReportData();
		if (is_array($data))
		{
			// loop through all rows
			foreach ($data as $row) 
			{
				// print all columns of the row
				foreach ($columnDefinition as $column) 
				{
					if ($column["type"]=="float") $row[$column["column_name"]]=str_replace(".", ",", $row[$column["column_name"]]);
					if ($column["type"]=="tapunit") $row[$column["column_name"]]=$NKM_TEILABRECHNUNGSPOSITION_EINHEIT[(int)$row[$column["column_name"]]]["long"];
					// Kostenart
					if ($column["column_name"]=="kostenartRS")
					{
						$row[$column["column_name"]] = $kostenartenPkeyToNameMap[(int)$row[$column["column_name"]]];
						if ($row[$column["column_name"]]=="") $row[$column["column_name"]] = "-";
					}
					if ($column["column_name"]=="umlagefaehig")
					{
						$row[$column["column_name"]] = ($row[$column["column_name"]]==2 ? "Nein" : ($row[$column["column_name"]]==1 ? "Ja" : "nicht festgelegt"));
					}
					if ($column["column_name"]=="erfasst")
					{
						$row[$column["column_name"]] = ($row[$column["column_name"]]==1 ? "Ja" : "Nein");
					}
                    if ($column["column_name"]=="pauschale")
                    {
                        if ($row[$column["column_name"]]==1)
                        {
                            $row["gesamteinheiten"] = "";
                            $row["gesamteinheitenEinheit"] = "";
                            $row["einheitKunde"] = "";
                            $row["einheitKundeEinheit"] = "";
                            $row["gesamtbetrag"] = "";
                        }
                        $row[$column["column_name"]] = ($row[$column["column_name"]]==1 ? "Ja" : "Nein");

                    }
					if ($column["column_name"]=="currentStatus")
					{
						$row[$column["column_name"]] = WorkflowManager::GetStatusName($this->db, $this->user, $row[$column["column_name"]]);
					}
					if ($column["column_name"]=="archiveStatus")
					{
						switch($row[$column["column_name"]])
						{
							case Schedule::ARCHIVE_STATUS_ARCHIVED:
								$row[$column["column_name"]] = "Archiviert";
								break;
							case Schedule::ARCHIVE_STATUS_UPDATEREQUIRED:
								$row[$column["column_name"]] = "Aktuell (noch auf Stand zu bringen)";
								break;
							case Schedule::ARCHIVE_STATUS_UPTODATE:
								$row[$column["column_name"]] = "Aktuell (bereits auf Stand)";
								break;
						}
					}
					$myCSVString .= str_replace(";", ",", $column["postText"].$row[$column["column_name"]]).$sep;
				}
				$myCSVString .= $endline;
			}
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
		header("Content-Disposition: attachment; filename=\"Teilabrechnungspositionen.csv\"");
		header("Content-Length: ".(string)strlen($myCSVString));
		echo $myCSVString;
		exit;
	}
	
}
?>