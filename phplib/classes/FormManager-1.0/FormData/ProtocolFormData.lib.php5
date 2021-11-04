<?php
/**
 * FormData-Implementierung für Protokolle
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class ProtocolFormData extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $DOMAIN_HTTP_ROOT, $SHARED_HTTP_ROOT;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "assignTask.png";
		$this->options["icontext"] = "Protokoll erstellen";
		// Protokollelemente
		$this->elements[] = new TextElement("emailto", "E-Mail Adresse Empfänger", $this->formElementValues["emailto"], true, $this->error["emailto"], false, new SearchTextDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 13, "param01" => AM_ADDRESSDATA_TYPE_NONE), "", "emailto"));
		$this->elements[count($this->elements)-1]->SetWidth(500);
		$this->elements[] = new TextElement("subject", "Betreff", $this->formElementValues["subject"], true, $this->error["subject"]);
		$this->elements[count($this->elements)-1]->SetWidth(500);
		$this->elements[] = new TextAreaElement("message", "Nachricht", $this->formElementValues["message"], true, $this->error["message"]);
		$this->elements[count($this->elements)-1]->SetWidth(500);
		$this->elements[count($this->elements)-1]->SetHeight(300);
		if (!isset($this->formElementValues["visibleFor"])) $this->formElementValues["visibleFor"]=-1;
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => -1);
		$options[]=Array("name" => "SFM Mitarbeiter", "value" => File::FM_FILE_INTERN_YES);
		$options[]=Array("name" => "Alle Benutzer", "value" => File::FM_FILE_INTERN_NO);
		$this->elements[] = new DropdownElement("visibleFor", "Protokoll sichtbar für", $this->formElementValues["visibleFor"], true, $this->error["visibleFor"], $options, false);
		$this->elements[] = new CustomHTMLElement("<input type='button' value='Email versenden' onClick='SendMail();' class='formButton2' />");
		// Protokolle 
		$protocolFiles = $this->obj->GetFiles($this->db, FM_FILE_SEMANTIC_PROTOCOL);
		ob_start();
		?>	<table width="600px" border="0" cellspacing="0" cellpadding="0">
			<?	for($a=count($protocolFiles)-1; $a>=0; $a--){ ?>
					<tr>
						<td width="120px"><?=date("d.m.Y H:i", $protocolFiles[$a]->GetCreationTime());?> Uhr</td>
						<td><a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($protocolFiles[$a])?>&timestamp=<?=time();?>"><?=$protocolFiles[$a]->GetFileName();?></a></td>
					</tr>
			<?	}
				if( count($protocolFiles)==0 ){?>
					<tr>
						<td>Keine Protokolle vorhanden</td>
					</tr>
			<?	} ?>
			</table>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT, "Protokoll-Historie");
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		global $SHARED_FILE_SYSTEM_ROOT;
		$this->errors = Array();
		if (trim($this->formElementValues["emailto"])=="") $this->error["emailto"] = "Bitte geben Sie die EMail-Adresse des Empfängers ein";
		if (trim($this->formElementValues["subject"])=="") $this->error["subject"] = "Bitte geben Sie den Betreff an";
		if (trim($this->formElementValues["message"])=="") $this->error["message"] = "Bitte geben Sie die Nachricht ein";
		if ((int)$this->formElementValues["visibleFor"]==-1) $this->error["visibleFor"] = "Bitte treffen sie eine Wahl";

		if (count($this->error)==0)
		{
			// PDF erzeugen
			ob_start();
			include($SHARED_FILE_SYSTEM_ROOT."templates/template_protocol.inc.php5");
			$html = ob_get_contents();
			ob_end_clean();
			include_once("html2pdf.php5");
			//echo $html;
			$pdfContent = convert_to_pdf($html, "A4", 1024, false, '', false, array('left' => 10, 'right' => 10, 'top' => 10, 'bottom' => 10) );
			//file_put_contents($SHARED_FILE_SYSTEM_ROOT.'test.pdf', $pdfContent);
			//exit;
			$file = FileManager::CreateFromStream($this->db, $pdfContent, FM_FILE_SEMANTIC_PROTOCOL, "Protokoll vom ".date("d.m.Y H.i")." Uhr.pdf", "pdf");
			if (is_object($file) && is_a($file, "File"))
			{
				$file->SetIntern($this->formElementValues["visibleFor"]==File::FM_FILE_INTERN_YES ? true : false);
				$file->Store($this->db);
				$returnValue = $this->obj->AddFile($this->db, $file);
				if ($returnValue===true) return true;
				$this->error["misc"][]="Protokoll konnte nicht gespeichert werden (Fehlercode: ".$returnValue.")";
			}
			else
			{
				$this->error["misc"][]="Protokoll konnte nicht erzeugt werden (Fehlercode: ".$file.")";
			}
		}
		return false;
	}
	
	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben nachdem das Formular ausgegeben wurde
	 */
	public function PostPrint()
	{
		?>
			<script type="text/javascript">
				<!--
					function SendMail() { 
						if ($('emailto').value=='' || $('subject').value=='' || $('message').value=='')
						{
							alert("Bitte füllen Sie alle Pflichtfelder aus.");
						}
						else
						{
							alert('Die E-Mail wird nun in Ihrem installierten E-Mail Client geöffnet. Bitte versenden Sie die E-Mail über dieses Programm.\n\nBitte vergessen Sie anschließend nicht das Protokoll in KIM zu speichern.');
							var link = "mailto:"+$('emailto').value
									+ "?subject=" + encodeURIComponent($('subject').value)
									+ "&body=" + encodeURIComponent($('message').value) 
							;
							window.location.href = link; 
						}
					} 

				-->
			</script>
		<?
	}
	
}
?>