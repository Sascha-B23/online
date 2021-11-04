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
class AddressCompanySyncManager extends SyncManager 
{
	const COLUMN_ID_COMPANYID = 1;
	const COLUMN_ID_COMPANYNAME = 2;
	const COLUMN_ID_COMPANYEMAIL = 3;
	const COLUMN_ID_COMPANYSTREET = 4;
	const COLUMN_ID_COMPANYZIP = 5;
	const COLUMN_ID_COMPANYCITY = 6;
	const COLUMN_ID_COMPANYPHONE = 7;
	const COLUMN_ID_COMPANYFAX = 8;
	const COLUMN_ID_COMPANYCOUNTRY = 9;
	const COLUMN_ID_COMPANYWEBSITE = 10;
	const COLUMN_ID_GROUPID = 11;
	
	/**
	 * AddressManager
	 * @var AddressManager 
	 */
	protected $addressManager = null;
	
	/**
	 * AddressCompanies
	 * @var AddressCompany[] 
	 */
	protected $addressCompanies = Array();
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 */
	public function AddressCompanySyncManager(DBManager $db, ExtendedLanguageManager $languageManager) 
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
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYID, AddressCompany::GetAttributeName($this->languageManager, 'id'), true, '', new SyncDataCellId(true, AddressCompany::ID_PREFIX));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYNAME, AddressCompany::GetAttributeName($this->languageManager, 'name'), false);
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYEMAIL, AddressCompany::GetAttributeName($this->languageManager, 'email'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYSTREET, AddressCompany::GetAttributeName($this->languageManager, 'street'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYZIP, AddressCompany::GetAttributeName($this->languageManager, 'zip'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYCITY, AddressCompany::GetAttributeName($this->languageManager, 'city'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYPHONE, AddressCompany::GetAttributeName($this->languageManager, 'phone'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYFAX, AddressCompany::GetAttributeName($this->languageManager, 'fax'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYCOUNTRY, AddressCompany::GetAttributeName($this->languageManager, 'country'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_COMPANYWEBSITE, AddressCompany::GetAttributeName($this->languageManager, 'website'));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_GROUPID, AddressGroup::GetAttributeName($this->languageManager, 'id'), true, '', new SyncDataCellId(true, AddressGroup::ID_PREFIX));
	}
	
	/**
	 * Initialize 
	 */
	protected function LoadDataFromDatabase()
	{
		// Load all addressCompanies from db (if not allready done)
		if (count($this->addressCompanies)==0)
		{
			$this->addressCompanies = $this->addressManager->GetAddressCompany();
		}
		
		$this->LoadDataFromObjects($this->addressCompanies);
	}
	
	/**
	 * Return an new instance of the data object
	 * @param DBManager $db
	 * @param SyncDataRow $dataRow
	 * @return DBEntry
	 */
	protected function CreateNewDataObject(DBManager $db, SyncDataRow $dataRow)
	{
		return new AddressCompany($db);
	}
	
	/**
	 * Return the value of the object for the specified column
	 * @param int $columnId
	 * @param AddressCompany $object
	 * @return mixed
	 */
	protected function GetValueForColumnFromObject($columnId, DBEntry $object)
	{
		/*@var $object AddressCompany */
		switch($columnId)
		{
			case self::COLUMN_ID_COMPANYID:
				return AddressCompany::ID_PREFIX.$object->GetPKey();
			case self::COLUMN_ID_COMPANYNAME:
				return $object->GetName();
			case self::COLUMN_ID_COMPANYEMAIL:
				return $object->GetEMail();
			case self::COLUMN_ID_COMPANYSTREET:
				return $object->GetStreet();
			case self::COLUMN_ID_COMPANYZIP:
				return $object->GetZIP();
			case self::COLUMN_ID_COMPANYCITY:
				return $object->GetCity();
			case self::COLUMN_ID_COMPANYPHONE:
				return $object->GetPhone();
			case self::COLUMN_ID_COMPANYFAX:
				return $object->GetFax();
			case self::COLUMN_ID_COMPANYCOUNTRY:
				return $object->GetCountry();
			case self::COLUMN_ID_COMPANYWEBSITE:
				return $object->GetWebsite();
			case self::COLUMN_ID_GROUPID:
				return ($object->GetAddressGroup()==null ? "" : AddressGroup::ID_PREFIX.$object->GetAddressGroup()->GetPKey());
			
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
		/* @var $object AddressCompany */
		switch($columnId)
		{
			case self::COLUMN_ID_COMPANYNAME:
				return $dataCell->GetValue()!=$object->GetName();
			case self::COLUMN_ID_COMPANYEMAIL:
				return $dataCell->GetValue()!=$object->GetEMail();
			case self::COLUMN_ID_COMPANYSTREET:
				return $dataCell->GetValue()!=$object->GetStreet();
			case self::COLUMN_ID_COMPANYZIP:
				return $dataCell->GetValue()!=$object->GetZIP();
			case self::COLUMN_ID_COMPANYCITY:
				return $dataCell->GetValue()!=$object->GetCity();
			case self::COLUMN_ID_COMPANYPHONE:
				return $dataCell->GetValue()!=$object->GetPhone();
			case self::COLUMN_ID_COMPANYFAX:
				return $dataCell->GetValue()!=$object->GetFax();
			case self::COLUMN_ID_COMPANYCOUNTRY:
				return $dataCell->GetValue()!=$object->GetCountry();
			case self::COLUMN_ID_COMPANYWEBSITE:
				return $dataCell->GetValue()!=$object->GetWebsite();
			case self::COLUMN_ID_GROUPID:
				$groupId = ($object->GetAddressGroup()==null ? "" : $object->GetAddressGroup()->GetPKey());
				if ($dataCell->GetValue()==$groupId) return false;
				// if new group is no group -> ok
				if ($dataCell->GetValue()=='') return true;
				// check if new group exist
				if ($this->addressManager->GetAddressGroupByPkey($this->db, $dataCell->GetValue())==null)
				{
					// group dosen't exist
					$dataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
					$dataCell->SetValidationErrorMessage("Eine Adressgruppe mit dieser ID existiert nicht");
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
		$idDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_COMPANYID);
		$id = trim($idDataCell->GetValue());
		if ($id=="")
		{
			// no id is set --> new entry
			$idDataCell->SetValidationState(SyncDataCell::STATE_NEW);
		}
		else
		{
			// check if id exists
			$objectInstance = $this->addressManager->GetAddressCompanyByPkey($this->db, (int)$id);
			if ($objectInstance==null)
			{
				// invalid value
				$idDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$idDataCell->SetValidationErrorMessage("Eine Firma mit dieser ID existiert nicht");
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
		/*@var $object AddressCompany */
		switch($columnId)
		{
			case self::COLUMN_ID_COMPANYID:
				return true;
			case self::COLUMN_ID_COMPANYNAME:
				return $object->SetName($dataCell->GetValue());
			case self::COLUMN_ID_COMPANYEMAIL:
				return ($object->SetEMail($dataCell->GetValue())===true ? true : false);
			case self::COLUMN_ID_COMPANYSTREET:
				return $object->SetStreet($dataCell->GetValue());
			case self::COLUMN_ID_COMPANYZIP:
				return $object->SetZIP($dataCell->GetValue());
			case self::COLUMN_ID_COMPANYCITY:
				return $object->SetCity($dataCell->GetValue());
			case self::COLUMN_ID_COMPANYPHONE:
				return $object->SetPhone($dataCell->GetValue());
			case self::COLUMN_ID_COMPANYFAX:
				return $object->SetFax($dataCell->GetValue());
			case self::COLUMN_ID_COMPANYCOUNTRY:
				return $object->SetCountry($dataCell->GetValue());
			case self::COLUMN_ID_COMPANYWEBSITE:
				return $object->SetWebsite($dataCell->GetValue());
			case self::COLUMN_ID_GROUPID:
				$addressGroup = null;
				$groupId = $dataCell->GetValue();
				if ($groupId!='' && (int)$groupId>0)
				{
					$addressGroup = $this->addressManager->GetAddressGroupByPkey($this->db, (int)$groupId);
				}
				return $object->SetAddressGroup($addressGroup);
		}
		return false;
	}
	
}
?>