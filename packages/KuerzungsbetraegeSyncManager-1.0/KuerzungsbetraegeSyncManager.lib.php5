<?php
include_once(__DIR__.'/../SyncManager-1.0/SyncManager.lib.php5');
/**
 * Base class to sync csv files with database
 * 
 * @access   	public
 * @author   	Nico Gerwien <n.gerwien@stollvongati.com>
 *
 * @since		PHP 5.4
 * @version	 1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class KuerzungsbetraegeSyncManager extends SyncManager
{
	const COLUMN_ID_FMSID = 1;
	const COLUMN_ID_CONTRACTID = 2;
	const COLUMN_ID_PROCESSID = 3;
	const COLUMN_ID_WIDERSPRUCHID = 4;
	const COLUMN_ID_WIDERSPRUCHPUNKTID = 5;
	const COLUMN_ID_KUERZUNGSBETRAGID = 6;
	const COLUMN_ID_GROUPNAME = 7;
	const COLUMN_ID_COMPANYNAME = 8;
	const COLUMN_ID_LOCATIONNAME = 9;
	const COLUMN_ID_SHOPNAME = 10;
	const COLUMN_ID_COUNTRY = 11;
	const COLUMN_ID_ABRECHNUNGSJAHR = 12;
	const COLUMN_ID_CURRENCY = 13;
	const COLUMN_ID_WSNAME = 14;
	const COLUMN_ID_KUERZUNGSBETRAG = 15;
	const COLUMN_ID_EINSTUFUNG = 16;
	const COLUMN_ID_ERSTEINSPARUNG = 17;
	const COLUMN_ID_REALISIERT = 18;
	const COLUMN_ID_GROUPID = 19;
	const COLUMN_ID_CATEGORIZATION = 20;
	const COLUMN_ID_STICHTAG_ZIELGELDEINGANG = 21;
	const COLUMN_ID_CPERSONRS = 22;
	const COLUMN_ID_GRUPPE_VERWALTER = 23;

	/**
	 * KostenartManager
	 * @var RSKostenartManager 
	 */
	protected $manager = null;
	
	/**
	 * CustomerManager
	 * @var CustomerManager 
	 */
	protected $customerManager = null;
	
	/**
	 * AddressManager
	 * @var AddressManager 
	 */
	protected $addressManager = null;
	
	/**
	 * Teilabrechnungspositionen
	 * @var Teilabrechnungsposition[] 
	 */
	protected $teilabrechnungspositionen = Array();

	/**
	 * TAPReport for fast export (workaround)
	 * @var TAPReport 
	 */
	protected $tapReport = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 */
	public function KuerzungsbetraegeSyncManager(DBManager $db, ExtendedLanguageManager $languageManager)
	{
		// init needed managers
		$this->manager = new RSKostenartManager($db);
		$this->customerManager = new CustomerManager($db);
		$this->addressManager = new AddressManager($db);
		
		// init TAP-Report for fast export (workaround)
		global $userManager, $em, $lm;
		$this->tapReport = new KuerzungsbetragReportCSV($db, $_SESSION["currentUser"], $this->customerManager, $this->addressManager, $userManager, $this->manager, $em, $lm);
		
		// call parent constructor
		parent::__construct($db, $languageManager);
	}
	
	/**
	 * Initialize the column objects for this SyncManager
	 */
	protected function InitColumns()
	{
		// build columns
		$this->columns[] = new SyncColumn(self::COLUMN_ID_FMSID, CShop::GetAttributeName($this->languageManager, 'RSID'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_CONTRACTID, "Vertrags-ID", false, '', new SyncDataCellId(false, Contract::ID_PREFIX));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_GROUPID, "Paket-ID", true, '', new SyncDataCellId(true, 'GID'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_PROCESSID, ProcessStatus::GetAttributeName($this->languageManager, 'processStatusId'), false, '', new SyncDataCellId(false, 'P'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_WIDERSPRUCHID, "Widerspruch-ID", false, '', new SyncDataCellId(false, 'WS'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_WIDERSPRUCHPUNKTID, "Widerspruchspunkt-ID", false, '', new SyncDataCellId(false, 'WSP'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_KUERZUNGSBETRAGID, "Kürzungsbetrag-ID", false, '', new SyncDataCellId(false, 'KB'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_GROUPNAME, CGroup::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYNAME, CCompany::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_LOCATIONNAME, CLocation::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_SHOPNAME, CShop::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_CPERSONRS, "Forderungsmanager SFM", false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_GRUPPE_VERWALTER, "Gruppe Verwalter", true);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COUNTRY, CLocation::GetAttributeName($this->languageManager, 'country'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_ABRECHNUNGSJAHR, AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr'), false, "", new SyncDataCellInteger(false, false));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_CURRENCY, "Währung Vertrag", false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_WSNAME, "Bezeichnung Widerspruchspunkt", false);

		$this->columns[] = new SyncColumn(self::COLUMN_ID_KUERZUNGSBETRAG, "Kürzungsbetrag", false, "", new SyncDataCellFloat(true, true));
		$options = Array();
		$options[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_UNKNOWN] = Kuerzungsbetrag::GetRatingName(Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_UNKNOWN);
		$options[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN] = Kuerzungsbetrag::GetRatingName(Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN);
		$options[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB] = Kuerzungsbetrag::GetRatingName(Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB);
		$options[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT] = Kuerzungsbetrag::GetRatingName(Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT);
		$options[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU] = Kuerzungsbetrag::GetRatingName(Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_EINSTUFUNG, "Ampelfarbe", false, "", new SyncDataCellIdToTextMap($options));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_ERSTEINSPARUNG, "Ersteinsparung", true, "", 	new SyncDataCellBoolean('Ja', 'Nein', SyncDataCellBoolean::VALUE_TRUE));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_REALISIERT, "Realisiert", false, "", new SyncDataCellBoolean('Ja', 'Nein', SyncDataCellBoolean::VALUE_TRUE));
		$options = Array();
		$options[Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_UNKNOWN] = Kuerzungsbetrag::GetCategorizationName(Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_UNKNOWN);
		$options[Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_CLEARONBOTHSIDES] = Kuerzungsbetrag::GetCategorizationName(Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_CLEARONBOTHSIDES);
		$options[Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_POTENTIALLYCLEARONBOTHSIDES] = Kuerzungsbetrag::GetCategorizationName(Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_POTENTIALLYCLEARONBOTHSIDES);
		$options[Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_ONESIDEDCLEAR] = Kuerzungsbetrag::GetCategorizationName(Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_ONESIDEDCLEAR);
		$options[Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_TOBECLARIFIED] = Kuerzungsbetrag::GetCategorizationName(Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_TOBECLARIFIED);
		$options[Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_DEPOWEREDPOINT] = Kuerzungsbetrag::GetCategorizationName(Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_DEPOWEREDPOINT);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_CATEGORIZATION, "Einstufung", false, "", new SyncDataCellIdToTextMap($options));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_STICHTAG_ZIELGELDEINGANG, "Stichtag Zielgeldeingang", true, "", new SyncDataCellDate(true));
	}
	
	/**
	 * Return data as CSV string
	 * @return string
	 */
	public function GetExportContent()
	{
		// Log export
		LoggingManager::GetInstance()->Log(new LoggingSyncManager(get_class($this), LoggingSyncManager::TYPE_EXPORT));
		// Stream data from TAP-Report
		$_POST["show"] = true;
		return $this->tapReport->StreamAsFormat(ReportManager::REPORT_FORMAT_CSV);
	}
	
	/**
	 * Initialize 
	 */
	protected function LoadDataFromDatabase()
	{
		// this function is disabled -> fn GetExportContent() is overwritten to use TAPReport for CSV-Export (performance reasons)
		echo "Function is disabled!!";
		exit;
	}
	
	/**
	 * Return an new instance of the data object
	 * @param DBManager $db
	 * @param SyncDataRow $dataRow
	 * @return DBEntry
	 */
	protected function CreateNewDataObject(DBManager $db, SyncDataRow $dataRow)
	{
		echo "This importer does not support creating new entries";
		exit;

	}
	
	/**
	 * Return the value of the object for the specified column
	 * @param int $columnId
	 * @param DBEntry | Teilabrechnungsposition $object
	 * @return mixed
	 */
	protected function GetValueForColumnFromObject($columnId, DBEntry $object)
	{
		/*@var $object Teilabrechnungsposition */
		
		// Get all data objects
		/** @var $teilabrechnung Teilabrechnung */
		$teilabrechnung = $object->GetTeilabrechnung($this->db);
		$abrechnungsJahr = $teilabrechnung->GetAbrechnungsJahr();
		$contract = $abrechnungsJahr->GetContract();
		$shop = $contract->GetShop();
		$location = ($shop!=null ? $shop->GetLocation() : null);
		$company = ($location!=null ? $location->GetCompany() : null);
		$group = ($company!=null ? $company->GetGroup() : null);
		
		switch($columnId)
		{
			case self::COLUMN_ID_FMSID:
				return ($shop!=null ? $shop->GetRSID() : "-");
			case self::COLUMN_ID_CONTRACTID:
				return Contract::ID_PREFIX.$contract->GetPKey();
			case self::COLUMN_ID_TEILABRECHNUNGID:
				return Teilabrechnung::ID_PREFIX.$teilabrechnung->GetPKey();
			case self::COLUMN_ID_TEILABRECHNUNGSPOSITIONID:
				return Teilabrechnungsposition::ID_PREFIX.$object->GetPKey();
			case self::COLUMN_ID_GROUPNAME:
				return ($group!=null ? $group->GetName() : "-");
			case self::COLUMN_ID_COMPANYNAME:
				return ($company!=null ? $company->GetName() : "-");
			case self::COLUMN_ID_LOCATIONNAME:
				return ($location!=null ? $location->GetName() : "-");
			case self::COLUMN_ID_SHOPNAME:
				return ($shop!=null ? $shop->GetName() : "-");
			case self::COLUMN_ID_CPERSONRS:
				/** @var $shop CShop */
				return ($shop!=null && $shop->GetCPersonRS()!=null ? $shop->GetCPersonRS()->GetName()." ". $shop->GetCPersonRS()->GetFirstName() : "-");
			case self::COLUMN_ID_ABRECHNUNGSJAHR:
				return (int)$abrechnungsJahr->GetJahr();
			case self::COLUMN_ID_ABRECHNUNGSNAME:
				return $teilabrechnung->GetBezeichnung();
			case self::COLUMN_ID_AREANAME:
				return $object->GetBezeichnungTeilflaech();
			case self::COLUMN_ID_GESAMTEINHEITEN:
				return (float)$object->GetGesamteinheiten();
			case self::COLUMN_ID_GESAMTEINHEITENEINHEIT:
				return (int)$object->GetGesamteinheitenEinheit();
			case self::COLUMN_ID_EINHEITKUNDE:
				return (float)$object->GetEinheitKunde();
			case self::COLUMN_ID_EINHEITKUNDEEINHEIT:
				return (int)$object->GetEinheitKundeEinheit();
            case self::COLUMN_ID_PAUSCHALE:
                return  $object->IsPauschale() ? SyncDataCellBoolean::VALUE_TRUE : SyncDataCellBoolean::VALUE_FALSE;
			case self::COLUMN_ID_GESAMTBETRAG:
				return (float)$object->GetGesamtbetrag();
			case self::COLUMN_ID_BETRAG_KUNDE:
				return (float)$object->GetBetragKunde();
			case self::COLUMN_ID_UMLAGEFAEHIG:
				return (int)$object->GetUmlagefaehig();
			case self::COLUMN_ID_ERFASST:
				return $teilabrechnung->GetErfasst() ? SyncDataCellBoolean::VALUE_TRUE : SyncDataCellBoolean::VALUE_FALSE;
			case self::COLUMN_ID_STICHTAG_ZIELGELDEINGANG:
				return (int)$object->GetStichtagZielgeldeingang();
			case self::COLUMN_ID_GRUPPE_VERWALTER:
				return $teilabrechnung!=null && $teilabrechnung->GetVerwalter()!=null ? $teilabrechnung->GetVerwalter()->GetOverviewString() : "-";
		}
		return "[ERROR: UNKNOWN COLUMN ID '".$columnId."']";
	}
	
	/**
	 * Return the value of the object for the specified column
	 * @param int $columnId
	 * @param SyncDataCell $dataCell
	 * @param DBEntry $object
     * @param SyncDataRow $dataRow
	 * @return mixed
	 */
	protected function HasValueChanged($columnId, SyncDataCell $dataCell, DBEntry $object, SyncDataRow $dataRow)
	{
        // Skip this data cell by pre condition ?
        if ($dataCell->SkipByPreConditions($dataRow)) return false;

		/* @var $object Kuerzungsbetrag */
		switch($columnId)
		{
			case self::COLUMN_ID_KUERZUNGSBETRAG:
				return $dataCell->GetValue()!=(float)$object->GetKuerzungsbetrag();
			case self::COLUMN_ID_EINSTUFUNG:
				return $dataCell->GetValue()!=(int)$object->GetRating();
			case self::COLUMN_ID_ERSTEINSPARUNG:
				return $dataCell->GetValue()!=(int)$object->GetType();
			case self::COLUMN_ID_REALISIERT:
				return $dataCell->GetValue()!=($object->GetRealisiert()==Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO ? SyncDataCellBoolean::VALUE_FALSE : SyncDataCellBoolean::VALUE_TRUE);
			case self::COLUMN_ID_CATEGORIZATION:
				return $dataCell->GetValue()!=(int)$object->GetCategorization();
			case self::COLUMN_ID_STICHTAG_ZIELGELDEINGANG:
				return $dataCell->GetValue()!=(int)$object->GetStichtagZielgeldeingang();
		}
	}
	
	/**
	 * get the object for the data row
	 * @param SyncDataRow $dataRow
	 * @return AddressCompany
	 */
	protected function GetDataRowObject(SyncDataRow $dataRow)
	{
		$objectInstance = null;
		// check if id is set
		$idDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_KUERZUNGSBETRAGID);
		$id = trim($idDataCell->GetValue());
		if ($id=="")
		{
			// no id is set --> not allowed to creat new one
			$idDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
			$idDataCell->SetValidationErrorMessage("Ein Kürzungsbetrag mit dieser ID existiert nicht");
		}
		else
		{
			// check if id exists
			$objectInstance = $this->manager->GetKuerzungsbetragById($_SESSION["currentUser"], (int)$id);
			if ($objectInstance==null)
			{
				// invalid value
				$idDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$idDataCell->SetValidationErrorMessage("Ein Kürzungsbetrag mit dieser ID existiert nicht");
			}
		}
		return $objectInstance;
	}
	
	/**
	 * Check if the WSP, abrechnungsjahr, contract, shop, location, company, group data and cPersonRs are valid
	 * @param SyncDataRow $dataRow
	 * @param DBEntry $object
	 * @return Array
	 */
	protected function CheckRow(SyncDataRow $dataRow, DBEntry $object=null)
	{
		/* @var $object Kuerzungsbetrag */
		// WSP
		$wsp = null;
		$wspIdDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_WIDERSPRUCHPUNKTID);
		if($wspIdDataCell->IsValid($dataRow) && $object!=null)
		{
			$wspId = trim($wspIdDataCell->GetValue());
			$wsp = $object->GetWiderspruchspunkt();
			if ($wsp==null || $wsp->GetPKey()!=(int)$wspId)
			{
				// invalid value
				$wspIdDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				if ($wsp!=null)
				{
					$wspIdDataCell->SetValidationErrorMessage("Die Widerspruchspunkt-ID unterscheidet sich von der im System hinterlegten (WSP".$wsp->GetPKey().")");
				}
				else
				{
					$wspIdDataCell->SetValidationErrorMessage("Ein Widerspruchspunkt mit dieser ID existiert nicht");
				}
			}
		}

		// WSP-Name
		$wsNameIdDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_WSNAME);
		if($wsNameIdDataCell->IsValid($dataRow) && $wsp!=null)
		{
			$wsName = trim($wsNameIdDataCell->GetValue());
			if (trim($wsp->GetTitle())!=$wsName)
			{
				// invalid value
				$wsNameIdDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$wsNameIdDataCell->SetValidationErrorMessage("Die Bezeichnung unterscheidet sich von der im System hinterlegten");
			}
		}

		// WS
		$ws = null;
		$wsIdDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_WIDERSPRUCHID);
		if($wsIdDataCell->IsValid($dataRow) && $wsp!=null)
		{
			$ws = $wsp->GetWiderspruch();
			$wsId = trim($wsIdDataCell->GetValue());
			if ($ws==null || $ws->GetPKey()!=(int)$wsId)
			{
				// invalid value
				$wsIdDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				if ($ws!=null)
				{
					$wsIdDataCell->SetValidationErrorMessage("Die Widerspruchs-ID unterscheidet sich von der im System hinterlegten (WS".$ws->GetPKey().")");
				}
				else
				{
					$wsIdDataCell->SetValidationErrorMessage("Ein Widerspruch mit dieser ID existiert nicht");
				}
			}
		}

		// Abrechnungsjahr
		$abrechnungsJahrDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_ABRECHNUNGSJAHR);
		if($abrechnungsJahrDataCell->IsValid($dataRow) && $ws != null)
		{		 
			$abrechnungsJahr = $ws->GetAbrechnungsJahr();
			$abrechnungsJahrValue = trim($abrechnungsJahrDataCell->GetValue());
			if($abrechnungsJahr == null || $abrechnungsJahrValue != (int)$abrechnungsJahr->GetJahr())
			{
				// invalid value
				$abrechnungsJahrDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$abrechnungsJahrDataCell->SetValidationErrorMessage("Das Abrechnungsjahr unterscheidet sich von dem im System hinterlegten (".$abrechnungsJahr->GetJahr().")");
			}				
		}

		// Prozessstatus
		$process = null;
		$processIdDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_PROCESSID);
		if($processIdDataCell->IsValid($dataRow) && $abrechnungsJahr != null)
		{
			$process = $abrechnungsJahr->GetProcessStatus($this->db);
			$processId = trim($processIdDataCell->GetValue());
			if($process == null || $processId != (int)$process->GetPKey())
			{
				// invalid value
				$processIdDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$processIdDataCell->SetValidationErrorMessage("Der Prozess unterscheidet sich von dem im System hinterlegten (P".$process->GetPKey().")");
			}
		}

		// Group
		$processIdDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_GROUPID);
		if($processIdDataCell->IsValid($dataRow) && $process != null)
		{
			$groupId = trim($processIdDataCell->GetValue());
			if($groupId != (int)$process->GetProcessStatusGroupId())
			{
				// invalid value
				$processIdDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$processIdDataCell->SetValidationErrorMessage("Das Paket unterscheidet sich von dem im System hinterlegten (GID".$process->GetProcessStatusGroupId().")");
			}
		}

		// contract id
		$contractIdDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_CONTRACTID);
		if($contractIdDataCell->IsValid($dataRow) && $abrechnungsJahr != null)
		{
			$contract = $abrechnungsJahr->GetContract();
			$contractId = trim($contractIdDataCell->GetValue());
			if($contractId == null || (int)$contract->GetPKey()!=(int)$contractId)
			{
				// invalid value
				$contractIdDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$contractIdDataCell->SetValidationErrorMessage("Die Vertrags-ID unterscheidet sich von der im System hinterlegten (V".$contract->GetPKey().")");
			}
		}

		// Country
		$currencyDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_CURRENCY);
		if ($currencyDataCell->IsValid($dataRow) && $contract!=null)
		{
			$currencyName = $currencyDataCell->GetValue();
			if (trim($contract->GetCurrency()!=trim($currencyName)))
			{
				// invalid value
				$currencyDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$currencyDataCell->SetValidationErrorMessage("Die Währung unterscheidet sich von dem im System hinterlegten (".$contract->GetCurrency().")");
			}
		}

		// FMS-ID
		$fmsIdDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_FMSID);
		if ($fmsIdDataCell->IsValid($dataRow) && $contract!=null)
		{
			$fmsId = trim($fmsIdDataCell->GetValue());
			// check if shop in contract has the same FMS-ID as the CSV file
			$shop = $contract->GetShop();
			if ($shop==null || $shop->GetRSID()!=$fmsId)
			{
				// invalid value
				$fmsIdDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$fmsIdDataCell->SetValidationErrorMessage("Diesem Vertrag ist ein anderer Laden zugeordnet");
			}
		}
		
		// Location name
		$locationDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_LOCATIONNAME);
		if ($locationDataCell->IsValid($dataRow) && $shop!=null)
		{
			$location = $shop->GetLocation();
			$locationName = trim($locationDataCell->GetValue());
			if ($location!=null)
			{
				if (trim($location->GetName())!=$locationName)
				{
					// invalid value
					$locationDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
					$locationDataCell->SetValidationErrorMessage("Der Standortname unterscheidet sich von dem im System hinterlegten (".$location->GetName().")");
				}
			}
			else
			{
				// invalid value
				$locationDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
			}
		}

		// Country
		$countryDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_COUNTRY);
		if ($countryDataCell->IsValid($dataRow) && $location!=null)
		{
			$countryName = $countryDataCell->GetValue();
			if (trim($location->GetCountry()!=trim($countryName)))
			{
				// invalid value
				$countryDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$countryDataCell->SetValidationErrorMessage("Das Land unterscheidet sich von dem im System hinterlegten (".$location->GetCountry().")");
			}
		}
				
		// Company name
		$companyDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_COMPANYNAME);
		if ($companyDataCell->IsValid($dataRow) && $location!=null)
		{
			$company = $location->GetCompany();
			$companyName = trim($companyDataCell->GetValue());
			if ($company!=null)
			{
				if (trim($company->GetName())!=trim($companyName))
				{
					// invalid value
					$companyDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
					$companyDataCell->SetValidationErrorMessage("Der Firmenname unterscheidet sich von dem im System hinterlegten (".$company->GetName().")");
				}
			}
			else
			{
				// invalid value
				$companyDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
			}
		}
		
		// Group name
		$groupDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_GROUPNAME);
		if ($groupDataCell->IsValid($dataRow) && $company!=null)
		{
			$group = $company->GetGroup();
			$groupName = trim($groupDataCell->GetValue());
			if ($group!=null)
			{
				if (trim($group->GetName())!=trim($groupName))
				{
					// invalid value
					$groupDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
					$groupDataCell->SetValidationErrorMessage("Der Gruppenname unterscheidet sich von dem im System hinterlegten (".$group->GetName().")");
				}
			}
			else
			{
				// invalid value
				$groupDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
			}
		}
		
		// Shop name
		$shopDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_SHOPNAME);
		if ($shopDataCell->IsValid($dataRow) && $shop!=null)
		{
			$shopName = trim($shopDataCell->GetValue());
			if (trim($shop->GetName())!=trim($shopName))
			{
				// invalid value
				$shopDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$shopDataCell->SetValidationErrorMessage("Der Ladenname unterscheidet sich von dem im System hinterlegten (".$shop->GetName().")");
			}
		}

		// cPersonRs
		$cPersonRsDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_CPERSONRS);
		if ($cPersonRsDataCell->IsValid($dataRow) && $contract!=null)
		{
			$cPersonRs = trim($cPersonRsDataCell->GetValue());
			// check if shop in contract (string concatenation) has the same "Forderungsmanager SFM" as the CSV file
			$shop = $contract->GetShop();
			if ($shop==null || $shop->GetCPersonRS()==null || $shop->GetCPersonRS()->GetName()." ". $shop->GetCPersonRS()->GetFirstName()!=$cPersonRs)
			{
				// invalid value
				$tempName = $shop!=null && $shop->GetCPersonRS()!=null ? $shop->GetCPersonRS()->GetName()." ". $shop->GetCPersonRS()->GetFirstName() : "";
				$cPersonRsDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$cPersonRsDataCell->SetValidationErrorMessage("Der Forderungsmanager SFM unterscheidet sich von dem im System hinterlegten (".$tempName.")");
			}
		}

		// Gruppe Verwalter
		$gruppeVerwalterDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_GRUPPE_VERWALTER);
		if ($gruppeVerwalterDataCell->IsValid($dataRow))
		{
			$gruppeVerwalter = trim($gruppeVerwalterDataCell->GetValue());
			// check if Gruppe Verwalter in Teilabrechnung has the same Gruppe Verwalter as the CSV file
			if ($process==null ||
				$process->GetTeilabrechnung($this->db)==null ||
				$process->GetTeilabrechnung($this->db)->GetVerwalter()==null ||
				$process->GetTeilabrechnung($this->db)->GetVerwalter()->GetOverviewString()!=$gruppeVerwalter)
			{
				/** @var $teilabrechnung Teilabrechnung */
				$tempTeilabrechnung = $process!=null ? $process->GetTeilabrechnung($this->db) : null;
				$tempVerwalter = $tempTeilabrechnung!=null ? $tempTeilabrechnung->GetVerwalter() : null;
				$tempName = $tempVerwalter!=null ? $tempVerwalter->GetOverviewString() : '';
				// invalid value
				$gruppeVerwalterDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$gruppeVerwalterDataCell->SetValidationErrorMessage("Gruppe Verwalter unterscheidet sich von dem im System hinterlegten (".$tempName.")");
			}
		}

		return Array(self::COLUMN_ID_GROUPID, self::COLUMN_ID_WIDERSPRUCHPUNKTID, self::COLUMN_ID_WIDERSPRUCHID, self::COLUMN_ID_WSNAME, self::COLUMN_ID_CURRENCY, self::COLUMN_ID_COUNTRY, self::COLUMN_ID_ABRECHNUNGSJAHR, self::COLUMN_ID_PROCESSID, self::COLUMN_ID_CONTRACTID, self::COLUMN_ID_FMSID, self::COLUMN_ID_LOCATIONNAME, self::COLUMN_ID_COMPANYNAME, self::COLUMN_ID_GROUPNAME, self::COLUMN_ID_SHOPNAME);
	}
	
	/**
	 * Return the value of the object for the specified column
	 * @param int $columnId
	 * @param SyncDataCell $dataCell
	 * @param DBEntry $object
     * @param SyncDataRow $dataRow
	 * @return boolean
	 */
	protected function SetValueOfColumnToObject($columnId, SyncDataCell $dataCell, DBEntry $object, SyncDataRow $dataRow)
	{
        // Skip this data cell by pre condition ?
        if ($dataCell->SkipByPreConditions($dataRow)) return true;
		/* @var $object Kuerzungsbetrag */
		switch($columnId)
		{
			case self::COLUMN_ID_FMSID:
			case self::COLUMN_ID_CONTRACTID:
			case self::COLUMN_ID_PROCESSID:
			case self::COLUMN_ID_GROUPID:
			case self::COLUMN_ID_WIDERSPRUCHID:
			case self::COLUMN_ID_WIDERSPRUCHPUNKTID:
			case self::COLUMN_ID_KUERZUNGSBETRAGID:
			case self::COLUMN_ID_GROUPNAME:
			case self::COLUMN_ID_COMPANYNAME:
			case self::COLUMN_ID_LOCATIONNAME:
			case self::COLUMN_ID_SHOPNAME:
			case self::COLUMN_ID_CPERSONRS:
			case self::COLUMN_ID_COUNTRY:
			case self::COLUMN_ID_ABRECHNUNGSJAHR:
			case self::COLUMN_ID_CURRENCY:
			case self::COLUMN_ID_WSNAME:
			case self::COLUMN_ID_GRUPPE_VERWALTER:
				return true;
			case self::COLUMN_ID_KUERZUNGSBETRAG:
				return $object->SetKuerzungsbetrag($dataCell->GetValue());
			case self::COLUMN_ID_EINSTUFUNG:
				return $object->SetRating($dataCell->GetValue());
			case self::COLUMN_ID_ERSTEINSPARUNG:
				return $object->SetType($dataCell->GetValue());
			case self::COLUMN_ID_REALISIERT:
				return $object->SetRealisiert($dataCell->GetValue()==SyncDataCellBoolean::VALUE_FALSE ? Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO : Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES);
			case self::COLUMN_ID_CATEGORIZATION:
				return $object->SetCategorization($dataCell->GetValue());
			case self::COLUMN_ID_STICHTAG_ZIELGELDEINGANG:
				return $object->SetStichtagZielgeldeingang($dataCell->GetValue());
		}
		return false;
	}
	
}
?>