<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_ADMINISTRATOR;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = true; // Menü und Username zeigen

$HM = 2;
$UM = 4;

include("page_top.inc.php5");

// GET-Varibale holen
if( $_GET["editElement"]!="" )$_POST["editElement"]=$_GET["editElement"];
if( isset($_POST["editElement"]) && !is_numeric($_POST["editElement"]) )unset($_POST["editElement"]);

// Objekt initialisieren
$object = new StandardText($db);
if (isset($_GET["type"]) && is_numeric($_GET["type"])) $object->SetType((int)$_GET["type"]);
$form=new OneColumnForm($DOMAIN_HTTP_ROOT."de/administration/standardtext.php5?UID=".$UID, new StandardTextFormData($_POST, $object, $db, $customerManager));
$form->PrintData(); 

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>