<?php
/**
 * Status "Realisierte Einsparung dokumentieren"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasRealisierteEinsparungDokumentieren extends NkasStatusFormDataEntry
{
	
	const RECHNUNG_STELLEN_JA = 1;
	const RECHNUNG_STELLEN_NEIN = 2;
	
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 0 Tage
		$this->obj->SetDeadline( time() );
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		// Aktueller Widerspruch holen
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			// Beim aktuellen WS Nachtrag notwendig?
			/* @var $widerspruch Widerspruch */
			$this->formElementValues["nachtragNotwendig"] = $widerspruch->GetNachtragNotwendig();
		}
		
		// Automatische Ermittlung, ob Rechnung gestellt werden soll oder nicht (Vorauswahl)
		if (!isset($this->formElementValues["rechnungStellen"]))
		{
			$this->formElementValues["rechnungStellen"] = self::RECHNUNG_STELLEN_NEIN;
			if ($widerspruch!=null)
			{
				// min. 1 WS-Punkt vorhanden ...
				if ($widerspruch->GetWiderspruchspunktCount($this->db)>0)
				{
					// ... und nicht alle Kürzungsbeträge eine Folgeeinsparung sind...
					if (!$widerspruch->AllKuerzungsbetraegeOfType($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG))
					{
						// ... und WS-Summe (grün + gelb + grau) != 0
						if ($widerspruch->GetWiderspruchsSumme($this->db)!=0.0)
						{
							// ... dann Rechnung erstellen
							$this->formElementValues["rechnungStellen"] = self::RECHNUNG_STELLEN_JA;
						}
					}
				}
			}
		}
		
		// Alle Widersprüche holen
		$widersprueche = $this->obj->GetWidersprueche($this->db);
		if( isset($_POST["showwiderspruch2"]) ){
			for($a=0; $a<count($widersprueche); $a++){
				if( $widersprueche[$a]->GetPKey()==(int)$_POST["showwiderspruch2"] ){
					$widerspruch = $widersprueche[$a];
					break;
				}
			}
		}
		// Auswahlliste mit allen Widersprüchen anzeigen		
		if (count($widersprueche)>0)
		{
			$options = Array();
			for ($a=0; $a<count($widersprueche); $a++)
			{
				$options[]=Array("name" => "Widerspruch ".($a+1), "value" => $widersprueche[$a]->GetPKey() );
			}
			$this->formElementValues["showwiderspruch2"] = $widerspruch->GetPKey();
			$this->elements[] = new DropdownElement("showwiderspruch2", "Widerspruch", $this->formElementValues["showwiderspruch2"], false, $this->error["showwiderspruch2"], $options);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		// Liste mit allen Widerpruchspunkten des ausgewählten Widerspruchs anzeigen
		if ($widerspruch!=null)
		{
			$this->elements[] = new ListElement("widerspruchspunkte", "<br />Widerspruchspunkte", $this->formElementValues["widerspruchspunkte"], false, $this->error["widerspruchspunkte"], false, new RealisierteEinsparungWsListData($this->db, $widerspruch));
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
			ob_start();
			?>
			<a href="javascript:EditKb();" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
			<span style="position:relative; left:30px;">
				<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/teilabrechnung.png" border="0" alt="" /> <span style="position:relative; top:-6px;">Kürzungsbeträge bearbeiten</span>
			</span>
			</a><br><br>
			<?
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		// Sonstige Angaben...
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => Widerspruch::NACHTRAG_NOTWENDIG_JA, "script" => "onClick=\"document.FM_FORM.weiterePos[0].disabled=true; document.FM_FORM.weiterePos[1].disabled=true;\"");
		$options[]=Array("name" => "Nein", "value" => Widerspruch::NACHTRAG_NOTWENDIG_NEIN, "script" => "onClick=\"document.FM_FORM.weiterePos[0].disabled=false;document.FM_FORM.weiterePos[1].disabled=false;\"");
		$this->elements[] = new RadioButtonElement("nachtragNotwendig", "Nachtrag notwendig?", $this->formElementValues["nachtragNotwendig"], true, $this->error["nachtragNotwendig"], $options );
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => "1");
		$options[]=Array("name" => "Nein", "value" => "0");
		$this->elements[] = new RadioButtonElement("weiterePos", "Weitere Abrechnungspositionen offen?", $this->formElementValues["weiterePos"], true, $this->error["weiterePos"], $options, false, null, Array(), $this->formElementValues["nachtragNotwendig"]==Widerspruch::NACHTRAG_NOTWENDIG_NEIN ? false : true );
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => self::RECHNUNG_STELLEN_JA);
		$options[]=Array("name" => "Nein", "value" => self::RECHNUNG_STELLEN_NEIN);
		$this->elements[] = new RadioButtonElement("rechnungStellen", "Rechnung stellen?", $this->formElementValues["rechnungStellen"], true, $this->error["rechnungStellen"], $options, false, null, Array(), $this->formElementValues["nachtragNotwendig"]==Widerspruch::NACHTRAG_NOTWENDIG_NEIN ? false : true );
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		$widerspruch->SetNachtragNotwendig( (int)$this->formElementValues["nachtragNotwendig"] );
		$returnValue = $widerspruch->Store($this->db);
		if ($returnValue===true)
		{
			if (!$gotoNextStatus) return true;
			if ($widerspruch->GetNachtragNotwendig()==Widerspruch::NACHTRAG_NOTWENDIG_JA) return true;
			if ($this->formElementValues["weiterePos"]=="") $this->error["weiterePos"]="Um die Aufgabe abschließen zu können, müssen Sie angeben, ob noch weitere Abrechnungspositionen offen sind.";
			if ($this->formElementValues["rechnungStellen"]=="") $this->error["rechnungStellen"]="Um die Aufgabe abschließen zu können, müssen Sie angeben, ob eine Rechnung gestellt werden soll.";
			if (count($this->error)==0)
			{
				// Festlegen, wohin nach Status 28 (Buchhaltung informieren) gesprungen werden soll
				$dataToStore = Array();
				$dataToStore["useBranchAfterStatus35"] = $this->formElementValues["rechnungStellen"]==self::RECHNUNG_STELLEN_NEIN ? ($this->formElementValues["weiterePos"]==1 ? 1 : 2) : 0;
				$dataToStore["useBranchAfterStatus28"] = $this->formElementValues["weiterePos"]==1 ? 0 : 1;
				$this->masterObject->SetAdditionalInfo(serialize($dataToStore));
				$returnValue=$this->masterObject->Store($this->db);
				if ($returnValue!==true)
				{
					$this->error["misc"][]="Systemfehler (".$returnValue.")";
					return false;	
				}
				return true;
			}
		}
		else
		{
			$this->error["misc"][]="Systemfehler (".$returnValue.")";
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
		$showWSSelect = $this->obj->GetWiderspruchCount($this->db)>0 ? true : false;
		?>
		<script type="text/javascript">
			<!--	
				function EditPos(posID){
					var newWin=window.open('editRealisierteEinsparungWs.php5?<?=SID;?>&editElement='+posID,'_editRealisierteEinsparung','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1048');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				function EditKb()
				{
					var newWin=window.open('editKbsRealisiert.php5?<?=SID;?>&editElement=<?=$this->obj->GetWiderspruch($this->db)->GetPKey();?>','_editKbs','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=900,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
			<? 	
				if( $showWSSelect ){ ?>
				$('showwiderspruch2').onchange= function(){ 
					document.forms.FM_FORM.submit();
				};
			<? 	} ?>

			-->
		</script>		
	<?
	}
	
	/**
	 * Return the following status 
	 */
	public function GetNextStatus()
	{
		// In ausgewählte Maßnahme springen
		if ($this->formElementValues["nachtragNotwendig"]==Widerspruch::NACHTRAG_NOTWENDIG_JA) return 1;
		if ($this->formElementValues["rechnungStellen"]==self::RECHNUNG_STELLEN_NEIN) return 2;
		return 0;
	}
				
	/**
	 * return if this status of the current process can be edited when in a group
	 * @return boolean
	 */
	public function IsEditableInGroup()
	{
		// only none group is editable
		return ($this->IsProcessGroup() ? false : true);
	}
	
}
?>