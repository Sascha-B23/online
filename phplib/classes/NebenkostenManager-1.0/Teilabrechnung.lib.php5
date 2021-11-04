<?php
/**
 * Diese Klasse repräsentiert eine Teilabrechnung
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class Teilabrechnung extends DBEntry implements DependencyFileDescription, AttributeNameMaper
{
	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "teilabrechnung";
	
	/**
	 * Prefix string for ID
	 */
	const ID_PREFIX = 'TA';
	
	/**
	 * File relation manager
	 * @var FileRelationManager 
	 */
	protected $fileRelationManager = null;
	
	/**
	 * Firmenname
	 * @var string
	 */
	protected $firmenName="";
	
	/**
	 * Vorauszahlung laut Abrechnung
	 * @var float
	 */
	protected $vorauszahlungLautKunde=0.0;
	
	/**
	 * Vorauszahlung laut Abrechnung
	 * @var float
	 */
	protected $vorauszahlungLautAbrechnung=0.0;
	
	/**
	 * Abrechnungsergebnis laut Abrechnung
	 * @var float
	 */
	protected $abrechnungsergebnisLautAbrechnung=0.0;
	
	/**
	 * Abschlagszahlung/Gutschrift
	 * @var float
	 */
	protected $abschlagszahlungGutschrift=0.0;
	
	/**
	 * Nachzahlung/Gutschrift
	 * @var float
	 */
	protected $nachzahlungGutschrift=0.0;
	
	/**
	 * Korrigiertes Abrechnungsergebnis
	 * @var float
	 */
	protected $korrigiertesAbrechnungsergebnis=0.0;
	
	/**
	 * Bezeichnung
	 * @var string
	 */
	protected $bezeichnung="";
	
	/**
	 * Datum
	 * @var timestamp
	 */
	protected $datum=0;
	
	/**
	 * Abrechnungszeitraum von
	 * @var timestamp
	 */
	protected $abrechnungszeitraumVon=0;
	
	/**
	 * Abrechnungszeitraum bis
	 * @var timestamp
	 */
	protected $abrechnungszeitraumBis=0;
	
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
	 * Anwalt
	 * @var AddressBase
	 */
	protected $anwalt = null;
	
	/**
	 * Schriftverkehr mit
	 * @var int
	 */
	protected $schriftverkehrMit=NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_KEINEM;
	
	/**
	 * Frist Belegeinsicht
	 * @var timestamp
	 */
	protected $fristBelegeinsicht=0;
	
	/**
	 * Frist Widerspruch
	 * @var timestamp
	 */
	protected $fristWiderspruch=0;
	
	/**
	 * Frist Zahlung
	 * @var timestamp
	 */
	protected $fristZahlung=0;
	
	/**
	 * Umlageflaeche in qm
	 * @var float
	 */
	protected $umlageflaeche_qm=0.0;
	
	/**
	 * Heizkosten nach HVO abgerechnet
	 * @var int
	 */
	protected $heizkostenNachHVOAbgerechnet=0;
	
	/**
	 * Versicherungserhoehung beachtet
	 * @var int
	 */
	protected $versicherungserhoehungBeachtet=0;
	
	/**
	 * Grundsteuererhoehung beachtet
	 * @var int
	 */
	protected $grundsteuererhoehungBeachtet=0;
	
	/**
	 * Erstabrechnung oder Abrechnung nach Umbau
	 * @var int
	 */
	protected $erstabrechnungOderAbrechnungNachUmbau=0;
	
	/**
	 * Gibt zurück, ob die Teilabrechnung bereits erfasst wurde
	 * @var int
	 */
	protected $erfasst=false;
	
	/**
	 * 
	 * @var bool 
	 */
	private $recursiveDelete = false; 
	
	/**
	 * Auftragsdatum Abrechnung
	 * @var int
	 */
	protected $auftragsdatumAbrechnung = 0;
	
	/**
	 * Flag that indicates if the difference of the settlement (Abrechnungsdifferenz) is hidden or not
	 * @var boolean
	 */
	protected $hideSettlementDifference = false;
	
	/**
	 * Zugehöriger Abrechnungs Jahr
	 * @var AbrechnungsJahr
	 */
	protected $abrechnungsJahr = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function Teilabrechnung(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("firmenName", "vorauszahlungLautKunde", "vorauszahlungLautAbrechnung", "abrechnungsergebnisLautAbrechnung", "abschlagszahlungGutschrift", "nachzahlungGutschrift", "korrigiertesAbrechnungsergebnis", "bezeichnung", "datum", "abrechnungszeitraumVon", "abrechnungszeitraumBis", "eigentuemer", "eigentuemerTyp", "verwalter", "verwalterTyp", "anwalt", "anwaltTyp", "schriftverkehrMit", "fristBelegeinsicht", "fristWiderspruch", "fristZahlung", "umlageflaeche_qm", "heizkostenNachHVOAbgerechnet", "versicherungserhoehungBeachtet", "grundsteuererhoehungBeachtet", "erstabrechnungOderAbrechnungNachUmbau", "erfasst", "auftragsdatumAbrechnung", "hideSettlementDifference", "abrechnungsJahr");
		$dbConfig->rowParam = Array("TEXT",		"DECIMAL(20,2)",			"DECIMAL(20,2)",				"DECIMAL(20,2)",					"DECIMAL(20,2)",				"DECIMAL(20,2)",		"DECIMAL(20,2)",				"LONGTEXT",		"BIGINT", "BIGINT",					"BIGINT",				"BIGINT",		"INT",			"BIGINT",		"INT",		 "BIGINT", "INT",		"INT",					"BIGINT",			"BIGINT",			"BIGINT",		"DECIMAL(20,2)",		"INT",							"INT",							"INT",							"INT",									"INT",			"BIGINT",			 "INT",						"BIGINT");
		$dbConfig->rowIndex = Array("eigentuemer", "verwalter", "anwalt", "abrechnungsJahr", "auftragsdatumAbrechnung");
		parent::__construct($db, $dbConfig);
		
		$this->fileRelationManager = new FileRelationManager($db, $this);
	}
	
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @return bool
	 */
	public function IsDeletable(&$db)
	{
		if( $this->GetTeilabrechnungspositionCount($db)>0 )return false;
		if( $this->GetPKey()!=-1 && !$this->recursiveDelete ){
			// only check this on none recursive delete (see fn DeleteRecursive)...
			$data=$db->SelectAssoc("SELECT pkey FROM ".ProcessStatus::TABLE_NAME." WHERE currentTeilabrechnung=".(int)$this->GetPKey());
			if( count($data)>0 )return false;
		}
		return parent::IsDeletable($db);
	}
		
	/**
	 * Löscht das Objekt aus der DB
	 * @param DBManager $db
	 * @return bool
	 */
	public function DeleteMe(&$db)
	{
		if( !$this->RemoveAllFiles($db) )return false;
		$abrechnungsJahrTemp=$this->GetAbrechnungsJahr();
		if( parent::DeleteMe($db) ){
			if( $abrechnungsJahrTemp!=null )$abrechnungsJahrTemp->DeleteMe($db);
			return true;
		}
		return false;
	}
	
	/**
	 * Delete this and all contained objects
	 * @param DBManager $db
	 * @param User $user 
	 */
	public function DeleteRecursive(DBManager $db, User $user)
	{
		if ($user->GetGroupBasetype($db) < UM_GROUP_BASETYPE_ADMINISTRATOR) return false;
		// delete all tabs
		$tabs = $this->GetTeilabrechnungspositionen($db);
		for ($a=0; $a<count($tabs);$a++)
		{
			$tabs[$a]->DeleteMe($db);
		}
		// remove all files...
		$this->RemoveAllFiles($db);
		// remove this TA
		// this flag needs to be set - otherwise fn DeleteMe will fail
		$this->recursiveDelete = true;
		$this->DeleteMe($db);
		return true;
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1	Zugehöriges Abrechnungsjahr nicht gesetzt
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if( $this->abrechnungsJahr==null || $this->abrechnungsJahr->GetPKey()==-1 )return -1;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "firmenName";
		$rowData[]= $this->firmenName;
		$rowName[]= "vorauszahlungLautKunde";
		$rowData[]= $this->vorauszahlungLautKunde;
		$rowName[]= "vorauszahlungLautAbrechnung";
		$rowData[]= $this->vorauszahlungLautAbrechnung;		
		$rowName[]= "abrechnungsergebnisLautAbrechnung";
		$rowData[]= $this->abrechnungsergebnisLautAbrechnung;
		$rowName[]= "abschlagszahlungGutschrift";
		$rowData[]= $this->abschlagszahlungGutschrift;
		$rowName[]= "nachzahlungGutschrift";
		$rowData[]= $this->nachzahlungGutschrift;
		$rowName[]= "korrigiertesAbrechnungsergebnis";
		$rowData[]= $this->korrigiertesAbrechnungsergebnis;
		$rowName[]= "bezeichnung";
		$rowData[]= $this->bezeichnung;
		$rowName[]= "datum";
		$rowData[]= $this->datum;
		$rowName[]= "abrechnungszeitraumVon";
		$rowData[]= $this->abrechnungszeitraumVon;
		$rowName[]= "abrechnungszeitraumBis";
		$rowData[]= $this->abrechnungszeitraumBis;
		$rowName[]= "eigentuemer";
		$rowData[]= $this->eigentuemer==null ? -1 : $this->eigentuemer->GetPKey();
		$rowName[]= "eigentuemerTyp";
		$rowData[]= $this->eigentuemer==null ? AddressBase::AM_CLASS_UNKNOWN : $this->eigentuemer->GetClassType();
		$rowName[]= "verwalter";
		$rowData[]= $this->verwalter==null ? -1 : $this->verwalter->GetPKey();
		$rowName[]= "verwalterTyp";
		$rowData[]= $this->verwalter==null ? AddressBase::AM_CLASS_UNKNOWN : $this->verwalter->GetClassType();
		$rowName[]= "anwalt";
		$rowData[]= $this->anwalt==null ? -1 : $this->anwalt->GetPKey();
		$rowName[]= "anwaltTyp";
		$rowData[]= $this->anwalt==null ? AddressBase::AM_CLASS_UNKNOWN : $this->anwalt->GetClassType();
		$rowName[]= "schriftverkehrMit";
		$rowData[]= $this->schriftverkehrMit;
		$rowName[]= "fristBelegeinsicht";
		$rowData[]= $this->fristBelegeinsicht;
		$rowName[]= "fristWiderspruch";
		$rowData[]= $this->fristWiderspruch;
		$rowName[]= "fristZahlung";
		$rowData[]= $this->fristZahlung;
		$rowName[]= "umlageflaeche_qm";
		$rowData[]= $this->umlageflaeche_qm;
		$rowName[]= "heizkostenNachHVOAbgerechnet";
		$rowData[]= $this->heizkostenNachHVOAbgerechnet;
		$rowName[]= "versicherungserhoehungBeachtet";
		$rowData[]= $this->versicherungserhoehungBeachtet;
		$rowName[]= "grundsteuererhoehungBeachtet";
		$rowData[]= $this->grundsteuererhoehungBeachtet;
		$rowName[]= "erstabrechnungOderAbrechnungNachUmbau";
		$rowData[]= $this->erstabrechnungOderAbrechnungNachUmbau;
		$rowName[]= "erfasst";
		$rowData[]= $this->erfasst ? 1 : 0;
		$rowName[]= "abrechnungsJahr";
		$rowData[]= $this->abrechnungsJahr==null ? -1 : $this->abrechnungsJahr->GetPKey();
		$rowName[]= "auftragsdatumAbrechnung";
		$rowData[]= $this->auftragsdatumAbrechnung;
		$rowName[]= "hideSettlementDifference";
		$rowData[]= ($this->hideSettlementDifference ? 1 : 0);
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array $data Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->firmenName = $data['firmenName'];
		$this->vorauszahlungLautKunde = $data['vorauszahlungLautKunde'];
		$this->vorauszahlungLautAbrechnung = $data['vorauszahlungLautAbrechnung'];
		$this->abrechnungsergebnisLautAbrechnung = $data['abrechnungsergebnisLautAbrechnung'];
		$this->abschlagszahlungGutschrift = $data['abschlagszahlungGutschrift'];
		$this->nachzahlungGutschrift = $data['nachzahlungGutschrift'];
		$this->korrigiertesAbrechnungsergebnis = $data['korrigiertesAbrechnungsergebnis'];
		$this->bezeichnung = $data['bezeichnung'];
		$this->datum = $data['datum'];
		$this->abrechnungszeitraumVon = $data['abrechnungszeitraumVon'];
		$this->abrechnungszeitraumBis = $data['abrechnungszeitraumBis'];
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
			$this->verwalter = AddressManager::GetAddressElementByPkeyAndType($db, (int)$data['verwalterTyp'], $data['verwalter']);
		}
		else
		{
			$this->verwalter=null;
		}
		if ($data['anwalt']!=-1)
		{
			$this->anwalt = AddressManager::GetAddressElementByPkeyAndType($db, (int)$data['anwaltTyp'], $data['anwalt']);
		}
		else
		{
			$this->anwalt=null;
		}
		$this->schriftverkehrMit = $data['schriftverkehrMit'];
		$this->fristBelegeinsicht = $data['fristBelegeinsicht'];
		$this->fristWiderspruch = $data['fristWiderspruch'];
		$this->fristZahlung = $data['fristZahlung'];
		$this->umlageflaeche_qm = $data['umlageflaeche_qm'];
		$this->heizkostenNachHVOAbgerechnet = $data['heizkostenNachHVOAbgerechnet'];
		$this->versicherungserhoehungBeachtet = $data['versicherungserhoehungBeachtet'];
		$this->grundsteuererhoehungBeachtet = $data['grundsteuererhoehungBeachtet'];
		$this->erstabrechnungOderAbrechnungNachUmbau = $data['erstabrechnungOderAbrechnungNachUmbau'];
		$this->erfasst = ($data['erfasst']==1 ? true : false);
		if (is_object($data['abrechnungsJahr']) && is_a($data['abrechnungsJahr'], 'AbrechnungsJahr'))
		{
			// optimized loading...
			$this->abrechnungsJahr = $data['abrechnungsJahr'];
		}
		elseif ($data['abrechnungsJahr']!=-1)
		{
			// standard loading...
			$this->abrechnungsJahr = RSKostenartManager::GetAbrechnungsJahrByPkey($db, $data['abrechnungsJahr']);
		}
		else
		{
			$this->abrechnungsJahr=null;
		}
		$this->auftragsdatumAbrechnung = (int)$data['auftragsdatumAbrechnung'];
		$this->hideSettlementDifference = ((int)$data['hideSettlementDifference'] == 1 ? true : false);
		return true;
	}
	
	/**
	 * Return Description
	 */
	public function GetDependencyFileDescription()
	{
		$abrechnungsJahr = $this->GetAbrechnungsJahr();
		return ($abrechnungsJahr==null ? "?" : $abrechnungsJahr->GetDependencyFileDescription()).DependencyFileDescription::SEPERATOR."TA ".$this->GetBezeichnung();
	}
	
	/**
	 * Setzt den Firmenname
	 * @param string $firmenName
	 * @return bool
	 */
	public function SetFirmenname($firmenName)
	{
		$this->firmenName=$firmenName;
		return true;
	}
	
	/**
	 * Gibt den Firmenname zurück
	 * @return string
	 */
	public function GetFirmenname()
	{
		return $this->firmenName;
	}
	
	/**
	 * Setzt die Vorauszahlung laut Buchhaltung
	 * @param float $vorauszahlungLautKunde
	 * @return bool
	 */
	public function SetVorauszahlungLautKunde($vorauszahlungLautKunde)
	{
		if (!is_numeric($vorauszahlungLautKunde)) return false;
		$this->vorauszahlungLautKunde = (float)$vorauszahlungLautKunde;
		return true;
	}
	
	/**
	 * Gibt die Vorauszahlung laut Buchhaltung zurück
	 * @return float
	 */
	public function GetVorauszahlungLautKunde()
	{
		return $this->vorauszahlungLautKunde;
	}

	/**
	 * Setzt die Vorauszahlung laut Abrechnung
	 * @param float $vorauszahlungLautAbrechnung
	 * @return bool
	 */
	public function SetVorauszahlungLautAbrechnung($vorauszahlungLautAbrechnung)
	{
		if (!is_numeric($vorauszahlungLautAbrechnung)) return false;
		$this->vorauszahlungLautAbrechnung = (float)$vorauszahlungLautAbrechnung;
		return true;
	}
	
	/**
	 * Gibt die Vorauszahlung laut Abrechnung zurück
	 * @return float		Vorauszahlung laut Abrechnung
	 */
	public function GetVorauszahlungLautAbrechnung()
	{
		return $this->vorauszahlungLautAbrechnung;
	}
		
	/**
	 * Setzt das Abrechnungsergebnis laut Abrechnung
	 * @param float $abrechnungsergebnisLautAbrechnung
	 * @return bool
	 */
	public function SetAbrechnungsergebnisLautAbrechnung($abrechnungsergebnisLautAbrechnung)
	{
		if( !is_numeric($abrechnungsergebnisLautAbrechnung) )return false;
		$this->abrechnungsergebnisLautAbrechnung=(float)$abrechnungsergebnisLautAbrechnung;
		return true;
	}
	
	/**
	 * Gibt das Abrechnungsergebnis laut Abrechnung zurück
	 * @return float
	 */
	public function GetAbrechnungsergebnisLautAbrechnung()
	{
		return $this->abrechnungsergebnisLautAbrechnung;
	}
		
	/**
	 * Setzt die Abschlagszahlung/Gutschrift
	 * @param float $abschlagszahlungGutschrift
	 * @return bool
	 */
	public function SetAbschlagszahlungGutschrift($abschlagszahlungGutschrift)
	{
		if( !is_numeric($abschlagszahlungGutschrift) )return false;
		$this->abschlagszahlungGutschrift=(float)$abschlagszahlungGutschrift;
		return true;
	}
	
	/**
	 * Gibt die Abschlagszahlung/Gutschrift zurück
	 * @return float
	 */
	public function GetAbschlagszahlungGutschrift()
	{
		return $this->abschlagszahlungGutschrift;
	}
			
	/**
	 * Setzt die Nachzahlung/Gutschrift
	 * @param float $nachzahlungGutschrift
	 * @return bool
	 */
	public function SetNachzahlungGutschrift($nachzahlungGutschrift)
	{
		if( !is_numeric($nachzahlungGutschrift) )return false;
		$this->nachzahlungGutschrift=(float)$nachzahlungGutschrift;
		return true;
	}
	
	/**
	 * Gibt die Nachzahlung/Gutschrift zurück
	 * @return float
	 */
	public function GetNachzahlungGutschrift()
	{
		return $this->nachzahlungGutschrift;
	}
	
	/**
	 * Setzt das korrigiertes Abrechnungsergebnis
	 * @param float $korrigiertesAbrechnungsergebnis
	 * @return bool
	 */
	public function SetKorrigiertesAbrechnungsergebnis($korrigiertesAbrechnungsergebnis)
	{
		if (!is_numeric($korrigiertesAbrechnungsergebnis)) return false;
		$this->korrigiertesAbrechnungsergebnis = (float)$korrigiertesAbrechnungsergebnis;
		return true;
	}
	
	/**
	 * Gibt das korrigiertes Abrechnungsergebnis zurück
	 * @return float
	 */
	public function GetKorrigiertesAbrechnungsergebnis()
	{
		return $this->korrigiertesAbrechnungsergebnis;
	}
	
	/**
	 * Setzt die Bezeichnung
	 * @param string $bezeichnung
	 * @return bool
	 */
	public function SetBezeichnung($bezeichnung)
	{
		$this->bezeichnung=$bezeichnung;
		return true;
	}
	
	/**
	 * Gibt die Bezeichnung zurück
	 * @return string
	 */
	public function GetBezeichnung()
	{
		return $this->bezeichnung;
	}
	
	/**
	 * Setzt Datum
	 * @param int $datum
	 * @return bool
	 */
	public function SetDatum($datum)
	{
		if( !is_int($datum) )return false;
		$this->datum=$datum;
		return true;
	}
	
	/**
	 * Gibt Datum zurück
	 * @return int
	 */
	public function GetDatum()
	{
		return $this->datum;
	}
	
	/**
	 * Setzt Abrechnungszeitraum von
	 * @param int $abrechnungszeitraumVon
	 * @return bool
	 */
	public function SetAbrechnungszeitraumVon($abrechnungszeitraumVon)
	{
		if( !is_int($abrechnungszeitraumVon) )return false;
		$this->abrechnungszeitraumVon=$abrechnungszeitraumVon;
		return true;
	}
	
	/**
	 * Gibt Abrechnungszeitraum von zurück
	 * @return int
	 */
	public function GetAbrechnungszeitraumVon()
	{
		return $this->abrechnungszeitraumVon;
	}
	
	/**
	 * Setzt Abrechnungszeitraum bis
	 * @param int $abrechnungszeitraumBis
	 * @return bool
	 */
	public function SetAbrechnungszeitraumBis($abrechnungszeitraumBis)
	{
		if( !is_int($abrechnungszeitraumBis) )return false;
		$this->abrechnungszeitraumBis=$abrechnungszeitraumBis;
		return true;
	}
	
	/**
	 * Gibt Abrechnungszeitraum bis zurück
	 * @return int
	 */
	public function GetAbrechnungszeitraumBis()
	{
		return $this->abrechnungszeitraumBis;
	}
	
	/**
	 * Setzt den Eigentümer
	 * @param AddressBase $eigentuemer
	 * @return bool
	 */
	public function SetEigentuemer(AddressBase $eigentuemer=null)
	{
		if( $eigentuemer==null)
		{
			$this->eigentuemer=null; 
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
			$this->verwalter=null; 
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
	 * Setzt den Anwalt
	 * @param AddressBase $anwalt
	 * @return bool
	 */
	public function SetAnwalt(AddressBase $anwalt=null)
	{
		if ($anwalt==null)
		{
			$this->anwalt=null; 
			return true;
		}
		if ($anwalt->GetPKey()==-1) return false;
		$this->anwalt = $anwalt;
		return true;
	}
	
	/**
	 * Gibt den Anwalt zurück
	 * @return AddressBase
	 */
	public function GetAnwalt()
	{
		return $this->anwalt;
	}
	
	/**
	 * Setzt Schriftverkehr mit
	 * @param int $schriftverkehrMit
	 * @return bool
	 */
	public function SetSchriftverkehrMit($schriftverkehrMit)
	{
		if( !is_int($schriftverkehrMit) )return false;
		$this->schriftverkehrMit=$schriftverkehrMit;
		return true;
	}
	
	/**
	 * Gibt Schriftverkehr mit zurück
	 * @return int
	 */
	public function GetSchriftverkehrMit()
	{
		return $this->schriftverkehrMit;
	}
	
	/**
	 * Gibt den Ansprechpartner zurück
	 * @return AddressBase
	 */
	public function GetAnsprechpartner()
	{
		if ($this->GetSchriftverkehrMit()==NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_EIGENTUEMER) return $this->GetEigentuemer();
		if ($this->GetSchriftverkehrMit()==NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_VERWALTER) return $this->GetVerwalter();
		if ($this->GetSchriftverkehrMit()==NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_ANWALT) return $this->GetAnwalt();
		return null;
	}
		
	/**
	 * Setzt Frist Belegeinsicht
	 * @param int $fristBelegeinsicht
	 * @return bool
	 */
	public function SetFristBelegeinsicht($fristBelegeinsicht)
	{
		if( !is_int($fristBelegeinsicht) )return false;
		$this->fristBelegeinsicht=$fristBelegeinsicht;
		return true;
	}
	
	/**
	 * Gibt Frist Belegeinsicht zurück
	 * @return int
	 */
	public function GetFristBelegeinsicht()
	{
		return $this->fristBelegeinsicht;
	}
		
	/**
	 * Setzt Frist Widerspruch
	 * @param int $fristWiderspruch
	 * @return bool
	 */
	public function SetFristWiderspruch($fristWiderspruch)
	{
		if( !is_int($fristWiderspruch) )return false;
		$this->fristWiderspruch=$fristWiderspruch;
		return true;
	}
	
	/**
	 * Gibt Frist Widerspruch zurück
	 * @return int
	 */
	public function GetFristWiderspruch()
	{
		return $this->fristWiderspruch;
	}
		
	/**
	 * Setzt Frist Zahlung
	 * @param int $fristZahlung
	 * @return bool
	 */
	public function SetFristZahlung($fristZahlung)
	{
		if( !is_int($fristZahlung) )return false;
		$this->fristZahlung=$fristZahlung;
		return true;
	}
	
	/**
	 * Gibt Frist Zahlung zurück
	 * @return int
	 */
	public function GetFristZahlung()
	{
		return $this->fristZahlung;
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
	 * @return float
	 */
	public function GetUmlageflaecheQM()
	{
		return $this->umlageflaeche_qm;
	}
	
	/**
	 * Setzt, ob Heizkosten nach HVO abgerechnet wurden
	 * @param int $heizkostenNachHVOAbgerechnet Heizkosten nach HVO abgerechnet (0=nicht gesetzt, 1=Ja, 2=Nein)
	 * @return bool
	 */
	public function SetHeizkostenNachHVOAbgerechnet($heizkostenNachHVOAbgerechnet)
	{
		if( !is_int($heizkostenNachHVOAbgerechnet) || $heizkostenNachHVOAbgerechnet<0 || $heizkostenNachHVOAbgerechnet>2 )return false;
		$this->heizkostenNachHVOAbgerechnet=$heizkostenNachHVOAbgerechnet;
		return true;
	}
	
	/**
	 * Gibt zurück, ob Heizkosten nach HVO abgerechnet wurden
	 * @return int Heizkosten nach HVO abgerechnet (0=nicht gesetzt, 1=Ja, 2=Nein)
	 */
	public function GetHeizkostenNachHVOAbgerechnet()
	{
		return $this->heizkostenNachHVOAbgerechnet;
	}
	
	/**
	 * Setzt, ob Versicherungserhoehung beachtet wurden
	 * @param int $versicherungserhoehungBeachtet Versicherungserhoehung beachtet (0=nicht gesetzt, 1=Ja, 2=Nein)
	 * @return bool
	 */
	public function SetVersicherungserhoehungBeachtet($versicherungserhoehungBeachtet)
	{
		if( !is_int($versicherungserhoehungBeachtet) || $versicherungserhoehungBeachtet<0 || $versicherungserhoehungBeachtet>2 )return false;
		$this->versicherungserhoehungBeachtet=$versicherungserhoehungBeachtet;
		return true;
	}
	
	/**
	 * Gibt zurück, ob Versicherungserhoehung beachtet wurden
	 * @return int Versicherungserhoehung beachtet (0=nicht gesetzt, 1=Ja, 2=Nein)
	 */
	public function GetVersicherungserhoehungBeachtet()
	{
		return $this->versicherungserhoehungBeachtet;
	}
	
	/**
	 * Setzt, ob Grundsteuererhöhung beachtet wurden
	 * @param int $grundsteuererhoehungBeachtet Grundsteuererhöhung beachtet (0=nicht gesetzt, 1=Ja, 2=Nein)
	 * @return bool
	 */
	public function SetGrundsteuererhoehungBeachtet($grundsteuererhoehungBeachtet)
	{
		if( !is_int($grundsteuererhoehungBeachtet) || $grundsteuererhoehungBeachtet<0 || $grundsteuererhoehungBeachtet>2 )return false;
		$this->grundsteuererhoehungBeachtet=$grundsteuererhoehungBeachtet;
		return true;
	}
	
	/**
	 * Gibt zurück, ob Grundsteuererhöhung beachtet wurden
	 * @return int Grundsteuererhöhung beachtet (0=nicht gesetzt, 1=Ja, 2=Nein)
	 */
	public function GetGrundsteuererhoehungBeachtet()
	{
		return $this->grundsteuererhoehungBeachtet;
	}
	
	/**
	 * Setzt, ob es Erstabrechnung oder Abrechnung nach Umbau ist
	 * @param int $erstabrechnungOderAbrechnungNachUmbau Erstabrechnung oder Abrechnung nach Umbau (0=nicht gesetzt, 1=Ja, 2=Nein)
	 * @return bool
	 */
	public function SetErstabrechnungOderAbrechnungNachUmbau($erstabrechnungOderAbrechnungNachUmbau)
	{
		if( !is_int($erstabrechnungOderAbrechnungNachUmbau) || $erstabrechnungOderAbrechnungNachUmbau<0 || $erstabrechnungOderAbrechnungNachUmbau>2 )return false;
		$this->erstabrechnungOderAbrechnungNachUmbau=$erstabrechnungOderAbrechnungNachUmbau;
		return true;
	}
	
	/**
	 * Gibt zurück, ob es Erstabrechnung oder Abrechnung nach Umbau ist
	 * @return int Erstabrechnung oder Abrechnung nach Umbau (0=nicht gesetzt, 1=Ja, 2=Nein)
	 */
	public function GetErstabrechnungOderAbrechnungNachUmbau()
	{
		return $this->erstabrechnungOderAbrechnungNachUmbau;
	}
	
	/**
	 * Setzt, ob die Teilabrechnung erfasst wurde
	 * @param bool $erfasst
	 * @return bool
	 */
	public function SetErfasst($erfasst)
	{
		if( !is_bool($erfasst) )return false;
		$this->erfasst=$erfasst;
		return true;
	}
	
	/**
	 * Gibt zurück, ob die Teilabrechnung erfasst wurde
	 * @return bool
	 */
	public function GetErfasst()
	{
		return $this->erfasst;
	}
	
	/**
	 * Gibt die Anzahl der zu dieser Teilabrechnung hinterlegten Dateien zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetFileCount(DBManager $db)
	{
		return $this->fileRelationManager->GetFileCount($db);
	}

	/**
	 * Gibt alle zu dieser Teilabrechnung hinterlegten Dateien zurück
	 * @param DBManager $db
	 * @return File[]
	 */
	public function GetFiles(DBManager $db)
	{
		return $this->fileRelationManager->GetFiles($db);
	}
		
	/**
	 * Fügt dieser Teilabrechnung die übergebene Datei hinzu
	 * @param DBManager $db
	 * @param File $file
	 * @return bool
	 */
	public function AddFile(DBManager $db, File $file)
	{
		return $this->fileRelationManager->AddFile($db, $file);
	}
	
	/**
	 * Entfernt die übergebene Datei von dieser Teilabrechnung. Falls die Datei 
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
	 * Gibt das zugehörige AbrechnungsJahr zurück
	 * @return AbrechnungsJahr
	 */
	public function GetAbrechnungsJahr()
	{
		return $this->abrechnungsJahr;
	}
	
	/**
	 * Setzt das zugehörige AbrechnungsJahr
	 * @param AbrechnungsJahr $abrechnungsJahr
	 * @return bool
	 */
	public function SetAbrechnungsJahr(AbrechnungsJahr $abrechnungsJahr)
	{
		if ($abrechnungsJahr->GetPKey()==-1) return false;
		$this->abrechnungsJahr = $abrechnungsJahr;
		return true;
	}
	
	/**
	 * Return the Teilabrechnung from the previous year if available
	 * @param DBManager $db
	 * @return Teilabrechnung
	 */
	public function GetTeilabrechnungPreviousYear(DBManager $db)
	{
		if ($this->pkey==-1) return null;
		// Shop holen
		$abrechnungsJahr=$this->GetAbrechnungsJahr();
		if ($abrechnungsJahr==null) return null;
		$contract=$abrechnungsJahr->GetContract();
		if ($contract==null) return null;
		$shop=$contract->GetShop();
		if ($shop==null) return null;
		// Abrechnungsjahre des Vorjahres vom Shop holen
		$vorjahre=$shop->GetAbrechnungsjahr($db, ((int)$abrechnungsJahr->GetJahr())-1 );
		if (count($vorjahre)==0) return null;
		// Optimale Teilabrechung des Vorjahrs suchen
		$returnValue = null;
		for ($a=0; $a<count($vorjahre); $a++)
		{
			//echo "-".$a."/".count($vorjahre)."<br />";
			if ($vorjahre[$a]==null || $vorjahre[$a]->GetContract()==null || $vorjahre[$a]->GetTeilabrechnungCount($db)==0) continue;
			$vorJahrTeilabrechnungen=$vorjahre[$a]->GetTeilabrechnungen($db);
			for ($b=0; $b<count($vorJahrTeilabrechnungen); $b++)
			{
				//echo " ".$b."/".count($vorJahrTeilabrechnungen)."<br />";
				if ($vorJahrTeilabrechnungen[$b]==null) continue;
				$returnValue = $vorJahrTeilabrechnungen[$b];
				if ($vorjahre[$a]->GetContract()->GetPKey()==$contract->GetPKey())
				{
					return $returnValue;
				}
			}
		}
		return $returnValue;
	}
	
	/**
	 * Erzeugt die Teilabrechnungspositionen anhand von Vorjahres-Teilabrechnungen
	 * @param DBManager $db
	 * @return bool
	 */
	public function AutoCreateTeilabrechnungspositionen(DBManager $db)
	{
		if ($this->pkey==-1) return false;
		// Shop holen
		$abrechnungsJahr=$this->GetAbrechnungsJahr();
		if ($abrechnungsJahr==null) return false;
		$contract=$abrechnungsJahr->GetContract();
		if ($contract==null) return false;
		$shop=$contract->GetShop();
		if ($shop==null) return false;
		// Abrechnungsjahre des Vorjahres vom Shop holen
		$vorjahre=$shop->GetAbrechnungsjahr($db, ((int)$abrechnungsJahr->GetJahr())-1 );
		if (count($vorjahre)==0) return false;
		$vorjahrAbrechnungsPositionen=Array();
		$vorjahrSameContract=false;
		// Optimale Teilabrechung des Vorjahrs suchen
		for ($a=0; $a<count($vorjahre); $a++)
		{
			if ($vorjahre[$a]==null || $vorjahre[$a]->GetContract()==null || $vorjahre[$a]->GetTeilabrechnungCount($db)==0) continue;
			// Alle Teilabrechnungspositionen über alle Teilabrechnungen des Vertrags hinweg sammeln
			$abrechnungsPositionenTemp=Array();
			$vorJahrTeilabrechnungen=$vorjahre[$a]->GetTeilabrechnungen($db);
			for ($b=0; $b<count($vorJahrTeilabrechnungen); $b++)
			{
				if ($vorJahrTeilabrechnungen[$b]==null) continue;
				if ($vorJahrTeilabrechnungen[$b]->GetTeilabrechnungspositionCount($db)==0) continue;
				$positionenTemp=$vorJahrTeilabrechnungen[$b]->GetTeilabrechnungspositionen($db);
				for ($c=0; $c<count($positionenTemp); $c++)
				{
					if ($positionenTemp[$c]==null) continue;
					$abrechnungsPositionenTemp[] = $positionenTemp[$c];
				}
			}
			// Ermittelte Positionen übernehmen
			$vorjahrAbrechnungsPositionen=$abrechnungsPositionenTemp;
			// Wenn der Vertrag der selbe ist wie für diese Teilabrechnung, dann können wir an dieser Stelle abbrechen
			if ($vorjahre[$a]->GetContract()->GetPKey()==$contract->GetPKey())
			{
				$vorjahrSameContract=true;
				break;
			}
		}
		// Wenn es keine Teilabrechnugspositionen zum übernehmen gibt -> Abbrechen
		if (count($vorjahrAbrechnungsPositionen)==0) return false;
		// Teilabrechnungspositionen in diese Teilabrechnung kopieren...
		for ($a=0; $a<count($vorjahrAbrechnungsPositionen); $a++)
		{
			if ($vorjahrAbrechnungsPositionen[$a]->Copy($this))
			{
				// Daten zurücksetzen
				$vorjahrAbrechnungsPositionen[$a]->SetGesamtbetrag(0.0);
				$vorjahrAbrechnungsPositionen[$a]->SetBetragKunde(0.0);
				//if( !$vorjahrSameContract )
				{
					// Wenn nicht gleicher Vertrag, dann auch Umlagefähigkeit zurücksetzen
					$vorjahrAbrechnungsPositionen[$a]->SetUmlagefaehig(2);
				}
				// Speichern
				$vorjahrAbrechnungsPositionen[$a]->Store($db);
			}
		}
		return true;
	}
		
	/**
	 * Gibt die Anzahl der Teilabrechnungspositionen zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetTeilabrechnungspositionCount(DBManager $db)
	{
		if( $this->pkey==-1 )return 0;
		$data=$db->SelectAssoc("SELECT count(pkey) as count FROM ".Teilabrechnungsposition::TABLE_NAME." WHERE teilabrechnung=".$this->pkey );
		return (int)$data[0]["count"];
	}
	
	/**
	 * Gibt alle Teilabrechnungspositionen zurück
	 * @param DBManager	$db		Datenbank-Objekt
	 * @return Teilabrechnungsposition[]
	 */
	public function GetTeilabrechnungspositionen(DBManager $db)
	{
		if( $this->pkey==-1 )return Array();
		$data=$db->SelectAssoc("SELECT * FROM ".Teilabrechnungsposition::TABLE_NAME." WHERE teilabrechnung=".$this->pkey." ORDER BY pkey ASC");
		$objects=Array();
		for($a=0; $a<count($data); $a++)
		{
			$object=new Teilabrechnungsposition($db);
			if ($object->LoadFromArray($data[$a], $db)===true)
			{
				// Optimierung: Teilabrechnung jetzt setzen, um zu verhindern dass Teilabrechnungsobjekt bei Zugriff aus DB für jede TAP nachgeladen wird
				$object->SetTeilabrechnung($this);
				$objects[]=$object;
			}
		}
		return $objects;
	}
	
	/**
	 * Ordnet der Teilabrechnungsposition diese Teilabrechnung zu
	 * @param DBManager $db
	 * @param Teilabrechnungsposition $teilabrechnungsposition
	 * @return bool
	 */
	public function AddTeilabrechnungsposition(DBManager $db, Teilabrechnungsposition $teilabrechnungsposition)
	{
		if ($this->pkey==-1) return false;
		// Der Teilabrechnungsposition diese Teilabrechnung zuweisen...
		return $teilabrechnungsposition->SetTeilabrechnung($this);
	}
	
	/**
	 * Gibt die Summe des Betrags Kunde über alle TAPs hinweg zurück
	 * @param DBManager $db
	 * @return float
	 */
	public function GetSummeBetragKunde(DBManager $db)
	{
		$data=$db->SelectAssoc("SELECT SUM(betragKunde) AS summe FROM ".Teilabrechnungsposition::TABLE_NAME." WHERE teilabrechnung=".$this->pkey);
		if( isset($data[0]["summe"]) && is_numeric($data[0]["summe"]) )return ((float)$data[0]["summe"]);
		return 0.0;
	}
	
	/**
	 * Gibt die Summe des Betrags Kunde über alle TAPs hinweg zurück
	 * @param DBManager $db
	 * @return float
	 */
	public function GetSummeGesamtbetrag(DBManager $db)
	{
		$data=$db->SelectAssoc("SELECT SUM(gesamtbetrag) AS summe FROM ".Teilabrechnungsposition::TABLE_NAME." WHERE teilabrechnung=".$this->pkey." AND pauschale=0");
		if( isset($data[0]["summe"]) && is_numeric($data[0]["summe"]) )return ((float)$data[0]["summe"]);
		return 0.0;
	}
	
	/**
	 * Clont die übergebene Teilabrechnungsposition
	 * @param DBManager	$db
	 * @param int $teilabrechnungspositionID
	 * @return Teilabrechnungsposition|bool
	 */
	public function CloneTeilabrechnungsposition(DBManager $db, $teilabrechnungspositionID)
	{
		if( $teilabrechnungspositionID=="" || ((int)$teilabrechnungspositionID)!=$teilabrechnungspositionID || $this->pkey==-1 )return false;
		// Teilabrechnungsposition mit der übergebenene ID suchen
		$data=$db->SelectAssoc("SELECT pkey FROM ".Teilabrechnungsposition::TABLE_NAME." WHERE teilabrechnung=".$this->pkey." AND pkey=".(int)$teilabrechnungspositionID );
		if( count($data)!=1 )return false;
		$object=new Teilabrechnungsposition($db);
		if( $object->Load($data[0]["pkey"], $db)!==true )return false;
		if( $object->Copy($this)!==true )return false;
		// Daten zurücksetzen
		$object->SetGesamtbetrag(0.0);
		$object->SetBetragKunde(0.0);
		$object->SetUmlagefaehig(0);
		$object->SetBezeichnungKostenart("");
		$object->SetKostenartRS(null);
		$object->SetUmlagefaehig(2);
		// Speichern
		if( $object->Store($db)===true )return $object;
		return false;
	}
	
	/**
	 * Gibt die Anzahl der Tage zurück, welche die Teilabrechnung abdeckt
	 * @return int
	 */
	public function GetNumDays()
	{
		$anzahlTageTA=(($this->GetAbrechnungszeitraumBis() - $this->GetAbrechnungszeitraumVon())/60/60/24)+1;
		return ((int)round($anzahlTageTA));
	}
	
	/**
	 * Gibt die Gewichtung der Teilabrechnung auf das Jahr gesehen in Prozent zurück
	 * @return float
	 */
	public function GetWeight()
	{
		$year=$this->GetAbrechnungsJahr();
		return (float)(((float)$this->GetNumDays())/( $year==null ? 365.0 : (float)$year->GetDaysOfYear() ));
	}
	
	/**
	 * Gibt das Auftragsdatum zurück
	 * @return int 
	 */
	public function GetAuftragsdatumAbrechnung()
	{
		return $this->auftragsdatumAbrechnung;
	}

	/**
	 * Setzt das Auftragsdatum
	 * @param int $auftragsdatumAbrechnung
	 * @return boolean Success
	 */
	public function SetAuftragsdatumAbrechnung($auftragsdatumAbrechnung)
	{
		if (!is_int($auftragsdatumAbrechnung)) return false;
		$this->auftragsdatumAbrechnung = $auftragsdatumAbrechnung;
		return true;
	}
	
	/**
	 * Returns if the difference of the settlement (Abrechnungsdifferenz) is hidden or not
	 * @return boolean 
	 */
	public function IsSettlementDifferenceHidden()
	{
		return $this->hideSettlementDifference;
	}
	
	/**
	 * Set if the difference of the settlement (Abrechnungsdifferenz) is hidden or not
	 * @param boolean $hidden
	 * @return boolean 
	 */
	public function SetSettlementDifferenceHidden($hidden)
	{
		if (!is_bool($hidden)) return false;
		$this->hideSettlementDifference = $hidden;
		return true;
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
	 * @return string 
	 */
	public function GetCurrency()
	{
		$contract = $this->GetContract();
		if ($contract==null) return "";
		return $contract->GetCurrency();
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
			case 'bezeichnung':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_BEZEICHNUNG');
			case 'auftragsdatumAbrechnung':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_AUFTRAGSDATUM');
			case 'abrechnungszeitraumVon':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_ABRECHNUNGSZEITRAUMVON');
			case 'abrechnungszeitraumBis':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_ABRECHNUNGSZEITRAUMBIS');
			case 'fristBelegeinsicht':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_FRISTBELEGEINSICHT');
			case 'fristWiderspruch':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_FRISTWIDERSPRUCH');
			case 'fristZahlung':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_FRISTZAHLUNG');
			case 'datum':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_DATUM');
			case 'vorauszahlungLautKunde':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_VORAUSZAHLUNGLAUTKUNDE');
			case 'vorauszahlungLautAbrechnung':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_VORAUSZAHLUNGLAUTABRECHNUNG');
			case 'abrechnungsergebnisLautAbrechnung':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_ABRECHNUNGSERGEBNISLAUTABRECHNUNG');
			case 'abschlagszahlungGutschrift':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_ABSCHLAGSZAHLUNGGUTSCHRIFT');
			case 'nachzahlungGutschrift':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_NACHZAHLUNGGUTSCHRIFT');
			case 'korrigiertesAbrechnungsergebnis':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_KORRIGIERTESABRECHNUNGSERGEBNIS');
			case 'summeBetragKundeTAPs':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_SUMMEBETRAGKUNDETAP');
			case 'umlageflaeche_qm':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_UMLAGEFLAECHE_QM');
			case 'hideSettlementDifference':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'TA_HIDESETTLEMENTDIFFERENCE');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}
	
}
?>