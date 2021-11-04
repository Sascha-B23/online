<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT;
require_once("../../packages/ContractSyncManager-1.0/ContractSyncManager.lib.php5");
require_once("../../phplib/session.inc.php5");

$pageTitle = 'Synchronisation Verträge';
$exportFilePrefix = 'Vertrags-Export';
$syncManager = new ContractSyncManager($db, $lm);

include 'template_sync.inc.php5';