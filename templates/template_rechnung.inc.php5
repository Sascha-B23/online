<?php
	/*@var $this Widerspruch */
	// Dieses Template wird innerhalb der Funktion CreateDocument() der Klasse >Widerspruch< geladen:
	// 1. Daher können über >$this< alle benötigten Informationen abgerufen werden (siehe phplib\classes\NebenKostenManager.lib.php5)
	// 2. Die Datenbank kann über das Objekt $db angesprochen werden
	// 3. Globale Variablen müssen aus diesem Grund entsprechend mit dem Schlüsselwort >global< eingebunden werden.
	// 
	// TODO: Wo müssen diese Zahlen hinterlegt werden?
	$umsatzsteuerProzent = 19;
	
	// Daten übernehmen
	$verguetungRealisierteEinsparungProzent = $additionalData["verguetungRealisierteEinsparungProzent"];
	$verguetungNichtRealisierteEinsparungProzent = $additionalData["verguetungNichtRealisierteEinsparungProzent"];
	$realisierteEinsparung = $additionalData["realisierteEinsparungen"];
	$nichtRealisierteEinsparung = $additionalData["nichtRealisierteEinsparungen"];
	$abschlagszahlungHoehe = $additionalData["abschlagszahlungHoehe"];

	// Benötigte Objekte holen
	$prozess = $this->GetProcessStatus($db);
	$country = ($prozess!=null ? $prozess->GetCountryName() : "");
	$kostenmanager = ($prozess!=null ? $prozess->GetResponsibleRSUser() : null);
	$company = ($prozess!=null ? $prozess->GetCompany() : null);
	$companyVat = ($company!=null ? trim($company->GetVat()) : "");
	if ($companyVat!="" && $country!="" && $country!="DE") $umsatzsteuerProzent=0;
	
	$group = ($prozess!=null ? $prozess->GetGroup() : null);
	//$currency = $this->GetCurrency($db);
	$currency = "EUR";
	// Berechnung
	$verguetungRealisierteEinsparung = round($realisierteEinsparung*$verguetungRealisierteEinsparungProzent/100.0, 2);
	$verguetungNichtRealisierteEinsparung = round($nichtRealisierteEinsparung*$verguetungNichtRealisierteEinsparungProzent/100.0, 2);
	$verguetungGesamtNetto = $verguetungRealisierteEinsparung + $verguetungNichtRealisierteEinsparung + $abschlagszahlungHoehe;
	// Aufbereitung für Ausgabe
	$outputData = Array(
		"gruppeKunde" => ($group!=null ? $group->GetName() : "?"),
		"firmenName" => ($company!=null ? $company->GetName() : "?"),
		"firmenVat" => $companyVat,
		"firmenLand" => ($company!=null ? $company->GetCountry() : ""),
		"rechnungsEmpfaenger" => $additionalData["rechnungsempfaenger"],
		"firmenStrasse" => ($company!=null ? $company->GetStreet() : "?"),
		"firmenPLZAndCity" => ($company!=null ? $company->GetZIP()." ".$company->GetCity() : "?"),
		"rechnungsNummer" => $this->GetPaymentNumber(),
		"rechnungsDatum" => date("d.m.Y", $this->GetPaymentDate()),
		"kostenmanager" => ($kostenmanager!=null ? $kostenmanager->GetUserName() : "-"),
		"kostenmanagerPhone" => ($kostenmanager!=null ? $kostenmanager->GetPhone() : "-"),

		"country" => ($prozess!=null ? $prozess->GetCountryName() : "?"),
		"abrechnungsJahre" => ($prozess!=null ? $prozess->GetAbrechnungsJahrString() : "?"),
		"mietflaeche" => ($prozess!=null ? $prozess->GetLocationName() : "?"),
		"auftragsDatum" => (($prozess!=null && $prozess->GetAuftragsdatumAbrechnung()!=0) ? date("d.m.Y", $prozess->GetAuftragsdatumAbrechnung()) : "-"),
		"einsparungSumme" => HelperLib::ConvertFloatToLocalizedString( $realisierteEinsparung+$nichtRealisierteEinsparung ),
		"realisierteEinsparung" => HelperLib::ConvertFloatToLocalizedString( $realisierteEinsparung ),
		"verguetungRealisierteEinsparungProzent" => $verguetungRealisierteEinsparungProzent."%",
		"verguetungRealisierteEinsparung" => HelperLib::ConvertFloatToLocalizedString( $verguetungRealisierteEinsparung ),
		"nichtRealisierteEinsparung" => HelperLib::ConvertFloatToLocalizedString( $nichtRealisierteEinsparung ),
		"verguetungNichtRealisierteEinsparungProzent" => $verguetungNichtRealisierteEinsparungProzent."%",
		"verguetungNichtRealisierteEinsparung" => HelperLib::ConvertFloatToLocalizedString( $verguetungNichtRealisierteEinsparung ),
		"verguetungGesamtNetto" => HelperLib::ConvertFloatToLocalizedString( $verguetungGesamtNetto ),
		"umsatzsteuerProzent" => $umsatzsteuerProzent."%",
		"umsatzsteuer" => HelperLib::ConvertFloatToLocalizedString( $verguetungGesamtNetto*($umsatzsteuerProzent/100.0) ),
		"rechnungsbetrag" => HelperLib::ConvertFloatToLocalizedString( $verguetungGesamtNetto + $verguetungGesamtNetto*($umsatzsteuerProzent/100.0) ),
		"zahlungsfrist" => $this->GetPaymentDaysOfGrace(),
		"abschlagszahlungName" => $additionalData["abschlagszahlungName"],
		"abschlagszahlungHoehe" => $abschlagszahlungHoehe==0.0 ? "" : HelperLib::ConvertFloatToLocalizedString($abschlagszahlungHoehe),
	);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
		<title>SFM System zur Nebenkostenabrechnung</title>
		<meta name="author" content="Seybold GmbH"/>
		<meta name="publisher" content="Seybold GmbH"/>
		<meta name="copyright" content="© 2010 Seybold GmbH"/>
		<style type="text/css">
			<?
			$padding = 5;
			$border_color = "#000000";
			$fontsize = 12;
			?>
			
			body{
				font-size:<?=$fontsize?>px;
				font-weight:none;
				font-family: frutiger, Arial;
			}
			.bigtext{
				font-size:14px;
				font-family: frutiger, Arial;
			}
			.normaltext{
				font-weight: normal;
				font-family: frutiger, Arial;
			}
			.anschriftSeybold{
				font-size:8px;
				font-family: frutiger, Arial;
			}
			.footer{
				font-size:9px;
				font-family: frutiger, Arial;
			}
			hr{
				color: #000000; 
				background-color: #000000; 
				height: 1px;
				border: 0;
			}
			.textareadiv{
				font-size:16px;
				font-family: frutiger, Arial;
				font-weight:normal;
				border:1px solid black; 
				width:<?=$page_width?>px;
			}
			td{
				vertical-align: top;
				text-align:left;
			}

			.td_1_1 { font-size:<?=$fontsize?>px; padding:<?=$padding?>px; border-top: 1px solid <?=$border_color?>; border-bottom: 1px solid <?=$border_color?>; }
			.td_2_1 { font-size:<?=$fontsize?>px; padding:<?=$padding?>px; border: 0px; }
			.td_2_2, .td_2_3 { font-size:<?=$fontsize?>px; padding:<?=$padding?>px; border: 0px; }
			.td_2_3 { text-align:right; }
			.td_3_1 { font-size:<?=$fontsize?>px; padding:<?=$padding?>px; border-top: 1px solid <?=$border_color?>;}
			.td_3_2, .td_3_3 { font-size:<?=$fontsize?>px; padding:<?=$padding?>px; border-top: 1px solid <?=$border_color?>;}
			.td_3_3 { text-align:right; }
			
		</style>
	</head>
	<body style="margin:0;">
		<table style="width:100%; height:900px;" border="0" cellpadding="0" cellspacing="0">
			<tr style="height:40px;">
				<td style="width:70px;"></td>
				<td></td>
				<td style="width:50px;"></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<table style="width:100%; height:900;" border="0" cellpadding="0" cellspacing="0">
						<tr style="height:130px;">
							<td style="width:50%;"></td>
							<td></td>
						</tr>
						<tr>
							<td><span class="anschriftSeybold">Seybold GmbH&#160;&#160;Roßfelder Str. 65/5&#160;&#160;74564 Crailsheim<br /><br /></span></td>
							<td></td>
						</tr>
						<tr style="height:133px;">
							<td style="width:55%;">
								<?=$outputData["firmenName"]?><br />
								<?=$outputData["rechnungsEmpfaenger"]?><br />
								<?=$outputData["firmenStrasse"]?><br />
								<?=$outputData["firmenPLZAndCity"]?><br />
								<?=$outputData["firmenLand"]?><br />
							</td>
							<td style="width:45%;">
								<table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
									<tr>
										<td style="width: 125px;">
											Seybold GmbH<br />
											Roßfelder Str. 65/5<br />
											74564 Crailsheim<br />
											<strong>Germany</strong><br />
											<br />
										</td>
										<td>&#160;</td>
									</tr>
									<tr>
										<td>Ansprechpartner:</td>
										<td><?=$outputData["kostenmanager"]?></td>
									</tr>
									<tr>
										<td>Kontakt:</td>
										<td><?=$outputData["kostenmanagerPhone"]?></td>
									</tr>
									<tr>
										<td>&#160;</td>
										<td>&#160;</td>
									</tr>
									<tr>
										<td>Rechnungsnummer:</td>
										<td><?=$outputData["rechnungsNummer"]?></td>
									</tr>
									<tr>
										<td>Rechnungsdatum:</td>
										<td><?=$outputData["rechnungsDatum"]?></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="2" class="bigtext">Rechnung</td>
						</tr>
						<tr style="height:20px;">
							<td colspan="2"></td>
						</tr>
						<tr style="height:450px;">
							<td colspan="2">
								<table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
									<tr>
										<td class="td_1_1" style="width:65%;">Bezeichnung</td>
										<td class="td_1_1" style="width:20%;">Einheit</td>
										<td class="td_1_1" style="width:15%; text-align: right;">Gesamt</td>
									</tr>
									<tr>
										<td class="td_2_1">Prüfung der Nebenkostenabrechnung <?=$outputData["abrechnungsJahre"]?> des Stores in <?=$outputData["mietflaeche"]?> in der Zeit vom <?=$outputData["auftragsDatum"]?> bis <?=$outputData["rechnungsDatum"]?>  und aufzeigen einer Einsparungen von</td>
										<td class="td_2_2"></td>
										<td class="td_2_3"></td>
									</tr>
								<?	if ($realisierteEinsparung!=0.0){?>
										<tr>
											<td class="td_2_1"><?=$currency;?> <?=$outputData["realisierteEinsparung"]?></td>
											<td class="td_2_2"><?=$outputData["verguetungRealisierteEinsparungProzent"]?> der Einsparung</td>
											<td class="td_2_3"><?=$currency;?> <?=$outputData["verguetungRealisierteEinsparung"]?></td>
										</tr>
								<?	}?>
								<?	if ($nichtRealisierteEinsparung!=0.0){?>
										<tr>
											<td class="td_2_1">&#160;</td>
											<td class="td_2_2">&#160;</td>
											<td class="td_2_3">&#160;</td>
										</tr>
										<tr>
											<td class="td_2_1">Nach interner Entscheidung von <?=$outputData["gruppeKunde"]?> wurden von den aufgezeigten Einsparungen nicht realisiert:</td>
											<td class="td_2_2"></td>
											<td class="td_2_3"></td>
										</tr>
										<tr>
											<td class="td_2_1"><?=$currency;?> <?=$outputData["nichtRealisierteEinsparung"]?></td>
											<td class="td_2_2"><?=$outputData["verguetungNichtRealisierteEinsparungProzent"]?> der Einsparung</td>
											<td class="td_2_3"><?=$currency;?> <?=$outputData["verguetungNichtRealisierteEinsparung"]?></td>
										</tr>
								<?	}?>
								<?	if (trim($outputData["abschlagszahlungHoehe"])!=""){?>
										<tr>
											<td class="td_2_1">&#160;</td>
											<td class="td_2_2">&#160;</td>
											<td class="td_2_3">&#160;</td>
										</tr>
										<tr>
											<td class="td_2_1"><?=$outputData["abschlagszahlungName"]?></td>
											<td class="td_2_2"></td>
											<td class="td_2_3"><?=$currency;?> <?=$outputData["abschlagszahlungHoehe"]?></td>
										</tr>
								<?	}?>
									<tr>
										<td class="td_2_1">&#160;</td>
										<td class="td_2_2">&#160;</td>
										<td class="td_2_3">&#160;</td>
									</tr>
									<tr>
										<td class="td_2_1">Wir danken für Ihren Auftrag.</td>
										<td class="td_2_2"></td>
										<td class="td_2_3"></td>
									</tr>
									<tr>
										<td class="td_2_1">&#160;</td>
										<td class="td_2_2">&#160;</td>
										<td class="td_2_3">&#160;</td>
									</tr>
									<tr>
										<td class="td_3_1">Gesamt netto:</td>
										<td class="td_3_2"></td>
										<td class="td_3_3"><?=$currency;?> <?=$outputData["verguetungGesamtNetto"]?></td>
									</tr>
								<?	if($umsatzsteuerProzent!=0){?>
										<tr>
											<td class="td_2_1">Umsatzsteuer:</td>
											<td class="td_2_2"><?=$outputData["umsatzsteuerProzent"]?></td>
											<td class="td_2_3"><?=$currency;?> <?=$outputData["umsatzsteuer"]?></td>
										</tr>
								<?	}?>
									<tr>
										<td class="td_3_1"></td>
										<td class="td_3_2"></td>
										<td class="td_3_3"><?=$currency;?> <?=$outputData["rechnungsbetrag"]?></td>
									</tr>
								</table>
								<br />
								<br />
                                Bitte überweisen Sie den Rechnungsbetrag innerhalb von <?=$outputData["zahlungsfrist"]?> Tagen unter Angabe der <br />
                                Rechnungsnummer auf unser Konto 1922369 bei der Sparkasse Crailsheim (BLZ 62250030) IBAN: DE97 6225 0030 0001 9223 69, BIC: SOLADES1SHA. <br />

							<?	if ($umsatzsteuerProzent==0){?>
									<br />
									Die Rechnung ist ohne Umsatzsteuer erstellt - Steuerschuldnerschaft des Leistungsempfängers <?=($outputData["country"]=="PL" ? "(odwrotne obciążenie)" : "");?>.<br />
									<br />
									<?=$outputData["firmenName"]?><br />
									Umsatzsteuer-ID Nummer (VAT Nummer): <?=$outputData["firmenVat"]?><br />
							<?	}?>
							</td>
						</tr>
						<tr style="height:73px;">
							<td colspan="2" style="vertical-align:bottom;"></td>
						</tr>
					</table>
				</td>
				<td></td>
			</tr>
			<tr style="height:30px;">
				<td></td>
				<td></td>
				<td></td>
			</tr>
		</table>
	</body>
</html>
<?
$pdfMergin = Array('left' => 0, 'right' => 0, 'top' => 0, 'bottom' => 15);
$footer_html = '<span class="footer">Geschäftsführer: Marcus Seybold - Amtsgericht Ulm, HRB 727786 - Ust. Id-Nr. DE 282769521 - Steuernummer 57073/22076</span>';
//exit;
?>