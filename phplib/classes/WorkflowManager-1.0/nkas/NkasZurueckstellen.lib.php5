<?php
/**
 * Status "Zurückstellen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasZurueckstellen extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 0 Tage
		$this->obj->SetDeadline(time());
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
			$this->formElementValues["rueckstellungDatum"]=$this->obj->GetRueckstellungBis()==0 ? "" : date("d.m.Y", $this->obj->GetRueckstellungBis());
			$this->formElementValues["rueckstellungBegruendung"]=$this->obj->GetRueckstellungBegruendung();
		}		
		$this->elements[] = new DateElement("rueckstellungDatum", "Aktionsdatum", $this->formElementValues["rueckstellungDatum"], true, $this->error["rueckstellungDatum"]);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		$this->elements[] = new TextAreaElement("rueckstellungBegruendung", "Beschreibung", $this->formElementValues["rueckstellungBegruendung"], true, $this->error["rueckstellungBegruendung"]);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Verantwortlicher? Kunde oder Kostenmanager?
		$options = Array();
		$options[] = Array("name" => "Bitte wählen...", "value" => "");
		$options[] = Array("name" => "Forderungsmanager", "value" => 1);
		$options[] = Array("name" => "Kunde", "value" => 2);
		$this->elements[] = new DropdownElement("nextAction", "<br />Verantwortlich", !isset($this->formElementValues["nextAction"]) ? Array() : $this->formElementValues["nextAction"], true, $this->error["nextAction"], $options, false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Zurückstellen abbrechen
		$this->elements[] = new CheckboxElement("cancelProcess", "<br>Vorgang abbrechen", "", false, $this->error["cancelProcess"]);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		if ($gotoNextStatus && $this->formElementValues["cancelProcess"]=="on")
		{
			$this->obj->SetRueckstellungBis(0);
			$this->obj->SetRueckstellungBegruendung("");
			return true;
		}
		// Datum
		if (trim($this->formElementValues["rueckstellungDatum"])!="")
		{
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["rueckstellungDatum"]);
			if ($tempValue===false) $this->error["rueckstellungDatum"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $this->obj->SetRueckstellungBis($tempValue);
		}
		else
		{
			$this->obj->SetRueckstellungBis(0);
		}
		// Bezeichnung
		$this->obj->SetRueckstellungBegruendung($this->formElementValues["rueckstellungBegruendung"]);
		if (count($this->error)==0)
		{
			$returnValue=$this->obj->Store($this->db);
			if ($returnValue===true)
			{
				// Prüfen, ob in nächsten Status gesprungen werden kann
				if (!$gotoNextStatus) return true;
				if ($this->formElementValues["nextAction"]=="") $this->error["nextAction"]="Um die Aufgabe abschließen zu können, müssen Sie ein Auswahl treffen.";
				if ($this->obj->GetRueckstellungBis()==0) $this->error["rueckstellungDatum"]="Um die Aufgabe abschließen zu können, müssen Sie ein Datum festlegen, bis zu welchem der Prozess zurückgestellt werden soll.";
				if (trim($this->obj->GetRueckstellungBegruendung())=="") $this->error["rueckstellungBegruendung"]="Um die Aufgabe abschließen zu können, müssen Sie eine Begründung für die Rückstellung eingeben.";
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
		if ($this->formElementValues["cancelProcess"]=="on") return 2;
		return (int)$this->formElementValues["nextAction"]-1;
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