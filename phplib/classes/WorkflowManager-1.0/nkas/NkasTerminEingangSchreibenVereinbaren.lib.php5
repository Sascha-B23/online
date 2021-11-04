<?php
/**
 * Status "Termin für Eingang Eigentümer-/Verwalter-/Anwalt-Schreiben vereinbaren und dokumentieren"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasTerminEingangSchreibenVereinbaren extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		$this->obj->SetRueckstellungBis(0);
		// Bearbeitungsfrist: 0 Tage
		$this->obj->SetDeadline( time() );
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		if ($loadFromObject)
		{
			// Datum (wird temporär in "RueckstellungBis" gespeichert und beim Übegang in den nächsten Status von dort als Frist übernommen)
			$this->formElementValues["deadline"] = $this->obj->GetRueckstellungBis()==0 ? "" : date("d.m.Y", $this->obj->GetRueckstellungBis());
		}
		$this->elements[] = new DateElement("deadline", "Eingangsdatum", $this->formElementValues["deadline"], true, $this->error["deadline"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		// Datum (wird temporär in "RueckstellungBis" gespeichert und beim Übegang in den nächsten Status von dort als Frist übernommen)
		if (trim($this->formElementValues["deadline"])!="")
		{
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["deadline"]);
			if ($tempValue===false) $this->error["deadline"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $this->obj->SetRueckstellungBis($tempValue);
		}
		else
		{
			$this->obj->SetRueckstellungBis(0);
		}
		// Bezeichnung
		if (count($this->error)==0)
		{
			$returnValue = $this->obj->Store($this->db);
			if ($returnValue===true)
			{
				// Prüfen, ob in nächsten Status gesprungen werden kann
				if (!$gotoNextStatus) return true;
				if ($this->obj->GetRueckstellungBis()==0) $this->error["deadline"]="Um die Aufgabe abschließen zu können, müssen Sie das Eingangsdatum eingeben.";
				if (count($this->error)==0) return true;
			}
			else
			{
				$this->error["misc"][]="Systemfehler (".$returnValue.")";
			}
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
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null && $widerspruch->GetDokumentenTyp()!=Widerspruch::DOKUMENTEN_TYP_PROTOKOLL && $widerspruch->GetDokumentenTyp()!=Widerspruch::DOKUMENTEN_TYP_PROTOKOLLENTWURF) return 0; // Widerspruch
		return 1;	// Protokoll
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