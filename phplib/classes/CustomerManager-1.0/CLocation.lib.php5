<?php
// Konstanten des CustomerManagers
define("CM_LOCATION_TYPE_NONE", 0);
define("CM_LOCATION_TYPE_CENTER", 1);
define("CM_LOCATION_TYPE_OUTLET", 2);
define("CM_LOCATION_TYPE_COMMERCIALBUILDING", 3);
define("CM_LOCATION_TYPE_STANDALONE", 4);
define("CM_LOCATION_TYPE_FACHMARKTCENTER", 5);
define("CM_LOCATION_TYPE_MISCELLANEOUS", 6);

$CM_LOCATION_TYPES = Array(	0 => Array("id" => CM_LOCATION_TYPE_NONE, "name" => "Nicht angegeben"),
							1 => Array("id" => CM_LOCATION_TYPE_CENTER, "name" => "Center"),
							2 => Array("id" => CM_LOCATION_TYPE_OUTLET, "name" => "Outlet"),
							3 => Array("id" => CM_LOCATION_TYPE_COMMERCIALBUILDING, "name" => "Geschäftshaus"),
							4 => Array("id" => CM_LOCATION_TYPE_STANDALONE, "name" => "Standalone"),
							5 => Array("id" => CM_LOCATION_TYPE_FACHMARKTCENTER, "name" => "Fachmarktzentrum"),
							6 => Array("id" => CM_LOCATION_TYPE_MISCELLANEOUS, "name" => "Sonstige (Wohnung, Büro usw.)")
						  );

function GetLocationName($id){
	global $CM_LOCATION_TYPES;
	for($a=0; $a<count($CM_LOCATION_TYPES); $a++){
		if( $CM_LOCATION_TYPES[$a]["id"]==$id )return $CM_LOCATION_TYPES[$a]["name"];
	}
	return "";
}

function GetLocationId($name){
	global $CM_LOCATION_TYPES;
	if(trim($name)==""){ 
		return CM_LOCATION_TYPE_NONE; 
	}
	for($a=0; $a<count($CM_LOCATION_TYPES); $a++){
		if( $CM_LOCATION_TYPES[$a]["name"] == trim($name) ){ 
			return $CM_LOCATION_TYPES[$a]["id"]; 
		}
	}
	return -1;
}


/**
 * Diese Klasse repräsentiert einen Standort (z.B. Breuningerland)
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class CLocation extends DBEntry implements DependencyFileDescription, AttributeNameMaper
{

	/**
	 * Datenbankname und Spalten
	 * @var string
	 */
	const TABLE_NAME = "customerLocation";
	
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
	 */
	protected $zip="";
	
	/**
	 * Stadt
	 * @var string
	 */
	protected $city="";
	
	/**
	 * Objektbeschriebung
	 * @var string
	 */
	protected $objectDescription="";
	
	/**
	 * Standorttyp
	 * @var int
	 */
	protected $locationType=CM_LOCATION_TYPE_NONE;
	
	/**
	 * Zugehörige Customer-Company
	 * @var CCompany
	 */
	protected $cCompany=null;

	/**
	 * Land, in welchem der Standort steht
	 * @var string
	 */
	protected $country = "DE";
	
	/**
	 * Konstruktor
	 * @param DBManager	$db
	 */
	public function CLocation(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("name", "street", "zip", "city", "objectDescription", "locationType", "country", "cCompany");
		$dbConfig->rowParam = Array("VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "INT", "VARCHAR(255)", "BIGINT");
		$dbConfig->rowIndex = Array("cCompany");
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @return bool			Kann der Datensatz gelöscht werden (true/false)
	 */
	public function IsDeletable(&$db)
	{
		if( $this->GetShopCount($db)>0 )return false;
		return parent::IsDeletable($db);
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1	Standortname nicht gesetzt
	 *								-2	Zugehörige Firma nicht gesetzt
	 *								-3	Standort mit diesem Namen existiert bereits
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// EMail-Adresse gesetzt?
		if( $this->name=="" )return -1;
		if( $this->cCompany==null || $this->cCompany->GetPKey()==-1 )return -2;
		// Prüfen, ob neuer Standort
		if($this->pkey == -1){
			// Prüfen ob Standort mit gleichem Namen bereits existiert
			if( count($db->SelectAssoc("SELECT pkey FROM ".self::TABLE_NAME." WHERE cCompany=".$this->cCompany->GetPKey()." AND name='".$db->ConvertStringToDBString($this->name)."'", false)) != 0){
				// Standort mit disesem Namen existiert bereits
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
		$rowName[]= "objectDescription";
		$rowData[]= $this->objectDescription;
		$rowName[]= "locationType";
		$rowData[]= $this->locationType;
		$rowName[]= "country";
		$rowData[]= $this->country;
		$rowName[]= "cCompany";
		$rowData[]= $this->cCompany==null ? -1 : $this->cCompany->GetPKey();
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
		$this->objectDescription = $data['objectDescription'];
		$this->locationType = (int)$data['locationType'];
		$this->country = $data['country'];
		if (is_object($data['cCompany']) && is_a($data['cCompany'], 'CCompany'))
		{
			// optimzed loading
			$this->cCompany = $data['cCompany'];
		}
		elseif ($data['cCompany']!=-1)
		{
			$this->cCompany = CustomerManager::GetCompanyByPkey($db, $data['cCompany']);
			/*$this->cCompany = new CCompany($db);
			if ($this->cCompany->Load($data['cCompany'], $db)!==true) $this->cCompany=null;*/
		}
		else
		{
			$this->cCompany=null;
		}
		return true;
	}
	
	/**
	 * Return Description
	 */
	public function GetDependencyFileDescription()
	{
		$company = $this->GetCompany();
		return ($company==null ? "?" : $company->GetDependencyFileDescription()).DependencyFileDescription::SEPERATOR.$this->GetName();
	}
	
	/**
	 * Setzt den Namen
	 * @param string $name
	 * @return bool
	 */
	public function SetName($name)
	{
		$this->name=$name;
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
	 * Setzt die Straße
	 * @param string $street
	 * @return bool
	 */
	public function SetStreet($street)
	{
		$this->street=$street;
		return true;
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
	 * @return bool
	 */
	public function SetZip($zip)
	{
		$this->zip=$zip;
		return true;
	}
	
	/**
	 * Gibt die PLZ zurück
	 * @return string
	 */
	public function GetZip()
	{
		return $this->zip;
	}
	
	/**
	 * Setzt die Stadt
	 * @param string $city
	 * @return bool
	 */
	public function SetCity($city)
	{
		$this->city=$city;
		return true;
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
	 * Setzt die Objektbeschriebung
	 * @param string $objectDescription
	 * @return bool
	 */
	public function SetObjectDescription($objectDescription)
	{
		$this->objectDescription = $objectDescription;
		return true;
	}
	
	/**
	 * Gibt die Objektbeschriebung zurück
	 * @return string
	 */
	public function GetObjectDescription()
	{
		return $this->objectDescription;
	}
	
	/**
	 * Setzt den Standorttyp
	 * @param string $locationType
	 * @return bool
	 */
	public function SetLocationType($locationType)
	{
		$this->locationType = (int)$locationType;
		return true;
	}
	
	/**
	 * Gibt den Standorttyp zurück
	 * @return string		Standorttyp
	 */
	public function GetLocationType()
	{
		return $this->locationType;
	}
	
	/**
	 * Setzt das Land
	 * @param string $country
	 * @return bool
	 */
	public function SetCountry($country)
	{
		$this->country = $country;
		return true;
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
	 * Gibt die zugehörige Customer-Company zurück
	 * @return CCompany
	 */
	public function GetCompany()
	{
		return $this->cCompany;
	}
	
	/**
	 * Setzt die zugehörige Customer-Company
	 * @param DBManager $db
	 * @param CCompany $cCompany
	 * @return bool
	 */
	public function SetCompany(DBManager $db, CCompany $cCompany=null)
	{
		// if the location is used in a ProcessStatusGroup the company couldn't be changed
		if (WorkflowManager::IsLocationInProcessStatusGroup($db, $this))
		{
			if ($this->cCompany==null || $cCompany==null || $this->cCompany->GetPKey()!=$cCompany->GetPKey()) return false;
		}
		
		if ($cCompany==null)
		{
			$this->cCompany=null; 
			return true;
		}
		if ($cCompany->GetPKey()==-1) return false;
		$this->cCompany = $cCompany;
		return true;
	}
	
	/**
	 * Gibt die Anzahl der untergeordneten Läden zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetShopCount(DBManager $db)
	{
		if ($this->pkey==-1) return 0;
		$data = $db->SelectAssoc("SELECT count(pkey) as count FROM ".CShop::TABLE_NAME." WHERE cLocation=".$this->pkey);
		return (int)$data[0]["count"];
	}
	
	/**
	 * Gibt alle untergeordneten Läden zurück
	 * @param DBManager $db
	 * @return CShop[]
	 */
	public function GetShops(DBManager $db)
	{
		if ($this->pkey==-1) return Array();
		$data = $db->SelectAssoc("SELECT * FROM ".CShop::TABLE_NAME." WHERE cLocation=".$this->pkey." ORDER BY name");
		$objects = Array();
		for($a=0; $a<count($data); $a++)
		{
			$object = new CShop($db);
			if ($object->LoadFromArray($data[$a], $db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Ordnet den übergebenen Laden diesem Standort unter
	 * @param DBManager $db
	 * @param CShop $shop
	 * @return bool
	 */
	public function AddShop(DBManager $db, CShop $shop)
	{
		if ($this->pkey==-1) return false;
		// Den Laden diesem Standort zuweisen...
		return $shop->SetLocation($db, $this);
	}
	
	/**
	 * Prüft, ob der übergebene Benutzer berechtigt ist, dieses Objekt zu verarbeiten
	 * @param User $user
	 * @param DBManager $db
	 * @return bool
	 */
	public function HasUserAccess(User $user, DBManager $db)
	{
		$company = $this->GetCompany();
		return ($company==null ? false : $company->HasUserAccess($user, $db));
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
				return $languageManager->GetString('CUSTOMERMANAGER', 'LOCATION_NAME');
			case 'locationType':
				return $languageManager->GetString('CUSTOMERMANAGER', 'LOCATION_TYPE');
			case 'city':
				return $languageManager->GetString('CUSTOMERMANAGER', 'LOCATION_CITY');
			case 'country':
				return $languageManager->GetString('CUSTOMERMANAGER', 'LOCATION_COUNTRY');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}
	
	/**
	 * return default placeholders 
	 * @return Array
	 */
	public function GetPlaceholders(DBManager $db, $language = 'DE', $prefix = 'LADEN')
	{
		$placeHolders["%".$prefix."_PLZ%"] = $this->GetZIP();
		$placeHolders["%".$prefix."_STADT%"] = $this->GetCity();
		$placeHolders["%".$prefix."_STRASSE%"] = $this->GetStreet();
		return $placeHolders;
	}
	
}
?>