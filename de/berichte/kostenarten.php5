<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED = UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP;
$EXCLUDE_GROUP_BASETYPES = Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT);
if ($_POST["print"]!="")
{
	$SESSION_DONT_SEND_HEADERS = "true";
	session_cache_limiter(""); // WORKAROUND FÜR IE SSL-Download BUG mit Sessions!!!!
}
require_once("../../phplib/session.inc.php5");
ini_alter("memory_limit","512M");
$report = new KostenartenReportCSV($db, $_SESSION["currentUser"], $customerManager, $addressManager, $userManager, $rsKostenartManager, $em, $lm);


$MENU_SHOW = true; // Menü und Username zeigen
$HM = 3;
$UM = 11;


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