<?php
/**
 * Status "Widerspruch durch Kunde freigeben"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasWiderspruchFreigabeKunde extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 3 Tage nach Erhalt
		$this->obj->SetDeadline( time()+60*60*24*3 );
		$this->obj->Store($this->db);
		// RueckweisungsBegruendung erzeugen
		$rueckweisungsBegruendung=new RueckweisungsBegruendung($this->db);
		$rueckweisungsBegruendung->SetType(RueckweisungsBegruendung::RWB_TYPE_WIDERSPRUCH);
		$rueckweisungsBegruendung->SetDatum( time() );
		$rueckweisungsBegruendung->SetWiderspruch( $this->obj->GetWiderspruch($this->db) );
		$rueckweisungsBegruendung->SetUser( $_SESSION["currentUser"] );
		$rueckweisungsBegruendung->Store($this->db);
		// Log delivery to customer
		//$prozessData['jahr']." ".$prozessData['groupName']." - ".$prozessData['locationName']." (FMS-ID: ".$prozessData['RSID'].")
		LoggingManager::GetInstance()->Log(new LoggingWsApproval($this->obj->GetProzessPath()[0]['path']));
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
			$rueckweisungsBegruendung = $widerspruch->GetLastRueckweisungsBegruendung($this->db, RueckweisungsBegruendung::RWB_TYPE_WIDERSPRUCH);
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
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		$showAttachment = true;
		if( $loadFromObject )
		{
			if( $widerspruch!=null )
			{
				$showAttachment = !$widerspruch->GetHideAttachemntFromCustomer();
				$rueckweisungsBegruendung=$widerspruch->GetLastRueckweisungsBegruendung($this->db, RueckweisungsBegruendung::RWB_TYPE_WIDERSPRUCH);
				if( $rueckweisungsBegruendung!=null )$this->formElementValues["ablehnungsbegruendung"]=$rueckweisungsBegruendung->GetBegruendung();
			}
		}
		// In Gruppe ist der automatische WS nicht verfügbar
		if ($this->IsProcessGroup()) $showAttachment = false;
		// Vorschau
		ob_start();
			$bemerkung = ($widerspruch!=null ? str_replace("\n", "<br/>", $widerspruch->GetBemerkungFuerKunde()) : "");
			if( trim($bemerkung)!="" ){ ?>	
				<table width="800" border="0" cellpadding="0" cellspacing="10" bgcolor="#ffffff">
					<tr>
						<td>
							<strong>Bemerkung</strong><br><br>
							<?=$bemerkung;?>
						</td>
					</tr>
				</table>
				<br><br>
		<?	}?>	
			<strong>Widerspruch</strong><br>
			<input type="hidden" name="createDownloadFile" id="createDownloadFile" value="" />
			<input type="button" value="Vorschau Anschreiben" onClick="DownloadFile(<?=DOCUMENT_TYPE_ANSCHREIBEN;?>); this.disabled=true;" class="formButton2"/><?if($showAttachment){?>&#160;&#160;&#160;<input type="button" value="Vorschau Anlage" onClick="DownloadFile(<?=DOCUMENT_TYPE_ANHANG;?>); this.disabled=true;" class="formButton2" /><?}?>
			<?if( trim($this->error["createDocument"])!=""){?><br /><br /><div class="errorText"><?=$this->error["createDocument"];?></div><?}?>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Zusätzliche Dateien
		if ($widerspruch!=null)
		{
			$additionalFiles = $widerspruch->GetFiles($this->db, FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE);
			if (count($additionalFiles)>0)
			{
				$this->elements[] = new FileElement("wFile", "<br/>Zusätzliche Anlagen", $this->formElementValues["wFile"], false, $this->error["wFile"], FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE, $widerspruch!=null ? $widerspruch->GetFiles($this->db, FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE) : Array(), false, false, false, false);
				$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
				$this->elements[] = new BlankElement();
				$this->elements[] = new BlankElement();
			}
		}
		// Entscheidung
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => "");
		$options[]=Array("name" => "Freigabe bestätigen", "value" => "0");
		$options[]=Array("name" => "Freigabe zurückweisen", "value" => "1");
		$this->elements[] = new DropdownElement("abbruchAktion", "<br/>Aktion", $this->formElementValues["abbruchAktion"], false, $this->error["abbruchAktion"], $options, false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Begründung
		$this->elements[] = new TextAreaElement("ablehnungsbegruendung", "Begründung für die Zurückweisung", $this->formElementValues["ablehnungsbegruendung"], false, $this->error["ablehnungsbegruendung"], $this->formElementValues["abbruchAktion"]=="1" ? false : true);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		if( $_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE && $_SESSION["currentUser"]->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_ADMINISTRATOR ){
			$this->error["misc"][]="Die Freigabe kann nur durch den Kunden erfolgen.";
			return false;
		}
		// AbbruchProtokoll
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		if( $widerspruch!=null ){
			$rueckweisungsBegruendung=$widerspruch->GetLastRueckweisungsBegruendung($this->db, RueckweisungsBegruendung::RWB_TYPE_WIDERSPRUCH);
			$rueckweisungsBegruendung->SetBegruendung($this->formElementValues["ablehnungsbegruendung"]);
			$rueckweisungsBegruendung->SetDatum( trim($this->formElementValues["ablehnungsbegruendung"])=="" ? 0 : time() );
			$rueckweisungsBegruendung->SetUserRelease( $_SESSION["currentUser"] );
			$returnValue=$rueckweisungsBegruendung->Store($this->db);
			if( $returnValue===true ){
				// Prüfen, ob in nächsten Status gesprungen werden kann
				if( $gotoNextStatus ){
					if( $this->formElementValues["abbruchAktion"]=="1" && trim($rueckweisungsBegruendung->GetBegruendung())=="" )$this->error["ablehnungsbegruendung"]="Bitte geben Sie eine Begründung für die Zurückweisung an.";
					elseif( $this->formElementValues["abbruchAktion"]=="" )$this->error["abbruchAktion"]="Um die Aufgabe abschließen zu können, müssen Sie eine Aktion auswählen.";
				}
			}else{
				$this->error["misc"][]="Systemfehler (1/".$returnValue.")";
			}
		}else{
			$this->error["misc"][]="Untergeordneter Widerspruch konnte nicht gefunden werden.";
		}
		if( count($this->error)==0 )return true;
		return false;
	}
	
	/**
	 * Inject HTML/JavaScript before the UI is send to the browser 
	 */
	public function PrePrint()
	{
		if (trim($_POST["createDownloadFile"])!="")
		{
			// Mögliche Änderungen übernehmen
			$this->Store(false);
			$widerspruch=$this->obj->GetWiderspruch($this->db);
			// Includes
			if ($widerspruch!=null)
			{
				$result=$widerspruch->CreateDocument($this->db, (int)$_POST["createDownloadFile"]==DOCUMENT_TYPE_ANSCHREIBEN ? DOCUMENT_TYPE_RTF : DOCUMENT_TYPE_PDF, (int)$_POST["createDownloadFile"]);
				if (is_int($result))
				{
					$this->error["createDocument"]="Dokument konnte nicht erzeugt werden (".$result.")";
				}
				else
				{
					if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_KUNDE)
					{
						LoggingManager::GetInstance()->Log(new LoggingWsCustomerView((int)$_POST["createDownloadFile"]==DOCUMENT_TYPE_ANSCHREIBEN ? "Anschrieben" : "Anhang", WorkflowManager::GetProzessStatusForStatusID($this->obj->GetCurrentStatus())['name'], $this->obj->GetProzessPath()[0]['path'], "Widerspruch.".$result["extension"]));
					}
					// Streamen...
					header('HTTP/1.1 200 OK');
					header('Status: 200 OK');
					header('Accept-Ranges: bytes');
					header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header('Content-Transfer-Encoding: Binary');
					header("Content-type: application/".$result["extension"]);
					header("Content-Disposition: attachment; filename=\"Widerspruch.".$result["extension"]."\"");
					header("Content-Length: ".(string)strlen($result["content"]));
					echo $result["content"];
					exit;
				}
			}
		}
	}
	
	/**
	 * Inject HTML/JavaScript after the UI is send to the browser 
	 */
	public function PostPrint()
	{
		?>
		<script type="text/javascript">
			<!--
				$('abbruchAktion').onchange= function(){ $('ablehnungsbegruendung').disabled = ($('abbruchAktion').value=="1" ? false : true); };
				
				function DownloadFile(format){
					document.forms.FM_FORM.createDownloadFile.value=format; 
					document.forms.FM_FORM.submit();
					document.forms.FM_FORM.createDownloadFile.value=""; 
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
		if ($this->formElementValues["abbruchAktion"]==1)
		{
			// E-Mail versenden
			global $emailManager;
			$this->obj->SendRejectApprovalEMail($this->db, $emailManager, $_SESSION["currentUser"], 0);
		}
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
	
	/**
	 * Return if the Status can be aboarded by user
	 * @return boolean
	 */
	public function CanBeAboarded()
	{
		return true;
	}
	
}
?>