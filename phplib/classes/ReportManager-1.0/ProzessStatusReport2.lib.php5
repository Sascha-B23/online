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
class ProzessStatusReport2 extends ReportManager
{
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param User $user
	 */
	public function ProzessStatusReport2(DBManager $db, User $user, CustomerManager $customerManager, AddressManager $addressManager, UserManager $userManager, RSKostenartManager $kostenartManager, ErrorManager $errorManager, ExtendedLanguageManager $languageManager)
	{
		parent::__construct($db, $user, $customerManager, $addressManager, $userManager, $kostenartManager, $errorManager, $languageManager, get_class($this), "Berichte > Prozessstatus");
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
		$defalutSettings["rsuser"] = "all";
		$defalutSettings["abrechnugsjahre"] = "all";
		$defalutSettings["addressGroupOwner"] = "all";
		$defalutSettings["addressGroupTrustee"] = "all";
		$defalutSettings["proccess"] = "all";
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$defalutSettings["showArchievedStatus"] = -1;
		}
		else
		{
			$defalutSettings["showArchievedStatus"] = Schedule::ARCHIVE_STATUS_UPTODATE;
		}
		$defalutSettings["showRealisiert"] = "-1";
		$defalutSettings["showRechnungBezahlt"] = "-1";
		$defalutSettings["showErsteinsparung"] = "all";
		
		// column filter settings
		$defalutSettings["cb_processgroup_name"] = true;
		$defalutSettings["cb_planned"] = true;
		$defalutSettings["cb_prio"] = true;
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
		if (isset($_POST["rsuser"])) $this->SetValue("rsuser", $_POST["rsuser"]);
		if (isset($_POST["abrechnugsjahre"])) $this->SetValue("abrechnugsjahre", $_POST["abrechnugsjahre"]);
		if (isset($_POST["addressGroupOwner"])) $this->SetValue("addressGroupOwner", $_POST["addressGroupOwner"]);
		if (isset($_POST["addressGroupTrustee"])) $this->SetValue("addressGroupTrustee", $_POST["addressGroupTrustee"]);
		if (isset($_POST["proccess"])) $this->SetValue("proccess", $_POST["proccess"]);
		if ($this->user->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_RSMITARBEITER)
		{
			if (isset($_POST["showArchievedStatus"])) $this->SetValue("showArchievedStatus", (int)$_POST["showArchievedStatus"]);
		}
		else
		{
			// Customers only can see uptodate prozesses
			$this->SetValue("showArchievedStatus", Schedule::ARCHIVE_STATUS_UPTODATE);
		}
		if (isset($_POST["showErsteinsparung"])) $this->SetValue("showErsteinsparung", $_POST["showErsteinsparung"]);
		if (isset($_POST["showRealisiert"])) $this->SetValue("showRealisiert", $_POST["showRealisiert"]);
		if (isset($_POST["showRechnungBezahlt"])) $this->SetValue("showRechnungBezahlt", $_POST["showRechnungBezahlt"]);
		
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
		$elements[] = $this->CreateFormCompanies($this->GetValue("company"));
		$elements[] = $this->CreateFormLocation($this->GetValue("location"));
		$elements[] = $this->CreateFormShop($this->GetValue("shop"));
		$elements[] = $this->CreateFormFmsUsers($this->GetValue("rsuser"));
		$elements[] = $this->CreateFormYears($this->GetValue("abrechnugsjahre"));
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$elements[] = $this->CreateFormAddressGroup("addressGroupOwner", "Gruppe Eigentümer", $this->GetValue("addressGroupOwner"));
			$elements[] = $this->CreateFormAddressGroup("addressGroupTrustee", "Gruppe Verwalter", $this->GetValue("addressGroupTrustee"));
		}
		$elements[] = $this->CreateFormProzessstatus($this->GetValue("proccess"), Array(0,1,2,3));
		
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$elements[] = $this->CreateFormArchievedStatus($this->GetValue("showArchievedStatus"));
		}
		$elements[] = $this->CreateFormErsteinsparung($this->GetValue("showErsteinsparung"));
		$elements[] = $this->CreateFormRealisiert($this->GetValue("showRealisiert"));
		$elements[] = $this->CreateFormRechnungBezahlt($this->GetValue("showRechnungBezahlt"));
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
		$columnFilter[]=array("id" => "cb_processgroup_name", "caption" => "Name Paket", "sortby" => "processGroupName", "sorttype" => "str", "visible" => true);
		$columnFilter[]=array("id" => "cb_fms_id", "caption" => CShop::GetAttributeName($this->languageManager, 'RSID'), "sortby" => "RSID", "sorttype" => "str", "visible" => false, "hidden" => true);
		$columnFilter[]=array("id" => "cb_contract_id", "caption" => "Vertrags-ID SFM", "sortby" => "contractID", "sorttype" => "str", "visible" => false, "convert" => "contract_id", "hidden" => true);
		$columnFilter[]=array("id" => "cb_process_id", "caption" => ProcessStatus::GetAttributeName($this->languageManager, 'processStatusId'), "sortby" => "prozessstatusPkey", "sorttype" => "str", "visible" => false, "convert" => "process_id", "hidden" => true);
		$columnFilter[]=array("id" => "cb_processgroup_id", "caption" => ProcessStatusGroup::GetAttributeName($this->languageManager, 'id'), "sortby" => "processGroupId", "sorttype" => "str", "visible" => false, "convert" => "processgroup_id", "hidden" => true);
		$columnFilter[]=array("id" => "cb_group", "caption" => CGroup::GetAttributeName($this->languageManager, 'name'), "sortby" => "groupName", "sorttype" => "str", "visible" => true, "hidden" => true);
		$columnFilter[]=Array("id" => "cb_location", "caption" => CLocation::GetAttributeName($this->languageManager, 'name'), "sortby" => "locationName", "sorttype" => "str", "visible" => true, "hidden" => true);
		$columnFilter[]=array("id" => "cb_shop", "caption" => CShop::GetAttributeName($this->languageManager, 'name'), "sortby" => "shopName", "sorttype" => "str", "visible" => true, "hidden" => true);
		$columnFilter[]=array("id" => "cb_abrechnungsjahr", "caption" => AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr'), "sortby" => "abrechnungJahr", "sorttype" => "num", "visible" => true, "hidden" => true);
		$columnFilter[]=array("id" => "cb_status", "caption" => WorkflowStatus::GetAttributeName($this->languageManager, 'currentStatus'), "sortby" => "currentStatus", "sorttype" => "num", "visible" => true, "convert" => "status", "hidden" => true);
		$columnFilter[]=array("id" => "cb_archievedStatus", "caption" => Schedule::GetAttributeName($this->languageManager, 'archiveStatus'), "sortby" => "archiveStatus", "sorttype" => "num", "visible" => true, "convert" => "archievedStatus", "hidden" => true);
		$columnFilter[]=array("id" => "cb_planned", "caption" => Schedule::GetAttributeName($this->languageManager, 'planned'), "sortby" => "planned", "sorttype" => "num", "visible" => true, "convert" => "bool2");
		$columnFilter[]=Array("id" => "cb_ws_volume_green", "caption" => "WS-Volumen grün", "sortby" => "gruen", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		$columnFilter[]=Array("id" => "cb_ws_volume_yellow", "caption" => "WS-Volumen gelb", "sortby" => "gelb", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		$columnFilter[]=Array("id" => "cb_ws_volume_red", "caption" => "WS-Volumen rot", "sortby" => "rot", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		$columnFilter[]=Array("id" => "cb_ws_volume_grey", "caption" => "WS-Volumen grau", "sortby" => "grau", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		$columnFilter[]=array("id" => "cb_ersteinsparung", "caption" => "Ersteinsparung", "sortby" => "wsSummeErsteinsparung", "sorttype" => "num", "visible" => false, "convert" => "currency", "hidden" => true);
		$columnFilter[]=array("id" => "cb_folgeeinsparung", "caption" => "Folgeeinsparung", "sortby" => "wsSummeFolgeeinsparung", "sorttype" => "num", "visible" => false, "convert" => "currency", "hidden" => true);
		$columnFilter[]=array("id" => "cb_ws_realisiert", "caption" => "WS-Summe realisiert", "sortby" => "wsSummeRealisiert", "sorttype" => "num", "visible" => false, "convert" => "currency", "hidden" => true);
		$columnFilter[]=array("id" => "cb_ws_nicht_realisiert", "caption" => "WS-Summe nicht realisiert", "sortby" => "ws_nicht_realisiert", "sorttype" => "num", "visible" => false, "convert" => "currency", "hidden" => true);
		$columnFilter[]=array("id" => "cb_prio", "caption" => Schedule::GetAttributeName($this->languageManager, 'prio'), "sortby" => "prio", "sorttype" => "num", "visible" => false, "convert" => "prio");
		$columnFilter[]=array("id" => "cb_frist_prozessstatus", "caption" => WorkflowStatus::GetAttributeName($this->languageManager, 'deadline'), "sortby" => "deadlineProcess", "sorttype" => "num", "visible" => false, "convert" => "date");
		$columnFilter[]=array("id" => "cb_abschlussdatum", "caption" => ProcessStatus::GetAttributeName($this->languageManager, 'abschlussdatum'), "sortby" => "abschlussdatum", "sorttype" => "num", "visible" => false, "convert" => "date");
		$columnFilter[]=array("id" => "cb_auftragsende", "caption" => ProcessStatus::GetAttributeName($this->languageManager, 'zahlungsdatum'), "sortby" => "zahlungsdatum", "sorttype" => "num", "visible" => false, "convert" => "date");
		$columnFilter[]=array("id" => "cb_schedule_comment", "caption" => Schedule::GetAttributeName($this->languageManager, 'scheduleComment'), "sortby" => "scheduleComment", "sorttype" => "str", "visible" => false);
		$columnFilter[]=Array("id" => "cb_datum_abrechnung", "caption" => "Rechnungsdatum", "sortby" => "paymentDate", "sorttype" => "num", "visible" => false, "convert" => "date");
		$columnFilter[]=Array("id" => "cb_rechnung_bezahlt", "caption" => "Rechnung bezahlt", "sortby" => "paymentReceived", "sorttype" => "num", "visible" => false, "hidden" => true, "convert" => "bool2");
		$columnFilter[]=Array("id" => "cb_zahlungsdatum", "caption" => "Zahlungsdatum", "sortby" => "paymentDate", "sorttype" => "num", "visible" => false, "hidden" => true, "convert" => "plus_14_days");
		$columnFilter[]=Array("id" => "cb_rechnungsnummer", "caption" => "Rechnungsnummer", "sortby" => "paymentNumber", "sorttype" => "str", "visible" => false);
		
				
		// Spaltenkonfiguration setzen
		for ($a=0; $a<count($columnFilter); $a++)
		{
			//hidden checkboxes are used for querying but are hidden in the frontend
			if($columnFilter[$a]["hidden"] === true)
			{
				$columnFilter[$a]["visible"] = true; //visible indicates that the checkbox is checked
			}
			else
			{
				$columnFilter[$a]["visible"] = ($this->GetValue($columnFilter[$a]["id"])===true ? true : false);
			}
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
			//$timeBeforeQuery = time();
			$data = $this->db->SelectAssoc($query);
			//echo (time()-$timeBeforeQuery)." Sekunden <br />";
			//echo "<pre>".print_r($data, true)."</pre>";
			//exit;
			$data2 = Array();
			for ($a=0; $a<count($data); $a++)
			{
				// Werte berechnen...
				$data[$a]["diffAbrechnungsergebnisLautAbrechnung"] = $data[$a]["betragKunde"]-$data[$a]["abrechnungsergebnisLautAbrechnung"];
				$data[$a]["ws_nicht_realisiert"] = (float)$data[$a]["wsSumme"]-(float)$data[$a]["wsSummeRealisiert"];
				$data[$a]["abrechnungsergebnisRS"] = (float)$data[$a]["betragKunde"]-(float)$data[$a]["vorauszahlungLautKunde"]-(float)$data[$a]["wsSumme"];
				// Daten filtern
				if ($this->GetValue("showRealisiert")==(string)Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO && (float)$data[$a]["ws_nicht_realisiert"]==0.0) continue;
				if ($this->GetValue("showRealisiert")==(string)Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES && (float)$data[$a]["wsSummeRealisiert"]==0.0) continue;
				
				if ($this->GetValue("showRechnungBezahlt")==1 && $data[$a]["paymentReceived"]==0) continue;
				if ($this->GetValue("showRechnungBezahlt")==0 && $data[$a]["paymentReceived"]==1) continue;
				
				if ($this->GetValue("showErsteinsparung")==(string)Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG && (float)$data[$a]["wsSummeErsteinsparung"]==0.0) continue;
				if ($this->GetValue("showErsteinsparung")==(string)Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG && (float)$data[$a]["wsSummeFolgeeinsparung"]==0.0) continue;
				$data2[] = $data[$a];
			}
			$data = $data2;
			// Namen nachladen
			//$timeBeforeQuery = time();
			for ($a=0; $a<count($data); $a++)
			{
				if ($data[$a]["processGroupId"]=="0") $data[$a]["processGroupId"] = "";
				/*// Eigentümer
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
						$data[$a]["gruppeAnwalt"]=$adressTemp->GetAddressGroupName();
					}
					else
					{
						$data[$a]["anwalt"]="-";
						$data[$a]["gruppeAnwalt"]="-";
					}
				}
				else
				{
					$data[$a]["anwalt"]="-";
					$data[$a]["gruppeAnwalt"]="-";
				}*/
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
					$data[$a]["cPersonRS"]="-";
				}
			}
			//echo (time()-$timeBeforeQuery)." Sekunden <br />";
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
		// build db query
		$query ="SELECT ".ProcessStatus::TABLE_NAME.".pkey AS prozessstatusPkey, ".ProcessStatus::TABLE_NAME.".currentStatus, "
				.ProcessStatus::TABLE_NAME.".deadline AS deadlineProcess, "
				.ProcessStatus::TABLE_NAME.".scheduleComment, ".ProcessStatus::TABLE_NAME.".archiveStatus, "
				.ProcessStatus::TABLE_NAME.".processStatusGroup_rel AS processGroupId, ".ProcessStatusGroup::TABLE_NAME.".name AS processGroupName, "
				.CGroup::TABLE_NAME.".name AS groupName, ".CCompany::TABLE_NAME.".name AS companyName, "
				.CLocation::TABLE_NAME.".pkey AS locationID, ".CShop::TABLE_NAME.".cPersonRS, "
				.CShop::TABLE_NAME.".pkey AS shopID, ".CShop::TABLE_NAME.".name AS shopName, "
				.CShop::TABLE_NAME.".internalShopNo, ".CShop::TABLE_NAME.".RSID , "
				.CLocation::TABLE_NAME.".name AS locationName, ".CLocation::TABLE_NAME.".city AS locationCity, "
				.CLocation::TABLE_NAME.".zip AS locationZIP, ".CLocation::TABLE_NAME.".street AS locationStreet, "
				.CLocation::TABLE_NAME.".country,  ".CLocation::TABLE_NAME.".locationType, "
				.AbrechnungsJahr::TABLE_NAME.".pkey AS abrechnungJahrID, ".AbrechnungsJahr::TABLE_NAME.".jahr AS abrechnungJahr, "
				.Contract::TABLE_NAME.".pkey AS contractID, ".Contract::TABLE_NAME.".currency , "
				.Contract::TABLE_NAME.".mietflaeche_qm, ".Contract::TABLE_NAME.".nurAuszuegeVorhanden, "
				.Contract::TABLE_NAME.".mietvertrVollstaendig, ".Teilabrechnung::TABLE_NAME.".eigentuemer, ".Teilabrechnung::TABLE_NAME.".eigentuemerTyp, "
				.Teilabrechnung::TABLE_NAME.".fristWiderspruch, ".Teilabrechnung::TABLE_NAME.".verwalter, ".Teilabrechnung::TABLE_NAME.".verwalterTyp, "
				.Teilabrechnung::TABLE_NAME.".anwalt, ".Teilabrechnung::TABLE_NAME.".anwaltTyp, ".Teilabrechnung::TABLE_NAME.".auftragsdatumAbrechnung, "
				.Teilabrechnung::TABLE_NAME.".datum AS datumAbrechnung, ".Teilabrechnung::TABLE_NAME.".abschlagszahlungGutschrift, "
				.Teilabrechnung::TABLE_NAME.".vorauszahlungLautKunde, ".ProcessStatus::TABLE_NAME.".abschlussdatum, "
				.ProcessStatus::TABLE_NAME.".zahlungsdatum, ";
		if ($this->user->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_KUNDE)
		{
			// this is a very expensive subquery and is only needed for customers
			$query.=" (SELECT count(".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".pkey) FROM ".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX)." LEFT JOIN ".File::TABLE_NAME." ON ".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".file=".File::TABLE_NAME.".pkey WHERE ".File::TABLE_NAME.".fileSemantic=".FM_FILE_SEMANTIC_WIDERSPRUCH." AND ".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".widerspruch IN (SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".ProcessStatus::TABLE_NAME.".abrechnungsjahr)) as numWs, ";
		}
		$query.=" (SELECT SUM(betragKunde) FROM ".Teilabrechnungsposition::TABLE_NAME." LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON teilabrechnung=".Teilabrechnung::TABLE_NAME.".pkey WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey) AS betragKunde, ";
		$query.=" (SELECT IF(".Contract::TABLE_NAME.".mietflaeche_qm=0, 0, (SELECT SUM(betragKunde) FROM ".Teilabrechnungsposition::TABLE_NAME." LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON teilabrechnung=".Teilabrechnung::TABLE_NAME.".pkey WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey)/".Contract::TABLE_NAME.".mietflaeche_qm)) AS betragKundeQM, ";
		$query.=" (SELECT SUM(abrechnungsergebnisLautAbrechnung) FROM ".Teilabrechnung::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey) AS abrechnungsergebnisLautAbrechnung, ";
		$query.=" (SELECT paymentReceived FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) AS paymentReceived, ";
		$query.=" (SELECT paymentDate FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) AS paymentDate, ";
		$query.=" (SELECT paymentNumber FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) AS paymentNumber, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS gruen, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT." AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS rot, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB." AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS gelb, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU." AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS grau, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE (rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU.") AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS wsSumme, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE realisiert=".Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES." AND (rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU.") AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS wsSummeRealisiert, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE type=".Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG." AND (rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU.") AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS wsSummeErsteinsparung, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE type=".Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG." AND (rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU.") AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS wsSummeFolgeeinsparung, ";
		$query.=" ".ProcessStatus::GetPrioColumnQuery()." ";
		//$query.=", (SELECT MIN(abrechnungszeitraumVon) FROM ".Teilabrechnung::TABLE_NAME.", ".ProcessStatus::TABLE_NAME." WHERE ".Teilabrechnung::TABLE_NAME.".abrechnungsJahr=abrechnungJahrID AND ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=abrechnungJahrID) AS min_abrechnungszeitraum,";
		//$query.=" (SELECT MAX(abrechnungszeitraumBis) FROM ".Teilabrechnung::TABLE_NAME.", ".ProcessStatus::TABLE_NAME." WHERE ".Teilabrechnung::TABLE_NAME.".abrechnungsJahr=abrechnungJahrID AND ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=abrechnungJahrID) AS max_abrechnungszeitraum";
		$query.=" FROM ".ProcessStatus::TABLE_NAME;
		$query.=" LEFT JOIN ".ProcessStatusGroup::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".processStatusGroup_rel=".ProcessStatusGroup::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".currentTeilabrechnung=".Teilabrechnung::TABLE_NAME.".pkey ";
		$query.=" LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract =".Contract::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey";
		// build WHERE clause
		$whereClause = $this->BuildWhereClause();
		if ($whereClause!="") $query.=" WHERE ".$whereClause;
		// sort by year
		$query.=" ORDER BY prozessstatusPkey";

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
		if (isset($_POST[$postVarName]) && $_POST[$postVarName]!=$allValue && $_POST[$postVarName]==(int)$_POST[$postVarName])
		{
			$whereClause.=($whereClause!="" ? " AND " : "").$columnName."=".(int)$_POST[$postVarName];
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
		$this->AppendToWhereClause($whereClause, "rsuser", CShop::TABLE_NAME.".cPersonRS");
		$this->AppendToWhereClause($whereClause, "abrechnugsjahre", AbrechnungsJahr::TABLE_NAME.".jahr");
		
		if (isset($_POST["addressGroupOwner"]) && $_POST["addressGroupOwner"]!="all" && $_POST["addressGroupOwner"]==(int)$_POST["addressGroupOwner"])
		{
			$eigentuemerGroup= "(SELECT IF(";
			$eigentuemerGroup.=	Teilabrechnung::TABLE_NAME.".eigentuemerTyp=".AddressBase::AM_CLASS_ADDRESSDATA.",";
			$eigentuemerGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup LEFT JOIN ".AddressData::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".pkey=".AddressData::TABLE_NAME.".addressCompany WHERE ".AddressData::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".eigentuemer),";
			$eigentuemerGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup WHERE ".AddressCompany::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".eigentuemer)";
			$eigentuemerGroup.= "))";
			
			$whereClause.=($whereClause!="" ? " AND " : "").(int)$_POST["addressGroupOwner"]."=$eigentuemerGroup";
		}
		
		if (isset($_POST["addressGroupTrustee"]) && $_POST["addressGroupTrustee"]!="all" && $_POST["addressGroupTrustee"]==(int)$_POST["addressGroupTrustee"])
		{
			$verwalterGroup= "(SELECT IF(";
			$verwalterGroup.=	Teilabrechnung::TABLE_NAME.".verwalterTyp=".AddressBase::AM_CLASS_ADDRESSDATA.",";
			$verwalterGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup LEFT JOIN ".AddressData::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".pkey=".AddressData::TABLE_NAME.".addressCompany WHERE ".AddressData::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".verwalter),";
			$verwalterGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup WHERE ".AddressCompany::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".verwalter)";
			$verwalterGroup.= "))";
			
			$whereClause.=($whereClause!="" ? " AND " : "").(int)$_POST["addressGroupTrustee"]."=$verwalterGroup";
		}
		
		$this->AppendToWhereClause($whereClause, "showArchievedStatus", ProcessStatus::TABLE_NAME.".archiveStatus", "-1");
		// add access rights to query
		if ($this->user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_RSMITARBEITER)
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
		header('HTTP/1.1 200 OK');
		header('Status: 200 OK');
		header('Accept-Ranges: bytes');
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Transfer-Encoding: Binary');
		header("Content-type: application/octetstream; charset=UTF-8");
		header("Content-Disposition: attachment; filename=\"Prozessstatus.csv\"");
		header("Content-Length: ".(string)strlen("\xEF\xBB\xBF".$myCSVString));
		echo "\xEF\xBB\xBF".$myCSVString;
		exit;
	}
	
}
?>