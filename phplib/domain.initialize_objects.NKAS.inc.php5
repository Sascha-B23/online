<?php
/*************************************************************************
* Domainspezifisch benötigte Objekte
*
* In dieser Datei werden alle domainbezogenen Objekte erzuegt
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
$em = new ErrorManager($LOGFILES_FILESYSTEM_DIR.$ERROR_LOGFILE_NAME,$ERROR_MAILTO_ADRESS);
$lm = new ExtendedLanguageManager($DOMAIN_FILE_SYSTEM_ROOT."languages/", "DE", "DE");
$db = DBManagerFactory::CreateDBManagerInstance(DBManagerFactory::DBT_MYSQL, $SHARED_DATABASES[0]["DB_NAME"], $SHARED_DATABASES[0]["DB_HOST"], $SHARED_DATABASES[0]["DB_USER"], $SHARED_DATABASES[0]["DB_PWD"], $em, $lm, isset($SHARED_DATABASES[0]["DB_ENABLE_TRACER"]) ? $SHARED_DATABASES[0]["DB_ENABLE_TRACER"] : false);
$db_logging = DBManagerFactory::CreateDBManagerInstance(DBManagerFactory::DBT_MYSQL, $SHARED_DATABASES[1]["DB_NAME"], $SHARED_DATABASES[1]["DB_HOST"], $SHARED_DATABASES[1]["DB_USER"], $SHARED_DATABASES[1]["DB_PWD"], $em, $lm, isset($SHARED_DATABASES[1]["DB_ENABLE_TRACER"]) ? $SHARED_DATABASES[1]["DB_ENABLE_TRACER"] : false);
LoggingManager::GetInstance($db_logging);

$userManager=new UserManager($db, SessionManager::GetInstance()->IsNewSession());

// init classes only if user is loged in
if (isset($_SESSION["currentUser"]))
{
	$fileManager=new FileManager($db);
	$addressManager=new AddressManager($db);
	$customerManager=new CustomerManager($db);
	$workflowManager=new WorkflowManager($db, true);
	$rsKostenartManager=new RSKostenartManager($db);
	// Init FileDownloadManager 
	if (!isset($_SESSION['fileDownloadManager'])) $_SESSION['fileDownloadManager'] = new KimFileDownloadManager();
	// DynamicTableManager initialisieren
	if (isset($_SESSION["dynamicTableManager"]))
	{
		$dynamicTableManager = $_SESSION["dynamicTableManager"];
	}
	else
	{
		$dynamicTableManager = DynamicTableManager::GetInstance($SHARED_HTTP_ROOT.'packages/DynamicTableManager-1.0/scripts/', $SHARED_HTTP_ROOT.'packages/DynamicTableManager-1.0/css/', $SHARED_HTTP_ROOT.'packages/DynamicTableManager-1.0/icons/');
		$_SESSION["dynamicTableManager"] = $dynamicTableManager;
	}
	//
	$emailManager = new EMailManager($SHARED_FILE_SYSTEM_ROOT."templates/emailSignatureSystem.html", "", $EMAIL_MANAGER_OFFLINE_MODE, $EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL);
	$appointmentManager = new AppointmentManager($emailManager);
}
?>