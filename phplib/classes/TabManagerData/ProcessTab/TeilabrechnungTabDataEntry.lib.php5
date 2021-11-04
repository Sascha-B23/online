<?php
/**
 * TabDataEntry implementation
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class TeilabrechnungTabDataEntry extends TabDataEntry 
{
	/**
	 * db object
	 * @var DBManager 
	 */
	protected $db = null;
	
	/**
	 * current proccess
	 * @var ProcessStatus
	 */
	protected $processStatus = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ProcessStatus $processStatus
	 */
	public function TeilabrechnungTabDataEntry(DBManager $db, ProcessStatus $processStatus)
	{
		$this->db = $db;
		$this->processStatus = $processStatus;
		parent::__construct(ProcessTabData::TAB_TA, "Teilabrechnung");
	}
	
	/**
	 * Return if the tab is visible
	 * @return bool 
	 */
	public function IsVisible()
	{
		if (!UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_SHOW_PROCESS_TAB_TEILABRECHNUNG)) return false;
		if ($this->processStatus->GetCurrentTeilabrechnung()==null) return false;
		return true;
	}
	
	/**
	 * Output the HTML for this tabs
	 * @return bool
	 */
	public function PrintContent()
	{
		global $DOMAIN_HTTP_ROOT, $lm;
		$abrechnungsjahr = $this->processStatus->GetAbrechnungsJahr();
		$teilabrechnung = $this->processStatus->GetTeilabrechnung($this->db);
		$tas = $abrechnungsjahr->GetTeilabrechnungen($this->db);
		if (isset($_POST["showteilabrechnung"]))
		{
			for ($a=0; $a<count($tas); $a++)
			{
				if ($tas[$a]->GetPKey()==(int)$_POST["showteilabrechnung"])
				{
					$teilabrechnung=$tas[$a];
					break;
				}
			}
		}
		if ($teilabrechnung!=null)
		{
			$bezeichnung = $teilabrechnung->GetBezeichnung();
			$datum = $teilabrechnung->GetDatum();
			if ($datum==0) $datum="";
			else $datum = date("d.m.Y", $datum);
			
			$abrechnungszeitraumVon = $teilabrechnung->GetAbrechnungszeitraumVon();
			if ($abrechnungszeitraumVon==0) $abrechnungszeitraumVon="";
			else $abrechnungszeitraumVon = date("d.m.Y", $abrechnungszeitraumVon);
			
			$abrechnungszeitraumBis = $teilabrechnung->GetAbrechnungszeitraumBis();
			if ($abrechnungszeitraumBis==0) $abrechnungszeitraumBis="";
			else $abrechnungszeitraumBis = date("d.m.Y", $abrechnungszeitraumBis);
			
			$fristBelegeinsicht = $teilabrechnung->GetFristBelegeinsicht();
			if ($fristBelegeinsicht==0) $fristBelegeinsicht="";
			else $fristBelegeinsicht = date("d.m.Y", $fristBelegeinsicht);
			
			$fristWiderspruch = $teilabrechnung->GetFristWiderspruch();
			if ($fristWiderspruch==0) $fristWiderspruch="";
			else $fristWiderspruch = date("d.m.Y", $fristWiderspruch);
			
			$fristZahlung = $teilabrechnung->GetFristZahlung();
			if ($fristZahlung==0) $fristZahlung="";
			else $fristZahlung = date("d.m.Y", $fristZahlung);
			
			$asp = $teilabrechnung->GetAnsprechpartner();
			if ($asp!=null)
			{
				$aspName = $asp->GetOverviewString();
			}
			$files=$teilabrechnung->GetFiles($this->db);
			// add files from 'Antwortschreiben'...
			$ws = $this->processStatus->GetWiderspruch($this->db);
			if ($ws!=null)
			{
				$aws = $ws->GetAntwortschreiben($this->db);
				for ($a=0; $a<count($aws);$a++)
				{
					$filesTemp = $aws[$a]->GetFiles($this->db, FM_FILE_SEMANTIC_ABRECHNUNGSKORREKTURGUTSCHRIFT);
					for ($b=0; $b<count($filesTemp);$b++)
					{
						$files[] = $filesTemp[$b];
					}
				}
			}
		}
		?>
		<script type="text/javascript">
			<!--
			function EditAbschlagszahlung(){
				var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/editAbschlagszahlung.php5?<?=SID;?>&editElement=<?=$teilabrechnung->GetPKey();?>', '_editAbschlagszahlung', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
				//newWin.moveTo(width,height);
				newWin.focus();
			}
			function EditTeilabrechnung(){
				var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/editTeilabrechnung.php5?<?=SID;?>&editElement=<?=$teilabrechnung->GetPKey();?>', '_editTeilabrechnung', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
				//newWin.moveTo(width,height);
				newWin.focus();
			}
			-->
		</script>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td width="29">&#160;</td>
				<td width="287">&#160;</td>
				<td width="29">&#160;</td>
				<td width="287">&#160;</td>
				<td width="29">&#160;</td>
				<td width="287">&#160;</td>
				<td width="29">&#160;</td>
			</tr>
			<tr>
				<td width="29">&#160;</td>
				<td width="287" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td width="60%" align="right" valign="top" class="contentText"><strong><?=Teilabrechnung::GetAttributeName($lm, 'bezeichnung');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=($bezeichnung!="" ? $bezeichnung : "-");?></td>
						</tr>
						<tr>
							<td valign="top" width="60%" align="right" class="contentText"><strong><?=Teilabrechnung::GetAttributeName($lm, 'datum');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=($datum!="" ? $datum : "-");?></td>
						</tr>
						<tr>
							<td valign="top" width="60%" align="right" class="contentText"><strong><?=Teilabrechnung::GetAttributeName($lm, 'abrechnungszeitraumVon');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=($abrechnungszeitraumVon!="" ? $abrechnungszeitraumVon : "-");?></td>
						</tr>
						<tr>
							<td valign="top" width="60%" align="right" class="contentText"><strong><?=Teilabrechnung::GetAttributeName($lm, 'abrechnungszeitraumBis');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=($abrechnungszeitraumBis!="" ? $abrechnungszeitraumBis : "-");?></td>
						</tr>
					<? 	if(count($tas)>1){?>
							<tr>
								<td width="60%" align="right" valign="top" class="contentText"><strong>Auswahl Teilabrechnung</strong></td>
								<td valign="top" width="10px" align="right">&#160;</td>
								<td valign="top" align="left" class="contentText">
									<table border="0" cellpadding="0" cellspacing="0">
										<tr>
											<td>
												<select name="showteilabrechnung">
											<? 	for($a=0; $a<count($tas); $a++){?>
													<option value="<?=$tas[$a]->GetPKey();?>" <?if($tas[$a]->GetPKey()==$teilabrechnung->GetPKey())echo "selected";?>><?=$tas[$a]->GetBezeichnung();?></option>
											<?	}?>
												</select>
											</td>
											<td>
												<input type="submit" value=">" />
											</td>
										</tr>
									</table>
								</td>
							</tr>
					<? }?>
					</table>
				</td>
				<td width="29">&#160;</td>
				<td width="287" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong>Ansprechpartner</strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=$aspName;?></td>
						</tr>
						<tr>
							<td valign="top" width="40%" align="right" class="contentText"><strong><?=Teilabrechnung::GetAttributeName($lm, 'fristBelegeinsicht');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=($fristBelegeinsicht!="" ? $fristBelegeinsicht : "-");?></td>
						</tr>
						<tr>
							<td valign="top" width="40%" align="right" class="contentText"><strong><?=Teilabrechnung::GetAttributeName($lm, 'fristWiderspruch');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=($fristWiderspruch!="" ? $fristWiderspruch : "-");?></td>
						</tr>
						<tr>
							<td valign="top" width="40%" align="right" class="contentText"><strong><?=Teilabrechnung::GetAttributeName($lm, 'fristZahlung');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=($fristZahlung!="" ? $fristZahlung : "-");?></td>
						</tr>
					</table>
				</td>
				<td width="29">&#160;</td>
				<td width="287" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<? 	if( $_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP && !in_array($_SESSION["currentUser"]->GetGroupBasetype($this->db), Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT)) ){?>
							<tr>
								<td valign="top" width="60%" align="right" class="contentText"><strong>Teilabrechnung</strong></td>
								<td valign="top" width="10px" align="right">&#160;</td>
								<td valign="top" align="left" class="contentText"><a href="javascript:EditTeilabrechnung();">Bearbeiten</a></td>
							</tr>
					<?	}?>
						<tr>
							<td valign="top" width="60%" align="right" class="contentText"><strong>Teilabrechnungspositionen</strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><a href="showTAPs.php5?<?=SID;?>&aj=<?=$abrechnungsjahr->GetPKey();?>&ta=<?=$teilabrechnung->GetPKey();?>" target="_showTAPs">Anzeigen</a></td>
						</tr>
						<tr>
							<td valign="top" width="40%" align="right" class="contentText"><strong>Teilabrechnung</strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText">
							<? 	for($a=0; $a<count($files); $a++){ ?>
									<a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($files[$a])?>&timestamp=<?=time();?>"><?=$files[$a]->GetFileName();?></a><br />
							<?	}
								if( count($files)==0 )echo "-";?>
							</td>
						</tr>
					</table>
				</td>
				<td width="29">&#160;</td>
			</tr>
			<tr>
				<td width="29">&#160;</td>
				<td colspan="5">
					<? self::PrintComparisonTable($this->db, $this->processStatus, $teilabrechnung, "javascript:EditAbschlagszahlung();"); ?>
				</td>
				<td width="29">&#160;</td>
			</tr>
		</table>
		<br />
		<?
	}
	
	/**
	 * Print comparison table
	 * @global type $SHARED_HTTP_ROOT
	 * @param DBManager $db
	 * @param ProcessStatus $processStatus
	 * @param Teilabrechnung $teilabrechnung
	 * @param string $linkEditAbschlagszahlung
	 * @param bool $reportPage 
	 */
	public static function PrintComparisonTable(DBManager $db, ProcessStatus $processStatus, Teilabrechnung $teilabrechnung=null, $linkEditAbschlagszahlung="", $reportPage=false)
	{
		global $SHARED_HTTP_ROOT, $lm;
		$widerspruch = $processStatus->GetWiderspruch($db);
		if ($teilabrechnung==null) $teilabrechnung = $processStatus->GetTeilabrechnung($db);
		$wsSumme = 0.0;
		$vorauszahlungLautKunde = 0.0;
		$vorauszahlungLautAbrechnung = 0.0;
		$abrechnungsergebnisLautAbrechnung = 0.0;
		$summeBetragKundeTAPs = 0.0;
		$abschlagszahlungGutschrift = 0.0;
		$nachzahlungGutschrift = 0.0;
		$showDifference = true;
		if( $teilabrechnung!=null )
		{
			$vorauszahlungLautKunde = $teilabrechnung->GetVorauszahlungLautKunde();
			$vorauszahlungLautAbrechnung = $teilabrechnung->GetVorauszahlungLautAbrechnung();
			$abrechnungsergebnisLautAbrechnung = $teilabrechnung->GetAbrechnungsergebnisLautAbrechnung();
			$summeBetragKundeTAPs = $teilabrechnung->GetSummeBetragKunde($db);
			$abschlagszahlungGutschrift = $teilabrechnung->GetAbschlagszahlungGutschrift();
			$nachzahlungGutschrift = $teilabrechnung->GetNachzahlungGutschrift();
			if ($teilabrechnung->IsSettlementDifferenceHidden()) $showDifference = false;
		}
		if (abs((float)$abrechnungsergebnisLautAbrechnung-(float)$summeBetragKundeTAPs)<=1.0 && abs((float)$vorauszahlungLautKunde-(float)$vorauszahlungLautAbrechnung)<=1.0) $showDifference = false;
		$showCheckboxDifference = ($showDifference && !$reportPage && $_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE);
		if ($widerspruch!=null)
		{
			$wsSumme = $widerspruch->GetWiderspruchsSumme($db);
		}
		$abrechnungsergebnisRS = (float)$summeBetragKundeTAPs-(float)$vorauszahlungLautKunde-(float)$wsSumme;
		
	
		
		
	?>
	
	<?	if( $teilabrechnung!=null && $showCheckboxDifference ){ ?>
			<script type="text/javascript">
			<!--
				function OnClickHideSettlementDifference()
				{
					var request_succes = false;
					if (confirm("Hierdurch wird die Differenz dauerhaft ausgeblendet.\n\nMöchten Sie fortfahren?") )
					{
						// Send JSON-Request
						var hideSettlementDifferenceRequest = new Request.JSON (
							{	url:'<?=$SHARED_HTTP_ROOT."phplib/jsInterface.php5";?>', 
								method: 'post',
								noCache: true,
								async: false,
								onSuccess: function(responseJSON, responseText) 
								{
									if (responseJSON=='OK')
									{
										request_succes = true;
									}
								},
								onFailure: function(xhr) 
								{
									alert('Die Aktion konnte nicht ausgeführt werden.\n\nBitte versuchen Sie es zu einem späteren Zeitpunkt erneut.');
								}
							}
						);
						hideSettlementDifferenceRequest.send('<?=SID.'&reqDataType=12&param01='.$teilabrechnung->GetPKey();?>');
						if (request_succes)
						{
							document.forms.FM_FORM.submit();
							return true;
						}
						return false;
					}
					return false;
				}
			-->
			</script>
	<?	}?>
			<br />
			<table style="border-collapse: collapse;">
				<tr >
					<th valign="top" class="contentText" style="border-bottom: 1px solid #666666; width: 10px;">&#160;</th>
					<th valign="top" class="contentText" style="border-bottom: 1px solid #666666; border-right: 1px solid #666666; width: 170px;">&#160;</th>
					<th valign="top" align="left" class="contentText" style="border-bottom: 1px solid #666666; border-right: 1px solid #666666; width: 70px;"><strong>Abrechnung</strong></th>
					<th valign="top" align="left" class="contentText" style="border-bottom: 1px solid #666666; border-right: 1px solid #666666; width: 70px;"><strong>SFM</strong></th>
				<?	if ($showDifference){?>
						<th colspan="2" valign="top" align="left" class="contentText" style="border-bottom: 1px solid #666666; width: <?=($showCheckboxDifference ? "250" : "75");?>px;"><strong>Differenz</strong></th>
				<?	}?>
				</tr>
				<tr>
					<th valign="top" class="contentText">&#160;</th>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;">Summe Betrag TAPs</td>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><nobr><?=($abrechnungsergebnisLautAbrechnung!="" ? HelperLib::ConvertFloatToLocalizedString($abrechnungsergebnisLautAbrechnung) : "-");?></nobr></td>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><nobr><?=($summeBetragKundeTAPs!="" ? HelperLib::ConvertFloatToLocalizedString($summeBetragKundeTAPs) : "-");?></nobr></td>
				<?	if ($showDifference){?>
						<td <?if (!$showCheckboxDifference){?>colspan="2"<?}?> valign="top" align="right" class="contentText"><nobr><font style="color: #ff0000;"><?=abs((float)$abrechnungsergebnisLautAbrechnung-(float)$summeBetragKundeTAPs)<=1.0 ? "" : HelperLib::ConvertFloatToLocalizedString((float)$summeBetragKundeTAPs-(float)$abrechnungsergebnisLautAbrechnung);?></font></nobr></td>
						<?if ($showCheckboxDifference){?>
							<td valign="top" align="right" class="contentText">
								<input type="checkbox" onClick="return OnClickHideSettlementDifference();" <?if($teilabrechnung->IsSettlementDifferenceHidden())echo "checked";?> /><?=Teilabrechnung::GetAttributeName($lm, 'hideSettlementDifference');?>
							</td>
						<?}?>
				<?	}?>
				</tr>
				<tr>
					<th valign="top" class="contentText">-</th>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;">Vorauszahlung</td>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><nobr><?=($vorauszahlungLautAbrechnung!="" ? HelperLib::ConvertFloatToLocalizedString($vorauszahlungLautAbrechnung) : "-");?></nobr></td>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><nobr><?=($vorauszahlungLautKunde!="" ? HelperLib::ConvertFloatToLocalizedString($vorauszahlungLautKunde) : "-");?></nobr></td>
				<?	if ($showDifference){?>
						<td <?if (!$showCheckboxDifference){?>colspan="2"<?}?> valign="top" align="right" class="contentText"><nobr><font style="color: #ff0000;"><?=abs((float)$vorauszahlungLautKunde-(float)$vorauszahlungLautAbrechnung)<=1.0 ? "" : HelperLib::ConvertFloatToLocalizedString((float)$vorauszahlungLautKunde-(float)$vorauszahlungLautAbrechnung);?></font></nobr></td>
						<?if ($showCheckboxDifference){?><td valign="top" align="right" class="contentText">&#160;</td><?}?>
				<?	}?>
				</tr>
				<tr>
					<th valign="top" class="contentText">-</th>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;">WS-Summe</td>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;">&#160;</td>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><nobr><?=($nachzahlungGutschrift!="" ? HelperLib::ConvertFloatToLocalizedString($wsSumme) : "-");?></nobr></td>
				<?	if ($showDifference){?>
						<td colspan="2" valign="top" align="right" class="contentText">&#160;</td>
				<?	}?>
				</tr>
				<tr>
					<th valign="top" class="contentText"><strong>=</strong></th>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><strong>Abrechnungsergebnis</strong></td>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><nobr><strong><?=($nachzahlungGutschrift!="" ? HelperLib::ConvertFloatToLocalizedString($nachzahlungGutschrift) : "-");?></strong></nobr></td>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><nobr><strong><?=( HelperLib::ConvertFloatToLocalizedString($abrechnungsergebnisRS) );?></strong></nobr></td>
				<?	if ($showDifference){?>
						<td colspan="2" valign="top" align="right" class="contentText">&#160;</td>
				<?	}?>
				</tr>
				<tr>
					<th valign="top" class="contentText">-</th>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;">Abschlagszahlung/Gutschrift</td>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><nobr><?if( $linkEditAbschlagszahlung!="" && $_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE && !in_array($_SESSION["currentUser"]->GetGroupBasetype($db), Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT)) ){ ?><a href="<?=$linkEditAbschlagszahlung;?>"><?}?><?=(HelperLib::ConvertFloatToLocalizedString((float)$abschlagszahlungGutschrift));?><?if( $linkEditAbschlagszahlung!="" && $_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE && !in_array($_SESSION["currentUser"]->GetGroupBasetype($db), Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE)) ){ ?></a><?}?></nobr></td>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><nobr><?if( $linkEditAbschlagszahlung!="" && $_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE && !in_array($_SESSION["currentUser"]->GetGroupBasetype($db), Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT)) ){ ?><a href="<?=$linkEditAbschlagszahlung;?>"><?}?><?=(HelperLib::ConvertFloatToLocalizedString((float)$abschlagszahlungGutschrift));?><?if( $linkEditAbschlagszahlung!="" && $_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE && !in_array($_SESSION["currentUser"]->GetGroupBasetype($db), Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE)) ){ ?></a><?}?></nobr></td>
				<?	if ($showDifference){?>
						<td colspan="2" valign="top" align="right" class="contentText">&#160;</td>
				<?	}?>
				</tr>
				<tr>
					<th valign="top" class="contentText"><strong>=</strong></th>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><strong>Zahlungssaldo</strong></td>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><nobr><strong><?=HelperLib::ConvertFloatToLocalizedString((float)$nachzahlungGutschrift-(float)$abschlagszahlungGutschrift);?></strong></nobr></td>
					<td valign="top" align="right" class="contentText" style="border-right: 1px solid #666666;"><nobr><strong><?=HelperLib::ConvertFloatToLocalizedString((float)$abrechnungsergebnisRS-(float)$abschlagszahlungGutschrift);?></strong></nobr></td>
				<?	if ($showDifference){?>
						<td colspan="2" valign="top" align="right" class="contentText">&#160;</td>
				<?	}?>
				</tr>
			</table>
		<?
	}
	
}
?>