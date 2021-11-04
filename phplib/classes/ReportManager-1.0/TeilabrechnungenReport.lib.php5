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
class TeilabrechnungenReport extends ReportManager
{
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param User $user
	 */
	public function TeilabrechnungenReport(DBManager $db, User $user, CustomerManager $customerManager, AddressManager $addressManager, UserManager $userManager, RSKostenartManager $kostenartManager, ErrorManager $errorManager, ExtendedLanguageManager $languageManager)
	{
		parent::__construct($db, $user, $customerManager, $addressManager, $userManager, $kostenartManager, $errorManager, $languageManager, "teilabrechnungenReport", "Berichte > Teilabrechnungen");
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
		$defalutSettings["rsuser"] = "all";
		$defalutSettings["abrechnugsjahre"] = "all";
		$defalutSettings["addressGroupEigentuemer"] = "all";
		$defalutSettings["addressGroupVerwalter"] = "all";
		$defalutSettings["addressGroupAnwalt"] = "all";
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
		if (isset($_POST["rsuser"])) $this->SetValue("rsuser", $_POST["rsuser"]);
		if (isset($_POST["datumBeauftragungVon"])) $this->SetValue("datumBeauftragungVon", $_POST["datumBeauftragungVon"]);
		if (isset($_POST["datumBeauftragungBis"])) $this->SetValue("datumBeauftragungBis", $_POST["datumBeauftragungBis"]);
		if (isset($_POST["datumAbrechnungVon"])) $this->SetValue("datumAbrechnungVon", $_POST["datumAbrechnungVon"]);
		if (isset($_POST["datumAbrechnungBis"])) $this->SetValue("datumAbrechnungBis", $_POST["datumAbrechnungBis"]);
		if (isset($_POST["abrechnugsjahre"])) $this->SetValue("abrechnugsjahre", $_POST["abrechnugsjahre"]);
		if (isset($_POST["fristWiderspruchVon"])) $this->SetValue("fristWiderspruchVon", $_POST["fristWiderspruchVon"]);
		if (isset($_POST["fristWiderspruchBis"])) $this->SetValue("fristWiderspruchBis", $_POST["fristWiderspruchBis"]);
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			if (isset($_POST["addressGroupEigentuemer"])) $this->SetValue("addressGroupEigentuemer", $_POST["addressGroupEigentuemer"]);
			if (isset($_POST["addressGroupVerwalter"])) $this->SetValue("addressGroupVerwalter", $_POST["addressGroupVerwalter"]);
			if (isset($_POST["addressGroupAnwalt"])) $this->SetValue("addressGroupAnwalt", $_POST["addressGroupAnwalt"]);
		}
		else
		{
			// Customers only can see uptodate prozesses
			$this->SetValue("addressGroupEigentuemer", "all");
			$this->SetValue("addressGroupVerwalter","all");
			$this->SetValue("addressGroupAnwalt", "all");
		}
		
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
		$elements[] = $this->CreateFormFmsUsers($this->GetValue("rsuser"));
		//Auftragsbeginn von bis
		$elements[] = $this->CreateDatePicker("datumBeauftragungVon", "Auftragsbeginn von", $this->GetValue("datumBeauftragungVon"));
		$elements[] = $this->CreateDatePicker("datumBeauftragungBis", "Auftragsbeginn bis", $this->GetValue("datumBeauftragungBis"));
		$elements[] = $this->CreateDatePicker("datumAbrechnungVon", "Datum Abrechnung von", $this->GetValue("datumAbrechnungVon"));
		$elements[] = $this->CreateDatePicker("datumAbrechnungBis", "Datum Abrechnung bis", $this->GetValue("datumAbrechnungBis"));
		$elements[] = $this->CreateFormYears($this->GetValue("abrechnugsjahre"));
		//TODO: "Gruppe Eigentümer" hinzufügen
		//TODO: "Gruppe Verwalter" hinzufügen
		$elements[] = $this->CreateDatePicker("fristWiderspruchVon", "Frist Widerspruch von", $this->GetValue("fristWiderspruchVon"));
		$elements[] = $this->CreateDatePicker("fristWiderspruchBis", "Frist Widerspruch bis", $this->GetValue("fristWiderspruchBis"));
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$elements[] = $this->CreateFormAddressGroup("addressGroupEigentuemer", "Gruppe Eigentümer", $this->GetValue("addressGroupEigentuemer"));
			$elements[] = $this->CreateFormAddressGroup("addressGroupVerwalter", "Gruppe Verwalter", $this->GetValue("addressGroupVerwalter"));
			$elements[] = $this->CreateFormAddressGroup("addressGroupAnwalt", "Gruppe Anwalt", $this->GetValue("addressGroupAnwalt"));
		}
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
		$columnFilter[]=array("id" => "cb_fms_id", "caption" => CShop::GetAttributeName($this->languageManager, 'RSID'), "sortby" => "RSID", "sorttype" => "str", "visible" => false, "hidden" => true);
		$columnFilter[]=array("id" => "cb_contract_id", "caption" => "Vertrags-ID SFM", "sortby" => "contractID", "sorttype" => "str", "visible" => false, "convert" => "contract_id", "hidden" => true);
		$columnFilter[]=array("id" => "cb_ta_id", "caption" => "TA-ID", "sortby" => "teilabrechnungPkey", "sorttype" => "str", "visible" => false, "convert" => "teilabrechnung_id", "hidden" => true);
		$columnFilter[]=array("id" => "cb_group", "caption" => CGroup::GetAttributeName($this->languageManager, 'name'), "sortby" => "groupName", "sorttype" => "str", "visible" => true, "hidden" => true);
		$columnFilter[]=array("id" => "cb_company", "caption" => CCompany::GetAttributeName($this->languageManager, 'name'), "sortby" => "companyName", "sorttype" => "str", "visible" => false, "hidden" => true);
		$columnFilter[]=Array("id" => "cb_location", "caption" => CLocation::GetAttributeName($this->languageManager, 'name'), "sortby" => "locationName", "sorttype" => "str", "visible" => true, "hidden" => true);
		
		$columnFilter[]=Array("id" => "cb_location_bezeichnung", "caption" => "Objektname Standort", "sortby" => "objectDescription", "sorttype" => "str", "visible" => true, "hidden" => true);
		$columnFilter[]=array("id" => "cb_fms_user", "caption" => "Forderungsmanager SFM", "sortby" => "cPersonRS", "sorttype" => "str", "visible" => false, "hidden" => true);
		$columnFilter[]=array("id" => "cb_ta_bezeichnung", "caption" => "Bezeichnung Abrechnung", "sortby" => "bezeichnung", "sorttype" => "str", "visible" => false, "hidden" => true);
		
		$columnFilter[]=array("id" => "cb_abrechnungsjahr", "caption" => AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr'), "sortby" => "abrechnungJahr", "sorttype" => "num", "visible" => true, "hidden" => true);
		// TODO: FALSCH!!
		$columnFilter[]=array("id" => "cb_datum_beauftragung", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'auftragsdatumAbrechnung'), "sortby" => "auftragsdatumAbrechnung", "sorttype" => "num", "visible" => true, "convert" => "date", "hidden" => true);
		$columnFilter[]=array("id" => "cb_vorauszahlung_kunde", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'vorauszahlungLautKunde'), "sortby" => "vorauszahlungLautKunde", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		$columnFilter[]=array("id" => "cb_vorauszahlung_abrechnung", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'vorauszahlungLautAbrechnung'), "sortby" => "vorauszahlungLautAbrechnung", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		$columnFilter[]=array("id" => "cb_nachzahlung_gutschrift", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'nachzahlungGutschrift'), "sortby" => "nachzahlungGutschrift", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		$columnFilter[]=array("id" => "cb_abschlagszahlung_gutschrift", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'abschlagszahlungGutschrift'), "sortby" => "abschlagszahlungGutschrift", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		$columnFilter[]=array("id" => "cb_korrigiertes_abrechnungsergebnis", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'korrigiertesAbrechnungsergebnis'), "sortby" => "korrigiertesAbrechnungsergebnis", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		$columnFilter[]=array("id" => "cb_datum_abrechnung", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'datum'), "sortby" => "ta_datum", "sorttype" => "num", "visible" => true, "convert" => "date", "hidden" => true);
		
		$columnFilter[]=array("id" => "cb_abrechnungszeitraum_von", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'abrechnungszeitraumVon'), "sortby" => "abrechnungszeitraumVon", "sorttype" => "num", "visible" => true, "convert" => "date", "hidden" => true);
		$columnFilter[]=array("id" => "cb_abrechnungszeitraum_bis", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'abrechnungszeitraumBis'), "sortby" => "abrechnungszeitraumBis", "sorttype" => "num", "visible" => true, "convert" => "date", "hidden" => true);
		
		$columnFilter[]=array("id" => "cb_owner", "caption" => "Firma Eigentümer", "sortby" => "eigentuemer", "sorttype" => "str", "visible" => true, "hidden" => true);
		$columnFilter[]=array("id" => "cb_thruste", "caption" => "Firma Verwalter", "sortby" => "verwalter", "sorttype" => "str", "visible" => true, "hidden" => true);
		$columnFilter[]=array("id" => "cb_advocate", "caption" => "Firma Anwalt", "sortby" => "anwalt", "sorttype" => "str", "visible" => true, "hidden" => true);
		
		$columnFilter[]=array("id" => "cb_schriftverkehr_mit", "caption" => "Schriftverkehr mit", "sortby" => "schriftverkehrMit", "sorttype" => "str", "visible" => true, "convert" => "schriftverkehr_mit", "hidden" => true);
		
		$columnFilter[]=array("id" => "cb_frist_belegeinsicht", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'fristBelegeinsicht'), "sortby" => "fristBelegeinsicht", "sorttype" => "num", "visible" => true, "convert" => "date", "hidden" => true);
		$columnFilter[]=array("id" => "cb_frist_widerspruch", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'fristWiderspruch'), "sortby" => "fristWiderspruch", "sorttype" => "num", "visible" => true, "convert" => "date", "hidden" => true);
		$columnFilter[]=array("id" => "cb_frist_zahlung", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'fristZahlung'), "sortby" => "fristZahlung", "sorttype" => "num", "visible" => true, "convert" => "date", "hidden" => true);
		
		$columnFilter[]=array("id" => "cb_umlageflaeche", "caption" => "Umlagefläche Abrechnung", "sortby" => "umlageflaeche_qm", "sorttype" => "num", "visible" => true, "convert" => "currency_qm", "hidden" => true);
		$columnFilter[]=array("id" => "cb_heizkosten_hvo", "caption" => "Heizkosten nach HVO", "sortby" => "heizkostenNachHVOAbgerechnet", "sorttype" => "num", "visible" => true, "convert" => "bool", "hidden" => true);
		$columnFilter[]=array("id" => "cb_erstabrechnung_oder_nach_umbau", "caption" => "Erstabrechnung oder nach Umbau", "sortby" => "erstabrechnungOderAbrechnungNachUmbau", "sorttype" => "num", "visible" => true, "convert" => "bool", "hidden" => true);
		
		$columnFilter[]=array("id" => "cb_abrechnungstage_gesamt", "caption" => "Abrechnungstage gesamt", "sortby" => "abrechnungszeitraumVon", "sorttype" => "num", "visible" => true, "convert" => "abrechnungstage_gesamt", "hidden" => true);
		$columnFilter[]=array("id" => "cb_hide_settlementdifference", "caption" => "Differenz wg. Abrechnungsfehler", "sortby" => "hideSettlementDifference", "sorttype" => "num", "visible" => true, "convert" => "bool2", "hidden" => true);
				
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
			$data = $this->db->SelectAssoc($query);
			$data2 = Array();
			for ($a=0; $a<count($data); $a++)
			{
				// Daten filtern
				$datumBeauftragungVon = $this->GetValue("datumBeauftragungVon");
				$datumBeauftragungBis = $this->GetValue("datumBeauftragungBis");
				if ($datumBeauftragungVon != "" && strtotime($datumBeauftragungVon) > $data[$a]["auftragsdatumAbrechnung"]) continue;
				if ($datumBeauftragungBis != "" && strtotime($datumBeauftragungBis) < $data[$a]["auftragsdatumAbrechnung"]) continue;
				
				$datumAbrechnungVon = $this->GetValue("datumAbrechnungVon");
				$datumAbrechnungBis = $this->GetValue("datumAbrechnungBis");
				if ($datumAbrechnungVon != "" && strtotime($datumAbrechnungVon) > $data[$a]["datumAbrechnung"]) continue;
				if ($datumAbrechnungBis != "" && strtotime($datumAbrechnungBis) < $data[$a]["datumAbrechnung"]) continue;
				
				$fristWiderspruchVon = $this->GetValue("fristWiderspruchVon");
				$fristWiderspruchBis = $this->GetValue("fristWiderspruchBis");
				if ($fristWiderspruchVon != "" && strtotime($fristWiderspruchVon) > $data[$a]["fristWiderspruch"]) continue;
				if ($fristWiderspruchBis != "" && strtotime($fristWiderspruchBis) < $data[$a]["fristWiderspruch"]) continue;
				
				$data2[] = $data[$a];
			}
			$data = $data2;
			// Namen nachladen
			//$timeBeforeQuery = time();
			for ($a=0; $a<count($data); $a++)
			{
				// Eigentümer
				if ($data[$a]["eigentuemer"]!=-1)
				{
					$adressTemp = null;
					if ($data[$a]["eigentuemerTyp"]==AddressBase::AM_CLASS_ADDRESSDATA) $adressTemp = AddressManager::GetAddressDataByPkey($this->db, (int)$data[$a]["eigentuemer"]);
					elseif ($data[$a]["eigentuemerTyp"]==AddressBase::AM_CLASS_ADDRESSCOMPANY) $adressTemp = AddressManager::GetAddressCompanyByPkey($this->db, (int)$data[$a]["eigentuemer"]);
					elseif ($data[$a]["eigentuemerTyp"]==AddressBase::AM_CLASS_ADDRESSGROUP) $adressTemp = AddressManager::GetAddressGroupByPkey($this->db, (int)$data[$a]["eigentuemer"]);
					if ($adressTemp!=null)
					{
						$data[$a]["eigentuemer"]=$adressTemp->GetCompany();
					}
					else
					{
						$data[$a]["eigentuemer"] = "-";
					}
				}
				else
				{
					$data[$a]["eigentuemer"] = "-";
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
						$data[$a]["verwalter"]=$adressTemp->GetCompany();
					}
					else
					{
						$data[$a]["verwalter"]="-";
					}
				}
				else
				{
					$data[$a]["verwalter"]="-";
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
						$data[$a]["anwalt"]=$adressTemp->GetCompany();
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
					$data[$a]["cPersonRS"]="-";
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
		// build db query
		$query ="SELECT ".Teilabrechnung::TABLE_NAME.".pkey AS teilabrechnungPkey, ".CGroup::TABLE_NAME.".name AS groupName, "
				.CCompany::TABLE_NAME.".name AS companyName, ".CLocation::TABLE_NAME.".objectDescription, ".CShop::TABLE_NAME.".cPersonRS, "
				.CLocation::TABLE_NAME.".name AS locationName, ".AbrechnungsJahr::TABLE_NAME.".pkey AS abrechnungJahrID, "
				.AbrechnungsJahr::TABLE_NAME.".jahr AS abrechnungJahr, ".Contract::TABLE_NAME.".pkey AS contractID, "
				.Contract::TABLE_NAME.".currency , ".CShop::TABLE_NAME.".RSID , "
				.Teilabrechnung::TABLE_NAME.".eigentuemer, ".Teilabrechnung::TABLE_NAME.".eigentuemerTyp, ".Teilabrechnung::TABLE_NAME.".bezeichnung, "
				.Teilabrechnung::TABLE_NAME.".fristWiderspruch, ".Teilabrechnung::TABLE_NAME.".verwalter, ".Teilabrechnung::TABLE_NAME.".verwalterTyp, ".Teilabrechnung::TABLE_NAME.".anwalt, ".Teilabrechnung::TABLE_NAME.".anwaltTyp, "
				.Teilabrechnung::TABLE_NAME.".auftragsdatumAbrechnung, ".Teilabrechnung::TABLE_NAME.".datum AS datumAbrechnung, "
				.Teilabrechnung::TABLE_NAME.".abschlagszahlungGutschrift, ".Teilabrechnung::TABLE_NAME.".vorauszahlungLautKunde, "
				.Teilabrechnung::TABLE_NAME.".vorauszahlungLautAbrechnung, ".Teilabrechnung::TABLE_NAME.".datum AS ta_datum, "
				.Teilabrechnung::TABLE_NAME.".nachzahlungGutschrift, ".Teilabrechnung::TABLE_NAME.".korrigiertesAbrechnungsergebnis, "
				.Teilabrechnung::TABLE_NAME.".schriftverkehrMit, ".Teilabrechnung::TABLE_NAME.".fristZahlung, "
				.Teilabrechnung::TABLE_NAME.".fristBelegeinsicht, ".Teilabrechnung::TABLE_NAME.".heizkostenNachHVOAbgerechnet, "
				.Teilabrechnung::TABLE_NAME.".abrechnungszeitraumVon, ".Teilabrechnung::TABLE_NAME.".abrechnungszeitraumBis, ".Teilabrechnung::TABLE_NAME.".umlageflaeche_qm, "
				.Teilabrechnung::TABLE_NAME.".hideSettlementDifference, ".Teilabrechnung::TABLE_NAME.".erstabrechnungOderAbrechnungNachUmbau ";

		$query.=" FROM ".Teilabrechnung::TABLE_NAME;
		$query.=" LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".Teilabrechnung ::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract =".Contract::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey";
		// build WHERE clause
		$whereClause = $this->BuildWhereClause();
		if ($whereClause!="") $query.=" WHERE ".$whereClause;
		// sort by year
		$query.=" ORDER BY teilabrechnungPkey";
		//echo $query;
		//exit;
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
		if (isset($_POST["addressGroupEigentuemer"]) && $_POST["addressGroupEigentuemer"]!="all" && $_POST["addressGroupEigentuemer"]==(int)$_POST["addressGroupEigentuemer"])
		{
			$eigentuemerGroup= "(SELECT IF(";
			$eigentuemerGroup.=	Teilabrechnung::TABLE_NAME.".eigentuemerTyp=".AddressBase::AM_CLASS_ADDRESSDATA.",";
			$eigentuemerGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup LEFT JOIN ".AddressData::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".pkey=".AddressData::TABLE_NAME.".addressCompany WHERE ".AddressData::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".eigentuemer),";
			$eigentuemerGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup WHERE ".AddressCompany::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".eigentuemer)";
			$eigentuemerGroup.= "))";
			$whereClause.=($whereClause!="" ? " AND " : "").(int)$_POST["addressGroupEigentuemer"]."=".$eigentuemerGroup;
		}
		
		if (isset($_POST["addressGroupVerwalter"]) && $_POST["addressGroupVerwalter"]!="all" && $_POST["addressGroupVerwalter"]==(int)$_POST["addressGroupVerwalter"])
		{
			$verwalterGroup= "(SELECT IF(";
			$verwalterGroup.=	Teilabrechnung::TABLE_NAME.".verwalterTyp=".AddressBase::AM_CLASS_ADDRESSDATA.",";
			$verwalterGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup LEFT JOIN ".AddressData::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".pkey=".AddressData::TABLE_NAME.".addressCompany WHERE ".AddressData::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".verwalter),";
			$verwalterGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup WHERE ".AddressCompany::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".verwalter)";
			$verwalterGroup.= "))";
			$whereClause.=($whereClause!="" ? " AND " : "").(int)$_POST["addressGroupVerwalter"]."=".$verwalterGroup;
		}
		if (isset($_POST["addressGroupAnwalt"]) && $_POST["addressGroupAnwalt"]!="all" && $_POST["addressGroupAnwalt"]==(int)$_POST["addressGroupAnwalt"])
		{
			$anwaltGroup= "(SELECT IF(";
			$anwaltGroup.=	Teilabrechnung::TABLE_NAME.".anwaltTyp=".AddressBase::AM_CLASS_ADDRESSDATA.",";
			$anwaltGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup LEFT JOIN ".AddressData::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".pkey=".AddressData::TABLE_NAME.".addressCompany WHERE ".AddressData::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".anwalt),";
			$anwaltGroup.=	"(SELECT ".AddressGroup::TABLE_NAME.".pkey FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup WHERE ".AddressCompany::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".anwalt)";
			$anwaltGroup.= "))";
			$whereClause.=($whereClause!="" ? " AND " : "").(int)$_POST["addressGroupAnwalt"]."=".$anwaltGroup;
		}
		
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