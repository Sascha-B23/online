<?php
require_once("WorkflowManager.inc.php5");
require_once("Schedule.lib.php5");
require_once("WorkflowStatus.lib.php5");
require_once("ProcessStatus.lib.php5");
require_once("ProcessStatusGroup.lib.php5");

define("WM_WORKFLOWSTATUS_TYPE_UNKNOWN", 0);
define("WM_WORKFLOWSTATUS_TYPE_PROCESS", 1);

/**
 * Verwaltungsklasse für den Workflow
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class WorkflowManager extends SearchDBEntry  
{
	/**
	 * process status types
	 */
	const PROCESS_TYPE_UNKNOWN = 0;
	const PROCESS_TYPE_PROCESSSTATUS = 1;
	const PROCESS_TYPE_PROCESSSTATUSGROUP = 2;
	
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
								  
	/**
	 * Constructor
	 * @param DBManager $db
	 */
	public function WorkflowManager($db, $checkIfViewsExist=false)
	{
		$this->db = $db;
		$this->abrechnungsJahrDummy = new AbrechnungsJahr($this->db);
		$this->contractDummy = new Contract($this->db);
		$this->shopDummy = new CShop($this->db);
		$this->locationDummy = new CLocation($this->db);
		$this->companyDummy = new CCompany($this->db);
		$this->groupDummy = new CGroup($this->db);
		$this->processStatusDummy = new ProcessStatus($this->db);
		$this->processStatusGroupDummy = new ProcessStatusGroup($this->db);
		$this->userDummy = new User($this->db);
		if ($checkIfViewsExist)
		{
			new Kuerzungsbetrag($this->db);
			new AddressData($this->db);
			$this->CreateWorkflowViews();
		}
	}
	
	/**
	 * Legt Table-Views für die Workflows an
	 * @return boolean Success 
	 */
	public function CreateWorkflowViews()
	{
		$currentResponsibleRSUserQuery = "(SELECT %select_column% FROM ".AddressData::TABLE_NAME;
		$currentResponsibleRSUserQuery.= "	LEFT JOIN ".User::TABLE_NAME." ON ".User::TABLE_NAME.".addressData=".AddressData::TABLE_NAME.".pkey";
		$currentResponsibleRSUserQuery.= "	WHERE ".User::TABLE_NAME.".pkey=(";
		$currentResponsibleRSUserQuery.= "		SELECT IF(coverUser<0, pkey, coverUser) FROM ".User::TABLE_NAME." WHERE pkey=(";
		$currentResponsibleRSUserQuery.= "			SELECT IF (";
		$currentResponsibleRSUserQuery.= "				".ProcessStatus::TABLE_NAME.".zuweisungUser<0, ";
		$currentResponsibleRSUserQuery.= "				(SELECT IF (";
		$currentResponsibleRSUserQuery.= "					".ProcessStatus::TABLE_NAME.".currentStatus IN (".implode(", ", self::GetAllStatusForGroup(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT))."), ";
		$currentResponsibleRSUserQuery.= "					".CShop::TABLE_NAME.".cPersonFmsAccountsdepartment, ";
		$currentResponsibleRSUserQuery.= "					".CShop::TABLE_NAME.".cPersonRS";
		$currentResponsibleRSUserQuery.= "				)),";
		$currentResponsibleRSUserQuery.= "				".ProcessStatus::TABLE_NAME.".zuweisungUser";
		$currentResponsibleRSUserQuery.= "			)";
		$currentResponsibleRSUserQuery.= "		)";
		$currentResponsibleRSUserQuery.= "	)";
		$currentResponsibleRSUserQuery.= ")";
		
		$currentResponsibleRSUser = str_replace('%select_column%', User::TABLE_NAME.".pkey", $currentResponsibleRSUserQuery)." AS currentResponsibleRSUser ";
		//$currentResponsibleRSUserShortName = str_replace('%select_column%', "shortName", $currentResponsibleRSUserQuery)." AS currentResponsibleRSUserShortName ";

		for ($a=0; $a<2; $a++)
		{
			for ($b=0; $b<2; $b++)
			{
				//$this->db->Query("DROP VIEW ".ProcessStatus::TABLE_NAME."_".($a==0 ? "rs" : "customer")."_view");
				$viewName = ProcessStatus::TABLE_NAME . "_" . ($a == 0 ? "rs" : "customer") . "_view". ($b == 0 ? "" : "_light");
				$data = $this->db->SelectAssoc("show tables like '".$viewName."'");
				if(count($data) == 0)
				{
					echo "CREATE VIEW '".$viewName."'<br />";
					$query = "CREATE OR REPLACE VIEW ".$viewName." AS ";
					$query .= "(";
					$query .= "SELECT ";
					$query .= ProcessStatus::TABLE_NAME . ".pkey, ";
					if ($b==0) $query .= ProcessStatus::TABLE_NAME . ".planned, ";
					if ($b==0) $query .= ProcessStatus::TABLE_NAME . ".deadline, ";
					$query .= ProcessStatus::TABLE_NAME . ".currentStatus, ";
					$query .= ProcessStatus::TABLE_NAME . ".archiveStatus, ";
					$query .= ProcessStatus::TABLE_NAME . ".customerMailSend, ";
					$query .= ProcessStatus::TABLE_NAME . ".zahlungsdatum, ";
					$query .= ProcessStatus::TABLE_NAME . ".processStatusGroup_rel, ";
					if ($b==0) $query .= ProcessStatusGroup::TABLE_NAME . ".name AS processStatusGroupName, ";
					$query .= CGroup::TABLE_NAME . ".pkey AS groupId, ";
					$query .= CGroup::TABLE_NAME . ".userGroup, ";
					$query .= CGroup::TABLE_NAME . ".name AS groupName, ";
					$query .= ProcessStatus::TABLE_NAME . ".zuweisungUser, ";
					$query .= CShop::TABLE_NAME . ".cPersonRS, ";
					if ($b==0) $query .= CShop::TABLE_NAME . ".cPersonFmsLeader, ";
					$query .= CShop::TABLE_NAME . ".cPersonCustomer, ";
					$query .= User::TABLE_NAME . ".coverUser, ";
					$query .= CLocation::TABLE_NAME . ".name AS locationName, ";
					$query .= CLocation::TABLE_NAME . ".street, ";
					$query .= CLocation::TABLE_NAME . ".zip, ";
					$query .= CLocation::TABLE_NAME . ".city, ";
					$query .= CLocation::TABLE_NAME . ".objectDescription, ";
					$query .= CLocation::TABLE_NAME . ".country, ";
					$query .= CShop::TABLE_NAME . ".name AS shopName, ";
					$query .= CShop::TABLE_NAME . ".RSID, ";
					if ($b==0) $query .= CShop::TABLE_NAME . ".internalShopNo, ";
					$query .= AbrechnungsJahr::TABLE_NAME . ".jahr, ";
					$query .= ProcessStatus::TABLE_NAME . ".creationTime, ";
					$query .= ProcessStatus::TABLE_NAME . ".telefontermin, ";
					if ($b==0) $query .= Teilabrechnung::TABLE_NAME . ".auftragsdatumAbrechnung, ";

					$query .= "(SELECT IF(";
					$query .= Teilabrechnung::TABLE_NAME . ".verwalterTyp=" . AddressBase::AM_CLASS_ADDRESSDATA . ",";
					$query .= "(SELECT " . AddressGroup::TABLE_NAME . ".name FROM " . AddressGroup::TABLE_NAME . " LEFT JOIN " . AddressCompany::TABLE_NAME . " ON " . AddressGroup::TABLE_NAME . ".pkey=" . AddressCompany::TABLE_NAME . ".addressGroup LEFT JOIN " . AddressData::TABLE_NAME . " ON " . AddressCompany::TABLE_NAME . ".pkey=" . AddressData::TABLE_NAME . ".addressCompany WHERE " . AddressData::TABLE_NAME . ".pkey=" . Teilabrechnung::TABLE_NAME . ".verwalter),";
					$query .= "(SELECT " . AddressGroup::TABLE_NAME . ".name FROM " . AddressGroup::TABLE_NAME . " LEFT JOIN " . AddressCompany::TABLE_NAME . " ON " . AddressGroup::TABLE_NAME . ".pkey=" . AddressCompany::TABLE_NAME . ".addressGroup WHERE " . AddressCompany::TABLE_NAME . ".pkey=" . Teilabrechnung::TABLE_NAME . ".verwalter)";
					$query .= ")) AS verwalterGroupName, ";

					if ($b==0) $query .= "(SELECT IF(" . ProcessStatus::TABLE_NAME . ".finished>0 && (" . ProcessStatus::TABLE_NAME . ".currentStatus=7 || " . ProcessStatus::TABLE_NAME . ".currentStatus=26) , " . ProcessStatus::TABLE_NAME . ".finished, UNIX_TIMESTAMP()) - (SELECT auftragsdatumAbrechnung FROM " . Teilabrechnung::TABLE_NAME . " WHERE abrechnungsJahr=" . ProcessStatus::TABLE_NAME . ".abrechnungsjahr ORDER BY auftragsdatumAbrechnung ASC LIMIT 1)) AS duration, ";
					if ($b==0) $query .= "(SELECT SUM(kuerzungsbetrag) FROM " . Kuerzungsbetrag::TABLE_NAME . " LEFT JOIN  " . Widerspruchspunkt::TABLE_NAME . " ON " . Widerspruchspunkt::TABLE_NAME . ".pkey=" . Kuerzungsbetrag::TABLE_NAME . ".widerspruchspunkt WHERE (rating=" . Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN . " OR rating=" . Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB . " OR rating=" . Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU . ") AND " . Widerspruchspunkt::TABLE_NAME . ".widerspruch=(SELECT pkey FROM " . Widerspruch::TABLE_NAME . " WHERE abrechnungsJahr=" . AbrechnungsJahr::TABLE_NAME . ".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS wsSumme, ";
					if ($b==0) $query .= "(SELECT SUM(kuerzungsbetrag) FROM " . Kuerzungsbetrag::TABLE_NAME . " LEFT JOIN  " . Widerspruchspunkt::TABLE_NAME . " ON " . Widerspruchspunkt::TABLE_NAME . ".pkey=" . Kuerzungsbetrag::TABLE_NAME . ".widerspruchspunkt WHERE realisiert=" . Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO . " AND (rating=" . Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN . " OR rating=" . Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB . " OR rating=" . Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU . ") AND " . Widerspruchspunkt::TABLE_NAME . ".widerspruch=(SELECT pkey FROM " . Widerspruch::TABLE_NAME . " WHERE abrechnungsJahr=" . AbrechnungsJahr::TABLE_NAME . ".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1) ) AS wsSummeOffen, ";
					if ($b==0) $query .= ProcessStatus::GetPrioColumnQuery() . ", ";
					if ($b==0) $query .= " (SELECT SUM(betragKunde) FROM " . Teilabrechnungsposition::TABLE_NAME . " LEFT JOIN " . Teilabrechnung::TABLE_NAME . " ON teilabrechnung=" . Teilabrechnung::TABLE_NAME . ".pkey WHERE abrechnungsJahr=" . AbrechnungsJahr::TABLE_NAME . ".pkey) AS betragKunde, ";
					$query .= $currentResponsibleRSUser." ";
					$query .= "FROM " . ProcessStatus::TABLE_NAME . " ";
					$query .= "LEFT JOIN " . ProcessStatusGroup::TABLE_NAME . " ON " . ProcessStatus::TABLE_NAME . ".processStatusGroup_rel=" . ProcessStatusGroup::TABLE_NAME . ".pkey ";
					$query .= "LEFT JOIN " . AbrechnungsJahr::TABLE_NAME . " ON " . ProcessStatus::TABLE_NAME . ".abrechnungsjahr=" . AbrechnungsJahr::TABLE_NAME . ".pkey ";
					$query .= "LEFT JOIN " . Teilabrechnung::TABLE_NAME . " ON " . ProcessStatus::TABLE_NAME . ".currentTeilabrechnung=" . Teilabrechnung::TABLE_NAME . ".pkey ";
					$query .= "LEFT JOIN " . Contract::TABLE_NAME . " ON " . AbrechnungsJahr::TABLE_NAME . ".contract=" . Contract::TABLE_NAME . ".pkey ";
					$query .= "LEFT JOIN " . CShop::TABLE_NAME . " ON " . Contract::TABLE_NAME . ".cShop=" . CShop::TABLE_NAME . ".pkey ";
					$query .= "LEFT JOIN " . CLocation::TABLE_NAME . " ON " . CShop::TABLE_NAME . ".cLocation=" . CLocation::TABLE_NAME . ".pkey ";
					$query .= "LEFT JOIN " . CCompany::TABLE_NAME . " ON " . CLocation::TABLE_NAME . ".cCompany=" . CCompany::TABLE_NAME . ".pkey ";
					$query .= "LEFT JOIN " . CGroup::TABLE_NAME . " ON " . CCompany::TABLE_NAME . ".cGroup=" . CGroup::TABLE_NAME . ".pkey ";
					if($a == 0)
					{
						$query .= "LEFT JOIN " . User::TABLE_NAME . " ON " . CShop::TABLE_NAME . ".cPersonRS=" . User::TABLE_NAME . ".pkey ";
					}
					else
					{
						if($a == 1)
						{
							$query .= "LEFT JOIN " . User::TABLE_NAME . " ON " . CShop::TABLE_NAME . ".cPersonCustomer=" . User::TABLE_NAME . ".pkey ";
						}
					}
					$query .= ")";
					$this->db->Query($query);
				}
			}
		}
		return true;
	}

	protected function BuildWhereClause($whereClause)
	{	
		// Zugriffsberechtigung 
		if( $_SESSION["currentUser"]->GetGroupBasetype($this->db) < UM_GROUP_BASETYPE_ADMINISTRATOR )
		{
			$userGroupIDs=$_SESSION["currentUser"]->GetGroupIDs($this->db);
			$userGroupQuery="";
			for( $a=0; $a<count($userGroupIDs); $a++ )
			{
				if( $userGroupQuery!="" )$userGroupQuery.=" OR ";
				$userGroupQuery.="userGroup=".$userGroupIDs[$a];
			}
			if( trim($userGroupQuery)!="" )
			{
				if( $whereClause!="" )$whereClause.=" AND ";
				$whereClause.=" (".$userGroupQuery.")";
			}
		}
		
		// Zugriff für Kunden weiter einschränken...
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			if ($whereClause!="") $whereClause.=" AND ";
			$whereClause.= " (archiveStatus=2 OR archiveStatus=1 ) "; // <-- QUICKHACK: bei Kunden sollen auch die zu bereinigenden Prozesse angezeigt werden i.A von Peter Hildenbrand 
		}
		
		return $whereClause;
	}
	
	/**
	 * Gibt die Anzahl der Prozesse zurück
	 * @return int
	 */
	public function GetWorkflowStatusCount($searchString="", $additionalWhereClause="", $getGroups=false, $useLightVersion=false)
	{
		// select view...
		$viewToUse = "";
		if( $_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP )
		{
			$viewToUse = ProcessStatus::TABLE_NAME."_rs_view".($useLightVersion ? "_light" : "");
		}
		else
		{
			$viewToUse = ProcessStatus::TABLE_NAME."_customer_view".($useLightVersion ? "_light" : "");
		}
		// build where clause...
		$whereClause = $this->BuildWhereClause($additionalWhereClause);
		$allRowNames = Array("pkey", "currentStatus", "userGroup", "groupId", "groupName", "zuweisungUser", "cPersonRS", "cPersonCustomer", "coverUser", "locationName", "street", "zip", "city", "objectDescription", "country", "shopName", "RSID", "jahr", "creationTime", "telefontermin", "verwalterGroupName", "currentResponsibleRSUser"/*, "currentResponsibleRSUserShortName"*/, "archiveStatus", "processStatusGroup_rel");
		if (!$useLightVersion) $allRowNames = array_merge($allRowNames, Array("planned", "deadline", "processStatusGroupName", "internalShopNo", "auftragsdatumAbrechnung", "duration", "wsSumme", "wsSummeOffen", "betragKunde", "prio"));
		$searchRowNames = Array("groupName", "locationName", "street", "zip", "city", "objectDescription", "country", "shopName", "RSID", "jahr", "verwalterGroupName");
		if (!$useLightVersion) $searchRowNames = array_merge($searchRowNames, Array("processStatusGroupName", "internalShopNo", "auftragsdatumAbrechnung", "wsSumme", "betragKunde"));

		
		$object=new ProcessStatus($this->db);
		//echo $whereClause;
		$numEntrys = $this->GetDBEntryCount($searchString, $viewToUse, $allRowNames, $whereClause, "", "", $searchRowNames, $additionalSelectField);
		return $numEntrys;
	}
	
	/**
	 * Gibt die Prozesse zurück
	 * @return ProcessStatus[]
	 */
	public function GetWorkflowStatus($searchString="", $orderBy="deadline", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20, $additionalWhereClause="", $getGroups=false, $useLightVersion=false)
	{
		// select view...
		$viewToUse = "";
		if( $_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP )
		{
			$viewToUse = ProcessStatus::TABLE_NAME."_rs_view".($useLightVersion ? "_light" : "");
		}
		else
		{
			$viewToUse = ProcessStatus::TABLE_NAME."_customer_view".($useLightVersion ? "_light" : "");
		}
		// build where clause...
		$whereClause = $this->BuildWhereClause($additionalWhereClause);
		$allRowNames = Array("pkey", "currentStatus", "userGroup", "groupId", "groupName", "zuweisungUser", "cPersonRS", "cPersonCustomer", "coverUser", "locationName", "street", "zip", "city", "objectDescription", "country", "shopName", "RSID", "jahr", "creationTime", "telefontermin", "verwalterGroupName", "currentResponsibleRSUser"/*, "currentResponsibleRSUserShortName"*/, "archiveStatus", "active", "processStatusGroup_rel");
		if (!$useLightVersion) $allRowNames = array_merge($allRowNames, Array("planned", "deadline", "processStatusGroupName", "internalShopNo", "auftragsdatumAbrechnung", "duration", "wsSumme", "wsSummeOffen", "betragKunde", "prio"));
		$searchRowNames = Array("groupName", "locationName", "street", "zip", "city", "objectDescription", "country", "shopName", "RSID", "jahr", "verwalterGroupName");
		if (!$useLightVersion) $searchRowNames = array_merge($searchRowNames, Array("processStatusGroupName", "internalShopNo", "auftragsdatumAbrechnung", "wsSumme", "betragKunde"));
		// build additional select query
		$additionalSelectField = "(SELECT IF(".self::BuildStatusResponsibleQuery($this->db, true).", 0, 1)) AS active";

		$entryData = $this->GetDBEntryData($searchString, Array("processStatusGroup_rel", $orderBy), $orderDirection, $currentPage, $numEntrysPerPage, $viewToUse, $allRowNames, $whereClause, "", "", $searchRowNames, ProcessStatus::TABLE_NAME, $additionalSelectField);

		// Objekte erzeugen
		$groups = Array();
		$objects = Array();
		foreach ($entryData as $data)
		{
			// process of a group? 
			if ($getGroups && (int)$data['processStatusGroup_rel']!=0)
			{
				// Load and add group...
				if (!isset($groups[(int)$data['processStatusGroup_rel']]))
				{
					$object = new ProcessStatusGroup($this->db);
					if ($object->Load((int)$data['processStatusGroup_rel'], $this->db)===true)
					{
						$groups[(int)$data['processStatusGroup_rel']] = $object;
						$objects[] = $object;
						// Add all processes of group...
						//$groupProcessList = $object->GetProcess();
						// use this function for retrive process elements to keep correct ordering
						//$groupProcessList = $this->GetWorkflowStatus("", $orderBy, $orderDirection, 0, 0, "processStatusGroup_rel=".(int)$data['processStatusGroup_rel'], false);
						//$objects = array_merge($objects, $groupProcessList);
					}
				}
			}
			else
			{
				// process
				$object = new ProcessStatus($this->db);
				if ($object->LoadFromArray($data, $this->db)===true) $objects[] = $object;
			}
		}
		return $objects;
	}
	
	/**
	 * Return the total number of workflows
	 * @param string $searchString
	 * @return int 
	 */
	public function GetWorkflowCount($searchString="")
	{
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db) < UM_GROUP_BASETYPE_RSMITARBEITER) return 0;
		
		//$joinClause= "LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".teilabrechnung=".Teilabrechnung::TABLE_NAME.".pkey ";
		$joinClause="LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".User::TABLE_NAME." ON ".CShop::TABLE_NAME.".cPersonRS=".User::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => ProcessStatus::TABLE_NAME, "tableRowNames" => $this->processStatusDummy->GetTableConfig()->rowName),
							Array("tableName" => AbrechnungsJahr::TABLE_NAME, "tableRowNames" => $this->abrechnungsJahrDummy->GetTableConfig()->rowName),
							Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
							Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
							Array("tableName" => User::TABLE_NAME, "tableRowNames" => $this->userDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		$allRowNames[] = "duration";
		$additionalSelectField = "(SELECT IF(".ProcessStatus::TABLE_NAME.".finished>0 && (".ProcessStatus::TABLE_NAME.".currentStatus=7 || ".ProcessStatus::TABLE_NAME.".currentStatus=26) , ".ProcessStatus::TABLE_NAME.".finished, ".time().") - (SELECT auftragsdatumAbrechnung FROM ".Teilabrechnung::TABLE_NAME." WHERE abrechnungsJahr=".ProcessStatus::TABLE_NAME.".abrechnungsjahr ORDER BY auftragsdatumAbrechnung ASC LIMIT 1)) AS duration ";
		// Für Suche
		$rowsToUseS= Array(	Array("tableName" => AbrechnungsJahr::TABLE_NAME, "tableRowNames" => $this->abrechnungsJahrDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
					);
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		$object=new ProcessStatus($this->db);
		return $this->GetDBEntryCount($searchString, ProcessStatus::TABLE_NAME, $allRowNames, "", $joinClause, "", $searchRowNames, $additionalSelectField);
	}
	
	/**
	 * Return the process status
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @return ProcessStatus[] 
	 */
	public function GetWorkflows($searchString="", $orderBy="deadline", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20)
	{
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db) < UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT) return Array();
		
		$joinClause ="LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".User::TABLE_NAME." ON ".CShop::TABLE_NAME.".cPersonRS=".User::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => ProcessStatus::TABLE_NAME, "tableRowNames" => $this->processStatusDummy->GetTableConfig()->rowName),
							Array("tableName" => AbrechnungsJahr::TABLE_NAME, "tableRowNames" => $this->abrechnungsJahrDummy->GetTableConfig()->rowName),
							Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
							Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
							Array("tableName" => User::TABLE_NAME, "tableRowNames" => $this->userDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		$allRowNames[] = "duration";
		// Für Suche
		$rowsToUseS= Array(	Array("tableName" => AbrechnungsJahr::TABLE_NAME, "tableRowNames" => $this->abrechnungsJahrDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
					);
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		$additionalSelectField = "(SELECT IF(".ProcessStatus::TABLE_NAME.".finished>0 && (".ProcessStatus::TABLE_NAME.".currentStatus=7 || ".ProcessStatus::TABLE_NAME.".currentStatus=26) , ".ProcessStatus::TABLE_NAME.".finished, ".time().") - (SELECT auftragsdatumAbrechnung FROM ".Teilabrechnung::TABLE_NAME." WHERE abrechnungsJahr=".ProcessStatus::TABLE_NAME.".abrechnungsjahr ORDER BY auftragsdatumAbrechnung ASC LIMIT 1)) AS duration ";
		//echo $whereClause;
		$object=new ProcessStatus($this->db);
		$dbEntrys=$this->GetDBEntry($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, ProcessStatus::TABLE_NAME, $allRowNames, "", $joinClause, "", $searchRowNames, $additionalSelectField);
		// Objekte erzeugen
		$objects=Array();
		for ($a=0; $a<count($dbEntrys); $a++)
		{
			$object = new ProcessStatus($this->db);
			if ($object->Load($dbEntrys[$a], $this->db)===true) $objects[] = $object;
		}
		return $objects;
	}

	/**
	 * Return the total number of workflows
	 * @param string $searchString
	 * @return int 
	 */
	public function GetProcessStatusCount($searchString="")
	{
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db) < UM_GROUP_BASETYPE_RSMITARBEITER) return 0;
		$object=new ProcessStatusGroup($this->db);
		
		$allRowNames = Array();
		$additionalSelectField = "";
		$joinClause = "";
		
		// Für Suche
		$rowsToUseS= Array(	Array("tableName" => ProcessStatusGroup::TABLE_NAME, "tableRowNames" => $object->GetTableConfig()->rowName) );
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		
		return $this->GetDBEntryCount($searchString, ProcessStatusGroup::TABLE_NAME, $allRowNames, "", $joinClause, "", $searchRowNames, $additionalSelectField);
	}
	
	/**
	 * 
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @return ProcessStatusGroup[]
	 */
	public function GetProcessStatusGroups($searchString="", $orderBy="deadline", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20)
	{
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db) < UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT) return Array();
		
		$object = new ProcessStatusGroup($this->db);
		
		$allRowNames = Array();
		$additionalSelectField = "";
		$joinClause = "";
		
		// Für Suche
		$rowsToUseS= Array(	Array("tableName" => ProcessStatusGroup::TABLE_NAME, "tableRowNames" => $object->GetTableConfig()->rowName) );
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		
		$dbEntrys=$this->GetDBEntry($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, ProcessStatusGroup::TABLE_NAME, $allRowNames, "", $joinClause, "", $searchRowNames, $additionalSelectField);
		// Objekte erzeugen
		$objects=Array();
		for ($a=0; $a<count($dbEntrys); $a++)
		{
			$object = new ProcessStatusGroup($this->db);
			if ($object->Load($dbEntrys[$a], $this->db)===true) $objects[] = $object;
		}
		return $objects;
	}
	
	/**
	 * return an empty ProcessStatus object of the passed type
	 * @param DBManager $db
	 * @param int $type
	 * @return ProcessStatus
	 */
	static public function CreateEmptyProcessStatus(DBManager $db, $type)
	{
		$object = null;
		switch((int)$type)
		{
			case self::PROCESS_TYPE_PROCESSSTATUS:
				$object = new ProcessStatus($db);
				break;
			case self::PROCESS_TYPE_PROCESSSTATUSGROUP:
				$object = new ProcessStatusGroup($db);
				break;
		}
		return $object;
	}
	
	/**
	 * Return the id of a ProcessStatus
	 * @param ProcessStatus $process
	 * @return string
	 */
	static public function GetProcessStatusId(ProcessStatus $process)
	{
		$preId = '';
		switch(get_class($process))
		{
			case 'ProcessStatus':
				$preId = 'PID';
				break;
			case 'ProcessStatusGroup':
				$preId = 'GID';
				break;
			default:
				$preId = get_class($process);
		}
		
		return $preId.$process->GetPKey();
	}
	
	/**
	 * return a process by id
	 * @param DBManager $db
	 * @param string $prozessId [CLASSNAME]_[PKEY]
	 * @return ProcessStatus
	 */
	static public function GetProcessStatusById(DBManager $db, $prozessId)
	{
		preg_match("/^([a-zA-Z]+)([0-9]+)$/i", trim($prozessId), $matches);
		$object = null;
		switch($matches[1])
		{
			case 'PID':
				$object = self::CreateEmptyProcessStatus($db, self::PROCESS_TYPE_PROCESSSTATUS);
				break;
			case 'GID':
				$object = self::CreateEmptyProcessStatus($db, self::PROCESS_TYPE_PROCESSSTATUSGROUP);
				break;
			default:
				echo "Unknown class identifier '".$matches[1]."'";
				exit;
		}
		if ($object->Load((int)$matches[2], $db)!==true) return null;
		return $object;
	}
	
	/**
	 * load the data from db into the passed object (if id match to class)
	 * @param DBManager $db
	 * @param ProcessStatus $obj
	 * @param string $prozessId
	 * @return boolean
	 */
	static public function LoadProcessStatusById(DBManager $db, ProcessStatus $obj, $prozessId)
	{
		// check to which class the ID belong to...
		preg_match("/^([a-zA-Z]+)([0-9]+)$/i", trim($prozessId), $matches);
		switch($matches[1])
		{
			case 'PID':
				$object = self::CreateEmptyProcessStatus($db, self::PROCESS_TYPE_PROCESSSTATUS);
				break;
			case 'GID':
				$object = self::CreateEmptyProcessStatus($db, self::PROCESS_TYPE_PROCESSSTATUSGROUP);
				break;
			default:
				echo "Unknown class identifier '".$matches[1]."'";
				exit;
		}
		// if the classes are the same, load data by key into passed object
		if (get_class($object)==get_class($obj))
		{
			if ($obj->Load((int)$matches[2], $db)===true) return true;
		}
		return false;
	}
	
	/**
	 * Gibt das Status-Config-Array für die übergebene ID zurück
	 * @param int $statusId
	 * @return Array
	 */
	static public function GetProzessStatusForStatusID($statusID)
	{
		global $processStatusConfig;
		for ($a=0; $a<count($processStatusConfig); $a++)
		{
			if ($processStatusConfig[$a]["ID"]==$statusID) return $processStatusConfig[$a];
		}
		return false;
	}
	
	/**
	 * Return if the status id is active for the usergroup
	 * @param int $statusId
	 * @param int $userGroup
	 * @return boolean
	 */
	static public function UserGroupResponsibleForProzess($statusId, $userGroup)
	{
	   $status = self::GetProzessStatusForStatusID($statusId);
	   if ($status===false) return false;
	   if (in_array($userGroup, $status["responsible"])) return true;
	   return false;
	}
	
	/**
	 * Diese Funktion gibt zurück, ob der aktuell angemeldete Benutzer für den Prozess verantwortlich ist
	 * @param DBManager $db
	 * @param int $statusID Prozessstatus der abgefragt werden soll
	 * @param User $user
	 * @return bool Verantwortlich? (true = Ja / false = Nein)
	 */
	static public function UserResponsibleForProzess(DBManager $db, $statusID, User $user=null)
	{
		global $processStatusConfig;
		if ($user==null) $user = $_SESSION["currentUser"];
		// Status suchen...
		for ($a=0; $a<count($processStatusConfig); $a++)
		{
			// gesuchter status?
			if ($processStatusConfig[$a]["ID"]==$statusID)
			{
				// Prüfen ob Benutzer für diesen Status zuständig ist
				$groupIDs = $user->GetGroupBasetypes($db);
				for ($b=0; $b<count($groupIDs); $b++)
				{
					if (in_array($groupIDs[$b], $processStatusConfig[$a]["responsible"])) return true;
				}
				return false;
			}
		}
		// Im Zweifelsfall kann der Eintrag nicht editiert werden
		return false;
	} 
	
	/**
	 * Return the highest basetpye group of the given user to determine active/passiv processes 
	 * @param DBManager $db
	 * @param User $user
	 * @return int
	 */
	static private function GetHighestGroupBaseTypeOfUser(DBManager $db, User $user)
	{
		$groups = self::GetAllGroupBaseTypeUsedForProcessStatus();
		$groupBasetype = UM_GROUP_BASETYPE_NONE;
		foreach ($groups as $groupId) 
		{
			if ($user->BelongToGroupBasetype($db, $groupId))
			{
				$groupBasetype = $groupId;
				break;
			}
		}
		if ($groupBasetype==UM_GROUP_BASETYPE_NONE && $user->GetGroupBasetype($db)>UM_GROUP_BASETYPE_RSMITARBEITER) $groupBasetype = UM_GROUP_BASETYPE_RSMITARBEITER;
		return $groupBasetype;
	}
	
	/**
	 * Return all GroupBaseTypes used in Process Status as 'responsible' (highest group first!)
	 * @global array $processStatusConfig
	 * @return array
	 */
	static private function GetAllGroupBaseTypeUsedForProcessStatus()
	{
		global $UM_GROUP_BASETYPE_INFOS;
		$mappingArray = Array();
		foreach ($UM_GROUP_BASETYPE_INFOS as $groupId => $groupInfo) 
		{
			if (isset($groupInfo['useGroupForProcess']))
			{
				$mappingArray[(int)$groupInfo['useGroupForProcess']] = (int)$groupInfo['useGroupForProcess'];
			}
			else
			{
				$mappingArray[(int)$groupId] = (int)$groupId;
			}
		}
		$usedGroupBaseTypes = array_keys($mappingArray);
		rsort($usedGroupBaseTypes, SORT_NUMERIC);
		return $usedGroupBaseTypes;
	}
	
	/**
	 * Diese Funktion erstellt einen DB-Query, welcher die Prozesse nach aktiv (true) bzw. passiv (false) filtert 
	 * @param DBManager $db
	 * @param bool active
	 * @return string DB-where clause
	 */
	static public function BuildStatusResponsibleQuery(DBManager $db, $active)
	{
		global $processStatusConfig;
		$groupBasetype = self::GetHighestGroupBaseTypeOfUser($db, $_SESSION["currentUser"]);
		// Wenn keine Gruppe ermittelt werden konnte, Query zurückgeben der keine Einträge zurückgibt
		if ($groupBasetype==UM_GROUP_BASETYPE_NONE) return "true=false";
		$status = self::GetAllStatusForGroup($groupBasetype);
		if (count($status)==0) return "true=false";
		$query = "currentStatus IN (".implode(', ', $status).")";
		$query = ($active ? "" : " NOT")." (".$query.")";
		//echo $query;
		return $query;
	}
	
	/**
	 * Diese Funktion gibt zurück, ob der aktuell angemeldete Benutzer den Prozess mit der übergebene Status ID editieren darf oder nicht 
	 * @param DBManager $db Datenbankobjekt
	 * @param int $statusID Prozessstatus der abgefragt werden soll
	 * @return bool Editierbar? (true = Ja / false = Nein)
	 */
	static public function UserAllowedToEditProzess(DBManager $db, $statusID)
	{
		global $processStatusConfig;
		$groupBasetype = self::GetHighestGroupBaseTypeOfUser($db, $_SESSION["currentUser"]);
		if ($groupBasetype==UM_GROUP_BASETYPE_RSMITARBEITER) return true; // FMS-Mitarbeiter dürfen auch passive Aufgaben bearbeiten
		for ($a=0; $a<count($processStatusConfig); $a++ )
		{
			if ($processStatusConfig[$a]["ID"]==$statusID)
			{
				if (in_array($groupBasetype, $processStatusConfig[$a]["responsible"])) return true;
			}
		}
		// Im Zweifelsfall kann der Eintrag nicht editiert werden
		return false;
	}
	
	/**
	 * Diese Funktion gibt zurück, ob der Prozess für den Kunden ist
	 * @param int $statusID Prozessstatus der abgefragt werden soll
	 * @return bool Kundenprozess? (true = Ja / false = Nein)
	 */
	static public function IsCustomerProzess($statusID)
	{
		global $processStatusConfig;
		for ($a=0; $a<count($processStatusConfig); $a++)
		{
			if ($processStatusConfig[$a]["ID"]==$statusID)
			{
				for ($b=0; $b<count($processStatusConfig[$a]["responsible"]);$b++)
				{
					if ($processStatusConfig[$a]["responsible"][$b]!=UM_GROUP_BASETYPE_KUNDE) return false;
				}
				return true;
			}
		}
		// Im Zweifelsfall kein Kundeneintrag
		return false;
	}
	
	/**
	 * Return all status the given group is responsible for
	 * @global array $processStatusConfig
	 * @param int $group
	 * @return int[]
	 */
	static public function GetAllStatusForGroup($group)
	{
		global $processStatusConfig;
		$returnValue = Array();
		for ($a=0; $a<count($processStatusConfig); $a++)
		{
			if (in_array($group, $processStatusConfig[$a]["responsible"]))
			{
				$returnValue[] = $processStatusConfig[$a]["ID"];
			}
		}
		return $returnValue;
	}

	/**
	 * Return all status the given group is responsible for
	 * @global array $processStatusConfig
	 * @param int $group the group the lookup should take place for
	 * @param array $excludedGroups if one of this groups are also responsible for the status, the status will not be returned
	 * @return int[]
	 */
	static public function GetAllStatusForGroupWithoutGroups($group, $excludedGroups)
	{
		global $processStatusConfig;
		$returnValue = Array();
		for ($a=0; $a<count($processStatusConfig); $a++)
		{
			// is the passed group responsible for this status?
			if (in_array($group, $processStatusConfig[$a]["responsible"]))
			{
				$addToList = true;
				// is one of the other groups responible for this status too?
				for ($b=0; $b<count($processStatusConfig[$a]["responsible"]); $b++)
				{
					if (in_array($processStatusConfig[$a]["responsible"][$b], $excludedGroups))
					{
						// if yes -> we dont add the status to the return array
						$addToList = false;
						break;
					}
				}
				if ($addToList)
				{
					$returnValue[] = $processStatusConfig[$a]["ID"];
				}
			}
		}
		return $returnValue;
	}
	
	/**
	 * Return all status the given group is responsible for
	 * @global array $processStatusConfig
	 * @param int $group the group the lookup should take place for
	 * @param array $excludedGroups only if one of this groups are also responsible for the status, the status will be returned
	 * @return int[]
	 */
	static public function GetAllStatusForGroupWithGroups($group, $excludedGroups)
	{
		global $processStatusConfig;
		$returnValue = Array();
		for ($a=0; $a<count($processStatusConfig); $a++)
		{
			// is the passed group responsible for this status?
			if (in_array($group, $processStatusConfig[$a]["responsible"]))
			{
				$addToList = false;
				// is one of the other groups responible for this status too?
				for ($b=0; $b<count($processStatusConfig[$a]["responsible"]); $b++)
				{
					if (in_array($processStatusConfig[$a]["responsible"][$b], $excludedGroups))
					{
						// if yes -> we add the status to the return array
						$addToList = true;
						break;
					}
				}
				if ($addToList)
				{
					$returnValue[] = $processStatusConfig[$a]["ID"];
				}
			}
		}
		return $returnValue;
	}
	
	/**
	 * Gibt den Anzeigenamen des Status für die übergebene ID zurück
	 * @param DBManager $db
	 * @param User $user
	 * @param int $statusID
	 * @param mixed $wsUploaded
	 * @return string
	 */
	static public function GetStatusName(DBManager $db, User $user, $statusID, $wsUploaded=-1)
	{
		$curStatusData = self::GetProzessStatusForStatusID($statusID);
		if ($curStatusData!==false)
		{
			// Immer den Prozessschtrittnamen zurückgeben
			return $curStatusData["name"];
			/*
			// Bei FMS-Mitarbeitern immer den Prozessschtrittnamen ausgeben
			if ($user->GetGroupBasetype($db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP) return $curStatusData["name"];
			// Wenn Kunde verantwortlich für den Prozessschritt ist, den Namen des Prozesses ausgeben
			if (in_array(UM_GROUP_BASETYPE_KUNDE, $curStatusData["responsible"])) return $curStatusData["nameCustomer"];
			// Wenn der Kunde nicht verantwortlich für den Prozessschritt ist, den Prozessgruppennamen ausgeben
			if ($wsUploaded===-1) return $curStatusData["nameCustomer"];
			$processGroup = false;
			if ($statusID==26) $processGroup = self::GetProzessGroupForGroupID(2);
			if ($wsUploaded==true) $processGroup = self::GetProzessGroupForGroupID(1);
			if ($wsUploaded==false) $processGroup = self::GetProzessGroupForGroupID(0);
			if ($processGroup!==false)
			{
				return $processGroup["name"];
			}
			return "??";*/
		}
		return "???";
	}
	
	/**
	 * Return all status ids where the creation of a ProcessStatusGroup is allowed
	 * @global array $processStatusConfig
	 * @return array
	 */
	public static function GetStatusForProcessGroupCreation()
	{
		global $processStatusConfig;
		$returnValue = Array();
		for ($a=0; $a<count($processStatusConfig); $a++)
		{
			if (isset($processStatusConfig[$a]["allowCreateProcessGroup"]) && $processStatusConfig[$a]["allowCreateProcessGroup"]==true)
			{
				$returnValue[] = $processStatusConfig[$a]["ID"];
			}
		}
		return $returnValue;
	}
	
	/**
	 * Return all prozesses of the user which are relevant for calendar
	 * @param User $user
	 * @return ProcessStatus 
	 */
	public function GetCalendarRelevantWorkflows(User $user)
	{
		$joinClause ="LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey ";
		$data = $this->db->SelectAssoc("SELECT ".ProcessStatus::TABLE_NAME.".* FROM ".ProcessStatus::TABLE_NAME." ".$joinClause." WHERE telefontermin>0 AND ".CShop::TABLE_NAME.".cPersonRS=".(int)$user->GetPKey());
		$objects = Array();
		for ($a=0; $a<count($data); $a++)
		{
			$object = new ProcessStatus($this->db);
			if ($object->LoadFromArray($data[$a], $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Return a assoc array with all new prozesses for the customer
	 * @param DBManager $db
	 * @return Array
	 */
	static public function GetProzessesNewToCustomer(DBManager $db)
	{
		$statusToCheck = Array(20, 21, 28);
		$data = $db->SelectAssoc("SELECT pkey, jahr, groupName, locationName, RSID, cPersonCustomer FROM ".ProcessStatus::TABLE_NAME."_customer_view"." WHERE archiveStatus=".ProcessStatus::ARCHIVE_STATUS_UPTODATE." AND customerMailSend=0 AND currentStatus IN (".implode(",", $statusToCheck).")" );
		//echo count($data);
		$usersMap = Array();
		$prozesses = Array();
		foreach ($data as $prozessData) 
		{
			// find user object using cache
			$user = null;
			if (!isset($usersMap[$prozessData['cPersonCustomer']]))
			{
				$user = new User($db);
				if ($user->Load($prozessData['cPersonCustomer'], $db)===true)
				{
					$usersMap[$prozessData['cPersonCustomer']] = $user;
				}
				else
				{
					$user = null;
				}
			}
			else
			{
				$user = $usersMap[$prozessData['cPersonCustomer']];
			}
			// get process
			if ($user!=null)
			{
				$userEmail = $user->GetEMail();
				if (!isset($prozesses[$userEmail]))
				{
					$prozesses[$userEmail] = Array("responsibleUser" => $user, "prozesses" => Array());
				}
				$prozesses[$userEmail]["prozesses"][] = Array(	"pkey" => $prozessData['pkey'],
																"desc" => $prozessData['jahr']." ".$prozessData['groupName']." - ".$prozessData['locationName']." (SFM-ID: ".$prozessData['RSID'].")"
															);
			}
		}
		return $prozesses;
	}
	
	/**
	 * Return a assoc array with all prozesses with unclassified files grouped by responsible FMS User (key)
	 * @param DBManager $db
	 * @return Array
	 */
	static public function GetProzessesWithUnclassifiedFiles(DBManager $db)
	{
		$data = $db->SelectAssoc("SELECT ".ProcessStatus::TABLE_NAME.".pkey FROM ".ProcessStatus::TABLE_NAME." LEFT JOIN ".strtolower(ProcessStatus::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX)." ON ".ProcessStatus::TABLE_NAME.".pkey=".strtolower(ProcessStatus::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".processstatus LEFT JOIN ".File::TABLE_NAME." ON ".strtolower(ProcessStatus::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".file=".File::TABLE_NAME.".pkey WHERE ".File::TABLE_NAME.".fileSemantic=".FM_FILE_SEMANTIC_NEWCUSTOMERFILE. " GROUP BY ".ProcessStatus::TABLE_NAME.".pkey");
		$prozesses = Array();
		foreach ($data as $prozessId) 
		{
			$prozess = new ProcessStatus($db);
			if ($prozess->Load((int)$prozessId["pkey"], $db)===true)
			{
				$responsibleUser = $prozess->GetResponsibleRSUser();
				if ($responsibleUser!=null)
				{
					$userEmail = $responsibleUser->GetEMail();
					if (!isset($prozesses[$userEmail]))
					{
						$prozesses[$userEmail] = Array("responsibleUser" => $responsibleUser, "prozesses" => Array());
						
					}
					$prozesses[$userEmail]["prozesses"][] = $prozess;
				}
			}
		}
		$data = $db->SelectAssoc("SELECT ".ProcessStatusGroup::TABLE_NAME.".pkey FROM ".ProcessStatusGroup::TABLE_NAME." LEFT JOIN ".strtolower(ProcessStatusGroup::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX)." ON ".ProcessStatusGroup::TABLE_NAME.".pkey=".strtolower(ProcessStatusGroup::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".processstatusgroup LEFT JOIN ".File::TABLE_NAME." ON ".strtolower(ProcessStatusGroup::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".file=".File::TABLE_NAME.".pkey WHERE ".File::TABLE_NAME.".fileSemantic=".FM_FILE_SEMANTIC_NEWCUSTOMERFILE. " GROUP BY ".ProcessStatusGroup::TABLE_NAME.".pkey");
		foreach ($data as $prozessId) 
		{
			$prozess = new ProcessStatusGroup($db);
			if ($prozess->Load((int)$prozessId["pkey"], $db)===true)
			{
				$responsibleUser = $prozess->GetCurrentResponsibleRSUser($db);
				if ($responsibleUser!=null)
				{
					$userEmail = $responsibleUser->GetEMail();
					if (!isset($prozesses[$userEmail]))
					{
						$prozesses[$userEmail] = Array("responsibleUser" => $responsibleUser, "prozesses" => Array());
						
					}
					$prozesses[$userEmail]["prozesses"][] = $prozess;
				}
			}
		}
		
		return $prozesses;
	}
	
	/**
	 * Return an array with all users that have to be notified about prozesses with exeeded deadlines
	 * @param DBManager $db
	 * @return array
	 */
	static public function GetProzessesWithExceededDeadlines(DBManager $db)
	{
		// get all processes where deadline is exeeded
		$data = $db->SelectAssoc("SELECT jahr, groupName, locationName, RSID, currentResponsibleRSUser FROM ".ProcessStatus::TABLE_NAME."_rs_view"." WHERE archiveStatus=".ProcessStatus::ARCHIVE_STATUS_UPTODATE." AND deadline!=0 AND deadline<".time() );
		//echo count($data);
		$usersMap = Array();
		$prozesses = Array();
		foreach ($data as $prozessData) 
		{
			// find user object using cache
			$user = null;
			if (!isset($usersMap[$prozessData['currentResponsibleRSUser']]))
			{
				$user = new User($db);
				if ($user->Load($prozessData['currentResponsibleRSUser'], $db)===true)
				{
					$usersMap[$prozessData['currentResponsibleRSUser']] = $user;
				}
				else
				{
					$user = null;
				}
			}
			else
			{
				$user = $usersMap[$prozessData['currentResponsibleRSUser']];
			}
			// get process
			if ($user!=null)
			{
				$userEmail = $user->GetEMail();
				if (!isset($prozesses[$userEmail]))
				{
					$prozesses[$userEmail] = Array("responsibleUser" => $user, "prozesses" => Array());
				}
				$prozesses[$userEmail]["prozesses"][] = $prozessData['jahr']." ".$prozessData['groupName']." - ".$prozessData['locationName']." (SFM-ID: ".$prozessData['RSID'].")";
			}
		}
		return $prozesses;
	}
	
	/**
	 * Return an array with all users that have to be notified about prozesses with exeeded deadlines
	 * @param DBManager $db
	 * @return array
	 */
	static public function GetProzessesWithExceededDeadlinesForBl(DBManager $db)
	{
		// get all processes where deadline is exeeded
		$statusToIgnore = Array(7, 26);
		$data = $db->SelectAssoc("SELECT currentStatus, jahr, groupName, locationName, RSID, cPersonFmsLeader, currentResponsibleRSUser, GREATEST((".time()."-deadline), (".time()."-zahlungsdatum)) AS deadlineMissed FROM ".ProcessStatus::TABLE_NAME."_rs_view"." WHERE archiveStatus=".ProcessStatus::ARCHIVE_STATUS_UPTODATE." AND ((deadline!=0 AND deadline<".time().") OR (zahlungsdatum!=0 AND zahlungsdatum<".time().")) AND currentStatus NOT IN (".implode(",", $statusToIgnore).") ORDER BY deadlineMissed DESC" );
		//echo count($data);
		$prozesses = Array();
		foreach ($data as $prozessData) 
		{
			// get user object using cache
			$userFmsLeader = UserManager::GetUserByPkey($db, $prozessData['cPersonFmsLeader']);
			$userFmsCurrrentResponibleUser = UserManager::GetUserByPkey($db, $prozessData['currentResponsibleRSUser']);
			// get process
			if ($userFmsLeader!=null && $userFmsCurrrentResponibleUser!=null)
			{
				$userFmsLeaderEmail = $userFmsLeader->GetEMail();
				$userFmsCurrrentResponibleUserEmail = $userFmsCurrrentResponibleUser->GetEMail();
				if (!isset($prozesses[$userFmsLeaderEmail]))
				{
					$prozesses[$userFmsLeaderEmail] = Array("fmsLeader" => $userFmsLeader, "currrentResponibleUser" => Array());
				}
				if (!isset($prozesses[$userFmsLeaderEmail]["currrentResponibleUser"][$userFmsCurrrentResponibleUserEmail]))
				{
					$prozesses[$userFmsLeaderEmail]["currrentResponibleUser"][$userFmsCurrrentResponibleUserEmail] = Array("fmsCurrrentResponibleUser" => $userFmsCurrrentResponibleUser, "prozesses" => Array());
				}
				$prozesses[$userFmsLeaderEmail]["currrentResponibleUser"][$userFmsCurrrentResponibleUserEmail]["prozesses"][] = $prozessData['jahr']." ".$prozessData['groupName']." - ".$prozessData['locationName']." - Status \"".(self::GetProzessStatusForStatusID($prozessData['currentStatus'])['name'])."\" (SFM-ID: ".$prozessData['RSID'].") <i>+ ".ceil($prozessData['deadlineMissed']/60/60/24)." Tage</i>";
			}
		}
		return $prozesses;
	}
	
	/**
	 * Return an array with all users that have to be notified about prozesses with exeeded deadlines
	 * @param DBManager $db
	 * @return array
	 */
	static public function GetProzessesWithExceededDeadlinesCustomers(DBManager $db)
	{
		$statusToCheck = Array(20, 21);
		// get all processes where deadline is exeeded
		$data = $db->SelectAssoc("SELECT jahr, groupName, locationName, RSID, cPersonCustomer FROM ".ProcessStatus::TABLE_NAME."_customer_view"." WHERE archiveStatus=".ProcessStatus::ARCHIVE_STATUS_UPTODATE." AND deadline!=0 AND deadline<".time()." AND currentStatus IN (".implode(",", $statusToCheck).")" );
		//echo count($data);
		$usersMap = Array();
		$prozesses = Array();
		foreach ($data as $prozessData) 
		{
			// find user object using cache
			$user = null;
			if (!isset($usersMap[$prozessData['cPersonCustomer']]))
			{
				$user = new User($db);
				if ($user->Load($prozessData['cPersonCustomer'], $db)===true)
				{
					$usersMap[$prozessData['cPersonCustomer']] = $user;
				}
				else
				{
					$user = null;
				}
			}
			else
			{
				$user = $usersMap[$prozessData['cPersonCustomer']];
			}
			// get process
			if ($user!=null)
			{
				$userEmail = $user->GetEMail();
				if (!isset($prozesses[$userEmail]))
				{
					$prozesses[$userEmail] = Array("responsibleUser" => $user, "prozesses" => Array());
				}
				$prozesses[$userEmail]["prozesses"][] = $prozessData['jahr']." ".$prozessData['groupName']." - ".$prozessData['locationName']." (SFM-ID: ".$prozessData['RSID'].")";
			}
		}
		return $prozesses;
	}
	
	/**
	 * Return a assoc array with all new prozesses for the customer
	 * @param DBManager $db
	 * @param Array $log
	 * @return Array
	 */
	static public function GetProzessesForCustomerNotification(DBManager $db)
	{
		$prozessesNew = self::GetProzessesNewToCustomer($db);
		$prozessesExceededDeadlines = self::GetProzessesWithExceededDeadlinesCustomers($db);
		
		$prozesses = Array();
		foreach ($prozessesNew as $email => $infos)
		{
			if (!isset($prozesses[$email]))
			{
				$prozesses[$email] = Array("responsibleUser" => $infos["responsibleUser"]);
			}
			$prozesses[$email]["new"] = $infos["prozesses"];
		}
		foreach ($prozessesExceededDeadlines as $email => $infos)
		{
			if (!isset($prozesses[$email]))
			{
				$prozesses[$email] = Array("responsibleUser" => $infos["responsibleUser"]);
			}
			$prozesses[$email]["exceeded"] = $infos["prozesses"];
		}
		return $prozesses;
	}
	
	/**
	 * Diese Funktion prüft, ob sich bei einem Prozess der Status automatisch ändern muss und führt dies entsprechend aus
	 * @param DBManager $db
	 */
	static public function CheckAutoUpdateStatus(DBManager $db)
	{
		// Alle Prozesse holen, die sich in einem Status befinden, in dem ein automatischer Wechsel stattfinden kann
		$typesToCheck = Array(self::PROCESS_TYPE_PROCESSSTATUSGROUP, self::PROCESS_TYPE_PROCESSSTATUS);
		$statusToCheck = Array(12, 13, 17);	// ACHTUNG: Wenn ein Status hinzugefügt wird, muss in dieser FN weiter unten ein entsprechender CASE-Fall implementiert werden, der die Überprüfung und ggf. die Umschaltung vornimmt!!!
		$whereQuery = "";
		for ($a=0; $a<count($statusToCheck); $a++)
		{
			if ($whereQuery!="") $whereQuery.=" OR ";
			$whereQuery.="currentStatus=".$statusToCheck[$a];
		}
		
		foreach ($typesToCheck as $type) 
		{
			// get all processes
			$data = self::GetAutoUpdateProcess($db, $whereQuery, $type);
			// check processes...
			foreach ($data as $value)
			{
				switch ($value["currentStatus"])
				{
					case 12:
					case 13:
						if ((int)$value["rueckstellungBis"]+60*60*24 < time())
						{
							self::SetCurrentStatusToProcess($db, $value['obj'], 9);
						}
						break;
					case 17:
						if ((int)$value["deadline"]+60*60*24 < time())
						{
							self::SetCurrentStatusToProcess($db, $value['obj'], 14);
						}
						break;
					default:
						echo "<br /><br /><font color='#ff0000'>&#160;&#160;&#160;TODO: Für Status ".$value["currentStatus"]." muss die Funktion zum automatischen Wechsel des Status implementiert werden!</font><br /><br />";
						break;
				}
			}
		}
	}

	/**
	 * get all process status groups for update check
	 * @param DBManager $db
	 * @param string $whereQuery
	 * @param int $processType
	 * @return ProcessStatusGroup
	 */
	static private function GetAutoUpdateProcess(DBManager $db, $whereQuery, $processType)
	{
		$tempObject = self::CreateEmptyProcessStatus($db, $processType);
		$query = "SELECT pkey, currentStatus, rueckstellungBis, deadline FROM ".$tempObject->GetTableName()." WHERE (".$whereQuery.") ORDER BY currentStatus";
		//echo $query."<br />";
		$data = $db->SelectAssoc($query);
		$returnData = Array();
		foreach ($data as $value)
		{
			$object = self::CreateEmptyProcessStatus($db, $processType);
			if ($object->Load($value['pkey'], $db)===true)
			{
				$value['obj'] = $object;
				$returnData[] = $value;
			}
		}
		return $returnData;
	}
	
	/**
	 * switch the passed process to a new status
	 * @param DBManager $db
	 * @param ProcessStatus $status
	 * @param int $statusToSwitchTo
	 * @return boolean|int
	 */
	static private function SetCurrentStatusToProcess(DBManager $db, ProcessStatus $status, $statusToSwitchTo)
	{
		$currentStatus = $status->GetCurrentStatus();
		$status->SetCurrentStatus((int)$statusToSwitchTo);
		$status->SetAutoJumpFromStatus((int)$currentStatus);
		$status->ClearLastStatus();
		//echo "Überführe Protess ".self::GetProcessStatusId($status)." von Status ".$currentStatus." in Status ".$statusToSwitchTo."<br />";
		return $status->Store($db);
	}
	
	/**
	 * Return if the passed location is in a ProcessStatusGroup
	 * @param DBManager $db
	 * @param CLocation $location
	 * @return boolean
	 */
	static public function IsLocationInProcessStatusGroup(DBManager $db, CLocation $location)
	{
		if ($location->GetPKey()==-1) return false;
		$query = "SELECT processStatusGroup_rel FROM ".ProcessStatus::TABLE_NAME;
		$query.= " LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$query.= " LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey";
		$query.= " LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey";
		$query.= " WHERE processStatusGroup_rel!=0 AND cLocation=".$location->GetPKey();
		$query.= " GROUP BY processStatusGroup_rel";
		$data = $db->SelectAssoc($query);
		//print_r($data);
		return (count($data)>0 ? true : false);
	}
	
	/**
	 * Return number of ProcessStatusGroups the passed shop is in
	 * @param DBManager $db
	 * @param CShop $shop
	 * @return int
	 */
	static public function GetProcessStatusGroupCountOfShop(DBManager $db, CShop $shop)
	{
		if ($shop->GetPKey()==-1) return 0;
		$query = "SELECT processStatusGroup_rel FROM ".ProcessStatus::TABLE_NAME;
		$query.= " LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$query.= " LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey";
		$query.= " WHERE processStatusGroup_rel!=0 AND cShop=".$shop->GetPKey();
		$query.= " GROUP BY processStatusGroup_rel";
		$data = $db->SelectAssoc($query);
		return count($data);
	}

    /**
     * Return ids of ProcessStatusGroups the passed shop is in
     * @param DBManager $db
     * @param int $shopId
     * @return int[]
     */
    static public function GetProcessStatusGroupIdsOfShop(DBManager $db, $shopId)
    {
        $query = "SELECT processStatusGroup_rel FROM ".ProcessStatus::TABLE_NAME;
        $query.= " LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
        $query.= " LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey";
        $query.= " WHERE processStatusGroup_rel!=0 AND cShop=".$shopId;
        $query.= " GROUP BY processStatusGroup_rel";
        $data = $db->SelectAssoc($query);

        $returnValues = Array();
        foreach ($data as $values)
        {
            $returnValues[] = $values['processStatusGroup_rel'];
        }
        return $returnValues;
    }

    /**
     * Return ProcessStatusGroups the passed shop is in
     * @param DBManager $db
     * @param $shopId
     * @return ProcessStatusGroup[]
     */
    static public function GetProcessStatusGroupNamesOfShop(DBManager $db, $shopId)
    {
        $groups = self::GetProcessStatusGroupIdsOfShop($db, $shopId);
        if (count($groups)==0) return Array();

        $query = "SELECT name FROM ".ProcessStatusGroup::TABLE_NAME;
        $query.= " WHERE pkey IN (".implode(',', $groups).")";
        $data = $db->SelectAssoc($query);

        $returnValues = Array();
        foreach ($data as $values)
        {
            $returnValues[] = $values['name'];
        }
        return $returnValues;
    }

    /**
     * return all shops which are in the same ProcessStatusGroups
     * @param DBManager $db
     * @param int $processStatusGroupId
     * @return int[]
     */
    static public function GetShopIdsInProcessStatusGroup(DBManager $db, $processStatusGroupId)
    {
        // get all shops of the group
        $query = "SELECT ".CShop::TABLE_NAME.".pkey FROM ".CShop::TABLE_NAME;
        $query.= " LEFT JOIN ".Contract::TABLE_NAME." ON ".CShop::TABLE_NAME.".pkey=".Contract::TABLE_NAME.".cShop";
        $query.= " LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".Contract::TABLE_NAME.".pkey=".AbrechnungsJahr::TABLE_NAME.".contract";
        $query.= " LEFT JOIN ".ProcessStatus::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".pkey=".ProcessStatus::TABLE_NAME.".abrechnungsjahr";
        $query.= " WHERE processStatusGroup_rel=".$processStatusGroupId;
        $query.= " GROUP BY ".CShop::TABLE_NAME.".pkey";
        $data = $db->SelectAssoc($query);
        $returnValues = Array();
        foreach ($data as $values)
        {
            $returnValues[] = $values['pkey'];
        }
        return $returnValues;
    }

	/**
	 * return all shops which are in the same ProcessStatusGroups
	 * @param DBManager $db
	 * @param CShop $shop
	 * @return CShop[]
	 */
	static public function GetShopsInSameProcessStatusGroup(DBManager $db, CShop $shop)
	{
		if ($shop->GetPKey()==-1) return Array();
		// get all groups the shop is in
		$groupQuery = "SELECT processStatusGroup_rel FROM ".ProcessStatus::TABLE_NAME;
		$groupQuery.= " LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$groupQuery.= " LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey";
		$groupQuery.= " WHERE processStatusGroup_rel!=0 AND cShop=".$shop->GetPKey();
		$groupQuery.= " GROUP BY processStatusGroup_rel";
		// get all shops of the group
		$query = "SELECT ".CShop::TABLE_NAME.".* FROM ".CShop::TABLE_NAME;
		$query.= " LEFT JOIN ".Contract::TABLE_NAME." ON ".CShop::TABLE_NAME.".pkey=".Contract::TABLE_NAME.".cShop";
		$query.= " LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".Contract::TABLE_NAME.".pkey=".AbrechnungsJahr::TABLE_NAME.".contract";
		$query.= " LEFT JOIN ".ProcessStatus::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".pkey=".ProcessStatus::TABLE_NAME.".abrechnungsjahr";
		$query.= " WHERE processStatusGroup_rel IN (".$groupQuery.") AND ".CShop::TABLE_NAME.".pkey!=".$shop->GetPKey();
		$query.= " GROUP BY ".CShop::TABLE_NAME.".pkey";
		$data = $db->SelectAssoc($query);
		$returnValues = Array();
		foreach ($data as $values) 
		{
			$shopTemp = new CShop($db);
			if ($shopTemp->LoadFromArray($values, $db)===true) $returnValues[] = $shopTemp;
		}
		return $returnValues;
	}

    /**
     * Returns all shops that have to be changed if the passed shop should be changed
     * @param DBManager $db
     * @param CShop $shop
     * @return CShop[]
     */
    static public function GetDependingShops(DBManager $db, CShop $shop)
    {
        if ($shop->GetPKey()==-1) return 0;
        $groupIds = self::GetProcessStatusGroupIdsOfShop($db, $shop->GetPKey());
        $shopIds = Array();
        for ($a=0; $a<count($groupIds); $a++)
        {
            $ids = self::GetShopIdsInProcessStatusGroup($db, $groupIds[$a]);
            //echo $groupIds[$a].": ".count($ids)."<br />";
            foreach ($ids as $id)
            {
                if (!isset($shopIds[$id]))
                {
                    $shopIds[$id] = Array('groupIds' => Array($groupIds[$a]));
                    $furtherGroupIds = self::GetProcessStatusGroupIdsOfShop($db, $id);
                    foreach ($furtherGroupIds as $furtherGroupId)
                    {
                        if (!in_array($furtherGroupId, $groupIds))
                        {
                            $groupIds[] = $furtherGroupId;
                        }
                    }
                }
                else
                {
                    $shopIds[$id]['groupIds'][] = $groupIds[$a];
                }
            }
        }

     /*   echo "<pre>";
        print_r($groupIds);
        print_r($shopIds);
        echo "</pre>";*/

        $shopIdsToLoad = array_keys($shopIds);
        if (count($shopIdsToLoad)==0) return Array();
        $shopKeys = implode(',', $shopIdsToLoad);
        $query = "SELECT ".CShop::TABLE_NAME.".* FROM ".CShop::TABLE_NAME." WHERE pkey IN (".$shopKeys.")";
        $data = $db->SelectAssoc($query);
        $returnValues = Array();
        foreach ($data as $values)
        {
            $shopTemp = new CShop($db);
            if ($shopTemp->LoadFromArray($values, $db)===true && $shopTemp->GetPKey()!=$shop->GetPKey()) $returnValues[] = $shopTemp;
        }
        return $returnValues;
    }

	/**
	 * Return number of ProcessStatusGroups the passed contract is in
	 * @param DBManager $db
	 * @param Contract $contract
	 * @return int
	 */
	static public function GetProcessStatusGroupCountOfContract(DBManager $db, Contract $contract)
	{
		if ($contract->GetPKey()==-1) return 0;
		$query = "SELECT processStatusGroup_rel FROM ".ProcessStatus::TABLE_NAME;
		$query.= " LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$query.= " WHERE processStatusGroup_rel!=0 AND ".AbrechnungsJahr::TABLE_NAME.".contract=".$contract->GetPKey();
		$query.= " GROUP BY processStatusGroup_rel";
		$data = $db->SelectAssoc($query);
		return count($data);
	}
	
	/**
	 * return all contracts which are in the same ProcessStatusGroups
	 * @param DBManager $db
	 * @param Contract $contract
	 * @return Contract[]
	 */
	static public function GetContractsInSameProcessStatusGroup(DBManager $db, Contract $contract)
	{
		if ($contract->GetPKey()==-1) return Array();
		// get all groups the shop is in
		$groupQuery = "SELECT processStatusGroup_rel FROM ".ProcessStatus::TABLE_NAME;
		$groupQuery.= " LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$groupQuery.= " WHERE processStatusGroup_rel!=0 AND ".AbrechnungsJahr::TABLE_NAME.".contract=".$contract->GetPKey();
		$groupQuery.= " GROUP BY processStatusGroup_rel";
		// get all shops of the group
		$query = "SELECT ".Contract::TABLE_NAME.".* FROM ".Contract::TABLE_NAME;
		$query.= " LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".Contract::TABLE_NAME.".pkey=".AbrechnungsJahr::TABLE_NAME.".contract";
		$query.= " LEFT JOIN ".ProcessStatus::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".pkey=".ProcessStatus::TABLE_NAME.".abrechnungsjahr";
		$query.= " WHERE processStatusGroup_rel IN (".$groupQuery.") AND ".Contract::TABLE_NAME.".pkey!=".$contract->GetPKey();
		$query.= " GROUP BY ".Contract::TABLE_NAME.".pkey";
		$data = $db->SelectAssoc($query);
		$returnValues = Array();
		foreach ($data as $values) 
		{
			$contractTemp = new Contract($db);
			if ($contractTemp->LoadFromArray($values, $db)===true) $returnValues[] = $contractTemp;
		}
		return $returnValues;
	}

	/**
	 * @param DBManager $db
	 * @param AbrechnungsJahr $year
	 * @return ProcessStatus
	 */
	static public function GetProcessStatusByAbrechnungsJahr(DBManager $db, AbrechnungsJahr $year)
	{
		if ($year->GetPKey()==-1) return null;
		$query = "SELECT pkey FROM ".ProcessStatus::TABLE_NAME." WHERE abrechnungsjahr=".$year->GetPKey();
		$data = $db->SelectAssoc($query);
		if (count($data)==0) return null;
		$prozess = new ProcessStatus($db);
		if ($prozess->Load((int)$data[0]["pkey"], $db)!==true)
		{
			return null;
		}
		return $prozess;
	}
	
}
?>