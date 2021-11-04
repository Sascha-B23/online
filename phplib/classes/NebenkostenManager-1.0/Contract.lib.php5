<?php
/**
 * Diese Klasse repräsentiert einen Vertrag
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class Contract extends DBEntry implements DependencyFileDescription
{
	/**
	 * Datenbankname und Spalten
	 */
	const TABLE_NAME = "contract";

	/**
	 * Prefix string for ID
	 */
	const ID_PREFIX = 'V';
	
	/**
	 * Dirtflags
	 */
	const DIRTFLAG_NONE = 0;
	const DIRTFLAG_CURRENCY = 1;
	
	/**
	 * Möglichkeiten für 'Nur Auszüge vorhanden'
	 * @var enum 
	 */
	const CONTRACT_UNKNOWN = 0;
	const CONTRACT_YES = 1;
	const CONTRACT_NO = 2;
	
	/**
	 * Möglichkeiten für 'Mietvertragsdokumente vollständig'
	 * @var enum 
	 */
	const MVC_UNKNOWN = 0;
	const MVC_NO_ADDITIONAL_FILES = 1;
	const MVC_NEW_FILES_ADDED = 2;
	const MVC_NEW_FILES_AVAILABLE_NOT_ADDED = 3;
	
	/**
	 * Flag that indicates which data have to be written to group members
	 * @var int 
	 */
	private $dirtFlag = self::DIRTFLAG_NONE;
	
	/**
	 * File relation manager
	 * @var FileRelationManager 
	 */
	protected $fileRelationManager = null;
	
	/**
	 * Mietfläche
	 * @var float
	 */
	protected $mietflaeche_qm = 0.0;
	
	/**
	 * Umlagefläche
	 * @var float
	 */
	protected $umlageflaeche_qm = 0.0;

	/**
	 * Allgemeine Texte für FMS-Kostenarten
	 * @var array 
	 */
	protected $rsKostenartAllgemein = Array();
	
	/**
	 * Bemerkung nicht vereinbarte Abrechnungspositionen
	 * @var string 
	 */
	protected $bemerkungNichtVereinbarteAbrPos = "";
	
	/**
	 * Deckelung Instandhaltung
	 * @var int
	 */
	protected $deckelungInstandhaltung = 0;
	
	/**
	 * Deckelung Instandhaltung Beschreibung
	 * @var string
	 */
	protected $deckelungInstandhaltungBeschreibung = "";
	
	/**
	 * Deckelung Verwaltung und Management
	 * @var int
	 */
	protected $deckelungVerwaltungManagement = 0;
	
	/**
	 * Deckelung Verwaltung und Management Beschreibung
	 * @var string
	 */
	protected $deckelungVerwaltungManagementBeschreibung = "";
	
	/**
	 * Verwaltung und Management auf gesetzliche Definition beschränkt
	 * @var int
	 */
	protected $gesetzlicheDefinitionVerwaltungManagement = 0;
	
	/**
	 * Allgemeine Bemerkung Umlageschlüssel
	 * @var string
	 */
	protected $bemerkungUmlageschluesselAllgemein = "";
	
	/**
	 * Allgemeine Bemerkung Gesamtkostendeckelung
	 * @var string
	 */
	protected $bemerkungGesamtkostendeckelungAllgemein = "";
	
	/**
	 * Allgemeine Bemerkung Zusammensetzung Gesamtkosten
	 * @var string
	 */
	protected $bemerkungZusammensetzungGesamtkostenAllgemein = "";
	
	/**
	 * Zurückbehaltung ausgeschlossen
	 * @var int
	 */
	protected $zurueckBehaltungAusgeschlossen = self::CONTRACT_UNKNOWN;

	/**
	 * Zurückbehaltung ausgeschlossen Beschreibung
	 * @var string
	 */
	protected $zurueckBehaltungAusgeschlossenBeschreibung="";

	/**
	 * Nur Grundsteuererhöhung Umlegbar
	 * @var int
	 */
	protected $nurGrundsteuererhoehungUmlegbar=0;

	/**
	 * Nur Grundsteuererhöhung Umlegbar Beschreibung
	 * @var string
	 */
	protected $nurGrundsteuererhoehungUmlegbarBeschreibung="";

	/**
	 * Nur Versicherungserhoehung umlegbar
	 * @var int
	 */
	protected $nurVersicherungserhoehungUmlegbar=0;

	/*
	 * Nur Versicherungserhoehung umlegbar Beschreibung
	 * @var string
	 */
	protected $nurVersicherungserhoehungUmlegbarBeschreibung="";

	/**
	 * Sonstige Besonderheiten
	 * @var int
	 */
	protected $sonstigeBesonderheiten=0;

	/**
	 * Sonstige Besonderheiten Beschreibung
	 * @var string
	 */
	protected $sonstigeBesonderheitenBeschreibung="";

	/**
	 * Werbegemeinschaft
	 * @var int
	 */
	protected $werbegemeinschaft=0;

	/**
	 * Werbegemeinschaft Beschreibung
	 * @var string
	 */
	protected $werbegemeinschaftBeschreibung="";
	
	/**
	 * Sonstige Besonderheiten Beschreibung
	 * @var boolean
	 */
	protected $vertragErfasst=false;
	
	/**
	 * Währung im ISO-4217-Code
	 * @var string
	 */	
	protected $currency="EUR";
	
	/**
	 * Mietvertragsbeginn
	 * @var int 
	 */
	protected $mvBeginn = 0;
	
	/**
	 * Erstmals mögliches Vertragsende
	 * @var int
	 */
	protected $mvEndeErstmalsMoeglich = 0;
	
	/**
	 * Aktuelles Mietvertragsende
	 * @var int
	 */
	protected $mvEnde = 0;
	
	/**
	 * Nur Vertragsauszüge vorhanden
	 * @var int
	 */
	protected $nurAuszuegeVorhanden = self::CONTRACT_UNKNOWN;
	
	/**
	 * Mietvertragsdokumente vollständig
	 * @var int 
	 */
	protected $mietvertragsdokumenteVollstaendig = self::MVC_UNKNOWN;
	
	/**
	 * Eigentümer
	 * @var AddressBase
	 */
	protected $eigentuemer = null;
	
	/**
	 * Verwalter
	 * @var AddressBase
	 */
	protected $verwalter = null;

	/**
	 * Zugehörige Customer-Laden
	 * @var CShop
	 */
	protected $cShop=null;

	/**
	 * Stammdatenblatt
	 * @var File
	 */
	protected $stammdatenblatt = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 */
	public function Contract(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("mietflaeche_qm", "umlageflaeche_qm", "deckelungInstandhaltung", "deckelungInstandhaltungBeschreibung",  "deckelungVerwaltungManagement", "deckelungVerwaltungManagementBeschreibung", "gesetzlicheDefinitionVerwaltungManagement",  "zurueckBehaltungAusgeschlossen", "zurueckBehaltungAusgeschlossenBeschreibung", "nurGrundsteuererhoehungUmlegbar", "nurGrundsteuererhoehungUmlegbarBeschreibung", "nurVersicherungserhoehungUmlegbar", "nurVersicherungserhoehungUmlegbarBeschreibung", "sonstigeBesonderheiten", "sonstigeBesonderheitenBeschreibung", "werbegemeinschaft", "werbegemeinschaftBeschreibung", "vertragErfasst", "bemerkungUmlageschluesselAllgemein", "bemerkungGesamtkostendeckelungAllgemein", "bemerkungZusammensGeskostAllg", "currency", "mvBeginn", "mvEndeErstmalsMoeglich", "mvEnde", "nurAuszuegeVorhanden", "mietvertrVollstaendig", "bemerkungNichtVereinbarteAbrPos", "eigentuemer", "eigentuemerTyp",  "verwalter", "verwalterTyp", "cShop", "stammdatenblatt" );
		$dbConfig->rowParam = Array("DECIMAL(20,2)", "DECIMAL(20,2)",	"INT",						"LONGTEXT",							  "INT",							"LONGTEXT",									"INT",											"INT",							"LONGTEXT",										"INT",								"LONGTEXT",										"INT",								"LONGTEXT",										"INT",					"LONGTEXT",								"INT",					"LONGTEXT",								"INT",				"LONGTEXT",							"LONGTEXT",						"LONGTEXT",						"VARCHAR(3)","BIGINT",	"BIGINT",					"BIGINT", "INT",					"INT",								"LONGTEXT",			"BIGINT",		"INT",				"BIGINT",	"INT",			 "BIGINT",			 "BIGINT");
		$dbConfig->rowIndex = Array("eigentuemer", "verwalter", "cShop");
		// Spalten für FMS-Kostenarten hinzufügen
		$rsKostenarten = RSKostenartManager::GetSystemKostenarten();
		for ($a=0; $a<count($rsKostenarten); $a++)
		{
			$dbConfig->rowName[] = "rsKostenartAllgemein_".$rsKostenarten[$a]['ID'];
			$dbConfig->rowParam[] = "LONGTEXT";
		}
		parent::__construct($db, $dbConfig);
		
		$this->fileRelationManager = new FileRelationManager($db, $this);
	}
	
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @param DBManager $db
	 * @return bool
	 */
	public function IsDeletable(&$db)
	{
		if( $this->GetAbrechnungsJahreCount($db)>0 )return false;
		return parent::IsDeletable($db);
	}
	
	/**
	 * Löscht das Objekt aus der DB
	 * @param DBManager $db
	 * @return bool
	 */
	public function DeleteMe(&$db)
	{
		if (!$this->RemoveAllFiles($db)) return false;
		return parent::DeleteMe($db);
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
			// get all other shops of the group this shop is in
			$others = WorkflowManager::GetContractsInSameProcessStatusGroup($db, $this);
			foreach ($others as $other)
			{
				//echo "UPDATE ".$other->GetPKey()." <br />";
				if ($this->dirtFlag & self::DIRTFLAG_CURRENCY)
				{
					$other->currency = $this->currency;
				}	
				// store changes to child
				$other->Store($db);
			}
			// reset dirt flag
			$this->dirtFlag = self::DIRTFLAG_NONE;
		}
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param DBManager $db
	 * @param array $rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param array $rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int			Im Erfolgsfall true oder 
	 *							-1	Zugehöriger Shop nicht gesetzt
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if( $this->cShop==null || $this->cShop->GetPKey()==-1  )return -1;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "mietflaeche_qm";
		$rowData[]= $this->mietflaeche_qm;
		$rowName[]= "umlageflaeche_qm";
		$rowData[]= $this->umlageflaeche_qm;
		$rowName[]= "deckelungInstandhaltung";
		$rowData[]= $this->deckelungInstandhaltung;
		$rowName[]= "deckelungInstandhaltungBeschreibung";
		$rowData[]= $this->deckelungInstandhaltungBeschreibung;
		$rowName[]= "deckelungVerwaltungManagement";
		$rowData[]= $this->deckelungVerwaltungManagement;
		$rowName[]= "deckelungVerwaltungManagementBeschreibung";
		$rowData[]= $this->deckelungVerwaltungManagementBeschreibung;
		$rowName[]= "gesetzlicheDefinitionVerwaltungManagement";
		$rowData[]= $this->gesetzlicheDefinitionVerwaltungManagement;	
		// Spalten für FMS-Kostenarten hinzufügen
		$rsKostenarten = RSKostenartManager::GetSystemKostenarten();
		for ($a=0; $a<count($rsKostenarten); $a++)
		{
			$rowName[]= 'rsKostenartAllgemein_'.$rsKostenarten[$a]['ID'];
			$rowData[]= $this->rsKostenartAllgemein[$rsKostenarten[$a]['ID']];
		}
		$rowName[]= "bemerkungUmlageschluesselAllgemein";
		$rowData[]= $this->bemerkungUmlageschluesselAllgemein;
		$rowName[]= "bemerkungGesamtkostendeckelungAllgemein";
		$rowData[]= $this->bemerkungGesamtkostendeckelungAllgemein;
		$rowName[]= "bemerkungZusammensGeskostAllg";
		$rowData[]= $this->bemerkungZusammensetzungGesamtkostenAllgemein;		
		$rowName[]= "zurueckBehaltungAusgeschlossen";
		$rowData[]= $this->zurueckBehaltungAusgeschlossen;
		$rowName[]= "zurueckBehaltungAusgeschlossenBeschreibung";
		$rowData[]= $this->zurueckBehaltungAusgeschlossenBeschreibung;
		$rowName[]= "nurGrundsteuererhoehungUmlegbar";
		$rowData[]= $this->nurGrundsteuererhoehungUmlegbar;
		$rowName[]= "nurGrundsteuererhoehungUmlegbarBeschreibung";
		$rowData[]= $this->nurGrundsteuererhoehungUmlegbarBeschreibung;
		$rowName[]= "nurVersicherungserhoehungUmlegbar";
		$rowData[]= $this->nurVersicherungserhoehungUmlegbar;
		$rowName[]= "nurVersicherungserhoehungUmlegbarBeschreibung";
		$rowData[]= $this->nurVersicherungserhoehungUmlegbarBeschreibung;
		$rowName[]= "sonstigeBesonderheiten";
		$rowData[]= $this->sonstigeBesonderheiten;
		$rowName[]= "sonstigeBesonderheitenBeschreibung";
		$rowData[]= $this->sonstigeBesonderheitenBeschreibung;
		$rowName[]= "werbegemeinschaft";
		$rowData[]= $this->werbegemeinschaft;
		$rowName[]= "werbegemeinschaftBeschreibung";
		$rowData[]= $this->werbegemeinschaftBeschreibung;
		$rowName[]= "vertragErfasst";
		$rowData[]= $this->vertragErfasst ? 1 : 0;
		$rowName[]= "currency";
		$rowData[]= $this->currency;
		$rowName[]= "mvBeginn";
		$rowData[]= $this->mvBeginn;
		$rowName[]= "mvEndeErstmalsMoeglich";
		$rowData[]= $this->mvEndeErstmalsMoeglich;
		$rowName[]= "mvEnde";
		$rowData[]= $this->mvEnde;
		$rowName[]= "nurAuszuegeVorhanden";
		$rowData[]= $this->nurAuszuegeVorhanden;
		$rowName[]= "mietvertrVollstaendig";
		$rowData[]= $this->mietvertragsdokumenteVollstaendig;
		$rowName[]= "bemerkungNichtVereinbarteAbrPos";
		$rowData[]= $this->bemerkungNichtVereinbarteAbrPos;
		$rowName[]= "eigentuemer";
		$rowData[]= $this->eigentuemer==null ? -1 : $this->eigentuemer->GetPKey();
		$rowName[]= "eigentuemerTyp";
		$rowData[]= $this->eigentuemer==null ? AddressBase::AM_CLASS_UNKNOWN : $this->eigentuemer->GetClassType();
		$rowName[]= "verwalter";
		$rowData[]= $this->verwalter==null ? -1 : $this->verwalter->GetPKey();
		$rowName[]= "verwalterTyp";
		$rowData[]= $this->verwalter==null ? AddressBase::AM_CLASS_UNKNOWN : $this->verwalter->GetClassType();
		$rowName[]= "cShop";
		$rowData[]= $this->cShop==null ? -1 : $this->cShop->GetPKey();
		$rowName[]= "stammdatenblatt";
		$rowData[]= $this->stammdatenblatt==null ? -1 : $this->stammdatenblatt->GetPKey();
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param DBManager $db
	 * @param array $data		Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool	
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->mietflaeche_qm = $data['mietflaeche_qm'];
		$this->umlageflaeche_qm = $data['umlageflaeche_qm'];
		$this->deckelungInstandhaltung = $data['deckelungInstandhaltung'];
		$this->deckelungInstandhaltungBeschreibung = $data['deckelungInstandhaltungBeschreibung'];
		$this->deckelungVerwaltungManagement = $data['deckelungVerwaltungManagement'];
		$this->deckelungVerwaltungManagementBeschreibung = $data['deckelungVerwaltungManagementBeschreibung'];
		$this->gesetzlicheDefinitionVerwaltungManagement = $data['gesetzlicheDefinitionVerwaltungManagement'];		
		// Spalten für FMS-Kostenarten hinzufügen
		$rsKostenarten = RSKostenartManager::GetSystemKostenarten();
		for ($a=0; $a<count($rsKostenarten); $a++)
		{
			$this->rsKostenartAllgemein[$rsKostenarten[$a]['ID']] = $data['rsKostenartAllgemein_'.$rsKostenarten[$a]['ID']];
		}
		$this->bemerkungUmlageschluesselAllgemein = $data['bemerkungUmlageschluesselAllgemein'];
		$this->bemerkungGesamtkostendeckelungAllgemein = $data['bemerkungGesamtkostendeckelungAllgemein'];
		$this->bemerkungZusammensetzungGesamtkostenAllgemein = $data['bemerkungZusammensGeskostAllg'];
		
		$this->zurueckBehaltungAusgeschlossen = (int)$data['zurueckBehaltungAusgeschlossen'];
		$this->zurueckBehaltungAusgeschlossenBeschreibung = $data['zurueckBehaltungAusgeschlossenBeschreibung'];
		$this->nurGrundsteuererhoehungUmlegbar = $data['nurGrundsteuererhoehungUmlegbar'];
		$this->nurGrundsteuererhoehungUmlegbarBeschreibung = $data['nurGrundsteuererhoehungUmlegbarBeschreibung'];
		$this->nurVersicherungserhoehungUmlegbar = $data['nurVersicherungserhoehungUmlegbar'];
		$this->nurVersicherungserhoehungUmlegbarBeschreibung = $data['nurVersicherungserhoehungUmlegbarBeschreibung'];
		$this->sonstigeBesonderheiten = $data['sonstigeBesonderheiten'];
		$this->sonstigeBesonderheitenBeschreibung = $data['sonstigeBesonderheitenBeschreibung'];
		$this->werbegemeinschaft = $data['werbegemeinschaft'];
		$this->werbegemeinschaftBeschreibung = $data['werbegemeinschaftBeschreibung'];
		$this->vertragErfasst = $data['vertragErfasst']==1 ? true : false;
		$this->currency = $data['currency'];
		$this->mvBeginn = (int)$data['mvBeginn'];
		$this->mvEndeErstmalsMoeglich = (int)$data['mvEndeErstmalsMoeglich'];
		$this->mvEnde = (int)$data['mvEnde'];
		$this->nurAuszuegeVorhanden = (int)$data['nurAuszuegeVorhanden'];
		$this->mietvertragsdokumenteVollstaendig = (int)$data['mietvertrVollstaendig'];
		$this->bemerkungNichtVereinbarteAbrPos = $data['bemerkungNichtVereinbarteAbrPos'];	
		if ($data['eigentuemer']!=-1)
		{
			$this->eigentuemer = AddressManager::GetAddressElementByPkeyAndType($db, (int)$data['eigentuemerTyp'], $data['eigentuemer']);
		}
		else
		{
			$this->eigentuemer=null;
		}
		if ($data['verwalter']!=-1)
		{
			$this->verwalter = AddressManager::GetAddressElementByPkeyAndType($db,(int)$data['verwalterTyp'], $data['verwalter']);
		}
		else
		{
			$this->verwalter=null;
		}
		
		if( $data['cShop']!=-1 )
		{
			$this->cShop = CustomerManager::GetShopByPkey($db, $data['cShop']);
		}
		else
		{
			$this->cShop=null;
		}

		if ($data["stammdatenblatt"]!=-1)
		{
			$this->stammdatenblatt = new File($db);
			if ($this->stammdatenblatt->Load((int)$data["stammdatenblatt"], $db)!==true) $this->stammdatenblatt = null;
		}
		else
		{
			$this->stammdatenblatt = null;
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function GetId()
	{
		if ($this->GetPKey()==-1) return "";
		return self::ID_PREFIX.$this->GetPKey();
	}

	/**
	 * Return Description
	 */
	public function GetDependencyFileDescription()
	{
		$shop = $this->GetShop();
		return ($shop==null ? "?" : $shop->GetDependencyFileDescription()).DependencyFileDescription::SEPERATOR."Mietvertrag ".date("d.m.Y", $this->GetMvBeginn())." bis ".date("d.m.Y", $this->GetMvEnde());
	}
	
	/**
	 * Setzt die Mietfläche in qm
	 * @param float $mietflaeche_qm
	 * @return bool
	 */
	public function SetMietflaecheQM($mietflaeche_qm)
	{
		if( !is_numeric($mietflaeche_qm) )return false;
		$this->mietflaeche_qm=(float)$mietflaeche_qm;
		return true;
	}
	
	/**
	 * Gibt die Mietfläche zurück
	 * @return float
	 */
	public function GetMietflaecheQM()
	{
		return $this->mietflaeche_qm;
	}
	
	/**
	 * Setzt die Umlagefläche in qm
	 * @param float $umlageflaeche_qm
	 * @return bool
	 */
	public function SetUmlageflaecheQM($umlageflaeche_qm)
	{
		if( !is_numeric($umlageflaeche_qm) )return false;
		$this->umlageflaeche_qm=(float)$umlageflaeche_qm;
		return true;
	}
	
	/**
	 * Gibt die Umlagefläche zurück
	 * @return float Umlagefläche in qm
	 */
	public function GetUmlageflaecheQM()
	{
		return $this->umlageflaeche_qm;
	}
	
	/**
	 * Setzt die Bemerkung für nicht vereinbarte Abrechnungspositionen
	 * @param string $bemerkung
	 * @return bool
	 */
	public function SetBemerkungNichtVereinbarteAbrPos($bemerkung)
	{
		$this->bemerkungNichtVereinbarteAbrPos = $bemerkung;
		return true;
	}
	
	/**
	 * Gibt die Bemerkung für nicht vereinbarte Abrechnungspositionen zurück
	 * @return string
	 */
	public function GetBemerkungNichtVereinbarteAbrPos()
	{
		return $this->bemerkungNichtVereinbarteAbrPos;
	}
	
	/**
	 * Gibt den allgemeinen Text für eine FMS-Kostenart zurück
	 * @return string
	 */
	public function GetRSKostenartAllgemein($rsKostenart)
	{
		if( !is_int($rsKostenart) || !RSKostenartManager::IsSystemKostenart($rsKostenart) )return false;
		return $this->rsKostenartAllgemein[$rsKostenart];
	}
	
	/**
	 * Setzt den allgemeinen Text für eine FMS-Kostenart
	 * @param bool $text
	 * @return bool
	 */
	public function SetRSKostenartAllgemein($rsKostenart, $text)
	{
		if( !is_int($rsKostenart) || !RSKostenartManager::IsSystemKostenart($rsKostenart) )return false;
		$this->rsKostenartAllgemein[$rsKostenart] = $text;
		return true;
	}
	
	/**
	 * Setzt die Deckelung Instandhaltung
	 * @param bool $deckelungInstandhaltung
	 * @return bool
	 */
	public function SetDeckelungInstandhaltung($deckelungInstandhaltung)
	{
		if( !is_bool($deckelungInstandhaltung) )return false;
		$this->deckelungInstandhaltung=$deckelungInstandhaltung ? 1 : 0;
		return true;
	}
	
	/**
	 * Gibt die Deckelung Instandhaltung zurück
	 * @return bool
	 */
	public function GetDeckelungInstandhaltung()
	{
		return $this->deckelungInstandhaltung==1 ? true : false;
	}
		
	/**
	 * Setzt die Beschreibung Deckelung Instandhaltung
	 * @param string $deckelungInstandhaltungBeschreibung
	 * @return bool
	 */
	public function SetDeckelungInstandhaltungBeschreibung($deckelungInstandhaltungBeschreibung)
	{
		$this->deckelungInstandhaltungBeschreibung=$deckelungInstandhaltungBeschreibung;
		return true;
	}
	
	/**
	 * Gibt die Beschreibung Deckelung Instandhaltung zurück
	 * @return string
	 */
	public function GetDeckelungInstandhaltungBeschreibung()
	{
		return $this->deckelungInstandhaltungBeschreibung;
	}
	
	/**
	 * Setzt die Deckelung Verwaltung Management
	 * @param bool $on
	 * @return bool
	 */
	public function SetDeckelungVerwaltungManagement($on)
	{
		if( !is_bool($on) )return false;
		$this->deckelungVerwaltungManagement=($on ? 1 : 0);
		return true;
	}
	
	/**
	 * Gibt die Deckelung Verwaltung Management zurück
	 * @return bool
	 */
	public function GetDeckelungVerwaltungManagement()
	{
		return $this->deckelungVerwaltungManagement==1 ? true : false;
	}
		
	/**
	 * Setzt die Beschreibung Deckelung Verwaltung Management
	 * @param string $text
	 * @return bool
	 */
	public function SetDeckelungVerwaltungManagementBeschreibung($text)
	{
		$this->deckelungVerwaltungManagementBeschreibung=$text;
		return true;
	}
	
	/**
	 * Gibt die Beschreibung Deckelung Verwaltung Management zurück
	 * @return string
	 */
	public function GetDeckelungVerwaltungManagementBeschreibung()
	{
		return $this->deckelungVerwaltungManagementBeschreibung;
	}
	
	/**
	 * Setzt ob die Verwaltung und Management auf gesetzliche Definition beschränkt ist
	 * @param bool $on
	 * @return bool
	 */
	public function SetGesetzlicheDefinitionVerwaltungManagement($on)
	{
		if( !is_bool($on) )return false;
		$this->gesetzlicheDefinitionVerwaltungManagement=($on ? 1 : 0);
		return true;
	}
	
	/**
	 * Gibt zurück, ob Verwaltung und Management auf gesetzliche Definition beschränkt
	 * @return bool
	 */
	public function GetGesetzlicheDefinitionVerwaltungManagement()
	{
		return $this->gesetzlicheDefinitionVerwaltungManagement==1 ? true : false;
	}
	
	/**
	 * Gibt die allgemeine Bemerkung zum Umlageschlüssel zurück
	 * @return string
	 */
	public function GetBemerkungUmlageschluesselAllgemein()
	{
		return $this->bemerkungUmlageschluesselAllgemein;
	}
	
	/**
	 * Setzt die allgemeine Bemerkung zum Umlageschlüssel 
	 * @param bool $text
	 * @return bool
	 */
	public function SetBemerkungUmlageschluesselAllgemein($text)
	{
		$this->bemerkungUmlageschluesselAllgemein = $text;
		return true;
	}
		
	/**
	 * Gibt die allgemeine Bemerkung zur Gesamtkostendeckelung zurück
	 * @return string
	 */
	public function GetBemerkungGesamtkostendeckelungAllgemein()
	{
		return $this->bemerkungGesamtkostendeckelungAllgemein;
	}
	
	/**
	 * Setzt die allgemeine Bemerkung zur Gesamtkostendeckelung 
	 * @param string $text
	 * @return bool
	 */
	public function SetBemerkungGesamtkostendeckelungAllgemein($text)
	{
		$this->bemerkungGesamtkostendeckelungAllgemein = $text;
		return true;
	}
	
	/**
	 * Gibt die allgemeine Bemerkung zur Zusammensetzung der Gesamtkosten zurück
	 * @return string
	 */
	public function GetBemerkungZusammensetzungGesamtkostenAllgemein()
	{
		return $this->bemerkungZusammensetzungGesamtkostenAllgemein;
	}
	
	/**
	 * Setzt die allgemeine Bemerkung zur Zusammensetzung der Gesamtkosten
	 * @param string $text
	 * @return bool
	 */
	public function SetBemerkungZusammensetzungGesamtkostenAllgemein($text)
	{
		$this->bemerkungZusammensetzungGesamtkostenAllgemein = $text;
		return true;
	}
	
	/**
	 * Setzt Zurückbehaltung ausgeschlossen
	 * @param int $zurueckBehaltungAusgeschlossen
	 * @return bool
	 */
	public function SetZurueckBehaltungAusgeschlossen($zurueckBehaltungAusgeschlossen)
	{
		if (!is_int($zurueckBehaltungAusgeschlossen)) return false;
		$this->zurueckBehaltungAusgeschlossen = $zurueckBehaltungAusgeschlossen;
		return true;
	}
	
	/**
	 * Gibt die Zurückbehaltung ausgeschlossen zurück
	 * @return int
	 */
	public function GetZurueckBehaltungAusgeschlossen() 
	{
		return $this->zurueckBehaltungAusgeschlossen; 
	}		
		
	/**
	 * Setzt die Beschreibung Zurückbehaltung ausgeschlossen
	 * @param string $zurueckBehaltungAusgeschlossenBeschreibung
	 * @return bool
	 */
	public function SetZurueckBehaltungAusgeschlossenBeschreibung($zurueckBehaltungAusgeschlossenBeschreibung)
	{
		$this->zurueckBehaltungAusgeschlossenBeschreibung=$zurueckBehaltungAusgeschlossenBeschreibung;
		return true;
	}
	
	/**
	 * Gibt die Beschreibung Zurückbehaltung ausgeschlossen zurück
	 * @return string
	 */
	public function GetZurueckBehaltungAusgeschlossenBeschreibung()
	{
		return $this->zurueckBehaltungAusgeschlossenBeschreibung;
	}
	
	/**
	 * Setzt Nur Grundsteuererhöhung umlegbar
	 * @param bool $nurGrundsteuererhoehungUmlegbar
	 * @return bool
	 */
	public function SetNurGrundsteuererhoehungUmlegbar($nurGrundsteuererhoehungUmlegbar)
	{
		if( !is_bool($nurGrundsteuererhoehungUmlegbar) )return false;
		$this->nurGrundsteuererhoehungUmlegbar=$nurGrundsteuererhoehungUmlegbar ? 1 : 0;
		return true;
	}
	
	/**
	 * Gibt Nur Grundsteuererhöhung umlegbar zurück
	 * @return bool
	 */
	public function GetNurGrundsteuererhoehungUmlegbar()
	{
		return $this->nurGrundsteuererhoehungUmlegbar==1 ? true : false;
	}		
		
	/**
	 * Setzt die Beschreibung Nur Grundsteuererhöhung umlegbar
	 * @param string $nurGrundsteuererhoehungUmlegbarBeschreibung
	 * @return bool
	 */
	public function SetNurGrundsteuererhoehungUmlegbarBeschreibung($nurGrundsteuererhoehungUmlegbarBeschreibung)
	{
		$this->nurGrundsteuererhoehungUmlegbarBeschreibung=$nurGrundsteuererhoehungUmlegbarBeschreibung;
		return true;
	}
	
	/**
	 * Gibt die Beschreibung Nur Grundsteuererhöhung umlegbar zurück
	 * @return string
	 */
	public function GetNurGrundsteuererhoehungUmlegbarBeschreibung()
	{
		return $this->nurGrundsteuererhoehungUmlegbarBeschreibung;
	}
	
	/**
	 * Setzt Nur Versicherungserhöhung umlegbar
	 * @param bool $nurVersicherungserhoehungUmlegbar
	 * @return bool
	 */
	public function SetNurVersicherungserhoehungUmlegbar($nurVersicherungserhoehungUmlegbar)
	{
		if( !is_bool($nurVersicherungserhoehungUmlegbar) )return false;
		$this->nurVersicherungserhoehungUmlegbar=$nurVersicherungserhoehungUmlegbar ? 1 : 0;
		return true;
	}
	
	/**
	 * Gibt Nur Versicherungserhöhung umlegbar zurück
	 * @return bool
	 */
	public function GetNurVersicherungserhoehungUmlegbar()
	{
		return $this->nurVersicherungserhoehungUmlegbar==1 ? true : false;
	}		
		
	/**
	 * Setzt die Beschreibung Nur Versicherungserhöhung umlegbar
	 * @param string $nurVersicherungserhoehungUmlegbarBeschreibung
	 * @return bool
	 */
	public function SetNurVersicherungserhoehungUmlegbarBeschreibung($nurVersicherungserhoehungUmlegbarBeschreibung)
	{
		$this->nurVersicherungserhoehungUmlegbarBeschreibung=$nurVersicherungserhoehungUmlegbarBeschreibung;
		return true;
	}
	
	/**
	 * Gibt die Beschreibung Nur Versicherungserhöhung umlegbar zurück
	 * @return string
	 */
	public function GetNurVersicherungserhoehungUmlegbarBeschreibung()
	{
		return $this->nurVersicherungserhoehungUmlegbarBeschreibung;
	}
		
	/**
	 * Setzt sonstige Besonderheiten
	 * @param bool $sonstigeBesonderheiten
	 * @return bool
	 */
	public function SetSonstigeBesonderheiten($sonstigeBesonderheiten)
	{
		if( !is_bool($sonstigeBesonderheiten) )return false;
		$this->sonstigeBesonderheiten=$sonstigeBesonderheiten ? 1 : 0;
		return true;
	}
	
	/**
	 * Gibt sonstige Besonderheiten zurück
	 * @return bool
	 */
	public function GetSonstigeBesonderheiten()
	{
		return $this->sonstigeBesonderheiten==1 ? true : false;
	}		
		
	/**
	 * Setzt die Beschreibung sonstige Besonderheiten
	 * @param string $sonstigeBesonderheitenBeschreibung
	 * @return bool
	 */
	public function SetSonstigeBesonderheitenBeschreibung($sonstigeBesonderheitenBeschreibung)
	{
		$this->sonstigeBesonderheitenBeschreibung=$sonstigeBesonderheitenBeschreibung;
		return true;
	}
	
	/**
	 * Gibt die Beschreibung sonstige Besonderheiten zurück
	 * @return string
	 */
	public function GetSonstigeBesonderheitenBeschreibung()
	{
		return $this->sonstigeBesonderheitenBeschreibung;
	}
	
	/**
	 * Setzt Werbegemeinschaft
	 * @param bool $werbegemeinschaft
	 * @return bool
	 */
	public function SetWerbegemeinschaft($werbegemeinschaft)
	{
		if( !is_bool($werbegemeinschaft) )return false;
		$this->werbegemeinschaft=$werbegemeinschaft ? 1 : 0;
		return true;
	}
	
	/**
	 * Gibt Werbegemeinschaft zurück
	 * @return bool
	 */
	public function GetWerbegemeinschaft()
	{
		return $this->werbegemeinschaft==1 ? true : false;
	}		
		
	/**
	 * Setzt die Beschreibung Werbegemeinschaft
	 * @param string $werbegemeinschaftBeschreibung
	 * @return bool
	 */
	public function SetWerbegemeinschaftBeschreibung($werbegemeinschaftBeschreibung)
	{
		$this->werbegemeinschaftBeschreibung=$werbegemeinschaftBeschreibung;
		return true;
	}
	
	/**
	 * Gibt die Beschreibung Werbegemeinschaft zurück
	 * @return string
	 */
	public function GetWerbegemeinschaftBeschreibung()
	{
		return $this->werbegemeinschaftBeschreibung;
	}
	
	/**
	 * Setzt das Flag, welches signalisiert, ob der Vertrag erfasst wurde oder nicht
	 * @param bool $vertragErfasst Vertrag erfasst = true ; Vertrag nicht erfasst = false
	 * @return bool
	 */
	public function SetVertragErfasst($vertragErfasst)
	{
		$this->vertragErfasst=$vertragErfasst;
		return true;
	}
	
	/**
	 * Setzt zurück, ob der Vertrag erfasst wurde oder nicht
	 * @return bool Vertrag erfasst = true / Vertrag nicht erfasst = false
	 */
	public function GetVertragErfasst()
	{
		return $this->vertragErfasst;
	}
	
	/**
	 * Return if the attribute can be changed in consideration of ProcessStatusGroups
	 * @param DBManager $db
	 * @return boolean
	 */
	public function IsAttributeAllowedToChange(DBManager $db)
	{
		// check if the contract is in more then one ProcessStatusGroup
		$numEffectedGroups = WorkflowManager::GetProcessStatusGroupCountOfContract($db, $this);
		// if contracts is in more then one group -> changes are not allowed
		if ($numEffectedGroups>1) return false;
		if ($numEffectedGroups==1)
		{
			// get all other contracts of the group this contract is in
			$others = WorkflowManager::GetContractsInSameProcessStatusGroup($db, $this);
			if (count($others)>0)
			{
				for ($a=0; $a<count($others); $a++)
				{
					// if one of the other contracts is in more then one group -> changes are not allowed
					if (WorkflowManager::GetProcessStatusGroupCountOfContract($db, $others[$a])>1) return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * Setzt die Währung im ISO-4217-Code
	 * @param DBManager $db
	 * @param string $iso4217
	 * @return bool
	 */
	public function SetCurrency(DBManager $db, $iso4217)
	{
		if ($iso4217=="" || strlen(trim($iso4217))!=3) return false;
		if (!$this->IsAttributeAllowedToChange($db)) return false;
		$this->dirtFlag |= self::DIRTFLAG_CURRENCY;
		$this->currency = strtoupper(trim($iso4217));
		return true;
	}
	
	/**
	 * Gibt die Währung im ISO-4217-Code zurück
	 * @return string
	 */
	public function GetCurrency()
	{
		return $this->currency;
	}
	
	/**
	 * Setzt den Mietvertragsbeginn
	 * @param int $mvBeginn
	 * @return bool
	 */
	public function SetMvBeginn($mvBeginn)
	{
		if (!is_int($mvBeginn)) return false;
		$this->mvBeginn = $mvBeginn;
		return true;
	}
	
	/**
	 * Gibt den Mietvertragsbeginn zurück
	 * @return int
	 */
	public function GetMvBeginn()
	{
		return $this->mvBeginn;
	}
	
	/**
	 * Setzt das erstmals mögliches Vertragsende
	 * @param int $mvEndeErstmalsMoeglich
	 * @return bool
	 */
	public function SetMvEndeErstmalsMoeglich($mvEndeErstmalsMoeglich)
	{
		if( !is_int($mvEndeErstmalsMoeglich) )return false;
		$this->mvEndeErstmalsMoeglich = $mvEndeErstmalsMoeglich;
		return true;
	}
	
	/**
	 * Gibt das erstmals mögliches Vertragsende zurück
	 * @return int
	 */
	public function GetMvEndeErstmalsMoeglich()
	{
		return $this->mvEndeErstmalsMoeglich;
	}
	
	/**
	 * Setzt das aktuelle Mietvertragsende
	 * @param int $mvEnde
	 * @return bool
	 */
	public function SetMvEnde($mvEnde)
	{
		if( !is_int($mvEnde) )return false;
		$this->mvEnde = $mvEnde;
		return true;
	}
	
	/**
	 * Gibt das aktuelle Mietvertragsende zurück
	 * @return int
	 */
	public function GetMvEnde()
	{
		return $this->mvEnde;
	}
	
	/**
	 * Setzt das aktuelle Mietvertragsende
	 * @param int $nurAuszuegeVorhanden
	 * @return bool
	 */
	public function SetNurAuszuegeVorhanden($nurAuszuegeVorhanden)
	{
		if (!is_int($nurAuszuegeVorhanden)) return false;
		$this->nurAuszuegeVorhanden = $nurAuszuegeVorhanden;
		return true;
	}
	
	/**
	 * Gibt das aktuelle Mietvertragsende zurück
	 * @return int
	 */
	public function GetNurAuszuegeVorhanden()
	{
		return $this->nurAuszuegeVorhanden;
	}
	
	/**
	 * Setzt "Mietvertragsdokumente vollstaendig"
	 * @param int $mietvertragsdokumenteVollstaendig
	 * @return bool
	 */
	public function SetMietvertragsdokumenteVollstaendig($mietvertragsdokumenteVollstaendig)
	{
		if (!is_int($mietvertragsdokumenteVollstaendig)) return false;
		$this->mietvertragsdokumenteVollstaendig = $mietvertragsdokumenteVollstaendig;
		return true;
	}
	
	/**
	 * Gibt "Mietvertragsdokumente vollstaendig" zurück
	 * @return int
	 */
	public function GetMietvertragsdokumenteVollstaendig()
	{
		return $this->mietvertragsdokumenteVollstaendig;
	}
	
	/**
	 * Setzt den Eigentümer
	 * @param AddressBase $eigentuemer
	 * @return bool
	 */
	public function SetEigentuemer(AddressBase $eigentuemer=null)
	{
		if ($eigentuemer==null)
		{
			$this->eigentuemer = null; 
			return true;
		}
		if ($eigentuemer->GetPKey()==-1) return false;
		$this->eigentuemer = $eigentuemer;
		return true;
	}
	
	/**
	 * Gibt den Eigentümer zurück
	 * @return AddressBase
	 */
	public function GetEigentuemer()
	{
		return $this->eigentuemer;
	}
		
	/**
	 * Setzt den Verwalter
	 * @param AddressBase $verwalter
	 * @return bool
	 */
	public function SetVerwalter(AddressBase $verwalter=null)
	{
		if( $verwalter==null)
		{
			$this->verwalter = null; 
			return true;
		}
		if ($verwalter->GetPKey()==-1) return false;
		$this->verwalter = $verwalter;
		return true;
	}
	
	/**
	 * Gibt den Verwalter zurück
	 * @return AddressBase
	 */
	public function GetVerwalter()
	{
		return $this->verwalter;
	}
	
	/**
	 * Gibt den zugehörigen Customer-Shop zurück
	 * @return CShop
	 */
	public function GetShop()
	{
		return $this->cShop;
	}
	
	/**
	 * Setzt den zugehörige Customer-Shop
	 * @param CShop $cShop
	 * @return bool
	 */
	public function SetShop(DBManager $db, CShop $cShop)
	{
		// if the contract is used in a ProcessStatusGroup the company couldn't be changed
		if (WorkflowManager::GetProcessStatusGroupCountOfContract($db, $this)>0)
		{
			if ($this->cShop==null || $cShop==null) return false;
			$location1 = $this->cShop->GetLocation();
			$location2 = $cShop->GetLocation();
			if ($location1==null || $location2==null) return false;
			$company1 = $location1->GetCompany();
			$company2 = $location2->GetCompany();
			if ($company1==null || $company2==null || $company1->GetPKey()!=$company2->GetPKey()) return false;
		}
		
		if ($cShop->GetPKey()==-1) return false;
		$this->cShop = $cShop;
		return true;
	}
	/**
	 * Gibt die Anzahl der zu dieser Contract hinterlegten Dateien zurück
	 * @param DBManager $db
	 * @param int $fileSemantic
	 * @return int
	 */
	public function GetFileCount(DBManager $db, $fileSemantic)
	{
		return $this->fileRelationManager->GetFileCount($db, $fileSemantic);
	}

	/**
	 * Gibt alle zu dieser Contract hinterlegten Dateien zurück
	 * @param DBManager $db
	 * @param int $fileSemantic
	 * @return File[]
	 */
	public function GetFiles(DBManager $db, $fileSemantic)
	{
		return $this->fileRelationManager->GetFiles($db, $fileSemantic);
	}
		
	/**
	 * Fügt dieser Contract die übergebene Datei hinzu
	 * @param DBManager $db
	 * @param File $file
	 * @return bool
	 */
	public function AddFile(DBManager $db, File $file)
	{
		$this->SetVertragErfasst(false);
		$this->Store($db);
		return $this->fileRelationManager->AddFile($db, $file);
	}
	
	/**
	 * Entfernt die übergebene Datei von dieser Contract. Falls die Datei 
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
	 * Entfernt alle Datei von dieser Contract. Falls die Dateien 
	 * sonst nirgends verwendet werden, werden diese gleich gelöscht.
	 * @param DBManager $db
	 * @return bool
	 */
	public function RemoveAllFiles(DBManager $db)
	{
		return $this->fileRelationManager->RemoveAllFiles($db);
	}
	
	/**
	 * Gibt die Anzahl der Abrechnungsjahre zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetAbrechnungsJahreCount(DBManager $db)
	{
		if ($this->pkey==-1) return 0;
		$data = $db->SelectAssoc("SELECT count(pkey) as count FROM ".AbrechnungsJahr::TABLE_NAME." WHERE contract=".$this->pkey);
		return (int)$data[0]["count"];
	}
	
	/**
	 * Gibt alle Abrechnungsjahre zurück
	 * @param DBManager $db
	 * @return AbrechnungsJahr[]
	 */
	public function GetAbrechnungsJahre(DBManager $db)
	{
		if ($this->pkey==-1) return Array();
		$data = $db->SelectAssoc("SELECT * FROM ".AbrechnungsJahr::TABLE_NAME." WHERE contract=".$this->pkey." ORDER BY jahr");
		$objects = Array();
		for($a=0; $a<count($data); $a++)
		{
			$object = new AbrechnungsJahr($db);
			if ($object->LoadFromArray($data[$a], $db)===true) $objects[] = $object;
		}
		return $objects;
	}
	
	/**
	 * Ordnet dem Abrechnungsjahr diesen Contract zu
	 * @param DBManager $db
	 * @param AbrechnungsJahr $abrechnungsJahr
	 * @return bool
	 */
	public function AddAbrechnungsJahr(DBManager $db, AbrechnungsJahr $abrechnungsJahr)
	{
		if ($this->pkey==-1) return false;
		// Dem Abrechnungsjahr diesen Contract zuweisen...
		return $abrechnungsJahr->SetContract($this);
	}

	/**
	 * Prüft, ob der übergebene Benutzer berechtigt ist, dieses Objekt zu verarbeiten
	 * @param User	$user
	 * @param DBManager $db
	 * @return bool
	 */
	public function HasUserAccess(User $user, DBManager $db)
	{
		$shop = $this->GetShop();
		return ($shop==null ? false : $shop->HasUserAccess($user, $db));
	}
	
	/**
	 * Prüft ob für den übergebenen Zeitraum eine Kollision mit einem anderen Vertrag (desselben Ladens) besteht
	 * @param DBManager $db
	 * @param int $dateFrom
	 * @param int $dateUntil
	 * @param Contract $collsionContract
	 * @return bool 
	 */
	public function CheckLifeOfLeaseCollison(DBManager $db, $dateFrom, $dateUntil, &$collsionContract)
	{
		if (!is_int($dateFrom) || !is_int($dateUntil) || $dateFrom>$dateUntil) return true;
		$shop = $this->GetShop();
		if ($shop==null) return true;
		// check all contracts of the same shop
		$contracts = $shop->GetContracts($db);
		foreach ($contracts as $contract)
		{
			//echo date("d.m.Y", $contract->GetMvBeginn())." - ".date("d.m.Y", $contract->GetMvEnde())."...";
			// skip this contract
			if ($contract->GetPKey()==$this->GetPKey()) continue;
			// skip contracts without life of lease
			if ($contract->GetMvBeginn()==0 || $contract->GetMvEnde()==0) continue;
			// check the passed period against life of lease of contract
			$collsionContract = $contract;
			if ($dateFrom>=$contract->GetMvBeginn() && $dateFrom<=$contract->GetMvEnde()) return true;
			if ($dateUntil>=$contract->GetMvBeginn() && $dateUntil<=$contract->GetMvEnde()) return true;
			if ($dateFrom<=$contract->GetMvBeginn() && $dateUntil>=$contract->GetMvEnde()) return true;
			//echo "OK<br />";
		}
		// no collision
		$collsionContract = null;
		return false;
	}
	
	/**
	 * Return a string with the life of lease of this contract
	 * @return string 
	 */
	public function GetLifeOfLeaseString()
	{
		if ($this->GetMvBeginn()==0 || $this->GetMvEnde()==0) return "";
		return date("d.m.Y", $this->GetMvBeginn())." - ".date("d.m.Y", $this->GetMvEnde());
	}

	/**
	 * Return a human readable name for the requested attribute
	 * @param ExtendedLanguageManager $languageManager
	 * @param string $attributeName
	 * @return string
	 */
	static public function GetAttributeName(ExtendedLanguageManager $languageManager, $attributeName)
	{
		switch($attributeName)
		{
			case 'ID':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'CONTRACT_ID');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}

	/**
	 * Gibt das Stammdatenblatt zurück
	 * @return File
	 */
	public function getStammdatenblatt()
	{
		return $this->stammdatenblatt;
	}

	/**
	 * Setzt das Stammdatenblatt
	 * @param File $stammdatenblatt
	 */
	public function setStammdatenblatt($stammdatenblatt)
	{
		$this->stammdatenblatt = $stammdatenblatt;
	}



	
}
?>