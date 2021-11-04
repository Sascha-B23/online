<?php
/**
 * Status "Widerspruch/Folgewiderspruch/Protokoll drucken und versenden"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasWiderspruchDruckenUndVersenden extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Die Bearbeitungsfrist läuft vom Vorherigen Status "Widerspruch durch Kunde freigeben" weiter!
		//$this->obj->SetDeadline( time()+60*60*24*7 );
		//$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		// Widerspruch-Objekt holen
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		$showAttachment = true;
		if( $loadFromObject ){
			if( $widerspruch!=null ){
				$showAttachment = !$widerspruch->GetHideAttachemntFromCustomer();
				$this->formElementValues["footer"]=$widerspruch->GetFooter();
				$this->formElementValues["versenderkuerzel"]=$widerspruch->GetVersenderkuerzel();
				$this->formElementValues["unterschrift1"]=$widerspruch->GetUnterschrift1();
				$this->formElementValues["unterschrift2"]=$widerspruch->GetUnterschrift2();
			}
		}
		// In Gruppe ist der automatische WS nicht verfügbar
		if ($this->IsProcessGroup()) $showAttachment = false;
		// Bemerkung
		ob_start();
		$bemerkung = ($widerspruch!=null ? str_replace("\n", "<br/>", $widerspruch->GetBemerkungFuerKunde()) : "");
		if( trim($bemerkung)!="" ){ ?>	
			<table width="100%" border="0" cellpadding="0" cellspacing="10" bgcolor="#ffffff">
				<tr>
					<td>
						<strong>Bemerkung</strong><br><br>
						<?=$bemerkung;?>
					</td>
				</tr>
			</table><?
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		// Footer
		ob_start();
		?>
			<strong>1. Tragen Sie Ihr Versenderkürzel ein und passen Sie ggf. die Namen der Personen an, die den Widerspruch unterschreiben müssen:</strong><br>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		//$this->elements[] = new TextAreaElement("footer", "Fußzeile des Widerspruchs", $this->formElementValues["footer"], false, $this->error["footer"]);
		//$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		//$this->elements[count($this->elements)-1]->SetWidth(800);
		// 
		$this->elements[] = new TextElement("versenderkuerzel", "Versenderkürzel", $this->formElementValues["versenderkuerzel"], false, $this->error["versenderkuerzel"]);
		$this->elements[] = new TextElement("unterschrift1", "Unterschrift 1", $this->formElementValues["unterschrift1"], false, $this->error["unterschrift1"]);
		$this->elements[] = new TextElement("unterschrift2", "Unterschrift 2", $this->formElementValues["unterschrift2"], false, $this->error["unterschrift2"]);
		// Download
		//$this->elements[] = new BlankElement();
		//$this->elements[] = new BlankElement();		
		ob_start();
		?>
			<strong>2. Laden Sie sich das Anschreiben und die Anlage(n) herunter und drucken Sie diese aus:</strong><br>
			<input type="hidden" name="createDownloadFile" id="createDownloadFile" value="" />
			<input type="button" value="Anschreiben" onClick="DownloadFile(<?=DOCUMENT_TYPE_ANSCHREIBEN;?>); this.disabled=true;" class="formButton2"/><?if($showAttachment){?>&#160;&#160;&#160;<input type="button" value="Anlage" onClick="DownloadFile(<?=DOCUMENT_TYPE_ANHANG;?>);  this.disabled=true;" class="formButton2" /><?}?>
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
		// Upload
		ob_start();
		?>
	<strong>3. Scannen Sie das unterschriebene Anschreiben inkl. der Anlage ein und laden Sie es hoch:</strong><br>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		if( $widerspruch!=null && isset($_POST["deleteFile_uploadFile"]) && $_POST["deleteFile_uploadFile"]!="" ){
			$fileToDelete=new File($this->db);
			if( $fileToDelete->Load((int)$_POST["deleteFile_uploadFile"], $this->db) ){
				$widerspruch->RemoveFile($this->db, $fileToDelete);
			}
		}
		$this->elements[] = new FileElement("uploadFile", "Unterschriebenes Dokument", $this->formElementValues["uploadFile"], true, $this->error["uploadFile"], FM_FILE_SEMANTIC_WIDERSPRUCH, $widerspruch!=null ? $widerspruch->GetFiles($this->db, FM_FILE_SEMANTIC_WIDERSPRUCH) : Array() );
		if( $_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE){
			$this->elements[] = new DateAndTimeElement("uploadFileTime", "Datum und Uhrzeit der Datei überschreiben", Array("date" => $this->formElementValues["uploadFileTime"], "time" => $this->formElementValues["uploadFileTime_clock"]), false, $this->error["uploadFileTime"]);
		}else{
			$this->elements[] = new BlankElement();
		}
		$this->elements[] = new BlankElement();
		// Abschließen
		ob_start();
		?>
			<strong>4. Versenden Sie das unterschriebene Anschreiben mit der Anlage an die angegebene Adresse und schließen Sie diese Aufgabe über den Button "Aufgabe abschließen" ab.</strong><br>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
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
		// Beliebige PDF-Dokumente die dem Widerspruch zugeordnet werden sollen
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		if( $widerspruch==null )return false;
		$widerspruch->SetFooter($this->formElementValues["footer"]);
		$widerspruch->SetVersenderkuerzel($this->formElementValues["versenderkuerzel"]);
		$widerspruch->SetUnterschrift1($this->formElementValues["unterschrift1"]);
		$widerspruch->SetUnterschrift2($this->formElementValues["unterschrift2"]);
		$fileObject=FileElement::GetFileElement($this->db, $_FILES["uploadFile"], FM_FILE_SEMANTIC_WIDERSPRUCH);
		if (!is_object($fileObject) || get_class($fileObject)!="File")
		{
			if ($fileObject!=-1)
			{
				$this->error["uploadFile"]=FileElement::GetErrorText($fileObject);
			}
		}
		else
		{
			/*@var $fileObject File*/
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE && (trim($this->formElementValues["uploadFileTime"])!="" || trim($this->formElementValues["uploadFileTime_clock"])!=""))
			{
				// Datum und Uhrzeit der Datei 
				$uploadFileTime = DateAndTimeElement::GetTimeStamp($this->formElementValues["uploadFileTime"], $this->formElementValues["uploadFileTime_clock"]);

				if ($uploadFileTime===false)
				{
					$this->error["uploadFileTime"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj und eine gültige Uhrzeit im Format hh:mm ein";
				}
				else
				{
					if ($uploadFileTime>time())
					{
						$this->error["uploadFileTime"]="Der Zeitpunkt darf nicht in der Zukunft liegen";
					}
					else
					{
						$fileObject->SetCreationTime($uploadFileTime);
					}
				}
			}
			// Datei wieder löschen, wenn ein Fehler
			if (count($this->error)>0)
			{
				$fileObject->DeleteMe($this->db);
			}
			else
			{
				// Log WS upload if user is a customer
				if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_KUNDE)
				{
					LoggingManager::GetInstance()->Log(new LoggingWsCustomerUpload($this->obj->GetProzessPath()[0]['path'], $fileObject->GetFileName()));
				}
				$widerspruch->AddFile($this->db, $fileObject);
			}
		}
		// Fehler aufgetreten?
		if( count($this->error)==0 ){
			$returnValue=$widerspruch->Store($this->db);
			if( $returnValue===true ){
				unset($this->formElementValues["uploadFileTime"]);
				unset($this->formElementValues["uploadFileTime_clock"]);
				if( !$gotoNextStatus )return true;
				// Prüfen, ob in nächsten Status gesprungen werden kann
				if( $widerspruch->GetFileCount($this->db, FM_FILE_SEMANTIC_WIDERSPRUCH)==0 )$this->error["uploadFile"]="Um die Aufgabe abschließen zu können, müssen Sie zunächst das unterschriebene Dokument hochladen.";
				if (count($this->error)==0)
				{
					// Festlegen, wohin gesprungen werden soll (wurde in Status 4 "Widerspruch/Protokoll erzeugen" festgelegt)
					$this->formElementValues["useBranchAfterStatus21"] = 0;
					$tempData = unserialize($this->obj->GetAdditionalInfo());
					if (is_array($tempData))
					{
						$this->formElementValues["useBranchAfterStatus21"] = (int)$tempData["useBranchAfterStatus21"];
					}
					// Wenn in Status 17 gesprungen wird...
					//echo date("d.m.Y", $this->obj->GetRueckstellungBis());
					//return false;
					$currentProcessInfo = WorkflowManager::GetProzessStatusForStatusID(21);
					if ($currentProcessInfo["nextStatusIDs"][$this->formElementValues["useBranchAfterStatus21"]]==17)
					{
						// ...Frist für Status 17 setzen (=Abschlussdatum Prozess)
						$this->obj->SetRueckstellungBis($this->obj->GetAbschlussdatum());
						$returnValue = $this->obj->Store($this->db);
						if ($returnValue!==true)
						{
							$this->error["misc"][]="Systemfehler beim Festlegen der Frist (".$returnValue.")";
							return false;
						}
					}
					return true;
				}
			}else{
				$this->error["misc"][]="Systemfehler in Objekt Widerspruch (".$returnValue.")";
			}
		}
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
				function DownloadFile(format){
					document.forms.FM_FORM.createDownloadFile.value=format; 
					document.forms.FM_FORM.submit()
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
		return (int)$this->formElementValues["useBranchAfterStatus21"];
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