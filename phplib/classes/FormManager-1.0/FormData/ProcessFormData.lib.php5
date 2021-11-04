<?php
/**
 * Implementierung der Basisklasse FormData für die Prozess-Ansicht
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class ProcessFormData extends FormData 
{
	/**
	 * List with all created StatusFormDataEntry instances
	 * @var StatusFormDataEntry[]
	 */
	protected $statusFormDataEntryMap = Array();
	
	/**
	 * the master process status object
	 * @var ProcessStatus
	 */
	protected $masterObject = null;
	
	/**
	 * Konstruktor
	 * @param string $formElementValues	Values der Elemente (Post-Vars)
	 * @param ProcessStatus $object
	 */
	public function ProcessFormData($formElementValues, $object, $masterObject, $db)
	{
		$this->masterObject = $masterObject;
		// edit or create new...
		if ($this->LoadFormDataObjectByID($db, $this->formElementValues["editElement"]))
		{
			// POST-Vars filtern wenn diese nicht vom aktuellen Status kommen
			if (((int)$_POST["currentStep"])!=$this->masterObject->GetCurrentStatus())
			{
				$_POST = $this->FilterVars($_POST);
				$formElementValues = $this->FilterVars($formElementValues);
			}
		}
		parent::__construct($formElementValues, $object, $db);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		// Icon und Überschrift festlegen
		$this->options["icon"] = "newProcess.png";
		$this->options["icontext"] = $this->masterObject->GetCurrentStatusName($_SESSION["currentUser"], $this->db);
		if (WorkflowManager::UserAllowedToEditProzess($this->db, $this->masterObject->GetCurrentStatus()))
		{
			return $this->InitElementsByStatus($edit, $loadFromObject, $this->masterObject->GetCurrentStatus());
		}
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		if (WorkflowManager::UserAllowedToEditProzess($this->db, $this->masterObject->GetCurrentStatus()))
		{
			$gotoPreviousStatus = $_POST["previousStep"]=="true" ? true : false;
			$aboardStatus = $_POST["aboardStep"]=="true" ? true : false;
			if ($gotoPreviousStatus || $aboardStatus)
			{
				$currentStatus = $this->masterObject->GetCurrentStatus();
				// Es soll in den vorherigen Schritt gesprungen werden
				if ($gotoPreviousStatus && !$this->masterObject->GotoPreviousStatus()) return false;
				if ($aboardStatus && !$this->masterObject->AboardStatus()) return false;
				// call cleanup function
				$this->OnReturnToPreviousStatusByStatus($currentStatus);
				// Den vom Benuter geplanter Zeitpunkt und die Dauer zurücksetzen
				$this->masterObject->SetDateAndTime(0);
				$this->masterObject->SetPlannedDuration(0);
				$this->masterObject->Store($this->db);
				// reload child object
				$this->obj->Load($this->obj->GetPKey(), $this->db);
				if ($this->masterObject->IsActiveProzess($_SESSION["currentUser"], $this->db))
				{
					// Nicht auf die Übersicht zurückspringen
					$_POST = $this->FilterVars($_POST);
					$this->formElementValues = $this->FilterVars($this->formElementValues);
					return false;
				}
				return true;
			}
			else
			{
				$gotoNextStatus = $_POST["nextStep"]=="true" ? true : false;
				if ($this->StoreByStatus($this->masterObject->GetCurrentStatus(), $gotoNextStatus))
				{
					if ($gotoNextStatus)
					{
						// Check if all dates are valid befor jumping to next status
						if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_EDIT_TAB_CURRENTTASK) && !$this->ProcessDatesValid())
						{
							$this->error["misc"][]="Bitte aktualisieren Sie ihr Auftragsdatum (Antwortfrist Vermieter und Auftragsende) im Reiter 'Aktuelle Aufgabe'.";
							return false;
						}
						$currentStatus = $this->masterObject->GetCurrentStatus();
						// Es soll in den nächsten Schritt gesprungen werden
						if (!$this->masterObject->GotoNextStatus($this->GetNextStatusByStatus($currentStatus))) return false;
						// Den vom Benuter geplanter Zeitpunkt und die Dauer zurücksetzen
						$this->masterObject->SetDateAndTime(0);
						$this->masterObject->SetPlannedDuration(0);
						if ($this->masterObject->Store($this->db)===true)
						{
							// reload child object
							$this->obj->Load($this->obj->GetPKey(), $this->db);
							$this->PostStoreByStatus($currentStatus);
						}
						// Vorbereitung für den nächsten Status ausführen
						$this->PrepareForStatusByStatus($this->masterObject->GetCurrentStatus());
						if ($this->masterObject->IsActiveProzess($_SESSION["currentUser"], $this->db))
						{
							// Nicht auf die Übersicht zurückspringen
							$_POST = $this->FilterVars($_POST);
							$this->formElementValues = $this->FilterVars($this->formElementValues);
							return false;
						}
					}
					// Auf die Übersicht zurückspringen
					return true;
				}
			}
		}
		// Nicht auf die Übersicht zurückspringen
		return false;
	}
	
	/**
	 * Check if the dates of the process are still valid
	 * @return boolean
	 */
	protected function ProcessDatesValid()
	{
		$dayStartTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$abschlussdatum = $this->masterObject->GetAbschlussdatum();	// Antwortfrist Vermieter

		if ($abschlussdatum < $dayStartTime)
		{
			return false;
		}

		return true;
	}
	
	/**
	 * return if the current status of the current process can be edited if in a group
	 * @return boolean
	 */
	public function IsEditableInGroup()
	{
		$statusFormDataEntry = $this->GetStatusFormDataEntry($this->masterObject->GetCurrentStatus());
		if ($statusFormDataEntry==null) return false;
		return $statusFormDataEntry->IsEditableInGroup();
	}
	
	/**
	 * return if the current status of the current process can be edited if in a group
	 * @return boolean
	 */
	public function CanBeCompletetdInGroup()
	{
		$statusFormDataEntry = $this->GetStatusFormDataEntry($this->masterObject->GetCurrentStatus());
		if ($statusFormDataEntry==null) return false;
		return $statusFormDataEntry->CanBeCompletetdInGroup();
	}
	
	/**
	 * Return if the Status can be aboarded by user
	 * @return boolean
	 */
	public function CanBeAboarded()
	{
		$statusFormDataEntry = $this->GetStatusFormDataEntry($this->masterObject->GetCurrentStatus());
		if ($statusFormDataEntry==null) return false;
		return $statusFormDataEntry->CanBeAboarded();
	}
	
	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben bevor etwas an den Browser gesendet wurde
	 */
	public function PrePrint()
	{
		if (get_class($this->masterObject)=='ProcessStatus' || $this->IsEditableInGroup())
		{
			if (WorkflowManager::UserAllowedToEditProzess($this->db, $this->masterObject->GetCurrentStatus()))
			{
				return $this->PrePrintByStatus( $this->masterObject->GetCurrentStatus());
			}
		}
		return false;
	}
	
	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben nachdem das Formular ausgegeben wurde
	 */
	public function PostPrint()
	{
		if (get_class($this->masterObject)=='ProcessStatus' || $this->IsEditableInGroup())
		{
			if (WorkflowManager::UserAllowedToEditProzess($this->db, $this->masterObject->GetCurrentStatus()))
			{
				return $this->PostPrintByStatus( $this->masterObject->GetCurrentStatus());
			}
		}
		return false;
	}
	
	/**
	 * Gibt den aktuellen Prozess-Status zurück
	 * @return int
	 */
	public function GetCurrentStatus()
	{
		return $this->masterObject->GetCurrentStatus();
	}
	
	/**
	 * Gibt den vorherigen Prozess-Status zurück
	 * @return int
	 */
	public function GetPreviousStatus()
	{
		return $this->masterObject->GetLastStatus();
	}
	
	/**
	 * Diese Funktion filtert alle unnötigen POST-Vars aus, wenn von einem zum 
	 * anderen Status gesprungen wird.
	 */
	static public function FilterVars($vars)
	{
		$varNew=Array();
		$varNew["UID"]=$vars["UID"];
		$varNew["showTab"]=$vars["showTab"];
		$varNew["processId"]=$vars["processId"];
		$varNew["editElement"]=$vars["editElement"];
		$varNew["currentStep"]=$vars["currentStep"];
		return $varNew;
	}
	
	/**
	 * Return the requested StatusFormDataEntry instance
	 * @param int $statusId 
	 * @return NkasStatusFormDataEntry
	 */
	protected function GetStatusFormDataEntry($statusId)
	{
		if (isset($this->statusFormDataEntryMap[$statusId]))
		{
			$this->statusFormDataEntryMap[$statusId]->SetFormElementValues($this->formElementValues);
			return $this->statusFormDataEntryMap[$statusId];
		}
		$returnValue = null;
		$statusInfo = WorkflowManager::GetProzessStatusForStatusID($statusId);
		if (isset($statusInfo["class"]))
		{
			$returnValue = eval("return new ".$statusInfo["class"]."(\$this->db, \$this->obj, \$this->masterObject, \$this->formElementValues);");
			if ($returnValue!=null) $this->statusFormDataEntryMap[$statusId] = $returnValue;
		}
		return $returnValue;
	}
	
	/**
	 * Weiche für die einzelnen Status
	 */
	protected function PrepareForStatusByStatus($status)
	{
		$statusFormDataEntry = $this->GetStatusFormDataEntry($status);
		if ($statusFormDataEntry!=null)
		{
			// Prepare the next Status allways with the masterobject!
			$statusFormDataEntry->SetObject($this->masterObject);
			$returnValue = $statusFormDataEntry->Prepare();
			$statusFormDataEntry->SetObject($this->obj);
			return $returnValue;
		}
		$evalString="return \$this->PrepareForStatus_".$status."();";
		return eval($evalString);
	}
	
	/**
	 * Weiche für die einzelnen Status
	 */
	protected function OnReturnToPreviousStatusByStatus($status)
	{
		$statusFormDataEntry = $this->GetStatusFormDataEntry($status);
		if ($statusFormDataEntry!=null)
		{
			// Prepare the next Status allways with the masterobject!
			$statusFormDataEntry->SetObject($this->masterObject);
			$returnValue = $statusFormDataEntry->OnReturnToPreviousStatus();
			$statusFormDataEntry->SetObject($this->obj);
			return $returnValue;
		}
		echo "StatusFormDataEntry for Status '".$status."' not found!";
		exit;
	}
	
	/**
	 * Weiche für die einzelnen Status
	 */
	protected function InitElementsByStatus($edit, $loadFromObject, $status)
	{
		$statusFormDataEntry = $this->GetStatusFormDataEntry($status);
		if ($statusFormDataEntry!=null)
		{
			// call status specific InitElements function
			$returnValue = $statusFormDataEntry->InitElements($loadFromObject);
			// get elements
			$this->elements = $statusFormDataEntry->GetUiElements();
			return $returnValue;
		}
		$evalString="return \$this->InitElementsForStatus_".$status."(\$edit, \$loadFromObject);";
		return eval($evalString);
	}

	/**
	 * Weiche für die einzelnen Status
	 */
	protected function StoreByStatus($status, $gotoNextStatus)
	{
		$statusFormDataEntry = $this->GetStatusFormDataEntry($status);
		if ($statusFormDataEntry!=null)
		{
			// call status specific Store function
			$returnValue = $statusFormDataEntry->Store($gotoNextStatus);
			// get errors
			$this->error = $statusFormDataEntry->GetUiErrors();
			return $returnValue;
		}
		$evalString="return \$this->StoreForStatus_".$status."(\$gotoNextStatus);";
		return eval($evalString);
	}

	/**
	 * Weiche für die einzelnen Status
	 */
	protected function PrePrintByStatus($status)
	{
		$statusFormDataEntry = $this->GetStatusFormDataEntry($status);
		if ($statusFormDataEntry!=null)
		{
			return $statusFormDataEntry->PrePrint();
		}
		$evalString="return \$this->PrePrintForStatus_".$status."();";
		return eval($evalString);
	}
	
	/**
	 * Weiche für die einzelnen Status
	 */
	protected function PostPrintByStatus($status)
	{
		$statusFormDataEntry = $this->GetStatusFormDataEntry($status);
		if ($statusFormDataEntry!=null)
		{
			return $statusFormDataEntry->PostPrint();
		}
		$evalString="return \$this->PostPrintForStatus_".$status."();";
		return eval($evalString);
	}
	
	/**
	 * Weiche für die einzelnen Status
	 */
	protected function GetNextStatusByStatus($status)
	{
		$statusFormDataEntry = $this->GetStatusFormDataEntry($status);
		if ($statusFormDataEntry!=null)
		{
			return $statusFormDataEntry->GetNextStatus();
		}
		$evalString="return \$this->GetNextStatusByStatus_".$status."();";
		return eval($evalString);
	}
	
	protected function PostStoreByStatus($status)
	{
		$statusFormDataEntry = $this->GetStatusFormDataEntry($status);
		if ($statusFormDataEntry!=null)
		{
			return $statusFormDataEntry->PostStore();
		}
		$evalString="return \$this->PostStoreByStatus_".$status."();";
		return eval($evalString);
	}
	
	/**
	 * Return the id of the entity
	 * @return string
	 */
	public function GetFormDataObjectID()
	{
		if ($this->obj!=null) return WorkflowManager::GetProcessStatusId($this->obj);
		return -1;
	}
	
	/**
	 * Load the objects data by id
	 * @param mixed $objectId
	 * @return boolean
	 */
	public function LoadFormDataObjectByID(DBManager $db, $objectId)
	{
		if (trim($objectId)=='') return false;
		return WorkflowManager::LoadProcessStatusById($db, $this->obj, $objectId);
	}
	
}
?>