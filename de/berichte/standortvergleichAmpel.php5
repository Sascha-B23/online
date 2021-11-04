<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$USER_MANAGER_ACTION = UM_ACTION_SHOW_REPORT_STANDORTVERGLEICH_AMPEL;
if ($_POST["print"]!="")
{
	$SESSION_DONT_SEND_HEADERS = "true";
	session_cache_limiter(""); // WORKAROUND FÜR IE SSL-Download BUG mit Sessions!!!!
}
require_once("../../phplib/session.inc.php5");
$report = new SignalLightReport($db, $_SESSION["currentUser"], $customerManager, $addressManager, $userManager, $rsKostenartManager, $em, $lm);

if (!isset($_POST["shop"]) && isset($_GET["shop"]) && is_numeric($_GET["shop"]) && (int)$_GET["shop"]==$_GET["shop"])
{
	$_POST["shop"] = (int)$_GET["shop"];
	$_POST["show"] = "on";
	$MENU_SHOW = false;
	$report->SetShowFilter(false);
	$report->SetUseSession(false);
}
else
{
	$MENU_SHOW = true; // Menü und Username zeigen
	$HM = 3;
	$UM = 4;
}

// stream file on request
if ($_POST["print"]!="")
{
	$report->StreamFile();
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