<?
/**
 * Der MySQLManager ist eine Zugriffsbibliothek für MySQL Datenbanken
 * Er stellt verschiedene Methoden zur Bearbeitung von Datensätzten bereit.
 * Benötigt Objekt vom Typ ErrorManager
 * @package  MySqlManager
 * @access   public
 * @author   Martin Walleczek <walle@daa-direkt.de>
 *           Stephan Walleczek <stephan@daa-direkt.de>
 * @since    PHP 4.0
 */

class MySqlManager extends DBManager 
{
	protected $debug = null;
	protected $Err = null;
	protected $errorText = "";
	protected $useUTF8 = true;
	protected $dbName = "";
	protected $dbUser = "";
	protected $dbPWD = "";
	protected $dbURL = "";
	protected $connID = null;
	protected $defaultRowType = "TINYTEXT";
	protected $mysqlWaitTimeout = 0;

	/**
	 * MySqlManager initialisieren und Verbindung zu DBMS herstellen.
	 * @param string $dbName Name der Datenbank die verbunden werden soll
	 * @param string $dbHost Datenbankhost, z.B. localhost oder 192.9.100.2
	 * @param string $dbUser Datenbankuser
	 * @param string $dbPwd Passwort des Datenbankusers
	 * @param object ErrorManager &$errorManager Referenz auf ErrorManager Objekt
	 * @param boolean $debug Sollen debug Informationen angezeigt werden? (Standard: false)
	 * @access public
	 */
	public function MySqlManager($dbName,$dbHost,$dbUser,$dbPwd,&$errorManager,$debug=false, $mysqlWaitTimeout=0)
	{
		//echo "$dbName,$dbHost,$dbUser,$dbPwd,&$errorManager";
		$this->debug = $debug;
		$this->dbName = $dbName;
		$this->dbUser = $dbUser;
		$this->dbPWD = $dbPwd;
		$this->dbURL = $dbHost;
		$this->errorText = "";
		$this->Err = $errorManager;
		$this->mysqlWaitTimeout = (int)$mysqlWaitTimeout;
		$this->connID = mysql_connect($this->dbURL, $this->dbUser, $this->dbPWD, true) or die ($this->Err->showError("MySqlManager","Can't connect to database, too many users! Please try again later!"));
		mysql_select_db ($this->dbName, $this->connID) or die ($this->Err->showError("MySqlManager::MySqlManager()",mySql_Error()));
		if ($this->useUTF8) mysql_query("set names 'utf8'");
		if ($this->mysqlWaitTimeout>0) mysql_query('SET SESSION wait_timeout = '.$this->mysqlWaitTimeout);
		return true;
	}
	
	/**
	 * Führt die übergebene SQL query aus und gibt bei Erfolg die Query-ID zurück
	 * @param string $query SQL-Query String
	 * @return ResourceID $query_id
	 * @access public
	 */
	public function Query($query)
	{
		$signature="MySqlManager::Query(string query)";
		if ($query=="") die ($this->Err->showError($signature,"Ungültige Parameter, $query ist leer."));
		if ($this->debug==true) echo "<strong>$signature:</strong>$query<br>";
		$query_id=@mysql_query ($query,$this->connID) or ($this->Err->showError($signature,mySql_Error($this->connID)));
			
		return $query_id;
	}

	/**
	 * Führt die übergebene SQL query aus und gibt bei Erfolg die Query-ID zurück. Als erstes Feld wird
	 * automatisch pkey (index,primary) angelegt.
	 * @param	array $tableName	Namen der neuen Tabelle
	 * @param	array $rowName		Spaltennamen der neuen Tabelle
	 * @param	string $rowParam 	Array mit den Parametern der einzelnen Spalten (Datentypen).
	 *								Ist Feld leer wird Standarddatentype (TINYTEXT) gesetzt.
	 * @return boolean true
	 * @access public
	 */
	public function CreateTable($tableName, $rowName, $rowParam, $primaryKey="pkey", $primaryKeyType="BIGINT")
	{
		$signature="MySqlManager::CreateTable(string tableName, array rowName, array rowParam)";
		$tableName=str_replace(" ","",$tableName);
		if ($tableName=="" || !is_array($rowName) || !is_array($rowParam)) die ($this->Err->showError($signature,"Ungültige Parameter"));
		if (count($rowName) != count($rowParam)) die ($this->Err->showError($signature,"rowName und rowParam haben unterschiedliche Länge!"));

		$query="CREATE TABLE $tableName (".$primaryKey." ".$primaryKeyType."  not null AUTO_INCREMENT ";
		for ($count=0;$count<count($rowName);$count++){
			$query.=", ".$rowName[$count]." ";
			if ($rowParam[$count]=="") $query.=$this->defaultRowType." ";
			else $query.=$rowParam[$count]." ";
			$query.="not null ";
		}
		$query.=$rowParam[$count].", PRIMARY KEY (".$primaryKey."))";
		if( $this->useUTF8 )$query.=" DEFAULT CHARACTER SET utf8";
		if ($this->debug==true)	echo "<strong>$signature:</strong>$query<br>";
		@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		return true;
	}

	/**
	 * Löscht eine Tabelle
	 * @param	string $tableName 	Name der zu löschenden Tabelle
	 * @return boolean true
	 * @access public
	 */
	public function DeleteTable($tableName)
	{
		$signature="MySqlManager::DeleteTable(string tableName, boolean fetchData)";
		if ($tableName=="") die ($this->Err->showError($signature,"Ungültige(r) Parameter"));

		$query="DROP TABLE $tableName";
		if ($this->TableExists($tableName))
		@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));

		return true;
	}

	/**
	 * Bennent eine Tabelle um
	 * @param	string $tableName 	Namen der Tabelle die umbenannt werden soll
	 * @param	string $newTableName Neuer Name der Tabelle
	 * @return boolean true
	 * @access public
	 */
	public function RenameTable($tableName,$newTableName)
	{
		$signature="MySqlManager::RenameTable(string tableName, string newTableName)";
		if ($tableName=="" || $newTableName=="") die ($this->Err->showError($signature,"Ungültige(r) Parameter"));

		$query="ALTER TABLE $tableName RENAME $newTableName";
		if ($this->TableExists($tableName))
		@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));

		return true;
	}

	/**
	 * Prüft ob Tabelle tableName vorhanden ist, wenn ja gibt sie true zurück ansonsten false
	 * @param	String $tableName	Name der Tabelle die geprüft werden soll
	 * @return boolean
	 * @access public
	 */
	public function TableExists($tableName)
	{
		$signature="MySqlManager::TableExists(string tableName)";
		if ($tableName=="") die ($this->Err->showError($signature,"Ungültiger Parameter, tableName ist leer"));

		$query="SHOW TABLES LIKE '$tableName'";
		$result =@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature, mySql_Error($this->connID)));
		if (mysql_num_rows($result)>0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Fügt in die Tabelle tableName in die Spalten rowName die Daten aus rowData ein.
	 * Liefert bei Erfolg den pkey zurück
	 * @param	string $tableName 	Tabelle in die eingefügt werden soll
	 * @param	array $rowName 	Spaltennamen in die eingefügt werden soll
	 * @param	array $rowData 	Daten die in die genannten Spalten eingefügt werden sollen
	 * @return int pkey
	 * @access public
	 */
	public function Insert($tableName,$rowName,$rowData,$allowInsertToIdent=false)
	{
		$signature="MySqlManager::Insert(string tableName, array rowName, array rowData)";
		if ($tableName=="" || !is_array($rowName) || !is_array($rowData)) die ($this->Err->showError($signature,"Ungültige Parameter"));
		if (count($rowName) != count($rowData)) die ($this->Err->showError($signature,"rowName und rowData haben unterschiedliche Länge!"));

		$query="Insert INTO $tableName ( ";
		for ($count=0;$count<count($rowName);$count++)
		$query.=" ".$rowName[$count].", ";
		$query=substr($query,0,(strlen($query)-2));
		$query.=") VALUES (";
		for ($count=0;$count<count($rowData);$count++)
		$query.=" '".MySqlManager::mysql_convert_string_to_db($rowData[$count])."', ";
		$query=substr($query,0,(strlen($query)-2));
		$query.=")";
		if ($this->debug==true) echo "<strong>$signature:</strong>$query<br>";
		@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		return mysql_Insert_id($this->connID);
	}

	/**
	 * Ändert den Datensatz pkey in Tabelle tableName. Nicht genannte Spalten bleiben unberührt.
	 * @param	string $tableName 	Tabelle in die eingefügt werden soll
	 * @param	array $rowName 	Spaltennamen deren Inhalt geändert werden solle
	 * @param	array $rowData 	Neue Inhalte der zu ändernden Spalte
	 * @param	int $pkey 		Pkey des zu ändernden Datensatztes
	 * @return boolean
	 * @access public
	 */
	public function UpdateByPkey($tableName,$rowName,$rowData,$pkey)
	{
		$signature="MySqlManager::UpdateByPkey(string tableName, array rowName, array rowData, int pkey)";
		if ($tableName=="" || $pkey=="" || !is_array($rowName) || !is_array($rowData)) die ($this->Err->showError($signature,"Ungültige Parameter"));
		if (count($rowName) != count($rowData)) die ($this->Err->showError($signature,"rowName und rowData haben unterschiedliche Länge!"));

		$query="Update $tableName SET ";
		for ($count=0;$count<count($rowName);$count++){
			$query.=" ".$rowName[$count]."='".MySqlManager::mysql_convert_string_to_db($rowData[$count])."', ";
		}
		$query=substr($query,0,(strlen($query)-2));
		$query.=" WHERE pkey=$pkey";

		if ($this->debug==true) echo "<strong>$signature:</strong>$query<br>";
		@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		return true;
	}

	/**
	 * Ändert den Datensätze die mit $where in Tabelle $tableName selectiert werden. Nicht genannte Spalten bleiben unberührt.
	 * @param	string 	$tableName 		Tabelle in die eingefügt werden soll
	 * @param	array 	$rowName 		Spaltennamen deren Inhalt geändert werden sollen
	 * @param	array 	$rowData 		Neue Inhalte der zu ändernden Spalten
	 * @param	string	$where 			TSQL-Bedingung der zu ändernden Datensätze
	 * @return boolean
	 * @access public
	 */
	public function Update($tableName,$rowName,$rowData,$where)
	{
		if( is_numeric($where) && $where==(int)$where )return $this->UpdateByPkey($tableName,$rowName,$rowData,(int)$where);
		$signature="MySqlManager::Update(string tableName, array rowName, array rowData, string where)";
		if ($tableName=="" || $where=="" || !is_array($rowName) || !is_array($rowData)) die ($this->Err->ShowError($signature,"Ungültige Parameter"));
		if (count($rowName) != count($rowData)) die ($this->Err->ShowError($signature,"rowName und rowData haben unterschiedliche Länge!"));

		$query="Update $tableName SET ";
		for ($count=0;$count<count($rowName);$count++){
			$query.=" ".$rowName[$count]."='".MySqlManager::mysql_convert_string_to_db($rowData[$count])."', ";
		}
		$query=substr($query,0,(strlen($query)-2));
		$query.=" WHERE $where";
		if ($this->debug==true) $this->Err->ShowDebugInfo($signature,$query);
		@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		return true;
	}

	/**
	 * Löscht Datensatz pkey in Tabelle tableName
	 * @param	string $tableName 	Tabelle aus der gelöscht werden soll
	 * @param	int $pkey 			Pkey des zu löschenden Datensatztes
	 * @return boolean
	 * @access public
	 */
	public function DeleteByPkey($tableName,$pkey)
	{
		$signature="MySqlManager::DeleteByPkey(string tableName, int $pkey)";
		if ($tableName=="" || $pkey=="") die ($this->Err->showError($signature,"Ungültige Parameter"));

		$query="DELETE FROM $tableName WHERE pkey='$pkey'";
		if ($this->debug==true) echo "<strong>$signature:</strong>$query<br>";
		@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		return true;
	}

	/**
	 * Löscht Datensatz pkey in Tabelle tableName
	 * @param	string $tableName 	Tabelle aus der gelöscht werden soll
	 * @param	int $pkey 		Pkey des zu löschenden Datensatztes
	 * @return boolean
	 * @access public
	 */
	public function Delete($tableName, $where)
	{
		$signature="MySqlManager::Delete(string tableName, string where)";
		if ($tableName=="" || trim($where)=="") die ($this->Err->showError($signature,"Ungültige Parameter"));

		$query="DELETE FROM $tableName WHERE ".$where;
		if ($this->debug==true) echo "<strong>$signature:</strong>$query<br>";
		@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		return true;
	}

	/**
	 * Führt die Übergebene SQL query aus und gibt die Ergebnisse in einem assoziativen
	 * Array zurück: Array[index]["row1Name"]["row2name"]...
	 * To Do: selectOnline auf WI3 anpassen
	 * @param	string $query 		SQL Abfrage
	 * @param	boolean $selectOnline	Selektiert nur Datensätzte die nach dem WI2.x Tabellenformat online sind
	 * @return array
	 * @access public
	 */
	public function SelectAssoc($query,$selectOnline=false)
	{
		$signature="MySqlManager::SelectAssoc(string query)";
		if ($query=="") die ($this->Err->showError($signature,"Ungültige Parameter, $query ist leer."));
		if ($selectOnline==true && strpos($query, 'SELECT *') === false)
		$query=str_replace(" FROM",",onlineFrom,onlineUntil,WF_STAT FROM",$query);

		if ($this->debug==true) echo "<strong>$signature:</strong>$query<br>";
		$query_id=@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));

		$return_data=Array();
		$index=0;
		while ($data=mysql_fetch_assoc($query_id)){
			array_walk ($data, 'MySqlManager::strip_slashes');
			$return_data[$index]=$data;
			$index++;
		}


		if ($selectOnline==true){
			$tmp=array();
			for ($lauf=0; $lauf<count($return_data);$lauf++){
				if ($this->UpToDate(time(),$return_data[$lauf]["onlineFrom"],$return_data[$lauf]["onlineUntil"]) && ($return_data[$lauf]["WF_STAT"]<=0 || $return_data[$lauf]["WF_STAT"]=="")){
					$tmp[]=$return_data[$lauf];
				}
			}
			$return_data=$tmp;
		}


		return $return_data;
	}

	/**
	 * Führt die übergebene SQL query aus und gibt die serialisierten, mit gzcompress gepackte Ergebnisse in einem assoziativen
	 * Array zurück Array[index]["row1Name"]["row2name"]...
	 * @param	string $query 		SQL Abfrage
	 * @return array
	 * @access public
	 */
	public function SelectAssocGZIP($query)
	{
		$signature="MySqlManager::SelectAssoc(string query)";
		if ($query=="") die ($this->Err->showError($signature,"Ungültige Parameter, $query ist leer."));
		if ($this->debug==true) echo "<strong>$signature:</strong>$query<br>";
		$query_id=@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));

		$return_data=Array();
		$index=0;
		while ($data=mysql_fetch_assoc($query_id)){
			array_walk ($data, 'MySqlManager::strip_slashes');
			$return_data[$index]=gzcompress(serialize($data),9);
			$index++;
		}

		return $return_data;
	}

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
	public function SelectHtmlAssoc($query,$selectOnline=false)
	{
		$signature="MySqlManager::SelectAssoc(string query)";
		if ($query=="") die ($this->Err->showError($signature,"Ungültige Parameter, $query ist leer."));
		if ($selectOnline==true)
		$query=str_replace(" FROM",",onlineFrom,onlineUntil,WF_STAT FROM",$query);

		if ($this->debug==true) echo "<strong>$signature:</strong>$query<br>";
		$query_id=@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));

		$return_data=Array();
		$index=0;
		while ($data=mysql_fetch_assoc($query_id)){
			array_walk ($data, 'MySqlManager::MySqlManagerConvertToHtml');
			array_walk ($data, 'MySqlManager::mysql_convert_string_from_db');
			$return_data[$index]=$data;
			$index++;
		}

		if ($selectOnline==true){
			$tmp=array();
			for ($lauf=0; $lauf<count($return_data);$lauf++){
				if ($this->UpToDate(time(),$return_data[$lauf]["onlineFrom"],$return_data[$lauf]["onlineUntil"]) && ($return_data[$lauf]["WF_STAT"]<=0 || $return_data[$lauf]["WF_STAT"]=="")){
					$tmp[]=$return_data[$lauf];
				}
			}
			$return_data=$tmp;
		}

		return $return_data;
	}

	/**
	 * Fährt die übergebene SQL query aus und gibt die Ergebnisse in einem
	 * Array zurück: Array[index][0][1]...
	 * @param	string $query 		SQL Abfrage
	 * @return array
	 * @access public
	 */
	public function SelectArray($query)
	{
		return $this->Select($query);
	}

	/**
	 * Fährt die übergebene SQL query aus und gibt die Ergebnisse in einem
	 * Array zurück: Array[index][0][1]...
	 * @param	string $query 		SQL Abfrage
	 * @return array
	 * @access public
	 */
	public function Select($query)
	{
		$signature="MySqlManager::SelectArray(string query)";
		if ($query=="") die ($this->Err->showError($signature,"Ungültige Parameter, $query ist leer."));
		if ($this->debug==true) echo "<strong>$signature:</strong>$query<br>";
		$query_id=mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		$return_data=Array();
		$index=0;
		while ($data=mysql_fetch_array($query_id)){
			while (list ($key, $val) = each ($data)) {
				$val=stripslashes($val);
			}
			$return_data[$index]=$data;
			$index++;
		}
		if (count($return_data)==0)
		$return_data=false;
		return $return_data;
	}

	/**
	 * Führt die übergebene SQL query aus und gibt die Ergebnisse für HTMl Ausgabe in einem
	 * Array zurück Array[index][0][1]...
	 * @param	string $query 		SQL Abfrage
	 * @return array
	 * @access public
	 */
	public function SelectHtmlArray($query)
	{
		$signature="MySqlManager::SelectArray(string query)";
		if ($query=="") die ($this->Err->showError($signature,"Ungültige Parameter, $query ist leer."));
		if ($this->debug==true) echo "<strong>$signature:</strong>$query<br>";
		$query_id=@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));

		$return_data=Array();
		$index=0;
		while ($data=mysql_fetch_array($query_id)){
			array_walk ($data, 'MySqlManager::MySqlManagerConvertToHtml');
			$return_data[$index]=$data;
			$index++;
		}
		if (count($return_data)==0)
		$return_data=false;

		return $return_data;
	}

	/**
	 * Gibt die Anzahl der in tableName Vorhandenen Einträge zurück
	 * @param	string $tableName 		Tabellenname
	 * @return int
	 * @access public
	 */
	public function GetTableEntries($tableName)
	{
		$signature="MySqlManager::GetTableEntries(string tableName)";
		if ($tableName=="") die ($this->Err->showError($signature,"Ungültige Parameter, $tableName ist leer."));
		$query="SELECT pkey FROM $tableName";
		$query_id=@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));

		return mysql_affected_rows($this->connID);
	}

	/**
	 * Gibt die Namen der Tabellenspalten in einem Array zurück: Array[index]
	 * @param	string $tableName 		Tabellenname
	 * @return array
	 * @access public
	 */
	public function GetTableRows($tableName)
	{
		$signature="MySqlManager::GetTableRows(string tableName)";
		if ($tableName=="") die ($this->Err->showError($signature,"Ungültige Parameter, $tableName ist leer."));

		$fields = @mysql_list_fields($this->dbName, $tableName, $this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		$columns = @mysql_num_fields($fields) or die ($this->Err->showError($signature,mySql_Error($this->connID)));

		$names= Array();
		for ($i = 0; $i < $columns; $i++) {
			$names[$i]=mysql_field_name($fields, $i);
		}
		return $names;
	}

	/**
	 * Gibt den Namen, den Datentyp und den Index der Spalte zurück:
	 * AssocArray["name"]["type"]["flags"]["index"]
	 * @param	string $tableName 		Tabellenname
	 * @param	string $rowName 		Spaltenname
	 * @return array
	 * @access public
	 */
	public function GetRowInfo($tableName,$rowName)
	{
		$signature="MySqlManager::GetRowInfo(string tableName, string rowName)";
		if ($tableName=="" || $rowName=="") die ($this->Err->showError($signature,"Ungültige(r) Parameter"));

		$query="SHOW FIELDS FROM $tableName LIKE '$rowName'";
		$queryID=mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		$info=mysql_fetch_array($queryID,$this->connID);
		$data["name"]=$info["Field"];
		$data["type"]=$info["Type"];
		$data["flags"]=$info["Null"];
		$fields = @mysql_list_fields($this->dbName, $tableName, $this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		$columns = @mysql_num_fields($fields) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		for ($i = 0; $i < $columns; $i++) {
			if(mysql_field_name($fields, $i)==$rowName){
				$data["index"]=$i;
			}
		}
		return $data;
	}

	/**
	 * Prüft ob die Spalte rowName vorhanden ist (true/false)
	 * @param	string $tableName 		Tabellenname
	 * @param	string $rowName 		Spaltenname
	 * @return boolean
	 * @access public
	 */
	public function TableRowExists($tableName,$rowName)
	{
		$signature="MySqlManager::TableRowExists(string tableName, string rowName)";
		if ($tableName=="" || $rowName=="") die ($this->Err->showError($signature,"Ungültige(r) Parameter."));

		$rows=$this->GetTableRows($tableName);
		if (in_array($rowName,$rows))
		return true;
		else
		return false;
	}

	/**
	 * Fügt eine neue Spalte in Tabelle tableName ein
	 * @param	string $tableName 		Tabellenname
	 * @param	string $rowName 		Spaltenname
	 * @param	string $rowType 		Parameter der neuen Spalte (Datentyp etc.)
	 * @return boolean
	 * @access public
	 */
	public function InsertRow($tableName,$rowName,$rowType)
	{
		$signature="MySqlManager::InsertRow(string tableName, string rowName, string rowType)";
		if ($tableName=="" || $rowName=="") die ($this->Err->showError($signature,"Ungültige(r) Parameter."));
		if ($rowType=="") $rowType=$this->defaultRowType;

		if(!$this->TableRowExists($tableName,$rowName)){
			$query="ALTER TABLE $tableName ADD $rowName $rowType not null";
			@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		}
		return true;
	}

	/**
	 * Fügt eine neue Spalte in Tabelle tableName nach der Spalte $afterRow ein
	 * @param	string $tableName 		Tabellenname
	 * @param	string $rowName 		Spaltenname
	 * @param	string $rowType 		Parameter der neuen Spalte (Datentyp etc.)
	 * @param	string $afterRow 		Name der Spalte nach der eingefügt soll
	 * @return boolean
	 * @access public
	 */
	public function InsertRowAfter($tableName,$rowName,$rowType, $afterRow)
	{
		$signature="MySqlManager::InsertRowAfter(string tableName, string rowName, string rowType, string afterRow)";
		if ($tableName=="" || $rowName=="" || $afterRow=="") die ($this->Err->showError($signature,"Ungültige(r) Parameter."));
		if ($rowType=="") $rowType=$this->defaultRowType;

		if($this->TableRowExists($tableName,$afterRow)){
			$query="ALTER TABLE $tableName ADD $rowName $rowType not null AFTER $afterRow ";
			@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		}
		return true;
	}

	/**
	 * Löscht die Spalte rowName in der Tabelle tableName
	 * @param	string $tableName 		Tabellenname
	 * @param	string $rowName 		Name der Spalte die gelöscht werden soll
	 * @return boolean
	 * @access public
	 */
	public function DeleteRow($tableName,$rowName)
	{
		$signature="MySqlManager::InsertRow(string tableName, string rowName)";
		if ($tableName=="" || $rowName=="") die ($this->Err->showError($signature,"Ungültige(r) Parameter."));

		if($this->TableRowExists($tableName,$rowName)){
			$query="ALTER TABLE $tableName DROP $rowName";
			@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
			return true;
		}
		else return false;
	}

	/**
	 * Ändert die Spalte in newRowName und newRowType
	 * @param	string $tableName 		Tabellenname
	 * @param	string $rowName 		Name der Spalte die gelöscht werden soll
	 * @param	string $newRowName 		Neuer Spaltenname
	 * @param	string $newRowType 		Neue Spaltenparamter Datentyp etc.)
	 * @return boolean
	 * @access public
	 */
	public function ChangeRow($tableName,$rowName,$newRowName,$newRowType)
	{
		$signature="MySqlManager::ChangeRow(string tableName, string rowName, string newRowName, string newRowType)";
		if ($tableName=="" || $rowName=="") die ($this->Err->showError($signature,"Ungültige(r) Parameter."));

		if($this->TableRowExists($tableName,$rowName)){
			if ($newRowName=="") $newRowName=$rowName;
			// bestehender Datentyp der Spalte auslesen
			if ($newRowType==""){
				$old_row_info=$this->GetRowInfo($tableName,$rowName);
				$newRowType=$old_row_info["type"]." ".$old_row_info["flags"];
			}

			$query="ALTER TABLE $tableName CHANGE $rowName $newRowName $newRowType";
			if ($this->debug==true) echo "<strong>$signature:</strong>$query<br>";
			@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
			return true;
		}
		else die ($this->Err->showError($signature,"Spalte '$rowName' exisitiert nicht!"));
	}

	/**
	 * Prüft ob pkey in Tabelle tableName bereits vorhanden ist (true/false)
	 * @param	string 	$tableName 		Tabellenname
	 * @param	int 	$pkey 			Pkey nach dem gesucht werden soll
	 * @return 	boolean
	 * @access 	public
	 */
	public function PkeyExists($tableName, $pkey)
	{
		$signature="MySqlManager::PkeyExists(string tableName, String $pkey)";
		if ($tableName=="" || $pkey=="") die ($this->Err->showError($signature,"Ungültige(r) Parameter"));
		$query="SELECT pkey FROM $tableName WHERE pkey=$pkey";
		$query_id=@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		if (mysql_affected_rows($this->connID)==1)
		return true;
		else
		return false;
	}

	/**
	 * Erzeugt einen Index
	 * @param string $tableName 	Tabellenname
	 * @param string $rowName 	Spaltenname or Array of column names defining together one index
	 * @param bool $unique 				Soll der Index unique sein?
	 * @access public
	 */
	public function CreateIndex($tableName, $rowName, $unique=false)
	{
		if (is_array($rowName))
		{
			$indexPrefix = implode("_", $rowName);
		}
		else
		{
			$indexPrefix = $rowName;
		}

		$indexName="index_".$tableName."_".$indexPrefix;
		// Prüfen, dass die Länge des Index-Namen nicht 64 überschrietet
		if( strlen($indexName)>64 ){
			$indexName="i_".$tableName."_".$indexPrefix;
			if( strlen($indexName)>64 ){
				// Zufällige Zeichenkombination erzeugen
				$idCharsetPool = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
				$randomID = "";
				$randomIDLength = 5;
				for($a=0; $a<$randomIDLength; $a++){
					$randomID .= substr($idCharsetPool, rand(0, strlen($idCharsetPool)), 1);
				}
				$indexName=substr($indexName, 0, 64-$randomIDLength-1)."_".$randomID;
			}
		}

		$query = "CREATE ".($unique ? "UNIQUE" : "")." INDEX ";
		if( !is_array($rowName) )
		{
			$query .= $indexName." ON ".$tableName." (".$rowName.")";
		}
		else
		{
			$query .= $indexName." ON ".$tableName." (".implode(", ", $rowName).")";
		}
		//echo $query;
		$this->Query($query);
	}

	/**
	 * Hilfsfunktion zum entfernenen von Sonderzeichen aus Datenbankinhalten
	 * @param 	string 	$string 	String
	 * @return 	string  			DBString
	 * @access 	public
	 */
	public function ConvertStringToDBString($string)
	{
		return $this->mysql_convert_string_to_db($string);
	}

	/**
	 * Hilfsfunktion für MySqlManager: Entfernt Slashes aus String
	 * Wird für ein Array über array_walk() o.Ä. aufgerufen
	 * @param	value 	$item1 		Inhalt an der Stelle Key im Array
	 * @param	key 	$pkey 		Key des Arrays
	 * @return 	void
	 * @access 	private
	 */
	public static function strip_slashes (&$item1, $key)
	{
		$item1 = str_replace("\\","",$item1);
	}

	/**
	 * Hilfsfunktion für MySqlManager: Wandelt Unix & Windows Steuerzeichen
	 * in HTML Tags um. Wird für ein Array über array_walk() o.Ä. aufgerufen.
	 * Wandelt beispielsweise "~" aus WI in HTML-Aufzählung um
	 * @param	value 	$item1 		Inhalt an der Stelle Key im Array
	 * @param	key 	$pkey 		Key des Arrays
	 * @return 	void
	 * @access 	private
	 */
	public static function MySqlManagerConvertToHtml (&$item1, $key)
	{
		$matches=array();
		$lists=array();
		global $UID;
		// Systemfelder sollen aus Performancegründen nicht geparsed werden
		$dontParseKeys=array("onlineFrom","language","onlineUntil","WF_STAT");
		$dontParseValues=array("on");
			
			
		if (!in_array($key,$dontParseKeys) && !in_array($item1,$dontParseValues) && $item1!="" && !is_numeric($item1)){
			//echo ".....".$item1." -->";
			//echo "Int?:".is_numeric($item1);
			//echo "<br>";
			$item1=preg_replace("/\r\n|\n\r|\n|\r/", "\n", $item1);



			//print_r($item1);
			preg_match_all("/~([^|]*)/",$item1,$lists);
			//echo "<br><strong>Starte...</strong><br><strong>Lists:</strong>";
			//echo "<br>";

			for ($lauf1=0;$lauf1<count($lists[0]);$lauf1++){
				$lists[0][$lauf1]=$lists[0][$lauf1]."\n";
				$completeOld[]=$lists[0][$lauf1];
				//echo "Lists[$lauf1]:".$lists[0][$lauf1]."<br>";
				$temp=preg_split("/~/",$lists[0][$lauf1]);
				//echo "<strong>tmp:</strong>"; print_r($temp); echo "<br>";
				$temp=count($temp);
				//echo "<strong>Aufzählungspunkte:</strong>$temp<br>";
				//if ($temp==2){ // Nur ein Listenpunkt
				//$matches[1]=split("~",$lists[0][$lauf1]);
				//preg_match_all("/~(([^~]|[^\n])*)/",$lists[0][$lauf1],$matches);
				//echo "<strong>Nur ein Listenpunkt!</strong><br>";
				//}
				//else{ // n- Listenpunkte
				//preg_match_all("/~([^\n]*)/",$lists[0][$lauf1],$matches);
				$matches[1]=preg_split("/~/",$lists[0][$lauf1]);
				//echo "<strong>".count($matches[1])." Listenpunkte!</strong><br>";
				//}
				if (count($matches[1])==2){
					//echo "<strong>Bearbeite einen Listenpunkt</strong><br>";
					$new="<ul><li>".$matches[1][1]."</li></ul>";
					$completeNew[]=$new;
					//$matches[1][1]=substr($matches[1][1],0,strlen($matches[1][1])-1);
					//$item1=str_replace($matches[1][1],$new,$item1);
					//echo "<strong>Listenpunkt jetzt: '$item1'</strong><br>";
					$completeNewEntries[]["anzahl"]=1;
					$completeNewEntries[count($completeNewEntries)-1]["eintrag"]=count($completeNewEntries)-1;
				}
				else
				{
					$new="";
					for ($lauf=0;$lauf<count($matches[1]);$lauf++){
						//echo "<strong>Bearbeite Listenpunkt '".$matches[1][$lauf]."'($lauf)</strong><br>";
						if ($matches[1][$lauf]!="")
						{
							if ($lauf==1){
								//echo "Erster Punkt<br>";
								$new="<ul><li>".$matches[1][$lauf]."</li>";
							}
							elseif (count($matches[1])-$lauf==1){
								//echo "Letzter Punkt<br>";
								$new.="<li>".$matches[1][$lauf]."</li></ul>";
							}
							else
							$new.="<li>".$matches[1][$lauf]."</li>";
						}
						//echo $lauf."--'".$matches[1][$lauf]."' replace with:'".$new."'<br>";
						//$item1 = str_replace("~".$matches[1][$lauf],$new,$item1);
						//echo htmlspecialchars($item1)."<br>";

					}
					$completeNewEntries[]["anzahl"]=$lauf-1;
					$completeNewEntries[count($completeNewEntries)-1]["eintrag"]=count($completeNewEntries)-1;
					$completeNew[]=$new;

				}
			}

			for ($lauf=0;$lauf<count($completeNew);$lauf++)
			{

				//echo "New: ".htmlspecialchars($completeNew[$lauf])."<br>";
				//echo "Old: ".htmlspecialchars($completeOld[$lauf])."<br>";
				//$pattern="/".trim($completeOld[$lauf])."/i";
				//$lists[0][$lauf] = preg_replace($pattern,$completeNew[$lauf],trim($lists[0][$lauf]));
				$lists[0][$lauf] = str_replace(trim($completeOld[$lauf]), $completeNew[$lauf], trim($lists[0][$lauf]));
				//echo "Ergebnis:<br>".trim($lists[0][$lauf])."<br><br>";
				//echo "lists[0][$lauf]: ".htmlspecialchars($lists[0][$lauf])."<br>";
			}

			for ($lauf=0;$lauf<count($lists[0]);$lauf++)
			{
				// Jeden Ausrduck vom Anfang des Strings item1 an suchen und ersetzten
				//echo strpos ($item1, $lists[0][$lauf]);
				//$pattern="/".trim($completeOld[$lauf])."/i";
				//$item1 = preg_replace($pattern,$lists[0][$lauf],trim($item1));
				$item1 = str_replace(trim($completeOld[$lauf]),$lists[0][$lauf],trim($item1));
				//echo "lists[0][$lauf]: ".htmlspecialchars($lists[0][$lauf])."<br>";
			}

			//echo $item1;
			$item1 = str_replace("|","\n\n",$item1);
			$item1 = str_replace("~","",$item1);
			$item1 = str_replace("\n","<br>",$item1);
			$item1 = str_replace("\\","",$item1);
			$item1 = str_replace("</ul><br><br>","</ul>",$item1);
			$item1 = str_replace("<br><ul>","<ul>",$item1);
			$item1 = str_replace("<br><br></li>","</li>",$item1);
			$item1 = str_replace("<br></li>","</li>",$item1);
			$item1 = str_replace("\$UID",$UID,$item1);
			$item1 = str_replace("</ul> <br><br>","</ul>",$item1);
			$item1 = str_replace("</ul>  <br><br>","</ul>",$item1);
			$item1=str_replace("http://../","../",$item1);
			$item1=str_replace("intern://","",$item1);
			$item1=str_replace("<ul><br><li>","<ul><li>",$item1);
			$item1=str_replace("?UID","?UID=".$UID,$item1);
			$item1=str_replace("&UID","&UID=".$UID,$item1);
			// <br> am Ende entfernen
			
			// $item1 = preg_replace("/<ol>([^<]*)(<br \/>|<br>)([^<]*)<li>/Uim", "<ol>$1 $3<li>", $item1); // Breaks zwischen <ol> und <li> entfernen
			// $item1 = preg_replace("/<ul>([^<]*)(<br \/>|<br>)([^<]*)<li>/Uim", "<ul>$1 $3<li>", $item1); // Breaks zwischen <ul> und <li> entfernen
			// $item1 = preg_replace("/<\/li>([^<]*)(<br \/>|<br>)([^<]*)<li>/Uim", "</li>$1 $3<li>", $item1); // Breaks zwischen </li> und <li> entfernen
			// $item1 = preg_replace("/<\/li>([^<]*)(<br \/>|<br>)([^<]*)<\/ul>/Uim", "</li>$1 $3</ul>", $item1); // Breaks zwischen </li> und </ul> entfernen
			// $item1 = preg_replace("/<\/li>([^<]*)(<br \/>|<br>)([^<]*)<\/ol>/Uim", "</li>$1 $3</ol>", $item1); // Breaks zwischen </li> und </ol> entfernen
		}
	}

	/**
	 * Hilfsfunktion für MySqlManager:: Sonderzeichen aus Datenbankinhalten entfernen
	 * @param	string 		$string 	String der umgewandelt werden soll
	 * @return 	string
	 * @access 	private
	 */
	public static function mysql_convert_string_to_db($string)
	{
		$string=str_replace("&amp;","&",$string);
		$string=str_replace("&#8211;","-",$string);
		$string=str_replace("&#8217;","'",$string);
		$string=str_replace("&#8222;","'",$string);
		$string=str_replace("&#8220;","'",$string);
		$string=str_replace("&#8218;","'",$string);
		$string=str_replace("&# ;","'",$string);
		$string=str_replace("&nbsp;"," ",$string);
		$string=mysql_real_escape_string($string);
		return $string;
	}

	/**
	 * Hilfsfunktion für MySqlManager:: Sonderzeichen aus Datenbankinhalten entfernen
	 * @param	string 		$string 	String der umgewandelt werden soll
	 * @return 	string
	 * @access private
	 */
	public static function mysql_convert_string_from_db(&$item1, $key)
	{
		global $UID;
		$item1=str_replace("%UID%","UID=".$UID,$item1);
		$item1=str_replace("intern://","",$item1);
	}
	
}
?>
