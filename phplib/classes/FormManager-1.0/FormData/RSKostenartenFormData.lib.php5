<?php
/**
 * FormData-Implementierung für die FMS-Kostenarten
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class RSKostenartenFormData extends FormData 
{
	/**
	 * CustomerManager
	 * @var CustomerManager 
	 */
	protected $customerManager = null;
	
	/**
	 * All available countries
	 * @var CCountry[] 
	 */
	protected $countries = Array();
	
	/**
	 * Constructor
	 * @param array $formElementValues	form element values (POST-Vars)
	 * @param DBEntry $object			Data-object for this form
	 * @param DBManager $db
	 */
	public function RSKostenartenFormData($formElementValues, RSKostenart $object, DBManager $db, CustomerManager $customerManager)
	{
		$this->customerManager = $customerManager;
		$this->countries = $this->customerManager->GetCountries();
		
		parent::__construct($formElementValues, $object, $db);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $CM_LOCATION_TYPES;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "kostenart.png";
		$this->options["icontext"] = "SFM Kostenart ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["name"]=$this->obj->GetName();
			$this->formElementValues["beschreibung"]=$this->obj->GetBeschreibung();
			for ($a=0; $a<count($CM_LOCATION_TYPES); $a++)
			{
				for ($b=0; $b<count($this->countries); $b++)
				{
					if ($loadFromObject)
					{
						$this->formElementValues["standortTyp_".$CM_LOCATION_TYPES[$a]["id"]."_".$this->countries[$b]->GetIso3166()]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetSollwert($this->countries[$b]->GetIso3166(), $CM_LOCATION_TYPES[$a]["id"]) );
					}
					else
					{
						$this->formElementValues["standortTyp_".$CM_LOCATION_TYPES[$a]["id"]."_".$this->countries[$b]->GetIso3166()]="0,00";
					}
				}
			}
		}
		// Elemente anlegen
		$this->elements[] = new TextElement("name", "Name", $this->formElementValues["name"], true, $this->error["name"]);
		$this->elements[] = new TextAreaElement("beschreibung", "Beschreibung", $this->formElementValues["beschreibung"], false, $this->error["beschreibung"]);
		// 
		for ($b=0; $b<count($this->countries); $b++)
		{
			$this->elements[] = new CustomHTMLElement("", "<strong>".$this->countries[$b]->GetName()." (".$this->countries[$b]->GetIso3166().")</strong>");
			for ($a=0; $a<count($CM_LOCATION_TYPES); $a++)
			{
				if( $CM_LOCATION_TYPES[$a]["id"]==CM_LOCATION_TYPE_NONE )continue;
				$this->elements[] = new TextElement("standortTyp_".$CM_LOCATION_TYPES[$a]["id"]."_".$this->countries[$b]->GetIso3166(), "Sollwert ".$CM_LOCATION_TYPES[$a]["name"]." in ".$this->countries[$b]->GetCurrency(), $this->formElementValues["standortTyp_".$CM_LOCATION_TYPES[$a]["id"]."_".$this->countries[$b]->GetIso3166()], true, $this->error["standortTyp_".$CM_LOCATION_TYPES[$a]["id"]."_".$this->countries[$b]->GetIso3166()]);
			}
		}
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		$this->obj->SetName( $this->formElementValues["name"] );
		$this->obj->SetBeschreibung( $this->formElementValues["beschreibung"] );
		global $CM_LOCATION_TYPES;
		for( $a=0; $a<count($CM_LOCATION_TYPES); $a++ ){
			if( $CM_LOCATION_TYPES[$a]["id"]==CM_LOCATION_TYPE_NONE )continue;
			for ($b=0; $b<count($this->countries); $b++)
			{
				$value=HelperLib::ConvertStringToFloat( $this->formElementValues["standortTyp_".$CM_LOCATION_TYPES[$a]["id"]."_".$this->countries[$b]->GetIso3166()] );
				if( $value===false ){
					$this->error["standortTyp_".$CM_LOCATION_TYPES[$a]["id"]."_".$this->countries[$b]->GetIso3166()]="Bitte geben Sie einen gültigen Betrag ein (z.B. 0,00)";
				}else{
					if( !$this->obj->SetSollwert($this->countries[$b]->GetIso3166(), $CM_LOCATION_TYPES[$a]["id"], $value) ){
						$this->error["standortTyp_".$CM_LOCATION_TYPES[$a]["id"]."_".$this->countries[$b]->GetIso3166()]="Bitte geben Sie einen gültigen Betrag ein (z.B. 0,00)";
					}
				}
			}
		}
		if( count($this->error)>0 )return false;
		$returnValue=$this->obj->Store($this->db);
		if( $returnValue===true )return true;
		if( $returnValue==-101 )$this->error["name"]="Bitte geben Sie einen Namen ein";
		if( $returnValue==-102 )$this->error["name"]="Es exisitiert bereits eine Kostenart mit diesem Namen";
		return false;
	}
	
}
?>