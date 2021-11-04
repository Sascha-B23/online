<?
	global $SHARED_HTTP_ROOT;
	// Browserweiche
	if( UserAgentManager::GetCurrentUserAgent()->GetBrowserShortName() == "FF3" ){ // Firefox
		$top = "-1px;";
		$left = "600px;";
	}else if( UserAgentManager::GetCurrentUserAgent()->GetBrowserShortName() == "IE8" ){ // IE8 Normal
		$top = "-1px;";
		$left = "600px;";
	}else if(UserAgentManager::GetCurrentUserAgent()->GetBrowserShortName() == "IE7" ){ // IE8 Kompatibilitätsmodus oder IE7
		$top = "-25px;";
	}else if(UserAgentManager::GetCurrentUserAgent()->GetBrowserShortName() == "OP9" ){ // Opera 9 + 10
		$top = "-27px;";
	}else if(UserAgentManager::GetCurrentUserAgent()->GetBrowserShortName() == "CR" ){ // Google Chrome
		$top = "-1px;";
	}else if(UserAgentManager::GetCurrentUserAgent()->GetBrowserShortName() == "SA" ){ // Safari
		$top = "-1px;";
	}else if(UserAgentManager::GetCurrentUserAgent()->GetBrowserShortName() == "NS6" ){ // Safari
		$top = "-27px;";
	}else{
		$top = "-1px;";
	}
	if (!isset($CANCEL_TEXT)) $CANCEL_TEXT = "Abbrechen";
?>
<div style="height:90%; width:100%;" id="div_body_main_content" >
	<table border="0" bgcolor="#ffffff" cellpadding="0" cellspacing="0" width="1024px;">
		<tr>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_left.png); background-repeat: no-repeat; height:37px; width:32px;">&#160;</td>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_center.png); background-repeat: repeat-x; height:37px;">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td>
							<?	// HEADER PIC UND HEADER TEXT
							if($HEADER_PIC != "" && $HEADER_TEXT != ""){?>
								<span style="position:relative; left:-20px;"><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/<?=$HEADER_PIC?>" alt="" style="position:relative; top:8px;" /> <strong><?=$HEADER_TEXT?></strong> </span>
							<?}else{?>
								&#160;
							<?}?>
						</td>
						<td style="width: 500px; text-align:right;">
						<?	if( isset($SHOW_BACK_ICON) && $SHOW_BACK_ICON!==false ){ ?>
								&#160;<img src="<?=$SHARED_HTTP_ROOT?>pics/edit/balken_weiss.png" alt="" style="position:relative; top:8px;" />&#160;
								<a href="<?=$SHOW_BACK_ICON;?>" style="text-decoration:none" onfocus="if (this.blur) this.blur()"><? // SAVE FUNKTION ?>
									<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/back.png" alt="Vorheriger Datensatz" style="position:relative; top:8px;" /> Vorheriger Datensatz
								</a>
						<?	}?>
						<?	if( isset($SHOW_NEXT_ICON) && $SHOW_NEXT_ICON!==false ){ ?>
								&#160;<img src="<?=$SHARED_HTTP_ROOT?>pics/edit/balken_weiss.png" alt="" style="position:relative; top:8px;" />&#160;
								<a href="<?=$SHOW_NEXT_ICON;?>" style="text-decoration:none" onfocus="if (this.blur) this.blur()"><? // SAVE FUNKTION ?>
									<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/next.png" alt="Nächster Datensatz" style="position:relative; top:8px;" /> Nächster Datensatz
								</a>
						<?	}?>
						<?	if( !isset($SHOW_SAVE_ICON) || $SHOW_SAVE_ICON==true ){ ?>
								&#160;<img src="<?=$SHARED_HTTP_ROOT?>pics/edit/balken_weiss.png" alt="" style="position:relative; top:8px;" />&#160;
								<a href="javascript:StoreData();" style="text-decoration:none" onfocus="if (this.blur) this.blur()"><? // SAVE FUNKTION ?>
									<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/save.png" alt="Speichern" style="position:relative; top:8px;" /> Speichern
								</a>
						<?	}?>
						<?	if( !isset($SHOW_CANCEL_ICON) || $SHOW_CANCEL_ICON==true ){ ?>
								&#160;<img src="<?=$SHARED_HTTP_ROOT?>pics/edit/balken_weiss.png" alt="" style="position:relative; top:8px;" />&#160;
								<a href="<?=$CANCEL_URL;?>" style="text-decoration:none" onfocus="if (this.blur) this.blur()"><? // BACK FUNKTION ?>
									<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/cancelEdit.png" alt="Abbrechen" style="position:relative; top:8px;" /> <?=$CANCEL_TEXT;?>
								</a>
						<?	}?>	
						</td>
					</tr>
				</table>
			</td>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_right.png); background-repeat: no-repeat; height:37px; width:17px;">&#160;</td>
		</tr>
		<tr>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_left.png); background-repeat: repeat-y; width:32px;">&#160;</td>
			<td style="height:574px; vertical-align:top; text-align:left;">
				<br />
				<span style="position:relative; top:15px; left:15px; background-color:#ffffff;">
					<?=$CONTENT?>
				</span>
				<br />
			</td>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_right.png); background-repeat: repeat-y; width:17x;">&#160;</td>
		</tr>
		<tr>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_bottom_left.png); background-repeat: no-repeat; height:63px; width:32px;">&#160;</td>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_bottom_center.png); background-repeat: repeat-x; height:63px;">&#160;</td>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_bottom_right.png); background-repeat: no-repeat; height:63px; width:17px;">&#160;</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	<!--
		function StoreData()
		{
		<?	if($FORMULARCHECK == true){?>
				if(chkFormular() != false)
				{
					document.<?=$FORMULARNAME?>.sendData.value=true; 
					document.<?=$FORMULARNAME?>.submit();
				}
		<?	}else{?>
				document.<?=$FORMULARNAME?>.sendData.value=true; 
				document.<?=$FORMULARNAME?>.submit();
		<?	}?>
		}
	-->
</script>