<?php
/**
 * FormData-Implementierung für die Gruppen
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class AbrechnungsjahrFormData extends FormData
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "calendar.png";
		$this->options["icontext"] = "Abrechnungsjahr ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";

		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$contract=$this->obj->GetContract();
			if( $contract!=null ){
				$this->formElementValues["contract"]=$contract->GetPKey();
				$cShop=$contract->GetShop();
				if( $cShop!=null ){
					$this->formElementValues["cShop"]=$cShop->GetPKey();
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
			}
			$this->formElementValues["year"]=$this->obj->GetJahr();
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
		$this->elements[] = new DropdownElement("group", "Gruppe", $this->formElementValues["group"], true, $this->error["group"], $options, false);
		// Firma
		$options=$emptyOptions;
		$currentGroup=$customerManager->GetGroupByID($_SESSION["currentUser"], $this->formElementValues["group"]);
		if($currentGroup!=null){
			$objects=$currentGroup->GetCompanys($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("company", "Firma", $this->formElementValues["company"], true, $this->error["company"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=1&param01='+$('group').options[$('group').selectedIndex].value+'", "$('group').onchange=function(){%REQUESTCALL%;};", "company") );
		// Standort
		$options=$emptyOptions;
		$currentCompany=$customerManager->GetCompanyByID($_SESSION["currentUser"], $this->formElementValues["company"]);
		if($currentCompany!=null){
			$objects=$currentCompany->GetLocations($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("location", "Standort", $this->formElementValues["location"], true, $this->error["location"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=2&param01='+$('company').options[$('company').selectedIndex].value+'", "$('company').onchange=function(){%REQUESTCALL%;};", "location"));
		// Läden
		$options=$emptyOptions;
		$currentLocation=$customerManager->GetLocationByID($_SESSION["currentUser"], $this->formElementValues["location"]);
		if($currentLocation!=null){
			$objects=$currentLocation->GetShops($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("cShop", "Laden", $this->formElementValues["cShop"], true, $this->error["cShop"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=3&param01='+$('location').options[$('location').selectedIndex].value+'", "$('location').onchange=function(){%REQUESTCALL%;};", "cShop"));
		// Verträge
		$options=$emptyOptions;
		$currentShop=$customerManager->GetShopByID($_SESSION["currentUser"], $this->formElementValues["cShop"]);
		if($currentShop!=null){
			$objects=$currentShop->GetContracts($this->db);
			for( $a=0; $a<count($objects); $a++)
			{
				$options[]=Array("name" => "Vertrag ".($objects[$a]->GetLifeOfLeaseString()=='' ? ($a+1) : $objects[$a]->GetLifeOfLeaseString() ), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("contract", "Vertrag", $this->formElementValues["contract"], true, $this->error["contract"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=4&param01='+$('cShop').options[$('cShop').selectedIndex].value+'", "$('cShop').onchange=function(){%REQUESTCALL%;};", "contract"));
		// Elemente anlegen
		$this->elements[] = new TextElement("year", "Jahr", $this->formElementValues["year"], true, $this->error["year"]);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error = array();
		$year=TextElement::GetInteger($this->formElementValues["year"]);
		if ($year===false || $year<1900 || $year>date('Y')+10)
		{
			$this->error["year"]="Bitte geben Sie das Abrechnungsjahr im Format jjjj ein";
		}
		else
		{
			$this->obj->SetJahr((int)$year);
		}
		// Set contract
		$rsKostenartManager = new RSKostenartManager($this->db);
		$contract = $rsKostenartManager->GetContractByID($_SESSION["currentUser"], $this->formElementValues["contract"]);
		if ($contract==null)
		{
			$this->error["contract"]="Bitte wählen Sie den zugehörigen Vertrag aus";
		}
		else
		{
			$this->obj->SetContract($contract);
		}

		if (count($this->error)>0) return false;
		$returnValue=$this->obj->Store($this->db);
		if ($returnValue===true) return true;
		if ($returnValue==-101) $this->error["year"]="Bitte geben Sie den zugehörigen Vertrag an";
		if ($returnValue==-102) $this->error["year"]="Das AbrechnungsJahr für den übergeordneten Vertrag existiert bereits";
		return false;
	}
	
}
?>