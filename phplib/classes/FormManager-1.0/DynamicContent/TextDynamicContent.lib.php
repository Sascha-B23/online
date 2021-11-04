<?php
/**
 * Implementierung der Basisklasse DynamicContent für das Text-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
 class TextDynamicContent extends DynamicContent 
{
	/**
	 *
	 * @var string
	 */
	protected $textEleemntID = "";

	/**
	* Konstruktor
	* @param string	$url			URL, unter welcher die Daten im JSON-Format zur Verfügung gestellt werden
	* @param string	$parameter		Übergabeparameter welche per POST an die URL weitergegebene werden
	* @param string	$textEleemntID	ID (oder Name) des Textelements, das befüllt werden soll
	*/
	public function TextDynamicContent($url, $parameter="", $initScriptCode="", $textEleemntID="")
	{
		parent::__construct($url, $parameter, $initScriptCode);
		$this->textEleemntID=$textEleemntID;
	}
	
	/**
	* Gibt den JavaScript-Code für den Erfolgshandler aus 
	* In Javascript steht die Variablen responseJSON, welche die angeforderten Daten enthält, zur Weiterverarbeitung bereit
	*/
	protected function PrintOnSuccess()
	{
		// Text-Element mit Daten füllen ... ?>
		$('<?=$this->textEleemntID;?>').value = ""; 
		for( var i=0; i<responseJSON.length; i++){
			$('<?=$this->textEleemntID;?>').value=responseJSON[i].value;
			break;
		}
		if( $('<?=$this->textEleemntID;?>').onchange!=undefined) $('<?=$this->textEleemntID;?>').onchange();
		<?
	}
	
	/**
	* Gibt den JavaScript-Code für den Eventhandler im Fehlerfall aus 
	* In Javascript steht die Variablen xhr, welche das Mootools-Request-Objekt beinhaltet zur Weiterverarbeitung bereit
	*/
	public function GetRequestCallString()
	{
		return "$('".$this->textEleemntID."').value=''; ".parent::GetRequestCallString();
	}
	
}
?>