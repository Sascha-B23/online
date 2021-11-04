<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED = UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Kein Menü und Username zeigen
include("page_top.inc.php5");

// GET-Varibale holen
if( $_GET["planElements"]!="" )$_POST["planElements"]=$_GET["planElements"];
if( isset($_POST["planElements"]) && trim($_POST["planElements"])=="" )unset($_POST["planElements"]);
if( !isset($_POST["planElements"]) ){
	echo "Zugriff verweigert";
	exit;
}
$prozessIDs=Array();
$lists=explode("|", $_POST["planElements"]);
for( $a=0; $a<count($lists); $a++ ){
	$tempIDs=explode("_", $lists[$a]);
	if( count($tempIDs)==2 && (int)$tempIDs[1]==$tempIDs[1] && !in_array((int)$tempIDs[1], $prozessIDs) ){
		$prozessIDs[]=(int)$tempIDs[1];
	}
}
if( count($prozessIDs)==0 ){
	echo "Zugriff verweigert (2)";
	exit;	
}
// Erstes Element verwenden
$_POST["editElement"]=$prozessIDs[0];




$object = new ProcessStatus($db);
/*if( $object->Load((int)$prozessIDs[0], $db)!==true ){
	echo "Fehler beim Laden der Prozessdaten";
	exit;
}*/

?>
<script type="text/javascript">
	<!--
		function ReturnToOpener(){
			if(dataStored)opener.document.form_lists.submit();
			// Fenster schließen
			window.close();
		}
	-->
</script>
<?
					
// Objekt initialisieren
$form=new OneColumnForm("javascript:ReturnToOpener();", new PlanTaskFormData($_POST, $object, $db, $prozessIDs) );
$form->PrintData(); 


include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>