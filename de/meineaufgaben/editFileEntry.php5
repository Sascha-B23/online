<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP;
$EXCLUDE_GROUP_BASETYPES = Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT);
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Menü und Username zeigen
include("page_top.inc.php5");

// GET-Varibale holen
if ($_GET["processId"]!="" && !isset($_POST["processId"])) $_POST["processId"]=$_GET["processId"];
if (!isset($_POST["processId"]))
{
	echo "Zugriff verweigert";
	exit;
}
if ($_GET["editElement"]!="") $_POST["editElement"]=$_GET["editElement"];
if (isset($_POST["editElement"]) && !is_numeric($_POST["editElement"])) unset($_POST["editElement"]);


$process = WorkflowManager::GetProcessStatusById($db, $_POST["processId"]);
if ($process===null)
{
	echo "Fehler beim Laden der Prozessdaten";
	exit;
}

$files = $process->GetAllProcessFiles($db);
$fileToUse = null;
foreach ($files as $file)
{
	if ($file['id']==(int)$_POST["editElement"]) 
	{
		$fileToUse = $file['fileObject'];
		break;
	}
}
if ($fileToUse==null)
{
	echo "Ungültige Übergabeparameter!";
	exit;
}

?>
<script type="text/javascript">
	<!--
		function ReturnToOpener()
		{
			if (dataStored) opener.document.FM_FORM.submit();
			// Fenster schließen
			window.close();
		}
	-->
</script>
<?


$form=new OneColumnForm("javascript:ReturnToOpener();", new EditFileEntryFormData($_POST, $fileToUse, $db) );
$form->PrintData(); 

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>