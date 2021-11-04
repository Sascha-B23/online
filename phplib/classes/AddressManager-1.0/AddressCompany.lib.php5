<?php
/**
 * Diese Klasse repräsentiert eine Fimra in der Addressdatenbank
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class AddressCompany extends DBEntry implements AttributeNameMaper, AddressBase
{	
	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "addresscompany";

	/**
	 * Prefix string for ID
	 */
	const ID_PREFIX = 'AC';
	
	/**
	 * Name Firma
	 * @var string
	 */
	protected $name="";

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
	 * Land
	 * @var string
	 */
	protected $country="";

	/**
	 * Telefon
	 * @var string
	 */
	protected $phone="";
	
	/**
	 * FAX
	 * @var string
	 */
	protected $fax="";
	
	/**
	 * Webseite
	 * @var string
	 */
	protected $website="";
		
	/**
	 * AddressGroup
	 * @var AddressGroup
	 */
	protected $addressGroup = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function AddressCompany(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("name", "email", "street", "zip", "city", "country", "phone", "fax", "website", "addressGroup");
		$dbConfig->rowParam = Array("VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "TEXT", "BIGINT");
		$dbConfig->rowIndex = Array("addressGroup");
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * return the class type 
	 * @return int
	 */
	public function GetClassType()
	{
		return AddressBase::AM_CLASS_ADDRESSCOMPANY;
	}
	
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @param DBManager $db
	 * @return bool
	 */
	public function IsDeletable(&$db)
	{
		if ($this->GetAddressDataCount($db)>0) return false;
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
		if ($this->name=="") return -1;
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "name";
		$rowData[]= $this->name;
		$rowName[]= "email";
		$rowData[]= $this->email;
		$rowName[]= "street";
		$rowData[]= $this->street;
		$rowName[]= "zip";
		$rowData[]= $this->zip;
		$rowName[]= "city";
		$rowData[]= $this->city;
		$rowName[]= "country";
		$rowData[]= $this->country;
		$rowName[]= "phone";
		$rowData[]= $this->phone;
		$rowName[]= "fax";
		$rowData[]= $this->fax;
		$rowName[]= "website";
		$rowData[]= $this->website;
		$rowName[]= "addressGroup";
		$rowData[]= ($this->addressGroup==null ? -1 : (int)$this->addressGroup->GetPKey());
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
		$this->name = $data['name'];
		$this->email = $data['email'];
		$this->street = $data['street'];
		$this->zip = $data['zip'];
		$this->city = $data['city'];
		$this->country = $data['country'];
		$this->phone = $data['phone'];
		$this->mobile = $data['mobile'];
		$this->fax = $data['fax'];
		$this->website = $data['website'];
		if ($data['addressGroup']!=-1)
		{
			$this->addressGroup = AddressManager::GetAddressGroupByPkey($db, (int)$data['addressGroup']);
		}
		return true;
	}
		
	/**
	 * Setzt Firma
	 * @param string $name
	 * @return bool
	 */
	public function SetName($name)
	{
		$this->name = $name;
		return true;
	}
	
	/**
	 * Gibt Firma zurück
	 * @return string
	 */
	public function GetName()
	{
		return $this->name;
	}
	
	/**
	 * Return the company name
	 * @return string
	 */
	public function GetCompany()
	{
		return $this->GetName();
	}
	
	/**
	 * Setzt die EMail-Adresse und prüft diese auf Richtigkeit
	 * @param string $email
	 * @return bool|integer Bei Erfolg true sonst siehe Funktion User::CheckFormatEMail 
	 */
	public function SetEMail($email)
	{
		if (trim($email)!='')
		{
			$email=strtolower(strip_tags(trim($email)));
			$retValue=User::CheckFormatEMail( $email );
			if( $retValue!==true )return $retValue;
		}
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
	public function GetStreet()
	{
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
	public function GetZIP()
	{
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
	public function GetCity()
	{
		return $this->city;
	}	
	
	/**
	 * Setzt Land
	 * @param string $country
	 * @return bool
	 */
	public function SetCountry($country)
	{
		$this->country=$country;
		return true;
	}
	
	/**
	 * Gibt Land zurück
	 * @return string
	 */
	public function GetCountry()
	{
		return $this->country;
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
	 * Setzt die Website
	 * @param string $fax
	 * @return bool
	 */
	public function SetWebsite($website)
	{
		$this->website=$website;
		return true;
	}
	
	/**
	 * Gibt die Website
	 * @return string
	 */
	public function GetWebsite()
	{
		return $this->website;
	}
	
	/**
	 * set the AddressGroup
	 * @param AddressGroup $addressGroup
	 * @return bool
	 */
	public function SetAddressGroup(AddressGroup $addressGroup=null)
	{
		if ($addressGroup!=null && $addressGroup->GetPKey()==-1) return false;
		$this->addressGroup = $addressGroup;
		return true;
	}
	
	/**
	 * return the AddressGroup
	 * @return AddressGroup
	 */
	public function GetAddressGroup()
	{
		return $this->addressGroup;
	}
	
	/**
	 * return the name of the group
	 * @return string
	 */
	public function GetAddressGroupName()
	{
		$addressGroup = $this->GetAddressGroup();
		return ($addressGroup!=null ? $addressGroup->GetName() : "");
	}
	
	/**
	 * Gibt einen eindeutigen String für diesen Datensatz zurück
	 * @return string
	 */
	public function GetOverviewString()
	{
		if ($this->GetPKey()==-1) return "";
		$str ="";
		$str.=$this->GetName();														// Company Name
		return $str;
	}
	
	/**
	 * Gibt einen eindeutigen String für diesen Datensatz zurück
	 * @param bool $noID
	 * @return string Company ([ACID: PKEY])
	 */
	public function GetAddressIDString($noID=false)
	{
		if ($this->GetPKey()==-1) return "";
		$str = $this->GetName();													// Company Name
		if (!$noID) $str .= " [".self::ID_PREFIX.$this->GetPKey()."]";				// AC[PKey]
		return $str;
	}
	
	/**
	 * Return the name and email in form "Titel Nachname, Vorname <email>"
	 * @return string 
	 */
	public function GetEmailString()
	{
		if ($this->GetPKey()==-1) return "";
		$str ='';
		$str.=$this->GetName();														// Company Name
		$str.=" <".$this->GetEMail().">";											// <email>
		return $str;
	}
	
	/**
	 * Returns the salutaion for this address data
	 * @param int $phrase Phrase to use:
	 *						0: none
	 *						1: Guten Tag
	 *						2: Hallo
	 * @return string
	 */
	public function GetSalutation(DBManager $db, $phrase = 0, $language='DE')
	{
		$phrases = Array(	0 => -1, 
							1 => StandardTextManager::STM_ADRESSDATA_PHRASE1_COMPANY, 
							2 => StandardTextManager::STM_ADRESSDATA_PHRASE2_COMPANY
						);
		$phraseObj = StandardTextManager::GetStandardTextById($db, $phrases[$phrase]);
		$str = '';
		if ($phraseObj!=null)
		{
			$str.=$phraseObj->GetStandardText($language).' ';	// Floskel
		}
		return $str;
	}
	
	/**
	 * Gibt die Anzahl der Firmen dieser Gruppe zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetAddressDataCount(DBManager $db)
	{
		if ($this->GetPKey()==-1) return false;
		$data = $db->SelectAssoc("SELECT count(pkey) as numAddressData FROM ".AddressData::TABLE_NAME." WHERE addressCompany=".$this->GetPKey());
		return (int)$data[0]["numAddressData"];
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
				return $languageManager->GetString('ADDRESSMANAGER', 'COMPANY_ID');
			case "name":
				return $languageManager->GetString('ADDRESSMANAGER', 'COMPANY_NAME');
			case "email":
				return $languageManager->GetString('ADDRESSMANAGER', 'COMPANY_EMAIL');
			case "street":
				return $languageManager->GetString('ADDRESSMANAGER', 'COMPANY_STREET');
			case "zip":
				return $languageManager->GetString('ADDRESSMANAGER', 'COMPANY_ZIP');
			case "city":
				return $languageManager->GetString('ADDRESSMANAGER', 'COMPANY_CITY');
			case "phone":
				return $languageManager->GetString('ADDRESSMANAGER', 'COMPANY_PHONE');
			case "fax":
				return $languageManager->GetString('ADDRESSMANAGER', 'COMPANY_FAX');
			case "country":
				return $languageManager->GetString('ADDRESSMANAGER', 'COMPANY_COUNTRY');
			case "website":
				return $languageManager->GetString('ADDRESSMANAGER', 'COMPANY_WEBSITE');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}
	
	/**
	 * Return default placeholders 
	 * @return Array
	 */
	public function GetPlaceholders(DBManager $db, $language = 'DE', $prefix = 'AP')
	{
		$placeHolders["%".$prefix."_FIRMA%"] = $this->GetName();
		$placeHolders["%".$prefix."_ANREDEKOMPLETT%"] = $this->GetSalutation($db, 1, $language);
		$placeHolders["%".$prefix."_ANREDE%"] = "";
		$placeHolders["%".$prefix."_VORNAME%"] = "";
		$placeHolders["%".$prefix."_NACHNAME%"] = "";
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