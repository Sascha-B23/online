<?php
/*****************************************************************************
* Folgende Variablen müssen VOR dem Einbinden dieses Skriptes gesetzt werden:
* $libDir			Enthält den File-Pfad zum phplib Verzeichnis 
* $DOMAIN_NAME	Kürzel der aktuellen Domain
*
* NACH dem Einbinden dieses Skriptes stehen alle Varibalen und Objekte aus folgenden Datien
* zur Verfügung:
* - domain.conf.php5
* - domain.conf.xxx.php5 (mit xxx=$DOMAIN_NAME)
* - domain.initialize_objects.xxx.php5 (mit xxx=$DOMAIN_NAME)
*
* und zusätzlich die folgenden globalen Varibalen:
* $UID			Enthält die aktuelle Session ID
*
*****************************************************************************/

///////////////////////////////	
// Speperators setzen
if (!stristr($_SERVER["SERVER_SOFTWARE"],"Win"))
{
	$sep=":";
}
else
{
	$sep=";";
}

///////////////////////////////	
// Shared Includefile einlesen
require($libDir."domain.conf.php5");

///////////////////////////////	
// Domain Includfile auslesen
if ($DOMAIN_NAME!="") require($libDir."domain.conf.".$DOMAIN_NAME.".php5");

/////////////////////////////////
// Include Pfade setzten
$iniPath = "";
for ($lauf=0; $lauf<count($INCLUDE_DIRS_DOMAIN); $lauf++)
{
	$iniPath.=$DOMAIN_FILE_SYSTEM_ROOT.$INCLUDE_DIRS_DOMAIN[$lauf].$sep;
}
for ($lauf=0; $lauf<count($INCLUDE_DIRS_SHARED); $lauf++)
{
	$iniPath.=$SHARED_FILE_SYSTEM_ROOT.$INCLUDE_DIRS_SHARED[$lauf].$sep;
}
for ($lauf=0; $lauf<count($INCLUDE_PACKAGES_DOMAIN); $lauf++)
{
	$iniPath.=$SHARED_PACKAGE_DIR.dirname($INCLUDE_PACKAGES_DOMAIN[$lauf])."/".$sep;
}
ini_alter("include_path", $iniPath);

/////////////////////////////////
// DOMAIN Package-Files includieren
for ($lauf=0; $lauf<count($INCLUDE_PACKAGES_DOMAIN); $lauf++)
{
	include_once($SHARED_PACKAGE_DIR.$INCLUDE_PACKAGES_DOMAIN[$lauf]);
}
///////////////////////////////////
// DOMAIN Include Files einbinden
for ($lauf=0; $lauf<count($INCLUDE_FILES_DOMAIN); $lauf++)
{
	require_once($INCLUDE_FILES_DOMAIN[$lauf]);
}
?>