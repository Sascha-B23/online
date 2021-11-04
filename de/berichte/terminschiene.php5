<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$USER_MANAGER_ACTION = UM_ACTION_SHOW_REPORT_TERMINSCHIENE;
require_once("../../phplib/session.inc.php5");

$report = new TerminschieneReport($db, $_SESSION["currentUser"], $customerManager, $addressManager, $userManager, $rsKostenartManager, $em, $lm);

$popupWindow = false;
if( !isset($_POST["year"]) && isset($_GET["year"]) && is_numeric($_GET["year"]) && (int)$_GET["year"]==$_GET["year"] )
{
	$_POST["year"] = (int)$_GET["year"];
	$popupWindow = true;
	$report->SetShowFilter(false);
	$report->SetUseSession(false);
}
if( $popupWindow )
{
	$MENU_SHOW = false;
}
else
{
	$MENU_SHOW = true; // Menü und Username zeigen
	$HM = 3;
	$UM = 6;
}

if (!isset($_SESSION["reports"]["terminschiene"]["showArchievedStatus"]))
{
	if( $_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE )
	{
		$_SESSION["reports"]["terminschiene"]["showArchievedStatus"] = -1;
	}
	else
	{
		// Customers only can see uptodate prozesses
		$_POST["showArchievedStatus"] = Schedule::ARCHIVE_STATUS_UPTODATE;
		$_SESSION["reports"]["terminschiene"]["showArchievedStatus"] = Schedule::ARCHIVE_STATUS_UPTODATE;
	}
}

include("page_top.inc.php5");
ob_start();
$report->PrepareVars();
$report->PrintContent();						
$CONTENT = ob_get_contents();
ob_end_clean();
include("template_1row.inc.php5"); // Content includen
include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>