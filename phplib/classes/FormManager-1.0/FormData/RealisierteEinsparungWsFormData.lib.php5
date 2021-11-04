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
class RealisierteEinsparungWsFormData extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		// Icon und Überschrift festlegen
		$this->options["icon"] = "teilabrechnung.png";
		$this->options["icontext"] = "Realisierte Einsparung dokumentieren";
		// Wenn wir nicht im Edit-Mode sind abbrechen
		if (!$edit) return false;
		// Daten aus Objekt laden?
		$this->formElementValues["title"]=$this->obj->GetTitle();
		$this->formElementValues["textLeft"]=$this->obj->GetTextLeft();
		$this->formElementValues["text"]=$this->obj->GetTextRight();
		if( $loadFromObject )
		{
			$this->formElementValues["kuerzungsbetraege2"] = $this->obj->GetKuerzungsbetraegeMatrix($this->db);
			foreach(Kuerzungsbetrag::GetEinsparungsTypen() as $einsparungType)
			{
				foreach(Kuerzungsbetrag::GetTypes() as $type)
				{
					foreach(Kuerzungsbetrag::GetRatings() as $rating)
					{
						$this->formElementValues["kuerzungsbetraege2"][$einsparungType][$type][$rating] = HelperLib::ConvertFloatToLocalizedString($this->formElementValues["kuerzungsbetraege2"][$einsparungType][$type][$rating]);
						$this->formElementValues["kuerzungsbetraege2"]["r"][$einsparungType][$type][$rating] = $this->formElementValues["kuerzungsbetraege2"]["r"][$einsparungType][$type][$rating]==Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES ? 'on' : '';
					}
				}
			}
		}
		else
		{
			foreach(Kuerzungsbetrag::GetEinsparungsTypen() as $einsparungType)
			{
				foreach(Kuerzungsbetrag::GetTypes() as $type)
				{
					foreach(Kuerzungsbetrag::GetRatings() as $rating)
					{
						$this->formElementValues["kuerzungsbetraege2"][$einsparungType][$type][$rating] = HelperLib::ConvertFloatToLocalizedString(0.0);
						$this->formElementValues["kuerzungsbetraege2"]["r"][$einsparungType][$type][$rating] = "";
						if (isset($this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating])) $this->formElementValues["kuerzungsbetraege2"][$einsparungType][$type][$rating] = $this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating];
						if (isset($this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating."_r"])) $this->formElementValues["kuerzungsbetraege2"]["r"][$einsparungType][$type][$rating] = $this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating."_r"];
					}
				}
			}
		}
		// Widerspruchspunkt
		$this->elements[] = new TextElement("title", "Überschrift", $this->formElementValues["title"], true, $this->error["title"], true);
		// Textblöcke 
		$this->elements[] = new TextAreaElement("textLeft", "Standpunkt Eigentümer/Verwalter/ Anwalt", $this->formElementValues["textLeft"], true, $this->error["textLeft"], true, true);	
		$this->elements[count($this->elements)-1]->SetWidth(480);
		$this->elements[count($this->elements)-1]->SetHeight(100);
		$this->elements[] = new TextAreaElement("text", "Standpunkt Kunde", $this->formElementValues["text"], true, $this->error["text"], true, true);	
		$this->elements[count($this->elements)-1]->SetWidth(480);
		$this->elements[count($this->elements)-1]->SetHeight(100);

		// Ampelbeträge
		$this->elements[] = new AmpelElement("kuerzungsbetraege2", "Kürzungsbeträge", $this->formElementValues["kuerzungsbetraege2"], true, $this->error["kuerzungsbetraege2"], false, true);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();

		// Kürzungsbeträge (Liste)
		//$this->elements[] = new ListElement("kuerzungsbetraege", "Kürzungsbeträge", $this->formElementValues["kuerzungsbetraege"], false, $this->error["kuerzungsbetraege"], false, new KuerzungsbetraegeListData($this->db, $this->obj, true));
		
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		foreach(Kuerzungsbetrag::GetEinsparungsTypen() as $einsparungType)
		{
			foreach(Kuerzungsbetrag::GetTypes() as $type)
			{
				foreach(Kuerzungsbetrag::GetRatings() as $rating)
				{
					$this->formElementValues["kuerzungsbetraege2"][$einsparungType][$type][$rating] = 0.0;
					$this->formElementValues["kuerzungsbetraege2"]["r"][$einsparungType][$type][$rating] = Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO;
					if (isset($this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating]) && trim($this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating])!="") $this->formElementValues["kuerzungsbetraege2"][$einsparungType][$type][$rating] = TextElement::GetFloat($this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating]);
					if (isset($this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating."_r"]) && $this->formElementValues["kuerzungsbetraege2_".$einsparungType."_".$type."_".$rating."_r"]=="on") $this->formElementValues["kuerzungsbetraege2"]["r"][$einsparungType][$type][$rating] = Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES;
				}
			}
		}
		$returnValue = $this->obj->SetKuerzungsbetraegeMatrix($this->db, $this->formElementValues["kuerzungsbetraege2"], true);
		if ($returnValue!==true) $this->error["kuerzungsbetraege2"] = $returnValue;
		if (count($this->error)==0)
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben nachdem das Formular ausgegeben wurde
	 */
	public function PostPrint()
	{
		/*global $DOMAIN_HTTP_ROOT;
		?>
		<script type="text/javascript">
			<!--		
				function EditPos(posID)
				{
					var newWin=window.open('editRealisierteEinsparungKb.php5?<?=SID;?>&editElement='+posID,'_createEditKuerzungsbetrag','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=900,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
			-->
		</script>
		<?	*/
	}
	
}