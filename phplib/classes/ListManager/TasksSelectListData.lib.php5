<?php

/**
 * ListData-Implementierung für Aufgabenauswahl
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class TasksSelectListData extends DynamicTable
{
        const COLUMN_PRIO = 0;
        const COLUMN_PROGRESS = 1;
        const COLUMN_TYPE = 2;
        const COLUMN_STATUS = 3;
        const COLUMN_COUNTRY = 4;
        const COLUMN_LOCATION = 5;
        const COLUMN_SHOP = 6;
        const COLUMN_CUSTOMERID = 7;
        const COLUMN_YEAR = 8;
        const COLUMN_GROUP_CUSTOMER = 9;
        const COLUMN_GROUP_TRUSTEE = 10;
        const COLUMN_DEADLINE = 11;
        const COLUMN_DURATION = 12;
        const COLUMN_SUM = 13;
        const COLUMN_FMS_LOCATION = 14;
        const COLUMN_FMS_TASK = 15;
        const COLUMN_CUSTOMER = 16;
        const COLUMN_INFO = 17;
		
		const COLUMN_PLANNED = 19;
		const COLUMN_SUM_OPEN = 20;
		const COLUMN_SELECTBOX = 21;

		const COLUMN_FMS_LEADER = 24;
    
	/**
	 * WorkflowManager-Objekt
	 * @var WorkflowManager
	 */
	protected $manager = null;
	
	/**
	 *
	 * @var ProcessStatusGroup 
	 */
	protected $processStatusGroupToEdit = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ProcessStatus $checkedProcesses
	 */
	public function TasksSelectListData(DBManager $db, ProcessStatusGroup $processStatusGroupToEdit=null)
	{
		$this->useSession = true;
		$this->processStatusGroupToEdit = $processStatusGroupToEdit;
		global $workflowManager;
		$this->manager = $workflowManager;
		
		if ($this->GetDynamicTableInformation() == null)
		{
			global $SHARED_HTTP_ROOT;
			$information = new DynamicTableInformation();
			$information->SetIcon($SHARED_HTTP_ROOT."pics/gui/activeTask.png");
			$information->SetHeadline("Aufgaben");
			$information->SetOnLoadCallbackFunction("on_table_loaded();");
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
	 * Set the process status group to be edit
	 * @param ProcessStatusGroup $processStatusGroupToEdit
	 */
	public function SetProcessStatusGroupToEdit(ProcessStatusGroup $processStatusGroupToEdit=null)
	{
		$this->processStatusGroupToEdit = $processStatusGroupToEdit;
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
		
		$columns = Array();
		
		// Selectbox
		$column = new DynamicTableColumn(self::COLUMN_SELECTBOX, "", "select", DynamicTableColumn::TYPE_NONE);
		$columns[] = $column;
		
		// Prio OK
		$column = new DynamicTableColumn(self::COLUMN_PRIO, "P", "prio", DynamicTableColumn::TYPE_INT, false);
		// add filters to column
		$filter = new DynamicTableFilterCheckbox(3);
		$filter->AddCheckbox(10, "Ja", true);
		$filter->AddCheckbox(0, "Nein", true);
		$column->AddFilter($filter);
		//$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TEXTSEARCH, 4));
		//$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TEXTSEARCH, 5));
		// add column
		$columns[] = $column;

		if ($_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE)
		{
			// Bearbeitungsstatus  OK
			$column = new DynamicTableColumn(self::COLUMN_PROGRESS, Schedule::GetAttributeName($lm, 'archiveStatus'), "archiveStatus", DynamicTableColumn::TYPE_INT, false);
			// add filters to column
			$filter = new DynamicTableFilterCheckbox(3);
			$filter->AddCheckbox(0, "archiviert", false);
			$filter->AddCheckbox(1, "auf Stand zu bringen", true);
			$filter->AddCheckbox(2, "bereits auf Stand", true);
			$column->AddFilter($filter);
			// add column
			$columns[] = $column;
		
			// Geplant/Ungeplant OK
			$column = new DynamicTableColumn(self::COLUMN_PLANNED, Schedule::GetAttributeName($lm, 'planned'), "planned", DynamicTableColumn::TYPE_INT, false);
			// add filters to column
			$filter = new DynamicTableFilterCheckbox(3);
			$filter->AddCheckbox(0, "Nein", true);
			$filter->AddCheckbox(1, "Ja", true);
			$column->AddFilter($filter);
			// add column
			$columns[] = $column;
		}
		// Aktiv OK
		$column = new DynamicTableColumn(self::COLUMN_TYPE, "Aktiv", "active", DynamicTableColumn::TYPE_INT, false);
		// add filters to column
		$filter = new DynamicTableFilterCheckbox(3);
		$filter->AddCheckbox(0, "Aktiv", true);
		$filter->AddCheckbox(1, "Passiv", true);
		$filter->SetQuery(new ActiveQuery($filter));
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
			$filter->AddCheckbox($processStatusConfig[$a]["ID"], $processStatusConfig[$a][$_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE ? "name" : "nameCustomer"], in_array($processStatusConfig[$a]["ID"], $excludeFromStandardSelection) ? false : true);
		}
		$column->AddFilter($filter);
		// add column
		$columns[] = $column;

		// Land OK
		$column = new DynamicTableColumn(self::COLUMN_COUNTRY, CLocation::GetAttributeName($lm, 'country'), "country", DynamicTableColumn::TYPE_STRING, false);
		// add filters to column
		$filter = new DynamicTableFilterCheckbox(3);
		$countries = $customerManager->GetCountries();
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
		
		// Interne Nr Kunde OK
		$column = new DynamicTableColumn(self::COLUMN_CUSTOMERID,  CShop::GetAttributeName($lm, 'internalShopNo'), "internalShopNo", DynamicTableColumn::TYPE_STRING, false);
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
		
		if ($_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE)
		{
			// Gruppe Kunde OK
			$column = new DynamicTableColumn(self::COLUMN_GROUP_CUSTOMER, CGroup::GetAttributeName($lm, 'name'), "groupId", DynamicTableColumn::TYPE_INT, true, true, "groupName");
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
		
		// Fällig am OK
		$column = new DynamicTableColumn(self::COLUMN_DEADLINE, WorkflowStatus::GetAttributeName($lm, 'deadline'), "deadline", DynamicTableColumn::TYPE_DATE, false);
		// add filters to column
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_FROM, 3));
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TO, 4));
		// add column
		$columns[] = $column;

		// Bearbeitungszeit OK
		$column = new DynamicTableColumn(self::COLUMN_DURATION, "Bearb.-Zeit", "duration", DynamicTableColumn::TYPE_INT, false);
		$column->SetCssStyle("text-align", "center");
		// add filters to column
		$filter = new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_FROM, 3);
		$filter->SetQuery(new DurationQuery($filter));
		$column->AddFilter($filter);
		$filter = new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TO, 4);
		$filter->SetQuery(new DurationQuery($filter));
		$column->AddFilter($filter);
		// add column
		$columns[] = $column;

		// WS-Summe OK
		$column = new DynamicTableColumn(self::COLUMN_SUM, "WS-Summe gesamt", "wsSumme", DynamicTableColumn::TYPE_FLOAT, false);
		$column->SetCssStyle("text-align", "right");
		// add filters to column
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_FROM, 3));
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TO, 4));
		// add column
		$columns[] = $column;
		
		// WS-Summe OK
		$column = new DynamicTableColumn(self::COLUMN_SUM_OPEN, "WS-Summe offen", "wsSummeOffen", DynamicTableColumn::TYPE_FLOAT, false);
		$column->SetCssStyle("text-align", "right");
		// add filters to column
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_FROM, 3));
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TO, 4));
		// add column
		$columns[] = $column;

		// FMS-AP Standort OK
		$column = new DynamicTableColumn(self::COLUMN_FMS_LOCATION, "SFM-AP Standort", "cPersonRS", DynamicTableColumn::TYPE_INT, false);
		if ($_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE)
		{
			// add filters to column
			$filter = new DynamicTableFilterCheckbox(3);
			$users = $userManager->GetUsers($_SESSION["currentUser"], "", "email", 0, 0, 0, UM_GROUP_BASETYPE_RSMITARBEITER);
			foreach ($users as $user)
			{
				$filter->AddCheckbox($user->GetPKey(), $user->GetUserName()." (".$user->GetShortName().")", ($_SESSION["currentUser"]->GetPKey()==$user->GetPKey() || $_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT ? true : false));
			}
			$column->AddFilter($filter);
		}
		// add column
		$columns[] = $column;
		
		if ($_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE)
		{
			// FMS-AP Prozess OK
			$column = new DynamicTableColumn(self::COLUMN_FMS_TASK, "SFM-AP Prozess", "currentResponsibleRSUser", DynamicTableColumn::TYPE_INT, false);
			// add filters to column
			$filter = new DynamicTableFilterCheckbox(3);
			foreach ($users as $user)
			{
				$filter->AddCheckbox($user->GetPKey(), $user->GetUserName()." (".$user->GetShortName().")", true);
			}
			$column->AddFilter($filter);
			// add column
			$columns[] = $column;
		}

		if ($_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE)
		{
			// FMS Nebenkostenanalyst
			$column = new DynamicTableColumn(self::COLUMN_FMS_LEADER, "SFM-AP NK-Analyst", "cPersonFmsLeader", DynamicTableColumn::TYPE_INT);
			// add filters to column
			$filter = new DynamicTableFilterCheckbox(3);
			foreach ($users as $user)
			{
				$filter->AddCheckbox($user->GetPKey(), $user->GetUserName()." (".$user->GetShortName().")", true);
			}
			$column->AddFilter($filter);
			// add column
			$columns[] = $column;
		}
		
		// Kunde-AP OK
		$column = new DynamicTableColumn(self::COLUMN_CUSTOMER, "Kunde-AP", "cPersonCustomer", DynamicTableColumn::TYPE_INT, false);
		// add filters to column
		$filter = new DynamicTableFilterCheckbox(3);
		$users = $userManager->GetUsers($_SESSION["currentUser"], "", "email", 0, 0, 0, UM_GROUP_BASETYPE_KUNDE);
		foreach ($users as $user)
		{
			if ($user->GetGroupBasetype($db)>$_SESSION["currentUser"]->GetGroupBasetype($db)) continue;
			$filter->AddCheckbox($user->GetPKey(), $user->GetUserName()." (".$user->GetShortName().")", $_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE || $_SESSION["currentUser"]->GetPKey()==$user->GetPKey() ? true : false);
		}
		$column->AddFilter($filter);
		// add column
		$columns[] = $column;
		
		// Info
		$column = new DynamicTableColumn(self::COLUMN_INFO, "Info", "", DynamicTableColumn::TYPE_STRING, false, false);
		$column->SetCssStyle("width", "75px");
		$column->SetCssStyle("vertical-align", "top");
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
		return $this->manager->GetWorkflowStatusCount($searchString, $additionalWhereClause);
	}
	
	/**
	 * Return the contetn of the table cell for the specified column of the passed status 
	 * @param DBManager $db
	 * @param ProcessStatus $status
	 * @param int $columnId
	 * @param DynamicTableRow $dynamicTableRow
	 */
	private function GetCellDataContent(DBManager $db, ProcessStatus $status, $columnId, DynamicTableRow $dynamicTableRow)
	{
		global $SHARED_HTTP_ROOT;
		$returnValue = "-";
		switch($columnId)
		{
			case self::COLUMN_SELECTBOX:
				if ($this->processStatusGroupToEdit->GetPKey()==$status->GetProcessStatusGroupId())
				{
					// process is allready in this group
					$returnValue = "<input type='checkbox' id='cb_tasksSelectListData_".$status->GetPKey()."' name='cb_tasksSelectListData_".$status->GetPKey()."' checked=true disabled=true />";	
				}
				elseif ($status->GetProcessStatusGroupId()!=0)
				{
					// process is in a nother group
					$returnValue = "<input type='checkbox' id='cb_tasksSelectListData_".$status->GetPKey()."' name='cb_tasksSelectListData_".$status->GetPKey()."' onClick='select_prozess(this.checked, ".$status->GetPKey().");' class='formCeckboxHiglight' alt='Dieser Prozess ist bereits einer anderen Gruppe zugeordnet. Wenn Sie den Prozess auswählen, wird er automatisch aus der anderen Gruppe entfernt.' title='Dieser Prozess ist bereits einer anderen Gruppe zugeordnet. Wenn Sie den Prozess auswählen, wird er automatisch aus der anderen Gruppe entfernt.' />";	
				}
				else
				{
					// process is in no group
					$returnValue = "<input type='checkbox' id='cb_tasksSelectListData_".$status->GetPKey()."' name='cb_tasksSelectListData_".$status->GetPKey()."' onClick='select_prozess(this.checked, ".$status->GetPKey().");' />";	
				}
				break;
			case self::COLUMN_PRIO:
				$reason = $status->GetPrioReason($this->db);
				$returnValue = ($status->GetPrio($this->db)===Schedule::PRIO_HIGH ? "<span class='errorText2' style='font-size: 15px; font-weight: bold; ".($reason!="" ? "cursor: help;" : "")."' ".($reason!="" ? "title='".$reason."' alt='".$reason."'" : "").">!</span>" : "&#160;");
				break;
			case self::COLUMN_PROGRESS:
				$returnValue = $status->GetArchiveStatusName();
				break;
			case self::COLUMN_PLANNED:
				$returnValue = ($status->IsPlanned() ? "Ja" : "Nein");
				break;
			case self::COLUMN_TYPE:
				$groupBaseType = $_SESSION["currentUser"]->GetGroupBasetype($db);
				$returnValue = (WorkflowManager::UserGroupResponsibleForProzess($status->GetCurrentStatus(), ($groupBaseType>UM_GROUP_BASETYPE_RSMITARBEITER ? UM_GROUP_BASETYPE_RSMITARBEITER : $groupBaseType) ) ? "aktiv" : "passiv");
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
			case self::COLUMN_CUSTOMERID:
				$returnValue = $status->GetInternalShopNo();
				break;
			case self::COLUMN_YEAR:
				$year = $status->GetAbrechnungsJahr();
				if ($year!=null) $returnValue = $year->GetJahr();
				break;
			case self::COLUMN_GROUP_CUSTOMER:
				$cGroup = $status->GetGroup();
				if ($cGroup!=null) $returnValue = $cGroup->GetName();
				break;
			case self::COLUMN_GROUP_TRUSTEE:
				$returnValue = $status->GetGroupTrusteeName($db);
				break;
			case self::COLUMN_DEADLINE:
				$deadline = $status->GetDeadline();
				if ($deadline!=0) $returnValue = date("d.m.Y", $deadline);
				break;
			case self::COLUMN_DURATION:
				// Anzahl Tage berechnen, seit der Prozess gestartet wurde
				$numTage = floor( $status->GetDuration($db)/60/60/24 );
				// Farbe bestimmen (< 50 Tage: grün, >=50 Tage und  <65 Tage: gelb, >=65 Tage rot)
				$color="#008800";
				if( $numTage>=50 && $numTage<65 )$color="#dd8800";
				if( $numTage>=65 )$color="#dd0000";
				$returnValue = "<font color='".$color."'>".$numTage."</font>";
				break;
			case self::COLUMN_SUM:
				$wsSumme = $status->GetWiderspruchssumme($db);
				if ($wsSumme!==false) $returnValue = $status->GetCurrency()." ".str_replace(",00", "", HelperLib::ConvertFloatToLocalizedString(round($wsSumme)));
				break;
			case self::COLUMN_SUM_OPEN:
				$wsSumme = $status->GetWiderspruchssumme($db, 2);
				if ($wsSumme!==false) $returnValue = $status->GetCurrency()." ".str_replace(",00", "", HelperLib::ConvertFloatToLocalizedString(round($wsSumme)));
				break;
			case self::COLUMN_FMS_LOCATION:
				$cShop = $status->GetShop();
				if ($cShop!=null)
				{
					$user = $cShop->GetCPersonRS();
					if ($user!=null) $returnValue = "<span title='".$user->GetUserName()."' alt='".$user->GetUserName()."'>".$user->GetShortName()."<span>";
				}
				break;
			case self::COLUMN_FMS_TASK:
				$user = $status->GetCurrentResponsibleRSUser($db);
				if ($user!=null) $returnValue = "<span title='".$user->GetUserName()."' alt='".$user->GetUserName()."'>".$user->GetShortName()."<span>";
				break;
			case self::COLUMN_FMS_LEADER:
				$user = $status->GetCurrentResponsibleRSUser($db);
				if ($user!=null) $returnValue = "<span title='".$user->GetUserName()."' alt='".$user->GetUserName()."'>".$user->GetShortName()."<span>";
				break;
			case self::COLUMN_CUSTOMER:
				$cShop = $status->GetShop();
				if ($cShop!=null)
				{
					$user = $cShop->GetCPersonCustomer();
					if ($user!=null) $returnValue = "<span title='".$user->GetUserName()."' alt='".$user->GetUserName()."'>".$user->GetShortName()."<span>";
				}
				break;
			case self::COLUMN_INFO:
				// Ist die Aufgabe von diesem Benutzer oder von einem anderen (z.B. Urlaubsvertretung)?
				$returnValue = "";
				$cShop = $status->GetShop();
				if ($cShop!=null)
				{
					if( $_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE )
					{
						$origUser = $cShop->GetCPersonRS();
					}
					else
					{
						$origUser = $cShop->GetCPersonCustomer();
					}
					if ($status->GetZuweisungUser()!=null && $status->GetZuweisungUser()->GetPKey()==$_SESSION["currentUser"]->GetPKey())
					{
						// Aufgabe wurde zugewiesen
						$returnValue.="<img src='".$SHARED_HTTP_ROOT."pics/gui/assigned.png' alt='Zugewiesene Aufgabe von ".$origUser->GetUserName()."' title='Zugewiesene Aufgabe von ".$origUser->GetUserName()."' />";
					}
					else
					{
						if( $origUser!=null && $origUser->GetPKey()!=$_SESSION["currentUser"]->GetPKey() && $_SESSION["currentUser"]->GetPKey()==$origUser->GetCoverUser() )
						{
							// Urlaubsvertretung
							$returnValue.="<img src='".$SHARED_HTTP_ROOT."pics/gui/assigned_to_replacement.png' alt='Urlaubsvertretung von ".$origUser->GetUserName()."' title='Urlaubsvertretung von ".$origUser->GetUserName()."' />";
						}
					}
				}
				if ($_SESSION["currentUser"]->GetGroupBasetype($db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
				{
					// Warnung WS durch Kunden freigeben nach 3 Tagen (7 Tage - 4 Tage)
					if ($status->GetCurrentStatus()==20 && $status->GetDeadline()-60*60*24*4<time() )
					{
						$returnValue.="<img src='".$SHARED_HTTP_ROOT."pics/gui/warning.png' width='25' height='25' alt='Es sind bereits mehr als drei Tage verstrichen, seit die Freigabe beim Kunden angefordert wurde' title='Es sind bereits mehr als drei Tage verstrichen, seit die Freigabe beim Kunden angefordert wurde' style='cursor: help;' />";
					}
					
					// Telefontermine
					if ($status->GetTelefontermin()!=0)
					{
						// Telefontermie können nur FMS-Mitarbeiter sehen
						$img="call_a.png";
						if ($status->GetTelefontermin()-time()<60*60*24*3) $img="call_b.png";
						if ($status->GetTelefontermin()-time()<60*60*24) $img="call_c.png";
						$returnValue.="<a href='javascript:EditDate(".$status->GetPKey().");' title='Telefontermin am ".date("d.m.Y", $status->GetTelefontermin())." um ".date("H:i",$status->GetTelefontermin())." Uhr' alt='Telefontermin am ".date("d.m.Y", $status->GetTelefontermin())." um ".date("H:i",$status->GetTelefontermin())." Uhr'><img src='".$SHARED_HTTP_ROOT."pics/gui/".$img."' /></a>";
					}
					// Files uploaded?
					$numNewCustomerFiles = $status->GetFileCount($db, FM_FILE_SEMANTIC_NEWCUSTOMERFILE);
					if ($numNewCustomerFiles>0)
					{
						$returnValue.="<a href='javascript:EditUploadedFiles(".$status->GetPKey().");' alt='Unklassifizierte Datei(en) vorhanden (".$numNewCustomerFiles." Stück)' title='Unklassifizierte Datei(en) vorhanden (".$numNewCustomerFiles." Stück)'><img src='".$SHARED_HTTP_ROOT."pics/gui/filesuploaded.png' width='25' height='25' alt='Unklassifizierte Datei(en) vorhanden (".$numNewCustomerFiles." Stück)' title='Unklassifizierte Datei(en) vorhanden (".$numNewCustomerFiles." Stück)' /></a>";
					}
				}
				// Comment
				if (trim($status->GetScheduleComment())!="")
				{
					$returnValue.="<img src='".$SHARED_HTTP_ROOT."pics/gui/comment.png' width='25' height='25' style='cursor: help;' alt='".str_replace("'", "\"", trim($status->GetScheduleComment()))."' title='".str_replace("'", "\"", trim($status->GetScheduleComment()))."' />";
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
		$objects=$this->manager->GetWorkflowStatus($searchString, $orderByColumnName, $orderByDirection, $information->GetCurrentPage(), $information->GetEntriesPerPage(), $additionalWhereClause);
		for ($a=0; $a<count($objects); $a++)
		{
			$dtrow = new DynamicTableRow();
			
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_SELECTBOX, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_PRIO, $dtrow);
			
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
			{
				$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_PROGRESS, $dtrow);
				$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_PLANNED, $dtrow);
			}
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_TYPE, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_STATUS, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_COUNTRY, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_LOCATION, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_SHOP, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_CUSTOMERID, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_YEAR, $dtrow);
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
			{
				$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_GROUP_CUSTOMER, $dtrow);
			}
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_GROUP_TRUSTEE, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_DEADLINE, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_DURATION, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_SUM, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_SUM_OPEN, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_FMS_LOCATION, $dtrow);
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
			{
				$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_FMS_TASK, $dtrow);
			}
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
			{
				$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_FMS_LEADER, $dtrow);
			}
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_CUSTOMER, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_INFO, $dtrow);
			
			$this->dynamicTableRows[] = $dtrow;
		}
	}
	
}
?>