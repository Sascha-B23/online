<?php
/**
 * FormData-Implementierung für die Verträge im Prozess
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class AddContractFormData extends FormData 
{
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		global $CM_LOCATION_TYPES;
		global $userManager;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "contract.png";
		$this->options["icontext"] = "Vertrag ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject ){
			$this->formElementValues["cShop"]=$this->obj->GetShop();
			if( $this->formElementValues["cShop"]!=null ){
				$cShop=$this->formElementValues["cShop"];
				$this->formElementValues["cShop"]=$this->formElementValues["cShop"]->GetPkey();
				$cLocation=$cShop->GetLocation();
				if( $cLocation!=null ){
					$this->formElementValues["location"]=$cLocation->GetPKey();
					$cCompany=$cLocation->GetCompany();
					if( $cCompany!=null ){
						$this->formElementValues["company"]=$cCompany->GetPKey();
						$cGroup=$cCompany->GetGroup();
						if( $cGroup!=null ){
							$this->formElementValues["group"]=$cGroup->GetPKey();
						}
					}
				}
			}
			else unset($this->formElementValues["cShop"]);
		}
		global $customerManager;
		$emptyOptions=Array();
		$emptyOptions[]=Array("name" => "Bitte wählen...", "value" => "");
		// Gruppe
		$options=$emptyOptions;
		$objects=$customerManager->GetGroups($_SESSION["currentUser"], '', 'name', 0, 0, 0);
		for( $a=0; $a<count($objects); $a++){
			$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("group", "Gruppe", $this->formElementValues["group"], true, $this->error["group"], $options, false, null, Array(), true);
		// Firma
		$options=$emptyOptions;
		$currentGroup=$customerManager->GetGroupByID($_SESSION["currentUser"], $this->formElementValues["group"]);
		if($currentGroup!=null){
			$objects=$currentGroup->GetCompanys($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("company", "Firma", $this->formElementValues["company"], true, $this->error["company"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=1&param01='+$('group').options[$('group').selectedIndex].value+'", "$('group').onchange=function(){%REQUESTCALL%;};", "company"),Array(), true );
		// Standort
		$options=$emptyOptions;
		$currentCompany=$customerManager->GetCompanyByID($_SESSION["currentUser"], $this->formElementValues["company"]);
		if($currentCompany!=null){
			$objects=$currentCompany->GetLocations($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("location", "Standort", $this->formElementValues["location"], true, $this->error["location"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=2&param01='+$('company').options[$('company').selectedIndex].value+'", "$('company').onchange=function(){%REQUESTCALL%;};", "location"),Array(), true);
		// Läden
		$options=$emptyOptions;
		$currentLocation=$customerManager->GetLocationByID($_SESSION["currentUser"], $this->formElementValues["location"]);
		if($currentLocation!=null){
			$objects=$currentLocation->GetShops($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("cShop", "Laden", $this->formElementValues["cShop"], true, $this->error["cShop"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=3&param01='+$('location').options[$('location').selectedIndex].value+'", "$('location').onchange=function(){%REQUESTCALL%;};", "cShop"),Array(), true);
		// Dateien
		$canDeleteFile = false;
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			$canDeleteFile = true;
			// Löschen können nur FMS-Mitarbeiter
			if (isset($_POST["deleteFile_mietvertrag"]) && $_POST["deleteFile_mietvertrag"]!="" )
			{
				$fileToDelete=new File($this->db);
				if ($fileToDelete->Load((int)$_POST["deleteFile_mietvertrag"], $this->db))
				{
					$this->obj->RemoveFile($this->db, $fileToDelete);
				}
			}
			if (isset($_POST["deleteFile_mietvertraganlage"]) && $_POST["deleteFile_mietvertraganlage"]!="")
			{
				$fileToDelete=new File($this->db);
				if ($fileToDelete->Load((int)$_POST["deleteFile_mietvertraganlage"], $this->db))
				{
					$this->obj->RemoveFile($this->db, $fileToDelete);
				}
			}
			if (isset($_POST["deleteFile_mietvertragnachtrag"]) && $_POST["deleteFile_mietvertragnachtrag"]!="")
			{
				$fileToDelete=new File($this->db);
				if ($fileToDelete->Load((int)$_POST["deleteFile_mietvertragnachtrag"], $this->db))
				{
					$this->obj->RemoveFile($this->db, $fileToDelete);
				}
			}
		}
		$this->elements[] = new FileElement("mietvertrag", "Mietvertrag", $this->formElementValues["mietvertrag"], false, $this->error["mietvertrag"], FM_FILE_SEMANTIC_MIETVERTRAG, $this->obj->GetFiles($this->db, FM_FILE_SEMANTIC_MIETVERTRAG), $canDeleteFile);
		$this->elements[] = new FileElement("mietvertragnachtrag", "Mietvertrag-Nachtrag", $this->formElementValues["mietvertragnachtrag"], false, $this->error["mietvertragnachtrag"], FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG, $this->obj->GetFiles($this->db, FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG), $canDeleteFile);
		$this->elements[] = new FileElement("mietvertraganlage", "Mietvertrag-Anlage", $this->formElementValues["mietvertraganlage"], false, $this->error["mietvertraganlage"], FM_FILE_SEMANTIC_MIETVERTRAGANLAGE, $this->obj->GetFiles($this->db, FM_FILE_SEMANTIC_MIETVERTRAGANLAGE), $canDeleteFile);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		// Laden seten
		$cShop = new CShop($this->db);
		if( !isset($this->formElementValues["cShop"]) || $this->formElementValues["cShop"]=="" || $cShop->Load((int)$this->formElementValues["cShop"], $this->db)!==true ){
			unset($cShop);
			$this->error["cShop"]="Bitte wählen Sie den zugehörigen Laden aus";
		}
		$this->obj->SetShop($this->db, $cShop);
		if( count($this->error)==0 ){
			$returnValue=$this->obj->Store($this->db);
			if( $returnValue===true ){
				// Mietvertrag
				$fileObject=FileElement::GetFileElement($this->db, $_FILES["mietvertrag"], FM_FILE_SEMANTIC_MIETVERTRAG);
				if( !is_object($fileObject) || get_class($fileObject)!="File" ){
					if( $fileObject!==-1 ){
						$this->error["mietvertrag"]=FileElement::GetErrorText($fileObject);
						$returnValue=false;
					}
				}else{
					$this->obj->AddFile($this->db, $fileObject);
				}
				// Mietvertraganlage
				$fileObject=FileElement::GetFileElement($this->db, $_FILES["mietvertraganlage"], FM_FILE_SEMANTIC_MIETVERTRAGANLAGE);
				if( !is_object($fileObject) || get_class($fileObject)!="File" ){
					if( $fileObject!==-1 ){
						$this->error["mietvertraganlage"]=FileElement::GetErrorText($fileObject);
						$returnValue=false;
					}
				}else{
					$this->obj->AddFile($this->db, $fileObject);
				}
				// Mietvertragnachtrag
				$fileObject=FileElement::GetFileElement($this->db, $_FILES["mietvertragnachtrag"], FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG);
				if( !is_object($fileObject) || get_class($fileObject)!="File" ){
					if( $fileObject!==-1 ){
						$this->error["mietvertragnachtrag"]=FileElement::GetErrorText($fileObject);
						$returnValue=false;
					}
				}else{
					$this->obj->AddFile($this->db, $fileObject);
				}
				return $returnValue;
			}else{
				$this->error["cShop"]="Systemfehler (".$returnValue.")";
			}
		}
		return false;
	}
	
}
?>