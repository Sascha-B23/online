<?php
include_once("EntryIDManager.lib.php5");		// EntryID-Manager einbinden

/**
 * Basisklasse für alle Datenbank-Implementierungen (MSSQL, MySQL etc.)
 *
 * @access public
 * @abstract
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class DBManager {

	/**
	 * LanguageManager
	 * @var LanguageManager
	 * @access protected
	 */
	protected $lm = null;

	/**
	 * ErrorManager
	 * @var ErrorManager
	 * @access protected
	 */
	protected $em = null;

	/**
	 * Name der Datenbank, zu der eine Verbundung aufgebaut werden soll
	 * @var string
	 * @access protected
	 */
	protected $dbName="";

	/**
	 * Username zum Verbinden mit DB-Server
	 * @var string
	 * @access protected
	 */
	protected $dbUser="";

	/**
	 * Passwort zum Verbinden mit DB-Server
	 * @var string
	 * @access protected
	 */
	protected $dbPWD="";

	/**
	 * URL des Datenbankservers
	 * @var string
	 * @access protected
	 */
	protected $dbHost="";

	/**
	 * Array mit allen Tables, für die bereits die Fn CreateOrUpdateTable(...)
	 * aufgerufen wurde (Geschwindigkeitsoptimierung)
	 * @var array
	 * @access protected
	 */
	protected static $updatedTables = Array();

	/**
	 * Konstruktor
	 * @param string $dbName			Datenbankname
	 * @param string $dbHost			Datenbankserver
	 * @param string $dbUser			Username
	 * @param string $dbPWD			Passwort
	 * @param ErrorManager $em		ErrorManager-Objekt
	 * @param LanguageManager $lm	LanguageManager-Objekt
	 * @access public
	 */
	public function DBManager($dbName, $dbHost, $dbUser, $dbPwd, &$em, &$lm){
		$this->dbName=$dbName;
		$this->dbHost=$dbHost;
		$this->dbUser=$dbUser;
		$this->dbPWD=$dbPwd;
		$this->em=$em;
		$this->lm=$lm;
	}

	/**
	 * Führt die übergebene SQL query aus und gibt bei Erfolg die Query-ID zurück
	 * @param string $query				SQL-Query
	 * @return ResourceID 				query_id
	 * @access public
	 */
	abstract public function Query($query);

	/**
	 * Erstellt die Tabelle $tableName mit den in $rowName und $rowParam hinterlegten Spalten.
	 * @param string $tableName			Namen der neuen Tabelle
	 * @param array $rowName				Array mit den Spaltennamen der neuen Tabelle
	 * @param array $rowParam 			Array mit den Parametern der einzelnen Spalten (Datentypen).
	 * @param string $primaryKey			Namen des Primärschlüssels
	 * @param string $primaryKeyType		Datentyp des Primärschlüssels
	 * @return bool 						Erfolg
	 * @access public
	 */
	abstract public function CreateTable($tableName, $rowName, $rowParam, $primaryKey="pkey", $primaryKeyType="BIGINT");

	/**
	 * Löscht eine Tabelle
	 * @param string $tableName 			Name der zu löschenden Tabelle
	 * @return bool 						Erfolg
	 * @access public
	 */
	abstract public function DeleteTable($tableName);

	/**
	 * Prüft ob die übergebene Tabelle vorhanden ist
	 * @param string $tableName			Name der Tabelle die geprüft werden soll
	 * @return bool 						Ja = true, Nein =false
	 * @access public
	 */
	abstract public function TableExists($tableName);

	/**
	 * Gibt die Namen der Tabellenspalten in einem Array zurück
	 * @param string $tableName			Name der Tabelle deren Spaltennamen zurückgegeben werden soll
	 * @return array						Namen der Tabellenspalten
	 * @access public
	 */
	abstract public function GetTableRows($tableName);

	/**
	 * Prüft ob die gesuchte Spalte in der angegebenen Tabelle vorhanden ist
	 * @param string $tableName 			Tabellenname
	 * @param string $rowName 			Spaltenname
	 * @return bool						Ja = true, Nein =false
	 * @access public
	 */
	abstract public function TableRowExists($tableName,$rowName);

	/**
	 * Fügt eine neue Spalte in Tabelle ein
	 * @param string $tableName 			Tabellenname
	 * @param string $rowName 			Spaltenname
	 * @param string $rowType 			Parameter der neuen Spalte (Datentyp etc.)
	 * @return bool						Erfolg
	 * @access public
	 */
	abstract public function InsertRow($tableName,$rowName,$rowType);

	/**
	 * Fügt in die angegebene Tabelle in die angegebenen Spalten die angegebenen Daten ein
	 * @param string $tableName 			Tabelle in die eingefügt werden soll
	 * @param array $rowName 			Spaltennamen in die eingefügt werden soll
	 * @param array $rowData 			Daten die in die genannten Spalten eingefügt werden sollen
	 * @param bool $allowInsertToIdent
	 * @return string IDENTITY			Identität des Inserts (pkey der Zeile)
	 * @access public
	 */
	abstract public function Insert($tableName,$rowName,$rowData,$allowInsertToIdent=false);

	/**
	 * Fügt in die Tabelle tableName in die Spalten rowName die Daten aus rowData ein.
	 * @param string $tableName 			Tabelle in die eingefügt werden soll
	 * @param array $rowName 			Spaltennamen deren Inhalt geändert werden sollen
	 * @param array $rowData 			Neue Inhalte der zu ändernden Spalten
	 * @param array $where 				SQL-Bedingung der zu ändernden Datensätze
	 * @return bool						Erfolg
	 * @access public
	 */
	abstract public function Update($tableName,$rowName,$rowData,$where);

	/**
	 * Löscht die Spalte rowName in der Tabelle tableName
	 * @param	string $tableName 		Tabellenname
	 * @param	string $rowName 		Name der Spalte die gelöscht werden soll
	 * @return boolean
	 * @access public
	 */
	abstract public function DeleteRow($tableName,$rowName);

	/**
	 * Ändert den Datensatz pkey in Tabelle tableName. Nicht genannte Spalten bleiben unberührt.
	 * @param string $tableName 			Tabelle in die eingefügt werden soll
	 * @param array $rowName 			Spaltennamen deren Inhalt geändert werden sollen
	 * @param array $rowData 			Neue Inhalte der zu ändernden Spalten
	 * @param int $pkey 					Pkey des zu ändernden Datensatztes
	 * @return bool	 					Erfolg
	 * @access public
	 */
	abstract public function UpdateByPkey($tableName,$rowName,$rowData,$pkey);

	/**
	 * Löscht Datensatz pkey in Tabelle tableName
	 * @param string $tableName 			Tabelle aus der gelöscht werden soll
	 * @param int $pkey 					Pkey des zu löschenden Datensatztes
	 * @return bool						Erfolg
	 * @access public
	 */
	abstract public function DeleteByPkey($tableName,$pkey);

	/**
	 * Löscht Datensatz pkey in Tabelle tableName
	 * @param	string $tableName 	Tabelle aus der gel�scht werden soll
	 * @param	int $pkey 		Pkey des zu l�schenden Datensatztes
	 * @return boolean
	 * @access public
	 */
	abstract public function Delete($tableName, $where);

	/**
	 * Führt die übergebene SQL query aus und gibt die Ergebnisse in einem assoziativen Array zurück
	 * 
	 * @param string $query 				SQL Abfrage
	 * @param	boolean $selectOnline	Selektiert nur Datensätzte die nach dem WI2.x Tabellenformat online sind
	 * @return array 					Array mit den Ergebinssen der Datenbankabfrage
	 * @access public
	 */
	abstract public function SelectAssoc($query, $selectOnline=false);

	/**
	 * Führt die Übergebene SQL query aus und gibt die Ergebnisse in einem assoziativen
	 * Array zurück. Die Daten werden für HTML-Ausgabe aufbereitet.
	 * Array[index]["row1Name"]["row2name"]...
	 * To Do: selectOnline auf WI3 anpassen
	 * %UID% wird durch UID=<?=$UID?\> ersetzt.
	 * @param	string $query 			SQL Abfrage
	 * @param	boolean $selectOnline	Selektiert nur Datensätzte die nach dem WI2.x Tabellenformat online sind
	 * @return array
	 * @access public
	 */
	abstract function SelectHtmlAssoc($query,$selectOnline=false);

	/**
	 * Führt die übergebene SQL query aus und gibt die Ergebnisse in einem Array zurück: Array[index][spalte 0][spalte 1]...
	 * @param string $query 				SQL Abfrage
	 * @return array 					Array mit den Ergebinssen der Datenbankabfrage
	 * @access public
	 */
	abstract public function Select($query);

	/**
	 * Erzeugt einen Index
	 * @param string $tableName 			Tabellenname
	 * @param string $rowName 			Spaltenname
	 * @param bool $unique 				Soll der Index unique sein?
	 * @access public
	 */
	abstract public function CreateIndex($tableName, $rowName, $unique=false);

	/**
	 * Hilfsfunktion zum entfernenen von Sonderzeichen aus Datenbankinhalten
	 * @param string $string 			String
	 * @return string  					DBString
	 * @access public
	 */
	abstract public function ConvertStringToDBString($string);

	/**
	 * Erzeugt oder aktuallsiert die Tabelle entsprechend der übergebenen Tabellenkonfiguration
	 * @param DBConfig $config 			Tabellenkonfiguration
	 * @param bool $forceUpdate 			Wenn true, dann wird der Abgleich der übergeben Konfiguration mit der vorhandenen DB-Table erzwungen
	 * @return bool						Erfolg
	 * @access public
	 */
	public function CreateOrUpdateTable($config, $forceUpdate=false){
		// Diese Optimierung verhindert, dass ein Update der Table nicht mehr als einmal je Objekt je Seitenaufruf vorgenommen wird
		if( !$forceUpdate && in_array($config->tableName, self::$updatedTables) ){
			// Update auf Table wurde bereits ausgeführt
			//echo $config->tableName." bereits geprüft</br>";
			return true;
		}
		self::$updatedTables[]=$config->tableName;
		$signature=get_class($this)."::CreateOrUpdateTable(DBConfig config)";
		if( !is_a($config, "DBConfig") ) die ($this->em->ShowError($signature, "Parameter muss vom Typ DBConfig sein"));
		if( !$this->TableExists($config->tableName) ){
			// Tabelle neu anlegen
			$this->CreateTable($config->tableName, $config->rowName, $config->rowParam);
				
			// Index hinzufügen
			for( $a=0; $a<count($config->rowIndex); $a++ )
			{
				if (is_array($config->rowIndex[$a]))
				{
					$this->CreateIndex($config->tableName, $config->rowIndex[$a]["columns"], isset($config->rowIndex[$a]["unique"]) ? $config->rowIndex[$a]["unique"] : false );
				}
				else
				{
					$this->CreateIndex($config->tableName, $config->rowIndex[$a]);
				}
			}
		}else{
			// Prüfen, was sich geändert hat
			for($a=0; $a<count($config->rowName); $a++){
				if( !$this->TableRowExists($config->tableName, $config->rowName[$a]) ){
					$this->InsertRow($config->tableName, $config->rowName[$a], $config->rowParam[$a]);
				}
			}
		}
		return true;
	}

	/**
	 * Prüft, ob der übergebene Timestamp im übergebene Zeitfenster liegt
	 * @param int|string $checkDate	Timestamp oder Datum im Format tt.mm.jjjj das geprüft werden soll
	 * @param int|string $startDate		Timestamp oder Datum im Format tt.mm.jjjj mit Anfang des Zeitfensters (ganzer Tag)
	 * @param int|string $endDate		Timestand oder Datum im Format tt.mm.jjjj mit Ende des Zeitfensters (ganzer Tag)
	 * @return bool				Wenn der Timestamp im übergebene Zeitfenster liegt wird true zurückgegeben andernfalls false
	 * @access public
	 */
	static public function UpToDate($checkDate, $startDate, $endDate){
		if($endDate==-1)unset($endDate);
		if($startDate==-1)unset($startDate);
		//Wenn Eingabe(n) nicht im TimeStamp Format sind werden Sie umkonvertiert
		if(strstr($checkDate,'.')!=false){$TeileCD=explode(".", $checkDate);$StampCD=mktime(0,0,0,$TeileCD[1],$TeileCD[0],$TeileCD[2]);} else {$StampCD=$checkDate;}
		if(strstr($startDate,'.')!=false){$TeileSD=explode(".", $startDate);$StampSD=mktime(0,0,0,$TeileSD[1],$TeileSD[0],$TeileSD[2]);} else {$StampSD=$startDate;}
		if(strstr($endDate,'.')!=false){$TeileED=explode(".", $endDate);$StampED=mktime(23,59,59,$TeileED[1],$TeileED[0],$TeileED[2]);} else {if($endDate>0){$StampED=$endDate+86400;}}
		//---------------------------------------------------------------------
		//Aktuell? true=Ja, false=Nein
		if(!isset($StampSD))return false;
		if(($StampSD <= $StampCD)&&($StampCD<=$StampED)){
			return true;
		}elseif(!isset($StampED)){
			if($StampSD<=$StampCD)return true;
			else return false;
		}
		return false;
	}

} // class DBManager



/**
 * Basisklasse für Suchfunktionalität
 *
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class SearchDBEntry {

	/**
	 * 
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @param string $tableName
	 * @param string[] $rowNames
	 * @param string $additionalWhereClause
	 * @param string $joinClause
	 * @param string $groupBy
	 * @param string[] $searchRowNames
	 * @param string $additionalSelectField
	 * @param string $dataTableName
	 * @param string $additionalSelectField
	 * @return type 
	 */
	protected function GetDBEntryData($searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20, $tableName="", $rowNames=Array(), $additionalWhereClause="", $joinClause="", $groupBy="", $searchRowNames=Array(), $dataTableName="", $additionalSelectField="")
	{
		// get all pkeys
		$query = $this->CreateQueryString($this->db->ConvertStringToDBString($searchString), $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, $tableName, $rowNames, $additionalWhereClause, $joinClause, $groupBy, $searchRowNames, $additionalSelectField);
		//echo $query;
		$pkeys = $this->db->SelectAssoc($query);
		if (count($pkeys)==0) return Array();
		$whereClause = "";
		foreach ($pkeys as $pkey)
		{
			if ($whereClause!='') $whereClause.=',';
			$whereClause.=$pkey['pkey'];
		}
		// get data to all pkeys
		if ($dataTableName=="") $dataTableName = $tableName;
		$query = "SELECT * FROM ".$dataTableName." WHERE pkey IN (".$whereClause.")";
		//echo $query;
		$data = $this->db->SelectAssoc($query);
		// restore sorting
		$pkeyMap = Array();
		foreach ($data as $value)
		{
			$pkeyMap[$value['pkey']] = $value;
		}
		$returnValue = Array();
		foreach ($pkeys as $pkey)
		{
			$returnValue[] = $pkeyMap[$pkey['pkey']];
		}
		return $returnValue;
	}
	
	/**
	 * Gibt die DBEntrys entsprechend den übergebenen Parametern zurück
	 * @param string $searchString				Suchstring
	 * @param string $orderBy					Spaltenname nach dem sortiert werden soll
	 * @param int $orderDirection				Sortierrichtung (ASC oder DESC)
	 * @param int $currentPage					Aktuelle Seite
	 * @param int $numEntrysPerPage				Anzhal der Einträge je Seite
	 * @param string $tableName					Tabellenname
	 * @param array $rowNames					Tabellenspalten
	 * @param string $additionalWhereClause		Zusätzliche WHERE-Bedingung
	 * @param string $joinClause					Zusätzliche JOIN-Bedingung
	 * @param string $groupBy					Zusätzliche GROUP BY-Bedingung
	 * @return array								Array mit den pkeys der DBEntrys
	 * @access protected
	 */
	protected function GetDBEntry($searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20, $tableName="", $rowNames=Array(), $additionalWhereClause="", $joinClause="", $groupBy="", $searchRowNames=Array(), $additionalSelectField="")
	{
		$query = $this->CreateQueryString($this->db->ConvertStringToDBString($searchString), $orderBy, $orderDirection, $currentPage, $numEntrysPerPage, $tableName, $rowNames, $additionalWhereClause, $joinClause, $groupBy, $searchRowNames, $additionalSelectField);
		$dataIDs = $this->db->SelectAssoc($query);
		$returnValue = Array();
		for ($a=0; $a<count($dataIDs); $a++)
		{
			$returnValue[] = (int)$dataIDs[$a]["pkey"];
		}
		return $returnValue;
	}
	
	/**
	 * Create a SQL query from the given parameters
	 * @param string $searchString
	 * @param string $orderBy
	 * @param int $orderDirection
	 * @param int $currentPage
	 * @param int $numEntrysPerPage
	 * @param string $tableName
	 * @param string[] $rowNames
	 * @param string $additionalWhereClause
	 * @param string $joinClause
	 * @param string $groupBy
	 * @param string[] $searchRowNames
	 * @param string $additionalSelectField
	 * @return string 
	 */
	protected function CreateQueryString($searchString="", $orderBy="name", $orderDirection=0, $currentPage=0, $numEntrysPerPage=20, $tableName="", $rowNames=Array(), $additionalWhereClause="", $joinClause="", $groupBy="", $searchRowNames=Array(), $additionalSelectField="")
	{
		if (trim($additionalSelectField)!="") $additionalSelectField=", ".$additionalSelectField;
		$query="SELECT ".$tableName.".pkey".$additionalSelectField." FROM ".$tableName;
		if( trim($joinClause)!="" )$query.=" ".$joinClause." ";
		$whereClauseUsed=false;
		if( trim($searchString)!="" )
		{
			$stringsToSearch=explode(" ", trim($searchString));
			$whereClause="";
			if (count($searchRowNames)==0) $searchRowNames = $rowNames;
			for ($a=0; $a<count($searchRowNames); $a++)
			{
				for ($b=0; $b<count($stringsToSearch); $b++)
				{
					if (trim($stringsToSearch[$b])=="") continue;
					if ($whereClause!="") $whereClause.="OR ";
					$whereClause.=$searchRowNames[$a]." LIKE '%".trim($stringsToSearch[$b])."%' ";
				}
			}
			if ($whereClause!="")
			{
				$query.=" WHERE (".$whereClause.")";
				$whereClauseUsed = true;
			}
		}
		// Zusätzlicher WhereClause anhängen
		if (trim($additionalWhereClause)!="")
		{
			if ($whereClauseUsed) $query.=" AND ".trim($additionalWhereClause);
			else $query.=" WHERE ".trim($additionalWhereClause);
		}
		if (trim($groupBy)!="") $query.=" GROUP BY ".$groupBy;
		
		if (is_array($orderBy))
		{
			$orderByString = "";
			foreach ($orderBy as $value)
			{
				if (in_array($value ,$rowNames))
				{
					if ($orderByString!="") $orderByString.=", ";
					$orderByString.=$value;
				}
			}
			if ($orderByString!="") $query.=" ORDER BY ".$orderByString." ".((int)$orderDirection==0 ? "ASC" : "DESC");
		}
		else
		{
			if ($orderBy!="" && in_array($orderBy ,$rowNames)) $query.=" ORDER BY ".$orderBy." ".((int)$orderDirection==0 ? "ASC" : "DESC");
		}
		if ($numEntrysPerPage>0) $query.=" LIMIT ".((int)$currentPage*(int)$numEntrysPerPage).", ".(int)$numEntrysPerPage;
		return $query;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge für die Suche zurück
	 * @param string $searchString				Suchstring
	 * @param string $tableName					Tabellenname
	 * @param array $rowNames					Tabellenspalten
	 * @param string $additionalWhereClause		Zusätzliche WHERE-Bedingung
	 * @param string $joinClause					Zusätzliche JOIN-Bedingung
	 * @param string $groupBy					Zusätzliche GROUP BY-Bedingung
	 * @return int								Anzahl der Einträge
	 * @access protected
	 */
	protected function GetDBEntryCount($searchString="", $tableName="", $rowNames=Array(), $additionalWhereClause="", $joinClause="", $groupBy="", $searchRowNames=Array(), $additionalSelectField="")
	{
		if (trim($additionalSelectField)!="") $additionalSelectField=", ".$additionalSelectField;
		$query="SELECT count(".$tableName.".pkey) AS count".$additionalSelectField." FROM ".$tableName;
		if( trim($joinClause)!="" )$query.=" ".$joinClause." ";
		$whereClauseUsed=false;
		if( trim($searchString)!="" ){
			if( count($searchRowNames)==0 )$searchRowNames = $rowNames;
			$stringsToSearch=explode(" ", trim($this->db->ConvertStringToDBString($searchString)));
			$whereClause="";
			for( $a=0; $a<count($searchRowNames); $a++){
				for( $b=0; $b<count($stringsToSearch); $b++){
					if( trim($stringsToSearch[$b])=="" )continue;
					if( $whereClause!="" )$whereClause.="OR ";
					$whereClause.=$searchRowNames[$a]." LIKE '%".trim($stringsToSearch[$b])."%' ";
				}
			}
			if( $whereClause!="" ){
				$query.=" WHERE (".$whereClause.")";
				$whereClauseUsed=true;
			}
		}
		// Zusätzlicher WhereClause anhängen
		if( trim($additionalWhereClause)!="" ){
			if( $whereClause )$query.=" AND ".trim($additionalWhereClause);
			else $query.=" WHERE ".trim($additionalWhereClause);
		}
		if( trim($groupBy)!="" )$query.=" GROUP BY ".$groupBy;
		//echo $query."<br>";
		$count=$this->db->SelectAssoc($query);
		if( trim($groupBy)!="" )return count($count);
		return $count[0]["count"]!="" ? $count[0]["count"] : 0;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge für die Suche zurück zurück
	 * @param array	$tableRowNames in der Form Array( Array( "tableName" => "X", "tableRowNames" => Array("name1", "name2", ...)), ....)
	 * @return array	Alle Spaltennamen mit vorangestelltem Tabellennamen
	 * @access protected
	 */
	protected function BuildRowNameArrayForMultipleTable($tableRowNames){
		$retArray=Array();
		for( $a=0; $a<count($tableRowNames); $a++ ){
			for( $b=0; $b<count($tableRowNames[$a]["tableRowNames"]); $b++ ){
				$retArray[]=$tableRowNames[$a]["tableName"].".".$tableRowNames[$a]["tableRowNames"][$b];
			}
		}
		return $retArray;
	}

} // class SearchDBEntry



/**
 * Konfiguration einer Datenbank-Tabelle
 *
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class DBConfig {

	/**
	 * Name der Tabelle
	 * @var string
	 * @access public
	 */
	public $tableName="";

	/**
	 * Array mit den Spaltennamen der Tabelle
	 * @var array
	 * @access public
	 */
	public $rowName=Array();

	/**
	 * Array mit den Datentypen der einzelnen Spalten
	 * @var array
	 * @access public
	 */
	public $rowParam=Array();

	/**
	 * Array mit den Spaltennamen welche indiziert werden sollen
	 * @var array
	 * @access public
	 */
	public $rowIndex=Array();

	/**
	 * Fügt die übergebenen Spalten dieser Konfiguration hinzu
	 * @param string[] $rowNamesToInsert
	 * @param string[] $rowParamsToInsert
	 * @param string[] $rowIndexToInsert
	 * @param boolean $append
	 * @return boolean 
	 */
	public function InsertRowsAt($rowNamesToInsert, $rowParamsToInsert, $rowIndexToInsert, $append=false){
		if( !is_array($rowNamesToInsert) || !is_array($rowParamsToInsert) || !is_array($rowIndexToInsert) || count($rowNamesToInsert)!=count($rowParamsToInsert) ) return false;
		if( $append )
		{
			// Spaltennamen am Ende hinzufügen
			$tableRowNames = $this->rowName;
			array_splice($tableRowNames, count($tableRowNames), 0, $rowNamesToInsert);
			$this->rowName = $tableRowNames;
			// Spaltenparameter am Ende hinzufügen
			$tableRowParams = $this->rowParam;
			array_splice($tableRowParams, count($tableRowParams), 0, $rowParamsToInsert);
			$this->rowParam = $tableRowParams;
			// Index am Ende hinzufügen
			$tableRowIndex = $this->rowIndex;
			array_splice($tableRowIndex, count($tableRowIndex), 0, $rowIndexToInsert);
			$this->rowIndex = $tableRowIndex;
		}
		else
		{
			// Spaltennamen am Anfang hinzufügen
			$tableRowNames=$rowNamesToInsert;
			array_splice($tableRowNames, count($rowNamesToInsert), 0, $this->rowName);
			$this->rowName=$tableRowNames;
			// Spaltenparameter am Anfang hinzufügen
			$tableRowParams=$rowParamsToInsert;
			array_splice($tableRowParams, count($rowParamsToInsert), 0, $this->rowParam);
			$this->rowParam=$tableRowParams;
			// Index am Anfang hinzufügen
			$tableRowIndex=$rowIndexToInsert;
			array_splice($tableRowIndex, count($rowIndexToInsert), 0, $this->rowIndex);
			$this->rowIndex=$tableRowIndex;
		}
		return true;
	}
	
	/**
	 * Append the rows of the passed DBConfig object to this object
	 * @param DBConfig $config	Config to append
	 * @return bool Success
	 */
	public function Append(DBConfig $config)
	{
		return $this->InsertRowsAt($config->rowName, $config->rowParam, $config->rowIndex, true);
	}

} // class DBConfig



/**
 * Basisklasse für Datenbankobjekte
 *
 * @access   	public
 * @abstract
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class DBEntry extends EntryID  {

	/**
	 * Tabellenkonfiguration
	 * @var string
	 * @access protected
	 */
	protected $dbConfig=null;
	protected $additionalTableRowNames=Array("creationTime", "lastChangeTime", "undeletable");
	protected $additionalTableRowParams=Array("BIGINT", "BIGINT", "INT");
	protected $additionalTableIndex=Array();

	/**
	 * Datensatzschlüssel
	 * @var int
	 * @access protected
	 */
	protected $pkey=-1;

	/**
	 * Zeitpunkt an dem der Datensatz erzeugt wurde
	 * @var int
	 * @access protected
	 */
	protected $creationTime=0;

	/**
	 * Zeitpunkt an dem der Datensatz zuletzt geändert wurde
	 * @var int
	 * @access protected
	 */
	protected $lastChangeTime=0;

	/**
	 * Legt fest, ob der Datensatz gelöscht werden darf
	 * @var int
	 * @access protected
	 */
	protected $undeletable=0;

	/**
	 * If this flag is set to true no entry id will be generated
	 * @var boolean 
	 */
	protected $doNotGenerateEntryId = false;
	
	/**
	 * Konstruktor
	 * @param DBManager	$db			Datenbankobjekt
	 * @param DBConfig	$dbConfig	Tabellenkonfiguration
	 * @access public
	 */
	public function DBEntry(&$db, $dbConfig){
		// Entry-Objekt initialisieren
		if (!$this->doNotGenerateEntryId) parent::__construct();
		$this->dbConfig=$dbConfig;
		$this->dbConfig->InsertRowsAt($this->additionalTableRowNames, $this->additionalTableRowParams, $this->additionalTableIndex);
		$db->CreateOrUpdateTable($this->dbConfig);
	}

	/**
	 * Reset pkey and time stamps.
	 */
	public function ResetPKey()
	{
		// reset the pkey and other attributes - they are reassigned on Store().
		$this->pkey = -1;
		$this->creationTime = 0;
		$this->lastChangeTime = 0;
	}

	/**
	 * Gibt die EntryID zurück
	 * @return	String				EntryID
	 * @access 	public
	 */
	public function GetEntryID(){
		// Wenn das Objekt bereits in der DB ist, PKey zurückgeben
		if( $this->pkey!=-1 )return $this->pkey;
		return parent::GetEntryID();
	}

	/**
	 * Gibt den Datensatzschlüssel zurück
	 * @return int					Datensatzschlüssel oder -1
	 * @access public
	 */
	public function GetPKey(){
		return $this->pkey;
	}

	/**
	 * Gibt den Namen der Tabelle zurück
	 * @return string				Tabellenname
	 * @access public
	 */
	public function GetTableName(){
		return $this->dbConfig->tableName;
	}

	/**
	 * Gibt die Tabellenkonfiguration zurück
	 * @return DBConfig				Tabellenkonfiguration
	 * @access public
	 */
	public function GetTableConfig(){
		return $this->dbConfig;
	}

	/**
	 * Gibt das Erstellungsdatum des Datensatzes zurück
	 * @return int					Timestamp des Erstellungsdatum
	 * @access public
	 */
	public function GetCreationTime(){
		return $this->creationTime;
	}

	/**
	 * Setzt das Erstellungsdatum des Datensatzes 
	 * @param int Timestamp des Erstellungsdatum
	 * @return bool
	 */
	public function SetCreationTime($creationTime)
	{
		if (!is_int($creationTime)) return false;
		$this->creationTime = $creationTime;
		return  true;
	}
	
	/**
	 * Gibt das letzte Änderungsdatum des Datensatzes zurück
	 * @return int					Timestamp der letzten Änderung
	 * @access public
	 */
	public function GetLastChangeTime(){
		return $this->lastChangeTime;
	}

	/**
	 * Gibt zurück, ob der Datensatz gelöscht werden kann oder nicht
	 * @param DBManager 	$db			Datenbankobjekt
	 * @return bool					Kann der Datensatz gelöscht werden (true/false)
	 * @access public
	 */
	public function IsDeletable(&$db){
		return $this->undeletable==1 ? false : true;
	}

	/**
	 * Speichert das Objekt in die Tabelle
	 * @param DBManager 	$db			Datenbankobjekt
	 * @return int|bool				Fehlercode als Integer oder Boolean true
	 *						-1		Aufruf von Update gescheitert
	 *						-2		Methode wurde von DutyCodeUnknown Objekt aufgerufen
	 *						<-100	Fehlercode der beim Aufruf von BuildDBArray zurückgegeben wurde
	 * @access public
	 */
	public function Store(&$db){
		// Daten aus dem Objekt holen
		$rowName=Array();
		$rowData=Array();
		$retValue=$this->BuildDBArray($db, $rowName, $rowData);
		if( $retValue!==true ){
			// Fehler beim Erzeugen des DB-Arrays
			return -100+$retValue;
		}
		$now=time();
		if($this->pkey == -1){
			// Eintrag wird neu erstellt
			$rowName[]= "creationTime";
			$rowData[]= $now;
			$rowName[]= "lastChangeTime";
			$rowData[]= $now;
			$rowName[]= "undeletable";
			$rowData[]= $this->undeletable;
			//Datensatz hinuzfügen
			$this->pkey = $db->Insert($this->dbConfig->tableName, $rowName, $rowData);
			$this->creationTime=$now;
			$this->lastChangeTime=$now;
			$this->OnCreated($db);
			return true;
		}else{
			//Datensatz überschreiben
			$rowName[]= "creationTime";
			$rowData[]= $this->creationTime;
			$rowName[]= "lastChangeTime";
			$rowData[]= $now;
			$rowName[]= "undeletable";
			$rowData[]= $this->undeletable;
			if( !$db->UpdateByPkey($this->dbConfig->tableName, $rowName, $rowData, $this->pkey) )return -1;
			$this->lastChangeTime=$now;
			$this->OnChanged($db);
			return true;
		}
	}

	/**
	 * Lädt Objekt aus Tabelle
	 * @param int 		$pkey 		Datensatzschlüssel
	 * @param DBManager 	$db			Datenbankobjekt
	 * @return int|bool				Fehlercode als Integer oder Boolean true
	 * @access public
	 */
	public function Load($pkey, &$db){
		$data = $db->SelectAssoc("SELECT * FROM ".$this->dbConfig->tableName." WHERE pkey=".(int)$pkey, false);
		if( count($data)!=1 )return false;
		$this->pkey = (int)$pkey;
		$this->creationTime = $data[0]["creationTime"];
		$this->lastChangeTime = $data[0]["lastChangeTime"];
		$this->undeletable = $data[0]["undeletable"];
		return $this->BuildFromDBArray($db, $data[0]);
	}

	/**
	 * Lädt Objekt aus Tabelle
	 * @param array $data 			Daten
	 * @param DBManager $db			Datenbankobjekt
	 * @return int/bool				Fehlercode als Integer oder Boolean true
	 * @access public
	 */
	public function LoadFromArray(&$data, &$db){
		if( !is_array($data) || !isset($data["pkey"]) || ((int)$data["pkey"])!=$data["pkey"] )return false;
		$this->pkey = (int)$data["pkey"];
		$this->creationTime = $data["creationTime"];
		$this->lastChangeTime = $data["lastChangeTime"];
		$this->undeletable = $data["undeletable"];
		return $this->BuildFromDBArray($db, $data);
	}

	/**
	 * Löscht das Objekt aus der DB
	 * @param DBManager  $db			Datenbank Objekt
	 * @return bool					Erfolg
	 * @access public
	 */
	public function DeleteMe(&$db){
		if( $this->pkey==-1 || !$this->IsDeletable($db) )return false;
		if( !$this->OnDeleteMe($db) )return false;
		if( !$db->DeleteByPkey($this->dbConfig->tableName, $this->pkey) )return false;
		// EntryID-Objekt beim Manager abmelden, wenn Objekt gelöscht wird
		parent::DeleteMe($db);
		$this->pkey=-1;
		return true;
	}

	/**
	 * Wird vor dem Löschen des Objekts aus der DB aufgerufen
	 * @param DBManager  $db			Datenbank Objekt
	 * @return bool					Erfolg (wenn false zurückgegeben wird, wird das Objekt nicht aus der DB gelöscht!)
	 * @access protected
	 */
	protected function OnDeleteMe(&$db){
		return true;
	}

	/**
	 * Funciton is called when DBEntry is added new to db
	 * @param DBManager $db
	 */
	protected function OnCreated($db)
	{
	}
	
	/**
	 * Funciton is called when DBEntry is updated in db
	 * @param DBManager $db
	 */
	protected function OnChanged($db)
	{
	}
	
	/**
	 * Erzeugt zwei Arrays: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param DBManager 	$db			Datenbankobjekt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool|int				Muss  im Erfolgsfall true oder eine negative Zahl als Fehlercode zurückgeben
	 * @access protected
	 * @abstract
	 */
	protected abstract function BuildDBArray(&$db, &$rowName, &$rowData);

	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param DBManager 	$db			Datenbankobjekt
	 * @param array		$data		Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool					Erfolg
	 * @access protected
	 * @abstract
	 */
	protected abstract function BuildFromDBArray(&$db, $data);

} // class DBEntry



/**
 * DBEntryFile
 *
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class DBEntryFile extends DBEntry {

	/**
	 * Original-Dateiname
	 * @var string
	 */
	protected $originalFileName = "";

	/**
	 * Dateiname im Filesystem
	 * @var string
	 */
	protected $fileName = "";

	/**
	 * Konstruktor
	 * @param DBManager $db				Datenbankobjekt
	 * @param string $originalFileName	Original Dateiname
	 * @param string $fileName			Dateiname
	 * @access public
	 */
	public function DBEntryFile(&$db, $dbConfig){
		$configTemp=DBEntryFile::GetDBConfig();
		$dbConfig->InsertRowsAt($configTemp->rowName, $configTemp->rowParam, $configTemp->rowIndex);
		parent::__construct($db, $dbConfig);
	}

	/**
	 * Gibt die Datenbankkonfigurtaion für diese Klasse zurück
	 * @return DBConfig	$db			Datenbankobjekt
	 * @access public
	 */
	static public function GetDBConfig(){
		$dbConfig=new DBConfig();
		$dbConfig->tableName="";
		$dbConfig->rowName=Array("originalFileName", "fileName");
		$dbConfig->rowParam=Array("VARCHAR(255)", "VARCHAR(255)");
		$dbConfig->rowIndex=Array();
		return $dbConfig;
	}

	/**
	 * Erzeugt zwei Array: in Einem die Spaltennamen und im Anderen der Spalteninhalt
	 * @param &array  	$rowName	Muss nach dem Aufruf die Spaltennamen enthalten
	 * @param &array  	$rowData	Muss nach dem Aufruf die Spaltendaten enthalten
	 * @return bool/int			Muss  im Erfolgsfall true oder eine negative Zahl als Fehlercode zurückgeben
	 * 							-1: Original Dateiname leer
	 *							-2: Dateiname leer
	 * @access protected
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData){
		if( trim($this->originalFileName)=="" )return -1;
		if( trim($this->fileName)=="" )return -2;
		$rowName[] = "originalFileName";
		$rowData[] = $this->originalFileName;
		$rowName[] = "fileName";
		$rowData[] = $this->fileName;
		return true;
	}

	/**
	 * Füllt die Variablen dieses Objektes mit den Daten aus der Datenbankl
	 * @param array  	$data		Assoziatives Array mit den Daten aus der Datenbank
	 * @return bool			Erfolg
	 * @access protected
	 */
	protected function BuildFromDBArray(&$db, $data){
		// Daten aus Array in Attribute kopieren
		$this->originalFileName = $data['originalFileName'];
		$this->fileName = $data['fileName'];
		return true;
	}

	/**
	 * Wird vor dem Löschen des Objekts aus der DB aufgerufen
	 * @param DBManager  $db			Datenbank Objekt
	 * @return bool					Erfolg (wenn false zurückgegeben wird, wird das Objekt nicht aus der DB gelöscht!)
	 * @access protected
	 */
	protected function OnDeleteMe(&$db){
		// Datei löschen...
		if( $this->GetFileName()!="" ){
			$fileToDelete=$this->GetCompleteFilePathAndName();
			if( file_exists($fileToDelete) && is_file($fileToDelete) ){
				@unlink($fileToDelete);
			}
		}
		return true;
	}

	/**
	 * Erzeugt aus der übergebenen Datei ein FileObject
	 * @param string $srcFile		Quelldatei
	 * @param bool  	$deleteSrcFile	Soll die Quelldatei nach erfolgreicher Verarbeitung gelöscht werden
	 * @return bool					Erfolg
	 * @access protected
	 */
	public function CreateFromLocalFile(&$db, $srcFile, $deleteSrcFile=false, $originalFileName=""){
		global $SHARED_FILE_SYSTEM_ROOT;
		// Wenn dieses Objekt bereits ein DB-Objekt ist, abbrechen
		if( $this->GetPKey()!=-1 )return false;
		if( !file_exists($srcFile) || !is_file($srcFile) )return false;
		$info=pathinfo($srcFile);
		if( $originalFileName=="" ){
			$info=pathinfo($srcFile);
			$originalFileName=$info["basename"];
		}
		$info=pathinfo($originalFileName);
		// Dateiname generieren
		$destFileName="";
		do{
			$destFileName=$this->GetNewFileName($info["extension"]);
		}while( file_exists($SHARED_FILE_SYSTEM_ROOT.$this->GetSubFileDirectory().$destFileName) );
		// Bild kopieren
		if( @copy($srcFile, $SHARED_FILE_SYSTEM_ROOT.$this->GetSubFileDirectory().$destFileName) ){
			// Dateinamen setzen
			$this->SetFileName($destFileName);
			$this->SetOriginalFileName($info["basename"]);
			// Speichern
			if( $this->Store($db)===true ){
				if( $deleteSrcFile )unlink($srcFile);
				return true;
			}
		}
		return false;
	}

	/**
	 * Setzt den original Dateiname
	 * @param int $originalFileName	Original Dateiname
	 * @return bool					Erfolg
	 * @access public
	 **/
	public function SetOriginalFileName($originalFileName){
		$this->originalFileName = $originalFileName;
		return true;
	}

	/**
	 * Gibt den original Dateiname zurück
	 * @return int					Original Dateiname
	 * @access public
	 **/
	public function GetOriginalFileName(){
		return $this->originalFileName;
	}

	/**
	 * Gibt die Dateierweiterung des Originaldateinamen zurück
	 * @return string				Dateierweiterung des Originaldateinamen
	 * @access public
	 **/
	public function GetOriginalFileNameExtension(){
		$path_parts = pathinfo($this->GetOriginalFileName());
		return $path_parts["extension"];
	}

	/**
	 * Gibt den Originaldateiname ohne Dateierweiterung zurück
	 * @return string				Originaldateiname ohne Dateierweiterung
	 * @access public
	 **/
	public function GetOriginalFileNameWithoutExtension(){
		$path_parts = pathinfo($this->GetOriginalFileName());
		return $path_parts["filename"];
	}

	/**
	 * Setzt den Dateinamen
	 * @param string $fileName		Dateiname
	 * @return bool					Erfolg
	 * @access protected
	 **/
	protected function SetFileName($fileName){
		$this->fileName = $fileName;
		return true;
	}

	/**
	 * Gibt den Dateinamen zurück
	 * @return string				Dateiname
	 * @access public
	 **/
	public function GetFileName(){
		return $this->fileName;
	}

	/**
	 * Gibt den Dateinamen inkl. Dateipfad zurück
	 * @return string				Dateiname inkl. Dateipfad
	 * @access public
	 **/
	public function GetCompleteFilePathAndName(){
		if( strpos($this->fileName, "/")!==false )return $this->fileName;
		global $SHARED_FILE_SYSTEM_ROOT;
		return $SHARED_FILE_SYSTEM_ROOT.$this->GetSubFileDirectory().$this->fileName;
	}

	/**
	 * Gibt den Dateinamen inkl. Dateipfad zurück
	 * @return string				Dateiname inkl. Dateipfad
	 * @access public
	 **/
	public function GetCompleteURL(){
		global $SHARED_HTTP_ROOT;
		return $SHARED_HTTP_ROOT.$this->GetSubFileDirectory().$this->fileName;
	}

	/**
	 * Gibt einen Dateinamen mit zufälligem Inhalt zurück
	 * @return string			Dateiname
	 * @access public
	 **/
	protected function GetNewFileName($extension){
		return get_class($this)."_".time()."_".rand(10000, 99999).".".$extension;
	}

	/**
	 * Unterverzeichnis, in welchem die Dateien abgelegt werden
	 * Der Dateipfad sollte OHNE '/' beginnen und MIT '/' enden
	 * @return string		Unterverzeichnis
	 * @access public
	 **/
	abstract public function GetSubFileDirectory();

}; // class DBEntryFile

?>