<?php
/**
 * FormData-Implementierung für Dateien
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class EditFileEntryFormData extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $DOMAIN_HTTP_ROOT, $SHARED_HTTP_ROOT;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "assignTask.png";
		$this->options["icontext"] = "Dokument bearbeiten";
		if ($loadFromObject)
		{
			$this->formElementValues["dateAndTime"]=$this->obj->GetCreationTime()==0 ? "" : date("d.m.Y", $this->obj->GetCreationTime());
			$this->formElementValues["dateAndTime_clock"]=$this->obj->GetCreationTime()==0 ? "" : date("H:i", $this->obj->GetCreationTime());
			$this->formElementValues["description"]= $this->obj->getDescription();
		}
		// UI-Elemente
		$this->elements[] = new DateAndTimeElement("dateAndTime", "Upload-Zeitpunkt", Array("date" => $this->formElementValues["dateAndTime"], "time" => $this->formElementValues["dateAndTime_clock"]), true, $this->error["dateAndTime"]);
		$this->elements[] = new FileElement("uploadFile", "Dokument", $this->formElementValues["uploadFile"], true, $this->error["uploadFile"], $this->obj->GetFileSemantic(), Array($this->obj), false);
		$this->elements[] = new TextElement("description", "Kommentar", $this->formElementValues["description"], false, $this->error["description"]);
		
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		global $SHARED_FILE_SYSTEM_ROOT;
		$this->errors = Array();
		// Datum
		if (trim($this->formElementValues["dateAndTime"])!="" || trim($this->formElementValues["dateAndTime_clock"])!="")
		{
			$tempValue = DateAndTimeElement::GetTimeStamp($this->formElementValues["dateAndTime"], $this->formElementValues["dateAndTime_clock"]);
			if  ($tempValue===false)
			{
				$this->error["dateAndTime"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj und eine gültige Uhrzeit im Format hh:mm ein";
			}
			else 
			{
				if ($tempValue>time())
				{
					$this->error["dateAndTime"]="Der Zeitpunkt darf nicht in der Zukunft liegen";
				}
				else
				{
					$this->obj->SetCreationTime($tempValue);
				}
			}
		}
		else
		{
			$this->error["dateAndTime"] = "Bitte geben Sie den Upload-Zeitpunkt an.";
		}
		// Datei
		$fileObject = FileElement::GetFileElement($this->db, $_FILES["uploadFile"], $this->obj->GetFileSemantic());
		if (!is_object($fileObject) || get_class($fileObject)!="File")
		{
			if ($fileObject!=-1)
			{
				$this->error["uploadFile"] = FileElement::GetErrorText($fileObject);
			}
		}
		else
		{
			// Datei ersetzen
			if (count($this->error)==0)
			{
				$this->obj->SetFileName($fileObject->GetFileName());
				$this->obj->SetFileType($fileObject->GetFileType());
				$this->obj->SetOriginalFileName($fileObject->GetOriginalFileName());
				$this->obj->SetDescription($fileObject->GetDescription());
				$newFilePath = $fileObject->GetDocumentPath();
				$oldFilePath = $this->obj->GetDocumentPath();
				if (!copy(FM_FILE_ROOT.$newFilePath, FM_FILE_ROOT.$oldFilePath) )
				{
					$this->error["uploadFile"] = "Datei konnte nicht ersetzt werden (Fehler beim Kopieren der Datei)";
				}
			}
			// Delete temporary file object
			$fileObject->DeleteMe($this->db);
		}

		// Kommentar
		if (strlen($this->formElementValues["description"])>50)
		{
			$this->error["description"] = "Bitte kürzen Sie Ihren Kommentar auf maximal 50 Zeichen.";
		}
		else
		{
			$this->obj->setDescription($this->formElementValues["description"]);
		}
		
		if (count($this->error)==0)
		{
			$returnValue = $this->obj->Store($this->db);
			return $returnValue;
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