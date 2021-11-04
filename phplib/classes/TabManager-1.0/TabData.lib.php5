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
abstract class TabData
{
	
	/**
	 * Array mit den Daten der einzelnen Tab-Einträge
	 * @var TabDataEntry[]
	 */
	protected $tabDataEntries = Array();
	
	/**
	 * Return all visible entries
	 * @return TabDataEntry[]
	 */
	public function GetVisibleTabEntries()
	{
		$returnValue = Array();
		for ($a=0; $a<count($this->tabDataEntries);$a++)
		{
			if ($this->tabDataEntries[$a]->IsVisible()) $returnValue[] = $this->tabDataEntries[$a];
		}
		return $returnValue;
	}

	/**
	 * Output the HTML for the active tab
	 * @param int $activeEntryId
	 * @return bool
	 */
	public function PrintContent($activeEntryId)
	{
		$entries = $this->GetVisibleTabEntries();
		for ($a=0; $a<count($entries); $a++)
		{
			if ($entries[$a]->GetId()==$activeEntryId)
			{
				$entries[$a]->PrintContent();
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Return the default active tab
	 * @return int
	 */
	public function GetDefaultActiveTab()
	{
		// default active tab is the first tab entry in list
		$entries = $this->GetVisibleTabEntries();
		if (count($entries)>0) return $entries[0]->GetId();
		return 0;
	}
	
	/**
	 * Return true if the passed entry ID is valid
	 * @param int $entryId
	 * @return boolean
	 */
	public function IsTabDataEntryIdAvailable($entryId)
	{
		$entries = $this->GetVisibleTabEntries();
		for ($a=0; $a<count($entries); $a++)
		{
			if ($entries[$a]->GetId()==$entryId)
			{
				return true;
			}
		}
		return false;
	}
	
}
?>