<?php
/**
 * FormData-Implementierung für die Abschlagszahlung
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class AbschlagszahlungFormData extends FormData 
{
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		// Icon und Überschrift festlegen
		$this->options["icon"] = "teilabrechnung.png";
		$this->options["icontext"] = "Abschlagszahlung ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject ){
			$this->formElementValues["abschlagszahlungGutschrift"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetAbschlagszahlungGutschrift() );
		}
		// Eingabefelder
		$this->elements[] = new TextElement("abschlagszahlungGutschrift", "Abschlagszahlung / Gutschrift", $this->formElementValues["abschlagszahlungGutschrift"], false, $this->error["abschlagszahlungGutschrift"]);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		// Abschlagszahlung / Gutschrift
		$tempValue=TextElement::GetFloat($this->formElementValues["abschlagszahlungGutschrift"]);
		if( $tempValue===false )$this->error["abschlagszahlungGutschrift"]="Bitte geben Sie eine gültige Zahl ein";
		else $this->obj->SetAbschlagszahlungGutschrift($tempValue);
		if( count($this->error)==0 ){
			$returnValue=$this->obj->Store($this->db);
			if( $returnValue===true ){
				return true;
			}else{
				$this->error["misc"][]="Systemfehler (".$returnValue.")";
			}
		}
		return false;
	}
	
}
?>