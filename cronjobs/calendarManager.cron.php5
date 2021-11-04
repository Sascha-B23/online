<?php
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
$USER_HAVE_TO_BE_LOGGED_IN = false;
require_once("../phplib/session.inc.php5");
require_once $SHARED_PACKAGE_DIR.'EMailManager-1.0/EmailReader.lib.php5';


$emailManager = new EMailManager($SHARED_FILE_SYSTEM_ROOT."templates/emailSignatureSystem.html", "", $EMAIL_MANAGER_OFFLINE_MODE, $EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL);
$appointmentManager = new AppointmentManager($emailManager);
$calendarManager = new CalendarManager($db, $lm);
$appointmentManager->AddObserver($calendarManager);

$jenkinsJobMonitor = null;
if ($JENKINS_SERVER_URL!="")
{
	$jenkinsJobMonitor = new JenkinsJobMonitor($JENKINS_SERVER_URL, "KIM-Online CalendarManager", $JENKINS_SERVER_USER, $JENKINS_SERVER_PORT);
	$jenkinsJobMonitor->StartRun();
}

// Config
$logfile=$DOMAIN_FILE_SYSTEM_ROOT."logfiles/calendarManager.log.txt";
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


// Startinfo
SetInfo("------------ START --------------");
$emailReader = new EmailReader();
if ($emailReader->Connect($CALENDAR_MANAGER_EMAIL_IMAP_SERVER, $CALENDAR_MANAGER_EMAIL_IMAP_PORT, $CALENDAR_MANAGER_EMAIL_USER, $CALENDAR_MANAGER_EMAIL_PWD)===true)
{
	// Check INBOX
	$appointmentManager->CheckAppointmentEmailReplies($emailReader, new EmailReadActionDelete());//, new EmailReadActionDelete());
	$emailReader->Close();
}
else
{
	SetError("  Es konnte keine Verbindung zum Mail-Server '".$CALENDAR_MANAGER_EMAIL_IMAP_SERVER."' auf Port '".$CALENDAR_MANAGER_EMAIL_IMAP_PORT."' für das Postfach '".$CALENDAR_MANAGER_EMAIL."' aufgebaut werden");
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