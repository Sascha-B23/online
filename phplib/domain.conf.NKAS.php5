<?php
/*************************************************************************
* Domainspezifische Server-Konfigurationsdatei
*
* In dieser Datei werden alle domainbezogenen Pfade, Includes und 
* Datenbankparameter gesetzt. Diese Datei gilt nur für eine Domain und 
* erfordert die voherige Einbindung der Serverkonfigurationsdatei 
* domain.config.inc.php5
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
	* WWW-Server-Config INTERN
	*************************************************/
	
	// Config einer Domain
	$DOMAIN_FILE_SYSTEM_ROOT="/WWW/kim_online/";				// Domain Root aus Filesystemsicht
	$DOMAIN_WEBSERVER_ROOT="/kim_online/";						// Domain Root aus Webserversicht
	$DOMAIN_HTTP_ROOT="http://".$_SERVER["HTTP_HOST"].$DOMAIN_WEBSERVER_ROOT;
	$DOMAIN_HTTPS_ROOT="https://".$_SERVER["HTTP_HOST"].$DOMAIN_WEBSERVER_ROOT;

	// SICHERHEIT: 	Wenn ein Referer angegeben ist, wird geprüft, ob einer der folgenden Texte in dieser URL vorhanden ist. Wenn nicht wird die Session zerstört und eine neue angelegt
	//			Anders formuliert:  Falls der Referer nicht die Website selbst ist, wird die Session zerstört und eine neue angelegt (löst Sicherheitsproblem bei z.B. Links von Google inkl. Session-IDs)
	$DOMAIN_SECURECHECK_NAME=Array($_SERVER["HTTP_HOST"]."/kim_online");

	// weitere Konfigurationsparameter
	$LOGFILES_FILESYSTEM_DIR=$SHARED_FILE_SYSTEM_ROOT."logfiles/";
	$ERROR_LOGFILE_NAME="error.log.NKAS.txt";
	$ERROR_MAILTO_ADRESS="";
	$EMAIL_MANAGER_OFFLINE_MODE = true;
	$EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL = "s.walleczek@stollvongati.com";
	
	// Jenkins Server for cronjob monitoring
	$JENKINS_SERVER_URL = "";
	$JENKINS_SERVER_PORT = 0;
	$JENKINS_SERVER_USER = "";
	
	// CalendarManager
	$CALENDAR_MANAGER_EMAIL = 'kim-online-test@fm-seybold.de';
	$CALENDAR_MANAGER_EMAIL_USER = 'kim-online-test@fm-seybold.de';
	$CALENDAR_MANAGER_EMAIL_PWD = 'kXunCkR.5ylrIT$eNnF?9:C-W3*tVj';
	$CALENDAR_MANAGER_EMAIL_IMAP_SERVER = 'imap.1und1.de';
	$CALENDAR_MANAGER_EMAIL_IMAP_PORT = 993;

}
elseif (strtolower(getenv('APP_ENV')) == "docker")
{
	/*************************************************
	* WWW-Config LAPTOP
	*************************************************/
	// Angaben bei mehreren Domains pro Server
	$DOMAIN_FILE_SYSTEM_ROOT=__DIR__."/../";					        // Shared Domain Root aus Filesystemsicht
    $DOMAIN_WEBSERVER_ROOT="/";						                    // Domain Root aus Webserversicht
	$DOMAIN_HTTP_ROOT="http://".$_SERVER["HTTP_HOST"].$DOMAIN_WEBSERVER_ROOT;
	$DOMAIN_HTTPS_ROOT="https://".$_SERVER["HTTP_HOST"].$DOMAIN_WEBSERVER_ROOT;
	
	//$SHOW_CONSTRUCTION_SITE = Array('show' => true, 'datefrom' => mktime(16, 0, 0, 10, 12, 2012), 'dateuntil' => mktime(8, 0, 0, 10, 15, 2012) );

	// weitere Konfigurationsparameter
	$LOGFILES_FILESYSTEM_DIR=$SHARED_FILE_SYSTEM_ROOT."logfiles/";
	$ERROR_LOGFILE_NAME="error.log.NKAS.txt";
	$ERROR_MAILTO_ADRESS="";
	$EMAIL_MANAGER_OFFLINE_MODE = true;
	// $EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL = "s.walleczek@stollvongati.com";

	// Jenkins Server for cronjob monitoring
	$JENKINS_SERVER_URL = "";
	$JENKINS_SERVER_PORT = 0;
	$JENKINS_SERVER_USER = "";

	// CalendarManager
	$CALENDAR_MANAGER_EMAIL = 'kim-online-test@fm-seybold.de';
	$CALENDAR_MANAGER_EMAIL_USER = 'kim-online-test@fm-seybold.de';
	$CALENDAR_MANAGER_EMAIL_PWD = 'kXunCkR.5ylrIT$eNnF?9:C-W3*tVj';
	$CALENDAR_MANAGER_EMAIL_IMAP_SERVER = 'imap.1und1.de';
	$CALENDAR_MANAGER_EMAIL_IMAP_PORT = 993;
}
elseif (strtolower(getenv('APP_ENV')) == "development")
{

	/*************************************************
	* WWW-Config LOCALHOST
	*************************************************/
	
	// Config einer Domain
	$DOMAIN_FILE_SYSTEM_ROOT=__DIR__."/../";				// Domain Root aus Filesystemsicht
	$DOMAIN_WEBSERVER_ROOT="/kim_online/";											// Domain Root aus Webserversicht
	$DOMAIN_HTTP_ROOT="http://".$_SERVER["HTTP_HOST"].$DOMAIN_WEBSERVER_ROOT;
	$DOMAIN_HTTPS_ROOT="https://".$_SERVER["HTTP_HOST"].$DOMAIN_WEBSERVER_ROOT;
	
	// SICHERHEIT: 	Wenn ein Referer angegeben ist, wird geprüft, ob einer der folgenden Texte in dieser URL vorhanden ist. Wenn nicht wird die Session zerstört und eine neue angelegt
	//			Anders formuliert:  Falls der Referer nicht die Website selbst ist, wird die Session zerstört und eine neue angelegt (löst Sicherheitsproblem bei z.B. Links von Google inkl. Session-IDs)
	$DOMAIN_SECURECHECK_NAME=Array($_SERVER["HTTP_HOST"]."/kim_online");

	
	// weitere Konfigurationsparameter
	$LOGFILES_FILESYSTEM_DIR=$SHARED_FILE_SYSTEM_ROOT."logfiles/";
	$ERROR_LOGFILE_NAME="error.log.NKAS.txt";
	$ERROR_MAILTO_ADRESS="";
	$EMAIL_MANAGER_OFFLINE_MODE = true;
	$EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL = "s.walleczek@stollvongati.com";
	
	// Jenkins Server for cronjob monitoring
	$JENKINS_SERVER_URL = "";
	$JENKINS_SERVER_PORT = 0;
	$JENKINS_SERVER_USER = "";
	
	// CalendarManager
	$CALENDAR_MANAGER_EMAIL = 'kim-online-test@fm-seybold.de';
	$CALENDAR_MANAGER_EMAIL_USER = 'kim-online-test@fm-seybold.de';
	$CALENDAR_MANAGER_EMAIL_PWD = 'kXunCkR.5ylrIT$eNnF?9:C-W3*tVj';
	$CALENDAR_MANAGER_EMAIL_IMAP_SERVER = 'imap.1und1.de';
	$CALENDAR_MANAGER_EMAIL_IMAP_PORT = 993;
	
}else if(strpos(__FILE__, 'kim_online_test')){
	/*************************************************
	* WWW-Server-Config ONLINE
	*************************************************/
	
	// Config einer Domain
	$DOMAIN_FILE_SYSTEM_ROOT="/homepages/32/d164859723/htdocs/kim_online_test/online/";		// Domain Root aus Filesystemsicht
	$DOMAIN_WEBSERVER_ROOT="/";																// Domain Root aus Webserversicht
	$DOMAIN_HTTP_ROOT="https://kim-online-test.seybold-fm.com".$DOMAIN_WEBSERVER_ROOT;
	$DOMAIN_HTTPS_ROOT="https://kim-online-test.seybold-fm.com".$DOMAIN_WEBSERVER_ROOT;
	
	// SICHERHEIT: 	Wenn ein Referer angegeben ist, wird geprüft, ob einer der folgenden Texte in dieser URL vorhanden ist. Wenn nicht wird die Session zerstört und eine neue angelegt
	//			Anders formuliert:  Falls der Referer nicht die Website selbst ist, wird die Session zerstört und eine neue angelegt (löst Sicherheitsproblem bei z.B. Links von Google inkl. Session-IDs)
	$DOMAIN_SECURECHECK_NAME=Array("seybold-fm.com");
	
	// weitere Konfigurationsparameter
	$LOGFILES_FILESYSTEM_DIR = $SHARED_FILE_SYSTEM_ROOT."logfiles/";
	$ERROR_LOGFILE_NAME="error.log.NKAS.txt";
	$ERROR_MAILTO_ADRESS="";
	$EMAIL_MANAGER_OFFLINE_MODE = true;
	$EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL = "f.schebesta@fm-seybold.com";
	
	// Jenkins Server for cronjob monitoring
	$JENKINS_SERVER_URL = "";
	$JENKINS_SERVER_PORT = 0;
	$JENKINS_SERVER_USER = "";
	
	// CalendarManager
	$CALENDAR_MANAGER_EMAIL = 'kim-online-test@fm-seybold.de';
	$CALENDAR_MANAGER_EMAIL_USER = 'kim-online-test@fm-seybold.de';
	$CALENDAR_MANAGER_EMAIL_PWD = 'kXunCkR.5ylrIT$eNnF?9:C-W3*tVj';
	$CALENDAR_MANAGER_EMAIL_IMAP_SERVER = 'imap.1und1.de';
	$CALENDAR_MANAGER_EMAIL_IMAP_PORT = 993;
	
}else{
	/*************************************************
	* WWW-Server-Config ONLINE
	*************************************************/
	
	// Config einer Domain
	$DOMAIN_FILE_SYSTEM_ROOT="/kunden/homepages/32/d164859723/htdocs/kim_online/online/";	// Domain Root aus Filesystemsicht
	$DOMAIN_WEBSERVER_ROOT="/";																// Domain Root aus Webserversicht
	$DOMAIN_HTTP_ROOT="https://kim-online.seybold-fm.com".$DOMAIN_WEBSERVER_ROOT;
	$DOMAIN_HTTPS_ROOT="https://kim-online.seybold-fm.com".$DOMAIN_WEBSERVER_ROOT;
	
	// SICHERHEIT: 	Wenn ein Referer angegeben ist, wird geprüft, ob einer der folgenden Texte in dieser URL vorhanden ist. Wenn nicht wird die Session zerstört und eine neue angelegt
	//			Anders formuliert:  Falls der Referer nicht die Website selbst ist, wird die Session zerstört und eine neue angelegt (löst Sicherheitsproblem bei z.B. Links von Google inkl. Session-IDs)
	$DOMAIN_SECURECHECK_NAME=Array("seybold-fm.com");
	
	// weitere Konfigurationsparameter
	$LOGFILES_FILESYSTEM_DIR = $SHARED_FILE_SYSTEM_ROOT."logfiles/";
	$ERROR_LOGFILE_NAME="error.log.NKAS.txt";
	$ERROR_MAILTO_ADRESS="fehlermeldungen@stollvongati.com";
	$EMAIL_MANAGER_OFFLINE_MODE = false;
	$EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL = "";
	
	// Jenkins Server for cronjob monitoring
	$JENKINS_SERVER_URL = "";
	$JENKINS_SERVER_PORT = 0;
	$JENKINS_SERVER_USER = "";
	
	// CalendarManager
	$CALENDAR_MANAGER_EMAIL = 'kim-online@fm-seybold.de';
	$CALENDAR_MANAGER_EMAIL_USER = 'kim-online@fm-seybold.de';
	$CALENDAR_MANAGER_EMAIL_PWD = 'z,6;I!R.ZUI?W8eePbFXhOdm#@#crn';
	$CALENDAR_MANAGER_EMAIL_IMAP_SERVER = 'imap.1und1.de';
	$CALENDAR_MANAGER_EMAIL_IMAP_PORT = 993;

	ini_alter("display_errors", "Off");
}

ini_alter("error_log", $LOGFILES_FILESYSTEM_DIR."php-errors.log");
ini_alter("log_errors", "On");


/*************************************************************************
* Domain-Include Verzeichnisse
* Include-Dirs für diese Domain
*************************************************************************/
// Include-Dirs von $DOMAIN_FILE_SYSTEM_ROOT aus gesehen
$INCLUDE_DIRS_DOMAIN=array("shared/","shared/menu/", "phplib", "templates", "templates/menu/", "ncas/", "packages/");
// Alle Dateien die für DIESE Domain includiert werden sollen
$INCLUDE_FILES_DOMAIN=array("AttributeNameMaper-1.0/AttributeNameMaper.lib.php5",
							"DynamicTableManager-1.0/DynamicTableManager.lib.php5", 
							"LoggingManager-1.0/LoggingManager.lib.php5",
							"KimFileDownloadManager-1.0/KimFileDownloadManager.lib.php5",
							"StandardTextManager-1.0/StandardTextManager.lib.php5",
							"Package.lib.php5",
							"CalendarManager-1.0/CalendarManager.lib.php5"
						);
// Alle Packages die für DIESE Domain includiert werden sollen (liegen unterhalb $SHARED_PACKAGE_DIR)
$INCLUDE_PACKAGES_DOMAIN=array(	"UserAgentManager-1.0/UserAgentManager.lib.php5", 
								"SessionManager-1.0/SessionManager.lib.php5", 
								"LanguageManager-2.0/ExtendedLanguageManager.lib.php5", 
								"ErrorManager-2.0/ErrorManager.lib.php5", 
								"DBManager-2.0/DBManagerFactory.lib.php5", 
								"EMailManager-1.0/EMailManager.lib.php5", 
								"FileDownloadManager-1.0/FileDownloadManager.lib.php5",
								"JenkinsJobMonitor-1.0/JenkinsJobMonitor.lib.php5",
								"AppointmentManager-1.0/AppointmentManager.lib.php5"
							);
?>