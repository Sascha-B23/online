<?php
/**
 * Status "Widerspruch durch FMS freigeben"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasZahlungseingangPruefen extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 14 Tage nach Erhalt
		$this->obj->SetDeadline(time()+60*60*24*14);
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		if ($loadFromObject)
		{
			$widerspruch=$this->obj->GetWiderspruch($this->db);
			if ($widerspruch!=null)
			{
				$this->formElementValues["paymentReceived"]=$widerspruch->IsPaymentReceived() ? 1 : 0;
			}
		}
		// Entscheidung
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => 1);
		$options[]=Array("name" => "Nein", "value" => 0);
		$this->elements[] = new RadioButtonElement("paymentReceived", "Ist die Zahlung eingegangen?", !isset($this->formElementValues["paymentReceived"]) ? Array() : $this->formElementValues["paymentReceived"], true, $this->error["paymentReceived"], $options);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			// Bemerkung Kunde
			$widerspruch->SetPaymentReceived((int)$this->formElementValues["paymentReceived"]===1 ? true : false);
			$returnValue=$widerspruch->Store($this->db);
			if ($returnValue===true)
			{
				// Prüfen, ob in nächsten Status gesprungen werden kann
				if ($gotoNextStatus)
				{
					if (!$widerspruch->IsPaymentReceived()) $this->error["paymentReceived"]="Sie können diese Aufgabe erst abschließen, wenn die Zahlung eingegangen ist.";
				}
			}
			else
			{
				$this->error["misc"][]="Systemfehler (".$returnValue.")";
			}
		}
		else
		{
			$this->error["misc"][]="Untergeordneter Widerspruch konnte nicht gefunden werden.";
		}
		if (count($this->error)==0) return true;
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