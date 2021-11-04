<?php
/**
 * FormData-Implementierung für die Widerspruchspunkte
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class WiderspruchspunktFormData extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "teilabrechnung.png";
		$this->options["icontext"] = "Widerspruchspunkt ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject )
		{
			$this->formElementValues["title"]=$this->obj->GetTitle();
			$this->formElementValues["textLeft"]=$this->obj->GetTextLeft();
			$this->formElementValues["text"]=$this->obj->GetTextRight();
			$this->formElementValues["rank"]=$this->obj->GetRank();
			$this->formElementValues["hideentry"] = ($this->obj->IsHidden()  ? "on" : "");
			$this->formElementValues["kuerzungsbetraege2"] = $this->obj->GetKuerzungsbetraegeMatrix($this->db);
			foreach(Kuerzungsbetrag::GetEinsparungsTypen() as $einsparungType)
			{
				foreach(Kuerzungsbetrag::GetTypes() as $type)
				{
					foreach(Kuerzungsbetrag::GetRatings() as $rating)
					{
						$this->formElementValues["kuerzungsbetraege2"][$einsparungType][$type][$rating] = HelperLib::ConvertFloatToLocalizedString($this->formElementValues["kuerzungsbetraege2"][$einsparungType][$type][$rating]);
					}
				}
			}
		}
		else
		{
			if (!isset($this->formElementValues["title2"])) $this->formElementValues["title2"]="";
			$this->formElementValues["title"] = $this->formElementValues["title2"];
			if (!isset($this->formElementValues["textLeft"])) $this->formElementValues["textLeft"]='<strong>[WS_NUMMER] </strong><br />';
			if (!isset($this->formElementValues["text"])) $this->formElementValues["text"]="";
			if (!isset($this->formElementValues["rank"]))
			{
				$this->formElementValues["rank"]="0";
				$ws = $this->obj->GetWiderspruch();
				if ($ws!=null) $this->formElementValues["rank"]=$ws->GetHighestRank($this->db)+10;
			}
			foreach(Kuerzungsbetrag::GetEinsparungsTypen() as $einsparungType)
			{
				foreach(Kuerzungsbetrag::GetTypes() as $type)
				{
					foreach(Kuerzungsbetrag::GetRatings() as $rating)
					{
						$this->formElementValues["kuerzungsbetraege2"][$einsparungType][$type][$rating] = HelperLib::ConvertFloatToLocalizedString(0.0);
						if (isset($this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating])) $this->formElementValues["kuerzungsbetraege2"][$einsparungType][$type][$rating] = $this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating];
					}
				}
			}
		}
		// Template-Texte holen
		$textModules = Array();
		
		$ws = $this->obj->GetWiderspruch();
		if ($ws!=null)
		{
			$countryCode = $ws->GetCountry();
			$country = CCountry::GetCountryByIso3166($this->db, $countryCode);
			if ($country!=null)
			{
				$textModuleManager = new TextModuleManager($this->db, $country);
				$textModules = $textModuleManager->GetTextModule();
				// Texte aus Template übernehmen?
				if (isset($this->formElementValues["getDataFromTemplate"]) && (int)$this->formElementValues["getDataFromTemplate"]>0)
				{
					foreach ($textModules as $textModule)
					{
						if ($textModule->GetPKey()==(int)$this->formElementValues["getDataFromTemplate"] )
						{
							$this->formElementValues["title"] = $textModule->GetTitle();
							$this->formElementValues["textLeft"]='<strong>[WS_NUMMER] '.$this->formElementValues["title"].'</strong><br />';
							$this->formElementValues["textLeft"].= $textModule->GetTextLeft();
							$this->formElementValues["text"] = "";
							for($a=0; $a<$textModule->GetNumberOfTextBlocks(); $a++)
							{
								$this->formElementValues["text"].=$textModule->GetTextRight($a);
							}
						}
					}
				}
			}
		}
		// Widerspruchspunkt
		$this->elements[] = new TextElement("title", "Überschrift", $this->formElementValues["title"], true, $this->error["title"], true);
		// Rang
		$this->elements[] = new TextElement("rank", "Rang", $this->formElementValues["rank"], true, $this->error["rank"]);
		// Übernehmen
		$options=Array();
		$options[]=Array("name" => "Keine Auswahl", "value" => -1);
		for ($a=0; $a<count($textModules); $a++)
		{
			$options[]=Array("name" => $textModules[$a]->GetTitle(), "value" => $textModules[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("templateToUse", "Texte aus Vorlage übernehmen", !isset($this->formElementValues["templateToUse"]) ? Array() : $this->formElementValues["templateToUse"], false, $this->error["templateToUse"], $options, false);
		// Ausblenden?
		$this->elements[] = new CheckboxElement("hideentry", "WS-Punkt in Anlage ausblenden", $this->formElementValues["hideentry"], false, $this->error["hideentry"]);
		
		// Textblöcke 
		$this->elements[] = new TextAreaElement("textLeft", "Standpunkt Eigentümer/Verwalter/ Anwalt", $this->formElementValues["textLeft"], true, $this->error["textLeft"], false, true);	
		$this->elements[count($this->elements)-1]->SetWidth(480);
		$this->elements[count($this->elements)-1]->SetHeight(400);
		$this->elements[] = new TextAreaElement("text", "Standpunkt Kunde", $this->formElementValues["text"], true, $this->error["text"], false, true);	
		$this->elements[count($this->elements)-1]->SetWidth(480);
		$this->elements[count($this->elements)-1]->SetHeight(400);

		// Ampelbeträge
		$this->elements[] = new AmpelElement("kuerzungsbetraege2", "Kürzungsbeträge", $this->formElementValues["kuerzungsbetraege2"], true, $this->error["kuerzungsbetraege2"], false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();

	/*	// Kürzungsbeträge (Liste)
		$this->elements[] = new ListElement("kuerzungsbetraege", "Kürzungsbeträge", $this->formElementValues["kuerzungsbetraege"], false, $this->error["kuerzungsbetraege"], false, new KuerzungsbetraegeListData($this->db, $this->obj));
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		ob_start();
		?>
			<table width="100%" border="0">
				<tr>
					<td>&#160;</td>
					<td style="width: 25px;">
						<a href="javascript:CreateNewPos(<?=($edit ? 'true' : 'false');?>);" style="text-decoration:none;" onfocus="if (this.blur) this.blur()"><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/teilabrechnung.png" border="0" alt="" /></a>
					</td>
					<td style="width: 150px;">
						<a href="javascript:CreateNewPos(<?=($edit ? 'true' : 'false');?>);" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">Kürzungsbetrag hinzufügen</a>
					</td>
				</tr>
			</table>
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();*/
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		// Titel nur übernehmen wenn der WSP nicht vom WS-Generator erzeugt wurde
		$this->obj->SetTitle($this->formElementValues["title2"]);
		if (trim($this->formElementValues["title2"])=="") $this->error["title"]="Bitte geben Sie eine Überschrift ein";
		
		$this->obj->SetTextRight($this->formElementValues["text"]);
		if (trim($this->formElementValues["text"])=="") $this->error["text"]="Bitte geben Sie einen Text ein";
		$this->obj->SetTextLeft($this->formElementValues["textLeft"]);
		if (trim($this->formElementValues["textLeft"])=="") $this->error["textLeft"]="Bitte geben Sie einen Text ein";	
		
		if (is_numeric($this->formElementValues["rank"]) && (int)$this->formElementValues["rank"]==$this->formElementValues["rank"] ) 
		{
			$this->obj->SetRank((int)$this->formElementValues["rank"]);
		}
		else
		{
			$this->error["rank"]="Bitte geben Sie eine Zahl ein";	
		}
		
		$this->obj->SetHidden($this->formElementValues["hideentry"]=='on' ? true : false);


		foreach(Kuerzungsbetrag::GetEinsparungsTypen() as $einsparungType)
		{
			foreach(Kuerzungsbetrag::GetTypes() as $type)
			{
				foreach(Kuerzungsbetrag::GetRatings() as $rating)
				{
					$this->formElementValues["kuerzungsbetraege2"][$einsparungType][$type][$rating] = 0.0;
					if (isset($this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating]) && trim($this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating])!="") $this->formElementValues["kuerzungsbetraege2"][$einsparungType][$type][$rating] = TextElement::GetFloat($this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating]);
				}
			}
		}

		if (count($this->error)==0)
		{
			$returnValue=$this->obj->Store($this->db);
			if( $returnValue===true ){
				$returnValue = $this->obj->SetKuerzungsbetraegeMatrix($this->db, $this->formElementValues["kuerzungsbetraege2"]);
				if ($returnValue===true)
				{
					return true;
				}
				$this->error["kuerzungsbetraege2"] = $returnValue;
			}else{
				$this->error["misc"][]="Systemfehler (".$returnValue.")";
			}
		}
		return false;
	}
	
	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben nachdem das Formular ausgegeben wurde
	 */
	public function PostPrint()
	{
		global $DOMAIN_HTTP_ROOT;
		?>
		<input type="hidden" name="title2" id="title2" value="<?=$this->formElementValues["title"];?>" />
		<input type="hidden" name="getDataFromTemplate" id="getDataFromTemplate" value="" />
		<script type="text/javascript">
			<!--
				function GetTextFromTemplate(templateID)
				{
					if (templateID!=-1)
					{
						if(confirm('Es werden nun die Felder "Überschrift", "Standpunkt Eigentümer/Verwalter/ Anwalt" und "Standpunkt Kunde" von dem ausgewählten Textbaustein übernommen.\n\nDabei gehen alle bisher in diesen Feldern eingetragenen Texte verloren.\n\nMöchten Sie trotzdem fortfahren? '))
						{
							//document.FM_FORM.forwardToListView.value=false;
							$('getDataFromTemplate').value = templateID;
							document.forms.FM_FORM.submit();
						}
					}
				}
				$('templateToUse').onchange = function(){GetTextFromTemplate($('templateToUse').value); return false;}
				
			<?/*
				function CreateNewPos(editMode)
				{
					if (editMode)
					{
						var newWin=window.open('editKuerzungsbetrag.php5?<?=SID;?>&widerspruchspunktID=<?=$this->obj->GetPKey();?>','_createEditKuerzungsbetrag','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=900,width=1050');
						//newWin.moveTo(width,height);
						newWin.focus();
					}
					else
					{
						if (confirm("Um einen Kürzungsbetrag anlegen zu können, muss zunächst der Widerspruchspunkt gespeichert werden.\n\nMöchten Sie den Widerspruchspunkt jetzt abspeichern?"))
						{
							document.FM_FORM.forwardToListView.value=false;
							document.FM_FORM.sendData.value=true;
							document.forms.FM_FORM.submit();
						}
					}
				}
				
				function EditPos(posID)
				{
					var newWin=window.open('editKuerzungsbetrag.php5?<?=SID;?>&editElement='+posID,'_createEditKuerzungsbetrag','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=900,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				} */?>
			-->
		</script>
		<?	
	}
	
}
?>