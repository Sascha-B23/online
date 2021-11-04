<?php
/**
 * Status "Rechnung stellen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasRechnungStellen extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 0 Tage nach Erhalt
		$this->obj->SetDeadline(time());
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		/*@var $widerspruch Widerspruch*/
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		//$currency = ($widerspruch!=null ? $widerspruch->GetCurrency($this->db) : "");
		$currency = "EUR";
		if ($loadFromObject)
		{
			if ($widerspruch!=null)
			{
				$this->formElementValues["rechnungsdatum"] = $widerspruch->GetPaymentDate()==0 ? "" : date("d.m.Y", $widerspruch->GetPaymentDate());
				$this->formElementValues["rechnungsnummer"] = $widerspruch->GetPaymentNumber();
				$this->formElementValues["zahlungsfrist"] = $widerspruch->GetPaymentDaysOfGrace();
			}
			
			$tempData = unserialize($this->obj->GetAdditionalInfo());
			if (is_array($tempData))
			{
				$this->formElementValues["rechnungsempfaenger"] = $tempData['rechnungsempfaenger'];
				//$this->formElementValues["zahlungsfrist"] = $tempData['zahlungsfrist'];
				$this->formElementValues["realisierteEinsparungen"] = HelperLib::ConvertFloatToLocalizedString( $tempData['realisierteEinsparungen'] );
				$this->formElementValues["verguetungRealisierteEinsparungProzent"] = str_replace(".", ",",  $tempData['verguetungRealisierteEinsparungProzent']);
				$this->formElementValues["nichtRealisierteEinsparungen"] = HelperLib::ConvertFloatToLocalizedString( $tempData['nichtRealisierteEinsparungen'] );
				$this->formElementValues["verguetungNichtRealisierteEinsparungProzent"] = str_replace(".", ",", $tempData['verguetungNichtRealisierteEinsparungProzent']);
				$this->formElementValues["abschlagszahlungName"] = $tempData['abschlagszahlungName'];
				$this->formElementValues["abschlagszahlungHoehe"] = HelperLib::ConvertFloatToLocalizedString( $tempData['abschlagszahlungHoehe'] );
				$this->formElementValues["hinweisFuerKunde"] = $tempData['hinweisFuerKunde'];
			}
			$this->formElementValues["storeBill"] = true;
			if ((int)trim($this->formElementValues["zahlungsfrist"])=="")
			{
				$this->formElementValues["zahlungsfrist"] = "14";
			}
		}

		if (trim($this->formElementValues["rechnungsempfaenger"])=="")
		{
			$cPerson = $this->obj->GetResponsibleCustomer();
			if( $cPerson!=null )
			{
				$this->formElementValues["rechnungsempfaenger"] = $cPerson->GetAnrede($this->db, 3);
			}
		}
		if (trim($this->formElementValues["rechnungsdatum"])=="")
		{
			$this->formElementValues["rechnungsdatum"] = date("d.m.Y", time());
		}
		
		if (trim($this->formElementValues["realisierteEinsparungen"])=="")
		{
			
			$this->formElementValues["realisierteEinsparungen"] = HelperLib::ConvertFloatToLocalizedString($widerspruch==null ? 0.0 : $widerspruch->GetRealisierteEinsparung($this->db));
		}
		if (trim($this->formElementValues["verguetungRealisierteEinsparungProzent"])=="")
		{
			$this->formElementValues["verguetungRealisierteEinsparungProzent"] = 50;
		}
		if (trim($this->formElementValues["nichtRealisierteEinsparungen"])=="")
		{
			$this->formElementValues["nichtRealisierteEinsparungen"] = HelperLib::ConvertFloatToLocalizedString($widerspruch==null ? 0.0 : $widerspruch->GetNichtRealisierteEinsparung($this->db));
		}
		if (trim($this->formElementValues["verguetungNichtRealisierteEinsparungProzent"])=="")
		{
			$this->formElementValues["verguetungNichtRealisierteEinsparungProzent"] = 30;
		}
		if (trim($this->formElementValues["abschlagszahlungName"])=="")
		{
			$this->formElementValues["abschlagszahlungName"] = "";
		}
		if (trim($this->formElementValues["abschlagszahlungHoehe"])=="")
		{
			$this->formElementValues["abschlagszahlungHoehe"] = 0.0;
		}
		
		$this->elements[] = new TextElement("rechnungsempfaenger", "Rechnungsempfänger", $this->formElementValues["rechnungsempfaenger"], true, $this->error["rechnungsempfaenger"]);
		$this->elements[] = new DateElement("rechnungsdatum", "Rechnungsdatum", $this->formElementValues["rechnungsdatum"], true, $this->error["rechnungsdatum"]);
		$this->elements[] = new TextElement("rechnungsnummer", "Rechnungsnummer", $this->formElementValues["rechnungsnummer"], true, $this->error["rechnungsnummer"]);
		$this->elements[] = new TextElement("zahlungsfrist", "Zahlungsfrist (in Tage)", $this->formElementValues["zahlungsfrist"], true, $this->error["zahlungsfrist"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		$this->elements[] = new TextElement("realisierteEinsparungen", "Realisierte Einsparungen in ".$currency, $this->formElementValues["realisierteEinsparungen"], true, $this->error["realisierteEinsparungen"]);
		$this->elements[] = new TextElement("verguetungRealisierteEinsparungProzent", "Vergütung bei realisierter Einsparung in %", $this->formElementValues["verguetungRealisierteEinsparungProzent"], true, $this->error["verguetungRealisierteEinsparungProzent"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new TextElement("nichtRealisierteEinsparungen", "Nicht realisierte Einsparungen in ".$currency, $this->formElementValues["nichtRealisierteEinsparungen"], true, $this->error["nichtRealisierteEinsparungen"]);
		$this->elements[] = new TextElement("verguetungNichtRealisierteEinsparungProzent", "Vergütung bei nicht realisierter Einsparung in %", $this->formElementValues["verguetungNichtRealisierteEinsparungProzent"], true, $this->error["verguetungNichtRealisierteEinsparungProzent"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new TextElement("abschlagszahlungName", "Bezeichnung sonstige Rechnungspositionen", $this->formElementValues["abschlagszahlungName"], false, $this->error["abschlagszahlungName"]);
		$this->elements[] = new TextElement("abschlagszahlungHoehe", "Sonstige Rechnungspositionen in ".$currency, $this->formElementValues["abschlagszahlungHoehe"], false, $this->error["abschlagszahlungHoehe"]);
		$this->elements[] = new BlankElement();
		
		// Download
		ob_start();
		?>
			<strong>Bitte laden Sie sich die Rechnung herunter und drucken Sie diese aus:</strong><br>
			<input type="hidden" name="createDownloadFile" id="createDownloadFile" value="" />
			<input type="button" value="Rechnung als PDF herunterladen" onClick="DownloadFile();" class="formButton2"/>
			<?if( trim($this->error["createDocument"])!=""){?><br /><br /><div class="errorText"><?=$this->error["createDocument"];?></div><?}?>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Begründung
		$this->elements[] = new TextAreaElement("hinweisFuerKunde", "<br />Hinweise für Kunden", $this->formElementValues["hinweisFuerKunde"], false, $this->error["hinweisFuerKunde"]);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Speichern?
		$this->elements[] = new CheckboxElement("storeBill", "<br />Rechnung beim Abschließen der Aufgabe in Terminschiene speichern", $this->formElementValues["storeBill"], false, $this->error["storeBill"]);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		/*@var $widerspruch Widerspruch*/
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		if ($widerspruch==null)
		{
			$this->error["misc"][]="Es konnte kein Widerspruch gefunden werden";
			return false;
		}
		$timeStamp = DateElement::GetTimeStamp($this->formElementValues["rechnungsdatum"]);
		if ($timeStamp===false)
		{
			$this->error["rechnungsdatum"]="Bitte geben Sie das Rechnungsdatum im Format tt.mm.jjjj ein";
		}
		else
		{
			$widerspruch->SetPaymentDate((int)$timeStamp);
		}
		if (trim($this->formElementValues["rechnungsnummer"])=="")
		{
			$this->error["rechnungsnummer"]="Bitte geben Sie die Rechnungsnummer an";
		}
		else
		{
			$widerspruch->SetPaymentNumber($this->formElementValues["rechnungsnummer"]);
		}
		if ((int)trim($this->formElementValues["zahlungsfrist"])==0)
		{
			$this->error["zahlungsfrist"]="Bitte geben Sie die Zahlungsfrist in Tagen an (>0)";
		}
		else
		{
			$widerspruch->SetPaymentDaysOfGrace((int)$this->formElementValues["zahlungsfrist"]);
		}
		
		$dataToStore = Array();
		$tempData = unserialize($this->obj->GetAdditionalInfo());
		if (is_array($tempData))$dataToStore = $tempData;
		
		if (trim($this->formElementValues["rechnungsempfaenger"])==""){
			$this->error["rechnungsempfaenger"]="Bitte geben Sie den Rechnungsempfänger an";
		}else{
			$dataToStore['rechnungsempfaenger'] = $this->formElementValues["rechnungsempfaenger"];
		}
		// Vorauszahlung laut Buchhaltung
		$floatTemp=TextElement::GetFloat($this->formElementValues["realisierteEinsparungen"]);
		if( $floatTemp===false ){
			$this->error["realisierteEinsparungen"]="Bitte geben Sie einen gültigen Betrag ein";
		}else{
			$dataToStore['realisierteEinsparungen'] = $floatTemp;
		}
		$floatTemp=TextElement::GetFloat($this->formElementValues["verguetungRealisierteEinsparungProzent"]);
		if( $floatTemp===false ){
			$this->error["verguetungRealisierteEinsparungProzent"]="Bitte geben Sie einen gültigen Betrag ein";
		}else{
			$dataToStore['verguetungRealisierteEinsparungProzent'] = $floatTemp;
		}
		$floatTemp=TextElement::GetFloat($this->formElementValues["nichtRealisierteEinsparungen"]);
		if( $floatTemp===false ){
			$this->error["nichtRealisierteEinsparungen"]="Bitte geben Sie einen gültigen Betrag ein";
		}else{
			$dataToStore['nichtRealisierteEinsparungen'] = $floatTemp;
		}
		$floatTemp=TextElement::GetFloat($this->formElementValues["verguetungNichtRealisierteEinsparungProzent"]);
		if( $floatTemp===false ){
			$this->error["verguetungNichtRealisierteEinsparungProzent"]="Bitte geben Sie einen gültigen Betrag ein";
		}else{
			$dataToStore['verguetungNichtRealisierteEinsparungProzent'] = $floatTemp;
		}
		$dataToStore['abschlagszahlungName'] = $this->formElementValues["abschlagszahlungName"];
		$floatTemp=TextElement::GetFloat($this->formElementValues["abschlagszahlungHoehe"]);
		if( $floatTemp===false ){
			$this->error["abschlagszahlungHoehe"]="Bitte geben Sie einen gültigen Betrag ein";
		}else{
			$dataToStore['abschlagszahlungHoehe'] = $floatTemp;
			if ($floatTemp!=0.0 && trim($dataToStore['abschlagszahlungName'])=="")
			{
				$this->error["abschlagszahlungName"]="Bitte geben Sie eine Bezeichnung der sonstigen Rechnungspositionen ein";
			}
		}
		$dataToStore['hinweisFuerKunde'] = $this->formElementValues["hinweisFuerKunde"];
		
		$returnValue = $widerspruch->Store($this->db);
		if ($returnValue!==true)
		{
			$this->error["misc"][]="Systemfehler beim Speichern des Widerspruchs (".$returnValue.")";
			return false;
		}
		
		if (count($this->error)==0)
		{
			$this->obj->SetAdditionalInfo(serialize($dataToStore));
			$returnValue = $this->obj->Store($this->db);
			if ($returnValue!==true)
			{
				$this->error["misc"][]="Systemfehler (".$returnValue.")";
				return false;
			}
			if (!$gotoNextStatus) return true;
			// PDF erzeugen und in Terminschiene speichern
			if ($this->formElementValues["storeBill"]=='on')
			{
				$tempData = unserialize($this->obj->GetAdditionalInfo());
				$result = $widerspruch->CreateDocument($this->db, DOCUMENT_TYPE_PDF, DOCUMENT_TYPE_RECHNUNG, $tempData);
				if (is_int($result))
				{
					$this->error["createDocument"] = "Dokument konnte nicht erzeugt werden (".$result.")";
				}
				else
				{
					// TODO: how to add bill to 'Terminschiene'??
					$file = FileManager::CreateFromStream($this->db, $result, FM_FILE_SEMANTIC_WIDERSPRUCH_RECHNUNG, "Rechnung ".$widerspruch->GetPaymentNumber()." vom ".date("d.m.Y", $widerspruch->GetPaymentDate()).".pdf", "pdf");
					if (is_object($file) && is_a($file, "File"))
					{
						$file->SetIntern(true);
						$file->Store($this->db);
						$returnValue = $widerspruch->AddFile($this->db, $file);
						if ($returnValue!==true) $this->error["misc"][]="Rechnung konnte nicht gespeichert werden (Fehlercode: ".$returnValue.")";
					}
					else
					{
						$this->error["misc"][]="Rechnungsdatei konnte nicht erzeugt werden (Fehlercode: ".$file.")";
					}
				}
			}
			if (count($this->error)==0) return true;
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
			if ($this->Store(false)===true)
			{
				$widerspruch=$this->obj->GetWiderspruch($this->db);
				// Includes
				if ($widerspruch!=null)
				{
					$tempData = unserialize($this->obj->GetAdditionalInfo());
					if (is_array($tempData))
					{
						// Includes
						$result=$widerspruch->CreateDocument($this->db, DOCUMENT_TYPE_PDF, DOCUMENT_TYPE_RECHNUNG, $tempData);
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
							header("Content-Disposition: attachment; filename=\"Rechnung_".$this->formElementValues["rechnungsnummer"].".".$result["extension"]."\"");
							header("Content-Length: ".(string)strlen($result["content"]));
							echo $result["content"];
							exit;
						}
					}
					else
					{
						$this->error["createDocument"]="Dokument konnte nicht erzeugt werden (-99)";
					}
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
				function DownloadFile(){
					document.forms.FM_FORM.createDownloadFile.value="true"; 
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