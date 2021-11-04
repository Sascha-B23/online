<?php
/**
 * Diese Klasse repräsentiert ein Status des Prozess-Workflows
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class ProcessStatus extends WorkflowStatus implements DependencyFileDescription, AttributeNameMaper
{
	/**
	 * Datenbankname und Spalten
	 * @var string
	 */
	const TABLE_NAME = "processStatus";
	
	/**
	 * File relation manager
	 * @var FileRelationManager 
	 */
	protected $fileRelationManager = null;
	
	/**
	 * CommentRelationManager
	 * @var CommentRelationManager 
	 */
	protected $commentRelationManager = null;
	
	/**
	 * Abrechnungsjahr des Status
	 * @var Abrechnungsjahr
	 */
	protected $abrechnungsjahr = null;
	
	/**
	 * Zeitpunkt bis zu dem der Prozess zurückgestellt ist
	 * @var int
	 */
	protected $rueckstellungBis = 0;

	/**
	 * Begründung für die Rückstellung des Prozesses
	 * @var string
	 */
	protected $rueckstellungBegruendung = "";

	/**
	 * Aktuell bearbeitete Teilabrechnung des Status
	 * @var Teilabrechnung
	 */
	protected $currentTeilabrechnung = null;
	
	/**
	 * CalendarEntry of the current 'Telefontermin'
	 * @var CalendarEntry
	 */
	protected $telefonterminCalendarEntry = null;
	
	/**
	 * Datum des aktuellen Telefontermins
	 * @var int
	 */
	protected $telefontermin = 0;
	
	/**
	 * Datum des aktuellen Telefontermins (Ende)
	 * @var int
	 */
	protected $telefonterminEnde = 0;

	/**
	 * Ansprechpartner des Telefontermins
	 * @var AddressBase 
	 */
	protected $telefonterminAnsprechpartner = null;

	/**
	 * Flag that indicates if a recursive delete call is processed 
	 * @var boolean 
	 */
	protected $recursiveDelete = false;
	
	/**
	 * Welcher Gruppe ist dieses Objekt untergeordnet (0=keiner)
	 * @var int 
	 */
	private $processStatusGroup = 0;
	
	/**
	 * Abschlussdatum
	 * @var int
	 */
	private $abschlussdatum = 0;
	
	/**
	 * Zahlungsdatum
	 * @var int
	 */
	private $zahlungsdatum = 0;

    /**
     * Process related comment from the customer
     * @var string
     */
    private $customerComment = "";

    /**
     * File upload related comment from the customer
     * @var string
     */
    private $customerFileUploadComment = "";

	/**
	 * Form
	 * @var int
	 */
    private $form = self::FORM_EXTRAJUDICIAL;
    const FORM_EXTRAJUDICIAL = 0; // außergerichtlich
	const FORM_JUDICIAL = 1; 	  // gerichtlich

	/**
	 * Stufen zu klärende Punkte
	 * @var int
	 */
    private $stagesPointsToBeClarified = self::STAGE_UNKNOWN;
    const STAGE_UNKNOWN = 0;
	const STAGE_PROVIDE = 1;  // 1_Informationen beibringen
	const STAGE_EVALUATE = 2; // 2_Informationen auswerten

	/**
	 * Aktenzeichen
	 * @var string
	 */
    private $referenceNumber = "";

	/**
	 * Teilvorgang
	 * @var string
	 */
	private $subProcedure = "";

	/**
	 * Konstruktor
	 * @param DBManager $db
	 * @param DBConfig $dbConfig
	 */
	public function ProcessStatus(DBManager $db, DBConfig $dbConfig=null)
	{	
		$configTemp = new DBConfig();
		$configTemp->tableName = self::TABLE_NAME;
		$configTemp->rowName = Array("abrechnungsjahr", "rueckstellungBis", "rueckstellungBegruendung", "currentTeilabrechnung", "telefonterminCalendarEntry_rel", "telefontermin", "telefonterminEnde", "telefonterminAnsprechpartner", "telefonterminAnsprechpartnerTyp", "abschlussdatum", "zahlungsdatum", "customerComment", "customerFileUploadComment", "processStatusGroup_rel", "form", "stagesPointsToBeClarified", "referenceNumber", "subProcedure");
		$configTemp->rowParam = Array("BIGINT", 		"BIGINT", 			"LONGTEXT", 				"BIGINT", 					"BIGINT", 						"BIGINT", 		"BIGINT", 			"BIGINT", 							"INT", 							"BIGINT", 			"BIGINT", 		"LONGTEXT", 		"LONGTEXT", 				"BIGINT", 				"BIGINT", "BIGINT", 					"LONGTEXT", 	"LONGTEXT");
		$configTemp->rowIndex = Array("abrechnungsjahr", "processStatusGroup_rel", "currentTeilabrechnung");
		
		if ($dbConfig!=null)
		{
			$dbConfig->Append($configTemp);
		}
		else
		{
			$dbConfig = $configTemp;
		}
		parent::__construct($db, $dbConfig);
		$this->fileRelationManager = new FileRelationManager($db, $this);
		$this->commentRelationManager = new CommentRelationManager($db, $this);
	}	
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *						-1 		Kein Abrechnungsjahr angegeben
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// process status without 'Abrechnungsjahr' is not allowed 
		if (get_class($this)=='ProcessStatus' && ($this->abrechnungsjahr==null || $this->abrechnungsjahr->GetPKey()==-1)) return -1;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "abrechnungsjahr";
		$rowData[]= $this->abrechnungsjahr==null ? -1 : $this->abrechnungsjahr->GetPKey();
		$rowName[]= "rueckstellungBis";
		$rowData[]= $this->rueckstellungBis;
		$rowName[]= "rueckstellungBegruendung";
		$rowData[]= $this->rueckstellungBegruendung;
		$rowName[]= "currentTeilabrechnung";
		$rowData[]= $this->currentTeilabrechnung==null ? -1 : $this->currentTeilabrechnung->GetPKey();
		$rowName[]= "telefonterminCalendarEntry_rel";
		$rowData[]= $this->telefonterminCalendarEntry==null ? -1 : $this->telefonterminCalendarEntry->GetPKey();
		$rowName[]= "telefontermin";
		$rowData[]= $this->telefontermin;
		$rowName[]= "telefonterminEnde";
		$rowData[]= $this->telefonterminEnde;
		$rowName[]= "telefonterminAnsprechpartner";
		$rowData[]= $this->telefonterminAnsprechpartner==null ? -1 : $this->telefonterminAnsprechpartner->GetPKey();
		$rowName[]= "telefonterminAnsprechpartnerTyp";
		$rowData[]= $this->telefonterminAnsprechpartner==null ? AddressBase::AM_CLASS_UNKNOWN : $this->telefonterminAnsprechpartner->GetClassType();
		$rowName[]= "abschlussdatum";
		$rowData[]= $this->abschlussdatum;
		$rowName[]= "zahlungsdatum";
		$rowData[]= $this->zahlungsdatum;
        $rowName[]= "customerComment";
        $rowData[]= $this->customerComment;
        $rowName[]= "customerFileUploadComment";
        $rowData[]= $this->customerFileUploadComment;
		$rowName[]= "processStatusGroup_rel";
		$rowData[]= $this->processStatusGroup;
		$rowName[]= "form";
		$rowData[]= $this->form;
		$rowName[]= "stagesPointsToBeClarified";
		$rowData[]= $this->stagesPointsToBeClarified;
		$rowName[]= "referenceNumber";
		$rowData[]= $this->referenceNumber;
		$rowName[]= "subProcedure";
		$rowData[]= $this->subProcedure;
		return WorkflowStatus::BuildDBArray($db, $rowName, $rowData);
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array $data Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		if ($data['abrechnungsjahr']!=-1)
		{
			$this->abrechnungsjahr = new AbrechnungsJahr($db);
			if ($this->abrechnungsjahr->Load($data['abrechnungsjahr'], $db)!==true) $this->abrechnungsjahr = null;
		}
		else
		{
			$this->abrechnungsjahr = null;
		}
		$this->rueckstellungBis = $data['rueckstellungBis'];
		$this->rueckstellungBegruendung = $data['rueckstellungBegruendung'];
		if ($data['currentTeilabrechnung']!=-1)
		{
			$taData = $db->SelectAssoc("SELECT * FROM ".Teilabrechnung::TABLE_NAME." WHERE pkey=".(int)$data['currentTeilabrechnung']);
			if (count($taData)>0)
			{
				$this->currentTeilabrechnung = new Teilabrechnung($db);
				if ($data['abrechnungsjahr']==$taData[0]['abrechnungsJahr'] && $this->abrechnungsjahr!=null) $taData[0]['abrechnungsJahr'] = $this->abrechnungsjahr;
				if ($this->currentTeilabrechnung->LoadFromArray($taData[0], $db)!==true) $this->currentTeilabrechnung = null;
			}
		}
		else
		{
			$this->currentTeilabrechnung = null;
		}
		
		
		if ($data['telefonterminCalendarEntry_rel']!=-1)
		{
			$this->telefonterminCalendarEntry = CalendarManager::GetCalendarEntryById($db, (int)$data['telefonterminCalendarEntry_rel']);
		}
		else
		{
			$this->telefonterminCalendarEntry = null;
		}
		
		$this->telefontermin = $data['telefontermin'];
		$this->telefonterminEnde = $data['telefonterminEnde'];
		if( $data['telefonterminAnsprechpartner']!=-1 )
		{
			$this->telefonterminAnsprechpartner = AddressManager::GetAddressElementByPkeyAndType($db, (int)$data['telefonterminAnsprechpartnerTyp'], $data['telefonterminAnsprechpartner']);
		}
		else
		{
			$this->telefonterminAnsprechpartner = null;
		}
		$this->abschlussdatum = (int)$data['abschlussdatum'];
		$this->zahlungsdatum = (int)$data['zahlungsdatum'];
        $this->customerComment = $data['customerComment'];
        $this->customerFileUploadComment = $data['customerFileUploadComment'];
		$this->processStatusGroup = $data['processStatusGroup_rel'];

		$this->form = (int)$data['form'];
		$this->stagesPointsToBeClarified = (int)$data['stagesPointsToBeClarified'];
		$this->referenceNumber = $data['referenceNumber'];
		$this->subProcedure = $data['subProcedure'];


		return WorkflowStatus::BuildFromDBArray($db, $data);
	}

	/**
	 * Prüft, ob der übergebene Benutzer berechtigt ist, dieses Objekt zu verarbeiten
	 * @param User $user
	 * @param DBManager $db
	 * @return bool
	 */
	public function HasUserAccess(User $user, DBManager $db)
	{
		$group = $this->GetGroup();
		return ($group==null ? false : $group->HasUserAccess($user, $db) );
	}

	/**
	 * Setzt die Gruppe
	 * @param ProcessStatusGroup $processStatusGroup
	 * @return boolean
	 */
	public function SetProcessStatusGroup(ProcessStatusGroup $processStatusGroup=null)
	{
		$this->processStatusGroup = ($processStatusGroup==null ? 0 : $processStatusGroup->GetPKey());
		return true;
	}
	
	/**
	 * Return the processgroup id
	 * @return int
	 */
	public function GetProcessStatusGroupId()
	{
		return $this->processStatusGroup;
	}

	/**
	 * Return if the process is attached to a group
	 * @return boolean
	 */
	public function IsAttachedToGroup()
	{
		return ($this->processStatusGroup==0 ? false : true);
	}
	
	/**
	 * Return the processgroup
	 * @param DBManager $db
	 * @return ProcessStatusGroup
	 */
	public function GetProcessStatusGroup(DBManager $db)
	{
		if ((int)$this->processStatusGroup==0) return null;
		$processStatusGroup = new ProcessStatusGroup($db);
		if ($processStatusGroup->Load((int)$this->processStatusGroup, $db)===true) return $processStatusGroup;
		return null;
	}

	/**
	 * Return Description
	 */
	public function GetDependencyFileDescription()
	{
		$abrechnungsJahr = $this->GetAbrechnungsJahr();
		return ($abrechnungsJahr==null ? "?" : $abrechnungsJahr->GetDependencyFileDescription()).DependencyFileDescription::SEPERATOR."Prozess #".$this->GetPKey();
	}
	
	/**
	 * Wird vor dem Löschen des Objekts aus der DB aufgerufen
	 * @param DBManager  $db			Datenbank Objekt
	 * @return bool					Erfolg (wenn false zurückgegeben wird, wird das Objekt nicht aus der DB gelöscht!)
	 * @access protected
	 */
	protected function OnDeleteMe(&$db)
	{
		// A prozess only can be deleteted through the function DeleteRecursive
		if (!$this->recursiveDelete) return false;
		return true;
	}
	
	/**
	 * Delete this and all contained objects
	 * @param DBManager $db
	 * @param User $user
     * @return boolean
	 */
	public function DeleteRecursive(DBManager $db, User $user)
	{
		if ($user->GetGroupBasetype($db) < UM_GROUP_BASETYPE_ADMINISTRATOR) return false;
		LoggingManager::GetInstance()->Log(new LoggingProcessStatus(LoggingProcessStatus::TYPE_DELETE, $this->GetCurrentStatusName($user, $db), "", serialize($this->GetDependencyFileDescription())));
		$this->recursiveDelete = true;
		// delete all comments...
		$this->commentRelationManager->RemoveAllComments($db);
		// delete all files...
		$files = $this->fileRelationManager->GetFiles($db);
		for ($a=0; $a<count($files); $a++)
		{
			$this->RemoveFile($db, $files[$a]);
		}
		// delete 'Abrechnungsjahr'...
		$year = $this->GetAbrechnungsJahr();
		if ($year!=null)
		{
			$year->DeleteRecursive($db, $user);
		}
		$this->DeleteMe($db);
		return true;
	}
	
	/**
	 * Setzt den Zeitpunkt bis zu dem der Prozess zurückgestellt wird
	 * @param int $rueckstellungBis Zeitpunkt bis zu dem der Prozess zurückgestellt werden soll
	 * @return bool
	 */
	public function SetRueckstellungBis($rueckstellungBis)
	{
		if( !is_int($rueckstellungBis) )return false;
		$this->rueckstellungBis=$rueckstellungBis;
		return true;
	}
	
	/**
	 * Gibt den Zeitpunkt zurück bis zu dem der Prozess zurückgestellt ist 
	 * @return int
	 */
	public function GetRueckstellungBis()
	{
		return $this->rueckstellungBis;
	}
	
	/**
	 * Setzt die Begründung für die Rückstellung
	 * @param string $rueckstellungBegruendung
	 * @return bool
	 */
	public function SetRueckstellungBegruendung($rueckstellungBegruendung)
	{
		$this->rueckstellungBegruendung=$rueckstellungBegruendung;
		return true;
	}
	
	/**
	 * Gibt die Begründung für die Rückstellung zurück
	 * @return string
	 */
	public function GetRueckstellungBegruendung()
	{
		return $this->rueckstellungBegruendung;
	}

    /**
     * @return string
     */
    public function GetCustomerComment()
    {
        return $this->customerComment;
    }

    /**
     * @param string $customerComment
     */
    public function SetCustomerComment($customerComment)
    {
        $this->customerComment = $customerComment;
    }

    /**
     * @return string
     */
    public function GetCustomerFileUploadComment()
    {
        return $this->customerFileUploadComment;
    }

    /**
     * @param string $customerFileUploadComment
     */
    public function SetCustomerFileUploadComment($customerFileUploadComment)
    {
        $this->customerFileUploadComment = $customerFileUploadComment;
    }

	/**
	 * Update the calendar entry for the 'Telefontermin'
	 * @param DBManager $db
	 * @param AppointmentManager $appointmentManager
	 * @return boolean
	 */
	public function UpdateTelefonterminCalendarEntry(DBManager $db, AppointmentManager $appointmentManager)
	{
		if ($this->telefonterminCalendarEntry==null)
		{
			if ($this->GetTelefontermin()!=0 && $this->GetTelefonterminEnde()!=0 && $this->GetTelefonterminAnsprechpartner()!=null)
			{
				$this->telefonterminCalendarEntry = new CalendarEntry($db);
				$this->telefonterminCalendarEntry->SetRefernceId(WorkflowManager::GetProcessStatusId($this));
				$this->telefonterminCalendarEntry->SetStart($this->GetTelefontermin());
				$this->telefonterminCalendarEntry->SetEnd($this->GetTelefonterminEnde());
				$this->telefonterminCalendarEntry->SetUser($this->GetResponsibleRSUser());
				$this->telefonterminCalendarEntry->SetSubject($this->GetProzessPath()[0]['path']);
				if ($this->telefonterminCalendarEntry->Store($db)===true)
				{
					$db->UpdateByPkey($this->GetTableName(), Array('telefonterminCalendarEntry_rel'), Array($this->telefonterminCalendarEntry->GetPKey()), $this->GetPKey());
					return $this->telefonterminCalendarEntry->SendAppointement($db, $appointmentManager);
				}
				return false;
			}
		}
		else
		{
			if ($this->GetTelefontermin()==0 && $this->GetTelefonterminEnde()==0 && $this->GetTelefonterminAnsprechpartner()==null)
			{
				// Removed
				if ($this->telefonterminCalendarEntry->GetStart()>time())
				{
					// send cancel notification if date is in feature
					$this->telefonterminCalendarEntry->CancelAppointement($db, $appointmentManager);
				}
				// delete entry
				if ($this->telefonterminCalendarEntry->DeleteMe($db)===true)
				{
					$this->telefonterminCalendarEntry = null;
					$db->UpdateByPkey($this->GetTableName(), Array('telefonterminCalendarEntry_rel'), Array(-1), $this->GetPKey());
				}
			}
			else
			{
				// Changed
				if ($this->GetTelefontermin()!=$this->telefonterminCalendarEntry->GetStart() || 
					$this->GetTelefonterminEnde()!=$this->telefonterminCalendarEntry->GetEnd() || 
					$this->GetResponsibleRSUser()->GetPKey()!=$this->telefonterminCalendarEntry->GetUser() ||
					$this->GetProzessPath()[0]['path'] != $this->telefonterminCalendarEntry->GetSubject())
				{
					$this->telefonterminCalendarEntry->SetStart($this->GetTelefontermin(), false);
					$this->telefonterminCalendarEntry->SetEnd($this->GetTelefonterminEnde(), false);
					$this->telefonterminCalendarEntry->SetUser($this->GetResponsibleRSUser());
					$this->telefonterminCalendarEntry->SetSubject($this->GetProzessPath()[0]['path']);
					if ($this->telefonterminCalendarEntry->Store($db)===true)
					{
						return $this->telefonterminCalendarEntry->SendAppointement($db, $appointmentManager);
					}
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * Updates the 'Telefontermin' from the passed calendar entry
	 * @param DBManager $db
	 * @param CalendarEntry $calendarEntry
	 * @return boolean
	 */
	public function UpdateTelefonterminByCalendarEntry(DBManager $db, CalendarEntry $calendarEntry)
	{
		/*echo "-".$this->GetPKey()."-<br />";
		echo date('d.m.Y H:i', $calendarEntry->GetStart())."<br />";
		echo date('d.m.Y H:i', $calendarEntry->GetEnd())."<br />";*/
		$this->SetTelefontermin($calendarEntry->GetStart());
		$this->SetTelefonterminEnde($calendarEntry->GetEnd());
		return $db->UpdateByPkey($this->GetTableName(), Array("telefontermin", "telefonterminEnde"), Array($this->GetTelefontermin(), $this->GetTelefonterminEnde()), $this->GetPKey());
	}
	
	/**
	 * Setzt das Datum und die Uhrzeit des Telefontermins
	 * @param int $telefontermin
	 * @return bool
	 */
	public function SetTelefontermin($telefontermin)
	{
		$this->telefontermin=$telefontermin;
		return true;
	}
	
	/**
	 * Gibt das Datum und die Uhrzeit des Telefontermins zurück
	 * @return int
	 */
	public function GetTelefontermin()
	{
		return $this->telefontermin;
	}
	
	/**
	 * Setzt das Datum und die Uhrzeit des Telefontermins
	 * @param int $telefonterminende
	 * @return bool
	 */
	public function SetTelefonterminEnde($telefonterminende)
	{
		if (!is_int($telefonterminende)) return false;
		$this->telefonterminEnde=$telefonterminende;
		return true;
	}
	
	/**
	 * Gibt das Datum und die Uhrzeit des Telefontermins zurück
	 * @return int
	 */
	public function GetTelefonterminEnde()
	{
		return $this->telefonterminEnde;
	}
	
	/**
	 * Setzt den Telefontermin des AP
	 * @param AddressBase
	 * @return bool
	 */
	public function SetTelefonterminAnsprechpartner(AddressBase $telefonterminAnsprechpartner=null)
	{
		$this->telefonterminAnsprechpartner = $telefonterminAnsprechpartner;
		return true;
	}
	
	/**
	 * Gibt den Telefontermin des AP zurück
	 * @return AddressBase
	 */
	public function GetTelefonterminAnsprechpartner()
	{
		return $this->telefonterminAnsprechpartner;
	}
	
	/**
	 * Setzt das Abschlussdatum
	 * @param int $abschlussdatum
	 * @return bool
	 */
	public function SetAbschlussdatum($abschlussdatum)
	{
		if (!is_int($abschlussdatum)) return false;
		$this->abschlussdatum = $abschlussdatum;
		return true;
	}
	
	/**
	 * Gibt das Abschlussdatum zurück
	 * @return int
	 */
	public function GetAbschlussdatum()
	{
		return $this->abschlussdatum;
	}
	
	/**
	 * Setzt das Zahlungsdatum
	 * @param int $zahlungsdatum
	 * @return boolean Success
	 */
	public function SetZahlungsdatum($zahlungsdatum)
	{
		if (!is_int($zahlungsdatum)) return false;
		$this->zahlungsdatum = $zahlungsdatum;
		return true;
	}
	
	/**
	 * Gibt das Zahlungsdatum zurück
	 * @return int 
	 */
	public function GetZahlungsdatum()
	{
		return $this->zahlungsdatum;
	}

	/**
	 * Gibt alle Form-Optionen zurück
	 * @return array
	 */
	static public function GetAvailableForms()
	{
		return array(self::FORM_EXTRAJUDICIAL, self::FORM_JUDICIAL);
	}

	/**
	 * Setzt die Form
	 * @param int $form
	 * @return boolean Success
	 */
	public function SetForm($form)
	{
		if (!is_int($form)) return false;
		$this->form = $form;
		return true;
	}

	/**
	 * Gibt die Form zurück
	 * @return int
	 */
	public function GetForm()
	{
		return $this->form;
	}

	/**
	 * Gibt alle Optionen der Stufen zu klärender Punkte zurück
	 * @return array
	 */
	static public function GetAvailableStagesPointsToBeClarified()
	{
		return array(self::STAGE_UNKNOWN, self::STAGE_PROVIDE, self::STAGE_EVALUATE);
	}

	/**
	 * Setzt die Stufen zu klärende Punkte
	 * @param int $stagesPointsToBeClarified
	 * @return boolean Success
	 */
	public function SetStagesPointsToBeClarified($stagesPointsToBeClarified)
	{
		if (!is_int($stagesPointsToBeClarified)) return false;
		$this->stagesPointsToBeClarified = $stagesPointsToBeClarified;
		return true;
	}

	/**
	 * Gibt die Stufen zu klärende Punkte zurück
	 * @return int
	 */
	public function GetStagesPointsToBeClarified()
	{
		return $this->stagesPointsToBeClarified;
	}

	/**
	 * Setzt das Aktenzeichen
	 * @param string $referenceNumber
	 * @return boolean Success
	 */
	public function SetReferenceNumber($referenceNumber)
	{
		$this->referenceNumber = $referenceNumber;
		return true;
	}

	/**
	 * Gibt das Aktenzeichen zurück
	 * @return string
	 */
	public function GetReferenceNumber()
	{
		return $this->referenceNumber;
	}

	/**
	 * Setzt den Teilvorgang
	 * @param string $subProcedure
	 * @return boolean Success
	 */
	public function SetSubProcedure($subProcedure)
	{
		$this->subProcedure = $subProcedure;
		return true;
	}

	/**
	 * Gibt den Teilvorgang zurück
	 * @return string
	 */
	public function GetSubProcedure()
	{
		return $this->subProcedure;
	}

	/**
	 * Gibt den Vorgang zurück
	 * @return string
	 */
	public function GetProcedure(DBManager $db)
	{
		$verwalterGruppe = "";
		$ta = $this->GetTeilabrechnung($db);
		if ($ta!=null)
		{
			$verwalter = $ta->GetVerwalter();
			if ($verwalter!=null)
			{
				$verwalterGruppe = $verwalter->GetAddressGroupName();
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
	 * Gibt den Vorgangstyp zurück
	 * @return string
	 */
	public function GetProcedureType(DBManager $db)
	{
		if (in_array($this->GetCurrentStatus(), array(0,29,1,2,3))) return "DA";
		if ((in_array($this->GetCurrentStatus(), array(4)) && $this->GetWiderspruchCount($db)>1) || $this->GetStagesPointsToBeClarified()==self::STAGE_EVALUATE) return "WS2";
		if (in_array($this->GetCurrentStatus(), array(36, 4))) return "WS1";
		return "FO";
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
	 * Überführt in den nächsten Status 
	 * @param int $branch
	 * @return bool
	 */
	public function GotoNextStatus($branch=0)
	{
		$curStatusData=WorkflowManager::GetProzessStatusForStatusID($this->GetCurrentStatus());
		if ($curStatusData===false) return false;
		$nextStatus = $curStatusData["nextStatusIDs"][$branch];
		if ($branch==-1) $nextStatus = $this->GetJumpBackStatus();
		return $this->SetCurrentStatus($nextStatus);
	}
	
	/**
	 * Überführt in den vorherigen Status 
	 * @return bool
	 */
	public function GotoPreviousStatus()
	{
		if ($this->GetLastStatus()==-1) return false;
		if ($this->SetCurrentStatus($this->GetLastStatus())!==true) return false;
		$this->ClearLastStatus();
		return true;
	}
	
	/**
	 * Überführt den Prozess in Status 9 "Nächste Maßnahme festlegen"
	 * @return bool
	 */
	public function AboardStatus()
	{
		if ($this->SetCurrentStatus(9)!==true) return false;
		$this->ClearLastStatus();
		return true;
	}
	
	/**
	 * Gibt den Typ des Status zurück
	 * @param User $currentUser
	 * @param DBManager $db
	 * @return string
	 */
	public function GetCurrentStatusName(User $currentUser, DBManager $db)
	{
		$wsUploaded = -1;
		if ($currentUser->GetGroupBasetype($db) <= UM_GROUP_BASETYPE_KUNDE)
		{
			$wsUploaded = $this->IsWiderspruchUploaded($db);
		}
		
		return WorkflowManager::GetStatusName($db, $currentUser, $this->GetCurrentStatus(), $wsUploaded);
	}
	
	/**
	 * Gibt zurück, ob bereits ein Widerspruch zu diesem Prozess im System hochgeladen wurde (und somit der Status 'Widerspruch drucken/versenden/hochladen' durchlaufen wurde)
	 * @pararm DBManager $db
	 * @return boolean
	 */
	protected function IsWiderspruchUploaded(DBManager $db)
	{
		$abrechnungsJahr = $this->GetAbrechnungsJahr();
		if ($abrechnungsJahr==null) return false;
		$data = $db->SelectAssoc("SELECT count(".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".pkey) as count FROM ".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX)." LEFT JOIN ".File::TABLE_NAME." ON ".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".file=".File::TABLE_NAME.".pkey WHERE ".File::TABLE_NAME.".fileSemantic=".FM_FILE_SEMANTIC_WIDERSPRUCH." AND ".(Widerspruch::TABLE_NAME.FileRelationManager::TABLE_NAME_POSTFIX).".widerspruch IN (SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".$abrechnungsJahr->GetPKey().")");
		return ($data[0]['count']>0 ? true : false);
	}
	
	/**
	 * Gibt die Anzahl der untergeordneten Kommentare zurück
	 * @param DBManager $db
	 * @param User $user User-Objekt, für den die Kommentare abgerufen werden sollen
	 * @return int
	 */
	public function GetCommentCount(DBManager $db, User $user)
	{
		if ($this->commentRelationManager==null) return 0;
		return $this->commentRelationManager->GetCommentCount($db, $user);
	}
	
	/**
	 * Gibt alle untergeordneten Kommentare zurück
	 * @param DBManager $db
	 * @param User $user User-Objekt, für den die Kommentare abgerufen werden sollen
	 * @return Comment[]
	 */
	public function GetComments(DBManager $db, User $user)
	{
		if ($this->commentRelationManager==null) return Array();
		return $this->commentRelationManager->GetComments($db, $user);
	}
	
	/**
	 * Ordnet das übergebene Kommentar dieser Gruppe unter
	 * @param DBManager $db
	 * @param Comment $comment
	 * @return bool
	 */
	public function AddComment(DBManager $db, Comment $comment)
	{
		if ($this->commentRelationManager==null) return false;
		return $this->commentRelationManager->AddComment($db, $comment);
	}
	
	/**
	 * Gibt die Anzahl der zu diesem Prozess hinterlegten Dateien mit der übergebenen Semantik zurück
	 * @param DBManager $db
	 * @param int $fileSemantic
	 * @param string $additionalWhereClause
	 * @return int
	 */
	public function GetFileCount(DBManager $db, $fileSemantic=FM_FILE_SEMANTIC_UNKNOWN, $additionalWhereClause="")
	{
		return $this->fileRelationManager->GetFileCount($db, $fileSemantic, $additionalWhereClause);
	}
	
	/**
	 * Gibt alle zu diesem Prozess hinterlegten Dateien mit der übergebenen Semantik zurück
	 * @param DBManager $db
	 * @param int $fileSemantic
	 * @param string $additionalWhereClause
	 * @return File[]
	 */
	public function GetFiles(DBManager $db, $fileSemantic=FM_FILE_SEMANTIC_UNKNOWN, $additionalWhereClause="")
	{
		return $this->fileRelationManager->GetFiles($db, $fileSemantic, $additionalWhereClause);
	}
		
	/**
	 * Fügt diesem Prozess die übergebene Datei hinzu
	 * @param DBManager $db
	 * @param File $file
	 * @return bool
	 */
	public function AddFile(DBManager $db, File $file)
	{
		return $this->fileRelationManager->AddFile($db, $file);
	}
	
	/**
	 * Entfernt die übergebene Datei von diesem Prozess. Falls die Datei 
	 * sonst nirgends verwendet wird, wird diese gleich gelöscht.
	 * @param DBManager $db
	 * @param File $file
	 * @return bool
	 */
	public function RemoveFile(DBManager $db, File $file)
	{
		return $this->fileRelationManager->RemoveFile($db, $file);
	}
	
	/**
	 * Gibt das zugehörige Abrechnungsjahr zurück
	 * @return Abrechnungsjahr
	 */
	public function GetAbrechnungsJahr()
	{
		return $this->abrechnungsjahr;
	}
	
	/**
	 * Gibt das zugehörige Abrechnungsjahr als String zurück
	 * @return string
	 */
	public function GetAbrechnungsJahrString()
	{
		$abrechnungsjahr = $this->GetAbrechnungsJahr();
		if ($abrechnungsjahr==null) return "";
		return $abrechnungsjahr->GetJahr();
	}
	
	/**
	 * Setzt das zugehörige Abrechnungsjahr
	 * @param DBManager $db
	 * @param AbrechnungsJahr $abrechnungsjahr
	 * @return bool
	 */
	public function SetAbrechnungsJahr(DBManager $db, $abrechnungsjahr)
	{
		if ($abrechnungsjahr->GetPKey()==-1) return false;
		// Prüfen ob der Abrechnungsjahr bereits ein anderer ProcessStatus zugewiesen ist
		$data=$db->SelectAssoc( "SELECT pkey FROM ".ProcessStatus::TABLE_NAME." WHERE abrechnungsjahr=".$abrechnungsjahr->GetPKey()." AND pkey!=".$this->GetPKey() );
		if( count($data)!=0 )return false;
		$this->abrechnungsjahr=$abrechnungsjahr;
		return true;
	}
	
	/**
	 * Gibt die aktuelle Teilabrechnung zurück
	 * @return Teilabrechnung
	 */
	public function GetCurrentTeilabrechnung()
	{
		return $this->currentTeilabrechnung;
	}

	/**
	 * Setzt die aktuelle Teilabrechnung
	 * @param DBManager $db
	 * @param Teilabrechnung $currentTeilabrechnung
	 * @param bool $skipSecurityCheck
	 * @return bool
	 */
	public function SetCurrentTeilabrechnung($db, Teilabrechnung $currentTeilabrechnung, $skipSecurityCheck=false)
	{
		if ($currentTeilabrechnung->GetPKey()==-1) return false;
		if ($skipSecurityCheck!==true) {
			// Prüfen, ob die übergebene TA zu diesem Prozess gehört
			$obj = $this->GetAbrechnungsJahr();
			if ($obj == null) return false;
			$found = false;
			$tas = $obj->GetTeilabrechnungen($db);
			for ($a = 0; $a < count($tas); $a++) {
				if ($tas[$a]->GetPKey() == $currentTeilabrechnung->GetPKey()) {
					$found = true;
					break;
				}
			}
			if (!$found) return false;
		}
		$this->currentTeilabrechnung = $currentTeilabrechnung;
		return true;
	}

	/**
	 * @param DBManager $db
	 * @return bool
	 */
	public function RemoveCurrentTeilabrechnung(DBManager $db)
	{
		$currentTaPkey = $this->currentTeilabrechnung!=null ? $this->currentTeilabrechnung->GetPKey() : -1;
		$this->currentTeilabrechnung = null;
		// try to replace the current TA with another TA
		$tas = $this->GetTeilabrechnungen($db);
		foreach ($tas as $ta)
		{
			if ($ta->GetPKey()!=$currentTaPkey)
			{
				$this->currentTeilabrechnung = $ta;
				break;
			}
		}
		return true;
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
	 * @param DBManager $db
	 * @return int
	 */
	public function GetTeilabrechnungenCount(DBManager $db)
	{
		return $this->GetAbrechnungsJahr()->GetTeilabrechnungCount($db);
	}

	/**
	 * @param DBManager $db
	 * @return Teilabrechnung[]
	 */
	public function GetTeilabrechnungen(DBManager $db)
	{
		return $this->GetAbrechnungsJahr()->GetTeilabrechnungen($db);
	}


	/**
	 * Gibt das Auftragsdatum zurück
	 * @return int 
	 */
	public function GetAuftragsdatumAbrechnung()
	{
		$teilabrechnung = $this->GetCurrentTeilabrechnung();
		if ($teilabrechnung==null) return 0;
		return $teilabrechnung->GetAuftragsdatumAbrechnung();
	}
	
	/**
	 * Gibt den Ansprechpartner zurück
	 * @return AddressBase
	 */
	public function GetAnsprechpartner()
	{
		$teilabrechnung = $this->GetCurrentTeilabrechnung();
		if ($teilabrechnung==null) return null;
		return $teilabrechnung->GetAnsprechpartner();
	}
	
	/**
	 * Return the Name of trustee group
	 * @param DBManager $db
	 * @return string
	 */
	public function GetGroupTrusteeName(DBManager $db)
	{
		$returnValue = "";
		$ta = $this->GetTeilabrechnung($db);
		if ($ta!=null)
		{
			$verwalter = $ta->GetVerwalter();
			if ($verwalter!=null)
			{
				$returnValue = $verwalter->GetAddressGroupName();
			}
		}
		return $returnValue;
	}
	
	/**
	 * Get calculated priority of the process 
	 * @param DBManager $db
	 * @return int
	 */
	public function GetAutoPrio(DBManager $db)
	{
		if ($this->GetPKey()==-1) return Schedule::PRIO_NORMAL;
		$query ="SELECT ";
		$query.=" ".ProcessStatus::GetPrioColumnQuery(true)." ";
		$query.=" FROM ".ProcessStatus::TABLE_NAME;
		$query.=" LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus ::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".currentTeilabrechnung=".Teilabrechnung::TABLE_NAME.".pkey ";
		$query.=" WHERE ".ProcessStatus::TABLE_NAME.".pkey=".$this->GetPKey();
		$data = $db->SelectAssoc($query);
		if (count($data)!=1) return Schedule::PRIO_NORMAL;
		return (int)$data[0]['prio'];
	}
	
	/**
	 * Return a text with the reason for the prio of this process
	 * @param DBManager $db
	 * @return string
	 */
	public function GetPrioReason(DBManager $db)
	{
		if ($this->GetPKey()==-1) return Schedule::PRIO_NORMAL;
		$query ="SELECT ";
		$query.=" ".ProcessStatus::GetPrioColumnQuery(false, true)." ";
		$query.=" FROM ".ProcessStatus::TABLE_NAME;
		$query.=" LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".ProcessStatus ::TABLE_NAME.".abrechnungsjahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON ".ProcessStatus::TABLE_NAME.".currentTeilabrechnung=".Teilabrechnung::TABLE_NAME.".pkey ";
		$query.=" WHERE ".ProcessStatus::TABLE_NAME.".pkey=".$this->GetPKey();
		$data = $db->SelectAssoc($query);
		if (count($data)!=1) return "";
		switch($data[0]['prio'])
		{
			case "0":
				return "";
			case "1":
				return "20 Tage nach der Beauftragung wurde noch kein WS an den Kunden versendet";
			case "2":
				if ($this->GetCurrentStatus()==18) return "3 Tage nachdem das Antwortschrieben hochgeladen wurde, wurde dieses noch nicht klassifiziert ";
				elseif ($this->GetCurrentStatus()==9) return "3 Tage nach Zuteilung des Prozesses, wurde noch keine nächste Maßnahme festgelegt";
				elseif ($this->GetCurrentStatus()==21) return "3 Tage nach Erhalt, wurde vom Kunden noch kein unterschriebener Widerspruch hochgeladen";
				else return "???";
			case "3":
				return "3 Tage nach Ablauf des Zahlungsziels wurde die Rechnung noch nicht bezahlt";
			case "10":
				return "Die Priorisierung wurde manuell erhöht";
		}
		return "MISSING";
	}
	
	/**
	 * Return a column query to use in SELECT statements to get the correct priority (auto/manuell priority)
	 * @param boolean $ignoreManuel
	 * @return string
	 */
	static public function GetPrioColumnQuery($ignoreManuel=false, $getReasonId=false)
	{
		$currentPrioQuery = "";
		if (!$ignoreManuel)
		{
			$currentPrioQuery.= "(SELECT IF (";
			$currentPrioQuery.= "	".ProcessStatus::TABLE_NAME.".prioFunction=".Schedule::PRIO_FUNCTION_MANUEL.", ";
			$currentPrioQuery.= "	".ProcessStatus::TABLE_NAME.".prio, ";
		}
		$currentPrioQuery.= "	".Schedule::PRIO_NORMAL." ";
		/*$currentPrioQuery.= "	(SELECT IF (";
									// Normale priorität wenn die Prüfung bereits eingestellt wurde
		$currentPrioQuery.= "		".ProcessStatus::TABLE_NAME.".currentStatus IN (7, 26), ";
		$currentPrioQuery.= "		".Schedule::PRIO_NORMAL.", ";
		$currentPrioQuery.= "		(SELECT IF (";
										// 20 Tage nach der Beauftragung wurde noch kein WS an den Kunden versendet...
		$currentPrioQuery.= "			(".time()."-".Teilabrechnung::TABLE_NAME.".auftragsdatumAbrechnung)>".(60*60*24*20)." AND ";
		$currentPrioQuery.= "			(SELECT COUNT(".File::TABLE_NAME.".pkey) FROM ".File::TABLE_NAME." LEFT JOIN ".Widerspruch::TABLE_NAME."_file ON ".Widerspruch::TABLE_NAME."_file.file=".File::TABLE_NAME.".pkey LEFT JOIN ".Widerspruch::TABLE_NAME." ON ".Widerspruch::TABLE_NAME."_file.widerspruch=".Widerspruch::TABLE_NAME.".pkey WHERE fileSemantic=".FM_FILE_SEMANTIC_WIDERSPRUCH." AND ".Widerspruch::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey)=0, ";
		$currentPrioQuery.= "			".($getReasonId ? 1 : Schedule::PRIO_HIGH).", ";
		$currentPrioQuery.= "			(SELECT IF (";
											// "Antwortschreiben klassifiziern", "Nächste Maßnahme festlegen" and "Widerspruch drucken/versenden/hochladen"...
		$currentPrioQuery.= "				".ProcessStatus::TABLE_NAME.".currentStatus IN (18, 9, 21) AND";
											// the last status change was at least 3 days ago
		$currentPrioQuery.= "				".ProcessStatus::TABLE_NAME.".lastStatusChange!=0 AND ";
		$currentPrioQuery.= "				(".time()."-".ProcessStatus::TABLE_NAME.".lastStatusChange)>".(60*60*24*3).", ";
		$currentPrioQuery.= "				".($getReasonId ? 2 : Schedule::PRIO_HIGH).", ";
		$currentPrioQuery.= "				(SELECT IF (";
												// the bill wasn't payed in time 
		$currentPrioQuery.= "					(";
		$currentPrioQuery.= "						(SELECT COUNT(pkey) FROM ".Widerspruch::TABLE_NAME." WHERE ".Widerspruch::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey)>0 AND ";
		$currentPrioQuery.= "						(SELECT paymentDate FROM ".Widerspruch::TABLE_NAME." WHERE ".Widerspruch::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1)>0 AND ";
		//$currentPrioQuery.= "						(SELECT paymentDaysOfGrace FROM ".Widerspruch::TABLE_NAME." WHERE ".Widerspruch::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1)>0 AND ";
		$currentPrioQuery.= "						(SELECT paymentReceived FROM ".Widerspruch::TABLE_NAME." WHERE ".Widerspruch::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1)=0 AND ";
		$currentPrioQuery.= "						(".time()."-(SELECT (paymentDate+paymentDaysOfGrace*60*60*24) FROM ".Widerspruch::TABLE_NAME." WHERE ".Widerspruch::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ORDER BY widerspruchsNummer DESC LIMIT 0,1)>".(60*60*24*3).") ";
		$currentPrioQuery.= "					),";
		$currentPrioQuery.= "					".($getReasonId ? 3 : Schedule::PRIO_HIGH).", ";
		$currentPrioQuery.= "					".Schedule::PRIO_NORMAL." ";
		$currentPrioQuery.= "				))";
		$currentPrioQuery.= "			))";
		$currentPrioQuery.= "		))";
		$currentPrioQuery.= "	))";*/
		if (!$ignoreManuel)
		{
			$currentPrioQuery.= "))";
		}
		$currentPrioQuery.= " AS prio ";
		return $currentPrioQuery;
	}
	
	/**
	 * Gibt das (älteste) Beuaftragungsdatum der Untergeorndeten TAs zurück
	 */
	public function GetBeauftragungsdatum(DBManager $db)
	{
		$abrechnungsJahr = $this->GetAbrechnungsJahr();
		if ($abrechnungsJahr==null) return 0;
		$data = $db->SelectAssoc("SELECT auftragsdatumAbrechnung FROM ".Teilabrechnung::TABLE_NAME." WHERE abrechnungsJahr=".$abrechnungsJahr->GetPKey()." ORDER BY auftragsdatumAbrechnung ASC" );
		return $data[0]['auftragsdatumAbrechnung'];
	}
	
	/**
	 * Gibt die Dauer der Prüfung zurück
	 * @return int
	 */
	public function GetDuration(DBManager $db)
	{
		if( $this->IsFinished() )return ($this->GetFinished()-$this->GetBeauftragungsdatum($db));
		return (time()-$this->GetBeauftragungsdatum($db));
	}
	
	/**
	 * Gibt den zugehörigen Vertrag zurück
	 * @return Contract
	 */
	public function GetContract()
	{
		$obj=$this->GetAbrechnungsJahr();
		return $obj==null ? null : $obj->GetContract();
	}
		
	/**
	 * Return the current currency
	 * @return string 
	 */
	public function GetCurrency()
	{
		$contract = $this->GetContract();
		if ($contract==null) return "";
		return $contract->GetCurrency();
	}
	
	/**
	 * Return the current currency
	 * @return string 
	 */
	public function GetCountryName()
	{
		$cLocation = $this->GetLocation();
		if ($cLocation==null) return "";
		return $cLocation->GetCountry();
	}
	
	/**
	 * Gibt den zugehörigen Laden zurück
	 * @return CShop
	 */
	public function GetShop()
	{
		$obj=$this->GetContract();
		return ($obj==null ? null : $obj->GetShop());
	}
	
	/**
	 * return shop name
	 * @return string
	 */
	public function GetShopName()
	{
		$cShop = $this->GetShop();
		if ($cShop==null) return "";
		return $cShop->GetName();
	}
	
	/**
	 * Gibt die interne Ladennummer zurück
	 * @return string
	 */
	public function GetInternalShopNo()
	{
		$cShop = $this->GetShop();
		if ($cShop==null) return "";
		return $cShop->GetInternalShopNo();
	}
	
	/**
	 * Gibt die zugehörige Location zurück
	 * @return CLocation
	 */
	public function GetLocation()
	{
		$obj = $this->GetShop();
		return $obj==null ? null : $obj->GetLocation();
	}
	
	/**
	 * return location name
	 * @return string
	 */
	public function GetLocationName()
	{
		$location = $this->GetLocation();
		if ($location==null) return "";
		return $location->GetName();
	}

	/**
	 * Return the company
	 * @return CCompany
	 */
	public function GetCompany()
	{
		$obj = $this->GetLocation();
		return ($obj==null ? null : $obj->GetCompany());
	}
	
	/**
	 * Return the company name
	 * @return string
	 */
	public function GetCompanyName()
	{
		$company = $this->GetCompany();
		if ($company==null) return "";
		return $company->GetName();
	}
	
	/**
	 * Return the group
	 * @return CGroup
	 */
	public function GetGroup()
	{
		$obj = $this->GetCompany();
		return ($obj==null ? null : $obj->GetGroup());
	}
	
	/**
	 * Return the group name
	 * @return string
	 */
	public function GetGroupName()
	{
		$group = $this->GetGroup();
		if ($group==null) return "";
		return $group->GetName();
	}
	
	/**
	 * Gibt den aktuellen Widerspruch
	 * @return Widerspruch
	 */
	public function GetWiderspruch($db)
	{
		$obj = $this->GetAbrechnungsJahr();
		return ($obj==null ? null : $obj->GetCurrentWiderspruch($db));
	}
	
	/**
	 * Gibt alle Widersprüche zurück
	 * @return Widerspruch[]
	 */
	public function GetWidersprueche($db)
	{
		$obj = $this->GetAbrechnungsJahr();
		return ($obj==null ? Array() : $obj->GetWidersprueche($db));
	}
	
	/**
	 * Gibt die Anzahl der Widersprüche zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetWiderspruchCount(DBManager $db)
	{
		$obj = $this->GetAbrechnungsJahr();
		return ($obj==null ? 0 : $obj->GetWiderspruchCount($db));
	}
	
	/**
	 * Ordnet dem Widerspruch diese Teilabrechnung zu
	 * @param DBManager $db
	 * @param Widerspruch $widerspruch
	 * @return bool
	 */
	public function AddWiderspruch(DBManager $db, Widerspruch $widerspruch)
	{
		$abrechnungsJahr = $this->GetAbrechnungsJahr();
		if ($abrechnungsJahr==null) return false;
		// Widerspruch dem zugehörigen Abrechnungsjahr zuweisen...
		return $abrechnungsJahr->AddWiderspruch($db, $widerspruch);
	}
	
	/**
	 * Gibt die Summe des "Betrag Kunden" über alle TAs dieses Prozessses hinweg zurück
	 * @return float
	 */
	public function GetSummeBetragKunde($db)
	{
		$summe=0.0;
		$abrechnungsJahr=$this->GetAbrechnungsJahr();
		if( $abrechnungsJahr==null )return $summe;
		$data=$db->SelectAssoc("SELECT pkey FROM ".Teilabrechnung::TABLE_NAME." WHERE abrechnungsJahr=".$abrechnungsJahr->GetPKey());
		for( $a=0; $a<count($data); $a++ ){
			$data2=$db->SelectAssoc("SELECT SUM(betragKunde) AS summe FROM ".Teilabrechnungsposition::TABLE_NAME." WHERE teilabrechnung=".$data[$a]["pkey"]);
			if( isset($data2[0]["summe"]) && is_numeric($data2[0]["summe"]) )$summe+=((float)$data2[0]["summe"]);
		}
		return $summe;
	}
		
	/**
	 * Gibt die Summe des "Betrag Kunden" je qm über alle TAs dieses Prozessses hinweg zurück
	 * @return float
	 */
	public function GetSummeBetragKundeQM($db)
	{
		$contract=$this->GetContract();
		if( $contract==null || $contract->GetMietflaecheQM()==0.0 )return 0.0;
		return round($this->GetSummeBetragKunde($db)/$contract->GetMietflaecheQM(),2);
	}
	
	/**
	 * Gibt die "Summe Betrag TAPs lt. Abrechnung" über alle TAs dieses Prozessses hinweg zurück
	 * @return float
	 */
	public function GetSummeTAPs($db)
	{
		$summe=0.0;
		$abrechnungsJahr=$this->GetAbrechnungsJahr();
		if( $abrechnungsJahr==null )return $summe;
		$data=$db->SelectAssoc("SELECT SUM(abrechnungsergebnisLautAbrechnung) AS summe FROM ".Teilabrechnung::TABLE_NAME." WHERE abrechnungsJahr=".$abrechnungsJahr->GetPKey());
		if( isset($data[0]["summe"]) && is_numeric($data[0]["summe"]) ) $summe=((float)$data[0]["summe"]);
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
		$abrechnungsJahr = $this->GetAbrechnungsJahr();
		if ($abrechnungsJahr==null) return false;
		
		$additionalWhereClause = "";
		if ($subtype==1) $additionalWhereClause = "realisiert=".Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES;
		if ($subtype==2) $additionalWhereClause = "realisiert=".Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO;
		if ($subtype==3) $additionalWhereClause = "type=".Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG;
		if ($subtype==4) $additionalWhereClause = "type=".Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG;
		if ($additionalWhereClause!="") $additionalWhereClause = $additionalWhereClause." AND ";
		$query = "SELECT SUM(kuerzungsbetrag) AS wsSumme FROM ".Kuerzungsbetrag::TABLE_NAME." LEFT JOIN  ".Widerspruchspunkt::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".pkey=".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt WHERE ".$additionalWhereClause." (rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB." OR rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU.") AND ".Widerspruchspunkt::TABLE_NAME.".widerspruch=(SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".$abrechnungsJahr->GetPKey()." ORDER BY widerspruchsNummer DESC LIMIT 0,1)";
		$data  = $db->SelectAssoc($query);
		$returnValue = $data[0]["wsSumme"];
		if (!is_numeric($returnValue)) return false;
		return (float)$returnValue;
	}
	
	/**
	 * Gibt die Kürzungsbeträge und die Widerspruchssumme des aktuellen Widerspruchs zurück
	 * @return float|bool Widerspruchssumme oder false wenn noch kein WS hinterlegt
	 */
	public function GetKuerzungsbetraege($db)
	{
		$returnValue = Array("gruen" => 0.0, "gelb" => 0.0, "grau" => 0.0, "wsSumme" => 0.0);
		$abrechnungsJahr = $this->GetAbrechnungsJahr();
		if ($abrechnungsJahr!=null)
		{
			$data = $db->SelectAssoc("SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".$abrechnungsJahr->GetPKey()." ORDER BY widerspruchsNummer DESC LIMIT 0,1");
			if (count($data)>0)
			{
				$data1 = $db->SelectAssoc("SELECT SUM(kuerzungsbetrag) AS gruen FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN." AND widerspruchspunkt IN (SELECT pkey FROM ".Widerspruchspunkt::TABLE_NAME." WHERE widerspruch=".((int)$data[0]['pkey']).")");
				$returnValue["gruen"] = (float)$data1[0]["gruen"];
				$data1 = $db->SelectAssoc("SELECT SUM(kuerzungsbetrag) AS gelb FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB." AND widerspruchspunkt IN (SELECT pkey FROM ".Widerspruchspunkt::TABLE_NAME." WHERE widerspruch=".((int)$data[0]['pkey']).")");
				$returnValue["gelb"] = (float)$data1[0]["gelb"];
				$data1 = $db->SelectAssoc("SELECT SUM(kuerzungsbetrag) AS grau FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU." AND widerspruchspunkt IN (SELECT pkey FROM ".Widerspruchspunkt::TABLE_NAME." WHERE widerspruch=".((int)$data[0]['pkey']).")");
				$returnValue["grau"] = (float)$data1[0]["grau"];
			}
	
		}
		$returnValue["wsSumme"] = $returnValue["gruen"]+$returnValue["gelb"]+$returnValue["grau"];
		return $returnValue;
	}
	
	/**
	 * Return if the prozess status is active for the specified user
	 * @param User $userToCheckFor
	 * @param DBManager $db
	 * @return boolean
	 */
	public function IsActiveProzess(User $userToCheckFor, DBManager $db)
	{
		$returnValue = WorkflowManager::UserResponsibleForProzess($db, $this->GetCurrentStatus(), $userToCheckFor);
		if ($userToCheckFor->GetGroupBasetype($db)>UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			// If user is a FMS User and the status is a customer status the prozess is passive
			$returnValue = !WorkflowManager::IsCustomerProzess($this->GetCurrentStatus());
			// if user is currently not responisble...
			$user = $this->GetCurrentResponsibleRSUser($db);
			if ($user!=null && $user->GetPKey()!=$userToCheckFor->GetPKey()) 
			{
				// ... the process is passive
				$returnValue = false;
			}
		}
		return $returnValue;
	}
	
	/**
	 * Return the current responsible user for this task
	 * @param DBManager $db
	 * @return User 
	 */
	public function GetCurrentResponsibleRSUser(DBManager $db)
	{
		$cShop = $this->GetShop();
		if ($cShop==null) return null;
		if (in_array($this->GetCurrentStatus(), WorkflowManager::GetAllStatusForGroup(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT)))
		{
			// FMS-Accounts Department is responible for this status
			$origUser = $cShop->GetCPersonFmsAccountsdepartment();
		}
		else
		{
			// FMS is responible for this status
			$origUser = $cShop->GetCPersonRS();
		}
		if ($origUser==null) return null;
		// User zugewiesen?
		$zuweisungUser = $this->GetZuweisungUser();
		if ($zuweisungUser!=null)
		{
			if ($zuweisungUser->GetPKey()!=$origUser->GetPKey())
			{
				$origUser = $zuweisungUser;
			}
		}
		// Hat der User Urlaubsvertretung?
		if ($origUser->GetCoverUser()==-1) return $origUser;
		// Urlaubsvertretung laden...
		$user = new User($db);
		if( $user->Load($origUser->GetCoverUser(), $db) ) return $user;
		return null;
	}
	
	/**
	 * Return the responsible user for this task
	 * @return User 
	 */
	public function GetResponsibleRSUser()
	{
		$cShop = $this->GetShop();
		if ($cShop==null) return null;
		return $cShop->GetCPersonRS();
	}
	
	/**
	 * Return the responsible FMS leader for this task
	 * @return User 
	 */
	public function GetCPersonFmsLeader()
	{
		$cShop = $this->GetShop();
		if ($cShop==null) return null;
		return $cShop->GetCPersonFmsLeader();
	}
	
	/**
	 * Return the responsible customer user for this task
	 * @return User 
	 */
	public function GetResponsibleCustomer()
	{
		$cShop = $this->GetShop();
		if ($cShop==null) return null;
		return $cShop->GetCPersonCustomer();
	}
	
	/**
	 * Return the responsible accounting customer user for this task
	 * @return User
	 */
	public function GetResponsibleCustomerAccounting()
	{
		$cShop = $this->GetShop();
		if ($cShop==null) return null;
		return $cShop->GetCPersonCustomer();
	}

	/**
	 * Gibt die Anzahl der Rückweisungsbegründungen zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetRueckweisungsBegruendungenCount(DBManager $db, $type=-1)
	{
		if( $this->pkey==-1 )return 0;
		$data=$db->SelectAssoc("SELECT count(pkey) as count FROM ".RueckweisungsBegruendungProzess::TABLE_NAME." WHERE ".($type==-1 ? "" : "rwType=".(int)$type." AND ")." process=".$this->pkey." AND processClass='".get_class($this)."'" );
		return (int)$data[0]["count"];
	}

	/**
	 * Gibt alle Rückweisungsbegründungen zurück
	 * @param DBManager $db
	 * @return RueckweisungsBegruendung[]
	 */
	public function GetRueckweisungsBegruendungen(DBManager $db, $type=-1)
	{
		if( $this->pkey==-1 )return Array();
		$data=$db->SelectAssoc("SELECT pkey FROM ".RueckweisungsBegruendungProzess::TABLE_NAME." WHERE ".($type==-1 ? "" : "rwType=".(int)$type." AND ")." process=".$this->pkey." AND processClass='".get_class($this)."' ORDER BY nummer DESC");
		$objects=Array();
		for($a=0; $a<count($data); $a++){
			$object=new RueckweisungsBegruendungProzess($db);
			if( $object->Load($data[$a]["pkey"], $db)===true) $objects[]=$object;
		}
		return $objects;
	}

	/**
	 * @param DBManager $db
	 * @param int $type
	 * @return RueckweisungsBegruendungProzess|null
	 */
	public function GetLastRueckweisungsBegruendung(DBManager $db, $type)
	{
		if ($this->pkey==-1) return null;
		$data = $db->SelectAssoc("SELECT pkey FROM ".RueckweisungsBegruendungProzess::TABLE_NAME." WHERE ".($type==-1 ? "" : "rwType=".(int)$type." AND ")." process=".$this->pkey." AND processClass='".get_class($this)."' ORDER BY nummer DESC LIMIT 1");
		$object = null;
		if (count($data)==1)
		{
			$object = new RueckweisungsBegruendungProzess($db);
			if ($object->Load($data[0]["pkey"], $db)!==true) $object = null;
		}
		return $object;
	}

	/**
	 * Return all files linked to the prozess
	 * @param DBManager $db 
	 * @return array
	 */
	public function GetAllProcessFiles(DBManager $db)
	{
		$customerUser = ($_SESSION["currentUser"]->GetGroupBasetype($db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP ? true : false);
		$files = Array();
		// files directly linked to the process
		$filesTemp = $this->GetFiles($db, FM_FILE_SEMANTIC_PROTOCOL);
		foreach ($filesTemp as $file)
		{
			$entry = $this->MakeFileArrayEntry($customerUser, $this, $file);
			if ($entry===false) continue;
			$files[] = $entry;
		}
		$filesTemp = $this->GetFiles($db, FM_FILE_SEMANTIC_NEWCUSTOMERFILE);
		foreach ($filesTemp as $file)
		{
			$entry = $this->MakeFileArrayEntry($customerUser, $this, $file);
			if ($entry===false) continue;
			$files[] = $entry;
		}
		// files linked to the contract used for this process
		$contract = $this->GetContract();
		if ($contract!=null)
		{
			$filesTemp = $contract->GetFiles($db, FM_FILE_SEMANTIC_MIETVERTRAG);
			foreach ($filesTemp as $file)
			{
				$entry = $this->MakeFileArrayEntry($customerUser, $contract, $file);
				if ($entry===false) continue;
				$files[] = $entry;
			}
			$filesTemp = $contract->GetFiles($db, FM_FILE_SEMANTIC_MIETVERTRAGANLAGE);
			foreach ($filesTemp as $file)
			{
				$entry = $this->MakeFileArrayEntry($customerUser, $contract, $file);
				if ($entry===false) continue;
				$files[] = $entry;
			}
			$filesTemp = $contract->GetFiles($db, FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG);
			foreach ($filesTemp as $file)
			{
				$entry = $this->MakeFileArrayEntry($customerUser, $contract, $file);
				if ($entry===false) continue;
				$files[] = $entry;
			}
		}
		// files linked to the 'Teilabrechnungen' used for this process
		$currentAbrechnungsJahr = $this->GetAbrechnungsJahr();
		if ($currentAbrechnungsJahr!=null)
		{
			$teilabrechnungen = $currentAbrechnungsJahr->GetTeilabrechnungen($db);
			foreach ($teilabrechnungen as $teilabrechnung)
			{
				if ($teilabrechnung==null) continue;
				$filesTemp = $teilabrechnung->GetFiles($db);
				foreach ($filesTemp as $file)
				{
					$entry = $this->MakeFileArrayEntry($customerUser, $teilabrechnung, $file);
					if ($entry===false) continue;
					$files[] = $entry;
				}
			}
		}
		// files linked to the 'Widersprüche' used for this process
		$widersprueche = $this->GetWidersprueche($db);
		foreach ($widersprueche as $widerspruch)
		{
			// Widerspruchschreiben
			$filesTemp = $widerspruch->GetFiles($db, FM_FILE_SEMANTIC_WIDERSPRUCH);
			foreach ($filesTemp as $file)
			{
				$entry = $this->MakeFileArrayEntry($customerUser, $widerspruch, $file);
				if ($entry===false) continue;
				$files[] = $entry;
			}
			// Widerspruchschreiben Sonstiges
			$filesTemp = $widerspruch->GetFiles($db, FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE);
			foreach ($filesTemp as $file)
			{
				$entry = $this->MakeFileArrayEntry($customerUser, $widerspruch, $file);
				if ($entry===false) continue;
				$files[] = $entry;
			}
			// Rechnungen
			$filesTemp = $widerspruch->GetFiles($db, FM_FILE_SEMANTIC_WIDERSPRUCH_RECHNUNG);
			foreach ($filesTemp as $file)
			{
				$entry = $this->MakeFileArrayEntry($customerUser, $widerspruch, $file);
				if ($entry===false) continue;
				$files[] = $entry;
			}
			// FMS-Schreiben
			$filesTemp = $widerspruch->GetFiles($db, FM_FILE_SEMANTIC_RSSCHREIBEN);
			foreach ($filesTemp as $file)
			{
				$entry = $this->MakeFileArrayEntry($customerUser, $widerspruch, $file, WorkflowManager::GetStatusName($db,  $_SESSION["currentUser"], FileManager::GetRSFileStatus($file->GetFileSemanticSpecificString())));
				if ($entry===false) continue;
				$files[] = $entry;
			}
			// Widerspruchschreiben Sonstiges
			$filesTemp = $widerspruch->GetFiles($db, FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE);
			foreach ($filesTemp as $file)
			{
				$entry = $this->MakeFileArrayEntry($customerUser, $widerspruch, $file);
				if ($entry===false) continue;
				$files[] = $entry;
			}
			// Antwortschreiben
			$antwortschreiben = $widerspruch->GetAntwortschreiben($db);
			foreach ($antwortschreiben as $awSchreiben)
			{
				$filesTemp = $awSchreiben->GetFiles($db);
				foreach ($filesTemp as $file)
				{
					$entry = $this->MakeFileArrayEntry($customerUser, $awSchreiben, $file);
					if ($entry===false) continue;
					$files[] = $entry;
				}
			}
		}
		return $files;
	}
	
	/**
	 * Create one entry for the file array (used by 'Terminschiene')
	 * @param boolean $customerUser
	 * @param mixed $containerObject
	 * @param File $file
	 * @param string $type
	 * @param string $subtype
	 * @param string $fileStatus
	 * @return boolean|Array 
	 */
	protected function MakeFileArrayEntry($customerUser, $containerObject, File $file, $fileStatus = "-")
	{
		if (!is_bool($customerUser) || $file->GetIntern() && $customerUser) return false;
		$fileArrayEntry = Array();
		$fileArrayEntry['fileObject'] = $file;
		$fileArrayEntry['containerObject'] = $containerObject;
		$fileArrayEntry['id'] = $file->GetPKey();
		$fileArrayEntry['date'] = $file->GetCreationTime();
		$fileArrayEntry['description'] = $file->GetDescription() ? : '-';
		$fileArrayEntry['name'] = $file->GetFileName();
		$fileArrayEntry['path'] = $file->GetDocumentPath();
		$fileArrayEntry['sichtbar'] = ($file->GetIntern() ? "SFM Mitarbeiter" : "Alle");
		$desc = $file->GetFileSemanticDescription();
		$fileArrayEntry['type1'] = $desc['short'];
		$fileArrayEntry['fileType'] = $desc['long'];
		$fileArrayEntry['fileStatus'] = $fileStatus;
		if (is_a($this, 'ProcessStatusGroup'))
		{
			$fileArrayEntry['prozess'] = $this->GetName();
		}
		else
		{
			$year = $this->GetAbrechnungsJahr();
			$fileArrayEntry['prozess'] = $this->GetLocationName().' | '.$this->GetShopName().' | '.$year->GetJahr();
		}
		$fileArrayEntry['prozess'].= ' ('.WorkflowManager::GetProcessStatusId($this).')';
		$fileArrayEntry['processStatusId'] = WorkflowManager::GetProcessStatusId($this);
		return $fileArrayEntry;
	}
	
	/**
	 * Delete the file with the specified id from this process
	 * @param DBManager $db
	 * @param int $fileIdToDelete 
	 */
	public function DeleteProcessFile(DBManager $db, $fileIdToDelete)
	{
		// search for file in this process...
		$processFiles = $this->GetAllProcessFiles($db);
		foreach ($processFiles as $fileArrayEntry)
		{
			if ($fileArrayEntry['id']==$fileIdToDelete)
			{
				// found...
				if (isset($fileArrayEntry['containerObject']))
				{
					// Remove file from container object
					$fileArrayEntry['containerObject']->RemoveFile($db, $fileArrayEntry['fileObject']);
					//echo "DELETE FILE ID ".$fileIdToDelete." FROM container ".get_class($fileArrayEntry['containerObject'])." <br />";
				}
			}
		}
	}
	
	/**
	 * Return the process path
	 * @return array
	 */
	public function GetProzessPath()
	{
		$returnValue = Array();
		if ($this->GetPKey()!=-1)
		{
			$path = $this->GetGroupName().' | '.$this->GetCompanyName().' | '.$this->GetLocationName().' | '.$this->GetShopName().' | '.$this->GetAbrechnungsJahrString();
			$path.=' ('.WorkflowManager::GetProcessStatusId($this).')';
			$returnValue[] = Array('path' => $path, 'process_id' => WorkflowManager::GetProcessStatusId($this), 'obj' => $this);
		}
		return $returnValue;
	}
	
	/**
	 * Send a reject approval email
	 * @global string $DOMAIN_HTTPS_ROOT
	 * @param EMailManager $emailManager
	 * @param User $currentUser
	 * @return boolean
	 */
	public function SendRejectApprovalEMail(DBManager $db, EMailManager $emailManager, User $currentUser, $documentType=0)
	{
		// E-Mail versenden:
		global $DOMAIN_HTTPS_ROOT;
		$text1 = "";
		$text2 = "";
		switch($documentType)
		{
			case 0:
			{
				$widerspruch = $this->GetWiderspruch($db);
				$text1 = ($widerspruch==null || $widerspruch->GetDokumentenTyp()==Widerspruch::DOKUMENTEN_TYP_WIDERSPRUCH ? "der Widerspruch" : "das Protokoll");
				$text2 = ($widerspruch==null || $widerspruch->GetDokumentenTyp()==Widerspruch::DOKUMENTEN_TYP_WIDERSPRUCH ? "Widerspruch" : "Protokoll");
				break;
			}
			case 1:
			{
				$text1 = "die Terminemail";
				$text2 = "Terminemail";
				break;
			}
		}
		
		$responsibleUser = $this->GetResponsibleRSUser();
		if ($responsibleUser!=null)
		{
			$isProcess = get_class($this)=="ProcessStatus" ? true : false;
			
			$msg =$responsibleUser->GetAnrede($db, 2).",<br /><br />";
			$msg.=$text1." des folgenden ".($isProcess ? "Prozesses" : "Paketes")." wurde von ".$currentUser->GetFirstName()." ".$currentUser->GetName()." zurückgewiesen:<br />\n";
			$msg.="</p><br /><ul>\n";
			$msg.="<li class=\"MsoNormal\">".$this->GetAbrechnungsJahrString()." ".$this->GetCompanyName()." - ".$this->GetLocationName()." (".WorkflowManager::GetProcessStatusId($this).")</li>\n";
			$msg.="</ul><br />\n<p class=\"MsoNormal\">";
			$msg.="Bitte loggen Sie sich <a href='".$DOMAIN_HTTPS_ROOT."'>hier</a> mit ihrem Benutzername und Kennwort ein.<br /><br />\n";
			$msg.="Mit freundlichen Grüßen<br /><strong>SEYBOLD GmbH</Strong>\n";
			return $emailManager->SendEmail($responsibleUser->GetEMail(), $text2." wurde zurückgewiesen", $msg, "Seybold FM <mnk@seybold-fm.com>", "", EMailManager::EMAIL_TYPE_HTML, Array());
		}
		return false;
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
			case "processStatusId":
				return $languageManager->GetString('PROCESS', 'PROCESS_ID');
			case "abschlussdatum":
				return $languageManager->GetString('PROCESS', 'ABSCHLUSSDATUM');
			case "zahlungsdatum":
				return $languageManager->GetString('PROCESS', 'ZAHLUNGSDATUM');
			case "form":
				return $languageManager->GetString('PROCESS', 'FORM');
			case "stagesPointsToBeClarified":
				return $languageManager->GetString('PROCESS', 'STAGE_POINTS_TO_BE_CLARIFIED');
			case "referenceNumber":
				return $languageManager->GetString('PROCESS', 'REFERENCE_NUMBER');
			case "procedure":
				return $languageManager->GetString('PROCESS', 'PROCEDURE');
			case "subProcedure":
				return $languageManager->GetString('PROCESS', 'SUB_PROCEDURE');
			case "procedureType":
				return $languageManager->GetString('PROCESS', 'PROCEDURE_TYPE');

		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}

}
?>