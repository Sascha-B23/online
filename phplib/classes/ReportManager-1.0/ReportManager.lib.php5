<?php
/**
 * Base class for all reports
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class ReportManager
{

	const REPORT_FORMAT_CSV = 1;
	const REPORT_FORMAT_PDF = 2;
	
	/**
	 * DB-Object
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * User
	 * @var User
	 */
	protected $user = null;
		
	/**
	 * CustomerManager
	 * @var CustomerManager 
	 */
	protected $customerManager = null;
	
	/**
	 * UserManager
	 * @var UserManager 
	 */
	protected $userManager = null;
	
	/**
	 * RSKostenartManager
	 * @var RSKostenartManager 
	 */
	protected $kostenartManager = null;
	
	/**
	 * LanguageManager
	 * @var ExtendedLanguageManager 
	 */
	protected $languageManager = null;
	
	/**
	 * ErrorManager
	 * @var ErrorManager 
	 */
	protected $errorManager = null;
	
	/**
	 * Name of the report
	 * @var string 
	 */
	protected $name = "";
	
	/**
	 * Show the filter
	 * @var boolean 
	 */
	protected $showFilter = false;
	
	/**
	 * Array with all filter settings of the report
	 * @var array 
	 */
	protected $settings = Array();
	
	/**
	 * Flag that indictes if settings should be stored in session
	 * @var boolean 
	 */
	protected $useSession = true;
	
	/**
	 * 
	 * @var string 
	 */
	protected $reportId = "";
	
	/**
	 * Cache of User-Objects
	 * @var User[] 
	 */
	private $userDataCache = Array();
	
	/**
	 * Submit button Text in report template
	 * @var string 
	 */
	protected $action_button_text = "Anzeigen";
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param User $user
	 */
	public function ReportManager(DBManager $db, User $user, CustomerManager $customerManager, AddressManager $addressManager, UserManager $userManager, RSKostenartManager $kostenartManager, ErrorManager $errorManager, ExtendedLanguageManager $languageManager, $reportId, $name)
	{
		$this->db = $db;
		$this->user = $user;
		$this->customerManager = $customerManager;
		$this->addressManager = $addressManager;
		$this->userManager = $userManager;
		$this->kostenartManager = $kostenartManager;
		$this->languageManager = $languageManager;
		$this->errorManager = $errorManager;
		$this->reportId = $reportId;
		$this->name = $name;
		// show filter only for FMS-Employees
		if ($this->user->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$this->showFilter = true;
		}
		// Get default settings
		$this->settings = $this->GetDefaultSetting();
	}
	
	/**
	 * Stream report as file
	 */
	final public function StreamFile()
	{
		// Stream report as File?
		if ($_POST["print"]!="" && (int)$_POST["print"]>0)
		{
			$this->PrepareVars();
			LoggingManager::GetInstance()->Log(new LoggingReport(get_class($this), (int)$_POST["print"], serialize($this->GetSettings(true))));
			return $this->StreamAsFormat((int)$_POST["print"]);
		}
		return true;
	}
	
	/**
	 * Return the default setting
	 * @return array 
	 */
	protected function GetDefaultSetting()
	{
		return Array();
	}
	
	/**
	 * Set Value
	 * @param string $name
	 * @param mixed $value 
	 */
	protected function SetValue($name, $value)
	{
		$this->settings[$name] = $value;
	}
	
	/**
	 * Get Value
	 * @param string $name
	 * @return mixed
	 */
	protected function GetValue($name)
	{
		return $this->settings[$name];
	}
	
	/**
	 * Return the current settings
	 * @return array
	 */
	protected function GetSettings($filteredForLogging = false)
	{
		$returnValue = $this->settings;
		if ($filteredForLogging)
		{
			// unset irrelevant filters to be stored in logs...
			unset($returnValue['addressGroup']);
			unset($returnValue['addressGroupTrustee']);
		}
		return $returnValue;
	}
	
	/**
	 * Prepare the var from POST for the report 
	 * @return boolean 
	 */
	public function PrepareVars()
	{
		if ($this->useSession && $this->reportId!="" && isset($_SESSION["reports"][$this->reportId]))
		{
			$this->settings = $_SESSION["reports"][$this->reportId];
		}
		$returnValue = $this->PrepareSettings();
		if ($returnValue && $this->useSession && $this->reportId!="")
		{
			$_SESSION["reports"][$this->reportId] = $this->settings;
		}
		return $returnValue;
	}
	
	/**
	 * Prepare the vars from POST for the report 
	 * @return boolean 
	 */
	protected function PrepareSettings()
	{
		return true;
	}
	
	/**
	 * Set the use session flag
	 * @param boolean $useSession
	 * @return boolean
	 */
	public function SetUseSession($useSession)
	{
		if (!is_bool($useSession)) return false;
		$this->useSession = $useSession;
		return true;
	}
	
	/**
	 * Set the show filter flag
	 * @param boolean $showFilter 
	 * @return boolean
	 */
	public function SetShowFilter($showFilter)
	{
		if (!is_bool($showFilter)) return false;
		$this->showFilter = $showFilter;
		return true;
	}
	
	/**
	 * return if the filter are shown
	 * @return boolean
	 */
	public function ShowFilter()
	{
		return $this->showFilter;
	}
	
	/**
	 * Return the name of the report
	 * @return string
	 */
	public function GetReportName()
	{
		return $this->name;
	}
	
	/**
	 * Output the report as HTML
	 * @return bool
	 */
	final public function PrintContent()
	{
		LoggingManager::GetInstance()->Log(new LoggingReport(get_class($this), 0, serialize($this->GetSettings(true))));
		include("template_report.inc.php5");
	}
	
	/**
	 * Return if the specified format is supported
	 * @param int $format
	 * @return boolean
	 */
	public function IsFormatSupported($format)
	{
		return false;
	}
	
	/**
	 * Output the report in the specified format
	 * @param int $format
	 * @return bool
	 */
	public function StreamAsFormat($format)
	{
		return false;
	}
	
	/**
	 * Return the column filters
	 * @return array 
	 */
	public function GetColumnFilters()
	{
		return Array();
	}
	
	/**
	 * Return the filter elements
	 * @return FormElement[]
	 */
	abstract public function GetFilterElements();
	
	/**
	 * Output the report as HTML
	 * @return bool
	 */
	abstract public function PrintHtml();

	
	/**
	 * Prepare filter lists for sequencial select of Group -> Company -> Location
	 * @param int $selectedGroup
	 * @param int $selectedCompany
	 * @param int $selectedLocation
	 * @param int $selectedShop
	 * @param int $selectedContract
	 * @return array
	 */
	public function PrepareFilters($selectedGroup=-1, $selectedCompany=-1, $selectedLocation=-1, $selectedShop=-1, $selectedContract=-1, $selectedYear=-1)
	{
		$returnValue = Array();
		// empty options
		$emptyOptions = Array();
		$emptyOptions[] = Array("name" => "Bitte wählen...", "value" => -1);
		// groups
		$options = $emptyOptions;
		$objects = $this->customerManager->GetGroups($this->user, "", "name", 0, 0, 0);
		for ($a=0; $a<count($objects); $a++)
		{
			$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
		}
		$returnValue['groups'] = Array('options' => $options, 'selected' => (int)$selectedGroup);
	
		// companies
		$options = $emptyOptions;
		$currentGroup = ((int)$selectedGroup!=-1 ? $this->customerManager->GetGroupByID($this->user, (int)$selectedGroup) : null);
		if ($currentGroup!=null)
		{
			$objects = $currentGroup->GetCompanys($this->db);
			for ($a=0; $a<count($objects); $a++)
			{
				$options[] = Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$returnValue['companies'] = Array('options' => $options, 'selected' => (int)$selectedCompany);
		
		// locations
		$options = $emptyOptions;
		$currentCompany = ((int)$selectedCompany!=-1 ? $this->customerManager->GetCompanyByID($this->user, (int)$selectedCompany) : null);
		if ($currentCompany!=null)
		{
			$objects = $currentCompany->GetLocations($this->db);
			for ($a=0; $a<count($objects); $a++)
			{
				$options[] = Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$returnValue['locations'] = Array('options' => $options, 'selected' => (int)$selectedLocation);
		
		// shop
		$options = $emptyOptions;
		$currentLocation = ((int)$selectedLocation!=-1 ? $this->customerManager->GetLocationByID($this->user, $selectedLocation) : null);
		if ($currentLocation!=null)
		{
			$objects = $currentLocation->GetShops($this->db);
			for ($a=0; $a<count($objects); $a++)
			{
				$options[] = Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$returnValue['shop'] = Array('options' => $options, 'selected' => (int)$selectedShop);
		
		// contract
		$options = $emptyOptions;
		$currentShop = ((int)$selectedShop!=-1 ? $this->customerManager->GetShopByID($this->user, $selectedShop) : null);
		if ($currentShop!=null)
		{
			$objects = $currentShop->GetContracts($this->db);
			for ($a=0; $a<count($objects); $a++)
			{
				$options[] = Array("name" => "Vertrag ".($objects[$a]->GetLifeOfLeaseString()=='' ? ($a+1) : $objects[$a]->GetLifeOfLeaseString() ), "value" => $objects[$a]->GetPKey());
			}
		}
		$returnValue['contract'] = Array('options' => $options, 'selected' => (int)$selectedContract);
		
		// year
		$options = $emptyOptions;
		$contract = ((int)$selectedContract!=-1 ? $this->kostenartManager->GetContractByID($this->user, $selectedContract) : null);
		if ($contract!=null)
		{
			$objects = $contract->GetAbrechnungsJahre($this->db);
			for ($a=0; $a<count($objects); $a++)
			{
				$options[] = Array("name" => $objects[$a]->GetJahr(), "value" => $objects[$a]->GetPKey());
			}
		}
		$returnValue['year'] = Array('options' => $options, 'selected' => (int)$selectedYear);
		
		return $returnValue;
	}
	
	/**
	 * Create FormElement-Object for 'Prozessstatus'
	 * @global array $processStatusConfig
	 * @param string $selected
	 * @param array $excludeIDs
	 * @return DropdownElement
	 */
	protected function CreateFormProzessstatus($selected="all", $excludeIDs=Array())
	{
		global $processStatusConfig;
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		for ($a=0; $a<count($processStatusConfig); $a++)
		{
			if (in_array((int)$processStatusConfig[$a]["ID"], $excludeIDs)) continue;
			$options[] = Array("name" => WorkflowManager::GetStatusName($this->db, $this->user, $processStatusConfig[$a]["ID"]), "value" => (string)$processStatusConfig[$a]["ID"]);
		}
		// create form element
		$element = new DropdownElement("proccess", WorkflowStatus::GetAttributeName($this->languageManager, 'currentStatus'), $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Standorttyp'
	 * @global array $CM_LOCATION_TYPES
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormStandorttyp($selected="all")
	{
		global $CM_LOCATION_TYPES;
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		for ($a=0; $a<count($CM_LOCATION_TYPES); $a++)
		{
			$options[] = Array("name" => $CM_LOCATION_TYPES[$a]["name"], "value" => (string)$CM_LOCATION_TYPES[$a]["id"]);
		}
		// create form element
		$element = new DropdownElement("standorttyp", CLocation::GetAttributeName($this->languageManager, 'locationType'), $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Gruppe'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormGroups($selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		$groups = $this->customerManager->GetGroups($this->user, "", "name", 0, 0, 0);
		for ($a=0; $a<count($groups); $a++)
		{
			$options[] = Array("name" => $groups[$a]->GetName(), "value" => (string)$groups[$a]->GetPKey());
		}
		// create form element
		$element = new DropdownElement("group", CGroup::GetAttributeName($this->languageManager, 'name'), $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Gruppe'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormCompanies($selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		$companies = $this->customerManager->GetCompanys($this->user, "", CCompany::TABLE_NAME.".name", 0, 0, 0);
		for ($a=0; $a<count($companies); $a++)
		{
			$options[] = Array("name" => $companies[$a]->GetName(), "value" => (string)$companies[$a]->GetPKey());
		}
		// create form element
		$element = new DropdownElement("company", CCompany::GetAttributeName($this->languageManager, 'name'), $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create generic FormElement-Object for time span
	 * @param string $inputName
	 * @param string $inputCaption
	 * @param string $selected
	 * @return DateElement
	 */
	protected function CreateDatePicker($inputName, $inputCaption, $selected="all")
	{
		// create form element
		$element = new DateElement($inputName, $inputCaption, $selected, false, '', null, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create generic FormElement-Object for AddressData
	 * @param int $addressType
	 * @param string $inputName
	 * @param string $inputCaption
	 * @param string $selected
	 * @return DropdownElement
	 */
	private function CreateFormAddressData($addressType, $inputName, $inputCaption, $selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		$addressData = $this->addressManager->GetAddressData("", "company", 0, 0, 0, $addressType);
		for ($a=0; $a<count($addressData); $a++)
		{
			$options[] = Array("name" => $addressData[$a]->GetAddressIDString(), "value" => (string)$addressData[$a]->GetPKey());
		}
		// create form element
		$element = new DropdownElement($inputName, $inputCaption, $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Eigentümer'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormOwner($selected="all")
	{
		return $this->CreateFormAddressData(AM_ADDRESSDATA_TYPE_OWNER, "owner", "Eigentümer", $selected);
	}
	
	/**
	 * Create FormElement-Object for 'Verwalter'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormTrustee($selected="all")
	{
		return $this->CreateFormAddressData(AM_ADDRESSDATA_TYPE_TRUSTEE, "trustee", "Verwalter", $selected);
	}
	
	/**
	 * Create FormElement-Object for 'Anwalt'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormAdvocate($selected="all")
	{
		return $this->CreateFormAddressData(AM_ADDRESSDATA_TYPE_ADVOCATE, "advocate", "Anwalt", $selected);
	}

	/**
	 * Create FormElement-Object for 'Adressgruppe'
	 * @param string $inputName
	 * @param string $inputCaption
	 * @param string $selected
	 * @param array $noselection ["name" => "Keine Auswahl", "value" => "-1"]
	 * @return DropdownElement
	 */
	protected function CreateFormAddressGroup($inputName, $inputCaption, $selected="all", $noselection = null)
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		if ($noselection!=null) $options[] = $noselection;
		$addressGroups = $this->addressManager->GetAddressGroupData();
		for ($a=0; $a<count($addressGroups); $a++)
		{
			$options[] = Array("name" => $addressGroups[$a]->GetName(), "value" => (string)$addressGroups[$a]->GetPKey());
		}
		// create form element
		$element = new DropdownElement($inputName, $inputCaption, $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}

	/**
	 * Create FormElement-Object for 'Adressgruppe'
	 * @param string $inputName
	 * @param string $inputCaption
	 * @param string $selected
	 * @param array $noselection ["name" => "Keine Auswahl", "value" => "-1"]
	 * @return DropdownElement
	 */
	protected function CreateFormAddressGroupMultiselect($inputName, $inputCaption, $selected="all", $noselection = null)
	{
		// selected values
		$values = Array();
		// build options
		$options = Array();
		if ($noselection!=null) $options[] = $noselection;
		for($a=0; $a<count($options);$a++)
		{
			if ($selected=="all" || is_array($selected) && in_array((string)$options[$a]["value"], $selected))  $values[] = (string)$options[$a]["value"];
		}
		$addressGroups = $this->addressManager->GetAddressGroupData();
		for ($a=0; $a<count($addressGroups); $a++)
		{
			$options[] = Array("name" => $addressGroups[$a]->GetName(), "value" => (string)$addressGroups[$a]->GetPKey());
			if ($selected=="all" || is_array($selected) && in_array((string)$addressGroups[$a]->GetPKey(), $selected))  $values[] = (string)$addressGroups[$a]->GetPKey();
		}
		// create form element
		$element = new DropdownElement($inputName, $inputCaption, $values, false, '', $options, true);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create generic FormElement-Object for Users
	 * @param int $userType
	 * @param string $inputName
	 * @param string $inputCaption
	 * @param string $selected
	 * @return DropdownElement
	 */
	private function CreateFormUsers($userType, $inputName, $inputCaption, $selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		$users = $this->userManager->GetUsers($this->user, "", AddressData::TABLE_NAME.".name", 0, 0, 0, $userType);
		for ($a=0; $a<count($users); $a++)
		{
			$options[] = Array("name" => $users[$a]->GetName()." ".$users[$a]->GetFirstName(), "value" => (string)$users[$a]->GetPKey());
		}
		// create form element
		$element = new DropdownElement($inputName, $inputCaption, $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Ansprechpartner FMS'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormFmsUsers($selected="all")
	{
		return $this->CreateFormUsers(UM_GROUP_BASETYPE_RSMITARBEITER, "rsuser", "Forderungsmanager SFM", $selected);
	}
	
	/**
	 * Create FormElement-Object for 'Ansprechpartner FMS'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormFmsLeaderUsers($selected="all")
	{
		return $this->CreateFormUsers(UM_GROUP_BASETYPE_RSMITARBEITER, "cPersonFmsLeader", "Nebenkostenanalyst SFM", $selected);
	}
	
	/**
	 * Create FormElement-Object for 'Ansprechpartner Kunde'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormCustomerUsers($selected="all")
	{
		return $this->CreateFormUsers(UM_GROUP_BASETYPE_KUNDE, "custumeruser", "Ansprechpartner Kunde", $selected);
	}
	
	/**
	 * Create FormElement-Object for 'Standort'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormLocation($selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		$locations = $this->customerManager->GetLocations($this->user, "", CLocation::TABLE_NAME.".name", 0, 0, 0);
		for ($a=0; $a<count($locations); $a++)
		{
			$options[] = Array("name" => $locations[$a]->GetName(), "value" => (string)$locations[$a]->GetPKey());
		}
		// create form element
		$element = new DropdownElement("location", CLocation::GetAttributeName($this->languageManager, 'name'), $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Standort'-Country
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormCountry($selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		$countries = $this->customerManager->GetCountries();
		for ($a=0; $a<count($countries); $a++)
		{
			$options[] = Array("name" => $countries[$a]->GetName(), "value" => (string)$countries[$a]->GetIso3166());
		}
		// create form element
		$element = new DropdownElement("country", CLocation::GetAttributeName($this->languageManager, 'country'), $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Laden'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormShop($selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		$shops = $this->customerManager->GetAllShops($this->user);
		for ($a=0; $a<count($shops); $a++)
		{
			$options[] = Array("name" => $shops[$a]["name"], "value" => (string)$shops[$a]["pkey"]);
		}
		// create form element
		$element = new DropdownElement("shop", CShop::GetAttributeName($this->languageManager, 'name'), $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Kostenart FMS'
	 * @return DropdownElement
	 */
	protected function CreateFormKostenarten()
	{
		// build options
		$options = Array();
		// selected values
		$values = Array();
		$kostenarten = $this->kostenartManager->GetKostenarten("", "name", 0, 0, 0);
		for ($a=0; $a<count($kostenarten); $a++)
		{
			$options[] = Array("name" => $kostenarten[$a]->GetName(), "value" => (string)$kostenarten[$a]->GetPKey());
			$values[] = (string)$kostenarten[$a]->GetPKey();
		}
		// create form element
		$element = new DropdownElement("kostenart", "Kostenart SFM", $values, false, '', $options, true);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Abrechnungsjahr'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormYears($selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		$years = $this->kostenartManager->GetYears($this->user);
		for ($a=0; $a<count($years); $a++)
		{
			$options[] = Array("name" => $years[$a], "value" => (string)$years[$a]);
		}
		// create form element
		$element = new DropdownElement("abrechnugsjahre", AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr'), $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Bearbeitungsstatus'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormArchievedStatus($selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "-1");
		$options[] = Array("name" => "Archivierte", "value" => (string)Schedule::ARCHIVE_STATUS_ARCHIVED);
		$options[] = Array("name" => "Aktuelle Prozesse (noch auf Stand zu bringen)", "value" => (string)Schedule::ARCHIVE_STATUS_UPDATEREQUIRED);
		$options[] = Array("name" => "Aktuelle Prozesse (bereits auf Stand)", "value" => (string)Schedule::ARCHIVE_STATUS_UPTODATE);
		// create form element
		$element = new DropdownElement("showArchievedStatus", Schedule::GetAttributeName($this->languageManager, 'archiveStatus'), $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Realisiert'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormRealisiert($selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "-1");
		$options[] = Array("name" => "Nicht realisiert", "value" => (string)Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO);
		$options[] = Array("name" => "Realisiert", "value" => (string)Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES);
		// create form element
		$element = new DropdownElement("showRealisiert", "Realisiert", $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Realisiert'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormRechnungBezahlt($selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "-1");
		$options[] = Array("name" => "Nein", "value" => 0);
		$options[] = Array("name" => "Ja", "value" => 1);
		// create form element
		$element = new DropdownElement("showRechnungBezahlt", "Rechnung bezahlt", $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Kostenart umlagefähig'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormKostenartUmlagefaehig($selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		$options[] = Array("name" => "Nein", "value" => "2");
		$options[] = Array("name" => "Ja", "value" => "1");
		// create form element
		$element = new DropdownElement("umlagefaehig", "Kostenart umlagefähig", $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}
	
	/**
	 * Create FormElement-Object for 'Ersteinsparung'
	 * @param string $selected
	 * @return DropdownElement
	 */
	protected function CreateFormErsteinsparung($selected="all")
	{
		// build options
		$options = Array();
		$options[] = Array("name" => "Alle", "value" => "all");
		$options[] = Array("name" => "Ersteinsparung", "value" => (string)Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG);
		$options[] = Array("name" => "Folgeeinsparung", "value" => (string)Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG);
		// create form element
		$element = new DropdownElement("showErsteinsparung", "Ersteinsparung", $selected, false, '', $options, false);
		$element->SetWidth(200);
		return $element;
	}

	/**
	 * Create FormElement-Object for 'Nächste Maßnahme'
	 * @param array $excludeIDs
	 * @param mixed $selected
	 * @return DropdownElement
	 */
	protected function CreateFormNaechsteMassnahme($excludeIDs, $selected="all", $selectAllBut = array())
	{
		global $processStatusConfig;
		// build options
		$options = Array();
		// selected values
		$values = Array();
		for ($a=0; $a<count($processStatusConfig); $a++)
		{
			if (in_array((int)$processStatusConfig[$a]["ID"], $excludeIDs)) continue;
			$options[] = Array("name" => WorkflowManager::GetStatusName($this->db, $this->user, $processStatusConfig[$a]["ID"]), "value" => (string)$processStatusConfig[$a]["ID"]);
			if ($selected=="all" && !in_array($processStatusConfig[$a]["ID"], $selectAllBut) || is_array($selected) && in_array($processStatusConfig[$a]["ID"], $selected))  $values[] = (string)$processStatusConfig[$a]["ID"];
		}

		ob_start();
		?>
		function selectLaufendeStandorte(){
			for(var a=0; a<$('naechsteMassnahme[]').length; a++){
				$('naechsteMassnahme[]').options[a].selected = !($('naechsteMassnahme[]').options[a].value==7 || $('naechsteMassnahme[]').options[a].value==26);
			}
		}
		function selectAbgeschlosseneStandorte(){
			for(var a=0; a<$('naechsteMassnahme[]').length; a++){
				$('naechsteMassnahme[]').options[a].selected = ($('naechsteMassnahme[]').options[a].value==7 || $('naechsteMassnahme[]').options[a].value==26);
			}
		}
		<?
		$script = ob_get_contents();
		ob_end_clean();
		//
		$buttons = Array();
		$buttons[] = Array("href" => "javascript: selectLaufendeStandorte();",
			"pic" => 'pics/gui/berichte.png',
			"width" => '25',
			"help" => 'Nur laufende'
		);
		$buttons[] = Array("href" => "javascript: selectAbgeschlosseneStandorte();",
			"pic" => 'pics/gui/finishTask.png',
			"width" => '25',
			"help" => 'Nur abgeschlossene'
		);

		// create form element
		$element = new DropdownElement("naechsteMassnahme", "Nächste Maßnahme", $values, false, '', $options, true, null, $buttons, false, $script);
		$element->SetWidth(200);
		return $element;
	}

	/**
	 * Return the user with the specified pkey
	 * @param int $pkey
	 * @return User
	 */
	protected function GetUserByPkey($pkey)
	{
		if (isset($this->userDataCache[(int)$pkey])) return $this->userDataCache[(int)$pkey];
		$userTemp = new User($this->db);
		if ($userTemp->Load((int)$pkey, $this->db)===true)
		{
			$this->userDataCache[(int)$pkey] = $userTemp;
		}
		else
		{
			$this->userDataCache[(int)$pkey] = null;
		}
		return $this->userDataCache[(int)$pkey];
	}
	
}
?>