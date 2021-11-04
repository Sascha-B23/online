<?php
	global $SHARED_HTTP_ROOT;
	/* @var $this SyncManager */
	/* @var $dataRows SyncDataRow[] */
	
?>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%;">
	<tr>
		<td valign="top" style="height:29px;">
			<table style="width:100%; height:29px; background:url(<?=$SHARED_HTTP_ROOT?>pics/liste/dif_background.jpg);" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="200">
						<table border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/snycronisation.png" alt="" style="width:28px; height:27px;" /></td>
								<td><strong>Import-Ergebnis</strong></td>
							</tr>
						</table>
					</td>
					<td align="right">
						<table border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td><div style="width:15px; height:15px; background-color:#DA8A01; border:1px solid #CBCBCB;"></div></td>
								<td width="5"></td>
								<td>Fehlerhaft</td>
								<td width="21"></td>
							</tr>
						</table>
					</td>
					<td width="27">&#160;</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<div id="floaterDiv" name="floaterDiv" style="width:900px; height:471px; overflow:scroll; border-bottom: 1px solid #C2C2C2;">
				<form name="table_import" id="table_import" method="post">
					<input type="hidden" name="doImport" value="true" />
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<td valign="bottom" style="height:32px; width:30px; border-right:1px solid #CACACA; border-bottom:2px solid #000000;"><strong>CSV Zeile</strong></td>
						<?  // print column names
							for ($i=0; $i<$this->GetColumnCount(); $i++)
							{
								$column = $this->GetColumnByIndex($i);
								if ($column==null) continue;?>
								<td valign="bottom" style="height:32px; border-right:1px solid #CACACA; border-bottom:2px solid #000000;"><strong><?=$column->GetName();?></strong></td>
						<?	}?>
							<td style="height:32px; width:51px; border-right:1px solid #CACACA; border-bottom:2px solid #000000;"><strong>IMPORT</strong></td>
						</tr>
					<?	// loop through all data rows and print them
						for ($i=0; $i<count($dataRows); $i++)
						{
							$dataRow = $dataRows[$i];?>
							<tr>
								<td valign="top" height="30" style="border-right:1px solid #CACACA; border-bottom:2px solid #CACACA;"><?=$dataRow->GetCsvLineNumber();?></td>
							<?	for ($a=0; $a<$this->GetColumnCount(); $a++)
								{
									$column = $this->GetColumnByIndex($a);
									if ($column==null) continue;
									// get the data cell
									$dataCell = $dataRow->GetDataCellForColumn($column->GetId());
									// select color to highlight cell
									$statuscolor = "#FFFFFF;";
									if (trim($dataCell->GetImportErrorMessage())!="") $statuscolor = "#DA8A01;";
									?>
									<td height="30" valign="top" style="background-color:<?=$statuscolor?> border-right:1px solid #CACACA; border-bottom:2px solid #CACACA;"><?=$dataCell->GetDisplayValue();?>&#160;</td>
							<?	}?>
								<td height="30" align="center" style="border-right:1px solid #CACACA; border-bottom:2px solid #CACACA;">
								<?	if ($dataRow->IsImportSuccessfully()){
										?><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/check.gif" width="18" height="18" alt="Datensatz wurde erfolgreich importiert" title="Datensatz wurde erfolgreich importiert" /><?
									} else {
										?><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/warning.png" alt="<?=$dataRow->GetImportErrorMessage()?>" title="<?=$dataRow->GetImportErrorMessage()?>" /><?
									}?>
								</td>
							</tr>
					<?	}?>
					</table>
				</form>
			</div>
			<script type="text/javascript">
				<!--
					function GetWindowWidth() 
					{
						if (window.innerWidth) 
						{
							return window.innerWidth;
						} 
						else if (document.body && document.body.offsetWidth) 
						{
							return document.body.offsetWidth;
						} 
						else 
						{
							return 0;
						}
					}

					function GetWindowHeight() 
					{
						if (window.innerHeight) 
						{
							return window.innerHeight;
						} 
						else if (document.body && document.body.offsetHeight) 
						{
							return document.body.offsetHeight;
						} 
						else 
						{
							return 0;
						}
					}

					window.onresize = function()
					{
						$('floaterDiv').style.width = (GetWindowWidth()-50)+"px";
						$('floaterDiv').style.height = (GetWindowHeight()-209)+"px";
					};
					$('floaterDiv').style.width = (GetWindowWidth()-50)+"px";
					$('floaterDiv').style.height = (GetWindowHeight()-209)+"px";
				-->
			</script>
		</td>
	</tr>
</table>