<?	global $SHARED_HTTP_ROOT;
	// Ampelfarben definieren
	$color_sv = array(
		Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_UNKNOWN => "#E3DFE0",
		Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN => "#76BA17",
		Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB => "#DA8A01",
		Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT => "#B12C03",
		Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU => "#888888",
	);
	for($i=0; $i<count($data); $i++)
	{
		for($a=0; $a<count($data[$i]["abrechnungen"]); $a++)
		{?>
			<table border="0" bgcolor="#ffffff" cellpadding="0" cellspacing="0" width="964px;">
				<tr>
					<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/spezialfall/edit_top_left.png); background-repeat: no-repeat; height:28px; width:32px;">&#160;</td>
					<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/spezialfall/edit_top_center.png); background-repeat: repeat-x; height:28px; width:900px;">
						<span style="position:relative; left:-20px;">
							<img src="<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_aktiv.png" alt="" style="position:relative; top:1px;" />
							<strong><?=$data[$i]["standort"]?> > <?=$data[$i]["laden"]?> > Abrechnung <?=$data[$i]["abrechnungen"][$a]["abrechnungsjahr"]?></strong>
						</span>
					</td>
					<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/spezialfall/edit_top_right.png); background-repeat: no-repeat; height:28px; width:32px;">&#160;</td>
				</tr>
				<tr>
					<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_left.png); background-repeat: repeat-y;"></td>
					<td style="vertical-align:top; text-align:left;">
						<table border="0" cellpadding="0" cellspacing="0">
							<tr style="height:10px;">
								<td></td>
								<td style="width:10px"></td>
								<td></td>
							</tr>
							<tr>
								<td align="right"><strong><?=CGroup::GetAttributeName($this->languageManager, 'name');?></strong></td>
								<td></td>
								<td><?=$data[$i]["gruppe"]?></td>
							</tr>
							<tr>
								<td align="right"><strong><?=CCompany::GetAttributeName($this->languageManager, 'name');?></strong></td>
								<td></td>
								<td><?=$data[$i]["firma"]?></td>
							</tr>
							<tr>
								<td align="right"><strong><?=CLocation::GetAttributeName($this->languageManager, 'name');?></strong></td>
								<td></td>
								<td><?=$data[$i]["standort"]?> | <?=$data[$i]["anschrift"]?></td>
							</tr>
							<tr>
								<td align="right"><strong><?=CLocation::GetAttributeName($this->languageManager, 'locationType');?></strong></td>
								<td></td>
								<td><?=$data[$i]["standorttyp"]?></td>
							</tr>
						</table>
						<table border="0" cellpadding="0" cellspacing="0" style="width:900px;">
							<tr>
								<td style="width:460px;" valign="top">
									<table border="0" cellpadding="0" cellspacing="0" style="width:460px;">
										<tr style="height:8px;">
											<td style="width:367px;"></td>
											<td style="width:9px;"></td>
											<td style="width:84px;"></td>
										</tr>
										<tr>
											<td style="background-color:#F5F5F5; padding-left:10px;"><strong>Position</strong></td>
											<td></td>
											<td style="background-color:#E3DFE0; padding-right:10px;" align="right"><strong>Summe</strong></td>
										</tr>
										<?for($b=0; $b<count($data[$i]["abrechnungen"][$a]["positionen"]); $b++){?>
											<tr style="height:4px;">
												<td></td>
												<td></td>
												<td></td>
											</tr>
											<tr>
												<td style="background-color:#F5F5F5; padding-left:10px;" valign="top"><?=$data[$i]["abrechnungen"][$a]["positionen"][$b]["name"]?></td>
												<td></td>
												<td>
													<?
													$posValues = Array();
													if ($data[$i]["abrechnungen"][$a]["positionen"][$b]["betragGruen"]>0.0) $posValues[] = Array("value" => $data[$i]["abrechnungen"][$a]["positionen"][$b]["betragGruen"], "einstufung" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN );
													if ($data[$i]["abrechnungen"][$a]["positionen"][$b]["betragGelb"]>0.0) $posValues[] = Array("value" => $data[$i]["abrechnungen"][$a]["positionen"][$b]["betragGelb"], "einstufung" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB );
													if ($data[$i]["abrechnungen"][$a]["positionen"][$b]["betragRot"]>0.0) $posValues[] = Array("value" => $data[$i]["abrechnungen"][$a]["positionen"][$b]["betragRot"], "einstufung" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT );
													if ($data[$i]["abrechnungen"][$a]["positionen"][$b]["betragGrau"]>0.0) $posValues[] = Array("value" => $data[$i]["abrechnungen"][$a]["positionen"][$b]["betragGrau"], "einstufung" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU );
													if (count($posValues)>0)
													{
													?>
														<table border="0" cellpadding="0" cellspacing="0" width="100%">
														<?	for ($c=0; $c<count($posValues); $c++){ ?>
																<tr>
																	<td style="background-color:<?=$color_sv[$posValues[$c]["einstufung"]]?>; color:#ffffff; padding-right:10px;" align="right" valign="top">
																		<strong><?=HelperLib::ConvertFloatToRoundedLocalizedString($posValues[$c]["value"]);?> <?=$data[$i]["currency"];?></strong>
																	</td>
																</tr>
														<?	}?>	
														</table>
													<?}else{?>
														<table border="0" cellpadding="0" cellspacing="0" width="100%">
															<tr>
																<td style="background-color:#F5F5F5; color:#000000; padding-right:10px;" align="right" valign="top">-</td>
															</tr>
														</table>
													<? }?>
												</td>
											</tr>
											<?
											$betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN] += round($data[$i]["abrechnungen"][$a]["positionen"][$b]["betragGruen"]);
											$betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB] += round($data[$i]["abrechnungen"][$a]["positionen"][$b]["betragGelb"]);
											$betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT] += round($data[$i]["abrechnungen"][$a]["positionen"][$b]["betragRot"]);
											$betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU] += round($data[$i]["abrechnungen"][$a]["positionen"][$b]["betragGrau"]);
											?>
										<?}?>
										<?
										$gesamtbetrag[$i][$a] = $betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN]+$betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB]+$betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU];
										$summe[$i]+=$gesamtbetrag[$i][$a];
										?>
										<tr style="height:4px;">
											<td></td>
											<td></td>
											<td></td>
										</tr>
										<tr>
											<td style="background-color:#E3DFE0; padding-left:10px;"><strong>Gesamt (ohne Rot)</strong></td>
											<td></td>
											<td style="background-color:#E3DFE0; padding-right:10px;" align="right"><strong><?=HelperLib::ConvertFloatToRoundedLocalizedString($gesamtbetrag[$i][$a]);?> <?=$data[$i]["currency"];?></strong></td>
										</tr>
									</table>
								</td>
								<td style="width:85px;"></td>
								<td style="width:355px;" valign="bottom" align="left">
									<table border="0" cellpadding="0" cellspacing="0" style="width:355px;">
										<tr>
											<?
											$betrag["complete"][$i][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN] += $betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN];
											$betrag["complete"][$i][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB] += $betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB];
											$betrag["complete"][$i][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT] += $betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT];
											$betrag["complete"][$i][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU] += $betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU];
											?>
											<td style="width:82px; background-color:<?=$color_sv[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN];?>; color:#ffffff; padding-right:10px;" align="right"><strong><?=HelperLib::ConvertFloatToRoundedLocalizedString($betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN])?> <?=$data[$i]["currency"];?></strong></td>
											<td style="width:9px;"></td>
											<td style="width:82px; background-color:<?=$color_sv[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB];?>; color:#ffffff; padding-right:10px;" align="right"><strong><?=HelperLib::ConvertFloatToRoundedLocalizedString($betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB])?> <?=$data[$i]["currency"];?></strong></td>
											<td style="width:9px;"></td>
											<td style="width:82px; background-color:<?=$color_sv[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT];?>; color:#ffffff; padding-right:10px;" align="right"><strong><?=HelperLib::ConvertFloatToRoundedLocalizedString($betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT])?> <?=$data[$i]["currency"];?></strong></td>
											<td style="width:9px;"></td>
											<td style="width:82px; background-color:<?=$color_sv[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU];?>; color:#ffffff; padding-right:10px;" align="right"><strong><?=HelperLib::ConvertFloatToRoundedLocalizedString($betrag[$i][$a][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU])?> <?=$data[$i]["currency"];?></strong></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
					<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_right.png); background-repeat: repeat-y;">&#160;</td>
				</tr>
				<tr style="height:20px;">
					<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_left.png); background-repeat: repeat-y;"></td>
					<td style="vertical-align:top; text-align:left;">&#160;</td>
					<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_right.png); background-repeat: repeat-y;">&#160;</td>
				</tr>
			</table>
	<?	}?>
		<table border="0" bgcolor="#ffffff" cellpadding="0" cellspacing="0" width="964px;">
			<tr>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/spezialfall/edit_top_left.png); background-repeat: no-repeat; height:28px; width:32px;">&#160;</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/spezialfall/edit_top_center.png); background-repeat: repeat-x; height:28px; width:900px;">
					<table border="0" cellpadding="0" cellspacing="0" style="width:900px;">
						<tr>
							<td style="width:376px;">
								<span style="position:relative; left:-20px;">
									<img src="<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_aktiv.png" alt="" style="position:relative; top:1px;" />
									<span style="color:#D9123D;">
										<strong>Summe (ohne Rot) <?=$data[$i]["standort"]?> > <?=$data[$i]["laden"]?></strong>
									</span>
								</span>
							</td>
							<td style="width:524px;">
								<table border="0" cellpadding="0" cellspacing="0" style="width:524px;">
									<tr>
										<td style="width:84px; background-color:#ffffff; color:#D9123D; padding-right:10px;" align="right"><strong><?=HelperLib::ConvertFloatToRoundedLocalizedString($summe[$i]);?> <?=$data[$i]["currency"];?></strong></td>
										<td style="width:85px;"></td>
										<td style="width:82px; background-color:<?=$color_sv[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN];?>; color:#ffffff; padding-right:10px;" align="right"><strong><?=HelperLib::ConvertFloatToRoundedLocalizedString($betrag["complete"][$i][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN]);?> <?=$data[$i]["currency"];?></strong></td>
										<td style="width:9px;"></td>
										<td style="width:82px; background-color:<?=$color_sv[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB];?>; color:#ffffff; padding-right:10px;" align="right"><strong><?=HelperLib::ConvertFloatToRoundedLocalizedString($betrag["complete"][$i][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB]);?> <?=$data[$i]["currency"];?></strong></td>
										<td style="width:9px;"></td>
										<td style="width:82px; background-color:<?=$color_sv[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT];?>; color:#ffffff; padding-right:10px;" align="right"><strong><?=HelperLib::ConvertFloatToRoundedLocalizedString($betrag["complete"][$i][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT]);?> <?=$data[$i]["currency"];?></strong></td>
										<td style="width:9px;"></td>
										<td style="width:82px; background-color:<?=$color_sv[Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU];?>; color:#ffffff; padding-right:10px;" align="right"><strong><?=HelperLib::ConvertFloatToRoundedLocalizedString($betrag["complete"][$i][Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU]);?> <?=$data[$i]["currency"];?></strong></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/spezialfall/edit_top_right.png); background-repeat: no-repeat; height:28px; width:32px;">&#160;</td>
			</tr>
		</table>
<?	}?>