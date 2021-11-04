<?php
/**
 * FormData-Implementierung für die FMS-Schreiben
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class FMSSchreibenFormData extends FormData 
{
	
	/**
	 * Widerspruchobjekt
	 * @param  Widerspruch
	 */
	protected $widerspruch = null;
	
	/**
	 * ProcessStatus 
	 * @var ProcessStatus 
	 */
	protected $process = null;
	
	/**
	 * Konstruktor
	 * @param array $formElementValues Values der Elemente (Post-Vars)
	 * @param DBEntry $object Objekt mit den Daten das Dargestellt werden soll
	 * @param DBManager $db
	 * @param Widerspruch $widerspruch
	 */
	public function FMSSchreibenFormData($formElementValues, $object, $db, $widerspruch)
	{
		$this->widerspruch = $widerspruch;
		if ($this->widerspruch!=null)
		{
			$this->process = $this->widerspruch->GetProcessStatus($db);
		}
		parent::__construct($formElementValues, $object, $db);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject){
		// Icon und Überschrift festlegen
		$this->options["icon"] = "newEntry.png";
		$this->options["icontext"] = "SFM Schreiben hinzufügen ";
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["abschlagszahlungGutschrift"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetAbschlagszahlungGutschrift() );
		}
		// Laden
		if (!isset($this->formElementValues["rsCreationTime"]))
		{
			$this->formElementValues["rsCreationTime"]=date("d.m.Y", time());
			$this->formElementValues["rsCreationTime_clock"]=date("H:i", time());
		}
		if (!isset($this->formElementValues["rsType"]))
		{
			$this->formElementValues["rsType"]=FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_PROTOKOLL;
		}
		// Elemente
		if ($this->widerspruch!=null && isset($_POST["deleteFile_rsFile"]) && $_POST["deleteFile_rsFile"]!="")
		{
			$fileToDelete = new File($this->db);
			if ($fileToDelete->Load((int)$_POST["deleteFile_rsFile"], $this->db))
			{
				$this->widerspruch->RemoveFile($this->db, $fileToDelete);
			}
		}
		$this->elements[] = new FileElement("rsFile", "SFM-Schreiben hochladen", $this->formElementValues["rsFile"], true, $this->error["rsFile"], FM_FILE_SEMANTIC_RSSCHREIBEN, Array(), false, false );
		$this->elements[] = new DateAndTimeElement("rsCreationTime", "Datum und Uhrzeit des SFM-Schreibens", Array("date" => $this->formElementValues["rsCreationTime"], "time" => $this->formElementValues["rsCreationTime_clock"]), true, $this->error["rsCreationTime"]);
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => -1);
		$options[]=Array("name" => "Protokoll", "value" => FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_PROTOKOLL);
		$options[]=Array("name" => "Aktennotiz", "value" => FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_AKTENNOTIZ);
		$options[]=Array("name" => "Sonstiges", "value" => FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_SONSTIGES);
		$this->elements[] = new DropdownElement("rsType", "Art des SFM-Schreibens", $this->formElementValues["rsType"], true, $this->error["rsType"], $options, false);
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => -1);
		$options[]=Array("name" => "SFM Mitarbeiter", "value" => File::FM_FILE_INTERN_YES);
		$options[]=Array("name" => "Alle Benutzer", "value" => File::FM_FILE_INTERN_NO);
		$this->elements[] = new DropdownElement("visibleFor", "SFM-Schreibens sichtbar f&uuml;r", $this->formElementValues["visibleFor"], true, $this->error["visibleFor"], $options, false);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store(){
		// Datum und Uhrzeit des FMS-Schreibens
		$creationTime = DateAndTimeElement::GetTimeStamp($this->formElementValues["rsCreationTime"], $this->formElementValues["rsCreationTime_clock"]);
		if ($creationTime===false)
		{
			$this->error["rsCreationTime"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj und eine gültige Uhrzeit im Format hh:mm ein";
		}
		else
		{
			if ($creationTime>time())
			{
				$this->error["rsCreationTime"]="Der Zeitpunkt darf nicht in der Zukunft liegen";
			}
		}
		// Art des FMS-Schriebens
		if ($this->formElementValues["rsType"]==-1)
		{
			$this->error["rsType"]="Bitte treffen Sie eine Wahl.";
		}
		// FMS-Schreibens sichtbar f&uuml;r
		if ($this->formElementValues["visibleFor"]==-1)
		{
			$this->error["visibleFor"]="Bitte treffen Sie eine Wahl.";
		}
		// Datei nur hochladen, wenn alles andere OK ist!
		if (count($this->error)==0)
		{
			// Datei
			$fileObject=FileElement::GetFileElement($this->db, $_FILES["rsFile"], FM_FILE_SEMANTIC_RSSCHREIBEN);
			if (!is_object($fileObject) || get_class($fileObject)!="File")
			{
				// Nur wenn nicht bereits eine Datei vorhanden ist einen Fehler ausgeben
				if ($fileObject!==-1 || $this->obj->GetPKey()==-1)
				{
					$this->error["rsFile"]=FileElement::GetErrorText($fileObject);
					return false;
				}
			}
			else
			{
				// Es wurde erfolgreich eine Datei hochgeladen
				
				// Wenn dieses FMS-Schrieben bereits in der DB ist dieses jetzt löschen
				if ($this->obj->GetPKey()!=-1)
				{
					$this->obj->DeleteMe($this->db);
				}
				$this->obj = $fileObject;
			}
			// Infos setzen...
			$this->obj->SetCreationTime($creationTime);
			$this->obj->SetIntern($this->formElementValues["visibleFor"]==File::FM_FILE_INTERN_YES ? true : false);
			$this->obj->SetFileSemanticSpecificString($this->formElementValues["rsType"]."_".($this->process!=null ? $this->process->GetCurrentStatus() : "-1"));
			$returnValue = $this->obj->Store($this->db);
			if ($returnValue!==true)
			{
				$this->error["rsFile"]="Systemfehler (".$returnValue.")";
				return false;
			}
			// Datei dem Widerspruch hinzufügen
			$this->widerspruch->AddFile($this->db, $this->obj);
			return true;
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