<?php
// Includes
$DOMAIN_NAME="NKAS";
$DONT_REDIRECT_SSL="true";
$SESSION_DONT_SEND_HEADERS="true";
session_cache_limiter(""); // WORKAROUND FÜR IE SSL-Download BUG mit Sessions!!!!
// Session laden
require_once("../phplib/session.inc.php5");
if (!isset($_SESSION["fileDownloadManager"]))
{
	$_SESSION["fileDownloadManager"] = new KimFileDownloadManager();
}
// Datei mit dem übergebenen DownloadCode streamen...
$errorCode = $_SESSION["fileDownloadManager"]->DownloadFile($db, $_GET["code"]);
// Wenn wir hier rauskommen, dann ist ein Fehler aufgetreten
switch($errorCode){
	default:
		echo "Error ".$errorCode;
}
?>