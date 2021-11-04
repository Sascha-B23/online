<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_ADMINISTRATOR;
require_once("../../packages/AddressSyncManager-1.0/AddressCompanySyncManager.lib.php5");
require_once("../../phplib/session.inc.php5");

$pageTitle = 'Synchronisation Adressdatenbank Firmen';
$exportFilePrefix = 'Adressdatenbank_Firmen-Export';
$syncManager = new AddressCompanySyncManager($db, $lm);

include 'template_sync.inc.php5';