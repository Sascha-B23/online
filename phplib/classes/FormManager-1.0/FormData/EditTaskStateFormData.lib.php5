<?php
/**
 * FormData-Implementierung zum Ändern des Prozess-Bearbeitungsstatus
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class EditTaskStateFormData extends FormData 
{
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 * @param boolean $edit
	 * @param Object $loadFromObject
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $CM_LOCATION_TYPES;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "activeTask.png";
		$this->options["icontext"] = "Prozess-Bearbeitungsstatus ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject )
		{
			$this->formElementValues["currentArchiveStatus"] = $this->obj->GetArchiveStatus();
		}
		// Info
		ob_start();
		echo "Jahr: ".($this->obj->GetAbrechnungsJahr()==null ? "-" : $this->obj->GetAbrechnungsJahr()->GetJahr())."<br />";
		// Namem von Standort und Laden bei Prozess-Workflows ermitteln
		if( $this->obj->GetStatusTyp()==WM_WORKFLOWSTATUS_TYPE_PROCESS )
		{
			$cShop=$this->obj->GetShop();
			if( $cShop!=null && is_a($cShop, "CShop") )
			{
				$cLocation=$cShop->GetLocation();
				if( $cLocation!=null && is_a($cLocation, "CLocation") )
				{
					echo "Standort: ".$cLocation->GetName()."<br />";
				}
				echo "Laden: ".$cShop->GetName();
			}
		}
		$html = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($html, "Prozessinformation&#160;&#160;&#160;&#160;&#160;");
		// Status
		$options = Array();
		$options[] = Array("name" => "Archiviert", "value" => Schedule::ARCHIVE_STATUS_ARCHIVED);
		$options[] = Array("name" => "Aktueller Prozess (noch auf Stand zu bringen)", "value" => Schedule::ARCHIVE_STATUS_UPDATEREQUIRED);
		$options[] = Array("name" => "Aktueller Prozess (bereits auf Stand)", "value" => Schedule::ARCHIVE_STATUS_UPTODATE);
		$this->elements[] = new DropdownElement("currentArchiveStatus", "Prozess-Bearbeitungsstatus", !isset($this->formElementValues["currentArchiveStatus"]) ? Array() : $this->formElementValues["currentArchiveStatus"], false, $this->error["currentArchiveStatus"], $options, false);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 * @return boolean Success
	 */
	public function Store()
	{
		$this->error=array();
		if ($this->obj->SetArchiveStatus( (int)$this->formElementValues["currentArchiveStatus"])!==true )
		{
			$this->error["currentArchiveStatus"] = "Der ausgewählte Prozess-Bearbeitungsstatus konnte nicht gesetzt werden";
		}
		if( count($this->error)>0 )return false;
		$returnValue=$this->obj->Store($this->db);
		if( $returnValue===true )return true;
		$this->error["name"]="Unerwarteter Fehler (Code: ".$returnValue.")";
		return false;
	}
	
}
?>