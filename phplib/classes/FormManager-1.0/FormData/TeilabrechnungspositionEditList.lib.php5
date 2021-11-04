<?php
/**
 * FormData-Implementierung für die Abschlagszahlung
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TeilabrechnungspositionEditList extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		// Icon und Überschrift festlegen
		$this->options["icon"] = "teilabrechnung.png";
		$this->options["icontext"] = "Umlagefähigkeit bei Teilabrechnungspositionen bearbeiten";
		
		// TAPs
		$this->elements[] = new CustomHTMLElement( $this->GetListAsHTML($this->obj->GetTeilabrechnungspositionen($this->db)) );
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();		
	}
	
	/**
	 * Erzeugt 
	 */
	public function GetListAsHTML($taps)
	{
		global $NKM_TEILABRECHNUNGSPOSITION_EINHEIT, $rsKostenartManager;
		$kostenarten=$rsKostenartManager->GetKostenarten("", "name", 0, 0, $rsKostenartManager->GetKostenartenCount());
		$kostenartenListe=Array();
		for ($a=0; $a<count($kostenarten); $a++)
		{
			$kostenartenListe[$kostenarten[$a]->GetPKey()] = $kostenarten[$a]->GetName();
		}
		ob_start();
		?>
		<br/>
		<table cellspacing="0" cellpadding="1px" border="0" width="100%" >
			<tr>
				<td valign="top" class="TAPMatrixHeader2"><strong>Bezeichnung Teilfläche</strong></td>
				<td valign="top" class="TAPMatrixHeader2"><strong>Bezeichnung Kostenart lt. Abrechnung</strong></td>
				<td valign="top" class="TAPMatrixHeader2"><strong>Bezeichnung Kostenart lt. SFM </strong></td>
				<!--<td valign="top" class="TAPMatrixHeader2"><strong>Gesamteinheiten</strong></td>
				<td valign="top" class="TAPMatrixHeader2"><strong>Einheiten Kunde</strong></td>
				<td valign="top" class="TAPMatrixHeader2"><strong>Gesamtbetrag</strong></td>-->
				<td valign="top" class="TAPMatrixHeader2" style="width: 90px;"><strong>Umlagefähig</strong></td>
			</tr>
		<? 	if( count($taps)==0 ){ ?>
				<tr>
					<td colspan="13">Es sind keine Teilabrechnungspositionen vorhanden</td>
				</tr>
		<?	}
			else
			{
				for($a=0; $a<count($taps); $a++)
				{ 
					?>
				<tr>
					<td valign="top" class="TAPMatrixRow"><?=$taps[$a]->GetBezeichnungTeilflaech();?></td>
					<td valign="top" class="TAPMatrixRow"><?=$taps[$a]->GetBezeichnungKostenart();?></td>
					<td valign="top" class="TAPMatrixRow"><?=$kostenartenListe[$taps[$a]->GetKostenartRSPKey()];?></td>
					<!--<td valign="top" class="TAPMatrixRow"><?=$taps[$a]->GetGesamteinheiten();?> <?=$NKM_TEILABRECHNUNGSPOSITION_EINHEIT[$taps[$a]->GetGesamteinheitenEinheit()]["short"];?></td>
					<td valign="top" class="TAPMatrixRow"><?=$taps[$a]->GetEinheitKunde();?> <?=$NKM_TEILABRECHNUNGSPOSITION_EINHEIT[$taps[$a]->GetEinheitKundeEinheit()]["short"];?></td>
					<td align="right" class="TAPMatrixRow"><?=$taps[$a]->GetCurrency($this->db);?> <?=HelperLib::ConvertFloatToLocalizedString( $taps[$a]->GetGesamtbetrag());?></td>-->
					<td align="top" class="TAPMatrixRow"><input type="radio" name="umlagefaehig_<?=$taps[$a]->GetPKey();?>" value="1" <?=($taps[$a]->GetUmlagefaehig()==1 ? "checked" : "");?> class="radio_element_yes" />Ja <input type="radio" name="umlagefaehig_<?=$taps[$a]->GetPKey();?>" value="2" <?=($taps[$a]->GetUmlagefaehig()==2 ? "checked" : "");?> class="radio_element_no" />Nein </td>
				</tr>
			<? 	} ?>
				<tr>
					<td colspan="3" align="right">Alle Teilabrechnungspositionen setzen auf</td>
					<td colspan="1" align="left"><input type="radio" name="umlagefaehig_all" value="1" onclick="set_all_to_yes();" />Ja <input type="radio" name="umlagefaehig_all" value="2" onclick="set_all_to_no();" />Nein </td>
				</tr>
		<?	} ?>
		</table>
		
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		return $CONTENT;
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$taps = $this->obj->GetTeilabrechnungspositionen($this->db);
		for ($a=0; $a<count($taps); $a++)
		{
			if (isset($this->formElementValues['umlagefaehig_'.$taps[$a]->GetPKey()]))
			{
				$value = (int)$this->formElementValues['umlagefaehig_'.$taps[$a]->GetPKey()];
				if ($value!=0)
				{
					$taps[$a]->SetUmlagefaehig($value);
					if ($taps[$a]->Store($this->db)!==true)
					{
						$this->error['misc'][] = "Änderung bei der Teilabrechnungsposition mit der ID '".$taps[$a]->GetPKey()."' konnte nicht gespiechert werden.";
					}
				}
			}
		}
		if (count($this->error)==0) return true;
		return false;
	}
	
	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben nachdem das Formular ausgegeben wurde
	 * @access public
	 */
	public function PostPrint()
	{
		?>
			<script type="text/javascript">
				<!--
					function select_all_radiobuttons(elements)
					{
						for (var a=0; a<elements.length; a++)
						{
							elements[a].checked = true;
						}
					}
	
					function set_all_to_yes()
					{
						select_all_radiobuttons($$('[class=radio_element_yes]'));
					}
					
					function set_all_to_no()
					{
						select_all_radiobuttons($$('[class=radio_element_no]'));
					}
				-->
			</script>
		<?
	}
	
}
?>