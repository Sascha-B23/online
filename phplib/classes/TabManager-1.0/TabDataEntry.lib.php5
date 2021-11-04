<?php
/**
 * Basisklasse, welche die Daten der Tabeinträge bereitstellt
 * 
 * @access   	abstract
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class TabDataEntry
{

	/**
	 * id of the tab entry
	 * @var int 
	 */
	protected $entryId = 0;
	
	/**
	 * caption of the tab entry
	 * @var string 
	 */
	protected $entryCaption = "";
	
	/**
	 * Constructor
	 * @param int $entryId
	 * @param string $entryCaption
	 */
	public function TabDataEntry($entryId, $entryCaption)
	{
		$this->entryId = $entryId;
		$this->entryCaption = $entryCaption;
	}
	
	/**
	 * Return the id of the tab entry
	 * @return int
	 */
	public function GetId()
	{
		return $this->entryId;
	}
	
	/**
	 * Return the caption of the tab entry
	 * @return string
	 */
	public function GetCaption()
	{
		return $this->entryCaption;
	}
	
	/**
	 * Return if the tab is visible
	 * @return bool 
	 */
	public function IsVisible()
	{
		return true;
	}
	
	/**
	 * Output the HTML for this tabs
	 * @return bool
	 */
	abstract public function PrintContent();
	
}
?>