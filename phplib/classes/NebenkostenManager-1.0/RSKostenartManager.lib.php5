<?php
include('TextModuleManager.lib.php5');
include('Contract.lib.php5');
include('AbrechnungsJahr.lib.php5');
include('Teilabrechnung.lib.php5');
include('Teilabrechnungsposition.lib.php5');
include('RSKostenart.lib.php5');
include('Widerspruch.lib.php5');
include('AbbruchProtokoll.lib.php5');
include('RueckweisungsBegruendung.lib.php5');
include('RueckweisungsBegruendungProzess.lib.php5');
include('TextModule.lib.php5');
include('Widerspruchspunkt.lib.php5');
include('Kuerzungsbetrag.lib.php5');
include('Antwortschreiben.lib.php5');
include('Termin.lib.php5');
include('EMail.lib.php5');
include('TerminMail.lib.php5');
include('InformationMail.lib.php5');


define("NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_KEINEM", 0);
define("NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_EIGENTUEMER", 1);
define("NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_VERWALTER", 2);
define("NKM_TEILABRECHNUNG_SCHRIFTVERKEHRMIT_ANWALT", 3);

define("NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE", 0);
define("NKM_TEILABRECHNUNGSPOSITION_EINHEIT_QUATRATMETER", 1);
define("NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KUBIKMETER", 2);
define("NKM_TEILABRECHNUNGSPOSITION_EINHEIT_GEWICHTETEQUATRATMETER", 3);
define("NKM_TEILABRECHNUNGSPOSITION_EINHEIT_QUATRATMETERTAGE", 4);
define("NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KWH", 5);

define("DOCUMENT_TYPE_UNBEKANNT", 0);
define("DOCUMENT_TYPE_PDF", 1);
define("DOCUMENT_TYPE_RTF", 2);
define("DOCUMENT_TYPE_ANSCHREIBEN", 4);
define("DOCUMENT_TYPE_ANHANG", 8);
define("DOCUMENT_TYPE_RECHNUNG", 16);


// Zuordung der Dateiendungen zu den entsprechenden Dateitypen
$NKM_TEILABRECHNUNGSPOSITION_EINHEIT = Array( 	NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE 					=> Array("short" => "", "long" => ""),
												NKM_TEILABRECHNUNGSPOSITION_EINHEIT_QUATRATMETER 			=> Array("short" => "m²", "long" => "Quadratmeter"),
												NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KUBIKMETER 				=> Array("short" => "km³", "long" => "Kubikmeter"),
												NKM_TEILABRECHNUNGSPOSITION_EINHEIT_GEWICHTETEQUATRATMETER 	=> Array("short" => "m²", "long" => "Gew. Quadratmeter"),
												NKM_TEILABRECHNUNGSPOSITION_EINHEIT_QUATRATMETERTAGE 		=> Array("short" => "m²/Tage", "long" => "Quadratmeter/Tage"),
												NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KWH 					=> Array("short" => "kw/h", "long" => "Kilowattstunde")
											);


/***************************************************************************
 * Diese Klasse verwaltet die FMS-Kostenarten
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class RSKostenartManager extends SearchDBEntry {

	/***************************************************************************
	 * Datenbankobjekt
	 * @var MySQLManager
	 * @access protected
	 **************************************************************************/
	protected $db=null;

	/***************************************************************************
	 * Dummyobjekte 
	 * @access protected
	 **************************************************************************/
	protected $groupDummy=null;
	protected $companyDummy=null;
	protected $locationDummy=null;
	protected $shopDummy=null;
	protected $contractDummy=null;
	protected $abrechnungsJahrDummy=null;
	protected $teilabrechnungDummy=null;
	protected $teilabrechnungspositionDummy=null;
	protected $widerspruchDummy=null;
	protected $widerspruchspunktDummy=null;
	protected $abbruchProtokollDummy=null;
	protected $rueckweisungsBegruendung=null;
	protected $rueckweisungsBegruendungProcess=null;
	protected $termin=null;
	protected $terminMail = null;
	protected $antwortschreiben=null;

	/**
	 * Array map with all teilabrechnung instances loaded with fn. GetTeilabrechnungByPkey
	 * @var Array
	 */
	protected static $teilabrechnungCache = Array();
	
	/**
	 * Array map with all AbrechnungsJahr instances loaded with fn. GetAbrechnungsJahrByPkey
	 * @var Array
	 */
	protected static $abrechnungsjahrCache = Array();
	
	/**
	 * Array map with all Contract instances loaded with fn. GetContractByPkey
	 * @var Array
	 */
	protected static $contractCache = Array();
	
	/**
	 * Array map with all RSKostenart instances loaded with fn. GetKostenartByPkey
	 * @var Array
	 */
	protected static $kostenartCache = Array();
	
	/***************************************************************************
	* Konstruktor
	* @param object	$db	Datenbank-Objekt
	* @access public
	***************************************************************************/
	public function __construct($db){
		$this->db=$db;
		$this->groupDummy=new CGroup($this->db);
		$this->companyDummy=new CCompany($this->db);
		$this->locationDummy=new CLocation($this->db);
		$this->shopDummy=new CShop($this->db);
		$this->contractDummy=new Contract($this->db);
		$this->abrechnungsJahrDummy=new AbrechnungsJahr($this->db);
		$this->teilabrechnungDummy=new Teilabrechnung($this->db);
		$this->teilabrechnungspositionDummy=new Teilabrechnungsposition($this->db);
		$this->widerspruchDummy=new Widerspruch($this->db);
		$this->widerspruchspunktDummy=new Widerspruchspunkt($this->db);
		$this->abbruchProtokollDummy=new AbbruchProtokoll($this->db);
		$this->rueckweisungsBegruendung=new RueckweisungsBegruendung($this->db);
		$this->rueckweisungsBegruendungProcess=new RueckweisungsBegruendungProzess($this->db);

		$this->termin=new Termin($this->db);
		$this->terminMail = new TerminMail($this->db);
		$this->antwortschreiben=new Antwortschreiben($this->db);
	}
	
	/***************************************************************************
	* Gibt die Anzahl der Kostenarten zurück
	* @return int		Anzahl
	* @access public
	***************************************************************************/
	public function GetKostenartenCount($searchString=""){
		$object=new RSKostenart($this->db);
		return $this->GetDBEntryCount($searchString, RSKostenart::TABLE_NAME, $object->GetTableConfig()->rowName);
	}
	
	/**
	 * Gibt die Users entsprechend den übergebenen Parametern zurück
	 * @param string	$searchString		Suchstring
	 * @param string	$orderBy			Spaltenname nach dem sortiert werden soll
	 * @param string	$orderDirection		Sortierrichtung (ASC oder DESC)
	 * @param string	$currentPage		Aktuelle Seite
	 * @param string	$numEntrysPerPage	Anzhal der Einträge je Seite
	 * @return RSKostenart[]
	 */
	public function GetKostenarten($searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20){
		$object=new RSKostenart($this->db);
		$dbEntrys=$this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, RSKostenart::TABLE_NAME, $object->GetTableConfig()->rowName);
		// Objekte erzeugen
		$objects=Array();
		for( $a=0; $a<count($dbEntrys); $a++)
		{
			$object=new RSKostenart($this->db);
			if ($object->LoadFromArray($dbEntrys[$a], $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Return RSKostenart instance by pkey
	 * @param DBManager $db
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return RSKostenart
	 */
	static public function GetKostenartByPkey(DBManager $db, $pkey, $useCache=true)
	{
		// look up cache
		if ($useCache && isset(self::$kostenartCache[(int)$pkey])) return self::$kostenartCache[(int)$pkey];
		// load from DB
		$object = new RSKostenart($db);
		if ($object->Load((int)$pkey, $db)!==true) $object = null;
		if ($useCache) self::$kostenartCache[(int)$pkey] = $object;
		return $object;
	}
	
	/**
	 * PKeys der FMS-Systemkostenarten
	 * @var enum
	 */
	const RS_KOSTENART_ALLGEMEINSTROM = 26;
	const RS_KOSTENART_ENTSORGUNG = 4;
	const RS_KOSTENART_HEIZKOSTEN = 16;
	const RS_KOSTENART_KOSTENART_ZUR_ZUORDNUNG = 11;
	const RS_KOSTENART_LUEFTUNG_UND_KLIMATISIERUNG = 22;
	const RS_KOSTENART_REINIGUNG_UND_PFLEGE = 3;
	const RS_KOSTENART_SONSTIGES = 25;
	const RS_KOSTENART_VERSICHERUNGEN = 5;
	const RS_KOSTENART_VERWALTUNG_UND_MANAGEMENT = 14;
	const RS_KOSTENART_WARTUNG_INSTANDHALTUNG_INSTANDSETZUNG = 8;
	const RS_KOSTENART_OEFFENTLICHE_ABGABEN = 2;
	
	/**
	 * Gibt ein Array mit allen System-FMS-Kostenarten zurück
	 * @return array 
	 */
	static public function GetSystemKostenarten()
	{
		return Array(	Array('ID' => self::RS_KOSTENART_ALLGEMEINSTROM),
						Array('ID' => self::RS_KOSTENART_ENTSORGUNG),
						Array('ID' => self::RS_KOSTENART_HEIZKOSTEN),
						Array('ID' => self::RS_KOSTENART_KOSTENART_ZUR_ZUORDNUNG),
						Array('ID' => self::RS_KOSTENART_LUEFTUNG_UND_KLIMATISIERUNG),
						Array('ID' => self::RS_KOSTENART_REINIGUNG_UND_PFLEGE),
						Array('ID' => self::RS_KOSTENART_SONSTIGES),
						Array('ID' => self::RS_KOSTENART_VERSICHERUNGEN),
						Array('ID' => self::RS_KOSTENART_VERWALTUNG_UND_MANAGEMENT),
						Array('ID' => self::RS_KOSTENART_WARTUNG_INSTANDHALTUNG_INSTANDSETZUNG),
						Array('ID' => self::RS_KOSTENART_OEFFENTLICHE_ABGABEN)
					);
	}
	
	/**
	 * Gibt zurück, ob die übergebenen Kostenart eine Systemkostenart ist
	 * @return array 
	 */
	static public function IsSystemKostenart($rsKostenart)
	{
		$kostenarten = self::GetSystemKostenarten();
		for ($a=0; $a<count($kostenarten); $a++)
		{
			if ($kostenarten[$a]['ID']==$rsKostenart )return true;
		}
		return false;
	}
	
	/***************************************************************************
	* Gibt die Anzahl der Verträge zurück
	* @param object	$user	User-Objekt
	* @return int		Anzahl
	* @access public
	***************************************************************************/
	public function GetContractCount($user, $searchString=""){
		if( $user==null || get_class($user)!="User" )return 0;
		// Berechtigung 
		$groupQuery="";
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP){
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		
		$joinClause="LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
							Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		// Für Suche
		$rowsToUseS= Array(	Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
					);
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		
		$object=new Contract($this->db);
		return $this->GetDBEntryCount($searchString, Contract::TABLE_NAME, $allRowNames, $groupQuery, $joinClause, "", $searchRowNames);
	}
	
	/**
	 * Return contracts
	 * @param User $user
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @return Contract[]
	 */
	public function GetContracts($user, $searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20){
		if( $user==null || get_class($user)!="User" )return Array();
		// Berechtigung 
		$groupQuery="";
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP){
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		
		$joinClause="LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
							Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		// Für Suche
		$rowsToUseS= Array(	Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
					);
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		
		$object=new Contract($this->db);
		$dbEntrys=$this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, Contract::TABLE_NAME, $allRowNames, $groupQuery, $joinClause, "", $searchRowNames);
		// Objekte erzeugen
		$objects=Array();
		for( $a=0; $a<count($dbEntrys); $a++)
		{
			$object = new Contract($this->db);
			if ($object->LoadFromArray($dbEntrys[$a], $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Gibt den Vertrag mit der angeforderten ID zurück
	 * @param User $user
	 * @param int $contractID ID des Vertrags der angefordert werden soll
	 * @return Contract
	 */
	public function GetContractByID($user, $contractID)
	{
		if ($user==null || get_class($user)!="User" || !is_numeric($contractID) || ((int)$contractID)!=$contractID) return null;
		// TODO: Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden


		$object=new Contract($this->db);
		if( !$object->Load((int)$contractID, $this->db) )return null;
		return $object;
	}
	
	/**
	 * Return Contract instance by pkey
	 * @param DBManager $db
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return Contract
	 */
	static public function GetContractByPkey(DBManager $db, $pkey, $useCache=true)
	{
		// look up cache
		if ($useCache && isset(self::$contractCache[(int)$pkey])) return self::$contractCache[(int)$pkey];
		// load from DB
		$object = new Contract($db);
		if ($object->Load((int)$pkey, $db)!==true) $object = null;
		if ($useCache) self::$contractCache[(int)$pkey] = $object;
		return $object;
	}
	
	/***************************************************************************
	* Gibt die Anzahl der Teilabrechnungen zurück
	* @param object	$user	User-Objekt
	* @return int		Anzahl
	* @access public
	***************************************************************************/
	public function GetTeilabrechnungenCount($user, $searchString=""){
		if( $user==null || get_class($user)!="User" )return 0;
		// Berechtigung 
		$groupQuery="";
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP){
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		
		$joinClause="LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".Teilabrechnung::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => Teilabrechnung::TABLE_NAME, "tableRowNames" => $this->teilabrechnungDummy->GetTableConfig()->rowName),
							Array("tableName" => AbrechnungsJahr::TABLE_NAME, "tableRowNames" => $this->abrechnungsJahrDummy->GetTableConfig()->rowName),
							Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
							Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		// Für Suche
		$rowsToUseS= Array(	Array("tableName" => Teilabrechnung::TABLE_NAME, "tableRowNames" => $this->teilabrechnungDummy->GetTableConfig()->rowName),
							Array("tableName" => AbrechnungsJahr::TABLE_NAME, "tableRowNames" => $this->abrechnungsJahrDummy->GetTableConfig()->rowName),
							Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
					);
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		
		$object=new Teilabrechnung($this->db);
		return $this->GetDBEntryCount($searchString, Teilabrechnung::TABLE_NAME, $allRowNames, $groupQuery, $joinClause, "", $searchRowNames);
	}
	
	/***************************************************************************
	* Gibt die Teilabrechnungen zurück
	* @param object	$user	User-Objekt
	* @return int		Anzahl
	* @access public
	***************************************************************************/
	public function GetTeilabrechnungen($user, $searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20){
		if( $user==null || get_class($user)!="User" )return Array();
		// Berechtigung 
		$groupQuery="";
		if( $user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP){
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++){
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}
		
		$joinClause="LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".Teilabrechnung::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => Teilabrechnung::TABLE_NAME, "tableRowNames" => $this->teilabrechnungDummy->GetTableConfig()->rowName),
							Array("tableName" => AbrechnungsJahr::TABLE_NAME, "tableRowNames" => $this->abrechnungsJahrDummy->GetTableConfig()->rowName),
							Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
							Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
					);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		// Für Suche
		$rowsToUseS= Array(	Array("tableName" => Teilabrechnung::TABLE_NAME, "tableRowNames" => $this->teilabrechnungDummy->GetTableConfig()->rowName),
							Array("tableName" => AbrechnungsJahr::TABLE_NAME, "tableRowNames" => $this->abrechnungsJahrDummy->GetTableConfig()->rowName),
							Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
					);
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		
		$object=new Teilabrechnung($this->db);
		$dbEntrys=$this->GetDBEntry($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, Teilabrechnung::TABLE_NAME, $allRowNames, $groupQuery, $joinClause, "", $searchRowNames);
		// Objekte erzeugen
		$objects=Array();
		for ($a=0; $a<count($dbEntrys); $a++)
		{
			$object = new Teilabrechnung($this->db);
			if ($object->Load($dbEntrys[$a], $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Gibt die Teilabrechnung mit der angeforderten ID zurück
	 * @param User $user
	 * @param int $teilabrechnungID
	 * @return Teilabrechnung
	 */
	public function GetTeilabrechnungByID(User $user, $teilabrechnungID)
	{
		if( !is_numeric($teilabrechnungID) || ((int)$teilabrechnungID)!=$teilabrechnungID )return null;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$object=new Teilabrechnung($this->db);
		if( !$object->Load((int)$teilabrechnungID, $this->db) )return null;
		return $object;
	}
	
	/**
	 * Return Teilabrechnung instance by pkey
	 * @param DBManager $db
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return Teilabrechnung
	 */
	static public function GetTeilabrechnungByPkey(DBManager $db, $pkey, $useCache=true)
	{
		// look up cache
		if ($useCache && isset(self::$teilabrechnungCache[(int)$pkey])) return self::$teilabrechnungCache[(int)$pkey];
		// load from DB
		$object = new Teilabrechnung($db);
		if ($object->Load((int)$pkey, $db)!==true) $object = null;
		if ($useCache) self::$teilabrechnungCache[(int)$pkey] = $object;
		return $object;
	}
	
	/***************************************************************************
	* Gibt die Teilabrechnung, welche dem übergebenen Jahres-PKey untergeordnet ist, zurück, wenn diese noch KEINEM Prozess zugeordnet ist
	* @param object	$user		User-Objekt
	* @param int	$yearPkey	ID des Yahres für welche die Teilabrechnung angefordert werden soll
	* @param bool	$notAssignedOnly Wenn true dann werden nur die Teilabrechungen zurückgegeben, welche noch KEINEM Prozess zugeordnet ist | wenn false dann werden alle zurückgegeben
	* @return array	Teilabrechnungs-Objekte
	* @access public
	***************************************************************************/
	public function GetTeilabrechnungenByYear($user, $yearPkey, $notAssignedOnly=true){
		if( $user==null || get_class($user)!="User" || !is_numeric($yearPkey) || ((int)$yearPkey)!=$yearPkey )return Array();
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$data=$this->db->SelectAssoc("SELECT pkey FROM ".Teilabrechnung::TABLE_NAME." WHERE abrechnungsJahr=".$yearPkey." ".($notAssignedOnly ? "AND abrechnungsJahr NOT IN (SELECT abrechnungsJahr FROM ".ProcessStatus::TABLE_NAME.")" : ""));
		$objects=Array();
		for( $a=0; $a<count($data); $a++ ){
			$object=new Teilabrechnung($this->db);
			if( $object->Load((int)$data[$a]["pkey"], $this->db) )$objects[]=$object;
		}
		return $objects;
	}
	
	/**
	 * Gibt die Anzahl der Teilabrechnungsposition zurück
	 * @param User $user
	 * @param string $searchString
	 * @param Teilabrechnung $teilabrechnung
	 * @return int
	 */
	public function GetTeilabrechnungspositionCount($user, $searchString="", $teilabrechnung=null)
	{
		if ($user==null || get_class($user)!="User") return 0;
		$joinClause="";
		$additionalWhereClause="";
		if ($teilabrechnung!=null)
		{
			$joinClause="LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON ".Teilabrechnungsposition::TABLE_NAME.".teilabrechnung=".Teilabrechnung::TABLE_NAME.".pkey ";
			$additionalWhereClause=" ".Teilabrechnung::TABLE_NAME.".pkey=".$teilabrechnung->GetPKey();
		}
		// Für Suche
		$object=new Teilabrechnungsposition($this->db);
		$rowsToUseS = Array(	Array("tableName" => Teilabrechnungsposition::TABLE_NAME, "tableRowNames" => $object->GetTableConfig()->rowName) );
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		return $this->GetDBEntryCount($searchString, Teilabrechnungsposition::TABLE_NAME, $object->GetTableConfig()->rowName, $additionalWhereClause, $joinClause, "", $searchRowNames);
	}
	
	/**
	 * Gibt die Teilabrechnungsposition zurück
	 * @param User $user
	 * @return Teilabrechnungsposition[]
	 */
	public function GetTeilabrechnungsposition($user, $searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20, $teilabrechnung=null)
	{
		if ($user==null || get_class($user)!="User") return Array();
		$joinClause="";
		$additionalWhereClause="";
		if ($teilabrechnung!=null)
		{
			$joinClause="LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON ".Teilabrechnungsposition::TABLE_NAME.".teilabrechnung=".Teilabrechnung::TABLE_NAME.".pkey ";
			$additionalWhereClause=" ".Teilabrechnung::TABLE_NAME.".pkey=".$teilabrechnung->GetPKey();
		}
		$object=new Teilabrechnungsposition($this->db);
		$rowsToUseS = Array(	Array("tableName" => Teilabrechnungsposition::TABLE_NAME, "tableRowNames" => $object->GetTableConfig()->rowName) );
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		$dbEntrys=$this->GetDBEntryData($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, Teilabrechnungsposition::TABLE_NAME, $object->GetTableConfig()->rowName, $additionalWhereClause, $joinClause, "", $searchRowNames);
		// Objekte erzeugen
		$objects=Array();
		for ($a=0; $a<count($dbEntrys); $a++)
		{
			$object = new Teilabrechnungsposition($this->db, true);
			if ($object->LoadFromArray($dbEntrys[$a], $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}

	/**
	 * @param string $searchString
	 * @return array
	 */
	public function GetBezeichnungTeilflächeAndFmsKostenart($searchString, $currentPage=0, $numEntrysPerPage=20)
	{
		// Query
		$query1 = "SELECT bezeichnungKostenart, kostenartRS, COUNT(kostenartRS) AS rsUsage FROM ".Teilabrechnungsposition::TABLE_NAME.(trim($searchString)!='' ? " WHERE (bezeichnungKostenart LIKE '%".$this->db->ConvertStringToDBString($searchString)."%' )" : "")." GROUP BY bezeichnungKostenart, kostenartRS ORDER BY bezeichnungKostenart";
		$query2 = "SELECT bezeichnungKostenart, MAX(rsUsage) rsUsage FROM(".$query1.") as T1 GROUP BY bezeichnungKostenart";
		$query3 = "SELECT a.* FROM (".$query1.") a INNER JOIN (".$query2.") b ON a.bezeichnungKostenart = b.bezeichnungKostenart AND a.rsUsage = b.rsUsage GROUP BY bezeichnungKostenart ";
		if ($numEntrysPerPage>0) $query3.=" LIMIT ".((int)$currentPage*(int)$numEntrysPerPage).", ".(int)$numEntrysPerPage;
		return $this->db->SelectAssoc($query3);
	}
	
	
	/***************************************************************************
	* Gibt die Teilabrechnung mit der angeforderten ID zurück
	* @param object	$user					User-Objekt
	* @param int	$teilabrechnungspositionID	ID der Teilabrechnungsposition die angefordert werden soll
	* @return int	Teilabrechnung-Objekt oder null
	* @access public
	***************************************************************************/
	public function GetTeilabrechnungspositionByID($user, $teilabrechnungspositionID){
		if( $user==null || get_class($user)!="User" || !is_numeric($teilabrechnungspositionID) || ((int)$teilabrechnungspositionID)!=$teilabrechnungspositionID )return null;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$object=new Teilabrechnungsposition($this->db);
		if( !$object->Load((int)$teilabrechnungspositionID, $this->db) )return null;
		return $object;
	}
	
	/**
	 * Gibt die Anzahl der Widerspruchspunkte zurück
	 * @param User $user
	 * @param string $searchString
	 * @param Widerspruch $widerspruch
	 * @return int
	 */
	public function GetWiderspruchspunkteCount($user, $searchString="", $widerspruch=null)
	{
		if ($user==null || get_class($user)!="User" || $widerspruch==null) return 0;
		$joinClause="";
		$additionalWhereClause="";
		if( $widerspruch!=null )
		{
			$joinClause="LEFT JOIN ".Widerspruch::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".widerspruch=".Widerspruch::TABLE_NAME.".pkey ";
			$additionalWhereClause = " ".Widerspruch::TABLE_NAME.".pkey=".$widerspruch->GetPKey();
			$additionalSelectField = "  (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE widerspruchspunkt=".Widerspruchspunkt::TABLE_NAME.".pkey AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN.") AS kuerzungGruen ";
			$additionalSelectField.= ", (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE widerspruchspunkt=".Widerspruchspunkt::TABLE_NAME.".pkey AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB.") AS kuerzungGelb ";
			$additionalSelectField.= ", (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE widerspruchspunkt=".Widerspruchspunkt::TABLE_NAME.".pkey AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT.") AS kuerzungRot ";
			$additionalSelectField.= ", (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE widerspruchspunkt=".Widerspruchspunkt::TABLE_NAME.".pkey AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU.") AS kuerzungGrau ";
		}
		$object=new Widerspruchspunkt($this->db);
		$rowNames = $object->GetTableConfig()->rowName;
		$rowNames[] = "kuerzungGruen";
		$rowNames[] = "kuerzungGelb";
		$rowNames[] = "kuerzungRot";
		$rowNames[] = "kuerzungGrau";
		$rowsToUseS = Array(	Array("tableName" => Widerspruchspunkt::TABLE_NAME, "tableRowNames" => $object->GetTableConfig()->rowName) );
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		return $this->GetDBEntryCount($searchString, Widerspruchspunkt::TABLE_NAME, $rowNames, $additionalWhereClause, $joinClause, "", $searchRowNames, $additionalSelectField);
	}
	
	/**
	 * Gibt die Widerspruchspunkte zurück
	 * @param User $user
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @param Widerspruch $widerspruch
	 * @return Widerspruchspunkt[]
	 */
	public function GetWiderspruchspunkte($user, $searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20, $widerspruch=null)
	{
		if ($user==null || get_class($user)!="User" || $widerspruch==null) return Array();
		$joinClause="";
		$additionalWhereClause="";
		if ($widerspruch!=null)
		{
			$joinClause="LEFT JOIN ".Widerspruch::TABLE_NAME." ON ".Widerspruchspunkt::TABLE_NAME.".widerspruch=".Widerspruch::TABLE_NAME.".pkey ";
			$additionalWhereClause=" ".Widerspruch::TABLE_NAME.".pkey=".$widerspruch->GetPKey();
			$additionalSelectField ="  (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE widerspruchspunkt=".Widerspruchspunkt::TABLE_NAME.".pkey AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN.") AS kuerzungGruen ";
			$additionalSelectField.=", (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE widerspruchspunkt=".Widerspruchspunkt::TABLE_NAME.".pkey AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB.") AS kuerzungGelb ";
			$additionalSelectField.=", (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE widerspruchspunkt=".Widerspruchspunkt::TABLE_NAME.".pkey AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT.") AS kuerzungRot ";
			$additionalSelectField.=", (SELECT SUM(kuerzungsbetrag) FROM ".Kuerzungsbetrag::TABLE_NAME." WHERE widerspruchspunkt=".Widerspruchspunkt::TABLE_NAME.".pkey AND rating=".Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU.") AS kuerzungGrau ";
		}
		$object=new Widerspruchspunkt($this->db);
		$rowNames = $object->GetTableConfig()->rowName;
		$rowNames[] = "kuerzungGruen";
		$rowNames[] = "kuerzungGelb";
		$rowNames[] = "kuerzungRot";
		$rowNames[] = "kuerzungGrau";
		$rowsToUseS = Array(	Array("tableName" => Widerspruchspunkt::TABLE_NAME, "tableRowNames" => $object->GetTableConfig()->rowName) );
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		$dbEntrys=$this->GetDBEntry($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, Widerspruchspunkt::TABLE_NAME, $rowNames, $additionalWhereClause, $joinClause, "", $searchRowNames, $additionalSelectField);
		// Objekte erzeugen
		$objects=Array();
		for ($a=0; $a<count($dbEntrys); $a++)
		{
			$object=new Widerspruchspunkt($this->db);
			if ($object->Load($dbEntrys[$a], $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}

	/**
	 * Gibt die Widerspruchspunkt mit der angeforderten ID zurück
	 * @param User $user
	 * @param int $wspID
	 * @return Widerspruchspunkt
	 */
	public function GetWiderspruchspunktById($user, $wspID)
	{
		if( $user==null || get_class($user)!="User" || !is_numeric($wspID) || ((int)$wspID)!=$wspID )return null;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$object=new Widerspruchspunkt($this->db);
		if( !$object->Load((int)$wspID, $this->db) )return null;
		return $object;
	}

	/**
	 * Gibt den Widerspruch mit der angeforderten ID zurück
	 * @param User $user
	 * @param int $wsID
	 * @return Widerspruch
	 */
	public function GetWiderspruchById($user, $wsID)
	{
		if( $user==null || get_class($user)!="User" || !is_numeric($wsID) || ((int)$wsID)!=$wsID )return null;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$object=new Widerspruch($this->db);
		if( !$object->Load((int)$wsID, $this->db) )return null;
		return $object;
	}
	
	/**
	 * Gibt die Anzahl der Kürzungsbeträge für den übergbenen Widerspruchspunkt zurück
	 * @param User $user
	 * @param Widerspruchspunkt $widerspruchspunkt
	 * @param string $searchString
	 * @return int
	 */
	public function GetKuerzungsbetraegeCount(User $user, Widerspruchspunkt $widerspruchspunkt, $searchString="")
	{
		$joinClause="LEFT JOIN ".Widerspruchspunkt::TABLE_NAME." ON ".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt=".Widerspruchspunkt::TABLE_NAME.".pkey ";
		$additionalWhereClause=" ".Widerspruchspunkt::TABLE_NAME.".pkey=".$widerspruchspunkt->GetPKey();
		$object=new Kuerzungsbetrag($this->db);
		$rowsToUseS = Array(	Array("tableName" => Kuerzungsbetrag::TABLE_NAME, "tableRowNames" => $object->GetTableConfig()->rowName) );
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		return $this->GetDBEntryCount($searchString, Kuerzungsbetrag::TABLE_NAME, $object->GetTableConfig()->rowName, $additionalWhereClause, $joinClause, "", $searchRowNames);
	}
	
	/**
	 * Gibt die Kürzungsbeträge des übergbenen Widerspruchspunkt zurück
	 * @param User $user
	 * @param Widerspruchspunkt $widerspruchspunkt
	 * @param string $searchString
	 * @return Kuerzungsbetrag[]
	 */
	public function GetKuerzungsbetraege(User $user, Widerspruchspunkt $widerspruchspunkt, $searchString="", $orderBy="pkey", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20)
	{
		$joinClause = "LEFT JOIN ".Widerspruchspunkt::TABLE_NAME." ON ".Kuerzungsbetrag::TABLE_NAME.".widerspruchspunkt=".Widerspruchspunkt::TABLE_NAME.".pkey ";
		$additionalWhereClause = " ".Widerspruchspunkt::TABLE_NAME.".pkey=".$widerspruchspunkt->GetPKey();
		$object=new Kuerzungsbetrag($this->db);
		$rowsToUseS = Array(	Array("tableName" => Kuerzungsbetrag::TABLE_NAME, "tableRowNames" => $object->GetTableConfig()->rowName) );
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		$dbEntrys=$this->GetDBEntry($searchString, $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, Kuerzungsbetrag::TABLE_NAME, $object->GetTableConfig()->rowName, $additionalWhereClause, $joinClause, "", $searchRowNames);
		// Objekte erzeugen
		$objects=Array();
		for( $a=0; $a<count($dbEntrys); $a++)
		{
			$object=new Kuerzungsbetrag($this->db);
			if ($object->Load($dbEntrys[$a], $this->db)===true) $objects[] = $object;
		}
		return $objects;
	}

	/**
	 * Gibt Kürzungsbetrag mit der angeforderten ID zurück
	 * @param User $user
	 * @param int $kuerzungsbetragID
	 * @return Kuerzungsbetrag
	 */
	public function GetKuerzungsbetragById($user, $kuerzungsbetragID)
	{
		if( $user==null || get_class($user)!="User" || !is_numeric($kuerzungsbetragID) || ((int)$kuerzungsbetragID)!=$kuerzungsbetragID )return null;
		// Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden
		$object=new Kuerzungsbetrag($this->db);
		if( !$object->Load((int)$kuerzungsbetragID, $this->db) )return null;
		return $object;
	}

	/**
	 * Gibt die Anzahl der Abrechnungsjahre zurück
	 * @param User $user
	 * @param string $searchString
	 * @return int Anzahl
	 * @access public
	 */
	public function GetAbrechnungsjahreCount(User $user, $searchString="")
	{
		// Berechtigung
		$groupQuery="";
		if ($user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			$groupIDs=$user->GetGroupIDs($this->db);
			for ($a=0; $a<count($groupIDs); $a++)
			{
				if ($groupQuery!="") $groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}

		$joinClause ="LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => AbrechnungsJahr::TABLE_NAME, "tableRowNames" => $this->abrechnungsJahrDummy->GetTableConfig()->rowName),
							Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
							Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
						);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUse );
		// Für Suche
		$rowsToUseS= Array(	Array("tableName" => AbrechnungsJahr::TABLE_NAME, "tableRowNames" => $this->abrechnungsJahrDummy->GetTableConfig()->rowName),
							Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
						);
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );
		return $this->GetDBEntryCount($searchString, AbrechnungsJahr::TABLE_NAME, $allRowNames, $groupQuery, $joinClause, "", $searchRowNames);
	}

	/**
	 * @param User $user
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntriesPerPage
	 * @return array
	 */
	public function GetAbrechnungsjahre(User $user, $searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntriesPerPage=20){
		// Berechtigung
		$groupQuery="";
		if ($user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			$groupIDs=$user->GetGroupIDs($this->db);
			for( $a=0; $a<count($groupIDs); $a++)
			{
				if( $groupQuery!="" )$groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="(".$groupQuery.")";
		}

		$joinClause ="LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
		$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		$rowsToUse= Array(	Array("tableName" => AbrechnungsJahr::TABLE_NAME, "tableRowNames" => $this->abrechnungsJahrDummy->GetTableConfig()->rowName),
							Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
							Array("tableName" => CCompany::TABLE_NAME, "tableRowNames" => $this->companyDummy->GetTableConfig()->rowName),
							Array("tableName" => CGroup::TABLE_NAME, "tableRowNames" => $this->groupDummy->GetTableConfig()->rowName),
						);
		$allRowNames = $this->BuildRowNameArrayForMultipleTable($rowsToUse);
		// Für Suche
		$rowsToUseS= Array( Array("tableName" => AbrechnungsJahr::TABLE_NAME, "tableRowNames" => $this->abrechnungsJahrDummy->GetTableConfig()->rowName),
							Array("tableName" => Contract::TABLE_NAME, "tableRowNames" => $this->contractDummy->GetTableConfig()->rowName),
							Array("tableName" => CShop::TABLE_NAME, "tableRowNames" => $this->shopDummy->GetTableConfig()->rowName),
							Array("tableName" => CLocation::TABLE_NAME, "tableRowNames" => $this->locationDummy->GetTableConfig()->rowName),
						);
		$searchRowNames = $this->BuildRowNameArrayForMultipleTable( $rowsToUseS );

		$dbEntries = $this->GetDBEntry($searchString, $orderBy, $orderDirection, $currentPage, $numEntriesPerPage, AbrechnungsJahr::TABLE_NAME, $allRowNames, $groupQuery, $joinClause, "", $searchRowNames);
		// Objekte erzeugen
		$objects = Array();
		for ($a=0; $a<count($dbEntries); $a++)
		{
			$object = new AbrechnungsJahr($this->db);
			if ($object->Load($dbEntries[$a], $this->db)===true) $objects[]=$object;
		}
		return $objects;
	}

	/**
	 * Gibt die Jahre zurück
	 * @param User $user
	 * @return int[]
	 */
	public function GetYears(User $user)
	{
		// Berechtigung 
		$joinClause=$groupQuery="";
		if ($user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			$groupIDs=$user->GetGroupIDs($this->db);
			for ($a=0; $a<count($groupIDs); $a++)
			{
				if ($groupQuery!="") $groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			$groupQuery="WHERE (".$groupQuery.")";
		
			// Join für Berechtigung
			$joinClause ="LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON ".Teilabrechnung::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey ";
			$joinClause.="LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract=".Contract::TABLE_NAME.".pkey ";
			$joinClause.="LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey ";
			$joinClause.="LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey ";
			$joinClause.="LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey ";
			$joinClause.="LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey ";
		}
		$data=$this->db->SelectAssoc("SELECT ".AbrechnungsJahr::TABLE_NAME.".jahr FROM ".AbrechnungsJahr::TABLE_NAME." ".$joinClause." ".$groupQuery." GROUP BY ".AbrechnungsJahr::TABLE_NAME.".jahr ORDER BY ".AbrechnungsJahr::TABLE_NAME.".jahr");
		$returnValue=Array();
		for ($a=0; $a<count($data); $a++)
		{
			$returnValue[]=$data[$a]["jahr"];
		}
		return $returnValue;
	}

	/**
	 * Gibt den Vertrag mit der angeforderten ID zurück
	 * @param User $user
	 * @param int $yearID ID des Vertrags der angefordert werden soll
	 * @return AbrechnungsJahr
	 */
	public function GetAbrechnungsJahrByID($user, $yearID)
	{
		if ($user==null || get_class($user)!="User" || !is_numeric($yearID) || ((int)$yearID)!=$yearID) return null;
		// TODO: Wenn übergebener Benutzer ein Kunde ist, darf nur dessen Gruppe zurückgegeben werden!!

		$object=new AbrechnungsJahr($this->db);
		if( !$object->Load((int)$yearID, $this->db) )return null;
		return $object;
	}
	
	/**
	 * Return AbrechnungsJahr instance by pkey
	 * @param DBManager $db
	 * @param int $pkey
	 * @param boolean $useCache
	 * @return AbrechnungsJahr
	 */
	static public function GetAbrechnungsJahrByPkey(DBManager $db, $pkey, $useCache=true)
	{
		// look up cache
		if ($useCache && isset(self::$abrechnungsjahrCache[(int)$pkey])) return self::$abrechnungsjahrCache[(int)$pkey];
		// load from DB
		$object = new AbrechnungsJahr($db);
		if ($object->Load((int)$pkey, $db)!==true) $object = null;
		if ($useCache) self::$abrechnungsjahrCache[(int)$pkey] = $object;
		return $object;
	}
	
}


?>
