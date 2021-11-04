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
class AddressDataFormData extends FormData 
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
	public function AddressDataFormData($formElementValues, $object, $db, $readOnly=false)
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
		global $AM_ADDRESSDATA_TYPE;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "address.png";
		$this->options["icontext"] = "Adresse ";
		$this->options["icontext"] .= $this->readOnly ? "anzeigen" : ($edit ? "bearbeiten" : "anlegen");
		$this->options["show_options_save"] = !$this->readOnly;
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["anrede"]=$this->obj->GetTitle() + 1;
			$this->formElementValues["anrede2"]=$this->obj->GetTitle2();
			$this->formElementValues["name"]=$this->obj->GetName();
			$this->formElementValues["vorname"]=$this->obj->GetFirstName();
			$this->formElementValues["funktion"]=$this->obj->GetRole();
			$this->formElementValues["strasse"]=$this->obj->GetStreet(true);
			$this->formElementValues["city_zip"]=$this->obj->GetZIP(true);
			$this->formElementValues["city_city"]=$this->obj->GetCity(true);
			$this->formElementValues["email"]=$this->obj->GetEMail();
			$this->formElementValues["telefon"]=$this->obj->GetPhone();
			$this->formElementValues["mobil"]=$this->obj->GetMobile();
			$this->formElementValues["fax"]=$this->obj->GetFax();
			$this->formElementValues["addressCompany"]=($this->obj->GetAddressCompany()==null ? -1 : $this->obj->GetAddressCompany()->GetPKey());
			$this->formElementValues["type"]=Array();
			$typeTemp=$this->obj->GetType();
			$keys = array_keys($AM_ADDRESSDATA_TYPE);
			for($a=0; $a<count($keys); $a++)
			{
				if( $keys[$a]==AM_ADDRESSDATA_TYPE_NONE )continue;
				if( ($typeTemp & $keys[$a])==$keys[$a] )
				{
					$this->formElementValues["type"][]=$keys[$a];
				}
			}				
		}
		// Elemente anlegen	
		$options=Array();
		$options[] = Array("name" => "Bitte wählen...", "value" => -1);
		$addressCompanies = $this->addressManager->GetAddressCompany("", AddressCompany::TABLE_NAME.".name");
		for($a=0; $a<count($addressCompanies); $a++)
		{
			$options[]=Array("name" => $addressCompanies[$a]->GetName(), "value" => $addressCompanies[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("addressCompany", AddressCompany::GetAttributeName($this->languageManager, 'name'), $this->formElementValues["addressCompany"], true, $this->error["addressCompany"], $options, false, null, $this->readOnly ? Array() : Array(0 => Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany();")), $this->readOnly);

		$optionsAnrede=Array(0 => Array("name" => "Herr", "value" => 1), Array("name" => "Frau", "value" => 2));
		$this->elements[] = new RadioButtonElement("anrede", AddressData::GetAttributeName($this->languageManager, 'title'), $this->formElementValues["anrede"], false, $this->error["anrede"], $optionsAnrede, false, null, Array(), $this->readOnly);
		$this->elements[] = new TextElement("anrede2", AddressData::GetAttributeName($this->languageManager, 'title2'), $this->formElementValues["anrede2"], false, $this->error["anrede2"], $this->readOnly);
		$this->elements[] = new TextElement("name", AddressData::GetAttributeName($this->languageManager, 'name'), $this->formElementValues["name"], true, $this->error["name"], $this->readOnly);
		$this->elements[] = new TextElement("vorname", AddressData::GetAttributeName($this->languageManager, 'firstname'), $this->formElementValues["vorname"], false, $this->error["vorname"], $this->readOnly);
		$this->elements[] = new TextElement("funktion", AddressData::GetAttributeName($this->languageManager, 'role'), $this->formElementValues["funktion"], false, $this->error["funktion"], $this->readOnly);
		$this->elements[] = new TextElement("strasse", AddressData::GetAttributeName($this->languageManager, 'street'), $this->formElementValues["strasse"], false, $this->error["strasse"], $this->readOnly, null, Array(), Array(), $this->obj->UseAddressFromCompany() ? $this->obj->GetStreet() : '');
		$this->elements[] = new ZIPCityElement("city", AddressData::GetAttributeName($this->languageManager, 'zip')." / ".AddressData::GetAttributeName($this->languageManager, 'city'), Array("zip" => $this->formElementValues["city_zip"], "city" => $this->formElementValues["city_city"]), false, $this->error["city"], $this->readOnly, $this->obj->UseAddressFromCompany() ? $this->obj->GetZIP() : '', $this->obj->UseAddressFromCompany() ? $this->obj->GetCity() : '');
		$this->elements[] = new TextElement("email", AddressData::GetAttributeName($this->languageManager, 'email'), $this->formElementValues["email"], false, $this->error["email"], $this->obj->IsSystemUserAddress($this->db) || $this->readOnly );
		$this->elements[] = new TextElement("telefon", AddressData::GetAttributeName($this->languageManager, 'phone'), $this->formElementValues["telefon"], false, $this->error["telefon"], $this->readOnly);
		$this->elements[] = new TextElement("mobil", AddressData::GetAttributeName($this->languageManager, 'mobile'), $this->formElementValues["mobil"], false, $this->error["mobil"], $this->readOnly);
		$this->elements[] = new TextElement("fax", AddressData::GetAttributeName($this->languageManager, 'fax'), $this->formElementValues["fax"], false, $this->error["fax"], $this->readOnly);
		$options=Array();
		$head = array_keys($AM_ADDRESSDATA_TYPE);
		$data = array_values($AM_ADDRESSDATA_TYPE);
		for($a=0; $a<count($head); $a++)
		{
			if ($head[$a]==AM_ADDRESSDATA_TYPE_NONE) continue;
			$options[]=Array("name" => $data[$a], "value" => $head[$a]);
		}
		$this->elements[] = new DropdownElement("type", AddressData::GetAttributeName($this->languageManager, 'type'), !isset($this->formElementValues["type"]) ? Array() : $this->formElementValues["type"], false, $this->error["type"], $options, true, null, Array(), $this->readOnly);
		
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		if ($this->readOnly) return true;
		if ($this->formElementValues["anrede"]=="1" || $this->formElementValues["anrede"]=="2") $this->obj->SetTitle( $this->formElementValues["anrede"]-1);
		$this->obj->SetTitle2($this->formElementValues["anrede2"]);
		$this->obj->SetName($this->formElementValues["name"]);
		$this->obj->SetFirstName($this->formElementValues["vorname"]);
		$this->obj->SetRole($this->formElementValues["funktion"]);
		if (!$this->obj->IsSystemUserAddress($this->db))
		{
			$retValue = $this->obj->SetEMail($this->formElementValues["email"]);
			if ($retValue!==true)
			{
				if ($retValue==-2) $this->error["email"]="Die eingegebene E-Mail-Adresse ist ungültig";
			}
		}
		$this->obj->SetStreet($this->formElementValues["strasse"]);
		$this->obj->SetZIP($this->formElementValues["city_zip"]);
		$this->obj->SetCity($this->formElementValues["city_city"]);
		$this->obj->SetPhone($this->formElementValues["telefon"]);
		$this->obj->SetMobile($this->formElementValues["mobil"]);
		$this->obj->SetFax($this->formElementValues["fax"]);
		if (!is_array($this->formElementValues["type"])) $this->formElementValues["type"]=Array();
		$typeTemp=0;
		for ($a=0; $a<count($this->formElementValues["type"]); $a++)
		{
			$typeTemp|=$this->formElementValues["type"][$a];
		}
		$this->obj->SetType($typeTemp);
		
		$addressCompany = null;
		if ($this->formElementValues["addressCompany"]!=-1)
		{
			$addressCompany = AddressManager::GetAddressCompanyByPkey($this->db, $this->formElementValues["addressCompany"]);
			if ($addressCompany==null)
			{
				$this->error["addressCompany"] = "Die Firma konnte nicht gefunden werden";
			}
		}
		else
		{
			$this->error["addressCompany"] = "Bitte wählen Sie eine Firma aus";
		}
		$this->obj->SetAddressCompany($addressCompany);
		
		if (count($this->error)>0 )return false;
		$returnValue = $this->obj->Store($this->db);
		if ($returnValue===true) return true;
		if ($returnValue==-101) $this->error["name"] = "Bitte geben Sie einen Nachnamen ein";
		else $this->error["misc"][] = "Ein Unerwarteter Fehler ist aufgetreten (Fehler: ".$returnValue.")";
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
				function CreateNewAddressCompany(type)
				{
					var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/createAddressCompany.php5?<?=SID;?>&type='+type, '_createAddressCompany', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				
				function SetAddressCompany(type, name, addressId, addressCompanyId)
				{
					$('addressCompany').options[$('addressCompany').options.length] = new Option(name, addressId, false, true);
				}
			-->
		</script>
		<?
	}
	
}
?>