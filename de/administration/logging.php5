<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_ADMINISTRATOR;
$SESSION_DONT_SEND_HEADERS="true";
session_cache_limiter(""); // WORKAROUND FÜR IE SSL-Download BUG mit Sessions!!!!
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = true; // Menü und Username zeigen
$HM = 2;
$UM = 9;

$form = new OneColumnForm($DOMAIN_HTTP_ROOT."de/administration/logging.php5?UID=".$UID, new ExportLogFormData($_POST, null, $db), "template_1row_edit.inc.php5", true);

include("page_top.inc.php5");
$form->PrintData(); 
include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>