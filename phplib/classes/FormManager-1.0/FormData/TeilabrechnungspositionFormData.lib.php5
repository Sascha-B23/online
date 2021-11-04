<?php
/**
 * FormData-Implementierung für die Teilabrechnungsposition
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TeilabrechnungspositionFormData extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $rsKostenartManager;
		global $NKM_TEILABRECHNUNGSPOSITION_EINHEIT;
		global $SHARED_HTTP_ROOT;
		$ta=$this->obj->GetTeilabrechnung($this->db);	
		$year=null;
		if( $ta!=null )
		{
			$year=$ta->GetAbrechnungsJahr();
		}
		// Icon und Überschrift festlegen
		$this->options["icon"] = "teilabrechnung.png";
		$this->options["icontext"] = "Teilabrechnungsposition ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		
		$objectBefore = null;
		$taps = Array();
		if ($ta!=null)
		{
			$taps = $ta->GetTeilabrechnungspositionen($this->db);
		}
		
		if ($edit)
		{	
			$previousTap = null;
			$nextTap = null;
			for ($a=0; $a<count($taps); $a++)
			{
				if ($taps[$a]->GetPKey()!=$this->obj->GetPKey())
				{
					$previousTap = $taps[$a];
				}
				else
				{
					if ($a+1<count($taps)) $nextTap = $taps[$a+1];
					break;
				}
			}
			if ($previousTap!=null)
			{
				$this->options["show_options_previous"] = "?".SID."&editElement=".$previousTap->GetPKey()."&editElementBefore=".$this->obj->GetPKey();
			}
			if ($nextTap!=null)
			{
				$this->options["show_options_next"] = "?".SID."&editElement=".$nextTap->GetPKey()."&editElementBefore=".$this->obj->GetPKey();
			}
		}
		// Vorherige TAP holen
		if (isset($_GET['editElementBefore']))
		{	
			for ($a=0; $a<count($taps); $a++)
			{
				if ($taps[$a]->GetPKey()==(int)$_GET['editElementBefore'])
				{
					$objectBefore = $taps[$a];
					break;
				}
			}
		}
		$advices = Array();
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["bezeichnungteilflaeche"]=$this->obj->GetBezeichnungTeilflaech();
			$this->formElementValues["bezeichnungkostenartabrechnung"]=$this->obj->GetBezeichnungKostenart();
			$this->formElementValues["kostenartaRS"]=$this->obj->GetKostenartRS($this->db)==null ? "" : $this->obj->GetKostenartRS($this->db)->GetPKey();
            $this->formElementValues["pauschale"]=$this->obj->IsPauschale() ? "on" : "";
			$this->formElementValues["gesamteinheiten"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetGesamteinheiten() );
			$this->formElementValues["gesamteinheiten_dd"]=$this->obj->GetGesamteinheitenEinheit();
			$this->formElementValues["einheitenkunde"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetEinheitKunde() );
			$this->formElementValues["einheitenkunde_dd"]=$this->obj->GetEinheitKundeEinheit();
			$this->formElementValues["gesamtbetrag"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetGesamtbetrag() );
			$this->formElementValues["betragkunde"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetBetragKunde() );
			$this->formElementValues["umlagefaehig"]=$this->obj->GetUmlagefaehig();
			// Bestimmte Daten aus vorheriger TAP holen wenn leer 
			if ($objectBefore!=null)
			{
				if ($this->formElementValues["bezeichnungteilflaeche"]=="" && trim($objectBefore->GetBezeichnungTeilflaech())!="")
				{
					$this->formElementValues["bezeichnungteilflaeche"]=$objectBefore->GetBezeichnungTeilflaech();
					$advices["bezeichnungteilflaeche"] = "Der Wert wurde von der vorherigen Teilabrechnungsposition <br />übernommen. Um diesen zu übernehmen, müssen Sie den <br />Datensatz abspeichern.";
				}
				if ($this->obj->GetGesamteinheiten()==0.0 && $this->obj->GetGesamteinheitenEinheit()==NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE )
				{
					$this->formElementValues["gesamteinheiten"]=HelperLib::ConvertFloatToLocalizedString( $objectBefore->GetGesamteinheiten() );
					$this->formElementValues["gesamteinheiten_dd"]=$objectBefore->GetGesamteinheitenEinheit();
					if ($objectBefore->GetGesamteinheiten()!=0.0 || $objectBefore->GetGesamteinheitenEinheit()!=NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE )
					{
						$advices["gesamteinheiten"] = "Die Werte wurden von der vorherigen Teilabrechnungsposition <br />übernommen. Um diese zu übernehmen, müssen Sie den <br />Datensatz abspeichern.";
					}
				}
				if ($this->obj->GetEinheitKunde()==0.0 && $this->obj->GetEinheitKundeEinheit()==NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE )
				{
					$this->formElementValues["einheitenkunde"]=HelperLib::ConvertFloatToLocalizedString( $objectBefore->GetEinheitKunde() );
					$this->formElementValues["einheitenkunde_dd"]=$objectBefore->GetEinheitKundeEinheit();
					if ($objectBefore->GetEinheitKunde()!=0.0 || $objectBefore->GetEinheitKundeEinheit()!=NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE )
					{
						$advices["einheitenkunde"] = "Die Werte wurden von der vorherigen Teilabrechnungsposition <br />übernommen. Um diese zu übernehmen, müssen Sie den <br />Datensatz abspeichern.";
					}
				}
			}
		}
		else
		{
			if (!isset($this->formElementValues["bezeichnungteilflaeche"]) && $objectBefore!=null && trim($objectBefore->GetBezeichnungTeilflaech())!="")
			{
				$this->formElementValues["bezeichnungteilflaeche"]=$objectBefore->GetBezeichnungTeilflaech();
				$advices["bezeichnungteilflaeche"] = "Der Wert wurde von der vorherigen Teilabrechnungsposition <br />übernommen.";
			}

            // Wenn auf Pauschale gesetzt wurde, die Werte der deaktivierten Elemente aus Objekt auslesen
            if ($this->formElementValues["pauschale"]=='on')
            {
                $this->formElementValues["gesamteinheiten"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetGesamteinheiten() );
                $this->formElementValues["gesamteinheiten_dd"]=$this->obj->GetGesamteinheitenEinheit();
                $this->formElementValues["einheitenkunde"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetEinheitKunde() );
                $this->formElementValues["einheitenkunde_dd"]=$this->obj->GetEinheitKundeEinheit();
                $this->formElementValues["gesamtbetrag"]=HelperLib::ConvertFloatToLocalizedString( $this->obj->GetGesamtbetrag() );
            }

            // Bestimmte Daten aus vorheriger TAP holen wenn leer
            if (!isset($this->formElementValues["gesamteinheiten"]) && !isset($this->formElementValues["gesamteinheiten_dd"]) && $objectBefore!=null)
            {
                $this->formElementValues["gesamteinheiten"]=HelperLib::ConvertFloatToLocalizedString( $objectBefore->GetGesamteinheiten() );
                $this->formElementValues["gesamteinheiten_dd"]=$objectBefore->GetGesamteinheitenEinheit();
                if ($objectBefore->GetGesamteinheiten()!=0.0 || $objectBefore->GetGesamteinheitenEinheit()!=NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE )
                {
                    $advices["gesamteinheiten"] = "Die Werte wurden von der vorherigen Teilabrechnungsposition <br />übernommen.";
                }
            }
            if (!isset($this->formElementValues["einheitenkunde"]) && !isset($this->formElementValues["einheitenkunde_dd"]) && $objectBefore!=null)
            {
                $this->formElementValues["einheitenkunde"]=HelperLib::ConvertFloatToLocalizedString( $objectBefore->GetEinheitKunde() );
                $this->formElementValues["einheitenkunde_dd"]=$objectBefore->GetEinheitKundeEinheit();
                if ($objectBefore->GetEinheitKunde()!=0.0 || $objectBefore->GetEinheitKundeEinheit()!=NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE )
                {
                    $advices["einheitenkunde"] = "Die Werte wurden von der vorherigen Teilabrechnungsposition <br />übernommen.";
                }
            }

			if( !isset($this->formElementValues["gesamteinheiten"]) )$this->formElementValues["gesamteinheiten"]=0;
			if( !isset($this->formElementValues["einheitenkunde"]) )$this->formElementValues["einheitenkunde"]=0;
			if( !isset($this->formElementValues["gesamtbetrag"]) )$this->formElementValues["gesamtbetrag"]="0,00";
			if( !isset($this->formElementValues["betragkunde"]) )$this->formElementValues["betragkunde"]="0,00";
			if( !isset($this->formElementValues["umlagefaehig"]) )$this->formElementValues["umlagefaehig"]=2;
		}	

		// Abrechnungsjahr
		$this->elements[] = new TextElement("bezeichnungteilflaeche", "Bezeichnung Teilfläche", $this->formElementValues["bezeichnungteilflaeche"], true, $this->error["bezeichnungteilflaeche"]);
		$this->elements[count($this->elements)-1]->SetAdvice($advices["bezeichnungteilflaeche"]);
		$this->elements[] = new TextElement("bezeichnungkostenartabrechnung", "Bezeichnung Kostenart lt. Abrechnung", $this->formElementValues["bezeichnungkostenartabrechnung"], true, $this->error["bezeichnungkostenartabrechnung"], false, new SearchTextWithPreselectDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 15), "", "bezeichnungkostenartabrechnung", "kostenartaRS"));
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => "");
		$kostenarten=$rsKostenartManager->GetKostenarten("", "name", 0, 0, $rsKostenartManager->GetKostenartenCount());
		for($a=0; $a<count($kostenarten); $a++)
		{
			$kostenarten[$a];
			$options[]=Array("name" => $kostenarten[$a]->GetName(), "value" => $kostenarten[$a]->GetPKey() );
		}
		$this->elements[] = new DropdownElement("kostenartaRS", "Bezeichnung Kostenart lt. SFM", $this->formElementValues["kostenartaRS"], true, $this->error["kostenartaRS"], $options, false);

		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => "");
		$keys=array_keys($NKM_TEILABRECHNUNGSPOSITION_EINHEIT);
		
		for($a=0; $a<count($keys); $a++)
		{
			if( $keys[$a]==NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE )continue;
			$options[]=Array("name" => $NKM_TEILABRECHNUNGSPOSITION_EINHEIT[$keys[$a]]["long"], "value" => $keys[$a] );
		}

        $this->elements[] = new CheckboxElement("pauschale", "Pauschale", $this->formElementValues["pauschale"]);

		$this->elements[] = new TextAndDropdownElement("gesamteinheiten", "Gesamteinheiten", $this->formElementValues["gesamteinheiten"], $this->formElementValues["gesamteinheiten_dd"], true, $this->error["gesamteinheiten"], $options, false, null, Array(), $this->formElementValues["pauschale"]=='on' ? true : false);
		$this->elements[count($this->elements)-1]->SetAdvice($advices["gesamteinheiten"]);
		$this->elements[] = new TextAndDropdownElement("einheitenkunde", "Einheiten Kunde", $this->formElementValues["einheitenkunde"], $this->formElementValues["einheitenkunde_dd"], true, $this->error["einheitenkunde"], $options, false, null, Array(), $this->formElementValues["pauschale"]=='on' ? true : false);
		$this->elements[count($this->elements)-1]->SetAdvice($advices["einheitenkunde"]);
		$this->elements[] = new TextElement("gesamtbetrag", "Gesamtbetrag in ".$ta->GetCurrency()." (für ".($year==null ? "?" : $year->GetDaysOfYear())." Tage)", $this->formElementValues["gesamtbetrag"], true, $this->error["gesamtbetrag"], $this->formElementValues["pauschale"]=='on' ? true : false);
		$this->elements[] = new TextElement("betragkunde", "Betrag Kunde in ".$ta->GetCurrency()." (für ".($ta==null ? "?" : (int)$ta->GetNumDays())." Tage)", $this->formElementValues["betragkunde"], true, $this->error["betragkunde"], false, null, Array(0 => Array("width" => 25, "pic" => "pics/gui/process.png", "help" => "Wert aus Eingaben berechnen", "href" => "javascript:UpdateGesamtbetrag(true);")));
		
		$options=Array();
		$options[]=Array("name" => "Ja", "value" => 1);
		$options[]=Array("name" => "Nein", "value" => 2);
		$this->elements[] = new RadioButtonElement("umlagefaehig", "Umlagefähig", $this->formElementValues["umlagefaehig"], true, $this->error["umlagefaehig"], $options );
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		global $rsKostenartManager;
		$this->error=array();
		$this->obj->SetBezeichnungTeilflaeche($this->formElementValues["bezeichnungteilflaeche"]);
		//if (trim($this->obj->GetBezeichnungTeilflaech())=="") $this->error["bezeichnungteilflaeche"]="Bitte geben Sie die Bezeichnung der Teilfläche ein";
		$this->obj->SetBezeichnungKostenart($this->formElementValues["bezeichnungkostenartabrechnung"]);
		//if (trim($this->obj->GetBezeichnungKostenart())=="") $this->error["bezeichnungkostenartabrechnung"]="Bitte geben Sie die Bezeichnung der Kostenart lt. Abrechnung ein";
		if( trim($this->formElementValues["kostenartaRS"])!="" && (int)trim($this->formElementValues["kostenartaRS"])==trim($this->formElementValues["kostenartaRS"]) )
		{
			$tempObject=new RSKostenart($this->db);
			if( $tempObject->Load((int)trim($this->formElementValues["kostenartaRS"]), $this->db)===false )
			{
				$this->error["kostenartaRS"]="Die SFM-Kostenart konnte nicht geladen werden.";
			}
			else
			{
				$this->obj->SetKostenartRS( $tempObject );
			}
		}
		else
		{
			$this->obj->SetKostenartRS( null );
		}
		//if ($this->obj->GetKostenartRS($this->db)==null) $this->error["kostenartaRS"]="Bitte wählen Sie die entsprechende FMS-Kostenart aus";

        $this->obj->SetPauschale( $this->formElementValues["pauschale"]=="on" ? true : false );

        if (!$this->obj->IsPauschale())
        {
            $tempValue=TextElement::GetFloat($this->formElementValues["gesamteinheiten"]);
            if( $tempValue===false )$this->error["gesamteinheiten"]="Bitte geben Sie eine gültige Zahl ein";
            else $this->obj->SetGesamteinheiten($tempValue);
            if( trim($this->formElementValues["gesamteinheiten_dd"])!="" && (int)trim($this->formElementValues["gesamteinheiten_dd"])==trim($this->formElementValues["gesamteinheiten_dd"]) )
            {
                $this->obj->SetGesamteinheitenEinheit( (int)trim($this->formElementValues["gesamteinheiten_dd"]) );
            }
            else
            {
                $this->obj->SetGesamteinheitenEinheit( NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE );
            }
            //if ($this->obj->GetGesamteinheitenEinheit()==NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE) $this->error["gesamteinheiten"].=($this->error["gesamteinheiten"]!="" ? "<br />" : "")."Bitte wählen Sie die Einheit aus";
            $tempValue=TextElement::GetFloat($this->formElementValues["einheitenkunde"]);
            if( $tempValue===false )$this->error["einheitenkunde"]="Bitte geben Sie eine gültige Zahl ein";
            else $this->obj->SetEinheitKunde($tempValue);
            if( trim($this->formElementValues["einheitenkunde_dd"])!="" && (int)trim($this->formElementValues["einheitenkunde_dd"])==trim($this->formElementValues["einheitenkunde_dd"]) )
            {
                $this->obj->SetEinheitKundeEinheit( (int)trim($this->formElementValues["einheitenkunde_dd"]) );
            }
            else
            {
                $this->obj->SetEinheitKundeEinheit( NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE );
            }
            //if ($this->obj->GetEinheitKundeEinheit()==NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE) $this->error["einheitenkunde"].=($this->error["einheitenkunde"]!="" ? "<br />" : "")."Bitte wählen Sie die Einheit aus";
            $tempValue=TextElement::GetFloat($this->formElementValues["gesamtbetrag"]);
            if( $tempValue===false )$this->error["gesamtbetrag"]="Bitte geben Sie eine gültige Zahl ein";
            else $this->obj->SetGesamtbetrag($tempValue);
        }

		$tempValue=TextElement::GetFloat($this->formElementValues["betragkunde"]);
		if( $tempValue===false )$this->error["betragkunde"]="Bitte geben Sie eine gültige Zahl ein";
		else $this->obj->SetBetragKunde($tempValue);
		$this->obj->SetUmlagefaehig((int)$this->formElementValues["umlagefaehig"]);
		if( count($this->error)==0 )
		{
			$returnValue=$this->obj->Store($this->db);
			if( $returnValue===true )
			{
				return true;
			}
			else
			{
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
		global $SHARED_HTTP_ROOT;
	?>
			<script type="text/javascript">
				<!--
					// Daten dynamisch laden...
					var lastResultValue=0.0;
					var dataRequestObjBetragKunde = new Request.JSON (
						{	url:'<?=$SHARED_HTTP_ROOT;?>phplib/jsInterface.php5', 
							method: 'post',
							async: false,
							noCache: true,
							onSuccess: function(responseJSON, responseText) {
								lastResultValue=0.0;
								for( var i=0; i<responseJSON.length; i++){
									lastResultValue=parseFloat(responseJSON[i]);
									break;
								}
								if( isNaN(lastResultValue) )lastResultValue=0.0;
							},
							onFailure: function(xhr) {
								lastResultValue=0.0;
							}
						}
					);
				
				
					function Calculate(){
						if( $('gesamteinheiten_dd').value==$('einheitenkunde_dd').value && $('gesamteinheiten_dd').value!="" ){
							var gesamteinheiten=parseFloat($('gesamteinheiten').value.replace(/\./g, "").replace(/,/g, "."));
							if( isNaN(gesamteinheiten) || gesamteinheiten==0.0 )return 0.00;
							var einheitenkunde=parseFloat($('einheitenkunde').value.replace(/\./g, "").replace(/,/g, "."));
							if( isNaN(einheitenkunde) )return 0.00;
							var gesamtbetrag=parseFloat($('gesamtbetrag').value.replace(/\./g, "").replace(/,/g, "."));
							if( isNaN(gesamtbetrag) )return 0.00;
							if( gesamtbetrag==0.0 || gesamteinheiten==0.0 || einheitenkunde==0.0 )return 0.00;
							var requestString="<?=SID;?>&reqDataType=9&param01=<?=$this->obj->GetPKey();?>&param02="+gesamtbetrag+"&param03="+gesamteinheiten+"&param04="+einheitenkunde<?if($this->obj->GetPKey()==-1){?>+"&param05=<?=$this->obj->GetTeilabrechnungPKey();?>"<?}?>;
							dataRequestObjBetragKunde.send(requestString);
							return parseFloat( (lastResultValue).toFixed(2) );
						}else{
							return 0.00;
						}
					}
					
					var autoUpdate=false;
					function UpdateGesamtbetrag(overwrite)
					{
                        if ($('pauschale').checked)
                        {
                            alert('Berechnung ist für Pauschale deaktiviert.');
                            return;
                        }
						if (!overwrite && !autoUpdate)return;
						$('betragkunde').value=(""+Calculate().toFixed(2)).replace(/\./g, ",");
					}
					
					function CheckAutoUpdate()
                    {
						var tempGesamtbetrag=parseFloat($('betragkunde').value.replace(/\./g, "").replace(/,/g, "."));
						if( tempGesamtbetrag==Calculate() )return true;
						return false;
					}
					autoUpdate=CheckAutoUpdate();
					
					$('gesamteinheiten').onblur = function(){UpdateGesamtbetrag(false);};
					$('gesamteinheiten_dd').onchange = function(){UpdateGesamtbetrag(false);};
					$('einheitenkunde').onblur = function(){UpdateGesamtbetrag(false);};
					$('einheitenkunde_dd').onchange = function(){UpdateGesamtbetrag(false);};
					$('gesamtbetrag').onblur = function(){UpdateGesamtbetrag(false);};
					$('betragkunde').onblur = function ()
                    {
                        if ($('pauschale').checked==false)
                        {
                            autoUpdate = CheckAutoUpdate();
                            if ($('betragkunde').value == "")autoUpdate = true;
                            UpdateGesamtbetrag(false);
                        }
                        else
                        {
                            autoUpdate = false;
                        }
					};

                    $('pauschale').onclick = function()
                    {
                        $('gesamteinheiten').disabled = $('pauschale').checked
                        $('gesamteinheiten_dd').disabled = $('pauschale').checked
                        $('einheitenkunde').disabled = $('pauschale').checked
                        $('einheitenkunde_dd').disabled = $('pauschale').checked
                        $('gesamtbetrag').disabled = $('pauschale').checked
                    }
				-->
			</script>
	<?
	}
	
}
?>