<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_KUNDE;
if(isset($_POST["createDownloadFile"]) && $_POST["createDownloadFile"] != "")
{
	$SESSION_DONT_SEND_HEADERS="true";
	session_cache_limiter(""); // WORKAROUND FÜR IE SSL-Download BUG mit Sessions!!!!
}
require_once("../../phplib/session.inc.php5");
//print_r($_POST);
if ($_GET["processId"]!="" && !isset($_POST["processId"])) $_POST["processId"]=$_GET["processId"];
if (!isset($_POST["processId"]) && isset($_POST["editElement"]) && trim($_POST["editElement"])!='') $_POST["processId"]=$_POST["editElement"];
if (isset($_POST["processId"]))
{
	$processStatus = WorkflowManager::GetProcessStatusById($db, $_POST["processId"]);
	if ($processStatus===null)
	{
		echo "Fehler beim Laden der Prozessdaten";
		exit;
	}
	$_POST["editElement"] = WorkflowManager::GetProcessStatusId($processStatus);
	if (!$processStatus->HasUserAccess($_SESSION["currentUser"], $db))
	{
		// Benutzer darf diese Seite nicht einsehen
		$em->ShowError("process.php5", "Zugriff auf Prozess '".$_POST["processId"]."' für Benutzer '".$_SESSION["currentUser"]->GetUserName()."' verweigert<br/>\n");
		return false;
	}
}
else
{
	// GET-Varibale holen
	if ($_GET["editElement"]!="") $_POST["editElement"] = $_GET["editElement"];
	if (isset($_POST["editElement"]) && !is_numeric($_POST["editElement"])) unset($_POST["editElement"]);
	// Objekt initialisieren
	$processStatus = new ProcessStatus($db);
}



$form = new ProcessForm($db, $processStatus, $DOMAIN_HTTP_ROOT."de/meineaufgaben/meineaufgaben.php5?UID=".$UID);
$MENU_SHOW = true; // Menü und Username zeigen
$HM = 1;
$UM = 1;
include("page_top.inc.php5");
$form->PrintData(); 
include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>