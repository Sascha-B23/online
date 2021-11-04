<?php
/**
 * FormData-Implementierung für einen Prozess (Admin-Ansicht)
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class AdminProzessFormData extends FormData 
{
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		// Icon und Überschrift festlegen
		$this->options["icon"] = "passiveTask.png";
		$this->options["icontext"] = "Prozess ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["currentStatus"]=$this->obj->GetCurrentStatus();
		}
		// Info
		ob_start();
		$processStatusGroup = $this->obj->GetProcessStatusGroup($this->db);
		echo ProcessStatus::GetAttributeName($this->languageManager, 'processStatusId').": ".WorkflowManager::GetProcessStatusId($this->obj)."<br />";
		echo ProcessStatusGroup::GetAttributeName($this->languageManager, 'name').": ".($processStatusGroup==null ? "-" : $processStatusGroup->GetName())."<br />";
		echo AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr').": ".($this->obj->GetAbrechnungsJahr()==null ? "-" : $this->obj->GetAbrechnungsJahr()->GetJahr())."<br />";
		echo CLocation::GetAttributeName($this->languageManager, 'name').": ".$this->obj->GetLocationName()."<br />";
		echo CShop::GetAttributeName($this->languageManager, 'name').": ".$this->obj->GetShopName()."<br />";
		$html = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($html, "Prozessinformation&#160;&#160;&#160;&#160;&#160;");
		
		if ($processStatusGroup==null)
		{
			// Status
			$options=Array();
			$options[]=Array("name" => "Bitte wählen...", "value" => "");
			// Nachfolger dieses Prozess auflisten
			global $processStatusConfig;
			if( $curStatusData!==false ){
				for( $a=0; $a<count($processStatusConfig); $a++){
					$options[]=Array("name" => $processStatusConfig[$a]["name"], "value" => $processStatusConfig[$a]["ID"]);
				}
			}
			$this->elements[] = new DropdownElement("currentStatus", WorkflowStatus::GetAttributeName($this->languageManager, 'currentStatus'), !isset($this->formElementValues["currentStatus"]) ? Array() : $this->formElementValues["currentStatus"], false, $this->error["currentStatus"], $options, false);

			// Warnung
			ob_start();
			echo "<strong><font color='#ff0000'>WARNUNG: Die Überführung in einen anderen Status kann zu Inkonsistenzen im Prozesses führen!</font></strong>";
			$html = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($html, "");
		}
		else
		{
			// Prozesse in einem Paket
			ob_start();
			echo "<strong><font color='#ff0000'>HINWEIS: Die Überführung in einen anderen Status ist nicht möglich, da dieser Prozess einem Paket zugeordnet ist!</font></strong>";
			$html = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($html, "");
		}
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$processStatusGroup = $this->obj->GetProcessStatusGroup($this->db);
		if ($processStatusGroup==null)
		{
			$this->error=array();
			if ($this->obj->GetCurrentStatus()==(int)$this->formElementValues["currentStatus"]) return true;
			$oldName = $this->obj->GetCurrentStatusName($_SESSION["currentUser"], $this->db); 
			if ($this->obj->SetCurrentStatus( (int)$this->formElementValues["currentStatus"])!==true )
			{
				$this->error["currentStatus"] = "Der ausgewählte Status konnte nicht gesetzt werden";
			}
			if( count($this->error)>0 )return false;
			LoggingManager::GetInstance()->Log(new LoggingProcessStatus(LoggingProcessStatus::TYPE_MANUAL_STATUS_CHANGE, $this->obj->GetCurrentStatusName($_SESSION["currentUser"], $this->db), $oldName, serialize($this->obj->GetDependencyFileDescription())));
			$returnValue=$this->obj->Store($this->db);
			if( $returnValue===true )return true;
			$this->error["name"]="Unerwarteter Fehler (Code: ".$returnValue.")";
		}
		return false;
	}
	
}
?>