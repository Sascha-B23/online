<?php
include_once("DBManager.lib.php5");	// Basisklasse DBManager

/**
* Factory-Klasse zum Erzeugen der DBManager-Instanzen  (MySQL, MSSQL etc.)
* 
* @access public
* @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
* @since    		PHP 5.0
* @version		1.0
* @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
*/
class DBManagerFactory {

	/**
	* DataBaseType
	* @var Enum
	* @access public
	*/
	const DBT_MYSQL = 1;
	const DBT_SQLITE = 2;
	const DBT_MYSQLI = 3;
	
	/**
	* Erzeugt die DBManager-Instanz für den übergebenen Datenbanktyp und gibt diese zurück
	* @param DataBaseType $dbType		Datenbanktyp
	* @param string $dbName			Datenbankname
	* @param string $dbHost				Datenbankserver
	* @param string $dbUser				Username
	* @param string $dbPWD			Passwort
	* @param ErrorManager $em			ErrorManager-Objekt
	* @param LanguageManager $lm		LanguageManager-Objekt
	* @return DBManager 				Datenbankobjekt oder null
	* @access public
	*/
	public static function CreateDBManagerInstance($dbType, $dbName, $dbHost, $dbUser, $dbPwd, &$em, &$lm, $enableDBTracer=false, $mysqlWaitTimeout=0){
		$dbManager=null;
		switch( (int)$dbType ){
			case DBManagerFactory::DBT_MYSQL:
				// DBManager für MySQL-Datenbank laden
				include_once("MySqlManager-2.0/MySqlManager.lib.php5");
				$dbManager = new MySqlManager($dbName, $dbHost, $dbUser, $dbPwd, $em, $enableDBTracer, $mysqlWaitTimeout);
				break;
			case DBManagerFactory::DBT_SQLITE:
				// DBManager für MySQL-Datenbank laden
				include_once("SQLiteManager-1.0/SQLiteManager.lib.php5");
				$dbManager = new SQLiteManager($dbName, $dbHost, $em, $enableDBTracer);
				break;
			case DBManagerFactory::DBT_MYSQLI:
				// DBManager für MySQL-Datenbank laden
				include_once("MySqlIManager-1.0/MySqlIManager.lib.php5");
				$dbManager = new MySqlIManager($em, $dbHost, $dbName, $dbUser, $dbPwd, "", "", $enableDBTracer);
				break;
			default:
				$em->showError("DBManagerFactory", "Can't initialize DBManager-instance: unknown DB-Type '".$dbType."'");
				exit;
		}
		// DBManagerTracer verwenden?
		if( $enableDBTracer ){
			// DBManagerTracer-Proxy einfügen
			include_once("DBManagerTracer.lib.php5");
			$dbManager = new DBManagerTracer($dbManager);
		}
		return $dbManager;
	}

}

?>