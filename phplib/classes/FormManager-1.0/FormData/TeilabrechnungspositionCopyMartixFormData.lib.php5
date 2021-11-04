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
class TeilabrechnungspositionCopyMartixFormData extends FormData 
{

	/**
	 * @var Teilabrechnung
	 */
	protected $selectedTa = null;
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		/**@var $customerManager CustomerManager */
		global $SHARED_HTTP_ROOT, $customerManager, $rsKostenartManager;
		// Vorauswahl treffen
		if ($loadFromObject)
		{
			if ((int)$this->formElementValues["taId"]>0)
			{
				/* @var $teilabrechnung Teilabrechnung */
				$teilabrechnung = $rsKostenartManager->GetTeilabrechnungByID($_SESSION["currentUser"], (int)$this->formElementValues["taId"]);
				// Get Teilabrechnung from previous year
				$teilabrechnung = $teilabrechnung!=null ? $teilabrechnung->GetTeilabrechnungPreviousYear($this->db) : null;
				if ($teilabrechnung!=null)
				{
					$jahr = $teilabrechnung->GetAbrechnungsJahr();
					$contract = $teilabrechnung->GetContract();
					$shop = $contract!=null ? $contract->GetShop() : null;
					$location = $shop!=null ? $shop->GetLocation() : null;
					$company = $location!=null ? $location->GetCompany() : null;
					$group = $company!=null ? $company->GetGroup() :null;

					$this->formElementValues["group"] = $group!=null ? $group->GetPKey() : '';
					$this->formElementValues["company"] = $company!=null ? $company->GetPKey() : '';
					$this->formElementValues["location"] = $location!=null ? $location->GetPKey() : '';
					$this->formElementValues["cShop"] = $shop!=null ? $shop->GetPKey() : '';
					$this->formElementValues["contract"] = $contract!=null ? $contract->GetPKey() : '';
					$this->formElementValues["year"] = $jahr!=null ? $jahr->GetPKey() : '';
					$this->formElementValues["teilabrechnung"] = $teilabrechnung->GetPKey();
				}
			}
		}

		/**@var $currentLocation CLocation */
		$locationName = $this->obj->GetContract()->GetShop()->GetLocation()->GetName();
		//echo $locationName;

		// Icon und Überschrift festlegen
		$this->options["icon"] = "teilabrechnung.png";
		$this->options["icontext"] = "Teilabrechnungspositionen aus anderer Abrechnung übernehmen";
		$this->options["show_options_save"] = false;
		
		$emptyOptions=Array();
		$emptyOptions[]=Array("name" => "Bitte wählen...", "value" => "");
		// TAP auswählen
		$this->elements[] = new CustomHTMLElement("<strong>Bitte wählen Sie die Teilabrechnung aus, von der Sie Teilabrechnungspositionen übernehmen möchten:</strong><br/>");
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Gruppe
		$options=$emptyOptions;
		$objects=$customerManager->GetGroups($_SESSION["currentUser"], '', 'name', 0, 0, 0, true);
		for( $a=0; $a<count($objects); $a++){
			$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("group", "Gruppe", $this->formElementValues["group"], true, $this->error["group"], $options, false);
		// Firma
		$options=$emptyOptions;
		$currentGroup=$customerManager->GetGroupByID($_SESSION["currentUser"], $this->formElementValues["group"], true);
		if($currentGroup!=null){
			$objects=$currentGroup->GetCompanys($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("company", "Firma", $this->formElementValues["company"], true, $this->error["company"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=101&param01='+$('group').options[$('group').selectedIndex].value+'", "$('group').onchange=function(){%REQUESTCALL%;};", "company") );
		// Standort
		$options=$emptyOptions;
		$currentCompany=$customerManager->GetCompanyByID($_SESSION["currentUser"], $this->formElementValues["company"], true);
		if($currentCompany!=null){
			$userHasAccess = $currentCompany->HasUserAccess($_SESSION["currentUser"], $this->db);
			$objects=$currentCompany->GetLocations($this->db);
			for( $a=0; $a<count($objects); $a++){
				if ($userHasAccess || $objects[$a]->GetName()==$locationName ) // if user has not full rights only return entries with the submitted name if exist
				{
					$options[] = Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
				}
			}
		}
		$this->elements[] = new DropdownElement("location", "Standort", $this->formElementValues["location"], true, $this->error["location"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=102&param01='+$('company').options[$('company').selectedIndex].value+'&param02=".urlencode($locationName), "$('company').onchange=function(){%REQUESTCALL%;};", "location"));
		// Läden
		$options=$emptyOptions;
		$currentLocation=$customerManager->GetLocationByID($_SESSION["currentUser"], $this->formElementValues["location"], true);
		if($currentLocation!=null){
			$objects=$currentLocation->GetShops($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("cShop", "Laden", $this->formElementValues["cShop"], true, $this->error["cShop"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=103&param01='+$('location').options[$('location').selectedIndex].value+'", "$('location').onchange=function(){%REQUESTCALL%;};", "cShop"));
		// Verträge
		$options=$emptyOptions;
		$currentShop=$customerManager->GetShopByID($_SESSION["currentUser"], $this->formElementValues["cShop"], true);
		if($currentShop!=null){
			$objects=$currentShop->GetContracts($this->db);
			for( $a=0; $a<count($objects); $a++)
			{
				$options[]=Array("name" => "Vertrag ".($objects[$a]->GetLifeOfLeaseString()=='' ? ($a+1) : $objects[$a]->GetLifeOfLeaseString() ), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("contract", "Vertrag", $this->formElementValues["contract"], true, $this->error["contract"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=104&param01='+$('cShop').options[$('cShop').selectedIndex].value+'", "$('cShop').onchange=function(){%REQUESTCALL%;};", "contract"));
		// Abrechnungsjahr
		$options=$emptyOptions;
		$currentContract=$rsKostenartManager->GetContractByID($_SESSION["currentUser"], $this->formElementValues["contract"]);
		if($currentContract!=null){
			$objects=$currentContract->GetAbrechnungsJahre($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetJahr(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("year", "Abrechnungsjahr", $this->formElementValues["year"], true, $this->error["year"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=10&param01='+$('contract').options[$('contract').selectedIndex].value+'", "$('contract').onchange=function(){%REQUESTCALL%;};", "year"));		
		// Teilabrechnungen
		$options=$emptyOptions;
		$objects=$rsKostenartManager->GetTeilabrechnungenByYear($_SESSION["currentUser"], $this->formElementValues["year"], false);
		for( $a=0; $a<count($objects); $a++){
			$options[]=Array("name" => $objects[$a]->GetBezeichnung(), "value" => $objects[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("teilabrechnung", "Teilabrechnung", $this->formElementValues["teilabrechnung"], true, $this->error["teilabrechnung"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=11&param01='+$('year').options[$('year').selectedIndex].value+'", "$('year').onchange=function(){%REQUESTCALL%;};", "teilabrechnung"));				
		// Button
		$this->elements[] = new CustomHTMLElement("<input type='button' value='TAPs anzeigen' onClick='SelectTA();' class='formButton2' />");
		$this->elements[] = new BlankElement();
		
		// TAPs
		if( $this->formElementValues["teilabrechnung"]!="" && (int)$this->formElementValues["teilabrechnung"]==$this->formElementValues["teilabrechnung"] ){
			$this->selectedTa = $rsKostenartManager->GetTeilabrechnungByID($_SESSION["currentUser"], (int)$this->formElementValues["teilabrechnung"]);
			if( $this->selectedTa !=null ){
				$this->elements[] = new CustomHTMLElement( $this->GetMatrixAsHTML($this->selectedTa , $this->selectedTa ->GetTeilabrechnungspositionen($this->db)) );
				$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
				$this->elements[] = new BlankElement();
				$this->elements[] = new BlankElement();
			}
		}
		
	}
	
	/**
	 * Erzeugt
	 * @param $ta Teilabrechnung
	 * @param $taps Teilabrechnungsposition[]
	 * @return string
	 */
	public function GetMatrixAsHTML($ta, $taps)
	{
		global $DOMAIN_HTTP_ROOT, $NKM_TEILABRECHNUNGSPOSITION_EINHEIT, $rsKostenartManager;
		$kostenarten=$rsKostenartManager->GetKostenarten("", "name", 0, 0, $rsKostenartManager->GetKostenartenCount());
		$kostenartenListe=Array();
		$kostenartenListe2=Array();
		for ($a=0; $a<count($kostenarten); $a++)
		{
			$kostenartenListe[$kostenarten[$a]->GetPKey()] = $kostenarten[$a]->GetName();
			$kostenartenListe2[$kostenarten[$a]->GetPKey()] = $kostenarten[$a];
		}
		// TAPs kopieren
		if ($_POST["copyTaps"]=="true")
		{
			// Prüfen welche TAPs übernpommen werden sollen
			/**@var $tapList Teilabrechnungsposition[]*/
			$tapList = Array();
			for ($a=0; $a<count($taps); $a++)
			{
				if ($_POST["TAP_"+$a]=="on" ||
					is_array($_POST["BTF"]) && in_array($taps[$a]->GetPKey(), $_POST["BTF"]) ||
					is_array($_POST["BKLA"]) && in_array($taps[$a]->GetPKey(), $_POST["BKLA"]) ||
					is_array($_POST["BKLRS"]) && in_array($taps[$a]->GetPKey(), $_POST["BKLRS"]) ||
                    is_array($_POST["PAU"]) && in_array($taps[$a]->GetPKey(), $_POST["PAU"]) ||
					is_array($_POST["GES"]) && in_array($taps[$a]->GetPKey(), $_POST["GES"]) ||
					is_array($_POST["EHK"]) && in_array($taps[$a]->GetPKey(), $_POST["EHK"]) ||
					is_array($_POST["GEB"]) && in_array($taps[$a]->GetPKey(), $_POST["GEB"]) ||
					is_array($_POST["BEK"]) && in_array($taps[$a]->GetPKey(), $_POST["BEK"]) )
				{
						$tapList[]=clone $taps[$a];
				}
			}
			// Alle TAPs die übernommen werden 
			$numSucces = 0;
			for ($a=0; $a<count($tapList); $a++)
			{
				// Daten in Abhängigkeit der Benutzerauswahl zurücksetzen
				if( !is_array($_POST["BTF"]) || !in_array($tapList[$a]->GetPKey(), $_POST["BTF"]) ) {$tapList[$a]->SetBezeichnungTeilflaeche("");}else{$tapList[$a]->SetBezeichnungTeilflaeche($_POST["BTF_".$tapList[$a]->GetPKey()."_value"]);}
				if( !is_array($_POST["BKLA"]) || !in_array($tapList[$a]->GetPKey(), $_POST["BKLA"]) ) {$tapList[$a]->SetBezeichnungKostenart("");}else{$tapList[$a]->SetBezeichnungKostenart($_POST["BKLA_".$tapList[$a]->GetPKey()."_value"]);}
				if( !is_array($_POST["BKLRS"]) || !in_array($tapList[$a]->GetPKey(), $_POST["BKLRS"]) ) {$tapList[$a]->SetKostenartRS(null);}else{$tapList[$a]->SetKostenartRS($kostenartenListe2[(int)$_POST["BKLRS_".$tapList[$a]->GetPKey()."_value"]]);}
                if( !is_array($_POST["PAU"]) || !in_array($tapList[$a]->GetPKey(), $_POST["PAU"]) ) {$tapList[$a]->SetPauschale(false);}else{$tapList[$a]->SetPauschale($_POST["PAU_".$tapList[$a]->GetPKey()."_value"]=="1" ? true : false);}
				if( !is_array($_POST["GES"]) || !in_array($tapList[$a]->GetPKey(), $_POST["GES"]) ) {$tapList[$a]->SetGesamteinheiten(0.0); $tapList[$a]->SetGesamteinheitenEinheit(NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE);}else{$tapList[$a]->SetGesamteinheiten(HelperLib::ConvertStringToFloat($_POST["GES_".$tapList[$a]->GetPKey()."_value"])); $tapList[$a]->SetGesamteinheitenEinheit((int)$_POST["GES_".$tapList[$a]->GetPKey()."_value2"]);}
				if( !is_array($_POST["EHK"]) || !in_array($tapList[$a]->GetPKey(), $_POST["EHK"]) ) {$tapList[$a]->SetEinheitKunde(0.0); $tapList[$a]->SetEinheitKundeEinheit(NKM_TEILABRECHNUNGSPOSITION_EINHEIT_KEINE); }else{$tapList[$a]->SetEinheitKunde(HelperLib::ConvertStringToFloat($_POST["EHK_".$tapList[$a]->GetPKey()."_value"])); $tapList[$a]->SetEinheitKundeEinheit((int)$_POST["EHK_".$tapList[$a]->GetPKey()."_value2"]);}
				if( !is_array($_POST["GEB"]) || !in_array($tapList[$a]->GetPKey(), $_POST["GEB"]) ) {$tapList[$a]->SetGesamtbetrag(0.0);}else{$tapList[$a]->SetGesamtbetrag(HelperLib::ConvertStringToFloat($_POST["GEB_".$tapList[$a]->GetPKey()."_value"]));}
				if( !is_array($_POST["BEK"]) || !in_array($tapList[$a]->GetPKey(), $_POST["BEK"]) ) {$tapList[$a]->SetBetragKunde(0.0);}else{$tapList[$a]->SetBetragKunde(HelperLib::ConvertStringToFloat($_POST["BEK_".$tapList[$a]->GetPKey()."_value"]));}
				/*
				// Betrag Kunde auf 0 setzen
				$tapList[$a]->SetBetragKunde(0.0);
				// Betrag Kunde berechnen (wenn alle Infos übernommen werden)
				if (is_array($_POST["BTF"]) && in_array($taps[$a]->GetPKey(), $_POST["BTF"]) &&
					is_array($_POST["BKLA"]) && in_array($taps[$a]->GetPKey(), $_POST["BKLA"]) &&
					is_array($_POST["BKLRS"]) && in_array($taps[$a]->GetPKey(), $_POST["BKLRS"]) &&
                    is_array($_POST["PAU"]) && in_array($taps[$a]->GetPKey(), $_POST["PAU"]) &&
					is_array($_POST["GES"]) && in_array($taps[$a]->GetPKey(), $_POST["GES"]) &&
					is_array($_POST["EHK"]) && in_array($taps[$a]->GetPKey(), $_POST["EHK"]) &&
					is_array($_POST["GEB"]) && in_array($taps[$a]->GetPKey(), $_POST["GEB"]) &&
					is_array($_POST["BEK"]) && in_array($taps[$a]->GetPKey(), $_POST["BEK"]) )

				{
                    if (!$tapList[$a]->IsPauschale()) $tapList[$a]->SetBetragKunde($tapList[$a]->GetBetragKundeSoll($this->db));
				}*/
				
				// Daten die immer zurückgesetzt werden
				$tapList[$a]->SetUmlagefaehig(2);
				// TAP auf diese Teilabrechnung umbiegen
				if( !$tapList[$a]->Copy($this->obj) )continue;
				// TAP speichern
				if( $tapList[$a]->Store($this->db)===true )$numSucces++;
			}
			?>
			<script type="text/javascript">
				<!--
				opener.document.FM_FORM.submit();
				-->
			</script>
			<?
		}
		ob_start();
		?>
		<br/><strong>Bitte wählen Sie die Teilabrechnungspositionen und Daten aus, die Sie übernehmen möchten:</strong><br/>
	<? if( isset($numSucces) && $numSucces>0 ){?>
			<div class="successText">Es wurde(n) <?=$numSucces;?> Teilabrechnungsposition(en) erfolgreich übernommen</div><br/>
	<?	}?>
		<br/>
		<input type="hidden" name="copyTaps" id="copyTaps" value="" />
		<table cellspacing="0" cellpadding="1px" border="0" width="100%" >
			<tr>
				<td valign="top" width="20px" class="TAPMatrixLeftRow"><input type="checkbox" id="TAP_ALL" name="TAP_ALL" class="CeckboxForm" onClick="SelectAll(this.checked); return true;" <? if($_POST["TAP_ALL"]=="on")echo "checked";?> /></td>
				<td valign="top" width="20px" class="TAPMatrixHeader"><input type="checkbox" id="BTF_ALL" name="BTF_ALL" class="CeckboxForm" onClick="SelectColumn('BTF', this.checked); return true;" <? if($_POST["BTF_ALL"]=="on")echo "checked";?> /></td>
				<td valign="top" class="TAPMatrixHeader2"><strong>Bezeichnung Teilfläche</strong></td>
				<td valign="top" width="20px" class="TAPMatrixHeader"><input type="checkbox" id="BKLA_ALL" name="BKLA_ALL" class="CeckboxForm" onClick="SelectColumn('BKLA', this.checked); return true;" <? if($_POST["BKLA_ALL"]=="on")echo "checked";?> /></td>
				<td valign="top" class="TAPMatrixHeader2"><strong>Bezeichnung Kostenart lt. Abrechnung</strong></td>
				<td valign="top" width="20px" class="TAPMatrixHeader"><input type="checkbox" id="BKLRS_ALL" name="BKLRS_ALL" class="CeckboxForm" onClick="SelectColumn('BKLRS', this.checked); return true;" <? if($_POST["BKLRS_ALL"]=="on")echo "checked";?> /></td>
				<td valign="top" class="TAPMatrixHeader2"><strong>Bezeichnung Kostenart lt. SFM </strong></td>
                <td valign="top" width="20px" class="TAPMatrixHeader"><input type="checkbox" id="PAU_ALL" name="PAU_ALL" class="CeckboxForm" onClick="SelectColumn('PAU', this.checked); return true;" <? if($_POST["PAU_ALL"]=="on")echo "checked";?> /></td>
                <td valign="top" class="TAPMatrixHeader2"><strong>Pauschale</strong></td>
                <td valign="top" width="20px" class="TAPMatrixHeader"><input type="checkbox" id="GES_ALL" name="GES_ALL" class="CeckboxForm" onClick="SelectColumn('GES', this.checked); return true;" <? if($_POST["GES_ALL"]=="on")echo "checked";?> /></td>
				<td valign="top" class="TAPMatrixHeader2"><strong>Gesamteinheiten</strong></td>
				<td valign="top" width="20px" class="TAPMatrixHeader"><input type="checkbox" id="EHK_ALL" name="EHK_ALL" class="CeckboxForm" onClick="SelectColumn('EHK', this.checked); return true;" <? if($_POST["EHK_ALL"]=="on")echo "checked";?> /></td>
				<td valign="top" class="TAPMatrixHeader2"><strong>Einheiten Kunde</strong></td>
				<td valign="top" width="20px" class="TAPMatrixHeader"><input type="checkbox" id="GEB_ALL" name="GEB_ALL" class="CeckboxForm" onClick="SelectColumn('GEB', this.checked); return true;" <? if($_POST["GEB_ALL"]=="on")echo "checked";?> /></td>
				<td valign="top" class="TAPMatrixHeader2"><strong>Gesamtbetrag in <?=$ta->GetCurrency();?></strong></td>
				<td valign="top" width="20px" class="TAPMatrixHeader"><input type="checkbox" id="BEK_ALL" name="BEK_ALL" class="CeckboxForm" onClick="SelectColumn('BEK', this.checked); return true;" <? if($_POST["BEK_ALL"]=="on")echo "checked";?> /></td>
				<td valign="top" class="TAPMatrixHeader2"><strong>Betrag Kunde in <?=$ta->GetCurrency();?></strong></td>
				<td valign="top" width="20px" class="TAPMatrixHeader2"><a href="javascript:UpdateGesamtbetrage()"><img style="height: 18px;" src="<?=$DOMAIN_HTTP_ROOT;?>pics/gui/process.png" title="Alle Werte aus Eingaben berechnen" alt="Alle Werte aus Eingaben berechnen" border="1" /></a></td>
			</tr>
		<? 	if( count($taps)==0 ){ ?>
				<tr>
					<td colspan="13">Es sind keine Teilabrechnungspositionen vorhanden</td>
				</tr>
		<?	}else{
				$summeGesamtbetrag = 0.0;
				$summeBetragKunde = 0.0;
				for($a=0; $a<count($taps); $a++){
					//$summeGesamtbetrag+=$taps[$a]->GetGesamtbetrag();
					//$summeBetragKunde+=$taps[$a]->GetBetragKunde();
					?>
					<tr>
						<td valign="top" class="TAPMatrixLeftRow"><input type="checkbox" id="TAP_<?=$a;?>" name="TAP_<?=$a;?>" onClick="SelectRow(<?=$a;?>, this.checked); return true;" class="CeckboxForm" <? if( $_POST["TAP_".$a]=="on" )echo "checked";?> /></td>
						<td valign="top" class="TAPMatrixRow"><input type="checkbox" id="BTF_<?=$a;?>" name="BTF[]" value="<?=$taps[$a]->GetPKey();?>" onClick="SelectCell('BTF', <?=$a;?>, this.checked); return true;" class="CeckboxForm" <? if( is_array($_POST["BTF"]) && in_array($taps[$a]->GetPKey(), $_POST["BTF"]) )echo "checked";?> /></td>
						<td valign="top" class="TAPMatrixRow"><input type="text" class="BTF_<?=$a;?>_value" style="width: 90px;"  id="BTF_<?=$taps[$a]->GetPKey();?>_value" name="BTF_<?=$taps[$a]->GetPKey();?>_value" value="<?=$taps[$a]->GetBezeichnungTeilflaech();?>" <? if( !is_array($_POST["BTF"]) || !in_array($taps[$a]->GetPKey(), $_POST["BTF"]) )echo "disabled";?> /></td>
						<td valign="top" class="TAPMatrixRow"><input type="checkbox" id="BKLA_<?=$a;?>" name="BKLA[]" value="<?=$taps[$a]->GetPKey();?>" onClick="SelectCell('BKLA', <?=$a;?>, this.checked); return true;" class="CeckboxForm" <? if( is_array($_POST["BKLA"]) && in_array($taps[$a]->GetPKey(), $_POST["BKLA"]) )echo "checked";?> /></td>
						<td valign="top" class="TAPMatrixRow"><input type="text" class="BKLA_<?=$a;?>_value" style="width: 90px;"  id="BKLA_<?=$taps[$a]->GetPKey();?>_value" name="BKLA_<?=$taps[$a]->GetPKey();?>_value" value="<?=$taps[$a]->GetBezeichnungKostenart();?>" <? if( !is_array($_POST["BKLA"]) || !in_array($taps[$a]->GetPKey(), $_POST["BKLA"]) )echo "disabled";?> /></td>
						<td valign="top" class="TAPMatrixRow"><input type="checkbox" id="BKLRS_<?=$a;?>" name="BKLRS[]" value="<?=$taps[$a]->GetPKey();?>" onClick="SelectCell('BKLRS', <?=$a;?>, this.checked); return true;" class="CeckboxForm" <? if( is_array($_POST["BKLRS"]) && in_array($taps[$a]->GetPKey(), $_POST["BKLRS"]) )echo "checked";?> /></td>
						<td valign="top" class="TAPMatrixRow">
							<select class="BKLRS_<?=$a;?>_value" style="width: 100px;" id="BKLRS_<?=$taps[$a]->GetPKey();?>_value" name="BKLRS_<?=$taps[$a]->GetPKey();?>_value" <? if( !is_array($_POST["BKLRS"]) || !in_array($taps[$a]->GetPKey(), $_POST["BKLRS"]) )echo "disabled";?>>
								<?foreach($kostenartenListe as $key => $name){?>
									<option value="<?=$key;?>" <?if($taps[$a]->GetKostenartRSPKey()==$key)echo "selected";?>><?=$name;?></option>
								<?} ?>
							</select>
						</td>
						<td valign="top" class="TAPMatrixRow"><input type="checkbox" id="PAU_<?=$a;?>" name="PAU[]" value="<?=$taps[$a]->GetPKey();?>" onClick="SelectCell('PAU', <?=$a;?>, this.checked); return true;" class="CeckboxForm" <? if( is_array($_POST["PAU"]) && in_array($taps[$a]->GetPKey(), $_POST["PAU"]) )echo "checked";?> /></td>
						<td valign="top" class="TAPMatrixRow">
							<select class="PAU_<?=$a;?>_value" style="width: 60px;" id="PAU_<?=$taps[$a]->GetPKey();?>_value" name="PAU_<?=$taps[$a]->GetPKey();?>_value" <? if( !is_array($_POST["PAU"]) || !in_array($taps[$a]->GetPKey(), $_POST["PAU"]) )echo "disabled";?>>
								<option value="0" <?if(!$taps[$a]->IsPauschale())echo "selected";?>>Nein</option>
								<option value="1" <?if($taps[$a]->IsPauschale())echo "selected";?>>Ja</option>
							</select>
						</td>
						<td valign="top" class="TAPMatrixRow"><input type="checkbox" id="GES_<?=$a;?>" name="GES[]" value="<?=$taps[$a]->GetPKey();?>" onClick="SelectCell('GES', <?=$a;?>, this.checked); return true;" class="CeckboxForm" <? if( is_array($_POST["GES"]) && in_array($taps[$a]->GetPKey(), $_POST["GES"]) )echo "checked";?> /></td>
						<td valign="top" class="TAPMatrixRow">
							<nobr>
								<input type="text" class="GES_<?=$a;?>_value GES_value" style="width: 60px;" id="GES_<?=$taps[$a]->GetPKey();?>_value" name="GES_<?=$taps[$a]->GetPKey();?>_value" value="<?=HelperLib::ConvertFloatToLocalizedString($taps[$a]->GetGesamteinheiten());?>" data-before="<?=HelperLib::ConvertFloatToLocalizedString($taps[$a]->GetGesamteinheiten());?>" data-id="<?=$taps[$a]->GetPKey();?>" <? if( !is_array($_POST["GES"]) || !in_array($taps[$a]->GetPKey(), $_POST["GES"]) )echo "disabled";?> onchange="UpdateAllValues('GES', this, false);" />
								<select class="GES_<?=$a;?>_value2" style="width: 50px;" id="GES_<?=$taps[$a]->GetPKey();?>_value2" name="GES_<?=$taps[$a]->GetPKey();?>_value2" <? if( !is_array($_POST["GES"]) || !in_array($taps[$a]->GetPKey(), $_POST["GES"]) )echo "disabled";?>>
									<?foreach($NKM_TEILABRECHNUNGSPOSITION_EINHEIT as $key => $name){?>
										<option value="<?=$key;?>" <?if($taps[$a]->GetGesamteinheitenEinheit()==$key)echo "selected";?>><?=$name["short"];?></option>
									<?} ?>
								</select>
							</nobr>
						</td>
						<td valign="top" class="TAPMatrixRow"><input type="checkbox" id="EHK_<?=$a;?>" name="EHK[]" value="<?=$taps[$a]->GetPKey();?>" onClick="SelectCell('EHK', <?=$a;?>, this.checked); return true;" class="CeckboxForm" <? if( is_array($_POST["EHK"]) && in_array($taps[$a]->GetPKey(), $_POST["EHK"]) )echo "checked";?> /></td>
						<td valign="top" class="TAPMatrixRow">
							<nobr>
								<input type="text" class="EHK_<?=$a;?>_value EHK_value" style="width: 60px;" id="EHK_<?=$taps[$a]->GetPKey();?>_value" name="EHK_<?=$taps[$a]->GetPKey();?>_value" value="<?=HelperLib::ConvertFloatToLocalizedString($taps[$a]->GetEinheitKunde());?>" data-before="<?=HelperLib::ConvertFloatToLocalizedString($taps[$a]->GetEinheitKunde());?>" data-id="<?=$taps[$a]->GetPKey();?>" <? if( !is_array($_POST["EHK"]) || !in_array($taps[$a]->GetPKey(), $_POST["EHK"]) )echo "disabled";?> onchange="UpdateAllValues('EHK', this, false);" />
								<select class="EHK_<?=$a;?>_value2" style="width: 50px;" id="EHK_<?=$taps[$a]->GetPKey();?>_value2" name="EHK_<?=$taps[$a]->GetPKey();?>_value2" <? if( !is_array($_POST["EHK"]) || !in_array($taps[$a]->GetPKey(), $_POST["EHK"]) )echo "disabled";?>>
									<?foreach($NKM_TEILABRECHNUNGSPOSITION_EINHEIT as $key => $name){?>
										<option value="<?=$key;?>" <?if($taps[$a]->GetEinheitKundeEinheit()==$key)echo "selected";?>><?=$name["short"];?></option>
									<?} ?>
								</select>
							</nobr>
						</td>
						<td valign="top" class="TAPMatrixRow"><input type="checkbox" id="GEB_<?=$a;?>" name="GEB[]" value="<?=$taps[$a]->GetPKey();?>" onClick="SelectCell('GEB', <?=$a;?>, this.checked); return true;" class="CeckboxForm" <? if( is_array($_POST["GEB"]) && in_array($taps[$a]->GetPKey(), $_POST["GEB"]) )echo "checked";?> /></td>
						<td align="right" class="TAPMatrixRow">
							<input type="text" class="GEB_<?=$a;?>_value GEB_value" style="width: 60px;" id="GEB_<?=$taps[$a]->GetPKey();?>_value" name="GEB_<?=$taps[$a]->GetPKey();?>_value" value="<?=HelperLib::ConvertFloatToLocalizedString( $taps[$a]->GetGesamtbetrag());?>" <? if( !is_array($_POST["GEB"]) || !in_array($taps[$a]->GetPKey(), $_POST["GEB"]) )echo "disabled";?> onchange="UpdateSum('GEB_value');" />
						</td>
						<td valign="top" class="TAPMatrixRow"><input type="checkbox" id="BEK_<?=$a;?>" name="BEK[]" value="<?=$taps[$a]->GetPKey();?>" onClick="SelectCell('BEK', <?=$a;?>, this.checked); return true;" class="CeckboxForm" <? if( is_array($_POST["BEK"]) && in_array($taps[$a]->GetPKey(), $_POST["BEK"]) )echo "checked";?> /></td>
						<td align="right" class="TAPMatrixRow">
							<input type="text" class="BEK_<?=$a;?>_value BEK_value" style="width: 60px;" id="BEK_<?=$taps[$a]->GetPKey();?>_value" name="BEK_<?=$taps[$a]->GetPKey();?>_value" value="<?=HelperLib::ConvertFloatToLocalizedString( $taps[$a]->GetBetragKunde());?>" <? if( !is_array($_POST["BEK"]) || !in_array($taps[$a]->GetPKey(), $_POST["BEK"]) )echo "disabled";?> onchange="UpdateSum('BEK_value');" />
						</td>
						<td align="right" class="TAPMatrixRow">
							<a href="javascript:UpdateGesamtbetrag('<?=$a;?>', '<?=$taps[$a]->GetPKey();?>', false);"><img class="BEK_<?=$a;?>_action BEK_action" data-index="<?=$a;?>" data-id="<?=$taps[$a]->GetPKey();?>" style="height: 18px;" src="<?=$DOMAIN_HTTP_ROOT;?>pics/gui/process.png" title="Wert aus Eingaben berechnen" alt="Wert aus Eingaben berechnen" border="1" <? if( !is_array($_POST["BEK"]) || !in_array($taps[$a]->GetPKey(), $_POST["BEK"]) )echo "hidden";?> /></a>
						</td>
					</tr>
			<? 	} ?>
				<tr>
					<td colspan="13" align="right">&#160;</td>
					<td colspan="2" align="right"><div id="GEB_value_sum"><?=HelperLib::ConvertFloatToLocalizedString($summeGesamtbetrag);?></div></td>
					<td colspan="2" align="right"><div id="BEK_value_sum"><?=HelperLib::ConvertFloatToLocalizedString($summeBetragKunde);?></div></td>
					<td></td>
				</tr>
				<tr>
					<td colspan="18" align="right"><br /><input type="button" value="Teilabrechnungspositionen übernehmen" onClick="CopyTAPs();" class="formButton2" /></td>
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
		
		return false;
	}
	
	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben nachdem das Formular ausgegeben wurde
	 * @access public
	 */
	public function PostPrint()
	{
		global $SHARED_HTTP_ROOT;
		?>
			<script type="text/javascript">
				<!--
					function SelectTA(){
						var taID = $('teilabrechnung').options[$('teilabrechnung').selectedIndex].value;
						if( taID=="" ){
							alert("Bitte wählen Sie eine Teilabrechnung aus");
						}else{
							document.forms.FM_FORM.submit();
						}
					}

					function SelectCell(type, index, changeTo)
					{
						$(type+"_"+index).checked=changeTo;
						if ($$("."+type+"_"+index+"_value").length==1) $$("."+type+"_"+index+"_value")[0].disabled=!changeTo;
						if ($$("."+type+"_"+index+"_value2").length==1) $$("."+type+"_"+index+"_value2")[0].disabled=!changeTo;
						if ($$("."+type+"_"+index+"_action").length==1) $$("."+type+"_"+index+"_action")[0].hidden=!changeTo;
						if (type=='GEB') UpdateSum('GEB_value');
						if (type=='BEK') UpdateSum('BEK_value');
					}

					function SelectColumn(type, changeTo){
						var index=0;
						while( $(type+"_"+index)!=null ){
							SelectCell(type, index, changeTo);
							index++;
						}
					}
					
					function SelectRow(index, changeTo){
						SelectCell("BTF", index, changeTo);
						SelectCell("BKLA", index, changeTo);
						SelectCell("BKLRS", index, changeTo);
						SelectCell("PAU", index, changeTo);
						SelectCell("GES", index, changeTo);
						SelectCell("EHK", index, changeTo);
						SelectCell("GEB", index, changeTo);
						SelectCell("BEK", index, changeTo);
					}
					
					function SelectAll(changeTo){
						//$("TAP_ALL").checked=changeTo;
						SelectColumn("TAP", changeTo);
						$("BTF_ALL").checked=changeTo;
						SelectColumn("BTF", changeTo);
						$("BKLA_ALL").checked=changeTo;
						SelectColumn("BKLA", changeTo);
						$("BKLRS_ALL").checked=changeTo;
						SelectColumn("BKLRS", changeTo);
                        $("PAU_ALL").checked=changeTo;
                        SelectColumn("PAU", changeTo);
						$("GES_ALL").checked=changeTo;
						SelectColumn("GES", changeTo);
						$("EHK_ALL").checked=changeTo;
						SelectColumn("EHK", changeTo);
						$("GEB_ALL").checked=changeTo;
						SelectColumn("GEB", changeTo);
						$("BEK_ALL").checked=changeTo;
						SelectColumn("BEK", changeTo);

					}
					
					function CopyTAPs(){
						$('copyTaps').value="true";
						document.forms.FM_FORM.submit();
					}

					function Calculate(index, id){
						if( $('GES_'+id+'_value2').value==$('EHK_'+id+'_value2').value && $('GES_'+id+'_value2').value!="" ){
							var gesamteinheiten=parseFloat($('GES_'+id+'_value').value.replace(/\./g, "").replace(/,/g, "."));
							if( isNaN(gesamteinheiten) || gesamteinheiten==0.0 )return 0.00;
							var einheitenkunde=parseFloat($('EHK_'+id+'_value').value.replace(/\./g, "").replace(/,/g, "."));
							if( isNaN(einheitenkunde) )return 0.00;
							var gesamtbetrag=parseFloat($('GEB_'+id+'_value').value.replace(/\./g, "").replace(/,/g, "."));
							if( isNaN(gesamtbetrag) )return 0.00;
							if( gesamtbetrag==0.0 || gesamteinheiten==0.0 || einheitenkunde==0.0 )return 0.00;
							ShowProgress(index, true);
							var requestString="<?=SID;?>&reqDataType=9&param01="+id+"&param02="+gesamtbetrag+"&param03="+gesamteinheiten+"&param04="+einheitenkunde; <? //+"&param05="+<?=$this->selectedTa->GetTeilabrechnungPKey();?>
							// Daten dynamisch laden...
							var dataRequestObjBetragKunde = new Request.JSON (
								{	url:'<?=$SHARED_HTTP_ROOT;?>phplib/jsInterface.php5',
									method: 'post',
									async: true,
									noCache: true,
									writeToElementIndex: index,
									writeToElementId: id,
									onSuccess: function(responseJSON, responseText) {
										var returnValue=0.0;
										for( var i=0; i<responseJSON.length; i++){
											returnValue=parseFloat(responseJSON[i]);
											break;
										}
										if( isNaN(returnValue) ) returnValue=0.0;
										$('BEK_'+this.options.writeToElementId+'_value').value=(""+returnValue.toFixed(2)).replace(/\./g, ",");
										ShowProgress(this.options.writeToElementIndex, false);
										UpdateSum('BEK_value');
									},
									onError: function(text, error){
										var returnValue=0.0;
										$('BEK_'+this.options.writeToElementId+'_value').value=(""+returnValue.toFixed(2)).replace(/\./g, ",");
										ShowProgress(this.options.writeToElementIndex, false);
										UpdateSum('BEK_value');
									},
									onFailure: function(xhr) {
										var returnValue=0.0;
										$('BEK_'+this.options.writeToElementId+'_value').value=(""+returnValue.toFixed(2)).replace(/\./g, ",");
										ShowProgress(this.options.writeToElementIndex, false);
										UpdateSum('BEK_value');
									}
								}
							).send(requestString);
						}
					}

					function ShowProgress(index, show)
					{
						$$(".BEK_"+index+"_action")[0].src = (show ? '<?=$SHARED_HTTP_ROOT;?>pics/gui/loading.gif' : '<?=$SHARED_HTTP_ROOT;?>pics/gui/process.png');
					}

					function UpdateGesamtbetrag(index, id, skipError)
					{

						if (!skipError && $('PAU_'+id+'_value').value=="1")
						{
							alert('Berechnung ist für Pauschale deaktiviert.');
							return;
						}
						Calculate(index, id);
					}

					function UpdateSum(className)
					{
						var sum = 0.0;
						var elements = $$('.'+className);
						for (var i=0; i<elements.length; i++)
						{
							if (elements[i].disabled) continue;
							var valueFloat = elements[i].value.replace(/\./g, "").replace(/,/g, "\.").toFloat();
							sum += valueFloat;
						}
						$(className+"_sum").innerHTML = sum.toFixed(2).replace(/\./g, ",");
					}

					function UpdateGesamtbetrage()
					{
						var elements = $$('.BEK_action');
						for (var i=0; i<elements.length; i++)
						{
							if (elements[i].hidden) continue;
							UpdateGesamtbetrag(elements[i].dataset.index, elements[i].dataset.id, true);
						}
					}

					function UpdateAllValues(type, element, skipCheck)
					{
						var oldValue = element.dataset.before.replace(/\./g, "").replace(/,/g, "\.").toFloat();
						element.dataset.before = element.value.replace(/\./g, "").replace(/,/g, "\.").toFloat();
						if (skipCheck==true) return;
						var unitType = $(type + '_' + element.dataset.id +'_value2').value;

						// check if there are other inputs with same old value and unit
						var elementsToOverwrite = new Array();
						var elements = $$('.' + type + '_value');
						for (var i=0; i<elements.length; i++)
						{
							if (oldValue == elements[i].value.replace(/\./g, "").replace(/,/g, "\.").toFloat() && unitType == $(type + '_' + elements[i].dataset.id +'_value2').value)
							{
								elementsToOverwrite.push(elements[i]);
							}
						}
						if (elementsToOverwrite.length>1 && confirm('Soll die neue Eingabe für die anderen ' + elementsToOverwrite.length + ' Felder mit gleichem Wert übernommen werden?'))
						{
							for (var i=0; i<elementsToOverwrite.length; i++)
							{
								elementsToOverwrite[i].value = element.value;
								UpdateAllValues(type, elementsToOverwrite[i], true);
							}
						}
					}
				-->
			</script>
		<?
	}
	
}
?>