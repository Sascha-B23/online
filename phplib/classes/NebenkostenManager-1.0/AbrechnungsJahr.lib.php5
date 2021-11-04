<?php
/**
 * Diese Klasse repräsentiert ein Abrechnungs-Jahr
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class AbrechnungsJahr extends DBEntry implements DependencyFileDescription, AttributeNameMaper
{
	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "abrechnungsJahr";
		
	/**
	 * Jahr
	 * @var int
	 */
	protected $jahr = 0;

	/**
	 * Alle Teilabrechnungen erfasst
	 * @var int
	 */
	protected $alleTeilabrechnungenErfasst = 0;

	/**
	 * Zugehöriger Contract
	 * @var Contract
	 */
	protected $contract=null;
	
	/**
	* Konstruktor
	* @param DBManager $db
	*/
	public function AbrechnungsJahr(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("jahr", "alleTeilabrechnungenErfasst", "contract");
		$dbConfig->rowParam = Array("INT", "INT", "BIGINT");
		$dbConfig->rowIndex = Array("contract");
		parent::__construct($db, $dbConfig);
	}
		
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @return bool
	 */
	public function IsDeletable(&$db)
	{
		if ($this->GetTeilabrechnungCount($db)>0) return false;
		if ($this->GetWiderspruchCount($db)>0) return false;
		return parent::IsDeletable($db);
	}
	
	/**
	 * Delete this and all contained objects
	 * @param DBManager $db
	 * @param User $user 
	 * @return bool
	 */
	public function DeleteRecursive(DBManager $db, User $user)
	{
		if ($user->GetGroupBasetype($db) < UM_GROUP_BASETYPE_ADMINISTRATOR) return false;
		// delete all 'Widersprüche'...
		$widersprueche = $this->GetWidersprueche($db);
		for ($a=0; $a<count($widersprueche); $a++)
		{
			$widersprueche[$a]->DeleteRecursive($db, $user);
		}
		// delete all 'Teilabrechnungen'...
		$tas = $this->GetTeilabrechnungen($db);
		for ($a=0; $a<count($tas); $a++)
		{
			$tas[$a]->DeleteRecursive($db, $user);
		}
		$this->DeleteMe($db);
		return true;
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1	Zugehöriger Contract nicht gesetzt
	 *								-2	Das AbrechnungsJahr für den übergeordneten Vertrag existiert bereits
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		if ($this->contract==null || $this->contract->GetPKey()==-1) return -1;
		// Prüfen, ob es für den übergeordneten Vertrag bereits ein Eintarg zu diesem AbrechnungsJahr gibt
		$query = "SELECT pkey FROM ".self::TABLE_NAME." WHERE jahr=".$this->jahr." AND contract=".$this->contract->GetPKey();
		if ($this->pkey!=-1) $query.=" AND pkey!=".$this->pkey;
		if (count($db->SelectAssoc($query))!= 0)
		{
			// Das AbrechnungsJahr für den übergeordneten Vertrag existiert bereits
			return -2;
		}

		// Array mit zu speichernden Daten anlegen
		$rowName[]= "jahr";
		$rowData[]= $this->jahr;
		$rowName[]= "alleTeilabrechnungenErfasst";
		$rowData[]= $this->alleTeilabrechnungenErfasst;
		$rowName[]= "contract";
		$rowData[]= $this->contract==null ? -1 : $this->contract->GetPKey();
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array  	$data		Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->jahr = $data['jahr'];
		$this->alleTeilabrechnungenErfasst = $data['alleTeilabrechnungenErfasst'];
		if( $data['contract']!=-1 )
		{
			$this->contract = RSKostenartManager::GetContractByPkey($db, $data['contract']);
		}
		else
		{
			$this->contract=null;
		}
		return true;
	}
	
	/**
	 * Return Description
	 */
	public function GetDependencyFileDescription()
	{
		$contract = $this->GetContract();
		return ($contract==null ? "?" : $contract->GetDependencyFileDescription()).DependencyFileDescription::SEPERATOR.$this->GetJahr();
	}
	
	/**
	 * Setzt das Jahr
	 * @param int $jahr Jahr
	 * @return bool
	 */
	public function SetJahr($jahr)
	{
		if (!is_int($jahr)) return false;
		$this->jahr = $jahr;
		return true;
	}
	
	/**
	 * Gibt das Jahr zurück
	 * @return int
	 */
	public function GetJahr()
	{
		return $this->jahr;
	}
	
	/**
	 * Setzt, ob alle Teilabrechnungen erfasst wurden
	 * @param bool $alleTeilabrechnungenErfasst Alle Teilabrechnungen erfasst
	 * @return bool
	 */
	public function SetAlleTeilabrechnungenErfasst($alleTeilabrechnungenErfasst)
	{
		if (!is_bool($alleTeilabrechnungenErfasst)) return false;
		$this->alleTeilabrechnungenErfasst = ($alleTeilabrechnungenErfasst ? 1 : 0);
		return true;
	}
	
	/**
	 * Gibt zurück, ob alle Teilabrechnungen erfasst wurden
	 * @return bool
	 */
	public function GetAlleTeilabrechnungenErfasst()
	{
		return ($this->alleTeilabrechnungenErfasst==1 ? true : false);
	}
				
	/**
	 * Gibt den zugehörigen Contract zurück
	 * @return Contract
	 */
	public function GetContract()
	{
		return $this->contract;
	}
	
	/**
	 * Setzt den zugehörige Contract
	 * @param Contract $contract
	 * @return bool
	 */
	public function SetContract($contract)
	{
		if ($contract==null || get_class($contract)!="Contract" || $contract->GetPKey()==-1) return false;
		$this->contract=$contract;
		return true;
	}
		
	/**
	 * Gibt die Anzahl der Teilabrechnungen zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetTeilabrechnungCount(DBManager $db)
	{
		if ($this->pkey==-1) return 0;
		$data = $db->SelectAssoc("SELECT count(pkey) as numEntries FROM ".Teilabrechnung::TABLE_NAME." WHERE abrechnungsJahr=".$this->pkey);
		return (int)$data[0]["numEntries"];
	}
	
	/**
	 * Gibt alle Teilabrechnungen zurück
	 * @param DBManager $db
	 * @return Teilabrechnung[]
	 */
	public function GetTeilabrechnungen(DBManager $db)
	{
		if ($this->pkey==-1) return Array();
		$data = $db->SelectAssoc("SELECT * FROM ".Teilabrechnung::TABLE_NAME." WHERE abrechnungsJahr=".$this->pkey." ORDER BY abrechnungszeitraumVon");
		$objects = Array();
		for ($a=0; $a<count($data); $a++)
		{
			$object = new Teilabrechnung($db);
			if ($object->LoadFromArray($data[$a], $db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Ordnet der Teilabrechnung dieses Abrechnungsjahr zu
	 * @param DBManager $db
	 * @param Teilabrechnung $teilabrechnung
	 * @return bool
	 */
	public function AddTeilabrechnung(DBManager $db, Teilabrechnung $teilabrechnung)
	{
		if ($this->pkey==-1) return false;
		// Der Teilabrechnung dieses Abrechnungsjahr zuweisen...
		return $teilabrechnung->SetAbrechnungsJahr($this);
	}
	
	/**
	 * Gibt den zugehörigen ProcessStatus zurück
	 * @param DBManager $db
	 * @return ProcessStatus
	 */
	public function GetProcessStatus(DBManager $db)
	{
		if ($this->pkey==-1) return null;
		$data = $db->SelectAssoc("SELECT pkey FROM ".ProcessStatus::TABLE_NAME." WHERE abrechnungsjahr=".$this->pkey);
		if (count($data)==0) return null;
		$object = new ProcessStatus($db);
		if ($object->Load($data[0]["pkey"], $db)!==true) return null;
		return $object;
	}
	
	/**
	 * Setzt den zugehörigen ProcessStatus
	 * @param DBManager $db
	 * @param ProcessStatus $processStatus
	 * @return bool
	 */
	public function SetProcessStatus(DBManager $db, ProcessStatus $processStatus)
	{
		if ($this->pkey==-1) return false;
		// Dem ProcessStatus dieses AbrechnungsJahr zuweisen...
		return $processStatus->SetAbrechnungsJahr($db, $this);
	}
	
	/**
	 * Gibt die Anzahl der Widersprüche zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetWiderspruchCount(DBManager $db)
	{
		if ($this->pkey==-1) return 0;
		$data = $db->SelectAssoc("SELECT count(pkey) as numEntries FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".$this->pkey);
		return (int)$data[0]["numEntries"];
	}
	
	/**
	 * Gibt alle Widersprüche zurück
	 * @param DBManager $db
	 * @return Widerspruch[]
	 */
	public function GetWidersprueche(DBManager $db)
	{
		if ($this->pkey==-1) return Array();
		$data = $db->SelectAssoc("SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".$this->pkey." ORDER BY widerspruchsNummer");
		$objects = Array();
		for ($a=0; $a<count($data); $a++)
		{
			$object = new Widerspruch($db);
			if ($object->Load($data[$a]["pkey"], $db)===true) $objects[] = $object;
		}
		return $objects;
	}
	
	/**
	 * Gibt den aktuellen Widerspruch zurück
	 * @param DBManager $db
	 * @return Widerspruch
	 */
	public function GetCurrentWiderspruch(DBManager $db)
	{
		if ($this->pkey==-1) return null;
		$data = $db->SelectAssoc("SELECT pkey FROM ".Widerspruch::TABLE_NAME." WHERE abrechnungsJahr=".$this->pkey." ORDER BY widerspruchsNummer DESC LIMIT 0,1");
		if (count($data)==0) return null;
		$object = new Widerspruch($db);
		if ($object->Load($data[0]["pkey"], $db)!==true) return null;
		return $object;
	}
	
	/**
	 * Ordnet dem Widerspruch diese Teilabrechnung zu
	 * @param DBManager $db
	 * @param Widerspruch $widerspruch
	 * @return bool
	 */
	public function AddWiderspruch(DBManager $db, Widerspruch $widerspruch)
	{
		if ($this->pkey==-1) return false;
		// Dem Widerspruch dieses Abrechnungsjahr zuweisen...
		return $widerspruch->SetAbrechnungsJahr($this);
	}
	
	/**
	 * Gibt die Anzahl der Tage dieses Jahrs zurück
	 * @return int Anzhal Tage (365 oder 366)
	 */
	public function GetDaysOfYear()
	{
		$year = $this->GetJahr();
		$firstDay = mktime(0,0,0,1,1,$year);
		$lastDay = mktime(0,0,0,12,31,$year);
		return (int)((($lastDay - $firstDay)/60/60/24)+1);
	}
	
	/**
	 * Gibt die Anzahl der Tage dieses Jahrs zurück
	 * @param DBManager $db
	 * @return int Anzhal Tage (365 oder 366)
	 */
	public function GetSummeBetragKunde(DBManager $db)
	{
		$summe=0.0;
		$tas=$this->GetTeilabrechnungen($db);
		for($a=0; $a<count($tas); $a++)
		{
			$summe+=$tas[$a]->GetSummeBetragKunde($db);
		}
		return $summe;
	}
	
	/**
	 * Gibt das Vorjahr zurück
	 * @param DBManager $db
	 * @return Abrechnungsjahr
	 */
	public function GetVorjahr(DBManager $db)
	{
		if ($this->pkey==-1) return null;
		// Vertrag holen
		$contract = $this->GetContract();
		if ($contract==null) return null;
		// Shop holen
		$shop = $contract->GetShop();
		if ($shop==null) return null;
		// Abrechnungsjahre des Vorjahres vom Shop holen
		$vorjahre = $shop->GetAbrechnungsjahr($db, ((int)$this->GetJahr())-1);
		if (count($vorjahre)==0) return null;
		return $vorjahre[0];
	}
	
	/**
	 * Prüft, ob der übergebene Benutzer berechtigt ist, dieses Objekt zu verarbeiten
	 * @param User $user
	 * @param DBManager $db
	 * @return bool
	 */
	public function HasUserAccess(User $user, DBManager $db)
	{
		$contract = $this->GetContract();
		return ($contract==null ? false : $contract->HasUserAccess($user, $db) );
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
			case 'jahr':
				return $languageManager->GetString('NEBENKOSTENMANAGER', 'AJ_JAHR');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}
	
	/**
	 * return default placeholders 
	 * @return Array
	 */
	public function GetPlaceholders(DBManager $db, $language = 'DE', $prefix = '')
	{
		if ($prefix!='') $prefix.='_';
		$placeHolders["%".$prefix."JAHR%"] = $this->GetJahr();
		return $placeHolders;
	}
	
}
?>