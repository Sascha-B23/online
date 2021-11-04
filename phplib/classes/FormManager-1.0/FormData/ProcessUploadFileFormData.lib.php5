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
class ProcessUploadFileFormData extends FormData {
		
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		// Icon und Überschrift festlegen
		$this->options["icon"] = "uploadFile.png";
		$this->options["icontext"] = "Datei hochladen";
		if ($loadFromObject)
		{
            $this->formElementValues["comment"] = $this->obj->GetCustomerFileUploadComment();
		}
		if (isset($_POST["deleteFile_new_file"]) && $_POST["deleteFile_new_file"]!="")
		{
			$fileToDelete=new File($this->db);
			if ($fileToDelete->Load((int)$_POST["deleteFile_new_file"], $this->db)===true)
			{
				$this->obj->RemoveFile($this->db, $fileToDelete);
			}
		}
		$this->elements[] = new FileElement("new_file", "Datei", $this->formElementValues["new_file"], false, $this->error["new_file"], FM_FILE_SEMANTIC_NEWCUSTOMERFILE, $this->obj->GetFiles($this->db, FM_FILE_SEMANTIC_NEWCUSTOMERFILE) );
        $this->elements[] = new TextAreaElement("comment", "Kommentar", $this->formElementValues["comment"]);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		// Speichern
		$returnValue = true;
		$fileObject = FileElement::GetFileElement($this->db, $_FILES["new_file"], FM_FILE_SEMANTIC_NEWCUSTOMERFILE);
		if (!is_object($fileObject) || get_class($fileObject)!="File")
		{
			if ($fileObject!==-1)
			{
				$this->error["new_file"] = FileElement::GetErrorText($fileObject);
				$returnValue=false;
			}
		}
		else
		{
			$this->obj->AddFile($this->db, $fileObject);

		}
        $this->obj->SetCustomerFileUploadComment($this->formElementValues["comment"]);
        $this->obj->Store($this->db);

		return $returnValue;
	}

	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben nachdem das Formular ausgegeben wurde
	 */
	public function PostPrint(){}
	
}
?>