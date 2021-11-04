<?

/**
 * Allows usage of SQLite v3 database over PDO as DBManager.
 * Attention: In PHP PDO support and a PDO Driver for SQLite 3.x have to be enabled.
 * Requires an ErrorManager
 * @package  SQLiteManager
 * @access   public
 * @author   Marco Pfeifer<m.pfeifer@stollvongati.com>
 * @since    PHP 5.2
 */
class SQLiteManager extends DBManager {

	var $debug;
	var $Err;
	var $errorText;
	var $dbURL = "sqlite::memory:";
	var $options = array();
	var $pdo = null;
	var $defaultRowType = "TEXT";

	/**
	 * SQLiteManager constructor. Crerates a connection to the DBMS.
	 * If $dbName is empty or equal to 'memory' a session scoped in memory database is created.
	 * @param string $dbName - The database file name
	 * @param string $pathToDbFile - The local path to the database file.
	 * @param string $dbHost - The local path of the database file to create.
	 * @param object ErrorManager &$errorManager Referenz auf ErrorManager Objekt
	 * @param boolean $debug Sollen debug Informationen angezeigt werden? (Standard: false)
	 * @access public
	 */
	function SQLiteManager($dbName, $pathToDbFile, &$errorManager, $debug = false)
	{
		//echo "$dbName,$dbHost,$dbUser,$dbPwd,&$errorManager";
		global $_SERVER;
		$this->debug = $debug;
		$this->errorText = "";
		$this->Err = $errorManager;

		$dbName = trim($dbName);

		if ($dbName == '' || $dbName == 'memory')
		{
			// connect to SQLite database
			$this->dbURL = 'sqlite::memory:';
			$this->options = array(PDO::ATTR_PERSISTENT => true);
		}
		else
		{
			$dbFile = trim($pathToDbFile);
			if ($dbFile[strlen($dbFile)] != '/')
			{
				$dbFile .= '/';
			}
			$dbFile .= $dbName;
			$this->dbURL = 'sqlite:'.$dbFile;
		}

		return true;
	}

	protected function GetPDO()
	{
		if (!$this->pdo)
		{
			try
			{
				// connect to SQLite database
				$this->pdo = new PDO($this->dbURL, null, null, $this->options);

				// set the error reporting attribute to warning level, which should not be displayed in production environment
				$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			}
			catch (PDOException $e)
			{
				die($this->Err->showError("SQLiteManager", "Can't connect to database.".($this->debug ? ' Error message: '.$e->getMessage() : '')));
			}
		}
		return $this->pdo;
	}

	/**
	 * Create a prepared statement for further use. 
	 * Does not catch any PDOException
	 * @param string $query - the SQL query to prepare
	 * @return PDOStatement
	 * @throws PDOException
	 */
	function PrepareStatement($query, $signature = "SQLiteManager::PrepareStatement(string query)")
	{
		if ($query == "")
		{
			die($this->Err->showError($signature, "Ungültige Parameter, $query ist leer."));
		}

		/* @var $statement PDOStatement */
		$statement = $this->GetPDO()->prepare($query);
		return $statement;
	}

	/**
	 * Prepare and execute the SQL query. Do not use this with a SELECT query!
	 * @see also PrepareStatement()
	 * @param string $query SQL-Query String to execute, may contain '?' for parameter bindings provided as $rowData parameter
	 * @param array $rowData - the data to bind (optional)
	 * @param bool $dieOnException - if true the script dies when a PDOException occures (optional)
	 * @param string $signature - the signature to use for reporting (optional)
	 * @return bool - query executed successful.
	 * @access public
	 */
	function Query($query, $rowData = null, $dieOnException = false, $signature = "SQLiteManager::Query(string query)")
	{
		try
		{
			if ($this->debug == true)
			{
				$this->Err->ShowDebugInfo($signature, $query.' Data:'.print_r($rowData, true));
			}

			/* @var $statement PDOStatement */
			$statement = $this->PrepareStatement($query, $signature);
			if ($statement)
			{
				if (is_array($rowData))
				{
					// array_values(): ensure bound params array begins with index 0
					return $statement->execute(array_values($rowData));
				}
				return $statement->execute();
			}
		}
		catch (PDOException $e)
		{
			if ($dieOnException)
			{
				die($this->Err->showError("SQLiteManager", $signature.($this->debug ? ' Error message: '.$e->getMessage() : '')));
			}
			$this->Err->showError("SQLiteManager", $signature.($this->debug ? ' Error message: '.$e->getMessage() : ''));
		}

		return false;
	}

	/**
	 * Führt die übergebene SQL query aus und gibt bei Erfolg die Query-ID zurück. Als erstes Feld wird
	 * automatisch pkey (index,primary) angelegt.
	 * @param	array $tableName	Namen der neuen Tabelle
	 * @param	array $rowName		Spaltennamen der neuen Tabelle
	 * @param	string $rowParam 	Array mit den Parametern der einzelnen Spalten (Datentypen).
	 * 								Ist Feld leer wird Standarddatentype (TEXT) gesetzt.
	 * @return boolean true
	 * @access public
	 */
	function CreateTable($tableName, $rowName, $rowParam, $primaryKey = "pkey", $primaryKeyType = "INTEGER")
	{
		$signature = "SQLiteManager::CreateTable(string tableName, array rowName, array rowParam)";
		try
		{
			$tableName = str_replace(" ", "", $tableName);
			if ($tableName == "" || !is_array($rowName) || !is_array($rowParam))
				die($this->Err->showError($signature, "Ungültige Parameter"));
			if (count($rowName) != count($rowParam))
				die($this->Err->showError($signature, "rowName und rowParam haben unterschiedliche Länge!"));

			$query = "CREATE TABLE IF NOT EXISTS $tableName (".$primaryKey." ".$primaryKeyType." PRIMARY KEY ASC AUTOINCREMENT";
			for ($count = 0; $count < count($rowName); $count++)
			{
				$query.=", ".$rowName[$count]." ";
				if ($rowParam[$count] == "")
					$query.=$this->defaultRowType." ";
				else
					$query.=$rowParam[$count]." ";
			}
			$query.=")";
			if ($this->debug == true)
			{
				$this->Err->ShowDebugInfo($signature, $query);
			}
			$this->GetPDO()->exec($query);
			return true;
		}
		catch (PDOException $e)
		{
			die($this->Err->showError("SQLiteManager", $signature.($this->debug ? ' Error message: '.$e->getMessage() : '')));
		}
	}

	/**
	 * Löscht eine Tabelle
	 * @param	string $tableName 	Name der zu löschenden Tabelle
	 * @return boolean true
	 * @access public
	 */
	function DeleteTable($tableName)
	{
		$signature = "SQLiteManager::DeleteTable(string tableName)";
		try
		{
			if ($tableName == "")
				die($this->Err->showError($signature, "Ungültige(r) Parameter"));

			$query = "DROP TABLE IF EXISTS $tableName";
			if ($this->debug == true)
			{
				$this->Err->ShowDebugInfo($signature, $query);
			}
			$this->GetPDO()->exec($query);

			return true;
		}
		catch (PDOException $e)
		{
			die($this->Err->showError("SQLiteManager", $signature.($this->debug ? ' Error message: '.$e->getMessage() : '')));
		}
	}

	/**
	 * Bennent eine Tabelle um
	 * @param	string $tableName 	Namen der Tabelle die umbenannt werden soll
	 * @param	string $newTableName Neuer Name der Tabelle
	 * @return boolean true
	 * @access public
	 */
	function RenameTable($tableName, $newTableName)
	{
		$signature = "SQLiteManager::RenameTable(string tableName, string newTableName)";
		try
		{
			if ($tableName == "" || $newTableName == "")
				die($this->Err->showError($signature, "Ungültige(r) Parameter"));

			$query = "ALTER TABLE $tableName RENAME $newTableName";
			if ($this->debug == true)
			{
				$this->Err->ShowDebugInfo($signature, $query);
			}
			$this->GetPDO()->exec($query);

			return true;
		}
		catch (PDOException $e)
		{
			die($this->Err->showError("SQLiteManager", $signature.($this->debug ? ' Error message: '.$e->getMessage() : '')));
		}
	}


	/**
	 * Prüft ob Tabelle tableName vorhanden ist, wenn ja gibt sie true zur�ck ansonsten false
	 * @param	String $tableName	Name der Tabelle die geprüft werden soll
	 * @return boolean
	 * @access public
	 */
	function TableExists($tableName)
	{
		$signature = "SQLiteManager::TableExists(string tableName)";
		try
		{
			if ($tableName == "")
			{
				die($this->Err->showError($signature, "Ungültiger Parameter, tableName ist leer"));
			}

			$statement = $this->FetchTableInfo($tableName);
			if ($statement)
			{
				$result = $statement->fetch(PDO::FETCH_NUM); // fetch table info
				return $result !== false;
			}
		}
		catch (PDOException $e)
		{
			// do nothing since table does not exist!
			return false;
		}

		return false;
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
	function Insert($tableName, $rowName, $rowData, $allowInsertToIdent = false)
	{
		$signature = "SQLiteManager::Insert(string tableName, array rowName, array rowData)";

		if ($tableName == "" || !is_array($rowName) || !is_array($rowData))
			die($this->Err->showError($signature, "Ungültige Parameter"));
		if (count($rowName) != count($rowData))
			die($this->Err->showError($signature, "rowName und rowData haben unterschiedliche Länge!"));

		$query = "INSERT INTO $tableName (".$rowName[0];
		for ($count = 1; $count < count($rowName); $count++)
		{
			$query.=", ".$rowName[$count];
		}
		$query.=") VALUES (?";
		for ($count = 1; $count < count($rowData); $count++)
		{
			$query.=", ?";
			// filter data
			$rowData[$count] = SQLiteManager::sqlite_convert_string_to_db($rowData[$count]);
		}
		$query.=")";

		if ($this->Query($query, $rowData, true, $signature))
		{
			return $this->pdo->lastInsertId();
		}
		return -1;
	}

	/**
	 * Ändert den Datensatz pkey in Tabelle tableName. Nicht genannte Spalten bleiben unberührt.
	 * @param	string $tableName 	Tabelle in die eingefügt werden soll
	 * @param	array $rowName 	Spaltennamen deren Inhalt geändert werden solle
	 * @param	array $rowData 	Neue Inhalte der zu �ndernden Spalte
	 * @param	int $pkey 		Pkey des zu �ndernden Datensatztes
	 * @return boolean
	 * @access public
	 */
	function UpdateByPkey($tableName, $rowName, $rowData, $pkey)
	{
		$signature = "SQLiteManager::UpdateByPkey(string tableName, array rowName, array rowData, int pkey)";
		if ($tableName == "" || $pkey == "" || !is_array($rowName) || !is_array($rowData))
			die($this->Err->showError($signature, "Ungültige Parameter"));
		if (count($rowName) != count($rowData))
			die($this->Err->showError($signature, "rowName und rowData haben unterschiedliche Länge!"));

		$where = "pkey=:pkey";
		return $this->Update($tableName, $rowName, $rowData, $where, array("pkey" => $pkey));
	}

	/**
	 * Ändert den Datensätze die mit $where in Tabelle $tableName selectiert werden. Nicht genannte Spalten bleiben unberührt.
	 * $where kann bind parameter enthalten, z.B.: 'pkey=:pkey' oder 'pkey=?', dann muss $whereData ein array mit den werten sein.
	 *
	 * @param	string 	$tableName 		Tabelle in die eingefügt werden soll
	 * @param	array 	$rowName 		Spaltennamen deren Inhalt geändert werden sollen
	 * @param	array 	$rowData 		Neue Inhalte der zu ändernden Spalten
	 * @param	string	$where 			SQL-Bedingung der zu ändernden Datensätze
	 * @param	array	$whereData		Bind-Parameter zur  WHERE-Bedingung (optional)
	 * @return boolean
	 * @access public
	 */
	public function Update($tableName, $rowName, $rowData, $where, $whereData = null)
	{
		if (is_numeric($where) && $where == (int) $where)
		{
			return $this->UpdateByPkey($tableName, $rowName, $rowData, (int) $where);
		}

		$signature = "SQLiteManager::Update(string tableName, array rowName, array rowData, string where)";
		if ($tableName == "" || $where == "" || !is_array($rowName) || !is_array($rowData))
			die($this->Err->ShowError($signature, "Ungültige Parameter"));
		if (count($rowName) != count($rowData))
			die($this->Err->ShowError($signature, "rowName und rowData haben unterschiedliche Länge!"));

		$query = "UPDATE $tableName SET ".$rowName[0]."=?";
		if (is_string($rowData[0]))
		{
			$rowData[0] = SQLiteManager::sqlite_convert_string_to_db($rowData[0]);
		}
		for ($count = 1; $count < count($rowName); $count++)
		{
			$query.=", ".$rowName[$count]."=?";
			if (is_string($rowData[$count]))
			{
				$rowData[$count] = SQLiteManager::sqlite_convert_string_to_db($rowData[$count]);
			}
		}

		$rowData = $this->prepareAndAddBindData($rowData, $whereData);

		$query.=" WHERE $where";

		return $this->Query($query, $rowData, true, $signature);
	}

	/**
	 * Löscht Datensatz pkey in Tabelle tableName
	 * @param	string $tableName 	Tabelle aus der gelöscht werden soll
	 * @param	int $pkey 			Pkey des zu löschenden Datensatztes
	 * @return boolean
	 * @access public
	 */
	function DeleteByPkey($tableName, $pkey)
	{
		$signature = "SQLiteManager::DeleteByPkey(string tableName, int $pkey)";
		if ($tableName == "" || $pkey == "")
			die($this->Err->showError($signature, "Ungültige Parameter"));

		return $this->Delete($tableName, 'pkey=?', array($pkey));
	}

	/**
	 * Löscht Datensatz pkey in Tabelle tableName
	 * @param	string $tableName 	Tabelle aus der gelöscht werden soll
	 * @param	int $pkey 		Pkey des zu löschenden Datensatztes
	 * @return boolean
	 * @access public
	 */
	function Delete($tableName, $where, $whereData = null)
	{
		$signature = "SQLiteManager::Delete(string tableName, string where)";
		if ($tableName == "" || trim($where) == "")
			die($this->Err->showError($signature, "Ungültige Parameter"));

		$query = "DELETE FROM $tableName WHERE ".$where;

		$whereData = $this->prepareBindData($whereData);
		return $this->Query($query, $whereData, true, $signature);
	}

	/**
	 * Executes the specified query with bind parameter and return an associative array with all results.
	 * Result array: Array[row-index]["name of column 1"]["name of column 2"]...
	 * E.G.:
	 * Scheme 1: $query = 'SELECT * FROM table1 WHERE pkey=? AND name=?' $bindParameter = array(0=> 1, 1=>'Peter');
	 * Scheme 2: $query = 'SELECT * FROM table1 WHERE pkey=:id AND name=:name' $bindParameter = array(':id'=> 1, ':name'=>'Peter');
	 *
	 * @param	string $query			the query
	 * @param	array $bindParameter	bind parameter data (optional)
	 * @return array
	 * @access public
	 */
	function SelectAssocBind($query, $bindParameter=null)
	{
		$signature = "SQLiteManager::SelectAssocBind(string query)";
		try
		{
			if ($this->debug == true)
			{
				$this->Err->ShowDebugInfo($signature, $query.' Data:'.print_r($bindParameter, true));
			}

			/* @var $statement PDOStatement */
			$statement = $this->PrepareStatement($query, $signature);
			if ($statement)
			{
				if (is_array($bindParameter))
				{
					// array_values(): ensure bound params array begins with index 0
					if ($statement->execute(array_values($bindParameter)) === false)
					{
						$statement = false;
					}
				}
				else if ($statement->execute() === false)
				{
					$statement = false;
				}
			}
			if ($statement)
			{
				return $statement->fetchAll(PDO::FETCH_ASSOC);
			}
			
			return false;
		}
		catch (PDOException $e)
		{
			die($this->Err->showError("SQLiteManager", $signature.($this->debug ? ' Error message: '.$e->getMessage() : '')));
		}
		return false;
	}

	/**
	 * Führt die Übergebene SQL query aus und gibt die Ergebnisse in einem assoziativen
	 * Array zurück: Array[index]["row1Name"]["row2name"]...
	 * To Do: selectOnline auf WI3 anpassen
	 * @param	string $query 		SQL Abfrage
	 * @param	boolean $selectOnline	Selektiert nur Datens�tzte die nach dem WI2.x Tabellenformat online sind
	 * @return array
	 * @access public
	 */
	function SelectAssoc($query, $selectOnline = false)
	{
		$signature = "SQLiteManager::SelectAssoc(string query)";
		if ($query == "")
			die($this->Err->showError($signature, "Ungültige Parameter, $query ist leer."));
		if ($selectOnline == true && strpos($query, 'SELECT *') === false)
			$query = str_replace(" FROM", ",onlineFrom,onlineUntil,WF_STAT FROM", $query);

		if ($this->debug == true)
		{
			$this->Err->ShowDebugInfo($signature, $query);
		}
		
		try
		{
			/* @var $statement PDOStatement */
			$statement = $this->PrepareStatement($query, $signature);

			$return_data = Array();
			if ($statement && $statement->execute())
			{
				$index = 0;
				while ($data = $statement->fetch(PDO::FETCH_ASSOC))
				{
					array_walk($data, 'SQLiteManager::strip_slashes');
					$return_data[$index] = $data;
					$index++;
				}
			}
		}
		catch (PDOException $e)
		{
			die($this->Err->showError("SQLiteManager", $signature.($this->debug ? ' Error message: '.$e->getMessage() : '')));
		}

		if ($selectOnline == true)
		{
			$tmp = array();
			for ($lauf = 0; $lauf < count($return_data); $lauf++)
			{
				if ($this->UpToDate(time(), $return_data[$lauf]["onlineFrom"], $return_data[$lauf]["onlineUntil"]) && ($return_data[$lauf]["WF_STAT"] <= 0 || $return_data[$lauf]["WF_STAT"] == ""))
				{
					$tmp[] = $return_data[$lauf];
				}
			}
			$return_data = $tmp;
		}

		return $return_data;
	}

	/**
	 * Führt die �bergebene SQL query aus und gibt die serialisierten, mit gzcompress gepackte Ergebnisse in einem assoziativen
	 * Array zurück Array[index]["row1Name"]["row2name"]...
	 * @param	string $query 		SQL Abfrage
	 * @return array
	 * @access public
	 */
	function SelectAssocGZIP($query)
	{
		$signature = "SQLiteManager::SelectAssocGZIP(string query)";
		die($this->Err->showError($signature, "TODO: Implement method for SQLite !!!"));
		/*
		  if ($query == "")
		  die($this->Err->showError($signature, "Ung�ltige Parameter, $query ist leer."));
		  if ($this->debug == true)
		  echo "<strong>$signature:</strong>$query<br>";
		  $query_id = @sqlite_query($query, $this->connID) or die($this->Err->showError($signature, sqlite_Error($this->connID)));

		  $return_data = Array();
		  $index = 0;
		  while ($data = sqlite_fetch_assoc($query_id))
		  {
		  array_walk($data, 'SQLiteManager::strip_slashes');
		  $return_data[$index] = gzcompress(serialize($data), 9);
		  $index++;
		  }

		  return $return_data;
		 */
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
	function SelectHtmlAssoc($query, $selectOnline = false)
	{
		$signature = "SQLiteManager::SelectAssoc(string query)";
		die($this->Err->showError($signature, "TODO: Implement method for SQLite !!!"));
		/*
		  if ($query == "")
		  die($this->Err->showError($signature, "Ungültige Parameter, $query ist leer."));
		  if ($selectOnline == true)
		  $query = str_replace(" FROM", ",onlineFrom,onlineUntil,WF_STAT FROM", $query);

		  if ($this->debug == true)
		  echo "<strong>$signature:</strong>$query<br>";
		  $query_id = @sqlite_query($query, $this->connID) or die($this->Err->showError($signature, sqlite_Error($this->connID)));

		  $return_data = Array();
		  $index = 0;
		  while ($data = sqlite_fetch_assoc($query_id))
		  {
		  array_walk($data, 'SQLiteManager::SQLiteManagerConvertToHtml');
		  array_walk($data, 'SQLiteManager::sqlite_convert_string_from_db');
		  $return_data[$index] = $data;
		  $index++;
		  }

		  if ($selectOnline == true)
		  {
		  $tmp = array();
		  for ($lauf = 0; $lauf < count($return_data); $lauf++)
		  {
		  if ($this->UpToDate(time(), $return_data[$lauf]["onlineFrom"], $return_data[$lauf]["onlineUntil"]) && ($return_data[$lauf]["WF_STAT"] <= 0 || $return_data[$lauf]["WF_STAT"] == ""))
		  {
		  $tmp[] = $return_data[$lauf];
		  }
		  }
		  $return_data = $tmp;
		  }

		  return $return_data;
		 */
	}

	/**
	 * Führt die übergebene SQL query aus und gibt die Ergebnisse in einem
	 * Array zur�ck: Array[index][0][1]...
	 * @param	string $query 		SQL Abfrage
	 * @return array
	 * @access public
	 */
	function SelectArray($query)
	{
		return $this->Select($query);
	}

	/**
	 * Führt die übergebene SQL query aus und gibt die Ergebnisse in einem
	 * Array zurück: Array[index][0][1]...
	 * @param	string $query 		SQL Abfrage
	 * @return array
	 * @access public
	 */
	function Select($query)
	{
		$signature = "SQLiteManager::SelectArray(string query)";
		if ($query == "")
			die($this->Err->showError($signature, "Ungültige Parameter, $query ist leer."));

		if ($this->debug == true)
		{
			$this->Err->ShowDebugInfo($signature, $query);
		}

		try
		{
			/* @var $statement PDOStatement */
			$statement = $this->PrepareStatement($query, $signature);

			$return_data = Array();
			$index = 0;
			while ($data = $statement->fetch(PDO::FETCH_NUM))
			{
				while (list ($key, $val) = each($data))
			{
				$val = stripslashes($val);
			}
				$return_data[$index] = $data;
				$index++;
			}
		}
		catch (PDOException $e)
		{
			die($this->Err->showError("SQLiteManager", $signature.($this->debug ? ' Error message: '.$e->getMessage() : '')));
		}

		if (count($return_data) == 0) {
			$return_data = false;
		}
		return $return_data;
	}

	/**
	 * Führt die übergebene SQL query aus und gibt die Ergebnisse für HTMl Ausgabe in einem
	 * Array zur�ck Array[index][0][1]...
	 * @param	string $query 		SQL Abfrage
	 * @return array
	 * @access public
	 */
	function SelectHtmlArray($query)
	{
		$signature = "SQLiteManager::SelectHtmlArray(string query)";
		die($this->Err->showError($signature, "TODO: Implement method for SQLite !!!"));
		/*
		if ($query == "")
			die($this->Err->showError($signature, "Ungültige Parameter, $query ist leer."));
		if ($this->debug == true)
			echo "<strong>$signature:</strong>$query<br>";
		$query_id = @sqlite_query($query, $this->connID) or die($this->Err->showError($signature, sqlite_Error($this->connID)));

		$return_data = Array();
		$index = 0;
		while ($data = sqlite_fetch_array($query_id))
		{
			array_walk($data, 'SQLiteManager::SQLiteManagerConvertToHtml');
			$return_data[$index] = $data;
			$index++;
		}
		if (count($return_data) == 0)
			$return_data = false;

		return $return_data;

		 */
	}

	/**
	 * Gibt die Anzahl der in tableName Vorhandenen Einträge zurück
	 * @param	string $tableName 		Tabellenname
	 * @return int
	 * @access public
	 */
	function GetTableEntries($tableName)
	{
		$signature = "SQLiteManager::GetTableEntries(string tableName)";
		if ($tableName == "")
			die($this->Err->showError($signature, "Ungültige Parameter, $tableName ist leer."));
		$query = "SELECT count(*) FROM $tableName";
		try {
			/* @var $statement PDOStatement */
			$statement = $this->PrepareStatement($query, $signature);
			return $statement->fetchColumn();
		}
		catch (PDOException $e)
		{
			die($this->Err->showError("SQLiteManager", $signature.($this->debug ? ' Error message: '.$e->getMessage() : '')));
		}
	}

	/**
	 * Gibt die Namen der Tabellenspalten in einem Array zurück: Array[index]
	 * @param	string $tableName 		Tabellenname
	 * @return array
	 * @access public
	 */
	function GetTableRows($tableName)
	{
		$signature = "SQLiteManager::GetTableRows(string tableName)";
		
		if ($tableName == "")
			die($this->Err->showError($signature, "Ungültige Parameter, $tableName ist leer."));

		try {
			/* @var $statement PDOStatement */
		$statement = $this->FetchTableInfo($tableName);
		$fields = array();
		if ($statement)
		{
			$fields = $statement->fetchAll(PDO::FETCH_ASSOC); // fetch table info
		}

		$names = Array();
		for ($i = 0; $i < count($fields); $i++)
		{
			$names[$i] = $fields[$i]['name']; 
		}
		return $names;
		}
		catch (PDOException $e)
		{
			die($this->Err->showError("SQLiteManager", $signature.($this->debug ? ' Error message: '.$e->getMessage() : '')));
		}
	}

	/**
	 * Gibt den Namen, den Datentyp und den Index der Spalte zurück:
	 * AssocArray["name"]["type"]["flags"]["index"]
	 * @param	string $tableName 		Tabellenname
	 * @param	string $rowName 		Spaltenname
	 * @return array
	 * @access public
	 */
	function GetRowInfo($tableName, $rowName)
	{
		$signature = "SQLiteManager::GetRowInfo(string tableName, string rowName)";
		try {
			/* @var $statement PDOStatement */
		$statement = $this->FetchTableInfo($tableName);
		while ($row = $statement->fetch(PDO::FETCH_ASSOC))
		{
			if ($row['name'] == $rowName)
			{
				$row['index'] = $row['cid'];
				return $row;
			}
		}
		}
		catch (PDOException $e)
		{
			die($this->Err->showError("SQLiteManager", $signature.($this->debug ? ' Error message: '.$e->getMessage() : '')));
		}
		return array();
		/*
		if ($tableName == "" || $rowName == "")
			die($this->Err->showError($signature, "Ungültige(r) Parameter"));

		$query = "SHOW FIELDS FROM $tableName LIKE '$rowName'";
		$queryID = sqlite_query($query, $this->connID) or die($this->Err->showError($signature, sqlite_Error($this->connID)));
		$info = sqlite_fetch_array($queryID, $this->connID);
		$data["name"] = $info["Field"];
		$data["type"] = $info["Type"];
		$data["flags"] = $info["Null"];
		$fields = @sqlite_list_fields($this->dbName, $tableName, $this->connID) or die($this->Err->showError($signature, sqlite_Error($this->connID)));
		$columns = @sqlite_num_fields($fields) or die($this->Err->showError($signature, sqlite_Error($this->connID)));
		for ($i = 0; $i < $columns; $i++)
		{
			if (sqlite_field_name($fields, $i) == $rowName)
			{
				$data["index"] = $i;
			}
		}
		return $data;

		 */
	}

	/**
	 * Prüft ob die Spalte rowName vorhanden ist (true/false)
	 * @param	string $tableName 		Tabellenname
	 * @param	string $rowName 		Spaltenname
	 * @return boolean
	 * @access public
	 */
	function TableRowExists($tableName, $rowName)
	{
		$signature = "SQLiteManager::TableRowExists(string tableName, string rowName)";
		
		if ($tableName == "" || $rowName == "")
			die($this->Err->showError($signature, "Ung�ltige(r) Parameter."));

		$rows = $this->GetTableRows($tableName);
		if (in_array($rowName, $rows))
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
	function InsertRow($tableName, $rowName, $rowType)
	{
		$signature = "SQLiteManager::InsertRow(string tableName, string rowName, string rowType)";
		die($this->Err->showError($signature, "TODO: Implement method for SQLite !!!"));
		/*
		if ($tableName == "" || $rowName == "")
			die($this->Err->showError($signature, "Ungültige(r) Parameter."));
		if ($rowType == "")
			$rowType = $this->defaultRowType;

		if (!$this->TableRowExists($tableName, $rowName))
		{
			$query = "ALTER TABLE $tableName ADD $rowName $rowType not null";
			return $this->Query($query, null, true, $signature);
		}
		return true;
		 *
		 */
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
	function InsertRowAfter($tableName, $rowName, $rowType, $afterRow)
	{
		$signature = "SQLiteManager::InsertRowAfter(string tableName, string rowName, string rowType, string afterRow)";
		die($this->Err->showError($signature, "TODO: Implement method for SQLite !!!"));
		/*
		if ($tableName == "" || $rowName == "" || $afterRow == "")
			die($this->Err->showError($signature, "Ungültige(r) Parameter."));
		if ($rowType == "")
			$rowType = $this->defaultRowType;

		if ($this->TableRowExists($tableName, $afterRow))
		{
			$query = "ALTER TABLE $tableName ADD $rowName $rowType not null AFTER $afterRow ";
			@sqlite_query($query, $this->connID) or die($this->Err->showError($signature, sqlite_Error($this->connID)));
		}
		return true;
		 *
		 */
	}

	/**
	 * Löscht die Spalte rowName in der Tabelle tableName
	 * @param	string $tableName 		Tabellenname
	 * @param	string $rowName 		Name der Spalte die gelöscht werden soll
	 * @return boolean
	 * @access public
	 */
	function DeleteRow($tableName, $rowName)
	{
		$signature = "SQLiteManager::InsertRow(string tableName, string rowName)";
		die($this->Err->showError($signature, "TODO: Implement method for SQLite !!!"));
		/*
		if ($tableName == "" || $rowName == "")
			die($this->Err->showError($signature, "Ung�ltige(r) Parameter."));

		if ($this->TableRowExists($tableName, $rowName))
		{
			$query = "ALTER TABLE $tableName DROP $rowName";
			@sqlite_query($query, $this->connID) or die($this->Err->showError($signature, sqlite_Error($this->connID)));
			return true;
		}
		else
			return false;
		 *
		 */
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
	function ChangeRow($tableName, $rowName, $newRowName, $newRowType)
	{
		$signature = "SQLiteManager::ChangeRow(string tableName, string rowName, string newRowName, string newRowType)";
		die($this->Err->showError($signature, "TODO: Implement method for SQLite !!!"));
		/*
		if ($tableName == "" || $rowName == "")
			die($this->Err->showError($signature, "Ungültige(r) Parameter."));

		if ($this->TableRowExists($tableName, $rowName))
		{
			if ($newRowName == "")
				$newRowName = $rowName;
			// bestehender Datentyp der Spalte auslesen
			if ($newRowType == "")
			{
				$old_row_info = $this->GetRowInfo($tableName, $rowName);
				$newRowType = $old_row_info["type"]." ".$old_row_info["flags"];
			}

			$query = "ALTER TABLE $tableName CHANGE $rowName $newRowName $newRowType";
			if ($this->debug == true)
				echo "<strong>$signature:</strong>$query<br>";
			@sqlite_query($query, $this->connID) or die($this->Err->showError($signature, sqlite_Error($this->connID)));
			return true;
		}
		else
			die($this->Err->showError($signature, "Spalte '$rowName' exisitiert nicht!"));
		 *
		 */
	}

	/**
	 * Prüft ob pkey in Tabelle tableName bereits vorhanden ist (true/false)
	 * @param	string 	$tableName 		Tabellenname
	 * @param	int 	$pkey 			Pkey nach dem gesucht werden soll
	 * @return 	boolean
	 * @access 	public
	 */
	function PkeyExists($tableName, $pkey)
	{
		$signature = "SQLiteManager::PkeyExists(string tableName, String $pkey)";
		if ($tableName == "" || $pkey == "")
			die($this->Err->showError($signature, "Ungültige(r) Parameter"));
		$query = "SELECT pkey FROM $tableName WHERE pkey=$pkey";

		try
		{
			/* @var $statement PDOStatement */
			$statement = $this->PrepareStatement($query, $signature);

			$data = $statement->fetchAll(PDO::FETCH_NUM);
			if (count($data) == 1)
			return true;
		else
			return false;
		}
		catch (PDOException $e)
		{
			die($this->Err->showError("SQLiteManager", $signature.($this->debug ? ' Error message: '.$e->getMessage() : '')));
		}
		return false;
	}

	/**
	 * Erzeugt einen Index
	 * @param string $tableName 	Tabellenname
	 * @param string $rowName 	Spaltenname or Array of column names defining together one index
	 * @param bool $unique 				Soll der Index unique sein?
	 * @access public
	 */
	public function CreateIndex($tableName, $rowName, $unique = false)
	{
		if (is_array($rowName))
		{
			$indexPrefix = implode("_", $rowName);
		}
		else
		{
			$indexPrefix = $rowName;
		}

		$indexName = "index_".$tableName."_".$indexPrefix;
		// Prüfen, dass die Länge des Index-Namen nicht 64 überschrietet
		if (strlen($indexName) > 64)
		{
			$indexName = "i_".$tableName."_".$indexPrefix;
			if (strlen($indexName) > 64)
			{
				// Zufällige Zeichenkombination erzeugen
				$idCharsetPool = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
				$randomID = "";
				$randomIDLength = 5;
				for ($a = 0; $a < $randomIDLength; $a++)
				{
					$randomID .= substr($idCharsetPool, rand(0, strlen($idCharsetPool)), 1);
				}
				$indexName = substr($indexName, 0, 64 - $randomIDLength - 1)."_".$randomID;
			}
		}

		$query = "CREATE ".($unique ? "UNIQUE" : "")." INDEX ";
		if (!is_array($rowName))
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
		return $this->sqlite_convert_string_to_db($string);
	}

	/**
	 * Hilfsfunktion für SQLiteManager: Entfernt Slashes aus String
	 * Wird für ein Array �ber array_walk() o.�. aufgerufen
	 * @param	value 	$item1 		Inhalt an der Stelle Key im Array
	 * @param	key 	$pkey 		Key des Arrays
	 * @return 	void
	 * @access 	private
	 */
	static function strip_slashes(&$item1, $key)
	{
		$item1 = str_replace("\\", "", $item1);
	}

	/**
	 * Hilfsfunktion für SQLiteManager: Wandelt Unix & Windows Steuerzeichen
	 * in HTML Tags um. Wird für ein Array über array_walk() o.Ä. aufgerufen.
	 * Wandelt beispielsweise "~" aus WI in HTML-Aufzählung um
	 * @param	value 	$item1 		Inhalt an der Stelle Key im Array
	 * @param	key 	$pkey 		Key des Arrays
	 * @return 	void
	 * @access 	private
	 */
	static function SQLiteManagerConvertToHtml(&$item1, $key)
	{
		$matches = array();
		$lists = array();
		global $UID;
		// Systemfelder sollen aus Performancegr�nden nicht geparsed werden
		$dontParseKeys = array("onlineFrom", "language", "onlineUntil", "WF_STAT");
		$dontParseValues = array("on");


		if (!in_array($key, $dontParseKeys) && !in_array($item1, $dontParseValues) && $item1 != "" && !is_numeric($item1))
		{
			//echo ".....".$item1." -->";
			//echo "Int?:".is_numeric($item1);
			//echo "<br>";
			$item1 = preg_replace("/\r\n|\n\r|\n|\r/", "\n", $item1);



			//print_r($item1);
			preg_match_all("/~([^|]*)/", $item1, $lists);
			//echo "<br><strong>Starte...</strong><br><strong>Lists:</strong>";
			//echo "<br>";

			for ($lauf1 = 0; $lauf1 < count($lists[0]); $lauf1++)
			{
				$lists[0][$lauf1] = $lists[0][$lauf1]."\n";
				$completeOld[] = $lists[0][$lauf1];
				//echo "Lists[$lauf1]:".$lists[0][$lauf1]."<br>";
				$temp = preg_split("/~/", $lists[0][$lauf1]);
				//echo "<strong>tmp:</strong>"; print_r($temp); echo "<br>";
				$temp = count($temp);
				//echo "<strong>Aufz�hlungspunkte:</strong>$temp<br>";
				//if ($temp==2){ // Nur ein Listenpunkt
				//$matches[1]=split("~",$lists[0][$lauf1]);
				//preg_match_all("/~(([^~]|[^\n])*)/",$lists[0][$lauf1],$matches);
				//echo "<strong>Nur ein Listenpunkt!</strong><br>";
				//}
				//else{ // n- Listenpunkte
				//preg_match_all("/~([^\n]*)/",$lists[0][$lauf1],$matches);
				$matches[1] = preg_split("/~/", $lists[0][$lauf1]);
				//echo "<strong>".count($matches[1])." Listenpunkte!</strong><br>";
				//}
				if (count($matches[1]) == 2)
				{
					//echo "<strong>Bearbeite einen Listenpunkt</strong><br>";
					$new = "<ul><li>".$matches[1][1]."</li></ul>";
					$completeNew[] = $new;
					//$matches[1][1]=substr($matches[1][1],0,strlen($matches[1][1])-1);
					//$item1=str_replace($matches[1][1],$new,$item1);
					//echo "<strong>Listenpunkt jetzt: '$item1'</strong><br>";
					$completeNewEntries[]["anzahl"] = 1;
					$completeNewEntries[count($completeNewEntries) - 1]["eintrag"] = count($completeNewEntries) - 1;
				}
				else
				{
					$new = "";
					for ($lauf = 0; $lauf < count($matches[1]); $lauf++)
					{
						//echo "<strong>Bearbeite Listenpunkt '".$matches[1][$lauf]."'($lauf)</strong><br>";
						if ($matches[1][$lauf] != "")
						{
							if ($lauf == 1)
							{
								//echo "Erster Punkt<br>";
								$new = "<ul><li>".$matches[1][$lauf]."</li>";
							}
							elseif (count($matches[1]) - $lauf == 1)
							{
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
					$completeNewEntries[]["anzahl"] = $lauf - 1;
					$completeNewEntries[count($completeNewEntries) - 1]["eintrag"] = count($completeNewEntries) - 1;
					$completeNew[] = $new;
				}
			}

			for ($lauf = 0; $lauf < count($completeNew); $lauf++)
			{

				//echo "New: ".htmlspecialchars($completeNew[$lauf])."<br>";
				//echo "Old: ".htmlspecialchars($completeOld[$lauf])."<br>";
				//$pattern="/".trim($completeOld[$lauf])."/i";
				//$lists[0][$lauf] = preg_replace($pattern,$completeNew[$lauf],trim($lists[0][$lauf]));
				$lists[0][$lauf] = str_replace(trim($completeOld[$lauf]), $completeNew[$lauf], trim($lists[0][$lauf]));
				//echo "Ergebnis:<br>".trim($lists[0][$lauf])."<br><br>";
				//echo "lists[0][$lauf]: ".htmlspecialchars($lists[0][$lauf])."<br>";
			}

			for ($lauf = 0; $lauf < count($lists[0]); $lauf++)
			{
				// Jeden Ausrduck vom Anfang des Strings item1 an suchen und ersetzten
				//echo strpos ($item1, $lists[0][$lauf]);
				//$pattern="/".trim($completeOld[$lauf])."/i";
				//$item1 = preg_replace($pattern,$lists[0][$lauf],trim($item1));
				$item1 = str_replace(trim($completeOld[$lauf]), $lists[0][$lauf], trim($item1));
				//echo "lists[0][$lauf]: ".htmlspecialchars($lists[0][$lauf])."<br>";
			}

			//echo $item1;
			$item1 = str_replace("|", "\n\n", $item1);
			$item1 = str_replace("~", "", $item1);
			$item1 = str_replace("\n", "<br>", $item1);
			$item1 = str_replace("\\", "", $item1);
			$item1 = str_replace("</ul><br><br>", "</ul>", $item1);
			$item1 = str_replace("<br><ul>", "<ul>", $item1);
			$item1 = str_replace("<br><br></li>", "</li>", $item1);
			$item1 = str_replace("<br></li>", "</li>", $item1);
			$item1 = str_replace("\$UID", $UID, $item1);
			$item1 = str_replace("</ul> <br><br>", "</ul>", $item1);
			$item1 = str_replace("</ul>  <br><br>", "</ul>", $item1);
			$item1 = str_replace("http://../", "../", $item1);
			$item1 = str_replace("intern://", "", $item1);
			$item1 = str_replace("<ul><br><li>", "<ul><li>", $item1);
			$item1 = str_replace("?UID", "?UID=".$UID, $item1);
			$item1 = str_replace("&UID", "&UID=".$UID, $item1);

			// <br> am Ende entfernen
		}
	}

	/**
	 * Hilfsfunktion für SQLiteManager:: Sonderzeichen aus Datenbankinhalten entfernen
	 * @param	string 		$string 	String der umgewandelt werden soll
	 * @return 	string
	 * @access 	private
	 */
	static function sqlite_convert_string_to_db($string)
	{

		$string = str_replace("&amp;", "&", $string);
		$string = str_replace("&#8211;", "-", $string);
		$string = str_replace("&#8217;", "'", $string);
		$string = str_replace("&#8222;", "'", $string);
		$string = str_replace("&#8220;", "'", $string);
		$string = str_replace("&#8218;", "'", $string);
		$string = str_replace("&# ;", "'", $string);
		$string = str_replace("&nbsp;", " ", $string);
		$string = sqlite_escape_string($string);
		return $string;
	}

	/**
	 * Hilfsfunktion für SQLiteManager:: Sonderzeichen aus Datenbankinhalten entfernen
	 * @param	string 		$string 	String der umgewandelt werden soll
	 * @return 	string
	 * @access private
	 */
	static function sqlite_convert_string_from_db(&$item1, $key)
	{

		global $UID;

		$item1 = str_replace("%UID%", "UID=".$UID, $item1);
		$item1 = str_replace("intern://", "", $item1);
	}

	private function prepareBindData($data)
	{
		if (is_array($data))
		{
			// add where parameter data
			foreach ($data as $key => $value)
			{
				if (is_string($value))
				{
					$value = SQLiteManager::sqlite_convert_string_to_db($value);
				}
				$data[$key] = $value;
			}
		}

		return $data;
	}

	/**
	 * Merge the $existingData array with $dataToAdd array and prepare the string values.
	 *
	 * @param array $existingData
	 * @param type $dataToAdd
	 * @return array the merged array
	 */
	private function prepareAndAddBindData(array $existingData, $dataToAdd)
	{
		if (is_array($dataToAdd))
		{
			// add where parameter data
			foreach ($dataToAdd as $key => $value)
			{
				if (is_string($value))
				{
					$value = SQLiteManager::sqlite_convert_string_to_db($value);
				}
				if (is_int($key))
				{
					$existingData[] = $value;
				}
				else
				{
					$existingData[$key] = $value;
				}
			}
		}

		return $existingData;
	}

	/**
	 * Prepares and executes a query to get table meta data.
	 *
	 * @param	String $tableName	Name der Tabelle die geprüft werden soll
	 * @return PDOStatement | false
	 * @throws PDOException
	 * @access public
	 */
	private function FetchTableInfo($tableName)
	{
		$signature = "SQLiteManager::FetchTableInfo(string tableName)";
		if ($tableName == "")
		{
			die($this->Err->showError($signature, "Ungültiger Parameter, tableName ist leer"));
		}

		$query = "PRAGMA table_info ($tableName)";
		/* @var $statement PDOStatement */
		$statement = $this->PrepareStatement($query, $signature);
		if ($statement && $statement->execute())
		{
			return $statement;
		}

		return false;
	}

}
// class
?>
