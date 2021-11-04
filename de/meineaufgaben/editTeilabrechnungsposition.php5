<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP;
$EXCLUDE_GROUP_BASETYPES = Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT);
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Kein Menü und Username zeigen
include("page_top.inc.php5");

// GET-Varibale holen
$object = new Teilabrechnungsposition($db);
$teilabrechnung = null;
if( $_GET["editElement"]!="" )$_POST["editElement"]=$_GET["editElement"];
if( isset($_POST["editElement"]) && !is_numeric($_POST["editElement"]) )unset($_POST["editElement"]);
if( !isset($_POST["teilabrechnungID"]) && isset($_GET["teilabrechnungID"]) && is_numeric($_GET["teilabrechnungID"]) )$_POST["teilabrechnungID"]=(int)$_GET["teilabrechnungID"];
if( isset($_POST["teilabrechnungID"]) ){
	$teilabrechnung = new Teilabrechnung($db);
	$teilabrechnung->Load($_POST["teilabrechnungID"], $db);
	$object->SetTeilabrechnung($teilabrechnung);
}

?>
<script type="text/javascript">
	<!--
		function ReturnToOpener(){
			opener.document.FM_FORM.submit();
			// Fenster schließen
			window.close();
		}
	-->
</script>
<?
					
// Objekt initialisieren
$editMode = ((int)$_POST["editElement"]>0 ? true : false);
$formData = new TeilabrechnungspositionFormData($_POST, $object, $db);
$form=new OneColumnForm("javascript:ReturnToOpener();", $formData);
if ($_POST['forwardToListView']=="false")
{
	if (!$editMode && (int)$_POST["editElement"]>0)
	{
		// Das Objekt wurde neu angelegt -> nächstes neu anlegen
		unset($_POST);
		$_GET["editElementBefore"] = $object->GetPKey();
		$_POST["teilabrechnungID"] = $object->GetTeilabrechnungPKey();
		$object = new Teilabrechnungsposition($db);
		$object->SetTeilabrechnung($teilabrechnung);
	}
	$form=new OneColumnForm("javascript:ReturnToOpener();", new TeilabrechnungspositionFormData($_POST, $object, $db), "template_1row_edit.inc.php5", true);
}
$form->SetDefaultSendData(false);
$form->SetDefaultForwardToListView(false);
$form->PrintData();

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>