<?php
/**
 * Status "Vertrag auf Vollständigkeit prüfen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasVertragPruefen extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 1 Tage ab Auftragsdatum
		$this->obj->SetDeadline($this->obj->GetAuftragsdatumAbrechnung()+60*60*24*1);
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		$contract = $this->obj->GetContract();
		// Daten aus Objekt laden?
		if( $loadFromObject )
		{
            $this->formElementValues["comment"] = $this->obj->GetCustomerComment();
			if( $contract!=null )
			{

			}
		}
		// UI-Elemente
		ob_start();
		?>
			<strong>Bitte überprüfen Sie die hinterlegten Mietvertragsdokumente auf Vollständigkeit und laden Sie ggf. fehlenden Dokumente hoch.</strong><br>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		
		if ($contract!=null)
		{
			$this->formElementValues["nurVertragsauszuegeVorhanden"] = $contract->GetNurAuszuegeVorhanden();
			$this->formElementValues["mietvertragsdokumenteVollstaendig"] = $contract->GetMietvertragsdokumenteVollstaendig();
			
			if (isset($_POST["deleteFile_mietvertrag"]) && $_POST["deleteFile_mietvertrag"]!="")
			{
				$fileToDelete=new File($this->db);
				if ($fileToDelete->Load((int)$_POST["deleteFile_mietvertrag"], $this->db))
				{
					$contract->RemoveFile($this->db, $fileToDelete);
				}
			}
			if (isset($_POST["deleteFile_mietvertraganlage"]) && $_POST["deleteFile_mietvertraganlage"]!="")
			{
				$fileToDelete=new File($this->db);
				if ($fileToDelete->Load((int)$_POST["deleteFile_mietvertraganlage"], $this->db))
				{
					$contract->RemoveFile($this->db, $fileToDelete);
				}
			}
			if (isset($_POST["deleteFile_mietvertragnachtrag"]) && $_POST["deleteFile_mietvertragnachtrag"]!="")
			{
				$fileToDelete=new File($this->db);
				if ($fileToDelete->Load((int)$_POST["deleteFile_mietvertragnachtrag"], $this->db))
				{
					$contract->RemoveFile($this->db, $fileToDelete);
				}
			}
			$this->elements[] = new FileElement("mietvertrag", "Mietvertrag", $this->formElementValues["mietvertrag"], false, $this->error["mietvertrag"], FM_FILE_SEMANTIC_MIETVERTRAG, $contract->GetFiles($this->db, FM_FILE_SEMANTIC_MIETVERTRAG), true, true, true);
			$this->elements[] = new FileElement("mietvertragnachtrag", "Mietvertrag-Nachtrag", $this->formElementValues["mietvertragnachtrag"], false, $this->error["mietvertragnachtrag"], FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG, $contract->GetFiles($this->db, FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG), true, true, true );
			$this->elements[] = new FileElement("mietvertraganlage", "Mietvertrag-Anlage", $this->formElementValues["mietvertraganlage"], false, $this->error["mietvertraganlage"], FM_FILE_SEMANTIC_MIETVERTRAGANLAGE, $contract->GetFiles($this->db, FM_FILE_SEMANTIC_MIETVERTRAGANLAGE), true, true, true );
			$options=Array();
			$options[]=Array("name" => "Ja", "value" => Contract::CONTRACT_YES);
			$options[]=Array("name" => "Nein", "value" => Contract::CONTRACT_NO);
			$this->elements[] = new RadioButtonElement("nurVertragsauszuegeVorhanden", "<br />Nur Vertragsauszüge vorhanden", $this->formElementValues["nurVertragsauszuegeVorhanden"], true, $this->error["nurVertragsauszuegeVorhanden"], $options);
		}
		// Vertrag vollständig?
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => Contract::MVC_UNKNOWN);
		$options[]=Array("name" => "Keine neuen Unterlagen hinzugefügt", "value" => Contract::MVC_NO_ADDITIONAL_FILES);
		$options[]=Array("name" => "Neue Unterlagen hinuzgefügt", "value" => Contract::MVC_NEW_FILES_ADDED);
		$options[]=Array("name" => "Neue Unterlagen vorhanden aber nicht hinzugefügt", "value" => Contract::MVC_NEW_FILES_AVAILABLE_NOT_ADDED);
		$this->elements[] = new DropdownElement("mietvertragsdokumenteVollstaendig", "Mietvertragsdokumente vollständig", $this->formElementValues["mietvertragsdokumenteVollstaendig"], false, $this->error["mietvertragsdokumenteVollstaendig"], $options);

        $this->elements[] = new TextAreaElement("comment", "Kommentar", $this->formElementValues["comment"]);
	//	$this->elements[] = new CheckboxElement("contractUpToDate", "<br />Die Mietvertragsdokumente sind vollständig", $this->formElementValues["contractUpToDate"], true, $this->error["contractUpToDate"] );		
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
        // Kommentar
        $this->obj->SetCustomerComment($this->formElementValues["comment"]);
        $returnValue=$this->obj->Store($this->db);
        if ($returnValue!==true)
        {
            $this->error["misc"][]="Systemfehler (".$returnValue.")";
            return false;
        }

		$contract = $this->obj->GetContract();
		if ($contract!=null)
		{
			$returnValue=true;
			// Mietvertrag
			$fileObject=FileElement::GetFileElement($this->db, $_FILES["mietvertrag"], FM_FILE_SEMANTIC_MIETVERTRAG);
			if( !is_object($fileObject) || get_class($fileObject)!="File" ){
				if( $fileObject!==-1 ){
					$this->error["mietvertrag"]=FileElement::GetErrorText($fileObject);
					$returnValue=false;
				}
			}else{
				$contract->AddFile($this->db, $fileObject);
			}
			// Mietvertraganlage
			$fileObject=FileElement::GetFileElement($this->db, $_FILES["mietvertraganlage"], FM_FILE_SEMANTIC_MIETVERTRAGANLAGE);
			if( !is_object($fileObject) || get_class($fileObject)!="File" ){
				if( $fileObject!==-1 ){
					$this->error["mietvertraganlage"]=FileElement::GetErrorText($fileObject);
					$returnValue=false;
				}
			}else{
				$contract->AddFile($this->db, $fileObject);
			}
			// Mietvertragnachtrag
			$fileObject=FileElement::GetFileElement($this->db, $_FILES["mietvertragnachtrag"], FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG);
			if( !is_object($fileObject) || get_class($fileObject)!="File" ){
				if( $fileObject!==-1 ){
					$this->error["mietvertragnachtrag"]=FileElement::GetErrorText($fileObject);
					$returnValue=false;
				}
			}else{
				$contract->AddFile($this->db, $fileObject);
			}
			if ((int)$this->formElementValues["nurVertragsauszuegeVorhanden"]>0)
			{
				$contract->SetNurAuszuegeVorhanden((int)$this->formElementValues["nurVertragsauszuegeVorhanden"]);
			}
			$contract->SetMietvertragsdokumenteVollstaendig((int)$this->formElementValues["mietvertragsdokumenteVollstaendig"]);
			if($returnValue)
			{
				// Wenn neue Daten zum Vertrag hinzugefügt wurden oder diese noch fehlen...
				if ($contract->GetMietvertragsdokumenteVollstaendig()==Contract::MVC_NEW_FILES_ADDED || $contract->GetMietvertragsdokumenteVollstaendig()==Contract::MVC_NEW_FILES_AVAILABLE_NOT_ADDED)
				{
					// ... muss der Vertrag neu erfasst werden
					$contract->SetVertragErfasst(false);
				}
				$returnValue=$contract->Store($this->db);
				if ($returnValue!==true)
				{
					$this->error["misc"][]="Systemfehler (".$returnValue.")";
					return false;
				}
				// Vertrag vollständig?
				if ($gotoNextStatus && $contract->GetNurAuszuegeVorhanden()<=0) $this->error["nurVertragsauszuegeVorhanden"]="Bitte geben Sie an, ob nur Auszüge vom Vertrag (Ja) oder der gesamte Vertrag hochgeladen wurde (Nein)";
				if ($gotoNextStatus && $this->formElementValues["mietvertragsdokumenteVollstaendig"]=="") $this->error["mietvertragsdokumenteVollstaendig"]="<br />Bitte bestätigen Sie, dass die Mietvertragsdokumente vollständig sind.";
				if ($gotoNextStatus && $contract->GetFileCount($this->db, FM_FILE_SEMANTIC_MIETVERTRAG)==0 && $contract->GetFileCount($this->db, FM_FILE_SEMANTIC_MIETVERTRAGANLAGE)==0 && $contract->GetFileCount($this->db, FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG)==0 )
				{
					$this->error["misc"][] = "Sie müssen mindestens einen Mietvertrag, eine Mietvertragsanlage oder einen Mietvertragsnachtrag hinterlegen";
				}
				if (count($this->error)==0)
				{
					return true;
				}
			}
		}
		else
		{
			$this->error["misc"][] = "Die Vertragsdaten konnten nicht abgerufen werden";
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
		// Wenn der Vertrag bereits erfasst wurde, die Erfassung überspringen
		$contract=$this->obj->GetContract();
		if ($contract!=null) return $contract->GetVertragErfasst() ? 1 : 0;
		return 0;
	}
		
	/**
	 * return if this status of the current process can be edited when in a group
	 * @return boolean
	 */
	public function IsEditableInGroup()
	{
		// only none group is editable
		return ($this->IsProcessGroup() ? false : true);
	}
	
}
?>