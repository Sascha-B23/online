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
class KuerzungsbetragFormData extends FormData
{

	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		// Icon und Überschrift festlegen
		$this->options["icon"] = "teilabrechnung.png";
		$this->options["icontext"] = "Kürzungsbetrag ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject )
		{
			$this->formElementValues["kuerzungbetrag"]=HelperLib::ConvertFloatToLocalizedString($this->obj->GetKuerzungsbetrag());
			$this->formElementValues["rating"] = $this->obj->GetRating();
			$this->formElementValues["type"] = $this->obj->GetType();
			$this->formElementValues["einsparungType"] = $this->obj->GetEinsparungsTyp();
			$this->formElementValues["categorization"] = $this->obj->GetCategorization();
			$this->formElementValues["effectiveDay"] = $this->obj->GetStichtagZielgeldeingang()==0 ? "" : date("d.m.Y", $this->obj->GetStichtagZielgeldeingang());
		}else{
			if( !isset($this->formElementValues["kuerzungbetrag"]) )$this->formElementValues["kuerzungbetrag"]="0,00";
			if( !isset($this->formElementValues["rating"]) )$this->formElementValues["rating"]=Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_UNKNOWN;
			if( !isset($this->formElementValues["type"]) )$this->formElementValues["type"]=Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_UNKNOWN;
			if( !isset($this->formElementValues["einsparungType"]) )$this->formElementValues["einsparungType"]=Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_UNKNOWN;
			if( !isset($this->formElementValues["categorization"]) )$this->formElementValues["categorization"]=Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_UNKNOWN;
			if( !isset($this->formElementValues["effectiveDay"]) )$this->formElementValues["effectiveDay"]="";
		}
		$this->elements[] = new TextElement("kuerzungbetrag", "Kürzungsbetrag in ".$this->obj->GetCurrency(), $this->formElementValues["kuerzungbetrag"], true, $this->error["kuerzungbetrag"]);
		$options=Array();
		$options[]=Array("name" => "Grün", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN);
		$options[]=Array("name" => "Gelb", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB);
		$options[]=Array("name" => "Rot", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT);
		$options[]=Array("name" => "Grau", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU);
		$this->elements[] = new RadioButtonElement("rating", "Ampelfarbe", $this->formElementValues["rating"], true, $this->error["rating"], $options );
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_UNKNOWN);
		$options[]=Array("name" => Kuerzungsbetrag::GetEinsparungsTypName(Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_MAXIMALFORDERUNG), "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_MAXIMALFORDERUNG);
		$options[]=Array("name" => Kuerzungsbetrag::GetEinsparungsTypName(Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_STANDARD), "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_STANDARD);
		$options[]=Array("name" => Kuerzungsbetrag::GetEinsparungsTypName(Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_KUNDE), "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_KUNDE);
		$options[]=Array("name" => Kuerzungsbetrag::GetEinsparungsTypName(Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_VERMIETERANGEBOT), "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_VERMIETERANGEBOT);
		$this->elements[] = new DropdownElement("einsparungType", "Typ Einsparung", $this->formElementValues["einsparungType"], true, $this->error["einsparungType"], $options );
		$options=Array();
		$options[]=Array("name" => "Ersteinsparung", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_ERSTEINSPARUNG);
		$options[]=Array("name" => "Folgeeinsparung", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG);
		$this->elements[] = new RadioButtonElement("type", "Typ", $this->formElementValues["type"], true, $this->error["type"], $options );
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_UNKNOWN);
		$options[]=Array("name" => Kuerzungsbetrag::GetCategorizationName(Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_CLEARONBOTHSIDES), "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_CLEARONBOTHSIDES);
		$options[]=Array("name" => Kuerzungsbetrag::GetCategorizationName(Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_POTENTIALLYCLEARONBOTHSIDES), "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_POTENTIALLYCLEARONBOTHSIDES);
		$options[]=Array("name" => Kuerzungsbetrag::GetCategorizationName(Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_ONESIDEDCLEAR), "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_ONESIDEDCLEAR);
		$options[]=Array("name" => Kuerzungsbetrag::GetCategorizationName(Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_TOBECLARIFIED), "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_TOBECLARIFIED);
		$options[]=Array("name" => Kuerzungsbetrag::GetCategorizationName(Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_DEPOWEREDPOINT), "value" => Kuerzungsbetrag::KUERZUNGSBETRAG_CATEGORIZATION_DEPOWEREDPOINT);
		$this->elements[] = new DropdownElement("categorization", "Einstufung", $this->formElementValues["categorization"], false, $this->error["categorization"], $options );
		// Datum-Feld dem Formular hinzufügen
		$this->elements[] = new DateElement('effectiveDay', "Stichtag Zahlungseingang", $this->formElementValues["effectiveDay"], false, $this->error["effectiveDay"]);
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
		// Typ Einsparung
		$this->obj->SetEinsparungsTyp((int)$this->formElementValues["einsparungType"]);
		if ($this->obj->GetEinsparungsTyp()<=0) $this->error["einsparungType"] = "Bitte treffen Sie eine Auswahl";
		// Kategorisierung
		$this->obj->SetCategorization((int)$this->formElementValues["categorization"]);
		// Stichtag Zielgeldeingang -> Datum Element
		$effectiveDay = DateElement::GetTimeStamp($this->formElementValues["effectiveDay"]);
		if ($this->formElementValues["effectiveDay"] !== "" && $effectiveDay === false)
		{
			$this->error["effectiveDay"] = "Ungültiges Datum";
		}
		else
		{
			$timeStamp = ($effectiveDay)? $effectiveDay:0;
			$this->obj->SetStichtagZielgeldeingang($timeStamp);
		}
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