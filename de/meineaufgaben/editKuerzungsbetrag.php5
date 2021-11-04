<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP;
$EXCLUDE_GROUP_BASETYPES = Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT);
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Kein Menü und Username zeigen
include("page_top.inc.php5");

// GET-Varibale holen
$object = new Kuerzungsbetrag($db);
if ($_GET["editElement"]!="" ) $_POST["editElement"]=$_GET["editElement"];
if (isset($_POST["editElement"]) && !is_numeric($_POST["editElement"])) unset($_POST["editElement"]);
if (!isset($_POST["widerspruchspunktID"]) && isset($_GET["widerspruchspunktID"]) && is_numeric($_GET["widerspruchspunktID"])) $_POST["widerspruchspunktID"]=(int)$_GET["widerspruchspunktID"];
if (isset($_POST["widerspruchspunktID"]))
{
	$widerspruchspunkt = new Widerspruchspunkt($db);
	$widerspruchspunkt->Load($_POST["widerspruchspunktID"], $db);
	$object->SetWiderspruchspunkt($widerspruchspunkt);
}


?>
<script type="text/javascript">
	<!--
		function ReturnToOpener(){
			if(dataStored)opener.document.FM_FORM.submit();
			// Fenster schließen
			window.close();
		}
	-->
</script>
<?
				
// Objekt initialisieren
$form = new OneColumnForm("javascript:ReturnToOpener();", new KuerzungsbetragFormData($_POST, $object, $db));
$form->PrintData(); 

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>