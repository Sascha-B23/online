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
class TeilabrechnungspositionenSyncManager extends SyncManager
{
	const COLUMN_ID_FMSID = 1;
	const COLUMN_ID_CONTRACTID = 2;
	const COLUMN_ID_TEILABRECHNUNGID = 3;
	const COLUMN_ID_TEILABRECHNUNGSPOSITIONID = 4;
	const COLUMN_ID_GROUPNAME = 5;
	const COLUMN_ID_COMPANYNAME = 6;
	const COLUMN_ID_LOCATIONNAME = 7;
	const COLUMN_ID_SHOPNAME = 8;
	const COLUMN_ID_ABRECHNUNGSJAHR = 9;
	const COLUMN_ID_ABRECHNUNGSNAME = 10;
	const COLUMN_ID_AREANAME = 11;
	const COLUMN_ID_KOSTENART = 12;
	const COLUMN_ID_KOSTENART_FMS = 13;
	const COLUMN_ID_GESAMTEINHEITEN = 14;
	const COLUMN_ID_EINHEITKUNDE = 15;
	const COLUMN_ID_GESAMTBETRAG = 16;
	const COLUMN_ID_BETRAG_KUNDE = 17;
	const COLUMN_ID_UMLAGEFAEHIG = 19;
	const COLUMN_ID_ERFASST = 20;
	const COLUMN_ID_GESAMTEINHEITENEINHEIT = 21;
	const COLUMN_ID_EINHEITKUNDEEINHEIT = 22;
    const COLUMN_ID_PAUSCHALE = 23;
	
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
	 * Pkey to RSKostenart Map
	 * @var RSKostenart 
	 */
	protected $kostenartenPkeyToKostenartMap = Array();
	
	/**
	 * Pkey to RSKostenart Name Map
	 * @var RSKostenart 
	 */
	protected $kostenartenPkeyToKostenartNameMap = Array();
	
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
	public function TeilabrechnungspositionenSyncManager(DBManager $db, ExtendedLanguageManager $languageManager) 
	{
		// init needed managers
		$this->manager = new RSKostenartManager($db);
		$this->customerManager = new CustomerManager($db);
		$this->addressManager = new AddressManager($db);
		
		// init TAP-Report for fast export (workaround)
		global $userManager, $em, $lm;
		$this->tapReport = new TAPReport($db, $_SESSION["currentUser"], $this->customerManager, $this->addressManager, $userManager, $this->manager, $em, $lm);
		
		// build pkey to RSKostenart map for FMS-Kostenarten
		$kostenarten = $this->manager->GetKostenarten("", "name", 0, 0, 0);
		foreach ($kostenarten as $kostenart)
		{
			$this->kostenartenPkeyToKostenartMap[$kostenart->GetPKey()] = $kostenart;
			$this->kostenartenPkeyToKostenartNameMap[$kostenart->GetPKey()] = $kostenart->GetName();
			
		}
		
		// call parent constructor
		parent::__construct($db, $languageManager);
	}
	
	/**
	 * Initialize the column objects for this SyncManager
	 */
	protected function InitColumns()
	{
		// Build Map for TAP units
		$tapUnitMap = Array();
		global $NKM_TEILABRECHNUNGSPOSITION_EINHEIT;
		foreach ($NKM_TEILABRECHNUNGSPOSITION_EINHEIT as $key => $value)
		{
			$tapUnitMap[$key] = $value["long"];
		}
		// build columns
		$this->columns[] = new SyncColumn(self::COLUMN_ID_FMSID, CShop::GetAttributeName($this->languageManager, 'RSID'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_CONTRACTID, "Vertrags-ID", false, '', new SyncDataCellId(false, Contract::ID_PREFIX));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_TEILABRECHNUNGID, "TA-ID", false, '', new SyncDataCellId(false, Teilabrechnung::ID_PREFIX));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_ABRECHNUNGSNAME, "Bezeichnung Abrechnung", false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_ERFASST, "Vollständig erfasst", false, "", new SyncDataCellBoolean());
		$this->columns[] = new SyncColumn(self::COLUMN_ID_GROUPNAME, CGroup::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYNAME, CCompany::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_LOCATIONNAME, CLocation::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_SHOPNAME, CShop::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_ABRECHNUNGSJAHR, AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr'), false, "", new SyncDataCellInteger(false, false));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_TEILABRECHNUNGSPOSITIONID, "TAP-ID", true, '', new SyncDataCellId(true, Teilabrechnungsposition::ID_PREFIX));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_AREANAME, "Flächenbezeichnung", false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_KOSTENART, "Kostenart Abrechnung", false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_KOSTENART_FMS, "Kostenart SFM", false, "", new SyncDataCellIdToTextMap($this->kostenartenPkeyToKostenartNameMap));
        $pauschale = new SyncColumn(self::COLUMN_ID_PAUSCHALE, "Pauschale", false, "", new SyncDataCellBoolean('Ja', 'Nein', SyncDataCellBoolean::VALUE_TRUE));
        $this->columns[] = $pauschale;
        $column = new SyncColumn(self::COLUMN_ID_GESAMTEINHEITEN, "Gesamteinheiten Umlageschlüssel", false, "", new SyncDataCellFloat(true, true));
        $column->AddPreConditionColumn($pauschale);
		$this->columns[] = $column;
        $column = new SyncColumn(self::COLUMN_ID_GESAMTEINHEITENEINHEIT, "Gesamteinheiten Umlageschlüssel Einheiten", false, "", new SyncDataCellIdToTextMap($tapUnitMap, NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE));
        $column->AddPreConditionColumn($pauschale);
        $this->columns[] = $column;
        $column = new SyncColumn(self::COLUMN_ID_EINHEITKUNDE, "Einheiten Umlageschlüssel Kunde", false, "", new SyncDataCellFloat(true, true));
        $column->AddPreConditionColumn($pauschale);
        $this->columns[] = $column;
        $column = new SyncColumn(self::COLUMN_ID_EINHEITKUNDEEINHEIT, "Einheiten Umlageschlüssel Kunde Einheiten", false, "", new SyncDataCellIdToTextMap($tapUnitMap, NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE));
        $column->AddPreConditionColumn($pauschale);
        $this->columns[] = $column;
        $column = new SyncColumn(self::COLUMN_ID_GESAMTBETRAG, "Gesamtkosten Kostenart", false, "", new SyncDataCellFloat(true, true));
        $column->AddPreConditionColumn($pauschale);
        $this->columns[] = $column;
		$this->columns[] = new SyncColumn(self::COLUMN_ID_BETRAG_KUNDE, "Kostenanteil Kunde", false, "", new SyncDataCellFloat(true, true));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_UMLAGEFAEHIG, "Kostenart Umlagefähig", false, "", new SyncDataCellBoolean());
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
		// Load all TAPs from db (if not allready done)
		if (count($this->teilabrechnungspositionen)==0)
		{
			$this->teilabrechnungspositionen = $this->manager->GetTeilabrechnungsposition($_SESSION["currentUser"], "", "name", 0, 0, 5000);
		}
		
		$this->LoadDataFromObjects($this->teilabrechnungspositionen);
	}
	
	/**
	 * Return an new instance of the data object
	 * @param DBManager $db
	 * @param SyncDataRow $dataRow
	 * @return DBEntry
	 */
	protected function CreateNewDataObject(DBManager $db, SyncDataRow $dataRow)
	{
		// new TAP
		$teilabrechnungsposition = new Teilabrechnungsposition($this->db);
		// for new TAP search TA with TA-ID
		$teilabrechnung = null;
		$teilabrechnungIdDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_TEILABRECHNUNGID);
		if ($teilabrechnungIdDataCell->IsValid($dataRow))
		{
			$teilabrechnungId = trim($teilabrechnungIdDataCell->GetValue());
			$teilabrechnung = $this->manager->GetTeilabrechnungByPkey($this->db, $teilabrechnungId);
			$teilabrechnungsposition->SetTeilabrechnung($teilabrechnung);
		}
		else
		{
			$teilabrechnungIdDataCell->SetValidationErrorMessage("Eine Teilabrechnung mit dieser SFM-ID existiert nicht");
			return null;
		}
		return $teilabrechnungsposition;
	}
	
	/**
	 * Return the value of the object for the specified column
	 * @param int $columnId
	 * @param DBEntry $object
	 * @return mixed
	 */
	protected function GetValueForColumnFromObject($columnId, DBEntry $object)
	{
		/*@var $object Teilabrechnungsposition */
		
		// Get all data objects
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
			case self::COLUMN_ID_ABRECHNUNGSJAHR:
				return (int)$abrechnungsJahr->GetJahr();
			case self::COLUMN_ID_ABRECHNUNGSNAME:
				return $teilabrechnung->GetBezeichnung();
			case self::COLUMN_ID_AREANAME:
				return $object->GetBezeichnungTeilflaech();
			case self::COLUMN_ID_KOSTENART:
				return $object->GetBezeichnungKostenart();
			case self::COLUMN_ID_KOSTENART_FMS:
				return (int)$object->GetKostenartRSPKey();
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

		/* @var $object Teilabrechnungsposition */
		switch($columnId)
		{
			case self::COLUMN_ID_AREANAME:
				return $dataCell->GetValue()!=trim($object->GetBezeichnungTeilflaech());
			case self::COLUMN_ID_KOSTENART:
				return $dataCell->GetValue()!=trim($object->GetBezeichnungKostenart());
			case self::COLUMN_ID_KOSTENART_FMS:
				return $dataCell->GetValue()!=(int)$object->GetKostenartRSPKey();
			case self::COLUMN_ID_GESAMTEINHEITEN:
				return $dataCell->GetValue()!=(float)$object->GetGesamteinheiten();
			case self::COLUMN_ID_GESAMTEINHEITENEINHEIT:
				return $dataCell->GetValue()!=(int)$object->GetGesamteinheitenEinheit();
			case self::COLUMN_ID_EINHEITKUNDE:
				return $dataCell->GetValue()!=(float)$object->GetEinheitKunde();
			case self::COLUMN_ID_EINHEITKUNDEEINHEIT:
				return $dataCell->GetValue()!=(int)$object->GetEinheitKundeEinheit();
            case self::COLUMN_ID_PAUSCHALE:
                return $dataCell->GetValue()!=($object->IsPauschale() ? SyncDataCellBoolean::VALUE_TRUE : SyncDataCellBoolean::VALUE_FALSE);
			case self::COLUMN_ID_GESAMTBETRAG:
				return $dataCell->GetValue()!=(float)$object->GetGesamtbetrag();
			case self::COLUMN_ID_BETRAG_KUNDE:
				return $dataCell->GetValue()!=(float)$object->GetBetragKunde();
			case self::COLUMN_ID_UMLAGEFAEHIG:
				return $dataCell->GetValue()!=(int)$object->GetUmlagefaehig();
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
		$idDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_TEILABRECHNUNGSPOSITIONID);
		$id = trim($idDataCell->GetValue());
		if ($id=="")
		{
			// no id is set --> new entry
			$idDataCell->SetValidationState(SyncDataCell::STATE_NEW);
		}
		else
		{
			// check if id exists
			$objectInstance = $this->manager->GetTeilabrechnungspositionByID($_SESSION["currentUser"], (int)$id);
			if ($objectInstance==null)
			{
				// invalid value
				$idDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$idDataCell->SetValidationErrorMessage("Eine Teilabrechnungsposition mit dieser ID existiert nicht");
			}
		}
		return $objectInstance;
	}
	
	/**
	 * Check if the teilabrechnung, abrechnungsjahr, contract, shop, location, company and group data are valid
	 * @param SyncDataRow $dataRow
	 * @param DBEntry $object
	 * @return Array
	 */
	protected function CheckRow(SyncDataRow $dataRow, DBEntry $object=null)
	{
		$teilabrechnung = null;
		$teilabrechnungIdDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_TEILABRECHNUNGID);
		if($teilabrechnungIdDataCell->IsValid($dataRow))
		{
			$teilabrechnungId = trim($teilabrechnungIdDataCell->GetValue());
			if ($object!=null)
			{
				$tapTeilabrechnung = $object->GetTeilabrechnung($this->db);
				if ($tapTeilabrechnung!=null && $tapTeilabrechnung->GetPKey()==(int)$teilabrechnungId)
				{
					// the same TA-ID --> use TA object
					$teilabrechnung = $tapTeilabrechnung;
				}
				else
				{
					// invalid value
					$teilabrechnungIdDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
					if ($tapTeilabrechnung!=null)
					{
						$teilabrechnungIdDataCell->SetValidationErrorMessage("Die Teilabrechnung-ID unterscheidet sich von der im System hinterlegten (TA".$tapTeilabrechnung->GetPKey().")");
					}
					else
					{
						$teilabrechnungIdDataCell->SetValidationErrorMessage("Eine Teilabrechnung mit dieser ID existiert nicht");
					}
				}
			}
			else
			{
				// for new contracts search shop with FMS-ID
				$teilabrechnung = $this->manager->GetTeilabrechnungByPkey($this->db, (int)$teilabrechnungId);
				if ($teilabrechnung==null)
				{
					// invalid value
					$teilabrechnungIdDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
					$teilabrechnungIdDataCell->SetValidationErrorMessage("Eine Teilabrechnung mit dieser ID existiert nicht");
				}
			}
		}

		// Abrechnungsjahr
		$abrechnungsJahrDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_ABRECHNUNGSJAHR);
		if($abrechnungsJahrDataCell->IsValid($dataRow) && $teilabrechnung != null)
		{		 
			$abrechnungsJahr = $teilabrechnung->GetAbrechnungsJahr();
			$abrechnungsJahrValue = trim($abrechnungsJahrDataCell->GetValue());
			if($abrechnungsJahr == null || $abrechnungsJahrValue != (int)$abrechnungsJahr->GetJahr())
			{
				// invalid value
				$abrechnungsJahrDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$abrechnungsJahrDataCell->SetValidationErrorMessage("Das Abrechnungsjahr unterscheidet sich von dem im System hinterlegten (".$abrechnungsJahr->GetJahr().")");
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
		$location = ($shop!=null ? $shop->GetLocation() : null);
		$locationDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_LOCATIONNAME);
		if ($locationDataCell->IsValid($dataRow))
		{
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
				
		// Bezeichnung Abrechnung
		$abrechnungsNameDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_ABRECHNUNGSNAME);
		if($abrechnungsNameDataCell->IsValid($dataRow))
		{
			if($teilabrechnung != null)
			{
				$abrechnungsName = trim($abrechnungsNameDataCell->GetValue());
				if($abrechnungsName != trim($teilabrechnung->GetBezeichnung()))
				{
					// invalid value
					$abrechnungsNameDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
					$abrechnungsNameDataCell->SetValidationErrorMessage("Dei Bezeichnung der Abrechnung unterscheidet sich von der im System hinterlegten (".$teilabrechnung->GetBezeichnung().")");
				}				
			}
			else
			{
				// invalid value
				$abrechnungsNameDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
			}
		}

		// vollständig erfasst
		$erfasstDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_ERFASST);
		if($erfasstDataCell->IsValid($dataRow))
		{
			if($teilabrechnung != null)
			{
				$erfasst = trim($erfasstDataCell->GetValue());
				if((int)$erfasst != ($teilabrechnung->GetErfasst() ? SyncDataCellBoolean::VALUE_TRUE : SyncDataCellBoolean::VALUE_FALSE) )
				{							
					// invalid value
					$erfasstDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
					$erfasstDataCell->SetValidationErrorMessage("Der Wert 'Vollständig erfasst' unterscheidet sich von dem im System hinterlegten (".($teilabrechnung->GetErfasst() ? "Ja" : "Nein").")");
				}				
			}
			else
			{
				// invalid value
				$erfasstDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
			}
		}
		return Array(self::COLUMN_ID_TEILABRECHNUNGID, self::COLUMN_ID_ABRECHNUNGSJAHR, self::COLUMN_ID_CONTRACTID, self::COLUMN_ID_FMSID, self::COLUMN_ID_LOCATIONNAME, self::COLUMN_ID_COMPANYNAME, self::COLUMN_ID_GROUPNAME, self::COLUMN_ID_SHOPNAME, self::COLUMN_ID_ABRECHNUNGSNAME, self::COLUMN_ID_ERFASST);
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
		/* @var $object Teilabrechnungsposition */
		switch($columnId)
		{
			case self::COLUMN_ID_FMSID:
			case self::COLUMN_ID_CONTRACTID:
			case self::COLUMN_ID_TEILABRECHNUNGID:
			case self::COLUMN_ID_TEILABRECHNUNGSPOSITIONID:
			case self::COLUMN_ID_GROUPNAME:
			case self::COLUMN_ID_COMPANYNAME:
			case self::COLUMN_ID_LOCATIONNAME:
			case self::COLUMN_ID_SHOPNAME:
			case self::COLUMN_ID_ABRECHNUNGSJAHR:
			case self::COLUMN_ID_ABRECHNUNGSNAME:
			case self::COLUMN_ID_ERFASST:
				return true;
			case self::COLUMN_ID_AREANAME:
				return $object->SetBezeichnungTeilflaeche($dataCell->GetValue());
			case self::COLUMN_ID_KOSTENART:
				return $object->SetBezeichnungKostenart($dataCell->GetValue());
			case self::COLUMN_ID_KOSTENART_FMS:
				if ($dataCell->GetValue()!=-1 && !isset($this->kostenartenPkeyToKostenartMap[$dataCell->GetValue()])) return false;
				return $object->SetKostenartRS($dataCell->GetValue()==-1 ? null : $this->kostenartenPkeyToKostenartMap[$dataCell->GetValue()]);
			case self::COLUMN_ID_GESAMTEINHEITEN:
				return $object->SetGesamteinheiten($dataCell->GetValue());
			case self::COLUMN_ID_GESAMTEINHEITENEINHEIT:
				return $object->SetGesamteinheitenEinheit($dataCell->GetValue());
			case self::COLUMN_ID_EINHEITKUNDE:
				return $object->SetEinheitKunde($dataCell->GetValue());
			case self::COLUMN_ID_EINHEITKUNDEEINHEIT:
				return $object->SetEinheitKundeEinheit($dataCell->GetValue());
            case self::COLUMN_ID_PAUSCHALE:
                return $object->SetPauschale($dataCell->GetValue()==SyncDataCellBoolean::VALUE_TRUE ? true : false);
			case self::COLUMN_ID_GESAMTBETRAG:
				return $object->SetGesamtbetrag($dataCell->GetValue());
			case self::COLUMN_ID_BETRAG_KUNDE:
				return $object->SetBetragKunde($dataCell->GetValue());
			case self::COLUMN_ID_UMLAGEFAEHIG:
				return $object->SetUmlagefaehig($dataCell->GetValue());
		}
		return false;
	}
	
}
?>