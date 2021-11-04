<?php
/**
 * FormData-Implementierung für CCountry
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class CCountryFormData extends FormData 
{
	/**
	 * CustomerManager
	 * @var CustomerManager 
	 */
	protected $customerManager = null;
	
	/**
	 * Constructor
	 * @param array $formElementValues	form element values (POST-Vars)
	 * @param DBEntry $object			Data-object for this form
	 * @param DBManager $db
	 */
	public function CCountryFormData($formElementValues, CCountry $object, DBManager $db, CustomerManager $customerManager)
	{
		$this->customerManager = $customerManager;
		parent::__construct($formElementValues, $object, $db);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $UM_GROUP_BASETYPE;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "world_icon.png";
		$this->options["icontext"] = "Land ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject )
		{
			$this->formElementValues["currency"]=$this->obj->GetCurrency();
			$this->formElementValues["name"]=$this->obj->GetName();
			$this->formElementValues["iso3166"]=$this->obj->GetIso3166();
		}
		// Elemente anlegen
		$this->elements[] = new TextElement("name", "Name", $this->formElementValues["name"], true, $this->error["name"]);
		$this->elements[] = new TextElement("iso3166", "ISO-3166 Ländercode", $this->formElementValues["iso3166"], true, $this->error["iso3166"]);
		
		$options=Array();
		$currencies = $this->customerManager->GetCurrencies();
		$options[]=Array("name" => "Bitte wählen...", "value" => "");
		for ($a=0; $a<count($currencies); $a++)
		{
			$options[]=Array("name" => $currencies[$a]->GetName()." (".$currencies[$a]->GetIso4217().")", "value" => $currencies[$a]->GetIso4217());
		}
		$this->elements[] = new DropdownElement("currency", "Landeswährung", !isset($this->formElementValues["currency"]) ? Array() : $this->formElementValues["currency"], true, $this->error["currency"], $options, false);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		if (!$this->obj->SetCurrency($this->formElementValues["currency"])) $this->error["currency"]="Bitte wählen Sie die Landeswährung aus.";
		$this->obj->SetName( $this->formElementValues["name"] );
		$this->obj->SetIso3166( $this->formElementValues["iso3166"] );
		if( count($this->error)>0 )return false;
		$returnValue=$this->obj->Store($this->db);
		if ($returnValue===true) return true;
		if ($returnValue==-101) $this->error["name"]="Bitte geben Sie einen Namen ein";
		elseif ($returnValue==-102) $this->error["iso3166"]="Bitte geben Sie den ISO-3166 Ländercode im Format XX ein (z.B. DE für Deutschland)";
		elseif ($returnValue==-103) $this->error["iso3166"]="Der angegebene ISO-3166 Ländercode ist bereits bei einem anderen Land hinterlegt.";
		elseif ($returnValue==-104) $this->error["iso3166"]="Bitte wählen Sie die Landeswährung aus.";
		else $this->error["misc"][] = "Unerwarteter Fehler beim Speichern des Datensatzes (Code: ".$returnValue.")";
		return false;
	}
	
}
?>