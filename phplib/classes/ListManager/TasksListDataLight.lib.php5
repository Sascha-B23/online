<?php

/**
 * ListData-Implementierung für Aufgaben
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2019 Stoll von Gáti GmbH www.stollvongati.com
 */
class TasksListDataLight extends DynamicTable
{
        const COLUMN_PROGRESS = 1;
        const COLUMN_TYPE = 2;
        const COLUMN_STATUS = 3;
        const COLUMN_COUNTRY = 4;
        const COLUMN_LOCATION = 5;
        const COLUMN_SHOP = 6;
        const COLUMN_YEAR = 8;
        const COLUMN_GROUP_CUSTOMER = 9;
        const COLUMN_GROUP_TRUSTEE = 10;
        const COLUMN_FMS_LOCATION = 14;
        const COLUMN_FMS_TASK = 15;
        const COLUMN_CUSTOMER = 16;
        const COLUMN_INFO = 17;
        const COLUMN_OPTIONS = 18;

	/**
	 * WorkflowManager-Objekt
	 * @var WorkflowManager
	 */
	protected $manager = null;
	
	/**
	 * GroupID to retrive tasks for (paramter for request to only retrive Tasks of a specific Group)
	 * @var int
	 */	
	protected $groupIdToRetriveTasks = -1;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 */
	public function TasksListDataLight(DBManager $db)
	{
		$this->useSession = true;
		
		global $workflowManager;
		$this->manager = $workflowManager;
		
		if ($this->GetDynamicTableInformation() == null)
		{
			global $SHARED_HTTP_ROOT;
			$information = new DynamicTableInformation();
			$information->SetIcon($SHARED_HTTP_ROOT."pics/gui/activeTask.png");
			$information->SetHeadline("Aufgaben");
			$this->SetDynamicTableInformation($information);
		}
		parent::__construct($db);
	}
	
	/**
	 * Set the current DBManager
	 * @param DBManager $db
	 */
	public function ResetObjects(DBManager $db)
	{
		parent::ResetObjects($db);
		global $workflowManager;
		$this->manager = $workflowManager;
	}
	
	/**
	 * Return the default colmn config for this list
	 * @param DBManager $db
	 * @return DynamicTableColumn[]
	 */
	protected function GetDefaultColumnConfig(DBManager $db)
	{
		global $customerManager, $rsKostenartManager, $userManager, $lm;
		/*@var $customerManager CustomerManager*/
		/*@var $rsKostenartManager RSKostenartManager*/
		/*@var $userManager UserManager*/
		$usersGroupBasetype = $_SESSION["currentUser"]->GetGroupBasetype($db);
		// get all FMS-Users for Dropdownlists
		$fmsUsers = Array();
		if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			$fmsUsers1 = $userManager->GetUsers($_SESSION["currentUser"], "", "email", 0, 0, 0, UM_GROUP_BASETYPE_RSMITARBEITER);
			$fmsUsers2 = $userManager->GetUsers($_SESSION["currentUser"], "", "email", 0, 0, 0, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT);
			$fmsUsers3 = $userManager->GetUsers($_SESSION["currentUser"], "", "email", 0, 0, 0, UM_GROUP_BASETYPE_AUSHILFE);
			$fmsUsers4 = $userManager->GetUsers($_SESSION["currentUser"], "", "email", 0, 0, 0, UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT);
			$fmsUsers = array_merge($fmsUsers1, $fmsUsers2, $fmsUsers3, $fmsUsers4);
			usort($fmsUsers, function(User $a, User $b){return strcmp($a->GetEMail(), $b->GetEMail());});
		}
		
		$columns = Array();

		if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			// Bearbeitungsstatus  OK
			$column = new DynamicTableColumn(self::COLUMN_PROGRESS, Schedule::GetAttributeName($lm, 'archiveStatus'), "archiveStatus", DynamicTableColumn::TYPE_INT, false);
			// add filters to column
			$filter = new DynamicTableFilterCheckbox(3);
			$filter->AddCheckbox(0, "archiviert", false);
			$filter->AddCheckbox(1, "auf Stand zu bringen", false);
			$filter->AddCheckbox(2, "bereits auf Stand", true);
			$column->AddFilter($filter);
			// add column
			$columns[] = $column;
		

		}
		// Aktiv OK
		$column = new DynamicTableColumn(self::COLUMN_TYPE, "Aktiv", "active", DynamicTableColumn::TYPE_INT);
		// add filters to column
		$filter = new DynamicTableFilterCheckbox(3);
		$filter->AddCheckbox(0, "Aktiv", true);
		if ($usersGroupBasetype!=UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE) // Buchhaltung soll keine passiven Prozesse einblenden können
		{
			$filter->AddCheckbox(1, "Passiv", false);
		}
		$filter->SetQuery(new ActiveQueryLight($filter));
		$column->AddFilter($filter);
		// add column
		$columns[] = $column;
		
		// Status OK
		$column = new DynamicTableColumn(self::COLUMN_STATUS, WorkflowStatus::GetAttributeName($lm, 'currentStatus'), "currentStatus", DynamicTableColumn::TYPE_INT);
		// add filters to column
		$filter = new DynamicTableFilterCheckbox(3);
		global $processStatusConfig;
		$excludeFromStandardSelection = Array(7, 26);
		for($a=0; $a<count($processStatusConfig); $a++)
		{
			$filter->AddCheckbox($processStatusConfig[$a]["ID"], $processStatusConfig[$a][$usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP ? "name" : "nameCustomer"], in_array($processStatusConfig[$a]["ID"], $excludeFromStandardSelection) ? false : true);
		}
		$column->AddFilter($filter);
		// add column
		$columns[] = $column;

		// Land OK
		$column = new DynamicTableColumn(self::COLUMN_COUNTRY, CLocation::GetAttributeName($lm, 'country'), "country", DynamicTableColumn::TYPE_STRING, false);
		// add filters to column
		$filter = new DynamicTableFilterCheckbox(3);
		$countries = $customerManager->GetCountries("", "name", 0, 0, 0);
		foreach ($countries as $country)
		{
			$filter->AddCheckbox($country->GetIso3166(), $country->GetName()." (".$country->GetIso3166().")", true);
		}
		$column->AddFilter($filter);
		// add column
		$columns[] = $column;
		
		// Standort OK
		$column = new DynamicTableColumn(self::COLUMN_LOCATION, CLocation::GetAttributeName($lm, 'name'), "locationName", DynamicTableColumn::TYPE_STRING);
		// add filters to column
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TEXTSEARCH, 3));
		// add column
		$columns[] = $column;
		
		// Laden OK
		$column = new DynamicTableColumn(self::COLUMN_SHOP, CShop::GetAttributeName($lm, 'name'), "shopName", DynamicTableColumn::TYPE_STRING);
		// add filters to column
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TEXTSEARCH, 3));
		// add column
		$columns[] = $column;

		// Jahr OK
		$column = new DynamicTableColumn(self::COLUMN_YEAR, AbrechnungsJahr::GetAttributeName($lm, 'jahr'), "jahr", DynamicTableColumn::TYPE_INT);
		// add filters to column
		$filter = new DynamicTableFilterCheckbox(3);
		$years = $rsKostenartManager->GetYears($_SESSION["currentUser"]);
		foreach ($years as $year)
		{
			$filter->AddCheckbox($year, $year, true);
		}
		$column->AddFilter($filter);
		// add column
		$columns[] = $column;
		
		if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			// Gruppe Kunde OK
			$column = new DynamicTableColumn(self::COLUMN_GROUP_CUSTOMER, CGroup::GetAttributeName($lm, 'name'), "groupId", DynamicTableColumn::TYPE_INT, false, true, "groupName");
			// add filters to column
			$filter = new DynamicTableFilterCheckbox(3);
			$groups = $customerManager->GetGroups($_SESSION["currentUser"], "", "name", 0, 0, 0);
			foreach ($groups as $group)
			{
				$filter->AddCheckbox($group->GetPKey(), $group->GetName(), true);
			}
			$column->AddFilter($filter);
			// add column
			$columns[] = $column;
		}
		
		// Gruppe Verwalter OK
		$column = new DynamicTableColumn(self::COLUMN_GROUP_TRUSTEE, "Gruppe Verwalter", "verwalterGroupName", DynamicTableColumn::TYPE_STRING, false);
		// add filters to column
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TEXTSEARCH, 3));
		// add column
		$columns[] = $column;

		// FMS-AP Standort OK
		$column = new DynamicTableColumn(self::COLUMN_FMS_LOCATION, "SFM-AP Standort", "cPersonRS", DynamicTableColumn::TYPE_INT, $usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP ? false : true);
		if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			// add filters to column
			$filter = new DynamicTableFilterCheckbox(3);
			foreach ($fmsUsers as $user)
			{
				$filter->AddCheckbox($user->GetPKey(), $user->GetUserName()." (".$user->GetShortName().")", ($_SESSION["currentUser"]->GetPKey()==$user->GetPKey() || $usersGroupBasetype==UM_GROUP_BASETYPE_SUPERUSER || $usersGroupBasetype==UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT || $usersGroupBasetype==UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT || $usersGroupBasetype==UM_GROUP_BASETYPE_AUSHILFE ? true : false));
			}
			$column->AddFilter($filter);
		}
		// add column
		$columns[] = $column;
		
		if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			// FMS-AP Prozess OK
			$column = new DynamicTableColumn(self::COLUMN_FMS_TASK, "SFM-AP Prozess", "currentResponsibleRSUser", DynamicTableColumn::TYPE_INT);
			// add filters to column
			$filter = new DynamicTableFilterCheckbox(3);
			foreach ($fmsUsers as $user)
			{
				$filter->AddCheckbox($user->GetPKey(), $user->GetUserName()." (".$user->GetShortName().")", true);
			}
			$column->AddFilter($filter);
			// add column
			$columns[] = $column;
		}

		// Kunde-AP OK
		$column = new DynamicTableColumn(self::COLUMN_CUSTOMER, "Kunde-AP", "cPersonCustomer", DynamicTableColumn::TYPE_INT, $usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP ? false : true);
		// add filters to column
		$filter = new DynamicTableFilterCheckbox(3);
		$users = $userManager->GetUsers($_SESSION["currentUser"], "", "email", 0, 0, 0, UM_GROUP_BASETYPE_KUNDE);
		foreach ($users as $user)
		{
			if ($user->GetGroupBasetype($db)>$usersGroupBasetype) continue;
			$filter->AddCheckbox($user->GetPKey(), $user->GetUserName()." (".$user->GetShortName().")", $usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP || $_SESSION["currentUser"]->GetPKey()==$user->GetPKey() ? true : false);
		}
		$column->AddFilter($filter);
		// add column
		$columns[] = $column;

		// Info
		$column = new DynamicTableColumn(self::COLUMN_INFO, "Info", "", DynamicTableColumn::TYPE_STRING, true, false);
		$column->SetCssStyle("width", "75px");
		$column->SetCssStyle("vertical-align", "top");
		// add column
		$columns[] = $column;
		
		// Optionen
		$column = new DynamicTableColumn(self::COLUMN_OPTIONS, "Optionen", "", DynamicTableColumn::TYPE_STRING, true, false);
		$column->SetCssStyle("width", "100px");
		// add column
		$columns[] = $column;
		
		return $columns;
	}
	
	/**
	 * Return the total number of entries 
	 * @return int
	 */
	protected function GetTotalDataCount()
	{
		$information = $this->GetDynamicTableInformation();
		$searchString = $information->GetSearch();
		
		$additionalWhereClause = "";
		foreach ($this->dynamicTableColumns as $column)
		{
			$tempValue = trim($column->GetWhereClause($this->db));
			if ($tempValue!="")
			{
				if ($additionalWhereClause!="") $additionalWhereClause.=" AND ";
				$additionalWhereClause.="(".$tempValue.")";
			}
		}
		return $this->manager->GetWorkflowStatusCount($searchString, $additionalWhereClause, true, true);
	}
	
	/**
	 * Return the contetn of the table cell for the specified column of the passed status 
	 * @param DBManager $db
	 * @param ProcessStatus $status
	 * @param int $column
	 * @param DynamicTableRow $dynamicTableRow
	 */
	private function GetCellDataContent(DBManager $db, ProcessStatus $status, $columnId, DynamicTableRow $dynamicTableRow)
	{
		global $SHARED_HTTP_ROOT;
		$usersGroupBasetype = $_SESSION["currentUser"]->GetGroupBasetype($db);
		$returnValue = "-";
		switch($columnId)
		{
			case self::COLUMN_PROGRESS:
				$returnValue = $status->GetArchiveStatusName();
				break;
			case self::COLUMN_TYPE:
				$returnValue = (WorkflowManager::UserGroupResponsibleForProzess($status->GetCurrentStatus(), ($usersGroupBasetype>UM_GROUP_BASETYPE_RSMITARBEITER ? UM_GROUP_BASETYPE_RSMITARBEITER : $usersGroupBasetype) ) ? "aktiv" : "passiv");
				break;
			case self::COLUMN_STATUS:
				$returnValue = $status->GetCurrentStatusName($_SESSION["currentUser"], $db);
				break;
			case self::COLUMN_COUNTRY:
				$returnValue = $status->GetCountryName();
				break;
			case self::COLUMN_LOCATION:
				$returnValue = $status->GetLocationName();
				break;
			case self::COLUMN_SHOP:
				$returnValue = $status->GetShopName();
				break;
			case self::COLUMN_YEAR:
				$returnValue = $status->GetAbrechnungsJahrString();
				break;
			case self::COLUMN_GROUP_CUSTOMER:
				$cGroup = $status->GetGroup();
				if ($cGroup!=null) $returnValue = $cGroup->GetName();
				break;
			case self::COLUMN_GROUP_TRUSTEE:
				$returnValue = $status->GetGroupTrusteeName($db);
				break;
			case self::COLUMN_FMS_LOCATION:
				$user = $status->GetResponsibleRSUser();
				if ($user!=null) $returnValue = "<span title='".$user->GetUserName()."' alt='".$user->GetUserName()."'>".$user->GetShortName()."<span>";
				break;
			case self::COLUMN_FMS_TASK:
				$user = $status->GetCurrentResponsibleRSUser($db);
				if ($user!=null) $returnValue = "<span title='".$user->GetUserName()."' alt='".$user->GetUserName()."'>".$user->GetShortName()."<span>";
				break;
			case self::COLUMN_CUSTOMER:
				$user = $status->GetResponsibleCustomer();
				if ($user!=null) $returnValue = "<span title='".$user->GetUserName()."' alt='".$user->GetUserName()."'>".$user->GetShortName()."<span>";
				break;

			case self::COLUMN_INFO:
				$returnValue = "";
				// Comment
				if (trim($status->GetScheduleComment())!="")
				{
					$returnValue.="<img src='".$SHARED_HTTP_ROOT."pics/gui/comment.png' width='25' height='25' style='cursor: help;' alt='".str_replace("'", "\"", trim($status->GetScheduleComment()))."' title='".str_replace("'", "\"", trim($status->GetScheduleComment()))."' />";
				}
				if ($returnValue=="") $returnValue = "&#160;";
				break;
			case self::COLUMN_OPTIONS:
				$returnValue = "";
				if (is_a($status, "ProcessStatusGroup"))
				{
					// ProcessStatusGroup instance
					
					// ProcessStatus instance
					if (WorkflowManager::UserAllowedToEditProzess($db, $status->GetCurrentStatus()))
					{
						$returnValue.="<a href='process.php5?processId=".WorkflowManager::GetProcessStatusId($status)."'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/edit.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/edit_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/edit.png'\" alt=\"Eintrag bearbeiten\" title=\"Eintrag bearbeiten\" /></a>";
					}
					
					if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
					{
						$returnValue.="<a href='javascript:AssignTask(\"".WorkflowManager::GetProcessStatusId($status)."\");'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/assigned.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/assigned_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/assigned.png'\"  alt=\"Benutzer zuweisen\" title=\"Benutzer zuweisen\" /></a>";
					}
					// Terminschiene
					$returnValue.="<a href='".$SHARED_HTTP_ROOT."de/meineaufgaben/showTerminschiene.php5?".SID."&processId=".WorkflowManager::GetProcessStatusId($status)."' target='_blank'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/statistic.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/statistic_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/statistic.png'\"  alt=\"Terminschiene anzeigen\" title=\"Terminschiene anzeigen\" /></a>";
					// Dateien direkt hochladen geht nur bei passiven Aufgaben
					if ($usersGroupBasetype!=UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT)
					{
						if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP || !$status->IsActiveProzess($_SESSION["currentUser"], $db))
						{
							$returnValue.="<a href='javascript: UploadFiles(\"".WorkflowManager::GetProcessStatusId($status)."\");'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/uploadFile.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/uploadFile_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/uploadFile.png'\" alt='Datei hochladen' title='Datei hochladen'></a>";
						}
					}
					// open/close group
					$dynamicTableRow->SetGroupId($status->GetPKey());
					$returnValue.="<a href='javascript: dynamic_table_".$this->GetId().".ToggleGroupElements(".$status->GetPKey().", \"".$SHARED_HTTP_ROOT."pics/gui/\");'><img id='img_".$status->GetPKey()."' src=\"".$SHARED_HTTP_ROOT."pics/gui/group_open.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/group_open_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/group_open.png'\" alt='Prozessgruppe öffnen/schließen' title='Prozessgruppe öffnen/schließen' /></a>";
				}
				else
				{
					$groupMember = $status->GetProcessStatusGroupId()!=0 ? true : false;
					// ProcessStatus instance
					if (WorkflowManager::UserAllowedToEditProzess($db, $status->GetCurrentStatus()))
					{
						if (!$groupMember) $returnValue.="<a href='process.php5?processId=".WorkflowManager::GetProcessStatusId($status)."'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/edit.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/edit_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/edit.png'\" alt=\"Eintrag bearbeiten\" title=\"Eintrag bearbeiten\" /></a>";
					}

					if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
					{
						// Aufgaben planen können nur FMS-Mitarbeiter und auch nur dann, wenn es sich um ihre aktive Aufgaben handelt
						if (!$groupMember) $returnValue.="<a href='javascript:AssignTask(\"".WorkflowManager::GetProcessStatusId($status)."\");'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/assigned.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/assigned_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/assigned.png'\"  alt=\"Benutzer zuweisen\" title=\"Benutzer zuweisen\" /></a>";
						if ($usersGroupBasetype>=UM_GROUP_BASETYPE_RSMITARBEITER)
						{
							// Den Bearbeitungsstatus einer Aufgabe kann nur von FMS-Mitarbeitern geändert werden
							$returnValue.="<a href='javascript: EditTaskState(".$status->GetPKey().");'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/activeTask.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/activeTask_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/activeTask.png'\" alt='Bearbeitungsstatus' title='Bearbeitungsstatus'></a>";
						}
					}
					if ($usersGroupBasetype!=UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT)
					{
						$cLocation = $status->GetLocation();
						if ($cLocation!=null)
						{
							$returnValue.="<a href='".$SHARED_HTTP_ROOT."de/berichte/kundenstandorte.php5?".SID."&location=".$cLocation->GetPKey()."' target='_blank'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/report.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/report_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/report.png'\"  alt=\"Bericht anzeigen\" title=\"Bericht anzeigen\" /></a>";
						}
					}
					// Terminschiene
					$returnValue.="<a href='".$SHARED_HTTP_ROOT."de/meineaufgaben/showTerminschiene.php5?".SID."&processId=".WorkflowManager::GetProcessStatusId($status)."' target='_blank'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/statistic.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/statistic_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/statistic.png'\"  alt=\"Terminschiene anzeigen\" title=\"Terminschiene anzeigen\" /></a>";
					// Dateien direkt hochladen geht nur bei passiven Aufgaben
					if ($usersGroupBasetype!=UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT)
					{
						if (!$groupMember && ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP || !$status->IsActiveProzess($_SESSION["currentUser"], $db)))
						{
							$returnValue.="<a href='javascript: UploadFiles(\"".WorkflowManager::GetProcessStatusId($status)."\");'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/uploadFile.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/uploadFile_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/uploadFile.png'\" alt='Datei hochladen' title='Datei hochladen'></a>";
						}
					}
				}
				if ($returnValue=="") $returnValue = "&#160;";
				break;
		}
		$dynamicTableRow->AddColumnData($columnId, $returnValue);
	}
	
	/**
	 * Load Data from DB depending on the active filters
	 */
	protected function LoadDataWithFilterOptions()
	{
		// get active filter configuration
		$information = $this->GetDynamicTableInformation();
		// main serach...
		$searchString = $information->GetSearch();
		// filter where clause...
		$additionalWhereClause = $this->GetWhereClause($this->db);
		// order by...
		$orderByColumn = $this->GetOrderByColumn();
		$orderByColumnName = "pkey";
		$orderByDirection = 0;
		if ($orderByColumn!=null)
		{
			if ($orderByColumn->GetSortdirection()!=DynamicTableColumn::SORT_NONE)
			{
				$orderByColumnName = $orderByColumn->GetTableSortRowName();
				$orderByDirection = ($orderByColumn->GetSortdirection()==DynamicTableColumn::SORT_ASC ? 0 : 1);
			}
		}
		
		// Set Rows
		$this->dynamicTableRows = Array();
		
		if ($this->groupIdToRetriveTasks!=-1)
		{
			// Retrive Group Members only
			$objects = $this->manager->GetWorkflowStatus('', $orderByColumnName, $orderByDirection, 0, 0, "processStatusGroup_rel=".(int)$this->groupIdToRetriveTasks, false, true);
		}
		else
		{
			// Retrive Groups and non grouped Tasks
			$objects = $this->manager->GetWorkflowStatus($searchString, $orderByColumnName, $orderByDirection, $information->GetCurrentPage(), $information->GetEntriesPerPage(), $additionalWhereClause, true, true);
		}
		
		
		for ($a=0; $a<count($objects); $a++)
		{
			$dtrow = new DynamicTableRow();
			if ($objects[$a]->GetProcessStatusGroupId()!=0) $dtrow->SetBelongToGroupId($objects[$a]->GetProcessStatusGroupId());
			

			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
			{
				$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_PROGRESS, $dtrow);
			}
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_TYPE, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_STATUS, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_COUNTRY, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_LOCATION, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_SHOP, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_YEAR, $dtrow);
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
			{
				$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_GROUP_CUSTOMER, $dtrow);
			}
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_GROUP_TRUSTEE, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_FMS_LOCATION, $dtrow);
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
			{
				$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_FMS_TASK, $dtrow);
			}
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_CUSTOMER, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_INFO, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_OPTIONS, $dtrow);
			
			$this->dynamicTableRows[] = $dtrow;
		}
	}
	
	/**
	 * Function to set the table parameters
	 * @param  Array  $tableParams  The post/get parameters sent by the JSON Request
	 */
	public function SetTableParams($tableParams)
	{
		// only retrive Tasks of a specific Group
		if (isset($tableParams["groupID"]))
		{
			$this->groupIdToRetriveTasks = (int)$tableParams["groupID"];
			
		}
		parent::SetTableParams($tableParams);
	}
	
	/**
	 * Function to create the JSON Answer
	 * @return  Array  The Answer as Array
	 */
	public function GetJSONAnswer()
	{
		$returnValue = parent::GetJSONAnswer();
		// Get the "request_information" part
		if ($this->groupIdToRetriveTasks!=-1)
		{
			$returnValue["request_information"]["reload_table"] = false;
			$returnValue["groupID"] = $this->groupIdToRetriveTasks;
			$this->groupIdToRetriveTasks = -1;
		}
		return $returnValue;
	}
	
}


// Filter-Query-Class for column Active
class ActiveQueryLight extends DynamicTableQuery
{

	/**
	 * Return the where clause for this filter
	 * @param DBManager $db
	 * @param DynamicTableColumn $column
	 * @return string
	 */
	public function GetWhereClause(DBManager $db, DynamicTableColumn $column)
	{
		$showAktiveTasks = $this->filter->IsCheckboxChecked(0); // Aktive Aufgabe
		$showPassiveTasks = $this->filter->IsCheckboxChecked(1); // Passive Aufgaben
		if (!$showAktiveTasks && !$showPassiveTasks)
		{
			// this filter combination have to return an empty result
			return "true=false";
		}
		else if ($showAktiveTasks && $showPassiveTasks)
		{
			// this filter combination does not have any effect to the result
			return "";
		}
		// Active Task query
		return WorkflowManager::BuildStatusResponsibleQuery($db, $showAktiveTasks);
	}

}