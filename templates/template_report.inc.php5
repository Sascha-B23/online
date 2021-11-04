<?
	global $SHARED_HTTP_ROOT;
?>
<form action="#" method="post" id="FM_FORM" name="FM_FORM">
	<input type="hidden" name="print" id="print" value="" />
	<input type="hidden" name="show" id="show" value="on" />
	<input type="hidden" name="columnFilterSend" id="columnFilterSend" value="on" />
	
	<div style="height:90%; width:100%;" id="div_body_main_content" >
		<table border="0" bgcolor="#ffffff" cellpadding="0" cellspacing="0" width="1014px;">
			<tr>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_left.png); background-repeat: no-repeat; height:37px; width:32px;">&#160;</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_center.png); background-repeat: repeat-x; height:37px;">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td>
								<span style="position:relative; left:-20px; top:-2px;">
									<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/user.png" alt="" style="position:relative; top:8px;" />
									<strong><?=$this->GetReportName();?></strong>
								</span>
							</td>
							<td style="width: 250px; text-align:right;">
								<span style="position:relative; top:5px; left:-5px;">
								<?	if($this->IsFormatSupported(ReportManager::REPORT_FORMAT_PDF)){ ?>
										<a href="#" onclick="document.getElementById('print').value='<?=ReportManager::REPORT_FORMAT_PDF;?>'; document.forms.FM_FORM.submit(); document.getElementById('print').value='';">PDF</a>
										&#160;&#160;
								<?	}
									if($this->IsFormatSupported(ReportManager::REPORT_FORMAT_CSV)){ ?>
										<a href="#" onclick="document.getElementById('print').value='<?=ReportManager::REPORT_FORMAT_CSV;?>'; document.forms.FM_FORM.submit(); document.getElementById('print').value='';">CSV</a>
										&#160;&#160;
								<?	}?>
								</span>
							</td>
						</tr>
					</table>
				</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_right.png); background-repeat: no-repeat; height:37px; width:17px;">&#160;</td>
			</tr>
			<tr>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_left.png); background-repeat: repeat-y;"></td>
				<td style="vertical-align:top; text-align:left; width: 965px;">
					<br />
				<?	$coulmnFilters = $this->GetColumnFilters();
					if ($this->ShowFilter() || count($coulmnFilters)>0)
					{	?>
						<!-- Filter -->
						<div style="position:relative; left:20px;">
							<table border="0" cellpadding="0" cellspacing="0" style="width:900px; ">
							<?	if ($this->ShowFilter())
								{
									$elements = $this->GetFilterElements(); ?>
									<tr style="font-weight:bold;">
									<? 	$numColums = 4;
										for($a=0; $a<count($elements); $a++)
										{
											if (($a % $numColums)==0)
											{
												if($a>0){?>	
													</tr>
													<tr><td colspan="<?=$numColums;?>" style="height:10px;"></td></tr>
											<?	}?>
												<tr style="font-weight:bold;">
										<?	}?>
											<td valign="top">
												<?=$elements[$a]->GetName();?><br />
												<?$elements[$a]->PrintElement();?>
											</td>
									<?	}?>
									</tr>
							<?	}
								$countColumnFilters = 0;
								foreach ($coulmnFilters as $coulmnFilter)
								{
									if($coulmnFilter["hidden"] !== true) $countColumnFilters++;
								}
								if ($countColumnFilters>0)
								{?>
									<tr>
										<td style="height:10px;" colspan="<?=$numColums;?>">&#160;</td>
									</tr>
									<tr>
										<td style="font-weight:bold;" colspan="<?=$numColums;?>">Spalten anzeigen</td>
									</tr>
									<tr>
										<td colspan="<?=$numColums;?>">
										<?	for ($a=0; $a<count($coulmnFilters); $a++){
												if($coulmnFilters[$a]["hidden"] === true)
												{?>
												<input type="hidden" name="<?=$coulmnFilters[$a]["id"];?>" <?if($coulmnFilters[$a]["visible"]===true)echo "value='checked'";?>>
												<?}else{?>
												<input type="checkbox" name="<?=$coulmnFilters[$a]["id"];?>" <?if($coulmnFilters[$a]["visible"]===true)echo "checked";?>><?=$coulmnFilters[$a]["caption"];?>
										<?		}
											}?>
										</td>
									</tr>
							<?	}?>				
								<tr>
									<td>&#160;</td>
									<td>&#160;</td>
									<td>&#160;</td>
									<td valign="top">
										<br /><input type="submit" style="width:150px;" value="<?=$this->action_button_text;?>" class="formButton2" />
									</td>
								</tr>
							</table>
						</div>
						<br />
				<?	} ?>
					<!-- Content -->
				<?	$this->PrintHtml(); ?>
				</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_right.png); background-repeat: repeat-y; width:17x;">&#160;</td>
			</tr>
			<tr style="height:20px;">
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_left.png); background-repeat: repeat-y;"></td>
				<td style="vertical-align:top; text-align:left;">&#160;</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_right.png); background-repeat: repeat-y; width:17x;">&#160;</td>
			</tr>
		</table>
		<table border="0" bgcolor="#ffffff" cellpadding="0" cellspacing="0" width="1014px;">
			<tr>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/spezialfall/edit_bottom_left.png); background-repeat: no-repeat; height:63px; width:32px;">&#160;</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/spezialfall/edit_bottom_center.png); background-repeat: repeat-x; height:63px;">&#160;</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/spezialfall/edit_bottom_right.png); background-repeat: no-repeat; height:63px; width:17px;">&#160;</td>
			</tr>
		</table>
	</div>
</form>