<?
	global $SHARED_HTTP_ROOT;
	// Browserweiche
	if(UserAgentManager::GetCurrentUserAgent()->GetBrowserShortName() == "FF3" ){ // Firefox
		$top = "-1px;";
		$left = "600px;";
	}else if(UserAgentManager::GetCurrentUserAgent()->GetBrowserShortName() == "IE8" ){ // IE8 Normal
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
	if (UserAgentManager::GetCurrentUserAgent()->GetBrowserName()=="FF") echo "<br />";
?>

<div style="height:90%; width:100%; overflow:scroll-y;" id="div_body_main_content">
	<table border="0" cellpadding="0" cellspacing="0" width="1024px;" style="background-color: #ffffff;">
	<?	if($HEADER_PIC != "" && $HEADER_TEXT != ""){?>
			<tr>
				<td colspan="3">
					<table border="0" cellpadding="0" cellspacing="0" width="960px">
						<tr>
							<td align="right" width="40px"><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/<?=$HEADER_PIC?>" alt="<?=$HEADER_TEXT;?>" /></td>
							<td><strong><?=$HEADER_TEXT?></strong></td>
						</tr>
					</table>
				</td>
			</tr>
	<?	}?>
		<tr>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_left.png); background-repeat: no-repeat; height:37px; width:32px;">&#160;</td>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_center.png); background-repeat: repeat-x; height:37px; width:960px;" align="right">
				<img src="<?=$SHARED_HTTP_ROOT?>pics/blind.gif" alt="" width="1px" height="6px" /><br />
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
					<?	if ($this->CanBeAboarded())
						{
							$altText = "Diesen Status abbrechen und zu Status '".WorkflowManager::GetStatusName($this->db, $_SESSION["currentUser"], 9)."' wechseln";?>
							<td valign="bottom" align="center" width="10px"><img src="<?=$SHARED_HTTP_ROOT?>pics/edit/balken_weiss.png" alt="" /></td>
							<td valign="middle" width="25px">
								<a href="javascript:AboardStatus();" style="text-decoration:none" onfocus="if (this.blur) this.blur()">
									<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/cancelEdit.png" alt="<?=$altText;?>" title="<?=$altText;?>" /> 
								</a>
							</td>
							<td>
								<a href="javascript:AboardStatus();" style="text-decoration:none" onfocus="if (this.blur) this.blur()" alt="<?=$altText;?>" title="<?=$altText;?>">
									<strong>Status abbrechen</strong>
								</a>
							</td>
					<?	}?>
					<?	if ($this->ShowBackToLastStatusButton())
						{
							$altText = "Zum vorherigen Status '".$this->GetLastStatusName()."' wechseln";?>
							<td valign="bottom" align="center" width="10px"><img src="<?=$SHARED_HTTP_ROOT?>pics/edit/balken_weiss.png" alt="" /></td>
							<td valign="middle" width="25px">
								<a href="javascript:GotoPreviousStatus();" style="text-decoration:none" onfocus="if (this.blur) this.blur()">
									<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/zurueck.png" alt="<?=$altText;?>" title="<?=$altText;?>" /> 
								</a>
							</td>
							<td>
								<a href="javascript:GotoPreviousStatus();" style="text-decoration:none" onfocus="if (this.blur) this.blur()" alt="<?=$altText;?>" title="<?=$altText;?>">
									<strong>Zum vorherigen Status wechseln</strong>
								</a>
							</td>
					<?	}?>
					<?	if ($this->ShowProtocolButton()){ ?>
							<td valign="bottom" align="center" width="10px"><img src="<?=$SHARED_HTTP_ROOT?>pics/edit/balken_weiss.png" alt="" /></td>
							<td valign="middle" width="25px">
								<a href="javascript:CreateProtocol();" style="text-decoration:none" onfocus="if (this.blur) this.blur()">
									<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/assignTask.png" alt="Protokoll erstellen" title="Protokoll erstellen" /> 
								</a>
							</td>
							<td>
								<a href="javascript:CreateProtocol();" style="text-decoration:none" onfocus="if (this.blur) this.blur()" alt="Protokoll erstellen" title="Protokoll erstellen">
									<strong>Protokoll erstellen</strong>
								</a>
							</td>
					<?	} ?>
					<?	if ($this->ShowCommentButton())
						{
							$commentsAvailable = $this->GetNumComments();?>
							<td valign="bottom" align="center" width="10px"><img src="<?=$SHARED_HTTP_ROOT?>pics/edit/balken_weiss.png" alt="" /></td>
							<td valign="middle" width="25px">
								<a href="javascript:OpenCommentWindow();" style="text-decoration:none" onfocus="if (this.blur) this.blur()">
									<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/comments.png" alt="Kommentare" title="Kommentare" />
								</a>
							</td>
							<td>
								<a href="javascript:OpenCommentWindow();" style="text-decoration:none" onfocus="if (this.blur) this.blur()" alt="<?=$commentsAvailable;?> Kommentar(e)" title="<?=$commentsAvailable;?> Kommentar(e)">
									<strong>Kommentare (<?=$commentsAvailable;?>)</strong>
								</a>
							</td>
					<?	} ?>
					<?	if ($this->ShowStoreButton()){?>
							<td valign="bottom" align="center" width="10px"><img src="<?=$SHARED_HTTP_ROOT?>pics/edit/balken_weiss.png" alt="" /></td>
							<td valign="middle" width="25px">
								<a href="javascript:StoreCurrentStep();" style="text-decoration:none" onfocus="if (this.blur) this.blur()">
									<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/save.png" alt="Eingabe speichern" title="Eingabe speichern" />
								</a>
							</td>
							<td>
								<a href="javascript:StoreCurrentStep();" style="text-decoration:none" onfocus="if (this.blur) this.blur()" alt="Eingabe speichern" title="Eingabe speichern">
									<strong>Eingabe speichern</strong>
								</a>
							</td>
					<?	}?>
					<?	if ($this->ShowCompleteTaskButton()){?>
							<td valign="bottom" align="center" width="10px"><img src="<?=$SHARED_HTTP_ROOT?>pics/edit/balken_weiss.png" alt="" /></td>
							<td valign="middle" width="25px">
								<a href="javascript:GotoNextStep();" style="text-decoration:none" onfocus="if (this.blur) this.blur()">
									<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/finishTask.png" alt="Aufgabe abschließen" title="Aufgabe abschließen" /> 
								</a>
							</td>
							<td>
								<a href="javascript:GotoNextStep();" style="text-decoration:none" onfocus="if (this.blur) this.blur()" alt="Aufgabe abschließen" title="Aufgabe abschließen">
									<strong>Aufgabe abschließen</strong>
								</a>
							</td>
					<?	}?>
						<td valign="bottom" align="center" width="10px"><img src="<?=$SHARED_HTTP_ROOT?>pics/edit/balken_weiss.png" alt="" /></td>
						<td valign="middle" width="25px">
							<a href="<?=$CANCEL_URL;?>" style="text-decoration:none" onfocus="if (this.blur) this.blur()">
								<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/cancelEdit.png" alt="Abbrechen" title="Abbrechen" />
							</a>
						</td>
						<td>
							<a href="<?=$CANCEL_URL;?>" style="text-decoration:none" onfocus="if (this.blur) this.blur()" alt="Abbrechen" title="Abbrechen">
								<strong>Abbrechen</strong>
							</a>
						</td>
					</tr>
				</table>
			</td>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_right.png); background-repeat: no-repeat; height:37px; width:17px;">&#160;</td>
		</tr>
		<tr>
			<td colspan="2">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_left2.png); background-repeat: repeat-y; width:17px;">&#160;</td>
						<td style="vertical-align:top; text-align:left;">
						  <?=$CONTENT?>
					</tr>
				</table>
			</td>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_right2.png); background-repeat: repeat-y; width:17px;">&#160;</td>
		</tr>
		<tr>
			<td colspan="2">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_bottom_left2.png); background-repeat: repeat-y; height:65px; width:17px;">&#160;</td>
						<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_bottom_center2.png); background-repeat: repeat-x; height:65px;">&#160;
					  </td>
					</tr>
				</table>
			</td>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_bottom_right2.png); background-repeat: repeat-y; height:65px; width:17px;">&#160;</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	<!--
	<?	if ($this->ShowProtocolButton()){ ?>
			function CreateProtocol()
			{
				var newWin=window.open('<?=$SHARED_HTTP_ROOT?>de/meineaufgaben/protocol.php5?<?=SID;?>&processId=<?=WorkflowManager::GetProcessStatusId($this->formData->GetFormDataObject());?>', '_createProtocol', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
				newWin.focus();
			}
	<?	}?>
	<?	if ($this->ShowCommentButton()){ ?>
			function OpenCommentWindow()
			{
				var newWin=window.open('<?=$SHARED_HTTP_ROOT?>de/meineaufgaben/comments.php5?<?=SID;?>&processId=<?=WorkflowManager::GetProcessStatusId($this->formData->GetFormDataObject());?>', '_showComments', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
				//newWin.moveTo(width,height);
				newWin.focus();
			}
	<?	}?>
	<?	if ($this->ShowStoreButton()){?>
			function StoreCurrentStep()
			{
				document.<?=$FORMULARNAME?>.sendData.value=true; 
				document.<?=$FORMULARNAME?>.submit();
			}
	<?	}?>
	<?	if ($this->ShowCompleteTaskButton()){?>
			function GotoNextStep()
			{
				document.<?=$FORMULARNAME?>.sendData.value=true; 
				document.<?=$FORMULARNAME?>.nextStep.value='true'; 
				document.<?=$FORMULARNAME?>.submit();
			}
	<?	}?>
	<?	if ($this->ShowBackToLastStatusButton()){?>
			function GotoPreviousStatus()
			{
				document.<?=$FORMULARNAME?>.sendData.value=true; 
				document.<?=$FORMULARNAME?>.previousStep.value='true'; 
				document.<?=$FORMULARNAME?>.submit();
			}
	<?	}?>
	<?	if ($this->CanBeAboarded()){?>
			function AboardStatus()
			{
				document.<?=$FORMULARNAME?>.sendData.value=true; 
				document.<?=$FORMULARNAME?>.aboardStep.value='true'; 
				document.<?=$FORMULARNAME?>.submit();
			}
	<?	}?>	
	
	
	-->
</script>