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
if( !isset($_POST["group"]) && isset($_GET["group"]) && is_numeric($_GET["group"]) )$_POST["group"]=(int)$_GET["group"];
if( !isset($_POST["company"]) && isset($_GET["company"]) && is_numeric($_GET["company"]) )$_POST["company"]=(int)$_GET["company"];
if( !isset($_POST["clocation"]) && isset($_GET["clocation"]) && is_numeric($_GET["clocation"]) )$_POST["clocation"]=(int)$_GET["clocation"];
if( !isset($_POST["shop"]) && isset($_GET["shop"]) && is_numeric($_GET["shop"]) )$_POST["shop"]=(int)$_GET["shop"];

?>
<script type="text/javascript">
	<!--
		function ReturnToOpener(){
			if(dataStored)opener.SelectShop(<?=$_POST["group"];?>, <?=$_POST["company"];?>, <?=$_POST["clocation"];?>, newElementID); 
			// Fenster schließen
			window.close();
		}
	-->
</script>
<?
					
// Objekt initialisieren
$cShop = new CShop($db);
$form=new OneColumnForm( "javascript:ReturnToOpener();", new CustomerShopFormData($_POST, $cShop, $db) );
$form->PrintData(); 

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>