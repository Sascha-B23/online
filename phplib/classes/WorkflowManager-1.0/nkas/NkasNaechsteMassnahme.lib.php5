<?php
/**
 * Status "Nächste Maßnahme festlegen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasNaechsteMassnahme extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 36 Stunden
		$this->obj->SetDeadline(time()+60*60*36);
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		// Rückweisungsbegründung für Abbruch anzeigen wenn vorhanden und wir von Status 6 kommen
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		/*@var $widerspruch Widerspruch*/
		if ($this->obj->GetLastStatus()==6 && $widerspruch!=null &&  $widerspruch->GetLastAbbruchProtokoll($this->db)!=null)
		{
			$abbruchProtokoll=$widerspruch->GetLastAbbruchProtokoll($this->db);
			$protokoll=Array();
			$protokoll[0]["date"]=$abbruchProtokoll->GetDatumAbbruch();
			$protokoll[0]["username"]=$abbruchProtokoll->GetUser()==null ? "-" : $abbruchProtokoll->GetUser()->GetUserName();
			$protokoll[0]["title"]="Prozess abgebrochen";
			$protokoll[0]["text"]=str_replace("\n", "<br/>", $abbruchProtokoll->GetBegruendung());
			$protokoll[1]["date"]=$abbruchProtokoll->GetDatumAblehnung();
			$protokoll[1]["username"]=$abbruchProtokoll->GetUserRelease()==null ? "-" : $abbruchProtokoll->GetUserRelease()->GetUserName();
			$protokoll[1]["title"]="Abbruch abgelehnt";
			$protokoll[1]["text"]=str_replace("\n", "<br/>", $abbruchProtokoll->GetAblehnungsbegruendung());
			ob_start();
			include("abbruchProtokoll.inc.php5");
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		$gutschriftNachtrag = false;
		if ($widerspruch!=null)
		{
			$antwortSchreiben = $widerspruch->GetLastAntwortschreiben($this->db);
			if ($antwortSchreiben!=null)
			{
				$files = $antwortSchreiben->GetFiles($this->db);
				foreach ($files as $file)
				{
					if ($file->GetFileSemantic()==FM_FILE_SEMANTIC_ABRECHNUNGSKORREKTURGUTSCHRIFT || $file->GetFileSemantic()==FM_FILE_SEMANTIC_NACHTRAG)
					{
						$gutschriftNachtrag = true;
						break;
					}
				}
			}
		}
		// Wurde eine Frist überschritten und wir sind deshalb hier gelandet?
		if( $this->obj->GetAutoJumpFromStatus()!=-1 ){
			$protokoll=Array();
			$protokoll[0]["username"]="System";
			switch( $this->obj->GetAutoJumpFromStatus() ){
				case 12:
				case 13:
					$protokoll[0]["title"]="Rückstellung beendet";
					$protokoll[0]["date"]=$this->obj->GetRueckstellungBis();
					$protokoll[0]["text"]="Das Datum ".date("d.m.Y", $this->obj->GetRueckstellungBis())." für die Rückstellung wurde überschritten. Aus diesem Grund wurde die Aufgabe automatisch in diesen Status überführt.";
					break;
				case 17:
					$protokoll[0]["title"]="Frist für den Eingang des Eigentümer/Verwalter/Anwalt-Schreibens wurde überschritten";
					$protokoll[0]["date"]=$this->obj->GetDeadline();
					$protokoll[0]["text"]="Die Frist ".date("d.m.Y", $this->obj->GetDeadline())." für den Eingang des Eigentümer/Verwalter/Anwalt-Schreibens wurde überschritten. Aus diesem Grund wurde die Aufgabe automatisch in diesen Status überführt.";
					break;
				case 20:
					$protokoll[0]["title"]="Frist für die Freigabe des Widerspruchs/Folgewiderspruchs/Protokolls durch den Kunden wurde überschritten";
					$protokoll[0]["date"]=$this->obj->GetDeadline();
					$protokoll[0]["text"]="Die Frist ".date("d.m.Y", $this->obj->GetDeadline())." für die Freigabe des Widerspruchs/Folgewiderspruchs/Protokolls durch den Kunden wurde überschritten. Aus diesem Grund wurde die Aufgabe automatisch in diesen Status überführt.";
					break;				
				case 23:
					$protokoll[0]["title"]="Frist für den Eingang der Abrechnungskorrektur/Gutschrift/Nachtrag wurde überschritten";
					$protokoll[0]["date"]=$this->obj->GetDeadline();
					$protokoll[0]["text"]="Die Frist ".date("d.m.Y", $this->obj->GetDeadline())." für den Eingang der Abrechnungskorrektur/Gutschrift/Nachtrag wurde überschritten. Aus diesem Grund wurde die Aufgabe automatisch in diesen Status überführt.";
					break;
			}
			ob_start();
			include("abbruchProtokoll.inc.php5");
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		
		// Entscheidung
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => "");
		// Nachfolger dieses Prozess auflisten
		$curStatusData = WorkflowManager::GetProzessStatusForStatusID(9);
		if ($curStatusData!==false)
		{
			for ($a=0; $a<count($curStatusData["nextStatusIDs"]); $a++)
			{
				if ($curStatusData["nextStatusIDs"][$a]==24 && !$gutschriftNachtrag) continue;
				$nextStatus = WorkflowManager::GetProzessStatusForStatusID($curStatusData["nextStatusIDs"][$a]);	
				$options[] = Array("name" => $nextStatus["name"], "value" => $a+1);
			}
		}
		$this->elements[] = new DropdownElement("nextAction", "Nächste Maßnahme", !isset($this->formElementValues["nextAction"]) ? Array() : $this->formElementValues["nextAction"], false, $this->error["nextAction"], $options, false);
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
		if ($gotoNextStatus && $this->formElementValues["nextAction"]=="") $this->error["nextAction"]="Um die Aufgabe abschließen zu können, müssen Sie die nächste Maßnahme auswählen.";
		if (count($this->error)==0) return true;
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
		$curStatusData = WorkflowManager::GetProzessStatusForStatusID(9);
		if ($curStatusData!==false)
		{
			if ($curStatusData["nextStatusIDs"][(int)$this->formElementValues["nextAction"]-1]==5) $this->obj->SetJumpBackStatus(9);
		}
		// In ausgewählte Maßnahme springen
		return ((int)$this->formElementValues["nextAction"])-1;
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