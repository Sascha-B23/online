<?php
/**
 * FormData-Implementierung für die Customer-Läden
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class CustomerShopFormData extends FormData 
{
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $CM_LOCATION_TYPES;
		global $userManager;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "cShops.png";
		$this->options["icontext"] = "Laden ";
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if( $loadFromObject ){
			$this->formElementValues["name"]=$this->obj->GetName();
			$this->formElementValues["rsid"]=$this->obj->GetRSID();
			$this->formElementValues["firstyear"]=$this->obj->GetFirstYear()>0 ? $this->obj->GetFirstYear() : "";
			$this->formElementValues["shopno"]=$this->obj->GetInternalShopNo();
			$this->formElementValues["opening"]=$this->obj->GetOpening()==0 ? "" : date("d.m.Y", $this->obj->GetOpening());
			$cLocation=$this->obj->GetLocation();
			if ($cLocation!=null) $this->formElementValues["clocation"]=$cLocation->GetPKey();
		}
		
		// check if shop is in one ore more groups
		$numEffectedGroups = WorkflowManager::GetProcessStatusGroupCountOfShop($this->db, $this->obj);
		$allowedToChange = $this->obj->IsAttributeAllowedToChange($this->db);
		$inGroup = $numEffectedGroups>0 ? true : false;
		$otherShops = Array();
		if ($numEffectedGroups>0) $otherShops = WorkflowManager::GetDependingShops($this->db, $this->obj);
		$showSub2 = (!$allowedToChange || count($otherShops)>0) ? true : false;
		
		if ($loadFromObject || $inGroup)
		{
			$cLocation = $this->obj->GetLocation();
			if ($cLocation!=null)
			{
				$cCompany = $cLocation->GetCompany();
				if ($cCompany!=null)
				{
					$this->formElementValues["company"]=$cCompany->GetPKey();
					$cGroup = $cCompany->GetGroup();
					if ($cGroup!=null)
					{
						$this->formElementValues["group"]=$cGroup->GetPKey();
					}
				}
			}
			if ($loadFromObject || !$allowedToChange)
			{
				$this->formElementValues["rsuser"]=$this->obj->GetCPersonRS();
				if( $this->formElementValues["rsuser"]!=null )$this->formElementValues["rsuser"]=$this->formElementValues["rsuser"]->GetPkey();
				else unset($this->formElementValues["rsuser"]);
				$this->formElementValues["cPersonFmsLeader"]=$this->obj->GetCPersonFmsLeader();
				if ($this->formElementValues["cPersonFmsLeader"]!=null) $this->formElementValues["cPersonFmsLeader"] = $this->formElementValues["cPersonFmsLeader"]->GetPkey();
				else unset($this->formElementValues["cPersonFmsLeader"]);
				$this->formElementValues["cPersonFmsAccountsdepartment"]=$this->obj->GetCPersonFmsAccountsdepartment();
				if ($this->formElementValues["cPersonFmsAccountsdepartment"]!=null) $this->formElementValues["cPersonFmsAccountsdepartment"] = $this->formElementValues["cPersonFmsAccountsdepartment"]->GetPkey();
				else unset($this->formElementValues["cPersonFmsAccountsdepartment"]);
				$this->formElementValues["customeruser"]=$this->obj->GetCPersonCustomer();
				if( $this->formElementValues["customeruser"]!=null )$this->formElementValues["customeruser"]=$this->formElementValues["customeruser"]->GetPkey();
				else unset($this->formElementValues["customeruser"]);
				$this->formElementValues["customerAccountUser"]=$this->obj->GetCPersonCustomerAccounting();
				if( $this->formElementValues["customerAccountUser"]!=null )$this->formElementValues["customerAccountUser"]=$this->formElementValues["customerAccountUser"]->GetPkey();
				else unset($this->formElementValues["customerAccountUser"]);	
			}
		}
		
		$this->elements[] = new TextElement("name", "Name", $this->formElementValues["name"], true, $this->error["name"]);
		global $customerManager;
		global $SHARED_HTTP_ROOT;
		$emptyOptions=Array();
		$emptyOptions[]=Array("name" => "Bitte wählen...", "value" => "");
		// Gruppe
		$options=$emptyOptions;
		$objects=$customerManager->GetGroups($_SESSION["currentUser"], '', 'name', 0, 0, 0);
		for( $a=0; $a<count($objects); $a++){
			$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("group", "Gruppe* ".($inGroup ? "<sup>1)</sup>" : ""), $this->formElementValues["group"], false, $this->error["group"], $options, false, null, Array(), $inGroup);
		// Firma
		$options=$emptyOptions;
		$currentGroup=$customerManager->GetGroupByID($_SESSION["currentUser"], $this->formElementValues["group"]);
		if($currentGroup!=null){
			$objects=$currentGroup->GetCompanys($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("company", "Firma* ".($inGroup ? "<sup>1)</sup>" : ""), $this->formElementValues["company"], false, $this->error["company"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=1&param01='+$('group').options[$('group').selectedIndex].value+'", "$('group').onchange=function(){%REQUESTCALL%;};", "company"), Array(), $inGroup );
		// Standort
		$options=$emptyOptions;
		$currentCompany=$customerManager->GetCompanyByID($_SESSION["currentUser"], $this->formElementValues["company"]);
		if($currentCompany!=null){
			$objects=$currentCompany->GetLocations($this->db);
			for( $a=0; $a<count($objects); $a++){
				$options[]=Array("name" => $objects[$a]->GetName(), "value" => $objects[$a]->GetPKey());
			}
		}
		$this->elements[] = new DropdownElement("clocation", "Standort", $this->formElementValues["clocation"], true, $this->error["clocation"], $options, false, new DropdownDynamicContent($SHARED_HTTP_ROOT."phplib/jsInterface.php5", "reqDataType=2&param01='+$('company').options[$('company').selectedIndex].value+'", "$('company').onchange=function(){%REQUESTCALL%;};", "clocation"));
		$this->elements[] = new TextElement("rsid", CShop::GetAttributeName($this->languageManager, 'RSID'), $this->formElementValues["rsid"], true, $this->error["rsid"]);
		$this->elements[] = new TextElement("firstyear", "Kunde seit", $this->formElementValues["firstyear"], false, $this->error["firstyear"]);
		$this->elements[] = new TextElement("shopno", CShop::GetAttributeName($this->languageManager, 'internalShopNo'), $this->formElementValues["shopno"], false, $this->error["shopno"]);
		$options=Array(0 => Array("name" => "Nein", "value" => 2), Array("name" => "Ja", "value" => 1) );
		$this->elements[] = new DateElement("opening", "Eröffnungsdatum", $this->formElementValues["opening"], false, $this->error["opening"]);
		
		// Zuständiger FMS-Mitarbeiter
		$users = $userManager->GetUsers($_SESSION["currentUser"], "", AddressData::TABLE_NAME.".name", 0, 0, 0);
		$options = Array(Array("name" => "Bitte wählen...", "value" => ""));
		for($a=0; $a<count($users); $a++)
		{
			if( !$users[$a]->BelongToGroupBasetype($this->db, UM_GROUP_BASETYPE_RSMITARBEITER) )continue;
			$options[]=Array("name" => $users[$a]->GetUserName(), "value" => $users[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("rsuser", "Zuständiger Forderungsmanager* ".($showSub2 ? "<sup>2)</sup>" : ""), !isset($this->formElementValues["rsuser"]) ? Array() : $this->formElementValues["rsuser"], false, $this->error["rsuser"], $options, false, null, Array(), !$allowedToChange);
		$this->elements[] = new DropdownElement("cPersonFmsLeader", "Zuständiger Nebenkostenanalyst* ".($showSub2 ? "<sup>2)</sup>" : ""), !isset($this->formElementValues["cPersonFmsLeader"]) ? Array() : $this->formElementValues["cPersonFmsLeader"], false, $this->error["cPersonFmsLeader"], $options, false, null, Array(), !$allowedToChange);
		// FMS Buchhaltung
		$options = Array(Array("name" => "Bitte wählen...", "value" => ""));
		for($a=0; $a<count($users); $a++)
		{
			if (!$users[$a]->BelongToGroupBasetype($this->db, UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT)) continue;
			$options[] = Array("name" => $users[$a]->GetUserName(), "value" => $users[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("cPersonFmsAccountsdepartment", "Zuständige Buchhaltung* ".($showSub2 ? "<sup>2)</sup>" : ""), !isset($this->formElementValues["cPersonFmsAccountsdepartment"]) ? Array() : $this->formElementValues["cPersonFmsAccountsdepartment"], false, $this->error["cPersonFmsAccountsdepartment"], $options, false, null, Array(), !$allowedToChange);
		// Zuständiger Kunde
		$options=Array(Array("name" => "Bitte wählen...", "value" => ""));
		for($a=0; $a<count($users); $a++){
			if( !$users[$a]->BelongToGroupBasetype($this->db, UM_GROUP_BASETYPE_KUNDE) || ($cGroup!=null && !$cGroup->HasUserAccess($users[$a], $this->db)) )continue;
			$options[]=Array("name" => $users[$a]->GetUserName(), "value" => $users[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("customeruser", "Zuständiger Kunde* ".($showSub2 ? "<sup>2)</sup>" : ""), !isset($this->formElementValues["customeruser"]) ? Array() : $this->formElementValues["customeruser"], false, $this->error["customeruser"], $options, false, null, Array(), !$allowedToChange);
		// Zuständiger Kunde Buchhaltung
		$this->elements[] = new DropdownElement("customerAccountUser", "Zuständiger Kunde Buchhaltung ".($showSub2 ? "<sup>2)</sup>" : ""), !isset($this->formElementValues["customerAccountUser"]) ? Array() : $this->formElementValues["customerAccountUser"], false, $this->error["customerAccountUser"], $options, false, null, Array(), !$allowedToChange);
		
		if ($numEffectedGroups>0)
		{
			ob_start();
			?>
			<div style="border-top: solid 1px #cccccc; padding: 5px;">
				<sup>1)</sup> Die Gruppe und Firma kann nicht geändert werden, da sich dieser Laden in einem Paket befindet.<br /><br />
			<?
			if (!$allowedToChange)
			{	?>
				<sup>2)</sup> Die zuständigen Personen können nicht geändert werden, da sich dieser Laden in <strong>mehreren</Strong> Pakten befindet.<br /><br />
				<?
			}
			else 
			{	
				if (count($otherShops)>0)
				{
					?>
					<sup>2)</sup> Dieser Laden befindet sich in min. einem Paket mit mehreren Läden.<br />
					Um die Konsistenz der Pakete zu gewährleisten, werden Änderungen<br />
					an den zuständigen Personen auch auf die anderen Läden aller beteiligten Pakete übernommen.<br />
					<br />
					Folgende Läden sind davon betroffen: <br />
					<table border="0" cellpadding="2" cellspacing="0" width="100%">
						<tr>
							<td class="TAPMatrixHeader2" align="left" valign="top">Gruppe</td>
							<td class="TAPMatrixHeader2" align="left" valign="top">Firma</td>
							<td class="TAPMatrixHeader2" align="left" valign="top">Standort</td>
							<td class="TAPMatrixHeader2" align="left" valign="top">Laden</td>
							<td class="TAPMatrixHeader2" align="left" valign="top">FMS-ID</td>
                            <td class="TAPMatrixHeader2" align="left" valign="top">Pakete</td>
						</tr>
						<?

						foreach ($otherShops as $otherShop) 
						{
							$locationTemp = $otherShop->GetLocation();
							$companyTemp = $locationTemp==null ? null : $locationTemp->GetCompany();
							$groupTemp = $companyTemp==null ? null : $companyTemp->GetGroup();
                            $groupNames = WorkflowManager::GetProcessStatusGroupNamesOfShop($this->db, $otherShop->GetPKey());
                            ?>
							<tr>
								<td class="TAPMatrixRow" align="left" valign="top"><?=($groupTemp==null ? "-" : $groupTemp->GetName());?></td>
								<td class="TAPMatrixRow" align="left" valign="top"><?=($companyTemp==null ? "-" : $companyTemp->GetName());?></td>
								<td class="TAPMatrixRow" align="left" valign="top"><?=($locationTemp==null ? "-" : $locationTemp->GetName());?></td>
								<td class="TAPMatrixRow" align="left" valign="top"><?=$otherShop->GetName();?></td>
								<td class="TAPMatrixRow" align="left" valign="top"><?=$otherShop->GetRSID();?></td>
                                <td class="TAPMatrixRow" align="left" valign="top"><?=implode(', ', $groupNames);?></td>
							</tr>
							<?
						}
						?>
					</table>
					<?
				}
			}
			?>
			</div>
			<?
			$html = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($html, "&#160;");
		}
		
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$allowedToChange = $this->obj->IsAttributeAllowedToChange($this->db);
		
		$this->error=array();
		$this->obj->SetName( $this->formElementValues["name"] );
		$this->obj->SetRSID( $this->formElementValues["rsid"] );
		$this->obj->SetFirstYear( $this->formElementValues["firstyear"] );
		$this->obj->SetInternalShopNo( $this->formElementValues["shopno"] );
		// Eröffnungdatum
		$timeStamp=DateElement::GetTimeStamp($this->formElementValues["opening"]);
		if( $timeStamp!==false || trim($this->formElementValues["opening"])=="" ){
			if ( $timeStamp!==false ) $this->obj->SetOpening($timeStamp);
			else $this->obj->SetOpening(0);
		}else{
			$this->error["opening"]="Bitte geben Sie das Eröffnungsdatum im Format tt.mm.jjjj ein";
		}
		
		// Gruppenzugehörigkeit seten
		$cLocation = new CLocation($this->db);
		if( !isset($this->formElementValues["clocation"]) || $this->formElementValues["clocation"]=="" || $cLocation->Load((int)$this->formElementValues["clocation"], $this->db)!==true ){
			unset($cLocation);
		}
		if (!$this->obj->SetLocation($this->db, $cLocation)) $this->error["company"]="Dieser Laden wird in einem Paket verwendet und kann aus<br/>diesem Grund keiner anderen Gruppe oder Firma zugeordnet werden.";
		
		// change responsible persons only if less den one group is affected
		if ($allowedToChange)
		{
			$rsuser = new User($this->db);
			if( !isset($this->formElementValues["rsuser"]) || $this->formElementValues["rsuser"]=="" || $rsuser->Load((int)$this->formElementValues["rsuser"], $this->db)!==true ){
				$rsuser = null;
			}
			if ($rsuser!=null)
			{
				$this->obj->SetCPersonRS($this->db, $rsuser);
			}
			else
			{
				$this->error["rsuser"] = "Bitte geben Sie den zuständigen Forderungsmanager an.";
			}
		
			$cPersonFmsLeader = new User($this->db);
			if (!isset($this->formElementValues["cPersonFmsLeader"]) || $this->formElementValues["cPersonFmsLeader"]=="" || $cPersonFmsLeader->Load((int)$this->formElementValues["cPersonFmsLeader"], $this->db)!==true)
			{
				$cPersonFmsLeader=null;
			}
			if ($cPersonFmsLeader!=null)
			{
				$this->obj->SetCPersonFmsLeader($this->db, $cPersonFmsLeader);
				if ($cPersonFmsLeader!=null && $rsuser!=null && $cPersonFmsLeader->GetPKey()==$rsuser->GetPKey())
				{
					$this->error["cPersonFmsLeader"] = "Der Nebenkostenanalyst muss eine andere Person als der Forderungsmanager sein.";
				}
			}
			else
			{
				$this->error["cPersonFmsLeader"] = "Bitte geben Sie den zuständigen Nebenkostenanalyst an.";
			}

			$cPersonFmsAccountsdepartment = new User($this->db);
			if (!isset($this->formElementValues["cPersonFmsAccountsdepartment"]) || $this->formElementValues["cPersonFmsAccountsdepartment"]=="" || $cPersonFmsAccountsdepartment->Load((int)$this->formElementValues["cPersonFmsAccountsdepartment"], $this->db)!==true)
			{
				$cPersonFmsAccountsdepartment=null;
			}
			if ($cPersonFmsAccountsdepartment!=null)
			{
				$this->obj->SetCPersonFmsAccountsdepartment($this->db, $cPersonFmsAccountsdepartment);
			}
			else
			{
				$this->error["cPersonFmsAccountsdepartment"] = "Bitte geben Sie die zuständige Buchhaltung an.";
			}		
			
			$customeruser = new User($this->db);
			if (!isset($this->formElementValues["customeruser"]) || $this->formElementValues["customeruser"]=="" || $customeruser->Load((int)$this->formElementValues["customeruser"], $this->db)!==true)
			{
				$customeruser=null;
			}
			if ($customeruser!=null)
			{
				$this->obj->SetCPersonCustomer($this->db, $customeruser);
			}
			else
			{
				$this->error["customeruser"] = "Bitte geben Sie den zuständigen Kunden an.";
			}

			$customerAccountUser = new User($this->db);
			if (!isset($this->formElementValues["customerAccountUser"]) || $this->formElementValues["customerAccountUser"]=="" || $customerAccountUser->Load((int)$this->formElementValues["customerAccountUser"], $this->db)!==true)
			{
				$customerAccountUser = null;
			}
			//if ($customerAccountUser!=null)
			{
				$this->obj->SetCPersonCustomerAccounting($this->db, $customerAccountUser);
			}
			/*else
			{
				$this->error["customerAccountUser"] = "Bitte geben Sie den zuständigen Kunden aus der Buchhaltung an.";
			}*/
		}
		
		if( trim($this->formElementValues["firstyear"])!="" && (!is_numeric($this->formElementValues["firstyear"]) || ((int)$this->formElementValues["firstyear"])<1990) )$this->error["firstyear"]="Bitte geben sie das Jahr im Format jjjj ein";
		if( count($this->error)==0 ){
			$returnValue=$this->obj->Store($this->db);
			if( $returnValue===true )return true;
		}
		if( $returnValue==-101 )$this->error["rsid"]="Bitte geben Sie eine eindeutige SFM-ID ein";
		if( $returnValue==-102 )$this->error["name"]="Bitte geben Sie einen Namen ein";
		if( $returnValue==-103 )$this->error["clocation"]="Bitte wählen Sie den zugehörigen Standort aus";
		if( $returnValue==-104 )$this->error["rsid"]="Es exisitiert bereits ein Laden mit dieser SFM-ID";
		return false;
	}
	
}
?>