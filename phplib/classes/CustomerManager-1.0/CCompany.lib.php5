<?php
/**
 * Diese Klasse repräsentiert eine Firma (z.B. Esprit Retail BV & Co KG) 
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class CCompany extends DBEntry implements DependencyFileDescription, AttributeNameMaper
{
	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "customerCompany";	
	
	/**
	 * Name
	 * @var string
	 */
	protected $name="";
	
	/**
	 * Straße
	 * @var string
	 */
	protected $street="";
	
	/**
	 * PLZ
	 * @var string
	 **/
	protected $zip="";
	
	/**
	 * Stadt
	 * @var string
	 */
	protected $city="";
	
	/**
	 * Land
	 * @var string
	 */
	protected $country="";
	
	/**
	 * Anschreiben
	 * @var File[]
	 */
	protected $anschreiben = Array();
	
	/**
	 * Kontonummer
	 * @var string
	 */
	protected $kontoNr = "";
	
	/**
	 * BLZ
	 * @var string
	 */
	protected $blz = "";
	
	/**
	 * Name Bank
	 * @var string
	 */
	protected $bankName = "";
	
	/**
	 * IBAN
	 * @var string
	 */
	protected $iban = "";
	
	/**
	 * VAT
	 * @var string
	 */
	protected $vat = "";
	
	/**
	 * Anschreiben Betreff-Vorlage
	 * @var StandardText 
	 */
	protected $anschreibenVorlageBetreff = null;
	
	/**
	 * Anschreiben Text-Vorlage
	 * @var StandardText 
	 */
	protected $anschreibenVorlageText = null;
	
	/**
	 * Zugehörige Customer-Gruppe
	 * @var CGroup
	 */
	protected $cGroup=null;
	
	/**
	 * Languages
	 * @var string[] 
	 */
	protected $languages = Array();

	/**
	 * Konditions- und Fristenliste
	 * @var File
	 */
	protected $konditionsUndFristenliste = null;
	
	/**
	 * Konstruktor
	 * @param DBManager	$db
	 */
	function CCompany(DBManager $db)
	{
		$this->languages = CustomerManager::GetLanguagesISO639List($db);
		if (!is_array($this->languages) || count($this->languages)==0)
		{
			die('Error: no languages available in class CCompany');
			exit;
		}
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("name", "street", "zip", "city", "country", "kontoNr", "blz", "bankName", "iban", "vat", "anschreibenVorlageBetreff_rel", "anschreibenVorlageText_rel", "cGroup", "konditionsUndFristenliste");
		$dbConfig->rowParam = Array("VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "BIGINT", "BIGINT", "BIGINT", "BIGINT");
		$dbConfig->rowIndex = Array("cGroup");

		foreach ($this->languages as $value) 
		{
			$dbConfig->rowName[] = "anschreiben_".$value;
			$dbConfig->rowParam[] = "BIGINT";
		}
		
		parent::__construct($db, $dbConfig);
	}
		
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @param DBManager	$db
	 * @return bool
	 */
	public function IsDeletable(&$db)
	{
		if ($this->GetLocationCount($db)>0) return false;
		return parent::IsDeletable($db);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1	Firmenname nicht gesetzt
	 *								-2	Zugehörige Gruppe nicht gesetzt
	 *								-3	Firma mit diesem Namen existiert bereits
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// EMail-Adresse gesetzt?
		if( $this->name=="" )return -1;
		if( $this->cGroup==null || $this->cGroup->GetPKey()==-1 )return -2;
		// Prüfen, ob neue Firma
		if($this->pkey == -1)
		{
			// Prüfen ob Firma mit gleichem Namen bereits existiert
			if( count($db->SelectAssoc("SELECT pkey FROM ".self::TABLE_NAME." WHERE cGroup=".$this->cGroup->GetPKey()." AND name='".$db->ConvertStringToDBString($this->name)."'", false)) != 0)
			{
				// Firma mit disesem Namen existiert bereits
				return -3;
			}
		}
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "name";
		$rowData[]= $this->name;
		$rowName[]= "street";
		$rowData[]= $this->street;
		$rowName[]= "zip";
		$rowData[]= $this->zip;
		$rowName[]= "city";
		$rowData[]= $this->city;
		$rowName[]= "country";
		$rowData[]= $this->country;
		$rowName[]= "kontoNr";
		$rowData[]= $this->kontoNr;
		$rowName[]= "blz";
		$rowData[]= $this->blz;
		$rowName[]= "bankName";
		$rowData[]= $this->bankName;
		$rowName[]= "iban";
		$rowData[]= $this->iban;
		$rowName[]= "vat";
		$rowData[]= $this->vat;
		$rowName[]= "anschreibenVorlageBetreff_rel";
		$rowData[]= ($this->anschreibenVorlageBetreff==null ? 0 : $this->anschreibenVorlageBetreff->GetPKey());
		$rowName[]= "anschreibenVorlageText_rel";
		$rowData[]= ($this->anschreibenVorlageText==null ? 0 : $this->anschreibenVorlageText->GetPKey());
		$rowName[]= "cGroup";
		$rowData[]= $this->cGroup==null ? -1 : $this->cGroup->GetPKey();
		$rowName[]= "konditionsUndFristenliste";
		$rowData[]= $this->konditionsUndFristenliste==null ? -1 : $this->konditionsUndFristenliste->GetPKey();
		
		foreach ($this->languages as $value) 
		{
			$rowName[]= "anschreiben_".$value;
			$rowData[]= $this->anschreiben[$value]==null ? -1 : $this->anschreiben[$value]->GetPKey();
		}
		
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array  	$data		Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool			Erfolg
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->name = $data['name'];
		$this->street = $data['street'];
		$this->zip = $data['zip'];
		$this->city = $data['city'];
		$this->country = $data['country'];
		$this->kontoNr = $data['kontoNr'];
		$this->blz = $data['blz'];
		$this->bankName = $data['bankName'];
		$this->iban = $data['iban'];
		$this->vat = $data['vat'];
		$this->anschreibenVorlageBetreff = StandardTextManager::GetStandardTextById($db, $data['anschreibenVorlageBetreff_rel']==0 ? StandardTextManager::STM_WSANSCHREIBEN_SUBJECT : (int)$data['anschreibenVorlageBetreff_rel']);
		$this->anschreibenVorlageText = StandardTextManager::GetStandardTextById($db, $data['anschreibenVorlageText_rel']==0 ? StandardTextManager::STM_WSANSCHREIBEN : (int)$data['anschreibenVorlageText_rel']);
		if (is_object($data['cGroup']) && is_a($data['cGroup'], 'CGroup'))
		{
			// optimzed loading
			$this->cGroup = $data['cGroup'];
		}
		elseif( $data['cGroup']!=-1 )
		{
			$this->cGroup = CustomerManager::GetGroupByPkey($db, $data['cGroup']);
			/*$this->cGroup = new CGroup($db);
			if ($this->cGroup->Load($data['cGroup'], $db)!==true) $this->cGroup=null;*/
		}
		else
		{
			$this->cGroup=null;
		}
		
		foreach ($this->languages as $value) 
		{
			if ($data["anschreiben_".$value]!=-1)
			{
				$this->anschreiben[$value] = new File($db);
				if ($this->anschreiben[$value]->Load((int)$data["anschreiben_".$value], $db)!==true) $this->anschreiben[$value] = null;
			}
			else
			{
				$this->anschreiben[$value] = null;
			}
		}

		if ($data["konditionsUndFristenliste"]!=-1)
		{
			$this->konditionsUndFristenliste = new File($db);
			if ($this->konditionsUndFristenliste->Load((int)$data["konditionsUndFristenliste"], $db)!==true) $this->konditionsUndFristenliste = null;
		}
		
		return true;
	}
	
	/**
	 * Return Description
	 */
	public function GetDependencyFileDescription()
	{
		$group = $this->GetGroup();
		return ($group==null ? "?" : $group->GetDependencyFileDescription()).DependencyFileDescription::SEPERATOR.$this->GetName();
	}
	
	/**
	 * Setzt den Gruppennamen
	 * @param string $name
	 * @return bool
	 */
	public function SetName($name)
	{
		$this->name = $name;
		return true;
	}
	
	/**
	 * Gibt den Gruppennamen zurück
	 * @return string
	 */
	public function GetName()
	{
		return $this->name;
	}
	
	/**
	 * Setzt die Straße
	 * @param string $street
	 */
	public function SetStreet($street)
	{
		$this->street = $street;
	}
	
	/**
	 * Gibt die Straße zurück
	 * @return string
	 */
	public function GetStreet()
	{
		return $this->street; 
	}
	
	/**
	 * Setzt die PLZ
	 * @param string $zip
	 */
	public function SetZIP($zip)
	{
		$this->zip = $zip;
	}
	
	/**
	 * Gibt die PLZ zurück
	 * @return string
	 */
	public function GetZIP()
	{
		return $this->zip;
	}
	
	/**
	 * Setzt die Stadt
	 * @param string $city
	 */
	public function SetCity($city)
	{
		$this->city = $city;
	}
	
	/**
	 * Gibt die Stadt zurück
	 * @return string
	 */
	public function GetCity()
	{
		return $this->city;
	}
	
	/**
	 * Setzt das Land
	 * @param string $country
	 */
	public function SetCountry($country)
	{
		$this->country = $country;
	}
	
	/**
	 * Gibt das Land zurück
	 * @return string
	 */
	public function GetCountry()
	{
		return $this->country;
	}
	
	/**
	 * Setzt das Anschreiben
	 * @param string $language
	 * @param File $anschreiben
	 * @return boolean
	 */
	public function SetAnschreiben($language, $anschreiben)
	{
		if (!in_array($language, $this->languages)) return false;
		$this->anschreiben[$language] = $anschreiben;
		return true;
	}
	
	/**
	 * Gibt das Anschreiben zurück
	 * @param string $language
	 * @return File
	 */
	public function GetAnschreiben($language)
	{
		if (!in_array($language, $this->languages)) return false;
		if (!isset($this->anschreiben[$language])) return null;
		return $this->anschreiben[$language];
	}
	
	/**
	 * Setzt die Kontonummer
	 * @param string $kontoNr	
	 */
	public function SetKontoNr($kontoNr)
	{
		$this->kontoNr = $kontoNr;
	}
	
	/**
	 * Gibt die Kontonummer zurück
	 * @return string
	 */
	public function GetKontoNr()
	{
		return $this->kontoNr;
	}
	
	/**
	 * Setzt die Bankleitzahl
	 * @param string $blz	
	 */
	public function SetBlz($blz)
	{
		$this->blz = $blz;
	}
	
	/**
	 * Gibt die Bankleitzahl zurück
	 * @return string	
	 */
	public function GetBlz()
	{
		return $this->blz;
	}

	/**
	 * Setzt den Namen der Bank
	 * @param string $bankName	
	 */
	public function SetBankName($bankName)
	{
		$this->bankName = $bankName;
	}
	
	/**
	 * Gibt den Namen der Bank zurück
	 * @return string
	 */
	public function GetBankName()
	{
		return $this->bankName;
	}

	/**
	 * Setzt den IBAN
	 * @param string $iban	
	 */
	public function SetIban($iban)
	{
		$this->iban = $iban;
	}
	
	/**
	 * Gibt den IBAN zurück
	 * @return string
	 */
	public function GetIban()
	{
		return $this->iban;
	}
	
	/**
	 * Set VAT number
	 * @param string $vat	
	 */
	public function SetVat($vat)
	{
		$this->vat = $vat;
	}
	
	/**
	 * Get VAT number
	 * @return string
	 */
	public function GetVat()
	{
		return $this->vat;
	}
	
	/**
	 * Setzt das Anschreiben Betreff-Vorlage
	 * @param StandardText $anschreibenVorlageBetreff	
	 */
	public function SetAnschreibenVorlageBetreff(StandardText $anschreibenVorlageBetreff=null)
	{
		$this->anschreibenVorlageBetreff = $anschreibenVorlageBetreff;
	}
	
	/**
	 * Gibt das Anschreiben Betreff-Vorlage zurück
	 * @return StandardText
	 */
	public function GetAnschreibenVorlageBetreff()
	{
		return $this->anschreibenVorlageBetreff;
	}
	
	/**
	 * Setzt das Anschreiben Text-Vorlage
	 * @param StandardText $anschreibenVorlageText	
	 */
	public function SetAnschreibenVorlageText(StandardText $anschreibenVorlageText=null)
	{
		$this->anschreibenVorlageText = $anschreibenVorlageText;
	}
	
	/**
	 * Gibt das Anschreiben Text-Vorlage zurück
	 * @return StandardText
	 */
	public function GetAnschreibenVorlageText()
	{
		return $this->anschreibenVorlageText;
	}
	
	/**
	 * Gibt die Bankverbindung zurück
	 * @return string
	 */
	public function GetBankverbindung()
	{
		$bankverbindung = "Kontoinhaber: ".$this->GetName()."\n";
		$bankverbindung.= "Kontonummer: ".$this->GetKontoNr()."\n";
		$bankverbindung.= "Bankleitzahl: ".$this->GetBlz()."\n";
		$bankverbindung.= "Bankname: ".$this->GetBankName()."\n";
		$bankverbindung.= "IBAN: ".$this->GetIban()."\n";
		return $bankverbindung;
	}
		
	/**
	 * Gibt die zugehörige Customer-Gruppe zurück
	 * @return CGroup
	 */
	public function GetGroup()
	{
		return $this->cGroup;
	}
	
	/**
	 * Setzt die zugehörige Customer-Gruppe
	 * @param CGroup $cGroup
	 * @return bool
	 */
	public function SetGroup(CGroup $cGroup=null)
	{
		if ($cGroup==null)
		{
			$this->cGroup=null; 
			return true;
		}
		if ($cGroup->GetPKey()==-1) return false;
		$this->cGroup = $cGroup;
		return true;
	}
	
	/**
	 * Gibt die Anzahl der untergeordneten Standorte zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetLocationCount(DBManager $db)
	{
		if ($this->pkey==-1) return 0;
		$data = $db->SelectAssoc("SELECT count(pkey) as count FROM ".CLocation::TABLE_NAME." WHERE cCompany=".$this->pkey );
		return (int)$data[0]["count"];
	}
	
	/**
	 * Gibt alle untergeordneten Standorte zurück
	 * @param DBManager $db
	 * @return CLocation[]
	 */
	public function GetLocations(DBManager $db)
	{
		if ($this->pkey==-1) return Array();
		$data=$db->SelectAssoc("SELECT * FROM ".CLocation::TABLE_NAME." WHERE cCompany=".$this->pkey." ORDER BY name");
		$objects=Array();
		for($a=0; $a<count($data); $a++)
		{
			$object=new CLocation($db);
			$data[$a]['cCompany'] = $this;
			if ($object->LoadFromArray($data[$a], $db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Ordnet den übergebenen Standort dieser Firma unter
	 * @param DBManager $db
	 * @param CLocation $location
	 * @return bool
	 */
	public function AddLocation(DBManager $db, CLocation $location)
	{
		if ($this->pkey==-1) return false;
		// Den Standort dieser Firma zuweisen...
		return $location->SetCompany($db, $this);
	}
	
	/**
	 * Prüft, ob der übergebene Benutzer berechtigt ist, dieses Objekt zu verarbeiten
	 * @param User	$user	User to check
	 * @param DBManager $db
	 * @return bool
	 */
	public function HasUserAccess(User $user, DBManager $db)
	{
		$group = $this->GetGroup();
		return ($group==null ? false : $group->HasUserAccess($user, $db));
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
			case "name":
				return $languageManager->GetString('CUSTOMERMANAGER', 'COMPANY_NAME');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}

	/**
	 * Gibt die Konditions- und Fristenlese zurück
	 * @return File
	 */
	public function GetKonditionsUndFristenliste()
	{
		return $this->konditionsUndFristenliste;
	}

	/**
	 * Setzt die Konditions- und Fristenliste
	 * @param File $konditionsUndFristenliste
	 */
	public function SetKonditionsUndFristenliste($konditionsUndFristenliste)
	{
		$this->konditionsUndFristenliste = $konditionsUndFristenliste;
	}


	
}
?>