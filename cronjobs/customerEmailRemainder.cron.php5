<?php
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
$USER_HAVE_TO_BE_LOGGED_IN = false;
require_once("../phplib/session.inc.php5");

$emailManager = new EMailManager($SHARED_FILE_SYSTEM_ROOT."templates/emailSignatureSystem.html", "", $EMAIL_MANAGER_OFFLINE_MODE, $EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL);
$jenkinsJobMonitor = null;
if ($JENKINS_SERVER_URL!="")
{
	$jenkinsJobMonitor = new JenkinsJobMonitor($JENKINS_SERVER_URL, "KIM-Online E-Mail Remainder for Customers", $JENKINS_SERVER_USER, $JENKINS_SERVER_PORT);
	$jenkinsJobMonitor->StartRun();
}

// Config
$logfile=$DOMAIN_FILE_SYSTEM_ROOT."logfiles/customerEmailRemainder.log.txt";
$errors="";
$resultCode = 0;

// Funktionsdefinitionen
function SetError($msg)
{
	global $logfile;
	global $errors;
	global $resultCode;
	$msg=date("d.m.Y H:i",time()).";ERROR;".$msg."\n";
	error_log ($msg, 3, $logfile);
	$msg="<font color='#ee0000'>".str_replace("\n", "<br />", $msg)."</font>";
	echo $msg;
	$errors.=$msg;
	$resultCode = 1;
}
function SetInfo($msg)
{
	global $logfile;
	global $errors;
	$msg=date("d.m.Y H:i",time()).";INFO;".$msg."\n";
	error_log ($msg, 3, $logfile);
	$msg="<font color='#008800'>".str_replace("\n", "<br />", $msg)."</font>";
	echo $msg;
	$errors.=$msg;
}

/**
 * Send info email for cronjob
 * @param EMailManager $emailManage
 * @param array $errors 
 */
function SendCronjobInfoMail(EMailManager $emailManager, $errors)
{
	global $ERROR_MAILTO_ADRESS;
	$msg = str_replace("\n", "<br />", $errors);
	return $emailManager->SendEmail("s.walleczek@stollvongati.com", "Cronjob NKAS CustomerEmailRemainder", $msg, "Cronjob NKAS <mnk@seybold-fm.com>");
}


/**
 * Send email to user
 * @param EMailManager $emailManager
 * @param string $to
 * @param Array $info
 */
function SendCustomerMail(DBManager $db, EMailManager $emailManager, $to, $prozesses)
{
	global $DOMAIN_HTTPS_ROOT;
	$msg =$prozesses["responsibleUser"]->GetAnrede($db, 1).",<br /><br />";
	$newTasks = false;
	if (isset($prozesses["new"]) && count($prozesses["new"])>0)
	{
		$newTasks = true;
		$msg.="in KIM-Online ".(count($prozesses["new"])==1 ? "liegt eine" : "liegen ".count($prozesses["new"]))." neue Aufgaben für Sie vor.<br /><br />\n";
		if (isset($prozesses["exceeded"]) && count($prozesses["exceeded"])>0)
		{
			$msg.="Zudem ".(count($prozesses["exceeded"])==1 ? "ist noch eine" : "sind noch ".count($prozesses["exceeded"]))." unerledigte Aufgaben vorhanden.<br /><br />\n";
		}
	}
	else
	{
		if ($prozesses["exceeded"] && count($prozesses["exceeded"])>0)
		{
			$msg.="in KIM-Online ".(count($prozesses["exceeded"])==1 ? "liegt noch eine" : "liegen noch ".count($prozesses["exceeded"]))." unerledigte Aufgaben für Sie vor.<br /><br />\n";
		}
	}
	
	$msg.="Bitte loggen Sie sich <a href='".$DOMAIN_HTTPS_ROOT."'>hier</a> mit ihrem Benutzername und Kennwort ein.<br /><br />\n";
	$msg.="Mit freundlichen Grüßen<br /><strong>SEYBOLD GmbH</Strong>\n";
	// TODO: Scharf schalten!
	$to = "f.schebesta@fm-seybold.com";
	return $emailManager->SendEmail($to, "Es liegen ".($newTasks ? "neue" : "unerledigte")." Aufgaben vor", $msg, "Seybold FM <mnk@seybold-fm.com>", "", EMailManager::EMAIL_TYPE_HTML, Array("f.schebesta@fm-seybold.com"));
}

/**
 * Send email to user
 * @param EMailManager $emailManager
 * @param string $to
 * @param Array $info
 */
function SendCustomerNewProcessMail(DBManager $db, EMailManager $emailManager, $to, $prozesses)
{
	// TODO: Funktion löschen wenn Fn. SendCustomerMail scharf geschaltet wurde
	global $DOMAIN_HTTPS_ROOT;
	$msg =$prozesses["responsibleUser"]->GetAnrede($db, 1).",<br /><br />";
	$msg.="in KIM-Online ".(count($prozesses["prozesses"])==1 ? "liegt eine" : "liegen ".count($prozesses["prozesses"]))." neue Aufgaben für Sie vor.<br /><br />\n";
	$msg.="Bitte loggen Sie sich <a href='".$DOMAIN_HTTPS_ROOT."'>hier</a> mit ihrem Benutzername und Kennwort ein.<br /><br />\n";
	$msg.="Mit freundlichen Grüßen<br /><strong>SEYBOLD GmbH</Strong>\n";
	return $emailManager->SendEmail($to, "Es liegen neue Aufgaben vor", $msg, "Seybold FM <mnk@seybold-fm.com>", "", EMailManager::EMAIL_TYPE_HTML, Array("f.schebesta@fm-seybold.com"));
}

/**
 * Send email to FMS User (UnclassifiedFiles)
 * @param EMailManager $emailManager
 * @param string $to
 * @param Array $info
 */
function SendFmsMailUnclassifiedFiles(DBManager $db, EMailManager $emailManager, $to, $prozesses)
{	
	global $DOMAIN_HTTPS_ROOT;
	$msg =$prozesses["responsibleUser"]->GetAnrede($db, 2).",<br /><br />";
	$msg.="in den folgenden Prozessen müssen noch vom Kunden hochgeladene Dateien klassifiziert werden:<br />\n";
	$msg.="</p><br /><ul>\n";
	/*@var $prozess ProcessStatus*/
	foreach ($prozesses["prozesses"] as $prozess)
	{
		$msg.="<li class=\"MsoNormal\">".$prozess->GetProzessPath()[0]['path']."</li>\n";
	}
	$msg.="</ul><br />\n<p class=\"MsoNormal\">";
	$msg.="Bitte loggen Sie sich <a href='".$DOMAIN_HTTPS_ROOT."'>hier</a> mit ihrem Benutzername und Kennwort ein.<br /><br />\n";
	$msg.="Mit freundlichen Grüßen<br /><strong>SEYBOLD GmbH</Strong>\n";
	return $emailManager->SendEmail($to, "Es liegen Dateien zur Klassifizierung vor", $msg, "Seybold FM <mnk@seybold-fm.com>", "", EMailManager::EMAIL_TYPE_HTML, Array("f.schebesta@fm-seybold.com"));
}

/**
 * Send email to FMS User (ExceededDeadlines)
 * @param EMailManager $emailManager
 * @param string $to
 * @param Array $info
 */
function SendFmsMailExceededDeadlines(DBManager $db, EMailManager $emailManager, $to, $prozesses)
{	
	global $DOMAIN_HTTPS_ROOT;
	$msg =$prozesses["responsibleUser"]->GetAnrede($db, 2).",<br /><br />";
	$msg.="in den folgenden Prozessen wurde die Bearbeitungsfrist überschritten:<br />\n";
	$msg.="</p><br /><ul>\n";
	/*@var $prozess ProcessStatus*/
	foreach ($prozesses["prozesses"] as $prozess)
	{
		$msg.="<li class=\"MsoNormal\">".$prozess."</li>\n";
	}
	$msg.="</ul><br />\n<p class=\"MsoNormal\">";
	$msg.="Bitte loggen Sie sich <a href='".$DOMAIN_HTTPS_ROOT."'>hier</a> mit ihrem Benutzername und Kennwort ein.<br /><br />\n";
	$msg.="Mit freundlichen Grüßen<br /><strong>SEYBOLD GmbH</Strong>\n";
	return $emailManager->SendEmail($to, "Bearbeitungsfrist überschritten", $msg, "Seybold FM <mnk@seybold-fm.com>", "", EMailManager::EMAIL_TYPE_HTML, Array("f.schebesta@fm-seybold.com"));
}

// Am Wochenende keine Benachrichtigung (0=So 6=Sa)
$dayOfTheWeek = date('w', time());
if ($dayOfTheWeek==0 || $dayOfTheWeek==6)
{
	echo "Keine Benachrichtigungen am WE!";
	exit;
}


// Startinfo
SetInfo("------------ START --------------");

// Kunden über neue Aufgaben und überschrittene Deadlines benachrichtigen
$prozesses = WorkflowManager::GetProzessesForCustomerNotification($db);
SetInfo("Anzahl zu benachrichtigenden Kunden: ".count($prozesses));
foreach ($prozesses as $customrerEmail => $prozess) 
{
	// Email versenden
	if (SendCustomerMail($db, $emailManager, $customrerEmail, $prozess))
	{
		// Bei neuen Aufgaben entsprechend das Flag setzen
		if (isset($prozess['new']))
		{
			$whereClause="";
			foreach ($prozess['new'] as $value)
			{
				if ($whereClause!="") $whereClause.=" OR ";
				$whereClause.="pkey=".$value["pkey"];
			}
			$updateQuery="UPDATE ".ProcessStatus::TABLE_NAME." SET customerMailSend=".time()." WHERE (".$whereClause.")";
			//TODO:  DB-Update scharf schalten
			//echo $updateQuery;
			//$db->Query($updateQuery);
		}
	}
	else
	{
		SetError("EMail an '".$customrerEmail."' konnte nicht versendet werden");
	}
}

// Kunden über neue Aufgaben benachrichtigen
// TODO: Funktion löschen wenn Benachrichtigung zuvor scharf geschaltet wurde
$prozesses = WorkflowManager::GetProzessesNewToCustomer($db);
SetInfo("Anzahl zu benachrichtigenden Kunden (neue Aufgaben): ".count($prozesses));
foreach ($prozesses as $customrerEmail => $prozess) 
{
	// Update-Query erzeugen
	$whereClause="";
	foreach ($prozess["prozesses"] as $value)
	{
		if ($whereClause!="") $whereClause.=" OR ";
		$whereClause.="pkey=".$value["pkey"];
	}
	if (trim($whereClause)=="") continue;
	$updateQuery="UPDATE ".ProcessStatus::TABLE_NAME." SET customerMailSend=".time()." WHERE (".$whereClause.")";
	// Email versenden
	if (SendCustomerNewProcessMail($db, $emailManager, $customrerEmail, $prozess))
	{
		$db->Query($updateQuery);
	}
	else
	{
		SetError("EMail an '".$customrerEmail."' konnte nicht versendet werden");
	}
}



// FMS-Mitarbeiter über unklassifizierte Dateien benachrichtigen
$prozesses = WorkflowManager::GetProzessesWithUnclassifiedFiles($db);
SetInfo("Anzahl zu benachrichtigenden SFM-Mitarbeiter (unklassifizierte Dateien): ".count($prozesses));
foreach ($prozesses as $fmsEmail => $prozess) 
{
	// Email versenden
	if (!SendFmsMailUnclassifiedFiles($db, $emailManager, $fmsEmail, $prozess))
	{
		SetError("EMail an '".$fmsEmail."' konnte nicht versendet werden");
	}
}

// FMS-Mitarbeiter über überschrittene Deadlines informieren
/*$prozesses = WorkflowManager::GetProzessesWithExceededDeadlines($db);
SetInfo("Anzahl zu benachrichtigenden FMS-Mitarbeiter (überschrittener Deadline): ".count($prozesses));
foreach ($prozesses as $fmsEmail => $prozess) 
{
	// Email versenden
	if (!SendFmsMailExceededDeadlines($db, $emailManager, $fmsEmail, $prozess))
	{
		SetError("EMail an '".$fmsEmail."' konnte nicht versendet werden");
	}
}
*/

// Fertig
SetInfo("------------ ENDE ---------------");
if ($jenkinsJobMonitor!=null)
{
	if ($jenkinsJobMonitor->SubmitRun(strip_tags(str_replace("<br />", "\n", $errors)), $resultCode))
	{
		// Send no info mail when run was successfully submited to Jenkins-Server
		exit;
	}
	SetError("Can't submit run to Jenkins Server (".$jenkinsJobMonitor->GetErrorText().")");
}
// Send info mail
if ($EMAIL_MANAGER_OFFLINE_MODE!==true)
{
	//SendCronjobInfoMail($emailManager, $errors);
}