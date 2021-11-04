<?php
/**
 * Diese Klasse repräsentiert eine Gruppe des Prozess-Workflows
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class ProcessStatusGroup extends ProcessStatus implements AttributeNameMaper //, DependencyFileDescription
{
	/**
	 * Datenbankname und Spalten
	 * @var string
	 */
	const TABLE_NAME = "processStatusGroup";
	
	/**
	 * Error codes for function IsProcessCompatible()
	 */
	const ERROR_DIFFERENT_STATUS = -1;
	const ERROR_DIFFERENT_FMS_PERSON = -2;
	const ERROR_DIFFERENT_CUSTOMER_PERSON = -3;
	const ERROR_PROCESS_WITHOUT_SHOP = -4;
	const ERROR_PROCESS_WITHOUT_GROUP = -5;
	const ERROR_DIFFERENT_CUSTOMER_GROUP = -6;
	const ERROR_DIFFERENT_CURRENCY = -7;
	const ERROR_PROCESS_WITHOUT_COMPANY = -8;
	const ERROR_DIFFERENT_CUSTOMER_COMPANY = -9;
	const ERROR_STORE_TO_DB = -10;
	const ERROR_DIFFERENT_FMSLEADER_PERSON = -11;
	const ERROR_DIFFERENT_CUSTOMERACCOUNTING_PERSON = -12;
	const ERROR_DIFFERENT_CUSTOMERSUPERVISOR_PERSON = -13;
	const ERROR_DIFFERENT_FMSACCOUNTSDEPARTMENT_PERSON = -15;
	const ERROR_WRONG_STATUS = -16;
	
	/**
	 * Dirtflags
	 */
	const DIRTFLAG_NONE = 0;
	const DIRTFLAG_PLANNED = 1;
	const DIRTFLAG_ASSIGNED_USER = 2;
	const DIRTFLAG_PRIO = 4;
	const DIRTFLAG_JUMPBACKSTATUS = 8;
	const DIRTFLAG_DEADLINE = 16;
	const DIRTFLAG_RUECKSTELLUNGBIS = 32;
	const DIRTFLAG_AUTOJUMPFROMSTATUS = 64;
	const DIRTFLAG_SETCURRENTSTATUS = 128;
	const DIRTFLAG_CLEARLASTSTATUS = 256;
	const DIRTFLAG_RUECKSTELLUNGBEGRUENDUNG = 512;
	const DIRTFLAG_FINISHED = 1024;
	const DIRTFLAG_ABSCHLUSSDATUM = 2048;
	const DIRTFLAG_ZAHLUNGSDATUM = 4096;
	const DIRTFLAG_PRIOFUNCTION = 8192;
	const DIRTFLAG_FORM = 16384;
	const DIRTFLAG_STAGE = 32768;
	const DIRTFLAG_REFERENCENUMBER = 65536;
	const DIRTFLAG_SUBPROCEDURE = 131072;

	/**
	 * Flag that indicates which data have to be written to group members
	 * @var int 
	 */
	private $dirtFlag = self::DIRTFLAG_NONE;
	
	/**
	 * Name der Prozessgruppe
	 * @var string 
	 */
	private $name = "";
	
	/**
	 * the currently selected ProcessStatus
	 * @var ProcessStatus
	 */
	private $selectedProcessStatus = null;
	
	/**
	 * List with all ProcessStatus of this group
	 * @var ProcessStatus []
	 */
	private $processStatusList = Array();
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function ProcessStatusGroup(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("name", "selectedProcessStatus_rel");
		$dbConfig->rowParam = Array("VARCHAR(255)", "BIGINT");
		$dbConfig->rowIndex = Array("name", "selectedProcessStatus_rel");
		parent::__construct($db, $dbConfig);
	}	
	
	/**
	 * Gibt den Typ des Status zurück
	 * @return int
	 */
	public function GetStatusTyp()
	{
		return WM_WORKFLOWSTATUS_TYPE_PROCESS;
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *						-1 		Kein Name angegeben
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if (trim($this->name)=="") return -1;
		$rowName[] = "name";
		$rowData[] = trim($this->name);
		$rowName[] = "selectedProcessStatus_rel";
		$rowData[] = ($this->selectedProcessStatus!=null ? $this->selectedProcessStatus->GetPKey() : -1);
		return parent::BuildDBArray($db, $rowName, $rowData);
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array $data Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		$this->name = $data["name"];
		// preload all processes of this group
		$this->LoadProcesses($db);
		// find selected process status in list
		$this->selectedProcessStatus = null;
		for($a=0; $a<count($this->processStatusList); $a++)
		{
			if ($this->processStatusList[$a]->GetPKey()==$data['selectedProcessStatus_rel'])
			{
				$this->selectedProcessStatus = $this->processStatusList[$a];
				break;
			}
		}
		return parent::BuildFromDBArray($db, $data);
	}
	
	/**
	 * Function is called when DBEntry is updated in db
	 * @param DBManager $db
	 */
	protected function OnChanged($db)
	{
		// check dirt flag
		if ($this->dirtFlag!=self::DIRTFLAG_NONE)
		{
			foreach ($this->processStatusList as $processStatus)
			{
				//echo "UPDATE ".$processStatus->GetPKey()." <br />";
				// Update 'Planned'
				if ($this->dirtFlag & self::DIRTFLAG_PLANNED)
				{
					$processStatus->SetPlanned($this->planned);
				}
				if ($this->dirtFlag & self::DIRTFLAG_ASSIGNED_USER)
				{
					$processStatus->SetZuweisungUser($this->zuweisungUser);
				}
				if ($this->dirtFlag & self::DIRTFLAG_PRIOFUNCTION)
				{
					$processStatus->SetPrioFunction(parent::GetPrioFunction());
				}
				if ($this->dirtFlag & self::DIRTFLAG_PRIO)
				{
					$processStatus->SetPrio(parent::GetPrio($db));
				}
				if ($this->dirtFlag & self::DIRTFLAG_JUMPBACKSTATUS)
				{
					$processStatus->SetJumpBackStatus(parent::GetJumpBackStatus());
				}
				if ($this->dirtFlag & self::DIRTFLAG_DEADLINE)
				{
					$processStatus->SetDeadline(parent::GetDeadline());
				}
				if ($this->dirtFlag & self::DIRTFLAG_RUECKSTELLUNGBIS)
				{
					$processStatus->SetRueckstellungBis(parent::GetRueckstellungBis());
				}
				if ($this->dirtFlag & self::DIRTFLAG_RUECKSTELLUNGBEGRUENDUNG)
				{
					$processStatus->SetRueckstellungBegruendung(parent::GetRueckstellungBegruendung());
				}
				if ($this->dirtFlag & self::DIRTFLAG_SETCURRENTSTATUS)
				{
					$processStatus->SetCurrentStatus(parent::GetCurrentStatus());
				}
				if ($this->dirtFlag & self::DIRTFLAG_AUTOJUMPFROMSTATUS)
				{
					$processStatus->SetAutoJumpFromStatus(parent::GetAutoJumpFromStatus());
				}
				if ($this->dirtFlag & self::DIRTFLAG_CLEARLASTSTATUS)
				{
					$processStatus->ClearLastStatus();
				}
				if ($this->dirtFlag & self::DIRTFLAG_FINISHED)
				{
					$processStatus->SetFinished(parent::GetFinished());
				}
				if ($this->dirtFlag & self::DIRTFLAG_ABSCHLUSSDATUM)
				{
					$processStatus->SetAbschlussdatum(parent::GetAbschlussdatum());
				}
				if ($this->dirtFlag & self::DIRTFLAG_ZAHLUNGSDATUM)
				{
					$processStatus->SetZahlungsdatum(parent::GetZahlungsdatum());
				}
				if ($this->dirtFlag & self::DIRTFLAG_FORM)
				{
					$processStatus->SetForm(parent::GetForm());
				}
				if ($this->dirtFlag & self::DIRTFLAG_STAGE)
				{
					$processStatus->SetStagesPointsToBeClarified(parent::GetStagesPointsToBeClarified());
				}
				if ($this->dirtFlag & self::DIRTFLAG_REFERENCENUMBER)
				{
					$processStatus->SetReferenceNumber(parent::GetReferenceNumber());
				}
				if ($this->dirtFlag & self::DIRTFLAG_SUBPROCEDURE)
				{
					$processStatus->SetSubProcedure(parent::GetSubProcedure());
				}
				// store changes to child
				$processStatus->Store($db);
			}
			// reset dirt flag
			$this->dirtFlag = self::DIRTFLAG_NONE;
		}
	}
	
	/**
	 * Delete this and all contained objects
	 * @param DBManager $db
	 * @param User $user 
	 */
	public function DeleteRecursive(DBManager $db, User $user)
	{
		if ($user->GetGroupBasetype($db) < UM_GROUP_BASETYPE_ADMINISTRATOR) return false;
		LoggingManager::GetInstance()->Log(new LoggingProcessStatus(LoggingProcessStatus::TYPE_DELETE, $this->GetCurrentStatusName($user, $db), "", serialize($this->GetDependencyFileDescription())));
		$this->recursiveDelete = true;
		// delete all comments...
		$this->commentRelationManager->RemoveAllComments($db);
		// delete all files...
		$this->fileRelationManager->RemoveAllFiles($db);
		// set relation in processstatus to 0 (ungroup)
		$db->Update(ProcessStatus::TABLE_NAME, Array("processStatusGroup_rel"), Array(0), "processStatusGroup_rel=".$this->GetPKey());
		$this->processStatusList = Array();
		// delete all 'Widersprüche'... (connected directly by ProcessStatusGroup)
		$widersprueche = $this->GetWidersprueche($db);
		for ($a=0; $a<count($widersprueche); $a++)
		{
			$widersprueche[$a]->DeleteRecursive($db, $user);
		}
		// delete this object
		$this->DeleteMe($db);
		return true;
	}
	
	/**
	 * Return Description
	 */
	public function GetDependencyFileDescription()
	{
		return "Gruppe '".$this->GetName()."'";
	}
	
	/**
	 * Setzt den Namen
	 * @param string $name
	 */
	public function SetName($name)
	{
		$this->name = trim($name);
	}
	
	/**
	 * Gbit den Namen zurück
	 * @return string
	 */
	public function GetName()
	{
		return $this->name;
	}
	
	/**
	 * Load all processes of this group
	 * @param DBManager $db
	 * @return boolean
	 */
	protected function LoadProcesses(DBManager $db)
	{
		if ($this->GetPKey()==-1) return false;
		$processList = Array();
		$data = $db->SelectAssoc("SELECT ".ProcessStatus::TABLE_NAME.".* FROM ".ProcessStatus::TABLE_NAME." WHERE processStatusGroup_rel=".(int)$this->GetPKey());
		foreach ($data as $entry)
		{
			$prozess = new ProcessStatus($db);
			if ($prozess->LoadFromArray($entry, $db)===true) $processList[] = $prozess;
		}
		$this->processStatusList = $processList;
		return true;
	}
	
	/**
	 * Return the process count of this group
	 * @return ProcessStatus[]
	 */
	public function GetProcessCount()
	{
		return count($this->processStatusList);
	}
	
	/**
	 * Return all process of this group
	 * @return ProcessStatus[]
	 */
	public function GetProcess()
	{
		return $this->processStatusList;
	}
	
	/**
	 * Check if the process can be added to this group
	 * @param DBManager $db
	 * @param ProcessStatus $processStatus
	 */
	protected function IsProcessCompatible(DBManager $db, ProcessStatus $processStatus)
	{
		// if there is no process in this group any process can be added
		if (count($this->processStatusList)==0) return true;
		// use first process in group as reference
		$processRef = $this->processStatusList[0];

		// Condition 1: customer group have to be the same
		$cGroupRef = $processRef->GetGroup();
		$cGroup = $processStatus->GetGroup();
		if ($cGroupRef==null || $cGroup==null) return self::ERROR_PROCESS_WITHOUT_GROUP;
		if ($cGroupRef->GetPKey()!=$cGroup->GetPKey()) return self::ERROR_DIFFERENT_CUSTOMER_GROUP;
		// Condition 2: customer companys have to be the same
		$cCompanyRef = $processRef->GetCompany();
		$cCompany = $processStatus->GetCompany();
		if ($cCompanyRef==null || $cCompany==null) return self::ERROR_PROCESS_WITHOUT_COMPANY;
		if ($cCompanyRef->GetPKey()!=$cCompany->GetPKey()) return self::ERROR_DIFFERENT_CUSTOMER_COMPANY;
		// Condition 3: all processes have to use the same currency
		if ($processRef->GetCurrency()!=$processStatus->GetCurrency()) return self::ERROR_DIFFERENT_CURRENCY;
		// Condition 4: all processes have to be in the same status and have to be in a specific status
		if ($processRef->GetCurrentStatus()!=$processStatus->GetCurrentStatus()) return self::ERROR_DIFFERENT_STATUS;
		if (!in_array($processRef->GetCurrentStatus(), WorkflowManager::GetStatusForProcessGroupCreation())) return self::ERROR_WRONG_STATUS;
		// Condition 5: responible FMS-Persons have to be the same
		$shopRef = $processRef->GetShop();
		$shop = $processStatus->GetShop();
		if ($shopRef==null || $shop==null) return self::ERROR_PROCESS_WITHOUT_SHOP;
		if ($shopRef->GetCPersonRS()==null || $shop->GetCPersonRS()==null || $shopRef->GetCPersonRS()->GetPKey()!=$shop->GetCPersonRS()->GetPKey()) return self::ERROR_DIFFERENT_FMS_PERSON;
		if ($shopRef->GetCPersonFmsLeader()==null || $shop->GetCPersonFmsLeader()==null || $shopRef->GetCPersonFmsLeader()->GetPKey()!=$shop->GetCPersonFmsLeader()->GetPKey()) return self::ERROR_DIFFERENT_FMSLEADER_PERSON;
		if ($shopRef->GetCPersonFmsAccountsdepartment()==null || $shop->GetCPersonFmsAccountsdepartment()==null || $shopRef->GetCPersonFmsAccountsdepartment()->GetPKey()!=$shop->GetCPersonFmsAccountsdepartment()->GetPKey()) return self::ERROR_DIFFERENT_FMSACCOUNTSDEPARTMENT_PERSON;
		// Condition 6: responible Customer-Persons have to be the same
		if ($shopRef->GetCPersonCustomer()==null || $shop->GetCPersonCustomer()==null || $shopRef->GetCPersonCustomer()->GetPKey()!=$shop->GetCPersonCustomer()->GetPKey()) return self::ERROR_DIFFERENT_CUSTOMER_PERSON;
		//if ($shopRef->GetCPersonCustomerAccounting()==null || $shop->GetCPersonCustomerAccounting()==null || $shopRef->GetCPersonCustomerAccounting()->GetPKey()!=$shop->GetCPersonCustomerAccounting()->GetPKey()) return self::ERROR_DIFFERENT_CUSTOMERACCOUNTING_PERSON;
		//if ($shopRef->GetCPersonCustomerSupervisor()==null || $shop->GetCPersonCustomerSupervisor()==null || $shopRef->GetCPersonCustomerSupervisor()->GetPKey()!=$shop->GetCPersonCustomerSupervisor()->GetPKey()) return self::ERROR_DIFFERENT_CUSTOMERSUPERVISOR_PERSON;
		// Condition 7: TODO: group of trustee have to be the same
		//$processRef->GetGroupTrusteeName();
		
		
		return true;
	}
	
	/**
	 * Fügt den übergebenen Prozess dieser Gruppe hinzu
	 * @param DBManager $db
	 * @param ProcessStatus $processStatus
	 * @return boolean|int
	 */
	public function AddProcessToGroup(DBManager $db, ProcessStatus $processStatus)
	{
		$returnValue = $this->IsProcessCompatible($db, $processStatus);
		if ($returnValue!==true) return $returnValue;
		$processStatus->SetProcessStatusGroup($this);
		if ($processStatus->Store($db)!==true)
		{
			// Änderung konnte nicht gespeichert werden
			return self::ERROR_STORE_TO_DB;
		}
		// check if process is allready in list...
		for ($a=0; $a<count($this->processStatusList); $a++)
		{
			if($this->processStatusList[$a]->GetPKey()==$processStatus->GetPKey()) return true;
		}
		// add process to list
		$this->processStatusList[] = $processStatus;
		return true;
	}
	
	/**
	 * Entfernt den übergebenen Prozess von dieser Gruppe
	 * @param DBManager $db
	 * @param ProcessStatus $processStatus
	 * @return boolean
	 */
	public function RemoveProcessFromGroup(DBManager $db, ProcessStatus $processStatus)
	{
		if ($processStatus->GetProcessStatusGroupId() != $this->GetPKey()) return false;
		$processStatus->SetProcessStatusGroup(null);
		if ($processStatus->Store($db)!==true)
		{
			return self::ERROR_STORE_TO_DB;
		}
		// remove process from list
		$newList = Array();
		for ($a=0; $a<count($this->processStatusList); $a++)
		{
			if($this->processStatusList[$a]->GetPKey()!=$processStatus->GetPKey())
			{
				$newList[] = $this->processStatusList[$a];
			}
		}
		$this->processStatusList = $newList;
		return true;
	}
	
	/**
	 * Set the selected ProcessStatus 
	 * @param ProcessStatus $processStatus
	 * @return boolean
	 */
	public function SetSelectedProcessStatus(ProcessStatus $processStatus=null)
	{
		if ($processStatus==null)
		{
			$this->selectedProcessStatus = null;
			return true;
		}
		for ($a=0; $a<count($this->processStatusList); $a++)
		{
			if ($this->processStatusList[$a]->GetPkey()==$processStatus->GetPKey())
			{
				$this->selectedProcessStatus = $this->processStatusList[$a];
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Return the selected ProcessStatus
	 * @return ProcessStatus
	 */
	public function GetSelectedProcessStatus()
	{
		return $this->selectedProcessStatus;
	}
	
	/**
	 * Setzt ob die Aufgabe geplant ist oder nicht
	 * @param bool $planned
	 * @return bool
	 */
	public function SetPlanned($planned)
	{
		$returnValue = parent::SetPlanned($planned);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_PLANNED;
		return $returnValue;
	}
	
	/**
	 * Gibt den Zeitpunkt an dem das Flag 'planned' zuletzt geändert wurde zurück
	 * @return int
	 */
	public function GetPlannedTime()
	{
		if (count($this->processStatusList)==0) return false;
		return $this->processStatusList[0]->GetPlannedTime();
	}
	
	/**
	 * Gibt zurück, ob die Aufgabe geplant ist oder nicht
	 * @return bool
	 */
	public function IsPlanned()
	{
		if (count($this->processStatusList)==0) return false;
		return $this->processStatusList[0]->IsPlanned();
	}
	
	/**
	 * Retrun the archive status
	 * @return int
	 */
	public function GetArchiveStatus()
	{
		$archiveStatus = Array();
		foreach ($this->processStatusList as $processStatus)
		{
			$status = $processStatus->GetArchiveStatus();
			if (!in_array($status, $archiveStatus)) $archiveStatus[]=$status;
		}
		if (count($archiveStatus)==1) return $archiveStatus[0];
		return -1;
	}
	
	/**
	 * Retrun the archive status
	 * @return int
	 */
	public function GetArchiveStatusName()
	{
		$status = $this->GetArchiveStatus();
		if ($status==-1) return "*";
		return $status;
	}
		
	/**
	 * Set the prio for all processes in this group
	 * @param int $prio 
	 * @return boolean 
	 */
	public function SetPrio($prio)
	{
		$returnValue = parent::SetPrio($prio);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_PRIO;
		return $returnValue;
	}
	
	/**
	 * Return the prio of the groups processes
	 * @return int
	 */
	public function GetPrio(DBManager $db)
	{
		foreach ($this->processStatusList as $processStatus)
		{
			$prio = $processStatus->GetPrio($db);
			if ($prio==Schedule::PRIO_HIGH) return Schedule::PRIO_HIGH;
		}
		return Schedule::PRIO_NORMAL;
	}
	
	/**
	 * Set the prio function of this schedule
	 * @param int $prioFunction 
	 * @return boolean 
	 */
	public function SetPrioFunction($prioFunction)
	{
		$returnValue = parent::SetPrioFunction($prioFunction);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_PRIOFUNCTION;
		return $returnValue;
	}
	
	/**
	 * Return the prio function of this schedule
	 * @return int
	 */
	public function GetPrioFunction()
	{
		foreach ($this->processStatusList as $processStatus)
		{
			$prioFunction = $processStatus->GetPrioFunction();
			if ($prioFunction!=Schedule::PRIO_FUNCTION_AUTO) return $prioFunction;
		}
		return Schedule::PRIO_FUNCTION_AUTO;
	}
	
	/**
	 * Setzt den JumpBackStatus
	 * @param int $jumpBackStatus
	 * @return bool
	 */
	public function SetJumpBackStatus($jumpBackStatus)
	{
		$returnValue = parent::SetJumpBackStatus($jumpBackStatus);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_JUMPBACKSTATUS;
		return $returnValue;
	}
	
	/**
	 * Setzt die Deadline
	 * @param int $deadline
	 * @return bool
	 */
	public function SetDeadline($deadline)
	{
		$returnValue = parent::SetDeadline($deadline);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_DEADLINE;
		return $returnValue;
	}
	
	/**
	 * Setzt den Zeitpunkt bis zu dem der Prozess zurückgestellt wird
	 * @param int $rueckstellungBis Zeitpunkt bis zu dem der Prozess zurückgestellt werden soll
	 * @return bool
	 */
	public function SetRueckstellungBis($rueckstellungBis)
	{
		$returnValue = parent::SetRueckstellungBis($rueckstellungBis);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_RUECKSTELLUNGBIS;
		return $returnValue;
	}
	
		/**
	 * Setzt die Begründung für die Rückstellung
	 * @param string $rueckstellungBegruendung
	 * @return bool
	 */
	public function SetRueckstellungBegruendung($rueckstellungBegruendung)
	{
		$returnValue = parent::SetRueckstellungBegruendung($rueckstellungBegruendung);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_RUECKSTELLUNGBEGRUENDUNG;
		return $returnValue;
	}
	
	/**
	 * Setzt das Abschlussdatum
	 * @param int $abschlussdatum
	 * @return bool
	 */
	public function SetAbschlussdatum($abschlussdatum)
	{
		$returnValue = parent::SetAbschlussdatum($abschlussdatum);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_ABSCHLUSSDATUM;
		return $returnValue;
	}
	
	/**
	 * Setzt das Zahlungsdatum
	 * @param int $zahlungsdatum
	 * @return boolean Success
	 */
	public function SetZahlungsdatum($zahlungsdatum)
	{
		$returnValue = parent::SetZahlungsdatum($zahlungsdatum);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_ZAHLUNGSDATUM;
		return $returnValue;
	}

	/**
	 * Setzt die Form
	 * @param int $form
	 * @return boolean Success
	 */
	public function SetForm($form)
	{
		$returnValue = parent::SetForm($form);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_FORM;
		return $returnValue;
	}

	/**
	 * Setzt die Stufen zu klärende Punkte
	 * @param int $stagesPointsToBeClarified
	 * @return boolean Success
	 */
	public function SetStagesPointsToBeClarified($stagesPointsToBeClarified)
	{
		$returnValue = parent::SetStagesPointsToBeClarified($stagesPointsToBeClarified);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_STAGE;
		return $returnValue;
	}

	/**
	 * Setzt das Aktenzeichen
	 * @param string $referenceNumber
	 * @return boolean Success
	 */
	public function SetReferenceNumber($referenceNumber)
	{
		$returnValue = parent::SetReferenceNumber($referenceNumber);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_REFERENCENUMBER;
		return $returnValue;
	}

	/**
	 * Setzt den Teilvorgang
	 * @param string $subProcedure
	 * @return boolean Success
	 */
	public function SetSubProcedure($subProcedure)
	{
		$returnValue = parent::SetSubProcedure($subProcedure);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_SUBPROCEDURE;
		return $returnValue;
	}

	/**
	 * Gibt den Vorgang zurück
	 * @return string
	 */
	public function GetProcedure(DBManager $db)
	{
		$verwalterGruppe = "";
		// check if process is allready in list...
		for ($a=0; $a<count($this->processStatusList); $a++)
		{
			$ta = $this->processStatusList[$a]->GetTeilabrechnung($db);
			if ($ta!=null)
			{
				$verwalter = $ta->GetVerwalter();
				if ($verwalter!=null)
				{
					$verwalterGruppeTemp = trim($verwalter->GetAddressGroupName());
					if ($verwalterGruppe == "") $verwalterGruppe = $verwalterGruppeTemp;
					if ($verwalterGruppeTemp!=$verwalterGruppe) $verwalterGruppe = "?";
				}
			}
		}
		$country = $this->GetCountryName();
		$company = $this->GetCompany();
		$companyName = $company!=null ? $company->GetGroup()->GetName() : "";
		$subProcedure = trim($this->GetSubProcedure());
		$returnValue = trim($verwalterGruppe." - ".$country." - ".$companyName);
		$returnValue = ($subProcedure!="" ? $returnValue." - ".$subProcedure : $returnValue);

		return $returnValue;
	}

	/**
	 * Setzt den Status von dem automatisch zurückgesprungen wurde
	 * @param int $autoJumpFromStatus
	 * @return bool
	 */
	public function SetAutoJumpFromStatus($autoJumpFromStatus)
	{
		$returnValue = parent::SetAutoJumpFromStatus($autoJumpFromStatus);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_AUTOJUMPFROMSTATUS;
		return $returnValue;
	}
	
	/**
	 * clear last status
	 * @return int
	 */
	public function ClearLastStatus()
	{
		parent::ClearLastStatus();
		$this->dirtFlag |= self::DIRTFLAG_CLEARLASTSTATUS;
	}
	
	/**
	 * Setzt den aktueller Status
	 * @param int $currentStatus
	 * @return bool
	 */
	public function SetCurrentStatus($currentStatus)
	{
		$returnValue = parent::SetCurrentStatus($currentStatus);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_SETCURRENTSTATUS;
		return $returnValue;
	}
	
	/**
	 * Gibt den aktueller Status zurück
	 * @return int
	 */
	public function GetCurrentStatus()
	{
		if (count($this->processStatusList)==0)	return -1;
		return $this->processStatusList[0]->GetCurrentStatus();
	}
	
	/**
	 * Gibt die Typen des Status der Prozesse dieser Gruppe zurück
	 * @param User $currentUser
	 * @param DBManager $db
	 * @return string
	 */
	public function GetCurrentStatusName(User $currentUser, DBManager $db)
	{
		$processNames = Array();
		foreach ($this->processStatusList as $processStatus)
		{
			$name = $processStatus->GetCurrentStatusName($currentUser, $db);
			if (!in_array($name, $processNames)) $processNames[]=$name;
		}
		if (count($processNames)==0) return "-";
		return implode(", ", $processNames);
	}
	
	/**
	 * Gibt zurück, ob bereits ein Widerspruch zu einem der Prozesse dieser Gruppe im System hochgeladen wurde (und somit der Status 'Widerspruch drucken/versenden/hochladen' durchlaufen wurde)
	 * @pararm DBManager $db
	 * @return boolean
	 */
	protected function IsWiderspruchUploaded(DBManager $db)
	{
		foreach ($this->processStatusList as $processStatus)
		{
			if ($processStatus->IsWiderspruchUploaded($db)) return true;
		}
		return false;
	}
	
	/**
	 * Gibt das zugehörige Abrechnungsjahr zurück
	 * @return Abrechnungsjahr
	 */
	public function GetAbrechnungsJahr()
	{
		// Eine Gruppe hat kein spezielles Abrechnungsjahr
		return null;
	}
	
	/**
	 * Gibt das zugehörige Abrechnungsjahr als String zurück
	 * @return string
	 */
	public function GetAbrechnungsJahrString()
	{
		$years = Array();
		foreach ($this->processStatusList as $processStatus)
		{
			$year = $processStatus->GetAbrechnungsJahrString();
			if (!in_array($year, $years)) $years[] = $year;
		}
		if (count($years)==0) return "";
		if (count($years)==1) return $years[0];
		sort($years);
		return $years[0]." - ".$years[count($years)-1];
	}
	
	/**
	 * Setzt das zugehörige Abrechnungsjahr
	 * @param DBManager $db
	 * @param AbrechnungsJahr $abrechnungsjahr
	 * @return bool
	 */
	public function SetAbrechnungsJahr(DBManager $db, $abrechnungsjahr)
	{
		// Eine Gruppe hat kein spezielles Abrechnungsjahr
		return false;
	}
	
	/**
	 * Gibt die aktuelle Teilabrechnung zurück
	 * @return Teilabrechnung
	 */
	public function GetCurrentTeilabrechnung()
	{
		// Eine Gruppe hat keine spezielle Teilabrechnung
		return null;
	}
	
	/**
	 * Setzt die aktuelle Teilabrechnung
	 * @param DBManager $db
	 * @param Teilabrechnung $currentTeilabrechnung
	 * @return bool
	 */
	public function SetCurrentTeilabrechnung($db, Teilabrechnung $currentTeilabrechnung, $skipSecurityCheck=false)
	{
		// Eine Gruppe hat keine spezielle Teilabrechnung
		return false;
	}	
	
	/**
	 * Gibt die zugehörige Teilabrechnung zurück
	 * @param DBManager $db
	 * @return Teilabrechnung
	 */
	public function GetTeilabrechnung(DBManager $db)
	{
		return $this->GetCurrentTeilabrechnung();
	}
	
	/**
	 * Gibt das Auftragsdatum zurück
	 * @return int 
	 */
	public function GetAuftragsdatumAbrechnung()
	{
		// if a process is selected return its AuftragsdatumAbrechnunng
		$selectedProcessStatus = $this->GetSelectedProcessStatus();
		if ($selectedProcessStatus!=null)return $selectedProcessStatus->GetAuftragsdatumAbrechnung();
		// if no process is selected return the recent AuftragsdatumAbrechnunng of all processes
		if (count($this->processStatusList)==0) return 0;
		$returnValue = $this->processStatusList[0]->GetAuftragsdatumAbrechnung();
		foreach ($this->processStatusList as $processStatus)
		{
			$abrechnungsdatum = $processStatus->GetAuftragsdatumAbrechnung();
			if ($abrechnungsdatum>$returnValue) $returnValue=$abrechnungsdatum;
		}
		return $returnValue;
	}
	
	/**
	 * Gibt den Ansprechpartner zurück
	 * @return AddressBase
	 */
	public function GetAnsprechpartner()
	{
		if (count($this->processStatusList)==0) return "";
		return $this->processStatusList[0]->GetAnsprechpartner();
	}
	
	/**
	 * Return the Name of trustee group (of the first process in group)
	 * @param DBManager $db
	 * @return string
	 */
	public function GetGroupTrusteeName(DBManager $db)
	{
		if (count($this->processStatusList)==0) return "";
		return $this->processStatusList[0]->GetGroupTrusteeName($db);
	}
	
	/**
	 * Gibt das (älteste) Beuaftragungsdatum der Untergeorndeten TAs zurück
	 */
	public function GetBeauftragungsdatum(DBManager $db)
	{
		$returnValue = time();
		foreach ($this->processStatusList as $processStatus)
		{
			$beauftragungsdatum = $processStatus->GetBeauftragungsdatum($db);
			if ($beauftragungsdatum<$returnValue) $returnValue=$beauftragungsdatum;
		}
		return $returnValue;
	}
	
	/**
	 * Gibt den zugehörigen Vertrag zurück
	 * @return Contract
	 */
	public function GetContract()
	{
		// Eine Gruppe hat keinen speziellen Vertrag
		return null;
	}
		
	/**
	 * Return the current currency (from first process in group)
	 * @return string 
	 */
	public function GetCurrency()
	{
		if (count($this->processStatusList)==0) return "";
		return $this->processStatusList[0]->GetCurrency();
	}
	
	/**
	 * Return the current currency
	 * @return string 
	 */
	public function GetCountryName()
	{
		if (count($this->processStatusList)==0) return "";
		return $this->processStatusList[0]->GetCountryName();
	}
	
	/**
	 * Gibt den zugehörigen Laden zurück
	 * @return CShop
	 */
	public function GetShop()
	{
		// Eine Gruppe hat keinen speziellen Laden
		return null;
	}
	
	/**
	 * Gibt den zugehörigen Laden zurück
	 * @return CShop
	 */
	public function GetShopName()
	{
		$cGroup = $this->GetGroup();
		if ($cGroup==null) return "";
		return $cGroup->GetName();
	}
	
	/**
	 * Gibt die interne Ladennummer zurück
	 * @return string
	 */
	public function GetInternalShopNo()
	{
		$internalShopNumbers = Array();
		foreach ($this->processStatusList as $processStatus)
		{
			$tempShopNumber = $processStatus->GetInternalShopNo();
			if (trim($tempShopNumber)!="") $internalShopNumbers[$tempShopNumber] = true;
		}
		$numbers = array_keys($internalShopNumbers);
		if (count($numbers)==0) return "";
		$returnValue = "";
		$maxNumbersToUse = 2;
		for ($a=0; $a<(count($numbers)<$maxNumbersToUse ? count($numbers) : $maxNumbersToUse); $a++)
		{
			if ($returnValue!="") $returnValue.=", ";
			$returnValue.=$numbers[$a];
		}
		if (count($numbers)>$maxNumbersToUse) $returnValue.="...";
		return $returnValue;
	}
	
	/**
	 * Gibt die zugehörige Location zurück
	 * @return CLocation
	 */
	public function GetLocation()
	{
		// Eine Gruppe hat keinen speziellen Standort
		return null;
	}

	/**
	 * Gibt die zugehörige Location zurück
	 * @return CLocation
	 */
	public function GetLocationName()
	{
		return $this->GetName();
	}
	
	/**
	 * Return the company
	 * @return CCompany
	 */
	public function GetCompany()
	{
		if (count($this->processStatusList)==0) return "";
		return $this->processStatusList[0]->GetCompany();
	}
	
	/**
	 * Return the group (from first process in group)
	 * @return CGroup
	 */
	public function GetGroup()
	{
		if (count($this->processStatusList)==0) return "";
		return $this->processStatusList[0]->GetGroup();
	}
	
	/**
	 * Gibt den aktuellen Widerspruch
	 * @return Widerspruch
	 */
	public function GetWiderspruch($db)
	{
		if ($this->GetPKey()==-1) return null;
		$data = $db->SelectAssoc("SELECT * FROM ".Widerspruch::TABLE_NAME." WHERE processStatusGroup_rel=".$this->GetPKey()." ORDER BY widerspruchsNummer DESC LIMIT 0,1");
		if (count($data)==0)
		{
			// create new WS
			$object = new Widerspruch($db);
			$object->SetProcessStatusGroup($this);
			if ($object->Store($db)===true) return $object;
			// return null pointer on error
			return null;
		}
		$object = new Widerspruch($db);
		if ($object->LoadFromArray($data[0], $db)!==true) return null;
		return $object;
	}
	
	/**
	 * Gibt alle Widersprüche zurück
	 * @return Widerspruch[]
	 */
	public function GetWidersprueche($db)
	{
		if ($this->GetPKey()==-1) return Array();
		$data = $db->SelectAssoc("SELECT * FROM ".Widerspruch::TABLE_NAME." WHERE processStatusGroup_rel=".$this->GetPKey()." ORDER BY widerspruchsNummer");
		$objects = Array();
		for ($a=0; $a<count($data); $a++)
		{
			$object = new Widerspruch($db);
			if ($object->LoadFromArray($data[$a], $db)===true) $objects[] = $object;
		}
		return $objects;
	}
	
		
	/**
	 * Gibt die Anzahl der Widersprüche zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetWiderspruchCount(DBManager $db)
	{
		if ($this->pkey==-1) return 0;
		$data = $db->SelectAssoc("SELECT count(pkey) as numEntries FROM ".Widerspruch::TABLE_NAME." WHERE processStatusGroup_rel=".$this->pkey);
		return (int)$data[0]["numEntries"];
	}
	
	/**
	 * Ordnet dem Widerspruch diese Teilabrechnung zu
	 * @param DBManager $db
	 * @param Widerspruch $widerspruch
	 * @return bool
	 */
	public function AddWiderspruch(DBManager $db, Widerspruch $widerspruch)
	{
		return $widerspruch->SetProcessStatusGroup($this);
	}
	
	/**
	 * Gibt die Summe des "Betrag Kunden" über alle TAs dieser Prozessgruppe hinweg zurück
	 * @return float
	 */
	public function GetSummeBetragKunde($db)
	{
		$summe=0.0;
		foreach ($this->processStatusList as $processStatus)
		{
			$summe+=$processStatus->GetSummeBetragKunde($db);
		}
		return $summe;
	}
		
	/**
	 * Gibt die Summe des "Betrag Kunden je qm" über alle TAs dieser Prozessgruppe hinweg zurück
	 * @return float
	 */
	public function GetSummeBetragKundeQM($db)
	{
		$summe=0.0;
		foreach ($this->processStatusList as $processStatus)
		{
			$summe+=$processStatus->GetSummeBetragKundeQM($db);
		}
		return $summe;
	}
	
	/**
	 * Gibt die "Summe Betrag TAPs lt. Abrechnung" über alle TAs dieser Prozessgruppe hinweg zurück
	 * @return float
	 */
	public function GetSummeTAPs($db)
	{
		$summe=0.0;
		foreach ($this->processStatusList as $processStatus)
		{
			$summe+=$processStatus->GetSummeTAPs($db);
		}
		return $summe;
	}
	
	/**
	 * Gibt die Widerspruchssumme des aktuellen Widerspruchs zurück (grün + gelb + grau)
	 * @param DBManager $db
	 * @param int $subtype	0: Gesamtwiderspruchssumme
	 *						1: WS-Summe realisiert
	 *						2: WS-Summe nicht realisiert
	 *						3: Ersteinsparung
	 *						4: Folgeeinsparung
	 * @return float|bool Widerspruchssumme oder false wenn noch kein WS hinterlegt
	 */
	public function GetWiderspruchssumme($db, $subtype=0)
	{
		$summe=0.0;
		foreach ($this->processStatusList as $processStatus)
		{
			$summe+=$processStatus->GetWiderspruchssumme($db, $subtype);
		}
		return $summe;
	}
	
	/**
	 * Gibt die Kürzungsbeträge und die Widerspruchssumme des aktuellen Widerspruchs zurück
	 * @return float|bool Widerspruchssumme oder false wenn noch kein WS hinterlegt
	 */
	public function GetKuerzungsbetraege($db)
	{
		$summe=0.0;
		foreach ($this->processStatusList as $processStatus)
		{
			$summe+=$processStatus->GetKuerzungsbetraege($db);
		}
		return $summe;
	}
	
	/**
	 * Return if any prozess status from this group is active for the specified user
	 * @param User $userToCheckFor
	 * @param DBManager $db
	 * @return boolean
	 */
	public function IsActiveProzess(User $userToCheckFor, DBManager $db)
	{
		foreach ($this->processStatusList as $processStatus)
		{
			if ($processStatus->IsActiveProzess($userToCheckFor, $db)) return true;
		}
		return false;
	}
	
	/**
	 * Return the current responsible user (for the first prcocess in this group)
	 * @param DBManager $db
	 * @return User 
	 */
	public function GetCurrentResponsibleRSUser(DBManager $db)
	{
		if (count($this->processStatusList)==0) return null;
		return $this->processStatusList[0]->GetCurrentResponsibleRSUser($db);
	}
	
	/**
	 * Return the responsible user for this task
	 * @return User 
	 */
	public function GetResponsibleRSUser()
	{
		if (count($this->processStatusList)==0) return null;
		return $this->processStatusList[0]->GetResponsibleRSUser();
	}
	
	/**
	 * Return the responsible FMS leader for this task
	 * @return User 
	 */
	public function GetCPersonFmsLeader()
	{
		if (count($this->processStatusList)==0) return null;
		return $this->processStatusList[0]->GetCPersonFmsLeader();
	}
	
	/**
	 * Return the responsible user for this task
	 * @return User 
	 */
	public function GetResponsibleCustomer()
	{
		if (count($this->processStatusList)==0) return null;
		return $this->processStatusList[0]->GetResponsibleCustomer();
	}
		
	/**
	 * Return the responsible accounting customer user for this task
	 * @return User
	 */
	public function GetResponsibleCustomerAccounting()
	{
		if (count($this->processStatusList)==0) return null;
		return $this->processStatusList[0]->GetResponsibleCustomerAccounting();
	}
	
	/**
	 * Return all files linked to the prozess
	 * @param DBManager $db 
	 * @return array
	 */
	public function GetAllProcessFiles(DBManager $db)
	{
		// get all files from the processgroup 
		$files = parent::GetAllProcessFiles($db);
		// add all files from the processes
		foreach ($this->processStatusList as $processStatus)
		{
			$files = array_merge($files, $processStatus->GetAllProcessFiles($db));
		}
		return $files;
	}

	/**
	 * Gibt ZuweisungUser zurück
	 * @return User
	 */
	public function GetZuweisungUser()
	{
		if ($this->GetProcessCount()==0) return null;
		return $this->processStatusList[0]->GetZuweisungUser();
	}
	
	/**
	 * Setzt ZuweisungUser
	 * @param User $zuweisungUser
	 * @return bool
	 */
	public function SetZuweisungUser(User $zuweisungUser=null)
	{
		$returnValue = parent::SetZuweisungUser($zuweisungUser);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_ASSIGNED_USER;
		return $returnValue;
	}

	/**
	 * Setzt den Abschlußzeitpunkt
	 * @param int $finished
	 * @return bool
	 */
	public function SetFinished($finished)
	{
		$returnValue = parent::SetFinished($finished);
		if ($returnValue) $this->dirtFlag |= self::DIRTFLAG_FINISHED;
		return $returnValue;
	}
	
	/**
	 * Gibt die Dauer der Prüfung zurück
	 * @return int
	 */
	public function GetDuration(DBManager $db)
	{
		$creationTime = 0;
		foreach ($this->processStatusList as $processStatus)
		{
			if ($creationTime<$processStatus->GetDuration($db)) $creationTime=$processStatus->GetDuration($db);
		}
		return $creationTime;
	}
		
	/**
	 * Return the process path
	 * @return string[]
	 */
	public function GetProzessPath()
	{
		// check if a filtered list is available and return this if present
		$returnValue = $this->GetFilteredProzessPath();
		if (count($returnValue)>0) return $returnValue;
		
		// build a complete list with group and all processes...
		// add group path to list
		$path = $this->GetGroupName().' | '.$this->GetName().' | '.$this->GetAbrechnungsJahrString();
		$path.=' ('.WorkflowManager::GetProcessStatusId($this).')';
		$returnValue[] = Array('path' => $path, 'process_id' => WorkflowManager::GetProcessStatusId($this), 'obj' => $this);
		
		// add path of all processes to list
		$processList = $this->GetProcess();
		foreach ($processList as $prozess)
		{
			$pathList = $prozess->GetProzessPath();
			foreach ($pathList as $path)
			{
				$returnValue[] = $path;
			}
		}
		return $returnValue;
	}
	
	/**
	 * Return the filtered process path. This is for status in which a switch between prozesses shold be avoided
	 * @return string[]
	 */
	private function GetFilteredProzessPath()
	{
		$selectedProcessStatus = $this->GetSelectedProcessStatus();
		if ($selectedProcessStatus==null) return Array();
		$currentStatusConfig = WorkflowManager::GetProzessStatusForStatusID($this->GetCurrentStatus());
		if ($currentStatusConfig['forbitProcessSwitching']!==true) return Array();
		// lookup selected process in list
		$returnValue = Array();
		$processList = $this->GetProcess();
		foreach ($processList as $prozess)
		{
			if ($prozess->GetPKey()==$selectedProcessStatus->GetPKey())
			{
				$pathList = $prozess->GetProzessPath();
				foreach ($pathList as $path)
				{
					$returnValue[] = $path;
				}
			}
		}
		return $returnValue;
	}
	
	/**
	 * Gibt die frühste Deadline zurück
	 * @return int
	 */
	public function GetDeadline()
	{
		$deadline = 0;
		if (count($this->processStatusList)==0) return 0;
		foreach ($this->processStatusList as $processStatus)
		{
			if ($deadline<$processStatus->GetDeadline()) $deadline=$processStatus->GetDeadline();
		}
		return $deadline;
	}
	
	/**
	 * Return a human readable name for the requested attribute
	 * @param LanguageManager $languageManager
	 * @param string $attributeName
	 * @return string
	 */
	static public function GetAttributeName(ExtendedLanguageManager $languageManager, $attributeName)
	{
		switch($attributeName)
		{
			case "id":
				return $languageManager->GetString('PROCESS', 'GROUP_ID');
			case "name":
				return $languageManager->GetString('PROCESS', 'GROUP_NAME');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}
	
}
?>