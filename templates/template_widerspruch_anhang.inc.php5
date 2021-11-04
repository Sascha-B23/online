<?php
	/*@var $this Widerspruch */
	// TODO: Widerspruchtemplate wie in Vorlage nachbauen - Format ist DIN A4 Hochformat für 1. Seite Anschreiben und alle weiteren sind Querformat
	// Dieses Template wird innerhalb der Funktion CreateDocument() der Klasse >Widerspruch< geladen:
	// 1. Daher können über >$this< alle benötigten Informationen abgerufen werden (siehe phplib\classes\NebenKostenManager.lib.php5)
	// 2. Die Datenbank kann über das Objekt $db angesprochen werden
	// 3. Globale Variablen müssen aus diesem Grund entsprechend mit dem Schlüsselwort >global< eingebunden werden.
	
	$max_strlen_per_line = 36; 	// 37 Zeichen lang
	$max_lines_per_page = 17;	// 19 Zeilen hoch
	$max_strlen_per_page = $max_strlen_per_line*$max_lines_per_page;
	
	
	
	// $abrechnungsjahr = $this->GetAbrechnungsJahr();
	// $contract = $abrechnungsjahr->GetContract();
	// $shop = $contract->GetShop();
	// $kostenManager = $shop->GetCPersonRS();
	$process = $this->GetProcessStatus($db);
	$location = $process->GetLocation();
	$company = $process->GetCompany();
	$group = $process->GetGroup();
	
	$wps = $this->GetWiderspruchspunkte($db, false);
	$data = array();
	for ($a=0; $a<count($wps); $a++)
	{
		$data[] = array("ihrStandpunkt" => str_replace('<th style="width: 10px;"> </th>', '<th style="width: 10px;">&#160;</th>', $wps[$a]->GetTextLeft()), "unserStandpunkt" => $wps[$a]->GetTextRight());
	}

	$plzAndCityAndStreet = "";
	if ($location!=null)
	{
		$plzAndCityAndStreet = trim($location->GetCity());
		if ($plzAndCityAndStreet!="") $plzAndCityAndStreet = " in ".$plzAndCityAndStreet.", ".$location->GetStreet();
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
		<title>KIM-Online Widerspruch</title>
		<meta name="author" content="Seybold GmbH"/>
		<meta name="publisher" content="Seybold GmbH"/>
		<meta name="copyright" content="© 2012 Stoll von Gáti GmbH"/>
		<style type="text/css">
			body{
				font-size:16px;
				font-weight:none;
				font-family: Arial;
			}
			th{
				width: 495px;
				vertical-align: top;
				font-size: 12px;
				text-align: left;
				font-weight: bold;
				text-decoration: underline;
				border-top: 1px solid #000000;
				border-bottom: 1px solid #000000;
				border-right: 1px solid #000000;
			}
			td{
				vertical-align: top;
				font-size: 12px;
				text-align: left;
				border-top: 1px solid #000000;
				border-bottom: 1px solid #000000;
				border-right: 1px solid #000000;
			}
			.bigtext{
				font-size:18px;
			}
			.normaltext{
				font-weight:normal;
			}
			h1{
				font-size: 14px;
				font-weight: bold;
			}
			h2{
				font-size: 10px;
				font-weight: normal;
			}
			.footer{
				padding-top: 3px;
				font-size: 10px;
				text-align: center;
			}
			/*
			* Widerspruchgenerator
			*/
			#wstable
			{
				font-family: Arial, Helvetica, sans-serif;
				border-top: 1px solid #000000;
				border-left: 1px solid #000000;
				border-collapse:collapse;
				border-spacing: 0px;
			}
			#wstable td, #wstable th 
			{
				vertical-align: top;
				font-size: 12px;
				border-top: 0px;
				border-bottom: 1px solid #000000;
				border-right: 1px solid #000000;
				text-decoration: none;
			}
			#wstable th 
			{
				text-align: left;
				font-weight: bold;
			}
		</style>
	</head>
	<body style="margin:0;">
		<!-- AUSGABE -->
		<h1>Anlage zum Brief vom <?=date("d.m.Y", time());?> - Aufstellung der zu klärenden Punkte (Fragen und Einwände)</h1>
		<h2><?=$company->GetName();?> <?=$plzAndCityAndStreet;?> - Widerspruch Nebenkostenabrechnung <?=$process->GetAbrechnungsJahrString();?></h2>
		<table border="0" cellpadding="4" cellspacing="0" style="width: 990px;">
			<thead>
				<tr>
					<th style="border-left: 1px solid #000000;">Ihr Standpunkt</th>
					<th>Standpunkt <?=$group->GetName();?></th>
				</tr>
			</thead>
			<tbody>
			<?	for($a=0; $a<count($data); $a++){?>
					<tr>
						<td style="border-left: 1px solid #000000;"><?=str_replace("[WS_NUMMER]", ($a+1).'.', $data[$a]['ihrStandpunkt']);?></td>
						<td><?=$data[$a]['unserStandpunkt']?></td>
					</tr>
			<?	}?>
			</tbody>
		</table>
	</body>
</html>
<?
	$MARGINS = array('left' => 15, 'right' => 15, 'top' => 15, 'bottom' => 20);
	$HEADER = "";//<div class='footer'>HEADER TEST</div>";
	$FOOTER = "<div class='footer'>Seite ##PAGE## von ##PAGES##</div>";
//	exit;
?>