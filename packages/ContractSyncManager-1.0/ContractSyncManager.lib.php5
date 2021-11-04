<?php
include_once(__DIR__.'/../SyncManager-1.0/SyncManager.lib.php5');
/**
 * Base class to sync csv files with database
 *
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class ContractSyncManager extends SyncManager
{
	const COLUMN_ID_CONTRACTID = 1;
	const COLUMN_ID_FMSID = 2;
	const COLUMN_ID_GROUPNAME = 3;
	const COLUMN_ID_COMPANYNAME = 4;
	const COLUMN_ID_LOCATIONNAME = 5;
	const COLUMN_ID_SHOPNAME = 6;
	const COLUMN_ID_CURRENCY = 7;
	const COLUMN_ID_RENTABLEAREA = 8;
	const COLUMN_ID_APPORTIONMENTAREA = 9;
	const COLUMN_ID_RENTALCONTRACTSTART = 10;
	const COLUMN_ID_RENTALCONTRACTFIRSTEND = 11;
	const COLUMN_ID_RENTALCONTRACTCURRETNEND = 12;
	const COLUMN_ID_ONLYPOLICYEXTRACTAVAILABLE = 13;
	const COLUMN_ID_OWNER = 14;
	const COLUMN_ID_TRUSTEE = 15;
	const COLUMN_ID_MAINTENANCECAP = 16;
	const COLUMN_ID_ADMINISTRATIONCAP = 17;

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
	 * ArrayMap iso to currency object
	 * @var CCurrency[]
	 */
	protected $currencyMap = Array();

	/**
	 * Contracts
	 * @var Contract[]
	 */
	protected $contracts = Array();

	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 */
	public function ContractSyncManager(DBManager $db, ExtendedLanguageManager $languageManager)
	{
		// init needed managers
		$this->manager = new RSKostenartManager($db);
		$this->customerManager = new CustomerManager($db);
		$this->addressManager = new AddressManager($db);

		// build currency map
		$currencies = $this->customerManager->GetCurrencies();
		for ($a=0; $a<count($currencies);$a++)
		{
			$this->currencyMap[$currencies[$a]->GetIso4217()] = $currencies[$a]->GetIso4217();
		}

		// call parent constructor
		parent::__construct($db, $languageManager);
	}

	/**
	 * Initialize the column objects for this SyncManager
	 */
	protected function InitColumns()
	{

		$this->columns[] = new SyncColumn(self::COLUMN_ID_CONTRACTID, "Vertrags-ID", true, '', new SyncDataCellId(true, Contract::ID_PREFIX));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_FMSID, CShop::GetAttributeName($this->languageManager, 'RSID'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_GROUPNAME, CGroup::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYNAME, CCompany::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_LOCATIONNAME, CLocation::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_SHOPNAME, CShop::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_CURRENCY, "Währung Vertrag", false, '', new SyncDataCellIdToTextMap($this->currencyMap, ''));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_RENTABLEAREA, "Mietfläche Vertrag", false, "", new SyncDataCellFloat());
		$this->columns[] = new SyncColumn(self::COLUMN_ID_APPORTIONMENTAREA, "Umlagefläche Vertrag", false, "", new SyncDataCellFloat());
		$this->columns[] = new SyncColumn(self::COLUMN_ID_RENTALCONTRACTSTART, "Mietbegin Vertrag", true, "", new SyncDataCellDate(true));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_RENTALCONTRACTFIRSTEND, "Erstmals mögliches Mietende Vertrag", true, "", new SyncDataCellDate(true));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_RENTALCONTRACTCURRETNEND, "Aktuelles Mietende Vertrag", true, "", new SyncDataCellDate(true));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_ONLYPOLICYEXTRACTAVAILABLE, "Nur Vertragsauszüge vorhanden", true, "", new SyncDataCellBoolean());
		$this->columns[] = new SyncColumn(self::COLUMN_ID_OWNER, "Firma Eigentümer Vertrag", true, "", new SyncDataCellAddressData($this->db));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_TRUSTEE, "Firma Verwalter Vertrag", true, "", new SyncDataCellAddressData($this->db));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_MAINTENANCECAP, "Deckelung/Pauschale Wartung, Instandhaltung und Instandsetzung", true, "", new SyncDataCellBoolean());
		$this->columns[] = new SyncColumn(self::COLUMN_ID_ADMINISTRATIONCAP, "Deckelung/Pauschale Verwaltung und Management", true, "", new SyncDataCellBoolean());
	}

	/**
	 * Initialize
	 */
	protected function LoadDataFromDatabase()
	{
		// Load all contracts from db (if not allready done)
		if (count($this->contracts)==0)
		{
			$this->contracts = $this->manager->GetContracts($_SESSION["currentUser"], "", "name", 0, 0, 0);
		}

		$this->LoadDataFromObjects($this->contracts);
	}

	/**
	 * Return an new instance of the data object
	 * @param DBManager $db
	 * @param SyncDataRow $dataRow
	 * @return DBEntry
	 */
	protected function CreateNewDataObject(DBManager $db, SyncDataRow $dataRow)
	{
		$contract = new Contract($db);
		// for new contracts search shop with FMS-ID
		$cShop = null;
		$fmsIdDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_FMSID);
		if ($fmsIdDataCell->IsValid($dataRow))
		{
			$fmsId = trim($fmsIdDataCell->GetValue());
			$cShop = $this->customerManager->GetShopByRSID($_SESSION["currentUser"], $fmsId);
			$contract->SetShop($this->db, $cShop);
		}
		else
		{
			$fmsIdDataCell->SetValidationErrorMessage("Ein Laden mit dieser SFM-ID existiert nicht");
			return null;
		}
		return $contract;
	}

	/**
	 * Return the value of the object for the specified column
	 * @param int $columnId
	 * @param DBEntry $object
	 * @return mixed
	 */
	protected function GetValueForColumnFromObject($columnId, DBEntry $object)
	{
		/* @var Contract $object  */

		// Get all data objects
		$shop = $object->GetShop();
		$location = ($shop!=null ? $shop->GetLocation() : null);
		$company = ($location!=null ? $location->GetCompany() : null);
		$group = ($company!=null ? $company->GetGroup() : null);

		switch($columnId)
		{
			case self::COLUMN_ID_CONTRACTID:
				return Contract::ID_PREFIX.$object->GetPKey();
			case self::COLUMN_ID_FMSID:
				return ($shop!=null ? $shop->GetRSID() : "-");
			case self::COLUMN_ID_LOCATIONNAME:
				return ($location!=null ? $location->GetName() : "-");
			case self::COLUMN_ID_COMPANYNAME:
				return ($company!=null ? $company->GetName() : "-");
			case self::COLUMN_ID_GROUPNAME:
				return ($group!=null ? $group->GetName() : "-");
			case self::COLUMN_ID_SHOPNAME:
				return ($shop!=null ? $shop->GetName() : "-");
			case self::COLUMN_ID_CURRENCY:
				return $object->GetCurrency();
			case self::COLUMN_ID_RENTABLEAREA:
				return (float)$object->GetMietflaecheQM();
			case self::COLUMN_ID_APPORTIONMENTAREA:
				return (float)$object->GetUmlageflaecheQM();
			case self::COLUMN_ID_RENTALCONTRACTSTART:
				return (int)$object->GetMvBeginn();
			case self::COLUMN_ID_RENTALCONTRACTFIRSTEND:
				return (int)$object->GetMvEndeErstmalsMoeglich();
			case self::COLUMN_ID_RENTALCONTRACTCURRETNEND:
				return (int)$object->GetMvEnde();
			case self::COLUMN_ID_ONLYPOLICYEXTRACTAVAILABLE:
				return (int)$object->GetNurAuszuegeVorhanden();
			case self::COLUMN_ID_OWNER:
				return $object->GetEigentuemer();
			case self::COLUMN_ID_TRUSTEE:
				return $object->GetVerwalter();
			case self::COLUMN_ID_MAINTENANCECAP:
				return $object->GetDeckelungInstandhaltung() ? 1 : 2;
			case self::COLUMN_ID_ADMINISTRATIONCAP:
				return $object->GetDeckelungVerwaltungManagement() ? 1 : 2;
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
		/* @var $object Contract */
		switch($columnId)
		{
			case self::COLUMN_ID_CURRENCY:
				return $dataCell->GetValue()!=$object->GetCurrency();
			case self::COLUMN_ID_RENTABLEAREA:
				return (float)$dataCell->GetValue()!=(float)$object->GetMietflaecheQM();
			case self::COLUMN_ID_APPORTIONMENTAREA:
				return (float)$dataCell->GetValue()!=(float)$object->GetUmlageflaecheQM();
			case self::COLUMN_ID_RENTALCONTRACTSTART:
				return (int)$dataCell->GetValue()!=(int)$object->GetMvBeginn();
			case self::COLUMN_ID_RENTALCONTRACTFIRSTEND:
				return (int)$dataCell->GetValue()!=(int)$object->GetMvEndeErstmalsMoeglich();
			case self::COLUMN_ID_RENTALCONTRACTCURRETNEND:
				return (int)$dataCell->GetValue()!=(int)$object->GetMvEnde();
			case self::COLUMN_ID_ONLYPOLICYEXTRACTAVAILABLE:
				return (int)$dataCell->GetValue()!=(int)$object->GetNurAuszuegeVorhanden();
			case self::COLUMN_ID_OWNER:
				$value = $dataCell->GetValue();
				if ( ($value==null && $object->GetEigentuemer()!=null) || ($value!=null && $object->GetEigentuemer()==null) || ($value!=null && $object->GetEigentuemer()!=null && $value->GetPKey()!=$object->GetEigentuemer()->GetPKey()) )
				{
					return true;
				}
				return false;
			case self::COLUMN_ID_TRUSTEE:
				$value = $dataCell->GetValue();
				if ( ($value==null && $object->GetVerwalter()!=null) || ($value!=null && $object->GetVerwalter()==null) || ($value!=null && $object->GetVerwalter()!=null && $value->GetPKey()!=$object->GetVerwalter()->GetPKey()) )
				{
					return true;
				}
				return false;
			case self::COLUMN_ID_MAINTENANCECAP:
				return (((int)$dataCell->GetValue() == 1) ? 1 : 0)!=(int)$object->GetDeckelungInstandhaltung();
			case self::COLUMN_ID_ADMINISTRATIONCAP:
				return (((int)$dataCell->GetValue() == 1) ? 1 : 0)!=(int)$object->GetDeckelungVerwaltungManagement();
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
		$idDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_CONTRACTID);
		$id = trim($idDataCell->GetValue());
		if ($id=="")
		{
			// no id is set --> new entry
			$idDataCell->SetValidationState(SyncDataCell::STATE_NEW);
		}
		else
		{
			// check if id exists
			$objectInstance = $this->manager->GetContractByID($_SESSION["currentUser"], (int)$id);
			if ($objectInstance==null)
			{
				// invalid value
				$idDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$idDataCell->SetValidationErrorMessage("Ein Vertrag mit dieser ID existiert nicht");
			}
		}
		return $objectInstance;
	}

	/**
	 * Check if the shop, location, company and group data are valid
	 * @param SyncDataRow $dataRow
	 * @param DBEntry $object
	 * @return Array
	 */
	protected function CheckRow(SyncDataRow $dataRow, DBEntry $object=null)
	{
		/*@var $object Contract */
		$shop = null;
		// FMS-ID
		$fmsIdDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_FMSID);
		if ($fmsIdDataCell->IsValid($dataRow))
		{
			$fmsId = trim($fmsIdDataCell->GetValue());
			// check if shop in contract has the same FMS-ID as the CSV file
			if ($object!=null)
			{
				$contractShop = $object->GetShop();
				if ($contractShop!=null && $contractShop->GetRSID()==$fmsId)
				{
					// the same FMS-ID --> use shop object
					$shop = $contractShop;
				}
				else
				{
					// invalid value
					$fmsIdDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
					$fmsIdDataCell->SetValidationErrorMessage("Diesem Vertrag ist ein anderer Laden zugeordnet");
				}
			}
			else
			{
				// for new contracts search shop with FMS-ID
				$shop = $this->customerManager->GetShopByRSID($_SESSION["currentUser"], $fmsId);
				if ($shop==null)
				{
					// invalid value
					$fmsIdDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
					$fmsIdDataCell->SetValidationErrorMessage("Ein Laden mit dieser SFM-ID existiert nicht");
				}
			}
		}

		// Location name
		$location = ($shop!=null ? $shop->GetLocation() : null);
		$locationDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_LOCATIONNAME);
		if ($locationDataCell->IsValid($dataRow))
		{
			$locationName = trim($locationDataCell->GetValue());
			if ($location!=null)
			{
				if (trim($location->GetName())!=trim($locationName))
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

		// Company name
		$company = ($location!=null ? $location->GetCompany() : null);
		$companyDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_COMPANYNAME);
		if ($companyDataCell->IsValid($dataRow))
		{
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
		$group = ($company!=null ? $company->GetGroup() : null);
		$groupDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_GROUPNAME);
		if ($groupDataCell->IsValid($dataRow))
		{
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
		if ($shopDataCell->IsValid($dataRow))
		{
			$shopName = trim($shopDataCell->GetValue());
			if ($shop!=null)
			{
				if (trim($shop->GetName())!=trim($shopName))
				{
					// invalid value
					$shopDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
					$shopDataCell->SetValidationErrorMessage("Der Ladenname unterscheidet sich von dem im System hinterlegten (".$shop->GetName().")");
				}
			}
			else
			{
				// invalid value
				$shopDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
			}
		}
		return Array(self::COLUMN_ID_FMSID, self::COLUMN_ID_LOCATIONNAME, self::COLUMN_ID_COMPANYNAME, self::COLUMN_ID_GROUPNAME, self::COLUMN_ID_SHOPNAME);
	}

	/**
	 * Set the value of the object for the specified column
	 * @param int $columnId
	 * @param SyncDataCell $dataCell
	 * @param DBEntry $object
	 * @param SyncDataRow $dataRow
	 * @return boolean
	 */
	protected function SetValueOfColumnToObject($columnId, SyncDataCell $dataCell, DBEntry $object, SyncDataRow $dataRow)
	{
		/* @var Contract $object  */

		switch($columnId)
		{
			case self::COLUMN_ID_CONTRACTID:
			case self::COLUMN_ID_FMSID:
			case self::COLUMN_ID_GROUPNAME:
			case self::COLUMN_ID_COMPANYNAME:
			case self::COLUMN_ID_LOCATIONNAME:
			case self::COLUMN_ID_SHOPNAME:
				return true;
			case self::COLUMN_ID_CURRENCY:
				return $object->SetCurrency($this->db, $dataCell->GetValue());
			case self::COLUMN_ID_RENTABLEAREA:
				return $object->SetMietflaecheQM($dataCell->GetValue());
			case self::COLUMN_ID_APPORTIONMENTAREA:
				return $object->SetUmlageflaecheQM($dataCell->GetValue());
			case self::COLUMN_ID_RENTALCONTRACTSTART:
				return $object->SetMvBeginn($dataCell->GetValue());
			case self::COLUMN_ID_RENTALCONTRACTFIRSTEND:
				return $object->SetMvEndeErstmalsMoeglich($dataCell->GetValue());
			case self::COLUMN_ID_RENTALCONTRACTCURRETNEND:
				return $object->SetMvEnde($dataCell->GetValue());
			case self::COLUMN_ID_ONLYPOLICYEXTRACTAVAILABLE:
				return $object->SetNurAuszuegeVorhanden($dataCell->GetValue());
			case self::COLUMN_ID_OWNER:
				return $object->SetEigentuemer($dataCell->GetValue());
			case self::COLUMN_ID_TRUSTEE:
				return $object->SetVerwalter($dataCell->GetValue());
			case self::COLUMN_ID_MAINTENANCECAP:
				return $object->SetDeckelungInstandhaltung(((int)$dataCell->GetValue() == 1));
			case self::COLUMN_ID_ADMINISTRATIONCAP:
				return $object->SetDeckelungVerwaltungManagement(((int)$dataCell->GetValue() == 1));
		}
		return false;
	}

}
?>