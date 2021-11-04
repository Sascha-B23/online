<?php
/**
 * FormData-Implementierung für CCurrency
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class CCurrencyFormData extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $UM_GROUP_BASETYPE;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "kostenart.png";
		$this->options["icontext"] = "Währung ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject )
		{
			$this->formElementValues["name"]=$this->obj->GetName();
			$this->formElementValues["short"]=$this->obj->GetShort();
			$this->formElementValues["symbol"]=$this->obj->GetSymbol();
			$this->formElementValues["iso4217"]=$this->obj->GetIso4217();
		}
		// Elemente anlegen
		$this->elements[] = new TextElement("name", "Name", $this->formElementValues["name"], true, $this->error["name"]);
		$this->elements[] = new TextElement("short", "Kurzname", $this->formElementValues["short"], true, $this->error["short"]);
		$this->elements[] = new TextElement("symbol", "Symbol", $this->formElementValues["symbol"], true, $this->error["symbol"]);
		$this->elements[] = new TextElement("iso4217", "ISO-4217 Währungscode", $this->formElementValues["iso4217"], true, $this->error["iso4217"]);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		$this->obj->SetName( $this->formElementValues["name"] );
		$this->obj->SetShort( $this->formElementValues["short"] );
		$this->obj->SetSymbol( $this->formElementValues["symbol"] );
		$this->obj->SetIso4217( $this->formElementValues["iso4217"] );
		if( count($this->error)>0 )return false;
		$returnValue=$this->obj->Store($this->db);
		if ($returnValue===true) return true;
		if ($returnValue==-101) $this->error["name"]="Bitte geben Sie einen Namen ein";
		elseif ($returnValue==-102) $this->error["iso4217"]="Bitte geben Sie den ISO-4217 Währungscode im Format XXX ein (z.B. EUR für Euro)";
		elseif ($returnValue==-103) $this->error["iso4217"]="Der angegebene ISO-4217 Währungscode ist bereits bei einer anderen Währung hinterlegt.";
		else $this->error["misc"][] = "Unerwarteter Fehler beim Speichern des Datensatzes (Code: ".$returnValue.")";
		return false;
	}
	
}
?>