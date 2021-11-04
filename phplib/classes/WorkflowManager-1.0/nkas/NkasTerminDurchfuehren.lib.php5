<?php
/**
 * Status Termin durchführen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasTerminDurchfuehren extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: Termin
		$this->obj->SetDeadline( $this->obj->GetTelefontermin() );
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		ob_start();
		?>
			<strong>Termin ist am <?=date("d.m.Y", $this->obj->GetTelefontermin())?> um <?=date("H:i", $this->obj->GetTelefontermin())?> Uhr</strong><br/><br/>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		
		// FMS-Schreiben
		$widerspruche = $this->obj->GetWidersprueche($this->db);
		$rsSchreibenList = Array();
		for ($a=count($widerspruche)-1; $a>=0; $a--)
		{
			$rsSchreibenWS = $widerspruche[$a]->GetFiles($this->db, FM_FILE_SEMANTIC_RSSCHREIBEN, "");
			for ($b=count($rsSchreibenWS)-1; $b>=0; $b--)
			{ 
				$rsSchreibenList[] = $rsSchreibenWS[$b];
			}
		}
		ob_start();
		?>
		<br />
		<a href="javascript:AddRSSchreiben();" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
		<span>
			<img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/newEntry.png" border="0" alt="" /> <span style="position:relative; top:-6px;">SFM-Schreiben hinzufügen</span>
		</span>
		</a><br/>
		<?
		include("rsSchreiben.inc.php5");
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT, "SFM-Schreiben");
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		
		// Gesprächs-Termin vereinbaren abbrechen
		$optionsAnrede=Array(0 => Array("name" => "Termin ändern", "value" => "1"), Array("name" => "Termin abschließen", "value" => "2") );
		$this->elements[] = new RadioButtonElement("cancelProcess", "<br/>Aktion", $this->formElementValues["cancelProcess"], false, $this->error["cancelProcess"], $optionsAnrede);		
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		global $appointmentManager;
		// Abbruch
		if ($gotoNextStatus)
		{
			if ($this->formElementValues["cancelProcess"]=="1")
			{
				return true;
			}
			elseif ($this->formElementValues["cancelProcess"]=="2")
			{
				$this->obj->SetTelefontermin(0);
				$this->obj->SetTelefonterminEnde(0);
				$this->obj->SetTelefonterminAnsprechpartner(null);
				$returnValue = $this->obj->Store($this->db);
				if ($returnValue===true)
				{
					$returnValue = $this->obj->UpdateTelefonterminCalendarEntry($this->db, $appointmentManager);
					if ($returnValue===true)
					{
						return true;
					}
					else
					{
						$this->error["misc"][]="Der Termin konnt nicht aktualisiert werden (".$returnValue.")";
					}
				}
				else
				{
					$this->error["misc"][]="Systemfehler (".$returnValue.")";
				}
			}
			else
			{
				$this->error["cancelProcess"] = "Um die Aufgabe abschließen zu können, müssen Sie eine Aktion auswählen.";
			}
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
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		?>
		<script type="text/javascript">
			<!--	
				function AddRSSchreiben(){
					var newWin=window.open('addRSSchreiben.php5?<?=SID;?>&widerspruchID=<?=$widerspruch->GetPKey();?>','_addRSSchreiben','resizable=1,status=1,location=0,menubar=0,scrollbars=0,toolbar=0,height=800,width=1024');
					//newWin.moveTo(width,height);
					newWin.focus();					
				}
			-->
		</script>
		<?
	}
	
	/**
	 * Return the following status 
	 */
	public function GetNextStatus()
	{
		if( $this->formElementValues["cancelProcess"]=="1" )return 1;
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