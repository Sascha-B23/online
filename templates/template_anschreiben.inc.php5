<?php
	// Dieses Template wird beim Erzeugen des Protokolls in der Klasse ProtocolFormData verwendet:
	// 1. Daher können über >$this< alle benötigten Informationen abgerufen werden (siehe ProtocolFormData.lib.php5)
	// 2. Globale Variablen müssen entsprechend mit dem Schlüsselwort >global< eingebunden werden.	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
		<title>Anschreiben</title>
		<meta name="author" content="Seybold GmbH"/>
		<meta name="copyright" content="© 2012 Stoll von Gáti GmbH"/>
		<style type="text/css">
			body{
				font-size:14pt;
				font-weight:none;
				font-family: Arial;
			}
			td{
				vertical-align: top;
				text-align:left;
			}
		</style>
	</head>
	<body style="margin:0;">
		<table style="width:1024px;" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<table style="width:100%;" border="0" cellpadding="2" cellspacing="2">
						<tr>
							<td style="width:125px; background-color: #eeeeee;"><strong>An</strong></td>
							<td style="background-color: #eeeeee;"><?=str_replace("<", "&lt;", str_replace(">", "&gt;", $ANSCHREIBEN["to"]));?></td>
						</tr>
					<?	if(trim($ANSCHREIBEN["cc"])!=""){?>
							<tr>
								<td style="width:125px; background-color: #eeeeee;"><strong>CC</strong></td>
								<td style="background-color: #eeeeee;"><?=str_replace("<", "&lt;", str_replace(">", "&gt;", $ANSCHREIBEN["cc"]));?></td>
							</tr>
					<?	}?>	
						<tr>
							<td style="width:125px; background-color: #eeeeee;"><strong>Von</strong></td>
							<td style="background-color: #eeeeee;"><?=str_replace("<", "&lt;", str_replace(">", "&gt;", $ANSCHREIBEN["from"]));?></td>
						</tr>
						<tr>
							<td style="background-color: #eeeeee;"><strong>Betreff</strong></td>
							<td style="background-color: #eeeeee;"><?=$ANSCHREIBEN["subject"];?></td>
						</tr>
						<tr>
							<td style="background-color: #eeeeee;"><strong>Datum</strong></td>
							<td style="background-color: #eeeeee;"><?=date("d.m.Y H:i", $ANSCHREIBEN["sendTime"]);?> Uhr</td>
						</tr>
						<tr>
							<td colspan="2"><br /><?=str_replace("\n", "<br />", $ANSCHREIBEN["text"]);?></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>
</html>