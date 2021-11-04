<?php
/**
 * Status "Eigentümer-/Verwalter-/Anwalt-Schreiben hochladen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasSchreibenHochladen extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: Wie in Status 16 festgelegt wurde
		$this->obj->SetDeadline($this->obj->GetRueckstellungBis());
		$this->obj->SetRueckstellungBis(0);
		$this->obj->Store($this->db);
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			// Antwortschreiben erzeugen
			$awSchreiben=new Antwortschreiben($this->db);
			$awSchreiben->SetDatum(0);
			$awSchreiben->SetWiderspruch($widerspruch);
			$awSchreiben->Store($this->db);
		}
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		// Aktuelles Antwortschrieben holen
		$awSchreiben=null;
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			$awSchreiben = $widerspruch->GetLastAntwortschreiben($this->db);
		}
		if ($loadFromObject && $awSchreiben!=null)
		{
			$this->formElementValues["eingangsDatum"]=$awSchreiben->GetDatum()==0 ? date("d.m.Y", time()) : date("d.m.Y", $awSchreiben->GetDatum());
		}
		$this->elements[] = new DateElement("eingangsDatum", "Eingangsdatum", $this->formElementValues["eingangsDatum"], true, $this->error["eingangsDatum"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		if ($awSchreiben!=null && isset($_POST["deleteFile_antwortschreiben"]) && $_POST["deleteFile_antwortschreiben"]!="")
		{
			$fileToDelete = new File($this->db);
			if ($fileToDelete->Load((int)$_POST["deleteFile_antwortschreiben"], $this->db))
			{
				$awSchreiben->RemoveFile($this->db, $fileToDelete);
			}
		}
		$this->elements[] = new FileElement("antwortschreiben", "Antwortschreiben", $this->formElementValues["antwortschreiben"], true, $this->error["antwortschreiben"], FM_FILE_SEMANTIC_UNKNOWN, $awSchreiben!=null ? $awSchreiben->GetFiles($this->db) : Array());
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$this->elements[] = new DateAndTimeElement("uploadFileTime", "Datum und Uhrzeit der Datei überschreiben", Array("date" => $this->formElementValues["uploadFileTime"], "time" => $this->formElementValues["uploadFileTime_clock"]), false, $this->error["uploadFileTime"]);
		}
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		$awSchreiben=null;
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			$awSchreiben=$widerspruch->GetLastAntwortschreiben($this->db);
		}
		if ($awSchreiben==null)
		{
			$this->error["misc"][]="Das zugehörige Antwortschrieben konnte nicht gefunden werden.";
			return false;
		}
		// Datum
		if (trim($this->formElementValues["eingangsDatum"])!="")
		{
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["eingangsDatum"]);
			if ($tempValue===false) $this->error["eingangsDatum"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $awSchreiben->SetDatum($tempValue);
		}
		else
		{
			$awSchreiben->SetDatum(0);
		}
		// Datei
		$fileObject=FileElement::GetFileElement($this->db, $_FILES["antwortschreiben"], FM_FILE_SEMANTIC_TEMP);
		if (!is_object($fileObject) || get_class($fileObject)!="File")
		{
			if ($fileObject!=-1)
			{
				$this->error["antwortschreiben"]=FileElement::GetErrorText($fileObject);
			}
		}
		else
		{
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE && (trim($this->formElementValues["uploadFileTime"])!="" || trim($this->formElementValues["uploadFileTime_clock"])!=""))
			{
				// Datum und Uhrzeit der Datei 
				$uploadFileTime = DateAndTimeElement::GetTimeStamp($this->formElementValues["uploadFileTime"], $this->formElementValues["uploadFileTime_clock"]);
				if ($uploadFileTime===false)
				{
					$this->error["uploadFileTime"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj und eine gültige Uhrzeit im Format hh:mm ein";
				}
				else
				{
					if ($uploadFileTime>time())
					{
						$this->error["uploadFileTime"]="Der Zeitpunkt darf nicht in der Zukunft liegen";
					}
					else
					{
						$fileObject->SetCreationTime($uploadFileTime);
					}
				}
			}
			// Datei hochladen...
			if( count($this->error)>0 || !$awSchreiben->AddFile($this->db, $fileObject) )
			{
				// Fehler: Datei wieder löschen
				$fileObject->DeleteMe($this->db);
				$this->error["antwortschreiben"]="Datei konnte dem Antwortschreiben nicht hinzugefügt werden.";
				return false;
			}
		}
		// Bezeichnung
		if (count($this->error)==0)
		{
			$returnValue=$awSchreiben->Store($this->db);
			if ($returnValue===true)
			{
				// Prüfen, ob in nächsten Status gesprungen werden kann
				unset($this->formElementValues["uploadFileTime"]);
				unset($this->formElementValues["uploadFileTime_clock"]);
				if( !$gotoNextStatus )return true;
				if( $awSchreiben->GetDatum()==0 )$this->error["eingangsDatum"]="Um die Aufgabe abschließen zu können, müssen Sie das Eingangsdatum eingeben.";
				// Wenn noch keine Datei hochgeladen wurde und auch jetzt keine hochgeladen wird, Fehler ausgeben
				if( $awSchreiben->GetFileCount($this->db)==0 )$this->error["antwortschreiben"]="Um die Aufgabe abschließen zu können, müssen Sie das Antwortschrieben hochladen.";
				if( count($this->error)==0 )return true;
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
		return 0;
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
	
	/**
	 * Return if the Status can be aboarded by user
	 * @return boolean
	 */
	public function CanBeAboarded()
	{
		return true;
	}
	
}
?>