<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED = UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP;
$EXCLUDE_GROUP_BASETYPES = Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT);
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Kein Menü und Username zeigen
include("page_top.inc.php5");


// GET-Varibale holen
if ($_GET["processId"]!="" && !isset($_POST["processId"])) $_POST["processId"]=$_GET["processId"];
if (!isset($_POST["processId"]))
{
	// Fehler beim Laden des Prozessstatus
	$em->ShowError(pathinfo(__FILE__, PATHINFO_BASENAME), "Zugriff verweigert");
	exit;
}

$proccess = WorkflowManager::GetProcessStatusById($db, $_POST["processId"]);
if ($proccess===null)
{
	// Fehler beim Laden des Prozessstatus
	$em->ShowError(pathinfo(__FILE__, PATHINFO_BASENAME), "Prozessstatus konnte nicht geladen werden");
	exit;
}
$_POST["editElement"] = $proccess->GetPKey();


?>
<script type="text/javascript">
	<!--
		function ReturnToOpener(){
			// Fenster schließen
			window.close();
		}
	-->
</script>
<?
					
// Objekt initialisieren
$form=new OneColumnForm("javascript:ReturnToOpener();", new ProtocolFormData($_POST, $proccess, $db) );
$form->SetTableWidth(800);
$form->PrintData(); 


include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>