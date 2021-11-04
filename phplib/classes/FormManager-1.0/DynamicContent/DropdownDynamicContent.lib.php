<?php
/**
 * Implementierung der Basisklasse DynamicContent für das Dropdown-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
 class DropdownDynamicContent extends DynamicContent 
{
	 /**
	  *
	  * @var string
	  */
	protected $dropDownListID = "";

	/**
	* Konstruktor
	* @param string	$url			URL, unter welcher die Daten im JSON-Format zur Verfügung gestellt werden
	* @param string	$parameter		Übergabeparameter welche per POST an die URL weitergegebene werden
	* @param string	$dropDownListID	ID (oder Name) der Dropdownliste, die befüllt werden soll
	*/
	public function DropdownDynamicContent($url, $parameter="", $initScriptCode="", $dropDownListID="")
	{
		parent::__construct($url, $parameter, $initScriptCode);
		$this->dropDownListID=$dropDownListID;
	}
	
	/**
	* Gibt den JavaScript-Code aus
	*/
	public function PrintElement()
	{
		?>	<script type="text/javascript">
				var preSelect_<?=$this->dropDownListID;?>="";
			</script> <?
		parent::PrintElement();
	}
	
	/**
	* Gibt den JavaScript-Code für den Erfolgshandler aus 
	* In Javascript steht die Variablen responseJSON, welche die angeforderten Daten enthält, zur Weiterverarbeitung bereit
	*/
	protected function PrintOnSuccess()
	{
		// Select-Element mit Daten füllen ... ?>
		$('<?=$this->dropDownListID;?>').options.length = 0; 
		$('<?=$this->dropDownListID;?>')[0]=new Option("Bitte wählen...", "", false, false);
		for( var i=0; i<responseJSON.length; i++){
			$('<?=$this->dropDownListID;?>')[i+1]=new Option(responseJSON[i].name, responseJSON[i].value, false, false);
			if( preSelect_<?=$this->dropDownListID;?>==responseJSON[i].value )$('<?=$this->dropDownListID;?>').selectedIndex=i+1;
		}
		if( $('<?=$this->dropDownListID;?>').onchange!=undefined) $('<?=$this->dropDownListID;?>').onchange();
		<?
	}
	
	/**
	* Gibt den JavaScript-Code für den Eventhandler im Fehlerfall aus 
	* In Javascript steht die Variablen xhr, welche das Mootools-Request-Objekt beinhaltet zur Weiterverarbeitung bereit
	*/
	public function GetRequestCallString()
	{
		return "$('".$this->dropDownListID."').options.length=0; ".parent::GetRequestCallString();
	}
	
}
?>