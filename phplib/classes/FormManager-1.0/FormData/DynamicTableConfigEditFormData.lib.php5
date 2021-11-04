<?php
/**
 * FormData-Implementierung für DynamicTableConfig
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class DynamicTableConfigEditFormData extends FormData 
{
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $UM_GROUP_BASETYPE;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "filter.png";
		$this->options["icontext"] = "Filterkonfiguration bearbeiten";
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["name"] = $this->obj->GetName();
			$this->formElementValues["defaultConfig"] = $this->obj->IsDefault() ? "on" : "";
		}
		// Elemente anlegen
		$this->elements[] = new TextElement("name", "Name", $this->formElementValues["name"], true, $this->error["name"]);
		$this->elements[] = new CheckboxElement("defaultConfig", "Standardfilter", $this->formElementValues["defaultConfig"], false, $this->error["defaultConfig"]);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		if (trim($this->formElementValues["name"])=="") $this->error["name"]="Bitte geben Sie einen Namen für die Konfiguraion an.";
		if (count($this->error)==0)
		{
			$this->obj->SetName(trim($this->formElementValues["name"]));
			$this->obj->SetDefault($this->formElementValues["defaultConfig"]=="on" ? true : false);
			$returnValue=$this->obj->Store($this->db);
			if( $returnValue===true ) return true;
			$this->error["misc"][]="Systemfehler (".$returnValue.")";
		}
		return false;
	}
	
}
?>