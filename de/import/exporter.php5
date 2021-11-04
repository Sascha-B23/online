<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
$DONT_REDIRECT_SSL="true";
$SESSION_DONT_SEND_HEADERS="true";
session_cache_limiter(""); // WORKAROUND FÜR IE SSL-Download BUG mit Sessions!!!!
// Session laden
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT;
require_once("../../phplib/session.inc.php5");
require_once("ImportManager-1.0/ImportManager.lib.php5");

$importManager = new ImportManager($db);
$content = $importManager->GetExportContent();

///////////////////
// AUSGABE ALS CSV
// Filename definieren (z.B. "Kundendaten-Export_13_04_2010.csv")
$filename = "Kundendaten-Export_";
$filename .= date("d_m_Y", time());

header('HTTP/1.1 200 OK');
header('Status: 200 OK');
header('Accept-Ranges: bytes');
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Transfer-Encoding: Binary');
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"".$filename.".csv\"");
header("Content-Length: ".(string)strlen($content));

echo $content;
exit;
?>
