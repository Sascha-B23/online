<?php
/*************************************************************************
* Zentrale Server-Konfigurationsdatei
*
* In dieser Datei werden alle globalen File- und HTTP-Pfade für alle Domains gesetzt.
* Wenn es sich um einen Server mit mehreren Domains handelt, muss in
* der jeweiligen Session noch die domain.conf.XXX.inkludiert werden!
* Diese Datei gilt für alle Domains auf dem Server und auch das Webinterface!
*
*
* Author(s):	Martin Walleczek <m.walleczek@stollvongati.com>
*		Stephan Walleczek <s.walleczek@stollvongati.com>
*		Andre Munzinger <a.munzinger@stollvongati.com>
*
* Created:	13.09.2002
* Changed:	16.03.2007 - Umstieg auf PHP5
*
* Version:	PHP Version 5
* Copyright (c) 2006 Stoll von Gáti GmbH www.stollvongati.com 
*************************************************************************/
if( strtolower($_SERVER['APP_ENV']) == "staging" )
{
	/*************************************************
	* WWW-Server-Config INTERN über lokales Netzwerk
	*************************************************/
	
	// Angaben bei mehreren Domains pro Server
	$SHARED_FILE_SYSTEM_ROOT="/WWW/kim_online/";					// Shared Domain Root aus Filesystemsicht
	$SHARED_WEBSERVER_ROOT="/kim_online/";							// Shared Domain Root aus Webserversicht
	$SHARED_HTTP_ROOT="http://".$_SERVER["HTTP_HOST"].$SHARED_WEBSERVER_ROOT;
	$SHARED_HTTPS_ROOT="https://".$_SERVER["HTTP_HOST"].$SHARED_WEBSERVER_ROOT;
	
	// Datenbank(en) Config	
	$SHARED_DATABASES[0]["DB_INFO"]="KIM-Online Datenbank";
	$SHARED_DATABASES[0]["DB_USER"]="root";
	$SHARED_DATABASES[0]["DB_PWD"]="Hd2gb1gd";
	$SHARED_DATABASES[0]["DB_HOST"]="localhost";
	$SHARED_DATABASES[0]["DB_NAME"]="kim_online";
	
	// Datenbank(en) Config	
	$SHARED_DATABASES[1]["DB_INFO"]="KIM-Online Datenbank Logging";
	$SHARED_DATABASES[1]["DB_USER"]="root";
	$SHARED_DATABASES[1]["DB_PWD"]="Hd2gb1gd";
	$SHARED_DATABASES[1]["DB_HOST"]="localhost";
	$SHARED_DATABASES[1]["DB_NAME"]="kim_online_logging";

}
elseif (strtolower(getenv('APP_ENV')) == "docker")
{
	/*************************************************
	* WWW-Config LAPTOP
	*************************************************/
	// Angaben bei mehreren Domains pro Server
	$SHARED_FILE_SYSTEM_ROOT=__DIR__."/../";					        // Shared Domain Root aus Filesystemsicht
	$SHARED_WEBSERVER_ROOT="/";							// Shared Domain Root aus Webserversicht
	$SHARED_HTTP_ROOT="http://".$_SERVER["HTTP_HOST"].$SHARED_WEBSERVER_ROOT;
	$SHARED_HTTPS_ROOT="https://".$_SERVER["HTTP_HOST"].$SHARED_WEBSERVER_ROOT;
	
	// Datenbank(en) Config	
	$SHARED_DATABASES[0]["DB_INFO"]="KIM-Online Datenbank";
	$SHARED_DATABASES[0]["DB_USER"]="root";
	$SHARED_DATABASES[0]["DB_PWD"]="root";
	$SHARED_DATABASES[0]["DB_HOST"]="mysql";
	$SHARED_DATABASES[0]["DB_NAME"]="kim_online";
	$SHARED_DATABASES[0]["DB_ENABLE_TRACER"] = false;
	
	$SHARED_DATABASES[1]["DB_INFO"]="KIM-Online Datenbank Logging";
	$SHARED_DATABASES[1]["DB_USER"]="root";
	$SHARED_DATABASES[1]["DB_PWD"]="root";
	$SHARED_DATABASES[1]["DB_HOST"]="mysql";
	$SHARED_DATABASES[1]["DB_NAME"]="kim_online_logging";
	$SHARED_DATABASES[1]["DB_ENABLE_TRACER"] = false;
	
	//$SHOW_CONSTRUCTION_SITE = Array('show' => true, 'datefrom' => mktime(16, 0, 0, 10, 12, 2012), 'dateuntil' => mktime(8, 0, 0, 10, 15, 2012) );
}
elseif (strtolower(getenv('APP_ENV')) == "development")
{
	/*************************************************
	* WWW-Config LAPTOP
	*************************************************/
	// Angaben bei mehreren Domains pro Server
	$SHARED_FILE_SYSTEM_ROOT=__DIR__."/../";					        // Shared Domain Root aus Filesystemsicht
	$SHARED_WEBSERVER_ROOT="/kim_online/";							// Shared Domain Root aus Webserversicht
	$SHARED_HTTP_ROOT="http://".$_SERVER["HTTP_HOST"].$SHARED_WEBSERVER_ROOT;
	$SHARED_HTTPS_ROOT="https://".$_SERVER["HTTP_HOST"].$SHARED_WEBSERVER_ROOT;
	
	// Datenbank(en) Config	
	$SHARED_DATABASES[0]["DB_INFO"]="KIM-Online Datenbank";
	$SHARED_DATABASES[0]["DB_USER"]="root";
	$SHARED_DATABASES[0]["DB_PWD"]="root";
	$SHARED_DATABASES[0]["DB_HOST"]="mysql";
	$SHARED_DATABASES[0]["DB_NAME"]="kim_online";
	$SHARED_DATABASES[0]["DB_ENABLE_TRACER"] = false;
	
	$SHARED_DATABASES[1]["DB_INFO"]="KIM-Online Datenbank Logging";
	$SHARED_DATABASES[1]["DB_USER"]="root";
	$SHARED_DATABASES[1]["DB_PWD"]="root";
	$SHARED_DATABASES[1]["DB_HOST"]="mysql";
	$SHARED_DATABASES[1]["DB_NAME"]="kim_online_logging";
	$SHARED_DATABASES[1]["DB_ENABLE_TRACER"] = false;
	
	//$SHOW_CONSTRUCTION_SITE = Array('show' => true, 'datefrom' => mktime(16, 0, 0, 10, 12, 2012), 'dateuntil' => mktime(8, 0, 0, 10, 15, 2012) );
}
else if(strpos(__FILE__, 'kim_online_test')){
	
	/*************************************************
	* WWW-Server-Config ONLINE
	*************************************************/
	
	// Angaben bei mehreren Domains pro Server
	$SHARED_FILE_SYSTEM_ROOT="/homepages/32/d164859723/htdocs/kim_online_test/online/";	// Shared Domain Root aus Filesystemsicht
	$SHARED_WEBSERVER_ROOT="/";															// Shared Domain Root aus Webserversicht
	$SHARED_HTTP_ROOT="https://kim-online-test.seybold-fm.com".$SHARED_WEBSERVER_ROOT;
	$SHARED_HTTPS_ROOT="https://kim-online-test.seybold-fm.com".$SHARED_WEBSERVER_ROOT;

	// Datenbank(en) Config
	$SHARED_DATABASES[0]["DB_INFO"]="KIM-Online Datenbank (Testsystem)";
	$SHARED_DATABASES[0]["DB_USER"]="dbo433231377";
	$SHARED_DATABASES[0]["DB_PWD"]="aGakd22t6_ai4Aa4";
	$SHARED_DATABASES[0]["DB_HOST"]="localhost:/tmp/mysql5.sock";
	$SHARED_DATABASES[0]["DB_NAME"]="db433231377";
	
	// Datenbank(en) Config	
	$SHARED_DATABASES[1]["DB_INFO"]="KIM-Online Datenbank Logging (Testsystem)";
	$SHARED_DATABASES[1]["DB_USER"]="dbo442399118";
	$SHARED_DATABASES[1]["DB_PWD"]="jkAFk_gar_4aggmn4a2";
	$SHARED_DATABASES[1]["DB_HOST"]="localhost:/tmp/mysql5.sock";
	$SHARED_DATABASES[1]["DB_NAME"]="db442399118";
	
	//$SHOW_CONSTRUCTION_SITE = Array('show' => true, 'datefrom' => mktime(16, 0, 0, 10, 12, 2012), 'dateuntil' => mktime(18, 0, 0, 10, 16, 2012) );
	
}else{
	/*************************************************
	* WWW-Server-Config ONLINE
	*************************************************/
	
	// Angaben bei mehreren Domains pro Server
	$SHARED_FILE_SYSTEM_ROOT="/kunden/homepages/32/d164859723/htdocs/kim_online/online/";	// Shared Domain Root aus Filesystemsicht
	$SHARED_WEBSERVER_ROOT="/";																// Shared Domain Root aus Webserversicht
	$SHARED_HTTP_ROOT="https://kim-online.seybold-fm.com".$SHARED_WEBSERVER_ROOT;
	$SHARED_HTTPS_ROOT="https://kim-online.seybold-fm.com".$SHARED_WEBSERVER_ROOT;

	// Datenbank(en) Config
	$SHARED_DATABASES[0]["DB_INFO"]="KIM-Online Datenbank";
	$SHARED_DATABASES[0]["DB_USER"]="dbo436298921"; // NKAS: "dbo317995881";
	$SHARED_DATABASES[0]["DB_PWD"]="djFAj_m3aaDKl32"; //  NKAS: "Jfgt7dfzhdh";
	$SHARED_DATABASES[0]["DB_HOST"]="localhost:/tmp/mysql5.sock";
	$SHARED_DATABASES[0]["DB_NAME"]="db436298921"; // NKAS: // "db317995881";
	
	// Datenbank(en) Config	
	$SHARED_DATABASES[1]["DB_INFO"]="KIM-Online Datenbank Logging";
	$SHARED_DATABASES[1]["DB_USER"]="dbo442399271";
	$SHARED_DATABASES[1]["DB_PWD"]="lkag4_zrAgjn_aungo432r";
	$SHARED_DATABASES[1]["DB_HOST"]="localhost:/tmp/mysql5.sock";
	$SHARED_DATABASES[1]["DB_NAME"]="db442399271";
	
	//$SHOW_CONSTRUCTION_SITE = Array('show' => true, 'datefrom' => mktime(20, 0, 0, 07, 31, 2013), 'dateuntil' => mktime(7, 0, 0, 08, 01, 2013) );
}

// Pfad zu Package Verzeichnis
$SHARED_PACKAGE_DIR = $SHARED_FILE_SYSTEM_ROOT."packages/global/";	// Verzeichnis in dem die Shared-Packages (für alle Domains) liegen.

/*************************************************************************
* Shared Include Verzeichnisse
* Include-Dirs für ALLE Domains, von $SHARED_FILE_SYSTEM_ROOT aus gesehen
*************************************************************************/
$INCLUDE_DIRS_SHARED=array("phplib/","phplib/classes/","templates/","templates/menu/","scripts/", "phplib/pear/PEAR");

$CALENDAR_UID = "rosskopf-seybold.de/nkas/";

?>