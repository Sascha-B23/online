<?
/**
 * Der MySQLManager ist eine Zugriffsbibliothek für MySQLi Datenbanken
 * Er stellt verschiedene Methoden zur Bearbeitung von Datensätzten bereit.
 * Benötigt Objekt vom Typ ErrorManager
 * @package  MySqlIManager
 * @access   public
 * @author   Martin Walleczek <walle@daa-direkt.de>
 *           Stephan Walleczek <stephan@daa-direkt.de>
 * @since    PHP 4.0
 */

class MySqlIManager extends DBManager 
{
	protected $debug = null;
	protected $Err = null;
	protected $errorText = "";
	protected $useUTF8 = true;

	protected $defaultRowType = "TINYTEXT";
	
	protected $dbURL = "";
	protected $dbName = "";
	protected $dbUser = "";
	protected $dbPWD = "";
	protected $dbPort = "";
	protected $dbSocket = "";
	
	/*@var mysqli*/
	protected $m_MySqlI = null;
	
	/**
	 * MySqlIManager initialisieren und Verbindung zu DBMS herstellen.
	 * @param ErrorManager &$errorManager Referenz auf ErrorManager Objekt
	 * @param String $dbHost Datenbankhost, z.B. localhost oder 192.9.100.2
	 * @param String $dbName Name der Datenbank die verbunden werden soll
	 * @param String $dbUser Datenbankuser
	 * @param String $dbPwd Passwort des Datenbankusers
	 * @param String $dbPort Port der Datenbank, defaults to "" (will be set to ini_get("mysqli.default_port") if empty)
	 * @param String $dbSocket Port der Datenbank, defaults to "" (will be set to ini_get("mysqli.default_socket") if empty)
	 * @param boolean $debug Sollen debug Informationen angezeigt werden? (Standard: false)
	 * @access public
	 */
	public function __construct(&$errorManager, $dbHost, $dbName, $dbUser, $dbPwd, $dbPort="", $dbSocket="", $debug=false)
	{
		if ($dbPort == "") $dbPort = ini_get("mysqli.default_port");
		if ($dbSocket == "") $dbSocket = ini_get("mysqli.default_socket");
		//echo "$dbName,$dbHost,$dbUser,$dbPwd,&$errorManager";
		$this->dbURL = $dbHost;
		$this->dbName = $dbName;
		$this->dbUser = $dbUser;
		$this->dbPWD = $dbPwd;
		$this->dbPort = $dbPort;
		$this->dbSocket = $dbSocket;
		
		$this->debug = $debug;
		
		$this->Err = $errorManager;
		$this->errorText = "";
		
		$this->m_MySqlI = new mysqli($this->dbURL, $this->dbUser, $this->dbPWD, $this->dbName, $this->dbPort, $this->dbSocket) or $this->ShowError("MySqlIManager","Can't connect to database, too many users! Please try again later!");
		$this->m_MySqlI->select_db($this->dbName);
		if ($this->useUTF8) $this->Query("set names 'utf8'");
		return true;
	}
	
	public function __destruct() 
	{
		//if ($this->m_MySqlI != null) $this->m_MySqlI->close();
		//$this->m_MySqlI = null;
		//$this->Err = null;
	}

		/**
	 * Displays a mysqli error
	 * @param String $signature
	 */
	private function ShowMySqlIError($signature)
	{
		$this->ShowError($signature, $this->m_MySqlI->error." (#".$this->m_MySqlI->errno.")");
	}
	
	/**
	 * Displays the debug message
	 * @param type $signature
	 * @param type $query
	 */
	private function ShowDebugMessage($signature, $query)
	{
		echo "<strong>$signature:</strong> $query<br />";	
	}
	
	/**
	 * Displays a error
	 * @param String $signature
	 * @param String $text
	 */
	private function ShowError($signature, $text)
	{
		die($this->Err->showError($signature, $text));
	}
	
	/**
	 * F�hrt die �bergebene SQL query aus und gibt bei Erfolg ein mysqli_result Objekt zur�ck
	 * @param string $query SQL-Query String
	 * @return mysqli_result | false
	 */
	public function Query($query)
	{
		$signature="MySqlIManager::Query(string query)";
		if ($query=="") $this->ShowError($signature, "Ung�ltige Parameter, $query ist leer.");
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		$query_id = @$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		return $query_id;
	}

	/**
	 * Erstellt eine Tabelle
	 * @param String $tableName		Namen der neuen Tabelle
	 * @param Array $rowName		Spaltennamen der neuen Tabelle
	 * @param Array $rowParam		Array mit den Parametern der einzelnen Spalten (Datentypen). st Feld leer wird Standarddatentype (TINYTEXT) gesetzt.
	 * @return boolean true
	 */
	public function CreateTable($tableName, $rowName, $rowParam, $primaryKey="pkey", $primaryKeyType="BIGINT")
	{
		$signature="MySqlIManager::CreateTable(string tableName, array rowName, array rowParam)";
		$tableName=str_replace(" ","",$tableName);
		if ($tableName=="" || !is_array($rowName) || !is_array($rowParam)) $this->ShowError($signature,"Ung�ltige Parameter");
		if (count($rowName) != count($rowParam)) $this->ShowError($signature,"rowName und rowParam haben unterschiedliche L�nge!");
		
		if (!$this->TableExists($tableName))
		{		
			$query="CREATE TABLE $tableName (".$primaryKey." ".$primaryKeyType."  not null AUTO_INCREMENT ";
			for ($count=0;$count<count($rowName);$count++)
			{
				$query.=", ".$rowName[$count]." ";
				if ($rowParam[$count]=="")
				{
					$query.=$this->defaultRowType." ";
				}
				else 
				{
					$query.=$rowParam[$count]." ";
				}
				$query.="not null ";
			}
			$query.=$rowParam[$count].", PRIMARY KEY (".$primaryKey."))";
			if ($this->useUTF8) $query.=" DEFAULT CHARACTER SET utf8";
			if ($this->debug==true)	$this->ShowDebugMessage($signature, $query);
			@$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		}
		return true;
	}

	/**
	 * L�scht eine Tabelle
	 * @param String $tableName 	Name der zu l�schenden Tabelle
	 * @return boolean true
	 */
	public function DeleteTable($tableName)
	{
		$signature="MySqlIManager::DeleteTable(string tableName, boolean fetchData)";
		if ($tableName=="") $this->ShowError($signature,"Ung�ltige(r) Parameter");

		$query="DROP TABLE $tableName";
		if ($this->debug==true)	$this->ShowDebugMessage($signature, $query);
		if ($this->TableExists($tableName)) @$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		return true;
	}

	/**
	 * Bennent eine Tabelle um
	 * @param String $tableName 	Namen der Tabelle die umbenannt werden soll
	 * @param String $newTableName	Neuer Name der Tabelle
	 * @return boolean
	 */
	public function RenameTable($tableName,$newTableName)
	{
		$signature="MySqlIManager::RenameTable(string tableName, string newTableName)";
		if ($tableName=="" || $newTableName=="") $this->ShowError($signature, "Ung�ltige(r) Parameter");

		$query="ALTER TABLE $tableName RENAME $newTableName";
		if ($this->debug==true)	$this->ShowDebugMessage($signature, $query);
		if ($this->TableExists($tableName)) @$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);

		return true;
	}

	/**
	 * Pr�ft ob Tabelle tableName vorhanden ist, wenn ja gibt sie true zur�ck ansonsten false
	 * @param String $tableName		Name der Tabelle die gepr�ft werden soll
	 * @return boolean
	 */
	public function TableExists($tableName)
	{
		$signature="MySqlIManager::TableExists(string tableName)";
		if ($tableName=="") $this->ShowError($signature, "Ung�ltiger Parameter, tableName ist leer");

		$query="SHOW TABLES LIKE '$tableName'";
		if ($this->debug==true)	$this->ShowDebugMessage($signature, $query);
		$result = @$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		/*@var $result mysqli_result*/
		if ($result !== false && $result->num_rows > 0)
		{
			$returnData = true;
		}
		else
		{
			$returnData = false;
		}
		mysqli_free_result($result);
		return $returnData;
	}

	/**
	 * F�gt in die Tabelle tableName in die Spalten rowName die Daten aus rowData ein. Liefert bei Erfolg den pkey zur�ck
	 * @param String $tableName 	Tabelle in die eingef�gt werden soll
	 * @param Array $rowName		Spaltennamen in die eingef�gt werden soll
	 * @param Array $rowData		Daten die in die genannten Spalten eingef�gt werden sollen
	 * @return int
	 */
	public function Insert($tableName, $rowName, $rowData, $allowInsertToIdent=false)
	{
		$signature="MySqlIManager::Insert(string tableName, array rowName, array rowData)";
		if ($tableName=="" || !is_array($rowName) || !is_array($rowData)) $this->ShowError($signature,"Ung�ltige Parameter");
		if (count($rowName) != count($rowData)) $this->ShowError($signature,"rowName und rowData haben unterschiedliche L�nge!");

		$query="Insert INTO $tableName ( ";
		for ($count=0;$count<count($rowName);$count++)
		{
			$query.=" ".$rowName[$count].", ";
		}
		$query=substr($query,0,(strlen($query)-2));
		$query.=") VALUES (";
		for ($count=0;$count<count($rowData);$count++)
		{
			$query.=" '".MySqliManager::mysql_convert_string_to_db($rowData[$count])."', ";
		}
		$query=substr($query,0,(strlen($query)-2));
		$query.=")";
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		
		@$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		return $this->m_MySqlI->insert_id;
	}

	/**
	 * �ndert den Datensatz pkey in Tabelle tableName. Nicht genannte Spalten bleiben unber�hrt.
	 * @param String $tableName 	Tabelle in die eingef�gt werden soll
	 * @param Array $rowName		Spaltennamen deren Inhalt ge�ndert werden solle
	 * @param Array $rowData		Neue Inhalte der zu �ndernden Spalte
	 * @param int $pkey				Pkey des zu �ndernden Datensatztes
	 * @return boolean
	 */
	public function UpdateByPkey($tableName,$rowName,$rowData,$pkey)
	{
		$signature="MySqlIManager::UpdateByPkey(string tableName, array rowName, array rowData, int pkey)";
		if ($tableName=="" || $pkey=="" || !is_array($rowName) || !is_array($rowData)) $this->ShowError($signature,"Ung�ltige Parameter");
		if (count($rowName) != count($rowData)) $this->ShowError($signature,"rowName und rowData haben unterschiedliche L�nge!");

		$query="Update $tableName SET ";
		for ($count=0;$count<count($rowName);$count++){
			$query.=" ".$rowName[$count]."='".MySqlIManager::mysql_convert_string_to_db($rowData[$count])."', ";
		}
		$query=substr($query,0,(strlen($query)-2));
		$query.=" WHERE pkey=$pkey";

		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		// @mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		@$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		return true;
	}

	/**
	 * �ndert den Datens�tze die mit $where in Tabelle $tableName selectiert werden. Nicht genannte Spalten bleiben unber�hrt.
	 * @param String 	$tableName 		Tabelle in die eingefügt werden soll
	 * @param Array 	$rowName 		Spaltennamen deren Inhalt geändert werden sollen
	 * @param Array 	$rowData 		Neue Inhalte der zu ändernden Spalten
	 * @param String	$where 			TSQL-Bedingung der zu ändernden Datensätze
	 * @return boolean
	 */
	public function Update($tableName,$rowName,$rowData,$where)
	{
		if (is_numeric($where) && $where==(int)$where) return $this->UpdateByPkey($tableName,$rowName,$rowData,(int)$where);
		
		$signature="MySqlIManager::Update(string tableName, array rowName, array rowData, string where)";
		if ($tableName=="" || $where=="" || !is_array($rowName) || !is_array($rowData)) $this->ShowError($signature,"Ung�ltige Parameter");
		if (count($rowName) != count($rowData)) $this->ShowError($signature,"rowName und rowData haben unterschiedliche L�nge!");

		$query="Update $tableName SET ";
		for ($count=0;$count<count($rowName);$count++){
			$query.=" ".$rowName[$count]."='".MySqlIManager::mysql_convert_string_to_db($rowData[$count])."', ";
		}
		$query=substr($query,0,(strlen($query)-2));
		$query.=" WHERE $where";
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		@$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		return true;
	}

	/**
	 * L�scht Datensatz pkey in Tabelle tableName
	 * @param String $tableName 	Tabelle aus der gel�scht werden soll
	 * @param int $pkey 			Pkey des zu l�schenden Datensatztes
	 * @return boolean
	 */
	public function DeleteByPkey($tableName,$pkey)
	{
		$signature="MySqlIManager::DeleteByPkey(string tableName, int $pkey)";
		if ($tableName=="" || $pkey=="") $this->ShowError($signature,"Ung�ltige Parameter");

		$query="DELETE FROM $tableName WHERE pkey='$pkey'";
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		@$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		return true;
	}

	/**
	 * L�scht Datensatz pkey in Tabelle tableName
	 * @param String $tableName 	Tabelle aus der gel�scht werden soll
	 * @param int $pkey 		Pkey des zu l�schenden Datensatztes
	 * @return boolean
	 */
	public function Delete($tableName, $where)
	{
		$signature="MySqlIManager::Delete(string tableName, string where)";
		if ($tableName=="" || trim($where)=="") $this->ShowError($signature,"Ung�ltige Parameter");

		$query="DELETE FROM $tableName WHERE ".$where;
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		@$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		return true;
	}

	/**
	 * F�hrt die �bergebene SQL query aus und gibt die Ergebnisse in einem assoziativen Array zur�ck: Array[index]["row1Name"]["row2name"]...
	 * @param String $query				SQL Abfrage
	 * @param boolean $selectOnline		Selektiert nur Datensätzte die nach dem WI2.x Tabellenformat online sind
	 * @return Array
	 */
	public function SelectAssoc($query, $selectOnline=false)
	{
		$signature="MySqlIManager::SelectAssoc(string query)";
		if ($query=="") $this->ShowError($signature,"Ung�ltige Parameter, ".$query." ist leer.");
		if ($selectOnline==true && strpos($query, 'SELECT *') === false)
		$query=str_replace(" FROM",",onlineFrom,onlineUntil,WF_STAT FROM",$query);

		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		$result = @$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		
		if ($result === false) return Array();

		$return_data=Array();
		$index=0;
		while ($data = $result->fetch_assoc())
		{
			array_walk ($data, 'MySqlIManager::strip_slashes');
			$return_data[$index]=$data;
			$index++;
		}
		mysqli_free_result($result);

		if ($selectOnline==true){
			$tmp=array();
			for ($lauf=0; $lauf<count($return_data);$lauf++)
			{
				if ($this->UpToDate(time(),$return_data[$lauf]["onlineFrom"],$return_data[$lauf]["onlineUntil"]) && ($return_data[$lauf]["WF_STAT"]<=0 || $return_data[$lauf]["WF_STAT"]==""))
				{
					$tmp[]=$return_data[$lauf];
				}
			}
			$return_data=$tmp;
		}


		return $return_data;
	}

	/**
	 * F�hrt die �bergebene SQL query aus und gibt die serialisierten, mit gzcompress gepackte Ergebnisse in einem assoziativen Array zur�ck Array[index]["row1Name"]["row2name"]...
	 * @param String $query 		SQL Abfrage
	 * @return Array
	 */
	public function SelectAssocGZIP($query)
	{
		$signature="MySqlIManager::SelectAssocGZIP(string query)";
		if ($query=="") $this->ShowError($signature,"Ung�ltige Parameter, $query ist leer.");
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		//$query_id=@mysql_query ($query,$this->connID) or die ($this->Err->showError($signature,mySql_Error($this->connID)));
		$result = @$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		
		if ($result === false) return Array();

		$return_data=Array();
		$index=0;
		while ($data = $result->fetch_assoc())
		{
			array_walk ($data, 'MySqlIManager::strip_slashes');
			$return_data[$index]=gzcompress(serialize($data),9);
			$index++;
		}
		mysqli_free_result($result);
		return $return_data;
	}

	/**
	 * F�hrt die �bergebene SQL query aus und gibt die Ergebnisse in einem assoziativen Array zur�ck. Die Daten werden f�r HTML-Ausgabe aufbereitet. Array[index]["row1Name"]["row2name"]...
	 * %UID% wird durch UID=<?=$UID?\> ersetzt.
	 * @param String $query 			SQL Abfrage
	 * @param boolean $selectOnline		Selektiert nur Datens�tzte die nach dem WI2.x Tabellenformat online sind
	 * @return Array
	 */
	public function SelectHtmlAssoc($query,$selectOnline=false)
	{
		$signature="MySqlIManager::SelectHtmlAssoc(string query, bool selectOnline)";
		if ($query=="") $this->ShowError($signature,"Ung�ltige Parameter, $query ist leer.");
		if ($selectOnline==true)
		$query=str_replace(" FROM",",onlineFrom,onlineUntil,WF_STAT FROM",$query);

		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		$result = @$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		
		if ($result === false) return Array();
		
		$return_data=Array();
		$index=0;
		while ($data = $result->fetch_assoc())
		{
			array_walk ($data, 'MySqlIManager::MySqlIManagerConvertToHtml');
			array_walk ($data, 'MySqlIManager::mysql_convert_string_from_db');
			$return_data[$index]=$data;
			$index++;
		}
		mysqli_free_result($result);
		if ($selectOnline==true){
			$tmp=array();
			for ($lauf=0; $lauf<count($return_data);$lauf++)
			{
				if ($this->UpToDate(time(),$return_data[$lauf]["onlineFrom"],$return_data[$lauf]["onlineUntil"]) && ($return_data[$lauf]["WF_STAT"]<=0 || $return_data[$lauf]["WF_STAT"]==""))
				{
					$tmp[]=$return_data[$lauf];
				}
			}
			$return_data=$tmp;
		}

		return $return_data;
	}

	/**
	 * F�hrt die �bergebene SQL query aus und gibt die Ergebnisse in einem Array zur�ck: Array[index][0][1]...
	 * @param String $query 		SQL Abfrage
	 * @return Array
	 */
	public function SelectArray($query)
	{
		return $this->Select($query);
	}

	/**
	 * F�hrt die �bergebene SQL query aus und gibt die Ergebnisse in einem Array zur�ck: Array[index][0][1]...
	 * @param String $query 		SQL Abfrage
	 * @return Array
	 */
	public function Select($query)
	{
		$signature="MySqlIManager::SelectArray(string query)";
		if ($query=="")  $this->ShowError($signature,"Ung�ltige Parameter, $query ist leer.");
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		$result = @$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		if ($result === false) return Array();
		
		$return_data=Array();
		$index=0;
		while ($data = $result->fetch_array())
		{
			while (list ($key, $val) = each ($data))
			{
				$val=stripslashes($val);
			}
			$return_data[$index]=$data;
			$index++;
		}
		mysqli_free_result($result);
		if (count($return_data)==0)
		$return_data=false;
		return $return_data;
	}

	/**
	 * F�hrt die �bergebene SQL query aus und gibt die Ergebnisse f�r HTMl Ausgabe in einem Array zur�ck Array[index][0][1]...
	 * @param String $query 		SQL Abfrage
	 * @return Array
	 */
	public function SelectHtmlArray($query)
	{
		$signature="MySqlIManager::SelectArray(string query)";
		if ($query=="") $this->ShowError($signature,"Ung�ltige Parameter, $query ist leer.");
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		$result = @$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		/*@var $result mysqli_result*/
		if ($result === false) return Array();
		
		$return_data=Array();
		$index=0;
		while ($data = $result->fetch_array())
		{
			array_walk ($data, 'MySqlIManager::MySqlIManagerConvertToHtml');
			$return_data[$index]=$data;
			$index++;
		}
		mysqli_free_result($result);
		
		if (count($return_data)==0)
		$return_data=false;

		return $return_data;
	}

	/**
	 * Gibt die Anzahl der in tableName Vorhandenen Eintr�ge zur�ck
	 * @param String $tableName 		Tabellenname
	 * @return int
	 */
	public function GetTableEntries($tableName)
	{
		$signature="MySqlIManager::GetTableEntries(string tableName)";
		if ($tableName=="") $this->ShowError($signature,"Ung�ltige Parameter, $tableName ist leer.");
		$query="SELECT pkey FROM $tableName";
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		$query_id = @$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		
		return $this->m_MySqlI->affected_rows;
	}

	/**
	 * Gibt die Namen der Tabellenspalten in einem Array zur�ck: Array[index]
	 * @param String $tableName 		Tabellenname
	 * @return Array
	 */
	public function GetTableRows($tableName)
	{
		$signature="MySqlIManager::GetTableRows(string tableName)";
		if ($tableName=="") $this->ShowError($signature,"Ung�ltige Parameter, $tableName ist leer.");
		
		
		$query = "SELECT * FROM ".$tableName." LIMIT 0,1";
		//$query="SHOW FIELDS FROM ".$tableName;
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);

		$result = @$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		$data = $result->fetch_fields();
		mysqli_free_result($result);
		
		
		foreach ($data as $column)
		{
			$names[] = $column->name;
		}
		/*
		$query="SHOW FIELDS FROM ".$tableName;
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);	
		$result = $this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		
		$names = Array();
		while ($data = $result->fetch_array())
		{
			$names[] = $data["Field"];
		}
		*/
		return $names;
	}

	/**
	 * Gibt den Namen, den Datentyp und den Index der Spalte zur�ck: AssocArray["name"]["type"]["flags"]["index"]
	 * @param String $tableName 		Tabellenname
	 * @param String $rowName 		Spaltenname
	 * @return Array
	 */
	public function GetRowInfo($tableName, $rowName)
	{
		$signature="MySqlIManager::GetRowInfo(string tableName, string rowName)";
		if ($tableName=="" || $rowName=="") $this->ShowError($signature,"Ung�ltige(r) Parameter");

		$query="SHOW FIELDS FROM $tableName LIKE '$rowName'";
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);		
		
		/*@var $result mysqli_result*/
		$result = $this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		$info = $result->fetch_array();
		
		$data["name"]=$info["Field"];
		$data["type"]=$info["Type"];
		$data["flags"]=$info["Null"];
		
		mysqli_free_result($result);
		
		$rows = $this->GetTableRows($tableName);
		
		$data["index"] = -1;
		for ($i=0; $i<count($rows); $i++)
		{
			if ($rows[$i] == $rowName)
			{
				$data["index"] = $i;
				break;
			}
		}
		return $data;
	}

	/**
	 * Pr�ft ob die Spalte rowName vorhanden ist (true/false)
	 * @param String $tableName 		Tabellenname
	 * @param String $rowName 		Spaltenname
	 * @return boolean
	 * @access public
	 */
	public function TableRowExists($tableName, $rowName)
	{
		$signature="MySqlIManager::TableRowExists(string tableName, string rowName)";
		if ($tableName=="" || $rowName=="") $this->ShowError($signature,"Ung�ltige(r) Parameter.");

		$rows=$this->GetTableRows($tableName);
		if (in_array($rowName,$rows))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * F�gt eine neue Spalte in Tabelle tableName ein
	 * @param String $tableName 		Tabellenname
	 * @param String $rowName			Spaltenname
	 * @param String $rowType			Parameter der neuen Spalte (Datentyp etc.)
	 * @return boolean
	 */
	public function InsertRow($tableName,$rowName,$rowType)
	{
		$signature="MySqlIManager::InsertRow(string tableName, string rowName, string rowType)";
		if ($tableName=="" || $rowName=="") $this->ShowError($signature,"Un�ltige(r) Parameter.");
		if ($rowType=="") $rowType=$this->defaultRowType;

		if(!$this->TableRowExists($tableName,$rowName))
		{
			$query="ALTER TABLE $tableName ADD $rowName $rowType not null";
			if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
			@$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		}
		return true;
	}

	/**
	 * F�gt eine neue Spalte in Tabelle tableName nach der Spalte $afterRow ein
	 * @param String $tableName 		Tabellenname
	 * @param String $rowName 		Spaltenname
	 * @param String $rowType 		Parameter der neuen Spalte (Datentyp etc.)
	 * @param String $afterRow 		Name der Spalte nach der eingef�gt soll
	 * @return boolean
	 */
	public function InsertRowAfter($tableName, $rowName, $rowType, $afterRow)
	{
		$signature="MySqlIManager::InsertRowAfter(string tableName, string rowName, string rowType, string afterRow)";
		if ($tableName=="" || $rowName=="" || $afterRow=="") $this->ShowError($signature,"Un�ltige(r) Parameter.");
		if ($rowType=="") $rowType=$this->defaultRowType;

		if($this->TableRowExists($tableName,$afterRow))
		{
			$query="ALTER TABLE $tableName ADD $rowName $rowType not null AFTER $afterRow ";
			if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
			@$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		}
		return true;
	}

	/**
	 * L�scht die Spalte rowName in der Tabelle tableName
	 * @param String $tableName 		Tabellenname
	 * @param String $rowName			Name der Spalte die gel�scht werden soll
	 * @return boolean
	 */
	public function DeleteRow($tableName,$rowName)
	{
		$signature="MySqlIManager::DeleteRow(string tableName, string rowName)";
		if ($tableName=="" || $rowName=="") $this->ShowError($signature,"Un�ltige(r) Parameter.");

		if($this->TableRowExists($tableName,$rowName))
		{
			$query="ALTER TABLE $tableName DROP $rowName";
			if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
			@$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
			return true;
		}
		else return false;
	}

	/**
	 * �ndert die Spalte in newRowName und newRowType
	 * @param	string $tableName 		Tabellenname
	 * @param	string $rowName 		Name der Spalte die umbenannt werden soll
	 * @param	string $newRowName 		Neuer Spaltenname
	 * @param	string $newRowType 		Neue Spaltenparamter Datentyp etc.)
	 * @return boolean
	 */
	public function ChangeRow($tableName, $rowName, $newRowName, $newRowType)
	{
		$signature="MySqlIManager::ChangeRow(string tableName, string rowName, string newRowName, string newRowType)";
		if ($tableName=="" || $rowName=="") $this->ShowError($signature,"Un�ltige(r) Parameter.");

		if($this->TableRowExists($tableName,$rowName)){
			if ($newRowName=="") $newRowName=$rowName;
			// bestehender Datentyp der Spalte auslesen
			if ($newRowType==""){
				$old_row_info=$this->GetRowInfo($tableName,$rowName);
				$newRowType=$old_row_info["type"]." ".$old_row_info["flags"];
			}

			$query="ALTER TABLE $tableName CHANGE $rowName $newRowName $newRowType";
			if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
			@$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
			return true;
		}
		else $this->ShowError($signature,"Spalte '$rowName' exisitiert nicht!");
	}

	/**
	 * Pr�ft ob pkey in Tabelle tableName bereits vorhanden ist (true/false)
	 * @param String $tableName 		Tabellenname
	 * @param int $pkey					Pkey nach dem gesucht werden soll
	 * @return 	boolean
	 */
	public function PkeyExists($tableName, $pkey)
	{
		$signature="MySqlIManager::PkeyExists(string tableName, String $pkey)";
		if ($tableName=="" || $pkey=="") $this->ShowError($signature,"Un�ltige(r) Parameter.");
		$query="SELECT pkey FROM $tableName WHERE pkey=$pkey";
		if ($this->debug==true) $this->ShowDebugMessage($signature, $query);
		@$this->m_MySqlI->query($query) or $this->ShowMySqlIError($signature);
		
		if ($this->m_MySqlI->affected_rows == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Erzeugt einen Index
	 * @param String $tableName 	Tabellenname
	 * @param String $rowName		Spaltenname or Array of column names defining together one index
	 * @param bool $unique 			Soll der Index unique sein?
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
		// Pr�fen, dass die L�nge des Index-Namen nicht 64 �berschrietet
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
	 * @param String $string 	String
	 * @return String  			DBString
	 */
	public function ConvertStringToDBString($string)
	{
		return $this->mysql_convert_string_to_db($string);
	}

	/**
	 * Hilfsfunktion f�r MySqlIManager: Entfernt Slashes aus String Wird f�r ein Array �ber array_walk() o.�. aufgerufen
	 * @param value $item1 		Inhalt an der Stelle Key im Array
	 * @param key $pkey 		Key des Arrays
	 */
	public static function strip_slashes (&$item1, $key)
	{
		$item1 = str_replace("\\","",$item1);
	}

	/**
	 * Hilfsfunktion f�r MySqlIManager: Wandelt Unix & Windows Steuerzeichen in HTML Tags um. Wird f�r ein Array �ber array_walk() o.�. aufgerufen. Wandelt beispielsweise "~" aus WI in HTML-Aufz�hlung um
	 * @param value $item1 		Inhalt an der Stelle Key im Array
	 * @param key $pkey 		Key des Arrays
	 */
	public static function MySqlIManagerConvertToHtml (&$item1, $key)
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
	 * Hilfsfunktion f�r MySqlIManager Sonderzeichen aus Datenbankinhalten entfernen
	 * @param String $string 	String der umgewandelt werden soll
	 * @return String
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
		//$string=mysql_real_escape_string($string);
		return $string;
	}

	/**
	 * Hilfsfunktion f�r MySqlIManager Sonderzeichen aus Datenbankinhalten entfernen
	 * @param String $string 	String der umgewandelt werden soll
	 * @return String
	 */
	public static function mysql_convert_string_from_db(&$item1, $key)
	{
		global $UID;
		$item1=str_replace("%UID%","UID=".$UID,$item1);
		$item1=str_replace("intern://","",$item1);
	}
	
}
?>
