<?php
/**
 * Status "Buchhaltung informieren"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasBuchhaltungInformieren extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 7 Tage nach Erhalt
		$this->obj->SetDeadline(time()+60*60*24*7);
		$this->obj->Store($this->db);
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		$tempData = unserialize($this->obj->GetAdditionalInfo());
		if (is_array($tempData))
		{
			$this->formElementValues["buchhaltungEmail"] = $tempData["buchhaltungEmail"];
			$this->formElementValues["buchhaltungEmailText"] = $tempData["buchhaltungEmailText"];
		}
		$apName = "";
		$apUserName = "";
		$cAccountingPersonUserName = "Sehr geehrte(r) [Name Ansprechpartner Buchhaltung]";
		$realisierteEinsparungen = "0,00";
		$nichtRealisierteEinsparungen = "0,00";
		$einsparungen = "0,00";
		$abschlagszahlungName ="";
		$abschlagszahlungHoehe = "";
		$currency = $this->obj->GetCurrency();
		$cPerson = $this->obj->GetResponsibleCustomer();
		if ($cPerson!=null)
		{
			$apName = $cPerson->GetAnrede($this->db, 1);
			$apUserName = $cPerson->GetUserName();
		}
		$rsPerson = $this->obj->GetResponsibleRSUser();
		if ($rsPerson!=null)
		{
			$rsUserName = $rsPerson->GetUserName();
		}
		
		$cAccountingPerson = $this->obj->GetResponsibleCustomerAccounting();
		if ($cAccountingPerson!=null)
		{
			$cAccountingPersonUserName = $cAccountingPerson->GetAnrede($this->db, 1);
			if (trim($this->formElementValues["buchhaltungEmail"])=="")
			{
				$this->formElementValues["buchhaltungEmail"] = $cAccountingPerson->GetEmail();
			}
		}
		
		if (is_array($tempData))
		{
			if ($tempData["useBranchAfterStatus28"]==2)
			{
				// Soll die Prüfung abgebrochen werden?
				$widerspruch=$this->obj->GetWiderspruch($this->db);
				if ($widerspruch!=null)
				{
					$abbruchProtokoll=$widerspruch->GetLastAbbruchProtokoll($this->db);
					if ($abbruchProtokoll!=null)
					{
						$hinweisFuerKunde = trim(str_replace("\n", "<br />", trim($abbruchProtokoll->GetBegruendung())));
						$rsPerson = $abbruchProtokoll->GetUser();
						if ($rsPerson!=null)
						{
							$rsUserName = $rsPerson->GetUserName();
						}
					}
				}
			}
			else
			{
				// Andernfalls wurde eine Rechnung gestellt..
				$hinweisFuerKunde = trim(str_replace("\n", "<br />", $tempData['hinweisFuerKunde']));
				$realisierteEinsparungen = HelperLib::ConvertFloatToLocalizedString( $tempData['realisierteEinsparungen'] );
				$nichtRealisierteEinsparungen = HelperLib::ConvertFloatToLocalizedString( $tempData['nichtRealisierteEinsparungen'] );
				$einsparungen = HelperLib::ConvertFloatToLocalizedString( $tempData['einsparungen'] = $tempData['realisierteEinsparungen'] + $tempData['nichtRealisierteEinsparungen'] );
				$abschlagszahlungName = $tempData['abschlagszahlungName'];
				$abschlagszahlungHoehe = $tempData['abschlagszahlungHoehe']==0.0 ? "" : HelperLib::ConvertFloatToLocalizedString($tempData['abschlagszahlungHoehe']);
			}
		}
		ob_start();
		?>
			<?=$apName;?>,<br /><br />
			die Prüfung der Nebenkostenabrechnung(en) ist aus unserer Sicht abgeschlossen. Die Ergebnisse stellen sich wie folgt dar:<br />
			<br />
			Aufgezeigte Einsparungen: <?=$einsparungen;?> <?=$currency;?><br />
			Von den aufgezeigten Einsparungen wurden realisiert: <?=$realisierteEinsparungen;?> <?=$currency;?><br />
			Nach interner Entscheidung von <?=$this->obj->GetGroupName();?> wurden von den aufgezeigten Einsparungen nicht realisiert: <?=$nichtRealisierteEinsparungen;?> <?=$currency;?><br />
		<?	if ($abschlagszahlungHoehe!=""){?>
			<?=$abschlagszahlungName;?>: <?=$abschlagszahlungHoehe;?> <?=$currency;?><br />
		<?	}?>
			<br />
		<?	if ($hinweisFuerKunde!=""){ ?>
			Dazu habe ich folgende Anmerkungen:<br/>
			<?=$hinweisFuerKunde;?><br />
			<br />
		<?	}?>
			Bitte leiten Sie das Prüfungsergebnis nach Freigabe an Ihre Buchhaltung weiter.<br />
			<br />
			Mit freundlichen Grüßen<br />
			<br />
			<?=$rsUserName;?><br />
			<br />
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		
		if ($this->formElementValues["buchhaltungEmailText"]=="")
		{
			$this->formElementValues["buchhaltungEmailText"] = $cAccountingPersonUserName.",\n\n";
			$this->formElementValues["buchhaltungEmailText"].= "die Prüfung der Nebenkostenabrechnung(en) ist abgeschlossen.\n";
			$this->formElementValues["buchhaltungEmailText"].= "Dazu habe ich folgende Anmerkungen:\n";
			$this->formElementValues["buchhaltungEmailText"].= "[Hinweise für Buchhaltung]\n\n";
			$this->formElementValues["buchhaltungEmailText"].= "Ich bitte um Verbuchung.\n\n";
			$this->formElementValues["buchhaltungEmailText"].= "Mit freundlichen Grüßen\n\n";
			$this->formElementValues["buchhaltungEmailText"].= $apUserName;
		}
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => 1);
		$options[]=Array("name" => "Nein", "value" => 2);
		$this->elements[] = new RadioButtonElement("sendEmail", "Email an Buchhaltung versenden", $this->formElementValues["sendEmail"], true, $this->error["sendEmail"], $options );
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		$this->elements[] = new TextElement("buchhaltungEmail", "Email-Adresse Buchhaltung", $this->formElementValues["buchhaltungEmail"], false, $this->error["buchhaltungEmail"]);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		$this->elements[] = new TextAreaElement("buchhaltungEmailText", "Email-Text an Buchhaltung", $this->formElementValues["buchhaltungEmailText"], false, $this->error["buchhaltungEmailText"]);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(600);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		$dataToStore = Array();
		$tempData = unserialize($this->obj->GetAdditionalInfo());
		if (is_array($tempData))$dataToStore = $tempData;
		
		$retValue=User::CheckFormatEMail( trim($this->formElementValues["buchhaltungEmail"]) );
		if( $retValue!==true && trim($this->formElementValues["buchhaltungEmail"])!="" ){
			$this->error["buchhaltungEmail"]="Die eingegebene Email-Adresse ist ungültig";
		}else{
			$dataToStore["buchhaltungEmail"] = trim($this->formElementValues["buchhaltungEmail"]);
		}
		$dataToStore["buchhaltungEmailText"] = trim($this->formElementValues["buchhaltungEmailText"]);

		if( count($this->error)==0 ){
			$this->obj->SetAdditionalInfo(serialize($dataToStore));
			$returnValue=$this->obj->Store($this->db);
			if ($returnValue!==true)
			{
				$this->error["misc"][]="Systemfehler (".$returnValue.")";
				return false;
			}
			if( !$gotoNextStatus )return true;
			
			if ($this->formElementValues["sendEmail"]==1)
			{
				if ($dataToStore["buchhaltungEmail"]=="")
				{
					$this->error["buchhaltungEmail"]="Bitte geben Síe die Email-Adresse der Buchhaltung an";
				}
				if ($dataToStore["buchhaltungEmailText"]=="")
				{
					$this->error["buchhaltungEmailText"]="Bitte geben Sie einen Email-Text an";
				}
			}
			elseif ($this->formElementValues["sendEmail"]!=2)
			{
				$this->error["sendEmail"]="Bitte geben Sie an, ob eine Email an die Buchhaltung gesendet werden soll";
				return false;
			}
			if( count($this->error)==0 )
			{
				if ($this->formElementValues["sendEmail"]==1)
				{
					// Email mit Anhängen versenden...
					// Dateien anhängen...
					$files = Array();
					$customerUser = ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP ? false : true);
					// Alle Teilabrechnungen zusammensuchen
					$abrechnungsJahr = $this->obj->GetAbrechnungsJahr();
					if ($abrechnungsJahr!=null)
					{
						$teilabrechnungen = $abrechnungsJahr->GetTeilabrechnungen($this->db);
						for ($a=0; $a<count($teilabrechnungen); $a++)
						{
							if ($teilabrechnungen[$a]==null) continue;
							$filesTemp = $teilabrechnungen[$a]->GetFiles($this->db);
							for ($b=0; $b<count($filesTemp); $b++)
							{
								if( $filesTemp[$b]->GetIntern() && $customerUser )continue;
								$files[]=Array("fileName" => $filesTemp[$b]->GetFileName(), "fileContent" => file_get_contents(FM_FILE_ROOT.$filesTemp[$b]->GetDocumentPath()) );
							}
						}
					}
					// Alle Abrechnungskorrekturen/Gutschriften
					$widersprueche = $this->obj->GetWidersprueche($this->db);
					for ($a=0; $a<count($widersprueche); $a++)
					{
						$filesTemp2 = $widersprueche[$a]->GetAntwortschreiben($this->db);
						for ($c=0; $c<count($filesTemp2); $c++)
						{
							$filesTemp = $filesTemp2[$c]->GetFiles($this->db);
							for ($b=0; $b<count($filesTemp); $b++)
							{
								if ($filesTemp[$b]->GetIntern() && $customerUser) continue;
								if ($filesTemp[$b]->GetFileSemantic()!=FM_FILE_SEMANTIC_ABRECHNUNGSKORREKTURGUTSCHRIFT && $filesTemp[$b]->GetFileSemantic()!=FM_FILE_SEMANTIC_NACHTRAG )continue;
								$files[]=Array("fileName" => $filesTemp[$b]->GetFileName(), "fileContent" => file_get_contents(FM_FILE_ROOT.$filesTemp[$b]->GetDocumentPath()) );
							}
						}
					}
					// Email versenden...
					global $SHARED_FILE_SYSTEM_ROOT, $EMAIL_MANAGER_OFFLINE_MODE, $EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL;
					$emailManager = new EMailManager($SHARED_FILE_SYSTEM_ROOT."templates/emailSignatureSystem.html", "", $EMAIL_MANAGER_OFFLINE_MODE, $EMAIL_MANAGER_OFFLINE_OVERWRITE_MAIL);
					//$dataToStore["buchhaltungEmail"] = "s.walleczek@stollvongati.com"; // TODO: Funktion scharf schalten und diese Zeile löschen
					$returnValue = $emailManager->SendEmailWithAttachments($dataToStore["buchhaltungEmail"], "Prüfung der Nebenkostenabrechnung abgeschlossen", str_replace("\n", "<br />", $dataToStore["buchhaltungEmailText"]), "Seybold FM <mnk@seybold-fm.com>", "", $files);
					if ($returnValue!==true)
					{
						// Fehler beim E-Mail Versand
						$this->error["misc"][]="E-Mail konnte nicht versandt werden (".$returnValue.")";
						return false;
					}
				}
				$this->formElementValues["useBranchAfterStatus28"] = (int)$dataToStore["useBranchAfterStatus28"];
				return true;
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
		return (int)$this->formElementValues["useBranchAfterStatus28"];
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