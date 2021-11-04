<?php
/**
 * Status "Prüfung beauftragen"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasPruefungBeauftragen extends NkasStatusFormDataEntry
{
	
	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Vorbereitung treffen, wenn in diesen Status gesprungen wird
		if ($this->obj->GetLastStatus()==3 || $this->obj->GetLastStatus()==7 || $this->obj->GetLastStatus()==26)
		{
			$selectedProcess = $this->obj;
			if ($this->IsProcessGroup())
			{
				// use selected ProcessStatus on groups (to determine deadline with TA AuftragsdatumAbrechnung)
				$selectedProcess = $this->obj->GetSelectedProcessStatus();
			}
			if ($selectedProcess!=null)
			{
				$teilabrechnung = $selectedProcess->GetCurrentTeilabrechnung();
				// Wenn aktuelle Teilabrechung bereits erfasst wurde neue anlegen, andernfalls die aktuelle bearbeiten
				if ($teilabrechnung==null || $teilabrechnung->GetErfasst())
				{
					// Neue Teilabrechnung anlegen und aktiv setzen
					$teilabrechnung=new Teilabrechnung($this->db);			
					$teilabrechnung->SetAbrechnungsJahr($selectedProcess->GetAbrechnungsJahr());
					$returnValue=$teilabrechnung->Store($this->db);
					if ($returnValue===true)
					{
						$selectedProcess->SetCurrentTeilabrechnung($this->db, $teilabrechnung);
						$selectedProcess->Store($this->db);
					}
				}
			}
			// Bearbeitungsfrist: 30 Tage wenn auf weitere Teilabrechnungen gewartet wird
			$this->obj->SetDeadline(time()+60*60*24*30);
			$this->obj->Store($this->db);
		}
		else
		{
			// Bearbeitungsfrist: 0 Tage wenn neues Abrechnugsjahr angelegt wird
			$this->obj->SetDeadline(time());
		}
	}
	
	/**
	 * Initialize all UI elements 
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		global $customerManager, $rsKostenartManager;
		$teilabrechnung=$this->obj->GetTeilabrechnung($this->db);
        if ($loadFromObject)
        {
            $this->formElementValues["comment"] = $this->obj->GetCustomerComment();
        }
		if ($loadFromObject || $teilabrechnung!=null)
		{
			if ($teilabrechnung!=null)
			{
				$this->formElementValues["date"]=$teilabrechnung->GetAuftragsdatumAbrechnung()==0 ? "" : date("d.m.Y", $teilabrechnung->GetAuftragsdatumAbrechnung());
				$this->formElementValues["prepaid"]=HelperLib::ConvertFloatToLocalizedString( $teilabrechnung->GetVorauszahlungLautKunde() );
				$abrechnungsjahr=$teilabrechnung->GetAbrechnungsJahr();
				if ($abrechnungsjahr!=null)
				{
					$this->formElementValues["year"]=$abrechnungsjahr->GetJahr();
					$contract=$abrechnungsjahr->GetContract();
					if ($contract!=null)
					{
						$this->formElementValues["contract"]=$contract->GetPKey();
						$cShop=$contract->GetShop();
						if ($cShop!=null)
						{
							$this->formElementValues["shop"]=$cShop->GetPKey();
							$cLocation=$cShop->GetLocation();
							if ($cLocation!=null)
							{
								$this->formElementValues["location"]=$cLocation->GetPKey();
								$cCompany=$cLocation->GetCompany();
								if ($cCompany!=null)
								{
									$this->formElementValues["company"]=$cCompany->GetPKey();
									$cGroup=$cCompany->GetGroup();
									if ($cGroup!=null)
									{
										$this->formElementValues["group"]=$cGroup->GetPKey();
									}
								}
							}
						}
					}
				}
			}
		}
		else
		{
			if (!isset($this->formElementValues["date"])) $this->formElementValues["date"] = date("d.m.Y", time());
			if (!isset($this->formElementValues["prepaid"])) $this->formElementValues["prepaid"] = "0,00";
		}
		if ($teilabrechnung==null)
		{
			$emptyOptions=Array();
			$emptyOptions[]=Array("name" => "Alle", "value" => "");
			// Gruppe
			$options=$emptyOptions;
			$objects=$customerManager->GetGroups($_SESSION["currentUser"], "", "name", 0, 0, 0);
			for( $a=0; $a<count($objects); $a++)
			{
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
			$this->elements[] = new DropdownElement("groupFilter", "Gruppe anzeigen", $this->formElementValues["groupFilter"], false, $this->error["groupFilter"], $options, false, null, Array());
			// Firma
			$options=$emptyOptions;
			$currentGroup=$customerManager->GetGroupByID($_SESSION["currentUser"], $this->formElementValues["groupFilter"]);
			if ($currentGroup!=null)
			{
				$objects=$currentGroup->GetCompanys($this->db);
				for ($a=0; $a<count($objects); $a++)
				{
					$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
				}
			}
			else
			{
				$objects=$customerManager->GetCompanys($_SESSION["currentUser"], "", CCompany::TABLE_NAME.".name", 0, 0, 0);
				for ($a=0; $a<count($objects); $a++)
				{
					$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
				}
			}
			$this->elements[] = new DropdownElement("companyFilter", "Firma anzeigen", $this->formElementValues["companyFilter"], false, $this->error["companyFilter"], $options, false, null, Array());
			// Land
			global $customerManager;
			$countries = $customerManager->GetCountries();
			$options = $emptyOptions;
			foreach($countries as $country)
			{
				$options[] = Array("name" => $country->GetName()." (".$country->GetIso3166().")", "value" => $country->GetIso3166());
			}
			$this->elements[] = new DropdownElement("countryFilter", "Land anzeigen", $this->formElementValues["countryFilter"], false, $this->error["countryFilter"], $options, false, null, Array());

			// Liste mit Läden
			$this->elements[] = new ListElement("shopList", "<br />Laden", $this->formElementValues["shopList"], false, $this->error["shopList"], false, new CustomerShopSelectListData($this->db, $this->formElementValues["shop"], $this->formElementValues["groupFilter"],$this->formElementValues["companyFilter"],$this->formElementValues["countryFilter"] ));
			$this->elements[] = new BlankElement();
			$this->elements[] = new BlankElement();
		}
		$emptyOptions=Array();
		$emptyOptions[]=Array("name" => "Bitte wählen...", "value" => "");
		// Verträge
		$options=$emptyOptions;
		$currentShop=$customerManager->GetShopByID($_SESSION["currentUser"], $this->formElementValues["shop"]);
		if($currentShop!=null){
			$objects=$currentShop->GetContracts($this->db);
			for( $a=0; $a<count($objects); $a++)
			{
				
				$options[]=Array("name" => "Vertrag ".($objects[$a]->GetLifeOfLeaseString()=='' ? ($a+1) : $objects[$a]->GetLifeOfLeaseString() ), "value" => $objects[$a]->GetPKey());
			}
		}
		$bottonArrays=Array();
		$bottonArrays[] = Array("width" => 25, "pic" => "pics/gui/edit.png", "help" => "Ausgewähltem Vertrag weitere Dokumente hinzufügen", "href" => "javascript:EditContract()");
		if( $teilabrechnung==null )$bottonArrays[] = Array("width" => 25, "pic" => "pics/gui/newEntry.png", "help" => "Neuen Vertrag anlegen", "href" => "javascript:CreateNewContract()");
		$this->elements[] = new DropdownElement("contract", "Vertrag", $this->formElementValues["contract"], true, $this->error["contract"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=4&param01='+$('shop').value+'", "function OnChangeShop(){%REQUESTCALL%;};", "contract"), $bottonArrays, $teilabrechnung==null ? false : true );
		// Abrechnungsjahr
		$this->elements[] = new TextElement("year", AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr'), $this->formElementValues["year"], true, $this->error["year"], $teilabrechnung==null ? false : true);
		// Nur bei FMS-Mitarbeiter eine Liste mit nicht zugewiesenene Teilabrechnungen anzeigen
		$doAutoFill=false;
		if ($teilabrechnung==null && $_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$options=$emptyOptions;
			$this->elements[] = new DropdownElement("teilabrechnungAuswahl", "Bereits vorhandene Teilabrechnung", $this->formElementValues["teilabrechnungAuswahl"], false, $this->error["teilabrechnungAuswahl"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=7&param01='+$('contract').options[$('contract').selectedIndex].value+'&param02='+$('year').value+'", "$('contract').onchange=function(){%REQUESTCALL%;};$('year').onchange=function(){%REQUESTCALL%;};", "teilabrechnungAuswahl") );
			$doAutoFill=true;
		}
		// Beauftragungsdatum
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			$this->elements[] = new DateElement("date", Teilabrechnung::GetAttributeName($this->languageManager, 'auftragsdatumAbrechnung'), $this->formElementValues["date"], true, $this->error["date"], $doAutoFill ? new TextDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=5&param01='+$('teilabrechnungAuswahl').options[$('teilabrechnungAuswahl').selectedIndex].value+'", "$('teilabrechnungAuswahl').onchange=function(){%REQUESTCALL%;};", "date") : null);
		}
		// Vorauszahlung laut Buchhaltung
		$this->elements[] = new TextElement("prepaid", "Vorauszahlung laut Buchhaltung in der im Vertrag angegebenen Währung", $this->formElementValues["prepaid"], false, $this->error["prepaid"], false, false ? new TextDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=8&param01='+$('teilabrechnungAuswahl').options[$('teilabrechnungAuswahl').selectedIndex].value+'", "$('date').onchange=function(){%REQUESTCALL%;};", "prepaid") : null);		
		// Teilabrechnungen
		if ($teilabrechnung!=null && isset($_POST["deleteFile_teilabrechnung"]) && $_POST["deleteFile_teilabrechnung"]!="")
		{
			$fileToDelete=new File($this->db);
			if ($fileToDelete->Load((int)$_POST["deleteFile_teilabrechnung"], $this->db))
			{
				$teilabrechnung->RemoveFile($this->db, $fileToDelete);
			}
		}
		$this->elements[] = new FileElement("teilabrechnung", "Teilabrechnung", $this->formElementValues["teilabrechnung"], true, $this->error["teilabrechnung"], FM_FILE_SEMANTIC_TEILABRECHNUNG, $teilabrechnung!=null ? $teilabrechnung->GetFiles($this->db) : Array() );
        //
        $this->elements[] = new TextAreaElement("comment", "Kommentar", $this->formElementValues["comment"]);
		
		// Hidden field...
		ob_start();
		?>
		<input type="hidden" name="group" id="group" value="<?=$this->formElementValues["group"];?>" />
		<input type="hidden" name="company" id="company" value="<?=$this->formElementValues["company"];?>" />
		<input type="hidden" name="location" id="location" value="<?=$this->formElementValues["location"];?>" />
		<input type="hidden" name="shop" id="shop" value="<?=$this->formElementValues["shop"];?>" />
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
	}
	
	/**
	 * Store all UI data 
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		global $customerManager, $rsKostenartManager;
		$yearCreated=false;
		$currentYear=$this->obj->GetAbrechnungsJahr();
		if( $currentYear==null ){
			// Laden
			$currentShop=$customerManager->GetShopByID($_SESSION["currentUser"], $this->formElementValues["shop"]);
			if( $currentShop==null )$this->error["shop"]="Bitte wählen Sie den zugehörigen Laden aus";
			// Vertrag
			$currentContract=$rsKostenartManager->GetContractByID($_SESSION["currentUser"], $this->formElementValues["contract"]);
			if( $currentContract==null )$this->error["contract"]="Bitte wählen Sie den zugehörigen Vertrag aus";
			// Abrechnungsjahr
			$year=TextElement::GetInteger($this->formElementValues["year"]);
			if( $year===false || $year<1900 ){
				$this->error["year"]="Bitte geben Sie das Abrechnungsjahr im Format jjjj ein";
			}
			if( $currentContract!=null ){
				// AbrechungsJahr-Objekt suchen
				$abrechnungsJahre=$currentContract->GetAbrechnungsJahre($this->db);
				for($a=0; $a<count($abrechnungsJahre); $a++){
					if( $abrechnungsJahre[$a]->GetJahr()==$year ){
						$currentYear=$abrechnungsJahre[$a];
						break;
					}
				}
				// Gibt es bereits einen Prozess für das AbrechnungsJahr?
				if( $currentYear!=null && $currentYear->GetProcessStatus($this->db)!=null ){
					// Fehler: Es gibt bereits einen Prozess für dieses Jahr
					$this->error["year"]="Für den ausgewählten Laden wurde bereits eine Prüfung im angegeben Jahr beauftragt.";
				}
			}
		}
		// Beauftragungsdatum
		if( $_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP){
			$timeStamp=DateElement::GetTimeStamp($this->formElementValues["date"]);
			if( $timeStamp===false ){
				$this->error["date"]="Bitte geben Sie das Beauftragungsdatum im Format tt.mm.jjjj ein";
			}
		}
		// Vertrag vollständig?
		//if( $gotoNextStatus && $this->formElementValues["contractUpToDate"]!="on" )$this->error["contractUpToDate"]="Bitte ergänzen Sie die Verträge und/oder Nachträge.";
		// Vorauszahlung laut Buchhaltung
		$prepaid=TextElement::GetFloat($this->formElementValues["prepaid"]);
		if( $prepaid===false ){
			$this->error["prepaid"]="Bitte geben Sie die geleistete Vorauszahlung in EUR ein";
		}
		// Teilabrechnug
		$fileObject=FileElement::GetFileElement($this->db, $_FILES["teilabrechnung"], FM_FILE_SEMANTIC_TEILABRECHNUNG);
		if( !is_object($fileObject) || get_class($fileObject)!="File" ){
			if( $fileObject!=-1 ){
				$this->error["teilabrechnung"]=FileElement::GetErrorText($fileObject);
			}
		}else{
			// Datei wieder löschen, wenn ein Fehler
			if( count($this->error)>0 )$fileObject->DeleteMe($this->db);
		}
		// Fehler aufgetreten?
		if( count($this->error)==0 ){
			if( $currentYear==null ){
				// AbrechungsJahr-Objekt erzeugen wenn es noch nicht existiert
				$yearCreated=true;
				$currentYear=new AbrechnungsJahr($this->db);
				$currentYear->SetJahr($year);
				$currentYear->SetContract($currentContract);
				// Bei Kunden ist das Auftragsdatum gleich dem Tag, an dem das Jahr angelegt wird
				if( $_SESSION["currentUser"]->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP){
					// Abrechnungsjahr speichern
					$returnValue=$currentYear->Store($this->db);
					if( $returnValue!==true ){
						$this->error["year"]="Systemfehler (".$returnValue.")";
						return false;
					}
				}
				// Beauftragungsdatum durch FMS-Mitarbeiter ändern
				if( $currentYear!=null && $_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP){
					
					$returnValue=$currentYear->Store($this->db);
					if( $returnValue!==true ){
						$this->error["year"]="Systemfehler (".$returnValue.")";
						return false;
					}
				}
			}
            // Kommentar
            $this->obj->SetCustomerComment($this->formElementValues["comment"]);

			// Teilabrechnung 
			$teilabrechnung=$this->obj->GetTeilabrechnung($this->db);
			$teilabrechnungCreated=false;		
			// Wurde eine bestehende Teilabrechnung ausgewählt?
			if( $_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE){
				if( $currentYear->GetTeilabrechnungCount($this->db)>0 && isset($this->formElementValues["teilabrechnungAuswahl"]) && $this->formElementValues["teilabrechnungAuswahl"]!="" ){
					$ta=$currentYear->GetTeilabrechnungen($this->db);
					for( $a=0; $a<count($ta); $a++){
						if( $ta[$a]->GetPKey()==(int)$this->formElementValues["teilabrechnungAuswahl"] ){
							$teilabrechnung=$ta[$a];
							break;
						}
					}
				}
			}
			// Wenn kein TA dann neu anlegen
			if( $teilabrechnung==null ){
				$teilabrechnung=new Teilabrechnung($this->db);
				$teilabrechnungCreated=true;
			}
			// Wenn noch keine Datei hochgeladen wurde und auch jetzt keine hochgeladen wird, Fehler ausgeben
			if( $teilabrechnung->GetFileCount($this->db)==0 && (!is_object($fileObject) || get_class($fileObject)!="File") && $fileObject==-1 ){
				if( $teilabrechnungCreated )$teilabrechnung->DeleteMe($this->db);
				if( $yearCreated )$currentYear->DeleteMe($this->db);
				$this->error["teilabrechnung"]="Bitte laden Sie die Teilabrechnung(en) hoch";
				return false;
			}
			// Wenn eine Teilabrechnung ausgewählt wurde, die Daten aus der TA verwenden und die Eingabe ignorieren
			$teilabrechnung->SetAbrechnungsJahr( $currentYear );
			// Bei Kunden ist das Auftragsdatum gleich dem Tag, an dem das Jahr angelegt wird
			if( $_SESSION["currentUser"]->GetGroupBasetype($this->db)<=UM_GROUP_BASETYPE_KUNDE){
				$teilabrechnung->SetAuftragsdatumAbrechnung(time());
				$this->obj->SetAbschlussdatum(time()+60*60*24*7*6);	// + 6 Wochen
			}
			// Beauftragungsdatum durch FMS-Mitarbeiter ändern
			if( $_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE){
				$teilabrechnung->SetAuftragsdatumAbrechnung((int)$timeStamp );
				$this->obj->SetAbschlussdatum((int)$timeStamp+60*60*24*7*6);	// + 6 Wochen
			}					
			$teilabrechnung->SetVorauszahlungLautKunde( $prepaid );
			$returnValue=$teilabrechnung->Store($this->db);
			if ($returnValue===true)
            {
				// Dateien hinzufügen
				if( is_object($fileObject) && get_class($fileObject)=="File" ){
					if( !$teilabrechnung->AddFile($this->db, $fileObject) ){
						// Fehler: Datei wieder löschen
						$fileObject->DeleteMe($this->db);
						if( $teilabrechnungCreated )$teilabrechnung->DeleteMe($this->db);
						if( $yearCreated )$currentYear->DeleteMe($this->db);
						return false;
					}
				}
				// Teilabrechung diesem Prozess zuweisen und speichern
				if ($this->obj->SetAbrechnungsJahr($this->db, $currentYear))
                {
					if ($this->obj->SetCurrentTeilabrechnung($this->db, $teilabrechnung))
                    {
						$returnValue=$this->obj->Store($this->db);
						if( $returnValue===true )return true;
						$this->error["misc"][]="Prozessstatus konnte nicht gespeichert werden (Fehlercode: ".$returnValue.")";
					}
					$this->error["misc"][]="Aktive Teilabrechnung konnte nicht gesetzt werden";
				}
				// Bei Fehler alle angelegten Daten wieder löschen
				if( $teilabrechnungCreated ){
					if( is_object($fileObject) && get_class($fileObject)=="File" )$fileObject->DeleteMe($this->db);				
					$teilabrechnung->DeleteMe($this->db);
				}
				if( $yearCreated )$currentYear->DeleteMe($this->db);
			}else{
				// Abrechnungsjahr wieder löschen, falls es gerade erzeugt wurde...
				if( $yearCreated )$currentYear->DeleteMe($this->db);
				$fileObject->DeleteMe($this->db);
				$this->error["teilabrechnung"]="Systemfehler (".$returnValue.")";
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
		$teilabrechnung = $this->obj->GetTeilabrechnung($this->db);
		?>
		<script type="text/javascript">
			<!--
			<?	if ($teilabrechnung==null){ ?>
					function SelectShopFromList(groupID, companyID, locationID, shopID)
					{
						$('group').value = groupID;
						$('company').value = companyID;
						$('location').value = locationID;
						$('shop').value = shopID;
						OnChangeShop();
					}

					function CreateNewContract(){
						if( $('shop').value=="" ){
							alert("Bitte wählen Sie einen Laden aus, zu dem Sie einen Vertrag anlegen möchten");
							return;
						}
						var newWin=window.open('editContract.php5?<?=SID;?>&group='+$('group').value+'&company='+$('company').value+'&location='+$('location').value+'&cShop='+$('shop').value,'_createEditContract','resizable=1,status=1,location=0,menubar=0,scrollbars=0,toolbar=0,height=800,width=1024');
						newWin.focus();
					}

					// Wird vom editContract.php5 beim Schließen des Fensters aufgerufen
					function SelectContract(group, company, location, shop, contract){
						preSelect_contract=contract;
						SelectShopFromList(group, company, location, shop)
					}

					$('groupFilter').onchange=function(){document.getElementById("FM_FORM").submit();}
					$('companyFilter').onchange=function(){document.getElementById("FM_FORM").submit();}
					$('countryFilter').onchange=function(){document.getElementById("FM_FORM").submit();}
			<?	}?>
				function EditContract(){
					if( $('contract').options[$('contract').selectedIndex].value=="" ){
						alert("Bitte wählen Sie den Vertrag aus, zu dem Sie weitere Dokumente hinzufügen möchten");
						return;
					}
					var newWin=window.open('editContract.php5?<?=SID;?>&group='+$('group').value+'&company='+$('company').value+'&location='+$('location').value+'&cShop='+$('shop').value+'&editElement='+$('contract').options[$('contract').selectedIndex].value,'_createEditContract','resizable=1,status=1,location=0,menubar=0,scrollbars=0,toolbar=0,height=800,width=1024');
					newWin.focus();
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
		// only none group is editable
		return ($this->IsProcessGroup() ? false : true);
	}
	
}
?>