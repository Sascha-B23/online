<?php

/**
 * ListData-Implementierung für Aufgaben
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class TasksListData extends DynamicTable
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
        const COLUMN_OPTIONS = 18;
		const COLUMN_PLANNED = 19;
		const COLUMN_SUM_OPEN = 20;
		const COLUMN_PROCESS_GROUP = 21;
		const COLUMN_CREATION_DATE = 22;
		const COLUMN_SUMTAPLTABR = 23;
		const COLUMN_FMS_LEADER = 24;

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
	public function TasksListData(DBManager $db)
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
		
		// Prio OK
		$column = new DynamicTableColumn(self::COLUMN_PRIO, "P", "prio", DynamicTableColumn::TYPE_INT);
		// add filters to column
		$filter = new DynamicTableFilterCheckbox(3);
		$filter->AddCheckbox(10, "Ja", true);
		$filter->AddCheckbox(0, "Nein", true);
		$column->AddFilter($filter);
		//$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TEXTSEARCH, 4));
		//$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TEXTSEARCH, 5));
		// add column
		$columns[] = $column;

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
		
			// Geplant/Ungeplant OK
			$column = new DynamicTableColumn(self::COLUMN_PLANNED, Schedule::GetAttributeName($lm, 'planned'), "planned", DynamicTableColumn::TYPE_INT);
			// add filters to column
			$filter = new DynamicTableFilterCheckbox(3);
			$filter->AddCheckbox(0, "Nein", true);
			$filter->AddCheckbox(1, "Ja", true);
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
		//if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			for($a=0; $a<count($processStatusConfig); $a++)
			{
				$filter->AddCheckbox($processStatusConfig[$a]["ID"], $processStatusConfig[$a][$usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP ? "name" : "nameCustomer"], in_array($processStatusConfig[$a]["ID"], $excludeFromStandardSelection) ? false : true);
			}
		}
		/*else
		{
			for($a=0; $a<count($processStatusConfig); $a++)
			{
				if (!WorkflowManager::UserGroupResponsibleForProzess($processStatusConfig[$a]["ID"], UM_GROUP_BASETYPE_KUNDE)) continue;
				$filter->AddCheckbox($processStatusConfig[$a]["ID"], $processStatusConfig[$a][$usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP ? "name" : "nameCustomer"], in_array($processStatusConfig[$a]["ID"], $excludeFromStandardSelection) ? false : true);
			}
			$filter->AddCheckbox('OTHERS', "Andere", true);
			$filter->SetQuery(new CustomerStatusQuery($filter));
		}*/
		
		
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
		
		// Interne Nr Kunde OK
		$column = new DynamicTableColumn(self::COLUMN_CUSTOMERID, CShop::GetAttributeName($lm, 'internalShopNo'), "internalShopNo", DynamicTableColumn::TYPE_STRING, $usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP ? false : true);
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
		
		// Fällig am OK
		$column = new DynamicTableColumn(self::COLUMN_DEADLINE, WorkflowStatus::GetAttributeName($lm, 'deadline'), "deadline", DynamicTableColumn::TYPE_DATE);
		// add filters to column
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_FROM, 3));
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TO, 4));
		// add column
		$columns[] = $column;

		// Bearbeitungszeit OK
		$column = new DynamicTableColumn(self::COLUMN_DURATION, "Bearb.-Zeit", "duration", DynamicTableColumn::TYPE_INT);
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
		$column = new DynamicTableColumn(self::COLUMN_SUM_OPEN, "WS-Summe offen", "wsSummeOffen", DynamicTableColumn::TYPE_FLOAT);
		$column->SetCssStyle("text-align", "right");
		// add filters to column
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_FROM, 3));
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TO, 4));
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
				$filter->AddCheckbox($user->GetPKey(), $user->GetUserName()." (".$user->GetShortName().")", ($_SESSION["currentUser"]->GetPKey()==$user->GetPKey() || $usersGroupBasetype==UM_GROUP_BASETYPE_SUPERUSER || $usersGroupBasetype==UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT || $usersGroupBasetype==UM_GROUP_BASETYPE_AUSHILFE ? true : false));
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

		if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			// FMS Nebenkostenanalyst
			$column = new DynamicTableColumn(self::COLUMN_FMS_LEADER, "SFM-AP NK&#x2011;Analyst", "cPersonFmsLeader", DynamicTableColumn::TYPE_INT, false);
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
		
		// Prozessgruppe OK
		$column = new DynamicTableColumn(self::COLUMN_PROCESS_GROUP, ProcessStatusGroup::GetAttributeName($lm, 'name'), "processStatusGroupName", DynamicTableColumn::TYPE_STRING, false);
		// add filters to column
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TEXTSEARCH, 3));
		// add column
		$columns[] = $column;
		
		// Beauftragungsdatum
		$column = new DynamicTableColumn(self::COLUMN_CREATION_DATE, Teilabrechnung::GetAttributeName($lm, 'auftragsdatumAbrechnung'), "auftragsdatumAbrechnung", DynamicTableColumn::TYPE_DATE, false);
		// add filters to column
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_FROM, 3));
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TO, 4));
		// add column
		$columns[] = $column;
		
		// Nebenkosten lt. Abr.
		$column = new DynamicTableColumn(self::COLUMN_SUMTAPLTABR, Teilabrechnung::GetAttributeName($lm, 'summeBetragKundeTAPs'), "betragKunde", DynamicTableColumn::TYPE_FLOAT, false);
		$column->SetCssStyle("text-align", "right");
		// add filters to column
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_FROM, 3));
		$column->AddFilter(new DynamicTableFilterText(DynamicTableFilter::TYPE_FILTER_TO, 4));
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
		return $this->manager->GetWorkflowStatusCount($searchString, $additionalWhereClause, true);
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
			case self::COLUMN_PRIO:
				$reason = "";
				if($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP) $reason = $status->GetPrioReason($this->db);
				$returnValue = ($status->GetPrio($this->db)===Schedule::PRIO_HIGH ? "<span class='errorText2' style='font-size: 15px; font-weight: bold; ".($reason!="" ? "cursor: help;" : "")."' ".($reason!="" ? "title='".$reason."' alt='".$reason."'" : "").">!</span>" : "&#160;");
				break;
			case self::COLUMN_PROGRESS:
				$returnValue = $status->GetArchiveStatusName();
				break;
			case self::COLUMN_PLANNED:
				$returnValue = ($status->IsPlanned() ? "Ja" : "Nein");
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
			case self::COLUMN_CUSTOMERID:
				$returnValue = $status->GetInternalShopNo();
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
				$user = $status->GetResponsibleRSUser();
				if ($user!=null) $returnValue = "<span title='".$user->GetUserName()."' alt='".$user->GetUserName()."'>".$user->GetShortName()."<span>";
				break;
			case self::COLUMN_FMS_TASK:
				$user = $status->GetCurrentResponsibleRSUser($db);
				if ($user!=null) $returnValue = "<span title='".$user->GetUserName()."' alt='".$user->GetUserName()."'>".$user->GetShortName()."<span>";
				break;
			case self::COLUMN_FMS_LEADER:
				$user = $status->GetCPersonFmsLeader();
				if ($user!=null) $returnValue = "<span title='".$user->GetUserName()."' alt='".$user->GetUserName()."'>".$user->GetShortName()."<span>";
				break;
			case self::COLUMN_CUSTOMER:
				$user = $status->GetResponsibleCustomer();
				if ($user!=null) $returnValue = "<span title='".$user->GetUserName()."' alt='".$user->GetUserName()."'>".$user->GetShortName()."<span>";
				break;
			case self::COLUMN_PROCESS_GROUP:
				if (is_a($status, "ProcessStatusGroup"))
				{
					$processGroup = $status;
				}
				else
				{
					$processGroup = $status->GetProcessStatusGroup($db);
				}
				if ($processGroup!=null) $returnValue = $processGroup->GetName();
				break;
			case self::COLUMN_CREATION_DATE:
				$date = $status->GetBeauftragungsdatum($db);
				if ($date!=0) $returnValue = date("d.m.Y", $date);
				break;
			case self::COLUMN_SUMTAPLTABR:
				$summe = $status->GetSummeBetragKunde($db);
				if ($summe!==false) $returnValue = $status->GetCurrency()." ".str_replace(",00", "", HelperLib::ConvertFloatToLocalizedString(round($summe)));
				break;
			case self::COLUMN_INFO:
				// Ist die Aufgabe von diesem Benutzer oder von einem anderen (z.B. Urlaubsvertretung)?
				$returnValue = "";
				if( $usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP )
				{
					$origUser = $status->GetResponsibleRSUser();
				}
				else
				{
					$origUser = $status->GetResponsibleCustomer();
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

				if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
				{
					/*// Warnung ausgeben, wenn im Status "Widerspruch durch Kunde freigeben" die Deadline überschritten wurde
					if ($status->GetCurrentStatus()==20 && $status->GetDeadline()<time())
					{
						$returnValue.="<img src='".$SHARED_HTTP_ROOT."pics/gui/warning.png' width='25' height='25' alt='Es sind bereits mehr als drei Tage verstrichen, seit die Freigabe beim Kunden angefordert wurde' title='Es sind bereits mehr als drei Tage verstrichen, seit die Freigabe beim Kunden angefordert wurde' style='cursor: help;' />";
					}*/
					// Warnung ausgeben, wenn die Deadline überschritten wurde
					if ($status->GetDeadline()<time() && ($status->GetCurrentStatus()==9 || $status->GetCurrentStatus()==20 || $status->GetCurrentStatus()==21))
					{
						$returnValue.="<img src='".$SHARED_HTTP_ROOT."pics/gui/warning.png' width='25' height='25' alt='Das Fälligkeitsdatum wurde überschritten' title='Das Fälligkeitsdatum wurde überschritten' style='cursor: help;' />";
					}
					// Warnung ausgeben, wenn das Abschlussdatum überschritten wurde
					if ($status->GetAbschlussdatum()<time() && $status->GetCurrentStatus()!=26 && $status->GetCurrentStatus()!=7)
					{
						$returnValue.="<img src='".$SHARED_HTTP_ROOT."pics/gui/warning_abschlusstermin.png' width='25' height='25' alt='Das Abschlussdatum ".date("d.m.Y", $status->GetAbschlussdatum())." wurde überschritten' title='Das Abschlussdatum ".date("d.m.Y", $status->GetAbschlussdatum())." wurde überschritten' style='cursor: help;' />";
					}
					// Ist diese Aufgaben eine aktive Aufgabe des angemeldenten Benutzers...
					if ($status->IsActiveProzess($_SESSION["currentUser"], $db))
					{
						// ... und wurde diese innerhalb der letzten 36 Stunden noch nicht geplant?
						if (!$status->IsPlanned() && time()-$status->GetPlannedTime()>60*60*36 )
						{
							$returnValue.="<img src='".$SHARED_HTTP_ROOT."pics/gui/warning.png' width='25' height='25' alt='Sie haben sich diese Aufgabe noch nicht eingeplant' title='Sie haben sich diese Aufgabe noch nicht eingeplant' style='cursor: help;' />";
						}
					}
					
					if ($usersGroupBasetype!=UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT)
					{
						// Telefontermine
						if ($status->GetTelefontermin()!=0)
						{
							// Telefontermie können nur FMS-Mitarbeiter sehen
							$img="call_a.png";
							if ($status->GetTelefontermin()-time()<60*60*24*3) $img="call_b.png";
							if ($status->GetTelefontermin()-time()<60*60*24) $img="call_c.png";
							$returnValue.="<a href='javascript:EditDate(\"".WorkflowManager::GetProcessStatusId($status)."\");' title='Telefontermin am ".date("d.m.Y", $status->GetTelefontermin())." um ".date("H:i",$status->GetTelefontermin())." Uhr' alt='Telefontermin am ".date("d.m.Y", $status->GetTelefontermin())." um ".date("H:i",$status->GetTelefontermin())." Uhr'><img src='".$SHARED_HTTP_ROOT."pics/gui/".$img."' /></a>";
						}
						// Files uploaded?
						$numNewCustomerFiles = $status->GetFileCount($db, FM_FILE_SEMANTIC_NEWCUSTOMERFILE);
						if ($numNewCustomerFiles>0)
						{
							$returnValue.="<a href='javascript:EditUploadedFiles(\"".WorkflowManager::GetProcessStatusId($status)."\");' alt='Unklassifizierte Datei(en) vorhanden (".$numNewCustomerFiles." Stück)' title='Unklassifizierte Datei(en) vorhanden (".$numNewCustomerFiles." Stück)'><img src='".$SHARED_HTTP_ROOT."pics/gui/filesuploaded.png' width='25' height='25' alt='Unklassifizierte Datei(en) vorhanden (".$numNewCustomerFiles." Stück)' title='Unklassifizierte Datei(en) vorhanden (".$numNewCustomerFiles." Stück)' /></a>";
						}
					}
				}
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
						// Telefontermie können nur FMS-Mitarbeiter bearbeiten
						if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP && $usersGroupBasetype!=UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT)
						{
							$returnValue.="<a href='javascript: EditDate(\"".WorkflowManager::GetProcessStatusId($status)."\");'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/call_b.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/call_b_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/call_b.png'\" alt='Telefontermin bearbeiten' title='Telefontermin bearbeiten'></a>";
						}
					}
					
					if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
					{
						// Aufgaben planen können nur FMS-Mitarbeiter und auch nur dann, wenn es sich um ihre aktive Aufgaben handelt
						if ($status->IsActiveProzess($_SESSION["currentUser"], $db))
						{
							if (!$status->IsPlanned())
							{
								$returnValue.="<a href='meineaufgaben.php5?".SID."&planElements=".WorkflowManager::GetProcessStatusId($status)."'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/planTask.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/planTask_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/planTask.png'\"  alt=\"Aufgabe als 'geplant' markieren\" title=\"Aufgabe als 'geplant' markieren\" /></a>";
							}
							if ($status->IsPlanned())
							{
								$returnValue.="<a href='meineaufgaben.php5?".SID."&planElements=".WorkflowManager::GetProcessStatusId($status)."'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/planTask.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/planTask_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/planTask.png'\"  alt=\"Aufgabe als 'ungeplant' markieren\" title=\"Aufgabe als 'ungeplant' markieren\" /></a>";
							}
						}
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
						// Telefontermie können nur FMS-Mitarbeiter bearbeiten
						if (!$groupMember && $usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP && $usersGroupBasetype!=UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE && $usersGroupBasetype!=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT)
						{
							$returnValue.="<a href='javascript: EditDate(\"".WorkflowManager::GetProcessStatusId($status)."\");'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/call_b.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/call_b_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/call_b.png'\" alt='Telefontermin bearbeiten' title='Telefontermin bearbeiten'></a>";
						}
					}

					if ($usersGroupBasetype>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
					{
						// Aufgaben planen können nur FMS-Mitarbeiter und auch nur dann, wenn es sich um ihre aktive Aufgaben handelt
						if (!$groupMember && $status->IsActiveProzess($_SESSION["currentUser"], $db))
						{
							if (!$status->IsPlanned())
							{
								$returnValue.="<a href='meineaufgaben.php5?".SID."&planElements=".WorkflowManager::GetProcessStatusId($status)."'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/planTask.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/planTask_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/planTask.png'\"  alt=\"Aufgabe als 'geplant' markieren\" title=\"Aufgabe als 'geplant' markieren\" /></a>";
							}
							if ($status->IsPlanned())
							{
								$returnValue.="<a href='meineaufgaben.php5?".SID."&planElements=".WorkflowManager::GetProcessStatusId($status)."'><img src=\"".$SHARED_HTTP_ROOT."pics/gui/planTask.png\" onmouseover=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/planTask_over.png'\" onmouseout=\"this.src='".$SHARED_HTTP_ROOT."pics/gui/planTask.png'\"  alt=\"Aufgabe als 'ungeplant' markieren\" title=\"Aufgabe als 'ungeplant' markieren\" /></a>";
							}
						}
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
			$objects = $this->manager->GetWorkflowStatus('', $orderByColumnName, $orderByDirection, 0, 0, "processStatusGroup_rel=".(int)$this->groupIdToRetriveTasks, false);
		}
		else
		{
			// Retrive Groups and non grouped Tasks
			$objects = $this->manager->GetWorkflowStatus($searchString, $orderByColumnName, $orderByDirection, $information->GetCurrentPage(), $information->GetEntriesPerPage(), $additionalWhereClause, true);
		}
		
		
		for ($a=0; $a<count($objects); $a++)
		{
			$dtrow = new DynamicTableRow();
			if ($objects[$a]->GetProcessStatusGroupId()!=0) $dtrow->SetBelongToGroupId($objects[$a]->GetProcessStatusGroupId());
			
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_PRIO, $dtrow);
			
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
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
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
			{
				$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_GROUP_CUSTOMER, $dtrow);
			}
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_GROUP_TRUSTEE, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_DEADLINE, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_DURATION, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_SUM, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_SUM_OPEN, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_FMS_LOCATION, $dtrow);
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
			{
				$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_FMS_TASK, $dtrow);
			}
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
			{
				$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_FMS_LEADER, $dtrow);
			}
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_CUSTOMER, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_PROCESS_GROUP, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_CREATION_DATE, $dtrow);
			$this->GetCellDataContent($this->db, $objects[$a], self::COLUMN_SUMTAPLTABR, $dtrow);
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
	
} // TasksListData

// Filter-Query-Class for column Active
class ActiveQuery extends DynamicTableQuery
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

// Filter-Query-Class for customers of column status 
class CustomerStatusQuery extends DynamicTableQuery
{
	
	/**
	 * Return the where clause for this filter
	 * @param DBManager $db
	 * @param DynamicTableColumn $column
	 * @return string
	 */
	public function GetWhereClause(DBManager $db, DynamicTableColumn $column)
	{
		$returnValue = "";
		$rowName = $column->GetTableRowName();
		if ($rowName=="") return "";
		$rowType = $column->GetType();
		// is checkbox 'others' checked?
		$showOthers = $this->filter->IsCheckboxChecked('OTHERS');
		// loop throug all status...
		$filtersDisabled = Array();
		global $processStatusConfig;
		$excludeFromStandardSelection = Array(7, 26);
		for ($a=0; $a<count($processStatusConfig); $a++)
		{
			// check if status is a customer status
			if (!WorkflowManager::UserGroupResponsibleForProzess($processStatusConfig[$a]["ID"], UM_GROUP_BASETYPE_KUNDE))
			{
				// if it is no costomer status it is a 'other'-status and have to be hidden if the checkbox 'other' is not checked
				if (!$showOthers) $filtersDisabled[] = $processStatusConfig[$a]["ID"];
				continue;
			}
			if (!$this->filter->IsCheckboxChecked($processStatusConfig[$a]["ID"]))
			{
				$filtersDisabled[] = $processStatusConfig[$a]["ID"];
			}
		}
		if (count($filtersDisabled)==0) return "";
		// build where clause
		foreach ($filtersDisabled as $checkbox)
		{
			if ($returnValue!="") $returnValue.=" AND ";
			$returnValue.=$rowName."!=".(int)$checkbox." ";
		}
		return $returnValue;
	}
	
}

// Filter-Query-Class for column duration 
class DurationQuery extends DynamicTableQuery
{
	
	/**
	 * Return the where clause for this filter
	 * @param DBManager $db
	 * @param DynamicTableColumn $column
	 * @return string
	 */
	public function GetWhereClause(DBManager $db, DynamicTableColumn $column)
	{
		$returnValue = "";
		$rowName = $column->GetTableRowName();
		if ($rowName=="") return "";
		$rowType = $column->GetType();
		
		if (trim($this->filter->GetValue())!='')
		{
			if ($this->filter->GetFilterType()==DynamicTableFilter::TYPE_FILTER_FROM || $this->filter->GetFilterType()==DynamicTableFilter::TYPE_FILTER_TO)
			{
				$returnValue = $rowName;
				if ($this->filter->GetFilterType()==DynamicTableFilter::TYPE_FILTER_FROM) $returnValue.=">=";
				if ($this->filter->GetFilterType()==DynamicTableFilter::TYPE_FILTER_TO) $returnValue.="<=";
				
				$returnValue.=((int)$this->filter->GetValue()*60*60*24);

			}
		}
		return $returnValue;
	}
	
}

?>