<?php
/**
 * FormData-Implementierung für die Gruppen
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class GroupFormData extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $UM_GROUP_BASETYPE;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "userGroup.png";
		$this->options["icontext"] = "Gruppe ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["name"]=$this->obj->GetName();
			$this->formElementValues["basetype"]=$this->obj->GetBaseType();
		}
		if ($this->formElementValues["basetype"]==UM_GROUP_BASETYPE_NONE) $this->formElementValues["basetype"]=UM_GROUP_BASETYPE_KUNDE;
		// Elemente anlegen
		$this->elements[] = new TextElement("name", "Name", $this->formElementValues["name"], true, $this->error["name"]);
		// nur Superuser können den Gruppentyp ändern
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_SUPERUSER)
		{
			$options=Array();
			$head = array_keys($UM_GROUP_BASETYPE);
			$data = array_values($UM_GROUP_BASETYPE);
			for ($a=0; $a<count($head); $a++)
			{
				$options[] = Array("name" => $data[$a], "value" => $head[$a]);
			}
			$this->elements[] = new DropdownElement("basetype", "Typ", $this->formElementValues["basetype"], true, $this->error["basetype"], $options);
		}
		else
		{
			$this->elements[] = new CustomHTMLElement($UM_GROUP_BASETYPE[$this->formElementValues["basetype"]], "Typ");
		}
		
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		if ($this->obj->GetPKey()!=-1 && $_SESSION["currentUser"]->GetGroupBasetype($this->db) < UM_GROUP_BASETYPE_ADMINISTRATOR)
		{
			$this->error["misc"][] = "Keine Schreibberechtigung";
			return false;
		}
		$this->error = array();
		$this->obj->SetName($this->formElementValues["name"]);
		// Gruppentyp nur ändern, wenn angemeldeter Benutzer ein Superuser ist oder der Eintrag neu ist
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_SUPERUSER || $this->obj->GetPKey()==-1)
		{
			$this->obj->SetBaseType(isset($this->formElementValues["basetype"]) ? $this->formElementValues["basetype"] : UM_GROUP_BASETYPE_KUNDE);
		}
		if( count($this->error)>0 )return false;
		$returnValue=$this->obj->Store($this->db);
		if( $returnValue===true )return true;
		if( $returnValue==-101 )$this->error["name"]="Bitte geben Sie einen Namen ein";
		if( $returnValue==-102 )$this->error["name"]="Es exisitiert bereits eine Benutzergruppe mit diesem Namen";
		return false;
	}
	
}
?>