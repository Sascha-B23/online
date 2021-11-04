<?php
/**
 * Status "Zurückgestellt"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasZurueckgestellt extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Vorbereitung treffen, wenn in diesen Status gesprungen wird
		$this->obj->SetDeadline($this->obj->GetRueckstellungBis());
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		ob_start();
		?>
			<strong>Dieser Prozess ist bis zm <?=date("d.m.Y", $this->obj->GetRueckstellungBis())?> mit folgender Begründung zurückgestellt:</strong><br>
			<i><?=str_replace("\n", "<br/>", $this->obj->GetRueckstellungBegruendung());?></i>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			// Zurückstellen abbrechen
			$this->elements[] = new CheckboxElement("cancelProcess", "<br>Zurückstellung aufheben", "", false, $this->error["cancelProcess"]);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		}
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		if ($gotoNextStatus && $this->formElementValues["cancelProcess"]=="on")
		{
			$this->obj->SetRueckstellungBis(0);
			$this->obj->SetRueckstellungBegruendung("");
			return true;
		}
		return false;
	}
	
	/**
	 * Inject HTML/JavaScript before the UI is send to the browser 
	 */
	public function PrePrint()
	{
		
	}
	
	/**
	 * Inject HTML/JavaScript after the UI is send to the browser 
	 */
	public function PostPrint()
	{
		
	}
	
	/**
	 * Return the following status 
	 */
	public function GetNextStatus()
	{
		return 0;
	}
			
	/**
	 * return if this status of the current process can be edited when in a group
	 * @return boolean
	 */
	public function IsEditableInGroup()
	{
		// only group is editable
		return ($this->IsProcessGroup() ? true : false);
	}
	
}
?>