<?
ini_alter("memory_limit","500M");
ini_alter("max_execution_time", "300"); // max 5 minutes

// Export Data as CSV?
if (isset($_POST['doExport']))
{
	$content = $syncManager->GetExportContent();
	$filename = $exportFilePrefix.'_'.date("d_m_Y", time());
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
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title><?=$pageTitle;?></title>
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
								<form enctype="multipart/form-data" method="post">
									<table border="0" cellpadding="0" cellspacing="0">
										<tr>
											<td>Bitte geben Sie die Datei an, die Sie in KIM-Online einspielen möchten:</td>
										</tr>
										<tr>
											<td style="cursor: pointer; cursor: hand;" align="left">
												<input size="30" type="file" accept=".csv" name="userfile" />
											</td>
											<td>
												<input type="submit" value="Datei zur Prüfung hochladen" class="formButton2" />
											</td>
										</tr>
									</table>
								</form>
							</td>
							<td align="right" valign="top">
								<form method="post">
									<input type="hidden" name="doExport" value="true" />
									<br /><input type="submit" class="formButton2" value="Datei exportieren" />
								</form>
							</td>
						</tr>
					</table>
				</td>
				<td width="25px">&#160;</td>
			</tr>
			<tr>
				<td width="25px">&#160;</td>
				<td valign="top">
				<?	if (isset($_FILES["userfile"]))
					{
						$result = $syncManager->UploadCsvFile($_FILES);
						if ($result===true)
						{
							// show overview table
							$syncManager->ShowImportOverview();
						}
						else
						{
							// error occured
							switch ($result)
							{
								// HTTP-Upload errors
								case 1: $error = "File Error #1: Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Größe."; break;
								case 2: $error = "File Error #2: Die hochgeladene Datei überschreitet die in dem HTML Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigröße."; break;
								case 3: $error = "File Error #3: Die Datei wurde nur teilweise hochgeladen."; break;
								case 4: $error = "File Error #4: Es wurde keine Datei hochgeladen."; break;
								// UploadCsvFile errors
								case -1: $error = "Upload nicht erfolgreich: Pfad der Datei ist nicht gesetzt."; break;
								case -2: $error = "Die ausgewählte Datei hat nicht die vorgeschriebene Endung: \".csv\""; break;
								case -3: $error = "Upload nicht erfolgreich: Datei wurde nach dem Upload auf den Server nicht gefunden."; break;
								case -4: $error = "Uploadvorgang erfolgreich, Datei kann aufgrund der Leserechte nicht ausgewertet werden."; break;
								// ReadCsvFile errors
								case -11: $error = "Die Datei enthält nicht die erforderlichen Spaltenüberschriften"; break;
								case -12: $error = "Es sind keine Einträge in der angegebenen Datei vorhanden."; break;
								default: $error = "Unerwarteter Fehler (".$result.")"; break;
							}
							?><span style="color:#ff0000;"><?=$error?></span><?
						}
					}
					if (isset($_POST["doImport"]))
					{
						$returnValue = $syncManager->ImportSelectedData();
						$syncManager->ShowImportResult();
					}
					?>
				</td>
				<td width="25px">&#160;</td>
			</tr>
		</table>
	</body>
</html>