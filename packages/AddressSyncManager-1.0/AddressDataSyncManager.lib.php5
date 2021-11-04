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
class AddressDataSyncManager extends SyncManager 
{
	const COLUMN_ID_DATAID = 1;
	const COLUMN_ID_DATATITLE = 2;
	const COLUMN_ID_DATATITLE2 = 3;
	const COLUMN_ID_DATANAME = 4;
	const COLUMN_ID_DATAFIRSTNAME = 5;
	const COLUMN_ID_DATAROLE = 6;
	const COLUMN_ID_DATAEMAIL = 7;
	const COLUMN_ID_DATASTREET = 8;
	const COLUMN_ID_DATAZIP = 9;
	const COLUMN_ID_DATACITY = 10;
	const COLUMN_ID_DATAPHONE = 11;
	const COLUMN_ID_DATAMOBILE = 12;
	const COLUMN_ID_DATAFAX = 13;
	//const COLUMN_ID_DATATYPE = 14;
	const COLUMN_ID_COMPANYID = 15;
	
	/**
	 * AddressManager
	 * @var AddressManager 
	 */
	protected $addressManager = null;
	
	/**
	 * AddressData
	 * @var AddressData[] 
	 */
	protected $addressData = Array();
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 */
	public function AddressDataSyncManager(DBManager $db, ExtendedLanguageManager $languageManager) 
	{
		// init needed managers
		$this->addressManager = new AddressManager($db);
		
		// call parent constructor
		parent::__construct($db, $languageManager);
	}
	
	/**
	 * Initialize the column objects for this SyncManager
	 */
	protected function InitColumns()
	{
		global $AM_ADDRESSDATA_TYPE;
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATAID, AddressData::GetAttributeName($this->languageManager, 'id'), true, '', new SyncDataCellId(true, AddressData::ID_PREFIX));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATATITLE, AddressData::GetAttributeName($this->languageManager, 'title'), false, '', new SyncDataCellIdToTextMap(Array( 0 => 'Herr', 1 => 'Frau')));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATATITLE2, AddressData::GetAttributeName($this->languageManager, 'title2'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATANAME, AddressData::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATAFIRSTNAME, AddressData::GetAttributeName($this->languageManager, 'firstname'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATAROLE, AddressData::GetAttributeName($this->languageManager, 'role'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATAEMAIL, AddressData::GetAttributeName($this->languageManager, 'email'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATASTREET, AddressData::GetAttributeName($this->languageManager, 'street'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATAZIP, AddressData::GetAttributeName($this->languageManager, 'zip'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATACITY, AddressData::GetAttributeName($this->languageManager, 'city'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATAPHONE, AddressData::GetAttributeName($this->languageManager, 'phone'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATAMOBILE, AddressData::GetAttributeName($this->languageManager, 'mobile'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_DATAFAX, AddressData::GetAttributeName($this->languageManager, 'fax'));
		//$this->columns[] = new SyncColumn(self::COLUMN_ID_DATATYPE, AddressData::GetAttributeName($this->languageManager, 'type'), false, '', new SyncDataCellIdToTextMap($AM_ADDRESSDATA_TYPE, AM_ADDRESSDATA_TYPE_NONE));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYID, AddressCompany::GetAttributeName($this->languageManager, 'id'), true, '', new SyncDataCellId(true, AddressCompany::ID_PREFIX));
	}
	
	/**
	 * Initialize 
	 */
	protected function LoadDataFromDatabase()
	{
		// Load all addressCompanies from db (if not allready done)
		if (count($this->addressData)==0)
		{
			$this->addressData = $this->addressManager->GetAddressData();
		}
		
		$this->LoadDataFromObjects($this->addressData);
	}
	
	/**
	 * Return an new instance of the data object
	 * @param DBManager $db
	 * @param SyncDataRow $dataRow
	 * @return DBEntry
	 */
	protected function CreateNewDataObject(DBManager $db, SyncDataRow $dataRow)
	{
		return new AddressData($db);
	}
	
	/**
	 * Return the value of the object for the specified column
	 * @param int $columnId
	 * @param AddressCompany $object
	 * @return mixed
	 */
	protected function GetValueForColumnFromObject($columnId, DBEntry $object)
	{
		/*@var $object AddressData */
		switch($columnId)
		{
			case self::COLUMN_ID_DATAID:
				return AddressData::ID_PREFIX.$object->GetPKey();
			case self::COLUMN_ID_DATATITLE:
				return (int)$object->GetTitle();
			case self::COLUMN_ID_DATATITLE2:
				return $object->GetTitle2();
			case self::COLUMN_ID_DATANAME:
				return $object->GetName();
			case self::COLUMN_ID_DATAFIRSTNAME:
				return $object->GetFirstName();
			case self::COLUMN_ID_DATAROLE:
				return $object->GetRole();
			case self::COLUMN_ID_DATAEMAIL:
				return $object->GetEMail();
			case self::COLUMN_ID_DATASTREET:
				return $object->GetStreet(true);
			case self::COLUMN_ID_DATAZIP:
				return $object->GetZIP(true);
			case self::COLUMN_ID_DATACITY:
				return $object->GetCity(true);
			case self::COLUMN_ID_DATAPHONE:
				return $object->GetPhone();
			case self::COLUMN_ID_DATAMOBILE:
				return $object->GetMobile();
			case self::COLUMN_ID_DATAFAX:
				return $object->GetFax();
			/*case self::COLUMN_ID_DATATYPE:
				return $object->GetType();*/
			case self::COLUMN_ID_COMPANYID:
				return ($object->GetAddressCompany()==null ? "" : AddressCompany::ID_PREFIX.$object->GetAddressCompany()->GetPKey());
			
		}
		return "[ERROR: UNKNOWN COLUMN ID '".$columnId."']";
	}
	
	/**
	 * Return the value of the object for the specified column
	 * @param int $columnId
	 * @param SyncDataCell $dataCell
	 * @param DBEntry $object
     * @param SyncDataRow $dataRow
	 * @return boolean
	 */
	protected function HasValueChanged($columnId, SyncDataCell $dataCell, DBEntry $object, SyncDataRow $dataRow)
	{
		/* @var $object AddressData */
		switch($columnId)
		{
			case self::COLUMN_ID_DATATITLE:
				return $dataCell->GetValue()!=(int)$object->GetTitle();
			case self::COLUMN_ID_DATATITLE2:
				return $dataCell->GetValue()!=$object->GetTitle2();
			case self::COLUMN_ID_DATANAME:
				return $dataCell->GetValue()!=$object->GetName();
			case self::COLUMN_ID_DATAFIRSTNAME:
				return $dataCell->GetValue()!=$object->GetFirstName();
			case self::COLUMN_ID_DATAROLE:
				return $dataCell->GetValue()!=$object->GetRole();
			case self::COLUMN_ID_DATAEMAIL:
				return $dataCell->GetValue()!=$object->GetEMail();
			case self::COLUMN_ID_DATASTREET:
				return $dataCell->GetValue()!=$object->GetStreet(true);
			case self::COLUMN_ID_DATAZIP:
				return $dataCell->GetValue()!=$object->GetZIP(true);
			case self::COLUMN_ID_DATACITY:
				return $dataCell->GetValue()!=$object->GetCity(true);
			case self::COLUMN_ID_DATAPHONE:
				return $dataCell->GetValue()!=$object->GetPhone();
			case self::COLUMN_ID_DATAMOBILE:
				return $dataCell->GetValue()!=$object->GetMobile();
			case self::COLUMN_ID_DATAFAX:
				return $dataCell->GetValue()!=$object->GetFax();
			case self::COLUMN_ID_COMPANYID:
				$id = ($object->GetAddressCompany()==null ? "" : $object->GetAddressCompany()->GetPKey());
				if ($dataCell->GetValue()==$id) return false;
				// if new id is no id -> ok
				if ($dataCell->GetValue()=='') return true;
				// check if new group exist
				if ($this->addressManager->GetAddressCompanyByPkey($this->db, $dataCell->GetValue())==null)
				{
					// company dosen't exist
					$dataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
					$dataCell->SetValidationErrorMessage("Eine Firma mit dieser ID existiert nicht");
					return false;
				}
				return true;
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
		$idDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_DATAID);
		$id = trim($idDataCell->GetValue());
		if ($id=="")
		{
			// no id is set --> new entry
			$idDataCell->SetValidationState(SyncDataCell::STATE_NEW);
		}
		else
		{
			// check if id exists
			$objectInstance = $this->addressManager->GetAddressDataByPkey($this->db, (int)$id);
			if ($objectInstance==null)
			{
				// invalid value
				$idDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$idDataCell->SetValidationErrorMessage("Einen Ansprechpartner mit dieser ID existiert nicht");
			}
		}
		return $objectInstance;
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
		/*@var $object AddressData */
		switch($columnId)
		{
			case self::COLUMN_ID_DATAID:
				return true;
			case self::COLUMN_ID_DATATITLE:
				return $object->SetTitle((int)$dataCell->GetValue());
			case self::COLUMN_ID_DATATITLE2:
				return $object->SetTitle2($dataCell->GetValue());
			case self::COLUMN_ID_DATANAME:
				return $object->SetName($dataCell->GetValue());
			case self::COLUMN_ID_DATAFIRSTNAME:
				return $object->SetFirstName($dataCell->GetValue());
			case self::COLUMN_ID_DATAROLE:
				return $object->SetRole($dataCell->GetValue());
			case self::COLUMN_ID_DATAEMAIL:
				return $object->SetEMail($dataCell->GetValue());
			case self::COLUMN_ID_DATASTREET:
				return $object->SetStreet($dataCell->GetValue());
			case self::COLUMN_ID_DATAZIP:
				return $object->SetZIP($dataCell->GetValue());
			case self::COLUMN_ID_DATACITY:
				return $object->SetCity($dataCell->GetValue());
			case self::COLUMN_ID_DATAPHONE:
				return $object->SetPhone($dataCell->GetValue());
			case self::COLUMN_ID_DATAMOBILE:
				return $object->SetMobile($dataCell->GetValue());
			case self::COLUMN_ID_DATAFAX:
				return $object->SetFax($dataCell->GetValue());
			case self::COLUMN_ID_COMPANYID:
				$addressCompany = null;
				$id = $dataCell->GetValue();
				if ($id!='' && (int)$id>0)
				{
					$addressCompany = $this->addressManager->GetAddressCompanyByPkey($this->db, (int)$id);
				}
				return $object->SetAddressCompany($addressCompany);
		}
		return false;
	}
	
}
?>