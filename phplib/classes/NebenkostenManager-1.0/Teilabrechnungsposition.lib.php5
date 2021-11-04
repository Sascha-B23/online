<?php
/**
 * Diese Klasse repräsentiert eine Teilabrechnungsposition
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class Teilabrechnungsposition extends DBEntry 
{

	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "teilabrechnungspositionen";
	
	/**
	 * Prefix string for ID
	 */
	const ID_PREFIX = 'TAP';
	
	/**
	 * Bezeichnung Teilflaeche
	 * @var string
	 */
	protected $bezeichnungTeilflaeche="";
		
	/**
	 * Bezeichnung Kostenart
	 * @var string
	 */
	protected $bezeichnungKostenart="";
	
	/**
	 * ID Kostenart FMS
	 * @var int
	 */
	protected $kostenartRSPkey=-1;
	
	/**
	 * Kostenart FMS
	 * @var RSKostenart
	 */
	protected $kostenartRS = null;

    /**
     * @var bool
     */
    protected $pauschale = false;

	/**
	 * Gesamteinheiten
	 * @var float
	 */
	protected $gesamteinheiten=0.0;
		
	/**
	 * Gesamteinheiten Einheit
	 * @var int
	 */
	protected $gesamteinheitenEinheit=NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE;
		
	/**
	 * Einheit Kunde
	 * @var float
	 */
	protected $einheitKunde=0.0;
		
	/**
	 * Einheit Kunde Einheit
	 * @var int
	 */
	protected $einheitKundeEinheit=NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE;
		
	/**
	 * Gesamtbetrag
	 * @var float
	 */
	protected $gesamtbetrag=0.0;
		
	/**
	 * Betrag Kunde
	 * @var float
	 */
	protected $betragKunde=0.0;

	/**
	 * Umlagefaehig
	 * @var int
	 */
	protected $umlagefaehig=0;
		
	/**
	 * ID zugehöriger Teilabrechnung
	 * @var int
	 */
	protected $teilabrechnungPkey=-1;
	
	/**
	 * Zugehöriger Teilabrechnung
	 * @var Teilabrechnung
	 */
	protected $teilabrechnung = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function Teilabrechnungsposition(DBManager $db, $doNotGenerateEntryId = false)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("bezeichnungTeilflaeche", "bezeichnungKostenart", "kostenartRS", "pauschale", "gesamteinheiten", "gesamteinheitenEinheit", "einheitKunde", "einheitKundeEinheit", "gesamtbetrag", "betragKunde", "umlagefaehig", "teilabrechnung");
		$dbConfig->rowParam = Array("TEXT", "TEXT", "BIGINT", "INT", "DECIMAL(20,2)", "INT", "DECIMAL(20,2)", "INT", "DECIMAL(20,2)", "DECIMAL(20,2)", "DECIMAL(20,2)", "INT", "BIGINT");
		$dbConfig->rowIndex = Array("teilabrechnung");
		$this->doNotGenerateEntryId = $doNotGenerateEntryId;
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1	Zugehöriger Teilabrechnung nicht gesetzt
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if( $this->teilabrechnungPkey==-1 )return -1;
		//if( $this->teilabrechnung==null || $this->teilabrechnung->GetPKey()==-1  )return -1;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "bezeichnungTeilflaeche";
		$rowData[]= $this->bezeichnungTeilflaeche;
		$rowName[]= "bezeichnungKostenart";
		$rowData[]= $this->bezeichnungKostenart;
		$rowName[]= "kostenartRS";
		$rowData[]= $this->kostenartRSPkey;
        $rowName[]= "pauschale";
        $rowData[]= ($this->pauschale ? 1 : 0);
		//$rowData[]= $this->kostenartRS==null ? -1 : $this->kostenartRS->GetPKey();
		$rowName[]= "gesamteinheiten";
		$rowData[]= $this->gesamteinheiten;
		$rowName[]= "gesamteinheitenEinheit";
		$rowData[]= $this->gesamteinheitenEinheit;
		$rowName[]= "einheitKunde";
		$rowData[]= $this->einheitKunde;
		$rowName[]= "einheitKundeEinheit";
		$rowData[]= $this->einheitKundeEinheit;
		$rowName[]= "gesamtbetrag";
		$rowData[]= $this->gesamtbetrag;
		$rowName[]= "betragKunde";
		$rowData[]= $this->betragKunde;
		$rowName[]= "umlagefaehig";
		$rowData[]= $this->umlagefaehig;
		$rowName[]= "teilabrechnung";
		$rowData[]= $this->teilabrechnungPkey;
		//$rowData[]= $this->teilabrechnung==null ? -1 : $this->teilabrechnung->GetPKey();
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
		$this->bezeichnungTeilflaeche = $data['bezeichnungTeilflaeche'];
		$this->bezeichnungKostenart = $data['bezeichnungKostenart'];
		$this->kostenartRSPkey = (int)$data['kostenartRS'];
		$this->kostenartRS = null;
/*		if( $data['kostenartRS']!=-1 ){
			$this->kostenartRS = new RSKostenart($db);
			if( !$this->kostenartRS->Load($data['kostenartRS'], $db) )$this->kostenartRS=null;
		}else{
			$this->kostenartRS=null;
		}		*/
        $this->pauschale = ($data['pauschale']==0 ? false : true);
		$this->gesamteinheiten = $data['gesamteinheiten'];
		$this->gesamteinheitenEinheit = $data['gesamteinheitenEinheit'];
		$this->einheitKunde = $data['einheitKunde'];
		$this->einheitKundeEinheit = $data['einheitKundeEinheit'];
		$this->gesamtbetrag = $data['gesamtbetrag'];
		$this->betragKunde = $data['betragKunde'];
		$this->umlagefaehig = $data['umlagefaehig'];
		$this->teilabrechnungPkey = (int)$data['teilabrechnung'];
		$this->teilabrechnung = null;
		/*if( $data['teilabrechnung']!=-1 ){
			$this->teilabrechnung = new Teilabrechnung($db);
			if( !$this->teilabrechnung->Load($data['teilabrechnung'], $db) )$this->teilabrechnung=null;
		}else{
			$this->teilabrechnung=null;
		}*/
		return true;
	}
	
	/**
	 * Setzt die Bezeichnung der Teilfläch
	 * @param string $bezeichnungTeilflaeche
	 * @return bool
	 */
	public function SetBezeichnungTeilflaeche($bezeichnungTeilflaeche)
	{
		$this->bezeichnungTeilflaeche=$bezeichnungTeilflaeche;
		return true;
	}
	
	/**
	 * Gibt die Bezeichnung der Teilfläch zurück
	 * @return string
	 */
	public function GetBezeichnungTeilflaech()
	{
		return $this->bezeichnungTeilflaeche;
	}
	
	/**
	 * Setzt die Bezeichnung der Kostenart
	 * @param string $bezeichnungKostenart
	 * @return bool
	 */
	public function SetBezeichnungKostenart($bezeichnungKostenart)
	{
		$this->bezeichnungKostenart=$bezeichnungKostenart;
		return true;
	}
	
	/**
	 * Gibt die Bezeichnung der Kostenart zurück
	 * @return string
	 */
	public function GetBezeichnungKostenart()
	{
		return $this->bezeichnungKostenart;
	}
	
	/**
	 * Setzt die zugehörige FMS-Kostenart
	 * @param RSKostenart $kostenartRS
	 * @return bool
	 */
	public function SetKostenartRS(RSKostenart $kostenartRS=null)
	{
		if ($kostenartRS==null)
		{
			$this->kostenartRS=null; 
			$this->kostenartRSPkey=-1; 
			return true;
		}
		if ($kostenartRS->GetPKey()==-1) return false;
		$this->kostenartRS = $kostenartRS;
		$this->kostenartRSPkey = $kostenartRS->GetPKey();
		return true;
	}
	
	/**
	 * Gibt den Pkey der zugehörige FMS-Kostenart zurück
	 * @return int
	 */
	public function GetKostenartRSPKey()
	{
		return $this->kostenartRSPkey;
	}
	
	/**
	 * Gibt die zugehörige FMS-Kostenart zurück
	 * @param DBManager $db
	 * @return RSKostenart
	 */
	public function GetKostenartRS(DBManager $db)
	{
		if ($this->kostenartRS!=null) return $this->kostenartRS;
		if ($this->kostenartRSPkey==-1) return null;
		// Wenn ein Pkey vorhanden ist, Objekt jetzt nachladen und zurückgeben
		//echo "RSKostenart wird nachgeladen...<br/>";
		$this->kostenartRS = RSKostenartManager::GetKostenartByPkey($db, $this->kostenartRSPkey);
		return $this->kostenartRS;
	}

    /**
     * @param $pauschale
     * @return bool
     */
    public function SetPauschale($pauschale)
    {
        if (!is_bool($pauschale)) return false;
        $this->pauschale = $pauschale;
        return true;
    }

    /**
     * @return bool
     */
    public function IsPauschale()
    {
        return $this->pauschale;
    }

	/**
	 * Setzt die Gesamteinheiten
	 * @param float $gesamteinheiten
	 * @return bool
	 */
	public function SetGesamteinheiten($gesamteinheiten)
	{
		if( !is_numeric($gesamteinheiten) )return false;
		$this->gesamteinheiten=(float)$gesamteinheiten;
		return true;
	}
	
	/**
	 * Gibt die Gesamteinheiten zurück
	 * @return float
	 */
	public function GetGesamteinheiten()
	{
		return $this->gesamteinheiten;
	}
	
	/**
	 * Setzt die Einheit der Gesamteinheiten
	 * @param int $gesamteinheitenEinheit
	 * @return bool
	 */
	public function SetGesamteinheitenEinheit($gesamteinheitenEinheit)
	{
		if( !is_int($gesamteinheitenEinheit) )return false;
		$this->gesamteinheitenEinheit=$gesamteinheitenEinheit;
		return true;
	}
	
	/**
	 * Gibt die Einheit der Gesamteinheiten zurück
	 * @return int
	 */
	public function GetGesamteinheitenEinheit()
	{
		return $this->gesamteinheitenEinheit;
	}
	
	/**
	 * Setzt die Einheiten laut Kunde
	 * @param float $einheitKunde
	 * @return bool
	 */
	public function SetEinheitKunde($einheitKunde)
	{
		if (!is_numeric($einheitKunde)) return false;
		$this->einheitKunde = (float)$einheitKunde;
		return true;
	}
	
	/**
	 * Gibt die Einheit laut Kunde zurück
	 * @return float
	 */
	public function GetEinheitKunde()
	{
		return $this->einheitKunde;
	}
	
	/**
	 * Setzt die Einheit der Kundeneinheit
	 * @param int $einheitKundeEinheit
	 * @return bool
	 */
	public function SetEinheitKundeEinheit($einheitKundeEinheit)
	{
		if (!is_int($einheitKundeEinheit)) return false;
		$this->einheitKundeEinheit = $einheitKundeEinheit;
		return true;
	}
	
	/**
	 * Gibt die Einheit der Kundeneinheit zurück
	 * @return int
	 */
	public function GetEinheitKundeEinheit()
	{
		return $this->einheitKundeEinheit;
	}
		
	/**
	 * Setzt Gesamtbetrag
	 * @param float $gesamtbetrag
	 * @return bool
	 */
	public function SetGesamtbetrag($gesamtbetrag)
	{
		if( !is_numeric($gesamtbetrag) )return false;
		$this->gesamtbetrag=(float)$gesamtbetrag;
		return true;
	}
	
	/**
	 * Gibt Gesamtbetrag zurück
	 * @return float
	 */
	public function GetGesamtbetrag()
	{
		return $this->gesamtbetrag;
	}	
	
	/**
	 * Setzt Betrag Kunde
	 * @param float $betragKunde
	 * @return bool
	 */
	public function SetBetragKunde($betragKunde)
	{
		if( !is_numeric($betragKunde) )return false;
		$this->betragKunde=(float)$betragKunde;
		return true;
	}
	
	/**
	 * Gibt Betrag Kunde zurück
	 * @return float
	 */
	public function GetBetragKunde()
	{
		return $this->betragKunde;
	}	
	
	/**
	 * Gibt den rechnerischen Betrag des Kunden zurück
	 * @param float/bool	$gesamtBetrag		Gesamtbetrag oder false (wird nur verarbeitet wenn ein Float-Wert übergebene wird)
	 * @param float/bool	$gesamtEinheiten		Gesamteinheiten oder false (wird nur verarbeitet wenn ein Float-Wert übergebene wird)
	 * @param float/bool	$kundeEinheiten		Kundeneinheiten oder false (wird nur verarbeitet wenn ein Float-Wert übergebene wird)
	 * @param bool		$ignoreEinheitenTyp	true = Verschiedene Einheitentypen ignorieren
	 * @return float
	 */
	public function GetBetragKundeSoll(&$db, $gesamtBetrag=false, $gesamtEinheiten=false, $kundeEinheiten=false, $ignoreEinheitenTyp=false)
	{
		$gesamtBetrag = ( !is_float($gesamtBetrag) ? $this->GetGesamtbetrag() : $gesamtBetrag);
		$gesamtEinheiten = ( !is_float($gesamtEinheiten) ? $this->GetGesamteinheiten() : $gesamtEinheiten);
		$kundeEinheiten = ( !is_float($kundeEinheiten) ? $this->GetEinheitKunde() : $kundeEinheiten);
		if( !$ignoreEinheitenTyp && $this->GetEinheitKundeEinheit()!=$this->GetGesamteinheitenEinheit() )return 0.0;
		if( $gesamtEinheiten==0.0 )return 0.0;
		// Gewichtung berücksichtigen
		$weight=1.0;
		$taTemp=$this->GetTeilabrechnung($db);
		if( $taTemp!=null )$weight=$taTemp->GetWeight();
		return round(($gesamtBetrag*$kundeEinheiten/$gesamtEinheiten)*$weight, 2);
	}

	/**
	 * Setzt, ob Possition umlagefähig ist
	 * @param int		Umlagefähig (0=nich definiert 1=Ja 2=Nein)
	 * @return bool	
	 */
	public function SetUmlagefaehig($umlagefaehig)
	{
		if (!is_int($umlagefaehig) || $umlagefaehig<0 || $umlagefaehig>2) return false;
		$this->umlagefaehig = $umlagefaehig;
		return true;
	}
	
	/**
	 * Gibt zurück, ob Possition umlagefähig ist
	 * @return int Umlagefähig (0=nich definiert 1=Ja 2=Nein)
	 */
	public function GetUmlagefaehig()
	{
		return $this->umlagefaehig;
	}
	
	/**
	 * Gibt den PKey der zugehörige Teilabrechnung zurück
	 * @return int
	 */
	public function GetTeilabrechnungPKey()
	{
		return $this->teilabrechnungPkey;
	}	
	
	/**
	 * Gibt die zugehörige Teilabrechnung zurück
	 * @param DBManager $db
	 * @return Teilabrechnung
	 */
	public function GetTeilabrechnung(DBManager $db)
	{
		// Wenn das Objekt bereits geladen ist, dieses dirket zurückgeben
		if( $this->teilabrechnung!=null )return $this->teilabrechnung;
		// Wenn das Objekt nicht geladen ist und kein Pkey dafür hinterlegt ist null-Pointer zurückgeben
		if( $this->teilabrechnungPkey==-1 )return null;
		// Wenn ein Pkey vorhanden ist, Objekt jetzt nachladen und zurückgeben
		$this->teilabrechnung = RSKostenartManager::GetTeilabrechnungByPkey($db, $this->teilabrechnungPkey);
		return $this->teilabrechnung;
	}
	
	/**
	 * Setzt die zugehörige Teilabrechnung
	 * @param Teilabrechnung $teilabrechnung
	 * @return bool
	 */
	public function SetTeilabrechnung(Teilabrechnung $teilabrechnung)
	{
		if ($teilabrechnung->GetPKey()==-1) return false;
		$this->teilabrechnung = $teilabrechnung;
		$this->teilabrechnungPkey = $teilabrechnung->GetPKey();
		return true;
	}
	
	/**
	 * Legt eine Kopie dieser Position für die übergeben Teilabrechnung an. Dieses
	 * Objekt ist nach der Operation mit der übergebene Teilabrechnung verknüpft!
	 * @param Teilabrechnung $teilabrechnung
	 * @return bool
	 */
	public function Copy(Teilabrechnung $teilabrechnung)
	{
		if ($teilabrechnung->GetPKey()==-1) return false;
		$this->pkey = -1;
		return $this->SetTeilabrechnung($teilabrechnung);
	}
	
	/**
	 * Gibt zurück, ob der Datensatz komplett ist
	 * @param DBManager $db
	 * @return bool
	 */
	public function IsComplete(DBManager $db)
	{
		if (trim($this->GetBezeichnungTeilflaech())=="") return false;
		if (trim($this->GetBezeichnungKostenart())=="") return false;
		if ($this->GetKostenartRS($db)==null) return false;
        if (!$this->IsPauschale())
        {
            if ($this->GetGesamteinheiten() == 0.0) return false;
            if ($this->GetGesamteinheitenEinheit() == NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE) return false;
            if ($this->GetEinheitKunde() == 0.0) return false;
            if ($this->GetEinheitKundeEinheit() == NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE) return false;
            if ($this->GetGesamtbetrag()==0.0) return false;
        }
		if ($this->GetBetragKunde()==0.0) return false;
		if ($this->GetUmlagefaehig()==0) return false;
		return true;
	}
	
		
	/**
	 * Return the related contract
	 * @return Contract
	 */
	public function GetContract(DBManager $db)
	{
		$ta = $this->GetTeilabrechnung($db);
		if ($ta==null) return null;
		return $ta->GetContract();
	}
	
	/**
	 * Return the current currency
	 * @return string 
	 */
	public function GetCurrency(DBManager $db)
	{
		$contract = $this->GetContract($db);
		if ($contract==null) return "";
		return $contract->GetCurrency();
	}
	
}
?>