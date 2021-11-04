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
class NkasWiderspruchFreigabeFMS extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 1 Tage nach Erhalt
		$this->obj->SetDeadline(time()+60*60*36);
		$this->obj->Store($this->db);
		// RueckweisungsBegruendung erzeugen
		$rueckweisungsBegruendung=new RueckweisungsBegruendung($this->db);
		$rueckweisungsBegruendung->SetType(RueckweisungsBegruendung::RWB_TYPE_WIDERSPRUCH);
		$rueckweisungsBegruendung->SetDatum( time() );
		$rueckweisungsBegruendung->SetWiderspruch( $this->obj->GetWiderspruch($this->db) );
		$rueckweisungsBegruendung->SetUser( $_SESSION["currentUser"] );
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
		$showAttachment = true;
		if ($loadFromObject)
		{
			$widerspruch=$this->obj->GetWiderspruch($this->db);
			if ($widerspruch!=null)
			{
				$showAttachment = !$widerspruch->GetHideAttachemntFromCustomer();
				$rueckweisungsBegruendung=$widerspruch->GetLastRueckweisungsBegruendung($this->db, RueckweisungsBegruendung::RWB_TYPE_WIDERSPRUCH);
				if( $rueckweisungsBegruendung!=null )$this->formElementValues["ablehnungsbegruendung"]=$rueckweisungsBegruendung->GetBegruendung();
				$this->formElementValues["customerComment"]=$widerspruch->GetBemerkungFuerKunde();
			}
		}
		// In Gruppe ist der automatische WS nicht verfügbar
		if ($this->IsProcessGroup()) $showAttachment = false;
		// Vorschau
		ob_start();
		?>
			<strong>Widerspruch</strong><br>
			<input type="hidden" name="createDownloadFile" id="createDownloadFile" value="" />
			<input type="button" value="Vorschau Anschreiben" onClick="DownloadFile(<?=DOCUMENT_TYPE_ANSCHREIBEN;?>);" class="formButton2"/><?if($showAttachment){?>&#160;&#160;&#160;<input type="button" value="Vorschau Anlage" onClick="DownloadFile(<?=DOCUMENT_TYPE_ANHANG;?>);" class="formButton2"/><?}?>
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
				$this->elements[] = new FileElement("wFile", "<br/>Zusätzliche Anlagen", $this->formElementValues["wFile"], false, $this->error["wFile"], FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE, $additionalFiles, false, false, false, false);
				$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
				$this->elements[] = new BlankElement();
				$this->elements[] = new BlankElement();
			}
		}
		// Bemerkungsfeld für den Kunden
		$this->elements[] = new TextAreaElement("customerComment", "<br/>Bemerkungsfeld für den Kunden", $this->formElementValues["customerComment"], false, $this->error["customerComment"]);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
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
			$this->error["misc"][]="Dieser Widerspruch kann nur von ".$this->obj->GetZuweisungUser()->GetUserName()." freigegeben werden.";
			return false;
		}
		// AbbruchProtokoll
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			// Bemerkung Kunde
			$widerspruch->SetBemerkungFuerKunde($this->formElementValues["customerComment"]);
			$returnValue=$widerspruch->Store($this->db);
			if( $returnValue===true ){
				$rueckweisungsBegruendung=$widerspruch->GetLastRueckweisungsBegruendung($this->db, RueckweisungsBegruendung::RWB_TYPE_WIDERSPRUCH);
				$rueckweisungsBegruendung->SetBegruendung($this->formElementValues["ablehnungsbegruendung"]);
				$rueckweisungsBegruendung->SetDatum( trim($this->formElementValues["ablehnungsbegruendung"])=="" ? 0 : time() );
				$rueckweisungsBegruendung->SetUserRelease( $_SESSION["currentUser"] );
				$returnValue=$rueckweisungsBegruendung->Store($this->db);
				if( $returnValue===true ){
					// Prüfen, ob in nächsten Status gesprungen werden kann
					if( $gotoNextStatus ){
						if( $this->formElementValues["abbruchAktion"]==2 && trim($rueckweisungsBegruendung->GetBegruendung())=="" )$this->error["ablehnungsbegruendung"]="Bitte geben Sie eine Begründung für die Zurückweisung an.";
						elseif( $this->formElementValues["abbruchAktion"]!=1 && $this->formElementValues["abbruchAktion"]!=2 )$this->error["abbruchAktion"]="Um die Aufgabe abschließen zu können, müssen Sie eine Aktion auswählen.";
					}
				}else{
					$this->error["misc"][]="Systemfehler (1/".$returnValue.")";
				}
			}else{
				$this->error["misc"][]="Systemfehler (2/".$returnValue.")";
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
		if( trim($_POST["createDownloadFile"])!="" ){
			// Mögliche Änderungen übernehmen
			$this->Store(false);
			$widerspruch=$this->obj->GetWiderspruch($this->db);
			// Includes
			if ($widerspruch!=null)
			{
				$result=$widerspruch->CreateDocument($this->db, (int)$_POST["createDownloadFile"]==DOCUMENT_TYPE_ANSCHREIBEN ? DOCUMENT_TYPE_RTF : DOCUMENT_TYPE_PDF, (int)$_POST["createDownloadFile"]==DOCUMENT_TYPE_ANSCHREIBEN ? DOCUMENT_TYPE_ANSCHREIBEN : DOCUMENT_TYPE_ANHANG );
				if (is_int($result))
				{
					$this->error["createDocument"]="Dokument konnte nicht erzeugt werden (".$result.")";
				}
				else
				{
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
		global $DOMAIN_HTTP_ROOT;
		?>
		<script type="text/javascript">
			<!--
				$('abbruchAktion').onchange= function(){ $('ablehnungsbegruendung').disabled = ($('abbruchAktion').value=="2" ? false : true); };
				
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
		$this->obj->SetZuweisungUser(null);
		if ($this->formElementValues["abbruchAktion"]==2)
		{
			// E-Mail versenden
			global $emailManager;
			$this->obj->SendRejectApprovalEMail($this->db, $emailManager, $_SESSION["currentUser"], 0);
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