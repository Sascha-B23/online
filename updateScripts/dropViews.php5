<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$USER_HAVE_TO_BE_LOGGED_IN=false;
require_once("../phplib/session.inc.php5");
ini_alter("max_execution_time","300");

// Drop Views
for ($a=0; $a<2; $a++)
{
	$db->Query("DROP VIEW ".ProcessStatus::TABLE_NAME."_".($a==0 ? "rs" : "customer")."_view");
}
// Generate new Views
new WorkflowManager($db, true);

echo "Fertig";