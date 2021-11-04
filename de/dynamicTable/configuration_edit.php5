<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_KUNDE;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Kein Menü und Username zeigen
include("page_top.inc.php5");

// GET-Varibale holen
if( $_GET["editElement"]!="" )$_POST["editElement"]=$_GET["editElement"];
if( isset($_POST["editElement"]) && !is_numeric($_POST["editElement"]) )unset($_POST["editElement"]);
if ((int)$_POST["editElement"]==0)
{
	echo "Zugriff verweigert";
	exit;
}

// Objekt initialisieren
$dynamicTableConfig = new DynamicTableConfig($db);
$form=new OneColumnForm($DOMAIN_HTTP_ROOT."de/dynamicTable/configurate.php5?".SID, new DynamicTableConfigEditFormData($_POST, $dynamicTableConfig, $db) );
$form->PrintData();

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>