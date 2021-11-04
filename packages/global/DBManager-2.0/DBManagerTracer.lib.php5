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
class DBManagerTracer extends DBManager {
	
	/**
	* DBManager-Objekt an welches die Funktionsaufrufe weitergeleitet werden
	* @param DBManager
	* @access protected
	*/
	protected $dbManager = null;
	
	/**
	* Infos über die aufgerufenen Funktionen
	* @param Array
	* @access protected
	*/
	protected $traceInfos = Array();
	
	/**
	* Konstruktor
	* @param DBManager $dbManager			Datenbank
	* @access public
	*/
	public function DBManagerTracer($dbManager){
		$this->dbManager=$dbManager;
	}
	
	public function GetDBManagerObj(){
		return $this->dbManager;
	}
	
	/*
	public function __call($name, $arguments){
		$returnValue="";
		$evalString = "\$returnValue=\$this->dbManager->".$name."(";
		for( $a=0; $a<count($arguments); $a++){
			if( $a>0 )$evalString.=", ";
			$evalString.="\$arguments[".$a."]";
		}
		$evalString.=");";
		echo $evalString."<br/>";
		eval($evalString);
		return $returnValue;
	}*/
	
	/**
	* Führt die übergebene SQL query aus und gibt bei Erfolg die Query-ID zurück 
	* @param string $query				SQL-Query
	* @return ResourceID 				query_id
	* @access public
	*/
	public function Query($query){
		$info=Array();
		$info["param"]=Array("query" => $query);
		$timeBefore = microtime(true);
		
		$returnValue = $this->dbManager->Query($query);
		
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["Query"][]=$info;
		return $returnValue;
	}
	
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
	public function CreateTable($tableName, $rowName, $rowParam, $primaryKey="pkey", $primaryKeyType="BIGINT"){
		$info=Array();
		$info["param"]=Array("tableName" => $tableName, "rowName" => $rowName, "rowParam" => $rowParam, "primaryKey" => $primaryKey, "primaryKeyType" => $primaryKeyType);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->CreateTable($tableName, $rowName, $rowParam, $primaryKey, $primaryKeyType);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["CreateTable"][]=$info;
		return $returnValue;
	}
	
	/**
	* Löscht eine Tabelle
	* @param string $tableName 			Name der zu löschenden Tabelle
	* @return bool 						Erfolg 
	* @access public
	*/
	public function DeleteTable($tableName){
		$info=Array();
		$info["param"]=Array("tableName" => $tableName);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->DeleteTable($tableName);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["DeleteTable"][]=$info;
		return $returnValue;
	}
	
	/**
	* Prüft ob die übergebene Tabelle vorhanden ist
	* @param string $tableName			Name der Tabelle die geprüft werden soll
	* @return bool 						Ja = true, Nein =false 
	* @access public
	*/
	public function TableExists($tableName){
		$info=Array();
		$info["param"]=Array("tableName" => $tableName);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->TableExists($tableName);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["TableExists"][]=$info;
		return $returnValue;
	}
	
	/**
	* Gibt die Namen der Tabellenspalten in einem Array zurück
	* @param string $tableName			Name der Tabelle deren Spaltennamen zurückgegeben werden soll
	* @return array						Namen der Tabellenspalten
	* @access public
	*/
	public function GetTableRows($tableName){
		$info=Array();
		$info["param"]=Array("tableName" => $tableName);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->GetTableRows($tableName);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["GetTableRows"][]=$info;
		return $returnValue;
	}
	
	/**
	* Prüft ob die gesuchte Spalte in der angegebenen Tabelle vorhanden ist
	* @param string $tableName 			Tabellenname
	* @param string $rowName 			Spaltenname
	* @return bool						Ja = true, Nein =false 
	* @access public
	*/
	public function TableRowExists($tableName,$rowName){
		$info=Array();
		$info["param"]=Array("tableName" => $tableName, "rowName" => $rowName);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->TableRowExists($tableName,$rowName);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["TableRowExists"][]=$info;
		return $returnValue;
	}	
	
	/**
	* Fügt eine neue Spalte in Tabelle ein
	* @param string $tableName 			Tabellenname
	* @param string $rowName 			Spaltenname
	* @param string $rowType 			Parameter der neuen Spalte (Datentyp etc.)
	* @return bool						Erfolg
	* @access public
	*/
	public function InsertRow($tableName,$rowName,$rowType){
		$info=Array();
		$info["param"]=Array("tableName" => $tableName, "rowName" => $rowName, "rowType" => $rowType);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->InsertRow($tableName,$rowName,$rowType);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["InsertRow"][]=$info;
		return $returnValue;
	}	
	
	/**
	* Fügt in die angegebene Tabelle in die angegebenen Spalten die angegebenen Daten ein 
	* @param string $tableName 			Tabelle in die eingefügt werden soll
	* @param array $rowName 			Spaltennamen in die eingefügt werden soll
	* @param array $rowData 			Daten die in die genannten Spalten eingefügt werden sollen
	* @param bool $allowInsertToIdent 	
	* @return string IDENTITY			Identität des Inserts (pkey der Zeile)
	* @access public
	*/
	public function Insert($tableName,$rowName,$rowData,$allowInsertToIdent=false){
		$info=Array();
		$info["param"]=Array("tableName" => $tableName, "rowName" => $rowName, "rowData" => $rowData, "allowInsertToIdent" => $allowInsertToIdent);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->Insert($tableName,$rowName,$rowData,$allowInsertToIdent);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["Insert"][]=$info;
		return $returnValue;
	}

	/**
	* Fügt in die Tabelle tableName in die Spalten rowName die Daten aus rowData ein. 
	* @param string $tableName 			Tabelle in die eingefügt werden soll
	* @param array $rowName 			Spaltennamen deren Inhalt geändert werden sollen
	* @param array $rowData 			Neue Inhalte der zu ändernden Spalten
	* @param array $where 				SQL-Bedingung der zu ändernden Datensätze
	* @return bool						Erfolg	 
	* @access public
	*/
	public function Update($tableName,$rowName,$rowData,$where){
		$info=Array();
		$info["param"]=Array("tableName" => $tableName, "rowName" => $rowName, "rowData" => $rowData, "where" => $where);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->Update($tableName,$rowName,$rowData,$where);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["Update"][]=$info;
		return $returnValue;
	}	
	
	/**
    * Löscht die Spalte rowName in der Tabelle tableName
	* @param	string $tableName 		Tabellenname
	* @param	string $rowName 		Name der Spalte die gelöscht werden soll
    * @return boolean
    * @access public
    */
	public function DeleteRow($tableName,$rowName){
		$info=Array();
		$info["param"]=Array("tableName" => $tableName, "rowName" => $rowName);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->DeleteRow($tableName,$rowName);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["DeleteRow"][]=$info;
		return $returnValue;
	}
	
	/**
	* Ändert den Datensatz pkey in Tabelle tableName. Nicht genannte Spalten bleiben unberührt.
	* @param string $tableName 			Tabelle in die eingefügt werden soll
	* @param array $rowName 			Spaltennamen deren Inhalt geändert werden sollen
	* @param array $rowData 			Neue Inhalte der zu ändernden Spalten
	* @param int $pkey 					Pkey des zu ändernden Datensatztes
	* @return bool	 					Erfolg
	* @access public
	*/
	public function UpdateByPkey($tableName,$rowName,$rowData,$pkey){
		$info=Array();
		$info["param"]=Array("tableName" => $tableName, "rowName" => $rowName, "rowData" => $rowData, "pkey" => $pkey);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->UpdateByPkey($tableName,$rowName,$rowData,$pkey);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["UpdateByPkey"][]=$info;
		return $returnValue;
	}	
		
	/**
	* Löscht Datensatz pkey in Tabelle tableName
	* @param string $tableName 			Tabelle aus der gelöscht werden soll
	* @param int $pkey 					Pkey des zu löschenden Datensatztes
	* @return bool						Erfolg 
	* @access public
	*/
	public function DeleteByPkey($tableName,$pkey){
		$info=Array();
		$info["param"]=Array("tableName" => $tableName, "pkey" => $pkey);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->DeleteByPkey($tableName,$pkey);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["DeleteByPkey"][]=$info;
		return $returnValue;
	}	
	
	/**
    * Löscht Datensatz pkey in Tabelle tableName
	* @param	string $tableName 	Tabelle aus der gel�scht werden soll
	* @param	int $pkey 		Pkey des zu l�schenden Datensatztes
    * @return boolean 
    * @access public
    */
	public function Delete($tableName, $where){
		$info=Array();
		$info["param"]=Array("tableName" => $tableName, "where" => $where);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->Delete($tableName, $where);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["Delete"][]=$info;
		return $returnValue;
	}
	
	/**
	* Führt die übergebene SQL query aus und gibt die Ergebnisse in einem assoziativen Array zurück
	* 
	* @param string $query 				SQL Abfrage
	* @param boolean $selectOnline		Selektiert nur Datensätzte die nach dem WI2.x Tabellenformat online sind
	* @return array 					Array mit den Ergebinssen der Datenbankabfrage
	* @access public
	*/
	public function SelectAssoc($query, $selectOnline=false){
		$info=Array();
		$info["param"]=Array("query" => $query, "selectOnline" => $selectOnline);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->SelectAssoc($query, $selectOnline);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["SelectAssoc"][]=$info;
		return $returnValue;
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
	function SelectHtmlAssoc($query, $selectOnline=false){
		$info=Array();
		$info["param"]=Array("query" => $query, "selectOnline" => $selectOnline);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->SelectHtmlAssoc($query, $selectOnline);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["SelectHtmlAssoc"][]=$info;
		return $returnValue;
	}
	
	/**
	* Führt die übergebene SQL query aus und gibt die Ergebnisse in einem Array zurück: Array[index][spalte 0][spalte 1]...
	* @param string $query 				SQL Abfrage
	* @return array 					Array mit den Ergebinssen der Datenbankabfrage
	* @access public
	*/
	public function Select($query){
		$info=Array();
		$info["param"]=Array("query" => $query);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->Select($query);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["Select"][]=$info;
		return $returnValue;
	}	
	
	/**
	* Erzeugt einen Index
	* @param string $tableName 			Tabellenname
	* @param string $rowName 			Spaltenname
	* @param bool $unique 				Soll der Index unique sein?
	* @access public
	*/
	public function CreateIndex($tableName, $rowName, $unique=false)
	{
		$rowNameParam = $rowName;
		if (is_array($rowNameParam))
		{
			$rowNameParam = implode(", ", $rowNameParam);
		}

		$info=Array();
		$info["param"]=Array("tableName" => $tableName, "rowName" => $rowNameParam, "unique" => $unique);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->CreateIndex($tableName, $rowName, $unique);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["CreateIndex"][]=$info;
		return $returnValue;
	}
	
	/**
	* Hilfsfunktion zum entfernenen von Sonderzeichen aus Datenbankinhalten
	* @param string $string 			String
	* @return string  					DBString
	* @access public
	*/
	public function ConvertStringToDBString($string){
		//$info=Array();
		//$info["param"]=Array("string" => $string);
		//$timeBefore = microtime(true);
		$returnValue = $this->dbManager->ConvertStringToDBString($string);
		//$info["callTime"] = microtime(true)-$timeBefore;
		//$this->traceInfos["ConvertStringToDBString"][]=$info;
		return $returnValue;
	}
	
	/**
	* Erzeugt oder aktuallsiert die Tabelle entsprechend der übergebenen Tabellenkonfiguration
	* @param string $config 			Tabellenkonfiguration
	* @param bool $forceUpdate 			Wenn true, dann wird der Abgleich der übergeben Konfiguration mit der vorhandenen DB-Table erzwungen 
	* @return bool						Erfolg
	* @access public
	*/
	public function CreateOrUpdateTable($config, $forceUpdate=false){
		$info=Array();
		$info["param"]=Array("config" => $config,"forceUpdate" => $forceUpdate);
		$timeBefore = microtime(true);
		$returnValue = $this->dbManager->CreateOrUpdateTable($config, $forceUpdate);
		$info["callTime"] = microtime(true)-$timeBefore;
		$this->traceInfos["CreateOrUpdateTable"][]=$info;
		return $returnValue;
	}
	
	/**
	 * Output a overview of the traced data
	 */
	public function PrintDebugOverviewInfo()
	{
		$timeInfo = Array();
		$functionNames = array_keys($this->traceInfos);
		for ($i=0; $i<count($functionNames); $i++)
		{
			$timeInfo[$i]["name"] = $functionNames[$i] ;
			$timeInfo[$i]["calls"] = count($this->traceInfos[$functionNames[$i]]);
			$timeInfo[$i]["summe"] = 0 ;
			for ($a = 0 ; $a < count($this->traceInfos[$functionNames[$i]]) ; $a++)
			{
				if ($this->traceInfos[$functionNames[$i]][$a]["callTime"]<0 ) $this->traceInfos[$functionNames[$i]][$a]["callTime"] = 0.0; 
				$timeInfo[$i]["summe"] = $timeInfo[$i]["summe"] + round($this->traceInfos[$functionNames[$i]][$a]["callTime"], 5);
			}
		}
		?>
		<table border="1" align = "center">
			<tr>
				<td>Funktion</td>
				<td>Anzahl Aufrufe</td>
				<td>Benötigte Zeit in Sekunden</td>
			</tr>
		<?	$totalTime = 0.0;
			foreach ($timeInfo as $value)
			{
				$totalTime+=$value["summe"]; ?>
				<tr>
					<td><?=$value["name"]?></td>
					<td><?=$value["calls"]?></td>
					<td><?=$value["summe"]?></td>
				</tr>
		<?	}?>
			<tr>
				<td colspan="2">&#160;</td>
				<td><?=$totalTime;?></td>
			</tr>
		</table>
		<?
	}
	
	/**
	 * Output the traced data in detail
	 */
	public function PrintDebugInfo()
	{
		$functionNames = Array();
		$functionNames = array_keys($this->traceInfos);
		
		for($i = 0; $i < count($functionNames) ; $i++){
			$zeit[$i]["name"] = $functionNames[$i] ;
			$zeit[$i]["calls"] = count($this->traceInfos[$functionNames[$i]]);
			$zeit[$i]["summe"] = 0 ;
			$paramNames = array_keys($this->traceInfos[$functionNames[$i]][0]["param"]);
				
		?>
			<table border="1" align = "center">
				<tr>
					<td colspan="<?=count($paramNames)+2?>"><?=$functionNames[$i]?></td>
				</tr>
				<tr>
					<td width="40"></td>
					<?for($a = 0 ; $a < count($paramNames) ; $a++){?>
						<td>&nbsp;<?=$paramNames[$a]?></td>
					<?}?>
					<td>Zeit</td>
				<tr>
				<?for($a = 0 ; $a < count($this->traceInfos[$functionNames[$i]]) ; $a++){
					if( $this->traceInfos[$functionNames[$i]][$a]["callTime"]<0 )$this->traceInfos[$functionNames[$i]][$a]["callTime"]=0.0; 
					$zeit[$i]["summe"] = $zeit[$i]["summe"] + round($this->traceInfos[$functionNames[$i]][$a]["callTime"], 5); ?>
					<tr>
						<td valign="top" align="center">&nbsp;<?=$a+1?></td>
						<?for($c = 0 ; $c < count($paramNames) ; $c++){?>
							<?if(is_array($this->traceInfos[$functionNames[$i]][$a]["param"][$paramNames[$c]]) || is_object($this->traceInfos[$functionNames[$i]][$a]["param"][$paramNames[$c]])){?>
								<td><pre><?print_r($this->traceInfos[$functionNames[$i]][$a]["param"][$paramNames[$c]])?></pre></td>
							<?}else{?>
								<td>&nbsp;<?=$this->traceInfos[$functionNames[$i]][$a]["param"][$paramNames[$c]]?></td>
							<?}?>
						<?}?>
						<td>&nbsp;<?=$this->traceInfos[$functionNames[$i]][$a]["callTime"]?></td>
					<tr>
				<?}?>
				<tr>
					<td colspan="<?=count($paramNames)+2?>">Gesamtzeit = <?=$zeit[$i]["summe"]?></td>
				</tr>
			</table>
			<br />
			<br />
		
		<?}?>
		<table border="1" align = "center">
			<tr>
				<td>Funktion</td>
				<td>Aufrufe</td>
				<td>Zeit in Sekunden</td>
			</tr>
			<?for($a = 0 ; $a < count($zeit); $a++){?>
				<tr>
					<td><?=$zeit[$a]["name"]?></td>
					<td><?=$zeit[$a]["calls"]?></td>
					<td><?=$zeit[$a]["summe"]?></td>
				</tr>
			<?
				$gesamt = $gesamt + $zeit[$a]["summe"];
			}?>
			<tr>
				<td colspan="2">&#160;</td>
				<td><?=$gesamt;?></td>
			</tr>
		</table>

	<?
	}
	
} // class DBManagerTracer

?>