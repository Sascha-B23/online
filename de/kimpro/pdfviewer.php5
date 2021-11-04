<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED = UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP;
$EXCLUDE_GROUP_BASETYPES = Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT);
if ($_POST["print"]!="")
{
	$SESSION_DONT_SEND_HEADERS = "true";
	session_cache_limiter(""); // WORKAROUND FÜR IE SSL-Download BUG mit Sessions!!!!
}
require_once("../../phplib/session.inc.php5");
ini_alter("memory_limit","512M");


$MENU_SHOW = true; // Menü und Username zeigen

if ( $_GET['file']!='' )$filename=$_GET['file'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//curl_setopt($ch, CURLOPT_URL, 'http://172.28.0.1:8010/static/file_1_1284540468_783691.pdf');
$encode = rawurlencode($filename);
$url = 'http://172.28.0.1:8010/static/'.$encode;
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$content = curl_exec($ch);
$httpReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
if ($content === false) {
    $info = curl_getinfo($ch);
    curl_close($ch);
    die('error in curl exec! ' . $info . $httpReturnCode .$url);
}
  
// Header content type
header('Content-type: application/pdf');
  
header('Content-Disposition: inline; filename="' . $filename . '"');
  
header('Content-Transfer-Encoding: binary');
  
header('Accept-Ranges: bytes');
  
// Read the file
//@readfile($content);

?>
<?=$content;?>