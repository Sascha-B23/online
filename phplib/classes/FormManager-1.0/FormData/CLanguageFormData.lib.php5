<?php
/**
 * FormData-Implementierung für CLanguage
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class CLanguageFormData extends FormData 
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
	public function CLanguageFormData($formElementValues, CLanguage $object, DBManager $db, CustomerManager $customerManager)
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
		$this->options["icon"] = "language.png";
		$this->options["icontext"] = "Sprache ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject )
		{
			$this->formElementValues["name"]=$this->obj->GetName();
			$this->formElementValues["iso639"]=$this->obj->GetIso639();
		}
		// Elemente anlegen
		$this->elements[] = new TextElement("name", "Name", $this->formElementValues["name"], true, $this->error["name"]);
		$this->elements[] = new TextElement("iso639", "ISO-639-1 Sprachcode", $this->formElementValues["iso639"], true, $this->error["iso639"]);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		$this->obj->SetName( $this->formElementValues["name"] );
		$this->obj->SetIso639( $this->formElementValues["iso639"] );
		if (count($this->error)>0) return false;
		$returnValue=$this->obj->Store($this->db);
		if ($returnValue===true) return true;
		if ($returnValue==-101) $this->error["name"]="Bitte geben Sie einen Namen ein";
		elseif ($returnValue==-102) $this->error["iso639"]="Bitte geben Sie den ISO-639-1 Ländercode im Format XX ein (z.B. DE für deutsch)";
		elseif ($returnValue==-103) $this->error["iso639"]="Der angegebene ISO-639-1 Ländercode ist bereits bei einer anderen Sprache hinterlegt.";
		else $this->error["misc"][] = "Unerwarteter Fehler beim Speichern des Datensatzes (Code: ".$returnValue.")";
		return false;
	}
	
}
?>