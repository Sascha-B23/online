<?php
	global $SHARED_HTTP_ROOT;
	/* @var $this SyncManager */
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
								<td><strong>Datensätze</strong></td>
							</tr>
						</table>
					</td>
					<td align="right">
						<table border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td><div style="width:15px; height:15px; background-color:#ffffff; border:1px solid #CBCBCB; border-radius:5px;"></div></td>
								<td width="5"></td>
								<td>Keine Änderungen</td>
								<td width="21"></td>
                                <td><div style="width:15px; height:15px; background-color:#EFEFEF; border:1px solid #CBCBCB;"></div></td>
                                <td width="5"></td>
                                <td>Wird ignoriert</td>
                                <td width="21"></td>
								<td><div style="width:15px; height:15px; background-color:#D0E8F2; border:1px solid #CBCBCB;"></div></td>
								<td width="5"></td>
								<td>Neu hinzufügen</td>
								<td width="21"></td>
								<td><div style="width:15px; height:15px; background-color:#75BC18; border:1px solid #CBCBCB;"></div></td>
								<td width="5"></td>
								<td>Änderung</td>
								<td width="21"></td>
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
						$checkboxen = Array();
						for ($i=0; $i<count($this->csvDataRows); $i++)
						{
							$dataRow = $this->csvDataRows[$i];?>
							<tr>
								<td valign="top" height="30" style="border-right:1px solid #CACACA; border-bottom:2px solid #CACACA;"><?=$dataRow->GetCsvLineNumber();?></td>
							<?	for ($a=0; $a<$this->GetColumnCount(); $a++)
								{
									$column = $this->GetColumnByIndex($a);
									if ($column==null) continue;
									// get the data cell
									$dataCell = $dataRow->GetDataCellForColumn($column->GetId());
									// select color to highlight cell
									switch($dataCell->GetValidationState())
									{
										case SyncDataCell::STATE_CANNOT_BE_CHANGED:
										case SyncDataCell::STATE_HAS_TO_EXIST_IN_DB:
										case SyncDataCell::STATE_ISREQUIRED:
										case SyncDataCell::STATE_INVALID_VALUE:
											$statuscolor = "#DA8A01;";
											break;

										case SyncDataCell::STATE_CHANGED:
											$statuscolor = "#75BC18;"; 
											break;

										case SyncDataCell::STATE_NEW:
											$statuscolor = "#D0E8F2;";
											break;
                                        case SyncDataCell::STATE_IGNORE:
                                            $statuscolor = "#EFEFEF;";
                                            break;
										case SyncDataCell::STATE_NOCHANGE:
										default:
											$statuscolor = "#FFFFFF;";
									}
									?>
									<td height="30" valign="top" style="background-color:<?=$statuscolor?> border-right:1px solid #CACACA; border-bottom:2px solid #CACACA;"><?=($dataCell->GetValidationState()!=SyncDataCell::STATE_IGNORE ? $dataCell->GetDisplayValue() : '');?>&#160;</td>
							<?	}?>
								<td height="30" align="center" style="border-right:1px solid #CACACA; border-bottom:2px solid #CACACA;">
								<?	if ($dataRow->IsValid() && $dataRow->HasChanged())
									{
										?><input type="checkbox" name="checkbox_<?=$i?>" id="checkbox_<?=$i?>" /><?
										$checkboxen[] = "checkbox_".$i;												
										$_SESSION["importDataRows"][get_class($this)][] = Array('data' => $dataRow, 'checkboxName' => $checkboxen[count($checkboxen)-1]);
									}
									else
									{
										if ($dataRow->IsValid() && !$dataRow->HasChanged()){
											?><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/check.gif" width="18" height="18" alt="Datensatz ist auf dem aktuellen Stand" title="Datensatz ist auf dem aktuellen Stand" /><?
										} else {
											?><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/warning.png" alt="<?=$dataRow->GetValidationErrorMessage()?>" title="<?=$dataRow->GetValidationErrorMessage()?>" /><?
										}
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
					}
					$('floaterDiv').style.width = (GetWindowWidth()-50)+"px";
					$('floaterDiv').style.height = (GetWindowHeight()-209)+"px";
				-->
			</script>
		</td>
	</tr>
	<tr>
		<td valign="top" style="height:100px;">
			<table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
				<tr>
					<td colspan="4" style="height:8px;"></td>
				</tr>
				<tr>
					<td colspan="3">
						<script type="text/javascript">
							<!--
								var checkboxlist = new Array();
							<?	foreach ($checkboxen as $checkboxName){?>
									checkboxlist.push('<?=$checkboxName;?>');
							<?	}?>

								/**
								 * check if all checkboxes are unchecked
								 * @returns {Boolean}
								 */
								function all_checkboxes_unchecked()
								{
									for (var i=0; i<checkboxlist.length; i++)
									{
										var checkboxObj = document.getElementById(checkboxlist[i]);
										if (checkboxObj!=undefined && typeof(checkboxObj)!=undefined)
										{
											if (checkboxObj.checked == true)
											{
												// at least one checkbox is checked
												return false;
											}
										}
									}
									// all checkboxes are unchecked
									return true;
								}

								/**
								 * check/uncheck all checkboxes
								 * @param {Boolean} value
								 */
								function set_all_checkboxes(value)
								{
									for (var i=0; i<checkboxlist.length; i++)
									{
										var checkboxObj = document.getElementById(checkboxlist[i]);
										if (checkboxObj!=undefined && typeof(checkboxObj)!=undefined)
										{
											checkboxObj.checked = value;
										}
									}
								}

								/**
								 * toggle all checkboxes
								 */
								function toggle_checkboxes()
								{
									set_all_checkboxes(all_checkboxes_unchecked());
								}

								/**
								 * start import
								 * @returns {undefined}
								 */
								function do_import()
								{
									if (all_checkboxes_unchecked())
									{
										alert("Bitte erst einen Datensatz auswählen");
									}
									else
									{
										document.forms.table_import.submit();
									}
								}
							-->
						</script>
						<img src="<?=$SHARED_HTTP_ROOT?>pics/kundensynchronisation/alle_markieren.png" align="right" onclick="toggle_checkboxes();" alt="" style="cursor:pointer;cursor:hand;" />
					</td>
					<td style="width:17px;"></td>
				</tr>
				<tr>
					<td colspan="4" style="height:12px;"></td>
				</tr>
				<tr>
					<td>
						<img src="<?=$SHARED_HTTP_ROOT?>pics/kundensynchronisation/abbrechen.png" onclick="window.close();" align="right" alt="" style="cursor:pointer;cursor:hand;" />
					</td>
					<td style="width:5px;"></td>
					<td style="width:75px;">
						<img src="<?=$SHARED_HTTP_ROOT?>pics/kundensynchronisation/importieren.png" align="right" onclick="do_import()" alt="" style="cursor:pointer;cursor:hand;" />
					</td>
					<td style="width:17px;"></td>
				</tr>				
			</table>
		</td>
	</tr>
</table>