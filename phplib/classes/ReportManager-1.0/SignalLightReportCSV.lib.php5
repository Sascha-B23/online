<?php
require_once 'ReportManager.lib.php5';
/**
 * Implementation of report 'Standortvergleich Ampelbewertung'
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class SignalLightReportCSV extends ReportManager
{
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param User $user
	 */
	public function SignalLightReportCSV(DBManager $db, User $user, CustomerManager $customerManager, AddressManager $addressManager, UserManager $userManager, RSKostenartManager $kostenartManager, ErrorManager $errorManager, ExtendedLanguageManager $languageManager)
	{
		parent::__construct($db, $user, $customerManager, $addressManager, $userManager, $kostenartManager, $errorManager, $languageManager, get_class($this), "Berichte > Ampelbewertung CSV");
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
		$defalutSettings["rsuser"] = "all";
		$defalutSettings["location"] = "all";
		$defalutSettings["shop"] = "all";
		$defalutSettings["country"] = "all";
		$defalutSettings["abrechnugsjahre"] = "all";
		$defalutSettings["showErsteinsparung"] = "all";
		$defalutSettings["showRealisiert"] = "-1";
		$defalutSettings["proccess"] = "all";
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$defalutSettings["showArchievedStatus"] = -1;
		}
		else
		{
			$defalutSettings["showArchievedStatus"] = Schedule::ARCHIVE_STATUS_UPTODATE;
		}
		$defalutSettings["addressGroupEigentuemer"] = "all";
		$defalutSettings["addressGroupVerwalter"] = "all";
		$defalutSettings["addressGroupAnwalt"] = "all";
		
		// column filter settings
		$defalutSettings["cb_datum_beauftragung"] = true;
		
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
		if (isset($_POST["rsuser"])) $this->SetValue("rsuser", $_POST["rsuser"]);
		if (isset($_POST["location"])) $this->SetValue("location", $_POST["location"]);
		if (isset($_POST["shop"])) $this->SetValue("shop", $_POST["shop"]);
		if (isset($_POST["country"])) $this->SetValue("country", $_POST["country"]);
		if (isset($_POST["abrechnugsjahre"])) $this->SetValue("abrechnugsjahre", $_POST["abrechnugsjahre"]);
		if (isset($_POST["showErsteinsparung"])) $this->SetValue("showErsteinsparung", $_POST["showErsteinsparung"]);
		if (isset($_POST["showRealisiert"])) $this->SetValue("showRealisiert", $_POST["showRealisiert"]);
		if (isset($_POST["proccess"])) $this->SetValue("proccess", $_POST["proccess"]);
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			if (isset($_POST["showArchievedStatus"])) $this->SetValue("showArchievedStatus", (int)$_POST["showArchievedStatus"]);
			if (isset($_POST["addressGroupEigentuemer"])) $this->SetValue("addressGroupEigentuemer", $_POST["addressGroupEigentuemer"]);
			if (isset($_POST["addressGroupVerwalter"])) $this->SetValue("addressGroupVerwalter", $_POST["addressGroupVerwalter"]);
			if (isset($_POST["addressGroupAnwalt"])) $this->SetValue("addressGroupAnwalt", $_POST["addressGroupAnwalt"]);
		}
		else
		{
			// Customers only can see uptodate prozesses
			$this->SetValue("showArchievedStatus", Schedule::ARCHIVE_STATUS_UPTODATE);
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
		$elements[] = $this->CreateFormFmsUsers($this->GetValue("rsuser"));
		$elements[] = $this->CreateFormLocation($this->GetValue("location"));
		$elements[] = $this->CreateFormShop($this->GetValue("shop"));
		$elements[] = $this->CreateFormCountry($this->GetValue("country"));
		$elements[] = $this->CreateFormYears($this->GetValue("abrechnugsjahre"));
		$elements[] = $this->CreateFormErsteinsparung($this->GetValue("showErsteinsparung"));
		$elements[] = $this->CreateFormRealisiert($this->GetValue("showRealisiert"));
		$elements[] = $this->CreateFormProzessstatus($this->GetValue("proccess"), Array(0,1,2,3));
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$elements[] = $this->CreateFormArchievedStatus($this->GetValue("showArchievedStatus"));
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
		$columnFilter[]=array("id" => "cb_contract_id", "caption" => "Vertrags-ID", "sortby" => "contractID", "sorttype" => "str", "visible" => false, "convert" => "contract_id", "hidden" => true);
		$columnFilter[]=array("id" => "cb_process_id", "caption" => ProcessStatus::GetAttributeName($this->languageManager, 'processStatusId'), "sortby" => "prozessstatusPkey", "sorttype" => "str", "visible" => false, "convert" => "process_id", "hidden" => true);
		$columnFilter[]=array("id" => "cb_widerspruch_id", "caption" => "Widerspruch-ID", "sortby" => "widerspruchsPkey", "sorttype" => "str", "visible" => false, "convert" => "widerspruch_id", "hidden" => true);
		$columnFilter[]=array("id" => "cb_widerspruchspunkt_id", "caption" => "Widerspruchspunkt-ID", "sortby" => "widerspruchspunktPkey", "sorttype" => "str", "visible" => false, "convert" => "widerspruchspunkt_id", "hidden" => true);
		$columnFilter[]=array("id" => "cb_group", "caption" => CGroup::GetAttributeName($this->languageManager, 'name'), "sortby" => "groupName", "sorttype" => "str", "visible" => true, "hidden" => true);
		$columnFilter[]=array("id" => "cb_company_name", "caption" => CCompany::GetAttributeName($this->languageManager, 'name'), "sortby" => "companyName", "sorttype" => "str", "visible" => true, "hidden" => true);
		$columnFilter[]=Array("id" => "cb_location", "caption" => CLocation::GetAttributeName($this->languageManager, 'name'), "sortby" => "locationName", "sorttype" => "str", "visible" => true, "hidden" => true);
		$columnFilter[]=array("id" => "cb_shop", "caption" => CShop::GetAttributeName($this->languageManager, 'name'), "sortby" => "shopName", "sorttype" => "str", "visible" => true, "hidden" => true);
		$columnFilter[]=array("id" => "cb_location_country", "caption" => CLocation::GetAttributeName($this->languageManager, 'country'), "sortby" => "country", "sorttype" => "str", "visible" => true, "hidden" => true);
		$columnFilter[]=array("id" => "cb_location_type", "caption" => CLocation::GetAttributeName($this->languageManager, 'locationType'), "sortby" => "locationType", "sorttype" => "str", "visible" => true, "hidden" => false);
		$columnFilter[]=array("id" => "cb_abrechnungsjahr", "caption" => AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr'), "sortby" => "abrechnungJahr", "sorttype" => "num", "visible" => true, "hidden" => true);
		$columnFilter[]=array("id" => "cb_datum_beauftragung", "caption" => Teilabrechnung::GetAttributeName($this->languageManager, 'auftragsdatumAbrechnung'), "sortby" => "auftragsdatumAbrechnung", "sorttype" => "num", "visible" => true, "convert" => "date", "hidden" => false);
		$columnFilter[]=array("id" => "cb_auftragsende", "caption" => ProcessStatus::GetAttributeName($this->languageManager, 'zahlungsdatum'), "sortby" => "zahlungsdatum", "sorttype" => "num", "visible" => false, "convert" => "date");
		$columnFilter[]=Array("id" => "cb_rechnungsdatum", "caption" => "Rechnungsdatum", "sortby" => "paymentDate", "sorttype" => "num", "visible" => false, "convert" => "date", "hidden" => false);
		$columnFilter[]=Array("id" => "cb_rechnung_bezahlt", "caption" => "Rechnung bezahlt", "sortby" => "paymentReceived", "sorttype" => "num", "visible" => false, "hidden" => false, "convert" => "bool");
		$columnFilter[]=Array("id" => "cb_zahlungsdatum", "caption" => "Zahlungsdatum", "sortby" => "paymentDate", "sorttype" => "num", "visible" => false, "hidden" => false, "convert" => "plus_14_days");
		$columnFilter[]=array("id" => "cb_curreny", "caption" => "Währung Vertrag", "sortby" => "currency", "sorttype" => "str", "visible" => false, "hidden" => true);
		$columnFilter[]=array("id" => "cb_ueberschrift", "caption" => "Bezeichnung Widerspruchspunkt", "sortby" => "title", "sorttype" => "str", "visible" => false, "hidden" => true);
		$columnFilter[]=Array("id" => "cb_ws_volume_green", "caption" => "WS-Volumen grün", "sortby" => "gruen", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		$columnFilter[]=Array("id" => "cb_ws_volume_yellow", "caption" => "WS-Volumen gelb", "sortby" => "gelb", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		$columnFilter[]=Array("id" => "cb_ws_volume_red", "caption" => "WS-Volumen rot", "sortby" => "rot", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		$columnFilter[]=Array("id" => "cb_ws_volume_grey", "caption" => "WS-Volumen grau", "sortby" => "grau", "sorttype" => "num", "visible" => true, "convert" => "currency", "hidden" => true);
		
		$columnFilter[]=Array("id" => "cb_ersteinsparung", "caption" => "Ersteinsparung", "sortby" => "erstFolgeEinsparung", "sorttype" => "num", "visible" => true, "convert" => "bool", "hidden" => true);
		$columnFilter[]=Array("id" => "cb_realisiert", "caption" => "Realisiert", "sortby" => "realisiert", "sorttype" => "num", "visible" => true, "convert" => "bool2", "hidden" => true);
		
		$columnFilter[]=array("id" => "cb_customer_id", "caption" => CShop::GetAttributeName($this->languageManager, 'internalShopNo'), "sortby" => "internalShopNo", "sorttype" => "str", "visible" => true, "hidden" => false);
		$columnFilter[]=array("id" => "cb_fms_user", "caption" => "Forderungsmanager SFM", "sortby" => "cPersonRS", "sorttype" => "str", "visible" => false, "hidden" => true);
		
		$columnFilter[]=array("id" => "cb_eigentuemer", "caption" => "Gruppe Eigentümer", "sortby" => "eigentuemerGroupName", "sorttype" => "str", "visible" => false, "hidden" => true, "convert" => "pkey_string");
		$columnFilter[]=array("id" => "cb_verwalter", "caption" => "Gruppe Verwalter", "sortby" => "verwalterGroupName", "sorttype" => "str", "visible" => false, "hidden" => true, "convert" => "pkey_string");
		$columnFilter[]=array("id" => "cb_anwalt", "caption" => "Gruppe Anwalt", "sortby" => "anwaltGroupName", "sorttype" => "str", "visible" => false, "hidden" => true, "convert" => "pkey_string");
		
		$columnFilter[]=Array("id" => "cb_mietvertragsbeginn", "caption" => "Mietbeginn Vertrag", "sortby" => "mvBeginn", "sorttype" => "num", "visible" => true, "convert" => "date", "hidden" => false);
		$columnFilter[]=Array("id" => "cb_erstmalsmoeglich_mietende", "caption" => "Erstmals mögliches Mietende Vertrag", "sortby" => "mvEndeErstmalsMoeglich", "sorttype" => "num", "visible" => true, "convert" => "date", "hidden" => false);
		$columnFilter[]=Array("id" => "cb_mietende_vertrag", "caption" => "Aktuelles Mietende Vertrag", "sortby" => "mvEnde", "sorttype" => "num", "visible" => true, "convert" => "date", "hidden" => false);
		
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
		return false;
	}
	
	/**
	 * removes double entries in an array by a specified key
	 * @return array
	 */
	private function removeDublicates($arr, $key)
	{
		return $arr;
		$keylist = array();
		$result = array();
		foreach ($arr as $value)
		{
			if(isset($keylist[$value[$key]])) continue;
			$keylist[$value[$key]] = true;
			$result[] = $value;
		}
		return $result;
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
		global $CM_LOCATION_TYPES;
		
		//query all combinations of Erst-/Folgeeinsparung and Realisiert/Nicht Realisiet and merge the results into one array
		$data=Array();
		if($_POST["showErsteinsparung"]!=Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG && $_POST["showRealisiert"]!=Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO)
		{
			$query = $this->BuildSqlQuery(Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG, Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES);
			$data = array_merge($data, $this->removeDublicates($this->db->SelectAssoc($query), "widerspruchspunktPkey"));
		}
		if($_POST["showErsteinsparung"]!=Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG && $_POST["showRealisiert"]!=Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES)
		{
			$query = $this->BuildSqlQuery(Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG, Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO);
			$data = array_merge($data, $this->removeDublicates($this->db->SelectAssoc($query), "widerspruchspunktPkey"));
		}
		if($_POST["showErsteinsparung"]!=Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG && $_POST["showRealisiert"]!=Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO)
		{
			$query = $this->BuildSqlQuery(Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG, Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES);
			$data = array_merge($data, $this->removeDublicates($this->db->SelectAssoc($query), "widerspruchspunktPkey"));
		}
		if($_POST["showErsteinsparung"]!=Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG && $_POST["showRealisiert"]!=Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES)
		{
			$query = $this->BuildSqlQuery(Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG, Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO);
			$data = array_merge($data, $this->removeDublicates($this->db->SelectAssoc($query), "widerspruchspunktPkey"));
		}
		
		//Namen nachladen
		for ($a=0; $a<count($data); $a++)
		{
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
			
			//Standorttyp
			$data[$a]["locationType"] = $CM_LOCATION_TYPES[(int)$data[$a]["locationType"]]["name"];
		}
		
		return $data;
	}
	
	/**
	 * Build the SQL-Query string
	 * @return string
	 */
	private function BuildSqlQuery($ersteinsparung, $realisiert)
	{
		// build db query
		$query ="SELECT ".Widerspruchspunkt::TABLE_NAME.".pkey AS widerspruchspunktPkey, ".Widerspruch::TABLE_NAME.".pkey AS widerspruchsPkey, ".CShop::TABLE_NAME.".pkey AS shopID, "
				.CShop::TABLE_NAME.".RSID, ".Contract::TABLE_NAME.".pkey AS contractID, "
				.CGroup::TABLE_NAME.".name AS groupName, ".CCompany::TABLE_NAME.".name AS companyName, "
				.CLocation::TABLE_NAME.".name AS locationName, ".CShop::TABLE_NAME.".name AS shopName, "
				.CLocation::TABLE_NAME.".country, ".CLocation::TABLE_NAME.".locationType, "
				.AbrechnungsJahr::TABLE_NAME.".pkey AS abrechnungJahrID, ".AbrechnungsJahr::TABLE_NAME.".jahr AS abrechnungJahr, "
				.Teilabrechnung::TABLE_NAME.".auftragsdatumAbrechnung, ".ProcessStatus::TABLE_NAME.".zahlungsdatum, "
				.ProcessStatus::TABLE_NAME.".pkey AS prozessstatusPkey, ".Teilabrechnung::TABLE_NAME.".verwalter, ".Teilabrechnung::TABLE_NAME.".verwalterTyp, "
				.Teilabrechnung::TABLE_NAME.".anwalt, ".Teilabrechnung::TABLE_NAME.".anwaltTyp, ".Teilabrechnung::TABLE_NAME.".eigentuemer, ".Teilabrechnung::TABLE_NAME.".eigentuemerTyp, "
				.Teilabrechnung::TABLE_NAME.".datum AS rechnungsDatum, ".Widerspruch::TABLE_NAME.".paymentReceived, ".Widerspruch::TABLE_NAME.".paymentDate, "
				.Widerspruchspunkt::TABLE_NAME.".title, ".Kuerzungsbetrag::TABLE_NAME.".type AS erstFolgeEinsparung, "
				.Kuerzungsbetrag::TABLE_NAME.".realisiert, ".CShop::TABLE_NAME.".internalShopNo, "
				.CShop::TABLE_NAME.".cPersonRS, ".Contract::TABLE_NAME.".currency, ".Contract::TABLE_NAME.".mvBeginn, "
				.Contract::TABLE_NAME.".mvEndeErstmalsMoeglich, ".Contract::TABLE_NAME.".mvEnde, ";
		
		$query.= "(SELECT IF(";
		$query.=	Teilabrechnung::TABLE_NAME.".eigentuemerTyp=".AddressBase::AM_CLASS_ADDRESSDATA.",";
		$query.=	"(SELECT ".AddressGroup::TABLE_NAME.".name FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup LEFT JOIN ".AddressData::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".pkey=".AddressData::TABLE_NAME.".addressCompany WHERE ".AddressData::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".eigentuemer),";
		$query.=	"(SELECT ".AddressGroup::TABLE_NAME.".name FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup WHERE ".AddressCompany::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".eigentuemer)";
		$query.= ")) AS eigentuemerGroupName, ";
		
		$query.= "(SELECT IF(";
		$query.=	Teilabrechnung::TABLE_NAME.".verwalterTyp=".AddressBase::AM_CLASS_ADDRESSDATA.",";
		$query.=	"(SELECT ".AddressGroup::TABLE_NAME.".name FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup LEFT JOIN ".AddressData::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".pkey=".AddressData::TABLE_NAME.".addressCompany WHERE ".AddressData::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".verwalter),";
		$query.=	"(SELECT ".AddressGroup::TABLE_NAME.".name FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup WHERE ".AddressCompany::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".verwalter)";
		$query.= ")) AS verwalterGroupName, ";
		
		$query.= "(SELECT IF(";
		$query.=	Teilabrechnung::TABLE_NAME.".anwaltTyp=".AddressBase::AM_CLASS_ADDRESSDATA.",";
		$query.=	"(SELECT ".AddressGroup::TABLE_NAME.".name FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup LEFT JOIN ".AddressData::TABLE_NAME." ON ".AddressCompany::TABLE_NAME.".pkey=".AddressData::TABLE_NAME.".addressCompany WHERE ".AddressData::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".anwalt),";
		$query.=	"(SELECT ".AddressGroup::TABLE_NAME.".name FROM ".AddressGroup::TABLE_NAME." LEFT JOIN ".AddressCompany::TABLE_NAME." ON ".AddressGroup::TABLE_NAME.".pkey=".AddressCompany::TABLE_NAME.".addressGroup WHERE ".AddressCompany::TABLE_NAME.".pkey=".Teilabrechnung::TABLE_NAME.".anwalt)";
		$query.= ")) AS anwaltGroupName, ";
		
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE widerspruchspunktPkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt AND ".Kuerzungsbetrag::TABLE_NAME.".type=".$ersteinsparung." AND ".Kuerzungsbetrag::TABLE_NAME.".realisiert=".$realisiert." AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN.") AS gruen, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE widerspruchspunktPkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt AND ".Kuerzungsbetrag::TABLE_NAME.".type=".$ersteinsparung." AND ".Kuerzungsbetrag::TABLE_NAME.".realisiert=".$realisiert." AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT.") AS rot, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE widerspruchspunktPkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt AND ".Kuerzungsbetrag::TABLE_NAME.".type=".$ersteinsparung." AND ".Kuerzungsbetrag::TABLE_NAME.".realisiert=".$realisiert." AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB.") AS gelb, ";
		$query.=" (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE widerspruchspunktPkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt AND ".Kuerzungsbetrag::TABLE_NAME.".type=".$ersteinsparung." AND ".Kuerzungsbetrag::TABLE_NAME.".realisiert=".$realisiert." AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU.") AS grau ";
		
		$query.=" FROM ".Widerspruchspunkt::TABLE_NAME;
		$query.=" LEFT JOIN ".Kuerzungsbetrag::TABLE_NAME." ON ".Widerspruchspunkt ::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt";
		$query.=" LEFT JOIN ".Widerspruch::TABLE_NAME." ON ".Widerspruchspunkt ::TABLE_NAME.".widerspruch=".Widerspruch::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".Widerspruch ::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".ProcessStatus::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".pkey =".ProcessStatus::TABLE_NAME.".abrechnungsjahr";
		$query.=" LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON ".Teilabrechnung::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey AND ".Teilabrechnung::TABLE_NAME.".pkey=".ProcessStatus::TABLE_NAME.".currentTeilabrechnung";
		$query.=" LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract =".Contract::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey";
		// build WHERE clause
		$whereClause = $this->BuildWhereClause($ersteinsparung, $realisiert);
		if ($whereClause!="") $query.=" WHERE ".$whereClause;
		// sort by year
		$query.=" GROUP BY widerspruchspunktPkey ORDER BY widerspruchspunktPkey";
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
	 * Helper to add terms to the WHERE-clause as string
	 * @param string $whereClause
	 * @param string $postVarName
	 * @param string $columnName
	 * @param string $allValue
	 */
	private function AppendStringToWhereClause(&$whereClause, $postVarName, $columnName, $allValue="all")
	{
		if (isset($_POST[$postVarName]) && $_POST[$postVarName]!=$allValue && is_string($_POST[$postVarName]))
		{
			$whereClause.=($whereClause!="" ? " AND " : "").$columnName."='".$_POST[$postVarName]."'";
		}
	}
	
	/**
	 * Build the db WHERE-clause
	 * @return string
	 */
	private function BuildWhereClause($ersteinsparung, $realisiert)
	{		
		$whereClause = "";
		$this->AppendToWhereClause($whereClause, "group", CGroup::TABLE_NAME.".pkey");
		$this->AppendToWhereClause($whereClause, "company", CCompany::TABLE_NAME.".pkey");
		$this->AppendToWhereClause($whereClause, "rsuser", CShop::TABLE_NAME.".cPersonRS");
		$this->AppendToWhereClause($whereClause, "location", CLocation::TABLE_NAME.".pkey");
		$this->AppendToWhereClause($whereClause, "shop", CShop::TABLE_NAME.".pkey");
		$this->AppendToWhereClause($whereClause, "abrechnugsjahre", AbrechnungsJahr::TABLE_NAME.".jahr");
		$this->AppendToWhereClause($whereClause, "proccess", ProcessStatus::TABLE_NAME.".currentStatus");
		$this->AppendToWhereClause($whereClause, "showArchievedStatus", ProcessStatus::TABLE_NAME.".archiveStatus", "-1");
		$this->AppendStringToWhereClause($whereClause, "country", CLocation::TABLE_NAME.".country");
		
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

		
		//Erst-/Folgeeinsparungen, realisiert/nicht realisiert		
		$whereClause.=($whereClause!="" ? " AND " : "").Kuerzungsbetrag::TABLE_NAME.".type=".$ersteinsparung;
		$whereClause.=" AND ".Kuerzungsbetrag::TABLE_NAME.".realisiert=".$realisiert;
		
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
		$columnFilterConfig = $this->GetColumnFilters();
		for($a=0; $a<count($columnFilterConfig); $a++){
			if( !$columnFilterConfig[$a]["visible"] )continue;
			$myCSVString .= str_replace(";", ",", $columnFilterConfig[$a]["caption"]).$sep;
		}
		$myCSVString.=$endline;
		$data = $this->GetReportData();
		$listData=new BereichtStandortvergleichProzessstatusListData($this->db, $columnFilterConfig, $data);
		$data=$listData->Search("", 3, $_POST["order_direction_0"]);
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
		header("Content-Disposition: attachment; filename=\"Ampelbewertung.csv\"");
		header("Content-Length: ".(string)strlen("\xEF\xBB\xBF".$myCSVString));
		echo "\xEF\xBB\xBF".$myCSVString;
		exit;
	}
	
}
?>