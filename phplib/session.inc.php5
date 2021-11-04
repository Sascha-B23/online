<?php
/*****************************************************************************
* Folgende Variablen müssen VOR dem Einbinden dieses Skriptes gesetzt werden:
* $libDir							Enthält den File-Pfad zum phplib Verzeichnis 
* $DOMAIN_NAME						Kürzel der aktuellen Domain
* $SESSION_DONT_START				Wenn dieser Wert "true" ist, wird keine Session initialisiert (optional)
* $SESSION_DONT_SEND_HEADERS		Wenn dieser Wert "true" ist, werden keine Header an den Browser gesendet (optional)
*
* NACH dem Einbinden dieses Skriptes stehen alle Varibalen und Objekte aus folgenden Datien
* zur Verfügung:
* - domain.conf.php5
* - domain.conf.xxx.php5 (mit xxx=$DOMAIN_NAME)
* - domain.initialize_objects.xxx.php5 (mit xxx=$DOMAIN_NAME)
*
* und zusätzlich die folgenden globalen Varibalen:
* $UID			Enthält die aktuelle Session ID
* $LANG			Aktuelle Sprache ("de", "en" ..)
*
*****************************************************************************/
date_default_timezone_set('Europe/Berlin');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
///////////////////////////////	
// Speperators setzen
if( !stristr($_SERVER["SERVER_SOFTWARE"],"win") ){
	$sep=":";
	$libDir="";
}else{ 
	$sep=";";
	$libDir="";
}

////////////////////////
// Globale und domainspezifische Konfiguration laden
require($libDir."session.initialize.inc.php5");

////////////////////////
// PHP Umgebung initialisieren
ini_alter("session.use_cookies","0");
ini_alter("session.use_only_cookies","0");
ini_alter("session.use_trans_sid","1");
ini_alter("session.gc_maxlifetime","14400");
ini_alter("max_execution_time","60");

/////////////////////////////////
// Session starten wenn gewünscht
$user_agent = UserAgentManager::GetCurrentUserAgent();
if ($SESSION_DONT_START!==true)
{
	SessionManager::InitSession($PATH_TO_TMPFILES, $DOMAIN_SECURECHECK_NAME);
	$UID = SessionManager::GetInstance()->GetSessionID();
	if (isset($_GET['SKIP_CONSTRUCTION_SITE'])) $_SESSION['SKIP_CONSTRUCTION_SITE'] = true;
}

if (isset($SHOW_CONSTRUCTION_SITE) && $SHOW_CONSTRUCTION_SITE['show']===true && $_SESSION['SKIP_CONSTRUCTION_SITE']!==true)
{
	include($SHARED_FILE_SYSTEM_ROOT.'baustellenseite/index.php5');
	exit;
}

/////////////////////////////////
// Domain Objekte initialisieren
if ($DOMAIN_NAME!="")include("domain.initialize_objects.".$DOMAIN_NAME.".inc.php5");

//////////////////////////////
// Sprache nach URL setzten
if (strstr($_SERVER["REQUEST_URI"],"/de/"))
	$_SESSION['LANG']="de";
	
if (strstr($_SERVER["REQUEST_URI"],"/en/"))
	$_SESSION['LANG']="en";

if ($_SESSION['LANG']=="")
	$_SESSION['LANG']="de";

$LANG=$_SESSION['LANG'];

@eval("include_once '".$SHARED_FILE_SYSTEM_ROOT."revisionInfo.php';");

require($libDir."session.secure.inc.php5");
?>