<?php
/**
 * Diese Klasse repräsentiert einen Address-Datensatz
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class AddressData extends DBEntry implements AttributeNameMaper, AddressBase
{	
	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "addressdata";

	/**
	 * Prefix string for ID
	 */
	const ID_PREFIX = 'AP';
	
	/**
	 * Anrede
	 * @var int
	 */
	protected $title=0;

	/**
	 * Anrede (Prof. oder Dr.)
	 * @var string
	 */
	protected $title2="";
	
	/**
	 * Nachname
	 * @var string
	 */
	protected $name="";

	/**
	 * Vorname
	 * @var string
	 */
	protected $firstname="";
	
	/**
	 * 
	 * @var string
	 */
	protected $role="";

	/**
	 * EMail-Addresse
	 * @var string
	 */
	protected $email="";

	/**
	 * Straße 
	 * @var string
	 */
	protected $street="";

	/**
	 * PLZ
	 * @var string
	 */
	protected $zip="";

	/**
	 * Stadt
	 * @var string
	 */
	protected $city="";

	/**
	 * Telefon
	 * @var string
	 */
	protected $phone="";

	/**
	 * Mobiltelefon
	 * @var string
	 */
	protected $mobile="";
	
	/**
	 * FAX
	 * @var string
	 */
	protected $fax="";

	/**
	 * Kontakt-Typ (Anwalt, Verwalter, Eigentümer, Systembenutzer)
	 * @var long
	 */
	protected $type=AM_ADDRESSDATA_TYPE_NONE;
	
	/**
	 * Short name
	 * @var string 
	 */
	protected $shortName = "";
			
	/**
	 * AddressCompany
	 * @var AddressCompany
	 */
	protected $addressCompany = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function AddressData(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("title", "title2", "name", "firstname", "role", "email", "street", "zip", "city", "phone", "mobile", "fax", "type", "shortName", "addressCompany");
		$dbConfig->rowParam = Array("INT", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "BIGINT", "VARCHAR(255)", "BIGINT");
		$dbConfig->rowIndex = Array("addressCompany");
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * return the class type 
	 * @return int
	 */
	public function GetClassType()
	{
		return AddressBase::AM_CLASS_ADDRESSDATA;
	}
	
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @param DBManager $db
	 * @return bool
	 */
	public function IsDeletable(&$db)
	{
		if ($this->IsSystemUserAddress($db)) return false;
		return parent::IsDeletable($db);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1	Name nicht gesetzt
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// Name gesetzt?
		if (trim($this->name)=="") return -1;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "title";
		$rowData[]= $this->title;
		$rowName[]= "title2";
		$rowData[]= $this->title2;
		$rowName[]= "name";
		$rowData[]= $this->name;
		$rowName[]= "firstname";
		$rowData[]= $this->firstname;
		$rowName[]= "role";
		$rowData[]= $this->role;
		$rowName[]= "email";
		$rowData[]= $this->email;
		$rowName[]= "street";
		$rowData[]= $this->street;
		$rowName[]= "zip";
		$rowData[]= $this->zip;
		$rowName[]= "city";
		$rowData[]= $this->city;
		$rowName[]= "phone";
		$rowData[]= $this->phone;
		$rowName[]= "mobile";
		$rowData[]= $this->mobile;
		$rowName[]= "fax";
		$rowData[]= $this->fax;
		$rowName[]= "type";
		$rowData[]= $this->type;
		$rowName[]= "shortName";
		$rowData[]= $this->shortName;
		$rowName[]= "addressCompany";
		$rowData[]= ($this->addressCompany==null ? -1 : $this->addressCompany->GetPKey());
		
		return true;
	}
	
	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbank
	 * @param array $data Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->title = $data['title'];
		$this->title2 = $data['title2'];
		$this->name = $data['name'];
		$this->firstname = $data['firstname'];
		$this->role = $data['role'];
		$this->email = $data['email'];
		$this->street = $data['street'];
		$this->zip = $data['zip'];
		$this->city = $data['city'];
		$this->phone = $data['phone'];
		$this->mobile = $data['mobile'];
		$this->fax = $data['fax'];
		$this->type = $data['type'];
		$this->shortName = $data['shortName'];
		if ($data['addressCompany']!=-1)
		{
			$this->addressCompany = AddressManager::GetAddressCompanyByPkey($db, (int)$data['addressCompany']);
		}
		return true;
	}

	/**
	 * Setzt die Anrede
	 * @param int $title Anrede (0 = Herr, 1 = Frau)
	 * @return bool
	 */
	public function SetTitle($title)
	{
		if( $title!=0 && $title!=1 )return false;
		$this->title=$title;
		return true;
	}
	
	/**
	 * Gibt die Anrede zurück
	 * @return int Anrede (0 = Herr, 1 = Frau)
	 */
	public function GetTitle()
	{
		return $this->title;
	}
	
	/**
	 * Setzt die Anrede (z.B. Prof., Dr. etc.)
	 * @param string $title
	 * @return bool
	 */
	public function SetTitle2($title)
	{
		$this->title2=$title;
		return true;
	}
	
	/**
	 * Gibt die Anrede zurück (z.B. Prof., Dr. etc.)
	 * @return string 
	 */
	public function GetTitle2()
	{
		return $this->title2;
	}
	
	/**
	 * Setzt den Namen
	 * @param string $name
	 * @return bool
	 */
	public function SetName($name)
	{
		if (trim($name)=='') return false;
		$this->name = $name;
		return true;
	}
	
	/**
	 * Gibt den Namen zurück
	 * @return string
	 */
	public function GetName()
	{
		return $this->name;
	}

	/**
	 * Setzt den Vornamen
	 * @param string $firstname
	 * @return bool
	 */
	public function SetFirstName($firstname)
	{
		$this->firstname=$firstname;
		return true;
	}
	
	/**
	 * Gibt den Vornamen zurück
	 * @return string 
	 */
	public function GetFirstName()
	{
		return $this->firstname;
	}	
	
	/**
	 * Setzt Funktion
	 * @param string $role
	 * @return bool
	 */
	public function SetRole($role)
	{
		$this->role=$role;
		return true;
	}
	
	/**
	 * Gibt Funktion zurück
	 * @return string
	 */
	public function GetRole()
	{
		return $this->role;
	}	
		
	/**
	 * Setzt die EMail-Adresse und prüft diese auf Richtigkeit
	 * @param string $email
	 * @return bool|integer Bei Erfolg true sonst siehe Funktion User::CheckFormatEMail 
	 */
	public function SetEMail($email)
	{
		$email=strtolower(strip_tags(trim($email)));
		$retValue=User::CheckFormatEMail( $email );
		if( $retValue!==true )return $retValue;
		$this->email = $email;
		return true;
	}

	/**
	 * Gibt die Emailadresse des Users zurück
	 * @return string
	 */
	public function GetEMail()
	{
		return $this->email;
	}

	/**
	 * check if the zip, city and street of the company should be returned instead of the own one
	 * @return boolean
	 */
	public function UseAddressFromCompany()
	{
		$addressCompany = $this->GetAddressCompany();
		if ($addressCompany==null) return false;
		// if one of the following attributes is set we use the address of this object
		if (trim($this->GetStreet(true))!='') return false;
		if (trim($this->GetCity(true))!='') return false;
		if (trim($this->GetZIP(true))!='') return false;
		// use the company address
		return true;
	}
	
	/**
	 * Setzt Straße
	 * @param string $street Straße
	 * @return bool
	 */
	public function SetStreet($street)
	{
		$this->street=$street;
		return true;
	}
	
	/**
	 * Gibt Straße zurück
	 * @return string
	 */
	public function GetStreet($ignoreCompany=false)
	{
		if (!$ignoreCompany && $this->UseAddressFromCompany())
		{
			$addressCompany = $this->GetAddressCompany();
			if ($addressCompany!=null) return $addressCompany->GetStreet();
		}
		return $this->street;
	}	
	
	/**
	 * Setzt PLZ
	 * @param string $zip
	 * @return bool
	 */
	public function SetZIP($zip)
	{
		$this->zip=$zip;
		return true;
	}
	
	/**
	 * Gibt PLZ zurück
	 * @return string
	 */
	public function GetZIP($ignoreCompany=false)
	{
		if (!$ignoreCompany && $this->UseAddressFromCompany())
		{
			$addressCompany = $this->GetAddressCompany();
			if ($addressCompany!=null) return $addressCompany->GetZIP();
		}
		return $this->zip;
	}	

	/**
	 * Setzt Stadt
	 * @param string $city
	 * @return bool
	 */
	public function SetCity($city)
	{
		$this->city=$city;
		return true;
	}
	
	/**
	 * Gibt Stadt zurück
	 * @return string
	 */
	public function GetCity($ignoreCompany=false)
	{
		if (!$ignoreCompany && $this->UseAddressFromCompany())
		{
			$addressCompany = $this->GetAddressCompany();
			if ($addressCompany!=null) return $addressCompany->GetCity();
		}
		return $this->city;
	}	
		
	/**
	 * Setzt Telefon
	 * @param string $phone
	 * @return bool
	 */
	public function SetPhone($phone)
	{
		$this->phone=$phone;
		return true;
	}
	
	/**
	 * Gibt Telefon zurück
	 * @return string
	 */
	public function GetPhone()
	{
		return $this->phone;
	}
	
	/**
	 * Setzt Mobiltelefon
	 * @param string $mobile
	 * @return bool
	 */
	public function SetMobile($mobile)
	{
		$this->mobile=$mobile;
		return true;
	}
	
	/**
	 * Gibt Mobiltelefon zurück
	 * @return string
	 */
	public function GetMobile()
	{
		return $this->mobile;
	}	
		
	/**
	 * Setzt Fax
	 * @param string $fax
	 * @return bool
	 */
	public function SetFax($fax)
	{
		$this->fax=$fax;
		return true;
	}
	
	/**
	 * Gibt Fax zurück
	 * @return string
	 */
	public function GetFax()
	{
		return $this->fax;
	}
	
	/**
	 * Gibt den Kontakt-Typ (Anwalt, Verwalter, Eigentümer, Systembenutzer) zurück
	 * @param int $type
	 * @return bool
	 */
	public function SetType($type)
	{
		$this->type=$type;
		return true;
	}
	
	/**
	 * Gibt den Kontakt-Typ (Anwalt, Verwalter, Eigentümer, Systembenutzer) zurück
	 * @return int
	 */
	public function GetType()
	{
		return $this->type;
	}
	
	/**
	 * Set the short name
	 * @param string $shortName
	 * @return bool
	 */
	public function SetShortName($shortName)
	{
		$this->shortName=$shortName;
		return true;
	}
	
	/**
	 * Return the short name
	 * @return string
	 */
	public function GetShortName()
	{
		return $this->shortName;
	}
	
	/**
	 * set the AddressCompany
	 * @param AddressCompany $addressCompany
	 * @return bool
	 */
	public function SetAddressCompany(AddressCompany $addressCompany=null)
	{
		if ($addressCompany!=null && $addressCompany->GetPKey()==-1) return false;
		$this->addressCompany = $addressCompany;
		return true;
	}
	
	/**
	 * return the AddressCompany
	 * @return AddressCompany
	 */
	public function GetAddressCompany()
	{
		return $this->addressCompany;
	}
	
	/**
	 * Return the company name
	 * @return string
	 */
	public function GetCompany()
	{
		$addressCompany = $this->GetAddressCompany();
		return ($addressCompany!=null ? $addressCompany->GetName() : "");
	}
	
	/**
	 * return the name of the group
	 * @return string
	 */
	public function GetAddressGroupName()
	{
		$addressCompany = $this->GetAddressCompany();
		return ($addressCompany!=null ? $addressCompany->GetAddressGroupName() : "");
	}
	
	/**
	 * Gibt einen eindeutigen String für diesen Datensatz zurück
	 * @return string
	 */
	public function GetOverviewString()
	{
		if( $this->GetPKey()==-1 )return "";
		$str = "";
		if( trim($this->GetTitle2())!="" ) $str .= trim($this->GetTitle2())." ";
		$str .= $this->GetName();
		if( $this->GetFirstName() != ""){ $str .= ", ".$this->GetFirstName(); }
		return $str;
	}
	
	/**
	 * Gibt einen eindeutigen String für diesen Datensatz zurück
	 * @param bool $noID
	 * @return string Company (Nachname,  Vorname) ([ID: PKEY]
	 */
	public function GetAddressIDString($noID=false)
	{
		if( $this->GetPKey()==-1 )return "";
		$str = $this->GetCompany();													// Company
		$str .= " (";																// (
		if( trim($this->GetTitle2())!="" ) $str .= trim($this->GetTitle2())." ";	// Dr. Prof.
		$str .= $this->GetName();													// Name
		if( $this->GetFirstName() != ""){ $str .= ", ".$this->GetFirstName(); }		// , Vorname
		$str .= " )";
		if(!$noID)$str .= " [".self::ID_PREFIX.$this->GetPKey()."]";				// AP[PKey]
		return $str;
	}
	
	/**
	 * Return the name and email in form "Titel Nachname, Vorname <email>"
	 * @return string 
	 */
	public function GetEmailString()
	{
		if ($this->GetPKey()==-1) return "";
		$str = '';
		if (trim($this->GetTitle2())!='') $str.=trim($this->GetTitle2())." ";		// Dr. Prof.
		$str.=$this->GetName();														// Name
		if ($this->GetFirstName()!="") $str.=", ".$this->GetFirstName();			// , Vorname
		if(!$noID)$str .= " <".$this->GetEMail().">";								// <email>
		return $str;
	}
	
	/**
	 * Gibt zurück, ob es sich um die Adresse eines Systembenutzers handelt
	 * @return bool Adresse eines Systembenutzers?
	 */
	public function IsSystemUserAddress($db)
	{
		if ($this->GetPKey()==-1) return false;
		$data = $db->SelectAssoc("SELECT pkey FROM ".User::TABLE_NAME." WHERE addressData=".$this->GetPKey());
		if (count($data)==0) return false;
		return true;	
	}
	
	/**
	 * Returns the salutaion for this address data
	 * @param int $phrase Phrase to use:
	 *						0: none
	 *						1: Sehr geehrte(r) ...
	 *						2: Hallo ...
	 * @return string
	 */
	public function GetSalutation(DBManager $db, $phrase = 0, $language='DE')
	{
		$phrases = Array(0 => Array(0 => -1, 1 => -1),
						1 => Array(0 => StandardTextManager::STM_ADRESSDATA_PHRASE1_MALE, 1 => StandardTextManager::STM_ADRESSDATA_PHRASE1_FEMALE),
						2 => Array(0 => StandardTextManager::STM_ADRESSDATA_PHRASE2_MALE, 1 => StandardTextManager::STM_ADRESSDATA_PHRASE2_FEMALE),
						3 => Array(0 => -1, 1 => -1),
				);
		$phraseObj = StandardTextManager::GetStandardTextById($db, $phrases[$phrase][$this->GetTitle()]);
		$str = '';
		if ($phraseObj!=null)
		{
			$str.=$phraseObj->GetStandardText($language).' ';	// Floskel
		}
		//$str = $phrases[$phrase][$this->GetTitle()];									// Floskel
		//$str.= $this->GetTitle()==0 ? "Herr " : "Frau ";							// Herr / Frau
		if (trim($this->GetTitle2())!="") $str.=trim($this->GetTitle2())." ";	// [Dr. Prof.]
		if ($phrase==3 && trim($this->GetFirstName())!="") $str.=$this->GetFirstName()." ";	// [Vorname]
		$str .= $this->GetName();													// Name
		return $str;
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
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_ID');
			case "title":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_TITLE');
			case "title2":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_TITLE2');
			case "name":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_NAME');
			case "firstname":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_FIRSTNAME');
			case "role":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_ROLE');
			case "email":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_EMAIL');
			case "street":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_STREET');
			case "zip":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_ZIP');
			case "city":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_CITY');
			case "phone":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_PHONE');
			case "mobile":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_MOBILE');
			case "fax":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_FAX');
			case "type":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_TYPE');
			case "shortName":
				return $languageManager->GetString('ADDRESSMANAGER', 'ADDRESSDATA_SHORTNAME');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}
	
	/**
	 * Return default placeholders 
	 * @return Array
	 */
	public function GetPlaceholders(DBManager $db, $language = 'DE', $prefix = 'AP')
	{
		$title = StandardTextManager::GetStandardTextById($db, $this->GetTitle()==0 ? StandardTextManager::STM_ADDRESSDATA_TITLE_MR : StandardTextManager::STM_ADDRESSDATA_TITLE_MS);
		$placeHolders["%".$prefix."_FIRMA%"] = $this->GetCompany();
		$placeHolders["%".$prefix."_ANREDEKOMPLETT%"] = $this->GetSalutation($db, 1, $language);
		$placeHolders["%".$prefix."_ANREDE%"] = ($title!=null ? $title->GetStandardText($language) : '');
		$placeHolders["%".$prefix."_VORNAME%"] = $this->GetFirstName();
		$placeHolders["%".$prefix."_NACHNAME%"] = $this->GetName();
		$placeHolders["%".$prefix."_STRASSE%"] = $this->GetStreet();
		$placeHolders["%".$prefix."_PLZ%"] = $this->GetZIP();
		$placeHolders["%".$prefix."_STADT%"] = $this->GetCity();
		$placeHolders["%".$prefix."_TEL%"] = $this->GetPhone();
		$placeHolders["%".$prefix."_FAX%"] = $this->GetFax();
		$placeHolders["%".$prefix."_EMAIL%"] = $this->GetEMail();
		return $placeHolders;
	}
	
}
?>