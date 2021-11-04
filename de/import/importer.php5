<?
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT;
require_once("../../packages/ImportManager-1.0/ImportManager.lib.php5");
require_once("../../phplib/session.inc.php5");

ini_alter("memory_limit","500M");
ini_alter("max_execution_time", "300");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>Kundensynchronisation</title>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
		<meta name="author" content="<?=$lm->GetString('SYSTEM', 'ID_COMPANY_NAME');?>">
		<meta name="generator" content="Stoll von Gáti GmbH / www.stollvongati.com">
		<meta name="copyright" content="(c)<?=date("Y");?> <?=$lm->GetString('SYSTEM', 'ID_COMPANY_NAME');?>">
		<meta name="robots" content="noindex">
		<meta name="robots" content="nofollow">
		<link rel="stylesheet" type="text/css" href="<?=$SHARED_HTTP_ROOT?>css/content.css">
		<script src="<?=$SHARED_HTTP_ROOT?>scripts/mootools-core-1.4.5-full-compat.js" type="text/javascript"></script>
	</head>
	<body style="margin:0px; padding:0px;">
		<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%;">
			<tr>
				<td width="25px">&#160;</td>
				<td valign="top" style="height:80px;">
					<br />
					<table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
						<tr>
							<td style="width:75%;">
								<form enctype="multipart/form-data" action="<?=$SHARED_HTTP_ROOT?>de/import/importer.php5?<?=SID?>" method="post">
									<table border="0" cellpadding="0" cellspacing="0">
										<tr>
											<td>Bitte geben Sie die Datei an, die Sie in KIM-Online einspielen möchten:</td>
										</tr>
										<tr>
											<td style="cursor: pointer; cursor: hand;" align="left">
												<input size="30" type="file" accept="text/*" name="userfile" />
											</td>
											<td>
												<input type="submit" value="Datei zur Prüfung hochladen" class="formButton2" />
											</td>
										</tr>
									</table>
								</form>
							</td>
							<td align="right" valign="top">
								<br /><input type="button"  class="formButton2" value="Datei exportieren" onclick="document.location = '<?=$SHARED_HTTP_ROOT?>de/import/exporter.php5?<?=SID?>';" />
							</td>
						</tr>
					</table>
				</td>
				<td width="25px">&#160;</td>
			</tr>
			<tr>
				<td width="25px">&#160;</td>
				<td valign="top">
				<?	
					$importManager = new ImportManager($db);					
					if (isset($_FILES["userfile"]))
					{
						if($_FILES["error"] != 0)
						{
							switch($_FILES["error"])
							{
								case 1: $error = "File Error #1: Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Größe."; break;
								case 2: $error = "File Error #2: Die hochgeladene Datei überschreitet die in dem HTML Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigröße."; break;
								case 3: $error = "File Error #3: Die Datei wurde nur teilweise hochgeladen."; break;
								case 4: $error = "File Error #4: Es wurde keine Datei hochgeladen."; break;
							}
						}
						else
						{
							$result = $importManager->UploadCsvFile($_FILES);
							switch($result)
							{
								case 0: $error = "Upload erfolgreich"; break;
								case 1: $error = "Die ausgewählte Datei hat nicht die vorgeschriebene Endung: \".csv\""; break;
								case 2: $error = "Uploadvorgang erfolgreich, Datei kann aufgrund der Leserechte nicht ausgewertet werden."; break;
								case 3: $error = "Upload nicht erfolgreich: Pfad der Datei ist nicht gesetzt."; break;
								case 4: $error = "Upload nicht erfolgreich: Angegebene Datei existiert nicht."; break;
								case 5: $error = "Upload nicht erfolgreich: Datei wurde nach dem Upload auf den Server nicht gefunden."; break;
								case 6: $error = "Es sind keine Einträge in der angegebenen Datei vorhanden."; break;
								default: $error = ""; break;
							}
						}

						if($result == 0 && $_FILES["error"] == 0)
						{
							$importManager->ShowImportResult();
						}
						else
						{
							?><span style="color:#ff0000;"><?=$error?></span><?
						}
					}
					if (isset($_POST["filename"]))
					{
						$results = $importManager->StoreImportData();
						if($results !== true)
						{
							?><span style="color:#ff0000;">Beim Import sind fehler aufgetreten <?=(!is_array($results) ? '('.print_r($results).')' : '');?></span><br /><?
							foreach ($results as $result)
							{
								$error = "SFM ID (".$result["fmsId"]."): ";
								switch($result["errorCode"])
								{
									case -1: $error .= "Die angegebene Firma exisitiert nicht"; break;
									case -2: $error .= "Die angegebene Firma passt nicht zu der angegebenen Gruppe"; break;
									default: 
										if($result["errorCode"] <= -2000) $error .= "Fehler beim speichern des Ladens (Errorcode:".$result["errorCode"].")";
										elseif($result["errorCode"] <= -1000) $error .= "Fehler beim speichern des Standorts (Errorcode:".$result["errorCode"].")";
										break;
								}
								?><span style="color:#ff0000;"><?=$error?></span><br /><?
							}
						}
						else
						{
							?><span>Import wurde erfolgreich durchgeführt</span><?
						}
					}
					?>
				</td>
				<td width="25px">&#160;</td>
			</tr>
		</table>
	</body>
</html>