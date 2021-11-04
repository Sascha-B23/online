<?php
/**
 * Diese Klasse repräsentiert einen Kürzungsbetrag
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class Kuerzungsbetrag extends DBEntry 
{
	/**
	 * Datenbankname und Spalten
	 * @var string
	 */
	const TABLE_NAME="kuerzungsbetrag";
	
	/**
	 * Einstufung des Kürzungsbetrags
	 * @var enum
	 */
	const KUERZUNGSBETRAG_EINSTUFUNG_UNKNOWN = 0;
	const KUERZUNGSBETRAG_EINSTUFUNG_GRUEN = 1;
	const KUERZUNGSBETRAG_EINSTUFUNG_GELB = 2;
	const KUERZUNGSBETRAG_EINSTUFUNG_ROT = 3;
	const KUERZUNGSBETRAG_EINSTUFUNG_GRAU = 4;
	
	/**
	 * Typ des Kürzungsbetrags
	 * @var enum
	 */
	const KUERZUNGSBETRAG_TYPE_UNKNOWN = 0;
	const KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG = 1;
	const KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG = 2;
	
	/**
	 * Realisiert
	 * @var enum
	 */
	const KUERZUNGSBETRAG_REALISIERT_NO = 0;
	const KUERZUNGSBETRAG_REALISIERT_YES = 1;

	/**
	 * Typ des Kürzungsbetrags
	 * @var enum
	 */
	const KUERZUNGSBETRAG_CATEGORIZATION_UNKNOWN = 0;
	const KUERZUNGSBETRAG_CATEGORIZATION_CLEARONBOTHSIDES = 1;
	const KUERZUNGSBETRAG_CATEGORIZATION_POTENTIALLYCLEARONBOTHSIDES = 2;
	const KUERZUNGSBETRAG_CATEGORIZATION_ONESIDEDCLEAR = 3;
	const KUERZUNGSBETRAG_CATEGORIZATION_TOBECLARIFIED = 4;
	const KUERZUNGSBETRAG_CATEGORIZATION_DEPOWEREDPOINT = 5;

	/**
	 * Typ Einsparung
	 * @var enum
	 */
	const KUERZUNGSBETRAG_EINSPARUNGSTYP_UNKNOWN = 0;
	const KUERZUNGSBETRAG_EINSPARUNGSTYP_MAXIMALFORDERUNG = 1;
	const KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_STANDARD = 2;
	const KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_KUNDE = 3;
	const KUERZUNGSBETRAG_EINSPARUNGSTYP_VERMIETERANGEBOT = 4;
	
	/**
	 * Kürzungbetrag
	 * @var float
	 */
	protected $kuerzungsbetrag = 0.0;
	
	/**
	 * Typ
	 * @var int 
	 */
	protected $type = self::KUERZUNGSBETRAG_TYPE_UNKNOWN;
	
	/**
	 * Einstufung
	 * @var int 
	 */
	protected $rating = self::KUERZUNGSBETRAG_EINSTUFUNG_UNKNOWN;
	
	/**
	 * Realisiert
	 * @var int 
	 */
	protected $realisiert = self::KUERZUNGSBETRAG_REALISIERT_NO;

	/**
	 * Kategorisierung
	 * @var int
	 */
	protected $categorization = self::KUERZUNGSBETRAG_CATEGORIZATION_UNKNOWN;
	
	/**
	 * Zugehöriger Widerspruchspunkt
	 * @var Widerspruchspunkt
	 */
	protected $widerspruchspunkt = null;

	/**
	 * Gesetzter Stichtag Zielgeldeingang
	 * @var int
	 */
	protected $stichtagZielgeldeingang = 0;

	/**
	 * Typ Einsparung
	 * @var int
	 */
	protected $einsparungsTyp = self::KUERZUNGSBETRAG_EINSPARUNGSTYP_MAXIMALFORDERUNG;
	
	/**
	 * Constructor
	 * @param DBManager	$db
	 */
	public function Kuerzungsbetrag(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("kuerzungsbetrag", "type", "rating", "realisiert", "widerspruchspunkt", "categorization", "stichtagZielgeldeingang", "einsparungsTyp");
		$dbConfig->rowParam = Array("DECIMAL(20,2)", "INT", "INT", "INT", "BIGINT", "BIGINT", "BIGINT", "BIGINT");
		$dbConfig->rowIndex = Array("type", "rating", "realisiert", "widerspruchspunkt");
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1 Kein Widerspruchspunkt zugeordnet
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// Array mit zu speichernden Daten anlegen
		if ($this->widerspruchspunkt==null) return -1;
		$rowName[]= "kuerzungsbetrag";
		$rowData[]= $this->kuerzungsbetrag;
		$rowName[]= "type";
		$rowData[]= $this->type;
		$rowName[]= "rating";
		$rowData[]= $this->rating;
		$rowName[]= "realisiert";
		$rowData[]= $this->realisiert;
		$rowName[]= "categorization";
		$rowData[]= $this->categorization;
		$rowName[]= "widerspruchspunkt";
		$rowData[]= ($this->widerspruchspunkt==null ? -1 : $this->widerspruchspunkt->GetPKey());
		$rowName[]= "stichtagZielgeldeingang";
		$rowData[]= $this->stichtagZielgeldeingang;
		$rowName[]= "einsparungsTyp";
		$rowData[]= $this->einsparungsTyp;
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
		$this->kuerzungsbetrag = $data['kuerzungsbetrag'];
		$this->type = (int)$data['type'];
		$this->rating = (int)$data['rating'];
		$this->realisiert = (int)$data['realisiert'];
		$this->categorization = (int)$data['categorization'];
		$this->stichtagZielgeldeingang = (int)$data['stichtagZielgeldeingang'];
		$this->einsparungsTyp = (int)$data['einsparungsTyp'];
		if (is_a($data['widerspruchspunkt'], "Widerspruchspunkt"))
		{
			$this->widerspruchspunkt = $data['widerspruchspunkt'];
		}
		elseif ($data['widerspruchspunkt']!=-1)
		{
			$this->widerspruchspunkt = new Widerspruchspunkt($db);
			if (!$this->widerspruchspunkt->Load($data['widerspruchspunkt'], $db)) $this->widerspruchspunkt = null;
		}
		else
		{
			$this->widerspruchspunkt = null;
		}
		return true;
	}
		
	/**
	 * Setzt den Kürzungbetrag 
	 * @param float	$kuerzungbetrag 
	 * @return bool
	 */
	public function SetKuerzungsbetrag($kuerzungbetrag)
	{
		if (!is_numeric($kuerzungbetrag) )return false;
		$this->kuerzungsbetrag = (float)$kuerzungbetrag;
		return true;
	}
	
	/**
	 * Gibt den Kürzungbetrag zurück
	 * @return float
	 */
	public function GetKuerzungsbetrag()
	{
		return $this->kuerzungsbetrag;
	}

	/**
	 * Gibt alle Typen zurück
	 * @param bool $includeUnknown
	 * @return int[]
	 */
	public static function GetTypes($includeUnknown = false)
	{
		$returnValue = Array();
		if ($includeUnknown) $returnValue[] = self::KUERZUNGSBETRAG_TYPE_UNKNOWN;
		$returnValue[] = self::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG;
		$returnValue[] = self::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG;
		return $returnValue;
	}

	/**
	 * Setzt den Typ (Erst- oder Folgeeinsparung)
	 * @param int $typ
	 * @return bool
	 */
	public function SetType($type)
	{
		if (!is_int($type)) return false;
		$this->type = $type;
		return true;
	}
	
	/**
	 * Gibt den Typ zurück (Erst- oder Folgeeinsparung)
	 * @return int
	 */
	public function GetType()
	{
		return $this->type;
	}

	/**
	 * Setzt, ob der Kürzungsbetrag realisiert wurde oder nicht
	 * @param int $realisiert
	 * @return bool
	 */
	public function SetRealisiert($realisiert)
	{
		if (!is_int($realisiert)) return false;
		$this->realisiert = $realisiert;
		return true;
	}
	
	/**
	 * Gibt zurück, ob der Kürzungsbetrag realisiert wurde oder nicht
	 * @return int
	 */
	public function GetRealisiert()
	{
		return $this->realisiert;
	}

	/**
	 * Gibt alle Einstufungen zurück
	 * @param bool $includeUnknown
	 * @return int[]
	 */
	public static function GetRatings($includeUnknown = false)
	{
		$returnValue = Array();
		if ($includeUnknown) $returnValue[] = self::KUERZUNGSBETRAG_EINSTUFUNG_UNKNOWN;
		$returnValue[] = self::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN;
		$returnValue[] = self::KUERZUNGSBETRAG_EINSTUFUNG_GELB;
		$returnValue[] = self::KUERZUNGSBETRAG_EINSTUFUNG_ROT;
		return $returnValue;
	}

	/**
	 * Setzt die Einstufung (Grün, Gelb, Rot, Grau)
	 * @param int $typ
	 * @return bool
	 */
	public function SetRating($rating)
	{
		if (!is_int($rating)) return false;
		$this->rating = $rating;
		return true;
	}
	
	/**
	 * Gibt die Einstufung zurück (Grün, Gelb, Rot, Grau)
	 * @return int
	 */
	public function GetRating()
	{
		return $this->rating;
	}

	/**
	 * Setzt die Kategorisierung (beidseitig klar; potentiell beidseitig klar; einseitig klar; zu klären)
	 * @param int $categorization
	 * @return bool
	 */
	public function SetCategorization($categorization)
	{
		if (!is_int($categorization)) return false;
		$this->categorization = $categorization;
		return true;
	}

	/**
	 * Gibt die Kategorisierung zurück (beidseitig klar; potentiell beidseitig klar; einseitig klar; zu klären)
	 * @return int
	 */
	public function GetCategorization()
	{
		return $this->categorization;
	}

	/**
	 * Gibt den zugehörigen Widerspruchspunkt zurück
	 * @return Widerspruchspunkt
	 */
	public function GetWiderspruchspunkt()
	{
		return $this->widerspruchspunkt;
	}
	
	/**
	 * Setzt den zugehörigen Widerspruchspunkt
	 * @param Widerspruchspunkt	$widerspruchspunkt
	 * @return bool
	 */
	public function SetWiderspruchspunkt(Widerspruchspunkt $widerspruchspunkt)
	{
		if( $widerspruchspunkt->GetPKey()==-1 )return false;
		$this->widerspruchspunkt = $widerspruchspunkt;
		return true;
	}

	/**
	 * Gibt den zugehörigen Stichtag Zielgeldeingang zurück
	 * @return string|null
	 */
	public function GetStichtagZielgeldeingang()
	{
		return $this->stichtagZielgeldeingang;
	}

	/**
	 * Setzt den Stichtag Zielgeldeingang
	 * @param $stichtagZielgeldeingang
	 * @return bool
	 */
	public function SetStichtagZielgeldeingang($stichtagZielgeldeingang)
	{
		if (!is_int($stichtagZielgeldeingang)) return false;
		$this->stichtagZielgeldeingang = $stichtagZielgeldeingang;
		return true;
	}

	/**
	 * Gibt alle Typen Einsparung zurück
	 * @param bool $includeUnknown
	 * @return int[]
	 */
	public static function GetEinsparungsTypen($includeUnknown = false)
	{
		$returnValue = Array();
		if ($includeUnknown) $returnValue[] = self::KUERZUNGSBETRAG_EINSPARUNGSTYP_UNKNOWN;
		$returnValue[] = self::KUERZUNGSBETRAG_EINSPARUNGSTYP_MAXIMALFORDERUNG;
		$returnValue[] = self::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_STANDARD;
		$returnValue[] = self::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_KUNDE;
		$returnValue[] = self::KUERZUNGSBETRAG_EINSPARUNGSTYP_VERMIETERANGEBOT;
		return $returnValue;
	}

	/**
	 * Setzt den Typ der Einsparung
	 * @param int $einsparungsTyp
	 * @return bool
	 */
	public function SetEinsparungsTyp($einsparungsTyp)
	{
		if (!is_int($einsparungsTyp)) return false;
		$this->einsparungsTyp = $einsparungsTyp;
		return true;
	}

	/**
	 * Gibt den Typ der Einsparung zurück
	 * @return int
	 */
	public function GetEinsparungsTyp()
	{
		return $this->einsparungsTyp;
	}
		
	/**
	 * Legt eine Kopie an. Dieses Objekt ist nach der Operation mit dem übergebenen
	 * Widerspruch verknüpft!
	 * @param DBManager $db
	 * @param Widerspruchspunkt	$widerspruchspunkt
	 * @return bool
	 */
	public function Copy(DBManager $db, Widerspruchspunkt $widerspruchspunkt)
	{
		if ($widerspruchspunkt->GetPKey()==-1) return false;
		$this->pkey=-1;
		$this->SetWiderspruchspunkt($widerspruchspunkt);
		$this->Store($db);
		return true;
	}

	/**
	 * Gibt den Namen der Ampel zurück
	 * @param int $rating
	 * @return string 
	 */
	public static function GetRatingName($rating)
	{
		switch($rating)
		{
			case self::KUERZUNGSBETRAG_EINSTUFUNG_UNKNOWN:
				return '-';
			case self::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN:
				return 'Grün';
			case self::KUERZUNGSBETRAG_EINSTUFUNG_GELB:
				return 'Gelb';
			case self::KUERZUNGSBETRAG_EINSTUFUNG_ROT:
				return 'Rot';
			case self::KUERZUNGSBETRAG_EINSTUFUNG_GRAU:
				return 'Grau';
		}
		return "?";
	}
	
	/**
	 * Gibt den Namen des Types zurück
	 * @param int $type
	 * @return string 
	 */
	public static function GetTypeName($type)
	{
		switch($type)
		{
			case self::KUERZUNGSBETRAG_TYPE_UNKNOWN:
				return '-';
			case self::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG:
				return 'Ersteinsparung';
			case self::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG:
				return 'Folgeeinsparung';
		}
		return "?";
	}

	/**
	 * Gibt den Namen der Ampel zurück
	 * @param int $rating
	 * @return string
	 */
	public static function GetCategorizationName($rating)
	{
		switch($rating)
		{
			case self::KUERZUNGSBETRAG_CATEGORIZATION_UNKNOWN:
				return '-';
			case self::KUERZUNGSBETRAG_CATEGORIZATION_CLEARONBOTHSIDES:
				return 'Beidseitig klar';
			case self::KUERZUNGSBETRAG_CATEGORIZATION_POTENTIALLYCLEARONBOTHSIDES:
				return 'Potentiell beidseitig klar';
			case self::KUERZUNGSBETRAG_CATEGORIZATION_ONESIDEDCLEAR:
				return 'Einseitig klar';
			case self::KUERZUNGSBETRAG_CATEGORIZATION_TOBECLARIFIED:
				return 'Zu klären';
			case self::KUERZUNGSBETRAG_CATEGORIZATION_DEPOWEREDPOINT:
				return 'Entkräfteter Punkt';
		}
		return "?";
	}

	public static function GetEinsparungsTypName($einsparungsTyp)
	{
		switch($einsparungsTyp)
		{
			case self::KUERZUNGSBETRAG_EINSPARUNGSTYP_UNKNOWN:
				return '-';
			case self::KUERZUNGSBETRAG_EINSPARUNGSTYP_MAXIMALFORDERUNG:
				return 'Maximalforderung';
			case self::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_STANDARD:
				return 'Entgegenkommen Standard';
			case self::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_KUNDE:
				return 'Entgegenkommen zusätzlich (Kunde)';
			case self::KUERZUNGSBETRAG_EINSPARUNGSTYP_VERMIETERANGEBOT:
				return 'Vermieterangebot';
		}
		return "?";
	}
	
	/**
	 * Return the related contract
	 * @return Contract
	 */
	public function GetContract()
	{
		$wsp = $this->GetWiderspruchspunkt();
		if ($wsp==null) return null;
		return $wsp->GetContract();
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
	
}
?>