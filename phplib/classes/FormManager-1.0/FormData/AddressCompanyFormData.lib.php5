<?php
/**
 * FormData-Implementierung für die Adressen
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class AddressCompanyFormData extends FormData 
{
	
	protected $readOnly=false;
	
	/**
	 *AddressManager
	 * @var AddressManager 
	 */
	protected $addressManager = null;
	
	/**
	 * Konstruktor
	 * @param array $formElementValues Values der Elemente (Post-Vars)
	 * @param DBEntry $object Objekt mit den Daten das Dargestellt werden soll
	 * @param DBManager $db
	 * @param bool $readOnly
	 */
	public function AddressCompanyFormData($formElementValues, $object, $db, $readOnly=false)
	{
		global $addressManager;
		$this->addressManager = $addressManager;
		$this->readOnly = $readOnly;
		parent::__construct($formElementValues, $object, $db);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		// Icon und Überschrift festlegen
		$this->options["icon"] = "addressCompany.png";
		$this->options["icontext"] = "Firma ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		$this->options["show_options_save"] = !$this->readOnly;
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["name"]=$this->obj->GetName();
			$this->formElementValues["strasse"]=$this->obj->GetStreet();
			$this->formElementValues["city_zip"]=$this->obj->GetZIP();
			$this->formElementValues["city_city"]=$this->obj->GetCity();
			$this->formElementValues["land"]=$this->obj->GetCountry();
			$this->formElementValues["email"]=$this->obj->GetEMail();
			$this->formElementValues["telefon"]=$this->obj->GetPhone();
			$this->formElementValues["fax"]=$this->obj->GetFax();
			$this->formElementValues["website"]=$this->obj->GetWebsite();
			$this->formElementValues["addressGroup"]=($this->obj->GetAddressGroup()==null ? -1 : $this->obj->GetAddressGroup()->GetPKey());
		}
		// Elemente anlegen	
		$options=Array();
		$options[] = Array("name" => "Bitte wählen...", "value" => -1);
		$addressGroups = $this->addressManager->GetAddressGroupData();
		for($a=0; $a<count($addressGroups); $a++)
		{
			$options[] = Array("name" => $addressGroups[$a]->GetName(), "value" => $addressGroups[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("addressGroup", AddressGroup::GetAttributeName($this->languageManager, 'name'), $this->formElementValues["addressGroup"], true, $this->error["addressGroup"], $options, false, null, $this->readOnly ? Array() : Array(0 => Array("width" => 25, "pic" => "pics/gui/adressgruppen_new.png", "help" => "Neuen Gruppe anlegen", "href" => "javascript:CreateNewAddressGroup();")), $this->readOnly);
		$this->elements[] = new TextElement("name", AddressCompany::GetAttributeName($this->languageManager, 'name'), $this->formElementValues["name"], true, $this->error["name"], $this->readOnly);
		$this->elements[] = new TextElement("strasse", AddressCompany::GetAttributeName($this->languageManager, 'street'), $this->formElementValues["strasse"], false, $this->error["strasse"], $this->readOnly);
		$this->elements[] = new ZIPCityElement("city", AddressCompany::GetAttributeName($this->languageManager, 'zip')." / ".AddressCompany::GetAttributeName($this->languageManager, 'city'), Array("zip" => $this->formElementValues["city_zip"], "city" => $this->formElementValues["city_city"]), false, $this->error["city"], $this->readOnly);
		$this->elements[] = new TextElement("land", AddressCompany::GetAttributeName($this->languageManager, 'country'), $this->formElementValues["land"], false, $this->error["land"], $this->readOnly);		
		$this->elements[] = new TextElement("email", AddressCompany::GetAttributeName($this->languageManager, 'email'), $this->formElementValues["email"], false, $this->error["email"], $this->readOnly );
		$this->elements[] = new TextElement("telefon", AddressCompany::GetAttributeName($this->languageManager, 'phone'), $this->formElementValues["telefon"], false, $this->error["telefon"], $this->readOnly);
		$this->elements[] = new TextElement("fax", AddressCompany::GetAttributeName($this->languageManager, 'fax'), $this->formElementValues["fax"], false, $this->error["fax"], $this->readOnly);
		$this->elements[] = new TextElement("website", AddressCompany::GetAttributeName($this->languageManager, 'website'), $this->formElementValues["website"], false, $this->error["website"], $this->readOnly);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		if ($this->readOnly) return true;
		$this->obj->SetName($this->formElementValues["name"]);
		$retValue = $this->obj->SetEMail( $this->formElementValues["email"] );
		if( $retValue!==true )
		{
			if( $retValue==-2 )$this->error["email"]="Die eingegebene E-Mail-Adresse ist ungültig";
		}
		$this->obj->SetStreet($this->formElementValues["strasse"]);
		$this->obj->SetZIP($this->formElementValues["city_zip"]);
		$this->obj->SetCity($this->formElementValues["city_city"]);
		$this->obj->SetCountry($this->formElementValues["land"]);
		$this->obj->SetPhone($this->formElementValues["telefon"]);
		$this->obj->SetFax($this->formElementValues["fax"]);
		$this->obj->SetWebsite($this->formElementValues["website"]);
		$addressGroup = null;
		if ($this->formElementValues["addressGroup"]!=-1)
		{
			$addressGroup = AddressManager::GetAddressGroupByPkey($this->db, $this->formElementValues["addressGroup"]);
			if ($addressGroup==null)
			{
				$this->error["addressGroup"]="Die Gruppe konnte nicht gefunden werden";
			}
		}
		else
		{
			$this->error["addressGroup"]="Bitte wählen Sie eine Gruppe aus";
		}
		$this->obj->SetAddressGroup($addressGroup);
		if (count($this->error)>0) return false;
		$returnValue = $this->obj->Store($this->db);
		if ($returnValue===true) return true;
		if ($returnValue==-101) $this->error["name"]="Bitte geben Sie einen Namen ein";
		else $this->error["misc"][]="Ein Unerwarteter Fehler ist aufgetreten (Fehler: ".$returnValue.")";
		return false;
	}
	
	/**
	 * This function can be used to output HTML data after the form
	 */
	public function PostPrint()
	{
		global $DOMAIN_HTTP_ROOT;
		?>
		<script type="text/javascript">
			<!--
				function CreateNewAddressGroup(type)
				{
					var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/createAddressGroup.php5?<?=SID;?>&type='+type, '_createAddressGroup', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				
				function SetAddressGroup(type, name, addressGroupId)
				{
					$('addressGroup').options[$('addressGroup').options.length] = new Option(name, addressGroupId, false, true);
				}
			-->
		</script>
		<?
	}
	
}
?>