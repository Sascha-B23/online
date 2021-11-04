<?php
/**
 * FormData-Implementierung für die Textbausteine
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TextModuleFormData extends FormData 
{
	/**
	 * CustomerManager
	 * @var CustomerManager 
	 */
	protected $customerManager = null;
	
	/**
	 * Constructor
	 * @param array $formElementValues	form element values (POST-Vars)
	 * @param DBEntry $object			Data-object for this form
	 * @param DBManager $db
	 */
	public function TextModuleFormData($formElementValues, TextModule $object, DBManager $db, CustomerManager $customerManager)
	{
		$this->customerManager = $customerManager;
		parent::__construct($formElementValues, $object, $db);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $CM_LOCATION_TYPES;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "textmodule.png";
		$this->options["icontext"] = "Textbaustein ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["country"]=$this->obj->GetCountry();
			$this->formElementValues["name"]=$this->obj->GetTitle();
			$this->formElementValues["textLeft"]=$this->obj->GetTextLeft();
			$this->formElementValues["numberOfTextBlocks"]=$this->obj->GetNumberOfTextBlocks();
			for ($a=0; $a<$this->obj->GetNumberOfTextBlocks(); $a++)
			{
				$this->formElementValues["beschreibung_".$a]=$this->obj->GetTextRight($a);
			}
			$this->formElementValues["protected"]=($this->obj->IsDeletable($this->db) ? "" : "on");
		}
		
		// Elemente anlegen
		// Das Land kann nur geändert werden, wenn der Textbaustein noch nicht mit einem WS-Generator verknüpft ist
		if ($this->obj->IsDeletable($db) /* || $_SESSION["currentUser"]->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_SUPERUSER*/)
		{
			$options=Array();
			$countries = $this->customerManager->GetCountries();
			$options[]=Array("name" => "Bitte wählen...", "value" => "");
			for ($a=0; $a<count($countries); $a++)
			{
				$options[]=Array("name" => $countries[$a]->GetName(), "value" => $countries[$a]->GetIso3166());
			}
			$this->elements[] = new DropdownElement("country", "Land", !isset($this->formElementValues["country"]) ? Array() : $this->formElementValues["country"], true, $this->error["country"], $options, false);
		}
		
		$this->elements[] = new TextElement("name", "Name", $this->formElementValues["name"], true, $this->error["name"]);
		$this->elements[] = new TextAreaElement("textLeft", "Standpunkt Eigentümer/Verwalter/Anwalt", $this->formElementValues["textLeft"], true, $this->error["textLeft"], false, true);
		$this->elements[count($this->elements)-1]->SetWidth(400);
		
		// Nur Superuser können die Anzahl der Bedingungen ändern, da eine Anbindung im WS-Generator notwendig ist
		if ($this->obj->IsDeletable($db) || $_SESSION["currentUser"]->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_SUPERUSER)
		{
			$options=Array();
			for ($a=1; $a<=TextModule::MAX_NUMBER_OF_TEXT_BLOCKS; $a++)
			{
				$options[]=Array("name" => $a, "value" => $a);
			}
			$this->elements[] = new DropdownElement("numberOfTextBlocks", "Anzahl der Bedingungen", !isset($this->formElementValues["numberOfTextBlocks"]) ? Array() : $this->formElementValues["numberOfTextBlocks"], true, $this->error["numberOfTextBlocks"], $options, false);
		}
		for ($a=0; $a<$this->obj->GetNumberOfTextBlocks(); $a++)
		{
			$this->elements[] = new TextAreaElement("beschreibung_".$a, "Standpunkt Kunde<br />(Bedingung ".($a+1).")", $this->formElementValues["beschreibung_".$a], true, $this->error["beschreibung_".$a], false, true);
			$this->elements[count($this->elements)-1]->SetWidth(400);
		}
		
		// Nur Superuser können festlegen, ob der Eintrag gelöscht werden kann oder nicht
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_SUPERUSER)
		{
			$this->elements[] = new CheckboxElement("protected", "Geschützt<br />Kann nicht gelöscht werden. Land kann nicht geändert werden. Anzahl der Bedingungen kann nur von Superusern geändert werden.", $this->formElementValues["protected"], false, $this->error["protected"]);
		}
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		if ($this->obj->IsDeletable($db) /* || $_SESSION["currentUser"]->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_SUPERUSER*/)
		{
			if (!$this->obj->SetCountry($this->formElementValues["country"])) $this->error["country"] = "Bitte wählen Sie ein Land aus";
		}
		
		$this->obj->SetTitle( $this->formElementValues["name"] );
		$this->obj->SetTextLeft( $this->formElementValues["textLeft"] );
		// Nur Superuser können die Anzahl der Bedingungen ändern, da eine Anbindung im WS-Generator notwendig ist
		if ($this->obj->IsDeletable($db) || $_SESSION["currentUser"]->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_SUPERUSER)
		{
			if ($this->obj->SetNumberOfTextBlocks( (int)$this->formElementValues["numberOfTextBlocks"] )!==true)
			{
				$this->error["numberOfTextBlocks"] = "Bitte geben Sie die Anzahl der Bedingungen ein";
			}
		}
		
		for ($a=0; $a<$this->obj->GetNumberOfTextBlocks(); $a++)
		{
			$this->obj->SetTextRight($a, $this->formElementValues["beschreibung_".$a] );
			if (trim($this->obj->GetTextRight($a))=="")
			{
				$this->error["beschreibung_".$a] = "Bitte geben Sie den Standpunkt des Kunde ein";
			}
		}
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_SUPERUSER)
		{
			$this->obj->SetDeletable($this->formElementValues["protected"]=="on" ? false : true);
		}
		if (count($this->error)==0)
		{
			$returnValue=$this->obj->Store($this->db);
			if ($returnValue===true) return true;
			if ($returnValue==-101) $this->error["name"]="Bitte geben Sie einen Namen ein";
		}
		return false;
	}
	
}
?>