<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_KUNDE;
$EXCLUDE_GROUP_BASETYPES = Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT);
require_once("../../phplib/session.inc.php5");

$report = new TerminschieneReport($db, $_SESSION["currentUser"], $customerManager, $addressManager, $userManager, $rsKostenartManager, $em, $lm);

$processStatus = WorkflowManager::GetProcessStatusById($db, $_GET["processId"]);
if ($processStatus==null)
{	?>
	<div class="errorText">
		<br /> Prozess konnte nicht geladen werden<br /><br /><br />
	</div>
<?	exit;
}else{
	if (!$processStatus->HasUserAccess($_SESSION["currentUser"], $db))
	{
		// Benutzer darf diese Seite nicht einsehen
		$additionalInfos = "Referer: ".(trim($_SERVER["HTTP_REFERER"])!="" ? $_SERVER["HTTP_REFERER"] : "-");
		$em->ShowError("showTerminschiene.php5", "Zugriff für Benutzer '".$_SESSION["currentUser"]->GetUserName()."' verweigert<br />\n".$additionalInfos);
		exit;
	}
}

$popupWindow = true;
$report->SetShowFilter(false);
$report->SetUseSession(false);

$MENU_SHOW = false; // Menü und Username zeigen

include("page_top.inc.php5");
ob_start();
$report->SetProcessStatus($processStatus);
$report->PrintContent();
$CONTENT = ob_get_contents();
ob_end_clean();
include("template_1row.inc.php5"); // Content includen
include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>