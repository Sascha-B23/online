<?php
/**
 * Status "Terminemail durch FMS freigeben"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2014 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasGespraechsTerminVereinbarenFreigabeFMS extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 1 Tage nach Erhalt
		$this->obj->SetDeadline( time()+60*60*24);
		$this->obj->Store($this->db);
		// RueckweisungsBegruendung erzeugen
		$rueckweisungsBegruendung = new RueckweisungsBegruendung($this->db);
		$rueckweisungsBegruendung->SetType(RueckweisungsBegruendung::RWB_TYPE_TERMINEMAIL);
		$rueckweisungsBegruendung->SetDatum(time());
		$rueckweisungsBegruendung->SetWiderspruch($this->obj->GetWiderspruch($this->db));
		$rueckweisungsBegruendung->SetUser($_SESSION["currentUser"]);
		$rueckweisungsBegruendung->Store($this->db);
	}
		
	/**
	 * this function is called once-only when sitching to the previous status 
	 */
	public function OnReturnToPreviousStatus()
	{	
		// delete RueckweisungsBegruendung 
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			$rueckweisungsBegruendung = $widerspruch->GetLastRueckweisungsBegruendung($this->db, RueckweisungsBegruendung::RWB_TYPE_TERMINEMAIL);
			if ($rueckweisungsBegruendung!=null)
			{
				$rueckweisungsBegruendung->DeleteMe($this->db);
			}
		}
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		/* @var $widerspruch Widerspruch */
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			$rueckweisungsBegruendung=$widerspruch->GetLastRueckweisungsBegruendung($this->db, RueckweisungsBegruendung::RWB_TYPE_TERMINEMAIL);
			if ($rueckweisungsBegruendung!=null) $this->formElementValues["ablehnungsbegruendung"]=$rueckweisungsBegruendung->GetBegruendung();
			
			$terminMail = $widerspruch->GetEMail($this->db, Widerspruch::EMAIL_TYPE_TERMIN);
			if ($terminMail!=null)
			{
				$this->formElementValues["letterSubject"] = $terminMail->ReplacePlaceholders($this->db, $terminMail->GetSubject(), $this->obj);
				$this->formElementValues["letter"] = $terminMail->ReplacePlaceholders($this->db, $terminMail->GetText(), $this->obj);
			}
		}
		// Betreff
		$this->elements[] = new TextElement("letterSubject", "Vorschau E-Mail Betreff (nur lesend)", $this->formElementValues["letterSubject"], false, $this->error["letterSubject"], false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		
		// Anschreiben
		ob_start();
		?>
			<div class="TextForm" style="width: 800px; height: 150px; overflow: scroll; background-color: #FFFFFF; border: solid 1px #707070;"><?=str_replace("\n", "<br />", $this->formElementValues["letter"]);?></div>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT, "Vorschau E-Mail Text (nur lesend)");
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		
		// Zusätzliche Dateien
		/*if ($widerspruch!=null)
		{
			$additionalFiles = $widerspruch->GetFiles($this->db, FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE);
			if (count($additionalFiles)>0)
			{
				$this->elements[] = new FileElement("wFile", "<br/>Zusätzliche Anlagen", $this->formElementValues["wFile"], false, $this->error["wFile"], FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE, $additionalFiles, false, false, false, false);
				$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
				$this->elements[] = new BlankElement();
				$this->elements[] = new BlankElement();
			}
		}*/
		
		// Entscheidung
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => "");
		$options[]=Array("name" => "Freigabe bestätigen", "value" => 1);
		$options[]=Array("name" => "Freigabe zurückweisen", "value" => 2);
		$this->elements[] = new DropdownElement("abbruchAktion", "<br/>Aktion", !isset($this->formElementValues["abbruchAktion"]) ? Array() : $this->formElementValues["abbruchAktion"], false, $this->error["abbruchAktion"], $options, false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Begründung
		$this->elements[] = new TextAreaElement("ablehnungsbegruendung", "Begründung für die Zurückweisung", $this->formElementValues["ablehnungsbegruendung"], false, $this->error["ablehnungsbegruendung"], $this->formElementValues["abbruchAktion"]=="2" ? false : true);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		// Ist der aktuelle Benutzer derjenige, der die Freigabe erteilen darf (oder ein Admin)?
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)<=UM_GROUP_BASETYPE_RSMITARBEITER && $this->obj->GetZuweisungUser()->GetPKey()!=$_SESSION["currentUser"]->GetPKey())
		{
			$this->error["misc"][]="Diese Terminemail kann nur von ".$this->obj->GetZuweisungUser()->GetUserName()." freigegeben werden.";
			return false;
		}
		// AbbruchProtokoll
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			$rueckweisungsBegruendung = $widerspruch->GetLastRueckweisungsBegruendung($this->db, RueckweisungsBegruendung::RWB_TYPE_TERMINEMAIL);
			$rueckweisungsBegruendung->SetBegruendung($this->formElementValues["ablehnungsbegruendung"]);
			$rueckweisungsBegruendung->SetDatum( trim($this->formElementValues["ablehnungsbegruendung"])=="" ? 0 : time() );
			$rueckweisungsBegruendung->SetUserRelease( $_SESSION["currentUser"] );
			$returnValue = $rueckweisungsBegruendung->Store($this->db);
			if ($returnValue===true)
			{
				// Prüfen, ob in nächsten Status gesprungen werden kann
				if ($gotoNextStatus)
				{
					if ($this->formElementValues["abbruchAktion"]==2 && trim($rueckweisungsBegruendung->GetBegruendung())=="") $this->error["ablehnungsbegruendung"]="Bitte geben Sie eine Begründung für die Zurückweisung an.";
					elseif ($this->formElementValues["abbruchAktion"]!=1 && $this->formElementValues["abbruchAktion"]!=2) $this->error["abbruchAktion"]="Um die Aufgabe abschließen zu können, müssen Sie eine Aktion auswählen.";
					
					if (count($this->error)==0 && $this->formElementValues["abbruchAktion"]==1)
					{
						// Wenn keine Fehler aufgetreten sind und die E-Mail freigegeben wurde, die E-Mail jetzt versenden
						/* @var $terminMail TerminMail */
						$terminMail = $widerspruch->GetEMail($this->db, Widerspruch::EMAIL_TYPE_TERMIN);
						if ($terminMail!=null)
						{
							$returnValue = $terminMail->SendMail($this->db);
							if ($returnValue!==true)
							{
								$this->error["misc"][]="E-Mail konnte nicht versandt werden (".$returnValue.")";
								switch($returnValue)
								{
									case -4:
										$this->error["misc"][]="Für den ausgewählten Ansprechpartner ist keine E-Mail-Adresse hinterlegt.";
										break;
								}
							}
						}
						else
						{
							$this->error["misc"][]="E-Mail konnte nicht versandt werden - Terminemail nicht vorhanden";
						}
					}
				}
			}
			else
			{
				$this->error["misc"][]="Systemfehler (1/".$returnValue.")";
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
		?>
		<script type="text/javascript">
			<!--
				$('abbruchAktion').onchange= function(){ $('ablehnungsbegruendung').disabled = ($('abbruchAktion').value=="2" ? false : true); };
				$('letterSubject').onfocus = function(){$('letterSubject').blur();}
				$('letter').onfocus = function(){$('letter').blur();}
			-->
		</script>
		<?
	}
	
	/**
	 * Return the following status 
	 */
	public function GetNextStatus()
	{
		$this->obj->SetZuweisungUser(null);
		if ($this->formElementValues["abbruchAktion"]==2)
		{
			// E-Mail versenden
			global $emailManager;
			$this->obj->SendRejectApprovalEMail($this->db, $emailManager, $_SESSION["currentUser"], 1);
			return -1;
		}
		// Freigabe wurde bestätigt!
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