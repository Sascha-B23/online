<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = true; // Menü und Username zeigen
$HM = 2;
$UM = 6;
include("page_top.inc.php5");

// GET-Varibale holen
if( $_GET["editElement"]!="" && !isset($_POST["editElement"]) )$_POST["editElement"]=$_GET["editElement"];
if( isset($_POST["editElement"]) && !is_numeric($_POST["editElement"]) )unset($_POST["editElement"]);

// Location aus der GET-Variable holen
if ( $_GET['location']!='' )$_POST["location"]=$_GET['location'];
$currentLocation=$customerManager->GetLocationByID($_SESSION["currentUser"], $_GET["location"]);
$currentCompany=$currentLocation->GetCompany();
$currentGroup=$currentCompany->GetGroup();

// Uebermitteln der id´s
$_POST["location"] = $currentLocation->GetPKey();
$_POST["group"] = $currentGroup->GetPKey();
$_POST["company"]=$currentCompany->GetPKey();

// Objekt initialisieren
$teilabrechnung = new Teilabrechnung($db);
$form=new OneColumnForm( $DOMAIN_HTTP_ROOT."de/administration/contracts.php5?UID=".$UID, new TeilabrechnungFormData($_POST, $teilabrechnung, $db) );
$form->PrintData(); 

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>