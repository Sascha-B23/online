<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../../phplib/session.inc.php5");

if (isset($_REQUEST["viewID"]))
{
	$table = $dynamicTableManager->GetDynamicTableById($_REQUEST["viewID"]);
	if ($table!=null)
	{
		//file_put_contents(dirname(__FILE__)."/lastRequest.txt", print_r($_REQUEST, true));
		$table->ResetObjects($db);
		$table->SetTableParams($_REQUEST);
		$returnData = $table->GetJSONAnswer();
		//file_put_contents(dirname(__FILE__)."/lastJsonAnswer.txt", print_r($returnData, true));
		echo json_encode($returnData);
	}
	else
	{
		echo "Table '".$_REQUEST["viewID"]."' not found";
	}
}
?>