<?php
/**
 * Status "Eigentümer-/Verwalter-/Anwalt-Schreiben klassifizieren"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasSchreibenKlassifizieren extends NkasStatusFormDataEntry
{
	
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
		global $DOMAIN_HTTP_ROOT;
		// Aktuelles Antwortschrieben holen
		$awSchreiben=null;
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			$awSchreiben = $widerspruch->GetLastAntwortschreiben($this->db);
		}
		if ($awSchreiben==null) return;
		ob_start(); ?>
		<strong>Dokumente vom <?= date("d.m.Y", $awSchreiben->GetDatum()); ?>:</strong>
		<table width="100%">
			<tr>
				<td valign="top"><strong>Datei</strong></td>
				<td valign="top"><strong>Dokumententyp</strong></td>
			</tr>
			<? $files = $awSchreiben->GetFiles($this->db);
			if (count($files) > 0) {
				for ($a = 0; $a < count($files); $a++) {
					?>
					<tr>
						<td valign="top"><a
								href="<?= $DOMAIN_HTTP_ROOT; ?>templates/download_file.php5?<?= SID; ?>&code=<?= $_SESSION['fileDownloadManager']->AddDownloadFile($files[$a]) ?>&timestamp=<?= time(); ?>"><?= $files[$a]->GetFileName(); ?></a>
						</td>
						<td valign="top">
							<select type="select" name="file_<?= $files[$a]->GetPKey(); ?>[]" multiple>
								<option
									value="<?= FM_FILE_SEMANTIC_ANTWORTSCHREIBEN; ?>" <? if ($files[$a]->GetFileSemantic() & FM_FILE_SEMANTIC_ANTWORTSCHREIBEN) echo "selected"; ?>>
									Antwortschreiben
								</option>
								<option
									value="<?= FM_FILE_SEMANTIC_ABRECHNUNGSKORREKTURGUTSCHRIFT; ?>" <? if ($files[$a]->GetFileSemantic() & FM_FILE_SEMANTIC_ABRECHNUNGSKORREKTURGUTSCHRIFT) echo "selected"; ?>>
									Abrechnungskorrektur/Gutschrift
								</option>
								<option
									value="<?= FM_FILE_SEMANTIC_NACHTRAG; ?>" <? if ($files[$a]->GetFileSemantic() & FM_FILE_SEMANTIC_NACHTRAG) echo "selected"; ?>>
									Nachtrag
								</option>
								<option
									value="<?= FM_FILE_SEMANTIC_SONSTIGES; ?>" <? if ($files[$a]->GetFileSemantic() & FM_FILE_SEMANTIC_SONSTIGES) echo "selected"; ?>>
									Sonstiges
								</option>
							</select>
						</td>
					</tr>
				<? }
			} else { ?>
				<tr>
					<td colspan="4">Keine Dokumente vorhanden</td>
				</tr>
			<? } ?>
		</table>
		<br/>
<? 		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);		
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		$awSchreiben=null;
		$widerspruch=$this->obj->GetWiderspruch($this->db);
		if ($widerspruch!=null)
		{
			$awSchreiben=$widerspruch->GetLastAntwortschreiben($this->db);
		}
		if ($awSchreiben==null) return false;
		
		$files=$awSchreiben->GetFiles($this->db);
		for ($a=0; $a<count($files); $a++)
		{ 
			if (isset($_POST["file_".$files[$a]->GetPKey()]) && is_array($_POST["file_".$files[$a]->GetPKey()]))
			{
				$newType=FM_FILE_SEMANTIC_UNKNOWN;
				for ($b=0; $b<count($_POST["file_".$files[$a]->GetPKey()]); $b++)
				{
					$newType=$newType | ((int)$_POST["file_".$files[$a]->GetPKey()][$b]);
				}
				if ($files[$a]->GetFileSemantic()!=$newType)
				{
					$files[$a]->SetFileSemantic($newType);
					$returnValue=$files[$a]->Store($this->db);
					if ($returnValue!==true)
					{
						$this->error["misc"][]="Systemfehler beim setzen des Dokumententyps (".$returnValue.")";
					}
				}
			}
			else
			{
				$newType=FM_FILE_SEMANTIC_UNKNOWN;
				if ($files[$a]->GetFileSemantic()!=$newType)
				{
					$files[$a]->SetFileSemantic($newType);
					$returnValue=$files[$a]->Store($this->db);
					if ($returnValue!==true)
					{
						$this->error["misc"][]="Systemfehler beim setzen des Dokumententyps (".$returnValue.")";
					}
				}
			}
		}
		if (count($this->error)==0)
		{
			if ($gotoNextStatus)
			{
				// Prüfen ob alle Dokumente klassifiziert wurden...
				for ($a=0; $a<count($files); $a++)
				{ 
					if ($files[$a]->GetFileSemantic()==FM_FILE_SEMANTIC_UNKNOWN)
					{
						$this->error["misc"][]="Um die Aufgabe abschließen zu können, müssen Sie alle Dokumente klassifizieren.";
						return false;
					}
				}
			}
			return true;
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