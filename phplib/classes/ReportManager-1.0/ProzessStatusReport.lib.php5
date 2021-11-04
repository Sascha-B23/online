<?php
require_once 'ReportManager.lib.php5';
/**
 * Implementation of report 'Standortvergleich Prozessstatus'
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class ProzessStatusReport extends ReportManager
{
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param User $user
	 */
	public function ProzessStatusReport(DBManager $db, User $user, CustomerManager $customerManager, AddressManager $addressManager, UserManager $userManager, RSKostenartManager $kostenartManager, ErrorManager $errorManager, ExtendedLanguageManager $languageManager)
	{
		parent::__construct($db, $user, $customerManager, $addressManager, $userManager, $kostenartManager, $errorManager, $languageManager, get_class($this), "Berichte > Standortvergleich Prozessstatus");
	}

	/**
	 * Return the default setting
	 * @return array 
	 */
	protected function GetDefaultSetting()
	{	
		$defaultSettings = Array();
		$defaultSettings["group"] = "all";
		$defaultSettings["showErsteinsparung"] = "all";
		$defaultSettings["addressGroup"] = "all";
		$defaultSettings["addressGroupTrustee"] = "all";
		$defaultSettings["naechsteMassnahme"] = "all";
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$defaultSettings["showRealisiert"] = "0";
			$defaultSettings["showArchievedStatus"] = Schedule::ARCHIVE_STATUS_UPTODATE;
		}
		else
		{
			$defaultSettings["showRealisiert"] = "-1";
			$defaultSettings["showArchievedStatus"] = Schedule::ARCHIVE_STATUS_UPTODATE;
		}

		// column filter settings
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$defaultSettings["cb_mvBeginn"] = true;
			$defaultSettings["cb_mvEnde"] = true;
			$defaultSettings["cb_archiveStatus"] = true;
			$defaultSettings["cb_status"] = true;
			$defaultSettings["cb_group"] = true;
			$defaultSettings["cb_country"] = true;
			$defaultSettings["cb_currency"] = true;
			$defaultSettings["cb_contractID"] = true;
			$defaultSettings["cb_location"] = true;
			$defaultSettings["cb_shop"] = true;
			$defaultSettings["cb_customer_id"] = true;
			$defaultSettings["cb_year"] = true;
			$defaultSettings["cb_group_thruste"] = true;
			$defaultSettings["cb_fms_user"] = true;
			$defaultSettings["cb_ws_maximalforderung"] = true;
			$defaultSettings["cb_ws_entgegenkommen_standard"] = true;
			$defaultSettings["cb_ws_entgegenkommen_zusaetzlich"] = true;
			$defaultSettings["cb_ws_loesungsvorschlag"] = true;
			$defaultSettings["cb_ws_vermieterangebot"] = true;
			$defaultSettings["cb_fms_user_process"] = true;
			$defaultSettings["cb_fms_leader"] = true;
			$defaultSettings["cb_company"] = true;
			$defaultSettings["cb_thruste"] = true;
			$defaultSettings["cb_group_owner"] = true;
			$defaultSettings["cb_owner"] = true;
			$defaultSettings["cb_advocate"] = true;
			$defaultSettings["cb_customer_user"] = true;
			$defaultSettings["cb_fms_id"] = true;
			$defaultSettings["cb_datum_beauftragung"] = true;
			$defaultSettings["cb_datum_abrechnung"] = true;
			$defaultSettings["cb_frist_belegeinsicht"] = true;
			$defaultSettings["cb_frist_widerspruch"] = true;
			$defaultSettings["cb_frist_widerspruch_sfm"] = true;
			$defaultSettings["cb_frist_prozessstatus"] = true;
			$defaultSettings["cb_schedule_comment"] = true;
			$defaultSettings["cb_process_id"] = true;
			$defaultSettings["cb_processgroup_id"] = true;
			$defaultSettings["cb_procedure"] = false;
			$defaultSettings["cb_procedure_type"] = false;
			$defaultSettings["cb_processgroup_name"] = true;
			$defaultSettings["cb_group_schedule_comment"] = true;
			$defaultSettings["cb_form"] = true;
			$defaultSettings["cb_stages_points_to_be_clarified"] = true;
			$defaultSettings["cb_referenceNumber"] = true;
			$defaultSettings["cb_sub_procedure"] = true;
			$defaultSettings["cb_abrechnungszeitraumVon"] = true;
			$defaultSettings["cb_abrechnungszeitraumBis"] = true;
		}
		else
		{
			$defaultSettings["cb_status"] = true;
			$defaultSettings["cb_group"] = true;
			$defaultSettings["cb_country"] = true;
			$defaultSettings["cb_currency"] = true;
			$defaultSettings["cb_location"] = true;
			$defaultSettings["cb_shop"] = true;
			$defaultSettings["cb_customer_id"] = true;
			$defaultSettings["cb_year"] = true;
			$defaultSettings["cb_group_thruste"] = true;
			$defaultSettings["cb_ws_maximalforderung"] = true;
			$defaultSettings["cb_ws_entgegenkommen_standard"] = true;
			$defaultSettings["cb_ws_entgegenkommen_zusaetzlich"] = true;
			$defaultSettings["cb_ws_loesungsvorschlag"] = true;
			$defaultSettings["cb_ws_vermieterangebot"] = true;
			$defaultSettings["cb_company"] = true;
			$defaultSettings["cb_datum_beauftragung"] = true;
			$defaultSettings["cb_datum_abrechnung"] = true;
		}
		return $defaultSettings;
	}
	
	/**
	 * Prepare the var from POST for the report 
	 * @return boolean 
	 */
	protected function PrepareSettings()
	{
		if (isset($_POST["group"])) $this->SetValue("group", $_POST["group"]);
		if (isset($_POST["showRealisiert"])) $this->SetValue("showRealisiert", $_POST["showRealisiert"]);
		if (isset($_POST["showErsteinsparung"])) $this->SetValue("showErsteinsparung", $_POST["showErsteinsparung"]);
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			if (isset($_POST["showArchievedStatus"])) $this->SetValue("showArchievedStatus", (int)$_POST["showArchievedStatus"]);
			if (isset($_POST["addressGroup"])) $this->SetValue("addressGroup", $_POST["addressGroup"]);
			if (isset($_POST["addressGroupTrustee"])) $this->SetValue("addressGroupTrustee", $_POST["addressGroupTrustee"]);
		}
		else
		{
			// Customers only can see uptodate prozesses
			$this->SetValue("showArchievedStatus", Schedule::ARCHIVE_STATUS_UPTODATE);
			$this->SetValue("addressGroup", "all");
			$this->SetValue("addressGroupTrustee", "all");
		}
		if (isset($_POST["naechsteMassnahme"])) $this->SetValue("naechsteMassnahme", $_POST["naechsteMassnahme"]);

		// Spalten-Konfiguration setzen
		if (isset($_POST["columnFilterSend"]))
		{
			$columnFilter = $this->GetColumnFilters();
			for ($a=0; $a<count($columnFilter); $a++)
			{
				// Neue Auswahl setzen
				$this->SetValue($columnFilter[$a]["id"], ($_POST[$columnFilter[$a]["id"]]!="" ? true : false));
			}
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
		$elements[] = $this->CreateFormRealisiert($this->GetValue("showRealisiert"));
		$elements[] = $this->CreateFormErsteinsparung($this->GetValue("showErsteinsparung"));
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$elements[] = $this->CreateFormArchievedStatus($this->GetValue("showArchievedStatus"));
			$elements[] = $this->CreateFormAddressGroupMultiselect("addressGroup", "Gruppe Eigentümer", $this->GetValue("addressGroup"));
			$elements[] = $this->CreateFormAddressGroupMultiselect("addressGroupTrustee", "Gruppe Verwalter", $this->GetValue("addressGroupTrustee"), Array("name" => "Ohne Zuweisung", "value" => "-1"));
		}
		$elements[] = $this->CreateFormNaechsteMassnahme(Array(), $this->GetValue("naechsteMassnahme"), Array(7, 26));
		return $elements;
	}
	
	/**
	 * Return the column filters
	 * @return array 
	 */
	public function GetColumnFilters()
	{
		// Spaltenkonfiguration
		$columnFilter = Array();
		$columnFilter[]=array("id" => "cb_fms_id", "caption" => CShop::GetAttributeName($this->languageManager, 'RSID'), "sortby" => "RSID", "sorttype" => "str", "visible" => false, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=Array("id" => "cb_contractID", "caption" => "Vertrags-ID", "sortby" => "contractID", "sorttype" => "num", "convert" => "contract_id", "visible" => true, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_process_id", "caption" => ProcessStatus::GetAttributeName($this->languageManager, 'processStatusId'), "sortby" => "prozessstatusPkey", "convert" => "process_id", "sorttype" => "num", "visible" => true, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_processgroup_id", "caption" => ProcessStatusGroup::GetAttributeName($this->languageManager, 'id'), "sortby" => "processGroupId", "convert" => "processgroup_id", "sorttype" => "num", "visible" => true, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_procedure", "caption" => "Vorgang", "sortby" => "prozessstatusPkey", "convert" => "procedure", "sorttype" => "num", "visible" => true, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_procedure_type", "caption" => "Vorgangstyp", "sortby" => "prozessstatusPkey", "convert" => "procedureType", "sorttype" => "num", "visible" => true, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=Array("id" => "cb_location", "caption" => CLocation::GetAttributeName($this->languageManager, 'name'), "sortby" => "locationName", "sorttype" => "str", "visible" => true);
		$columnFilter[]=array("id" => "cb_year", "caption" => AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr'), "sortby" => "abrechnungJahr", "sorttype" => "num", "visible" => true);
		$columnFilter[]=array("id" => "cb_fms_user", "caption" => "Nebenkostenanalyst SFM", "sortby" => "cPersonRS", "sorttype" => "str", "visible" => false, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_fms_user_process", "caption" => "Vermieterverhandlung SFM", "sortby" => "currentResponsibleRSUser", "sorttype" => "str", "visible" => false, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_fms_leader", "caption" => "Nebenkostenanalyst SFM", "sortby" => "cPersonFmsLeader", "sorttype" => "str", "visible" => true, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=Array("id" => "cb_ws_maximalforderung", "caption" => "Maximalforderung", "sortby" => "maximalforderung", "sorttype" => "num", "visible" => true, "convert" => "currency");
		$columnFilter[]=Array("id" => "cb_ws_entgegenkommen_standard", "caption" => "Entgegenkommen Standard", "sortby" => "entgegenkommen_standard", "sorttype" => "num", "visible" => true, "convert" => "currency");
		$columnFilter[]=Array("id" => "cb_ws_entgegenkommen_zusaetzlich", "caption" => "Entgegenkommen zusaetzlich", "sortby" => "entgegenkommen_zusaetzlich", "sorttype" => "num", "visible" => true, "convert" => "currency");
		$columnFilter[]=Array("id" => "cb_ws_loesungsvorschlag", "caption" => "Lösungsvorschlag", "sortby" => "loesungsvorschlag", "sorttype" => "num", "visible" => true, "convert" => "currency");
		$columnFilter[]=Array("id" => "cb_ws_vermieterangebot", "caption" => "Vermieterangebot", "sortby" => "vermieterangebot", "sorttype" => "num", "visible" => true, "convert" => "currency");
		$columnFilter[]=array("id" => "cb_form", "caption" => ProcessStatus::GetAttributeName($this->languageManager, 'form'), "sortby" => "form", "sorttype" => "num", "visible" => false, "convert" => "form", "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_stages_points_to_be_clarified", "caption" => ProcessStatus::GetAttributeName($this->languageManager, 'stagesPointsToBeClarified'), "sortby" => "stagesPointsToBeClarified", "sorttype" => "num", "visible" => false, "convert" => "stagesPointsToBeClarified", "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_referenceNumber", "caption" => ProcessStatus::GetAttributeName($this->languageManager, 'referenceNumber'), "sortby" => "referenceNumber", "sorttype" => "str", "visible" => false, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=Array("id" => "cb_datum_abrechnung", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'datum'), "sortby" => "datumAbrechnung", "sorttype" => "num", "visible" => false, "convert" => "date");
		$columnFilter[]=array("id" => "cb_frist_belegeinsicht", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'fristBelegeinsicht'), "sortby" => "fristBelegeinsicht", "sorttype" => "num", "visible" => false, "convert" => "date", "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_frist_widerspruch", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'fristWiderspruch'), "sortby" => "fristWiderspruch", "sorttype" => "num", "visible" => false, "convert" => "date", "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_frist_widerspruch_sfm", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'fristZahlung'), "sortby" => "fristZahlung", "sorttype" => "num", "visible" => false, "convert" => "date", "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_status", "caption" => WorkflowStatus::GetAttributeName($this->languageManager, 'currentStatus'), "sortby" => "currentStatus", "sorttype" => "num", "visible" => true, "convert" => "status");
		$columnFilter[]=array("id" => "cb_processgroup_name", "caption" => ProcessStatusGroup::GetAttributeName($this->languageManager, 'name'), "sortby" => "processGroupName", "sorttype" => "str", "visible" => true, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_group_thruste", "caption" => "Gruppe Verwalter", "sortby" => "gruppeVerwalter", "sorttype" => "str", "visible" => true);
		$columnFilter[]=array("id" => "cb_thruste", "caption" => "Firma Verwalter", "sortby" => "verwalter", "sorttype" => "str", "visible" => false, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_group_owner", "caption" => "Gruppe Eigentümer", "sortby" => "gruppeEigentuemer", "sorttype" => "str", "visible" => false, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_owner", "caption" => "Firma Eigentümer", "sortby" => "eigentuemer", "sorttype" => "str", "visible" => false, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_advocate", "caption" => "Firma Anwalt", "sortby" => "anwalt", "sorttype" => "str", "visible" => false, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_frist_prozessstatus", "caption" => WorkflowStatus::GetAttributeName($this->languageManager, 'deadline'), "sortby" => "deadlineProcess", "sorttype" => "num", "visible" => false, "convert" => "date", "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_sub_procedure", "caption" => ProcessStatus::GetAttributeName($this->languageManager, 'subProcedure'), "sortby" => "subProcedure", "sorttype" => "str", "visible" => false, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_schedule_comment", "caption" => Schedule::GetAttributeName($this->languageManager, 'scheduleComment'), "sortby" => "scheduleComment", "sorttype" => "str", "visible" => false, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_group_schedule_comment", "caption" => Schedule::GetAttributeName($this->languageManager, 'scheduleCommentGroup'), "sortby" => "groupScheduleComment", "sorttype" => "str", "visible" => false, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_archiveStatus", "caption" => "Status Datenbereinigung", "sortby" => "archiveStatus", "sorttype" => "num", "convert" => "archievedStatus", "visible" => true, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_group", "caption" => CGroup::GetAttributeName($this->languageManager, 'name'), "sortby" => "groupName", "sorttype" => "str", "visible" => true);
		$columnFilter[]=array("id" => "cb_company", "caption" => "Firma Kunde", "sortby" => "companyName", "sorttype" => "str", "visible" => false);
		$columnFilter[]=Array("id" => "cb_country", "caption" => CLocation::GetAttributeName($this->languageManager, 'country'), "sortby" => "country", "sorttype" => "str", "visible" => true);
		$columnFilter[]=array("id" => "cb_shop", "caption" => CShop::GetAttributeName($this->languageManager, 'name'), "sortby" => "shopName", "sorttype" => "str", "visible" => true);
		$columnFilter[]=array("id" => "cb_customer_user", "caption" => "Anspr. Kunde", "sortby" => "cPersonCustomer", "sorttype" => "str", "visible" => false, "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_customer_id", "caption" => CShop::GetAttributeName($this->languageManager, 'internalShopNo'), "sortby" => "internalShopNo", "sorttype" => "str", "visible" => true);
		$columnFilter[]=Array("id" => "cb_currency", "caption" => "Währung", "sortby" => "currency", "sorttype" => "str", "visible" => true);
		$columnFilter[]=array("id" => "cb_mvBeginn", "caption" => "Mietvertragsbeginn", "sortby" => "mvBeginn", "sorttype" => "num", "visible" => false, "convert" => "date", "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_mvEnde", "caption" => "Mietvertragsende", "sortby" => "mvEnde", "sorttype" => "num", "visible" => false, "convert" => "date", "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=Array("id" => "cb_datum_beauftragung", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'auftragsdatumAbrechnung'), "sortby" => "auftragsdatumAbrechnung", "sorttype" => "num", "visible" => false, "convert" => "date");
		$columnFilter[]=array("id" => "cb_abrechnungszeitraumVon", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'abrechnungszeitraumVon'), "sortby" => "abrechnungszeitraumVon", "sorttype" => "num", "visible" => false, "convert" => "date", "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);
		$columnFilter[]=array("id" => "cb_abrechnungszeitraumBis", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'abrechnungszeitraumBis'), "sortby" => "abrechnungszeitraumBis", "sorttype" => "num", "visible" => false, "convert" => "date", "minUserGroup" => UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP);

		// Spaltenkonfiguration setzen
		for ($a=0; $a<count($columnFilter); $a++)
		{
			$columnFilter[$a]["visible"] = ($this->GetValue($columnFilter[$a]["id"])===true ? true : false);
			if (isset($columnFilter[$a]["minUserGroup"])) $columnFilter[$a]["hidden"] = $this->user->GetGroupBasetype($this->db)>=$columnFilter[$a]["minUserGroup"] ? false : true;
		}
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
		if ($format==ReportManager::REPORT_FORMAT_PDF) return true;
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
			for ($a=0; $a<count($data); $a++)
			{
                if ($data[$a]["prozessstatusPkey"]=="0") $data[$a]["prozessstatusPkey"] = "";
				if ($data[$a]["processGroupId"]=="0") $data[$a]["processGroupId"] = "";
				// Eigentümer
				if ($data[$a]["eigentuemer"]!=-1)
				{
					$adressTemp = null;
					if ($data[$a]["eigentuemerTyp"]==AddressBase::AM_CLASS_ADDRESSDATA) $adressTemp = AddressManager::GetAddressDataByPkey($this->db, (int)$data[$a]["eigentuemer"]);
					elseif ($data[$a]["eigentuemerTyp"]==AddressBase::AM_CLASS_ADDRESSCOMPANY) $adressTemp = AddressManager::GetAddressCompanyByPkey($this->db, (int)$data[$a]["eigentuemer"]);
					elseif ($data[$a]["eigentuemerTyp"]==AddressBase::AM_CLASS_ADDRESSGROUP) $adressTemp = AddressManager::GetAddressGroupByPkey($this->db, (int)$data[$a]["eigentuemer"]);
					if ($adressTemp!=null)
					{
						$data[$a]["eigentuemer"]=$adressTemp->GetAddressIDString(true);
						$data[$a]["gruppeEigentuemer"]=$adressTemp->GetAddressGroupName();
					}
					else
					{
						$data[$a]["eigentuemer"] = "-";
						$data[$a]["gruppeEigentuemer"] = "-";
					}
				}
				else
				{
					$data[$a]["eigentuemer"] = "-";
					$data[$a]["gruppeEigentuemer"] = "-";
				}
				// Verwalter
				if ($data[$a]["verwalter"]!=-1)
				{
					$adressTemp = null;
					if ($data[$a]["verwalterTyp"]==AddressBase::AM_CLASS_ADDRESSDATA) $adressTemp = AddressManager::GetAddressDataByPkey($this->db, (int)$data[$a]["verwalter"]);
					elseif ($data[$a]["verwalterTyp"]==AddressBase::AM_CLASS_ADDRESSCOMPANY) $adressTemp = AddressManager::GetAddressCompanyByPkey($this->db, (int)$data[$a]["verwalter"]);
					elseif ($data[$a]["verwalterTyp"]==AddressBase::AM_CLASS_ADDRESSGROUP) $adressTemp = AddressManager::GetAddressGroupByPkey($this->db, (int)$data[$a]["verwalter"]);
					if ($adressTemp!=null)
					{
						$data[$a]["verwalter"]=$adressTemp->GetAddressIDString(true);
						$data[$a]["gruppeVerwalter"]=$adressTemp->GetAddressGroupName();
					}
					else
					{
						$data[$a]["verwalter"]="-";
						$data[$a]["gruppeVerwalter"]="-";
					}
				}
				else
				{
					$data[$a]["verwalter"]="-";
					$data[$a]["gruppeVerwalter"]="-";
				}
				// Anwalt
				if ($data[$a]["anwalt"]!=-1)
				{
					$adressTemp = null;
					if ($data[$a]["anwaltTyp"]==AddressBase::AM_CLASS_ADDRESSDATA) $adressTemp = AddressManager::GetAddressDataByPkey($this->db, (int)$data[$a]["anwalt"]);
					elseif ($data[$a]["anwaltTyp"]==AddressBase::AM_CLASS_ADDRESSCOMPANY) $adressTemp = AddressManager::GetAddressCompanyByPkey($this->db, (int)$data[$a]["anwalt"]);
					elseif ($data[$a]["anwaltTyp"]==AddressBase::AM_CLASS_ADDRESSGROUP) $adressTemp = AddressManager::GetAddressGroupByPkey($this->db, (int)$data[$a]["anwalt"]);
					if ($adressTemp!=null)
					{
						$data[$a]["anwalt"]=$adressTemp->GetAddressIDString(true);
					}
					else
					{
						$data[$a]["anwalt"]="-";
					}
				}
				else
				{
					$data[$a]["anwalt"]="-";
				}
				// FMS
				if ($data[$a]["cPersonRS"]!=-1)
				{
					$userTemp = $this->GetUserByPkey((int)$data[$a]["cPersonRS"]);
					if ($userTemp!=null)
					{
						$data[$a]["cPersonRS"]=$userTemp->GetName()." ".$userTemp->GetFirstName();
					}
					else
					{
						$data[$a]["cPersonRS"]="-";
					}
				}
				else
				{
					$data[$a]["currentResponsibleRSUser"]="-";
				}

                // FMS Prozess
				if ($data[$a]["currentResponsibleRSUser"]!=-1)
				{
					$userTemp = $this->GetUserByPkey((int)$data[$a]["currentResponsibleRSUser"]);
					if ($userTemp!=null)
					{
						$data[$a]["currentResponsibleRSUser"]=$userTemp->GetName()." ".$userTemp->GetFirstName();
					}
					else
					{
						$data[$a]["currentResponsibleRSUser"]="-";
					}
				}
				else
				{
					$data[$a]["currentResponsibleRSUser"]="-";
				}

				// FMS Nebenkostenanalyst
				if ($data[$a]["cPersonFmsLeader"]!=-1)
				{
					$userTemp = $this->GetUserByPkey((int)$data[$a]["cPersonFmsLeader"]);
					if ($userTemp!=null)
					{
						$data[$a]["cPersonFmsLeader"]=$userTemp->GetName()." ".$userTemp->GetFirstName();
					}
					else
					{
						$data[$a]["cPersonFmsLeader"]="-";
					}
				}
				else
				{
					$data[$a]["cPersonFmsLeader"]="-";
				}
				// Kunde
				if ($data[$a]["cPersonCustomer "]!=-1)
				{
					$userTemp = $this->GetUserByPkey((int)$data[$a]["cPersonCustomer"]);
					if ($userTemp!=null)
					{
						$data[$a]["cPersonCustomer"]=$userTemp->GetName()." ".$userTemp->GetFirstName();
					}
					else
					{
						$data[$a]["cPersonCustomer"]="-";
					}
				}
				else
				{
					$data[$a]["cPersonCustomer"]="-";
				}
			}
		}
		return $data;
	}
	
	/**
	 * Build the SQL-Query string
	 * @return string
	 */
	private function BuildSqlQuery()
	{
		$currentResponsibleRSUserQuery = "(SELECT %select_column% FROM ".AddressData::TABLE_NAME;
		$currentResponsibleRSUserQuery.= "	LEFT JOIN ".User::TABLE_NAME." ON ".User::TABLE_NAME.".addressData=".AddressData::TABLE_NAME.".pkey";
		$currentResponsibleRSUserQuery.= "	WHERE ".User::TABLE_NAME.".pkey=(";
		$currentResponsibleRSUserQuery.= "		SELECT IF(coverUser<0, pkey, coverUser) FROM ".User::TABLE_NAME." WHERE pkey=(";
		$currentResponsibleRSUserQuery.= "			SELECT IF (";
		$currentResponsibleRSUserQuery.= "				".ProcessStatus::TABLE_NAME.".zuweisungUser<0, ";
		$currentResponsibleRSUserQuery.= "				(SELECT IF (";
		$currentResponsibleRSUserQuery.= "					".ProcessStatus::TABLE_NAME.".currentStatus IN (".implode(", ", WorkflowManager::GetAllStatusForGroup(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT))."), ";
		$currentResponsibleRSUserQuery.= "					".CShop::TABLE_NAME.".cPersonFmsAccountsdepartment, ";
		$currentResponsibleRSUserQuery.= "					".CShop::TABLE_NAME.".cPersonRS";
		$currentResponsibleRSUserQuery.= "				)),";
		$currentResponsibleRSUserQuery.= "				".ProcessStatus::TABLE_NAME.".zuweisungUser";
		$currentResponsibleRSUserQuery.= "			)";
		$currentResponsibleRSUserQuery.= "		)";
		$currentResponsibleRSUserQuery.= "	)";
		$currentResponsibleRSUserQuery.= ")";

		$currentResponsibleRSUser = str_replace('%select_column%', User::TABLE_NAME.".pkey", $currentResponsibleRSUserQuery)." AS currentResponsibleRSUser ";

		// build db query
		$query ="SELECT ".ProcessStatus::TABLE_NAME.".pkey AS prozessstatusPkey, ".ProcessStatus::TABLE_NAME.".currentStatus, ".ProcessStatus::TABLE_NAME.".archiveStatus, ".ProcessStatus::TABLE_NAME.".deadline AS deadlineProcess, ".ProcessStatus::TABLE_NAME.".scheduleComment, ".ProcessStatus::TABLE_NAME.".processStatusGroup_rel AS processGroupId, ".ProcessStatusGroup::TABLE_NAME.".name AS processGroupName, ".ProcessStatusGroup::TABLE_NAME.".scheduleComment AS groupScheduleComment, ".CGroup::TABLE_NAME.".name AS groupName, ".CCompany::TABLE_NAME.".name AS companyName, ".CLocation::TABLE_NAME.".pkey AS locationID, ".CShop::TABLE_NAME.".cPersonRS, ".CShop::TABLE_NAME.".cPersonFmsLeader, ".CShop::TABLE_NAME.".cPersonCustomer, ".CShop::TABLE_NAME.".pkey AS shopID, ".CShop::TABLE_NAME.".name AS shopName, ".CShop::TABLE_NAME.".internalShopNo, ".CShop::TABLE_NAME.".RSID , ".CLocation::TABLE_NAME.".name AS locationName, ".CLocation::TABLE_NAME.".city AS locationCity, ".CLocation::TABLE_NAME.".zip AS locationZIP, ".CLocation::TABLE_NAME.".street AS locationStreet, ".CLocation::TABLE_NAME.".country, ".AbrechnungsJahr::TABLE_NAME.".pkey AS abrechnungJahrID, ".AbrechnungsJahr::TABLE_NAME.".jahr AS abrechnungJahr, ".Contract::TABLE_NAME.".pkey AS contractID , ".Contract::TABLE_NAME.".currency, ".Contract::TABLE_NAME.".mvBeginn, ".Contract::TABLE_NAME.".mvEnde, ".Teilabrechnung::TABLE_NAME.".eigentuemer, ".Teilabrechnung::TABLE_NAME.".eigentuemerTyp, ".Teilabrechnung::TABLE_NAME.".fristWiderspruch, ".Teilabrechnung::TABLE_NAME.".fristBelegeinsicht, ".Teilabrechnung::TABLE_NAME.".fristZahlung, ".Teilabrechnung::TABLE_NAME.".verwalter, ".Teilabrechnung::TABLE_NAME.".verwalterTyp, ".Teilabrechnung::TABLE_NAME.".anwalt, ".Teilabrechnung::TABLE_NAME.".anwaltTyp, ".Teilabrechnung::TABLE_NAME.".auftragsdatumAbrechnung, ".Teilabrechnung::TABLE_NAME.".datum AS datumAbrechnung, ".Teilabrechnung::TABLE_NAME.".abrechnungszeitraumVon, ".Teilabrechnung::TABLE_NAME.".abrechnungszeitraumBis, ".ProcessStatus::TABLE_NAME.".form, ".ProcessStatus::TABLE_NAME.".stagesPointsToBeClarified, ".ProcessStatus::TABLE_NAME.".referenceNumber, ".ProcessStatus::TABLE_NAME.".subProcedure, ";
		/*if ($this->user->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_KUNDE)
		{
			// this is a very expensive subquery and is only needed for customers
			$query.=" (SELECT count(".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".pkey) FROM ".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX)." LEFT JOIN ".File::TABLE_NAME." ON ".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".file=".File::TABLE_NAME.".pkey WHERE ".File::TABLE_NAME.".fileSemantic=".FM_FILE_SEMANTIC_WIDERSPRUCH." AND ".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".widerspruch IN (SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".ProcessStatus::TABLE_NAME.".abrechnungsjahr)) as numWs, ";
		}*/
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE ".($this->GetValue("showRealisiert")==-1 ? "" : "realisiert=".(int)$this->GetValue("showRealisiert")." AND ")." ".($this->GetValue("showErsteinsparung")=='all' ? "" : "type=".(int)$this->GetValue("showErsteinsparung")." AND ")." einsparungsTyp=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_MAXIMALFORDERUNG." AND (rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB.") AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS maximalforderung, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE ".($this->GetValue("showRealisiert")==-1 ? "" : "realisiert=".(int)$this->GetValue("showRealisiert")." AND ")." ".($this->GetValue("showErsteinsparung")=='all' ? "" : "type=".(int)$this->GetValue("showErsteinsparung")." AND ")." einsparungsTyp=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_STANDARD." AND (rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB.") AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS entgegenkommen_standard, ";
		$query.=" ( IFNULL((SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE ".($this->GetValue("showRealisiert")==-1 ? "" : "realisiert=".(int)$this->GetValue("showRealisiert")." AND ")." ".($this->GetValue("showErsteinsparung")=='all' ? "" : "type=".(int)$this->GetValue("showErsteinsparung")." AND ")." einsparungsTyp=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_MAXIMALFORDERUNG." AND (rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB.") AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ), 0) - IFNULL((SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE ".($this->GetValue("showRealisiert")==-1 ? "" : "realisiert=".(int)$this->GetValue("showRealisiert")." AND ")." ".($this->GetValue("showErsteinsparung")=='all' ? "" : "type=".(int)$this->GetValue("showErsteinsparung")." AND ")." einsparungsTyp=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_STANDARD." AND (rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB.") AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ), 0) - IFNULL((SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE ".($this->GetValue("showRealisiert")==-1 ? "" : "realisiert=".(int)$this->GetValue("showRealisiert")." AND ")." ".($this->GetValue("showErsteinsparung")=='all' ? "" : "type=".(int)$this->GetValue("showErsteinsparung")." AND ")." einsparungsTyp=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_KUNDE." AND (rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB.") AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ), 0)) AS loesungsvorschlag, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE ".($this->GetValue("showRealisiert")==-1 ? "" : "realisiert=".(int)$this->GetValue("showRealisiert")." AND ")." ".($this->GetValue("showErsteinsparung")=='all' ? "" : "type=".(int)$this->GetValue("showErsteinsparung")." AND ")." einsparungsTyp=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_KUNDE." AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS entgegenkommen_zusaetzlich, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE ".($this->GetValue("showRealisiert")==-1 ? "" : "realisiert=".(int)$this->GetValue("showRealisiert")." AND ")." ".($this->GetValue("showErsteinsparung")=='all' ? "" : "type=".(int)$this->GetValue("showErsteinsparung")." AND ")." einsparungsTyp=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_VERMIETERANGEBOT." AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS vermieterangebot, ";
		$query.= $currentResponsibleRSUser." ";
		$query.=" FROM ".ProcessStatus::TABLE_NAME;
		$query.=" LEFT JOIN ".ProcessStatusGroup::TABLE_NAME." ON ".ProcessStatus ::TABLE_NAME.".processStatusGroup_rel=".ProcessStatusGroup::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus ::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".currentTeilabrechnung=".Teilabrechnung::TABLE_NAME.".pkey ";
		$query.=" LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract =".Contract::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey";
		// build WHERE clause
		$whereClause = $this->BuildWhereClause();
		if ($whereClause!="") $query.=" WHERE ".$whereClause;
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
		$this->AppendToWhereClause($whereClause, "naechsteMassnahme", ProcessStatus::TABLE_NAME.".currentStatus");
		$this->AppendToWhereClause($whereClause, "showArchievedStatus", ProcessStatus::TABLE_NAME.".archiveStatus", "-1");
		if (isset($_POST["addressGroup"]) && $_POST["addressGroup"]!="all" && is_array($_POST["addressGroup"]) && count($_POST["addressGroup"])>0)
		{
			$addressGroupIds = '';
			foreach($_POST["addressGroup"] as $addressGroupId)
			{
				if ($addressGroupIds!='') $addressGroupIds.=', ';
				$addressGroupIds.= (int)$addressGroupId;
			}

			$eigentuemerGroup= "(SELECT IF(";
			$eigentuemerGroup.=	Teilabrechnung::TABLE_NAME.".eigentuemerTyp=".AddressBase::AM_CLASS_ADDRESSDATA.",";
			$eigentuemerGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup LEFT JOIN ".AddressData::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".pkey=".AddressData::TABLE_NAME.".addressCompany WHERE ".AddressData::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".eigentuemer),";
			$eigentuemerGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup WHERE ".AddressCompany::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".eigentuemer)";
			$eigentuemerGroup.= ") LIMIT 0,1)";

			$whereClause.=($whereClause!="" ? " AND " : "")." $eigentuemerGroup IN (".$addressGroupIds.") "; //, $verwalterGroup, $anwaltGroup)";
		}
		if (isset($_POST["addressGroupTrustee"]) && $_POST["addressGroupTrustee"]!="all" && is_array($_POST["addressGroupTrustee"]) && count($_POST["addressGroupTrustee"])>0)
		{
			$addressGroupIds = '';
			foreach($_POST["addressGroupTrustee"] as $addressGroupId)
			{
				if ($addressGroupId==-1) continue;
				if ($addressGroupIds!='') $addressGroupIds.=', ';
				$addressGroupIds.= (int)$addressGroupId;
			}

			$subWhereClause = '';
			if (in_array(-1, $_POST["addressGroupTrustee"]))
			{
				$verwalterGroup= Teilabrechnung::TABLE_NAME.".verwalter";
				$subWhereClause.=($subWhereClause!="" ? " OR " : "").Teilabrechnung::TABLE_NAME.".verwalter=-1 ";
			}
			if ($addressGroupIds!='')
			{
				$verwalterGroup= "(SELECT IF(";
				$verwalterGroup.=	Teilabrechnung::TABLE_NAME.".verwalterTyp=".AddressBase::AM_CLASS_ADDRESSDATA.",";
				$verwalterGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup LEFT JOIN ".AddressData::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".pkey=".AddressData::TABLE_NAME.".addressCompany WHERE ".AddressData::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".verwalter),";
				$verwalterGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup WHERE ".AddressCompany::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".verwalter)";
				$verwalterGroup.= ") LIMIT 0,1)";
				$subWhereClause.=($subWhereClause!="" ? " OR " : "").$verwalterGroup." IN (".$addressGroupIds.") ";
			}
			if ($subWhereClause!='') $whereClause.=($whereClause!="" ? " AND " : "")."(".$subWhereClause.")";
		}
		// add access rights to query
		if ($this->user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			$groupIDs = $this->user->GetGroupIDs($this->db);
			for ($a=0; $a<count($groupIDs); $a++)
			{
				if ($groupQuery!="") $groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			if (trim($whereClause)!="")
			{
				$whereClause = "(".$groupQuery.")"." AND ".$whereClause;
			}
			else
			{
				$whereClause = "(".$groupQuery.")";
			}
		}
		return $whereClause;
	}
	
	/**
	 * Output the report as HTML
	 * @return bool
	 */
	public function PrintHtml()
	{
		if (isset($_POST["show"]))
		{
			$columnFilter = $this->GetColumnFilters();
			$data = $this->GetReportData();
			$list1 = new NCASList( new BereichtStandortvergleichProzessstatusListData($this->db, $columnFilter, $data), "FM_FORM" );
			$list1->PrintData(); 
		}
		return true;
	}
	
	
	/**
	 * Output the report in the specified format
	 * @param int $format
	 * @return bool
	 */
	public function StreamAsFormat($format)
	{
		if ($format==ReportManager::REPORT_FORMAT_PDF) return $this->StreamAsPdf();
		if ($format==ReportManager::REPORT_FORMAT_CSV) return $this->StreamAsCsv();
		return false;
	}
	
	/**
	 * Stream report as PDF-File
	 */
	private function StreamAsPdf()
	{
		global $SHARED_HTTP_ROOT;
		ob_start();
	?>	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
			</head>
			<body>
				<style type="text/css">
					body{
						text-align: center;
						background-color:#ffffff;
						color:#404040;
						font-size: 11px;
						font-family: Arial, Helvetica, sans-serif;
						line-height: 16px;
						margin-top:0px;
						padding:0px;	
					}

					table{
						color:#404040;
						font-size: 11px;
						font-family: Arial, Helvetica, sans-serif;
						line-height: 16px;
					}

					img{
						border:0;
						margin:0px;
						padding:0px;	
					}
				</style>
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td>
							<img src="<?=$SHARED_HTTP_ROOT?>pics/Logo.png" alt="" align="left" /><br />
						</td>
					</tr>
					<tr>
						<td style="height:10px;"></td>
					</tr>
					<tr>
						<td>
							<? $this->PrintHtml(); ?>
							<table border="0" bgcolor="#ffffff" cellpadding="0" cellspacing="0" width="1014px;">
								<tr>
									<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/spezialfall/edit_bottom_left.png); background-repeat: no-repeat; height:63px; width:32px;">&#160;</td>
									<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/spezialfall/edit_bottom_center.png); background-repeat: repeat-x; height:63px;">&#160;</td>
									<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/spezialfall/edit_bottom_right.png); background-repeat: no-repeat; height:63px; width:17px;">&#160;</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</body>
		</html>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		// PDF erzeugen
		include("html2pdf.php5");
		$pdfContent = convert_to_pdf($CONTENT, "A4", 1024, true, '', true );
		if( $pdfContent!="" ){
			// PDF streamen...
			header('HTTP/1.1 200 OK');
			header('Status: 200 OK');
			header('Accept-Ranges: bytes');
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header('Content-Transfer-Encoding: Binary');
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"Prozessstatus.pdf\"");
			header("Content-Length: ".strlen($pdfContent));
			echo $pdfContent;
			exit;
		}else{
			// TODO: Fehler: PDF konnte nicht erzeugt werden
			echo "Fehler 1";
		}
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
		$columnFilterConfig = $this->GetColumnFilters();
		for($a=0; $a<count($columnFilterConfig); $a++){
			if( !$columnFilterConfig[$a]["visible"] )continue;
			$myCSVString .= str_replace(";", ",", $columnFilterConfig[$a]["caption"]).$sep;
		}
		$myCSVString.=$endline;
		$data = $this->GetReportData();
		$listData=new BereichtStandortvergleichProzessstatusListData($this->db, $columnFilterConfig, $data);
		$data=$listData->Search("", $_POST["order_by_0"], $_POST["order_direction_0"]);
		if( is_array($data) ){
			for($a=0; $a<count($data); $a++){
				for($b=0; $b<count($data[$a]); $b++){
					$myCSVString .= str_replace(";", ",", $data[$a][$b]).$sep;
				}
				$myCSVString .= $endline;
			}
		}
		$myCSVString = utf8_decode($myCSVString);
		header('HTTP/1.1 200 OK');
		header('Status: 200 OK');
		header('Accept-Ranges: bytes');
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Transfer-Encoding: Binary');
		header("Content-type: application/octetstream; charset=UTF-8");
		header("Content-Disposition: attachment; filename=\"Prozessstatus.csv\"");
		header("Content-Length: ".(string)strlen($myCSVString));
		echo $myCSVString;
		exit;
	}
	
}
?>