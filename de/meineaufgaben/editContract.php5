<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_KUNDE;
$EXCLUDE_GROUP_BASETYPES = Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT);
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Menü und Username zeigen
include("page_top.inc.php5");

// GET-Varibale holen
if( $_GET["editElement"]!="" )$_POST["editElement"]=$_GET["editElement"];
if( isset($_POST["editElement"]) && !is_numeric($_POST["editElement"]) )unset($_POST["editElement"]);
if( !isset($_POST["group"]) && isset($_GET["group"]) && is_numeric($_GET["group"]) )$_POST["group"]=(int)$_GET["group"];
if( !isset($_POST["company"]) && isset($_GET["company"]) && is_numeric($_GET["company"]) )$_POST["company"]=(int)$_GET["company"];
if( !isset($_POST["location"]) && isset($_GET["location"]) && is_numeric($_GET["location"]) )$_POST["location"]=(int)$_GET["location"];
if( !isset($_POST["cShop"]) && isset($_GET["cShop"]) && is_numeric($_GET["cShop"]) )$_POST["cShop"]=(int)$_GET["cShop"];

?>
<script type="text/javascript">
	<!--
		function ReturnToOpener(){
			if(dataStored)opener.SelectContract(<?=$_POST["group"];?>, <?=$_POST["company"];?>, <?=$_POST["location"];?>, <?=$_POST["cShop"];?>, newElementID); 
			// Fenster schließen
			window.close();
		}
	-->
</script>
<?

// Objekt initialisieren
$contract = new Contract($db);
$form=new OneColumnForm("javascript:ReturnToOpener();", new AddContractFormData($_POST, $contract, $db) );
$form->PrintData(); 

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>