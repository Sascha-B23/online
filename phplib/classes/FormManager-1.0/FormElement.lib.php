<?php
/**
 * Basisklasse aller Elemente einer Form
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class FormElement 
{

	/**
	 * ID des Elements
	 * @var string
	 */
	protected $id = "";

	/**
	 * Name des Elements
	 * @var string
	 */
	protected $name = "";

	/**
	 * Wert des Elements
	 * @var mixed
	 */
	protected $value = "";
	
	/**
	 * Pflichtfeld
	 * @var boolean
	 */
	protected $required = false;
	
	/**
	 * Fehlertext
	 * @var string
	 */
	protected $error = "";
	
	/**
	 * Hinweis
	 * @var string 
	 */
	protected $advice = "";
	
	/**
	 * Breite des Elements
	 * @var int
	 */
	protected $width = 300;
	
	/**
	 * Höhe des Elements
	 * @var int
	 */
	protected $height = 150;

	/**
	 * Readonly
	 * @var bool
	 */
	protected $readonly = false;
	
	/**
	 * FullWidthNeeded
	 * @var bool
	 */
	protected $fullWidthNeeded = false;
	
	/**
	 * Konstruktor
	 * @param string $name Name des Elements
	 * @param string $value Wert des Elements
	 * @param bool $required Pflichtfeld
	 */
	public function FormElement($id, $name, $value, $required=false, $error="", $readonly=false )
	{
		$this->id=$id;
		$this->name=$name;
		$this->value=$value;
		$this->required=$required;
		$this->error=$error;
		$this->readonly=$readonly;
	}

	/**
	 * Gibt die ID des Elements zurück
	 * @return string Gibt die ID zurück
	 */
	public function GetID()
	{
		return $this->id;
	}
	
	/**
	 * Gibt den Namen des Elements zurück
	 * @return string Gibt den Name zurück
	 */
	public function GetName()
	{
		return $this->name;
	}

	/**
	 * Gibt den Wert des Elements zurück
	 * @return string Gibt den Wert zurück
	 */
	public function GetValue()
	{
		return $this->value;
	}
	
	/**
	 * Gibt zurück, ob es sich um ein Pflichtfeld handelt
	 * @return boolean Pflichtfeld
	 */
	public function IsRequired()
	{
		return $this->required;
	}
	
	/**
	 * Gibt zurück, ob es einen Fehler für dieses Element gibt
	 * @return boolean Fehler
	 */
	public function HasError()
	{
		return $this->error!="" ? true : false;
	}
	
	/**
	 * Gibt den Fehlertext des Elements zurück
	 * @return string Fehlertext
	 */
	public function GetError()
	{
		return $this->error;
	}
	
	/**
	 * Gibt zurück, ob es einen Hinweis für dieses Element gibt
	 * @return boolean
	 */
	public function HasAdvice()
	{
		return $this->advice!="" ? true : false;
	}
	
	/**
	 * Gibt den Hinweis des Elements zurück
	 * @return string
	 */
	public function GetAdvice()
	{
		return $this->advice;
	}

	/**
	 * Setzt den Hinweis des Elements
	 * @param string $advice
	 */
	public function SetAdvice($advice)
	{
		$this->advice = trim($advice);
	}
	
	/**
	 * Gibt zurück, ob dieses Element die volle Breite benötigt
	 * @return bool true = Ja ; false =Nein
	 */
	public function SetWidth($newWidth)
	{
		$this->width=$newWidth;
	}
	
	/**
	 * Set the hight in pixel of the form element
	 * @var int $newHeight
	 */
	public function SetHeight($newHeight)
	{
		$this->height = $newHeight;
		return true;
	}
	
	/**
	 * Gibt zurück, ob dieses Element die volle Breite benötigt
	 * @return bool true = Ja ; false =Nein
	 */
	public function FullWidthNeeded()
	{
		return $this->fullWidthNeeded;
	}
	
	/**
	 * Gibt zurück, ob dieses Element die volle Breite benötigt
	 * @return bool true = Ja ; false =Nein
	 */
	public function SetFullWidthNeeded($value)
	{
		if( !is_bool($value) )return;
		$this->fullWidthNeeded=$value;
	}
	
	/**
	 * Gibt das Element aus
	 */
	abstract public function PrintElement();
	
}
?>