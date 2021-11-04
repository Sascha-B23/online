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
class KuerzungsbetraegeMatrixFormData extends FormData
{

	protected $realisiert = false;

	/**
	 * KuerzungsbetraegeMatrixFormData constructor.
	 * @param $formElementValues
	 * @param $object
	 * @param $db
	 * @param bool $realisiert
	 */
	public function __construct($formElementValues, $object, $db, $realisiert=false)
	{
		$this->realisiert = $realisiert;
		parent::__construct($formElementValues, $object, $db);
	}

	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		/** @var Widerspruch $widerspruch */
		$widerspruch = $this->obj;

		global $SHARED_HTTP_ROOT;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "teilabrechnung.png";
		$this->options["icontext"] = "Kürzungsbeträge ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		$wsps = $widerspruch->GetWiderspruchspunkte($this->db);
		if( $loadFromObject )
		{
			foreach($wsps as $wsp)
			{

				$this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()] = $wsp->GetKuerzungsbetraegeMatrix($this->db);
				foreach(Kuerzungsbetrag::GetEinsparungsTypen() as $einsparungType)
				{
					foreach(Kuerzungsbetrag::GetTypes() as $type)
					{
						foreach(Kuerzungsbetrag::GetRatings() as $rating)
						{
							$this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()][$einsparungType][$type][$rating] = HelperLib::ConvertFloatToLocalizedString($this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()][$einsparungType][$type][$rating]);
							$this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()]["r"][$einsparungType][$type][$rating] = $this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()]["r"][$einsparungType][$type][$rating]==Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES ? 'on' : '';
						}
					}
				}
			}
		}else{
			foreach($wsps as $wsp)
			{
				foreach(Kuerzungsbetrag::GetEinsparungsTypen() as $einsparungType)
				{
					foreach(Kuerzungsbetrag::GetTypes() as $type)
					{
						foreach(Kuerzungsbetrag::GetRatings() as $rating)
						{
							$this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()][$einsparungType][$type][$rating] = HelperLib::ConvertFloatToLocalizedString(0.0);
							$this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()]["r"][$einsparungType][$type][$rating] = "";
							if (isset($this->formElementValues["kuerzungsbetraege_" . $wsp->GetPKey() . "_" . $einsparungType . "_" . $type . "_" . $rating])) $this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()][$einsparungType][$type][$rating] = $this->formElementValues["kuerzungsbetraege_". $wsp->GetPKey() . "_" . $einsparungType . "_" . $type . "_" . $rating];
							if (isset($this->formElementValues["kuerzungsbetraege_" . $wsp->GetPKey() . "_" . $einsparungType . "_" . $type . "_" . $rating."_r"])) $this->formElementValues["kuerzungsbetraege".$wsp->GetPKey()]["r"][$einsparungType][$type][$rating] = $this->formElementValues["kuerzungsbetraege_". $wsp->GetPKey() . "_" .$einsparungType."_".$type."_".$rating."_r"];
						}
					}
				}
			}
		}

		foreach($wsps as $wsp)
		{
			// Ampelbeträge
			$this->elements[] = new AmpelElement("kuerzungsbetraege_".$wsp->GetPKey(), $wsp->GetRank()." ".$wsp->GetTitle(), $this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()], true, $this->error["kuerzungsbetraege_".$wsp->GetPKey()], false, $this->realisiert);
			$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
			$this->elements[] = new BlankElement();
		}
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		/** @var Widerspruch $widerspruch */
		$widerspruch = $this->obj;
		$wsps = $widerspruch->GetWiderspruchspunkte($this->db);
		foreach($wsps as $wsp)
		{
			foreach(Kuerzungsbetrag::GetEinsparungsTypen() as $einsparungType)
			{
				foreach(Kuerzungsbetrag::GetTypes() as $type)
				{
					foreach(Kuerzungsbetrag::GetRatings() as $rating)
					{
						$this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()][$einsparungType][$type][$rating] = 0.0;
						$this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()]["r"][$einsparungType][$type][$rating] = Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_NO;
						if (isset($this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey().'_'.$einsparungType."_".$type."_".$rating]) && trim($this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey().'_'.$einsparungType."_".$type."_".$rating])!="") $this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()][$einsparungType][$type][$rating] = TextElement::GetFloat($this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey().'_'.$einsparungType."_".$type."_".$rating]);
						if (isset($this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey().'_'.$einsparungType."_".$type."_".$rating."_r"]) && $this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey().'_'.$einsparungType."_".$type."_".$rating."_r"]=="on") $this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()]["r"][$einsparungType][$type][$rating] = Kuerzungsbetrag::KUERZUNGSBETRAG_REALISIERT_YES;
					}
				}
			}
			$returnValue = $wsp->SetKuerzungsbetraegeMatrix($this->db, $this->formElementValues["kuerzungsbetraege_".$wsp->GetPKey()], $this->realisiert);
			if ($returnValue!==true) $this->error["kuerzungsbetraege_".$wsp->GetPKey()] = $returnValue;
		}

		if (count($this->error)==0)
		{
			return true;
		}
		return false;
	}
	
}