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
	$jenkinsJobMonitor = new JenkinsJobMonitor($JENKINS_SERVER_URL, "KIM-Online E-Mail Remainder for BL", $JENKINS_SERVER_USER, $JENKINS_SERVER_PORT);
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
 * Send email to BL User (ExceededDeadlines)
 * @param EMailManager $emailManager
 * @param string $to
 * @param Array $info
 */
function SendOverviewMailExceededDeadlines(DBManager $db, EMailManager $emailManager, $to, $prozesses)
{	
	global $DOMAIN_HTTPS_ROOT;
	$msg =$prozesses["fmsLeader"]->GetAnrede($db, 2).",<br /><br />";
	$msg.="in den folgenden Prozessen wurde die Bearbeitungsfrist überschritten:<br />\n";
	$msg.="</p><br />\n";
	foreach ($prozesses["currrentResponibleUser"] as $currrentResponibleUser)
	{
		$msg.="<strong>Prozessverantwortlicher Forderungsmanager: ".$currrentResponibleUser["fmsCurrrentResponibleUser"]->GetUserName()." (".count($currrentResponibleUser["prozesses"])." Stück)</strong>\n";
		$msg.="<ul>\n";
		foreach ($currrentResponibleUser["prozesses"] as $prozess)
		{
			$msg.="<li class=\"MsoNormal\">".$prozess."</li>\n";
		}
		$msg.="</ul><br />";
	}
	$msg.="<br />\n<p class=\"MsoNormal\">";
	$msg.="Mit freundlichen Grüßen<br /><strong>SEYBOLD GmbH</Strong>\n";
	//echo $msg;
	//exit;
	// TODO: Scharf schalten!
	$to = "f.schebesta@fm-seybold.com";
	return $emailManager->SendEmail($to, "Überblick: Bearbeitungsfrist überschritten", $msg, "Seybold FM <mnk@seybold-fm.com>", "", EMailManager::EMAIL_TYPE_HTML, Array("f.schebesta@fm-seybold.com"));
}

// Startinfo
SetInfo("------------ START --------------");

// BL über überschrittene Deadlines informieren
$prozesses = WorkflowManager::GetProzessesWithExceededDeadlinesForBl($db);
SetInfo("Anzahl zu benachrichtigenden BLs (überschrittener Deadline): ".count($prozesses));
foreach ($prozesses as $fmsEmail => $prozess) 
{
	// Email versenden
	if (!SendOverviewMailExceededDeadlines($db, $emailManager, $fmsEmail, $prozess))
	{
		SetError("EMail an '".$fmsEmail."' konnte nicht versendet werden");
	}
}

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