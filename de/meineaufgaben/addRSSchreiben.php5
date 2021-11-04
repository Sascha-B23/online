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
$object = new File($db);
$object->SetFileSemantic(FM_FILE_SEMANTIC_RSSCHREIBEN);
if( !isset($_POST["widerspruchID"]) && isset($_GET["widerspruchID"]) && is_numeric($_GET["widerspruchID"]) )$_POST["widerspruchID"]=(int)$_GET["widerspruchID"];
if( isset($_POST["widerspruchID"]) ){
	$widerspruch = new Widerspruch($db);
	$widerspruch->Load((int)$_POST["widerspruchID"], $db);
}

?>
<script type="text/javascript">
	<!--
		function ReturnToOpener(){
			if(dataStored){
				opener.document.FM_FORM.forwardToListView.value=false;
				opener.document.FM_FORM.submit();
			}
			// Fenster schließen
			window.close();
		}
	-->
</script>
<?
					
// Objekt initialisieren
$form=new OneColumnForm("javascript:ReturnToOpener();", new FMSSchreibenFormData($_POST, $object, $db, $widerspruch) );
$form->PrintData(); 


include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>