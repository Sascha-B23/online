<?php
/**
 * Diese Klasse repräsentiert einen Laden (z.B. Esprit Men)
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class CShop extends DBEntry implements DependencyFileDescription, AttributeNameMaper
{

	/**
	 * Datenbankname
	 * @var string
	 */
	const TABLE_NAME = "customerShop";

	/**
	 * Dirtflags
	 */
	const DIRTFLAG_NONE = 0;
	const DIRTFLAG_CPERSONRS = 1;
	const DIRTFLAG_CPERSONFMSLEADER = 2;
	const DIRTFLAG_CPERSONCUSTOMER = 4;
	const DIRTFLAG_CPERSONCUSTOMERSUPERVISOR = 8;
	const DIRTFLAG_CPERSONCUSTOMERACCOUNTING = 16;
	const DIRTFLAG_CPERSONACCOUNTSDEPARTMENT = 64;
	const DIRTFLAG_CPERSONAUSHILFE = 128;
	
	/**
	 * Flag that indicates which data have to be written to group members
	 * @var int 
	 */
	private $dirtFlag = self::DIRTFLAG_NONE;
	
	/**
	 * Eindeutige FMS-ID
	 * @var string
	 */
	protected $RSID="";
	
	/**
	 * Name
	 * @var string
	 */
	protected $name="";
	
	/**
	 * Erstes Jahr, seit welchem FMS für den Standort verantwortlich ist
	 * @var integer
	 */
	protected $firstYear = 0;
	
	/**
	 * Eröffnungsdatum des Ladens
	 * @var int 
	 */
	protected $opening = 0;
	
	/**
	 * Interne Ladennummer
	 * @var string
	 */
	protected $internalShopNo="";
	
	/**
	 * Zuständiger FMS-Systembenutzer
	 * @var User
	 */
	protected $cPersonRS=null;
	
	/**
	 * Zuständiger Nebenkostenanalyst FMS
	 * @var User
	 */
	protected $cPersonFmsLeader = null;
	
	/**
	 * Zuständige Buchhaltung FMS
	 * @var User
	 */
	protected $cPersonFmsAccountsdepartment = null;
	
	/**
	 * Zuständiger Kunden-Systembenutzer
	 * @var User
	 */
	protected $cPersonCustomer=null;
	
	/**
	 * Verantwortlicher Kunden-Systembenutzer
	 * @var User
	 */
	protected $cPersonCustomerSupervisor=null;
	
	/**
	 * Verantwortlicher Kunden-Systembenutzer
	 * @var User
	 */
	protected $cPersonCustomerAccounting=null;
	
	/**
	 * Zugehörige Customer-Location
	 * @var CLocation
	 */
	protected $cLocation=null;
	
	/**
	 * Konstruktor
	 * @param object	$db		Datenbankobjekt
	 */
	public function CShop($db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("RSID",			"name",			"firstYear", "opening", "internalShopNo", "cPersonRS", "cPersonFmsLeader",  "cPersonFmsAccountsdepartment", "cPersonCustomer", "cPersonCustomerSupervisor", "cPersonCustomerAccounting", "cLocation");
		$dbConfig->rowParam = Array("VARCHAR(255)", "VARCHAR(255)", "INT",		"BIGINT",	"VARCHAR(255)",	"BIGINT",		"BIGINT",			"BIGINT",						"BIGINT",			"BIGINT",					"BIGINT",					"BIGINT");
		$dbConfig->rowIndex = Array("cLocation");
		parent::__construct($db, $dbConfig);
	}
	
	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @return bool
	 */
	public function IsDeletable(&$db)
	{
		if( $this->GetContractCount($db)>0 )return false;
		return parent::IsDeletable($db);
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
			$otherShops = WorkflowManager::GetDependingShops($db, $this);
			foreach ($otherShops as $otherShop)
			{
				//echo "UPDATE ".$otherShop->GetPKey()." <br />";
				if ($this->dirtFlag & self::DIRTFLAG_CPERSONRS)
				{
					$otherShop->cPersonRS = $this->cPersonRS;
				}
				if ($this->dirtFlag & self::DIRTFLAG_CPERSONFMSLEADER)
				{
					$otherShop->cPersonFmsLeader = $this->cPersonFmsLeader;
				}
				if ($this->dirtFlag & self::DIRTFLAG_CPERSONACCOUNTSDEPARTMENT)
				{
					$otherShop->cPersonFmsAccountsdepartment = $this->cPersonFmsAccountsdepartment;
				}				
				if ($this->dirtFlag & self::DIRTFLAG_CPERSONCUSTOMER)
				{
					$otherShop->cPersonCustomer = $this->cPersonCustomer;
				}
				if ($this->dirtFlag & self::DIRTFLAG_CPERSONCUSTOMERSUPERVISOR)
				{
					$otherShop->cPersonCustomerSupervisor = $this->cPersonCustomerSupervisor;
				}
				if ($this->dirtFlag & self::DIRTFLAG_CPERSONCUSTOMERACCOUNTING)
				{
					$otherShop->cPersonCustomerAccounting = $this->cPersonCustomerAccounting;
				}				
				// store changes to child
				$otherShop->Store($db);
			}
			// reset dirt flag
			$this->dirtFlag = self::DIRTFLAG_NONE;
		}
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int				Im Erfolgsfall true oder 
	 *								-1	FMS-ID nicht gesetzt
	 *								-2	Name nicht gesetzt
	 *								-3	Zugehöriger Standort nicht gesetzt
	 *								-4	Shop mit dieser FMS-ID existiert bereits
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData)
	{
		// EMail-Adresse gesetzt?
		if( $this->RSID=="" )return -1;
		if( $this->name=="" )return -2;
		if( $this->cLocation==null || $this->cLocation->GetPKey()==-1 )return -3;
		// Prüfen, ob neuer Shop
		if($this->pkey == -1){
			// Prüfen ob Shop mit gleichem Namen bereits existiert
			if( count($db->SelectAssoc("SELECT pkey FROM ".self::TABLE_NAME." WHERE RSID='".$this->RSID."'", false)) != 0){
				// Shop mit diseser FMS-ID existiert bereits
				return -4;
			}
		}
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "RSID";
		$rowData[]= $this->RSID;
		$rowName[]= "name";
		$rowData[]= $this->name;
		$rowName[]= "firstYear";
		$rowData[]= $this->firstYear;
		$rowName[]= "opening";
		$rowData[]= $this->opening;
		$rowName[]= "internalShopNo";
		$rowData[]= $this->internalShopNo;
		$rowName[]= "cPersonRS";
		$rowData[]= $this->cPersonRS==null ? -1 : $this->cPersonRS->GetPKey();
		$rowName[]= "cPersonFmsLeader";
		$rowData[]= $this->cPersonFmsLeader==null ? -1 : $this->cPersonFmsLeader->GetPKey();
		$rowName[]= "cPersonFmsAccountsdepartment";
		$rowData[]= $this->cPersonFmsAccountsdepartment==null ? -1 : $this->cPersonFmsAccountsdepartment->GetPKey();
		$rowName[]= "cPersonCustomer";
		$rowData[]= $this->cPersonCustomer==null ? -1 : $this->cPersonCustomer->GetPKey();
		$rowName[]= "cPersonCustomerSupervisor";
		$rowData[]= $this->cPersonCustomerSupervisor==null ? -1 : $this->cPersonCustomerSupervisor->GetPKey();
		$rowName[]= "cPersonCustomerAccounting";
		$rowData[]= $this->cPersonCustomerAccounting==null ? -1 : $this->cPersonCustomerAccounting->GetPKey();
		$rowName[]= "cLocation";
		$rowData[]= $this->cLocation==null ? -1 : $this->cLocation->GetPKey();
		return true;
	}
	
	/*
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array  	$data		Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool
	 */
	protected function BuildFromDBArray(&$db, $data)
	{
		// Daten aus Array in Attribute kopieren
		$this->RSID = $data['RSID'];
		$this->name = $data['name'];
		$this->firstYear = $data['firstYear'];
		$this->opening = $data['opening'];
		$this->internalShopNo = $data['internalShopNo'];
		if ($data['cPersonRS']!=-1)
		{
			$this->cPersonRS = UserManager::GetUserByPkey($db, $data['cPersonRS']);
		}
		else
		{
			$this->cPersonRS=null;
		}
		// Nebenkostenanalyst FMS
		if ($data['cPersonFmsLeader']!=-1)
		{
			$this->cPersonFmsLeader = UserManager::GetUserByPkey($db, $data['cPersonFmsLeader']);
		}
		else
		{
			$this->cPersonFmsLeader=null;
		}
		// Buchhaltung FMS
		if ($data['cPersonFmsAccountsdepartment']!=-1)
		{
			$this->cPersonFmsAccountsdepartment = UserManager::GetUserByPkey($db, $data['cPersonFmsAccountsdepartment']);
		}
		else
		{
			$this->cPersonFmsAccountsdepartment=null;
		}
		
		if( $data['cPersonCustomer']!=-1 )
		{
			$this->cPersonCustomer = UserManager::GetUserByPkey($db, $data['cPersonCustomer']);
		}
		else
		{
			$this->cPersonCustomer=null;
		}
		if ($data['cPersonCustomerSupervisor']!=-1)
		{
			$this->cPersonCustomerSupervisor = UserManager::GetUserByPkey($db, $data['cPersonCustomerSupervisor']);
		}
		else
		{
			$this->cPersonCustomerSupervisor=null;
		}
		if ($data['cPersonCustomerAccounting']!=-1)
		{
			$this->cPersonCustomerAccounting = UserManager::GetUserByPkey($db, $data['cPersonCustomerAccounting']);
		}
		else
		{
			$this->cPersonCustomerAccounting=null;
		}
		if ($data['cLocation']!=-1)
		{
			$this->cLocation = CustomerManager::GetLocationByPkey($db, $data['cLocation']);
		}
		else
		{
			$this->cLocation=null;
		}
		return true;
	}

	/**
	 * Return Description
	 */
	public function GetDependencyFileDescription()
	{
		$location = $this->GetLocation();
		return ($location==null ? "?" : $location->GetDependencyFileDescription()).DependencyFileDescription::SEPERATOR.$this->GetName()." (".$this->GetRSID().")";
	}
	
	/**
	 * Setzt die FMS-ID
	 * @param string $RSID
	 * @return bool
	 */
	public function SetRSID($RSID)
	{
		$this->RSID=$RSID;
		return true;
	}
	
	/**
	 * Gibt die FMS-ID zurück
	 * @return string
	 */
	public function GetRSID()
	{
		return $this->RSID;
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
	 * Setzt das Jahr, ab welchem FMS die NKA übernommen hat
	 * @param int $firstYear
	 * @return bool
	 */
	public function SetFirstYear($firstYear)
	{
		$this->firstYear=(int)$firstYear;
		return true;
	}
	
	/**
	 * Gibt das Jahr, ab welchem FMS die NKA übernommen hat, zurück
	 * @return int
	 */
	public function GetFirstYear()
	{
		return $this->firstYear;
	}
	
	/**
	 * Setzt das Eröffnungsdatum des Ladens 
	 * @param int $opening
	 * @return bool
	 */
	public function SetOpening($opening)
	{
		$this->opening=(int)$opening;
		return true;
	}
	
	/**
	 * Gibt das Eröffnungsdatum des Ladens zurück
	 * @return int
	 */
	public function GetOpening()
	{
		return $this->opening;
	}
	
	/**
	 * Setzt die interne Ladennummer
	 * @param string $internalShopNo
	 * @return bool
	 */
	public function SetInternalShopNo($internalShopNo){
		$this->internalShopNo=$internalShopNo;
		return true;
	}
	
	/**
	 * Gibt die interne Ladennummer zurück
	 * @return string
	 */
	public function GetInternalShopNo()
	{
		return $this->internalShopNo;
	}
	
	/**
	 * Gibt den zugehörigen FMS-System-Benutzer zurück
	 * @return User
	 */
	public function GetCPersonRS()
	{
		return $this->cPersonRS;
	}
	
	/**
	 * Return if the responsible user can be changed in consideration of ProcessStatusGroups
	 * @param DBManager $db
	 * @return boolean
	 */
	public function IsAttributeAllowedToChange(DBManager $db)
	{
        // Update: The attributes can be changed even if they are in multiple groups
        return true;
	}
	
	/**
	 * Setzt den zugehörigen FMS-System-Benutzer 
	 * @param DBManager $db
	 * @param User $cPersonRS
	 * @return bool
	 */
	public function SetCPersonRS(DBManager $db, User $cPersonRS)
	{
		if ($cPersonRS->GetPKey()==-1) return false;
		if ($this->cPersonRS!=null && $this->cPersonRS->GetPKey()==$cPersonRS->GetPKey()) return true;
		if (!$this->IsAttributeAllowedToChange($db)) return false;
		$this->dirtFlag |= self::DIRTFLAG_CPERSONRS;
		$this->cPersonRS = $cPersonRS;
		return true;
	}
	
	/**
	 * Gibt den zugehörigen FMS-Nebenkostenanalyst zurück
	 * @return User
	 */
	public function GetCPersonFmsLeader()
	{
		return $this->cPersonFmsLeader;
	}
	
	/**
	 * Setzt den zugehörigen FMS-Nebenkostenanalyst
	 * @param User $cPersonFmsLeader
	 * @return bool
	 */
	public function SetCPersonFmsLeader(DBManager $db, User $cPersonFmsLeader)
	{
		if ($cPersonFmsLeader->GetPKey()==-1) return false;
		if ($this->cPersonFmsLeader!=null && $this->cPersonFmsLeader->GetPKey()==$cPersonFmsLeader->GetPKey()) return true;
		if (!$this->IsAttributeAllowedToChange($db)) return false;
		$this->dirtFlag |= self::DIRTFLAG_CPERSONFMSLEADER;
		$this->cPersonFmsLeader = $cPersonFmsLeader;
		return true;
	}

	/**
	 * Gibt den zugehörigen FMS-Accountdepartment zurück
	 * @return User
	 */
	public function GetCPersonFmsAccountsdepartment()
	{
		return $this->cPersonFmsAccountsdepartment;
	}
	
	/**
	 * Setzt den zugehörigen FMS-Accountdepartment
	 * @param User $cPersonFmsAccountsdepartment
	 * @return bool
	 */
	public function SetCPersonFmsAccountsdepartment(DBManager $db, User $cPersonFmsAccountsdepartment)
	{
		if ($cPersonFmsAccountsdepartment->GetPKey()==-1) return false;
		if ($this->cPersonFmsAccountsdepartment!=null && $this->cPersonFmsAccountsdepartment->GetPKey()==$cPersonFmsAccountsdepartment->GetPKey()) return true;
		if (!$this->IsAttributeAllowedToChange($db)) return false;
		$this->dirtFlag |= self::DIRTFLAG_CPERSONACCOUNTSDEPARTMENT;
		$this->cPersonFmsAccountsdepartment = $cPersonFmsAccountsdepartment;
		return true;
	}
	
	/**
	 * Gibt den zugehörigen Kunden-System-Benutzer zurück
	 * @return User
	 */
	public function GetCPersonCustomer()
	{
		return $this->cPersonCustomer;
	}
	
	/**
	 * Setzt den zugehörigen Kunden-System-Benutzer 
	 * @param User $cPersonCustomer
	 * @return bool
	 */
	public function SetCPersonCustomer(DBManager $db, User $cPersonCustomer)
	{
		if ($cPersonCustomer->GetPKey()==-1) return false;
		if ($this->cPersonCustomer!=null && $this->cPersonCustomer->GetPKey()==$cPersonCustomer->GetPKey()) return true;
		if (!$this->IsAttributeAllowedToChange($db)) return false;
		$this->dirtFlag |= self::DIRTFLAG_CPERSONCUSTOMER;
		$this->cPersonCustomer = $cPersonCustomer;
		return true;
	}
		
	/**
	 * Gibt den zugehörigen Kunden-System-Benutzer zurück (Supervisor)
	 * @return User
	 */
	public function GetCPersonCustomerSupervisor()
	{
		return $this->cPersonCustomerSupervisor;
	}
	
	/**
	 * Setzt den zugehörigen Kunden-System-Benutzer (Supervisor)
	 * @param User $cPersonCustomerSupervisor
	 * @return bool
	 */
	public function SetCPersonCustomerSupervisor(DBManager $db, User $cPersonCustomerSupervisor)
	{
		if ($cPersonCustomerSupervisor->GetPKey()==-1) return false;
		if ($this->cPersonCustomerSupervisor!=null && $this->cPersonCustomerSupervisor->GetPKey()==$cPersonCustomerSupervisor->GetPKey()) return true;
		if (!$this->IsAttributeAllowedToChange($db)) return false;
		$this->dirtFlag |= self::DIRTFLAG_CPERSONCUSTOMERSUPERVISOR;
		$this->cPersonCustomerSupervisor=$cPersonCustomerSupervisor;
		return true;
	}
	
	/**
	 * Gibt den zugehörigen Kunden-System-Benutzer der Buchhaltung zurück
	 * @return User
	 */
	public function GetCPersonCustomerAccounting()
	{
		return $this->cPersonCustomerAccounting;
	}
	
	/**
	 * Setzt den zugehörigen Kunden-System-Benutzer der Buchhaltung 
	 * @param User $cPersonCustomer
	 * @return bool
	 */
	public function SetCPersonCustomerAccounting(DBManager $db, User $cPersonCustomerAccounting=null)
	{
		if ($cPersonCustomerAccounting!=null && $cPersonCustomerAccounting->GetPKey()==-1) return false;
		if ($this->cPersonCustomerAccounting==null && $cPersonCustomerAccounting==null) return true;
		if ($this->cPersonCustomerAccounting!=null && $cPersonCustomerAccounting!=null && $this->cPersonCustomerAccounting->GetPKey()==$cPersonCustomerAccounting->GetPKey()) return true;
		if (!$this->IsAttributeAllowedToChange($db)) return false;
		$this->dirtFlag |= self::DIRTFLAG_CPERSONCUSTOMERACCOUNTING;
		$this->cPersonCustomerAccounting = $cPersonCustomerAccounting;
		return true;
	}
	
	/**
	 * Gibt die zugehörige Customer-Location zurück
	 * @return CLocation
 	 */
	public function GetLocation()
	{
		return $this->cLocation;
	}
	
	/**
	 * Setzt die zugehörige Customer-Location
	 * @param DBManager $db
	 * @param CLocation $cLocation
	 * @return bool
	 */
	public function SetLocation(DBManager $db, CLocation $cLocation=null)
	{
		// if the shop is used in a ProcessStatusGroup the company couldn't be changed
		if (WorkflowManager::GetProcessStatusGroupCountOfShop($db, $this)>0)
		{
			if ($this->cLocation==null || $cLocation==null) return false;
			$company1 = $this->cLocation->GetCompany();
			$company2 = $cLocation->GetCompany();
			if ($company1==null || $company2==null || $company1->GetPKey()!=$company2->GetPKey()) return false;
		}
		
		if ($cLocation==null)
		{
			$this->cLocation = null; 
			return true;
		}
		if ($cLocation->GetPKey()==-1) return false;
		$this->cLocation = $cLocation;
		return true;
	}
	
	/**
	 * Gibt die Anzahl der Contracts zurück
	 * @param DBManager $db
	 * @return int
	 */
	public function GetContractCount(DBManager $db)
	{
		if ($this->pkey==-1) return 0;
		$data = $db->SelectAssoc("SELECT count(pkey) as count FROM ".Contract::TABLE_NAME." WHERE cShop=".$this->pkey);
		return (int)$data[0]["count"];
	}
	
	/**
	 * Gibt alle Contracts zurück
	 * @param DBManager $db
	 * @return Contract[]
	 */
	public function GetContracts(DBManager $db)
	{
		if ($this->pkey==-1) return Array();
		$data = $db->SelectAssoc("SELECT * FROM ".Contract::TABLE_NAME." WHERE cShop=".$this->pkey);
		$objects = Array();
		for($a=0; $a<count($data); $a++)
		{
			$object=new Contract($db);
			if ($object->LoadFromArray($data[$a], $db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Ordnet dem Contract diesen CShop zu
	 * @param DBManager $db
	 * @param Contract $contract
	 * @return bool
	 */
	public function AddContract(DBManager $db, Contract $contract)
	{
		if ($this->pkey==-1) return false;
		// Dem Contract diesen CShop zuweisen...
		return $contract->SetShop($db, $this);
	}
	
	/**
	 * Gibt die Abrechnungsjahr-Objekte für das übergebene Jahr zurück
	 * @param DBManager $db
	 * @param int $jahr
	 * @return AbrechnungsJahr[]
	 */
	public function GetAbrechnungsjahr(DBManager $db, $jahr)
	{
		$returnValue=Array();
		$contracs=$this->GetContracts($db);
		for( $a=0; $a<count($contracs); $a++)
		{
			$abrechnungsjahre = $contracs[$a]->GetAbrechnungsJahre($db);
			for ($b=0; $b<count($abrechnungsjahre); $b++)
			{
				if ($abrechnungsjahre[$b]->GetJahr()==(int)$jahr) $returnValue[] = $abrechnungsjahre[$b];
			}
		}
		return $returnValue;
	}
	
	/**
	 * Prüft, ob der übergebene Benutzer berechtigt ist, dieses Objekt zu verarbeiten
	 * @param User $user
	 * @param DBManager $db
	 * @return bool
	 */
	public function HasUserAccess(User $user, DBManager $db)
	{
		$location = $this->GetLocation();
		return ($location==null ? false : $location->HasUserAccess($user, $db) );
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
			case 'name':
				return $languageManager->GetString('CUSTOMERMANAGER', 'SHOP_NAME');
			case 'RSID':
				return $languageManager->GetString('CUSTOMERMANAGER', 'SHOP_FMS_ID');
			case 'internalShopNo':
				return $languageManager->GetString('CUSTOMERMANAGER', 'SHOP_CUSTOMER_ID');
		}
		return "Unknown attribute name '".$attributeName."' in class '".__CLASS__."'";
	}
	
	/**
	 * return default placeholders 
	 * @return Array
	 */
	public function GetPlaceholders(DBManager $db, $language = 'DE', $prefix = 'LADEN')
	{
		$placeHolders["%".$prefix."_NAME%"] = $this->GetName();
		$placeHolders["%".$prefix."_ID%"] = $this->GetInternalShopNo();
		return $placeHolders;
	}
	
}
?>