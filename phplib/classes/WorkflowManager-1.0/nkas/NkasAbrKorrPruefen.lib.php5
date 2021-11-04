<?php
/**
 * Status "Korrigierte Abrechnung/Gutschrift/Nachtrag prüfen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasAbrKorrPruefen extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 3 Tage nach Erhalt
		$this->obj->SetDeadline(time()+60*60*24*3);
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		global $DOMAIN_HTTP_ROOT;
		$awSchreiben=null;
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		if( $widerspruch!=null ){
			$this->formElementValues["nachtragNotwendig"] = $widerspruch->GetNachtragNotwendig();
			$awSchreiben=$widerspruch->GetLastAntwortschreiben($this->db);
		}
		if( $loadFromObject && $awSchreiben!=null ){
			$this->formElementValues["eingangsDatum"]=$awSchreiben->GetDatum()==0 ? "" : date("d.m.Y", $awSchreiben->GetDatum());
		}
		ob_start();
		?>
			<table width="100%">
				<tr>
					<td valign="top"><strong>Zu überprüfende Abrechnungskorrekturen/Gutschriften</strong></td>
				</tr>
		<?	$files=$awSchreiben->GetFiles($this->db, FM_FILE_SEMANTIC_ABRECHNUNGSKORREKTURGUTSCHRIFT);
			if( count($files)>0 ){
				for( $a=0; $a<count($files); $a++){ 
				?>
					<tr>
						<td valign="top"><a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($files[$a])?>&timestamp=<?=time();?>"><?=$files[$a]->GetFileName();?></a></td>
					</tr>
			<?	}
			}else{ ?>
				<tr>
					<td colspan="4">Keine Dokumente vorhanden</td>
				</tr>
		<?	}?>
				<tr>
					<td valign="top"><br/></td>
				</tr>
				<tr>
					<td valign="top"><strong>Zu überprüfende Nachträge</strong></td>
				</tr>
		<?	$files=$awSchreiben->GetFiles($this->db, FM_FILE_SEMANTIC_NACHTRAG);
			if( count($files)>0 ){
				for( $a=0; $a<count($files); $a++){ 
				?>
					<tr>
						<td valign="top"><a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($files[$a])?>&timestamp=<?=time();?>"><?=$files[$a]->GetFileName();?></a></td>
					</tr>
			<?	}
			}else{ ?>
				<tr>
					<td colspan="4">Keine Dokumente vorhanden</td>
				</tr>
		<?	}?>
			</table>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Datum
		$this->elements[] = new DateElement("eingangsDatum", "Datum der korrigierten Abrechnung/Gutschrift", $this->formElementValues["eingangsDatum"], true, $this->error["eingangsDatum"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => "");
		$options[]=Array("name" => "Abrechnung ist korrekt", "value" => "0");
		$options[]=Array("name" => "Abrechnung ist nicht korrekt", "value" => "1");
		$this->elements[] = new DropdownElement("abbruchAktion", "Prüfungsergebnis", $this->formElementValues["abbruchAktion"], true, $this->error["abbruchAktion"], $options, false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => Widerspruch::NACHTRAG_NOTWENDIG_JA);
		$options[]=Array("name" => "Nein", "value" => Widerspruch::NACHTRAG_NOTWENDIG_NEIN);
		$this->elements[] = new RadioButtonElement("nachtragNotwendig", "Nachtrag notwendig?", $this->formElementValues["nachtragNotwendig"], false, $this->error["nachtragNotwendig"], $options );
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		$awSchreiben=null;
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		if( $widerspruch!=null ){
			$widerspruch->SetNachtragNotwendig((int)$this->formElementValues["nachtragNotwendig"]);
			$awSchreiben=$widerspruch->GetLastAntwortschreiben($this->db);
		}
		if( $awSchreiben==null )return false;
		// Datum
		if( trim($this->formElementValues["eingangsDatum"])!="" ){
			$tempValue=DateElement::GetTimeStamp($this->formElementValues["eingangsDatum"]);
			if( $tempValue===false )$this->error["eingangsDatum"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein";
			else $awSchreiben->SetDatum($tempValue);
		}else{
			$awSchreiben->SetDatum(0);
		}
		// Bezeichnung
		if( count($this->error)==0 ){
			$returnValue = $widerspruch->Store($this->db);
			if ($returnValue===true)
			{
				$returnValue=$awSchreiben->Store($this->db);
				if( $returnValue===true ){
					// Prüfen, ob in nächsten Status gesprungen werden kann
					if( !$gotoNextStatus )return true;
					// Prüfen, ob in nächsten Status gesprungen werden kann
					if( $awSchreiben->GetDatum()==0 )$this->error["eingangsDatum"]="Um die Aufgabe abschließen zu können, müssen Sie ein gültiges Datum im Format tt.mm.jjjj eingeben.";
					if( $this->formElementValues["abbruchAktion"]=="" )$this->error["abbruchAktion"]="Um die Aufgabe abschließen zu können, müssen Sie das Ergebnis der Prüfung angeben.";
					if( count($this->error)==0 )return true;
				}else{
					$this->error["misc"][]="Systemfehler 2 (".$returnValue.")";
				}
			}
			else
			{
				$this->error["misc"][]="Systemfehler 1 (".$returnValue.")";
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
		
	}
	
	/**
	 * Return the following status 
	 */
	public function GetNextStatus()
	{
		// In ausgewählte Maßnahme springen
		return $this->formElementValues["abbruchAktion"];
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