<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT;
require_once("../../packages/KuerzungsbetraegeSyncManager-1.0/KuerzungsbetraegeSyncManager.lib.php5");
require_once("../../phplib/session.inc.php5");

$pageTitle = 'Synchronisation Kürzungsbeträge';
$exportFilePrefix = 'Kürzungsbeträge-Export';

ini_alter("display_errors", "on");
$syncManager = new KuerzungsbetraegeSyncManager($db, $lm);

include 'template_sync.inc.php5';
