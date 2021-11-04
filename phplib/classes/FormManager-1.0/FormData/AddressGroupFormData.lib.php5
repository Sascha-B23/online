<?php
/**
 * FormData-Implementierung für die Adress-Gruppen
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class AddressGroupFormData extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		// Icon und Überschrift festlegen
		$this->options["icon"] = "adressgruppen.png";
		$this->options["icontext"] = "Gruppe ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["name"]=$this->obj->GetName();
		}
		$this->elements[] = new TextElement("name", AddressGroup::GetAttributeName($this->languageManager, 'name'), $this->formElementValues["name"], true, $this->error["name"]);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		$this->obj->SetName($this->formElementValues["name"]);
		$returnValue=$this->obj->Store($this->db);
		if ($returnValue===true) return true;
		if ($returnValue==-101) $this->error["name"]="Bitte geben Sie einen Namen ein";
		return false;
	}
	
}
?>