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
class TaskTabDataEntry extends TabDataEntry 
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
	public function TaskTabDataEntry(DBManager $db, ProcessStatus $processStatus)
	{
		$this->db = $db;
		$this->processStatus = $processStatus;
		parent::__construct(ProcessTabData::TAB_TASK, "Aktuelle Aufgabe");
	}
	
	/**
	 * Output the HTML for this tabs
	 * @return bool
	 */
	public function PrintContent()
	{
		global $DOMAIN_HTTP_ROOT, $lm, $UID;
		$statusName = "-";
		$year = "-";
		$locationType = "-";
		$fmsId = "-";
		$customerId = "-";
		$isCurrentUserFmsLeader = false;
		$comment = "";
		$cLocation = null;
		$pid = "-";
		if ($this->processStatus!=null && is_object($this->processStatus))
		{
			$pid = WorkflowManager::GetProcessStatusId($this->processStatus);

			$fmsLeader = $this->processStatus->GetCPersonFmsLeader();
			if ($fmsLeader!=null && $fmsLeader->GetPKey()==$_SESSION["currentUser"]->GetPKey())
			{
				$isCurrentUserFmsLeader = true;
			}

			if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_TAB_CURRENTTASK))
			{
				if ($_POST['storeTapData']=="true")
				{

					$this->processStatus->SetForm((int)$_POST['tap_ta_form']);
					$this->processStatus->SetStagesPointsToBeClarified((int)$_POST['tap_ta_stages_points_to_be_clarified']);
					$this->processStatus->SetReferenceNumber($_POST['tap_ta_reference_number']);
					$this->processStatus->SetSubProcedure($_POST['tap_ta_sub_procedure']);


					$abschlussdatum = DateElement::GetTimeStamp($_POST['tap_ta_abschlussdatum']);
					if ($abschlussdatum!==false)
					{
						$this->processStatus->SetAbschlussdatum($abschlussdatum);
					}
					elseif(trim($_POST['tap_ta_abschlussdatum'])=="")
					{
						$this->processStatus->SetAbschlussdatum(0);
					}
					$deadline = DateElement::GetTimeStamp($_POST['tap_ta_deadline']);
					if ($deadline!==false)
					{
						$this->processStatus->SetDeadline($deadline);
					}
					elseif(trim($_POST['tap_ta_deadline'])=="")
					{
						// Nicht zulässig für Deadline
					}
					$this->processStatus->SetScheduleComment($_POST['status_comment']);
					
					// Prio function and prio
					$this->processStatus->SetPrioFunction($_POST['prio']=='0' ? Schedule::PRIO_FUNCTION_AUTO : Schedule::PRIO_FUNCTION_MANUEL);
					if ($_POST['prio']!='0')
					{
						$this->processStatus->SetPrio(($_POST['prio']=='2' ? Schedule::PRIO_HIGH : Schedule::PRIO_NORMAL));
					}
					$this->processStatus->Store($this->db);
				}
				$autoPrio = $this->processStatus->GetAutoPrio($this->db);
				$prio = 0;
				$prioFunction = $this->processStatus->GetPrioFunction();
				if ($prioFunction==Schedule::PRIO_FUNCTION_MANUEL) $prio = ($this->processStatus->GetPrio($this->db)==Schedule::PRIO_HIGH ? 2 : 1);
			}
			
			$shop = $this->processStatus->GetShop();
			if ($shop!=null)
			{
				$fmsId = $shop->GetRSID();
				$customerId = $shop->GetInternalShopNo();
				$fmsLeader = $shop->GetCPersonFmsLeader();
			}
			$statusName = $this->processStatus->GetCurrentStatusName($_SESSION["currentUser"], $this->db);
			$comment = $this->processStatus->GetScheduleComment();
			$abrechnungsjahr = $this->processStatus->GetAbrechnungsJahr();
			if ($abrechnungsjahr!=null)
			{
				$year = $abrechnungsjahr->GetJahr();
				$contract = $abrechnungsjahr->GetContract();
				if ($contract!=null)
				{
					$cShop = $contract->GetShop();
					if ($cShop!=null)
					{
						$cLocation = $cShop->GetLocation();
						if ($cLocation!=null)
						{
							$locationType = GetLocationName($cLocation->GetLocationType());
						}
					}
				}
			}

			$form = $this->processStatus->GetForm();
			$stagesPointsToBeClarified = $this->processStatus->GetStagesPointsToBeClarified();
			$referenceNumber = $this->processStatus->GetReferenceNumber();
			$subProcedure = $this->processStatus->GetSubProcedure();
			$procedure = $this->processStatus->GetProcedure($this->db);
			$procedureType = $this->processStatus->GetProcedureType($this->db);

			$abschlussdatum = $this->processStatus->GetAbschlussdatum();
			if ($abschlussdatum==0) $abschlussdatum="";
			else $abschlussdatum = date("d.m.Y", $abschlussdatum);

			$deadline = $this->processStatus->GetDeadline();
			if ($deadline==0) $deadline="";
			else $deadline = date("d.m.Y", $deadline);
		}
		?>
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
							<td valign="top" width="30%" align="right" class="contentText"><strong><?=WorkflowStatus::GetAttributeName($lm, 'currentStatus');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=$statusName;?></td>
						</tr>
						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong><?=CShop::GetAttributeName($lm, 'RSID');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=$fmsId;?></td>
						</tr>
						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong><?=ProcessStatus::GetAttributeName($lm, 'form');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText">
								<?	if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_TAB_CURRENTTASK)){ ?>
									<select name="tap_ta_form" id="tap_ta_form" style="width: 104px;">
									<? 	$availableForms = ProcessStatus::GetAvailableForms();
										foreach($availableForms as $availableForm){?>
											<option value="<?=$availableForm?>" <?if($form==$availableForm) echo "selected";?>><?=$lm->GetString("PROCESS", "FORM_".$availableForm)?></option>
									<?	}?>
									</select>
									<input type="button" onclick="$('storeTapData').value='true'; document.forms.FM_FORM.submit();" class="formButton" style="width: 25px; height: 20px;" value="OK" />
								<?}else{?>
									<?=$lm->GetString("PROCESS", "FORM_".$form);?>
								<?}?>
							</td>
						</tr>
						<tr>
							<td valign="top" width="40%" align="right" class="contentText"><strong><?=ProcessStatus::GetAttributeName($lm, 'referenceNumber');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText">
								<?	if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_TAB_CURRENTTASK)){ ?>
									<input type="text" class="TextForm" name="tap_ta_reference_number" id="tap_ta_reference_number" value="<?=$referenceNumber;?>" maxlength="10" style="width: 100px;" onkeydown="if(window.event.keyCode==13){$('storeTapData').value='true'; document.forms.FM_FORM.submit();}" />
									<input type="button" onclick="$('storeTapData').value='true'; document.forms.FM_FORM.submit();" class="formButton" style="width: 25px; height: 20px;" value="OK" />
								<?}else{?>
									<?=$referenceNumber;?>
								<?}?>
							</td>
						</tr>
					</table>
				</td>
				<td width="29">&#160;</td>
				<td width="287" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong><?=CLocation::GetAttributeName($lm, 'locationType');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=$locationType;?></td>
						</tr>
						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong><?=CShop::GetAttributeName($lm, 'internalShopNo');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=$customerId;?></td>
						</tr>
						<tr>
							<td valign="top" width="40%" align="right" class="contentText"><strong><?=ProcessStatus::GetAttributeName($lm, 'abschlussdatum');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText">
								<?	if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_TAB_CURRENTTASK)){ ?>
									<input type="text" class="TextForm" name="tap_ta_abschlussdatum" id="tap_ta_abschlussdatum" value="<?=$abschlussdatum;?>" maxlength="10" style="width: 60px;" onkeydown="if(window.event.keyCode==13){$('storeTapData').value='true'; document.forms.FM_FORM.submit();}" />
									<input type="button" onclick="$('storeTapData').value='true'; document.forms.FM_FORM.submit();" class="formButton" style="width: 25px; height: 20px;" value="OK" />
								<?	}else{?>
									<?=($abschlussdatum!="" ? $abschlussdatum : "-");?>
								<?	}?>
							</td>
						</tr>
						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong><?=ProcessStatus::GetAttributeName($lm, 'stagesPointsToBeClarified');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText">
								<?	if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_TAB_CURRENTTASK)){ ?>
									<select name="tap_ta_stages_points_to_be_clarified" id="tap_ta_stages_points_to_be_clarified" style="width: 130px;">
										<? 	$availableStagesPointsToBeClarified = ProcessStatus::GetAvailableStagesPointsToBeClarified();
										foreach($availableStagesPointsToBeClarified as $availableStagesPointToBeClarified){?>
											<option value="<?=$availableStagesPointToBeClarified?>" <?if($stagesPointsToBeClarified==$availableStagesPointToBeClarified) echo "selected";?>><?=$lm->GetString("PROCESS", "STAGE_POINTS_TO_BE_CLARIFIED_".$availableStagesPointToBeClarified)?></option>
										<?	}?>
									</select>
									<input type="button" onclick="$('storeTapData').value='true'; document.forms.FM_FORM.submit();" class="formButton" style="width: 25px; height: 20px;" value="OK" />
								<?}else{?>
									<?=$lm->GetString("PROCESS", "STAGE_POINTS_TO_BE_CLARIFIED_".$stagesPointsToBeClarified);?>
								<?}?>
							</td>
						</tr>
						<?	if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_TAB_CURRENTTASK)){ ?>
							<tr>
								<td valign="top" width="30%" align="right" class="contentText"><strong><?=Schedule::GetAttributeName($lm, 'prio');?></strong></td>
								<td valign="top" width="10px" align="right">&#160;</td>
								<td valign="top" align="left" class="contentText">
									<input type="radio" name="prio" value="0" <?if($prio==0)echo "checked";?> onclick="$('storeTapData').value='true'; document.forms.FM_FORM.submit();" /> Auto (<?=($autoPrio==Schedule::PRIO_HIGH ? "Ja" : "Nein");?>)
									<input type="radio" name="prio" value="2" <?if($prio==2)echo "checked";?> onclick="$('storeTapData').value='true'; document.forms.FM_FORM.submit();" /> Ja
									<input type="radio" name="prio" value="1" <?if($prio==1)echo "checked";?> onclick="$('storeTapData').value='true'; document.forms.FM_FORM.submit();" /> Nein
								</td>
							</tr>
						<?}?>
						
					</table>
				</td>
				<td width="29">&#160;</td>
				<td width="287" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong><?=ProcessStatus::GetAttributeName($lm, 'processStatusId');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=$pid;?></td>
						</tr>
						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong><?=ProcessStatus::GetAttributeName($lm, 'procedure');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=$procedure;?></td>
						</tr>
						<tr>
							<td valign="top" width="40%" align="right" class="contentText"><strong><?=ProcessStatus::GetAttributeName($lm, 'subProcedure');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText">
								<?	if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_TAB_CURRENTTASK)){ ?>
									<input type="text" class="TextForm" name="tap_ta_sub_procedure" id="tap_ta_sub_procedure" value="<?=$subProcedure;?>" style="width: 60px;" onkeydown="if(window.event.keyCode==13){$('storeTapData').value='true'; document.forms.FM_FORM.submit();}" />
									<input type="button" onclick="$('storeTapData').value='true'; document.forms.FM_FORM.submit();" class="formButton" style="width: 25px; height: 20px;" value="OK" />
								<?	}else{?>
										<?=$subProcedure;?>
								<?	}?>
							</td>
						</tr>
						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong><?=ProcessStatus::GetAttributeName($lm, 'procedureType');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=$procedureType;?></td>
						</tr>

						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong><?=WorkflowStatus::GetAttributeName($lm, 'deadline');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText">
							<?	if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_TAB_CURRENTTASK)){ ?>
									<input type="text" class="TextForm" name="tap_ta_deadline" id="tap_ta_deadline" value="<?=$deadline;?>" maxlength="10" style="width: 60px;" onkeydown="if(window.event.keyCode==13){$('storeTapData').value='true'; document.forms.FM_FORM.submit();}" />
									<input type="button" onclick="$('storeTapData').value='true'; document.forms.FM_FORM.submit();" class="formButton" style="width: 25px; height: 20px;" value="OK" />
							<?	}else{?>
									<?=($deadline!="" ? $deadline : "-");?>
							<?	}?>
							</td>
						</tr>
						<?	if ($cLocation!=null && UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_TAB_CURRENTTASK)){ ?>
							<tr>
								<td valign="top" width="30%" align="right" class="contentText"><strong>Bericht</strong></td>
								<td valign="top" width="10px" align="right">&#160;</td>
								<td valign="top" align="left" class="contentText"><a href="<?=$DOMAIN_HTTP_ROOT;?>de/berichte/kundenstandorte.php5?<?=SID?>&location=<?=$cLocation->GetPKey();?>" target="_blank">anzeigen</a></td>
							</tr>
					<?	}?>
					</table>
				</td>
				<td width="29">&#160;</td>
			</tr>
			<tr>
				<td width="29">&#160;</td>
				<td colspan="5">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td valign="top" width="117px" align="right" class="contentText"><strong><?=Schedule::GetAttributeName($lm, 'scheduleComment');?></strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="middle" align="left" class="contentText">
							<?	if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_TAB_CURRENTTASK)){ ?>
								<input type="hidden" name="storeTapData" id="storeTapData" value="" />
								<input type="text" class="TextForm" style="width: 700px;" name="status_comment" id="status_comment" value="<?=str_replace("\"", "'", $comment);?>" maxlength="255" onkeydown="if(window.event.keyCode==13){$('storeTapData').value='true'; document.forms.FM_FORM.submit(); return;}; commentAutocompleter.onCommand(window.event);" />
                                <input type="button" onclick="$('storeTapData').value='true'; document.forms.FM_FORM.submit();" class="formButton" style="width: 25px; height: 22px;" value="OK" />
                                <script type="text/javascript">
                                    var commentAutocompleter = new Autocompleter.Ajax.Json($('status_comment'), '<?=$DOMAIN_HTTP_ROOT;?>phplib/jsInterface.php5', {
                                        minLength: 0,
                                        maxChoices: 25,
                                        postVar : 'query',
                                        //forceSelect: true,
                                        autoTrim: false,
                                        postData : {
                                            'reqDataType' : '14',
                                            'UID' : '<?=$UID;?>'
                                        }
                                    });
                                </script>
							<?}else{?>
								<?=$comment;?>
							<?}?>
							</td>
						</tr>
					</table>
				</td>
				<td width="29">&#160;</td>
			</tr>
		</table>
		<br />
		<?
	}
	
}
?>