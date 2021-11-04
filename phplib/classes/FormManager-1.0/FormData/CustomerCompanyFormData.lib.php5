<?php
/**
 * FormData-Implementierung für die Customer-Firmen
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class CustomerCompanyFormData extends FormData 
{
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		// Icon und Überschrift festlegen
		$this->options["icon"] = "cCompany.png";
		$this->options["icontext"] = "Firma ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject ){
			$this->formElementValues["name"]=$this->obj->GetName();
			$this->formElementValues["street"]=$this->obj->GetStreet();
			$this->formElementValues["zip"]=$this->obj->GetZIP();
			$this->formElementValues["city"]=$this->obj->GetCity();
			$this->formElementValues["country"]=$this->obj->GetCountry();
			$this->formElementValues["kontoNr"]=$this->obj->GetKontoNr();
			$this->formElementValues["blz"]=$this->obj->GetBlz();
			$this->formElementValues["bankName"]=$this->obj->GetBankName();
			$this->formElementValues["iban"]=$this->obj->GetIban();
			$this->formElementValues["vat"]=$this->obj->GetVat();
			$this->formElementValues["cgroup"]=$this->obj->GetGroup();
			if( $this->formElementValues["cgroup"]!=null )$this->formElementValues["cgroup"]=$this->formElementValues["cgroup"]->GetPkey();
			else unset($this->formElementValues["cgroup"]);
			$this->formElementValues["anschreibenVorlageBetreff"] = $this->obj->GetAnschreibenVorlageBetreff()==null ? StandardTextManager::STM_WSANSCHREIBEN_SUBJECT : $this->obj->GetAnschreibenVorlageBetreff()->GetPKey();
			$this->formElementValues["anschreibenVorlageText"] = $this->obj->GetAnschreibenVorlageText()==null ? StandardTextManager::STM_WSANSCHREIBEN : $this->obj->GetAnschreibenVorlageText()->GetPKey();
			
		}
		$this->elements[] = new TextElement("name", "Name", $this->formElementValues["name"], true, $this->error["name"]);
		$this->elements[] = new TextElement("street", "Straße", $this->formElementValues["street"], false, $this->error["street"]);
		$this->elements[] = new TextElement("zip", "PLZ", $this->formElementValues["zip"], false, $this->error["zip"]);
		$this->elements[] = new TextElement("city", "Ort", $this->formElementValues["city"], false, $this->error["city"]);
		$this->elements[] = new TextElement("country", "Land", $this->formElementValues["country"], false, $this->error["country"]);
		$this->elements[] = new TextElement("kontoNr", "Kontonummer", $this->formElementValues["kontoNr"], false, $this->error["kontoNr"]);
		$this->elements[] = new TextElement("blz", "Bankleitzahl", $this->formElementValues["blz"], false, $this->error["blz"]);
		$this->elements[] = new TextElement("bankName", "Bankname", $this->formElementValues["bankName"], false, $this->error["bankName"]);
		$this->elements[] = new TextElement("iban", "IBAN", $this->formElementValues["iban"], false, $this->error["iban"]);
		$this->elements[] = new TextElement("vat", "VAT", $this->formElementValues["vat"], false, $this->error["vat"]);
		
		global $customerManager;		
		$cGroups=$customerManager->GetGroups($_SESSION["currentUser"], '', 'name', 0, 0, 0);
		$options=Array(Array("name" => "Bitte wählen...", "value" => ""));
		for($a=0; $a<count($cGroups); $a++)
		{
			$options[]=Array("name" => $cGroups[$a]->GetName(), "value" => $cGroups[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("cgroup", "Kundengruppe", !isset($this->formElementValues["cgroup"]) ? Array() : $this->formElementValues["cgroup"], true, $this->error["cgroup"], $options, false);
		// Anschreiben
		$languages = CustomerManager::GetLanguagesISO639List($this->db);
		foreach ($languages as $language) 
		{
			$anschreiben = $this->obj->GetAnschreiben($language);
			$this->elements[] = new FileElement("anschreiben_".$language, "Anschreiben (".$language.")", $this->formElementValues["anschreiben_".$language], true, $this->error["anschreiben_".$language], FM_FILE_SEMANTIC_WSANSCHREIBEN, $anschreiben==null ? Array() : Array($anschreiben), false );
		}


		if( isset($_POST["deleteFile_konditionsUndFristenliste"]) && $_POST["deleteFile_konditionsUndFristenliste"]!="" ){

			$fileToDelete=new File($this->db);
			if( $fileToDelete->Load((int)$_POST["deleteFile_konditionsUndFristenliste"], $this->db)===true ){
				$fileToDelete->DeleteMe($this->db);
				$this->obj->SetKonditionsUndFristenliste(null);
			}
		}

		$konditionsUndFristenliste = $this->obj->GetKonditionsUndFristenliste();
		$this->elements[] = new FileElement("konditionsUndFristenliste", "Konditions- und Fristenliste", $this->formElementValues["konditionsUndFristenliste"], false, $this->error["konditionsUndFristenliste"], FM_FILE_SEMANTIC_KONDITIONSUNDFRISTENLISTE, $konditionsUndFristenliste==null ? Array() : Array($konditionsUndFristenliste), true );

		$standardTextManager = new StandardTextManager($this->db);
		$standardTexts = $standardTextManager->GetStandardText('', '', 0, 0, 0, '', StandardText::ST_TYPE_TEMPLATE);
		$options=Array();
		for($a=0; $a<count($standardTexts); $a++)
		{
			// Subject only can be single line entrys
			if ($standardTexts[$a]->IsMultiLineText()) continue;
			$options[]=Array("name" => $standardTexts[$a]->GetName().($standardTexts[$a]->GetPKey()==StandardTextManager::STM_WSANSCHREIBEN_SUBJECT ? ' (Standard)' : ''), "value" => $standardTexts[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("anschreibenVorlageBetreff", "Anschreiben Betreff", !isset($this->formElementValues["anschreibenVorlageBetreff"]) ? Array() : $this->formElementValues["anschreibenVorlageBetreff"], true, $this->error["anschreibenVorlageBetreff"], $options, false);

		$options=Array();
		for($a=0; $a<count($standardTexts); $a++)
		{
			// E-Mail-Text only can be multi line entrys
			if (!$standardTexts[$a]->IsMultiLineText()) continue;
			$options[]=Array("name" => $standardTexts[$a]->GetName().($standardTexts[$a]->GetPKey()==StandardTextManager::STM_WSANSCHREIBEN ? ' (Standard)' : ''), "value" => $standardTexts[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("anschreibenVorlageText", "Anschreiben Text", !isset($this->formElementValues["anschreibenVorlageText"]) ? Array() : $this->formElementValues["anschreibenVorlageText"], true, $this->error["anschreibenVorlageText"], $options, false);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		$this->obj->SetName( $this->formElementValues["name"] );
		$this->obj->SetStreet( $this->formElementValues["street"] );
		$this->obj->SetZIP( $this->formElementValues["zip"] );
		$this->obj->SetCity( $this->formElementValues["city"] );
		$this->obj->SetCountry( $this->formElementValues["country"] );
		$this->obj->SetKontoNr( $this->formElementValues["kontoNr"] );
		$this->obj->SetBlz( $this->formElementValues["blz"] );
		$this->obj->SetBankName( $this->formElementValues["bankName"] );
		$this->obj->SetIban( $this->formElementValues["iban"] );
		$this->obj->SetVat( $this->formElementValues["vat"] );
		$this->obj->SetAnschreibenVorlageBetreff( StandardTextManager::GetStandardTextById($this->db, (int)$this->formElementValues["anschreibenVorlageBetreff"]) );
		$this->obj->SetAnschreibenVorlageText( StandardTextManager::GetStandardTextById($this->db, (int)$this->formElementValues["anschreibenVorlageText"]) );
		// Gruppenzugehörigkeit seten
		$cGroup = new CGroup($this->db);
		if( !isset($this->formElementValues["cgroup"]) || $this->formElementValues["cgroup"]=="" || $cGroup->Load((int)$this->formElementValues["cgroup"], $this->db)!==true ){
			unset($cGroup);
		}
		$this->obj->SetGroup( $cGroup );

		// Konditions- und Fristenliste
		$fileObject=FileElement::GetFileElement($this->db, $_FILES["konditionsUndFristenliste"], FM_FILE_SEMANTIC_KONDITIONSUNDFRISTENLISTE);
		if( is_object($fileObject) && get_class($fileObject)=="File" ){

			$oldFile = $this->obj->GetKonditionsUndFristenliste();
			$this->obj->SetKonditionsUndFristenliste($fileObject);
			// Alte Datei löschen wenn vorhanden
			if ($oldFile!=null) $oldFile->DeleteMe($this->db);
		}

		$returnValue=$this->obj->Store($this->db);

		if( $returnValue===true ){
			// Anschreiben
			$languages = CustomerManager::GetLanguagesISO639List($this->db);
			foreach ($languages as $language) 
			{
				$fileObject = FileElement::GetFileElement($this->db, $_FILES["anschreiben_".$language], FM_FILE_SEMANTIC_WSANSCHREIBEN);
				if (!is_object($fileObject) || get_class($fileObject)!="File")
				{
					// Nur wenn nicht bereits eine Datei vorhanden ist einen Fehler ausgeben
					if ($fileObject!==-1 || $this->obj->GetAnschreiben($language)==null)
					{
						$this->error["anschreiben_".$language] = FileElement::GetErrorText($fileObject);
						return false;
					}
				}
				else
				{
					$oldFile = $this->obj->GetAnschreiben($language);
					$this->obj->SetAnschreiben($language, $fileObject);
					$returnValue = $this->obj->Store($this->db);
					if ($returnValue!==true)
					{
						$this->error["cShop"]="Systemfehler (".$returnValue.")";
						return false;
					}
					// Alte Datei löschen wenn vorhanden
					if ($oldFile!=null) $oldFile->DeleteMe($this->db);
					return true;
				}
			}
			return true;
		}
		if( $returnValue==-101 )$this->error["name"]="Bitte geben Sie einen Namen ein";
		if( $returnValue==-102 )$this->error["cgroup"]="Bitte wählen Sie die zugehörige Kundengruppe aus";
		if( $returnValue==-103 )$this->error["name"]="Es exisitiert bereits eine Firma mit diesem Namen";
		return false;
	}
	
}
?>