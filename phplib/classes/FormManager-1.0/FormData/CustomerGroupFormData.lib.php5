<?php
/**
 * FormData-Implementierung für die Customer-Gruppen
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class CustomerGroupFormData extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $UM_GROUP_BASETYPE;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "cGroup.png";
		$this->options["icontext"] = "Kundengruppe ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject ){
			$this->formElementValues["name"]=$this->obj->GetName();
			$this->formElementValues["usergroup"]=$this->obj->GetUserGroup($this->db);
			if( $this->formElementValues["usergroup"]!=null )$this->formElementValues["usergroup"]=$this->formElementValues["usergroup"]->GetPkey();
			else unset($this->formElementValues["usergroup"]);
		}
		$this->elements[] = new TextElement("name", "Name", $this->formElementValues["name"], true, $this->error["name"]);
		// Nicht anzeigen, wenn man unter "Meine Daten" ist
		global $userManager;		
		$userGroups=$userManager->GetGroups("", $_SESSION["currentUser"]->GetGroupBasetype($this->db), 'name', 0, 0, 0);
		$options=Array();
		for($a=0; $a<count($userGroups); $a++){
			if( $userGroups[$a]->GetBaseType()!=UM_GROUP_BASETYPE_KUNDE )continue;
			$options[]=Array("name" => $userGroups[$a]->GetName(), "value" => $userGroups[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("usergroup", "Benutzergruppe", !isset($this->formElementValues["usergroup"]) ? Array() : $this->formElementValues["usergroup"], true, $this->error["usergroup"], $options, false);

	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		$this->obj->SetName( $this->formElementValues["name"] );
		// Gruppenzugehörigkeit seten
		$userGroup = new UserGroup($this->db);
		if( isset($this->formElementValues["usergroup"]) && $userGroup->Load((int)$this->formElementValues["usergroup"], $this->db)===true ){
			if( !$userGroup->GetBaseType()>$_SESSION["currentUser"]->GetGroupBasetype($this->db) ){
				unset($userGroup);
			}
		}else{
			unset($userGroup);
		}
		$this->obj->SetUserGroup( $userGroup );
		$returnValue=$this->obj->Store($this->db);
		if( $returnValue===true )return true;
		if( $returnValue==-101 )$this->error["name"]="Bitte geben Sie einen Namen ein";
		if( $returnValue==-102 )$this->error["usergroup"]="Bitte wählen Sie die zugehörige Benutzergruppe aus";
		if( $returnValue==-103 )$this->error["name"]="Es exisitiert bereits eine Kundengruppe mit diesem Namen";
		return false;
	}
	
}
?>