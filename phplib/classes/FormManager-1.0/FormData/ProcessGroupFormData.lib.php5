<?php
/**
 * FormData-Implementierung für die Pakete
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class ProcessGroupFormData extends FormData 
{	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 * @param type $edit
	 * @param type $loadFromObject
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $UM_GROUP_BASETYPE;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "passiveTask.png";
		$this->options["icontext"] = "Paket ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject )
		{
			$this->formElementValues["name"]=$this->obj->GetName();
		}
		$this->elements[] = new TextElement("name", ProcessStatusGroup::GetAttributeName($this->languageManager, 'name'), $this->formElementValues["name"], true, $this->error["name"]);
		
		/* ASSIGNED PROZESS LIST */
		// build table with assigned processes
		$processList = $this->obj->GetProcess();
		ob_start();
		if (count($processList)>0)
		{	?>
			<table border="0" cellpadding="3" cellspacing="0">
				<tr>
					<td class="TAPMatrixHeader2">&#160;</td>
					<td class="TAPMatrixHeader2"><?=ProcessStatus::GetAttributeName($this->languageManager, 'processStatusId');?></td>
					<td class="TAPMatrixHeader2"><?=WorkflowStatus::GetAttributeName($this->languageManager, 'currentStatus');?></td>
					<td class="TAPMatrixHeader2"><?=CLocation::GetAttributeName($this->languageManager, 'name');?></td>
					<td class="TAPMatrixHeader2"><?=CShop::GetAttributeName($this->languageManager, 'name');?></td>
					<td class="TAPMatrixHeader2"><?=AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr');?></td>
					<td class="TAPMatrixHeader2">Währung</td>
					<td class="TAPMatrixHeader2"><?=CGroup::GetAttributeName($this->languageManager, 'name');?></td>
					<td class="TAPMatrixHeader2"><?=CCompany::GetAttributeName($this->languageManager, 'name');?></td>
				</tr>
				<?
				foreach ($processList as $process)
				{	?>
					<tr>
						<td class="TAPMatrixRow"><input type="checkbox" name="processToRemove[]" value="<?=$process->GetPKey();?>" /></td>
						<td class="TAPMatrixRow"><?=WorkflowManager::GetProcessStatusId($process);?></td>
						<td class="TAPMatrixRow"><?=$process->GetCurrentStatusName($_SESSION["currentUser"], $this->db);?></td>
						<td class="TAPMatrixRow"><?=$process->GetLocationName();?></td>
						<td class="TAPMatrixRow"><?=$process->GetShopName();?></td>
						<td class="TAPMatrixRow"><?=$process->GetAbrechnungsJahrString();?></td>
						<td class="TAPMatrixRow"><?=$process->GetCurrency();?></td>
						<td class="TAPMatrixRow"><?=$process->GetGroupName();?></td>
						<td class="TAPMatrixRow"><?=$process->GetCompanyName();?></td>
					</tr>
					<?
				}
				?>
			</table>
			<br />
			<input type="button" class="formButton2" value="Markierte Prozesse aus Paket entfernen und speichern" onclick="remove_process();" /><br /><br />
			<?
		}
		else
		{
			?>Es sind derzeit keine Prozesse zugeordnet<?
		}
		?>
			<input type="hidden" name="processToAdd" id="processToAdd" value="" />
		<?
		if ($this->error["processToAdd"]!="")
		{
			?><br /><br /><div class="errorText"><?=$this->error["processToAdd"];?></div><?
		}
		$html = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($html, "Prozesse");
		/* PROCESS TO ADD SELECTION LIST */
		$processIdList = Array();
		foreach ($processList as $value)
		{
			$processIdList[] = $value->GetPKey();
		}
		
		global $dynamicTableManager;
		$dynamicTable = $dynamicTableManager->GetDynamicTableById("TasksSelectListData");
		if ($dynamicTable==null) $dynamicTable = new TasksSelectListData($this->db, $this->obj);
		$dynamicTable->SetProcessStatusGroupToEdit($this->obj);
		
		$this->elements[] = new DynamicListElement("processList", "Prozess hinzufügen", $this->formElementValues["processList"], false, $this->error["processList"], false, $dynamicTable);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(false);
		ob_start();
		$allowedStatus = WorkflowManager::GetStatusForProcessGroupCreation();
		$statusNames = Array();
		foreach ($allowedStatus as $statusID) 
		{
			$statusNames[] = WorkflowManager::GetStatusName($this->db, $_SESSION["currentUser"], $statusID);
		}
		?><input type="button" class="formButton2" value="Markierte Prozesse in Paket hinzufügen und speichern" onclick="add_process();" /><br /><br />
		Folgende Eigenschaften der Prozesse müssen für die Bildung eines Paketes identisch sein: <br />
			1. <?=CGroup::GetAttributeName($this->languageManager, 'name');?><br />
			2. <?=CCompany::GetAttributeName($this->languageManager, 'name');?><br />
			3. Währung<br />
			4. <?=WorkflowStatus::GetAttributeName($this->languageManager, 'currentStatus');?> (<?=implode(', ', $statusNames)?>)<br />
			5. SFM-AP Laden<br />
			6. SFM-AP Nebenkostenanalyst<br />
			8. SFM-AP Buchhaltung<br />
			9. Kunde-AP Laden<br />
			10. Kunde-AP Buchhaltung<br />
		<?
		$html = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($html, "&#160;");
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 * @return boolean
	 */
	public function Store()
	{
		$this->error=array();
		$this->obj->SetName($this->formElementValues["name"]);
		$newGroup = false;
		if ($this->obj->GetPKey()==-1)
		{
			$newGroup = true;
			$returnValue=$this->obj->Store($this->db);
			if ($returnValue!==true)
			{
				if( $returnValue==-101 )$this->error["name"]="Bitte geben Sie einen Namen ein";
				else $this->error["misc"][]="Es ist ein unerwarteter Fehler aufgetreten (Fehlercode: ".$returnValue.")";
				return false;
			}
		}
		
		// remove process from group
		if (is_array($this->formElementValues["processToRemove"]) && count($this->formElementValues["processToRemove"])>0)
		{
			foreach ($this->formElementValues["processToRemove"] as $value)
			{
				$process = new ProcessStatus($this->db);
				if ($process->Load((int)$value, $this->db)===true)
				{
					$this->obj->RemoveProcessFromGroup($this->db, $process);
				}
			}
		}
		// add process to group
		if (trim($this->formElementValues["processToAdd"])!="")
		{
			$abschlussdatum = 0;
			$zahlungsdatum = 0;
			$deadline = 0;

			$idsToAdd = explode(",", $this->formElementValues["processToAdd"]);
			foreach ($idsToAdd as $value)
			{
				$process = new ProcessStatus($this->db);
				if ($process->Load((int)$value, $this->db)===true)
				{
					if (!$newGroup)
					{
						// If a process is added to the group - overwrite process dates with dates from group
						$process->SetAbschlussdatum($this->obj->GetAbschlussdatum());
						$process->SetZahlungsdatum($this->obj->GetZahlungsdatum());
						$process->SetDeadline($this->obj->GetDeadline());
					}
					$returnValue = $this->obj->AddProcessToGroup($this->db, $process);
					if ($returnValue!==true)
					{
						$this->error["processToAdd"].="Der Prozess ".WorkflowManager::GetProcessStatusId($process)." konnte nicht hinzugefügt werden. ";
						switch($returnValue)
						{
							case ProcessStatusGroup::ERROR_DIFFERENT_STATUS:
								$this->error["processToAdd"].="Der Prozess befindet sich in einem anderen Status als das Paket.";
								break;
							case ProcessStatusGroup::ERROR_WRONG_STATUS:
								$this->error["processToAdd"].="Der Prozess befindet sich in einem unzulässigen Status für die Paketbildung.";
								break;
							case ProcessStatusGroup::ERROR_DIFFERENT_FMS_PERSON:
								$this->error["processToAdd"].="Der SFM-Ansprechpartner von Prozess und Paket muss übereinstimmen.";
								break;
							case ProcessStatusGroup::ERROR_DIFFERENT_FMSLEADER_PERSON:
								$this->error["processToAdd"].="Der SFM-Nebenkostenanalyst von Prozess und Paket muss übereinstimmen.";
								break;
							case ProcessStatusGroup::ERROR_DIFFERENT_FMSACCOUNTSDEPARTMENT_PERSON:
								$this->error["processToAdd"].="Die SFM-Buchhaltung von Prozess und Paket muss übereinstimmen.";
								break;
							case ProcessStatusGroup::ERROR_DIFFERENT_CUSTOMER_PERSON:
								$this->error["processToAdd"].="Der Ansprechpartner beim Kunden von Prozess und Paket muss übereinstimmen.";
								break;
							case ProcessStatusGroup::ERROR_DIFFERENT_CUSTOMERACCOUNTING_PERSON:
								$this->error["processToAdd"].="Der Buchhaltungsansprechpartner beim Kunden von Prozess und Paket muss übereinstimmen.";
								break;
							case ProcessStatusGroup::ERROR_DIFFERENT_CUSTOMERSUPERVISOR_PERSON:
								$this->error["processToAdd"].="Der Bereichsleiter beim Kunden von Prozess und Paket muss übereinstimmen.";
								break;
							case ProcessStatusGroup::ERROR_DIFFERENT_CUSTOMER_GROUP:
								$this->error["processToAdd"].="Die Kundengruppe von Prozess und Paket muss übereinstimmen.";
								break;
							case ProcessStatusGroup::ERROR_DIFFERENT_CUSTOMER_COMPANY:
								$this->error["processToAdd"].="Die Firma von Prozess und Paket muss übereinstimmen.";
								break;
							case ProcessStatusGroup::ERROR_DIFFERENT_CURRENCY:
								$this->error["processToAdd"].="Die Währung von Prozess und Paket muss übereinstimmen.";
								break;
							case ProcessStatusGroup::ERROR_STORE_TO_DB:
								$this->error["processToAdd"].="Änderung konnte nicht gespeichert werden.";
								break;
							default:
								$this->error["processToAdd"].="Fehlercode ".$returnValue;
						}
						$this->error["processToAdd"].="<br />";
					}
					else
					{
						if ($abschlussdatum==0 || $process->GetAbschlussdatum()>time() && $abschlussdatum>$process->GetAbschlussdatum()) $abschlussdatum = $process->GetAbschlussdatum(); // Das Datum sein welches am nächsten in der Zukunft liegt
						if ($zahlungsdatum<$process->GetZahlungsdatum()) $zahlungsdatum = $process->GetZahlungsdatum(); // Auftragsende immer das Datum welches am weitesten in der Zukunft liegt
						if ($deadline==0 || $process->GetDeadline()>time() && $deadline>$process->GetDeadline()) $deadline = $process->GetDeadline(); // Das Datum sein welches am nächsten in der Zukunft liegt
					}
				}
				else
				{
					$this->error["processToAdd"].="Der Prozess mit der ID '".$value."' konnte nicht hinzugefügt werden. Das Objekt konnte nicht geladen werden.<br />";
				}
			}
			// Set dates on group creation
			if ($newGroup)
			{
				$this->obj->SetAbschlussdatum($abschlussdatum);
				$this->obj->SetZahlungsdatum($zahlungsdatum);
				$this->obj->SetDeadline($deadline);
				$this->obj->Store($this->db);
			}
		}

		if( count($this->error)>0 )return false;
		$returnValue=$this->obj->Store($this->db);
		if( $returnValue===true )return true;
		if( $returnValue==-101 )$this->error["name"]="Bitte geben Sie einen Namen ein";
		if( $returnValue==-102 )$this->error["name"]="Es exisitiert bereits eine Benutzergruppe mit diesem Namen";
		return false;
	}
	
	/**
	 * This function can be used to output HTML data bevor the form
	 */
	public function PrePrint()
	{
		?>
			<script type="text/javascript">
				<!--
					var selectedTasks = new Array();
					function select_prozess(selected, process_id)
					{
						var temp = new Array();
						for (a=0; a<selectedTasks.length; a++)
						{
							if (selectedTasks[a]==process_id)
							{
								// element allready in list
								if (selected) return true;
								// skip element
								continue;
							}
							temp.push(selectedTasks[a]);
						}
						if (selected)
						{
							temp.push(process_id);
						}
						selectedTasks = temp;
						// write array to hidden form element
						$('processToAdd').value = selectedTasks.join(',')
						//alert($('processToAdd').value);
					}
					
					function remove_process()
					{
						if (confirm('Markierte Prozesse aus dem Paket entfernen?'))
						{
							if ($('processToAdd').value!='')
							{
								if (confirm('Sie haben bereits Prozesse zum Hinzufügen markiert. Wenn Sie fortfahren, geht diese Information verloren.\n\nMöchten Sie fortfahren?'))
								{
									$('processToAdd').value = '';
								}
								else
								{
									return false;
								}		
							}
							$('forwardToListView').value = 'false';
							document.forms.FM_FORM.submit();
						}
					}
					
					function add_process()
					{
						$('forwardToListView').value = 'false';
						document.forms.FM_FORM.submit();
					}
					
					function on_table_loaded()
					{
						// search for checkboxes which have to be selected...
						for (a=0; a<selectedTasks.length; a++)
						{
							if ($('cb_tasksSelectListData_'+selectedTasks[a])!=null)
							{
								$('cb_tasksSelectListData_'+selectedTasks[a]).set('checked', true);
							}
						}
					}
					
				-->
			</script>
		<?
	}
	
}
?>