<?php
/**
 * Diese Klasse repräsentiert einen Benutzer
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class User extends DBEntry 
{
	/**
	 * Datenbanknamen
	 * @var string
	 */
	const TABLE_NAME = "user";
	const TABLE_NAME_USERGROUP_REL = "rel_user_usergroup";
	
	/**
	 * Variable in der E-Mailadresse gespeichert wird
	 * @var string
	 */
	protected $email="";

	/**
	 * Variable in der Passwort gespeichert wird
	 * @var string
	 */
	protected $pw="";
	
	/**
	 * Password has to be reset
	 * @var boolean 
	 */
	protected $passwordResetRequired = true;

	/**
	 * Anzhal der zurückgewiesenen Anmeldevorgänge
	 * @var int
	 */
	protected $wrongPasswordCounter=0;

	/**
	 * Letzter Login des Benutzers
	 * @var int
	 */
	protected $lastLoginTime=0;

	/**
	 * Address-Daten des Benutzers
	 * @var AddressData
	 */
	protected $addressData = null;

	/**
	 * Urlaubsvertretung
	 * @var User
	 */
	protected $coverUser=-1;	
	
	/**
	 * Listen-Konfiguration des Benutzers
	 * @var String
	 */
	protected $listmanagerConfig="";
	
	/**
	 * Gruppen-Basistyp mit der höchsten Berechtigung dieses Benutzer
	 * @var int 
	 */
	protected $groupBasetype = UM_GROUP_BASETYPE_NONE;
	
	/**
	 * Zugehörige Gruppen
	 * @var type 
	 */
	protected $groups = Array();
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function User(DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("email", "passwort", "passwordResetRequired", "wrongPasswordCounter", "lastLoginTime", "addressData", "coverUser", "listmanagerConfig");
		$dbConfig->rowParam = Array("VARCHAR(255)", "VARCHAR(255)", "INT", "BIGINT", "BIGINT", "BIGINT", "BIGINT", "TEXT");
		$dbConfig->rowIndex = Array("addressData", "coverUser");
		parent::__construct($db, $dbConfig);
		$dbConfig2 = new DBConfig();
		$dbConfig2->tableName = self::TABLE_NAME_USERGROUP_REL;
		$dbConfig2->rowName = Array("user", "usergroup");
		$dbConfig2->rowParam = Array("BIGINT", "BIGINT");
		$dbConfig2->rowIndex = Array("user", "usergroup");
		$db->CreateOrUpdateTable($dbConfig2);
	}

	/**
	 * Prüft die übergebene EMail auf Gültigkeit
	 * @param string $email EMail
	 * @return bool|integer Wenn gültig true sonst
	 *					-1	Email leer
	 *					-2	EMail ungültig
	 */
	static public function CheckFormatEMail($email)
	{
		$email=strtolower(strip_tags(trim($email)));
		if ($email=="") return -1;
		if (filter_var($email, FILTER_VALIDATE_EMAIL)===false) return -2;
		//if (!preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*([a-z0-9]))+(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', $email)) return -2;
		return true;
	}
	
	/**
	 * Prüft, ob die übergebene EMail gültig und noch nicht verwendet ist
	 * @param DBManager $db
	 * @param string $email EMail
	 * @return bool|integer Wenn gültig true sonst
	 *					siehe Funktion CheckFormatEMail
	 *					-10 = Email bereits verwendet
	 */
	static public function CheckEMail($db, $email)
	{
		$email=strtolower(trim($email));
		$retValue=User::CheckFormatEMail( $email );
		if( $retValue!==true )return $retValue;
		if( count($db->SelectAssoc("SELECT pkey FROM ".self::TABLE_NAME." WHERE email='".$email."'",false))!=0 )return -10;
		return true;
	}
	
	/**
	 * Prüft das übergebene Passwort auf Gültigkeit
	 * @param string $pwd Passwort
	 * @return bool|integer Wenn gültig true andernfalls
	 *						-1	PWD leer
	 *						-2	PWD zu kurz
	 *						-3 	Weniger als 2 Zahlen im PWD
	 *						-4 	Weniger als 2 Buchstaben im PWD
	 */
	static public function CheckPWD($pwd)
	{
		$pwd=strip_tags(trim($pwd));
		if( $pwd != "" ){ // Passwort nicht leer
			if( strlen($pwd) >= 5 ){ // Passwort länger als 5 Zeichen
				// Buchstaben und Zahlen zählen
				$check_pswd["number"]=0;
				$check_pswd["string"]=0;
				
				for($i=0; $i<strlen($pwd); $i++){
					if( intval( substr($pwd, $i, 1) ) == true || substr($pwd, $i, 1) == "0"){
						$check_pswd["number"]++;
					}else{
						$check_pswd["string"]++;
					}
				}
				
				if($check_pswd["number"] >= 2){ // Mehr als 2 Zahlen vorhanden
					if($check_pswd["string"] >= 2){ // Mehr als 2 Buchstaben vorhanden
						return true;
					}else{
						return -4; //Zu wenig Buchstaben in $pswd
					}
				}else{
					return -3; //Zu wenig Zahlen in $pswd
				}
			}else{
				return -2; //$pw ist zu kurz (mind. 5 Zeichen)
			}
		}else{
			return -1; // PWD leer
		}
	}
	
	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
 	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int			Im Erfolgsfall true oder 
	 *					-1	EMail-Adresse nicht gesetzt
	 *					-2	Passwort nicht gesetzt
	 *					-3	Benutzer mit dieser EMail-Adresse existiert bereits
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData){
		// EMail-Adresse gesetzt?
		if( $this->email=="" )return -1;
		// Passwort gesetzt?
		if( $this->pw=="" )return -2;
		// Prüfen, ob neuer Benutzer
		if($this->pkey == -1){
			// Prüfen ob Benutzer mit gleicher EMail bereits existiert
			if(count($db->SelectAssoc("SELECT pkey FROM ".self::TABLE_NAME." WHERE email='".strtolower(trim($this->email))."'", false)) != 0){
				// Benutzer mit diseser E-Mail existiert bereits
				return -3;
			}
		}
		// Array mit zu speichernden Daten anlegen
		$rowName[]= "email";
		$rowData[]= $this->email;
		$rowName[]= "passwort";
		$rowData[]= $this->pw;
		$rowName[]= "passwordResetRequired";
		$rowData[]= ($this->passwordResetRequired ? 1 : 0);
		$rowName[]= "wrongPasswordCounter";
		$rowData[]= $this->wrongPasswordCounter;
		$rowName[]= "lastLoginTime";
		$rowData[]= $this->lastLoginTime;
		$rowName[]= "addressData";
		$rowData[]= $this->addressData==null ? -1 : $this->addressData->GetPKey();
		$rowName[]= "coverUser";
		$rowData[]= $this->coverUser;
		$rowName[]= "listmanagerConfig";
		$rowData[]= serialize($this->listmanagerConfig);
		
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
		$this->email = strtolower(trim($data['email']));
		$this->pw = $data['passwort'];
		$this->passwordResetRequired = ($data['passwordResetRequired']==1 ? true : false);
		$this->wrongPasswordCounter = $data['wrongPasswordCounter'];
		$this->lastLoginTime = $data['lastLoginTime'];
		$this->listmanagerConfig = unserialize($data["listmanagerConfig"]);
		if ($data['addressData']!=-1)
		{
			$this->addressData = AddressManager::GetAddressDataByPkey($db, $data['addressData']);
		}
		else
		{
			$this->addressData=null;
		}
		$this->coverUser = $data['coverUser'];
		return true;
	}
	
	/**
	 * Prüft, ob es den Benutzer mit den übergebenen Zugangsdaten gibt und lädt dessen Daten in diese Objekt
	 * @param bool $email Email des Users
	 * @param bool $pw PW des Users
	 * @param DBManager $db
	 * @return bool|int Bei Erfolg true oder
	 *							-1	Login fehlgeschlagen
	 *							-2	Maximale Anzahl der Loginversuche wurde überschritten
	 */
	public function Login($email, $pw, $db)
	{
		$email=strtolower(trim($email));
		if (User::CheckFormatEMail($email)!==true) return -1;
		$data = $db->SelectHtmlAssoc("SELECT pkey, passwort, wrongPasswordCounter FROM ".self::TABLE_NAME." WHERE email='".$email."'", false);
		if(count($data)==1)
		{
			// Maximale Anzahl der Loginversuche überschritten?
			if( (int)$data[0]['wrongPasswordCounter']>=3 )
			{
				return -2;
			}
			// Benutzer mit EMail-Adresse exisitiert, stimmt das Passwort?
			//echo $data[0]['passwort']."==".md5(trim($pw)) ."<br>";
			if( $data[0]['passwort']==md5(trim($pw)) )
			{
				// Passwort ist OK
				$pkey = $data[0]['pkey'];
				$this->Load($pkey, $db);
				$db->Update(self::TABLE_NAME, Array("lastLoginTime", "wrongPasswordCounter"), Array(time(), 0), $pkey);
				return true;
			}
			// Passwort ist falsch
			$pkey = $data[0]['pkey'];
			$db->Update(self::TABLE_NAME, Array("wrongPasswordCounter"), Array( ((int)$data[0]['wrongPasswordCounter'])+1), $pkey);				
			return -1;
		}
		return -1;
	}
	
	/**
	 * Setzt die EMail-Adresse und prüft diese auf Richtigkeit
	 * @param string $email Name des Users
	 * @return bool|integer
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
	 * Gibt zurück, ob das übergebene Passwort das Passwort dieses Benutzer ist
	 * @param string $pw Passwort das geprüft werden soll
	 * @return bool
	 */
	public function IsPWDCorrect($pw)
	{
		$pw=trim($pw);
		//$retValue=User::CheckPWD( $pw );
		//if( $retValue!==true )return false;
		return ($this->pw==md5($pw));
	}
	
	/**
	 * Setzt das Passwort und prüft es auf Richtigkeit
	 * @param string $pw Name des Users
	 * @return bool|integer
	 */
	public function SetPWD($pw)
	{
		$pw=trim($pw);
		$retValue=User::CheckPWD( trim($pw) );
		if( $retValue!==true )return $retValue;
		$this->pw = md5($pw);
		return true;
	}

	/**
	 * Gibt das verschlüsselte Passwort zurück
	 * @return string
	 */
	public function GetPWD()
	{
		return $this->pw;
	}
	
	/**
	 * Return if a password reset is required
	 * @param boolean $passwordResetRequired
	 * @return boolean
	 */
	public function SetPasswordResetRequired($passwordResetRequired)
	{
		if (!is_bool($passwordResetRequired)) return false;
		$this->passwordResetRequired = $passwordResetRequired;
		return true;
	}
	
	/**
	 * Return if a password reset is required
	 * @return boolean
	 */
	public function IsPasswordResetRequired()
	{
		return $this->passwordResetRequired;
	}

	/**
	 * Gibt ein zufallsgeneriertes Passwort zurück
	 * @return String
	 */
	static public function GenerateRandomPWD()
	{
		$pattern[0] = "0123456789"; // Zahlen
		$pattern[1] = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";// Buchstaben
		
		$rnd = rand(0,1); // Randomzahl um zwischen Zahlen und Buchstaben zu wählen
		$count[$rnd]++;	 // Counter an der Stelle ++
		$pw = $pattern[$rnd]{rand(0, strlen($pattern[$rnd])-1 )}; // Passwort generieren

		$counter = 10; // Counter für Anzahl Schleifendurchläufe
		
		
		for($i=1;$i<$counter;$i++){
			$rnd = rand(0,1);
			$count[$rnd]++;
			$pw .= $pattern[$rnd]{rand(0, strlen($pattern[$rnd])-1)};
			
			// Wenn Schleife durchgelaufen (9/9 Durchläufen) wird geprüft 
			// ob PSWD valide (minimal 2 Zahlen, 2 Buchstaben) ist 
			// ansonsten wird counter so lange um 1 erhöht solange 
			// bis die Kriterien (minimal 2 Zahlen, 2 Buchstaben ) erfüllt werden.
			if($i>=9){ 
				if($count[0] < 2 || $count[1] < 2){
					$counter++;
				}
			}
		}
		return $pw;
	}
	
	/**
	 * Gibt den letzten Login-Zeitpunkt zurück
	 * @return int
	 */
	public function GetLastLogin()
	{
		return $this->lastLoginTime;
	}
	
	/**
	 * Setzt die Addressdaten des Benutzers
	 * @param AddressData $addressData AddressData-Objekt
	 * @return bool
	 */
	public function SetAddressData($addressData)
	{
		if( $addressData==null || get_class($addressData)!="AddressData" || $addressData->GetPKey()==-1 )return false;
		$this->addressData=$addressData;
		return true;
	}
	
	/**
	 * Gibt die Adressdaten des Benutzers zurück
	 * @return AddressData
	 */
	public function GetAddressData()
	{
		return $this->addressData;
	}
		
	/**
	 * Setzt die ID der Urlaubsvertretung 
	 * @param int $coverUserID UserID der Urlaubsvertretung (-1=keine)
	 * @return bool
	 */
	public function SetCoverUser($coverUserID)
	{
		if( !is_numeric($coverUserID) || (int)$coverUserID!=$coverUserID )return false;
		$this->coverUser=$coverUserID;
		return true;
	}
	
	/**
	 * Gibt die ID der Urlaubsvertretung zurück
	 * @return int		UserID der Urlaubsvertretung (-1=keine)
	 */
	public function GetCoverUser()
	{
		return $this->coverUser;
	}
	
	/**
	 * Gibt den Gruppen-Basistyp mit der höchsten Berechtigung, zu welcher dieses 
	 * Benutzers angehört, zurück 
	 * @param DBManager $db
	 * @param bool $useCache Load data from local attribute
	 * @return int
	 */
	public function GetGroupBasetype(DBManager $db, $useCache=true)
	{
		// check if the group basetype is allready loaded from DB
		if ($useCache && $this->groupBasetype>UM_GROUP_BASETYPE_NONE) return $this->groupBasetype;
		// load group basetype from DB for this user
		$baseType=UM_GROUP_BASETYPE_NONE;
		$groups=$this->GetGroups($db, $useCache);
		for ($a=0; $a<count($groups); $a++)
		{
			$tempType = $groups[$a]->GetBaseType();
			if ($tempType>$baseType) $baseType=$tempType;
		}
		// remember group basetype for this user
		$this->groupBasetype = $baseType;
		return $baseType;
	}
		
	/**
	 * Gibt die Gruppen-Basistypen, zu welcher dieses Benutzers angehört, zurück 
	 * @param DBManager $db
	 * @return int
	 */
	public function GetGroupBasetypes($db)
	{
		$baseTypes=Array();
		$groups=$this->GetGroups($db);
		for ($a=0; $a<count($groups); $a++)
		{
			$baseTypes[]=$groups[$a]->GetBaseType();
		}
		return $baseTypes;
	}
	
	/**
	 * Gibt zurück, ob dieser Benutzer zum übergebene Basetype  gehört
	 * @param DBManager $db
	 * @param int $groupBasetypeToCheck
	 * @return bool
	 */
	public function BelongToGroupBasetype($db, $groupBasetypeToCheck)
	{
		$groups=$this->GetGroups($db);
		for ($a=0; $a<count($groups); $a++)
		{
			if ($groups[$a]->GetBaseType()==$groupBasetypeToCheck) return true;
		}
		return false;
	}
	
	/**
	 * Gibt die IDs der Gruppen zurück, zu welchen dieser Benutzer angehört
	 * @param DBManager $db
	 * @return array
	 */	
	public function GetGroupIDs($db)
	{
		$groups=Array();
		if ($this->pkey!=-1)
		{
			$groupIDs=$db->SelectAssoc("SELECT usergroup FROM ".self::TABLE_NAME_USERGROUP_REL." WHERE user=".$this->pkey." GROUP BY usergroup");
			for ($a=0; $a<count($groupIDs); $a++)
			{
				$groups[]=$groupIDs[$a]["usergroup"];
			}
		}
		return $groups;
	}
	
	/**
	 * Gibt die IDs der Gruppen zurück, zu welchen dieser Benutzer angehört
	 * @param DBManager $db
	 * @param array $groupIDs Gruppen IDs
	 * @return bool
	 */	
	public function SetGroupIDs($db, $groupIDs)
	{
		if ($this->pkey==-1 || !is_array($groupIDs)) return false;
		$db->Query("DELETE FROM ".self::TABLE_NAME_USERGROUP_REL." WHERE user=".$this->pkey);
		for ($a=0; $a<count($groupIDs); $a++)
		{
			if (!is_numeric($groupIDs[$a]) || ((int)$groupIDs[$a])!=$groupIDs[$a]) continue;
			$db->Insert(self::TABLE_NAME_USERGROUP_REL, Array("user", "usergroup"), Array($this->pkey, (int)$groupIDs[$a]) );
		}
		return true;
	}
	
	/**
	 * Gibt die Gruppen zurück, zu welchen dieser Benutzer angehörig ist
	 * @param DBManager $db
	 * @param bool $useCache Load data from local attribute
	 * @return UserGroup[]
	 */	
	public function GetGroups($db, $useCache=true)
	{
		if ($useCache && count($this->groups)>0) return $this->groups;
		$objects = Array();
		if ($this->pkey!=-1)
		{
			$data = $db->SelectAssoc("SELECT * FROM ".UserGroup::TABLE_NAME." WHERE pkey IN (SELECT usergroup FROM ".self::TABLE_NAME_USERGROUP_REL." WHERE user=".$this->pkey." GROUP BY usergroup)");
			foreach ($data as $value)
			{
				$object = new UserGroup($db);
				if ($object->LoadFromArray($value, $db)===true) $objects[] = $object;
			}
		}
		$this->groups = $objects;
		return $objects;
	}
	
	/**
	 * Fügt den Benutzer zu den übergebenen Gruppen hinzu
	 * @param DBManager $db
	 * @param array $groups Gruppen, zu denen der Benutzer hinzugefügt werden soll
	 * @return bool
	 */	
	public function AddToGroups($db, $groups)
	{
		if ($this->pkey==-1) return false;
		// Prüfen, ob der User bereits in den Gruppen ist
		$groupsToAdd=Array();
		$groupIDs=$db->SelectAssoc("SELECT usergroup FROM ".self::TABLE_NAME_USERGROUP_REL." WHERE user=".$this->pkey." GROUP BY usergroup");
		for ($a=0; $a<count($groups); $a++)
		{
			$found=false;
			for ($b=0; $b<count($groups); $b++)
			{
				if ($groups[$a]->GetPKey()==$groupIDs[$b]["usergroup"])
				{
					$found=true;
					break;
				}
			}
			if (!$found)
			{
				// Prüfen ob Gruppe bereits im Array ist
				for ($b=0; $b<count($groupsToAdd); $b++)
				{
					if ($groupsToAdd[$b]->GetPKey()==$groups[$a]->GetPKey())
					{
						$found=true;
						break;
					}
				}
				// Wenn nicht, Gruppe in Array hinzufügen
				if (!$found) $groupsToAdd[]=$groups[$a];
			}
		}
		// User bei allen Gruppen eintragen, bei denen er noch nicht mitglied ist
		for ($a=0; $a<count($groupsToAdd); $a++)
		{
			$db->Insert(self::TABLE_NAME_USERGROUP_REL, Array("user", "usergroup"), Array($this->pkey, $groupsToAdd[$a]->GetPKey()) );
		}
		return true;
	}

	/**
	 * Entfernt den Benutzer von den übergebenen Gruppen
	 * @param DBManager $db
	 * @param array $groups Gruppen, von denen der Benutzer entfernt werden soll
	 * @return bool
	 */	
	public function RemoveFromGroups($db, $groups)
	{
		if ($this->pkey==-1) return false;
		// Query erzeugen
		$query="DELETE FROM ".self::TABLE_NAME_USERGROUP_REL." WHERE user=".$this->pkey;
		if (count($groups)>0)$query.=" AND (";
		for ($a=0; $a<count($groups); $a++)
		{
			if ($a>0) $query.=" OR ";
			$query.=" usergroup=".$groups[$a]->GetPKey();
		}
		if (count($groups)>0) $query.=" )";
		$db->Query($query);
		return true;
	}

	/**
	 * Entfernt den Benutzer von den übergebenen Gruppen
	 * @param DBManager $db
	 * @param array $groups Gruppen, von denen der Benutzer entfernt werden soll
	 * @return bool
	 */	
	public function RemoveFromAllGroups($db)
	{
		if ($this->pkey==-1) return false;
		// Query erzeugen
		$query="DELETE FROM ".self::TABLE_NAME_USERGROUP_REL." WHERE user=".$this->pkey;
		$db->Query($query);
		return true;
	}
	
	/**
	 * Überprüft ob der User gesperrt ist oder nicht
	 * @return bool
	 */	
	public function IsLocked()
	{
		if($this->wrongPasswordCounter >= 3)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Sperrt, bzw. entsperrt den Benutzer per Post Variable
	 */	
	public function Lock()
	{
		if($_POST["userlock"] == "on")
		{
			$this->wrongPasswordCounter = 3;
		}
		else
		{
			$this->wrongPasswordCounter = 0;
		}
	}
	
	/**
	 * Setzt die List-Manager-Konfiguration
	 * @param array
	 */	
	public function SetListmanagerConfig($newListmanagerConfig)
	{
		$this->listmanagerConfig = $newListmanagerConfig;
	}
	
	/**
	 * Gibt die List-Manager-Konfiguration zurück
	 * @return array
	 */	
	public function GetListmanagerConfig()
	{
		return $this->listmanagerConfig;
	}
	
	/**
	 * Speichert die ListManagerConfig in der Datenbank
	 * @return array
	 */	
	public function StoreListmanagerConfig($db)
	{
		// $_SESSION["listmanager"][$pagename][$ID]["search"]
		if (is_array($this->listmanagerConfig))
		{
			foreach($this->listmanagerConfig as $key => $value)
			{
				foreach($this->listmanagerConfig[$key] as $key2 => $value2)
				{
					if(isset($this->listmanagerConfig[$key][$key2]["search"]))
					{
						unset($this->listmanagerConfig[$key][$key2]["search"]);
					}
				}
			}
		}
		$db->Update( $this->GetTableName(), array("listmanagerConfig"), array(serialize($this->GetListmanagerConfig())), $this->GetPKey() );
	}
	
	/**
	 * Gibt Dr. Vorname Nachnamen (wenn Vorhanden) zurück oder EMail-Adresse
	 * @return string
	 */	
	public function GetUserName()
	{
		$addressDataTemp=$this->GetAddressData();
		if( $addressDataTemp==null )return $this->GetEMail();
		$returnString=trim($addressDataTemp->GetTitle2()." ".trim($addressDataTemp->GetFirstName()." ".$addressDataTemp->GetName()));
		if( $returnString=="" )return $this->GetEMail();
		return $returnString;
	}
	
	
	/**
	 * Gibt Nachnamen (wenn Vorhanden) zurück
	 * @return string
	 */	
	public function GetName()
	{
		$addressDataTemp=$this->GetAddressData();
		if( $addressDataTemp==null )return "";
		return $addressDataTemp->GetName();
	}
	
	/**
	 * Gibt Vorname (wenn Vorhanden) zurück
	 * @return string
	 */	
	public function GetFirstName()
	{
		$addressDataTemp=$this->GetAddressData();
		if( $addressDataTemp==null )return "";
		return $addressDataTemp->GetFirstName();
	}
	
	/**
	 * Gibt Anrede Nachnamen zurück 
	 * @return string
	 */	
	public function GetAnrede(DBManager $db, $phrase = 0, $language='DE')
	{
		$addressDataTemp=$this->GetAddressData();
		if( $addressDataTemp==null )return "-";
		return $addressDataTemp->GetSalutation($db, $phrase, $language);
	}
	
	/**
	 * Gibt die Telefonnummer zurück
	 * @return string
	 */	
	public function GetPhone()
	{
		$addressDataTemp=$this->GetAddressData();
		if( $addressDataTemp==null )return "-";
		$returnString=$addressDataTemp->GetPhone();
		if( trim($returnString)=="" )return "-";
		return $returnString;
	}
	
	/**
	 * Gibt die Facnummer zurück
	 * @return string
	 */	
	public function GetFax()
	{
		$addressDataTemp=$this->GetAddressData();
		if( $addressDataTemp==null )return "-";
		$returnString=$addressDataTemp->GetFax();
		if( trim($returnString)=="" )return "-";
		return $returnString;
	}

    /**
     * Rturn the role of the user
     * @return string
     */
    public function GetRole()
    {
        $addressDataTemp=$this->GetAddressData();
        if( $addressDataTemp==null )return "";
        return $addressDataTemp->GetRole();
    }

	/**
	 * Return the short name
	 * @return string 
	 */
	public function GetShortName()
	{
		$addressDataTemp=$this->GetAddressData();
		if( $addressDataTemp==null )return "-";
		$returnString=$addressDataTemp->GetShortName();
		if( trim($returnString)=="" )return "-";
		return $returnString;
	}
	
	/**
	 * Return the name and email in form "Titel Nachname, Vorname <email>"
	 * @return string 
	 */
	public function GetEmailString()
	{
		$addressDataTemp = $this->GetAddressData();
		if( $addressDataTemp==null )return $this->GetEMail();
		$returnValue = $addressDataTemp->GetEmailString();
		if ($returnValue=="") $returnValue = $this->GetEMail();
		return $returnValue;
	}
	
}
?>