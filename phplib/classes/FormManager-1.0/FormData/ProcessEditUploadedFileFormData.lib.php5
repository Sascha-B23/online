<?php
/**
 * FormData-Implementierung für "Aufgabe zuweisen"
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class ProcessEditUploadedFileFormData extends FormData {
		
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $FM_DESCRIPTIONS_FOR_SEMANTIC, $DOMAIN_HTTP_ROOT;
		$assignments = Array(FM_FILE_SEMANTIC_PROTOCOL, FM_FILE_SEMANTIC_WIDERSPRUCH, FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE, FM_FILE_SEMANTIC_ABRECHNUNGSKORREKTURGUTSCHRIFT, FM_FILE_SEMANTIC_NACHTRAG, FM_FILE_SEMANTIC_SONSTIGES); 
		// Icon und Überschrift festlegen
		$this->options["icon"] = "filesuploaded.png";
		$this->options["icontext"] = "Dateien zuordnen";
		/*@var $this->obj WorkflowStatus*/
		
		if (isset($_POST["deleteFile"]) && $_POST["deleteFile"]!="")
		{
			$files = $this->obj->GetFiles($this->db, FM_FILE_SEMANTIC_NEWCUSTOMERFILE);
			foreach ($files as $file)
			{
				if ($file->GetPKey()==(int)$_POST["deleteFile"])
				{
					$this->obj->RemoveFile($this->db, $file);
				}
			}
		}
		
		$files = $this->obj->GetFiles($this->db, FM_FILE_SEMANTIC_NEWCUSTOMERFILE);
		$widersprueche = $this->obj->GetWidersprueche($this->db);
		
		if ($loadFromObject)
		{

		}
		
		ob_start();
		?>
		<script>
			/**
			 * Enable or disable WS-dropdown 
			 */
			function OnSelectFileType(dropdown_semantic, dropdown_ws)
			{
				if (dropdown_semantic.value==0 || dropdown_semantic.value==<?=FM_FILE_SEMANTIC_PROTOCOL;?>)
				{
					dropdown_ws.disabled=true;
				}
				else
				{
					dropdown_ws.disabled=false;
				}
			}
		</script>
		<input type="hidden" id="deleteFile" name="deleteFile" value=""/>
		<table>
			<tr>
				<td>Dateiname</td>
				<td>Zuordnung Typ</td>
				<td>Zuordnung Widerspruch</td>
				<td>Kommentar</td>
				<td>Optionen</td>
			</tr>
		<?	foreach ($files as $file){ ?>
				<tr>
					<td><a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($file)?>&timestamp=<?=time();?>"><?=$file->GetFileName();?></a></td>
					<td>
						<select name="new_semantic_<?=$file->GetPKey();?>" id="new_semantic_<?=$file->GetPKey();?>" onchange="OnSelectFileType($('new_semantic_<?=$file->GetPKey();?>'), $('ws_<?=$file->GetPKey();?>'));">
							<option value="0">Bitte wählen...</option>
						<?	foreach ($assignments as $value) {?>
								<option value="<?=$value;?>" <?if($_POST["new_semantic_".$file->GetPKey()]==$value) echo "selected";?> ><?=$FM_DESCRIPTIONS_FOR_SEMANTIC[$value]["short"]." ".$FM_DESCRIPTIONS_FOR_SEMANTIC[$value]["long"];?></option>
						<?	}?>
						</select>
					</td>
					<td>
						<select name="ws_<?=$file->GetPKey();?>" id="ws_<?=$file->GetPKey();?>">
							<option value="0">Bitte wählen...</option>
						<?	foreach ($widersprueche as $widerspruch) {?>
								<option value="<?=$widerspruch->GetPKey();?>" <?if($_POST["ws_".$file->GetPKey()]==$widerspruch->GetPKey()) echo "selected";?> >Widerspruch <?=$widerspruch->GetWiderspruchsNummer();?></option>
						<?	}?>
						</select>
						<script>
							OnSelectFileType($('new_semantic_<?=$file->GetPKey();?>'), $('ws_<?=$file->GetPKey();?>'));
						</script>
					</td>
					<td>
						<input type="text" name="description_<?=$file->GetPKey();?>" id="description_<?=$file->GetPKey();?>" value="<?php echo $_POST["description_".$file->GetPKey()]; ?>" />
					</td>
					<td>
						<a href="javascript:showDialogWindow(263, 163, 'url(<?=$DOMAIN_HTTP_ROOT;?>pics/dialog/dialog_mitte_links.gif)', 'Warnhinweis', 'Möchten Sie die Datei wirklich löschen?', 'Ja, Datei löschen', 'javascript:document.forms.FM_FORM.forwardToListView.value=false; document.forms.FM_FORM.deleteFile.value=<?=$file->GetPKey();?>; document.forms.FM_FORM.submit();', '', '', '', '');">[Löschen]</a>
					</td>
				</tr>
			<?	if($this->error["ws_".$file->GetPKey()]!=""){ ?>
					<tr>
						<td>&#160;</td>
						<td colspan="3" class="errorText">
							<?=$this->error["ws_".$file->GetPKey()];?>
						</td>
					</tr>
			<?	}?>
			<?	if($this->error["description_".$file->GetPKey()]!=""){ ?>
					<tr>
						<td>&#160;</td>
						<td colspan="3" class="errorText">
							<?=$this->error["description_".$file->GetPKey()];?>
						</td>
					</tr>
			<?	}?>
		<?	}?>
        <?  if($this->obj->GetCustomerFileUploadComment()!=""){?>
                <tr>
                    <td colspan="4"><br />Kommentar</td>
                </tr>
                <tr>
                    <td colspan="4"><i><?=str_replace("\n", "<br />", $this->obj->GetCustomerFileUploadComment());?></i></td>
                </tr>
        <?  }?>
		</table>	
		<?
		$htmlCode = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($htmlCode);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		global $FM_DESCRIPTIONS_FOR_SEMANTIC;
		$returnValue = true;
		$files = $this->obj->GetFiles($this->db, FM_FILE_SEMANTIC_NEWCUSTOMERFILE);
		/*@var $files File[]*/
		$widersprueche = $this->obj->GetWidersprueche($this->db);
		/*@var $widersprueche Widerspruch[]*/
		foreach ($files as $file)
		{
			$newSemantic = (int)$_POST["new_semantic_".$file->GetPKey()];
			$wsId = (int)$_POST["ws_".$file->GetPKey()];
			$description = $_POST["description_".$file->GetPKey()];
			$descriptionError = false;
			// Kommentar
			if (strlen($description)>50)
			{
				$this->error["description_".$file->GetPKey()] = "Bitte kürzen Sie Ihren Kommentar auf maximal 50 Zeichen.";
				$descriptionError = true;
			}
			else
			{
				$file->setDescription($description);
			}

			if ($newSemantic!=0)
			{
				// get selected WS
				$selectedWiderspruch = null;
				foreach ($widersprueche as $widerspruch)
				{
					if ($widerspruch->GetPKey()==$wsId)
					{
						$selectedWiderspruch = $widerspruch;
						break;
					}
				}

				
				// check if ws is selected if required
				if ($newSemantic!=FM_FILE_SEMANTIC_PROTOCOL && $selectedWiderspruch==null)
				{
					$this->error["ws_".$file->GetPKey()] = "Bitte wählen Sie den Widerspruch aus, dem Sie dieses Dokument zuordnen möchten.";
					$returnValue = false;
				}
				else if (!$descriptionError)
				{
					// set new file semantic
					$file->SetFileSemantic($newSemantic);
					// move file to new object if required
					switch($newSemantic)
					{
						case FM_FILE_SEMANTIC_PROTOCOL:
							// Nothing special to do here because protocols belong to the process object too
							// store new semantic of file
							$file->Store($this->db);
							break;
						case FM_FILE_SEMANTIC_WIDERSPRUCH:
						case FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE:
							// add file to ws
							if ($selectedWiderspruch->AddFile($this->db, $file)===true)
							{
								// remove file from prozess
								$this->obj->RemoveFile($this->db, $file);
								// store new semantic of file
								$file->Store($this->db);
							}
							else
							{
								$this->error["ws_".$file->GetPKey()] = "Datei konnte dem Widerspruch nicht zugeordnet werden";
								$returnValue = false;
							}
							break;
						case FM_FILE_SEMANTIC_ABRECHNUNGSKORREKTURGUTSCHRIFT:
						case FM_FILE_SEMANTIC_NACHTRAG:
						case FM_FILE_SEMANTIC_SONSTIGES:
							// create new AW-object
							$aw = new Antwortschreiben($this->db);
							$aw->SetWiderspruch($selectedWiderspruch);
							$aw->SetDatum(time());
							$returnValue = $aw->Store($this->db);
							if ($returnValue===true)
							{
								// add file to aw
								if ($aw->AddFile($this->db, $file))
								{
									// remove file from prozess
									$this->obj->RemoveFile($this->db, $file);
									// store new semantic of file
									$file->Store($this->db);
								}
								else
								{
									// delete aw
									$aw->DeleteMe($this->db);
									$this->error["ws_".$file->GetPKey()] = "Datei konnte dem Antwortschreiben nicht zugeordnet werden";
									$returnValue = false;
								}
							}
							else
							{
								$this->error["ws_".$file->GetPKey()] = "Antwortschreiben konnte nicht angelegt werden (Code: ".$returnValue.")";
								$returnValue = false;
							}
							break;
						default:
							$returnValue = false;
							$this->error["ws_".$file->GetPKey()] = "Die Datei kann nicht als '".$FM_DESCRIPTIONS_FOR_SEMANTIC[$newSemantic]["short"]." ".$FM_DESCRIPTIONS_FOR_SEMANTIC[$newSemantic]["long"]."' zugeordnet werden.";
							break;
					}
				}
			}
		}
		// only close window if nomore unclassified files are 
		$files = $this->obj->GetFiles($this->db, FM_FILE_SEMANTIC_NEWCUSTOMERFILE);
		if (count($files)>0) return false;

        // delete comment if no more files are here
        $this->obj->SetCustomerFileUploadComment("");
        $this->obj->Store($this->db);

		return $returnValue;
	}

	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben nachdem das Formular ausgegeben wurde
	 */
	public function PostPrint(){}
	
}
?>