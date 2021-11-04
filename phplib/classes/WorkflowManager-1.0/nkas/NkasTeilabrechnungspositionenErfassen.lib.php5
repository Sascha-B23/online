<?php
/**
 * Status "Teilabrechnungspositionen erfassen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasTeilabrechnungspositionenErfassen extends NkasStatusFormDataEntry
{

	protected $ignoreTAs = false;

	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 14 Tage ab Auftragsdatum
		$this->obj->SetDeadline($this->obj->GetAuftragsdatumAbrechnung()+60*60*24*14);
		$this->obj->Store($this->db);
	}

	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		$teilabrechnung=$this->obj->GetTeilabrechnung($this->db);
		if ($teilabrechnung==null) return;
		
		if( $this->formElementValues["cloneID"]!="" && ((int)$this->formElementValues["cloneID"])==$this->formElementValues["cloneID"] )
		{
			if ($teilabrechnung!=null)
			{
				$teilabrechnung->CloneTeilabrechnungsposition($this->db, (int)$this->formElementValues["cloneID"]);
			}
		}


		// Rückweisungsbegründung für Freigabe anzeigen wenn vorhanden und wenn wir von Status 8 oder 20 kommen (Freigabe durch FMS bzw. Kunde abgelehnt)
		if( $this->obj->GetLastStatus()==36 && $this->masterObject->GetRueckweisungsBegruendungenCount($this->db, RueckweisungsBegruendungProzess::RWB_TYPE_ERFASSUNG)>0 )
		{
			/**@var RueckweisungsBegruendungProzess[] $rueckweisungsBegruendung*/
			$rueckweisungsBegruendung = $this->masterObject->GetRueckweisungsBegruendungen($this->db, RueckweisungsBegruendungProzess::RWB_TYPE_ERFASSUNG);
			$protokoll=Array();
			for( $a=0; $a<count($rueckweisungsBegruendung); $a++){
				$protokoll[$a]["date"]=$rueckweisungsBegruendung[$a]->GetDatum();
				$protokoll[$a]["username"]=$rueckweisungsBegruendung[$a]->GetUserRelease()==null ? "*GELÖSCHT*" : $rueckweisungsBegruendung[$a]->GetUserRelease()->GetUserName();
				$protokoll[$a]["title"]="Freigabe abgelehnt";
				$protokoll[$a]["text"]=str_replace("\n", "<br/>", $rueckweisungsBegruendung[$a]->GetBegruendung());
			}
			ob_start();
			include("abbruchProtokoll.inc.php5");
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}

		// Wenn wir von Status 9 kommen 
		//if ($this->obj->GetLastStatus()==9)
		{
			// Weitere Teilabrechungen?
			$abrechnungsJahr=$this->obj->GetAbrechnungsJahr();
			$tasAll=$abrechnungsJahr->GetTeilabrechnungen($this->db);
			$tas=Array();
			for( $a=0; $a<count($tasAll); $a++)
			{
				if( $tasAll[$a]->GetPKey()==(int)$this->formElementValues["teilabrechnungAuswahl2"] )
				{
					// Aktuelle TA Umschalten
					if( $tasAll[$a]->GetPKey()!=$teilabrechnung->GetPKey() )
					{
						$this->obj->SetCurrentTeilabrechnung($this->db, $tasAll[$a]);
						if( $this->obj->Store($this->db)===true )
						{
							$teilabrechnung=$tasAll[$a];
						}
					}
				}
				$tas[]=$tasAll[$a];
			}
			if (count($tas)>0)
			{
				$options=Array();
				for( $a=0; $a<count($tas); $a++)
				{
					$options[]=Array("name" => $tas[$a]->GetBezeichnung(), "value" => $tas[$a]->GetPKey() );
				}
				$this->formElementValues["teilabrechnungAuswahl2"]=$teilabrechnung->GetPKey();
				$this->elements[] = new DropdownElement("teilabrechnungAuswahl2", "Aktuelle Teilabrechnung", $this->formElementValues["teilabrechnungAuswahl2"], false, $this->error["teilabrechnungAuswahl2"], $options);
				$this->elements[] = new BlankElement();
				$this->elements[] = new BlankElement();
			}
		}

		$this->elements[] = new ListElement("positionen", "Teilabrechnungspositionen", $this->formElementValues["positionen"], false, $this->error["positionen"], false, new TeilabrechnungspositionListData($this->db, $teilabrechnung));
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// TAPs aus dem Vorjahr übernehmen
		ob_start();
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td valign="top"><a href="javascript:CopyTAPMatrix(<?=$teilabrechnung->GetPKey();?>);" style="text-decoration:none;" onfocus="if (this.blur) this.blur()"><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/teilabrechnung.png" border="0" alt="" /> <span style="position:relative; top:-6px;"></a></td>
				<td><a href="javascript:CopyTAPMatrix(<?=$teilabrechnung->GetPKey();?>);" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">Teilabrechnungspositionen aus dem Vorjahr übernehmen</a></td>
			</tr>
		</table>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		// TAPs aus anderer Teilabrechnung übernehmen
		ob_start();
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td valign="top"><a href="javascript:CopyTAPMatrix(-1);" style="text-decoration:none;" onfocus="if (this.blur) this.blur()"><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/teilabrechnung.png" border="0" alt="" /> <span style="position:relative; top:-6px;"></a></td>
				<td><a href="javascript:CopyTAPMatrix(-1);" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">Teilabrechnungspositionen aus anderer Abrechnung übernehmen</a></td>
			</tr>
		</table>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		// Neue TAP hinzufügen
		ob_start();
		?>
		<a href="javascript:CreateNewPos();" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
			<span style="position:relative; left:30px;">
				<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/newEntry.png" border="0" alt="" /> <span style="position:relative; top:-6px;">Teilabrechnungsposition hinzufügen</span>
			</span>
		</a>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		// Neue TAP hinzufügen
		ob_start();
		?>
		<a href="javascript:ListEdit();" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
			<span style="position:relative;">
				<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/teilabrechnung.png" border="0" alt="" /> <span style="position:relative; top:-6px;">Umlagefähigkeit der Teilabrechnungspositionen bearbeiten</span>
			</span>
		</a>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Weitere Teilabrechungen?
		if (!$this->ignoreTAs){
			$abrechnungsJahr=$this->obj->GetAbrechnungsJahr();
			$tasAll=$abrechnungsJahr->GetTeilabrechnungen($this->db);
			$tas=Array();
			for( $a=0; $a<count($tasAll); $a++)
			{
				if( !$tasAll[$a]->GetErfasst() && $tasAll[$a]->GetPKey()!=$teilabrechnung->GetPKey() )
				{
					$tas[]=$tasAll[$a];
				}
			}
			if( count($tas)>0 )
			{
				$options=Array();
				$options[]=Array("name" => "Bitte wählen...", "value" => "");
				for( $a=0; $a<count($tas); $a++)
				{
					$options[]=Array("name" => $tas[$a]->GetBezeichnung(), "value" => $tas[$a]->GetPKey() );
				}
				$this->elements[] = new DropdownElement("teilabrechnungAuswahl", "Nächste zu erfassende Teilabrechnung", $this->formElementValues["teilabrechnungAuswahl"], false, $this->error["teilabrechnungAuswahl"], $options);
			}
			else
			{
				$options=Array(0 => Array("name" => "Ja", "value" => "1"), Array("name" => "Nein", "value" => "2") );
				$this->elements[] = new RadioButtonElement("weitereTeilabrechnungen", "<br/>Stehen noch weitere Teilabrechnungen aus?", $this->formElementValues["weitereTeilabrechnungen"], false, $this->error["weitereTeilabrechnungen"], $options);
			}
		}
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		if (!$gotoNextStatus) return true;
		// Weitere Teilabrechungen?
		$teilabrechnung=$this->obj->GetTeilabrechnung($this->db);
		if ($teilabrechnung==null)
		{
			$this->error["misc"][]="Systemfehler: Teilabrechnung konnte nicht gefunden werden";
			return false;
		}
		$abrechnungsJahr=$this->obj->GetAbrechnungsJahr();
		if ($abrechnungsJahr==null)
		{
			$this->error["misc"][]="Systemfehler: Abrechnungsjahr konnte nicht gefunden werden";
			return false;
		}
		$tasAll=$abrechnungsJahr->GetTeilabrechnungen($this->db);
		$tas=Array();
		for ($a=0; $a<count($tasAll); $a++)
		{
			if (!$tasAll[$a]->GetErfasst() && $tasAll[$a]->GetPKey()!=$teilabrechnung->GetPKey())
			{
				$tas[]=$tasAll[$a];
			}
		}
		if (count($tas)>0)
		{
			if ($this->formElementValues["teilabrechnungAuswahl"]==="")
			{
				$this->error["teilabrechnungAuswahl"]="Um die Aufgabe abschließen zu können, müssen Sie angeben, welche Teilabrechnung Sie als nächstes erfassen möchten.";
			}
		}
		else
		{
			if ($this->formElementValues["weitereTeilabrechnungen"]!=1 && $this->formElementValues["weitereTeilabrechnungen"]!=2)
			{
				$this->error["weitereTeilabrechnungen"]="Um die Aufgabe abschließen zu können, müssen Sie angeben ob noch weitere Teilabrechnugen ausstehen.";
			}
		}
		if ($teilabrechnung->GetTeilabrechnungspositionCount($this->db)==0)
		{
			$this->error["misc"][]="Um die Aufgabe abschließen zu können, muss mindestens eine Teilabrechnungsposition angelegt sein.";
		}
		$positionen=$teilabrechnung->GetTeilabrechnungspositionen($this->db);
		for ($a=0; $a<count($positionen); $a++)
		{
			if (!$positionen[$a]->IsComplete($this->db))
			{
				$this->error["misc"][]="Um die Aufgabe abschließen zu können, müssen alle Teilabrechnungspositionen vollständig ausgefüllt sein.";
				break;
			}
		}
		if (count($this->error)>0) return false;
		// Flag setzen, dass TA erfasst wurde
		$teilabrechnung->SetErfasst( true );
		if ($teilabrechnung->Store($this->db)!==true)
		{
			$this->error["misc"][]="Systemfehler: Teilabrechnung konnte nicht gespeichert werden.";
			return false;
		}
		if (count($tas)>0)
		{
			$currentTeilabrechnung=null;
			for ($a=0; $a<count($tas); $a++)
			{
				if (!$tas[$a]->GetErfasst() && $tas[$a]->GetPKey()==$this->formElementValues["teilabrechnungAuswahl"])
				{
					$currentTeilabrechnung=$tas[$a];
					break;
				}
			}
			if ($currentTeilabrechnung==null)
			{
				$this->error["misc"][]="Systemfehler: Zu erfassende Teilabrechnung konnte nicht gefunden werden.";
				return false;
			}
			$this->obj->SetCurrentTeilabrechnung($this->db, $currentTeilabrechnung);
			if ($this->obj->Store($this->db)!==true)
			{
				$this->error["misc"][]="Systemfehler: Zu erfassende Teilabrechnung konnte nicht gesetzt werden.";
				return false;
			}
		}
		else
		{
			$abrechnungsJahr=$this->obj->GetAbrechnungsJahr();
			if ($abrechnungsJahr==null)
			{
				$this->error["misc"][]="Systemfehler: Zugehöriges Abrechnungsjahr konnte nicht gefunden werden.";
				return false;
			}
			$abrechnungsJahr->SetAlleTeilabrechnungenErfasst( $this->formElementValues["weitereTeilabrechnungen"]==1 ? false : true );
			if ($abrechnungsJahr->Store($this->db)!==true)
			{
				$this->error["misc"][]="Systemfehler: Abrechnungsjahr konnte nicht gespeichert werden.";
				return false;
			}
		}
		// Jump back to Status 0?
		if ($this->formElementValues["weitereTeilabrechnungen"]==1 || $this->formElementValues["teilabrechnungAuswahl"]!="")
		{
			// if process is in a group set this process as selected process in group
			if (is_a($this->masterObject, 'ProcessStatusGroup'))
			{
				$this->masterObject->SetSelectedProcessStatus($this->obj);
			}
		}
		// Aktuelle Benutzerzuweisung löschen
		$this->masterObject->SetZuweisungUser(null);
		return true;
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
		global $DOMAIN_HTTP_ROOT;
		?>
		<script type="text/javascript">
			<!--	
				function CreateNewPos()
				{
					var newWin=window.open('editTeilabrechnungsposition.php5?<?=SID;?>&teilabrechnungID=<?=$this->obj->GetTeilabrechnung($this->db)->GetPKey();?>','_createEditContract','resizable=1,status=1,location=0,menubar=0,scrollbars=0,toolbar=0,height=800,width=1024');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				
				function CopyTAPMatrix(ta_id)
				{
					var newWin=window.open('copyTAPMatrix.php5?<?=SID;?>&editElement=<?=$this->obj->GetTeilabrechnung($this->db)->GetPKey();?>&taId='+ta_id,'_createEditContract','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();					
				}
				
				function EditPos(posID)
				{
					var newWin=window.open('editTeilabrechnungsposition.php5?<?=SID;?>&editElement='+posID,'_createEditContract','resizable=1,status=1,location=0,menubar=0,scrollbars=0,toolbar=0,height=800,width=1024');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				
				function ClonePos(posID)
				{
					document.forms.FM_FORM.cloneID.value=posID; 
					document.forms.FM_FORM.submit();
				}
				
				function ListEdit()
				{
					var newWin=window.open('editTapList.php5?<?=SID;?>&editElement=<?=$this->obj->GetTeilabrechnung($this->db)->GetPKey();?>','_createEditTap','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
								
			<? /*if( $this->obj->GetLastStatus()==9 )*/{ ?>
				$('teilabrechnungAuswahl2').onchange= function(){ 
					document.forms.FM_FORM.submit();
				};
			<? } ?>
			-->
		</script>
		<?
	}
	
	/**
	 * Return the following status 
	 */
	public function GetNextStatus()
	{
		if ($this->formElementValues["weitereTeilabrechnungen"]==1 || $this->formElementValues["teilabrechnungAuswahl"]!="") return 1;
		// Set current user as previews status user
		$dataToStore = Array();
		$tempData = unserialize($this->obj->GetAdditionalInfo());
		if (is_array($tempData)) $dataToStore = $tempData;
		$dataToStore['previewsStatusUser'] = $_SESSION["currentUser"]->GetPkey();
		$this->obj->SetAdditionalInfo(serialize($dataToStore));
		// remove responsible user
		$this->obj->SetZuweisungUser(null);
		return 0;
	}
			
	/**
	 * return if this status of the current process can be edited when in a group
	 * @return boolean
	 */
	public function IsEditableInGroup()
	{
		// only non group is editable
		return ($this->IsProcessGroup() ? false : true);
	}
	
}
?>