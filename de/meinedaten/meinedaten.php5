<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = true; // Menü und Username zeigen
$HM = 4;
$UM = 1;
include("page_top.inc.php5");

$_POST["editElement"]=$_SESSION["currentUser"]->GetPkey();

// Objekt initialisieren
$user = new User($db);
$form = new OneColumnForm( $DOMAIN_HTTP_ROOT."de/meinedaten/meinedaten.php5?UID=".$UID, new UserFormData($_POST, $user, $db, true) );
$form->PrintData(); 

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>

?>
