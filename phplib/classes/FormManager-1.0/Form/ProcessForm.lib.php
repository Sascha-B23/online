<?php
/**
 * Form-Klasse für die Prozess-Ansicht
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class ProcessForm extends Form
{
	/**
	 * DBManager
	 * @var DBManager 
	 */
	protected $db = null;
	/**
	 * TabManager
	 * @var TabManager 
	 */
	protected $tabManager = null;
	
	/**
	 * The current process status
	 * @var ProcessStatus 
	 */
	protected $processStatus = null;
	
	/**
	 * The current process status
	 * @var ProcessStatus 
	 */
	protected $subProcessStatus = null;
	
	/**
	 * Constructor
	 * @param string $jumpBackURL
	 * @param array $formData
	 * @param ProcessTabData $tabData
	 * @param ProcessStatus $processStatus
	 */
	public function ProcessForm(DBManager $db, ProcessStatus $processStatus, $jumpBackURL)
	{
		$this->defaultSendData = false;
		$this->db = $db;
		$this->subProcessStatus = $this->processStatus = $processStatus;

		if (is_a($this->processStatus, 'ProcessStatusGroup'))
		{
			if (isset($_POST['sub_process']))
			{
				$processList = $this->processStatus->GetProcess();
				foreach ($processList as $process)
				{
					if ($_POST['sub_process'] == WorkflowManager::GetProcessStatusId($process))
					{
						$this->subProcessStatus = $process;
						$_POST["editElement"] = WorkflowManager::GetProcessStatusId($this->subProcessStatus);
						break;
					}
				}
			}
			else
			{
				// preselect ProcessStatus if set
				$this->subProcessStatus = $this->processStatus->GetSelectedProcessStatus();
				if ($this->subProcessStatus!=null)
				{
					$_POST["editElement"] = WorkflowManager::GetProcessStatusId($this->subProcessStatus);
				}
				else
				{
					$this->subProcessStatus = $this->processStatus;
				}
			}
		}
		// when switching to another subprocess we have to reload the data from the new process and drop the POST-Vars
		$formVars = ($_POST["change_sub_process"]=='true' ? ProcessFormData::FilterVars($_POST) : $_POST);
		$formData = new ProcessFormData($formVars, $this->subProcessStatus, $this->processStatus, $db);
		parent::__construct($jumpBackURL, $formData, "template_process.php5", true);
		
		$tabData = new ProcessTabData($db, $this->subProcessStatus);
		$this->tabManager = new TabManager($tabData);
		
	}
	
	/**
	 * Output the path of the current prozess (Gruppe | Firma | Standort | Laden | Jahr)
	 */
	protected function PrintPath()
	{
		$pathList = $this->processStatus!=null ? $this->processStatus->GetProzessPath() : "";
		if (count($pathList)==1)
		{
			?><font style='color: #666666;'>&#160;&#160;<?=$pathList[0]['path'];?></font><br /><?
		}
		elseif (count($pathList)>1)
		{
		?>	<script>
				<!--
				function set_sub_process(select)
				{
					if (confirm('Achtung: Nicht gespeicherte Änderungen gehen bei diesem Vorgang verloren!'))
					{
						$('change_sub_process').value = 'true';
						$('FM_FORM').submit();
					}
					else
					{
						select.form.reset();
					}
				}
				-->
			</script>
			<input type="hidden" name="change_sub_process" id="change_sub_process" value="">
			<select name='sub_process' onchange='set_sub_process(this);'><?
				foreach ($pathList as $path) 
				{
					?><option value='<?=$path['process_id'];?>' <?if (WorkflowManager::GetProcessStatusId($this->subProcessStatus)==$path['process_id'])echo 'selected';?>><?=$path['path'];?></option><?
				}
		?>	</select><?
		}
	}
	
	/**
	 * Return if the button "Zum vorherigen Status wechseln" should been shown
	 * @return boolean
	 */
	protected function ShowBackToLastStatusButton()
	{
		// is a previews status set
		if ($this->formData->GetPreviousStatus()==-1) return false;
		// only FMS employees and admins can jump back to previews status
		if (!UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_JUMP_BACK_TO_LAST_STATUS)) return false;
		// in a group only the group itself can be set to previews status
		if (is_a($this->processStatus, 'ProcessStatusGroup') && !is_a($this->subProcessStatus, 'ProcessStatusGroup') && $this->processStatus->GetSelectedProcessStatus()==null) return false;
		return true;
	}
	
	/**
	 * Return the name of the last status
	 * @return string
	 */
	protected function GetLastStatusName()
	{
		return WorkflowManager::GetStatusName($this->db, $_SESSION["currentUser"], $this->formData->GetPreviousStatus());
	}
	
	/**
	 * Return if the button "Protokoll erstellen" should been shown
	 * @return boolean
	 */
	protected function ShowProtocolButton()
	{
		if (!UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_PROTOCOL)) return false;
		return true;
	}
		
	/**
	 * Return if the button "Kommentare" should been shown
	 * @return boolean
	 */
	protected function ShowCommentButton()
	{
		if ($this->formData->GetFormDataObjectID()==-1) return false;
		if (!UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_COMMENT)) return false;
		return true;
	}
	
	/**
	 * Return if the Status can be aboarded by user
	 * @return boolean
	 */
	protected function CanBeAboarded()
	{
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP) return false;
		return $this->formData->CanBeAboarded();
	}
	
	/**
	 * Get the number of comments in the active process
	 * @return int
	 */
	protected function GetNumComments()
	{
		if ($this->subProcessStatus==null) return 0;
		return $this->subProcessStatus->GetCommentCount($this->db, $_SESSION["currentUser"]);
	}
	
	/**
	 * Return if the button "Eingabe speichern" should been shown
	 * @return boolean
	 */
	protected function ShowStoreButton()
	{
		if (!$this->IsFormEditable()) return false;
		return true;
	}
	
	/**
	 * Return if the button "Aufgabe abschließen" should been shown
	 * @return boolean
	 */
	protected function ShowCompleteTaskButton()
	{
		// if no store button is available the "complete task" button isn't visible too
		if (!$this->ShowStoreButton()) return false;
		if (is_a($this->processStatus, 'ProcessStatusGroup') && !$this->formData->CanBeCompletetdInGroup()) return false;
		return true;
	}
	
	/**
	 * Return if the current Form is editable
	 * @return boolean
	 */
	protected function IsFormEditable()
	{
		if (is_a($this->processStatus, 'ProcessStatusGroup') && !$this->formData->IsEditableInGroup()) return false;
		return true;
	}
	
	/**
	 * PrintData()
	 * Ausgabe der Daten in HTML mit Design
	 */
	protected function PrintContent()
	{
		// Tab ausgeben
		global $DOMAIN_HTTP_ROOT;
		?>
		<input type="hidden" name="sendData" id="sendData" value="<?=($this->defaultSendData ? "true" : "false");?>">
		<input type="hidden" name="nextStep" id="nextStep" value="false">
		<input type="hidden" name="previousStep" id="previousStep" value="false">
		<input type="hidden" name="aboardStep" id="aboardStep" value="false">
		<input type="hidden" name="currentStep" id="currentStep" value="<?=$this->formData->GetCurrentStatus();?>">
		<?
		$this->PrintPath();
		if( !$this->tabManager->PrintData() )return;?>
		<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
			<tr height="28px">
				<td style="background-image:url('<?=$DOMAIN_HTTP_ROOT?>pics/reiter/hg_datenerfassung.png'); background-repeat: repeat-x; height: 28px;">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td width="29">&#160;</td>
							<td><strong>Datenerfassung</strong></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td style="background-color:#f5f5f5;" valign="top">
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
					<?	if (!$this->IsFormEditable()){ ?>
							<tr>
								<td>&#160;</td>
								<td colspan="5" align="center"><font class="errorText">Eine Datenerfassung für diesen Status ist nur auf <?=(is_a($this->subProcessStatus, 'ProcessStatusGroup') ? "Prozessebene" : "Paketebene");?> möglich</font></td>
								<td>&#160;</td>
							</tr>
							<tr>
								<td colspan="7">&#160;</td>
							</tr>
					<?	}else{?>
						<?	// Allgemeine Fehlerausgabe
							$errors = $this->formData->GetErrors();
							if( count($errors)>0 ){ ?>
								<tr>
									<td>&#160;</td>
									<td colspan="5" align="center"><font class="errorText">Es sind Fehler aufgetreten. Bitte überprüfen Sie Ihre Eingaben.</font></td>
									<td>&#160;</td>
								</tr>
								<tr>
									<td colspan="7">&#160;</td>
								</tr>
							<?	if( is_array($errors["misc"]) && count($errors["misc"])>0 ){ 
									for($a=0; $a<count($errors["misc"]); $a++){ ?>
										<tr>
											<td>&#160;</td>
											<td colspan="5" align="center"><font class="errorText"><?=$errors["misc"][$a];?></font></td>
											<td>&#160;</td>
										</tr>
								<?	}?>
									<tr>
										<td colspan="7">&#160;</td>
									</tr>							
							<?	}?>
						<?	}?>					
						<?	// Eingabeelemente
							$elements=$this->formData->GetElements();
							$numLines=ceil(count($elements)/3);
							for($a=0; $a<$numLines; $a++){ 
								$elem1=$elements[$a*3+0];
								$elem2=$elements[$a*3+1];
								$elem3=$elements[$a*3+2];
								if( $elem1!="" && $elem1->FullWidthNeeded() ){?>
									<tr>
										<td width="29">&#160;</td>
										<td width="919" valign="top" colspan="5">
											<table width="919" border="0" cellpadding="0" cellspacing="0">
												<tr>
													<td>
														<strong><?=$elem1->GetName();?><?if($elem1->IsRequired())echo "*";?></strong><br>
														<?=$elem1->PrintElement();?><br/>
														<? if($elem1->HasError()){ ?>
															<font class="errorText"><?=$elem1->GetError();?>&#160;</font><br><br>
														<? }?>
													</td>
												</tr>
											</table>
										</td>
										<td width="29">&#160;</td>
									</tr>
							<?	}else{?>
									<tr>
										<td width="29">&#160;</td>
										<td width="287" valign="top">
										<? 	if( $elem1!="" ){?>
												<strong><?=$elem1->GetName();?><?if($elem1->IsRequired())echo "*";?></strong><br>
												<?=$elem1->PrintElement();?>
												<? if($elem1->HasError()){ ?>
													<font class="errorText"><?=$elem1->GetError();?>&#160;</font><br><br>
												<? }?>
										<?	}else{?>
												&#160;
										<?	}?>
										</td>
										<td width="29">&#160;</td>
										<td width="287" valign="top">
										<? 	if( $elem2!="" ){?>
												<strong><?=$elem2->GetName();?><?if($elem2->IsRequired())echo "*";?></strong><br>
												<?=$elem2->PrintElement();?>
												<? if($elem2->HasError()){ ?>
													<font class="errorText"><?=$elem2->GetError();?>&#160;</font><br><br>
												<? }?>
										<?	}else{?>
												&#160;
										<?	}?>
										</td>
										<td width="29">&#160;</td>
										<td width="287" valign="top">
										<? 	if( $elem3!="" ){?>
												<strong><?=$elem3->GetName();?><?if($elem3->IsRequired())echo "*";?></strong><br>
												<?=$elem3->PrintElement();?>
												<? if($elem3->HasError()){ ?>
													<font class="errorText"><?=$elem3->GetError();?>&#160;</font><br><br>
												<? }?>
										<?	}else{?>
												&#160;
										<?	}?>
										</td>
										<td width="29">&#160;</td>
									</tr>
							<?	}?>
						<?	} ?>
					<?	}?>
					</table>
				</td>
			</tr>
		</table><?
	}	
}
?>