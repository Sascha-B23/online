<?php
/**
 * FormData-Implementierung für die Kürzungsbeträge
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class RealisierteEinsparungKbFormData extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		// Icon und Überschrift festlegen
		$this->options["icon"] = "teilabrechnung.png";
		$this->options["icontext"] = "Realisierte Einsparung dokumentieren";
		// Wenn wir nicht im Edit-Mode sind abbrechen
		if (!$edit) return false;
		// Daten aus Objekt laden?
		if( $loadFromObject )
		{
			$this->formElementValues["kuerzungbetrag"]=HelperLib::ConvertFloatToLocalizedString($this->obj->GetKuerzungsbetrag());
			$this->formElementValues["rating"] = $this->obj->GetRating();
			$this->formElementValues["type"] = $this->obj->GetType();
			$this->formElementValues["realisiert"] = $this->obj->GetRealisiert();
		}
		else
		{
			if (!isset($this->formElementValues["kuerzungbetrag"])) $this->formElementValues["kuerzungbetrag"]="0,00";
			if (!isset($this->formElementValues["rating"])) $this->formElementValues["rating"]=Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_UNKNOWN;
			if (!isset($this->formElementValues["type"])) $this->formElementValues["type"]=Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_UNKNOWN;
			if (!isset($this->formElementValues["realisiert"])) $this->formElementValues["realisiert"]=Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO;
		}	
		$this->elements[] = new TextElement("kuerzungbetrag", "Kürzungsbetrag in ".$this->obj->GetCurrency(), $this->formElementValues["kuerzungbetrag"], false, $this->error["kuerzungbetrag"]);
		$options=Array();
		$options[]=Array("name" => "Grün", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN);
		$options[]=Array("name" => "Gelb", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB);
		$options[]=Array("name" => "Rot", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT);
		$options[]=Array("name" => "Grau", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU);
		$this->elements[] = new RadioButtonElement("rating", "Ampelfarbe", $this->formElementValues["rating"], false, $this->error["rating"], $options);
		$options=Array();
		$options[]=Array("name" => "Ersteinsparung", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG);
		$options[]=Array("name" => "Folgeeinsparung", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG);
		$this->elements[] = new RadioButtonElement("type", "Typ", $this->formElementValues["type"], false, $this->error["type"], $options);		
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES);
		$options[]=Array("name" => "Nein", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO);
		$this->elements[] = new RadioButtonElement("realisiert", "Realisiert", $this->formElementValues["realisiert"], true, $this->error["realisiert"], $options);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		// Kürzungsbetrag
		$tempValue=TextElement::GetFloat($this->formElementValues["kuerzungbetrag"]);
		if ($tempValue===false)
		{
			$this->error["kuerzungbetrag"] = "Bitte geben Sie eine gültige Zahl ein";
		}
		else
		{
			$this->obj->SetKuerzungsbetrag($tempValue);
		}
		// Typ
		$this->obj->SetType((int)$this->formElementValues["type"]);
		if ($this->obj->GetType()<=0) $this->error["type"] = "Bitte treffen Sie eine Auswahl";
		// Ampelfarbe
		$this->obj->SetRating((int)$this->formElementValues["rating"]);
		if ($this->obj->GetRating()<=0) $this->error["rating"] = "Bitte treffen Sie eine Auswahl";
		// Realisiert
		$this->obj->SetRealisiert((int)$this->formElementValues["realisiert"]);
		if ($this->obj->GetRealisiert()<0 || $this->obj->GetRealisiert()>1) $this->error["realisiert"] = "Bitte treffen Sie eine Auswahl";
		
		// Speichern
		if (count($this->error)==0)
		{
			$returnValue = $this->obj->Store($this->db);
			if ($returnValue===true)
			{
				return true;
			}
			else
			{
				$this->error["misc"][] = "Systemfehler (".$returnValue.")";
			}
		}
		return false;
	}
	
	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben nachdem das Formular ausgegeben wurde
	 */
	public function PostPrint(){}
	
}
?>