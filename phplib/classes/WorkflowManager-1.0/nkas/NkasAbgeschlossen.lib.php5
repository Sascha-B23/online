<?php
/**
 * Status "Prozess abgeschlossen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasAbgeschlossen extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Zeitpunkt der Fertigstellung speichern
		$this->obj->SetZahlungsdatum(time());
		$this->obj->SetDeadline(0);
		$this->obj->SetFinished(time());
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		ob_start();
		?>
			<strong>Dieser Prozess wurde am <?=date("d.m.Y", $this->obj->GetFinished())?> um <?=date("H:i", $this->obj->GetFinished());?> Uhr erfolgreich abgeschlossen</strong><br>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		
		$options = Array(Array("name" => "Bitte wählen...", "value" => -1));
		$pathList = $this->obj->GetProzessPath();
		foreach ($pathList as $path) 
		{
			if (is_a($path['obj'], 'ProcessStatusGroup')) continue;
			$options[] = Array("name" => $path['path'], "value" => $path['process_id']);
		}
		$this->elements[] = new DropdownElement("selectProcess", "Neue Teilabrechnung für folgenden Prozess erfassen", $this->formElementValues["selectProcess"], true, $this->error["selectProcess"], $options);
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		if ($this->formElementValues["selectProcess"]!="-1")
		{
			// if process is in a group set this process as selected process in group
			if (is_a($this->masterObject, 'ProcessStatusGroup'))
			{
				// search for selected process...
				$processList = $this->masterObject->GetProcess();
				foreach ($processList as $prozess)
				{
					if (WorkflowManager::GetProcessStatusId($prozess)==$this->formElementValues["selectProcess"])
					{
						// set the selected process 
						$this->masterObject->SetSelectedProcessStatus($prozess);
						return true;
					}
				}
				$this->error["selectProcess"] = "Prozess mit der ID '".$this->formElementValues["selectProcess"]."' konnte nicht gefunden werden";
				return false;
			}
			return true;
		}
		$this->error["selectProcess"] = "Bitte wählen Sie den Prozess aus, für den Sie eine neue Teilabrechnung anlegen möchten.";
		return false;
	}
	
	/**
	 * Inject HTML/JavaScript before the UI is send to the browser 
	 */
	public function PrePrint()
	{
		
	}
	
	/**
	 * Inject HTML/JavaScript after the UI is send to the browser 
	 */
	public function PostPrint()
	{
		
	}
	
	/**
	 * Return the following status 
	 */
	public function GetNextStatus()
	{
		if ($this->formElementValues["selectProcess"]!="-1") return 1;
		return 0;
	}
						
	/**
	 * return if this status of the current process can be edited when in a group
	 * @return boolean
	 */
	public function IsEditableInGroup()
	{
		// only group is editable
		return ($this->IsProcessGroup() ? true : false);
	}
	
}
?>