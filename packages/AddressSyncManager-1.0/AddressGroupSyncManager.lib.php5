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
class AddressGroupSyncManager extends SyncManager 
{
	const COLUMN_ID_GROUPID = 1;
	const COLUMN_ID_GROUPNAME = 2;
	
	/**
	 * AddressManager
	 * @var AddressManager 
	 */
	protected $addressManager = null;
	
	/**
	 * AddressGroups
	 * @var AddressGroup[] 
	 */
	protected $addressGroups = Array();
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 */
	public function AddressGroupSyncManager(DBManager $db, ExtendedLanguageManager $languageManager) 
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
		$this->columns[] = new SyncColumn(self::COLUMN_ID_GROUPID, AddressGroup::GetAttributeName($this->languageManager, 'id'), true, '', new SyncDataCellId(true, AddressGroup::ID_PREFIX));
		$this->columns[] = new SyncColumn(self::COLUMN_ID_GROUPNAME, AddressGroup::GetAttributeName($this->languageManager, 'name'), false);
	}
	
	/**
	 * Initialize 
	 */
	protected function LoadDataFromDatabase()
	{
		// Load all AddressGroups from db (if not allready done)
		if (count($this->addressGroups)==0)
		{
			$this->addressGroups = $this->addressManager->GetAddressGroupData();
		}
		
		$this->LoadDataFromObjects($this->addressGroups);
	}
	
	/**
	 * Return an new instance of the data object
	 * @param DBManager $db
	 * @param SyncDataRow $dataRow
	 * @return DBEntry
	 */
	protected function CreateNewDataObject(DBManager $db, SyncDataRow $dataRow)
	{
		return new AddressGroup($db);
	}
	
	/**
	 * Return the value of the object for the specified column
	 * @param int $columnId
	 * @param DBEntry $object
	 * @return mixed
	 */
	protected function GetValueForColumnFromObject($columnId, DBEntry $object)
	{
		/*@var $object AddressGroup */
		switch($columnId)
		{
			case self::COLUMN_ID_GROUPID:
				return AddressGroup::ID_PREFIX.$object->GetPKey();
			case self::COLUMN_ID_GROUPNAME:
				return $object->GetName();
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
		/* @var $object AddressGroup */
		switch($columnId)
		{
			case self::COLUMN_ID_GROUPNAME:
				return $dataCell->GetValue()!=$object->GetName();
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
		$idDataCell = $dataRow->GetDataCellForColumn(self::COLUMN_ID_GROUPID);
		$id = trim($idDataCell->GetValue());
		if ($id=="")
		{
			// no id is set --> new entry
			$idDataCell->SetValidationState(SyncDataCell::STATE_NEW);
		}
		else
		{
			// check if id exists
			$objectInstance = $this->addressManager->GetAddressGroupByPkey($this->db, (int)$id);
			if ($objectInstance==null)
			{
				// invalid value
				$idDataCell->SetValidationState(SyncDataCell::STATE_INVALID_VALUE);
				$idDataCell->SetValidationErrorMessage("Eine Adressgruppe mit dieser ID existiert nicht");
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
		/*@var $object AddressGroup */
		switch($columnId)
		{
			case self::COLUMN_ID_GROUPID:
				return true;
			case self::COLUMN_ID_GROUPNAME:
				return $object->SetName($dataCell->GetValue());
		}
		return false;
	}
		
}
?>