<?php
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$DONT_REDIRECT_SSL="true";
$SESSION_DONT_SEND_HEADERS="true";
session_cache_limiter(""); // WORKAROUND FÜR IE SSL-Download BUG mit Sessions!!!!
// Session laden
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_RSMITARBEITER;
require_once("../phplib/session.inc.php5");
require_once("../phplib/iCalcreator-2.12/iCalcreator.class.php");

// iCalendar erzeugen
$config = array( 'unique_id' => $CALENDAR_UID );
$v = new vcalendar( $config );
$tz = "Europe/Berlin";
$v->setProperty( 'method', 'PUBLISH' );
$v->setProperty( "x-wr-calname", "KIM-Online Telefontermine - ".utf8_encode($_SESSION["currentUser"]->GetUserName()) );
$v->setProperty( "X-WR-CALDESC", "KIM-Online Kalender mit Telefonterminen von ".utf8_encode($_SESSION["currentUser"]->GetUserName()) );
$v->setProperty( "X-WR-TIMEZONE", $tz );
$xprops = array( "X-LIC-LOCATION" => $tz );
iCalUtilityFunctions::createTimezone( $v, $tz, $xprops );
// Telefontermine hinzufügen...
$processes = $workflowManager->GetCalendarRelevantWorkflows($_SESSION["currentUser"]);
for ($a=0; $a<count($processes); $a++)
{
	$process = $processes[$a];
	/*var $process ProcessStatus */
	$date_start = $process->GetTelefontermin();
	$date_end = $process->GetTelefonterminEnde();
	// Wenn kein Endtermin hinterlegt ist 15 Minuten zum Startdatum hinzuzählen
	if ($date_end==0) $date_end=$date_start+60*15;
	$contact_person = $process->GetTelefonterminAnsprechpartner();
	$contact_person_email = "";
	$name_contact_person = "";
	if ($contact_person!=null)
	{
		$contact_person_email = utf8_encode($contact_person->GetEMail());
		if (is_a($contact_person, "AddressData")) $name_contact_person = utf8_encode(trim($contact_person->GetTitle2()." ".trim($contact_person->GetFirstName()." ".$contact_person->GetName())));
		if( $name_contact_person=="" )$name_contact_person = utf8_encode($contact_person->GetEMail());
		if( $name_contact_person=="" )$name_contact_person = "-";
	}
	$name_location = "???";
	$name_group = "???";
	$loaction = $process->GetLocation();
	if ($loaction!=null)
	{
		$name_location = utf8_encode($loaction->GetName());
		$company = $loaction->GetCompany();
		if ($company!=null)
		{
			$group = $company->GetGroup();
			if ($group!=null)
			{
				$name_group = utf8_encode($group->GetName());
			}
		}
	}
	
	// create an event calendar component
	$vevent = & $v->newComponent( 'vevent' );
	// start and end time
	$start = explode(":", date("d:m:Y:H:i", $date_start));
	$start = array( 'year' => $start[2], 'month' => $start[1], 'day' => $start[0], 'hour' => $start[3], 'min' => $start[4], 'sec' => 0 );
	$vevent->setProperty( 'dtstart', $start );
	$end = explode(":", date("d:m:Y:H:i", $date_end));
	$end = array( 'year' => $end[2], 'month' => $end[1], 'day' => $end[0], 'hour' => $end[3], 'min' => $end[4], 'sec' => 0 );
	$vevent->setProperty( 'dtend', $end );
	// Summary = subject: Group_Location_ContactPerson
	$vevent->setProperty( 'summary', $name_group.'_'.$name_location.'_'.$name_contact_person );
	//$vevent->setProperty( 'LOCATION', '' );
	//$vevent->setProperty( 'description', '' );
	//$vevent->setProperty( 'comment', '' );
	/*if ($contact_person_email!="")
	{
		$vevent->setProperty( 'attendee', utf8_encode($contact_person_email) );
	}*/
	// overwrite uid
	$vevent->setProperty( 'UID', 'tt'.$process->GetPKey().'@'.$v->getConfig('unique_id') );

	// create an event alarm
	/*$valarm = & $vevent->newComponent( "valarm" );
	$valarm->setProperty("action", "DISPLAY" );
	// reuse the event description
	$valarm->setProperty("description", $vevent->getProperty( "description") );
	// create alarm trigger (in UTC datetime)
	$start = explode(":", date("d:m:Y:H:i", $date_start-60*60));
	$d = sprintf( '%04d%02d%02d %02d%02d%02d', $start[2], $start[1], $start[0], $start[3], $start[4], 0 );
	iCalUtilityFunctions::transformDateTime( $d, $tz, "UTC", "Ymd\THis\Z");
	$valarm->setProperty( "trigger", $d );*/
	
	
}

// Output XML-String
$downloadFileName="calendar.ics";
$data = $v->createCalendar();
/*
echo $data;
exit;
*/
if( $data!="" ){
	// ... und streamen
	header('HTTP/1.1 200 OK');
	header('Status: 200 OK');
	header('Accept-Ranges: bytes');
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
	header("Content-Type: text/calendar; charset=utf-8");
	header("Content-Disposition: attachment; filename=\"".$downloadFileName."\"");
	header("Cache-Control: max-age=10" );
	header("Content-Length: ".strlen($data));
	echo $data;
	exit;
}else{
	// TODO: Fehler: Datei nicht gefunden
	echo "Fehler 1";
}
?>