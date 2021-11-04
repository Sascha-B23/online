<?php
/**
 * FormData-Implementierung für die Customer-Standorte
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class CustomerLocationFormData extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $CM_LOCATION_TYPES;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "cLocation.png";
		$this->options["icontext"] = "Standort ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["name"]=$this->obj->GetName();
			$this->formElementValues["type"]=$this->obj->GetLocationType();
			$this->formElementValues["strasse"]=$this->obj->GetStreet();
			$this->formElementValues["city_zip"]=$this->obj->GetZIP();
			$this->formElementValues["city_city"]=$this->obj->GetCity();
			$this->formElementValues["beschreibung"]=$this->obj->GetObjectDescription();		
			$this->formElementValues["country"]=$this->obj->GetCountry();		
		}
		// Location in a ProcessStatusGroup?
		$locationInGroup = WorkflowManager::IsLocationInProcessStatusGroup($this->db, $this->obj);
		if ($loadFromObject || $locationInGroup)
		{
			$cCompany=$this->obj->GetCompany();
			if ($cCompany!=null)
			{
				$this->formElementValues["ccompany"]=$cCompany->GetPKey();
				$cGroup=$cCompany->GetGroup();
				if ($cGroup!=null)
				{
					$this->formElementValues["group"]=$cGroup->GetPKey();
				}
			}
		}
		
		
		$this->elements[] = new TextElement("name", CLocation::GetAttributeName($this->languageManager, 'name'), $this->formElementValues["name"], true, $this->error["name"]);

		global $customerManager;
		global $SHARED_HTTP_ROOT;
		$emptyOptions=Array();
		$emptyOptions[]=Array("name" => "Bitte wählen...", "value" => "");
		
		// Gruppe
		$options=$emptyOptions;
		$objects=$customerManager->GetGroups($_SESSION["currentUser"], '', 'name', 0, 0, 0);
		for ($a=0; $a<count($objects); $a++)
		{
			$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("group", "Gruppe* ".($locationInGroup ? "<sup>1)</sup>" : ""), $this->formElementValues["group"], false, $this->error["group"], $options, false, null, Array(), $locationInGroup);
		
		// Firma
		$options=$emptyOptions;
		$currentGroup=$customerManager->GetGroupByID($_SESSION["currentUser"], $this->formElementValues["group"]);
		if($currentGroup!=null){
			$objects=$currentGroup->GetCompanys($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("ccompany", "Firma* ".($locationInGroup ? "<sup>1)</sup>" : ""), $this->formElementValues["ccompany"], false, $this->error["ccompany"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=1&param01='+$('group').options[$('group').selectedIndex].value+'", "$('group').onchange=function(){%REQUESTCALL%;};", "ccompany"), Array(), $locationInGroup );

		$options=Array(Array("name" => "Bitte wählen...", "value" => CM_LOCATION_TYPE_NONE));
		for( $a=0; $a<count($CM_LOCATION_TYPES); $a++ ){
			if( $CM_LOCATION_TYPES[$a]["id"]==CM_LOCATION_TYPE_NONE )continue;
			$options[]=Array("name" => $CM_LOCATION_TYPES[$a]["name"], "value" => $CM_LOCATION_TYPES[$a]["id"]);
		}		
		$this->elements[] = new DropdownElement("type", CLocation::GetAttributeName($this->languageManager, 'locationType'), !isset($this->formElementValues["type"]) ? Array() : $this->formElementValues["type"], false, $this->error["type"], $options, false);
		$this->elements[] = new TextElement("strasse", "Straße", $this->formElementValues["strasse"], false, $this->error["strasse"]);
		$this->elements[] = new ZIPCityElement("city", "PLZ / ".CLocation::GetAttributeName($this->languageManager, 'city'), Array("zip" => $this->formElementValues["city_zip"], "city" => $this->formElementValues["city_city"]), false, $this->error["city"]);
		// Land
		global $customerManager;
		$countries = $customerManager->GetCountries();
		$options = $emptyOptions;
		foreach($countries as $country)
		{
			$options[] = Array("name" => $country->GetName()." (".$country->GetIso3166().")", "value" => $country->GetIso3166());
		}
		$this->elements[] = new DropdownElement("country", CLocation::GetAttributeName($this->languageManager, 'country'), $this->formElementValues["country"], true, $this->error["country"], $options, false);
		
		$this->elements[] = new TextAreaElement("beschreibung", "Objektbeschreibung", $this->formElementValues["beschreibung"], false, $this->error["beschreibung"]);

		if ($locationInGroup)
		{
			ob_start();
			?>
			<div style="border-top: solid 1px #cccccc;">
				<sup>1)</sup> Die Gruppe und Firma kann nicht geändert werden, da sich dieser<br />
				Standort in einem Paket befindet.<br /><br />
			</div>
			<?
			$html = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($html, "&#160;");
		}
		
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		$this->obj->SetName( $this->formElementValues["name"] );
		$this->obj->SetLocationType( $this->formElementValues["type"] );
		$this->obj->SetStreet( $this->formElementValues["strasse"] );
		$this->obj->SetZIP( $this->formElementValues["city_zip"] );
		$this->obj->SetCity( $this->formElementValues["city_city"] );
		$this->obj->SetObjectDescription( $this->formElementValues["beschreibung"] );
		$this->obj->SetCountry( $this->formElementValues["country"] );
		
		// Gruppenzugehörigkeit seten
		$locationInGroup = WorkflowManager::IsLocationInProcessStatusGroup($this->db, $this->obj);
		if (!$locationInGroup)
		{
			$cCompany = new CCompany($this->db);
			if( !isset($this->formElementValues["ccompany"]) || $this->formElementValues["ccompany"]=="" || $cCompany->Load((int)$this->formElementValues["ccompany"], $this->db)!==true ){
				unset($cCompany);
			}
			if (!$this->obj->SetCompany($this->db, $cCompany)) $this->error["ccompany"]="Dieser Standort wird in einem Paket verwendet und kann aus<br/>diesem Grund keiner anderen Gruppe oder Firma zugeordnet werden.";
		}
		if (count($this->error)>0) return false;
		$returnValue=$this->obj->Store($this->db);
		if( $returnValue===true )return true;
		if( $returnValue==-101 )$this->error["name"]="Bitte geben Sie einen Namen ein";
		if( $returnValue==-102 )$this->error["ccompany"]="Bitte wählen Sie die zugehörige Firma aus";
		if( $returnValue==-103 )$this->error["name"]="Es exisitiert bereits ein Standort mit diesem Namen";
		return false;
	}
	
}
?>