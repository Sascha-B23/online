<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP;
$EXCLUDE_GROUP_BASETYPES = Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE);
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Kein Menü und Username zeigen
include("page_top.inc.php5");

// GET-Varibale holen
if( $_GET["editElement"]!="" )$_POST["editElement"]=$_GET["editElement"];
if( isset($_POST["editElement"]) && !is_numeric($_POST["editElement"]) )unset($_POST["editElement"]);
if( !isset($_POST["editElement"]) ){
	echo "Zugriff verweigert";
	exit;
}

if( !isset($_POST["type"]) && isset($_GET["type"]) && is_numeric($_GET["type"]) )$_POST["type"]=Array((int)$_GET["type"]);

?>
<script type="text/javascript">
	<!--
		var addressIDString="";
		function ReturnToOpener(){
			// Fenster schließen
			window.close();
		}
	-->
</script>
<?
					
// Objekt initialisieren
$addressCompany = new AddressCompany($db);
$form = new OneColumnForm( "javascript:ReturnToOpener();", new AddressCompanyFormData($_POST, $addressCompany, $db, true) );
$form->PrintData(); 

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>