<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = true; // Menü und Username zeigen
$HM = 2;
$UM = 6;
include("page_top.inc.php5");

// GET-Varibale holen
if( $_GET["editElement"]!="" )$_POST["editElement"]=$_GET["editElement"];
if( isset($_POST["editElement"]) && !is_numeric($_POST["editElement"]) )unset($_POST["editElement"]);

//URL-Parameter holen
if ( $_GET['location']!='' )$locID=$_GET['location'];
if ( $_GET['group']!='' )$_POST["group"]=$_GET['group'];
// Dokumentenname wird in der Session gespeichert, wird benoetigt für request auf die richtige Datei
if ( $_SESSION['filename'.$locID]!='')$filename =$_SESSION['filename'.$locID];
$currentLocation=$customerManager->GetLocationByID($_SESSION["currentUser"], $_GET["location"]);
$currentCompany=$currentLocation->GetCompany();
$currentGroup=$currentCompany->GetGroup();

$_POST["location"] = $currentLocation->GetPKey();
$_POST["group"] = $currentGroup->GetPKey();
$_POST["company"]=$currentCompany->GetPKey();

//*** API CALL
//Datei muss der ContractFormData angehaengt werden, initialisieren von curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//$filename = "VG-Calvin Klein-Berlin, Friedrichstr. _ Unter den Linden 24 (Haus der Schweiz)-05.12.13-1417.pdf";
// hole PDF im Rohformat
$encode = rawurlencode($filename);
$url = 'http://172.28.0.1:8010/static/'.$encode;
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$content = curl_exec($ch);
$httpReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($content === false) {
    $info = curl_getinfo($ch);
    curl_close($ch);
    die('error in curl exec! ' . $info . $httpReturnCode .$url . $content);
}

$_POST["klassifikation"] = $content;

//<?=print_r($contractdata->GetElements()[34]->GetFileElement($db, $content, FM_FILE_SEMANTIC_MIETVERTRAG), true); GetFileElement erzeugt ein File aus dem zwischenspeicher $_FILE
// Hier muss noch ein Test implementiert werden, ob die Datei schon existiert!!!! Sonst wird das File immerwieder erstellet wenn man refresehed!!
if (!is_uploaded_file($filename)) $testfile = FileManager::CreateFromStream($db, $content, FM_FILE_SEMANTIC_MIETVERTRAG, $filename, "pdf"); // geht nicht
// Diesselbe Datei wird immer angelegt aber nur mit anderem File-name also es werden im Ordner uploadfiles 3 unterschiedliche Files angelegt, die
// aber alle denselben Inhalt haben !!!!!!!!!!!!!!!!
// Objekt initialisieren und die Erzeugte Datei (File Objekt repraesentiert nur den PFAD zum File auf dem Server, FileManager hat aber dort die Datei angelegt)
$contract = new Contract($db);
$geklappt ="Nein";
if($contract->AddFile($db,$testfile))$geklappt = "ja";
// Form in der UI erzeugen es wird post-url mitgegeben
$contractdata = new ContractFormData($_POST, $contract, $db);

?>
<div>Filename: <?=print_r($filename, true);?></div>
<div>TESTFILE: <?=print_r($testfile, true);?></div>
<div>TESTFILE PKEY: <?=print_r($testfile->GetPKey(), true);?></div>
<div>geklappt?: <?=print_r($geklappt, true);?></div>
<div>Contract FileCount: <?=print_r($contract->GetFileCount($db, FM_FILE_SEMANTIC_MIETVERTRAG), true);?></div>
<?
//OneColumnForm manipuliert die Daten des ContractFormData und demnach die Daten des Contracts
$form=new OneColumnForm( $DOMAIN_HTTP_ROOT."de/administration/contracts.php5?UID=".$UID,  $contractdata);
$form->SetTableWidth("700px");
$form->PrintData();

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>