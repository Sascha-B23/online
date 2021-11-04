<?php
/**
 * Diese Klasse repräsentiert einen Widerspruch
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class Widerspruch extends DBEntry implements DependencyFileDescription
{
	/**
	 * Datenbankname und Spalten
	 * @var string
	 */
	const TABLE_NAME = "widerspruch";
	
	/**
	 * Enums für: Nachtrag notwendig?
	 * @var int
	 */
	const NACHTRAG_NOTWENDIG_UNDEFINED = 0;
	const NACHTRAG_NOTWENDIG_JA = 1;
	const NACHTRAG_NOTWENDIG_NEIN = 2;
	
	/**
	 * Enums für: Dokumententyp
	 * @var int
	 */
	const DOKUMENTEN_TYP_UNDEFINED = 0;
	const DOKUMENTEN_TYP_WIDERSPRUCH = 1;
	const DOKUMENTEN_TYP_PROTOKOLL = 2;
	const DOKUMENTEN_TYP_PROTOKOLLENTWURF = 3;
	
	
	const EMAIL_TYPE_UNDEFINED = 0;
	const EMAIL_TYPE_TERMIN = 1;
	const EMAIL_TYPE_INFORMATION = 2;
	
	/**
	 * File relation manager
	 * @var FileRelationManager 
	 */
	protected $fileRelationManager = null;
	
	/**
	 * Nummer des Widerspruchs für diese Abrechnung
	 * @var int
	 */
	protected $widerspruchsNummer=-1;

	/**
	 * Ist der Widerspruch bereinigt?
	 * @var bool
	 */
	protected $upToDate = false;

	/**
	 * Datum des Widerspruchs
	 * @var timestamp
	 */
	protected $datum=0;

	/**
	 * Eingangsfrist
	 * @var timestamp
	 */
	protected $datumEingangsfrist=0;
	
	/**
	 * Fußzeile des Widerspruchs
	 * @var string
	 */
	protected $footer="";
	
	/**
	 * Zugehöriger Abrechnungsjahr
	 * @var AbrechnungsJahr
	 */
	protected $abrechnungsJahr = null;
	
	/**
	 * Corresponding ProcessStatusGroup
	 * @var ProcessStatusGroup 
	 */
	protected $processStatusGroup = null;
	
	/**
	 * Zugehöriger Ansprechpartner
	 * @var AddressBase
	 */
	protected $ansprechpartner = null;
	
	/**
	 * Versenderkürzel
	 * @var string
	 */
	protected $versenderkuerzel="";
	
	/**
	 * Unterschrift 1 Fußzeile
	 * @var string
	 */
	protected $unterschrift1="";
	
	/**
	 * Unterschrift 2 Fußzeile
	 * @var string
	 */
	protected $unterschrift2="";

	/**
	 * Funktion des Unterschreibenden 1
	 * @var string
	 */
	protected $funktionUnterschrift1="";
	
	/**
	 * Funktion des Unterschreibenden 2
	 * @var string
	 */
	protected $funktionUnterschrift2="";
	
	/**
	 * Bemerkungsfeld für den Kunden
	 * @var string
	 */
	protected $bemerkungFuerKunde = "";
	
	/**
	 * Sprache des Anschreibens
	 * @var string 
	 */
	protected $letterLanguage = "DE";
	
	/**
	 * Betreff des Anschreibens
	 * @var string 
	 */
	protected $letterSubject = "";
	
	/**
	 * Text des Anschreibens
	 * @var string 
	 */
	protected $letter = "";
	
	/**
	 * Soll das Protokoll in den Anhang der Email ?
	 * @var boolean 
	 */
	protected $sendProtocolAsAttachemnt = true;
	
	/**
	 * Soll die Anlage für den Kunden sichtbar sein?
	 * @var boolean 
	 */
	protected $hideAttachemntFromCustomer = false;
	
	/**
	 * Versandtdatum der Email (Protokoll)
	 * @var int 
	 */
	protected $letterSendTime = 0;
	
	/**
	 * Nachtrag notwendig
	 * @var int 
	 */
	protected $nachtragNotwendig = self::NACHTRAG_NOTWENDIG_UNDEFINED;
	
	/**
	 * Typ des Dokumentes
	 * @var int 
	 */
	protected $dokumentenTyp = self::DOKUMENTEN_TYP_UNDEFINED;
	
	/**
	 * Zahlung eingegangen
	 * @var boolean 
	 */
	protected $paymentReceived = false;
	
	/**
	 * Rechnungsdatum
	 * @var int 
	 */
	protected $paymentDate = 0;
	
	/**
	 * Rechnungsnummer
	 * @var string
	 */
	protected $paymentNumber = "";
	
	/**
	 * Zahlungsfrist Rechnung in Tagen
	 * @var int 
	 */
	protected $paymentDaysOfGrace = 14;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function Widerspruch(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("widerspruchsNummer", "datum", "datumEingangsfrist", "footer", "abrechnungsJahr", "ansprechpartner", "ansprechpartnerTyp", "versenderkuerzel", "unterschrift1", "unterschrift2", "bemerkungFuerKunde", "letterLanguage", "letterSubject", "letter", "letterSendTime", "sendProtocolAsAttachemnt", "hideAttachemntFromCustomer", "nachtragNotwendig", "dokumentenTyp", "funktionUnterschrift1", "funktionUnterschrift2", "processStatusGroup_rel", "paymentReceived", "paymentDate", "paymentNumber", "paymentDaysOfGrace", "upToDate");
		$dbConfig->rowParam = Array("INT", "BIGINT", "BIGINT", "TEXT", "BIGINT", "BIGINT", "INT", "TEXT", "TEXT", "TEXT", "TEXT", "VARCHAR(2)", "TEXT", "TEXT", "BIGINT", "INT", "INT", "INT", "INT", "TEXT", "TEXT", "BIGINT", "INT", "BIGINT", "TINYTEXT", "INT", "INT");
		$dbConfig->rowIndex = Array("widerspruchsNummer", "abrechnungsJahr", "ansprechpartner", "ansprechpartnerTyp", "processStatusGroup_rel");
		parent::__construct($db, $dbConfig);
		
		$this->fileRelationManager = new FileRelationManager($db, $this);
	}
		
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @return bool Kann der Datensatz gelöscht werden (true/false)
	 */
	public function IsDeletable(&$db)
	{
		if( $this->GetWiderspruchspunktCount($db)>0 )return false;
		if( $this->GetFileCount($db)>0 )return false;
		if( $this->GetTerminCount($db, NKM_TERMIN_TYPE_PHONE)>0 )return false;
		if( $this->GetTerminCount($db, NKM_TERMIN_TYPE_MEETING)>0 )return false;
		if( $this->GetTerminCount($db, NKM_TERMIN_TYPE_RESPONSE)>0 )return false;
		if( $this->GetAntwortschreibenCount($db)>0 )return false;
		if( $this->GetAbbruchProtokolleCount($db)>0 )return false;
		if( $this->GetRueckweisungsBegruendungenCount($db)>0 )return false;
		return parent::IsDeletable($db);
	}
	
	/**
	 * Delete this and all contained objects
	 * @param DBManager $db
	 * @param User $user 
	 */
	public function DeleteRecursive(DBManager $db, User $user)
	{
		if ($user->GetGroupBasetype($db) < UM_GROUP_BASETYPE_ADMINISTRATOR) return false;
		// delete all 'Widerspruchspunkte'...
		$wsPunkte = $this->GetWiderspruchspunkte($db);
		for ($a=0; $a<count($wsPunkte);$a++)
		{
			$wsPunkte[$a]->DeleteMe($db);
		}
		// delete all 'Antwortschrieben'...
		$awSchreiben = $this->GetAntwortschreiben($db);
		for ($a=0; $a<count($awSchreiben);$a++)
		{
			$awSchreiben[$a]->DeleteRecursive($db, $user);
		}
		// delete all 'Termine'...
		$termine = $this->GetAllTermine($db);
		for ($a=0; $a<count($termine);$a++)
		{
			$termine[$a]->DeleteMe($db);
		}
		
		// delete all 'AbbruchProtokolle'...
		$abbruchProtokolle = $this->GetAbbruchProtokolle($db);
		for ($a=0; $a<count($abbruchProtokolle);$a++)
		{
			$abbruchProtokolle[$a]->DeleteMe($db);
		}
		// delete all 'RueckweisungsBegruendungen'...
		$rueckweisungsBegruendungen = $this->GetRueckweisungsBegruendungen($db);
		for ($a=0; $a<count($rueckweisungsBegruendungen);$a++)
		{
			$rueckweisungsBegruendungen[$a]->DeleteMe($db);
		}
		// delete all files...
		$files = $this->fileRelationManager->GetFiles($db);
		for ($a=0; $a<count($files);$a++)
		{
			$this->RemoveFile($db, $files[$a]);
		}
		$this->DeleteMe($db);
		return true;
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if ($this->abrechnungsJahr==null && $this->processStatusGroup==null) return -1;
		// Array mit zu speichernden Daten anlegen
		if ($this->widerspruchsNummer==-1)
		{
			// Wenn die Nummer noch nicht gesetzt wurde, dies jetzt nachholen
			
			$data=$db->SelectAssoc("SELECT count(pkey) as count FROM ".self::TABLE_NAME." WHERE ".($this->abrechnungsJahr!=null ? "abrechnungsJahr=".$this->abrechnungsJahr->GetPKey() : "processStatusGroup_rel=".$this->processStatusGroup->GetPKey()) );
			$this->widerspruchsNummer=((int)$data[0]["count"])+1;
		}		
		$rowName[]= "widerspruchsNummer";
		$rowData[]= $this->widerspruchsNummer;
		$rowName[]= "upToDate";
		$rowData[]= $this->upToDate ? 1 : 0;
		$rowName[]= "datum";
		$rowData[]= $this->datum;
		$rowName[]= "datumEingangsfrist";
		$rowData[]= $this->datumEingangsfrist;
		$rowName[]= "footer";
		$rowData[]= $this->footer;
		$rowName[]= "abrechnungsJahr";
		$rowData[]= ($this->abrechnungsJahr!=null ? $this->abrechnungsJahr->GetPKey() : -1);
		$rowName[]= "processStatusGroup_rel";
		$rowData[]= ($this->processStatusGroup!=null ? $this->processStatusGroup->GetPKey() : -1);
		$rowName[]= "ansprechpartner";
		$rowData[]= $this->ansprechpartner!=null ? $this->ansprechpartner->GetPKey() : -1;
		$rowName[]= "ansprechpartnerTyp";
		$rowData[]= $this->ansprechpartner==null ? AddressBase::AM_CLASS_UNKNOWN : $this->ansprechpartner->GetClassType();
		$rowName[]= "versenderkuerzel";
		$rowData[]= $this->versenderkuerzel;
		$rowName[]= "unterschrift1";
		$rowData[]= $this->unterschrift1;
		$rowName[]= "unterschrift2";
		$rowData[]= $this->unterschrift2;
		$rowName[]= "funktionUnterschrift1";
		$rowData[]= $this->funktionUnterschrift1;
		$rowName[]= "funktionUnterschrift2";
		$rowData[]= $this->funktionUnterschrift2;
		$rowName[]= "bemerkungFuerKunde";
		$rowData[]= $this->bemerkungFuerKunde;
		$rowName[]= "letterLanguage";
		$rowData[]= $this->letterLanguage;
		$rowName[]= "letterSubject";
		$rowData[]= $this->letterSubject;
		$rowName[]= "letter";
		$rowData[]= $this->letter;
		$rowName[]= "sendProtocolAsAttachemnt";
		$rowData[]= ($this->sendProtocolAsAttachemnt ? 1 : 0);
		$rowName[]= "hideAttachemntFromCustomer";
		$rowData[]= ($this->hideAttachemntFromCustomer ? 1 : 0);
		$rowName[]= "letterSendTime";
		$rowData[]= $this->letterSendTime;
		$rowName[]= "nachtragNotwendig";
		$rowData[]= $this->nachtragNotwendig;
		$rowName[]= "dokumentenTyp";
		$rowData[]= $this->dokumentenTyp;
		$rowName[]= "paymentReceived";
		$rowData[]= ($this->paymentReceived ? 1 : 0);
		$rowName[]= "paymentDate";
		$rowData[]= $this->paymentDate;
		$rowName[]= "paymentNumber";
		$rowData[]= $this->paymentNumber;
		$rowName[]= "paymentDaysOfGrace";
		$rowData[]= $this->paymentDaysOfGrace;
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array  	$data		Assoziatives Array mit den Daten aus der Datenbank
 	 * @return bool					Erfolg
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->widerspruchsNummer = $data['widerspruchsNummer'];
		$this->upToDate = ($data['upToDate']==1 ? true : false);
		$this->datum = $data['datum'];
		$this->datumEingangsfrist = $data['datumEingangsfrist'];
		$this->footer=$data["footer"];
		if ($data['abrechnungsJahr']!=-1)
		{
			$this->abrechnungsJahr = new AbrechnungsJahr($db);
			if ($this->abrechnungsJahr->Load($data['abrechnungsJahr'], $db)!==true) $this->abrechnungsJahr=null;
		}
		else
		{
			$this->abrechnungsJahr=null;
		}
		
		
		if ($data['processStatusGroup_rel']!=-1)
		{
			$this->processStatusGroup = new ProcessStatusGroup($db);
			if ($this->processStatusGroup->Load($data['processStatusGroup_rel'], $db)!==true) $this->processStatusGroup=null;
		}
		else
		{
			$this->processStatusGroup=null;
		}
		if ($data['ansprechpartner']!=-1)
		{
			$this->ansprechpartner = AddressManager::GetAddressElementByPkeyAndType($db, (int)$data['ansprechpartnerTyp'], $data['ansprechpartner']);
		}
		else
		{
			$this->ansprechpartner=null;
		}
		$this->versenderkuerzel=$data["versenderkuerzel"];
		$this->unterschrift1=$data["unterschrift1"];
		$this->unterschrift2=$data["unterschrift2"];
		$this->funktionUnterschrift1=$data["funktionUnterschrift1"];
		$this->funktionUnterschrift2=$data["funktionUnterschrift2"];
		$this->bemerkungFuerKunde=$data["bemerkungFuerKunde"];
		$this->letterLanguage= (trim($data['letterLanguage'])!='' ? trim($data['letterLanguage']) : "DE");
		$this->letterSubject=$data["letterSubject"];
		$this->letter=$data["letter"];
		$this->sendProtocolAsAttachemnt=($data["sendProtocolAsAttachemnt"]==1 ? true : false);
		$this->hideAttachemntFromCustomer=($data["hideAttachemntFromCustomer"]==1 ? true : false);
		$this->letterSendTime=(int)$data["letterSendTime"];
		$this->nachtragNotwendig=(int)$data["nachtragNotwendig"];
		$this->dokumentenTyp=(int)$data["dokumentenTyp"];
		$this->paymentReceived=($data["paymentReceived"]==1 ? true : false);
		$this->paymentDate = (int)$data["paymentDate"];
		$this->paymentNumber = $data["paymentNumber"];
		$this->paymentDaysOfGrace = (int)$data["paymentDaysOfGrace"];
		return true;
	}
	
	/**
	 * Return Description
	 */
	public function GetDependencyFileDescription()
	{
		$abrechnungsJahr = $this->GetAbrechnungsJahr();
		$processStatusGroup = $this->GetProcessStatusGroup();
		return ($abrechnungsJahr==null ? ($processStatusGroup==null ? "?" : $processStatusGroup->GetDependencyFileDescription()) : $abrechnungsJahr->GetDependencyFileDescription()).DependencyFileDescription::SEPERATOR."WS Nr. ".$this->GetWiderspruchsNummer()." vom ".date("d.m.Y", $this->GetDatum());
	}
	
	/**
	 * Gibt die Widerspruchs-Nummer zurück
	 * @return int
	 */
	public function GetWiderspruchsNummer()
	{
		return $this->widerspruchsNummer;
	}

	/**
	 * Set if the WSP is up to date or not
	 * @param boolean $upToDate
	 * @return boolean
	 */
	public function SetUpToDate($upToDate)
	{
		if (!is_bool($upToDate)) return false;
		$this->upToDate = $upToDate;
		return true;
	}

	/**
	 * return if the WSP is up to date
	 * @return boolean
	 */
	public function IsUpToDate()
	{
		return $this->upToDate;
	}

	/**
	 * Setzt das Datum
	 * @param int $datum
	 * @return bool
	 */
	public function SetDatum($datum)
	{
		if (!is_int($datum)) return false;
		$this->datum = $datum;
		return true;
	}
	
	/**
	 * Gibt das Datum zurück
	 * @return int
	 */
	public function GetDatum()
	{
		return $this->datum;
	}
	
	/**
	 * Setzt das Datum der Eingangsfrist
	 * @param int $datumEingangsfrist
	 * @return bool
	 */
	public function SetDatumEingangsfrist($datumEingangsfrist)
	{
		if (!is_int($datumEingangsfrist)) return false;
		$this->datumEingangsfrist = $datumEingangsfrist;
		return true;
	}
	
	/**
	 * Gibt das Datum der Eingangsfrist zurück
	 * @return int
	 */
	public function GetDatumEingangsfrist()
	{
		return $this->datumEingangsfrist;
	}
	
	/**
	 * Setzt die Fußzeile des Widerspruchs
	 * @param string $footer
	 * @return bool
	 */
	public function SetFooter($footer)
	{
		$this->footer = $footer;
		return true;
	}
	
	/**
	 * Gibt die Fußzeile des Widerspruchs zurück
	 * @return string
	 */
	public function GetFooter()
	{
		return $this->footer;
	}
		
	/**
	 * Setzt das Versenderkürzel
	 * @param string $versenderkuerzel
	 * @return bool
 	 */
	public function SetVersenderkuerzel($versenderkuerzel)
	{
		$this->versenderkuerzel=$versenderkuerzel;
		return true;
	}
	
	/**
	 * Gibt das Versenderkürzel zurück
	 * @return string
	 */
	public function GetVersenderkuerzel()
	{
		return $this->versenderkuerzel;
	}
	
	/**
	 * Setzt die Unterschrift
	 * @param string $unterschrift
	 * @return bool
 	 */
	public function SetUnterschrift1($unterschrift)
	{
		$this->unterschrift1=$unterschrift;
		return true;
	}
	
	/**
	 * Gibt die Unterschrift zurück
	 * @return string
	 */
	public function GetUnterschrift1()
	{
		return $this->unterschrift1;
	}
			
	/**
	 * Setzt die Unterschrift
	 * @param string $unterschrift
	 * @return bool
 	 */
	public function SetUnterschrift2($unterschrift)
	{
		$this->unterschrift2=$unterschrift;
		return true;
	}
	
	/**
	 * Gibt die Unterschrift zurück
	 * @return string
	 */
	public function GetUnterschrift2()
	{
		return $this->unterschrift2;
	}
	
	/**
	 * Setzt die Funktion des Unterschreibenden
	 * @param string $funktionUnterschrift
	 * @return bool
	 */
	public function SetFunktionUnterschrift1($funktionUnterschrift)
	{
		$this->funktionUnterschrift1=$funktionUnterschrift;
		return true;
	}
	
	/**
	 * Gibt die Funktion des Unterschreibenden zurück
	 * @return string
	 */
	public function GetFunktionUnterschrift1()
	{
		return $this->funktionUnterschrift1;
	}
			
	/**
	 * Setzt die Funktion des Unterschreibenden
	 * @param string $funktionUnterschrift
	 * @return bool
	 */
	public function SetFunktionUnterschrift2($funktionUnterschrift)
	{
		$this->funktionUnterschrift2=$funktionUnterschrift;
		return true;
	}
	
	/**
	 * Gibt die Funktion des Unterschreibenden zurück
	 * @return string
	 */
	public function GetFunktionUnterschrift2()
	{
		return $this->funktionUnterschrift2;
	}
	
	/**
	 * Setzt die Bemerkung für den Kunden
	 * @param string $bemerkungFuerKunde Bemerkung für den Kunden
	 * @return bool
	 */
	public function SetBemerkungFuerKunde($bemerkungFuerKunde)
	{
		$this->bemerkungFuerKunde=$bemerkungFuerKunde;
		return true;
	}
	
	/**
	 * Gibt die Bemerkung für den Kunden zurück
	 * @return string
	 */
	public function GetBemerkungFuerKunde()
	{
		return $this->bemerkungFuerKunde;
	}
	
	/**
	 * Setzt die Sprache
	 * @param string $language
	 * @return bool
	 */
	public function SetLetterLanguage($language)
	{
		if (!CLanguage::ValidateIso639($language)) return false;
		$this->letterLanguage = $language;
		return true;
	}
	
	/**
	 * Gibt die Sprache zurück
	 * @return string
	 */
	public function GetLetterLanguage()
	{
		return $this->letterLanguage;
	}
	
	/**
	 * Setzt den Email-Betreff des Protokolls
	 * @param string $letterSubject
	 * @return bool
	 */
	public function SetLetterSubject($letterSubject)
	{
		$this->letterSubject=$letterSubject;
		return true;
	}
	
	/**
	 * Gibt den Email-Betreff des Protokolls zurück
	 * @return string
	 */
	public function GetLetterSubject()
	{
		return $this->letterSubject;
	}
	
	/**
	 * Setzt den Email-Text des Protokolls
	 * @param string $letter
	 * @return bool
	 */
	public function SetLetter($letter)
	{
		$this->letter = $letter;
		return true;
	}
	
	/**
	 * Gibt den Email-Text des Protokolls zurück
	 * @return string
	 */
	public function GetLetter()
	{
		return $this->letter;
	}
	
	/**
	 * Setzt, ob das Protokoll an die Email angehängt werden soll
	 * @param boolean $sendProtocolAsAttachemnt
	 * @return bool
	 */
	public function SetSendProtocolAsAttachemnt($sendProtocolAsAttachemnt)
	{
		if (!is_bool($sendProtocolAsAttachemnt)) return false;
		$this->sendProtocolAsAttachemnt = $sendProtocolAsAttachemnt;
		return true;
	}
	
	/**
	 * Gibt zurück, ob das Protokoll an die Email angehängt werden soll
	 * @return boolean
	 */
	public function GetSendProtocolAsAttachemnt()
	{
		return $this->sendProtocolAsAttachemnt;
	}
	
	/**
	 * Setzt, ob die Anlage für den Kunden sichtbar sein soll
	 * @param boolean $hideAttachemntFromCustomer
	 * @return bool
	 */
	public function SetHideAttachemntFromCustomer($hideAttachemntFromCustomer)
	{
		if (!is_bool($hideAttachemntFromCustomer)) return false;
		$this->hideAttachemntFromCustomer = $hideAttachemntFromCustomer;
		return true;
	}
	
	/**
	 * Gibt zurück, ob die Anlage für den Kunden sichtbar sein soll
	 * @return boolean
	 */
	public function GetHideAttachemntFromCustomer()
	{
		return $this->hideAttachemntFromCustomer;
	}
	
	/**
	 * Setzt das Versanddatum der Email (Protokoll)
	 * @param int $letterSendTime
	 * @return bool
	 */
	public function SetLetterSendTime($letterSendTime)
	{
		if (!is_int($letterSendTime)) return false;
		$this->letterSendTime = $letterSendTime;
		return true;
	}
	
	/**
	 * Gibt das Versanddatum der Email (Protokoll) zurück
	 * @return int
	 */
	public function GetLetterSendTime()
	{
		return $this->letterSendTime;
	}
	
	/**
	 * Setzt, ob ein Nachtrag notwendig ist
	 * @param int $nachtragNotwendig
	 * @return boolean
	 */
	public function SetNachtragNotwendig($nachtragNotwendig)
	{
		if (!is_int($nachtragNotwendig)) return false;
		$this->nachtragNotwendig=$nachtragNotwendig;
		return true;
	}
	
	/**
	 * Gibt zurück, ob ein Nachtrag notwendig ist
	 * @return int
	 */
	public function GetNachtragNotwendig()
	{
		return $this->nachtragNotwendig;
	}
	
	/**
	 * Setzt den Typ des Dokumentes
	 * @param int $dokumentenTyp
	 * @return boolean
	 */
	public function SetDokumentenTyp($dokumentenTyp)
	{
		if (!is_int($dokumentenTyp)) return false;
		$this->dokumentenTyp = $dokumentenTyp;
		return true;
	}
	
	/**
	 * Gibt den Typ des Dokumentes zurück
	 * @return int
	 */
	public function GetDokumentenTyp()
	{
		return $this->dokumentenTyp;
	}
	
	/**
	 * Setzt, ob die Zahlung eingegangen ist
	 * @param boolean $paymentReceived
	 * @return bool
	 */
	public function SetPaymentReceived($paymentReceived)
	{
		if (!is_bool($paymentReceived)) return false;
		$this->paymentReceived = $paymentReceived;
		return true;
	}
	
	/**
	 * Gibt zurück, ob die Zahlung eingegangen ist
	 * @return boolean
	 */
	public function IsPaymentReceived()
	{
		return $this->paymentReceived;
	}
	
	/**
	 * Setzt, wann die Zahlung eingegangen ist
	 * @param int $paymentDate
	 * @return bool
	 */
	public function SetPaymentDate($paymentDate)
	{
		if (!is_int($paymentDate)) return false;
		$this->paymentDate = $paymentDate;
		return true;
	}
	
	/**
	 * Gibt zurück, wann die Zahlung eingegangen ist
	 * @return int
	 */
	public function GetPaymentDate()
	{
		return $this->paymentDate;
	}
	
	/**
	 * Setzt die Rechnungsnummer
	 * @param string $paymentNumber
	 * @return bool
	 */
	public function SetPaymentNumber($paymentNumber)
	{
		$this->paymentNumber = $paymentNumber;
		return true;
	}
	
	/**
	 * Gibt die Rechnungsnummer zurück
	 * @return string
	 */
	public function GetPaymentNumber()
	{
		return $this->paymentNumber;
	}
	
	/**
	 * Setzt die Zahlungsfrist in Tagen
	 * @param int $paymentDaysOfGrace
	 * @return bool
	 */
	public function SetPaymentDaysOfGrace($paymentDaysOfGrace)
	{
		if (!is_int($paymentDaysOfGrace) || $paymentDaysOfGrace<=0) return false;
		$this->paymentDaysOfGrace = $paymentDaysOfGrace;
		return true;
	}
	
	/**
	 * Gibt die Zahlungsfrist in Tagen zurück
	 * @return int
	 */
	public function GetPaymentDaysOfGrace()
	{
		return $this->paymentDaysOfGrace;
	}
	
	/**
	 * Gibt die Anzahl der zu diesem Widerspruch hinterlegten Dateien mit der übergebenen Semantik zurück
	 * @param DBManager $db
	 * @param int $fileSemantic
	 * @return int
	 */
	public function GetFileCount(DBManager $db, $fileSemantic=FM_FILE_SEMANTIC_UNKNOWN, $additionalWhereClause="")
	{
		return $this->fileRelationManager->GetFileCount($db, $fileSemantic, $additionalWhereClause);
	}
	
	/**
	 * Gibt alle zu diesem Widerspruch hinterlegten Dateien mit der übergebenen Semantik zurück
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
	 * Fügt diesem Widerspruch die übergebene Datei hinzu
	 * @param DBManager $db
	 * @param File $file
	 * @return bool
	 */
	public function AddFile(DBManager $db, File $file)
	{
		return $this->fileRelationManager->AddFile($db, $file);
	}
	
	/**
	 * Entfernt die übergebene Datei von diesem Widerspruch. Falls die Datei 
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
	 * Gibt die zugehörige AbrechnungsJahr zurück
	 * @return AbrechnungsJahr
	 */
	public function GetAbrechnungsJahr()
	{
		return $this->abrechnungsJahr;
	}
	
	/**
	 * Setzt die zugehörige AbrechnungsJahr
	 * @param AbrechnungsJahr $abrechnungsJahr
	 * @return bool
	 */
	public function SetAbrechnungsJahr(AbrechnungsJahr $abrechnungsJahr)
	{
		if ($this->processStatusGroup!=null || $abrechnungsJahr->GetPKey()==-1) return false;
		$this->abrechnungsJahr = $abrechnungsJahr;
		return true;
	}
	
	/**
	 * Gibt die zugehörige ProcessStatusGroup zurück
	 * @return ProcessStatusGroup
	 */
	public function GetProcessStatusGroup()
	{
		return $this->processStatusGroup;
	}
	
	/**
	 * Setzt die zugehörige ProcessStatusGroup
	 * @param ProcessStatusGroup $processStatusGroup
	 * @return bool
	 */
	public function SetProcessStatusGroup(ProcessStatusGroup $processStatusGroup)
	{
		if ($processStatusGroup->GetPKey()==-1) return false;
		$this->processStatusGroup = $processStatusGroup;
		return true;
	}
	
	/**
	 * Gibt den zugehörigen Ansprechpartner zurück
	 * @return AddressBase
	 */
	public function GetAnsprechpartner()
	{
		return $this->ansprechpartner;
	}
	
	/**
	 * Setzt den zugehörigen Ansprechpartner
	 * @param AddressBase $ansprechpartner
	 * @return bool
	 */
	public function SetAnsprechpartner(AddressBase $ansprechpartner=null)
	{
		if ($ansprechpartner==null)
		{
			$this->ansprechpartner=null; 
			return true;
		}
		if ($ansprechpartner->GetPKey()==-1) return false;
		$this->ansprechpartner=$ansprechpartner;
		return true;
	}
	
	/**
	 * Gibt die Anzahl der Widerspruchspunkte zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetWiderspruchspunktCount(DBManager $db)
	{
		if ($this->pkey==-1) return 0;
		$data = $db->SelectAssoc("SELECT count(pkey) as count FROM ".Widerspruchspunkt::TABLE_NAME." WHERE widerspruch=".$this->pkey);
		return (int)$data[0]["count"];
	}
	
	/**
	 * Returns the highest rank value from all 'Widerspruchspunkte' of this 'Widerspruch'
	 * @param DBManager $db
	 * @return int
	 */
	public function GetHighestRank($db)
	{
		if ($this->pkey==-1) return 0;
		$data = $db->SelectAssoc("SELECT rank FROM ".Widerspruchspunkt::TABLE_NAME." WHERE widerspruch=".$this->pkey." ORDER BY rank DESC LIMIT 1");
		return (int)$data[0]["rank"];
	}
	
	/**
	 * Gibt alle Widerspruchspunkte zurück
	 * @param DBManager $db
	 * @param boolean $all Wenn false übergeben wird, werden die versteckten WSPs nicht mit zurückgegeben
	 * @return Widerspruchspunkt[]
	 */
	public function GetWiderspruchspunkte(DBManager $db, $all=true)
	{
		if( $this->pkey==-1 )return Array();
		$data=$db->SelectAssoc("SELECT * FROM ".Widerspruchspunkt::TABLE_NAME." WHERE widerspruch=".$this->pkey." ".($all===false ? "AND hidden!=1" : "")." ORDER BY rank");
		$objects=Array();
		for($a=0; $a<count($data); $a++)
		{
			$object=new Widerspruchspunkt($db);
			$data[$a]['widerspruch'] = $this;
			if ($object->LoadFromArray($data[$a], $db)===true) $objects[] = $object;
		}
		return $objects;
	}
		
	/**
	 * Ordnet dem Widerspruchspunkt diesen Widerspruch zu
	 * @param DBManager $db
	 * @param Widerspruchspunkt $widerspruchspunkt
	 * @return bool
	 */
	public function AddWiderspruchspunkt(DBManager $db, Widerspruchspunkt $widerspruchspunkt)
	{
		if ($this->pkey==-1) return false;
		// Dem Widerspruchspunkt diesen Widerspruch zuweisen...
		return $widerspruchspunkt->SetWiderspruch($this);
	}
	
	/**
	 * Gibt die Summe der Widerspruchspunkte zurück (grün + gelb + grau)
	 * @param DBManager $db 
	 * @param int $type
	 * @return float
	 */
	public function GetWiderspruchsSumme(DBManager $db, $type=0)
	{
		$summe = 0.0;
		$wsPunkte = $this->GetWiderspruchspunkte($db);
		foreach ($wsPunkte as $wsPunkt)
		{
			$summe += $wsPunkt->GetWiderspruchspunktSumme($db, $type);
		}
		return $summe;
	}
	
	/**
	 * Gibt die realisierte Einsparung zurück (grün + gelb)
	 * @param DBManager $db 
	 * @param int $type
	 * @return float
	 */
	public function GetRealisierteEinsparung(DBManager $db, $type=0)
	{
		$summe = 0.0;
		$wsPunkte = $this->GetWiderspruchspunkte($db);
		foreach ($wsPunkte as $wsPunkt)
		{
			$summe += $wsPunkt->GetRealisierteEinsparung($db, $type);
		}
		return $summe;
	}
	
	/**
	 * Gibt die nicht realisierte Einsparung zurück (grau)
	 * @param DBManager $db 
	 * @return float
	 */
	public function GetNichtRealisierteEinsparung(DBManager $db)
	{
		$summe = 0.0;
		$wsPunkte = $this->GetWiderspruchspunkte($db);
		foreach ($wsPunkte as $wsPunkt)
		{
			$summe += $wsPunkt->GetNichtRealisierteEinsparung($db);
		}
		return $summe;
	}
	
	/**
	 * Returns if all Kuerzungsbetraege are from a specific type
	 * @param DBManager $db
	 * @return boolean
	 */
	public function AllKuerzungsbetraegeOfType(DBManager $db, $type=0)
	{
		$wsPunkte = $this->GetWiderspruchspunkte($db);
		foreach ($wsPunkte as $wsPunkt)
		{
			if (!$wsPunkt->AllKuerzungsbetraegeOfType($db, $type))return false;
		}
		return true;
	}

	/**
	 * Gibt die Anzahl der Antwortschreiben zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetAntwortschreibenCount(DBManager $db)
	{
		if ($this->pkey==-1) return 0;
		$data = $db->SelectAssoc("SELECT count(pkey) as count FROM ".Antwortschreiben::TABLE_NAME." WHERE widerspruch=".$this->pkey);
		return (int)$data[0]["count"];
	}
	
	/**
	 * Gibt alle Antwortschreiben zurück
	 * @param DBManager $db
	 * @return Antwortschreiben[]
	 */
	public function GetAntwortschreiben($db)
	{
		if ($this->pkey==-1) return Array();
		$data = $db->SelectAssoc("SELECT pkey FROM ".Antwortschreiben::TABLE_NAME." WHERE widerspruch=".$this->pkey." ORDER BY nummer DESC");
		$objects = Array();
		for($a=0; $a<count($data); $a++)
		{
			$object = new Antwortschreiben($db);
			if ($object->Load($data[$a]["pkey"], $db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Gibt das letzte Antwortschreiben zurück
	 * @param DBManager $db
	 * @return Antwortschreiben
	 */
	public function GetLastAntwortschreiben($db)
	{
		if ($this->pkey==-1) return null;
		$data=$db->SelectAssoc("SELECT pkey FROM ".Antwortschreiben::TABLE_NAME." WHERE widerspruch=".$this->pkey." ORDER BY nummer DESC LIMIT 1");
		$object=null;
		if( count($data)==1 )
		{
			$object = new Antwortschreiben($db);
			if ($object->Load($data[0]["pkey"], $db)!==true) $object = null;
		}
		return $object;
	}
	
	/**
	 * Ordnet dem Antwortschreiben diesen Widerspruch zu
	 * @param DBManager $db
	 * @param Antwortschreiben $antwortschreiben
	 * @return bool
	 */
	public function AddAntwortschreiben(DBManager $db, Antwortschreiben $antwortschreiben)
	{
		if ($this->pkey==-1) return false;
		// Dem Antwortschreiben diesen Widerspruch zuweisen...
		return $antwortschreiben->SetWiderspruch($this);
	}
	
	/**
	 * Return the last Email of the specified type
	 * @param DBManager $db
	 * @return EMail
	 */
	public function GetEMail(DBManager $db, $type)
	{
		if ($this->GetPKey()==-1) return null;
		$mail = $this->GetEmptyMailObject($db, $type);
		if ($mail==null) return $mail;
		$data = $db->SelectAssoc('SELECT * FROM '.$mail->GetTableName().' WHERE widerspruch_rel='.(int)$this->GetPKey().' ORDER BY pkey DESC LIMIT 1');
		if (count($data)==1)
		{
			$data[0]['widerspruch_rel'] = $this;
			if ($mail->LoadFromArray($data[0], $db)!==true) $mail=null;
		}
		else
		{
			$mail->SetWiderspruch($this);
		}
		return $mail;
	}
	
	/**
	 * Return a EMail instance of the specified type
	 * @param DBManager $db
	 * @param int $type
	 * @return EMail
	 */
	protected function GetEmptyMailObject(DBManager $db, $type)
	{
		switch ($type)
		{
			case self::EMAIL_TYPE_TERMIN:
				return new TerminMail($db);
			case self::EMAIL_TYPE_INFORMATION:
				return new InformationMail($db);
		}
		return null;
	}
	
	/**
	 * Gibt die Anzahl der Termine zurück
	 * @param DBManager $db
	 * @param int $type
	 * @return int
	 */
	public function GetTerminCount(DBManager $db, $type)
	{
		if( $this->pkey==-1 || !is_int($type) )return 0;
		$data=$db->SelectAssoc("SELECT count(pkey) as count FROM ".Termin::TABLE_NAME." WHERE type=".$type." AND widerspruch=".$this->pkey );
		return (int)$data[0]["count"];
	}
	
	/**
	* Gibt alle Termine zurück
	* @param DBManager $db
	* @param int $type
	* @return Termin[]	
	*/
	public function GetTermine(DBManager $db, $type)
	{
		if( $this->pkey==-1 || !is_int($type) )return Array();
		$data=$db->SelectAssoc("SELECT pkey FROM ".Termin::TABLE_NAME." WHERE type=".$type." AND widerspruch=".$this->pkey." ORDER BY pkey");
		$objects=Array();
		for($a=0; $a<count($data); $a++)
		{
			$object=new Termin($db);
			if ($object->Load($data[$a]["pkey"], $db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Return all Dates
	 * @param DBManager $db
	 * @return Termin 
	 */
	public function GetAllTermine($db)
	{
		if ($this->pkey==-1) return Array();
		$data = $db->SelectAssoc("SELECT pkey FROM ".Termin::TABLE_NAME." WHERE widerspruch=".$this->pkey." ORDER BY pkey");
		$objects = Array();
		for($a=0; $a<count($data); $a++)
		{
			$object=new Termin($db);
			if( $object->Load($data[$a]["pkey"], $db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Gibt das letzte Termin zurück
	 * @param DBManager $db
	 * @param int $type
	 * @return Termin
	 */
	public function GetLastTermin(DBManager $db, $type)
	{
		if( $this->pkey==-1 || !is_int($type) )return null;
		$data=$db->SelectAssoc("SELECT pkey FROM ".Termin::TABLE_NAME." WHERE type=".$type." AND widerspruch=".$this->pkey." ORDER BY pkey DESC LIMIT 1");
		$object=null;
		if( count($data)==1 )
		{
			$object=new Termin($db);
			if( $object->Load($data[0]["pkey"], $db)!==true )$object=null;
		}
		return $object;
	}
	
	/**
	 * Ordnet dem Termin diesen Widerspruch zu
	 * @param DBManager $db
	 * @param Termin $termin
	 * @return bool
	 */
	public function AddTermin(DBManager $db, Termin $termin)
	{
		if ($this->pkey==-1) return false;
		// Dem Termin diesen Widerspruch zuweisen...
		return $termin->SetWiderspruch($this);
	}

	/**
	 * Gibt die Anzahl der Abbruch-Protokolle zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetAbbruchProtokolleCount(DBManager $db)
	{
		if( $this->pkey==-1 )return 0;
		$data=$db->SelectAssoc("SELECT count(pkey) as count FROM ".AbbruchProtokoll::TABLE_NAME." WHERE widerspruch=".$this->pkey );
		return (int)$data[0]["count"];
	}
	
	/**
	 * Gibt alle Abbruch-Protokolle zurück
	 * @param DBManager $db
	 * @return AbbruchProtokoll[]
	 */
	public function GetAbbruchProtokolle(DBManager $db)
	{
		if( $this->pkey==-1 )return Array();
		$data=$db->SelectAssoc("SELECT pkey FROM ".AbbruchProtokoll::TABLE_NAME." WHERE widerspruch=".$this->pkey." ORDER BY nummer DESC");
		$objects=Array();
		for($a=0; $a<count($data); $a++){
			$object=new AbbruchProtokoll($db);
			if( $object->Load($data[$a]["pkey"], $db) )$objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Gibt das letzte Abbruch-Protokoll zurück
	 * @param DBManager $db
	 * @return AbbruchProtokoll
	 */
	public function GetLastAbbruchProtokoll(DBManager $db)
	{
		if( $this->pkey==-1 )return null;
		$data=$db->SelectAssoc("SELECT pkey FROM ".AbbruchProtokoll::TABLE_NAME." WHERE widerspruch=".$this->pkey." ORDER BY nummer DESC LIMIT 1");
		$object=null;
		if( count($data)==1 )
		{
			$object=new AbbruchProtokoll($db);
			if( $object->Load($data[0]["pkey"], $db)!==true )$object=null;
		}
		return $object;
	}
	
	/**
	 * Ordnet dem AbbruchProtokoll diesen Widerspruch zu
	 * @param DBManager $db
	 * @param AbbruchProtokoll $abbruchProtokoll
	 * @return bool
	 */
	public function AddAbbruchProtokoll(DBManager $db, AbbruchProtokoll $abbruchProtokoll)
	{
		if ($this->pkey==-1) return false;
		// Dem AbbruchProtokoll diesen Widerspruch zuweisen...
		return $abbruchProtokoll->SetWiderspruch($this);
	}
	
	/**
	 * Gibt die Anzahl der Rückweisungsbegründungen zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetRueckweisungsBegruendungenCount(DBManager $db, $type=-1)
	{
		if( $this->pkey==-1 )return 0;
		$data=$db->SelectAssoc("SELECT count(pkey) as count FROM ".RueckweisungsBegruendung::TABLE_NAME." WHERE ".($type==-1 ? "" : "rwType=".(int)$type." AND ")." widerspruch=".$this->pkey );
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
		$data=$db->SelectAssoc("SELECT pkey FROM ".RueckweisungsBegruendung::TABLE_NAME." WHERE ".($type==-1 ? "" : "rwType=".(int)$type." AND ")." widerspruch=".$this->pkey." ORDER BY nummer DESC");
		$objects=Array();
		for($a=0; $a<count($data); $a++){
			$object=new RueckweisungsBegruendung($db);
			if( $object->Load($data[$a]["pkey"], $db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Gibt die letzte Rückweisungsbegründung zurück
	 * @param DBManager $db
	 * @return RueckweisungsBegruendung
	 */
	public function GetLastRueckweisungsBegruendung(DBManager $db, $type=-1)
	{
		if ($this->pkey==-1) return null;
		$data = $db->SelectAssoc("SELECT pkey FROM ".RueckweisungsBegruendung::TABLE_NAME." WHERE ".($type==-1 ? "" : "rwType=".(int)$type." AND ")." widerspruch=".$this->pkey." ORDER BY nummer DESC LIMIT 1");
		$object = null;
		if (count($data)==1)
		{
			$object = new RueckweisungsBegruendung($db);
			if ($object->Load($data[0]["pkey"], $db)!==true) $object = null;
		}
		return $object;
	}
	
	/**
	 * Ordnet der Rückweisungsbegründung diesen Widerspruch zu
	 * @param DBManager $db
	 * @param RueckweisungsBegruendung $rueckweisungsBegruendung
	 * @param int $type
	 * @return boolean
	 */
	public function AddRueckweisungsBegruendung(DBManager $db, RueckweisungsBegruendung $rueckweisungsBegruendung, $type)
	{
		if ($this->pkey==-1) return false;
		// Dem RueckweisungsBegruendung diesen Widerspruch zuweisen...
		return ($rueckweisungsBegruendung->SetType((int)$type) && $rueckweisungsBegruendung->SetWiderspruch($this));
	}
	
	/**
	 * Erzeugt einen Folgewiderspruch auf Basis dieses Widerspruchs
	 * @param DBManager $db
	 * @return Widerspruch
	 */
	public function CreateFolgewiderspruch(DBManager $db)
	{
		// Klone erzugen
		$folgeWiderspruch=clone $this;
		// Paramter zurücksetzen
		$folgeWiderspruch->pkey=-1;
		$folgeWiderspruch->widerspruchsNummer=-1;
		if ($folgeWiderspruch->Store($db)===true)
		{
			// Widerspruchspunkte Klonen
			$wsp=$this->GetWiderspruchspunkte($db);
			for ($a=0; $a<count($wsp); $a++)
			{
				$wsp[$a]->Copy($db, $folgeWiderspruch);
			}
			// Folgewiderspruch zurückgeben
			return $folgeWiderspruch;
		}
		return null;
	}
	
	/**
	 * Return the related contract
	 * @return Contract
	 */
	public function GetContract()
	{
		$year = $this->GetAbrechnungsJahr();
		if ($year==null) return null;
		return $year->GetContract();
	}
	
	/**
	 * Return the current currency
	 * @param DBManager $db
	 * @return string 
	 */
	public function GetCurrency(DBManager $db)
	{
		$process = $this->GetProcessStatus($db);
		if ($process==null) return "";
		return $process->GetCurrency();
	}
	
	/**
	 * Return the current country
	 * @return string 
	 */
	public function GetCountry()
	{
		$contract = $this->GetContract();
		if ($contract==null) return "";
		$shop = $contract->GetShop();
		if ($shop==null) return "";
		$location = $shop->GetLocation();
		if ($location==null) return "";
		return $location->GetCountry();
	}
	
	/**
	 * Return a list with all placeholders with preview data
	 * @param DBManager $db
	 * @return Array
	 */
	static public function GetPlaceholderPreview(DBManager $db)
	{
		$placeholders = Array();
		$widerspruch = new Widerspruch($db);
		if ($widerspruch->Load(324, $db)===true)
		{
			$placeholders = $widerspruch->GetPlaceholders($db);
		}
		return $placeholders;
	}
	
	/**
	 * Return a array with all placehodlers (array keys) and the corresponding values (array values)
	 * @return array
	 */
	protected function GetPlaceholders(DBManager $db)
	{
		// Datumsformat
		$dateFormat = "";
		$date = StandardTextManager::GetStandardTextById($db, StandardTextManager::STM_DATE);
		if ($date!=null) $dateFormat = $date->GetStandardText($this->GetLetterLanguage());
		if (trim($dateFormat)=="") $dateFormat = "d.m.Y";
		
		// get all needed data objects for placeholders
		$process = $this->GetProcessStatus($db); 
		// build placeholder array
		$placeHolders = Array(	// Sonstiges
								"%DATUM%" => date($dateFormat, time()),
								"%DATUM_ABSCHLUSS%" => ($process!=null ? ($process->GetAbschlussdatum()!=0 ? date($dateFormat, $process->GetAbschlussdatum()) : "-") : "-"),
								"%KUERZEL%" => $this->GetVersenderkuerzel(),
								"%UNTERSCHRIFT_1%" => $this->GetUnterschrift1(),
								"%UNTERSCHRIFT_2%" => $this->GetUnterschrift2(),
								"%FUNKTION_UNTERSCHRIFT_1%" => $this->GetFunktionUnterschrift1(),
								"%FUNKTION_UNTERSCHRIFT_2%" => $this->GetFunktionUnterschrift2(),
							);
		// Shop
		$shop = ($process!=null ? $process->GetShop() : null);
		if ($shop!=null)
		{
			$placeHoldersTemp = $shop->GetPlaceholders($db, $this->GetLetterLanguage());
			foreach ($placeHoldersTemp as $key => $value) 
			{
				$placeHolders[$key] = $value;
			}
		}
		// Location
		$location = ($process!=null ? $process->GetLocation() : null);
		if ($location!=null)
		{
			$placeHoldersTemp = $location->GetPlaceholders($db, $this->GetLetterLanguage());
			foreach ($placeHoldersTemp as $key => $value) 
			{
				$placeHolders[$key] = $value;
			}
		}
		// Abrehnungsjahr
		$abrechnungsjahr = $this->GetAbrechnungsJahr();
		if ($abrechnungsjahr!=null)
		{
			$placeHoldersTemp = $abrechnungsjahr->GetPlaceholders($db, $this->GetLetterLanguage());
			foreach ($placeHoldersTemp as $key => $value) 
			{
				$placeHolders[$key] = $value;
			}
		}
		// Kostenmanager FMS
		$addressData = ($process!=null ? ($process->GetResponsibleRSUser()!=null ? $process->GetResponsibleRSUser()->GetAddressData() : null ) : null);
		if ($addressData!=null)
		{
			$placeHoldersTemp = $addressData->GetPlaceholders($db, $this->GetLetterLanguage(), 'KM');
			foreach ($placeHoldersTemp as $key => $value) 
			{
				$placeHolders[$key] = $value;
			}
		}
		// Ansprechpartner Kunde
		$addressData = ($process!=null ? ($process->GetResponsibleCustomer()!=null ? $process->GetResponsibleCustomer()->GetAddressData() : null ) : null);
		if ($addressData!=null)
		{
			$placeHoldersTemp = $addressData->GetPlaceholders($db, $this->GetLetterLanguage(), 'KU');
			foreach ($placeHoldersTemp as $key => $value) 
			{
				$placeHolders[$key] = $value;
			}
		}
		// Ansprechpartner Vermieter/Verwalter/Anwalt
		$addressData = $this->GetAnsprechpartner();
		if ($addressData!=null)
		{
			$placeHoldersTemp = $addressData->GetPlaceholders($db, $this->GetLetterLanguage(), 'AP');
			foreach ($placeHoldersTemp as $key => $value) 
			{
				$placeHolders[$key] = $value;
			}
		}
		// return filled placeholder array
		return $placeHolders;
	}
	
	/**
	 * Replace all placeholders in subject with the corresponding values
	 * @param string $subject
	 * @param boolean $utf8decode
	 * @return string
	 */
	public function ReplacePlaceholders(DBManager $db, $subject, $utf8decode=false, $additionalPlaceholders=Array())
	{
		$placeholders = $this->GetPlaceholders($db);
		// add additional placeholders
		foreach ($additionalPlaceholders as $key => $value)
		{
			$placeholders[$key] = $value;
		}
		foreach ($placeholders as $key => $value)
		{
			if ($utf8decode)
			{
				$subject=str_replace($key, utf8_decode($value), $subject);
			}
			else
			{
				$subject=str_replace($key, $value, $subject);
			}
		}
		return $subject;
	}
	
	/**
	 * Erzeugt das Dokument für den Widerspruch
	 * @param DBManager $db
	 * @param int $fileFormat
	 * @param int $type
	 * @param array additionalData
	 * @return string
	 */
	public function CreateDocument(DBManager $db, $fileFormat, $type=DOCUMENT_TYPE_ANSCHREIBEN, $additionalData = Array() )
	{
		global $DOMAIN_FILE_SYSTEM_ROOT;
		if( $fileFormat==DOCUMENT_TYPE_PDF )
		{
			$header_html = "";
			$footer_html = "";
			$pdfMergin = Array('left' => 0, 'right' => 0, 'top' => 0, 'bottom' => 0);
			ob_start();
			if ($type==DOCUMENT_TYPE_ANHANG )include_once("template_widerspruch_anhang.inc.php5");
			if ($type==DOCUMENT_TYPE_RECHNUNG )include_once("template_rechnung.inc.php5");
			$CONTENT = ob_get_contents();
			ob_end_clean();
			//echo $CONTENT;
			//exit;
			include_once("html2pdf.php5");
			// Temporären Dateinamen erzeugen
			$filePostName="";
			if ($type==DOCUMENT_TYPE_ANSCHREIBEN )$filePostName="_anschreiben";
			if ($type==DOCUMENT_TYPE_ANHANG )$filePostName="_anhang";
			if ($type==DOCUMENT_TYPE_RECHNUNG )$filePostName="_rechnung";
			$pdfContent = "";
			// PDF erzeugen...
			if ($type==DOCUMENT_TYPE_ANSCHREIBEN )$pdfContent = convert_to_pdf($CONTENT, "A4", 700, false);
			if ($type==DOCUMENT_TYPE_ANHANG )
			{
				//$CONTENT = str_replace("<p>", "", $CONTENT);
				//$CONTENT = str_replace("</p>", "", $CONTENT);
				$pdfContent = convert_to_pdf($CONTENT, "A4", 990, true, '', false, $MARGINS, $HEADER, $FOOTER);
			}
			if ($type==DOCUMENT_TYPE_RECHNUNG )$pdfContent = convert_to_pdf($CONTENT, "A4", 700, false, '', false, $pdfMergin, $header_html, $footer_html);
			if ($pdfContent!="")
			{
				// Daten zurückgeben
				return Array("content" => $pdfContent, "extension" => "pdf");
			}
			// PDF konnt nicht erzeugt werden
			return -1;
		}
		elseif( $fileFormat==DOCUMENT_TYPE_RTF )
		{
			$rtfVorlage="";
			$anschreibenVorlageBetreff = $this->GetLetterSubject();
			$anschreibenVorlageText = $this->GetLetter();
			if( $type==DOCUMENT_TYPE_ANSCHREIBEN ){
				$processStatus = $this->GetProcessStatus($db);
				$company = ($processStatus!=null ? $processStatus->GetCompany() : null); 
				// Load deafult templates if no text is available
				if ($anschreibenVorlageBetreff=="")
				{
					$standardText = ($company!=null ? $company->GetAnschreibenVorlageBetreff() : null);
					if ($standardText==null) $standardText = StandardTextManager::GetStandardTextById($db, StandardTextManager::STM_WSANSCHREIBEN_SUBJECT);
					if ($standardText!=null) 
					{
						$anschreibenVorlageBetreff = $standardText->GetStandardText($this->GetLetterLanguage());
					}
				}
				if ($anschreibenVorlageText=="")
				{
					$standardText = ($company!=null ? $company->GetAnschreibenVorlageText() : null);
					if ($standardText==null) $standardText = StandardTextManager::GetStandardTextById($db, StandardTextManager::STM_WSANSCHREIBEN);
					if ($standardText!=null) 
					{
						$anschreibenVorlageText = $standardText->GetStandardText($this->GetLetterLanguage());
					}
				}
				$anschreiben = ($company!=null ? $company->GetAnschreiben($this->GetLetterLanguage()) : null);
				$anschreibenFile = ($anschreiben!=null ? FM_FILE_ROOT.$anschreiben->GetDocumentPath() : "");
				if( $anschreibenFile=="" )return -5;
				$rtfVorlage=$anschreibenFile;
			}
			// Datei öffnen...
			if (trim($rtfVorlage)!="" && is_file($rtfVorlage))
			{
				$fp = fopen($rtfVorlage, "rb");
				if ($fp!==false)
				{
					// ... und in Speicher laden
					$fileContent = fread($fp, filesize($rtfVorlage) );
					fclose($fp);
					$anschreibenVorlageBetreff = str_replace("\n", "\n\\par ", $this->ReplacePlaceholders($db, $anschreibenVorlageBetreff));
					$anschreibenVorlageText = str_replace("\n", "\n\\par ", $this->ReplacePlaceholders($db, $anschreibenVorlageText));
					$fileContent = $this->ReplacePlaceholders($db, $fileContent, true, Array("%ANSCHREIBEN_BETREFF%" => $anschreibenVorlageBetreff, "%ANSCHREIBEN_TEXT%" => $anschreibenVorlageText));
					// Daten zurückgeben
					return Array("content" => $fileContent, "extension" => "rtf");
				}
				// RTF konnte nicht geöffnet werden
				return -2;
			}
			else
			{
				// Keine RTF-Vorlage vorhanden
				return -3;
			}
		}
		// TODO: In PDF bzw. RTF wandeln und zurückgeben
		return -1;
	}
	
	/**
	 * Return the corresponding ProcessStatus
	 * @param DBManager $db
	 * @return ProcessStatus
	 */
	public function GetProcessStatus(DBManager $db)
	{
		$abrechnungsjahr = $this->GetAbrechnungsJahr();
		if ($abrechnungsjahr!=null) return $abrechnungsjahr->GetProcessStatus($db);
		return $this->GetProcessStatusGroup();
	}
	
}
?>