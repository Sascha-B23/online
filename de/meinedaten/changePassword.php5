<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
$PASSWORD_CHANGE_SITE = true;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Menü und Username zeigen
include("page_top.inc.php5");

$_POST["editElement"]=$_SESSION["currentUser"]->GetPkey();

// Objekt initialisieren
$user = new User($db);
$form = new OneColumnForm( $DOMAIN_HTTP_ROOT."de/meineaufgaben/meineaufgaben.php5?UID=".$UID, new ChangePasswordData($_POST, $user, $db) );
$form->SetTableWidth('919px');
$form->PrintData(); 

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>

?>
