<?php
/**
 * Status "Abbrechen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasAbbrechen extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 0 Tage
		$this->obj->SetDeadline(time());
		// Für Freigabe zuständigen FMS-Benutzer zurücksetzen
		$this->obj->SetZuweisungUser(null);
		// Änderungen speichern
		$this->obj->Store($this->db);
		
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			// Abbruchprotokoll erzeugen
			$abbruchProtokoll=new AbbruchProtokoll($this->db);
			$abbruchProtokoll->SetDatumAbbruch(time());
			$abbruchProtokoll->SetWiderspruch($widerspruch);
			$abbruchProtokoll->SetUser($_SESSION["currentUser"]);
			$abbruchProtokoll->Store($this->db);
		}
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		if ($loadFromObject)
		{
			$this->formElementValues["rsuser"] = $this->obj->GetZuweisungUser()!=null ? $this->obj->GetZuweisungUser()->GetPKey() : "";
			$widerspruch=$this->obj->GetWiderspruch($this->db);
			if ($widerspruch!=null)
			{
				$abbruchProtokoll=$widerspruch->GetLastAbbruchProtokoll($this->db);
				if ($abbruchProtokoll!=null) $this->formElementValues["abbruchBegruendung"]=$abbruchProtokoll->GetBegruendung();
			}
		}
		// Begründung
		$this->elements[] = new TextAreaElement("abbruchBegruendung", "Hinweise für Kunden", $this->formElementValues["abbruchBegruendung"], false, $this->error["abbruchBegruendung"]);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Abbruch abbrechen
		$this->elements[] = new CheckboxElement("cancelProcess", "<br>Abbruch der Prüfung wegen Geringfügigkeit zurückziehen", "", false, $this->error["cancelProcess"]);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		// Abbruch zurückziehen?
		if( $this->formElementValues["cancelProcess"]=="on" )
		{
			return true;
		}
		// AbbruchProtokoll
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			$abbruchProtokoll=$widerspruch->GetLastAbbruchProtokoll($this->db);
			$abbruchProtokoll->SetBegruendung($this->formElementValues["abbruchBegruendung"]);
			$abbruchProtokoll->SetUser( $_SESSION["currentUser"] );
			//$abbruchProtokoll->SetUserRelease($user);
			$returnValue=$abbruchProtokoll->Store($this->db);
			if ($returnValue===true)
			{
				// Prüfen, ob in nächsten Status gesprungen werden kann
				if ($gotoNextStatus && trim($abbruchProtokoll->GetBegruendung())=="") $this->error["abbruchBegruendung"]="Bitte geben Sie für den Kunden einen Hinweis ein.";
			}
			else
			{
				$this->error["misc"][]="Systemfehler (2/".$returnValue.")";
			}
		}
		else
		{
			$this->error["misc"][]="Untergeordneter Widerspruch konnte nicht gefunden werden.";
		}
		
		if( count($this->error)==0 )
		{
			// Festlegen, wohin nach Status 35 & 28 (Kunde informieren & Buchhaltung informieren) gesprungen werden soll
			$dataToStore = Array();
			$dataToStore["useBranchAfterStatus35"] = 0;
			$dataToStore["useBranchAfterStatus28"] = 2;
			$this->obj->SetAdditionalInfo(serialize($dataToStore));
			$returnValue=$this->obj->Store($this->db);
			if ($returnValue!==true)
			{
				$this->error["misc"][]="Systemfehler (".$returnValue.")";
				return false;	
			}
			return true;
		}
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
		// Abbruch zurückziehen
		if ($this->formElementValues["cancelProcess"]=="on")
		{
			// Für Freigabe zuständigen FMS-Benutzer zurücksetzen
			$this->obj->SetZuweisungUser(null);
			// In vorherigen Status springen
			return -1;
		}
		// Weiter
		return 0;
	}
	
	/**
	 * Is called after the changes have been saved successfully
	 */
	public function PostStore()
	{
		// Abbruch zurückziehen
		if ($this->formElementValues["cancelProcess"]=="on")
		{
			// Abbruchprotokoll wieder löschen
			$widerspruch=$this->obj->GetWiderspruch($this->db);
			if ($widerspruch!=null)
			{
				$abbruchProtokoll = $widerspruch->GetLastAbbruchProtokoll($this->db);
				if ($abbruchProtokoll!=null) $abbruchProtokoll->DeleteMe($this->db);
			}
		}
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