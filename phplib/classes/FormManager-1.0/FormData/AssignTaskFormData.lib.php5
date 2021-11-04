<?php
/**
 * FormData-Implementierung für "Aufgabe zuweisen"
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class AssignTaskFormData extends FormData 
{
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "plannedTask.png";
		$this->options["icontext"] = "Aufgabe zuweisen";
		if ($loadFromObject)
		{
			$user=$this->obj->GetZuweisungUser();
			if ($user!=null) $this->formElementValues["user"]=$user->GetPKey();
			else $this->formElementValues["user"]=-1;
		}
		// Zuständiger FMS-Mitarbeiter
		global $userManager;
		$users=$userManager->GetUsers($_SESSION["currentUser"], "", "email", 0, 0, 0, UM_GROUP_BASETYPE_RSMITARBEITER);
		
		$options=Array(Array("name" => "Bitte wählen...", "value" => -1));
		for($a=0; $a<count($users); $a++){
			if( $users[$a]->GetPKey()==$_SESSION["currentUser"]->GetPKey() )continue;
			$userName=$users[$a]->GetUserName()." (".$users[$a]->GetShortName().")";
			$options[]=Array("name" => $userName, "value" => $users[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("user", "Benutzer", !isset($this->formElementValues["user"]) ? Array() : $this->formElementValues["user"], true, $this->error["user"], $options, false);
		$this->elements[] = new CheckboxElement("zuweisungLoeschen", "Zuweisung löschen", $this->formElementValues["zuweisungLoeschen"], false, "");
		$this->elements[] = new BlankElement();
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		// Datum
		if ($this->formElementValues["zuweisungLoeschen"]=="on")
		{
			$this->formElementValues["user"]=-2;
		}
		if ($this->formElementValues["user"]==-2)
		{
			$this->obj->SetZuweisungUser(null);
		}
		else
		{
			$userTemp=new User($this->db);
			if ($userTemp->Load((int)$this->formElementValues["user"], $this->db)!==true)
			{
				$this->error["user"]="Bitte wählen Sie den Benutzer aus, an den Sie die Aufgabe übertragen möchten.";
			}
			else
			{
				$this->obj->SetZuweisungUser($userTemp);
			}
		}
		// Speichern
		if( count($this->error)==0 )
		{
			$returnValue=$this->obj->Store($this->db);
			if ($returnValue===true) return true;
			$this->error["misc"][]="Systemfehler (".$returnValue.")";
		}
		return false;
	}

	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben nachdem das Formular ausgegeben wurde
	 */
	public function PostPrint()
	{
	}
	
}
?>